<?php

namespace App\ViewModels;

use App\Models\Coleccion;
use App\Models\Recursos;
use App\Models\TipoAcervo;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Meilisearch\Contracts\SearchQuery;
use Spatie\ViewModels\ViewModel;

class ColeccionViewModel extends ViewModel
{
    private const OPCIONES_POR_PAGINA = [5, 10, 25, 50];
    private const POR_PAGINA_DEFECTO = 10;
    private const MAX_COLUMNAS = 6;

    // Respaldo estético: se usa solo si el esquema del acervo no trae el label
    private const ETIQUETAS_BASE = [
        'anio' => 'Año',
        'anio_2' => 'Año 2',
        'tipoarchivo' => 'Tipo Archivo',
        'numpaginas' => 'No. Páginas',
        'numarchivos' => 'No. Archivos',
        'numero' => 'Número',
        'titulo' => 'Título',
        'autor' => 'Autor',
        'dia' => 'Día',
        'paginas' => 'Páginas',
        'epocaperiodo' => 'Epoca o Periodo',
        'personaje_principal' => 'Nombre de Personaje principal',
        'personaje_secundario' => 'Nombre de Personaje secundario',
        'clavefondoprincipal' => 'Clave Fondo Principal',
        'fondoprincipal' => 'Fondo principal',
        'lugar' => 'Lugar',
        'lugar_2' => 'Lugar 2',
        'numinventario' => 'No. Inventario',
        'observaciones' => 'Observaciones',
        'observaciones_2' => 'Observaciones 2',
        'observaciones_3' => 'Observaciones 3',
        'volumentomoejemplar' => 'Volumen / Tomo / Ejemplar',
        'descripcion_fisica' => 'Descripción Física',
    ];

    // Llaves de control internas que no deben mostrarse como columnas
    private const CLAVES_IGNORADAS = ['id', 'created_at', 'updated_at', 'deleted_at', 'estatus'];

    private $acervosDisponiblesProcesados;
    private array $esquema = [];
    private array $columnasPrimarias = [];
    private array $etiquetas = [];
    private array $columnasVisibles = [];
    private bool $tieneMasColumnasProcesado = false;
    private array $filasProcesadas = [];
    private int $totalHits = 0;
    private LengthAwarePaginator $paginador;
    private int $porPagina = self::POR_PAGINA_DEFECTO;

    /**
     * @param object $meili Cliente de Meilisearch (el mismo que usas en $this->meili del controlador)
     */
    public function __construct(
        private Request $request,
        private Coleccion $coleccion,
        private object $meili,
    ) {
        $this->view('coleccion');
        $this->porPagina = $this->resolverPorPagina();

        $acervoId = $this->request->filled('acervo_id') ? (int) $this->request->input('acervo_id') : null;
        $paginaActual = LengthAwarePaginator::resolveCurrentPage();

        $this->acervosDisponiblesProcesados = $this->coleccion->items()
            ->whereNotNull('acervo_id')
            ->select('acervo_id')
            ->distinct()
            ->with('acervo')
            ->get();

        $this->cargarEsquema($acervoId);
        $resultados = $this->buscar($acervoId, $paginaActual);
        $this->adjuntarUuids($resultados);

        $this->paginador = new LengthAwarePaginator(
            $resultados,
            $this->totalHits,
            $this->porPagina,
            $paginaActual,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );
        $this->paginador->appends($this->request->all())->onEachSide(0);

        $this->procesarTabla($resultados);
    }

    /* ---------------------------------------------------------------
     | Métodos públicos: cada uno llega a la vista como variable
     | ($title, $data, $columnas, $filas, ...)
     --------------------------------------------------------------- */

    public function title(): string
    {
        return $this->coleccion->ruta ?? 'Colección';
    }

    /** Descripción del fondo en texto plano (conserva saltos de línea), o null si no hay */
    public function descripcionFondo(): ?string
    {
        $html = (string) ($this->coleccion->descripcion ?? '');
        $html = preg_replace('/<\/p>|<br\s*\/?>/i', "\n", $html);
        $texto = html_entity_decode(strip_tags($html), ENT_QUOTES, 'UTF-8');
        $texto = trim(preg_replace("/\n{3,}/", "\n\n", $texto));

        return $texto !== '' ? $texto : null;
    }

