<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article5Seeder extends Seeder
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
            ['slug' => 'membaca-sensor-dht22-suhu-kelembaban-esp32'],
            [
                'user_id'           => $admin->id,
                'category_id'       => $iotCat->id,
                'title'             => 'Membaca Sensor DHT22 (Suhu & Kelembaban) dengan ESP32',
                'body'              => $this->body(),
                'status'            => 'published',
                'is_featured'       => false,
                'seo_title'         => 'Tutorial Sensor DHT22 dengan ESP32 - Baca Suhu & Kelembaban',
                'seo_description'   => 'Tutorial lengkap membaca suhu dan kelembaban dengan sensor DHT22 + ESP32. Wiring pin-ke-pin, library Adafruit, Serial Monitor, dan kirim data via WiFi.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament; hindari wipe saat re-seed

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        Tag::updateOrCreate(['slug' => 'dht22'], ['name' => 'dht22']);

        $tagSlugs = ['esp32', 'sensor', 'iot', 'dht22'];
        $tagIds   = Tag::whereIn('slug', $tagSlugs)->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command->info('✓ Artikel ke-5 berhasil dipublish: ' . $article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan</h2>
<p>DHT22 (juga dikenal sebagai <strong>AM2302</strong>) adalah sensor digital yang mengukur <strong>suhu</strong> dan <strong>kelembaban udara</strong> sekaligus. Sensor ini sangat populer di proyek IoT karena mudah dipakai dan akurasinya cukup untuk kebanyakan aplikasi rumah / laboratorium.</p>

<p>Di Seri 1 kita sudah mengenal GPIO lewat <a href="/artikel/blink-led-esp32-tutorial-pertama-embedded-system">Blink LED (#3)</a> dan koneksi jaringan di <a href="/artikel/menghubungkan-esp32-wifi-kirim-data-server">WiFi ESP32 (#4)</a>. Kali ini ESP32 membaca sensor nyata — fondasi untuk <a href="/artikel/membuat-web-server-esp32-monitoring-sensor-dht22">Web Server (#6)</a>, <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">MQTT (#7)</a>, dan proyek gabungan nanti.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> Sudah pernah upload sketch Arduino ke ESP32. Idealnya sudah baca <a href="/artikel/blink-led-esp32-tutorial-pertama-embedded-system">GPIO dasar (#3)</a>. Bagian WiFi di akhir artikel mengikuti pola <a href="/artikel/menghubungkan-esp32-wifi-kirim-data-server">koneksi WiFi (#4)</a>.</p>
</blockquote>

<h2>Spesifikasi DHT22</h2>
<ul>
  <li><strong>Range suhu:</strong> −40°C hingga +80°C</li>
  <li><strong>Akurasi suhu:</strong> ±0.5°C</li>
  <li><strong>Range kelembaban:</strong> 0% hingga 100% RH</li>
  <li><strong>Akurasi kelembaban:</strong> ±2–5% RH</li>
  <li><strong>Interface:</strong> Single-wire digital (protokol 1-wire khas DHT)</li>
  <li><strong>Tegangan:</strong> 3.3V atau 5V — di ESP32 kita pakai <strong>3.3V</strong></li>
</ul>

<blockquote>
  <p><strong>Catatan pin:</strong> Modul breakout biasanya punya 3 pin (VCC, DATA, GND) atau 4 pin (pin 3 NC). Ikuti label di PCB, bukan tebak urutan kaki sensor mentah.</p>
</blockquote>

<h2>Yang Kamu Butuhkan</h2>
<ul>
  <li>ESP32 DevKit + kabel jumper + breadboard</li>
  <li>Sensor DHT22 (modul breakout disarankan) + resistor pull-up <strong>10kΩ</strong> jika belum ada di modul</li>
  <li>Arduino IDE + library <strong>DHT sensor library</strong> (Adafruit)</li>
  <li>Opsional: WiFi 2.4 GHz untuk contoh kirim HTTP di bagian akhir</li>
</ul>

<h2>Wiring / Koneksi Hardware</h2>
<p>Koneksi DHT22 ke ESP32 sangat sederhana — pin DATA di <strong>GPIO 4</strong> (konsisten di seluruh Seri 1–2):</p>

<figure role="img" aria-label="Diagram wiring ESP32 ke DHT22: 3.3V ke VCC, GND ke GND, GPIO 4 ke DATA dengan pull-up 10k ohm" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 620 320" style="display:block;max-width:620px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="w5R" markerWidth="9" markerHeight="9" refX="8" refY="4.5" orient="auto" markerUnits="userSpaceOnUse"><path d="M0,0 L9,4.5 L0,9 Z" fill="#C62828"/></marker>
    <marker id="w5K" markerWidth="9" markerHeight="9" refX="8" refY="4.5" orient="auto" markerUnits="userSpaceOnUse"><path d="M0,0 L9,4.5 L0,9 Z" fill="#1a1a1a"/></marker>
    <marker id="w5O" markerWidth="9" markerHeight="9" refX="8" refY="4.5" orient="auto" markerUnits="userSpaceOnUse"><path d="M0,0 L9,4.5 L0,9 Z" fill="#FF7A2F"/></marker>
  </defs>
  <rect x="0" y="0" width="620" height="320" fill="#F5F5F0" rx="6"/>
  <rect x="30" y="40" width="170" height="200" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="115" y="68" text-anchor="middle" fill="#1a1a1a" font-size="13" font-weight="700">ESP32 DevKit</text>
  <circle cx="185" cy="110" r="5" fill="#C62828"/>
  <text x="170" y="115" text-anchor="end" fill="#1a1a1a" font-size="11" font-weight="600">3.3V</text>
  <circle cx="185" cy="155" r="5" fill="#1a1a1a"/>
  <text x="170" y="160" text-anchor="end" fill="#1a1a1a" font-size="11" font-weight="600">GND</text>
  <circle cx="185" cy="200" r="5" fill="#FF7A2F"/>
  <text x="170" y="197" text-anchor="end" fill="#1a1a1a" font-size="11" font-weight="600">GPIO 4</text>
  <text x="170" y="211" text-anchor="end" fill="#4A5568" font-size="9">DATA</text>
  <rect x="400" y="70" width="190" height="160" rx="6" fill="#C8E6C9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="495" y="98" text-anchor="middle" fill="#1a1a1a" font-size="13" font-weight="700">DHT22</text>
  <text x="495" y="116" text-anchor="middle" fill="#4A5568" font-size="10">1-wire · 3.3V</text>
  <circle cx="415" cy="145" r="5" fill="#C62828"/>
  <text x="430" y="150" fill="#1a1a1a" font-size="11" font-weight="600">VCC</text>
  <circle cx="415" cy="180" r="5" fill="#1a1a1a"/>
  <text x="430" y="185" fill="#1a1a1a" font-size="11" font-weight="600">GND</text>
  <circle cx="415" cy="215" r="5" fill="#FF7A2F"/>
  <text x="430" y="220" fill="#1a1a1a" font-size="11" font-weight="600">DATA</text>
  <polyline fill="none" points="190,110 300,110 300,145 410,145" stroke="#C62828" stroke-width="2.5" marker-end="url(#w5R)"/>
  <polyline fill="none" points="190,155 320,155 320,180 410,180" stroke="#1a1a1a" stroke-width="2.5" marker-end="url(#w5K)"/>
  <polyline fill="none" points="190,200 340,200 340,215 410,215" stroke="#FF7A2F" stroke-width="2.5" marker-end="url(#w5O)"/>
  <text x="30" y="280" fill="#4A5568" font-size="10">3.3V→VCC · GND→GND · GPIO4→DATA · pull-up 10kΩ DATA→3.3V</text>
  <text x="30" y="300" fill="#4A5568" font-size="10">Modul breakout biasanya sudah punya pull-up internal</text>
</svg>
<figcaption style="margin-top:.75rem;font-size:.875rem;color:#4A5568;text-align:center">Wiring pin-ke-pin: 3.3V→VCC, GND→GND, GPIO 4→DATA. Pull-up 10kΩ penting jika modul belum punya.</figcaption>
</figure>

<ul>
  <li><strong>VCC</strong> (pin 1) → 3.3V ESP32</li>
  <li><strong>DATA</strong> (pin 2) → GPIO 4 (+ resistor 10kΩ ke VCC jika perlu)</li>
  <li><strong>GND</strong> (pin 4) → GND ESP32</li>
</ul>

<p>Resistor pull-up 10kΩ antara pin DATA dan VCC sangat penting untuk komunikasi yang stabil!</p>

<h2>Alur Pembacaan Sensor</h2>
<p>Sketch dasar hanya butuh Serial Monitor. Setelah data stabil, kamu bisa lanjut ke browser lewat <a href="/artikel/membuat-web-server-esp32-monitoring-sensor-dht22">Web Server (#6)</a> atau publish ke broker lewat <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">MQTT (#7)</a>.</p>

<figure role="img" aria-label="Diagram alur: DHT22 ke ESP32 lalu ke Serial Monitor, dengan jalur opsional Web Server dan MQTT" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 620 300" style="display:block;max-width:620px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="a5G" markerWidth="9" markerHeight="9" refX="8" refY="4.5" orient="auto" markerUnits="userSpaceOnUse"><path d="M0,0 L9,4.5 L0,9 Z" fill="#2E7D32"/></marker>
    <marker id="a5B" markerWidth="9" markerHeight="9" refX="8" refY="4.5" orient="auto" markerUnits="userSpaceOnUse"><path d="M0,0 L9,4.5 L0,9 Z" fill="#2979FF"/></marker>
    <marker id="a5O" markerWidth="9" markerHeight="9" refX="8" refY="4.5" orient="auto" markerUnits="userSpaceOnUse"><path d="M0,0 L9,4.5 L0,9 Z" fill="#FF7A2F"/></marker>
  </defs>
  <rect x="0" y="0" width="620" height="300" fill="#F5F5F0" rx="6"/>
  <rect x="30" y="100" width="140" height="70" rx="6" fill="#C8E6C9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="100" y="130" text-anchor="middle" fill="#1a1a1a" font-size="13" font-weight="700">DHT22</text>
  <text x="100" y="150" text-anchor="middle" fill="#4A5568" font-size="10">suhu · RH</text>
  <line x1="170" y1="135" x2="230" y2="135" stroke="#2E7D32" stroke-width="2.5" marker-end="url(#a5G)"/>
  <rect x="240" y="90" width="150" height="90" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="315" y="125" text-anchor="middle" fill="#1a1a1a" font-size="13" font-weight="700">ESP32</text>
  <text x="315" y="145" text-anchor="middle" fill="#4A5568" font-size="10">GPIO 4 · DHT.h</text>
  <text x="315" y="163" text-anchor="middle" fill="#4A5568" font-size="10">baca tiap 2 detik</text>
  <line x1="390" y1="135" x2="450" y2="135" stroke="#2979FF" stroke-width="2.5" marker-end="url(#a5B)"/>
  <rect x="460" y="100" width="130" height="70" rx="6" fill="#2979FF" stroke="#000" stroke-width="2.5"/>
  <text x="525" y="130" text-anchor="middle" fill="#fff" font-size="12" font-weight="700">Serial</text>
  <text x="525" y="150" text-anchor="middle" fill="#e3f2fd" font-size="10">115200 baud</text>
  <line x1="315" y1="180" x2="315" y2="220" stroke="#FF7A2F" stroke-width="2.5" marker-end="url(#a5O)"/>
  <rect x="200" y="228" width="110" height="44" rx="6" fill="#FFF8E7" stroke="#FF7A2F" stroke-width="2"/>
  <text x="255" y="246" text-anchor="middle" fill="#1a1a1a" font-size="11" font-weight="700">Web (#6)</text>
  <text x="255" y="262" text-anchor="middle" fill="#4A5568" font-size="9">browser LAN</text>
  <rect x="330" y="228" width="110" height="44" rx="6" fill="#FFF8E7" stroke="#FF7A2F" stroke-width="2"/>
  <text x="385" y="246" text-anchor="middle" fill="#1a1a1a" font-size="11" font-weight="700">MQTT (#7)</text>
  <text x="385" y="262" text-anchor="middle" fill="#4A5568" font-size="9">broker IoT</text>
  <text x="310" y="40" text-anchor="middle" fill="#4A5568" font-size="11">Artikel ini: fokus baca sensor → Serial. Jalur oranye = langkah selanjutnya.</text>
</svg>
<figcaption style="margin-top:.75rem;font-size:.875rem;color:#4A5568;text-align:center">Alur dasar: DHT22 → ESP32 → Serial. Lanjut ke <a href="/artikel/membuat-web-server-esp32-monitoring-sensor-dht22">Web Server (#6)</a> atau <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">MQTT (#7)</a>.</figcaption>
</figure>

<h2>Install Library DHT</h2>
<p>Kita perlu menginstall library DHT sensor dari Adafruit:</p>
<ol>
  <li>Buka Arduino IDE</li>
  <li>Klik <strong>Sketch → Include Library → Manage Libraries</strong></li>
  <li>Cari <strong>"DHT sensor library"</strong> oleh Adafruit</li>
  <li>Klik Install (juga install dependency <strong>Adafruit Unified Sensor</strong> jika diminta)</li>
</ol>

<h2>Kode Program: Baca Suhu dan Kelembaban</h2>
<p>Upload sketch berikut, lalu buka Serial Monitor di baud <strong>115200</strong>. DHT22 butuh jeda ~2 detik antar pembacaan.</p>

<pre><code class="language-arduino">#include &lt;DHT.h&gt;

#define DHT_PIN 4        // Pin data DHT22 terhubung ke GPIO 4
#define DHT_TYPE DHT22   // Tipe sensor: DHT22

DHT dht(DHT_PIN, DHT_TYPE);

void setup() {
  Serial.begin(115200);
  dht.begin();
  Serial.println("DHT22 Sensor Siap!");
}

void loop() {
  // Tunggu 2 detik antara pembacaan (DHT22 butuh waktu)
  delay(2000);

  // Baca kelembaban
  float kelembaban = dht.readHumidity();

  // Baca suhu dalam Celsius
  float suhu = dht.readTemperature();

  // Baca suhu dalam Fahrenheit
  float suhuF = dht.readTemperature(true);

  // Cek apakah pembacaan berhasil
  if (isnan(kelembaban) || isnan(suhu)) {
    Serial.println("Gagal membaca dari sensor DHT22!");
    return;
  }

  // Hitung Heat Index
  float heatIndex = dht.computeHeatIndex(suhu, kelembaban, false);

  // Tampilkan hasil
  Serial.println("=== Pembacaan DHT22 ===");
  Serial.print("Kelembaban: ");
  Serial.print(kelembaban);
  Serial.println(" %");

  Serial.print("Suhu: ");
  Serial.print(suhu);
  Serial.println(" °C");

  Serial.print("Suhu: ");
  Serial.print(suhuF);
  Serial.println(" °F");

  Serial.print("Heat Index: ");
  Serial.print(heatIndex);
  Serial.println(" °C");
  Serial.println();
}
</code></pre>

<h2>Kirim Data DHT22 ke Server via WiFi</h2>
<p>Kombinasikan dengan WiFi (pola <a href="/artikel/menghubungkan-esp32-wifi-kirim-data-server">artikel WiFi (#4)</a>) untuk mengirim data sensor ke server. Ganti placeholder <code>GANTI_SSID_WIFI</code> / <code>GANTI_PASSWORD_WIFI</code>. Untuk produksi tanpa hardcode, lihat <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">NVS + WiFiManager (#12)</a>.</p>

<pre><code class="language-arduino">#include &lt;WiFi.h&gt;
#include &lt;HTTPClient.h&gt;
#include &lt;DHT.h&gt;

#define DHT_PIN 4
#define DHT_TYPE DHT22

const char* ssid     = "GANTI_SSID_WIFI";
const char* password = "GANTI_PASSWORD_WIFI";

DHT dht(DHT_PIN, DHT_TYPE);

void setup() {
  Serial.begin(115200);
  dht.begin();

  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
  }
  Serial.println("WiFi terhubung!");
}

void loop() {
  delay(10000); // Baca dan kirim setiap 10 detik

  float suhu = dht.readTemperature();
  float kelembaban = dht.readHumidity();

  if (!isnan(suhu) &amp;&amp; !isnan(kelembaban)) {
    HTTPClient http;
    http.begin("http://api.server.com/sensor");
    http.addHeader("Content-Type", "application/json");

    String payload = "{\"suhu\":" + String(suhu, 2) +
                     ",\"kelembaban\":" + String(kelembaban, 2) + "}";

    int code = http.POST(payload);
    Serial.println(code == 200 ? "Data terkirim!" : "Gagal kirim data");
    http.end();
  }
}
</code></pre>

<blockquote>
  <p><strong>Tips Troubleshooting:</strong> Jika pembacaan selalu NaN (Not a Number), periksa koneksi kabel dan pastikan resistor pull-up 10kΩ terpasang dengan benar. DHT22 sensitif terhadap noise listrik — jauhkan dari sumber interferensi. Pastikan juga <code>delay(2000)</code> setelah <code>dht.begin()</code> sebelum baca pertama.</p>
</blockquote>

<blockquote>
  <p><strong>Pin strapping:</strong> Hindari GPIO yang dipakai saat boot (mis. beberapa board sensitif di GPIO 0, 2, 12, 15). GPIO 4 aman untuk DHT22 di kebanyakan DevKit.</p>
</blockquote>

<h2>Langkah Selanjutnya</h2>
<ul>
  <li><a href="/artikel/membuat-web-server-esp32-monitoring-sensor-dht22">Web Server ESP32 + DHT22 (#6)</a> — tampilkan data di browser</li>
  <li><a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">Publish data sensor via MQTT (#7)</a> — kirim ke broker IoT</li>
  <li><a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">Deep Sleep DHT22 (#11)</a> — node sensor hemat baterai</li>
  <li><strong>Seri 2:</strong> <a href="/artikel/i2c-esp32-sensor-bme280-suhu-tekanan-mqtt">Sensor BME280 via I2C (#13)</a> — upgrade akurasi + tekanan udara</li>
  <li><strong>Seri 2:</strong> <a href="/artikel/adc-esp32-sensor-analog-soil-moisture-ldr-mqtt">ADC Soil Moisture &amp; LDR (#35)</a> — kelembaban tanah &amp; cahaya analog</li>
</ul>
HTML;
    }
}
