<x-layouts::app :title="__('Favoritos')">

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Grid de tarjetas -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                @forelse ($favoritos as $favorito)
                    <div
                        class="bg-white border rounded-lg p-5 flex flex-col justify-between shadow-sm hover:shadow-md transition-shadow">
                        <div>
                            <!-- Título extraído de la relación JSON -->
                            <h3 class="text-lg font-bold text-gray-900 mb-2">
                                {{ $favorito->recurso->metadata['titulo'] ?? 'Sin título' }}
                            </h3>

                            <p class="text-sm text-gray-500 mb-4">
                                Guardado el: {{ $favorito->created_at->format('d/m/Y') }}
                            </p>
                            <p class="text-end">

                                <a href="{{ route('buscador.registro', ['tipo' => 'documento', 'recurso' => $favorito->recurso]) }}"
                                    class="dark:text-white text-right mx-1 my-3  w-fit items-center gap-1 text-xs font-bold uppercase tracking-wide text-guinda 
                                    border-b-2 border-[#86212b] pb-0.5">
                                    Leer más
                                </a>
                            </p>
                        </div>

                        <div class="mt-1 pt-4 border-t">
                            <livewire:boton-favorito :recursoId="$favorito->recurso->id" :key="'fav-' . $favorito->id" />
                        </div>
                    </div>
                @empty
                    <!-- Estado vacío -->
                    <div class="col-span-full text-center py-16 bg-white rounded-lg shadow-sm border">
                        <svg class="mx-auto h-12 w-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900">Aún no tienes favoritos</h3>
                        <p class="mt-1 text-sm text-gray-500">Explora nuestro catálogo de recursos y guárdalos para
                            verlos más tarde.</p>
                    </div>
                @endforelse

            </div>

        </div>
    </div>
</x-layouts::app>
