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
            overflow: hidden;
            background: #020617;
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
    </style>
</head>

<body class="overflow-hidden">

    <!-- HEADER -->
    <header class="border-b border-slate-800 bg-slate-900 px-4 py-3 text-center">

        <h1 class="text-white text-sm font-bold">
            {{ $recurso['titulo'] ?? 'Sin título' }}
        </h1>

        <p class="text-slate-400 text-xs">
            {{ $recurso['autor'] ?? 'Sin autor' }}
        </p>

    </header>

    <!-- MAIN -->
    <main class="h-[calc(100dvh-73px)] overflow-hidden">

        <div class="flex h-full bg-zinc-950">

            <!-- VISOR -->
            <div id="visor-container" class="relative flex-1 h-full">

                <div id="viewer">

                    <canvas id="page-canvas"></canvas>

                </div>

            </div>

            <!-- SIDEBAR -->
            <aside class="hidden lg:block w-96 h-full overflow-y-auto bg-zinc-900 p-6 text-zinc-300">

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
