<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article11Seeder extends Seeder
{
    public function run(): void
    {
        $admin  = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first();

        if (! $admin || ! $iotCat) {
            throw new \RuntimeException('User atau kategori tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'deep-sleep-esp32-sensor-dht22-hemat-baterai';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $iotCat->id,
                'title'           => 'Deep Sleep ESP32: Node Sensor DHT22 Hemat Baterai',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => true,
                'seo_title'       => 'Deep Sleep ESP32 + DHT22 MQTT — Node Sensor Hemat Baterai',
                'seo_description' => 'Bangun node sensor ESP32 bertenaga baterai: bangun dari deep sleep, baca DHT22, publish MQTT, lalu tidur lagi. Tutorial IoT berbahasa Indonesia.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        Tag::updateOrCreate(['slug' => 'dht22'], ['name' => 'dht22']);
        Tag::updateOrCreate(['slug' => 'deep-sleep'], ['name' => 'deep-sleep']);

        $tagSlugs = ['esp32', 'dht22', 'sensor', 'iot', 'wifi', 'mqtt', 'deep-sleep'];
        $tagIds   = Tag::whereIn('slug', $tagSlugs)->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command->info('✓ Artikel ke-11 berhasil dipublish: ' . $article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — Seri 2 ESP32/IoT</h2>
<p>Selamat datang di <strong>artikel pembuka Seri 2</strong> Koding Indonesia! Ini adalah lanjutan langsung dari janji <strong>deep sleep</strong> di <a href="/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22">roadmap artikel #10</a>. Di capstone Seri 1 kamu sudah membangun dashboard hybrid Web + MQTT yang terus menyala. Itu cocok untuk proyek bertenaga adaptor USB di meja kerja.</p>

<p>Tapi bagaimana jika sensor harus dipasang di <strong>kebun, gudang, atau sudut rumah tanpa stop kontak</strong>? Node yang terus hidup akan menghabiskan baterai dalam hitungan jam. Solusinya: <strong>deep sleep</strong> — ESP32 tidur nyaris total, bangun sebentar untuk kirim data, lalu tidur lagi.</p>

<p>Dalam tutorial ini kita bangun <strong>node sensor DHT22 hemat baterai</strong> yang setiap beberapa menit:</p>
<ol>
  <li>Bangun dari deep sleep</li>
  <li>Hubungkan WiFi</li>
  <li>Baca suhu &amp; kelembaban</li>
  <li>Publish JSON ke MQTT (topic sama seperti Seri 1)</li>
  <li>Matikan radio WiFi dan tidur lagi</li>
</ol>

<blockquote>
  <p><strong>Prasyarat:</strong> Sudah paham <a href="/artikel/menghubungkan-esp32-wifi-kirim-data-server">WiFi ESP32 (#4)</a>, <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">sensor DHT22 (#5)</a>, dan <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">publish MQTT (#7)</a>. Familiar dengan JSON MQTT dari <a href="/artikel/gabungkan-dht22-relay-mqtt-esp32-satu-proyek">proyek gabungan (#9)</a> atau <a href="/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22">dashboard capstone (#10)</a> akan membantu.</p>
</blockquote>

<h2>Light Sleep vs Deep Sleep</h2>
<table>
  <thead>
    <tr><th>Mode</th><th>CPU &amp; WiFi</th><th>Konsumsi arus</th><th>Cocok untuk</th></tr>
  </thead>
  <tbody>
    <tr><td><strong>Light sleep</strong></td><td>CPU pause, RAM tetap</td><td>Lebih tinggi dari deep sleep</td><td>Bangun cepat, koneksi WiFi tetap hidup</td></tr>
    <tr><td><strong>Deep sleep</strong></td><td>Hampir mati total; hanya RTC</td><td>~10 µA (ideal) pada chip</td><td>Node baterai kirim data periodik</td></tr>
  </tbody>
</table>

<p>Pada deep sleep, ESP32 <strong>reset penuh</strong> saat bangun — program dimulai lagi dari <code>setup()</code>. Itu normal dan justru disederhanakan: tidak perlu khawatir state lama di RAM.</p>

<p><strong>Wake-up timer vs GPIO:</strong> Tutorial ini memakai <code>esp_sleep_enable_timer_wakeup()</code> (bangun tiap N detik). Alternatif lanjutan: bangun dari pin eksternal (mis. tombol atau sensor) dengan <code>esp_sleep_enable_ext0_wakeup()</code> — berguna jika event jarang terjadi.</p>

<h2>Install Library</h2>
<p>Install lewat Arduino IDE → <strong>Sketch → Include Library → Manage Libraries</strong>:</p>
<ol>
  <li><strong>DHT sensor library</strong> — Adafruit (+ <strong>Adafruit Unified Sensor</strong>)</li>
  <li><strong>PubSubClient</strong> — Nick O'Leary</li>
</ol>
<p>Board ESP32 via Board Manager sudah menyertakan <code>esp_sleep.h</code> — tidak perlu library tambahan untuk deep sleep.</p>

<h2>Yang Kamu Butuhkan</h2>
<ul>
  <li>ESP32 DevKit + sensor DHT22 (GPIO <strong>4</strong>, pull-up <strong>10kΩ</strong> ke 3.3V)</li>
  <li>Kabel jumper &amp; breadboard</li>
  <li>Power: USB untuk uji awal; opsional <strong>LiPo 3.7V + modul TP4056</strong> untuk simulasi baterai</li>
  <li>Library: <strong>DHT sensor library</strong>, <strong>PubSubClient</strong></li>
  <li>WiFi 2.4 GHz + <a href="https://mqtt-explorer.com/" target="_blank" rel="noopener">MQTT Explorer</a> (subscribe topic)</li>
</ul>

<blockquote>
  <p><strong>Catatan daya:</strong> Saat WiFi connect, arus bisa melonjak &gt;100 mA meski sebentar. Itulah mengapa kita matikan WiFi sebelum tidur — bukan hanya deep sleep chip, tapi juga <strong>radio WiFi</strong> yang harus off.</p>
</blockquote>

<h2>Wiring DHT22</h2>
<p>Sama seperti tutorial <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">DHT22 (#5)</a> Seri 1 — jangan ubah pin tanpa alasan:</p>
<figure role="img" aria-label="Diagram wiring ESP32 ke DHT22: 3.3V ke VCC, GND ke GND, GPIO 4 ke DATA dengan pull-up 10k ohm" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 620 320" style="display:block;max-width:620px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="dht11R" markerWidth="7" markerHeight="7" refX="6" refY="3.5" orient="auto"><path d="M0,0 L7,3.5 L0,7 Z" fill="#C62828"/></marker>
    <marker id="dht11K" markerWidth="7" markerHeight="7" refX="6" refY="3.5" orient="auto"><path d="M0,0 L7,3.5 L0,7 Z" fill="#1a1a1a"/></marker>
    <marker id="dht11O" markerWidth="7" markerHeight="7" refX="6" refY="3.5" orient="auto"><path d="M0,0 L7,3.5 L0,7 Z" fill="#FF7A2F"/></marker>
  </defs>
  <rect x="0" y="0" width="620" height="320" fill="#F5F5F0" rx="6"/>
  <rect x="30" y="35" width="170" height="210" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="115" y="68" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">ESP32 DevKit</text>
  <circle cx="185" cy="110" r="5" fill="#C62828"/>
  <text x="170" y="115" text-anchor="end" fill="#1a1a1a" font-size="12" font-weight="600">3.3V</text>
  <circle cx="185" cy="160" r="5" fill="#1a1a1a"/>
  <text x="170" y="165" text-anchor="end" fill="#1a1a1a" font-size="12" font-weight="600">GND</text>
  <circle cx="185" cy="210" r="5" fill="#FF7A2F"/>
  <text x="170" y="207" text-anchor="end" fill="#1a1a1a" font-size="12" font-weight="600">GPIO 4</text>
  <text x="170" y="221" text-anchor="end" fill="#4A5568" font-size="9">DATA</text>
  <rect x="400" y="50" width="190" height="180" rx="6" fill="#C8E6C9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="495" y="82" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">DHT22</text>
  <text x="495" y="102" text-anchor="middle" fill="#4A5568" font-size="10">1-wire · 3.3V</text>
  <circle cx="415" cy="135" r="5" fill="#C62828"/>
  <text x="430" y="140" fill="#1a1a1a" font-size="12" font-weight="600">VCC</text>
  <circle cx="415" cy="175" r="5" fill="#1a1a1a"/>
  <text x="430" y="180" fill="#1a1a1a" font-size="12" font-weight="600">GND</text>
  <circle cx="415" cy="215" r="5" fill="#FF7A2F"/>
  <text x="430" y="220" fill="#1a1a1a" font-size="12" font-weight="600">DATA</text>
  <line x1="190" y1="110" x2="410" y2="135" stroke="#C62828" stroke-width="2.5" marker-end="url(#dht11R)"/>
  <line x1="190" y1="160" x2="410" y2="175" stroke="#1a1a1a" stroke-width="2.5" marker-end="url(#dht11K)"/>
  <line x1="190" y1="210" x2="410" y2="215" stroke="#FF7A2F" stroke-width="2.5" marker-end="url(#dht11O)"/>
  <rect x="30" y="270" width="14" height="10" rx="2" fill="#C62828"/>
  <text x="50" y="279" fill="#4A5568" font-size="10">3.3V → VCC</text>
  <rect x="160" y="270" width="14" height="10" rx="2" fill="#1a1a1a"/>
  <text x="180" y="279" fill="#4A5568" font-size="10">GND → GND</text>
  <rect x="290" y="270" width="14" height="10" rx="2" fill="#FF7A2F"/>
  <text x="310" y="279" fill="#4A5568" font-size="10">GPIO 4 → DATA</text>
  <text x="310" y="302" text-anchor="middle" fill="#4A5568" font-size="10">Pull-up 10kΩ DATA→3.3V (modul breakout biasanya sudah ada)</text>
</svg>
<figcaption style="margin-top:.75rem;font-size:.875rem;color:#4A5568;text-align:center">Wiring pin-ke-pin: 3.3V→VCC, GND→GND, GPIO 4→DATA. Modul wajib <strong>3.3V</strong>.</figcaption>
</figure>

<blockquote>
  <p><strong>Pin strapping:</strong> Hindari GPIO yang dipakai saat boot (mis. beberapa board sensitif di GPIO 0, 2, 12, 15). GPIO 4 aman untuk DHT22 di kebanyakan DevKit.</p>
</blockquote>

<h2>Arsitektur Node Sensor</h2>
<figure role="img" aria-label="Diagram siklus deep sleep ESP32: tidur, bangun timer, baca DHT22, WiFi MQTT publish, matikan WiFi, lalu tidur lagi" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 620 430" style="display:block;max-width:620px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="ds11Arr" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/></marker>
    <marker id="ds11ArrO" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#FF7A2F"/></marker>
    <marker id="ds11ArrG" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#2E7D32"/></marker>
  </defs>
  <rect x="0" y="0" width="620" height="430" fill="#F5F5F0" rx="6"/>
  <!-- Sleep -->
  <rect x="150" y="14" width="320" height="56" rx="6" fill="#E8EAF6" stroke="#000" stroke-width="2.5"/>
  <text x="310" y="38" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">Deep sleep · ~10 µA</text>
  <text x="310" y="56" text-anchor="middle" fill="#4A5568" font-size="11">RTC timer · DURASI_TIDUR_DETIK</text>
  <line x1="310" y1="70" x2="310" y2="104" stroke="#2979FF" stroke-width="2.5" marker-end="url(#ds11Arr)"/>
  <rect x="340" y="76" width="150" height="24" rx="12" fill="#E8F4FF" stroke="#2979FF" stroke-width="1.5"/>
  <text x="415" y="92" text-anchor="middle" fill="#2979FF" font-size="10" font-weight="700">timer wake-up</text>
  <!-- Wake -->
  <rect x="150" y="112" width="320" height="56" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="310" y="136" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">Bangun · setup() dari awal</text>
  <text x="310" y="154" text-anchor="middle" fill="#4A5568" font-size="11">reset penuh · setCpuFrequencyMhz(80)</text>
  <line x1="310" y1="168" x2="310" y2="202" stroke="#FF7A2F" stroke-width="2.5" marker-end="url(#ds11ArrO)"/>
  <!-- DHT -->
  <rect x="150" y="210" width="320" height="50" rx="6" fill="#C8E6C9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="310" y="232" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">Baca DHT22 (GPIO 4)</text>
  <text x="310" y="248" text-anchor="middle" fill="#4A5568" font-size="10">dht.begin() + delay(2000)</text>
  <line x1="310" y1="260" x2="310" y2="292" stroke="#FF7A2F" stroke-width="2.5" marker-end="url(#ds11ArrO)"/>
  <!-- MQTT -->
  <rect x="150" y="300" width="320" height="50" rx="6" fill="#2979FF" stroke="#000" stroke-width="2.5"/>
  <text x="310" y="322" text-anchor="middle" fill="#fff" font-size="14" font-weight="700">WiFi + MQTT publish JSON</text>
  <text x="310" y="338" text-anchor="middle" fill="#e3f2fd" font-size="10">sekali per siklus · lalu WIFI_OFF</text>
  <line x1="310" y1="350" x2="310" y2="378" stroke="#2E7D32" stroke-width="2.5" marker-end="url(#ds11ArrG)"/>
  <rect x="180" y="386" width="260" height="32" rx="6" fill="#FFF8E7" stroke="#000" stroke-width="2"/>
  <text x="310" y="407" text-anchor="middle" fill="#1a1a1a" font-size="12" font-weight="700">esp_deep_sleep_start() → ulang</text>
</svg>
<figcaption style="margin-top:.75rem;font-size:.875rem;color:#4A5568;text-align:center">Siklus hemat baterai — lanjut hilangkan hardcode WiFi lewat <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">NVS + WiFiManager (#12)</a>.</figcaption>
</figure>

<table>
  <thead>
    <tr><th>Tahap</th><th>Durasi tipikal</th><th>Catatan</th></tr>
  </thead>
  <tbody>
    <tr><td>Deep sleep</td><td>10 menit (konfigurasi)</td><td>Hanya RTC timer hidup</td></tr>
    <tr><td>Bangun + baca DHT22</td><td>~2–3 detik</td><td><code>delay(2000)</code> setelah <code>dht.begin()</code></td></tr>
    <tr><td>WiFi + MQTT publish</td><td>~3–10 detik</td><td>Sekali publish per siklus</td></tr>
    <tr><td>Matikan WiFi → deep sleep</td><td>&lt;1 detik</td><td><code>WiFi.mode(WIFI_OFF)</code></td></tr>
  </tbody>
</table>

<p><strong>Broker latihan:</strong> <code>test.mosquitto.org:1883</code> (sama Seri 1).  
<strong>Topic:</strong> <code>kodingindonesia/esp32/dht22/data</code> — payload JSON <code>{"suhu":28.5,"kelembaban":65.2}</code>.</p>

<blockquote>
  <p><strong>Broker bukan website:</strong> <code>test.mosquitto.org</code> tidak dibuka di browser. Pakai MQTT Explorer atau <code>mosquitto_sub</code>. Detail di <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">artikel MQTT (#7)</a>.</p>
</blockquote>

<blockquote>
  <p><strong>Pro tip:</strong> Ganti segmen topic dengan nama unik, misalnya <code>kodingindonesia/anton/esp32/dht22/data</code>, agar tidak bentrok dengan peserta tutorial lain di broker publik.</p>
</blockquote>

<h2>Kode Lengkap: Deep Sleep + DHT22 + MQTT</h2>
<p>Ganti placeholder <code>GANTI_SSID_WIFI</code> / <code>GANTI_PASSWORD_WIFI</code>, lalu upload. Setelah upload, buka Serial Monitor 115200 — kamu akan melihat satu siklus lalu ESP32 tidur (Serial berhenti sampai bangun lagi). Untuk produksi tanpa hardcode, lanjut <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">NVS + WiFiManager (#12)</a>.</p>

<pre><code class="language-arduino">#include &lt;WiFi.h&gt;
#include &lt;PubSubClient.h&gt;
#include &lt;DHT.h&gt;
#include "esp_sleep.h"

// Durasi tidur (detik) — ubah sesuai kebutuhan
const uint64_t DURASI_TIDUR_DETIK = 600; // 10 menit

const char* ssid     = "GANTI_SSID_WIFI";
const char* password = "GANTI_PASSWORD_WIFI";

const char* mqttServer  = "test.mosquitto.org";
const int   mqttPort    = 1883;
const char* topicSensor = "kodingindonesia/esp32/dht22/data";

#define DHT_PIN  4
#define DHT_TYPE DHT22

DHT dht(DHT_PIN, DHT_TYPE);
WiFiClient espClient;
PubSubClient mqttClient(espClient);

void cetakAlasanBangun() {
  esp_sleep_wakeup_cause_t cause = esp_sleep_get_wakeup_cause();
  switch (cause) {
    case ESP_SLEEP_WAKEUP_TIMER:
      Serial.println("Bangun: timer deep sleep");
      break;
    case ESP_SLEEP_WAKEUP_UNDEFINED:
    default:
      Serial.println("Bangun: boot / reset (upload atau tombol EN)");
      break;
  }
}

void matikanWiFi() {
  mqttClient.disconnect();
  WiFi.disconnect(true);
  WiFi.mode(WIFI_OFF);
}

bool koneksiWiFi() {
  Serial.print("WiFi");
  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);

  unsigned long mulai = millis();
  while (WiFi.status() != WL_CONNECTED &amp;&amp; millis() - mulai &lt; 15000) {
    delay(500);
    Serial.print(".");
  }

  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("\nWiFi gagal (timeout 15 detik)");
    return false;
  }

  Serial.println("\nWiFi terhubung");
  return true;
}

bool koneksiMQTT() {
  mqttClient.setServer(mqttServer, mqttPort);
  mqttClient.setBufferSize(512);

  String clientId = "ESP32-Sleep-" + String(random(0xffff), HEX);
  if (mqttClient.connect(clientId.c_str())) {
    Serial.println("MQTT terhubung");
    return true;
  }

  Serial.print("MQTT gagal, rc=");
  Serial.println(mqttClient.state());
  return false;
}

bool publishSensor() {
  float kelembaban = dht.readHumidity();
  float suhu       = dht.readTemperature();

  if (isnan(kelembaban) || isnan(suhu)) {
    Serial.println("DHT22 gagal (NaN) — lewati publish");
    return false;
  }

  String payload = "{\"suhu\":" + String(suhu, 1);
  payload += ",\"kelembaban\":" + String(kelembaban, 1) + "}";

  mqttClient.loop();
  if (mqttClient.publish(topicSensor, payload.c_str())) {
    Serial.print("Publish ");
    Serial.print(topicSensor);
    Serial.print(": ");
    Serial.println(payload);
    return true;
  }

  Serial.println("Publish gagal");
  return false;
}

void masukDeepSleep() {
  Serial.print("Deep sleep ");
  Serial.print((unsigned long)DURASI_TIDUR_DETIK);
  Serial.println(" detik...");
  Serial.flush();

  esp_sleep_enable_timer_wakeup(DURASI_TIDUR_DETIK * 1000000ULL);
  esp_deep_sleep_start();
  // tidak pernah sampai di sini
}

void setup() {
  Serial.begin(115200);
  delay(100);

  cetakAlasanBangun();

  // Hemat daya saat aktif (opsional, trade-off kecepatan)
  setCpuFrequencyMhz(80);

  randomSeed(micros());
  dht.begin();
  delay(2000); // stabilkan DHT22 setelah bangun

  if (!koneksiWiFi()) {
    matikanWiFi();
    masukDeepSleep();
    return;
  }

  if (!koneksiMQTT()) {
    matikanWiFi();
    masukDeepSleep();
    return;
  }

  publishSensor();
  matikanWiFi();
  masukDeepSleep();
}

void loop() {
  // Deep sleep: loop tidak dipakai — semua logika di setup() tiap bangun
}
</code></pre>

<h2>Alur Program (Tiap Siklus Bangun)</h2>
<ol>
  <li><code>setup()</code> jalan dari awal (reset setelah deep sleep)</li>
  <li>Cetak alasan bangun (timer atau boot pertama)</li>
  <li>Inisialisasi DHT22 + jeda 2 detik</li>
  <li>Connect WiFi (timeout 15 detik)</li>
  <li>Connect MQTT + <code>setBufferSize(512)</code></li>
  <li>Publish satu payload JSON</li>
  <li>Disconnect MQTT &amp; matikan WiFi</li>
  <li><code>esp_deep_sleep_start()</code> — tidur <code>DURASI_TIDUR_DETIK</code></li>
</ol>

<h2>Uji Coba</h2>
<ol>
  <li>Upload sketch, buka <strong>Serial Monitor 115200</strong></li>
  <li>Catat log: WiFi OK → MQTT → publish JSON → "Deep sleep 600 detik..."</li>
  <li>Serial berhenti — ESP32 tidur (normal)</li>
  <li>Di MQTT Explorer, connect ke <code>test.mosquitto.org</code>, subscribe <code>kodingindonesia/esp32/dht22/data</code></li>
  <li>Tunggu ~10 menit (atau ubah <code>DURASI_TIDUR_DETIK</code> jadi <code>60</code> untuk uji cepat) — pesan baru muncul periodik</li>
</ol>

<pre><code class="language-bash">mosquitto_sub -h test.mosquitto.org -t "kodingindonesia/esp32/dht22/data" -v</code></pre>

<blockquote>
  <p><strong>Uji cepat:</strong> Set <code>DURASI_TIDUR_DETIK = 60</code> saat development, kembalikan ke 600 (10 menit) untuk deploy baterai.</p>
</blockquote>

<h2>Optimasi Hemat Daya</h2>
<ul>
  <li><strong><code>setCpuFrequencyMhz(80)</code></strong> — kurangi konsumsi saat aktif (boleh 160 jika connect WiFi sering gagal)</li>
  <li><strong><code>WiFi.mode(WIFI_OFF)</code></strong> — wajib sebelum sleep; jangan biarkan radio idle</li>
  <li><strong>Satu publish per bangun</strong> — hindari loop panjang di <code>loop()</code></li>
  <li><strong>Timeout WiFi</strong> — jangan infinite loop; gagal → tidur lagi agar baterai tidak habis di jaringan WiFi rumah yang lemah</li>
  <li><strong>DHT22</strong> — selalu <code>delay(2000)</code> setelah <code>begin()</code> pasca deep sleep</li>
  <li><strong>Multimeter</strong> — ukur arus di jalur 3.3V saat bangun vs saat tidur untuk validasi optimasi (harapkan lonjakan singkat saat WiFi connect)</li>
</ul>

<h2>Tips &amp; Troubleshooting</h2>
<ul>
  <li><strong>DHT22 NaN setelah bangun:</strong> Perpanjang delay; cek wiring &amp; pull-up; hindari kabel panjang tanpa shield</li>
  <li><strong>Boot loop tanpa tidur:</strong> Pastikan tidak ada kode setelah <code>esp_deep_sleep_start()</code> yang mengharapkan lanjut; cek brownout jika pakai baterai lemah (gunakan supply stabil)</li>
  <li><strong>Tidak ada pesan MQTT:</strong> Topic case-sensitive; cek <code>setBufferSize(512)</code>; lihat <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">artikel MQTT (#7)</a></li>
  <li><strong>Baterai cepat habis:</strong> Kurangi frekuensi bangun; pastikan WiFi benar-benar off; ukur arus dengan multimeter untuk baseline</li>
  <li><strong>Upload gagal setelah sleep:</strong> Tekan tombol <strong>BOOT</strong> saat upload, atau colok USB lalu reset sebelum ESP32 sempat tidur</li>
</ul>

<h2>Langkah Selanjutnya (Seri 2)</h2>
<ul>
  <li><strong><a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">NVS + WiFiManager ESP32 (#12)</a></strong> — simpan kredensial WiFi di flash, tanpa hardcode <code>ssid</code>/<code>password</code> di kode produksi</li>
  <li><strong><a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Broker Mosquitto pribadi (#16)</a></strong> — pindah dari <code>test.mosquitto.org</code> dengan autentikasi</li>
  <li><strong><a href="/artikel/i2c-esp32-sensor-bme280-suhu-tekanan-mqtt">Sensor BME280 via I2C (#13)</a></strong> — upgrade akurasi + tekanan udara untuk node lapangan</li>
  <li><strong><a href="/artikel/oled-ssd1306-esp32-tampilkan-data-sensor-i2c">OLED SSD1306 (#14)</a></strong> — tampilkan suhu &amp; tekanan di layar lokal</li>
  <li><strong><a href="/artikel/ota-update-firmware-esp32-via-wifi">OTA update firmware (#15)</a></strong> — maintain node lapangan tanpa kabel USB (setelah <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">WiFiManager (#12)</a>)</li>
  <li><strong><a href="/artikel/ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt">NTP &amp; timestamp (#34)</a></strong> — log waktu akurat tiap publish dari node deep sleep</li>
  <li><strong><a href="/artikel/esp-now-kirim-data-antar-esp32-tanpa-router-wifi">ESP-NOW (#25)</a></strong> — kirim data tanpa WiFi router; ideal untuk node baterai + gateway MQTT</li>
  <li><strong><a href="/artikel/lora-esp32-modul-sx1278-kirim-data-jarak-jauh">LoRa SX1278 (#26)</a></strong> — node baterai di ujung lahan; kirim telemetry jarak jauh</li>
  <li>Kembali ke <a href="/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22">dashboard capstone Seri 1 (#10)</a> untuk membandingkan node always-on vs battery-powered</li>
</ul>

<blockquote>
  <p><strong>Keamanan:</strong> Jangan hardcode password WiFi di repo publik. Untuk proyek lapangan, gunakan <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">NVS + WiFiManager (#12)</a> atau simpan kredensial di file terpisah yang tidak di-commit.</p>
</blockquote>

<p>Ini langkah pertama menuju node IoT yang benar-benar <em>wireless</em> — sensor di mana adaptor listrik tidak tersedia. Lanjutkan Seri 2 di <a href="/artikel">halaman artikel</a> Koding Indonesia.</p>
HTML;
    }
}
