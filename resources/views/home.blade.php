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
                            Un espacio dedicado a la <span class="text-[rgb(233,79,53)]">memoria</span>.
                        </h2>

                        {{-- Parrafo descriptivo 
                        <p class="text-sm sm:text-base text-zinc-300 leading-relaxed">
                            Lorem ipsum
                        </p>
                        --}}
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

    <section class="py-10 bg-[#ffffff] dark:bg-neutral-800">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            @if ($novedades->isNotEmpty())
                <!-- Grid de 2 filas exactas de 5 elementos en desktop (lg:grid-cols-5) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                    @foreach ($novedades as $index => $item)
                        <div data-aos="fade-up" data-aos-duration="400" data-aos-delay="{{ ($index % 10) * 40 }}"
                            class="dark:bg-zinc-700  group flex flex-col justify-between h-32 rounded-xl bg-[#ffffff] p-4 border border-[#131f30]/15 shadow-sm hover:shadow-md hover:border-[#1d1d1b] transition-all duration-200">
                            <div>
                                <span
                                    class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md text-[11px] font-semibold bg-[#1d1d1b]/10 text-[#1d1d1b] border border-[#1d1d1b]/20">
                                    <svg class="w-3 h-3 dark:text-white text-[#1d1d1b] flex-shrink-0" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                        </path>
                                    </svg>
                                    <span class="truncate dark:text-white hover:text-guinda">
                                        {{ $item->acervo->nombre ?? 'General' }}</span>
                                </span>
                            </div>
                            <h3 class="dark:text-white hover:text-guinda text-sm font-semibold text-[#131f30] line-clamp-2 leading-snug transition-colors duration-150"
                                title="{{ $item->metadata['titulo'] }}">
                                {{ $item->metadata['titulo'] }}
                            </h3>
                            <a href="{{ route('buscador.registro', ['tipo' => 'documento', 'id' => $item->id]) }}"
                                class="dark:text-white text-right mt-1  w-fit items-center gap-1 text-xs font-bold uppercase tracking-wide text-guinda 
                                border-b-2 border-[#86212b] pb-0.5">
                                Leer más
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <div
                    class="flex flex-col items-center justify-center py-12 px-4 text-center rounded-2xl border border-dashed border-[#131f30]/20 bg-[#ffffff]">
                    <h4 class="text-base font-semibold text-[#131f30]">Aún no hay datos disponibles</h4>
                </div>
            @endif

        </div>
    </section>
    <section>
        <div class="mx-auto w-full max-w-7xl px-4 sm:px-7 py-10" data-aos="fade-up" data-aos-duration="500">
            <div class="w-full bg-[var(--color-guinda)] py-2 px-4 my-2 rounded-md dark:bg-red-700">
                <div class="max-w-6xl mx-auto text-center flex flex-col gap-3">
                    <span class="text-white text-md uppercase tracking-[0.2em] font-bold">
                        SALA ARREOLA
                    </span>
                </div>
            </div>
            <x-slider :posts="$blogs" />
        </div>
    </section>

    <section>
        <div class="mx-auto w-full max-w-7xl px-4 sm:px-7 py-10">

            @if ($areas->count())
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach ($areas as $index => $item)
                        <div class="h-full" data-aos="fade-up" data-aos-duration="500" data-aos-delay="{{ $index * 50 }}">
                            <livewire:area :area="$item" :key="$item->id" />
                        </div>
                    @endforeach
                </div>
            @else
                <div class="w-full text-center py-12">
                    <h4 class="text-lg font-medium text-zinc-500">No hay datos aun</h4>
                </div>
            @endif
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
