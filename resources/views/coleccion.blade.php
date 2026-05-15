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
    <section class="bg-gray-50">
        <div class=" sm:px-7 px-2 w-full py-20 flex gap-10 flex flex-col lg:flex-row items-center">
            <div class="grid grid-cols-1  gap-4 w-full">
                <div class=" shadow-lg">
                    <div class="bg-[#86212b] p-3 rounded-t-lg">
                        <h3 class="text-2xl font-bold text-white uppercase tracking-wider flex items-center gap-3">
                            <svg class="w-8 h-8 text-red-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            {{ $title }}
                        </h3>
                    </div>


                    <livewire:filter-form :tabla="$tablaNombre" />
                </div>

                @php
                    // --- Lógica de preparación (Se mantiene igual) ---
                    $firstItem = $data->first();
                    $allKeys = $firstItem ? array_keys((array) $firstItem) : [];
                    $cambios = [
                        'anio' => 'Año',
                        'tipoarchivo' => 'Tipo Archivo',
                        'numpaginas' => 'No. Páginas',
                        'numarchivos' => 'No. Archivos',
                        'numero' => 'Número',
                        'titulo' => 'Título',
                        'autor' => 'Autor',
                        'dia' => 'Día',
                        'paginas' => 'Páginas',
                        'epocaperiodo' => 'Epoca o Periodo',
                        'nombrepersonajeprincipal' => 'Nombre de Personaje principal',
                        'nombrepersonajesecundario' => 'Nombre de Personaje secundario',
                        'clavefondoprincipal' => 'Clave Fondo Principal',
                        'fondoprincipal' => 'Fondo principal',
                        'lugar1' => 'Lugar 1',
                        'lugar2' => 'Lugar 2',
                        'anio2' => 'Año 2',
                        'numinventario' => 'No. Invenatrio',
                        'observaciones1' => 'Observaciones',
                        'autor1' => 'Autor 1',
                        'volumentomoejemplar' => 'Volumen / Tomo / Ejemplar',
                    ];
                    $prioritarios = ['titulo', 'autor', 'anio', 'clavefondoprincipal', 'fondoprincipal'];
                    $keysFiltradas = array_filter($allKeys, fn($k) => !str_contains(strtolower($k), 'id'));
                    $headerPrimarios = array_filter($keysFiltradas, fn($k) => in_array(strtolower($k), $prioritarios));
                    $headerResto = array_diff($keysFiltradas, $headerPrimarios);
                    sort($headerResto);
                    $ordenTotal = array_merge($headerPrimarios, $headerResto);

                    $columnasVisibles = array_slice($ordenTotal, 0, 6);
                    $tieneMasColumnas = count($ordenTotal) > 6;
                @endphp

                <div class="relative overflow-x-auto shadow-2xl rounded-md border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200 bg-white text-left">
                        <thead class="bg-custom-wine text-white">
                            <tr>
                                @foreach ($columnasVisibles as $item)
                                    <th class="px-2 py-2 text-xs font-bold uppercase tracking-widest">
                                        {{ $cambios[strtolower($item)] ?? $item }}
                                    </th>
                                @endforeach
                                @if ($tieneMasColumnas)
                                    <th class="px-2 py-4 text-center text-xs font-bold uppercase tracking-widest">Acciones
                                    </th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($data as $index => $value)
                                <tr class="hover:bg-red-50/30 transition-colors">
                                    @foreach ($columnasVisibles as $item)
                                        <td
                                            class="px-6 py-4 whitespace-nowrap md:whitespace-normal text-sm text-gray-700 font-medium">
                                            {{ isset($value->$item) && $value->$item !== '' && $value->$item !== '-' && $value->$item !== 0 ? $value->$item : '---' }}
                                        </td>
                                    @endforeach

                                    @if ($tieneMasColumnas)
                                        <td class="px-3 py-2 text-center">
                                            <button type="button" onclick="toggleModal('modal-{{ $index }}', true)"
                                                class="group relative inline-flex items-center 
                                                justify-center px-2 py-1 font-black text-xs tracking-widest 
                                                uppercase transition-all duration-300 ease-in-out 
               text-custom-wine border-2 border-custom-wine rounded-sm 
               hover:bg-custom-wine hover:text-white hover:shadow-[0_5px_15px_rgba(134,33,43,0.4)] hover:-translate-y-0.5 active:scale-95">
                                                <span>Ver Detalle</span>

                                            </button>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @foreach ($data as $index => $value)
                    @if ($tieneMasColumnas)
                        <div id="modal-{{ $index }}"
                            class="fixed inset-0 z-[9999] invisible opacity-0 transition-all duration-300 ease-out overflow-y-auto"
                            role="dialog" aria-modal="true">

                            <!-- Overlay con desenfoque -->
                            <div class="fixed inset-0 bg-black/60 backdrop-blur-md"
                                onclick="toggleModal('modal-{{ $index }}', false)"></div>

                            <!-- Contenedor de Centrado -->
                            <div class="flex items-center justify-center min-h-screen p-4 pointer-events-none">

                                <!-- Caja del Modal -->
                                <div id="content-{{ $index }}"
                                    class="relative bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col transform scale-90 transition-all duration-300 ease-out pointer-events-auto">

                                    <!-- Header con el color solicitado -->
                                    <div
                                        class="px-6 py-5 bg-custom-wine text-white flex justify-between items-center shadow-lg">
                                        <div>
                                            <h3 class="text-xl font-bold tracking-tight uppercase">Información Detallada
                                            </h3>
                                            <p class="text-xs text-red-200 mt-1 uppercase tracking-widest font-medium">
                                                Registro No. {{ $index + 1 }}</p>
                                        </div>
                                        <button onclick="toggleModal('modal-{{ $index }}', false)"
                                            class="text-white/80 hover:text-white text-4xl leading-none transition-colors">&times;</button>
                                    </div>

                                    <!-- Body con Grid de 2 columnas -->
                                    <div class="p-8 overflow-y-auto bg-gray-50/50">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                            @foreach ($ordenTotal as $item)
                                                <div
                                                    class="bg-white p-4 rounded-md border-l-4 border-custom-wine shadow-sm hover:shadow-md transition-shadow">
                                                    <dt
                                                        class="text-[10px] font-black text-custom-wine uppercase tracking-widest mb-1">
                                                        {{ $cambios[strtolower($item)] ?? $item }}
                                                    </dt>
                                                    <dd class="text-sm text-gray-800 font-semibold leading-relaxed">
                                                        {{ isset($value->$item) && $value->$item !== '' && $value->$item !== '-' && $value->$item !== 0 ? $value->$item : 'N/A' }}
                                                    </dd>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Footer -->
                                    <div class="px-8 py-5 border-t bg-white text-right">
                                        <button onclick="toggleModal('modal-{{ $index }}', false)"
                                            class="bg-custom-wine hover-bg-custom-wine text-white px-5 py-2 
                                            rounded-md text-xs font-bold transition-all shadow-lg active:scale-95 uppercase tracking-widest">
                                            Cerrar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
        <div class="mx-auto w-full px-4 sm:px-7 py-6">
            <div class="flex flex-wrap justify-center items-center gap-2 overflow-hidden">
                {{ $data->links() }}
            </div>
        </div>

    </section>


    {{--  --}}
@endsection

@section('js')
    <script>
        function toggleModal(modalId, show) {
            const modal = document.getElementById(modalId);
            const content = document.getElementById(modalId.replace('modal-', 'content-'));

            if (!modal || !content) return;

            if (show) {
                modal.classList.remove('invisible', 'opacity-0');
                modal.classList.add('opacity-100');
                content.classList.remove('scale-90');
                content.classList.add('scale-100');
                document.body.style.overflow = 'hidden';
            } else {
                modal.classList.remove('opacity-100');
                modal.classList.add('opacity-0');
                content.classList.remove('scale-100');
                content.classList.add('scale-90');

                setTimeout(() => {
                    modal.classList.add('invisible');
                    document.body.style.overflow = 'auto';
                }, 300);
            }
        }
    </script>
@endsection
