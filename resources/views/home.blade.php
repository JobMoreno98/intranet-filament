@extends('layouts.plantilla')

@section('content')
    <section class="bg-gray-50">
        <div class="mx-auto sm:px-7 px-2 max-w-screen-xl py-20 flex gap-10 flex flex-col lg:flex-row items-center">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach ($colecciones as $item)
                    <div class="p-6 rounded-md border bg-white flex flex-col gap-6 w-full shadow-xl">
                        <div>
                            <h2 class="text-xl font-bold text-center">{{ $item->coleccion }}</h2>
                        </div>
                        <div>
                            <img class="h-auto max-w-full rounded-base" src="https://picsum.photos/400/300" alt="">
                        </div>
                        <p class="text-body text-justify">

                        </p>
                        <p class="text-right mt-auto">
                            <a href="{{ route('coleccion.show',  $item->clave) }}" target="_blank"
                                class="text-sm md:text-base bg-red-400 rounded text-white font-bold py-1 px-6 hover:bg-red-800">

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
