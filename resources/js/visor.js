import Panzoom from "@panzoom/panzoom";

export function initVisor({ paginas, recursoId = 0 }) {

    const viewer = document.getElementById("viewer");

    const canvas = document.getElementById("page-canvas");

    const ctx = canvas.getContext("2d", {
        alpha: false,
        desynchronized: true,
    });

    const STORAGE_KEY = `visor_page_${recursoId}`;

    let currentPage = parseInt(
        localStorage.getItem(STORAGE_KEY) || 0
    );

    let currentBitmap = null;

    // =========================
    // RENDER
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

            cache: "force-cache"

        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        return await response.blob();
    }

    // =========================
    // PRELOAD
    // =========================

    const preloadCache = new Map();

    async function preload(index) {

        if (!paginas[index]) return;

        if (preloadCache.has(index)) return;

        try {

            const response = await fetch(paginas[index].url, {

                credentials: "include",

                cache: "force-cache"

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

        if (
            tag === "INPUT" ||
            tag === "TEXTAREA"
        ) {
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

    viewer.addEventListener("touchstart", (e) => {

        touchStartX = e.touches[0].clientX;

    }, { passive: true });

    viewer.addEventListener("touchend", async (e) => {

        const deltaX =
            e.changedTouches[0].clientX - touchStartX;

        // swipe horizontal
        if (Math.abs(deltaX) < 50) {
            return;
        }

        // no cambiar página si zoom
        if (panzoom.getScale() > 1.05) {
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

    renderPage(currentPage);
}