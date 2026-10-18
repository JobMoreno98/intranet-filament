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

        /* Primer párrafo: más grande, como introducción */
        .post-contenido>p:first-child {
            @apply text-xl leading-9 text-zinc-800 dark:text-zinc-100;
        }

        /* Citas textuales */
        .post-contenido blockquote {
            @apply border-l-4 border-[#86212b] pl-5 italic text-zinc-600 dark:border-red-400 dark:text-zinc-300;
        }

        .post-contenido blockquote footer {
            @apply mt-3 text-sm not-italic text-zinc-500 dark:text-zinc-400;
        }
    </style>
@endsection

@section('content')
    <div>
        <div class="w-full bg-[#86212b] py-8 px-4">
            <div class="max-w-6xl mx-auto text-center">
                <h1 class="text-white text-base uppercase tracking-[0.2em] font-bold">
                    NUESTROS FONDOS HISTÓRICOS
                </h1>
            </div>
        </div>
        <div class="w-full max-w-5xl mx-auto">
            <div class="post-contenido mt-4">
                <p>La Biblioteca Histórica cuenta con el personal especializado y las condiciones de temperatura y
                    humedad relativa adecuadas para la preservación del patrimonio histórico de Jalisco y la región
                    Centro-Occidente de México.</p>
                <p>Nuestros acervos se encuentran organizados en siete áreas, que a su vez resguardan fondos
                    archivísticos, audiovisuales, bibliográficos, hemerográficos, fotográficos y sonoros: </p>

            </div>
        </div>

        <article>
            <div class="w-full max-w-4xl mx-auto px-4 pt-6">

                <h2 class="text-3xl border-guinda  p-2 border-l-4">1. Archivos Históricos y Mapoteca</h2>
                <div class="post-contenido ">
                    <p>
                        <span class="underline">
                            Archivo de la Real Audiencia de Guadalajara:
                        </span>
                        Durante el periodo colonial una extensa
                        región del territorio novohispano conformó la jurisdicción del tribunal judicial de la Audiencia de
                        Guadalajara. Este archivo cubre desde el siglo XVI hasta la primera década del siglo XIX.
                        Obtuvo en 2010 el reconocimiento de Memoria del Mundo de México por la UNESCO.
                    </p>

                    <p>
                        <span class="underline">
                            Archivo del Supremo Tribunal de Justicia de Jalisco:
                        </span>
                        la memoria judicial del
                        estado desde 1824 hasta la primera mitad del siglo XX.
                    </p>
                    <p>
                        <span class="underline">
                            Otros archivos:
                        </span>
                        Dirección General de Instrucción Pública del Estado de Jalisco; Hospital;
                        Dirección General de Rentas; Ferrocarriles de Jalisco, 1897-1927; Jesús Camarena; Capitán
                        de Fragata de la Armada Mexicana Juan José Matute.
                    </p>
                    <p>
                        <span class="underline">
                            Mapoteca:
                        </span> Está integrada por 809 títulos de materiales cartográficos correspondientes a los
                        siglos XIX y XX y unos pocos del siglo XVIII.
                    </p>
                </div>

            </div>
        </article>

        <article>
            <div class="w-full max-w-4xl mx-auto px-4 pt-6">

                <h2 class=" text-3xl border-guinda  p-2 border-l-4">2. Bibliotecas Álvarez del Castillo y Florescano</h2>
                <div class="post-contenido ">
                    <p>
                        <span class="underline">
                            Biblioteca Jorge Álvarez del Castillo:
                        </span>
                        Resguarda 94,000 volúmenes aproximadamente. Es un
                        fondo universal en sus temas y géneros. Contiene correspondencia original de Benito Juárez, un
                        manuscrito de Gerardo Murillo “Dr. Atl” y otro de Amado Nervo. Resguarda tres de los primeros
                        impresos en Guadalajara (1793), cinco de los primeros libros de las Américas (el más antiguo de
                        1559), un libro de coro y 360 diferentes ediciones de Don Quijote de la Mancha
                    </p>

                    <p>
                        <span class="underline">
                            Biblioteca Enrique Florescano:
                        </span>
                        Consta de 18 mil volúmenes. Destacan entre sus libros una gran
                        cantidad de códices facsimilares, así como estudios de historia indígena, de los olmecas,
                        teotihuacanos, mayas y aztecas.
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
