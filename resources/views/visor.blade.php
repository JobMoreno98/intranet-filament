<!DOCTYPE html>
<html lang="es" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $recurso['titulo'] ?? 'Sin título' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            background: #020617;
        }

        #scroll-viewer {
            display: none;
            flex-direction: column;
            gap: 10px;
            padding: 10px;
        }

        .scroll-page {
            width: 100%;
            background: black;
            min-height: 300px;
        }

        .scroll-page canvas {
            width: 100%;
            height: auto;
        }

        #gallery-trigger {
            display: none;
        }

        /* Asegura que PhotoSwipe ocupe solo su contenedor padre */
        #visor-container .pswp {
            position: absolute !important;
            width: 100% !important;
            height: 100% !important;
        }


        /* Ocultar el botón de cerrar nativo si no lo quieres */
        .pswp__button--close {
            display: none !important;
        }

        #visor-container {
            user-select: none;
            -webkit-user-select: none;
        }

        .pswp__content canvas {
            display: block;
            max-width: 100%;
            max-height: 100%;
        }

        .pswp__content {
            overflow: hidden;
        }

        .pswp canvas {
            display: block;
        }

        .pswp canvas.pswp__img {
            object-fit: contain;
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

        <!-- Scroll mode -->
        <div id="scroll-viewer"></div>

        <div class="flex h-full overflow-hidden bg-zinc-950">
            <!-- LADO IZQUIERDO: El Visor -->
            <div id="visor-container" class="relative w-full h-screen">
                <div id="gallery-trigger" style="display:none;">
                    @foreach ($paginas as $p)
                        <a href="{{ $p['url'] }}" data-pswp-width="{{ $p['w'] }}"
                            data-pswp-height="{{ $p['h'] }}"></a>
                    @endforeach
                </div>
            </div>

            <!-- LADO DERECHO: Información del Libro -->
            <div class="w-80 lg:w-96 h-full overflow-y-auto bg-zinc-900 p-6 text-zinc-300">
                <h2 class="text-xl font-bold text-white mb-4">{{ $recurso['titulo'] }}</h2>

                <div class="space-y-4 text-sm">
                    <div>
                        <span class="block text-zinc-500 uppercase text-xs font-semibold">Autor</span>
                        <p>{{ $recurso['autor'] }}</p>
                    </div>
                    <div>
                        <span class="block text-zinc-500 uppercase text-xs font-semibold">Clasificación</span>

                    </div>
                    <!-- Botón para continuar lectura -->
                    <button id="continue-btn" class="hidden w-full py-2 bg-accent text-white rounded-md mt-6">
                        Continuar lectura
                    </button>
                </div>
            </div>
        </div>
    </main>

    <script>
        document
            .getElementById("visor-container")
            ?.addEventListener("dragstart", (e) => {
                e.preventDefault();
            });
        document.addEventListener('DOMContentLoaded', () => {
            window.initVisor({
                paginas: @json($paginas),
                recursoId: {{ $recurso['id'] }},
                nombreUser: "{{ auth()->user()->email ?? 'usuario' }}"
            });
        });
    </script>

</body>

</html>
