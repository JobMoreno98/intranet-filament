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
        $this->configuracion = DB::connection('mysql2')->table('colecciones')->where('tabla', $this->tabla)->orderBy('filtro')->get();

        foreach ($this->configuracion as $campo) {
            $this->valores[$campo->campo] = '';
        }
    }
}; ?>

<div class="bg-gray-50 p-6 rounded-xl border border-gray-200">
    <form wire:submit="filtrar" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach ($configuracion as $filtro)
                <div class="flex flex-col">
                    <label class="text-sm font-semibold text-gray-600 mb-1">
                        {{ $filtro->titulo }}
                    </label>

                    <input type="text" wire:model="valores.{{ $filtro->campo }}" placeholder="Buscar..."
                        class="rounded-md  px-2 border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            @endforeach
        </div>

        <div class="flex justify-end pt-2">
            <button type="submit"
                class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-lg transition duration-150 shadow-md">
                Aplicar Filtros
            </button>
        </div>
    </form>
</div>
