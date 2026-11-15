<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen antialiased font-titillium bg-white dark:bg-neutral-800">
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

    {{-- Sidebar con ancho w-72 (igual que el cajón de plantilla.blade.php) y colores institucionales --}}
    <flux:sidebar sticky collapsible="mobile"
        class="!w-72 border-e border-gray-200 bg-white dark:border-neutral-700 dark:bg-neutral-600 px-4 py-4">

        {{-- Logo institucional grande (h-16) --}}
        <flux:sidebar.header class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-neutral-700">
            <a href="{{ route('home') }}" class="flex items-center shrink-0" wire:navigate>
                <img src="{{ asset('img/arreola-blanco.svg') }}" alt="{{ config('app.name', 'Laravel') }} Logo"
                    class="block dark:hidden h-16 w-auto">
                <img src="{{ asset('img/arreola-oscuro.svg') }}" alt="{{ config('app.name', 'Laravel') }} Logo"
                    class="hidden dark:block h-16 w-auto">
            </a>

            <flux:sidebar.collapse class="lg:hidden text-zinc-600 dark:text-zinc-200" />
        </flux:sidebar.header>

        {{-- Enlaces estilo cajón de plantilla.blade.php (sin truncar texto y con hover/activo en bg-guinda) --}}
        <nav class="mt-4 flex flex-col space-y-1 font-medium">
            @foreach ($enlaces as [$etiqueta, $ruta, $icono])
                @php $activo = request()->routeIs($ruta); @endphp
                <a href="{{ route($ruta) }}" wire:navigate
                    class="uppercase flex items-center gap-3 rounded-md px-3 py-2.5 text-sm leading-snug transition-colors duration-200
                    {{ $activo
                        ? 'bg-guinda text-white font-semibold shadow-sm'
                        : 'text-zinc-700 dark:text-zinc-200 hover:bg-guinda hover:text-white' }}">
                    <flux:icon :name="$icono" class="w-5 h-5 shrink-0 opacity-85" />
                    <span>{{ $etiqueta }}</span>
                </a>
            @endforeach
        </nav>

        <flux:spacer />

        {{-- Pie del Sidebar: Selector de Tema y Usuario --}}
        <div class="mt-6 pt-4 border-t border-gray-200 dark:border-neutral-700 space-y-3">
            {{-- Botón Modo Claro / Oscuro --}}
            <div x-data>
                <button x-on:click="$flux.dark = ! $flux.dark" type="button"
                    class="flex w-full items-center justify-between px-3 py-2.5 rounded-xl border border-zinc-200 dark:border-neutral-500 bg-white text-zinc-700 dark:bg-neutral-700 dark:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-neutral-500 transition shadow-sm group text-xs font-semibold uppercase tracking-wider">
                    <span x-text="$flux.dark ? 'Modo Claro' : 'Modo Oscuro'"></span>
                    <span x-show="! $flux.dark" x-cloak>
                        <x-heroicon-o-moon class="w-5 h-5 group-hover:scale-110 transition-transform" />
                    </span>
                    <span x-show="$flux.dark" x-cloak>
                        <x-heroicon-o-sun class="w-5 h-5 text-amber-400 group-hover:rotate-45 transition-transform" />
                    </span>
                </button>
            </div>

            {{-- Usuario / Login --}}
            <div class="hidden lg:block">
                @auth
                    <x-desktop-user-menu :name="auth()->user()->name" />
                @else
                    <a href="{{ route('login') }}"
                        class="block w-full rounded-md bg-red-800 px-4 py-2.5 text-center text-sm font-medium text-white shadow-sm transition hover:bg-red-900">
                        {{ __('Log In') }}
                    </a>
                @endauth
            </div>
        </div>
    </flux:sidebar>

    {{-- Header Móvil --}}
    <flux:header class="lg:hidden bg-white dark:bg-neutral-600 border-b border-gray-200 dark:border-neutral-700 px-4 py-2">
        <flux:sidebar.toggle class="lg:hidden text-zinc-700 dark:text-zinc-200" icon="bars-2" inset="left" />

        <a href="{{ route('home') }}" class="ms-2 flex items-center" wire:navigate>
            <img src="{{ asset('img/arreola-blanco.svg') }}" alt="{{ config('app.name', 'Laravel') }} Logo"
                class="block dark:hidden h-12 w-auto">
            <img src="{{ asset('img/arreola-oscuro.svg') }}" alt="{{ config('app.name', 'Laravel') }} Logo"
                class="hidden dark:block h-12 w-auto">
        </a>

        <flux:spacer />

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

        @auth
            <flux:dropdown position="top" align="end">
                <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />
                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" />
                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>
                    <flux:menu.separator />
                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>
                    <flux:menu.separator />
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer" data-test="logout-button">
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        @else
            <a href="{{ route('login') }}"
                class="rounded-md bg-red-800 px-3 py-1.5 text-xs font-medium text-white shadow-sm transition hover:bg-red-900">
                {{ __('Log In') }}
            </a>
        @endauth
    </flux:header>

    {{-- Contenedor Principal alineado arriba a la izquierda --}}
    <flux:main class="w-full bg-white dark:bg-neutral-800 p-6 lg:p-8">
        {{ $slot }}
    </flux:main>

    @fluxScripts
</body>

</html>