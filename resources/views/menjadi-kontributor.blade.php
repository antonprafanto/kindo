<x-layouts.app title="Menjadi Kontributor — Koding Indonesia">

    <x-breadcrumb :items="[['label' => 'Menjadi Kontributor']]" />

    <div class="max-w-4xl mx-auto px-4 py-10 sm:py-16">

        <div class="text-center mb-10 sm:mb-14">
            <h1 class="text-3xl sm:text-4xl font-black mb-4" style="letter-spacing:-0.02em;">Menjadi Kontributor</h1>
            <p class="text-lg max-w-2xl mx-auto theme-muted" style="font-family:'Inter',sans-serif; line-height:1.7;">
                Bagikan pengetahuanmu ke ribuan pembaca Koding Indonesia. Tulis artikel teknologi berbahasa Indonesia — kami bantu proses publikasinya.
            </p>
        </div>

        <div class="space-y-8 mb-14" style="font-family:'Inter',sans-serif; line-height:1.8;">

            <div class="p-6 sm:p-8 theme-paper border-2 border-black" style="box-shadow: 4px 4px 0 #000;">
                <h2 class="text-xl font-black mb-4 border-b-2 border-black pb-3">🎯 Visi Program</h2>
                <p class="theme-body">
                    Koding Indonesia ingin menjadi rumah bagi konten IT & teknologi berkualitas dalam Bahasa Indonesia.
                    Kontributor membantu memperkaya topik di luar fokus utama kami — dari embedded system hingga web development, UI/UX, networking, dan lainnya.
                </p>
            </div>

            <div class="p-6 sm:p-8 theme-paper border-2 border-black" style="box-shadow: 4px 4px 0 #000;">
                <h2 class="text-xl font-black mb-4 border-b-2 border-black pb-3">👤 Siapa Bisa Bergabung?</h2>
                <p class="theme-body mb-4">Siapa saja yang ingin berbagi ilmu — mahasiswa, profesional, hobbyist, atau pengajar. Tidak ada persyaratan gelar; yang penting kontennya akurat, jelas, dan bermanfaat.</p>
                <ul class="space-y-2 text-sm theme-body">
                    <li class="flex items-start gap-2"><span class="text-[#2979FF] font-bold mt-0.5">▸</span> Wajib menulis dalam <strong>Bahasa Indonesia</strong> yang baik dan mudah dipahami</li>
                    <li class="flex items-start gap-2"><span class="text-[#2979FF] font-bold mt-0.5">▸</span> Panjang artikel fleksibel; konten panjang boleh dipecah menjadi seri (Bagian 1, 2, dst.)</li>
                    <li class="flex items-start gap-2"><span class="text-[#2979FF] font-bold mt-0.5">▸</span> Saat ini belum ada honorarium — kontribusi bersifat sukarela untuk membangun portofolio publik</li>
                </ul>
            </div>

            <details class="p-6 sm:p-8 theme-paper border-2 border-black group" style="box-shadow: 4px 4px 0 #000;" open>
                <summary class="text-xl font-black border-b-2 border-black pb-3 cursor-pointer list-none flex items-center justify-between gap-3">
                    <span>📝 Pedoman Penulisan</span>
                    <span class="text-xs font-mono normal-case theme-muted group-open:rotate-180 transition-transform" aria-hidden="true">▼</span>
                </summary>
                <ul class="space-y-3 theme-body text-sm sm:text-base mt-4">
                    <li class="flex items-start gap-2"><span class="text-[#FF7A2F] font-bold mt-0.5">1.</span> <span><strong>Judul jelas</strong> — langsung ke inti topik, hindari clickbait</span></li>
                    <li class="flex items-start gap-2"><span class="text-[#FF7A2F] font-bold mt-0.5">2.</span> <span><strong>Struktur rapi</strong> — pendahuluan, langkah-langkah/tutorial, kesimpulan; gunakan heading H2/H3</span></li>
                    <li class="flex items-start gap-2"><span class="text-[#FF7A2F] font-bold mt-0.5">3.</span> <span><strong>Kode & perintah</strong> — format rapi, sertakan penjelasan baris penting</span></li>
                    <li class="flex items-start gap-2"><span class="text-[#FF7A2F] font-bold mt-0.5">4.</span> <span><strong>Gambar sampul</strong> — rasio 16:9 (ideal 1200×630 px), relevan dengan topik</span></li>
                    <li class="flex items-start gap-2"><span class="text-[#FF7A2F] font-bold mt-0.5">5.</span> <span><strong>Orisinalitas</strong> — tulis sendiri; kutipan wajib disertai sumber</span></li>
                    <li class="flex items-start gap-2"><span class="text-[#FF7A2F] font-bold mt-0.5">6.</span> <span><strong>Tidak ada promosi berlebihan</strong> — link produk/jasa hanya jika relevan dan transparan</span></li>
                </ul>
            </details>

            <details class="p-6 sm:p-8 theme-paper border-2 border-black group" style="box-shadow: 4px 4px 0 #000;">
                <summary class="text-xl font-black border-b-2 border-black pb-3 cursor-pointer list-none flex items-center justify-between gap-3">
                    <span>🏷️ Kategori & Tag</span>
                    <span class="text-xs font-mono normal-case theme-muted group-open:rotate-180 transition-transform" aria-hidden="true">▼</span>
                </summary>
                <div class="mt-4">
                    <p class="theme-body mb-4">
                        Saat menulis artikel, <strong>pilih kategori dan tag yang sudah tersedia</strong> — jangan membuat duplikat atau variasi nama yang hampir sama (misalnya "ESP32" dan "esp-32").
                        Jika topikmu belum punya kategori yang pas, sebutkan di aplikasi atau hubungi admin setelah disetujui.
                    </p>

                    <h3 class="text-sm font-bold uppercase tracking-wider mb-3 theme-muted">Kategori tersedia</h3>
                    <div class="flex flex-wrap gap-2 mb-6">
                        @foreach($categories as $cat)
                        <a href="{{ route('categories.show', $cat->slug) }}"
                           class="text-xs px-3 py-1.5 border-2 border-black font-semibold no-underline hover:bg-[#2979FF] hover:text-white transition-colors"
                           style="box-shadow: 2px 2px 0 #000; background: {{ $cat->color }}20;">
                            {{ $cat->name }}
                        </a>
                        @endforeach
                    </div>

                    <h3 class="text-sm font-bold uppercase tracking-wider mb-3 theme-muted">Tag tersedia</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($tags as $tag)
                        <a href="{{ route('tags.show', $tag->slug) }}"
                           class="text-xs px-2.5 py-1 border border-gray-400 rounded-full theme-muted hover:text-[#2979FF] no-underline transition-colors">
                            #{{ $tag->name }}
                        </a>
                        @endforeach
                    </div>
                </div>
            </details>

            <details class="p-6 sm:p-8 border-2 border-black text-white group" style="background:#2D3748; box-shadow: 4px 4px 0 #000;">
                <summary class="text-xl font-black border-b border-gray-600 pb-3 cursor-pointer list-none flex items-center justify-between gap-3">
                    <span>🔄 Alur Kerja</span>
                    <span class="text-xs font-mono normal-case opacity-70 group-open:rotate-180 transition-transform" aria-hidden="true">▼</span>
                </summary>
                <ol class="space-y-4 text-sm sm:text-base mt-4" style="color:#CBD5E0;">
                    <li class="flex gap-3"><span class="font-black text-[#FF7A2F] shrink-0">01</span> <span>Isi formulir aplikasi di bawah — tim kami meninjau dalam <strong>3–5 hari kerja</strong></span></li>
                    <li class="flex gap-3"><span class="font-black text-[#FF7A2F] shrink-0">02</span> <span>Jika disetujui, kamu mendapat email untuk <strong>membuat password</strong> (link berlaku <strong>24 jam</strong>) lalu login panel penulis di <code class="text-[#82B1FF]">/admin</code></span></li>
                    <li class="flex gap-3"><span class="font-black text-[#FF7A2F] shrink-0">03</span> <span>Tulis artikel sebagai <strong>Draft</strong>, lalu ubah status ke <strong>Menunggu Review</strong> saat siap</span></li>
                    <li class="flex gap-3"><span class="font-black text-[#FF7A2F] shrink-0">04</span> <span>Admin meninjau, bisa minta revisi, lalu <strong>mempublikasikan</strong> artikelmu</span></li>
                    <li class="flex gap-3"><span class="font-black text-[#FF7A2F] shrink-0">05</span> <span>Artikel terbit di situs & otomatis masuk ke newsletter pelanggan</span></li>
                </ol>
                <p class="mt-5 text-xs" style="color:#A0AEC0;">Aplikasi yang ditolak boleh diajukan ulang setelah memperbaiki profil atau portofolio.</p>
            </details>

            <details class="p-6 sm:p-8 theme-paper border-2 border-black group" style="box-shadow: 4px 4px 0 #000;">
                <summary class="text-xl font-black border-b-2 border-black pb-3 cursor-pointer list-none flex items-center justify-between gap-3">
                    <span>⚖️ Lisensi Konten</span>
                    <span class="text-xs font-mono normal-case theme-muted group-open:rotate-180 transition-transform" aria-hidden="true">▼</span>
                </summary>
                <p class="theme-body text-sm sm:text-base mt-4">
                    Artikel yang diterbitkan dilisensikan di bawah
                    <a href="https://creativecommons.org/licenses/by-nc-sa/4.0/" target="_blank" rel="noopener noreferrer" class="text-[#2979FF] font-semibold underline">CC BY-NC-SA 4.0</a>
                    — sama seperti konten resmi Koding Indonesia. Kamu tetap disebut sebagai penulis; kami berhak melakukan koreksi editorial ringan (typo, format, SEO).
                </p>
            </details>
        </div>

        {{-- Application form --}}
        <div id="daftar" class="max-w-2xl mx-auto">
            @if(session('success'))
            <div class="mb-8 p-5 border-2 border-black font-semibold text-sm theme-success" style="box-shadow: 4px 4px 0 #000;">
                ✓ {{ session('success') }}
            </div>
            <p class="text-sm theme-muted text-center">
                Terima kasih! Tim kami akan meninjau aplikasi dalam 3–5 hari kerja.
            </p>
            @else
            <h2 class="text-2xl font-black mb-2">Formulir Aplikasi</h2>
            <p class="mb-8 theme-muted text-sm">Lengkapi data di bawah untuk mengajukan diri sebagai kontributor.</p>

            <form method="POST" action="{{ route('contributor.apply.store') }}" class="space-y-5">
                @csrf
                <div style="display:none;" aria-hidden="true">
                    <input type="text" name="hp_fax" tabindex="-1" autocomplete="off">
                </div>

                <div>
                    <label class="block text-sm font-bold mb-2 uppercase tracking-wider">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Nama yang akan ditampilkan sebagai penulis"
                           class="input-brutal @error('name') border-red-500 @enderror">
                    @error('name')<p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-bold mb-2 uppercase tracking-wider">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="email@kamu.com"
                           class="input-brutal @error('email') border-red-500 @enderror">
                    @error('email')<p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-bold mb-2 uppercase tracking-wider">Bidang Keahlian <span class="text-red-500">*</span></label>
                    <input type="text" name="topic_expertise" value="{{ old('topic_expertise') }}" placeholder="Contoh: ESP32 & IoT, Laravel, Python Data Science"
                           class="input-brutal @error('topic_expertise') border-red-500 @enderror">
                    @error('topic_expertise')<p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-bold mb-2 uppercase tracking-wider">Link Portofolio / Contoh Tulisan</label>
                    <input type="url" name="sample_url" value="{{ old('sample_url') }}" placeholder="https://medium.com/@kamu atau GitHub, blog pribadi, dll."
                           class="input-brutal @error('sample_url') border-red-500 @enderror">
                    @error('sample_url')<p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-bold mb-2 uppercase tracking-wider">Motivasi & Topik yang Ingin Ditulis <span class="text-red-500">*</span></label>
                <textarea name="motivation" rows="6" placeholder="Ceritakan pengalamanmu, topik yang ingin kamu tulis, dan mengapa ingin berkontribusi di Koding Indonesia (min. 50 karakter)..."
                          class="input-brutal resize-none @error('motivation') border-red-500 @enderror">{{ old('motivation') }}</textarea>
                @error('motivation')<p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p>@enderror
                <p class="text-xs mt-1 theme-muted">Minimal 50 karakter — saat ini: <span id="motivation-count">{{ strlen(old('motivation', '')) }}</span>/50</p>
                </div>

                @if(config('services.turnstile.site_key'))
                <div>
                    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
                    <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}" data-theme="auto"></div>
                    @error('turnstile')<p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p>@enderror
                </div>
                @endif

            <button type="submit" class="btn-brutal btn-primary w-full py-4 text-sm">
                Kirim Aplikasi →
            </button>
        </form>

        <script>
            (function () {
                const field = document.querySelector('textarea[name="motivation"]');
                const counter = document.getElementById('motivation-count');
                if (!field || !counter) return;
                const update = () => { counter.textContent = field.value.length; };
                field.addEventListener('input', update);
                update();
            })();
        </script>
            @endif
        </div>

    </div>

</x-layouts.app>
