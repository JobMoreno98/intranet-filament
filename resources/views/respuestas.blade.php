@extends('layouts.plantilla')

@section('css')
    <!-- Estilos css embebidos para resaltar la palabra encontrada -->
    <style>
        em {
            background-color: #fff3cd !important;
            color: #856404 !important;
            font-style: normal !important;
            font-weight: bold !important;
            padding: 2px 4px;
            border-radius: 4px;
            border: 1px solid #ffeeba;
        }
    </style>
@endsection

@section('content')
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <h2 class="mb-4 text-center">🔎 Buscador General Inteligente</h2>

                <!-- Formulario de Entrada Única -->
                <form action="{{ route('buscador') }}" method="GET" class="mb-4">
                    <div class="input-group input-group-lg shadow-sm">
                        <input type="text" name="q" value="{{ $term }}" class="form-control"
                            placeholder="Escribe palabras clave para buscar en todas las colecciones..." required
                            autocomplete="off">
                        <button class="btn btn-primary px-4" type="submit">Buscar</button>
                    </div>
                </form>

                @if (isset($resultados) && $resultados->count() > 0)
                    <p class="text-muted mb-3">Se encontraron <strong>{{ $resultados->total() }}</strong> coincidencias en
                        el sistema.</p>

                    <div class="list-group">
                        @foreach ($resultados as $res)
                            <div
                                class="list-group-item list-group-item-action p-3 mb-2 shadow-sm rounded border-start border-primary border-4">
                                <div class="d-flex w-100 justify-content-between align-items-center mb-2">
                                    <!-- Título o ID del registro -->
                                    <h5 class="mb-0 text-dark font-weight-bold">{{ $res['titulo_resultado'] }}</h5>
                                    <!-- Tag de la tabla de la base de datos -->
                                    <span
                                        class="badge bg-secondary text-uppercase px-2 py-1">{{ $res['coleccion_nombre'] }}</span>
                                </div>

                                <!-- El extracto de Meilisearch con el texto resaltado -->
                                <p class="mb-3 text-secondary small">
                                    {!! $res['coincidencia'] !!}
                                </p>

                                @if ($res['coleccion_id'])
                                    <!-- Redirección directa al método show de la colección pasándole la clave -->
                                    <a href="{{ url('coleccion/' . $res['coleccion_id']) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        Ver Tabla Completa
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <!-- Links de Paginación -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $resultados->links() }}
                    </div>
                @elseif($term)
                    <div class="alert alert-warning text-center shadow-sm">
                        No se encontraron registros que contengan: "<strong>{{ $term }}</strong>"
                    </div>
                @endif
            </div>
        </div>
    </div>


@endsection
