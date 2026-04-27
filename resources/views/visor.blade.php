<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.6/viewer.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.6/viewer.min.js"></script>

    <style>
        body {
            margin: 0;
            background: #020617;
            color: #f8fafc;
            font-family: system-ui, sans-serif;
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
        }

        /* Contenedor principal del visor */
        .viewer-wrapper {
            flex: 1;
            position: relative;
            width: 100%;
        }

        /* Ocultamos la lista original de imágenes */
        #images-source {
            display: none;
        }

        /* Estilización del panel de control inferior */
        .custom-footer {
            background: #0f172a;
            padding: 15px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            border-top: 1px solid #1e293b;
            z-index: 1001;
        }

        .nav-btn {
            background: #1e293b;
            border: 1px solid #334155;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }

        .nav-btn:hover {
            background: #334155;
        }

        .page-input-container {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .page-input {
            width: 60px;
            background: #020617;
            border: 1px solid #475569;
            color: #fff;
            text-align: center;
            border-radius: 4px;
            padding: 5px;
            font-weight: bold;
        }

        /* Forzar que el visor ocupe todo el espacio sin navbar */
        .viewer-container {
            background-color: #000 !important;
        }
    </style>
</head>

<body>

    <div class="viewer-wrapper" id="viewer-parent">
        <ul id="images-source">
            @foreach ($paginas as $index => $p)
                <li>
                    <img data-id="{{ $p['id'] }}" src="" alt="Página {{ $index + 1 }}">
                </li>
            @endforeach
        </ul>
    </div>

    <div class="custom-footer">
        <button class="nav-btn" onclick="viewer.prev()">⬅ Anterior</button>

        <div class="page-input-container">
            <input type="number" id="pageNumber" class="page-input" value="1" min="1">
            <span>de {{ count($paginas) }}</span>
        </div>

        <button class="nav-btn" onclick="viewer.next()">Siguiente ➡</button>
    </div>

    <script>
        const paginas = @json($paginas);
        const source = document.getElementById('images-source');
        const pageInput = document.getElementById('pageNumber');
        let viewer;

        async function fetchImageUrl(id) {
            const res = await fetch(`/media/url/${id}`);
            const data = await res.json();
            return data.url;
        }

        function init() {
            viewer = new Viewer(source, {
                inline: true,
                container: '#viewer-parent',
                navbar: false, // 🔥 DESACTIVADO: No más galería/miniaturas abajo
                title: false,
                toolbar: {
                    zoomIn: 4,
                    zoomOut: 4,
                    oneToOne: 4,
                    reset: 4,
                    rotateLeft: 4,
                    rotateRight: 4,
                    flipHorizontal: 4,
                    flipVertical: 4,
                },
                viewed(e) {
                    const index = e.detail.index;
                    pageInput.value = index + 1;

                    // Lazy Load: Cargar solo cuando se visualiza
                    const img = e.detail.image;
                    if (!img.src || img.src === window.location.href) {
                        const id = img.getAttribute('data-id');
                        fetchImageUrl(id).then(url => {
                            img.src = url;
                            viewer.update();
                        });
                    }
                }
            });

            // Navegación por input
            pageInput.addEventListener('change', () => {
                const val = parseInt(pageInput.value) - 1;
                if (val >= 0 && val < paginas.length) {
                    viewer.view(val);
                }
            });

            // Soporte para teclas de flecha
            document.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowLeft') viewer.prev();
                if (e.key === 'ArrowRight') viewer.next();
            });
        }

        // Protección de contenido
        document.addEventListener('contextmenu', e => e.preventDefault());
        document.addEventListener('dragstart', e => e.preventDefault());

        window.onload = init;
    </script>
</body>

</html>
