<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article71Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first()
            ?? Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $iotCat) {
            throw new \RuntimeException('User admin atau kategori iot-smart-device tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-pintu-masuk-iot';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        foreach ([
            'fullstack-iot' => 'Fullstack IoT',
            'iot'           => 'IoT',
            'esp32'         => 'ESP32',
        ] as $tagSlug => $tagName) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagName]);
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'            => $admin->id,
                'category_id'        => $iotCat->id,
                'title'              => 'Pintu Masuk IoT: Konsep Dasar Tanpa Rumus Rumit',
                'title_en'           => 'Entryway to IoT: Core Concepts Without Complex Formulas',
                'excerpt'            => 'Modul perdana M-01 (#71): pahami konsep dasar Internet of Things (IoT) dari nol — analogi panca indra, 4 pilar sistem, studi kasus meja belajar, dan kuis pemahaman.',
                'excerpt_en'         => 'Module M-01 (#71): understand core Internet of Things (IoT) concepts from scratch — sensory analogy, 4 system pillars, smart desk case study, and interactive quiz.',
                'body'               => $this->body(),
                'body_en'            => $this->bodyEn(),
                'status'             => 'draft',
                'published_at'       => null,
                'is_featured'        => false,
                'seo_title'          => 'Apa itu IoT? Konsep Dasar untuk Pemula Total',
                'seo_title_en'       => 'What is IoT? A Complete Beginner Guide',
                'seo_description'    => 'Pelajari apa itu Internet of Things (IoT) dari nol: analogi 4 pilar, cara kerja sensor di meja belajar, hingga data tampil di web browser secara live.',
                'seo_description_en' => 'Learn what Internet of Things (IoT) really is from scratch: 4 core pillars, desk sensors, and real-time browser dashboard integration.',
            ]
        );

        $tagIds = Tag::whereIn('slug', ['fullstack-iot', 'iot', 'esp32'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Seeder Modul M-01 (#71) berhasil disimpan sebagai DRAFT: '.$article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — selamat datang di jalur Full Stack IoT</h2>
<p>Selamat datang di <strong>Modul M-01 (#71 (ini))</strong>, langkah pembuka dari perjalanan panjang kita di kurikulum <strong>Full Stack IoT Developer</strong> Koding Indonesia! Jika selama ini kamu merasa istilah <em>Internet of Things</em> (IoT) terdengar sangat canggih, rumit, atau hanya milik insinyur laboratorium berjas putih, artikel ini hadir untuk mematahkan mitos tersebut.</p>

<p>Pada hakikatnya, IoT bukan tentang rumus fisika yang memusingkan kepala atau kalkulasi matematika yang membingungkan. IoT adalah seni <strong>menjembatani dunia fisik nyata yang kita sentuh setiap hari dengan dunia digital internet</strong>. Melalui pendekatan belajar yang praktis dan ramah pemula, di modul perdana ini kita akan meletakkan fondasi cara berpikir seorang insinyur IoT modern dengan bahasa yang santai, visual yang jelas, dan analogi kehidupan sehari-hari yang mudah dipahami.</p>

<blockquote>
  <p><strong>Status Jalur:</strong> Modul ini berstatus <em>Draft Resmi</em>. Sebagai pembaca, kamu tidak perlu terburu-buru membeli alat elektronik apa pun hari ini. Cukup nikmati pembahasannya, serap konsep dasarnya, dan ikuti alurnya secara bertahap.</p>
</blockquote>

<h2>Alat yang dipakai hari ini (Tools-First)</h2>
<p>Di Koding Indonesia, kami menerapkan prinsip <em>tools-first</em>: sebelum kamu membaca penjelasan konsep atau perintah, kami selalu memberi tahu alat apa yang perlu kamu buka terlebih dahulu agar kamu tidak kebingungan di depan layar.</p>

<div style="background:#F5F5F0;border:2px solid #1a1a1a;border-radius:8px;padding:16px;margin:20px 0;">
  <h3 style="margin-top:0;color:#1a1a1a;">Daftar Alat untuk Modul M-01</h3>
  <ol style="margin-bottom:0;padding-left:20px;color:#1a1a1a;line-height:1.7;">
    <li><strong>Aplikasi Peramban Web (Web Browser):</strong> Buka Google Chrome, Mozilla Firefox, Microsoft Edge, atau Safari di komputer, laptop, ataupun ponsel pintarmu.</li>
    <li><strong>Buku Catatan / Aplikasi Catatan Sederhana:</strong> Buka aplikasi Notepad di Windows, TextEdit di Mac, atau selembar kertas fisik untuk mencatat ide benda pintar di sekitarmu.</li>
    <li><strong>Tidak Perlu Memasang Software Tambahan:</strong> Untuk modul pembuka ini, kamu <em>belum perlu</em> memasang aplikasi pemrograman atau menyambungkan kabel listrik apa pun. Kita fokus membangun logika berpikir terlebih dahulu.</li>
  </ol>
</div>

<div style="background:#FFFFFF;border:2px solid #1a1a1a;border-radius:8px;padding:16px;margin:20px 0;">
  <p><strong style="color:#1a1a1a;">Uji Coba Praktik Pertama Kamu: Menjalankan Perintah di Peramban Web</strong></p>
  <p>Sebelum kita menyentuh hardware fisik di modul-modul berikutnya, mari buktikan bahwa peramban web di komputermu sudah siap mengeksekusi instruksi digital tanpa perlu menginstal aplikasi tambahan:</p>
  <ol>
    <li>Buka tab baru di perambanmu (Google Chrome atau Microsoft Edge).</li>
    <li>Tekan tombol <code>F12</code> pada keyboard (atau klik kanan di sembarang area kosong halaman lalu pilih <strong>Inspect / Periksa</strong>).</li>
    <li>Pilih tab <strong>Console</strong> pada panel pengembang yang muncul di samping atau bawah layar.</li>
    <li>Ketikkan perintah satu baris berikut lalu tekan <strong>Enter</strong>:</li>
  </ol>
  <pre><code class="language-javascript">console.log("Halo Dunia IoT Koding Indonesia!");</code></pre>
  <p>Kamu akan langsung melihat pesan sambutan tersebut tercetak di layar konsol! Ini adalah bukti awal bahwa perangkat sehari-harimu sudah memiliki mesin eksekusi kode yang siap kita gunakan untuk membangun antarmuka dasbor IoT nanti.</p>
</div>

<h2>Perbedaan barang elektronik biasa vs perangkat IoT</h2>
<p>Mari kita mulai dari hal yang paling dekat dengan kita. Kamu tentu pernah melihat termometer digital pengukur suhu tubuh yang dijual di apotek atau kipas angin meja biasa yang memiliki tombol putar kecepatan 1, 2, dan 3. Apakah alat-alat tersebut bisa disebut sebagai perangkat IoT?</p>

<p>Jawabannya adalah <strong>bukan</strong>. Alat-alat tersebut adalah <em>elektronik konvensional (alat terisolasi)</em>. Mengapa demikian?</p>

<ul>
  <li><strong>Termometer Digital Biasa:</strong> Sensor membaca suhu tubuhmu, menampilkan angka 36,5 °C di layar kecilnya, lalu selesai. Data suhu itu menguap begitu saja. Alat tersebut tidak tahu suhu 5 jam yang lalu, tidak bisa memberitahu dokter dari jarak jauh, dan tidak bisa memicu peringatan otomatis.</li>
  <li><strong>Kipas Angin Meja Biasa:</strong> Baling-baling hanya berputar jika jari tanganmu menekan tombol fisiknya. Kipas tersebut tidak peduli apakah ruanganmu sedang sepanas sauna atau sedingin kutub utara jika kamu tidak menekan tombolnya secara langsung.</li>
</ul>

<p>Sekarang, bayangkan jika alat-alat tersebut kita sentuh dengan sihir <strong>Internet of Things</strong>:</p>

<ul>
  <li><strong>Termometer Berbasis IoT:</strong> Begitu sensor membaca suhu ruangan mencapai 32 °C, angka tersebut tidak hanya tampil di layar meja, melainkan otomatis dikirim melalui jaringan Wi-Fi ke basis data server. Dalam hitungan detik, grafik suhu di ponsel pintarmu terbarui, dan kamu menerima pesan notifikasi Telegram: <em>"Peringatan: Suhu ruangan kerja melebihi batas nyaman!"</em></li>
  <li><strong>Kipas / Pendingin Berbasis IoT:</strong> Tanpa perlu kamu sentuh, sistem di server mengirimkan perintah balik ke sakelar elektronik (relay) di meja belajarmu untuk menyalakan kipas angin secara otomatis sampai suhu ruangan turun kembali ke angka 25 °C.</li>
</ul>

<figure style="margin:28px 0;background:#F5F5F0;border:2px solid #1a1a1a;border-radius:8px;padding:20px;text-align:center;">
  <svg viewBox="0 0 760 320" width="100%" height="100%" style="max-width:760px;font-family:'Space Grotesk',system-ui,sans-serif;" aria-label="Diagram Perbandingan Elektronik Biasa vs Perangkat IoT">
    <defs>
      <marker id="arrowM01A" markerWidth="10" markerHeight="10" refX="6" refY="3" orient="auto">
        <path d="M0,0 L0,6 L9,3 z" fill="#2979FF" />
      </marker>
      <marker id="arrowM01Gray" markerWidth="10" markerHeight="10" refX="6" refY="3" orient="auto">
        <path d="M0,0 L0,6 L9,3 z" fill="#757575" />
      </marker>
    </defs>

    <!-- Header Panel Kiri: Elektronik Biasa -->
    <rect x="20" y="20" width="340" height="270" rx="8" fill="#FFFFFF" stroke="#E0E0E0" stroke-width="2" />
    <rect x="20" y="20" width="340" height="42" rx="8" fill="#EEEEEE" stroke="#E0E0E0" stroke-width="2" />
    <text x="190" y="47" text-anchor="middle" font-size="15" font-weight="700" fill="#424242">Elektronik Biasa (Terisolasi)</text>

    <!-- Komponen Kiri -->
    <rect x="45" y="85" width="120" height="55" rx="6" fill="#F5F5F5" stroke="#9E9E9E" stroke-width="1.5" />
    <text x="105" y="110" text-anchor="middle" font-size="13" font-weight="600" fill="#212121">Sensor Fisik</text>
    <text x="105" y="128" text-anchor="middle" font-size="11" fill="#616161">Ukur Suhu</text>

    <line x1="165" y1="112" x2="215" y2="112" stroke="#757575" stroke-width="2" marker-end="url(#arrowM01Gray)" />

    <rect x="225" y="85" width="115" height="55" rx="6" fill="#F5F5F5" stroke="#9E9E9E" stroke-width="1.5" />
    <text x="282" y="110" text-anchor="middle" font-size="13" font-weight="600" fill="#212121">Layar Lokal</text>
    <text x="282" y="128" text-anchor="middle" font-size="11" fill="#616161">Tampil Angka</text>

    <rect x="45" y="175" width="295" height="85" rx="6" fill="#FFFDE7" stroke="#FBC02D" stroke-width="1.5" />
    <text x="192" y="202" text-anchor="middle" font-size="13" font-weight="700" fill="#F57F17">Keterbatasan:</text>
    <text x="192" y="224" text-anchor="middle" font-size="12" fill="#5D4037">• Data hilang seketika (tanpa histori)</text>
    <text x="192" y="244" text-anchor="middle" font-size="12" fill="#5D4037">• Wajib ada manusia di tempat untuk melihat</text>

    <!-- Header Panel Kanan: Sistem IoT -->
    <rect x="400" y="20" width="340" height="270" rx="8" fill="#FFFFFF" stroke="#2979FF" stroke-width="2.5" />
    <rect x="400" y="20" width="340" height="42" rx="8" fill="#E3F2FD" stroke="#2979FF" stroke-width="2" />
    <text x="570" y="47" text-anchor="middle" font-size="15" font-weight="700" fill="#1565C0">Perangkat IoT (Terhubung)</text>

    <!-- Komponen Kanan -->
    <rect x="420" y="85" width="85" height="55" rx="6" fill="#F5F5F5" stroke="#1E88E5" stroke-width="1.5" />
    <text x="462" y="110" text-anchor="middle" font-size="12" font-weight="600" fill="#0D47A1">Sensor</text>
    <text x="462" y="128" text-anchor="middle" font-size="11" fill="#546E7A">Ukur Suhu</text>

    <line x1="505" y1="112" x2="530" y2="112" stroke="#2979FF" stroke-width="2" marker-end="url(#arrowM01A)" />

    <rect x="535" y="85" width="85" height="55" rx="6" fill="#F5F5F5" stroke="#1E88E5" stroke-width="1.5" />
    <text x="577" y="110" text-anchor="middle" font-size="12" font-weight="600" fill="#0D47A1">ESP32</text>
    <text x="577" y="128" text-anchor="middle" font-size="11" fill="#546E7A">Proses &amp; Wi-Fi</text>

    <line x1="620" y1="112" x2="645" y2="112" stroke="#2979FF" stroke-width="2" marker-end="url(#arrowM01A)" />

    <rect x="650" y="85" width="75" height="55" rx="6" fill="#F5F5F5" stroke="#1E88E5" stroke-width="1.5" />
    <text x="687" y="110" text-anchor="middle" font-size="12" font-weight="600" fill="#0D47A1">Cloud / PC</text>
    <text x="687" y="128" text-anchor="middle" font-size="11" fill="#546E7A">Basis Data</text>

    <rect x="420" y="175" width="305" height="85" rx="6" fill="#E8F5E9" stroke="#4CAF50" stroke-width="1.5" />
    <text x="572" y="202" text-anchor="middle" font-size="13" font-weight="700" fill="#2E7D32">Kekuatan IoT:</text>
    <text x="572" y="224" text-anchor="middle" font-size="12" fill="#1B5E20">• Data tersimpan rapi, bisa dianalisis 24 jam</text>
    <text x="572" y="244" text-anchor="middle" font-size="12" fill="#1B5E20">• Kontrol otomatis &amp; pantau jarak jauh via HP</text>
  </svg>
  <figcaption style="font-size:13px;color:#616161;margin-top:12px;font-style:italic;">Gambar 1.1: Perbandingan alur kerja elektronik konvensional tertutup vs sistem Internet of Things yang terhubung jaringan. (Sumber: Desain Orisinal Tim Kurikulum Koding Indonesia)</figcaption>
</figure>

<h2>Empat pilar utama IoT — analogi tubuh manusia</h2>
<p>Agar kamu tidak bingung membayangkan arsitektur sistem yang rumit, para ahli teknologi sepakat membagi sistem IoT ke dalam <strong>4 pilar utama</strong>. Cara paling menyenangkan untuk memahaminya adalah dengan membayangkan <strong>anatomi tubuh manusia</strong>:</p>

<div style="background:#FFFFFF;border:2px solid #1a1a1a;border-radius:8px;padding:18px;margin:20px 0;">
  <h3 style="margin-top:0;color:#2979FF;">1. Pilar Panca Indra ➔ Sensor Fisik</h3>
  <p style="margin-bottom:0;line-height:1.7;">Mata kita melihat terangnya matahari, kulit kita merasakan panasnya udara, dan telinga kita mendengar suara bising. Di dunia IoT, peran panca indra ini digantikan oleh <strong>Sensor</strong>. Contohnya adalah sensor <em>DHT22</em> yang merasakan derajat panas dan kelembapan udara, atau sensor <em>LDR</em> yang merasakan gelap terangnya ruangan belajarmu.</p>
</div>

<div style="background:#FFFFFF;border:2px solid #1a1a1a;border-radius:8px;padding:18px;margin:20px 0;">
  <h3 style="margin-top:0;color:#2979FF;">2. Pilar Otak Pemroses ➔ Mikrokontroler (ESP32)</h3>
  <p style="margin-bottom:0;line-height:1.7;">Ketika kulit tanganmu menyentuh cangkir kopi panas, sinyal listrik biologis dikirim ke otak untuk diterjemahkan: <em>"Wah, air ini bersuhu 80 derajat!"</em> Di sistem IoT kita, peran otak kecil ini dipegang oleh <strong>Mikrokontroler ESP32</strong>. Papan chip seukuran dua ruas jari ini membaca tegangan listrik dari sensor, mengubahnya menjadi angka desimal yang teratur, dan memutuskan tindakan apa yang perlu diambil.</p>
</div>

<div style="background:#FFFFFF;border:2px solid #1a1a1a;border-radius:8px;padding:18px;margin:20px 0;">
  <h3 style="margin-top:0;color:#2979FF;">3. Pilar Jaringan Saraf &amp; Kurir Pesan ➔ Konektivitas (Wi-Fi &amp; MQTT)</h3>
  <p style="margin-bottom:0;line-height:1.7;">Setelah otak mengetahui informasi tersebut, bagaimana pesan itu bisa sampai ke orang lain di seberang pulau? Melalui <strong>Konektivitas Jaringan</strong>. ESP32 dilengkapi pemancar radio Wi-Fi bawaan yang mampu berbicara menggunakan bahasa protokol data ringan bernama <strong>MQTT</strong>. Bayangkan MQTT seperti kurir kilat super cepat yang mengantar surat data dari meja belajarmu ke komputer server tanpa membebani kuota internet.</p>
</div>

<div style="background:#FFFFFF;border:2px solid #1a1a1a;border-radius:8px;padding:18px;margin:20px 0;">
  <h3 style="margin-top:0;color:#2979FF;">4. Pilar Layar Pemantau &amp; Otot Bergerak ➔ Web Dashboard &amp; Aktuator (Relay)</h3>
  <p style="margin-bottom:0;line-height:1.7;">Informasi tidak akan berguna jika tidak menghasilkan aksi nyata atau tidak bisa dilihat oleh manusia. Pilar keempat terdiri dari dua elemen:
  <br>• <strong>Layar Pemantau (Dashboard Web/PWA):</strong> Halaman visual menarik dengan grafik dan angka yang bisa dibuka di peramban web laptop atau layar ponsel pintarmu.
  <br>• <strong>Otot Penggerak (Aktuator / Sakelar Relay):</strong> Komponen mekanik yang bisa menyalakan lampu belajar, memutar motor servo, atau mengaktifkan pompa air secara otomatis saat menerima sinyal perintah.</p>
</div>

<figure style="margin:28px 0;background:#F5F5F0;border:2px solid #1a1a1a;border-radius:8px;padding:20px;text-align:center;">
  <svg viewBox="0 0 760 260" width="100%" height="100%" style="max-width:760px;font-family:'Space Grotesk',system-ui,sans-serif;" aria-label="Diagram 4 Pilar Utama IoT">
    <defs>
      <marker id="arrowPilar" markerWidth="10" markerHeight="10" refX="6" refY="3" orient="auto">
        <path d="M0,0 L0,6 L9,3 z" fill="#1a1a1a" />
      </marker>
    </defs>

    <!-- Kartu 1: Sensor -->
    <rect x="15" y="40" width="160" height="170" rx="8" fill="#FFFFFF" stroke="#1a1a1a" stroke-width="2" />
    <rect x="15" y="40" width="160" height="36" rx="8" fill="#E3F2FD" stroke="#1a1a1a" stroke-width="2" />
    <text x="95" y="64" text-anchor="middle" font-size="13" font-weight="700" fill="#1565C0">1. Panca Indra</text>
    <text x="95" y="115" text-anchor="middle" font-size="16" font-weight="700" fill="#1a1a1a">SENSOR</text>
    <text x="95" y="145" text-anchor="middle" font-size="12" fill="#616161">DHT22, LDR, PIR</text>
    <text x="95" y="185" text-anchor="middle" font-size="11" font-weight="600" fill="#2E7D32">Merasakan Alam</text>

    <!-- Panah 1 ke 2 -->
    <line x1="175" y1="125" x2="200" y2="125" stroke="#1a1a1a" stroke-width="2.5" marker-end="url(#arrowPilar)" />

    <!-- Kartu 2: Mikrokontroler -->
    <rect x="205" y="40" width="160" height="170" rx="8" fill="#FFFFFF" stroke="#1a1a1a" stroke-width="2" />
    <rect x="205" y="40" width="160" height="36" rx="8" fill="#EDE7F6" stroke="#1a1a1a" stroke-width="2" />
    <text x="285" y="64" text-anchor="middle" font-size="13" font-weight="700" fill="#512DA8">2. Otak Mini</text>
    <text x="285" y="115" text-anchor="middle" font-size="16" font-weight="700" fill="#1a1a1a">ESP32 SoC</text>
    <text x="285" y="145" text-anchor="middle" font-size="12" fill="#616161">Program C++</text>
    <text x="285" y="185" text-anchor="middle" font-size="11" font-weight="600" fill="#2E7D32">Mengolah Data</text>

    <!-- Panah 2 ke 3 -->
    <line x1="365" y1="125" x2="390" y2="125" stroke="#1a1a1a" stroke-width="2.5" marker-end="url(#arrowPilar)" />

    <!-- Kartu 3: Jaringan -->
    <rect x="395" y="40" width="160" height="170" rx="8" fill="#FFFFFF" stroke="#1a1a1a" stroke-width="2" />
    <rect x="395" y="40" width="160" height="36" rx="8" fill="#FFF3E0" stroke="#1a1a1a" stroke-width="2" />
    <text x="475" y="64" text-anchor="middle" font-size="13" font-weight="700" fill="#E65100">3. Kurir Pesan</text>
    <text x="475" y="115" text-anchor="middle" font-size="16" font-weight="700" fill="#1a1a1a">Wi-Fi &amp; MQTT</text>
    <text x="475" y="145" text-anchor="middle" font-size="12" fill="#616161">Paket JSON Kilat</text>
    <text x="475" y="185" text-anchor="middle" font-size="11" font-weight="600" fill="#2E7D32">Mengirim Sinyal</text>

    <!-- Panah 3 ke 4 -->
    <line x1="555" y1="125" x2="580" y2="125" stroke="#1a1a1a" stroke-width="2.5" marker-end="url(#arrowPilar)" />

    <!-- Kartu 4: Dashboard & Aksi -->
    <rect x="585" y="40" width="160" height="170" rx="8" fill="#FFFFFF" stroke="#1a1a1a" stroke-width="2" />
    <rect x="585" y="40" width="160" height="36" rx="8" fill="#E8F5E9" stroke="#1a1a1a" stroke-width="2" />
    <text x="665" y="64" text-anchor="middle" font-size="13" font-weight="700" fill="#2E7D32">4. Layar &amp; Aksi</text>
    <text x="665" y="115" text-anchor="middle" font-size="16" font-weight="700" fill="#1a1a1a">DASHBOARD</text>
    <text x="665" y="145" text-anchor="middle" font-size="12" fill="#616161">Web PWA &amp; Relay</text>
    <text x="665" y="185" text-anchor="middle" font-size="11" font-weight="600" fill="#2E7D32">Pantau &amp; Kendali</text>
  </svg>
  <figcaption style="font-size:13px;color:#616161;margin-top:12px;font-style:italic;">Gambar 1.2: Empat pilar fundamental arsitektur Full Stack IoT: dari penginderaan sensor fisik hingga visualisasi dasbor web. (Sumber: Desain Orisinal Tim Kurikulum Koding Indonesia, diadaptasi dari acuan arsitektur IEEE IoT)</figcaption>
</figure>

<h2>Studi kasus proyek kita — Stasiun Pintar Meja Belajar</h2>
<p>Daripada mempelajari teori abstrak seperti pabrik nuklir atau satelit luar angkasa yang sulit kamu jumpai, seluruh kurikulum 73 modul ini dirancang mengitari sebuah proyek studi kasus nyata yang sangat dekat dengan hidupmu: <strong>Stasiun Pemantau Meja Belajar (Smart Desk Station)</strong>.</p>

<p>Bayangkan di sudut meja belajarmu terdapat sebuah kotak rapi yang bekerja 24 jam sehari tanpa henti:</p>
<ol>
  <li>Ia memantau apakah suhu meja kerjamu terlalu panas agar kamu tidak mudah mengantuk saat belajar.</li>
  <li>Ia mendeteksi tingkat pencahayaan ruangan: jika senja tiba dan ruangan mulai gelap, ia otomatis menyalakan lampu meja LED secara perlahan.</li>
  <li>Ia menghitung apakah ada gerakan orang di depan meja. Jika kamu beranjak tidur dan meja kosong selama 15 menit, seluruh lampu dan kipas dimatikan otomatis untuk menghemat tagihan listrik.</li>
  <li>Ia menyajikan grafik tren suhu 24 jam terakhir di aplikasi web ponsel pintarmu, sehingga kamu bisa mengecek kondisi ruangan dari mana saja di luar rumah.</li>
</ol>

<p>Dan yang paling membahagiakan: <strong>arsitektur kurikulum ini 100% inklusif secara hybrid</strong>. Kamu cukup memiliki satu keping papan mikrokontroler <em>ESP32-DevKitC-1</em> seharga kurang lebih Rp 50.000 dan laptop harianmu untuk menyelesaikan seluruh proyek ini dari hulu ke hilir! Jika di kemudian hari kamu memiliki komputer mini <em>Raspberry Pi</em> (atau laptop tua bekas), kita menyediakan Babak 5 khusus untuk meningkatkan stasiun mejamu menjadi server mini industri 24/7.</p>

<h2>Latihan mandiri awam — memetakan 3 benda pintar di sekitarmu</h2>
<p>Sebelum melangkah lebih jauh, mari kita latih kepekaan insting teknologimu. Luangkan waktu 2 menit untuk mengamati lingkungan di sekitarmu dan petakan 3 benda berikut ke dalam 4 pilar yang baru saja kita pelajari:</p>

<div style="background:#F5F5F0;border-left:4px solid #2979FF;padding:14px 18px;margin:18px 0;">
  <p><strong style="color:#1a1a1a;">1. Lampu Penerangan Jalan Umum (PJU) Otomatis</strong></p>
  <p>Pernahkah kamu melihat lampu di pinggir jalan raya yang menyala sendiri tepat saat matahari terbenam dan padam saat fajar tiba? Pilar sensornya adalah <em>sensor cahaya (LDR)</em>, otaknya adalah mikrokontroler kecil di tiang lampu, dan aktuatornya adalah sakelar relay yang menghubungkan listrik berdaya besar ke bohlam lampu.</p>
</div>

<div style="background:#F5F5F0;border-left:4px solid #2979FF;padding:14px 18px;margin:18px 0;">
  <p><strong style="color:#1a1a1a;">2. Mesin Presensi Kartu RFID di Sekolah atau Kantor</strong></p>
  <p>Ketika kamu menempelkan kartu siswa ke mesin absensi di depan pintu gerbang, nomor unik kartu dibaca oleh sensor radio (RFID), dikirimkan seketika via Wi-Fi kantor ke database sekolah, dan layar LCD langsung menyapa: <em>"Selamat pagi, kehadiranmu tercatat pukul 06.50 WIB!"</em></p>
</div>

<div style="background:#F5F5F0;border-left:4px solid #2979FF;padding:14px 18px;margin:18px 0;">
  <p><strong style="color:#1a1a1a;">3. Pendingin Ruangan (AC) Pintar dengan Kontrol HP</strong></p>
  <p>Saat kamu masih dalam perjalanan pulang di dalam kereta dan merasa gerah, kamu membuka aplikasi di ponselmu dan menekan tombol <em>"Nyalakan AC 22 °C"</em>. Melalui internet, perintah tersebut melesat ke modul Wi-Fi AC di kamarmu, sehingga begitu kamu membuka pintu rumah, kamarmu sudah sejuk dan nyaman.</p>
</div>

<h2>Kuis pemahaman modul M-01 (Micro-Quiz)</h2>
<p>Yuk uji pemahamanmu terhadap konsep dasar yang baru saja kita bahas! Ujilah analisismu pada 3 pertanyaan di bawah ini:</p>

<div style="background:#FFFFFF;border:2px solid #1a1a1a;border-radius:8px;padding:18px;margin:20px 0;">
  <p><span style="background:#2979FF;color:#FFFFFF;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700;margin-right:6px;">Soal 1 dari 3</span> <strong>Pertanyaan:</strong> Manakah karakteristik utama yang membedakan termometer digital biasa di apotek dengan termometer berbasis IoT?</p>
  <ul>
    <li>⚪ A. Termometer IoT tidak menggunakan daya baterai.</li>
    <li>⚪ B. Termometer IoT mengirimkan data suhu ke jaringan/server sehingga riwayatnya bisa dipantau dari jarak jauh.</li>
    <li>⚪ C. Termometer IoT layarnya pasti berukuran raksasa.</li>
    <li>⚪ D. Termometer IoT tidak membutuhkan sensor suhu fisik.</li>
  </ul>
  <div style="background:#F5F5F0;border-left:4px solid #2E7D32;border-radius:0 6px 6px 0;padding:12px 16px;margin-top:10px;">
    <p><strong style="color:#2E7D32;">Kunci Jawaban: B</strong></p>
    <p><strong>Pembahasan:</strong> Termometer biasa bersifat tertutup (hanya menampilkan angka sesaat di layarnya lalu hilang). Sebaliknya, perangkat IoT memiliki pilar konektivitas yang mengirim data ke server, memungkinkan penyimpanan histori data jangka panjang dan pemantauan jarak jauh via aplikasi web atau ponsel.</p>
  </div>
</div>

<div style="background:#FFFFFF;border:2px solid #1a1a1a;border-radius:8px;padding:18px;margin:20px 0;">
  <p><span style="background:#2979FF;color:#FFFFFF;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700;margin-right:6px;">Soal 2 dari 3</span> <strong>Pertanyaan:</strong> Dalam analogi tubuh manusia pada sistem IoT, komponen mikrokontroler (seperti ESP32) berperan sebagai apa?</p>
  <ul>
    <li>⚪ A. Panca indra yang merasakan lingkungan fisik.</li>
    <li>⚪ B. Otak pemroses yang membaca sinyal listrik dan mengolahnya menjadi data angka teratur.</li>
    <li>⚪ C. Otot tangan dan kaki yang menggerakkan benda fisik.</li>
    <li>⚪ D. Pembuluh darah yang menyalurkan aliran listrik.</li>
  </ul>
  <div style="background:#F5F5F0;border-left:4px solid #2E7D32;border-radius:0 6px 6px 0;padding:12px 16px;margin-top:10px;">
    <p><strong style="color:#2E7D32;">Kunci Jawaban: B</strong></p>
    <p><strong>Pembahasan:</strong> Sensor adalah panca indra (mata, telinga, kulit), sedangkan mikrokontroler ESP32 adalah otak mini yang bertugas membaca sinyal dari sensor tersebut, menjalankan logika program, dan memutuskan data apa yang harus dikirim ke jaringan.</p>
  </div>
</div>

<div style="background:#FFFFFF;border:2px solid #1a1a1a;border-radius:8px;padding:18px;margin:20px 0;">
  <p><span style="background:#2979FF;color:#FFFFFF;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700;margin-right:6px;">Soal 3 dari 3</span> <strong>Pertanyaan:</strong> Apakah kamu wajib memiliki komputer mini Raspberry Pi untuk dapat menyelesaikan kurikulum Full Stack IoT Koding Indonesia ini?</p>
  <ul>
    <li>⚪ A. Wajib, karena ESP32 tidak bisa terhubung ke internet tanpa Raspberry Pi.</li>
    <li>⚪ B. Wajib membeli server cloud berbayar dari luar negeri.</li>
    <li>⚪ C. Tidak wajib; cukup sebuah papan ESP32 murah dan laptop/PC harian sudah mampu menyelesaikan 100% materi inti.</li>
    <li>⚪ D. Tidak bisa belajar sama sekali jika hanya memiliki komputer biasa.</li>
  </ul>
  <div style="background:#F5F5F0;border-left:4px solid #2E7D32;border-radius:0 6px 6px 0;padding:12px 16px;margin-top:10px;">
    <p><strong style="color:#2E7D32;">Kunci Jawaban: C</strong></p>
    <p><strong>Pembahasan:</strong> Kurikulum Koding Indonesia sengaja dirancang ramah kantong dan inklusif. Kamu cukup menggunakan satu papan ESP32 (~Rp 50.000) dan laptop sehari-hari untuk menyelesaikan 100% sistem dari sensor hingga dashboard web. Raspberry Pi hanyalah materi pelengkap super-upgrade mandiri di Babak 5 bagi yang memilikinya.</p>
  </div>
</div>

<h2>Rangkuman intisari &amp; langkah selanjutnya</h2>
<p>Selamat! Kamu telah menyelesaikan modul perdana dengan gemilang. Tiga hal terpenting yang wajib kamu bawa pulang hari ini adalah:</p>
<ol>
  <li><strong>IoT Menghubungkan Dua Alam:</strong> IoT adalah teknologi yang membawa kondisi fisik dunia nyata (panas, dingin, terang, gelap) ke dalam dunia internet dan komputasi digital.</li>
  <li><strong>Empat Pilar Abadi:</strong> Setiap sistem IoT di dunia—baik smart home sederhana hingga pabrik industri raksasa—selalu tersusun dari 4 pilar: <em>Sensor (Panca Indra)</em> ➔ <em>ESP32 (Otak)</em> ➔ <em>Jaringan &amp; MQTT (Kurir Pesan)</em> ➔ <em>Dashboard &amp; Relay (Layar &amp; Aksi)</em>.</li>
  <li><strong>Belajar Bertahap &amp; Ramah Kantong:</strong> Kita tidak memerlukan peralatan laboratorium berharga jutaan rupiah untuk memulai. Cukup ESP32, laptop, dan ketekunan belajarmu.</li>
</ol>

<p>Di <strong>Modul M-02</strong> berikutnya, kita akan melangkah lebih dekat dengan membedah <em>Anatomi Sistem IoT: Dari Sensor di Meja Hingga Tampilan di HP</em>. Kita akan melacak secara visual bagaimana sebuah elektron tegangan 3,3 Volt di breadboard bisa menjelma menjadi grafik angka yang memikat di layar smartphone-mu. Sampai jumpa di modul selanjutnya!</p>
HTML;
    }

    private function bodyEn(): string
    {
        return <<<'HTML'
<h2>Introduction — welcome to the Full Stack IoT learning track</h2>
<p>Welcome to <strong>Module M-01 (#71 (this article))</strong>, the opening milestone of our exciting journey through the Koding Indonesia <strong>Full Stack IoT Developer</strong> curriculum! If you have ever felt that the term <em>Internet of Things</em> (IoT) sounds overly complicated, intimidating, or reserved exclusively for high-tech laboratory engineers, this article is here to demystify it once and for all.</p>

<p>At its core, IoT is not about intimidating physics formulas or head-scratching mathematical calculations. IoT is simply the art of <strong>bridging the tangible physical world we interact with daily to the digital realm of the internet</strong>. In this foundational module, we will establish the mindset of a modern IoT engineer using clear visual illustrations, relatable real-world analogies, and friendly, jargon-free explanations.</p>

<blockquote>
  <p><strong>Track Status:</strong> This module is an <em>Official Draft</em>. As a learner, you do not need to rush out and purchase any electronic hardware today. Simply enjoy the reading, absorb the conceptual foundations, and follow the progression step by step.</p>
</blockquote>

<h2>Tools used in this article (Tools-First)</h2>
<p>At Koding Indonesia, we adhere strictly to a <em>tools-first</em> pedagogical approach: before presenting conceptual walkthroughs or syntax commands, we always specify what software tools you need to open first so you never feel lost before your screen.</p>

<div style="background:#F5F5F0;border:2px solid #1a1a1a;border-radius:8px;padding:16px;margin:20px 0;">
  <h3 style="margin-top:0;color:#1a1a1a;">Toolkit for Module M-01</h3>
  <ol style="margin-bottom:0;padding-left:20px;color:#1a1a1a;line-height:1.7;">
    <li><strong>A Standard Web Browser:</strong> Open Google Chrome, Mozilla Firefox, Microsoft Edge, or Safari on your desktop PC, laptop, or smartphone.</li>
    <li><strong>A Simple Notepad Application:</strong> Open Windows Notepad, Mac TextEdit, or grab a physical sheet of paper to brainstorm smart devices around your living space.</li>
    <li><strong>No Extra Software Required:</strong> For this introductory module, you <em>do not need</em> to install any code editors or connect any electrical wiring yet. We focus entirely on building your technical intuition first.</li>
  </ol>
</div>

<div style="background:#FFFFFF;border:2px solid #1a1a1a;border-radius:8px;padding:16px;margin:20px 0;">
  <p><strong style="color:#1a1a1a;">Your First Hands-On Check: Running a Command in the Browser</strong></p>
  <p>Before handling physical hardware in upcoming modules, let us verify that your web browser is ready to execute digital instructions without installing any extra software:</p>
  <ol>
    <li>Open a new tab in your browser (Google Chrome or Microsoft Edge).</li>
    <li>Press <code>F12</code> on your keyboard (or right-click anywhere on the page and select <strong>Inspect</strong>).</li>
    <li>Click the <strong>Console</strong> tab on the developer tools panel that appears.</li>
    <li>Type the following single command line and hit <strong>Enter</strong>:</li>
  </ol>
  <pre><code class="language-javascript">console.log("Hello Full Stack IoT World!");</code></pre>
  <p>You will immediately see that greeting message printed on the console screen! This confirms that your everyday workstation already possesses a built-in code engine ready to power future IoT web dashboards.</p>
</div>

<h2>Ordinary electronics vs connected IoT devices</h2>
<p>Let us begin with everyday devices around us. You have certainly seen a digital oral thermometer purchased at a local pharmacy, or a desktop electric fan featuring mechanical speed buttons 1, 2, and 3. Can these devices be classified as IoT?</p>

<p>The clear answer is <strong>no</strong>. They are <em>isolated conventional electronic appliances</em>. Why?</p>

<ul>
  <li><strong>Standard Digital Thermometer:</strong> A thermal probe measures body temperature, renders 36.5 °C on its tiny monochrome LCD, and shuts off. That data disappears into thin air. The device has zero memory of readings taken five hours ago, cannot alert a distant physician, and cannot trigger automated climate control.</li>
  <li><strong>Standard Desktop Fan:</strong> Its motor only spins when human fingers press a physical latch. The fan remains indifferent whether your work room is stifling like a sauna or freezing cold unless a human physically intervenes.</li>
</ul>

<p>Now, consider what happens when we elevate these appliances with the power of the <strong>Internet of Things</strong>:</p>

<ul>
  <li><strong>An IoT-Enabled Temperature Station:</strong> As soon as the sensor detects ambient temperature climbing past 32 °C, the metric is not merely shown locally. It is instantly dispatched across a Wi-Fi network to a server database. Within split seconds, live telemetry graphs on your smartphone refresh, accompanied by an instant Telegram alert: <em>"Notice: Workspace temperature has exceeded comfortable study thresholds!"</em></li>
  <li><strong>An IoT-Enabled Climate Controller:</strong> Without requiring human intervention, an automated server rule sends a control packet back to an electromechanical relay switch on your desk, triggering your fan to spin until ambient temperatures drop comfortably back to 25 °C.</li>
</ul>

<figure style="margin:28px 0;background:#F5F5F0;border:2px solid #1a1a1a;border-radius:8px;padding:20px;text-align:center;">
  <svg viewBox="0 0 760 320" width="100%" height="100%" style="max-width:760px;font-family:'Space Grotesk',system-ui,sans-serif;" aria-label="Comparison Diagram: Conventional Electronics vs Connected IoT Systems">
    <defs>
      <marker id="arrowM01AEn" markerWidth="10" markerHeight="10" refX="6" refY="3" orient="auto">
        <path d="M0,0 L0,6 L9,3 z" fill="#2979FF" />
      </marker>
      <marker id="arrowM01GrayEn" markerWidth="10" markerHeight="10" refX="6" refY="3" orient="auto">
        <path d="M0,0 L0,6 L9,3 z" fill="#757575" />
      </marker>
    </defs>

    <!-- Left Panel: Conventional -->
    <rect x="20" y="20" width="340" height="270" rx="8" fill="#FFFFFF" stroke="#E0E0E0" stroke-width="2" />
    <rect x="20" y="20" width="340" height="42" rx="8" fill="#EEEEEE" stroke="#E0E0E0" stroke-width="2" />
    <text x="190" y="47" text-anchor="middle" font-size="15" font-weight="700" fill="#424242">Conventional Electronics (Isolated)</text>

    <!-- Components Left -->
    <rect x="45" y="85" width="120" height="55" rx="6" fill="#F5F5F5" stroke="#9E9E9E" stroke-width="1.5" />
    <text x="105" y="110" text-anchor="middle" font-size="13" font-weight="600" fill="#212121">Physical Sensor</text>
    <text x="105" y="128" text-anchor="middle" font-size="11" fill="#616161">Measures Temp</text>

    <line x1="165" y1="112" x2="215" y2="112" stroke="#757575" stroke-width="2" marker-end="url(#arrowM01GrayEn)" />

    <rect x="225" y="85" width="115" height="55" rx="6" fill="#F5F5F5" stroke="#9E9E9E" stroke-width="1.5" />
    <text x="282" y="110" text-anchor="middle" font-size="13" font-weight="600" fill="#212121">Local Display</text>
    <text x="282" y="128" text-anchor="middle" font-size="11" fill="#616161">Shows Digits</text>

    <rect x="45" y="175" width="295" height="85" rx="6" fill="#FFFDE7" stroke="#FBC02D" stroke-width="1.5" />
    <text x="192" y="202" text-anchor="middle" font-size="13" font-weight="700" fill="#F57F17">Limitations:</text>
    <text x="192" y="224" text-anchor="middle" font-size="12" fill="#5D4037">• Data is lost immediately (no history)</text>
    <text x="192" y="244" text-anchor="middle" font-size="12" fill="#5D4037">• Requires physical human presence</text>

    <!-- Right Panel: IoT -->
    <rect x="400" y="20" width="340" height="270" rx="8" fill="#FFFFFF" stroke="#2979FF" stroke-width="2.5" />
    <rect x="400" y="20" width="340" height="42" rx="8" fill="#E3F2FD" stroke="#2979FF" stroke-width="2" />
    <text x="570" y="47" text-anchor="middle" font-size="15" font-weight="700" fill="#1565C0">IoT Architecture (Connected)</text>

    <!-- Components Right -->
    <rect x="420" y="85" width="85" height="55" rx="6" fill="#F5F5F5" stroke="#1E88E5" stroke-width="1.5" />
    <text x="462" y="110" text-anchor="middle" font-size="12" font-weight="600" fill="#0D47A1">Sensor</text>
    <text x="462" y="128" text-anchor="middle" font-size="11" fill="#546E7A">Read Temp</text>

    <line x1="505" y1="112" x2="530" y2="112" stroke="#2979FF" stroke-width="2" marker-end="url(#arrowM01AEn)" />

    <rect x="535" y="85" width="85" height="55" rx="6" fill="#F5F5F5" stroke="#1E88E5" stroke-width="1.5" />
    <text x="577" y="110" text-anchor="middle" font-size="12" font-weight="600" fill="#0D47A1">ESP32</text>
    <text x="577" y="128" text-anchor="middle" font-size="11" fill="#546E7A">Process &amp; Wi-Fi</text>

    <line x1="620" y1="112" x2="645" y2="112" stroke="#2979FF" stroke-width="2" marker-end="url(#arrowM01AEn)" />

    <rect x="650" y="85" width="75" height="55" rx="6" fill="#F5F5F5" stroke="#1E88E5" stroke-width="1.5" />
    <text x="687" y="110" text-anchor="middle" font-size="12" font-weight="600" fill="#0D47A1">Cloud / PC</text>
    <text x="687" y="128" text-anchor="middle" font-size="11" fill="#546E7A">Database</text>

    <rect x="420" y="175" width="305" height="85" rx="6" fill="#E8F5E9" stroke="#4CAF50" stroke-width="1.5" />
    <text x="572" y="202" text-anchor="middle" font-size="13" font-weight="700" fill="#2E7D32">IoT Strengths:</text>
    <text x="572" y="224" text-anchor="middle" font-size="12" fill="#1B5E20">• Historical data stored for deep analysis</text>
    <text x="572" y="244" text-anchor="middle" font-size="12" fill="#1B5E20">• Remote control &amp; live mobile dashboards</text>
  </svg>
  <figcaption style="font-size:13px;color:#616161;margin-top:12px;font-style:italic;">Figure 1.1: Conventional isolated electronic workflows versus connected Internet of Things system architectures. (Source: Original Design by Koding Indonesia Curriculum Team)</figcaption>
</figure>

<h2>Four core pillars of IoT — the human body analogy</h2>
<p>To prevent feeling overwhelmed by complex technical architecture diagrams, seasoned engineers summarize all IoT architectures into <strong>4 core pillars</strong>. The most intuitive way to remember them is through the <strong>human body analogy</strong>:</p>

<div style="background:#FFFFFF;border:2px solid #1a1a1a;border-radius:8px;padding:18px;margin:20px 0;">
  <h3 style="margin-top:0;color:#2979FF;">1. Sensory Organs ➔ Physical Sensors</h3>
  <p style="margin-bottom:0;line-height:1.7;">Human eyes capture light intensity, skin detects thermal heat, and ears hear auditory sound waves. In the IoT ecosystem, biological organs are represented by <strong>Sensors</strong>. Common examples include the <em>DHT22</em> probe measuring ambient temperature and relative humidity, or an <em>LDR</em> photoresistor detecting ambient room brightness.</p>
</div>

<div style="background:#FFFFFF;border:2px solid #1a1a1a;border-radius:8px;padding:18px;margin:20px 0;">
  <h3 style="margin-top:0;color:#2979FF;">2. Central Processing Brain ➔ Microcontroller (ESP32)</h3>
  <p style="margin-bottom:0;line-height:1.7;">When fingertips touch a hot mug, electrical nerve impulses race to the brain to be translated: <em>"Caution, this fluid is 80 degrees Celsius!"</em> In our system, this central processing role is handled by the <strong>ESP32 Microcontroller</strong>. This thumb-sized dual-core SoC reads raw sensor voltages, formats them into structured data, and coordinates all downstream logic.</p>
</div>

<div style="background:#FFFFFF;border:2px solid #1a1a1a;border-radius:8px;padding:18px;margin:20px 0;">
  <h3 style="margin-top:0;color:#2979FF;">3. Nervous System &amp; Message Courier ➔ Connectivity (Wi-Fi &amp; MQTT)</h3>
  <p style="margin-bottom:0;line-height:1.7;">Once information is processed, how does it travel to remote destinations? Through <strong>Network Connectivity</strong>. The ESP32 features integrated 2.4 GHz Wi-Fi radios communicating through an ultra-efficient protocol named <strong>MQTT</strong>. Picture MQTT as a lightning-fast courier delivering telemetry packets from your desk to local or cloud servers without wasting bandwidth.</p>
</div>

<div style="background:#FFFFFF;border:2px solid #1a1a1a;border-radius:8px;padding:18px;margin:20px 0;">
  <h3 style="margin-top:0;color:#2979FF;">4. Monitoring Display &amp; Physical Muscles ➔ Web Dashboards &amp; Actuators (Relays)</h3>
  <p style="margin-bottom:0;line-height:1.7;">Data holds little value unless it empowers human understanding or triggers physical action. This fourth pillar spans two domains:
  <br>• <strong>Monitoring Displays (Web PWA Dashboard):</strong> Clean, responsive graphical dashboards rendered in web browsers on laptops and mobile devices.
  <br>• <strong>Mechanical Muscles (Actuators / Relay Modules):</strong> Physical electromechanical switches capable of toggling desk lamps, moving servo motor arms, or switching irrigation water pumps upon receiving commands.</p>
</div>

<figure style="margin:28px 0;background:#F5F5F0;border:2px solid #1a1a1a;border-radius:8px;padding:20px;text-align:center;">
  <svg viewBox="0 0 760 260" width="100%" height="100%" style="max-width:760px;font-family:'Space Grotesk',system-ui,sans-serif;" aria-label="Four Core Pillars of IoT Diagram">
    <defs>
      <marker id="arrowPilarEn" markerWidth="10" markerHeight="10" refX="6" refY="3" orient="auto">
        <path d="M0,0 L0,6 L9,3 z" fill="#1a1a1a" />
      </marker>
    </defs>

    <!-- Card 1 -->
    <rect x="15" y="40" width="160" height="170" rx="8" fill="#FFFFFF" stroke="#1a1a1a" stroke-width="2" />
    <rect x="15" y="40" width="160" height="36" rx="8" fill="#E3F2FD" stroke="#1a1a1a" stroke-width="2" />
    <text x="95" y="64" text-anchor="middle" font-size="13" font-weight="700" fill="#1565C0">1. Sensory Organs</text>
    <text x="95" y="115" text-anchor="middle" font-size="16" font-weight="700" fill="#1a1a1a">SENSORS</text>
    <text x="95" y="145" text-anchor="middle" font-size="12" fill="#616161">DHT22, LDR, PIR</text>
    <text x="95" y="185" text-anchor="middle" font-size="11" font-weight="600" fill="#2E7D32">Perceive Physics</text>

    <!-- Arrow 1 to 2 -->
    <line x1="175" y1="125" x2="200" y2="125" stroke="#1a1a1a" stroke-width="2.5" marker-end="url(#arrowPilarEn)" />

    <!-- Card 2 -->
    <rect x="205" y="40" width="160" height="170" rx="8" fill="#FFFFFF" stroke="#1a1a1a" stroke-width="2" />
    <rect x="205" y="40" width="160" height="36" rx="8" fill="#EDE7F6" stroke="#1a1a1a" stroke-width="2" />
    <text x="285" y="64" text-anchor="middle" font-size="13" font-weight="700" fill="#512DA8">2. Mini Brain</text>
    <text x="285" y="115" text-anchor="middle" font-size="16" font-weight="700" fill="#1a1a1a">ESP32 SoC</text>
    <text x="285" y="145" text-anchor="middle" font-size="12" fill="#616161">C++ Firmware</text>
    <text x="285" y="185" text-anchor="middle" font-size="11" font-weight="600" fill="#2E7D32">Process Logic</text>

    <!-- Arrow 2 to 3 -->
    <line x1="365" y1="125" x2="390" y2="125" stroke="#1a1a1a" stroke-width="2.5" marker-end="url(#arrowPilarEn)" />

    <!-- Card 3 -->
    <rect x="395" y="40" width="160" height="170" rx="8" fill="#FFFFFF" stroke="#1a1a1a" stroke-width="2" />
    <rect x="395" y="40" width="160" height="36" rx="8" fill="#FFF3E0" stroke="#1a1a1a" stroke-width="2" />
    <text x="475" y="64" text-anchor="middle" font-size="13" font-weight="700" fill="#E65100">3. Fast Courier</text>
    <text x="475" y="115" text-anchor="middle" font-size="16" font-weight="700" fill="#1a1a1a">Wi-Fi &amp; MQTT</text>
    <text x="475" y="145" text-anchor="middle" font-size="12" fill="#616161">JSON Payloads</text>
    <text x="475" y="185" text-anchor="middle" font-size="11" font-weight="600" fill="#2E7D32">Dispatch Packets</text>

    <!-- Arrow 3 to 4 -->
    <line x1="555" y1="125" x2="580" y2="125" stroke="#1a1a1a" stroke-width="2.5" marker-end="url(#arrowPilarEn)" />

    <!-- Card 4 -->
    <rect x="585" y="40" width="160" height="170" rx="8" fill="#FFFFFF" stroke="#1a1a1a" stroke-width="2" />
    <rect x="585" y="40" width="160" height="36" rx="8" fill="#E8F5E9" stroke="#1a1a1a" stroke-width="2" />
    <text x="665" y="64" text-anchor="middle" font-size="13" font-weight="700" fill="#2E7D32">4. Eyes &amp; Muscle</text>
    <text x="665" y="115" text-anchor="middle" font-size="16" font-weight="700" fill="#1a1a1a">DASHBOARD</text>
    <text x="665" y="145" text-anchor="middle" font-size="12" fill="#616161">Web PWA &amp; Relay</text>
    <text x="665" y="185" text-anchor="middle" font-size="11" font-weight="600" fill="#2E7D32">Monitor &amp; Actuate</text>
  </svg>
  <figcaption style="font-size:13px;color:#616161;margin-top:12px;font-style:italic;">Figure 1.2: Four fundamental pillars of Full Stack IoT architecture: from physical environmental sensing to web dashboard telemetry. (Source: Original Design by Koding Indonesia Curriculum Team, adapted from IEEE IoT architectural standards)</figcaption>
</figure>

<h2>Case study domain — the Smart Study Desk Station</h2>
<p>Rather than studying abstract theoretical concepts such as offshore nuclear reactors or aerospace satellites that you rarely touch, our entire 73-module curriculum is anchored around an engaging, practical project built right on your workspace: the <strong>Smart Study Desk Station</strong>.</p>

<p>Picture a compact enclosure resting on your desk operating autonomously 24 hours a day:</p>
<ol>
  <li>It continuously tracks temperature and humidity levels to help keep you alert and prevent sluggishness during study sessions.</li>
  <li>It reads ambient lighting: as dusk falls and shadows deepen, it automatically illuminates an LED desk lamp smoothly.</li>
  <li>It checks for human occupancy via passive infrared motion sensors. If you step away to sleep and the desk remains unoccupied for 15 minutes, appliances shut off automatically to conserve power.</li>
  <li>It streams real-time telemetry to an elegant web app on your phone, letting you verify desk climate anywhere across the internet.</li>
</ol>

<p>Most importantly: <strong>this curriculum is 100% hybrid and inclusive</strong>. An affordable <em>ESP32-DevKitC-1</em> board costing approximately $4 alongside your everyday laptop is completely sufficient to build this entire ecosystem from end to end! If you happen to own a <em>Raspberry Pi</em> (or an old repurposed laptop), Chapter 5 provides dedicated super-upgrades to transform your desk setup into an industrial-grade edge server.</p>

<h2>Hands-on exercise — mapping 3 smart devices in your daily life</h2>
<p>Before moving on, take two minutes to look around your surroundings and identify how three familiar devices map into the four IoT pillars:</p>

<div style="background:#F5F5F0;border-left:4px solid #2979FF;padding:14px 18px;margin:18px 0;">
  <p><strong style="color:#1a1a1a;">1. Automated Municipal Street Lights</strong></p>
  <p>Have you ever observed highway lampposts turning on precisely at sunset and turning off at dawn? The sensory organ is a <em>light-dependent resistor (LDR)</em>, the brain is a compact controller inside the pole casing, and the muscle is an electrical relay connecting high-voltage mains power.</p>
</div>

<div style="background:#F5F5F0;border-left:4px solid #2979FF;padding:14px 18px;margin:18px 0;">
  <p><strong style="color:#1a1a1a;">2. School or Office RFID Attendance Terminals</strong></p>
  <p>When tapping an ID badge against an entrance scanner, the unique token ID is captured by an RFID reader, transmitted instantly via office Wi-Fi to human resources databases, and an LCD screen greets you: <em>"Good morning, check-in logged at 07:45 AM!"</em></p>
</div>

<div style="background:#F5F5F0;border-left:4px solid #2979FF;padding:14px 18px;margin:18px 0;">
  <p><strong style="color:#1a1a1a;">3. Smart Air Conditioner with Mobile App Controls</strong></p>
  <p>While commuting home on a sweltering afternoon, you open your smartphone app and tap <em>"Cool to 22 °C"</em>. Across the internet, control packets reach your bedroom AC Wi-Fi module so your living space is chilled before you even step through the front door.</p>
</div>

<h2>Module M-01 understanding check (Micro-Quiz)</h2>
<p>Let us test your grasp of the core concepts introduced today! Test your analysis on these three multiple-choice questions below:</p>

<div style="background:#FFFFFF;border:2px solid #1a1a1a;border-radius:8px;padding:18px;margin:20px 0;">
  <p><span style="background:#2979FF;color:#FFFFFF;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700;margin-right:6px;">Question 1 of 3</span> <strong>Question:</strong> What is the primary characteristic distinguishing a standard pharmacy digital thermometer from a connected IoT temperature monitor?</p>
  <ul>
    <li>⚪ A. IoT thermometers never require battery power.</li>
    <li>⚪ B. IoT thermometers transmit temperature data over networks/servers for remote tracking and historical analysis.</li>
    <li>⚪ C. IoT thermometers always feature giant physical displays.</li>
    <li>⚪ D. IoT thermometers do not require physical temperature sensors.</li>
  </ul>
  <div style="background:#F5F5F0;border-left:4px solid #2E7D32;border-radius:0 6px 6px 0;padding:12px 16px;margin-top:10px;">
    <p><strong style="color:#2E7D32;">Correct Answer: B</strong></p>
    <p><strong>Explanation:</strong> Conventional thermometers are isolated devices displaying values locally before disappearing. IoT devices integrate network connectivity pillars to archive telemetry in databases, enabling long-term analytics and remote monitoring via web and mobile interfaces.</p>
  </div>
</div>

<div style="background:#FFFFFF;border:2px solid #1a1a1a;border-radius:8px;padding:18px;margin:20px 0;">
  <p><span style="background:#2979FF;color:#FFFFFF;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700;margin-right:6px;">Question 2 of 3</span> <strong>Question:</strong> In the human body analogy of IoT architectures, what role does a microcontroller (such as the ESP32) serve?</p>
  <ul>
    <li>⚪ A. Sensory organs that perceive raw environmental properties.</li>
    <li>⚪ B. The central brain that samples electrical signals and processes them into structured numbers.</li>
    <li>⚪ C. Muscular limbs that exert physical mechanical movement.</li>
    <li>⚪ D. Circulatory blood vessels delivering electric current.</li>
  </ul>
  <div style="background:#F5F5F0;border-left:4px solid #2E7D32;border-radius:0 6px 6px 0;padding:12px 16px;margin-top:10px;">
    <p><strong style="color:#2E7D32;">Correct Answer: B</strong></p>
    <p><strong>Explanation:</strong> Sensors act as sensory organs, while the ESP32 serves as the embedded brain executing firmware logic, parsing electrical sensor signals, and dispatching formatted telemetry to networks.</p>
  </div>
</div>

<div style="background:#FFFFFF;border:2px solid #1a1a1a;border-radius:8px;padding:18px;margin:20px 0;">
  <p><span style="background:#2979FF;color:#FFFFFF;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700;margin-right:6px;">Question 3 of 3</span> <strong>Question:</strong> Are you strictly required to purchase a Raspberry Pi single-board computer to complete this Full Stack IoT curriculum?</p>
  <ul>
    <li>⚪ A. Yes, because an ESP32 cannot connect to the internet without a Raspberry Pi.</li>
    <li>⚪ B. Yes, you must also purchase expensive international cloud servers.</li>
    <li>⚪ C. No; an affordable ESP32 board paired with your everyday PC/laptop can complete 100% of the core curriculum.</li>
    <li>⚪ D. No learning can happen with standard computers.</li>
  </ul>
  <div style="background:#F5F5F0;border-left:4px solid #2E7D32;border-radius:0 6px 6px 0;padding:12px 16px;margin-top:10px;">
    <p><strong style="color:#2E7D32;">Correct Answer: C</strong></p>
    <p><strong>Explanation:</strong> The curriculum is deliberately designed for maximum inclusivity. A single $4 ESP32 board and your personal workstation are all you need to complete 100% of the hardware, network, backend, and dashboard pipeline. Raspberry Pi is an optional super-upgrade covered in Chapter 5.</p>
  </div>
</div>

<h2>Summary takeaways &amp; upcoming roadmap</h2>
<p>Congratulations! You have completed the inaugural module with flying colors. Here are the three most vital takeaways to retain:</p>
<ol>
  <li><strong>IoT Bridges Two Realms:</strong> IoT connects physical real-world phenomena (thermal heat, light, motion) with the digital universe of computing and the internet.</li>
  <li><strong>Four Universal Pillars:</strong> Every IoT system on Earth—from simple smart desks to industrial smart cities—relies on four pillars: <em>Sensors (Organs)</em> ➔ <em>ESP32 (Brain)</em> ➔ <em>Wi-Fi &amp; MQTT (Courier)</em> ➔ <em>Dashboard &amp; Relays (Display &amp; Muscles)</em>.</li>
  <li><strong>Budget-Friendly, Step-by-Step Learning:</strong> You do not need thousands of dollars in high-end laboratory gear to begin. All it takes is an ESP32, your everyday laptop, and consistent curiosity.</li>
</ol>

<p>In upcoming <strong>Module M-02</strong>, we will zoom in closer with <em>System Anatomy of IoT: From Desk Sensors to Smartphone Screens</em>. We will visually trace how a tiny 3.3V electrical voltage on a breadboard transforms into an animated live chart on your smartphone screen. See you in the next module!</p>
HTML;
    }
}
