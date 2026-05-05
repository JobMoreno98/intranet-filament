<!DOCTYPE html>
<html lang="es" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $recurso->titulo }}</title>

    <link rel="stylesheet" href="https://unpkg.com/photoswipe@5.4.3/dist/photoswipe.css">
    <script src="https://cdn.tailwindcss.com"></script>
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
    </style>
</head>

<body class="overflow-hidden">

    <header class="border-b border-slate-800 bg-slate-900 px-4 py-3 text-center sticky top-0 z-10">
        <h1 class="text-white text-sm font-bold">{{ $recurso->titulo }}</h1>
        <p class="text-slate-400 text-xs">{{ $recurso->autor }}</p>

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

        <!-- Desktop mode -->
        <div id="gallery-trigger">
            <div id="gallery-trigger">
                @foreach ($paginas as $p)
                    <a data-id="{{ $p['id'] }}" data-pswp-width="{{ $p['w'] }}"
                        data-pswp-height="{{ $p['h'] }}">
                    </a>
                @endforeach
            </div>
        </div>

    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.initVisor({
                paginas: @json($paginas),
                recursoId: {{ $recurso->id }},
                nombreUser: "{{ auth()->user()->email ?? 'usuario' }}"
            });
        });
    </script>

</body>

</html>
