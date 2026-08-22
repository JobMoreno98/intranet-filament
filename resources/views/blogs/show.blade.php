@extends('layouts.plantilla')

@section('css')
    <style>
        .text-custom-wine {
            color: #86212b;
        }

        .bg-custom-wine {
            background-color: #86212b;
        }

        .hover-text-custom-wine:hover {
            color: #86212b;
        }

        /* Tipografía de lectura larga */
        .post-contenido {
            font-size: 1.05rem;
        }

        .post-contenido p {
            margin-bottom: 1.35rem;
            line-height: 1.85;
        }

        .post-contenido h2 {
            font-weight: 700;
            font-size: 1.5rem;
            margin-top: 2.5rem;
            margin-bottom: 1rem;
            color: #27272a;
        }

        .post-contenido h3 {
            font-weight: 700;
            font-size: 1.2rem;
            margin-top: 2rem;
            margin-bottom: 0.75rem;
            color: #27272a;
        }

        /* Imágenes dentro del cuerpo, con espacio para su pie de foto */
        .post-contenido figure,
        .post-contenido img {
            margin: 2rem 0;
        }

        .post-contenido img {
            width: 100%;
            border-radius: 0.25rem;
        }

        .post-contenido figcaption,
        .post-contenido img + em {
            display: block;
            font-size: 0.8rem;
            color: #71717a;
            margin-top: 0.5rem;
            text-align: center;
        }

        .post-contenido a {
            color: #86212b;
            text-decoration: underline;
            text-underline-offset: 2px;
        }

        .post-contenido blockquote {
            border-left: 3px solid #86212b;
            padding-left: 1.25rem;
            font-style: italic;
            color: #52525b;
            margin: 2rem 0;
            font-size: 1.15rem;
        }
    </style>
@endsection

@section('content')
    <article>

        {{-- Cabecera del artículo --}}
        <header class="w-full max-w-3xl mx-auto px-4 pt-16 pb-8 flex flex-col gap-4 text-center">
            <a href="{{ route('blog.index') }}"
                class="text-xs uppercase tracking-[0.15em] font-bold text-custom-wine hover:underline w-fit mx-auto">
                Blog del Archivo
            </a>

            <h1 class="text-3xl md:text-[2.75rem] leading-tight font-bold text-zinc-900 dark:text-white">
                {{ $post->nombre }}
            </h1>

            <div class="flex items-center justify-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                <span>{{ $post->created_at->format('d \d\e F, Y') }}</span>
            </div>

            @if (!empty($post->tags))
                <div class="flex flex-wrap justify-center gap-2 pt-1">
                    @foreach ($post->tags as $tagItem)
                        <a href="{{ route('blog.index', ['tag' => $tagItem]) }}"
                            class="text-[11px] uppercase tracking-wide font-semibold bg-zinc-100 text-zinc-600 rounded-full px-3 py-1 hover:bg-red-50 hover:text-custom-wine transition-colors dark:bg-zinc-800 dark:text-zinc-300">
                            {{ $tagItem }}
                        </a>
                    @endforeach
                </div>
            @endif
        </header>

        {{-- Imagen de portada a todo lo ancho --}}
        <div class="w-full max-w-5xl mx-auto px-4">
            <img src="{{ asset('storage/' . $post->portada) }}" alt="{{ $post->nombre }}"
                class="w-full max-h-[480px] object-cover rounded-md">
        </div>

        {{-- Cuerpo del artículo: columna angosta para lectura --}}
        <div class="w-full max-w-2xl mx-auto px-4 py-12">

            <div class="post-contenido text-zinc-700 dark:text-zinc-200">
                {!! $post->contenido !!}
            </div>

            {{-- Caja de contenido relacionado, estilo "descubre más" --}}
            @if (isset($relacionados) && $relacionados->isNotEmpty())
                <div class="bg-zinc-50 border border-zinc-200 rounded-md p-5 my-10 dark:bg-zinc-800 dark:border-zinc-700">
                    <h4 class="text-xs uppercase tracking-wide font-bold text-custom-wine mb-3">
                        Descubre también
                    </h4>
                    <ul class="flex flex-col gap-2">
                        @foreach ($relacionados as $rel)
                            <li>
                                <a href="{{ route('blog.show', $rel->slug) }}"
                                    class="text-sm font-semibold text-zinc-700 hover-text-custom-wine transition-colors dark:text-zinc-200">
                                    → {{ $rel->nombre }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Compartir --}}
            <div class="border-t border-zinc-200 dark:border-zinc-700 pt-6 flex items-center justify-center gap-3">
                <span class="text-xs font-bold text-zinc-500 uppercase tracking-wide">Compartir:</span>
                <a href="https://wa.me/?text={{ urlencode($post->nombre . ' ' . url()->current()) }}"
                    target="_blank"
                    class="text-xs bg-zinc-100 hover:bg-zinc-200 text-zinc-700 rounded-full py-1.5 px-3 transition-colors dark:bg-zinc-800 dark:text-white">
                    WhatsApp
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
                    target="_blank"
                    class="text-xs bg-zinc-100 hover:bg-zinc-200 text-zinc-700 rounded-full py-1.5 px-3 transition-colors dark:bg-zinc-800 dark:text-white">
                    Facebook
                </a>
            </div>

        </div>

        {{-- Artículos relacionados en grid, al final --}}
        @if (isset($relacionados) && $relacionados->isNotEmpty())
            <div class="w-full bg-zinc-50 dark:bg-zinc-800 py-12 mt-4">
                <div class="max-w-5xl mx-auto px-4 flex flex-col gap-6">
                    <h3 class="text-sm font-bold text-zinc-800 uppercase tracking-wide text-center dark:text-white">
                        Más publicaciones
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        @foreach ($relacionados as $rel)
                            <a href="{{ route('blog.show', $rel->slug) }}" class="group flex flex-col gap-3">
                                <img src="{{ asset('storage/' . $rel->portada) }}" alt="{{ $rel->nombre }}"
                                    class="w-full aspect-square object-cover rounded-md">
                                <span class="text-sm font-semibold text-zinc-800 line-clamp-2 group-hover:text-custom-wine transition-colors dark:text-white">
                                    {{ $rel->nombre }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

    </article>
@endsection