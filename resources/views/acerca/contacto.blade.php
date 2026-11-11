@php
    $direccion = 'Periférico Norte Manuel Gómez Morín no. 1695, Col. Belenes,  C.P. 45100, Zapopan, Jalisco, México';
    $telefono = '33 3836 4530';
    $correo = 'arreola.historica@udg.mx';

    // Clave = día ISO (1 = lunes ... 7 = domingo), null para "Cerrado".
    $bibliotecas = [
        [
            'nombre' => 'Biblioteca Histórica',
            'horarios' => [['Lunes a Viernes', [1, 2, 3, 4, 5], '9 a 15 hrs.']],
        ],
        [
            'nombre' => 'Biblioteca Contemporánea',
            'horarios' => [
                ['Lunes a Viernes', [1, 2, 3, 4, 5], '9 a 20 hrs.'],
                ['Sábado', [6], '9 a 17 hrs.'],
                ['Domingo', [7], '9 a 15 hrs.'],
            ],
        ],
    ];

    $hoy = now()->dayOfWeekIso;
@endphp
@extends('layouts.plantilla')

@section('content')
    <div class="min-h-screen">
        <!-- Encabezado -->
        <header class="w-full bg-[#86212b] px-4 py-10">
            <div class="mx-auto max-w-6xl text-center">
                <h1 class="text-xl font-bold uppercase tracking-[0.15em] text-white md:text-2xl">
                    {{ $title }}
                </h1>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-5 py-12 md:px-8 md:py-16">

            <section class="mb-12">
                <div class="grid gap-6 md:grid-cols-2">

                    <!-- Tarjeta de Dirección -->
                    <div
                        class="flex flex-col gap-4 rounded-lg border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                        <div class="mb-2 border-b border-zinc-200 pb-3 dark:border-zinc-700">
                            <h3 class="flex items-center gap-2 text-xl font-semibold text-zinc-800 dark:text-zinc-100">
                                <x-heroicon-o-map-pin class="h-6 w-6 shrink-0 text-[#86212b] dark:text-[#d98b91]" />
                                Dirección
                            </h3>
                        </div>

                        <p class="text-base leading-7 text-zinc-700 dark:text-zinc-300">
                            <strong>Biblioteca Pública del Estado de Jalisco “Juan José Arreola”</strong><br>
                            {{ $direccion }}
                        </p>

                        @if (filled($telefono))
                            <p class="flex items-center gap-3 text-sm text-zinc-700 dark:text-zinc-300">
                                <x-heroicon-o-phone class="h-5 w-5 shrink-0 text-[#86212b] dark:text-[#d98b91]" />
                                <a href="tel:{{ preg_replace('/\D/', '', $telefono) }}"
                                    class="hover:text-[#86212b] hover:underline dark:hover:text-[#d98b91] transition-colors">
                                    {{ $telefono }}
                                </a>
                            </p>
                        @endif

                        @if (filled($correo))
                            <p class="flex items-center gap-3 text-sm text-zinc-700 dark:text-zinc-300">
                                <x-heroicon-o-envelope class="h-5 w-5 shrink-0 text-[#86212b] dark:text-[#d98b91]" />
                                <a href="mailto:{{ $correo }}"
                                    class="break-all hover:text-[#86212b] hover:underline dark:hover:text-[#d98b91] transition-colors">
                                    {{ $correo }}
                                </a>
                            </p>
                        @endif

                        <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($direccion) }}"
                            target="_blank" rel="noopener"
                            class="mt-auto inline-flex w-fit items-center gap-2 rounded-md bg-[#86212b] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-[#6a1a22] focus:outline-none focus:ring-2 focus:ring-[#86212b] focus:ring-offset-2 dark:focus:ring-offset-zinc-900">
                            <x-heroicon-o-map class="h-5 w-5" />
                            Cómo llegar
                        </a>
                    </div>

                    <!-- Tarjeta de Horarios -->
                    <div
                        class="flex flex-col gap-5 rounded-lg border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                        <div class="mb-2 border-b border-zinc-200 pb-3 dark:border-zinc-700">
                            <h3 class="flex items-center gap-2 text-xl font-semibold text-zinc-800 dark:text-zinc-100">
                                <x-heroicon-o-clock class="h-6 w-6 shrink-0 text-[#86212b] dark:text-[#d98b91]" />
                                Horarios
                            </h3>
                        </div>

                        <div class="space-y-6">
                            @foreach ($bibliotecas as $biblioteca)
                                <div>
                                    <h4
                                        class="mb-3 text-sm font-semibold uppercase tracking-wider text-[#86212b] dark:text-white">
                                        {{ $biblioteca['nombre'] }}
                                    </h4>

                                    <dl class="flex flex-col gap-2 text-sm">
                                        @foreach ($biblioteca['horarios'] as [$etiqueta, $dias, $horario])
                                            <div
                                                class="flex items-center justify-between rounded-md px-3 py-2.5 transition-colors
                                                {{ in_array($hoy, $dias) ? 'bg-[#86212b]/10 font-medium text-[#86212b] dark:bg-[#d98b91]/10 dark:text-white' : 'text-zinc-700 dark:text-zinc-300' }}">
                                                <dt>{{ $etiqueta }}</dt>
                                                <dd>{{ $horario }}</dd>
                                            </div>
                                        @endforeach
                                    </dl>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>
            </section>

            <!-- Botones de Compartir -->
            <section class="mt-12 border-t border-zinc-200 pt-8 dark:border-zinc-700">

                <div
                    class="mx-auto w-full max-w-4xl mt-10 border-t border-zinc-200 dark:border-zinc-700 pt-6 flex flex-wrap items-center justify-center gap-3 dark:bg-neutral-600">
                    <span class="text-xs font-bold text-zinc-500 dark:text-white uppercase tracking-wide">Compartir:</span>
                    <a href="https://wa.me/?text={{ urlencode(url()->current()) }}" target="_blank" rel="noopener"
                        class="text-xs bg-zinc-100 hover:bg-zinc-200 text-zinc-700 rounded-full py-1.5 px-3 transition-colors dark:bg-neutral-600 dark:text-white dark:hover:bg-zinc-700">
                        WhatsApp
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
                        target="_blank" rel="noopener"
                        class="dark:border dark:border-zinc-800 text-xs bg-zinc-100 hover:bg-zinc-200 text-zinc-700 rounded-full py-1.5 px-3 transition-colors dark:bg-neutral-600 
                    dark:text-white dark:hover:bg-zinc-700">
                        Facebook
                    </a>

                    <a href="https://x.com/intent/post?url={{ urlencode(url()->current()) }}&text={{ urlencode($titulo ?? '') }}"
                        target="_blank" rel="noopener"
                        class="dark:border dark:border-zinc-800 text-xs bg-zinc-100 hover:bg-zinc-200 text-zinc-700 rounded-full py-1.5 px-3 transition-colors dark:bg-neutral-600 dark:text-white dark:hover:bg-zinc-700">
                        X
                    </a>

                    <button type="button" x-data="{ soportado: !!navigator.share }" x-show="soportado" x-cloak
                        x-on:click="navigator.share({ title: document.title, url: '{{ url()->current() }}' })"
                        class="text-xs bg-zinc-100 hover:bg-zinc-200 text-zinc-700 rounded-full py-1.5 px-3 transition-colors dark:bg-neutral-600 dark:text-white dark:hover:bg-zinc-700">
                        Más opciones
                    </button>
                </div>
            </section>
        </main>
    </div>
@endsection
