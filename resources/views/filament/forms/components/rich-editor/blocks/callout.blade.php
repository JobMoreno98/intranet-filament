@props([
    'type' => 'info',
    'title' => '',
    'message' => '',
])

@php
    $styles = [
        'info' => 'bg-blue-50 border-blue-500 text-blue-900',
        'warning' => 'bg-amber-50 border-amber-500 text-amber-900',
        'danger' => 'bg-red-50 border-red-500 text-red-900',
    ][$type] ?? 'bg-gray-50 border-gray-500 text-gray-900';
@endphp

<div class="p-4 my-4 border-l-4 rounded-r-lg {{ $styles }}">
    @if ($title)
        <h4 class="font-bold text-lg mb-1">{{ $title }}</h4>
    @endif
    <p class="m-0 text-sm">{{ $message }}</p>
</div>