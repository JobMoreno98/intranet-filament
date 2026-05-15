@extends('layouts.plantilla')

@section('content')
    <section class="bg-gray-50">
        <div class="mx-auto sm:px-7 px-2 max-w-screen-xl pt-10">
            <form action="{{ route('home') }}" method="get" class="w-full flex flex-col lg:flex-row items-end gap-4">
                <div class="flex flex-col w-full">
                    <input type="text" name="coleccion" placeholder="Buscar colección..."
                        class="w-full border border-gray-300 rounded-md p-1 focus:ring-2 focus:ring-red-900 outline-none">
                </div>
                <button type="submit"
                    class="text-sm bg-red-800 hover:bg-red-950 text-white px-6 py-2 rounded-md transition duration-200">
                    Buscar
                </button>
                <a class="text-sm bg-red-800 hover:bg-red-950 text-white px-6 py-2 rounded-md transition duration-200"
                    href="{{ route('home') }}">Limpiar</a>
            </form>
        </div>

        <div class="mx-auto sm:px-7 px-2 max-w-screen-xl py-20 flex gap-10 flex flex-col lg:flex-row items-center">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach ($colecciones as $item)
                    <div class="p-6 rounded-md border bg-white flex flex-col gap-6 w-full shadow-xl">

                        <div>
                            <img class="h-auto max-w-full rounded-base" src="https://picsum.photos/400/300" alt="">
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-center">{{ $item->coleccion }}</h2>
                        </div>
                        <p class="text-body text-justify">

                        </p>
                        <p class="text-right mt-auto">
                            <a href="{{ route('coleccion.show', $item->clave) }}" target="_blank"
                                class="text-sm md:text-base bg-red-800 rounded text-white font-bold py-1 px-6 hover:bg-red-950">
                                Ver
                            </a>
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="mx-auto max-w-7xl px-2">
            {{ $colecciones->links() }}
        </div>

    </section>
@endsection
