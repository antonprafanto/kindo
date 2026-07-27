@props([
    'size' => 'md', // sm = mobile header denser pad; md = desktop
])

@php
    $locale = app()->getLocale();
    // Both sizes keep min-h-9 for touch; sm trims horizontal pad slightly for narrow headers.
    $pad = $size === 'sm'
        ? 'min-h-9 min-w-[2.25rem] px-2 inline-flex items-center justify-center'
        : 'min-h-9 min-w-[2.25rem] px-2.5 inline-flex items-center justify-center';
@endphp

<div
    {{ $attributes->class([
        'inline-flex items-stretch border-2 border-black text-xs font-bold shrink-0',
    ]) }}
    style="box-shadow: 2px 2px 0 #000;"
    role="group"
    aria-label="{{ __('ui.nav.language') }}"
>
    @if ($locale === 'id')
        <span
            class="{{ $pad }} bg-black text-white select-none"
            aria-current="true"
            lang="id"
            title="{{ __('ui.nav.language') }}: ID"
        >ID</span>
    @else
        <a
            href="{{ route('locale.switch', 'id') }}"
            class="{{ $pad }} theme-paper theme-heading hover:bg-black hover:text-white transition-colors"
            hreflang="id"
            lang="id"
            title="{{ __('ui.nav.language') }}: ID"
        >ID</a>
    @endif

    @if ($locale === 'en')
        <span
            class="{{ $pad }} border-l-2 border-black bg-black text-white select-none"
            aria-current="true"
            lang="en"
            title="{{ __('ui.nav.language') }}: EN"
        >EN</span>
    @else
        <a
            href="{{ route('locale.switch', 'en') }}"
            class="{{ $pad }} border-l-2 border-black theme-paper theme-heading hover:bg-black hover:text-white transition-colors"
            hreflang="en"
            lang="en"
            title="{{ __('ui.nav.language') }}: EN"
        >EN</a>
    @endif
</div>
