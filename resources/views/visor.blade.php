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

    <div class="container-flip">
        <div id="book" class="mx-auto">
            @foreach ($paginasCargadas as $p)
                <div class="page" data-density="hard">
                    <img src="{{ $p['url'] }}" alt="Página" loading="lazy">
                </div>
            @endforeach
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const bookElement = document.getElementById("book");

            const pageFlip = new St.PageFlip(bookElement, {
                width: 550, // Dimensiones ajustadas a tus WebP
                height: 800,
                size: "stretch",
                minWidth: 315,
                maxWidth: 1000,
                minHeight: 420,
                maxHeight: 1350,
                maxShadowOpacity: 0.5,
                showCover: true,
                mobileScrollSupport: false
            });

            // Cargar desde el HTML existente
            pageFlip.loadFromHTML(document.querySelectorAll('.page'));

            // Bloqueo de seguridad solicitado
            bookElement.addEventListener('contextmenu', e => e.preventDefault());
        });
    </script>

    <style>
        .container-flip {
            display: flex;
            justify-content: center;
            background: #222;
            padding: 20px;
            border-radius: 8px;
        }

        .page {
            background-color: white;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
        }

        .page img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            user-select: none;
            -webkit-user-drag: none;
        }
    </style>

</body>

</html>
