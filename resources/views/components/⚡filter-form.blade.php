<?php

use Livewire\Volt\Component as VoltComponent;

new class extends VoltComponent {
    public $acervoId; // Permitido (Primitivo)
    public $valores = []; // Guardará únicamente strings planos
    public $urlBase;

    public function mount($acervoId = null)
    {
        $this->urlBase = request()->url();
        $this->acervoId = $acervoId ? (int) $acervoId : null;

        if ($this->acervoId) {
            // 1. Obtenemos el esquema de arrays
            $esquema = $this->obtenerEsquemaDeMetadata($this->acervoId);
            //dd($esquema);

            foreach ($esquema as $campo) {
                $nombreVariable = $campo['variable'] ?? null;

                if ($nombreVariable) {
                    // Extraemos texto plano de la URL si ya se realizó una búsqueda previa
                    $valorUrl = request()->input($nombreVariable);

                    // IMPORTANTE: Inicializamos explícitamente todas las llaves como strings
                    $this->valores[$nombreVariable] = is_string($valorUrl) ? $valorUrl : '';
                }
            }
        }
    }

    public function aplicarFiltrado()
    {
        // Limpiamos los inputs vacíos
        $filtrosActivos = array_filter($this->valores, function ($value) {
            return $value !== '' && $value !== null;
        });

        // Mantenemos el acervo_id en la URL
        if ($this->acervoId) {
            $filtrosActivos['acervo_id'] = $this->acervoId;
        }
        // Redireccionamos limpiamente rompiendo el ciclo de renderizado de Livewire
        return redirect()->to($this->urlBase . '?' . http_build_query($filtrosActivos));
    }

    // Pasamos el esquema directamente a la vista evitando problemas de serialización
    public function with()
    {
        return [
            'configuracion' => $this->acervoId ? $this->obtenerEsquemaDeMetadata($this->acervoId) : [],
        ];
    }

    private function obtenerEsquemaDeMetadata($acervoId)
    {
        $config = App\Models\TipoAcervo::where('id', $acervoId)->first();

        if ($config && isset($config->esquema)) {
            $esquema = is_string($config->esquema) ? json_decode($config->esquema, true) : (array) $config->esquema;

            return array_values(
                array_filter($esquema, function ($item) {
                    return ($item['visible'] ?? null) === 'Recuperable';
                }),
            );
        }

        return [];
    }
}; ?>

<div class="bg-white p-6 rounded-b-lg shadow-sm border-t border-gray-100">
    @if ($acervoId)
        @if (count($configuracion) > 0)
            <h4 class="text-sm font-bold mb-4 text-gray-700 uppercase tracking-wider flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 8.293A1 1 0 013 7.586V4z" />
                </svg>
                Campos de búsqueda disponibles
            </h4>

            <div wire:keydown.enter="aplicarFiltrado">

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach ($configuracion as $f)
                        @php
                            $variable = $f['variable'] ?? null;
                            $label = $f['label'] ?? 'Campo';
                        @endphp

                        @if ($variable)
                            <div class="flex flex-col">
                                <label class="text-xs font-bold text-gray-600 uppercase mb-1 tracking-wide">
                                    {{ $label }}
                                </label>
                                <input type="text" wire:model.blur="valores.{{ $variable }}"
                                    placeholder="Buscar por {{ strtolower($label) }}..."
                                    class="border border-gray-300 rounded-md p-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all placeholder-gray-400">
                            </div>
                        @endif
                    @endforeach
                </div>

                <div class="mt-6 flex justify-end space-x-2 border-t pt-4 border-gray-100">

                    @if (request()->anyFilled(collect($configuracion)->pluck('variable')->toArray()))
                        <a href="{{ url()->current() . '?acervo_id=' . $acervoId }}"
                            class="px-4 py-2 text-xs font-bold uppercase tracking-wider text-gray-500 hover:text-red-700 flex items-center transition duration-200">
                            Limpiar criterios
                        </a>
                    @endif

                    <button type="button" wire:click="aplicarFiltrado"
                        class="text-xs font-bold uppercase tracking-widest bg-[#86212b] hover:bg-[#6d1b23] text-white px-6 py-2.5 rounded-md transition duration-200 shadow-sm">
                        Buscar en este Acervo
                    </button>
                </div>
            </div>
        @else
            <div class="text-center py-6 text-gray-400 text-sm font-medium">
                Este acervo no cuenta con un esquema de metadatos configurado.
            </div>
        @endif
    @else
        <div class="text-center py-6 text-gray-400 text-sm font-medium">
            Selecciona un acervo para visualizar sus opciones de filtrado.
        </div>
    @endif
</div>
