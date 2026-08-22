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

<div wire:loading.class="opacity-50" class="transition-opacity duration-300 flex flex-col gap-4">

    @forelse ($colecciones as $index => $item)
        <div data-aos="fade-up" data-aos-duration="500" data-aos-delay="{{ $index * 50 }}">
            <livewire:parent-collection :collection="$item" :key="'col-' . $item->id" />
        </div>
    @empty
        <div class="w-full text-center py-12">
            <h4 class="text-lg font-medium text-zinc-500">No hay colecciones disponibles</h4>
        </div>
    @endforelse

    <div class="mt-4 flex justify-center">
        {{ $colecciones->links() }}
    </div>

</div>