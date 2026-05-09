import Panzoom from "@panzoom/panzoom";

export function initVisor({ paginas }) {

    const viewer = document.getElementById("viewer");
    const canvas = document.getElementById("page-canvas");
    const wrapper = document.getElementById("canvas-wrapper");

    const ctx = canvas.getContext("2d");

    let currentPage = 0;
    let currentBitmap = null;

    const preloadCache = new Map();

    const panzoom = Panzoom(wrapper, {
        maxScale: 5,
        minScale: 1,
        contain: "outside"
    });

    viewer.addEventListener("wheel", panzoom.zoomWithWheel);

    async function renderPage(index) {

        if (!paginas[index]) return;

        currentPage = index;

        try {

            ctx.clearRect(0, 0, canvas.width, canvas.height);

            if (currentBitmap) {
                currentBitmap.close();
            }

            let response = await fetch(paginas[index].url, {
                credentials: "include",
                cache: "force-cache"
            });

            if (!response.ok) {
                throw new Error("Error cargando imagen");
            }

            const blob = await response.blob();
            const bitmap = await createImageBitmap(blob);

            currentBitmap = bitmap;

            const rect = viewer.getBoundingClientRect();

            const scale = Math.min(
                rect.width / bitmap.width,
                rect.height / bitmap.height
            );

            const dpr = window.devicePixelRatio || 1;

            // tamaño real (retina)
            canvas.width = bitmap.width * scale * dpr;
            canvas.height = bitmap.height * scale * dpr;

            // tamaño visual
            canvas.style.width = bitmap.width * scale + "px";
            canvas.style.height = bitmap.height * scale + "px";

            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            // IMPORTANTE: sin scale aquí
            ctx.drawImage(bitmap, 0, 0, bitmap.width, bitmap.height);

            panzoom.reset();

            preload(index + 1);

        } catch (err) {
            console.error(err);
        }
    }

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

    async function nextPage() {
        if (currentPage >= paginas.length - 1) return;
        await renderPage(currentPage + 1);
    }

    async function prevPage() {
        if (currentPage <= 0) return;
        await renderPage(currentPage - 1);
    }

    window.addEventListener("keydown", async (e) => {
        if (e.key === "ArrowRight") await nextPage();
        if (e.key === "ArrowLeft") await prevPage();
    });

    viewer.addEventListener("click", async (e) => {
        const middle = window.innerWidth / 2;
        if (e.clientX > middle) await nextPage();
        else await prevPage();
    });

    viewer.addEventListener("contextmenu", e => e.preventDefault());
    viewer.addEventListener("dragstart", e => e.preventDefault());
    viewer.addEventListener("selectstart", e => e.preventDefault());

    window.addEventListener("resize", () => {
        renderPage(currentPage);
    });

    renderPage(0);
}