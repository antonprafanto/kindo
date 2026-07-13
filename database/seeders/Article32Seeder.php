<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article32Seeder extends Seeder
{
    public function run(): void
    {
        $admin  = User::first();
        $espCat = Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $espCat) {
            throw new \RuntimeException('User atau kategori tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'bluetooth-esp32-ble-kirim-data-sensor-smartphone';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        Tag::updateOrCreate(['slug' => 'ble'], ['name' => 'ble']);
        Tag::updateOrCreate(['slug' => 'bluetooth'], ['name' => 'bluetooth']);
        Tag::updateOrCreate(['slug' => 'smartphone'], ['name' => 'smartphone']);

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $espCat->id,
                'title'           => 'Bluetooth BLE di ESP32: Kirim Data Sensor ke Smartphone',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'Bluetooth BLE ESP32 — Kirim Data DHT22 ke Smartphone',
                'seo_description' => 'Tutorial BLE ESP32: GATT server, notifikasi JSON suhu & kelembaban DHT22 ke app nRF Connect — tanpa WiFi, router, atau broker MQTT.',
            ]
        );

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', [
            'esp32', 'bluetooth', 'ble', 'iot', 'sensor', 'smartphone',
        ])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command->info('✓ Artikel ke-32 berhasil dipublish: ' . $article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — Janji Bluetooth Terpenuhi</h2>
<p>Di artikel pengenalan <a href="/artikel/mengenal-esp32-mikrokontroler-wifi-bluetooth-iot">Mengenal ESP32 (#1)</a>, kita menyebut ESP32 punya <strong>WiFi dan Bluetooth</strong> dalam satu chip. Seri 1 dan Tier 1 Seri 2 fokus ke WiFi, MQTT, dan cloud — sampai sekarang belum ada tutorial Bluetooth. Artikel <strong>Tier 2</strong> ini menutup celah itu.</p>

<p>Kamu akan membuat <strong>BLE GATT server</strong> di ESP32 yang membaca <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">DHT22 (#5)</a> dan mengirim JSON suhu &amp; kelembaban ke <strong>smartphone</strong> lewat notifikasi BLE — <strong>tanpa router WiFi</strong>, tanpa broker <code>192.168.1.50</code>, tanpa kabel USB setelah upload firmware.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> Sudah pernah upload sketch Arduino (<a href="/artikel/blink-led-esp32-tutorial-pertama-embedded-system">Blink LED #3</a>), paham GPIO dasar, dan pernah baca <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">DHT22 (#5)</a>. Familiar dengan pola JSON di <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">MQTT (#7)</a> membantu membandingkan transport — tapi MQTT tidak wajib untuk lab ini.</p>
</blockquote>

<h2>BLE vs WiFi vs ESP-NOW — Kapan Pakai Apa</h2>
<table>
  <thead>
    <tr><th>Transport</th><th>Jangkauan tipikal</th><th>Infrastruktur</th><th>Cocok untuk</th></tr>
  </thead>
  <tbody>
    <tr><td><strong>BLE</strong></td><td>~5–15 m</td><td>Hanya ESP32 + smartphone</td><td>Setup perangkat, monitoring dekat, app companion</td></tr>
    <tr><td><strong>WiFi + MQTT</strong></td><td>Seluruh jaringan rumah</td><td>Router + <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">broker (#16)</a></td><td>Dashboard 24/7, histori <a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">Grafana (#19)</a></td></tr>
    <tr><td><strong><a href="/artikel/esp-now-kirim-data-antar-esp32-tanpa-router-wifi">ESP-NOW (#25)</a></strong></td><td>~50–200 m (LOS)</td><td>Dua+ ESP32, tanpa router</td><td>Sensor node → gateway ESP32</td></tr>
  </tbody>
</table>

<p>BLE bukan pengganti <a href="/artikel/menghubungkan-esp32-wifi-kirim-data-server">WiFi (#4)</a> atau MQTT — melainkan <strong>saluran langsung ke HP</strong> saat teknisi berdiri di samping perangkat. Kombinasi umum di produksi: BLE untuk provisioning SSID/password, WiFi untuk telemetry harian.</p>

<h2>Arsitektur GATT — Server, Service, Characteristic</h2>
<p><strong>BLE</strong> (Bluetooth Low Energy) memakai model <strong>GATT</strong> (Generic Attribute Profile):</p>
<ul>
  <li><strong>Peripheral (ESP32)</strong> — mengiklankan diri, menyimpan service &amp; characteristic</li>
  <li><strong>Central (smartphone)</strong> — scan, connect, baca/tulis characteristic</li>
  <li><strong>Service</strong> — grup logical (mis. "sensor lingkungan")</li>
  <li><strong>Characteristic</strong> — nilai data aktual (JSON suhu/kelembaban)</li>
  <li><strong>Notify</strong> — ESP32 push data otomatis saat terhubung (butuh descriptor <code>BLE2902</code>)</li>
</ul>

<p>Urutan komunikasi dari scan sampai data sensor masuk ke HP — bayangkan dua pihak berbicara lewat Bluetooth:</p>

<figure role="img" aria-label="Diagram urutan BLE: smartphone scan, connect, subscribe notify, terima JSON sensor dari ESP32" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 780 400" style="display:block;max-width:780px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="bleArrowR" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto">
      <path d="M0,0 L8,4 L0,8 Z" fill="#1a1a1a"/>
    </marker>
    <marker id="bleArrowL" markerWidth="8" markerHeight="8" refX="1" refY="4" orient="auto">
      <path d="M8,0 L0,4 L8,8 Z" fill="#1a1a1a"/>
    </marker>
  </defs>
  <rect x="0" y="0" width="780" height="400" fill="#F5F5F0" rx="6"/>
  <!-- Aktor -->
  <rect x="48" y="20" width="220" height="56" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="158" y="44" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">Smartphone</text>
  <text x="158" y="62" text-anchor="middle" fill="#4A5568" font-size="11">Central — nRF Connect / app</text>
  <rect x="512" y="20" width="220" height="56" rx="6" fill="#2979FF" stroke="#000" stroke-width="2.5"/>
  <text x="622" y="44" text-anchor="middle" fill="#fff" font-size="14" font-weight="700">ESP32</text>
  <text x="622" y="62" text-anchor="middle" fill="#e3f2fd" font-size="11">Peripheral — GATT Server</text>
  <!-- Lifelines -->
  <line x1="158" y1="76" x2="158" y2="360" stroke="#CBD5E0" stroke-width="2" stroke-dasharray="6 4"/>
  <line x1="622" y1="76" x2="622" y2="360" stroke="#CBD5E0" stroke-width="2" stroke-dasharray="6 4"/>
  <!-- Step 1 -->
  <circle cx="36" cy="108" r="14" fill="#2979FF" stroke="#000" stroke-width="2"/>
  <text x="36" y="113" text-anchor="middle" fill="#fff" font-size="12" font-weight="700">1</text>
  <rect x="72" y="90" width="172" height="36" rx="4" fill="#fff" stroke="#CBD5E0" stroke-width="1.5"/>
  <text x="158" y="108" text-anchor="middle" fill="#1a1a1a" font-size="11" font-weight="600">Scan</text>
  <text x="158" y="122" text-anchor="middle" fill="#4A5568" font-size="10">cari &quot;KindoESP32-DHT22&quot;</text>
  <!-- Step 2 -->
  <circle cx="36" cy="168" r="14" fill="#2979FF" stroke="#000" stroke-width="2"/>
  <text x="36" y="173" text-anchor="middle" fill="#fff" font-size="12" font-weight="700">2</text>
  <line x1="200" y1="168" x2="580" y2="168" stroke="#1a1a1a" stroke-width="2" marker-end="url(#bleArrowR)"/>
  <text x="390" y="158" text-anchor="middle" fill="#4A5568" font-size="11" font-weight="600">connect</text>
  <text x="622" y="188" text-anchor="middle" fill="#718096" font-size="10">onConnect → deviceConnected=true</text>
  <!-- Step 3 -->
  <circle cx="36" cy="238" r="14" fill="#FF7A2F" stroke="#000" stroke-width="2"/>
  <text x="36" y="243" text-anchor="middle" fill="#fff" font-size="12" font-weight="700">3</text>
  <line x1="580" y1="238" x2="200" y2="238" stroke="#1a1a1a" stroke-width="2" marker-end="url(#bleArrowL)"/>
  <text x="390" y="228" text-anchor="middle" fill="#4A5568" font-size="11" font-weight="600">subscribe notify</text>
  <text x="390" y="258" text-anchor="middle" fill="#718096" font-size="10">aktifkan ikon notify di characteristic</text>
  <!-- Step 4 loop -->
  <circle cx="36" cy="318" r="14" fill="#FF7A2F" stroke="#000" stroke-width="2"/>
  <text x="36" y="323" text-anchor="middle" fill="#fff" font-size="12" font-weight="700">4</text>
  <line x1="580" y1="308" x2="200" y2="308" stroke="#1a1a1a" stroke-width="2" marker-end="url(#bleArrowL)"/>
  <text x="390" y="298" text-anchor="middle" fill="#4A5568" font-size="11" font-weight="600">notify JSON tiap ~2 detik</text>
  <rect x="480" y="318" width="284" height="40" rx="4" fill="#fff" stroke="#FF7A2F" stroke-width="2"/>
  <text x="622" y="336" text-anchor="middle" fill="#1a1a1a" font-size="10" font-family="monospace">{&quot;suhu&quot;:28.5,&quot;kelembaban&quot;:62,&quot;unix&quot;:…}</text>
  <text x="622" y="350" text-anchor="middle" fill="#718096" font-size="10">DHT22 baca → setValue → notify()</text>
  <path d="M 200 340 Q 158 370 200 340" fill="none" stroke="#718096" stroke-width="1.5" stroke-dasharray="4 3"/>
  <text x="158" y="378" text-anchor="middle" fill="#718096" font-size="9">ulang selama terhubung</text>
</svg>
<figcaption style="margin-top:.75rem;font-size:.875rem;color:#718096;text-align:center">Diagram urutan BLE — HP memulai scan &amp; connect; ESP32 mengirim data sensor lewat notifikasi GATT.</figcaption>
</figure>

<h2>UUID Kustom Koding Indonesia</h2>
<p>Gunakan UUID 128-bit kustom agar tidak bentrok dengan service standar (Battery Service, dll.):</p>
<ul>
  <li><strong>Service:</strong> <code>b10d4001-0001-4001-8001-000032000001</code></li>
  <li><strong>Characteristic:</strong> <code>b10d4002-0002-4002-8002-000032000002</code></li>
  <li><strong>Nama perangkat:</strong> <code>KindoESP32-DHT22</code> — muncul saat scan di app</li>
</ul>

<p>Simpan UUID ini di dokumentasi proyek internal — app Android/iOS custom nanti memakai nilai yang sama.</p>

<h2>Hardware &amp; Wiring DHT22</h2>
<p>Sama seperti artikel <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">DHT22 (#5)</a>:</p>
<ul>
  <li><strong>VCC</strong> → 3.3 V</li>
  <li><strong>GND</strong> → GND</li>
  <li><strong>DATA</strong> → <strong>GPIO 4</strong></li>
  <li>Resistor pull-up 4,7–10 kΩ antara DATA dan VCC (banyak modul sudah onboard)</li>
</ul>

<p>ESP32 DevKit cukup — tidak perlu modul Bluetooth eksternal. Chip sudah punya radio BLE seperti dijelaskan di <a href="/artikel/mengenal-esp32-mikrokontroler-wifi-bluetooth-iot">#1</a>.</p>

<h2>Library &amp; Dependensi</h2>
<p>Di <strong>Arduino IDE</strong>, library BLE sudah termasuk di core ESP32 — tidak perlu install tambahan:</p>
<ul>
  <li><code>BLEDevice.h</code>, <code>BLEServer.h</code>, <code>BLEUtils.h</code>, <code>BLE2902.h</code></li>
  <li><code>DHT sensor library</code> oleh Adafruit (sama seperti <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">#5</a>)</li>
</ul>

<p>Di <a href="/artikel/migrasi-platformio-esp32-vscode-project-rapi">PlatformIO (#29)</a>, tambahkan di <code>platformio.ini</code>:</p>
<pre><code class="language-ini">lib_deps =
  adafruit/DHT sensor library
  adafruit/Adafruit Unified Sensor</code></pre>

<h2>Sketch BLE Server — Struktur Utama</h2>
<p>Alur <code>setup()</code>:</p>
<ol>
  <li><code>BLEDevice::init("KindoESP32-DHT22")</code></li>
  <li>Buat server + callback connect/disconnect</li>
  <li>Buat service &amp; characteristic (READ + NOTIFY)</li>
  <li>Tambah descriptor <code>BLE2902</code> untuk notifikasi</li>
  <li><code>startAdvertising()</code> — ESP32 siap discan</li>
</ol>

<h2>Sketch Lengkap — BLE + DHT22</h2>
<pre><code class="language-cpp">#include &lt;BLEDevice.h&gt;
#include &lt;BLEServer.h&gt;
#include &lt;BLEUtils.h&gt;
#include &lt;BLE2902.h&gt;
#include &lt;DHT.h&gt;

#define DHT_PIN  4
#define DHT_TYPE DHT22

#define SERVICE_UUID        "b10d4001-0001-4001-8001-000032000001"
#define CHARACTERISTIC_UUID "b10d4002-0002-4002-8002-000032000002"

DHT dht(DHT_PIN, DHT_TYPE);
BLECharacteristic* pCharacteristic = nullptr;
bool deviceConnected = false;

class ServerCallbacks : public BLEServerCallbacks {
  void onConnect(BLEServer* server) {
    deviceConnected = true;
    Serial.println("BLE client terhubung");
  }
  void onDisconnect(BLEServer* server) {
    deviceConnected = false;
    Serial.println("BLE client putus — advertising ulang");
    BLEDevice::startAdvertising();
  }
};

void setup() {
  Serial.begin(115200);
  dht.begin();
  delay(2000);  // stabilisasi DHT22 — sama seperti #5

  BLEDevice::init("KindoESP32-DHT22");
  BLEServer* server = BLEDevice::createServer();
  server-&gt;setCallbacks(new ServerCallbacks());

  BLEService* service = server-&gt;createService(SERVICE_UUID);
  pCharacteristic = service-&gt;createCharacteristic(
    CHARACTERISTIC_UUID,
    BLECharacteristic::PROPERTY_READ | BLECharacteristic::PROPERTY_NOTIFY
  );
  pCharacteristic-&gt;addDescriptor(new BLE2902());
  service-&gt;start();

  BLEAdvertising* advertising = BLEDevice::getAdvertising();
  advertising-&gt;addServiceUUID(SERVICE_UUID);
  advertising-&gt;setScanResponse(true);
  BLEDevice::startAdvertising();

  Serial.println("BLE advertising — buka nRF Connect di HP");
}

void loop() {
  if (!deviceConnected) {
    delay(500);
    return;
  }

  float suhu = dht.readTemperature();
  float kelembaban = dht.readHumidity();

  if (isnan(suhu) || isnan(kelembaban)) {
    Serial.println("DHT22 baca gagal — cek wiring GPIO4");
    delay(2000);
    return;
  }

  // Unix contoh konsisten Seri 2 — production: NTP (#34)
  char json[96];
  snprintf(json, sizeof(json),
    "{\"suhu\":%.1f,\"kelembaban\":%.1f,\"unix\":%lu}",
    suhu, kelembaban, (unsigned long)1782977400UL);

  pCharacteristic-&gt;setValue(json);
  pCharacteristic-&gt;notify();
  Serial.println(json);

  delay(2000);
}</code></pre>

<p>Payload JSON mengikuti konvensi topic MQTT <code>kodingindonesia/esp32/dht22/data</code> — memudahkan port ke <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">MQTT (#7)</a> nanti. Timestamp contoh <code>1782977400</code> = <code>2026-07-02T14:30:00</code> UTC (lihat <a href="/artikel/ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt">NTP #34</a>).</p>

<h2>Memahami GATT — Istilah di nRF Connect</h2>
<p>Saat membuka nRF Connect, kamu akan melihat istilah berikut:</p>
<ul>
  <li><strong>Advertising</strong> — ESP32 mengirim beacon "saya di sini" dengan nama <code>KindoESP32-DHT22</code></li>
  <li><strong>Connection</strong> — smartphone dan ESP32 menyetujui sesi; callback <code>onConnect</code> di sketch</li>
  <li><strong>Primary Service</strong> — wadah UUID <code>b10d4001-...</code></li>
  <li><strong>Characteristic</strong> — nilai JSON; properti <code>READ</code> + <code>NOTIFY</code></li>
  <li><strong>CCC (Client Characteristic Configuration)</strong> — descriptor <code>BLE2902</code> yang mengaktifkan notifikasi</li>
</ul>

<p>Tidak perlu hafal seluruh spesifikasi Bluetooth SIG untuk proyek sensor — cukup paham alur <strong>advertise → connect → subscribe notify → terima JSON</strong>.</p>

<h2>Callback Connect &amp; Disconnect</h2>
<p>Kelas <code>ServerCallbacks</code> menangani siklus hidup koneksi:</p>
<ul>
  <li><strong>onConnect</strong> — set <code>deviceConnected = true</code>; mulai kirim notifikasi di <code>loop()</code></li>
  <li><strong>onDisconnect</strong> — set flag false; panggil <code>BLEDevice::startAdvertising()</code> agar perangkat bisa discan lagi</li>
</ul>

<p>Tanpa restart advertising setelah disconnect, smartphone tidak akan menemukan ESP32 di scan berikutnya — bug umum pemula BLE.</p>

<h2>Testing dengan nRF Connect</h2>
<p><strong>nRF Connect</strong> (Nordic Semiconductor) gratis di Android &amp; iOS — standar de facto untuk debug BLE:</p>
<ol>
  <li>Upload sketch, buka Serial Monitor 115200</li>
  <li>Di HP: buka nRF Connect → tab <strong>Scanner</strong></li>
  <li>Cari <code>KindoESP32-DHT22</code> → tap <strong>Connect</strong></li>
  <li>Buka service UUID <code>b10d4001-...</code></li>
  <li>Tap characteristic → aktifkan <strong>Notify</strong> (ikon tiga panah)</li>
  <li>Lihat JSON masuk setiap ~2 detik: <code>{"suhu":28.5,"kelembaban":62.0,"unix":1782977400}</code></li>
</ol>

<blockquote>
  <p><strong>Pro tip:</strong> Jika tidak muncul di scan, pastikan Bluetooth HP aktif, ESP32 tidak sedang connect USB ke laptop yang juga punya BLE stack aktif, dan coba reboot board setelah upload.</p>
</blockquote>

<h2>Testing dengan Serial Monitor</h2>
<p>Serial mencetak status connect/disconnect dan setiap JSON yang di-notify. Urutan debug yang disarankan:</p>
<ol>
  <li>Pastikan <code>BLE advertising</code> muncul setelah boot</li>
  <li>Connect dari HP → harus muncul <code>BLE client terhubung</code></li>
  <li>Jika DHT22 gagal → perbaiki wiring sebelum blame BLE</li>
  <li>Putuskan HP → harus <code>advertising ulang</code> otomatis</li>
</ol>

<h2>BLE + WiFi Bersamaan di ESP32</h2>
<p>ESP32 <em>bisa</em> menjalankan BLE dan WiFi bersamaan (coexistence), tapi:</p>
<ul>
  <li>Radio berbagi antena — throughput turun, timing lebih sensitif</li>
  <li>Sketch gabungan BLE provisioning + MQTT lebih kompleks — pertimbangkan <a href="/artikel/freertos-esp32-multi-task-sensor-wifi-mqtt">FreeRTOS (#31)</a> untuk pisah task</li>
  <li>Artikel ini sengaja <strong>BLE-only</strong> agar fokus; tambah WiFi setelah pola GATT paham</li>
</ul>

<h2>Jangkauan, Latency &amp; Daya</h2>
<ul>
  <li><strong>Jangkauan:</strong> 5–15 m di dalam ruangan; dinding beton mengurangi drastis</li>
  <li><strong>Latency notify:</strong> &lt;100 ms — cocok untuk display real-time di app</li>
  <li><strong>Daya:</strong> Advertising terus-menerus boros baterai; untuk node baterai kombinasikan <a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">deep sleep (#11)</a> + wake singkat saat ada koneksi</li>
  <li><strong>Throughput:</strong> JSON kecil (&lt;100 byte) jauh di bawah limit BLE — aman</li>
</ul>

<h2>Perbandingan dengan Pola Seri 2 Lain</h2>
<p>Jika tujuanmu dashboard 24 jam di server, tetap pakai MQTT ke broker dan <a href="/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22">capstone #10</a>. BLE cocok untuk:</p>
<ul>
  <li>Teknisi lapangan cek suhu tanpa buka laptop</li>
  <li>Onboarding: kirim SSID <code>GANTI_NAMA_WIFI</code> dari app (lanjutan di <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">WiFiManager #12</a>)</li>
  <li>Demo cepat di pameran sekolah/kampus tanpa infrastruktur WiFi</li>
</ul>

<h2>Keamanan BLE Dasar</h2>
<ul>
  <li>BLE tanpa pairing di artikel ini = <strong>data terbuka</strong> untuk siapa saja dalam jangkauan — OK untuk lab, tidak untuk data sensitif</li>
  <li>Produksi: aktifkan <strong>LE Secure Connections</strong> + pairing PIN atau Just Works sesuai threat model</li>
  <li>Jangan kirim kredensial MQTT (<code>GANTI_PASSWORD_MQTT</code>) mentah lewat characteristic tanpa enkripsi</li>
  <li>Nama perangkat generik (<code>KindoESP32-DHT22</code>) lebih aman daripada nama lokasi spesifik di publik</li>
</ul>

<h2>Estimasi Biaya</h2>
<table>
  <thead>
    <tr><th>Komponen</th><th>Harga perkiraan (IDR)</th></tr>
  </thead>
  <tbody>
    <tr><td>ESP32 DevKit</td><td>35.000 – 55.000</td></tr>
    <tr><td>Modul DHT22</td><td>15.000 – 25.000</td></tr>
    <tr><td>Smartphone (sudah punya)</td><td>0</td></tr>
    <tr><td><strong>Total</strong></td><td><strong>~50.000 – 80.000</strong></td></tr>
  </tbody>
</table>

<h2>Checklist Sebelum Demo</h2>
<ul>
  <li>☐ Sketch ter-upload tanpa error compile</li>
  <li>☐ Serial menampilkan <code>BLE advertising</code></li>
  <li>☐ DHT22 baca valid (bukan <code>nan</code>) di Serial</li>
  <li>☐ nRF Connect bisa connect ke <code>KindoESP32-DHT22</code></li>
  <li>☐ Notify aktif — JSON update tiap 2 detik</li>
  <li>☐ UUID service &amp; characteristic cocok dengan dokumentasi proyek</li>
</ul>

<h2>FAQ Singkat</h2>
<dl>
  <dt><strong>Perlu internet?</strong></dt>
  <dd>Tidak. BLE langsung ESP32 ↔ smartphone.</dd>
  <dt><strong>Bisa pakai app buatan sendiri?</strong></dt>
  <dd>Ya — Flutter/React Native dengan plugin BLE; UUID harus sama dengan sketch.</dd>
  <dt><strong>Bluetooth Classic vs BLE?</strong></dt>
  <dd>ESP32 mendukung keduanya; IoT sensor hampir selalu pakai <strong>BLE</strong> karena hemat daya.</dd>
  <dt><strong>Bentrok dengan WiFi sketch lama?</strong></dt>
  <dd>Sketch ini tidak init WiFi — upload terpisah atau gabung manual dengan pola coexistence.</dd>
</dl>

<h2>Tips &amp; Troubleshooting</h2>
<ul>
  <li><strong>Tidak muncul di scan:</strong> Cek <code>BLEDevice::init</code> terpanggil; reboot ESP32; matikan BLE laptop jika bentrok</li>
  <li><strong>Connect langsung putus:</strong> Naikkan interval notify; kurangi beban Serial print</li>
  <li><strong>Notify kosong:</strong> Pastikan client subscribe (ikon notify aktif di nRF Connect)</li>
  <li><strong>DHT22 nan:</strong> Cek GPIO4, pull-up, dan <code>delay(2000)</code> setelah <code>dht.begin()</code></li>
  <li><strong>iOS tidak connect:</strong> iOS butuh UUID valid 128-bit — jangan pakai UUID 16-bit saja</li>
  <li><strong>Heap menurun:</strong> Hindari <code>new</code> berulang di <code>loop()</code>; callback cukup sekali di setup</li>
</ul>

<h2>Langkah Selanjutnya — Tier 2 Seri 2</h2>
<p>Artikel pertama Tier 2 pelengkap selesai — janji Bluetooth dari <a href="/artikel/mengenal-esp32-mikrokontroler-wifi-bluetooth-iot">#1</a> terpenuhi. Lanjutkan ke topik pelengkap:</p>
<ul>
  <li><strong><a href="/artikel/kontrol-servo-pwm-esp32-mqtt-gerakan-presisi">Kontrol Servo &amp; PWM (#33)</a>:</strong> gerakan presisi — melengkapi relay on/off di <a href="/artikel/kontrol-lampu-esp32-mqtt-relay">#8</a></li>
  <li><strong><a href="/artikel/ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt">NTP (#34)</a></strong> — ganti unix contoh dengan waktu nyata di JSON</li>
  <li><strong><a href="/artikel/freertos-esp32-multi-task-sensor-wifi-mqtt">FreeRTOS (#31)</a></strong> — task BLE + task WiFi/MQTT paralel</li>
  <li><strong><a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">WiFiManager (#12)</a></strong> — provisioning WiFi lewat portal web</li>
  <li>Capstone <a href="/artikel/smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt">greenhouse (#39)</a> — sensor multi-saluran + aktuator</li>
</ul>

<p>BLE membuka ESP32 ke dunia smartphone — lanjutkan perjalanan di <a href="/artikel">halaman artikel</a> Koding Indonesia.</p>
HTML;
    }
}
