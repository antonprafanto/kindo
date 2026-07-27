<x-layouts.app
    :title="'#' . $tag->name . ' — Koding Indonesia'"
    :description="__('ui.articles.tag_meta', ['name' => $tag->name])"
>

    <x-breadcrumb :items="[
        ['label' => __('ui.articles.breadcrumb'), 'url' => route('articles.index')],
        ['label' => '#' . $tag->name],
    ]" />

    <x-locale-article-banner />

    <div class="max-w-6xl mx-auto px-4 py-10">

        <div class="mb-10">
            <div class="inline-flex items-center gap-2 text-xl font-black px-4 py-2 border-2 border-black theme-surface" style="box-shadow: 3px 3px 0 #000;">
                <span style="color:#2979FF;">#</span>{{ $tag->name }}
            </div>
            <p class="mt-3 text-sm theme-muted">{{ __('ui.articles.tag_count', ['count' => $articles->total()]) }}</p>
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
            <div class="text-6xl mb-4">🏷️</div>
            <p class="text-lg font-bold theme-heading mb-2">{{ __('ui.articles.empty_tag_title') }}</p>
            <div class="flex flex-wrap justify-center gap-3 mt-6">
                <a href="{{ route('articles.index') }}" class="btn-brutal btn-primary px-5 py-2 text-sm">{{ __('ui.articles.empty_cta') }}</a>
                <a href="{{ route('search') }}" class="btn-brutal btn-outline px-5 py-2 text-sm">{{ __('ui.articles.search_cta') }}</a>
            </div>
        </div>
        @endif
    </div>

</x-layouts.app>
