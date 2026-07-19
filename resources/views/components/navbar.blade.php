<nav
    x-data="{ open: false }"
    class="sticky top-0 z-50 theme-paper border-b-[3px] border-black"
    style="box-shadow: 0 3px 0 #000;"
>
    <div class="max-w-6xl mx-auto px-4">
        <div class="flex items-center justify-between h-14 sm:h-16">

            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex items-center gap-2 sm:gap-3 no-underline">
                <x-logo size="md" class="border-2 border-black" style="box-shadow: 2px 2px 0 #000;" />
                <span class="font-bold theme-heading text-base sm:text-lg hidden sm:inline" style="letter-spacing:-0.02em;">Koding Indonesia</span>
                <span class="w-2 h-2 rounded-full" style="background:#FF7A2F; border: 2px solid #000; display:inline-block;"></span>
            </a>

            {{-- Desktop Nav --}}
            <div class="hidden md:flex items-center gap-1">
                <a href="{{ route('articles.index') }}"
                   class="px-4 py-2 font-semibold text-sm hover:bg-black hover:text-white transition-colors {{ request()->routeIs('articles.*') ? 'bg-black text-white' : '' }}">
                    Artikel
                </a>

                {{-- Kategori Dropdown --}}
                <div class="relative" x-data="{ catOpen: false }">
                    <button
                        @click="catOpen = !catOpen"
                        @keydown.escape="catOpen = false"
                        :aria-expanded="catOpen.toString()"
                        aria-haspopup="true"
                        class="px-4 py-2 font-semibold text-sm flex items-center gap-1 hover:bg-black hover:text-white transition-colors"
                    >
                        Kategori
                        <svg class="w-3.5 h-3.5" :class="catOpen ? 'rotate-180' : ''" style="transition: transform .2s" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-linejoin="miter" d="M6 9l6 6 6-6"/></svg>
                    </button>
                    <div
                        x-show="catOpen"
                        @click.away="catOpen = false"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="absolute top-full left-0 mt-1 w-52 theme-paper border-2 border-black z-50"
                        style="box-shadow: 4px 4px 0 #000;"
                    >
                        @foreach($navCategories as $cat)
                        <a
                            href="{{ route('categories.show', $cat->slug) }}"
                            class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium hover:bg-black hover:text-white border-b border-black/10 dark:border-white/10 last:border-0"
                            @click="catOpen = false"
                        >
                            <span class="w-2.5 h-2.5 rounded-full border border-black flex-shrink-0" style="background: {{ $cat->color }};"></span>
                            {{ $cat->name }}
                        </a>
                        @endforeach
                        <a href="{{ route('articles.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm font-bold hover:bg-[#2979FF] hover:text-white border-t-2 border-black">
                            Semua Artikel →
                        </a>
                    </div>
                </div>

                <a href="{{ route('about') }}"
                   class="px-4 py-2 font-semibold text-sm hover:bg-black hover:text-white transition-colors {{ request()->routeIs('about') ? 'bg-black text-white' : '' }}">
                    Tentang
                </a>

                <a href="{{ route('authors.index') }}"
                   class="px-4 py-2 font-semibold text-sm hover:bg-black hover:text-white transition-colors {{ request()->routeIs('authors.*') ? 'bg-black text-white' : '' }}">
                    Penulis
                </a>

                <a href="{{ route('contact') }}"
                   class="px-4 py-2 font-semibold text-sm hover:bg-black hover:text-white transition-colors {{ request()->routeIs('contact') ? 'bg-black text-white' : '' }}">
                    Kontak
                </a>

                <a href="{{ route('search') }}"
                   class="ml-2 px-3 py-2 border-2 border-black hover:bg-black hover:text-white transition-colors"
                   style="box-shadow: 2px 2px 0 #000;"
                   aria-label="Cari artikel"
                   title="Cari artikel"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                    </svg>
                </a>

                <x-theme-toggle />
            </div>

            {{-- Mobile: Search + Theme + Hamburger --}}
            <div class="flex items-center gap-2 md:hidden">
                <x-theme-toggle />
                <a href="{{ route('search') }}" class="p-2 border-2 border-black" aria-label="Cari artikel">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                    </svg>
                </a>
                <button @click="open = !open" class="p-2 border-2 border-black" aria-label="Menu" :aria-expanded="open.toString()">
                    <svg x-show="!open" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="square" d="M3 6h18M3 12h18M3 18h18"/>
                    </svg>
                    <svg x-show="open" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="square" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile Menu --}}
        <div x-show="open" x-transition class="md:hidden border-t-2 border-black pb-4">
            <div class="flex flex-col pt-2">
                <a href="{{ route('articles.index') }}" @click="open=false" class="px-4 py-3 font-semibold text-sm border-b border-black/10 dark:border-white/10 hover:bg-black hover:text-white">Artikel</a>
                <div class="border-b border-black/10 dark:border-white/10">
                    <div class="px-4 py-2 text-xs font-bold uppercase tracking-widest theme-muted mt-1">Kategori</div>
                    @foreach($navCategories as $cat)
                    <a href="{{ route('categories.show', $cat->slug) }}" @click="open=false" class="flex items-center gap-2 px-6 py-2 text-sm hover:bg-black hover:text-white">
                        <span class="w-2 h-2 rounded-full" style="background: {{ $cat->color }};"></span>
                        {{ $cat->name }}
                    </a>
                    @endforeach
                </div>
                <a href="{{ route('about') }}" @click="open=false" class="px-4 py-3 font-semibold text-sm border-b border-black/10 dark:border-white/10 hover:bg-black hover:text-white">Tentang</a>
                <a href="{{ route('authors.index') }}" @click="open=false" class="px-4 py-3 font-semibold text-sm border-b border-black/10 dark:border-white/10 hover:bg-black hover:text-white">Penulis</a>
                <a href="{{ route('contact') }}" @click="open=false" class="px-4 py-3 font-semibold text-sm hover:bg-black hover:text-white">Kontak</a>
            </div>
        </div>
    </div>
</nav>
