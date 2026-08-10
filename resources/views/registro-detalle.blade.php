@extends('layouts.plantilla')

@section('content')
    @php
        $color = Auth::check() ? 'bg-zinc-950' : 'bg-white';

        // Determina qué visor mostrar: imágenes/páginas o video HLS.
        // Idealmente esto llega ya calculado desde el controlador como
        // $esVideo, pero se deja un fallback por si no se define ahí.
        $esVideo =
            $esVideo ??
            (isset($recurso['tipo_media']) && $recurso['tipo_media'] === 'video') || $recurso['tipo_media'] === 'audio';
    @endphp
    <section class="{{ $color }} min-h-screen">

        <div class="flex flex-col lg:flex-row h-screen">
            <!-- VISOR -->
            <main class="flex-1 flex flex-col min-h-0 border-b border-zinc-200">
                <!-- TOPBAR -->
                <div
                    class="flex items-center flex-col md:flex-row justify-between px-4 py-3 border-b border-zinc-800 bg-zinc-900">
                    <a href="{{ url()->previous() }}"
                        class="inline-flex items-center gap-2 px-3 py-2 text-xs font-medium text-zinc-300 bg-zinc-800 rounded-lg hover:bg-zinc-700 transition">
                        <x-heroicon-o-arrow-left class="w-4 h-4" />
                        Volver
                    </a>
                    <span class="bg-red-900 text-white text-xs uppercase font-bold px-3 py-2 mt-2 md:mt-0 rounded-md">
                        {{ $coleccionNombre->nombre }}
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
                            @isset($registro)
                                @php
                                    $datos =
                                        $registro instanceof \Illuminate\Database\Eloquent\Model
                                            ? $registro->getAttributes()
                                            : (array) $registro;

                                    $datos = array_filter(
                                        $datos,
                                        fn($v, $k) => !in_array($k, $omitir),
                                        ARRAY_FILTER_USE_BOTH,
                                    );
                                @endphp
                                @foreach ($datos as $columna => $valor)
                                    @php
                                        $valorTexto = is_scalar($valor)
                                            ? trim(strip_tags((string) $valor))
                                            : json_encode($valor, JSON_UNESCAPED_UNICODE);

                                        $esCorto = mb_strlen($valorTexto) <= 40;

                                        $esRelacion = str_ends_with($columna, '_id');

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

                                        $esMetadata = $columna === 'metadata';

                                        $valorFinal = $valor;

                                        $compacto =
                                            !in_array($columna, $camposLargos) &&
                                            ($esCorto || in_array($columna, $camposCompactos));

                                        $label = $labels[$columna] ?? ucwords(str_replace(['_', '-'], ' ', $columna));

                                        $valorFinal = $valor;

                                        if ($esRelacion && !is_null($valor)) {
                                            $relacion = str_replace('_id', '', $columna);

                                            $modeloRelacionado = $registro->$relacion ?? null;

                                            if ($modeloRelacionado) {
                                                $valorFinal = $modeloRelacionado->nombre ?? $modeloRelacionado->id;
                                            }
                                        }
                                        $label =
                                            $labels[$columna] ??
                                            ucwords(str_replace('_', ' ', str_replace('_id', '', $columna)));

                                        if ($esMetadata) {
                                            $valorFinal = is_string($valor)
                                                ? json_decode($valor, true)
                                                : (array) $valor;
                                        }
                                    @endphp

                                    <div
                                        class="{{ $esMetadata ? 'lg:col-span-2' : ($compacto ? 'lg:col-span-1' : 'lg:col-span-2') }}">

                                        <div
                                            class="h-full rounded-xl border border-zinc-800 bg-zinc-950 p-4 hover:border-zinc-700 transition">

                                            <div class="text-[11px] font-bold text-zinc-500 uppercase tracking-wider mb-2">
                                                {{ $label }}
                                            </div>

                                            <div class="text-sm text-zinc-200 break-words leading-relaxed">
                                                @if (empty($valor))
                                                    <span class="text-zinc-500 italic text-xs">Sin información</span>
                                                @else
                                                    @if ($esMetadata && is_array($valorFinal))
                                                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-2">
                                                            @foreach ($valorFinal as $metaKey => $metaValue)
                                                                @if ($metaValue != null)
                                                                    @php

                                                                        $label_m =
                                                                            $labels[$metaKey] ??
                                                                            ucwords(
                                                                                str_replace(['_', '-'], ' ', $metaKey),
                                                                            );

                                                                        $esCorto = mb_strlen($metaValue) <= 40;

                                                                        $compacto =
                                                                            !in_array($metaKey, $camposLargos) &&
                                                                            ($esCorto ||
                                                                                in_array($metaKey, $camposCompactos));
                                                                    @endphp
                                                                    <div
                                                                        class="{{ $compacto ? 'lg:col-span-1' : 'lg:col-span-2' }} border border-zinc-800 bg-zinc-900 p-2 rounded">

                                                                        <div class="text-[10px] text-zinc-500 uppercase">
                                                                            {{ $label_m }}
                                                                        </div>

                                                                        <div class="text-sm text-zinc-200">
                                                                            {{ is_scalar($metaValue) ? $metaValue : json_encode($metaValue) }}
                                                                        </div>

                                                                    </div>
                                                                @endif
                                                            @endforeach

                                                        </div>
                                                    @else
                                                        {{ is_scalar($valorFinal) ? ucwords($valorFinal) : json_encode($valorFinal, JSON_UNESCAPED_UNICODE) }}
                                                    @endif
                                                @endif
                                            </div>

                                        </div>

                                    </div>
                                @endforeach
                            @endisset
                            <!-- PÁGINAS -->
                            @unless ($esVideo)
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
                            @endunless

                    </details>

                </aside>

                @if (Auth::check())
                    @if ($esVideo)
                        {{-- ============ VISOR DE VIDEO (HLS) ============ --}}
                        <div id="video-container"
                            class="relative flex-1 h-0 min-h-0 w-full max-w-5xl mx-auto flex flex-col bg-black">

                            <div class="relative flex-1 flex items-center justify-center h-full">
                                <video id="player" controls muted playsinline
                                    class="max-w-full max-h-full w-full h-full bg-black"></video>
                            </div>

                        </div>
                    @else
                        {{-- ============ VISOR DE IMÁGENES / PÁGINAS ============ --}}
                        <div id="visor-container"
                            class="relative flex-1 h-0 min-h-0 w-full max-w-5xl mx-auto flex flex-col bg-zinc-800">

                            <!-- 1. El visor principal con overflow oculto para el zoom -->
                            <div id="viewer"
                                class="relative flex-1 overflow-hidden flex items-center justify-center p-4 group">

                                <!-- 2. El contenedor que Panzoom moverá (Canvas + OCR juntos) -->
                                <div id="panzoom-content" class="relative origin-center inline-block">
                                    <canvas id="page-canvas" class="block w-full h-full shadow-2xl bg-zinc-900"></canvas>
                                    <div id="ocr-layer" class="absolute top-0 left-0 w-full h-full pointer-events-none">
                                    </div>
                                </div>

                                <!-- 3. Botones Prev/Next Flotantes -->
                                <button onclick="document.getElementById('prev-page').click()"
                                    class="flex absolute left-4 top-1/2 -translate-y-1/2 bg-zinc-900/60 hover:bg-zinc-900/90 text-white p-3 rounded-full shadow-lg transition border border-zinc-700 backdrop-blur-sm z-10">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15.75 19.5L8.25 12l7.5-7.5" />
                                    </svg>
                                </button>
                                <button onclick="document.getElementById('next-page').click()"
                                    class="flex absolute right-4 top-1/2 -translate-y-1/2 bg-indigo-600/80 hover:bg-indigo-600 text-white p-3 rounded-full shadow-lg transition z-10">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                    </svg>
                                </button>
                            </div>

                            <!-- 4. Indicador de Páginas (Esto es lo que perdiste) -->
                            <div class="px-4 py-2 bg-zinc-900 border-t border-zinc-800 text-center">
                                <p id="page-indicator" class="text-xs text-zinc-400 font-medium">
                                    @isset($paginas)
                                        1 / {{ count($paginas) }}
                                    @endisset
                                </p>
                            </div>

                            <!-- 5. Barra inferior con controles de Zoom y botones ocultos -->
                            <div
                                class="flex flex-col sm:flex-row items-center justify-between gap-4 p-4 border-t border-zinc-800 bg-zinc-900 w-full">
                                <div class="lg:block">
                                    <!-- Estos botones ocultos son disparados por el JS -->
                                    <button id="prev-page" class="hidden"></button>
                                    <button id="next-page" class="hidden"></button>
                                </div>

                                <!-- Búsqueda de texto OCR: solo resalta en la página que se está viendo -->
                                <div class="flex flex-col items-center gap-1 w-full sm:w-auto">
                                    <div class="flex items-center gap-2 w-full sm:w-auto">
                                        <div
                                            class="flex items-center gap-2 bg-white p-1 rounded-xl border border-gray-200 shadow-sm w-full sm:w-64">
                                            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-gray-400 ml-2 shrink-0" />
                                            <input id="ocr-search-input" type="text"
                                                placeholder="Buscar en esta página..."
                                                class="flex-1 min-w-0 text-sm text-gray-700 placeholder-gray-400 outline-none bg-transparent" />
                                            <button id="ocr-search-btn"
                                                class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-medium rounded-lg transition shrink-0">
                                                Buscar
                                            </button>
                                        </div>
                                        <button id="ocr-copy-page-btn" type="button"
                                            title="Copiar todo el texto de esta página"
                                            class="flex items-center gap-1.5 px-3 py-2 bg-white hover:bg-gray-50 text-gray-700 text-xs font-medium rounded-xl border border-gray-200 shadow-sm shrink-0 transition">
                                            <x-heroicon-o-clipboard-document class="w-4 h-4" />
                                            <span class="hidden sm:inline">Copiar página</span>
                                        </button>
                                    </div>
                                    <span id="ocr-search-count" class="text-[11px] text-zinc-400"></span>
                                </div>

                                <div
                                    class="flex items-center justify-center gap-4 bg-white p-1 rounded-xl border border-gray-200 shadow-sm w-full lg:w-auto mx-auto">
                                    <button id="btn-zoom-out"
                                        class="p-1 rounded-lg hover:bg-gray-100 text-gray-600 transition font-bold text-lg w-8 h-8 flex items-center justify-center border border-gray-200">−</button>

                                    <span id="zoom-percent"
                                        class="text-sm font-semibold text-gray-700 min-w-[50px] text-center">100%</span>

                                    <button id="btn-zoom-in"
                                        class="p-1 rounded-lg hover:bg-gray-100 text-gray-600 transition font-bold text-lg w-8 h-8 flex items-center justify-center border border-gray-200">+</button>
                                    <div class="h-6 w-px bg-gray-200 mx-1"></div>
                                    <button id="btn-reset-zoom"
                                        class="px-3 py-1 bg-gray-50 hover:bg-gray-100 text-gray-700 font-medium rounded-lg transition text-xs border border-gray-200 h-8 flex items-center">Reiniciar</button>
                                </div>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="  bg-white p-10 text-center  h-full">
                        <x-heroicon-o-lock-closed class="w-12 h-12 mx-auto text-zinc-400 mb-4" />

                        <h3 class="text-lg font-bold text-zinc-800">
                            Inicia sesión para visualizar el contenido
                        </h3>

                        <p class="text-sm text-zinc-500 mt-2">
                            Debes autenticarte para acceder al visor digital.
                        </p>
                        @section('js')
                            @php session(['url.intended' => url()->current()]); @endphp
                        @endsection

                        <a href="{{ route('login') }}"
                            class="inline-flex items-center gap-2 mt-5 px-5 py-2.5 rounded-xl bg-red-900 text-white text-sm font-semibold hover:bg-red-800 transition">
                            <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4" />
                            Iniciar sesión
                        </a>
                    </div>
                @endif
            </main>

            <!-- DESKTOP SIDEBAR -->
            <aside class="hidden lg:flex lg:flex-col w-[420px] border-l border-zinc-800 bg-zinc-900 overflow-auto">

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

                    @php
                        $datos =
                            $registro instanceof \Illuminate\Database\Eloquent\Model
                                ? $registro->getAttributes()
                                : (array) $registro;

                        $datos = array_filter($datos, fn($v, $k) => !in_array($k, $omitir), ARRAY_FILTER_USE_BOTH);
                    @endphp

                    @foreach ($datos as $columna => $valor)
                        @php
                            $valorTexto = is_scalar($valor)
                                ? trim(strip_tags((string) $valor))
                                : json_encode($valor, JSON_UNESCAPED_UNICODE);

                            $esCorto = mb_strlen($valorTexto) <= 40;

                            $esRelacion = str_ends_with($columna, '_id');

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

                            $esMetadata = $columna === 'metadata';

                            $valorFinal = $valor;

                            $compacto =
                                !in_array($columna, $camposLargos) &&
                                ($esCorto || in_array($columna, $camposCompactos));

                            $label = $labels[$columna] ?? ucwords(str_replace(['_', '-'], ' ', $columna));

                            $valorFinal = $valor;

                            if ($esRelacion && !is_null($valor)) {
                                $relacion = str_replace('_id', '', $columna);

                                $modeloRelacionado = $registro->$relacion ?? null;

                                if ($modeloRelacionado) {
                                    $valorFinal = $modeloRelacionado->nombre ?? $modeloRelacionado->id;
                                }
                            }
                            $label =
                                $labels[$columna] ?? ucwords(str_replace('_', ' ', str_replace('_id', '', $columna)));

                            if ($esMetadata) {
                                $valorFinal = is_string($valor) ? json_decode($valor, true) : (array) $valor;
                            }
                        @endphp

                        <div class="{{ $esMetadata ? 'lg:col-span-2' : ($compacto ? 'lg:col-span-1' : 'lg:col-span-2') }}">

                            <div
                                class="h-full rounded-xl border border-zinc-800 bg-zinc-950 p-4 hover:border-zinc-700 transition">

                                <div class="text-[11px] font-bold text-zinc-500 uppercase tracking-wider mb-2">
                                    {{ $label }}
                                </div>

                                <div class="text-sm text-zinc-200 break-words leading-relaxed">
                                    @if (empty($valor))
                                        <span class="text-zinc-500 italic text-xs">Sin información</span>
                                    @else
                                        @if ($esMetadata && is_array($valorFinal))
                                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-2">
                                                @foreach ($valorFinal as $metaKey => $metaValue)
                                                    @if ($metaValue != null)
                                                        @php

                                                            $label_m =
                                                                $labels[$metaKey] ??
                                                                ucwords(str_replace(['_', '-'], ' ', $metaKey));

                                                            $esCorto = mb_strlen($metaValue) <= 40;

                                                            $compacto =
                                                                !in_array($metaKey, $camposLargos) &&
                                                                ($esCorto || in_array($metaKey, $camposCompactos));
                                                        @endphp
                                                        <div
                                                            class="{{ $compacto ? 'lg:col-span-1' : 'lg:col-span-2' }} border border-zinc-800 bg-zinc-900 p-2 rounded">

                                                            <div class="text-[10px] text-zinc-500 uppercase">
                                                                {{ $label_m }}
                                                            </div>

                                                            <div class="text-sm text-zinc-200">
                                                                {{ is_scalar($metaValue) ? $metaValue : json_encode($metaValue) }}
                                                            </div>

                                                        </div>
                                                    @endif
                                                @endforeach

                                            </div>
                                        @else
                                            {{ is_scalar($valorFinal) ? ucwords($valorFinal) : json_encode($valorFinal, JSON_UNESCAPED_UNICODE) }}
                                        @endif
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
    @if ($esVideo)
        <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                window.initVideoVisor({
                    src: "/videos/{{ $recurso['id'] }}/{{ $recurso['id'] }}.m3u8"
                });
            });
        </script>
    @else
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                window.initVisor({
                    paginas: @json($paginas),
                    recursoId: {{ $recurso['id'] }}
                });
            });
        </script>
    @endif
@endsection