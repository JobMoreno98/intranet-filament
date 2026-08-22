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
        ->paginate(9);

    return [
        'colecciones' => $colecciones,
    ];
});

?>

<div wire:loading.class="opacity-50" class="transition-opacity duration-300 flex flex-col gap-4">

    @forelse ($colecciones as $item)
        <livewire:parent-collection :collection="$item" :key="'area-' . $area->id . '-col-' . $item->id" />
    @empty
        <div class="w-full text-center py-12">
            <h4 class="text-lg font-medium text-zinc-500">Esta área aún no tiene colecciones</h4>
        </div>
    @endforelse

    <div class="mt-4 flex justify-center">
        {{ $colecciones->links() }}
    </div>

</div>