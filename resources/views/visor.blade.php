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
        }

        .header {
            padding: 12px 20px;
            background: #020617;
            border-bottom: 1px solid #1e293b;
            flex-shrink: 0;
        }

        .title { font-size: 16px; font-weight: 600; }
        .author { font-size: 13px; color: #94a3b8; }

        .viewer-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 10px;
        }

        /* Bloqueo estricto de dimensiones para evitar el modo 'spread' */
        #book-container {
            width: 550px;
            height: 750px;
            box-shadow: 0 10px 50px rgba(0, 0, 0, 0.8);
            background: #000;
            overflow: hidden;
        }

        #book {
            width: 100%;
            height: 100%;
        }

        .page {
            background: #000;
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

        .nav-controls {
            margin-top: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            background: #1e293b;
            padding: 10px 25px;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }

        .page-input {
            width: 60px;
            background: #0f172a;
            border: 1px solid #334155;
            color: white;
            text-align: center;
            border-radius: 6px;
            padding: 5px;
            font-size: 14px;
            outline: none;
        }

        .page-input:focus { border-color: #6366f1; }

        .total-pages { color: #94a3b8; font-size: 14px; }

        .btn {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: white;
            padding: 10px 15px;
            border-radius: 50%;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn:hover { background: rgba(255, 255, 255, 0.2); }

        @media (max-width: 600px) {
            #book-container {
                width: 95vw;
                height: 75vh;
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
            
            totalSpan.innerText = paginas.length;
            pageInput.max = paginas.length;

            pageFlip = new St.PageFlip(bookElement, {
                width: 550,
                height: 750,
                size: "stretch",
                displayMode: "portrait", // Obliga a renderizar solo una hoja
                usePortrait: true,       // Desactiva el modo libro (doble hoja)
                showCover: false,        // En modo retrato no necesitamos portada
                flippingTime: 600,
                mobileScrollSupport: false,
                clickEventForward: false
            });

            const htmlPages = paginas.map((_, i) => {
                const div = document.createElement("div");
                div.classList.add("page");
                div.innerHTML = `<img data-index="${i}" src="" style="width:100%;height:100%;object-fit:contain;">`;
                return div;
            });

            pageFlip.loadFromHTML(htmlPages);

            // Evento al terminar la animación de cambio
            pageFlip.on('flip', (e) => {
                const currentIndex = e.data;
                pageInput.value = currentIndex + 1;
                loadAround(currentIndex);
            });

            // Saltar a página al cambiar valor o presionar Enter
            const jumpToPage = () => {
                let val = parseInt(pageInput.value) - 1;
                if (!isNaN(val) && val >= 0 && val < paginas.length) {
                    pageFlip.turnToPage(val);
                } else {
                    pageInput.value = pageFlip.getCurrentPageIndex() + 1;
                }
            };

            pageInput.addEventListener('change', jumpToPage);
            pageInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') jumpToPage();
            });

            // Carga inicial
            loadAround(0);
        }

        async function loadAround(index) {
            // Cargamos la actual, 2 anteriores y 2 siguientes
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
            try {
                const res = await fetch(`/media/url/${id}`);
                const data = await res.json();
                return data.url;
            } catch (error) {
                console.error("Error recuperando URL firmada", error);
            }
        }

        function nextPage() { pageFlip.flipNext(); }
        function prevPage() { pageFlip.flipPrev(); }

        document.addEventListener('DOMContentLoaded', initFlipbook);
        
        // Bloqueos de seguridad
        document.addEventListener('contextmenu', e => e.preventDefault());
        document.addEventListener('dragstart', e => e.preventDefault());
    </script>
</body>
</html>