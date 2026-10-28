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
        <h2 class=" text-3xl border-guinda  p-2 border-l-4">1. Archivos Históricos y Mapoteca</h2>
        <div class="post-contenido ">
            <p>
                <span class="underline">Archivo de la Real Audiencia de Guadalajara:</span> Durante el periodo colonial una extensa región del territorio novohispano conformó la jurisdicción del tribunal judicial de la Audiencia de Guadalajara[cite: 1]. Este archivo cubre desde el siglo XVI hasta la primera década del siglo XIX[cite: 1]. Obtuvo en 2010 el reconocimiento de Memoria del Mundo de México por la UNESCO[cite: 1].
            </p>
            <p>
                <span class="underline">Archivo del Supremo Tribunal de Justicia de Jalisco:</span> Resguarda la memoria judicial del estado desde 1824 hasta la primera mitad del siglo XX[cite: 1].
            </p>
            <p>
                <span class="underline">Otros archivos:</span> Dirección General de Instrucción Pública del Estado de Jalisco; Hospital; Dirección General de Rentas; Ferrocarriles de Jalisco, 1897-1927; Jesús Camarena; Capitán de Fragata de la Armada Mexicana Juan José Matute[cite: 1].
            </p>
            <p>
                <span class="underline">Mapoteca:</span> Está integrada por 809 títulos de materiales cartográficos correspondientes a los siglos XIX y XX y unos pocos del siglo XVIII[cite: 1].
            </p>
        </div>
    </div>
</article>

<article>
    <div class="w-full max-w-4xl mx-auto px-4 pt-6">
        <h2 class=" text-3xl border-guinda  p-2 border-l-4">2. Bibliotecas Álvarez del Castillo y Florescano</h2>
        <div class="post-contenido ">
            <p>
                <span class="underline">Biblioteca Jorge Álvarez del Castillo:</span> Resguarda 94,000 volúmenes aproximadamente[cite: 1]. Es un fondo universal en sus temas y géneros[cite: 1]. Contiene correspondencia original de Benito Juárez, un manuscrito de Gerardo Murillo “Dr. Atl” y otro de Amado Nervo[cite: 1]. Resguarda tres de los primeros impresos en Guadalajara (1793), cinco de los primeros libros de las Américas (el más antiguo de 1559), un libro de coro y 360 diferentes ediciones de Don Quijote de la Mancha[cite: 1].
            </p>
            <p>
                <span class="underline">Biblioteca Enrique Florescano:</span> Consta de 18 mil volúmenes[cite: 1]. Destacan entre sus libros una gran cantidad de códices facsimilares, así como estudios de historia indígena, de los olmecas, teotihuacanos, mayas y aztecas[cite: 1].
            </p>
        </div>
    </div>
</article>

<article>
    <div class="w-full max-w-4xl mx-auto px-4 pt-6">
        <h2 class=" text-3xl border-guinda  p-2 border-l-4">3. Hemeroteca Histórica</h2>
        <div class="post-contenido ">
            <p>Resguarda periódicos impresos desde 1805 en México y en Guadalajara desde 1809 hasta finales del siglo pasado[cite: 1]. Incluye prensa local y nacional, prensa jurídica, duplicados de México y de Jalisco[cite: 1]. Algunos de los títulos con los que cuenta la Hemeroteca Histórica: El Semanario Patriótico (1809), Gaceta de Guadalajara (1823), El Imperio (1864-1866), Las Clases Productoras (1878-1880), La Chispa (1868), Juan Panadero (1872-904) y La Labor Nueva (1914-1915)[cite: 1].</p>
        </div>
    </div>
</article>

<article>
    <div class="w-full max-w-4xl mx-auto px-4 pt-6">
        <h2 class=" text-3xl border-guinda  p-2 border-l-4">4. Publicaciones Seriadas</h2>
        <div class="post-contenido ">
            <p>Revistas desde el siglo XIX hasta 1989[cite: 1]. Para su mejor localización están dividas en cuatro apartados: publicaciones seriadas de Jalisco (siglo XIX-1999); publicaciones seriadas de México (siglo XIX-1999); publicaciones seriadas de los estados de la República Mexicana (siglo XX); publicaciones seriadas de países (siglo XX)[cite: 1].</p>
        </div>
    </div>
</article>

