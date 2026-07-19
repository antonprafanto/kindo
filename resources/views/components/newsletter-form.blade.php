<form method="POST" action="{{ route('newsletter.subscribe') }}" class="space-y-4">
    @csrf
    <div style="display:none;" aria-hidden="true">
        <input type="text" name="hp_fax" tabindex="-1" autocomplete="off">
    </div>

    <div>
        <label for="newsletter-email" class="block text-sm font-bold mb-2 uppercase tracking-wider theme-heading">Email</label>
        <input
            type="email"
            id="newsletter-email"
            name="email"
            value="{{ old('email') }}"
            placeholder="email@kamu.com"
            required
            class="input-brutal w-full @error('email') border-red-500 @enderror"
            @error('email') aria-invalid="true" @enderror
        >
        @error('email')
        <p class="text-red-400 text-xs mt-1 font-semibold">{{ $message }}</p>
        @enderror
    </div>

    @if(config('services.turnstile.site_key'))
    <div>
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
        <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}" data-theme="auto"></div>
        @error('turnstile')
        <p class="text-red-400 text-xs mt-1 font-semibold">{{ $message }}</p>
        @enderror
    </div>
    @endif

    <button type="submit" class="btn-brutal btn-primary w-full py-3 px-6 text-sm">
        Berlangganan Newsletter →
    </button>

    <p class="text-xs theme-muted">
        Dengan berlangganan, kamu setuju menerima email dari kami.
        <a href="{{ route('privacy') }}" class="underline">Kebijakan Privasi</a>
    </p>
</form>
