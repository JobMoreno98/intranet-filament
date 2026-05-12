@extends('layouts.plantilla')

@section('content')
    <section class="bg-gray-50">
        <div class="mx-auto sm:px-7 px-2 max-w-screen-xl py-20 flex gap-10 flex flex-col lg:flex-row items-center">
            <div class="grid grid-cols-1  gap-4 w-full">

                @foreach ($data as $key => $value)
                    @php
                        $keys = array_keys((array) $value);
                        sort($keys);
                    @endphp
                    <div class="p-6 rounded-md border bg-white flex flex-col  w-full shadow-xl">
                        {{--  
                        <div>
                            <img class="h-auto max-w-full rounded-base" src="https://picsum.photos/400/300"
                                alt="">
                        </div>
                        --}}
                        <p class="text-body text-justify">
                            @foreach ($keys as $item)
                                @if (!str_contains(strtolower($item), 'id') && isset($value->$item) && $value->$item != '' && $value->$item != 0)
                                    <b class="uppercase">{{ $item }}</b>
                                    {{ $value->$item }} <br>
                                @endif
                            @endforeach
                        </p>
                        {{-- 
                        <p class="text-right mt-auto">
                            <a href="{{ route('coleccion.individual', ['tabla' => $coleccion, 'id' => $value->IdElemento]) }}"
                                target="_blank"
                                class="text-sm md:text-base bg-red-400 rounded text-white font-bold py-1 px-6 hover:bg-red-800">
                                Ver información
                            </a>

                        </p>
                     --}}
                    </div>
                @endforeach
            </div>
        </div>
        <div class="mx-auto max-w-7xl">
            {{ $data->links() }}
        </div>

    </section>
    {{--  --}}
@endsection
