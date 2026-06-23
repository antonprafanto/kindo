<x-layouts.app title="Semua Artikel — Koding Indonesia">

    <x-breadcrumb :items="[['label' => 'Artikel']]" />

    <div class="max-w-6xl mx-auto px-4 py-10">

        {{-- Header + Filter --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
            <h1 class="text-3xl font-black" style="letter-spacing:-0.02em;">
                Semua Artikel
                <span class="ml-2 text-base font-mono font-normal theme-muted">({{ $articles->total() }})</span>
            </h1>

            {{-- Sort --}}
            <form method="GET" action="{{ route('articles.index') }}" class="flex items-center gap-2">
                @if(request('kategori'))
                    <input type="hidden" name="kategori" value="{{ request('kategori') }}">
                @endif
                <label class="text-xs font-bold uppercase tracking-wider theme-muted">Urutkan:</label>
                <select name="sort" onchange="this.form.submit()"
                        class="input-brutal py-2 pl-3 pr-8 text-sm w-auto"
                        style="box-shadow: 2px 2px 0 #000;">
                    <option value="terbaru" {{ $sort === 'terbaru' ? 'selected' : '' }}>Terbaru</option>
                    <option value="populer" {{ $sort === 'populer' ? 'selected' : '' }}>Paling Populer</option>
                </select>
            </form>
        </div>

        {{-- Category Tabs --}}
        <div class="flex flex-wrap gap-2 mb-8 pb-6 border-b-2 border-black">
            <a href="{{ route('articles.index', ['sort' => $sort]) }}"
               class="text-xs font-bold uppercase tracking-wider px-4 py-2 border-2 border-black transition-all {{ !request('kategori') ? 'bg-black text-white' : 'theme-paper hover:bg-black hover:text-white' }}"
               style="{{ !request('kategori') ? 'box-shadow: none;' : 'box-shadow: 2px 2px 0 #000;' }}">
                Semua
            </a>
            @foreach($categories as $cat)
            <a href="{{ route('articles.index', ['kategori' => $cat->slug, 'sort' => $sort]) }}"
               class="text-xs font-bold uppercase tracking-wider px-4 py-2 border-2 border-black transition-all {{ request('kategori') === $cat->slug ? 'text-white' : 'theme-paper hover:bg-black hover:text-white' }}"
               style="{{ request('kategori') === $cat->slug ? 'background:'.$cat->color.'; box-shadow: none;' : 'box-shadow: 2px 2px 0 #000;' }}">
                {{ $cat->name }}
            </a>
            @endforeach
        </div>

        {{-- Grid --}}
        @if($articles->count())
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
            @foreach($articles as $article)
                <x-article-card :article="$article" />
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-8">
            {{ $articles->links() }}
        </div>
        @else
        <div class="py-24 text-center">
            <div class="text-6xl mb-4">📭</div>
            <h2 class="text-xl font-bold mb-2">Belum ada artikel</h2>
            <p style="color:#718096;">Kategori ini belum memiliki artikel yang dipublikasikan.</p>
            <a href="{{ route('articles.index') }}" class="btn-brutal btn-primary mt-6 px-8 py-3 text-sm inline-flex">Lihat Semua Artikel</a>
        </div>
        @endif
    </div>

</x-layouts.app>
