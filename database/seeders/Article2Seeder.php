<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article2Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $cat   = Category::where('slug', 'iot-smart-device')->first()
            ?? Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $cat) {
            $this->command->error('User atau kategori tidak ditemukan. Jalankan DatabaseSeeder dulu.');

            return;
        }

        Tag::updateOrCreate(['slug' => 'arduino'], ['name' => 'arduino']);

        $article = Article::updateOrCreate(
            ['slug' => 'cara-install-arduino-ide-setup-esp32-board-manager'],
            [
                'user_id'           => $admin->id,
                'category_id'       => $cat->id,
                'title'             => 'Cara Install Arduino IDE dan Setup ESP32 Board Manager',
                'body'              => $this->body(),
                'status'            => 'published',
                'is_featured'       => false,
                'seo_title'         => 'Tutorial Install Arduino IDE dan Setup ESP32 Board Manager 2026',
                'seo_description'   => 'Panduan lengkap install Arduino IDE terbaru, tambah URL Board Manager ESP32, install driver USB, dan upload sketch Hello World ke ESP32 DevKit.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament; hindari wipe saat re-seed

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagSlugs = ['esp32', 'arduino'];
        $tagIds   = Tag::whereIn('slug', $tagSlugs)->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command->info('✓ Artikel ke-2 berhasil dipublish: ' . $article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan</h2>
<p>Sebelum bisa memprogram ESP32, kita perlu menginstall Arduino IDE dan menambahkan support untuk ESP32. Artikel ini memandu kamu step-by-step dari download IDE hingga ESP32 siap di-upload — fondasi untuk <a href="/artikel/blink-led-esp32-tutorial-pertama-embedded-system">Blink LED (#3)</a> dan seluruh Seri 1.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> Sudah punya board ESP32 DevKit dan kabel USB. Baca dulu <a href="/artikel/mengenal-esp32-mikrokontroler-wifi-bluetooth-iot">Mengenal ESP32 (#1)</a> untuk memahami hardware yang akan diprogram.</p>
</blockquote>

<h2>Alur Setup Singkat</h2>
<p>Urutan instalasi yang direkomendasikan:</p>

<figure role="img" aria-label="Diagram alur setup ESP32: Download IDE, tambah Board URL, install via Boards Manager, driver USB, lalu upload Blink" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 620 220" style="display:block;max-width:620px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="a2B" markerWidth="9" markerHeight="9" refX="8" refY="4.5" orient="auto" markerUnits="userSpaceOnUse"><path d="M0,0 L9,4.5 L0,9 Z" fill="#2979FF"/></marker>
  </defs>
  <rect x="0" y="0" width="620" height="220" fill="#F5F5F0" rx="6"/>
  <!-- 1 Download -->
  <rect x="12" y="50" width="100" height="72" rx="6" fill="#E8F4FF" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="62" y="74" text-anchor="middle" fill="#2979FF" font-size="12" font-weight="700">1</text>
  <text x="62" y="94" text-anchor="middle" fill="#1a1a1a" font-size="11" font-weight="700">Download</text>
  <text x="62" y="110" text-anchor="middle" fill="#4A5568" font-size="9">Arduino IDE</text>
  <line x1="112" y1="86" x2="132" y2="86" stroke="#2979FF" stroke-width="2.5" marker-end="url(#a2B)"/>
  <!-- 2 Board URL -->
  <rect x="138" y="50" width="100" height="72" rx="6" fill="#FFF8E7" stroke="#FF7A2F" stroke-width="2.5"/>
  <text x="188" y="74" text-anchor="middle" fill="#FF7A2F" font-size="12" font-weight="700">2</text>
  <text x="188" y="94" text-anchor="middle" fill="#1a1a1a" font-size="11" font-weight="700">Board URL</text>
  <text x="188" y="110" text-anchor="middle" fill="#4A5568" font-size="9">Preferences</text>
  <line x1="238" y1="86" x2="258" y2="86" stroke="#2979FF" stroke-width="2.5" marker-end="url(#a2B)"/>
  <!-- 3 Boards Manager -->
  <rect x="264" y="50" width="100" height="72" rx="6" fill="#C8E6C9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="314" y="74" text-anchor="middle" fill="#2E7D32" font-size="12" font-weight="700">3</text>
  <text x="314" y="94" text-anchor="middle" fill="#1a1a1a" font-size="11" font-weight="700">Boards Mgr</text>
  <text x="314" y="110" text-anchor="middle" fill="#4A5568" font-size="9">esp32 install</text>
  <line x1="364" y1="86" x2="384" y2="86" stroke="#2979FF" stroke-width="2.5" marker-end="url(#a2B)"/>
  <!-- 4 USB Driver -->
  <rect x="390" y="50" width="100" height="72" rx="6" fill="#F3E5F5" stroke="#7B1FA2" stroke-width="2.5"/>
  <text x="440" y="74" text-anchor="middle" fill="#7B1FA2" font-size="12" font-weight="700">4</text>
  <text x="440" y="94" text-anchor="middle" fill="#1a1a1a" font-size="11" font-weight="700">USB Driver</text>
  <text x="440" y="110" text-anchor="middle" fill="#4A5568" font-size="9">CH340/CP2102</text>
  <line x1="490" y1="86" x2="510" y2="86" stroke="#2979FF" stroke-width="2.5" marker-end="url(#a2B)"/>
  <!-- 5 Upload Blink -->
  <rect x="516" y="50" width="92" height="72" rx="6" fill="#2979FF" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="562" y="74" text-anchor="middle" fill="#BBDEFB" font-size="12" font-weight="700">5</text>
  <text x="562" y="94" text-anchor="middle" fill="#fff" font-size="11" font-weight="700">Upload</text>
  <text x="562" y="110" text-anchor="middle" fill="#e3f2fd" font-size="9">Blink / Hello</text>
  <text x="310" y="165" text-anchor="middle" fill="#4A5568" font-size="11">Ikuti lima langkah di bawah sesuai urutan panah.</text>
  <text x="310" y="190" text-anchor="middle" fill="#4A5568" font-size="10">IDE → URL board → install ESP32 → driver USB → upload pertama</text>
</svg>
<figcaption style="margin-top:.75rem;font-size:.875rem;color:#4A5568;text-align:center">Alur setup: IDE → URL board → install ESP32 → driver USB → upload pertama. Lanjut uji di <a href="/artikel/blink-led-esp32-tutorial-pertama-embedded-system">Blink LED (#3)</a>.</figcaption>
</figure>

<h2>Langkah 1: Download Arduino IDE</h2>
<p>Arduino IDE adalah software untuk menulis dan meng-upload program ke mikrokontroler. Download versi terbaru dari website resmi:</p>
<ol>
  <li>Buka browser dan pergi ke <strong>arduino.cc/en/software</strong></li>
  <li>Pilih versi sesuai OS kamu (Windows, Mac, atau Linux)</li>
  <li>Download file installer (.exe untuk Windows)</li>
  <li>Install seperti biasa, ikuti wizard instalasi</li>
</ol>

<h2>Langkah 2: Tambahkan ESP32 Board Manager URL</h2>
<p>Setelah Arduino IDE terinstall, tambahkan URL untuk Board Manager ESP32:</p>
<ol>
  <li>Buka Arduino IDE</li>
  <li>Klik menu <strong>File → Preferences</strong></li>
  <li>Di bagian <em>Additional boards manager URLs</em>, tambahkan URL berikut:</li>
</ol>

<pre><code class="language-text">https://raw.githubusercontent.com/espressif/arduino-esp32/gh-pages/package_esp32_index.json</code></pre>

<ol start="4">
  <li>Klik OK untuk menyimpan</li>
</ol>

<h2>Langkah 3: Install ESP32 via Board Manager</h2>
<p>Setelah menambahkan URL, install board ESP32:</p>
<ol>
  <li>Klik menu <strong>Tools → Board → Boards Manager</strong></li>
  <li>Di kolom pencarian, ketik <strong>esp32</strong></li>
  <li>Pilih <em>esp32 by Espressif Systems</em></li>
  <li>Klik tombol <strong>Install</strong> dan tunggu prosesnya selesai</li>
</ol>
<p>Proses instalasi membutuhkan koneksi internet dan bisa memakan waktu 5–10 menit tergantung kecepatan internet.</p>

<h2>Langkah 4: Install Driver USB</h2>
<p>ESP32 DevKit biasanya menggunakan chip CH340 atau CP2102 untuk komunikasi USB. Jika board tidak terdeteksi, install driver yang sesuai:</p>
<ul>
  <li><strong>CH340:</strong> Download dari <strong>wch.cn/downloads/CH341SER_EXE.html</strong></li>
  <li><strong>CP2102:</strong> Download dari <strong>silabs.com/developers/usb-to-uart-bridge-vcp-drivers</strong></li>
</ul>

<h2>Langkah 5: Test Koneksi ESP32</h2>
<p>Setelah semua terinstall, test koneksi ESP32:</p>
<ol>
  <li>Hubungkan ESP32 ke laptop via kabel USB</li>
  <li>Di Arduino IDE, pilih <strong>Tools → Board → ESP32 Arduino → ESP32 Dev Module</strong></li>
  <li>Pilih port yang sesuai di <strong>Tools → Port</strong> (biasanya COM3, COM4, dsb.)</li>
  <li>Upload contoh program: <strong>File → Examples → 01.Basics → Blink</strong></li>
</ol>

<h2>Contoh Program Pertama: Hello World</h2>
<p>Test dengan program sederhana yang menampilkan teks di Serial Monitor:</p>

<pre><code class="language-arduino">void setup() {
  Serial.begin(115200);
  Serial.println("Halo dari ESP32!");
}

void loop() {
  Serial.println("Koding Indonesia - ESP32 siap digunakan!");
  delay(1000);
}</code></pre>

<p>Upload program ini ke ESP32, kemudian buka <strong>Tools → Serial Monitor</strong> dengan baud rate <strong>115200</strong>. Kamu akan melihat pesan yang dicetak setiap detik.</p>

<blockquote>
  <p><strong>Masalah Umum:</strong> Jika gagal upload, tekan dan tahan tombol BOOT pada ESP32 saat proses upload dimulai, kemudian lepaskan ketika muncul teks "Connecting..." di output.</p>
</blockquote>

<h2>Langkah Selanjutnya</h2>
<ul>
  <li><a href="/artikel/blink-led-esp32-tutorial-pertama-embedded-system">Blink LED dengan ESP32 (#3)</a> — tutorial GPIO pertama</li>
  <li><a href="/artikel/menghubungkan-esp32-wifi-kirim-data-server">Menghubungkan ESP32 ke WiFi (#4)</a> — setelah nyaman dengan upload</li>
  <li><strong>Seri 2:</strong> <a href="/artikel/migrasi-platformio-esp32-vscode-project-rapi">Migrasi ke PlatformIO (#29)</a> — project ESP32 lebih rapi di VS Code</li>
</ul>
HTML;
    }
}
