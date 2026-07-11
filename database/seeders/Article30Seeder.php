<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article30Seeder extends Seeder
{
    public function run(): void
    {
        $admin  = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first();

        if (! $admin || ! $iotCat) {
            throw new \RuntimeException('User atau kategori tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'esp32-firebase-realtime-database-sensor-cloud';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $iotCat->id,
                'title'           => 'ESP32 + Firebase Realtime Database: Sensor ke Cloud Tanpa Server',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'ESP32 Firebase Realtime Database — Sensor ke Cloud',
                'seo_description' => 'Kirim data sensor DHT22 dari ESP32 ke Firebase Realtime Database: setup console, auth, rules, library Firebase_ESP_Client, dan bandingkan dengan MQTT Seri 2.',
            ]
        );

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', [
            'esp32', 'firebase', 'iot', 'wifi', 'sensor', 'mqtt',
        ])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command->info('✓ Artikel ke-30 berhasil dipublish: ' . $article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — Cloud Tanpa Server Sendiri</h2>
<p>Sejauh ini Seri 2 mengandalkan <strong>broker MQTT sendiri</strong> (<a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Mosquitto #16</a>) atau web server lokal. Itu bagus untuk kontrol penuh — tapi butuh VPS/Raspberry Pi yang selalu online.</p>

<p><strong>Firebase Realtime Database</strong> menawarkan backend cloud Google: ESP32 cukup push JSON lewat HTTPS, data langsung tersedia di console atau app mobile. Artikel <strong>Jalur E</strong> ini melengkapi <a href="/artikel/migrasi-platformio-esp32-vscode-project-rapi">PlatformIO (#29)</a> — cocok untuk prototipe cepat tanpa maintain server.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> Sudah bisa <a href="/artikel/menghubungkan-esp32-wifi-kirim-data-server">koneksi WiFi ESP32 (#4)</a>, baca <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">DHT22 (#5)</a>, dan paham perbedaan push vs pull di <a href="/artikel/rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32">REST vs MQTT (#20)</a>.</p>
</blockquote>

<h2>Firebase vs MQTT vs REST — Kapan Pakai?</h2>
<table>
  <thead>
    <tr><th>Pendekatan</th><th>Cocok untuk</th><th>Contoh Seri 2</th></tr>
  </thead>
  <tbody>
    <tr><td><strong>MQTT + broker sendiri</strong></td><td>Kontrol penuh, banyak device, offline LAN</td><td><a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">#7</a>, <a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">Grafana #19</a></td></tr>
    <tr><td><strong>REST / HTTP</strong></td><td>Request-response, API sederhana</td><td><a href="/artikel/membuat-web-server-esp32-monitoring-sensor-dht22">Web server #6</a></td></tr>
    <tr><td><strong>Firebase Realtime DB</strong></td><td>Prototipe cloud, sync real-time ke app</td><td>Artikel ini (#30)</td></tr>
  </tbody>
</table>

<p>Firebase <strong>bukan pengganti</strong> MQTT untuk industri — latency dan biaya skala besar perlu dipertimbangkan. Tapi untuk belajar cloud IoT atau dashboard mobile cepat, Firebase sangat praktis.</p>

<h2>Arsitektur Project</h2>
<pre><code>┌─────────────┐     WiFi/HTTPS      ┌──────────────────────┐
│  ESP32      │ ──────────────────► │ Firebase Realtime DB │
│  + DHT22    │   JSON push         │  /kodingindonesia/... │
└─────────────┘                     └──────────┬───────────┘
                                               │
                                    ┌──────────▼───────────┐
                                    │ Console / App / Web    │
                                    └────────────────────────┘</code></pre>

<p>Node ESP32 membaca sensor, format JSON (dengan timestamp dari <a href="/artikel/ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt">NTP #34</a> jika perlu), lalu <code>set()</code> ke path database.</p>

<h2>Buat Project di Firebase Console</h2>
<ol>
  <li>Buka <strong>console.firebase.google.com</strong> → Add project</li>
  <li>Nama: <code>kindo-esp32-demo</code> (contoh)</li>
  <li>Nonaktifkan Google Analytics jika hanya untuk lab</li>
  <li>Build → <strong>Realtime Database</strong> → Create Database</li>
  <li>Pilih region dekat Indonesia (mis. <code>asia-southeast1</code>)</li>
  <li>Mode awal: <strong>locked</strong> — kita atur rules manual</li>
</ol>

<p>Catat <strong>Database URL</strong> (contoh format):</p>
<pre><code class="language-text">https://GANTI_PROJECT_ID-default-rtdb.asia-southeast1.firebasedatabase.app</code></pre>

<p>Di Project settings → General, salin <strong>Web API Key</strong> — jangan commit ke Git publik.</p>

<h2>Authentication — Email/Password</h2>
<p>Untuk ESP32, pola umum adalah <strong>Firebase Authentication</strong> dengan akun layanan khusus device:</p>
<ol>
  <li>Build → Authentication → Get started → Email/Password → Enable</li>
  <li>Tambah user: <code>device-esp32@contoh.local</code> / password kuat</li>
  <li>ESP32 login sekali saat boot, dapat token, lalu tulis ke Realtime DB</li>
</ol>

<p>Placeholder di sketch (ganti sebelum upload):</p>
<ul>
  <li><code>GANTI_FIREBASE_API_KEY</code></li>
  <li><code>GANTI_FIREBASE_DATABASE_URL</code></li>
  <li><code>GANTI_FIREBASE_USER_EMAIL</code></li>
  <li><code>GANTI_FIREBASE_USER_PASSWORD</code></li>
</ul>

<h2>Realtime Database Rules</h2>
<p>Rules produksi minimal — hanya user terautentikasi yang boleh tulis path sensor:</p>
<pre><code class="language-json">{
  "rules": {
    "kodingindonesia": {
      "esp32": {
        "dht22": {
          "data": {
            ".read": "auth != null",
            ".write": "auth != null"
          }
        }
      }
    }
  }
}</code></pre>

<blockquote>
  <p><strong>Pro tip:</strong> Jangan pakai <code>".read": true, ".write": true</code> di production — data sensor bisa dibaca/ditulis siapa saja. Untuk lab 5 menit saja, lalu kunci rules.</p>
</blockquote>

<h2>Memahami Struktur Payload JSON</h2>
<p>Payload yang dikirim ESP32 ke path <code>/kodingindonesia/esp32/dht22/data</code> sebaiknya konsisten dengan format MQTT Seri 2 — memudahkan tim yang sudah punya subscriber Python (#18) atau dashboard Grafana (#19) untuk membandingkan sumber data.</p>

<p>Field minimal yang disarankan:</p>
<ul>
  <li><code>temperature</code> — float °C, satu desimal cukup untuk DHT22</li>
  <li><code>humidity</code> — float % RH</li>
  <li><code>unix</code> — epoch detik (contoh statis <code>1782977400</code> di artikel ini; produksi pakai NTP #34)</li>
  <li><code>iso</code> — string ISO 8601 untuk debug manusia (<code>2026-07-02T14:30:00</code>)</li>
  <li><code>source</code> — identitas device, mis. <code>esp32</code> atau <code>esp32-greenhouse-01</code></li>
</ul>

<p>Firebase menyimpan nilai sebagai <strong>string JSON</strong> di node leaf jika kamu pakai <code>setString()</code> — itu normal. Alternatif <code>setJSON()</code> memecah field menjadi child node; pilih satu pola dan dokumentasikan ke tim agar parser app mobile tidak bingung.</p>

<p>Jika suhu tiba-tiba <code>null</code> di console, cek Serial Monitor: biasanya DHT22 gagal baca (<code>isnan</code>) atau WiFi drop sehingga <code>Firebase.ready()</code> belum true. Pola retry sama seperti troubleshooting di <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">artikel DHT22 (#5)</a>.</p>

<h2>Library — Firebase_ESP_Client</h2>
<p>Di Arduino IDE: Library Manager → cari <strong>Firebase ESP Client</strong> (Mobizt). Di <a href="/artikel/migrasi-platformio-esp32-vscode-project-rapi">PlatformIO (#29)</a>:</p>
<pre><code class="language-ini">[env:esp32dev]
platform = espressif32
board = esp32dev
framework = arduino
monitor_speed = 115200

lib_deps =
  mobizt/Firebase ESP Client @ ^4.4
  adafruit/DHT sensor library @ ^1.4
  adafruit/Adafruit Unified Sensor @ ^1.1</code></pre>

<h2>Sketch ESP32 — WiFi + Firebase</h2>
<pre><code class="language-cpp">#include &lt;Arduino.h&gt;
#include &lt;WiFi.h&gt;
#include &lt;Firebase_ESP_Client.h&gt;
#include &lt;DHT.h&gt;

#define DHTPIN 4
#define DHTTYPE DHT22
DHT dht(DHTPIN, DHTTYPE);

#define API_KEY "GANTI_FIREBASE_API_KEY"
#define DATABASE_URL "GANTI_FIREBASE_DATABASE_URL"
#define USER_EMAIL "GANTI_FIREBASE_USER_EMAIL"
#define USER_PASSWORD "GANTI_FIREBASE_USER_PASSWORD"

const char* ssid = "GANTI_NAMA_WIFI";
const char* password = "GANTI_PASSWORD_WIFI";

FirebaseData fbdo;
FirebaseAuth auth;
FirebaseConfig config;

void setup() {
  Serial.begin(115200);
  dht.begin();
  delay(2000);

  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) { delay(500); Serial.print("."); }
  Serial.println("\nWiFi OK");

  config.api_key = API_KEY;
  config.database_url = DATABASE_URL;
  auth.user.email = USER_EMAIL;
  auth.user.password = USER_PASSWORD;

  Firebase.begin(&amp;config, &amp;auth);
  Firebase.reconnectWiFi(true);
}

void loop() {
  float t = dht.readTemperature();
  float h = dht.readHumidity();
  if (isnan(t) || isnan(h)) { delay(5000); return; }

  String json = "{\"temperature\":" + String(t, 1) +
    ",\"humidity\":" + String(h, 1) +
    ",\"unix\":1782977400" +
    ",\"iso\":\"2026-07-02T14:30:00\"" +
    ",\"source\":\"esp32\"}";

  if (Firebase.RTDB.setString(&amp;fbdo, "/kodingindonesia/esp32/dht22/data", json)) {
    Serial.println("Firebase OK");
  } else {
    Serial.println(fbdo.errorReason());
  }
  delay(30000);
}</code></pre>

<p>Path <code>/kodingindonesia/esp32/dht22/data</code> konsisten dengan topic MQTT Seri 2 — memudahkan migrasi mental dari <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">MQTT #7</a>.</p>

<h2>Verifikasi di Firebase Console</h2>
<ol>
  <li>Buka Realtime Database → tab Data</li>
  <li>Refresh — muncul tree <code>kodingindonesia → esp32 → dht22 → data</code></li>
  <li>Klik node <code>data</code> — JSON suhu/kelembaban terlihat</li>
  <li>Opsional: export JSON untuk bandingkan dengan payload MQTT di broker <code>192.168.1.50</code></li>
</ol>

<h2>Hybrid: Firebase + MQTT (Konsep)</h2>
<p>Di lapangan besar, banyak tim memakai <strong>keduanya</strong>:</p>
<ul>
  <li>MQTT lokal untuk automasi cepat (relay, PIR #24)</li>
  <li>Firebase untuk app mobile penjaga kebun</li>
  <li>Gateway seperti <a href="/artikel/gateway-lora-mqtt-esp32-sensor-jarak-jauh-dashboard">LoRa #28</a> tetap ke Mosquitto; cloud bridge terpisah</li>
</ul>

<p>Baca <a href="/artikel/rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32">#20</a> sebelum memutuskan satu stack saja.</p>

<h2>Keamanan &amp; Produksi</h2>
<ul>
  <li>Jangan commit <code>GANTI_FIREBASE_*</code> — pakai <code>build_flags</code> PlatformIO atau NVS (#12)</li>
  <li>Aktifkan rules ketat; rotasi password device berkala</li>
  <li>Untuk TLS mendalam di ESP32, pelajari <a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">MQTT TLS (#17)</a> — Firebase client sudah HTTPS</li>
  <li>Pantau quota Spark (free tier) di console</li>
</ul>

<h2>Estimasi Biaya</h2>
<table>
  <thead>
    <tr><th>Item</th><th>Biaya</th></tr>
  </thead>
  <tbody>
    <tr><td>Firebase Spark (free tier)</td><td>Rp 0 untuk prototipe kecil</td></tr>
    <tr><td>ESP32 + DHT22 (sudah punya)</td><td>Rp 0 tambahan</td></tr>
    <tr><td>Skala produksi / banyak GB transfer</td><td>Upgrade Blaze — cek kalkulator Firebase</td></tr>
  </tbody>
</table>

<h2>Checklist Sebelum Go-Live</h2>
<ol>
  <li>Rules database bukan mode test terbuka?</li>
  <li>Credential device bukan password pribadi kamu?</li>
  <li>Path data konsisten dengan dokumentasi tim?</li>
  <li>Interval publish tidak terlalu agresif (hemat quota)?</li>
  <li>Ada rencana backup jika vendor lock-in jadi masalah?</li>
</ol>

<h2>Uji Coba</h2>
<ol>
  <li>Buat project Firebase + Realtime DB</li>
  <li>Enable Email/Password auth + user device</li>
  <li>Upload sketch — Serial: WiFi OK + Firebase OK</li>
  <li>Data muncul di console dalam 30 detik</li>
  <li>Ubah rules ke locked — pastikan tanpa auth gagal write</li>
</ol>

<h2>Membaca Data dari Web (Tanpa App Native)</h2>
<p>Firebase Console sudah cukup untuk lab. Untuk dashboard sederhana, kamu bisa pakai <strong>Firebase Hosting</strong> + JavaScript SDK — di luar scope artikel ini, tapi konsepnya: web page subscribe <code>onValue()</code> ke path <code>/kodingindonesia/esp32/dht22/data</code> dan update DOM setiap ada perubahan.</p>

<p>Alternatif: export berkala ke <a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">MySQL (#18)</a> jika tim sudah punya pipeline SQL — Firebase jadi buffer real-time, ETL ke histori.</p>

<h2>PlatformIO vs Arduino IDE untuk Firebase</h2>
<p>Library <code>Firebase_ESP_Client</code> cukup besar — compile pertama bisa 2–5 menit. Di project <a href="/artikel/migrasi-platformio-esp32-vscode-project-rapi">PlatformIO (#29)</a>, pin versi di <code>lib_deps</code> agar build reproducible:</p>
<pre><code class="language-ini">lib_deps =
  mobizt/Firebase ESP Client @ 4.4.14
  adafruit/DHT sensor library @ ^1.4</code></pre>

<p>Partition scheme ESP32: jika sketch besar, pilih <strong>Huge APP</strong> di board menu atau tambahkan <code>board_build.partitions = huge_app.csv</code> di PlatformIO.</p>

<h2>Migrasi Mental dari MQTT ke Firebase</h2>
<p>Jika kamu sudah punya node MQTT yang publish ke <code>kodingindonesia/esp32/dht22/data</code>, berikut peta konsepnya:</p>
<table>
  <thead>
    <tr><th>MQTT (#7)</th><th>Firebase (#30)</th></tr>
  </thead>
  <tbody>
    <tr><td>Broker <code>192.168.1.50</code></td><td>Database URL Google</td></tr>
    <tr><td>Topic path</td><td>Node path (sama: <code>/kodingindonesia/esp32/dht22/data</code>)</td></tr>
    <tr><td><code>mosquitto_pub</code> / subscriber</td><td>Console / SDK <code>onValue</code></td></tr>
    <tr><td>User <code>kindo_esp32</code> + <code>GANTI_PASSWORD_MQTT</code></td><td>Email device + rules auth</td></tr>
    <tr><td>Retained / LWT (#17)</td><td>Last value tetap di node sampai overwrite</td></tr>
  </tbody>
</table>

<p>Node yang sudah jalan di MQTT <strong>tidak perlu dimatikan</strong> — Firebase bisa jadi saluran paralel untuk app mobile, sementara automasi relay tetap lewat broker lokal. Pola ini umum di smart home skala rumah sebelum tim memutuskan satu vendor cloud.</p>

<p>Untuk timestamp live (bukan contoh statis <code>1782977400</code>), integrasikan <a href="/artikel/ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt">NTP #34</a> di <code>setup()</code> sebelum loop publish — payload JSON jadi siap untuk grafik Grafana (#19) maupun audit trail.</p>

<h2>FAQ Singkat</h2>
<dl>
  <dt><strong>Firebase menggantikan Grafana?</strong></dt>
  <dd>Tidak — Grafana (#19) untuk grafik histori MQTT; Firebase untuk sync real-time ke app.</dd>
  <dt><strong>Bisa pakai Arduino IDE tanpa PlatformIO?</strong></dt>
  <dd>Ya — library sama; #29 opsional tapi disarankan untuk project besar.</dd>
  <dt><strong>Perlu kartu kredit?</strong></dt>
  <dd>Spark plan gratis tanpa kartu untuk belajar; Blaze butuh billing untuk beberapa fitur.</dd>
</dl>

<h2>Tips &amp; Troubleshooting</h2>
<ul>
  <li><strong>auth/network-request-failed:</strong> Cek SSID 2.4 GHz, bukan guest network terisolasi</li>
  <li><strong>Permission denied:</strong> Rules atau token auth salah — login ulang device user di Authentication tab</li>
  <li><strong>DHT22 nan:</strong> Ulangi <code>delay(2000)</code> setelah <code>dht.begin()</code> seperti #5</li>
  <li><strong>Token expired:</strong> Panggil <code>Firebase.ready()</code> di loop atau refresh sesuai docs library</li>
  <li><strong>Heap low / crash:</strong> Kurangi buffer SSL — matikan fitur Firebase yang tidak dipakai; pertimbangkan partition Huge APP</li>
  <li><strong>Data tidak update:</strong> Pastikan path diawali <code>/</code> dan tidak ada typo <code>dht22</code> vs <code>dht-22</code></li>
  <li><strong>Quota exceeded:</strong> Perpanjang interval <code>delay(30000)</code> menjadi 60–120 detik untuk prototipe</li>
</ul>

<p>Untuk debug rules tanpa upload ulang sketch, gunakan tab <strong>Rules Playground</strong> di Firebase Console — simulasikan read/write dengan UID user device sebelum deploy rules ke production. Simpan screenshot hasil simulasi di dokumentasi tim agar onboarding anggota baru lebih cepat.</p>

<h2>Langkah Selanjutnya (Jalur E)</h2>
<ul>
  <li><strong>FreeRTOS (#31):</strong> task terpisah sensor / WiFi / cloud</li>
  <li><strong><a href="/artikel/ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt">NTP (#34)</a></strong> — timestamp live, bukan contoh statis</li>
  <li><strong><a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">Python → MySQL (#18)</a></strong> — arsip SQL paralel cloud</li>
  <li><strong><a href="/artikel/ota-update-firmware-esp32-via-wifi">OTA (#15)</a></strong> — update firmware tanpa USB</li>
  <li>Capstone <strong>greenhouse (#39)</strong></li>
</ul>

<p>Dengan Firebase, ESP32 kamu punya jalur cepat ke cloud — tanpa sewa VPS dulu. Lanjutkan Seri 2 di <a href="/artikel">halaman artikel</a> Koding Indonesia.</p>
HTML;
    }
}
