@extends('layouts.plantilla')

@section('content')
    <div class="min-h-screen">
        <!-- Encabezado -->
        <header class="w-full bg-[#86212b] px-4 py-10">
            <div class="mx-auto max-w-6xl text-center">
                <h1 class="text-xl font-bold uppercase tracking-[0.15em] text-white md:text-2xl">
                    {{ $title ?? 'Memoria UDG' }}
                </h1>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-5 py-12 md:px-8 md:py-16">
            <!-- Presentación -->
            <section class="mb-12">
                <div class="mb-8 max-w-3xl">
                    <span class="mb-4 block text-sm font-semibold uppercase tracking-widest text-[#86212b] dark:text-[#d98b91]">
                        Red de acervos históricos
                    </span>

                    <h2 class="text-3xl font-semibold leading-tight tracking-tight text-zinc-800 dark:text-zinc-100 md:text-4xl">
                        Memoria UDG
                    </h2>

                    <div class="mt-5 h-1 w-16 bg-guinda dark:bg-[#d98b91]"></div>
                </div>

                <p class="max-w-4xl text-justify text-base leading-8 text-zinc-600 dark:text-zinc-300">
                    Es la red de acervos históricos de la Universidad de Guadalajara. La Biblioteca Histórica de la
                    Biblioteca Pública del Estado de Jalisco, el Sistema Universitario de Bibliotecas, Casa Zuno – Archivo
                    Histórico de la Universidad de Guadalajara y el Laboratorio de Humanidades Digitales del Centro
                    Universitario de los Lagos UDG trabajamos en colaboración para compartir estrategias y procedimientos
                    de preservación, control intelectual, digitalización y acceso del patrimonio histórico universitario.
                </p>
            </section>

            <!-- Instituciones participantes -->
            <section>
                <div class="mb-6 border-b border-zinc-200 pb-3 dark:border-zinc-700">
                    <h3 class="text-xl font-semibold text-zinc-800 dark:text-zinc-100 md:text-2xl">
                        Acervos y recursos digitales
                    </h3>
                    <p class="mt-2 text-sm leading-7 text-zinc-500 dark:text-zinc-400">
                        Consulta los sitios de las instituciones y recursos que forman parte de esta red.
                    </p>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <a href="https://www.arreolahistorica.udg.mx"
                       target="_blank" rel="noopener noreferrer"
                       class="group flex h-full flex-col rounded-lg border border-zinc-200 bg-white p-6 transition duration-300 hover:-translate-y-1 hover:border-[#86212b] hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-[#d98b91]">
                        <span class="mb-4 flex h-11 w-11 items-center justify-center rounded-full bg-[#86212b]/10 text-[#86212b] dark:bg-[#d98b91]/15 dark:text-[#d98b91]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>
                            </svg>
                        </span>
                        <h4 class="text-lg font-semibold text-zinc-800 group-hover:text-[#86212b] dark:text-zinc-100 dark:group-hover:text-[#d98b91]">
                            Biblioteca Pública del Estado de Jalisco
                        </h4>
                        <span class="mt-auto pt-5 text-sm font-semibold text-[#86212b] dark:text-[#d98b91]">
                            Visitar sitio <span aria-hidden="true">↗</span>
                        </span>
                    </a>

                    <a href="https://archivohistorico.udg.mx/pinakes_v404"
                       target="_blank" rel="noopener noreferrer"
                       class="group flex h-full flex-col rounded-lg border border-zinc-200 bg-white p-6 transition duration-300 hover:-translate-y-1 hover:border-[#86212b] hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-[#d98b91]">
                        <span class="mb-4 flex h-11 w-11 items-center justify-center rounded-full bg-[#86212b]/10 text-[#86212b] dark:bg-[#d98b91]/15 dark:text-[#d98b91]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-8h6v8M8 9h.01M16 9h.01"/>
                            </svg>
                        </span>
                        <h4 class="text-lg font-semibold text-zinc-800 group-hover:text-[#86212b] dark:text-zinc-100 dark:group-hover:text-[#d98b91]">
                            Casa Zuno – Archivo Histórico de la Universidad de Guadalajara
                        </h4>
                        <span class="mt-auto pt-5 text-sm font-semibold text-[#86212b] dark:text-[#d98b91]">
                            Visitar sitio <span aria-hidden="true">↗</span>
                        </span>
                    </a>

                    <a href="https://www.lagos.udg.mx/investigacion/laboratorio-de-humanidades-digitales"
                       target="_blank" rel="noopener noreferrer"
                       class="group flex h-full flex-col rounded-lg border border-zinc-200 bg-white p-6 transition duration-300 hover:-translate-y-1 hover:border-[#86212b] hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-[#d98b91]">
                        <span class="mb-4 flex h-11 w-11 items-center justify-center rounded-full bg-[#86212b]/10 text-[#86212b] dark:bg-[#d98b91]/15 dark:text-[#d98b91]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M2 20h20M4 17V7l8-4 8 4v10M8 20v-6h8v6M8 9h.01M16 9h.01"/>
                            </svg>
                        </span>
                        <h4 class="text-lg font-semibold text-zinc-800 group-hover:text-[#86212b] dark:text-zinc-100 dark:group-hover:text-[#d98b91]">
                            Centro Universitario de los Lagos – Laboratorio de Humanidades Digitales
                        </h4>
                        <span class="mt-auto pt-5 text-sm font-semibold text-[#86212b] dark:text-[#d98b91]">
                            Visitar sitio <span aria-hidden="true">↗</span>
                        </span>
                    </a>

                    <a href="https://wdg.biblio.udg.mx"
                       target="_blank" rel="noopener noreferrer"
                       class="group flex h-full flex-col rounded-lg border border-zinc-200 bg-white p-6 transition duration-300 hover:-translate-y-1 hover:border-[#86212b] hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-[#d98b91]">
                        <span class="mb-4 flex h-11 w-11 items-center justify-center rounded-full bg-[#86212b]/10 text-[#86212b] dark:bg-[#d98b91]/15 dark:text-[#d98b91]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>
                                <path d="M8 7h8M8 11h8"/>
                            </svg>
                        </span>
                        <h4 class="text-lg font-semibold text-zinc-800 group-hover:text-[#86212b] dark:text-zinc-100 dark:group-hover:text-[#d98b91]">
                            Biblioteca Digital Universidad de Guadalajara
                        </h4>
                        <span class="mt-auto pt-5 text-sm font-semibold text-[#86212b] dark:text-[#d98b91]">
                            Visitar sitio <span aria-hidden="true">↗</span>
                        </span>
                    </a>
                </div>
            </section>
        </main>
    </div>
@endsection