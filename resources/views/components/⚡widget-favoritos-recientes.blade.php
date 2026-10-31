<?php

use Livewire\Component;

new class extends Component {
    public function render()
    {
        $ultimosFavoritos = Auth::check() ? Auth::user()->favoritos()->with('recurso')->latest()->take(5)->get() : collect();

        return view('livewire.widget-favoritos-recientes', [
            'ultimosFavoritos' => $ultimosFavoritos,
        ]);
    }
};
?>
