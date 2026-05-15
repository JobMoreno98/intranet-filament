@extends('layouts.plantilla')

@section('content')
    <section class="bg-gray-50 min-h-screen py-8">
        <div class="mx-auto max-w-screen-md px-4">

            <!-- Botón de Regreso -->
            <div class="mb-4">
                <a href="{{ url()->previous() }}"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-100 transition shadow-sm">
                    <x-heroicon-o-arrow-left class="w-4 h-4" /> Volver a los resultados
                </a>
            </div>

            <!-- Ficha Técnica Única -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-2xl overflow-hidden">
                <div class="bg-red-900 px-6 py-4 flex justify-between items-center border-b border-gray-800">
                    <h3 class="text-lg font-bold text-white">Ficha Completa del Registro</h3>
                    <span class="bg-orange-800 text-white text-xs uppercase font-bold px-3 py-1.5 rounded-md shadow">
                        {{ $coleccionNombre }}
                    </span>
                </div>

                <div class="divide-y divide-gray-100">
                    @foreach ((array) $registro as $columna => $valor)
                        <!-- TRUCO: Si la columna actual está en la lista de exclusión, la saltamos de inmediato -->
                        @if (in_array($columna, $omitir))
                            @continue
                        @endif

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 p-4 hover:bg-gray-50 transition">
                            <!-- Nombre del campo -->
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider sm:pt-0.5">
                                {{ $labels[$columna] ?? ucwords(str_replace('_', ' ', $columna)) }}

                            </div>

                            <!-- Valor del campo -->
                            <div class="text-sm text-gray-800 sm:col-span-2 space-y-1 text-break font-medium">
                                @if (is_null($valor) || $valor === '')
                                    <span class="text-gray-300 italic text-xs">Sin información / Vacío</span>
                                @else
                                    {{ $valor }}
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="bg-gray-50 px-6 py-3 border-t border-gray-100 text-right text-xs text-gray-400">
                    Colección: <span class="text-red-700 font-semibold">{{ $coleccionNombre }}</span>
                </div>
            </div>

        </div>
    </section>
@endsection
