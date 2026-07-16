<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article22Seeder extends Seeder
{
    public function run(): void
    {
        $admin  = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first();

        if (! $admin || ! $iotCat) {
            throw new \RuntimeException('User atau kategori tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'esphome-flash-esp32-tanpa-coding-arduino';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $iotCat->id,
                'title'           => 'ESPHome: Flash ESP32 Tanpa Coding Arduino',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'ESPHome ESP32 Indonesia — Flash Tanpa Arduino IDE',
                'seo_description' => 'Flash ESP32 dengan ESPHome + Home Assistant: sensor DHT22, relay lampu, YAML sederhana, OTA, dan perbandingan dengan sketch MQTT manual.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        Tag::updateOrCreate(['slug' => 'esphome'], ['name' => 'esphome']);

        $tagIds = Tag::whereIn('slug', [
            'esp32', 'esphome', 'homeassistant', 'mqtt', 'iot', 'smarthome', 'relay',
        ])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command->info('✓ Artikel ke-22 berhasil dipublish: ' . $article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan</h2>
<p>Di <a href="/artikel/home-assistant-integrasi-esp32-mqtt">artikel #21</a> kamu menghubungkan ESP32 ke Home Assistant lewat <strong>MQTT manual</strong>: edit <code>configuration.yaml</code>, definisikan sensor <code>value_template</code>, dan atur switch relay sendiri. Itu fleksibel — tapi banyak boilerplate.</p>

<p>Artikel ini melanjutkan <strong>Jalur C</strong> (smart home): setelah <a href="/artikel/home-assistant-integrasi-esp32-mqtt">integrasi MQTT manual (#21)</a>, kamu bisa mempercepat deploy node ESP32 dengan <strong>ESPHome</strong> — tanpa menulis <code>setup()</code> / <code>loop()</code> di Arduino IDE.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> Home Assistant sudah jalan (<a href="/artikel/home-assistant-integrasi-esp32-mqtt">artikel #21</a>). Paham wiring <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">DHT22 (#5)</a> dan <a href="/artikel/kontrol-lampu-esp32-mqtt-relay">relay (#8)</a> — pin yang sama dipakai di sini. Familiar dengan <a href="/artikel/gabungkan-dht22-relay-mqtt-esp32-satu-proyek">proyek gabungan DHT22 + relay (#9)</a> membantu membandingkan pendekatan.</p>
</blockquote>

<h2>Yang Kamu Butuhkan</h2>
<ul>
  <li><strong>Home Assistant</strong> dengan add-on <strong>ESPHome</strong> (Supervisor / HA OS) atau ESPHome Dashboard di PC</li>
  <li><strong>ESP32 DevKit</strong> + kabel USB (flash pertama)</li>
  <li>Sensor <strong>DHT22</strong> + modul <strong>relay 1 channel</strong> — wiring sama <a href="/artikel/gabungkan-dht22-relay-mqtt-esp32-satu-proyek">artikel #9</a></li>
  <li>PC dan ESP32 di <strong>WiFi 2.4 GHz</strong> yang sama dengan Home Assistant</li>
</ul>

<p><strong>Estimasi biaya:</strong> ESPHome &amp; add-on HA gratis — hardware sama proyek <a href="/artikel/gabungkan-dht22-relay-mqtt-esp32-satu-proyek">#9</a> (ESP32 ~35rb + DHT22 ~25rb + relay ~15rb).</p>

<h2>Arduino Sketch vs ESPHome</h2>
<table>
  <thead>
    <tr><th>Aspek</th><th><a href="/artikel/gabungkan-dht22-relay-mqtt-esp32-satu-proyek">Sketch Arduino (#9)</a></th><th>ESPHome (artikel ini)</th></tr>
  </thead>
  <tbody>
    <tr><td>Bahasa</td><td>C++ (<code>.ino</code>)</td><td><strong>YAML</strong> deklaratif</td></tr>
    <tr><td>Integrasi HA</td><td>MQTT manual di <code>configuration.yaml</code> (<a href="/artikel/home-assistant-integrasi-esp32-mqtt">#21</a>)</td><td><strong>Native API</strong> — entitas otomatis</td></tr>
    <tr><td>OTA</td><td>ArduinoOTA / <a href="/artikel/ota-update-firmware-esp32-via-wifi">custom OTA (#15)</a></td><td>OTA bawaan ESPHome</td></tr>
    <tr><td>Broker eksternal</td><td>Wajib <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Mosquitto (#16)</a></td><td>Opsional — bisa tetap pakai <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Mosquitto (#16)</a></td></tr>
    <tr><td>Kapan pakai</td><td>Logika custom, protokol non-HA</td><td>Node sensor/aktuator cepat di smart home</td></tr>
  </tbody>
</table>

<h2>Arsitektur: ESPHome + Home Assistant</h2>
<table>
  <thead>
    <tr><th>Komponen</th><th>Peran</th><th>Koneksi</th></tr>
  </thead>
  <tbody>
    <tr><td><strong>ESP32</strong> (firmware ESPHome)</td><td>Baca DHT22, kontrol relay GPIO</td><td>WiFi/LAN → Home Assistant</td></tr>
    <tr><td><strong>ESPHome add-on</strong></td><td>Compile YAML → flash / OTA</td><td>Di dalam HA atau PC lokal</td></tr>
    <tr><td><strong>Home Assistant</strong></td><td>Dashboard, automasi, native API</td><td>Terima entitas tanpa edit MQTT manual</td></tr>
    <tr><td><strong>Mosquitto</strong> (opsional)</td><td>Broker untuk node Arduino lain</td><td><a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">#16</a> — lihat bagian opsional di bawah</td></tr>
  </tbody>
</table>

<p>Alur data secara singkat:</p>
<figure role="img" aria-label="Diagram alur ESPHome: YAML dikompile ke firmware ESP32, lalu Native API ke Home Assistant dengan entitas sensor dan switch" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 620 460" style="display:block;max-width:620px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="ehArr" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/></marker>
    <marker id="ehArrO" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#FF7A2F"/></marker>
    <marker id="ehArrG" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#2E7D32"/></marker>
  </defs>
  <rect x="0" y="0" width="620" height="460" fill="#F5F5F0" rx="6"/>
  <!-- YAML -->
  <rect x="40" y="20" width="200" height="55" rx="6" fill="#FFF3E8" stroke="#000" stroke-width="2.5"/>
  <text x="140" y="42" text-anchor="middle" fill="#1a1a1a" font-size="13" font-weight="700">YAML ESPHome</text>
  <text x="140" y="58" text-anchor="middle" fill="#4A5568" font-size="10">deklaratif · secrets.yaml</text>
  <!-- Arrow YAML → compile -->
  <line x1="240" y1="47" x2="288" y2="47" stroke="#FF7A2F" stroke-width="2.5" marker-end="url(#ehArrO)"/>
  <text x="264" y="38" text-anchor="middle" fill="#FF7A2F" font-size="9" font-weight="700">compile</text>
  <!-- Firmware ESP32 -->
  <rect x="300" y="20" width="280" height="55" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="440" y="42" text-anchor="middle" fill="#1a1a1a" font-size="13" font-weight="700">Firmware ESP32</text>
  <text x="440" y="58" text-anchor="middle" fill="#4A5568" font-size="10">DHT22 GPIO4 · Relay GPIO26</text>
  <!-- Arrow ESP32 → HA -->
  <line x1="440" y1="75" x2="440" y2="128" stroke="#2979FF" stroke-width="2.5" marker-end="url(#ehArr)"/>
  <text x="490" y="108" fill="#2979FF" font-size="10" font-weight="700">WiFi · Native API · OTA ↓</text>
  <!-- Home Assistant -->
  <rect x="150" y="135" width="320" height="60" rx="6" fill="#2979FF" stroke="#000" stroke-width="2.5"/>
  <text x="310" y="162" text-anchor="middle" fill="#fff" font-size="14" font-weight="700">Home Assistant (#21)</text>
  <text x="310" y="180" text-anchor="middle" fill="#e3f2fd" font-size="10">dashboard · automasi · Native API</text>
  <!-- 4 entity outputs -->
  <line x1="200" y1="195" x2="110" y2="248" stroke="#2E7D32" stroke-width="2" marker-end="url(#ehArrG)"/>
  <line x1="270" y1="195" x2="250" y2="248" stroke="#2E7D32" stroke-width="2" marker-end="url(#ehArrG)"/>
  <line x1="350" y1="195" x2="390" y2="248" stroke="#2E7D32" stroke-width="2" marker-end="url(#ehArrG)"/>
  <line x1="420" y1="195" x2="520" y2="248" stroke="#2E7D32" stroke-width="2" marker-end="url(#ehArrG)"/>
  <!-- Entity 1 -->
  <rect x="15" y="255" width="180" height="55" rx="6" fill="#E8F5E9" stroke="#000" stroke-width="2"/>
  <text x="105" y="276" text-anchor="middle" fill="#1a1a1a" font-size="10" font-weight="700">sensor … suhu</text>
  <text x="105" y="292" text-anchor="middle" fill="#4A5568" font-size="9">DHT22 temperature</text>
  <!-- Entity 2 -->
  <rect x="210" y="255" width="180" height="55" rx="6" fill="#E8F5E9" stroke="#000" stroke-width="2"/>
  <text x="300" y="276" text-anchor="middle" fill="#1a1a1a" font-size="10" font-weight="700">sensor … kelembaban</text>
  <text x="300" y="292" text-anchor="middle" fill="#4A5568" font-size="9">DHT22 humidity</text>
  <!-- Entity 3 -->
  <rect x="405" y="255" width="200" height="55" rx="6" fill="#FFF3E8" stroke="#000" stroke-width="2"/>
  <text x="505" y="276" text-anchor="middle" fill="#1a1a1a" font-size="10" font-weight="700">switch … lampu_relay</text>
  <text x="505" y="292" text-anchor="middle" fill="#4A5568" font-size="9">GPIO26 active LOW</text>
  <!-- Automasi box -->
  <rect x="100" y="335" width="420" height="50" rx="6" fill="#FFEBEE" stroke="#C62828" stroke-width="2"/>
  <text x="310" y="356" text-anchor="middle" fill="#1a1a1a" font-size="12" font-weight="700">Automasi HA</text>
  <text x="310" y="372" text-anchor="middle" fill="#4A5568" font-size="10">suhu &gt; 30°C selama 5 menit → switch.turn_off</text>
  <!-- Summary -->
  <text x="310" y="420" text-anchor="middle" fill="#4A5568" font-size="11">YAML → compile → ESP32 → Native API → Home Assistant → entitas + automasi</text>
  <text x="310" y="440" text-anchor="middle" fill="#4A5568" font-size="10">Mosquitto (#16) opsional — publish paralel ke topic Seri 1</text>
</svg>
<figcaption style="margin-top:.75rem;font-size:.875rem;color:#4A5568;text-align:center">YAML ESPHome dikompile ke firmware ESP32, entitas muncul otomatis di <a href="/artikel/home-assistant-integrasi-esp32-mqtt">Home Assistant (#21)</a> via Native API — <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Mosquitto (#16)</a> opsional.</figcaption>
</figure>

<h2>Wiring Hardware</h2>
<p>Ikuti diagram di <a href="/artikel/gabungkan-dht22-relay-mqtt-esp32-satu-proyek">artikel #9</a> — pin konsisten di seluruh seri:</p>
<ul>
  <li><strong>DHT22 DATA</strong> → GPIO 4 (+ pull-up 10kΩ ke 3.3V)</li>
  <li><strong>Relay IN</strong> → GPIO 26 · modul umum <strong>active LOW</strong> (<a href="/artikel/kontrol-lampu-esp32-mqtt-relay">#8</a>)</li>
</ul>

<h2>Langkah 1 — Pasang Add-on ESPHome</h2>
<ol>
  <li>Buka Home Assistant → <strong>Settings</strong> → <strong>Add-ons</strong> → <strong>Add-on Store</strong></li>
  <li>Cari <strong>ESPHome</strong> → Install → Start → centang <strong>Show in sidebar</strong></li>
  <li>Klik <strong>ESPHome</strong> di sidebar → <strong>+ New Device</strong></li>
  <li>Beri nama misalnya <code>kindo-esp32-node</code> → pilih <strong>ESP32</strong> → <strong>Skip</strong> (kita edit YAML manual)</li>
</ol>

<blockquote>
  <p><strong>HA di Docker (Windows/Mac):</strong> Flash USB pertama kali butuh ESPHome Dashboard di PC, atau passthrough USB ke VM. Setelah OTA aktif, update berikutnya tanpa kabel.</p>
</blockquote>

<h2>Langkah 2 — File <code>secrets.yaml</code></h2>
<p>Jangan hardcode password WiFi di YAML utama. Buat <code>secrets.yaml</code> di folder device ESPHome:</p>
<pre><code class="language-yaml">wifi_ssid: "Nama_WiFi_Rumah"
wifi_password: "password_wifi_anda"
api_encryption_key: "ganti_dengan_string_acak_panjang"
ota_password: "password_ota_anda"
ap_password: "password_ap_fallback_anda"
# Opsional — hanya jika pakai blok mqtt: ke Mosquitto (#16)
mqtt_password: "password_mqtt_anda"</code></pre>

<p>Generate <code>api_encryption_key</code> dari menu ESPHome → perangkat → <strong>API encryption key</strong>.</p>

<h2>Langkah 3 — Konfigurasi YAML Lengkap</h2>
<p>Ganti isi <code>kindo-esp32-node.yaml</code> (nama file mengikuti <code>esphome.name</code>):</p>
<pre><code class="language-yaml">esphome:
  name: kindo-esp32-node
  friendly_name: Kindo ESP32 Node

esp32:
  board: esp32dev
  framework:
    type: arduino

wifi:
  ssid: !secret wifi_ssid
  password: !secret wifi_password
  ap:
    ssid: "Kindo-ESP32-Fallback"
    password: !secret ap_password

captive_portal:

logger:

api:
  encryption:
    key: !secret api_encryption_key

ota:
  - platform: esphome
    password: !secret ota_password

sensor:
  - platform: dht
    pin: GPIO4
    model: DHT22
    temperature:
      name: "Suhu Ruangan"
      id: suhu_ruangan
      device_class: temperature
      unit_of_measurement: "°C"
    humidity:
      name: "Kelembaban Ruangan"
      id: kelembaban_ruangan
      device_class: humidity
      unit_of_measurement: "%"
    update_interval: 10s

switch:
  - platform: gpio
    pin: GPIO26
    name: "Lampu Relay"
    id: lampu_relay
    inverted: true</code></pre>

<p><strong>Penjelasan singkat:</strong></p>
<ul>
  <li><code>dht</code> + <code>GPIO4</code> — sama dengan <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">sketch DHT22 (#5)</a></li>
  <li><code>inverted: true</code> — relay active LOW seperti di <a href="/artikel/kontrol-lampu-esp32-mqtt-relay">#8</a></li>
  <li><code>api</code> + <code>ota</code> — koneksi aman ke HA dan update firmware nanti tanpa USB</li>
  <li><code>captive_portal</code> — hotspot fallback jika WiFi gagal (mirip konsep <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">WiFiManager #12</a>); password AP di <code>secrets.yaml</code> → <code>ap_password</code></li>
</ul>

<h2>Langkah 4 — Flash Pertama (USB)</h2>
<ol>
  <li>Sambungkan ESP32 ke PC via USB</li>
  <li>Di ESPHome Dashboard → perangkat → <strong>Install</strong> → <strong>Plug into this computer</strong></li>
  <li>Pilih port COM yang muncul → tunggu compile &amp; upload (3–8 menit pertama kali)</li>
  <li>Setelah sukses, ESP32 connect WiFi → status <strong>Online</strong> di dashboard ESPHome</li>
</ol>

<h2>Langkah 5 — Integrasi ke Home Assistant</h2>
<ol>
  <li>Notifikasi <strong>Discovered</strong> muncul di HA → klik <strong>Configure</strong></li>
  <li>Masukkan <strong>API encryption key</strong> dari <code>secrets.yaml</code></li>
  <li>Buka <strong>Settings → Devices &amp; Services → ESPHome</strong> — tiga entitas baru: suhu, kelembaban, switch lampu</li>
  <li>Tambahkan ke dashboard — tidak perlu edit <code>configuration.yaml</code> seperti di <a href="/artikel/home-assistant-integrasi-esp32-mqtt">#21</a></li>
</ol>

<h2>Dashboard di Home Assistant</h2>
<ol>
  <li>Buka <strong>Overview</strong> → <strong>Edit dashboard</strong> (ikon pensil)</li>
  <li><strong>Add card</strong> → <strong>Entities</strong> → pilih sensor suhu, kelembaban, dan switch lampu</li>
  <li>Simpan — nilai suhu harus update tiap ~10 detik</li>
  <li>Opsional: klik sensor → <strong>Add to dashboard</strong> sebagai <strong>History graph</strong> (tren 24 jam)</li>
</ol>

<h2>Penjelasan Entity ID</h2>
<p>ESPHome membuat entity ID dari <code>friendly_name</code> + <code>name</code> per komponen. Contoh setelah integrasi:</p>
<ul>
  <li><strong><code>sensor.kindo_esp32_node_suhu_ruangan</code></strong> — suhu DHT22</li>
  <li><strong><code>sensor.kindo_esp32_node_kelembaban_ruangan</code></strong> — kelembaban</li>
  <li><strong><code>switch.kindo_esp32_node_lampu_relay</code></strong> — kontrol relay</li>
</ul>
<p>Cek nama pasti di <strong>Settings → Devices &amp; Services → ESPHome</strong> → klik device → lihat entitas, atau <strong>Developer Tools → States</strong> (cari <code>kindo_esp32</code>).</p>

<blockquote>
  <p><strong>Pro tip:</strong> Beri <code>friendly_name</code> yang jelas di YAML agar entitas mudah dicari di automasi HA.</p>
</blockquote>

<h2>Automasi Sederhana di Home Assistant</h2>
<p>Contoh rule: matikan lampu jika suhu &gt; 30°C (sama konsep <a href="/artikel/home-assistant-integrasi-esp32-mqtt">automasi #21</a>). Ganti <code>entity_id</code> sesuai device kamu:</p>
<pre><code class="language-yaml">alias: Matikan lampu jika panas
trigger:
  - platform: numeric_state
    entity_id: sensor.kindo_esp32_node_suhu_ruangan
    above: 30
    for:
      minutes: 5
action:
  - service: switch.turn_off
    target:
      entity_id: switch.kindo_esp32_node_lampu_relay</code></pre>

<p>Tempel via <strong>Settings → Automations → Create → Edit in YAML</strong>. Nama entity bisa sedikit berbeda — selalu verifikasi di <strong>Developer Tools → States</strong> sebelum simpan.</p>

<h2>OTA — Update Tanpa Kabel USB</h2>
<p>Setelah flash pertama, edit YAML lalu klik <strong>Install → Wirelessly</strong> di ESPHome Dashboard. Ini menggantikan kebutuhan <a href="/artikel/ota-update-firmware-esp32-via-wifi">ArduinoOTA custom (#15)</a> untuk node ESPHome — meski sketch Arduino manual tetap relevan untuk proyek non-HA.</p>

<h2>Opsional — Publish ke <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Mosquitto (#16)</a></h2>
<p>Jika kamu punya node Arduino lama (<a href="/artikel/gabungkan-dht22-relay-mqtt-esp32-satu-proyek">#9</a>) dan ingin ESPHome ikut ekosistem topic Seri 1, tambahkan blok <code>mqtt:</code>:</p>
<pre><code class="language-yaml">mqtt:
  broker: 192.168.1.50
  username: kindo_esp32
  password: !secret mqtt_password
  discovery: false
  topic_prefix: kodingindonesia/esp32/esphome</code></pre>

<p>Dengan <code>discovery: false</code>, HA tetap pakai Native API; Mosquitto menerima telemetri paralel. Untuk topic persis <code>kodingindonesia/esp32/dht22/data</code>, gunakan <code>on_...</code> template lanjutan — atau biarkan node Arduino dan ESPHome hidup berdampingan.</p>

<blockquote>
  <p><strong>Broker publik:</strong> Jangan pakai <code>test.mosquitto.org</code> untuk produksi — sama seperti peringatan di <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">#16</a> dan <a href="/artikel/home-assistant-integrasi-esp32-mqtt">#21</a>.</p>
</blockquote>

<h2>Gabung dengan Stack Seri 2</h2>
<ul>
  <li>Sensor <a href="/artikel/i2c-esp32-sensor-bme280-suhu-tekanan-mqtt">BME280 (#13)</a> — tambah blok <code>bme280</code> di YAML ESPHome (bus I2C sama)</li>
  <li>Konfigurasi lapangan <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">WiFiManager + NVS (#12)</a> — untuk sketch Arduino; ESPHome pakai <code>captive_portal</code> + <code>!secret</code></li>
  <li>Node Arduino lama tetap jalan via <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Mosquitto (#16)</a> — ESPHome bisa hidup berdampingan (bagian MQTT opsional di atas)</li>
</ul>

<h2>Uji Coba (Checklist)</h2>
<ol>
  <li>Flash YAML → status ESPHome <strong>Online</strong></li>
  <li>Entitas suhu &amp; kelembaban update tiap ~10 detik di HA</li>
  <li>Toggle <strong>Lampu Relay</strong> → LED/modul relay klik ON/OFF</li>
  <li>Putus WiFi router → ESP32 buka AP fallback <code>Kindo-ESP32-Fallback</code> (opsional uji)</li>
  <li>Edit YAML (misalnya ubah <code>update_interval</code>) → OTA wireless → verifikasi perubahan</li>
</ol>

<h2>Tips &amp; Troubleshooting</h2>
<ul>
  <li><strong>Compile error GPIO:</strong> Hindari GPIO 6–11 (flash internal). GPIO 4 &amp; 26 aman — sama <a href="/artikel/kontrol-lampu-esp32-mqtt-relay">#8</a></li>
  <li><strong>DHT22 NaN:</strong> Cek pull-up 10kΩ, kabel pendek, dan <code>model: DHT22</code> (bukan AM2302 salah pin)</li>
  <li><strong>Relay terbalik ON/OFF:</strong> Toggle <code>inverted: true/false</code> di YAML switch</li>
  <li><strong>HA tidak discover device:</strong> Pastikan ESP32 dan HA satu subnet; restart add-on ESPHome</li>
  <li><strong>Flash USB gagal:</strong> Tahan tombol BOOT saat upload; ganti kabel data (bukan charge-only)</li>
  <li><strong>Entity unavailable setelah reboot:</strong> Cek WiFi 2.4 GHz — ESP32 tidak support 5 GHz saja</li>
  <li><strong>MQTT + Native API bentrok:</strong> Pakai <code>discovery: false</code> pada blok <code>mqtt:</code></li>
</ul>

<h2>Keamanan &amp; Produksi</h2>
<ul>
  <li>Simpan <code>secrets.yaml</code> dan <code>api_encryption_key</code> — jangan commit ke repo publik</li>
  <li>Ganti <code>ota_password</code> default; OTA tanpa password = risiko di jaringan tamu</li>
  <li>Backup folder konfigurasi ESPHome bersama backup <code>/config</code> Home Assistant</li>
  <li>Untuk akses dari internet, amankan HA dengan reverse proxy + TLS — bukan expose port OTA langsung</li>
</ul>

<h2>Langkah Selanjutnya (Seri 2)</h2>
<ul>
  <li><strong><a href="/artikel/node-red-dashboard-otomasi-iot-mqtt-esp32">Node-RED (#23)</a></strong> — dashboard &amp; otomasi visual via MQTT</li>
  <li><strong><a href="/artikel/sensor-gerak-pir-esp32-lampu-mqtt-debounce">Sensor PIR + lampu MQTT (#24)</a></strong> — automasi gerak dengan debounce</li>
  <li><strong><a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">MQTT TLS (#17)</a></strong> — amankan broker Mosquitto di internet</li>
  <li>Kembali ke pendekatan manual: <a href="/artikel/home-assistant-integrasi-esp32-mqtt">Home Assistant + MQTT (#21)</a> untuk node non-ESPHome</li>
  <li>Capstone <strong><a href="/artikel/smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt">greenhouse (#39)</a></strong> — gabung sensor, relay, dan dashboard</li>
</ul>

<p>ESPHome mempercepat Jalur C smart home: dari YAML ke dashboard dalam hitungan menit. Lanjutkan di <a href="/artikel">halaman artikel</a> Koding Indonesia.</p>
HTML;
    }
}
