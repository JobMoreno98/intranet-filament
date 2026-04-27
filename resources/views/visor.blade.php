<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>

    <script src="https://unpkg.com/page-flip/dist/js/page-flip.browser.js"></script>

    <style>
        body {
            margin: 0;
            background: #0f172a;
            color: #e5e7eb;
            font-family: system-ui, sans-serif;
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        /* HEADER */
        .header {
            padding: 12px 20px;
            background: #020617;
            border-bottom: 1px solid #1e293b;
        }

        .title {
            font-size: 16px;
            font-weight: 600;
        }

        .author {
            font-size: 13px;
            color: #94a3b8;
        }

        /* CONTENEDOR */
        .viewer-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        /* LIBRO */
        #book {
            width: 100%;
            max-width: 900px;
            margin: auto;
        }

        .page {
            background: black;
        }

        .page img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            user-select: none;
            pointer-events: none;
        }

        /* BOTONES */
        .controls {
            position: absolute;
            bottom: 20px;
            display: flex;
            gap: 10px;
        }

        .btn {
            background: #1e293b;
            border: none;
            color: white;
            padding: 10px 14px;
            border-radius: 8px;
            cursor: pointer;
        }

        .btn:hover {
            background: #334155;
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="title">{{ $titulo }}</div>
        <div class="author">{{ $autor }}</div>
    </div>

    <div class="viewer-container">
        <div id="book"></div>

        <div class="controls">
            <button class="btn" onclick="prevPage()">⬅</button>
            <button class="btn" onclick="nextPage()">➡</button>
        </div>
    </div>

    <script>
        const paginas = @json($paginas);

        let pageFlip;
        let loadedPages = {};
        let buffer = 2;
        let currentPage = 0;

        // detectar móvil
        function isMobile() {
            return window.matchMedia("(max-width: 768px)").matches;
        }

        // crear página vacía
        function createPage(index) {
            const div = document.createElement("div");
            div.classList.add("page");

            const img = document.createElement("img");
            div.appendChild(img);

            return div;
        }

        // obtener URL firmada
        async function getSignedUrl(id) {
            const res = await fetch(`/media/url/${id}`);
            const data = await res.json();
            return data.url;
        }

        // cargar imagen correctamente (sin bug de páginas negras)
        async function loadPage(index) {
            if (loadedPages[index] || !paginas[index]) return;

            const pages = document.querySelectorAll("#book .page");
            const page = pages[index];

            if (!page) return;

            const img = page.querySelector("img");
            if (!img || img.src) return;

            const url = await getSignedUrl(paginas[index].id);
            img.src = url;

            loadedPages[index] = true;
        }

        // inicializar visor
        function initFlipbook() {
            const book = document.getElementById("book");
            const mobile = isMobile();

            if (pageFlip) {
                currentPage = pageFlip.getCurrentPageIndex();
                book.innerHTML = "";
                loadedPages = {};
            }

            const width = mobile ? window.innerWidth * 0.95 : 900;
            const height = mobile ? window.innerHeight * 0.75 : 650;

            pageFlip = new St.PageFlip(book, {
                width: width,
                height: height,
                size: "fixed",
                showCover: true,
                usePortrait: mobile,
                mobileScrollSupport: false,
                maxShadowOpacity: 0.3
            });

            const pages = paginas.map((_, i) => createPage(i));
            pageFlip.loadFromHTML(pages);

            setTimeout(() => {
                pageFlip.update();
                pageFlip.turnToPage(currentPage);

                for (let i = currentPage - buffer; i <= currentPage + buffer; i++) {
                    if (i >= 0 && i < paginas.length) {
                        loadPage(i);
                    }
                }
            }, 300);

            pageFlip.on("flip", (e) => {
                currentPage = e.data;

                for (let i = currentPage - buffer; i <= currentPage + buffer; i++) {
                    if (i >= 0 && i < paginas.length) {
                        loadPage(i);
                    }
                }
            });
        }

        // controles
        function nextPage() {
            pageFlip.flipNext();
        }

        function prevPage() {
            pageFlip.flipPrev();
        }

        // resize inteligente
        let resizeTimeout;

        window.addEventListener("resize", () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                initFlipbook();
            }, 300);
        });

        // bloquear acciones básicas
        document.addEventListener('contextmenu', e => e.preventDefault());
        document.addEventListener('dragstart', e => e.preventDefault());

        // iniciar
        initFlipbook();
    </script>

</body>

</html>
