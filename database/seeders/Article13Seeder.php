<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article13Seeder extends Seeder
{
    public function run(): void
    {
        $admin  = User::first();
        $espCat = Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $espCat) {
            throw new \RuntimeException('User atau kategori tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'i2c-esp32-sensor-bme280-suhu-tekanan-mqtt';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $espCat->id,
                'title'           => 'Protokol I2C di ESP32 + Sensor BME280 (Suhu, Kelembaban & Tekanan)',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'I2C ESP32 + Sensor BME280 — Suhu, Tekanan & MQTT',
                'seo_description' => 'Pelajari protokol I2C di ESP32 dan baca sensor BME280: suhu, kelembaban, tekanan udara. Publish JSON ke broker MQTT pribadi dengan pola NVS.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        Tag::updateOrCreate(['slug' => 'bme280'], ['name' => 'bme280']);
        Tag::updateOrCreate(['slug' => 'i2c'], ['name' => 'i2c']);

        $tagSlugs = ['esp32', 'bme280', 'i2c', 'sensor', 'iot', 'mqtt', 'wifi', 'dht22'];
        $tagIds   = Tag::whereIn('slug', $tagSlugs)->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command->info('✓ Artikel ke-13 berhasil dipublish: ' . $article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan</h2>
<p>Di Seri 1 kita memakai <strong>DHT22</strong> — sensor digital satu kabel data yang mudah untuk pemula. Di Seri 2, saatnya naik level ke <strong>I2C</strong>: bus dua kawat (SDA/SCL) yang bisa menghubungkan banyak perangkat sekaligus, dengan akurasi lebih baik.</p>

<p>Artikel ini fokus pada <strong>sensor BME280</strong>: mengukur <strong>suhu</strong>, <strong>kelembaban</strong>, dan <strong>tekanan udara</strong> dalam satu modul kecil. Kita baca datanya lewat I2C, lalu publish JSON ke broker MQTT — mengikuti pola <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">NVS + WiFiManager (#12)</a> dan <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">broker pribadi (#16)</a>.</p>

<p>Ini lanjutan <strong>Jalur A</strong> (hardware &amp; sensor) setelah <a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">deep sleep (#11)</a> dan <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">konfigurasi lapangan (#12)</a>.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> Sudah paham <a href="/artikel/blink-led-esp32-tutorial-pertama-embedded-system">GPIO dasar (#3)</a>, <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">DHT22 (#5)</a>, <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">MQTT (#7)</a>. Untuk WiFi/NVS/broker auth, baca <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">#12</a> dan <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">#16</a>.</p>
</blockquote>

<h2>Yang Kamu Butuhkan</h2>
<ul>
  <li>ESP32 DevKit</li>
  <li>Modul sensor <strong>BME280</strong> (breakout I2C, 3.3V) — ±Rp 25.000–40.000 di marketplace lokal</li>
  <li>Kabel jumper female–female atau breadboard</li>
  <li>Broker MQTT — disarankan <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Mosquitto pribadi (#16)</a> (boleh <code>test.mosquitto.org</code> hanya untuk uji hardware)</li>
</ul>

<h2>DHT22 vs BME280 — Kenapa Upgrade?</h2>
<table>
  <thead>
    <tr><th>Aspek</th><th>DHT22 (Seri 1)</th><th>BME280 (Seri 2)</th></tr>
  </thead>
  <tbody>
    <tr><td>Protokol</td><td>Digital 1-wire (GPIO)</td><td><strong>I2C</strong> (SDA + SCL)</td></tr>
    <tr><td>Suhu</td><td>Cukup untuk hobby</td><td>Stabil, cocok monitoring &amp; cuaca</td></tr>
    <tr><td>Kelembaban</td><td>±2–5% RH</td><td>±3% RH</td></tr>
    <tr><td>Tekanan udara</td><td>Tidak ada</td><td><strong>Ada</strong> (hPa) — ketinggian, cuaca</td></tr>
    <tr><td>Perangkat di satu bus</td><td>1 pin per sensor</td><td>Banyak (alamat I2C berbeda)</td></tr>
  </tbody>
</table>

<h2>Apa itu I2C?</h2>
<p><strong>I2C</strong> (Inter-Integrated Circuit) memakai dua kabel data:</p>
<ul>
  <li><strong>SDA</strong> — data (Serial Data)</li>
  <li><strong>SCL</strong> — clock (Serial Clock)</li>
</ul>
<p>Setiap perangkat punya <strong>alamat 7-bit</strong> unik di bus yang sama. BME280 umumnya <code>0x76</code> atau <code>0x77</code> (tergantung jumper modul).</p>

<blockquote>
  <p><strong>Analogi:</strong> I2C seperti lift apartemen — banyak penghuni (sensor), satu jalur (bus), setiap lantai punya nomor (alamat).</p>
</blockquote>

<figure role="img" aria-label="Diagram arsitektur BME280 I2C: ESP32 baca BME280 via SDA/SCL lalu publish MQTT ke Mosquitto" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 620 420" style="display:block;max-width:620px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="bmeArr" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/></marker>
    <marker id="bmeArrO" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#FF7A2F"/></marker>
    <marker id="bmeArrG" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#2E7D32"/></marker>
  </defs>
  <rect x="0" y="0" width="620" height="420" fill="#F5F5F0" rx="6"/>
  <!-- ESP32 -->
  <rect x="140" y="15" width="340" height="65" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="310" y="42" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">ESP32 — Wire + WiFiManager (#12)</text>
  <text x="310" y="62" text-anchor="middle" fill="#4A5568" font-size="10">Wire.begin(21, 22) · PubSubClient · Preferences NVS</text>
  <!-- I2C arrow -->
  <line x1="310" y1="80" x2="310" y2="118" stroke="#2979FF" stroke-width="2.5" marker-end="url(#bmeArr)"/>
  <rect x="215" y="90" width="190" height="26" rx="13" fill="#E8F4FF" stroke="#2979FF" stroke-width="1.5"/>
  <text x="310" y="108" text-anchor="middle" fill="#2979FF" font-size="10" font-weight="700">I2C · SDA 21 / SCL 22</text>
  <!-- BME280 -->
  <rect x="155" y="130" width="310" height="70" rx="6" fill="#C8E6C9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="310" y="158" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">BME280</text>
  <text x="310" y="178" text-anchor="middle" fill="#4A5568" font-size="10">alamat 0x76 / 0x77 · suhu · RH · tekanan (hPa)</text>
  <!-- MQTT arrow -->
  <line x1="310" y1="200" x2="310" y2="248" stroke="#FF7A2F" stroke-width="2.5" marker-end="url(#bmeArrO)"/>
  <rect x="330" y="210" width="150" height="26" rx="13" fill="#FFF3E8" stroke="#FF7A2F" stroke-width="1.5"/>
  <text x="405" y="228" text-anchor="middle" fill="#FF7A2F" font-size="10" font-weight="700">MQTT publish JSON</text>
  <!-- Mosquitto -->
  <rect x="130" y="255" width="360" height="55" rx="6" fill="#2979FF" stroke="#000" stroke-width="2.5"/>
  <text x="310" y="280" text-anchor="middle" fill="#fff" font-size="14" font-weight="700">Mosquitto (#16)</text>
  <text x="310" y="298" text-anchor="middle" fill="#e3f2fd" font-size="10">kodingindonesia/esp32/bme280/data</text>
  <!-- Outcomes -->
  <line x1="210" y1="310" x2="110" y2="348" stroke="#2E7D32" stroke-width="2" marker-end="url(#bmeArrG)"/>
  <line x1="310" y1="310" x2="310" y2="348" stroke="#2E7D32" stroke-width="2" marker-end="url(#bmeArrG)"/>
  <line x1="410" y1="310" x2="510" y2="348" stroke="#2E7D32" stroke-width="2" marker-end="url(#bmeArrG)"/>
  <rect x="15" y="355" width="190" height="42" rx="6" fill="#FFF3E8" stroke="#000" stroke-width="2"/>
  <text x="110" y="373" text-anchor="middle" fill="#1a1a1a" font-size="11" font-weight="700">Serial Monitor</text>
  <text x="110" y="389" text-anchor="middle" fill="#4A5568" font-size="9">Publish OK + JSON</text>
  <rect x="215" y="355" width="190" height="42" rx="6" fill="#FFF3E8" stroke="#000" stroke-width="2"/>
  <text x="310" y="373" text-anchor="middle" fill="#1a1a1a" font-size="11" font-weight="700">MQTT Explorer</text>
  <text x="310" y="389" text-anchor="middle" fill="#4A5568" font-size="9">subscribe topic BME280</text>
  <rect x="415" y="355" width="190" height="42" rx="6" fill="#FFF3E8" stroke="#000" stroke-width="2"/>
  <text x="510" y="373" text-anchor="middle" fill="#1a1a1a" font-size="11" font-weight="700">OLED (#14) next</text>
  <text x="510" y="389" text-anchor="middle" fill="#4A5568" font-size="9">bus I2C sama, alamat beda</text>
  <text x="310" y="412" text-anchor="middle" fill="#4A5568" font-size="11">ESP32 ← I2C → BME280 → MQTT → Mosquitto · siap gabung OLED</text>
</svg>
<figcaption style="margin-top:.75rem;font-size:.875rem;color:#4A5568;text-align:center">BME280 di bus I2C → publish JSON ke <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Mosquitto (#16)</a> — lanjut <a href="/artikel/oled-ssd1306-esp32-tampilkan-data-sensor-i2c">OLED (#14)</a> di bus yang sama.</figcaption>
</figure>

<h2>Komponen &amp; Wiring I2C</h2>
<p>Pin default I2C ESP32 DevKit (bisa diubah di kode):</p>

<figure role="img" aria-label="Diagram wiring ESP32 ke BME280: 3.3V ke VCC, GND ke GND, GPIO 21 SDA, GPIO 22 SCL" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 620 340" style="display:block;max-width:620px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="bmeWR" markerWidth="7" markerHeight="7" refX="6" refY="3.5" orient="auto"><path d="M0,0 L7,3.5 L0,7 Z" fill="#C62828"/></marker>
    <marker id="bmeWK" markerWidth="7" markerHeight="7" refX="6" refY="3.5" orient="auto"><path d="M0,0 L7,3.5 L0,7 Z" fill="#1a1a1a"/></marker>
    <marker id="bmeWB" markerWidth="7" markerHeight="7" refX="6" refY="3.5" orient="auto"><path d="M0,0 L7,3.5 L0,7 Z" fill="#2979FF"/></marker>
    <marker id="bmeWG" markerWidth="7" markerHeight="7" refX="6" refY="3.5" orient="auto"><path d="M0,0 L7,3.5 L0,7 Z" fill="#2E7D32"/></marker>
  </defs>
  <rect x="0" y="0" width="620" height="340" fill="#F5F5F0" rx="6"/>
  <!-- ESP32 -->
  <rect x="30" y="40" width="170" height="240" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="115" y="72" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">ESP32 DevKit</text>
  <circle cx="185" cy="110" r="5" fill="#C62828"/>
  <text x="170" y="115" text-anchor="end" fill="#1a1a1a" font-size="12" font-weight="600">3.3V</text>
  <circle cx="185" cy="155" r="5" fill="#1a1a1a"/>
  <text x="170" y="160" text-anchor="end" fill="#1a1a1a" font-size="12" font-weight="600">GND</text>
  <circle cx="185" cy="200" r="5" fill="#2979FF"/>
  <text x="170" y="197" text-anchor="end" fill="#1a1a1a" font-size="12" font-weight="600">GPIO 21</text>
  <text x="170" y="211" text-anchor="end" fill="#4A5568" font-size="9">SDA</text>
  <circle cx="185" cy="245" r="5" fill="#2E7D32"/>
  <text x="170" y="242" text-anchor="end" fill="#1a1a1a" font-size="12" font-weight="600">GPIO 22</text>
  <text x="170" y="256" text-anchor="end" fill="#4A5568" font-size="9">SCL</text>
  <!-- BME280 -->
  <rect x="400" y="55" width="190" height="210" rx="6" fill="#C8E6C9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="495" y="85" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">BME280</text>
  <text x="495" y="105" text-anchor="middle" fill="#4A5568" font-size="10">I2C 0x76 / 0x77 · 3.3V</text>
  <circle cx="415" cy="140" r="5" fill="#C62828"/>
  <text x="430" y="145" fill="#1a1a1a" font-size="12" font-weight="600">VCC</text>
  <circle cx="415" cy="175" r="5" fill="#1a1a1a"/>
  <text x="430" y="180" fill="#1a1a1a" font-size="12" font-weight="600">GND</text>
  <circle cx="415" cy="210" r="5" fill="#2979FF"/>
  <text x="430" y="215" fill="#1a1a1a" font-size="12" font-weight="600">SDA</text>
  <circle cx="415" cy="245" r="5" fill="#2E7D32"/>
  <text x="430" y="250" fill="#1a1a1a" font-size="12" font-weight="600">SCL</text>
  <!-- Wires pin-to-pin -->
  <line x1="190" y1="110" x2="410" y2="140" stroke="#C62828" stroke-width="2.5" marker-end="url(#bmeWR)"/>
  <line x1="190" y1="155" x2="410" y2="175" stroke="#1a1a1a" stroke-width="2.5" marker-end="url(#bmeWK)"/>
  <line x1="190" y1="200" x2="410" y2="210" stroke="#2979FF" stroke-width="2.5" marker-end="url(#bmeWB)"/>
  <line x1="190" y1="245" x2="410" y2="245" stroke="#2E7D32" stroke-width="2.5" marker-end="url(#bmeWG)"/>
  <!-- Legend -->
  <rect x="30" y="305" width="14" height="10" rx="2" fill="#C62828"/>
  <text x="50" y="314" fill="#4A5568" font-size="10">3.3V → VCC</text>
  <rect x="150" y="305" width="14" height="10" rx="2" fill="#1a1a1a"/>
  <text x="170" y="314" fill="#4A5568" font-size="10">GND → GND</text>
  <rect x="270" y="305" width="14" height="10" rx="2" fill="#2979FF"/>
  <text x="290" y="314" fill="#4A5568" font-size="10">GPIO 21 → SDA</text>
  <rect x="420" y="305" width="14" height="10" rx="2" fill="#2E7D32"/>
  <text x="440" y="314" fill="#4A5568" font-size="10">GPIO 22 → SCL</text>
</svg>
<figcaption style="margin-top:.75rem;font-size:.875rem;color:#4A5568;text-align:center">Wiring pin-ke-pin: 3.3V→VCC, GND→GND, GPIO 21→SDA, GPIO 22→SCL. Modul wajib <strong>3.3V</strong>.</figcaption>
</figure>

<ul>
  <li>Modul breakout BME280 biasanya sudah punya <strong>pull-up</strong> kecil di PCB</li>
  <li>Pastikan modul <strong>3.3V</strong> — jangan 5V langsung ke ESP32</li>
  <li>Jika <code>bme.begin()</code> gagal, coba alamat <code>0x77</code> (beberapa modul pakai ini)</li>
</ul>

<h2>Install Library</h2>
<p>Pastikan Arduino IDE dan board ESP32 sudah terpasang — ikuti <a href="/artikel/cara-install-arduino-ide-setup-esp32-board-manager">tutorial install Arduino IDE &amp; Board Manager (#2)</a> jika belum.</p>
<p>Arduino IDE → <strong>Sketch → Include Library → Manage Libraries</strong>:</p>
<ul>
  <li><strong>Adafruit BME280 Library</strong></li>
  <li><strong>Adafruit Unified Sensor</strong> (dependency)</li>
  <li><strong>PubSubClient</strong> (Nick O'Leary)</li>
  <li><strong>WiFiManager</strong> (tzapu) — provisioning WiFi + MQTT</li>
</ul>
<p>Board: <strong>esp32</strong> by Espressif (<strong>v3.x</strong>). Library <code>Wire</code>, <code>Preferences</code>, dan <code>WiFi</code> sudah built-in.</p>

<p><strong>Broker &amp; topic:</strong> publish ke broker kamu (lihat <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">#16</a>).</p>
<p><strong>Topic default:</strong> <code>kodingindonesia/esp32/bme280/data</code></p>
<p><strong>Payload JSON:</strong> <code>{"suhu":28.5,"kelembaban":65.2,"tekanan":1013.25}</code> — tekanan dalam hPa.</p>

<blockquote>
  <p><strong>Catatan topic Seri 2:</strong> DHT22 memakai <code>.../dht22/data</code>. BME280 punya subtopic sendiri <code>.../bme280/data</code> agar hierarki MQTT tetap rapi.</p>
</blockquote>

<h2>Kode Lengkap: BME280 + I2C + WiFiManager + MQTT Auth</h2>
<p>Pola NVS sama <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">artikel #16</a> — tanpa hardcode WiFi/MQTT di sketch.</p>

<pre><code class="language-arduino">#include &lt;Wire.h&gt;
#include &lt;WiFi.h&gt;
#include &lt;WiFiManager.h&gt;
#include &lt;Preferences.h&gt;
#include &lt;PubSubClient.h&gt;
#include &lt;Adafruit_Sensor.h&gt;
#include &lt;Adafruit_BME280.h&gt;

#define I2C_SDA 21
#define I2C_SCL 22
const char* NS_KINDO = "kindo";
const int MQTT_PORT = 1883;

Adafruit_BME280 bme;
WiFiClient espClient;
PubSubClient mqttClient(espClient);
Preferences prefs;

String mqttHost, mqttUser, mqttPass, topicSensor;

WiFiManagerParameter pHost("mqtt_host", "MQTT broker IP", "192.168.1.50", 64);
WiFiManagerParameter pUser("mqtt_user", "MQTT username", "kindo_esp32", 32);
WiFiManagerParameter pPass("mqtt_pass", "MQTT password", "", 48);
WiFiManagerParameter pTopic("mqtt_topic", "MQTT topic", "kodingindonesia/esp32/bme280/data", 64);

bool initBME280() {
  Wire.begin(I2C_SDA, I2C_SCL);
  if (bme.begin(0x76)) return true;
  if (bme.begin(0x77)) return true;
  Serial.println("BME280 tidak ditemukan — cek wiring I2C");
  return false;
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
  String clientId = "ESP32-BME280-" + String(random(0xffff), HEX);
  if (!mqttClient.connect(clientId.c_str(), mqttUser.c_str(), mqttPass.c_str())) {
    Serial.print("MQTT gagal, rc=");
    Serial.println(mqttClient.state());
    return false;
  }
  return true;
}

void publishBME280() {
  float suhu = bme.readTemperature();
  float kelembaban = bme.readHumidity();
  float tekanan = bme.readPressure() / 100.0F; // Pa → hPa

  if (isnan(suhu) || isnan(kelembaban) || isnan(tekanan)) {
    Serial.println("BME280 baca gagal");
    return;
  }

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

  if (!initBME280()) {
    while (true) delay(1000);
  }
  delay(100);

  if (!setupWiFiManager()) ESP.restart();
  if (!koneksiMQTT()) ESP.restart();

  publishBME280();
}

void loop() {
  mqttClient.loop();
  delay(10000);
  publishBME280();
}</code></pre>

<h2>Penjelasan Bagian Kritis</h2>
<ul>
  <li><strong><code>Wire.begin(I2C_SDA, I2C_SCL)</code></strong> — inisialisasi bus I2C di pin 21/22</li>
  <li><strong><code>bme.begin(0x76)</code> / <code>0x77</code></strong> — coba kedua alamat umum modul breakout</li>
  <li><strong><code>readPressure() / 100.0F</code></strong> — library mengembalikan Pascal; dibagi 100 jadi hPa</li>
  <li><strong><code>setBufferSize(512)</code></strong> — payload JSON 3 field butuh buffer cukup besar</li>
  <li><strong><code>mqttClient.loop()</code></strong> — wajib sebelum <code>publish()</code></li>
</ul>

<h2>Uji Coba (Step-by-Step)</h2>
<ol>
  <li>Rakit wiring I2C, upload sketch, Serial Monitor <strong>115200</strong></li>
  <li>Pastikan tidak ada error <code>BME280 tidak ditemukan</code></li>
  <li>Portal <code>KindoESP32-Setup</code> — isi WiFi + kredensial <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">broker (#16)</a></li>
  <li>Serial: <code>Publish OK</code> dengan JSON suhu, kelembaban, tekanan</li>
  <li>Di laptop — <code>mosquitto_sub</code> atau <strong>MQTT Explorer</strong>:</li>
</ol>

<pre><code class="language-bash">mosquitto_sub -h 192.168.1.50 -p 1883 \
  -u kindo_esp32 -P 'GANTI_PASSWORD_MQTT' \
  -t "kodingindonesia/esp32/bme280/data" -v</code></pre>

<blockquote>
  <p><strong>Pro tip:</strong> Topic unik per unit, misalnya <code>kodingindonesia/anton/esp32/bme280/data</code>.</p>
</blockquote>

<h2>Scanner I2C (Opsional)</h2>
<p>Jika sensor tidak terdeteksi, upload sketch scanner untuk melihat alamat di bus:</p>

<pre><code class="language-arduino">#include &lt;Wire.h&gt;
void setup() {
  Serial.begin(115200);
  Wire.begin(21, 22);
  Serial.println("Scan I2C...");
  for (byte addr = 1; addr &lt; 127; addr++) {
    Wire.beginTransmission(addr);
    if (Wire.endTransmission() == 0) {
      Serial.printf("Perangkat di 0x%02X\n", addr);
    }
  }
}
void loop() {}</code></pre>

<h2>Tips &amp; Troubleshooting</h2>
<ul>
  <li><strong>BME280 tidak ditemukan:</strong> Cek 3.3V, SDA/SCL tidak tertukar, coba <code>0x77</code>, jalankan scanner I2C</li>
  <li><strong>Nilai tekanan aneh:</strong> Normal sekitar 950–1050 hPa di permukaan laut; bandingkan dengan cuaca lokal</li>
  <li><strong>Portal WiFi tidak muncul:</strong> Reset WiFi tersimpan — tahan BOOT saat boot atau <code>wm.resetSettings()</code>; buka manual <code>http://192.168.4.1</code> jika captive portal tidak redirect</li>
  <li><strong>Compile error WiFiManager:</strong> Update library tzapu ke 2.x; pastikan board <strong>esp32 v3.x</strong></li>
  <li><strong>MQTT gagal (rc=-2):</strong> Broker tidak terjangkau — cek IP host, firewall port 1883, ESP32 dan broker satu jaringan</li>
  <li><strong>MQTT auth gagal (rc=5):</strong> Username/password salah — lihat <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">troubleshooting broker #16</a></li>
  <li><strong>Panjang kabel I2C:</strong> Untuk prototype breadboard &lt;30 cm biasanya aman; kabel panjang butuh pull-up lebih kuat</li>
  <li><strong>WiFi 2.4 GHz:</strong> ESP32 tidak support jaringan WiFi <strong>5 GHz saja</strong></li>
</ul>

<h2>Keamanan &amp; Produksi</h2>
<ul>
  <li>Jangan commit password MQTT ke Git — simpan lewat portal WiFiManager + NVS seperti <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">artikel #12</a></li>
  <li>Portal AP <code>KindoESP32-Setup</code> default <strong>tanpa password</strong> — untuk deploy lapangan, pertimbangkan password AP atau provisioning terbatas</li>
  <li>Gunakan <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">broker Mosquitto pribadi + auth (#16)</a> — jangan andalkan <code>test.mosquitto.org</code> untuk data produksi</li>
</ul>

<h2>Langkah Selanjutnya (Seri 2)</h2>
<ul>
  <li><strong><a href="/artikel/oled-ssd1306-esp32-tampilkan-data-sensor-i2c">OLED SSD1306 (#14)</a></strong> — tampilkan suhu/tekanan di layar (<strong>bus I2C sama</strong>, alamat berbeda dari BME280)</li>
  <li><strong><a href="/artikel/ota-update-firmware-esp32-via-wifi">OTA update firmware (#15)</a></strong> — update tanpa kabel</li>
  <li>Gabung dengan <a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">deep sleep (#11)</a> untuk node sensor hemat baterai + BME280</li>
  <li><strong><a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">Subscriber Python → MySQL (#18)</a></strong> untuk simpan histori tekanan &amp; suhu</li>
  <li><strong><a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">InfluxDB + Grafana (#19)</a></strong> — grafik histori measurement <code>bme280</code></li>
  <li>Capstone <strong><a href="/artikel/smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt">greenhouse (#39)</a></strong> — sensor BME280 + aktuator + dashboard</li>
</ul>

<p>Dengan I2C dan BME280, hardware stack kamu siap untuk dashboard <a href="/artikel/oled-ssd1306-esp32-tampilkan-data-sensor-i2c">OLED (#14)</a> dan capstone <a href="/artikel/smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt">greenhouse (#39)</a> Seri 2. Lanjutkan di <a href="/artikel">halaman artikel</a> Koding Indonesia.</p>
HTML;
    }
}
