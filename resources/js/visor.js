// resources/js/canvas-viewer.js

import Panzoom from "@panzoom/panzoom";

export function initVisor({ paginas, recursoId = 0 }) {
    const viewer = document.getElementById("viewer");
    const panzoomContent = document.getElementById("panzoom-content"); // Contenedor nuevo
    const canvas = document.getElementById("page-canvas");
    const ocrLayer = document.getElementById("ocr-layer"); // Capa nueva para el texto
    ocrLayer.style.containerType = "size"; // necesario para que 'cqh' funcione en renderOcrLayer

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

    const panzoom = Panzoom(panzoomContent, {
        startScale: 1.0,
        maxScale: 8,
        minScale: 0.8,
        contain: "invert",
        cursor: "default",
        step: 0.2,
        canvas: true,
        // Al centrar el contenedor, el transform origin debe ser coherente
        transformOrigin: { x: 0.5, y: 0.5 },
        // Clave: si el gesto empieza sobre una palabra del OCR, Panzoom no
        // debe interceptarlo, para permitir seleccionar texto sin mover la imagen
        exclude: [ocrLayer],
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

    /*
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
*/
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
        // evitar navegación al hacer clic sobre una palabra del OCR
        if (e.target.closest("#ocr-layer")) {
            return;
        }

        // evitar navegación accidental si el usuario acaba de seleccionar texto
        if (window.getSelection().toString().length > 0) {
            return;
        }

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
    let touchStartedOnText = false;

    viewer.addEventListener(
        "touchstart",
        (e) => {
            touchStartX = e.touches[0].clientX;
            touchStartedOnText = !!e.target.closest("#ocr-layer");
        },
        { passive: true },
    );

    viewer.addEventListener("touchend", async (e) => {
        // si el gesto empezó sobre una palabra, dejamos que el navegador
        // maneje la selección táctil en vez de interpretarlo como swipe
        if (touchStartedOnText) {
            return;
        }

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

    viewer.addEventListener("dblclick", (e) => {
        // doble clic sobre una palabra = selección de palabra, no zoom
        if (e.target.closest("#ocr-layer")) {
            return;
        }
        panzoom.zoomIn();
    });

    viewer.addEventListener("contextmenu", (e) => {
        e.preventDefault();
    });

    viewer.addEventListener("dragstart", (e) => {
        e.preventDefault();
    });

    viewer.addEventListener("selectstart", (e) => {
        // permitir seleccionar texto solo dentro de la capa OCR
        if (e.target.closest("#ocr-layer")) {
            return;
        }
        e.preventDefault();
    });

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

    async function fetchOcr(index) {
        if (!paginas[index] || !paginas[index].ocrUrl) return null;
        try {
            const response = await fetch(paginas[index].ocrUrl);
            if (!response.ok) return null;
            return await response.json();
        } catch (err) {
            console.error("OCR fetch error", err);
            return null;
        }
    }

    async function renderPage(index) {
        if (!paginas[index]) return;
        if (rendering) return;
        rendering = true;

        viewer.classList.add("loading");

        try {
            currentPage = index;
            // ... (Actualización de indicador y localStorage igual que antes) ...

            panzoom.reset();
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ocrLayer.innerHTML = ""; // Limpiar la capa de texto

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

            // Aseguramos que el contenedor tenga el mismo tamaño exacto del canvas
            panzoomContent.style.width = `${bitmap.width}px`;
            panzoomContent.style.height = `${bitmap.height}px`;

            ctx.drawImage(bitmap, 0, 0);

            // -- NUEVO: Procesar OCR --
            const words = await fetchOcr(index);
            if (words && words.length > 0) {
                renderOcrLayer(words, bitmap.width, bitmap.height);
            }

            preload(index + 1);
            preload(index - 1);
        } catch (err) {
            console.error("Render error", err);
        } finally {
            viewer.classList.remove("loading");
            rendering = false;
        }
    }

    function renderOcrLayer(words, imgWidth, imgHeight) {
        const fragment = document.createDocumentFragment();

        words.forEach((item) => {
            const minX = item.Box.Min.X;
            const minY = item.Box.Min.Y;
            const width = item.Box.Max.X - minX;
            const height = item.Box.Max.Y - minY;

            if (width <= 0 || height <= 0) return;

            const span = document.createElement("span");

            // Clases de Tailwind. pointer-events-auto es necesario porque el
            // contenedor #ocr-layer ahora tiene pointer-events-none (así deja
            // pasar los gestos de pan/zoom hacia el canvas en cualquier zona
            // sin texto), y cada palabra reactiva sus propios eventos.
            span.className =
                "absolute text-transparent cursor-text select-text origin-top-left selection:bg-blue-500/40 selection:text-transparent pointer-events-auto";
            span.textContent = item.Word;

            // 1. Posición y área de selección (en %, así se mantienen
            // correctas sin importar el tamaño real en pantalla ni el zoom)
            span.style.left = `${(minX / imgWidth) * 100}%`;
            span.style.top = `${(minY / imgHeight) * 100}%`;
            span.style.width = `${(width / imgWidth) * 100}%`;
            span.style.height = `${(height / imgHeight) * 100}%`;

            // 2. Tamaño de fuente al tamaño real de la palabra en la imagen.
            // 'cqh' mide contra el tamaño EN PANTALLA de #ocr-layer (que tiene
            // container-type: size), no contra el bitmap, así que el texto
            // sigue midiendo lo correcto tanto si la imagen se ve reducida
            // para caber en el visor como si se hace zoom con Panzoom.
            // El *1.15 compensa que la caja de Tesseract mide ~cap-height,
            // no el font-size completo (que incluye ascendentes/descendentes);
            // ajusta este factor a ojo si el texto se ve chico o grande.
            const heightPercent = (height / imgHeight) * 100;
            span.style.fontSize = `${heightPercent * 1.15}cqh`;
            span.style.lineHeight = `${heightPercent}cqh`;

            // 3. Alineación perfecta del texto dentro de la caja de Tesseract
            span.style.display = "flex";
            span.style.alignItems = "center";
            span.style.justifyContent = "center";
            span.style.whiteSpace = "pre";

            fragment.appendChild(span);
        });

        ocrLayer.appendChild(fragment);
    }
}