@extends('layouts.plantilla')

@section('content')
    <section>

        <div
            class="mx-auto sm:px-7 px-2 md:max-w-screen-xl py-10 flex gap-10 flex flex-col lg:flex-row items-center max-w-[300px] sm:max-w-[340px]">

            <article
                class="relative w-full rounded-2xl bg-gradient-to-br from-[#131f30] via-[#1a2a40] to-[#0f172a] text-white p-6 sm:p-8 md:p-10 shadow-xl overflow-hidden border border-white/10 my-6">

                {{-- Malla/Patrón sutil de fondo para dar textura --}}
                <div
                    class="absolute inset-0 opacity-10 bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:16px_16px] pointer-events-none">
                </div>

                {{-- Destello de color Naranja/Rojo decorativo en la esquina inferior --}}
                <div
                    class="absolute -bottom-12 -right-12 w-48 h-48 bg-[rgb(233,79,53)] rounded-full blur-3xl opacity-25 pointer-events-none">
                </div>

                <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">

                    {{-- Contenido principal del texto --}}
                    <div class="flex flex-col gap-3 max-w-2xl">

                        {{-- Badge o Etiqueta superior --}}
                        <div class="inline-flex items-center gap-2 self-start">
                            <span class="flex h-2 w-2 rounded-full bg-[rgb(233,79,53)] animate-pulse"></span>
                            <span class="text-xs font-bold uppercase tracking-widest text-zinc-300">
                                Bienvenido a Arreola Histórica
                            </span>
                        </div>

                        {{-- Título de Bienvenida --}}
                        <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold tracking-tight text-white leading-tight">
                            Un espacio dedicado a la <span class="text-[rgb(233,79,53)]">memoria</span> y el patrimonio.
                        </h2>

                        {{-- Parrafo descriptivo --}}
                        <p class="text-sm sm:text-base text-zinc-300 leading-relaxed">
                            Lorem ipsum
                        </p>
                    </div>

                    <div class="flex flex-wrap sm:flex-nowrap gap-3 w-full md:w-auto flex-shrink-0 pt-2 md:pt-0">
                        <a href="{{ route('busqueda.avanzada') }}"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 text-sm font-semibold rounded-xl bg-white/10 hover:bg-white/20 text-white border border-white/15 transition-all duration-200 backdrop-blur-sm">
                            Búsqueda avanzada
                        </a>
                    </div>

                </div>
            </article>
        </div>

    </section>
    <section>
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
