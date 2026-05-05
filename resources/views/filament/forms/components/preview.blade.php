<div class="flex justify-center bg-gray-950 rounded-lg overflow-hidden border border-gray-800" style="height: 200px; width: 100%;">
    @php
        // Obtenemos el registro completo del modelo RecursosArchivos
        $record = $getRecord();
        
        // Generamos tu ruta firmada
        $url = URL::temporarySignedRoute(
            'media.stream', 
            now()->addMinutes(60), 
            ['archivo_id' => $getState(), 'tipo' => 'main']
        );

        // Obtenemos la extensión desde el nombre original o el path
        $extension = $record ? strtolower(pathinfo($record->nombre_archivo_original, PATHINFO_EXTENSION)) : null;
    @endphp

    @if($record)
        @if(in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif']))
            <img src="{{ $url }}" class="h-full w-full object-contain" loading="lazy">
        
        @elseif($extension === 'pdf')
            <div class="w-full h-full relative">
                <iframe src="{{ $url }}#toolbar=0" class="w-full h-full border-none"></iframe>
                {{-- Capa transparente para permitir el drag-and-drop del repeater sin que el iframe robe el foco --}}
                <div class="absolute inset-0 z-10"></div>
            </div>

        @elseif(in_array($extension, ['mp4', 'webm', 'ogv']))
            <video class="w-full h-full" preload="metadata">
                <source src="{{ $url }}" type="video/{{ $extension }}">
            </video>

        @else
            <div class="flex flex-col items-center justify-center text-gray-500 w-full">
                <x-heroicon-o-document-arrow-down class="w-12 h-12 mb-2 opacity-20"/>
                <span class="text-[10px] uppercase font-bold tracking-tighter">{{ $extension ?: 'Archivo' }}</span>
            </div>
        @endif
    @else
        <div class="flex items-center justify-center text-gray-600 italic text-xs">
            Cargando vista previa...
        </div>
    @endif
</div>