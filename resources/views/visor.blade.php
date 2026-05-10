<!DOCTYPE html>
<html lang="es" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $recurso['titulo'] ?? 'Sin título' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])


    <style>
        html,
        body {
            margin: 0;
            width: 100%;
            height: 100%;
            background: #020617;
        }

        body {
            overflow-x: hidden;
        }

        #viewer {
            width: 100%;
            height: 100%;

            overflow: hidden;

            display: flex;
            justify-content: center;
            align-items: center;

            position: relative;

            user-select: none;
            -webkit-user-select: none;
        }

        canvas {
            display: block;

            width: auto;
            height: auto;

            max-width: 100%;
            max-height: 100%;

            object-fit: contain;

            cursor: grab;

            image-rendering: auto;
        }

        canvas:active {
            cursor: grabbing;
        }

        #viewer.loading::after {
            content: 'Cargando...';

            position: absolute;

            color: white;

            font-size: 14px;

            top: 50%;
            left: 50%;

            transform: translate(-50%, -50%);
        }

        #viewer {
            min-height: 60vh;
        }
    </style>
</head>

<body class="overflow-x-hidden">

    <!-- MOBILE INFO -->
    <aside class="lg:hidden border-b border-zinc-800 bg-zinc-900 p-3 text-zinc-300">

        <flux:accordion>

            <flux:accordion.item collapsed>

                <flux:accordion.heading>
                    Información del libro
                </flux:accordion.heading>

                <flux:accordion.content>

                    <div class="space-y-4 text-sm pt-2">

                        <div>
                            <span class="block text-zinc-500 uppercase text-xs font-semibold">
                                Título
                            </span>

                            <p>{{ $recurso['titulo'] }}</p>
                        </div>

                        <div>
                            <span class="block text-zinc-500 uppercase text-xs font-semibold">
                                Autor
                            </span>

                            <p>{{ $recurso['autor'] }}</p>
                        </div>

                        <div>
                            <span class="block text-zinc-500 uppercase text-xs font-semibold">
                                Páginas
                            </span>

                            <p>{{ count($paginas) }}</p>
                        </div>

                    </div>

                </flux:accordion.content>

            </flux:accordion.item>

        </flux:accordion>

    </aside>

    <!-- MAIN -->
    <main class="min-h-[calc(100dvh-72px)] lg:h-[calc(100dvh-73px)] overflow-y-auto lg:overflow-hidden">

        <div class="flex flex-col lg:flex-row lg:h-full bg-zinc-950">

            <!-- VISOR -->
            <div id="visor-container" class="relative flex-1 lg:h-full flex flex-col">

                <!-- CANVAS -->
                <div id="viewer" class="flex-1">

                    <canvas id="page-canvas"></canvas>

                </div>
                <!-- PAGE INDICATOR -->
                <div class="lg:hidden px-4 py-2 bg-zinc-900 border-t border-zinc-800 text-center">

                    <p id="page-indicator" class="text-xs text-zinc-400 font-medium">

                        1 / {{ count($paginas) }}

                    </p>

                </div>

                <!-- MOBILE BUTTONS -->
                <div class="lg:hidden flex items-center justify-between gap-3 p-3 border-t border-zinc-800 bg-zinc-900">

                    <button id="prev-page"
                        class="flex-1 rounded-lg bg-zinc-800 hover:bg-zinc-700 text-white py-3 text-sm font-semibold transition">

                        ← Anterior

                    </button>

                    <button id="next-page"
                        class="flex-1 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white py-3 text-sm font-semibold transition">

                        Siguiente →

                    </button>

                </div>


            </div>

            <!-- DESKTOP SIDEBAR -->
            <aside
                class="hidden lg:block w-96 h-full overflow-y-auto bg-zinc-900 p-6 text-zinc-300 border-l border-zinc-800">

                <h2 class="text-xl font-bold text-white mb-4">
                    {{ $recurso['titulo'] }}
                </h2>

                <div class="space-y-4 text-sm">

                    <div>
                        <span class="block text-zinc-500 uppercase text-xs font-semibold">
                            Autor
                        </span>

                        <p>{{ $recurso['autor'] }}</p>
                    </div>

                    <div>
                        <span class="block text-zinc-500 uppercase text-xs font-semibold">
                            Páginas
                        </span>

                        <p>{{ count($paginas) }}</p>
                    </div>

                    <div>
                        <span class="block text-zinc-500 uppercase text-xs font-semibold">
                            Navegación
                        </span>

                        <p>
                            ← Página anterior<br>
                            → Página siguiente<br>
                            Wheel = Zoom
                        </p>
                    </div>

                </div>

            </aside>

        </div>

    </main>
    @fluxScripts
    <script>
        document.addEventListener("DOMContentLoaded", () => {


            window.initVisor({

                paginas: @json($paginas),

                recursoId: {{ $recurso['id'] }}

            });

        });
    </script>

</body>

</html>
