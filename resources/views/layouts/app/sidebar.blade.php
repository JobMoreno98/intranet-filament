<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body x-data="{ menuOpen: false }" @keydown.escape.window="menuOpen = false"
    @resize.window="if (window.innerWidth >= 1024) menuOpen = false"
    x-effect="document.body.classList.toggle('overflow-hidden', menuOpen && window.innerWidth < 1024)"
    class="min-h-screen antialiased font-titillium bg-white dark:bg-neutral-800 lg:!block lg:pe-72">

    {{-- Fondo oscuro (solo móvil) --}}
    <div x-show="menuOpen" x-cloak @click="menuOpen = false" x-transition.opacity.duration.300ms
        class="fixed inset-0 z-40 bg-black/60 lg:hidden" aria-hidden="true"></div>

    {{--
        Sidebar derecho.
        - Móvil: cajón fijo a la derecha que se desliza (translate-x-full -> translate-x-0).
        - Desktop (lg): sigue fijo a la derecha (right-0) y siempre visible; el body
          reserva su espacio con lg:pe-72, sin depender de ningún grid.
        `invisible` se transiciona junto con el transform para que el cajón cerrado
        no reciba foco por teclado, sin cortar la animación de salida.
    --}}
    <aside id="menu-principal" role="dialog" aria-label="Menú principal" :aria-modal="menuOpen ? 'true' : 'false'"
        :class="menuOpen && 'max-lg:!translate-x-0 max-lg:!visible'"
        class="fixed inset-y-0 right-0 z-50 flex w-72 max-w-[85vw] flex-col overflow-y-auto
               border-s border-gray-200 bg-white px-4 py-4 shadow-2xl
               max-lg:translate-x-full max-lg:invisible
               transition-[transform,translate,visibility] duration-300 ease-in-out
               dark:border-neutral-700 dark:bg-neutral-600
               lg:z-30 lg:max-w-none lg:shadow-none">

        {{-- Encabezado móvil: título + cerrar --}}
        <div
            class="flex h-12 shrink-0 items-center justify-between border-b border-gray-200 pb-3 dark:border-neutral-700 lg:hidden">
            <span class="text-xs font-bold uppercase tracking-[0.2em] text-zinc-500 dark:text-zinc-300">Menú</span>
            <button @click="menuOpen = false" type="button" aria-label="Cerrar menú"
                class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 focus:outline-none dark:text-gray-400 dark:hover:bg-gray-700">
                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>

        {{-- Encabezado desktop: logo institucional + modo oscuro --}}
        <div
            class="hidden shrink-0 items-center justify-between border-b border-gray-200 pb-3 dark:border-neutral-700 lg:flex">
            <a href="{{ route('home') }}" class="flex items-center shrink-0" wire:navigate>
                <img src="{{ asset('img/arreola-blanco.svg') }}" alt="{{ config('app.name', 'Laravel') }} Logo"
                    class="block dark:hidden h-16 w-auto">
                <img src="{{ asset('img/arreola-oscuro.svg') }}" alt="{{ config('app.name', 'Laravel') }} Logo"
                    class="hidden dark:block h-16 w-auto">
            </a>

            <div x-data>
                <button x-on:click="$flux.dark = ! $flux.dark" type="button"
                    class="flex items-center justify-between px-3 py-2.5 rounded-xl border
                    border-zinc-200 dark:border-neutral-500 bg-white text-zinc-700
                    dark:bg-neutral-700 dark:text-zinc-200 hover:bg-zinc-100
                    dark:hover:bg-neutral-500 transition shadow-sm group
                    text-xs font-semibold uppercase tracking-wider">
                    <span x-show="! $flux.dark" x-cloak>
                        <x-heroicon-o-moon class="w-5 h-5 group-hover:scale-110 transition-transform" />
                    </span>
                    <span x-show="$flux.dark" x-cloak>
                        <x-heroicon-o-sun class="w-5 h-5 text-amber-400 group-hover:rotate-45 transition-transform" />
                    </span>
                </button>
            </div>
        </div>

        {{-- Navegación principal --}}
        <nav class="mt-4">
            <x-nav-links :with-icons="true" :wire-navigate="true" :show-auth="false" item-class="text-sm leading-snug" />
        </nav>

        {{-- Empuja lo siguiente al fondo --}}
        <div class="flex-1"></div>

        {{-- Enlaces de usuario autenticado + menú de usuario --}}
        @auth
            <nav class="pt-4 border-t border-gray-200 dark:border-neutral-700">
                <x-nav-links :with-icons="true" :wire-navigate="true" :only-auth="true" item-class="text-sm leading-snug" />
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-neutral-700 space-y-3">
                    <x-desktop-user-menu :name="auth()->user()->name" />
                </div>
            </nav>
        @endauth
    </aside>

    {{-- Header móvil --}}
    <flux:header
        class="lg:hidden bg-white dark:bg-neutral-600 border-b border-gray-200 dark:border-neutral-700 px-4 py-2">
        <a href="{{ route('home') }}" class="flex items-center" wire:navigate>
            <img src="{{ asset('img/arreola-blanco.svg') }}" alt="{{ config('app.name', 'Laravel') }} Logo"
                class="block dark:hidden h-12 w-auto">
            <img src="{{ asset('img/arreola-oscuro.svg') }}" alt="{{ config('app.name', 'Laravel') }} Logo"
                class="hidden dark:block h-12 w-auto">
        </a>

        <flux:spacer />

        {{-- Toggle modo oscuro --}}
        <div x-data class="flex items-center me-2">
            <button x-on:click="$flux.dark = ! $flux.dark" type="button"
                class="p-2 rounded-xl border border-zinc-200 dark:border-neutral-500 bg-white text-zinc-600 dark:bg-neutral-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-neutral-500 transition shadow-sm">
                <span x-show="! $flux.dark" x-cloak>
                    <x-heroicon-o-moon class="w-5 h-5" />
                </span>
                <span x-show="$flux.dark" x-cloak>
                    <x-heroicon-o-sun class="w-5 h-5 text-amber-400" />
                </span>
            </button>
        </div>

        {{-- Hamburguesa: abre el cajón derecho --}}
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
    </flux:header>

    {{-- Contenido principal --}}
    {{ $slot }}

    @fluxScripts
</body>

</html>