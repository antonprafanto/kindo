<x-layouts.app
    :title="__('ui.home.meta_title')"
    :description="__('ui.home.meta_description')"
    :ogDescription="__('ui.home.meta_og_description')"
>

    {{-- ═══════════════════════════════════ HERO ═══════════════════════════════════ --}}
    <section class="theme-paper border-b-4 border-black hero-grid">
        <div class="max-w-6xl mx-auto px-4 py-10 sm:py-16 lg:py-24">
            <div class="grid lg:grid-cols-2 gap-8 lg:gap-12 items-center">

                <div>
                    <div class="inline-flex items-center gap-2 text-white text-xs font-bold px-3 py-1.5 border-2 border-black mb-4 sm:mb-6" style="background:#FF7A2F; box-shadow:2px 2px 0 #000; text-transform:uppercase; letter-spacing:.05em;">
                        <span>✦</span> {{ __('ui.home.badge') }}
                    </div>

                    <h1 class="font-black leading-[1.1] mb-4 sm:mb-6 text-4xl sm:text-5xl lg:text-6xl theme-heading" style="letter-spacing:-0.03em;">
                        {{ __('ui.home.hero_line1') }}<br>
                        <span class="text-white px-2 sm:px-3 py-1 border-2 border-black text-3xl sm:text-4xl lg:text-5xl" style="background:#2979FF; box-shadow:4px 4px 0 #000; display:inline;">{{ __('ui.home.hero_highlight') }}</span><br>
                        {{ __('ui.home.hero_line3') }}
                    </h1>

                    <p class="text-sm sm:text-base leading-relaxed mb-6 sm:mb-8 max-w-lg theme-body" style="font-family: 'Inter', sans-serif;">
                        {{ __('ui.home.hero_body') }}
                    </p>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('articles.index') }}"
                           class="btn-brutal btn-primary px-7 py-3 text-sm">
                            {{ __('ui.home.cta_start') }}
                        </a>
                        <a href="{{ route('about') }}"
                           class="btn-brutal btn-outline px-7 py-3 text-sm">
                            {{ __('ui.home.cta_about') }}
                        </a>
                    </div>
                </div>

                {{-- Code Decoration --}}
                <div class="hidden lg:block">
                    <div class="border-2 border-black font-mono text-sm" style="background:#0d1117; box-shadow: 8px 8px 0 #000;">
                        <div class="flex items-center gap-2 px-4 py-3 border-b border-gray-700">
                            <div class="w-3 h-3 rounded-full bg-red-500 border border-red-700"></div>
                            <div class="w-3 h-3 rounded-full bg-yellow-400 border border-yellow-600"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500 border border-green-700"></div>
                            <span class="ml-3 text-xs" style="color:#8b949e;">hello_esp32.ino</span>
                        </div>
                        <pre class="p-5 text-xs leading-relaxed overflow-x-auto"><code style="color:#e6edf3; background:transparent;"><span style="color:#FF7A2F;">#include</span> <span style="color:#a5d6ff;">&lt;WiFi.h&gt;</span>
<span style="color:#FF7A2F;">#include</span> <span style="color:#a5d6ff;">&lt;WebServer.h&gt;</span>

<span style="color:#79c0ff;">const char*</span> ssid <span style="color:#e6edf3;">= </span><span style="color:#a5d6ff;">"KodingIndonesia"</span><span style="color:#e6edf3;">;</span>
<span style="color:#79c0ff;">const char*</span> pass <span style="color:#e6edf3;">= </span><span style="color:#a5d6ff;">"esp32rocks!"</span><span style="color:#e6edf3;">;</span>

<span style="color:#2979FF;">WebServer</span> server(<span style="color:#f0883e;">80</span>);