    public function acervosDisponibles()
    {
        return $this->acervosDisponiblesProcesados;
    }

    public function data(): LengthAwarePaginator
    {
        return $this->paginador;
    }

    /** Etiquetas legibles de las columnas visibles (en orden) */
    public function columnas(): array
    {
        return array_map(fn($k) => $this->etiqueta($k), $this->columnasVisibles);
    }

    public function tieneMasColumnas(): bool
    {
        return $this->tieneMasColumnasProcesado;
    }

    public function porPagina(): int
    {
        return $this->porPagina;
    }

    public function opcionesPorPagina(): array
    {
        return self::OPCIONES_POR_PAGINA;
    }

    /**
     * Cada fila: index, acervo, urlVer, celdas[], detalle[ [label, valor] ]
     */
    public function filas(): array
    {
        return $this->filasProcesadas;
    }

    /* ---------------------------------------------------------------
     | Lógica privada
     --------------------------------------------------------------- */

    private function cargarEsquema(?int $acervoId): void
    {
        if (!$acervoId) {
            return;
        }

        // Misma llave de caché que usabas en el controlador; se cachea solo el esquema
        $cacheado = Cache::remember("tipo_acervo_esquema_{$acervoId}", 3600, function () use ($acervoId) {
            $tipoAcervo = TipoAcervo::find($acervoId);

            return $tipoAcervo ? $tipoAcervo->esquema : null;
        });

        if (!$cacheado) {
            return;
        }

        $esquema = is_string($cacheado) ? json_decode($cacheado, true) : (array) $cacheado;
        $this->esquema = is_array($esquema) ? $esquema : [];

        // Columnas "primarias" = campos Recuperables (los mismos de filter-form)
        $this->columnasPrimarias = collect($this->esquema)
            ->filter(fn($item) => ($item['visible'] ?? null) === 'Recuperable')
            ->pluck('variable')
            ->filter()
            ->values()
            ->toArray();

        // Labels: el esquema manda, ETIQUETAS_BASE es el respaldo
        $delEsquema = [];
        foreach ($this->esquema as $campo) {
            if (!empty($campo['variable']) && !empty($campo['label'])) {
                $delEsquema[strtolower($campo['variable'])] = $campo['label'];
            }
        }

        $this->etiquetas = array_merge(
            array_change_key_case(self::ETIQUETAS_BASE, CASE_LOWER),
            $delEsquema
        );
    }

