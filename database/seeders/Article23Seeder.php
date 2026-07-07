<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article23Seeder extends Seeder
{
    public function run(): void
    {
        $admin  = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first();

        if (! $admin || ! $iotCat) {
            throw new \RuntimeException('User atau kategori tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'node-red-dashboard-otomasi-iot-mqtt-esp32';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $iotCat->id,
                'title'           => 'Node-RED: Dashboard & Otomasi IoT Visual dengan MQTT',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'Node-RED IoT MQTT — Dashboard Visual ESP32',
                'seo_description' => 'Buat dashboard dan otomasi IoT visual dengan Node-RED: subscribe sensor DHT22 ESP32, kontrol relay MQTT, dan rule suhu tanpa coding backend.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        Tag::updateOrCreate(['slug' => 'nodered'], ['name' => 'nodered']);

        $tagIds = Tag::whereIn('slug', [
            'esp32', 'nodered', 'mqtt', 'iot', 'smarthome', 'homeassistant', 'relay',
        ])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command->info('✓ Artikel ke-23 berhasil dipublish: ' . $article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan</h2>
<p>Di <a href="/artikel/home-assistant-integrasi-esp32-mqtt">Home Assistant (#21)</a> dan <a href="/artikel/esphome-flash-esp32-tanpa-coding-arduino">ESPHome (#22)</a> kamu sudah membangun smart home lewat integrasi native. Artikel ini melanjutkan <strong>Jalur C</strong> dengan pendekatan berbeda: <strong>Node-RED</strong> — editor <em>flow</em> berbasis node yang menghubungkan MQTT, logika, dan dashboard visual tanpa menulis backend Python/PHP.</p>

<p>Node-RED sangat cocok untuk prototipe cepat, integrasi antar layanan, dan dashboard custom di LAN — sambil tetap memakai <strong>broker Mosquitto pribadi (#16)</strong> dan topic MQTT yang sama dengan ESP32 dari <a href="/artikel/gabungkan-dht22-relay-mqtt-esp32-satu-proyek">proyek gabungan (#9)</a>.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> Paham <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">MQTT dasar (#7)</a>, broker <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Mosquitto + auth (#16)</a> sudah jalan, dan ESP32 mempublish DHT22 + subscribe relay (sketch <a href="/artikel/gabungkan-dht22-relay-mqtt-esp32-satu-proyek">#9</a> atau setara — wiring <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">DHT22 (#5)</a>). Opsional: sudah baca <a href="/artikel/home-assistant-integrasi-esp32-mqtt">#21</a> / <a href="/artikel/esphome-flash-esp32-tanpa-coding-arduino">#22</a> untuk membandingkan stack smart home.</p>
</blockquote>

<h2>Yang Kamu Butuhkan</h2>
<ul>
  <li>PC / Raspberry Pi / VPS — bisa mesin yang sama dengan <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Mosquitto (#16)</a></li>
  <li><strong>Node.js</strong> 18+ (jika install npm) atau <strong>Docker</strong></li>
  <li><strong>ESP32</strong> yang publish ke topic Seri 1 (broker pribadi, bukan publik)</li>
  <li>Browser untuk editor Node-RED (<code>http://IP:1880</code>)</li>
</ul>

<p><strong>Estimasi biaya:</strong> Node-RED gratis (open-source) — hardware sama proyek sebelumnya; bisa pakai Raspberry Pi yang sudah menjalankan Mosquitto.</p>

<h2>Home Assistant vs ESPHome vs Node-RED</h2>
<table>
  <thead>
    <tr><th>Aspek</th><th>Home Assistant (#21)</th><th>ESPHome (#22)</th><th>Node-RED (artikel ini)</th></tr>
  </thead>
  <tbody>
    <tr><td>Fokus</td><td>Platform smart home lengkap</td><td>Firmware ESP32 dari YAML</td><td><strong>Integrasi &amp; flow</strong> visual</td></tr>
    <tr><td>UI</td><td>Dashboard HA built-in</td><td>Entitas di HA</td><td>Dashboard Node-RED + flow canvas</td></tr>
    <tr><td>Automasi</td><td>YAML / UI automasi HA</td><td>Via HA</td><td>Drag-and-drop node (if/switch/function)</td></tr>
    <tr><td>MQTT</td><td>Subscriber/publisher</td><td>Native API (+ MQTT opsional)</td><td><strong>Inti</strong> — mqtt in/out node</td></tr>
    <tr><td>Kapan pakai</td><td>Smart home rumah tangga</td><td>Flash ESP32 cepat</td><td>Prototipe, glue antar API, dashboard custom</td></tr>
  </tbody>
</table>

<h2>Arsitektur: ESP32 → Mosquitto → Node-RED</h2>
<table>
  <thead>
    <tr><th>Komponen</th><th>Peran</th><th>Koneksi</th></tr>
  </thead>
  <tbody>
    <tr><td><strong>ESP32</strong></td><td>Publisher sensor &amp; subscriber relay</td><td>WiFi → Mosquitto <code>:1883</code></td></tr>
    <tr><td><strong>Mosquitto</strong> (<a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">#16</a>)</td><td>Broker pusat</td><td>Topic Seri 1 + auth</td></tr>
    <tr><td><strong>Node-RED</strong></td><td>Dashboard + otomasi visual</td><td>Subscribe/publish MQTT yang sama</td></tr>
    <tr><td><strong>Home Assistant</strong> (opsional)</td><td>Bisa jalan paralel</td><td>Subscriber MQTT terpisah — tidak bentrok</td></tr>
  </tbody>
</table>

<p>Alur data secara singkat:</p>
<pre><code>  [ ESP32 — DHT22 + relay ]
      |
      |  publish: kodingindonesia/esp32/dht22/data  (JSON)
      |  subscribe: kodingindonesia/esp32/lampu/kontrol  (ON/OFF)
      v
  [ Mosquitto @ Pi / VPS ]  (#16)
      |
      |  MQTT
      v
  [ Node-RED :1880 ]
      |
      +-- dashboard: gauge suhu &amp; kelembaban
      +-- tombol: NYALA / MATI lampu
      +-- automasi: suhu &gt; 30°C → publish OFF</code></pre>

<p><strong>Topic MQTT</strong> (konsisten Seri 1):</p>
<ul>
  <li>Sensor: <code>kodingindonesia/esp32/dht22/data</code> — JSON <code>{"suhu":28.5,"kelembaban":65.2}</code></li>
  <li>Relay: <code>kodingindonesia/esp32/lampu/kontrol</code> — <code>ON</code> / <code>OFF</code></li>
</ul>

<h2>Langkah 1 — Install Node-RED (Docker)</h2>
<p>Cara termudah di server yang sudah punya Mosquitto:</p>
<pre><code class="language-yaml"># docker-compose.yml (tambahkan service di bawah Mosquitto)
services:
  nodered:
    image: nodered/node-red:latest
    container_name: nodered
    ports:
      - "1880:1880"
    volumes:
      - nodered_data:/data
    restart: unless-stopped

volumes:
  nodered_data:</code></pre>

<pre><code class="language-bash">docker compose up -d nodered
# Buka http://192.168.1.50:1880</code></pre>

<blockquote>
  <p><strong>Docker + Mosquitto di mesin sama:</strong> Dari dalam container Node-RED, broker MQTT pakai <strong>IP LAN host</strong> (mis. <code>192.168.1.50</code>) — bukan <code>localhost</code> / <code>127.0.0.1</code>, karena container punya network terpisah.</p>
</blockquote>

<blockquote>
  <p><strong>Install npm (alternatif):</strong> <code>npm install -g --unsafe-perm node-red</code> lalu <code>node-red</code> — cocok di Raspberry Pi tanpa Docker.</p>
</blockquote>

<h2>Langkah 2 — Pasang Dashboard UI</h2>
<ol>
  <li>Node-RED → menu ☰ → <strong>Manage palette</strong></li>
  <li>Tab <strong>Install</strong> → cari <strong>node-red-dashboard</strong></li>
  <li>Install → tunggu selesai → <strong>Deploy</strong></li>
  <li>Di palette kiri, buka grup <strong>dashboard</strong> (bukan <em>dashboard 2.0</em>)</li>
  <li>Drag <strong>ui_tab</strong> ke canvas → isi nama tab, misalnya <code>ESP32 Kindo</code></li>
  <li>Drag <strong>ui_group</strong> → pilih tab tadi → nama group <code>Sensor DHT22</code></li>
  <li>Dashboard tersedia di <code>http://IP:1880/ui</code> setelah node ui_* di-deploy</li>
</ol>

<h2>Langkah 3 — Konfigurasi Broker MQTT</h2>
<ol>
  <li>Drag node <strong>mqtt in</strong> ke canvas</li>
  <li>Klik ikon pensil pada node → <strong>Add new mqtt-broker</strong></li>
  <li>Isi (sesuaikan <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">#16</a>):
    <ul>
      <li>Server: <code>192.168.1.50</code> (IP LAN host; jika Node-RED <em>native</em> di mesin yang sama dengan Mosquitto, <code>127.0.0.1</code> boleh)</li>
      <li>Jika Mosquitto dan Node-RED dalam <strong>satu</strong> <code>docker-compose.yml</code>, isi Server dengan <strong>nama service</strong> Mosquitto (mis. <code>mosquitto</code>) — Docker DNS internal mengenali hostname antar-container</li>
      <li>Port: <code>1883</code></li>
      <li>Username: <code>kindo_esp32</code></li>
      <li>Password: isi lewat credential store Node-RED — jangan hardcode di flow yang di-export</li>
    </ul>
  </li>
  <li>Simpan broker — node mqtt in/out lain bisa pakai config yang sama</li>
</ol>

<blockquote>
  <p><strong>Broker publik:</strong> Jangan pakai <code>test.mosquitto.org</code> — sama seperti peringatan di <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">#16</a> dan <a href="/artikel/home-assistant-integrasi-esp32-mqtt">#21</a>.</p>
</blockquote>

<h2>Langkah 4 — Flow Sensor DHT22 (Dashboard)</h2>
<p>Rangkai node berikut (kiri → kanan):</p>
<ol>
  <li><strong>mqtt in</strong> — topic <code>kodingindonesia/esp32/dht22/data</code>, Output: auto-detect</li>
  <li><strong>json</strong> — parse payload string ke object</li>
  <li><strong>function</strong> — ekstrak nilai:
<pre><code class="language-javascript">msg.payload = msg.payload.suhu;
return msg;</code></pre>
    (Duplikasi cabang dari output <strong>json</strong> — function kedua untuk kelembaban:
<pre><code class="language-javascript">msg.payload = msg.payload.kelembaban;
return msg;</code></pre>
    lalu sambungkan ke <strong>ui_text</strong>)</li>
  <li><strong>ui_gauge</strong> — pilih <em>Group</em> <code>Sensor DHT22</code> yang sudah dibuat; set min 0, max 50 untuk suhu</li>
  <li><strong>ui_text</strong> — group yang sama; tampilkan angka kelembaban (%)</li>
</ol>

<p>Pastikan setiap node <strong>ui_gauge</strong> / <strong>ui_text</strong> terhubung ke <strong>ui_group</strong> — kalau group kosong, widget tidak muncul di <code>/ui</code>.</p>

<p>Klik <strong>Deploy</strong> → buka <code>/ui</code> → gauge harus bergerak setiap ~10 detik (interval publish ESP32).</p>

<h2>Langkah 5 — Kontrol Relay dari Dashboard</h2>
<ol>
  <li>Drag <strong>ui_group</strong> baru di tab yang sama → nama <code>Kontrol Lampu</code> (group terpisah dari <code>Sensor DHT22</code>)</li>
  <li>Drag <strong>ui_button</strong> — pilih group <code>Kontrol Lampu</code>; label <code>NYALA</code></li>
  <li>Drag <strong>change</strong> — set <code>msg.payload</code> = <code>ON</code> (string)</li>
  <li><strong>mqtt out</strong> — topic <code>kodingindonesia/esp32/lampu/kontrol</code>; QoS 0 cukup</li>
  <li><strong>Sambungkan berurutan:</strong> output <strong>ui_button</strong> → input <strong>change</strong> → input <strong>mqtt out</strong> (satu jalur untuk tombol NYALA)</li>
  <li>Duplikasi ketiga node untuk tombol <code>MATI</code> dengan payload <code>OFF</code> — tetap di group <code>Kontrol Lampu</code></li>
  <li>Deploy → uji di <code>/ui</code> — relay ESP32 harus klik (pastikan sketch <a href="/artikel/kontrol-lampu-esp32-mqtt-relay">#8</a> / <a href="/artikel/gabungkan-dht22-relay-mqtt-esp32-satu-proyek">#9</a> subscribe topic ini)</li>
</ol>

<blockquote>
  <p><strong>Pro tip:</strong> Beri nama node yang jelas (<code>DHT22 → Gauge Suhu</code>) — flow kompleks cepat berantakan tanpa label.</p>
</blockquote>

<h2>Langkah 6 — Automasi Visual (Suhu &gt; 30°C)</h2>
<p>Tambahkan cabang paralel dari output <strong>json</strong>:</p>
<ol>
  <li><strong>function</strong> — cek threshold:
<pre><code class="language-javascript">if (msg.payload.suhu &gt; 30) {
    return { payload: "OFF", topic: "kodingindonesia/esp32/lampu/kontrol" };
}
return null;</code></pre>
  </li>
  <li><strong>mqtt out</strong> — pilih broker yang sama dengan Langkah 3; <strong>kosongkan</strong> field Topic (Node-RED memakai <code>msg.topic</code> dari function) atau pilih <em>msg.topic</em> dari dropdown di field Topic</li>
</ol>

<p>Rule ini jalan setiap kali payload sensor masuk (~10 detik). Berbeda dengan automasi <a href="/artikel/home-assistant-integrasi-esp32-mqtt">Home Assistant (#21)</a> yang bisa pakai <code>for: 5 minutes</code> — kalau lampu sering berkedip, tambahkan node <strong>delay</strong> (mis. 5 menit, <em>rate limit</em> 1 msg) atau <strong>trigger</strong> hanya saat suhu naik melewati 30°C, bukan setiap pembacaan di atas threshold.</p>

<p>Ini setara automasi <code>numeric_state</code> di Home Assistant (#21), tapi sepenuhnya visual di canvas Node-RED.</p>

<h2>Import Flow (Opsional)</h2>
<p>Jika ingin mempercepat, salin JSON berikut → menu ☰ → <strong>Import</strong> → clipboard → Deploy. Sesuaikan broker MQTT setelah import:</p>
<pre><code class="language-json">[
  {
    "id": "tab_esp32",
    "type": "tab",
    "label": "ESP32 MQTT Kindo"
  },
  {
    "id": "mqtt_broker_kindo",
    "type": "mqtt-broker",
    "name": "Mosquitto Kindo",
    "broker": "192.168.1.50",
    "port": "1883",
    "clientid": "nodered-kindo",
    "usetls": false,
    "credentials": {
      "user": "kindo_esp32",
      "password": "GANTI_PASSWORD_MQTT_ANDA"
    }
  }
]</code></pre>

<p>JSON di atas hanya contoh <strong>tab + broker</strong> — setelah import, double-click broker node dan isi password lewat <strong>credential</strong> Node-RED (jangan simpan password asli di artikel/repo publik). Lengkapi node mqtt in, json, ui_gauge, dan mqtt out mengikuti Langkah 4–6.</p>

<h2>Uji Coba (Checklist)</h2>
<ol>
  <li>ESP32 online — verifikasi publish dari terminal:
<pre><code class="language-bash">mosquitto_sub -h 192.168.1.50 -p 1883 \
  -u kindo_esp32 -P 'PASSWORD_ANDA' \
  -t "kodingindonesia/esp32/dht22/data" -v</code></pre>
  </li>
  <li>Node-RED <code>/ui</code> menampilkan suhu &amp; kelembaban</li>
  <li>Tombol NYALA/MATI mengubah relay — atau uji tanpa dashboard:
<pre><code class="language-bash">mosquitto_pub -h 192.168.1.50 -p 1883 \
  -u kindo_esp32 -P 'PASSWORD_ANDA' \
  -t "kodingindonesia/esp32/lampu/kontrol" -m "ON"</code></pre>
  </li>
  <li>Automasi: hangatkan sensor (&gt;30°C) → lampu mati otomatis</li>
  <li>Restart container Node-RED — flow harus restore dari volume <code>/data</code></li>
</ol>

<h2>Gabung dengan Stack Seri 2</h2>
<ul>
  <li><a href="/artikel/home-assistant-integrasi-esp32-mqtt">Home Assistant (#21)</a> + Node-RED bisa subscribe topic yang sama — HA untuk rumah, Node-RED untuk integrasi custom</li>
  <li><a href="/artikel/esphome-flash-esp32-tanpa-coding-arduino">ESPHome (#22)</a> untuk node baru; Node-RED untuk orkestrasi MQTT lintas perangkat</li>
  <li>Sensor <a href="/artikel/i2c-esp32-sensor-bme280-suhu-tekanan-mqtt">BME280 (#13)</a> — tambah <strong>mqtt in</strong> ke topic <code>kodingindonesia/esp32/bme280/data</code></li>
  <li><strong><a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">Subscriber Python (#18)</a></strong> — simpan histori MQTT ke MySQL (flow Node-RED + function, atau subscriber Python)</li>
</ul>

<h2>Tips &amp; Troubleshooting</h2>
<ul>
  <li><strong>Dashboard kosong:</strong> Pastikan sudah Deploy; cek tab UI di <code>/ui</code>; install <code>node-red-dashboard</code></li>
  <li><strong>mqtt in tidak terima data:</strong> Topic case-sensitive; cek auth broker; ESP32 harus ke broker pribadi (#16)</li>
  <li><strong>JSON parse error:</strong> Pastikan node <strong>json</strong> setelah mqtt in; payload harus valid JSON dari <a href="/artikel/gabungkan-dht22-relay-mqtt-esp32-satu-proyek">#9</a></li>
  <li><strong>Relay tidak merespons:</strong> Cek topic <code>lampu/kontrol</code>; ESP32 perlu <code>mqttClient.loop()</code> di sketch</li>
  <li><strong>Node-RED &amp; Mosquitto di Docker:</strong> Gunakan IP host atau nama service compose, bukan <code>localhost</code> dari dalam container lain</li>
  <li><strong>Port 1880 tidak bisa diakses:</strong> Buka firewall LAN; jangan expose ke internet tanpa auth</li>
  <li><strong>WiFi 2.4 GHz:</strong> ESP32 tidak support jaringan 5 GHz saja</li>
</ul>

<h2>Keamanan &amp; Produksi</h2>
<ul>
  <li>Aktifkan <strong>adminAuth</strong> di <code>settings.js</code> Node-RED — editor tanpa password berbahaya di LAN tamu</li>
  <li>Jangan expose port <code>1880</code> ke internet tanpa HTTPS + reverse proxy</li>
  <li>Simpan kredensial MQTT di credential store Node-RED (bukan hardcode di flow export publik)</li>
  <li>Backup folder <code>/data</code> (volume Docker) berisi flow — sama pentingnya dengan backup HA</li>
  <li>Untuk MQTT over internet, gunakan <a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">TLS (#17)</a> — bukan port 1883 plain</li>
</ul>

<h2>Langkah Selanjutnya (Seri 2)</h2>
<ul>
  <li><strong><a href="/artikel/sensor-gerak-pir-esp32-lampu-mqtt-debounce">PIR + lampu MQTT (#24)</a></strong> — automasi gerak dengan debounce &amp; hold time</li>
  <li><strong><a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">MQTT TLS (#17)</a></strong> — amankan Mosquitto di internet</li>
  <li><strong><a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">Subscriber Python (#18)</a></strong> → MySQL untuk histori sensor</li>
  <li>Capstone <strong>greenhouse (#39)</strong> — gabung sensor, Node-RED/HA, dan aktuator</li>
</ul>

<p>Node-RED melengkapi Jalur C: prototipe dashboard dan otomasi MQTT dalam hitungan menit. Lanjutkan di <a href="/artikel">halaman artikel</a> Koding Indonesia.</p>
HTML;
    }
}
