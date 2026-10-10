@extends('layouts.plantilla')

@section('css')
    <style>
        .bg-custom-wine {
            background-color: #86212b;
        }

        .text-custom-wine {
            color: #86212b;
        }

        .border-custom-wine {
            border-color: #86212b;
        }

        .hover-bg-custom-wine:hover {
            background-color: #6d1b23;
        }
    </style>
@endsection

@section('content')
    <section>
        <div class="mx-auto flex w-full  flex-col gap-6 px-4 py-10 sm:px-7">

            {{-- Encabezado: mismo contenedor, radio y sombra que las tarjetas de áreas --}}
            <div class="overflow-hidden rounded-md border border-zinc-200 shadow-lg dark:border-zinc-600">
                <div class="flex flex-col items-start gap-4 bg-[#86212b] p-4 md:flex-row md:items-center md:justify-between">
                    <h3 class="flex items-center gap-3 text-xl font-bold uppercase tracking-wider text-white md:text-2xl">
                        <svg class="h-7 w-7 shrink-0 text-red-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        {{ $title ?? 'Colecciones (Fondos)' }}
                    </h3>

                    <div class="w-full md:w-64 max-w-5xl">
                        <label for="filtro-area" class="mb-1 block text-xs font-bold uppercase text-red-200">Filtrar por Área:</label>
                        <select id="filtro-area"
                            onchange="window.location.href = '{{ route('fondos.index') }}?area_id=' + this.value"
                            class="dark:bg-neutral-600 dark:text-white w-full rounded-md bg-white px-3 py-2 text-base text-gray-800 focus:outline-none focus:ring-1 focus:ring-red-400 md:text-sm">
                            <option value="">-- Todas las Áreas --</option>
                            @foreach ($areasDisponibles as $itemArea)
                                <option value="{{ $itemArea->id }}"
                                    {{ request('area_id') == $itemArea->id ? 'selected' : '' }}>
                                    {{ $itemArea->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <livewire:colecciones-list :areaId="request('area_id')" :key="'colecciones-' . request('area_id', 'todas')" />

        </div>
    </section>
@endsection