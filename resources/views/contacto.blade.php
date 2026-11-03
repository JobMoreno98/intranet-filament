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

@section('css')
    <style>
        .text-custom-wine {
            color: #86212b;
        }

        .bg-custom-wine {
            background-color: #86212b;
        }

        .hover-text-custom-wine:hover {
            color: #86212b;
        }

        .post-contenido {
            margin-left: 25px;
        }

        .post-contenido>p {
            text-align: justify;
            margin-bottom: 10px;
        }

        /* Letra capitular (quítalo si no te gusta) */
        .post-contenido>p:first-child::first-letter {
            float: left;
            margin-right: 0.5rem;
            font-size: 3.75rem;
            line-height: 1;
            font-weight: 700;
            color: #86212b;
        }
    </style>
@endsection

@section('content')
    <div>
        <div class="w-full bg-[#86212b] py-8 px-4">
            <div class="max-w-6xl mx-auto text-center">
                <h1 class="text-white text-base uppercase tracking-[0.2em] font-bold">
                    {{ $title }}
                </h1>
            </div>
        </div>
        <article>
            <div>
                <div class="w-full max-w-4xl mx-auto px-4 py-12 grid gap-6 md:grid-cols-2">

                    <div
                        class="flex flex-col gap-4 rounded-md border border-zinc-200 bg-white p-6 shadow-lg dark:border-zinc-600 dark:bg-zinc-700">
                        <h3 class="flex items-center gap-2 text-lg font-bold text-zinc-800 dark:text-white">
                            <x-heroicon-o-map-pin class="h-6 w-6 shrink-0 text-[#86212b] dark:text-red-300" />
                            Dirección
                        </h3>

                        <p class="text-zinc-700 dark:text-zinc-200">
                            Biblioteca Pública del Estado de Jalisco “Juan José Arreola”<br>
                            {{ $direccion }}
                        </p>

                        @if (filled($telefono))
                            <p class="flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-200">
                                <x-heroicon-o-phone class="h-5 w-5 shrink-0 text-zinc-400" />
                                <a href="tel:{{ preg_replace('/\D/', '', $telefono) }}"
                                    class="hover:underline">{{ $telefono }}</a>
                            </p>
                        @endif

                        @if (filled($correo))
                            <p class="flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-200">
                                <x-heroicon-o-envelope class="h-5 w-5 shrink-0 text-zinc-400" />
                                <a href="mailto:{{ $correo }}"
                                    class="break-all hover:underline">{{ $correo }}</a>
                            </p>
                        @endif

                        <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($direccion) }}"
                            target="_blank" rel="noopener"
                            class="mt-auto inline-flex w-fit items-center gap-1.5 rounded bg-red-800 px-3 py-2 text-xs font-bold text-white transition-colors hover:bg-red-950">
                            <x-heroicon-o-map class="h-4 w-4" />
                            Cómo llegar
                        </a>
                    </div>

                    {{-- Horarios --}}
                    <div
                        class="flex flex-col gap-5 rounded-md border border-zinc-200 bg-white p-6 shadow-lg dark:border-zinc-600 dark:bg-zinc-700">
                        <h3 class="flex items-center gap-2 text-lg font-bold text-zinc-800 dark:text-white">
                            <x-heroicon-o-clock class="h-6 w-6 shrink-0 text-[#86212b] dark:text-red-300" />
                            Horarios
                        </h3>

                        @foreach ($bibliotecas as $biblioteca)
                            <div class="flex flex-col gap-1">
                                <h4 class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-300">
                                    {{ $biblioteca['nombre'] }}
                                </h4>

                                <dl class="flex flex-col text-sm">
                                    @foreach ($biblioteca['horarios'] as [$etiqueta, $dias, $horario])
                                        <div
                                            class="flex items-center justify-between gap-4 rounded px-3 py-2
                        {{ in_array($hoy, $dias) ? 'bg-red-50 font-bold text-red-900 dark:bg-red-950/40 dark:text-red-100' : 'text-zinc-700 dark:text-zinc-200' }}">
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
        </article>

        <div
            class="mx-auto w-full max-w-4xl mt-10 border-t border-zinc-200 dark:border-zinc-700 pt-6 flex items-center justify-center gap-3">
            <span class="text-xs font-bold text-zinc-500 uppercase tracking-wide">Compartir:</span>
            <a href="https://wa.me/?text={{ urlencode(url()->current()) }}" target="_blank" rel="noopener"
                class="text-xs bg-zinc-100 hover:bg-zinc-200 text-zinc-700 rounded-full py-1.5 px-3 transition-colors dark:bg-zinc-800 dark:text-white dark:hover:bg-zinc-700">
                WhatsApp
            </a>
            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank"
                rel="noopener"
                class="text-xs bg-zinc-100 hover:bg-zinc-200 text-zinc-700 rounded-full py-1.5 px-3 transition-colors dark:bg-zinc-800 dark:text-white dark:hover:bg-zinc-700">
                Facebook
            </a>
        </div>
    </div>
@endsection
