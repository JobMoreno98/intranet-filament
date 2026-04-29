@props([
    'sidebar' => false,
])

@if ($sidebar)
    <flux:sidebar.brand name="{{ config('app.name', 'Laravel') }}" {{ $attributes }} class="text-center break-all">

    </flux:sidebar.brand>
@else
    <flux:brand name="{{ config('app.name', 'Laravel') }}" {{ $attributes }}>

    </flux:brand>
@endif
