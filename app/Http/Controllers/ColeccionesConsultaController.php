<?php

namespace App\Http\Controllers;

use App\Models\ColeccionesConsulta;
use App\Models\Recursos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Meilisearch\Client as MeilisearchClient;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Meilisearch\Contracts\SearchQuery;

class ColeccionesConsultaController extends Controller
{
    public function index(Request $request)
    {
        $colecciones = DB::connection('mysql2')
            ->table('colecciones')
            ->select('clave', 'coleccion')
            ->distinct('clave')
            ->when($request->filled('coleccion'), function ($query) use ($request) {
                $query->where('coleccion', 'like', '%' . $request->coleccion . '%');
            })
            ->orderBy('coleccion')
            ->paginate(15);
        //dd($colecciones);
        return view('home', compact('colecciones'))->with(['title' => 'Inicio']);
    }

    public function show(Request $request, $id)
    {
        $nombreTabla = DB::connection('mysql2')->table('colecciones')->select('tabla', 'coleccion')->where('clave', $id)->first();

        if (!$nombreTabla) {
            abort(404, 'La colección no existe.');
        }

        // 2. Iniciar la consulta sobre esa tabla
        $query = DB::connection('mysql2')->table($nombreTabla->tabla);

        // Buscamos en la configuración qué campos están permitidos para esta tabla
        $camposPermitidos = DB::connection('mysql2')->table('colecciones')->where('tabla', $nombreTabla->tabla)->pluck('campo')->toArray();

        foreach ($request->only($camposPermitidos) as $campo => $valor) {
            if ($valor !== null && $valor !== '') {
                $query->where($campo, 'LIKE', "%{$valor}%");
            }
        }

        $data = $query->paginate(14)->appends($request->all())->onEachSide(0);

        return view('coleccion', [
            'data' => $data,
            'tablaNombre' => $nombreTabla->tabla,
            'id' => $id,
            'title' => $nombreTabla->coleccion,
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
            $coleccionesMeta = DB::connection('mysql2')->table('colecciones')->select('tabla', 'coleccion', 'clave')->orderBy('coleccion')->distinct('tabla')->get()->keyBy('tabla');

            // 2. Construir las consultas usando las clases oficiales del SDK
            $queries = [];
            foreach ($coleccionesMeta as $tablaNombre => $meta) {
                $searchQuery = new SearchQuery()
                    ->setIndexUid($tablaNombre)
                    ->setQuery($term)
                    ->setLimit(20)
                    ->setAttributesToHighlight(['*']);

                $queries[] = $searchQuery;
            }

            try {
                // 3. Enviamos el lote unificado de objetos Query a Meilisearch
                $response = $meili->multiSearch($queries);

                // 4. Procesar resultados de forma segura según el formato devuelto
                $results = is_array($response) ? $response['results'] : $response->getResults();

                foreach ($results as $indexResult) {
                    // Evaluamos si la respuesta del SDK viene mapeada como array u objeto estructurado
                    $tabla = is_array($indexResult) ? $indexResult['indexUid'] : $indexResult->getIndexUid();
                    $hits = is_array($indexResult) ? $indexResult['hits'] : $indexResult->getHits();

                    $meta = $coleccionesMeta->get($tabla);

                    foreach ($hits as $hit) {
                        // Buscamos la sección de texto resaltada por Meilisearch <em>
                        $snippet = 'Coincidencia encontrada';
                        $formatted = $hit['_formatted'] ?? [];

                        if (!empty($formatted)) {
                            foreach ($formatted as $campo => $valorFormateado) {
                                if (str_contains($valorFormateado, '<em>') && $campo !== 'id' && $campo !== 'IdElemento') {
                                    $snippet = 'En [' . ucfirst($campo) . ']: ...' . $valorFormateado . '...';
                                    break;
                                }
                            }
                        }

                        $resultados[] = [
                            'coleccion_id' => $meta->clave ?? null,
                            'coleccion_nombre' => $meta->coleccion ?? $tabla,
                            'tabla' => $tabla,
                            'titulo_resultado' => $hit['titulo'] ?? ($hit['nombre'] ?? ($hit['coleccion'] ?? 'Registro ID: ' . ($hit['IdElemento'] ?? 'N/A'))),
                            'coincidencia' => $snippet,
                            'registro_id' => $hit['IdElemento'] ?? null,
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error en búsqueda Meilisearch: ' . $e->getMessage());
            }
        }

        $perPage = 15;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = array_slice($resultados, ($currentPage - 1) * $perPage, $perPage);

        $paginados = new LengthAwarePaginator($currentItems, count($resultados), $perPage, $currentPage, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
        ]);

        $paginados->appends($request->all());

        return view('respuestas', [
            'resultados' => $paginados,
            'term' => $term,
            'title' => 'Búsqueda General',
        ]);
    }
    public function showRegistro($tabla, $id)
    {
        // 1. Validar que la tabla exista por seguridad
        if (!Schema::connection('mysql2')->hasTable($tabla)) {
            abort(404, 'La tabla no existe.');
        }

        // 2. Traer la metadata de la colección
        $meta = DB::connection('mysql2')->table('colecciones')->where('tabla', $tabla)->first();

        // 3. Buscar el registro completo usando su llave 'IdElemento'
        $registro = DB::connection('mysql2')->table($tabla)->where('IdElemento', $id)->first();

        if (!$registro) {
            abort(404, 'El registro no fue encontrado.');
        }
        $omitir = ['IdElemento', 'id', 'created_at', 'updated_at', 'usuario_id', 'carpetaContenido'];

        $id = 2;
        // 1. Cacheamos solo el array de datos, no el modelo vivo
        $recursoData = Cache::remember("recurso_view_data_{$id}", 1800, function () use ($id) {
            $recurso = Recursos::with([
                'archivos' => function ($q) {
                    $q->orderBy('orden');
                },
            ])->findOrFail($id);

            // Convertimos a array para evitar problemas de serialización
            return $recurso->toArray();
        });

        // 2. Como ahora $recursoData es un array, accedemos con corchetes []
        $paginas = collect($recursoData['archivos'])
            ->map(function ($archivo) {
                $payload = [
                    'a' => $archivo['id'],
                    'u' => auth()->id(),
                    'e' => now()->timestamp + 3600,
                ];

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
            'registro' => $registro,
            'tablaNombre' => $tabla,
            'coleccionNombre' => $meta->coleccion ?? $tabla,
            'labels' => $labels,
            'title' => 'Detalle del Registro',
            'omitir' => $omitir,
            'paginas' => $paginas,
            'recurso' => $recursoData,
        ]);
    }
}
