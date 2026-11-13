<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        {{ filled($title ?? null) ? $title . ' | ' . config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
    </title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link href="https://fonts.googleapis.com/css2?family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700&display=swap"
        rel="stylesheet">

    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/vnd.microsoft.icon" />

    <style>
        html {
            background-color: #fff;
        }

        html.dark {
            background-color: #1d1d1b;
        }

        body,
        html {
            scroll-behavior: smooth !important;
        }

        .pts {
            font-family: "PT Serif", serif;
        }

        @media (max-width: 800px) {
            #img-bpej {
                aspect-ratio: 16 / 5 !important;
            }
        }

        @media (min-width: 801px) {
            #img-bpej {
                aspect-ratio: 16 / 9 !important;
            }
        }

        #img-bpej {
            background-image: url('{{ asset('img/portada-web.jpg') }}');
            background-size: cover;
            background-position: center;
            max-height: 200px;
            width: 100%;
        }
    </style>

    @fluxAppearance
    @livewireStyles
    @yield('css')


</head>

<body class="antialiased font-titillium">

    @php
        $enlaces = [
            ['INICIO', 'home'],
            ['NUESTRA BIBLIOTECA', 'nuestra.historia'],
            ['Áreas y Fondos', 'areas.index'],

            //['FONDOS', 'fondos.index'],
            ['Arreola Histórica', 'arreola.historica'],

            //['NUESTROS FONDOS HISTÓRICOS', 'fondos.historicos'],

            ['CÓMO USAR', 'arreola.uso'],
            ['SOLICITUD DE REPRODUCCIONES', 'arreola.solicitud'],
            ['SALA ARREOLA (BLOG)', 'blog.index'],
            ['MEMORIA UDG', 'arreola.memoria'],
            ['CONTACTO', 'arreola.contacto'],
            ['CRÉDITOS', 'arreola.creditos'],
        ];
    @endphp
    <nav x-data="{ menuOpen: false }" @keydown.escape.window="menuOpen = false"
        x-effect="document.body.classList.toggle('overflow-hidden', menuOpen)" class="bg-white dark:bg-neutral-600">

        <div class="flex flex-wrap items-center justify-between w-full min-h-16 gap-x-4 gap-y-2 px-4 py-2 mx-auto">

            <a href="{{ route('home') }}" class="flex items-center font-bold text-xl shrink-0">

                <!-- SVG Claro: Se oculta cuando el modo oscuro está activo -->
                <img src="{{ asset('img/arreola-blanco.svg') }}" alt="{{ config('app.name', 'Laravel') }} Logo"
                    class="block dark:hidden h-16 mr-3 sm:h-16">

                <!-- SVG Oscuro: Permanece oculto hasta que el modo oscuro se activa -->
                <img src="{{ asset('img/arreola-oscuro.svg') }}" alt="{{ config('app.name', 'Laravel') }} Logo"
                    class="hidden dark:block h-16 mr-3 sm:h-16">
            </a>

            {{-- Texto siempre visible: en móvil baja a su propia fila (order-last), en lg va entre logo y botones --}}
            <p
                class="hidden md:block order-last w-full text-center text-md leading-snug text-zinc-700 dark:text-zinc-200 lg:order-none lg:w-auto lg:flex-1 lg:min-w-0">
                Arreola Histórica es el buscador y repositorio digital de los fondos históricos de la Biblioteca Pública
                del Estado de Jalisco de la Universidad de Guadalajara.

            </p>

            <div class="flex items-center gap-2 md:gap-4">
                <div x-data class="flex items-center">
                    <button x-on:click="$flux.dark = ! $flux.dark" type="button"
                        class="p-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition shadow-sm group">
                        <span x-show="! $flux.dark" x-cloak>
                            <x-heroicon-o-moon class="w-5 h-5 group-hover:scale-110 transition-transform" />
                        </span>
                        <span x-show="$flux.dark" x-cloak>
                            <x-heroicon-o-sun
                                class="w-5 h-5 text-amber-400 group-hover:rotate-45 transition-transform" />
                        </span>
                    </button>
                </div>

                <button @click="menuOpen = true" type="button"
                    class="inline-flex items-center p-2 text-sm text-gray-500 rounded-lg hover:bg-gray-100 focus:outline-none dark:text-gray-400 dark:hover:bg-gray-700"
                    aria-controls="menu-principal" :aria-expanded="menuOpen">
                    <span class="sr-only">Abrir menú principal</span>
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                            d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                            clip-rule="evenodd"></path>
                    </svg>
                </button>

            </div>

            {{-- Fondo oscuro --}}
            <div x-show="menuOpen" x-cloak @click="menuOpen = false" x-transition.opacity.duration.300ms
                class="fixed inset-0 z-40 bg-black/60" aria-hidden="true"></div>

            {{-- Cajón lateral --}}
            <aside id="menu-principal" x-show="menuOpen" x-cloak role="dialog" aria-modal="true"
                aria-label="Menú principal" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
                class="fixed inset-y-0 right-0 z-50 flex w-72 max-w-[85vw] flex-col overflow-y-auto bg-white shadow-2xl dark:bg-neutral-600">

                {{-- Encabezado con botón de cerrar --}}
                <div
                    class="flex h-16 shrink-0 items-center justify-between border-b border-gray-200 px-4 dark:border-neutral-700">
                    <span
                        class="text-xs font-bold uppercase tracking-[0.2em] text-zinc-500 dark:text-zinc-300">Menú</span>
                    <button @click="menuOpen = false" type="button" aria-label="Cerrar menú"
                        class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 focus:outline-none dark:text-gray-400 dark:hover:bg-gray-700">
                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>

                <ul class="flex flex-col space-y-1 px-4 pt-4 font-medium">
                    @foreach ($enlaces as [$etiqueta, $ruta])
                        <li>
                            <a href="{{ route($ruta) }}"
                                class="uppercase block rounded-md px-3 py-2.5 transition-colors duration-200 hover:bg-guinda hover:text-white
                    {{ request()->routeIs($ruta) ? 'bg-guinda text-white' : 'text-zinc-700 dark:text-zinc-200' }}">
                                {{ $etiqueta }}
                            </a>
                        </li>
                    @endforeach
                </ul>

                {{-- Sesión: debajo de lg (en lg ya está en la barra). mt-auto la empuja al fondo del cajón --}}
                <div class="mt-auto border-t border-gray-200 p-4 dark:border-neutral-700 ">
                    @if (Auth::check())
                        <x-desktop-user-menu />
                    @else
                        <a href="{{ route('login') }}"
                            class="block w-full rounded-md bg-red-800 px-4 py-2 text-center text-white shadow-sm transition hover:bg-red-900">
                            {{ __('Log In') }}
                        </a>
                    @endif
                </div>
            </aside>
    </nav>

    <section class="relative flex flex-col dark:bg-gray-200">

        {{-- Contenedor principal (Usa el Azul Oscuro #131f30 de tu paleta) --}}
        <div
            class="relative w-full bg-[#131f30] pt-14 pb-10 px-4 overflow-hidden flex flex-col items-center justify-center min-h-[220px]">


            <div
                class="absolute inset-0 opacity-10 bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:16px_16px] pointer-events-none">
            </div>

            {{-- SVG Ondas de fondo con los colores de tu marca
            <svg class="absolute inset-0 w-full h-full bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:16px_16px] pointer-events-none"
                preserveAspectRatio="none" viewBox="0 0 1440 320" xmlns="http://www.w3.org/2000/svg">
            </svg>

           
                Grupo de líneas Naranja/Rojo (RGB: 233, 79, 53) 
                <path fill="none" stroke="rgb(233, 79, 53)" stroke-width="1.5"
                    d="M0,160 C320,300 420,0 720,120 C1020,240 1120,60 1440,160" opacity="0.8" />
                <path fill="none" stroke="rgb(233, 79, 53)" stroke-width="2"
                    d="M0,180 C300,320 400,20 720,140 C1040,260 1140,80 1440,180" opacity="0.5" />
                <path fill="none" stroke="rgb(233, 79, 53)" stroke-width="1.5"
                    d="M0,140 C340,280 440,-20 720,100 C1000,220 1100,40 1440,140" opacity="0.3" />

                Grupo de líneas Blancas (#ffffff) para contraste 
                <path fill="none" stroke="#ffffff" stroke-width="1.2"
                    d="M0,200 C400,100 500,300 800,200 C1100,100 1200,250 1440,200" opacity="0.3" />
                <path fill="none" stroke="#ffffff" stroke-width="2"
                    d="M0,220 C380,120 480,320 800,220 C1120,120 1220,270 1440,220" opacity="0.2" />
                <path fill="none" stroke="#ffffff" stroke-width="0.4"
                    d="M0,180 C420,80 520,280 800,180 C1080,80 1180,230 1440,180" opacity="0.1" />
           
            --}}

            {{-- Overlay sutil usando el Azul Oscuro para difuminar los bordes --}}
            <div
                class="absolute -bottom-12 -right-12 w-48 h-48 bg-[rgb(233,79,53)] rounded-full blur-3xl opacity-25 pointer-events-none">
            </div>

            {{-- Título
            <div class="relative z-10 w-full max-w-6xl mx-auto text-center flex flex-col gap-3 mb-8">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white uppercase tracking-wide">
                    {{ config('app.name', 'Laravel') }}
                </h1>
            </div>
 --}}
            {{-- Buscador Integrado --}}
            <form action="{{ route('buscador') }}" method="GET"
                class="relative z-20 mx-auto w-full max-w-5xl sm:px-7">
                <div class="w-full flex flex-col lg:flex-row gap-3 lg:items-end">

                    {{-- Input de Búsqueda --}}
                    <div class="flex-1">
                        <div class="relative shadow-lg rounded-xl">
                            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                            </div>
                            <input type="text" name="q" placeholder="Buscar contenido..."
                                class="dark:bg-stone-700 dark:text-white w-full pl-10 pr-3 py-3 
                            text-sm rounded-xl border-none bg-white focus:ring-2 focus:ring-red-700 outline-none transition">
                        </div>
                    </div>

                    {{-- Botones (El primario usa el naranja/rojo) --}}
                    <div class="flex gap-2 w-full lg:w-auto shadow-sm rounded-xl">
                        <flux:button type="submit" variant="primary" size="sm"
                            class="hover:bg-red-900 dark:bg-red-700 w-full inline-flex items-center justify-center gap-1.5 px-6 py-3 text-sm 
                        font-medium rounded-xl h-[44px] bg-[rgb(233,79,53)] hover:bg-opacity-90 text-white transition border-none">
                            <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                        </flux:button>

                        <flux:button href="{{ route('busqueda.avanzada') }}" variant="primary" size="sm"
                            class="dark:bg-stone-700 dark:text-white dark:border-stone-600 dark:hover:bg-stone-600 w-full inline-flex items-center justify-center gap-1.5 h-[44px] px-4 py-3 text-sm font-medium rounded-xl bg-white/10 hover:bg-white/20 text-white border border-white/20 transition backdrop-blur-sm">
                            Búsqueda <br /> avanzada
                        </flux:button>
                    </div>

                </div>
                @php $alcance = request('alcance', 'todos'); @endphp

                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <span class="text-xs uppercase tracking-wide text-white/70">Mostrar:</span>

                    <div> <label class="cursor-pointer">
                            <input type="radio" name="alcance" value="todos" class="peer sr-only"
                                @checked($alcance === 'todos')>
                            <span
                                class="inline-flex items-center rounded-full border border-white/20 px-3 py-1.5 
                                text-xs font-medium text-white transition hover:bg-white/10 
                                peer-checked:border-transparent peer-checked:bg-red-900
                                peer-focus-visible:ring-2 peer-focus-visible:ring-white">
                                Todos los registros
                            </span>
                        </label>

                        <label class="cursor-pointer">
                            <input type="radio" name="alcance" value="digitalizados" class="peer sr-only"
                                @checked($alcance === 'digitalizados')>
                            <span
                                class="inline-flex items-center rounded-full border border-white/20 
                                px-3 py-1.5 text-xs font-medium text-white transition hover:bg-white/10 
                                peer-checked:border-transparent peer-checked:bg-red-900 
                                peer-focus-visible:ring-2 peer-focus-visible:ring-white">
                                Solo con material digitalizado
                            </span>
                        </label>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <div class=" bg-white border-gray-200  dark:bg-neutral-800">
        @yield('content')
    </div>

    <footer class="dark:bg-neutral-600 pb-8">
        <div class="px-4 pt-10 mx-auto max-w-7xl">
            <!-- 1. CAMBIO AQUÍ: Añadimos "justify-between" y "items-start md:items-center" para separar izquierda y derecha -->
            <div
                class="pt-6 border-t border-gray-300 dark:border-neutral-500 flex flex-col md:flex-row justify-between gap-8 items-center md:items-start">

                <!-- BLOQUE IZQUIERDO: Logos y Dirección -->
                <div class="text-black flex flex-col items-center md:items-start text-center md:text-left">
                    <div class="w-full">
                        <div class="flex flex-wrap items-center justify-center md:justify-start gap-4 sm:gap-6">
                            <!-- 1. Logo UdeG -->
                            <a href="https://udg.mx/" target="_blank" rel="noopener noreferrer">
                                <img src="{{ asset('img/udeg_negro.svg') }}" alt="Universidad de Guadalajara"
                                    class="block dark:hidden h-13 w-auto object-contain">
                                <img src="{{ asset('img/udeg_blanco.svg') }}" alt="Universidad de Guadalajara"
                                    class="hidden dark:block h-13 w-auto object-contain">
                            </a>

                            <span class="hidden sm:block h-8 w-px bg-gray-300 dark:bg-gray-500"
                                aria-hidden="true"></span>

                            <!-- 2. Logo SIUBIUDG -->
                            <a href="https://bibliotecas.udg.mx/" target="_blank" rel="noopener noreferrer">
                                <img src="{{ asset('img/siubi_negro.svg') }}"
                                    alt="Sistema Universitario de Bibliotecas"
                                    class="block dark:hidden h-12 w-auto object-contain">
                                <img src="{{ asset('img/siubi_blanco.svg') }}"
                                    alt="Sistema Universitario de Bibliotecas"
                                    class="hidden dark:block h-12 w-auto object-contain">
                            </a>

                            <span class="hidden sm:block h-8 w-px bg-gray-300 dark:bg-gray-500"
                                aria-hidden="true"></span>

                            <!-- 3. Logo BPEJ -->
                            <a href="https://bpej.udg.mx/" target="_blank" rel="noopener noreferrer">
                                <img src="{{ asset('img/bpej.svg') }}" alt="Biblioteca Pública del Estado de Jalisco"
                                    class="block dark:hidden h-8 sm:h-10 w-auto object-contain">
                                <img src="{{ asset('img/bpej-oscuro.svg') }}"
                                    alt="Biblioteca Pública del Estado de Jalisco"
                                    class="hidden dark:block h-8 sm:h-10 w-auto object-contain">
                            </a>
                        </div>
                    </div>

                    <p class="mt-4 text-sm text-gray-500 dark:text-zinc-200 leading-relaxed">
                        Biblioteca Pública del Estado de Jalisco "Juan José Arreola" <br>
                        Periférico Norte Manuel Gómez Morín no. 1695, Colonia Belenes C.P. 45150 <br>
                        Zapopan, Jalisco, México. <br>
                        Teléfono 33 3836 4530
                    </p>
                </div>

                <!-- 2. BLOQUE DERECHO: Redes Sociales -->
                <div class="flex flex-col items-center md:items-end justify-center shrink-0">
                    <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-300 mb-3">
                        Síguenos en redes
                    </span>

                    <div class="flex items-center gap-3">
                        <!-- Facebook -->
                        <a href="https://es-la.facebook.com/bibliojalisco/" target="_blank" rel="noopener noreferrer"
                            aria-label="Facebook"
                            class="p-2.5 rounded-xl border border-zinc-200 dark:border-neutral-500 bg-white dark:bg-neutral-700 text-zinc-600 dark:text-zinc-200 hover:bg-[#86212b] hover:border-[#86212b] hover:text-white transition-all duration-300 shadow-sm">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                <path
                                    d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" />
                            </svg>
                        </a>

                        <!-- X (Twitter) -->
                        <a href="https://twitter.com/BiblioJalisco" target="_blank" rel="noopener noreferrer"
                            aria-label="X"
                            class="p-2.5 rounded-xl border border-zinc-200 dark:border-neutral-500 bg-white dark:bg-neutral-700 text-zinc-600 dark:text-zinc-200 hover:bg-[#86212b] hover:border-[#86212b] hover:text-white transition-all duration-300 shadow-sm">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                <path
                                    d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                            </svg>
                        </a>

                        <!-- Instagram -->
                        <a href="https://www.instagram.com/biblio_jalisco" target="_blank" rel="noopener noreferrer"
                            aria-label="Instagram"
                            class="p-2.5 rounded-xl border border-zinc-200 dark:border-neutral-500 bg-white dark:bg-neutral-700 text-zinc-600 dark:text-zinc-200 hover:bg-[#86212b] hover:border-[#86212b] hover:text-white transition-all duration-300 shadow-sm">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                <path
                                    d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                            </svg>
                        </a>

                        <!-- YouTube -->
                        <a href="https://www.youtube.com/channel/UCh_wEI5dQ6WEBo7h_BJ1emw" target="_blank" rel="noopener noreferrer"
                            aria-label="YouTube"
                            class="p-2.5 rounded-xl border border-zinc-200 dark:border-neutral-500 bg-white dark:bg-neutral-700 text-zinc-600 dark:text-zinc-200 hover:bg-[#86212b] hover:border-[#86212b] hover:text-white transition-all duration-300 shadow-sm">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                <path
                                    d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" />
                            </svg>
                        </a>
                    </div>
                </div>

            </div>

            <!-- Copyright y Aviso de Privacidad -->
            <div class="flex flex-col pt-10">
                <p class="text-center">
                    <span class="mx-auto text-sm md:text-base text-gray-500 dark:text-white">
                        Derechos reservados ©1997 - {{ date('Y') }}. Universidad de Guadalajara |
                        <a href="https://transparencia.udg.mx/aviso-confidencialidad-integral" target="_blank"
                            rel="noopener noreferrer"
                            class="hover:text-[#86212b] dark:hover:text-amber-400 underline-offset-4 hover:underline transition-colors">
                            Aviso de privacidad
                        </a>
                    </span>
                </p>
            </div>
        </div>
    </footer>
    @fluxScripts

    @livewireScripts

    @yield('js')

    <x-toast />

    <x-announcement-popup title="Aviso importante" storage-key="hide_arreola_aviso">
        <p>
            Arreola Histórica es un repositorio en construcción. En esta primera etapa, ponemos a tu disposición un
            buscador que permite la exploración de una parte de los materiales antiguos que se resguardan en nuestra
            Biblioteca. Sólo un porcentaje de estos registros ya puede ser consultado en línea. Todos los días
            trabajamos para seguir alimentando nuestro repositorio.
        </p>
    </x-announcement-popup>

</body>

</html>
