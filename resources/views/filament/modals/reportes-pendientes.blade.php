<div class="space-y-3">
    @forelse ($reportes as $reporte)
        <div class="rounded-lg border border-gray-200 p-3 dark:border-white/10">
            <div class="mb-1 text-xs text-gray-500 dark:text-gray-400">
                {{ $reporte->created_at?->format('d/m/Y H:i') }}
            </div>
            <p class="whitespace-pre-line text-sm">Reportado por: {{ $reporte->user->name }}</p>
            <p class="whitespace-pre-line text-sm">{{ $reporte->observaciones }}</p>
        </div>
    @empty
        <p class="text-sm text-gray-500">No hay reportes pendientes.</p>
    @endforelse
</div>