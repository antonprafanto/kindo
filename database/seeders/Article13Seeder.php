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

<p>Ini lanjutan <strong>Jalur A</strong> (hardware &amp; sensor) setelah deep sleep (#11) dan konfigurasi lapangan (#12).</p>

<blockquote>
  <p><strong>Prasyarat:</strong> Sudah paham <a href="/artikel/blink-led-esp32-tutorial-pertama-embedded-system">GPIO dasar (#3)</a>, <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">DHT22 (#5)</a>, <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">MQTT (#7)</a>. Untuk WiFi/NVS/broker auth, baca <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">#12</a> dan <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">#16</a>.</p>
</blockquote>

<h2>Yang Kamu Butuhkan</h2>
<ul>
  <li>ESP32 DevKit</li>
  <li>Modul sensor <strong>BME280</strong> (breakout I2C, 3.3V) — ±Rp 25.000–40.000 di marketplace lokal</li>
  <li>Kabel jumper female–female atau breadboard</li>
  <li>Broker MQTT — disarankan <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Mosquitto pribadi</a> (boleh <code>test.mosquitto.org</code> hanya untuk uji hardware)</li>
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

<h2>Komponen &amp; Wiring I2C</h2>
<p>Pin default I2C ESP32 DevKit (bisa diubah di kode):</p>
<pre><code>ESP32 DevKit          BME280
─────────────         ──────
3.3V          ─────── VCC
GND           ─────── GND
GPIO 21 (SDA) ─────── SDA
GPIO 22 (SCL) ─────── SCL</code></pre>

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
  <li>Portal <code>KindoESP32-Setup</code> — isi WiFi + kredensial broker (#16)</li>
  <li>Serial: <code>Publish OK</code> dengan JSON suhu, kelembaban, tekanan</li>
  <li>Di laptop — <code>mosquitto_sub</code> atau <strong>MQTT Explorer</strong>:</li>
</ol>

<pre><code class="language-bash">mosquitto_sub -h 192.168.1.50 -p 1883 \
  -u kindo_esp32 -P 'KindoMQTT2026!' \
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
  <li><strong><a href="/artikel/oled-ssd1306-esp32-tampilkan-data-sensor-i2c">OLED SSD1306</a></strong> — tampilkan suhu/tekanan di layar (<strong>bus I2C sama</strong>, alamat berbeda dari BME280)</li>
  <li><strong><a href="/artikel/ota-update-firmware-esp32-via-wifi">OTA update firmware</a></strong> — update tanpa kabel</li>
  <li>Gabung dengan <a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">deep sleep (#11)</a> untuk node sensor hemat baterai + BME280</li>
  <li><strong><a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">Subscriber Python → MySQL (#18)</a></strong> untuk simpan histori tekanan &amp; suhu</li>
</ul>

<p>Dengan I2C dan BME280, hardware stack kamu siap untuk dashboard OLED dan capstone greenhouse Seri 2. Lanjutkan di <a href="/artikel">halaman artikel</a> Koding Indonesia.</p>
HTML;
    }
}
