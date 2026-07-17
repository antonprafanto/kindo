<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article3Seeder extends Seeder
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
            ['slug' => 'blink-led-esp32-tutorial-pertama-embedded-system'],
            [
                'user_id'           => $admin->id,
                'category_id'       => $iotCat->id,
                'title'             => 'Blink LED dengan ESP32 — Tutorial Pertama Embedded System',
                'body'              => $this->body(),
                'status'            => 'published',
                'is_featured'       => false,
                'seo_title'         => 'Blink LED dengan ESP32 - Tutorial Pemula Embedded System',
                'seo_description'   => 'Tutorial step-by-step Blink LED pertama dengan ESP32: wiring GPIO 2, resistor 220 ohm, kode Arduino, upload, dan modifikasi kecepatan kedip.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament; hindari wipe saat re-seed

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        Tag::updateOrCreate(['slug' => 'gpio'], ['name' => 'gpio']);
        Tag::updateOrCreate(['slug' => 'arduino'], ['name' => 'arduino']);

        $tagSlugs = ['esp32', 'gpio', 'arduino'];
        $tagIds   = Tag::whereIn('slug', $tagSlugs)->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command->info('✓ Artikel ke-3 berhasil dipublish: ' . $article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Program Pertama: Blink LED</h2>
<p>Blink LED adalah "Hello World"-nya dunia embedded system. Pada tutorial ini, kita membuat LED berkedip setiap 1 detik menggunakan Arduino IDE — fondasi GPIO sebelum <a href="/artikel/menghubungkan-esp32-wifi-kirim-data-server">WiFi (#4)</a> dan <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">sensor DHT22 (#5)</a>.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> Sudah <a href="/artikel/cara-install-arduino-ide-setup-esp32-board-manager">install Arduino IDE &amp; board ESP32 (#2)</a> dan paham dasar hardware dari <a href="/artikel/mengenal-esp32-mikrokontroler-wifi-bluetooth-iot">Mengenal ESP32 (#1)</a>.</p>
</blockquote>

<h2>Apa itu GPIO?</h2>
<p><strong>GPIO</strong> (General Purpose Input/Output) adalah pin pada mikrokontroler yang bisa diprogram sebagai input atau output. ESP32 memiliki 34 pin GPIO yang masing-masing bisa digunakan untuk berbagai fungsi.</p>
<p>Built-in LED pada ESP32 DevKit biasanya terhubung ke pin <strong>GPIO 2</strong>. Namun ini bisa berbeda tergantung versi board yang kamu gunakan — cek silkscreen di PCB.</p>

<h2>Wiring LED Eksternal (Opsional)</h2>
<p>Untuk LED terpisah di breadboard, ikuti koneksi pin-ke-pin berikut. Built-in LED di GPIO 2 sering sudah ada di board — wiring ini berguna jika kamu pakai LED eksternal:</p>

<figure role="img" aria-label="Diagram wiring ESP32 GPIO 2 ke LED dengan resistor 220 ohm ke GND" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 620 300" style="display:block;max-width:620px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="w3G" markerWidth="9" markerHeight="9" refX="8" refY="4.5" orient="auto" markerUnits="userSpaceOnUse"><path d="M0,0 L9,4.5 L0,9 Z" fill="#2E7D32"/></marker>
    <marker id="w3O" markerWidth="9" markerHeight="9" refX="8" refY="4.5" orient="auto" markerUnits="userSpaceOnUse"><path d="M0,0 L9,4.5 L0,9 Z" fill="#FF7A2F"/></marker>
    <marker id="w3K" markerWidth="9" markerHeight="9" refX="8" refY="4.5" orient="auto" markerUnits="userSpaceOnUse"><path d="M0,0 L9,4.5 L0,9 Z" fill="#1a1a1a"/></marker>
  </defs>
  <rect x="0" y="0" width="620" height="300" fill="#F5F5F0" rx="6"/>
  <!-- ESP32 -->
  <rect x="30" y="50" width="160" height="190" rx="6" fill="#E8F4FF" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="110" y="78" text-anchor="middle" fill="#1a1a1a" font-size="13" font-weight="700">ESP32 DevKit</text>
  <text x="110" y="98" text-anchor="middle" fill="#4A5568" font-size="10">built-in LED sering GPIO 2</text>
  <circle cx="175" cy="140" r="5" fill="#FF7A2F"/>
  <text x="160" y="135" text-anchor="end" fill="#1a1a1a" font-size="11" font-weight="600">GPIO 2</text>
  <circle cx="175" cy="200" r="5" fill="#1a1a1a"/>
  <text x="160" y="205" text-anchor="end" fill="#1a1a1a" font-size="11" font-weight="600">GND</text>
  <!-- LED -->
  <rect x="280" y="110" width="70" height="70" rx="6" fill="#FFEB3B" stroke="#F9A825" stroke-width="2.5"/>
  <text x="315" y="140" text-anchor="middle" fill="#1a1a1a" font-size="12" font-weight="700">LED</text>
  <text x="315" y="158" text-anchor="middle" fill="#4A5568" font-size="9">+ anoda / − katoda</text>
  <!-- Resistor -->
  <rect x="430" y="125" width="90" height="40" rx="6" fill="#FFF8E7" stroke="#FF7A2F" stroke-width="2.5"/>
  <text x="475" y="150" text-anchor="middle" fill="#1a1a1a" font-size="12" font-weight="700">220 Ω</text>
  <!-- Wires: GPIO2 → LED → 220Ω → kembali ke GND ESP32 -->
  <polyline fill="none" points="180,140 250,140 250,145 275,145" stroke="#FF7A2F" stroke-width="2.5" marker-end="url(#w3O)"/>
  <text x="210" y="128" fill="#FF7A2F" font-size="10" font-weight="600">anoda (+)</text>
  <polyline fill="none" points="350,145 390,145 390,145 425,145" stroke="#2E7D32" stroke-width="2.5" marker-end="url(#w3G)"/>
  <text x="375" y="128" fill="#2E7D32" font-size="10" font-weight="600">katoda (−)</text>
  <polyline fill="none" points="520,145 550,145 550,200 180,200" stroke="#1a1a1a" stroke-width="2.5" marker-end="url(#w3K)"/>
  <text x="430" y="188" fill="#1a1a1a" font-size="10" font-weight="600">kembali ke GND</text>
  <text x="30" y="270" fill="#4A5568" font-size="10">GPIO2 → LED (+) → resistor 220Ω → GND ESP32 · Hindari GPIO strapping boot (0, 12, 15)</text>
</svg>
<figcaption style="margin-top:.75rem;font-size:.875rem;color:#4A5568;text-align:center">Wiring LED eksternal: GPIO 2 → anoda LED → resistor 220Ω → GND. Built-in LED DevKit sering sudah di GPIO 2.</figcaption>
</figure>

<h2>Kode Program Blink LED</h2>
<p>Berikut kode lengkap untuk membuat LED berkedip di <strong>GPIO 2</strong>:</p>

<pre><code class="language-arduino">// Mendefinisikan pin LED
#define LED_PIN 2

void setup() {
  // Set pin LED sebagai OUTPUT
  pinMode(LED_PIN, OUTPUT);

  // Mulai Serial Monitor untuk debugging
  Serial.begin(115200);
  Serial.println("Program Blink LED ESP32 dimulai!");
}

void loop() {
  // Nyalakan LED
  digitalWrite(LED_PIN, HIGH);
  Serial.println("LED ON");
  delay(1000);  // Tunggu 1 detik

  // Matikan LED
  digitalWrite(LED_PIN, LOW);
  Serial.println("LED OFF");
  delay(1000);  // Tunggu 1 detik
}</code></pre>

<h2>Alur Eksekusi Program</h2>
<p>Sketch Arduino ESP32 mengikuti pola <code>setup()</code> sekali, lalu <code>loop()</code> berulang:</p>

<figure role="img" aria-label="Diagram alur setup dan loop: HIGH delay LOW delay berulang" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 620 300" style="display:block;max-width:620px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="a3B" markerWidth="9" markerHeight="9" refX="8" refY="4.5" orient="auto" markerUnits="userSpaceOnUse"><path d="M0,0 L9,4.5 L0,9 Z" fill="#2979FF"/></marker>
    <marker id="a3G" markerWidth="9" markerHeight="9" refX="8" refY="4.5" orient="auto" markerUnits="userSpaceOnUse"><path d="M0,0 L9,4.5 L0,9 Z" fill="#2E7D32"/></marker>
    <marker id="a3R" markerWidth="9" markerHeight="9" refX="8" refY="4.5" orient="auto" markerUnits="userSpaceOnUse"><path d="M0,0 L9,4.5 L0,9 Z" fill="#C62828"/></marker>
  </defs>
  <rect x="0" y="0" width="620" height="300" fill="#F5F5F0" rx="6"/>
  <!-- setup row -->
  <rect x="40" y="30" width="120" height="50" rx="6" fill="#E8F4FF" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="100" y="52" text-anchor="middle" fill="#1a1a1a" font-size="12" font-weight="700">setup()</text>
  <text x="100" y="68" text-anchor="middle" fill="#4A5568" font-size="9">sekali saat boot</text>
  <line x1="160" y1="55" x2="188" y2="55" stroke="#2979FF" stroke-width="2.5" marker-end="url(#a3B)"/>
  <rect x="194" y="30" width="120" height="50" rx="6" fill="#C8E6C9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="254" y="60" text-anchor="middle" fill="#1a1a1a" font-size="11" font-weight="700">pinMode OUTPUT</text>
  <line x1="314" y1="55" x2="342" y2="55" stroke="#2979FF" stroke-width="2.5" marker-end="url(#a3B)"/>
  <rect x="348" y="30" width="110" height="50" rx="6" fill="#FFF8E7" stroke="#FF7A2F" stroke-width="2.5"/>
  <text x="403" y="60" text-anchor="middle" fill="#1a1a1a" font-size="11" font-weight="700">Serial.begin</text>
  <!-- masuk loop: turun lalu ke kiri ke HIGH -->
  <polyline fill="none" points="403,80 403,105 90,105 90,125" stroke="#2979FF" stroke-width="2.5" marker-end="url(#a3B)"/>
  <rect x="250" y="92" width="90" height="22" rx="11" fill="#E8F4FF" stroke="#2979FF" stroke-width="1.5"/>
  <text x="295" y="107" text-anchor="middle" fill="#2979FF" font-size="10" font-weight="700">masuk loop</text>
  <!-- loop row -->
  <rect x="40" y="140" width="100" height="44" rx="6" fill="#FFEB3B" stroke="#F9A825" stroke-width="2.5"/>
  <text x="90" y="167" text-anchor="middle" fill="#1a1a1a" font-size="11" font-weight="700">HIGH</text>
  <line x1="140" y1="162" x2="168" y2="162" stroke="#2E7D32" stroke-width="2.5" marker-end="url(#a3G)"/>
  <rect x="174" y="140" width="100" height="44" rx="6" fill="#ECEFF1" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="224" y="167" text-anchor="middle" fill="#1a1a1a" font-size="11" font-weight="700">delay(1000)</text>
  <line x1="274" y1="162" x2="302" y2="162" stroke="#2E7D32" stroke-width="2.5" marker-end="url(#a3G)"/>
  <rect x="308" y="140" width="100" height="44" rx="6" fill="#FFCDD2" stroke="#C62828" stroke-width="2.5"/>
  <text x="358" y="167" text-anchor="middle" fill="#1a1a1a" font-size="11" font-weight="700">LOW</text>
  <line x1="408" y1="162" x2="436" y2="162" stroke="#2E7D32" stroke-width="2.5" marker-end="url(#a3G)"/>
  <rect x="442" y="140" width="100" height="44" rx="6" fill="#ECEFF1" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="492" y="167" text-anchor="middle" fill="#1a1a1a" font-size="11" font-weight="700">delay(1000)</text>
  <!-- ulang ke HIGH -->
  <polyline fill="none" points="542,162 570,162 570,230 90,230 90,190" stroke="#C62828" stroke-width="2.5" marker-end="url(#a3R)"/>
  <text x="330" y="250" text-anchor="middle" fill="#C62828" font-size="10" font-weight="600">ulang loop() selamanya</text>
  <text x="310" y="285" text-anchor="middle" fill="#4A5568" font-size="10">setup() sekali → loop() HIGH → delay → LOW → delay → ulang</text>
</svg>
<figcaption style="margin-top:.75rem;font-size:.875rem;color:#4A5568;text-align:center">Alur: setup() sekali, lalu loop() HIGH → delay → LOW → delay berulang.</figcaption>
</figure>

<h2>Penjelasan Kode</h2>
<h3>setup() Function</h3>
<p>Fungsi <code>setup()</code> hanya dijalankan sekali saat ESP32 pertama kali dinyalakan atau direset. Di sini kita:</p>
<ul>
  <li><code>pinMode(LED_PIN, OUTPUT)</code> — mengatur pin LED sebagai output</li>
  <li><code>Serial.begin(115200)</code> — menginisialisasi komunikasi serial dengan baud rate 115200</li>
</ul>

<h3>loop() Function</h3>
<p>Fungsi <code>loop()</code> dijalankan berulang-ulang setelah <code>setup()</code> selesai. Di sini kita:</p>
<ul>
  <li><code>digitalWrite(LED_PIN, HIGH)</code> — memberikan tegangan ke pin LED (LED menyala)</li>
  <li><code>delay(1000)</code> — menunggu 1000 milidetik (1 detik)</li>
  <li><code>digitalWrite(LED_PIN, LOW)</code> — mematikan tegangan di pin LED (LED padam)</li>
</ul>

<h2>Cara Upload Program</h2>
<ol>
  <li>Salin kode di atas ke Arduino IDE</li>
  <li>Pastikan board sudah dipilih: <strong>Tools → Board → ESP32 Arduino → ESP32 Dev Module</strong></li>
  <li>Pilih port yang benar di <strong>Tools → Port</strong></li>
  <li>Klik tombol <strong>Upload</strong> (panah ke kanan)</li>
  <li>Tunggu hingga muncul "Done uploading"</li>
</ol>

<h2>Modifikasi: Blink dengan Kecepatan Berbeda</h2>
<p>Coba eksperimen dengan mengubah nilai delay untuk kecepatan kedip yang berbeda:</p>

<pre><code class="language-arduino">void loop() {
  digitalWrite(LED_PIN, HIGH);
  delay(100);   // Cepat: 100ms

  digitalWrite(LED_PIN, LOW);
  delay(100);
}</code></pre>

<p>Dengan delay 100ms, LED akan berkedip 5 kali lebih cepat dari sebelumnya!</p>

<blockquote>
  <p><strong>Tantangan:</strong> Modifikasi program agar LED berkedip dengan pola SOS morse code: 3 kedipan cepat, 3 kedipan lambat, 3 kedipan cepat.</p>
</blockquote>

<h2>Langkah Selanjutnya</h2>
<ul>
  <li><a href="/artikel/menghubungkan-esp32-wifi-kirim-data-server">Menghubungkan ESP32 ke WiFi (#4)</a> — tambahkan konektivitas jaringan</li>
  <li><a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">Membaca sensor DHT22 (#5)</a> — baca suhu &amp; kelembaban</li>
  <li><a href="/artikel/membuat-web-server-esp32-monitoring-sensor-dht22">Web Server ESP32 + DHT22 (#6)</a> — monitoring di browser</li>
</ul>
HTML;
    }
}
