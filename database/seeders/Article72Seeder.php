<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article72Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first()
            ?? Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $iotCat) {
            throw new \RuntimeException('User admin atau kategori iot-smart-device tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-mengenal-kotak-perkakas';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        foreach ([
            'fullstack-iot' => 'Fullstack IoT',
            'iot'           => 'IoT',
            'esp32'         => 'ESP32',
            'hardware'      => 'Hardware',
        ] as $tagSlug => $tagName) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagName]);
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'            => $admin->id,
                'category_id'        => $iotCat->id,
                'title'              => 'Mengenal Kotak Perkakas: ESP32-DevKitC-1 & Komponen Kit',
                'title_en'           => 'Getting Familiar with Hardware: ESP32-DevKitC-1 & Starter Kit',
                'excerpt'            => 'Modul M-02 (#72): bongkar kotak perkakas starter kit IoT — kenali anatomi fisik ESP32, antena PCB, breadboard, LED, resistor, sensor, relay, dan kuis interaktif.',
                'excerpt_en'         => 'Module M-02 (#72): unbox your IoT starter kit — explore ESP32 physical anatomy, PCB antenna, breadboard, LEDs, resistors, sensors, relays, and interactive quiz.',
                'body'               => $this->body(),
                'body_en'            => $this->bodyEn(),
                'status'             => 'draft',
                'published_at'       => null,
                'is_featured'        => false,
                'seo_title'          => 'Anatomi Board ESP32 & Starter Kit IoT untuk Pemula',
                'seo_title_en'       => 'ESP32-DevKitC-1 Board Anatomy & IoT Starter Kit Guide',
                'seo_description'    => 'Kenali fisik ESP32-DevKitC-1, pin header, antena PCB, serta 8 komponen starter kit (breadboard, resistor, LED, sensor, relay) secara visual dan ramah awam.',
                'seo_description_en' => 'Explore the physical anatomy of the ESP32-DevKitC-1, pin headers, PCB antenna, and 8 essential starter kit components (breadboard, LEDs, sensors, and relays).',
            ]
        );

        $tagIds = Tag::whereIn('slug', ['fullstack-iot', 'iot', 'esp32', 'hardware'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Seeder Modul M-02 (#72) berhasil disimpan sebagai DRAFT: '.$article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — membongkar kotak perkakas pertama kita</h2>
<p>Selamat datang di <strong>Modul M-02 (#72 (ini))</strong> dalam perjalanan kurikulum <strong>Full Stack IoT Developer</strong> Koding Indonesia! Setelah di <a href="/artikel/fullstack-iot-pintu-masuk-iot">Modul M-01 (#71)</a> kita memahami konsep dasar IoT dan analogi 4 pilar tubuh manusia, hari ini kita akan melangkah lebih nyata dengan membongkar kotak perkakas (<em>starter kit</em>) dan membedah wujud fisik dari pahlawan utama kita: papan mikrokontroler <strong>ESP32-DevKitC-1</strong>.</p>

<p>Bagi orang awam yang baru pertama kali melihat papan sirkuit hijau atau hitam dengan kaki-kaki jarum logam dan komponen elektronik kecil, wajar sekali jika muncul sedikit rasa ragu: <em>"Apakah alat ini gampang rusak kalau salah pegang? Apakah saya bisa kesetrum?"</em></p>

<p>Tarik napas dalam-dalam dan rileks. Semua peralatan elektronika IoT yang kita gunakan di Babak 1 beroperasi pada tegangan listrik searah (DC) yang sangat rendah, yaitu <strong>3,3 Volt dan 5 Volt</strong>. Tegangan ini dialirkan aman melalui kabel USB laptopmu—tidak ada sengatan listrik berbahaya bagi tubuh dan dirancang kuat untuk eksperimen belajar. Di modul ini, kita akan berkenalan dengan setiap sudut papan ESP32 dan 8 komponen kit dasar secara praktis, santai, dan visual!</p>

<blockquote>
  <p><strong>Status Jalur:</strong> Modul ini berstatus <em>Draft Resmi</em>. Jika kamu belum membeli alat fisik apa pun hari ini, jangan khawatir! Seluruh penjelasan di bawah dilengkapi diagram resolusi tinggi beranotasi lengkap sehingga kamu tetap bisa menyerap 100% materinya.</p>
</blockquote>

<h2>Alat yang disiapkan hari ini (Tools-First)</h2>
<p>Sebelum kita membedah bagian demi bagian, mari siapkan meja belajarmu sesuai panduan <em>tools-first</em>:</p>

<div class="fsiot-card">
  <h3 style="margin-top:0;color:#1a1a1a;">Daftar Alat untuk Modul M-02</h3>
  <ol style="margin-bottom:0;padding-left:20px;line-height:1.7;">
    <li><strong>Kotak Komponen Starter Kit ESP32:</strong> Jika kamu sudah membelinya, letakkan kotak tersebut di mejamu dan buka tutupnya dengan rapi.</li>
    <li><strong>Peramban Web (Laptop / HP):</strong> Gunakan peramban untuk melihat diagram visual beresolusi tajam pada artikel ini.</li>
    <li><strong>Buku Catatan Saku atau Aplikasi Catatan:</strong> Untuk mencatat ciri fisik unik dari masing-masing komponen agar kamu cepat hafal.</li>
    <li><strong>Belum Butuh Kabel USB Dicolok:</strong> Hari ini kita murni melakukan <em>inspeksi visual</em> tanpa menyambungkan listrik apa pun. Sangat santai dan aman!</li>
  </ol>
</div>

<h2>Anatomi fisik board ESP32-DevKitC-1 — apa saja isinya?</h2>
<p>Mari ambil papan <strong>ESP32-DevKitC-1</strong> di tanganmu (atau amati dengan saksama pada diagram di bawah). Papan mungil seukuran dua ruas jari ini memiliki 6 bagian vital yang perlu kamu kenali:</p>

<div class="fsiot-card">
  <h3 style="margin-top:0;color:#2979FF;">1. Chip Logam Perak (ESP-WROOM-32)</h3>
  <p style="margin-bottom:0;line-height:1.7;">Kotak logam persegi panjang berwarna perak di tengah board adalah penutup pelindung elektromagnetik untuk chip utama ESP32. Apa itu SoC (System on Chip)? Bayangkan seluruh komponen penting komputer—prosesor dual-core 240 MHz, memori kerja SRAM 520 KB, antena nirkabel Wi-Fi/Bluetooth, dan penyimpanan flash 4 MB—semuanya dipadatkan menjadi satu keping silikon mini seukuran kuku jari!</p>
</div>

<div class="fsiot-card">
  <h3 style="margin-top:0;color:#2979FF;">2. Antena Emas Berkelok-kelok (PCB Antenna)</h3>
  <p style="margin-bottom:0;line-height:1.7;">Di ujung atas papan terdapat jalur tembaga/emas meliuk-liuk seperti labirin. Itu bukan hiasan grafis, melainkan <strong>antena radio Wi-Fi 2.4 GHz dan Bluetooth BLE bawaan</strong>! Karena antena ini tercetak langsung di atas papan sirkuit (PCB), ESP32 bisa memancarkan dan menerima sinyal internet tanpa butuh antena tiang luar yang merepotkan.</p>
</div>

<div class="fsiot-card">
  <h3 style="margin-top:0;color:#2979FF;">3. Port USB (Micro-USB atau Type-C)</h3>
  <p style="margin-bottom:0;line-height:1.7;">Terletak di ujung bawah papan. Port ini memiliki dua fungsi sakti sekaligus: menyalurkan suplai daya listrik 5 Volt dari charger atau port USB laptop, serta menjadi jalur kabel transfer data untuk mengunggah baris kode program dari Arduino IDE ke dalam memori ESP32.</p>
</div>

<div class="fsiot-card">
  <h3 style="margin-top:0;color:#2979FF;">4. Chip Penerjemah USB-ke-UART (Jembatan Komunikasi)</h3>
  <p style="margin-bottom:0;line-height:1.7;">Di dekat port USB terdapat chip kotak hitam kecil (biasanya bertuliskan CP2102 atau CH340). Chip ini bertindak sebagai penerjemah bahasa: ia mengubah format data paket USB dari komputermu menjadi sinyal serial UART yang dimengerti langsung oleh prosesor ESP32. Komunikasi serial UART ini mirip dua orang yang saling berkirim pesan huruf demi huruf secara berurutan lewat sepasang jalur kabel pengirim (TX) dan penerima (RX).</p>
</div>

<div class="fsiot-card">
  <h3 style="margin-top:0;color:#2979FF;">5. Dua Tombol Fisik: EN (Reset) &amp; BOOT (Flash)</h3>
  <p style="margin-bottom:0;line-height:1.7;">Ada dua tombol klik mikro di samping port USB:
  <br>• <strong>Tombol EN (Enable / Reset):</strong> Berfungsi seperti tombol restart di komputermu. Jika ditekan, ESP32 akan menyala ulang dan mengeksekusi program dari baris pertama.
  <br>• <strong>Tombol BOOT:</strong> Digunakan untuk memaksa ESP32 masuk ke mode siap menerima kode program baru dari laptop.</p>
</div>

<div class="fsiot-card">
  <h3 style="margin-top:0;color:#2979FF;">6. Deretan Kaki Jarum Logam (Pin Header 30 / 38 Pin)</h3>
  <p style="margin-bottom:0;line-height:1.7;">Kaki-kaki jarum hitam di sisi kiri dan kanan adalah gerbang input/output (GPIO). Lewat pin-pin inilah ESP32 terhubung dengan kabel jumper untuk membaca sensor suhu, menyalakan lampu LED, mengontrol relay, atau menampilkan teks di layar OLED.</p>
</div>

<figure style="margin:28px 0;background:#F5F5F0;border:2px solid #1a1a1a;border-radius:8px;padding:20px;text-align:center;">
  <svg viewBox="0 0 760 360" width="100%" height="100%" style="max-width:760px;font-family:'Space Grotesk',system-ui,sans-serif;" aria-label="Diagram Anatomi Fisik ESP32-DevKitC-1">
    <defs>
      <marker id="arrowM02" markerWidth="10" markerHeight="10" refX="6" refY="3" orient="auto">
        <path d="M0,0 L0,6 L9,3 z" fill="#2979FF" />
      </marker>
    </defs>

    <!-- Board Body -->
    <rect x="230" y="20" width="300" height="320" rx="12" fill="#1E293B" stroke="#0F172A" stroke-width="3" />

    <!-- Pin Headers Kiri & Kanan -->
    <rect x="210" y="45" width="20" height="270" rx="4" fill="#0F172A" stroke="#475569" stroke-width="1.5" />
    <rect x="530" y="45" width="20" height="270" rx="4" fill="#0F172A" stroke="#475569" stroke-width="1.5" />
    <!-- Pin Dots -->
    <circle cx="220" cy="65" r="3.5" fill="#F59E0B" /><circle cx="220" cy="85" r="3.5" fill="#F59E0B" /><circle cx="220" cy="105" r="3.5" fill="#F59E0B" /><circle cx="220" cy="125" r="3.5" fill="#F59E0B" /><circle cx="220" cy="145" r="3.5" fill="#F59E0B" /><circle cx="220" cy="165" r="3.5" fill="#F59E0B" /><circle cx="220" cy="185" r="3.5" fill="#F59E0B" /><circle cx="220" cy="205" r="3.5" fill="#F59E0B" /><circle cx="220" cy="225" r="3.5" fill="#F59E0B" /><circle cx="220" cy="245" r="3.5" fill="#F59E0B" /><circle cx="220" cy="265" r="3.5" fill="#F59E0B" /><circle cx="220" cy="285" r="3.5" fill="#F59E0B" />
    <circle cx="540" cy="65" r="3.5" fill="#F59E0B" /><circle cx="540" cy="85" r="3.5" fill="#F59E0B" /><circle cx="540" cy="105" r="3.5" fill="#F59E0B" /><circle cx="540" cy="125" r="3.5" fill="#F59E0B" /><circle cx="540" cy="145" r="3.5" fill="#F59E0B" /><circle cx="540" cy="165" r="3.5" fill="#F59E0B" /><circle cx="540" cy="185" r="3.5" fill="#F59E0B" /><circle cx="540" cy="205" r="3.5" fill="#F59E0B" /><circle cx="540" cy="225" r="3.5" fill="#F59E0B" /><circle cx="540" cy="245" r="3.5" fill="#F59E0B" /><circle cx="540" cy="265" r="3.5" fill="#F59E0B" /><circle cx="540" cy="285" r="3.5" fill="#F59E0B" />

    <!-- Antena PCB Emas Atas -->
    <rect x="290" y="30" width="180" height="35" rx="4" fill="#0F172A" stroke="#B45309" stroke-width="1.5" />
    <path d="M 305 48 L 330 48 L 330 38 L 360 38 L 360 48 L 390 48 L 390 38 L 420 38 L 420 48 L 455 48" fill="none" stroke="#F59E0B" stroke-width="2.5" stroke-linecap="round" />

    <!-- Chip Logam ESP-WROOM-32 -->
    <rect x="280" y="80" width="200" height="135" rx="6" fill="#94A3B8" stroke="#CBD5E1" stroke-width="2" />
    <text x="380" y="135" text-anchor="middle" font-size="16" font-weight="700" fill="#0F172A">ESP-WROOM-32</text>
    <text x="380" y="158" text-anchor="middle" font-size="11" fill="#334155">Dual-Core 240MHz · 4MB Flash</text>
    <text x="380" y="176" text-anchor="middle" font-size="11" fill="#334155">Wi-Fi &amp; Bluetooth BLE</text>

    <!-- Chip USB-UART -->
    <rect x="350" y="235" width="60" height="40" rx="4" fill="#0F172A" stroke="#64748B" stroke-width="1.5" />
    <text x="380" y="259" text-anchor="middle" font-size="10" font-weight="600" fill="#94A3B8">CP2102</text>

    <!-- Tombol EN & BOOT -->
    <rect x="260" y="280" width="36" height="28" rx="4" fill="#CBD5E1" stroke="#475569" stroke-width="1.5" />
    <circle cx="278" cy="294" r="6" fill="#DC2626" />
    <text x="278" y="325" text-anchor="middle" font-size="11" font-weight="700" fill="#CBD5E1">EN</text>

    <rect x="464" y="280" width="36" height="28" rx="4" fill="#CBD5E1" stroke="#475569" stroke-width="1.5" />
    <circle cx="482" cy="294" r="6" fill="#2563EB" />
    <text x="482" y="325" text-anchor="middle" font-size="11" font-weight="700" fill="#CBD5E1">BOOT</text>

    <!-- Port USB -->
    <rect x="345" y="295" width="70" height="35" rx="4" fill="#CBD5E1" stroke="#475569" stroke-width="2" />
    <rect x="355" y="310" width="50" height="15" rx="2" fill="#475569" />

    <!-- Label Petunjuk Kiri -->
    <text x="90" y="52" text-anchor="middle" font-size="12" font-weight="700" fill="#2979FF">Antena PCB Wi-Fi</text>
    <line x1="160" y1="48" x2="280" y2="48" stroke="#2979FF" stroke-width="1.5" marker-end="url(#arrowM02)" />

    <text x="90" y="145" text-anchor="middle" font-size="12" font-weight="700" fill="#2979FF">Chip Utama Logam</text>
    <line x1="160" y1="142" x2="270" y2="142" stroke="#2979FF" stroke-width="1.5" marker-end="url(#arrowM02)" />

    <text x="90" y="295" text-anchor="middle" font-size="12" font-weight="700" fill="#2979FF">Tombol Reset (EN)</text>
    <line x1="160" y1="292" x2="250" y2="292" stroke="#2979FF" stroke-width="1.5" marker-end="url(#arrowM02)" />

    <!-- Label Petunjuk Kanan -->
    <text x="670" y="90" text-anchor="middle" font-size="12" font-weight="700" fill="#2979FF">Pin Header GPIO</text>
    <line x1="600" y1="88" x2="555" y2="88" stroke="#2979FF" stroke-width="1.5" marker-end="url(#arrowM02)" />

    <text x="670" y="245" text-anchor="middle" font-size="12" font-weight="700" fill="#2979FF">Konverter UART</text>
    <line x1="600" y1="242" x2="420" y2="248" stroke="#2979FF" stroke-width="1.5" marker-end="url(#arrowM02)" />

    <text x="670" y="315" text-anchor="middle" font-size="12" font-weight="700" fill="#2979FF">Port Daya &amp; Data USB</text>
    <line x1="585" y1="312" x2="425" y2="312" stroke="#2979FF" stroke-width="1.5" marker-end="url(#arrowM02)" />
  </svg>
  <figcaption style="font-size:13px;color:#616161;margin-top:12px;font-style:italic;">Gambar 2.1: Anatomi fisik papan pengembang ESP32-DevKitC-1 dan letak komponen kuncinya. (Sumber: Desain Orisinal Tim Kurikulum Koding Indonesia, merujuk spesifikasi resmi Espressif Systems)</figcaption>
</figure>

<h2>Tur visual komponen starter kit — mengenali benda di mejamu</h2>
<p>Selain board ESP32, di dalam kotak starter kit terdapat 8 komponen pendukung yang akan menemani petualangan kita sepanjang Babak 1. Mari kenali satu per satu:</p>

<div class="fsiot-card">
  <h3 style="margin-top:0;color:#1a1a1a;">1. Breadboard 830 Titik (Papan Rancang Sirkuit)</h3>
  <p style="margin-bottom:0;line-height:1.7;">Papan plastik putih berlubang-lubang ini adalah penyelamat nomor satu pemula. Mengapa? Karena di dalamnya terdapat klip logam pegas tersembunyi. Kamu bisa menancapkan kaki komponen dan kabel jumper dengan mudah tanpa perlu menyolder sama sekali! Jika salah tancap, tinggal cabut dan pasang ulang tanpa risiko merusak alat.</p>
</div>

<div class="fsiot-card">
  <h3 style="margin-top:0;color:#1a1a1a;">2. Kabel Jumper Dupont (Jembatan Listrik)</h3>
  <p style="margin-bottom:0;line-height:1.7;">Kabel warna-warni yang fleksibel ini bertugas mengalirkan sinyal listrik antar lubang di breadboard. Terdapat tiga jenis ujung:
  <br>• <strong>Male-to-Male (M-to-M):</strong> Kedua ujung memiliki jarum logam runcing (paling sering dipakai menancap di breadboard).
  <br>• <strong>Male-to-Female (M-to-F):</strong> Satu ujung jarum, satu ujung lubang soket (biasanya untuk menghubungkan pin sensor ke breadboard).
  <br>• <strong>Female-to-Female (F-to-F):</strong> Kedua ujung memiliki lubang soket.</p>
</div>

<div class="fsiot-card">
  <h3 style="margin-top:0;color:#1a1a1a;">3. Lampu LED 5mm (Indikator Cahaya)</h3>
  <p style="margin-bottom:0;line-height:1.7;">Lampu kecil bening atau berwarna (merah, hijau, kuning, biru). LED memiliki kutub positif dan negatif yang tidak boleh tertukar:
  <br>• <strong>Kaki Lebih Panjang = Anoda (+):</strong> Dihubungkan ke sumber tegangan atau pin sinyal mikrokontroler.
  <br>• <strong>Kaki Lebih Pendek = Katoda (-):</strong> Dihubungkan ke kutub Ground (GND).</p>
</div>

<div class="fsiot-card">
  <h3 style="margin-top:0;color:#1a1a1a;">4. Resistor Pembatas Arus (Pelindung Komponen)</h3>
  <p style="margin-bottom:0;line-height:1.7;">Komponen kecil silinder berwarna krem atau biru muda dengan gelang garis warna-warni di badannya. Resistor bertindak sebagai "polisi tidur" penghambat aliran arus listrik. Tanpa resistor, lampu LED yang terhubung langsung ke pin 3,3V akan kemasukan arus berlebih dan seketika terbakar/putus! Kabar gembiranya bagi pemula: resistor tidak memiliki kutub positif atau negatif, jadi kamu bebas menancapkannya bolak-balik tanpa khawatir salah arah.</p>
</div>

<div class="fsiot-card">
  <h3 style="margin-top:0;color:#1a1a1a;">5. Sensor Suhu &amp; Kelembapan Udara (DHT22 atau BME280)</h3>
  <p style="margin-bottom:0;line-height:1.7;">Sensor kotak putih berpori (tipe DHT22) atau modul kecil berwarna ungu (tipe BME280). Sensor ini bertindak sebagai indra perasa yang mengukur suhu (°C) dan persentase uap air di udara (%RH).</p>
</div>

<div class="fsiot-card">
  <h3 style="margin-top:0;color:#1a1a1a;">6. Sensor Cahaya LDR (Light Dependent Resistor)</h3>
  <p style="margin-bottom:0;line-height:1.7;">Lempengan bulat kecil seukuran ujung jari kelingking dengan dua kaki kawat dan garis jalur zig-zag merah/cokelat di permukaannya. Nilai hambatannya berubah otomatis tergantung seberapa terang cahaya lampu di ruangan belajarmu.</p>
</div>

<div class="fsiot-card">
  <h3 style="margin-top:0;color:#1a1a1a;">7. Modul Sakelar Relay 1-Channel 5V</h3>
  <p style="margin-bottom:0;line-height:1.7;">Kotak biru bersegel di atas papan sirkuit kecil dengan terminal sekrup hijau di ujungnya. <em>(Catatan unik untuk awam: jika kamu melihat komponen fisiknya di meja, di atas bodi plastik kubus biru ini biasanya tercetak tulisan merek pabrikan legendaris bernama <strong>SONGLE</strong> dengan kode tipe SRD-05VDC-SL-C)</em>. Relay adalah sakelar mekanik dengan pengaman khusus bernama isolasi optocoupler: perintah dari ESP32 diubah menjadi kilatan sinar inframerah kecil di dalam chip untuk menyalakan sakelar mekanik, sehingga arus listrik besar 220V pada lampu rumah sama sekali tidak bisa melompat menyengat laptopmu.</p>
</div>

<div class="fsiot-card">
  <h3 style="margin-top:0;color:#1a1a1a;">8. Layar OLED 0.96 Inch I2C (SSD1306)</h3>
  <p style="margin-bottom:0;line-height:1.7;">Layar kaca mini hitam beresolusi 128x64 piksel yang sangat tajam dan hanya membutuhkan 4 kabel penghubung: kabel daya (VCC dan GND) serta jalur data cerdas I2C (kabel SCL sebagai pengetuk tempo data dan kabel SDA sebagai pembawa teks angka suhunya). Layar ini akan menampilkan data langsung di sudut mejamu.</p>
</div>

<figure style="margin:28px 0;background:#F5F5F0;border:2px solid #1a1a1a;border-radius:8px;padding:20px;text-align:center;">
  <svg viewBox="0 0 760 360" width="100%" height="100%" style="max-width:760px;font-family:'Space Grotesk',system-ui,sans-serif;" aria-label="Peta 8 Komponen Starter Kit Full Stack IoT">
    <!-- Baris 1: 4 Komponen -->
    <!-- Breadboard -->
    <rect x="20" y="20" width="165" height="150" rx="8" fill="#FFFFFF" stroke="#1a1a1a" stroke-width="2" />
    <rect x="35" y="35" width="135" height="50" rx="4" fill="#F8FAFC" stroke="#94A3B8" stroke-dasharray="3,3" />
    <text x="102" y="112" text-anchor="middle" font-size="13" font-weight="700" fill="#0F172A">Breadboard 830</text>
    <text x="102" y="132" text-anchor="middle" font-size="11" fill="#64748B">Papan Sirkuit Bebas Solder</text>
    <text x="102" y="152" text-anchor="middle" font-size="10" font-weight="600" fill="#2563EB">Lego Elektronika</text>

    <!-- Jumper Wires -->
    <rect x="205" y="20" width="165" height="150" rx="8" fill="#FFFFFF" stroke="#1a1a1a" stroke-width="2" />
    <path d="M 235 45 Q 285 15 340 55" fill="none" stroke="#DC2626" stroke-width="3" />
    <path d="M 235 60 Q 285 30 340 70" fill="none" stroke="#2563EB" stroke-width="3" />
    <path d="M 235 75 Q 285 45 340 85" fill="none" stroke="#10B981" stroke-width="3" />
    <text x="287" y="112" text-anchor="middle" font-size="13" font-weight="700" fill="#0F172A">Kabel Jumper</text>
    <text x="287" y="132" text-anchor="middle" font-size="11" fill="#64748B">Male / Female Dupont</text>
    <text x="287" y="152" text-anchor="middle" font-size="10" font-weight="600" fill="#2563EB">Jembatan Sinyal</text>

    <!-- LED -->
    <rect x="390" y="20" width="165" height="150" rx="8" fill="#FFFFFF" stroke="#1a1a1a" stroke-width="2" />
    <circle cx="472" cy="50" r="14" fill="#EF4444" stroke="#B91C1C" stroke-width="2" />
    <line x1="467" y1="64" x2="467" y2="90" stroke="#475569" stroke-width="2" />
    <line x1="477" y1="64" x2="477" y2="82" stroke="#475569" stroke-width="2" />
    <text x="472" y="112" text-anchor="middle" font-size="13" font-weight="700" fill="#0F172A">Lampu LED 5mm</text>
    <text x="472" y="132" text-anchor="middle" font-size="11" fill="#64748B">Kaki Panjang Anoda (+)</text>
    <text x="472" y="152" text-anchor="middle" font-size="10" font-weight="600" fill="#2563EB">Indikator Visual</text>

    <!-- Resistor -->
    <rect x="575" y="20" width="165" height="150" rx="8" fill="#FFFFFF" stroke="#1a1a1a" stroke-width="2" />
    <line x1="595" y1="58" x2="720" y2="58" stroke="#94A3B8" stroke-width="2" />
    <rect x="625" y="48" width="65" height="20" rx="4" fill="#E2E8F0" stroke="#64748B" stroke-width="1.5" />
    <line x1="638" y1="48" x2="638" y2="68" stroke="#DC2626" stroke-width="3" />
    <line x1="650" y1="48" x2="650" y2="68" stroke="#DC2626" stroke-width="3" />
    <line x1="662" y1="48" x2="662" y2="68" stroke="#9A3412" stroke-width="3" />
    <text x="657" y="112" text-anchor="middle" font-size="13" font-weight="700" fill="#0F172A">Resistor</text>
    <text x="657" y="132" text-anchor="middle" font-size="11" fill="#64748B">220Ω, 1kΩ, 10kΩ</text>
    <text x="657" y="152" text-anchor="middle" font-size="10" font-weight="600" fill="#2563EB">Pembatas Arus</text>

    <!-- Baris 2: 4 Komponen -->
    <!-- DHT22 / BME280 -->
    <rect x="20" y="190" width="165" height="150" rx="8" fill="#FFFFFF" stroke="#1a1a1a" stroke-width="2" />
    <rect x="60" y="208" width="85" height="42" rx="4" fill="#F8FAFC" stroke="#3B82F6" stroke-width="1.5" />
    <circle cx="85" cy="229" r="4" fill="#93C5FD" /><circle cx="102" cy="229" r="4" fill="#93C5FD" /><circle cx="120" cy="229" r="4" fill="#93C5FD" />
    <text x="102" y="282" text-anchor="middle" font-size="13" font-weight="700" fill="#0F172A">Sensor Suhu</text>
    <text x="102" y="302" text-anchor="middle" font-size="11" fill="#64748B">DHT22 / BME280</text>
    <text x="102" y="322" text-anchor="middle" font-size="10" font-weight="600" fill="#059669">Ukur Termal &amp; RH</text>

    <!-- LDR -->
    <rect x="205" y="190" width="165" height="150" rx="8" fill="#FFFFFF" stroke="#1a1a1a" stroke-width="2" />
    <circle cx="287" cy="229" r="16" fill="#FEF3C7" stroke="#D97706" stroke-width="1.5" />
    <path d="M 277 229 Q 287 220 297 229" fill="none" stroke="#B45309" stroke-width="2" />
    <text x="287" y="282" text-anchor="middle" font-size="13" font-weight="700" fill="#0F172A">Sensor LDR</text>
    <text x="287" y="302" text-anchor="middle" font-size="11" fill="#64748B">Photoresistor Zig-zag</text>
    <text x="287" y="322" text-anchor="middle" font-size="10" font-weight="600" fill="#059669">Deteksi Terang/Gelap</text>

    <!-- Relay Module -->
    <rect x="390" y="190" width="165" height="150" rx="8" fill="#FFFFFF" stroke="#1a1a1a" stroke-width="2" />
    <rect x="425" y="210" width="95" height="40" rx="4" fill="#2563EB" stroke="#1D4ED8" stroke-width="1.5" />
    <text x="472" y="234" text-anchor="middle" font-size="11" font-weight="700" fill="#FFFFFF">RELAY 5V</text>
    <text x="472" y="282" text-anchor="middle" font-size="13" font-weight="700" fill="#0F172A">Modul Relay 5V</text>
    <text x="472" y="302" text-anchor="middle" font-size="11" fill="#64748B">Isolasi Optocoupler</text>
    <text x="472" y="322" text-anchor="middle" font-size="10" font-weight="600" fill="#059669">Sakelar Beban Aman</text>

    <!-- OLED Screen -->
    <rect x="575" y="190" width="165" height="150" rx="8" fill="#FFFFFF" stroke="#1a1a1a" stroke-width="2" />
    <rect x="610" y="208" width="95" height="42" rx="4" fill="#0F172A" stroke="#334155" stroke-width="1.5" />
    <text x="657" y="233" text-anchor="middle" font-size="9" font-family="'JetBrains Mono', monospace" fill="#38BDF8">27.5°C 60%</text>
    <text x="657" y="282" text-anchor="middle" font-size="13" font-weight="700" fill="#0F172A">Layar OLED I2C</text>
    <text x="657" y="302" text-anchor="middle" font-size="11" fill="#64748B">SSD1306 128x64</text>
    <text x="657" y="322" text-anchor="middle" font-size="10" font-weight="600" fill="#059669">Monitor Mini Lokal</text>
  </svg>
  <figcaption style="font-size:13px;color:#616161;margin-top:12px;font-style:italic;">Gambar 2.2: Peta visual 8 komponen pendukung Starter Kit IoT yang akan digunakan sepanjang Babak 1. (Sumber: Desain Orisinal Tim Kurikulum Koding Indonesia)</figcaption>
</figure>

<h2>Studi kasus meja belajar — mencocokkan komponen ke fungsi nyata</h2>
<p>Mari kita segarkan kembali proyek <strong>Stasiun Pintar Meja Belajar</strong> yang kita canangkan di <a href="/artikel/fullstack-iot-pintu-masuk-iot">Modul M-01 (#71)</a>. Sekarang kamu sudah tahu benda-benda aslinya! Mari kita cocokkan mana komponen yang memegang tanggung jawab setiap tugas:</p>

<ol>
  <li><strong>Memantau Suhu Meja Kerja:</strong> Tugas ini dipegang oleh <em>Sensor DHT22 / BME280</em>.</li>
  <li><strong>Mendeteksi Waktu Senja / Gelap:</strong> Tugas ini dikawal oleh <em>Sensor Cahaya LDR</em>.</li>
  <li><strong>Menyalakan Lampu Belajar Secara Otomatis:</strong> Tugas sakelar ini diserahkan kepada <em>Modul Relay 5V</em>.</li>
  <li><strong>Memberikan Tanda Status di Meja:</strong> Lampu kedip hijau atau merah menggunakan <em>Lampu LED 5mm</em> yang dilindungi oleh <em>Resistor 220Ω</em>.</li>
  <li><strong>Menampilkan Angka Suhu di Sudut Meja:</strong> Teks grafis ditampilkan jernih di <em>Layar OLED 0.96 inch</em>.</li>
  <li><strong>Menghubungkan Semuanya Tanpa Solder:</strong> Seluruh komponen ditancapkan di atas <em>Breadboard 830</em> dan disambungkan oleh <em>Kabel Jumper</em>.</li>
  <li><strong>Komandan &amp; Otak Pengendali:</strong> Papan <em>ESP32-DevKitC-1</em> yang berpikir dan mengirim data via Wi-Fi!</li>
</ol>

<h2>Latihan mandiri awam — inspeksi fisik 2 menit</h2>
<p>Sebelum kita menguji pemahaman lewat kuis, mari luangkan 2 menit untuk melakukan observasi fisik terhadap peralatanmu (atau melalui gambar di atas):</p>

<div class="fsiot-callout">
  <p><strong style="color:#1a1a1a;">1. Raba dan Perhatikan Kaki Lampu LED</strong></p>
  <p>Ambil satu buah LED 5mm. Perhatikan kedua kawat kakinya yang menjulur ke bawah. Apakah kamu melihat ada satu kaki yang sengaja dipotong sedikit lebih panjang dari kaki satunya? Kaki yang lebih panjang tersebut adalah kutub <strong>Anoda (+)</strong>. Ingat aturan emas ini selamanya: arus listrik masuk melalui kaki yang lebih panjang!</p>
</div>

<div class="fsiot-callout">
  <p><strong style="color:#1a1a1a;">2. Temukan Antena Emas di Papan ESP32</strong></p>
  <p>Pegang papan ESP32 di tanganmu dan arahkan ke bawah lampu meja. Amati ujung atas papan di sebelah chip perak. Kamu akan melihat jejak tembaga berkelok-kelok yang mengilap di balik lapisan pelindung hitam/hijau. Itulah antena pemancar Wi-Fi yang akan membawa pesan datamu ke seluruh dunia!</p>
</div>

<div class="fsiot-callout">
  <p><strong style="color:#1a1a1a;">3. Rasakan Klip Pegas di Lubang Breadboard</strong></p>
  <p>Ambil sebatang kabel jumper dan tancapkan ujung jarumnya ke salah satu lubang breadboard. Rasakan ada gigitan pegas lembut yang menjepit jarum tersebut. Klip logam di dalam lubang itulah yang membuat komponenmu terhubung kuat tanpa butuh setetes pun timah solder.</p>
</div>

<h2>Kuis pemahaman modul M-02 (Micro-Quiz)</h2>
<p>Yuk uji analisismu setelah berkenalan langsung dengan wujud fisik komponen kit! Klik salah satu pilihan jawaban di bawah ini untuk melihat hasilnya secara langsung:</p>

<div class="fsiot-quiz" id="quiz-72-1">
  <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
    <span style="background:#2979FF;color:#FFFFFF;padding:3px 10px;border-radius:4px;font-size:11px;font-weight:700;">Soal 1 dari 3</span>
    <strong style="font-size:14px;">Kuis Polaritas Komponen</strong>
  </div>
  <p class="fsiot-quiz-question">Bagaimanakah cara termudah mengenali kutub positif (Anoda) pada sebuah lampu LED fisik baru?</p>

  <div class="fsiot-quiz-options">
    <div class="fsiot-quiz-opt" data-option="A" data-correct="false">
      <strong>A.</strong> Kutub positif adalah kaki kawat yang dipotong lebih pendek.
    </div>
    <div class="fsiot-quiz-opt" data-option="B" data-correct="true">
      <strong>B.</strong> Kutub positif adalah kaki kawat yang sengaja dibuat lebih panjang.
    </div>
    <div class="fsiot-quiz-opt" data-option="C" data-correct="false">
      <strong>C.</strong> Warna kacanya selalu lebih gelap dibanding kutub negatif.
    </div>
    <div class="fsiot-quiz-opt" data-option="D" data-correct="false">
      <strong>D.</strong> LED tidak memiliki kutub, bebas dipasang bolak-balik.
    </div>
  </div>

  <div class="fsiot-quiz-feedback"></div>

  <div class="fsiot-quiz-explanation">
    <p><strong style="color:#2E7D32;">Kunci Jawaban: B</strong></p>
    <p><strong>Pembahasan:</strong> Komponen dioda pemancar cahaya (LED) bersifat terpolarisasi. Pabrik pembuat selalu mencetak kaki Anoda (kutub positif) lebih panjang daripada kaki Katoda (kutub negatif). Arus listrik harus mengalir dari anoda ke katoda agar LED dapat memancarkan cahaya.</p>
  </div>

  <div style="margin-top:10px;text-align:right;">
    <span class="fsiot-quiz-toggle">Lihat Kunci &amp; Pembahasan</span>
  </div>
</div>

<div class="fsiot-quiz" id="quiz-72-2">
  <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
    <span style="background:#2979FF;color:#FFFFFF;padding:3px 10px;border-radius:4px;font-size:11px;font-weight:700;">Soal 2 dari 3</span>
    <strong style="font-size:14px;">Kuis Anatomi Board ESP32</strong>
  </div>
  <p class="fsiot-quiz-question">Apakah fungsi dari jalur tembaga berkelok-kelok yang terletak di ujung atas papan sirkuit ESP32?</p>

  <div class="fsiot-quiz-options">
    <div class="fsiot-quiz-opt" data-option="A" data-correct="false">
      <strong>A.</strong> Hiasan artistik pembuat sirkuit agar papan terlihat keren.
    </div>
    <div class="fsiot-quiz-opt" data-option="B" data-correct="false">
      <strong>B.</strong> Elemen pemanas darurat untuk mendinginkan prosesor.
    </div>
    <div class="fsiot-quiz-opt" data-option="C" data-correct="true">
      <strong>C.</strong> Antena radio bawaan untuk memancarkan dan menerima sinyal Wi-Fi 2.4 GHz serta Bluetooth BLE.
    </div>
    <div class="fsiot-quiz-opt" data-option="D" data-correct="false">
      <strong>D.</strong> Pegangan jempol tangan saat memasang kabel jumper.
    </div>
  </div>

  <div class="fsiot-quiz-feedback"></div>

  <div class="fsiot-quiz-explanation">
    <p><strong style="color:#2E7D32;">Kunci Jawaban: C</strong></p>
    <p><strong>Pembahasan:</strong> Pola meander tembaga tersebut adalah antena PCB (Printed Circuit Board Antenna). Jalur ini dirancang khusus oleh insinyur RF untuk beresonansi pada frekuensi radio 2.4 GHz, memungkinkan ESP32 berkomunikasi via Wi-Fi dan Bluetooth tanpa membutuhkan komponen antena eksternal tambahan.</p>
  </div>

  <div style="margin-top:10px;text-align:right;">
    <span class="fsiot-quiz-toggle">Lihat Kunci &amp; Pembahasan</span>
  </div>
</div>

<div class="fsiot-quiz" id="quiz-72-3">
  <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
    <span style="background:#2979FF;color:#FFFFFF;padding:3px 10px;border-radius:4px;font-size:11px;font-weight:700;">Soal 3 dari 3</span>
    <strong style="font-size:14px;">Kuis Keamanan &amp; Peralatan</strong>
  </div>
  <p class="fsiot-quiz-question">Mengapa papan breadboard sangat disukai dan menjadi standar belajar elektronika bagi pemula?</p>

  <div class="fsiot-quiz-options">
    <div class="fsiot-quiz-opt" data-option="A" data-correct="false">
      <strong>A.</strong> Karena breadboard bisa menghasilkan daya listrik sendiri tanpa perlu dicolok sumber tenaga.
    </div>
    <div class="fsiot-quiz-opt" data-option="B" data-correct="false">
      <strong>B.</strong> Karena breadboard hanya bisa dipakai untuk rangkaian permanen di pabrik.
    </div>
    <div class="fsiot-quiz-opt" data-option="C" data-correct="true">
      <strong>C.</strong> Karena memungkinkan merangkai dan membongkar sirkuit secara bebas tanpa perlu menyolder timah panas.
    </div>
    <div class="fsiot-quiz-opt" data-option="D" data-correct="false">
      <strong>D.</strong> Karena breadboard terbuat dari kayu anti air.
    </div>
  </div>

  <div class="fsiot-quiz-feedback"></div>

  <div class="fsiot-quiz-explanation">
    <p><strong style="color:#2E7D32;">Kunci Jawaban: C</strong></p>
    <p><strong>Pembahasan:</strong> Breadboard (sering disebut *solderless breadboard*) menggunakan deretan klip pegas logam di balik lubang plastiknya. Hal ini memungkinkan pemula menancap dan mencabut kabel berkali-kali secara aman, cepat, dan higienis tanpa risiko luka bakar solder atau kerusakan komponen.</p>
  </div>

  <div style="margin-top:10px;text-align:right;">
    <span class="fsiot-quiz-toggle">Lihat Kunci &amp; Pembahasan</span>
  </div>
</div>

<h2>Rangkuman intisari &amp; langkah selanjutnya</h2>
<p>Luar biasa! Kini kamu tidak lagi memandang komponen elektronika sebagai benda misterius yang menakutkan. Tiga intisari utama dari modul ini adalah:</p>
<ol>
  <li><strong>ESP32 Board Lengkap &amp; Mandiri:</strong> Papan ESP32-DevKitC-1 sudah memiliki otak prosesor dual-core, antena Wi-Fi bawaan, tombol reset, dan port USB yang siap dicolok langsung ke komputermu.</li>
  <li><strong>Starter Kit Siap Pakai:</strong> Mulai dari breadboard bebas solder, kabel jumper, lampu LED berpolaritas, resistor pembatas arus, sensor suhu/cahaya, hingga relay dan OLED mini siap dirangkai menjadi Stasiun Meja Belajar Pintar.</li>
  <li><strong>Aman dan Nyaman untuk Awam:</strong> Tegangan kerja DC 3,3V dan 5V sepenuhnya aman bagi pemula. Tidak ada sengatan listrik berbahaya saat kamu bereksperimen di Babak 1.</li>
</ol>

<p>Di <strong>Modul M-03 (#73)</strong> berikutnya, kita akan membahas pilar keselamatan paling krusial sebelum mulai merakit kabel: <em>Keselamatan Listrik: DC 3.3V/5V Aman vs Bahaya AC 220V</em>. Kita akan mempelajari mengapa arus DC dari USB laptop tidak menyetrum, mengapa kita tidak boleh menyentuh colokan listrik rumah sembarangan, dan aturan emas mencabut kabel sebelum merakit. Sampai jumpa di modul berikutnya!</p>
HTML;
    }

    private function bodyEn(): string
    {
        return <<<'HTML'
<h2>Introduction — unboxing our first hardware toolkit</h2>
<p>Welcome to <strong>Module M-02 (#72 (this article))</strong> in the Koding Indonesia <strong>Full Stack IoT Developer</strong> curriculum! Following our conceptual journey in <a href="/artikel/fullstack-iot-pintu-masuk-iot">Module M-01 (#71)</a> covering core definitions and the human body 4-pillar analogy, today we step closer into tangible reality by unboxing our starter kit and exploring our primary hardware hero: the <strong>ESP32-DevKitC-1</strong> development board.</p>

<p>For beginners encountering a dark circuit board populated with metallic pin headers, microchips, and discrete components for the very first time, feeling slightly hesitant is completely natural: <em>"Could I break this if I touch the wrong trace? Could I get an electric shock?"</em></p>

<p>Take a relaxing breath. All electronic components explored throughout Chapter 1 operate strictly on ultra-low Direct Current (DC) voltages: <strong>3.3 Volts and 5 Volts</strong>. Sourced safely through your laptop's standard USB port, these voltages pose zero shock hazards to human skin and are built resiliently for beginner experiments. In this module, we will explore every corner of the ESP32 board and 8 essential kit components with visual ease and confidence!</p>

<blockquote>
  <p><strong>Track Status:</strong> This module is an <em>Official Draft</em>. If you haven't bought any physical hardware kit yet, don't worry! Every section below includes high-resolution annotated diagrams so you can absorb 100% of the concepts right from your screen.</p>
</blockquote>

<h2>Tools used in this article (Tools-First)</h2>
<p>Before dissecting hardware elements, let us organize your workspace following our <em>tools-first</em> pedagogical approach:</p>

<div class="fsiot-card">
  <h3 style="margin-top:0;color:#1a1a1a;">Toolkit for Module M-02</h3>
  <ol style="margin-bottom:0;padding-left:20px;line-height:1.7;">
    <li><strong>ESP32 Starter Kit Box:</strong> If you already have your physical kit, place it on your workspace and unbox it neatly.</li>
    <li><strong>Web Browser (Laptop / Mobile):</strong> To view high-clarity architectural illustrations throughout this article.</li>
    <li><strong>A Simple Notepad:</strong> To jot down the visual physical markers of each component for fast memorization.</li>
    <li><strong>No USB Cables Plugged Yet:</strong> Today is purely dedicated to <em>visual inspection</em> without plugging any power. Completely calm and safe!</li>
  </ol>
</div>

<h2>Physical anatomy of the ESP32-DevKitC-1 board</h2>
<p>Pick up your <strong>ESP32-DevKitC-1</strong> board (or observe the detailed illustration below). This thumb-sized board features 6 vital sections you should understand:</p>

<div class="fsiot-card">
  <h3 style="margin-top:0;color:#2979FF;">1. Silver Metal Shield (ESP-WROOM-32)</h3>
  <p style="margin-bottom:0;line-height:1.7;">The rectangular silver can at the center is an electromagnetic shield protecting the main ESP32 SoC. What is a SoC (System on Chip)? Picture an entire computer architecture—a 240 MHz dual-core processor, 520 KB of SRAM, Wi-Fi/Bluetooth radios, and 4 MB of flash storage—condensed into a single miniature silicon chip the size of a fingernail!</p>
</div>

<div class="fsiot-card">
  <h3 style="margin-top:0;color:#2979FF;">2. Gold Meandering PCB Antenna</h3>
  <p style="margin-bottom:0;line-height:1.7;">At the top edge lies a snaking copper/gold trace resembling a maze. This is not decorative artwork; it is the <strong>integrated 2.4 GHz Wi-Fi and Bluetooth BLE antenna</strong>! Because it is etched directly onto the PCB, the ESP32 can send and receive internet packets without needing clumsy external wire antennas.</p>
</div>

<div class="fsiot-card">
  <h3 style="margin-top:0;color:#2979FF;">3. USB Interface Port (Micro-USB or Type-C)</h3>
  <p style="margin-bottom:0;line-height:1.7;">Positioned at the bottom edge. This port serves two critical duties: delivering 5V operating power from your laptop or adapter, and serving as a high-speed data conduit to flash compiled code from Arduino IDE into ESP32 flash memory.</p>
</div>

<div class="fsiot-card">
  <h3 style="margin-top:0;color:#2979FF;">4. USB-to-UART Bridge Controller</h3>
  <p style="margin-bottom:0;line-height:1.7;">A small black IC located next to the USB port (typically labeled CP2102 or CH340). It serves as a digital translator: translating complex USB packet data from your computer into serial UART signals understood natively by the ESP32 processor. Serial UART works just like two people exchanging messages letter by letter over dedicated transmit (TX) and receive (RX) wires.</p>
</div>

<div class="fsiot-card">
  <h3 style="margin-top:0;color:#2979FF;">5. Dual Micro Pushbuttons: EN (Reset) &amp; BOOT (Flash)</h3>
  <p style="margin-bottom:0;line-height:1.7;">Two tactile pushbuttons sit alongside the USB connector:
  <br>• <strong>EN Button (Enable / Reset):</strong> Operates like a computer restart switch. Tapping it reboots the microcontroller and restarts firmware execution from line one.
  <br>• <strong>BOOT Button:</strong> Forces the ESP32 into ROM download mode when flashing new firmware over serial.</p>
</div>

<div class="fsiot-card">
  <h3 style="margin-top:0;color:#2979FF;">6. Dual Pin Header Strips (30 / 38 Pins)</h3>
  <p style="margin-bottom:0;line-height:1.7;">Rows of metallic pins extending down both flanks are General-Purpose Input/Output (GPIO) pins. Through jumper wires connected to these pins, the ESP32 samples sensor readings, blinks LEDs, trips relays, or drives OLED displays.</p>
</div>

<figure style="margin:28px 0;background:#F5F5F0;border:2px solid #1a1a1a;border-radius:8px;padding:20px;text-align:center;">
  <svg viewBox="0 0 760 360" width="100%" height="100%" style="max-width:760px;font-family:'Space Grotesk',system-ui,sans-serif;" aria-label="Physical Anatomy of the ESP32-DevKitC-1 Diagram">
    <defs>
      <marker id="arrowM02En" markerWidth="10" markerHeight="10" refX="6" refY="3" orient="auto">
        <path d="M0,0 L0,6 L9,3 z" fill="#2979FF" />
      </marker>
    </defs>

    <!-- Board Body -->
    <rect x="230" y="20" width="300" height="320" rx="12" fill="#1E293B" stroke="#0F172A" stroke-width="3" />

    <!-- Pin Headers Left & Right -->
    <rect x="210" y="45" width="20" height="270" rx="4" fill="#0F172A" stroke="#475569" stroke-width="1.5" />
    <rect x="530" y="45" width="20" height="270" rx="4" fill="#0F172A" stroke="#475569" stroke-width="1.5" />
    <!-- Pin Dots -->
    <circle cx="220" cy="65" r="3.5" fill="#F59E0B" /><circle cx="220" cy="85" r="3.5" fill="#F59E0B" /><circle cx="220" cy="105" r="3.5" fill="#F59E0B" /><circle cx="220" cy="125" r="3.5" fill="#F59E0B" /><circle cx="220" cy="145" r="3.5" fill="#F59E0B" /><circle cx="220" cy="165" r="3.5" fill="#F59E0B" /><circle cx="220" cy="185" r="3.5" fill="#F59E0B" /><circle cx="220" cy="205" r="3.5" fill="#F59E0B" /><circle cx="220" cy="225" r="3.5" fill="#F59E0B" /><circle cx="220" cy="245" r="3.5" fill="#F59E0B" /><circle cx="220" cy="265" r="3.5" fill="#F59E0B" /><circle cx="220" cy="285" r="3.5" fill="#F59E0B" />
    <circle cx="540" cy="65" r="3.5" fill="#F59E0B" /><circle cx="540" cy="85" r="3.5" fill="#F59E0B" /><circle cx="540" cy="105" r="3.5" fill="#F59E0B" /><circle cx="540" cy="125" r="3.5" fill="#F59E0B" /><circle cx="540" cy="145" r="3.5" fill="#F59E0B" /><circle cx="540" cy="165" r="3.5" fill="#F59E0B" /><circle cx="540" cy="185" r="3.5" fill="#F59E0B" /><circle cx="540" cy="205" r="3.5" fill="#F59E0B" /><circle cx="540" cy="225" r="3.5" fill="#F59E0B" /><circle cx="540" cy="245" r="3.5" fill="#F59E0B" /><circle cx="540" cy="265" r="3.5" fill="#F59E0B" /><circle cx="540" cy="285" r="3.5" fill="#F59E0B" />

    <!-- Gold PCB Antenna -->
    <rect x="290" y="30" width="180" height="35" rx="4" fill="#0F172A" stroke="#B45309" stroke-width="1.5" />
    <path d="M 305 48 L 330 48 L 330 38 L 360 38 L 360 48 L 390 48 L 390 38 L 420 38 L 420 48 L 455 48" fill="none" stroke="#F59E0B" stroke-width="2.5" stroke-linecap="round" />

    <!-- ESP-WROOM-32 Shield -->
    <rect x="280" y="80" width="200" height="135" rx="6" fill="#94A3B8" stroke="#CBD5E1" stroke-width="2" />
    <text x="380" y="135" text-anchor="middle" font-size="16" font-weight="700" fill="#0F172A">ESP-WROOM-32</text>
    <text x="380" y="158" text-anchor="middle" font-size="11" fill="#334155">Dual-Core 240MHz · 4MB Flash</text>
    <text x="380" y="176" text-anchor="middle" font-size="11" fill="#334155">Wi-Fi &amp; Bluetooth BLE</text>

    <!-- USB-UART Bridge -->
    <rect x="350" y="235" width="60" height="40" rx="4" fill="#0F172A" stroke="#64748B" stroke-width="1.5" />
    <text x="380" y="259" text-anchor="middle" font-size="10" font-weight="600" fill="#94A3B8">CP2102</text>

    <!-- Pushbuttons EN & BOOT -->
    <rect x="260" y="280" width="36" height="28" rx="4" fill="#CBD5E1" stroke="#475569" stroke-width="1.5" />
    <circle cx="278" cy="294" r="6" fill="#DC2626" />
    <text x="278" y="325" text-anchor="middle" font-size="11" font-weight="700" fill="#CBD5E1">EN</text>

    <rect x="464" y="280" width="36" height="28" rx="4" fill="#CBD5E1" stroke="#475569" stroke-width="1.5" />
    <circle cx="482" cy="294" r="6" fill="#2563EB" />
    <text x="482" y="325" text-anchor="middle" font-size="11" font-weight="700" fill="#CBD5E1">BOOT</text>

    <!-- USB Port -->
    <rect x="345" y="295" width="70" height="35" rx="4" fill="#CBD5E1" stroke="#475569" stroke-width="2" />
    <rect x="355" y="310" width="50" height="15" rx="2" fill="#475569" />

    <!-- Left Annotations -->
    <text x="90" y="52" text-anchor="middle" font-size="12" font-weight="700" fill="#2979FF">PCB Wi-Fi Antenna</text>
    <line x1="160" y1="48" x2="280" y2="48" stroke="#2979FF" stroke-width="1.5" marker-end="url(#arrowM02En)" />

    <text x="90" y="145" text-anchor="middle" font-size="12" font-weight="700" fill="#2979FF">Main SoC Shield</text>
    <line x1="160" y1="142" x2="270" y2="142" stroke="#2979FF" stroke-width="1.5" marker-end="url(#arrowM02En)" />

    <text x="90" y="295" text-anchor="middle" font-size="12" font-weight="700" fill="#2979FF">Reset Button (EN)</text>
    <line x1="160" y1="292" x2="250" y2="292" stroke="#2979FF" stroke-width="1.5" marker-end="url(#arrowM02En)" />

    <!-- Right Annotations -->
    <text x="670" y="90" text-anchor="middle" font-size="12" font-weight="700" fill="#2979FF">GPIO Pin Headers</text>
    <line x1="600" y1="88" x2="555" y2="88" stroke="#2979FF" stroke-width="1.5" marker-end="url(#arrowM02En)" />

    <text x="670" y="245" text-anchor="middle" font-size="12" font-weight="700" fill="#2979FF">UART Bridge IC</text>
    <line x1="600" y1="242" x2="420" y2="248" stroke="#2979FF" stroke-width="1.5" marker-end="url(#arrowM02En)" />

    <text x="670" y="315" text-anchor="middle" font-size="12" font-weight="700" fill="#2979FF">USB Power &amp; Data</text>
    <line x1="585" y1="312" x2="425" y2="312" stroke="#2979FF" stroke-width="1.5" marker-end="url(#arrowM02En)" />
  </svg>
  <figcaption style="font-size:13px;color:#616161;margin-top:12px;font-style:italic;">Figure 2.1: Physical anatomy of the ESP32-DevKitC-1 development board and its core features. (Source: Original Design by Koding Indonesia Curriculum Team, referencing Espressif Systems official datasheets)</figcaption>
</figure>

<h2>Visual tour of starter kit components — mapping your desk items</h2>
<p>In addition to the ESP32 board, your starter kit package includes 8 primary companion components supporting our experiments throughout Chapter 1:</p>

<div class="fsiot-card">
  <h3 style="margin-top:0;color:#1a1a1a;">1. 830-Point Solderless Breadboard</h3>
  <p style="margin-bottom:0;line-height:1.7;">This perforated plastic prototyping board is a beginner's finest asset. Beneath its surface lie rows of spring metal clips that clamp jumper wire leads securely. You can rapidly plug, reconfigure, and unplug circuits without touching soldering irons or melting solder tin.</p>
</div>

<div class="fsiot-card">
  <h3 style="margin-top:0;color:#1a1a1a;">2. Dupont Jumper Wires (Flexible Interconnects)</h3>
  <p style="margin-bottom:0;line-height:1.7;">Multi-colored insulated wires carrying electrical signals across your circuit. Three termination styles exist:
  <br>• <strong>Male-to-Male (M-to-M):</strong> Rigid pin probes on both ends (standard breadboard jumper).
  <br>• <strong>Male-to-Female (M-to-F):</strong> Pin probe on one end, socket receptacle on the other.
  <br>• <strong>Female-to-Female (F-to-F):</strong> Socket receptacles on both terminations.</p>
</div>

<div class="fsiot-card">
  <h3 style="margin-top:0;color:#1a1a1a;">3. 5mm Light Emitting Diodes (LED Indicators)</h3>
  <p style="margin-bottom:0;line-height:1.7;">Compact semiconductor lamps emitting visible light. LEDs are strictly polarized:
  <br>• <strong>Longer Lead = Anode (+):</strong> Connects to positive voltage or microcontroller digital output pin.
  <br>• <strong>Shorter Lead = Cathode (-):</strong> Connects to Ground (GND).</p>
</div>

<div class="fsiot-card">
  <h3 style="margin-top:0;color:#1a1a1a;">4. Current-Limiting Resistors</h3>
  <p style="margin-bottom:0;line-height:1.7;">Small cylindrical components bearing color-coded bands. Resistors throttle electric current flow. Without an inline resistor protecting an LED connected to 3.3V, excessive current instantly burns the semiconductor junction! Great news for beginners: resistors are non-polarized, meaning you can insert them in either direction without fear of plugging them backward.</p>
</div>

<div class="fsiot-card">
  <h3 style="margin-top:0;color:#1a1a1a;">5. Environmental Temperature &amp; Humidity Sensors (DHT22 or BME280)</h3>
  <p style="margin-bottom:0;line-height:1.7;">A white vented enclosure (DHT22) or miniature purple breakout board (BME280). Acts as biological sensory organs measuring ambient temperatures (°C) and relative humidity percentages (%RH).</p>
</div>

<div class="fsiot-card">
  <h3 style="margin-top:0;color:#1a1a1a;">6. Light Dependent Resistor (LDR Photoresistor)</h3>
  <p style="margin-bottom:0;line-height:1.7;">A small coin-shaped disc with a snaking brown/red pattern across its top face. Its internal resistance drops when illuminated and surges when shadowed, ideal for ambient light sensing.</p>
</div>

<div class="fsiot-card">
  <h3 style="margin-top:0;color:#1a1a1a;">7. 5V 1-Channel Electromechanical Relay Module</h3>
  <p style="margin-bottom:0;line-height:1.7;">A blue rectangular box mounted on a breakout PCB with green terminal screw blocks. <em>(Beginner hardware note: on the physical component in your kit, this blue plastic cube usually carries the ubiquitous manufacturer brand name <strong>SONGLE</strong> with model code SRD-05VDC-SL-C)</em>. Relays feature optocoupler isolation: switching commands from the ESP32 are converted into internal infrared light pulses to trip the mechanical contacts, completely preventing hazardous mains voltage from leaping back to your laptop.</p>
</div>

<div class="fsiot-card">
  <h3 style="margin-top:0;color:#1a1a1a;">8. 0.96-inch I2C Monochrome OLED Display (SSD1306)</h3>
  <p style="margin-bottom:0;line-height:1.7;">A crisp 128x64 pixel graphic screen requiring only 4 wiring leads: power lines (VCC and GND) and the smart I2C communication bus (an SCL clock line pulsing data rhythm and an SDA line carrying display telemetry).</p>
</div>

<figure style="margin:28px 0;background:#F5F5F0;border:2px solid #1a1a1a;border-radius:8px;padding:20px;text-align:center;">
  <svg viewBox="0 0 760 360" width="100%" height="100%" style="max-width:760px;font-family:'Space Grotesk',system-ui,sans-serif;" aria-label="Eight Essential IoT Starter Kit Components Map">
    <!-- Row 1 -->
    <rect x="20" y="20" width="165" height="150" rx="8" fill="#FFFFFF" stroke="#1a1a1a" stroke-width="2" />
    <rect x="35" y="35" width="135" height="50" rx="4" fill="#F8FAFC" stroke="#94A3B8" stroke-dasharray="3,3" />
    <text x="102" y="112" text-anchor="middle" font-size="13" font-weight="700" fill="#0F172A">830 Breadboard</text>
    <text x="102" y="132" text-anchor="middle" font-size="11" fill="#64748B">Solderless Clip Layout</text>
    <text x="102" y="152" text-anchor="middle" font-size="10" font-weight="600" fill="#2563EB">Circuit Canvas</text>

    <rect x="205" y="20" width="165" height="150" rx="8" fill="#FFFFFF" stroke="#1a1a1a" stroke-width="2" />
    <path d="M 235 45 Q 285 15 340 55" fill="none" stroke="#DC2626" stroke-width="3" />
    <path d="M 235 60 Q 285 30 340 70" fill="none" stroke="#2563EB" stroke-width="3" />
    <path d="M 235 75 Q 285 45 340 85" fill="none" stroke="#10B981" stroke-width="3" />
    <text x="287" y="112" text-anchor="middle" font-size="13" font-weight="700" fill="#0F172A">Jumper Wires</text>
    <text x="287" y="132" text-anchor="middle" font-size="11" fill="#64748B">Male / Female Dupont</text>
    <text x="287" y="152" text-anchor="middle" font-size="10" font-weight="600" fill="#2563EB">Signal Highways</text>

    <rect x="390" y="20" width="165" height="150" rx="8" fill="#FFFFFF" stroke="#1a1a1a" stroke-width="2" />
    <circle cx="472" cy="50" r="14" fill="#EF4444" stroke="#B91C1C" stroke-width="2" />
    <line x1="467" y1="64" x2="467" y2="90" stroke="#475569" stroke-width="2" />
    <line x1="477" y1="64" x2="477" y2="82" stroke="#475569" stroke-width="2" />
    <text x="472" y="112" text-anchor="middle" font-size="13" font-weight="700" fill="#0F172A">5mm LED</text>
    <text x="472" y="132" text-anchor="middle" font-size="11" fill="#64748B">Longer Lead Anode (+)</text>
    <text x="472" y="152" text-anchor="middle" font-size="10" font-weight="600" fill="#2563EB">Light Indicator</text>

    <rect x="575" y="20" width="165" height="150" rx="8" fill="#FFFFFF" stroke="#1a1a1a" stroke-width="2" />
    <line x1="595" y1="58" x2="720" y2="58" stroke="#94A3B8" stroke-width="2" />
    <rect x="625" y="48" width="65" height="20" rx="4" fill="#E2E8F0" stroke="#64748B" stroke-width="1.5" />
    <line x1="638" y1="48" x2="638" y2="68" stroke="#DC2626" stroke-width="3" />
    <line x1="650" y1="48" x2="650" y2="68" stroke="#DC2626" stroke-width="3" />
    <line x1="662" y1="48" x2="662" y2="68" stroke="#9A3412" stroke-width="3" />
    <text x="657" y="112" text-anchor="middle" font-size="13" font-weight="700" fill="#0F172A">Resistors</text>
    <text x="657" y="132" text-anchor="middle" font-size="11" fill="#64748B">220Ω, 1kΩ, 10kΩ</text>
    <text x="657" y="152" text-anchor="middle" font-size="10" font-weight="600" fill="#2563EB">Current Limiters</text>

    <!-- Row 2 -->
    <rect x="20" y="190" width="165" height="150" rx="8" fill="#FFFFFF" stroke="#1a1a1a" stroke-width="2" />
    <rect x="60" y="208" width="85" height="42" rx="4" fill="#F8FAFC" stroke="#3B82F6" stroke-width="1.5" />
    <circle cx="85" cy="229" r="4" fill="#93C5FD" /><circle cx="102" cy="229" r="4" fill="#93C5FD" /><circle cx="120" cy="229" r="4" fill="#93C5FD" />
    <text x="102" y="282" text-anchor="middle" font-size="13" font-weight="700" fill="#0F172A">Temp Sensors</text>
    <text x="102" y="302" text-anchor="middle" font-size="11" fill="#64748B">DHT22 / BME280</text>
    <text x="102" y="322" text-anchor="middle" font-size="10" font-weight="600" fill="#059669">Climate Telemetry</text>

    <rect x="205" y="190" width="165" height="150" rx="8" fill="#FFFFFF" stroke="#1a1a1a" stroke-width="2" />
    <circle cx="287" cy="229" r="16" fill="#FEF3C7" stroke="#D97706" stroke-width="1.5" />
    <path d="M 277 229 Q 287 220 297 229" fill="none" stroke="#B45309" stroke-width="2" />
    <text x="287" y="282" text-anchor="middle" font-size="13" font-weight="700" fill="#0F172A">LDR Sensor</text>
    <text x="287" y="302" text-anchor="middle" font-size="11" fill="#64748B">Light-Dependent Resistor</text>
    <text x="287" y="322" text-anchor="middle" font-size="10" font-weight="600" fill="#059669">Ambient Brightness</text>

    <rect x="390" y="190" width="165" height="150" rx="8" fill="#FFFFFF" stroke="#1a1a1a" stroke-width="2" />
    <rect x="425" y="210" width="95" height="40" rx="4" fill="#2563EB" stroke="#1D4ED8" stroke-width="1.5" />
    <text x="472" y="234" text-anchor="middle" font-size="11" font-weight="700" fill="#FFFFFF">RELAY 5V</text>
    <text x="472" y="282" text-anchor="middle" font-size="13" font-weight="700" fill="#0F172A">5V Relay Module</text>
    <text x="472" y="302" text-anchor="middle" font-size="11" fill="#64748B">Optocoupler Isolation</text>
    <text x="472" y="322" text-anchor="middle" font-size="10" font-weight="600" fill="#059669">High Power Switching</text>

    <rect x="575" y="190" width="165" height="150" rx="8" fill="#FFFFFF" stroke="#1a1a1a" stroke-width="2" />
    <rect x="610" y="208" width="95" height="42" rx="4" fill="#0F172A" stroke="#334155" stroke-width="1.5" />
    <text x="657" y="233" text-anchor="middle" font-size="9" font-family="'JetBrains Mono', monospace" fill="#38BDF8">27.5°C 60%</text>
    <text x="657" y="282" text-anchor="middle" font-size="13" font-weight="700" fill="#0F172A">OLED Display</text>
    <text x="657" y="302" text-anchor="middle" font-size="11" fill="#64748B">SSD1306 128x64</text>
    <text x="657" y="322" text-anchor="middle" font-size="10" font-weight="600" fill="#059669">Local Dashboard</text>
  </svg>
  <figcaption style="font-size:13px;color:#616161;margin-top:12px;font-style:italic;">Figure 2.2: Visual map of 8 primary IoT Starter Kit companion components used across Chapter 1. (Source: Original Design by Koding Indonesia Curriculum Team)</figcaption>
</figure>

<h2>Case study domain — mapping components to project duties</h2>
<p>Let us reconnect with our <strong>Smart Study Desk Station</strong> project introduced in <a href="/artikel/fullstack-iot-pintu-masuk-iot">Module M-01 (#71)</a>. You now know the real hardware components! Let us assign each physical tool to its operational duty:</p>

<ol>
  <li><strong>Monitoring Ambient Study Temperature:</strong> Delegated to the <em>DHT22 or BME280 Environmental Sensor</em>.</li>
  <li><strong>Detecting Twilight &amp; Room Darkness:</strong> Assigned to the <em>LDR Photoresistor</em>.</li>
  <li><strong>Switching the Desk Lamp Automatically:</strong> Managed safely through the <em>5V Relay Module</em>.</li>
  <li><strong>Blinking Desk Status Indicators:</strong> Expressed via <em>5mm LEDs</em> protected with <em>220Ω Resistors</em>.</li>
  <li><strong>Rendering Live Metrics on the Workspace:</strong> Formatted cleanly across the <em>0.96-inch OLED Screen</em>.</li>
  <li><strong>Interconnecting Circuits Solder-Free:</strong> Fastened into the <em>830-Point Breadboard</em> via <em>Dupont Jumper Wires</em>.</li>
  <li><strong>The Chief Coordinator:</strong> The <em>ESP32-DevKitC-1</em> SoC executing firmware and dispatching Wi-Fi telemetry!</li>
</ol>

<h2>Beginner self-check — 2-minute physical inspection</h2>
<p>Before testing your knowledge through the interactive quiz, take two minutes to inspect your hardware (or review the illustrations above):</p>

<div class="fsiot-callout">
  <p><strong style="color:#1a1a1a;">1. Feel and Observe LED Leads</strong></p>
  <p>Pick up a 5mm LED. Notice the two downward wire leads. Notice how one lead is deliberately longer than the other? The longer lead is the <strong>Anode (+)</strong> terminal. Always remember this golden principle: current enters through the longer leg!</p>
</div>

<div class="fsiot-callout">
  <p><strong style="color:#1a1a1a;">2. Locate the Gold Trace on the ESP32</strong></p>
  <p>Hold the ESP32 board and tilt it under your desk lamp. Observe the snaking copper trace gleaming near the silver chip. That is the onboard radio antenna that will broadcast your data across the internet!</p>
</div>

<div class="fsiot-callout">
  <p><strong style="color:#1a1a1a;">3. Feel the Spring Clips Inside Breadboard Tie-Points</strong></p>
  <p>Take a jumper wire and press its pin lead into a breadboard hole. Feel that firm, satisfying grip? Hidden spring clips inside hold connections firmly without needing a single drop of soldering tin.</p>
</div>

<h2>Module M-02 understanding check (Micro-Quiz)</h2>
<p>Let us test your hardware comprehension after meeting your physical kit components! Click one of the multiple-choice cards below to see live feedback:</p>

<div class="fsiot-quiz" id="quiz-72-en-1">
  <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
    <span style="background:#2979FF;color:#FFFFFF;padding:3px 10px;border-radius:4px;font-size:11px;font-weight:700;">Question 1 of 3</span>
    <strong style="font-size:14px;">Component Polarity Quiz</strong>
  </div>
  <p class="fsiot-quiz-question">How can you most easily identify the positive terminal (Anode) on a brand-new 5mm LED?</p>

  <div class="fsiot-quiz-options">
    <div class="fsiot-quiz-opt" data-option="A" data-correct="false">
      <strong>A.</strong> The positive terminal is the shorter wire lead.
    </div>
    <div class="fsiot-quiz-opt" data-option="B" data-correct="true">
      <strong>B.</strong> The positive terminal is the noticeably longer wire lead.
    </div>
    <div class="fsiot-quiz-opt" data-option="C" data-correct="false">
      <strong>C.</strong> Its glass envelope is always darker than the negative terminal.
    </div>
    <div class="fsiot-quiz-opt" data-option="D" data-correct="false">
      <strong>D.</strong> LEDs are non-polarized and can be reversed freely.
    </div>
  </div>

  <div class="fsiot-quiz-feedback"></div>

  <div class="fsiot-quiz-explanation">
    <p><strong style="color:#2E7D32;">Correct Answer: B</strong></p>
    <p><strong>Explanation:</strong> Light emitting diodes (LEDs) are polarized semiconductors. Manufacturers purposely manufacture the Anode lead (positive) longer than the Cathode lead (negative). Current must flow from anode to cathode to produce light.</p>
  </div>

  <div style="margin-top:10px;text-align:right;">
    <span class="fsiot-quiz-toggle">View Answer &amp; Explanation</span>
  </div>
</div>

<div class="fsiot-quiz" id="quiz-72-en-2">
  <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
    <span style="background:#2979FF;color:#FFFFFF;padding:3px 10px;border-radius:4px;font-size:11px;font-weight:700;">Question 2 of 3</span>
    <strong style="font-size:14px;">ESP32 Board Anatomy Quiz</strong>
  </div>
  <p class="fsiot-quiz-question">What is the primary function of the gold snaking copper trace etched along the top edge of the ESP32 board?</p>

  <div class="fsiot-quiz-options">
    <div class="fsiot-quiz-opt" data-option="A" data-correct="false">
      <strong>A.</strong> Purely artistic decoration to make the circuit board look futuristic.
    </div>
    <div class="fsiot-quiz-opt" data-option="B" data-correct="false">
      <strong>B.</strong> An emergency heat sink element cooling the main processor.
    </div>
    <div class="fsiot-quiz-opt" data-option="C" data-correct="true">
      <strong>C.</strong> An integrated radio antenna for transmitting and receiving 2.4 GHz Wi-Fi and Bluetooth BLE signals.
    </div>
    <div class="fsiot-quiz-opt" data-option="D" data-correct="false">
      <strong>D.</strong> A thumb grip for pressing the board into breadboard holes.
    </div>
  </div>

  <div class="fsiot-quiz-feedback"></div>

  <div class="fsiot-quiz-explanation">
    <p><strong style="color:#2E7D32;">Correct Answer: C</strong></p>
    <p><strong>Explanation:</strong> That meandering pattern is a Printed Circuit Board (PCB) trace antenna. It is tuned specifically to resonate at 2.4 GHz, allowing the ESP32 to communicate wirelessly across Wi-Fi and Bluetooth without needing bulky external antennas.</p>
  </div>

  <div style="margin-top:10px;text-align:right;">
    <span class="fsiot-quiz-toggle">View Answer &amp; Explanation</span>
  </div>
</div>

<div class="fsiot-quiz" id="quiz-72-en-3">
  <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
    <span style="background:#2979FF;color:#FFFFFF;padding:3px 10px;border-radius:4px;font-size:11px;font-weight:700;">Question 3 of 3</span>
    <strong style="font-size:14px;">Prototyping Safety Quiz</strong>
  </div>
  <p class="fsiot-quiz-question">Why are solderless breadboards universally celebrated as the gold standard for electronics beginners?</p>

  <div class="fsiot-quiz-options">
    <div class="fsiot-quiz-opt" data-option="A" data-correct="false">
      <strong>A.</strong> Because breadboards generate their own electricity without requiring external power.
    </div>
    <div class="fsiot-quiz-opt" data-option="B" data-correct="false">
      <strong>B.</strong> Because they can only be used for permanent factory assemblies.
    </div>
    <div class="fsiot-quiz-opt" data-option="C" data-correct="true">
      <strong>C.</strong> Because they allow rapid, flexible circuit prototyping without hot soldering irons or melted tin.
    </div>
    <div class="fsiot-quiz-opt" data-option="D" data-correct="false">
      <strong>D.</strong> Because breadboards are waterproof wooden blocks.
    </div>
  </div>

  <div class="fsiot-quiz-feedback"></div>

  <div class="fsiot-quiz-explanation">
    <p><strong style="color:#2E7D32;">Correct Answer: C</strong></p>
    <p><strong>Explanation:</strong> Solderless breadboards feature internal spring clips beneath rows of tie-point holes. This lets learners insert, reconfigure, and remove components safely and quickly without burning fingers or permanently damaging parts.</p>
  </div>

  <div style="margin-top:10px;text-align:right;">
    <span class="fsiot-quiz-toggle">View Answer &amp; Explanation</span>
  </div>
</div>

<h2>Summary takeaways &amp; upcoming roadmap</h2>
<p>Terrific work! Electronic components are no longer intimidating black boxes. Three primary lessons to carry forward:</p>
<ol>
  <li><strong>All-in-One ESP32 Board:</strong> The ESP32-DevKitC-1 provides a dual-core SoC, onboard Wi-Fi antenna, reset buttons, and USB serial interface ready to plug right into your laptop.</li>
  <li><strong>Modular Starter Kit:</strong> From breadboard prototyping, jumper connections, polarized LEDs, current-limiting resistors, to relays and OLED screens—every piece fits into our Smart Study Desk architecture.</li>
  <li><strong>Safe &amp; Beginner-Friendly:</strong> 3.3V and 5V DC power is completely safe for beginners. There is zero risk of hazardous electrical shocks in Chapter 1.</li>
</ol>

<p>In upcoming <strong>Module M-03 (#73)</strong>, we will explore our most vital electrical safety fundamentals before wiring: <em>Electrical Safety: Safe 3.3V/5V DC vs Lethal 220V AC</em>. We will demystify why low DC voltage cannot shock you, why household mains AC is strictly dangerous, and establish our golden safety habit of unplugging USB before touching wires. See you in the next module!</p>
HTML;
    }
}