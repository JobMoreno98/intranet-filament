@extends('layouts.plantilla')

@section('content')
    <div class="min-h-screen">
        <!-- Encabezado -->
        <header class="w-full bg-[#86212b] px-4 py-8">
            <div class="mx-auto max-w-6xl text-center">
                <h1 class="text-xl font-bold uppercase tracking-[0.15em] text-white md:text-2xl">
                    {{ $title ?? 'Créditos' }}
                </h1>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-5 py-12 md:px-8 md:py-16">
            <!-- Presentación -->
            <section class="mb-12">
                <span class="mb-4 block text-sm font-semibold uppercase tracking-widest text-[#86212b] dark:text-[#d98b91]">
                    Arreola Histórica
                </span>
                <h2 class="text-3xl font-semibold leading-tight tracking-tight text-zinc-800 dark:text-zinc-100 md:text-4xl">
                    Créditos del proyecto
                </h2>
                <div class="mt-5 h-1 w-16 bg-guinda dark:bg-[#d98b91]"></div>
            </section>

            <!-- Universidad de Guadalajara -->
            <section class="mb-12">
                <div class="mb-6 border-b border-zinc-200 pb-3 dark:border-zinc-700">
                    <h3 class="text-xl font-semibold text-zinc-800 dark:text-zinc-100 md:text-2xl">
                        Universidad de Guadalajara
                    </h3>
                </div>

                <dl class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-semibold text-[#86212b] dark:text-[#d98b91]">Rectora general</dt>
                        <dd class="mt-1 text-base leading-7 text-zinc-700 dark:text-zinc-300">Mtra. Karla Alejandrina Planter Pérez</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold text-[#86212b] dark:text-[#d98b91]">Vicerrector Ejecutivo</dt>
                        <dd class="mt-1 text-base leading-7 text-zinc-700 dark:text-zinc-300">Dr. Héctor Raúl Solís Gadea</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold text-[#86212b] dark:text-[#d98b91]">Director del Sistema Universitario de Bibliotecas</dt>
                        <dd class="mt-1 text-base leading-7 text-zinc-700 dark:text-zinc-300">Dr. Sergio López Ruelas</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold text-[#86212b] dark:text-[#d98b91]">Director de Biblioteca Pública del Estado de Jalisco</dt>
                        <dd class="mt-1 text-base leading-7 text-zinc-700 dark:text-zinc-300">Mtro. José Trinidad Padilla López</dd>
                    </div>
                </dl>
            </section>

            <!-- Equipo -->
            <section class="mb-12">
                <div class="mb-6 border-b border-zinc-200 pb-3 dark:border-zinc-700">
                    <h3 class="text-xl font-semibold text-zinc-800 dark:text-zinc-100 md:text-2xl">Equipo de Arreola Histórica</h3>
                </div>

                <div class="grid gap-8 md:grid-cols-2">
                    <div>
                        <h4 class="mb-2 text-sm font-semibold uppercase tracking-wide text-[#86212b] dark:text-[#d98b91]">Coordinación de proyecto</h4>
                        <p class="leading-7 text-zinc-700 dark:text-zinc-300">Rafael S. Villegas y Donají Padilla Mireles</p>
                    </div>
                    <div>
                        <h4 class="mb-2 text-sm font-semibold uppercase tracking-wide text-[#86212b] dark:text-[#d98b91]">Coordinación administrativa</h4>
                        <p class="leading-7 text-zinc-700 dark:text-zinc-300">María Dolores Arias Lara</p>
                    </div>
                    <div>
                        <h4 class="mb-2 text-sm font-semibold uppercase tracking-wide text-[#86212b] dark:text-[#d98b91]">Digitalización</h4>
                        <p class="leading-7 text-zinc-700 dark:text-zinc-300">Yadira Cea y Francisco Velasco</p>
                    </div>
                    <div>
                        <h4 class="mb-2 text-sm font-semibold uppercase tracking-wide text-[#86212b] dark:text-[#d98b91]">Programación y tecnología</h4>
                        <p class="leading-7 text-zinc-700 dark:text-zinc-300">Santiago González, Job Moreno, Francisco Tomás, Roberto Barrios y Esaú Mendoza.</p>
                    </div>
                    <div>
                        <h4 class="mb-2 text-sm font-semibold uppercase tracking-wide text-[#86212b] dark:text-[#d98b91]">Difusión y diseño gráfico</h4>
                        <p class="leading-7 text-zinc-700 dark:text-zinc-300">Josué Nolasco e Iván Sánchez</p>
                    </div>
                </div>

                <div class="mt-8 rounded-lg border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-700 dark:bg-zinc-800/60 md:p-7">
                    <h4 class="mb-3 text-sm font-semibold uppercase tracking-wide text-[#86212b] dark:text-[#d98b91]">Asesoría y control intelectual</h4>
                    <p class="leading-8 text-zinc-700 dark:text-zinc-300">
                        Guadalupe Martínez Corona, Ángeles Domínguez, Indira Palomino, María Eugenia Barrios, Miguel Tamayo,
                        Óscar Espinoza, Hugo Salas, Cristina Meza, Mario Bernache, Fernando Gutiérrez, Mónica Jiménez,
                        Joselyn Alvarado, Blanca García, Virginia Márquez, Miriam Goiz, Alicia Delgadillo, Laura Benítez,
                        Alejandro Solís y Carolina Bueno.
                    </p>
                </div>
            </section>

            <!-- Agradecimientos -->
            <section class="mb-12">
                <div class="mb-6 border-b border-zinc-200 pb-3 dark:border-zinc-700">
                    <h3 class="text-xl font-semibold text-zinc-800 dark:text-zinc-100 md:text-2xl">Agradecimientos</h3>
                </div>
                <div class="space-y-5 text-justify text-base leading-8 text-zinc-600 dark:text-zinc-300">
                    <p>
                        A la familia Arreola: Sara Sánchez †; Claudia Berenice Arreola Sánchez, Fuensanta Arreola Sánchez,
                        Orso Arreola Sánchez †, José María Arreola Velasco, Alonso Arreola Velasco, Mireya Hernández Arreola,
                        Berenice Hernández Arreola, Sara Arreola Cuenca y Juan José Arreola Cuenca.
                    </p>
                    <p>
                        Rosa Beltrán y Socorro Venegas (Universidad Nacional Autónoma de México); Juan Nepote (Museo de
                        Ciencias Ambientales); José Luis Valencia (Centro Cultural Universitario); Ana María de la O Castellanos
                        (Centro Universitario de la Costa Sur UDG); Juana Suárez (New York University); Édgar Torres y Tania
                        López Espinal (Cineteca Nacional); Mauricio Bidault y Andrés González Cosío (Cineteca UDG);
                        José Ramón Mikelajáuregui e Itzel Rangel (Departamento de Imagen y Sonido UDG); Martín Plascencia
                        (Radio UDG); Abril Ambriz, Erandi Barbosa, Víctor Ramírez y Robert Curley (Centro Universitario de
                        Ciencias Sociales y Humanidades UDG); Ixchel Ruiz Anguiano y Mariela Bárcenas (El Colegio de Jalisco);
                        Guillermo Ortega (Casa Zuno – Archivo Histórico UDG); Alejandra Aguiñaga y Eduardo Camacho
                        (Laboratorio de Humanidades Digitales – Centro Universitario de los Lagos UDG); Brenda Estupiñán y
                        Roberto Díaz Mares (Sistema Universitario de Bibliotecas UDG).
                    </p>
                    <p>
                        Alberto Gómez Barbosa, Francisco Navarro †, Amanda García Martín, Cristina Martín Sarrat, Celia Montes,
                        Gabriela Salazar, Cielo Orozco, Gilberto López, Lorena González Medina, Amparo López, Antonio Padilla,
                        Juan Pedro Hernández, Jorge María, Izcóatl Villagrana, Salma Yerenas, Evelyn Ramírez de Santiago,
                        Mayra Padilla, Víctor Hugo Carrera, Ariadna García y Juan Carlos Martínez.
                    </p>
                </div>
            </section>

            <!-- Dedicatoria -->
            <section class="rounded-lg bg-guinda px-6 py-10 md:px-12">
                <span class="mb-3 block text-xs font-semibold uppercase tracking-[0.2em] text-white/70">Dedicatoria</span>
                <p class="text-base leading-8 text-white/95 md:text-lg">
                    Dedicamos este proyecto a las generaciones de bibliotecarios, archivistas, conservadores y estudiantes
                    que desde 1861, con su trabajo discreto y cotidiano, han organizado, registrado, restaurado y digitalizado
                    las unidades documentales que hoy son accesibles al mundo a través de Arreola Histórica.
                </p>
            </section>
        </main>
    </div>
@endsection
