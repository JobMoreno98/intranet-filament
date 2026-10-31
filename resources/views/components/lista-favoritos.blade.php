@props(['favoritos'])

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    @forelse($favoritos as $favorito)
        <div class="p-4 border rounded shadow">
            <!-- Asumiendo que usas el mismo JSON metadata para el título -->
            <h3 class="font-bold">{{ $favorito->recurso->metadata['titulo'] ?? 'Sin título' }}</h3>
            
            <form action="{{ route('favoritos.toggle') }}" method="POST" class="mt-2">
                @csrf
                <input type="hidden" name="recursos_id" value="{{ $favorito->recursos_id }}">
                <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                    Quitar de mis favoritos
                </button>
            </form>
        </div>
    @empty
        <p class="text-gray-500">Aún no tienes recursos guardados.</p>
    @endforelse
</div>