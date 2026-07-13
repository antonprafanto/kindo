<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article39Seeder extends Seeder
{
    public function run(): void
    {
        $admin  = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first();

        if (! $admin || ! $iotCat) {
            throw new \RuntimeException('User atau kategori tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        Tag::updateOrCreate(['slug' => 'greenhouse'], ['name' => 'greenhouse']);

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $iotCat->id,
                'title'           => 'Capstone Seri 2: Smart Greenhouse ESP32 — Sensor, Aktuator & Dashboard MQTT',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => true,
                'seo_title'       => 'Smart Greenhouse ESP32 — Capstone IoT Sensor, Pompa & Grafana',
                'seo_description' => 'Capstone Seri 2: gabung BME280, soil moisture, relay pompa, PIR, Grafana & MQTT broker sendiri — proyek greenhouse ESP32 lengkap berbahasa Indonesia.',
            ]
        );

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', [
            'esp32', 'mqtt', 'iot', 'greenhouse', 'sensor', 'smarthome',
        ])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command->info('✓ Artikel ke-39 berhasil dipublish: ' . $article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — Penutup Seri 2 ESP32/IoT Lanjutan</h2>
<p>Selamat! Kamu sudah menempuh <strong>28 artikel Seri 2</strong> — dari <a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">deep sleep (#11)</a> hingga <a href="/artikel/https-sertifikat-esp32-wificlientsecure-api-rest">HTTPS (#38)</a>. Artikel ini adalah <strong>capstone penutup</strong> yang menggabungkan sensor, aktuator, broker MQTT, dan dashboard ke dalam satu proyek nyata: <strong>smart greenhouse</strong> (rumah kaca pintar).</p>

<p>Di capstone Seri 1 (<a href="/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22">#10</a>), kamu membangun dashboard hybrid Web + MQTT untuk satu sensor DHT22. Di capstone Seri 2, skala naik: <strong>multi-sensor</strong> (udara + tanah + cahaya), <strong>multi-aktuator</strong> (pompa air + ventilasi), <strong>multi-node</strong> (gateway utama + node kebun hemat baterai), dan <strong>dashboard histori</strong> di <a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">Grafana (#19)</a>.</p>

<blockquote>
  <p><strong>Prasyarat wajib:</strong> Sudah paham <a href="/artikel/i2c-esp32-sensor-bme280-suhu-tekanan-mqtt">BME280 I2C (#13)</a>, <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">broker Mosquitto (#16)</a>, <a href="/artikel/adc-esp32-sensor-analog-soil-moisture-ldr-mqtt">soil moisture ADC (#35)</a>, dan idealnya sudah baca <a href="/artikel/sensor-gerak-pir-esp32-lampu-mqtt-debounce">PIR (#24)</a> serta <a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">deep sleep (#11)</a>.</p>
</blockquote>

<h2>Apa yang Akan Kamu Bangun?</h2>
<p>Sistem greenhouse skala rumahan / UMKM kecil dengan komponen berikut:</p>
<ul>
  <li><strong>Gateway utama</strong> — ESP32 di dalam rumah kaca: BME280 (suhu, kelembaban udara, tekanan) + soil moisture capacitive + LDR cahaya</li>
  <li><strong>Node aktuator</strong> — ESP32 + relay modul: kontrol pompa irigasi dan lampu grow (opsional)</li>
  <li><strong>Node kebun (opsional)</strong> — ESP32 deep sleep di ujung lahan: kirim data tanah periodik</li>
  <li><strong>Sensor gerak (opsional)</strong> — PIR di pintu greenhouse untuk lampu koridor</li>
  <li><strong>Broker MQTT</strong> — Mosquitto pribadi di Raspberry Pi / VPS (<a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">#16</a>)</li>
  <li><strong>Dashboard</strong> — Grafana panel suhu, kelembaban tanah, status pompa; alert Telegram opsional</li>
  <li><strong>Backup offline</strong> — log CSV ke SD Card (<a href="/artikel/sd-card-spi-esp32-logging-data-sensor-offline">#37</a>) saat WiFi putus</li>
</ul>

<h2>Arsitektur Sistem Greenhouse</h2>
<figure role="img" aria-label="Diagram flowchart arsitektur smart greenhouse: sensor publish ke broker, dashboard subscribe histori, aktuator terima perintah kontrol" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 540" style="display:block;max-width:860px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="ghArrow" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto">
      <path d="M0,0 L8,4 L0,8 Z" fill="#1a1a1a"/>
    </marker>
    <marker id="ghArrowBlue" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto">
      <path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/>
    </marker>
    <marker id="ghArrowOrange" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto">
      <path d="M0,0 L8,4 L0,8 Z" fill="#FF7A2F"/>
    </marker>
  </defs>
  <rect x="0" y="0" width="860" height="540" fill="#F5F5F0" rx="6"/>
  <!-- Broker -->
  <rect x="230" y="16" width="400" height="76" rx="6" fill="#2979FF" stroke="#000" stroke-width="2.5"/>
  <text x="430" y="44" text-anchor="middle" fill="#fff" font-size="15" font-weight="700">Broker Mosquitto</text>
  <text x="430" y="64" text-anchor="middle" fill="#e3f2fd" font-size="12">192.168.1.50:1883 · auth kindo_esp32</text>
  <text x="430" y="82" text-anchor="middle" fill="#e3f2fd" font-size="11">Topic: kodingindonesia/esp32/...</text>
  <text x="430" y="108" text-anchor="middle" fill="#718096" font-size="10">Pusat MQTT — semua publish &amp; subscribe lewat sini</text>
  <!-- Left: Gateway -->
  <rect x="48" y="148" width="264" height="72" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="180" y="176" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">Gateway utama</text>
  <text x="180" y="196" text-anchor="middle" fill="#4A5568" font-size="12">BME280 + soil moisture + LDR</text>
  <text x="180" y="212" text-anchor="middle" fill="#718096" font-size="11">GPIO 21/22 · 34 · 35</text>
  <!-- Right: Aktuator -->
  <rect x="548" y="148" width="264" height="72" rx="6" fill="#FFF3E8" stroke="#000" stroke-width="2.5"/>
  <text x="680" y="176" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">Node aktuator</text>
  <text x="680" y="196" text-anchor="middle" fill="#4A5568" font-size="12">Relay pompa + lampu grow</text>
  <text x="680" y="212" text-anchor="middle" fill="#718096" font-size="11">GPIO 26 · 27 · topic pompa/kontrol</text>
  <!-- MQTT arrows — arah data benar -->
  <line x1="180" y1="148" x2="310" y2="92" stroke="#2979FF" stroke-width="2.5" marker-end="url(#ghArrowBlue)"/>
  <text x="228" y="128" text-anchor="middle" fill="#2979FF" font-size="11" font-weight="700">publish sensor ↑</text>
  <line x1="550" y1="92" x2="680" y2="148" stroke="#FF7A2F" stroke-width="2.5" marker-end="url(#ghArrowOrange)"/>
  <text x="612" y="128" text-anchor="middle" fill="#FF7A2F" font-size="11" font-weight="700">perintah kontrol ↓</text>
  <!-- Optional nodes — grouping, bukan alur MQTT -->
  <line x1="180" y1="220" x2="180" y2="268" stroke="#CBD5E0" stroke-width="1.5" stroke-dasharray="5 4"/>
  <rect x="48" y="268" width="264" height="56" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2" stroke-dasharray="6 4"/>
  <text x="180" y="292" text-anchor="middle" fill="#1a1a1a" font-size="13" font-weight="600">Node deep sleep</text>
  <text x="180" y="310" text-anchor="middle" fill="#718096" font-size="11">opsional · probe tanah jauh (#11)</text>
  <line x1="680" y1="220" x2="680" y2="268" stroke="#CBD5E0" stroke-width="1.5" stroke-dasharray="5 4"/>
  <rect x="548" y="268" width="264" height="56" rx="6" fill="#FFF3E8" stroke="#000" stroke-width="2" stroke-dasharray="6 4"/>
  <text x="680" y="292" text-anchor="middle" fill="#1a1a1a" font-size="13" font-weight="600">Servo flap ventilasi</text>
  <text x="680" y="310" text-anchor="middle" fill="#718096" font-size="11">opsional · topic servo/sudut (#33)</text>
  <!-- Dashboard -->
  <rect x="130" y="408" width="600" height="88" rx="6" fill="#F5F5F0" stroke="#000" stroke-width="2.5"/>
  <text x="430" y="436" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">Dashboard &amp; Otomasi</text>
  <text x="430" y="458" text-anchor="middle" fill="#4A5568" font-size="12">InfluxDB + Grafana (#19) · Home Assistant (#21) · Node-RED (#23)</text>
  <text x="430" y="478" text-anchor="middle" fill="#718096" font-size="11">Subscriber Python (#18) · alert Telegram · threshold pompa</text>
  <!-- Broker ↔ Dashboard -->
  <line x1="430" y1="92" x2="430" y2="408" stroke="#1a1a1a" stroke-width="2" marker-end="url(#ghArrow)"/>
  <text x="456" y="250" text-anchor="start" fill="#4A5568" font-size="10" font-weight="600">subscribe data ↓</text>
  <line x1="500" y1="408" x2="500" y2="120" stroke="#718096" stroke-width="1.5" stroke-dasharray="5 4" marker-end="url(#ghArrow)"/>
  <text x="524" y="270" text-anchor="start" fill="#718096" font-size="10">publish otomasi ↑</text>
  <!-- SD backup di gateway -->
  <rect x="48" y="340" width="64" height="56" rx="4" fill="#fff" stroke="#FF7A2F" stroke-width="2"/>
  <text x="80" y="364" text-anchor="middle" fill="#FF7A2F" font-size="10" font-weight="700">SD</text>
  <text x="80" y="378" text-anchor="middle" fill="#718096" font-size="9">#37</text>
  <text x="80" y="390" text-anchor="middle" fill="#718096" font-size="9">backup</text>
  <line x1="112" y1="368" x2="140" y2="200" stroke="#FF7A2F" stroke-width="1.5" stroke-dasharray="4 3"/>
  <text x="88" y="332" text-anchor="middle" fill="#A0AEC0" font-size="9">offline</text>
</svg>
<figcaption style="margin-top:.75rem;font-size:.875rem;color:#718096;text-align:center">Diagram alur smart greenhouse — sensor <strong>publish ↑</strong> ke broker; dashboard <strong>subscribe ↓</strong> histori &amp; <strong>publish ↑</strong> otomasi; aktuator <strong>terima perintah ↓</strong> dari broker.</figcaption>
</figure>

<p>Alur data: sensor publish JSON ke MQTT → subscriber Python/InfluxDB menyimpan histori → Grafana menampilkan grafik → otomasi (Node-RED / HA / firmware) mengontrol pompa berdasarkan threshold kelembaban tanah.</p>

<h2>Estimasi Biaya Hardware</h2>
<table>
  <thead>
    <tr><th>Komponen</th><th>Qty</th><th>Estimasi (IDR)</th></tr>
  </thead>
  <tbody>
    <tr><td>ESP32 DevKit</td><td>2–3</td><td>~90.000–135.000</td></tr>
    <tr><td>BME280 module I2C</td><td>1</td><td>~35.000</td></tr>
    <tr><td>Soil moisture capacitive</td><td>1–2</td><td>~25.000–50.000</td></tr>
    <tr><td>LDR + resistor 10kΩ</td><td>1</td><td>~5.000</td></tr>
    <tr><td>Relay modul 2-ch</td><td>1</td><td>~20.000</td></tr>
    <tr><td>Pompa DC mini + power supply</td><td>1</td><td>~40.000–80.000</td></tr>
    <tr><td>Raspberry Pi / VPS broker</td><td>1</td><td>~500.000+ (atau pakai PC lama)</td></tr>
    <tr><td><strong>Total minimal</strong></td><td></td><td><strong>~215.000</strong> (tanpa broker baru)</td></tr>
  </tbody>
</table>
<p>Software stack (Mosquitto, InfluxDB, Grafana, ESP32 firmware) <strong>gratis</strong> open source. Biaya listrik broker 24/7 diperhitungkan terpisah.</p>

<h2>Hierarki Topic MQTT Greenhouse</h2>
<p>Konvensi Seri 2 — semua node memakai prefix <code>kodingindonesia/esp32/</code>:</p>
<table>
  <thead>
    <tr><th>Topic</th><th>Arah</th><th>Payload</th><th>Sumber artikel</th></tr>
  </thead>
  <tbody>
    <tr><td><code>.../bme280/data</code></td><td>publish</td><td><code>{"suhu":28.1,"kelembaban":72.0,"tekanan":1008.5,"unix":1782977400}</code></td><td>#13</td></tr>
    <tr><td><code>.../tanah/data</code></td><td>publish</td><td><code>{"kelembaban_tanah":42,"unix":1782977400}</code></td><td>#35</td></tr>
    <tr><td><code>.../cahaya/data</code></td><td>publish</td><td><code>{"cahaya_percent":78,"unix":1782977400}</code></td><td>#35</td></tr>
    <tr><td><code>.../pompa/kontrol</code></td><td>subscribe</td><td><code>ON</code> / <code>OFF</code> / <code>AUTO</code></td><td>capstone</td></tr>
    <tr><td><code>.../pir/gerak</code></td><td>publish</td><td><code>{"gerak":true,"lampu":"ON"}</code></td><td>#24</td></tr>
    <tr><td><code>.../servo/sudut</code></td><td>subscribe</td><td><code>120</code> (derajat flap)</td><td>#33</td></tr>
  </tbody>
</table>
<p>Timestamp <code>unix: 1782977400</code> = <code>2026-07-02T14:30:00</code> UTC — konsisten dengan <a href="/artikel/ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt">NTP (#34)</a> di seluruh Seri 2.</p>
<p><strong>Catatan topic pompa:</strong> di <a href="/artikel/adc-esp32-sensor-analog-soil-moisture-ldr-mqtt">#35</a>, relay pompa masih memakai <code>kodingindonesia/esp32/lampu/kontrol</code>. Di capstone ini kita pakai <code>.../pompa/kontrol</code> agar irigasi terpisah dari lampu koridor PIR (<code>.../lampu/kontrol</code>) — pola relay tetap sama seperti <a href="/artikel/kontrol-lampu-esp32-mqtt-relay">#8</a>.</p>

<h2>Persiapan Broker Mosquitto</h2>
<p>Gunakan broker pribadi yang sudah dikonfigurasi di <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">artikel #16</a>:</p>
<ul>
  <li>Host: <code>192.168.1.50</code> (ganti IP broker kamu)</li>
  <li>Port: <code>1883</code> (plain) atau <code>8883</code> dengan TLS (<a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">#17</a>)</li>
  <li>User: <code>kindo_esp32</code> · Password: <code>GANTI_PASSWORD_MQTT</code></li>
</ul>
<pre><code class="language-bash">mosquitto_sub -h 192.168.1.50 -u kindo_esp32 -P GANTI_PASSWORD_MQTT \
  -t "kodingindonesia/esp32/#" -v</code></pre>
<p>Wildcard <code>#</code> memudahkan debug seluruh traffic greenhouse sekaligus.</p>

<h2>Gateway Utama — BME280 + Soil + LDR</h2>
<p>Node ini menggabungkan pola <a href="/artikel/i2c-esp32-sensor-bme280-suhu-tekanan-mqtt">BME280 (#13)</a> dan <a href="/artikel/adc-esp32-sensor-analog-soil-moisture-ldr-mqtt">ADC soil/LDR (#35)</a> dalam satu firmware.</p>

<h3>Wiring I2C BME280</h3>
<table>
  <thead><tr><th>BME280</th><th>ESP32</th></tr></thead>
  <tbody>
    <tr><td>VCC</td><td>3.3V</td></tr>
    <tr><td>GND</td><td>GND</td></tr>
    <tr><td>SDA</td><td>GPIO 21</td></tr>
    <tr><td>SCL</td><td>GPIO 22</td></tr>
  </tbody>
</table>

<h3>Wiring Soil Moisture &amp; LDR</h3>
<table>
  <thead><tr><th>Sensor</th><th>ESP32</th><th>Catatan</th></tr></thead>
  <tbody>
    <tr><td>Soil VCC</td><td>3.3V</td><td>Capacitive — hindari korosi probe resistif</td></tr>
    <tr><td>Soil OUT</td><td>GPIO 34 (ADC1)</td><td>Kalibrasi kering/basah</td></tr>
    <tr><td>LDR + divider</td><td>GPIO 35 (ADC1)</td><td>Resistor 10kΩ ke GND</td></tr>
  </tbody>
</table>

<h2>Sketch Gateway — Publish Multi-Sensor</h2>
<pre><code class="language-cpp">#include &lt;WiFi.h&gt;
#include &lt;PubSubClient.h&gt;
#include &lt;Wire.h&gt;
#include &lt;Adafruit_BME280.h&gt;

#define SOIL_PIN 34
#define LDR_PIN  35

const char* ssid = "GANTI_NAMA_WIFI";
const char* pass = "GANTI_PASSWORD_WIFI";
const char* mqtt_host = "192.168.1.50";
const char* mqtt_user = "kindo_esp32";
const char* mqtt_pass = "GANTI_PASSWORD_MQTT";
const char* topic_bme   = "kodingindonesia/esp32/bme280/data";
const char* topic_tanah = "kodingindonesia/esp32/tanah/data";
const char* topic_cahaya = "kodingindonesia/esp32/cahaya/data";

const int SOIL_DRY = 3200;
const int SOIL_WET = 1400;

Adafruit_BME280 bme;
WiFiClient wifiClient;
PubSubClient mqtt(wifiClient);

int soilPercent(int raw) {
  return constrain(map(raw, SOIL_DRY, SOIL_WET, 0, 100), 0, 100);
}

int readFiltered(uint8_t pin) {
  long sum = 0;
  for (int i = 0; i &lt; 10; i++) { sum += analogRead(pin); delay(5); }
  return (int)(sum / 10);
}

void reconnectMqtt() {
  while (!mqtt.connected()) {
    if (mqtt.connect("kindo-greenhouse-gw", mqtt_user, mqtt_pass)) {
      Serial.println("MQTT OK");
    } else { delay(2000); }
  }
}

void setup() {
  Serial.begin(115200);
  analogReadResolution(12);
  Wire.begin(21, 22);
  if (!bme.begin(0x76)) { Serial.println("BME280 fail"); while(1); }
  WiFi.begin(ssid, pass);
  while (WiFi.status() != WL_CONNECTED) delay(300);
  mqtt.setServer(mqtt_host, 1883);
  reconnectMqtt();
}

void loop() {
  if (!mqtt.connected()) reconnectMqtt();
  mqtt.loop();

  static unsigned long last = 0;
  if (millis() - last &lt; 15000) return;
  last = millis();

  float t = bme.readTemperature();
  float h = bme.readHumidity();
  float p = bme.readPressure() / 100.0F;
  int soil = soilPercent(readFiltered(SOIL_PIN));
  int ldr  = constrain(map(readFiltered(LDR_PIN), 500, 3500, 0, 100), 0, 100);

  char buf[128];
  snprintf(buf, sizeof(buf),
    "{\"suhu\":%.1f,\"kelembaban\":%.1f,\"tekanan\":%.1f,\"unix\":1782977400}",
    t, h, p);
  mqtt.publish(topic_bme, buf);

  snprintf(buf, sizeof(buf), "{\"kelembaban_tanah\":%d,\"unix\":1782977400}", soil);
  mqtt.publish(topic_tanah, buf);

  snprintf(buf, sizeof(buf), "{\"cahaya_percent\":%d,\"unix\":1782977400}", ldr);
  mqtt.publish(topic_cahaya, buf);

  Serial.printf("GW: suhu=%.1f tanah=%d%% cahaya=%d%%\n", t, soil, ldr);
}</code></pre>

<h2>PlatformIO — Gateway</h2>
<pre><code class="language-ini">lib_deps =
  knolleary/PubSubClient
  adafruit/Adafruit BME280 Library
  adafruit/Adafruit Unified Sensor</code></pre>

<h2>Node Aktuator — Relay Pompa &amp; Lampu</h2>
<p>Ikuti pola <a href="/artikel/kontrol-lampu-esp32-mqtt-relay">relay MQTT (#8)</a> dengan topic khusus pompa:</p>
<ul>
  <li>GPIO relay pompa: <code>GPIO 26</code> (active LOW — sesuaikan modul kamu)</li>
  <li>GPIO relay lampu grow: <code>GPIO 27</code></li>
  <li>Subscribe: <code>kodingindonesia/esp32/pompa/kontrol</code></li>
</ul>
<pre><code class="language-cpp">const char* topicPompa = "kodingindonesia/esp32/pompa/kontrol";
#define RELAY_POMPA 26
#define RELAY_LAMPU 27

void mqttCallback(char* topic, byte* payload, unsigned int len) {
  char msg[8] = {0};
  memcpy(msg, payload, min(len, (unsigned)7));
  if (strcmp(msg, "ON") == 0) {
    digitalWrite(RELAY_POMPA, LOW);
    digitalWrite(RELAY_LAMPU, LOW);
  } else if (strcmp(msg, "OFF") == 0) {
    digitalWrite(RELAY_POMPA, HIGH);
    digitalWrite(RELAY_LAMPU, HIGH);
  }
  // AUTO: logika threshold di Node-RED / HA
}

void setup() {
  pinMode(RELAY_POMPA, OUTPUT);
  pinMode(RELAY_LAMPU, OUTPUT);
  digitalWrite(RELAY_POMPA, HIGH);
  digitalWrite(RELAY_LAMPU, HIGH);
  mqtt.setCallback(mqttCallback);
  reconnectMqtt();
  mqtt.subscribe(topicPompa);
}

void loop() {
  if (!mqtt.connected()) reconnectMqtt();
  mqtt.loop();
}
</code></pre>
<p>Pola lengkap WiFi + <code>mqtt.connect</code> sama seperti <a href="/artikel/kontrol-lampu-esp32-mqtt-relay">relay (#8)</a> — pastikan <code>mqtt.loop()</code> dipanggil setiap iterasi agar callback kontrol pompa responsif.</p>

<h2>Otomasi Pompa — Threshold Kelembaban Tanah</h2>
<p>Logika irigasi dengan <strong>hysteresis</strong> anti-flicker (sama prinsip debounce <a href="/artikel/sensor-gerak-pir-esp32-lampu-mqtt-debounce">PIR (#24)</a>):</p>
<ul>
  <li>Jika <code>kelembaban_tanah &lt; 30%</code> → publish <code>ON</code> ke <code>kodingindonesia/esp32/pompa/kontrol</code></li>
  <li>Jika <code>kelembaban_tanah &gt; 60%</code> → publish <code>OFF</code></li>
  <li>Zona 30–60%: pertahankan status terakhir</li>
</ul>
<p>Implementasi bisa di <strong>Node-RED</strong> (<a href="/artikel/node-red-dashboard-otomasi-iot-mqtt-esp32">#23</a>): mqtt in <code>kodingindonesia/esp32/tanah/data</code> → function node cek threshold → mqtt out <code>kodingindonesia/esp32/pompa/kontrol</code>. Atau di <strong>Home Assistant</strong> (<a href="/artikel/home-assistant-integrasi-esp32-mqtt">#21</a>) dengan automation <code>for: 2 minutes</code> agar pompa tidak cepat on/off.</p>

<h2>Ventilasi Servo Flap (Opsional)</h2>
<p>Tambahkan <a href="/artikel/kontrol-servo-pwm-esp32-mqtt-gerakan-presisi">servo SG90 (#33)</a> untuk membuka flap saat suhu BME280 &gt; 32°C:</p>
<ul>
  <li>Subscribe <code>kodingindonesia/esp32/servo/sudut</code> — payload angka 0–180</li>
  <li>Sudut 0° = tertutup · 120° = ventilasi cukup · 180° = terbuka penuh</li>
</ul>
<p>Ramp perlahan (2° per 20 ms) melindungi engsel plastik — detail di artikel #33.</p>

<h2>Node Deep Sleep di Ujung Kebun (Opsional)</h2>
<p>Untuk probe tanah jauh dari stop kontak, pakai pola <a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">deep sleep (#11)</a>:</p>
<ul>
  <li>Bangun tiap 15 menit (timer wakeup)</li>
  <li>Baca soil moisture → publish <code>kodingindonesia/esp32/tanah/data</code> dengan field <code>node: "kebun-barat"</code></li>
  <li>Matikan WiFi → <code>esp_deep_sleep(15 * 60 * 1000000ULL)</code></li>
</ul>
<p>Baterai 18650 + modul charge bisa bertahan berminggu-minggu — cocok untuk kebun luas tanpa kabel listrik.</p>

<h2>PIR di Pintu Greenhouse (Opsional)</h2>
<p>Integrasikan <a href="/artikel/sensor-gerak-pir-esp32-lampu-mqtt-debounce">PIR (#24)</a> untuk lampu koridor: gerak terdeteksi → publish ke <code>kodingindonesia/esp32/pir/gerak</code> → relay lampu ON selama 60 detik (hold time). Topic kontrol tetap kompatibel dengan <code>kodingindonesia/esp32/lampu/kontrol</code> dari artikel #8.</p>

<h2>Backup SD Card Saat WiFi Putus</h2>
<p>Tambahkan modul microSD SPI seperti <a href="/artikel/sd-card-spi-esp32-logging-data-sensor-offline">artikel #37</a> pada gateway utama. Saat <code>mqtt.publish()</code> gagal, append baris CSV ke <code>/greenhouse.csv</code>:</p>
<pre><code class="language-csv">unix,suhu,kelembaban_udara,kelembaban_tanah,cahaya_percent
1782977400,28.1,72.0,42,78</code></pre>
<p>Saat WiFi kembali, baca file dan publish batch ke MQTT — data histori tidak hilang.</p>

<h2>Dashboard Grafana — Panel Greenhouse</h2>
<p>Setelah data masuk InfluxDB lewat subscriber <a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">Python (#18)</a>, buat dashboard di <a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">Grafana (#19)</a>:</p>
<ol>
  <li><strong>Panel Time series</strong> — suhu BME280 (<code>suhu</code>) + kelembaban udara</li>
  <li><strong>Panel Gauge</strong> — kelembaban tanah real-time (threshold merah &lt; 30%)</li>
  <li><strong>Panel Stat</strong> — status pompa (ON/OFF dari topic kontrol)</li>
  <li><strong>Alert rule</strong> — suhu &gt; 35°C selama 10 menit → notifikasi Telegram</li>
</ol>
<p>Query Flux contoh: <code>from(bucket:"sensor") |> range(start:-24h) |> filter(fn:(r) => r._measurement == "tanah")</code></p>

<h2>Integrasi Home Assistant &amp; Node-RED</h2>
<p><strong>Home Assistant</strong> — entity dari MQTT discovery:</p>
<ul>
  <li><code>sensor.greenhouse_suhu</code> ← topic <code>bme280/data</code> · template <code>{{ value_json.suhu }}</code></li>
  <li><code>sensor.greenhouse_tanah</code> ← topic <code>kodingindonesia/esp32/tanah/data</code></li>
  <li><code>switch.greenhouse_pompa</code> ← topic <code>kodingindonesia/esp32/pompa/kontrol</code> · command <code>ON</code>/<code>OFF</code></li>
</ul>
<p><strong>Node-RED</strong> — flow visual: gauge tanah + tombol manual pompa + automasi threshold — lihat pola lengkap di <a href="/artikel/node-red-dashboard-otomasi-iot-mqtt-esp32">#23</a>.</p>

<h2>Keamanan Produksi</h2>
<ul>
  <li><strong>MQTT auth</strong> — jangan pakai broker tanpa password di production (<a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">#16</a>)</li>
  <li><strong>TLS</strong> — aktifkan port 8883 untuk traffic terenkripsi (<a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">#17</a>)</li>
  <li><strong>Webhook HTTPS</strong> — alert Grafana ke Telegram via <a href="/artikel/https-sertifikat-esp32-wificlientsecure-api-rest">WiFiClientSecure (#38)</a> jika perlu notifikasi dari ESP32 langsung</li>
  <li><strong>OTA</strong> — update firmware node tanpa kabel USB (<a href="/artikel/ota-update-firmware-esp32-via-wifi">#15</a>)</li>
  <li><strong>WiFiManager</strong> — ganti SSID tanpa reflash (<a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">#12</a>)</li>
</ul>

<blockquote>
  <p><strong>Pro tip:</strong> Pisahkan VLAN atau SSID khusus IoT di router rumah — node greenhouse tidak perlu akses ke laptop/kantor. Broker Mosquitto cukup expose ke LAN, bukan internet publik, kecuali kamu sudah paham hardening TLS + firewall.</p>
</blockquote>

<h2>Perbandingan Jalur yang Digabung</h2>
<table>
  <thead>
    <tr><th>Jalur Seri 2</th><th>Artikel</th><th>Peran di Greenhouse</th></tr>
  </thead>
  <tbody>
    <tr><td>A — Hardware</td><td>#11, #13, #24, #35</td><td>Sensor + deep sleep + PIR</td></tr>
    <tr><td>B — Infrastruktur</td><td>#16, #17, #18, #19</td><td>Broker, TLS, histori, Grafana</td></tr>
    <tr><td>C — Smart home</td><td>#21, #23</td><td>HA entity + Node-RED otomasi</td></tr>
    <tr><td>D — Jarak jauh</td><td>#26, #28</td><td>LoRa node kebun luas (opsional)</td></tr>
    <tr><td>E — Tooling</td><td>#29, #31</td><td>PlatformIO monorepo + FreeRTOS</td></tr>
    <tr><td>Tier 2</td><td>#33, #37, #38</td><td>Servo flap, SD backup, HTTPS alert</td></tr>
  </tbody>
</table>

<h2>Checklist Uji Coba End-to-End</h2>
<ol>
  <li>Broker Mosquitto hidup — <code>systemctl status mosquitto</code></li>
  <li><code>mosquitto_sub -t "kodingindonesia/esp32/#" -v</code> menampilkan traffic</li>
  <li>Gateway publish BME280 + tanah + cahaya setiap 15 detik</li>
  <li>Node aktuator merespons <code>mosquitto_pub -h 192.168.1.50 -u kindo_esp32 -P GANTI_PASSWORD_MQTT -t "kodingindonesia/esp32/pompa/kontrol" -m ON</code></li>
  <li>Pompa fisik menyala — cek arus relay sebelum sambung ke pompa besar</li>
  <li>Grafana panel menampilkan data histori 1 jam terakhir</li>
  <li>Otomasi threshold: tanah &lt; 30% → pompa ON tanpa intervensi manual</li>
  <li>(Opsional) SD Card mencatat saat WiFi dimatikan sementara</li>
  <li>(Opsional) Alert Grafana Telegram saat suhu &gt; 35°C</li>
</ol>

<h2>Troubleshooting</h2>
<ul>
  <li><strong>BME280 tidak terbaca:</strong> Cek alamat I2C <code>0x76</code> vs <code>0x77</code> — ganti di <code>bme.begin()</code></li>
  <li><strong>Soil selalu 0% atau 100%:</strong> Kalibrasi ulang <code>SOIL_DRY</code> / <code>SOIL_WET</code> di air vs tanah kering</li>
  <li><strong>Pompa tidak mati:</strong> Cek hysteresis — mungkin threshold terlalu dekat; tambah <code>for: 2 minutes</code> di HA</li>
  <li><strong>MQTT rc=-2:</strong> Broker tidak terjangkau — ping <code>192.168.1.50</code> dari ESP32 serial monitor</li>
  <li><strong>Grafana kosong:</strong> Pastikan subscriber Python jalan dan bucket InfluxDB menerima measurement <code>tanah</code></li>
  <li><strong>Relay terbalik:</strong> Banyak modul active LOW — <code>LOW</code> = ON, <code>HIGH</code> = OFF</li>
</ul>

<h2>Indeks Lengkap Seri 2 ESP32/IoT Lanjutan (29 Artikel)</h2>
<p>Ini adalah <strong>indeks resmi</strong> Seri 2 — diselaraskan dengan <a href="/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22">capstone Seri 1 (#10)</a>:</p>
<ol>
  <li><a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">Deep sleep ESP32 + DHT22 hemat baterai</a></li>
  <li><a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">NVS + WiFiManager</a></li>
  <li><a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Broker Mosquitto pribadi + autentikasi</a></li>
  <li><a href="/artikel/i2c-esp32-sensor-bme280-suhu-tekanan-mqtt">I2C + sensor BME280</a></li>
  <li><a href="/artikel/oled-ssd1306-esp32-tampilkan-data-sensor-i2c">OLED SSD1306</a></li>
  <li><a href="/artikel/ota-update-firmware-esp32-via-wifi">OTA update firmware ESP32</a></li>
  <li><a href="/artikel/home-assistant-integrasi-esp32-mqtt">Home Assistant + ESP32 MQTT</a></li>
  <li><a href="/artikel/esphome-flash-esp32-tanpa-coding-arduino">ESPHome flash ESP32 tanpa Arduino</a></li>
  <li><a href="/artikel/node-red-dashboard-otomasi-iot-mqtt-esp32">Node-RED dashboard IoT MQTT</a></li>
  <li><a href="/artikel/sensor-gerak-pir-esp32-lampu-mqtt-debounce">Sensor gerak PIR + lampu MQTT</a></li>
  <li><a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">MQTT TLS + QoS + LWT</a></li>
  <li><a href="/artikel/ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt">NTP &amp; timestamp ESP32</a></li>
  <li><a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">Subscriber Python → MySQL</a></li>
  <li><a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">InfluxDB + Grafana</a></li>
  <li><a href="/artikel/rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32">REST API vs MQTT</a></li>
  <li><a href="/artikel/esp-now-kirim-data-antar-esp32-tanpa-router-wifi">ESP-NOW antar ESP32 tanpa router WiFi</a></li>
  <li><a href="/artikel/lora-esp32-modul-sx1278-kirim-data-jarak-jauh">LoRa ESP32 + SX1278 jarak jauh</a></li>
  <li><a href="/artikel/esp32-cam-streaming-mjpeg-capture-foto-wifi">ESP32-CAM MJPEG streaming</a></li>
  <li><a href="/artikel/gateway-lora-mqtt-esp32-sensor-jarak-jauh-dashboard">Gateway LoRa → MQTT</a></li>
  <li><a href="/artikel/migrasi-platformio-esp32-vscode-project-rapi">Migrasi ke PlatformIO di VS Code</a></li>
  <li><a href="/artikel/esp32-firebase-realtime-database-sensor-cloud">ESP32 + Firebase Realtime Database</a></li>
  <li><a href="/artikel/freertos-esp32-multi-task-sensor-wifi-mqtt">FreeRTOS multi-task Sensor + WiFi + MQTT</a></li>
  <li><a href="/artikel/bluetooth-esp32-ble-kirim-data-sensor-smartphone">Bluetooth BLE kirim data sensor ke smartphone</a></li>
  <li><a href="/artikel/kontrol-servo-pwm-esp32-mqtt-gerakan-presisi">Kontrol Servo &amp; PWM gerakan presisi via MQTT</a></li>
  <li><a href="/artikel/adc-esp32-sensor-analog-soil-moisture-ldr-mqtt">ADC ESP32: Soil Moisture &amp; LDR via MQTT</a></li>
  <li><a href="/artikel/esp8266-nodemcu-vs-esp32-kapan-pakai-upgrade">ESP8266 / NodeMCU vs ESP32</a></li>
  <li><a href="/artikel/sd-card-spi-esp32-logging-data-sensor-offline">SD Card &amp; SPI logging offline</a></li>
  <li><a href="/artikel/https-sertifikat-esp32-wificlientsecure-api-rest">HTTPS &amp; sertifikat ESP32 (WiFiClientSecure)</a></li>
  <li><strong>Smart Greenhouse capstone</strong> — artikel ini (penutup Seri 2)</li>
</ol>

<h2>FAQ — Pertanyaan Umum Greenhouse IoT</h2>
<dl>
  <dt>Apakah bisa pakai satu ESP32 untuk semua sensor dan relay?</dt>
  <dd>Ya untuk prototipe skala kecil — gateway utama di artikel ini sudah menggabungkan BME280 + soil + LDR. Untuk produksi, pisahkan node aktuator agar reset sensor tidak mematikan pompa secara tidak sengaja.</dd>
  <dt>Broker test.mosquitto.org cukup untuk greenhouse?</dt>
  <dd>Tidak untuk data produksi — pakai <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">broker sendiri (#16)</a>. Broker publik cocok hanya untuk uji coba 15 menit pertama.</dd>
  <dt>Soil moisture resistif vs capacitive?</dt>
  <dd>Capacitive lebih awet di tanah basah — sudah dibahas di <a href="/artikel/adc-esp32-sensor-analog-soil-moisture-ldr-mqtt">#35</a>. Probe resistif murah tapi korosi dalam berminggu-minggu.</dd>
  <dt>Berapa lama baterai node deep sleep?</dt>
  <dd>Dengan 18650 3000 mAh dan wake tiap 15 menit, estimasi 2–4 minggu — tergantung durasi WiFi connect. Optimasi: kurangi payload JSON, matikan LED board.</dd>
  <dt>Apakah Seri 2 berakhir setelah artikel ini?</dt>
  <dd>Ya — <strong>29/29 artikel inti selesai</strong>. Topik Fase 3 (MicroPython, GPRS, Zigbee) akan ditulis jika ada permintaan komunitas.</dd>
</dl>

<h2>Penutup — Terima Kasih Mengikuti Seri 2!</h2>
<p>Kamu sekarang punya fondasi lengkap IoT embedded: dari <strong>sensor digital &amp; analog</strong>, <strong>protokol MQTT &amp; HTTPS</strong>, <strong>broker &amp; dashboard</strong>, hingga <strong>smart home &amp; cloud</strong>. Proyek greenhouse ini bisa dikembangkan ke skala komersial — tambah <a href="/artikel/esp32-cam-streaming-mjpeg-capture-foto-wifi">kamera (#27)</a>, <a href="/artikel/gateway-lora-mqtt-esp32-sensor-jarak-jauh-dashboard">gateway LoRa (#28)</a>, atau <a href="/artikel/esp32-firebase-realtime-database-sensor-cloud">Firebase (#30)</a> untuk monitoring mobile.</p>

<blockquote>
  <p><strong>Seri 2 selesai — 29/29 artikel.</strong> Share proyek greenhouse kamu ke komunitas, dan pantau <a href="/artikel">artikel baru</a> di Koding Indonesia untuk topik Fase 3 (MicroPython, GPRS, Zigbee, dan lainnya).</p>
</blockquote>
HTML;
    }
}
