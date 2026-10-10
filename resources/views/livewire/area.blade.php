<?php

use Livewire\Volt\Component;
use function Livewire\Volt\state;

state(['area']);

?>

<div class="h-full w-full overflow-hidden rounded-md border border-zinc-200 bg-white shadow-xl flex flex-col text-center dark:bg-zinc-700 dark:text-white dark:border-zinc-600">

    {{-- Portada --}}
    <div class="aspect-[4/3] w-full bg-zinc-100 dark:bg-zinc-600 overflow-hidden">
        @if ($area->imagen)
            <img src="{{ asset('storage/' . $area->imagen) }}" alt="Portada de {{ $area->nombre }}"
                class="h-full w-full object-cover" loading="lazy">
        @else
            <div class="h-full w-full flex items-center justify-center text-zinc-300 dark:text-zinc-400">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v13.5A1.5 1.5 0 0 0 3.75 21Z" />
                </svg>
            </div>
        @endif
    </div>

    <div class="flex flex-1 flex-col items-center gap-4 p-6">

        {{-- La inicial se encima sobre el borde inferior de la portada --}}
        <span
            class="-mt-12 w-12 h-12 flex items-center justify-center rounded-full bg-red-800 text-white font-bold text-lg ring-4 ring-white dark:ring-zinc-700">
            {{ strtoupper(substr($area->nombre, 0, 1)) }}
        </span>

        <div class="flex flex-col gap-1">
            <h2 class="text-2xl font-bold text-zinc-800 dark:text-white">
                {{ $area->nombre }}
            </h2>
            <p class="text-sm text-zinc-500 dark:text-zinc-300">
                {{ $area->ubicacion }}
            </p>
        </div>

        {{-- mt-auto empuja el botón al fondo para que todas las tarjetas queden alineadas --}}
        <a href="{{ route('area.colecciones', $area->slug) }}"
            class="mt-auto inline-flex items-center text-xs md:text-base bg-red-800 rounded text-white font-bold py-1 px-4 hover:bg-red-950 transition-colors">
            Ver fondos
        </a>
    </div>

</div>