@props([
    'withIcons' => false,
    'wireNavigate' => false,
    'itemClass' => '',
    'showAuth' => true,
    'onlyAuth' => false,
])

@php
    $enlacesPublicos = [
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

    $enlacesAuth = [['DASHBOARD', 'dashboard', 'squares-2x2'], ['MIS FAVORITOS', 'favoritos.index', 'heart']];
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-col space-y-4 font-medium']) }}>
    {{-- Enlaces Públicos --}}
    {{-- Enlaces Exclusivos para Usuarios Autenticados --}}
    @if ($showAuth)
        @auth
            <div class="{{ $onlyAuth ? '' : 'pt-3 border-t border-gray-200 dark:border-neutral-700' }}">
                <span class="block px-3 pb-2 text-xs font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-300">
                    Mi Cuenta
                </span>
                <ul class="flex flex-col space-y-1">
                    @foreach ($enlacesAuth as [$etiqueta, $ruta, $icono])
                        @php $activo = request()->routeIs($ruta); @endphp
                        <li>
                            <a href="{{ route($ruta) }}" @if ($wireNavigate) wire:navigate @endif
                                class="uppercase flex items-center gap-3 rounded-md px-3 py-2.5 transition-colors duration-200 hover:bg-guinda hover:text-white {{ $itemClass }}
                                {{ $activo ? 'bg-guinda text-white font-semibold shadow-sm' : 'text-zinc-700 dark:text-zinc-200' }}">
                                @if ($withIcons)
                                    <flux:icon :name="$icono" class="w-5 h-5 shrink-0 opacity-85" />
                                @endif
                                <span>{{ $etiqueta }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endauth
    @endif
    @unless ($onlyAuth)
        <ul class="flex flex-col space-y-1">
            @foreach ($enlacesPublicos as [$etiqueta, $ruta, $icono])
                @php $activo = request()->routeIs($ruta); @endphp
                <li>
                    <a href="{{ route($ruta) }}" @if ($wireNavigate) wire:navigate @endif
                        class="uppercase flex items-center gap-3 rounded-md px-3 py-2.5 transition-colors duration-200 hover:bg-guinda hover:text-white {{ $itemClass }}
                        {{ $activo ? 'bg-guinda text-white font-semibold shadow-sm' : 'text-zinc-700 dark:text-zinc-200' }}">
                        @if ($withIcons)
                            <flux:icon :name="$icono" class="w-5 h-5 shrink-0 opacity-85" />
                        @endif
                        <span>{{ $etiqueta }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    @endunless
</div>
