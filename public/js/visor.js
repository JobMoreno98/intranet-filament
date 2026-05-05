import PhotoSwipeLightbox from "https://unpkg.com/photoswipe@5.4.3/dist/photoswipe-lightbox.esm.js";
import PhotoSwipe from "https://unpkg.com/photoswipe@5.4.3/dist/photoswipe.esm.js";

let lightbox = null;

const STORAGE_KEY = "visor_last_page_{{ $recurso->id }}";

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
                    if (btn && btn.classList.contains("hidden")) {
                        btn.classList.remove("hidden");
                    }
                }
            });
        },
        {
            threshold: 0.5,
        },
    );

    pages.forEach((p) => observerProgress.observe(p));

    const observer = new IntersectionObserver(
        async (entries, obs) => {
            for (let entry of entries) {
                if (!entry.isIntersecting) continue;

                const canvas = entry.target;
                const index = canvas.dataset.index;

                await drawImage(canvas, index);
                obs.unobserve(canvas);
            }
        },
        {
            rootMargin: "300px",
        },
    );

    document.querySelectorAll("canvas").forEach((c) => observer.observe(c));

    document.querySelectorAll("canvas").forEach((c, i) => {
        if (i < 2) drawImage(c, i);
    });
}

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

            /* Watermark opcional
                    ctx.font = "20px Arial";
                    ctx.fillStyle = "rgba(255,255,255,0.2)";
                    ctx.fillText("{{ auth()->user()->email ?? 'usuario' }}", 20, 40);
                    */

            URL.revokeObjectURL(blobUrl);
            canvas.dataset.loaded = true;
        };
    } catch (e) {
        console.error("Error canvas", index);
    }
}

function initDesktop() {
    lightbox = new PhotoSwipeLightbox({
        gallery: "#gallery-trigger",
        children: "a",
        pswpModule: PhotoSwipe,
        loop: false,
        showHideAnimationType: "zoom",
        closeOnVerticalDrag: true,
        clickToCloseNonZoomable: true,
    });

    lightbox.on("change", () => {
        const index = lightbox.pswp?.currIndex ?? 0;
        localStorage.setItem(STORAGE_KEY, index);

        const btn = document.getElementById("continue-btn");
        if (btn) btn.classList.remove("hidden");
    });

    lightbox.on("contentLoad", async (e) => {
        const { content } = e;
        const el = content.data.element;

        e.preventDefault();

        try {
            const blobUrl = await getBlobUrl(el.dataset.id);

            const img = document.createElement("img");

            img.onload = () => {
                content.element = img;
                if (content.instance) {
                    content.instance.updateSize(true);
                }
            };

            img.src = blobUrl;
        } catch (err) {
            console.error("Error cargando imagen desktop", err);
        }
    });
    lightbox.on("change", async () => {
        const index = lightbox.pswp.currIndex;

        const next = document.querySelectorAll("#gallery-trigger a")[index + 1];

        if (next && !next.dataset.preloaded) {
            getBlobUrl(next.dataset.id).then(() => {
                next.dataset.preloaded = "true";
            });
        }
    });

    lightbox.init();
    const saved = localStorage.getItem(STORAGE_KEY);
    const startIndex = saved ? parseInt(saved) : 0;

    setTimeout(() => {
        lightbox.loadAndOpen(startIndex);
    }, 300);
}
if (isMobile()) {
    initScroll();
} else {
    initDesktop();
}

document.addEventListener("contextmenu", (e) => e.preventDefault());
document.addEventListener("dragstart", (e) => e.preventDefault());

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
            if (!lightbox) {
                console.warn("Lightbox no inicializado aún");
                return;
            }
            lightbox.loadAndOpen(index);
        }
    };
}

window.addEventListener("DOMContentLoaded", () => {
    initContinueButton();
});