<span style="color:#79c0ff;">void</span> <span style="color:#d2a8ff;">setup</span>() {
  Serial.<span style="color:#d2a8ff;">begin</span>(<span style="color:#f0883e;">115200</span>);
  WiFi.<span style="color:#d2a8ff;">begin</span>(ssid, pass);

  <span style="color:#ff7b72;">while</span> (WiFi.<span style="color:#d2a8ff;">status</span>() != WL_CONNECTED) {
    <span style="color:#d2a8ff;">delay</span>(<span style="color:#f0883e;">500</span>);
    Serial.<span style="color:#d2a8ff;">print</span>(<span style="color:#a5d6ff;">"."</span>);
  }

  Serial.<span style="color:#d2a8ff;">println</span>(<span style="color:#a5d6ff;">"\n✓ Koneksi Berhasil!"</span>);
  Serial.<span style="color:#d2a8ff;">println</span>(WiFi.<span style="color:#d2a8ff;">localIP</span>());
}</code></pre>
                        <div class="px-4 py-2 border-t border-gray-700 flex items-center gap-2">
                            <span class="text-xs" style="color:#3fb950;">●</span>
                            <span class="text-xs" style="color:#8b949e;">Koneksi berhasil: 192.168.1.42</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- Stats (below hero viewport) --}}
    <section class="border-b-4 border-black theme-paper">
        <div class="max-w-6xl mx-auto px-4 py-6 sm:py-8">
            <div class="flex flex-wrap gap-6 sm:gap-10">
                <div>
                    <div class="text-2xl font-black" style="color:#2979FF;">{{ \App\Models\Article::published()->count() }}+</div>
                    <div class="text-xs font-semibold uppercase tracking-wider theme-muted">{{ __('ui.home.stat_articles') }}</div>
                </div>
                <div>
                    <div class="text-2xl font-black" style="color:#FF7A2F;">{{ \App\Models\Category::count() }}</div>
                    <div class="text-xs font-semibold uppercase tracking-wider theme-muted">{{ __('ui.home.stat_categories') }}</div>
                </div>
                <div>
                    <div class="text-2xl font-black">100%</div>
                    <div class="text-xs font-semibold uppercase tracking-wider theme-muted">{{ __('ui.home.stat_language') }}</div>
                </div>
            </div>
        </div>
    </section>

    {{-- Promo carousel: Beasiswa + FSIOT --}}
    <section
        class="py-8 sm:py-10 border-b-4 border-black"
        style="background: var(--color-surface);"
        x-data="homePromoCarousel()"
        aria-roledescription="carousel"
        aria-label="{{ __('ui.home.promo_carousel_label') }}"
    >
        <div class="max-w-6xl mx-auto px-4">
            <div class="relative theme-paper border-2 border-black overflow-hidden" style="box-shadow: 6px 6px 0 #000;">
                <div
                    class="home-promo-track flex"
                    :class="reduce ? '' : 'home-promo-track--animate'"
                    :style="'transform:translateX(-' + (i * 100) + '%)'"
                >
                    {{-- Slide 1: Beasiswa --}}
                    <div class="home-promo-slide beasiswa-page relative w-full shrink-0 p-5 sm:p-7" :aria-hidden="i !== 0 ? 'true' : 'false'">
                        <div class="beasiswa-hero-pattern absolute inset-0 pointer-events-none opacity-80" aria-hidden="true"></div>
                        <div class="relative max-w-3xl">
                            <div class="flex flex-wrap items-center gap-2 mb-3">
                                <span class="beasiswa-free-badge">{{ __('ui.home.beasiswa_badge_free') }}</span>
                                <span class="beasiswa-open-badge">
                                    <span class="beasiswa-open-dot" aria-hidden="true"></span>
                                    {{ __('ui.home.beasiswa_badge_open') }}
                                </span>
                            </div>
                            <h2 class="text-xl sm:text-2xl font-black mb-2 theme-heading" style="letter-spacing:-0.02em;">
                                {{ __('ui.home.beasiswa_title') }}
                            </h2>
                            <p class="theme-body text-sm max-w-2xl mb-4" style="font-family:'Inter',sans-serif; line-height:1.65;">
                                {{ __('ui.home.beasiswa_body') }}
                            </p>
                            <div class="flex flex-wrap gap-2 sm:gap-3">
                                <a href="{{ route('beasiswa') }}" class="btn-brutal btn-primary px-5 py-2.5 text-sm">{{ __('ui.home.beasiswa_cta') }}</a>
                                <a
                                    href="{{ config('kindo.scholarships.aws_ai_academy_url') }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="btn-brutal btn-dark px-5 py-2.5 text-sm inline-flex no-underline"
                                >{{ __('ui.home.beasiswa_secondary') }}</a>
                            </div>
                        </div>
                    </div>

                    {{-- Slide 2: FSIOT --}}
                    <div class="home-promo-slide relative w-full shrink-0 p-5 sm:p-7" :aria-hidden="i !== 1 ? 'true' : 'false'">
                        <div class="relative max-w-3xl">
                            <p class="fsiot-soon-badge mb-3">
                                <span class="fsiot-soon-dot" aria-hidden="true"></span>
                                {{ __('ui.home.fsiot_badge') }}
                            </p>
                            <h2 class="text-xl sm:text-2xl font-black mb-2 theme-heading" style="letter-spacing:-0.02em;">
                                {{ __('ui.home.fsiot_title') }}
                            </h2>
                            <p class="theme-body text-sm max-w-2xl mb-4" style="font-family:'Inter',sans-serif; line-height:1.65;">
                                {{ __('ui.home.fsiot_body') }}
                            </p>
                            <div class="flex flex-wrap gap-2 sm:gap-3">
                                <a href="{{ route('belajar.fullstack-iot') }}" class="btn-brutal btn-primary px-5 py-2.5 text-sm">{{ __('ui.home.fsiot_cta') }}</a>
                                <a href="{{ config('kindo.trakteer_tip_url') }}" target="_blank" rel="noopener noreferrer" class="btn-brutal btn-outline px-5 py-2.5 text-sm">{{ __('ui.home.fsiot_support') }}</a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Autoplays progress (resets each slide) --}}
                <div class="home-promo-progress" aria-hidden="true" x-show="!reduce">
                    <template x-if="progressOn">
                        <div class="home-promo-progress-bar" :class="paused ? 'is-paused' : ''"></div>
                    </template>
                </div>

                <div
                    class="flex items-center justify-between gap-3 px-4 sm:px-5 py-3 border-t-2 border-black theme-paper"
                    @mouseenter="pause()"
                    @mouseleave="resume()"
                    @focusin="pause()"
                    @focusout="resume()"
                >
                    <div class="flex items-center gap-2" role="tablist" aria-label="{{ __('ui.home.promo_carousel_label') }}">
                        <button
                            type="button"
                            class="home-promo-dot"
                            :class="i === 0 && 'is-active'"
                            aria-label="{{ __('ui.home.promo_carousel_goto', ['num' => 1]) }}"
                            :aria-current="i === 0 ? 'true' : 'false'"
                            @click="go(0)"
                        ></button>
                        <button
                            type="button"
                            class="home-promo-dot"
                            :class="i === 1 && 'is-active'"
                            aria-label="{{ __('ui.home.promo_carousel_goto', ['num' => 2]) }}"
                            :aria-current="i === 1 ? 'true' : 'false'"
                            @click="go(1)"
                        ></button>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" class="btn-brutal btn-outline px-3 py-1.5 text-xs" @click="prev()" aria-label="{{ __('ui.home.promo_carousel_prev') }}">←</button>
                        <button type="button" class="btn-brutal btn-outline px-3 py-1.5 text-xs" @click="next()" aria-label="{{ __('ui.home.promo_carousel_next') }}">→</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════ FEATURED ARTICLES ══════════════════════════ --}}
    @if($featuredArticles->count())
    <section class="py-10 sm:py-16 border-b-4 border-black theme-paper">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8 sm:mb-10">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-black relative inline-block" style="letter-spacing:-0.02em;">
                        {{ __('ui.home.featured_title') }}
                        <span class="absolute -bottom-1 left-0 w-full h-1" style="background:#FF7A2F;"></span>
                    </h2>
                    <p class="mt-3 text-sm theme-muted" style="font-family:'Inter',sans-serif;">{{ __('ui.home.featured_subtitle') }}</p>
                </div>
                <a href="{{ route('articles.index') }}" class="btn-brutal btn-outline text-sm px-5 py-2 hidden sm:flex">
                    {{ __('ui.home.see_all') }}
                </a>
            </div>

            <div class="grid md:grid-cols-3 gap-6">
                @foreach($featuredArticles as $article)
                    <x-article-card :article="$article" :featured="true" />
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- ═══════════════════════════════ CATEGORIES GRID ════════════════════════════ --}}
    <section class="py-10 sm:py-16 border-b-4 border-black" style="background: var(--color-surface);">
        <div class="max-w-6xl mx-auto px-4">
            <div class="mb-8 sm:mb-10">
                <h2 class="text-2xl sm:text-3xl font-black relative inline-block" style="letter-spacing:-0.02em;">
                    {{ __('ui.home.topics_title') }}
                    <span class="absolute -bottom-1 left-0 w-full h-1" style="background:#2979FF;"></span>
                </h2>
                <p class="mt-3 text-sm theme-muted" style="font-family:'Inter',sans-serif;">{{ __('ui.home.topics_subtitle') }}</p>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-3 gap-4">
                @foreach($categories as $cat)
                <a href="{{ route('categories.show', $cat->slug) }}"
                   class="group theme-paper border-2 border-black p-3 sm:p-5 text-center transition-all"
                   style="box-shadow: 3px 3px 0 #000;"
                   onmouseenter="this.style.transform='translate(-2px,-2px)';this.style.boxShadow='5px 5px 0 #000';this.style.background='{{ $cat->color }}'"
                   onmouseleave="this.style.transform='';this.style.boxShadow='3px 3px 0 #000';this.style.background=''">
                    <div class="w-10 h-10 rounded-full border-2 border-black mx-auto mb-3" style="background: {{ $cat->color }};"></div>
                    <div class="font-bold text-sm theme-heading group-hover:text-white">{{ $cat->name }}</div>
                    <div class="text-xs mt-1 font-mono theme-muted group-hover:text-white/80">{{ __('ui.home.articles_count', ['count' => $cat->articles_count]) }}</div>
                </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════ RECENT ARTICLES ════════════════════════════ --}}
    @if($recentArticles->count())
    <section class="py-10 sm:py-16 theme-paper">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8 sm:mb-10">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-black relative inline-block" style="letter-spacing:-0.02em;">
                        {{ __('ui.home.recent_title') }}
                        <span class="absolute -bottom-1 left-0 w-full h-1" style="background:#2979FF;"></span>
                    </h2>
                </div>
                <a href="{{ route('articles.index') }}" class="btn-brutal btn-outline text-sm px-5 py-2 hidden sm:flex">{{ __('ui.home.all_articles') }}</a>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($recentArticles as $article)
                    <x-article-card :article="$article" />
                @endforeach
            </div>

            <div class="text-center mt-10">
                <a href="{{ route('articles.index') }}" class="btn-brutal btn-dark px-10 py-3 text-sm">
                    {{ __('ui.home.see_all_articles') }}
                </a>
            </div>
        </div>
    </section>
    @endif

    {{-- ═══════════════════════════════ EMPTY STATE ════════════════════════════════ --}}
    @if($recentArticles->isEmpty() && $featuredArticles->isEmpty())
    <section class="py-32 text-center theme-paper">
        <div class="max-w-lg mx-auto px-4">
            <div class="text-8xl mb-6">🚀</div>
            <h2 class="text-3xl font-black mb-4">{{ __('ui.home.empty_title') }}</h2>
            <p class="mb-8 theme-muted" style="font-family:'Inter',sans-serif;">{{ __('ui.home.empty_body') }}</p>
            <a href="{{ route('contact') }}" class="btn-brutal btn-primary px-8 py-3">{{ __('ui.home.empty_cta') }}</a>
        </div>
    </section>
    @endif

</x-layouts.app>
