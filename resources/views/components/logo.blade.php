@props([
    'size' => 'md',
])

@php
    $sizes = [
        'sm' => 'h-8 w-8',
        'md' => 'h-10 w-10',
        'lg' => 'h-14 w-14',
        'xl' => 'h-20 w-20',
        '2xl' => 'h-28 w-28',
    ];
    $classes = $sizes[$size] ?? $sizes['md'];
@endphp

<img
    src="{{ asset('logo.png') }}"
    alt="Koding Indonesia"
    {{ $attributes->merge(['class' => "$classes object-contain border-2 border-black flex-shrink-0"]) }}
    style="box-shadow: 2px 2px 0 #000;"
    width="40"
    height="40"
    decoding="async"
>
