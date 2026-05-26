@extends('layouts.plantilla')

@section('content')
    <section class="bg-gray-50 min-h-screen">
        <!-- Contenedor del Buscador Superior -->
        <div class="mx-auto max-w-screen-xl px-3 sm:px-7 pt-8">
            <form action="{{ route('buscador') }}" method="GET" class="bg-transparent p-4">
                <div class="flex flex-col lg:flex-row gap-3 lg:items-end">
                    <div class="flex-1">
                        <div class="relative">
                            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                            </div>
                            <!-- Cambiamos el name a 'q' para Meilisearch -->
                            <input type="text" name="q" value="{{ $term }}"
                                placeholder="Buscar en todos los registros de la base de datos..."
                                class="w-full pl-10 pr-3 py-2.5 text-sm rounded-xl border border-gray-300
                               bg-white focus:ring-2 focus:ring-red-100 focus:border-red-700 outline-none transition">
                        </div>
                    </div>

                    <div class="flex gap-2 w-full lg:w-auto">
                        <flux:button type="submit" variant="primary" size="sm"
                            class="w-full lg:w-auto inline-flex items-center justify-center gap-1.5 px-6 py-2 text-sm font-medium rounded-xl h-10 bg-red-800 hover:bg-red-900 text-white transition shadow-sm">
                            <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                        </flux:button>

                        <flux:button href="{{ route('home') }}" variant="ghost" size="sm"
                            class="w-full lg:w-auto inline-flex items-center justify-center gap-1.5 h-10 px-4 py-2 text-sm font-medium rounded-xl bg-white hover:bg-gray-200 text-gray-700 border border-gray-200 transition">
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </flux:button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabla de Resultados Coincidentes -->
        <div class="mx-auto sm:px-7 px-3 max-w-screen-xl py-6">
            <div class="mb-4">
                <p class="text-sm text-gray-500">
                    Se encontraron <span class="font-bold text-gray-800">{{ $resultados->total() }}</span> registros
                    coincidentes para "<span class="font-semibold text-red-800">{{ $term }}</span>".
                </p>
            </div>

            <div class=" overflow-hidden">

                <!-- ========================================== -->
                <!-- 1. VISTA TABLA (Pantallas MD en adelante)  -->
                <!-- ========================================== -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-900 text-white text-sm font-medium">
                            <tr>
                                <th class="p-4 pl-6">Tipo</th>
                                <th class="p-4">Extracto de Coincidencia</th>
                                <th class="p-4 text-center w-40">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                            @foreach ($resultados as $res)
                                <tr class="hover:bg-gray-50 transition">
                                    <!-- Celda de Colección -->
                                    <td class="p-4 pl-6">
                                        <span class="font-bold text-gray-900 block">{{ $res['tipo'] }}</span>
                                    </td>
                                    <!-- Celda de Fragmento -->
                                    <td class="p-4 text-gray-500 text-xs max-w-xs truncate-2-lines">
                                        <h5 class="font-bold"> {{ $res['titulo_resultado'] }}</h5>  <br>
                                        {!! $res['coincidencia'] !!}
                                    </td>
                                    <!-- Botón de acción -->
                                    <td class="p-4 text-center">
                                        @if ($res['registro_id'])
                                            <a href="{{ route('buscador.registro', ['tipo' => $res['tipo'], 'id' => $res['registro_id']]) }}"
                                                class="inline-flex items-center justify-center px-4 py-1.5 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition shadow-sm whitespace-nowrap">
                                                Ver información
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach

                            @if ($resultados->isEmpty())
                                <tr>
                                    <td colspan="3" class="p-12 text-center text-gray-400 font-medium">
                                        No se encontraron registros que coincidan con la búsqueda.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <!-- ========================================== -->
                <!-- 2. VISTA CARDS (Pantallas Móviles)         -->
                <!-- ========================================== -->
                <div class="block md:hidden divide-y divide-gray-100 flex flex-col gap-4">
                    @foreach ($resultados as $res)
                        <div class="p-5 my-2 shadow-md border border-gray-200 bg-white">
                            <div class="flex flex-col gap-3 ">
                                <!-- Encabezado de la Card -->
                                <div>
                                    <span
                                        class="text-[10px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded uppercase tracking-wider font-semibold inline-block mt-1">{{ $res['tipo'] }}</span>
                                </div>

                                <!-- Contenido / Extracto -->
                                <div class="text-gray-600 text-sm bg-gray-50 p-3 rounded-lg border border-gray-100">
                                    <span
                                        class="text-xs font-semibold text-gray-400 block mb-1 uppercase tracking-wider">Coincidencia:</span>
                                    <div class="line-clamp-3 text-xs leading-relaxed text-gray-500">
                                        {!! $res['tipo'] !!}
                                    </div>
                                </div>

                                <!-- Acción de la Card -->
                                @if ($res['registro_id'])
                                    <div class="mt-1">
                                        <a href="{{ route('buscador.registro', ['tipo' => $res['tipo'], 'id' => $res['registro_id']]) }}"
                                            class="w-full inline-flex items-center justify-center px-4 py-2.5 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition shadow-sm">
                                            Ver información
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    @if ($resultados->isEmpty())
                        <div class="p-12 text-center text-gray-400 font-medium">
                            <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            No se encontraron registros que coincidan con la búsqueda.
                        </div>
                    @endif
                </div>

            </div>

            <!-- Paginación con Tailwind -->
            <div class="mt-6">
                {{ $resultados->links() }}
            </div>
        </div>
    </section>

    <!-- Estilo para que Meilisearch pinte las palabras encontradas en color oro/amarillo -->
    <style>
        em {
            background-color: #fef08a !important;
            /* amarillo tailwind */
            color: #854d0e !important;
            font-style: normal !important;
            font-weight: 700 !important;
            padding: 1px 3px;
            border-radius: 4px;
        }
    </style>
@endsection
