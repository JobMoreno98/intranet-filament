@extends('layouts.plantilla')

@section('css')
    <style>
        .bg-custom-wine { background-color: #86212b; }
        .text-custom-wine { color: #86212b; }
        .border-custom-wine { border-color: #86212b; }
        .hover-bg-custom-wine:hover { background-color: #6d1b23; }
    </style>
@endsection

@section('content')
    <section class="bg-gray-50">
        <div class="sm:px-7 px-2 w-full py-20 flex flex-col gap-6">
            
            <div class="shadow-lg bg-white rounded-lg overflow-hidden">
                <div class="bg-[#86212b] p-4 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <h3 class="text-2xl font-bold text-white uppercase tracking-wider flex items-center gap-3">
                        <svg class="w-8 h-8 text-red-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        {{ $title }}
                    </h3>
                    
                    <div class="w-full md:w-72">
                        <label class="block text-xs font-bold text-red-200 uppercase mb-1">Filtrar por Tipo de Acervo:</label>
                        <select onchange="window.location.href = '?acervo_id=' + this.value" 
                                class="w-full bg-white text-gray-800 text-sm rounded-md px-3 py-2 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-red-400">
                            <option value="">-- Todos los Acervos --</option>
                            @foreach($acervosDisponibles as $itemAcervo)
                                @if($itemAcervo->acervo)
                                    <option value="{{ $itemAcervo->acervo_id }}" {{ request('acervo_id') == $itemAcervo->acervo_id ? 'selected' : '' }}>
                                        {{ $itemAcervo->acervo->nombre }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="p-4 bg-gray-100/50">
                    <livewire:filter-form  :acervoId="request('acervo_id')" />
                </div>
            </div>

            @php
                // --- PROCESAMIENTO DINÁMICO DE COLUMNAS DESDE METADATA ---
                $firstItem = $data->first();
                
                // Extraemos las llaves del JSON metadata del primer registro disponible
                $allKeys = ($firstItem && isset($firstItem->metadata)) ? array_keys((array) $firstItem->metadata) : [];
                
                // Mapeo estético de nombres técnicos a etiquetas legibles
                $cambios = [
                    'anio' => 'Año', 'tipoarchivo' => 'Tipo Archivo', 'numpaginas' => 'No. Páginas',
                    'numarchivos' => 'No. Archivos', 'numero' => 'Número', 'titulo' => 'Título',
                    'autor' => 'Autor', 'dia' => 'Día', 'paginas' => 'Páginas', 'epocaperiodo' => 'Epoca o Periodo',
                    'personaje_principal' => 'Nombre de Personaje principal', 'personaje_secundario' => 'Nombre de Personaje secundario',
                    'clavefondoprincipal' => 'Clave Fondo Principal', 'fondoprincipal' => 'Fondo principal',
                    'lugar' => 'Lugar', 'lugar_2' => 'Lugar 2', 'anio_2' => 'Año 2',
                    'numinventario' => 'No. Inventario', 'observaciones' => 'Observaciones', 'autor' => 'Autor',
                    'volumentomoejemplar' => 'Volumen / Tomo / Ejemplar','observaciones_2' => 'Observaciones 2', 'observaciones_3' => 'Observaciones 3'
                ];

                // Atributos que queremos que salgan primero en la tabla si existen
                $prioritarios = ['titulo', 'autor', 'anio'];

                // Quitamos llaves de control internas si se colaron en la metadata
                $ignorar = ['id', 'created_at', 'updated_at', 'deleted_at','status'];
                $keysFiltradas = array_filter($allKeys, fn($k) => !in_array($k, $ignorar));
                
                $headerPrimarios = array_filter($keysFiltradas, fn($k) => in_array(strtolower($k), $prioritarios));
                $headerResto = array_diff($keysFiltradas, $headerPrimarios);
                sort($headerResto);
                
                // Unimos y limitamos a un máximo de 6 columnas visibles para que no se rompa la vista
                $ordenTotal = array_merge($headerPrimarios, $headerResto);
                $columnasVisibles = array_slice($ordenTotal, 0, 6);
                $tieneMasColumnas = count($ordenTotal) > 6;
            @endphp

            <div class="relative overflow-x-auto shadow-2xl rounded-md border border-gray-200 bg-white">
                <table class="min-w-full divide-y divide-gray-200 text-left">
                    <thead class="bg-custom-wine text-white">
                        <tr>
                            <th class="px-4 py-3 text-xs font-bold uppercase tracking-widest">Acervo</th>
                            
                            @foreach ($columnasVisibles as $item)
                                <th class="px-4 py-3 text-xs font-bold uppercase tracking-widest">
                                    {{ $cambios[strtolower($item)] ?? $cambios[$item] ?? $item }}
                                </th>
                            @endforeach
                            <th class="px-2 py-3 text-center text-xs font-bold uppercase tracking-widest">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($data as $index => $registro)
                            @php
                                // Convertimos metadata a un array normal por seguridad en la lectura
                                $regMeta = (array) $registro->metadata;
                            @endphp
                            <tr class="hover:bg-red-50/30 transition-colors">
                                <td class="px-4 py-4 text-sm text-gray-700 font-bold">
                                    {{ $registro->acervo->nombre }}
                                </td>

                                @foreach ($columnasVisibles as $item)
                                    <td class="px-4 py-4 text-sm text-gray-600 font-medium">
                                        {{ isset($regMeta[$item]) && $regMeta[$item] !== '' && $regMeta[$item] !== '-' ? $regMeta[$item] : '---' }}
                                    </td>
                                @endforeach

                                <td class="px-3 py-2 text-center">
                                    <button type="button" onclick="toggleModal('modal-{{ $index }}', true)"
                                        class="text-custom-wine border-2 border-custom-wine rounded-sm px-2 py-1 font-black text-xs uppercase tracking-widest hover:bg-custom-wine hover:text-white transition-all">
                                        Ver Detalle
                                        </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-8 text-gray-400 font-medium text-sm">
                                    No se encontraron registros que coincidan con los filtros aplicados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @foreach ($data as $index => $registro)
                @php
                    $regMeta = (array) $registro->metadata;
                @endphp
                <div id="modal-{{ $index }}" class="fixed inset-0 z-[9999] invisible opacity-0 transition-all duration-300 ease-out overflow-y-auto" role="dialog" aria-modal="true">
                    <div class="fixed inset-0 bg-black/60 backdrop-blur-md" onclick="toggleModal('modal-{{ $index }}', false)"></div>
                    <div class="flex items-center justify-center min-h-screen p-4 pointer-events-none">
                        <div id="content-{{ $index }}" class="relative bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col transform scale-90 transition-all duration-300 ease-out pointer-events-auto">
                            
                            <div class="px-6 py-5 bg-custom-wine text-white flex justify-between items-center shadow-lg">
                                <div>
                                    <h3 class="text-xl font-bold tracking-tight uppercase">Información Completa</h3>
                                    <p class="text-xs text-red-200 mt-1 uppercase tracking-widest font-medium">Pertenece al Acervo: {{ $registro->acervo->nombre }}</p>
                                </div>
                                <button onclick="toggleModal('modal-{{ $index }}', false)" class="text-white/80 hover:text-white text-4xl leading-none">&times;</button>
                            </div>

                            <div class="p-8 overflow-y-auto bg-gray-50/50">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div class="bg-white p-4 rounded-md border-l-4 border-gray-400 shadow-sm">
                                        <dt class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Estatus del recurso</dt>
                                        <dd class="text-sm text-gray-800 font-semibold uppercase">{{ $registro->status ?? 'N/A' }}</dd>
                                    </div>

                                    @foreach ($ordenTotal as $item)
                                        <div class="bg-white p-4 rounded-md border-l-4 border-custom-wine shadow-sm">
                                            <dt class="text-[10px] font-black text-custom-wine uppercase tracking-widest mb-1">
                                                {{ $cambios[strtolower($item)] ?? $cambios[$item] ?? $item }}
                                            </dt>
                                            <dd class="text-sm text-gray-800 font-semibold leading-relaxed">
                                                {{ isset($regMeta[$item]) && $regMeta[$item] !== '' && $regMeta[$item] !== '-' ? $regMeta[$item] : 'N/A' }}
                                            </dd>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="px-8 py-5 border-t bg-white text-right">
                                <button onclick="toggleModal('modal-{{ $index }}', false)" class="bg-custom-wine text-white px-5 py-2 rounded-md text-xs font-bold uppercase tracking-widest">
                                    Cerrar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="mx-auto w-full py-2">
                <div class="flex flex-wrap justify-center items-center gap-2">
                    {{ $data->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </section>
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