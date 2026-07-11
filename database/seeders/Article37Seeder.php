<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article37Seeder extends Seeder
{
    public function run(): void
    {
        $admin  = User::first();
        $espCat = Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $espCat) {
            throw new \RuntimeException('User atau kategori tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'sd-card-spi-esp32-logging-data-sensor-offline';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        Tag::updateOrCreate(['slug' => 'spi'], ['name' => 'spi']);
        Tag::updateOrCreate(['slug' => 'sd-card'], ['name' => 'sd-card']);

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $espCat->id,
                'title'           => 'SD Card & SPI di ESP32: Logging Data Sensor Offline di Lapangan',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'SD Card SPI ESP32 — Logging Sensor Offline Lapangan',
                'seo_description' => 'Tutorial SD Card microSD via SPI di ESP32: tulis CSV sensor DHT22 offline, sinkron MQTT saat WiFi ada — pelengkap deep sleep & NTP Seri 2.',
            ]
        );

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', [
            'esp32', 'spi', 'sd-card', 'sensor', 'iot', 'mqtt',
        ])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command->info('✓ Artikel ke-37 berhasil dipublish: ' . $article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — Data Tetap Tersimpan Tanpa WiFi</h2>
<p>Seri 2 sudah mengajarkan kirim sensor ke broker <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">MQTT (#7)</a>, histori di <a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">MySQL (#18)</a>, dan grafik <a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">Grafana (#19)</a>. Semua itu mengasumsikan <strong>WiFi atau jaringan tersedia</strong>.</p>

<p>Di lapangan — kebun, greenhouse, atau titik monitoring jauh dari router — koneksi bisa putus berjam-jam. Node <a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">deep sleep (#11)</a> bangun sebentar, baca sensor, lalu tidur lagi; tanpa penyimpanan lokal, sampel hilang selamanya.</p>

<p>Artikel <strong>Tier 2</strong> ini mengajarkan <strong>SD Card microSD</strong> lewat bus <strong>SPI</strong> (bukan I2C): tulis log CSV offline, lalu sinkron ke MQTT saat WiFi kembali — melengkapi arsitektur hybrid <a href="/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22">capstone #10</a>, <a href="/artikel/esp8266-nodemcu-vs-esp32-kapan-pakai-upgrade">ESP8266 vs ESP32 (#36)</a>, dan persiapan capstone <strong>greenhouse (#39)</strong>.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> Paham <a href="/artikel/blink-led-esp32-tutorial-pertama-embedded-system">GPIO (#3)</a>, <a href="/artikel/menghubungkan-esp32-wifi-kirim-data-server">WiFi (#4)</a>, <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">DHT22 (#5)</a>, dan idealnya sudah pakai <a href="/artikel/ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt">NTP (#34)</a> untuk timestamp akurat di setiap baris log.</p>
</blockquote>

<h2>Mengapa SD Card, Bukan Hanya Cloud?</h2>
<table>
  <thead>
    <tr><th>Skenario</th><th>MQTT / cloud saja</th><th>SD Card + MQTT hybrid</th></tr>
  </thead>
  <tbody>
    <tr><td>WiFi mati 6 jam</td><td>Data hilang</td><td>CSV tetap terisi</td></tr>
    <tr><td>Deep sleep node baterai</td><td>Reconnect lambat</td><td>Log dulu, upload nanti</td></tr>
    <tr><td>Audit lapangan</td><td>Butuh laptop + broker</td><td>Cabut kartu → baca di PC</td></tr>
    <tr><td>Debugging firmware</td><td>Sulit trace event lokal</td><td>File teks mudah dibuka</td></tr>
  </tbody>
</table>

<h2>SPI vs I2C — Jangan Campur</h2>
<p>Di <a href="/artikel/i2c-esp32-sensor-bme280-suhu-tekanan-mqtt">artikel I2C (#13)</a>, sensor BME280 dan OLED <a href="/artikel/oled-ssd1306-esp32-tampilkan-data-sensor-i2c">#14</a> pakai <strong>dua wire</strong> (SDA/SCL) + alamat perangkat. <strong>SD Card memakai SPI</strong> — protokol berbeda:</p>
<table>
  <thead>
    <tr><th>Bus</th><th>Wire</th><th>Cocok untuk</th><th>Artikel Seri 2</th></tr>
  </thead>
  <tbody>
    <tr><td><strong>I2C</strong></td><td>SDA, SCL</td><td>Sensor kecil, OLED</td><td><a href="/artikel/i2c-esp32-sensor-bme280-suhu-tekanan-mqtt">#13 BME280</a></td></tr>
    <tr><td><strong>SPI</strong></td><td>MOSI, MISO, SCK, CS</td><td>SD Card, display cepat, LoRa</td><td><strong>#37 (ini)</strong>, <a href="/artikel/lora-esp32-modul-sx1278-kirim-data-jarak-jauh">#26 LoRa</a></td></tr>
  </tbody>
</table>

<p>SPI lebih cepat untuk burst data ke kartu memori — tepat untuk append ribuan baris CSV.</p>

<h2>Dasar SPI — Empat Sinyal Utama</h2>
<ul>
  <li><strong>MOSI</strong> (Master Out Slave In) — ESP32 kirim data ke SD</li>
  <li><strong>MISO</strong> (Master In Slave Out) — SD kirim balik ke ESP32</li>
  <li><strong>SCK</strong> (Clock) — tick sinkron</li>
  <li><strong>CS / SS</strong> (Chip Select) — pilih perangkat di bus yang sama</li>
</ul>

<blockquote>
  <p><strong>Pro tip:</strong> Satu bus SPI bisa dipakai beberapa perangkat — asalkan masing-masing punya <strong>CS pin terpisah</strong>. Jangan dua perangkat aktif CS bersamaan.</p>
</blockquote>

<h2>Hardware — Modul SD Card microSD</h2>
<ul>
  <li><strong>Modul SD Card</strong> (holder microSD + level shifter 3,3 V)</li>
  <li><strong>ESP32 DevKit</strong></li>
  <li><strong>microSD</strong> 4–32 GB (FAT32)</li>
  <li><strong>DHT22</strong> untuk demo log suhu/kelembaban (<a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">#5</a>)</li>
  <li>Kabel jumper · breadboard</li>
</ul>

<h2>Wiring SPI — ESP32 ke Modul SD</h2>
<p>Pin HSPI default yang umum di tutorial ESP32 (hindari bentrok dengan flash internal):</p>
<table>
  <thead>
    <tr><th>SD Module</th><th>ESP32 GPIO</th><th>Fungsi SPI</th></tr>
  </thead>
  <tbody>
    <tr><td>CS</td><td><strong>GPIO 5</strong></td><td>Chip Select</td></tr>
    <tr><td>SCK / CLK</td><td><strong>GPIO 18</strong></td><td>Clock</td></tr>
    <tr><td>MOSI</td><td><strong>GPIO 23</strong></td><td>Data ke SD</td></tr>
    <tr><td>MISO</td><td><strong>GPIO 19</strong></td><td>Data dari SD</td></tr>
    <tr><td>VCC</td><td>3,3 V</td><td>Jangan 5 V langsung ke GPIO</td></tr>
    <tr><td>GND</td><td>GND</td><td>Common ground</td></tr>
    <tr><td>DHT22 data</td><td><strong>GPIO 4</strong></td><td>Digital — tidak bentrok SPI</td></tr>
  </tbody>
</table>

<h2>Konflik Pin — Cek Sebelum Wiring</h2>
<table>
  <thead>
    <tr><th>Fungsi lain</th><th>GPIO</th><th>Bentrok SPI?</th></tr>
  </thead>
  <tbody>
    <tr><td>DHT22 (#5)</td><td>4</td><td>Tidak</td></tr>
    <tr><td>Relay (#8)</td><td>26</td><td>Tidak</td></tr>
    <tr><td>Soil ADC (#35)</td><td>34, 35</td><td>Tidak</td></tr>
    <tr><td>Servo (#33)</td><td>27</td><td>Tidak</td></tr>
    <tr><td>SD CS</td><td>5</td><td>Hindari jika board pakai flash pada GPIO 6–11</td></tr>
  </tbody>
</table>

<h2>Format File CSV di Kartu</h2>
<p>Satu file <code>/sensor.csv</code> dengan header — mudah diimpor ke Excel atau script <a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">Python (#18)</a>:</p>
<pre><code class="language-text">timestamp_iso,unix,temp_c,hum_pct
2026-07-02T14:30:00,1782977400,28.5,62.1
</code></pre>

<p>Timestamp ISO dan unix mengikuti konvensi <a href="/artikel/ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt">NTP (#34)</a> — contoh unix <code>1782977400</code> agar konsisten di seluruh seri.</p>

<h2>Library &amp; Include</h2>
<p>Di Arduino IDE untuk ESP32, cukup:</p>
<ul>
  <li><code>SPI.h</code> — bus SPI bawaan</li>
  <li><code>SD.h</code> — filesystem FAT di kartu</li>
  <li><code>DHT sensor library</code> — sama seperti #5</li>
</ul>

<h2>Sketch — Init SD &amp; Tulis Baris Pertama</h2>
<pre><code class="language-cpp">#include &lt;SPI.h&gt;
#include &lt;SD.h&gt;
#include &lt;DHT.h&gt;
#include &lt;time.h&gt;

#define SD_CS     5
#define SD_SCK   18
#define SD_MISO  19
#define SD_MOSI  23
#define DHT_PIN   4
#define DHT_TYPE DHT22

#define LOG_FILE "/sensor.csv"

DHT dht(DHT_PIN, DHT_TYPE);

bool initSD() {
  SPI.begin(SD_SCK, SD_MISO, SD_MOSI, SD_CS);
  if (!SD.begin(SD_CS, SPI)) {
    Serial.println("SD init gagal");
    return false;
  }
  if (!SD.exists(LOG_FILE)) {
    File f = SD.open(LOG_FILE, FILE_WRITE);
    if (f) {
      f.println("timestamp_iso,unix,temp_c,hum_pct");
      f.close();
    }
  }
  return true;
}

void appendLog(float t, float h, time_t unixNow) {
  struct tm ti;
  localtime_r(&unixNow, &ti);
  char iso[25];
  strftime(iso, sizeof(iso), "%Y-%m-%dT%H:%M:%S", &ti);

  File f = SD.open(LOG_FILE, FILE_APPEND);
  if (!f) return;
  f.printf("%s,%ld,%.1f,%.1f\n", iso, (long)unixNow, t, h);
  f.close();
}

void setup() {
  Serial.begin(115200);
  dht.begin();
  delay(2000);
  initSD();
}

void loop() {
  float t = dht.readTemperature();
  float h = dht.readHumidity();
  if (!isnan(t) &amp;&amp; !isnan(h)) {
    time_t now = 1782977400; // ganti dengan NTP (#34) di produksi
    appendLog(t, h, now);
    Serial.println("Baris ditulis ke SD");
  }
  delay(60000);
}
</code></pre>

<h2>Sinkron ke MQTT Saat WiFi Kembali</h2>
<p>Pola <strong>hybrid</strong> — log selalu ke SD; jika WiFi connect, baca baris terakhir (atau seluruh file) dan publish ke broker <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Mosquitto (#16)</a>:</p>
<pre><code class="language-cpp">// Setelah WiFi + mqtt.connected()
// Topic konsisten Seri 1: kodingindonesia/esp32/dht22/data
const char* TOPIC = "kodingindonesia/esp32/dht22/data";

void publishLastSample(float t, float h, long unixTs) {
  char payload[160];
  snprintf(payload, sizeof(payload),
    "{\"unix\":%ld,\"temp\":%.1f,\"hum\":%.1f,\"source\":\"sd_sync\"}",
    unixTs, t, h);
  mqtt.publish(TOPIC, payload);
}
</code></pre>

<p>Subscriber Python (#18) dan Grafana (#19) tidak perlu diubah — field JSON sama dengan node MQTT langsung.</p>

<h2>Deep Sleep + Log Satu Sampel</h2>
<p>Gabung dengan <a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">deep sleep (#11)</a>: di setiap wake, baca DHT → <code>appendLog()</code> → tidur lagi. Kartu SD tetap terisi meski WiFi tidak pernah aktif selama seminggu.</p>

<h2>Baca Kartu di PC</h2>
<ol>
  <li><strong>Cabut microSD</strong> dengan aman (unmount tidak ada di bare metal — minimal stop write dengan delay setelah <code>f.close()</code>)</li>
  <li>Colok ke reader USB di laptop</li>
  <li>Buka <code>sensor.csv</code> di Excel/LibreOffice</li>
  <li>Opsional: impor ke MySQL dengan script Python mirip #18 — baca CSV bukan MQTT</li>
</ol>

<h2>PlatformIO — Dependency</h2>
<p>Di <a href="/artikel/migrasi-platformio-esp32-vscode-project-rapi">PlatformIO (#29)</a>:</p>
<pre><code class="language-ini">[env:esp32dev]
platform = espressif32
board = esp32dev
framework = arduino
lib_deps =
    adafruit/DHT sensor library@^1.4
</code></pre>
<p>Library <code>SD</code> dan <code>SPI</code> sudah bundled framework Arduino ESP32.</p>

<h2>SD vs SPIFFS vs LittleFS</h2>
<table>
  <thead>
    <tr><th>Media</th><th>Kapasitas</th><th>Cocok untuk</th></tr>
  </thead>
  <tbody>
    <tr><td><strong>SD Card (SPI)</strong></td><td>GB</td><td>Log bulanan, cabut &amp; baca di PC</td></tr>
    <tr><td>SPIFFS / LittleFS</td><td>MB (flash chip)</td><td>Config kecil, web asset <a href="/artikel/ota-update-firmware-esp32-via-wifi">OTA (#15)</a></td></tr>
    <tr><td>MQTT → DB</td><td>Unlimited (server)</td><td>Dashboard real-time #19</td></tr>
  </tbody>
</table>

<h2>Keamanan &amp; Best Practice</h2>
<ul>
  <li>Jangan cabut kartu saat <code>SD.open(..., FILE_APPEND)</code> aktif — selalu <code>close()</code></li>
  <li>Kartu FAT32 — hindari power loss saat menulis (kapasitor kecil membantu)</li>
  <li>Jangan simpan password WiFi di file teks di SD — pakai <code>GANTI_*</code> placeholder di firmware</li>
  <li>Produksi: enkripsi kartu atau fisik secure enclosure</li>
</ul>

<h2>Estimasi Biaya (Indonesia)</h2>
<table>
  <thead>
    <tr><th>Komponen</th><th>Harga (Rp)</th></tr>
  </thead>
  <tbody>
    <tr><td>ESP32 DevKit</td><td>35.000 – 55.000</td></tr>
    <tr><td>Modul SD Card + holder</td><td>15.000 – 30.000</td></tr>
    <tr><td>microSD 8 GB</td><td>25.000 – 45.000</td></tr>
    <tr><td>DHT22</td><td>35.000 – 55.000</td></tr>
    <tr><td><strong>Total</strong></td><td><strong>~110.000 – 185.000</strong></td></tr>
  </tbody>
</table>

<h2>Checklist Sebelum Demo</h2>
<ul>
  <li>☐ Kartu diformat <strong>FAT32</strong></li>
  <li>☐ Serial: <code>SD init gagal</code> tidak muncul</li>
  <li>☐ File <code>/sensor.csv</code> ada + header benar</li>
  <li>☐ Baris baru setiap interval baca DHT</li>
  <li>☐ Cabut kartu → buka CSV di PC → data terbaca</li>
  <li>☐ Opsional: WiFi sync publish ke <code>kodingindonesia/esp32/dht22/data</code></li>
</ul>

<h2>FAQ Singkat</h2>
<dl>
  <dt><strong>Bisa pakai I2C untuk SD Card?</strong></dt>
  <dd>Modul SD standar di pasaran pakai <strong>SPI</strong> — I2C jarang dan lambat untuk log intensif.</dd>
  <dt><strong>SD init gagal terus?</strong></dt>
  <dd>Cek wiring CS/SCK/MOSI/MISO, format FAT32, dan supply 3,3 V stabil.</dd>
  <dt><strong>Bentrok dengan BME280 I2C?</strong></dt>
  <dd>Tidak — I2C GPIO 21/22, SPI pakai 18/19/23/5 di sketch ini.</dd>
  <dt><strong>Perlu NTP?</strong></dt>
  <dd>Sangat disarankan — tanpa NTP, timestamp placeholder tidak akurat di lapangan (<a href="/artikel/ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt">#34</a>).</dd>
  <dt><strong>ESP8266 bisa logging SD?</strong></dt>
  <dd>Bisa terbatas — lihat perbandingan <a href="/artikel/esp8266-nodemcu-vs-esp32-kapan-pakai-upgrade">#36</a>; ESP32 lebih nyaman untuk SPI + WiFi + MQTT.</dd>
</dl>

<h2>Tips &amp; Troubleshooting</h2>
<ul>
  <li><strong>File corrupt:</strong> Gunakan kartu berkualitas · hindari cabut saat write · coba <code>SD.end()</code> sebelum sleep panjang</li>
  <li><strong>0 byte file:</strong> Pastikan <code>FILE_APPEND</code> dan <code>flush</code> / <code>close</code> dipanggil</li>
  <li><strong>DHT NaN di log:</strong> Sama seperti <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">#5</a> — pull-up 10kΩ, <code>delay(2000)</code> setelah <code>dht.begin()</code></li>
  <li><strong>MQTT sync dobel:</strong> Tandai baris sudah di-upload (kolom <code>synced=1</code>) atau simpan offset di <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">NVS (#12)</a></li>
  <li><strong>Kartu penuh:</strong> Rotasi file harian <code>/2026-07-02.csv</code> atau hapus baris lama</li>
</ul>

<h2>MQTT Credentials Saat Sync</h2>
<p>Saat node kembali online dan mengirim backlog, gunakan pola kredensial yang sama dengan artikel MQTT Seri 1:</p>
<ul>
  <li>Broker: <code>192.168.1.50</code> (Mosquitto #16)</li>
  <li>User: <code>kindo_esp32</code></li>
  <li>Password: <code>GANTI_PASSWORD_MQTT</code> — jangan hardcode di repo</li>
  <li>WiFi: <code>GANTI_NAMA_WIFI</code> / <code>GANTI_PASSWORD_WIFI</code></li>
</ul>
<p>Placeholder ini konsisten di seluruh seri — ganti saat flash firmware ke lapangan.</p>

<h2>FreeRTOS &amp; SD Write</h2>
<p>Jika firmware memakai task terpisah (<a href="/artikel/freertos-esp32-multi-task-sensor-wifi-mqtt">FreeRTOS #31</a>), jangan tulis SD dari dua task bersamaan tanpa mutex. Pola aman: satu task <code>loggerTask</code> yang dequeue sample dari queue dan append ke kartu — hindari korupsi FAT saat WiFi task dan logger task bentrok.</p>

<h2>Perbandingan dengan ESP-NOW &amp; LoRa</h2>
<p><a href="/artikel/esp-now-kirim-data-antar-esp32-tanpa-router-wifi">ESP-NOW (#25)</a> dan <a href="/artikel/lora-esp32-modul-sx1278-kirim-data-jarak-jauh">LoRa (#26)</a> mengirim data ke gateway — tetap butuh penyimpanan jika gateway offline. SD di node sensor adalah <strong>buffer lokal pertama</strong> sebelum data mencapai gateway LoRa → MQTT (<a href="/artikel/gateway-lora-mqtt-esp32-sensor-jarak-jauh-dashboard">#28</a>).</p>

<h2>Rotasi File Harian</h2>
<p>Untuk deployment jangka panjang, jangan biarkan satu <code>sensor.csv</code> membengkak tanpa batas. Pola umum:</p>
<ul>
  <li><code>/log/2026-07-02.csv</code> — satu file per hari (butuh <a href="/artikel/ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt">NTP #34</a> untuk nama file)</li>
  <li>Hapus file &gt;30 hari saat kartu &gt;80% penuh</li>
  <li>Simpan offset baris terakhir yang sudah di-MQTT-kan di <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">NVS (#12)</a></li>
</ul>
<p>Rotasi ini mencegah kartu 8 GB penuh di musim hujan saat node tidak pernah di-flash ulang.</p>

<h2>Gabung Multi-Sensor di Satu Baris</h2>
<p>Setelah paham DHT22, tambahkan kolom dari <a href="/artikel/adc-esp32-sensor-analog-soil-moisture-ldr-mqtt">ADC soil &amp; LDR (#35)</a>:</p>
<pre><code class="language-text">timestamp_iso,unix,temp_c,hum_pct,soil_pct,light_pct
</code></pre>
<p>Satu baris CSV = snapshot lengkap greenhouse — siap diimpor ke MySQL (#18) tanpa ubah skema MQTT.</p>

<h2>Node-RED &amp; Home Assistant</h2>
<p>Setelah sync MQTT, flow <a href="/artikel/node-red-dashboard-otomasi-iot-mqtt-esp32">Node-RED (#23)</a> dan entitas <a href="/artikel/home-assistant-integrasi-esp32-mqtt">Home Assistant (#21)</a> membaca topic yang sama seperti node tanpa SD — field <code>source: sd_sync</code> membantu debug data yang tertunda.</p>

<h2>Langkah Selanjutnya — Tier 2 Seri 2</h2>
<p>Logging offline melengkapi pipeline MQTT — data aman di lapangan dan di cloud. Lanjut:</p>
<ul>
  <li><strong><a href="/artikel/https-sertifikat-esp32-wificlientsecure-api-rest">Keamanan HTTPS &amp; sertifikat (#38)</a></strong> — amankan HTTP client ESP32 ke API eksternal</li>
  <li><strong><a href="/artikel/adc-esp32-sensor-analog-soil-moisture-ldr-mqtt">ADC (#35)</a></strong> — tambah kolom soil/LDR di CSV yang sama</li>
  <li><strong><a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">Grafana (#19)</a></strong> — visualisasi setelah CSV diimpor atau MQTT sync</li>
  <li>Capstone <strong>greenhouse (#39)</strong> — multi-sensor + SD backup + dashboard</li>
</ul>

<p>SPI dan SD Card membuka jalur data offline — lanjutkan di <a href="/artikel">halaman artikel</a> Koding Indonesia.</p>
HTML;
    }
}
