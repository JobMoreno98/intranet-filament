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

        /* 📱 SCROLL VIEWER */
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

        /* 🖥️ DESKTOP */
        #gallery-trigger {
            display: none;
        }
    </style>
</head>

<body class="overflow-hidden">

    <header class="border-b border-slate-800 bg-slate-900 px-4 py-3 text-center">
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

        <!-- 📱 SCROLL -->
        <div id="scroll-viewer"></div>

        <!-- 🖥️ DESKTOP -->
        <div id="gallery-trigger">
            @foreach ($paginas as $p)
                <a data-id="{{ $p['id'] }}"></a>
            @endforeach
        </div>

    </main>

    <script type="module">
        import PhotoSwipeLightbox from 'https://unpkg.com/photoswipe@5.4.3/dist/photoswipe-lightbox.esm.js';
        import PhotoSwipe from 'https://unpkg.com/photoswipe@5.4.3/dist/photoswipe.esm.js';

        const paginas = @json($paginas);
        const STORAGE_KEY = "visor_last_page_{{ $recurso->id }}";

        /* =========================
           📱 DETECTAR MODO
        ========================= */
        function isMobile() {
            return window.matchMedia("(max-width: 768px)").matches;
        }

        /* =========================
           🔐 FETCH PROTEGIDO
        ========================= */
        async function getBlobUrl(id) {

            // 🔥 Este endpoint puede incluir watermark en el futuro
            // Ejemplo:
            // /media/url/${id}?watermark=1

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

        /* =========================
           📱 SCROLL + CANVAS
        ========================= */

        function initScroll() {
            const pages = document.querySelectorAll('.scroll-page');

            const observerProgress = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const index = [...pages].indexOf(entry.target);
                        localStorage.setItem(STORAGE_KEY, index);
                    }
                });
            }, {
                threshold: 0.6
            });

            pages.forEach(p => observerProgress.observe(p));
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

            // carga inicial
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

                    /* =========================
                       💧 WATERMARK (OPCIONAL)
                       =========================

                    // 👉 Activa esto cuando quieras watermark en frontend
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

        /* =========================
           🖥️ PHOTOSWIPE
        ========================= */

        function initDesktop() {
            lightbox.on('change', () => {
                const index = lightbox.pswp.currIndex;
                localStorage.setItem(STORAGE_KEY, index);
            });
            const lightbox = new PhotoSwipeLightbox({
                gallery: '#gallery-trigger',
                children: 'a',
                pswpModule: PhotoSwipe,
                loop: false
            });

            // 🔥 carga bajo demanda
            lightbox.on('contentLoad', async (e) => {
                const {
                    content
                } = e;
                const el = content.data.element;

                if (!el.dataset.loaded) {
                    const blobUrl = await getBlobUrl(el.dataset.id);

                    content.data.src = blobUrl;
                    el.dataset.loaded = true;
                }
            });

            // 🧹 limpiar memoria
            lightbox.on('close', () => {
                document.querySelectorAll('#gallery-trigger a').forEach(a => {
                    if (a.href?.startsWith('blob:')) {
                        URL.revokeObjectURL(a.href);
                        a.href = '';
                    }
                });
            });

            lightbox.init();
            setTimeout(() => lightbox.loadAndOpen(0), 300);
        }

        /* =========================
           🚀 INIT
        ========================= */
        if (isMobile()) {
            initScroll();
        } else {
            initDesktop();
        }

        /* =========================
           🔐 SEGURIDAD BÁSICA
        ========================= */
        document.addEventListener('contextmenu', e => e.preventDefault());
        document.addEventListener('dragstart', e => e.preventDefault());

        function initContinueButton() {
            const btn = document.getElementById('continue-btn');
            const saved = localStorage.getItem(STORAGE_KEY);

            if (saved !== null) {
                btn.classList.remove('hidden');
            }

            btn.onclick = () => {
                const index = parseInt(saved);

                if (isMobile()) {
                    const pages = document.querySelectorAll('.scroll-page');
                    if (pages[index]) {
                        pages[index].scrollIntoView({
                            behavior: 'smooth'
                        });
                    }
                } else {
                    window.openVisor(index);
                }
            };
        }
        window.addEventListener('DOMContentLoaded', () => {
            initContinueButton();
        });
    </script>

</body>

</html>
