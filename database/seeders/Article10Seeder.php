<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article10Seeder extends Seeder
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
            ['slug' => 'dashboard-esp32-web-server-mqtt-monitoring-dht22'],
            [
                'user_id'         => $admin->id,
                'category_id'     => $iotCat->id,
                'title'           => 'Dashboard ESP32: Web Server Lokal + MQTT untuk Monitoring DHT22',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => true,
                'seo_title'       => 'Dashboard ESP32 Web Server + MQTT DHT22 — Proyek IoT Lengkap',
                'seo_description' => 'Gabungkan dashboard lokal di browser dan publish MQTT ke cloud dalam satu ESP32. Capstone tutorial IoT DHT22 berbahasa Indonesia.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament; hindari wipe saat re-seed

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagSlugs = ['esp32', 'mqtt', 'iot', 'dht22', 'wifi', 'smarthome', 'sensor'];
        Tag::updateOrCreate(['slug' => 'dht22'], ['name' => 'dht22']);
        $tagIds   = Tag::whereIn('slug', $tagSlugs)->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command->info('✓ Artikel ke-10 berhasil dipublish: ' . $article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan</h2>
<p>Selamat datang di <strong>artikel penutup seri ESP32 IoT</strong> Koding Indonesia! Sejauh ini kamu sudah belajar:</p>
<ul>
  <li>Dashboard <strong>lokal</strong> lewat Web Server (<a href="/artikel/membuat-web-server-esp32-monitoring-sensor-dht22">artikel web server</a>)</li>
  <li>Telemetri <strong>remote</strong> lewat MQTT (<a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">artikel MQTT</a>)</li>
  <li>Proyek gabungan sensor + aktuator (<a href="/artikel/gabungkan-dht22-relay-mqtt-esp32-satu-proyek">artikel DHT22 + relay</a>)</li>
  <li>Kontrol lampu via MQTT (<a href="/artikel/kontrol-lampu-esp32-mqtt-relay">artikel relay</a>)</li>
</ul>
<p>Kali ini kita satukan <strong>dua saluran monitoring</strong> dalam satu firmware:</p>
<ul>
  <li><strong>Lokal</strong> — buka IP ESP32 di browser rumah (cepat, tanpa internet)</li>
  <li><strong>Remote</strong> — publish suhu ke broker MQTT (bisa dipantau dari mana saja lewat MQTT Explorer)</li>
</ul>
<p>Ini pola nyata di IoT: edge device punya UI lokal untuk teknisi di lapangan, sekaligus mengirim data ke sistem pusat.</p>

<h2>Yang Kamu Butuhkan</h2>
<ul>
  <li>ESP32 DevKit + sensor DHT22 (GPIO 4, pull-up 10kΩ)</li>
  <li>Kabel jumper</li>
  <li>Library: <strong>DHT sensor library</strong>, <strong>PubSubClient</strong> (WebServer &amp; WiFi built-in)</li>
  <li>HP/laptop di WiFi yang sama + <a href="https://mqtt-explorer.com/" target="_blank" rel="noopener">MQTT Explorer</a></li>
</ul>

<blockquote>
  <p><strong>Prasyarat:</strong> Sudah pernah membuat <a href="/artikel/membuat-web-server-esp32-monitoring-sensor-dht22">Web Server ESP32</a> dan <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">publish MQTT</a>. Paham juga <a href="/artikel/menghubungkan-esp32-wifi-kirim-data-server">koneksi WiFi ESP32</a> dan wiring <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">DHT22</a>.</p>
</blockquote>

<h2>Arsitektur Proyek</h2>
<table>
  <thead>
    <tr><th>Saluran</th><th>Cara akses</th><th>Fungsi</th></tr>
  </thead>
  <tbody>
    <tr><td>Web Server (lokal)</td><td><code>http://IP_ESP32/</code> di WiFi rumah</td><td>Dashboard HTML + API <code>/api/data</code></td></tr>
    <tr><td>MQTT (remote)</td><td><code>test.mosquitto.org:1883</code></td><td>Publish JSON ke <code>kodingindonesia/esp32/dht22/data</code> tiap 10 detik</td></tr>
  </tbody>
</table>

<p>Topic MQTT sama dengan <a href="/artikel/gabungkan-dht22-relay-mqtt-esp32-satu-proyek">proyek gabungan</a> agar konsisten di seluruh seri.</p>

<p><em>Catatan topik:</em> Di <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">artikel MQTT</a> kita pakai <code>kodingindonesia/esp32/dht22</code> (payload teks). Dari artikel gabungan ke dashboard ini kita pakai subtopic <code>.../dht22/data</code> dengan payload JSON — pola yang sama dengan <a href="/artikel/gabungkan-dht22-relay-mqtt-esp32-satu-proyek">artikel #9</a>.</p>

<blockquote>
  <p><strong>Broker bukan website</strong> — <code>test.mosquitto.org</code> tidak dibuka di browser. Gunakan MQTT Explorer atau ESP32.</p>
</blockquote>

<blockquote>
  <p><strong>Keamanan:</strong> Web server hanya untuk jaringan lokal — jangan expose ke internet tanpa autentikasi. Broker publik hanya untuk belajar; pakai topic unik dan jangan kirim data sensitif.</p>
</blockquote>

<h2>Kode Lengkap: Web Server + MQTT Publish</h2>
<p>Ganti <code>ssid</code> dan <code>password</code>, lalu upload:</p>

<pre><code class="language-arduino">#include &lt;WiFi.h&gt;
#include &lt;WebServer.h&gt;
#include &lt;PubSubClient.h&gt;
#include &lt;DHT.h&gt;

const char* ssid     = "NamaWiFiKamu";
const char* password = "PasswordWiFiKamu";

const char* mqttServer  = "test.mosquitto.org";
const int   mqttPort    = 1883;
const char* topicSensor = "kodingindonesia/esp32/dht22/data";

#define DHT_PIN  4
#define DHT_TYPE DHT22

DHT dht(DHT_PIN, DHT_TYPE);
WebServer server(80);
WiFiClient espClient;
PubSubClient mqttClient(espClient);

float suhuTerakhir       = 0;
float kelembabanTerakhir = 0;
unsigned long waktuBacaTerakhir = 0;
unsigned long terakhirPublish   = 0;
const unsigned long intervalBaca    = 2000;
const unsigned long intervalPublish = 10000;

String halamanDashboard() {
  String html = "&lt;!DOCTYPE html&gt;&lt;html lang='id'&gt;&lt;head&gt;&lt;meta charset='UTF-8'&gt;";
  html += "&lt;meta name='viewport' content='width=device-width,initial-scale=1'&gt;";
  html += "&lt;title&gt;ESP32 IoT Dashboard&lt;/title&gt;&lt;style&gt;";
  html += "body{font-family:system-ui,sans-serif;max-width:480px;margin:40px auto;padding:20px;background:#f0f4f8;}";
  html += "h1{color:#2979FF;font-size:1.4rem;} .card{background:#fff;border:3px solid #000;";
  html += "box-shadow:4px 4px 0 #000;padding:20px;margin:12px 0;}";
  html += ".nilai{font-size:2.2rem;font-weight:800;} .label{color:#666;font-size:0.85rem;text-transform:uppercase;}";
  html += "&lt;/style&gt;&lt;/head&gt;&lt;body&gt;&lt;h1&gt;ESP32 IoT Dashboard&lt;/h1&gt;";
  html += "&lt;div class='card'&gt;&lt;div class='label'&gt;Suhu&lt;/div&gt;&lt;div class='nilai'&gt;";
  html += String(suhuTerakhir, 1) + " °C&lt;/div&gt;&lt;/div&gt;";
  html += "&lt;div class='card'&gt;&lt;div class='label'&gt;Kelembaban&lt;/div&gt;&lt;div class='nilai'&gt;";
  html += String(kelembabanTerakhir, 1) + " %&lt;/div&gt;&lt;/div&gt;";
  html += "&lt;p style='font-size:0.8rem;color:#888;'&gt;Lokal · Koding Indonesia&lt;/p&gt;";
  html += "&lt;/body&gt;&lt;/html&gt;";
  return html;
}

void handleAPI() {
  String json = "{\"suhu\":" + String(suhuTerakhir, 1);
  json += ",\"kelembaban\":" + String(kelembabanTerakhir, 1) + "}";
  server.send(200, "application/json", json);
}

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

void koneksiWiFi() {
  Serial.print("Menghubungkan ke WiFi");
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi terhubung!");
  Serial.print("Dashboard lokal: http://");
  Serial.println(WiFi.localIP());
}

void koneksiMQTT() {
  mqttClient.setServer(mqttServer, mqttPort);
  mqttClient.setBufferSize(512);
  while (!mqttClient.connected()) {
    Serial.print("MQTT connect...");
    String clientId = "ESP32-Dash-" + String(random(0xffff), HEX);
    if (mqttClient.connect(clientId.c_str())) {
      Serial.println(" OK");
    } else {
      Serial.print(" gagal rc=");
      Serial.println(mqttClient.state());
      delay(5000);
    }
  }
}

void publishMQTT() {
  if (waktuBacaTerakhir == 0) return;
  if (isnan(suhuTerakhir) || isnan(kelembabanTerakhir)) return;
  String payload = "{\"suhu\":" + String(suhuTerakhir, 1);
  payload += ",\"kelembaban\":" + String(kelembabanTerakhir, 1) + "}";
  if (mqttClient.publish(topicSensor, payload.c_str())) {
    Serial.print("MQTT publish: ");
    Serial.println(payload);
  }
}

void setup() {
  Serial.begin(115200);
  randomSeed(micros());
  dht.begin();
  delay(2000);

  koneksiWiFi();

  server.on("/",        []() { server.send(200, "text/html", halamanDashboard()); });
  server.on("/api/data",  handleAPI);
  server.onNotFound([]() { server.send(404, "text/plain", "404"); });
  server.begin();
  Serial.println("Web Server aktif port 80");

  koneksiMQTT();
  delay(2000);
}

void loop() {
  if (WiFi.status() != WL_CONNECTED) {
    koneksiWiFi();
  }
  server.handleClient();
  bacaSensor();

  if (!mqttClient.connected()) {
    koneksiMQTT();
  }
  mqttClient.loop();

  unsigned long sekarang = millis();
  if (sekarang - terakhirPublish &gt;= intervalPublish) {
    terakhirPublish = sekarang;
    publishMQTT();
  }
}</code></pre>

<h2>Uji Coba</h2>
<h3>1. Dashboard lokal</h3>
<ol>
  <li>Upload kode, buka Serial Monitor (115200 baud)</li>
  <li>Catat IP, misalnya <code>192.168.1.100</code></li>
  <li>Buka <code>http://192.168.1.100</code> di browser (WiFi sama)</li>
  <li>Cek API: <code>http://192.168.1.100/api/data</code></li>
  <li>Refresh browser untuk nilai terbaru (dashboard statis per request — auto-refresh bisa ditambah dengan JavaScript)</li>
</ol>

<h3>2. Saluran MQTT (remote)</h3>
<ol>
  <li>Buka MQTT Explorer → <code>test.mosquitto.org:1883</code></li>
  <li>Cari topic <code>kodingindonesia/esp32/dht22/data</code></li>
  <li>Pastikan JSON suhu/kelembaban masuk setiap ~10 detik</li>
</ol>

<h3>Alternatif: mosquitto_sub (Terminal)</h3>
<pre><code class="language-bash">mosquitto_sub -h test.mosquitto.org -t "kodingindonesia/esp32/dht22/data" -v</code></pre>
<p><em>Tool CLI dari paket <a href="https://mosquitto.org/download/" target="_blank" rel="noopener">Mosquitto</a> — Linux, Mac, dan Windows setelah install Mosquitto.</em></p>

<h2>Alur Program</h2>
<ol>
  <li><code>bacaSensor()</code> — baca DHT22 tiap 2 detik, simpan ke variabel global</li>
  <li><code>server.handleClient()</code> — layani dashboard &amp; API saat ada request HTTP</li>
  <li><code>mqttClient.loop()</code> — jaga koneksi MQTT tetap hidup</li>
  <li><code>publishMQTT()</code> — kirim JSON ke broker tiap 10 detik</li>
</ol>

<p>Kedua saluran membaca <strong>variabel sensor yang sama</strong> — tidak ada pembacaan ganda yang bentrok.</p>

<h2>Tips &amp; Troubleshooting</h2>
<ul>
  <li><strong>Browser tidak bisa buka IP ESP32:</strong> Pastikan HP/laptop satu WiFi dengan ESP32. Cek IP di Serial Monitor.</li>
  <li><strong>MQTT publish gagal tapi web OK:</strong> Cek internet router. <code>mqttClient.loop()</code> harus dipanggil di <code>loop()</code>.</li>
  <li><strong>rc=-2 / rc=4 MQTT:</strong> Broker tidak terjangkau atau masalah auth — <code>test.mosquitto.org:1883</code> tanpa password.</li>
  <li><strong>Nilai suhu NaN di dashboard:</strong> Cek wiring DHT22, pull-up 10kΩ, dan <code>delay(2000)</code> setelah <code>dht.begin()</code>.</li>
  <li><strong>Web lambat saat MQTT reconnect:</strong> Normal di jaringan WiFi rumah — reconnect MQTT pakai <code>delay(5000)</code> di dalam blocking loop; untuk produksi pertimbangkan non-blocking reconnect.</li>
  <li><strong>Topic tidak muncul:</strong> Case-sensitive. Coba subscribe wildcard <code>kodingindonesia/esp32/#</code>.</li>
</ul>

<h2>Indeks Seri ESP32 IoT (10 Artikel)</h2>
<ol>
  <li><a href="/artikel/mengenal-esp32-mikrokontroler-wifi-bluetooth-iot">Mengenal ESP32</a></li>
  <li><a href="/artikel/cara-install-arduino-ide-setup-esp32-board-manager">Install Arduino IDE &amp; ESP32 Board</a></li>
  <li><a href="/artikel/blink-led-esp32-tutorial-pertama-embedded-system">Blink LED — Tutorial Pertama</a></li>
  <li><a href="/artikel/menghubungkan-esp32-wifi-kirim-data-server">ESP32 ke WiFi &amp; Kirim Data</a></li>
  <li><a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">Baca Sensor DHT22</a></li>
  <li><a href="/artikel/membuat-web-server-esp32-monitoring-sensor-dht22">Web Server Monitoring DHT22</a></li>
  <li><a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">MQTT — Kirim Data ke Broker</a></li>
  <li><a href="/artikel/kontrol-lampu-esp32-mqtt-relay">Kontrol Lampu via MQTT &amp; Relay</a></li>
  <li><a href="/artikel/gabungkan-dht22-relay-mqtt-esp32-satu-proyek">Gabungan DHT22 + Relay MQTT</a></li>
  <li><strong>Dashboard Web Server + MQTT</strong> — artikel ini (capstone)</li>
</ol>

<h2>Roadmap Belajar Selanjutnya — Seri 2</h2>
<p>Seri 10 artikel ini adalah fondasi capstone. <strong>Seri 2 ESP32/IoT Lanjutan</strong> sudah berjalan — delapan belas artikel pertama (urutan publish):</p>
<ol>
  <li><strong><a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">Deep sleep ESP32 + DHT22 hemat baterai</a></strong></li>
  <li><strong><a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">NVS + WiFiManager</a></strong> — konfigurasi tanpa hardcode</li>
  <li><strong><a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Broker Mosquitto pribadi + autentikasi</a></strong></li>
  <li><strong><a href="/artikel/i2c-esp32-sensor-bme280-suhu-tekanan-mqtt">I2C + sensor BME280</a></strong> — suhu, kelembaban &amp; tekanan</li>
  <li><strong><a href="/artikel/oled-ssd1306-esp32-tampilkan-data-sensor-i2c">OLED SSD1306</a></strong> — tampilkan data di layar I2C</li>
  <li><strong><a href="/artikel/ota-update-firmware-esp32-via-wifi">OTA update firmware ESP32</a></strong> — update tanpa kabel USB</li>
  <li><strong><a href="/artikel/home-assistant-integrasi-esp32-mqtt">Home Assistant + ESP32 MQTT</a></strong> — dashboard smart home</li>
  <li><strong><a href="/artikel/esphome-flash-esp32-tanpa-coding-arduino">ESPHome flash ESP32 tanpa Arduino</a></strong> — YAML + OTA</li>
  <li><strong><a href="/artikel/node-red-dashboard-otomasi-iot-mqtt-esp32">Node-RED dashboard IoT MQTT</a></strong> — otomasi visual</li>
  <li><strong><a href="/artikel/sensor-gerak-pir-esp32-lampu-mqtt-debounce">Sensor gerak PIR + lampu MQTT</a></strong> — automasi koridor dengan debounce</li>
  <li><strong><a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">MQTT TLS + QoS + LWT</a></strong> — amankan broker Mosquitto</li>
  <li><strong><a href="/artikel/ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt">NTP &amp; timestamp ESP32</a></strong> — waktu akurat untuk log sensor MQTT</li>
  <li><strong><a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">Subscriber Python → MySQL</a></strong> — histori sensor MQTT ke database</li>
  <li><strong><a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">InfluxDB + Grafana</a></strong> — dashboard grafik histori sensor</li>
  <li><strong><a href="/artikel/rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32">REST API vs MQTT</a></strong> — kapan pakai HTTP vs push MQTT</li>
  <li><strong><a href="/artikel/esp-now-kirim-data-antar-esp32-tanpa-router-wifi">ESP-NOW antar ESP32 tanpa router WiFi</a></strong> — peer-to-peer tanpa router</li>
  <li><strong><a href="/artikel/lora-esp32-modul-sx1278-kirim-data-jarak-jauh">LoRa ESP32 + SX1278 jarak jauh</a></strong> — sensor kilometer tanpa WiFi</li>
  <li><strong><a href="/artikel/esp32-cam-streaming-mjpeg-capture-foto-wifi">ESP32-CAM MJPEG streaming</a></strong> — live video &amp; capture foto via WiFi</li>
</ol>
<p>Masih akan datang di Seri 2:</p>
<ul>
  <li>Gateway LoRa → MQTT (#28)</li>
</ul>

<blockquote>
  <p><strong>Terima kasih sudah mengikuti seri ini!</strong> Kamu sekarang punya fondasi solid: sensor → WiFi → web lokal → MQTT → aktuator → dashboard hybrid. Share proyek kamu dan pantau terus <a href="/artikel">artikel baru</a> di Koding Indonesia.</p>
</blockquote>
HTML;
    }
}