    private function buscar(?int $acervoId, int $paginaActual): array
    {
        $term = $this->request->input('q', '');

        // Filtro base obligatorio
        $filtros = ["coleccion_id = {$this->coleccion->id}"];

        if ($acervoId) {
            $filtros[] = "acervo_id = {$acervoId}";

            foreach ($this->request->only($this->columnasPrimarias) as $campo => $valor) {
                if (is_string($valor) && $valor !== '') {
                    $filtros[] = "metadata.{$campo} CONTAINS \"" . $this->escaparFiltroMeili($valor) . '"';
                }
            }
        }

        $resultados = [];

        try {
            // Solo pedimos la página actual (offset/limit nativos de Meilisearch)
            $consulta = (new SearchQuery())
                ->setIndexUid('recursos')
                ->setQuery($term)
                ->setOffset(($paginaActual - 1) * $this->porPagina)
                ->setLimit($this->porPagina)
                ->setFilter($filtros);

            $response = $this->meili->multiSearch([$consulta]);
            $results = is_array($response) ? $response['results'] : $response->toArray()['results'];

            foreach ($results as $indexResult) {
                $this->totalHits = $indexResult['estimatedTotalHits']
                    ?? ($indexResult['totalHits'] ?? count($indexResult['hits'] ?? []));

                foreach ($indexResult['hits'] ?? [] as $hit) {
                    $resultados[] = (object) [
                        'id' => $hit['id'],
                        'uuid' => null,
                        'acervo_id' => $hit['acervo_id'] ?? null,
                        'coleccion_id' => $hit['coleccion_id'] ?? null,
                        'metadata' => $hit['metadata_text'] ?? '',
                        'tipo_media' => $hit['tipo_media'] ?? null,
                        'status' => $hit['status'] ?? null,
                        'acervo' => (object) ['nombre' => $hit['acervo'] ?? '---'],
                        'coleccion' => (object) ['nombre' => $hit['coleccion'] ?? '---'],
                        'archivos' => $hit['archivos'] ?? [],
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('Error en búsqueda automática por Acervo en Meilisearch: ' . $e->getMessage());
        }

        return $resultados;
    }

    /** Una sola consulta para los (máx. 14) resultados, en vez de una por fila */
    private function adjuntarUuids(array $resultados): void
    {
        if (empty($resultados)) {
            return;
        }

        $uuids = Recursos::whereIn('id', collect($resultados)->pluck('id'))->pluck('uuid', 'id');

        foreach ($resultados as $item) {
            $item->uuid = $uuids[$item->id] ?? null;
        }
    }

    private function procesarTabla(array $resultados): void
    {
        // Las columnas se deducen de la metadata del primer resultado
        $primero = $resultados[0] ?? null;
        $clavesPrimero = $primero ? array_keys($this->parsearMetadata($primero->metadata)) : [];
        $clavesPrimero = array_diff($clavesPrimero, self::CLAVES_IGNORADAS);

        $resto = array_values(array_diff($clavesPrimero, $this->columnasPrimarias));
        sort($resto);

        $ordenTotal = array_merge($this->columnasPrimarias, $resto);

        $this->columnasVisibles = array_slice($ordenTotal, 0, self::MAX_COLUMNAS);
        $this->tieneMasColumnasProcesado = count($ordenTotal) > self::MAX_COLUMNAS;

        foreach ($resultados as $index => $registro) {
            $meta = $this->parsearMetadata($registro->metadata);

            // Celdas de la tabla (solo columnas visibles)
            $celdas = array_map(
                fn($k) => $this->tieneValor($meta[$k] ?? null) ? $meta[$k] : '---',
                $this->columnasVisibles
            );

            // Detalle del modal (todas las columnas con valor)
            $detalle = [];
            foreach ($ordenTotal as $k) {
                if ($this->tieneValor($meta[$k] ?? null)) {
                    $detalle[] = ['label' => $this->etiqueta($k), 'valor' => $meta[$k]];
                }
            }

            $this->filasProcesadas[] = [
                'index' => $index,
                'acervo' => $registro->acervo->nombre,
                'urlVer' => (!empty($registro->archivos) && $registro->uuid)
                    ? route('buscador.registro', ['tipo' => 'documento', 'recurso' => $registro->uuid])
                    : null,
                'celdas' => $celdas,
                'detalle' => $detalle,
            ];
        }
    }

    /** "clave:valor|clave:valor" => ['clave' => 'valor', ...] */
    private function parsearMetadata($metadata): array
    {
        if (!is_string($metadata) || $metadata === '') {
            return [];
        }

        $resultado = [];
        foreach (explode('|', $metadata) as $par) {
            if (!str_contains($par, ':')) {
                continue;
            }
            [$clave, $valor] = explode(':', $par, 2);
            $resultado[trim($clave)] = trim($valor);
        }

        return $resultado;
    }

    private function tieneValor($valor): bool
    {
        return $valor !== null && $valor !== '' && $valor !== '-';
    }

    private function etiqueta(string $clave): string
    {
        return $this->etiquetas[strtolower($clave)]
            ?? self::ETIQUETAS_BASE[strtolower($clave)]
            ?? $clave;
    }

    /** Solo se aceptan los valores de la lista; cualquier otro cae al defecto */
    private function resolverPorPagina(): int
    {
        $pedido = (int) $this->request->input('per_page');

        return in_array($pedido, self::OPCIONES_POR_PAGINA, true)
            ? $pedido
            : self::POR_PAGINA_DEFECTO;
    }

    /** Si tu controlador tenía una versión más completa, cópiala aquí */
    private function escaparFiltroMeili(string $valor): string
    {
        return str_replace(['\\', '"'], ['\\\\', '\\"'], $valor);
    }
}