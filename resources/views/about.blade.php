<x-layouts.app :title="__('ui.about.meta_title')">

    <x-breadcrumb :items="[['label' => __('ui.about.breadcrumb')]]" />

    <div class="max-w-4xl mx-auto px-4 py-10 sm:py-16">

        {{-- Hero --}}
        <div class="text-center mb-10 sm:mb-16">
            <div class="inline-flex flex-wrap items-center justify-center gap-3 sm:gap-4 mb-6">
                <x-logo size="xl" class="border-2 border-black" style="box-shadow: 4px 4px 0 #000;" />
                <span class="text-2xl sm:text-3xl font-black">Koding Indonesia</span>
                <span class="w-4 h-4 rounded-full border-2 border-black" style="background:#FF7A2F;"></span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-black mb-4" style="letter-spacing:-0.02em;">{{ __('ui.about.heading') }}</h1>
            <p class="text-lg max-w-2xl mx-auto" style="color:#4A5568; font-family:'Inter',sans-serif; line-height:1.7;">
                {{ __('ui.about.intro') }}
            </p>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-10 sm:mb-16">
            @foreach([
                ['num' => $articleCount . '+', 'label' => __('ui.about.stat_articles'), 'color' => '#2979FF'],
                ['num' => '100%', 'label' => __('ui.about.stat_language'), 'color' => '#FF7A2F'],
                ['num' => '0', 'label' => __('ui.about.stat_cost'), 'color' => '#2D3748'],
            ] as $stat)
            <div class="text-center p-4 sm:p-6 theme-paper border-2 border-black" style="box-shadow: 4px 4px 0 #000;">
                <div class="text-2xl sm:text-3xl font-black mb-1" style="color: {{ $stat['color'] }};">{{ $stat['num'] }}</div>
                <div class="text-xs font-semibold uppercase tracking-wider theme-muted">{{ $stat['label'] }}</div>
            </div>
            @endforeach
        </div>

        {{-- Content --}}
        <div class="space-y-10" style="font-family:'Inter',sans-serif; line-height:1.8;">

            <div class="p-8 theme-paper border-2 border-black" style="box-shadow: 4px 4px 0 #000;">
                <h2 class="text-xl font-black mb-4 border-b-2 border-black pb-3">{{ __('ui.about.mission_title') }}</h2>
                <p class="theme-body">
                    {{ __('ui.about.mission_body') }}
                </p>
            </div>

            <div class="p-8 theme-paper border-2 border-black" style="box-shadow: 4px 4px 0 #000;">
                <h2 class="text-xl font-black mb-4 border-b-2 border-black pb-3">{{ __('ui.about.topics_title') }}</h2>
                <ul class="space-y-2 theme-body">
                    <li class="flex items-start gap-2"><span class="text-[#2979FF] font-bold mt-0.5">▸</span> <span>{{ __('ui.about.topic_embedded') }}</span></li>
                    <li class="flex items-start gap-2"><span class="text-[#2979FF] font-bold mt-0.5">▸</span> <span>{{ __('ui.about.topic_programming') }}</span></li>
                    <li class="flex items-start gap-2"><span class="text-[#2979FF] font-bold mt-0.5">▸</span> <span>{{ __('ui.about.topic_web') }}</span></li>
                    <li class="flex items-start gap-2"><span class="text-[#2979FF] font-bold mt-0.5">▸</span> <span>{{ __('ui.about.topic_uiux') }}</span></li>
                    <li class="flex items-start gap-2"><span class="text-[#2979FF] font-bold mt-0.5">▸</span> <span>{{ __('ui.about.topic_network') }}</span></li>
                </ul>
            </div>

            <div class="p-8 theme-paper border-2 border-black" style="box-shadow: 4px 4px 0 #000;">
                <h2 class="text-xl font-black mb-4 border-b-2 border-black pb-3">{{ __('ui.about.fsiot_title') }}</h2>
                <p class="theme-body mb-6">
                    {{ __('ui.about.fsiot_body') }}
                </p>
                <a href="{{ route('belajar.fullstack-iot') }}" class="btn-brutal btn-primary px-8 py-3 text-sm inline-flex">{{ __('ui.about.fsiot_cta') }}</a>
            </div>

            <div class="p-8 border-2 border-black text-white" style="background:#2D3748; box-shadow: 4px 4px 0 #000;">
                <h2 class="text-xl font-black mb-4 border-b border-gray-600 pb-3">{{ __('ui.about.contribute_title') }}</h2>
                <p class="mb-6" style="color:#CBD5E0;">
                    {{ __('ui.about.contribute_body') }}
                </p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('contributor.apply') }}" class="btn-brutal btn-primary px-8 py-3 text-sm inline-flex">{{ __('ui.about.contribute_cta') }}</a>
                    <a href="{{ config('kindo.trakteer_tip_url') }}" target="_blank" rel="noopener noreferrer" class="btn-brutal btn-outline px-8 py-3 text-sm inline-flex" style="border-color:#fff; color:#fff;">{{ __('ui.about.tip_cta') }}</a>
                </div>
            </div>

        </div>
    </div>

</x-layouts.app>
