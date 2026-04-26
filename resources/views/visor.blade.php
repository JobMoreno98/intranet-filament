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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header .info {
            display: flex;
            flex-direction: column;
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
            width: 900px;
            height: 650px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.6);
            border-radius: 10px;
            overflow: hidden;
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

        /* CONTROLES */
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
            transition: 0.2s;
        }

        .btn:hover {
            background: #334155;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            #book {
                width: 100%;
                height: 80%;
            }
        }
    </style>
</head>

<body>

    <!-- HEADER -->
    <div class="header">
        <div class="info">
            <div class="title">{{ $titulo }}</div>
            <div class="author">{{ $autor }}</div>
        </div>
    </div>

    <!-- VISOR -->
    <div class="viewer-container">
        <div id="book"></div>

        <div class="controls">
            <button class="btn" onclick="prevPage()">⬅</button>
            <button class="btn" onclick="nextPage()">➡</button>
        </div>
    </div>

    <script>
        const paginas = @json($paginas);

        const book = document.getElementById("book");

        const pageFlip = new St.PageFlip(book, {
            width: 450,
            height: 650,
            showCover: true,
            mobileScrollSupport: false
        });

        let loadedPages = {};
        let buffer = 2;

        // Crear páginas vacías
        function createPage(index) {
            const div = document.createElement("div");
            div.classList.add("page");

            const img = document.createElement("img");
            div.appendChild(img);

            return div;
        }

        // Obtener URL firmada
        async function getSignedUrl(id) {
            const res = await fetch(`/media/url/${id}`);
            const data = await res.json();
            return data.url;
        }

        // Lazy load
        async function loadPage(index) {
            if (loadedPages[index] || !paginas[index]) return;

            const page = pageFlip.getPage(index);
            if (!page) return;

            const element = page.element;
            const img = element.querySelector("img");

            const url = await getSignedUrl(paginas[index].id);

            img.src = url;

            loadedPages[index] = true;
        }

        // Inicializar
        const initialPages = paginas.map((_, i) => createPage(i));
        pageFlip.loadFromHTML(initialPages);

        // Primeras páginas
        for (let i = 0; i < 3; i++) {
            loadPage(i);
        }

        // Evento flip
        pageFlip.on("flip", (e) => {
            const current = e.data;

            for (let i = current - buffer; i <= current + buffer; i++) {
                if (i >= 0 && i < paginas.length) {
                    loadPage(i);
                }
            }
        });

        // Controles
        function nextPage() {
            pageFlip.flipNext();
        }

        function prevPage() {
            pageFlip.flipPrev();
        }

        // Seguridad básica
        document.addEventListener('contextmenu', e => e.preventDefault());
        document.addEventListener('dragstart', e => e.preventDefault());
    </script>

</body>

</html>
