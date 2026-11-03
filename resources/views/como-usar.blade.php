@extends('layouts.plantilla')
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

                <div class="post-contenido">


                    <section class="mx-auto my-10 max-w-5xl rounded-xl bg-white p-6 shadow-sm md:p-10">

                        <!-- Introducción -->
                        <div class="mb-8">
                            <h2 class="mb-5 text-2xl font-bold text-[#713d32] md:text-3xl">
                                Explora nuestro acervo histórico
                            </h2>

                            <p class="mb-4 text-base leading-7 text-gray-600">
                                En nuestro <strong class="font-semibold text-[#713d32]">buscador general</strong>
                                tienes la opción de filtrar por tipo documental. De manera predeterminada
                                se incluyen todos los tipos documentales, pero puedes seleccionar aquellos
                                que sean de tu interés.
                            </p>

                            <p class="text-base leading-7 text-gray-600">
                                Además, puedes elegir que la búsqueda incluya únicamente material
                                digitalizado y disponible para consulta en línea.
                            </p>
                        </div>

                        <!-- Información adicional -->
                        <div class="space-y-4">

                            <!-- Búsqueda avanzada -->
                            <article class="flex gap-4 rounded-lg border-l-4 border-[#9b6959] bg-[#f8f6f4] p-5">
                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#713d32] text-sm font-bold text-white">
                                    01
                                </div>

                                <div>
                                    <h3 class="mb-2 text-lg font-bold text-[#713d32]">
                                        Búsqueda avanzada
                                    </h3>
                                    <p class="text-sm leading-7 text-gray-600 md:text-base">
                                        En nuestro buscador avanzado puedes realizar búsquedas más
                                        específicas mediante filtros por tipo documental e incluir
                                        criterios como área, fondo y metadato.
                                    </p>
                                </div>
                            </article>

                            <!-- Consulta digital -->
                            <article class="flex gap-4 rounded-lg border-l-4 border-[#9b6959] bg-[#f8f6f4] p-5">
                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#713d32] text-sm font-bold text-white">
                                    02
                                </div>

                                <div>
                                    <h3 class="mb-2 text-lg font-bold text-[#713d32]">
                                        Consulta digital
                                    </h3>
                                    <p class="text-sm leading-7 text-gray-600 md:text-base">
                                        Sólo un porcentaje de los registros disponibles en nuestro
                                        buscador puede consultarse digitalmente. Debes iniciar sesión
                                        para acceder a nuestro visor digital.
                                    </p>
                                </div>
                            </article>

                            <!-- Registro -->
                            <article class="flex gap-4 rounded-lg border-l-4 border-[#9b6959] bg-[#f8f6f4] p-5">
                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#713d32] text-sm font-bold text-white">
                                    03
                                </div>

                                <div>
                                    <h3 class="mb-2 text-lg font-bold text-[#713d32]">
                                        Regístrate en Arreola Histórica
                                    </h3>
                                    <p class="text-sm leading-7 text-gray-600 md:text-base">
                                        Para iniciar sesión debes registrarte como usuario de
                                        Arreola Histórica, llenando por única ocasión nuestro
                                        formulario de registro.
                                    </p>
                                </div>
                            </article>

                        </div>
                    </section>

                </div>

            </div>

            <div
                class="mx-auto w-full max-w-4xl mt-10 border-t border-zinc-200 dark:border-zinc-700 pt-6 flex items-center justify-center gap-3">
                <span class="text-xs font-bold text-zinc-500 uppercase tracking-wide">Compartir:</span>
                <a href="https://wa.me/?text={{ urlencode(url()->current()) }}" target="_blank" rel="noopener"
                    class="text-xs bg-zinc-100 hover:bg-zinc-200 text-zinc-700 rounded-full py-1.5 px-3 transition-colors dark:bg-zinc-800 dark:text-white dark:hover:bg-zinc-700">
                    WhatsApp
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank"
                    rel="noopener"
                    class="text-xs bg-zinc-100 hover:bg-zinc-200 text-zinc-700 rounded-full py-1.5 px-3 transition-colors dark:bg-zinc-800 dark:text-white dark:hover:bg-zinc-700">
                    Facebook
                </a>
            </div>
        </article>

    </div>
@endsection
