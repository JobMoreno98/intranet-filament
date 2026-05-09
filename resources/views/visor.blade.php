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

        canvas {
            display: block;
            max-width: 100%;
            max-height: 100%;
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
                <div class="w-full md:flex-1 flex flex-col">

                    <div id="visor-container" class="w-full flex-1 bg-black overflow-hidden">

                        <div id="viewer" class="w-full h-full flex items-center justify-center">
                            <canvas id="page-canvas"></canvas>
                        </div>

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
            const canvas = document.getElementById("page-canvas");
            const container = document.getElementById("visor-container");

            function renderCanvas(originalWidth, originalHeight, drawFn) {
                const ctx = canvas.getContext("2d");

                const containerWidth = container.clientWidth;
                const containerHeight = container.clientHeight;

                // Escala para que quepa sin deformar (contain)
                const scale = Math.min(
                    containerWidth / originalWidth,
                    containerHeight / originalHeight
                );

                const scaledWidth = originalWidth * scale;
                const scaledHeight = originalHeight * scale;

                // Tamaño real del canvas (alta calidad)
                canvas.width = scaledWidth;
                canvas.height = scaledHeight;

                // Limpiar y renderizar
                ctx.setTransform(1, 0, 0, 1, 0, 0);
                ctx.clearRect(0, 0, canvas.width, canvas.height);

                // Dibujar contenido escalado
                drawFn(ctx, scale);
            }
            window.initVisor({

                paginas: @json($paginas)
                renderCanvas

            });

        });
    </script>

</body>

</html>
