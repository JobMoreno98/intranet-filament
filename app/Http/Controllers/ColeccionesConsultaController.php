<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Coleccion;
use App\Models\ColeccionesConsulta;
use App\Models\Recursos;
use App\Models\TipoAcervo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Meilisearch\Client as MeilisearchClient;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Schema;
use Meilisearch\Contracts\SearchQuery;

class ColeccionesConsultaController extends Controller
{
    public function index(Request $request)
    {
        /*
        $colecciones = Coleccion::from('coleccions as c')
            ->select('c.*')
            ->join(
                DB::raw('(
        WITH RECURSIVE colecciones_tree AS (
            SELECT id, CAST(TRIM(nombre) AS CHAR(500)) as path_tree
            FROM coleccions
            WHERE parent_id IS NULL

            UNION ALL

            SELECT child.id, CAST(CONCAT(parent.path_tree, " > ", TRIM(child.nombre)) AS CHAR(500))
            FROM coleccions child
            INNER JOIN colecciones_tree parent ON child.parent_id = parent.id
        )
        SELECT id, path_tree FROM colecciones_tree
    ) as tree'),
                'c.id',
                '=',
                'tree.id',
            )
            // IMPORTANTE: Solo nos interesan los padres en la lista principal
            ->whereNull('c.parent_id')
            // Cargamos de golpe todos sus hijos (y si quieres, ordenados)
            ->with([
                'children' => function ($query) {
                    $query->orderBy('nombre', 'ASC');
                },
            ])
            ->orderBy('tree.path_tree', 'ASC')
            ->paginate(5);
*/

        $areas = Area::where('parent_id', null)->paginate(6);
        return view('home', compact('areas'))->with(['title' => 'Inicio']);
    }

