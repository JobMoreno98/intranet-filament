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
            background: #0f172a;
        }

        #viewer {
            height: 100dvh;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #020617;
            position: relative;
            user-select: none;
        }

        canvas {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .contenedor {
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .contenedor canvas {
            width: 100%;
            height: 100%;
            display: block;

            transform: none !important;
            transition: none !important;
        }

        #page-canvas {
            transform: none !important;
        }

        #visor-container {
            width: 100%;
            aspect-ratio: 4 / 3;
        }

        @media (min-width: 768px) {
            #visor-container {
                aspect-ratio: 16 / 9;
            }
        }
    </style>
</head>

<body class="overflow-hidden">

    <header class="border-b border-slate-800 bg-slate-900 px-4 py-3 text-center sticky top-0 z-10">
        <h1 class="text-white text-sm font-bold">{{ $recurso['titulo'] ?? 'Sin título' }}</h1>
        <p class="text-slate-400 text-xs">{{ $recurso['autor'] ?? 'Sin autor' }}</p>

        <div class="flex justify-center mt-2">
            <button id="continue-btn"
                class="hidden bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-1.5 text-sm rounded-full font-bold">
                Continuar leyendo
            </button>
        </div>
    </header>

    <main class="h-[calc(100dvh-120px)] overflow-auto">
        <div class="flex flex-col md:flex-row h-screen overflow-hidden bg-zinc-950">
            <div class="w-full md:flex-1 flex flex-col h-1/2 md:h-full">

                <!-- TEXTO arriba en móvil -->
                <div class="w-full p-6 bg-zinc-900 text-zinc-300 md:hidden">
                    <h2 class="text-xl font-bold text-white mb-4">{{ $recurso['titulo'] }}</h2>

                    <div class="space-y-4 text-sm">
                        <div>
                            <span class="block text-zinc-500 uppercase text-xs font-semibold">Autor</span>
                            <p>{{ $recurso['autor'] }}</p>
                        </div>
                    </div>
                </div>

                <div id="visor-container" class="w-full md:flex-1 aspect-[4/3] md:aspect-[16/9] bg-black">

                    <div id="viewer" class="w-full h-full">
                        <canvas id="page-canvas" class="w-full h-full block"></canvas>
                    </div>

                </div>
            </div>

            <!-- PANEL DERECHO (solo desktop) -->
            <div class="hidden md:block w-80 lg:w-96 h-full overflow-y-auto bg-zinc-900 p-6 text-zinc-300">
                <h2 class="text-xl font-bold text-white mb-4">{{ $recurso['titulo'] }}</h2>

                <div class="space-y-4 text-sm">
                    <div>
                        <span class="block text-zinc-500 uppercase text-xs font-semibold">Autor</span>
                        <p>{{ $recurso['autor'] }}</p>
                    </div>
                </div>
            </div>

        </div>
    </main>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const canvas = document.querySelector("canvas");

            function resizeCanvas() {
                const parent = canvas.parentElement;

                canvas.width = parent.clientWidth;
                canvas.height = parent.clientHeight;
            }

            window.addEventListener("resize", resizeCanvas);
            resizeCanvas();

            window.initVisor({

                paginas: @json($paginas)

            });

        });
    </script>

</body>

</html>
