<?php

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public $recursoId;
    public $esFavorito = false;

    public function mount($recursoId)
    {
        $this->recursoId = $recursoId;

        // Solo busca favoritos si hay una sesión activa
        if (Auth::check()) {
            $this->esFavorito = Auth::user()->favoritos()->where('recursos_id', $this->recursoId)->exists();
        }
    }

    public function toggleFavorito()
    {
        // Si no está logueado, lo redirigimos a la pantalla de login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if ($this->esFavorito) {
            Auth::user()->favoritos()->where('recursos_id', $this->recursoId)->delete();
            $this->esFavorito = false;
        } else {
            Auth::user()
                ->favoritos()
                ->create(['recursos_id' => $this->recursoId]);
            $this->esFavorito = true;
        }
    }

    public function render()
    {
        return view('livewire.boton-favorito');
    }
};
?>

<button wire:click="toggleFavorito"
    class="flex items-center gap-2 px-4 py-2 text-sm font-medium rounded transition-colors {{ $esFavorito ? 'text-red-600 bg-red-50 hover:bg-red-100' : 'text-gray-600 bg-gray-100 hover:bg-gray-200' }}">

    <!-- Ícono de Corazón dinámico -->
    <svg class="w-5 h-5 {{ $esFavorito ? 'fill-current' : 'fill-none stroke-current' }}" viewBox="0 0 24 24"
        stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round"
            d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
    </svg>

    {{ $esFavorito ? 'Quitar de favoritos' : 'Agregar a favoritos' }}
</button>
