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

<p>Ini lanjutan <strong>Jalur A</strong> (hardware &amp; sensor) setelah deep sleep (#11), NVS (#12), dan BME280 (#13).</p>

<blockquote>
  <p><strong>Prasyarat:</strong> Sudah paham <a href="/artikel/blink-led-esp32-tutorial-pertama-embedded-system">GPIO dasar (#3)</a>, <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">DHT22 (#5)</a>, <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">MQTT (#7)</a>, dan <strong><a href="/artikel/i2c-esp32-sensor-bme280-suhu-tekanan-mqtt">I2C + BME280 (#13)</a></strong>. Untuk WiFi/NVS/broker auth, baca <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">#12</a> dan <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">#16</a>.</p>
</blockquote>

<h2>Yang Kamu Butuhkan</h2>
<ul>
  <li>ESP32 DevKit</li>
  <li>Modul sensor <strong>BME280</strong> (I2C) — dari artikel #13</li>
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

<h2>Komponen &amp; Wiring</h2>
<p>Hubungkan <strong>kedua modul</strong> ke ESP32 (paralel di breadboard):</p>
<pre><code>ESP32 DevKit          BME280 + OLED (paralel)
─────────────         ─────────────────────
3.3V          ─────── VCC (kedua modul)
GND           ─────── GND (kedua modul)
GPIO 21 (SDA) ─────── SDA (kedua modul)
GPIO 22 (SCL) ─────── SCL (kedua modul)</code></pre>

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

<p><strong>Topic MQTT</strong> (sama #13): <code>kodingindonesia/esp32/bme280/data</code></p>
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
  <li>Portal <code>KindoESP32-Setup</code> — isi WiFi + kredensial broker (#16)</li>
  <li>Verifikasi angka di OLED ≈ Serial Monitor / MQTT Explorer</li>
  <li>Subscribe topic di laptop:</li>
</ol>

<pre><code class="language-bash">mosquitto_sub -h 192.168.1.50 -p 1883 \
  -u kindo_esp32 -P 'KindoMQTT2026!' \
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
  <li><strong>Artikel #15:</strong> <strong>OTA update</strong> — update firmware tanpa kabel USB (butuh #12 WiFiManager)</li>
  <li>Gabung <a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">deep sleep (#11)</a> + BME280 + OLED untuk node lapangan (matikan OLED saat tidur)</li>
  <li>Subscriber <strong>Python → MySQL (#18)</strong> untuk histori + OLED sebagai panel lokal</li>
  <li>Capstone <strong>greenhouse (#39)</strong> — sensor + layar + pompa relay</li>
</ul>

<p>Dengan OLED di bus I2C yang sama, node sensormu punya <strong>antarmuka lokal</strong> selain MQTT cloud. Lanjutkan di <a href="/artikel">halaman artikel</a> Koding Indonesia.</p>
HTML;
    }
}
