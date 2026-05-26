<div class="p-4 bg-zinc-50 border border-zinc-800 rounded-xl space-y-2">
    @php $record = $getRecord(); @endphp

    @if($record->children->isEmpty())
        <p class="text-sm text-zinc-500 italic">Esta colección no tiene subcolecciones descendientes.</p>
    @else
        <ul class="space-y-2">
            @foreach($record->children()->orderBy('nombre')->get() as $child)
                
                @include('filament.infolists.components.coleccion-node', ['node' => $child, 'depth' => 1])
            @endforeach
        </ul>
    @endif
</div>