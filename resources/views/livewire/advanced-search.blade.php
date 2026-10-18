<div
    class="bg-white dark:bg-neutral-600 dark:text-white p-4 sm:p-6 rounded-b-lg shadow-sm border-t border-gray-100 font-sans">

    <h4 class="text-sm font-bold mb-6 text-gray-700 uppercase tracking-wider flex items-center gap-2 dark:text-white">
        <svg class="w-4 h-4 shrink-0 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
            stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
        </svg>
        Búsqueda Avanzada Global
    </h4>

    {{-- PASO 1: Fondo --}}
    <div class="flex flex-col">
        <label class="text-xs font-bold text-gray-600 uppercase mb-3 tracking-wide dark:text-white">
            1. Fondo
        </label>
        <select wire:model.live="fondoSeleccionado"
            class="dark:bg-neutral-600 dark:text-white w-full max-w-2xl border rounded-md px-3 py-2 text-base md:text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all {{ $fondoSeleccionado === '' ? 'border-red-300 text-gray-400' : 'border-gray-300 text-gray-700' }}">
            <option value="">--- Selecciona un fondo ---</option>
            <option value="Todos">Todos los fondos</option>
            @foreach ($fondosDisponibles as $fondo)
                <option value="{{ $fondo['id'] }}">{{ str_repeat('— ', $fondo['depth']) . $fondo['nombre'] }}</option>
            @endforeach
        </select>
        @if ($fondoSeleccionado === '')
            <p class="text-xs text-red-500 italic mt-2">
                Selecciona un fondo para continuar.
            </p>
        @endif
    </div>

    {{-- PASO 2: Tipo de acervo (solo después de elegir fondo) --}}
    @if ($fondoSeleccionado !== '')
        <div class="mt-6 flex flex-col border-t border-gray-100 pt-6">
            <label class="text-xs font-bold text-gray-600 uppercase mb-3 tracking-wide dark:text-white">
                2. Tipo de acervo
            </label>
            <select wire:model.live="acervoSeleccionado"
                class="dark:bg-neutral-600 dark:text-white w-full max-w-2xl border rounded-md px-3 py-2 text-base md:text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all {{ $acervoSeleccionado === '' ? 'border-red-300 text-gray-400' : 'border-gray-300 text-gray-700' }}">
                <option value="">--- Selecciona un tipo de acervo ---</option>
                <option value="todos">Todos los tipos de acervo</option>
                @foreach ($acervosDisponibles as $acervo)
                    <option value="{{ $acervo['id'] }}">{{ $acervo['nombre'] }}</option>
                @endforeach
            </select>
            @if (empty($acervosDisponibles))
                <p class="text-xs text-gray-500 italic mt-2">
                    Este fondo aún no tiene recursos registrados.
                </p>
            @elseif ($acervoSeleccionado === '')
                <p class="text-xs text-red-500 italic mt-2">
                    Selecciona un tipo de acervo (o "Todos") para mostrar los campos de búsqueda.
                </p>
            @endif
        </div>
    @endif

    {{-- PASO 3: Campos de búsqueda (solo con fondo y acervo elegidos) --}}
    @if ($fondoSeleccionado !== '' && $acervoSeleccionado !== '')
        <div wire:keydown.enter="aplicarFiltrado" class="mt-6 space-y-5 border-t border-gray-100 pt-6">

            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide dark:text-white">
                3. Criterios de búsqueda
            </label>

            <div class="space-y-4 md:space-y-3">
                @foreach ($filas as $index => $fila)
                    <div
                        class="flex flex-col gap-2 rounded-lg border border-gray-100 p-3 dark:border-neutral-500 md:flex-row md:items-center md:gap-3 md:border-0 md:p-0">

                        <select wire:model="filas.{{ $index }}.campo"
                            class="dark:bg-neutral-600 dark:text-white w-full border border-gray-300 text-gray-700 rounded-md px-3 py-2 text-base md:text-sm md:w-44 md:shrink-0 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all">
                            <option value="all">--- Todos los campos ---</option>
                            @foreach ($camposEsquema as $campo)
                                <option value="{{ $campo['variable'] }}">{{ $campo['label'] ?? $campo['variable'] }}
                                </option>
                            @endforeach
                        </select>

                        @if ($index > 0)
                            <select wire:model="filas.{{ $index }}.operador"
                                class="dark:bg-neutral-600 dark:text-white w-full border border-gray-300 text-gray-600 font-bold uppercase text-base md:text-xs rounded-md px-3 py-2 md:w-28 md:shrink-0 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all bg-gray-50">
                                <option value="AND">Y (AND)</option>
                                <option value="OR">O (OR)</option>
                                <option value="NOT">NO (NOT)</option>
                            </select>
                        @else
                            <div class="hidden md:block md:w-28 md:shrink-0"></div>
                        @endif

                        {{-- En móvil: término + botón en una línea. Desde md el wrapper desaparece (contents). --}}
                        <div class="flex items-center gap-2 md:contents">
                            <input type="text" wire:model="filas.{{ $index }}.termino"
                                placeholder="Ingresa el término..."
                                class="min-w-0 flex-1 border border-gray-300 rounded-md p-2 text-base md:text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all placeholder-gray-400">

                            @if ($loop->last && count($filas) < $maxFilas)
                                <button type="button" wire:click="agregarFila" aria-label="Agregar criterio"
                                    class="h-10 w-10 shrink-0 md:h-9 md:w-9 rounded-md border border-gray-300 text-gray-500 flex items-center justify-center hover:bg-gray-100 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4.5v15m7.5-7.5h-15"></path>
                                    </svg>
                                </button>
                            @else
                                <button type="button" wire:click="removerFila({{ $index }})"
                                    aria-label="Quitar criterio"
                                    class="h-10 w-10 shrink-0 md:h-9 md:w-9 rounded-md border border-red-200 text-red-500 flex items-center justify-center hover:bg-red-50 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20 12H4"></path>
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @error('busqueda_vacia')
        <p class="mt-4 text-xs text-red-500 italic">{{ $message }}</p>
    @enderror

    <!-- Botones de acción: apilados en móvil (Buscar arriba), en fila desde sm -->
    <div
        class="mt-8 flex flex-col-reverse gap-3 border-t border-gray-100 pt-4 sm:flex-row sm:items-center sm:justify-end">
        @if (request()->anyFilled(['q', 'acervo', 'tipos', 'fondos']))
            <a href="{{ url()->current() }}"
                class="px-4 py-2 text-xs font-bold uppercase tracking-wider text-gray-500 hover:text-red-700 flex items-center justify-center transition duration-200">
                Limpiar criterios
            </a>
        @endif

        <button type="button" wire:click="aplicarFiltrado" @disabled($fondoSeleccionado === '' || $acervoSeleccionado === '')
            title="{{ $fondoSeleccionado === '' ? 'Selecciona un fondo primero' : ($acervoSeleccionado === '' ? 'Selecciona un tipo de acervo primero' : '') }}"
            class="w-full sm:w-auto justify-center text-xs font-bold uppercase tracking-widest bg-[#86212b] hover:bg-[#6d1b23] text-white px-8 py-2.5 rounded-md transition duration-200 shadow-sm flex items-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-[#86212b]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            Buscar
        </button>
    </div>
</div>
