<?php

use Livewire\Volt\Component;
use function Livewire\Volt\{state, with, usesPagination};

usesPagination();

state(['areaId' => null]);

with(function () {
    $query = \App\Models\Coleccion::query()
        ->orderBy('nombre', 'ASC');

    if (!empty($this->areaId)) {
        $query->where('areaS_id', $this->areaId);
    }

    return [
        'colecciones' => $query->paginate(9),
    ];
});

?>

<div wire:loading.class="opacity-50" class="flex flex-col gap-6 transition-opacity duration-300">

    @if ($colecciones->count())
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($colecciones as $index => $item)
                <div class="h-full" data-aos="fade-up" data-aos-duration="500" data-aos-delay="{{ $index * 50 }}">
                    <livewire:parent-collection :collection="$item" :key="'col-' . $item->id" />
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