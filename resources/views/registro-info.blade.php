@extends('layouts.plantilla')

@section('content')
    @php
        use Illuminate\Database\Eloquent\Model;

        $omitir = $omitir ?? [];
        $labels = $labels ?? [];

        $camposLargos = ['descripcion', 'contenido', 'notas', 'resumen', 'observaciones'];
        $camposCompactos = [
            'anio',
            'fecha',
            'paginas',
            'tomo',
            'volumen',
            'idioma',
            'isbn',
            'clave',
            'folio',
            'numero',
        ];
        $umbralLargo = 80; // cualquier campo que supere estos caracteres se considera largo
        $minimoLargo = 50; // un campo "largo" por nombre (contenido, notas...) solo lo es si llega a este mínimo

        // Decide si un valor es largo, tanto para campos generales como para metadatos
        $esLargo = function ($clave, $texto) use ($camposCompactos, $camposLargos, $umbralLargo, $minimoLargo) {
            if (in_array($clave, $camposCompactos)) {
                return false;
            }

            $largoTexto = mb_strlen($texto);

            return $largoTexto > $umbralLargo || (in_array($clave, $camposLargos) && $largoTexto >= $minimoLargo);
        };
        $camposTitulo = ['titulo', 'nombre', 'title'];

        $datos = $registro instanceof Model ? $registro->getAttributes() : (array) $registro;
        $datos = array_filter($datos, fn($v, $k) => !in_array($k, $omitir), ARRAY_FILTER_USE_BOTH);

        $aTexto = fn($v) => is_scalar($v) ? trim(strip_tags((string) $v)) : json_encode($v, JSON_UNESCAPED_UNICODE);
        $etiqueta = fn($k) => $labels[$k] ?? ucwords(str_replace(['_', '-'], ' ', str_replace('_id', '', $k)));

        // Metadata decodificada (puede llegar como JSON string o como array)
        $metaCrudo = $datos['metadata'] ?? [];
        $metaArray = is_string($metaCrudo) ? json_decode($metaCrudo, true) : (array) $metaCrudo;
        $metaArray = is_array($metaArray) ? $metaArray : [];

        // Título principal: se busca primero dentro de metadata (sin importar mayúsculas)
        // y, si no está ahí, en las columnas del registro.
        $claveTituloMeta = collect(array_keys($metaArray))->first(
            fn($k) => in_array(mb_strtolower(trim((string) $k)), $camposTitulo) && !empty($metaArray[$k]),
        );

        $campoTitulo = null;

        if ($claveTituloMeta !== null) {
            $titulo = $aTexto($metaArray[$claveTituloMeta]);
        } else {
            $campoTitulo = collect($camposTitulo)->first(fn($c) => !empty($datos[$c] ?? null));
            $titulo = $campoTitulo ? $aTexto($datos[$campoTitulo]) : 'Registro sin título';
        }

        // Clasificamos los campos en tres grupos
        $generales = [];
        $largos = [];
        $metadata = [];

        foreach ($datos as $columna => $valor) {
            if ($columna === $campoTitulo) {
                continue;
            }

            $vacio = is_null($valor) || $valor === '' || $valor === [];

            if ($columna === 'metadata') {
                foreach ($metaArray as $k => $v) {
                    // El título ya se muestra en el encabezado
                    if ($k === $claveTituloMeta || is_null($v) || $v === '') {
                        continue;
                    }
                    $textoMeta = $aTexto($v);

                    $largoMeta = $esLargo($k, $textoMeta);

                    $metadata[] = ['label' => $etiqueta($k), 'valor' => $textoMeta, 'largo' => $largoMeta];
                }

                // Cortos primero, largos al final, para que la cuadrícula no quede con huecos
                usort($metadata, fn($a, $b) => $a['largo'] <=> $b['largo']);
                continue;
            }

            $valorFinal = $valor;

            // Relaciones (xxx_id): mostramos el nombre del modelo relacionado
            if (str_ends_with($columna, '_id') && !is_null($valor) && $registro instanceof Model) {
                $relacion = str_replace('_id', '', $columna);
                $relacionado = $registro->$relacion ?? null;

                if ($relacionado) {
                    $valorFinal = $relacionado->nombre ?? $relacionado->id;
                }
            }

            $texto = $vacio ? '' : $aTexto($valorFinal);
            if ($esLargo($columna, $texto)) {
                if (!$vacio) {
                    $largos[] = ['label' => $etiqueta($columna), 'valor' => $texto];
                }
            } else {
                $generales[] = ['label' => $etiqueta($columna), 'valor' => $texto];
            }
        }
    @endphp

    <section class="dark:bg-zinc-950">
        <div class="max-w-5xl mx-auto px-4 py-6 lg:py-10 space-y-10">
            <a href="{{ url()->previous() }}"
                class="inline-flex items-center gap-2 px-3 py-2 text-xs font-medium text-zinc-700 bg-zinc-100 rounded-lg hover:bg-zinc-200 transition self-start dark:text-zinc-300 dark:bg-zinc-800 dark:hover:bg-zinc-700">
                <x-heroicon-o-arrow-left class="w-4 h-4" />
                Volver
            </a>
            <div
                class="rounded-2xl border border-zinc-200 bg-white shadow-sm overflow-hidden dark:border-zinc-800 dark:bg-gradient-to-b dark:from-zinc-900 dark:to-zinc-950 dark:shadow-none">

                <!-- ENCABEZADO -->
                <header class="p-6 lg:p-8 border-b border-zinc-200 dark:border-zinc-800">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <h1
                            class="mt-6 text-2xl lg:text-3xl font-bold text-zinc-900 leading-snug break-words dark:text-white">
                            {{ $titulo }}
                        </h1>
                    </div>
                    {{--  
                    <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-zinc-500">
                        <span>Ficha del registro</span>

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
                                    <dd class="mt-1  text-xl text-zinc-800 break-words dark:text-zinc-100">
                                        @if ($item['valor'] === '')
                                            <span class="text-zinc-400 italic  text-xl dark:text-zinc-600">
                                                Sin
                                                información
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

            @if (count($metadata))
                <section>
                    <div class="flex items-center gap-4 mb-4">
                        <h2 class="text-xs font-bold uppercase tracking-widest text-zinc-500 shrink-0">Metadatos</h2>
                        <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-800"></div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach ($metadata as $item)
                            <div
                                class="{{ $item['largo'] ? 'sm:col-span-2 lg:col-span-3' : '' }} rounded-xl border border-zinc-200 bg-white p-4 min-w-0 shadow-sm hover:border-zinc-300 transition dark:border-zinc-800 dark:bg-zinc-900 dark:shadow-none dark:hover:border-zinc-700">
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
                    ¿Ves algún error en la información? Reporta este registro dando clic
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

                            <form method="POST" action="{{ route('reporte.recurso', $registro) }}" class="space-y-5 p-6">
                                @csrf

                                {{-- Honeypot --}}
                                <div style="position:absolute; left:-9999px;" aria-hidden="true">
                                    <label for="sitio_web">Sitio web</label>
                                    <input type="text" name="sitio_web" id="sitio_web" tabindex="-1" autocomplete="off">
                                </div>

                                {{-- Marca de tiempo --}}
                                <input type="hidden" name="cargado_en" value="{{ now()->timestamp }}">

                                <div>
                                    <label for="correo"
                                        class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-zinc-500">
                                        Correo
                                    </label>
                                    <input type="email" name="correo" id="correo" required value="{{ old('correo') }}"
                                        placeholder="tucorreo@ejemplo.com"
                                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-800 placeholder-zinc-400 focus:border-red-900 focus:outline-none focus:ring-1 focus:ring-red-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:placeholder-zinc-600">
                                    @error('correo')
                                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

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
