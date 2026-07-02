<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article15Seeder extends Seeder
{
    public function run(): void
    {
        $admin  = User::first();
        $espCat = Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $espCat) {
            throw new \RuntimeException('User atau kategori tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'ota-update-firmware-esp32-via-wifi';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $espCat->id,
                'title'           => 'OTA Update Firmware ESP32 via WiFi',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'OTA Update ESP32 via WiFi — Firmware Tanpa Kabel USB',
                'seo_description' => 'Pelajari OTA (Over-The-Air) di ESP32: update firmware via WiFi dengan ArduinoOTA + WiFiManager, partition OTA, dan tips keamanan untuk deploy lapangan.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        Tag::updateOrCreate(['slug' => 'ota'], ['name' => 'ota']);

        $tagSlugs = ['esp32', 'ota', 'wifi', 'wifimanager', 'nvs', 'iot'];
        $tagIds   = Tag::whereIn('slug', $tagSlugs)->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command->info('✓ Artikel ke-15 berhasil dipublish: ' . $article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan</h2>
<p>Di <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">artikel NVS + WiFiManager (#12)</a> kita sudah menyiapkan ESP32 agar WiFi dan MQTT bisa dikonfigurasi tanpa hardcode. Node sensor di kebun, gudang, atau atap rumah tidak praktis jika setiap perbaikan bug mengharuskan <strong>buka casing + kabel USB</strong>.</p>

<p><strong>OTA (Over-The-Air)</strong> memungkinkan kamu mengunggah firmware baru lewat <strong>WiFi</strong> — dari Arduino IDE atau script CI — setelah flash awal via USB. Artikel ini menutup <strong>Jalur A</strong> (hardware &amp; sensor): deep sleep (#11) → NVS (#12) → BME280 (#13) → OLED (#14) → <strong>OTA (#15)</strong>.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> Paham <a href="/artikel/menghubungkan-esp32-wifi-kirim-data-server">koneksi WiFi (#4)</a>, familiar dengan <a href="/artikel/membuat-web-server-esp32-monitoring-sensor-dht22">web server ESP32 (#6)</a>, dan sudah implementasi <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">WiFiManager + NVS (#12)</a>. Stack sensor <a href="/artikel/oled-ssd1306-esp32-tampilkan-data-sensor-i2c">OLED (#14)</a> boleh digabung nanti.</p>
</blockquote>

<h2>Yang Kamu Butuhkan</h2>
<ul>
  <li>ESP32 DevKit (flash minimal <strong>4 MB</strong>)</li>
  <li>Kabel USB — hanya untuk <strong>flash pertama</strong></li>
  <li>PC + Arduino IDE di <strong>jaringan WiFi yang sama</strong> dengan ESP32</li>
  <li>Library <strong>WiFiManager</strong> (tzapu) — dari artikel #12</li>
</ul>

<h2>Apa itu OTA?</h2>
<table>
  <thead>
    <tr><th>Metode update</th><th>Kelebihan</th><th>Kekurangan</th></tr>
  </thead>
  <tbody>
    <tr><td><strong>USB serial</strong></td><td>Paling andal untuk development</td><td>Butuh akses fisik ke board</td></tr>
    <tr><td><strong>OTA via WiFi</strong></td><td>Update node terpasang jauh dari PC</td><td>Butuh partition khusus + keamanan</td></tr>
  </tbody>
</table>

<p>ESP32 menyimpan firmware di dua slot partition: <strong>app0</strong> (jalan sekarang) dan <strong>app1</strong> (slot download). Saat OTA selesai, bootloader pindah ke firmware baru.</p>

<h2>Partition Table OTA</h2>
<p>Di Arduino IDE → <strong>Tools → Partition Scheme</strong>, pilih skema yang mendukung OTA, misalnya:</p>
<ul>
  <li><strong>Default 4MB with spiffs</strong> — cukup untuk sketch kecil + OTA</li>
  <li><strong>Minimal SPIFFS (1.9MB APP with OTA/190KB SPIFFS)</strong> — jika sketch besar (BME280 + OLED + MQTT)</li>
</ul>

<blockquote>
  <p><strong>Penting:</strong> Ganti partition <strong>sebelum</strong> flash pertama OTA. Jika sudah upload tanpa slot OTA, flash ulang via USB dengan skema yang benar.</p>
</blockquote>

<h2>Install Library</h2>
<p>Ikuti <a href="/artikel/cara-install-arduino-ide-setup-esp32-board-manager">setup Arduino IDE &amp; board ESP32 (#2)</a> jika belum.</p>
<ul>
  <li><strong>WiFiManager</strong> (tzapu) — provisioning WiFi</li>
  <li><strong>ArduinoOTA</strong> — sudah included di core ESP32 Arduino</li>
</ul>
<p>Board: <strong>esp32</strong> by Espressif (<strong>v3.x</strong>).</p>

<h2>Kode Lengkap: WiFiManager + ArduinoOTA</h2>
<p>Sketch minimal OTA — ubah <code>FIRMWARE_VERSION</code> tiap rilis untuk memverifikasi update berhasil.</p>

<pre><code class="language-arduino">#include &lt;WiFi.h&gt;
#include &lt;WiFiManager.h&gt;
#include &lt;ArduinoOTA.h&gt;

#define FIRMWARE_VERSION "1.0.0"

void setupOTA() {
  ArduinoOTA.setHostname("kindo-esp32-node");
  ArduinoOTA.setPassword("kindo_ota_2026");

  ArduinoOTA.onStart([]() {
    Serial.println("OTA mulai...");
  });
  ArduinoOTA.onEnd([]() {
    Serial.println("\nOTA selesai — reboot...");
  });
  ArduinoOTA.onProgress([](unsigned int progress, unsigned int total) {
    Serial.printf("Progress: %u%%\r", (progress * 100) / total);
  });
  ArduinoOTA.onError([](ota_error_t err) {
    Serial.printf("OTA error[%u]: ", err);
    if (err == OTA_AUTH_ERROR) Serial.println("Auth gagal");
    else if (err == OTA_BEGIN_ERROR) Serial.println("Begin gagal");
    else if (err == OTA_CONNECT_ERROR) Serial.println("Connect gagal");
    else if (err == OTA_RECEIVE_ERROR) Serial.println("Receive gagal");
    else if (err == OTA_END_ERROR) Serial.println("End gagal");
  });

  ArduinoOTA.begin();
  Serial.print("OTA siap — versi firmware: ");
  Serial.println(FIRMWARE_VERSION);
  Serial.print("Hostname: kindo-esp32-node.local | IP: ");
  Serial.println(WiFi.localIP());
}

void setup() {
  Serial.begin(115200);
  delay(500);

  WiFiManager wm;
  wm.setConfigPortalTimeout(180);
  if (!wm.autoConnect("KindoESP32-Setup")) {
    ESP.restart();
  }

  setupOTA();
}

void loop() {
  ArduinoOTA.handle();
  delay(10);
}</code></pre>

<h2>Penjelasan Bagian Kritis</h2>
<ul>
  <li><strong><code>ArduinoOTA.begin()</code></strong> — daftarkan layanan OTA di port UDP setelah WiFi terhubung</li>
  <li><strong><code>ArduinoOTA.handle()</code></strong> — wajib dipanggil di <code>loop()</code> secara rutin</li>
  <li><strong><code>setHostname()</code></strong> — muncul sebagai <code>kindo-esp32-node</code> di Arduino IDE → Ports</li>
  <li><strong><code>setPassword()</code></strong> — lindungi OTA; contoh di sketch untuk demo — di produksi simpan lewat NVS (#12)</li>
  <li><strong><code>FIRMWARE_VERSION</code></strong> — naikkan ke <code>"1.0.1"</code> untuk uji OTA berhasil</li>
  <li><strong>Partition OTA</strong> — sketch harus muat di setengah flash (slot app)</li>
</ul>

<h2>Uji Coba (Step-by-Step)</h2>
<ol>
  <li>Arduino IDE → <strong>Tools → Partition Scheme</strong> → pilih skema dengan OTA</li>
  <li>Upload sketch via <strong>USB</strong> (flash pertama)</li>
  <li>Serial Monitor <strong>115200</strong> — catat IP dan pesan <code>OTA siap</code></li>
  <li>Ubah <code>FIRMWARE_VERSION</code> menjadi <code>"1.0.1"</code> (tanpa ubah logic lain)</li>
  <li>Arduino IDE → <strong>Tools → Port</strong> → pilih <code>kindo-esp32-node at 192.168.x.x</code> (network port)</li>
  <li>Upload lagi — masukkan password OTA saat diminta</li>
  <li>ESP32 reboot — Serial Monitor menampilkan versi <strong>1.0.1</strong></li>
</ol>

<blockquote>
  <p><strong>Pro tip:</strong> Jika network port tidak muncul, pastikan PC dan ESP32 di subnet WiFi sama, firewall Windows mengizinkan Arduino IDE, dan hostname tidak bentrok dengan perangkat lain.</p>
</blockquote>

<h2>Gabung dengan Stack Seri 2</h2>
<p>OTA bisa ditambahkan ke sketch <a href="/artikel/oled-ssd1306-esp32-tampilkan-data-sensor-i2c">BME280 + OLED (#14)</a> atau <a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">deep sleep (#11)</a>:</p>
<ul>
  <li>Panggil <code>ArduinoOTA.handle()</code> di <code>loop()</code> utama (node USB/adaptor)</li>
  <li>Untuk deep sleep: OTA hanya aktif saat bangun — atau sediakan mode maintenance (GPIO tombol tahan = tidak tidur + OTA aktif)</li>
  <li>Jangan buka portal WiFiManager setiap boot — hanya saat provisioning (#12)</li>
</ul>

<h2>Tips &amp; Troubleshooting</h2>
<ul>
  <li><strong>Network port tidak muncul:</strong> Cek WiFi sama, reboot ESP32, coba upload via IP manual (plugin espota)</li>
  <li><strong>OTA_AUTH_ERROR:</strong> Password OTA salah — cocokkan <code>setPassword()</code> dengan prompt Arduino IDE</li>
  <li><strong>Sketch too big:</strong> Pilih partition lebih besar atau kurangi fitur (OLED/MQTT) untuk slot OTA</li>
  <li><strong>OTA gagal setengah jalan:</strong> Jangan cabut listrik; ESP32 rollback ke app0 jika download corrupt</li>
  <li><strong>Portal WiFiManager tiap boot:</strong> NVS WiFi ter-reset — lihat <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">troubleshooting #12</a>; coba <code>wm.resetSettings()</code> lalu provisioning ulang</li>
  <li><strong>Compile error WiFiManager:</strong> Update tzapu ke 2.x; board <strong>esp32 v3.x</strong></li>
  <li><strong>WiFi 2.4 GHz:</strong> ESP32 tidak support jaringan <strong>5 GHz saja</strong></li>
</ul>

<h2>Keamanan &amp; Produksi</h2>
<ul>
  <li><strong>Wajib</strong> pakai <code>ArduinoOTA.setPassword()</code> — jangan deploy OTA tanpa auth di LAN pelanggan</li>
  <li>Untuk update via internet (bukan LAN), pertimbangkan HTTPS OTA atau tunnel VPN — di luar scope artikel ini</li>
  <li>Simpan password OTA di NVS (pola #12) — jangan hardcode di repo publik</li>
  <li>Node lapangan dengan <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">broker Mosquitto pribadi (#16)</a> — publish topic metadata versi firmware setelah OTA</li>
</ul>

<h2>Langkah Selanjutnya (Seri 2)</h2>
<ul>
  <li>Gabungkan OTA + <a href="/artikel/oled-ssd1306-esp32-tampilkan-data-sensor-i2c">OLED (#14)</a> + <a href="/artikel/i2c-esp32-sensor-bme280-suhu-tekanan-mqtt">BME280 (#13)</a> untuk node sensor lengkap</li>
  <li><strong><a href="/artikel/home-assistant-integrasi-esp32-mqtt">Home Assistant + ESP32 MQTT</a></strong> — integrasi ESP32 via broker pribadi</li>
  <li><strong><a href="/artikel/esphome-flash-esp32-tanpa-coding-arduino">ESPHome (#22)</a></strong> — OTA bawaan ESPHome untuk node smart home</li>
  <li><strong><a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">MQTT TLS (#17)</a></strong> — amankan broker di internet</li>
  <li>Capstone <strong>greenhouse (#39)</strong> — stack lapangan dengan OTA maintenance</li>
</ul>

<p>Dengan OTA, node ESP32 di Jalur A siap di-maintain tanpa kabel USB. Lanjutkan di <a href="/artikel">halaman artikel</a> Koding Indonesia.</p>
HTML;
    }
}
