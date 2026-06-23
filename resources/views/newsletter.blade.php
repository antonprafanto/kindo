<x-layouts.app title="Newsletter — Koding Indonesia" description="Berlangganan newsletter Koding Indonesia dan dapatkan notifikasi artikel tutorial ESP32, IoT, dan pemrograman terbaru.">

    <x-breadcrumb :items="[['label' => 'Newsletter']]" />

    <div class="max-w-2xl mx-auto px-4 py-10 sm:py-16">

        <h1 class="text-3xl sm:text-4xl font-black mb-2 theme-heading" style="letter-spacing:-0.02em;">Newsletter</h1>
        <p class="mb-8 theme-muted" style="font-family:'Inter',sans-serif;">
            Dapatkan notifikasi email setiap ada artikel tutorial baru — ESP32, IoT, Arduino, dan pemrograman berbahasa Indonesia.
        </p>

        @if(session('newsletter_success'))
        <div class="mb-8 p-5 border-2 border-black font-semibold text-sm" style="background:#ECFDF5; color:#065F46; box-shadow: 4px 4px 0 #000;">
            ✓ {{ session('newsletter_success') }}
        </div>
        @endif

        <div class="card-brutal p-6 sm:p-8 mb-8">
            <h2 class="text-lg font-bold mb-4 theme-heading">Kenapa berlangganan?</h2>
            <ul class="space-y-3 text-sm theme-muted">
                <li class="flex gap-2"><span>📬</span> Notifikasi otomatis saat artikel baru publish</li>
                <li class="flex gap-2"><span>🎯</span> Fokus ESP32, IoT, dan embedded system</li>
                <li class="flex gap-2"><span>🇮🇩</span> 100% Bahasa Indonesia, mudah dipahami</li>
                <li class="flex gap-2"><span>🔒</span> Double opt-in — email dikonfirmasi dulu</li>
                <li class="flex gap-2"><span>✋</span> Unsubscribe kapan saja, satu klik</li>
            </ul>
        </div>

        <x-newsletter-form />

    </div>

</x-layouts.app>
