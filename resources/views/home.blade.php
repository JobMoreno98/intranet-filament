@extends('layouts.plantilla')

@section('css')
    <style>
        /* Ocultar scrollbar para Chrome, Safari y Opera */
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }

        /* Ocultar scrollbar para IE, Edge y Firefox */
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
@endsection

@section('content')

    <section>
        {{-- 

        <div
            class="mx-auto sm:px-7 px-2 md:max-w-screen-xl flex gap-10 flex flex-col lg:flex-row items-center max-w-[400px] sm:max-w-[340px]">

            <article
                class="relative w-full rounded-2xl bg-gradient-to-br from-[#131f30] via-[#1a2a40] to-[#0f172a] text-white p-6 sm:p-8 md:p-10 shadow-xl overflow-hidden border border-white/10 my-6">

                
                Malla/Patrón sutil de fondo para dar textura 
                <div
                    class="absolute inset-0 opacity-10 bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:16px_16px] pointer-events-none">
                </div>

                Destello de color Naranja/Rojo decorativo en la esquina inferior 
                <div
                    class="absolute -bottom-12 -right-12 w-48 h-48 bg-[rgb(233,79,53)] rounded-full blur-3xl opacity-25 pointer-events-none">
                </div>
 
                <div class=" w-full">
                    {{-- Contenido principal del texto 
                    <div class="flex flex-col gap-3 max-w-7xl">
                        {{-- Badge o Etiqueta superior 
                        <div class="inline-flex items-center gap-2 self-start">
                            <span class="flex h-2 w-2 rounded-full bg-[rgb(233,79,53)] animate-pulse"></span>
                            <span class="text-xs font-bold uppercase tracking-widest text-zinc-300">
                                Bienvenido a Arreola Histórica
                            </span>
                        </div>


        Parrafo descriptivo 
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
        --}}
        <div class="flex flex-col gap-3 max-w-7xl mx-auto px-4 sm:px-7 ">
            <h2
                class="text-2xl sm:text-3xl md:text-4xl font-extrabold tracking-tight text-guinda dark:text-white leading-tight">
                <div class="w-full py-2 my-2 rounded-md ">
                    <div class="max-full mx-auto flex flex-col text-center">
                        SALA ARREOLA
                    </div>
                </div>
                {{-- 
                            Un espacio dedicado a la <span class="text-[rgb(233,79,53)]">memoria</span>.
                             --}}
            </h2>
        </div>
        <section>
            <div class="mx-auto w-full max-w-7xl px-4 sm:px-7" data-aos="fade-up" data-aos-duration="500">
                <x-slider :posts="$blogs" />
            </div>
        </section>


    </section>

    <section class="py-10 px-4 sm:px-7 bg-[#ffffff] dark:bg-neutral-800 max-w-7xl mx-auto ">
        <!-- Se cambió de flex a grid con grid-flow-col y grid-rows-2 para forzar las dos filas en móvil -->
        <x-scroll-hint
            class="mx-2 grid grid-rows-2 grid-flow-col auto-cols-[85vw] overflow-x-auto gap-4 snap-x snap-mandatory pb-4 hide-scrollbar -mx-4 px-4 sm:mx-0 sm:px-0 sm:auto-cols-auto sm:grid-rows-none sm:grid-flow-row sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 sm:pb-0 sm:overflow-visible">
            @foreach ($novedades as $index => $item)
                <!-- La tarjeta ya no necesita w-[85vw], el ancho lo dicta la clase auto-cols-[85vw] del contenedor padre -->
                <div data-aos="fade-up" data-aos-duration="400" data-aos-delay="{{ ($index % 10) * 40 }}"
                    class="snap-center dark:bg-zinc-700 group flex flex-col justify-between h-32 rounded-xl bg-[#ffffff] p-4 border border-[#131f30]/15 shadow-sm hover:shadow-md hover:border-[#1d1d1b] transition-all duration-200">

                    <div>
                        <span
                            class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md text-[11px] font-semibold bg-[#1d1d1b]/10 text-[#1d1d1b] border border-[#1d1d1b]/20">
                            <svg class="w-3 h-3 dark:text-white text-[#1d1d1b] flex-shrink-0" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                </path>
                            </svg>
                            <span
                                class="truncate dark:text-white hover:text-guinda">{{ $item->acervo->nombre ?? 'General' }}</span>
                        </span>
                    </div>
                    <h3 class="dark:text-white hover:text-guinda text-sm font-semibold text-[#131f30] line-clamp-2 leading-snug transition-colors duration-150"
                        title="{{ $item->metadata['titulo'] }}">
                        {{ $item->metadata['titulo'] }}
                    </h3>
                    <a href="{{ route('buscador.registro', ['tipo' => 'documento', 'recurso' => $item]) }}"
                        class="dark:text-white text-right mt-1 w-fit items-center gap-1 text-xs font-bold uppercase tracking-wide text-guinda border-b-2 border-[#86212b] pb-0.5">
                        Leer más
                    </a>
                </div>
            @endforeach
        </x-scroll-hint>
    </section>


    <section>
        <div class="mx-auto w-full max-w-7xl px-4 sm:px-7 py-10">
            @if ($areas->count())
                <x-scroll-hint :gradient="false" :step="1"
                    class="flex overflow-x-auto gap-4 snap-x snap-mandatory pb-6 sm:grid sm:grid-cols-2 lg:grid-cols-4 sm:gap-8 sm:pb-0 sm:overflow-visible hide-scrollbar">
                    @foreach ($areas as $index => $item)
                        <div class="flex-none w-full sm:w-auto snap-center h-full" data-aos="fade-up"
                            data-aos-duration="500" data-aos-delay="{{ $index * 50 }}">
                            <livewire:area :area="$item" :key="$item->id" />
                        </div>
                    @endforeach
                </x-scroll-hint>
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
