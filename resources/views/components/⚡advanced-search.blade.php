<?php
 
use Livewire\Volt\Component as VoltComponent;
use App\Models\TipoAcervo;
use App\Models\Coleccion;
use App\Models\Recursos;
use Illuminate\Support\Facades\Cache;
 
new class extends VoltComponent {
    private const MAX_FILAS = 3;
 
    public string $urlBase;
 
    public array $filas = [];
 
    // Flujo: 1) fondo  ->  2) tipo de acervo (o "todos")  ->  3) campos de búsqueda
    // '' = sin elegir | 'Todos' = todos los fondos | id de Coleccion (incluye sus subcolecciones)
    public string $fondoSeleccionado = '';
    // '' = sin elegir | 'todos' = todos los tipos de acervo | id numérico = un tipo
    public string $acervoSeleccionado = '';
    public array $camposEsquema = [];
 
    public function mount()
    {
        $this->urlBase = route('busqueda.resultado');
 
        // Recuperamos el fondo: el primer nombre de "fondos" es el elegido (los demás son sus subcolecciones)
        if (request()->has('fondos') && is_array(request()->input('fondos'))) {
            $primero = (string) (request()->input('fondos')[0] ?? '');
            $this->fondoSeleccionado = $primero === 'Todos' ? 'Todos' : (string) (Coleccion::where('nombre', $primero)->value('id') ?? '');
        }
 
        // Recuperamos el tipo de acervo, solo si ya hay fondo
        if ($this->fondoSeleccionado !== '' && request()->filled('acervo')) {
            $this->acervoSeleccionado = (string) request()->input('acervo');
        }
 
        $this->cargarCamposEsquema();
 
        // Recuperamos las filas de búsqueda dinámica si existen en la URL
        if (request()->has('q') && is_array(request()->input('q'))) {
            $this->filas = array_slice(array_values(request()->input('q')), 0, self::MAX_FILAS);
        } else {
            $this->agregarFila();
        }
    }
 
    // wire:model.live="fondoSeleccionado": al cambiar el fondo, los acervos
    // disponibles cambian, así que se reinicia todo lo que depende de él.
    public function updatedFondoSeleccionado(): void
    {
        $this->acervoSeleccionado = '';
        $this->camposEsquema = [];
        $this->filas = [];
        $this->agregarFila();
    }
 
    // wire:model.live="acervoSeleccionado"
    public function updatedAcervoSeleccionado(): void
    {
        $this->cargarCamposEsquema();
 
        // El campo elegido en cada fila puede no existir en el nuevo esquema.
        foreach ($this->filas as $i => $fila) {
            $this->filas[$i]['campo'] = 'all';
        }
    }
 
    public function agregarFila()
    {
        if (count($this->filas) >= self::MAX_FILAS) {
            return;
        }
 
        $this->filas[] = [
            'operador' => 'AND',
            'campo' => 'all',
            'termino' => '',
        ];
    }
 
    public function removerFila(int $index)
    {
        unset($this->filas[$index]);
        $this->filas = array_values($this->filas);
    }
 
    public function aplicarFiltrado()
    {
        // Respaldo en servidor de lo que ya oculta la UI.
        if ($this->fondoSeleccionado === '') {
            $this->addError('busqueda_vacia', 'Debes seleccionar un fondo antes de buscar.');
            return;
        }
 
        if ($this->acervoSeleccionado === '') {
            $this->addError('busqueda_vacia', 'Debes seleccionar un tipo de acervo (o "Todos") antes de buscar.');
            return;
        }
 
        $this->resetValidation();
 
        // Limpiamos los términos vacíos
        $filasValidas = array_filter($this->filas, fn($fila) => !empty(trim($fila['termino'] ?? '')));
 
        // El controlador espera "tipos" como arreglo de nombres de acervo.
        if ($this->acervoSeleccionado === 'todos') {
            $tipos = collect($this->obtenerAcervosDisponibles())->pluck('nombre')->all();
        } else {
            $nombreAcervo = TipoAcervo::find($this->acervoSeleccionado)?->nombre;
            $tipos = $nombreAcervo ? [$nombreAcervo] : [];
        }
 
        $parametros = [
            'q' => $filasValidas,
            'acervo' => $this->acervoSeleccionado,
            'tipos' => $tipos,
            'fondos' => $this->nombresFondosParaBusqueda(),
        ];
 
        return redirect()->to($this->urlBase . '?' . http_build_query($parametros));
    }
 
    public function with()
    {
        return [
            'camposEsquema' => $this->camposEsquema,
            'maxFilas' => self::MAX_FILAS,
            'fondosDisponibles' => $this->obtenerFondosDisponibles(),
            'acervosDisponibles' => $this->fondoSeleccionado !== '' ? $this->obtenerAcervosDisponibles() : [],
        ];
    }
 
    // Todas las colecciones sin padre/hijo mezclados: id, nombre y parent_id (una sola consulta).
    private function coleccionesPlanas(): array
    {
        return Cache::remember('colecciones_planas', 600, fn() => Coleccion::orderBy('nombre')->get(['id', 'nombre', 'parent_id'])->toArray());
    }
 
    // Árbol aplanado en orden jerárquico, con profundidad para indentar en el select.
    private function obtenerFondosDisponibles(): array
    {
        $porPadre = collect($this->coleccionesPlanas())->groupBy(fn($c) => $c['parent_id'] ?? 0);
        $salida = [];
 
        $recorrer = function ($padreId, int $depth) use (&$recorrer, $porPadre, &$salida) {
            if ($depth > 20) {
                return; // protección contra ciclos
            }
            foreach ($porPadre->get($padreId, []) as $c) {
                $salida[] = ['id' => $c['id'], 'nombre' => $c['nombre'], 'depth' => $depth];
                $recorrer($c['id'], $depth + 1);
            }
        };
        $recorrer(0, 0);
 
        return $salida;
    }
 
    // El id de la colección más los de todas sus subcolecciones.
    private function idsConDescendientes(int $id): array
    {
        $porPadre = collect($this->coleccionesPlanas())->groupBy('parent_id');
        $ids = [$id];
        $pendientes = [$id];
 
        while ($pendientes) {
            $actual = array_pop($pendientes);
            foreach ($porPadre->get($actual, []) as $hijo) {
                if (!in_array($hijo['id'], $ids, true)) {
                    $ids[] = $hijo['id'];
                    $pendientes[] = $hijo['id'];
                }
            }
        }
 
        return $ids;
    }
 
    // Nombres que se mandan al controlador: el fondo elegido primero y luego sus subcolecciones.
    private function nombresFondosParaBusqueda(): array
    {
        if ($this->fondoSeleccionado === 'Todos') {
            return ['Todos'];
        }
 
        $ids = $this->idsConDescendientes((int) $this->fondoSeleccionado);
        $nombres = collect($this->coleccionesPlanas())->whereIn('id', $ids)->sortBy(fn($c) => $c['id'] === (int) $this->fondoSeleccionado ? 0 : 1)->pluck('nombre');
 
        return $nombres->unique()->values()->all();
    }
 
    // Tipos de acervo con recursos dentro del fondo elegido (incluyendo sus subcolecciones).
    // Con "Todos" los fondos, se listan todos los tipos de acervo.
    private function obtenerAcervosDisponibles(): array
    {
        if ($this->fondoSeleccionado === '') {
            return [];
        }
 
        if ($this->fondoSeleccionado === 'Todos') {
            return Cache::remember('acervos_todos', 600, fn() => TipoAcervo::orderBy('nombre')->get(['id', 'nombre'])->toArray());
        }
 
        $ids = $this->idsConDescendientes((int) $this->fondoSeleccionado);
 
        return Cache::remember('acervos_en_fondos_' . md5(implode(',', $ids)), 600, function () use ($ids) {
            $acervoIds = Recursos::whereIn('coleccion_id', $ids)->whereNotNull('acervo_id')->distinct()->pluck('acervo_id');
 
            return TipoAcervo::whereIn('id', $acervoIds)->orderBy('nombre')->get(['id', 'nombre'])->toArray();
        });
    }
 
    // Campos "Recuperable" del esquema del acervo elegido. Con "todos" se usa la
    // unión global; sin acervo no se muestran campos.
    private function cargarCamposEsquema(): void
    {
        if ($this->acervoSeleccionado === '') {
            $this->camposEsquema = [];
            return;
        }
 
        if ($this->acervoSeleccionado === 'todos') {
            $this->camposEsquema = $this->obtenerEsquemasComunes();
            return;
        }
 
        $this->camposEsquema = Cache::remember("campos_recuperables_acervo_{$this->acervoSeleccionado}", 3600, function () {
            $tipo = TipoAcervo::find($this->acervoSeleccionado);
            if (!$tipo || empty($tipo->esquema)) {
                return [];
            }
 
            $esquema = is_string($tipo->esquema) ? json_decode($tipo->esquema, true) : $tipo->esquema;
 
            return collect($esquema ?? [])
                ->filter(function ($campo) {
                    $visible = trim(strtolower(data_get($campo, 'visible', '')));
                    return $visible === 'recuperable' && !empty(data_get($campo, 'variable'));
                })
                ->values()
                ->toArray();
        });
    }
 
    private function obtenerEsquemasComunes()
    {
        return Cache::remember('campos_recuperables_union_global', 3600, function () {
            $esquemasRaw = TipoAcervo::whereNotNull('esquema')->pluck('esquema');
            if ($esquemasRaw->isEmpty()) {
                return [];
            }
 
            return $esquemasRaw
                ->flatMap(function ($esquema) {
                    $decodificado = is_string($esquema) ? json_decode($esquema, true) : $esquema;
                    return json_decode(json_encode($decodificado), true) ?? [];
                })
                ->filter(function ($campo) {
                    $visible = trim(strtolower(data_get($campo, 'visible', '')));
                    return $visible === 'recuperable' && !empty(data_get($campo, 'variable'));
                })
                ->unique('variable')
                ->values()
                ->toArray();
        });
    }
}; ?>