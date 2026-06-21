<x-layouts.app
    title="Kebijakan Privasi — Koding Indonesia"
    description="Kebijakan privasi Koding Indonesia menjelaskan bagaimana kami mengumpulkan, menggunakan, dan melindungi informasi Anda."
    :canonical="route('privacy')"
>

<x-breadcrumb :items="[['label' => 'Kebijakan Privasi']]" />

<div class="max-w-3xl mx-auto px-4 py-12">

    <h1 class="text-4xl font-black mb-2" style="letter-spacing:-0.02em;">Kebijakan Privasi</h1>
    <p class="text-sm mb-10" style="color:#718096; font-family:'Inter',sans-serif;">
        Terakhir diperbarui: {{ date('d F Y') }}
    </p>

    <div class="prose max-w-none space-y-8" style="font-family:'Inter',sans-serif; color:#2D3748; line-height:1.8;">

        <section>
            <h2 class="text-xl font-black mb-3 border-b-2 border-black pb-2">1. Informasi yang Kami Kumpulkan</h2>
            <p>Koding Indonesia hanya mengumpulkan informasi yang Anda berikan secara sukarela, yaitu:</p>
            <ul class="list-disc pl-6 mt-2 space-y-1">
                <li>Nama dan alamat email melalui formulir kontak</li>
                <li>Pesan yang Anda kirimkan melalui halaman Kontak</li>
            </ul>
            <p class="mt-3">Kami <strong>tidak</strong> mengumpulkan data pribadi tanpa sepengetahuan Anda, tidak menjual data Anda kepada pihak ketiga, dan tidak menggunakan cookie pelacakan pihak ketiga (kecuali analitik anonim).</p>
        </section>

        <section>
            <h2 class="text-xl font-black mb-3 border-b-2 border-black pb-2">2. Cara Kami Menggunakan Informasi</h2>
            <p>Informasi yang Anda berikan digunakan untuk:</p>
            <ul class="list-disc pl-6 mt-2 space-y-1">
                <li>Merespons pertanyaan atau pesan yang Anda kirimkan</li>
                <li>Meningkatkan kualitas konten dan layanan website</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-black mb-3 border-b-2 border-black pb-2">3. Cookie</h2>
            <p>Website ini menggunakan cookie teknis yang diperlukan untuk fungsionalitas dasar (seperti session browser). Cookie ini tidak mengandung informasi pribadi dan tidak digunakan untuk pelacakan lintas situs.</p>
        </section>

        <section>
            <h2 class="text-xl font-black mb-3 border-b-2 border-black pb-2">4. Layanan Pihak Ketiga</h2>
            <p>Kami menggunakan layanan berikut yang mungkin memiliki kebijakan privasi sendiri:</p>
            <ul class="list-disc pl-6 mt-2 space-y-1">
                <li><strong>Google Analytics</strong> — untuk analitik pengunjung anonim</li>
                <li><strong>GitHub</strong> — untuk hosting kode sumber</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-black mb-3 border-b-2 border-black pb-2">5. Keamanan Data</h2>
            <p>Kami menerapkan langkah-langkah teknis yang wajar untuk melindungi informasi Anda, termasuk enkripsi HTTPS, security headers, dan rate limiting pada form kontak.</p>
        </section>

        <section>
            <h2 class="text-xl font-black mb-3 border-b-2 border-black pb-2">6. Hak Anda</h2>
            <p>Anda berhak untuk meminta penghapusan data yang telah Anda kirimkan kepada kami. Hubungi kami melalui halaman <a href="{{ route('contact') }}" class="font-bold underline" style="color:#2979FF;">Kontak</a>.</p>
        </section>

        <section>
            <h2 class="text-xl font-black mb-3 border-b-2 border-black pb-2">7. Perubahan Kebijakan</h2>
            <p>Kami dapat memperbarui kebijakan privasi ini sewaktu-waktu. Perubahan signifikan akan diinformasikan di halaman ini dengan memperbarui tanggal di bagian atas.</p>
        </section>

        <section>
            <h2 class="text-xl font-black mb-3 border-b-2 border-black pb-2">8. Kontak</h2>
            <p>Jika Anda memiliki pertanyaan tentang kebijakan privasi ini, silakan hubungi kami melalui halaman <a href="{{ route('contact') }}" class="font-bold underline" style="color:#2979FF;">Kontak</a>.</p>
        </section>

    </div>

    <div class="mt-12">
        <a href="{{ route('home') }}" class="btn-brutal btn-outline px-6 py-3 inline-flex items-center gap-2">
            ← Kembali ke Beranda
        </a>
    </div>

</div>

</x-layouts.app>
