@extends('layouts.plantilla')

@section('content')
    <div class="min-h-screen">

        <!-- Encabezado -->
        <header class="w-full bg-[#86212b] px-4 py-8">
            <div class="mx-auto max-w-6xl text-center">
                <h1 class="text-xl font-bold uppercase tracking-[0.15em] text-white md:text-2xl">
                    {{ $title }}
                </h1>
            </div>
        </header>


        <!-- Contenedor principal -->
        <main class="mx-auto max-w-5xl px-5 py-12 md:px-8 md:py-16">

            <!-- Introducción -->
            <section class="mb-14">

                <div class="mb-8 max-w-3xl">
                    <span
                        class="mb-4 block text-sm font-semibold uppercase tracking-widest text-[#86212b] dark:text-[#d98b91]">
                        Arreola Histórica
                    </span>

                    <h2
                        class="text-3xl font-semibold leading-tight tracking-tight text-zinc-800 dark:text-zinc-100 md:text-4xl">
                        La memoria histórica de Jalisco al alcance de todos.
                    </h2>

                    <div class="mt-5 h-1 w-16 bg-guinda dark:bg-[#d98b91]"></div>
                </div>

                <div class="space-y-5 text-justify text-base leading-8 text-zinc-600 dark:text-zinc-300">
                    <p
                        class="first-letter:float-left first-letter:mr-3 first-letter:text-6xl first-letter:font-bold first-letter:leading-[0.85] first-letter:text-[#86212b] dark:first-letter:text-[#d98b91]">
                        Arreola Histórica es el buscador y repositorio digital de los fondos históricos de la Biblioteca
                        Pública del Estado de Jalisco de la Universidad de Guadalajara. Nuestro portal permite el acceso a
                        500 años de libros, periódicos, revistas, fotografías, películas, audios, manuscritos, mapas y
                        piezas de arte. Además de ser un portal de consulta virtual, Arreola Histórica se presenta como
                        centro de producción de conocimiento sobre el patrimonio que resguarda la Universidad de
                        Guadalajara, así como una herramienta para la consulta, la conservación y el rescate de la memoria
                        de la sociedad jalisciense.

                    </p>

                    <p>
                        Arreola Histórica es un repositorio en construcción. En esta primera etapa, ponemos a tu disposición
                        un buscador que permite la exploración de una buena parte de los materiales antiguos que se
                        resguardan en nuestra Biblioteca. Sólo un porcentaje de estos registros ya puede ser consultado en
                        línea. Todos los días trabajamos para seguir alimentando nuestro repositorio.

                    </p>
                </div>

            </section>

            <!-- Patrimonio documental -->
            <section class="mb-14 border-y border-zinc-200 py-10 dark:border-zinc-700">

                <div class="mb-7 text-center">
                    <span class="text-xs font-semibold uppercase tracking-[0.2em] text-[#86212b] dark:text-[#d98b91]">
                        Nuestro patrimonio
                    </span>

                    <h2 class="mt-2 text-2xl font-semibold text-zinc-800 dark:text-zinc-100 md:text-3xl">
                        500 años de historia documental
                    </h2>

                    <p class="mx-auto mt-3 max-w-2xl text-sm leading-7 text-zinc-500 dark:text-zinc-400">
                        Un acervo que reúne diferentes expresiones de la memoria
                        histórica y cultural.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3 md:grid-cols-4">

                    @foreach (['Libros', 'Publicaciones seriadas', 'Fotografías', 'Películas', 'Audios', 'Manuscritos', 'Mapas', 'Piezas de arte'] as $material)
                        <div
                            class="group flex min-h-24 items-center justify-center rounded-lg border border-zinc-200 bg-zinc-50 p-4 text-center transition-all duration-300 hover:border-[#86212b] hover:bg-[#86212b] dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-[#86212b] dark:hover:bg-[#86212b]">
                            <span
                                class="text-sm font-medium text-zinc-700 transition-colors group-hover:text-white dark:text-zinc-200">
                                {{ $material }}
                            </span>
                        </div>
                    @endforeach

                </div>

            </section>

            <!-- Cierre -->
            <section class="rounded-lg bg-guinda px-6 py-10 text-center md:px-12">

                <span class="mb-3 block text-xs font-semibold uppercase tracking-[0.2em] text-white/70">
                    Biblioteca Pública del Estado de Jalisco
                </span>

                <h2 class="mb-4 text-2xl font-semibold leading-snug text-white md:text-3xl">
                    Un servicio abierto al mundo.
                </h2>

                <p class="mx-auto max-w-2xl text-base leading-7 text-white/90">
                    A través de Arreola Histórica, la Biblioteca Pública del
                    Estado de Jalisco ofrece un servicio abierto al mundo
                    las 24 horas, los 7 días de la semana.
                </p>

            </section>

        </main>

    </div>
@endsection
