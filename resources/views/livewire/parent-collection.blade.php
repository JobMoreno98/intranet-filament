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
        ->paginate(12, ['*'], 'page-' . $this->collection->id);

    return [
        'children' => $children,
    ];
});

?>

{{-- Alpine en la raíz. h-full para que la tarjeta iguale la altura de las demás en el grid --}}
<div class="h-full w-full" x-data="{
    abrir() {
        this.$refs.modal.showModal();
        document.body.classList.add('overflow-hidden');
    },
    cerrar() {
        this.$refs.modal.close();
    },
}">

    {{-- Tarjeta del fondo padre --}}
    <div
        class="flex h-full w-full flex-col overflow-hidden rounded-md border border-zinc-200 bg-white shadow-lg dark:border-zinc-600 dark:bg-zinc-700 dark:text-white">

        {{-- Portada --}}
        <div class="aspect-[4/3] w-full shrink-0 overflow-hidden bg-zinc-100 dark:bg-zinc-600">
            @if ($collection->foto)
                <img src="{{ asset('storage/colecciones/' . $collection->foto) }}" alt="{{ $collection->nombre }}"
                    class="h-full w-full object-cover" loading="lazy">
            @else
                <div class="flex h-full w-full items-center justify-center text-zinc-300 dark:text-zinc-400">
                    <svg class="h-10 w-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v13.5A1.5 1.5 0 0 0 3.75 21Z" />
                    </svg>
                </div>
            @endif
        </div>

        <div class="flex flex-1 flex-col items-center gap-3 p-4 text-center">
            <div class="flex flex-col gap-1">
                <h2 class="text-lg font-bold text-zinc-800 dark:text-white">
                    {{ $collection->nombre }}
                </h2>
                <p class="line-clamp-3 text-xs text-zinc-500 dark:text-zinc-300">
                    {{ $collection->descripcion }}
                </p>
            </div>

            {{-- mt-auto empuja los botones al fondo para alinear todas las tarjetas --}}
            <div class="mt-auto flex flex-wrap justify-center gap-2 pt-1">
                <a href="{{ route('coleccion.show', $collection->slug) }}" target="_blank"
                    class="inline-flex items-center rounded bg-red-800 px-3 py-1 text-xs font-bold text-white transition-colors hover:bg-red-950">
                    Ver Fondo
                </a>

                @if ($children->total() > 0)
                    <button type="button" @click="abrir()"
                        class="inline-flex items-center rounded border border-zinc-300 bg-zinc-100 px-3 py-1 text-xs font-bold text-zinc-800 transition-colors hover:bg-zinc-200 dark:border-zinc-500 dark:bg-zinc-600 dark:text-white dark:hover:bg-zinc-500">
                        Ver fondos internos ({{ $children->total() }})
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{--
        Modal con <dialog> nativo: se dibuja en la "top layer" del navegador, así que no lo recortan
        el overflow-hidden de la tarjeta ni el transform que aplica AOS al contenedor.
        wire:ignore.self evita que Livewire le quite el atributo "open" al re-renderizar al paginar,
        pero sigue actualizando su contenido.
    --}}
    <dialog x-ref="modal" wire:ignore.self @click.self="cerrar()"
        @close="document.body.classList.remove('overflow-hidden')"
        class="m-auto w-[min(72rem,calc(100%-1.5rem))] rounded-lg bg-white p-0 text-zinc-800 shadow-2xl backdrop:bg-black/60 dark:bg-zinc-700 dark:text-white">

        <div class="flex max-h-[85vh] flex-col">

            <div
                class="flex items-center justify-between gap-3 border-b border-zinc-100 px-4 py-3 sm:px-6 dark:border-zinc-600">
                <div class="min-w-0">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-zinc-400">Fondos internos</p>
                    <h3 class="truncate text-base font-bold sm:text-lg">{{ $collection->nombre }}</h3>
                </div>

                <button type="button" @click="cerrar()" aria-label="Cerrar"
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md text-zinc-500 transition-colors hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Cuerpo con scroll propio --}}
            <div x-ref="cuerpo" wire:loading.class="opacity-50"
                class="flex-1 overflow-y-auto p-4 transition-opacity duration-300 sm:p-6">
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4">
                    @foreach ($children as $child)
                        <div
                            class="flex flex-col overflow-hidden rounded-md border border-zinc-200 bg-zinc-50 shadow-sm dark:border-zinc-500 dark:bg-zinc-600">
                            <div class="aspect-[4/3] w-full overflow-hidden bg-zinc-100 dark:bg-zinc-500">
                                @if ($child->foto)
                                    <img src="{{ asset('storage/colecciones/' . $child->foto) }}"
                                        alt="{{ $child->nombre }}" class="h-full w-full object-cover" loading="lazy">
                                @endif
                            </div>

                            <div class="flex flex-1 flex-col gap-1 p-3">
                                <h4 class="line-clamp-2 text-sm font-bold text-zinc-800 dark:text-white">
                                    {{ $child->nombre }}
                                </h4>
                                <p class="line-clamp-2 text-xs text-zinc-500 dark:text-zinc-300">
                                    {{ $child->descripcion }}
                                </p>

                                <div class="mt-auto pt-2">
                                    <a href="{{ route('coleccion.show', $child->slug) }}" target="_blank"
                                        class="flex w-full items-center justify-center rounded bg-red-800 px-3 py-1 text-xs font-bold text-white transition-colors hover:bg-red-950">
                                        Ver
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            @if ($children->hasPages())
                {{-- scrollTo => false: evita que la paginación desplace la página de fondo; subimos el cuerpo del modal --}}
                <div @click="$refs.cuerpo.scrollTo({ top: 0 })"
                    class="flex justify-center border-t border-zinc-100 px-4 py-3 dark:border-zinc-600">
                    {{ $children->links(data: ['scrollTo' => false]) }}
                </div>
            @endif
        </div>
    </dialog>

</div>