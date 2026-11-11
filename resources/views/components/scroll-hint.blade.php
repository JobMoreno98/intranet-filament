@props(['fade' => 'from-white dark:from-neutral-800', 'step' => 0.8, 'gradient' => true])

<div x-data="{
    atStart: true,
    atEnd: false,
    overflow: false,
    update() {
        const el = this.$refs.track;
        this.overflow = el.scrollWidth > el.clientWidth + 4;
        this.atStart = el.scrollLeft <= 4;
        this.atEnd = el.scrollLeft + el.clientWidth >= el.scrollWidth - 4;
    },
    go(dir) {
        const el = this.$refs.track;
        el.scrollBy({ left: dir * el.clientWidth * {{ $step }}, behavior: 'smooth' });
    }
}" x-init="$nextTick(() => update())" @resize.window.debounce.150ms="update()" class="relative">

    {{-- Tu contenedor scrolleable: recibe las clases que le pases al componente --}}
    <div x-ref="track" @scroll.passive="update()" {{ $attributes }}>
        {{ $slot }}
    </div>

    @if ($gradient)
        {{-- Degradados --}}
        <div x-show="overflow && !atStart" x-cloak x-transition.opacity aria-hidden="true"
            class="pointer-events-none absolute inset-y-0 left-0 w-12 bg-gradient-to-r {{ $fade }} sm:hidden">
        </div>
        <div x-show="overflow && !atEnd" x-cloak x-transition.opacity aria-hidden="true"
            class="pointer-events-none absolute inset-y-0 right-0 w-12 bg-gradient-to-l {{ $fade }} sm:hidden">
        </div>
    @endif
    
    {{-- Flechas --}}
    <button type="button" x-show="overflow && !atStart" x-cloak x-transition.opacity @click="go(-1)"
        aria-label="Anterior"
        class="absolute left-1 top-1/2 z-10 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full bg-white/90 text-zinc-700 shadow-md ring-1 ring-zinc-200 sm:hidden dark:bg-zinc-700/90 dark:text-white dark:ring-zinc-600">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
        </svg>
    </button>
    <button type="button" x-show="overflow && !atEnd" x-cloak x-transition.opacity @click="go(1)"
        aria-label="Siguiente"
        class="absolute right-1 top-1/2 z-10 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full bg-white/90 text-zinc-700 shadow-md ring-1 ring-zinc-200 sm:hidden dark:bg-zinc-700/90 dark:text-white dark:ring-zinc-600">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
        </svg>
    </button>
</div>
