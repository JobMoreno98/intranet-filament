<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visor BPEJ - {{ $recurso->titulo }}</title>
    
    <link rel="stylesheet" href="https://unpkg.com/photoswipe@5.4.3/dist/photoswipe.css">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        .pswp { --pswp-bg: #020617; } /* Fondo azul muy oscuro */
        body { background-color: #020617; }
        .canvas-container { height: calc(100vh - 80px); }
    </style>
</head>
<body class="overflow-hidden">

    <header class="h-16 border-b border-slate-800 flex items-center justify-between px-6 bg-slate-900/50 backdrop-blur-md">
        <div>
            <h1 class="text-slate-100 font-bold text-sm truncate max-w-xs md:max-w-xl">{{ $recurso->titulo }}</h1>
            <p class="text-slate-400 text-xs uppercase tracking-widest">{{ $recurso->autor }}</p>
        </div>
        
        <div class="flex items-center gap-3 bg-slate-800 px-3 py-1.5 rounded-full border border-slate-700">
            <input type="number" id="goto-page" value="1" min="1" max="{{ count($paginas) }}"
                   class="w-12 bg-transparent text-center text-slate-100 font-bold outline-none focus:text-indigo-400">
            <span class="text-slate-500 text-xs font-medium">/ {{ count($paginas) }}</span>
        </div>
    </header>

    <main id="visor-container" class="canvas-container relative flex items-center justify-center">
        <div id="gallery-trigger" class="hidden">
            @foreach($paginas as $p)
                <a href="{{ $p['url'] }}" data-pswp-width="{{ $p['w'] }}" data-pswp-height="{{ $p['h'] }}" target="_blank">
                    <img src="{{ $p['url'] }}" alt="Página" />
                </a>
            @endforeach
        </div>
        <div class="text-slate-500 animate-pulse text-sm">Iniciando visor de alta resolución...</div>
    </main>

    <footer class="h-16 border-t border-slate-800 flex items-center justify-center gap-8 bg-slate-900/80">
        <button id="prev-btn" class="text-slate-300 hover:text-white transition-colors p-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </button>
        
        <div class="h-1 w-32 bg-slate-800 rounded-full overflow-hidden">
            <div id="progress-bar" class="h-full bg-indigo-500 transition-all duration-300" style="width: 0%"></div>
        </div>

        <button id="next-btn" class="text-slate-300 hover:text-white transition-colors p-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>
    </footer>

    <script type="module">
        import PhotoSwipeLightbox from 'https://unpkg.com/photoswipe@5.4.3/dist/photoswipe-lightbox.esm.js';
        import PhotoSwipe from 'https://unpkg.com/photoswipe@5.4.3/dist/photoswipe.esm.js';

        const options = {
            gallery: '#gallery-trigger',
            children: 'a',
            pswpModule: PhotoSwipe,
            // Configuraciones de Interfaz
            padding: { top: 20, bottom: 20, left: 20, right: 20 },
            initialZoomLevel: 'fit',
            secondaryZoomLevel: 2,
            maxZoomLevel: 4,
            bgOpacity: 1,
            // Desactivar lo que no queremos
            zoom: true,
            close: false,
            counter: false,
            arrowPrev: false,
            arrowNext: false,
        };

        const lightbox = new PhotoSwipeLightbox(options);
        
        // Inicialización
        lightbox.init();

        // Esperar a que el DOM cargue para abrir automáticamente
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                lightbox.loadAndOpen(0); // Abre la primera página automáticamente
            }, 500);
        });

        // Sincronización con controles externos
        lightbox.on('change', () => {
            const curr = lightbox.pswp.currIndex;
            const total = {{ count($paginas) }};
            document.getElementById('goto-page').value = curr + 1;
            document.getElementById('progress-bar').style.width = ((curr + 1) / total * 100) + '%';
        });

        // Botones de navegación
        document.getElementById('prev-btn').onclick = () => lightbox.pswp.prev();
        document.getElementById('next-btn').onclick = () => lightbox.pswp.next();

        // Salto de página
        document.getElementById('goto-page').onchange = (e) => {
            lightbox.pswp.goTo(parseInt(e.target.value) - 1);
        };

        // Bloqueo de seguridad básica solicitado
        document.addEventListener('contextmenu', e => e.preventDefault());
    </script>
</body>
</html>