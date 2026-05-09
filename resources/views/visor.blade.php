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
            height: 100%;
            overflow: hidden;
            background: #0f172a;
        }

        #viewer {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #020617;
        }

        /* wrapper del canvas (para panzoom) */
        #canvas-wrapper {
            display: inline-block;
        }

        /* canvas limpio */
        canvas {
            display: block;
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

            <!-- VISOR -->
            <div class="w-full md:flex-1 flex flex-col">

                <div id="visor-container" class="w-full flex-1 bg-black overflow-hidden">

                    <div id="viewer" class="w-full h-full flex items-center justify-center">

                        <!-- WRAPPER NECESARIO PARA PANZOOM -->
                        <div id="canvas-wrapper">
                            <canvas id="page-canvas"></canvas>
                        </div>

                    </div>

                </div>

            </div>

            <!-- PANEL DERECHO -->
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
            const canvas = document.getElementById("page-canvas");
            const container = document.getElementById("visor-container");

            function renderCanvas(originalWidth, originalHeight, drawFn) {
                const ctx = canvas.getContext("2d");

                const rect = container.getBoundingClientRect();

                const scale = Math.min(
                    rect.width / originalWidth,
                    rect.height / originalHeight
                );

                const dpr = window.devicePixelRatio || 1;

                canvas.width = originalWidth * scale * dpr;
                canvas.height = originalHeight * scale * dpr;

                canvas.style.width = (originalWidth * scale) + "px";
                canvas.style.height = (originalHeight * scale) + "px";

                ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
                ctx.clearRect(0, 0, canvas.width, canvas.height);

                drawFn(ctx, scale);
            }

            window.initVisor({
                paginas: @json($paginas),
                renderCanvas
            });
        });
    </script>

</body>

</html>
