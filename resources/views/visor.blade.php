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

        .viewer-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: #0f172a;
            padding: 10px;
        }

        #book-container {
            /* Este contenedor limita físicamente a la librería */
            width: 550px;
            height: 750px;
            box-shadow: 0 10px 50px rgba(0, 0, 0, 0.8);
        }

        @media (max-width: 600px) {
            #book-container {
                width: 95vw;
                height: 70vh;
            }
        }

        /* Controles de navegación */
        .nav-controls {
            margin-top: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            background: #1e293b;
            padding: 10px 20px;
            border-radius: 50px;
        }

        .page-input {
            width: 50px;
            background: #0f172a;
            border: 1px solid #334155;
            color: white;
            text-align: center;
            border-radius: 4px;
            padding: 4px;
        }

        .total-pages {
            color: #94a3b8;
            font-size: 14px;
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="title">{{ $titulo }}</div>
        <div class="author">{{ $autor }}</div>
    </div>

    <div class="viewer-container">
        <div id="book-container">
            <div id="book"></div>
        </div>

        <div class="nav-controls">
            <button class="btn" onclick="prevPage()">⬅</button>

            <div class="counter">
                <input type="number" id="pageNumber" class="page-input" value="1" min="1">
                <span class="total-pages">de <span id="totalPages">0</span></span>
            </div>

            <button class="btn" onclick="nextPage()">➡</button>
        </div>
    </div>
    <script>
        const paginas = @json($paginas);
        let pageFlip;
        let loadedPages = {};
        const pageInput = document.getElementById('pageNumber');
        const totalSpan = document.getElementById('totalPages');

        function initFlipbook() {
            const bookElement = document.getElementById("book");
            const container = document.getElementById("book-container");

            totalSpan.innerText = paginas.length;
            pageInput.max = paginas.length;

            pageFlip = new St.PageFlip(bookElement, {
                width: 550,
                height: 750,
                size: "stretch",
                displayMode: "portrait", // Fuerza modo retrato
                clickEventForward: false,
                usePortrait: true, // Obliga a una sola página
                showCover: false,
                flippingTime: 800
            });

            const htmlPages = paginas.map((_, i) => {
                const div = document.createElement("div");
                div.classList.add("page");
                div.innerHTML = `<img data-index="${i}" src="" style="width:100%;height:100%;object-fit:contain;">`;
                return div;
            });

            pageFlip.loadFromHTML(htmlPages);

            // Evento al cambiar de página
            pageFlip.on('flip', (e) => {
                const currentIndex = e.data;
                pageInput.value = currentIndex + 1; // El input muestra 1-based index
                loadAround(currentIndex);
            });

            // Evento para saltar a página desde el input
            pageInput.addEventListener('change', () => {
                let val = parseInt(pageInput.value) - 1;
                if (val >= 0 && val < paginas.length) {
                    pageFlip.turnToPage(val);
                } else {
                    pageInput.value = pageFlip.getCurrentPageIndex() + 1;
                }
            });

            loadAround(0);
        }

        async function loadAround(index) {
            for (let i = index - 2; i <= index + 2; i++) {
                if (i >= 0 && i < paginas.length && !loadedPages[i]) {
                    const url = await getSignedUrl(paginas[i].id);
                    const img = document.querySelectorAll('#book img')[i];
                    if (img) {
                        img.src = url;
                        loadedPages[i] = true;
                    }
                }
            }
        }

        async function getSignedUrl(id) {
            const res = await fetch(`/media/url/${id}`);
            const data = await res.json();
            return data.url;
        }

        function nextPage() {
            pageFlip.flipNext();
        }

        function prevPage() {
            pageFlip.flipPrev();
        }

        // Inicialización
        document.addEventListener('DOMContentLoaded', initFlipbook);
    </script>
</body>

</html>
