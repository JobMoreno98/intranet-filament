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
                <div class="bg-[#86212b] p-4 flex items-center gap-3">
                    <svg class="w-8 h-8 text-red-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 21V7a2 2 0 012-2h4l2-2h6a2 2 0 012 2v16M3 21h18M9 21V9h6v12" />
                    </svg>
                    <h3 class="text-2xl font-bold text-white uppercase tracking-wider">
                        {{ $title ?? 'Áreas' }}
                    </h3>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse ($areas as $index => $item)
                    <div data-aos="fade-up" data-aos-duration="500" data-aos-delay="{{ $index * 50 }}">
                        <livewire:area :area="$item" :key="$item->id" />
                    </div>
                @empty
                    <div class="col-span-full text-center py-12">
                        <h4 class="text-lg font-medium text-zinc-500">No hay áreas registradas aún</h4>
                    </div>
                @endforelse
            </div>

            @if (isset($areas) && $areas instanceof \Illuminate\Contracts\Pagination\Paginator)
                <div class="mx-auto w-full py-2">
                    <div class="flex flex-wrap justify-center items-center gap-2">
                        {{ $areas->links() }}
                    </div>
                </div>
            @endif

        </div>
    </section>
@endsection