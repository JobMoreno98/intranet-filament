@extends('layouts.plantilla')

@section('content')
    <div class="min-h-screen">
        <!-- Encabezado -->
        <header class="w-full bg-[#86212b] px-4 py-10">
            <div class="mx-auto max-w-6xl text-center">
                <h1 class="text-xl font-bold uppercase tracking-[0.15em] text-white md:text-2xl">
                    {{ $title }}
                </h1>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-5 py-12 md:px-8 md:py-16">
            <!-- Contenido Principal -->
            <section class="mb-12">
                <div class="space-y-5 text-justify text-base leading-8 text-zinc-700 dark:text-zinc-300">
                    <p class="text-xl leading-9 text-zinc-800 dark:text-zinc-100">
                        La Biblioteca Pública del Estado de Jalisco “Juan José Arreola” es una de las más importantes del país, no sólo su tamaño o la cantidad de materiales que resguarda, sino por el valor histórico y cultural de los mismos.
                    </p>

                    <p>
                        Su fundación el 24 de julio de 1861 marca un hito importante en la historia cultural y educativa de la región. Su establecimiento se llevó a cabo mediante el título quinto del Plan General de Enseñanza, a través de un decreto emitido por el entonces gobernador Pedro Ogazón:
                    </p>

                    <div class="mt-8 rounded-lg border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-700 dark:bg-zinc-800/60 md:p-7">
                        <p class="italic text-zinc-600 dark:text-zinc-300 border-l-4 border-[#86212b] pl-5 dark:border-[#d98b91]">
                            “La biblioteca pública se formará de las obras que pertenecen al Instituto, de las del Seminario y de los conventos extinguidos. La Junta (Directiva de Estudios, de quien dependería la biblioteca) hará formar inventarios de los libros procedentes de esas bibliotecas, así como de los enseres y útiles, para que sean aplicados a su objeto, y dicha biblioteca pública quede en servicio a la mayor brevedad posible, destinando al Liceo las obras que crean convenientes”.
                        </p>
                        <p class="mt-3 text-sm font-semibold text-zinc-500 dark:text-zinc-400 pl-5">
                            — Decreto del gobernador Pedro Ogazón, 1861
                        </p>
                    </div>

                    <div class="my-8 flex justify-center">
                        <img class="h-80 rounded-lg shadow-sm" src="{{ asset('img/Imagen1.png') }}" alt="Imagen 1">
                    </div>

                    <p>
                        Así, las colecciones que conformaron el primer acervo de la Biblioteca provenían del Instituto de Estudios Profesionales del Estado, del Colegio Seminario Tridentino del Señor San José, el Colegio de Niñas de San Diego y varios conventos que ya habían sido extintos para esa época: San Francisco, San Agustín, San Felipe, Santo Domingo, Nuestra Señora del Carmen, Nuestra Señora de la Merced, Belén y del Colegio Apostólico de Nuestra Señora de Zapopan. La incorporación de estas colecciones a la Biblioteca no solo representó la consolidación de una nueva institución cultural en Jalisco, sino también justificó la puesta a disposición del público de valiosos materiales provenientes de diversas fuentes religiosas de la época. Esto contribuyó a disponer de una nueva forma de acceso al conocimiento y a justificar la preservación de parte del patrimonio cultural y documental de la región.
                    </p>

                    <p>
                        El primer director fue el señor Ignacio Acal de 1861 hasta 1863, quien inició con los trabajos de clasificación y organización de los materiales; le sucedió José María Vigil en 1872. Debido a los arduos trabajos en la Biblioteca, aunado a lo convulsionado del siglo XIX, esta se inauguró hasta el 18 de diciembre de 1874, durante el gobierno del licenciado Ignacio L. Vallarta, siendo su director Diego Baz. De inicio, la Biblioteca puso a disposición de sus usuarios veinte mil volúmenes.
                    </p>

                    <p>
                        La primera sede de la Biblioteca estuvo en la planta alta del antiguo Seminario de Guadalajara, que posteriormente fue el Liceo de Varones y actualmente es el Museo Regional de Guadalajara. La Biblioteca ocupó este espacio hasta el 30 de marzo de 1891, cuando cerró sus puertas para trasladarse a un espacio más adecuado en el mismo edificio. Después de su cierre temporal, la Biblioteca reabrió el 1 de enero de 1894 en una nueva ubicación, esta vez en la parte baja del edificio, entre las calles Hidalgo y Pino Suárez.
                    </p>

                    <p>
                        En 1925, la Biblioteca pasó a formar parte de la Universidad de Guadalajara. El gobernador José Guadalupe Zuno Hernández decretó el 15 de septiembre, en lo relativo a la Ley Orgánica de la Universidad de Guadalajara, que la Biblioteca sería, de esa fecha en adelante, una de las instituciones que formarían parte de la Universidad tomando una función de apoyo y fomento a la cultura.
                    </p>

                    <p>
                        La segunda sede de la Biblioteca fue inaugurada en febrero de 1959 en las inmediaciones del Parque Agua Azul, en avenida 16 de septiembre no. 849. Sin embargo, aunque la inauguración formal tuvo lugar ese año, no fue sino hasta inicios del año 1975 que la Biblioteca inició operaciones en estas nuevas instalaciones. José Cornejo Franco, director de la Biblioteca en ese periodo, desempeñó un papel crucial al insistir en acondicionar y remodelar adecuadamente las nuevas instalaciones para garantizar la seguridad y conservación de los acervos. Este enfoque en la preservación y seguridad de los materiales refleja el compromiso de la Biblioteca con el cuidado y mantenimiento de su valioso patrimonio cultural.
                    </p>

                    <div class="my-8 flex justify-center">
                        <img class="h-80 rounded-lg shadow-sm" src="{{ asset('img/Imagen2.png') }}" alt="Imagen 2">
                    </div>

                    <p>
                        En 2001, el Consejo General Universitario aprobó cambiar agregar el nombre “Juan José Arreola” al nombre de la Biblioteca en memoria de este destacado escritor mexicano que fue director de la misma durante una década, entre 1991 y 2001.
                    </p>

                    <p>
                        En 2004, debido a problemas estructurales del edificio de 16 de Septiembre no. 849, los acervos bibliográficos y la hemeroteca contemporánea se trasladaron al edificio ubicado en avenida Alcalde no. 130, permitiendo así preservar los materiales en un entorno más seguro y adecuado. El 3 de mayo de 2011 inició el traslado de los acervos de la Biblioteca a un nuevo edificio ubicado en el Centro Cultural de la Universidad de Guadalajara (Periférico Norte Manuel Gómez Morín no. 1695). Este cambio de sede proporcionó instalaciones más modernas y adecuadas (distribuidas en dos edificios, la Biblioteca Histórica y la Contemporánea) para la preservación y acceso a los acervos.
                    </p>

                    <p>
                        En lo administrativo, la Biblioteca primero formó parte del Instituto de Bibliotecas, posteriormente de la Coordinación de Bibliotecas y a partir de 2009 del Centro Universitario de Ciencias Sociales y Humanidades (CUCSH). Actualmente, la BPEJ depende del Sistema Universitario de Bibliotecas de la Universidad de Guadalajara.
                    </p>

                    <div class="my-8 flex justify-center">
                        <img class="h-80 rounded-lg shadow-sm" src="{{ asset('img/Imagen3.png') }}" alt="Imagen 3">
                    </div>

                    <p>
                        Estos cambios reflejan el compromiso continuo de la Biblioteca Pública del Estado de Jalisco "Juan José Arreola" con la adaptación y mejora constante para cumplir con los estándares de preservación y proporcionar servicios de calidad a la comunidad.
                    </p>
                </div>
            </section>

            <!-- Botones de Compartir -->
            <section class="mt-12 border-t border-zinc-200 pt-8 dark:border-zinc-700">
                <div class="flex items-center justify-center gap-4">
                    <span class="text-xs font-bold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Compartir:</span>
                    <a href="https://wa.me/?text={{ urlencode(url()->current()) }}" target="_blank" rel="noopener"
                        class="rounded-full bg-zinc-100 px-4 py-2 text-xs font-medium text-zinc-700 transition-colors hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                        WhatsApp
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank" rel="noopener"
                        class="rounded-full bg-zinc-100 px-4 py-2 text-xs font-medium text-zinc-700 transition-colors hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                        Facebook
                    </a>
                </div>
            </section>
        </main>
    </div>
@endsection
