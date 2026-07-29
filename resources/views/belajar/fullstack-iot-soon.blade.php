<x-layouts.app
    :title="__('ui.fsiot.soon_meta_title')"
    :description="__('ui.fsiot.soon_meta_description')"
    :ogTitle="__('ui.fsiot.soon_meta_og_title')"
    :ogDescription="__('ui.fsiot.soon_meta_og_description')"
>

    <x-breadcrumb :items="[
        ['label' => __('ui.fsiot.breadcrumb_learn'), 'url' => route('belajar.fullstack-iot')],
        ['label' => __('ui.fsiot.title')],
    ]" />

    <div class="max-w-4xl mx-auto px-4 py-10 sm:py-16">

        <div class="relative overflow-hidden theme-paper border-2 border-black mb-10 sm:mb-14" style="box-shadow: 4px 4px 0 #000;">
            <div class="fsiot-hero-bg absolute inset-0 pointer-events-none" aria-hidden="true"></div>
            <div class="relative px-5 py-10 sm:px-8 sm:py-14">
                <p class="inline-flex items-center gap-2 text-white text-xs font-bold px-3 py-1.5 border-2 border-black mb-4" style="background:#2D3748; box-shadow:2px 2px 0 #000; text-transform:uppercase; letter-spacing:.05em;">
                    {{ __('ui.fsiot.soon_badge') }}
                </p>
                <h1 class="text-3xl sm:text-4xl font-black mb-4 theme-heading" style="letter-spacing:-0.02em;">
                    {{ __('ui.fsiot.title') }}
                </h1>
                <p class="text-lg theme-body max-w-2xl mb-3" style="font-family:'Inter',sans-serif; line-height:1.7;">
                    {{ __('ui.fsiot.soon_headline') }}
                </p>
                <p class="text-sm theme-muted max-w-2xl" style="font-family:'Inter',sans-serif; line-height:1.7;">
                    {{ __('ui.fsiot.soon_body') }}
                </p>
            </div>
        </div>

        <div class="p-6 sm:p-8 theme-paper border-2 border-black mb-10 sm:mb-14" style="box-shadow: 4px 4px 0 #000;">
            <h2 class="text-xl font-black mb-3 border-b-2 border-black pb-3">{{ __('ui.fsiot.soon_board_title') }}</h2>
            <p class="theme-body" style="font-family:'Inter',sans-serif; line-height:1.75;">
                {{ __('ui.fsiot.soon_board_body') }}
            </p>
        </div>

        <div class="flex flex-wrap gap-3 mb-10 sm:mb-14">
            <a href="{{ route('newsletter') }}" class="btn-brutal btn-primary px-6 py-3 text-sm">{{ __('ui.fsiot.soon_newsletter') }}</a>
            <a href="{{ route('articles.index') }}" class="btn-brutal btn-outline px-6 py-3 text-sm">{{ __('ui.fsiot.browse_articles') }}</a>
        </div>

        <div class="p-6 sm:p-8 border-2 border-black text-white" style="background:#2D3748; box-shadow: 4px 4px 0 #000;">
            <h2 class="text-xl font-black mb-3 border-b border-gray-600 pb-3">{{ __('ui.fsiot.support_title') }}</h2>
            <p class="mb-6 text-sm leading-relaxed" style="color:#CBD5E0; font-family:'Inter',sans-serif;">
                {{ __('ui.fsiot.soon_support_body') }}
            </p>
            <a
                href="{{ $trakteerUrl }}"
                target="_blank"
                rel="noopener noreferrer"
                class="btn-brutal btn-primary px-8 py-3 text-sm inline-flex no-underline"
            >
                {{ __('ui.fsiot.support_cta') }}
            </a>
            <p class="mt-4 text-xs" style="color:#718096; font-family:'Inter',sans-serif;">
                {{ __('ui.fsiot.support_note') }}
            </p>
        </div>
    </div>

</x-layouts.app>
