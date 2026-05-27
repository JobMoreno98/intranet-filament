<?php

namespace App\Http\Controllers;

use App\Models\Coleccion;
use App\Models\ColeccionesConsulta;
use App\Models\Recursos;
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

        $colecciones = $colecciones = Coleccion::from('coleccions as c')
            ->select('c.*')
            ->join(DB::raw('(
        WITH RECURSIVE colecciones_tree AS (
            # 1. CASO BASE: Limpiamos espacios con TRIM por si acaso
            SELECT id, CAST(TRIM(nombre) AS CHAR(500)) as path_tree 
            FROM coleccions 
            WHERE parent_id IS NULL
            
            UNION ALL
            
            # 2. CASO RECURSIVO: Limpiamos también el nombre del hijo al concatenar
            SELECT child.id, CAST(CONCAT(parent.path_tree, " > ", TRIM(child.nombre)) AS CHAR(500))
            FROM coleccions child
            INNER JOIN colecciones_tree parent ON child.parent_id = parent.id
        )
        SELECT id, path_tree FROM colecciones_tree
    ) as tree'), 'c.id', '=', 'tree.id')
            # Usamos el ordenamiento natural de la ruta limpia
            ->orderBy('tree.path_tree', 'ASC')
            ->paginate(15);


        //dd($colecciones);
        return view('home', compact('colecciones'))->with(['title' => 'Inicio']);
    }

    public function show(Request $request, Coleccion $coleccion)
    {

        //dd($coleccion->items);

        //$nombreTabla = DB::connection('mysql2')->table('colecciones')->select('tabla', 'coleccion')->where('clave', $id)->first();

        if (!$coleccion) {
            abort(404, 'La colección no existe.');
        }

        // 2. Iniciar la consulta sobre esa tabla

        // Buscamos en la configuración qué campos están permitidos para esta tabla
        /*
        $camposPermitidos = DB::connection('mysql2')->table('colecciones')->where('tabla', $nombreTabla->tabla)->pluck('campo')->toArray();

        foreach ($request->only($camposPermitidos) as $campo => $valor) {
            if ($valor !== null && $valor !== '') {
                $query->where($campo, 'LIKE', "%{$valor}%");
            }
        }*/

        $data = $coleccion->items()
            ->paginate(14)
            ->appends($request->all())
            ->onEachSide(0);


        return view('coleccion', [
            'data' => $data,
            'tablaNombre' => $coleccion->tabla,
            //'id' => $id,
            'title' => $coleccion->nombre,
        ]);
    }

    public function buscador(Request $request)
    {
        $term = $request->input('q');
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
                    ->setLimit(20)
                    ->setAttributesToHighlight(['*'])
                    ->setAttributesToCrop(['descripcion', 'texto', 'biografia']) // Los campos largos que uses
                    ->setCropLength(25); // Trae aproximadamente unas 25 palabras alrededor del 'em'

                $queries[] = $searchQuery;
            }

            //dd($queries);

            try {
                // 3. Enviamos el lote unificado a Meilisearch
                $response = $meili->multiSearch($queries);

                // Normalizamos la respuesta a un arreglo nativo para ganar consistencia y velocidad
                $results = is_array($response) ? $response['results'] : $response->toArray()['results'];

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
                                            $cleanValue = str_replace(
                                                ['&lt;em&gt;', '&lt;/em&gt;'],
                                                ['<em class="bg-amber-200 text-black font-semibold px-0.5 rounded">', '</em>'],
                                                $cleanValue
                                            );

                                            $snippet = 'Perteneciente a la colección padre: ... ' . $cleanValue . ' ...';
                                            break 2; // Rompemos el bucle del array y el de los campos
                                        }
                                    }
                                }

                                // 3. CASO NORMAL: Si es un campo de texto plano (Nombre, Descripción, etc.)
                                if (is_string($valorFormateado) && str_contains($valorFormateado, '<em>')) {
                                    $cleanValue = htmlspecialchars($valorFormateado, ENT_QUOTES, 'UTF-8');
                                    $cleanValue = str_replace(
                                        ['&lt;em&gt;', '&lt;/em&gt;'],
                                        ['<em class="bg-amber-200 text-black font-semibold px-0.5 rounded">', '</em>'],
                                        $cleanValue
                                    );

                                    $snippet = 'En [' . ucfirst($campo) . ']: ... ' . $cleanValue . ' ...';
                                    break; // Encontró coincidencia en texto plano, rompemos bucle
                                }
                            }
                        }

                        $resultados[] = [
                            'index' => $indexUid,
                            'tipo' => $hit['tipo'] ?? 'documento',
                            'titulo_resultado' => $hit['titulo'] ?? ($hit['nombre'] ?? 'Registro sin título'),
                            'coincidencia' => $snippet, // Ahora va garantizado con texto útil
                            'registro_id' => $hit['id'] ?? null,
                            'slug' => $hit['slug'] ?? null,
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error en búsqueda Meilisearch: ' . $e->getMessage(), [
                    'queries' => $queries,
                    'trace' => $e->getTraceAsString()
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
    public function showRegistro($tipo, $id)
    {
        // 1. Validar que la tabla exista por seguridad

        $recurso = Recursos::findOrFail($id);
        //dd($recurso);

        if (!$recurso) {
            abort(404, 'El registro no fue encontrado.');
        }

        try {
            // Incrementa el contador del recurso ID dentro del Hash "analytics:recursos_vistas"
            Redis::hincrby('analytics:recursos_vistas', $recurso->id, 1);

            // También sumamos al contador global del día
            $hoy = now()->format('Y-m-d');
            Redis::incr("analytics:recursos_vistas:{$hoy}");
        } catch (\Exception $e) {
            // Fallback en caso de que Redis no responda
        }

        $omitir = [
            'IdElemento',
            'id',
            'created_at',
            'updated_at',
            'usuario_id',
            'carpetaContenido',
            'archvios',
            'updated_at',
            'deleted_at',
            'vistas_count',
            'hash_archivo',
            'assets_procesados',
            'status'
        ];

        $id = $recurso->id;

        $recursoData = Cache::remember("recurso_view_data_{$id}", 1800, function () use ($id) {
            $recurso = Recursos::with([
                'archivos' => function ($q) {
                    $q->orderBy('orden');
                },
            ])->findOrFail($id);

            return $recurso->toArray();
        });

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
        $labels = [
            'anio' => 'Año',
            'tipoarchivo' => 'Tipo Archivo',
            'numpaginas' => 'No. Páginas',
            'numarchivos' => 'No. Archivos',
            'numero' => 'Número',
            'titulo' => 'Título',
            'autor' => 'Autor',
            'dia' => 'Día',
            'paginas' => 'Páginas',
            'epocaperiodo' => 'Epoca o Periodo',
            'nombrepersonajeprincipal' => 'Nombre de Personaje principal',
            'nombrepersonajesecundario' => 'Nombre de Personaje secundario',
            'clavefondoprincipal' => 'Clave Fondo Principal',
            'fondoprincipal' => 'Fondo principal',
            'lugar1' => 'Lugar 1',
            'lugar2' => 'Lugar 2',
            'anio2' => 'Año 2',
            'numinventario' => 'No. Inventario',
            'observaciones1' => 'Observaciones',
            'autor1' => 'Autor 1',
            'volumentomoejemplar' => 'Volumen / Tomo / Ejemplar',
            'numInventario' => 'No. Inventario',
            'descripcion' => 'Descripción',
            'ejemplarTomo' => 'Ejemplar / Tomo ',
            'numArchivos' => 'No. Archivos',
            'tipoArchivo' => 'Tipo Archivo',
            'nombrePersonajePrincipal' => 'Nombre de Personaje principal',
            'nombrePersonajeSecundario' => 'Nombre de Personaje secundario',
        ];

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
