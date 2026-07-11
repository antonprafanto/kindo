<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article36Seeder extends Seeder
{
    public function run(): void
    {
        $admin  = User::first();
        $espCat = Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $espCat) {
            throw new \RuntimeException('User atau kategori tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'esp8266-nodemcu-vs-esp32-kapan-pakai-upgrade';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        Tag::updateOrCreate(['slug' => 'esp8266'], ['name' => 'esp8266']);
        Tag::updateOrCreate(['slug' => 'nodemcu'], ['name' => 'nodemcu']);

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $espCat->id,
                'title'           => 'ESP8266 & NodeMCU vs ESP32: Kapan Pakai, Kapan Upgrade',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'ESP8266 NodeMCU vs ESP32 — Panduan Pilih Board IoT',
                'seo_description' => 'Bandingkan ESP8266, NodeMCU, Wemos D1 Mini vs ESP32: GPIO, ADC, BLE, biaya & contoh MQTT — kapan pakai board murah dan kapan upgrade ke ESP32.',
            ]
        );

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', [
            'esp8266', 'esp32', 'nodemcu', 'iot', 'mqtt', 'wemos',
        ])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command->info('✓ Artikel ke-36 berhasil dipublish: ' . $article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — Dua Chip, Satu Ekosistem</h2>
<p>Di <a href="/artikel/mengenal-esp32-mikrokontroler-wifi-bluetooth-iot">artikel #1</a>, perbandingan <strong>ESP8266</strong> vs <strong>ESP32</strong> hanya disebut singkat. Seri ESP32 Koding Indonesia memang berfokus ke ESP32 — WiFi, MQTT, sensor, dan dashboard — tapi di lapangan banyak node murah masih pakai <strong>NodeMCU</strong> atau <strong>Wemos D1 Mini</strong>.</p>

<p>Artikel <strong>Tier 2</strong> ini menjawab pertanyaan praktis: <em>kapan ESP8266 cukup</em>, <em>kapan wajib upgrade ke ESP32</em>, dan bagaimana pola kode MQTT dari <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">#7</a> dipindah ke NodeMCU tanpa mengubah arsitektur broker <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Mosquitto (#16)</a>.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> Sudah paham <a href="/artikel/blink-led-esp32-tutorial-pertama-embedded-system">GPIO (#3)</a>, <a href="/artikel/menghubungkan-esp32-wifi-kirim-data-server">WiFi (#4)</a>, dan idealnya pernah publish MQTT dari <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">DHT22 (#5)</a>. Capstone <a href="/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22">#10</a> tetap referensi arsitektur hybrid.</p>
</blockquote>

<h2>Ringkasan Cepat — Pilih Board dalam 30 Detik</h2>
<table>
  <thead>
    <tr><th>Kebutuhan proyek</th><th>Pilih</th><th>Alasan</th></tr>
  </thead>
  <tbody>
    <tr><td>Sensor tunggal + MQTT murah</td><td><strong>ESP8266 / NodeMCU</strong></td><td>Harga board ~Rp 25–40 rb · cukup untuk DHT + publish</td></tr>
    <tr><td>Multi-sensor + ADC + servo</td><td><strong>ESP32</strong></td><td>GPIO &amp; ADC lebih banyak · <a href="/artikel/adc-esp32-sensor-analog-soil-moisture-ldr-mqtt">ADC (#35)</a> · <a href="/artikel/kontrol-servo-pwm-esp32-mqtt-gerakan-presisi">servo (#33)</a></td></tr>
    <tr><td>Bluetooth ke smartphone</td><td><strong>ESP32</strong></td><td>ESP8266 tidak punya BLE — lihat <a href="/artikel/bluetooth-esp32-ble-kirim-data-sensor-smartphone">#32</a></td></tr>
    <tr><td>Kamera / streaming</td><td><strong>ESP32-CAM</strong></td><td>Di luar scope ESP8266 — <a href="/artikel/esp32-cam-streaming-mjpeg-capture-foto-wifi">#27</a></td></tr>
    <tr><td>Peer-to-peer tanpa router</td><td><strong>ESP32 (ESP-NOW)</strong></td><td>ESP8266 support terbatas — <a href="/artikel/esp-now-kirim-data-antar-esp32-tanpa-router-wifi">#25</a></td></tr>
    <tr><td>Multi-task paralel</td><td><strong>ESP32 + FreeRTOS</strong></td><td>Dual-core — <a href="/artikel/freertos-esp32-multi-task-sensor-wifi-mqtt">#31</a></td></tr>
  </tbody>
</table>

<h2>Spesifikasi Teknis — ESP8266 vs ESP32</h2>
<table>
  <thead>
    <tr><th>Aspek</th><th>ESP8266 (NodeMCU)</th><th>ESP32 (DevKit)</th></tr>
  </thead>
  <tbody>
    <tr><td>CPU</td><td>Single-core ~160 MHz</td><td>Dual-core ~240 MHz</td></tr>
    <tr><td>WiFi</td><td>802.11 b/g/n 2,4 GHz</td><td>802.11 b/g/n 2,4 GHz</td></tr>
    <tr><td>Bluetooth</td><td>Tidak ada</td><td>BLE Classic + BLE</td></tr>
    <tr><td>GPIO usable</td><td>~11 (tergantung board)</td><td>~25+ (tergantung modul)</td></tr>
    <tr><td>ADC</td><td><strong>1 pin</strong> (A0, 0–1 V atau 0–3,3 V tergantung board)</td><td><strong>Banyak pin ADC1</strong> 12-bit — <a href="/artikel/adc-esp32-sensor-analog-soil-moisture-ldr-mqtt">#35</a></td></tr>
    <tr><td>Flash tipikal</td><td>4 MB</td><td>4–16 MB</td></tr>
    <tr><td>Deep sleep</td><td>Ya (~20 µA) — <a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">#11</a> pola serupa di ESP32</td><td>Ya, lebih fleksibel wake source</td></tr>
    <tr><td>Harga board (ID)</td><td>~Rp 25.000 – 40.000</td><td>~Rp 35.000 – 55.000</td></tr>
  </tbody>
</table>

<h2>NodeMCU v3 — Board Paling Umum</h2>
<p><strong>NodeMCU</strong> bukan chip — itu development board berbasis ESP-12E/ESP-12F dengan USB-UART CH340, regulator 3,3 V, dan label pin <code>D0</code>–<code>D8</code> plus <code>A0</code>.</p>
<ul>
  <li>LED built-in biasanya di <strong>GPIO2</strong> (label <code>D4</code> di NodeMCU)</li>
  <li><strong>A0</strong> = satu-satunya input analog</li>
  <li><strong>D3 (GPIO0)</strong> &amp; <strong>D8 (GPIO15)</strong> punya constraint boot — hindari pull saat flash</li>
  <li>Semua pin logika <strong>3,3 V</strong> — jangan hubungkan 5 V langsung ke GPIO</li>
</ul>

<blockquote>
  <p><strong>Pro tip:</strong> Di NodeMCU, nomor <code>D4</code> ≠ GPIO4. Selalu cek pinout: <code>D4</code> = GPIO2 untuk LED onboard.</p>
</blockquote>

<h2>Wemos D1 Mini — Alternatif Kompak</h2>
<p><strong>Wemos D1 Mini</strong> (tag <code>wemos</code>) punya footprint lebih kecil dari NodeMCU, cocok untuk node sensor tertutup di kotak kecil. Pinout mirip — tetap ESP8266, satu ADC, pola MQTT sama. Pilih NodeMCU untuk prototyping breadboard; D1 Mini untuk produk kecil.</p>

<h2>GPIO &amp; Pin — Perbedaan yang Sering Bikin Bug</h2>
<table>
  <thead>
    <tr><th>Fungsi</th><th>NodeMCU (ESP8266)</th><th>ESP32 DevKit</th></tr>
  </thead>
  <tbody>
    <tr><td>LED onboard</td><td>GPIO2 (<code>D4</code>)</td><td>GPIO2 (board umum)</td></tr>
    <tr><td>DHT22 (#5)</td><td>GPIO4 (<code>D2</code>) — hindari konflik boot</td><td>GPIO4</td></tr>
    <tr><td>Relay (#8)</td><td>GPIO5 (<code>D1</code>)</td><td>GPIO26</td></tr>
    <tr><td>Soil moisture analog</td><td>Hanya <strong>A0</strong> — pilih soil <em>atau</em> LDR</td><td>GPIO34 + GPIO35 bersamaan — <a href="/artikel/adc-esp32-sensor-analog-soil-moisture-ldr-mqtt">#35</a></td></tr>
    <tr><td>Servo PWM (#33)</td><td>Mungkin, tapi timing WiFi + servo rawan</td><td>GPIO27 + library <code>ESP32Servo</code></td></tr>
  </tbody>
</table>

<h2>ADC: Satu Pin vs Banyak</h2>
<p>ESP8266 hanya punya <strong>satu ADC</strong>. Untuk greenhouse dengan soil moisture <em>dan</em> LDR sekaligus, ESP32 jauh lebih nyaman. Di ESP8266, solusi workaround: multiplexer analog atau pakai sensor digital (BME280 via I2C di ESP32 — <a href="/artikel/i2c-esp32-sensor-bme280-suhu-tekanan-mqtt">#13</a>).</p>

<h2>WiFi &amp; Bluetooth</h2>
<p>Keduanya WiFi 2,4 GHz. ESP32 menambah <strong>Bluetooth BLE</strong> — berguna untuk provisioning atau kirim data ke app tanpa router (<a href="/artikel/bluetooth-esp32-ble-kirim-data-sensor-smartphone">#32</a>). ESP8266 tidak bisa menggantikan use case BLE.</p>

<h2>Dual-Core vs Single-Core</h2>
<p>ESP32 bisa pisahkan task sensor dan WiFi di core berbeda (<a href="/artikel/freertos-esp32-multi-task-sensor-wifi-mqtt">FreeRTOS #31</a>). ESP8266 single-core — <code>loop()</code> blocking lebih terasa; hindari <code>delay()</code> panjang saat MQTT aktif, sama seperti di ESP32.</p>

<h2>Konsumsi Daya &amp; Deep Sleep</h2>
<p>Untuk node baterai, ESP8266 dan ESP32 sama-sama bisa deep sleep. Pola wake + publish MQTT di <a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">#11</a> berlaku konseptual — API beda (<code>ESP.deepSleep()</code> vs <code>esp_deep_sleep_start()</code>), tapi arsitektur broker tetap.</p>

<h2>Kapan Pilih ESP8266 / NodeMCU</h2>
<ul>
  <li>Proyek belajar MQTT pertama dengan budget minimal</li>
  <li>Node sensor tunggal: DHT22 → broker → Grafana/Home Assistant</li>
  <li>Replika sketch <a href="/artikel/kontrol-lampu-esp32-mqtt-relay">relay #8</a> dengan biaya board lebih rendah</li>
  <li>Gateway kecil yang hanya forward JSON ke topic yang sama</li>
  <li>Stok board lama di rak — manfaatkan sebelum beli ESP32 baru</li>
</ul>

<h2>Kapan Upgrade ke ESP32</h2>
<ul>
  <li>Butuh <strong>2+ sensor analog</strong> atau kombinasi soil + LDR (<a href="/artikel/adc-esp32-sensor-analog-soil-moisture-ldr-mqtt">#35</a>)</li>
  <li>Servo presisi + WiFi stabil (<a href="/artikel/kontrol-servo-pwm-esp32-mqtt-gerakan-presisi">#33</a>)</li>
  <li>Timestamp akurat NTP di setiap payload (<a href="/artikel/ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt">#34</a>)</li>
  <li>OTA firmware tanpa kabel (<a href="/artikel/ota-update-firmware-esp32-via-wifi">#15</a>)</li>
  <li>Project <a href="/artikel/migrasi-platformio-esp32-vscode-project-rapi">PlatformIO (#29)</a> multi-target (esp32 + esp8266 dalam satu repo)</li>
  <li>Capstone <strong>greenhouse (#39)</strong> — multi-jalur sensor + aktuator</li>
</ul>

<h2>Setup Arduino IDE — Board Manager ESP8266</h2>
<p>Selain ESP32 Board Manager dari <a href="/artikel/cara-install-arduino-ide-setup-esp32-board-manager">#2</a>, tambahkan URL board ESP8266:</p>
<ol>
  <li>File → Preferences → <em>Additional boards manager URLs</em></li>
  <li>Tambah: <code>https://arduino.esp8266.com/stable/package_esp8266com_index.json</code></li>
  <li>Tools → Board → Boards Manager → install <strong>esp8266 by ESP8266 Community</strong></li>
  <li>Pilih board: <strong>NodeMCU 1.0 (ESP-12E Module)</strong> atau <strong>LOLIN(WEMOS) D1 R2 &amp; mini</strong></li>
</ol>

<h2>Blink LED di NodeMCU</h2>
<p>Setara <a href="/artikel/blink-led-esp32-tutorial-pertama-embedded-system">Blink #3</a>, tapi pin LED onboard = GPIO2:</p>
<pre><code class="language-cpp">#define LED_PIN 2  // GPIO2 = D4 di NodeMCU, active LOW

void setup() {
  pinMode(LED_PIN, OUTPUT);
}

void loop() {
  digitalWrite(LED_PIN, LOW);   // LED nyala (active low)
  delay(500);
  digitalWrite(LED_PIN, HIGH);  // LED mati
  delay(500);
}
</code></pre>

<h2>Contoh MQTT Publish di ESP8266</h2>
<p>Pola sama dengan <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">#7</a> — beda library include dan board. Topic dipisah namespace <code>esp8266</code> agar tidak bentrok dengan node ESP32 di broker yang sama:</p>
<pre><code class="language-cpp">#include &lt;ESP8266WiFi.h&gt;
#include &lt;PubSubClient.h&gt;
#include &lt;DHT.h&gt;

#define DHT_PIN  4       // D2 di NodeMCU
#define DHT_TYPE DHT22

const char* WIFI_SSID     = "GANTI_NAMA_WIFI";
const char* WIFI_PASSWORD = "GANTI_PASSWORD_WIFI";
const char* MQTT_BROKER   = "192.168.1.50";
const int   MQTT_PORT     = 1883;
const char* MQTT_USER     = "kindo_esp32";
const char* MQTT_PASS     = "GANTI_PASSWORD_MQTT";
const char* TOPIC_DHT     = "kodingindonesia/esp8266/dht22/data";

DHT dht(DHT_PIN, DHT_TYPE);
WiFiClient espClient;
PubSubClient mqtt(espClient);

void connectWiFi() {
  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
  }
}

void connectMqtt() {
  while (!mqtt.connected()) {
    if (mqtt.connect("nodemcu-dht22", MQTT_USER, MQTT_PASS)) {
      // connected
    } else {
      delay(5000);
    }
  }
}

void setup() {
  Serial.begin(115200);
  dht.begin();
  delay(2000);
  connectWiFi();
  mqtt.setServer(MQTT_BROKER, MQTT_PORT);
  mqtt.setBufferSize(256);
}

void loop() {
  if (!mqtt.connected()) {
    connectMqtt();
  }
  mqtt.loop();

  float t = dht.readTemperature();
  float h = dht.readHumidity();
  if (!isnan(t) &amp;&amp; !isnan(h)) {
    char payload[128];
    snprintf(payload, sizeof(payload),
      "{\"device\":\"nodemcu\",\"temp\":%.1f,\"hum\":%.1f}",
      t, h);
    mqtt.publish(TOPIC_DHT, payload);
  }
  delay(10000);
}
</code></pre>

<p>Verifikasi di laptop:</p>
<pre><code class="language-bash">mosquitto_sub -h 192.168.1.50 -u kindo_esp32 -P GANTI_PASSWORD_MQTT -t "kodingindonesia/esp8266/#" -v
</code></pre>

<h2>Migrasi Sketch ESP8266 → ESP32</h2>
<table>
  <thead>
    <tr><th>Bagian</th><th>ESP8266</th><th>ESP32</th></tr>
  </thead>
  <tbody>
    <tr><td>WiFi include</td><td><code>#include &lt;ESP8266WiFi.h&gt;</code></td><td><code>#include &lt;WiFi.h&gt;</code></td></tr>
    <tr><td>MQTT client</td><td><code>PubSubClient</code> + <code>WiFiClient</code></td><td>Sama</td></tr>
    <tr><td>Topic</td><td><code>kodingindonesia/esp8266/...</code></td><td><code>kodingindonesia/esp32/...</code></td></tr>
    <tr><td>GPIO LED</td><td>GPIO2 active LOW</td><td>GPIO2 (cek board)</td></tr>
    <tr><td>Timestamp JSON</td><td>Opsional manual</td><td><a href="/artikel/ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt">NTP #34</a> — contoh unix <code>1782977400</code></td></tr>
  </tbody>
</table>

<p>Broker <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Mosquitto (#16)</a>, subscriber <a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">Python (#18)</a>, dan dashboard <a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">Grafana (#19)</a> <strong>tidak perlu diganti</strong> — cukup subscribe wildcard <code>kodingindonesia/#</code> atau topic per device.</p>

<h2>PlatformIO — Dua Target Satu Repo</h2>
<p>Di <a href="/artikel/migrasi-platformio-esp32-vscode-project-rapi">PlatformIO (#29)</a>, tambah environment kedua:</p>
<pre><code class="language-ini">[env:nodemcu]
platform = espressif8266
board = nodemcuv2
framework = arduino
lib_deps =
    knolleary/PubSubClient@^2.8
    adafruit/DHT sensor library@^1.4

[env:esp32dev]
platform = espressif32
board = esp32dev
framework = arduino
lib_deps =
    knolleary/PubSubClient@^2.8
    adafruit/DHT sensor library@^1.4
</code></pre>

<h2>Node-RED &amp; Home Assistant</h2>
<p>Node <a href="/artikel/node-red-dashboard-otomasi-iot-mqtt-esp32">Node-RED (#23)</a> dan <a href="/artikel/home-assistant-integrasi-esp32-mqtt">Home Assistant (#21)</a> tidak peduli chip asal topic MQTT konsisten. Pisahkan prefix <code>esp8266</code> vs <code>esp32</code> di flow agar entitas tidak bentrok.</p>

<h2>Memori Flash &amp; Library</h2>
<p>ESP8266 modul umum punya <strong>4 MB flash</strong> — cukup untuk firmware Arduino + OTA kecil. ESP32 sering 4–16 MB dan lebih nyaman jika kamu menambah banyak library (Grafana stack di sisi server, bukan di chip). Untuk sketch MQTT + DHT22, keduanya longgar; perbedaan terasa saat project <a href="/artikel/migrasi-platformio-esp32-vscode-project-rapi">PlatformIO (#29)</a> menarik puluhan dependency.</p>
<p>Library <code>PubSubClient</code> dan <code>DHT sensor library</code> sama di kedua platform — yang berubah hanya header WiFi dan nomor pin. Itu memudahkan maintain dua versi node (murah di ruang tamu, ESP32 di greenhouse) tanpa mengubah topic schema di broker.</p>

<h2>OTA &amp; Maintenance di Lapangan</h2>
<p>ESP32 punya tutorial <a href="/artikel/ota-update-firmware-esp32-via-wifi">OTA (#15)</a> yang matang — upload firmware baru tanpa USB. ESP8266 juga support OTA, tapi ekosistem dan dokumentasi di komunitas Indonesia lebih condong ke ESP32 untuk produk jangka panjang. Jika node ESP8266 terpasang di langit-langit dan sulit dijangkau USB, rencanakan OTA sejak awal atau pilih ESP32.</p>
<p>Untuk prototipe di meja kerja, flash ulang NodeMCU via USB sama mudahnya dengan ESP32 — bottleneck biasanya driver CH340, bukan chip.</p>

<h2>Contoh Arsitektur Hybrid di Rumah</h2>
<p>Pola yang sering bekerja di proyek nyata:</p>
<ul>
  <li><strong>3× NodeMCU</strong> — DHT22 di kamar, relay lampu koridor, tombol virtual MQTT (biaya rendah)</li>
  <li><strong>1× ESP32</strong> — gateway utama: web server lokal + subscribe semua topic + tampilkan di OLED <a href="/artikel/oled-ssd1306-esp32-tampilkan-data-sensor-i2c">#14</a></li>
  <li><strong>Broker Mosquitto</strong> di Raspberry Pi (<a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">#16</a>) — satu titik kebenaran data</li>
  <li><strong>Subscriber Python → MySQL</strong> (<a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">#18</a>) — histori tanpa peduli chip pengirim</li>
</ul>
<p>Subscriber tidak memeriksa apakah payload datang dari <code>esp8266</code> atau <code>esp32</code> — cukup parse JSON field <code>device</code> untuk label di Grafana (<a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">#19</a>).</p>

<h2>Subscriber Python — Node Campuran</h2>
<p>Jika kamu sudah menjalankan subscriber dari <a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">#18</a>, tidak perlu script baru untuk ESP8266. Subscribe wildcard:</p>
<pre><code class="language-bash">mosquitto_sub -h 192.168.1.50 -u kindo_esp32 -P GANTI_PASSWORD_MQTT -t "kodingindonesia/#" -v
</code></pre>
<p>Di callback Python, cabang berdasarkan prefix topic atau field <code>device</code> di JSON. Pola ini mempersiapkan capstone <strong>greenhouse (#39)</strong> di mana node murah dan node kaya coexist.</p>

<h2>Keamanan &amp; Produksi</h2>
<ul>
  <li>Jangan commit password WiFi/MQTT — pakai placeholder <code>GANTI_*</code></li>
  <li>Produksi: TLS di broker — <a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">MQTT TLS (#17)</a></li>
  <li>Firmware ESP8266 tidak update OTA semudah ESP32 — pertimbangkan akses fisik untuk flash ulang</li>
  <li>GPIO boot (GPIO0/GPIO15) — pastikan relay tidak tarik pin saat reset</li>
</ul>

<h2>Estimasi Biaya (Indonesia)</h2>
<table>
  <thead>
    <tr><th>Komponen</th><th>ESP8266</th><th>ESP32</th></tr>
  </thead>
  <tbody>
    <tr><td>Board dev</td><td>25.000 – 40.000</td><td>35.000 – 55.000</td></tr>
    <tr><td>DHT22</td><td>35.000 – 55.000</td><td>35.000 – 55.000</td></tr>
    <tr><td>Modul relay</td><td>12.000 – 20.000</td><td>12.000 – 20.000</td></tr>
    <tr><td><strong>Total node minimal</strong></td><td><strong>~72.000 – 115.000</strong></td><td><strong>~82.000 – 130.000</strong></td></tr>
  </tbody>
</table>
<p>Selisih board kecil — hemat ESP8266 terasa saat deploy puluhan node identik.</p>

<h2>Checklist Sebelum Demo</h2>
<ul>
  <li>☐ Board Manager ESP8266 terinstall · port COM benar</li>
  <li>☐ Blink GPIO2 (LED onboard) berkedip</li>
  <li>☐ WiFi connect · Serial tampilkan IP</li>
  <li>☐ MQTT publish ke <code>kodingindonesia/esp8266/dht22/data</code></li>
  <li>☐ <code>mosquitto_sub</code> terima JSON valid</li>
  <li>☐ Topic tidak bentrok dengan node ESP32 di broker yang sama</li>
</ul>

<h2>FAQ Singkat</h2>
<dl>
  <dt><strong>NodeMCU sama dengan ESP8266?</strong></dt>
  <dd>NodeMCU adalah <em>board</em> development; chip di dalamnya ESP8266 (biasanya ESP-12E).</dd>
  <dt><strong>Bisa ikut tutorial ESP32 pakai NodeMCU?</strong></dt>
  <dd>MQTT, DHT, relay — ya, dengan penyesuaian include WiFi dan GPIO. ADC multi-pin, BLE, servo presisi — tidak.</dd>
  <dt><strong>ESP8266 support ESP-NOW?</strong></dt>
  <dd>Ada dukungan terbatas — untuk mesh peer-to-peer serius pakai ESP32 (<a href="/artikel/esp-now-kirim-data-antar-esp32-tanpa-router-wifi">#25</a>).</dd>
  <dt><strong>FreeRTOS di ESP8266?</strong></dt>
  <dd>ESP8266 SDK punya tasking terbatas; untuk pola multi-task penuh gunakan ESP32 (<a href="/artikel/freertos-esp32-multi-task-sensor-wifi-mqtt">#31</a>).</dd>
  <dt><strong>Wemos vs NodeMCU?</strong></dt>
  <dd>Chip sama — beda ukuran board dan pinout label. Pilih sesuai enclosure proyek.</dd>
</dl>

<h2>Tips &amp; Troubleshooting</h2>
<ul>
  <li><strong>Upload gagal "timed out":</strong> Tahan FLASH/BOOT saat upload · ganti kabel USB data · turunkan upload speed</li>
  <li><strong>LED blink terbalik:</strong> NodeMCU LED active LOW — LOW = nyala</li>
  <li><strong>MQTT rc=-4:</strong> Buffer kecil — <code>mqtt.setBufferSize(256)</code></li>
  <li><strong>DHT NaN:</strong> Pull-up 10kΩ · <code>delay(2000)</code> setelah <code>dht.begin()</code> — sama seperti <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">#5</a></li>
  <li><strong>WiFi connect lambat:</strong> Cek 2,4 GHz — ESP8266 tidak support 5 GHz</li>
  <li><strong>Reset loop saat relay aktif:</strong> Relay tarik GPIO0/GPIO15 — pindah pin atau isolasi dengan optocoupler</li>
</ul>

<h2>Langkah Selanjutnya — Tier 2 Seri 2</h2>
<p>Setelah paham kapan pakai ESP8266 vs ESP32, lanjut pelengkap Tier 2:</p>
<ul>
  <li><strong>SD Card &amp; SPI logging offline (#37):</strong> backup data sensor di lapangan tanpa WiFi</li>
  <li><strong><a href="/artikel/adc-esp32-sensor-analog-soil-moisture-ldr-mqtt">ADC (#35)</a></strong> — alasan upgrade ke ESP32 untuk multi-sensor analog</li>
  <li><strong><a href="/artikel/ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt">NTP (#34)</a></strong> — timestamp ISO <code>2026-07-02T14:30:00</code> di payload ESP32</li>
  <li><strong>Keamanan HTTPS (#38)</strong> — sertifikat untuk HTTP client ESP32</li>
  <li>Capstone <strong>greenhouse (#39)</strong> — gabung multi-sensor + aktuator + dashboard</li>
</ul>

<p>Pilih board yang tepat, jangan over-engineer — lanjutkan di <a href="/artikel">halaman artikel</a> Koding Indonesia.</p>
HTML;
    }
}
