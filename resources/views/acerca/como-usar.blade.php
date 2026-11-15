@extends('layouts.plantilla')
@section('content')
    <div>
        <div class="w-full bg-[#86212b]  py-8 px-4">
            <div class="max-w-6xl mx-auto text-center">
                <h1 class="text-xl text-white text-base uppercase tracking-[0.2em] font-bold">
                    {{ $title }}
                </h1>
            </div>
        </div>

        <article class=" pb-8">
            <div class="w-full max-w-4xl mx-auto px-4 py-12">

                <div class="post-contenido">
                    <section
                        class="mx-auto my-10 max-w-5xl rounded-xl bg-white p-6 shadow-sm md:p-10 border dark:border-[#9b6959] dark:bg-zinc-800 dark:text-white">

                        {{--  
                         Introducción
                            <div class="mb-8">
                                <h2 class="mb-5 text-2xl font-bold text-[#713d32] md:text-3xl dark:text-white">
                                    Explora nuestro acervo histórico
                                </h2>

                            </div>
--}}
                        <!-- Información adicional -->
                        <div class="space-y-4 ">

                            <!-- Búsqueda avanzada -->
                            <article
                                class="border  dark:bg-zinc-800 flex gap-4 rounded-lg border-l-4 border-[#9b6959] bg-[#f8f6f4] p-5">
                                <div
                                    class="dark:text-white flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#713d32] text-sm font-bold text-white">
                                    01
                                </div>

                                <div>
                                    {{-- 
                                    <h3 class="dark:text-white mb-2 text-lg font-bold text-[#713d32]">
                                        Búsqueda avanzada
                                    </h3>
                                     --}}
                                    <p class="dark:text-white text-sm leading-7 text-gray-600 md:text-base">
                                        En nuestro <strong class="font-semibold text-[#713d32] uppercase">BUSCADOR
                                            GENERAL</strong>
                                        tienes la opción de filtrar por tipo documental. De manera
                                        predeterminada se incluyen todos los tipos documentales, pero también puedes
                                        seleccionar
                                        en específico los que sean de tu interés. Además, puedes seleccionar la opción de
                                        que sólo
                                        busque material que ya esté digitalizado y que sea visible online.

                                    </p>
                                </div>
                            </article>


                            <!-- Búsqueda avanzada -->
                            <article
                                class="border  dark:bg-zinc-800 flex gap-4 rounded-lg border-l-4 border-[#9b6959] bg-[#f8f6f4] p-5">
                                <div
                                    class="dark:text-white flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#713d32] text-sm font-bold text-white">
                                    02
                                </div>

                                <div>
                                    {{-- 
                                    <h3 class="dark:text-white mb-2 text-lg font-bold text-[#713d32]">
                                        Búsqueda avanzada
                                    </h3>
                                     --}}
                                    <p class="dark:text-white text-sm leading-7 text-gray-600 md:text-base">
                                        En nuestro <strong class="font-semibold text-[#713d32] uppercase ">buscador
                                            avanzado</strong>
                                        puedes hacer búsquedas más específicas. Tienes un
                                        filtro por tipo documental, fondo y metadato.
                                    </p>
                                </div>
                            </article>

                            <!-- Consulta digital -->
                            <article
                                class=" border  dark:bg-zinc-800 flex gap-4 rounded-lg border-l-4 border-[#9b6959] bg-[#f8f6f4] p-5">
                                <div
                                    class="dark:text-white flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#713d32] text-sm font-bold text-white">
                                    03
                                </div>

                                <div>
                                    {{--  
                                    <h3 class="dark:text-white mb-2 text-lg font-bold text-[#713d32]">
                                        Consulta digital
                                    </h3>
                                    --}}
                                    <p class="dark:text-white text-sm leading-7 text-gray-600 md:text-base">
                                        En nuestra sección de <strong class="font-semibold text-[#713d32] uppercase">ÁREAS Y
                                            FONDOS</strong> puedes explorar los registros por cada una de
                                        nuestras colecciones.
                                    </p>
                                </div>
                            </article>

                            <!-- Registro -->
                            <article
                                class=" border dark:bg-zinc-800 flex gap-4 rounded-lg border-l-4 border-[#9b6959] bg-[#f8f6f4] p-5">
                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#713d32] text-sm font-bold text-white">
                                    03
                                </div>

                                <div>
                                    {{-- 
                                    <h3 class="dark:text-white mb-2 text-lg font-bold text-[#713d32]">
                                        Regístrate en Arreola Histórica
                                    </h3>
                                     --}}
                                    <p class="dark:text-white text-sm leading-7 text-gray-600 md:text-base">
                                        Sólo un porcentaje de los registros disponibles en nuestro buscador ya pueden ser
                                        consultados de manera digital. Debes <strong
                                            class="font-semibold text-[#713d32] uppercase">INICIAR SESIÓN</strong> para
                                        acceder a nuestro visor digital.
                                    </p>
                                </div>
                            </article>

                            <!-- Registro -->
                            <article
                                class=" border dark:bg-zinc-800 flex gap-4 rounded-lg border-l-4 border-[#9b6959] bg-[#f8f6f4] p-5">
                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#713d32] text-sm font-bold text-white">
                                    05
                                </div>

                                <div>
                                    {{-- 
                                    <h3 class="dark:text-white mb-2 text-lg font-bold text-[#713d32]">
                                        Regístrate en Arreola Histórica
                                    </h3>
                                     --}}
                                    <p class="dark:text-white text-sm leading-7 text-gray-600 md:text-base">
                                        Para iniciar sesión debes registrarte como persona usuaria de Arreola Histórica
                                        llenando por
                                        única ocasión nuestro <strong
                                            class="font-semibold text-[#713d32] uppercase">formulario de
                                            registro.</strong>
                                    </p>
                                </div>
                            </article>

                            <!-- Registro -->
                            <article
                                class=" border dark:bg-zinc-800 flex gap-4 rounded-lg border-l-4 border-[#9b6959] bg-[#f8f6f4] p-5">
                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#713d32] text-sm font-bold text-white">
                                    06
                                </div>

                                <div>
                                    {{-- 
                                    <h3 class="dark:text-white mb-2 text-lg font-bold text-[#713d32]">
                                        Regístrate en Arreola Histórica
                                    </h3>
                                     --}}
                                    <p class="dark:text-white text-sm leading-7 text-gray-600 md:text-base">
                                        Se generará un PDF con la información que llenaste en el formulario de registro. Con
                                        el
                                        formulario impreso y una identificación oficial puedes <strong
                                            class="font-semibold text-[#713d32] uppercase"> SOLICITAR TU CREDENCIAL</strong>
                                        para
                                        consultar de manera presencial los fondos de la Biblioteca Histórica. Sólo tienes
                                        que acudir
                                        con estos documentos al módulo de información ubicado en la Planta Baja de la
                                        Biblioteca
                                        Contemporánea de la BPEJ (Periférico Norte Manuel Gómez Morín no. 1695, Col.
                                        Belenes,
                                        C.P. 45150, Zapopan, Jalisco, México), de lunes a viernes de 9 a 16 horas.
                                    </p>
                                </div>
                            </article>

                        </div>
                    </section>

                </div>

            </div>

            <div
                class="mx-auto w-full max-w-4xl mt-10 border-t border-zinc-200 dark:border-zinc-700 pt-6 flex flex-wrap items-center justify-center gap-3 dark:bg-neutral-600">
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
        </article>

    </div>
@endsection
