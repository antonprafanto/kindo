<x-layouts.app
    :title="($isPreview ?? false ? '[Pratinjau] ' : '') . $article->display_seo_title . ' — Koding Indonesia'"
    :description="$article->display_seo_description"
    :ogImage="$article->cover_url"
    :ogImageAlt="$article->cover_image
        ? $article->display_seo_title . ' — Tutorial ESP32 & IoT di Koding Indonesia'
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

<x-locale-article-banner :article="$article" />

@unless($isPreview ?? false)
@push('schema')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "Article",
    "headline": "{{ $article->display_title }}",
    "description": "{{ $article->display_seo_description }}",
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
            ['label' => __('ui.articles.breadcrumb'), 'url' => route('articles.index')],
            $article->category ? ['label' => $article->category->name, 'url' => route('categories.show', $article->category->slug)] : null,
            ['label' => $article->display_title],
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
                             alt="{{ $article->display_title }}"
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
                                {{ $article->display_title }}
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
                            {{ __('ui.articles.unpublished') }}
                        @else
                            {{ $article->published_at?->translatedFormat('d F Y') }}
                            @if($article->updated_at && $article->published_at && $article->updated_at->gt($article->published_at->copy()->addMinute()))
                                · {{ __('ui.articles.updated', ['date' => $article->updated_at->translatedFormat('d F Y')]) }}
                            @endif
                        @endif
                    </span>
                    <span class="text-sm font-mono theme-muted">{{ __('ui.articles.minutes_read', ['count' => $article->display_read_time_minutes]) }}</span>
                    @unless($isPreview ?? false)
                    <span class="flex items-center gap-1 text-sm font-mono theme-muted">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        {{ number_format($article->views_count) }}
                    </span>
                    @endunless
                </div>

                {{-- Title --}}
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black leading-tight mb-6 theme-heading" style="letter-spacing:-0.02em;">
                    {{ $article->display_title }}
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
                            <div class="text-xs theme-muted">{{ $author->expertise ?: __('ui.articles.author_fallback') }}</div>
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
                        <span>{{ __('ui.articles.toc') }}</span>
                        <span class="text-xs font-mono normal-case opacity-90" aria-hidden="true">▼</span>
                    </summary>
                    <nav id="toc-mobile" class="p-4 text-sm space-y-1.5 max-h-64 overflow-y-auto">
                        <p class="text-xs italic theme-muted">{{ __('ui.articles.toc_loading') }}</p>
                    </nav>
                </details>

                {{-- Article Body --}}
                <div class="article-body prose max-w-none min-w-0" id="article-content">
                    {!! $article->display_body !!}
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
                    <p class="font-bold text-sm mb-3">{{ __('ui.articles.share') }}</p>
                    <div class="flex flex-wrap gap-2">
                        <a href="https://wa.me/?text={{ urlencode($article->display_title . ' — ' . route('articles.show', $article->slug)) }}" target="_blank" rel="noopener noreferrer"
                           class="btn-brutal px-4 py-2 text-xs text-white" style="background:#25D366; border-color:#000;">WhatsApp</a>
                        <a href="https://twitter.com/intent/tweet?text={{ urlencode($article->display_title) }}&url={{ urlencode(route('articles.show', $article->slug)) }}" target="_blank" rel="noopener noreferrer"
                           class="btn-brutal px-4 py-2 text-xs text-white" style="background:#1DA1F2; border-color:#000;">X</a>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(route('articles.show', $article->slug)) }}" target="_blank" rel="noopener noreferrer"
                           class="btn-brutal px-4 py-2 text-xs text-white" style="background:#0077B5; border-color:#000;">LinkedIn</a>
                        <button type="button"
                                id="copy-article-link"
                                data-url="{{ route('articles.show', $article->slug) }}"
                                data-label-copy="{{ __('ui.articles.copy_link') }}"
                                data-label-copied="{{ __('ui.articles.copied') }}"
                                data-label-failed="{{ __('ui.articles.copy_failed') }}"
                                data-label-feedback="{{ __('ui.articles.link_copied') }}"
                                class="btn-brutal px-4 py-2 text-xs theme-heading"
                                style="background:#FFD600; border-color:#000;">
                            {{ __('ui.articles.copy_link') }}
                        </button>
                    </div>
                    <p id="copy-article-link-feedback" class="mt-2 text-xs font-bold theme-muted hidden" aria-live="polite">{{ __('ui.articles.link_copied') }}</p>
                </div>

                <div class="print:hidden">
                <livewire:article-comments :article="$article" />
                </div>
                @else
                <div class="mt-8 p-4 border-2 border-dashed border-black theme-muted text-sm text-center">
                    {{ __('ui.articles.preview_share_disabled') }}
                </div>
                @endunless

                {{-- Series nav: same-category prev/next --}}
                @unless($isPreview ?? false)
                @if(($previousArticle ?? null) || ($nextArticle ?? null))
                <div class="mt-10 theme-paper border-2 border-black" style="box-shadow: 4px 4px 0 #000;">
                    <div class="px-4 py-3 border-b-2 border-black" style="background:#2D3748;">
                        <h2 class="text-sm font-bold text-white uppercase tracking-wider">
                            @if($article->category)
                                {{ __('ui.articles.in_category', ['name' => $article->category->name]) }}
                            @else
                                {{ __('ui.articles.related_category') }}
                            @endif
                        </h2>
                    </div>
                    <div class="grid sm:grid-cols-2 divide-y sm:divide-y-0 sm:divide-x divide-black/10 dark:divide-white/10">
                        <div class="p-4">
                            @if($previousArticle ?? null)
                            <p class="text-xs font-bold uppercase tracking-wider theme-muted mb-1">{{ __('ui.articles.prev') }}</p>
                            <a href="{{ route('articles.show', $previousArticle->slug) }}" class="block text-sm font-semibold theme-heading hover:text-[#2979FF] leading-snug">
                                {{ $previousArticle->display_title }}
                            </a>
                            @else
                            <p class="text-xs theme-muted italic">{{ __('ui.articles.prev_none') }}</p>
                            @endif
                        </div>
                        <div class="p-4 sm:text-right">
                            @if($nextArticle ?? null)
                            <p class="text-xs font-bold uppercase tracking-wider theme-muted mb-1">{{ __('ui.articles.next') }}</p>
                            <a href="{{ route('articles.show', $nextArticle->slug) }}" class="block text-sm font-semibold theme-heading hover:text-[#2979FF] leading-snug">
                                {{ $nextArticle->display_title }}
                            </a>
                            @else
                            <p class="text-xs theme-muted italic">{{ __('ui.articles.next_none') }}</p>
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
                        <h2 class="text-sm font-bold text-white uppercase tracking-wider">{{ __('ui.articles.related') }}</h2>
                    </div>
                    <div class="p-4 space-y-4">
                        @foreach($related as $rel)
                        <a href="{{ route('articles.show', $rel->slug) }}" class="block group">
                            <div class="text-sm font-semibold theme-heading group-hover:text-[#2979FF] leading-snug mb-1">{{ $rel->display_title }}</div>
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
                            <h3 class="text-sm font-bold text-white uppercase tracking-wider">{{ __('ui.articles.toc') }}</h3>
                        </div>
                        <nav id="toc" class="p-4 text-sm space-y-1.5 max-h-80 overflow-y-auto">
                            <p class="text-xs italic theme-muted">{{ __('ui.articles.toc_loading') }}</p>
                        </nav>
                    </div>

                    {{-- Series nav (desktop sidebar) --}}
                    @unless($isPreview ?? false)
                    @if(($previousArticle ?? null) || ($nextArticle ?? null))
                    <div class="theme-paper border-2 border-black" style="box-shadow: 4px 4px 0 #000;">
                        <div class="px-4 py-3 border-b-2 border-black" style="background:#2D3748;">
                            <h3 class="text-sm font-bold text-white uppercase tracking-wider">{{ __('ui.articles.prev_next') }}</h3>
                        </div>
                        <div class="p-4 space-y-4">
                            @if($previousArticle ?? null)
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider theme-muted mb-1">{{ __('ui.articles.prev') }}</p>
                                <a href="{{ route('articles.show', $previousArticle->slug) }}" class="block text-sm font-semibold theme-heading hover:text-[#2979FF] leading-snug">
                                    {{ $previousArticle->display_title }}
                                </a>
                            </div>
                            @endif
                            @if($nextArticle ?? null)
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider theme-muted mb-1">{{ __('ui.articles.next') }}</p>
                                <a href="{{ route('articles.show', $nextArticle->slug) }}" class="block text-sm font-semibold theme-heading hover:text-[#2979FF] leading-snug">
                                    {{ $nextArticle->display_title }}
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
                            <h3 class="text-sm font-bold text-white uppercase tracking-wider">{{ __('ui.articles.related') }}</h3>
                        </div>
                        <div class="p-4 space-y-4">
                            @foreach($related as $rel)
                            <a href="{{ route('articles.show', $rel->slug) }}" class="block group">
                                <div class="text-sm font-semibold theme-heading group-hover:text-[#2979FF] leading-snug mb-1">{{ $rel->display_title }}</div>
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

    const codeCopyLabel = @js(__('ui.articles.copy_code'));
    const codeCopiedLabel = @js(__('ui.articles.code_copied'));
    const codeCopyAria = @js(__('ui.articles.copy_code_aria'));

    document.querySelectorAll('.article-body pre, #article-content pre').forEach(pre => {
        const wrap = document.createElement('div');
        wrap.className = 'code-block-wrap';
        pre.parentNode.insertBefore(wrap, pre);
        wrap.appendChild(pre);

        const copyBtn = document.createElement('button');
        copyBtn.textContent = codeCopyLabel;
        copyBtn.className = 'copy-code-btn';
        copyBtn.type = 'button';
        copyBtn.setAttribute('aria-label', codeCopyAria);
        copyBtn.addEventListener('click', () => {
            navigator.clipboard.writeText(pre.querySelector('code')?.textContent || pre.textContent);
            copyBtn.textContent = codeCopiedLabel;
            setTimeout(() => copyBtn.textContent = codeCopyLabel, 2000);
        });
        wrap.appendChild(copyBtn);
    });
