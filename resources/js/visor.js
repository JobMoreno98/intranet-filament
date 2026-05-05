// resources/js/visor.js

import PhotoSwipeLightbox from "photoswipe/lightbox";
import PhotoSwipe from "photoswipe";

export function initVisor({ paginas, recursoId, nombreUser }) {
    let lightbox = null;

    const STORAGE_KEY = `visor_last_page_${recursoId}`;

    function isMobile() {
        return window.matchMedia("(max-width: 768px)").matches;
    }

    async function getBlobUrl(id) {
        const res = await fetch(`/media/url/${id}`, {
            credentials: "include",
        });

        const data = await res.json();

        const imgRes = await fetch(data.url, {
            credentials: "include",
        });

        const blob = await imgRes.blob();
        return URL.createObjectURL(blob);
    }

    // 👇 usa nombreUser en lugar de variable Blade
    async function drawImage(canvas, index) {
        if (canvas.dataset.loaded) return;

        try {
            const blobUrl = await getBlobUrl(paginas[index].id);

            const img = new Image();
            img.src = blobUrl;

            img.onload = () => {
                const ctx = canvas.getContext("2d");

                canvas.width = img.width;
                canvas.height = img.height;

                ctx.drawImage(img, 0, 0);

                // watermark opcional
                /*
                ctx.font = "20px Arial";
                ctx.fillStyle = "rgba(255,255,255,0.2)";
                ctx.fillText(nombreUser, 20, 40);
                */

                URL.revokeObjectURL(blobUrl);
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
                    if (entry.isIntersecting) {
                        const index = [...pages].indexOf(entry.target);

                        localStorage.setItem(STORAGE_KEY, index);

                        const btn = document.getElementById("continue-btn");
                        if (btn) btn.classList.remove("hidden");
                    }
                });
            },
            { threshold: 0.5 },
        );

        pages.forEach((p) => observerProgress.observe(p));

        const observerProgress = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;

                    const pages = document.querySelectorAll(".scroll-page");
                    const index = [...pages].indexOf(entry.target);

                    const currentIndex = index;

                    localStorage.setItem(STORAGE_KEY, index);

                    const btn = document.getElementById("continue-btn");
                    if (btn) btn.classList.remove("hidden");

                    document.querySelectorAll("canvas").forEach((canvas) => {
                        const i = parseInt(canvas.dataset.index);

                        if (i < currentIndex - 10 || i > currentIndex + 10) {
                            canvas.width = 0;
                            canvas.height = 0;
                            canvas.dataset.loaded = "";
                        }
                    });
                });
            },
            {
                threshold: 0.5,
            },
        );

        document.querySelectorAll("canvas").forEach((c) => observer.observe(c));

        document.querySelectorAll("canvas").forEach((c, i) => {
            if (i < 2) drawImage(c, i);
        });
    }

    function initDesktop() {
        lightbox = new PhotoSwipeLightbox({
            gallery: "#gallery-trigger",
            children: "a",
            pswpModule: PhotoSwipe,
            loop: false,
        });

        lightbox.on("change", () => {
            const index = lightbox.pswp?.currIndex ?? 0;
            localStorage.setItem(STORAGE_KEY, index);
            const btn = document.getElementById("continue-btn");
            if (btn) btn.classList.remove("hidden");

            const anchors = document.querySelectorAll("#gallery-trigger a");

            [index - 1, index + 1].forEach((i) => {
                const el = anchors[i];

                if (el && !el.dataset.preloaded) {
                    getBlobUrl(el.dataset.id).then(() => {
                        el.dataset.preloaded = "true";
                    });
                }
            });
        });

        lightbox.on("contentLoad", async (e) => {
            const { content } = e;
            const el = content.data.element;

            e.preventDefault();

            const blobUrl = await getBlobUrl(el.dataset.id);

            const img = document.createElement("img");

            img.onload = () => {
                content.element = img;

                requestAnimationFrame(() => {
                    lightbox.pswp?.updateSize(true);
                });
            };

            img.src = blobUrl;
        });
        document.addEventListener("keydown", (e) => {
            if (e.key === "PrintScreen") {
                navigator.clipboard.writeText("");
            }
        });

        lightbox.init();

        const saved = localStorage.getItem(STORAGE_KEY);
        const startIndex = saved ? parseInt(saved) : 0;

        setTimeout(() => {
            lightbox.loadAndOpen(startIndex);
        }, 300);
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
