<x-layouts.app
    :title="$category->name . ' — Koding Indonesia'"
    :description="$category->description ?? 'Artikel tentang ' . $category->name . ' di Koding Indonesia'"
>

    <x-breadcrumb :items="[
        ['label' => 'Artikel', 'url' => route('articles.index')],
        ['label' => $category->name],
    ]" />

    <div class="max-w-6xl mx-auto px-4 py-10">

        {{-- Header --}}
        <div class="flex items-start gap-5 mb-10 pb-8 border-b-2 border-black">
            <div class="w-16 h-16 rounded-full border-2 border-black flex-shrink-0" style="background: {{ $category->color }};"></div>
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <h1 class="text-3xl font-black" style="letter-spacing:-0.02em;">{{ $category->name }}</h1>
                    <span class="text-xs font-mono px-2 py-1 border-2 border-black" style="background:#f5f5f5;">{{ $articles->total() }} artikel</span>
                </div>
                @if($category->description)
                <p class="text-sm max-w-xl" style="color:#4A5568; font-family:'Inter',sans-serif;">{{ $category->description }}</p>
                @endif
            </div>
        </div>

        @if($articles->count())
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
            @foreach($articles as $article)
                <x-article-card :article="$article" />
            @endforeach
        </div>
        {{ $articles->links() }}
        @else
        <div class="py-24 text-center">
            <div class="text-6xl mb-4">📭</div>
            <h2 class="text-xl font-bold mb-2">Belum ada artikel</h2>
            <p style="color:#718096;">Belum ada artikel yang dipublikasikan di kategori ini.</p>
        </div>
        @endif
    </div>

</x-layouts.app>
