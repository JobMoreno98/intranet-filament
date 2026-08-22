<?php

use Livewire\Volt\Component;
use function Livewire\Volt\state;

state(['area']);

?>

<div class="w-full rounded-md border border-zinc-200 bg-white shadow-xl p-6 flex flex-col items-center text-center gap-4 dark:bg-zinc-700 dark:text-white">

    <span class="w-12 h-12 flex items-center justify-center rounded-full bg-red-800 text-white font-bold text-lg">
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

    <a href="{{ route('area.colecciones', $area->slug) }}"
        class="inline-flex items-center text-xs md:text-base bg-red-800 rounded text-white font-bold py-1 px-4 hover:bg-red-950 transition-colors">
        Ver fondos
    </a>

</div>