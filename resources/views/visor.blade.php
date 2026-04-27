<!DOCTYPE html>
<html lang="es" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visor BPEJ - {{ $recurso->titulo }}</title>

    <link rel="stylesheet" href="https://unpkg.com/photoswipe@5.4.3/dist/photoswipe.css">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        .pswp {
            --pswp-bg: #020617;
        }

        /* Fondo azul muy oscuro */
        body {
            background-color: #020617;
        }

        .canvas-container {
            height: calc(100vh - 80px);
        }

        /* Estilos para que los elementos se vean modernos sobre el visor oscuro */
        .pswp__title-container {
            position: absolute;
            left: 20px;
            top: 15px;
            display: flex;
            flex-direction: column;
            z-index: 1000;
        }

        .pswp__title-main {
            color: #f1f5f9;
            font-weight: 700;
            font-size: 14px;
        }

        .pswp__title-sub {
            color: #94a3b8;
            font-size: 11px;
            text-transform: uppercase;
        }

        .pswp__pager-container {
            position: absolute;
            left: 50%;
            bottom: 25px;
            transform: translateX(-50%);
            background: rgba(15, 23, 42, 0.85);
            padding: 6px 15px;
            border-radius: 50px;
            backdrop-filter: blur(8px);
            border: 1px solid rgba(51, 65, 85, 0.5);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .pswp__pager-input {
            background: transparent;
            border: none;
            color: #fff;
            width: 45px;
            text-align: center;
            font-weight: 800;
            outline: none;
        }

        .pswp__pager-total {
            color: #64748b;
            font-size: 12px;
        }

        #reopen-container {
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Evitar que el visor se vea "muerto" al cerrar */
        .pswp--open {
            z-index: 2000 !important;
        }
    </style>
</head>

<body class="overflow-hidden">

    <header
        class="h-16 border-b border-slate-800 flex items-center justify-between px-6 bg-slate-900/50 backdrop-blur-md">
        <div>
            <h1 class="text-slate-100 font-bold text-sm truncate max-w-xs md:max-w-xl">{{ $recurso->titulo }}</h1>
            <p class="text-slate-400 text-xs uppercase tracking-widest">{{ $recurso->autor }}</p>
        </div>

        <div class="flex items-center gap-3 bg-slate-800 px-3 py-1.5 rounded-full border border-slate-700">
            <input type="number" id="goto-page" value="1" min="1" max="{{ count($paginas) }}"
                class="w-12 bg-transparent text-center text-slate-100 font-bold outline-none focus:text-indigo-400">
            <span class="text-slate-500 text-xs font-medium">/ {{ count($paginas) }}</span>
        </div>
        <button id="reopen-btn"
            class="bg-indigo-600 hover:bg-indigo-500 text-white px-8 py-3 rounded-full font-bold shadow-lg transition-all transform hover:scale-105">
            Continuar leyendo
        </button>
    </header>

    <main id="visor-container" class="canvas-container relative flex items-center justify-center">
        <div id="gallery-trigger" class="hidden">
            @foreach ($paginas as $p)
                <a href="{{ $p['url'] }}" data-pswp-width="{{ $p['w'] }}"
                    data-pswp-height="{{ $p['h'] }}" target="_blank">
                    <img src="{{ $p['url'] }}" alt="Página" />
                </a>
            @endforeach
        </div>
        <div class="text-slate-500 animate-pulse text-sm">Iniciando visor de alta resolución...</div>
    </main>
    <div id="reopen-container" class="hidden flex flex-col items-center justify-center gap-4 py-20">
        <div class="text-slate-400 text-center">
            <p class="text-lg">Lectura pausada</p>
            <p id="last-page-info" class="text-xs uppercase tracking-widest text-indigo-400">Página actual: 1</p>
        </div>

    </div>

    <footer class="h-16 border-t border-slate-800 flex items-center justify-center gap-8 bg-slate-900/80">
        <button id="prev-btn" class="text-slate-300 hover:text-white transition-colors p-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>

        <div class="h-1 w-32 bg-slate-800 rounded-full overflow-hidden">
            <div id="progress-bar" class="h-full bg-indigo-500 transition-all duration-300" style="width: 0%"></div>
        </div>

        <button id="next-btn" class="text-slate-300 hover:text-white transition-colors p-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>
    </footer>

    <script type="module">
        import PhotoSwipeLightbox from 'https://unpkg.com/photoswipe@5.4.3/dist/photoswipe-lightbox.esm.js';
        import PhotoSwipe from 'https://unpkg.com/photoswipe@5.4.3/dist/photoswipe.esm.js';

        let lastIndex = 0;

        // 1. Configuración Única
        const options = {
            gallery: '#gallery-trigger',
            children: 'a',
            pswpModule: PhotoSwipe,
            initialZoomLevel: 'fit',
            secondaryZoomLevel: 2,
            maxZoomLevel: 4,
            bgOpacity: 1,
            loop: false, // No bucle
            close: true, // Permitir cerrar para ver el botón de reabrir
            counter: false,
            arrowPrev: false,
            arrowNext: false,
            padding: {
                top: 20,
                bottom: 20,
                left: 20,
                right: 20
            }
        };

        const lightbox = new PhotoSwipeLightbox(options);

        // 2. Registro de Interfaz (Título y Selector)
        lightbox.on('uiRegister', function() {
            lightbox.pswp.ui.registerElement({
                name: 'custom-title',
                order: 9,
                isOrigin: false,
                html: `<div class="pswp__title-container">
                    <span class="pswp__title-main">{{ $recurso->titulo }}</span>
                    <span class="pswp__title-sub">{{ $recurso->autor }}</span>
                   </div>`,
            });

            lightbox.pswp.ui.registerElement({
                name: 'custom-pager',
                order: 10,
                isOrigin: false,
                html: `<div class="pswp__pager-container">
                    <input type="number" id="pswp-input-page" class="pswp__pager-input" value="1">
                    <span class="pswp__pager-total">/ {{ count($paginas) }}</span>
                   </div>`,
            });
        });

        // 3. Manejo de Eventos (Cierre y Cambio)
        lightbox.on('close', () => {
            const container = document.getElementById('reopen-container');
            if (container) container.classList.remove('hidden');

            const info = document.getElementById('last-page-info');
            if (info) info.innerText = `Página actual: ${lastIndex + 1}`;
        });

        lightbox.on('change', () => {
            const curr = lightbox.pswp.currIndex;
            lastIndex = curr; // Guardamos el progreso

            // Actualizar selector interno del visor
            const inputInterno = document.getElementById('pswp-input-page');
            if (inputInterno) inputInterno.value = curr + 1;

            // Actualizar barra de progreso y selector externo (si existen)
            const total = {{ count($paginas) }};
            const inputExterno = document.getElementById('goto-page');
            if (inputExterno) inputExterno.value = curr + 1;

            const progress = document.getElementById('progress-bar');
            if (progress) progress.style.width = ((curr + 1) / total * 100) + '%';
        });

        // 4. Inicialización
        lightbox.init();

        // 5. Funciones Globales (para los botones de tu HTML)
        window.openVisor = (index) => {
            lightbox.loadAndOpen(index);
            const container = document.getElementById('reopen-container');
            if (container) container.classList.add('hidden');
        };

        // Apertura inicial automática
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => window.openVisor(0), 500);
        });

        // Listeners para controles externos
        document.getElementById('reopen-btn').onclick = () => window.openVisor(lastIndex);
        document.getElementById('prev-btn').onclick = () => lightbox.pswp.prev();
        document.getElementById('next-btn').onclick = () => lightbox.pswp.next();

        document.getElementById('goto-page').onchange = (e) => {
            lightbox.pswp.goTo(parseInt(e.target.value) - 1);
        };

        // Salto desde el input interno del visor
        document.addEventListener('change', (e) => {
            if (e.target.id === 'pswp-input-page') {
                lightbox.pswp.goTo(parseInt(e.target.value) - 1);
            }
        });

        document.addEventListener('contextmenu', e => e.preventDefault());
    </script>
</body>

</html>
