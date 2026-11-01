<div class="grid grid-cols-1 lg:grid-cols-2 gap-2">
    <!-- 1. Datos Generales -->
    @foreach ($generales as $item)
        <div class="lg:col-span-1">
            <div class="h-full rounded-xl border border-zinc-800 bg-zinc-950 p-4 hover:border-zinc-700 transition">
                <div class="text-[11px] font-bold text-zinc-500 uppercase tracking-wider mb-2">{{ $item['label'] }}</div>
                <div class="text-sm text-zinc-200 break-words leading-relaxed">{{ $item['valor'] }}</div>
            </div>
        </div>
    @endforeach

    <!-- 2. Metadatos -->
    @foreach ($metadataList as $item)
        <div class="{{ $item['largo'] ? 'lg:col-span-2' : 'lg:col-span-1' }}">
            <div class="h-full rounded-xl border border-zinc-800 bg-zinc-950 p-4 hover:border-zinc-700 transition">
                <div class="text-[11px] font-bold text-zinc-500 uppercase tracking-wider mb-2">{{ $item['label'] }}</div>
                <div class="text-sm text-zinc-200 break-words leading-relaxed whitespace-pre-wrap">{{ $item['valor'] }}</div>
            </div>
        </div>
    @endforeach

    <!-- 3. Datos Largos -->
    @foreach ($largos as $item)
        <div class="lg:col-span-2">
            <div class="h-full rounded-xl border border-zinc-800 bg-zinc-950 p-4 hover:border-zinc-700 transition">
                <div class="text-[11px] font-bold text-zinc-500 uppercase tracking-wider mb-2">{{ $item['label'] }}</div>
                <div class="text-sm text-zinc-200 break-words leading-relaxed whitespace-pre-wrap">{{ $item['valor'] }}</div>
            </div>
        </div>
    @endforeach
</div>