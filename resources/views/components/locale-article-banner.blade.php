@props(['article' => null])

{{-- On a single-article page, suppress this notice once that article has a full English body. --}}
@if(app()->getLocale() === 'en' && (!$article || !$article->has_english))
<aside
    class="border-b-2 border-black theme-highlight"
    role="status"
    aria-label="{{ __('ui.articles.locale_banner') }}"
>
    <div class="max-w-6xl mx-auto px-4 py-3 sm:py-3.5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-l-4"
         style="border-left-color:#FF7A2F; font-family:'Inter',sans-serif;">
        <p class="theme-heading text-sm font-semibold m-0 leading-snug">
            {{ __('ui.articles.locale_banner') }}
        </p>
        <a
            href="{{ route('belajar.fullstack-iot') }}"
            class="btn-brutal btn-outline text-xs px-4 py-2 shrink-0 inline-flex no-underline self-start sm:self-auto"
        >
            {{ __('ui.articles.locale_banner_cta') }}
        </a>
    </div>
</aside>
@endif
