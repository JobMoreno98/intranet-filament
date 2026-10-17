<?php

use Livewire\Volt\Component;
use function Livewire\Volt\{state, with, usesPagination};

usesPagination();

state(['area']);

with(function () {
    // Ajusta el nombre de la relación según tu modelo Area
    $colecciones = $this->area
        ->colecciones()
        ->orderBy('nombre', 'ASC')
        ->paginate(12);

    return [
        'colecciones' => $colecciones,
    ];
});

?>

<div wire:loading.class="opacity-50" class="flex flex-col gap-6 w-full transition-opacity duration-300">

    @if ($colecciones->isNotEmpty())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 w-full">
            @foreach ($colecciones as $index => $item)
                <div class="h-full min-w-0" data-aos="fade-up" data-aos-duration="500"
                    data-aos-delay="{{ $index * 50 }}">
                    <livewire:parent-collection :collection="$item" :key="'area-' . $area->id . '-col-' . $item->id" />
                </div>
            @endforeach
        </div>
    @else
        <div class="col-span-full w-full text-center py-12">
            <h4 class="text-lg font-medium text-zinc-500">Esta área aún no tiene colecciones</h4>
        </div>
    @endif

    @if ($colecciones->hasPages())
        <div class="mx-auto w-full py-2">
            <div class="flex flex-wrap justify-center items-center gap-2">
                {{ $colecciones->links() }}
            </div>
        </div>
    @endif

</div>