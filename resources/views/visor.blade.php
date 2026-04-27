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

        #book {
            margin: 0 auto;
            width: 100%;
            max-width: 600px;
            /* Limita el ancho en monitores grandes */
            height: 80vh;
            /* Altura relativa a la pantalla */
        }

        .viewer-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: #0f172a;
            height: 100%;
        }

        .page {
            background: #000;
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

        @media (max-width: 768px) {
            #book {
                /* En móvil el contenedor debe ser el 100% para que usePortrait funcione */
                width: 100vw !important;
                height: 80vh !important;
                box-shadow: none;
                /* Quitamos sombras pesadas en móvil */
            }

            .viewer-container {
                padding: 0;
                /* Aprovechar todo el espacio en el celular */
            }
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
                return null;
            }
        }

        async function loadPage(index) {
            if (loadedPages[index] || !paginas[index]) return;
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

            if (pageFlip) {
                currentPage = pageFlip.getCurrentPageIndex();
                pageFlip.destroy();
            }
            bookContainer.innerHTML = "";
            loadedPages = {};

            // CONFIGURACIÓN PARA PÁGINA ÚNICA SIEMPRE
            pageFlip = new St.PageFlip(bookContainer, {
                width: 550, // Ancho de la página
                height: 750, // Alto de la página
                size: "stretch", // Se adapta al contenedor
                showCover: false, // En página única suele verse mejor sin cover
                usePortrait: true, // 🔥 FUERZA UNA SOLA PÁGINA
                mode: "portrait", // 🔥 MODO RETRATO SIEMPRE
                startPage: currentPage,
                mobileScrollSupport: false,
                drawShadow: true,
                maxShadowOpacity: 0.2,
                clickEventForward: false
            });

            const htmlPages = paginas.map((_, i) => createPage(i));
            pageFlip.loadFromHTML(htmlPages);

            // Carga inicial de páginas
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

        window.addEventListener("resize", () => {
            // En modo 'stretch' y 'portrait', la librería maneja 
            // mejor el resize sin necesidad de destruir todo el tiempo.
            pageFlip.update();
        });

        initFlipbook();
    </script>
</body>

</html>
