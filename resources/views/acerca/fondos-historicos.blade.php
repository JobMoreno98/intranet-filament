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

        <main class="mx-auto max-w-5xl px-5 py-12 md:px-8 md:py-16">
            
            <!-- Introducción -->
            <section class="mb-12 rounded-lg bg-guinda px-6 py-10 md:px-12 dark:bg-[#6a1a22]">
                <p class="text-base leading-8 text-white/95 md:text-lg text-justify">
                    Nuestros acervos se encuentran organizados en siete áreas, que a su vez resguardan fondos archivísticos, audiovisuales, bibliográficos, hemerográficos, fotográficos y sonoros:
                </p>
            </section>

            <!-- 1. Archivos Históricos y Mapoteca -->
            <section class="mb-12">
                <div class="mb-6 border-b border-zinc-200 pb-3 dark:border-zinc-700">
                    <h3 class="text-xl font-semibold text-zinc-800 dark:text-zinc-100 md:text-2xl">
                        1. Archivos Históricos y Mapoteca
                    </h3>
                </div>
                <dl class="grid gap-6 sm:grid-cols-2 text-justify">
                    <div>
                        <dt class="text-sm font-semibold uppercase tracking-wide text-[#86212b] dark:text-[#d98b91]">Archivo de la Real Audiencia de Guadalajara</dt>
                        <dd class="mt-2 text-base leading-7 text-zinc-700 dark:text-zinc-300">Durante el periodo colonial una extensa región del territorio novohispano conformó la jurisdicción del tribunal judicial de la Audiencia de Guadalajara. Este archivo cubre desde el siglo XVI hasta la primera década del siglo XIX. Obtuvo en 2010 el reconocimiento de Memoria del Mundo de México por la UNESCO.</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold uppercase tracking-wide text-[#86212b] dark:text-[#d98b91]">Archivo del Supremo Tribunal de Justicia de Jalisco</dt>
                        <dd class="mt-2 text-base leading-7 text-zinc-700 dark:text-zinc-300">Resguarda la memoria judicial del estado desde 1824 hasta la primera mitad del siglo XX.</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold uppercase tracking-wide text-[#86212b] dark:text-[#d98b91]">Otros archivos</dt>
                        <dd class="mt-2 text-base leading-7 text-zinc-700 dark:text-zinc-300">Dirección General de Instrucción Pública del Estado de Jalisco; Hospital; Dirección General de Rentas; Ferrocarriles de Jalisco, 1897-1927; Jesús Camarena; Capitán de Fragata de la Armada Mexicana Juan José Matute.</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold uppercase tracking-wide text-[#86212b] dark:text-[#d98b91]">Mapoteca</dt>
                        <dd class="mt-2 text-base leading-7 text-zinc-700 dark:text-zinc-300">Está integrada por 809 títulos de materiales cartográficos correspondientes a los siglos XIX y XX y unos pocos del siglo XVIII.</dd>
                    </div>
                </dl>
            </section>

            <!-- 2. Bibliotecas Álvarez del Castillo y Florescano -->
            <section class="mb-12">
                <div class="mb-6 border-b border-zinc-200 pb-3 dark:border-zinc-700">
                    <h3 class="text-xl font-semibold text-zinc-800 dark:text-zinc-100 md:text-2xl">
                        2. Bibliotecas Álvarez del Castillo y Florescano
                    </h3>
                </div>
                <dl class="grid gap-6 sm:grid-cols-2 text-justify">
                    <div>
                        <dt class="text-sm font-semibold uppercase tracking-wide text-[#86212b] dark:text-[#d98b91]">Biblioteca Jorge Álvarez del Castillo</dt>
                        <dd class="mt-2 text-base leading-7 text-zinc-700 dark:text-zinc-300">Resguarda 94,000 volúmenes aproximadamente. Es un fondo universal en sus temas y géneros. Contiene correspondencia original de Benito Juárez, un manuscrito de Gerardo Murillo “Dr. Atl” y otro de Amado Nervo. Resguarda tres de los primeros impresos en Guadalajara (1793), cinco de los primeros libros de las Américas (el más antiguo de 1559), un libro de coro y 360 diferentes ediciones de Don Quijote de la Mancha.</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold uppercase tracking-wide text-[#86212b] dark:text-[#d98b91]">Biblioteca Enrique Florescano</dt>
                        <dd class="mt-2 text-base leading-7 text-zinc-700 dark:text-zinc-300">Consta de 18 mil volúmenes. Destacan entre sus libros una gran cantidad de códices facsimilares, así como estudios de historia indígena, de los olmecas, teotihuacanos, mayas y aztecas.</dd>
                    </div>
                </dl>
            </section>

            <!-- 3. Hemeroteca Histórica -->
            <section class="mb-12">
                <div class="mb-6 border-b border-zinc-200 pb-3 dark:border-zinc-700">
                    <h3 class="text-xl font-semibold text-zinc-800 dark:text-zinc-100 md:text-2xl">
                        3. Hemeroteca Histórica
                    </h3>
                </div>
                <div class="space-y-5 text-justify text-base leading-8 text-zinc-700 dark:text-zinc-300">
                    <p>Resguarda periódicos impresos desde 1805 en México y en Guadalajara desde 1809 hasta finales del siglo pasado. Incluye prensa local y nacional, prensa jurídica, duplicados de México y de Jalisco. Algunos de los títulos con los que cuenta la Hemeroteca Histórica: El Semanario Patriótico (1809), Gaceta de Guadalajara (1823), El Imperio (1864-1866), Las Clases Productoras (1878-1880), La Chispa (1868), Juan Panadero (1872-904) y La Labor Nueva (1914-1915).</p>
                </div>
            </section>

            <!-- 4. Publicaciones Seriadas -->
            <section class="mb-12">
                <div class="mb-6 border-b border-zinc-200 pb-3 dark:border-zinc-700">
                    <h3 class="text-xl font-semibold text-zinc-800 dark:text-zinc-100 md:text-2xl">
                        4. Publicaciones Seriadas
                    </h3>
                </div>
                <div class="space-y-5 text-justify text-base leading-8 text-zinc-700 dark:text-zinc-300">
                    <p>Revistas desde el siglo XIX hasta 1989. Para su mejor localización están dividas en cuatro apartados: publicaciones seriadas de Jalisco (siglo XIX-1999); publicaciones seriadas de México (siglo XIX-1999); publicaciones seriadas de los estados de la República Mexicana (siglo XX); publicaciones seriadas de países (siglo XX).</p>
                </div>
            </section>

            <!-- 5. Fondos Particulares -->
            <section class="mb-12">
                <div class="mb-6 border-b border-zinc-200 pb-3 dark:border-zinc-700">
                    <h3 class="text-xl font-semibold text-zinc-800 dark:text-zinc-100 md:text-2xl">
                        5. Fondos Particulares
                    </h3>
                </div>
                <div class="space-y-5 text-justify text-base leading-8 text-zinc-700 dark:text-zinc-300">
                    <p>El acervo está integrado por fondos de jaliscienses notables. Consta de alrededor de 80,000 volúmenes. La mayoría son impresos europeos y americanos de temáticas muy variadas. Algunos de los fondos son: José Cornejo Franco, Pedro Espinosa y Dávalos, Ignacio Igor Arriola Haro, Arturo Rivas Sainz, Phil Weigand, Jorge Enciso, Luis M. Rivera, Saúl Rodiles, Paulino Machorro, Álvaro Matute y Evelia Trejo, Felipe de la Rosa y Ernesto Flores, entre otros.</p>
                </div>
            </section>

            <!-- 6. Fondos Especiales y Tesoro Bibliográfico -->
            <section class="mb-12">
                <div class="mb-6 border-b border-zinc-200 pb-3 dark:border-zinc-700">
                    <h3 class="text-xl font-semibold text-zinc-800 dark:text-zinc-100 md:text-2xl">
                        6. Fondos Especiales y Tesoro Bibliográfico
                    </h3>
                </div>
                <div class="mb-6 space-y-5 text-justify text-base leading-8 text-zinc-700 dark:text-zinc-300">
                    <p>Colecciones bibliográficas de los siglos XV al XX.</p>
                </div>
                <dl class="grid gap-6 sm:grid-cols-2 text-justify">
                    <div>
                        <dt class="text-sm font-semibold uppercase tracking-wide text-[#86212b] dark:text-[#d98b91]">Colección de Libros de Lenguas Indígenas</dt>
                        <dd class="mt-2 text-base leading-7 text-zinc-700 dark:text-zinc-300">Consta de 128 títulos y 166 volúmenes elaborados principalmente con fines de evangelización del siglo XVI al XX, que conservan el testimonio de 21 lenguas, pertenecientes a nueve familias lingüísticas distintas. Esto permite resaltar su importancia ya que se trata de un conjunto de obras que constituyen el único testimonio existente con el que se cuenta sobre algunas lenguas, como es el caso de las coahuilteca, ópata y teguima. De ahí que dicha colección fuera declarada Memoria del Mundo por la UNESCO.</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold uppercase tracking-wide text-[#86212b] dark:text-[#d98b91]">Incunables</dt>
                        <dd class="mt-2 text-base leading-7 text-zinc-700 dark:text-zinc-300">Libros impresos en caracteres móviles desde los orígenes de la imprenta hacia 1450 hasta 1500.</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold uppercase tracking-wide text-[#86212b] dark:text-[#d98b91]">Primeros Impresos Mexicanos, Siglo XVI</dt>
                        <dd class="mt-2 text-base leading-7 text-zinc-700 dark:text-zinc-300">16 monografías que representan las primeras impresiones en la Nueva España.</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold uppercase tracking-wide text-[#86212b] dark:text-[#d98b91]">Manuscritos (1531-1950)</dt>
                        <dd class="mt-2 text-base leading-7 text-zinc-700 dark:text-zinc-300">Conformado principalmente por Fondo Franciscano y Fondo Particular de Luis M. Rivera.</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-semibold uppercase tracking-wide text-[#86212b] dark:text-[#d98b91]">Colección de Joyas Bibliográficas de la Biblioteca Pública del Estado de Jalisco</dt>
                        <dd class="mt-2 text-base leading-7 text-zinc-700 dark:text-zinc-300">Se trata de textos inicialmente impresos en los siglos XVI al XIX, la edición de algunas de estas obras son auténticas “Editio Prínceps”.</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-semibold uppercase tracking-wide text-[#86212b] dark:text-[#d98b91]">Otras colecciones</dt>
                        <dd class="mt-2 text-base leading-7 text-zinc-700 dark:text-zinc-300">Obras impresas en Europa durante el Siglo XVI; Colección de Códices Facsimilares; Fondo de Cedularios; Fondo Reservado; Guía de Forasteros y Calendarios desde el Siglo XVII al XX; Álbum Fotográfico Casasola.</dd>
                    </div>
                </dl>
            </section>

            <!-- 7. Archivo Visual y Sonoro (AVS) -->
            <section class="mb-12">
                <div class="mb-6 border-b border-zinc-200 pb-3 dark:border-zinc-700">
                    <h3 class="text-xl font-semibold text-zinc-800 dark:text-zinc-100 md:text-2xl">
                        7. Archivo Visual y Sonoro (AVS)
                    </h3>
                </div>
                <div class="space-y-5 text-justify text-base leading-8 text-zinc-700 dark:text-zinc-300">
                    <p>Es el repositorio en su tipo más grande del estado y la región. En el AVS se resguardan acervos audiovisuales, fotográficos, iconográficos, fonográficos, bibliográficos y documentales. Se trata de más de 150,000 unidades organizadas (incluyendo un acervo fílmico) en fondos relacionados principalmente con la cultura cinematográfica, musical y visual de México y Jalisco.</p>
                    <p>Los acervos del AVS se resguardan en más de veinte fondos. Algunos de estos son: Emilio García Riera, Películas Nacionales, Mediateca Emilio García Riera, Festival Internacional de Cine en Guadalajara, Almendra McBride, Ignacio y Gabriel Retes, Cine y Video Casero, Departamento de Imagen y Sonido UDG, Instituto Francés de América Latina (IFAL), Cine Popular, El Informador, Alberto Gómez Barbosa, Comunicación Social UDG, Federico Solórzano Barreto, Luis Jorge Figueroa, Melba Wasey, Cátedra Latinoamericana Julio Cortázar, Miguel Aldana Mijares, Alfonso de Lara Gallardo, Radio UDG, Francisco Navarro, Gabriel Pareyón, Escuela de Música UDG y Emilio Pardo.</p>
                </div>
            </section>

            <!-- Botones de Compartir -->
            <section class="mt-12 border-t border-zinc-200 pt-8 dark:border-zinc-700">

            <div
                class="mx-auto w-full max-w-4xl mt-10 border-t border-zinc-200 dark:border-zinc-700 pt-6 flex items-center justify-center gap-3">
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

        </main>
    </div>
@endsection
