@extends('layouts.plantilla')

@section('css')
    <style>
        .text-custom-wine {
            color: #86212b;
        }

        .bg-custom-wine {
            background-color: #86212b;
        }

        .hover-text-custom-wine:hover {
            color: #86212b;
        }

        .post-contenido {
            margin-left: 25px;
        }

        .post-contenido>p {
            text-align: justify;
            margin-bottom: 10px;
        }

        /* Letra capitular (quítalo si no te gusta) */
        .post-contenido>p:first-child::first-letter {
            float: left;
            margin-right: 0.5rem;
            font-size: 3.75rem;
            line-height: 1;
            font-weight: 700;
            color: #86212b;
        }
    </style>
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

                <div class="post-contenido">
                    <p>
                        Arreola Histórica es un trabajo en proceso y que apenas inicia. En esta primera etapa, ponemos a tu
                        disposición un buscador que permite la exploración de una buena parte de los fondos históricos que
                        se resguardan en la Biblioteca Pública del Estado de Jalisco de la Universidad de Guadalajara. Sin
                        embargo, en este momento, sólo un porcentaje de estos registros ya puede ser consultado en línea.
                        Por supuesto, todos los días trabajamos para seguir alimentando nuestro repositorio.
                    </p>

                    <p>
                        Arreola Histórica permite el acceso a 500 años de libros, publicaciones seriadas, fotografías,
                        películas, audios, manuscritos, mapas y piezas de arte. Además de ser un portal de consulta virtual,
                        Arreola Histórica se presenta como centro de producción de conocimiento sobre el patrimonio y el
                        pasado que resguarda la Universidad de Guadalajara, así como una herramienta para el acceso, la
                        conservación y el rescate de la memoria de la sociedad jalisciense.
                    </p>

                    <p>
                        A través de Arreola Histórica, la Biblioteca Pública del Estado de Jalisco ofrece un servicio
                        abierto al mundo las 24 horas, los 7 días de la semana.
                    </p>


                </div>

            </div>
        </article>

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
    </div>
@endsection