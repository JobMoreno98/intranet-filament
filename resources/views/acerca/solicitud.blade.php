@extends('layouts.plantilla')

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox@3.3.1/dist/css/glightbox.min.css">
@endsection


@section('content')
    <div>
        <div class="w-full bg-[#86212b] py-8 px-4">
            <div class="max-w-6xl mx-auto text-center">
                <h1 class="text-white text-base uppercase tracking-[0.2em] font-bold">
                    {{ $title }}
                </h1>
            </div>
        </div>

        <article>
            <div class="w-full max-w-4xl mx-auto px-4 py-12">

                <section
                    class="mx-auto my-10 max-w-5xl rounded-xl bg-white p-6 shadow-sm md:p-10 dark:border dark:border-zinc-200 dark:bg-zinc-800 dark:text-white">

                    <!-- Encabezado -->
                    <div class="mb-6">
                        <span
                            class="mb-3 inline-block rounded-full bg-[#f8f1ed] px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-[#713d32]">
                            Servicios de digitalización
                        </span>

                        <h2 class="mb-4 text-2xl font-bold leading-tight text-[#713d32] md:text-3xl">
                            Laboratorio de Digitalización
                        </h2>

                        <p class="text-base leading-7 text-gray-600  dark:text-white">
                            El Laboratorio de Digitalización de la Biblioteca Pública del Estado
                            de Jalisco es uno de los mejor equipados del país. Trabajamos en la
                            reproducción de diversos materiales documentales, artísticos y
                            audiovisuales.
                        </p>
                    </div>

                    <!-- Materiales -->
                    <div class="mb-8">
                        <h3 class="mb-4 text-lg font-bold text-gray-800  dark:text-white">
                            Materiales que digitalizamos
                        </h3>

                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">

                            <div class="rounded-lg bg-[#f8f6f4] p-4 text-center text-sm font-medium text-gray-700">
                                Archivos
                            </div>

                            <div class="rounded-lg bg-[#f8f6f4] p-4 text-center text-sm font-medium text-gray-700">
                                Audiovisuales
                            </div>

                            <div class="rounded-lg bg-[#f8f6f4] p-4 text-center text-sm font-medium text-gray-700">
                                Fotografías
                            </div>

                            <div class="rounded-lg bg-[#f8f6f4] p-4 text-center text-sm font-medium text-gray-700">
                                Arte y gráfica
                            </div>

                            <div class="rounded-lg bg-[#f8f6f4] p-4 text-center text-sm font-medium text-gray-700">
                                Libros
                            </div>

                            <div class="rounded-lg bg-[#f8f6f4] p-4 text-center text-sm font-medium text-gray-700">
                                Periódicos
                            </div>

                            <div class="rounded-lg bg-[#f8f6f4] p-4 text-center text-sm font-medium text-gray-700">
                                Revistas
                            </div>

                            <div class="rounded-lg bg-[#f8f6f4] p-4 text-center text-sm font-medium text-gray-700">
                                Audios y mapas
                            </div>

                        </div>
                    </div>

                    <div class="rounded-lg border border-[#e9ded8] bg-[#faf7f5] p-5 md:p-6">
                        <h3 class="mb-2 text-lg font-bold text-[#713d32]">
                            Solicita una digitalización
                        </h3>

                        <p class="mb-4 text-sm leading-7 text-gray-600 md:text-base">
                            Es posible solicitar digitalizaciones en alta calidad.
                            Te recomendamos consultar las condiciones de uso del material
                            y las tarifas correspondientes escribiendo a nuestro correo:
                        </p>

                        <a href="mailto:arreola.historica@udg.mx"
                            class="inline-flex flex-wrap items-center gap-2 font-semibold text-[#713d32] transition-colors hover:text-[#9b6959] hover:underline">
                            arreola.historica@udg.mx
                            <span aria-hidden="true">↗</span>
                        </a>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 w-full max-w-4xl mx-auto mt-4">
                        <!-- Imagen 1 -->
                        <a href="{{ asset('img/laboratorio/1.jpg') }}"
                            class="glightbox aspect-[4/3] w-full rounded-2xl overflow-hidden relative group block border border-zinc-200 dark:border-zinc-700 bg-zinc-100 dark:bg-zinc-800 shadow-sm hover:shadow-md transition-all duration-300"
                            data-gallery="galeria-laboratorio" data-title="Laboratorio 1">

                            <img src="{{ asset('img/laboratorio/1.jpg') }}" alt="Laboratorio 1" loading="lazy"
                                class="w-full h-full object-cover object-top transition-transform duration-500 group-hover:scale-105">

                            <!-- Degradado oscuro en hover -->
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            </div>

                            <!-- Botón lupa central + etiqueta inferior -->
                            <div
                                class="absolute inset-0 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <div
                                    class="w-11 h-11 rounded-full bg-white/95 dark:bg-zinc-900/95 text-[#86212b] dark:text-amber-400 flex items-center justify-center shadow-md transform scale-90 group-hover:scale-100 transition-transform duration-300">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 8v5m-2.5-2.5h5" />
                                    </svg>
                                </div>
                            </div>

                            <span
                                class="absolute bottom-3 left-4 text-sm font-medium text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                Laboratorio 1
                            </span>
                        </a>

                        <!-- Imagen 2 -->
                        <a href="{{ asset('img/laboratorio/2.jpg') }}"
                            class="glightbox aspect-[4/3] w-full rounded-2xl overflow-hidden relative group block border border-zinc-200 dark:border-zinc-700 bg-zinc-100 dark:bg-zinc-800 shadow-sm hover:shadow-md transition-all duration-300"
                            data-gallery="galeria-laboratorio" data-title="Laboratorio 2">

                            <img src="{{ asset('img/laboratorio/2.jpg') }}" alt="Laboratorio 2" loading="lazy"
                                class="w-full h-full object-cover object-top transition-transform duration-500 group-hover:scale-105">

                            <!-- Degradado oscuro en hover -->
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            </div>

                            <!-- Botón lupa central + etiqueta inferior -->
                            <div
                                class="absolute inset-0 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <div
                                    class="w-11 h-11 rounded-full bg-white/95 dark:bg-zinc-900/95 text-[#86212b] dark:text-amber-400 flex items-center justify-center shadow-md transform scale-90 group-hover:scale-100 transition-transform duration-300">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 8v5m-2.5-2.5h5" />
                                    </svg>
                                </div>
                            </div>

                            <span
                                class="absolute bottom-3 left-4 text-sm font-medium text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                Laboratorio 2
                            </span>
                        </a>
                    </div>
                </section>


            </div>

        </article>
        <div
            class="mx-auto w-full max-w-6xl mt-10 border-t border-zinc-200 dark:border-zinc-700 pt-6 flex flex-wrap items-center justify-center gap-3  ">
            <span class="text-xs font-bold text-zinc-500 dark:text-white uppercase tracking-wide">Compartir:</span>
            <a href="https://wa.me/?text={{ urlencode(url()->current()) }}" target="_blank" rel="noopener"
                class="text-xs bg-zinc-100 hover:bg-zinc-200 text-zinc-700 rounded-full py-1.5 px-3 transition-colors dark:bg-neutral-600 dark:text-white dark:hover:bg-zinc-700">
                WhatsApp
            </a>
            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank"
                rel="noopener"
                class="dark:border dark:border-zinc-800 text-xs bg-zinc-100 hover:bg-zinc-200 text-zinc-700 rounded-full py-1.5 px-3 transition-colors dark:bg-neutral-600 
                    dark:text-white dark:hover:bg-zinc-700">
                Facebook
            </a>

            <a href="https://x.com/intent/post?url={{ urlencode(url()->current()) }}&text={{ urlencode($titulo ?? '') }}"
                target="_blank" rel="noopener"
                class="dark:border dark:border-zinc-800 text-xs bg-zinc-100 hover:bg-zinc-200 text-zinc-700 rounded-full py-1.5 px-3 transition-colors dark:bg-neutral-600 dark:text-white dark:hover:bg-zinc-700">
                X
            </a>

            <button type="button" x-data="{ soportado: !!navigator.share }" x-show="soportado" x-cloak
                x-on:click="navigator.share({ title: document.title, url: '{{ url()->current() }}' })"
                class="text-xs bg-zinc-100 hover:bg-zinc-200 text-zinc-700 rounded-full py-1.5 px-3 transition-colors dark:bg-neutral-600 dark:text-white dark:hover:bg-zinc-700">
                Más opciones
            </button>
        </div>

    </div>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/glightbox@3.3.1/dist/js/glightbox.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            GLightbox({
                selector: '.glightbox',
                touchNavigation: true,
                loop: true,
                zoomable: true,
                draggable: true,
                descPosition: 'bottom',
            });
        });
    </script>
@endsection
