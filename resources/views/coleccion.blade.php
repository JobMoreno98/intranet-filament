@extends('layouts.plantilla')

@section('content')
    <section class="bg-gray-50">
        <div class="mx-auto sm:px-7 px-2 max-w-screen-xl py-20 flex gap-10 flex flex-col lg:flex-row items-center">
            <div class="grid grid-cols-1  gap-4 w-full">
                @foreach ($data as $key => $value)
                    @php
                        $keys = array_keys((array) $value);

                        // Diccionario de traducciones
                        $cambios = [
                            'anio' => 'Año',
                            'tipoarchivo' => 'Tipo Archivo',
                            'numpaginas' => 'No. Páginas',
                            'numarchivos' => 'No. Archivos',
                            'numero' => 'Número',
                            'titulo' => 'Título',
                            'autor' => 'Autor',
                            'dia' => 'Día',
                            'paginas' => 'Páginas',
                            'epocaperiodo' => 'Epoca o Periodo'
                        ];

                        // Orden especial: primero titulo y autor si existen
                        $prioritarios = ['titulo', 'autor'];

                        // Separamos los prioritarios del resto usando strtolower solo para comparar
                        $primero = array_filter($keys, fn($k) => in_array(strtolower($k), $prioritarios));
                        $resto = array_diff($keys, $primero);

                        // Si quieres, ordena el resto alfabéticamente
                        sort($resto);

                        // Unión final: primero los prioritarios, luego el resto
                        $ordenFinal = array_merge($primero, $resto);
                    @endphp

                    <div class="p-6 rounded-md border bg-white flex flex-col w-full shadow-xl">
                        <p class="text-body text-justify">
                            @foreach ($ordenFinal as $item)
                                @if (!str_contains(strtolower($item), 'id') && isset($value->$item) && $value->$item != '' && $value->$item != 0)
                                    @php
                                        $label = $cambios[strtolower($item)] ?? $item;
                                    @endphp

                                    <b class="uppercase">{{ $label }}</b>
                                    {{ $value->$item }} <br>
                                @endif
                            @endforeach
                        </p>
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
