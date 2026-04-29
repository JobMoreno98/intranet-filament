<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body
    class="min-h-screen my-5 md:my-0 bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
    <div
        class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">

        <div
            class="bg-muted relative hidden h-full flex-col p-10 text-white lg:flex dark:border-e dark:border-neutral-800 justify-center items-center">
            <div class="absolute inset-0 bg-neutral-900"></div>
            <a href="{{ route('home') }}" class="relative z-20 flex flex-col items-center text-lg font-medium"
                wire:navigate>
                <span class="flex  items-center justify-center rounded-lg">
                    <x-app-logo-icon class="me-2 h-7 fill-current text-white" />
                </span>
            </a>

        </div>
        <div class="w-full lg:p-8">
            <div class="mx-auto flex w-full flex-col justify-center p-4 rounded-md space-y-6 sm:w-[350px]"
                style="border:#7c2422 solid 2px;">
                <a href="{{ route('home') }}" class="z-20 flex flex-col items-center gap-2 font-medium lg:hidden"
                    wire:navigate>

                    <span class="flex  items-center justify-center rounded-md">
                        <x-app-logo-icon class=" fill-current text-black dark:text-white" />
                    </span>

                    <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
                </a>
                {{-- 
                <span class="flex  items-center justify-center rounded-lg">
                    <x-app-logo-icon class="me-2 h-7 fill-current text-white" />
                </span>
                 --}}
                {{ $slot }}
            </div>
        </div>
    </div>
    @fluxScripts
</body>

</html>
