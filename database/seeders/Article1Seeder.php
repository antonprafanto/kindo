<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article1Seeder extends Seeder
{
    public function run(): void
    {
        $admin  = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first();

        if (! $admin || ! $iotCat) {
            $this->command->error('User atau kategori tidak ditemukan. Jalankan DatabaseSeeder dulu.');

            return;
        }

        $article = Article::updateOrCreate(
            ['slug' => 'mengenal-esp32-mikrokontroler-wifi-bluetooth-iot'],
            [
                'user_id'           => $admin->id,
                'category_id'       => $iotCat->id,
                'title'             => 'Mengenal ESP32: Mikrokontroler WiFi & Bluetooth untuk IoT',
                'body'              => $this->body(),
                'status'            => 'published',
                'is_featured'       => false,
                'seo_title'         => 'Mengenal ESP32: Panduan Lengkap Mikrokontroler WiFi Bluetooth',
                'seo_description'   => 'Pelajari ESP32: mikrokontroler WiFi & Bluetooth untuk IoT. Spesifikasi lengkap, dual-core, perbandingan ESP8266, dan langkah memulai proyek embedded.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament; hindari wipe saat re-seed

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        Tag::updateOrCreate(['slug' => 'bluetooth'], ['name' => 'bluetooth']);

        $tagSlugs = ['esp32', 'iot', 'wifi', 'bluetooth'];
        $tagIds   = Tag::whereIn('slug', $tagSlugs)->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command->info('✓ Artikel ke-1 berhasil dipublish: ' . $article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Apa itu ESP32?</h2>
<p>ESP32 adalah mikrokontroler serbaguna yang dikembangkan oleh <strong>Espressif Systems</strong> dari Tiongkok. Diluncurkan pertama kali pada tahun 2016, ESP32 langsung menjadi fenomena di dunia elektronik dan IoT karena menawarkan konektivitas WiFi dan Bluetooth dalam satu chip dengan harga yang sangat terjangkau.</p>

<p>Berbeda dengan Arduino Uno yang membutuhkan modul tambahan untuk konektivitas nirkabel, ESP32 sudah memiliki WiFi 802.11 b/g/n dan Bluetooth 4.2 (Classic + BLE) terintegrasi langsung di chipnya. Ini menjadikan ESP32 pilihan ideal untuk proyek IoT modern — dari sensor rumah hingga gateway industri ringan.</p>

<h2>Spesifikasi Teknis ESP32</h2>
<p>Berikut spesifikasi chip ESP32 yang paling umum dipakai di board DevKit:</p>
<ul>
  <li><strong>Prosesor:</strong> Dual-core Xtensa LX6, hingga 240 MHz</li>
  <li><strong>RAM:</strong> 520 KB SRAM internal</li>
  <li><strong>Flash:</strong> 4 MB (umum pada board development)</li>
  <li><strong>WiFi:</strong> 802.11 b/g/n (2.4 GHz)</li>
  <li><strong>Bluetooth:</strong> v4.2 BR/EDR dan BLE</li>
  <li><strong>GPIO:</strong> 34 pin programmable</li>
  <li><strong>ADC:</strong> 18 channel 12-bit SAR ADC</li>
  <li><strong>DAC:</strong> 2 channel 8-bit DAC</li>
  <li><strong>Tegangan operasi:</strong> 2.2V – 3.6V</li>
</ul>

<figure role="img" aria-label="Diagram ringkasan ESP32: WiFi, Bluetooth, GPIO, dan dual-core mengelilingi chip ESP32 di tengah" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 620 360" style="display:block;max-width:620px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="a1G" markerWidth="9" markerHeight="9" refX="8" refY="4.5" orient="auto" markerUnits="userSpaceOnUse"><path d="M0,0 L9,4.5 L0,9 Z" fill="#2E7D32"/></marker>
    <marker id="a1B" markerWidth="9" markerHeight="9" refX="8" refY="4.5" orient="auto" markerUnits="userSpaceOnUse"><path d="M0,0 L9,4.5 L0,9 Z" fill="#2979FF"/></marker>
    <marker id="a1O" markerWidth="9" markerHeight="9" refX="8" refY="4.5" orient="auto" markerUnits="userSpaceOnUse"><path d="M0,0 L9,4.5 L0,9 Z" fill="#FF7A2F"/></marker>
    <marker id="a1P" markerWidth="9" markerHeight="9" refX="8" refY="4.5" orient="auto" markerUnits="userSpaceOnUse"><path d="M0,0 L9,4.5 L0,9 Z" fill="#7B1FA2"/></marker>
  </defs>
  <rect x="0" y="0" width="620" height="360" fill="#F5F5F0" rx="6"/>
  <rect x="230" y="130" width="160" height="100" rx="8" fill="#E8F4FF" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="310" y="165" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">ESP32</text>
  <text x="310" y="188" text-anchor="middle" fill="#4A5568" font-size="11">SoC · 240 MHz</text>
  <text x="310" y="208" text-anchor="middle" fill="#4A5568" font-size="10">520 KB SRAM · 4 MB Flash</text>
  <rect x="250" y="50" width="120" height="52" rx="6" fill="#C8E6C9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="310" y="72" text-anchor="middle" fill="#1a1a1a" font-size="12" font-weight="700">WiFi</text>
  <text x="310" y="90" text-anchor="middle" fill="#4A5568" font-size="10">802.11 b/g/n</text>
  <line x1="310" y1="102" x2="310" y2="128" stroke="#2E7D32" stroke-width="2.5" marker-end="url(#a1G)"/>
  <text x="338" y="118" fill="#2E7D32" font-size="10" font-weight="600">2.4 GHz</text>
  <rect x="430" y="154" width="120" height="52" rx="6" fill="#E3F2FD" stroke="#2979FF" stroke-width="2.5"/>
  <text x="490" y="176" text-anchor="middle" fill="#1a1a1a" font-size="12" font-weight="700">Bluetooth</text>
  <text x="490" y="194" text-anchor="middle" fill="#4A5568" font-size="10">Classic + BLE</text>
  <line x1="430" y1="180" x2="392" y2="180" stroke="#2979FF" stroke-width="2.5" marker-end="url(#a1B)"/>
  <text x="404" y="168" fill="#2979FF" font-size="10" font-weight="600">v4.2</text>
  <rect x="250" y="258" width="120" height="52" rx="6" fill="#FFF3E0" stroke="#FF7A2F" stroke-width="2.5"/>
  <text x="310" y="280" text-anchor="middle" fill="#1a1a1a" font-size="12" font-weight="700">GPIO</text>
  <text x="310" y="298" text-anchor="middle" fill="#4A5568" font-size="10">34 pin · ADC/DAC</text>
  <line x1="310" y1="258" x2="310" y2="232" stroke="#FF7A2F" stroke-width="2.5" marker-end="url(#a1O)"/>
  <text x="338" y="248" fill="#FF7A2F" font-size="10" font-weight="600">sensor/relay</text>
  <rect x="70" y="154" width="120" height="52" rx="6" fill="#F3E5F5" stroke="#7B1FA2" stroke-width="2.5"/>
  <text x="130" y="172" text-anchor="middle" fill="#1a1a1a" font-size="12" font-weight="700">Dual-core</text>
  <text x="130" y="190" text-anchor="middle" fill="#4A5568" font-size="10">Xtensa LX6 ×2</text>
  <line x1="190" y1="180" x2="228" y2="180" stroke="#7B1FA2" stroke-width="2.5" marker-end="url(#a1P)"/>
  <text x="196" y="168" fill="#7B1FA2" font-size="10" font-weight="600">paralel</text>
  <text x="310" y="340" text-anchor="middle" fill="#4A5568" font-size="11">Satu chip: konektivitas + I/O + pemrosesan — fondasi Seri 1 IoT</text>
</svg>
<figcaption style="margin-top:.75rem;font-size:.875rem;color:#4A5568;text-align:center">Ringkasan kemampuan ESP32: WiFi, Bluetooth, GPIO, dan dual-core terintegrasi dalam satu SoC.</figcaption>
</figure>

<h2>Mengapa Memilih ESP32?</h2>
<p>Ada beberapa alasan kuat mengapa ESP32 menjadi pilihan utama para developer IoT dan hobbist embedded system:</p>

<h3>1. Harga Terjangkau</h3>
<p>Board development ESP32 seperti ESP32 DevKit V1 bisa didapat dengan harga mulai dari Rp 30.000 – Rp 80.000 di marketplace lokal. Bandingkan dengan Arduino Uno + modul WiFi yang bisa mencapai Rp 150.000+.</p>

<h3>2. Dual-Core Processing</h3>
<p>Dengan dua core CPU, ESP32 bisa menangani task secara paralel. Misalnya, satu core untuk membaca sensor, sementara core lain menangani komunikasi WiFi — pola yang akan kamu praktikkan mulai dari <a href="/artikel/blink-led-esp32-tutorial-pertama-embedded-system">Blink LED (#3)</a> hingga <a href="/artikel/menghubungkan-esp32-wifi-kirim-data-server">koneksi WiFi (#4)</a>.</p>

<h3>3. Konektivitas Lengkap</h3>
<p>WiFi dan Bluetooth terintegrasi memungkinkan ESP32 berkomunikasi dengan smartphone, cloud, dan perangkat lain tanpa hardware tambahan. Untuk sensor suhu/kelembaban, lanjut ke <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">tutorial DHT22 (#5)</a>.</p>

<h2>Perbandingan ESP32 vs ESP8266 vs Arduino</h2>
<p>Sebelum memilih mikrokontroler, penting memahami perbedaannya:</p>
<ul>
  <li><strong>Arduino Uno:</strong> Cocok untuk belajar dasar, tidak ada WiFi/Bluetooth built-in</li>
  <li><strong>ESP8266:</strong> Ada WiFi, single-core, GPIO lebih sedikit, lebih murah dari ESP32</li>
  <li><strong>ESP32:</strong> Terbaik untuk IoT lengkap, dual-core, WiFi + Bluetooth, GPIO banyak</li>
</ul>
<p>Perbandingan mendalam ESP8266 vs ESP32 — kapan upgrade — dibahas di <a href="/artikel/esp8266-nodemcu-vs-esp32-kapan-pakai-upgrade">artikel perbandingan (#36)</a>.</p>

<h2>Memulai dengan ESP32</h2>
<p>Untuk mulai belajar ESP32, yang kamu butuhkan:</p>
<ul>
  <li>Board ESP32 DevKit V1 atau sejenisnya</li>
  <li>Kabel USB micro-B atau USB-C (tergantung board)</li>
  <li>Laptop/PC dengan Arduino IDE terinstall</li>
  <li>Koneksi internet untuk download library dan board package</li>
</ul>

<p>Langkah pertama setelah punya hardware: <a href="/artikel/cara-install-arduino-ide-setup-esp32-board-manager">install Arduino IDE &amp; setup ESP32 Board Manager (#2)</a>, lalu lanjut ke <a href="/artikel/blink-led-esp32-tutorial-pertama-embedded-system">Blink LED (#3)</a>, <a href="/artikel/menghubungkan-esp32-wifi-kirim-data-server">WiFi (#4)</a>, dan <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">DHT22 (#5)</a>.</p>

<blockquote>
  <p><strong>Tips:</strong> Beli ESP32 dari toko terpercaya dan pastikan mendapatkan board yang genuine. Board palsu/kualitas rendah sering menyebabkan masalah koneksi dan tidak stabil.</p>
</blockquote>

<h2>Langkah Selanjutnya</h2>
<ul>
  <li><a href="/artikel/cara-install-arduino-ide-setup-esp32-board-manager">Install Arduino IDE &amp; ESP32 Board Manager (#2)</a> — siapkan toolchain pemrograman</li>
  <li><a href="/artikel/blink-led-esp32-tutorial-pertama-embedded-system">Blink LED — tutorial pertama (#3)</a> — kenalan dengan GPIO</li>
  <li><a href="/artikel/menghubungkan-esp32-wifi-kirim-data-server">ESP32 ke WiFi &amp; kirim data (#4)</a> — koneksi jaringan</li>
  <li><a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">Membaca sensor DHT22 (#5)</a> — data sensor nyata</li>
  <li><strong>Seri 2:</strong> <a href="/artikel/bluetooth-esp32-ble-kirim-data-sensor-smartphone">Bluetooth BLE ESP32 (#32)</a> — kirim data sensor ke smartphone</li>
  <li><strong>Seri 2:</strong> <a href="/artikel/esp8266-nodemcu-vs-esp32-kapan-pakai-upgrade">ESP8266 vs ESP32 (#36)</a> — kapan pakai board murah dan kapan upgrade</li>
</ul>
HTML;
    }
}
