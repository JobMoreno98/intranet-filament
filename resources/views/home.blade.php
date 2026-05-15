@extends('layouts.plantilla')

@section('content')
    <section class="bg-gray-50">
        <div class="mx-auto max-w-screen-xl px-3 sm:px-7 pt-8">
            <form action="{{ route('home') }}" method="GET"
                class="bg-transparent  p-4">

                <div class="flex flex-col lg:flex-row gap-3 lg:items-end">

                    <!-- Input -->
                    <div class="flex-1">
                        <div class="relative">
                            <!-- Heroicon -->
                            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                            </div>

                            <input type="text" name="coleccion" value="{{ request('coleccion') }}"
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
                    <div class="p-6 rounded-md border bg-white flex flex-col gap-6 w-full shadow-xl" data-aos="fade-up"
                        data-aos-duration="500" data-aos-delay="{{ $index * 100 }}"> <!-- escalera -->

                        <div>
                            <img class="mx-auto h-auto max-w-full rounded-base" src="{{ asset('img/bpej.jpg') }}"
                                alt="">
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-center">{{ $item->coleccion }}</h2>
                        </div>
                        <p class="text-body text-justify"></p>
                        <p class="text-right mt-auto">
                            <a href="{{ route('coleccion.show', $item->clave) }}" target="_blank"
                                class="text-sm md:text-base bg-red-800 rounded text-white font-bold py-1 px-6 hover:bg-red-950">
                                Ver
                            </a>
                        </p>
                    </div>
                @empty
                    <h3>No hay resultados en la búsqueda</h3>
                @endforelse
            </div>

        </div>
        <div class="mx-auto max-w-7xl px-2">
            {{ $colecciones->links() }}
        </div>

    </section>
@endsection
