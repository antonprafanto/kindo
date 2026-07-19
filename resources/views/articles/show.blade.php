<x-layouts.app
    :title="($isPreview ?? false ? '[Pratinjau] ' : '') . $article->seo_title . ' — Koding Indonesia'"
    :description="$article->seo_description"
    :ogImage="$article->cover_url"
    :ogImageAlt="$article->cover_image
        ? $article->seo_title . ' — Tutorial ESP32 & IoT di Koding Indonesia'
        : null"
    ogType="article"
    :ogPublished="$article->published_at?->toIso8601String()"
    :ogModified="$article->updated_at?->toIso8601String()"
    :ogAuthor="$article->user?->name"
    :ogSection="$article->category?->name"
    :canonical="($isPreview ?? false) ? null : route('articles.show', $article->slug)"
    :noindex="$isPreview ?? false"
>

{{-- Reading Progress Bar --}}
<div id="reading-progress"
     aria-hidden="true"
     style="position:fixed; top:0; left:0; height:3px; width:0%; background:#2979FF; z-index:9999; transition:width .1s linear; box-shadow: 0 0 6px rgba(41,121,255,0.6);"></div>

@if($isPreview ?? false)
<div class="sticky top-14 sm:top-16 z-40 border-b-2 border-black px-4 py-3 text-center text-white" style="background:#FF7A2F;">
    <p class="font-black text-sm sm:text-base uppercase tracking-wide">Pratinjau — Belum Dipublikasikan</p>
    <p class="text-xs sm:text-sm mt-1 opacity-95">
        Status: <strong>{{ $article->previewStatusLabel() }}</strong>
        · Tampilan ini sama dengan artikel live, tetapi belum terlihat publik
        · <strong>Simpan</strong> perubahan di panel sebelum pratinjau agar versi terbaru tampil
    </p>
    @if(!empty($previewBackUrl))
    <a href="{{ $previewBackUrl }}"
       class="inline-block mt-2 text-xs font-bold px-3 py-1.5 border-2 border-black bg-white text-black hover:bg-black hover:text-white transition-colors"
       style="box-shadow: 2px 2px 0 #000;">
        ← Kembali ke Panel Admin
    </a>
    @endif
</div>
@endif

