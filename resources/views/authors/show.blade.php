@php
    $metaDescription = $author->bio
        ? Str::limit(strip_tags($author->bio), 155)
        : 'Portofolio ' . $author->name . ' — kontributor Koding Indonesia' . ($author->expertise ? ' · ' . $author->expertise : '') . '.';
    $externalWorks = collect($author->external_works ?? [])->filter(fn ($w) => filled($w['title'] ?? null) && filled($w['url'] ?? null));
@endphp

<x-layouts.app
    :title="$author->name . ' — Penulis Koding Indonesia'"
    :description="$metaDescription"
    :ogImage="$author->avatar_url"
    :ogImageAlt="$author->name . ' — Penulis di Koding Indonesia'"
    :canonical="route('authors.show', $author->slug)"
>

@push('schema')
@php
    $personSchema = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Person',
        'name' => $author->name,
        'url' => route('authors.show', $author->slug),
        'description' => $author->bio ? Str::limit(strip_tags($author->bio), 300) : null,
        'image' => $author->avatar_url,
        'jobTitle' => $author->expertise ?: 'Penulis',
        'worksFor' => [
            '@type' => 'Organization',
            'name' => 'Koding Indonesia',
            'url' => url('/'),
        ],
        'sameAs' => array_values(array_filter([
            $author->github_url,
            $author->linkedin_url,
            $author->website_url,
        ])) ?: null,
        'mainEntityOfPage' => route('authors.show', $author->slug),
    ], fn ($v) => $v !== null && $v !== []);
@endphp
<script type="application/ld+json">
{!! json_encode($personSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>
@endpush

    <x-breadcrumb :items="[
        ['label' => 'Penulis', 'url' => route('authors.index')],
        ['label' => $author->name],
    ]" />

    <div class="max-w-6xl mx-auto px-4 py-10">

        {{-- Profile header --}}
        <div class="border-2 border-black theme-surface p-6 sm:p-8 mb-10" style="box-shadow: 5px 5px 0 #000;">
            <div class="flex flex-col sm:flex-row items-start gap-5 sm:gap-6">
                @if($author->avatar_url)
                    <img src="{{ $author->avatar_url }}" alt="{{ $author->name }}"
                         class="w-24 h-24 sm:w-28 sm:h-28 rounded-full border-2 border-black object-cover flex-shrink-0"
                         style="box-shadow: 3px 3px 0 #000;">
                @else
                    <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-full border-2 border-black flex-shrink-0 flex items-center justify-center font-black text-white text-3xl"
                         style="background:#2979FF; box-shadow: 3px 3px 0 #000;">
                        {{ $author->initial }}
                    </div>
                @endif

                <div class="min-w-0 flex-1">
                    <h1 class="text-2xl sm:text-3xl font-black theme-heading mb-1" style="letter-spacing:-0.02em;">
                        {{ $author->name }}
                    </h1>
                    @if($author->expertise)
                    <p class="text-sm font-bold mb-3" style="color:#2979FF;">{{ $author->expertise }}</p>
                    @else
                    <p class="text-sm theme-muted mb-3">Penulis · Koding Indonesia</p>
                    @endif

                    @if($author->bio)
                    <p class="text-sm leading-relaxed theme-muted mb-4 max-w-2xl" style="font-family:'Inter',sans-serif;">
                        {{ $author->bio }}
                    </p>
                    @endif

                    @if($author->github_url || $author->linkedin_url || $author->website_url)
                    <div class="flex flex-wrap items-center gap-2">
                        @if($author->github_url)
                        <a href="{{ $author->github_url }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 border-2 border-black theme-paper hover:bg-black hover:text-white transition-colors no-underline"
                           style="box-shadow: 2px 2px 0 #000;">
                            GitHub
                        </a>
                        @endif
                        @if($author->linkedin_url)
                        <a href="{{ $author->linkedin_url }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 border-2 border-black theme-paper hover:bg-black hover:text-white transition-colors no-underline"
                           style="box-shadow: 2px 2px 0 #000;">
                            LinkedIn
                        </a>
                        @endif
                        @if($author->website_url)
                        <a href="{{ $author->website_url }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 border-2 border-black theme-paper hover:bg-black hover:text-white transition-colors no-underline"
                           style="box-shadow: 2px 2px 0 #000;">
                            Website
                        </a>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- External works --}}
        @if($externalWorks->isNotEmpty())
        <section class="mb-12">
            <h2 class="text-xl font-black theme-heading mb-5" style="letter-spacing:-0.02em;">Karya Eksternal</h2>
            <ul class="space-y-3">
                @foreach($externalWorks as $work)
                <li class="border-2 border-black theme-surface p-4" style="box-shadow: 3px 3px 0 #000;">
                    <a href="{{ $work['url'] }}" target="_blank" rel="noopener noreferrer"
                       class="font-bold text-sm theme-heading hover:text-[#2979FF] no-underline">
                        {{ $work['title'] }} →
                    </a>
                    @if(!empty($work['description']))
                    <p class="text-sm theme-muted mt-1" style="font-family:'Inter',sans-serif;">{{ $work['description'] }}</p>
                    @endif
                </li>
                @endforeach
            </ul>
        </section>
        @endif

        {{-- Articles --}}
        <section>
            <div class="flex items-center gap-3 mb-6">
                <h2 class="text-xl font-black theme-heading" style="letter-spacing:-0.02em;">Artikel</h2>
                <span class="text-xs font-mono px-2 py-1 border-2 border-black theme-surface">{{ $articles->total() }}</span>
            </div>

            @if($articles->count())
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
                @foreach($articles as $article)
                    <x-article-card :article="$article" />
                @endforeach
            </div>
            {{ $articles->links() }}
            @else
            <div class="py-16 text-center border-2 border-black theme-surface">
                <h3 class="text-lg font-bold mb-2 theme-heading">Belum ada artikel</h3>
                <p class="theme-muted text-sm">Penulis ini belum memiliki artikel yang dipublikasikan.</p>
            </div>
            @endif
        </section>
    </div>

</x-layouts.app>
