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
                    <div class="flex items-center gap-3">
                        <svg class="w-8 h-8 text-red-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <div>
                            <h3 class="text-2xl font-bold text-white uppercase tracking-wider">
                                {{ $area->nombre }}
                            </h3>
                            <p class="text-xs text-red-200 uppercase tracking-widest font-medium mt-1">
                                {{ $area->ubicacion }}
                            </p>
                        </div>
                    </div>

                    <a href="{{ url()->previous() }}"
                        class="inline-flex items-center text-xs bg-white/10 border border-white/30 text-white rounded font-bold py-1.5 px-4 hover:bg-white/20 transition-colors">
                        ← Volver a Áreas
                    </a>
                </div>
            </div>

            <livewire:area-colecciones-list :area="$area" />

        </div>
    </section>
@endsection