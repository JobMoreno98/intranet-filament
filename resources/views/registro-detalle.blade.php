@extends('layouts.plantilla')

@section('content')
    @php
        $color = Auth::check() ? 'bg-zinc-950' : 'bg-white';
    @endphp
    <section class="{{ $color }} min-h-screen">

        <div class="flex flex-col lg:flex-row lg:h-screen">
            <!-- VISOR -->
            <main class="flex-1 flex flex-col min-h-0 border-b border-zinc-200">
                <!-- TOPBAR -->
                <div
                    class="flex items-center flex-col md:flex-row justify-between px-4 py-3 border-b border-zinc-800 bg-zinc-900">
                    <a href="{{ url()->previous() }}"
                        class="inline-flex items-center gap-2 px-3 py-2 text-xs font-medium text-zinc-300 bg-zinc-800 rounded-lg hover:bg-zinc-700 transition">
                        <x-heroicon-o-arrow-left class="w-4 h-4" />
                        Volver
                    </a>
                    <span class="bg-red-900 text-white text-xs uppercase font-bold px-3 py-2 mt-2 md:mt-0 rounded-md">
                        {{ $coleccionNombre->nombre }}
                    </span>
                </div>
                <!-- MOBILE INFO -->
                <aside class="lg:hidden border-b border-zinc-800 bg-zinc-900 p-3 text-zinc-300">
                    <details class="group rounded-lg border border-zinc-800 bg-zinc-950">
                        <summary
                            class="cursor-pointer list-none px-4 py-3 text-sm font-semibold text-white flex items-center justify-between">
                            Información del libro
                            <span class="transition duration-200 group-open:rotate-180">
                                ▼
                            </span>
                        </summary>
                        <div class="divide-y divide-zinc-800">
                            @isset($registro)
                                <!-- RECURSO -->
                                @php
                                    $datos =
                                        $registro instanceof \Illuminate\Database\Eloquent\Model
                                            ? $registro->getAttributes()
                                            : (array) $registro;

                                    $datos = array_filter(
                                        $datos,
                                        fn($v, $k) => !in_array($k, $omitir),
                                        ARRAY_FILTER_USE_BOTH,
                                    );
                                @endphp

                                @foreach ($datos as $columna => $valor)
                                    @php
                                        $valorTexto = is_scalar($valor)
                                            ? trim(strip_tags((string) $valor))
                                            : json_encode($valor, JSON_UNESCAPED_UNICODE);

                                        $esCorto = mb_strlen($valorTexto) <= 40;

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

                                        $camposLargos = [
                                            'descripcion',
                                            'contenido',
                                            'notas',
                                            'resumen',
                                            'observaciones',
                                        ];

                                        $compacto =
                                            !in_array($columna, $camposLargos) &&
                                            ($esCorto || in_array($columna, $camposCompactos));

                                        $label = $labels[$columna] ?? ucwords(str_replace(['_', '-'], ' ', $columna));
                                    @endphp

                                    <div class="{{ $compacto ? 'lg:col-span-1' : 'lg:col-span-2' }}">

                                        <div
                                            class="h-full rounded-xl border border-zinc-800 bg-zinc-950 p-4 hover:border-zinc-700 transition">

                                            <div class="text-[11px] font-bold text-zinc-500 uppercase tracking-wider mb-2">
                                                {{ $label }}
                                            </div>

                                            <div class="text-sm text-zinc-200 break-words leading-relaxed">
                                                @if (empty($valor))
                                                    <span class="text-zinc-500 italic text-xs">Sin información</span>
                                                @else
                                                    {{ is_scalar($valor) ? $valor : json_encode($valor, JSON_UNESCAPED_UNICODE) }}
                                                @endif
                                            </div>

                                        </div>

                                    </div>
                                @endforeach
                            @endisset
                            <!-- PÁGINAS -->
                            <div class="p-4 space-y-1">

                                <span class="block text-zinc-500 uppercase text-[11px] font-semibold tracking-wider">

                                    Páginas

                                </span>

                                <div class="text-sm text-zinc-200">
                                    @isset($paginas)
                                        {{ count($paginas) }}
                                    @endisset

                                </div>

                            </div>

                            <!-- REGISTRO -->
                            @php
                                $datos =
                                    $registro instanceof \Illuminate\Database\Eloquent\Model
                                        ? $registro->getAttributes()
                                        : (array) $registro;

                                $datos = array_filter(
                                    $datos,
                                    fn($v, $k) => !in_array($k, $omitir),
                                    ARRAY_FILTER_USE_BOTH,
                                );
                            @endphp

                            @foreach ($datos as $columna => $valor)
                                @php
                                    $valorTexto = is_scalar($valor)
                                        ? trim(strip_tags((string) $valor))
                                        : json_encode($valor, JSON_UNESCAPED_UNICODE);

                                    $esCorto = mb_strlen($valorTexto) <= 40;

                                    $esRelacion = str_ends_with($columna, '_id');

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

                                    $camposLargos = ['descripcion', 'contenido', 'notas', 'resumen', 'observaciones'];

                                    $esMetadata = $columna === 'metadata';

                                    $valorFinal = $valor;

                                    $compacto =
                                        !in_array($columna, $camposLargos) &&
                                        ($esCorto || in_array($columna, $camposCompactos));

                                    $label = $labels[$columna] ?? ucwords(str_replace(['_', '-'], ' ', $columna));

                                    $valorFinal = $valor;

                                    if ($esRelacion && !is_null($valor)) {
                                        $relacion = str_replace('_id', '', $columna);

                                        $modeloRelacionado = $registro->$relacion ?? null;

                                        if ($modeloRelacionado) {
                                            $valorFinal = $modeloRelacionado->nombre ?? $modeloRelacionado->id;
                                        }
                                    }
                                    $label =
                                        $labels[$columna] ??
                                        ucwords(str_replace('_', ' ', str_replace('_id', '', $columna)));

                                    if ($esMetadata) {
                                        $valorFinal = is_string($valor) ? json_decode($valor, true) : (array) $valor;
                                    }
                                @endphp

                                <div
                                    class="{{ $esMetadata ? 'lg:col-span-2' : ($compacto ? 'lg:col-span-1' : 'lg:col-span-2') }}">

                                    <div
                                        class="h-full rounded-xl border border-zinc-800 bg-zinc-950 p-4 hover:border-zinc-700 transition">

                                        <div class="text-[11px] font-bold text-zinc-500 uppercase tracking-wider mb-2">
                                            {{ $label }}
                                        </div>

                                        <div class="text-sm text-zinc-200 break-words leading-relaxed">
                                            @if (empty($valor))
                                                <span class="text-zinc-500 italic text-xs">Sin información</span>
                                            @else
                                                @if ($esMetadata && is_array($valorFinal))
                                                    <div class="lg:col-span-2">
                                                        @foreach ($valorFinal as $metaKey => $metaValue)
                                                            <div class="border border-zinc-800 bg-zinc-900 p-2 rounded">

                                                                <div class="text-[10px] text-zinc-500 uppercase">
                                                                    {{ ucwords(str_replace('_', ' ', $metaKey)) }}
                                                                </div>

                                                                <div class="text-sm text-zinc-200">
                                                                    {{ is_scalar($metaValue) ? $metaValue : json_encode($metaValue) }}
                                                                </div>

                                                            </div>
                                                        @endforeach

                                                    </div>
                                                @else
                                                    {{ is_scalar($valorFinal) ? $valorFinal : json_encode($valorFinal, JSON_UNESCAPED_UNICODE) }}
                                                @endif
                                            @endif
                                        </div>

                                    </div>

                                </div>
                            @endforeach

                        </div>

                    </details>

                </aside>
                @if (Auth::check())
                    <!-- VIEWER -->
                    <div class="flex flex-row h-[90%]">

                        <div class="flex  gap-3 px-4 sm:px-6  my-4 hidden md:flex">
                            <button id="prev-page"
                                class="w-1/2 md:w-auto flex-1 md:flex-none rounded-lg bg-zinc-800 hover:bg-zinc-700 text-white py-2 px-4 text-xs font-semibold transition">
                                <flux:icon.arrow-left variant="mini" />
                            </button>

                        </div>

                        <div id="visor-container" class="relative flex-1 min-h-0 max-w-5xl mx-auto w-full">
                            <div id="viewer" class="h-full overflow-auto flex justify-center bg-zinc-800 p-4">
                                <!-- El canvas mantiene su renderizado pero contenido en el ancho máximo -->
                                <canvas id="page-canvas" class="max-w-full h-auto shadow-lg"></canvas>
                            </div>
                        </div>

                        <div class="flex  gap-3 px-4 sm:px-6 my-4 hidden md:flex">
                            <button id="next-page"
                                class="w-1/2 md:w-auto flex-1 md:flex-none rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white py-2 px-4 text-xs font-semibold transition">
                                <flux:icon.arrow-right variant="mini" />
                            </button>
                        </div>
                    </div>

                    <!-- MOBILE PAGE -->
                    <div class="px-4 py-2 bg-zinc-900 border-t border-zinc-800 text-center">

                        <p id="page-indicator" class="text-xs text-zinc-400 font-medium">
                            @isset($paginas)
                                1 / {{ count($paginas) }}
                            @endisset

                        </p>

                    </div>

                    <!-- MOBILE CONTROLS -->
                    <div
                        class=" flex items-center flex-col md:flex-row justify-between gap-3  border-t border-zinc-800 bg-zinc-900">

                        <div class="flex w-full gap-3 px-4 sm:px-6  md:hidden my-2">
                            <button id="prev-page"
                                class="w-1/2 md:w-auto flex-1 md:flex-none rounded-lg bg-zinc-800 hover:bg-zinc-700 text-white py-2 px-4 text-xs font-semibold transition text-center">
                                <flux:icon.arrow-left variant="mini" class="mx-auto" />
                            </button>

                            <button id="next-page"
                                class="w-1/2 md:w-auto flex-1 md:flex-none rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white py-2 px-4 text-xs font-semibold transition">
                                <flux:icon.arrow-right variant="mini" class="mx-auto" />
                            </button>
                        </div>

                        <div class="mx-auto max-w-4xl px-4 sm:px-6">

                            <!-- Barra de Herramientas: Botones de Zoom interactivos -->
                            <div
                                class="flex items-center justify-center gap-4 bg-white p-1 my-2  rounded-xl border border-gray-200 shadow-sm">
                                <!-- Botón Alejar -->
                                <button id="btn-zoom-out"
                                    class="p-1 rounded-lg hover:bg-gray-100 text-gray-600 transition font-bold text-lg w-8 h-8 flex items-center justify-center border border-gray-200">
                                    −
                                </button>

                                <!-- Indicador de Porcentaje Dinámico -->
                                <span id="zoom-percent"
                                    class="text-sm font-semibold text-gray-700 min-w-[60px] text-center">
                                    100%
                                </span>

                                <!-- Botón Acercar -->
                                <button id="btn-zoom-in"
                                    class="p-1 rounded-lg hover:bg-gray-100 text-gray-600 transition font-bold text-lg w-8 h-8 flex items-center justify-center border border-gray-200">
                                    +
                                </button>

                                <!-- Separador visual -->
                                <div class="h-6 w-px bg-gray-200 mx-1"></div>

                                <!-- Botón Restablecer -->
                                <button id="btn-reset-zoom"
                                    class="px-3 py-1 bg-gray-50 hover:bg-gray-100 text-gray-700 font-medium rounded-lg transition text-xs border border-gray-200 h-8 flex items-center">
                                    Reiniciar
                                </button>
                            </div>
                        </div>

                    </div>
                @else
                    <div class="bg-white p-10 text-center h-full">
                        <x-heroicon-o-lock-closed class="w-12 h-12 mx-auto text-zinc-400 mb-4" />

                        <h3 class="text-lg font-bold text-zinc-800">
                            Inicia sesión para visualizar el contenido
                        </h3>

                        <p class="text-sm text-zinc-500 mt-2">
                            Debes autenticarte para acceder al visor digital.
                        </p>

                        @php
                            session(['url.intended' => url()->current()]);
                        @endphp

                        <a href="{{ route('login') }}"
                            class="inline-flex items-center gap-2 mt-5 px-5 py-2.5 rounded-xl bg-red-900 text-white text-sm font-semibold hover:bg-red-800 transition">
                            <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4" />
                            Iniciar sesión
                        </a>
                    </div>
                @endif
            </main>

            <!-- DESKTOP SIDEBAR -->
            <aside class="hidden lg:flex lg:flex-col w-[420px] border-l border-zinc-800 bg-zinc-900 overflow-auto">

                <!-- HEADER -->
                <div class="px-6 py-5 border-b border-zinc-800">

                    <h2 class="text-lg font-bold text-white">
                        Ficha del Registro
                    </h2>

                    <p class="text-xs text-zinc-500 mt-1">
                        Información completa del documento
                    </p>

                </div>

                <!-- CONTENT -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-2 mx-4 my-2">

                    @php
                        $datos =
                            $registro instanceof \Illuminate\Database\Eloquent\Model
                                ? $registro->getAttributes()
                                : (array) $registro;

                        $datos = array_filter($datos, fn($v, $k) => !in_array($k, $omitir), ARRAY_FILTER_USE_BOTH);
                    @endphp

                    @foreach ($datos as $columna => $valor)
                        @php
                            $valorTexto = is_scalar($valor)
                                ? trim(strip_tags((string) $valor))
                                : json_encode($valor, JSON_UNESCAPED_UNICODE);

                            $esCorto = mb_strlen($valorTexto) <= 40;

                            $esRelacion = str_ends_with($columna, '_id');

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

                            $camposLargos = ['descripcion', 'contenido', 'notas', 'resumen', 'observaciones'];

                            $esMetadata = $columna === 'metadata';

                            $valorFinal = $valor;

                            $compacto =
                                !in_array($columna, $camposLargos) &&
                                ($esCorto || in_array($columna, $camposCompactos));

                            $label = $labels[$columna] ?? ucwords(str_replace(['_', '-'], ' ', $columna));

                            $valorFinal = $valor;

                            if ($esRelacion && !is_null($valor)) {
                                $relacion = str_replace('_id', '', $columna);

                                $modeloRelacionado = $registro->$relacion ?? null;

                                if ($modeloRelacionado) {
                                    $valorFinal = $modeloRelacionado->nombre ?? $modeloRelacionado->id;
                                }
                            }
                            $label =
                                $labels[$columna] ?? ucwords(str_replace('_', ' ', str_replace('_id', '', $columna)));

                            if ($esMetadata) {
                                $valorFinal = is_string($valor) ? json_decode($valor, true) : (array) $valor;
                            }
                        @endphp

                        <div class="{{ $esMetadata ? 'lg:col-span-2' : ($compacto ? 'lg:col-span-1' : 'lg:col-span-2') }}">

                            <div
                                class="h-full rounded-xl border border-zinc-800 bg-zinc-950 p-4 hover:border-zinc-700 transition">

                                <div class="text-[11px] font-bold text-zinc-500 uppercase tracking-wider mb-2">
                                    {{ $label }}
                                </div>

                                <div class="text-sm text-zinc-200 break-words leading-relaxed">
                                    @if (empty($valor))
                                        <span class="text-zinc-500 italic text-xs">Sin información</span>
                                    @else
                                        @if ($esMetadata && is_array($valorFinal))
                                            <div class="grid grid-cols-1 gap-2">
                                                @foreach ($valorFinal as $metaKey => $metaValue)
                                                    <div class="border border-zinc-800 bg-zinc-900 p-2 rounded">

                                                        <div class="text-[10px] text-zinc-500 uppercase">
                                                            {{ ucwords(str_replace('_', ' ', $metaKey)) }}
                                                        </div>

                                                        <div class="text-sm text-zinc-200">
                                                            {{ is_scalar($metaValue) ? $metaValue : json_encode($metaValue) }}
                                                        </div>

                                                    </div>
                                                @endforeach

                                            </div>
                                        @else
                                            {{ is_scalar($valorFinal) ? $valorFinal : json_encode($valorFinal, JSON_UNESCAPED_UNICODE) }}
                                        @endif
                                    @endif
                                </div>

                            </div>

                        </div>
                    @endforeach
                </div>

            </aside>

        </div>

    </section>
@endsection
@section('js')
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            window.initVisor({
                paginas: @json($paginas),
                recursoId: {{ $recurso['id'] }}
            });
        });
    </script>
@endsection
