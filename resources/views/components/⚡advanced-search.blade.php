<?php

use Livewire\Volt\Component as VoltComponent;
use App\Models\TipoAcervo;
use App\Models\Coleccion;
// use App\Models\Fondo; // Descomenta o ajusta si tienes un modelo para Fondos
use Illuminate\Support\Facades\Cache;

new class extends VoltComponent {
    public string $urlBase;

    public array $filas = [];
    public array $tiposSeleccionados = [];
    public array $fondosSeleccionados = [];

    public function mount()
    {
        // 1. Definimos la URL base limpia (sin parámetros) para el redireccionamiento posterior
        $this->urlBase = route('busqueda.resultado');

        // 2. Recuperamos las filas de búsqueda dinámica si existen en la URL
        if (request()->has('q') && is_array(request()->input('q'))) {
            $this->filas = request()->input('q');
        } else {
            // Solo creamos la fila vacía si no hay parámetros en la URL
            $this->agregarFila();
        }

        // 3. Recuperamos los checkboxes de Tipos Documentales
        if (request()->has('tipos') && is_array(request()->input('tipos'))) {
            $this->tiposSeleccionados = request()->input('tipos');
        }

        // 4. Recuperamos los checkboxes de Fondos
        if (request()->has('fondos') && is_array(request()->input('fondos'))) {
            $this->fondosSeleccionados = request()->input('fondos');
        }
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
        // Limpiamos los términos vacíos
        $filasValidas = array_filter($this->filas, fn($fila) => !empty(trim($fila['termino'])));

        // Validación Livewire: Exigir al menos un término o un filtro
        if (empty($filasValidas) && empty($this->tiposSeleccionados) && empty($this->fondosSeleccionados)) {
            $this->addError('busqueda_vacia', 'Por favor, ingresa al menos un término de búsqueda o selecciona un filtro para continuar.');
            return; // Detiene la ejecución aquí, no hace el redirect
        }

        // Si pasa la validación, limpiamos cualquier error previo
        $this->resetValidation();

        $parametros = [
            'q' => $filasValidas,
            'tipos' => $this->tiposSeleccionados,
            'fondos' => $this->fondosSeleccionados,
        ];

        return redirect()->to($this->urlBase . '?' . http_build_query($parametros));
    }

    public function with()
    {
        return [
            'configuracion' => $this->obtenerEsquemasComunes(),
            // Ajusta 'nombre' a la columna real de tu base de datos (ej. 'titulo', 'descripcion')
            'tiposDocumentales' => TipoAcervo::pluck('nombre')->toArray(),

            // Si Fondo es otro modelo:
            // 'fondosDisponibles' => Fondo::pluck('nombre')->toArray(),
            // Si el fondo viene de un campo específico en otra tabla, ajusta la consulta aquí:
            'fondosDisponibles' => Coleccion::pluck('nombre')->toArray(), // Reemplazar con consulta real
        ];
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
                        <select wire:model="filas.{{ $index }}.operador"
                            class="border border-gray-300 text-gray-600 font-bold uppercase text-xs rounded-md px-3 py-2 w-32 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all bg-gray-50">
                            <option value="AND">Y (AND)</option>
                            <option value="OR">O (OR)</option>
                            <option value="NOT">NO (NOT)</option>
                        </select>
                    @else
                        <div class="w-32"></div>
                    @endif

                    <select wire:model="filas.{{ $index }}.campo"
                        class="border border-gray-300 text-gray-700 rounded-md px-3 py-2 text-sm w-64 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all">
                        <option value="all">--- Todos los campos ---</option>
                        @foreach($configuracion as $campo)
                            <option value="{{ $campo['variable'] }}">{{ $campo['label'] ?? $campo['variable'] }}</option>
                        @endforeach
                    </select>

                    <input type="text" wire:model="filas.{{ $index }}.termino" placeholder="Ingresa el término..."
                        class="flex-1 border border-gray-300 rounded-md p-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all placeholder-gray-400">

                    @if($loop->last)
                        <button type="button" wire:click="agregarFila"
                            class="w-9 h-9 rounded-md border border-gray-300 text-gray-500 flex items-center justify-center hover:bg-gray-100 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.5v15m7.5-7.5h-15"></path>
                            </svg>
                        </button>
                    @else
                        <button type="button" wire:click="removerFila({{ $index }})"
                            class="w-9 h-9 rounded-md border border-red-200 text-red-500 flex items-center justify-center hover:bg-red-50 transition-colors">
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

            <!-- Tipo Documental -->
            <div class="flex flex-col">
                <label class="text-xs font-bold text-gray-600 uppercase mb-3 tracking-wide">
                    En tipo documental
                </label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="flex items-center space-x-2 text-sm text-gray-700 cursor-pointer">
                        <input type="checkbox" wire:model="tiposSeleccionados" value="Todos"
                            class="rounded border-gray-300 text-[#86212b] focus:ring-[#86212b]">
                        <span>Todos</span>
                    </label>
                    @foreach($tiposDocumentales as $tipo)
                        <label class="flex items-center space-x-2 text-sm text-gray-700 cursor-pointer">
                            <input type="checkbox" wire:model="tiposSeleccionados" value="{{ $tipo }}"
                                class="rounded border-gray-300 text-[#86212b] focus:ring-[#86212b]">
                            <span>{{ $tipo }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Fondo -->
            <div class="flex flex-col border-l border-gray-100 pl-6">
                <label class="text-xs font-bold text-gray-600 uppercase mb-3 tracking-wide">
                    En fondo
                </label>
                <div class="grid grid-cols-1 gap-2">
                    <label class="flex items-center space-x-2 text-sm text-gray-700 cursor-pointer">
                        <input type="checkbox" wire:model="fondosSeleccionados" value="Todos"
                            class="rounded border-gray-300 text-[#86212b] focus:ring-[#86212b]">
                        <span>Todos</span>
                    </label>
                    @foreach($fondosDisponibles as $fondo)
                        <label class="flex items-center space-x-2 text-sm text-gray-700 cursor-pointer">
                            <input type="checkbox" wire:model="fondosSeleccionados" value="{{ $fondo }}"
                                class="rounded border-gray-300 text-[#86212b] focus:ring-[#86212b]">
                            <span>{{ $fondo }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="mt-8 flex justify-end space-x-3 border-t pt-4 border-gray-100">
            @if(request()->anyFilled(['q', 'tipos', 'fondos']))
                <a href="{{ url()->current() }}"
                    class="px-4 py-2 text-xs font-bold uppercase tracking-wider text-gray-500 hover:text-red-700 flex items-center transition duration-200">
                    Limpiar criterios
                </a>
            @endif

            <button type="button" wire:click="aplicarFiltrado"
                class="text-xs font-bold uppercase tracking-widest bg-[#86212b] hover:bg-[#6d1b23] text-white px-8 py-2.5 rounded-md transition duration-200 shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                Buscar Acervos
            </button>
        </div>

    </div>
</div>