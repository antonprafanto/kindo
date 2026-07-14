<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article25Seeder extends Seeder
{
    public function run(): void
    {
        $admin  = User::first();
        $espCat = Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $espCat) {
            throw new \RuntimeException('User atau kategori tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'esp-now-kirim-data-antar-esp32-tanpa-router-wifi';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $espCat->id,
                'title'           => 'ESP-NOW: Kirim Data Antar ESP32 Tanpa Router WiFi',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'ESP-NOW ESP32 — Komunikasi Peer-to-Peer Tanpa Router',
                'seo_description' => 'Tutorial ESP-NOW: kirim data sensor antar dua ESP32 tanpa router WiFi. Pola sensor + gateway MQTT, struct data, MAC pairing, dan kapan pakai ESP-NOW vs MQTT.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        Tag::updateOrCreate(['slug' => 'esp-now'], ['name' => 'esp-now']);

        $tagIds = Tag::whereIn('slug', [
            'esp32', 'esp-now', 'iot', 'wifi', 'sensor', 'mqtt',
        ])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command->info('✓ Artikel ke-25 berhasil dipublish: ' . $article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — Sensor Tanpa Router di Tengah</h2>
<p>Sejauh ini, hampir semua artikel Seri 2 mengasumsikan <strong>router WiFi</strong> ada: ESP32 connect ke AP, lalu HTTP (<a href="/artikel/membuat-web-server-esp32-monitoring-sensor-dht22">#6</a>), MQTT (<a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">#7</a>), atau broker (<a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">#16</a>).</p>

<p>Di lapangan, tidak selalu demikian. Greenhouse besar, gudang, atau kebun punya <strong>node sensor di ujung</strong> yang jauh dari router — sinyal WiFi lemah, tapi jarak antar board ESP32 dekat. Di sinilah <strong>ESP-NOW</strong> masuk: protokol <strong>peer-to-peer</strong> Espressif untuk kirim paket kecil antar ESP32 <strong>tanpa router</strong>.</p>

<p>Artikel <strong>Jalur D</strong> ini membahas pola paling umum: <strong>sensor node</strong> (ESP-NOW only) → <strong>gateway node</strong> (ESP-NOW + WiFi + MQTT) → broker → <a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">Grafana (#19)</a>. Ini pola <strong>sensor + gateway</strong> klasik untuk IoT lapangan.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> Paham <a href="/artikel/menghubungkan-esp32-wifi-kirim-data-server">koneksi WiFi ESP32 (#4)</a>, <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">DHT22 (#5)</a>, dan dasar <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">MQTT (#7)</a>. Disarankan baca <a href="/artikel/rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32">REST vs MQTT (#20)</a> untuk konteks kapan butuh infrastruktur penuh.</p>
</blockquote>

<h2>Apa Itu ESP-NOW?</h2>
<p><strong>ESP-NOW</strong> adalah protokol wireless proprietary Espressif, built-in di chip ESP32/ESP8266. Karakteristik utama:</p>
<ul>
  <li><strong>Tanpa router</strong> — komunikasi langsung antar MAC address</li>
  <li><strong>Payload kecil</strong> — maksimal ~250 byte per paket</li>
  <li><strong>Latency rendah</strong> — cocok untuk trigger, remote control, telemetry ringan</li>
  <li><strong>Channel WiFi</strong> — kedua board harus di channel yang sama (1–13), meski tidak connect ke AP</li>
  <li><strong>Enkripsi opsional</strong> — PMK/LMK untuk peer terenkripsi</li>
</ul>

<h2>ESP-NOW vs WiFi/MQTT vs LoRa</h2>
<table>
  <thead>
    <tr><th>Protokol</th><th>Jangkau</th><th>Infrastruktur</th><th>Cocok untuk</th></tr>
  </thead>
  <tbody>
    <tr><td><strong>ESP-NOW</strong></td><td>~200 m LOS (tergantung antena)</td><td>2+ ESP32, tanpa router</td><td>Sensor lokal → gateway dekat</td></tr>
    <tr><td><strong><a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">WiFi + MQTT (#7)</a></strong></td><td>Seluruh jaringan AP</td><td>Router + <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">broker (#16)</a></td><td>Dashboard, multi-subscriber</td></tr>
    <tr><td><strong><a href="/artikel/membuat-web-server-esp32-monitoring-sensor-dht22">REST (#6)</a></strong></td><td>LAN AP</td><td>Router</td><td>Debug cepat, satu klien</td></tr>
    <tr><td><strong><a href="/artikel/lora-esp32-modul-sx1278-kirim-data-jarak-jauh">LoRa (#26)</a></strong></td><td>Kilometer</td><td>2× ESP32 + modul SX1278</td><td>Sensor sangat jauh, data jarang</td></tr>
  </tbody>
</table>

<p>ESP-NOW <strong>bukan pengganti MQTT</strong> — ia mengisi celah antara node sensor dan gateway yang punya WiFi. Lihat juga perbandingan di <a href="/artikel/rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32">#20</a>.</p>

<h2>Yang Kamu Butuhkan</h2>
<ul>
  <li><strong>2× ESP32</strong> (DevKit v1 atau serupa) — satu sensor, satu gateway</li>
  <li><strong>DHT22</strong> di node sensor (opsional BME280 dari <a href="/artikel/i2c-esp32-sensor-bme280-suhu-tekanan-mqtt">#13</a>)</li>
  <li><strong>Router WiFi</strong> hanya di <em>gateway</em> — untuk forward ke MQTT</li>
  <li>Kabel USB ×2 untuk flash &amp; baca MAC address</li>
</ul>

<p><strong>Estimasi biaya:</strong> ~Rp 80–120rb per ESP32 + sensor — total ~Rp 200–250rb untuk lab dua node.</p>

<h2>Arsitektur Sensor + Gateway</h2>
<table>
  <thead>
    <tr><th>Komponen</th><th>Peran</th><th>Koneksi</th></tr>
  </thead>
  <tbody>
    <tr><td><strong>ESP32 Sensor</strong> + DHT22</td><td>Baca suhu &amp; kelembaban, kirim paket kecil</td><td><strong>ESP-NOW</strong> saja — <em>tanpa</em> router WiFi</td></tr>
    <tr><td><strong>ESP32 Gateway</strong></td><td>Terima paket ESP-NOW, publish ke MQTT</td><td>ESP-NOW + <strong>WiFi STA</strong> ke router</td></tr>
    <tr><td><strong>Mosquitto</strong> (<a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">#16</a>)</td><td>Broker pusat</td><td>Topic <code>kodingindonesia/esp32/dht22/data</code></td></tr>
    <tr><td><strong>Grafana</strong> (<a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">#19</a>)</td><td>Dashboard histori (opsional)</td><td>Subscribe / Telegraf dari broker</td></tr>
  </tbody>
</table>

<p>Alur data secara singkat:</p>
<figure role="img" aria-label="Diagram ESP-NOW sensor ke gateway: sensor kirim paket peer-to-peer ke gateway ESP32, gateway publish MQTT ke Mosquitto, lalu Grafana dan subscriber Python" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 900 360" style="display:block;max-width:900px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="enArrow" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto">
      <path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/>
    </marker>
    <marker id="enArrowOrange" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto">
      <path d="M0,0 L8,4 L0,8 Z" fill="#FF7A2F"/>
    </marker>
    <marker id="enArrowGreen" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto">
      <path d="M0,0 L8,4 L0,8 Z" fill="#2E7D32"/>
    </marker>
  </defs>
  <rect x="0" y="0" width="900" height="360" fill="#F5F5F0" rx="6"/>
  <!-- Sensor → Gateway (sensor kirim ESP-NOW) -->
  <line x1="248" y1="95" x2="318" y2="95" stroke="#FF7A2F" stroke-width="2.5" marker-end="url(#enArrowOrange)"/>
  <!-- Gateway → Mosquitto (gateway publish MQTT) -->
  <line x1="568" y1="95" x2="638" y2="95" stroke="#2979FF" stroke-width="2.5" marker-end="url(#enArrow)"/>
  <!-- Mosquitto → Dashboard (broker push ke subscriber) -->
  <line x1="760" y1="148" x2="760" y2="208" stroke="#2E7D32" stroke-width="2.5" marker-end="url(#enArrowGreen)"/>
  <!-- Sensor -->
  <rect x="24" y="40" width="224" height="110" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="136" y="72" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">ESP32 Sensor</text>
  <text x="136" y="94" text-anchor="middle" fill="#4A5568" font-size="12">DHT22 · deep sleep</text>
  <text x="136" y="114" text-anchor="middle" fill="#718096" font-size="11">tanpa router WiFi</text>
  <text x="136" y="134" text-anchor="middle" fill="#718096" font-size="11">esp_now_send()</text>
  <rect x="255" y="56" width="78" height="24" rx="4" fill="#fff" stroke="#FF7A2F" stroke-width="1.5"/>
  <text x="294" y="73" text-anchor="middle" fill="#FF7A2F" font-size="11" font-weight="700">ESP-NOW →</text>
  <!-- Gateway -->
  <rect x="328" y="40" width="240" height="110" rx="6" fill="#FFF3E8" stroke="#000" stroke-width="2.5"/>
  <text x="448" y="72" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">ESP32 Gateway</text>
  <text x="448" y="94" text-anchor="middle" fill="#4A5568" font-size="12">ESP-NOW RX + WiFi STA</text>
  <text x="448" y="114" text-anchor="middle" fill="#718096" font-size="11">PubSubClient publish</text>
  <text x="448" y="134" text-anchor="middle" fill="#718096" font-size="11">topic Seri 2</text>
  <rect x="575" y="56" width="72" height="24" rx="4" fill="#fff" stroke="#2979FF" stroke-width="1.5"/>
  <text x="611" y="73" text-anchor="middle" fill="#2979FF" font-size="11" font-weight="700">MQTT →</text>
  <!-- Mosquitto -->
  <rect x="648" y="40" width="224" height="108" rx="6" fill="#2979FF" stroke="#000" stroke-width="2.5"/>
  <text x="760" y="72" text-anchor="middle" fill="#fff" font-size="14" font-weight="700">Broker Mosquitto</text>
  <text x="760" y="94" text-anchor="middle" fill="#e3f2fd" font-size="12">192.168.1.50:1883</text>
  <text x="760" y="114" text-anchor="middle" fill="#cfe4ff" font-size="11">user kindo_esp32</text>
  <rect x="780" y="166" width="100" height="24" rx="4" fill="#fff" stroke="#2E7D32" stroke-width="1.5"/>
  <text x="830" y="183" text-anchor="middle" fill="#2E7D32" font-size="12" font-weight="700">data ↓</text>
  <!-- Dashboard -->
  <rect x="548" y="218" width="320" height="90" rx="6" fill="#F5F5F0" stroke="#000" stroke-width="2.5"/>
  <text x="708" y="248" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">Subscriber / Dashboard</text>
  <text x="708" y="270" text-anchor="middle" fill="#4A5568" font-size="12">mosquitto_sub · Python · Grafana</text>
  <text x="708" y="290" text-anchor="middle" fill="#718096" font-size="11">histori + otomasi dari topic MQTT</text>
  <text x="450" y="332" text-anchor="middle" fill="#4A5568" font-size="11">Alur: sensor kirim ESP-NOW → gateway publish MQTT → broker → dashboard subscribe</text>
</svg>
<figcaption style="margin-top:.75rem;font-size:.875rem;color:#4A5568;text-align:center">Diagram ESP-NOW sensor + gateway — sensor kirim paket ke gateway tanpa router; gateway forward JSON ke Mosquitto; <a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">Python (#18)</a> / <a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">Grafana (#19)</a> subscribe dari broker.</figcaption>
</figure>

<p>Sensor node hemat daya: bangun → baca sensor → kirim ESP-NOW → tidur (<a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">deep sleep #11</a>). Gateway selalu hidup, terima paket, publish MQTT dengan timestamp <a href="/artikel/ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt">NTP (#34)</a>.</p>

<h2>MAC Address &amp; Pairing</h2>
<p>Setiap ESP32 punya <strong>MAC address WiFi STA</strong> unik. Wajib dicatat sebelum pairing:</p>
<pre><code class="language-cpp">#include &lt;WiFi.h&gt;

void setup() {
  Serial.begin(115200);
  WiFi.mode(WIFI_STA);
  Serial.print("MAC STA: ");
  Serial.println(WiFi.macAddress());
}</code></pre>

<p>Flash sketch di atas ke <strong>kedua</strong> board — catat MAC gateway, misalnya <code>24:6F:28:AA:BB:02</code>, dan MAC sensor <code>24:6F:28:AA:BB:01</code>. Ganti placeholder di sketch berikut dengan nilai nyata dari Serial Monitor.</p>

<blockquote>
  <p><strong>Pro tip:</strong> Tempel label fisik di tiap board (SENSOR / GATEWAY + MAC) agar tidak tertukar saat debug.</p>
</blockquote>

<h2>Struktur Data Bersama</h2>
<p>Pakai <code>struct</code> yang sama di sender dan receiver — maksimal 250 byte:</p>
<pre><code class="language-cpp">typedef struct __attribute__((packed)) {
  float suhu;
  float kelembaban;
  uint32_t unix;  // opsional dari RTC/NTP di sensor; gateway bisa isi ulang
} sensor_packet_t;</code></pre>

<p>Contoh nilai setelah gateway publish MQTT (konsisten Seri 2):</p>
<pre><code>{"suhu":28.5,"kelembaban":65.2,"timestamp":"2026-07-02T14:30:00","unix":1782977400}</code></pre>

<h2>Sketch Sensor — ESP-NOW Sender</h2>
<pre><code class="language-cpp">#include &lt;esp_now.h&gt;
#include &lt;WiFi.h&gt;
#include &lt;DHT.h&gt;

#define DHT_PIN 4
#define GATEWAY_MAC {0x24, 0x6F, 0x28, 0xAA, 0xBB, 0x02}  // GANTI_MAC_GATEWAY

DHT dht(DHT_PIN, DHT22);

typedef struct __attribute__((packed)) {
  float suhu;
  float kelembaban;
  uint32_t unix;
} sensor_packet_t;

uint8_t gatewayMac[] = GATEWAY_MAC;

void onSent(const uint8_t* mac, esp_now_send_status_t status) {
  Serial.println(status == ESP_NOW_SEND_SUCCESS ? "ESP-NOW OK" : "ESP-NOW GAGAL");
}

void setup() {
  Serial.begin(115200);
  WiFi.mode(WIFI_STA);
  dht.begin();

  if (esp_now_init() != ESP_OK) {
    Serial.println("esp_now_init gagal");
    return;
  }
  esp_now_register_send_cb(onSent);

  esp_now_peer_info_t peer = {};
  memcpy(peer.peer_addr, gatewayMac, 6);
  peer.channel = 1;
  peer.encrypt = false;
  esp_now_add_peer(&amp;peer);
}

void loop() {
  sensor_packet_t pkt;
  pkt.suhu = dht.readTemperature();
  pkt.kelembaban = dht.readHumidity();
  pkt.unix = 1782977400;  // production: NTP (#34)

  esp_now_send(gatewayMac, (uint8_t*)&amp;pkt, sizeof(pkt));
  delay(5000);
}</code></pre>

<h2>Sketch Gateway — ESP-NOW Receiver + MQTT</h2>
<p>Gateway: terima ESP-NOW, lalu publish ke broker <code>192.168.1.50</code> topic <code>kodingindonesia/esp32/dht22/data</code> — pola <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">#7</a> + auth <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">#16</a>.</p>
<pre><code class="language-cpp">#include &lt;esp_now.h&gt;
#include &lt;WiFi.h&gt;
#include &lt;PubSubClient.h&gt;

const char* ssid = "GANTI_NAMA_WIFI";
const char* pass = "GANTI_PASSWORD_WIFI";
const char* mqtt_host = "192.168.1.50";
const char* mqtt_user = "kindo_esp32";
const char* mqtt_pass = "GANTI_PASSWORD_MQTT";
const char* topic = "kodingindonesia/esp32/dht22/data";

WiFiClient wifiClient;
PubSubClient mqtt(wifiClient);

typedef struct __attribute__((packed)) {
  float suhu;
  float kelembaban;
  uint32_t unix;
} sensor_packet_t;

void onRecv(const uint8_t* mac, const uint8_t* data, int len) {
  if (len != sizeof(sensor_packet_t)) return;
  sensor_packet_t pkt;
  memcpy(&amp;pkt, data, sizeof(pkt));

  char json[128];
  snprintf(json, sizeof(json),
    "{\"suhu\":%.1f,\"kelembaban\":%.1f,\"unix\":%lu}",
    pkt.suhu, pkt.kelembaban, (unsigned long)pkt.unix);
  mqtt.publish(topic, json);
  Serial.println(json);
}

void setup() {
  Serial.begin(115200);
  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, pass);
  while (WiFi.status() != WL_CONNECTED) { delay(300); }

  mqtt.setServer(mqtt_host, 1883);
  mqtt.connect("kindo-espnow-gw", mqtt_user, mqtt_pass);

  if (esp_now_init() != ESP_OK) return;
  esp_now_register_recv_cb(onRecv);
}

void loop() {
  if (!mqtt.connected()) mqtt.connect("kindo-espnow-gw", mqtt_user, mqtt_pass);
  mqtt.loop();
}</code></pre>

<h2>Multi-Node &amp; Topologi Star</h2>
<p>Satu gateway bisa menerima data dari <strong>banyak sensor</strong> ESP-NOW. Pola umum di greenhouse atau gudang:</p>
<ul>
  <li><strong>Star topology</strong> — semua sensor kirim ke satu gateway; gateway publish MQTT dengan field <code>node_id</code> di JSON</li>
  <li><strong>Daftar peer</strong> — di gateway, panggil <code>esp_now_add_peer</code> untuk setiap MAC sensor</li>
  <li><strong>Di sensor</strong> — cukup satu peer (MAC gateway); tidak perlu tahu MAC sensor lain</li>
</ul>
<p>Contoh payload gateway dengan identitas node:</p>
<pre><code>{"node":"greenhouse-1","suhu":28.5,"kelembaban":65.2,"unix":1782977400}</code></pre>
<p>Topic MQTT bisa diperluas: <code>kodingindonesia/esp32/greenhouse-1/dht22/data</code> — pola hierarki sama dengan artikel <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">#7</a>. <a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">Subscriber Python (#18)</a> atau <a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">Telegraf/Grafana (#19)</a> bisa filter per node.</p>

<blockquote>
  <p><strong>Pro tip:</strong> Batasi jumlah peer aktif (~20 pada ESP32) dan interval kirim agar gateway tidak kelebihan beban CPU saat WiFi + MQTT + ESP-NOW berjalan bersamaan.</p>
</blockquote>

<h2>Debugging ESP-NOW di Serial Monitor</h2>
<p>Saat pertama kali setup, aktifkan log verbose di kedua board:</p>
<ol>
  <li>Print MAC STA di <code>setup()</code> — wajib sebelum pairing</li>
  <li>Di sender: pantau callback <code>onSent</code> — <code>ESP_NOW_SEND_SUCCESS</code> vs gagal</li>
  <li>Di gateway: print panjang paket di <code>onRecv</code> — harus sama dengan <code>sizeof(sensor_packet_t)</code></li>
  <li>Cek <code>WiFi.channel()</code> di gateway setelah connect — samakan di <code>peer.channel</code> sensor</li>
  <li>Matikan power save WiFi sementara jika paket hilang acak: <code>WiFi.setSleep(false)</code></li>
</ol>
<p>Jika MQTT publish OK tapi ESP-NOW gagal, masalah ada di radio layer — jangan buru-buru ganti broker atau sketch MQTT.</p>

<h2>Channel WiFi &amp; Koeksistensi</h2>
<p>ESP-NOW memakai <strong>channel radio</strong> yang sama dengan interface WiFi STA. Aturan praktis:</p>
<ul>
  <li>Set <code>peer.channel = 1</code> di kedua sisi — atau samakan dengan channel AP gateway</li>
  <li>Gateway yang connect ke router: channel mengikuti AP — sensor harus set channel yang sama</li>
  <li>Gunakan <code>WiFi.channel()</code> di gateway, print ke Serial, lalu hardcode di sensor (atau simpan di <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">NVS #12</a>)</li>
</ul>

<h2>Deep Sleep + ESP-NOW di Node Sensor</h2>
<p>Kombinasi ideal untuk baterai: sensor tidak perlu maintain WiFi association — cukup <code>WiFi.mode(WIFI_STA)</code>, init ESP-NOW, kirim, lalu <code>esp_deep_sleep_start()</code>. Pola detail di <a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">#11</a>.</p>

<blockquote>
  <p><strong>Pro tip:</strong> Setelah <code>esp_deep_sleep</code>, ESP32 reboot — init ESP-NOW ulang di <code>setup()</code> setiap bangun.</p>
</blockquote>

<h2>Keamanan ESP-NOW</h2>
<ul>
  <li>Default <code>encrypt = false</code> — OK untuk lab dalam ruangan</li>
  <li>Produksi: set <code>peer.encrypt = true</code> + PMK (Primary Master Key) 16 byte sama di kedua firmware</li>
  <li>Jangan broadcast MAC ke publik jika sistem kontrol (relay/pintu) — whitelist peer saja</li>
  <li>Gateway tetap amankan MQTT dengan TLS <a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">#17</a> jika ke internet</li>
</ul>

<h2>Integrasi Dashboard &amp; Histori</h2>
<p>Setelah gateway publish MQTT, alur sama dengan artikel sebelumnya:</p>
<ul>
  <li><a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">Python → MySQL (#18)</a></li>
  <li><a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">InfluxDB + Grafana (#19)</a></li>
  <li><a href="/artikel/home-assistant-integrasi-esp32-mqtt">Home Assistant (#21)</a></li>
</ul>

<p>ESP-NOW hanya menggantikan <strong>hop pertama</strong> (sensor → gateway); selebihnya stack MQTT biasa.</p>

<h2>Checklist: Kapan Pakai ESP-NOW?</h2>
<ol>
  <li>Sensor jauh dari router, tapi dekat ke ESP32 lain? → <strong>ESP-NOW</strong></li>
  <li>Butuh kirim &lt; 250 byte, interval pendek? → <strong>ESP-NOW</strong></li>
  <li>Butuh jangkau kilometer? → <strong><a href="/artikel/lora-esp32-modul-sx1278-kirim-data-jarak-jauh">LoRa (#26)</a></strong>, bukan ESP-NOW</li>
  <li>Butuh banyak subscriber langsung dari sensor? → <strong><a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">MQTT (#7)</a></strong></li>
  <li>Node baterai + gateway solar/USB? → <strong>ESP-NOW sensor + MQTT gateway</strong></li>
</ol>

<h2>Uji Coba (Lab)</h2>
<pre><code class="language-bash"># 1. Catat MAC kedua board dari Serial Monitor
# 2. Flash sender ke sensor, receiver+MQTT ke gateway
# 3. Di laptop:
mosquitto_sub -h 192.168.1.50 -t "kodingindonesia/esp32/dht22/data" -v</code></pre>
<ol>
  <li>Power sensor — dalam 5 detik JSON muncul di <code>mosquitto_sub</code></li>
  <li>Matikan sensor — stream berhenti; gateway tetap online</li>
  <li>Jauhkan sensor &gt;30 m atau balik dinding — amati packet loss</li>
  <li>Ubah channel salah di sensor — tidak ada data (debug channel)</li>
  <li>Cek <a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">Grafana (#19)</a> — titik suhu naik jika Telegraf/subscriber jalan</li>
</ol>

<h2>FAQ Singkat</h2>
<dl>
  <dt><strong>ESP-NOW butuh internet?</strong></dt>
  <dd>Tidak. Hanya butuh dua ESP32 di jangkau radio. Internet/WiFi hanya di gateway jika mau forward MQTT.</dd>
  <dt><strong>Bisa lebih dari 2 board?</strong></dt>
  <dd>Ya — satu gateway bisa daftar banyak peer <code>esp_now_add_peer</code>. Sensor broadcast ke gateway, atau star topology.</dd>
  <dt><strong>ESP8266 support ESP-NOW?</strong></dt>
  <dd>Ya, tapi API sedikit berbeda. Artikel ini fokus ESP32.</dd>
  <dt><strong>Bentrok dengan WiFi router?</strong></dt>
  <dd>Tidak jika channel diselaraskan. Gateway connect WiFi + ESP-NOW simultan didukung ESP32.</dd>
</dl>

<h2>Tips &amp; Troubleshooting</h2>
<ul>
  <li><strong>ESP-NOW GAGAL:</strong> MAC gateway salah atau channel beda — cek <code>onSent</code> callback</li>
  <li><strong>MQTT tidak publish:</strong> Gateway WiFi/MQTT bermasalah — debug terpisah dari ESP-NOW</li>
  <li><strong>Data korup:</strong> <code>struct</code> tidak <code>packed</code> sama di kedua sketch — ukuran harus identik</li>
  <li><strong>Range pendek:</strong> Normal di dalam bangunan; antena eksternal atau <a href="/artikel/lora-esp32-modul-sx1278-kirim-data-jarak-jauh">LoRa (#26)</a> untuk jarak jauh</li>
  <li><strong>Payload &gt;250 byte:</strong> Pecah paket atau kirim field penting saja; atau pakai WiFi/MQTT</li>
  <li><strong>MAC berubah?</strong> Tidak untuk STA — tapi tulis di label agar tidak tertukar board</li>
</ul>

<h2>Keamanan &amp; Produksi</h2>
<ul>
  <li>Ganti <code>GANTI_NAMA_WIFI</code>, <code>GANTI_PASSWORD_WIFI</code>, <code>GANTI_PASSWORD_MQTT</code> — jangan commit ke GitHub</li>
  <li>Pakai <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">NVS + WiFiManager (#12)</a> di gateway untuk deploy lapangan</li>
  <li>Aktifkan enkripsi ESP-NOW jika data kontrol (relay, kunci)</li>
  <li>Placeholder MAC — selalu ganti dengan MAC nyata dari hardware kamu</li>
</ul>

<h2>Langkah Selanjutnya (Jalur D)</h2>
<ul>
  <li><strong><a href="/artikel/lora-esp32-modul-sx1278-kirim-data-jarak-jauh">LoRa SX1278 (#26)</a>:</strong> sensor jarak jauh (ratusan meter–km) menggantikan/perluas ESP-NOW</li>
  <li><strong><a href="/artikel/gateway-lora-mqtt-esp32-sensor-jarak-jauh-dashboard">Gateway LoRa → MQTT (#28)</a>:</strong> receiver LoRa + WiFi publish — pola gateway serupa untuk sensor kebun jauh</li>
  <li><strong><a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">MQTT TLS (#17)</a></strong> — amankan hop gateway → broker</li>
  <li><strong><a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">Deep sleep (#11)</a></strong> — optimalkan node sensor baterai</li>
  <li><strong><a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">Grafana (#19)</a></strong> — visualisasi data dari gateway MQTT</li>
  <li>Capstone <strong><a href="/artikel/smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt">greenhouse (#39)</a></strong> — multi-node ESP-NOW + pompa MQTT</li>
</ul>

<p>Dengan ESP-NOW, kamu bisa menaruh sensor di sudut yang WiFi router tidak jangkau — asalkan ada <strong>gateway ESP32</strong> di tengah yang menjembatani ke MQTT. Lanjutkan Seri 2 di <a href="/artikel">halaman artikel</a> Koding Indonesia.</p>
HTML;
    }
}
