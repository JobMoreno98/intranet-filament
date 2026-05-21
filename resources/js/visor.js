// resources/js/canvas-viewer.js

import Panzoom from "@panzoom/panzoom";

export function initVisor({ paginas, recursoId = 0 }) {
    const viewer = document.getElementById("viewer");

    const canvas = document.getElementById("page-canvas");

    const zoomInBtn = document.getElementById("btn-zoom-in");
    const zoomOutBtn = document.getElementById("btn-zoom-out");
    const resetBtn = document.getElementById("btn-reset-zoom");
    const zoomPercent = document.getElementById("zoom-percent");

    const ctx = canvas.getContext("2d", {
        alpha: false,
        desynchronized: true,
    });

    const STORAGE_KEY = `visor_page_${recursoId}`;

    let currentPage = parseInt(localStorage.getItem(STORAGE_KEY) || 0);

    let currentBitmap = null;

    let rendering = false;

    // =========================
    // PANZOOM
    // =========================

    const panzoom = Panzoom(canvas, {
        startScale: 1.0,
        maxScale: 5,
        minScale: .8,
        contain: "invert",
        cursor: "default",
        step: 0.2,
        canvas: true,
        transformOrigin: { x: 0.5, y: 0.5 }
    });

    viewer.addEventListener("wheel", panzoom.zoomWithWheel, {
        passive: true,
    });

    // =========================
    // CACHE
    // =========================

    const preloadCache = new Map();

    async function fetchBlob(index) {
        if (!paginas[index]) {
            return null;
        }

        // usar cache preload
        if (preloadCache.has(index)) {
            const cached = preloadCache.get(index);

            preloadCache.delete(index);

            return cached;
        }

        const response = await fetch(paginas[index].url, {
            credentials: "include",

            cache: "force-cache",
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        return await response.blob();
    }

    // =========================
    // PRELOAD
    // =========================

    async function preload(index) {
        if (!paginas[index]) return;

        if (preloadCache.has(index)) return;

        try {
            const response = await fetch(paginas[index].url, {
                credentials: "include",

                cache: "force-cache",
            });

            if (!response.ok) return;

            const blob = await response.blob();

            preloadCache.set(index, blob);
        } catch (err) {
            console.error("Preload error", err);
        }
    }

    // =========================
    // RENDER
    // =========================

    async function renderPage(index) {
        if (!paginas[index]) return;

        if (rendering) return;

        rendering = true;

        viewer.classList.add("loading");

        try {
            currentPage = index;

            const indicator = document.getElementById("page-indicator");

            if (indicator) {
                indicator.innerText = `${currentPage + 1} / ${paginas.length}`;
            }

            localStorage.setItem(STORAGE_KEY, index);

            // reset zoom
            panzoom.reset();

            // limpiar canvas
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            // liberar bitmap anterior
            if (currentBitmap) {
                currentBitmap.close();

                currentBitmap = null;
            }

            // obtener blob
            const blob = await fetchBlob(index);

            if (!blob) {
                throw new Error("Blob vacío");
            }

            // bitmap acelerado GPU
            const bitmap = await createImageBitmap(blob);

            currentBitmap = bitmap;

            // tamaño real
            canvas.width = bitmap.width;

            canvas.height = bitmap.height;

            // render
            ctx.drawImage(bitmap, 0, 0);

            // preload alrededor
            preload(index + 1);

            preload(index + 2);

            preload(index - 1);
        } catch (err) {
            console.error("Render error", err);
        } finally {
            viewer.classList.remove("loading");

            rendering = false;
        }
    }

    // =========================
    // NAVEGACIÓN
    // =========================

    async function nextPage() {
        if (currentPage >= paginas.length - 1) return;

        await renderPage(currentPage + 1);
    }

    async function prevPage() {
        if (currentPage <= 0) return;

        await renderPage(currentPage - 1);
    }

    // =========================
    // KEYBOARD
    // =========================

    window.addEventListener("keydown", async (e) => {
        // evitar conflicto escribiendo
        const tag = document.activeElement.tagName;

        if (tag === "INPUT" || tag === "TEXTAREA") {
            return;
        }

        if (e.key === "ArrowRight") {
            e.preventDefault();

            await nextPage();
        }

        if (e.key === "ArrowLeft") {
            e.preventDefault();

            await prevPage();
        }
    });

    // =========================
    // CLICK NAVEGACIÓN
    // =========================

    viewer.addEventListener("click", async (e) => {
        // evitar navegación accidental mientras zoom
        if (panzoom.getScale() > 1.05) {
            return;
        }

        const middle = viewer.clientWidth / 2;

        if (e.clientX > middle) {
            await nextPage();
        } else {
            await prevPage();
        }
    });

    // =========================
    // TOUCH MOBILE
    // =========================

    let touchStartX = 0;

    viewer.addEventListener(
        "touchstart",
        (e) => {
            touchStartX = e.touches[0].clientX;
        },
        { passive: true },
    );

    viewer.addEventListener("touchend", async (e) => {
        const deltaX = e.changedTouches[0].clientX - touchStartX;

        // swipe horizontal
        if (Math.abs(deltaX) < 120) {
            return;
        }

        // no cambiar página si zoom
        if (panzoom.getScale() > 1.07) {
            return;
        }

        if (deltaX < 0) {
            await nextPage();
        } else {
            await prevPage();
        }
    });

    // =========================
    // DOBLE CLICK ZOOM
    // =========================

    viewer.addEventListener("dblclick", () => {
        panzoom.zoomIn();
    });

    // =========================
    // PROTECCIONES
    // =========================

    viewer.addEventListener("contextmenu", (e) => {
        e.preventDefault();
    });

    viewer.addEventListener("dragstart", (e) => {
        e.preventDefault();
    });

    viewer.addEventListener("selectstart", (e) => {
        e.preventDefault();
    });

    // =========================
    // START
    // =========================

    // botones mobile
    const nextBtn = document.getElementById("next-page");

    const prevBtn = document.getElementById("prev-page");

    if (nextBtn) {
        nextBtn.addEventListener("click", async () => {
            await nextPage();
        });
    }

    if (prevBtn) {
        prevBtn.addEventListener("click", async () => {
            await prevPage();
        });
    }

    renderPage(currentPage);

    ////////////////           ---------------------           Zoom

    // Función auxiliar para actualizar el texto del porcentaje de zoom en la interfaz
    function updateZoomLabel() {
        const scale = panzoom.getScale();
        zoomPercent.innerText = `${Math.round(scale * 100)}%`;
    }

    // 3. Vincular Botón de Acercar (+)
    zoomInBtn.addEventListener("click", () => {
        panzoom.zoomIn(); // Zoom nativo de la librería
        updateZoomLabel();
    });

    // 4. Vincular Botón de Alejar (−)
    zoomOutBtn.addEventListener("click", () => {
        panzoom.zoomOut(); // Zoom nativo de la librería
        updateZoomLabel();
    });

    // 5. Vincular Botón de Reiniciar
    resetBtn.addEventListener("click", () => {
        panzoom.reset();
        updateZoomLabel();
    });

    // 6. Mantener activo el zoom con la rueda del mouse (Scroll Wheel)
    viewer.addEventListener("wheel", (event) => {
        event.preventDefault();
        panzoom.zoomWithWheel(event);
        updateZoomLabel(); // Actualiza el porcentaje si usan la rueda
    });

    // 7. Actualizar el porcentaje si usan gestos táctiles (Pellizco)
    viewer.addEventListener("panzoomzoom", () => {
        updateZoomLabel();
    });
}
