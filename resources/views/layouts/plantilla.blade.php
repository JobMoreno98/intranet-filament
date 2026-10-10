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
    <script>
        let theme = 'light';

        // 2. LocalStorage: ¿El usuario ya eligió algo antes?
        if (localStorage.getItem('theme')) {
            theme = localStorage.getItem('theme');
        }
        // 3. Sistema: Si no hay LocalStorage, ¿qué prefiere su sistema operativo?
        else if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
            theme = 'dark';
        }

        // Finalmente, aplicamos el resultado de ese análisis a la página
        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link href="https://fonts.googleapis.com/css2?family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700&display=swap"
        rel="stylesheet">

    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/vnd.microsoft.icon" />

    <style>
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

    <nav x-data="{ mobileMenuOpen: false }" class="bg-white dark:bg-neutral-600">

        <div class="flex items-center justify-between w-full h-16 px-4 mx-auto">

            <a href="{{ route('home') }}" class="flex items-center font-bold text-xl h-full py-2">

                <!-- SVG Claro: Se oculta cuando el modo oscuro está activo -->
                <img src="{{ asset('img/arreola-blanco.svg') }}" alt="{{ config('app.name', 'Laravel') }} Logo"
                    class="block dark:hidden h-6 mr-3 sm:h-9">

                <!-- SVG Oscuro: Permanece oculto hasta que el modo oscuro se activa -->
                <img src="{{ asset('img/arreola-oscuro.svg') }}" alt="{{ config('app.name', 'Laravel') }} Logo"
                    class="hidden dark:block h-6 mr-3 sm:h-9">


                <img src="{{ asset('img/bpej.svg') }}" alt="{{ config('app.name', 'Laravel') }} Logo"
                    class="block dark:hidden h-4 mr-3 sm:h-6">

                <!-- SVG Oscuro: Permanece oculto hasta que el modo oscuro se activa -->
                <img src="{{ asset('img/bpej-oscuro.svg') }}" alt="{{ config('app.name', 'Laravel') }} Logo"
                    class="hidden dark:block h-4 mr-3 sm:h-6">

                {{-- 
                <span class="dark:text-white text-guinda">{{ config('app.name', 'Laravel') }}</span>
 --}}

            </a>

            <div class="hidden lg:flex items-center h-full ms-6 space-x-1">
                <a href="{{ route('home') }}"
                    class="h-full flex items-center px-4 text-zinc-700 dark:text-zinc-200 hover:bg-guinda hover:text-white transition-colors duration-200">Inicio</a>
                <a href="{{ route('areas.index') }}"
                    class="h-full flex items-center px-4 text-zinc-700 dark:text-zinc-200 hover:bg-guinda hover:text-white transition-colors duration-200">Áreas</a>
                <a href="{{ route('fondos.index') }}"
                    class="h-full flex items-center px-4 text-zinc-700 dark:text-zinc-200 hover:bg-guinda hover:text-white transition-colors duration-200">Fondos</a>
                <a href="{{ route('blog.index') }}"
                    class="h-full flex items-center px-4 text-zinc-700 dark:text-zinc-200 hover:bg-guinda hover:text-white transition-colors duration-200">Blog</a>
            </div>

            <div class="flex items-center gap-2 md:gap-4">

                <div x-data="{
                    darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)
                }" x-init="darkMode ? document.documentElement.classList.add('dark') : document.documentElement.classList.remove('dark');
                $watch('darkMode', val => {
                    val ? document.documentElement.classList.add('dark') : document.documentElement.classList.remove('dark');
                    localStorage.setItem('theme', val ? 'dark' : 'light');
                });" class="flex items-center">
                    <button @click="darkMode = !darkMode" type="button"
                        class="p-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition shadow-sm group">
                        <span x-show="!darkMode" x-cloak>
                            <x-heroicon-o-moon class="w-5 h-5 group-hover:scale-110 transition-transform" />
                        </span>
                        <span x-show="darkMode" x-cloak>
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

                <button @click="mobileMenuOpen = !mobileMenuOpen" type="button"
                    class="inline-flex lg:hidden items-center p-2 text-sm text-gray-500 rounded-lg hover:bg-gray-100 focus:outline-none dark:text-gray-400 dark:hover:bg-gray-700"
                    aria-controls="mobile-menu" :aria-expanded="mobileMenuOpen">
                    <span class="sr-only">Abrir menú principal</span>

                    <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                            d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                            clip-rule="evenodd"></path>
                    </svg>

                    <svg x-show="mobileMenuOpen" x-cloak class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        </div>

        <div x-show="mobileMenuOpen" x-transition x-cloak
            class="lg:hidden w-full bg-white dark:bg-neutral-600 border-t border-gray-200 dark:border-neutral-700 pb-4">
            <ul class="flex flex-col font-medium px-4 pt-2 space-y-1">
                <li>
                    <a href="{{ route('home') }}"
                        class="block py-2 px-3 rounded-md text-zinc-700 dark:text-zinc-200 hover:bg-guinda hover:text-white transition-colors duration-200">Inicio</a>
                    <a href="{{ route('fondos.index') }}"
                        class="block py-2 px-3 rounded-md text-zinc-700 dark:text-zinc-200 hover:bg-guinda hover:text-white transition-colors duration-200">Fondos</a>
                    <a href="{{ route('blog.index') }}"
                        class="block py-2 px-3 rounded-md text-zinc-700 dark:text-zinc-200 hover:bg-guinda hover:text-white transition-colors duration-200">Blog</a>
                </li>

                <li class="pt-4 mt-2 border-t border-gray-200 dark:border-neutral-700">
                    @if (Auth::check())
                        <div class="px-3">
                            <x-desktop-user-menu />
                        </div>
                    @else
                        <div class="flex flex-col gap-2 px-3">
                            <a href="{{ route('login') }}"
                                class="text-center rounded-md text-white py-2 px-4 bg-red-800 hover:bg-red-900 transition shadow-sm w-full">
                                {{ __('Log In') }}
                            </a>
                        </div>
                    @endif
                </li>
            </ul>
        </div>
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

            {{-- Título --}}
            <div class="relative z-10 w-full max-w-6xl mx-auto text-center flex flex-col gap-3 mb-8">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white uppercase tracking-wide">
                    {{ config('app.name', 'Laravel') }}
                </h1>
            </div>

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
                                class="dark:bg-stone-700 dark:text-white w-full pl-10 pr-3 py-3 text-sm rounded-xl border-none bg-white focus:ring-2 focus:ring-red-700 outline-none transition">
                        </div>
                    </div>

                    {{-- Botones (El primario usa el naranja/rojo) --}}
                    <div class="flex gap-2 w-full lg:w-auto shadow-sm rounded-xl">
                        <flux:button type="submit" variant="primary" size="sm"
                            class="hover:bg-red-900 dark:bg-red-700 w-full inline-flex items-center justify-center gap-1.5 px-6 py-3 text-sm font-medium rounded-xl h-[44px] bg-[rgb(233,79,53)] hover:bg-opacity-90 text-white transition border-none">
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

    {{--
    <section class="px-6 md:px-2">
        <div data-aos="fade-up"
            class="relative mx-auto sm:px-7 px-4 max-w-screen-xl py-10 flex items-center aos-init aos-animate">
            <img src="./imgs/hero.png" class="mx-auto w-full max-w-[1000px] rounded shadow-2xl" alt="">
        </div>
    </section>
    <section class="bg-gray-50">
        <div class="flex flex-col gap-8 mx-auto sm:px-7 px-4 max-w-screen-xl py-20 mt-20">
            <div class="flex flex-col gap-4">
                <h2 class="text-4xl font-bold text-center">
                    Lorem ipsum dolor
                </h2>
                <p class="text-base md:text-lg text-center">
                    Lorem ipsum dolor sit, amet consectetur adipisicing elit. Neque autem eos magnam alias, dolorum...
                </p>
            </div>
            <ul class="flex flex-row gap-5 justify-center flex-wrap mx-auto items-center">
                <li>
                    <img class="grayscale max-w-[140px]"
                        src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2f/Google_2015_logo.svg/1200px-Google_2015_logo.svg.png"
                        alt="">
                </li>
                <li>
                    <img class="grayscale max-w-[140px]"
                        src="https://upload.wikimedia.org/wikipedia/commons/thumb/1/1a/Coinbase.svg/2560px-Coinbase.svg.png"
                        alt="">
                </li>
                <li>
                    <img class="grayscale max-w-[140px]"
                        src="https://upload.wikimedia.org/wikipedia/commons/thumb/b/ba/Stripe_Logo%2C_revised_2016.svg/2560px-Stripe_Logo%2C_revised_2016.svg.png"
                        alt="">
                </li>
                <li>
                    <img class="grayscale max-w-[140px]"
                        src="https://upload.wikimedia.org/wikipedia/commons/thumb/3/37/Firebase_Logo.svg/1280px-Firebase_Logo.svg.png"
                        alt="">
                </li>
                <li>
                    <img class="grayscale max-w-[140px]"
                        src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2f/Google_2015_logo.svg/1200px-Google_2015_logo.svg.png"
                        alt="">
                </li>
                <li>
                    <img class="grayscale max-w-[140px]"
                        src="https://upload.wikimedia.org/wikipedia/commons/thumb/1/1a/Coinbase.svg/2560px-Coinbase.svg.png"
                        alt="">
                </li>
                <li>
                    <img class="grayscale max-w-[140px]"
                        src="https://upload.wikimedia.org/wikipedia/commons/thumb/b/ba/Stripe_Logo%2C_revised_2016.svg/2560px-Stripe_Logo%2C_revised_2016.svg.png"
                        alt="">
                </li>
                <li>
                    <img class="grayscale max-w-[140px]"
                        src="https://upload.wikimedia.org/wikipedia/commons/thumb/3/37/Firebase_Logo.svg/1280px-Firebase_Logo.svg.png"
                        alt="">
                </li>
                <li>
                    <img class="grayscale max-w-[140px]"
                        src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2f/Google_2015_logo.svg/1200px-Google_2015_logo.svg.png"
                        alt="">
                </li>
                <li>
                    <img class="grayscale max-w-[140px]"
                        src="https://upload.wikimedia.org/wikipedia/commons/thumb/1/1a/Coinbase.svg/2560px-Coinbase.svg.png"
                        alt="">
                </li>
                <li>
                    <img class="grayscale max-w-[140px]"
                        src="https://upload.wikimedia.org/wikipedia/commons/thumb/b/ba/Stripe_Logo%2C_revised_2016.svg/2560px-Stripe_Logo%2C_revised_2016.svg.png"
                        alt="">
                </li>
            </ul>
        </div>
    </section>
    <section>
        <div class="mx-auto sm:px-7 px-4 max-w-screen-xl py-20 flex flex-col gap-10">
            <div class="flex flex-col gap-4">
                <h2 class="text-4xl font-bold text-center">
                    Lorem ipsum dolor
                </h2>
                <p class="text-base md:text-lg text-center max-w-[600px] mx-auto">
                    Lorem ipsum dolor sit, amet consectetur adipisicing elit. Neque autem eos magnam alias, dolorum...
                </p>
            </div>
            <div class="flex flex-row gap-5 justify-center flex-wrap mx-auto items-center">
                <div class="flex flex-col text-center max-w-[340px]">
                    <div
                        class="border-2 border-orange-50 bg-orange-100 mx-auto rounded-full text-orange-600 w-12 h-12 flex">
                        <i class="bi bi-badge-8k-fill text-2xl my-auto mx-auto"></i>
                    </div>
                    <h2 class="mt-4 text-xl font-semibold text-gray-800">Lorem ipsum et</h2>
                    <p class="mt-2 text-gray-500 text-sm">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Dignissim
                        fusce tortor, ac sed malesuada blandit. Et mi gravida sem feugiat.
                    </p>
                </div>
                <div class="flex flex-col text-center max-w-[340px]">
                    <div
                        class="border-2 border-orange-50 bg-orange-100 mx-auto rounded-full text-orange-600 w-12 h-12 flex">
                        <i class="bi bi-badge-8k-fill text-2xl my-auto mx-auto"></i>
                    </div>
                    <h2 class="mt-4 text-xl font-semibold text-gray-800">Fully ready</h2>
                    <p class="mt-2 text-gray-500 text-sm">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Dignissim
                        fusce tortor, ac sed malesuada blandit. Et mi gravida sem feugiat.
                    </p>
                </div>
                <div class="flex flex-col text-center max-w-[340px]">
                    <div
                        class="border-2 border-orange-50 bg-orange-100 mx-auto rounded-full text-orange-600 w-12 h-12 flex">
                        <i class="bi bi-badge-8k-fill text-2xl my-auto mx-auto"></i>
                    </div>
                    <h2 class="mt-4 text-xl font-semibold text-gray-800">Super yes</h2>
                    <p class="mt-2 text-gray-500 text-sm">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Dignissim
                        fusce tortor, ac sed malesuada blandit. Et mi gravida sem feugiat.
                    </p>
                </div>
                <div class="flex flex-col text-center max-w-[340px]">
                    <div
                        class="border-2 border-orange-50 bg-orange-100 mx-auto rounded-full text-orange-600 w-12 h-12 flex">
                        <i class="bi bi-badge-8k-fill text-2xl my-auto mx-auto"></i>
                    </div>
                    <h2 class="mt-4 text-xl font-semibold text-gray-800">Lorem ipsum et</h2>
                    <p class="mt-2 text-gray-500 text-sm">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Dignissim
                        fusce tortor, ac sed malesuada blandit. Et mi gravida sem feugiat.
                    </p>
                </div>
                <div class="flex flex-col text-center max-w-[340px]">
                    <div
                        class="border-2 border-orange-50 bg-orange-100 mx-auto rounded-full text-orange-600 w-12 h-12 flex">
                        <i class="bi bi-badge-8k-fill text-2xl my-auto mx-auto"></i>
                    </div>
                    <h2 class="mt-4 text-xl font-semibold text-gray-800">Fully ready</h2>
                    <p class="mt-2 text-gray-500 text-sm">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Dignissim
                        fusce tortor, ac sed malesuada blandit. Et mi gravida sem feugiat.
                    </p>
                </div>
                <div class="flex flex-col text-center max-w-[340px]">
                    <div
                        class="border-2 border-orange-50 bg-orange-100 mx-auto rounded-full text-orange-600 w-12 h-12 flex">
                        <i class="bi bi-badge-8k-fill text-2xl my-auto mx-auto"></i>
                    </div>
                    <h2 class="mt-4 text-xl font-semibold text-gray-800">Super yes</h2>
                    <p class="mt-2 text-gray-500 text-sm">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Dignissim
                        fusce tortor, ac sed malesuada blandit. Et mi gravida sem feugiat.
                    </p>
                </div>
            </div>
        </div>
    </section>


    <section>
        <div class="px-2 py-20 mx-auto max-w-7xl">
            <div class="grid grid-cols-1 gap-6 lg:gap-12 lg:grid-cols-3">
                <div class="text-center lg:text-left">
                    <div>
                        <h2 class="text-2xl font-bold">
                            Lorem ipsum dolor sit
                        </h2>
                        <p class="text-gray-600 text-base">
                            Dolorem cupiditate voluptatem veniam reprehenderit.
                        </p>
                    </div>
                </div>
                <div class="relative w-full mx-auto font-normal lg:col-span-2" x-data="{
                    activeAccordion: '',
                    setActiveAccordion(id) {
                        this.activeAccordion = (this.activeAccordion == id) ? '' : id
                    }
                }">
                    <div class="cursor-pointer group text-gray-600 hover:text-zinc-500"
                        x-data="{ id: $id('accordion') }" :class="{
                            'text-zinc-900': activeAccordion ==
                                id,
                            'text-gray-600 hover:text-zinc-500': activeAccordion != id
                        }">
                        <button
                            class="flex items-center justify-between w-full p-4 pb-1 text-sm text-left select-none lg:text-base"
                            @click="setActiveAccordion(id)">
                            <span>How does bookme work?</span>
                            <svg class="w-5 h-5 duration-300 ease-out text-zinc-500" fill="none" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg" stroke="currentColor" stroke-width="1.5"
                                :class="{ '-rotate-[45deg]': activeAccordion == id }">
                                <path d="M12 6v12m6-6H6" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </button>
                        <div x-collapse="" x-show="activeAccordion==id"
                            style="height: 0px; overflow: hidden; display: none;" hidden="">
                            <div class="p-4 pt-2 text-sm text-gray-500">
                                Lorem ipsum dolor sit amet consectetur, adipisicing elit. Excepturi laboriosam vitae
                                reiciendis corrupti! Explicabo, libero reiciendis iste ipsa voluptatem, cupiditate ab
                                culpa expedita asperiores at quasi laborum tempore magnam corrupti.
                            </div>
                        </div>
                    </div>
                    <div class="text-gray-600 cursor-pointer group hover:text-zinc-500"
                        x-data="{ id: $id('accordion') }" :class="{
                            'text-zinc-900': activeAccordion ==
                                id,
                            'text-gray-600 hover:text-zinc-500': activeAccordion != id
                        }">
                        <button
                            class="flex items-center justify-between w-full p-4 pb-1 text-sm text-left select-none lg:text-base"
                            @click="setActiveAccordion(id)">
                            <span>What type of bookme?</span>
                            <svg class="w-5 h-5 duration-300 ease-out text-zinc-500" fill="none" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg" stroke="currentColor" stroke-width="1.5"
                                :class="{ '-rotate-[45deg]': activeAccordion == id }">
                                <path d="M12 6v12m6-6H6" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </button>
                        <div x-collapse="" x-show="activeAccordion==id"
                            style="display: none; height: 0px; overflow: hidden;" hidden="">
                            <div class="p-4 pt-2 text-sm text-gray-500">
                                Lorem ipsum, dolor sit amet consectetur adipisicing elit. Nostrum ratione expedita
                                possimus inventore in. Explicabo, quasi repellendus a dolore totam laudantium, molestias
                                quis reiciendis rerum vero, numquam sint earum voluptatum?
                            </div>
                        </div>
                    </div>
                    <div class="text-gray-600 cursor-pointer group hover:text-zinc-500"
                        x-data="{ id: $id('accordion') }" :class="{
                            'text-zinc-900': activeAccordion ==
                                id,
                            'text-gray-600 hover:text-zinc-500': activeAccordion != id
                        }">
                        <button
                            class="flex items-center justify-between w-full p-4 pb-1 text-sm text-left select-none lg:text-base"
                            @click="setActiveAccordion(id)">
                            <span>Can I cancel my subscription?</span>
                            <svg class="w-5 h-5 duration-300 ease-out text-zinc-500" fill="none" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg" stroke="currentColor" stroke-width="1.5"
                                :class="{ '-rotate-[45deg]': activeAccordion == id }">
                                <path d="M12 6v12m6-6H6" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </button>
                        <div x-collapse="" x-show="activeAccordion==id"
                            style="display: none; height: 0px; overflow: hidden;" hidden="">
                            <div class="p-4 pt-2 text-sm text-gray-500">
                                Lorem ipsum dolor sit amet consectetur adipisicing elit. Ipsum ex cum repellendus
                                eligendi maxime magni molestiae saepe beatae, excepturi id dolores recusandae ea
                                perferendis repudiandae? Ea deleniti blanditiis quae iusto!
                            </div>
                        </div>
                    </div>
                    <div class="text-gray-600 cursor-pointer group hover:text-zinc-500"
                        x-data="{ id: $id('accordion') }" :class="{
                            'text-zinc-900': activeAccordion ==
                                id,
                            'text-gray-600 hover:text-zinc-500': activeAccordion != id
                        }">
                        <button
                            class="flex items-center justify-between w-full p-4 pb-1 text-sm text-left select-none lg:text-base"
                            @click="setActiveAccordion(id)">
                            <span>is bookme safe?</span>
                            <svg class="w-5 h-5 duration-300 ease-out text-zinc-500" fill="none" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg" stroke="currentColor" stroke-width="1.5"
                                :class="{ '-rotate-[45deg]': activeAccordion == id }">
                                <path d="M12 6v12m6-6H6" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </button>
                        <div x-collapse="" x-show="activeAccordion==id"
                            style="display: none; height: 0px; overflow: hidden;" hidden="">
                            <div class="p-4 pt-2 text-sm text-gray-500">
                                Lorem ipsum dolor sit, amet consectetur adipisicing elit. Placeat aspernatur iste ab
                                optio magni veritatis voluptatibus quaerat. Error ipsam dolore, explicabo consequatur
                                incidunt rem culpa, est corporis nihil necessitatibus cum!
                            </div>
                        </div>
                    </div>
                    <div class="text-gray-600 cursor-pointer group hover:text-zinc-500"
                        x-data="{ id: $id('accordion') }" :class="{
                            'text-zinc-900': activeAccordion ==
                                id,
                            'text-gray-600 hover:text-zinc-500': activeAccordion != id
                        }">
                        <button
                            class="flex items-center justify-between w-full p-4 pb-1 text-sm text-left select-none lg:text-base"
                            @click="setActiveAccordion(id)">
                            <span>Can I use 2 account?</span>
                            <svg class="w-5 h-5 duration-300 ease-out text-zinc-500" fill="none" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg" stroke="currentColor" stroke-width="1.5"
                                :class="{ '-rotate-[45deg]': activeAccordion == id }">
                                <path d="M12 6v12m6-6H6" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </button>
                        <div x-collapse="" x-show="activeAccordion==id"
                            style="display: none; height: 0px; overflow: hidden;" hidden="">
                            <div class="p-4 pt-2 text-sm text-gray-500">
                                Lorem ipsum dolor, sit amet consectetur adipisicing elit. Est placeat veritatis ex
                                perspiciatis, incidunt, quos quia accusantium recusandae aspernatur inventore nulla,
                                esse aut expedita numquam rem! Quibusdam deleniti magni rerum.
                            </div>
                        </div>
                    </div>
                    <div class="text-gray-600 cursor-pointer group hover:text-zinc-500"
                        x-data="{ id: $id('accordion') }" :class="{
                            'text-zinc-900': activeAccordion ==
                                id,
                            'text-gray-600 hover:text-zinc-500': activeAccordion != id
                        }">
                        <button
                            class="flex items-center justify-between w-full p-4 pb-1 text-sm text-left select-none lg:text-base"
                            @click="setActiveAccordion(id)">
                            <span>Is bookme a free service?</span>
                            <svg class="w-5 h-5 duration-300 ease-out text-zinc-500" fill="none" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg" stroke="currentColor" stroke-width="1.5"
                                :class="{ '-rotate-[45deg]': activeAccordion == id }">
                                <path d="M12 6v12m6-6H6" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </button>
                        <div x-collapse="" x-show="activeAccordion==id"
                            style="display: none; height: 0px; overflow: hidden;" hidden="">
                            <div class="p-4 pt-2 text-sm text-gray-500">
                                Lorem ipsum dolor sit amet consectetur adipisicing elit. Ut, consequuntur optio tempore
                                voluptatibus quae commodi voluptate excepturi pariatur vitae odit eius placeat dicta
                                cum, culpa quod similique, incidunt delectus est.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    --}}

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
                {{--
                <nav class="ml-0 md:ml-auto">
                    <ul class="flex flex-row gap-4">
                        <li>
                            <a href="" class="hover:text-orange-500">Inicio</a>
                        </li>
                        <li>
                            <a href="" class="hover:text-orange-500">Acerca</a>
                        </li>
                        <li>
                            <a href="" class="hover:text-orange-500">Acer</a>
                        </li>
                        <li>
                            <a href="" class="hover:text-orange-500">Demo</a>
                        </li>
                    </ul>
                </nav>
                --}}
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
    <script src="https://unpkg.com/flowbite@1.4.1/dist/flowbite.js"></script>
    @fluxScripts
    @livewireScripts
    @yield('js')

</body>

</html>
