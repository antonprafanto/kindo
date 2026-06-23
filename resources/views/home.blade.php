<x-layouts.app
    title="Koding Indonesia — Tutorial ESP32 & IoT"
    description="Belajar ESP32, Arduino, IoT, dan pemrograman dalam Bahasa Indonesia. Tutorial praktis step-by-step gratis untuk pemula hingga mahir."
    ogDescription="Belajar ESP32, Arduino, dan IoT dengan tutorial praktis berbahasa Indonesia. Gratis untuk pemula hingga mahir."
>

    {{-- ═══════════════════════════════════ HERO ═══════════════════════════════════ --}}
    <section class="theme-paper border-b-4 border-black hero-grid">
        <div class="max-w-6xl mx-auto px-4 py-10 sm:py-16 lg:py-24">
            <div class="grid lg:grid-cols-2 gap-8 lg:gap-12 items-center">

                <div>
                    <div class="inline-flex items-center gap-2 text-white text-xs font-bold px-3 py-1.5 border-2 border-black mb-4 sm:mb-6" style="background:#FF7A2F; box-shadow:2px 2px 0 #000; text-transform:uppercase; letter-spacing:.05em;">
                        <span>✦</span> Platform Edukasi Pemrograman
                    </div>

                    <h1 class="font-black leading-[1.1] mb-4 sm:mb-6 text-4xl sm:text-5xl lg:text-6xl theme-heading" style="letter-spacing:-0.03em;">
                        Belajar Coding<br>
                        <span class="text-white px-2 sm:px-3 py-1 border-2 border-black text-3xl sm:text-4xl lg:text-5xl" style="background:#2979FF; box-shadow:4px 4px 0 #000; display:inline;">Berbahasa</span><br>
                        Indonesia
                    </h1>

                    <p class="text-sm sm:text-base leading-relaxed mb-6 sm:mb-8 max-w-lg theme-body" style="font-family: 'Inter', sans-serif;">
                        Tutorial ESP32, Arduino, IoT, dan pemrograman — ditulis dengan bahasa yang mudah dipahami oleh developer dan pelajar Indonesia.
                    </p>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('articles.index') }}"
                           class="btn-brutal btn-primary px-7 py-3 text-sm">
                            Mulai Belajar →
                        </a>
                        <a href="{{ route('about') }}"
                           class="btn-brutal btn-outline px-7 py-3 text-sm">
                            Tentang Kami
                        </a>
                    </div>

                    {{-- Stats --}}
                    <div class="flex flex-wrap gap-4 sm:gap-6 mt-6 sm:mt-8 pt-6 sm:pt-8 border-t-2 border-black">
                        <div>
                            <div class="text-2xl font-black" style="color:#2979FF;">{{ \App\Models\Article::published()->count() }}+</div>
                            <div class="text-xs font-semibold uppercase tracking-wider theme-muted">Artikel</div>
                        </div>
                        <div>
                            <div class="text-2xl font-black" style="color:#FF7A2F;">{{ \App\Models\Category::count() }}</div>
                            <div class="text-xs font-semibold uppercase tracking-wider theme-muted">Kategori</div>
                        </div>
                        <div>
                            <div class="text-2xl font-black">100%</div>
                            <div class="text-xs font-semibold uppercase tracking-wider theme-muted">Bahasa Indonesia</div>
                        </div>
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

    {{-- ═══════════════════════════════ FEATURED ARTICLES ══════════════════════════ --}}
    @if($featuredArticles->count())
    <section class="py-10 sm:py-16 border-b-4 border-black theme-paper">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8 sm:mb-10">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-black relative inline-block" style="letter-spacing:-0.02em;">
                        Artikel Unggulan
                        <span class="absolute -bottom-1 left-0 w-full h-1" style="background:#FF7A2F;"></span>
                    </h2>
                    <p class="mt-3 text-sm theme-muted" style="font-family:'Inter',sans-serif;">Artikel pilihan editor terbaik untuk kamu</p>
                </div>
                <a href="{{ route('articles.index') }}" class="btn-brutal btn-outline text-sm px-5 py-2 hidden sm:flex">
                    Lihat Semua →
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
                    Jelajahi Topik
                    <span class="absolute -bottom-1 left-0 w-full h-1" style="background:#2979FF;"></span>
                </h2>
                <p class="mt-3 text-sm theme-muted" style="font-family:'Inter',sans-serif;">Pilih topik yang ingin kamu pelajari</p>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                @foreach($categories as $cat)
                <a href="{{ route('categories.show', $cat->slug) }}"
                   class="group theme-paper border-2 border-black p-3 sm:p-5 text-center transition-all"
                   style="box-shadow: 3px 3px 0 #000;"
                   onmouseenter="this.style.transform='translate(-2px,-2px)';this.style.boxShadow='5px 5px 0 #000';this.style.background='{{ $cat->color }}'"
                   onmouseleave="this.style.transform='';this.style.boxShadow='3px 3px 0 #000';this.style.background=''">
                    <div class="w-10 h-10 rounded-full border-2 border-black mx-auto mb-3" style="background: {{ $cat->color }};"></div>
                    <div class="font-bold text-sm theme-heading group-hover:text-white">{{ $cat->name }}</div>
                    <div class="text-xs mt-1 font-mono theme-muted group-hover:text-white/80">{{ $cat->articles_count }} artikel</div>
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
                        Artikel Terbaru
                        <span class="absolute -bottom-1 left-0 w-full h-1" style="background:#2979FF;"></span>
                    </h2>
                </div>
                <a href="{{ route('articles.index') }}" class="btn-brutal btn-outline text-sm px-5 py-2 hidden sm:flex">Semua Artikel →</a>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($recentArticles as $article)
                    <x-article-card :article="$article" />
                @endforeach
            </div>

            <div class="text-center mt-10">
                <a href="{{ route('articles.index') }}" class="btn-brutal btn-dark px-10 py-3 text-sm">
                    Lihat Semua Artikel →
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
            <h2 class="text-3xl font-black mb-4">Konten Segera Hadir!</h2>
            <p class="mb-8 theme-muted" style="font-family:'Inter',sans-serif;">Kami sedang mempersiapkan artikel-artikel berkualitas tentang ESP32, IoT, dan pemrograman. Tunggu sebentar ya!</p>
            <a href="{{ route('contact') }}" class="btn-brutal btn-primary px-8 py-3">Hubungi Kami</a>
        </div>
    </section>
    @endif

</x-layouts.app>
