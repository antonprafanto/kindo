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

<p><strong>OTA (Over-The-Air)</strong> memungkinkan kamu mengunggah firmware baru lewat <strong>WiFi</strong> — dari Arduino IDE atau script CI — setelah flash awal via USB. Artikel ini menutup <strong>Jalur A</strong> (hardware &amp; sensor): <a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">deep sleep (#11)</a> → <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">NVS (#12)</a> → <a href="/artikel/i2c-esp32-sensor-bme280-suhu-tekanan-mqtt">BME280 (#13)</a> → <a href="/artikel/oled-ssd1306-esp32-tampilkan-data-sensor-i2c">OLED (#14)</a> → <strong>OTA (#15)</strong>.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> Paham <a href="/artikel/menghubungkan-esp32-wifi-kirim-data-server">koneksi WiFi (#4)</a>, familiar dengan <a href="/artikel/membuat-web-server-esp32-monitoring-sensor-dht22">web server ESP32 (#6)</a>, dan sudah implementasi <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">WiFiManager + NVS (#12)</a>. Stack sensor <a href="/artikel/oled-ssd1306-esp32-tampilkan-data-sensor-i2c">OLED (#14)</a> boleh digabung nanti.</p>
</blockquote>

<h2>Yang Kamu Butuhkan</h2>
<ul>
  <li>ESP32 DevKit (flash minimal <strong>4 MB</strong>)</li>
  <li>Kabel USB — hanya untuk <strong>flash pertama</strong></li>
  <li>PC + Arduino IDE di <strong>jaringan WiFi yang sama</strong> dengan ESP32</li>
  <li>Library <strong>WiFiManager</strong> (tzapu) — dari <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">artikel #12</a></li>
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

<figure role="img" aria-label="Diagram alur OTA ESP32: flash pertama via USB dari Arduino IDE, update berikutnya via WiFi OTA UDP ke ESP32 dengan partition app0 dan app1" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 620 510" style="display:block;max-width:620px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="otaArr" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/></marker>
    <marker id="otaArrO" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#FF7A2F"/></marker>
    <marker id="otaArrG" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#2E7D32"/></marker>
  </defs>
  <rect x="0" y="0" width="620" height="510" fill="#F5F5F0" rx="6"/>
  <!-- Arduino IDE -->
  <rect x="155" y="15" width="310" height="70" rx="6" fill="#FFF3E8" stroke="#000" stroke-width="2.5"/>
  <text x="310" y="42" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">PC — Arduino IDE</text>
  <text x="310" y="60" text-anchor="middle" fill="#4A5568" font-size="10">subnet WiFi sama · network port OTA</text>
  <text x="310" y="76" text-anchor="middle" fill="#4A5568" font-size="10">FIRMWARE_VERSION naik tiap rilis</text>
  <!-- USB path (left) -->
  <line x1="240" y1="85" x2="180" y2="138" stroke="#2979FF" stroke-width="2.5" marker-end="url(#otaArr)"/>
  <rect x="95" y="108" width="118" height="28" rx="14" fill="#E8F4FF" stroke="#2979FF" stroke-width="1.5"/>
  <text x="154" y="126" text-anchor="middle" fill="#2979FF" font-size="10" font-weight="700">USB flash #1</text>
  <!-- OTA path (right) -->
  <line x1="380" y1="85" x2="440" y2="138" stroke="#FF7A2F" stroke-width="2.5" marker-end="url(#otaArrO)"/>
  <rect x="407" y="108" width="138" height="28" rx="14" fill="#FFF3E8" stroke="#FF7A2F" stroke-width="1.5"/>
  <text x="476" y="126" text-anchor="middle" fill="#FF7A2F" font-size="10" font-weight="700">WiFi OTA UDP</text>
  <!-- ESP32 -->
  <rect x="120" y="150" width="380" height="72" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="310" y="178" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">ESP32 — WiFiManager (#12) + ArduinoOTA</text>
  <text x="310" y="198" text-anchor="middle" fill="#4A5568" font-size="10">kindo-esp32-node.local · ArduinoOTA.handle() di loop()</text>
  <text x="310" y="214" text-anchor="middle" fill="#4A5568" font-size="10">setPassword() · partition OTA wajib sebelum flash pertama</text>
  <!-- Partition boxes -->
  <rect x="155" y="235" width="145" height="38" rx="4" fill="#C8E6C9" stroke="#2E7D32" stroke-width="2"/>
  <text x="227" y="252" text-anchor="middle" fill="#1a1a1a" font-size="10" font-weight="700">app0 — aktif</text>
  <text x="227" y="266" text-anchor="middle" fill="#4A5568" font-size="9">firmware jalan</text>
  <rect x="320" y="235" width="145" height="38" rx="4" fill="#FFE0B2" stroke="#FF7A2F" stroke-width="2"/>
  <text x="392" y="252" text-anchor="middle" fill="#1a1a1a" font-size="10" font-weight="700">app1 — download</text>
  <text x="392" y="266" text-anchor="middle" fill="#4A5568" font-size="9">slot OTA baru</text>
  <!-- Arrow to reboot -->
  <line x1="310" y1="273" x2="310" y2="308" stroke="#2E7D32" stroke-width="2.5" marker-end="url(#otaArrG)"/>
  <text x="355" y="295" fill="#2E7D32" font-size="10" font-weight="700">OTA selesai ↓</text>
  <!-- Reboot / swap -->
  <rect x="155" y="315" width="310" height="55" rx="6" fill="#2E7D32" stroke="#000" stroke-width="2.5"/>
  <text x="310" y="340" text-anchor="middle" fill="#fff" font-size="13" font-weight="700">Bootloader swap app0 ↔ app1</text>
  <text x="310" y="358" text-anchor="middle" fill="#C8E6C9" font-size="10">reboot → firmware v1.0.1 aktif di node terpasang</text>
  <!-- 3 outcomes -->
  <line x1="210" y1="370" x2="110" y2="418" stroke="#2E7D32" stroke-width="2" marker-end="url(#otaArrG)"/>
  <line x1="310" y1="370" x2="310" y2="418" stroke="#2E7D32" stroke-width="2" marker-end="url(#otaArrG)"/>
  <line x1="410" y1="370" x2="510" y2="418" stroke="#2E7D32" stroke-width="2" marker-end="url(#otaArrG)"/>
  <rect x="15" y="425" width="190" height="50" rx="6" fill="#FFF3E8" stroke="#000" stroke-width="2"/>
  <text x="110" y="446" text-anchor="middle" fill="#1a1a1a" font-size="11" font-weight="700">Node lapangan</text>
  <text x="110" y="462" text-anchor="middle" fill="#4A5568" font-size="9">tanpa buka casing USB</text>
  <rect x="215" y="425" width="190" height="50" rx="6" fill="#FFF3E8" stroke="#000" stroke-width="2"/>
  <text x="310" y="446" text-anchor="middle" fill="#1a1a1a" font-size="11" font-weight="700">Serial Monitor</text>
  <text x="310" y="462" text-anchor="middle" fill="#4A5568" font-size="9">versi FIRMWARE_VERSION baru</text>
  <rect x="415" y="425" width="190" height="50" rx="6" fill="#FFF3E8" stroke="#000" stroke-width="2"/>
  <text x="510" y="446" text-anchor="middle" fill="#1a1a1a" font-size="11" font-weight="700">MQTT metadata (#16)</text>
  <text x="510" y="462" text-anchor="middle" fill="#4A5568" font-size="9">publish versi setelah OTA</text>
  <text x="310" y="492" text-anchor="middle" fill="#4A5568" font-size="11">USB flash pertama → WiFi OTA berikutnya → partition swap → node ter-update</text>
</svg>
<figcaption style="margin-top:.75rem;font-size:.875rem;color:#4A5568;text-align:center">Flash pertama via USB, update berikutnya lewat WiFi OTA — butuh <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">WiFiManager (#12)</a> dan partition <code>app0</code>/<code>app1</code>.</figcaption>
</figure>

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
  ArduinoOTA.setPassword("GANTI_PASSWORD_OTA");

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
  <li><strong><code>setPassword()</code></strong> — lindungi OTA; contoh di sketch untuk demo — di produksi simpan lewat <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">NVS (#12)</a></li>
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
  <li>Jangan buka portal WiFiManager setiap boot — hanya saat <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">provisioning (#12)</a></li>
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
  <li>Simpan password OTA di <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">NVS (pola #12)</a> — jangan hardcode di repo publik</li>
  <li>Node lapangan dengan <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">broker Mosquitto pribadi (#16)</a> — publish topic metadata versi firmware setelah OTA</li>
</ul>

<h2>Langkah Selanjutnya (Seri 2)</h2>
<ul>
  <li>Gabungkan OTA + <a href="/artikel/oled-ssd1306-esp32-tampilkan-data-sensor-i2c">OLED (#14)</a> + <a href="/artikel/i2c-esp32-sensor-bme280-suhu-tekanan-mqtt">BME280 (#13)</a> untuk node sensor lengkap</li>
  <li><strong><a href="/artikel/home-assistant-integrasi-esp32-mqtt">Home Assistant + ESP32 MQTT</a></strong> — integrasi ESP32 via broker pribadi</li>
  <li><strong><a href="/artikel/esphome-flash-esp32-tanpa-coding-arduino">ESPHome (#22)</a></strong> — OTA bawaan ESPHome untuk node smart home</li>
  <li><strong><a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">MQTT TLS (#17)</a></strong> — amankan broker di internet</li>
  <li>Capstone <strong><a href="/artikel/smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt">greenhouse (#39)</a></strong> — stack lapangan dengan OTA maintenance</li>
</ul>

<p>Dengan OTA, node ESP32 di Jalur A siap di-maintain tanpa kabel USB. Lanjutkan di <a href="/artikel">halaman artikel</a> Koding Indonesia.</p>
HTML;
    }
}
