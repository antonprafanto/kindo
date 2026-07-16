<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article14Seeder extends Seeder
{
    public function run(): void
    {
        $admin  = User::first();
        $espCat = Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $espCat) {
            throw new \RuntimeException('User atau kategori tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'oled-ssd1306-esp32-tampilkan-data-sensor-i2c';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $espCat->id,
                'title'           => 'Tampilkan Data Sensor di OLED SSD1306 (I2C)',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'OLED SSD1306 ESP32 — Tampilkan Sensor BME280 via I2C',
                'seo_description' => 'Tutorial OLED SSD1306 0.96 inch di ESP32: gabung BME280 di bus I2C sama, tampilkan suhu & tekanan di layar, plus publish MQTT dengan NVS.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        Tag::updateOrCreate(['slug' => 'oled'], ['name' => 'oled']);

        $tagSlugs = ['esp32', 'oled', 'i2c', 'bme280', 'sensor', 'iot', 'mqtt', 'wifi'];
        $tagIds   = Tag::whereIn('slug', $tagSlugs)->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command->info('✓ Artikel ke-14 berhasil dipublish: ' . $article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan</h2>
<p>Di <a href="/artikel/i2c-esp32-sensor-bme280-suhu-tekanan-mqtt">artikel BME280 (#13)</a> kita sudah membaca suhu, kelembaban, dan tekanan lewat <strong>I2C</strong>, lalu mengirimnya ke broker MQTT. Data itu hanya terlihat di Serial Monitor atau aplikasi subscriber — tidak ada feedback langsung di hardware.</p>

<p>Artikel ini menambahkan <strong>layar OLED SSD1306 0,96″</strong> (128×64 piksel) ke node yang sama. Pembaca sensor bisa melihat angka di lokasi tanpa membuka laptop. Karena OLED juga memakai <strong>I2C</strong>, BME280 dan OLED berbagi bus <strong>SDA/SCL</strong> — hanya alamat perangkat yang berbeda.</p>

<p>Ini lanjutan <strong>Jalur A</strong> (hardware &amp; sensor) setelah <a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">deep sleep (#11)</a>, <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">NVS (#12)</a>, dan <a href="/artikel/i2c-esp32-sensor-bme280-suhu-tekanan-mqtt">BME280 (#13)</a>.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> Sudah paham <a href="/artikel/blink-led-esp32-tutorial-pertama-embedded-system">GPIO dasar (#3)</a>, <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">DHT22 (#5)</a>, <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">MQTT (#7)</a>, dan <strong><a href="/artikel/i2c-esp32-sensor-bme280-suhu-tekanan-mqtt">I2C + BME280 (#13)</a></strong>. Untuk WiFi/NVS/broker auth, baca <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">#12</a> dan <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">#16</a>.</p>
</blockquote>

<h2>Yang Kamu Butuhkan</h2>
<ul>
  <li>ESP32 DevKit</li>
  <li>Modul sensor <strong>BME280</strong> (I2C) — dari <a href="/artikel/i2c-esp32-sensor-bme280-suhu-tekanan-mqtt">artikel #13</a></li>
  <li>Modul <strong>OLED 0,96″ SSD1306</strong> (I2C, 128×64, 3.3V) — ±Rp 20.000–35.000</li>
  <li>Breadboard + kabel jumper</li>
  <li>Broker MQTT — disarankan <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Mosquitto pribadi (#16)</a> (boleh <code>test.mosquitto.org</code> hanya untuk uji hardware/OLED)</li>
</ul>

<h2>Mengapa OLED SSD1306?</h2>
<table>
  <thead>
    <tr><th>Aspek</th><th>Serial Monitor saja</th><th>+ OLED SSD1306</th></tr>
  </thead>
  <tbody>
    <tr><td>Feedback di lapangan</td><td>Butuh laptop/USB</td><td><strong>Langsung di modul</strong></td></tr>
    <tr><td>Konsumsi daya</td><td>Minimal</td><td>±20–40 mA saat layar menyala</td></tr>
    <tr><td>Protokol</td><td>UART</td><td><strong>I2C</strong> — satu bus dengan BME280</td></tr>
    <tr><td>Use case</td><td>Debug development</td><td>Panel sensor dinding, greenhouse, gudang</td></tr>
  </tbody>
</table>

<blockquote>
  <p><strong>Catatan daya:</strong> Untuk node <a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">deep sleep baterai (#11)</a>, matikan backlight OLED saat tidur atau pakai layar hanya saat ada interaksi — OLED boros dibanding ESP32 tidur.</p>
</blockquote>

<h2>Dua Perangkat, Satu Bus I2C</h2>
<p>I2C mendukung banyak slave di kabel yang sama. Setiap modul punya <strong>alamat unik</strong>:</p>
<ul>
  <li><strong>BME280</strong> — biasanya <code>0x76</code> atau <code>0x77</code></li>
  <li><strong>SSD1306 OLED</strong> — biasanya <code>0x3C</code> (kadang <code>0x3D</code>)</li>
</ul>
<p>Karena alamat berbeda, BME280 dan OLED bisa dirakit <strong>paralel</strong> ke GPIO 21 (SDA) dan GPIO 22 (SCL) tanpa konflik.</p>

<figure role="img" aria-label="Diagram arsitektur OLED + BME280: ESP32 berbagi bus I2C dengan BME280 dan OLED SSD1306, lalu publish MQTT ke Mosquitto" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 620 480" style="display:block;max-width:620px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="oledArr" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/></marker>
    <marker id="oledArrO" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#FF7A2F"/></marker>
    <marker id="oledArrG" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#2E7D32"/></marker>
  </defs>
  <rect x="0" y="0" width="620" height="480" fill="#F5F5F0" rx="6"/>
  <!-- ESP32 -->
  <rect x="130" y="15" width="360" height="70" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="310" y="42" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">ESP32 — Wire + WiFiManager (#12)</text>
  <text x="310" y="62" text-anchor="middle" fill="#4A5568" font-size="10">Wire.begin(21, 22) · PubSubClient · Preferences NVS</text>
  <!-- I2C bus label -->
  <line x1="310" y1="85" x2="310" y2="118" stroke="#2979FF" stroke-width="2.5" marker-end="url(#oledArr)"/>
  <rect x="220" y="95" width="180" height="26" rx="13" fill="#E8F4FF" stroke="#2979FF" stroke-width="1.5"/>
  <text x="310" y="113" text-anchor="middle" fill="#2979FF" font-size="10" font-weight="700">I2C bus · SDA 21 / SCL 22</text>
  <!-- Split to two devices -->
  <line x1="310" y1="121" x2="310" y2="150" stroke="#2979FF" stroke-width="2"/>
  <line x1="160" y1="150" x2="460" y2="150" stroke="#2979FF" stroke-width="2"/>
  <line x1="160" y1="150" x2="160" y2="175" stroke="#2979FF" stroke-width="2" marker-end="url(#oledArr)"/>
  <line x1="460" y1="150" x2="460" y2="175" stroke="#2979FF" stroke-width="2" marker-end="url(#oledArr)"/>
  <!-- BME280 -->
  <rect x="55" y="180" width="210" height="70" rx="6" fill="#C8E6C9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="160" y="208" text-anchor="middle" fill="#1a1a1a" font-size="13" font-weight="700">BME280 (#13)</text>
  <text x="160" y="228" text-anchor="middle" fill="#4A5568" font-size="10">alamat 0x76 / 0x77</text>
  <text x="160" y="244" text-anchor="middle" fill="#4A5568" font-size="10">suhu · RH · tekanan</text>
  <!-- OLED -->
  <rect x="355" y="180" width="210" height="70" rx="6" fill="#FFF3E8" stroke="#FF7A2F" stroke-width="2.5"/>
  <text x="460" y="208" text-anchor="middle" fill="#1a1a1a" font-size="13" font-weight="700">OLED SSD1306</text>
  <text x="460" y="228" text-anchor="middle" fill="#4A5568" font-size="10">alamat 0x3C / 0x3D</text>
  <text x="460" y="244" text-anchor="middle" fill="#4A5568" font-size="10">128×64 · panel lokal</text>
  <!-- Data flow to MQTT -->
  <line x1="160" y1="250" x2="160" y2="278" stroke="#FF7A2F" stroke-width="2"/>
  <line x1="160" y1="278" x2="310" y2="278" stroke="#FF7A2F" stroke-width="2"/>
  <line x1="310" y1="278" x2="310" y2="318" stroke="#FF7A2F" stroke-width="2.5" marker-end="url(#oledArrO)"/>
  <rect x="330" y="288" width="150" height="24" rx="12" fill="#FFF3E8" stroke="#FF7A2F" stroke-width="1.5"/>
  <text x="405" y="305" text-anchor="middle" fill="#FF7A2F" font-size="10" font-weight="700">MQTT publish JSON</text>
  <!-- Mosquitto -->
  <rect x="130" y="325" width="360" height="55" rx="6" fill="#2979FF" stroke="#000" stroke-width="2.5"/>
  <text x="310" y="350" text-anchor="middle" fill="#fff" font-size="14" font-weight="700">Mosquitto (#16)</text>
  <text x="310" y="368" text-anchor="middle" fill="#e3f2fd" font-size="10">kodingindonesia/esp32/bme280/data</text>
  <!-- Outcomes -->
  <line x1="210" y1="380" x2="110" y2="412" stroke="#2E7D32" stroke-width="2" marker-end="url(#oledArrG)"/>
  <line x1="310" y1="380" x2="310" y2="412" stroke="#2E7D32" stroke-width="2" marker-end="url(#oledArrG)"/>
  <line x1="410" y1="380" x2="510" y2="412" stroke="#2E7D32" stroke-width="2" marker-end="url(#oledArrG)"/>
  <rect x="15" y="418" width="190" height="42" rx="6" fill="#FFF3E8" stroke="#000" stroke-width="2"/>
  <text x="110" y="436" text-anchor="middle" fill="#1a1a1a" font-size="11" font-weight="700">OLED panel</text>
  <text x="110" y="452" text-anchor="middle" fill="#4A5568" font-size="9">angka di lokasi fisik</text>
  <rect x="215" y="418" width="190" height="42" rx="6" fill="#FFF3E8" stroke="#000" stroke-width="2"/>
  <text x="310" y="436" text-anchor="middle" fill="#1a1a1a" font-size="11" font-weight="700">Serial Monitor</text>
  <text x="310" y="452" text-anchor="middle" fill="#4A5568" font-size="9">debug development</text>
  <rect x="415" y="418" width="190" height="42" rx="6" fill="#FFF3E8" stroke="#000" stroke-width="2"/>
  <text x="510" y="436" text-anchor="middle" fill="#1a1a1a" font-size="11" font-weight="700">MQTT Explorer</text>
  <text x="510" y="452" text-anchor="middle" fill="#4A5568" font-size="9">subscriber laptop</text>
  <text x="310" y="472" text-anchor="middle" fill="#4A5568" font-size="11">BME280 + OLED paralel di I2C → ESP32 tampilkan + publish MQTT</text>
</svg>
<figcaption style="margin-top:.75rem;font-size:.875rem;color:#4A5568;text-align:center">Satu bus I2C: <a href="/artikel/i2c-esp32-sensor-bme280-suhu-tekanan-mqtt">BME280 (#13)</a> + OLED SSD1306 → publish ke <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Mosquitto (#16)</a>.</figcaption>
</figure>

<h2>Komponen &amp; Wiring</h2>
<p>Hubungkan <strong>kedua modul</strong> ke ESP32 (paralel di breadboard):</p>

<figure role="img" aria-label="Diagram wiring ESP32 ke BME280 dan OLED SSD1306 paralel: 3.3V ke VCC, GND ke GND, GPIO 21 SDA, GPIO 22 SCL" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 440" style="display:block;max-width:640px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="wArrR" markerWidth="7" markerHeight="7" refX="6" refY="3.5" orient="auto"><path d="M0,0 L7,3.5 L0,7 Z" fill="#C62828"/></marker>
    <marker id="wArrK" markerWidth="7" markerHeight="7" refX="6" refY="3.5" orient="auto"><path d="M0,0 L7,3.5 L0,7 Z" fill="#1a1a1a"/></marker>
    <marker id="wArrB" markerWidth="7" markerHeight="7" refX="6" refY="3.5" orient="auto"><path d="M0,0 L7,3.5 L0,7 Z" fill="#2979FF"/></marker>
    <marker id="wArrG" markerWidth="7" markerHeight="7" refX="6" refY="3.5" orient="auto"><path d="M0,0 L7,3.5 L0,7 Z" fill="#2E7D32"/></marker>
  </defs>
  <rect x="0" y="0" width="640" height="440" fill="#F5F5F0" rx="6"/>

  <!-- ESP32 -->
  <rect x="20" y="80" width="155" height="270" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="97" y="110" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">ESP32 DevKit</text>
  <circle cx="160" cy="155" r="5" fill="#C62828"/>
  <text x="145" y="160" text-anchor="end" fill="#1a1a1a" font-size="12" font-weight="600">3.3V</text>
  <circle cx="160" cy="215" r="5" fill="#1a1a1a"/>
  <text x="145" y="220" text-anchor="end" fill="#1a1a1a" font-size="12" font-weight="600">GND</text>
  <circle cx="160" cy="275" r="5" fill="#2979FF"/>
  <text x="145" y="272" text-anchor="end" fill="#1a1a1a" font-size="12" font-weight="600">GPIO 21</text>
  <text x="145" y="286" text-anchor="end" fill="#4A5568" font-size="9">SDA</text>
  <circle cx="160" cy="335" r="5" fill="#2E7D32"/>
  <text x="145" y="332" text-anchor="end" fill="#1a1a1a" font-size="12" font-weight="600">GPIO 22</text>
  <text x="145" y="346" text-anchor="end" fill="#4A5568" font-size="9">SCL</text>

  <!-- BME280 -->
  <rect x="430" y="25" width="185" height="170" rx="6" fill="#C8E6C9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="522" y="52" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">BME280</text>
  <text x="522" y="72" text-anchor="middle" fill="#4A5568" font-size="10">I2C 0x76 / 0x77 · 3.3V</text>
  <circle cx="445" cy="100" r="5" fill="#C62828"/>
  <text x="460" y="105" fill="#1a1a1a" font-size="12" font-weight="600">VCC</text>
  <circle cx="445" cy="125" r="5" fill="#1a1a1a"/>
  <text x="460" y="130" fill="#1a1a1a" font-size="12" font-weight="600">GND</text>
  <circle cx="445" cy="150" r="5" fill="#2979FF"/>
  <text x="460" y="155" fill="#1a1a1a" font-size="12" font-weight="600">SDA</text>
  <circle cx="445" cy="175" r="5" fill="#2E7D32"/>
  <text x="460" y="180" fill="#1a1a1a" font-size="12" font-weight="600">SCL</text>

  <!-- OLED -->
  <rect x="430" y="230" width="185" height="170" rx="6" fill="#FFF3E8" stroke="#FF7A2F" stroke-width="2.5"/>
  <text x="522" y="257" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">OLED SSD1306</text>
  <text x="522" y="277" text-anchor="middle" fill="#4A5568" font-size="10">I2C 0x3C · 128×64 · 3.3V</text>
  <circle cx="445" cy="305" r="5" fill="#C62828"/>
  <text x="460" y="310" fill="#1a1a1a" font-size="12" font-weight="600">VCC</text>
  <circle cx="445" cy="330" r="5" fill="#1a1a1a"/>
  <text x="460" y="335" fill="#1a1a1a" font-size="12" font-weight="600">GND</text>
  <circle cx="445" cy="355" r="5" fill="#2979FF"/>
  <text x="460" y="360" fill="#1a1a1a" font-size="12" font-weight="600">SDA</text>
  <circle cx="445" cy="380" r="5" fill="#2E7D32"/>
  <text x="460" y="385" fill="#1a1a1a" font-size="12" font-weight="600">SCL</text>

  <!-- 3.3V → VCC (split to both) -->
  <line x1="165" y1="155" x2="250" y2="155" stroke="#C62828" stroke-width="2.5"/>
  <line x1="250" y1="100" x2="250" y2="305" stroke="#C62828" stroke-width="2.5"/>
  <line x1="250" y1="100" x2="440" y2="100" stroke="#C62828" stroke-width="2.5" marker-end="url(#wArrR)"/>
  <line x1="250" y1="305" x2="440" y2="305" stroke="#C62828" stroke-width="2.5" marker-end="url(#wArrR)"/>

  <!-- GND → GND (split) -->
  <line x1="165" y1="215" x2="290" y2="215" stroke="#1a1a1a" stroke-width="2.5"/>
  <line x1="290" y1="125" x2="290" y2="330" stroke="#1a1a1a" stroke-width="2.5"/>
  <line x1="290" y1="125" x2="440" y2="125" stroke="#1a1a1a" stroke-width="2.5" marker-end="url(#wArrK)"/>
  <line x1="290" y1="330" x2="440" y2="330" stroke="#1a1a1a" stroke-width="2.5" marker-end="url(#wArrK)"/>

  <!-- SDA GPIO21 → SDA (split) -->
  <line x1="165" y1="275" x2="330" y2="275" stroke="#2979FF" stroke-width="2.5"/>
  <line x1="330" y1="150" x2="330" y2="355" stroke="#2979FF" stroke-width="2.5"/>
  <line x1="330" y1="150" x2="440" y2="150" stroke="#2979FF" stroke-width="2.5" marker-end="url(#wArrB)"/>
  <line x1="330" y1="355" x2="440" y2="355" stroke="#2979FF" stroke-width="2.5" marker-end="url(#wArrB)"/>

  <!-- SCL GPIO22 → SCL (split) -->
  <line x1="165" y1="335" x2="370" y2="335" stroke="#2E7D32" stroke-width="2.5"/>
  <line x1="370" y1="175" x2="370" y2="380" stroke="#2E7D32" stroke-width="2.5"/>
  <line x1="370" y1="175" x2="440" y2="175" stroke="#2E7D32" stroke-width="2.5" marker-end="url(#wArrG)"/>
  <line x1="370" y1="380" x2="440" y2="380" stroke="#2E7D32" stroke-width="2.5" marker-end="url(#wArrG)"/>

  <!-- Legend -->
  <rect x="20" y="415" width="14" height="10" rx="2" fill="#C62828"/>
  <text x="40" y="424" fill="#4A5568" font-size="10">3.3V → VCC</text>
  <rect x="130" y="415" width="14" height="10" rx="2" fill="#1a1a1a"/>
  <text x="150" y="424" fill="#4A5568" font-size="10">GND → GND</text>
  <rect x="240" y="415" width="14" height="10" rx="2" fill="#2979FF"/>
  <text x="260" y="424" fill="#4A5568" font-size="10">GPIO 21 → SDA</text>
  <rect x="380" y="415" width="14" height="10" rx="2" fill="#2E7D32"/>
  <text x="400" y="424" fill="#4A5568" font-size="10">GPIO 22 → SCL (paralel kedua modul)</text>
</svg>
<figcaption style="margin-top:.75rem;font-size:.875rem;color:#4A5568;text-align:center">Wiring paralel pin-ke-pin: 3.3V→VCC, GND→GND, GPIO 21→SDA, GPIO 22→SCL ke <a href="/artikel/i2c-esp32-sensor-bme280-suhu-tekanan-mqtt">BME280 (#13)</a> dan OLED.</figcaption>
</figure>

<ul>
  <li>Pastikan modul OLED <strong>3.3V</strong> — beberapa modul punya jumper VCC/3V3</li>
  <li>Pin <strong>RST</strong> OLED boleh tidak di-wire jika library pakai <code>OLED_RESET -1</code></li>
  <li>Panjang kabel prototype &lt;30 cm biasanya aman; untuk kabel panjang pertimbangkan pull-up 4.7kΩ di SDA/SCL</li>
</ul>

<h2>Install Library</h2>
<p>Pastikan Arduino IDE dan board ESP32 sudah terpasang — ikuti <a href="/artikel/cara-install-arduino-ide-setup-esp32-board-manager">tutorial install Arduino IDE &amp; Board Manager (#2)</a> jika belum.</p>
<p>Arduino IDE → <strong>Sketch → Include Library → Manage Libraries</strong>:</p>
<ul>
  <li><strong>Adafruit SSD1306</strong></li>
  <li><strong>Adafruit GFX Library</strong> (dependency OLED)</li>
  <li><strong>Adafruit BME280 Library</strong> + <strong>Adafruit Unified Sensor</strong></li>
  <li><strong>PubSubClient</strong> (Nick O'Leary)</li>
  <li><strong>WiFiManager</strong> (tzapu)</li>
</ul>
<p>Board: <strong>esp32</strong> by Espressif (<strong>v3.x</strong>). Library <code>Wire</code>, <code>Preferences</code>, dan <code>WiFi</code> sudah built-in.</p>

<p><strong>Topic MQTT</strong> (sama <a href="/artikel/i2c-esp32-sensor-bme280-suhu-tekanan-mqtt">#13</a>): <code>kodingindonesia/esp32/bme280/data</code></p>
<p><strong>Payload JSON:</strong> <code>{"suhu":28.5,"kelembaban":65.2,"tekanan":1013.25}</code></p>

<h2>Kode Lengkap: BME280 + OLED + WiFiManager + MQTT</h2>
<p>Sketch membaca BME280, menggambar suhu/kelembaban/tekanan di OLED, lalu publish JSON ke broker — pola NVS seperti <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">#12</a> dan <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">#16</a>.</p>

<pre><code class="language-arduino">#include &lt;Wire.h&gt;
#include &lt;WiFi.h&gt;
#include &lt;WiFiManager.h&gt;
#include &lt;Preferences.h&gt;
#include &lt;PubSubClient.h&gt;
#include &lt;Adafruit_Sensor.h&gt;
#include &lt;Adafruit_BME280.h&gt;
#include &lt;Adafruit_GFX.h&gt;
#include &lt;Adafruit_SSD1306.h&gt;

#define I2C_SDA 21
#define I2C_SCL 22
#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 64
#define OLED_RESET -1
#define SCREEN_ADDRESS 0x3C

const char* NS_KINDO = "kindo";
const int MQTT_PORT = 1883;

Adafruit_BME280 bme;
Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &amp;Wire, OLED_RESET);
WiFiClient espClient;
PubSubClient mqttClient(espClient);
Preferences prefs;

String mqttHost, mqttUser, mqttPass, topicSensor;

WiFiManagerParameter pHost("mqtt_host", "MQTT broker IP", "192.168.1.50", 64);
WiFiManagerParameter pUser("mqtt_user", "MQTT username", "kindo_esp32", 32);
WiFiManagerParameter pPass("mqtt_pass", "MQTT password", "", 48);
WiFiManagerParameter pTopic("mqtt_topic", "MQTT topic", "kodingindonesia/esp32/bme280/data", 64);

bool initI2C() {
  Wire.begin(I2C_SDA, I2C_SCL);
  delay(50);
  return true;
}

bool initBME280() {
  if (bme.begin(0x76)) return true;
  if (bme.begin(0x77)) return true;
  Serial.println("BME280 tidak ditemukan");
  return false;
}

bool initOLED() {
  if (display.begin(SSD1306_SWITCHCAPVCC, SCREEN_ADDRESS)) return true;
  if (display.begin(SSD1306_SWITCHCAPVCC, 0x3D)) return true;
  Serial.println("OLED SSD1306 tidak ditemukan");
  return false;
}

void tampilkanOLED(float suhu, float kelembaban, float tekanan) {
  display.clearDisplay();
  display.setTextSize(1);
  display.setTextColor(SSD1306_WHITE);
  display.setCursor(0, 0);
  display.println(F("Koding Indonesia"));
  display.println(F("BME280 + OLED"));
  display.drawLine(0, 18, 128, 18, SSD1306_WHITE);
  display.setCursor(0, 24);
  display.printf("Suhu:    %.1f C", suhu);
  display.printf("\nRH:      %.1f %%", kelembaban);
  display.printf("\nTekanan: %.0f hPa", tekanan);
  display.display();
}

void muatMqttDariNvs() {
  prefs.begin(NS_KINDO, true);
  mqttHost    = prefs.getString("mqtt_host", "192.168.1.50");
  mqttUser    = prefs.getString("mqtt_user", "kindo_esp32");
  mqttPass    = prefs.getString("mqtt_pass", "");
  topicSensor = prefs.getString("mqtt_topic", "kodingindonesia/esp32/bme280/data");
  prefs.end();
}

void simpanMqttKeNvs() {
  prefs.begin(NS_KINDO, false);
  prefs.putString("mqtt_host", pHost.getValue());
  prefs.putString("mqtt_user", pUser.getValue());
  prefs.putString("mqtt_pass", pPass.getValue());
  prefs.putString("mqtt_topic", pTopic.getValue());
  prefs.end();
}

bool setupWiFiManager() {
  WiFiManager wm;
  wm.setConfigPortalTimeout(180);
  wm.addParameter(&amp;pHost);
  wm.addParameter(&amp;pUser);
  wm.addParameter(&amp;pPass);
  wm.addParameter(&amp;pTopic);

  muatMqttDariNvs();
  pHost.setValue(mqttHost.c_str(), 64);
  pUser.setValue(mqttUser.c_str(), 32);
  pPass.setValue(mqttPass.c_str(), 48);
  pTopic.setValue(topicSensor.c_str(), 64);

  if (!wm.autoConnect("KindoESP32-Setup")) return false;
  simpanMqttKeNvs();
  return true;
}

bool koneksiMQTT() {
  mqttClient.setServer(mqttHost.c_str(), MQTT_PORT);
  mqttClient.setBufferSize(512);
  String clientId = "ESP32-OLED-" + String(random(0xffff), HEX);
  if (!mqttClient.connect(clientId.c_str(), mqttUser.c_str(), mqttPass.c_str())) {
    Serial.print("MQTT gagal, rc=");
    Serial.println(mqttClient.state());
    return false;
  }
  return true;
}

void bacaDanTampilkan() {
  float suhu = bme.readTemperature();
  float kelembaban = bme.readHumidity();
  float tekanan = bme.readPressure() / 100.0F;

  if (isnan(suhu) || isnan(kelembaban) || isnan(tekanan)) {
    Serial.println("BME280 baca gagal");
    return;
  }

  tampilkanOLED(suhu, kelembaban, tekanan);

  char payload[128];
  snprintf(payload, sizeof(payload),
    "{\"suhu\":%.1f,\"kelembaban\":%.1f,\"tekanan\":%.2f}",
    suhu, kelembaban, tekanan);

  mqttClient.loop();
  if (mqttClient.publish(topicSensor.c_str(), payload, false)) {
    Serial.print("Publish OK → ");
    Serial.println(payload);
  } else {
    Serial.println("Publish gagal");
  }
}

void setup() {
  Serial.begin(115200);
  delay(500);

  initI2C();
  if (!initBME280() || !initOLED()) {
    while (true) delay(1000);
  }

  tampilkanOLED(0, 0, 0);
  display.clearDisplay();
  display.setTextSize(1);
  display.setTextColor(SSD1306_WHITE);
  display.setCursor(0, 24);
  display.println(F("Menghubungkan..."));
  display.display();

  if (!setupWiFiManager()) ESP.restart();
  if (!koneksiMQTT()) ESP.restart();

  bacaDanTampilkan();
}

void loop() {
  mqttClient.loop();
  delay(5000);
  bacaDanTampilkan();
}</code></pre>

<h2>Penjelasan Bagian Kritis</h2>
<ul>
  <li><strong><code>Wire.begin(21, 22)</code> sekali</strong> — BME280 dan OLED berbagi objek <code>Wire</code> yang sama</li>
  <li><strong><code>display.begin(SSD1306_SWITCHCAPVCC, 0x3C)</code></strong> — coba <code>0x3D</code> jika layar kosong</li>
  <li><strong><code>display.display()</code></strong> — wajib dipanggil setelah menggambar; tanpa ini layar tidak berubah</li>
  <li><strong><code>display.clearDisplay()</code></strong> — hapus buffer sebelum menggambar frame baru (hindari ghosting)</li>
  <li><strong>Urutan init</strong> — <code>Wire.begin</code> → BME280 → OLED; jika salah satu gagal, hentikan agar mudah debug</li>
  <li><strong><code>mqttClient.loop()</code></strong> — tetap dipanggil di <code>loop()</code> meski fokus artikel ini adalah layar</li>
</ul>

<h2>Uji Coba (Step-by-Step)</h2>
<ol>
  <li>Rakit BME280 + OLED paralel di breadboard, upload sketch, Serial Monitor <strong>115200</strong></li>
  <li>Pastikan OLED menampilkan teks <code>Menghubungkan...</code> lalu angka sensor</li>
  <li>Portal <code>KindoESP32-Setup</code> — isi WiFi + kredensial <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">broker (#16)</a></li>
  <li>Verifikasi angka di OLED ≈ Serial Monitor / MQTT Explorer</li>
  <li>Subscribe topic di laptop:</li>
</ol>

<pre><code class="language-bash">mosquitto_sub -h 192.168.1.50 -p 1883 \
  -u kindo_esp32 -P 'GANTI_PASSWORD_MQTT' \
  -t "kodingindonesia/esp32/bme280/data" -v</code></pre>

<blockquote>
  <p><strong>Pro tip:</strong> Untuk demo, tiup hangat ke BME280 — suhu di OLED naik dalam 2–3 detik. Topic unik per unit, misalnya <code>kodingindonesia/anton/esp32/bme280/data</code>.</p>
</blockquote>

<h2>Scanner I2C (Opsional)</h2>
<p>Jika salah satu modul tidak terdeteksi, jalankan sketch <strong>Scan I2C</strong> dari <a href="/artikel/i2c-esp32-sensor-bme280-suhu-tekanan-mqtt">artikel #13</a>. Kamu harus melihat <strong>dua alamat</strong> (misalnya <code>0x76</code> + <code>0x3C</code>).</p>

<h2>Tips &amp; Troubleshooting</h2>
<ul>
  <li><strong>Layar putih/kosong:</strong> Cek alamat <code>0x3C</code> vs <code>0x3D</code>, wiring 3.3V, dan panggilan <code>display.display()</code></li>
  <li><strong>Hanya BME280 terdeteksi:</strong> OLED mungkin modul SPI — pastikan beli varian <strong>I2C</strong> (4 pin: GND VCC SCL SDA)</li>
  <li><strong>Teks terpotong:</strong> Resolusi 128×64 — pakai <code>setTextSize(1)</code>; untuk font besar kurangi jumlah baris</li>
  <li><strong>Ghosting/berbayang:</strong> Selalu <code>clearDisplay()</code> sebelum menggambar ulang</li>
  <li><strong>BME280 gagal setelah pasang OLED:</strong> Cek beban 3.3V — dua modul + ESP32; gunakan USB port yang stabil</li>
  <li><strong>Portal WiFi tidak muncul:</strong> <code>wm.resetSettings()</code> atau buka <code>http://192.168.4.1</code> — sama seperti <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">#12</a></li>
  <li><strong>MQTT gagal (rc=-2):</strong> Broker tidak terjangkau — cek IP, firewall port 1883, satu jaringan WiFi</li>
  <li><strong>MQTT auth (rc=5):</strong> Lihat <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">troubleshooting broker #16</a></li>
  <li><strong>Compile error WiFiManager:</strong> Update library tzapu ke 2.x; pastikan board <strong>esp32 v3.x</strong></li>
  <li><strong>Compile error GFX:</strong> Install <strong>Adafruit GFX</strong> versi terbaru sebelum SSD1306</li>
  <li><strong>WiFi 2.4 GHz:</strong> ESP32 tidak support jaringan WiFi <strong>5 GHz saja</strong></li>
</ul>

<h2>Keamanan &amp; Produksi</h2>
<ul>
  <li>Jangan commit password MQTT ke Git — simpan lewat portal WiFiManager + NVS seperti <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">artikel #12</a></li>
  <li>OLED menampilkan data sensor di lokasi fisik — hindari menampilkan kredensial atau token di layar</li>
  <li>Gunakan <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">broker Mosquitto pribadi + auth (#16)</a> — jangan andalkan <code>test.mosquitto.org</code> untuk data produksi</li>
</ul>

<h2>Langkah Selanjutnya (Seri 2)</h2>
<ul>
  <li><strong><a href="/artikel/ota-update-firmware-esp32-via-wifi">OTA update firmware (#15)</a></strong> — update tanpa kabel USB (butuh <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">#12 WiFiManager</a>)</li>
  <li>Gabung <a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">deep sleep (#11)</a> + BME280 + OLED untuk node lapangan (matikan OLED saat tidur)</li>
  <li><strong><a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">Subscriber Python → MySQL (#18)</a></strong> untuk histori + OLED sebagai panel lokal</li>
  <li><strong><a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">InfluxDB + Grafana (#19)</a></strong> — dashboard cloud selain layar OLED</li>
  <li>Capstone <strong><a href="/artikel/smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt">greenhouse (#39)</a></strong> — sensor + layar + pompa relay</li>
</ul>

<p>Dengan OLED di bus I2C yang sama, node sensormu punya <strong>antarmuka lokal</strong> selain MQTT cloud. Lanjutkan di <a href="/artikel">halaman artikel</a> Koding Indonesia.</p>
HTML;
    }
}
