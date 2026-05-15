<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        {{ filled($title ?? null) ? $title . ' - ' . config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
    </title>
    <link rel="icon" type="image/x-icon" href="imgs/favico.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link href="https://fonts.googleapis.com/css2?family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

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

        .bg-gradiant {
            background-image: linear-gradient(rgba(255, 255, 255, 0), #fff), url("data:image/svg+xml,%3Csvg width='12' height='24' viewBox='0 0 12 24' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d6d6d6' fill-opacity='0.62'%3E%3Cpath d='M2 0h2v12H2V0zm1 20c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM9 8c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zm-1 4h2v12H8V12z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        @media(max-width:800px) {
            #img-bpej {
                aspect-ratio: 16 / 5 !important;
            }
        }

        @media(min-width:801px) {
            #img-bpej {
                aspect-ratio: 16 / 9 !important;
            }
        }

        #img-bpej {
            object-fit: cover;
            max-height: 350px;
            width: 100%;
        }
    </style>
    @yield('css')
    @livewireStyles
</head>

<body class="antialiased">
    <header class="border-b-2">
        <div class="mx-auto w-full px-6 py-4 flex flex-col md:flex-row gap-2 md:gap-8 items-between">
            <a href="{{ route('home') }}" class="font-bold text-xl flex flex-col md:flex-row text-center md:text-left"
                style="align-items: center; gap: 8px;">
                <img src="{{ asset('img/logo.svg') }}" alt="" width="80" height="80" id="logo">
                <span class="" style="color:#86212b;">

                    {{ config('app.name', 'Laravel') }}</span>
            </a>
            <nav style="display: flex;align-items: center;">
                <ul class="flex items-center gap-4 text-sm md:text-base">
                    <li>
                        <a href="{{ route('home') }}" class="hover:text-orange-500">Colecciones</a>
                    </li>
                </ul>
            </nav>
            <div class="flex ml-0 md:ml-auto gap-2 md:gap-8 items-center">
                {{-- <a href="/" class="text-sm md:text-base hover:text-orange-500">Demo</a> --}}
                <a href="{{ route('login') }}" style="background:#86212b"
                    class="text-sm md:text-base rounded text-white font-bold py-1 px-6">{{ __('Log In') }}
                    / {{ __('Sign up') }}</a>
            </div>
        </div>
    </header>
    <section class="relative">
        <div class="z-10">
            {{--
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
            --}}
            <img src="{{ asset('img/portada-web.jpg') }}" id="img-bpej" alt="logo-bpej">
        </div>

        <div class="-z-10 absolute top-0 w-full h-[200px] bg-gradiant">

        </div>
    </section>


    <div>
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

    <footer>
        <div class="px-2 py-20 mx-auto max-w-7xl">
            <div class="pt-12 border-t border-gray-300 flex flex-col md:flex-row gap-10 items-center">
                <div class="text-black flex flex-col ">
                    <a href="{{ route('home') }}" class="font-bold text-xl"
                        style="display: flex; align-items: center; gap: 8px;">
                        <img src="{{ asset('img/logo.svg') }}" alt="" width="80" height="80">
                        <span class="" style="color:#7c2422;">

                            {{ config('app.name', 'Laravel') }}</span>
                    </a>
                    <p class="mt-2 text-sm text-gray-500 lg:w-4/5">
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
                    <span class="mx-auto mt-2 text-sm text-gray-500 lg:mx-0">
                        Derechos reservados ©1997 - 2025. Universidad de Guadalajara. |
                        Política de privacidad y manejo de datos
                    </span>
                </p>
            </div>

        </div>
    </footer>
    @livewireScripts
    @yield('js')
</body>

</html>
