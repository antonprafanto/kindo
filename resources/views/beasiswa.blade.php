<x-layouts.app
    :title="__('ui.beasiswa.meta_title')"
    :description="__('ui.beasiswa.meta_description')"
    :ogTitle="__('ui.beasiswa.meta_og_title')"
    :ogDescription="__('ui.beasiswa.meta_og_description')"
>

    <x-breadcrumb :items="[['label' => __('ui.beasiswa.breadcrumb')]]" />

    <div class="beasiswa-page max-w-4xl mx-auto px-4 py-10 sm:py-16">

        {{-- Hero --}}
        <div class="beasiswa-hero relative overflow-hidden theme-paper border-2 border-black mb-8 sm:mb-12" style="box-shadow: 6px 6px 0 #000;">
            <div class="beasiswa-hero-pattern absolute inset-0 pointer-events-none" aria-hidden="true"></div>
            <div class="relative px-5 py-10 sm:px-8 sm:py-14">
                <div class="flex flex-wrap items-center gap-2 mb-5">
                    <span class="beasiswa-free-badge">{{ __('ui.beasiswa.badge_free') }}</span>
                    <span class="beasiswa-open-badge">
                        <span class="beasiswa-open-dot" aria-hidden="true"></span>
                        {{ __('ui.beasiswa.badge_open') }}
                    </span>
                </div>
                <h1 class="text-3xl sm:text-5xl font-black mb-4 theme-heading max-w-2xl" style="letter-spacing:-0.03em; line-height:1.1;">
                    {{ __('ui.beasiswa.heading') }}
                </h1>
                <p class="text-base sm:text-lg theme-body max-w-2xl mb-8" style="font-family:'Inter',sans-serif; line-height:1.7;">
                    {{ __('ui.beasiswa.intro') }}
                </p>
                <div class="flex flex-wrap gap-3">
                    <a
                        href="#aws-ai-academy"
                        class="btn-brutal btn-primary px-6 py-3 text-sm inline-flex items-center gap-2"
                    >
                        {{ __('ui.beasiswa.hero_cta') }}
                        <span aria-hidden="true">↓</span>
                    </a>
                    <a href="{{ route('articles.index') }}" class="btn-brutal btn-outline px-6 py-3 text-sm">
                        {{ __('ui.beasiswa.hero_secondary') }}
                    </a>
                </div>
            </div>
        </div>

        {{-- Quick facts --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 mb-10 sm:mb-14">
            @foreach([
                ['label' => __('ui.beasiswa.fact_cost_label'), 'value' => __('ui.beasiswa.fact_cost_value'), 'accent' => '#1565C0'],
                ['label' => __('ui.beasiswa.fact_age_label'), 'value' => __('ui.beasiswa.fact_age_value'), 'accent' => '#FF7A2F'],
                ['label' => __('ui.beasiswa.fact_mode_label'), 'value' => __('ui.beasiswa.fact_mode_value'), 'accent' => '#2D3748'],
            ] as $fact)
            <div class="beasiswa-fact p-4 sm:p-5 theme-paper border-2 border-black" style="box-shadow: 4px 4px 0 #000; border-top: 4px solid {{ $fact['accent'] }};">
                <div class="text-xs font-bold uppercase tracking-wider theme-muted mb-1">{{ $fact['label'] }}</div>
                <div class="text-lg sm:text-xl font-black theme-heading">{{ $fact['value'] }}</div>
            </div>
            @endforeach
        </div>

        {{-- Featured scholarship: AWS AI Academy --}}
        <article id="aws-ai-academy" class="beasiswa-card theme-paper border-2 border-black mb-10 sm:mb-14 scroll-mt-24" style="box-shadow: 6px 6px 0 #000;">
            <div class="beasiswa-card-accent" aria-hidden="true"></div>
            <div class="p-6 sm:p-8">
                <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest mb-2" style="color:#1565C0;">
                            {{ __('ui.beasiswa.aws_provider') }}
                        </p>
                        <h2 class="text-2xl sm:text-3xl font-black theme-heading" style="letter-spacing:-0.02em;">
                            {{ __('ui.beasiswa.aws_title') }}
                        </h2>
                    </div>
                    <span class="inline-flex items-center px-3 py-1.5 text-xs font-black uppercase tracking-wide border-2 border-black" style="background:#EBF4FF; color:#1565C0; box-shadow: 2px 2px 0 #000;">
                        {{ __('ui.beasiswa.aws_tag') }}
                    </span>
                </div>

                <p class="theme-body mb-6 max-w-2xl" style="font-family:'Inter',sans-serif; line-height:1.75;">
                    {{ __('ui.beasiswa.aws_body') }}
                </p>

                <div class="grid sm:grid-cols-2 gap-3 mb-8">
                    @foreach([
                        __('ui.beasiswa.aws_req_1'),
                        __('ui.beasiswa.aws_req_2'),
                        __('ui.beasiswa.aws_req_3'),
                        __('ui.beasiswa.aws_req_4'),
                    ] as $req)
                    <div class="flex items-start gap-2.5 text-sm theme-body" style="font-family:'Inter',sans-serif;">
                        <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center border-2 border-black text-xs font-black" style="background:#FFD600;">✓</span>
                        <span>{{ $req }}</span>
                    </div>
                    @endforeach
                </div>

                <h3 class="text-sm font-black uppercase tracking-widest mb-4 border-b-2 border-black pb-2">
                    {{ __('ui.beasiswa.aws_stages_title') }}
                </h3>
                <ol class="space-y-3 mb-8">
                    @foreach([
                        ['num' => '1', 'title' => __('ui.beasiswa.aws_stage1_title'), 'body' => __('ui.beasiswa.aws_stage1_body'), 'deadline' => __('ui.beasiswa.aws_stage1_deadline'), 'status' => 'closed', 'status_label' => __('ui.beasiswa.aws_stage_status_closed')],
                        ['num' => '2', 'title' => __('ui.beasiswa.aws_stage2_title'), 'body' => __('ui.beasiswa.aws_stage2_body'), 'deadline' => __('ui.beasiswa.aws_stage2_deadline'), 'status' => 'active', 'status_label' => __('ui.beasiswa.aws_stage_status_active')],
                        ['num' => '3', 'title' => __('ui.beasiswa.aws_stage3_title'), 'body' => __('ui.beasiswa.aws_stage3_body'), 'deadline' => __('ui.beasiswa.aws_stage3_deadline'), 'status' => 'open', 'status_label' => __('ui.beasiswa.aws_stage_status_open')],
                    ] as $stage)
                    <li class="beasiswa-stage flex gap-3 sm:gap-4 p-3 sm:p-4 border-2 border-black theme-highlight">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center border-2 border-black text-sm font-black bg-white dark:bg-black dark:text-white" style="box-shadow: 2px 2px 0 #000;">
                            {{ $stage['num'] }}
                        </span>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-0.5">
                                <div class="font-black theme-heading text-sm sm:text-base">{{ $stage['title'] }}</div>
                                <span class="beasiswa-stage-status beasiswa-stage-status--{{ $stage['status'] }}">{{ $stage['status_label'] }}</span>
                            </div>
                            <p class="text-sm theme-body mt-0.5" style="font-family:'Inter',sans-serif; line-height:1.55;">{{ $stage['body'] }}</p>
                            <p class="beasiswa-deadline text-xs font-bold mt-1.5">{{ $stage['deadline'] }}</p>
                        </div>
                    </li>
                    @endforeach
                </ol>

                <div class="flex flex-col sm:flex-row sm:items-center gap-4 p-4 sm:p-5 border-2 border-black" style="background:#FFD600; box-shadow: 4px 4px 0 #000;">
                    <div class="flex-1 min-w-0">
                        <p class="font-black text-sm sm:text-base text-black">{{ __('ui.beasiswa.aws_cta_label') }}</p>
                        <p class="text-xs sm:text-sm text-black/70 mt-0.5" style="font-family:'Inter',sans-serif;">{{ __('ui.beasiswa.aws_cta_note') }}</p>
                    </div>
                    <a
                        href="{{ $awsRegisterUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="btn-brutal btn-dark px-6 py-3 text-sm inline-flex items-center justify-center gap-2 shrink-0 no-underline"
                    >
                        {{ __('ui.beasiswa.aws_cta') }}
                        <span aria-hidden="true">↗</span>
                    </a>
                </div>

                <p class="mt-4 text-xs theme-muted" style="font-family:'Inter',sans-serif;">
                    {{ __('ui.beasiswa.aws_disclaimer') }}
                    · {{ __('ui.beasiswa.aws_updated') }}
                </p>
            </div>
        </article>

        {{-- Bridge to learning content --}}
        <div class="p-6 sm:p-8 theme-paper border-2 border-black mb-10 sm:mb-14" style="box-shadow: 4px 4px 0 #000;">
            <h2 class="text-xl font-black mb-3 border-b-2 border-black pb-3">{{ __('ui.beasiswa.bridge_title') }}</h2>
            <p class="theme-body mb-6" style="font-family:'Inter',sans-serif; line-height:1.75;">
                {{ __('ui.beasiswa.bridge_body') }}
            </p>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('articles.index') }}" class="btn-brutal btn-primary px-6 py-3 text-sm">{{ __('ui.beasiswa.bridge_articles') }}</a>
                <a href="{{ route('belajar.fullstack-iot') }}" class="btn-brutal btn-outline px-6 py-3 text-sm">{{ __('ui.beasiswa.bridge_fsiot') }}</a>
            </div>
        </div>

        {{-- More coming --}}
        <div class="p-6 sm:p-8 border-2 border-black text-white" style="background:#2D3748; box-shadow: 4px 4px 0 #000;">
            <h2 class="text-xl font-black mb-3 border-b border-gray-600 pb-3">{{ __('ui.beasiswa.more_title') }}</h2>
            <p class="mb-6 text-sm leading-relaxed" style="color:#CBD5E0; font-family:'Inter',sans-serif;">
                {{ __('ui.beasiswa.more_body') }}
            </p>
            <a href="{{ route('newsletter') }}" class="btn-brutal btn-primary px-6 py-3 text-sm inline-flex no-underline">
                {{ __('ui.beasiswa.more_cta') }}
            </a>
        </div>
    </div>

</x-layouts.app>
