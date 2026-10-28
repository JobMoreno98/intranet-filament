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
            ['NUESTROS FONDOS HISTÓRICOS','fondos.historicos'],
            ['ARREOLA HISTÓRICA','arreola.historica'],
            ['ÁREAS', 'areas.index'],
            ['FODNOS', 'fondos.index'],
            ['SALA ARREOLA (BLOG)', 'blog.index'],
        ];
    @endphp

    <nav x-data="{ menuOpen: false }" @keydown.escape.window="menuOpen = false"
        x-effect="document.body.classList.toggle('overflow-hidden', menuOpen)" class="bg-white dark:bg-neutral-600">

        <div class="flex flex-wrap items-center justify-between w-full min-h-16 gap-x-4 gap-y-2 px-4 py-2 mx-auto">

            <a href="{{ route('home') }}" class="flex items-center font-bold text-xl shrink-0">

                <!-- SVG Claro: Se oculta cuando el modo oscuro está activo -->
                <img src="{{ asset('img/arreola-blanco.svg') }}" alt="{{ config('app.name', 'Laravel') }} Logo"
                    class="block dark:hidden h-15 mr-3 sm:h-15">

                <!-- SVG Oscuro: Permanece oculto hasta que el modo oscuro se activa -->
                <img src="{{ asset('img/arreola-oscuro.svg') }}" alt="{{ config('app.name', 'Laravel') }} Logo"
                    class="hidden dark:block h-15 mr-3 sm:h-15">

                <img src="{{ asset('img/bpej.svg') }}" alt="{{ config('app.name', 'Laravel') }} Logo"
                    class="block dark:hidden h-8 mr-3 sm:h-8">

                <img src="{{ asset('img/bpej-oscuro.svg') }}" alt="{{ config('app.name', 'Laravel') }} Logo"
                    class="hidden dark:block h-8 mr-3 sm:h-8">

            </a>

            {{-- Texto siempre visible: en móvil baja a su propia fila (order-last), en lg va entre logo y botones --}}
            <p
                class="order-last w-full text-center text-md leading-snug text-zinc-700 dark:text-zinc-200 lg:order-none lg:w-auto lg:flex-1 lg:min-w-0">
                Arreola Histórica es el buscador y repositorio digital de los acervos históricos de la Biblioteca
                Pública del Estado de Jalisco de la Universidad de Guadalajara.
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

                <div class="hidden lg:flex items-center h-full">
                    @if (Auth::check())
                        <x-desktop-user-menu />
                    @else
                        <a href="{{ route('login') }}"
                            class="inline-flex items-center text-sm font-medium rounded-md bg-red-800 hover:bg-red-900 text-white transition shadow-sm h-9 px-4">
                            {{ __('Log In') }}
                        </a>
                    @endif
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
                                class="block rounded-md px-3 py-2.5 transition-colors duration-200 hover:bg-guinda hover:text-white
                    {{ request()->routeIs($ruta) ? 'bg-guinda text-white' : 'text-zinc-700 dark:text-zinc-200' }}">
                                {{ $etiqueta }}
                            </a>
                        </li>
                    @endforeach
                </ul>

                {{-- Sesión: debajo de lg (en lg ya está en la barra). mt-auto la empuja al fondo del cajón --}}
                <div class="mt-auto border-t border-gray-200 p-4 dark:border-neutral-700 lg:hidden">
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

            {{-- SVG Ondas de fondo con los colores de tu marca --}}
            <svg class="absolute inset-0 w-full h-full object-cover opacity-70 pointer-events-none"
                preserveAspectRatio="none" viewBox="0 0 1440 320" xmlns="http://www.w3.org/2000/svg">

                {{-- Grupo de líneas Naranja/Rojo (RGB: 233, 79, 53) --}}
                <path fill="none" stroke="rgb(233, 79, 53)" stroke-width="1.5"
                    d="M0,160 C320,300 420,0 720,120 C1020,240 1120,60 1440,160" opacity="0.8" />
                <path fill="none" stroke="rgb(233, 79, 53)" stroke-width="2"
                    d="M0,180 C300,320 400,20 720,140 C1040,260 1140,80 1440,180" opacity="0.5" />
                <path fill="none" stroke="rgb(233, 79, 53)" stroke-width="1.5"
                    d="M0,140 C340,280 440,-20 720,100 C1000,220 1100,40 1440,140" opacity="0.3" />

                {{-- Grupo de líneas Blancas (#ffffff) para contraste --}}
                <path fill="none" stroke="#ffffff" stroke-width="1.2"
                    d="M0,200 C400,100 500,300 800,200 C1100,100 1200,250 1440,200" opacity="0.3" />
                <path fill="none" stroke="#ffffff" stroke-width="2"
                    d="M0,220 C380,120 480,320 800,220 C1120,120 1220,270 1440,220" opacity="0.2" />
                <path fill="none" stroke="#ffffff" stroke-width="0.4"
                    d="M0,180 C420,80 520,280 800,180 C1080,80 1180,230 1440,180" opacity="0.1" />
            </svg>

            {{-- Overlay sutil usando el Azul Oscuro para difuminar los bordes --}}
            <div
                class="absolute inset-0 bg-gradient-to-r from-[#131f30] via-transparent to-[#131f30] opacity-90 pointer-events-none">
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
            </form>
        </div>
    </section>

    <div class=" bg-white border-gray-200  dark:bg-neutral-800">
        @yield('content')
    </div>

    <footer class="dark:bg-neutral-600">
        <div class="px-2 pt-10 mx-auto max-w-7xl ">
            <div class="pt-12 border-t border-gray-300 flex flex-col md:flex-row gap-10 items-center ">
                <div class="text-black flex flex-col ">
                    <a href="{{ route('home') }}" class="font-bold text-xl"
                        style="display: flex; align-items: center; gap: 8px;">
                        <img src="{{ asset('img/bpej.svg') }}" alt="{{ config('app.name', 'Laravel') }} Logo"
                            class="block dark:hidden h-4 mr-3 sm:h-8">

                        <!-- SVG Oscuro: Permanece oculto hasta que el modo oscuro se activa -->
                        <img src="{{ asset('img/bpej-oscuro.svg') }}" alt="{{ config('app.name', 'Laravel') }} Logo"
                            class="hidden dark:block h-4 mr-3 sm:h-8">

                    </a>
                    <p class="mt-2 text-sm text-gray-500 lg:w-4/5 dark:text-white">
                        Biblioteca Pública del Estado de Jalisco "Juan José Arreola" <br>
                        Periférico Norte Manuel Gómez Morín no. 1695, Colonia Belenes C.P. 45150 <br>
                        Zapopan, Jalisco, México. <br>
                        Teléfono 33 3836 4530
                    </p>
                </div>
            </div>

            <div class="flex flex-col pt-12 ">
                <p class="text-center">
                    <span class="mx-auto mt-2 text-sm md:text-base text-gray-500 lg:mx-0 dark:text-white">
                        Derechos reservados ©1997 - 2025. Universidad de Guadalajara |
                        <a href="https://transparencia.udg.mx/aviso-confidencialidad-integral" target="_blank"
                            rel="noopener noreferrer" class="  hover:text-red-800">
                            Aviso de privacidad</a>

                    </span>
                </p>
            </div>

        </div>
    </footer>

    @fluxScripts
    @livewireScripts
    @yield('js')

</body>

</html>
