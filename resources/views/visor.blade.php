<!DOCTYPE html>
<html lang="es" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $recurso->titulo }}</title>

    <link rel="stylesheet" href="https://unpkg.com/photoswipe@5.4.3/dist/photoswipe.css">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            background: #020617;
        }

        /* Scroll viewer */
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

        /* Desktop gallery */
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

    <main class="h-[calc(100vh-60px)] overflow-auto">

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

    <script type="module">
        import PhotoSwipeLightbox from 'https://unpkg.com/photoswipe@5.4.3/dist/photoswipe-lightbox.esm.js';
        import PhotoSwipe from 'https://unpkg.com/photoswipe@5.4.3/dist/photoswipe.esm.js';
        let lightbox = null;
        const paginas = @json($paginas);
        const STORAGE_KEY = "visor_last_page_{{ $recurso->id }}";

        function isMobile() {
            return window.matchMedia("(max-width: 768px)").matches;
        }

        async function getBlobUrl(id) {
            const res = await fetch(`/media/url/${id}`, {
                credentials: 'include'
            });

            const data = await res.json();

            const imgRes = await fetch(data.url, {
                credentials: 'include'
            });

            const blob = await imgRes.blob();
            return URL.createObjectURL(blob);
        }

        function initScroll() {

            const container = document.getElementById('scroll-viewer');
            container.style.display = 'flex';

            paginas.forEach((p, index) => {
                const div = document.createElement('div');
                div.classList.add('scroll-page');

                const canvas = document.createElement('canvas');
                canvas.dataset.index = index;

                div.appendChild(canvas);
                container.appendChild(div);
            });

            const pages = document.querySelectorAll('.scroll-page');

            const observerProgress = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const index = [...pages].indexOf(entry.target);

                        localStorage.setItem(STORAGE_KEY, index);

                        const btn = document.getElementById('continue-btn');
                        if (btn && btn.classList.contains('hidden')) {
                            btn.classList.remove('hidden');
                        }
                    }
                });
            }, {
                threshold: 0.5
            });

            pages.forEach(p => observerProgress.observe(p));

            const observer = new IntersectionObserver(async (entries, obs) => {
                for (let entry of entries) {
                    if (!entry.isIntersecting) continue;

                    const canvas = entry.target;
                    const index = canvas.dataset.index;

                    await drawImage(canvas, index);
                    obs.unobserve(canvas);
                }
            }, {
                rootMargin: "300px"
            });

            document.querySelectorAll('canvas').forEach(c => observer.observe(c));

            document.querySelectorAll('canvas').forEach((c, i) => {
                if (i < 2) drawImage(c, i);
            });
        }

        async function drawImage(canvas, index) {
            if (canvas.dataset.loaded) return;

            try {
                const blobUrl = await getBlobUrl(paginas[index].id);

                const img = new Image();
                img.src = blobUrl;

                img.onload = () => {
                    const ctx = canvas.getContext('2d');

                    canvas.width = img.width;
                    canvas.height = img.height;

                    ctx.drawImage(img, 0, 0);

                    /* Watermark opcional
                    ctx.font = "20px Arial";
                    ctx.fillStyle = "rgba(255,255,255,0.2)";
                    ctx.fillText("{{ auth()->user()->email ?? 'usuario' }}", 20, 40);
                    */

                    URL.revokeObjectURL(blobUrl);
                    canvas.dataset.loaded = true;
                };

            } catch (e) {
                console.error("Error canvas", index);
            }
        }



        function initDesktop() {

            lightbox = new PhotoSwipeLightbox({
                gallery: '#gallery-trigger',
                children: 'a',
                pswpModule: PhotoSwipe,
                loop: false,
                showHideAnimationType: 'zoom',
                closeOnVerticalDrag: true,
                clickToCloseNonZoomable: true
            });

            // Eventos DESPUÉS de crear la instancia
            lightbox.on('change', () => {
                const index = lightbox.pswp?.currIndex ?? 0;
                localStorage.setItem(STORAGE_KEY, index);

                const btn = document.getElementById('continue-btn');
                if (btn) btn.classList.remove('hidden');
            });

            lightbox.on('contentLoad', async (e) => {
                const {
                    content
                } = e;
                const el = content.data.element;

                if (!el?.dataset.loaded) {
                    try {
                        const blobUrl = await getBlobUrl(el.dataset.id);
                        content.data.src = blobUrl;

                        if (content.element) {
                            content.element.src = blobUrl;
                        }

                        el.dataset.loaded = 'true';
                    } catch (err) {
                        console.error('Error cargando imagen desktop', err);
                    }
                }
            });

            lightbox.init();
        }
        if (isMobile()) {
            initScroll();
        } else {
            initDesktop();
        }

        document.addEventListener('contextmenu', e => e.preventDefault());
        document.addEventListener('dragstart', e => e.preventDefault());

        function initContinueButton() {
            const btn = document.getElementById('continue-btn');

            if (localStorage.getItem(STORAGE_KEY) !== null) {
                btn.classList.remove('hidden');
            }

            btn.onclick = () => {
                const index = parseInt(localStorage.getItem(STORAGE_KEY) || 0);

                if (isMobile()) {
                    const pages = document.querySelectorAll('.scroll-page');
                    const container = document.querySelector('main');
                    const target = pages[index];

                    if (container && target) {
                        container.scrollTo({
                            top: target.offsetTop,
                            behavior: 'smooth'
                        });
                    }
                } else {
                    if (!lightbox) {
                        console.warn('Lightbox no inicializado aún');
                        return;
                    }
                    lightbox.loadAndOpen(index);
                }
            };
        }

        window.addEventListener('DOMContentLoaded', () => {
            initContinueButton();
        });
    </script>

</body>

</html>
