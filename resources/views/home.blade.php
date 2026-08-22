@extends('layouts.plantilla')

@section('content')
    <section>
        <div class="mx-auto sm:px-7 px-2 max-w-screen-xl py-10 flex gap-10 flex-col items-center w-full">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 w-full">
                @foreach ($tiposAcervo as $categoria)
                    @php
                        // Comprobamos si la iteración actual corresponde a las tarjetas anchas
                        $esAncho = in_array($loop->iteration, [1, 4, 6]);
                    @endphp

                    <a href="#"
                        class="dark:bg-zinc-700  group rounded-xl border bg-white p-6 shadow-sm hover:shadow-lg hover:-translate-y-1 transition 
                                           {{ $esAncho ? 'flex flex-col lg:col-span-2 flex items-center justify-start gap-4 text-left' : 'lg:col-span-1 text-center flex flex-col items-center justify-center' }}">

                        {{-- El componente dinámico carga el icono correspondiente --}}
                        <x-dynamic-component :component="'heroicon-o-' . $categoria->icono"
                            class="dark:text-white {{ $esAncho ? 'w-12 h-12 shrink-0' : 'w-10 h-10 mb-3' }} {{ $categoria->color ?? 'text-guinda' }}" />

                        <h4 class="dark:text-white font-semibold text-gray-900 {{ $esAncho ? 'text-lg' : '' }}">
                            {{ $categoria->nombre }}
                        </h4>
                    </a>
                @endforeach
            </div>
        </div>

        <div
            class="mx-auto sm:px-7 px-2 md:max-w-screen-xl py-10 flex gap-10 flex flex-col lg:flex-row items-center max-w-[300px] sm:max-w-[340px]">
            <div class="flex flex-col gap-8 w-full">
                @forelse ($areas as $index => $item)
                    <div data-aos="fade-up" data-aos-duration="500" data-aos-delay="{{ $index * 50 }}">
                        <livewire:area :area="$item" :key="$item->id" />
                    </div>
                @empty
                    <div class="w-full text-center py-12">
                        <h4 class="text-lg font-medium text-zinc-500">No hay datos aun</h4>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="mx-auto max-w-7xl px-2">
            {{ $areas->links() }}
        </div>

    </section>
@endsection

@section('js')
    <script>
        function toggleChildren(parentId) {
            const childrenContainer = document.getElementById(`children-${parentId}`);
            if (childrenContainer) {
                childrenContainer.classList.toggle('hidden');
            }
        }
    </script>
@endsection