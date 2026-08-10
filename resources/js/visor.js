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

    // Palabras OCR de la página actual (en el mismo orden que ocr.json) y los
    // <span> ya insertados en el DOM, indexados 1:1 con currentWords — así la
    // búsqueda puede ir de "coincidencia en el texto" a "elemento a resaltar"
    // sin tener que re-parsear el layer.
    let currentWords = [];
    let ocrSpans = [];

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
    // (la función renderPage real, con soporte de OCR, está más abajo)

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

            const indicator = document.getElementById("page-indicator");
            if (indicator) {
                indicator.innerText = `${currentPage + 1} / ${paginas.length}`;
            }

            localStorage.setItem(STORAGE_KEY, index);

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
            currentWords = [];
            ocrSpans = [];
            limpiarResaltado();
            actualizarContadorBusqueda(null); // limpia el "N resultados" de la página anterior

            const words = await fetchOcr(index);
            if (words && words.length > 0) {
                currentWords = words;
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

        words.forEach((item, idx) => {
            const minX = item.Box.Min.X;
            const minY = item.Box.Min.Y;
            const width = item.Box.Max.X - minX;
            const height = item.Box.Max.Y - minY;

            if (width <= 0 || height <= 0) return;

            const span = document.createElement("span");
            span.dataset.wordIndex = idx;

            // Clases de Tailwind. pointer-events-auto es necesario porque el
            // contenedor #ocr-layer ahora tiene pointer-events-none (así deja
            // pasar los gestos de pan/zoom hacia el canvas en cualquier zona
            // sin texto), y cada palabra reactiva sus propios eventos.
            span.className =
                "absolute text-transparent cursor-text select-text origin-top-left selection:bg-blue-500/40 selection:text-transparent pointer-events-auto";
            span.textContent = item.Word + " "; // espacio final invisible: respaldo si el navegador no usa nuestro handler de "copy"

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
            ocrSpans[idx] = span;
        });

        ocrLayer.appendChild(fragment);
    }

    // =========================
    // BÚSQUEDA Y RESALTADO (solo en la página actual)
    // =========================

    // Clases Tailwind que se agregan/quitan por JS. El texto sigue siendo
    // transparente (viene del span original): lo único que cambia es el
    // fondo, así que visualmente se ve como un marcador sobre la imagen.
    const HIGHLIGHT_CLASSES = ["bg-yellow-400/50", "rounded-[2px]"];
    const ACTIVE_CLASSES = ["bg-orange-500/70", "ring-2", "ring-orange-400"];

    function normalizarTexto(str) {
        return (str || "")
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "") // quita acentos: á->a, ñ se conserva aparte abajo
            .toLowerCase()
            .replace(/[^\p{L}\p{N}\s]/gu, "") // quita puntuación pegada (comas, puntos, etc.)
            .trim();
    }

    function limpiarResaltado() {
        ocrSpans.forEach((span) => {
            if (span) span.classList.remove(...HIGHLIGHT_CLASSES, ...ACTIVE_CLASSES);
        });
    }

    function actualizarContadorBusqueda(total) {
        const el = document.getElementById("ocr-search-count");
        if (!el) return;
        if (total === null) {
            el.textContent = "";
        } else if (total === 0) {
            el.textContent = "Sin resultados en esta página";
        } else {
            el.textContent = `${total} resultado${total === 1 ? "" : "s"} en esta página`;
        }
    }

    /**
     * Busca una frase (una o varias palabras) dentro del OCR de la página
     * que se está viendo actualmente y resalta todas las coincidencias.
     * No busca en otras páginas ni en el resto del documento.
     */
    function buscarEnPaginaActual(query) {
        limpiarResaltado();

        const queryNorm = normalizarTexto(query);
        if (!queryNorm) {
            actualizarContadorBusqueda(null);
            return { total: 0 };
        }

        const tokens = queryNorm.split(/\s+/).filter(Boolean);
        const matches = [];

        for (let i = 0; i <= currentWords.length - tokens.length; i++) {
            let ok = true;
            let prevItem = null;

            for (let t = 0; t < tokens.length; t++) {
                const item = currentWords[i + t];
                if (!item) {
                    ok = false;
                    break;
                }

                if (normalizarTexto(item.Word) !== tokens[t]) {
                    ok = false;
                    break;
                }

                // Confirma que las palabras estén en el mismo renglón, para no
                // encadenar palabras que casualmente quedaron seguidas en el
                // arreglo pero pertenecen a líneas o bloques distintos.
                if (prevItem) {
                    const prevCenterY = (prevItem.Box.Min.Y + prevItem.Box.Max.Y) / 2;
                    const curCenterY = (item.Box.Min.Y + item.Box.Max.Y) / 2;
                    const lineHeight = item.Box.Max.Y - item.Box.Min.Y || 1;

                    if (Math.abs(curCenterY - prevCenterY) > lineHeight * 0.6) {
                        ok = false;
                        break;
                    }
                }

                prevItem = item;
            }

            if (ok) {
                matches.push({ start: i, end: i + tokens.length - 1 });
                i += tokens.length - 1; // no solapar coincidencias consecutivas
            }
        }

        matches.forEach((m) => {
            for (let idx = m.start; idx <= m.end; idx++) {
                const span = ocrSpans[idx];
                if (span) span.classList.add(...HIGHLIGHT_CLASSES);
            }
        });

        if (matches.length > 0) {
            for (let idx = matches[0].start; idx <= matches[0].end; idx++) {
                const span = ocrSpans[idx];
                if (span) span.classList.add(...ACTIVE_CLASSES);
            }
        }

        actualizarContadorBusqueda(matches.length);
        return { total: matches.length, matches };
    }

    // -- Conectar con el input de búsqueda (si existe en el Blade) --
    const searchInput = document.getElementById("ocr-search-input");
    const searchBtn = document.getElementById("ocr-search-btn");

    function ejecutarBusqueda() {
        if (!searchInput) return;
        buscarEnPaginaActual(searchInput.value);
    }

    if (searchBtn) {
        searchBtn.addEventListener("click", ejecutarBusqueda);
    }

    if (searchInput) {
        searchInput.addEventListener("keydown", (e) => {
            if (e.key === "Enter") {
                e.preventDefault();
                ejecutarBusqueda();
            }
        });

        // Si borra el texto, limpia el resaltado de inmediato
        searchInput.addEventListener("input", () => {
            if (searchInput.value.trim() === "") {
                limpiarResaltado();
                actualizarContadorBusqueda(null);
            }
        });
    }

    // =========================
    // COPIAR TEXTO CON SALTOS DE LÍNEA
    // =========================

    // Cada palabra es un <span> posicionado con "absolute": para el navegador
    // no existe ningún salto de línea real entre ellas, así que una selección
    // de varias líneas se copia como un solo renglón pegado. Aquí interceptamos
    // el copiado y reconstruimos el texto agrupando por renglón (misma lógica
    // de "misma línea" que usa la búsqueda), insertando "\n" entre líneas.
    // Nota: esto reconstruye saltos de línea DENTRO de la página que se está
    // viendo. Como el visor solo mantiene una página en el DOM a la vez, no
    // es posible seleccionar texto que cruce dos páginas distintas.
    /**
     * Toma una lista de items OCR (en el orden en que aparecen en ocr.json)
     * y arma el texto agrupándolos por renglón, insertando "\n" entre líneas.
     * La comparten tanto el copiado manual (selección) como el botón "Copiar página".
     */
    function construirTextoDesdeWords(words) {
        const lineas = [];
        let lineaActual = [];
        let prevItem = null;

        words.forEach((item) => {
            if (prevItem) {
                const prevCenterY = (prevItem.Box.Min.Y + prevItem.Box.Max.Y) / 2;
                const curCenterY = (item.Box.Min.Y + item.Box.Max.Y) / 2;
                const lineHeight = item.Box.Max.Y - item.Box.Min.Y || 1;

                if (Math.abs(curCenterY - prevCenterY) > lineHeight * 0.6) {
                    lineas.push(lineaActual.join(" "));
                    lineaActual = [];
                }
            }

            lineaActual.push(item.Word);
            prevItem = item;
        });

        if (lineaActual.length > 0) {
            lineas.push(lineaActual.join(" "));
        }

        return lineas.join("\n").trim();
    }

    document.addEventListener("copy", (e) => {
        const selection = window.getSelection();
        if (!selection || selection.rangeCount === 0 || selection.isCollapsed) {
            return;
        }

        // Solo intervenimos si la selección empieza o termina dentro del OCR
        const anchorInLayer = ocrLayer.contains(selection.anchorNode);
        const focusInLayer = ocrLayer.contains(selection.focusNode);
        if (!anchorInLayer && !focusInLayer) {
            return;
        }

        const seleccionadas = [];
        ocrSpans.forEach((span, idx) => {
            if (span && selection.containsNode(span, true) && currentWords[idx]) {
                seleccionadas.push(currentWords[idx]);
            }
        });

        const texto = construirTextoDesdeWords(seleccionadas);
        if (texto) {
            e.clipboardData.setData("text/plain", texto);
            e.preventDefault();
        }
    });

    // -- Botón "Copiar página": copia TODO el texto OCR de la página actual
    // de un clic, sin necesidad de seleccionar manualmente con el mouse --
    const copyPageBtn = document.getElementById("ocr-copy-page-btn");

    if (copyPageBtn) {
        copyPageBtn.addEventListener("click", async () => {
            if (!currentWords || currentWords.length === 0) return;

            const texto = construirTextoDesdeWords(currentWords);
            if (!texto) return;

            try {
                await navigator.clipboard.writeText(texto);

                const original = copyPageBtn.textContent;
                copyPageBtn.textContent = "¡Copiado!";
                copyPageBtn.disabled = true;

                setTimeout(() => {
                    copyPageBtn.textContent = original;
                    copyPageBtn.disabled = false;
                }, 1500);
            } catch (err) {
                console.error("No se pudo copiar la página", err);
            }
        });
    }
}