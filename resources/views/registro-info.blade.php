@extends('layouts.plantilla')


@section('content')

    <section class="dark:bg-zinc-950">
        <div class="max-w-5xl mx-auto px-4 py-6 lg:py-10 space-y-10">
            <a href="{{ url()->previous() }}"
                class="inline-flex items-center gap-2 px-3 py-2 text-xs font-medium 
                text-zinc-700 bg-zinc-100 rounded-lg hover:bg-zinc-200 transition self-start dark:text-zinc-300 dark:bg-zinc-800 dark:hover:bg-zinc-700">
                <x-heroicon-o-arrow-left class="w-4 h-4" />
                Volver
            </a>
            @if (session('success'))
                <div x-data="{ open: true }" x-show="open" x-transition
                    class="mb-4 flex items-center justify-between rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-emerald-800 dark:border-emerald-800/60 dark:bg-emerald-950/40 dark:text-emerald-300"
                    role="alert">
                    <div class="flex items-center gap-3">
                        <svg class="h-5 w-5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none"
                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-sm font-medium">{{ session('success') }}</span>
                    </div>

                    <button type="button" @click="open = false"
                        class="rounded-md p-1 text-emerald-600 hover:bg-emerald-100 dark:text-emerald-400 dark:hover:bg-emerald-900/50">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            @endif
            <div
                class="rounded-2xl border bg-white 
                shadow-sm overflow-hidden dark:bg-zinc-900 dark:shadow-none">

                <!-- ENCABEZADO -->
                <header class="p-6 lg:p-8 border-b  ">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <h1
                            class="mt-6 text-2xl lg:text-3xl font-bold text-zinc-900 leading-snug break-words dark:text-white">
                            {{ $tituloPrincipal }}
                        </h1>
                        @auth
                            <livewire:boton-favorito :recursoId="$recurso->id" :key="'fav-' . $recurso->id" />
                        @endauth
                    </div>
                    {{--  
                    <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-zinc-500">
                        <span>Ficha del recurso</span>

                        @if (isset($paginas) && count($paginas))
                            <span class="text-zinc-500 dark:text-zinc-400">
                                <span class="font-bold text-zinc-900 dark:text-white">{{ count($paginas) }}</span> páginas
                            </span>
                        @endif
                    </div>
                    --}}
                </header>

                <div class="p-6 lg:p-8 space-y-8">

                    <!-- DATOS GENERALES -->
                    @if (count($generales))
                        <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-6">
                            @foreach ($generales as $item)
                                <div class="border-l-2 border-red-900 pl-4 min-w-0">
                                    {{--
                                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">
                                        {{ $item['label'] }}
                                    </dt>
                                    --}}
                                    <dd class="mt-1 text-2xl text-zinc-800 break-words dark:text-zinc-100">
                                        @if ($item['valor'] === '')
                                            <span class="text-zinc-400 italic text-xl dark:text-zinc-600">
                                                Sin información
                                            </span>
                                        @else
                                            {{ $item['valor'] }}
                                        @endif
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    @endif

                    @foreach ($largos as $item)
                        <div class="pt-6 border-t border-zinc-200 dark:border-zinc-800">
                            <h2 class="text-xs font-bold uppercase tracking-widest text-zinc-500 mb-3">
                                {{ $item['label'] }}
                            </h2>
                            <p
                                class="text-sm text-zinc-700 leading-relaxed whitespace-pre-line break-words dark:text-zinc-200">
                                {{ $item['valor'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            @if (count($metadataList))
                <section>
                    <div class="flex items-center gap-4 mb-4">
                        <h2 class="text-xs font-bold uppercase tracking-widest text-zinc-500 shrink-0">Metadatos</h2>
                        <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-800"></div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach ($metadataList as $item)
                            <div
                                class="{{ $item['largo'] ? 'sm:col-span-2 lg:col-span-3' : '' }} rounded-xl 
                                border border-zinc-200 bg-white p-4 min-w-0 shadow-sm hover:border-zinc-300 transition 
                                dark:border-zinc-800 dark:bg-zinc-900 dark:shadow-none dark:hover:border-zinc-700">
                                <div
                                    class="text-[10px] font-semibold uppercase tracking-wider text-primary-600 dark:text-primary-400">
                                    {{ $item['label'] }}
                                </div>
                                <div
                                    class="mt-1 text-sm text-zinc-800 break-words dark:text-zinc-200 {{ $item['largo'] ? 'leading-relaxed whitespace-pre-line' : '' }}">
                                    {{ $item['valor'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            @auth
                <div x-data="{ open: {{ $errors->any() ? 'true' : 'false' }} }" class="text-sm text-zinc-600 dark:text-zinc-400">
                    ¿Ves algún error en la información? Reporta este recurso dando clic
                    <a href="#" @click.prevent="open = true"
                        class="font-medium text-red-900 underline underline-offset-2 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                        aquí
                    </a>

                    {{-- Modal --}}
                    <div x-show="open" x-cloak x-transition.opacity @keydown.escape.window="open = false"
                        class="fixed inset-0 z-50 flex items-center justify-center bg-zinc-900/60 p-4 backdrop-blur-sm">

                        <div @click.outside="open = false"
                            class="w-full max-w-md overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-xl dark:border-zinc-800 dark:bg-gradient-to-b dark:from-zinc-900 dark:to-zinc-950">

                            {{-- Encabezado --}}
                            <div
                                class="flex items-start justify-between gap-4 border-b border-zinc-200 p-6 dark:border-zinc-800">
                                <div>
                                    <h2 class="text-lg font-bold text-zinc-900 dark:text-white">Reportar un error</h2>
                                    <p class="mt-1 text-xs text-zinc-500">Cuéntanos qué información debería corregirse.</p>
                                </div>
                                <button type="button" @click="open = false" aria-label="Cerrar"
                                    class="rounded-lg p-1.5 text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-700 dark:hover:bg-zinc-800 dark:hover:text-zinc-200">
                                    <x-heroicon-o-x-mark class="h-5 w-5" />
                                </button>
                            </div>

                            <form method="POST" action="{{ route('reporte.recurso', $recurso) }}" class="space-y-5 p-6">
                                @csrf

                                {{-- Honeypot --}}
                                <div style="position:absolute; left:-9999px;" aria-hidden="true">
                                    <label for="sitio_web">Sitio web</label>
                                    <input type="text" name="sitio_web" id="sitio_web" tabindex="-1" autocomplete="off">
                                </div>

                                {{-- Marca de tiempo --}}
                                <input type="hidden" name="cargado_en" value="{{ now()->timestamp }}">

                                <div>
                                    <label for="observaciones"
                                        class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-zinc-500">
                                        Observaciones
                                    </label>
                                    <textarea name="observaciones" id="observaciones" rows="4" required maxlength="1000"
                                        placeholder="Describe el error que encontraste..."
                                        class="w-full resize-none rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm leading-relaxed text-zinc-800 placeholder-zinc-400 focus:border-red-900 focus:outline-none focus:ring-1 focus:ring-red-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:placeholder-zinc-600">{{ old('observaciones') }}</textarea>
                                    @error('observaciones')
                                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Turnstile --}}
                                <div>
                                    <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}"
                                        data-theme="auto"></div>
                                    @error('cf-turnstile-response')
                                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="flex justify-end gap-2 border-t border-zinc-200 pt-5 dark:border-zinc-800">
                                    <button type="button" @click="open = false"
                                        class="rounded-lg bg-zinc-100 px-4 py-2 text-xs font-medium text-zinc-700 transition hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                                        Cancelar
                                    </button>
                                    <button type="submit"
                                        class="rounded-lg bg-red-900 px-4 py-2 text-xs font-medium text-white transition hover:bg-red-800">
                                        Enviar reporte
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div
                        class="mx-auto w-full max-w-4xl mt-10 border-t border-zinc-200 dark:border-zinc-700 pt-6 flex flex-wrap items-center justify-center gap-3 dark:bg-neutral-600">
                        <span class="text-xs font-bold text-zinc-500 dark:text-white uppercase tracking-wide">Compartir:</span>
                        <a href="https://wa.me/?text={{ urlencode(url()->current()) }}" target="_blank" rel="noopener"
                            class="text-xs bg-zinc-100 hover:bg-zinc-200 text-zinc-700 rounded-full py-1.5 px-3 transition-colors dark:bg-neutral-600 dark:text-white dark:hover:bg-zinc-700">
                            WhatsApp
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
                            target="_blank" rel="noopener"
                            class="dark:border dark:border-zinc-800 text-xs bg-zinc-100 hover:bg-zinc-200 text-zinc-700 rounded-full py-1.5 px-3 transition-colors dark:bg-neutral-600 
                    dark:text-white dark:hover:bg-zinc-700">
                            Facebook
                        </a>

                        <a href="https://x.com/intent/post?url={{ urlencode(url()->current()) }}&text={{ urlencode($titulo ?? '') }}"
                            target="_blank" rel="noopener"
                            class="dark:border dark:border-zinc-800 text-xs bg-zinc-100 hover:bg-zinc-200 text-zinc-700 rounded-full py-1.5 px-3 transition-colors dark:bg-neutral-600 dark:text-white dark:hover:bg-zinc-700">
                            X
                        </a>

                        <button type="button" x-data="{ soportado: !!navigator.share }" x-show="soportado" x-cloak
                            x-on:click="navigator.share({ title: document.title, url: '{{ url()->current() }}' })"
                            class="text-xs bg-zinc-100 hover:bg-zinc-200 text-zinc-700 rounded-full py-1.5 px-3 transition-colors dark:bg-neutral-600 dark:text-white dark:hover:bg-zinc-700">
                            Más opciones
                        </button>
                    </div>
                </div>
            @endauth

            @once
                <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
                <style>
                    [x-cloak] {
                        display: none !important
                    }
                </style>
            @endonce
        </div>
    </section>
@endsection
