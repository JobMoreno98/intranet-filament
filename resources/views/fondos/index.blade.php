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
        <div class="sm:px-7 px-2 w-full py-20 flex flex-col gap-6">

            <div class="shadow-lg bg-white rounded-lg overflow-hidden">
                <div class="bg-[#86212b] p-4 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <h3 class="text-2xl font-bold text-white uppercase tracking-wider flex items-center gap-3">
                        <svg class="w-8 h-8 text-red-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        {{ $title ?? 'Colecciones (Fondos)' }}
                    </h3>

                    <div class="w-full md:w-72">
                        <label class="block text-xs font-bold text-red-200 uppercase mb-1">Filtrar por Área:</label>
                        <select onchange="window.location.href = '{{ route('fondos.index') }}?area_id=' + this.value"
                            class="dark:bg-neutral-600 dark:text-white w-full bg-white text-gray-800 text-sm rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-red-400">
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