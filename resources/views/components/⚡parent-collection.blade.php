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
