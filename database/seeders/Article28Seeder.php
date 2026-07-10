<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article28Seeder extends Seeder
{
    public function run(): void
    {
        $admin  = User::first();
        $espCat = Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $espCat) {
            throw new \RuntimeException('User atau kategori tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'gateway-lora-mqtt-esp32-sensor-jarak-jauh-dashboard';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $espCat->id,
                'title'           => 'Gateway LoRa → MQTT: Sensor Jarak Jauh ke Dashboard',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'Gateway LoRa MQTT ESP32 — Sensor Jauh ke Grafana',
                'seo_description' => 'Tutorial gateway ESP32: terima paket LoRa SX1278 dari sensor jauh, forward ke broker Mosquitto via WiFi, dan pantau di Grafana. Capstone Jalur D Seri 2.',
            ]
        );

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        Tag::updateOrCreate(['slug' => 'gateway'], ['name' => 'gateway']);

        $tagIds = Tag::whereIn('slug', [
            'esp32', 'lora', 'mqtt', 'iot', 'wifi', 'gateway',
        ])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command->info('✓ Artikel ke-28 berhasil dipublish: ' . $article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — Menjembatani LoRa dan Cloud Lokal</h2>
<p>Di <a href="/artikel/lora-esp32-modul-sx1278-kirim-data-jarak-jauh">artikel #26 LoRa</a>, sensor di ujung kebun mengirim paket ke receiver ESP32 — tapi data masih berhenti di <strong>Serial Monitor</strong>. Di <a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">Grafana (#19)</a>, kamu sudah punya dashboard cantik — asalkan data masuk <strong>MQTT</strong> lewat <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">broker Mosquitto (#16)</a>.</p>

<p>Artikel <strong>capstone Jalur D</strong> ini menggabungkan keduanya: satu board <strong>gateway ESP32</strong> dengan modul LoRa + WiFi yang <strong>menerima paket sensor</strong> lalu <strong>publish JSON ke MQTT</strong>. Sensor jauh tanpa WiFi akhirnya muncul di dashboard yang sama dengan node DHT22 biasa.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> Sudah bisa <a href="/artikel/lora-esp32-modul-sx1278-kirim-data-jarak-jauh">LoRa peer-to-peer (#26)</a>, punya <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">broker MQTT (#16)</a>, dan idealnya sudah pasang <a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">Grafana (#19)</a>. Bukan pengganti <a href="/artikel/esp-now-kirim-data-antar-esp32-tanpa-router-wifi">ESP-NOW (#25)</a> — itu untuk node dekat tanpa LoRa.</p>
</blockquote>

<h2>Arsitektur Gateway LoRa → MQTT</h2>
<pre><code>  [ Sensor node ]          [ Gateway ESP32 ]              [ Cloud lokal ]
  ESP32 + LoRa TX    --LoRa--&gt;  ESP32 + LoRa RX + WiFi  --MQTT--&gt;  Mosquitto (#16)
  DHT22 (#5)                    PubSubClient publish            |
  deep sleep (#11)              topic Seri 2                    v
                                                          Grafana (#19)
                                                          Node-RED (#23)</code></pre>

<p>Node sensor tetap seperti #26 (kirim <code>lora_packet_t</code>). Gateway menambahkan lapisan <strong>WiFi + MQTT</strong> — pola mirip <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">publish MQTT (#7)</a>, tapi sumber datanya bukan sensor lokal, melainkan <strong>radio LoRa</strong>.</p>

<h2>LoRa Gateway vs ESP-NOW vs WiFi Langsung</h2>
<table>
  <thead>
    <tr><th>Pola</th><th>Jangkau sensor</th><th>Gateway butuh</th><th>Dashboard</th></tr>
  </thead>
  <tbody>
    <tr><td><strong><a href="/artikel/esp-now-kirim-data-antar-esp32-tanpa-router-wifi">ESP-NOW (#25)</a></strong></td><td>~10–200 m</td><td>ESP32 kedua (bisa tanpa LoRa)</td><td>Serial / MQTT manual</td></tr>
    <tr><td><strong><a href="/artikel/lora-esp32-modul-sx1278-kirim-data-jarak-jauh">LoRa P2P (#26)</a></strong></td><td>ratusan m – km</td><td>Receiver Serial saja</td><td>Belum MQTT</td></tr>
    <tr><td><strong>Gateway (artikel ini)</strong></td><td>sama LoRa #26</td><td>LoRa RX + WiFi + MQTT</td><td>Grafana / HA / Node-RED</td></tr>
    <tr><td><strong><a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">WiFi + MQTT (#7)</a></strong></td><td>jangkau AP</td><td>Sensor punya WiFi</td><td>Langsung ke broker</td></tr>
  </tbody>
</table>

<p>Lihat <a href="/artikel/rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32">REST vs MQTT (#20)</a> untuk memilih lapisan cloud setelah gateway publish.</p>

<h2>Yang Kamu Butuhkan</h2>
<ul>
  <li><strong>1× node sensor</strong> — sketch sender #26 (ESP32 + LoRa + DHT22)</li>
  <li><strong>1× gateway ESP32</strong> — ESP32 + modul SX1278 + akses WiFi rumah/lab</li>
  <li><strong>Broker Mosquitto</strong> — <code>192.168.1.50</code>, user <code>kindo_esp32</code> (sesuaikan dengan #16)</li>
  <li><strong>Stack dashboard</strong> — Grafana + InfluxDB dari #19 (opsional tapi disarankan)</li>
  <li>Library: <strong>LoRa</strong> (Sandeep Mistry), <strong>PubSubClient</strong>, <strong>WiFi</strong></li>
</ul>

<h2>Topik MQTT &amp; Format JSON</h2>
<p>Gunakan topic konsisten Seri 2 agar panel Grafana #19 langsung menangkap data:</p>
<pre><code class="language-bash">Topic: kodingindonesia/esp32/dht22/data
Payload contoh:
{"suhu":28.5,"kelembaban":65.2,"unix":1782977400,"iso":"2026-07-02T14:30:00","source":"lora"}</code></pre>

<p>Field <code>source":"lora"</code> membantu membedakan node WiFi vs node LoRa di query Grafana. Timestamp selaras <a href="/artikel/ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt">NTP (#34)</a> — gateway bisa menambahkan <code>unix</code> saat forward jika paket sensor belum punya waktu akurat.</p>

<h2>Struktur Paket LoRa (Sama dengan #26)</h2>
<pre><code class="language-cpp">typedef struct __attribute__((packed)) {
  float suhu;
  float kelembaban;
  uint32_t unix;
} lora_packet_t;</code></pre>

<p>Sync word, frequency, dan spreading factor gateway <strong>harus identik</strong> dengan node sensor — lihat troubleshooting di #26 jika paket tidak masuk.</p>

<h2>Sketch Gateway — LoRa RX + MQTT Publish</h2>
<pre><code class="language-cpp">#include &lt;WiFi.h&gt;
#include &lt;PubSubClient.h&gt;
#include &lt;SPI.h&gt;
#include &lt;LoRa.h&gt;
#include &lt;ArduinoJson.h&gt;

const char* ssid = "GANTI_NAMA_WIFI";
const char* password = "GANTI_PASSWORD_WIFI";
const char* mqtt_server = "192.168.1.50";
const int mqtt_port = 1883;
const char* mqtt_user = "kindo_esp32";
const char* mqtt_pass = "GANTI_PASSWORD_MQTT";
const char* topic = "kodingindonesia/esp32/dht22/data";

#define LORA_FREQ 433E6

WiFiClient espClient;
PubSubClient mqtt(espClient);

typedef struct __attribute__((packed)) {
  float suhu;
  float kelembaban;
  uint32_t unix;
} lora_packet_t;

void reconnectMqtt() {
  while (!mqtt.connected()) {
    if (mqtt.connect("gateway-lora-esp32", mqtt_user, mqtt_pass)) {
      Serial.println("MQTT connected");
    } else {
      delay(3000);
    }
  }
}

void setup() {
  Serial.begin(115200);
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) { delay(500); }

  mqtt.setServer(mqtt_server, mqtt_port);

  SPI.begin(5, 19, 27, 18);
  LoRa.setPins(18, 14, 26);
  if (!LoRa.begin(LORA_FREQ)) {
    Serial.println("LoRa init gagal!");
    while (true) { delay(1000); }
  }
  LoRa.setSpreadingFactor(7);
  LoRa.setSyncWord(0x12);
}

void loop() {
  if (!mqtt.connected()) reconnectMqtt();
  mqtt.loop();

  int packetSize = LoRa.parsePacket();
  if (packetSize == sizeof(lora_packet_t)) {
    lora_packet_t pkt;
    LoRa.readBytes((uint8_t*)&amp;pkt, sizeof(pkt));

    StaticJsonDocument&lt;200&gt; doc;
    doc["suhu"] = pkt.suhu;
    doc["kelembaban"] = pkt.kelembaban;
    doc["unix"] = pkt.unix;
    doc["iso"] = "2026-07-02T14:30:00";
    doc["source"] = "lora";
    doc["rssi"] = LoRa.packetRssi();

    char buf[200];
    serializeJson(doc, buf, sizeof(buf));
    mqtt.publish(topic, buf);
    Serial.printf("MQTT &lt;- LoRa suhu=%.1f\n", pkt.suhu);
  }
}</code></pre>

<blockquote>
  <p><strong>Pro tip:</strong> Jangan publish setiap byte noise — cek <code>packetSize == sizeof(lora_packet_t)</code> dan validasi rentang suhu (mis. -40…85 °C) sebelum forward ke MQTT.</p>
</blockquote>

<h2>Integrasi Grafana (#19)</h2>
<ol>
  <li>Pastikan Telegraf / subscriber #19 sudah subscribe <code>kodingindonesia/esp32/#</code></li>
  <li>Tambah filter <code>source = lora</code> di panel Grafana untuk memisahkan node kebun</li>
  <li>Panel suhu &amp; kelembaban — reuse dashboard dari #19, tambah legend "LoRa kebun"</li>
  <li>Opsional: alert jika <code>rssi</code> &lt; -120 dBm (link radio melemah)</li>
</ol>

<p>Untuk otomasi visual lain, lihat <a href="/artikel/node-red-dashboard-otomasi-iot-mqtt-esp32">Node-RED (#23)</a> atau <a href="/artikel/home-assistant-integrasi-esp32-mqtt">Home Assistant (#21)</a>.</p>

<h2>Keamanan MQTT &amp; WiFi</h2>
<ul>
  <li>Ganti <code>GANTI_NAMA_WIFI</code>, <code>GANTI_PASSWORD_WIFI</code>, <code>GANTI_PASSWORD_MQTT</code> — jangan commit ke GitHub</li>
  <li>Untuk gateway di internet, aktifkan <a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">MQTT TLS (#17)</a></li>
  <li>LoRa sync word bukan enkripsi — untuk lapangan sensitif pertimbangkan AES di payload</li>
  <li>Label fisik board <strong>GATEWAY</strong> vs <strong>SENSOR</strong></li>
</ul>

<h2>Visual vs Telemetry</h2>
<p><a href="/artikel/esp32-cam-streaming-mjpeg-capture-foto-wifi">ESP32-CAM (#27)</a> cocok untuk live video di LAN — <strong>bukan</strong> lewat LoRa/MQTT telemetry. Gateway artikel ini untuk <strong>angka sensor</strong>; kamera tetap terpisah di titik yang ada WiFi.</p>

<h2>OTA &amp; Deploy Lapangan</h2>
<p>Setelah gateway terpasang di rak outdoor, update firmware tanpa USB lewat <a href="/artikel/ota-update-firmware-esp32-via-wifi">OTA (#15)</a>. Untuk SSID berbeda per lokasi, kombinasikan <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">WiFiManager (#12)</a> di gateway saja — node sensor LoRa tetap tanpa WiFi.</p>

<h2>Buffer &amp; Retry MQTT</h2>
<p>Di lapangan, WiFi bisa drop sesaat. Pertimbangkan pola berikut di gateway:</p>
<ul>
  <li>Simpan paket LoRa terakhir di variabel static — publish ulang saat MQTT reconnect</li>
  <li>Gunakan <a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">QoS 1 (#17)</a> jika broker mendukung — mengurangi hilang saat jaringan fluktuatif</li>
  <li>Jangan queue ratusan paket di RAM ESP32 — LoRa telemetry jarang &amp; kecil, cukup <strong>last value</strong></li>
  <li>Log RSSI (<code>LoRa.packetRssi()</code>) ke MQTT untuk diagnosa jarak di Grafana</li>
</ul>

<p>Jika gateway reboot, node sensor tetap kirim — data akan muncul lagi setelah WiFi + MQTT connected. Untuk alert gateway mati, konfigurasi <strong>LWT</strong> di #17 dengan topic status terpisah.</p>

<h2>Multi-Node (Opsional)</h2>
<p>Satu gateway bisa menerima beberapa sensor jika:</p>
<ol>
  <li>Setiap node punya <code>node_id</code> uint8 di struct (ubah di #26 &amp; gateway bersamaan)</li>
  <li>Interval kirim di-stagger — hindari tabrakan paket di udara</li>
  <li>MQTT topic bisa tetap satu dengan field <code>node_id</code>, atau pecah: <code>kodingindonesia/esp32/lora/kebun1/data</code></li>
</ol>

<p>Untuk lab awal, satu node + satu gateway sudah cukup membuktikan alur end-to-end ke <a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">Grafana</a>.</p>

<h2>Estimasi Biaya (Indonesia, 2026)</h2>
<table>
  <thead>
    <tr><th>Komponen</th><th>Perkiraan</th></tr>
  </thead>
  <tbody>
    <tr><td>ESP32 + SX1278 (gateway)</td><td>Rp 80–120 rb</td></tr>
    <tr><td>ESP32 + SX1278 + DHT22 (sensor node)</td><td>Rp 100–150 rb</td></tr>
    <tr><td>Antena 433 MHz (opsional)</td><td>Rp 15–40 rb</td></tr>
    <tr><td>Broker + Grafana (VPS/RPi #16+#19)</td><td>sudah ada dari artikel sebelumnya</td></tr>
  </tbody>
</table>

<h2>Checklist: Kapan Pakai Gateway LoRa?</h2>
<ol>
  <li>Sensor &gt;200 m dari router WiFi? → <strong>LoRa node + gateway</strong></li>
  <li>Butuh grafik histori di Grafana? → <strong>Gateway publish MQTT (#19)</strong></li>
  <li>Node dekat &lt;50 m tanpa LoRa? → <strong>ESP-NOW (#25)</strong> lebih sederhana</li>
  <li>Butuh video kebun? → <strong>ESP32-CAM (#27)</strong> di titik ada WiFi</li>
  <li>Greenhouse capstone? → <strong>#39</strong> gabung LoRa + pompa MQTT</li>
</ol>

<h2>Uji Coba (Lab)</h2>
<pre><code class="language-bash"># Terminal 1 — subscribe MQTT (broker #16):
mosquitto_sub -h 192.168.1.50 -u kindo_esp32 -P GANTI_PASSWORD_MQTT \
  -t kodingindonesia/esp32/dht22/data -v

# Flash gateway sketch, nyalakan node sensor #26
# Harus muncul JSON dengan "source":"lora"

# Buka Grafana (#19) — panel suhu harus update</code></pre>
<ol>
  <li>Verifikasi node sensor TX di Serial (artikel #26)</li>
  <li>Gateway Serial menampilkan <code>MQTT &lt;- LoRa</code></li>
  <li><code>mosquitto_sub</code> menerima payload JSON</li>
  <li>Grafana panel update dalam ±1 menit (refresh Telegraf)</li>
  <li>Jauhkan node sensor — catat jarak saat paket putus</li>
</ol>

<h2>Monitoring &amp; Observabilitas</h2>
<p>Setelah alur dasar jalan, tambahkan metrik berikut di dashboard:</p>
<ul>
  <li><strong>Counter paket LoRa/hari</strong> — deteksi node mati tanpa datang ke MQTT</li>
  <li><strong>Rata-rata RSSI</strong> — trend melemah = antena/kabel perlu cek</li>
  <li><strong>Latency LoRa → MQTT</strong> — selisih waktu RX vs publish (opsional, butuh NTP di gateway)</li>
  <li><strong>Status gateway</strong> — topic <code>kodingindonesia/esp32/gateway/status</code> dengan LWT <code>offline</code></li>
</ul>

<p>Panel ini melengkapi grafik suhu dari #19 — kamu memantau <em>kesehatan link</em>, bukan hanya nilai sensor. Untuk notifikasi Telegram/WhatsApp, arahkan <a href="/artikel/node-red-dashboard-otomasi-iot-mqtt-esp32">Node-RED (#23)</a> ke topic status gateway.</p>

<h2>FAQ Singkat</h2>
<dl>
  <dt><strong>Bisa beberapa sensor LoRa ke satu gateway?</strong></dt>
  <dd>Ya — tambahkan <code>node_id</code> di struct dan filter di MQTT; hindari collision dengan interval kirim berbeda.</dd>
  <dt><strong>Gateway harus online 24/7?</strong></dt>
  <dd>Ya, selama kamu mau data real-time. Node sensor bisa deep sleep; gateway butuh WiFi + power stabil.</dd>
  <dt><strong>Perlu LoRaWAN?</strong></dt>
  <dt><strong>Gateway bisa pakai Raspberry Pi?</strong></dt>
  <dd>Bisa — RPi + LoRa hat + Python forwarder; artikel ini fokus ESP32 agar konsisten dengan #26 dan biaya murah.</dd>
  <dt><strong>Harus sama persis board dengan #26?</strong></dt>
  <dd>Pin LoRa disarankan sama; jika beda board, sesuaikan <code>SPI.begin</code> dan <code>LoRa.setPins</code> seperti tabel wiring #26.</dd>
</dl>

<h2>Tips &amp; Troubleshooting</h2>
<ul>
  <li><strong>MQTT kosong tapi LoRa RX OK:</strong> Cek user/password broker, topic typo, firewall port 1883</li>
  <li><strong>LoRa RX kosong:</strong> SF/sync word/frequency beda dengan sensor — ulang #26</li>
  <li><strong>Grafana flat:</strong> Telegraf belum parse field baru — cek measurement &amp; JSON keys</li>
  <li><strong>WiFi drop:</strong> Gateway dekat router atau pakai AP outdoor; LWT (#17) untuk alert offline</li>
  <li><strong>Duplicate data:</strong> Sensor kirim terlalu cepat — naikkan interval TX ke 30–60 d</li>
</ul>

<h2>Regulasi &amp; Deploy Lapangan</h2>
<p>Gateway biasanya dipasang di rumah/gudang dekat router WiFi — bukan di tengah kebun seperti node sensor. Pastikan:</p>
<ul>
  <li>Antena LoRa gateway terpasang vertikal, bebas halangan metal besar</li>
  <li>Power supply stabil (5V 2A) — WiFi + LoRa TX bersamaan mengonsumsi puncak lebih tinggi</li>
  <li>Node sensor di kebun tetap mengikuti catatan regulasi 433 MHz dari <a href="/artikel/lora-esp32-modul-sx1278-kirim-data-jarak-jauh">artikel #26</a></li>
  <li>Dokumentasi sketsa: frekuensi, SF, sync word, IP broker — berguna saat debug bulan depan</li>
</ul>

<p>Untuk proyek komersial skala besar, pertimbangkan LoRaWAN operator atau konsultasi frekuensi — tutorial ini untuk prototipe edukasi di lab dan kebun rumah.</p>

<h2>Langkah Selanjutnya (Jalur D selesai → Jalur E)</h2>
<ul>
  <li><strong><a href="/artikel/migrasi-platformio-esp32-vscode-project-rapi">Migrasi PlatformIO (#29)</a>:</strong> struktur project gateway + sensor lebih rapi</li>
  <li><strong><a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">Python → MySQL (#18)</a></strong> — arsip SQL paralel Grafana</li>
  <li><strong>Capstone greenhouse (#39)</strong> — LoRa kebun + pompa relay + dashboard</li>
  <li>Kembali ke <a href="/artikel/lora-esp32-modul-sx1278-kirim-data-jarak-jauh">LoRa (#26)</a> jika perlu tuning SF/antena</li>
</ul>

<p>Dengan gateway LoRa → MQTT, sensor di ujung lahan akhirnya <strong>terlihat di dashboard</strong> — capstone <strong>Jalur D</strong> Seri 2 selesai. Lanjutkan di <a href="/artikel">halaman artikel</a> Koding Indonesia.</p>
HTML;
    }
}
