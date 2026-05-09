// resources/js/visor.js

import PhotoSwipeLightbox from "photoswipe/lightbox";
import PhotoSwipe from "photoswipe";

export function initVisor({ paginas, recursoId, nombreUser }) {
    let lightbox = null;

    const STORAGE_KEY = `visor_last_page_${recursoId}`;

    const idle =
        window.requestIdleCallback ||
        function (cb) {
            return setTimeout(cb, 1);
        };

    function isMobile() {
        return window.matchMedia("(max-width: 768px)").matches;
    }

    async function drawImage(canvas, index) {
        if (canvas.dataset.loaded || canvas.dataset.loading) return;

        canvas.dataset.loading = "true";

        try {
            const img = new Image();

            img.decoding = "async";
            img.loading = "lazy";

            img.onload = () => {
                const ctx = canvas.getContext("2d");

                canvas.width = img.width;
                canvas.height = img.height;

                ctx.drawImage(img, 0, 0);

                canvas.dataset.loaded = "true";
                delete canvas.dataset.loading;
            };

            img.onerror = () => {
                delete canvas.dataset.loading;
                console.error("Error cargando imagen", index);
            };

            img.src = paginas[index].url;
        } catch (e) {
            delete canvas.dataset.loading;
            console.error("Error canvas", index, e);
        }
    }

    function releaseCanvas(canvas) {
        const ctx = canvas.getContext("2d");

        ctx?.clearRect(0, 0, canvas.width, canvas.height);

        canvas.width = 1;
        canvas.height = 1;

        delete canvas.dataset.loaded;
        delete canvas.dataset.loading;
    }

    function initScroll() {
        const container = document.getElementById("scroll-viewer");

        if (!container) return;

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
        const canvases = document.querySelectorAll("canvas");

        // Control de progreso + liberación de memoria
        const observerProgress = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;

                    const index = [...pages].indexOf(entry.target);

                    localStorage.setItem(STORAGE_KEY, index);

                    // Mantener solo canvases cercanos
                    canvases.forEach((canvas) => {
                        const i = parseInt(canvas.dataset.index);

                        if (i < index - 5 || i > index + 5) {
                            releaseCanvas(canvas);
                        }
                    });
                });
            },
            {
                threshold: 0.5,
            },
        );

        pages.forEach((p) => observerProgress.observe(p));

        // Lazy render
        const observer = new IntersectionObserver(
            async (entries) => {
                for (const entry of entries) {
                    if (!entry.isIntersecting) continue;

                    const canvas = entry.target;
                    const index = parseInt(canvas.dataset.index);

                    await drawImage(canvas, index);
                }
            },
            {
                rootMargin: "300px",
            },
        );

        canvases.forEach((c) => observer.observe(c));

        // Precargar primeras páginas
        canvases.forEach((c, i) => {
            if (i < 2) {
                drawImage(c, i);
            }
        });
    }

    function initDesktop() {
        const container = document.querySelector("#visor-container");

        if (!container) {
            console.error(
                "Error: No se encontró el elemento #visor-container.",
            );
            return;
        }

        lightbox = new PhotoSwipeLightbox({
            appendToEl: container,
            gallery: "#gallery-trigger",
            children: "a",
            pswpModule: PhotoSwipe,
            loop: false,

            // Desactivamos preload nativo
            preload: [0, 0],
        });

        // Preload manual
        lightbox.on("change", () => {
            const index = lightbox.pswp?.currIndex ?? 0;

            localStorage.setItem(STORAGE_KEY, index);

            const anchors = document.querySelectorAll("#gallery-trigger a");

            [index + 1, index + 2].forEach((i) => {
                const el = anchors[i];

                if (!el || el.dataset.preloaded) return;

                const imgPreload = new Image();

                imgPreload.decoding = "async";

                imgPreload.onload = () => {
                    el.dataset.preloaded = "true";
                };

                imgPreload.src = paginas[i].url;
            });
        });
        /*
        // Carga personalizada
        lightbox.on("contentLoad", (e) => {
            const { content } = e;

            const el = content.data.element;

            e.preventDefault();

            const width = parseInt(el.dataset.pswpWidth);
            const height = parseInt(el.dataset.pswpHeight);

            const imageUrl = paginas[content.index].url;

            const img = document.createElement("img");

            img.src = imageUrl;
            img.className = "pswp__img";
            img.decoding = "async";

            img.onload = () => {
                content.element = img;

                content.width = width;
                content.height = height;

                content.onLoaded();

                if (lightbox.pswp) {
                    lightbox.pswp.updateSize(true);
                }
            };

            img.onerror = () => {
                content.onError();
            };
        });
*/
        lightbox.init();

        // Restaurar lectura
        const saved = localStorage.getItem(STORAGE_KEY);

        if (saved !== null) {
            setTimeout(() => {
                idle(() => {
                    lightbox.loadAndOpen(parseInt(saved));
                });
            }, 500);
        }
    }

    function initContinueButton() {
        const btn = document.getElementById("continue-btn");

        if (!btn) return;

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

    // Protección básica
    document.addEventListener("contextmenu", (e) => e.preventDefault());

    document.addEventListener("dragstart", (e) => e.preventDefault());

    // Inicialización
    if (isMobile()) {
        initScroll();
    } else {
        initDesktop();
    }

    initContinueButton();
}
