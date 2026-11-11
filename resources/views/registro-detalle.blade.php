@extends('layouts.plantilla')

@section('content')
    @php
        $color = Auth::check() ? 'bg-zinc-950' : 'bg-white';
        // Puedes mover esto también al ViewModel si lo deseas, por ahora se queda:
        $esVideo = $esVideo ?? $recurso->tipo_media === 'video' || $recurso->tipo_media === 'audio';
    @endphp
    <section class="{{ $color }} min-h-screen">
        <div class="flex flex-col lg:flex-row h-screen">

            <!-- VISOR (ZONA IZQUIERDA) -->
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
                        {{ $coleccionNombre }}
                    </span>
                </div>

                <!-- MOBILE INFO (Acordeón) -->
                <aside class="lg:hidden border-b border-zinc-800 bg-zinc-900 p-3 text-zinc-300">
                    <details class="group rounded-lg border border-zinc-800 bg-zinc-950">
                        <summary
                            class="cursor-pointer list-none px-4 py-3 text-sm font-semibold text-white flex items-center justify-between">
                            Información del libro
                            <span class="transition duration-200 group-open:rotate-180">▼</span>
                        </summary>
                        <div class="divide-y divide-zinc-800 p-4">
                            <!-- Inyectamos el componente reutilizable de datos -->
                            @include('partials.registro-datos')

                            @unless ($esVideo)
                                <div class="mt-4 pt-4 border-t border-zinc-800">
                                    <span
                                        class="block text-zinc-500 uppercase text-[11px] font-semibold tracking-wider">Páginas</span>
                                    <div class="text-sm text-zinc-200">{{ count($paginas ?? []) }}</div>
                                </div>
                            @endunless
                        </div>
                    </details>
                </aside>

                <!-- CONTENEDOR DEL VISOR -->
                @if (Auth::check())
                    @if ($esVideo)
                        {{-- ============ VISOR DE VIDEO (HLS) ============ --}}
                        <div id="video-container"
                            class="relative flex-1 h-0 min-h-0 w-full max-w-5xl mx-auto flex flex-col bg-black">
                            <div class="relative flex-1 flex items-center justify-center h-full">
                                <video id="player" controls muted playsinline
                                    poster="/videos/{{ $recurso->id }}/thumb.webp"
                                    class="max-w-full max-h-full w-full h-full bg-black"></video>
                            </div>
                        </div>
                    @else
                        {{-- ============ VISOR DE IMÁGENES / PÁGINAS ============ --}}
                        <div id="visor-container"
                            class="relative flex-1 h-0 min-h-0 w-full max-w-5xl mx-auto flex flex-col bg-zinc-800">
                            <!-- Tu código original del visor Panzoom se queda exactamente igual aquí -->
                            <!-- (Viewer, botones, barra inferior...) -->
                            <div id="viewer"
                                class="relative flex-1 overflow-hidden flex items-center justify-center p-4 group">
                                <div id="panzoom-content" class="relative origin-center inline-block">
                                    <canvas id="page-canvas" class="block shadow-2xl bg-zinc-900"></canvas>
                                    <div id="ocr-layer" class="absolute top-0 left-0 w-full h-full pointer-events-none">
                                    </div>
                                </div>
                                <button onclick="document.getElementById('prev-page').click()"
                                    class="flex absolute left-4 top-1/2 -translate-y-1/2 bg-zinc-900/60 text-white p-3 rounded-full z-10"><x-heroicon-o-arrow-left
                                        class="w-6 h-6" /></button>
                                <button onclick="document.getElementById('next-page').click()"
                                    class="flex absolute right-4 top-1/2 -translate-y-1/2 bg-indigo-600/80 text-white p-3 rounded-full z-10"><x-heroicon-o-arrow-right
                                        class="w-6 h-6" /></button>
                            </div>

                            <div class="px-4 py-2 bg-zinc-900 border-t border-zinc-800 text-center">
                                <p id="page-indicator" class="text-xs text-zinc-400 font-medium">1 /
                                    {{ count($paginas ?? []) }}</p>
                            </div>

                            <!-- Resultados de la búsqueda en todo el documento -->
                            <div id="ocr-results"
                                class="hidden shrink-0 flex-col max-h-44 sm:max-h-52 bg-zinc-900 border-t border-zinc-700">
                                <div class="flex items-center justify-between px-4 py-2 border-b border-zinc-800">
                                    <span id="ocr-results-title" class="text-xs font-semibold text-zinc-200">Resultados en
                                        el documento</span>
                                    <button id="ocr-results-close" type="button"
                                        class="text-zinc-400 hover:text-white text-lg leading-none">×</button>
                                </div>
                                <ul id="ocr-results-list" class="flex-1 overflow-y-auto divide-y divide-zinc-800"></ul>
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
                                                data-url="{{ route('recursos.buscar-ocr', $recurso) }}"
                                                placeholder="Buscar en este documento..."
                                                class="flex-1 min-w-0 text-sm text-gray-700 placeholder-gray-400 outline-none bg-transparent" />
                                            <button id="ocr-search-btn"
                                                class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-medium rounded-lg transition shrink-0">Buscar</button>
                                        </div>
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
                    <!-- LOGIN REQUIRED -->
                    <div class="dark:bg-zinc-700 bg-white p-10 text-center h-full">
                        <x-heroicon-o-lock-closed class="w-12 h-12 mx-auto text-zinc-400 mb-4 dark:text-white" />
                        <h3 class="text-lg font-bold text-zinc-800 dark:text-white">Inicia sesión para visualizar el
                            contenido</h3>
                        @php session(['url.intended' => url()->current()]); @endphp
                        <a href="{{ route('login') }}"
                            class="inline-flex items-center gap-2 mt-5 px-5 py-2.5 rounded-xl bg-red-900 text-white text-sm font-semibold hover:bg-red-800 transition">
                            <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4" /> Iniciar sesión
                        </a>
                    </div>
                @endif
            </main>

            <!-- DESKTOP SIDEBAR (ZONA DERECHA) -->
            <aside class="hidden lg:flex lg:flex-col w-[420px] border-l border-zinc-800 bg-zinc-900 overflow-auto">
                <div class="px-6 py-5 border-b border-zinc-800">
                    <h2 class="text-lg font-bold text-white">{{ $tituloPrincipal }}</h2>
                    <p class="text-xs text-zinc-500 mt-1">Ficha del Registro</p>
                </div>

                <div class="p-4">
                    <!-- Inyectamos el componente reutilizable de datos -->
                    @include('partials.registro-datos')
                </div>
            </aside>
        </div>
        <div
            class="mx-auto w-full max-w-4xl mt-10 border-t border-zinc-200 dark:border-zinc-700 pt-6 flex flex-wrap items-center justify-center gap-3">
            <span class="text-xs font-bold text-zinc-500 uppercase tracking-wide">Compartir:</span>
            <a href="https://wa.me/?text={{ urlencode(url()->current()) }}" target="_blank" rel="noopener"
                class="text-xs bg-zinc-100 hover:bg-zinc-200 text-zinc-700 rounded-full py-1.5 px-3 transition-colors dark:bg-zinc-800 dark:text-white dark:hover:bg-zinc-700">
                WhatsApp
            </a>
            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank"
                rel="noopener"
                class="dark:border dark:border-zinc-800 text-xs bg-zinc-100 hover:bg-zinc-200 text-zinc-700 rounded-full py-1.5 px-3 transition-colors dark:bg-zinc-800 
                    dark:text-white dark:hover:bg-zinc-700">
                Facebook
            </a>

            <a href="https://x.com/intent/post?url={{ urlencode(url()->current()) }}&text={{ urlencode($titulo ?? '') }}"
                target="_blank" rel="noopener"
                class="dark:border dark:border-zinc-800 text-xs bg-zinc-100 hover:bg-zinc-200 text-zinc-700 rounded-full py-1.5 px-3 transition-colors dark:bg-zinc-800 dark:text-white dark:hover:bg-zinc-700">
                X
            </a>

            <button type="button" x-data="{ soportado: !!navigator.share }" x-show="soportado" x-cloak
                x-on:click="navigator.share({ title: document.title, url: '{{ url()->current() }}' })"
                class="text-xs bg-zinc-100 hover:bg-zinc-200 text-zinc-700 rounded-full py-1.5 px-3 transition-colors dark:bg-zinc-800 dark:text-white dark:hover:bg-zinc-700">
                Más opciones
            </button>
        </div>
    </section>
@endsection

@section('js')
    @if ($esVideo)
        <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                window.initVideoVisor({
                    src: "/videos/{{ $recurso->id }}/{{ $recurso->id }}.m3u8"
                });
            });
        </script>
    @else
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                const paginaSolicitada = {{ $paginaSolicitada ?? 1 }};
                window.initVisor({
                    paginas: @json($paginas),
                    recursoId: {{ $recurso->id }},
                    paginaInicial: paginaSolicitada,
                    startIndex: paginaSolicitada > 0 ? paginaSolicitada - 1 : 0
                });
            });
        </script>
    @endif
@endsection
