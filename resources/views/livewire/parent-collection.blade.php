<?php

use function Livewire\Volt\{state, with, usesPagination};

usesPagination();

state([
    'collection',
    'mostrarHijos' => false, // true: los internos van en la lista, sin modal
    'compacto' => false, // true: tarjeta pequeña (cuando vive dentro de un modal)
    'cargado' => false, // los hijos se consultan hasta que se abre el modal
]);

// Se dispara al abrir el modal por primera vez
$cargar = function () {
    $this->cargado = true;
};

with(function () {
    // Con los internos visibles en la lista no hace falta el modal ni la consulta
    if ($this->mostrarHijos) {
        return ['totalHijos' => 0, 'children' => null];
    }

    // Si el padre ya trajo children_count (withCount) nos ahorramos una consulta por tarjeta
    $total = $this->collection->getAttributes()['children_count'] ?? $this->collection->children()->count();

    return [
        'totalHijos' => $total,
        'children' =>
            $this->cargado && $total > 0
                ? $this->collection
                    ->children()
                    ->withCount('children')
                    ->orderBy('nombre', 'ASC')
                    ->paginate(12, ['*'], 'page-' . $this->collection->id)
                : null,
    ];
});

?>

{{-- Alpine en la raíz. h-full para que la tarjeta iguale la altura de las demás en el grid --}}
<div class="h-full w-full" x-data="{
    abrir() {
        this.$refs.modal.showModal();
        document.body.classList.add('overflow-hidden');
        if (!this.$wire.cargado) this.$wire.cargar();
    },
    cerrar() {
        this.$refs.modal.close();
    },
}">

    {{-- Tarjeta del fondo --}}
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

        <div @class([
            'flex flex-1 flex-col items-center text-center',
            'gap-3 p-4' => !$compacto,
            'gap-2 p-3' => $compacto,
        ])>
            <div class="flex flex-col gap-1">
                @if ($compacto)
                    <h4 class="line-clamp-2 text-sm font-bold text-zinc-800 dark:text-white">
                        {{ $collection->ruta }}
                    </h4>
                @else
                    <h2 class="text-lg font-bold text-zinc-800 dark:text-white">
                        {{ $collection->ruta }}
                    </h2>
                @endif

                <p @class([
                    'text-xs text-zinc-500 dark:text-zinc-300',
                    'line-clamp-3' => !$compacto,
                    'line-clamp-2' => $compacto,
                ])>
                    {{ $collection->descripcion }}
                </p>
            </div>

            {{-- mt-auto empuja los botones al fondo para alinear todas las tarjetas --}}
            <div class="mt-auto flex flex-wrap justify-center gap-2 pt-1">
                <a href="{{ route('coleccion.show', $collection->slug) }}" target="_blank"
                    class="inline-flex items-center rounded bg-red-800 px-3 py-1 text-xs font-bold text-white transition-colors hover:bg-red-950">
                    {{ $compacto ? 'Ver' : 'Ver Fondo' }}
                </a>

                @if ($totalHijos > 0)
                    <button type="button" @click="abrir()"
                        class="inline-flex items-center rounded border border-zinc-300 bg-zinc-100 px-3 py-1 text-xs font-bold text-zinc-800 transition-colors hover:bg-zinc-200 dark:border-zinc-500 dark:bg-zinc-600 dark:text-white dark:hover:bg-zinc-500">
                        Ver fondos internos ({{ $totalHijos }})
                    </button>
                @endif
            </div>
        </div>
    </div>

    @if ($totalHijos > 0)
        {{-- Los <dialog> anidados funcionan: showModal() los manda a la capa superior sin importar el DOM --}}
        <dialog x-ref="modal" wire:ignore.self @click.self="cerrar()"
            @close="if (!document.querySelector('dialog[open]')) document.body.classList.remove('overflow-hidden')"
            aria-labelledby="titulo-modal-{{ $collection->id }}"
            class="m-auto w-[min(72rem,calc(100%-1.5rem))] rounded-lg bg-white p-0 text-zinc-800 shadow-2xl backdrop:bg-black/60 dark:bg-zinc-700 dark:text-white">

            <div class="flex max-h-[85vh] flex-col">

                <div
                    class="flex items-center justify-between gap-3 border-b border-zinc-100 px-4 py-3 sm:px-6 dark:border-zinc-600">
                    <div class="min-w-0">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-zinc-400">
                            Fondos internos · {{ $totalHijos }}
                        </p>
                        <h3 id="titulo-modal-{{ $collection->id }}" class="truncate text-base font-bold sm:text-lg">
                            {{ $collection->nombre }}
                        </h3>
                    </div>

                    <button type="button" @click="cerrar()" aria-label="Cerrar"
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md text-zinc-500 transition-colors hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Cuerpo con scroll propio --}}
                <div x-ref="cuerpo" wire:loading.class="opacity-50"
                    class="flex-1 overflow-y-auto p-4 transition-opacity duration-300 sm:p-6">
                    @if ($children)
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4">
                            @foreach ($children as $child)
                                {{-- Cada hijo es otra tarjeta: si tiene internos, trae su propio botón y modal --}}
                                <livewire:parent-collection :collection="$child" :compacto="true"
                                    :key="'hijo-' . $collection->id . '-' . $child->id" />
                            @endforeach
                        </div>
                    @else
                        <div class="flex items-center justify-center py-16 text-sm text-zinc-400">
                            Cargando fondos internos…
                        </div>
                    @endif
                </div>

                @if ($children)
                    {{-- scrollTo => false: evita que la paginación desplace la página de fondo; subimos el cuerpo del modal --}}
                    <div @click="$refs.cuerpo.scrollTo({ top: 0 })"
                        class="flex flex-col items-center gap-2 border-t border-zinc-100 px-4 py-3 sm:flex-row sm:justify-between dark:border-zinc-600">
                        <p class="text-xs text-zinc-500 dark:text-zinc-300">
                            Mostrando {{ $children->firstItem() }}–{{ $children->lastItem() }} de
                            {{ $children->total() }}
                        </p>

                        @if ($children->hasPages())
                            {{ $children->links(data: ['scrollTo' => false]) }}
                        @endif
                    </div>
                @endif
            </div>
        </dialog>
    @endif

</div>