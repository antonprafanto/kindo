@if(app(\App\Services\TurnstileService::class)->isConfigured())
<div>
    <div id="filament-auth-turnstile"></div>
</div>
@endif

@assets
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
@endassets

@script
<script>
    const siteKey = @js(config('services.turnstile.site_key'));
    let widgetId = null;

    function renderAuthTurnstile() {
        const el = document.getElementById('filament-auth-turnstile');
        if (! el) return;

        if (typeof turnstile === 'undefined') {
            setTimeout(renderAuthTurnstile, 100);
            return;
        }

        if (widgetId !== null) {
            turnstile.remove(widgetId);
            widgetId = null;
        }

        el.innerHTML = '';
        widgetId = turnstile.render(el, {
            sitekey: siteKey,
            theme: 'auto',
            callback: (token) => $wire.set('turnstileToken', token),
            'expired-callback': () => $wire.set('turnstileToken', ''),
            'error-callback': () => $wire.set('turnstileToken', ''),
        });
    }

    renderAuthTurnstile();

    $wire.on('reset-turnstile', () => {
        $wire.set('turnstileToken', '');
        renderAuthTurnstile();
    });
</script>
@endscript
