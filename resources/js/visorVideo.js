// resources/js/video-viewer.js

/**
 * Inicializa el reproductor de video HLS.
 *
 * Requiere que hls.js ya esté cargado en window (por el <script> del CDN
 * en el blade) y que exista un <video id="player"> en el DOM.
 *
 * @param {Object} opts
 * @param {string} opts.src - URL del .m3u8 a reproducir
 */
export function initVideoVisor({ src }) {
    const video = document.getElementById("player");

    if (!video) {
        console.error("initVideoVisor: no se encontró #player en el DOM");
        return;
    }

    if (!src) {
        console.error("initVideoVisor: falta la URL del manifiesto (src)");
        return;
    }

    let hls = null;

    if (window.Hls && Hls.isSupported()) {
        hls = new Hls({
            enableWorker: true,

            lowLatencyMode: false,

            xhrSetup: function (xhr) {
                xhr.withCredentials = true;
            },
        });

        hls.loadSource(src);

        hls.attachMedia(video);

        hls.on(Hls.Events.ERROR, (event, data) => {
            console.error("HLS error:", data);
        });
    } else if (video.canPlayType("application/vnd.apple.mpegurl")) {
        // Safari nativo soporta HLS sin hls.js
        video.src = src;
    } else {
        console.error("Este navegador no soporta reproducción HLS");
    }

    // limpieza si el visor se destruye/navega (SPA, turbo, etc.)
    window.addEventListener(
        "beforeunload",
        () => {
            if (hls) {
                hls.destroy();
            }
        },
        { once: true },
    );

    return hls;
}