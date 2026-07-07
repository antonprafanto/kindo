<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article34Seeder extends Seeder
{
    public function run(): void
    {
        $admin  = User::first();
        $espCat = Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $espCat) {
            throw new \RuntimeException('User atau kategori tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $espCat->id,
                'title'           => 'NTP & Timestamp di ESP32: Waktu Akurat untuk Log Sensor MQTT',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'NTP ESP32 — Timestamp Akurat Log Sensor MQTT',
                'seo_description' => 'Sinkronkan waktu ESP32 via NTP (pool.ntp.org), set zona WIB/WITA/WIT, dan kirim JSON MQTT dengan timestamp ISO — fondasi sebelum simpan ke MySQL.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        Tag::updateOrCreate(['slug' => 'ntp'], ['name' => 'ntp']);
        Tag::updateOrCreate(['slug' => 'timestamp'], ['name' => 'timestamp']);

        $tagIds = Tag::whereIn('slug', [
            'esp32', 'ntp', 'timestamp', 'mqtt', 'wifi', 'iot', 'sensor',
        ])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command->info('✓ Artikel ke-34 berhasil dipublish: ' . $article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — Kenapa Waktu Penting?</h2>
<p>Di artikel <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">MQTT (#7)</a> dan <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">broker Mosquitto (#16)</a>, payload sensor sudah berupa JSON — tapi sering <strong>tanpa penanda waktu</strong>. Fungsi <code>millis()</code> di ESP32 hanya menghitung milidetik sejak boot; setelah <a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">deep sleep (#11)</a> atau reset, angka itu kembali ke nol.</p>

<p>Artikel Tier 2 ini menutup celah itu: sinkronkan <strong>wall-clock time</strong> lewat <strong>NTP (Network Time Protocol)</strong>, set zona waktu Indonesia, lalu sisipkan <code>timestamp</code> ke setiap publish MQTT. Ini <strong>wajib</strong> sebelum <strong><a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">Subscriber Python (#18)</a></strong> → MySQL dan <strong>#19</strong> (InfluxDB + Grafana) — database histori butuh waktu yang konsisten dan bisa diurutkan.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> ESP32 sudah connect <a href="/artikel/menghubungkan-esp32-wifi-kirim-data-server">WiFi (#4)</a> dan publish <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">MQTT (#7)</a>. Broker <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Mosquitto pribadi (#16)</a> disarankan (bukan broker publik). Familiar <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">NVS (#12)</a> membantu untuk deploy lapangan.</p>
</blockquote>

<h2>millis() vs Waktu NTP</h2>
<table>
  <thead>
    <tr><th>Sumber waktu</th><th>Contoh</th><th>Cocok untuk histori DB?</th></tr>
  </thead>
  <tbody>
    <tr><td><code>millis()</code></td><td><code>452301</code> ms sejak boot</td><td>❌ Reset tiap deep sleep / power cycle</td></tr>
    <tr><td><code>time(nullptr)</code> setelah NTP</td><td>Unix epoch <code>1782977400</code></td><td>✅ Sortable, universal</td></tr>
    <tr><td>ISO 8601 lokal</td><td><code>2026-07-02T14:30:00</code></td><td>✅ Mudah dibaca manusia &amp; Python</td></tr>
  </tbody>
</table>

<p>NTP membutuhkan <strong>internet atau server waktu di LAN</strong> (UDP port <code>123</code>). Di lab rumah, <code>pool.ntp.org</code> atau <code>id.pool.ntp.org</code> biasanya cukup — asalkan ESP32 sudah online via WiFi.</p>

<h2>Zona Waktu Indonesia</h2>
<table>
  <thead>
    <tr><th>Zona</th><th>UTC offset</th><th><code>gmtOffset_sec</code></th><th>Contoh kota</th></tr>
  </thead>
  <tbody>
    <tr><td><strong>WIB</strong></td><td>UTC+7</td><td><code>7 * 3600</code></td><td>Jakarta, Bandung, Surabaya</td></tr>
    <tr><td><strong>WITA</strong></td><td>UTC+8</td><td><code>8 * 3600</code></td><td>Makassar, Bali</td></tr>
    <tr><td><strong>WIT</strong></td><td>UTC+9</td><td><code>9 * 3600</code></td><td>Jayapura, Papua</td></tr>
  </tbody>
</table>

<p>Indonesia <strong>tidak pakai daylight saving</strong> — set <code>daylightOffset_sec = 0</code>. Ganti offset di sketch sesuai lokasi node sensor kamu.</p>

<p>Di Arduino IDE, setelah upload, baris seperti <code>Waktu lokal: Wednesday, July 02 2026 14:30:00</code> di Serial Monitor membuktikan SNTP berhasil. Jika tahun masih <code>1970</code>, berarti NTP belum sinkron — jangan publish dulu.</p>

<h2>Yang Kamu Butuhkan</h2>
<ul>
  <li><strong>ESP32 DevKit</strong> + sensor DHT22 (GPIO 4, pull-up 10kΩ) — sama <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">tutorial DHT22</a></li>
  <li>WiFi 2.4 GHz dengan akses internet (untuk NTP)</li>
  <li>Broker MQTT — contoh sketch memakai <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Mosquitto #16</a> di <code>192.168.1.50</code></li>
  <li>Library: <strong>PubSubClient</strong>, <strong>DHT sensor library</strong>, <strong>ArduinoJson</strong></li>
  <li>Header bawaan ESP32: <code>time.h</code> (SNTP) — tidak perlu install tambahan</li>
</ul>

<p><strong>Estimasi biaya:</strong> Rp 0 (NTP publik gratis; tidak ada komponen baru jika sudah punya stack MQTT).</p>

<h2>Arsitektur: WiFi → NTP → Sensor → MQTT</h2>
<pre><code>  [ ESP32 ]
      | 1) WiFi connect
      | 2) configTime() → pool.ntp.org (UDP :123)
      | 3) Baca DHT22
      | 4) JSON + timestamp → MQTT publish
      v
  [ Mosquitto #16 ]
      |
      +-- Subscriber Python (#18) — INSERT dengan kolom waktu
      +-- Home Assistant (#21) — entity dengan last_changed akurat</code></pre>

<p><strong>Topic</strong> (konsisten Seri 2): <code>kodingindonesia/esp32/dht22/data</code></p>
<p><strong>Payload contoh:</strong></p>
<pre><code>{"suhu":28.5,"kelembaban":65.2,"timestamp":"2026-07-02T14:30:00","unix":1782977400}</code></pre>

<h2>Fungsi Sinkronisasi NTP</h2>
<p>Panggil <strong>setelah WiFi connected</strong>, sebelum publish:</p>
<pre><code class="language-arduino">#include "time.h"

const char* ntpServer = "id.pool.ntp.org";  // atau pool.ntp.org
const long  gmtOffset_sec = 7 * 3600;       // WIB — ganti 8/9 untuk WITA/WIT
const int   daylightOffset_sec = 0;

bool sinkronisasiNTP(int maxRetry = 15) {
  configTime(gmtOffset_sec, daylightOffset_sec, ntpServer);
  struct tm timeinfo;
  for (int i = 0; i &lt; maxRetry; i++) {
    if (getLocalTime(&amp;timeinfo)) {
      return true;
    }
    delay(500);
  }
  return false;
}

bool ambilTimestampISO(char* buf, size_t len) {
  struct tm timeinfo;
  if (!getLocalTime(&amp;timeinfo)) {
    return false;
  }
  strftime(buf, len, "%Y-%m-%dT%H:%M:%S", &amp;timeinfo);
  return true;
}</code></pre>

<blockquote>
  <p><strong>Pro tip:</strong> Jangan panggil NTP sebelum WiFi <code>WL_CONNECTED</code> — permintaan SNTP akan gagal diam-diam. Beri timeout (loop di atas) agar tidak hang selamanya.</p>
</blockquote>

<h2>Kode Lengkap: DHT22 + MQTT + Timestamp</h2>
<p>Ganti SSID, password WiFi, kredensial MQTT, dan offset zona waktu:</p>
<pre><code class="language-arduino">#include &lt;WiFi.h&gt;
#include &lt;PubSubClient.h&gt;
#include &lt;DHT.h&gt;
#include &lt;ArduinoJson.h&gt;
#include "time.h"

const char* ssid     = "NamaWiFiKamu";
const char* password = "PasswordWiFiKamu";

const char* mqttHost = "192.168.1.50";
const int   mqttPort = 1883;
const char* mqttUser = "kindo_esp32";
const char* mqttPass = "GANTI_PASSWORD_MQTT";

const char* topicData = "kodingindonesia/esp32/dht22/data";

const char* ntpServer = "id.pool.ntp.org";
const long  gmtOffset_sec = 7 * 3600;
const int   daylightOffset_sec = 0;

#define DHT_PIN  4
#define DHT_TYPE DHT22

DHT dht(DHT_PIN, DHT_TYPE);
WiFiClient espClient;
PubSubClient mqttClient(espClient);

unsigned long lastPublishMs = 0;

bool sinkronisasiNTP(int maxRetry = 15) {
  configTime(gmtOffset_sec, daylightOffset_sec, ntpServer);
  struct tm timeinfo;
  for (int i = 0; i &lt; maxRetry; i++) {
    if (getLocalTime(&amp;timeinfo)) {
      return true;
    }
    delay(500);
  }
  return false;
}

bool ambilTimestampISO(char* buf, size_t len) {
  struct tm timeinfo;
  if (!getLocalTime(&amp;timeinfo)) {
    return false;
  }
  strftime(buf, len, "%Y-%m-%dT%H:%M:%S", &amp;timeinfo);
  return true;
}

bool koneksiWiFi() {
  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);
  unsigned long mulai = millis();
  while (WiFi.status() != WL_CONNECTED &amp;&amp; millis() - mulai &lt; 15000) {
    delay(500);
  }
  return WiFi.status() == WL_CONNECTED;
}

bool koneksiMQTT() {
  mqttClient.setServer(mqttHost, mqttPort);
  mqttClient.setBufferSize(512);
  String clientId = "ESP32-NTP-" + String((uint32_t)ESP.getEfuseMac(), HEX);

  uint8_t percobaan = 0;
  while (!mqttClient.connected() &amp;&amp; percobaan &lt; 5) {
    if (mqttClient.connect(clientId.c_str(), mqttUser, mqttPass)) {
      return true;
    }
    Serial.print("MQTT gagal, rc=");
    Serial.println(mqttClient.state());
    percobaan++;
    delay(2000);
  }
  return false;
}

bool publishSensorDenganWaktu() {
  float rh  = dht.readHumidity();
  float suhu = dht.readTemperature();
  if (isnan(rh) || isnan(suhu)) {
    return false;
  }

  char isoBuf[32] = "";
  if (!ambilTimestampISO(isoBuf, sizeof(isoBuf))) {
    Serial.println("Waktu belum sinkron — publish tanpa timestamp akurat");
  }

  StaticJsonDocument&lt;192&gt; doc;
  doc["suhu"] = roundf(suhu * 10) / 10.0;
  doc["kelembaban"] = roundf(rh * 10) / 10.0;
  if (isoBuf[0] != '\0') {
    doc["timestamp"] = isoBuf;
    doc["unix"] = (long)time(nullptr);
  }

  char buffer[192];
  serializeJson(doc, buffer);
  mqttClient.loop();
  return mqttClient.publish(topicData, buffer);
}

void setup() {
  Serial.begin(115200);
  dht.begin();
  delay(2000);

  if (!koneksiWiFi()) {
    Serial.println("WiFi gagal");
    return;
  }
  Serial.println("WiFi OK");

  if (!sinkronisasiNTP()) {
    Serial.println("NTP gagal — cek internet / firewall UDP 123");
  } else {
    struct tm timeinfo;
    getLocalTime(&amp;timeinfo);
    Serial.println(&amp;timeinfo, "Waktu lokal: %A, %B %d %Y %H:%M:%S");
  }

  if (!koneksiMQTT()) {
    Serial.println("MQTT gagal");
    return;
  }
  Serial.println("MQTT OK");
}

void loop() {
  if (WiFi.status() != WL_CONNECTED) {
    koneksiWiFi();
  }
  if (!mqttClient.connected()) {
    koneksiMQTT();
  }
  mqttClient.loop();

  if (millis() - lastPublishMs &gt; 10000) {
    lastPublishMs = millis();
    if (publishSensorDenganWaktu()) {
      Serial.println("Publish dengan timestamp OK");
    }
  }
}</code></pre>

<h2>Penjelasan Bagian Kritis</h2>
<ol>
  <li><strong>Urutan:</strong> WiFi → NTP → baca sensor → publish. NTP butuh route ke internet.</li>
  <li><strong><code>configTime()</code></strong> — memanggil SNTP di balik layar ESP32 Arduino core.</li>
  <li><strong><code>timestamp</code> + <code>unix</code></strong> — ISO lokal (tanpa suffix <code>+07:00</code>) untuk dashboard manusia; Unix UTC untuk query SQL/Grafana di <a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">#18</a> / #19.</li>
  <li><strong>Max 5 percobaan MQTT</strong> — <code>koneksiMQTT()</code> tidak block <code>loop()</code> selamanya jika broker down (pola sama <a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">#17</a>).</li>
  <li><strong><code>setBufferSize(512)</code></strong> — JSON lebih panjang setelah tambah field waktu.</li>
  <li><strong>Broker #16</strong> — ganti ke port <code>8883</code> + TLS jika sudah ikut <a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">artikel #17</a>.</li>
  <li><strong>Deep sleep (#11):</strong> sinkronkan NTP <strong>tiap bangun</strong> — RTC tidak menyimpan zona waktu setelah reset penuh.</li>
</ol>

<h2>Integrasi Deep Sleep &amp; Node Lapangan</h2>
<p>Pada <a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">node deep sleep (#11)</a>, tambahkan <code>sinkronisasiNTP()</code> di <code>setup()</code> setelah WiFi — sebelum <code>publishSensorDenganWaktu()</code>. Budget waktu: NTP biasanya 1–3 detik; tetap jauh lebih hemat daripada node always-on.</p>

<p>Untuk node tanpa internet (hanya LAN), pertimbangkan <strong>NTP server lokal</strong> di Raspberry Pi (<code>ntp</code> package) dan arahkan <code>ntpServer</code> ke IP Pi — di luar scope artikel ini, tapi pola <code>configTime()</code> sama.</p>

<h2>Verifikasi dari Laptop (mosquitto_sub)</h2>
<p>Setelah ESP32 publish, pantau payload lengkap dari terminal:</p>
<pre><code class="language-bash">mosquitto_sub -h 192.168.1.50 -p 1883 \
  -u kindo_esp32 -P 'GANTI_PASSWORD_MQTT' \
  -t "kodingindonesia/esp32/dht22/data" -v</code></pre>

<p>Output yang diharapkan:</p>
<pre><code>kodingindonesia/esp32/dht22/data {"suhu":28.5,"kelembaban":65.2,"timestamp":"2026-07-02T14:30:00","unix":1782977400}</code></pre>

<p>Field <code>unix</code> akan dipakai di <strong><a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">Subscriber Python (#18)</a></strong> untuk kolom <code>DATETIME</code> atau <code>INT UNSIGNED</code> di MySQL — lebih mudah di-query daripada string ISO saja.</p>

<p>Di Grafana (#19), sumbu waktu grafik otomatis benar jika setiap titik data punya timestamp UTC atau lokal yang konsisten — tanpa NTP, grafik akan terlihat “loncat” atau semua titik menumpuk di waktu upload subscriber, bukan waktu pengukuran sensor di lapangan.</p>

<h2>Uji Coba (Checklist)</h2>
<ol>
  <li>Upload sketch → Serial: <code>WiFi OK</code> lalu baris waktu lokal terformat</li>
  <li>Jika NTP gagal — cek router block UDP 123 atau DNS</li>
  <li><code>mosquitto_sub -h 192.168.1.50 -p 1883 -u kindo_esp32 -P 'GANTI_PASSWORD_MQTT' -t "kodingindonesia/esp32/dht22/data" -v</code></li>
  <li>Pastikan JSON punya <code>timestamp</code> dan <code>unix</code> — bukan hanya suhu/kelembaban</li>
  <li>Ubah <code>gmtOffset_sec</code> ke WITA/WIT — bandingkan jam di payload dengan jam HP (zona sama)</li>
  <li>Reset board — <code>unix</code> harus tetap masuk akal (bukan 0 atau 1970)</li>
  <li>Gabung dengan <a href="/artikel/sensor-gerak-pir-esp32-lampu-mqtt-debounce">PIR (#24)</a> — tambahkan <code>timestamp</code> ke event gerak</li>
</ol>

<h2>Tips &amp; Troubleshooting</h2>
<ul>
  <li><strong>NTP selalu gagal:</strong> WiFi belum connect; firewall blok UDP 123; coba <code>pool.ntp.org</code> atau IP NTP router</li>
  <li><strong>Jam salah ± beberapa jam:</strong> <code>gmtOffset_sec</code> salah — sesuaikan WIB/WITA/WIT</li>
  <li><strong><code>timestamp</code> kosong di JSON:</strong> <code>getLocalTime()</code> gagal — perpanjang retry atau cek SNTP setelah WiFi stabil</li>
  <li><strong>Publish gagal setelah tambah field:</strong> Naikkan <code>setBufferSize(512)</code> atau kurangi presisi string</li>
  <li><strong>Deep sleep — waktu 1970:</strong> Normal jika lupa panggil NTP setelah bangun; panggil ulang di awal <code>setup()</code></li>
  <li><strong>WiFi 2.4 GHz:</strong> ESP32 tidak support jaringan 5 GHz saja</li>
  <li><strong>rc=5 MQTT:</strong> Sama seperti <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">#16</a> — user/password salah</li>
</ul>

<h2>Langkah Selanjutnya (Seri 2)</h2>
<ul>
  <li><strong><a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">Subscriber Python (#18)</a></strong> — parse <code>timestamp</code> / <code>unix</code> → simpan ke <strong>MySQL</strong> (kolom <code>DATETIME</code>)</li>
  <li><strong>Artikel #19:</strong> <strong>InfluxDB + Grafana</strong> — histori grafik dengan sumbu waktu benar</li>
  <li><strong><a href="/artikel/home-assistant-integrasi-esp32-mqtt">Home Assistant (#21)</a></strong> — sensor MQTT dengan timestamp di <code>last_changed</code></li>
  <li><strong><a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">MQTT TLS (#17)</a></strong> — amankan transport sebelum subscriber cloud</li>
  <li><strong><a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">Deep sleep (#11)</a></strong> — gabung NTP tiap siklus bangun</li>
  <li><strong><a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">NVS (#12)</a></strong> — simpan offset zona / host NTP opsional</li>
</ul>

<blockquote>
  <p><strong>Keamanan:</strong> Jangan hardcode password WiFi atau MQTT di repo publik. Gunakan <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">NVS + WiFiManager (#12)</a> untuk deploy lapangan.</p>
</blockquote>

<p>Dengan timestamp akurat, data sensor siap masuk pipeline histori — langkah berikutnya adalah <strong><a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">Subscriber Python (#18)</a></strong> → MySQL. Lanjutkan Seri 2 di <a href="/artikel">halaman artikel</a> Koding Indonesia.</p>
HTML;
    }
}
