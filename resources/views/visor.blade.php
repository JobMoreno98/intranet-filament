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
            font-family: system-ui;
        }

        /* CONTENEDOR */
        .container {
            max-width: 1000px;
            margin: auto;
            padding: 10px;
        }

        /* HEADER CONTROLES */
        .controls-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #020617;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .btn {
            background: #1e293b;
            border: none;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
        }

        .btn:hover {
            background: #334155;
        }

        /* LIBRO */
        .flip-book {
            width: 100%;
            height: 650px;
            margin: auto;
        }

        /* PÁGINA */
        .page {
            background: #111;
            color: white;
        }

        .page-content {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        /* HEADER PÁGINA */
        .page-header {
            padding: 10px;
            font-size: 14px;
            background: #020617;
            border-bottom: 1px solid #1e293b;
        }

        /* IMAGEN */
        .page-image {
            flex: 1;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }

        /* FOOTER */
        .page-footer {
            padding: 6px;
            text-align: center;
            font-size: 12px;
            background: #020617;
            border-top: 1px solid #1e293b;
        }

        /* PORTADA */
        .page-cover {
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 22px;
            background: #020617;
        }

        /* MOBILE */
        @media (max-width: 768px) {
            .flip-book {
                height: 70vh;
            }
        }

        .scroll-viewer {
            display: none;
            flex-direction: column;
            gap: 10px;
            padding: 10px;
        }

        .scroll-page {
            width: 100%;
            background: black;
        }

        .scroll-page img {
            width: 100%;
            height: auto;
            object-fit: contain;

            user-select: none;
            pointer-events: none;
        }
    </style>
</head>

<body>

    <div class="container">

        <!-- CONTROLES -->
        <div class="controls-bar">
            <div>
                <button class="btn" onclick="prevPage()">⬅</button>
                <span>
                    <span id="page-current">1</span> /
                    <span id="page-total">{{ count($paginas) }}</span>
                </span>
                <button class="btn" onclick="nextPage()">➡</button>
            </div>

            <div>
                <strong>{{ $titulo }}</strong> — {{ $autor }}
            </div>
        </div>

        <!-- LIBRO -->
        <div id="book" class="flip-book"></div>

        <div id="scroll-viewer" class="scroll-viewer"></div>

    </div>

    <script>
        const paginas = @json($paginas);

        // evitar múltiples cargas simultáneas
        const loading = new Set();

        function initScrollMode() {
            const container = document.getElementById("scroll-viewer");
            container.innerHTML = "";

            paginas.forEach((p, index) => {
                const div = document.createElement("div");
                div.classList.add("scroll-page");

                const img = document.createElement("img");
                img.dataset.index = index;

                div.appendChild(img);
                container.appendChild(div);
            });

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (!entry.isIntersecting) return;

                    const img = entry.target;
                    const index = img.dataset.index;

                    loadImage(img, index);

                    // 🔥 MUY IMPORTANTE: dejar de observar después de cargar
                    observer.unobserve(img);
                });
            }, {
                root: null,
                rootMargin: "400px",
                threshold: 0.01
            });

            document.querySelectorAll(".scroll-page img").forEach(img => {
                observer.observe(img);
            });
        }

        // 🔥 carga segura
        async function loadImage(img, index) {
            if (img.src || loading.has(index)) return;

            loading.add(index);

            try {
                const res = await fetch(`/media/url/${paginas[index].id}`);
                const data = await res.json();

                img.src = data.url;
            } catch (e) {
                console.error("Error cargando imagen", index);

                // 🔁 reintento automático
                setTimeout(() => loadImage(img, index), 1000);
            } finally {
                loading.delete(index);
            }
        }

        // init
        initScrollMode();
    </script>

</body>

</html>
