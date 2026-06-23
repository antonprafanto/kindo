@props([
    'size' => 'md',
])

@php
    $sizes = [
        'xs' => 'h-7 w-7',
        'sm' => 'h-8 w-8',
        'md' => 'h-9 w-9 sm:h-10 sm:w-10',
        'lg' => 'h-14 w-14 sm:h-16 sm:w-16',
        'xl' => 'h-20 w-20 sm:h-24 sm:w-24',
    ];
    $class = $sizes[$size] ?? $sizes['md'];
@endphp

<img
    src="{{ asset('logo.png') }}"
    alt="Koding Indonesia"
    width="512"
    height="512"
    {{ $attributes->merge(['class' => $class . ' object-contain flex-shrink-0']) }}
    loading="eager"
    decoding="async"
>
