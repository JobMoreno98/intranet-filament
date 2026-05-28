import Panzoom from "@panzoom/panzoom";

export function initVisor({ paginas, recursoId = 0 }) {
    if (!paginas || paginas.length === 0) {
        console.error("No se recibieron páginas válidas para el visor.");
        return;
    }

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
    // PANZOOM CONFIG
    // =========================
    const panzoom = Panzoom(canvas, {
        startScale: 1.0,
        maxScale: 5,
        minScale: 0.8,
        contain: "invert",
        cursor: "default",
        step: 0.2,
        canvas: true,
        transformOrigin: { x: 0.5, y: 0.5 }
    });

    viewer.addEventListener("wheel", panzoom.zoomWithWheel, { passive: true });

    const preloadCache = new Map();

    async function fetchBlob(index) {
        if (!paginas[index]) return null;

        if (preloadCache.has(index)) {
            const cached = preloadCache.get(index);
            preloadCache.delete(index);
            return cached;
        }

        const response = await fetch(paginas[index].url, {
            credentials: "include",
            cache: "force-cache",
        });

        if (!response.ok) throw new Error(`HTTP Error ${response.status}`);
        return await response.blob();
    }

    async function preload(index) {
        if (!paginas[index] || preloadCache.has(index)) return;

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
    // CORE RENDER
    // =========================
    async function renderPage(index) {
        if (!paginas[index] || rendering) return;

        rendering = true;
        viewer.classList.add("loading");

        try {
            currentPage = index;
            const indicator = document.getElementById("page-indicator");

            if (indicator) {
                indicator.innerText = `${currentPage + 1} / ${paginas.length}`;
            }

            localStorage.setItem(STORAGE_KEY, index);
            panzoom.reset();
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            if (currentBitmap) {
                currentBitmap.close();
                currentBitmap = null;
            }

            const blob = await fetchBlob(index);
            if (!blob) throw new Error("Blob vacío");

            const bitmap = await createImageBitmap(blob);
            currentBitmap = bitmap;

            canvas.width = bitmap.width;
            canvas.height = bitmap.height;
            ctx.drawImage(bitmap, 0, 0);

            // Pre-fetch de seguridad de páginas adyacentes
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

    async function nextPage() {
        if (currentPage >= paginas.length - 1) return;
        await renderPage(currentPage + 1);
    }

    async function prevPage() {
        if (currentPage <= 0) return;
        await renderPage(currentPage - 1);
    }

    // =========================
    // LISTENERS & MULTI-BUTTONS
    // =========================

    // Mapeo dinámico para múltiples botones compartidos (Mobile + Desktop)
    document.querySelectorAll(".btn-next-page").forEach(btn => {
        btn.addEventListener("click", async (e) => {
            e.stopPropagation();
            await nextPage();
        });
    });

    document.querySelectorAll(".btn-prev-page").forEach(btn => {
        btn.addEventListener("click", async (e) => {
            e.stopPropagation();
            await prevPage();
        });
    });

    // Teclado
    window.addEventListener("keydown", async (e) => {
        const tag = document.activeElement.tagName;
        if (tag === "INPUT" || tag === "TEXTAREA") return;

        if (e.key === "ArrowRight") { e.preventDefault(); await nextPage(); }
        if (e.key === "ArrowLeft") { e.preventDefault(); await prevPage(); }
    });

    // Clic en los costados del contenedor del visor
    viewer.addEventListener("click", async (e) => {
        if (panzoom.getScale() > 1.05) return;
        
        // Evitamos disparar si se hizo clic en un botón real
        if (e.target.closest('button')) return;

        const middle = viewer.clientWidth / 2;
        if (e.clientX > middle) {
            await nextPage();
        } else {
            await prevPage();
        }
    });

    // Touch Swipes
    let touchStartX = 0;
    viewer.addEventListener("touchstart", (e) => { touchStartX = e.touches[0].clientX; }, { passive: true });
    viewer.addEventListener("touchend", async (e) => {
        const deltaX = e.changedTouches[0].clientX - touchStartX;
        if (Math.abs(deltaX) < 120 || panzoom.getScale() > 1.07) return;

        if (deltaX < 0) {
            await nextPage();
        } else {
            await prevPage();
        }
    });

    viewer.addEventListener("dblclick", () => { panzoom.zoomIn(); updateZoomLabel(); });
    viewer.addEventListener("contextmenu", (e) => e.preventDefault());
    viewer.addEventListener("dragstart", (e) => e.preventDefault());
    viewer.addEventListener("selectstart", (e) => e.preventDefault());

    // Zoom UI Helpers
    function updateZoomLabel() {
        const scale = panzoom.getScale();
        if (zoomPercent) zoomPercent.innerText = `${Math.round(scale * 100)}%`;
    }

    if (zoomInBtn) zoomInBtn.addEventListener("click", () => { panzoom.zoomIn(); updateZoomLabel(); });
    if (zoomOutBtn) zoomOutBtn.addEventListener("click", () => { panzoom.zoomOut(); updateZoomLabel(); });
    if (resetBtn) resetBtn.addEventListener("click", () => { panzoom.reset(); updateZoomLabel(); });

    viewer.addEventListener("panzoomzoom", () => { updateZoomLabel(); });

    // Inicializar primer render
    renderPage(currentPage);
}

// Al final de tu archivo resources/js/canvas-viewer.js, abajo de todo:

document.addEventListener("DOMContentLoaded", () => {
    const paginasRaw = document.documentElement.getAttribute('data-paginas');
    const recursoIdRaw = document.documentElement.getAttribute('data-recurso-id');

    if (paginasRaw) {
        const paginas = JSON.parse(paginasRaw);
        const recursoId = parseInt(recursoIdRaw || 0);
        
        // Auto-inicialización directa desde el bundle compilado
        initVisor({ paginas, recursoId });
    }
});