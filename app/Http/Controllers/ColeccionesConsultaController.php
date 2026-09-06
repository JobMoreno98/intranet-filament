<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Coleccion;
use App\Models\ColeccionesConsulta;
use App\Models\Recursos;
use App\Models\TipoAcervo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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

            // Cacheamos SOLO el esquema (que es un string o array), no el modelo completo
            $esquemaCacheado = Cache::remember("tipo_acervo_esquema_{$acervoIdActual}", 3600, function () use ($acervoIdActual) {
                $tipoAcervo = TipoAcervo::find($acervoIdActual);
                return $tipoAcervo ? $tipoAcervo->esquema : null;
            });

            if ($esquemaCacheado) {
                // Decodificamos de forma segura
                $esquema = is_string($esquemaCacheado) ? json_decode($esquemaCacheado, true) : (array) $esquemaCacheado;

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

        return $this->procesarMultiSearch(
            term: $request->input('q'),
            filtrosBase: [],
            request: $request,
            tituloVista: 'Búsqueda General',
            acervoId: $request->input('acervo_id')
        );
    }

    // 2. Nuevo Método Exclusivo para Búsqueda Avanzada
    public function busquedaAvanzada(Request $request)
    {
        $request->validate([
            'q' => ['nullable', 'array'],
            'tipos' => ['nullable', 'array'],
            'fondos' => ['nullable', 'array'],
        ]);

        $filas = $request->input('q') ?? [];
        $filtrosAvanzados = [];
        $terminosGlobales = [];
        $camposPrioritarios = [];
        $terminosDescriptivos = [];

        // Filtros de Tipos Documentales
        if ($request->filled('tipos') && !in_array('Todos', $request->input('tipos'))) {
            $tipos = array_map(fn($t) => "acervo = \"" . $this->escaparFiltroMeili($t) . "\"", $request->input('tipos'));
            $filtrosAvanzados[] = "(" . implode(" OR ", $tipos) . ")";
        }

        // Filtros de Fondos
        if ($request->filled('fondos') && !in_array('Todos', $request->input('fondos'))) {
            $fondos = array_map(fn($f) => "coleccion = \"" . $this->escaparFiltroMeili($f) . "\"", $request->input('fondos'));
            $filtrosAvanzados[] = "(" . implode(" OR ", $fondos) . ")";
        }

        // Procesar TODAS las filas (sin ignorar la 0)
        $stringFilas = "";
        foreach ($filas as $fila) {
            if (empty($fila['termino']))
                continue;

            if ($fila['campo'] === 'all') {
                // Se acumulan para la búsqueda global (Full-Text)
                $terminosGlobales[] = $fila['termino'];
                $terminosDescriptivos[] = $fila['termino'];
            } else {
                // Se envían como filtros específicos de Meilisearch
                $campo = "metadata.{$fila['campo']}";
                $condicion = "{$campo} CONTAINS \"" . $this->escaparFiltroMeili($fila['termino']) . "\"";

                // Los filtros no generan highlighting (<em>) en Meilisearch, solo la
                // búsqueda de texto lo hace. Mandamos el mismo término como texto
                // libre para que el snippet muestre en qué campo se encontró.
                $terminosGlobales[] = $fila['termino'];
                $camposPrioritarios[] = $fila['campo'];
                $terminosDescriptivos[] = ucfirst($fila['campo']) . ': ' . $fila['termino'];

                if (empty($stringFilas)) {
                    $stringFilas = $fila['operador'] === 'NOT' ? "NOT {$condicion}" : $condicion;
                } else {
                    $operador = $fila['operador'] === 'NOT' ? 'AND NOT' : $fila['operador'];
                    $stringFilas .= " {$operador} {$condicion}";
                }
            }
        }

        if (!empty($stringFilas)) {
            $filtrosAvanzados[] = "({$stringFilas})";
        }

        // Unimos múltiples términos globales con un espacio (si eligieron 'all' varias veces)
        $term = implode(" ", $terminosGlobales);

        return $this->procesarMultiSearch(
            term: $term,
            filtrosBase: $filtrosAvanzados,
            request: $request,
            tituloVista: 'Búsqueda Avanzada',
            camposPrioritarios: array_unique($camposPrioritarios),
            terminoMostrar: implode(', ', $terminosDescriptivos)
        );
    }
    // 3. Motor Centralizado (Privado) que ejecuta Meilisearch para ambos métodos
    private function procesarMultiSearch(string $term, array $filtrosBase, Request $request, string $tituloVista, $acervoId = null, array $camposPrioritarios = [], ?string $terminoMostrar = null)
    {
        $resultados = [];

        // Si no hay texto libre (búsqueda puramente por campo/metadata), no tiene
        // sentido consultar "coleccions" ni "paginas_index": con query vacía y sin
        // filtro aplicable ahí, Meilisearch entra en "modo browse" y devuelve
        // documentos sin relación con la búsqueda. Solo "recursos" entiende metadata.*.
        $coleccionesMeta = trim($term) !== ''
            ? ['coleccions', 'recursos', 'paginas_index']
            : ['recursos'];

        $queries = [];

        foreach ($coleccionesMeta as $meta) {
            $searchQuery = (new SearchQuery())
                ->setIndexUid($meta)
                ->setQuery($term) // Funciona incluso si $term es un string vacío ""
                ->setLimit(150)
                ->setAttributesToHighlight(['*'])
                ->setAttributesToCrop(['descripcion', 'texto', 'biografia', 'ocr'])
                ->setCropLength(25);

            // Los filtros avanzados (tipos, fondos, metadata.*) usan campos que
            // solo existen en el esquema del índice "recursos". Aplicarlos a
            // "coleccions" o "paginas_index" rompe el multiSearch completo,
            // porque Meilisearch valida todo el batch antes de ejecutar nada.
            $filtrosLocales = $meta === 'recursos' ? $filtrosBase : [];

            // Inyectar el filtro de acervo si existe (dependiendo del índice)
            if (!empty($acervoId)) {
                if ($meta === 'recursos') {
                    $filtrosLocales[] = "acervo_id = " . $this->escaparFiltroMeili((string) $acervoId);
                } elseif ($meta === 'paginas_index') {
                    $filtrosLocales[] = "libro_acervo_id = " . $this->escaparFiltroMeili((string) $acervoId);
                }
            }

            if (!empty($filtrosLocales)) {
                // Unimos todos los bloques (checkboxes, filas dinámicas y acervo) con AND
                $filtroFinalString = implode(' AND ', $filtrosLocales);

                // Lo enviamos envuelto en un array para satisfacer el requerimiento de la librería
                $searchQuery->setFilter([$filtroFinalString]);
            }

            $queries[] = $searchQuery;
        }

        try {
            $response = $this->meili->multiSearch($queries);
            $results = is_array($response) ? $response['results'] : $response->toArray()['results'];

            foreach ($results as $indexResult) {
                $hits = $indexResult['hits'] ?? [];
                $indexUid = $indexResult['indexUid'] ?? 'desconocido';

                foreach ($hits as $hit) {
                    $formatted = $hit['_formatted'] ?? [];
                    $descripcionBase = $hit['descripcion'] ?? ($hit['resumen'] ?? 'Coincidencia encontrada');
                    $snippet = Str::limit($descripcionBase, 140);

                    if (!empty($formatted)) {
                        foreach ($formatted as $campo => $valorFormateado) {
                            if (in_array($campo, ['id', 'IdElemento', 'parent_id', 'parent_ids', 'recursos_id', 'orden']))
                                continue;

                            if ($campo === 'parent_names' && is_array($valorFormateado)) {
                                foreach ($valorFormateado as $nombrePadre) {
                                    if (str_contains($nombrePadre, '<em>')) {
                                        $snippet = 'Perteneciente a la colección padre: ... ' . $this->resaltarCoincidencia($nombrePadre) . ' ...';
                                        break 2;
                                    }
                                }
                            }

                            if (is_string($valorFormateado) && str_contains($valorFormateado, '<em>')) {
                                $nombreCampo = $campo === 'ocr' ? 'Página ' . ($hit['orden'] ?? '') : ucfirst($campo);
                                $snippet = 'En [' . $nombreCampo . ']: ... ' . $this->resaltarCoincidencia($valorFormateado) . ' ...';
                                break;
                            }
                        }

                        if (isset($formatted['metadata']) && is_array($formatted['metadata'])) {
                            // Primero revisamos los campos que el usuario eligió explícitamente
                            // en la búsqueda avanzada (ej. "titulo"), antes de aceptar cualquier
                            // otro campo de metadata que también haya resultado resaltado.
                            $ordenCampos = !empty($camposPrioritarios)
                                ? array_intersect_key(
                                    $formatted['metadata'],
                                    array_flip($camposPrioritarios)
                                ) + $formatted['metadata']
                                : $formatted['metadata'];

                            foreach ($ordenCampos as $clave => $valorFormateado) {
                                if (is_string($valorFormateado) && str_contains($valorFormateado, '<em>')) {
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
                    } elseif ($indexUid === 'recursos') {
                        $resultados[] = [
                            'index' => $indexUid,
                            'acervo' => $hit['acervo'] ?? null,
                            'coleccion' => $hit['coleccion'] ?? null,
                            'tipo' => 'documento',
                            'titulo_resultado' => $hit['titulo'] ?? ($hit['nombre'] ?? 'Registro sin título'),
                            'coincidencia' => $snippet,
                            'registro_id' => $hit['id'] ?? null,
                            'slug' => $hit['slug'] ?? null,
                            'metadata' => $hit['metadata'] ?? [],
                        ];
                    } elseif ($indexUid === 'paginas_index') {
                        $resultados[] = [
                            'index' => $indexUid,
                            'tipo' => 'página',
                            'titulo_resultado' => 'Coincidencia en el texto del documento',
                            'coincidencia' => $snippet,
                            'registro_id' => $hit['recursos_id'] ?? null,
                            'orden_pagina' => $hit['orden'] ?? null,
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


        $perPage = 15;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = array_slice($resultados, ($currentPage - 1) * $perPage, $perPage);

        $paginados = new LengthAwarePaginator($currentItems, count($resultados), $perPage, $currentPage, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
        ]);

        $paginados->appends($request->all())->onEachSide(0);

        return view('respuestas', [
            'resultados' => $paginados,
            'term' => $terminoMostrar ?? $term,
            'title' => $tituloVista,
        ]);
    }

    public function showRegistro(Request $request, $tipo, $id)
    {
        // NUEVO: Capturamos la página solicitada desde la URL (ej. ?page=4). Si no viene, por defecto es la 1.
        $paginaSolicitada = $request->input('page', 1);

        $recurso = Recursos::with([
            'archivos' => function ($q) {
                $q->orderBy('orden');
            },
            'acervo',
            'coleccion',
        ])->findOrFail($id);

        try {
            Redis::hincrby('analytics:recursos_vistas', $recurso->id, 1);

            $payload = json_encode([
                'ip' => $request->ip() ?? $request->header('X-Forwarded-For'),
                'user_agent' => $request->userAgent(),
                'url' => $request->getRequestUri(),
                'user_id' => auth()->id(),
                'created_at' => now()->toDateTimeString(),
            ]);

            Redis::rpush('analytics:visitas_queue', $payload);

            $hoy = now()->format('Y-m-d');
            Redis::incr("analytics:recursos_vistas:{$hoy}");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error registrando analítica en visor: ' . $e->getMessage());
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

        $paginas = collect($recursoData['archivos'])
            ->map(function ($archivo) {
                $payload = [
                    'a' => $archivo['id'],
                    'u' => auth()->id(),
                    'e' => now()->timestamp + 300,
                ];

                $token = encrypt(json_encode($payload));

                return [
                    'id' => $archivo['id'],
                    // NUEVO: Agregamos el orden (número de página real) al JSON que recibe el frontend
                    'orden' => $archivo['orden'] ?? 1,
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
            // NUEVO: Pasamos la variable de la página a la vista
            'paginaSolicitada' => (int) $paginaSolicitada,
        ]);
    }
}