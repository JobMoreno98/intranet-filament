<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Coleccion;
use App\Models\Blog;
use App\Models\Recursos;
use App\Models\TipoAcervo;
use App\ViewModels\RegistroViewModel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Meilisearch\Client as MeilisearchClient;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Meilisearch\Contracts\SearchQuery;
use Illuminate\Validation\Rule;
use App\Support\FiltroAvanzado;
use App\ViewModels\ColeccionViewModel;

class ColeccionesConsultaController extends Controller
{
    /**
     * Cliente Meilisearch compartido por los métodos que hacen búsquedas,
     * en vez de instanciar uno nuevo en cada método.
     */
    public function __construct(protected MeilisearchClient $meili) {}

    public function index(Request $request)
    {
        $areas = Area::where('fijado', true)->get()->take(8);
        $blogs = Blog::where('fijado', true)->get()->take(10);
        $novedades = Recursos::with('acervo')->where('fijado', true)->get()->take(10);
        return view('home', compact('areas', 'novedades', 'blogs'))->with(['title' => 'Inicio']);
    }

    public function show(Request $request, Coleccion $coleccion)
    {
        // Con route-model binding tipado, Laravel ya lanza 404 si $coleccion no existe.
        $viewModel = new ColeccionViewModel($request, $coleccion, $this->meili);

        // Analítica: efecto secundario, por eso se queda en el controlador
        try {
            Redis::hincrby('analytics:coleccion_vistas', $coleccion->id, 1);
            $hoy = now()->format('Y-m-d');
            Redis::incr("analytics:coleccion_vistas:{$hoy}");
        } catch (\Exception $e) {
        }

        return $viewModel;
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

        return str_replace(['&lt;em&gt;', '&lt;/em&gt;'], ['<em class="bg-amber-200 text-black font-semibold px-0.5 rounded">', '</em>'], $cleanValue);
    }
    public function buscador(Request $request)
    {
        $request->validate([
            'q' => ['required', 'string', 'min:1'],
            'acervo_id' => ['nullable', 'exists:tipo_acervos,id'],
            'alcance' => ['nullable', Rule::in(['todos', 'digitalizados'])],
        ]);

        $soloDigitalizados = $request->input('alcance') === 'digitalizados';

        return $this->procesarMultiSearch(
            term: $request->input('q'),
            filtrosBase: $soloDigitalizados ? ['archivos = true'] : [],
            request: $request,
            tituloVista: 'Búsqueda General',
            acervoId: $request->input('acervo_id'),
            // Las colecciones no tienen material digitalizado: se excluyen con este filtro
            indices: $soloDigitalizados ? ['recursos'] : ['coleccions', 'recursos'],
        );
    }
    // 2. Nuevo Método Exclusivo para Búsqueda Avanzada
    public function busquedaAvanzada(Request $request)
    {
        $r = FiltroAvanzado::construir(
            $request->input('q') ?? [],
            $request->input('fondos') ?? []
        );

        return $this->procesarMultiSearch(
            term: $r['termino'],
            filtrosBase: $r['filtros'],
            request: $request,
            tituloVista: 'Búsqueda Avanzada',
            acervoId: $request->input('acervo'),
            camposPrioritarios: $r['campos'],
            terminoMostrar: $r['descripcion'],
            indices: ['recursos'],
        );
    }
    // 3. Motor Centralizado (Privado) que ejecuta Meilisearch para ambos métodos
    private function procesarMultiSearch(
        string $term,
        array $filtrosBase,
        Request $request,
        string $tituloVista,
        $acervoId = null,
        array $camposPrioritarios = [],
        ?string $terminoMostrar = null,
        array $indices = ['coleccions', 'recursos']
    ) {
        $resultados = [];

        // Si no hay texto libre (búsqueda puramente por campo/metadata), no tiene
        // sentido consultar "coleccions" ni "paginas_index": con query vacía y sin
        // filtro aplicable ahí, Meilisearch entra en "modo browse" y devuelve
        // documentos sin relación con la búsqueda. Solo "recursos" entiende metadata.*.
        $coleccionesMeta = $indices;
        $queries = [];

        foreach ($coleccionesMeta as $meta) {
            $searchQuery = new SearchQuery()
                ->setIndexUid($meta)
                ->setQuery($term) // Funciona incluso si $term es un string vacío ""
                ->setLimit(150)
                ->setAttributesToHighlight(['*'])
                ->setAttributesToCrop(['descripcion', 'texto', 'biografia', 'ocr'])
                ->setCropLength(25);

            $filtrosLocales = $meta === 'recursos' ? $filtrosBase : [];

            // Inyectar el filtro de acervo si existe (dependiendo del índice)
            if (!empty($acervoId)) {
                if ($meta === 'recursos') {
                    $filtrosLocales[] = 'acervo_id = ' . $this->escaparFiltroMeili((string) $acervoId);
                }
            }


            if (!empty($filtrosLocales)) {
                // Unimos todos los bloques (checkboxes, filas dinámicas y acervo) con AND
                $filtroFinalString = implode(' AND ', $filtrosLocales);

                // Lo enviamos envuelto en un array
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
                            if (in_array($campo, ['id', 'IdElemento', 'parent_id', 'parent_ids', 'recursos_id', 'orden'])) {
                                continue;
                            }

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
                            $ordenCampos = !empty($camposPrioritarios) ? array_intersect_key($formatted['metadata'], array_flip($camposPrioritarios)) + $formatted['metadata'] : $formatted['metadata'];

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
                            'fondo' => $hit['fondo'] ?? null,
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
                            // BUG CORREGIDO: antes era 'página' (con tilde) y el Blade compara con
                            // 'pagina', por eso el botón "Ir a Pág." nunca se mostraba.
                            'tipo' => 'pagina',
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

        // UUID: agregamos 'registro_uuid' a los resultados de esta página (máx. 15) con UNA
        // sola consulta. Solo aplica a 'documento' y 'pagina', cuyo registro_id es un recurso;
        // en las colecciones el registro_id es de otra tabla y se enlaza por slug.
        $idsRecursos = collect($currentItems)
            ->whereIn('tipo', ['documento'])
            ->pluck('registro_id')
            ->filter()
            ->unique();

        $uuids = $idsRecursos->isNotEmpty() ? Recursos::whereIn('id', $idsRecursos)->pluck('uuid', 'id') : collect();


        $currentItems = array_map(function ($res) use ($uuids) {
            if ($res['tipo'] === 'documento') {
                $res['registro_uuid'] = $uuids[$res['registro_id']] ?? null;
            }

            return $res;
        }, $currentItems);

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

    /**
     * UUID: el recurso llega resuelto por route model binding.
     * Requisitos para que funcione:
     *  - Ruta:   Route::get('/buscador/recurso/{tipo}/{recurso}', ...)->whereUuid('recurso')
     *  - Modelo: Recursos con el trait HasUuids, uniqueIds() => ['uuid'] y getRouteKeyName() => 'uuid'
     * Si el uuid no existe, Laravel devuelve 404 solo (ya no hace falta findOrFail).
     */
    public function showRegistro(Request $request, $tipo, Recursos $recurso)
    {
        $paginaSolicitada = (int) $request->input('page', 1);

        $recurso->load([
            'archivos' => fn($q) => $q->orderBy('orden'),
            'acervo',
            'coleccion',
        ]);

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
            Redis::incr('analytics:recursos_vistas:{' . now()->format('Y-m-d') . '}');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error registrando analítica: ' . $e->getMessage());
        }

        return new RegistroViewModel($recurso, $paginaSolicitada);
    }
}
