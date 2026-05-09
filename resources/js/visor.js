// resources/js/visor.js

import PhotoSwipeLightbox from "photoswipe/lightbox";
import PhotoSwipe from "photoswipe";

export function initVisor({ paginas, recursoId, nombreUser }) {
    let lightbox = null;

    const STORAGE_KEY = `visor_last_page_${recursoId}`;

    function isMobile() {
        return window.matchMedia("(max-width: 768px)").matches;
    }

    async function getImageUrl(id, isPreload = false) {
        // Construimos la URL con el query string si es preload
        const url = isPreload
            ? `/media/url/${id}?preload=1`
            : `/media/url/${id}`;

        const res = await fetch(url, {
            credentials: "include",
        });

        const data = await res.json();
        // Retornamos la URL directa de la API sin convertir a blob
        return data.url;
    }
    async function drawImage(canvas, index) {
        if (canvas.dataset.loaded) return;

        try {
            const imageUrl = await getImageUrl(paginas[index].id);

            const img = new Image();
            img.src = imageUrl;

            img.onload = () => {
                const ctx = canvas.getContext("2d");

                canvas.width = img.width;
                canvas.height = img.height;

                ctx.drawImage(img, 0, 0);

                // Ya no es necesario revokeObjectURL porque imageUrl es una URL normal
                canvas.dataset.loaded = true;
            };
        } catch (e) {
            console.error("Error canvas", index);
        }
    }

    function initScroll() {
        const container = document.getElementById("scroll-viewer");
        container.style.display = "flex";
        paginas.forEach((p, index) => {
            const div = document.createElement("div");
            div.classList.add("scroll-page");

            const canvas = document.createElement("canvas");
            canvas.dataset.index = index;

            div.appendChild(canvas);
            container.appendChild(div);
        });

        const pages = document.querySelectorAll(".scroll-page");

        const observerProgress = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;

                    const index = [...pages].indexOf(entry.target);
                    const currentIndex = index;

                    localStorage.setItem(STORAGE_KEY, index);

                    // liberar memoria
                    document.querySelectorAll("canvas").forEach((canvas) => {
                        const i = parseInt(canvas.dataset.index);

                        if (i < currentIndex - 5 || i > currentIndex + 5) {
                            canvas.width = 0;
                            canvas.height = 0;
                            canvas.dataset.loaded = "";
                        }
                    });
                });
            },
            { threshold: 0.5 },
        );

        pages.forEach((p) => observerProgress.observe(p));
        const observer = new IntersectionObserver(
            async (entries, obs) => {
                for (let entry of entries) {
                    if (!entry.isIntersecting) continue;

                    const canvas = entry.target;
                    const index = canvas.dataset.index;

                    await drawImage(canvas, index);
                    //obs.unobserve(canvas);
                }
            },
            { rootMargin: "300px" },
        );

        document.querySelectorAll("canvas").forEach((c) => observer.observe(c));

        document.querySelectorAll("canvas").forEach((c, i) => {
            if (i < 2) drawImage(c, i);
        });
    }

    function initDesktop() {
        const container = document.querySelector("#visor-container");

        // 2. Verificación de seguridad
        if (!container) {
            console.error(
                "Error: No se encontró el elemento #visor-container en el DOM.",
            );
            return;
        }
        lightbox = new PhotoSwipeLightbox({
            appendToEl: container,
            gallery: "#gallery-trigger",
            children: "a",
            pswpModule: PhotoSwipe,
            loop: false,
            // Limitamos la precarga nativa para que no compita con tu lógica
            preload: [1, 1],
        });

        // OPTIMIZACIÓN: Preload más ligero
        lightbox.on("change", () => {
            const index = lightbox.pswp?.currIndex ?? 0;
            localStorage.setItem(STORAGE_KEY, index);

            const anchors = document.querySelectorAll("#gallery-trigger a");
            [index + 1, index + 2].forEach((i) => {
                // Solo predecimos hacia adelante
                const el = anchors[i];
                if (el && !el.dataset.preloaded) {
                    const imgPreload = new Image();
                    // Llamamos a la URL de preload pero dejamos que el navegador gestione el cache
                    getImageUrl(el.dataset.id, true).then((url) => {
                        imgPreload.src = url;
                        el.dataset.preloaded = "true";
                    });
                }
            });
        });

        // OPTIMIZACIÓN: Evitar el parpadeo y mejorar el LCP
        lightbox.on("contentLoad", (e) => {
            const { content } = e;
            const el = content.data.element;

            e.preventDefault();

            // 1. IMPORTANTE: Extraer dimensiones del elemento <a>
            // PhotoSwipe necesita saber el aspecto antes de pintar
            const width = parseInt(el.dataset.pswpWidth);
            const height = parseInt(el.dataset.pswpHeight);

            getImageUrl(el.dataset.id).then((imageUrl) => {
                const img = document.createElement("img");
                img.src = imageUrl;
                img.className = "pswp__img";

                img.onload = () => {
                    // 2. Asignar el elemento
                    content.element = img;

                    // 3. Forzar dimensiones en el objeto content
                    // Sin esto, PhotoSwipe v5 calcula un tamaño de 0px
                    content.width = width;
                    content.height = height;

                    // 4. Notificar que la carga terminó
                    content.onLoaded();

                    if (lightbox.pswp) {
                        lightbox.pswp.updateSize(true);
                    }
                };

                img.onerror = () => {
                    content.onError();
                };
            });
        });

        lightbox.init();

        // Solo abrir automáticamente si el usuario no ha interactuado
        const saved = localStorage.getItem(STORAGE_KEY);
        if (saved) {
            setTimeout(() => {
                // Usar delay para que el hilo principal termine de procesar el CSS de Tailwind
                requestIdleCallback(() => {
                    lightbox.loadAndOpen(parseInt(saved));
                });
            }, 500);
        }
    }

    function initContinueButton() {
        const btn = document.getElementById("continue-btn");

        if (localStorage.getItem(STORAGE_KEY) !== null) {
            btn.classList.remove("hidden");
        }

        btn.onclick = () => {
            const index = parseInt(localStorage.getItem(STORAGE_KEY) || 0);

            if (isMobile()) {
                const pages = document.querySelectorAll(".scroll-page");
                const container = document.querySelector("main");
                const target = pages[index];

                if (container && target) {
                    container.scrollTo({
                        top: target.offsetTop - 20,
                        behavior: "smooth",
                    });
                }
            } else {
                lightbox?.loadAndOpen(index);
            }
        };
    }

    document.addEventListener("contextmenu", (e) => e.preventDefault());
    document.addEventListener("dragstart", (e) => e.preventDefault());

    if (isMobile()) {
        initScroll();
    } else {
        initDesktop();
    }

    initContinueButton();
}
