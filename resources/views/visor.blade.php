<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>{{ $titulo }}</title>

<script src="https://unpkg.com/page-flip/dist/js/page-flip.browser.js"></script>

<style>
body {
    margin: 0;
    background: #0f172a;
    color: #e5e7eb;
    font-family: system-ui, sans-serif;

    display: flex;
    flex-direction: column;
    height: 100vh;
}

/* HEADER */
.header {
    padding: 12px 20px;
    background: #020617;
    border-bottom: 1px solid #1e293b;
}

.title {
    font-size: 16px;
    font-weight: 600;
}

.author {
    font-size: 13px;
    color: #94a3b8;
}

/* CONTENEDOR PRINCIPAL */
.main {
    flex: 1;
    display: flex;
    flex-direction: column;
}

/* VISOR */
.viewer {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
}

#book {
    width: 100%;
    max-width: 900px;
}

/* PÁGINAS */
.page {
    background: black;
}

.page img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    user-select: none;
    pointer-events: none;
}

/* CONTROLES (SIN ABSOLUTE) */
.controls {
    display: flex;
    justify-content: center;
    gap: 12px;
    padding: 12px;
    background: #020617;
    border-top: 1px solid #1e293b;
}

.btn {
    background: #1e293b;
    border: none;
    color: white;
    padding: 10px 16px;
    border-radius: 8px;
    cursor: pointer;
}

.btn:hover {
    background: #334155;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    #book {
        max-width: 100%;
    }
}
</style>
</head>

<body>

<div class="header">
    <div class="title">{{ $titulo }}</div>
    <div class="author">{{ $autor }}</div>
</div>

<div class="main">

    <div class="viewer">
        <div id="book"></div>
    </div>

    <div class="controls">
        <button class="btn" onclick="prevPage()">⬅ Anterior</button>
        <button class="btn" onclick="nextPage()">Siguiente ➡</button>
    </div>

</div>

<script>
const paginas = @json($paginas);

let pageFlip;
let loadedPages = {};
let buffer = 2;
let currentPage = 0;

function isMobile() {
    return window.matchMedia("(max-width: 1250px)").matches;
}

function createPage(index) {
    const div = document.createElement("div");
    div.classList.add("page");

    const img = document.createElement("img");
    div.appendChild(img);

    return div;
}

async function getSignedUrl(id) {
    const res = await fetch(`/media/url/${id}`);
    const data = await res.json();
    return data.url;
}

async function loadPage(index) {
    if (loadedPages[index] || !paginas[index]) return;

    const pages = document.querySelectorAll("#book .page");
    const page = pages[index];

    if (!page) return;

    const img = page.querySelector("img");
    if (!img || img.src) return;

    const url = await getSignedUrl(paginas[index].id);
    img.src = url;

    loadedPages[index] = true;
}

function initFlipbook() {
    const book = document.getElementById("book");
    const mobile = isMobile();

    if (pageFlip) {
        currentPage = pageFlip.getCurrentPageIndex();
        book.innerHTML = "";
        loadedPages = {};
    }

    const width = mobile ? window.innerWidth * 0.95 : 900;
    const height = mobile ? window.innerHeight * 0.65 : 650;

    pageFlip = new St.PageFlip(book, {
        width: width,
        height: height,
        size: "fixed",
        showCover: true,
        usePortrait: mobile,
        mobileScrollSupport: false
    });

    const pages = paginas.map((_, i) => createPage(i));
    pageFlip.loadFromHTML(pages);

    setTimeout(() => {
        pageFlip.update();
        pageFlip.turnToPage(currentPage);

        for (let i = currentPage - buffer; i <= currentPage + buffer; i++) {
            if (i >= 0 && i < paginas.length) {
                loadPage(i);
            }
        }
    }, 300);

    pageFlip.on("flip", (e) => {
        currentPage = e.data;

        for (let i = currentPage - buffer; i <= currentPage + buffer; i++) {
            if (i >= 0 && i < paginas.length) {
                loadPage(i);
            }
        }
    });
}

function nextPage() {
    pageFlip.flipNext();
}

function prevPage() {
    pageFlip.flipPrev();
}

let resizeTimeout;

window.addEventListener("resize", () => {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(() => {
        initFlipbook();
    }, 300);
});

// seguridad básica
document.addEventListener('contextmenu', e => e.preventDefault());
document.addEventListener('dragstart', e => e.preventDefault());

initFlipbook();
</script>

</body>
</html>