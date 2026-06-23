<x-layouts.app
    :title="$article->seo_title . ' — Koding Indonesia'"
    :description="$article->seo_description"
    :ogImage="$article->cover_url"
    ogType="article"
    :canonical="route('articles.show', $article->slug)"
>

{{-- Reading Progress Bar --}}
<div id="reading-progress"
     style="position:fixed; top:0; left:0; height:3px; width:0%; background:#2979FF; z-index:9999; transition:width .1s linear; box-shadow: 0 0 6px rgba(41,121,255,0.6);"></div>

@push('schema')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "Article",
    "headline": "{{ $article->title }}",
    "description": "{{ $article->seo_description }}",
    "image": "{{ $article->cover_url }}",
    "datePublished": "{{ $article->published_at?->toIso8601String() }}",
    "dateModified": "{{ $article->updated_at->toIso8601String() }}",
    "author": {"@@type": "Person","name": "{{ $article->user->name ?? 'Koding Indonesia' }}"},
    "publisher": {
        "@@type": "Organization",
        "name": "Koding Indonesia",
        "url": "{{ url('/') }}",
        "logo": {
            "@@type": "ImageObject",
            "url": "{{ asset('logo.png') }}"
        }
    },
    "url": "{{ route('articles.show', $article->slug) }}"
}
</script>
@endpush

    @php
        $breadcrumbs = array_values(array_filter([
            ['label' => 'Artikel', 'url' => route('articles.index')],
            $article->category ? ['label' => $article->category->name, 'url' => route('categories.show', $article->category->slug)] : null,
            ['label' => $article->title],
        ]));
    @endphp
    <x-breadcrumb :items="$breadcrumbs" />

    <div class="max-w-6xl mx-auto px-4 py-6 sm:py-10">
        <div class="grid lg:grid-cols-[1fr_280px] gap-8 lg:gap-10 min-w-0">

            {{-- ── MAIN CONTENT ── --}}
            <div class="min-w-0">

                {{-- Cover Image --}}
                <div class="border-2 border-black mb-6 sm:mb-8 overflow-hidden aspect-video lg:aspect-[16/7]" style="box-shadow: 5px 5px 0 #000;">
                    @if($article->cover_image && file_exists(storage_path('app/public/' . $article->cover_image)))
                        <img src="{{ asset('storage/' . $article->cover_image) }}"
                             alt="{{ $article->title }}"
                             class="w-full h-full object-cover">
                    @else
                        {{-- Placeholder branded gradient --}}
                        <div class="w-full h-full flex flex-col items-center justify-center gap-3"
                             style="background: linear-gradient(135deg, #2979FF 0%, #1a56cc 50%, #2D3748 100%);">
                            <img src="{{ asset('logo.png') }}" alt="Koding Indonesia"
                                 class="h-16 w-16 sm:h-20 sm:w-20 object-contain border-2 border-white"
                                 style="box-shadow: 3px 3px 0 rgba(0,0,0,0.3);">
                            <p class="text-white text-center font-bold px-6 max-w-lg"
                               style="font-size:1.1rem; text-shadow: 1px 1px 0 rgba(0,0,0,0.4); line-height:1.4;">
                                {{ $article->title }}
                            </p>
                            @if($article->category)
                            <span class="text-xs font-bold uppercase tracking-wider px-3 py-1 border-2 border-white text-white opacity-80">
                                {{ $article->category->name }}
                            </span>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Category + Meta --}}
                <div class="flex flex-wrap items-center gap-3 mb-5">
                    @if($article->category)
                    <a href="{{ route('categories.show', $article->category->slug) }}"
                       class="text-xs font-bold uppercase tracking-wider px-3 py-1 border-2 border-black text-white"
                       style="background: {{ $article->category->color }}; box-shadow: 2px 2px 0 #000;">
                        {{ $article->category->name }}
                    </a>
                    @endif
                    <span class="text-sm font-mono" style="color:#718096;">{{ $article->published_at?->translatedFormat('d F Y') }}</span>
                    <span class="text-sm font-mono" style="color:#718096;">{{ $article->read_time_minutes }} menit baca</span>
                    <span class="flex items-center gap-1 text-sm font-mono" style="color:#718096;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        {{ number_format($article->views_count) }}
                    </span>
                </div>

                {{-- Title --}}
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black leading-tight mb-6" style="letter-spacing:-0.02em;">
                    {{ $article->title }}
                </h1>

                {{-- Author --}}
                @if($article->user)
                <div class="flex items-center gap-3 p-4 border-2 border-black mb-8" style="background:#F7F7F5;">
                    <div class="w-10 h-10 rounded-full border-2 border-black flex-shrink-0 flex items-center justify-center font-bold text-white text-sm" style="background:#2979FF;">
                        {{ strtoupper(substr($article->user->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="font-bold text-sm">{{ $article->user->name }}</div>
                        <div class="text-xs" style="color:#718096;">Penulis · Koding Indonesia</div>
                    </div>
                </div>
                @endif

                {{-- Article Body --}}
                <div class="article-body prose max-w-none min-w-0" id="article-content">
                    {!! $article->body !!}
                </div>

                {{-- Tags --}}
                @if($article->tags->count())
                <div class="mt-10 pt-6 border-t-2 border-black">
                    <span class="text-xs font-bold uppercase tracking-wider mr-3" style="color:#718096;">Tags:</span>
                    @foreach($article->tags as $tag)
                    <a href="{{ route('tags.show', $tag->slug) }}"
                       class="inline-block mr-2 mb-2 text-xs font-bold px-3 py-1.5 border-2 border-black hover:bg-black hover:text-white transition-colors"
                       style="box-shadow: 2px 2px 0 #000;">
                        #{{ $tag->name }}
                    </a>
                    @endforeach
                </div>
                @endif

                {{-- Share --}}
                <div class="mt-8 p-6 border-2 border-black" style="background: #EBF4FF; box-shadow: 4px 4px 0 #000;">
                    <p class="font-bold text-sm mb-3">Bagikan artikel ini:</p>
                    <div class="flex flex-wrap gap-2">
                        <a href="https://wa.me/?text={{ urlencode($article->title . ' — ' . route('articles.show', $article->slug)) }}" target="_blank"
                           class="btn-brutal px-4 py-2 text-xs text-white" style="background:#25D366; border-color:#000;">WhatsApp</a>
                        <a href="https://twitter.com/intent/tweet?text={{ urlencode($article->title) }}&url={{ urlencode(route('articles.show', $article->slug)) }}" target="_blank"
                           class="btn-brutal px-4 py-2 text-xs text-white" style="background:#1DA1F2; border-color:#000;">Twitter / X</a>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(route('articles.show', $article->slug)) }}" target="_blank"
                           class="btn-brutal px-4 py-2 text-xs text-white" style="background:#0077B5; border-color:#000;">LinkedIn</a>
                    </div>
                </div>

            </div>

            {{-- ── SIDEBAR ── --}}
            <aside class="hidden lg:block">
                <div class="sticky top-24 space-y-6">

                    {{-- Table of Contents --}}
                    <div class="theme-paper border-2 border-black" style="box-shadow: 4px 4px 0 #000;">
                        <div class="px-4 py-3 border-b-2 border-black" style="background:#2979FF;">
                            <h3 class="text-sm font-bold text-white uppercase tracking-wider">Daftar Isi</h3>
                        </div>
                        <nav id="toc" class="p-4 text-sm space-y-1.5 max-h-80 overflow-y-auto">
                            <p class="text-xs italic theme-muted">Memuat...</p>
                        </nav>
                    </div>

                    {{-- Related Articles --}}
                    @if($related->count())
                    <div class="theme-paper border-2 border-black" style="box-shadow: 4px 4px 0 #000;">
                        <div class="px-4 py-3 border-b-2 border-black" style="background:#FF7A2F;">
                            <h3 class="text-sm font-bold text-white uppercase tracking-wider">Artikel Terkait</h3>
                        </div>
                        <div class="p-4 space-y-4">
                            @foreach($related as $rel)
                            <a href="{{ route('articles.show', $rel->slug) }}" class="block group">
                                <div class="text-sm font-semibold theme-heading group-hover:text-[#2979FF] leading-snug mb-1">{{ $rel->title }}</div>
                                <div class="text-xs font-mono theme-muted">{{ $rel->published_at?->translatedFormat('d M Y') }}</div>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                </div>
            </aside>

        </div>
    </div>

@push('scripts')
<script>
// Build Table of Contents from headings
document.addEventListener('DOMContentLoaded', () => {
    const content = document.getElementById('article-content');
    const toc = document.getElementById('toc');
    if (!content || !toc) return;

    const headings = content.querySelectorAll('h2, h3');
    if (!headings.length) {
        toc.innerHTML = '<p class="text-xs italic" style="color:#718096;">Tidak ada daftar isi.</p>';
        return;
    }

    toc.innerHTML = '';
    headings.forEach((h, i) => {
        h.id = 'heading-' + i;
        const a = document.createElement('a');
        a.href = '#heading-' + i;
        a.textContent = h.textContent;
        a.style.cssText = 'display:block; padding:4px 0; color:#4A5568; text-decoration:none; border-left:2px solid transparent; padding-left:8px; transition: all .15s;';
        if (h.tagName === 'H3') a.style.paddingLeft = '20px';
        a.addEventListener('mouseenter', () => { a.style.color='#2979FF'; a.style.borderLeftColor='#2979FF'; });
        a.addEventListener('mouseleave', () => { a.style.color='#4A5568'; a.style.borderLeftColor='transparent'; });
        a.addEventListener('click', e => {
            e.preventDefault();
            document.getElementById('heading-' + i).scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
        toc.appendChild(a);
    });
});
</script>
@endpush

@push('scripts')
<script>
// Reading progress bar
(function() {
    const bar = document.getElementById('reading-progress');
    if (!bar) return;
    function updateProgress() {
        const docH   = document.documentElement.scrollHeight - window.innerHeight;
        const scroll = window.scrollY;
        const pct    = docH > 0 ? Math.min(100, (scroll / docH) * 100) : 0;
        bar.style.width = pct + '%';
    }
    window.addEventListener('scroll', updateProgress, { passive: true });
    updateProgress();
})();
</script>
@endpush

</x-layouts.app>
