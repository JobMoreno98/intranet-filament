@php
    // REEMPLAZA con los datos reales
    $direccion = 'Periférico Norte Manuel Gómez Morín no. 1695, Col. Belenes, 
C.P. 45100, Zapopan, Jalisco, México';
    $telefono = '33 0000 0000';
    $correo = 'arreola.historica@udg.mx';

    // Clave = día ISO (1 = lunes ... 7 = domingo). Usa null para "Cerrado".
    $bibliotecas = [
        [
            'nombre' => 'Biblioteca Histórica',
            'horarios' => [['Lunes a Viernes', [1, 2, 3, 4, 5], '9 a 15 hrs.']],
        ],
        [
            'nombre' => 'Biblioteca Contemporánea',
            'horarios' => [
                ['Lunes a Viernes', [1, 2, 3, 4, 5], '9 a 20 hrs.'],
                ['Sábado', [6], '9 a 17 hrs.'],
                ['Domingo', [7], '9 a 15 hrs.'],
            ],
        ],
    ];

    $hoy = now()->dayOfWeekIso;
@endphp
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

        .post-contenido>p {
            text-align: justify;
            margin-bottom: 10px;
        }

        /* Primer párrafo: más grande, como introducción */
        .post-contenido>p:first-child {
            @apply text-xl leading-9 text-zinc-800 dark:text-zinc-100;
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

        .dark .post-contenido>p:first-child::first-letter {
            color: #fca5a5;
            /* red-300 */
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
                    Historia
                </h1>
            </div>
        </div>

        <article>
            <div class="w-full max-w-4xl mx-auto px-4 py-12">

                <div class="post-contenido">
                    <p>
                        La Biblioteca Pública del Estado de Jalisco “Juan José Arreola” es una de las más importantes del
                        país, no sólo su tamaño o la cantidad de materiales que resguarda, sino por el valor histórico y
                        cultural de los mismos.
                    </p>

                    <p>
                        Su fundación el 24 de julio de 1861 marca un hito importante en la historia cultural y educativa de
                        la región. Su establecimiento se llevó a cabo mediante el título quinto del Plan General de
                        Enseñanza, a través de un decreto emitido por el entonces gobernador Pedro Ogazón:
                    </p>

                    <blockquote>
                        <p>
                            “La biblioteca pública se formará de las obras que pertenecen al Instituto, de las del Seminario
                            y de los conventos extinguidos. La Junta (Directiva de Estudios, de quien dependería la
                            biblioteca) hará formar inventarios de los libros procedentes de esas bibliotecas, así como de
                            los enseres y útiles, para que sean aplicados a su objeto, y dicha biblioteca pública quede en
                            servicio a la mayor brevedad posible, destinando al Liceo las obras que crean convenientes”.
                        </p>
                        <footer>— Decreto del gobernador Pedro Ogazón, 1861</footer>
                    </blockquote>
                    <p>
                        <img class="mx-auto my-2 h-80" src="{{ asset('img/Imagen1.png') }}">
                    </p>
                    <p>
                        Así, las colecciones que conformaron el primer acervo de la Biblioteca provenían del Instituto de
                        Estudios Profesionales del Estado, del Colegio Seminario Tridentino del Señor San José, el Colegio
                        de Niñas de San Diego y varios conventos que ya habían sido extintos para esa época: San Francisco,
                        San Agustín, San Felipe, Santo Domingo, Nuestra Señora del Carmen, Nuestra Señora de la Merced,
                        Belén y del Colegio Apostólico de Nuestra Señora de Zapopan. La incorporación de estas colecciones
                        a la Biblioteca no solo representó la consolidación de una nueva institución cultural en Jalisco,
                        sino
                        también justificó la puesta a disposición del público de valiosos materiales provenientes de
                        diversas
                        fuentes religiosas de la época. Esto contribuyó a disponer de una nueva forma de acceso al
                        conocimiento y a justificar la preservación de parte del patrimonio cultural y documental de la
                        región.
                    </p>
                    <p>
                        El primer director fue el señor Ignacio Acal de 1861 hasta 1863, quien inició con los trabajos de
                        clasificación y organización de los materiales; le sucedió José María Vigil en 1872. Debido a los
                        arduos trabajos en la Biblioteca, aunado a lo convulsionado del siglo XIX, esta se inauguró hasta el
                        18 de diciembre de 1874, durante el gobierno del licenciado Ignacio L. Vallarta, siendo su director
                        Diego Baz. De inicio, la Biblioteca puso a disposición de sus usuarios veinte mil volúmenes.
                    </p>

                    <p>
                        La primera sede de la Biblioteca estuvo en la planta alta del antiguo Seminario de Guadalajara, que
                        posteriormente fue el Liceo de Varones y actualmente es el Museo Regional de Guadalajara. La
                        Biblioteca ocupó este espacio hasta el 30 de marzo de 1891, cuando cerró sus puertas para
                        trasladarse a un espacio más adecuado en el mismo edificio. Después de su cierre temporal, la
                        Biblioteca reabrió el 1 de enero de 1894 en una nueva ubicación, esta vez en la parte baja del
                        edificio, entre las calles Hidalgo y Pino Suárez.
                    </p>

                    <p>
                        En 1925, la Biblioteca pasó a formar parte de la Universidad de Guadalajara. El gobernador José
                        Guadalupe Zuno Hernández decretó el 15 de septiembre, en lo relativo a la Ley Orgánica de la
                        Universidad de Guadalajara, que la Biblioteca sería, de esa fecha en adelante, una de las
                        instituciones que formarían parte de la Universidad tomando una función de apoyo y fomento a la
                        cultura.
                    </p>

                    <p>
                        La segunda sede de la Biblioteca fue inaugurada en febrero de 1959 en las inmediaciones del Parque
                        Agua Azul, en avenida 16 de septiembre no. 849. Sin embargo, aunque la inauguración formal tuvo
                        lugar ese año, no fue sino hasta inicios del año 1975 que la Biblioteca inició operaciones en estas
                        nuevas instalaciones. José Cornejo Franco, director de la Biblioteca en ese periodo, desempeñó un
                        papel crucial al insistir en acondicionar y remodelar adecuadamente las nuevas instalaciones para
                        garantizar la seguridad y conservación de los acervos. Este enfoque en la preservación y seguridad
                        de los materiales refleja el compromiso de la Biblioteca con el cuidado y mantenimiento de su
                        valioso patrimonio cultural.
                    </p>
                    <p>
                        <img class="mx-auto my-2 h-80" src="{{ asset('img/Imagen2.png') }}">
                    </p>
                    <p>
                        En 2001, el Consejo General Universitario aprobó cambiar agregar el nombre “Juan José Arreola” al
                        nombre de la Biblioteca en memoria de este destacado escritor mexicano que fue director de la
                        misma durante una década, entre 1991 y 2001.
                    </p>
                    <p>
                        En 2004, debido a problemas estructurales del edificio de 16 de Septiembre no. 849, los acervos
                        bibliográficos y la hemeroteca contemporánea se trasladaron al edificio ubicado en avenida Alcalde
                        no. 130, permitiendo así preservar los materiales en un entorno más seguro y adecuado. El 3 de
                        mayo de 2011 inició el traslado de los acervos de la Biblioteca a un nuevo edificio ubicado en el
                        Centro Cultural de la Universidad de Guadalajara (Periférico Norte Manuel Gómez Morín no. 1695).
                        Este cambio de sede proporcionó instalaciones más modernas y adecuadas (distribuidas en dos
                        edificios, la Biblioteca Histórica y la Contemporánea) para la preservación y acceso a los acervos.
                    </p>
                    <p>
                        En lo administrativo, la Biblioteca primero formó parte del Instituto de Bibliotecas, posteriormente
                        de la Coordinación de Bibliotecas y a partir de 2009 del Centro Universitario de Ciencias Sociales y
                        Humanidades (CUCSH). Actualmente, la BPEJ depende del Sistema Universitario de Bibliotecas de la
                        Universidad de Guadalajara.
                    </p>
                    <p>
                        <img class="mx-auto my-2 h-80" src="{{ asset('img/Imagen3.png') }}">
                    </p>
                    <p>
                        Estos cambios reflejan el compromiso continuo de la Biblioteca Pública del Estado de Jalisco "Juan
                        José Arreola" con la adaptación y mejora constante para cumplir con los estándares de preservación
                        y proporcionar servicios de calidad a la comunidad.
                    </p>
                </div>

            </div>
            <div>
                <div class="w-full max-w-4xl mx-auto px-4 py-12 grid gap-6 md:grid-cols-2">

                    <div
                        class="flex flex-col gap-4 rounded-md border border-zinc-200 bg-white p-6 shadow-lg dark:border-zinc-600 dark:bg-zinc-700">
                        <h3 class="flex items-center gap-2 text-lg font-bold text-zinc-800 dark:text-white">
                            <x-heroicon-o-map-pin class="h-6 w-6 shrink-0 text-[#86212b] dark:text-red-300" />
                            Dirección
                        </h3>

                        <p class="text-zinc-700 dark:text-zinc-200">
                            Biblioteca Pública del Estado de Jalisco “Juan José Arreola”<br>
                            {{ $direccion }}
                        </p>

                        @if (filled($telefono))
                            <p class="flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-200">
                                <x-heroicon-o-phone class="h-5 w-5 shrink-0 text-zinc-400" />
                                <a href="tel:{{ preg_replace('/\D/', '', $telefono) }}"
                                    class="hover:underline">{{ $telefono }}</a>
                            </p>
                        @endif

                        @if (filled($correo))
                            <p class="flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-200">
                                <x-heroicon-o-envelope class="h-5 w-5 shrink-0 text-zinc-400" />
                                <a href="mailto:{{ $correo }}"
                                    class="break-all hover:underline">{{ $correo }}</a>
                            </p>
                        @endif

                        <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($direccion) }}"
                            target="_blank" rel="noopener"
                            class="mt-auto inline-flex w-fit items-center gap-1.5 rounded bg-red-800 px-3 py-2 text-xs font-bold text-white transition-colors hover:bg-red-950">
                            <x-heroicon-o-map class="h-4 w-4" />
                            Cómo llegar
                        </a>
                    </div>

                    {{-- Horarios --}}
                    <div
                        class="flex flex-col gap-5 rounded-md border border-zinc-200 bg-white p-6 shadow-lg dark:border-zinc-600 dark:bg-zinc-700">
                        <h3 class="flex items-center gap-2 text-lg font-bold text-zinc-800 dark:text-white">
                            <x-heroicon-o-clock class="h-6 w-6 shrink-0 text-[#86212b] dark:text-red-300" />
                            Horarios
                        </h3>

                        @foreach ($bibliotecas as $biblioteca)
                            <div class="flex flex-col gap-1">
                                <h4 class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-300">
                                    {{ $biblioteca['nombre'] }}
                                </h4>

                                <dl class="flex flex-col text-sm">
                                    @foreach ($biblioteca['horarios'] as [$etiqueta, $dias, $horario])
                                        <div
                                            class="flex items-center justify-between gap-4 rounded px-3 py-2
                        {{ in_array($hoy, $dias) ? 'bg-red-50 font-bold text-red-900 dark:bg-red-950/40 dark:text-red-100' : 'text-zinc-700 dark:text-zinc-200' }}">
                                            <dt>{{ $etiqueta }}</dt>
                                            <dd>{{ $horario }}</dd>
                                        </div>
                                    @endforeach
                                </dl>
                            </div>
                        @endforeach
                    </div>

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
