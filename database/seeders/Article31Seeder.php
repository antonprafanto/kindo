<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article31Seeder extends Seeder
{
    public function run(): void
    {
        $admin  = User::first();
        $espCat = Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $espCat) {
            throw new \RuntimeException('User atau kategori tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'freertos-esp32-multi-task-sensor-wifi-mqtt';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        Tag::updateOrCreate(['slug' => 'freertos'], ['name' => 'freertos']);

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $espCat->id,
                'title'           => 'FreeRTOS di ESP32: Multi-task Sensor + WiFi + MQTT',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'FreeRTOS ESP32 — Multi-task Sensor, WiFi & MQTT',
                'seo_description' => 'Pecah sketch ESP32 jadi task FreeRTOS terpisah: baca DHT22, antrean data, publish MQTT ke Mosquitto — tanpa blocking loop tunggal.',
            ]
        );

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', [
            'esp32', 'freertos', 'mqtt', 'iot', 'wifi', 'sensor',
        ])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command->info('✓ Artikel ke-31 berhasil dipublish: ' . $article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — Kenapa FreeRTOS?</h2>
<p>Di Seri 1, sketch ESP32 biasanya satu <code>loop()</code> besar: baca sensor, reconnect WiFi, publish MQTT, handle web server — semuanya berjalan berurutan. Itu cukup untuk belajar, tapi saat proyek membesar (seperti <a href="/artikel/gabungkan-dht22-relay-mqtt-esp32-satu-proyek">gabungan DHT22 + relay (#9)</a> atau <a href="/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22">dashboard capstone (#10)</a>), satu operasi blocking bisa menggagalkan yang lain.</p>

<p><strong>FreeRTOS</strong> sudah built-in di ESP32 Arduino core. Artikel penutup <strong>Jalur E</strong> ini memecah firmware jadi <strong>task paralel</strong>: task sensor, task MQTT/WiFi, dan antrean data di antaranya — pola yang dipakai firmware produksi sebelum kamu masuk Tier 2 Seri 2.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> Paham <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">MQTT dasar (#7)</a>, sudah punya sketch gabungan <a href="/artikel/gabungkan-dht22-relay-mqtt-esp32-satu-proyek">DHT22 + MQTT (#9)</a>, dan nyaman dengan broker <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Mosquitto (#16)</a>. Familiar <a href="/artikel/migrasi-platformio-esp32-vscode-project-rapi">PlatformIO (#29)</a> membantu tapi opsional.</p>
</blockquote>

<h2>Masalah loop() Tunggal</h2>
<table>
  <thead>
    <tr><th>Skenario</th><th>Tanpa FreeRTOS</th><th>Dengan FreeRTOS</th></tr>
  </thead>
  <tbody>
    <tr><td><code>delay(2000)</code> tunggu DHT22</td><td>MQTT <code>loop()</code> tidak jalan</td><td>Task MQTT tetap jalan di core lain</td></tr>
    <tr><td>Reconnect WiFi 10 detik</td><td>Sensor tidak terbaca</td><td>Task sensor tetap periodic</td></tr>
    <tr><td>Publish JSON besar</td><td>Subscribe relay telat</td><td>Task kontrol terpisah (opsional)</td></tr>
  </tbody>
</table>

<p>Artikel ini fokus pada <strong>sensor → queue → MQTT</strong> — fondasi sebelum kamu menambah relay subscribe seperti di <a href="/artikel/gabungkan-dht22-relay-mqtt-esp32-satu-proyek">#9</a> atau cloud bridge <a href="/artikel/esp32-firebase-realtime-database-sensor-cloud">Firebase (#30)</a>.</p>

<h2>Arsitektur Task</h2>
<figure role="img" aria-label="Diagram arsitektur FreeRTOS ESP32: task sensor Core 1 mengirim data ke queue, task MQTT Core 0 publish ke broker" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 820 370" style="display:block;max-width:820px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="frtArrow" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto">
      <path d="M0,0 L8,4 L0,8 Z" fill="#1a1a1a"/>
    </marker>
  </defs>
  <rect x="0" y="0" width="820" height="370" fill="#F5F5F0" rx="6"/>
  <!-- Core labels -->
  <text x="130" y="36" text-anchor="middle" fill="#2979FF" font-size="11" font-weight="700">Core 1</text>
  <text x="610" y="36" text-anchor="middle" fill="#FF7A2F" font-size="11" font-weight="700">Core 0</text>
  <!-- Task Sensor -->
  <rect x="24" y="48" width="212" height="88" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="130" y="76" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">Task Sensor</text>
  <text x="130" y="96" text-anchor="middle" fill="#4A5568" font-size="12">DHT22 @ GPIO4</text>
  <text x="130" y="114" text-anchor="middle" fill="#718096" font-size="11">baca tiap 5 detik</text>
  <text x="130" y="128" text-anchor="middle" fill="#718096" font-size="10">vTaskDelay · xQueueSend</text>
  <!-- Queue -->
  <rect x="300" y="64" width="180" height="56" rx="6" fill="#fff" stroke="#2979FF" stroke-width="2" stroke-dasharray="6 4"/>
  <text x="390" y="88" text-anchor="middle" fill="#2979FF" font-size="13" font-weight="700">FreeRTOS Queue</text>
  <text x="390" y="106" text-anchor="middle" fill="#4A5568" font-size="11">SensorReading · depth 5</text>
  <!-- Task MQTT -->
  <rect x="544" y="48" width="252" height="88" rx="6" fill="#FFF3E8" stroke="#000" stroke-width="2.5"/>
  <text x="670" y="76" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">Task MQTT / WiFi</text>
  <text x="670" y="96" text-anchor="middle" fill="#4A5568" font-size="12">mqttClient.loop()</text>
  <text x="670" y="114" text-anchor="middle" fill="#718096" font-size="11">xQueueReceive → publish</text>
  <text x="670" y="128" text-anchor="middle" fill="#718096" font-size="10">reconnect broker otomatis</text>
  <!-- Arrows sensor → queue → mqtt -->
  <line x1="236" y1="92" x2="300" y2="92" stroke="#1a1a1a" stroke-width="2" marker-end="url(#frtArrow)"/>
  <text x="268" y="82" text-anchor="middle" fill="#4A5568" font-size="10" font-weight="600">send</text>
  <line x1="480" y1="92" x2="544" y2="92" stroke="#1a1a1a" stroke-width="2" marker-end="url(#frtArrow)"/>
  <text x="512" y="82" text-anchor="middle" fill="#4A5568" font-size="10" font-weight="600">recv</text>
  <!-- Broker -->
  <rect x="220" y="210" width="380" height="76" rx="6" fill="#2979FF" stroke="#000" stroke-width="2.5"/>
  <text x="410" y="238" text-anchor="middle" fill="#fff" font-size="15" font-weight="700">Broker Mosquitto</text>
  <text x="410" y="258" text-anchor="middle" fill="#e3f2fd" font-size="12">192.168.1.50:1883 · user kindo_esp32</text>
  <text x="410" y="276" text-anchor="middle" fill="#e3f2fd" font-size="11">topic kodingindonesia/esp32/dht22/data</text>
  <line x1="670" y1="136" x2="410" y2="210" stroke="#2979FF" stroke-width="2.5" marker-end="url(#frtArrow)"/>
  <text x="548" y="162" text-anchor="middle" fill="#2979FF" font-size="10" font-weight="700">publish</text>
  <!-- loop() note — bukan bagian alur data -->
  <rect x="24" y="210" width="172" height="56" rx="4" fill="#fff" stroke="#CBD5E0" stroke-width="1.5" stroke-dasharray="4 3"/>
  <text x="110" y="232" text-anchor="middle" fill="#1a1a1a" font-size="11" font-weight="600">loop() kosong</text>
  <text x="110" y="250" text-anchor="middle" fill="#718096" font-size="10">vTaskDelay(portMAX_DELAY)</text>
  <text x="110" y="278" text-anchor="middle" fill="#A0AEC0" font-size="9">bukan alur data</text>
  <!-- Legend -->
  <text x="410" y="330" text-anchor="middle" fill="#718096" font-size="10">Alur data: sensor → queue → MQTT → broker · dua task jalan paralel di core berbeda</text>
</svg>
<figcaption style="margin-top:.75rem;font-size:.875rem;color:#718096;text-align:center">Diagram arsitektur FreeRTOS — task sensor (Core 1) mengisi queue; task MQTT (Core 0) mengonsumsi dan publish ke broker.</figcaption>
</figure>

<p>ESP32 punya dua core. Pola umum: networking di <strong>Core 0</strong>, pembacaan sensor di <strong>Core 1</strong> — mengurangi kontensi saat TLS atau reconnect WiFi.</p>

<h2>Core 0 vs Core 1 — Panduan Singkat</h2>
<p>Arduino-ESP32 menjalankan WiFi stack dan sebagian driver di core tertentu. Meskipun detail internal bisa berubah antar versi core, aturan praktis untuk proyek Koding Indonesia:</p>
<ul>
  <li><strong>Core 0</strong> — WiFi, MQTT client, reconnect broker, opsional HTTPS/Firebase</li>
  <li><strong>Core 1</strong> — pembacaan DHT22, debounce GPIO, sampling <a href="/artikel/adc-esp32-sensor-analog-soil-moisture-ldr-mqtt">ADC (#35)</a> nanti</li>
  <li>Hindari dua task memanggil <code>WiFi.disconnect()</code> bersamaan — race condition</li>
</ul>

<p>Jika kamu hanya punya satu task berat (mis. TLS <a href="/artikel/esp32-firebase-realtime-database-sensor-cloud">Firebase (#30)</a>), pertimbangkan stack 12–16 KB dan monitor heap secara berkala di Serial.</p>

<h2>Struktur Data &amp; Queue</h2>
<pre><code class="language-cpp">typedef struct {
  float temperature;
  float humidity;
  uint32_t unix_ts;
} SensorReading;

QueueHandle_t sensorQueue;

void sensorTask(void* param) {
  for (;;) {
    SensorReading r;
    r.temperature = dht.readTemperature();
    r.humidity = dht.readHumidity();
    if (!isnan(r.temperature) &amp;&amp; !isnan(r.humidity)) {
      r.unix_ts = 1782977400; // produksi: NTP #34
      xQueueSend(sensorQueue, &amp;r, pdMS_TO_TICKS(100));
    }
    vTaskDelay(pdMS_TO_TICKS(5000));
  }
}</code></pre>

<p>Queue memisahkan <strong>produksi</strong> data (sensor) dari <strong>konsumsi</strong> (MQTT). Jika broker lambat, queue bisa menampung beberapa sampel — atau drop jika penuh (kebijakan tim).</p>

<h2>Task MQTT &amp; WiFi</h2>
<pre><code class="language-cpp">void mqttTask(void* param) {
  WiFi.begin("GANTI_NAMA_WIFI", "GANTI_PASSWORD_WIFI");
  while (WiFi.status() != WL_CONNECTED) {
    vTaskDelay(pdMS_TO_TICKS(500));
  }

  mqttClient.setServer("192.168.1.50", 1883);
  mqttClient.setBufferSize(512);

  for (;;) {
    if (!mqttClient.connected()) {
      String clientId = "esp32-" + String(random(0xffff), HEX);
      if (mqttClient.connect(clientId.c_str(), "kindo_esp32", "GANTI_PASSWORD_MQTT")) {
        Serial.println("MQTT OK");
      }
    }
    mqttClient.loop();

    SensorReading r;
    if (xQueueReceive(sensorQueue, &amp;r, pdMS_TO_TICKS(200)) == pdTRUE) {
      String json = "{\"temperature\":" + String(r.temperature, 1) +
        ",\"humidity\":" + String(r.humidity, 1) +
        ",\"unix\":" + String(r.unix_ts) +
        ",\"iso\":\"2026-07-02T14:30:00\"" +
        ",\"source\":\"esp32\"}";
      mqttClient.publish("kodingindonesia/esp32/dht22/data", json.c_str());
    }
    vTaskDelay(pdMS_TO_TICKS(50));
  }
}</code></pre>

<p>Topic <code>kodingindonesia/esp32/dht22/data</code> konsisten dengan seluruh Seri 2. Verifikasi dengan:</p>
<pre><code class="language-bash">mosquitto_sub -h 192.168.1.50 -u kindo_esp32 -P GANTI_PASSWORD_MQTT \
  -t kodingindonesia/esp32/dht22/data -v</code></pre>

<h2>setup() — Spawn Task</h2>
<pre><code class="language-cpp">void setup() {
  Serial.begin(115200);
  dht.begin();
  delay(2000);

  sensorQueue = xQueueCreate(5, sizeof(SensorReading));

  xTaskCreatePinnedToCore(sensorTask, "Sensor", 4096, NULL, 1, NULL, 1);
  xTaskCreatePinnedToCore(mqttTask, "MQTT", 8192, NULL, 1, NULL, 0);
}

void loop() {
  vTaskDelay(portMAX_DELAY); // kerja di task, loop kosong
}</code></pre>

<blockquote>
  <p><strong>Pro tip:</strong> Stack size <code>8192</code> untuk task MQTT — SSL/Firebase butuh lebih besar; untuk MQTT plain 4096–8192 biasanya cukup. Pantau <code>uxTaskGetStackHighWaterMark()</code> saat debug.</p>
</blockquote>

<h2>Membandingkan dengan Sketch #9</h2>
<p>Di <a href="/artikel/gabungkan-dht22-relay-mqtt-esp32-satu-proyek">artikel #9</a>, <code>loop()</code> memanggil <code>dht.read</code>, <code>mqttClient.loop()</code>, dan callback subscribe relay secara serial. Itu valid untuk satu node. FreeRTOS menjadi penting ketika:</p>
<ul>
  <li>Interval sensor ketat (mis. setiap 2 detik) tapi MQTT harus responsif untuk relay</li>
  <li>Ada task ketiga: <a href="/artikel/oled-ssd1306-esp32-tampilkan-data-sensor-i2c">OLED (#14)</a>, <a href="/artikel/ota-update-firmware-esp32-via-wifi">OTA check (#15)</a>, atau <a href="/artikel/ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt">NTP sync (#34)</a></li>
  <li><a href="/artikel/gateway-lora-mqtt-esp32-sensor-jarak-jauh-dashboard">Gateway LoRa (#28)</a> menerima packet sementara WiFi reconnect</li>
</ul>

<p>Kamu bisa migrasi bertahap: pertahankan logika <a href="/artikel/gabungkan-dht22-relay-mqtt-esp32-satu-proyek">#9</a>, pindahkan hanya bagian sensor ke task pertama — tanpa rewrite total.</p>

<h2>Rencana Migrasi dari Sketch #9 (Langkah demi Langkah)</h2>
<ol>
  <li><strong>Salin sketch <a href="/artikel/gabungkan-dht22-relay-mqtt-esp32-satu-proyek">#9</a></strong> ke project baru — pastikan MQTT ke broker <code>192.168.1.50</code> masih jalan</li>
  <li><strong>Ekstrak fungsi baca DHT22</strong> ke <code>sensorTask</code> — interval 5 detik dengan <code>vTaskDelay</code></li>
  <li><strong>Pindahkan WiFi + mqttClient.loop + publish</strong> ke <code>mqttTask</code></li>
  <li><strong>Tambahkan queue</strong> di antara keduanya — mulai depth 3–5</li>
  <li><strong>Kosongkan loop()</strong> — hanya <code>vTaskDelay(portMAX_DELAY)</code></li>
  <li><strong>Uji subscribe relay</strong> — jika masih dipakai dari <a href="/artikel/gabungkan-dht22-relay-mqtt-esp32-satu-proyek">#9</a>, tambahkan task ketiga atau gabung di mqttTask dengan prioritas lebih tinggi</li>
</ol>

<p>Setiap langkah di atas bisa di-commit terpisah di Git — memudahkan rollback jika stack overflow muncul di tengah migrasi.</p>

<h2>Prioritas, Mutex &amp; Watchdog</h2>
<p>FreeRTOS menyediakan primitif lain untuk produksi:</p>
<ul>
  <li><strong>Mutex</strong> — lindungi bus <a href="/artikel/i2c-esp32-sensor-bme280-suhu-tekanan-mqtt">I2C (#13)</a> jika OLED dan BME280 dibaca dari task berbeda</li>
  <li><strong>Semaphore</strong> — signal event (tombol GPIO interrupt → task MQTT)</li>
  <li><strong>Task watchdog (TWDT)</strong> — reset ESP32 jika task macet; wajib di firmware lapangan</li>
</ul>

<p>Untuk lab, watchdog bisa dimatikan dulu. Sebelum deploy ke kebun <a href="/artikel/smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt">greenhouse capstone (#39)</a>, aktifkan TWDT di task yang paling kritis.</p>

<h2>Stack Size — Titik Awal yang Aman</h2>
<table>
  <thead>
    <tr><th>Task</th><th>Stack (word)</th><th>Catatan</th></tr>
  </thead>
  <tbody>
    <tr><td>Sensor DHT22</td><td>4096</td><td>Cukup untuk float + queue send</td></tr>
    <tr><td>MQTT plain</td><td>8192</td><td>Naikkan jika PubSubClient + String JSON</td></tr>
    <tr><td>MQTT + TLS</td><td>12288+</td><td>Lihat <a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">MQTT TLS (#17)</a> — uji di lapangan</td></tr>
    <tr><td>Firebase HTTPS</td><td>16384+</td><td>Pisah task cloud dari MQTT lokal (<a href="/artikel/esp32-firebase-realtime-database-sensor-cloud">#30</a>)</td></tr>
  </tbody>
</table>

<p>Gunakan <code>uxTaskGetStackHighWaterMark(NULL)</code> setelah beberapa menit jalan untuk melihat sisa stack terendah per task — naikkan parameter stack di <code>xTaskCreatePinnedToCore</code> jika margin tipis.</p>

<h2>PlatformIO vs Arduino IDE</h2>
<p>FreeRTOS berjalan di kedua workflow. Di <a href="/artikel/migrasi-platformio-esp32-vscode-project-rapi">PlatformIO (#29)</a>, pecah file:</p>
<pre><code class="language-ini">[env:esp32dev]
platform = espressif32
board = esp32dev
framework = arduino
monitor_speed = 115200
lib_deps =
  knolleary/PubSubClient @ ^2.8
  adafruit/DHT sensor library @ ^1.4</code></pre>

<ul>
  <li><code>src/main.cpp</code> — setup + spawn task</li>
  <li><code>src/sensor_task.cpp</code> — implementasi sensorTask</li>
  <li><code>src/mqtt_task.cpp</code> — WiFi + MQTT</li>
</ul>

<h2>Debugging Multi-task</h2>
<ol>
  <li>Beri nama task jelas — muncul di panic backtrace</li>
  <li>Log dengan prefix: <code>[SENSOR]</code>, <code>[MQTT]</code></li>
  <li>Cek heap: <code>ESP.getFreeHeap()</code> setelah spawn task</li>
  <li>Jika crash Guru Meditation — naikkan stack task yang bersangkutan</li>
  <li>Pastikan hanya satu task yang memanggil <code>WiFi</code> / <code>mqttClient</code> (hindari race)</li>
</ol>

<p>Untuk timestamp live, ganti contoh statis <code>1782977400</code> dengan sinkronisasi <a href="/artikel/ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt">NTP (#34)</a> di task MQTT sebelum publish.</p>

<h2>Hybrid: FreeRTOS + Firebase</h2>
<p>Task MQTT bisa publish ke broker lokal <strong>dan</strong> push ke <a href="/artikel/esp32-firebase-realtime-database-sensor-cloud">Firebase (#30)</a> — tapi jangan blokir task sensor menunggu HTTPS. Pola aman: sensor → queue → task cloud terpisah dengan stack besar (SSL).</p>

<p>Untuk kebanyakan proyek rumahan, pilih satu saluran cloud dulu; FreeRTOS memudahkan penambahan saluran kedua nanti tanpa merombak task sensor.</p>

<p>Di tim yang sudah memakai <a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">subscriber Python → MySQL (#18)</a>, task MQTT tidak perlu tahu soal database — cukup publish JSON konsisten; worker Python tetap jalan di server terpisah tanpa blocking firmware.</p>

<h2>Keamanan &amp; Produksi</h2>
<ul>
  <li>Jangan commit <code>GANTI_PASSWORD_MQTT</code> — pakai <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">NVS (#12)</a> atau <code>build_flags</code></li>
  <li>Broker produksi: aktifkan <a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">MQTT TLS (#17)</a></li>
  <li>Batasi ukuran queue agar RAM tidak habis saat broker down berjam-jam</li>
  <li>Log stack watermark sebelum rilis firmware</li>
</ul>

<h2>Estimasi Biaya</h2>
<table>
  <thead>
    <tr><th>Item</th><th>Biaya</th></tr>
  </thead>
  <tbody>
    <tr><td>FreeRTOS (built-in ESP32)</td><td>Rp 0</td></tr>
    <tr><td>Hardware (ESP32 + DHT22 dari seri sebelumnya)</td><td>Rp 0 tambahan</td></tr>
    <tr><td>Waktu belajar abstraksi task</td><td>±2–4 jam setelah <a href="/artikel/gabungkan-dht22-relay-mqtt-esp32-satu-proyek">#9</a> menguasai</td></tr>
  </tbody>
</table>

<h2>Checklist Sebelum Go-Live</h2>
<ol>
  <li>Hanya satu task mengelola WiFi/MQTT client?</li>
  <li>Stack size cukup (cek high water mark)?</li>
  <li>Queue depth sesuai interval publish?</li>
  <li>Topic dan JSON konsisten dengan <a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">Grafana (#19)</a>?</li>
  <li>Watchdog dipertimbangkan untuk node lapangan?</li>
</ol>

<h2>Uji Coba</h2>
<ol>
  <li>Upload sketch multi-task — Serial: WiFi OK + MQTT OK</li>
  <li><code>mosquitto_sub</code> menerima JSON setiap ~5 detik</li>
  <li>Matikan broker 1 menit — queue penuh, task sensor tidak crash</li>
  <li>Nyalakan broker — publish lanjut otomatis</li>
  <li>Cek heap stabil setelah 30 menit</li>
</ol>

<h2>FAQ Singkat</h2>
<dl>
  <dt><strong>FreeRTOS wajib di ESP32?</strong></dt>
  <dd>Arduino core sudah memakainya di balik layar; artikel ini membuat task eksplisit untuk kontrol kamu.</dd>
  <dt><strong>Bisa tetap pakai loop()?</strong></dt>
  <dd>Ya untuk proyek kecil; refactor ke task saat blocking jadi masalah.</dd>
  <dt><strong>Core 0 vs Core 1?</strong></dt>
  <dd>WiFi stack biasanya di Core 0 — ikuti pola pin task di contoh.</dd>
  <dt><strong>Queue penuh — data hilang?</strong></dt>
  <dd><code>xQueueSend</code> dengan timeout pendek bisa gagal — log di Serial atau naikkan depth queue jika broker sering down.</dd>
  <dt><strong>Bisa pakai FreeRTOS di ESP8266?</strong></dt>
  <dd>ESP8266 single-core — pola task tetap ada tapi tanpa pin core; lihat perbandingan <a href="/artikel/mengenal-esp32-mikrokontroler-wifi-bluetooth-iot">ESP32 (#1)</a> sebelum porting.</dd>
</dl>

<h2>Tips &amp; Troubleshooting</h2>
<ul>
  <li><strong>Guru Meditation Error:</strong> Stack overflow — naikkan parameter stack di <code>xTaskCreatePinnedToCore</code></li>
  <li><strong>MQTT putus acak:</strong> Pastikan <code>mqttClient.loop()</code> dipanggil rutin di task MQTT</li>
  <li><strong>DHT22 nan:</strong> Hanya task sensor yang memanggil <code>dht.read</code>; delay 2 detik setelah <code>dht.begin()</code></li>
  <li><strong>Data duplikat di queue:</strong> Kurangi frekuensi sensor atau naikkan interval publish</li>
  <li><strong>Heap menurun:</strong> Hindari <code>String</code> besar di loop task — pakai buffer tetap</li>
  <li><strong>Task tidak start:</strong> Cek parameter stack tidak 0; pastikan <code>sensorQueue</code> dibuat sebelum <code>xTaskCreate</code></li>
</ul>

<p>Saat debugging di <a href="/artikel/migrasi-platformio-esp32-vscode-project-rapi">PlatformIO (#29)</a>, filter log per task dengan prefix — lebih mudah dibanding satu aliran Serial panjang dari loop tunggal. Simpan log panic backtrace jika Guru Meditation muncul — biasanya menunjuk task mana yang kehabisan stack.</p>

<h2>Langkah Selanjutnya — Tier 2 Seri 2</h2>
<p>Dengan Jalur E (<a href="/artikel/migrasi-platformio-esp32-vscode-project-rapi">#29</a>–#31) selesai, Tier 1 inti Seri 2 <strong>lengkap</strong> — total 22 artikel live setelah deploy artikel ini, termasuk <a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">deep sleep (#11)</a>, broker sendiri (<a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">#16</a>), dan <a href="/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22">capstone dashboard (#10)</a>. Lanjut ke pelengkap Tier 2:</p>
<ul>
  <li><strong><a href="/artikel/bluetooth-esp32-ble-kirim-data-sensor-smartphone">Bluetooth BLE (#32)</a>:</strong> kirim data sensor ke smartphone tanpa WiFi</li>
  <li><strong><a href="/artikel/ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt">NTP (#34)</a></strong> — timestamp live di payload JSON</li>
  <li><strong><a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">Deep sleep (#11)</a></strong> — kombinasikan dengan task ringan pre-sleep</li>
  <li><strong><a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">Grafana (#19)</a></strong> — visualisasi histori dari topic yang sama</li>
  <li>Capstone <strong><a href="/artikel/smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt">greenhouse (#39)</a></strong></li>
</ul>

<p>FreeRTOS membuka pintu ke firmware ESP32 yang lebih andal dan siap skala tim — lanjutkan perjalanan di <a href="/artikel">halaman artikel</a> Koding Indonesia.</p>
HTML;
    }
}
