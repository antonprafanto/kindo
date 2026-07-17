<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article6Seeder extends Seeder
{
    public function run(): void
    {
        $admin  = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first();

        if (!$admin || !$iotCat) {
            $this->command->error('User atau kategori tidak ditemukan. Jalankan DatabaseSeeder dulu.');
            return;
        }

        $article = Article::updateOrCreate(
            ['slug' => 'membuat-web-server-esp32-monitoring-sensor-dht22'],
            [
                'user_id'         => $admin->id,
                'category_id'     => $iotCat->id,
                'title'           => 'Membuat Web Server ESP32 untuk Monitoring Sensor DHT22',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'Tutorial Web Server ESP32 + DHT22 — Monitoring Suhu via Browser',
                'seo_description' => 'Buat dashboard monitoring suhu dan kelembaban langsung di browser. Gabungkan ESP32 WebServer, WiFi, dan sensor DHT22 dalam satu proyek IoT.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament; hindari wipe saat re-seed

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagSlugs = ['esp32', 'iot', 'wifi', 'sensor', 'api'];
        $tagIds   = Tag::whereIn('slug', $tagSlugs)->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command->info('✓ Artikel ke-6 berhasil dipublish: ' . $article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan</h2>
<p>Di artikel sebelumnya kita sudah belajar <a href="/artikel/menghubungkan-esp32-wifi-kirim-data-server">menghubungkan ESP32 ke WiFi (#4)</a> dan <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">membaca data sensor DHT22 (#5)</a>. Kali ini kita akan menggabungkan keduanya menjadi satu proyek lengkap: <strong>Web Server di ESP32</strong> yang menampilkan suhu dan kelembaban langsung di browser HP atau laptop kamu — tanpa server eksternal!</p>

<p>Ini adalah langkah penting menuju smart home dan IoT dashboard. Kamu tidak perlu hosting cloud untuk monitoring sederhana; ESP32 sendiri bisa menjadi server mini.</p>

<h2>Yang Kamu Butuhkan</h2>
<ul>
  <li>Board ESP32 DevKit (sudah terpasang di breadboard dari tutorial sebelumnya)</li>
  <li>Sensor DHT22 + resistor pull-up 10kΩ</li>
  <li>Kabel jumper</li>
  <li>Laptop/PC dengan Arduino IDE</li>
  <li>HP atau laptop yang terhubung ke WiFi yang sama dengan ESP32</li>
</ul>

<blockquote>
  <p><strong>Prasyarat:</strong> Pastikan kamu sudah membaca artikel <a href="/artikel/menghubungkan-esp32-wifi-kirim-data-server"><em>Menghubungkan ESP32 ke WiFi (#4)</em></a> dan <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32"><em>Membaca Sensor DHT22 (#5)</em></a>. Kode di bawah menggabungkan kedua konsep tersebut.</p>
</blockquote>

<h2>Wiring Hardware</h2>
<p>Koneksi DHT22 ke ESP32 (sama seperti <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">tutorial DHT22 (#5)</a>):</p>
<ul>
  <li><strong>VCC</strong> → 3.3V ESP32</li>
  <li><strong>DATA</strong> → GPIO 4 (+ resistor 10kΩ ke VCC)</li>
  <li><strong>GND</strong> → GND ESP32</li>
</ul>

<h2>Install Library yang Diperlukan</h2>
<p>Pastikan library berikut sudah terinstall di Arduino IDE:</p>
<ol>
  <li><strong>DHT sensor library</strong> — oleh Adafruit</li>
  <li><strong>Adafruit Unified Sensor</strong> — dependency DHT library</li>
</ol>
<p>Library <code>WiFi</code> dan <code>WebServer</code> sudah built-in di ESP32 board package, tidak perlu install tambahan.</p>

<h2>Kode Program: Web Server + DHT22</h2>
<p>Buka Arduino IDE, buat sketch baru, lalu salin kode berikut. Ganti placeholder <code>GANTI_SSID_WIFI</code> / <code>GANTI_PASSWORD_WIFI</code>. Untuk produksi tanpa hardcode, lihat <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">NVS + WiFiManager (#12)</a>.</p>

<pre><code class="language-arduino">#include &lt;WiFi.h&gt;
#include &lt;WebServer.h&gt;
#include &lt;DHT.h&gt;

// ── Konfigurasi WiFi ──────────────────────────────────
const char* ssid     = "GANTI_SSID_WIFI";
const char* password = "GANTI_PASSWORD_WIFI";

// ── Konfigurasi DHT22 ───────────────────────────────────
#define DHT_PIN  4
#define DHT_TYPE DHT22
DHT dht(DHT_PIN, DHT_TYPE);

// ── Web Server di port 80 ───────────────────────────────
WebServer server(80);

// Variabel global untuk data sensor
float suhuTerakhir      = 0;
float kelembabanTerakhir = 0;
unsigned long waktuBacaTerakhir = 0;
const unsigned long intervalBaca = 2000; // baca setiap 2 detik

// ── Halaman utama (HTML) ────────────────────────────────
String halamanDashboard() {
  String html = "&lt;!DOCTYPE html&gt;&lt;html lang='id'&gt;";
  html += "&lt;head&gt;&lt;meta charset='UTF-8'&gt;";
  html += "&lt;meta name='viewport' content='width=device-width, initial-scale=1'&gt;";
  html += "&lt;title&gt;ESP32 Sensor Monitor&lt;/title&gt;";
  html += "&lt;style&gt;";
  html += "body{font-family:system-ui,sans-serif;max-width:480px;margin:40px auto;padding:20px;background:#f0f4f8;}";
  html += "h1{color:#2979FF;font-size:1.5rem;}";
  html += ".card{background:#fff;border:3px solid #000;box-shadow:4px 4px 0 #000;padding:24px;margin:16px 0;}";
  html += ".nilai{font-size:2.5rem;font-weight:800;color:#2D3748;}";
  html += ".label{color:#666;font-size:0.9rem;text-transform:uppercase;letter-spacing:1px;}";
  html += ".footer{text-align:center;color:#999;font-size:0.8rem;margin-top:24px;}";
  html += "&lt;/style&gt;&lt;/head&gt;&lt;body&gt;";
  html += "&lt;h1&gt;🌡️ ESP32 Sensor Monitor&lt;/h1&gt;";
  html += "&lt;div class='card'&gt;&lt;div class='label'&gt;Suhu&lt;/div&gt;";
  html += "&lt;div class='nilai'&gt;" + String(suhuTerakhir, 1) + " °C&lt;/div&gt;&lt;/div&gt;";
  html += "&lt;div class='card'&gt;&lt;div class='label'&gt;Kelembaban&lt;/div&gt;";
  html += "&lt;div class='nilai'&gt;" + String(kelembabanTerakhir, 1) + " %&lt;/div&gt;&lt;/div&gt;";
  html += "&lt;div class='footer'&gt;Koding Indonesia · ESP32 IoT&lt;/div&gt;";
  html += "&lt;/body&gt;&lt;/html&gt;";
  return html;
}

// ── API JSON untuk data sensor ──────────────────────────
void handleAPI() {
  String json = "{";
  json += "\"suhu\":" + String(suhuTerakhir, 2) + ",";
  json += "\"kelembaban\":" + String(kelembabanTerakhir, 2) + ",";
  json += "\"device\":\"ESP32-001\"";
  json += "}";
  server.send(200, "application/json", json);
}

// ── Baca sensor DHT22 ───────────────────────────────────
void bacaSensor() {
  if (millis() - waktuBacaTerakhir &lt; intervalBaca) return;
  waktuBacaTerakhir = millis();

  float h = dht.readHumidity();
  float t = dht.readTemperature();

  if (!isnan(h) &amp;&amp; !isnan(t)) {
    kelembabanTerakhir = h;
    suhuTerakhir       = t;
  }
}

void setup() {
  Serial.begin(115200);
  dht.begin();

  // Koneksi WiFi
  Serial.print("Menghubungkan ke WiFi");
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi terhubung!");
  Serial.print("Buka browser: http://");
  Serial.println(WiFi.localIP());

  // Daftarkan route web server
  server.on("/",       []() { server.send(200, "text/html", halamanDashboard()); });
  server.on("/api/data", handleAPI);
  server.onNotFound([]() { server.send(404, "text/plain", "Halaman tidak ditemukan"); });

  server.begin();
  Serial.println("Web Server aktif di port 80");
}

void loop() {
  server.handleClient(); // tangani request HTTP
  bacaSensor();          // baca DHT22 secara berkala
}</code></pre>

<h2>Upload dan Uji Coba</h2>
<ol>
  <li>Pilih board <strong>ESP32 Dev Module</strong> dan port COM yang benar</li>
  <li>Klik <strong>Upload</strong> dan tunggu hingga selesai</li>
  <li>Buka <strong>Serial Monitor</strong> (115200 baud)</li>
  <li>Catat alamat IP yang muncul, contoh: <code>192.168.1.100</code></li>
  <li>Buka browser di HP/laptop (WiFi yang sama), ketik: <code>http://192.168.1.100</code></li>
</ol>

<p>Kamu akan melihat halaman dashboard dengan nilai suhu dan kelembaban real-time!</p>

<h2>Endpoint API JSON</h2>
<p>Selain halaman HTML, ESP32 juga menyediakan endpoint API untuk integrasi dengan aplikasi lain:</p>

<pre><code class="language-bash">GET http://192.168.1.100/api/data</code></pre>

<p>Response JSON:</p>

<pre><code class="language-json">{
  "suhu": 28.50,
  "kelembaban": 65.30,
  "device": "ESP32-001"
}</code></pre>

<p>Endpoint ini bisa dipakai untuk membangun dashboard custom, mengirim data ke server cloud, atau diintegrasikan dengan <a href="/artikel/home-assistant-integrasi-esp32-mqtt">Home Assistant (#21)</a> di fase berikutnya.</p>

<h2>Cara Kerja Web Server ESP32</h2>
<p>Berikut alur singkat yang terjadi di balik layar:</p>
<figure role="img" aria-label="Diagram alur web server ESP32: DHT22 dibaca ESP32, WebServer port 80 melayani browser HTML dan endpoint API JSON" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 620 420" style="display:block;max-width:620px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="wsArr" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/></marker>
    <marker id="wsArrO" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#FF7A2F"/></marker>
    <marker id="wsArrG" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#2E7D32"/></marker>
  </defs>
  <rect x="0" y="0" width="620" height="420" fill="#F5F5F0" rx="6"/>
  <!-- DHT22 -->
  <rect x="195" y="15" width="230" height="55" rx="6" fill="#FFF3E8" stroke="#000" stroke-width="2.5"/>
  <text x="310" y="38" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">DHT22</text>
  <text x="310" y="56" text-anchor="middle" fill="#4A5568" font-size="10">suhu · kelembaban · GPIO 4</text>
  <!-- Arrow DHT → ESP32 -->
  <line x1="310" y1="70" x2="310" y2="108" stroke="#FF7A2F" stroke-width="2.5" marker-end="url(#wsArrO)"/>
  <text x="355" y="95" fill="#FF7A2F" font-size="10" font-weight="700">baca tiap 2s ↓</text>
  <!-- ESP32 WebServer -->
  <rect x="130" y="115" width="360" height="85" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="310" y="145" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">ESP32 WebServer :80</text>
  <text x="310" y="165" text-anchor="middle" fill="#4A5568" font-size="10">WiFi LAN · IP lokal (mis. 192.168.1.100)</text>
  <text x="310" y="183" text-anchor="middle" fill="#4A5568" font-size="10">handleClient() · non-blocking di loop()</text>
  <!-- Two outputs -->
  <line x1="220" y1="200" x2="140" y2="268" stroke="#2979FF" stroke-width="2.5" marker-end="url(#wsArr)"/>
  <line x1="400" y1="200" x2="480" y2="268" stroke="#2E7D32" stroke-width="2.5" marker-end="url(#wsArrG)"/>
  <!-- Label GET / — di kiri panah, tidak menimpa garis -->
  <rect x="55" y="222" width="70" height="22" rx="11" fill="#fff" stroke="#2979FF" stroke-width="1.5"/>
  <text x="90" y="237" text-anchor="middle" fill="#2979FF" font-size="10" font-weight="700">GET /</text>
  <!-- Label GET /api/data — di kanan panah -->
  <rect x="490" y="222" width="110" height="22" rx="11" fill="#fff" stroke="#2E7D32" stroke-width="1.5"/>
  <text x="545" y="237" text-anchor="middle" fill="#2E7D32" font-size="10" font-weight="700">GET /api/data</text>
  <!-- Browser -->
  <rect x="30" y="280" width="220" height="60" rx="6" fill="#FFF3E8" stroke="#000" stroke-width="2"/>
  <text x="140" y="305" text-anchor="middle" fill="#1a1a1a" font-size="12" font-weight="700">Browser HP / Laptop</text>
  <text x="140" y="323" text-anchor="middle" fill="#4A5568" font-size="10">halaman HTML dashboard</text>
  <!-- API client -->
  <rect x="370" y="280" width="220" height="60" rx="6" fill="#E8F5E9" stroke="#000" stroke-width="2"/>
  <text x="480" y="305" text-anchor="middle" fill="#1a1a1a" font-size="12" font-weight="700">Klien API / Integrasi</text>
  <text x="480" y="323" text-anchor="middle" fill="#4A5568" font-size="10">JSON suhu · kelembaban</text>
  <!-- Summary -->
  <text x="310" y="375" text-anchor="middle" fill="#4A5568" font-size="11">DHT22 → ESP32 → WebServer :80 → browser HTML + /api/data JSON</text>
  <text x="310" y="395" text-anchor="middle" fill="#4A5568" font-size="10">Hanya LAN — jangan expose ke internet tanpa autentikasi</text>
</svg>
<figcaption style="margin-top:.75rem;font-size:.875rem;color:#4A5568;text-align:center">ESP32 menjadi server mini di LAN: sensor <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">DHT22 (#5)</a> ditampilkan di browser, endpoint JSON siap untuk integrasi lanjut (mis. <a href="/artikel/home-assistant-integrasi-esp32-mqtt">Home Assistant (#21)</a>).</figcaption>
</figure>
<ol>
  <li>ESP32 terhubung ke WiFi dan mendapat alamat IP lokal (misalnya 192.168.1.100)</li>
  <li><code>WebServer</code> mendengarkan request HTTP di port 80</li>
  <li>Saat browser membuka <code>/</code>, ESP32 mengirim halaman HTML dashboard (data dari pembacaan berkala)</li>
  <li>Saat ada request ke <code>/api/data</code>, ESP32 mengirim data sensor dalam format JSON</li>
  <li>Di <code>loop()</code>, <code>server.handleClient()</code> memproses request secara non-blocking</li>
</ol>

<h2>Tips &amp; Troubleshooting</h2>
<ul>
  <li><strong>Halaman tidak bisa dibuka:</strong> Pastikan HP/laptop dan ESP32 di jaringan WiFi yang sama. Cek IP di Serial Monitor.</li>
  <li><strong>Nilai suhu NaN:</strong> Periksa wiring DHT22 dan resistor pull-up 10kΩ.</li>
  <li><strong>ESP32 restart terus:</strong> WiFi password salah atau sinyal terlalu lemah. Dekatkan ke router.</li>
  <li><strong>Data tidak update:</strong> Refresh browser. DHT22 butuh minimal 2 detik antar pembacaan.</li>
  <li><strong>Akses dari internet:</strong> Butuh port forwarding di router atau tunneling (ngrok, Cloudflare Tunnel) — topik lanjutan.</li>
</ul>

<h2>Langkah Selanjutnya</h2>
<p>Proyek ini adalah fondasi untuk proyek IoT yang lebih kompleks. Beberapa ide pengembangan:</p>
<ul>
  <li>Tambahkan <strong>auto-refresh</strong> di halaman HTML dengan JavaScript <code>setInterval</code></li>
  <li>Kirim data ke <strong>server cloud</strong> secara berkala (kombinasi dengan artikel <a href="/artikel/menghubungkan-esp32-wifi-kirim-data-server">WiFi + HTTP (#4)</a>)</li>
  <li>Pelajari protokol <strong><a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">MQTT (#7)</a></strong> untuk komunikasi IoT yang lebih efisien</li>
  <li>Pahami kapan pakai REST vs MQTT di <strong><a href="/artikel/rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32">panduan #20</a></strong> sebelum scale ke cloud</li>
  <li>Tambahkan <strong><a href="/artikel/kontrol-lampu-esp32-mqtt-relay">relay (#8)</a></strong> untuk kontrol lampu berdasarkan suhu</li>
  <li>Integrasikan dengan <strong><a href="/artikel/home-assistant-integrasi-esp32-mqtt">Home Assistant (#21)</a></strong> untuk smart home lengkap</li>
  <li>Upgrade dashboard hybrid: <strong><a href="/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22">Web Server + MQTT (#10)</a></strong></li>
  <li>Tambahkan visual: <strong><a href="/artikel/esp32-cam-streaming-mjpeg-capture-foto-wifi">ESP32-CAM MJPEG streaming (#27)</a></strong> — live video di browser, melengkapi angka sensor di web server ini</li>
  <li>Akses aman dari luar LAN → pelajari <a href="/artikel/https-sertifikat-esp32-wificlientsecure-api-rest">HTTPS di ESP32 (#38)</a></li>
</ul>

<blockquote>
  <p><strong>Keamanan:</strong> Web server ini hanya bisa diakses di jaringan lokal. Jangan expose langsung ke internet tanpa autentikasi. Untuk akses remote, gunakan VPN atau tunneling yang aman — atau naik level ke <a href="/artikel/https-sertifikat-esp32-wificlientsecure-api-rest">HTTPS (#38)</a> / <a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">MQTT TLS (#17)</a>.</p>
</blockquote>
HTML;
    }
}
