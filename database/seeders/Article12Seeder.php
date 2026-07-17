<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article12Seeder extends Seeder
{
    public function run(): void
    {
        $admin   = User::first();
        $espCat  = Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $espCat) {
            throw new \RuntimeException('User atau kategori tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $espCat->id,
                'title'           => 'NVS Preferences + WiFiManager ESP32: Konfigurasi Tanpa Hardcode',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'NVS + WiFiManager ESP32 — Simpan WiFi Tanpa Hardcode',
                'seo_description' => 'Pelajari NVS Preferences dan WiFiManager di ESP32: portal captive WiFi, simpan SSID & MQTT di flash, tanpa hardcode kredensial di sketch Arduino.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        Tag::updateOrCreate(['slug' => 'wifimanager'], ['name' => 'wifimanager']);
        Tag::updateOrCreate(['slug' => 'nvs'], ['name' => 'nvs']);

        $tagSlugs = ['esp32', 'wifi', 'iot', 'mqtt', 'sensor', 'dht22', 'wifimanager', 'nvs'];
        $tagIds   = Tag::whereIn('slug', $tagSlugs)->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command->info('✓ Artikel ke-12 berhasil dipublish: ' . $article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan</h2>
<p>Di <a href="/artikel/menghubungkan-esp32-wifi-kirim-data-server">artikel WiFi ESP32</a> dan <a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">node deep sleep DHT22</a>, kita masih menulis <code>ssid</code> dan <code>password</code> langsung di sketch. Itu cepat untuk belajar, tapi <strong>tidak layak produksi</strong>: setiap ganti WiFi atau deploy ke pelanggan lain, kamu harus edit kode, compile ulang, dan upload via USB.</p>

<p>Artikel ini mengajarkan dua fondasi firmware ESP32 yang wajib untuk proyek lapangan:</p>
<ol>
  <li><strong>WiFiManager</strong> — portal konfigurasi WiFi lewat hotspot captive (tanpa Serial Monitor)</li>
  <li><strong>NVS Preferences</strong> — simpan pengaturan (topic MQTT, interval, flag) di flash secara persisten</li>
</ol>

<p>Kita gabungkan keduanya dalam satu sketch: baca DHT22, publish MQTT JSON (topic Seri 1), tanpa satu pun kredensial WiFi di source code.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> Paham <a href="/artikel/menghubungkan-esp32-wifi-kirim-data-server">koneksi WiFi</a>, <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">DHT22</a>, dan <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">publish MQTT</a>. Familiar dengan <a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">deep sleep</a> membantu untuk node baterai nanti.</p>
</blockquote>

<h2>Masalah Hardcode WiFi</h2>
<table>
  <thead>
    <tr><th>Skenario</th><th>Tanpa WiFiManager</th><th>Dengan WiFiManager</th></tr>
  </thead>
  <tbody>
    <tr><td>Pindah rumah / kantor</td><td>Edit sketch + upload USB</td><td>Buka portal, pilih WiFi baru</td></tr>
    <tr><td>Deploy ke banyak unit</td><td>Satu firmware per lokasi</td><td>Satu firmware universal</td></tr>
    <tr><td>Password WiFi di GitHub</td><td>Risiko bocor</td><td>Tidak ada password di repo</td></tr>
    <tr><td>Node di atap / kebun</td><td>Harus bawa laptop</td><td>Setup lewat HP saja</td></tr>
  </tbody>
</table>

<p>Di <a href="/artikel/menghubungkan-esp32-wifi-kirim-data-server">artikel WiFi ESP32 (#4)</a> sudah disebutkan: gunakan <strong>WiFiManager</strong> atau file konfigurasi terpisah — ini janji yang kita penuhi di artikel Seri 2. Kali ini kita implementasi lengkapnya.</p>

<h2>NVS (Non-Volatile Storage) &amp; Preferences</h2>
<p>ESP32 punya partisi flash bernama <strong>NVS</strong> untuk menyimpan key-value yang tetap ada setelah reboot atau deep sleep. Di Arduino, library <code>Preferences</code> adalah wrapper resmi:</p>
<ul>
  <li><code>prefs.begin("namespace")</code> — buka namespace (misalnya <code>"kindo"</code>)</li>
  <li><code>prefs.putString("mqtt_topic", ...)</code> — simpan string</li>
  <li><code>prefs.getString("mqtt_topic", default)</code> — baca dengan nilai default</li>
  <li><code>prefs.clear()</code> — hapus semua key di namespace</li>
</ul>

<p><strong>WiFiManager</strong> sendiri sudah menyimpan kredensial WiFi ke NVS internal. Kita pakai Preferences tambahan untuk parameter aplikasi: topic MQTT, durasi deep sleep (opsional), nama perangkat.</p>

<h2>WiFiManager: Alur Portal Captive</h2>
<figure role="img" aria-label="Diagram alur WiFiManager: ESP32 buat AP KindoESP32-Setup, HP isi WiFi dan topic MQTT, simpan NVS, lalu connect WiFi rumah dan publish MQTT" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 620 390" style="display:block;max-width:620px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="wm12Arr" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/></marker>
    <marker id="wm12ArrO" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#FF7A2F"/></marker>
    <marker id="wm12ArrG" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#2E7D32"/></marker>
  </defs>
  <rect x="0" y="0" width="620" height="390" fill="#F5F5F0" rx="6"/>
  <!-- Step 1 -->
  <rect x="140" y="14" width="340" height="58" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="310" y="38" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">ESP32 — belum ada WiFi tersimpan</text>
  <text x="310" y="56" text-anchor="middle" fill="#4A5568" font-size="11">buat AP · KindoESP32-Setup</text>
  <line x1="310" y1="72" x2="310" y2="108" stroke="#2979FF" stroke-width="2.5" marker-end="url(#wm12Arr)"/>
  <!-- Step 2 -->
  <rect x="140" y="116" width="340" height="58" rx="6" fill="#FFF3E8" stroke="#FF7A2F" stroke-width="2.5"/>
  <text x="310" y="140" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">HP / laptop → portal captive</text>
  <text x="310" y="158" text-anchor="middle" fill="#4A5568" font-size="11">SSID rumah · password · MQTT topic</text>
  <line x1="310" y1="174" x2="310" y2="210" stroke="#FF7A2F" stroke-width="2.5" marker-end="url(#wm12ArrO)"/>
  <rect x="340" y="180" width="130" height="24" rx="12" fill="#FFF3E8" stroke="#FF7A2F" stroke-width="1.5"/>
  <text x="405" y="196" text-anchor="middle" fill="#C45A11" font-size="10" font-weight="700">simpan NVS</text>
  <!-- Step 3 -->
  <rect x="140" y="218" width="340" height="58" rx="6" fill="#C8E6C9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="310" y="242" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">Connect WiFi rumah + MQTT</text>
  <text x="310" y="260" text-anchor="middle" fill="#4A5568" font-size="11">boot berikutnya · portal tidak muncul</text>
  <!-- Fan to outcomes -->
  <line x1="200" y1="276" x2="110" y2="318" stroke="#2E7D32" stroke-width="2" marker-end="url(#wm12ArrG)"/>
  <line x1="310" y1="276" x2="310" y2="318" stroke="#2E7D32" stroke-width="2" marker-end="url(#wm12ArrG)"/>
  <line x1="420" y1="276" x2="510" y2="318" stroke="#2E7D32" stroke-width="2" marker-end="url(#wm12ArrG)"/>
  <rect x="15" y="325" width="190" height="42" rx="6" fill="#FFF8E7" stroke="#000" stroke-width="2"/>
  <text x="110" y="343" text-anchor="middle" fill="#1a1a1a" font-size="11" font-weight="700">Preferences NVS</text>
  <text x="110" y="359" text-anchor="middle" fill="#4A5568" font-size="9">mqtt_topic · wifi_ok</text>
  <rect x="215" y="325" width="190" height="42" rx="6" fill="#FFF8E7" stroke="#000" stroke-width="2"/>
  <text x="310" y="343" text-anchor="middle" fill="#1a1a1a" font-size="11" font-weight="700">Publish DHT22</text>
  <text x="310" y="359" text-anchor="middle" fill="#4A5568" font-size="9">JSON suhu · kelembaban</text>
  <rect x="415" y="325" width="190" height="42" rx="6" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2"/>
  <text x="510" y="343" text-anchor="middle" fill="#1a1a1a" font-size="11" font-weight="700">Reset = BOOT GPIO 0</text>
  <text x="510" y="359" text-anchor="middle" fill="#4A5568" font-size="9">wm.resetSettings()</text>
</svg>
<figcaption style="margin-top:.75rem;font-size:.875rem;color:#4A5568;text-align:center">Portal sekali → kredensial di NVS — siap digabung <a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">deep sleep (#11)</a> dan <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">broker auth (#16)</a>.</figcaption>
</figure>

<ol>
  <li>ESP32 tidak menemukan WiFi tersimpan → buat AP <code>KindoESP32-Setup</code></li>
  <li>HP/laptop connect ke AP tersebut → browser terbuka halaman konfigurasi</li>
  <li>Pilih SSID rumah, masukkan password, isi field custom (topic MQTT)</li>
  <li>ESP32 simpan ke flash, reboot, connect ke WiFi rumah</li>
  <li>Boot berikutnya langsung connect — portal tidak muncul lagi</li>
</ol>

<p><strong>Reset konfigurasi:</strong> Tahan tombol <strong>BOOT</strong> (GPIO 0) saat boot, atau panggil <code>wm.resetSettings()</code> + <code>prefs.clear()</code> di kode maintenance.</p>

<h2>Komponen &amp; Wiring</h2>
<p>Sama seperti tutorial <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">DHT22</a> Seri 1 — sensor digital di GPIO 4:</p>
<figure role="img" aria-label="Diagram wiring ESP32 ke DHT22: 3.3V ke VCC, GND ke GND, GPIO 4 ke DATA dengan pull-up 10k ohm" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 620 320" style="display:block;max-width:620px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="dht12R" markerWidth="7" markerHeight="7" refX="6" refY="3.5" orient="auto"><path d="M0,0 L7,3.5 L0,7 Z" fill="#C62828"/></marker>
    <marker id="dht12K" markerWidth="7" markerHeight="7" refX="6" refY="3.5" orient="auto"><path d="M0,0 L7,3.5 L0,7 Z" fill="#1a1a1a"/></marker>
    <marker id="dht12O" markerWidth="7" markerHeight="7" refX="6" refY="3.5" orient="auto"><path d="M0,0 L7,3.5 L0,7 Z" fill="#FF7A2F"/></marker>
  </defs>
  <rect x="0" y="0" width="620" height="320" fill="#F5F5F0" rx="6"/>
  <!-- ESP32 -->
  <rect x="30" y="35" width="170" height="210" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="115" y="68" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">ESP32 DevKit</text>
  <circle cx="185" cy="110" r="5" fill="#C62828"/>
  <text x="170" y="115" text-anchor="end" fill="#1a1a1a" font-size="12" font-weight="600">3.3V</text>
  <circle cx="185" cy="160" r="5" fill="#1a1a1a"/>
  <text x="170" y="165" text-anchor="end" fill="#1a1a1a" font-size="12" font-weight="600">GND</text>
  <circle cx="185" cy="210" r="5" fill="#FF7A2F"/>
  <text x="170" y="207" text-anchor="end" fill="#1a1a1a" font-size="12" font-weight="600">GPIO 4</text>
  <text x="170" y="221" text-anchor="end" fill="#4A5568" font-size="9">DATA</text>
  <!-- DHT22 -->
  <rect x="400" y="50" width="190" height="180" rx="6" fill="#C8E6C9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="495" y="82" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">DHT22</text>
  <text x="495" y="102" text-anchor="middle" fill="#4A5568" font-size="10">1-wire · 3.3V</text>
  <circle cx="415" cy="135" r="5" fill="#C62828"/>
  <text x="430" y="140" fill="#1a1a1a" font-size="12" font-weight="600">VCC</text>
  <circle cx="415" cy="175" r="5" fill="#1a1a1a"/>
  <text x="430" y="180" fill="#1a1a1a" font-size="12" font-weight="600">GND</text>
  <circle cx="415" cy="215" r="5" fill="#FF7A2F"/>
  <text x="430" y="220" fill="#1a1a1a" font-size="12" font-weight="600">DATA</text>
  <!-- Wires -->
  <line x1="190" y1="110" x2="410" y2="135" stroke="#C62828" stroke-width="2.5" marker-end="url(#dht12R)"/>
  <line x1="190" y1="160" x2="410" y2="175" stroke="#1a1a1a" stroke-width="2.5" marker-end="url(#dht12K)"/>
  <line x1="190" y1="210" x2="410" y2="215" stroke="#FF7A2F" stroke-width="2.5" marker-end="url(#dht12O)"/>
  <!-- Legend -->
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

<ul>
  <li>ESP32 DevKit (USB untuk upload pertama)</li>
  <li>Sensor DHT22 + kabel jumper</li>
  <li>HP Android/iOS untuk portal WiFiManager</li>
</ul>

<h2>Install Library</h2>
<p>Di Arduino IDE 2.x → <strong>Sketch → Include Library → Manage Libraries</strong>:</p>
<ul>
  <li><strong>WiFiManager</strong> oleh <em>tzapu</em> (versi 2.x)</li>
  <li><strong>DHT sensor library</strong> oleh <em>Adafruit</em> + dependency <strong>Adafruit Unified Sensor</strong></li>
  <li><strong>PubSubClient</strong> oleh <em>Nick O'Leary</em></li>
</ul>
<p>Board: <strong>esp32</strong> by Espressif (v3.x). Library <code>Preferences</code> dan <code>WiFi</code> sudah built-in.</p>

<p><strong>Broker latihan:</strong> <code>test.mosquitto.org:1883</code> (sama Seri 1).</p>
<p><strong>Topic default:</strong> <code>kodingindonesia/esp32/dht22/data</code> — payload JSON <code>{"suhu":28.5,"kelembaban":65.2}</code> (bisa diubah lewat portal).</p>

<blockquote>
  <p><strong>Broker bukan website:</strong> <code>test.mosquitto.org</code> tidak dibuka di browser. Pakai MQTT Explorer atau <code>mosquitto_sub</code>. Detail di <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">artikel MQTT</a>.</p>
</blockquote>

<h2>Kode Lengkap: WiFiManager + NVS + DHT22 + MQTT</h2>
<p>Tidak ada <code>const char* ssid</code> / <code>password</code> di bawah. Ganti default topic jika perlu; sisanya diatur lewat portal.</p>

<pre><code class="language-arduino">#include &lt;WiFi.h&gt;
#include &lt;WiFiManager.h&gt;
#include &lt;Preferences.h&gt;
#include &lt;PubSubClient.h&gt;
#include &lt;DHT.h&gt;

#define DHT_PIN  4
#define DHT_TYPE DHT22
#define BTN_RESET_WIFI 0   // BOOT — tahan saat power-on untuk reset WiFi

const char* NS_KINDO = "kindo";
const char* DEFAULT_TOPIC = "kodingindonesia/esp32/dht22/data";
const char* MQTT_HOST = "test.mosquitto.org";
const int   MQTT_PORT = 1883;

DHT dht(DHT_PIN, DHT_TYPE);
WiFiClient espClient;
PubSubClient mqttClient(espClient);
Preferences prefs;

String topicSensor;

WiFiManagerParameter paramTopic(
  "mqtt_topic", "MQTT topic sensor", DEFAULT_TOPIC, 64);

bool tombolResetDitekan() {
  pinMode(BTN_RESET_WIFI, INPUT_PULLUP);
  return digitalRead(BTN_RESET_WIFI) == LOW;
}

void muatPengaturan() {
  prefs.begin(NS_KINDO, true);
  topicSensor = prefs.getString("mqtt_topic", DEFAULT_TOPIC);
  prefs.end();
}

void simpanTopicDariPortal() {
  prefs.begin(NS_KINDO, false);
  prefs.putString("mqtt_topic", paramTopic.getValue());
  prefs.end();
  topicSensor = String(paramTopic.getValue());
}

bool setupWiFiManager() {
  WiFiManager wm;
  wm.setConfigPortalTimeout(180);
  wm.addParameter(&amp;paramTopic);

  if (tombolResetDitekan()) {
    Serial.println("Reset WiFi + NVS (tombol BOOT)");
    wm.resetSettings();
    prefs.begin(NS_KINDO, false);
    prefs.clear();
    prefs.end();
  }

  muatPengaturan();
  paramTopic.setValue(topicSensor.c_str(), 64);

  Serial.println("WiFiManager: autoConnect...");
  if (!wm.autoConnect("KindoESP32-Setup")) {
    Serial.println("Portal gagal / timeout");
    return false;
  }

  simpanTopicDariPortal();
  Serial.println("WiFi OK — SSID: " + WiFi.SSID());
  return true;
}

bool koneksiMQTT() {
  mqttClient.setServer(MQTT_HOST, MQTT_PORT);
  mqttClient.setBufferSize(512);

  String clientId = "ESP32-NVS-" + String(random(0xffff), HEX);
  if (mqttClient.connect(clientId.c_str())) {
    Serial.println("MQTT terhubung");
    return true;
  }
  Serial.print("MQTT gagal, rc=");
  Serial.println(mqttClient.state());
  return false;
}

void publishDHT() {
  float suhu = dht.readTemperature();
  float kelembaban = dht.readHumidity();

  if (isnan(suhu) || isnan(kelembaban)) {
    Serial.println("DHT22 gagal — cek wiring");
    return;
  }

  char payload[96];
  snprintf(payload, sizeof(payload),
    "{\"suhu\":%.1f,\"kelembaban\":%.1f}", suhu, kelembaban);

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

  dht.begin();
  delay(2000);

  if (!setupWiFiManager()) {
    delay(3000);
    ESP.restart();
  }

  if (!koneksiMQTT()) {
    Serial.println("MQTT gagal — restart 5 detik");
    delay(5000);
    ESP.restart();
  }

  publishDHT();
}

void loop() {
  mqttClient.loop();
  delay(10000);
  publishDHT();
}
</code></pre>

<h2>Penjelasan Bagian Kritis</h2>
<ul>
  <li><strong><code>wm.autoConnect("KindoESP32-Setup")</code></strong> — blocking sampai WiFi tersimpan atau timeout 180 detik</li>
  <li><strong><code>WiFiManagerParameter</code></strong> — field custom di portal; nilainya kita simpan ke NVS via <code>prefs.putString</code></li>
  <li><strong><code>wm.resetSettings()</code></strong> — hapus kredensial WiFi tersimpan (dipicu tombol BOOT)</li>
  <li><strong><code>prefs.begin(NS_KINDO, true)</code></strong> — mode read-only saat boot normal</li>
  <li><strong><code>mqttClient.loop()</code></strong> — wajib sebelum <code>publish()</code> (konsisten Seri 1)</li>
  <li><strong><code>setBufferSize(512)</code></strong> — cukup untuk payload JSON DHT22</li>
</ul>

<h2>Uji Coba (Step-by-Step)</h2>
<ol>
  <li>Upload sketch, buka Serial Monitor <strong>115200</strong></li>
  <li>Pertama kali: ESP32 membuat AP <code>KindoESP32-Setup</code></li>
  <li>Di HP: Settings → WiFi → connect <code>KindoESP32-Setup</code></li>
  <li>Portal terbuka otomatis (atau buka <code>192.168.4.1</code>)</li>
  <li>Pilih WiFi rumah, password, cek field <em>MQTT topic sensor</em></li>
  <li>Simpan — ESP32 reboot dan connect</li>
  <li>Serial: <code>WiFi OK</code> → <code>MQTT terhubung</code> → <code>Publish OK</code></li>
  <li>Di MQTT Explorer / <code>mosquitto_sub</code>, subscribe topic yang kamu set</li>
  <li>Reboot ESP32 (tanpa upload) — harus langsung connect tanpa portal</li>
</ol>

<pre><code class="language-bash">mosquitto_sub -h test.mosquitto.org -t "kodingindonesia/esp32/dht22/data" -v</code></pre>

<blockquote>
  <p><strong>Pro tip:</strong> Gunakan topic unik per perangkat, misalnya <code>kodingindonesia/anton/esp32/dht22/data</code>, agar tidak bentrok di broker publik.</p>
</blockquote>

<h2>Gabung dengan <a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">Deep Sleep (#11)</a></h2>
<p>Sketch di atas cocok untuk node USB/adaptor. Untuk baterai, pindahkan logika <code>publishDHT()</code> ke dalam <code>setup()</code> seperti artikel <a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">deep sleep (#11)</a>, lalu tidur lagi. <strong>WiFiManager hanya perlu dijalankan saat pertama kali</strong> atau setelah reset — jangan buka portal tiap bangun (boros baterai).</p>

<pre><code class="language-arduino">// Pola deep sleep + WiFiManager (pseudocode)
prefs.begin("kindo", true);
bool wifiConfigured = prefs.getBool("wifi_ok", false);
prefs.end();

if (!wifiConfigured || tombolResetDitekan()) {
  setupWiFiManager();  // portal sekali
  prefs.begin("kindo", false);
  prefs.putBool("wifi_ok", true);
  prefs.end();
} else {
  WiFi.begin();  // credentials sudah di NVS internal WiFi stack
  // ... timeout connect ...
}
</code></pre>

<h2>Tips &amp; Troubleshooting</h2>
<ul>
  <li><strong>Portal tidak muncul:</strong> Pastikan tidak ada WiFi tersimpan — reset dengan tahan BOOT saat boot, atau <code>wm.resetSettings()</code></li>
  <li><strong>Captive portal tidak redirect (Android):</strong> Buka manual <code>http://192.168.4.1</code></li>
  <li><strong>WiFi connect loop:</strong> Cek <strong>2.4 GHz</strong> — ESP32 tidak support jaringan WiFi <strong>5 GHz saja</strong>; dekatkan ke router</li>
  <li><strong>Topic MQTT kosong:</strong> Cek <code>prefs.getString</code> default; isi ulang lewat portal</li>
  <li><strong>DHT22 NaN:</strong> <code>delay(2000)</code> setelah <code>dht.begin()</code>; GPIO 4 + pull-up</li>
  <li><strong>Compile error WiFiManager:</strong> Update library tzapu ke 2.x; board esp32 v3.x</li>
  <li><strong>NVS penuh:</strong> Jarang di hobby project; <code>prefs.clear()</code> pada namespace <code>kindo</code> jika perlu factory reset</li>
</ul>

<h2>Keamanan &amp; Produksi</h2>
<ul>
  <li>Jangan commit file <code>secrets.h</code> dengan password — WiFiManager menghilangkan kebutuhan itu untuk WiFi</li>
  <li>Portal default <strong>tidak pakai password AP</strong> — untuk deploy komersial, set <code>wm.setAPStaticIPConfig</code> + password AP atau gunakan MQTT dengan auth di <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">broker sendiri (artikel #16)</a></li>
  <li>Segera pindah dari <code>test.mosquitto.org</code> ke <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">broker pribadi</a> untuk data produksi</li>
</ul>

<h2>Langkah Selanjutnya (Seri 2)</h2>
<ul>
  <li><strong><a href="/artikel/i2c-esp32-sensor-bme280-suhu-tekanan-mqtt">Sensor BME280 via I2C (#13)</a></strong> — lebih akurat dari DHT22, plus tekanan udara</li>
  <li><strong><a href="/artikel/oled-ssd1306-esp32-tampilkan-data-sensor-i2c">OLED SSD1306 (#14)</a></strong> — tampilkan data sensor di layar I2C</li>
  <li><strong><a href="/artikel/ota-update-firmware-esp32-via-wifi">OTA update firmware (#15)</a></strong> — butuh <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">WiFiManager (#12)</a> agar firmware bisa di-update tanpa kabel setelah deploy</li>
  <li><strong><a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Broker Mosquitto pribadi (#16)</a></strong> + autentikasi — simpan host/user/pass di NVS dengan pola sama</li>
  <li>Kembali ke <a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">node deep sleep (#11)</a> untuk gabungkan hemat baterai + konfigurasi lapangan</li>
</ul>

<p>Dengan NVS dan WiFiManager, ESP32 kamu siap dipasang di lokasi pelanggan tanpa membawa laptop setiap kali jaringan berubah. Lanjutkan Seri 2 di <a href="/artikel">halaman artikel</a> Koding Indonesia.</p>
HTML;
    }
}
