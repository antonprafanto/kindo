<x-layouts.app
    :title="__('ui.fsiot.meta_title')"
    :description="__('ui.fsiot.meta_description')"
    :ogTitle="__('ui.fsiot.meta_og_title')"
    :ogDescription="__('ui.fsiot.meta_og_description')"
>

    <x-breadcrumb :items="[
        ['label' => __('ui.fsiot.breadcrumb_learn'), 'url' => route('belajar.fullstack-iot')],
        ['label' => __('ui.fsiot.title')],
    ]" />

    <div class="max-w-4xl mx-auto px-4 py-10 sm:py-16">

        @if(!empty($isAdminPreview))
        <div class="mb-6 p-4 border-2 border-black theme-highlight" style="box-shadow: 3px 3px 0 #000; font-family:'Inter',sans-serif;">
            <p class="text-sm font-bold theme-heading mb-1">{{ __('ui.fsiot.admin_preview_badge') }}</p>
            <p class="text-xs theme-body">{{ __('ui.fsiot.admin_preview_note') }}</p>
        </div>
        @endif

        <div class="relative overflow-hidden theme-paper border-2 border-black mb-10 sm:mb-14" style="box-shadow: 4px 4px 0 #000;">
            <div class="fsiot-hero-bg absolute inset-0 pointer-events-none" aria-hidden="true"></div>
            <div class="relative px-5 py-8 sm:px-8 sm:py-12">
                <p class="inline-flex items-center gap-2 text-white text-xs font-bold px-3 py-1.5 border-2 border-black mb-4" style="background:#FF7A2F; box-shadow:2px 2px 0 #000; text-transform:uppercase; letter-spacing:.05em;">
                    {{ __('ui.fsiot.badge') }}
                </p>
                <h1 class="text-3xl sm:text-4xl font-black mb-4 theme-heading" style="letter-spacing:-0.02em;">
                    {{ __('ui.fsiot.title') }}
                </h1>
                <p class="text-lg theme-body max-w-2xl" style="font-family:'Inter',sans-serif; line-height:1.7;">
                    {{ __('ui.fsiot.intro') }}
                </p>
            </div>
        </div>

        <div class="grid sm:grid-cols-3 gap-4 mb-10 sm:mb-14">
            @foreach([
                ['label' => __('ui.fsiot.stat_modules'), 'value' => '55+', 'hint' => __('ui.fsiot.stat_modules_hint')],
                ['label' => __('ui.fsiot.stat_lang'), 'value' => __('ui.fsiot.stat_lang_value'), 'hint' => __('ui.fsiot.stat_lang_hint')],
                ['label' => __('ui.fsiot.stat_cost'), 'value' => __('ui.fsiot.stat_cost_value'), 'hint' => __('ui.fsiot.stat_cost_hint')],
            ] as $stat)
            <div class="p-4 sm:p-5 theme-paper border-2 border-black" style="box-shadow: 4px 4px 0 #000;">
                <div class="text-2xl font-black" style="color:#2979FF;">{{ $stat['value'] }}</div>
                <div class="text-xs font-bold uppercase tracking-wider theme-heading mt-1">{{ $stat['label'] }}</div>
                <div class="text-xs theme-muted mt-1" style="font-family:'Inter',sans-serif;">{{ $stat['hint'] }}</div>
            </div>
            @endforeach
        </div>

        <div class="p-6 sm:p-8 theme-paper border-2 border-black mb-10 sm:mb-14" style="box-shadow: 4px 4px 0 #000;">
            <h2 class="text-xl font-black mb-3 border-b-2 border-black pb-3">{{ __('ui.fsiot.thread_title') }}</h2>
            <p class="theme-body mb-4" style="font-family:'Inter',sans-serif; line-height:1.75;">
                {{ __('ui.fsiot.thread_body') }}
            </p>
            <p class="text-sm theme-muted" style="font-family:'Inter',sans-serif;">
                {{ __('ui.fsiot.thread_status') }}
            </p>
        </div>

        <div class="mb-10 sm:mb-14">
            <h2 class="text-xl sm:text-2xl font-black mb-6 relative inline-block" style="letter-spacing:-0.02em;">
                {{ __('ui.fsiot.phases_title') }}
                <span class="absolute -bottom-1 left-0 w-full h-1" style="background:#FF7A2F;"></span>
            </h2>
            <ol class="space-y-4" style="font-family:'Inter',sans-serif;">
                @foreach($phases as $i => $phase)
                <li class="p-5 theme-paper border-2 border-black" style="box-shadow: 3px 3px 0 #000;">
                    <div class="flex flex-wrap items-baseline gap-2 mb-2">
                        <span class="text-xs font-bold px-2 py-0.5 border-2 border-black text-white" style="background:#2D3748;">{{ $i + 1 }}</span>
                        <span class="text-xs font-mono font-bold" style="color:#2979FF;">{{ $phase['code'] }}</span>
                        <span class="font-black theme-heading">{{ $phase['title'] }}</span>
                    </div>
                    <p class="text-sm theme-body mb-2" style="line-height:1.65;">{{ $phase['blurb'] }}</p>
                    <p class="text-xs theme-muted font-mono">{{ $phase['modules'] }}</p>
                </li>
                @endforeach
            </ol>
        </div>

        <div class="p-6 sm:p-8 border-2 border-black mb-10 sm:mb-14 text-white" style="background:#2D3748; box-shadow: 4px 4px 0 #000;">
            <h2 class="text-xl font-black mb-3 border-b border-gray-600 pb-3">{{ __('ui.fsiot.support_title') }}</h2>
            <p class="mb-6 text-sm leading-relaxed" style="color:#CBD5E0; font-family:'Inter',sans-serif;">
                {{ __('ui.fsiot.support_body') }}
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

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('articles.index') }}" class="btn-brutal btn-outline px-6 py-3 text-sm">{{ __('ui.fsiot.browse_articles') }}</a>
            <a href="{{ route('newsletter') }}" class="btn-brutal btn-primary px-6 py-3 text-sm">{{ __('ui.fsiot.newsletter') }}</a>
        </div>
    </div>

</x-layouts.app>
