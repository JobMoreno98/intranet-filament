<button type="button" wire:click="toggleFavorito" wire:loading.attr="disabled"
    class="group inline-flex items-center gap-2 px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-xl border transition-all duration-200 shadow-sm cursor-pointer disabled:opacity-60
    {{ $esFavorito
        ? 'bg-[#86212b]/10 text-[#86212b] border-[#86212b]/30 hover:bg-[#86212b] hover:text-white dark:bg-[#86212b]/25 dark:text-red-200 dark:border-[#86212b]/60 dark:hover:bg-[#86212b] dark:hover:text-white'
        : 'bg-white text-zinc-700 border-zinc-300 hover:border-[#86212b] hover:text-[#86212b] hover:bg-red-50/40 dark:bg-zinc-800 dark:text-zinc-200 dark:border-zinc-600 dark:hover:border-red-400 dark:hover:text-white dark:hover:bg-zinc-700' }}">

    <svg class="w-4 h-4 transition-transform duration-200 group-hover:scale-110 {{ $esFavorito ? 'fill-current text-[#86212b] group-hover:text-white dark:text-red-400' : 'fill-none stroke-current' }}"
        viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round"
            d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
    </svg>

    <span>{{ $esFavorito ? 'En favoritos' : 'Agregar a favoritos' }}</span>
</button>
