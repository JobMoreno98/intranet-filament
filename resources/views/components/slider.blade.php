@props([
    'posts' => [],
    'max' => 5, // máximo de slides
    'interval' => 6000, // ms entre slides
    'autoplay' => true,
    'limit' => 140, // caracteres del texto descriptivo
    'height' => 'h-[440px] sm:h-[500px] ',
])

@php
    $lista = collect($posts)->take($max)->values();
@endphp

@if ($lista->isNotEmpty())
    <section
        {{ $attributes->merge(['class' => 'relative w-full overflow-hidden rounded-2xl bg-zinc-900 shadow-sm ' . $height]) }}
        role="region" aria-roledescription="carrusel" aria-label="Publicaciones destacadas" tabindex="0"
        x-data="{
            active: 0,
            total: {{ $lista->count() }},
            // Imágenes ya solicitadas: se carga la actual y la siguiente, no todas de golpe
            seen: [0, {{ $lista->count() > 1 ? 1 : 0 }}],
            autoplay: @js((bool) $autoplay),
            delay: @js((int) $interval),
            timer: null,
            startX: 0,
            init() {
                if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    this.autoplay = false;
                }
                this.start();
            },
            start() {
                this.stop();
                if (this.autoplay && this.total > 1) {
                    this.timer = setInterval(() => this.next(), this.delay);
                }
            },
            stop() {
                clearInterval(this.timer);
                this.timer = null;
            },
            go(i) {
                this.active = (i + this.total) % this.total;
                const siguiente = (this.active + 1) % this.total;
                [this.active, siguiente].forEach(n => { if (!this.seen.includes(n)) this.seen.push(n); });
            },
            next() { this.go(this.active + 1); },
            prev() { this.go(this.active - 1); },
            destroy() { this.stop(); }
        }"
        x-on:mouseenter="stop()" x-on:mouseleave="start()"
        x-on:focusin="stop()" x-on:focusout="start()"
        x-on:visibilitychange.document="document.hidden ? stop() : start()"
        x-on:keydown.arrow-left.prevent="prev(); start()"
        x-on:keydown.arrow-right.prevent="next(); start()"
        x-on:touchstart.passive="startX = $event.touches[0].clientX"
        x-on:touchend="
            const d = $event.changedTouches[0].clientX - startX;
            if (Math.abs(d) > 50) { d < 0 ? next() : prev(); start(); }
        ">

        {{-- SLIDES --}}
        @foreach ($lista as $i => $post)
            @php
                $url = route('blog.show', $post->slug);
                $imagen = !empty($post->portada) ? asset('storage/' . $post->portada) : null;

                $texto = $post->extracto ?? ($post->descripcion ?? $post->contenido);
                $texto = \Illuminate\Support\Str::limit(trim(html_entity_decode(strip_tags((string) $texto))), $limit);

                $tag = is_array($post->tags ?? null) ? $post->tags[0] ?? null : null;
            @endphp

            <article
                class="absolute inset-0 transition-opacity duration-700 ease-in-out {{ $loop->first ? 'opacity-100 z-10' : 'opacity-0 pointer-events-none' }}"
                x-bind:class="{ 'opacity-100 z-10': active === {{ $i }}, 'opacity-0 pointer-events-none': active !== {{ $i }} }"
                x-bind:aria-hidden="active !== {{ $i }}"
                aria-roledescription="diapositiva" aria-label="{{ $i + 1 }} de {{ $lista->count() }}">

                {{-- Imagen: la primera se carga con prioridad; las demás, bajo demanda --}}
                @if ($imagen)
                    <img alt="" decoding="async"
                        class="absolute inset-0 h-full w-full object-cover object-center"
                        @if ($loop->first) src="{{ $imagen }}" fetchpriority="high"
                        @else
                            x-bind:src="seen.includes({{ $i }}) ? @js($imagen) : null" @endif>
                @endif

                {{-- Degradado para legibilidad del texto --}}
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent"></div>

                {{-- Contenido --}}
                <div class="absolute inset-x-0 bottom-0 p-5 sm:p-8 lg:p-12 pb-14 sm:pb-16">
                    <div class="max-w-2xl flex flex-col gap-2 sm:gap-3 text-left">
                        <div class="flex items-center gap-2 text-[11px] uppercase tracking-wide font-semibold">
                            @if ($tag)
                                <span class="bg-[#86212b] text-white rounded-full px-2.5 py-1">{{ $tag }}</span>
                            @endif
                            <time datetime="{{ $post->created_at->toDateString() }}" class="text-zinc-200">
                                {{ $post->created_at->translatedFormat('d \d\e F, Y') }}
                            </time>
                        </div>

                        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold leading-tight text-white line-clamp-3">
                            {{-- Enlace "estirado": toda la diapositiva es clicable --}}
                            <a href="{{ $url }}"
                                class="after:absolute after:inset-0 focus:outline-none focus-visible:after:ring-4 focus-visible:after:ring-white/70"
                                x-bind:tabindex="active === {{ $i }} ? 0 : -1">
                                {{ $post->nombre }}
                            </a>
                        </h2>

                        @if ($texto !== '')
                            <p class="text-sm sm:text-base text-zinc-100/90 leading-relaxed line-clamp-2 sm:line-clamp-3">
                                {{ $texto }}
                            </p>
                        @endif

                        <span
                            class="mt-1 inline-flex w-fit items-center gap-1 text-xs font-bold uppercase tracking-wide text-white border-b-2 border-[#86212b] pb-0.5">
                            Leer más <span aria-hidden="true">→</span>
                        </span>
                    </div>
                </div>
            </article>
        @endforeach

        @if ($lista->count() > 1)
            {{-- FLECHAS (ocultas en móvil: ahí se desliza con el dedo) --}}
            <button type="button" aria-label="Publicación anterior" x-on:click="prev(); start()"
                class="hidden sm:flex absolute left-3 top-1/2 -translate-y-1/2 z-20 items-center justify-center w-10 h-10 rounded-full bg-white/80 hover:bg-white text-zinc-900 shadow-md backdrop-blur-sm transition focus:outline-none focus-visible:ring-4 focus-visible:ring-white/70">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <button type="button" aria-label="Publicación siguiente" x-on:click="next(); start()"
                class="hidden sm:flex absolute right-3 top-1/2 -translate-y-1/2 z-20 items-center justify-center w-10 h-10 rounded-full bg-white/80 hover:bg-white text-zinc-900 shadow-md backdrop-blur-sm transition focus:outline-none focus-visible:ring-4 focus-visible:ring-white/70">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                </svg>
            </button>

            {{-- INDICADORES --}}
            <div class="absolute bottom-4 sm:bottom-5 left-1/2 -translate-x-1/2 z-20 flex items-center gap-2">
                @foreach ($lista as $i => $post)
                    <button type="button" aria-label="Ir a la publicación {{ $i + 1 }}"
                        x-on:click="go({{ $i }}); start()"
                        class="h-2.5 rounded-full transition-all duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-white {{ $loop->first ? 'w-6 bg-white' : 'w-2.5 bg-white/50' }}"
                        x-bind:class="{ 'w-6 bg-white': active === {{ $i }}, 'w-2.5 bg-white/50 hover:bg-white/80': active !== {{ $i }} }"
                        x-bind:aria-current="active === {{ $i }}"></button>
                @endforeach
            </div>
        @endif

        {{-- Anuncia el cambio de diapositiva a lectores de pantalla --}}
        <div class="sr-only" aria-live="polite" x-text="'Publicación ' + (active + 1) + ' de ' + total"></div>
    </section>
@endif