// resources/js/visor-canvas-desktop.js

import PhotoSwipeLightbox from "photoswipe/lightbox";
import PhotoSwipe from "photoswipe";

export function initCanvasDesktop({ paginas }) {
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

    // Renderizar TODO usando canvas
    lightbox.on("contentLoad", async (e) => {
        e.preventDefault();

        const { content } = e;

        const page = paginas[content.index];

        // Canvas principal
        const canvas = document.createElement("canvas");

        canvas.className = "pswp__img";

        const ctx = canvas.getContext("2d");

        try {
            const img = new Image();

            img.decoding = "async";

            img.onload = () => {
                // Dimensiones reales
                canvas.width = img.width;
                canvas.height = img.height;

                // Dibujar imagen
                ctx.drawImage(img, 0, 0);

                // Entregar canvas a PhotoSwipe
                content.element = canvas;

                content.width = img.width;
                content.height = img.height;

                content.onLoaded();

                if (lightbox.pswp) {
                    lightbox.pswp.updateSize(true);
                }
            };

            img.onerror = () => {
                content.onError();
            };

            img.src = page.url;

        } catch (err) {
            console.error(err);
            content.onError();
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