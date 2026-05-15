<?php

namespace App\Http\Controllers;

use App\Models\ColeccionesConsulta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Meilisearch\Client as MeilisearchClient;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;


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
        /*
        foreach ($colecciones as $key => $item) {
            //$titulo = DB::connection('mysql2')->table($item->tabla)->first();
            dd($item);
            // URL pública temporal
            $item->public_url = URL::temporarySignedRoute(

                'visor.publico',

                now()->addHours(1),

                [
                    'recurso' => $item->clave
                ]
            );
        }
            */
        //dd($colecciones);
        return view('home', compact('colecciones'))->with(['title' => 'Inicio']);
    }

    public function show(Request $request, $id)
    {
        $nombreTabla = DB::connection('mysql2')
            ->table('colecciones')->select('tabla', 'coleccion')
            ->where('clave', $id)
            ->first();

        if (!$nombreTabla) {
            abort(404, "La colección no existe.");
        }

        // 2. Iniciar la consulta sobre esa tabla
        $query = DB::connection('mysql2')->table($nombreTabla->tabla);

        // Buscamos en la configuración qué campos están permitidos para esta tabla
        $camposPermitidos = DB::connection('mysql2')->table('colecciones')
            ->where('tabla', $nombreTabla->tabla)
            ->pluck('campo')
            ->toArray();

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
            'title' => $nombreTabla->coleccion
        ]);
    }


    public function buscador(Request $request)
    {
        $term = $request->input('q');
        $resultados = [];

        if ($request->filled('q')) {
            // 1. Inicializamos el cliente leyendo de tu .env de forma segura
            $meili = new MeilisearchClient(
                config('scout.meilisearch.host'),
                config('scout.meilisearch.key')
            );

            // Obtener la metadata de las colecciones desde tu MySQL
            $coleccionesMeta = DB::connection('mysql2')
                ->table('colecciones')
                ->select('tabla', 'coleccion', 'clave')
                ->distinct('tabla')
                ->get()
                ->keyBy('tabla');

            // 2. Construir las consultas usando las clases oficiales del SDK
            $queries = [];
            foreach ($coleccionesMeta as $tablaNombre => $meta) {
                // Creamos una instancia de búsqueda individual con sus parámetros configurados
                $searchQuery = (new \Meilisearch\Contracts\SearchQuery())
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
                    $hits  = is_array($indexResult) ? $indexResult['hits'] : $indexResult->getHits();

                    $meta = $coleccionesMeta->get($tabla);

                    foreach ($hits as $hit) {
                        // Buscamos la sección de texto resaltada por Meilisearch <em>
                        $snippet = 'Coincidencia encontrada';
                        $formatted = $hit['_formatted'] ?? [];

                        if (!empty($formatted)) {
                            foreach ($formatted as $campo => $valorFormateado) {
                                if (str_contains($valorFormateado, '<em>') && $campo !== 'id' && $campo !== 'IdElemento') {
                                    $snippet = "En [" . ucfirst($campo) . "]: ..." . $valorFormateado . "...";
                                    break;
                                }
                            }
                        }

                        $resultados[] = [
                            'coleccion_id'     => $meta->clave ?? null,
                            'coleccion_nombre' => $meta->coleccion ?? $tabla,
                            'tabla'            => $tabla,
                            'titulo_resultado' => $hit['titulo'] ?? $hit['nombre'] ?? $hit['coleccion'] ?? "Registro ID: " . ($hit['IdElemento'] ?? 'N/A'),
                            'coincidencia'     => $snippet,
                            'registro_id'      => $hit['IdElemento'] ?? null
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::error("Error en búsqueda Meilisearch: " . $e->getMessage());
            }
        }

        // 5. Paginación del array resultante para la vista
        $perPage = 15;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = array_slice($resultados, ($currentPage - 1) * $perPage, $perPage);

        $paginados = new LengthAwarePaginator($currentItems, count($resultados), $perPage, $currentPage, [
            'path' => LengthAwarePaginator::resolveCurrentPath()
        ]);
        $paginados->appends($request->all());

        return view('respuestas', [
            'resultados' => $paginados,
            'term' => $term,
            'title' => 'Búsqueda General'
        ]);
    }
}
