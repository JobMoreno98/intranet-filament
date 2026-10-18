<?php

use Livewire\Volt\Component;
use function Livewire\Volt\{state, with, usesPagination, updated};

usesPagination();

state([
    'areaId' => null,
    'mostrarHijos' => false,
]);

// Al cambiar el interruptor cambia el total de resultados: volvemos a la página 1
updated(['mostrarHijos' => fn() => $this->resetPage()]);

with(function () {
    $query = \App\Models\Coleccion::query()->orderBy('nombre', 'ASC');

    // Por defecto solo fondos padres; con el interruptor activo entran también los internos
    if (!$this->mostrarHijos) {
        $query->whereNull('parent_id'); // ajusta al nombre real de tu columna
    }

    if (!empty($this->areaId)) {
        $query->where('areaS_id', $this->areaId);
    }

    return [
        'colecciones' => $query->paginate(5),
    ];
});

?>

<div wire:loading.class="opacity-50" class="flex flex-col gap-6 transition-opacity duration-300">

    {{-- Interruptor: solo fondos / fondos con internos --}}
    <label class="flex w-fit cursor-pointer select-none items-center gap-3">
        <input type="checkbox" wire:model.live="mostrarHijos" class="peer sr-only">
        <span
            class="relative h-6 w-11 rounded-full bg-zinc-300 transition-colors after:absolute after:left-0.5 after:top-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition-transform peer-checked:bg-[#86212b] peer-checked:after:translate-x-5 peer-focus-visible:ring-2 peer-focus-visible:ring-red-400 dark:bg-zinc-500"></span>
        <span class="text-sm font-medium text-zinc-700 dark:text-zinc-200">
            Mostrar fondos internos
        </span>
    </label>

    @if ($colecciones->count())
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
            @foreach ($colecciones as $index => $item)
                <div class="h-full" data-aos="fade-up" data-aos-duration="500" data-aos-delay="{{ $index * 50 }}">
                    <livewire:parent-collection :collection="$item" :mostrarHijos="$mostrarHijos" :key="'col-' . $item->id . '-' . ($mostrarHijos ? 'h' : 'p')" />
                </div>
            @endforeach
        </div>
    @else
        <div class="w-full py-12 text-center">
            <h4 class="text-lg font-medium text-zinc-500">No hay colecciones disponibles</h4>
        </div>
    @endif

    <div class="mt-2 flex justify-center">
        {{ $colecciones->links() }}
    </div>

</div>
