<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
    <div class="grid min-h-dvh lg:grid-cols-2">

        <!-- Panel izquierdo (branding): fijo mientras el formulario hace scroll -->
        <aside
            class="relative hidden bg-[#7c2422] p-10 text-white lg:sticky lg:top-0 lg:flex md:flex-col lg:h-dvh lg:items-center lg:justify-center">
            <a href="{{ route('home') }}" wire:navigate>
                <x-app-logo-icon mono class="h-20 text-white" />


            </a>
            <a href="https://bpej.udg.mx" class="border-t border-white pt-2 mt-2 ">
                <img src="{{ asset('img/bpej-oscuro.svg') }}" alt="" class="mt-1 h-20 text-white">
            </a>
        </aside>

        <!-- Panel derecho (formulario): arriba en móvil, centrado en desktop si cabe -->
        <main class="flex min-h-dvh items-start justify-center px-4 py-8 sm:px-8 lg:items-center lg:px-10 lg:py-12">
            <div
                {{ $attributes->class([
                    'mx-auto flex w-full flex-col gap-6 @container',
                    // ancho por defecto (login, etc.); si la vista pasa su propio max-w-*, se respeta
                    'max-w-sm' => !str_contains($attributes->get('class', ''), 'max-w-'),
                ]) }}>
                <!-- Logo solo en móvil/tablet (en desktop está en el panel izquierdo) -->
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-medium lg:hidden"
                    wire:navigate>
                    <x-app-logo-icon class="h-12 text-neutral-900 dark:text-white" />

                </a>

                <a href="https://bpej.udg.mx" class="flex flex-col items-center gap-2 font-medium lg:hidden">
                    <img src="{{ asset('img/bpej.svg') }}" alt="" class="mt-1 h-10 text-white">
                </a>
                {{ $slot }}
            </div>
        </main>

    </div>
    @fluxScripts
</body>

</html>