    public function show(Request $request, Coleccion $coleccion)
    {
        if (!$coleccion) {
            abort(404, 'La colección no existe.');
        }

        $acervosDisponibles = $coleccion->items()->whereNotNull('acervo_id')->select('acervo_id')->distinct()->with('acervo')->get();

        $resultados = [];
        $term = $request->input('q', '');

        $meili = new MeilisearchClient(config('scout.meilisearch.host'), config('scout.meilisearch.key'));

        // Filtro base obligatorio
        $meiliFilters = ["coleccion_id = {$coleccion->id}"];

        if ($request->filled('acervo_id')) {
            $acervoIdActual = $request->input('acervo_id');
            $meiliFilters[] = "acervo_id = {$acervoIdActual}";

            // Consultamos el esquema dinámico con tu modelo
            $config = \App\Models\TipoAcervo::where('id', $acervoIdActual)->first();

            if ($config && isset($config->esquema)) {
                $esquema = is_string($config->esquema) ? json_decode($config->esquema, true) : (array) $config->esquema;
                $camposAtributos = collect($esquema)
                    ->filter(fn($item) => ($item['visible'] ?? null) === 'Recuperable')
                    ->pluck('variable')
                    ->toArray();
                // Mapeamos los filtros extras de la URL si el usuario los escribió
                foreach ($request->only($camposAtributos) as $campo => $valor) {
                    if ($valor !== null && $valor !== '') {
                        $meiliFilters[] = "metadata.{$campo} = \"{$valor}\"";
                    }
                }
            }
        }

        try {
            // La consulta ahora se ejecutará al instante sin errores de "not filterable"
            $searchQuery = new SearchQuery()->setIndexUid('recursos')->setQuery($term)->setLimit(300)->setFilter($meiliFilters);

            $response = $meili->multiSearch([$searchQuery]);
            $results = is_array($response) ? $response['results'] : $response->toArray()['results'];

            foreach ($results as $indexResult) {
                $hits = $indexResult['hits'] ?? [];
                foreach ($hits as $hit) {
                    $resultados[] = (object) [
                        'id' => $hit['id'],
                        'acervo_id' => $hit['acervo_id'] ?? null,
                        'coleccion_id' => $hit['coleccion_id'] ?? null,
                        'metadata' => $hit['metadata_text'] ?? [],
                        'tipo_media' => $hit['tipo_media'] ?? null,
                        'status' => $hit['status'] ?? null,
                        'acervo' => (object) ['nombre' => $hit['acervo'] ?? '---'],
                        'coleccion' => (object) ['nombre' => $hit['coleccion'] ?? '---'],
                        'archivos' => $hit['archivos'],
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('Error en búsqueda automática por Acervo en Meilisearch: ' . $e->getMessage());
        }

        $perPage = 14;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = array_slice($resultados, ($currentPage - 1) * $perPage, $perPage);

        $data = new LengthAwarePaginator($currentItems, count($resultados), $perPage, $currentPage, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
        ]);

        $data->appends($request->all())->onEachSide(0);

        try {
            \Illuminate\Support\Facades\Redis::hincrby('analytics:coleccion_vistas', $coleccion->id, 1);
            $hoy = now()->format('Y-m-d');
            \Illuminate\Support\Facades\Redis::incr("analytics:coleccion_vistas:{$hoy}");
        } catch (\Exception $e) {
        }



        return view('coleccion', [
            'data' => $data,
            'tablaNombre' => 'recursos',
            'coleccion' => $coleccion,
            'acervosDisponibles' => $acervosDisponibles,
            'title' => $coleccion->nombre ?? 'Colección',
            'header' => isset($esquema) ? collect($esquema)
                ->filter(fn($item) => ($item['visible'] ?? null) === 'Recuperable')
                ->pluck('variable')
                ->toArray() : []
        ]);
    }

    public function buscador(Request $request)
    {

        $request->validate([
            'q' => ['required', 'string', 'min:1'],
            'acervo_id' => ['nullable', 'exists:tipo_acervos,id'],
        ]);

        $term = $request->input('q');
        $acervo = $request->acervo_id;
        $resultados = [];

        if ($request->filled('q')) {
            // 1. Inicializamos el cliente leyendo de tu .env de forma segura
            $meili = new MeilisearchClient(config('scout.meilisearch.host'), config('scout.meilisearch.key'));

            // Obtener la metadata de las colecciones desde tu MySQL
            $coleccionesMeta = ['coleccions', 'recursos'];

            // 2. Construir las consultas usando las clases oficiales del SDK
            $queries = [];
            foreach ($coleccionesMeta as $key => $meta) {
                $searchQuery = new SearchQuery()
                    ->setIndexUid($meta)
                    ->setQuery($term)
                    //->setLimit(20)
                    ->setAttributesToHighlight(['*'])
                    ->setAttributesToCrop(['descripcion', 'texto', 'biografia']) // Los campos largos que uses
                    ->setCropLength(25); // Trae aproximadamente unas 25 palabras alrededor del 'em'

                if (!empty($acervo) && $meta === 'recursos') {
                    $searchQuery->setFilter(["acervo_id = $acervo"]);
                }
                $queries[] = $searchQuery;
            }

            //dd($queries);

            try {
                // 3. Enviamos el lote unificado a Meilisearch
                $response = $meili->multiSearch($queries);

                // Normalizamos la respuesta a un arreglo nativo para ganar consistencia y velocidad
                $results = is_array($response) ? $response['results'] : $response->toArray()['results'];
                //dd($results);

                foreach ($results as $indexResult) {
                    $hits = $indexResult['hits'] ?? [];
                    $indexUid = $indexResult['indexUid'] ?? 'desconocido';

                    foreach ($hits as $hit) {
                        $formatted = $hit['_formatted'] ?? [];

                        // 1. SALVAVIDAS: En lugar de un texto estático, usamos la descripción como base
                        // Si el match fue en un ID o Array, el usuario verá el inicio de la descripción
                        $descripcionBase = $hit['descripcion'] ?? ($hit['resumen'] ?? 'Sin descripción disponible');
                        $snippet = \Illuminate\Support\Str::limit($descripcionBase, 140);
                        if (!empty($formatted)) {
                            foreach ($formatted as $campo => $valorFormateado) {
                                // 1. Ignoramos IDs y campos numéricos
                                if (in_array($campo, ['id', 'IdElemento', 'parent_id', 'parent_ids'])) {
                                    continue;
                                }

                                // 2. CASO ESPECIAL: Si es el array de los nombres de los padres (Escalera)
                                if ($campo === 'parent_names' && is_array($valorFormateado)) {
                                    foreach ($valorFormateado as $nombrePadre) {
                                        if (str_contains($nombrePadre, '<em>')) {
                                            // Sanitizamos y resaltamos el nombre del padre encontrado
                                            $cleanValue = htmlspecialchars($nombrePadre, ENT_QUOTES, 'UTF-8');
                                            $cleanValue = str_replace(['&lt;em&gt;', '&lt;/em&gt;'], ['<em class="bg-amber-200 text-black font-semibold px-0.5 rounded">', '</em>'], $cleanValue);

                                            $snippet = 'Perteneciente a la colección padre: ... ' . $cleanValue . ' ...';
                                            break 2;
                                        }
                                    }
                                }

                                // 3. CASO NORMAL: Si es un campo de texto plano (Nombre, Descripción, etc.)
                                if (is_string($valorFormateado) && str_contains($valorFormateado, '<em>')) {
                                    $cleanValue = htmlspecialchars($valorFormateado, ENT_QUOTES, 'UTF-8');
                                    $cleanValue = str_replace(['&lt;em&gt;', '&lt;/em&gt;'], ['<em class="bg-amber-200 text-black font-semibold px-0.5 rounded">', '</em>'], $cleanValue);

                                    $snippet = 'En [' . ucfirst($campo) . ']: ... ' . $cleanValue . ' ...';
                                    break; // Encontró coincidencia en texto plano, rompemos bucle
                                }
                            }


                            if (isset($formatted['metadata']) && is_array($formatted['metadata'])) {
                                foreach ($formatted['metadata'] as $clave => $valorFormateado) {
                                    if (is_string($valorFormateado) && str_contains($valorFormateado, '<em>')) {
                                        $cleanValue = htmlspecialchars($valorFormateado, ENT_QUOTES, 'UTF-8');
                                        $cleanValue = str_replace(
                                            ['&lt;em&gt;', '&lt;/em&gt;'],
                                            ['<em class="bg-amber-200 text-black font-semibold px-0.5 rounded">', '</em>'],
                                            $cleanValue
                                        );

                                        // 👇 Aquí armas la salida completa con la clave y el valor resaltado
                                        $snippet = 'Valor encontrado en: <br/> ' . ucfirst($clave) . ': ' . $cleanValue;
                                        break;
                                    }
                                }
                            }
                        }
                        if ($indexUid === 'coleccions') {
                            $resultados[] = [
                                'index' => $indexUid,
                                'tipo' => $hit['tipo'] ?? 'coleccion',
                                'titulo_resultado' => $hit['nombre'] ?? 'Colección sin nombre',
                                'coincidencia' => $snippet,
                                'registro_id' => $hit['id'] ?? null,
                                'slug' => $hit['slug'] ?? null,
                                'descripcion' => $hit['descripcion'] ?? null,
                            ];
                        } else { // recursos
                            $resultados[] = [
                                'index' => $indexUid,
                                'acervo' => $hit['acervo'] ?? null,
                                'coleccion' => $hit['coleccion'] ?? null,
                                'tipo' => $hit['tipo'] ?? 'documento',
                                'titulo_resultado' => $hit['titulo'] ?? ($hit['nombre'] ?? 'Registro sin título'),
                                'coincidencia' => $snippet,
                                'registro_id' => $hit['id'] ?? null,
                                'slug' => $hit['slug'] ?? null,
                                'metadata' => $hit['metadata'] ?? [],
                            ];
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error en búsqueda Meilisearch: ' . $e->getMessage(), [
                    'queries' => $queries,
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        $perPage = 15;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = array_slice($resultados, ($currentPage - 1) * $perPage, $perPage);

        $paginados = new LengthAwarePaginator($currentItems, count($resultados), $perPage, $currentPage, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
        ]);

        $paginados->appends($request->all())->onEachSide(0);

        return view('respuestas', [
            'resultados' => $paginados,
            'term' => $term,
            'title' => 'Búsqueda General',
        ]);
    }

    public function showRegistro(Request $request, $tipo, $id)
    {
        // 1. Validar que la tabla exista por seguridad

        $recurso = Recursos::findOrFail($id);
        //dd($recurso);

        if (!$recurso) {
            abort(404, 'El registro no fue encontrado.');
        }

        try {
            // 1. Incrementa el contador del recurso ID dentro del Hash (Esto ya te funciona)
            Redis::hincrby('analytics:recursos_vistas', $recurso->id, 1);

            // 2. CORREGIDO: Construimos el payload real del visitante único diario
            $payload = json_encode([
                'ip' => $request->ip() ?? $request->header('X-Forwarded-For'),
                'user_agent' => $request->userAgent(),
                'url' => $request->getRequestUri(),
                'user_id' => auth()->id(),
                'created_at' => now()->toDateTimeString(),
            ]);

            // 3. CORREGIDO: Empujamos a la lista global limpia que procesa tu comando Artisan
            Redis::rpush('analytics:visitas_queue', $payload);

            // 4. (Opcional) Si quieres mantener tu contador plano del día:
            $hoy = now()->format('Y-m-d');
            Redis::incr("analytics:recursos_vistas:{$hoy}");
        } catch (\Exception $e) {
            // Fallback en caso de que Redis no responda
            Log::error('Error registrando analítica en visor: ' . $e->getMessage());
        }

        $omitir = ['tipo_media', 'IdElemento', 'id', 'created_at', 'updated_at', 'usuario_id', 'carpetaContenido', 'archvios', 'updated_at', 'deleted_at', 'vistas_count', 'hash_archivo', 'assets_procesados', 'status'];

        $id = $recurso->id;

        $recursoData = Cache::remember("recurso_view_data_{$id}", 1800, function () use ($id) {
            $recurso = Recursos::with([
                'archivos' => function ($q) {
                    $q->orderBy('orden');
                },
            ])->findOrFail($id);

            return $recurso->toArray();
        });


        $esquema = [];

        if ($recurso->acervo && $recurso->acervo->esquema) {
            $esquema = is_string($recurso->acervo->esquema)
                ? json_decode($recurso->acervo->esquema, true)
                : $recurso->acervo->esquema;
        }

        $camposPermitidos = collect($esquema)
            ->whereIn('visible', ['Recuperable', 'Adicional'])
            ->pluck('variable')
            ->toArray();

        $metadata = collect($recursoData['metadata'] ?? [])
            ->only($camposPermitidos)
            ->toArray();

        $recurso->metadata = $metadata;

        // 2. Mapeamos los archivos y les firmamos un token con caducidad
        $paginas = collect($recursoData['archivos'])
            ->map(function ($archivo) {
                $payload = [
                    'a' => $archivo['id'],
                    'u' => auth()->id(),
                    'e' => now()->timestamp + 300, //
                ];

                // Se encripta usando la App Key única de tu servidor
                $token = encrypt(json_encode($payload));

                return [
                    'id' => $archivo['id'],
                    'url' => route('media.stream', [
                        'token' => $token,
                    ]),
                    'w' => 1200,
                    'h' => 1600,

                    'ocrUrl' => route('visor.ocr', [
                        'token' => $token,
                    ]),
                ];
            })
            ->toArray();

        // 3. Pasamos los datos a la vista
        // Nota: En la vista, ahora $recurso será un array,
        // asegúrate de usar $recurso['titulo'] en lugar de $recurso->titulo

        /*
        return view('visor', [
            'paginas' => $paginas,
            'recurso' => $recursoData,
        ]);
        */

        // Tu diccionario de etiquetas amigables


        $labels = collect($esquema)
            ->whereIn('visible', ['Recuperable', 'Adicional'])
            ->pluck('label', 'variable')
            ->toArray();


        return view('registro-detalle', [
            'registro' => $recurso,
            'tablaNombre' => $recurso->coleccion,
            'coleccionNombre' => $recurso->coleccion,
            'labels' => $labels,
            'title' => 'Detalle del Registro',
            'omitir' => $omitir,
            'paginas' => $paginas,
            'recurso' => $recursoData,
        ]);
    }
}
