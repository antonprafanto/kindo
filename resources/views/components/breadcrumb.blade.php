@props(['items' => []])
{{-- items: [['label' => 'Beranda', 'url' => '/'], ['label' => 'Artikel', 'url' => '/artikel'], ['label' => 'Judul Artikel']] --}}

<nav aria-label="breadcrumb" class="py-3 border-b-2 border-black" style="background: #fff;">
    <div class="max-w-6xl mx-auto px-4">
        <ol class="flex flex-wrap items-center gap-1 text-sm">
            <li>
                <a href="{{ route('home') }}" class="font-medium hover:text-[#2979FF] transition-colors" style="color: #4A5568;">Beranda</a>
            </li>
            @foreach($items as $i => $item)
                <li style="color: #CBD5E0;">›</li>
                <li>
                    @if(!empty($item['url']) && $i < count($items) - 1)
                        <a href="{{ $item['url'] }}" class="font-medium hover:text-[#2979FF] transition-colors" style="color: #4A5568;">{{ $item['label'] }}</a>
                    @else
                        <span class="font-semibold text-black">{{ Str::limit($item['label'], 50) }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </div>
</nav>
