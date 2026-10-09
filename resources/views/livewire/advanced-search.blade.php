<?php

use Livewire\Volt\Component as VoltComponent;
use App\Models\TipoAcervo;
use App\Models\Coleccion;
use App\Models\Recursos;
// use App\Models\Fondo; // Descomenta o ajusta si tienes un modelo para Fondos
use Illuminate\Support\Facades\Cache;

new class extends VoltComponent {
    public string $urlBase;

    public array $filas = [];

    // Id (string, vacío = "Todos los tipos") del TipoAcervo elegido. Al cambiar,
    // se recargan los campos de esquema específicos de ese acervo.
    public string $acervoSeleccionado = '';
    public array $camposEsquema = [];

    public array $fondosSeleccionados = [];

    public function mount()
    {
        // 1. Definimos la URL base limpia (sin parámetros) para el redireccionamiento posterior
        $this->urlBase = route('busqueda.resultado');

        // 2. Recuperamos el tipo de acervo elegido, si viene en la URL
        if (request()->filled('acervo')) {
            $this->acervoSeleccionado = (string) request()->input('acervo');
        }
        $this->cargarCamposEsquema();

        // 3. Recuperamos las filas de búsqueda dinámica si existen en la URL
        if (request()->has('q') && is_array(request()->input('q'))) {
            $this->filas = request()->input('q');
        } else {
            // Solo creamos la fila vacía si no hay parámetros en la URL
            $this->agregarFila();
        }

        // 4. Recuperamos los checkboxes de Fondos
        if (request()->has('fondos') && is_array(request()->input('fondos'))) {
            $this->fondosSeleccionados = request()->input('fondos');
        }
    }

    // Se dispara automáticamente cuando cambia el <select> de tipo de acervo
    // (wire:model.live="acervoSeleccionado").
    public function updatedAcervoSeleccionado(): void
    {
        $this->cargarCamposEsquema();

        // Los campos elegidos en cada fila pueden no existir en el nuevo esquema,
        // así que reseteamos a "Todos los campos" para evitar un valor inválido.
        foreach ($this->filas as $i => $fila) {
            $this->filas[$i]['campo'] = 'all';
        }

        // Los fondos disponibles cambian según el acervo (solo se muestran los que
        // tienen registros de ese tipo), así que una selección previa puede dejar
        // de ser válida.
        $this->fondosSeleccionados = [];
    }

    public function agregarFila()
    {
        $this->filas[] = [
            'operador' => 'AND',
            'campo' => 'all',
            'termino' => ''
        ];
    }

    public function removerFila(int $index)
    {
        unset($this->filas[$index]);
        $this->filas = array_values($this->filas);
    }

    public function aplicarFiltrado()
    {
        // Validación Livewire: ahora es obligatorio elegir un tipo de acervo primero.
        // Esto respalda en servidor lo que ya bloquea la UI (botón/campos deshabilitados).
        if (empty($this->acervoSeleccionado)) {
            $this->addError('busqueda_vacia', 'Debes seleccionar un tipo de acervo antes de buscar.');
            return;
        }

        // Limpiamos los términos vacíos
        $filasValidas = array_filter($this->filas, fn($fila) => !empty(trim($fila['termino'])));

        // Exigir además al menos un término o un fondo
        if (empty($filasValidas) && empty($this->fondosSeleccionados)) {
            $this->addError('busqueda_vacia', 'Por favor, ingresa al menos un término de búsqueda o elige un fondo para continuar.');
            return; // Detiene la ejecución aquí, no hace el redirect
        }

        // Si pasa la validación, limpiamos cualquier error previo
        $this->resetValidation();

        // El controlador (ColeccionesConsultaController) espera "tipos" como un
        // arreglo de nombres de acervo; aquí siempre es exactamente uno.
        $tipos = [];
        $nombreAcervo = TipoAcervo::find($this->acervoSeleccionado)?->nombre;
        if ($nombreAcervo) {
            $tipos = [$nombreAcervo];
        }

        $parametros = [
            'q' => $filasValidas,
            'acervo' => $this->acervoSeleccionado,
            'tipos' => $tipos,
            'fondos' => $this->fondosSeleccionados,
        ];

        return redirect()->to($this->urlBase . '?' . http_build_query($parametros));
    }

    public function with()
    {
        return [
            'camposEsquema' => $this->camposEsquema,
            'tiposDocumentales' => TipoAcervo::orderBy('nombre')->get(['id', 'nombre']),
            'fondosDisponibles' => $this->obtenerFondosDisponibles(),
        ];
    }

    // Solo los fondos (Coleccion) que tienen al menos un Recurso del tipo de
    // acervo elegido. Mientras no haya acervo seleccionado, no hay nada que
    // listar (la sección ya está deshabilitada en ese caso).
    private function obtenerFondosDisponibles(): array
    {
        if (empty($this->acervoSeleccionado)) {
            return [];
        }

        return Cache::remember(
            "fondos_con_recursos_acervo_{$this->acervoSeleccionado}",
            600,
            function () {
                $coleccionIds = Recursos::where('acervo_id', $this->acervoSeleccionado)
                    ->whereNotNull('coleccion_id')
                    ->distinct()
                    ->pluck('coleccion_id');

                return Coleccion::whereIn('id', $coleccionIds)
                    ->orderBy('nombre')
                    ->pluck('nombre')
                    ->toArray();
            }
        );
    }

    // Carga los campos "Recuperable" del esquema del acervo elegido. Si no hay
    // ninguno elegido ("Todos los tipos"), cae de vuelta a la unión global de
    // todos los esquemas, igual que el comportamiento anterior.
    private function cargarCamposEsquema(): void
    {
        if (empty($this->acervoSeleccionado)) {
            $this->camposEsquema = $this->obtenerEsquemasComunes();
            return;
        }

        $this->camposEsquema = Cache::remember(
            "campos_recuperables_acervo_{$this->acervoSeleccionado}",
            3600,
            function () {
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
            }
        );
    }

    private function obtenerEsquemasComunes()
    {
        return Cache::remember('campos_recuperables_union_global', 3600, function () {
            $esquemasRaw = TipoAcervo::whereNotNull('esquema')->pluck('esquema');
            if ($esquemasRaw->isEmpty())
                return [];

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

<div class="bg-white p-6 rounded-b-lg shadow-sm border-t border-gray-100 font-sans">

    <h4 class="text-sm font-bold mb-6 text-gray-700 uppercase tracking-wider flex items-center gap-2">
        <svg class="w-4 h-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
            stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
        </svg>
        Búsqueda Avanzada Global
    </h4>

    <div wire:keydown.enter="aplicarFiltrado" class="space-y-5">

        <!-- Filas Dinámicas de Búsqueda -->
        <div class="space-y-3">
            @foreach($filas as $index => $fila)
                <div class="flex items-center space-x-3">

                    @if($index > 0)
                        <select wire:model="filas.{{ $index }}.operador" @disabled(empty($acervoSeleccionado))
                            class="border border-gray-300 text-gray-600 font-bold uppercase text-xs rounded-md px-3 py-2 w-32 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                            <option value="AND">Y (AND)</option>
                            <option value="OR">O (OR)</option>
                            <option value="NOT">NO (NOT)</option>
                        </select>
                    @else
                        <div class="w-32"></div>
                    @endif

                    <select wire:model="filas.{{ $index }}.campo" @disabled(empty($acervoSeleccionado))
                        class="border border-gray-300 text-gray-700 rounded-md px-3 py-2 text-sm w-64 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-50">
                        <option value="all">--- Todos los campos ---</option>
                        @foreach($camposEsquema as $campo)
                            <option value="{{ $campo['variable'] }}">{{ $campo['label'] ?? $campo['variable'] }}</option>
                        @endforeach
                    </select>

                    <input type="text" wire:model="filas.{{ $index }}.termino" @disabled(empty($acervoSeleccionado))
                        placeholder="{{ empty($acervoSeleccionado) ? 'Elige primero un tipo de acervo...' : 'Ingresa el término...' }}"
                        class="flex-1 border border-gray-300 rounded-md p-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all placeholder-gray-400 disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-50">

                    @if($loop->last)
                        <button type="button" wire:click="agregarFila" @disabled(empty($acervoSeleccionado))
                            class="w-9 h-9 rounded-md border border-gray-300 text-gray-500 flex items-center justify-center hover:bg-gray-100 transition-colors disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-transparent">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.5v15m7.5-7.5h-15"></path>
                            </svg>
                        </button>
                    @else
                        <button type="button" wire:click="removerFila({{ $index }})" @disabled(empty($acervoSeleccionado))
                            class="w-9 h-9 rounded-md border border-red-200 text-red-500 flex items-center justify-center hover:bg-red-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-transparent">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                            </svg>
                        </button>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Contenedores de Checkboxes (Estilo Colecciones) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6 border-t pt-6 border-gray-100">

            <!-- Tipo Documental: ahora se elige uno solo, y define qué campos aparecen arriba -->
            <div class="flex flex-col">
                <label class="text-xs font-bold text-gray-600 uppercase mb-3 tracking-wide">
                    Tipo de acervo
                </label>
                <select wire:model.live="acervoSeleccionado"
                    class="border rounded-md px-3 py-2 text-sm w-full focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all {{ empty($acervoSeleccionado) ? 'border-red-300 text-gray-400' : 'border-gray-300 text-gray-700' }}">
                    <option value="">--- Selecciona un tipo de acervo ---</option>
                    @foreach($tiposDocumentales as $tipo)
                        <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                    @endforeach
                </select>
                @if(empty($acervoSeleccionado))
                    <p class="text-xs text-red-500 italic mt-2">
                        Selecciona un tipo de acervo para habilitar la búsqueda.
                    </p>
                @endif
            </div>

            <!-- Fondo -->
            <div class="flex flex-col border-l border-gray-100 pl-6">
                <label class="text-xs font-bold text-gray-600 uppercase mb-3 tracking-wide">
                    En fondo
                </label>
                <div class="grid grid-cols-1 gap-2 {{ empty($acervoSeleccionado) ? 'opacity-50' : '' }}">
                    <label class="flex items-center space-x-2 text-sm text-gray-700 cursor-pointer">
                        <input type="checkbox" wire:model="fondosSeleccionados" value="Todos" @disabled(empty($acervoSeleccionado))
                            class="rounded border-gray-300 text-[#86212b] focus:ring-[#86212b] disabled:cursor-not-allowed">
                        <span>Todos</span>
                    </label>
                    @foreach($fondosDisponibles as $fondo)
                        <label class="flex items-center space-x-2 text-sm text-gray-700 cursor-pointer">
                            <input type="checkbox" wire:model="fondosSeleccionados" value="{{ $fondo }}" @disabled(empty($acervoSeleccionado))
                                class="rounded border-gray-300 text-[#86212b] focus:ring-[#86212b] disabled:cursor-not-allowed">
                            <span>{{ $fondo }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="mt-8 flex justify-end space-x-3 border-t pt-4 border-gray-100">
            @if(request()->anyFilled(['q', 'acervo', 'tipos', 'fondos']))
                <a href="{{ url()->current() }}"
                    class="px-4 py-2 text-xs font-bold uppercase tracking-wider text-gray-500 hover:text-red-700 flex items-center transition duration-200">
                    Limpiar criterios
                </a>
            @endif

            <button type="button" wire:click="aplicarFiltrado" @disabled(empty($acervoSeleccionado))
                title="{{ empty($acervoSeleccionado) ? 'Selecciona un tipo de acervo primero' : '' }}"
                class="text-xs font-bold uppercase tracking-widest bg-[#86212b] hover:bg-[#6d1b23] text-white px-8 py-2.5 rounded-md transition duration-200 shadow-sm flex items-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-[#86212b]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                Buscar Acervos
            </button>
        </div>

    </div>
</div>