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

        const page = paginas[content.index];

        content.data.src = page.url;
    });

    lightbox.on("contentAppend", (e) => {
        const { content } = e;

        const img = content.element;

        if (!(img instanceof HTMLImageElement)) {
            return;
        }

        // ocultar imagen original
        img.style.opacity = "0";
        img.style.pointerEvents = "none";

        // evitar duplicados
        if (content.slide.container.querySelector("canvas")) {
            return;
        }

        const canvas = document.createElement("canvas");

        const ctx = canvas.getContext("2d");

        canvas.style.position = "absolute";
        canvas.style.top = "50%";
        canvas.style.left = "50%";
        canvas.style.transform = "translate(-50%, -50%)";

        canvas.style.maxWidth = "100%";
        canvas.style.maxHeight = "100%";

        canvas.style.objectFit = "contain";

        const renderCanvas = () => {
            canvas.width = img.naturalWidth;
            canvas.height = img.naturalHeight;

            ctx.drawImage(img, 0, 0);

            content.slide.container.appendChild(canvas);
        };

        // IMPORTANTE
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
