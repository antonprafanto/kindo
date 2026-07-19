@props(['items' => []])
{{-- items: [['label' => 'Artikel', 'url' => '/artikel'], ['label' => 'Judul Artikel']] --}}

@php
    $crumbList = [
        ['label' => 'Beranda', 'url' => route('home')],
    ];
    foreach ($items as $i => $item) {
        $isLast = $i === count($items) - 1;
        $crumbList[] = [
            'label' => $item['label'],
            'url'   => (! $isLast && ! empty($item['url'])) ? $item['url'] : null,
        ];
    }
@endphp

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => collect($crumbList)->values()->map(function ($crumb, $index) {
        $entry = [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'name' => $crumb['label'],
        ];
        if (! empty($crumb['url'])) {
            $entry['item'] = $crumb['url'];
        }

        return $entry;
    })->all(),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}
</script>
@endpush

<nav aria-label="breadcrumb" class="py-3 border-b-2 border-black theme-paper">
    <div class="max-w-6xl mx-auto px-4">
        <ol class="flex flex-wrap items-center gap-1 text-sm">
            <li>
                <a href="{{ route('home') }}" class="font-medium theme-body hover:text-[#2979FF] transition-colors">Beranda</a>
            </li>
            @foreach($items as $i => $item)
                <li class="theme-muted">›</li>
                <li>
                    @if(!empty($item['url']) && $i < count($items) - 1)
                        <a href="{{ $item['url'] }}" class="font-medium theme-body hover:text-[#2979FF] transition-colors">{{ $item['label'] }}</a>
                    @else
                        <span class="font-semibold theme-heading">{{ Str::limit($item['label'], 50) }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </div>
</nav>
