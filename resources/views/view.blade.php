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
