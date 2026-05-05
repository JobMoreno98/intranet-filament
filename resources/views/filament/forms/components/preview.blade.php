<div class="relative flex justify-center bg-slate-900 rounded-md overflow-hidden border border-slate-800"
    style="aspect-ratio: 3/4; width: 100%; max-height: 250px;">

    @php
        $record = $getRecord();
        $url = null;

        if ($record) {
            // Generamos la firma para la ruta de miniaturas
            $url = URL::temporarySignedRoute('admin.media.thumbnail', now()->addMinutes(120), [
                'archivo_id' => $record->id,
            ]);
        }
    @endphp

    @if ($url)
        <img src="{{ $url }}" alt="Miniatura"
            class="h-full w-full object-cover transition-opacity duration-300 hover:opacity-80" loading="lazy"
            onerror="this.src='https://placehold.co/300x400/0f172a/64748b?text=Error+Vista+Previa'">

        {{-- Indicador de formato en la esquina --}}
        <div
            class="absolute bottom-1 right-1 px-1.5 py-0.5 bg-black/60 rounded text-[8px] font-bold text-slate-300 uppercase">
            {{ pathinfo($record->nombre_archivo_original, PATHINFO_EXTENSION) }}
        </div>
    @else
        <div class="flex items-center justify-center h-full w-full">
            <x-heroicon-m-photo class="w-8 h-8 text-slate-700 animate-pulse" />
        </div>
    @endif
</div>
