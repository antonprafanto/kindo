@props(['article', 'featured' => false])

<article class="article-card border-2 border-black flex flex-col h-full">

    {{-- Cover Image --}}
    <a href="{{ route('articles.show', $article->slug) }}" class="block overflow-hidden border-b-2 border-black" style="{{ $featured ? 'aspect-ratio:16/7' : 'aspect-ratio:16/9' }}">
        @if($article->cover_image)
            <img
                src="{{ $article->cover_url }}"
                alt="{{ $article->title }}"
                class="w-full h-full object-cover"
                loading="lazy"
                onerror="this.onerror=null;this.src='{{ asset('og-default.png') }}';"
            >
        @else
            <div class="w-full h-full flex items-center justify-center theme-surface" style="background: #E8EEF7;">
                <div class="text-center p-4">
                    <div class="text-xs font-bold uppercase tracking-wider theme-muted mb-2">Koding Indonesia</div>
                    <div class="text-xs font-mono font-semibold theme-heading opacity-70">{{ Str::limit($article->title, 40) }}</div>
                </div>
            </div>
        @endif
    </a>

    <div class="p-5 flex flex-col flex-1">
        {{-- Category + Read Time --}}
        <div class="flex items-center justify-between mb-3">
            @if($article->category)
            <a href="{{ route('categories.show', $article->category->slug) }}"
               class="text-xs font-bold uppercase tracking-wider px-2.5 py-1 border-2 border-black"
               style="background: {{ $article->category->color }}; color: {{ \App\Support\Contrast::textOn($article->category->color) }}; box-shadow: 2px 2px 0 #000;">
                {{ $article->category->name }}
            </a>
            @endif
            <span class="text-xs font-mono theme-muted">{{ $article->read_time_minutes ?? 1 }} menit</span>
        </div>

        {{-- Title --}}
        <h2 class="{{ $featured ? 'text-xl' : 'text-base' }} font-bold leading-snug mb-3 flex-1">
            <a href="{{ route('articles.show', $article->slug) }}" class="theme-heading hover:text-[#2979FF] transition-colors no-underline">
                {{ $article->title }}
            </a>
        </h2>

        {{-- Excerpt --}}
        @if($article->excerpt)
        <p class="text-sm leading-relaxed mb-4 theme-body">
            {{ Str::limit($article->excerpt, 100) }}
        </p>
        @endif

        {{-- Meta --}}
        <div class="flex items-center justify-between mt-auto pt-3 border-t border-black/10 dark:border-white/10 text-xs theme-muted">
            <span class="font-medium">{{ $article->published_at?->translatedFormat('d M Y') ?? '-' }}</span>
            <span class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                {{ number_format($article->views_count) }}
            </span>
        </div>
    </div>
</article>
