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
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia(
                '(prefers-color-scheme: dark)').matches)) {
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
        :root {
            font-family: 'Inter', sans-serif;
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
            /* coloca aquí la ruta */
            background-size: cover;
            /* equivalente a object-fit: cover */
            background-position: center;
            /* centra la imagen */
            max-height: 320px;
            width: 100%;
        }

        #indicatorDesktop {
            transform: translateX(calc(var(--active-index, 0) * var(--tab-width)));
            transition: transform 0.5s cubic-bezier(0.68, -0.55, 0.26, 1.55);
        }

        #indicatorMoviile {
            transform: translateX(calc(var(--active-index, 0) * var(--tab-width)));
            transition: transform 0.5s cubic-bezier(0.68, -0.55, 0.26, 1.55);
        }

        .scrollbar-none::-webkit-scrollbar {
            display: none;
        }

        .scrollbar-none {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>

    @fluxAppearance
    @livewireStyles
    @yield('css')


</head>

<body class="antialiased">

    <nav class="bg-white border-b border-gray-200 dark:border-neutral-700 h-16 dark:bg-neutral-600">
        <div class="flex items-center justify-between w-full h-full px-4 mx-auto">

            <div class="flex items-center h-full w-full md:w-auto">
                <div class="flex flex-row justify-between items-center w-full h-full">
                    <a href="{{ route('home') }}" class="flex items-center font-bold text-xl h-full py-2">
                        <img src="{{ asset('img/logo.svg') }}" class="h-6 mr-3 sm:h-9"
                            alt="{{ config('app.name', 'Laravel') }} Logo">
                        <span style="color:#86212b;">{{ config('app.name', 'Laravel') }}</span>
                    </a>

                    <div class="flex items-center md:hidden gap-2">
                        <div x-data="{
                            darkMode: localStorage.getItem('theme') === 'dark' ||
                                (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)
                        }" x-init="darkMode ? document.documentElement.classList.add('dark') : document.documentElement.classList.remove('dark');
                        $watch('darkMode', val => {
                            val ? document.documentElement.classList.add('dark') : document.documentElement.classList.remove('dark');
                            localStorage.setItem('theme', val ? 'dark' : 'light');
                        });">
                            <button @click="darkMode = !darkMode" type="button"
                                class="p-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition shadow-sm group">
                                <span x-show="!darkMode" x-cloak>
                                    <x-heroicon-o-moon class="w-5 h-5 group-hover:scale-110 transition-transform" />
                                </span>
                                <span x-show="darkMode" x-cloak>
                                    <x-heroicon-o-sun
                                        class="w-5 h-5 text-amber-400 group-hover:rotate-45 transition-transform" />
                                </span>
                            </button>
                        </div>

                        <button data-collapse-toggle="mobile-menu-2" type="button"
                            class="inline-flex items-center p-2 text-sm text-gray-500 rounded-lg hover:bg-gray-100 focus:outline-none dark:text-gray-400 dark:hover:bg-gray-700"
                            aria-controls="mobile-menu-2" aria-expanded="true">
                            <span class="sr-only">Open main menu</span>
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                                    clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="items-stretch justify-between w-full lg:flex lg:w-auto lg:order-1 hidden ms-6 h-full"
                    id="mobile-menu-2">
                    <ul class="flex flex-col font-medium lg:flex-row lg:space-x-0 h-full items-stretch">
                        <li class="flex h-full items-stretch">
                            <a href="{{ route('home') }}"
                                class="h-full flex items-center px-4 text-zinc-700 dark:text-zinc-200 hover:bg-guinda hover:text-white transition-colors duration-200"
                                aria-current="page">Inicio</a>
                        </li>
                        <li class="flex h-full items-stretch">
                            <a href="{{ route('home') }}"
                                class="h-full flex items-center px-4 text-zinc-700 dark:text-zinc-200 hover:bg-guinda hover:text-white transition-colors duration-200">Fondos</a>
                        </li>
                    </ul>

                    <div class="block md:hidden">
                        @if (Auth::check())
                            <div class="flex ml-0 md:ml-auto gap-2 md:gap-8 items-center h-full">
                                <x-desktop-user-menu />
                            </div>
                        @else
                            <div class="flex ml-0 md:ml-auto gap-2 md:gap-8 items-center h-full">
                                <a href="{{ route('login') }}"
                                    class="text-sm md:text-base rounded text-white py-1 px-6 bg-red-800 hover:bg-red-900 transition shadow-sm">{{ __('Log In') }}
                                    | {{ __('Sign up') }}</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex items-center lg:order-2 h-full gap-4">
                <div class="hidden md:flex items-center h-full gap-4">
                    <div x-data="{
                        darkMode: localStorage.getItem('theme') === 'dark' ||
                            (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)
                    }" x-init="darkMode ? document.documentElement.classList.add('dark') : document.documentElement.classList.remove('dark');
                    $watch('darkMode', val => {
                        val ? document.documentElement.classList.add('dark') : document.documentElement.classList.remove('dark');
                        localStorage.setItem('theme', val ? 'dark' : 'light');
                    });" class="flex items-center">
                        <button @click="darkMode = !darkMode" type="button"
                            class="p-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition shadow-sm group">
                            <span x-show="!darkMode" x-cloak>
                                <x-heroicon-o-moon class="w-5 h-5 group-hover:scale-110 transition-transform" />
                            </span>
                            <span x-show="darkMode" x-cloak>
                                <x-heroicon-o-sun
                                    class="w-5 h-5 text-amber-400 group-hover:rotate-45 transition-transform" />
                            </span>
                        </button>
                    </div>

                    @if (Auth::check())
                        <div class="flex items-center h-full">
                            <x-desktop-user-menu />
                        </div>
                        @nodef
                    @else
                        <div class="flex items-center h-full">
                            <a href="{{ route('login') }}"
                                class="inline-flex items-center text-sm font-medium rounded-md bg-red-800 hover:bg-red-900 text-white transition shadow-sm h-9 px-4">
                                {{ __('Log In') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </nav>

    <section class="relative flex flex-col dark:bg-gray-200">
        {{--
        <div class="z-10">
          
            <div class="mx-auto max-w-5xl px-6 md:px-2 py-10 md:py-24 flex flex-col gap-6 items-center">
                <h1 class="text-4xl font-bold text-center max-w-[600px]">
                    Lorem ipsum dolor sit<br>amet consectetur adipisicing elit
                </h1>
                <p class="text-gray-600 text-base md:text-lg text-center max-w-[600px]">
                    Dolorem cupiditate voluptatem veniam reprehenderit, commodi ea quia, sunt enim modi fugit, eius qui
                    explicabo sit inventore labore deleniti iure atque optio.
                </p>
                <div class="flex gap-4 items-center text-center">
                    <a href=""
                        class="text-sm md:text-base bg-white border rounded font-bold py-2 px-6 hover:bg-gray-50">Book a
                        demo</a>
                    <a href=""
                        class="text-sm md:text-base bg-orange-500 border border-orange-500 rounded text-white font-bold py-2 px-6 hover:bg-orange-600">Get
                        started - it's free</a>
                </div>
                <div class="flex flex-wrap flex-row justify-center gap-4">
                    <small>
                        <i
                            class="mr-1 bi bi-check bg-orange-100 border border-orange-200 rounded-full w-[21px] h-[21px] inline-block text-center text-orange-600"></i>
                        <span>Lifetime free plan</span>
                    </small>
                    <small>
                        <i
                            class="mr-1 bi bi-check bg-orange-100 border border-orange-200 rounded-full w-[21px] h-[21px] inline-block text-center text-orange-600"></i>
                        <span>No credit card needed</span>
                    </small>
                    <small>
                        <i
                            class="mr-1 bi bi-check bg-orange-100 border border-orange-200 rounded-full w-[21px] h-[21px] inline-block text-center text-orange-600"></i>
                        <span>Support 24/24 - 7/7</span>
                    </small>
                </div>
            </div>
           
        </div>
 --}}
        <div id="img-bpej" style="width: 100%;" class="hidden md:block">
            <div class="flex flex-col h-full ">
                <form action="{{ route('buscador') }}" method="GET"
                    class="mt-auto mx-auto w-full md:max-w-screen-lg mb-3 px-3 sm:px-7 pt-9" x-data="{ activeIndexDesktop: 0 }"
                    :style="'--active-index: ' + activeIndexDesktop + '; --tab-width: 120px;'">

                    <div class="flex flex-col lg:flex-row gap-3 lg:items-end">
                        <div class="flex-1">
                            <div class="relative">
                                <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                    <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                                </div>
                                <input type="text" name="q" placeholder="Buscar contenido..."
                                    class="w-full pl-10 pr-3 py-2.5 text-sm rounded-xl border border-gray-300 bg-white focus:ring-2 focus:ring-red-100 focus:border-red-700 outline-none transition">
                            </div>
                        </div>

                        <div class="flex gap-2 w-full lg:w-auto">
                            <flux:button type="submit" variant="primary" size="sm"
                                class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2 text-sm font-medium rounded-xl h-10 bg-red-800 hover:bg-red-900 text-white transition shadow-sm">
                                <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                            </flux:button>

                            <flux:button href="{{ route('home') }}" variant="primary" size="sm"
                                class="w-full inline-flex items-center justify-center gap-1.5 h-10 px-4 py-2 text-sm font-medium rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-200 transition">
                                <x-heroicon-o-x-mark class="w-5 h-5" />
                            </flux:button>
                        </div>
                    </div>

                    <div class="w-full overflow-x-auto scrollbar-none mt-1 bg-white rounded-xl mt-3 dark:bg-stone-700">
                        <div class="relative flex min-w-max items-stretch h-12">

                            <input type="radio" id="d-todas" name="tabs-desktop" class="hidden" checked
                                @click="activeIndexDesktop = 0">
                            <label for="d-todas"
                                class="dark:text-white h-full flex items-center justify-center text-center cursor-pointer z-20 font-bold text-sm transition-colors duration-300 w-[var(--tab-width)]"
                                :class="activeIndexDesktop === 0 ? 'text-white' : 'text-zinc-600'">
                                Todas
                            </label>

                            <input type="radio" id="d-libros" name="tabs-desktop" class="hidden"
                                @click="activeIndexDesktop = 1">
                            <label for="d-libros"
                                class="dark:text-white h-full flex items-center justify-center text-center cursor-pointer z-20 font-bold text-sm transition-colors duration-300 w-[var(--tab-width)]"
                                :class="activeIndexDesktop === 1 ? 'text-white' : 'text-zinc-600'">
                                Libros
                            </label>

                            <input type="radio" id="d-periodicos" name="tabs-desktop" class="hidden"
                                @click="activeIndexDesktop = 2">
                            <label for="d-periodicos"
                                class="dark:text-white h-full flex items-center justify-center text-center cursor-pointer z-20 font-bold text-sm transition-colors duration-300 w-[var(--tab-width)]"
                                :class="activeIndexDesktop === 2 ? 'text-white' : 'text-zinc-600'">
                                Periódicos
                            </label>

                            <input type="radio" id="d-revistas" name="tabs-desktop" class="hidden"
                                @click="activeIndexDesktop = 3">
                            <label for="d-revistas"
                                class="dark:text-white h-full flex items-center justify-center text-center cursor-pointer z-20 font-bold text-sm transition-colors duration-300 w-[var(--tab-width)]"
                                :class="activeIndexDesktop === 3 ? 'text-white' : 'text-zinc-600'">
                                Revistas
                            </label>

                            <input type="radio" id="d-mapas" name="tabs-desktop" class="hidden"
                                @click="activeIndexDesktop = 4">
                            <label for="d-mapas"
                                class="dark:text-white h-full flex items-center justify-center text-center cursor-pointer z-20 font-bold text-sm transition-colors duration-300 w-[var(--tab-width)]"
                                :class="activeIndexDesktop === 4 ? 'text-white' : 'text-zinc-600'">
                                Mapas
                            </label>

                            <div id="indicatorDesktop" class="absolute bg-guinda-dark rounded-md z-10 top-0 bottom-0"
                                style="width: var(--tab-width); transform: translateX(calc(var(--active-index, 0) * var(--tab-width))); transition: transform 0.5s cubic-bezier(0.68, -0.55, 0.26, 1.55);">
                            </div>

                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="mt-auto mx-auto md:hidden w-full sm:px-7 pt-6 dark:bg-neutral-800">
            <form action="{{ route('buscador') }}" method="GET" class="bg-transparent" x-data="{ activeIndexMobile: 0 }"
                :style="'--active-index: ' + activeIndexMobile + '; --tab-width: 120px;'">

                <div class="flex flex-col gap-3 p-4 bg-transparent">
                    <div class="w-full">
                        <div class="relative">
                            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                            </div>
                            <input type="text" name="q" value="{{ request('coleccion') }}"
                                placeholder="Buscar colección..."
                                class="dark:text-zinc-900 w-full pl-10 pr-3 py-2.5 text-sm rounded-xl border border-gray-300 bg-white focus:ring-2 focus:ring-red-100 focus:border-red-700 outline-none transition">
                        </div>
                    </div>

                    <div class="flex gap-2 w-full">
                        <flux:button type="submit" variant="primary" size="sm"
                            class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2 text-sm font-medium rounded-xl h-10 bg-red-800 hover:bg-red-900 text-white transition shadow-sm">
                            <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                        </flux:button>

                        <flux:button href="{{ route('home') }}" variant="ghost" size="sm"
                            class="dark:bg-zinc-700 dark:text-white w-full inline-flex items-center justify-center gap-1.5 h-10 px-4 py-2 text-sm font-medium rounded-xl bg-white hover:bg-gray-200 text-gray-700 border border-gray-200 transition">
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </flux:button>
                    </div>
                </div>

                <div class="flex flex-col  px-2" id="img-bpej">
                    <div
                        class="my-auto mx-auto w-full overflow-x-auto scrollbar-none bg-white dark:bg-stone-700 dark:text-white rounded-xl shadow-sm">
                        <div class="relative flex min-w-max items-stretch h-12">

                            <input type="radio" id="m-todas" name="tabs-mobile" class="hidden" checked
                                @click="activeIndexMobile = 0">
                            <label for="m-todas"
                                class="dark:text-white h-full flex items-center justify-center text-center cursor-pointer z-20 font-bold text-sm transition-colors duration-300 w-[var(--tab-width)]"
                                :class="activeIndexMobile === 0 ? 'text-white' : 'text-zinc-600'">
                                Todas
                            </label>

                            <input type="radio" id="m-libros" name="tabs-mobile" class="hidden"
                                @click="activeIndexMobile = 1">
                            <label for="m-libros"
                                class="dark:text-white h-full flex items-center justify-center text-center cursor-pointer z-20 font-bold text-sm transition-colors duration-300 w-[var(--tab-width)]"
                                :class="activeIndexMobile === 1 ? 'text-white' : 'text-zinc-600'">
                                Libros
                            </label>

                            <input type="radio" id="m-periodicos" name="tabs-mobile" class="hidden"
                                @click="activeIndexMobile = 2">
                            <label for="m-periodicos"
                                class="dark:text-white h-full flex items-center justify-center text-center cursor-pointer z-20 font-bold text-sm transition-colors duration-300 w-[var(--tab-width)]"
                                :class="activeIndexMobile === 2 ? 'text-white' : 'text-zinc-600'">
                                Periódicos
                            </label>

                            <input type="radio" id="m-revistas" name="tabs-mobile" class="hidden"
                                @click="activeIndexMobile = 3">
                            <label for="m-revistas"
                                class="dark:text-white h-full flex items-center justify-center text-center cursor-pointer z-20 font-bold text-sm transition-colors duration-300 w-[var(--tab-width)]"
                                :class="activeIndexMobile === 3 ? 'text-white' : 'text-zinc-600'">
                                Revistas
                            </label>

                            <input type="radio" id="m-mapas" name="tabs-mobile" class="hidden"
                                @click="activeIndexMobile = 4">
                            <label for="m-mapas"
                                class="dark:text-white h-full flex items-center justify-center text-center cursor-pointer z-20 font-bold text-sm transition-colors duration-300 w-[var(--tab-width)]"
                                :class="activeIndexMobile === 4 ? 'text-white' : 'text-zinc-600'">
                                Mapas
                            </label>

                            <div id="indicatorMovile" class="absolute bg-red-800 rounded-md z-10 top-0 bottom-0"
                                style="width: var(--tab-width); transform: translateX(calc(var(--active-index, 0) * var(--tab-width))); transition: transform 0.5s cubic-bezier(0.68, -0.55, 0.26, 1.55);">
                            </div>

                        </div>
                    </div>
                </div>

            </form>
        </div>

        {{-- 
        <div class="-z-10 absolute top-0 w-full h-[200px] bg-gradiant">

        </div>
         --}}
    </section>


    <div class=" bg-white border-gray-200 py-2.5 dark:bg-neutral-800">
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
                    <div class="cursor-pointer group text-gray-600 hover:text-zinc-500" x-data="{ id: $id('accordion') }"
                        :class="{
                            'text-zinc-900': activeAccordion ==
                                id,
                            'text-gray-600 hover:text-zinc-500': activeAccordion != id
                        }">
                        <button
                            class="flex items-center justify-between w-full p-4 pb-1 text-sm text-left select-none lg:text-base"
                            @click="setActiveAccordion(id)">
                            <span>How does bookme work?</span>
                            <svg class="w-5 h-5 duration-300 ease-out text-zinc-500" fill="none"
                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" stroke="currentColor"
                                stroke-width="1.5" :class="{ '-rotate-[45deg]': activeAccordion == id }">
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
                    <div class="text-gray-600 cursor-pointer group hover:text-zinc-500" x-data="{ id: $id('accordion') }"
                        :class="{
                            'text-zinc-900': activeAccordion ==
                                id,
                            'text-gray-600 hover:text-zinc-500': activeAccordion != id
                        }">
                        <button
                            class="flex items-center justify-between w-full p-4 pb-1 text-sm text-left select-none lg:text-base"
                            @click="setActiveAccordion(id)">
                            <span>What type of bookme?</span>
                            <svg class="w-5 h-5 duration-300 ease-out text-zinc-500" fill="none"
                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" stroke="currentColor"
                                stroke-width="1.5" :class="{ '-rotate-[45deg]': activeAccordion == id }">
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
                    <div class="text-gray-600 cursor-pointer group hover:text-zinc-500" x-data="{ id: $id('accordion') }"
                        :class="{
                            'text-zinc-900': activeAccordion ==
                                id,
                            'text-gray-600 hover:text-zinc-500': activeAccordion != id
                        }">
                        <button
                            class="flex items-center justify-between w-full p-4 pb-1 text-sm text-left select-none lg:text-base"
                            @click="setActiveAccordion(id)">
                            <span>Can I cancel my subscription?</span>
                            <svg class="w-5 h-5 duration-300 ease-out text-zinc-500" fill="none"
                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" stroke="currentColor"
                                stroke-width="1.5" :class="{ '-rotate-[45deg]': activeAccordion == id }">
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
                    <div class="text-gray-600 cursor-pointer group hover:text-zinc-500" x-data="{ id: $id('accordion') }"
                        :class="{
                            'text-zinc-900': activeAccordion ==
                                id,
                            'text-gray-600 hover:text-zinc-500': activeAccordion != id
                        }">
                        <button
                            class="flex items-center justify-between w-full p-4 pb-1 text-sm text-left select-none lg:text-base"
                            @click="setActiveAccordion(id)">
                            <span>is bookme safe?</span>
                            <svg class="w-5 h-5 duration-300 ease-out text-zinc-500" fill="none"
                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" stroke="currentColor"
                                stroke-width="1.5" :class="{ '-rotate-[45deg]': activeAccordion == id }">
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
                    <div class="text-gray-600 cursor-pointer group hover:text-zinc-500" x-data="{ id: $id('accordion') }"
                        :class="{
                            'text-zinc-900': activeAccordion ==
                                id,
                            'text-gray-600 hover:text-zinc-500': activeAccordion != id
                        }">
                        <button
                            class="flex items-center justify-between w-full p-4 pb-1 text-sm text-left select-none lg:text-base"
                            @click="setActiveAccordion(id)">
                            <span>Can I use 2 account?</span>
                            <svg class="w-5 h-5 duration-300 ease-out text-zinc-500" fill="none"
                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" stroke="currentColor"
                                stroke-width="1.5" :class="{ '-rotate-[45deg]': activeAccordion == id }">
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
                    <div class="text-gray-600 cursor-pointer group hover:text-zinc-500" x-data="{ id: $id('accordion') }"
                        :class="{
                            'text-zinc-900': activeAccordion ==
                                id,
                            'text-gray-600 hover:text-zinc-500': activeAccordion != id
                        }">
                        <button
                            class="flex items-center justify-between w-full p-4 pb-1 text-sm text-left select-none lg:text-base"
                            @click="setActiveAccordion(id)">
                            <span>Is bookme a free service?</span>
                            <svg class="w-5 h-5 duration-300 ease-out text-zinc-500" fill="none"
                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" stroke="currentColor"
                                stroke-width="1.5" :class="{ '-rotate-[45deg]': activeAccordion == id }">
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
                        <img src="{{ asset('img/logo.svg') }}" alt="" width="80" height="80">
                        <span class="" style="color:#7c2422;">

                            {{ config('app.name', 'Laravel') }}</span>
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
