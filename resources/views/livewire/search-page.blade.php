<div>
    <x-breadcrumb :items="[['label' => 'Cari Artikel']]" />

    <div class="max-w-6xl mx-auto px-4 py-10">

        {{-- Search Input --}}
        <div class="mb-10">
            <h1 class="text-3xl font-black mb-6" style="letter-spacing:-0.02em;">Cari Artikel</h1>
            <div class="relative max-w-2xl">
                <input
                    wire:model.live.debounce.400ms="query"
                    type="search"
                    placeholder="Cari ESP32, IoT, Arduino, PHP..."
                    class="input-brutal pr-14 py-4 text-base"
                    style="font-family: 'Inter', sans-serif;"
                    autofocus
                >
                <div class="absolute right-4 top-1/2 -translate-y-1/2">
                    <div wire:loading wire:target="query">
                        <svg class="w-5 h-5 animate-spin" style="color:#2979FF;" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                    </div>
                    <div wire:loading.remove wire:target="query">
                        <svg class="w-5 h-5" style="color:#718096;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Results --}}
        @if(strlen($query) >= 2)
            <div class="mb-4 text-sm font-semibold" style="color:#718096;">
                Ditemukan <strong>{{ $results->total() }}</strong> artikel untuk "<strong>{{ $query }}</strong>"
            </div>

            @if($results->count())
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6" wire:loading.class="opacity-60">
                @foreach($results as $article)
                    <x-article-card :article="$article" />
                @endforeach
            </div>
            <div class="mt-8">{{ $results->links() }}</div>
            @else
            <div class="py-20 text-center border-2 border-black" style="background:#F7F7F5; box-shadow: 4px 4px 0 #000;">
                <div class="text-6xl mb-4">🔍</div>
                <h2 class="text-xl font-bold mb-2">Tidak ditemukan</h2>
                <p style="color:#718096;">Coba kata kunci lain seperti "ESP32", "WiFi", atau "Python"</p>
            </div>
            @endif
        @elseif(strlen($query) > 0)
            <div class="py-10 text-center" style="color:#718096;">Ketik minimal 2 karakter untuk mulai mencari...</div>
        @else
            <div>
                <h2 class="text-lg font-bold mb-6" style="color:#718096;">Artikel Terbaru</h2>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($results as $article)
                        <x-article-card :article="$article" />
                    @endforeach
                </div>
                <div class="mt-8">{{ $results->links() }}</div>
            </div>
        @endif

    </div>
</div>
