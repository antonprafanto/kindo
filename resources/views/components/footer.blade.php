<footer style="background:#2D3748; color:#fff; border-top: 4px solid #000;">
    <div class="max-w-6xl mx-auto px-4 py-14">
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-10">

            {{-- Brand --}}
            <div>
                <a href="{{ route('home') }}" class="flex flex-wrap items-center gap-2 sm:gap-3 mb-4 no-underline">
                    <x-logo size="sm" />
                    <span class="font-bold text-white text-lg sm:text-xl">Koding Indonesia</span>
                </a>
                <p class="text-sm leading-relaxed" style="color: #A0AEC0;">
                    {{ __('ui.footer.blurb') }}
                </p>
                <div class="mt-5 flex items-center gap-1">
                    <span class="text-xs px-2 py-0.5 border border-gray-500 font-mono" style="color:#A0AEC0;">PHP 8.4</span>
                    <span class="text-xs px-2 py-0.5 border border-gray-500 font-mono" style="color:#A0AEC0;">Laravel 13</span>
                    <span class="text-xs px-2 py-0.5 border border-gray-500 font-mono" style="color:#A0AEC0;">Filament 5</span>
                </div>
            </div>

            {{-- Navigasi --}}
            <div>
                <h3 class="text-xs font-bold uppercase tracking-widest mb-5" style="color:#FF7A2F;">{{ __('ui.footer.nav') }}</h3>
                <ul class="space-y-2.5 text-sm">
                    <li><a href="{{ route('home') }}" class="hover:text-[#2979FF] transition-colors" style="color:#CBD5E0;">{{ __('ui.footer.home') }}</a></li>
                    <li><a href="{{ route('articles.index') }}" class="hover:text-[#2979FF] transition-colors" style="color:#CBD5E0;">{{ __('ui.footer.all_articles') }}</a></li>
                    <li><a href="{{ route('belajar.fullstack-iot') }}" class="hover:text-[#2979FF] transition-colors" style="color:#CBD5E0;">{{ __('ui.footer.iot_path') }}</a></li>
                    <li><a href="{{ route('search') }}" class="hover:text-[#2979FF] transition-colors" style="color:#CBD5E0;">{{ __('ui.footer.search') }}</a></li>
                    <li><a href="{{ route('about') }}" class="hover:text-[#2979FF] transition-colors" style="color:#CBD5E0;">{{ __('ui.footer.about') }}</a></li>
                    <li><a href="{{ route('beasiswa') }}" class="hover:text-[#2979FF] transition-colors" style="color:#CBD5E0;">{{ __('ui.footer.scholarship') }}</a></li>
                    <li><a href="{{ config('kindo.trakteer_tip_url') }}" target="_blank" rel="noopener noreferrer" class="hover:text-[#2979FF] transition-colors" style="color:#CBD5E0;">{{ __('ui.footer.support') }}</a></li>
                    <li><a href="{{ route('authors.index') }}" class="hover:text-[#2979FF] transition-colors" style="color:#CBD5E0;">{{ __('ui.footer.authors') }}</a></li>
                    <li><a href="{{ route('contributor.apply') }}" class="hover:text-[#2979FF] transition-colors" style="color:#CBD5E0;">{{ __('ui.footer.contributor') }}</a></li>
                    <li><a href="{{ route('contact') }}" class="hover:text-[#2979FF] transition-colors" style="color:#CBD5E0;">{{ __('ui.footer.contact') }}</a></li>
                    <li><a href="{{ route('newsletter') }}" class="hover:text-[#2979FF] transition-colors" style="color:#CBD5E0;">{{ __('ui.footer.newsletter') }}</a></li>
                </ul>
            </div>

            {{-- Newsletter --}}
            <div>
                <h3 class="text-xs font-bold uppercase tracking-widest mb-5" style="color:#FF7A2F;">{{ __('ui.footer.newsletter') }}</h3>
                <p class="text-sm mb-5 leading-relaxed" style="color:#A0AEC0;">
                    {{ __('ui.footer.newsletter_blurb') }}
                </p>
                <a href="{{ route('newsletter') }}" class="btn-brutal btn-primary inline-block py-3 px-6 text-sm no-underline">
                    {{ __('ui.footer.newsletter_cta') }}
                </a>
            </div>

            {{-- Kategori --}}
            <div>
                <h3 class="text-xs font-bold uppercase tracking-widest mb-5" style="color:#FF7A2F;">{{ __('ui.footer.topics') }}</h3>
                <ul class="space-y-2.5 text-sm">
                    @foreach($navCategories as $cat)
                    <li>
                        <a href="{{ route('categories.show', $cat->slug) }}" class="flex items-center gap-2 hover:text-[#2979FF] transition-colors" style="color:#CBD5E0;">
                            <span class="w-2 h-2 rounded-full flex-shrink-0" style="background: {{ $cat->color }};"></span>
                            {{ $cat->name }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="mt-10 pt-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 text-xs" style="border-top: 1px solid #4A5568; color: #718096;">
            <span class="max-w-full">{{ __('ui.footer.copyright', ['year' => date('Y')]) }} <a href="https://creativecommons.org/licenses/by-nc-sa/4.0/" target="_blank" class="underline hover:text-white">CC BY-NC-SA 4.0</a></span>
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-4">
                <a href="{{ route('privacy') }}" class="underline hover:text-white">{{ __('ui.footer.privacy') }}</a>
                <span class="font-mono">{{ __('ui.footer.built_with') }}</span>
            </div>
        </div>
    </div>
</footer>
