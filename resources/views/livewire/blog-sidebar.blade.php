{{-- Buscador --}}
<div class="bg-white rounded-lg border border-zinc-200 p-5 shadow-sm dark:bg-zinc-700 dark:border-zinc-600">
    <h4 class="text-sm font-bold text-zinc-800 uppercase tracking-wide mb-3 dark:text-white">
        Buscar en el blog
    </h4>
    <form action="{{ route('blog.index') }}" method="GET" class="flex gap-2">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Escribe algo..."
            class="flex-1 text-sm rounded-md border border-zinc-300 px-3 py-2 focus:outline-none focus:ring-1 focus:ring-red-400 dark:bg-zinc-800 dark:text-white dark:border-zinc-600">
        <button type="submit"
            class="bg-[#86212b] text-white rounded-md px-3 hover:bg-[#6d1b23] transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </button>
    </form>
</div>

{{-- Tags --}}
<div class="bg-white rounded-lg border border-zinc-200 p-5 shadow-sm dark:bg-zinc-700 dark:border-zinc-600">
    <h4 class="text-sm font-bold text-zinc-800 uppercase tracking-wide mb-3 dark:text-white">
        Etiquetas
    </h4>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('blog.index') }}"
            class="text-xs rounded-full px-3 py-1 transition-colors {{ !request('tag') ? 'bg-[#86212b] text-white' : 'bg-zinc-100 text-zinc-600 hover:bg-red-50 hover:text-custom-wine dark:bg-zinc-800 dark:text-zinc-300' }}">
            Todas
        </a>
        @foreach ($tagsDisponibles as $tagItem)
            <a href="{{ route('blog.index', ['tag' => $tagItem]) }}"
                class="text-xs rounded-full px-3 py-1 transition-colors {{ request('tag') == $tagItem ? 'bg-[#86212b] text-white' : 'bg-zinc-100 text-zinc-600 hover:bg-red-50 hover:text-custom-wine dark:bg-zinc-800 dark:text-zinc-300' }}">
                #{{ $tagItem }}
            </a>
        @endforeach
    </div>
</div>

{{-- Publicaciones recientes --}}
<div class="bg-white rounded-lg border border-zinc-200 p-5 shadow-sm dark:bg-zinc-700 dark:border-zinc-600">
    <h4 class="text-sm font-bold text-zinc-800 uppercase tracking-wide mb-3 dark:text-white">
        Publicaciones recientes
    </h4>
    <ul class="flex flex-col gap-4">
        @foreach ($recientes as $post)
            <li>
                <a href="{{ route('blog.show', $post->slug) }}" class="flex gap-3 group">
                    <img src="{{ asset('storage/' . $post->portada) }}"
                        alt="{{ $post->nombre }}"
                        class="w-16 h-16 object-cover rounded-md flex-shrink-0">
                    <div class="flex flex-col justify-center">
                        <span class="text-sm font-semibold text-zinc-800 line-clamp-2 group-hover:text-custom-wine transition-colors dark:text-white">
                            {{ $post->nombre }}
                        </span>
                        <span class="text-xs text-zinc-400 mt-1">
                            {{ $post->created_at->format('d M, Y') }}
                        </span>
                    </div>
                </a>
            </li>
        @endforeach
    </ul>
</div>