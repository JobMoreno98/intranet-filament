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
            overflow: hidden;
            /* Evita scroll innecesario */
        }

        .header {
            padding: 12px 20px;
            background: #020617;
            border-bottom: 1px solid #1e293b;
            flex-shrink: 0;
        }

        .title {
            font-size: 16px;
            font-weight: 600;
        }

        .author {
            font-size: 13px;
            color: #94a3b8;
        }

        .viewer-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            padding: 20px;
        }

        /* Contenedor del libro */
        #book {
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.6);
            background: #000;
        }

        .page {
            background: #111;
            width: 100%;
            height: 100%;
        }

        .page img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            user-select: none;
            pointer-events: none;
        }

        .controls {
            position: absolute;
            bottom: 30px;
            display: flex;
            gap: 20px;
            z-index: 10;
        }

        .btn {
            background: rgba(30, 41, 59, 0.8);
            border: 1px solid #334155;
            color: white;
            padding: 12px 18px;
            border-radius: 50%;
            cursor: pointer;
            backdrop-filter: blur(4px);
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
        const buffer = 2;
        let currentPage = 0;

        function isMobile() {
            return window.innerWidth <= 768;
        }

        function createPage(index) {
            const div = document.createElement("div");
            div.classList.add("page");
            const img = document.createElement("img");
            img.setAttribute("data-index", index);
            div.appendChild(img);
            return div;
        }

        async function getSignedUrl(id) {
            try {
                const res = await fetch(`/media/url/${id}`);
                const data = await res.json();
                return data.url;
            } catch (e) {
                console.error("Error obteniendo URL:", e);
                return null;
            }
        }

        async function loadPage(index) {
            if (loadedPages[index] || !paginas[index]) return;

            // Buscamos el contenedor de la página por su posición en el DOM
            const allPages = document.querySelectorAll(".page");
            const pageDiv = allPages[index];
            if (!pageDiv) return;

            const img = pageDiv.querySelector("img");
            const url = await getSignedUrl(paginas[index].id);
            if (url) {
                img.src = url;
                loadedPages[index] = true;
            }
        }

        function initFlipbook() {
            const bookContainer = document.getElementById("book");
            const mobile = isMobile();

            // Guardar progreso antes de destruir
            if (pageFlip) {
                currentPage = pageFlip.getCurrentPageIndex();
                pageFlip.destroy();
                loadedPages = {};
            }
            bookContainer.innerHTML = "";

            // Lógica de dimensiones:
            // En modo libro (desktop), 'width' es el ancho de UNA página.
            // La librería duplicará ese ancho automáticamente para el visor total.
            let viewWidth, viewHeight;

            if (mobile) {
                viewWidth = window.innerWidth;
                viewHeight = window.innerHeight * 0.7;
            } else {
                viewWidth = 450; // Ancho de una página (total 900px)
                viewHeight = 650;
            }

            pageFlip = new St.PageFlip(bookContainer, {
                width: viewWidth,
                height: viewHeight,
                size: mobile ? "stretch" : "fixed",
                minWidth: 300,
                maxWidth: 500,
                minHeight: 400,
                maxHeight: 700,
                showCover: true,
                usePortrait: mobile, // 🔥 MODO RETRATO: 1 página en móvil
                startPage: currentPage,
                mobileScrollSupport: false,
                clickEventForward: false
            });

            const htmlPages = paginas.map((_, i) => createPage(i));
            pageFlip.loadFromHTML(htmlPages);

            // Cargar páginas adyacentes al iniciar
            for (let i = currentPage - buffer; i <= currentPage + buffer; i++) {
                if (i >= 0) loadPage(i);
            }

            pageFlip.on("flip", (e) => {
                const index = e.data;
                for (let i = index - buffer; i <= index + buffer; i++) {
                    if (i >= 0) loadPage(i);
                }
            });
        }

        function nextPage() {
            pageFlip.flipNext();
        }

        function prevPage() {
            pageFlip.flipPrev();
        }

        let resizeTimeout;
        window.addEventListener("resize", () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(initFlipbook, 300);
        });

        // Seguridad
        document.addEventListener('contextmenu', e => e.preventDefault());
        document.addEventListener('dragstart', e => e.preventDefault());

        initFlipbook();
    </script>
</body>

</html>
