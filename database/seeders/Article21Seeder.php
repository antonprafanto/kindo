<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article21Seeder extends Seeder
{
    public function run(): void
    {
        $admin  = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first();

        if (! $admin || ! $iotCat) {
            throw new \RuntimeException('User atau kategori tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'home-assistant-integrasi-esp32-mqtt';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $iotCat->id,
                'title'           => 'Home Assistant + ESP32 via MQTT: Smart Home Dashboard',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'Home Assistant + ESP32 MQTT — Smart Home Indonesia',
                'seo_description' => 'Hubungkan ESP32 ke Home Assistant via Mosquitto: sensor DHT22, switch relay MQTT, dashboard smart home, dan automasi sederhana.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', [
            'esp32', 'mqtt', 'iot', 'homeassistant', 'smarthome', 'relay',
        ])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command->info('✓ Artikel ke-21 berhasil dipublish: ' . $article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan</h2>
<p>Di Seri 1 kamu sudah mengontrol <strong>relay lampu</strong> lewat MQTT (<a href="/artikel/kontrol-lampu-esp32-mqtt-relay">artikel #8</a>) dan mengirim data <strong>DHT22</strong> sebagai JSON (<a href="/artikel/gabungkan-dht22-relay-mqtt-esp32-satu-proyek">artikel #9</a>). Di Seri 2, <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">broker Mosquitto pribadi (#16)</a> menjadi fondasi infrastruktur sendiri.</p>

<p>Artikel ini membuka <strong>Jalur C</strong> (smart home): <strong>Home Assistant (HA)</strong> — platform open-source yang mengumpulkan sensor, switch, dan automasi dalam satu dashboard. ESP32 tetap publisher/subscriber MQTT; HA yang jadi &ldquo;otak&rdquo; smart home.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> Paham <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">MQTT dasar (#7)</a>, sudah bisa <a href="/artikel/kontrol-lampu-esp32-mqtt-relay">kontrol relay via MQTT (#8)</a>, dan punya (atau bisa akses) <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">broker Mosquitto pribadi + auth (#16)</a>. Sketch gabungan DHT22 + relay dari <a href="/artikel/gabungkan-dht22-relay-mqtt-esp32-satu-proyek">artikel #9</a> dipakai sebagai contoh ESP32.</p>
</blockquote>

<h2>Yang Kamu Butuhkan</h2>
<ul>
  <li>PC / laptop / Raspberry Pi untuk menjalankan <strong>Home Assistant</strong> (Docker atau instalasi native)</li>
  <li>Broker <strong>Mosquitto</strong> yang sudah jalan — dari <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">artikel #16</a> (IP LAN, misalnya <code>192.168.1.50</code>)</li>
  <li><strong>ESP32</strong> dengan sketch publish DHT22 + subscribe relay (topik Seri 1)</li>
  <li>ESP32 dan server HA di <strong>jaringan WiFi/LAN yang sama</strong> dengan broker</li>
</ul>

<h2>Apa itu Home Assistant?</h2>
<table>
  <thead>
    <tr><th>Aspek</th><th><a href="/artikel/membuat-web-server-esp32-monitoring-sensor-dht22">Web server ESP32 (#6)</a></th><th>Home Assistant</th></tr>
  </thead>
  <tbody>
    <tr><td>Fokus</td><td>Halaman monitoring satu board</td><td><strong>Hub</strong> banyak perangkat &amp; protokol</td></tr>
    <tr><td>UI</td><td>HTML custom di ESP32</td><td>Dashboard, mobile app, automasi visual</td></tr>
    <tr><td>ESP32</td><td>Server + sensor sekaligus</td><td>ESP32 tetap <strong>node MQTT</strong>; HA sebagai subscriber/publisher</td></tr>
    <tr><td>Automasi</td><td>Harus coding di firmware</td><td>Rule di HA (suhu &gt; 30°C → matikan lampu)</td></tr>
  </tbody>
</table>

<h2>Arsitektur: ESP32 → Mosquitto → Home Assistant</h2>
<pre><code>┌─────────────┐   publish/subscribe   ┌──────────────┐   MQTT    ┌─────────────────┐
│   ESP32     │ ◄──────────────────► │  Mosquitto   │ ◄───────► │ Home Assistant  │
│ DHT22+relay │   port 1883 + auth     │  (#16)       │           │  (Jalur C)      │
└─────────────┘                        └──────────────┘           └─────────────────┘
</code></pre>

<p><strong>Topic MQTT</strong> (konsisten Seri 1):</p>
<ul>
  <li>Sensor: <code>kodingindonesia/esp32/dht22/data</code> — JSON <code>{"suhu":28.5,"kelembaban":65.2}</code></li>
  <li>Relay: <code>kodingindonesia/esp32/lampu/kontrol</code> — payload <code>ON</code> / <code>OFF</code></li>
</ul>

<h2>Install Home Assistant (Docker)</h2>
<p>Cara paling portabel untuk belajar — jalankan di PC Linux, Mac, atau Raspberry Pi dengan Docker:</p>

<pre><code class="language-yaml"># docker-compose.yml
services:
  homeassistant:
    container_name: homeassistant
    image: ghcr.io/home-assistant/home-assistant:stable
    volumes:
      - ./ha-config:/config
    restart: unless-stopped
    network_mode: host
</code></pre>

<pre><code class="language-bash">mkdir ha-config
docker compose up -d
# Buka http://localhost:8123 — ikuti wizard setup akun</code></pre>

<blockquote>
  <p><strong>Alternatif:</strong> <strong>Home Assistant OS</strong> di Raspberry Pi 4 (image resmi) — cocok untuk server smart home 24/7. Logika MQTT di artikel ini sama; hanya cara install yang berbeda.</p>
</blockquote>

<h2>Hubungkan MQTT Broker di Home Assistant</h2>
<ol>
  <li>Home Assistant → <strong>Settings → Devices &amp; Services</strong></li>
  <li><strong>Add Integration</strong> → cari <strong>MQTT</strong></li>
  <li>Isi broker dari <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Mosquitto pribadi (#16)</a>:
    <ul>
      <li>Broker: <code>192.168.1.50</code> (ganti IP broker kamu)</li>
      <li>Port: <code>1883</code></li>
      <li>Username / Password: misalnya <code>kindo_esp32</code> / <code>KindoMQTT2026!</code></li>
    </ul>
  </li>
  <li>Klik <strong>Submit</strong> — status harus <em>Connected</em></li>
</ol>

<p>Jika HA dan Mosquitto di mesin yang sama (Raspberry Pi), broker bisa <code>127.0.0.1</code> atau <code>core-mosquitto</code> (add-on HA OS).</p>

<h2>Konfigurasi Sensor DHT22 (MQTT)</h2>
<p>Tambahkan ke <code>configuration.yaml</code> (via <strong>Settings → Add-ons → File editor</strong> atau SSH):</p>

<pre><code class="language-yaml">mqtt:
  sensor:
    - name: "ESP32 Suhu DHT22"
      unique_id: kindo_esp32_dht22_suhu
      state_topic: "kodingindonesia/esp32/dht22/data"
      value_template: "{{ value_json.suhu }}"
      unit_of_measurement: "°C"
      device_class: temperature
    - name: "ESP32 Kelembaban DHT22"
      unique_id: kindo_esp32_dht22_rh
      state_topic: "kodingindonesia/esp32/dht22/data"
      value_template: "{{ value_json.kelembaban }}"
      unit_of_measurement: "%"
      device_class: humidity
</code></pre>

<p>Restart Home Assistant → <strong>Developer Tools → States</strong> — cari <code>sensor.esp32_suhu_dht22</code>.</p>

<h2>Konfigurasi Switch Relay (MQTT)</h2>
<pre><code class="language-yaml">mqtt:
  switch:
    - name: "Lampu ESP32 Relay"
      unique_id: kindo_esp32_lampu_relay
      command_topic: "kodingindonesia/esp32/lampu/kontrol"
      state_topic: "kodingindonesia/esp32/lampu/kontrol"
      payload_on: "ON"
      payload_off: "OFF"
      optimistic: true
</code></pre>

<p>Switch ini memakai topic yang sama dengan <a href="/artikel/kontrol-lampu-esp32-mqtt-relay">artikel relay #8</a> — ESP32 subscribe dan mengubah GPIO relay saat HA mengirim <code>ON</code>/<code>OFF</code>.</p>

<blockquote>
  <p><strong>Catatan <code>optimistic: true</code>:</strong> Sketch <a href="/artikel/gabungkan-dht22-relay-mqtt-esp32-satu-proyek">#9</a> hanya <strong>subscribe</strong> topic kontrol — tidak mem-publish balik status relay. Tanpa itu, HA dengan <code>optimistic: false</code> + <code>state_topic</code> akan tampak &ldquo;macet&rdquo;. Untuk produksi, tambahkan <code>mqttClient.publish(topicKontrol, "ON")</code> di <code>callbackMQTT()</code> setelah relay berubah, lalu set <code>optimistic: false</code>.</p>
</blockquote>

<h2>Penjelasan Entity di Home Assistant</h2>
<ul>
  <li><strong><code>sensor.esp32_suhu_dht22</code></strong> — dibuat dari <code>name</code> di YAML (spasi → underscore, huruf kecil)</li>
  <li><strong><code>switch.lampu_esp32_relay</code></strong> — toggle di dashboard mengirim <code>ON</code>/<code>OFF</code> ke broker</li>
  <li><strong>Developer Tools → MQTT Listen</strong> — debug payload mentah tanpa mosquitto_sub di terminal</li>
  <li><strong>History graph</strong> — klik sensor → Add to dashboard → tren suhu 24 jam (butuh Recorder aktif — default on)</li>
</ul>

<h2>Dashboard &amp; Automasi Sederhana</h2>
<ol>
  <li><strong>Dashboard:</strong> Overview → Edit → Add card → <strong>Entities</strong> → pilih sensor suhu, kelembaban, dan switch lampu</li>
  <li><strong>Automasi contoh (YAML):</strong> Settings → Automations → menu ⋮ → <strong>Edit in YAML</strong>:
<pre><code class="language-yaml">alias: Matikan lampu jika suhu tinggi
trigger:
  - platform: numeric_state
    entity_id: sensor.esp32_suhu_dht22
    above: 30
    for:
      minutes: 5
action:
  - service: switch.turn_off
    target:
      entity_id: switch.lampu_esp32_relay</code></pre>
  </li>
  <li><strong>Notifikasi (opsional):</strong> Kirim alert ke HP saat suhu tinggi — butuh integrasi mobile app HA</li>
</ol>

<blockquote>
  <p><strong>Pro tip:</strong> Gunakan <strong>unique_id</strong> di setiap entitas MQTT agar nama entity stabil setelah restart — hindari duplikat entitas di HA.</p>
</blockquote>

<h2>Uji Coba (Step-by-Step)</h2>
<ol>
  <li>Pastikan Mosquitto (#16) jalan — <code>sudo systemctl status mosquitto</code></li>
  <li>Upload sketch <a href="/artikel/gabungkan-dht22-relay-mqtt-esp32-satu-proyek">DHT22 + relay (#9)</a> — arahkan broker ke IP Mosquitto pribadi (bukan <code>test.mosquitto.org</code>)</li>
  <li>Verifikasi publish dari laptop:
<pre><code class="language-bash">mosquitto_sub -h 192.168.1.50 -p 1883 \
  -u kindo_esp32 -P 'KindoMQTT2026!' \
  -t "kodingindonesia/esp32/dht22/data" -v</code></pre>
  </li>
  <li>Setup MQTT integration + <code>configuration.yaml</code> di HA → restart</li>
  <li>Buka dashboard — suhu/kelembaban harus update setiap ~10 detik</li>
  <li>Toggle switch lampu di HA → relay ESP32 klik ON/OFF</li>
  <li>Opsional — uji relay tanpa HA dulu:
<pre><code class="language-bash">mosquitto_pub -h 192.168.1.50 -p 1883 \
  -u kindo_esp32 -P 'KindoMQTT2026!' \
  -t "kodingindonesia/esp32/lampu/kontrol" -m "ON"</code></pre>
  </li>
</ol>

<h2>Gabung dengan Stack Seri 2</h2>
<ul>
  <li>Sensor <a href="/artikel/i2c-esp32-sensor-bme280-suhu-tekanan-mqtt">BME280 (#13)</a> — tambah entitas MQTT dari topic <code>kodingindonesia/esp32/bme280/data</code></li>
  <li>Node <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">WiFiManager + NVS (#12)</a> — ESP32 lapangan tanpa hardcode broker</li>
  <li>Maintenance <a href="/artikel/ota-update-firmware-esp32-via-wifi">OTA (#15)</a> — patch firmware tanpa buka casing setelah integrasi HA</li>
</ul>

<h2>Tips &amp; Troubleshooting</h2>
<ul>
  <li><strong>HA tidak connect ke broker:</strong> Cek firewall port 1883, IP broker, username/password — sama seperti troubleshooting <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">#16</a></li>
  <li><strong>Sensor unavailable:</strong> Pastikan ESP32 publish JSON valid; cek <code>value_template</code> cocok dengan key <code>suhu</code> / <code>kelembaban</code></li>
  <li><strong>Switch tidak merespons:</strong> ESP32 harus <strong>subscribe</strong> topic kontrol — lihat <a href="/artikel/kontrol-lampu-esp32-mqtt-relay">#8</a>; cek <code>mqttClient.loop()</code> di <code>loop()</code></li>
  <li><strong>Entity duplikat setelah edit YAML:</strong> Hapus entitas lama di Settings → Devices → MQTT → hapus device orphan</li>
  <li><strong>HA di Docker Windows:</strong> <code>network_mode: host</code> tidak tersedia — map port <code>8123:8123</code> dan gunakan IP LAN PC sebagai broker address dari ESP32</li>
  <li><strong>Payload bukan JSON:</strong> Jika masih pakai topic lama <code>kodingindonesia/esp32/dht22</code> (teks), upgrade ke sketch <a href="/artikel/gabungkan-dht22-relay-mqtt-esp32-satu-proyek">#9</a></li>
  <li><strong>WiFi 2.4 GHz:</strong> ESP32 tidak support jaringan <strong>5 GHz saja</strong></li>
</ul>

<h2>Keamanan &amp; Produksi</h2>
<ul>
  <li>Jangan expose Mosquitto port 1883 ke internet tanpa <strong>TLS (#17)</strong> — password MQTT terkirim plain di LAN saja masih risiko jika WiFi tamu terbuka</li>
  <li>Gunakan user MQTT terpisah untuk HA (read/write) vs ESP32 (publish terbatas) — ACL Mosquitto seperti di <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">#16</a></li>
  <li>Jangan commit password HA atau MQTT ke repo publik</li>
  <li>Backup folder <code>/config</code> Home Assistant secara berkala</li>
</ul>

<h2>Langkah Selanjutnya (Seri 2)</h2>
<ul>
  <li><strong>Artikel #22:</strong> <strong>ESPHome</strong> — flash ESP32 tanpa coding Arduino (YAML)</li>
  <li><strong>Artikel #23:</strong> <strong>Node-RED</strong> — dashboard &amp; otomasi visual</li>
  <li><strong>Artikel #24:</strong> Sensor <strong>PIR + lampu MQTT</strong> — automasi gerak dengan debounce</li>
  <li><strong>Artikel #17:</strong> MQTT <strong>TLS</strong> — amankan broker di internet</li>
  <li>Capstone <strong>greenhouse (#39)</strong> — sensor + HA + pompa relay</li>
</ul>

<p>Dengan Home Assistant, proyek ESP32-mu naik kelas dari sketch tunggal menjadi ekosistem smart home terpusat. Lanjutkan di <a href="/artikel">halaman artikel</a> Koding Indonesia.</p>
HTML;
    }
}
