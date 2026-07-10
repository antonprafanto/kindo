<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article29Seeder extends Seeder
{
    public function run(): void
    {
        $admin  = User::first();
        $espCat = Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $espCat) {
            throw new \RuntimeException('User atau kategori tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'migrasi-platformio-esp32-vscode-project-rapi';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $espCat->id,
                'title'           => 'Migrasi ke PlatformIO: Project ESP32 Lebih Rapi di VS Code',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'Migrasi PlatformIO ESP32 — VS Code Project Rapi',
                'seo_description' => 'Panduan migrasi dari Arduino IDE ke PlatformIO di VS Code: struktur project, platformio.ini, lib_deps, dan port sketch MQTT Seri 2 ke workflow modern.',
            ]
        );

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        Tag::updateOrCreate(['slug' => 'platformio'], ['name' => 'platformio']);
        Tag::updateOrCreate(['slug' => 'vscode'], ['name' => 'vscode']);

        $tagIds = Tag::whereIn('slug', [
            'esp32', 'platformio', 'vscode', 'iot', 'arduino', 'mqtt',
        ])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command->info('✓ Artikel ke-29 berhasil dipublish: ' . $article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — Dari Sketch Tunggal ke Project Profesional</h2>
<p>Seri 1 dan Seri 2 sejauh ini memakai <strong>Arduino IDE</strong> — cocok untuk belajar cepat. Tapi saat proyek bertambah (MQTT #7, gateway LoRa #28, multi-file), kamu butuh struktur folder, dependency terkelola, dan build yang konsisten.</p>

<p><strong>PlatformIO</strong> adalah ekosistem development embedded berbasis VS Code (atau IDE lain) yang mengelola board ESP32, library, dan upload lewat file <code>platformio.ini</code>. Artikel <strong>Jalur E</strong> ini memandu migrasi dari <a href="/artikel/cara-install-arduino-ide-setup-esp32-board-manager">Arduino IDE (#2)</a> ke workflow modern — tanpa mengubah logika sketch yang sudah kamu paham.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> Sudah bisa flash ESP32 di Arduino IDE (#2), paham dasar <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">MQTT (#7)</a>, dan pernah buat project multi-sketch seperti <a href="/artikel/gateway-lora-mqtt-esp32-sensor-jarak-jauh-dashboard">gateway LoRa (#28)</a>.</p>
</blockquote>

<h2>Mengapa PlatformIO?</h2>
<table>
  <thead>
    <tr><th>Aspek</th><th>Arduino IDE</th><th>PlatformIO</th></tr>
  </thead>
  <tbody>
    <tr><td>Struktur project</td><td>Satu folder sketch</td><td><code>src/</code>, <code>lib/</code>, <code>include/</code></td></tr>
    <tr><td>Library</td><td>Library Manager global</td><td><code>lib_deps</code> per project (versi pinned)</td></tr>
    <tr><td>Multi-board</td><td>Ganti menu Tools manual</td><td>Environment di <code>platformio.ini</code></td></tr>
    <tr><td>CI / tim</td><td>Sulit direproduksi</td><td><code>pio run</code> di terminal identik di mana saja</td></tr>
    <tr><td>IntelliSense</td><td>Terbatas</td><td>Autocomplete kuat di VS Code</td></tr>
  </tbody>
</table>

<p>PlatformIO <strong>bukan pengganti</strong> pemahaman ESP32 — kamu tetap menulis <code>setup()</code> / <code>loop()</code>. Yang berubah adalah <em>cara mengelola</em> project, terutama sebelum <a href="/artikel/ota-update-firmware-esp32-via-wifi">OTA (#15)</a> dan project capstone nanti.</p>

<h2>Instalasi — VS Code + PlatformIO</h2>
<ol>
  <li>Download <strong>Visual Studio Code</strong> dari code.visualstudio.com</li>
  <li>Buka Extensions (<kbd>Ctrl+Shift+X</kbd>) → cari <strong>PlatformIO IDE</strong> → Install</li>
  <li>Restart VS Code — ikon alien PlatformIO muncul di sidebar kiri</li>
  <li>Pastikan driver USB CH340/CP2102 sudah terpasang (sama seperti #2)</li>
</ol>

<p>Di Windows, PlatformIO mengunduh toolchain sendiri — pertama kali build bisa 5–15 menit. Sabar, ini sekali saja per versi board.</p>

<h2>Buat Project ESP32 Baru</h2>
<ol>
  <li>PlatformIO Home → <strong>New Project</strong></li>
  <li>Name: <code>kindo-esp32-mqtt</code></li>
  <li>Board: <strong>Espressif ESP32 Dev Module</strong></li>
  <li>Framework: <strong>Arduino</strong></li>
  <li>Finish — tunggu scaffold selesai</li>
</ol>

<p>Struktur default:</p>
<pre><code>kindo-esp32-mqtt/
├── platformio.ini    ← konfigurasi board &amp; library
├── src/
│   └── main.cpp      ← setup() / loop() kamu
├── lib/              ← library lokal (opsional)
└── include/          ← header custom</code></pre>

<h2>platformio.ini — Jantung Project</h2>
<pre><code class="language-ini">[env:esp32dev]
platform = espressif32
board = esp32dev
framework = arduino
monitor_speed = 115200
upload_speed = 921600

lib_deps =
  knolleary/PubSubClient @ ^2.8
  bblanchon/ArduinoJson @ ^7.0

build_flags =
  -D CORE_DEBUG_LEVEL=1</code></pre>

<p><strong>lib_deps</strong> menggantikan Library Manager Arduino — versi library terkunci di project, sehingga sketch MQTT kamu tidak rusak saat library global ter-update.</p>

<h2>Migrasi Sketch MQTT dari Arduino IDE</h2>
<p>Salin isi sketch <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">MQTT #7</a> ke <code>src/main.cpp</code>. Pastikan placeholder Seri 2 tetap:</p>
<ul>
  <li>SSID: <code>GANTI_NAMA_WIFI</code></li>
  <li>Password WiFi: <code>GANTI_PASSWORD_WIFI</code></li>
  <li>Broker: <code>192.168.1.50</code> · user <code>kindo_esp32</code></li>
  <li>MQTT pass: <code>GANTI_PASSWORD_MQTT</code></li>
  <li>Topic: <code>kodingindonesia/esp32/dht22/data</code></li>
</ul>

<pre><code class="language-cpp">#include &lt;Arduino.h&gt;
#include &lt;WiFi.h&gt;
#include &lt;PubSubClient.h&gt;

const char* ssid = "GANTI_NAMA_WIFI";
const char* password = "GANTI_PASSWORD_WIFI";
// ... sisa sketch MQTT #7</code></pre>

<p>Build: ikon <strong>✓</strong> di status bar atau terminal <code>pio run</code>. Upload: <code>pio run --target upload</code>. Serial: <code>pio device monitor</code>.</p>

<blockquote>
  <p><strong>Pro tip:</strong> Tambahkan <code>.gitignore</code> dengan folder <code>.pio/</code> — jangan commit build cache ke GitHub.</p>
</blockquote>

<h2>Multi-Environment (Dev vs Produksi)</h2>
<p>Pisahkan board di meja kerja vs node lapangan:</p>
<pre><code class="language-ini">[env:esp32dev]
platform = espressif32
board = esp32dev
framework = arduino
lib_deps = knolleary/PubSubClient @ ^2.8

[env:esp32cam]
platform = espressif32
board = esp32cam
framework = arduino
lib_deps = knolleary/PubSubClient @ ^2.8
build_flags = -DBOARD_HAS_PSRAM</code></pre>

<p>Upload ke environment tertentu: <code>pio run -e esp32cam --target upload</code> — berguna saat kamu punya <a href="/artikel/esp32-cam-streaming-mjpeg-capture-foto-wifi">ESP32-CAM (#27)</a> dan DevKit biasa dalam satu repo monorepo.</p>

<h2>Memecah Kode — src vs lib</h2>
<p>Project gateway #28 bisa dirapikan:</p>
<ul>
  <li><code>src/main.cpp</code> — setup WiFi + MQTT + loop LoRa</li>
  <li><code>lib/lora_packet/packet.h</code> — struct <code>lora_packet_t</code> bersama</li>
  <li><code>lib/mqtt_publish/mqtt_publish.cpp</code> — fungsi publish JSON</li>
</ul>

<p>Pola ini memudahkan unit test dan review kode sebelum modul <strong>FreeRTOS (#31)</strong>.</p>

<h2>Serial Monitor &amp; Debug</h2>
<pre><code class="language-bash"># Monitor dengan filter timestamp
pio device monitor --filter time

# Build + upload + monitor satu baris
pio run --target upload --target monitor</code></pre>

<p>Untuk debug MQTT, subscribe paralel di <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">broker Mosquitto pribadi (#16)</a>:</p>
<pre><code class="language-bash">mosquitto_sub -h 192.168.1.50 -u kindo_esp32 -P GANTI_PASSWORD_MQTT \
  -t kodingindonesia/esp32/dht22/data -v</code></pre>

<h2>PlatformIO vs ESPHome / Arduino IDE</h2>
<table>
  <thead>
    <tr><th>Tool</th><th>Cocok untuk</th></tr>
  </thead>
  <tbody>
    <tr><td><strong>Arduino IDE</strong></td><td>Belajar pertama, sketch kecil (#3 Blink)</td></tr>
    <tr><td><strong>PlatformIO</strong></td><td>Project besar, tim, CI, multi-board</td></tr>
    <tr><td><strong><a href="/artikel/esphome-flash-esp32-tanpa-coding-arduino">ESPHome (#22)</a></strong></td><td>Smart home YAML tanpa C++</td></tr>
  </tbody>
</table>

<h2>Keamanan &amp; Git</h2>
<ul>
  <li>Jangan commit <code>GANTI_PASSWORD_*</code> — gunakan <code>build_flags</code> + file lokal <code>secrets.ini</code> (gitignored)</li>
  <li>Contoh: <code>-DWIFI_SSID=\"\\\"GANTI_NAMA_WIFI\\\"\"</code> atau env var di CI</li>
  <li>Untuk produksi cloud, lihat <a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">MQTT TLS (#17)</a></li>
</ul>

<h2>Estimasi Biaya</h2>
<table>
  <thead>
    <tr><th>Item</th><th>Biaya</th></tr>
  </thead>
  <tbody>
    <tr><td>VS Code + PlatformIO</td><td>Gratis (open source)</td></tr>
    <tr><td>ESP32 DevKit (sudah punya)</td><td>Rp 0 tambahan</td></tr>
    <tr><td>Waktu setup pertama</td><td>±30–60 menit (download toolchain)</td></tr>
  </tbody>
</table>

<h2>Checklist: Siap Migrasi?</h2>
<ol>
  <li>Sketch MQTT #7 sudah jalan di Arduino IDE? → port ke <code>src/main.cpp</code></li>
  <li>Butuh dua board berbeda? → tambah <code>[env:...]</code></li>
  <li>Library bentrok versi? → pin di <code>lib_deps</code></li>
  <li>Gateway LoRa #28 makin besar? → pecah ke <code>lib/</code></li>
  <li>Butuh OTA dari PlatformIO? → plugin espota / artikel #15</li>
</ol>

<h2>Uji Coba</h2>
<ol>
  <li>Buat project <code>kindo-esp32-mqtt</code> di PlatformIO</li>
  <li>Salin sketch publish DHT22/MQTT — build sukses</li>
  <li>Upload — Serial menampilkan IP + connected MQTT</li>
  <li><code>mosquitto_sub</code> menerima payload JSON</li>
  <li>Tambah environment kedua — verifikasi <code>pio run -e</code> keduanya compile</li>
</ol>

<h2>Workflow Harian dengan PlatformIO</h2>
<p>Setelah migrasi pertama, kebiasaan kerja di VS Code biasanya seperti ini:</p>
<ol>
  <li><strong>Buka folder project</strong> — File → Open Folder → pilih root yang berisi <code>platformio.ini</code></li>
  <li><strong>Edit <code>src/main.cpp</code></strong> — sama seperti sketch Arduino, dengan autocomplete header</li>
  <li><strong>Build</strong> (<code>pio run</code>) — tangkap error compile sebelum upload</li>
  <li><strong>Upload</strong> — tombol panah atau <code>pio run -t upload</code></li>
  <li><strong>Monitor</strong> — <code>pio device monitor</code> untuk log Serial</li>
  <li><strong>Commit Git</strong> — hanya source (<code>src/</code>, <code>lib/</code>, <code>platformio.ini</code>), bukan <code>.pio/</code></li>
</ol>

<p>Untuk project yang sudah pakai <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">MQTT (#7)</a>, alur debug tetap: Serial untuk status WiFi, <code>mosquitto_sub</code> untuk payload broker. PlatformIO tidak mengubah protokol — hanya mempercepat iterasi saat kamu menambah library atau environment baru.</p>

<h2>Integrasi dengan Tooling Seri 2</h2>
<p>PlatformIO melengkapi artikel infrastruktur yang sudah kamu baca:</p>
<ul>
  <li><strong><a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Mosquitto (#16)</a></strong> — broker tetap di Raspberry Pi/VPS; ESP32 hanya client MQTT</li>
  <li><strong><a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">Grafana (#19)</a></strong> — data dari topic <code>kodingindonesia/esp32/dht22/data</code> tidak berubah</li>
  <li><strong><a href="/artikel/gateway-lora-mqtt-esp32-sensor-jarak-jauh-dashboard">Gateway LoRa (#28)</a></strong> — refactor ke <code>lib/</code> agar packet LoRa dan publish MQTT terpisah</li>
  <li><strong><a href="/artikel/ota-update-firmware-esp32-via-wifi">OTA (#15)</a></strong> — upload awal via USB, update berikutnya bisa lewat WiFi</li>
</ul>

<p>Saat tim bertambah, <code>platformio.ini</code> menjadi kontrak: semua developer memakai versi board dan library yang sama — mengurangi bug “di laptop saya jalan”.</p>

<h2>Contoh platformio.ini Lengkap (MQTT + JSON)</h2>
<pre><code class="language-ini">[env:esp32dev]
platform = espressif32
board = esp32dev
framework = arduino
monitor_speed = 115200
upload_speed = 921600

lib_deps =
  knolleary/PubSubClient @ ^2.8
  bblanchon/ArduinoJson @ ^7.0

build_flags =
  -D CORE_DEBUG_LEVEL=1
  -D WIFI_SSID=\"GANTI_NAMA_WIFI\"
  -D WIFI_PASS=\"GANTI_PASSWORD_WIFI\"

; Opsional: monitor filter
monitor_filters = time, esp32_exception_decoder</code></pre>

<p>Dengan <code>build_flags</code>, credential bisa diinjeksi saat compile tanpa menulis literal di <code>main.cpp</code> — praktik yang disarankan sebelum deploy ke lapangan atau sebelum integrasi <a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">MQTT TLS (#17)</a>.</p>

<h2>FAQ Singkat</h2>
<dl>
  <dt><strong>Bisa pakai PlatformIO tanpa VS Code?</strong></dt>
  <dd>Ya — CLI <code>pio</code> standalone; ekstensi VS Code paling populer untuk pemula.</dd>
  <dt><strong>Sketch Arduino IDE lama masih bisa dibuka?</strong></dt>
  <dd>Ya — copy-paste ke <code>src/main.cpp</code>, tambahkan <code>#include &lt;Arduino.h&gt;</code>.</dd>
  <dt><strong>Harus migrasi semua project?</strong></dt>
  <dd>Tidak — Arduino IDE tetap OK untuk eksperimen cepat; PlatformIO untuk project yang disimpan &amp; di-deploy.</dd>
</dl>

<h2>Migrasi Step-by-Step dari Folder Arduino IDE</h2>
<p>Anggap kamu punya sketch <code>mqtt_dht22.ino</code> di Arduino IDE yang sudah jalan. Berikut urutan migrasi tanpa mengubah logika:</p>
<ol>
  <li>Di PlatformIO, buat project baru <code>kindo-esp32-mqtt</code> (framework Arduino)</li>
  <li>Buka <code>src/main.cpp</code> — hapus template default</li>
  <li>Salin seluruh isi <code>.ino</code> ke <code>main.cpp</code></li>
  <li>Tambahkan baris pertama: <code>#include &lt;Arduino.h&gt;</code> (wajib di PlatformIO, tidak di .ino)</li>
  <li>Pindahkan library dari Library Manager ke <code>lib_deps</code> di <code>platformio.ini</code> — contoh PubSubClient: <code>knolleary/PubSubClient @ ^2.8</code></li>
  <li>Jalankan <code>pio run</code> — perbaiki error include atau nama fungsi</li>
  <li>Upload dan bandingkan output Serial dengan versi Arduino IDE</li>
  <li>Subscribe MQTT di broker #16 — pastikan topic dan JSON identik</li>
</ol>

<p>Jika sketch memakai banyak file tab di Arduino IDE, buat file tambahan di <code>src/</code> (misalnya <code>wifi_connect.cpp</code> + header di <code>include/</code>) atau pindahkan ke <code>lib/nama_modul/</code> untuk reuse di project gateway #28.</p>

<p>Folder <code>.pio/libdeps/</code> dihasilkan otomatis — jangan edit manual; selalu ubah <code>lib_deps</code> lalu <code>pio run</code> ulang.</p>

<h2>PlatformIO di CI/CD (Opsional)</h2>
<p>Salah satu keunggulan PlatformIO adalah perintah <code>pio run</code> yang deterministik — cocok untuk GitHub Actions atau pipeline internal tim. Pola minimal:</p>
<pre><code class="language-yaml">- name: Build ESP32 firmware
  run: |
    pip install platformio
    pio run -e esp32dev</code></pre>

<p>Build di CI tidak menggantikan uji di hardware nyata, tapi menangkap error compile dini — terutama saat <code>lib_deps</code> di-update atau saat menambah environment <code>esp32cam</code> untuk modul kamera. Simpan firmware binary dari <code>.pio/build/</code> sebagai artefak release jika kamu mendistribusikan OTA ke banyak node lapangan.</p>

<p>Untuk proyek pribadi, cukup build lokal; langkah CI ini opsional sampai kamu punya puluhan device seperti rencana capstone <strong>greenhouse (#39)</strong>.</p>

<h2>Tips &amp; Troubleshooting</h2>
<ul>
  <li><strong>Build gagal lib not found:</strong> Cek ejaan <code>lib_deps</code> di Registry PlatformIO</li>
  <li><strong>Upload timeout:</strong> Tahan BOOT seperti di #2; turunkan <code>upload_speed</code></li>
  <li><strong>IntelliSense merah tapi build OK:</strong> <code>pio run</code> sekali, lalu Reload Window VS Code</li>
  <li><strong>Port tidak muncul:</strong> Driver CH340/CP2102 — ulangi langkah #2</li>
</ul>

<h2>Langkah Selanjutnya (Jalur E)</h2>
<ul>
  <li><strong>ESP32 + Firebase (#30):</strong> backend cloud tanpa server sendiri</li>
  <li><strong><a href="/artikel/ota-update-firmware-esp32-via-wifi">OTA (#15)</a></strong> — update firmware dari PlatformIO</li>
  <li><strong><a href="/artikel/gateway-lora-mqtt-esp32-sensor-jarak-jauh-dashboard">Gateway LoRa (#28)</a></strong> — refactor ke struktur <code>lib/</code></li>
  <li><strong>FreeRTOS (#31)</strong> — task terpisah sensor/WiFi/MQTT</li>
  <li>Capstone <strong>greenhouse (#39)</strong> — monorepo sensor + gateway + dashboard</li>
</ul>

<p>PlatformIO membawa project ESP32 kamu ke level berikutnya — struktur rapi, dependency aman, siap kolaborasi. Lanjutkan Seri 2 di <a href="/artikel">halaman artikel</a> Koding Indonesia.</p>
HTML;
    }
}
