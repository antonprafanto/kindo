<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article11Seeder extends Seeder
{
    public function run(): void
    {
        $admin  = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first();

        if (! $admin || ! $iotCat) {
            throw new \RuntimeException('User atau kategori tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'deep-sleep-esp32-sensor-dht22-hemat-baterai';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $iotCat->id,
                'title'           => 'Deep Sleep ESP32: Node Sensor DHT22 Hemat Baterai',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => true,
                'seo_title'       => 'Deep Sleep ESP32 + DHT22 MQTT — Node Sensor Hemat Baterai',
                'seo_description' => 'Bangun node sensor ESP32 bertenaga baterai: bangun dari deep sleep, baca DHT22, publish MQTT, lalu tidur lagi. Tutorial IoT berbahasa Indonesia.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        Tag::updateOrCreate(['slug' => 'dht22'], ['name' => 'dht22']);
        Tag::updateOrCreate(['slug' => 'deep-sleep'], ['name' => 'deep-sleep']);

        $tagSlugs = ['esp32', 'dht22', 'sensor', 'iot', 'wifi', 'mqtt', 'deep-sleep'];
        $tagIds   = Tag::whereIn('slug', $tagSlugs)->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command->info('✓ Artikel ke-11 berhasil dipublish: ' . $article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — Seri 2 ESP32/IoT</h2>
<p>Selamat datang di <strong>artikel pembuka Seri 2</strong> Koding Indonesia! Ini adalah lanjutan langsung dari janji <strong>deep sleep</strong> di <a href="/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22">roadmap artikel #10</a>. Di capstone Seri 1 kamu sudah membangun dashboard hybrid Web + MQTT yang terus menyala. Itu cocok untuk proyek bertenaga adaptor USB di meja kerja.</p>

<p>Tapi bagaimana jika sensor harus dipasang di <strong>kebun, gudang, atau sudut rumah tanpa stop kontak</strong>? Node yang terus hidup akan menghabiskan baterai dalam hitungan jam. Solusinya: <strong>deep sleep</strong> — ESP32 tidur nyaris total, bangun sebentar untuk kirim data, lalu tidur lagi.</p>

<p>Dalam tutorial ini kita bangun <strong>node sensor DHT22 hemat baterai</strong> yang setiap beberapa menit:</p>
<ol>
  <li>Bangun dari deep sleep</li>
  <li>Hubungkan WiFi</li>
  <li>Baca suhu &amp; kelembaban</li>
  <li>Publish JSON ke MQTT (topic sama seperti Seri 1)</li>
  <li>Matikan radio WiFi dan tidur lagi</li>
</ol>

<blockquote>
  <p><strong>Prasyarat:</strong> Sudah paham <a href="/artikel/menghubungkan-esp32-wifi-kirim-data-server">WiFi ESP32</a>, <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">sensor DHT22</a>, dan <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">publish MQTT</a>. Familiar dengan JSON MQTT dari <a href="/artikel/gabungkan-dht22-relay-mqtt-esp32-satu-proyek">proyek gabungan</a> atau <a href="/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22">dashboard capstone</a> akan membantu.</p>
</blockquote>

<h2>Light Sleep vs Deep Sleep</h2>
<table>
  <thead>
    <tr><th>Mode</th><th>CPU &amp; WiFi</th><th>Konsumsi arus</th><th>Cocok untuk</th></tr>
  </thead>
  <tbody>
    <tr><td><strong>Light sleep</strong></td><td>CPU pause, RAM tetap</td><td>Lebih tinggi dari deep sleep</td><td>Bangun cepat, koneksi WiFi tetap hidup</td></tr>
    <tr><td><strong>Deep sleep</strong></td><td>Hampir mati total; hanya RTC</td><td>~10 µA (ideal) pada chip</td><td>Node baterai kirim data periodik</td></tr>
  </tbody>
</table>

<p>Pada deep sleep, ESP32 <strong>reset penuh</strong> saat bangun — program dimulai lagi dari <code>setup()</code>. Itu normal dan justru disederhanakan: tidak perlu khawatir state lama di RAM.</p>

<p><strong>Wake-up timer vs GPIO:</strong> Tutorial ini memakai <code>esp_sleep_enable_timer_wakeup()</code> (bangun tiap N detik). Alternatif lanjutan: bangun dari pin eksternal (mis. tombol atau sensor) dengan <code>esp_sleep_enable_ext0_wakeup()</code> — berguna jika event jarang terjadi.</p>

<h2>Install Library</h2>
<p>Install lewat Arduino IDE → <strong>Sketch → Include Library → Manage Libraries</strong>:</p>
<ol>
  <li><strong>DHT sensor library</strong> — Adafruit (+ <strong>Adafruit Unified Sensor</strong>)</li>
  <li><strong>PubSubClient</strong> — Nick O'Leary</li>
</ol>
<p>Board ESP32 via Board Manager sudah menyertakan <code>esp_sleep.h</code> — tidak perlu library tambahan untuk deep sleep.</p>

<h2>Yang Kamu Butuhkan</h2>
<ul>
  <li>ESP32 DevKit + sensor DHT22 (GPIO <strong>4</strong>, pull-up <strong>10kΩ</strong> ke 3.3V)</li>
  <li>Kabel jumper &amp; breadboard</li>
  <li>Power: USB untuk uji awal; opsional <strong>LiPo 3.7V + modul TP4056</strong> untuk simulasi baterai</li>
  <li>Library: <strong>DHT sensor library</strong>, <strong>PubSubClient</strong></li>
  <li>WiFi 2.4 GHz + <a href="https://mqtt-explorer.com/" target="_blank" rel="noopener">MQTT Explorer</a> (subscribe topic)</li>
</ul>

<blockquote>
  <p><strong>Catatan daya:</strong> Saat WiFi connect, arus bisa melonjak &gt;100 mA meski sebentar. Itulah mengapa kita matikan WiFi sebelum tidur — bukan hanya deep sleep chip, tapi juga <strong>radio WiFi</strong> yang harus off.</p>
</blockquote>

<h2>Wiring DHT22</h2>
<p>Sama seperti Seri 1 — jangan ubah pin tanpa alasan:</p>
<ul>
  <li>VCC DHT22 → 3.3V</li>
  <li>GND → GND</li>
  <li>DATA → GPIO 4 + resistor pull-up 10kΩ ke 3.3V</li>
</ul>

<blockquote>
  <p><strong>Pin strapping:</strong> Hindari GPIO yang dipakai saat boot (mis. beberapa board sensitif di GPIO 0, 2, 12, 15). GPIO 4 aman untuk DHT22 di kebanyakan DevKit.</p>
</blockquote>

<h2>Arsitektur Node Sensor</h2>
<table>
  <thead>
    <tr><th>Tahap</th><th>Durasi tipikal</th><th>Catatan</th></tr>
  </thead>
  <tbody>
    <tr><td>Deep sleep</td><td>10 menit (konfigurasi)</td><td>Hanya RTC timer hidup</td></tr>
    <tr><td>Bangun + baca DHT22</td><td>~2–3 detik</td><td><code>delay(2000)</code> setelah <code>dht.begin()</code></td></tr>
    <tr><td>WiFi + MQTT publish</td><td>~3–10 detik</td><td>Sekali publish per siklus</td></tr>
    <tr><td>Matikan WiFi → deep sleep</td><td>&lt;1 detik</td><td><code>WiFi.mode(WIFI_OFF)</code></td></tr>
  </tbody>
</table>

<p><strong>Broker latihan:</strong> <code>test.mosquitto.org:1883</code> (sama Seri 1).  
<strong>Topic:</strong> <code>kodingindonesia/esp32/dht22/data</code> — payload JSON <code>{"suhu":28.5,"kelembaban":65.2}</code>.</p>

<blockquote>
  <p><strong>Broker bukan website:</strong> <code>test.mosquitto.org</code> tidak dibuka di browser. Pakai MQTT Explorer atau <code>mosquitto_sub</code>. Detail di <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">artikel MQTT</a>.</p>
</blockquote>

<blockquote>
  <p><strong>Pro tip:</strong> Ganti segmen topic dengan nama unik, misalnya <code>kodingindonesia/anton/esp32/dht22/data</code>, agar tidak bentrok dengan peserta tutorial lain di broker publik.</p>
</blockquote>

<h2>Kode Lengkap: Deep Sleep + DHT22 + MQTT</h2>
<p>Ganti <code>ssid</code> dan <code>password</code>, lalu upload. Setelah upload, buka Serial Monitor 115200 — kamu akan melihat satu siklus lalu ESP32 tidur (Serial berhenti sampai bangun lagi).</p>

<pre><code class="language-arduino">#include &lt;WiFi.h&gt;
#include &lt;PubSubClient.h&gt;
#include &lt;DHT.h&gt;
#include "esp_sleep.h"

// Durasi tidur (detik) — ubah sesuai kebutuhan
const uint64_t DURASI_TIDUR_DETIK = 600; // 10 menit

const char* ssid     = "NamaWiFiKamu";
const char* password = "PasswordWiFiKamu";

const char* mqttServer  = "test.mosquitto.org";
const int   mqttPort    = 1883;
const char* topicSensor = "kodingindonesia/esp32/dht22/data";

#define DHT_PIN  4
#define DHT_TYPE DHT22

DHT dht(DHT_PIN, DHT_TYPE);
WiFiClient espClient;
PubSubClient mqttClient(espClient);

void cetakAlasanBangun() {
  esp_sleep_wakeup_cause_t cause = esp_sleep_get_wakeup_cause();
  switch (cause) {
    case ESP_SLEEP_WAKEUP_TIMER:
      Serial.println("Bangun: timer deep sleep");
      break;
    case ESP_SLEEP_WAKEUP_UNDEFINED:
    default:
      Serial.println("Bangun: boot / reset (upload atau tombol EN)");
      break;
  }
}

void matikanWiFi() {
  mqttClient.disconnect();
  WiFi.disconnect(true);
  WiFi.mode(WIFI_OFF);
}

bool koneksiWiFi() {
  Serial.print("WiFi");
  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);

  unsigned long mulai = millis();
  while (WiFi.status() != WL_CONNECTED &amp;&amp; millis() - mulai &lt; 15000) {
    delay(500);
    Serial.print(".");
  }

  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("\nWiFi gagal (timeout 15 detik)");
    return false;
  }

  Serial.println("\nWiFi terhubung");
  return true;
}

bool koneksiMQTT() {
  mqttClient.setServer(mqttServer, mqttPort);
  mqttClient.setBufferSize(512);

  String clientId = "ESP32-Sleep-" + String(random(0xffff), HEX);
  if (mqttClient.connect(clientId.c_str())) {
    Serial.println("MQTT terhubung");
    return true;
  }

  Serial.print("MQTT gagal, rc=");
  Serial.println(mqttClient.state());
  return false;
}

bool publishSensor() {
  float kelembaban = dht.readHumidity();
  float suhu       = dht.readTemperature();

  if (isnan(kelembaban) || isnan(suhu)) {
    Serial.println("DHT22 gagal (NaN) — lewati publish");
    return false;
  }

  String payload = "{\"suhu\":" + String(suhu, 1);
  payload += ",\"kelembaban\":" + String(kelembaban, 1) + "}";

  mqttClient.loop();
  if (mqttClient.publish(topicSensor, payload.c_str())) {
    Serial.print("Publish ");
    Serial.print(topicSensor);
    Serial.print(": ");
    Serial.println(payload);
    return true;
  }

  Serial.println("Publish gagal");
  return false;
}

void masukDeepSleep() {
  Serial.print("Deep sleep ");
  Serial.print((unsigned long)DURASI_TIDUR_DETIK);
  Serial.println(" detik...");
  Serial.flush();

  esp_sleep_enable_timer_wakeup(DURASI_TIDUR_DETIK * 1000000ULL);
  esp_deep_sleep_start();
  // tidak pernah sampai di sini
}

void setup() {
  Serial.begin(115200);
  delay(100);

  cetakAlasanBangun();

  // Hemat daya saat aktif (opsional, trade-off kecepatan)
  setCpuFrequencyMhz(80);

  randomSeed(micros());
  dht.begin();
  delay(2000); // stabilkan DHT22 setelah bangun

  if (!koneksiWiFi()) {
    matikanWiFi();
    masukDeepSleep();
    return;
  }

  if (!koneksiMQTT()) {
    matikanWiFi();
    masukDeepSleep();
    return;
  }

  publishSensor();
  matikanWiFi();
  masukDeepSleep();
}

void loop() {
  // Deep sleep: loop tidak dipakai — semua logika di setup() tiap bangun
}
</code></pre>

<h2>Alur Program (Tiap Siklus Bangun)</h2>
<ol>
  <li><code>setup()</code> jalan dari awal (reset setelah deep sleep)</li>
  <li>Cetak alasan bangun (timer atau boot pertama)</li>
  <li>Inisialisasi DHT22 + jeda 2 detik</li>
  <li>Connect WiFi (timeout 15 detik)</li>
  <li>Connect MQTT + <code>setBufferSize(512)</code></li>
  <li>Publish satu payload JSON</li>
  <li>Disconnect MQTT &amp; matikan WiFi</li>
  <li><code>esp_deep_sleep_start()</code> — tidur <code>DURASI_TIDUR_DETIK</code></li>
</ol>

<h2>Uji Coba</h2>
<ol>
  <li>Upload sketch, buka <strong>Serial Monitor 115200</strong></li>
  <li>Catat log: WiFi OK → MQTT → publish JSON → "Deep sleep 600 detik..."</li>
  <li>Serial berhenti — ESP32 tidur (normal)</li>
  <li>Di MQTT Explorer, connect ke <code>test.mosquitto.org</code>, subscribe <code>kodingindonesia/esp32/dht22/data</code></li>
  <li>Tunggu ~10 menit (atau ubah <code>DURASI_TIDUR_DETIK</code> jadi <code>60</code> untuk uji cepat) — pesan baru muncul periodik</li>
</ol>

<pre><code class="language-bash">mosquitto_sub -h test.mosquitto.org -t "kodingindonesia/esp32/dht22/data" -v</code></pre>

<blockquote>
  <p><strong>Uji cepat:</strong> Set <code>DURASI_TIDUR_DETIK = 60</code> saat development, kembalikan ke 600 (10 menit) untuk deploy baterai.</p>
</blockquote>

<h2>Optimasi Hemat Daya</h2>
<ul>
  <li><strong><code>setCpuFrequencyMhz(80)</code></strong> — kurangi konsumsi saat aktif (boleh 160 jika connect WiFi sering gagal)</li>
  <li><strong><code>WiFi.mode(WIFI_OFF)</code></strong> — wajib sebelum sleep; jangan biarkan radio idle</li>
  <li><strong>Satu publish per bangun</strong> — hindari loop panjang di <code>loop()</code></li>
  <li><strong>Timeout WiFi</strong> — jangan infinite loop; gagal → tidur lagi agar baterai tidak habis di jaringan WiFi rumah yang lemah</li>
  <li><strong>DHT22</strong> — selalu <code>delay(2000)</code> setelah <code>begin()</code> pasca deep sleep</li>
  <li><strong>Multimeter</strong> — ukur arus di jalur 3.3V saat bangun vs saat tidur untuk validasi optimasi (harapkan lonjakan singkat saat WiFi connect)</li>
</ul>

<h2>Tips &amp; Troubleshooting</h2>
<ul>
  <li><strong>DHT22 NaN setelah bangun:</strong> Perpanjang delay; cek wiring &amp; pull-up; hindari kabel panjang tanpa shield</li>
  <li><strong>Boot loop tanpa tidur:</strong> Pastikan tidak ada kode setelah <code>esp_deep_sleep_start()</code> yang mengharapkan lanjut; cek brownout jika pakai baterai lemah (gunakan supply stabil)</li>
  <li><strong>Tidak ada pesan MQTT:</strong> Topic case-sensitive; cek <code>setBufferSize(512)</code>; lihat <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">artikel MQTT</a></li>
  <li><strong>Baterai cepat habis:</strong> Kurangi frekuensi bangun; pastikan WiFi benar-benar off; ukur arus dengan multimeter untuk baseline</li>
  <li><strong>Upload gagal setelah sleep:</strong> Tekan tombol <strong>BOOT</strong> saat upload, atau colok USB lalu reset sebelum ESP32 sempat tidur</li>
</ul>

<h2>Langkah Selanjutnya (Seri 2)</h2>
<ul>
  <li><strong><a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">NVS + WiFiManager ESP32</a></strong> — simpan kredensial WiFi di flash, tanpa hardcode <code>ssid</code>/<code>password</code> di kode produksi</li>
  <li><strong><a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Broker Mosquitto pribadi</a></strong> — pindah dari <code>test.mosquitto.org</code> dengan autentikasi</li>
  <li><strong><a href="/artikel/i2c-esp32-sensor-bme280-suhu-tekanan-mqtt">Sensor BME280 via I2C</a></strong> — upgrade akurasi + tekanan udara untuk node lapangan</li>
  <li>Kembali ke <a href="/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22">dashboard capstone Seri 1</a> untuk membandingkan node always-on vs battery-powered</li>
</ul>

<blockquote>
  <p><strong>Keamanan:</strong> Jangan hardcode password WiFi di repo publik. Untuk proyek lapangan, gunakan <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">NVS + WiFiManager</a> atau simpan kredensial di file terpisah yang tidak di-commit.</p>
</blockquote>

<p>Ini langkah pertama menuju node IoT yang benar-benar <em>wireless</em> — sensor di mana adaptor listrik tidak tersedia. Lanjutkan Seri 2 di <a href="/artikel">halaman artikel</a> Koding Indonesia.</p>
HTML;
    }
}
