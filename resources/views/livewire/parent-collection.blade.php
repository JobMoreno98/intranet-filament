<?php

use Livewire\Volt\Component;
// Importamos todas las funciones de Volt de forma explícita en una sola línea
use function Livewire\Volt\{state, with, usesPagination};

// Activamos la paginación
usesPagination();

// Definimos las propiedades reactivas (estado)
state(['collection', 'isOpen' => false]);

// Acción para abrir/cerrar los hijos
$toggleChildren = function () {
    $this->isOpen = !$this->isOpen;

    if (!$this->isOpen) {
        $this->resetPage('page-' . $this->collection->id);
    }
};

// Pasamos los datos dinámicos mapeados a la vista
with(function () {
    // Traemos los hijos siempre para que el contenedor HTML no desaparezca y Alpine pueda animar el cierre
    $children = $this->collection
        ->children()
        ->orderBy('nombre', 'ASC')
        ->paginate(6, ['*'], 'page-' . $this->collection->id);

    return [
        'children' => $children,
    ];
});

?>

<div class="w-full">

    <div
        class="w-full p-6 rounded-md border border-zinc-200 bg-white flex flex-col md:flex-row gap-6 shadow-xl relative items-center">

        <div class="w-full md:w-1/4 flex-shrink-0">
            <img style="aspect-ratio:1/1;" class="mx-auto h-auto w-full object-cover rounded-base"
                src="{{ asset('storage/colecciones/' . $collection->foto) }}" alt="{{ $collection->nombre }}">
        </div>

        <div class="flex-1 flex flex-col w-full h-full justify-center">
            <h2 class="text-2xl font-bold text-left mb-2">
                {{ $collection->nombre }}
            </h2>

            <p class="text-body text-justify text-zinc-600 text-sm line-clamp-3 mb-4">
                {{ $collection->descripcion }}
            </p>

            <div class="flex justify-end gap-3 mt-auto">
                <a href="{{ route('coleccion.show', $collection->slug) }}" target="_blank"
                    class="inline-flex items-center text-sm md:text-base bg-red-800 rounded text-white font-bold py-2 px-6 hover:bg-red-950 transition-colors">
                    Ver Colección
                </a>

                {{-- Botón de Volt para abrir los hijos (Solo si tiene hijos en la BD) --}}
                @if ($collection->children()->exists())
                    <button wire:click="toggleChildren"
                        class="inline-flex items-center text-sm md:text-base bg-zinc-100 border border-zinc-300 rounded text-zinc-800 font-bold py-2 px-6 hover:bg-zinc-200 transition-colors">
                        {{ $isOpen ? 'Ver menos' : 'Ver más' }}
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div x-data="{ open: @entangled('isOpen') }" x-show="open" x-collapse.duration.500ms
        class="mt-6 pl-0 md:pl-12 border-l-0 md:border-l-4 border-red-900">

        {{-- Mientras los hijos se cargan o cambian de página en Livewire, bajamos un poco la opacidad --}}
        <div wire:loading.class="opacity-50" class="transition-opacity duration-300">

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                @foreach ($children as $child)
                    <div class="w-full p-4 rounded-md border bg-zinc-50 flex flex-col gap-4 shadow-md relative">
                        <img style="aspect-ratio:1/1;" class="mx-auto h-auto max-w-full rounded-base object-cover"
                            src="{{ asset('storage/colecciones/' . $child->foto) }}" alt="{{ $child->nombre }}">

                        <h3 class="text-lg font-bold text-center">
                            {{ $child->nombre }}
                        </h3>

                        <p class="text-zinc-600 text-sm text-justify line-clamp-2">
                            {{ $child->descripcion }}
                        </p>

                        <p class="text-right mt-auto">
                            <a href="{{ route('coleccion.show', $child->slug) }}" target="_blank"
                                class="text-sm bg-red-800 rounded text-white font-bold py-1 px-4 hover:bg-red-950 transition-colors">
                                Ver
                            </a>
                        </p>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 flex justify-center">
                {{ $children->links() }}
            </div>

        </div>
    </div>

</div>
