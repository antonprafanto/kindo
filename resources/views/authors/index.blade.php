<x-layouts.app
    title="Penulis — Koding Indonesia"
    description="Kenali kontributor dan penulis tutorial di Koding Indonesia. Baca portofolio dan artikel mereka."
    :canonical="route('authors.index')"
>

    <x-breadcrumb :items="[
        ['label' => 'Penulis'],
    ]" />

    <div class="max-w-6xl mx-auto px-4 py-10">

        <div class="mb-10 pb-8 border-b-2 border-black">
            <h1 class="text-3xl font-black theme-heading mb-2" style="letter-spacing:-0.02em;">Penulis</h1>
            <p class="text-sm max-w-xl theme-muted" style="font-family:'Inter',sans-serif;">
                Portofolio publik kontributor Koding Indonesia — bio, keahlian, dan artikel yang sudah dipublikasikan.
            </p>
        </div>

        @if($authors->count())
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
            @foreach($authors as $author)
            <a href="{{ route('authors.show', $author->slug) }}"
               class="block border-2 border-black theme-surface p-5 no-underline hover:-translate-y-0.5 transition-transform"
               style="box-shadow: 4px 4px 0 #000;">
                <div class="flex items-center gap-4 mb-4">
                    @if($author->avatar_url)
                        <img src="{{ $author->avatar_url }}" alt="{{ $author->name }}"
                             class="w-14 h-14 rounded-full border-2 border-black object-cover flex-shrink-0">
                    @else
                        <div class="w-14 h-14 rounded-full border-2 border-black flex-shrink-0 flex items-center justify-center font-bold text-white text-lg"
                             style="background:#2979FF;">
                            {{ $author->initial }}
                        </div>
                    @endif
                    <div class="min-w-0">
                        <div class="font-bold text-base theme-heading truncate">{{ $author->name }}</div>
                        @if($author->expertise)
                        <div class="text-xs theme-muted truncate">{{ $author->expertise }}</div>
                        @else
                        <div class="text-xs theme-muted">Penulis · Koding Indonesia</div>
                        @endif
                    </div>
                </div>
                @if($author->bio)
                <p class="text-sm theme-muted line-clamp-2 mb-3" style="font-family:'Inter',sans-serif;">
                    {{ Str::limit($author->bio, 120) }}
                </p>
                @endif
                <div class="text-xs font-mono font-bold" style="color:#2979FF;">
                    {{ $author->articles_count }} artikel →
                </div>
            </a>
            @endforeach
        </div>
        {{ $authors->links() }}
        @else
        <div class="py-24 text-center">
            <h2 class="text-xl font-bold mb-2 theme-heading">Belum ada penulis</h2>
            <p class="theme-muted mb-6">Belum ada kontributor dengan artikel yang dipublikasikan.</p>
            <a href="{{ route('contributor.apply') }}" class="btn-brutal btn-primary inline-block py-3 px-6 text-sm no-underline">
                Menjadi Kontributor →
            </a>
        </div>
        @endif
    </div>

</x-layouts.app>