<article>
    <div class="w-full max-w-4xl mx-auto px-4 pt-6">
        <h2 class=" text-3xl border-guinda  p-2 border-l-4">5. Fondos Particulares</h2>
        <div class="post-contenido ">
            <p>El acervo está integrado por fondos de jaliscienses notables[cite: 1]. Consta de alrededor de 80,000 volúmenes[cite: 1]. La mayoría son impresos europeos y americanos de temáticas muy variadas[cite: 1]. Algunos de los fondos son: José Cornejo Franco, Pedro Espinosa y Dávalos, Ignacio Igor Arriola Haro, Arturo Rivas Sainz, Phil Weigand, Jorge Enciso, Luis M. Rivera, Saúl Rodiles, Paulino Machorro, Álvaro Matute y Evelia Trejo, Felipe de la Rosa y Ernesto Flores, entre otros[cite: 1].</p>
        </div>
    </div>
</article>

<article>
    <div class="w-full max-w-4xl mx-auto px-4 pt-6">
        <h2 class=" text-3xl border-guinda  p-2 border-l-4">6. Fondos Especiales y Tesoro Bibliográfico</h2>
        <div class="post-contenido ">
            <p>Colecciones bibliográficas de los siglos XV al XX[cite: 1].</p>
            <p>
                <span class="underline">Colección de Libros de Lenguas Indígenas:</span> Consta de 128 títulos y 166 volúmenes elaborados principalmente con fines de evangelización del siglo XVI al XX, que conservan el testimonio de 21 lenguas, pertenecientes a nueve familias lingüísticas distintas[cite: 1]. Esto permite resaltar su importancia ya que se trata de un conjunto de obras que constituyen el único testimonio existente con el que se cuenta sobre algunas lenguas, como es el caso de las coahuilteca, ópata y teguima[cite: 1]. De ahí que dicha colección fuera declarada Memoria del Mundo por la UNESCO[cite: 1].
            </p>
            <p>
                <span class="underline">Incunables:</span> Libros impresos en caracteres móviles desde los orígenes de la imprenta hacia 1450 hasta 1500[cite: 1].
            </p>
            <p>
                <span class="underline">Primeros Impresos Mexicanos, Siglo XVI:</span> 16 monografías que representan las primeras impresiones en la Nueva España[cite: 1].
            </p>
            <p>
                <span class="underline">Manuscritos (1531-1950):</span> Conformado principalmente por Fondo Franciscano y Fondo Particular de Luis M. Rivera[cite: 1].
            </p>
            <p>
                <span class="underline">Colección de Joyas Bibliográficas de la Biblioteca Pública del Estado de Jalisco:</span> Se trata de textos inicialmente impresos en los siglos XVI al XIX, la edición de algunas de estas obras son auténticas “Editio Prínceps”[cite: 1].
            </p>
            <p>
                <span class="underline">Otras colecciones:</span> Obras impresas en Europa durante el Siglo XVI; Colección de Códices Facsimilares; Fondo de Cedularios; Fondo Reservado; Guía de Forasteros y Calendarios desde el Siglo XVII al XX; Álbum Fotográfico Casasola[cite: 1].
            </p>
        </div>
    </div>
</article>

<article>
    <div class="w-full max-w-4xl mx-auto px-4 pt-6">
        <h2 class=" text-3xl border-guinda  p-2 border-l-4">7. Archivo Visual y Sonoro (AVS)</h2>
        <div class="post-contenido ">
            <p>Es el repositorio en su tipo más grande del estado y la región[cite: 1]. En el AVS se resguardan acervos audiovisuales, fotográficos, iconográficos, fonográficos, bibliográficos y documentales[cite: 1]. Se trata de más de 150,000 unidades organizadas (incluyendo un acervo fílmico) en fondos relacionados principalmente con la cultura cinematográfica, musical y visual de México y Jalisco[cite: 1].</p>
            <p>Los acervos del AVS se resguardan en más de veinte fondos[cite: 1]. Algunos de estos son: Emilio García Riera, Películas Nacionales, Mediateca Emilio García Riera, Festival Internacional de Cine en Guadalajara, Almendra McBride, Ignacio y Gabriel Retes, Cine y Video Casero,, Departamento de Imagen y Sonido UDG, Instituto Francés de América Latina (IFAL), Cine Popular, El Informador, Alberto Gómez Barbosa, Comunicación Social UDG, Federico Solórzano Barreto, Luis Jorge Figueroa, Melba Wasey, Cátedra Latinoamericana Julio Cortázar, Miguel Aldana Mijares, Alfonso de Lara Gallardo, Radio UDG, Francisco Navarro, Gabriel Pareyón, Escuela de Música UDG y Emilio Pardo[cite: 1].</p>
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
