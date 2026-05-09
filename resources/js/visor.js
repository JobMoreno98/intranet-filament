// resources/js/visor-canvas-desktop.js

import PhotoSwipeLightbox from "photoswipe/lightbox";
import PhotoSwipe from "photoswipe";

export function initVisor({ paginas }) {
    const container = document.querySelector("#visor-container");

    if (!container) return;

    const lightbox = new PhotoSwipeLightbox({
        appendToEl: container,
        gallery: "#gallery-trigger",
        children: "a",
        pswpModule: PhotoSwipe,
        loop: false,
        preload: [0, 0],
    });

    lightbox.on("contentLoad", (e) => {
        const { content } = e;

        content.data.src = paginas[content.index].url;
    });

    lightbox.on("contentAppend", (e) => {
        const { content } = e;

        const img = content.element;

        if (!(img instanceof HTMLImageElement)) {
            return;
        }

        // evitar doble canvas
        if (img.dataset.canvasRendered) {
            return;
        }

        const renderCanvas = () => {
            const canvas = document.createElement("canvas");

            const ctx = canvas.getContext("2d");

            // tamaño REAL imagen
            canvas.width = img.naturalWidth;
            canvas.height = img.naturalHeight;

            // tamaño VISUAL igual al img de PhotoSwipe
            canvas.style.width = img.style.width;
            canvas.style.height = img.style.height;

            canvas.style.maxWidth = "100%";
            canvas.style.maxHeight = "100%";

            canvas.className = "pswp__img";

            ctx.drawImage(img, 0, 0);

            // ocultar img real
            img.style.display = "none";

            // insertar canvas exactamente donde estaba img
            img.parentNode.insertBefore(canvas, img);

            img.dataset.canvasRendered = "true";
        };

        if (img.complete && img.naturalWidth > 0) {
            renderCanvas();
        } else {
            img.onload = renderCanvas;
        }
    });
    // Liberar memoria
    lightbox.on("contentRemove", (e) => {
        const canvas = e.content.element;

        if (canvas instanceof HTMLCanvasElement) {
            const ctx = canvas.getContext("2d");

            ctx?.clearRect(0, 0, canvas.width, canvas.height);

            canvas.width = 1;
            canvas.height = 1;
        }
    });

    // Preload manual
    lightbox.on("change", () => {
        const index = lightbox.pswp?.currIndex ?? 0;

        [index + 1, index + 2].forEach((i) => {
            if (!paginas[i]) return;

            const img = new Image();

            img.decoding = "async";
            img.src = paginas[i].url;
        });
    });

    lightbox.init();

    // Abrir automáticamente
    lightbox.loadAndOpen(0);

    // Protección básica
    container.addEventListener("contextmenu", (e) => {
        e.preventDefault();
    });

    container.addEventListener("dragstart", (e) => {
        e.preventDefault();
    });
}
