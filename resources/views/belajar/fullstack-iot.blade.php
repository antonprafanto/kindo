<x-layouts.app
    title="Full Stack IoT Developer — Dari Nol | Koding Indonesia"
    description="Jalur unggulan Koding Indonesia: belajar Full Stack IoT dari awam hingga hero — gratis, berbahasa Indonesia, runut dari nol. Dukung kami lewat Trakteer."
    ogTitle="Full Stack IoT Developer — Dari Nol"
    ogDescription="Kurikulum premium (akses gratis) dari sensor sampai dashboard. From zero to hero, berbahasa Indonesia."
>

    <x-breadcrumb :items="[
        ['label' => 'Belajar', 'url' => route('belajar.fullstack-iot')],
        ['label' => 'Full Stack IoT'],
    ]" />

    <div class="max-w-4xl mx-auto px-4 py-10 sm:py-16">

        <div class="mb-8 sm:mb-12">
            <p class="inline-flex items-center gap-2 text-white text-xs font-bold px-3 py-1.5 border-2 border-black mb-4" style="background:#FF7A2F; box-shadow:2px 2px 0 #000; text-transform:uppercase; letter-spacing:.05em;">
                Jalur unggulan · akses gratis
            </p>
            <h1 class="text-3xl sm:text-4xl font-black mb-4 theme-heading" style="letter-spacing:-0.02em;">
                Full Stack IoT Developer
            </h1>
            <p class="text-lg theme-body max-w-2xl" style="font-family:'Inter',sans-serif; line-height:1.7;">
                Kurikulum khusus dari <strong>nol sekali</strong> sampai mampu membangun sistem IoT utuh:
                sensor → ESP32 → MQTT → backend → dashboard → aman & OTA.
                Ditulis ulang khusus jalur ini (FRESH), bukan salinan tutorial lama.
            </p>
        </div>

        <div class="grid sm:grid-cols-3 gap-4 mb-10 sm:mb-14">
            @foreach([
                ['label' => 'Modul inti', 'value' => '55+', 'hint' => 'plus 1 opsional'],
                ['label' => 'Bahasa', 'value' => 'ID', 'hint' => 'ramah awam'],
                ['label' => 'Biaya akses', 'value' => 'Gratis', 'hint' => 'dukungan opsional'],
            ] as $stat)
            <div class="p-4 sm:p-5 theme-paper border-2 border-black" style="box-shadow: 4px 4px 0 #000;">
                <div class="text-2xl font-black" style="color:#2979FF;">{{ $stat['value'] }}</div>
                <div class="text-xs font-bold uppercase tracking-wider theme-heading mt-1">{{ $stat['label'] }}</div>
                <div class="text-xs theme-muted mt-1" style="font-family:'Inter',sans-serif;">{{ $stat['hint'] }}</div>
            </div>
            @endforeach
        </div>

        <div class="p-6 sm:p-8 theme-paper border-2 border-black mb-10 sm:mb-14" style="box-shadow: 4px 4px 0 #000;">
            <h2 class="text-xl font-black mb-3 border-b-2 border-black pb-3">Proyek benang merah</h2>
            <p class="theme-body mb-4" style="font-family:'Inter',sans-serif; line-height:1.75;">
                Seluruh jalur mengitari <strong>Stasiun Ruang Belajar</strong>: pantau suhu & cahaya,
                kendalikan lampu (relay), lihat data di dashboard, dapat alert, tetap aman saat Wi‑Fi putus.
            </p>
            <p class="text-sm theme-muted" style="font-family:'Inter',sans-serif;">
                Status: kurikulum sudah dikunci (v2.3). Modul artikel akan terbit bertahap — halaman ini adalah pintu masuk resmi jalur tersebut.
            </p>
        </div>

        <div class="mb-10 sm:mb-14">
            <h2 class="text-xl sm:text-2xl font-black mb-6 relative inline-block" style="letter-spacing:-0.02em;">
                Lima fase belajar
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
            <h2 class="text-xl font-black mb-3 border-b border-gray-600 pb-3">Dukung Koding Indonesia</h2>
            <p class="mb-6 text-sm leading-relaxed" style="color:#CBD5E0; font-family:'Inter',sans-serif;">
                Jalur ini gratis dibaca. Kalau materi kami membantu, kamu bisa mendukung lewat tip di Trakteer —
                untuk biaya server, tooling, dan waktu menulis konten.
            </p>
            <a
                href="{{ $trakteerUrl }}"
                target="_blank"
                rel="noopener noreferrer"
                class="btn-brutal btn-primary px-8 py-3 text-sm inline-flex no-underline"
            >
                Kirim tip di Trakteer →
            </a>
            <p class="mt-4 text-xs" style="color:#718096; font-family:'Inter',sans-serif;">
                Membuka trakteer.id di tab baru. Tidak wajib — hanya jika kamu ingin mendukung.
            </p>
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('articles.index') }}" class="btn-brutal btn-outline px-6 py-3 text-sm">Jelajahi artikel →</a>
            <a href="{{ route('newsletter') }}" class="btn-brutal btn-primary px-6 py-3 text-sm">Langganan newsletter</a>
        </div>
    </div>

</x-layouts.app>