</script>
@endpush

@push('scripts')
<script>
// Build Table of Contents from headings
document.addEventListener('DOMContentLoaded', () => {
    const tocEmptyLabel = @js(__('ui.articles.toc_empty'));
    const content = document.getElementById('article-content');
    const tocTargets = [document.getElementById('toc'), document.getElementById('toc-mobile')].filter(Boolean);
    if (!content || !tocTargets.length) return;

    const headings = content.querySelectorAll('h2, h3');
    if (!headings.length) {
        tocTargets.forEach(toc => {
            toc.innerHTML = '<p class="text-xs italic theme-muted">' + tocEmptyLabel + '</p>';
        });
        const mobileWrap = document.getElementById('toc-mobile-wrap');
        if (mobileWrap) mobileWrap.hidden = true;
        return;
    }

    tocTargets.forEach(toc => { toc.innerHTML = ''; });

    headings.forEach((h, i) => {
        if (!h.id) {
            h.id = 'heading-' + i;
        }
        const hid = h.id;
        tocTargets.forEach(toc => {
            const a = document.createElement('a');
            a.href = '#' + hid;
            a.textContent = h.textContent;
            a.className = 'toc-link' + (h.tagName === 'H3' ? ' toc-link--h3' : '');
            a.dataset.headingId = hid;
            a.addEventListener('click', e => {
                e.preventDefault();
                document.getElementById(hid)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
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
                feedback.textContent = btn.dataset.labelFeedback || feedback.textContent;
                feedback.classList.remove('hidden');
                btn.textContent = btn.dataset.labelCopied || 'Copied!';
                setTimeout(() => {
                    feedback.classList.add('hidden');
                    btn.textContent = btn.dataset.labelCopy || 'Copy link';
                }, 2000);
            }
        } catch (err) {
            if (feedback) {
                feedback.textContent = btn.dataset.labelFailed || feedback.textContent;
                feedback.classList.remove('hidden');
            }
        }
    });
})();
</script>
@endpush

@push('scripts')
<script>
// FSIOT interactive widgets (injected after sanitizer — safe from article body HTML)
document.addEventListener('DOMContentLoaded', () => {
    initFsiotMatchQuiz();
    initFsiotWorksheet();
    initFsiotKitChecklist();
    initFsiotSafetyChecklist();
    initFsiotSetupChecklist();
    initFsiotMultimeterChecklist();
    initFsiotResistorCalc();
    initFsiotElectricChecklist();
    initFsiotLedCircuitChecklist();
    initFsiotSignalChecklist();
    initFsiotSketchChecklist();
    initFsiotVarChecklist();
    initFsiotSerialChecklist();
    initFsiotIfChecklist();
    initFsiotForWhileChecklist();
    initFsiotFnChecklist();
});

function initFsiotMatchQuiz() {
    const labels = {
        badge: @js(__('ui.articles.fsiot_quiz_badge')),
        hint: @js(__('ui.articles.fsiot_quiz_hint')),
        placeholder: @js(__('ui.articles.fsiot_quiz_placeholder')),
        check: @js(__('ui.articles.fsiot_quiz_check')),
        retry: @js(__('ui.articles.fsiot_quiz_retry')),
        showKey: @js(__('ui.articles.fsiot_quiz_show_key')),
        hideKey: @js(__('ui.articles.fsiot_quiz_hide_key')),
        paper: @js(__('ui.articles.fsiot_quiz_paper')),
        progress: @js(__('ui.articles.fsiot_quiz_progress')),
        pass: @js(__('ui.articles.fsiot_quiz_pass')),
        fail: @js(__('ui.articles.fsiot_quiz_fail')),
        incomplete: @js(__('ui.articles.fsiot_quiz_incomplete')),
        correct: @js(__('ui.articles.fsiot_quiz_correct')),
        wrong: @js(__('ui.articles.fsiot_quiz_wrong')),
    };

    const content = document.getElementById('article-content');
    if (!content) return;

    const quizH2 = content.querySelector('#fsiot-kuis-matching');
    const keyH2 = content.querySelector('#fsiot-kuis-kunci');
    if (!quizH2 || !keyH2) return;

    const sectionNodes = [];
    for (let n = quizH2.nextElementSibling; n && n !== keyH2; n = n.nextElementSibling) {
        sectionNodes.push(n);
    }

    const termsOl = sectionNodes.find(n => n.tagName === 'OL');
    const meaningsUl = sectionNodes.find(n => n.tagName === 'UL');
    if (!termsOl || !meaningsUl) return;

    const terms = Array.from(termsOl.querySelectorAll('li')).map(li => li.textContent.trim()).filter(Boolean);
    const meanings = Array.from(meaningsUl.querySelectorAll('li')).map(li => {
        const raw = li.textContent.trim();
        const m = raw.match(/^([A-O])\.\s*(.+)$/i);
        return m ? { letter: m[1].toUpperCase(), text: m[2].trim() } : null;
    }).filter(Boolean);

    const keyPara = keyH2.nextElementSibling;
    const keyText = keyPara ? keyPara.textContent : '';
    const answers = {};
    for (const match of keyText.matchAll(/(\d+)\s*([A-O])/gi)) {
        answers[parseInt(match[1], 10)] = match[2].toUpperCase();
    }

    if (terms.length !== 15 || meanings.length !== 15 || Object.keys(answers).length !== 15) {
        return;
    }

    const intro = sectionNodes[0] && sectionNodes[0].tagName === 'P' ? sectionNodes[0] : null;

    const paper = document.createElement('details');
    paper.className = 'fsiot-match-paper';
    const paperSummary = document.createElement('summary');
    paperSummary.textContent = labels.paper;
    paper.appendChild(paperSummary);

    const afterIntro = [];
    for (let n = quizH2.nextElementSibling; n && n !== keyH2; ) {
        const next = n.nextElementSibling;
        if (n !== intro) {
            afterIntro.push(n);
        }
        n = next;
    }
    afterIntro.forEach(n => paper.appendChild(n));
    if (intro) {
        intro.after(paper);
    } else {
        quizH2.after(paper);
    }

    const keyWrap = document.createElement('div');
    keyWrap.className = 'fsiot-match-key-wrap is-hidden';
    keyWrap.id = 'fsiot-match-key-panel';
    const toWrap = [keyH2];
    for (let n = keyH2.nextElementSibling; n && n.tagName !== 'H2'; n = n.nextElementSibling) {
        toWrap.push(n);
    }
    keyH2.before(keyWrap);
    toWrap.forEach(n => keyWrap.appendChild(n));

    const widget = document.createElement('div');
    widget.className = 'fsiot-match-quiz';
    widget.setAttribute('role', 'region');
    widget.setAttribute('aria-label', labels.badge);

    const head = document.createElement('div');
    head.className = 'fsiot-match-quiz__head';
    head.innerHTML = '<span class="fsiot-match-quiz__badge"></span>'
        + '<p class="fsiot-match-quiz__hint"></p>'
        + '<span class="fsiot-match-quiz__progress" aria-live="polite"></span>';
    head.querySelector('.fsiot-match-quiz__badge').textContent = labels.badge;
    head.querySelector('.fsiot-match-quiz__hint').textContent = labels.hint;
    const progressEl = head.querySelector('.fsiot-match-quiz__progress');
    widget.appendChild(head);

    const list = document.createElement('ul');
    list.className = 'fsiot-match-quiz__list';

    const selects = [];
    terms.forEach((term, idx) => {
        const num = idx + 1;
        const li = document.createElement('li');
        li.className = 'fsiot-match-quiz__row';

        const termEl = document.createElement('p');
        termEl.className = 'fsiot-match-quiz__term';
        termEl.innerHTML = '<span class="fsiot-match-quiz__num">' + num + '.</span>';
        termEl.appendChild(document.createTextNode(term));

        const select = document.createElement('select');
        select.className = 'fsiot-match-quiz__select';
        select.setAttribute('aria-label', term);
        const empty = document.createElement('option');
        empty.value = '';
        empty.textContent = labels.placeholder;
        select.appendChild(empty);
        meanings.forEach(m => {
            const opt = document.createElement('option');
            opt.value = m.letter;
            opt.textContent = m.letter + '. ' + m.text;
            select.appendChild(opt);
        });

        const status = document.createElement('span');
        status.className = 'fsiot-match-quiz__status';
        status.setAttribute('aria-live', 'polite');

        li.appendChild(termEl);
        li.appendChild(select);
        li.appendChild(status);
        list.appendChild(li);
        selects.push({ select, status, li, num });
    });
    widget.appendChild(list);

    const result = document.createElement('p');
    result.className = 'fsiot-match-quiz__result';
    result.hidden = true;
    result.setAttribute('aria-live', 'polite');
    widget.appendChild(result);

    const actions = document.createElement('div');
    actions.className = 'fsiot-match-quiz__actions';

    const checkBtn = document.createElement('button');
    checkBtn.type = 'button';
    checkBtn.className = 'fsiot-match-quiz__btn';
    checkBtn.textContent = labels.check;

    const retryBtn = document.createElement('button');
    retryBtn.type = 'button';
    retryBtn.className = 'fsiot-match-quiz__btn fsiot-match-quiz__btn--ghost';
    retryBtn.textContent = labels.retry;

    const keyBtn = document.createElement('button');
    keyBtn.type = 'button';
    keyBtn.className = 'fsiot-match-quiz__btn fsiot-match-quiz__btn--ghost';
    keyBtn.textContent = labels.showKey;

    actions.appendChild(checkBtn);
    actions.appendChild(retryBtn);
    actions.appendChild(keyBtn);
    widget.appendChild(actions);

    paper.before(widget);

    const updateProgress = () => {
        const filled = selects.filter(s => s.select.value !== '').length;
        progressEl.textContent = labels.progress.replace(':filled', String(filled));
    };
    selects.forEach(s => s.select.addEventListener('change', updateProgress));
    updateProgress();

    const clearMarks = () => {
        selects.forEach(s => {
            s.li.classList.remove('is-correct', 'is-wrong');
            s.status.textContent = '';
            s.select.disabled = false;
        });
        result.hidden = true;
        result.classList.remove('is-pass', 'is-fail', 'is-warn');
    };

    checkBtn.addEventListener('click', () => {
        const filled = selects.filter(s => s.select.value !== '').length;
        if (filled < 15) {
            result.hidden = false;
            result.classList.remove('is-pass', 'is-fail');
            result.classList.add('is-warn');
            result.textContent = labels.incomplete;
            return;
        }

        let score = 0;
        selects.forEach(s => {
            const ok = s.select.value === answers[s.num];
            if (ok) score += 1;
            s.li.classList.toggle('is-correct', ok);
            s.li.classList.toggle('is-wrong', !ok);
            s.status.textContent = ok ? labels.correct : labels.wrong;
            s.select.disabled = true;
        });

        result.hidden = false;
        result.classList.remove('is-warn');
        const tpl = score >= 12 ? labels.pass : labels.fail;
        result.classList.toggle('is-pass', score >= 12);
        result.classList.toggle('is-fail', score < 12);
        result.textContent = tpl.replace(':score', String(score)).replace(':total', '15');
        keyWrap.classList.remove('is-hidden');
        keyBtn.textContent = labels.hideKey;
    });

    retryBtn.addEventListener('click', () => {
        selects.forEach(s => { s.select.value = ''; });
        clearMarks();
        updateProgress();
        keyWrap.classList.add('is-hidden');
        keyBtn.textContent = labels.showKey;
        selects[0]?.select.focus();
    });

    keyBtn.addEventListener('click', () => {
        const hidden = keyWrap.classList.toggle('is-hidden');
        keyBtn.textContent = hidden ? labels.showKey : labels.hideKey;
        if (!hidden) {
            keyWrap.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    });
}

function initFsiotWorksheet() {
    const labels = {
        badge: @js(__('ui.articles.fsiot_ws_badge')),
        hint: @js(__('ui.articles.fsiot_ws_hint')),
        placeholder: @js(__('ui.articles.fsiot_ws_placeholder')),
        check: @js(__('ui.articles.fsiot_ws_check')),
        retry: @js(__('ui.articles.fsiot_ws_retry')),
        showSamples: @js(__('ui.articles.fsiot_ws_show_samples')),
        hideSamples: @js(__('ui.articles.fsiot_ws_hide_samples')),
        paper: @js(__('ui.articles.fsiot_ws_paper')),
        progress: @js(__('ui.articles.fsiot_ws_progress')),
        phaseLabel: @js(__('ui.articles.fsiot_ws_phase_label')),
        pass: @js(__('ui.articles.fsiot_ws_pass')),
        incomplete: @js(__('ui.articles.fsiot_ws_incomplete')),
        phaseWrong: @js(__('ui.articles.fsiot_ws_phase_wrong')),
        samplesTitle: @js(__('ui.articles.fsiot_ws_samples_title')),
        samplesNote: @js(__('ui.articles.fsiot_ws_samples_note')),
        ok: @js(__('ui.articles.fsiot_ws_ok')),
        short: @js(__('ui.articles.fsiot_ws_short')),
        empty: @js(__('ui.articles.fsiot_ws_empty')),
    };

    const content = document.getElementById('article-content');
    if (!content) return;

    const wsH2 = content.querySelector('#fsiot-worksheet-boxes');
    if (!wsH2) return;

    const nextH2 = (() => {
        for (let n = wsH2.nextElementSibling; n; n = n.nextElementSibling) {
            if (n.tagName === 'H2') return n;
        }
        return null;
    })();

    const sectionNodes = [];
    for (let n = wsH2.nextElementSibling; n && n !== nextH2; n = n.nextElementSibling) {
        sectionNodes.push(n);
    }

    const table = sectionNodes.find(n => n.tagName === 'TABLE');
    if (!table) return;

    const boxes = Array.from(table.querySelectorAll('tbody tr')).map(tr => {
        const cells = tr.querySelectorAll('td');
        return cells[0] ? cells[0].textContent.trim() : '';
    }).filter(Boolean);

    if (boxes.length !== 7) return;

    // Sample roles from the earlier "one sentence per box" table
    const samples = {};
    const rolesH2 = content.querySelector('#fsiot-layer-roles');
    if (rolesH2) {
        let rolesTable = null;
        for (let n = rolesH2.nextElementSibling; n && n.tagName !== 'H2'; n = n.nextElementSibling) {
            if (n.tagName === 'TABLE') { rolesTable = n; break; }
        }
        if (rolesTable) {
            rolesTable.querySelectorAll('tbody tr').forEach(tr => {
                const cells = tr.querySelectorAll('td');
                if (cells.length >= 2) {
                    const name = cells[0].textContent.trim().replace(/\s*\(.*\)\s*$/, '').trim();
                    samples[name] = cells[1].textContent.trim();
                }
            });
        }
    }

    const intro = sectionNodes[0] && sectionNodes[0].tagName === 'P' ? sectionNodes[0] : null;
    const howto = sectionNodes.find(n => n.tagName === 'P' && n !== intro && /Awam|Beginner/i.test(n.textContent || ''));

    const paper = document.createElement('details');
    paper.className = 'fsiot-match-paper';
    const paperSummary = document.createElement('summary');
    paperSummary.textContent = labels.paper;
    paper.appendChild(paperSummary);

    const toPaper = sectionNodes.filter(n => n !== intro && n !== howto);
    toPaper.forEach(n => paper.appendChild(n));
    if (intro) {
        intro.after(paper);
    } else {
        wsH2.after(paper);
    }
    if (howto) {
        paper.after(howto);
    }

    const widget = document.createElement('div');
    widget.className = 'fsiot-match-quiz';
    widget.setAttribute('role', 'region');
    widget.setAttribute('aria-label', labels.badge);

    const head = document.createElement('div');
    head.className = 'fsiot-match-quiz__head';
    head.innerHTML = '<span class="fsiot-match-quiz__badge"></span>'
        + '<p class="fsiot-match-quiz__hint"></p>'
        + '<span class="fsiot-match-quiz__progress" aria-live="polite"></span>';
    head.querySelector('.fsiot-match-quiz__badge').textContent = labels.badge;
    head.querySelector('.fsiot-match-quiz__hint').textContent = labels.hint;
    const progressEl = head.querySelector('.fsiot-match-quiz__progress');
    widget.appendChild(head);

    const list = document.createElement('ul');
    list.className = 'fsiot-match-quiz__list';

    const storageKey = 'fsiot-ws-72:' + (document.documentElement.lang || 'id');
    let saved = {};
    try { saved = JSON.parse(localStorage.getItem(storageKey) || '{}') || {}; } catch (e) { saved = {}; }

    const rows = [];
    boxes.forEach((box, idx) => {
        const num = idx + 1;
        const li = document.createElement('li');
        li.className = 'fsiot-match-quiz__row';

        const termEl = document.createElement('p');
        termEl.className = 'fsiot-match-quiz__term';
        termEl.innerHTML = '<span class="fsiot-match-quiz__num">' + num + '.</span>';
        termEl.appendChild(document.createTextNode(box));

        const input = document.createElement('textarea');
        input.className = 'fsiot-match-quiz__input';
        input.rows = 2;
        input.setAttribute('aria-label', box);
        input.placeholder = labels.placeholder;
        if (saved[box]) input.value = saved[box];

        const status = document.createElement('span');
        status.className = 'fsiot-match-quiz__status';
        status.setAttribute('aria-live', 'polite');

        li.appendChild(termEl);
        li.appendChild(input);
        li.appendChild(status);
        list.appendChild(li);
        rows.push({ box, input, status, li });
    });
    widget.appendChild(list);

    const phasesWrap = document.createElement('div');
    phasesWrap.className = 'fsiot-match-quiz__phases';
    const phaseLabel = document.createElement('p');
    phaseLabel.className = 'fsiot-match-quiz__phase-label';
    phaseLabel.textContent = labels.phaseLabel;
    phasesWrap.appendChild(phaseLabel);

    const phases = ['ZERO', 'BUILDER', 'CONNECTED', 'FULLSTACK', 'HERO'];
    let selectedPhase = saved.phase || 'ZERO';
    const phaseBtns = [];
    phases.forEach(phase => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'fsiot-match-quiz__phase' + (phase === selectedPhase ? ' is-active' : '');
        btn.textContent = phase;
        btn.setAttribute('aria-pressed', phase === selectedPhase ? 'true' : 'false');
        btn.addEventListener('click', () => {
            selectedPhase = phase;
            phaseBtns.forEach(b => {
                const on = b.textContent === selectedPhase;
                b.classList.toggle('is-active', on);
                b.setAttribute('aria-pressed', on ? 'true' : 'false');
            });
            persist();
        });
        phasesWrap.appendChild(btn);
        phaseBtns.push(btn);
    });
    widget.appendChild(phasesWrap);

    const samplesEl = document.createElement('div');
    samplesEl.className = 'fsiot-match-quiz__samples';
    samplesEl.hidden = true;
    const samplesTitle = document.createElement('p');
    samplesTitle.className = 'fsiot-match-quiz__samples-title';
    samplesTitle.textContent = labels.samplesTitle;
    const samplesNote = document.createElement('p');
    samplesNote.className = 'fsiot-match-quiz__samples-note';
    samplesNote.textContent = labels.samplesNote;
    const samplesList = document.createElement('ol');
    samplesList.className = 'fsiot-match-quiz__samples-list';
    boxes.forEach(box => {
        const li = document.createElement('li');
        const shortName = box.replace(/\s*\(.*\)\s*$/, '').trim();
        const sample = samples[shortName] || samples[box] || '—';
        li.innerHTML = '<strong></strong>: ';
        li.querySelector('strong').textContent = box;
        li.appendChild(document.createTextNode(sample));
        samplesList.appendChild(li);
    });
    samplesEl.appendChild(samplesTitle);
    samplesEl.appendChild(samplesNote);
    samplesEl.appendChild(samplesList);
    widget.appendChild(samplesEl);

    const result = document.createElement('p');
    result.className = 'fsiot-match-quiz__result';
    result.hidden = true;
    result.setAttribute('aria-live', 'polite');
    widget.appendChild(result);

    const actions = document.createElement('div');
    actions.className = 'fsiot-match-quiz__actions';

    const checkBtn = document.createElement('button');
    checkBtn.type = 'button';
    checkBtn.className = 'fsiot-match-quiz__btn';
    checkBtn.textContent = labels.check;

    const retryBtn = document.createElement('button');
    retryBtn.type = 'button';
    retryBtn.className = 'fsiot-match-quiz__btn fsiot-match-quiz__btn--ghost';
    retryBtn.textContent = labels.retry;

    const samplesBtn = document.createElement('button');
    samplesBtn.type = 'button';
    samplesBtn.className = 'fsiot-match-quiz__btn fsiot-match-quiz__btn--ghost';
    samplesBtn.textContent = labels.showSamples;

    actions.appendChild(checkBtn);
    actions.appendChild(retryBtn);
    actions.appendChild(samplesBtn);
    widget.appendChild(actions);

    paper.before(widget);

    const isFilled = (val) => val.trim().replace(/\s+/g, ' ').length >= 8;

    const persist = () => {
        const data = { phase: selectedPhase };
        rows.forEach(r => { data[r.box] = r.input.value; });
        try { localStorage.setItem(storageKey, JSON.stringify(data)); } catch (e) {}
    };

    const updateProgress = () => {
        const filled = rows.filter(r => isFilled(r.input.value)).length;
        progressEl.textContent = labels.progress.replace(':filled', String(filled));
        persist();
    };
    rows.forEach(r => r.input.addEventListener('input', updateProgress));
    updateProgress();

    const clearMarks = () => {
        rows.forEach(r => {
            r.li.classList.remove('is-correct', 'is-wrong');
            r.status.textContent = '';
            r.input.disabled = false;
        });
        result.hidden = true;
        result.classList.remove('is-pass', 'is-fail', 'is-warn');
    };

    checkBtn.addEventListener('click', () => {
        let filled = 0;
        rows.forEach(r => {
            const val = r.input.value;
            const ok = isFilled(val);
            const empty = val.trim() === '';
            if (ok) filled += 1;
            r.li.classList.toggle('is-correct', ok);
            r.li.classList.toggle('is-wrong', !ok);
            r.status.textContent = ok ? labels.ok : (empty ? labels.empty : labels.short);
        });

        result.hidden = false;
        result.classList.remove('is-pass', 'is-fail', 'is-warn');

        if (filled < 7) {
            result.classList.add('is-warn');
            result.textContent = labels.incomplete;
            return;
        }

        if (selectedPhase !== 'ZERO') {
            result.classList.add('is-fail');
            result.textContent = labels.phaseWrong;
            return;
        }

        rows.forEach(r => { r.input.disabled = true; });
        result.classList.add('is-pass');
        result.textContent = labels.pass.replace(':filled', String(filled));
        samplesEl.hidden = false;
        samplesBtn.textContent = labels.hideSamples;
    });

    retryBtn.addEventListener('click', () => {
        rows.forEach(r => { r.input.value = ''; });
        selectedPhase = 'ZERO';
        phaseBtns.forEach(b => {
            const on = b.textContent === 'ZERO';
            b.classList.toggle('is-active', on);
            b.setAttribute('aria-pressed', on ? 'true' : 'false');
        });
        clearMarks();
        samplesEl.hidden = true;
        samplesBtn.textContent = labels.showSamples;
        updateProgress();
        rows[0]?.input.focus();
    });

    samplesBtn.addEventListener('click', () => {
        samplesEl.hidden = !samplesEl.hidden;
        samplesBtn.textContent = samplesEl.hidden ? labels.showSamples : labels.hideSamples;
        if (!samplesEl.hidden) {
            samplesEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    });
}

function initFsiotKitChecklist() {
    initFsiotChecklistWidget({
        h2Id: 'fsiot-kit-checklist',
        listId: 'fsiot-kit-checklist-items',
        storagePrefix: 'fsiot-cl-74',
        idPrefix: 'fsiot-cl',
        minItems: 8,
        labels: {
            badge: @js(__('ui.articles.fsiot_cl_badge')),
            hint: @js(__('ui.articles.fsiot_cl_hint')),
            check: @js(__('ui.articles.fsiot_cl_check')),
            retry: @js(__('ui.articles.fsiot_cl_retry')),
            paper: @js(__('ui.articles.fsiot_cl_paper')),
            progress: @js(__('ui.articles.fsiot_cl_progress')),
            pass: @js(__('ui.articles.fsiot_cl_pass')),
            incomplete: @js(__('ui.articles.fsiot_cl_incomplete')),
            done: @js(__('ui.articles.fsiot_cl_done')),
            todo: @js(__('ui.articles.fsiot_cl_todo')),
        },
    });
}

function initFsiotSafetyChecklist() {
    initFsiotChecklistWidget({
        h2Id: 'fsiot-safety-checklist',
        listId: 'fsiot-safety-checklist-items',
        storagePrefix: 'fsiot-cl-75',
        idPrefix: 'fsiot-sf',
        minItems: 8,
        labels: {
            badge: @js(__('ui.articles.fsiot_sf_badge')),
            hint: @js(__('ui.articles.fsiot_sf_hint')),
            check: @js(__('ui.articles.fsiot_sf_check')),
            retry: @js(__('ui.articles.fsiot_sf_retry')),
            paper: @js(__('ui.articles.fsiot_sf_paper')),
            progress: @js(__('ui.articles.fsiot_sf_progress')),
            pass: @js(__('ui.articles.fsiot_sf_pass')),
            incomplete: @js(__('ui.articles.fsiot_sf_incomplete')),
            done: @js(__('ui.articles.fsiot_sf_done')),
            todo: @js(__('ui.articles.fsiot_sf_todo')),
        },
    });
}

function initFsiotSetupChecklist() {
    initFsiotChecklistWidget({
        h2Id: 'fsiot-setup-checklist',
        listId: 'fsiot-setup-checklist-items',
        storagePrefix: 'fsiot-cl-76',
        idPrefix: 'fsiot-su',
        minItems: 8,
        labels: {
            badge: @js(__('ui.articles.fsiot_su_badge')),
            hint: @js(__('ui.articles.fsiot_su_hint')),
            check: @js(__('ui.articles.fsiot_su_check')),
            retry: @js(__('ui.articles.fsiot_su_retry')),
            paper: @js(__('ui.articles.fsiot_su_paper')),
            progress: @js(__('ui.articles.fsiot_su_progress')),
            pass: @js(__('ui.articles.fsiot_su_pass')),
            incomplete: @js(__('ui.articles.fsiot_su_incomplete')),
            done: @js(__('ui.articles.fsiot_su_done')),
            todo: @js(__('ui.articles.fsiot_su_todo')),
        },
    });
}

function initFsiotMultimeterChecklist() {
    initFsiotChecklistWidget({
        h2Id: 'fsiot-multimeter-checklist',
        listId: 'fsiot-multimeter-checklist-items',
        storagePrefix: 'fsiot-cl-77',
        idPrefix: 'fsiot-mm',
        minItems: 8,
        labels: {
            badge: @js(__('ui.articles.fsiot_mm_badge')),
            hint: @js(__('ui.articles.fsiot_mm_hint')),
            check: @js(__('ui.articles.fsiot_mm_check')),
            retry: @js(__('ui.articles.fsiot_mm_retry')),
            paper: @js(__('ui.articles.fsiot_mm_paper')),
            progress: @js(__('ui.articles.fsiot_mm_progress')),
            pass: @js(__('ui.articles.fsiot_mm_pass')),
            incomplete: @js(__('ui.articles.fsiot_mm_incomplete')),
            done: @js(__('ui.articles.fsiot_mm_done')),
            todo: @js(__('ui.articles.fsiot_mm_todo')),
        },
    });
}

function initFsiotChecklistWidget(cfg) {
    const labels = cfg.labels;
    const content = document.getElementById('article-content');
    if (!content) return;

    const clH2 = content.querySelector('#' + cfg.h2Id);
    if (!clH2) return;

    const nextH2 = (() => {
        for (let n = clH2.nextElementSibling; n; n = n.nextElementSibling) {
            if (n.tagName === 'H2') return n;
        }
        return null;
    })();

    const sectionNodes = [];
    for (let n = clH2.nextElementSibling; n && n !== nextH2; n = n.nextElementSibling) {
        sectionNodes.push(n);
    }

    const list = sectionNodes.find(n => n.id === cfg.listId || (n.tagName === 'UL' && n.querySelectorAll('li').length >= cfg.minItems));
    if (!list) return;

    const items = Array.from(list.querySelectorAll('li')).map(li => li.textContent.trim()).filter(Boolean);
    if (items.length < cfg.minItems) return;

    const intro = sectionNodes[0] && sectionNodes[0].tagName === 'P' ? sectionNodes[0] : null;
    const howto = sectionNodes.find(n => n.tagName === 'P' && n !== intro && /Awam|Beginner/i.test(n.textContent || ''));

    const paper = document.createElement('details');
    paper.className = 'fsiot-match-paper';
    const paperSummary = document.createElement('summary');
    paperSummary.textContent = labels.paper;
    paper.appendChild(paperSummary);

    const toPaper = sectionNodes.filter(n => n !== intro && n !== howto);
    toPaper.forEach(n => paper.appendChild(n));
    if (intro) {
        intro.after(paper);
    } else {
        clH2.after(paper);
    }
    if (howto) {
        paper.after(howto);
    }

    const lang = (document.documentElement.lang || 'id').slice(0, 2);
    const storageKey = `${cfg.storagePrefix}:${lang}`;

    const widget = document.createElement('div');
    widget.className = 'fsiot-match-quiz fsiot-kit-checklist';
    widget.setAttribute('role', 'region');
    widget.setAttribute('aria-label', labels.badge);

    const head = document.createElement('div');
    head.className = 'fsiot-match-quiz__head';
    head.innerHTML = `<span class="fsiot-match-quiz__badge">${labels.badge}</span><p class="fsiot-match-quiz__hint"></p><span class="fsiot-match-quiz__progress"></span>`;
    head.querySelector('.fsiot-match-quiz__hint').textContent = labels.hint;
    const progressEl = head.querySelector('.fsiot-match-quiz__progress');

    const body = document.createElement('div');
    body.className = 'fsiot-match-quiz__body';

    const ul = document.createElement('ul');
    ul.className = 'fsiot-kit-checklist__list';

    let saved = [];
    try {
        saved = JSON.parse(localStorage.getItem(storageKey) || '[]');
        if (!Array.isArray(saved)) saved = [];
    } catch (_) {
        saved = [];
    }

    const rows = items.map((label, i) => {
        const li = document.createElement('li');
        li.className = 'fsiot-kit-checklist__item';
        const id = `${cfg.idPrefix}-${i}`;
        const cb = document.createElement('input');
        cb.type = 'checkbox';
        cb.id = id;
        cb.className = 'fsiot-kit-checklist__cb';
        cb.checked = !!saved[i];
        const lab = document.createElement('label');
        lab.htmlFor = id;
        lab.textContent = label;
        const mark = document.createElement('span');
        mark.className = 'fsiot-kit-checklist__mark';
        mark.textContent = cb.checked ? labels.done : labels.todo;
        li.appendChild(cb);
        li.appendChild(lab);
        li.appendChild(mark);
        ul.appendChild(li);
        return { cb, mark };
    });

    body.appendChild(ul);

    const actions = document.createElement('div');
    actions.className = 'fsiot-match-quiz__actions';
    const checkBtn = document.createElement('button');
    checkBtn.type = 'button';
    checkBtn.className = 'fsiot-match-quiz__btn fsiot-match-quiz__btn--primary';
    checkBtn.textContent = labels.check;
    const retryBtn = document.createElement('button');
    retryBtn.type = 'button';
    retryBtn.className = 'fsiot-match-quiz__btn';
    retryBtn.textContent = labels.retry;
    actions.appendChild(checkBtn);
    actions.appendChild(retryBtn);

    const result = document.createElement('p');
    result.className = 'fsiot-match-quiz__result';
    result.setAttribute('aria-live', 'polite');

    widget.appendChild(head);
    widget.appendChild(body);
    widget.appendChild(actions);
    widget.appendChild(result);

    if (intro) {
        intro.after(widget);
    } else {
        clH2.after(widget);
    }

    const total = rows.length;

    function persist() {
        localStorage.setItem(storageKey, JSON.stringify(rows.map(r => r.cb.checked)));
    }

    function updateProgress() {
        const filled = rows.filter(r => r.cb.checked).length;
        progressEl.textContent = labels.progress
            .replace(':filled', String(filled))
            .replace(':total', String(total));
        rows.forEach(r => {
            r.mark.textContent = r.cb.checked ? labels.done : labels.todo;
            r.cb.closest('li').classList.toggle('is-checked', r.cb.checked);
        });
        persist();
    }

    rows.forEach(r => r.cb.addEventListener('change', () => {
        result.className = 'fsiot-match-quiz__result';
        result.textContent = '';
        updateProgress();
    }));

    checkBtn.addEventListener('click', () => {
        const filled = rows.filter(r => r.cb.checked).length;
        result.className = 'fsiot-match-quiz__result';
        if (filled < total) {
            result.classList.add('is-warn');
            result.textContent = labels.incomplete;
            return;
        }
        result.classList.add('is-pass');
        result.textContent = labels.pass
            .replace(':filled', String(filled))
            .replace(':total', String(total));
    });

    retryBtn.addEventListener('click', () => {
        rows.forEach(r => { r.cb.checked = false; });
        result.className = 'fsiot-match-quiz__result';
        result.textContent = '';
        updateProgress();
    });

    updateProgress();
}

function initFsiotResistorCalc() {
    const labels = {
        badge: @js(__('ui.articles.fsiot_rc_badge')),
        hint: @js(__('ui.articles.fsiot_rc_hint')),
        supply: @js(__('ui.articles.fsiot_rc_label_supply')),
        vled: @js(__('ui.articles.fsiot_rc_label_vled')),
        current: @js(__('ui.articles.fsiot_rc_label_current')),
        calc: @js(__('ui.articles.fsiot_rc_calc')),
        retry: @js(__('ui.articles.fsiot_rc_retry')),
        result: @js(__('ui.articles.fsiot_rc_result')),
        pass: @js(__('ui.articles.fsiot_rc_pass')),
        invalid: @js(__('ui.articles.fsiot_rc_invalid')),
    };

    const content = document.getElementById('article-content');
    if (!content) return;

    const root = content.querySelector('#fsiot-resistor-calc-root');
    if (!root) return;

    const lang = (document.documentElement.lang || 'id').slice(0, 2);
    const storageKey = `fsiot-rc-78:${lang}`;

    let saved = {};
    try {
        saved = JSON.parse(localStorage.getItem(storageKey) || '{}');
    } catch (_) {
        saved = {};
    }

    const widget = document.createElement('div');
    widget.className = 'fsiot-match-quiz fsiot-resistor-calc';
    widget.setAttribute('role', 'region');
    widget.setAttribute('aria-label', labels.badge);

    const head = document.createElement('div');
    head.className = 'fsiot-match-quiz__head';
    head.innerHTML = `<span class="fsiot-match-quiz__badge">${labels.badge}</span><p class="fsiot-match-quiz__hint"></p>`;
    head.querySelector('.fsiot-match-quiz__hint').textContent = labels.hint;

    const body = document.createElement('div');
    body.className = 'fsiot-match-quiz__body fsiot-resistor-calc__grid';

    function field(label, id, value, step) {
        const wrap = document.createElement('label');
        wrap.className = 'fsiot-resistor-calc__field';
        wrap.htmlFor = id;
        const span = document.createElement('span');
        span.textContent = label;
        const input = document.createElement('input');
        input.type = 'number';
        input.id = id;
        input.className = 'fsiot-resistor-calc__input';
        input.step = step || '0.1';
        input.min = '0';
        input.value = value;
        wrap.appendChild(span);
        wrap.appendChild(input);
        return { wrap, input };
    }

    const fSupply = field(labels.supply, 'fsiot-rc-supply', saved.vs ?? '3.3', '0.1');
    const fVled = field(labels.vled, 'fsiot-rc-vled', saved.vl ?? '2.0', '0.1');
    const fI = field(labels.current, 'fsiot-rc-i', saved.im ?? '10', '1');

    body.appendChild(fSupply.wrap);
    body.appendChild(fVled.wrap);
    body.appendChild(fI.wrap);

    const actions = document.createElement('div');
    actions.className = 'fsiot-match-quiz__actions';
    const calcBtn = document.createElement('button');
    calcBtn.type = 'button';
    calcBtn.className = 'fsiot-match-quiz__btn fsiot-match-quiz__btn--primary';
    calcBtn.textContent = labels.calc;
    const retryBtn = document.createElement('button');
    retryBtn.type = 'button';
    retryBtn.className = 'fsiot-match-quiz__btn';
    retryBtn.textContent = labels.retry;
    actions.appendChild(calcBtn);
    actions.appendChild(retryBtn);

    const result = document.createElement('p');
    result.className = 'fsiot-match-quiz__result';
    result.setAttribute('aria-live', 'polite');

    widget.appendChild(head);
    widget.appendChild(body);
    widget.appendChild(actions);
    widget.appendChild(result);
    root.appendChild(widget);

    function persist() {
        localStorage.setItem(storageKey, JSON.stringify({
            vs: fSupply.input.value,
            vl: fVled.input.value,
            im: fI.input.value,
        }));
    }

    [fSupply.input, fVled.input, fI.input].forEach(inp => inp.addEventListener('input', persist));

    function pickStandard(r) {
        if (r > 280) return '330';
        return '220';
    }

    calcBtn.addEventListener('click', () => {
        const vs = parseFloat(fSupply.input.value);
        const vl = parseFloat(fVled.input.value);
        const im = parseFloat(fI.input.value);
        result.className = 'fsiot-match-quiz__result';
        if (!Number.isFinite(vs) || !Number.isFinite(vl) || !Number.isFinite(im) || im <= 0 || vs <= vl) {
            result.classList.add('is-warn');
            result.textContent = labels.invalid;
            return;
        }
        const i = im / 1000;
        const vRem = vs - vl;
        const r = Math.round((vRem / i) * 10) / 10;
        const pick = pickStandard(r);
        result.classList.add(r >= 100 && r <= 200 ? 'is-pass' : 'is-warn');
        result.textContent = labels.result
            .replace(':r', String(r))
            .replace(':vrem', String(Math.round(vRem * 100) / 100))
            .replace(':pick', pick);
        if (r >= 100 && r <= 200) {
            result.textContent += ' ' + labels.pass;
        }
        persist();
    });

    retryBtn.addEventListener('click', () => {
        fSupply.input.value = '3.3';
        fVled.input.value = '2.0';
        fI.input.value = '10';
        result.className = 'fsiot-match-quiz__result';
        result.textContent = '';
        persist();
    });
}

function initFsiotElectricChecklist() {
    initFsiotChecklistWidget({
        h2Id: 'fsiot-electric-checklist',
        listId: 'fsiot-electric-checklist-items',
        storagePrefix: 'fsiot-cl-78',
        idPrefix: 'fsiot-el',
        minItems: 8,
        labels: {
            badge: @js(__('ui.articles.fsiot_el_badge')),
            hint: @js(__('ui.articles.fsiot_el_hint')),
            check: @js(__('ui.articles.fsiot_el_check')),
            retry: @js(__('ui.articles.fsiot_el_retry')),
            paper: @js(__('ui.articles.fsiot_el_paper')),
            progress: @js(__('ui.articles.fsiot_el_progress')),
            pass: @js(__('ui.articles.fsiot_el_pass')),
            incomplete: @js(__('ui.articles.fsiot_el_incomplete')),
            done: @js(__('ui.articles.fsiot_el_done')),
            todo: @js(__('ui.articles.fsiot_el_todo')),
        },
    });
}

function initFsiotLedCircuitChecklist() {
    initFsiotChecklistWidget({
        h2Id: 'fsiot-led-circuit-checklist',
        listId: 'fsiot-led-circuit-checklist-items',
        storagePrefix: 'fsiot-cl-79',
        idPrefix: 'fsiot-lc',
        minItems: 10,
        labels: {
            badge: @js(__('ui.articles.fsiot_lc_badge')),
            hint: @js(__('ui.articles.fsiot_lc_hint')),
            check: @js(__('ui.articles.fsiot_lc_check')),
            retry: @js(__('ui.articles.fsiot_lc_retry')),
            paper: @js(__('ui.articles.fsiot_lc_paper')),
            progress: @js(__('ui.articles.fsiot_lc_progress')),
            pass: @js(__('ui.articles.fsiot_lc_pass')),
            incomplete: @js(__('ui.articles.fsiot_lc_incomplete')),
            done: @js(__('ui.articles.fsiot_lc_done')),
            todo: @js(__('ui.articles.fsiot_lc_todo')),
        },
    });
}

function initFsiotSignalChecklist() {
    initFsiotChecklistWidget({
        h2Id: 'fsiot-signal-checklist',
        listId: 'fsiot-signal-checklist-items',
        storagePrefix: 'fsiot-cl-80',
        idPrefix: 'fsiot-sg',
        minItems: 10,
        labels: {
            badge: @js(__('ui.articles.fsiot_sg_badge')),
            hint: @js(__('ui.articles.fsiot_sg_hint')),
            check: @js(__('ui.articles.fsiot_sg_check')),
            retry: @js(__('ui.articles.fsiot_sg_retry')),
            paper: @js(__('ui.articles.fsiot_sg_paper')),
            progress: @js(__('ui.articles.fsiot_sg_progress')),
            pass: @js(__('ui.articles.fsiot_sg_pass')),
            incomplete: @js(__('ui.articles.fsiot_sg_incomplete')),
            done: @js(__('ui.articles.fsiot_sg_done')),
            todo: @js(__('ui.articles.fsiot_sg_todo')),
        },
    });
}

function initFsiotSketchChecklist() {
    initFsiotChecklistWidget({
        h2Id: 'fsiot-sketch-checklist',
        listId: 'fsiot-sketch-checklist-items',
        storagePrefix: 'fsiot-cl-81',
        idPrefix: 'fsiot-sk',
        minItems: 10,
        labels: {
            badge: @js(__('ui.articles.fsiot_sk_badge')),
            hint: @js(__('ui.articles.fsiot_sk_hint')),
            check: @js(__('ui.articles.fsiot_sk_check')),
            retry: @js(__('ui.articles.fsiot_sk_retry')),
            paper: @js(__('ui.articles.fsiot_sk_paper')),
            progress: @js(__('ui.articles.fsiot_sk_progress')),
            pass: @js(__('ui.articles.fsiot_sk_pass')),
            incomplete: @js(__('ui.articles.fsiot_sk_incomplete')),
            done: @js(__('ui.articles.fsiot_sk_done')),
            todo: @js(__('ui.articles.fsiot_sk_todo')),
        },
    });
}

function initFsiotVarChecklist() {
    initFsiotChecklistWidget({
        h2Id: 'fsiot-var-checklist',
        listId: 'fsiot-var-checklist-items',
        storagePrefix: 'fsiot-cl-82',
        idPrefix: 'fsiot-vr',
        minItems: 10,
        labels: {
            badge: @js(__('ui.articles.fsiot_vr_badge')),
            hint: @js(__('ui.articles.fsiot_vr_hint')),
            check: @js(__('ui.articles.fsiot_vr_check')),
            retry: @js(__('ui.articles.fsiot_vr_retry')),
            paper: @js(__('ui.articles.fsiot_vr_paper')),
            progress: @js(__('ui.articles.fsiot_vr_progress')),
            pass: @js(__('ui.articles.fsiot_vr_pass')),
            incomplete: @js(__('ui.articles.fsiot_vr_incomplete')),
            done: @js(__('ui.articles.fsiot_vr_done')),
            todo: @js(__('ui.articles.fsiot_vr_todo')),
        },
    });
}

function initFsiotSerialChecklist() {
    initFsiotChecklistWidget({
        h2Id: 'fsiot-sm-checklist',
        listId: 'fsiot-sm-checklist-items',
        storagePrefix: 'fsiot-cl-83',
        idPrefix: 'fsiot-sm',
        minItems: 10,
        labels: {
            badge: @js(__('ui.articles.fsiot_sm_badge')),
            hint: @js(__('ui.articles.fsiot_sm_hint')),
            check: @js(__('ui.articles.fsiot_sm_check')),
            retry: @js(__('ui.articles.fsiot_sm_retry')),
            paper: @js(__('ui.articles.fsiot_sm_paper')),
            progress: @js(__('ui.articles.fsiot_sm_progress')),
            pass: @js(__('ui.articles.fsiot_sm_pass')),
            incomplete: @js(__('ui.articles.fsiot_sm_incomplete')),
            done: @js(__('ui.articles.fsiot_sm_done')),
            todo: @js(__('ui.articles.fsiot_sm_todo')),
        },
    });
}

function initFsiotIfChecklist() {
    initFsiotChecklistWidget({
        h2Id: 'fsiot-if-checklist',
        listId: 'fsiot-if-checklist-items',
        storagePrefix: 'fsiot-cl-84',
        idPrefix: 'fsiot-if',
        minItems: 10,
        labels: {
            badge: @js(__('ui.articles.fsiot_if_badge')),
            hint: @js(__('ui.articles.fsiot_if_hint')),
            check: @js(__('ui.articles.fsiot_if_check')),
            retry: @js(__('ui.articles.fsiot_if_retry')),
            paper: @js(__('ui.articles.fsiot_if_paper')),
            progress: @js(__('ui.articles.fsiot_if_progress')),
            pass: @js(__('ui.articles.fsiot_if_pass')),
            incomplete: @js(__('ui.articles.fsiot_if_incomplete')),
            done: @js(__('ui.articles.fsiot_if_done')),
            todo: @js(__('ui.articles.fsiot_if_todo')),
        },
    });
}

function initFsiotForWhileChecklist() {
    initFsiotChecklistWidget({
        h2Id: 'fsiot-fw-checklist',
        listId: 'fsiot-fw-checklist-items',
        storagePrefix: 'fsiot-cl-85',
        idPrefix: 'fsiot-fw',
        minItems: 10,
        labels: {
            badge: @js(__('ui.articles.fsiot_fw_badge')),
            hint: @js(__('ui.articles.fsiot_fw_hint')),
            check: @js(__('ui.articles.fsiot_fw_check')),
            retry: @js(__('ui.articles.fsiot_fw_retry')),
            paper: @js(__('ui.articles.fsiot_fw_paper')),
            progress: @js(__('ui.articles.fsiot_fw_progress')),
            pass: @js(__('ui.articles.fsiot_fw_pass')),
            incomplete: @js(__('ui.articles.fsiot_fw_incomplete')),
            done: @js(__('ui.articles.fsiot_fw_done')),
            todo: @js(__('ui.articles.fsiot_fw_todo')),
        },
    });
}

function initFsiotFnChecklist() {
    initFsiotChecklistWidget({
        h2Id: 'fsiot-fn-checklist',
        listId: 'fsiot-fn-checklist-items',
        storagePrefix: 'fsiot-cl-86',
        idPrefix: 'fsiot-fn',
        minItems: 10,
        labels: {
            badge: @js(__('ui.articles.fsiot_fn_badge')),
            hint: @js(__('ui.articles.fsiot_fn_hint')),
            check: @js(__('ui.articles.fsiot_fn_check')),
            retry: @js(__('ui.articles.fsiot_fn_retry')),
            paper: @js(__('ui.articles.fsiot_fn_paper')),
            progress: @js(__('ui.articles.fsiot_fn_progress')),
            pass: @js(__('ui.articles.fsiot_fn_pass')),
            incomplete: @js(__('ui.articles.fsiot_fn_incomplete')),
            done: @js(__('ui.articles.fsiot_fn_done')),
            todo: @js(__('ui.articles.fsiot_fn_todo')),
        },
    });
}
</script>
@endpush

</x-layouts.app>
