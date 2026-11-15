<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body
    class="min-h-screen antialiased font-titillium bg-white dark:bg-neutral-800 lg:grid lg:grid-cols-[1fr_18rem] lg:[grid-template-areas:'main_sidebar']">
    @php
        $enlaces = [
            ['INICIO', 'home', 'home'],
            ['NUESTRA BIBLIOTECA', 'nuestra.historia', 'building-library'],
            ['ÁREAS Y FONDOS', 'areas.index', 'folder-open'],
            ['ARREOLA HISTÓRICA', 'arreola.historica', 'archive-box'],
            ['INSTRUCCIONES DE USO', 'arreola.uso', 'information-circle'],
            ['SOLICITUD DE REPRODUCCIONES', 'arreola.solicitud', 'document-duplicate'],
            ['SALA ARREOLA (BLOG)', 'blog.index', 'newspaper'],
            ['MEMORIA UDG', 'arreola.memoria', 'academic-cap'],
            ['CONTACTO', 'arreola.contacto', 'envelope'],
            ['CRÉDITOS', 'arreola.creditos', 'users'],
        ];
    @endphp

    <flux:sidebar sticky collapsible="mobile"
        class="!w-72 lg:![grid-area:sidebar] border-s border-gray-200 bg-white dark:border-neutral-700 dark:bg-neutral-600 px-4 py-4">
        {{-- Logo institucional grande --}}
        <flux:sidebar.header
            class="hidden lg:flex items-center justify-between pb-3 border-b border-gray-200 dark:border-neutral-700">
            <a href="{{ route('home') }}" class="flex items-center shrink-0" wire:navigate>
                <img src="{{ asset('img/arreola-blanco.svg') }}" alt="{{ config('app.name', 'Laravel') }} Logo"
                    class="block dark:hidden h-16 w-auto">
                <img src="{{ asset('img/arreola-oscuro.svg') }}" alt="{{ config('app.name', 'Laravel') }} Logo"
                    class="hidden dark:block h-16 w-auto">
            </a>

            <div x-data>
                <button x-on:click="$flux.dark = ! $flux.dark" type="button"
                    class="flex  items-center justify-between px-3 py-2.5 rounded-xl border 
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

            <flux:sidebar.collapse class="lg:hidden text-zinc-600 dark:text-zinc-200" />

        </flux:sidebar.header>

        {{-- Enlaces estilo cajón de plantilla.blade.php --}}
        {{-- Navegación Principal --}}
        {{-- Navegación Principal (Arriba) --}}
        <nav class="mt-4">
            <x-nav-links :with-icons="true" :wire-navigate="true" :show-auth="false" item-class="text-sm leading-snug" />
        </nav>

        <flux:spacer />

        {{-- Enlaces de Usuario Autenticado (Justo encima del pie del Sidebar) --}}
        @auth
            <nav class="pt-4 border-t border-gray-200 dark:border-neutral-700">
                <x-nav-links :with-icons="true" :wire-navigate="true" :only-auth="true" item-class="text-sm leading-snug" />
                {{-- Pie del Sidebar: Selector de Tema y Usuario --}}
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-neutral-700 space-y-3">
                    @auth
                        <x-desktop-user-menu :name="auth()->user()->name" />
                    @else
                        <a href="{{ route('login') }}"
                            class="block w-full rounded-md bg-red-800 px-4 py-2.5 text-center text-sm font-medium text-white shadow-sm transition hover:bg-red-900">
                            {{ __('Log In') }}
                        </a>
                    @endauth
                </div>
            </nav>

        @endauth


    </flux:sidebar>

    {{-- Header Móvil --}}
    <flux:header
        class="lg:hidden bg-white dark:bg-neutral-600 border-b border-gray-200 dark:border-neutral-700 px-4 py-2">
        <a href="{{ route('home') }}" class="flex items-center" wire:navigate>
            <img src="{{ asset('img/arreola-blanco.svg') }}" alt="{{ config('app.name', 'Laravel') }} Logo"
                class="block dark:hidden h-12 w-auto">
            <img src="{{ asset('img/arreola-oscuro.svg') }}" alt="{{ config('app.name', 'Laravel') }} Logo"
                class="hidden dark:block h-12 w-auto">
        </a>

        <flux:spacer />

        {{-- Toggle Dark Mode y Usuario --}}
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

        <flux:sidebar.toggle class="lg:hidden text-zinc-700 dark:text-zinc-200" icon="bars-2" inset="right" />

        {{-- Botón hamburguesa ahora al extremo derecho --}}

    </flux:header>

    {{-- Contenido Principal --}}

    {{ $slot }}

    @fluxScripts
</body>

</html>