@unless($isPreview ?? false)
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
    "author": {
        "@@type": "Person",
        "name": "{{ $article->user->name ?? 'Koding Indonesia' }}"@if($article->user?->hasPublicProfile()),
        "url": "{{ route('authors.show', $article->user->slug) }}"@endif
    },
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
@endunless

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
                    @php
                        $coverOnDisk = $article->cover_image
                            && (
                                file_exists(storage_path('app/public/' . $article->cover_image))
                                || (
                                    is_string(config('filesystems.public_html_storage'))
                                    && file_exists(rtrim(config('filesystems.public_html_storage'), '/\\') . '/' . $article->cover_image)
                                )
                            );
                    @endphp
                    @if($coverOnDisk)
                        <img src="{{ $article->cover_url }}"
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
                       class="text-xs font-bold uppercase tracking-wider px-3 py-1 border-2 border-black"
                       style="background: {{ $article->category->color }}; color: {{ \App\Support\Contrast::textOn($article->category->color) }}; box-shadow: 2px 2px 0 #000;">
                        {{ $article->category->name }}
                    </a>
                    @endif
                    <span class="text-sm font-mono theme-muted">
                        @if($isPreview ?? false)
                            Belum dipublikasikan
                        @else
                            {{ $article->published_at?->translatedFormat('d F Y') }}
                            @if($article->updated_at && $article->published_at && $article->updated_at->gt($article->published_at->copy()->addMinute()))
                                · Terakhir diperbarui {{ $article->updated_at->translatedFormat('d F Y') }}
                            @endif
                        @endif
                    </span>
                    <span class="text-sm font-mono theme-muted">{{ $article->read_time_minutes }} menit baca</span>
                    @unless($isPreview ?? false)
                    <span class="flex items-center gap-1 text-sm font-mono theme-muted">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        {{ number_format($article->views_count) }}
                    </span>
                    @endunless
                </div>

                {{-- Title --}}
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black leading-tight mb-6 theme-heading" style="letter-spacing:-0.02em;">
                    {{ $article->title }}
                </h1>

                {{-- Author --}}
                @if($article->user)
                @php
                    $author = $article->user;
                    $authorUrl = $author->hasPublicProfile() ? route('authors.show', $author->slug) : null;
                @endphp
                <div class="flex items-center gap-3 p-4 border-2 border-black mb-8 theme-surface">
                    @if($authorUrl)
                    <a href="{{ $authorUrl }}" class="flex items-center gap-3 no-underline min-w-0 flex-1 group">
                    @else
                    <div class="flex items-center gap-3 min-w-0 flex-1">
                    @endif
                        @if($author->avatar_url)
                            <img src="{{ $author->avatar_url }}" alt="{{ $author->name }}"
                                 class="w-10 h-10 rounded-full border-2 border-black object-cover flex-shrink-0">
                        @else
                            <div class="w-10 h-10 rounded-full border-2 border-black flex-shrink-0 flex items-center justify-center font-bold text-white text-sm" style="background:#2979FF;">
                                {{ $author->initial }}
                            </div>
                        @endif
                        <div class="min-w-0">
                            <div class="font-bold text-sm theme-heading {{ $authorUrl ? 'group-hover:text-[#2979FF] transition-colors' : '' }}">{{ $author->name }}</div>
                            <div class="text-xs theme-muted">{{ $author->expertise ?: 'Penulis · Koding Indonesia' }}</div>
                        </div>
                    @if($authorUrl)
                    </a>
                    @else
                    </div>
                    @endif
                </div>
                @endif

                {{-- Mobile TOC (desktop uses sidebar) --}}
                <details id="toc-mobile-wrap" class="lg:hidden mb-8 border-2 border-black theme-paper" style="box-shadow: 4px 4px 0 #000;">
                    <summary class="px-4 py-3 border-b-2 border-black cursor-pointer list-none flex items-center justify-between font-bold text-sm uppercase tracking-wider text-white" style="background:#2979FF;">
                        <span>Daftar Isi</span>
                        <span class="text-xs font-mono normal-case opacity-90" aria-hidden="true">▼</span>
                    </summary>
                    <nav id="toc-mobile" class="p-4 text-sm space-y-1.5 max-h-64 overflow-y-auto">
                        <p class="text-xs italic theme-muted">Memuat...</p>
                    </nav>
                </details>

                {{-- Article Body --}}
                <div class="article-body prose max-w-none min-w-0" id="article-content">
                    {!! $article->body !!}
                </div>

                {{-- Tags --}}
                @if($article->tags->count())
                <div class="mt-10 pt-6 border-t-2 border-black">
                    <span class="text-xs font-bold uppercase tracking-wider mr-3 theme-muted">Tag:</span>
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
                @unless($isPreview ?? false)
                <div id="article-share" class="mt-8 p-6 border-2 border-black theme-highlight" style="box-shadow: 4px 4px 0 #000;">
                    <p class="font-bold text-sm mb-3">Bagikan artikel ini:</p>
                    <div class="flex flex-wrap gap-2">
                        <a href="https://wa.me/?text={{ urlencode($article->title . ' — ' . route('articles.show', $article->slug)) }}" target="_blank" rel="noopener noreferrer"
                           class="btn-brutal px-4 py-2 text-xs text-white" style="background:#25D366; border-color:#000;">WhatsApp</a>
                        <a href="https://twitter.com/intent/tweet?text={{ urlencode($article->title) }}&url={{ urlencode(route('articles.show', $article->slug)) }}" target="_blank" rel="noopener noreferrer"
                           class="btn-brutal px-4 py-2 text-xs text-white" style="background:#1DA1F2; border-color:#000;">X</a>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(route('articles.show', $article->slug)) }}" target="_blank" rel="noopener noreferrer"
                           class="btn-brutal px-4 py-2 text-xs text-white" style="background:#0077B5; border-color:#000;">LinkedIn</a>
                        <button type="button"
                                id="copy-article-link"
                                data-url="{{ route('articles.show', $article->slug) }}"
                                class="btn-brutal px-4 py-2 text-xs theme-heading"
                                style="background:#FFD600; border-color:#000;">
                            Salin tautan
                        </button>
                    </div>
                    <p id="copy-article-link-feedback" class="mt-2 text-xs font-bold theme-muted hidden" aria-live="polite">Tautan disalin!</p>
                </div>

                <div class="print:hidden">
                <livewire:article-comments :article="$article" />
                </div>
                @else
                <div class="mt-8 p-4 border-2 border-dashed border-black theme-muted text-sm text-center">
                    Bagikan dan komentar akan tersedia setelah artikel dipublikasikan.
                </div>
                @endunless

                {{-- Series nav: same-category prev/next --}}
                @unless($isPreview ?? false)
                @if(($previousArticle ?? null) || ($nextArticle ?? null))
                <div class="mt-10 theme-paper border-2 border-black" style="box-shadow: 4px 4px 0 #000;">
                    <div class="px-4 py-3 border-b-2 border-black" style="background:#2D3748;">
                        <h2 class="text-sm font-bold text-white uppercase tracking-wider">
                            @if($article->category)
                                Di {{ $article->category->name }}
                            @else
                                Artikel terkait kategori
                            @endif
                        </h2>
                    </div>
                    <div class="grid sm:grid-cols-2 divide-y sm:divide-y-0 sm:divide-x divide-black/10 dark:divide-white/10">
                        <div class="p-4">
                            @if($previousArticle ?? null)
                            <p class="text-xs font-bold uppercase tracking-wider theme-muted mb-1">← Sebelumnya</p>
                            <a href="{{ route('articles.show', $previousArticle->slug) }}" class="block text-sm font-semibold theme-heading hover:text-[#2979FF] leading-snug">
                                {{ $previousArticle->title }}
                            </a>
                            @else
                            <p class="text-xs theme-muted italic">Tidak ada artikel sebelumnya</p>
                            @endif
                        </div>
                        <div class="p-4 sm:text-right">
                            @if($nextArticle ?? null)
                            <p class="text-xs font-bold uppercase tracking-wider theme-muted mb-1">Berikutnya →</p>
                            <a href="{{ route('articles.show', $nextArticle->slug) }}" class="block text-sm font-semibold theme-heading hover:text-[#2979FF] leading-snug">
                                {{ $nextArticle->title }}
                            </a>
                            @else
                            <p class="text-xs theme-muted italic">Tidak ada artikel berikutnya</p>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
                @endunless

                {{-- Related (mobile / tablet — sidebar shows on lg+) --}}
                @if($related->count())
                <div class="lg:hidden mt-10 theme-paper border-2 border-black" style="box-shadow: 4px 4px 0 #000;">
                    <div class="px-4 py-3 border-b-2 border-black" style="background:#FF7A2F;">
                        <h2 class="text-sm font-bold text-white uppercase tracking-wider">Artikel Terkait</h2>
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

                    {{-- Series nav (desktop sidebar) --}}
                    @unless($isPreview ?? false)
                    @if(($previousArticle ?? null) || ($nextArticle ?? null))
                    <div class="theme-paper border-2 border-black" style="box-shadow: 4px 4px 0 #000;">
                        <div class="px-4 py-3 border-b-2 border-black" style="background:#2D3748;">
                            <h3 class="text-sm font-bold text-white uppercase tracking-wider">Sebelumnya / Berikutnya</h3>
                        </div>
                        <div class="p-4 space-y-4">
                            @if($previousArticle ?? null)
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider theme-muted mb-1">← Sebelumnya</p>
                                <a href="{{ route('articles.show', $previousArticle->slug) }}" class="block text-sm font-semibold theme-heading hover:text-[#2979FF] leading-snug">
                                    {{ $previousArticle->title }}
                                </a>
                            </div>
                            @endif
                            @if($nextArticle ?? null)
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider theme-muted mb-1">Berikutnya →</p>
                                <a href="{{ route('articles.show', $nextArticle->slug) }}" class="block text-sm font-semibold theme-heading hover:text-[#2979FF] leading-snug">
                                    {{ $nextArticle->title }}
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                    @endunless

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

