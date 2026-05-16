<!DOCTYPE html>
<html lang="es" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $recurso['titulo'] ?? 'Sin título' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @fluxAppearance

    <style>
        html,
        body {
            margin: 0;
            width: 100%;
            height: 100%;
            background: #020617;
        }

        body {
            overflow-x: hidden;
        }

        #viewer {
            width: 100%;
            height: 100%;

            overflow: hidden;

            display: flex;
            justify-content: center;
            align-items: center;

            position: relative;

            user-select: none;
            -webkit-user-select: none;
        }

        canvas {
            display: block;

            width: auto;
            height: auto;

            max-width: 100%;
            max-height: 100%;

            object-fit: contain;

            cursor: grab;

            image-rendering: auto;
        }

        canvas:active {
            cursor: grabbing;
        }

        #viewer.loading::after {
            content: 'Cargando...';

            position: absolute;

            color: white;

            font-size: 14px;

            top: 50%;
            left: 50%;

            transform: translate(-50%, -50%);
        }

        #viewer {
            min-height: 60vh;
        }
    </style>
</head>

<body class="overflow-x-hidden">


    @fluxScripts
    <script>
        document.addEventListener("DOMContentLoaded", () => {


            window.initVisor({

                paginas: @json($paginas),

                recursoId: {{ $recurso['IdElemento'] }}

            });

        });
    </script>

</body>

</html>
