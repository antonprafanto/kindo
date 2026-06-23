<form method="POST" action="{{ route('newsletter.subscribe') }}" class="space-y-4">
    @csrf
    <div style="display:none;" aria-hidden="true">
        <input type="text" name="website" tabindex="-1" autocomplete="off">
    </div>

    <div class="{{ $compact ?? false ? 'flex flex-col sm:flex-row gap-3' : '' }}">
        <div class="{{ ($compact ?? false) ? 'flex-1' : '' }}">
            @if(!($compact ?? false))
            <label class="block text-sm font-bold mb-2 uppercase tracking-wider theme-heading">Email</label>
            @endif
            <input
                type="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="email@kamu.com"
                required
                class="input-brutal w-full @error('email') border-red-500 @enderror {{ ($compact ?? false) ? '!bg-white/10 !text-white placeholder:!text-gray-400 !border-gray-500 focus:!border-[#2979FF]' : '' }}"
                {{ ($compact ?? false) ? 'style=color-scheme:dark;' : '' }}
            >
            @error('email')
            <p class="text-red-400 text-xs mt-1 font-semibold">{{ $message }}</p>
            @enderror
        </div>
        <button type="submit" class="btn-brutal {{ ($compact ?? false) ? 'btn-primary whitespace-nowrap' : 'btn-primary w-full' }} py-3 px-6 text-sm shrink-0">
            {{ ($compact ?? false) ? 'Langganan →' : 'Berlangganan Newsletter →' }}
        </button>
    </div>

    @if(session('newsletter_success') && ($compact ?? false))
    <p class="text-xs font-semibold" style="color:#68D391;">✓ {{ session('newsletter_success') }}</p>
    @endif

    @if(!($compact ?? false))
    <p class="text-xs theme-muted">
        Dengan berlangganan, kamu setuju menerima email dari kami.
        <a href="{{ route('privacy') }}" class="underline">Kebijakan Privasi</a>
    </p>
    @endif
</form>