@push('highlight')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/cpp.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/arduino.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/python.min.js"></script>
<script>
    hljs.highlightAll();

    document.querySelectorAll('.article-body pre, #article-content pre').forEach(pre => {
        const wrap = document.createElement('div');
        wrap.className = 'code-block-wrap';
        pre.parentNode.insertBefore(wrap, pre);
        wrap.appendChild(pre);

        const copyBtn = document.createElement('button');
        copyBtn.textContent = 'Salin';
        copyBtn.className = 'copy-code-btn';
        copyBtn.type = 'button';
        copyBtn.setAttribute('aria-label', 'Salin kode');
        copyBtn.addEventListener('click', () => {
            navigator.clipboard.writeText(pre.querySelector('code')?.textContent || pre.textContent);
            copyBtn.textContent = 'Tersalin!';
            setTimeout(() => copyBtn.textContent = 'Salin', 2000);
        });
        wrap.appendChild(copyBtn);
    });
</script>
@endpush

@push('scripts')
<script>
// Build Table of Contents from headings
document.addEventListener('DOMContentLoaded', () => {
    const content = document.getElementById('article-content');
    const tocTargets = [document.getElementById('toc'), document.getElementById('toc-mobile')].filter(Boolean);
    if (!content || !tocTargets.length) return;

    const headings = content.querySelectorAll('h2, h3');
    if (!headings.length) {
        tocTargets.forEach(toc => {
            toc.innerHTML = '<p class="text-xs italic theme-muted">Tidak ada daftar isi.</p>';
        });
        const mobileWrap = document.getElementById('toc-mobile-wrap');
        if (mobileWrap) mobileWrap.hidden = true;
        return;
    }

    tocTargets.forEach(toc => { toc.innerHTML = ''; });

    headings.forEach((h, i) => {
        h.id = 'heading-' + i;
        tocTargets.forEach(toc => {
            const a = document.createElement('a');
            a.href = '#heading-' + i;
            a.textContent = h.textContent;
            a.className = 'toc-link' + (h.tagName === 'H3' ? ' toc-link--h3' : '');
            a.dataset.headingId = 'heading-' + i;
            a.addEventListener('click', e => {
                e.preventDefault();
                document.getElementById('heading-' + i).scrollIntoView({ behavior: 'smooth', block: 'start' });
                const mobileWrap = document.getElementById('toc-mobile-wrap');
                if (mobileWrap && mobileWrap.open) mobileWrap.open = false;
            });
            toc.appendChild(a);
        });
    });

    // Scroll-spy: highlight TOC link for the heading in view
    const setActiveToc = (id) => {
        document.querySelectorAll('.toc-link').forEach(link => {
            link.classList.toggle('toc-link--active', link.dataset.headingId === id);
        });
    };

    const spyObserver = new IntersectionObserver((entries) => {
        const visible = entries
            .filter(e => e.isIntersecting)
            .sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top);
        if (visible.length) {
            setActiveToc(visible[0].target.id);
        }
    }, {
        rootMargin: '-20% 0px -60% 0px',
        threshold: 0,
    });

    headings.forEach(h => spyObserver.observe(h));
    if (headings[0]) setActiveToc(headings[0].id);
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

// Salin tautan
(function() {
    const btn = document.getElementById('copy-article-link');
    const feedback = document.getElementById('copy-article-link-feedback');
    if (!btn) return;

    btn.addEventListener('click', async () => {
        const url = btn.dataset.url || window.location.href;
        try {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(url);
            } else {
                const ta = document.createElement('textarea');
                ta.value = url;
                ta.setAttribute('readonly', '');
                ta.style.position = 'absolute';
                ta.style.left = '-9999px';
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
            }
            if (feedback) {
                feedback.classList.remove('hidden');
                btn.textContent = 'Disalin!';
                setTimeout(() => {
                    feedback.classList.add('hidden');
                    btn.textContent = 'Salin tautan';
                }, 2000);
            }
        } catch (err) {
            if (feedback) {
                feedback.textContent = 'Gagal menyalin — salin manual dari bilah alamat.';
                feedback.classList.remove('hidden');
            }
        }
    });
})();
</script>
@endpush

</x-layouts.app>
