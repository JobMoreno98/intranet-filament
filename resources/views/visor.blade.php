<div id="galeria">
    @foreach ($recurso->archivos as $archivo)
    @php
    $urlFirmada = URL::temporarySignedRoute('media.stream', now()->addMinutes(60), [
    'archivo_id' => $archivo->id,
    ]);
    @endphp
    <img data-original="{{ $urlFirmada }}" src="{{ $urlFirmada }}" alt="{{ $archivo->nombre_archivo_original }}"
        class="img-thumbnail" style="width: 150px; cursor: zoom-in;">
    @endforeach
</div>

<script>
    // Inicializar el visor
    const viewer = new Viewer(document.getElementById('galeria'), {
        url: 'data-original', // Lee la URL firmada de este atributo
        navbar: true,
        title: false,
        toolbar: {
            zoomIn: 4,
            zoomOut: 4,
            oneToOne: 4,
            reset: 4,
            prev: 4,
            next: 4,
            rotateLeft: 4,
            rotateRight: 4,
            flipHorizontal: 4,
            flipVertical: 4,
            // NO añadimos el botón de descarga aquí
        },
    });
</script>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Visor tipo libro</title>

    <script src="https://unpkg.com/page-flip/dist/js/page-flip.browser.js"></script>

    <style>
        body {
            background: #111;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        #book {
            width: 800px;
            height: 600px;
        }

        .page {
            background: #000;
        }

        .page img {
            width: 100%;
            height: 100%;
            object-fit: contain;

            /* Protección básica */
            user-select: none;
            pointer-events: none;
        }
    </style>
</head>

<body>

    <div id="book"></div>

    <script>
        const paginas = @json($paginas);

        const book = document.getElementById("book");

        const pageFlip = new St.PageFlip(book, {
            width: 400,
            height: 600,
            showCover: true,
            mobileScrollSupport: false
        });

        let loadedPages = {};
        let buffer = 2;

        // Crear página vacía
        function createPage(index) {
            const div = document.createElement("div");
            div.classList.add("page");

            const img = document.createElement("img");
            img.setAttribute("data-index", index);

            div.appendChild(img);
            return div;
        }

        // Obtener URL firmada dinámicamente
        async function getSignedUrl(id) {
            const res = await fetch(`/media/url/${id}`);
            const data = await res.json();
            return data.url;
        }

        // Cargar imagen bajo demanda
        async function loadPage(index) {
            if (loadedPages[index] || !paginas[index]) return;

            const page = book.children[index];
            const img = page.querySelector("img");

            const url = await getSignedUrl(paginas[index].id);

            img.src = url;

            loadedPages[index] = true;
        }

        // Inicializar placeholders
        const initialPages = paginas.map((_, i) => createPage(i));
        pageFlip.loadFromHTML(initialPages);

        // Cargar primeras páginas
        for (let i = 0; i < 3; i++) {
            loadPage(i);
        }

        // Lazy loading al cambiar página
        pageFlip.on("flip", (e) => {
            const current = e.data;

            for (let i = current - buffer; i <= current + buffer; i++) {
                if (i >= 0 && i < paginas.length) {
                    loadPage(i);
                }
            }
        });

        // Bloqueos básicos
        document.addEventListener('contextmenu', e => e.preventDefault());
        document.addEventListener('dragstart', e => e.preventDefault());
    </script>

</body>

</html>