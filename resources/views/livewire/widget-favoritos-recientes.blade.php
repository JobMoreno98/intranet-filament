<div>
    <h2 class="text-xl font-bold text-gray-800 mb-4">Agregados recientemente</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($ultimosFavoritos as $favorito)
            <div class="bg-white border rounded-lg p-4 flex flex-col justify-between shadow-sm">
                <div>
                    <h3 class="font-semibold text-gray-900 truncate">
                        {{ $favorito->recurso->metadata['titulo'] ?? 'Sin título' }}
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">
                        Guardado {{ $favorito->created_at->diffForHumans() }}
                    </p>
                </div>
                
                <div class="mt-3 pt-3 border-t">
                    <livewire:boton-favorito :recursoId="$favorito->recurso->id" :key="'dash-fav-'.$favorito->id" />
                </div>
            </div>
        @empty
            <div class="col-span-full text-sm text-gray-500 bg-gray-50 p-6 rounded-lg border border-dashed text-center">
                Aún no tienes recursos guardados en tus favoritos.
            </div>
        @endforelse
    </div>

    @if($ultimosFavoritos->count() > 0)
        <div class="mt-4">
            <a href="{{ route('favoritos.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 hover:underline">
                Ver todos mis favoritos &rarr;
            </a>
        </div>
    @endif
</div>