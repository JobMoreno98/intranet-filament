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

    // Renderizar TODO usando canvas
    lightbox.on("contentLoad", (e) => {
        e.preventDefault();

        const { content } = e;

        const page = paginas[content.index];

        // Wrapper compatible con PhotoSwipe
        const wrapper = document.createElement("div");

        wrapper.className = "pswp__img";

        wrapper.style.width = "100%";
        wrapper.style.height = "100%";
        wrapper.style.display = "flex";
        wrapper.style.alignItems = "center";
        wrapper.style.justifyContent = "center";

        // Canvas
        const canvas = document.createElement("canvas");

        canvas.style.maxWidth = "100%";
        canvas.style.maxHeight = "100%";
        canvas.style.objectFit = "contain";

        wrapper.appendChild(canvas);

        const ctx = canvas.getContext("2d");

        const img = new Image();

        img.crossOrigin = "use-credentials";
        img.decoding = "async";

        img.onload = () => {
            canvas.width = img.width;
            canvas.height = img.height;

            ctx.drawImage(img, 0, 0);

            content.element = wrapper;

            content.width = img.width;
            content.height = img.height;

            content.state = "loaded";

            content.onLoaded();

            lightbox.pswp?.updateSize(true);
        };

        img.onerror = () => {
            console.error("Error cargando imagen");
            content.onError();
        };

        img.src = page.url;
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
