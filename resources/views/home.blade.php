@extends('layouts.plantilla')

@section('content')
    <section class="bg-gray-50">
        <div class="mx-auto max-w-screen-xl px-3 sm:px-7 pt-8">
            <form action="{{ route('buscador') }}" method="GET" class="bg-transparent  p-4">

                <div class="flex flex-col lg:flex-row gap-3 lg:items-end">
                    <div class="flex-1">
                        <div class="relative">
                            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                            </div>

                            <input type="text" name="q" value="{{ request('coleccion') }}"
                                placeholder="Buscar colección..."
                                class="w-full pl-10 pr-3 py-2.5 text-sm rounded-xl border border-gray-300
                               bg-white focus:ring-2 focus:ring-red-100
                               focus:border-red-700 outline-none transition">
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex gap-2 w-full lg:w-auto">

                        <flux:button type="submit" variant="primary" size="sm"
                            class="
                                w-full
                            inline-flex items-center justify-center gap-1.5
                        px-4   py-2 text-sm font-medium rounded-xl h-10
                        bg-red-800 hover:bg-red-900 text-white
                        transition shadow-sm">
                            <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                        </flux:button>

                        <flux:button href="{{ route('home') }}" variant="ghost" size="sm"
                            class=" w-full inline-flex items-center justify-center gap-1.5 h-10
                        px-4 py-2 text-sm font-medium rounded-xl bg-white
                        bg-gray-100 hover:bg-gray-200 text-gray-700
                        border border-gray-200 transition">
                            <x-heroicon-o-x-mark class="w-5 h-5" />

                        </flux:button>

                    </div>
                </div>
            </form>
        </div>

        <div class="mx-auto sm:px-7 px-2 max-w-screen-xl py-10 flex gap-10 flex flex-col lg:flex-row items-center">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                @forelse ($colecciones as $index => $item)
                    @php
                        // 1. Calculamos el nivel de profundidad actual subiendo por los padres
                        $depth = 0;
                        $parent = $item->parent;
                        while ($parent) {
                            $depth++;
                            $parent = $parent->parent;
                        }
                        $marginClasses = match ($depth) {
                            1 => 'md:ml-12 border-l-2 border-zinc-300 pl-6', // Primer hijo
                            2 => 'md:ml-24 border-l-2 border-l-dashed border-zinc-300 pl-6', // Nieto
                            3 => 'md:ml-36 border-l-2 border-l-dotted border-zinc-300 pl-6', // Bisnieto
                            default => 'w-full', // Nivel raíz o superiores
                        };
                    @endphp

                    <div class="{{ $marginClasses }} p-6 rounded-md border bg-white flex flex-col gap-6 shadow-xl relative"
                        data-aos="fade-up" data-aos-duration="500" data-aos-delay="{{ $index * 100 }}">

                        @if ($depth > 0)
                            <div
                                class="absolute -left-3 top-1/2 -translate-y-1/2 bg-zinc-100 text-zinc-500 text-xs px-1.5 py-0.5 rounded border font-mono">
                                Sub-{{ $depth }}
                            </div>
                        @endif

                        <div>
                            <img style="aspect-ratio:1/1;" class="mx-auto h-auto max-w-full rounded-base"
                                src="{{ asset('storage/colecciones/'.$item->foto) }}" alt="{{ $item->nombre }}">
                        </div>

                        <div>
                            <h2 class="text-xl font-bold text-center">
                                {{ $item->nombre }}
                            </h2>
                        </div>

                        <p class="text-body text-justify text-zinc-600 text-sm line-clamp-3">
                            {{ $item->descripcion }}
                        </p>

                        <p class="text-right mt-auto">
                            <a href="{{ route('coleccion.show', $item->slug) }}" target="_blank"
                                class="text-sm md:text-base bg-red-800 rounded text-white font-bold py-1 px-6 hover:bg-red-950 transition-colors">
                                Ver
                            </a>
                        </p>
                    </div>
                @empty
                    <div class="col-span-full text-center py-12">
                        <h3 class="text-lg font-medium text-zinc-500">No hay resultados en la búsqueda</h3>
                    </div>
                @endforelse
            </div>

        </div>
        <div class="mx-auto max-w-7xl px-2">
            {{ $colecciones->links() }}
        </div>

    </section>
@endsection
