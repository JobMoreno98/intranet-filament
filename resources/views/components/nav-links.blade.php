@props([
    'withIcons' => false,
    'wireNavigate' => false,
    'itemClass' => '',
])

@php
    $enlaces = [
        ['INICIO', 'home', 'home'],
        ['NUESTRA BIBLIOTECA', 'nuestra.historia', 'building-library'],
        ['ÁREAS Y FONDOS', 'areas.index', 'folder-open'],
        ['ARREOLA HISTÓRICA', 'arreola.historica', 'archive-box'],
        ['INSTRUCCIONES DE USO', 'arreola.uso', 'information-circle'],
        ['SOLICITUD DE REPRODUCCIONES', 'arreola.solicitud', 'document-duplicate'],
        ['SALA ARREOLA (BLOG)', 'blog.index', 'newspaper'],
        ['MEMORIA UDG', 'arreola.memoria', 'academic-cap'],
        ['CONTACTO', 'arreola.contacto', 'envelope'],
        ['CRÉDITOS', 'arreola.creditos', 'users'],
    ];
@endphp

<ul {{ $attributes->merge(['class' => 'flex flex-col space-y-1 font-medium']) }}>
    @foreach ($enlaces as [$etiqueta, $ruta, $icono])
        @php $activo = request()->routeIs($ruta); @endphp
        <li>
            <a href="{{ route($ruta) }}"
                @if($wireNavigate) wire:navigate @endif
                class="uppercase flex items-center gap-3 rounded-md px-3 py-2.5 transition-colors duration-200 hover:bg-guinda hover:text-white {{ $itemClass }}
                {{ $activo
                    ? 'bg-guinda text-white font-semibold shadow-sm'
                    : 'text-zinc-700 dark:text-zinc-200' }}">
                @if ($withIcons)
                    <flux:icon :name="$icono" class="w-5 h-5 shrink-0 opacity-85" />
                @endif
                <span>{{ $etiqueta }}</span>
            </a>
        </li>
    @endforeach
</ul>