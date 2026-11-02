<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recursos;
use Illuminate\Support\Facades\Log;
use Meilisearch\Contracts\SearchQuery;
use Meilisearch\Client as MeilisearchClient;

class BuscadorController extends Controller
{

    public function __construct(protected MeilisearchClient $meili) {}
    public function buscarEnRecurso(Request $request, Recursos $recurso)
    {
        abort_unless($recurso->visible, 404);

        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:100'],
        ]);

        $query = (new SearchQuery())
            ->setIndexUid('paginas_index')
            ->setQuery($validated['q'])
            ->setFilter(['recursos_id = ' . (int) $recurso->id])
            ->setSort(['orden:asc'])
            ->setLimit(200)
            ->setAttributesToRetrieve(['orden', 'ocr'])
            ->setAttributesToHighlight(['ocr'])
            ->setAttributesToCrop(['ocr'])
            ->setCropLength(25);

        try {
            $response = $this->meili->multiSearch([$query]);
            $results = is_array($response) ? $response['results'] : $response->toArray()['results'];
            $hits = $results[0]['hits'] ?? [];
        } catch (\Exception $e) {
            Log::error('Error buscando en OCR del recurso: ' . $e->getMessage(), [
                'recurso_id' => $recurso->id,
            ]);

            return response()->json(['message' => 'No se pudo realizar la búsqueda.'], 503);
        }

        $paginas = collect($hits)->map(function ($hit) {
            $texto = $hit['_formatted']['ocr'] ?? '';

            // El OCR es texto no confiable: se escapa todo y solo se restauran las marcas <em> de Meilisearch
            $snippet = str_replace(['&lt;em&gt;', '&lt;/em&gt;'], ['<em>', '</em>'], e($texto));

            return [
                'orden' => $hit['orden'] ?? null,
                'snippet' => $snippet,
            ];
        })->values();

        return response()->json([
            'total' => $paginas->count(),
            'paginas' => $paginas,
        ]);
    }
}
