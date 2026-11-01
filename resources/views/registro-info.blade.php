@extends('layouts.plantilla')

@section('content')
    <section class="bg-zinc-950 min-h-screen">
        <div class="max-w-5xl mx-auto px-4 py-6 lg:py-10 space-y-10">
            <!-- BOTÓN VOLVER -->
            <a href="{{ url()->previous() }}"
                class="inline-flex items-center gap-2 px-3 py-2 text-xs font-medium text-zinc-300 bg-zinc-800 rounded-lg hover:bg-zinc-700 transition self-start">
                <x-heroicon-o-arrow-left class="w-4 h-4" />
                Volver
            </a>

            <!-- CONTENEDOR PRINCIPAL -->
            <div class="rounded-2xl border border-zinc-800 bg-zinc-900 overflow-hidden shadow-none">
                
                <!-- ENCABEZADO -->
                <header class="p-6 lg:p-8 border-b border-zinc-800">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <h1 class="mt-6 text-2xl lg:text-3xl font-bold text-white leading-snug break-words">
                            {{ $tituloPrincipal }}
                        </h1>
                        @auth
                            <livewire:boton-favorito :recursoId="$recurso->id" :key="'fav-' . $recurso->id" />
                        @endauth
                    </div>
                </header>

                <div class="p-6 lg:p-8">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-2">
                        <!-- 1. Datos Generales -->
                        @foreach ($generales as $item)
                            <div class="lg:col-span-1">
                                <div class="h-full rounded-xl border border-zinc-800 bg-zinc-950 p-4 hover:border-zinc-700 transition">
                                    <div class="text-[11px] font-bold text-zinc-500 uppercase tracking-wider mb-2">{{ $item['label'] }}</div>
                                    <div class="text-sm text-zinc-200 break-words leading-relaxed">{{ $item['valor'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    
                        <!-- 2. Metadatos -->
                        @foreach ($metadataList as $item)
                            <div class="{{ $item['largo'] ? 'lg:col-span-2' : 'lg:col-span-1' }}">
                                <div class="h-full rounded-xl border border-zinc-800 bg-zinc-950 p-4 hover:border-zinc-700 transition">
                                    <div class="text-[11px] font-bold text-zinc-500 uppercase tracking-wider mb-2">{{ $item['label'] }}</div>
                                    <div class="text-sm text-zinc-200 break-words leading-relaxed whitespace-pre-wrap">{{ $item['valor'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    
                        <!-- 3. Datos Largos -->
                        @foreach ($largos as $item)
                            <div class="lg:col-span-2">
                                <div class="h-full rounded-xl border border-zinc-800 bg-zinc-950 p-4 hover:border-zinc-700 transition">
                                    <div class="text-[11px] font-bold text-zinc-500 uppercase tracking-wider mb-2">{{ $item['label'] }}</div>
                                    <div class="text-sm text-zinc-200 break-words leading-relaxed whitespace-pre-wrap">{{ $item['valor'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- MODAL REPORTE -->
            @auth
                <div x-data="{ open: {{ $errors->any() ? 'true' : 'false' }} }" class="text-sm text-zinc-400">
                    ¿Ves algún error en la información? Reporta este recurso dando clic
                    <a href="#" @click.prevent="open = true"
                        class="font-medium text-red-500 underline underline-offset-2 hover:text-red-400">
                        aquí
                    </a>

                    <div x-show="open" x-cloak x-transition.opacity @keydown.escape.window="open = false"
                        class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4 backdrop-blur-sm">

                        <div @click.outside="open = false"
                            class="w-full max-w-md overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900 shadow-xl">

                            <div class="flex items-start justify-between gap-4 border-b border-zinc-800 p-6">
                                <div>
                                    <h2 class="text-lg font-bold text-white">Reportar un error</h2>
                                    <p class="mt-1 text-xs text-zinc-400">Cuéntanos qué información debería corregirse.</p>
                                </div>
                                <button type="button" @click="open = false" aria-label="Cerrar"
                                    class="rounded-lg p-1.5 text-zinc-500 transition hover:bg-zinc-800 hover:text-zinc-300">
                                    <x-heroicon-o-x-mark class="h-5 w-5" />
                                </button>
                            </div>

                            <form method="POST" action="{{ route('reporte.recurso', $recurso) }}" class="space-y-5 p-6">
                                @csrf
                                <div style="position:absolute; left:-9999px;" aria-hidden="true">
                                    <label for="sitio_web">Sitio web</label>
                                    <input type="text" name="sitio_web" id="sitio_web" tabindex="-1" autocomplete="off">
                                </div>
                                <input type="hidden" name="cargado_en" value="{{ now()->timestamp }}">
                                <div>
                                    <label for="observaciones" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-zinc-400">Observaciones</label>
                                    <textarea name="observaciones" id="observaciones" rows="4" required maxlength="1000" placeholder="Describe el error que encontraste..."
                                        class="w-full resize-none rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm leading-relaxed text-zinc-100 placeholder-zinc-600 focus:border-red-900 focus:outline-none focus:ring-1 focus:ring-red-900">{{ old('observaciones') }}</textarea>
                                    @error('observaciones')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}" data-theme="dark"></div>
                                    @error('cf-turnstile-response')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="flex justify-end gap-2 border-t border-zinc-800 pt-5">
                                    <button type="button" @click="open = false" class="rounded-lg bg-zinc-800 px-4 py-2 text-xs font-medium text-zinc-300 transition hover:bg-zinc-700">Cancelar</button>
                                    <button type="submit" class="rounded-lg bg-red-900 px-4 py-2 text-xs font-medium text-white transition hover:bg-red-800">Enviar reporte</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endauth

            @once
                <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
                <style>[x-cloak] { display: none !important }</style>
            @endonce
        </div>
    </section>
@endsection