<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article4Seeder extends Seeder
{
    public function run(): void
    {
        $admin  = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first();

        if (! $admin || ! $iotCat) {
            $this->command->error('User atau kategori tidak ditemukan. Jalankan DatabaseSeeder dulu.');

            return;
        }

        Tag::updateOrCreate(['slug' => 'http'], ['name' => 'http']);

        $article = Article::updateOrCreate(
            ['slug' => 'menghubungkan-esp32-wifi-kirim-data-server'],
            [
                'user_id'           => $admin->id,
                'category_id'       => $iotCat->id,
                'title'             => 'Menghubungkan ESP32 ke WiFi dan Kirim Data ke Server',
                'body'              => $this->body(),
                'status'            => 'published',
                'is_featured'       => false,
                'seo_title'         => 'Tutorial Menghubungkan ESP32 ke WiFi dan Kirim Data HTTP',
                'seo_description'   => 'Pelajari cara menghubungkan ESP32 ke jaringan WiFi 2.4 GHz dan mengirim data sensor ke server menggunakan HTTP GET/POST dengan Arduino IDE.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament; hindari wipe saat re-seed

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagSlugs = ['esp32', 'wifi', 'iot', 'http'];
        $tagIds   = Tag::whereIn('slug', $tagSlugs)->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command->info('✓ Artikel ke-4 berhasil dipublish: ' . $article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan</h2>
<p>Salah satu keunggulan utama ESP32 adalah kemampuannya terhubung ke WiFi. Pada tutorial ini, kita belajar menghubungkan ESP32 ke jaringan WiFi 2.4 GHz dan mengirim data ke server menggunakan protokol HTTP — fondasi untuk <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">sensor DHT22 (#5)</a>, <a href="/artikel/membuat-web-server-esp32-monitoring-sensor-dht22">Web Server (#6)</a>, dan <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">MQTT (#7)</a>.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> Sudah bisa upload sketch dari <a href="/artikel/blink-led-esp32-tutorial-pertama-embedded-system">Blink LED (#3)</a> dan <a href="/artikel/cara-install-arduino-ide-setup-esp32-board-manager">install Arduino IDE (#2)</a>.</p>
</blockquote>

<h2>Topologi Jaringan</h2>
<p>ESP32 terhubung ke router WiFi rumah/kantor, lalu berkomunikasi dengan server HTTP di internet atau LAN:</p>

<figure role="img" aria-label="Diagram topologi ESP32 ke router WiFi lalu server HTTP GET POST" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 620 280" style="display:block;max-width:620px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="w4B" markerWidth="9" markerHeight="9" refX="8" refY="4.5" orient="auto" markerUnits="userSpaceOnUse"><path d="M0,0 L9,4.5 L0,9 Z" fill="#2979FF"/></marker>
    <marker id="w4G" markerWidth="9" markerHeight="9" refX="8" refY="4.5" orient="auto" markerUnits="userSpaceOnUse"><path d="M0,0 L9,4.5 L0,9 Z" fill="#2E7D32"/></marker>
  </defs>
  <rect x="0" y="0" width="620" height="280" fill="#F5F5F0" rx="6"/>
  <rect x="40" y="100" width="130" height="80" rx="6" fill="#E8F4FF" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="105" y="130" text-anchor="middle" fill="#1a1a1a" font-size="13" font-weight="700">ESP32</text>
  <text x="105" y="150" text-anchor="middle" fill="#4A5568" font-size="10">WiFi.h · HTTPClient</text>
  <text x="105" y="168" text-anchor="middle" fill="#4A5568" font-size="10">2.4 GHz client</text>
  <line x1="170" y1="140" x2="218" y2="140" stroke="#2979FF" stroke-width="2.5" marker-end="url(#w4B)"/>
  <text x="188" y="128" fill="#2979FF" font-size="10" font-weight="600">WiFi</text>
  <rect x="224" y="90" width="140" height="100" rx="6" fill="#FFF8E7" stroke="#FF7A2F" stroke-width="2.5"/>
  <text x="294" y="125" text-anchor="middle" fill="#1a1a1a" font-size="13" font-weight="700">Router WiFi</text>
  <text x="294" y="145" text-anchor="middle" fill="#4A5568" font-size="10">SSID 2.4 GHz</text>
  <text x="294" y="163" text-anchor="middle" fill="#4A5568" font-size="10">DHCP → IP lokal</text>
  <line x1="364" y1="140" x2="412" y2="140" stroke="#2E7D32" stroke-width="2.5" marker-end="url(#w4G)"/>
  <text x="382" y="128" fill="#2E7D32" font-size="10" font-weight="600">HTTP</text>
  <rect x="418" y="90" width="160" height="100" rx="6" fill="#C8E6C9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="498" y="125" text-anchor="middle" fill="#1a1a1a" font-size="13" font-weight="700">Server HTTP</text>
  <text x="498" y="145" text-anchor="middle" fill="#4A5568" font-size="10">GET / POST</text>
  <text x="498" y="163" text-anchor="middle" fill="#4A5568" font-size="10">JSON payload</text>
  <text x="310" y="240" text-anchor="middle" fill="#4A5568" font-size="11">Ganti GANTI_SSID_WIFI / GANTI_PASSWORD_WIFI — jangan commit kredensial nyata</text>
</svg>
<figcaption style="margin-top:.75rem;font-size:.875rem;color:#4A5568;text-align:center">Topologi: ESP32 → router WiFi → server HTTP (GET/POST).</figcaption>
</figure>

<h2>Library yang Digunakan</h2>
<p>Untuk koneksi WiFi, ESP32 sudah memiliki library bawaan <code>WiFi.h</code> yang termasuk saat menginstall ESP32 Board Manager. Untuk HTTP request, kita pakai <code>HTTPClient.h</code>.</p>

<h2>Alur Koneksi WiFi</h2>
<figure role="img" aria-label="Diagram alur koneksi WiFi: connect, dapat IP, lalu HTTP request" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 620 200" style="display:block;max-width:620px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="a4B" markerWidth="9" markerHeight="9" refX="8" refY="4.5" orient="auto" markerUnits="userSpaceOnUse"><path d="M0,0 L9,4.5 L0,9 Z" fill="#2979FF"/></marker>
  </defs>
  <rect x="0" y="0" width="620" height="200" fill="#F5F5F0" rx="6"/>
  <rect x="30" y="70" width="115" height="60" rx="6" fill="#E8F4FF" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="87" y="95" text-anchor="middle" fill="#1a1a1a" font-size="11" font-weight="700">WiFi.begin()</text>
  <text x="87" y="112" text-anchor="middle" fill="#4A5568" font-size="9">SSID + password</text>
  <line x1="145" y1="100" x2="163" y2="100" stroke="#2979FF" stroke-width="2.5" marker-end="url(#a4B)"/>
  <rect x="169" y="70" width="115" height="60" rx="6" fill="#FFF8E7" stroke="#FF7A2F" stroke-width="2.5"/>
  <text x="226" y="95" text-anchor="middle" fill="#1a1a1a" font-size="11" font-weight="700">WL_CONNECTED</text>
  <text x="226" y="112" text-anchor="middle" fill="#4A5568" font-size="9">tunggu loop</text>
  <line x1="284" y1="100" x2="302" y2="100" stroke="#2979FF" stroke-width="2.5" marker-end="url(#a4B)"/>
  <rect x="308" y="70" width="115" height="60" rx="6" fill="#C8E6C9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="365" y="95" text-anchor="middle" fill="#1a1a1a" font-size="11" font-weight="700">localIP()</text>
  <text x="365" y="112" text-anchor="middle" fill="#4A5568" font-size="9">192.168.x.x</text>
  <line x1="423" y1="100" x2="441" y2="100" stroke="#2979FF" stroke-width="2.5" marker-end="url(#a4B)"/>
  <rect x="447" y="70" width="145" height="60" rx="6" fill="#2979FF" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="519" y="95" text-anchor="middle" fill="#fff" font-size="11" font-weight="700">HTTP GET/POST</text>
  <text x="519" y="112" text-anchor="middle" fill="#e3f2fd" font-size="9">HTTPClient.h</text>
  <text x="310" y="165" text-anchor="middle" fill="#4A5568" font-size="11">Produksi: lihat NVS + WiFiManager (#12) di artikel terpisah</text>
</svg>
<figcaption style="margin-top:.75rem;font-size:.875rem;color:#4A5568;text-align:center">Alur: connect → dapat IP → kirim HTTP request.</figcaption>
</figure>

<h2>Menghubungkan ESP32 ke WiFi</h2>
<p>Kode dasar untuk menghubungkan ESP32 ke jaringan WiFi. Ganti placeholder dengan SSID dan password jaringan kamu:</p>

<pre><code class="language-arduino">#include &lt;WiFi.h&gt;

const char* ssid     = "GANTI_SSID_WIFI";
const char* password = "GANTI_PASSWORD_WIFI";

void setup() {
  Serial.begin(115200);

  WiFi.begin(ssid, password);
  Serial.print("Menghubungkan ke WiFi");

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println();
  Serial.println("Terhubung ke WiFi!");
  Serial.print("IP Address: ");
  Serial.println(WiFi.localIP());
}

void loop() {
  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("WiFi: Terhubung");
  } else {
    Serial.println("WiFi: Terputus, mencoba reconnect...");
    WiFi.reconnect();
  }
  delay(5000);
}</code></pre>

<h2>Mengirim Data ke Server (HTTP GET)</h2>
<p>Setelah terhubung ke WiFi, kirim data ke server menggunakan HTTP request:</p>

<pre><code class="language-arduino">#include &lt;WiFi.h&gt;
#include &lt;HTTPClient.h&gt;

const char* ssid     = "GANTI_SSID_WIFI";
const char* password = "GANTI_PASSWORD_WIFI";
const char* serverURL = "http://api.example.com/data";

void setup() {
  Serial.begin(115200);
  WiFi.begin(ssid, password);

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println();
  Serial.println("WiFi terhubung!");
}

void loop() {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;

    String url = String(serverURL) + "?suhu=25.5&amp;kelembaban=70";
    http.begin(url);

    int httpCode = http.GET();

    if (httpCode &gt; 0) {
      Serial.print("HTTP Code: ");
      Serial.println(httpCode);

      if (httpCode == HTTP_CODE_OK) {
        String payload = http.getString();
        Serial.println("Response: " + payload);
      }
    } else {
      Serial.print("Error: ");
      Serial.println(http.errorToString(httpCode));
    }

    http.end();
  }

  delay(10000);
}</code></pre>

<h2>Mengirim Data dengan HTTP POST</h2>
<p>Untuk data yang lebih kompleks, gunakan HTTP POST dengan format JSON:</p>

<pre><code class="language-arduino">void kirimDataJSON(float suhu, float kelembaban) {
  HTTPClient http;
  http.begin(serverURL);
  http.addHeader("Content-Type", "application/json");

  String jsonData = "{\"suhu\":" + String(suhu) +
                    ",\"kelembaban\":" + String(kelembaban) +
                    ",\"device\":\"ESP32-001\"}";

  int httpCode = http.POST(jsonData);

  if (httpCode == 200) {
    Serial.println("Data berhasil dikirim!");
  }

  http.end();
}</code></pre>

<blockquote>
  <p><strong>Produksi:</strong> Jangan hardcode password WiFi di kode yang dipublish ke repository publik. Untuk deploy ke banyak perangkat, gunakan <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">NVS + WiFiManager (#12)</a> agar SSID bisa dikonfigurasi lewat portal captive tanpa re-flash.</p>
</blockquote>

<blockquote>
  <p><strong>Tips:</strong> ESP32 hanya mendukung WiFi 2.4 GHz — pastikan router tidak memaksa perangkat ke band 5 GHz saja. Jika HTTP gagal, cek firewall router dan pastikan URL server dapat dijangkau dari jaringan yang sama.</p>
</blockquote>

<h2>Langkah Selanjutnya</h2>
<ul>
  <li><a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">Membaca sensor DHT22 (#5)</a> — gabungkan sensor dengan WiFi</li>
  <li><a href="/artikel/membuat-web-server-esp32-monitoring-sensor-dht22">Web Server ESP32 + DHT22 (#6)</a> — tampilkan data di browser LAN</li>
  <li><a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">Publish data via MQTT (#7)</a> — alternatif HTTP untuk IoT</li>
  <li><a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">NVS + WiFiManager (#12)</a> — konfigurasi WiFi tanpa hardcode</li>
  <li><strong>Seri 2:</strong> <a href="/artikel/esp32-firebase-realtime-database-sensor-cloud">ESP32 + Firebase (#30)</a> — simpan data sensor di cloud</li>
</ul>
HTML;
    }
}
