<?php

use Livewire\Volt\Component;
use function Livewire\Volt\{state, with, usesPagination};

// Activamos la paginación de Volt
usesPagination();

// Definimos el estado
state(['collection']);

// Pasamos los datos dinámicos a la vista
with(function () {
    $children = $this->collection
        ->children()
        ->orderBy('nombre', 'ASC')
        ->paginate(6, ['*'], 'page-' . $this->collection->id);

    return [
        'children' => $children,
    ];
});

?>

{{-- Inicializamos Alpine en la raíz --}}
<div class="w-full bg-white  dark:bg-zinc-700 dark:text-white" x-data="{ desplegado: false }">
    
    <div class="w-full rounded-md border border-zinc-200 bg-white shadow-xl grid grid-cols-1 grid-rows-1 overflow-hidden min-h-[240px] transition-all duration-300">
        
        <div x-show="!desplegado" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-98"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-98"
             {{-- El secreto: col-start-1 y row-start-1 los obliga a compartir el mismo espacio físico --}}
             class="col-start-1 row-start-1 p-6 flex flex-col md:flex-row gap-6 items-center w-full h-full bg-white z-10 bg-white  dark:bg-zinc-700">
            
            <div class="w-full md:w-1/6 flex-shrink-0">
                <img style="aspect-ratio:1/1;" class="mx-auto h-auto w-full object-cover rounded-base"
                    src="{{ asset('storage/colecciones/' . $collection->foto) }}" alt="{{ $collection->nombre }}">
            </div>

            <div class="flex-1 flex flex-col w-full h-full justify-center">
                <h2 class="text-2xl font-bold text-left mb-2 text-zinc-800 dark:text-white">
                    {{ $collection->nombre }}
                </h2>
                
                <p class="text-body text-justify text-zinc-600 text-sm line-clamp-4 mb-4 dark:text-white">
                    {{ $collection->descripcion }}
                </p>

                <div class="flex justify-end gap-3 mt-auto">
                    <a href="{{ route('coleccion.show', $collection->slug) }}" target="_blank"
                        class="inline-flex items-center text-xs md:text-base bg-red-800 rounded text-white font-bold py-1 px-4 hover:bg-red-950 transition-colors">
                        Ver Fondo
                    </a>

                    @if ($collection->children()->exists())
                        <button @click="desplegado = true"
                            class="inline-flex items-center text-xs md:text-base bg-zinc-100 border border-zinc-300 
                            rounded text-zinc-800 font-bold py-1 px-4 hover:bg-zinc-200 transition-colors">
                            Ver fondos internos
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <div x-show="desplegado" 
             x-cloak
             x-transition:enter="transition ease-out duration-300 delay-100"
             x-transition:enter-start="opacity-0 scale-98"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-98"
             {{-- Comparten col-start-1 y row-start-1 con la vista del padre --}}
             class="col-start-1 row-start-1 p-6 w-full flex flex-col h-full  z-20 bg-white  dark:bg-zinc-700">
            
            <div class="flex flex-row justify-between items-center mb-4 border-b border-zinc-100 pb-2 ">
                <h3 class="text-lg font-bold text-zinc-700 flex items-center gap-2  dark:text-white">
                    <span class="w-2 h-4 bg-red-800 inline-block rounded-sm"></span>
                    {{ $collection->nombre }} <span class="text-zinc-400 font-normal text-sm">(Contenido)</span>
                </h3>
                
                <button @click="desplegado = false" 
                        class="inline-flex items-center text-xs bg-zinc-800 text-white rounded font-bold py-1.5 px-4 hover:bg-zinc-950 transition-colors  dark:text-white">
                    ← Volver
                </button>
            </div>

            {{-- Loader asíncrono para la paginación interna --}}
            <div wire:loading.class="opacity-50" class="transition-opacity duration-300 flex-1 flex flex-col justify-between">

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
                    @foreach ($children as $child)
                        <div class="w-full p-3 rounded-md border border-zinc-200 bg-zinc-50 flex flex-col gap-2 shadow-sm relative dark:bg-stone-700">
                            <img style="aspect-ratio:1/1;" class="mx-auto h-40 w-auto rounded-base object-cover"
                                src="{{ asset('storage/colecciones/' . $child->foto) }}" alt="{{ $child->nombre }}">

                            <h4 class="text-sm font-bold text-center text-zinc-800 line-clamp-1  dark:text-white">
                                {{ $child->nombre }}
                            </h4>

                            <p class="text-zinc-600 text-xs text-justify line-clamp-2  dark:text-white">
                                {{ $child->descripcion }}
                            </p>

                            <p class="text-right mt-auto">
                                <a href="{{ route('coleccion.show', $child->slug) }}" target="_blank"
                                    class="text-[11px] bg-red-800 rounded text-white font-bold py-1 px-2.5 hover:bg-red-950 transition-colors">
                                    Ver
                                </a>
                            </p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 flex justify-center">
                    {{ $children->links() }}
                </div>

            </div>
        </div>

    </div>
</div>