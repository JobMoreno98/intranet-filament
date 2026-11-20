<?php

use function Livewire\Volt\{state, with, usesPagination};
use App\Models\Blog;

usesPagination();

// ->url() captura automáticamente ?tag=... de la URL, lo decodifica (espacios, acentos, símbolos)
// y lo mantiene persistente al cambiar de página en Livewire
state([
    'tag' => null,
])->url();

with(function () {
    $tag = filled($this->tag) ? trim(urldecode((string) $this->tag)) : null;
    $mes = request('mes');
    $anio = request('anio');
    $q = request('q');

    if (!empty($q)) {
        $publicaciones = Blog::search($q)
            ->query(function ($builder) use ($tag, $mes, $anio) {
                // Filtrar por tag directamente en MySQL evita errores de sintaxis en Meilisearch
                // con espacios, comillas, acentos o caracteres especiales
                $builder->when(!empty($tag), function ($query) use ($tag) {
                    $query->whereJsonContains('tags', $tag);
                });

                $builder->when(!empty($mes) && !empty($anio), function ($query) use ($mes, $anio) {
                    $query->whereMonth('created_at', $mes)
                          ->whereYear('created_at', $anio);
                });
            })
            ->paginate(5);
    } else {
        $query = Blog::query()->orderByDesc('created_at');

        if (!empty($tag)) {
            $query->whereJsonContains('tags', $tag);
        }

        if (!empty($mes) && !empty($anio)) {
            $query->whereMonth('created_at', $mes)
                  ->whereYear('created_at', $anio);
        }

        $publicaciones = $query->paginate(5);
    }

    return [
        'publicaciones' => $publicaciones,
    ];
});
?>

<div wire:loading.class="opacity-50" class="transition-opacity duration-300 flex flex-col gap-8">

    @forelse ($publicaciones as $index => $post)
        <article data-aos="fade-up" data-aos-duration="500" data-aos-delay="{{ $index * 50 }}"
            class="bg-white rounded-lg border border-zinc-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow flex flex-col sm:flex-row dark:bg-zinc-700 dark:border-zinc-600">

            <a href="{{ route('blog.show', $post->slug) }}" class="sm:w-48 flex-shrink-0">
                <img src="{{ asset('storage/' . $post->portada) }}" alt="{{ $post->nombre }}"
                    class="h-full w-full aspect-square object-cover">
            </a>

            <div class="p-5 flex flex-col gap-2 flex-1">
                <div class="flex items-center gap-2 text-xs">
                    <span class="text-zinc-400">
                        {{ $post->created_at->translatedFormat('d M, Y') }}
                    </span>
                </div>

                <h2 class="text-lg font-bold text-zinc-800 dark:text-white">
                    <a href="{{ route('blog.show', $post->slug) }}" class="hover-text-custom-wine transition-colors">
                        {{ $post->nombre }}
                    </a>
                </h2>

                <p class="text-sm text-zinc-600 line-clamp-3 dark:text-zinc-300">
                    {{ Str::limit(strip_tags($post->contenido), 160) }}
                </p>

                @if (!empty($post->tags))
                    <div class="flex flex-wrap gap-1.5 mt-1">
                        @foreach ($post->tags as $tagItem)
                            <a href="{{ route('blog.index', ['tag' => $tagItem]) }}"
                                class="text-[11px] bg-zinc-100 text-zinc-600 rounded-full px-2.5 py-0.5 hover:bg-red-50 hover:text-custom-wine transition-colors dark:bg-zinc-800 dark:text-zinc-300">
                                #{{ $tagItem }}
                            </a>
                        @endforeach
                    </div>
                @endif

                <a href="{{ route('blog.show', $post->slug) }}"
                    class="text-sm font-bold text-custom-wine hover:underline mt-auto self-start pt-2">
                    Leer más →
                </a>
            </div>

        </article>
    @empty
        <div class="w-full text-center py-16">
            <h4 class="text-lg font-medium text-zinc-500">Aún no hay
                publicaciones{{ request('q') || request('mes') ? ' que coincidan con tu búsqueda' : '' }}</h4>
        </div>
    @endforelse

    <div class="mt-2 flex justify-center">
        {{ $publicaciones->links() }}
    </div>

</div>