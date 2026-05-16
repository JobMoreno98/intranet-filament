@extends('layouts.plantilla')

@section('content')
    <section class="bg-zinc-950 min-h-screen">

        <div class="flex flex-col lg:flex-row lg:h-screen">
            <!-- VISOR -->
            <main class="flex-1 flex flex-col min-h-0">
                @auth

                    <!-- TOPBAR -->
                    <div class="flex items-center justify-between px-4 py-3 border-b border-zinc-800 bg-zinc-900">

                        <a href="{{ url()->previous() }}"
                            class="inline-flex items-center gap-2 px-3 py-2 text-xs font-medium text-zinc-300 bg-zinc-800 rounded-lg hover:bg-zinc-700 transition">

                            <x-heroicon-o-arrow-left class="w-4 h-4" />

                            Volver

                        </a>

                        <span class="bg-red-900 text-white text-xs uppercase font-bold px-3 py-1 rounded-md">

                            {{ $coleccionNombre }}

                        </span>

                    </div>

                    <!-- MOBILE INFO -->
                    <aside class="lg:hidden border-b border-zinc-800 bg-zinc-900 p-3 text-zinc-300">

                        <details class="group rounded-lg border border-zinc-800 bg-zinc-950">

                            <summary
                                class="cursor-pointer list-none px-4 py-3 text-sm font-semibold text-white flex items-center justify-between">

                                Información del libro

                                <span class="transition duration-200 group-open:rotate-180">
                                    ▼
                                </span>

                            </summary>

                            <div class="divide-y divide-zinc-800">
                                @isset($recurso)
                                    <!-- RECURSO -->
                                    @foreach ((array) $registro as $columna => $valor)
                                        @if (in_array($columna, $omitir))
                                            @continue
                                        @endif

                                        @php
                                            $valorTexto = trim(strip_tags((string) $valor));

                                            $esCorto = mb_strlen($valorTexto) <= 40;

                                            $camposCompactos = [
                                                'anio',
                                                'fecha',
                                                'paginas',
                                                'tomo',
                                                'volumen',
                                                'idioma',
                                                'isbn',
                                                'clave',
                                                'folio',
                                                'numero',
                                            ];

                                            $camposLargos = [
                                                'descripcion',
                                                'contenido',
                                                'notas',
                                                'resumen',
                                                'observaciones',
                                            ];

                                            $compacto =
                                                !in_array($columna, $camposLargos) &&
                                                ($esCorto || in_array($columna, $camposCompactos));
                                        @endphp

                                        <div class="{{ $compacto ? 'lg:col-span-1' : 'lg:col-span-2' }}">

                                            <div
                                                class="h-full rounded-xl border border-zinc-800 bg-zinc-950 p-4 hover:border-zinc-700 transition">

                                                <!-- Nombre -->
                                                <div class="text-[11px] font-bold text-zinc-500 uppercase tracking-wider mb-2">
                                                    {{ $labels[$columna] ?? ucwords(str_replace('_', ' ', $columna)) }}
                                                </div>

                                                <!-- Valor -->
                                                <div class="text-sm text-zinc-200 break-words leading-relaxed">
                                                    @if (is_null($valor) || $valor === '')
                                                        <span class="text-zinc-500 italic text-xs">
                                                            Sin información
                                                        </span>
                                                    @else
                                                        {{ $valor }}
                                                    @endif
                                                </div>

                                            </div>

                                        </div>
                                    @endforeach
                                @endisset
                                <!-- PÁGINAS -->
                                <div class="p-4 space-y-1">

                                    <span class="block text-zinc-500 uppercase text-[11px] font-semibold tracking-wider">

                                        Páginas

                                    </span>

                                    <div class="text-sm text-zinc-200">
                                        @isset($paginas)
                                            {{ count($paginas) }}
                                        @endisset

                                    </div>

                                </div>

                                <!-- REGISTRO -->
                                @foreach ((array) $registro as $columna => $valor)
                                    @if (in_array($columna, $omitir))
                                        @continue
                                    @endif

                                    @php
                                        $valorTexto = trim(strip_tags((string) $valor));

                                        $esCorto = mb_strlen($valorTexto) <= 40;

                                        $camposCompactos = [
                                            'anio',
                                            'fecha',
                                            'paginas',
                                            'tomo',
                                            'volumen',
                                            'idioma',
                                            'isbn',
                                            'clave',
                                            'folio',
                                            'numero',
                                        ];

                                        $camposLargos = [
                                            'descripcion',
                                            'contenido',
                                            'notas',
                                            'resumen',
                                            'observaciones',
                                        ];

                                        $compacto =
                                            !in_array($columna, $camposLargos) &&
                                            ($esCorto || in_array($columna, $camposCompactos));
                                    @endphp

                                    <div class="{{ $compacto ? 'lg:col-span-1' : 'lg:col-span-2' }}">

                                        <div
                                            class="h-full rounded-xl border border-zinc-800 bg-zinc-950 p-4 hover:border-zinc-700 transition">

                                            <!-- Nombre -->
                                            <div class="text-[11px] font-bold text-zinc-500 uppercase tracking-wider mb-2">
                                                {{ $labels[$columna] ?? ucwords(str_replace('_', ' ', $columna)) }}
                                            </div>

                                            <!-- Valor -->
                                            <div class="text-sm text-zinc-200 break-words leading-relaxed">
                                                @if (is_null($valor) || $valor === '')
                                                    <span class="text-zinc-500 italic text-xs">
                                                        Sin información
                                                    </span>
                                                @else
                                                    {{ $valor }}
                                                @endif
                                            </div>

                                        </div>

                                    </div>
                                @endforeach

                            </div>

                        </details>

                    </aside>

                    <!-- VIEWER -->
                    <!-- Añadimos max-w-4xl para limitar el ancho y mx-auto para centrarlo -->
                    <div id="visor-container" class="relative flex-1 min-h-0 max-w-5xl mx-auto w-full">
                        <div id="viewer" class="h-full overflow-auto flex justify-center bg-zinc-800 p-4">
                            <!-- El canvas mantiene su renderizado pero contenido en el ancho máximo -->
                            <canvas id="page-canvas" class="max-w-full h-auto shadow-lg"></canvas>
                        </div>
                    </div>

                    <!-- MOBILE PAGE -->
                    <div class="lg:hidden px-4 py-2 bg-zinc-900 border-t border-zinc-800 text-center">

                        <p id="page-indicator" class="text-xs text-zinc-400 font-medium">
                            @isset($paginas)
                                1 / {{ count($paginas) }}
                            @endisset

                        </p>

                    </div>

                    <!-- MOBILE CONTROLS -->
                    <div class="lg:hidden flex items-center justify-between gap-3 p-3 border-t border-zinc-800 bg-zinc-900">

                        <button id="prev-page"
                            class="flex-1 rounded-lg bg-zinc-800 hover:bg-zinc-700 text-white py-3 text-sm font-semibold transition">

                            ← Anterior

                        </button>

                        <button id="next-page"
                            class="flex-1 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white py-3 text-sm font-semibold transition">

                            Siguiente →

                        </button>

                    </div>
                @endauth
                @guest
                    <div class=" border border-zinc-200 bg-white p-10 text-center shadow-sm h-full">
                        <x-heroicon-o-lock-closed class="w-12 h-12 mx-auto text-zinc-400 mb-4" />

                        <h3 class="text-lg font-bold text-zinc-800">
                            Inicia sesión para visualizar el contenido
                        </h3>

                        <p class="text-sm text-zinc-500 mt-2">
                            Debes autenticarte para acceder al visor digital.
                        </p>

                        <a href="{{ route('login') }}"
                            class="inline-flex items-center gap-2 mt-5 px-5 py-2.5 rounded-xl bg-red-900 text-white text-sm font-semibold hover:bg-red-800 transition">
                            <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4" />
                            Iniciar sesión
                        </a>
                    </div>
                @endguest
            </main>

            <!-- DESKTOP SIDEBAR -->
            <aside class="hidden lg:flex lg:flex-col w-[420px] border-l border-zinc-800 bg-zinc-900">

                <!-- HEADER -->
                <div class="px-6 py-5 border-b border-zinc-800">

                    <h2 class="text-lg font-bold text-white">
                        Ficha del Registro
                    </h2>

                    <p class="text-xs text-zinc-500 mt-1">
                        Información completa del documento
                    </p>

                </div>

                <!-- CONTENT -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-2 mx-4 my-2">

                    @foreach ((array) $registro as $columna => $valor)
                        @if (in_array($columna, $omitir))
                            @continue
                        @endif

                        @php
                            $valorTexto = trim(strip_tags((string) $valor));

                            $esCorto = mb_strlen($valorTexto) <= 40;

                            $camposCompactos = [
                                'anio',
                                'fecha',
                                'paginas',
                                'tomo',
                                'volumen',
                                'idioma',
                                'isbn',
                                'clave',
                                'folio',
                                'numero',
                            ];

                            $camposLargos = ['descripcion', 'contenido', 'notas', 'resumen', 'observaciones'];

                            $compacto =
                                !in_array($columna, $camposLargos) &&
                                ($esCorto || in_array($columna, $camposCompactos));
                        @endphp

                        <div class="{{ $compacto ? 'lg:col-span-1' : 'lg:col-span-2' }}">

                            <div
                                class="h-full rounded-xl border border-zinc-800 bg-zinc-950 p-4 hover:border-zinc-700 transition">

                                <!-- Nombre -->
                                <div class="text-[11px] font-bold text-zinc-500 uppercase tracking-wider mb-2">
                                    {{ $labels[$columna] ?? ucwords(str_replace('_', ' ', $columna)) }}
                                </div>

                                <!-- Valor -->
                                <div class="text-sm text-zinc-200 break-words leading-relaxed">
                                    @if (is_null($valor) || $valor === '')
                                        <span class="text-zinc-500 italic text-xs">
                                            Sin información
                                        </span>
                                    @else
                                        {{ $valor }}
                                    @endif
                                </div>

                            </div>

                        </div>
                    @endforeach

                </div>

            </aside>

        </div>

    </section>
@endsection
@section('js')
    <script>
        document.addEventListener("DOMContentLoaded", () => {


            window.initVisor({

                paginas: @json($paginas),

                recursoId: {{ $recurso['id'] }}

            });

        });
    </script>
@endsection
