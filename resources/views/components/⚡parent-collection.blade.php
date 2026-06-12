<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Coleccion;

// Activamos el Trait de paginación en Volt
uses([WithPagination::class]);

// Definimos el estado (las propiedades del componente)
// 'collection' recibirá el objeto del Padre desde la vista principal
state(['collection', 'isOpen' => false]);

// Acción para abrir/cerrar y limpiar la paginación si se cierra
$toggleChildren = function () {
    $this->isOpen = !$this->isOpen;

    if (!$this->isOpen) {
        $this->resetPage('page-' . $this->collection->id);
    }
};

// Pasamos los datos dinámicos a la vista en cada renderizado (Eager Loading reactivo)
with(function () {
    $children = $this->isOpen
        ? $this->collection->children()
            ->orderBy('nombre', 'ASC')
            ->paginate(6, ['*'], 'page-' . $this->collection->id)
        : collect();

    return [
        'children' => $children,
    ];
});

?>

<div class="w-full" data-aos="fade-up" data-aos-duration="500">
    
    <div class="w-full p-6 rounded-md border border-zinc-200 bg-white flex flex-col md:flex-row gap-6 shadow-xl relative items-center">
        
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
                @if($collection->children()->exists())
                    <button wire:click="toggleChildren"
                        class="inline-flex items-center text-sm md:text-base bg-zinc-100 border border-zinc-300 rounded text-zinc-800 font-bold py-2 px-6 hover:bg-zinc-200 transition-colors">
                        {{ $isOpen ? 'Ocultar hijos' : 'Ver más' }}
                    </button>
                @endif
            </div>
        </div>
    </div>

    @if($isOpen)
        <div class="mt-6 pl-0 md:pl-12 border-l-0 md:border-l-4 border-red-900 transition-all duration-300">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                @foreach($children as $child)
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
    @endif

</div>