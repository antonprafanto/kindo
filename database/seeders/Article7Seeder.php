<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article7Seeder extends Seeder
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
            ['slug' => 'memahami-mqtt-esp32-kirim-data-sensor-broker'],
            [
                'user_id'           => $admin->id,
                'category_id'       => $iotCat->id,
                'title'             => 'Memahami MQTT dengan ESP32: Kirim Data Sensor ke Broker IoT',
                'body'              => $this->body(),
                'status'            => 'published',
                'is_featured'       => false,
                'seo_title'         => 'Tutorial MQTT ESP32 — Publish Data Sensor ke Broker IoT',
                'seo_description'   => 'Pelajari protokol MQTT dan cara mengirim data sensor DHT22 dari ESP32 ke broker. Panduan lengkap untuk pemula IoT berbahasa Indonesia.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament; hindari wipe saat re-seed

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        Tag::updateOrCreate(['slug' => 'dht22'], ['name' => 'dht22']);

        $tagSlugs = ['esp32', 'mqtt', 'iot', 'wifi', 'sensor', 'dht22'];
        $tagIds   = Tag::whereIn('slug', $tagSlugs)->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command->info('✓ Artikel ke-7 berhasil dipublish: ' . $article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan</h2>
<p>Di artikel sebelumnya kita sudah membuat <a href="/artikel/membuat-web-server-esp32-monitoring-sensor-dht22">Web Server ESP32 (#6)</a> untuk monitoring sensor DHT22 lewat browser. Itu bagus untuk akses lokal, tapi di dunia IoT nyata, banyak perangkat perlu mengirim data ke satu sistem pusat — tanpa saling tahu IP masing-masing.</p>

<p>Di sinilah protokol <strong>MQTT</strong> (Message Queuing Telemetry Transport) berperan. MQTT adalah standar de facto untuk komunikasi IoT: ringan, cepat, dan cocok untuk ESP32 yang mengirim data sensor secara berkala.</p>

<p>Dalam tutorial ini, kamu akan belajar konsep dasar MQTT dan membuat ESP32 mem-publish data suhu &amp; kelembaban <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">DHT22 (#5)</a> ke broker MQTT publik.</p>

<h2>Apa itu MQTT?</h2>
<p>MQTT menggunakan pola <strong>publish/subscribe</strong> (pub/sub):</p>
<ul>
  <li><strong>Publisher</strong> — perangkat yang mengirim data (ESP32 kamu)</li>
  <li><strong>Subscriber</strong> — perangkat/aplikasi yang menerima data (HP, server, <a href="/artikel/home-assistant-integrasi-esp32-mqtt">Home Assistant (#21)</a>)</li>
  <li><strong>Broker</strong> — server perantara yang meneruskan pesan berdasarkan <em>topic</em></li>
  <li><strong>Topic</strong> — "alamat" pesan, misalnya <code>kodingindonesia/esp32/dht22</code></li>
</ul>

<p>ESP32 tidak perlu tahu siapa yang membaca datanya. Cukup publish ke topic tertentu — broker yang mengurus sisanya.</p>

<figure role="img" aria-label="Diagram topologi MQTT publish subscribe: DHT22 ke ESP32 publish ke Mosquitto lalu subscriber MQTT Explorer" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 620 360" style="display:block;max-width:620px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="g7ArrG" markerWidth="10" markerHeight="10" refX="9" refY="5" orient="auto" markerUnits="userSpaceOnUse"><path d="M0,0 L10,5 L0,10 Z" fill="#2E7D32"/></marker>
    <marker id="g7ArrB" markerWidth="10" markerHeight="10" refX="9" refY="5" orient="auto" markerUnits="userSpaceOnUse"><path d="M0,0 L10,5 L0,10 Z" fill="#2979FF"/></marker>
    <marker id="g7ArrO" markerWidth="10" markerHeight="10" refX="9" refY="5" orient="auto" markerUnits="userSpaceOnUse"><path d="M0,0 L10,5 L0,10 Z" fill="#FF7A2F"/></marker>
  </defs>
  <rect x="0" y="0" width="620" height="360" fill="#F5F5F0" rx="6"/>
  <!-- DHT22 -->
  <rect x="30" y="40" width="150" height="55" rx="6" fill="#C8E6C9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="105" y="62" text-anchor="middle" fill="#1a1a1a" font-size="13" font-weight="700">DHT22 · GPIO 4</text>
  <text x="105" y="80" text-anchor="middle" fill="#4A5568" font-size="10">suhu · kelembaban</text>
  <!-- ESP32 -->
  <rect x="230" y="40" width="160" height="55" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="310" y="62" text-anchor="middle" fill="#1a1a1a" font-size="13" font-weight="700">ESP32 publish</text>
  <text x="310" y="80" text-anchor="middle" fill="#4A5568" font-size="10">PubSubClient</text>
  <line x1="180" y1="67" x2="228" y2="67" stroke="#2E7D32" stroke-width="2.5" marker-end="url(#g7ArrG)"/>
  <!-- Broker -->
  <rect x="170" y="150" width="280" height="60" rx="6" fill="#2979FF" stroke="#000" stroke-width="2.5"/>
  <text x="310" y="175" text-anchor="middle" fill="#fff" font-size="14" font-weight="700">Mosquitto · :1883</text>
  <text x="310" y="193" text-anchor="middle" fill="#e3f2fd" font-size="10">test.mosquitto.org</text>
  <line x1="310" y1="95" x2="310" y2="148" stroke="#2979FF" stroke-width="2.5" marker-end="url(#g7ArrB)"/>
  <rect x="320" y="110" width="70" height="20" rx="10" fill="#E8F4FF" stroke="#2979FF" stroke-width="1.5"/>
  <text x="355" y="124" text-anchor="middle" fill="#2979FF" font-size="9" font-weight="700">PUB</text>
  <!-- Subscribers -->
  <rect x="40" y="260" width="200" height="55" rx="6" fill="#FFF3E8" stroke="#FF7A2F" stroke-width="2.5"/>
  <text x="140" y="282" text-anchor="middle" fill="#1a1a1a" font-size="13" font-weight="700">MQTT Explorer</text>
  <text x="140" y="300" text-anchor="middle" fill="#4A5568" font-size="10">subscribe · laptop</text>
  <rect x="380" y="260" width="200" height="55" rx="6" fill="#FFF3E8" stroke="#FF7A2F" stroke-width="2.5"/>
  <text x="480" y="282" text-anchor="middle" fill="#1a1a1a" font-size="13" font-weight="700">Subscriber lain</text>
  <text x="480" y="300" text-anchor="middle" fill="#4A5568" font-size="10">HA · Python · Node-RED</text>
  <line x1="250" y1="210" x2="160" y2="258" stroke="#FF7A2F" stroke-width="2.5" marker-end="url(#g7ArrO)"/>
  <line x1="370" y1="210" x2="450" y2="258" stroke="#FF7A2F" stroke-width="2.5" marker-end="url(#g7ArrO)"/>
  <rect x="200" y="220" width="70" height="20" rx="10" fill="#FFF3E8" stroke="#FF7A2F" stroke-width="1.5"/>
  <text x="235" y="234" text-anchor="middle" fill="#C45A11" font-size="9" font-weight="700">SUB</text>
  <text x="310" y="345" text-anchor="middle" fill="#4A5568" font-size="11">topic · kodingindonesia/esp32/dht22</text>
</svg>
<figcaption style="margin-top:.75rem;font-size:.875rem;color:#4A5568;text-align:center">ESP32 publish JSON DHT22 → broker → banyak subscriber. Pola kebalikan <a href="/artikel/kontrol-lampu-esp32-mqtt-relay">kontrol relay (#8)</a> (subscribe perintah).</figcaption>
</figure>

<blockquote>
  <p><strong>Analogi sederhana:</strong> MQTT seperti grup WhatsApp. ESP32 mengirim pesan ke grup (topic), siapa saja yang ada di grup (subscriber) bisa membaca — tanpa perlu chat langsung ke setiap orang.</p>
</blockquote>

<h2>MQTT vs HTTP — Kapan Pakai Apa?</h2>
<table>
  <thead>
    <tr><th>Aspek</th><th>HTTP</th><th>MQTT</th></tr>
  </thead>
  <tbody>
    <tr><td>Model</td><td>Request–Response</td><td>Publish–Subscribe</td></tr>
    <tr><td>Ukuran header</td><td>Lebih besar</td><td>Sangat ringan</td></tr>
    <tr><td>Koneksi</td><td>Buka–tutup tiap request</td><td>Persistent (tetap terhubung)</td></tr>
    <tr><td>Cocok untuk</td><td>API web, upload file</td><td>Sensor IoT, telemetry real-time</td></tr>
  </tbody>
</table>
<p>Perbandingan lebih dalam: <a href="/artikel/rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32">REST API vs MQTT (#20)</a>.</p>

<h2>Yang Kamu Butuhkan</h2>
<ul>
  <li>ESP32 DevKit + sensor DHT22 (wiring sama seperti <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">tutorial DHT22 (#5)</a>)</li>
  <li>Arduino IDE dengan board ESP32 terinstall</li>
  <li>Koneksi WiFi</li>
  <li>Aplikasi subscriber MQTT (opsional): <strong>MQTT Explorer</strong> di laptop, atau app <strong>MQTT Client</strong> di HP</li>
</ul>

<blockquote>
  <p><strong>Prasyarat:</strong> Sudah paham koneksi WiFi ESP32 dan cara membaca sensor DHT22. Jika belum, baca <a href="/artikel/menghubungkan-esp32-wifi-kirim-data-server">Menghubungkan ESP32 ke WiFi (#4)</a> dan <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">Membaca Sensor DHT22 (#5)</a>. Disarankan juga sudah membaca <a href="/artikel/membuat-web-server-esp32-monitoring-sensor-dht22">Web Server ESP32 + DHT22 (#6)</a>.</p>
</blockquote>

<h2>Wiring DHT22 Ringkas</h2>
<p>Pin sama dengan <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">artikel DHT22 (#5)</a> — DATA di GPIO 4:</p>

<figure role="img" aria-label="Diagram wiring ESP32 ke DHT22: 3.3V, GND, dan GPIO 4 ke DATA dengan pull-up" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 620 320" style="display:block;max-width:620px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="w7R" markerWidth="9" markerHeight="9" refX="8" refY="4.5" orient="auto" markerUnits="userSpaceOnUse"><path d="M0,0 L9,4.5 L0,9 Z" fill="#C62828"/></marker>
    <marker id="w7K" markerWidth="9" markerHeight="9" refX="8" refY="4.5" orient="auto" markerUnits="userSpaceOnUse"><path d="M0,0 L9,4.5 L0,9 Z" fill="#1a1a1a"/></marker>
    <marker id="w7O" markerWidth="9" markerHeight="9" refX="8" refY="4.5" orient="auto" markerUnits="userSpaceOnUse"><path d="M0,0 L9,4.5 L0,9 Z" fill="#FF7A2F"/></marker>
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
  <polyline fill="none" points="190,110 300,110 300,145 410,145" stroke="#C62828" stroke-width="2.5" marker-end="url(#w7R)"/>
  <polyline fill="none" points="190,155 320,155 320,180 410,180" stroke="#1a1a1a" stroke-width="2.5" marker-end="url(#w7K)"/>
  <polyline fill="none" points="190,200 340,200 340,215 410,215" stroke="#FF7A2F" stroke-width="2.5" marker-end="url(#w7O)"/>
  <text x="30" y="280" fill="#4A5568" font-size="10">3.3V→VCC · GND→GND · GPIO4→DATA · pull-up 10kΩ DATA→3.3V</text>
  <text x="30" y="300" fill="#4A5568" font-size="10">Modul breakout biasanya sudah punya pull-up internal</text>
</svg>
<figcaption style="margin-top:.75rem;font-size:.875rem;color:#4A5568;text-align:center">Wiring pin-ke-pin DHT22 — konsisten dengan <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">DHT22 (#5)</a> dan <a href="/artikel/gabungkan-dht22-relay-mqtt-esp32-satu-proyek">proyek gabungan (#9)</a>.</figcaption>
</figure>

<h2>Install Library</h2>
<p>Install library berikut lewat Arduino IDE → <strong>Sketch → Include Library → Manage Libraries</strong>:</p>
<ol>
  <li><strong>PubSubClient</strong> — oleh Nick O'Leary (client MQTT untuk Arduino/ESP32)</li>
  <li><strong>DHT sensor library</strong> — oleh Adafruit (+ Adafruit Unified Sensor)</li>
</ol>

<h2>Broker MQTT untuk Latihan — Eclipse Mosquitto</h2>
<p>Untuk tutorial ini kita memakai <strong>test server resmi</strong> dari proyek <a href="https://mosquitto.org/" target="_blank" rel="noopener">Eclipse Mosquitto</a> — broker MQTT open source yang populer di dunia IoT:</p>
<ul>
  <li><strong>Host:</strong> <code>test.mosquitto.org</code></li>
  <li><strong>Port:</strong> <code>1883</code> (MQTT plain, tanpa TLS)</li>
  <li><strong>Autentikasi:</strong> tidak perlu username/password</li>
</ul>

<blockquote>
  <p><strong>Penting — broker bukan website:</strong> <code>test.mosquitto.org</code> adalah server MQTT, bukan halaman web. Jika kamu ketik alamat itu di browser Chrome/Firefox, akan muncul error — itu <em>normal</em>. MQTT berjalan di port 1883, bukan port 80/443. ESP32 dan MQTT Explorer yang terhubung ke broker, bukan browser biasa.</p>
</blockquote>

<blockquote>
  <p><strong>Keamanan:</strong> Broker publik hanya untuk belajar dan uji coba. Jangan kirim data sensitif. Untuk production, pasang <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">broker Mosquitto pribadi (#16)</a> di Raspberry Pi/VPS dengan autentikasi, lalu amankan dengan <a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">MQTT over TLS (#17)</a>.</p>
</blockquote>

<h2>Kode Program: ESP32 + DHT22 + MQTT</h2>
<p>Ganti placeholder <code>GANTI_SSID_WIFI</code> / <code>GANTI_PASSWORD_WIFI</code>, lalu upload. Untuk produksi tanpa hardcode, lihat <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">NVS + WiFiManager (#12)</a>.</p>

<pre><code class="language-arduino">#include &lt;WiFi.h&gt;
#include &lt;PubSubClient.h&gt;
#include &lt;DHT.h&gt;

// ── WiFi ──────────────────────────────────────────────
const char* ssid     = "GANTI_SSID_WIFI";
const char* password = "GANTI_PASSWORD_WIFI";

// ── MQTT Broker ───────────────────────────────────────
const char* mqttServer = "test.mosquitto.org";
const int   mqttPort   = 1883;
const char* mqttTopic  = "kodingindonesia/esp32/dht22";

// ── DHT22 ───────────────────────────────────────────
#define DHT_PIN  4
#define DHT_TYPE DHT22
DHT dht(DHT_PIN, DHT_TYPE);

WiFiClient espClient;
PubSubClient mqttClient(espClient);

unsigned long waktuKirimTerakhir = 0;
const unsigned long intervalKirim = 5000; // kirim setiap 5 detik

void koneksiWiFi() {
  Serial.print("Menghubungkan ke WiFi");
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi terhubung!");
  Serial.print("IP: ");
  Serial.println(WiFi.localIP());
}

void koneksiMQTT() {
  mqttClient.setServer(mqttServer, mqttPort);

  while (!mqttClient.connected()) {
    Serial.print("Menghubungkan ke MQTT broker...");
    // Client ID unik agar tidak bentrok dengan perangkat lain
    String clientId = "ESP32-Kindo-" + String(random(0xffff), HEX);

    if (mqttClient.connect(clientId.c_str())) {
      Serial.println(" terhubung!");
    } else {
      Serial.print(" gagal, rc=");
      Serial.print(mqttClient.state());
      Serial.println(" — coba lagi dalam 5 detik");
      delay(5000);
    }
  }
}

void kirimDataSensor() {
  float suhu       = dht.readTemperature();
  float kelembaban = dht.readHumidity();

  if (isnan(suhu) || isnan(kelembaban)) {
    Serial.println("Gagal membaca DHT22, lewati pengiriman.");
    return;
  }

  // Payload JSON
  String payload = "{";
  payload += "\"suhu\":" + String(suhu, 2) + ",";
  payload += "\"kelembaban\":" + String(kelembaban, 2) + ",";
  payload += "\"device\":\"ESP32-001\"";
  payload += "}";

  if (mqttClient.publish(mqttTopic, payload.c_str())) {
    Serial.println("Data terkirim: " + payload);
  } else {
    Serial.println("Gagal publish ke MQTT!");
  }
}

void setup() {
  Serial.begin(115200);
  randomSeed(micros());
  dht.begin();
  koneksiWiFi();
  koneksiMQTT();
}

void loop() {
  // Jaga koneksi MQTT tetap hidup
  if (!mqttClient.connected()) {
    koneksiMQTT();
  }
  mqttClient.loop();

  // Kirim data sensor secara berkala
  if (millis() - waktuKirimTerakhir &gt;= intervalKirim) {
    waktuKirimTerakhir = millis();
    kirimDataSensor();
  }
}</code></pre>

<h2>Uji Coba: Subscribe ke Topic yang Sama</h2>
<p>Setelah ESP32 berjalan, buka Serial Monitor (115200 baud). Kamu harus melihat log <code>Data terkirim: {...}</code> setiap 5 detik.</p>

<p>Sekarang subscribe ke topic yang sama untuk melihat data masuk:</p>

<h3>Opsi 1 — MQTT Explorer (Laptop, disarankan)</h3>
<ol>
  <li>Download <strong><a href="https://mqtt-explorer.com/" target="_blank" rel="noopener">MQTT Explorer</a></strong></li>
  <li>Buat koneksi baru: Host <code>test.mosquitto.org</code>, Port <code>1883</code></li>
  <li>Connect → cari topic <code>kodingindonesia/esp32/dht22</code></li>
  <li>Kamu akan melihat JSON suhu &amp; kelembaban update setiap 5 detik</li>
</ol>

<h3>Opsi 2 — mosquitto_sub (Terminal)</h3>

<pre><code class="language-bash">mosquitto_sub -h test.mosquitto.org -t "kodingindonesia/esp32/dht22" -v</code></pre>

<p><em>Perintah <code>mosquitto_sub</code> adalah tool CLI dari paket <a href="https://mosquitto.org/download/" target="_blank" rel="noopener">Mosquitto</a> — tersedia di Linux, Mac, dan Windows setelah install Mosquitto.</em></p>

<h2>Memahami Struktur Topic</h2>
<p>Topic MQTT menggunakan hierarki seperti folder:</p>

<pre><code>kodingindonesia/esp32/dht22
└── organisasi / perangkat / jenis-data</code></pre>

<p>Best practice penamaan topic:</p>
<ul>
  <li>Gunakan huruf kecil dan slash <code>/</code> sebagai pemisah</li>
  <li>Jangan mulai dengan <code>$</code> — reserved untuk sistem broker</li>
  <li>Gunakan wildcard saat subscribe: <code>kodingindonesia/#</code> (semua sub-topic)</li>
</ul>

<p>Di <a href="/artikel/gabungkan-dht22-relay-mqtt-esp32-satu-proyek">proyek gabungan (#9)</a> topic sensor menjadi <code>.../dht22/data</code> (JSON) agar lebih terstruktur — pola yang sama, subtopic lebih rapi.</p>

<h2>QoS (Quality of Service)</h2>
<p>MQTT punya 3 level QoS yang menentukan jaminan pengiriman:</p>
<ul>
  <li><strong>QoS 0</strong> — kirim sekali, tanpa konfirmasi (paling ringan, default PubSubClient)</li>
  <li><strong>QoS 1</strong> — minimal sekali sampai (ada ACK)</li>
  <li><strong>QoS 2</strong> — tepat sekali sampai (paling andal, paling berat)</li>
</ul>
<p>Untuk data sensor suhu yang dikirim tiap 5 detik, <strong>QoS 0</strong> sudah cukup — jika satu paket hilang, paket berikutnya segera menyusul. Detail LWT &amp; retained: <a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">MQTT TLS / QoS / LWT (#17)</a>.</p>

<h2>Tips &amp; Troubleshooting</h2>
<ul>
  <li><strong>rc=-2 saat connect:</strong> Broker tidak terjangkau. Cek koneksi internet WiFi.</li>
  <li><strong>rc=4 (bad credentials):</strong> Broker butuh username/password — <code>test.mosquitto.org</code> port 1883 tidak perlu auth.</li>
  <li><strong>Broker tidak bisa dibuka di browser:</strong> Normal. Pakai MQTT Explorer atau ESP32, bukan Chrome.</li>
  <li><strong>Data tidak muncul di subscriber:</strong> Pastikan topic sama persis, case-sensitive.</li>
  <li><strong>ESP32 reconnect terus:</strong> Client ID bentrok — kode di atas sudah pakai ID random.</li>
  <li><strong>Nilai suhu NaN:</strong> Cek wiring DHT22 dan resistor pull-up 10kΩ.</li>
  <li><strong>PubSubClient buffer kecil:</strong> Jika payload panjang, tambahkan <code>mqttClient.setBufferSize(512);</code> di <code>setup()</code>.</li>
</ul>

<h2>Gabungkan dengan Web Server (Artikel Sebelumnya)</h2>
<p>MQTT dan <a href="/artikel/membuat-web-server-esp32-monitoring-sensor-dht22">Web Server (#6)</a> bisa jalan bersamaan di ESP32:</p>
<ul>
  <li><strong>Web Server</strong> → monitoring lokal via browser di rumah</li>
  <li><strong>MQTT</strong> → kirim data ke cloud/dashboard/<a href="/artikel/home-assistant-integrasi-esp32-mqtt">Home Assistant (#21)</a></li>
</ul>
<p>Keduanya <strong>komplementer</strong> — bukan pengganti satu sama lain. Capstone dual-channel: <a href="/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22">Dashboard Web + MQTT (#10)</a>.</p>

<h2>Langkah Selanjutnya</h2>
<ul>
  <li>Kontrol aktuator: <a href="/artikel/kontrol-lampu-esp32-mqtt-relay">relay lampu via MQTT (#8)</a> — subscribe ON/OFF</li>
  <li>Gabungkan publish DHT22 + subscribe relay: <a href="/artikel/gabungkan-dht22-relay-mqtt-esp32-satu-proyek">satu sketch (#9)</a></li>
  <li>Subscribe MQTT di <a href="/artikel/home-assistant-integrasi-esp32-mqtt">Home Assistant (#21)</a> untuk smart home dashboard</li>
  <li>Setup broker <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Mosquitto pribadi (#16)</a> di Raspberry Pi atau VPS</li>
  <li>Simpan data ke database via <a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">subscriber Python → MySQL (#18)</a> — setelah payload punya <a href="/artikel/ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt">timestamp NTP (#34)</a></li>
  <li>Visualisasikan histori di <a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">InfluxDB + Grafana (#19)</a> — grafik time-series interaktif</li>
  <li>Bandingkan REST vs MQTT: <a href="/artikel/rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32">kapan pakai yang mana (#20)</a></li>
  <li>Hop wireless tanpa router: <a href="/artikel/esp-now-kirim-data-antar-esp32-tanpa-router-wifi">ESP-NOW antar ESP32 (#25)</a> — sensor ke gateway tanpa AP WiFi</li>
  <li>Sensor sangat jauh: <a href="/artikel/lora-esp32-modul-sx1278-kirim-data-jarak-jauh">LoRa SX1278 (#26)</a> — kilometer tanpa WiFi di titik sensor</li>
  <li>Pelajari <a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">MQTT over TLS (#17)</a> (port 8883) untuk koneksi aman</li>
</ul>

<blockquote>
  <p><strong>Pro tip:</strong> Ubah topic menjadi unik untuk kamu, misalnya <code>kodingindonesia/anton/esp32/dht22</code>, agar tidak bentrok dengan peserta tutorial lain yang memakai topic yang sama di broker publik.</p>
</blockquote>
HTML;
    }
}
