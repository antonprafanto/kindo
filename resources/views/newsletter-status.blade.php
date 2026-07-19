<x-layouts.app title="{{ $title }} — Koding Indonesia">

    <div class="max-w-xl mx-auto px-4 py-20 text-center">
        <div class="card-brutal p-8 sm:p-12">
            @if($success)
            <div class="text-5xl mb-4">✓</div>
            @else
            <div class="text-5xl mb-4">!</div>
            @endif
            <h1 class="text-2xl sm:text-3xl font-black mb-4 theme-heading">{{ $title }}</h1>
            <p class="theme-muted mb-8">{{ $message }}</p>
            <div class="flex flex-wrap justify-center gap-3">
                @if($success)
                <a href="{{ route('articles.index') }}" class="btn-brutal btn-primary inline-block px-6 py-3 text-sm">
                    Lihat Artikel →
                </a>
                @else
                <a href="{{ route('newsletter') }}" class="btn-brutal btn-primary inline-block px-6 py-3 text-sm">
                    Daftar Newsletter →
                </a>
                @endif
                <a href="{{ route('home') }}" class="btn-brutal btn-outline inline-block px-6 py-3 text-sm">
                    Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>

</x-layouts.app>
