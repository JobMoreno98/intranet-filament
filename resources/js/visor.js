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

    lightbox.on("uiRegister", () => {
        lightbox.pswp.on("change", () => {
            const currSlide = lightbox.pswp.currSlide;

            if (!currSlide) return;

            const container = currSlide.container;

            if (!container) return;

            const img = container.querySelector("img");

            if (!img) return;

            // evitar duplicados
            if (container.querySelector("canvas")) return;

            const render = () => {
                const canvas = document.createElement("canvas");

                const ctx = canvas.getContext("2d");

                canvas.width = img.naturalWidth;
                canvas.height = img.naturalHeight;

                // copiar tamaño visual REAL
                canvas.style.width = img.clientWidth + "px";
                canvas.style.height = img.clientHeight + "px";

                canvas.style.position = "absolute";
                canvas.style.left = img.offsetLeft + "px";
                canvas.style.top = img.offsetTop + "px";

                canvas.style.pointerEvents = "none";

                ctx.drawImage(img, 0, 0);

                container.appendChild(canvas);

                // ocultar imagen original
                img.style.opacity = "0";
            };

            if (img.complete && img.naturalWidth > 0) {
                render();
            } else {
                img.onload = render;
            }
        });
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
