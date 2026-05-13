<?php

use Livewire\Volt\Component as VoltComponent;
use Illuminate\Support\Facades\DB;

new class extends VoltComponent {
    public $tabla; // Recibido desde la vista padre
    public $configuracion = [];
    public $valores = [];

    public function mount($tabla)
    {
        $this->tabla = $tabla;

        // Consultamos la tabla de metadatos de MySQL
        $this->configuracion = DB::connection('mysql2')->table('colecciones')->where('tabla', $this->tabla)->orderBy('filtro')->get();
        foreach ($this->configuracion as $campo) {
            $this->valores[$campo->campo] = '';
        }
    }

    public function filtrar()
    {
        // Emitimos los valores al componente de la tabla de resultados
        $this->dispatch('aplicar-filtros', filtros: $this->valores);
    }

    public function aplicarFiltrado()
    {
        // Limpiamos los valores vacíos para que la URL no sea kilométrica
        $filtrosActivos = array_filter($this->valores, function ($value) {
            return $value !== '' && $value !== null;
        });

        // IMPORTANTE: Redirigir a la URL actual + los filtros
        // Esto hará que el controlador reciba los datos en el $request
        return redirect()->to(url()->current() . '?' . http_build_query($filtrosActivos));
    }
}; ?>
<div class="bg-white p-4 rounded-lg shadow mb-6">
    <h4 class="text-md font-bold mb-4 text-gray-700">Filtrar registros</h4>

    {{-- El formulario usa GET para poner los datos en la URL directamente --}}
    <form action="{{ url()->current() }}" method="GET">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach ($configuracion as $f)
                <div class="flex flex-col">
                    <label class="text-sm font-medium text-gray-600 mb-1">
                        {{ $f->titulo }}
                    </label>

                    {{-- IMPORTANTE: El atributo 'name' es lo que el controlador leerá --}}
                    <input type="text" name="{{ $f->campo }}" value="{{ request($f->campo) }}"
                        placeholder="Buscar por {{ strtolower($f->titulo) }}..."
                        class="border border-gray-300 rounded-md p-2 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
            @endforeach
        </div>

        <div class="mt-4 flex justify-end space-x-2">
            {{-- Botón para limpiar: Simplemente redirige a la URL sin parámetros --}}
            @if (request()->anyFilled($configuracion->pluck('campos')->toArray()))
                <a href="{{ url()->current() }}"
                    class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 flex items-center">
                    Limpiar filtros
                </a>
            @endif

            <button type="submit"
                class="text-sm bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md transition duration-200">
                Aplicar Filtros
            </button>
        </div>
    </form>
</div>
