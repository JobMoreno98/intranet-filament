<?php
namespace App\ViewModels;

use Spatie\ViewModels\ViewModel;
use Illuminate\Database\Eloquent\Model;

class RegistroViewModel extends ViewModel
{
    private array $generalesProcesados = [];
    private array $largosProcesados = [];
    private array $metadataProcesada = [];
    private string $tituloCalculado = 'Registro sin título';
    private array $paginasProcesadas = [];

    public function __construct(
        public Model $recurso,
        public int $paginaSolicitada = 1
    ) {
        $this->configurarVistaYArchivos();
        $this->procesarDatos();
    }

    public function coleccionNombre(): string
    {
        return $this->recurso->coleccion->nombre ?? '';
    }

    public function tablaNombre(): string
    {
        return $this->recurso->coleccion ?? '';
    }

    public function title(): string
    {
        return 'Detalle del Registro';
    }

    public function tituloPrincipal(): string
    {
        return $this->tituloCalculado;
    }

    public function generales(): array
    {
        return $this->generalesProcesados;
    }

    public function largos(): array
    {
        return $this->largosProcesados;
    }

    public function metadataList(): array
    {
        return $this->metadataProcesada;
    }

    public function paginas(): array
    {
        return $this->paginasProcesadas;
    }



    private function configurarVistaYArchivos(): void
    {
        if ($this->recurso->archivos->isEmpty() || !$this->recurso->visible) {
            $this->view('registro-info');
        } else {
            $this->view('registro-detalle');
            
            $this->paginasProcesadas = $this->recurso->archivos->map(function ($archivo) {
                $token = encrypt(json_encode([
                    'a' => $archivo['id'],
                    'u' => auth()->id(),
                    'e' => now()->timestamp + 300
                ]));

                return [
                    'id' => $archivo['id'],
                    'orden' => $archivo['orden'] ?? 1,
                    'url' => route('media.stream', ['token' => $token]),
                    'w' => 1200,
                    'h' => 1600,
                    'ocrUrl' => route('visor.ocr', ['token' => $token]),
                ];
            })->toArray();
        }
    }

    private function procesarDatos(): void
    {
        $omitir = [
            'tipo_media', 'IdElemento', 'id', 'uuid', 'created_at', 'updated_at', 
            'usuario_id', 'carpetaContenido', 'archivos', 'deleted_at', 'vistas_count', 
            'hash_archivo', 'assets_procesados', 'status', 'fijado', 'visible'
        ];

        // 1. Obtener Esquema y Labels
        $esquema = $this->recurso->acervo->esquema ?? [];
        $esquema = is_string($esquema) ? json_decode($esquema, true) : (array) $esquema;

        $camposPermitidos = collect($esquema)->whereIn('visible', ['Recuperable', 'Adicional'])->pluck('variable')->toArray();
        $labels = collect($esquema)->whereIn('visible', ['Recuperable', 'Adicional'])->pluck('label', 'variable')->toArray();

        // 2. Extraer Metadata permitida
        $recursoData = $this->recurso->toArray();
        $metadataArray = collect($recursoData['metadata'] ?? [])->only($camposPermitidos)->toArray();
        
        // Actualizamos la propiedad metadata del modelo para que la vista tenga solo lo permitido (por si se llama directo a $recurso->metadata)
        $this->recurso->metadata = $metadataArray;

        // 3. Reglas de Clasificación
        $camposLargos = ['descripcion', 'contenido', 'notas', 'resumen', 'observaciones'];
        $camposCompactos = ['anio', 'fecha', 'paginas', 'tomo', 'volumen', 'idioma', 'isbn', 'clave', 'folio', 'numero'];
        $camposTitulo = ['titulo', 'nombre', 'title'];

        $esLargo = function ($clave, $texto) use ($camposCompactos, $camposLargos) {
            if (in_array($clave, $camposCompactos)) return false;
            $longitud = mb_strlen($texto);
            return $longitud > 80 || (in_array($clave, $camposLargos) && $longitud >= 50);
        };

        $aTexto = fn($v) => is_scalar($v) ? trim(strip_tags((string) $v)) : json_encode($v, JSON_UNESCAPED_UNICODE);
        $etiqueta = fn($k) => $labels[$k] ?? ucwords(str_replace(['_id', '_', '-'], ['', ' ', ' '], $k));

        // 4. Filtrar Datos Principales (array_diff_key es O(1) contra O(N) de array_filter)
        $datosFiltrados = array_diff_key($this->recurso->getAttributes(), array_flip($omitir));

        // 5. Determinar el Título
        $claveTituloMeta = collect(array_keys($metadataArray))->first(
            fn($k) => in_array(mb_strtolower(trim((string) $k)), $camposTitulo) && !empty($metadataArray[$k])
        );

        $campoTitulo = null;
        if ($claveTituloMeta !== null) {
            $this->tituloCalculado = $aTexto($metadataArray[$claveTituloMeta]);
        } else {
            $campoTitulo = collect($camposTitulo)->first(fn($c) => !empty($datosFiltrados[$c] ?? null));
            if ($campoTitulo) {
                $this->tituloCalculado = $aTexto($datosFiltrados[$campoTitulo]);
            }
        }

        // 6. Procesar Metadata (Cortos primero, largos al final)
        $metaTemp = [];
        foreach ($metadataArray as $k => $v) {
            if ($k === $claveTituloMeta || is_null($v) || $v === '') continue;
            
            $textoMeta = $aTexto($v);
            $metaTemp[] = [
                'label' => $etiqueta($k), 
                'valor' => $textoMeta, 
                'largo' => $esLargo($k, $textoMeta)
            ];
        }
        $this->metadataProcesada = collect($metaTemp)->sortBy('largo')->values()->all();

        // 7. Procesar Generales y Largos
        foreach ($datosFiltrados as $columna => $valor) {
            if ($columna === $campoTitulo || $columna === 'metadata' || is_null($valor) || $valor === '') continue;

            $valorFinal = $valor;
            
            // Resolver Relaciones si fueron cargadas previamente
            if (str_ends_with($columna, '_id')) {
                $relacion = str_replace('_id', '', $columna);
                if ($this->recurso->relationLoaded($relacion) && $this->recurso->$relacion) {
                    $valorFinal = $this->recurso->$relacion->nombre ?? $this->recurso->$relacion->id;
                }
            }

            $texto = $aTexto($valorFinal);
            
            if ($esLargo($columna, $texto)) {
                $this->largosProcesados[] = ['label' => $etiqueta($columna), 'valor' => $texto];
            } else {
                $this->generalesProcesados[] = ['label' => $etiqueta($columna), 'valor' => $texto];
            }
        }
    }
}