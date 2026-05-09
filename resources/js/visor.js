import Panzoom from "@panzoom/panzoom";

export function initVisor({ paginas }) {

    const viewer = document.getElementById("viewer");

    const canvas = document.getElementById("page-canvas");

    const ctx = canvas.getContext("2d");

    let currentPage = 0;

    let currentBitmap = null;

    async function renderPage(index) {

        if (!paginas[index]) return;

        currentPage = index;

        try {

            // limpiar canvas anterior
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            // liberar bitmap previo
            if (currentBitmap) {
                currentBitmap.close();
            }

            // descargar protegida
            const response = await fetch(paginas[index].url, {
                credentials: "include",
                cache: "force-cache"
            });

            if (!response.ok) {
                throw new Error("Error cargando imagen");
            }

            // blob
            const blob = await response.blob();

            // bitmap eficiente
            const bitmap = await createImageBitmap(blob);

            currentBitmap = bitmap;

            // tamaño real
            canvas.width = bitmap.width;
            canvas.height = bitmap.height;

            // dibujar
            ctx.drawImage(bitmap, 0, 0);

            // reset zoom
            panzoom.reset();

            // preload siguiente
            preload(index + 1);

        } catch (err) {

            console.error(err);

        }
    }
    const preloadCache = new Map();

    async function preload(index) {

        if (!paginas[index]) return;

        if (preloadCache.has(index)) return;

        try {

            const response = await fetch(paginas[index].url, {
                credentials: "include",
                cache: "force-cache"
            });

            const blob = await response.blob();

            preloadCache.set(index, blob);

        } catch (err) {

            console.error("Error preload", err);

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

    // teclado
    window.addEventListener("keydown", async (e) => {

        if (e.key === "ArrowRight") {
            await nextPage();
        }

        if (e.key === "ArrowLeft") {
            await prevPage();
        }
    });

    // click lateral
    viewer.addEventListener("click", async (e) => {

        const middle = window.innerWidth / 2;

        if (e.clientX > middle) {
            await nextPage();
        } else {
            await prevPage();
        }
    });

    // =========================
    // ZOOM / PAN
    // =========================

    const panzoom = Panzoom(canvas, {

        maxScale: 5,

        minScale: 1,

        contain: "outside"

    });

    // wheel zoom
    viewer.addEventListener("wheel", panzoom.zoomWithWheel);

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

    renderPage(0);
}