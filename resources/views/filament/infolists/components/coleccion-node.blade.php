<li class="space-y-2">
    <div class="flex items-center gap-2 text-sm text-zinc-300" style="padding-left: {{ $depth * 1.25 }}rem;">
        <svg class="w-4 h-4 text-zinc-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
        </svg>
        
        <span class="font-medium text-zinc-900">{{ $node->nombre }}</span>
    </div>

    @if($node->children->isNotEmpty())
        <ul class="space-y-2">
            @foreach($node->children()->orderBy('nombre')->get() as $subChild)
                @include('filament.infolists.components.coleccion-node', ['node' => $subChild, 'depth' => $depth + 1])
            @endforeach
        </ul>
    @endif
</li>