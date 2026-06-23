<x-layouts.app title="Tentang — Koding Indonesia">

    <x-breadcrumb :items="[['label' => 'Tentang']]" />

    <div class="max-w-4xl mx-auto px-4 py-10 sm:py-16">

        {{-- Hero --}}
        <div class="text-center mb-10 sm:mb-16">
            <div class="inline-flex flex-wrap items-center justify-center gap-2 sm:gap-3 mb-6">
                <x-logo size="xl" />
                <span class="text-2xl sm:text-3xl font-black">Koding Indonesia</span>
                <span class="w-4 h-4 rounded-full border-2 border-black" style="background:#FF7A2F;"></span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-black mb-4" style="letter-spacing:-0.02em;">Tentang Koding Indonesia</h1>
            <p class="text-lg max-w-2xl mx-auto" style="color:#4A5568; font-family:'Inter',sans-serif; line-height:1.7;">
                Platform edukasi pemrograman berbahasa Indonesia — didirikan untuk membantu pelajar dan developer
                Indonesia belajar embedded system, IoT, dan pemrograman dengan konten yang berkualitas.
            </p>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-10 sm:mb-16">
            @foreach([['num' => $articleCount . '+', 'label' => 'Artikel Diterbitkan', 'color' => '#2979FF'], ['num' => '100%', 'label' => 'Bahasa Indonesia', 'color' => '#FF7A2F'], ['num' => '0', 'label' => 'Biaya Belajar', 'color' => '#2D3748']] as $stat)
            <div class="text-center p-4 sm:p-6 bg-white border-2 border-black" style="box-shadow: 4px 4px 0 #000;">
                <div class="text-2xl sm:text-3xl font-black mb-1" style="color: {{ $stat['color'] }};">{{ $stat['num'] }}</div>
                <div class="text-xs font-semibold uppercase tracking-wider" style="color:#718096;">{{ $stat['label'] }}</div>
            </div>
            @endforeach
        </div>

        {{-- Content --}}
        <div class="space-y-10" style="font-family:'Inter',sans-serif; line-height:1.8;">

            <div class="p-8 bg-white border-2 border-black" style="box-shadow: 4px 4px 0 #000;">
                <h2 class="text-xl font-black mb-4 border-b-2 border-black pb-3">🎯 Misi Kami</h2>
                <p style="color:#4A5568;">
                    Koding Indonesia hadir dengan satu misi sederhana: <strong>membuat konten pemrograman berkualitas yang mudah dipahami oleh siapapun di Indonesia</strong>.
                    Kami percaya bahwa bahasa tidak seharusnya menjadi halangan untuk belajar teknologi.
                </p>
            </div>

            <div class="p-8 bg-white border-2 border-black" style="box-shadow: 4px 4px 0 #000;">
                <h2 class="text-xl font-black mb-4 border-b-2 border-black pb-3">📚 Yang Kami Bahas</h2>
                <ul class="space-y-2" style="color:#4A5568;">
                    <li class="flex items-start gap-2"><span class="text-[#2979FF] font-bold mt-0.5">▸</span> <span><strong>Embedded System & IoT</strong> — ESP32, ESP8266, Arduino, sensor, aktuator</span></li>
                    <li class="flex items-start gap-2"><span class="text-[#2979FF] font-bold mt-0.5">▸</span> <span><strong>Pemrograman Umum</strong> — Python, JavaScript, PHP, C/C++</span></li>
                    <li class="flex items-start gap-2"><span class="text-[#2979FF] font-bold mt-0.5">▸</span> <span><strong>Web Development</strong> — Laravel, React, database, API</span></li>
                    <li class="flex items-start gap-2"><span class="text-[#2979FF] font-bold mt-0.5">▸</span> <span><strong>Networking & Linux</strong> — Jaringan komputer, server, keamanan</span></li>
                </ul>
            </div>

            <div class="p-8 border-2 border-black text-white" style="background:#2D3748; box-shadow: 4px 4px 0 #000;">
                <h2 class="text-xl font-black mb-4 border-b border-gray-600 pb-3">✉️ Ingin Berkontribusi?</h2>
                <p class="mb-6" style="color:#CBD5E0;">
                    Punya artikel menarik atau ingin berbagi pengetahuan? Kami terbuka untuk kolaborasi!
                </p>
                <a href="{{ route('contact') }}" class="btn-brutal btn-primary px-8 py-3 text-sm inline-flex">Hubungi Kami</a>
            </div>

        </div>
    </div>

</x-layouts.app>
