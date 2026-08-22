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
    /**
     * Cliente Meilisearch compartido por los métodos que hacen búsquedas,
     * en vez de instanciar uno nuevo en cada método.
     */
    protected MeilisearchClient $meili;

    public function __construct()
    {
        $this->meili = new MeilisearchClient(config('scout.meilisearch.host'), config('scout.meilisearch.key'));
    }

    public function index(Request $request)
    {
        $areas = Area::paginate(6);
        return view('home', compact('areas'))->with(['title' => 'Inicio']);
    }

    public function show(Request $request, Coleccion $coleccion)
    {
        // Nota: con route-model binding tipado, Laravel ya lanza 404 automáticamente
        // si $coleccion no existe, así que no hace falta comprobarlo aquí.

        $acervosDisponibles = $coleccion->items()->whereNotNull('acervo_id')->select('acervo_id')->distinct()->with('acervo')->get();

        $resultados = [];
        $term = $request->input('q', '');
        $esquema = [];

        // Filtro base obligatorio
        $meiliFilters = ["coleccion_id = {$coleccion->id}"];

        if ($request->filled('acervo_id')) {
            $acervoIdActual = $request->input('acervo_id');
            $meiliFilters[] = "acervo_id = {$acervoIdActual}";

            // El esquema de un TipoAcervo casi no cambia: lo cacheamos para no
            // pegarle a la base de datos en cada búsqueda filtrada.
            $config = Cache::remember("tipo_acervo_esquema_{$acervoIdActual}", 3600, function () use ($acervoIdActual) {
                return TipoAcervo::find($acervoIdActual);
            });

            if ($config && isset($config->esquema)) {
                $esquema = is_string($config->esquema) ? json_decode($config->esquema, true) : (array) $config->esquema;
                $camposAtributos = collect($esquema)
                    ->filter(fn($item) => ($item['visible'] ?? null) === 'Recuperable')
                    ->pluck('variable')
                    ->toArray();
                // Mapeamos los filtros extras de la URL si el usuario los escribió
                foreach ($request->only($camposAtributos) as $campo => $valor) {
                    if ($valor !== null && $valor !== '') {
                        $meiliFilters[] = "metadata.{$campo} = \"" . $this->escaparFiltroMeili($valor) . "\"";
                    }
                }
            }
        }

        $perPage = 14;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $totalHits = 0;

        try {
            // Pedimos solo la página actual a Meilisearch (offset/limit nativos)
            // en vez de traer 300 resultados y cortarlos en PHP.
            $searchQuery = new SearchQuery()
                ->setIndexUid('recursos')
                ->setQuery($term)
                ->setOffset(($currentPage - 1) * $perPage)
                ->setLimit($perPage)
                ->setFilter($meiliFilters);

            $response = $this->meili->multiSearch([$searchQuery]);
            $results = is_array($response) ? $response['results'] : $response->toArray()['results'];

            foreach ($results as $indexResult) {
                $totalHits = $indexResult['estimatedTotalHits'] ?? $indexResult['totalHits'] ?? count($indexResult['hits'] ?? []);
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

        // $resultados ya es solo la página actual, no hace falta array_slice
        $data = new LengthAwarePaginator($resultados, $totalHits, $perPage, $currentPage, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
        ]);

        $data->appends($request->all())->onEachSide(0);

        try {
            Redis::hincrby('analytics:coleccion_vistas', $coleccion->id, 1);
            $hoy = now()->format('Y-m-d');
            Redis::incr("analytics:coleccion_vistas:{$hoy}");
        } catch (\Exception $e) {
        }

        return view('coleccion', [
            'data' => $data,
            'tablaNombre' => 'recursos',
            'coleccion' => $coleccion,
            'acervosDisponibles' => $acervosDisponibles,
            'title' => $coleccion->nombre ?? 'Colección',
            'header' => collect($esquema)
                ->filter(fn($item) => ($item['visible'] ?? null) === 'Recuperable')
                ->pluck('variable')
                ->toArray(),
        ]);
    }

    /**
     * Escapa comillas dobles y backslashes para poder interpolar un valor
     * de forma segura dentro de un filtro de Meilisearch.
     */
    private function escaparFiltroMeili(string $valor): string
    {
        return str_replace(['\\', '"'], ['\\\\', '\\"'], $valor);
    }

    /**
     * Sanitiza un valor resaltado por Meilisearch (con marcas <em>) y le aplica
     * las clases de Tailwind para mostrarlo en la vista de resultados.
     */
    private function resaltarCoincidencia(string $valorFormateado): string
    {
        $cleanValue = htmlspecialchars($valorFormateado, ENT_QUOTES, 'UTF-8');

        return str_replace(
            ['&lt;em&gt;', '&lt;/em&gt;'],
            ['<em class="bg-amber-200 text-black font-semibold px-0.5 rounded">', '</em>'],
            $cleanValue
        );
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
            // Índices reales sobre los que buscamos (el '' original generaba
            // una búsqueda vacía contra un índice inexistente en cada request)
            $coleccionesMeta = ['coleccions', 'recursos'];

            // Construir las consultas usando las clases oficiales del SDK
            $queries = [];
            foreach ($coleccionesMeta as $meta) {
                $searchQuery = new SearchQuery()
                    ->setIndexUid($meta)
                    ->setQuery($term)
                    ->setLimit(150)
                    ->setAttributesToHighlight(['*'])
                    ->setAttributesToCrop(['descripcion', 'texto', 'biografia']) // Los campos largos que uses
                    ->setCropLength(25); // Trae aproximadamente unas 25 palabras alrededor del 'em'

                if (!empty($acervo) && $meta === 'recursos') {
                    $searchQuery->setFilter(["acervo_id = " . $this->escaparFiltroMeili((string) $acervo)]);
                }
                $queries[] = $searchQuery;
            }

            try {
                // Enviamos el lote unificado a Meilisearch
                $response = $this->meili->multiSearch($queries);
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
                                            $snippet = 'Perteneciente a la colección padre: ... ' . $this->resaltarCoincidencia($nombrePadre) . ' ...';
                                            break 2;
                                        }
                                    }
                                }

                                // 3. CASO NORMAL: Si es un campo de texto plano (Nombre, Descripción, etc.)
                                if (is_string($valorFormateado) && str_contains($valorFormateado, '<em>')) {
                                    $snippet = 'En [' . ucfirst($campo) . ']: ... ' . $this->resaltarCoincidencia($valorFormateado) . ' ...';
                                    break; // Encontró coincidencia en texto plano, rompemos bucle
                                }
                            }

                            if (isset($formatted['metadata']) && is_array($formatted['metadata'])) {
                                foreach ($formatted['metadata'] as $clave => $valorFormateado) {
                                    if (is_string($valorFormateado) && str_contains($valorFormateado, '<em>')) {
                                        // Aquí armas la salida completa con la clave y el valor resaltado
                                        $snippet = 'Valor encontrado en: <br/> ' . ucfirst($clave) . ': ' . $this->resaltarCoincidencia($valorFormateado);
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
        // Nota: findOrFail() ya lanza 404 automáticamente si no existe,
        // así que no hace falta un chequeo adicional después.

        // Una sola query (cacheada) trayendo también 'archivos' y 'acervo',
        // en vez de consultar Recursos dos veces (una suelta y otra dentro del cache).
        $recurso = Cache::remember("recurso_con_relaciones_{$id}", 1800, function () use ($id) {
            return Recursos::with([
                'archivos' => function ($q) {
                    $q->orderBy('orden');
                },
                'acervo',
                'coleccion',
            ])->findOrFail($id);
        });

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

        $omitir = ['tipo_media', 'IdElemento', 'id', 'created_at', 'updated_at', 'usuario_id', 'carpetaContenido', 'archvios', 'deleted_at', 'vistas_count', 'hash_archivo', 'assets_procesados', 'status'];

        $recursoData = $recurso->toArray();


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