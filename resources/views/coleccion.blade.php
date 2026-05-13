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
                            'epocaperiodo' => 'Epoca o Periodo',
                            'nombrepersonajeprincipal' => 'Nombre de Personaje principal',
                            'nombrepersonajesecundario' => 'Nombre de Personaje secundario',
                            'clavefondoprincipal' => 'Clave Fondo Principal',
                            'fondoprincipal' => 'Fondo principal',
                            'lugar1' => 'Lugar 1',
                            'lugar2' => 'Lugar 2',
                            'anio2' => 'Año 2',
                        ];

                        // Orden especial: primero titulo y autor si existen
                        $prioritarios = ['titulo', 'autor', 'anio', 'clavefondoprincipal', 'fondoprincipal'];

                        $primero = array_filter($keys, fn($k) => in_array(strtolower($k), $prioritarios));
                        $resto = array_diff($keys, $primero);

                        sort($resto);

                        $ordenFinal = array_merge($primero, $resto);
                    @endphp

                    <div class="p-6 rounded-md border bg-white flex flex-col w-full shadow-xl">
                        <p class="text-body text-justify">
                            @foreach ($ordenFinal as $item)
                                @if (
                                    !str_contains(strtolower($item), 'id') &&
                                        isset($value->$item) &&
                                        $value->$item != '' &&
                                        $value->$item != '-' &&
                                        $value->$item != 0)
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
