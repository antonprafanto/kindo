<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article20Seeder extends Seeder
{
    public function run(): void
    {
        $admin  = User::first();
        $netCat = Category::where('slug', 'networking')->first();

        if (! $admin || ! $netCat) {
            throw new \RuntimeException('User atau kategori tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $netCat->id,
                'title'           => 'REST API vs MQTT: Kapan Pakai Yang Mana di Proyek IoT ESP32',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'REST API vs MQTT — Panduan Pilih Protokol IoT ESP32',
                'seo_description' => 'Bandingkan HTTP REST (pull) dan MQTT (push) untuk ESP32: kapan pakai /api/data web server (#6) vs publish broker (#7), hybrid, TLS, dan integrasi MySQL/Grafana.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        Tag::updateOrCreate(['slug' => 'networking'], ['name' => 'networking']);
        Tag::updateOrCreate(['slug' => 'http'], ['name' => 'http']);

        $tagIds = Tag::whereIn('slug', [
            'esp32', 'mqtt', 'iot', 'networking', 'api', 'http', 'wifi',
        ])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command->info('✓ Artikel ke-20 berhasil dipublish: ' . $article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — Dua Cara ESP32 Berbicara ke Dunia</h2>
<p>Di Seri 1, kamu sudah membangun <strong>web server ESP32</strong> dengan endpoint <code>/api/data</code> (<a href="/artikel/membuat-web-server-esp32-monitoring-sensor-dht22">artikel #6</a>) — klien <strong>meminta</strong> data lewat HTTP. Lalu di <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">artikel #7</a>, ESP32 <strong>mendorong</strong> data sensor ke broker MQTT begitu ada pembacaan baru.</p>

<p>Keduanya valid. Masalahnya: tutorial IoT sering memaksa satu protokol untuk semua kasus. Artikel <strong>Jalur B</strong> ini menjawab pertanyaan praktis: <strong>kapan REST API (HTTP)</strong>, <strong>kapan MQTT</strong>, dan kapan <strong>kombinasi keduanya</strong> — sebelum kamu lanjut ke pipeline data (<a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">#18</a>, <a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">#19</a>) atau smart home (<a href="/artikel/home-assistant-integrasi-esp32-mqtt">#21</a>–<a href="/artikel/node-red-dashboard-otomasi-iot-mqtt-esp32">#23</a>).</p>

<blockquote>
  <p><strong>Prasyarat:</strong> Sudah baca <a href="/artikel/membuat-web-server-esp32-monitoring-sensor-dht22">web server ESP32 (#6)</a> dan <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">MQTT dasar (#7)</a>. Disarankan paham broker pribadi (<a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">#16</a>) dan dashboard capstone (<a href="/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22">#10</a>).</p>
</blockquote>

<h2>Pull vs Push — Intuisi Dasar</h2>
<table>
  <thead>
    <tr><th>Paradigma</th><th>REST / HTTP</th><th>MQTT</th></tr>
  </thead>
  <tbody>
    <tr><td><strong>Arah</strong></td><td>Klien <em>pull</em> — minta saat butuh</td><td>Device <em>push</em> — kirim saat event terjadi</td></tr>
    <tr><td><strong>Koneksi</strong></td><td>Request–response, lalu idle</td><td>Subscribe + publish berkelanjutan</td></tr>
    <tr><td><strong>Contoh Seri 2</strong></td><td><code>GET /api/data</code> (<a href="/artikel/membuat-web-server-esp32-monitoring-sensor-dht22">#6</a>)</td><td>Publish ke <code>kodingindonesia/esp32/dht22/data</code> (<a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">#7</a>)</td></tr>
    <tr><td><strong>Cocok untuk</strong></td><td>Dashboard web lokal, debug cepat, integrasi app yang jarang baca</td><td>Sensor real-time, banyak subscriber, otomasi (<a href="/artikel/node-red-dashboard-otomasi-iot-mqtt-esp32">#23</a>), histori (<a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">#18</a>/<a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">#19</a>)</td></tr>
  </tbody>
</table>

<h2>REST API di ESP32 — Kapan Pakai?</h2>
<p>Pilih <strong>HTTP REST</strong> jika:</p>
<ul>
  <li>Hanya <strong>satu atau sedikit klien</strong> yang sesekali cek suhu (browser di LAN, Postman, script cron)</li>
  <li>Kamu butuh respons <strong>langsung dalam satu request</strong> tanpa infrastruktur broker</li>
  <li>Prototipe cepat — buka IP ESP32, lihat JSON, selesai (pola <a href="/artikel/membuat-web-server-esp32-monitoring-sensor-dht22">#6</a>)</li>
  <li>Perangkat tidak perlu online 24/7; ESP32 boleh tidur (<a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">deep sleep #11</a>) lalu bangun, serve HTTP, tidur lagi</li>
</ul>

<p><strong>Kelemahan REST untuk IoT skala:</strong> setiap klien harus polling — boros bandwidth dan baterai jika interval pendek. Sepuluh dashboard yang polling tiap 1 detik = sepuluh kali beban WiFi yang sama.</p>

<pre><code class="language-cpp">// Pola ringkas dari artikel #6 — handler REST
void handleAPI() {
  float suhu = dht.readTemperature();
  float rh = dht.readHumidity();
  String json = "{\"suhu\":" + String(suhu) + ",\"kelembaban\":" + String(rh) + "}";
  server.send(200, "application/json", json);
}</code></pre>

<p>Akses dari laptop: <code>GET http://192.168.1.100/api/data</code> — tidak perlu broker Mosquitto.</p>

<h2>MQTT — Kapan Pakai?</h2>
<p>Pilih <strong>MQTT</strong> jika:</p>
<ul>
  <li><strong>Banyak subscriber</strong> butuh data yang sama: <a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">Grafana (#19)</a>, <a href="/artikel/node-red-dashboard-otomasi-iot-mqtt-esp32">Node-RED (#23)</a>, <a href="/artikel/home-assistant-integrasi-esp32-mqtt">Home Assistant (#21)</a>, <a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">subscriber Python (#18)</a></li>
  <li>Data harus <strong>push real-time</strong> — relay, alert, grafik live tanpa refresh manual</li>
  <li>ESP32 di lapangan dengan koneksi tidak stabil — <a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">QoS &amp; LWT (#17)</a> menjaga reliabilitas</li>
  <li>Arsitektur <strong>decoupled</strong>: sensor tidak perlu tahu siapa yang consume data</li>
</ul>

<pre><code class="language-cpp">// Pola ringkas dari artikel #7 — publish MQTT
const char* topic = "kodingindonesia/esp32/dht22/data";
// Payload dengan timestamp (#34):
// {"suhu":28.5,"kelembaban":65.2,"timestamp":"2026-07-02T14:30:00","unix":1782977400}
mqttClient.publish(topic, json.c_str());</code></pre>

<p>Broker contoh: <code>192.168.1.50:1883</code>, user <code>kindo_esp32</code> / placeholder <code>GANTI_PASSWORD_MQTT</code> — detail di <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">#16</a>.</p>

<h2>Perbandingan Lengkap REST vs MQTT</h2>
<table>
  <thead>
    <tr><th>Aspek</th><th>REST (HTTP)</th><th>MQTT</th></tr>
  </thead>
  <tbody>
    <tr><td><strong>Latency tipikal</strong></td><td>Tergantung interval polling klien</td><td>Sub-detik setelah publish (LAN)</td></tr>
    <tr><td><strong>Bandwidth</strong></td><td>Tinggi jika polling sering</td><td>Rendah — hanya kirim saat berubah</td></tr>
    <tr><td><strong>Baterai / deep sleep</strong></td><td>Bagus — ESP32 bangun on-demand</td><td>Perlu maintain koneksi atau reconnect sering</td></tr>
    <tr><td><strong>Skala subscriber</strong></td><td>Buruk — N klien = N× polling</td><td>Bagus — 1 publish, N subscriber</td></tr>
    <tr><td><strong>Firewall/NAT</strong></td><td>ESP32 sebagai server sulit di internet</td><td>ESP32 outbound ke broker — lebih mudah</td></tr>
    <tr><td><strong>Debugging</strong></td><td>Mudah — curl/browser</td><td>Butuh mosquitto_sub atau MQTT Explorer</td></tr>
    <tr><td><strong>Histori &amp; DB</strong></td><td>Server harus poll ESP32 (aneh)</td><td>Natural — <a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">subscriber #18</a> tulis MySQL/InfluxDB</td></tr>
  </tbody>
</table>

<h2>Arsitektur Hybrid — Best of Both Worlds</h2>
<p>Proyek produksi sering memakai <strong>keduanya</strong>:</p>
<figure role="img" aria-label="Diagram hybrid REST dan MQTT: ESP32 publish MQTT ke Mosquitto lalu fan-out ke Grafana Python HA, plus HTTP lokal ke browser; atau ESP32 ke broker ke Backend REST untuk app mobile" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 920 420" style="display:block;max-width:920px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="rmArrow" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/></marker>
    <marker id="rmArrowOrange" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#FF7A2F"/></marker>
    <marker id="rmArrowGreen" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#2E7D32"/></marker>
  </defs>
  <rect x="0" y="0" width="920" height="420" fill="#F5F5F0" rx="6"/>
  <text x="24" y="28" fill="#1a1a1a" font-size="13" font-weight="700">Pola A — ESP32 hybrid lokal</text>
  <rect x="24" y="48" width="160" height="88" rx="6" fill="#FFF3E8" stroke="#000" stroke-width="2.5"/>
  <text x="104" y="78" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">ESP32</text>
  <text x="104" y="100" text-anchor="middle" fill="#4A5568" font-size="11">MQTT + HTTP lokal</text>
  <text x="104" y="120" text-anchor="middle" fill="#718096" font-size="11">sensor DHT22</text>
  <line x1="184" y1="70" x2="250" y2="70" stroke="#2979FF" stroke-width="2.5" marker-end="url(#rmArrow)"/>
  <text x="217" y="62" text-anchor="middle" fill="#2979FF" font-size="10" font-weight="700">MQTT →</text>
  <rect x="256" y="40" width="200" height="60" rx="6" fill="#2979FF" stroke="#000" stroke-width="2.5"/>
  <text x="356" y="65" text-anchor="middle" fill="#fff" font-size="13" font-weight="700">Mosquitto</text>
  <text x="356" y="85" text-anchor="middle" fill="#e3f2fd" font-size="11">broker LAN</text>
  <line x1="456" y1="70" x2="520" y2="70" stroke="#2E7D32" stroke-width="2.5" marker-end="url(#rmArrowGreen)"/>
  <text x="488" y="62" text-anchor="middle" fill="#2E7D32" font-size="10" font-weight="700">fan-out →</text>
  <rect x="526" y="40" width="370" height="60" rx="6" fill="#F5F5F0" stroke="#000" stroke-width="2.5"/>
  <text x="711" y="65" text-anchor="middle" fill="#1a1a1a" font-size="13" font-weight="700">Grafana · Python · HA</text>
  <text x="711" y="85" text-anchor="middle" fill="#4A5568" font-size="11">multi-subscriber dari topic MQTT</text>
  <line x1="104" y1="136" x2="104" y2="170" stroke="#FF7A2F" stroke-width="2.5" marker-end="url(#rmArrowOrange)"/>
  <text x="128" y="158" fill="#FF7A2F" font-size="10" font-weight="700">HTTP ↓</text>
  <rect x="24" y="178" width="240" height="48" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="144" y="198" text-anchor="middle" fill="#1a1a1a" font-size="13" font-weight="700">Browser lokal / debug</text>
  <text x="144" y="216" text-anchor="middle" fill="#4A5568" font-size="11">GET /api/data di LAN</text>
  <text x="24" y="258" fill="#1a1a1a" font-size="13" font-weight="700">Pola B — MQTT internal + REST di server</text>
  <rect x="24" y="278" width="150" height="70" rx="6" fill="#FFF3E8" stroke="#000" stroke-width="2.5"/>
  <text x="99" y="308" text-anchor="middle" fill="#1a1a1a" font-size="13" font-weight="700">ESP32</text>
  <text x="99" y="328" text-anchor="middle" fill="#4A5568" font-size="11">MQTT only</text>
  <line x1="174" y1="313" x2="230" y2="313" stroke="#2979FF" stroke-width="2.5" marker-end="url(#rmArrow)"/>
  <text x="202" y="305" text-anchor="middle" fill="#2979FF" font-size="10" font-weight="700">MQTT →</text>
  <rect x="236" y="278" width="160" height="70" rx="6" fill="#2979FF" stroke="#000" stroke-width="2.5"/>
  <text x="316" y="308" text-anchor="middle" fill="#fff" font-size="13" font-weight="700">Broker</text>
  <text x="316" y="328" text-anchor="middle" fill="#e3f2fd" font-size="11">internal</text>
  <line x1="396" y1="313" x2="452" y2="313" stroke="#2979FF" stroke-width="2.5" marker-end="url(#rmArrow)"/>
  <text x="424" y="305" text-anchor="middle" fill="#2979FF" font-size="10" font-weight="700">sub →</text>
  <rect x="458" y="278" width="200" height="70" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="558" y="308" text-anchor="middle" fill="#1a1a1a" font-size="13" font-weight="700">Backend REST API</text>
  <text x="558" y="328" text-anchor="middle" fill="#4A5568" font-size="11">bukan langsung ke ESP32</text>
  <line x1="658" y1="313" x2="710" y2="313" stroke="#FF7A2F" stroke-width="2.5" marker-end="url(#rmArrowOrange)"/>
  <text x="684" y="305" text-anchor="middle" fill="#FF7A2F" font-size="10" font-weight="700">GET →</text>
  <rect x="716" y="278" width="180" height="70" rx="6" fill="#F5F5F0" stroke="#000" stroke-width="2.5"/>
  <text x="806" y="308" text-anchor="middle" fill="#1a1a1a" font-size="13" font-weight="700">App mobile</text>
  <text x="806" y="328" text-anchor="middle" fill="#4A5568" font-size="11">/api/v1/sensor</text>
  <text x="460" y="392" text-anchor="middle" fill="#4A5568" font-size="11">Hybrid: MQTT untuk bus data · REST lokal untuk debug · REST server untuk partner eksternal</text>
</svg>
<figcaption style="margin-top:.75rem;font-size:.875rem;color:#4A5568;text-align:center">Diagram hybrid REST + MQTT — ESP32 boleh serve HTTP lokal untuk debug, sementara produksi multi-consumer lewat Mosquitto lalu <a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">Grafana (#19)</a> / <a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">Python (#18)</a>.</figcaption>
</figure>

<p>Sensor tetap push via MQTT; aplikasi mobile atau partner eksternal baca lewat <strong>REST API di server</strong> (bukan langsung ke ESP32). Ini pola yang dipakai <a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">Grafana (#19)</a> dan <a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">MySQL (#18)</a>: ESP32 tidak expose HTTP ke internet.</p>

<blockquote>
  <p><strong>Pro tip:</strong> Jangan expose <code>/api/data</code> ESP32 langsung ke internet. Letakkan reverse proxy + auth di VPS, atau arahkan semua konsumsi eksternal lewat backend yang sudah subscribe MQTT.</p>
</blockquote>

<h2>Contoh Skenario Keputusan</h2>
<ol>
  <li><strong>Greenhouse kecil, 1 orang pantau HP di WiFi rumah</strong> → <a href="/artikel/membuat-web-server-esp32-monitoring-sensor-dht22">REST (#6)</a> cukup; refresh manual atau auto-refresh JS.</li>
  <li><strong>5 sensor + Grafana + alert Telegram</strong> → <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">MQTT (#7)</a> + stack <a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">#18</a>/<a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">#19</a>.</li>
  <li><strong>Smart home + automasi PIR</strong> → MQTT wajib (<a href="/artikel/sensor-gerak-pir-esp32-lampu-mqtt-debounce">#24</a>, <a href="/artikel/home-assistant-integrasi-esp32-mqtt">#21</a>).</li>
  <li><strong>Node baterai solar, kirim tiap 15 menit</strong> → MQTT publish lalu <a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">deep sleep</a>; hindari HTTP server always-on.</li>
  <li><strong>Integrasi API partner (mobile app)</strong> → MQTT ke backend internal; partner pakai REST API server kamu.</li>
</ol>

<h2>Payload &amp; Format Data — Samakan Keduanya</h2>
<p>Agar migrasi REST → MQTT mudah, pakai <strong>JSON yang sama</strong> di kedua protokol:</p>
<pre><code>{"suhu":28.5,"kelembaban":65.2,"timestamp":"2026-07-02T14:30:00","unix":1782977400}</code></pre>

<p>Field <code>unix</code> dari <a href="/artikel/ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt">NTP (#34)</a> membuat data REST dan MQTT bisa masuk ke tabel <code>sensor_readings</code> (<a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">#18</a>) atau InfluxDB (<a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">#19</a>) tanpa transformasi berbeda.</p>

<h2>Keamanan: HTTP vs MQTT di Produksi</h2>
<ul>
  <li><strong>LAN saja:</strong> HTTP port 80 dan MQTT 1883 plain — OK untuk lab (bukan password hardcode di firmware)</li>
  <li><strong>Internet:</strong> HTTPS (reverse proxy) untuk REST; <a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">MQTT over TLS (#17)</a> port 8883 untuk ESP32 → broker</li>
  <li>Jangan pakai <code>test.mosquitto.org</code> untuk data produksi — gunakan <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">broker pribadi (#16)</a></li>
  <li>REST: validasi input pada POST/PUT; MQTT: ACL user terpisah publisher vs subscriber (<a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">#18</a>)</li>
</ul>

<h2>Integrasi dengan Pipeline Data Seri 2</h2>
<p>Setelah memilih MQTT sebagai tulang punggung sensor:</p>
<ul>
  <li><a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">Subscriber Python → MySQL (#18)</a> — arsip SQL</li>
  <li><a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">InfluxDB + Grafana (#19)</a> — grafik histori</li>
  <li><a href="/artikel/node-red-dashboard-otomasi-iot-mqtt-esp32">Node-RED (#23)</a> — otomasi tanpa ubah firmware</li>
</ul>

<p>REST tetap berguna untuk <strong>panel debug lokal</strong> di ESP32 atau endpoint OTA status — tidak menggantikan MQTT untuk multi-consumer.</p>

<h2>Checklist: Pilih Protokol dalam 2 Menit</h2>
<ol>
  <li>Lebih dari 2 konsumen data? → <strong>MQTT</strong></li>
  <li>Butuh grafik/DB histori? → <strong>MQTT</strong> (+ <a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">#18</a>/<a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">#19</a>)</li>
  <li>Hanya buka browser sesekali di LAN? → <strong><a href="/artikel/membuat-web-server-esp32-monitoring-sensor-dht22">REST (#6)</a></strong></li>
  <li>Node baterai deep sleep? → <strong>MQTT publish</strong> lalu tidur (bukan HTTP server 24 jam)</li>
  <li>Partner butuh API standar? → <strong>Backend REST</strong> yang consume MQTT internal</li>
  <li>Ragu? → Mulai <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">MQTT (#7)</a> + <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">broker (#16)</a> — skala lebih mudah nanti</li>
</ol>

<h2>HTTP POST vs MQTT Publish — Kontrol &amp; Command</h2>
<p><a href="/artikel/membuat-web-server-esp32-monitoring-sensor-dht22">Web server ESP32 (#6)</a> fokus pada <strong>GET</strong> (baca sensor). Di lapangan, kamu juga mungkin butuh <strong>kontrol aktuator</strong> — nyalakan relay, ubah setpoint, trigger OTA.</p>
<ul>
  <li><strong>REST POST/PUT:</strong> Klien kirim perintah ke endpoint <code>/api/relay</code> — cocok untuk aplikasi mobile yang sesekali toggle lampu</li>
  <li><strong>MQTT publish ke topic kontrol:</strong> <code>kodingindonesia/esp32/dht22/cmd</code> — cocok untuk <a href="/artikel/node-red-dashboard-otomasi-iot-mqtt-esp32">Node-RED (#23)</a> dan <a href="/artikel/home-assistant-integrasi-esp32-mqtt">Home Assistant (#21)</a> yang sudah subscribe</li>
</ul>
<p>MQTT unggul untuk <em>event-driven control</em>: banyak rule otomasi bisa subscribe topic yang sama. REST unggul jika partner eksternal hanya punya SDK HTTP.</p>
<p>Pola aman: pisahkan topic <strong>data</strong> (sensor) dan <strong>cmd</strong> (aktuator), dengan ACL berbeda di <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Mosquitto (#16)</a> — publisher sensor tidak boleh publish ke topic relay.</p>

<h2>Latency &amp; Polling — Angka Kasar</h2>
<p>Contoh: 5 dashboard polling REST tiap 2 detik = <strong>150 request/menit</strong> ke ESP32. Satu MQTT publish tiap 30 detik = <strong>2 message/menit</strong> dari device, fan-out gratis ke 5 subscriber.</p>
<p>Untuk sensor suhu greenhouse yang berubah lambat, polling 1 detik via REST adalah overkill. MQTT interval 30–60 detik lebih masuk akal — selaras dengan rekomendasi DHT22 minimal 2 detik antar pembacaan di <a href="/artikel/membuat-web-server-esp32-monitoring-sensor-dht22">#6</a>.</p>

<h2>Diagram Alur Data — REST vs MQTT</h2>
<p>Visualisasi sederhana membantu tim memilih protokol sebelum menulis firmware:</p>
<figure role="img" aria-label="Perbandingan alur REST pull versus MQTT push: browser GET ke ESP32 versus ESP32 publish ke Mosquitto lalu fan-out ke banyak subscriber" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 900 320" style="display:block;max-width:900px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="cmpArrow" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/></marker>
    <marker id="cmpArrowOrange" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#FF7A2F"/></marker>
    <marker id="cmpArrowGreen" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#2E7D32"/></marker>
  </defs>
  <rect x="0" y="0" width="900" height="320" fill="#F5F5F0" rx="6"/>
  <text x="24" y="28" fill="#1a1a1a" font-size="13" font-weight="700">REST (pull) — klien yang memulai</text>
  <rect x="24" y="44" width="150" height="70" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="99" y="74" text-anchor="middle" fill="#1a1a1a" font-size="13" font-weight="700">Browser</text>
  <text x="99" y="94" text-anchor="middle" fill="#4A5568" font-size="11">polling klien</text>
  <line x1="174" y1="79" x2="230" y2="79" stroke="#FF7A2F" stroke-width="2.5" marker-end="url(#cmpArrowOrange)"/>
  <text x="202" y="70" text-anchor="middle" fill="#FF7A2F" font-size="10" font-weight="700">GET →</text>
  <rect x="236" y="44" width="150" height="70" rx="6" fill="#FFF3E8" stroke="#000" stroke-width="2.5"/>
  <text x="311" y="74" text-anchor="middle" fill="#1a1a1a" font-size="13" font-weight="700">ESP32</text>
  <text x="311" y="94" text-anchor="middle" fill="#4A5568" font-size="11">baca DHT22</text>
  <line x1="386" y1="79" x2="442" y2="79" stroke="#FF7A2F" stroke-width="2.5" marker-end="url(#cmpArrowOrange)"/>
  <text x="414" y="70" text-anchor="middle" fill="#FF7A2F" font-size="10" font-weight="700">JSON →</text>
  <rect x="448" y="44" width="200" height="70" rx="6" fill="#F5F5F0" stroke="#000" stroke-width="2.5"/>
  <text x="548" y="74" text-anchor="middle" fill="#1a1a1a" font-size="13" font-weight="700">Response</text>
  <text x="548" y="94" text-anchor="middle" fill="#4A5568" font-size="11">beban × jumlah klien</text>
  <text x="24" y="156" fill="#1a1a1a" font-size="13" font-weight="700">MQTT (push) — device yang memulai</text>
  <rect x="24" y="172" width="150" height="70" rx="6" fill="#FFF3E8" stroke="#000" stroke-width="2.5"/>
  <text x="99" y="202" text-anchor="middle" fill="#1a1a1a" font-size="13" font-weight="700">ESP32</text>
  <text x="99" y="222" text-anchor="middle" fill="#4A5568" font-size="11">publish event</text>
  <line x1="174" y1="207" x2="230" y2="207" stroke="#2979FF" stroke-width="2.5" marker-end="url(#cmpArrow)"/>
  <text x="202" y="198" text-anchor="middle" fill="#2979FF" font-size="10" font-weight="700">pub →</text>
  <rect x="236" y="172" width="170" height="70" rx="6" fill="#2979FF" stroke="#000" stroke-width="2.5"/>
  <text x="321" y="202" text-anchor="middle" fill="#fff" font-size="13" font-weight="700">Mosquitto</text>
  <text x="321" y="222" text-anchor="middle" fill="#e3f2fd" font-size="11">fan-out broker</text>
  <line x1="406" y1="207" x2="462" y2="207" stroke="#2E7D32" stroke-width="2.5" marker-end="url(#cmpArrowGreen)"/>
  <text x="434" y="198" text-anchor="middle" fill="#2E7D32" font-size="10" font-weight="700">sub →</text>
  <rect x="468" y="172" width="408" height="70" rx="6" fill="#F5F5F0" stroke="#000" stroke-width="2.5"/>
  <text x="672" y="202" text-anchor="middle" fill="#1a1a1a" font-size="13" font-weight="700">Grafana · Python · HA · Node-RED</text>
  <text x="672" y="222" text-anchor="middle" fill="#4A5568" font-size="11">1 publish = banyak consumer</text>
  <text x="450" y="288" text-anchor="middle" fill="#4A5568" font-size="11">REST: panah dimulai dari Browser · MQTT: panah dimulai dari ESP32 ke broker</text>
</svg>
<figcaption style="margin-top:.75rem;font-size:.875rem;color:#4A5568;text-align:center">Diagram alur REST vs MQTT — panah mengikuti siapa yang memulai: klien pull HTTP, atau device push MQTT lalu fan-out ke subscriber.</figcaption>
</figure>
<p>Pada skenario REST, ESP32 harus <strong>selalu siap</strong> menerima HTTP jika kamu ingin data “live”. Pada MQTT, ESP32 cukup publish sesuai interval — subscriber yang bertanggung jawab menampilkan atau menyimpan.</p>
<p>Hybrid umum di produksi: ESP32 hanya MQTT outbound; backend (Laravel, FastAPI, Node) expose REST untuk partner eksternal yang tidak bisa subscribe MQTT langsung.</p>

<h2>Uji Coba (Keduanya di Lab)</h2>
<pre><code class="language-bash"># REST — artikel #6
curl http://192.168.1.100/api/data

# MQTT — artikel #7
mosquitto_sub -h 192.168.1.50 -t "kodingindonesia/esp32/dht22/data" -v</code></pre>
<ol>
  <li>Flash sketch <a href="/artikel/membuat-web-server-esp32-monitoring-sensor-dht22">#6</a> — <code>curl http://192.168.1.100/api/data</code> → JSON suhu</li>
  <li>Flash sketch <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">#7</a> — <code>mosquitto_sub -h 192.168.1.50 -t "kodingindonesia/esp32/dht22/data" -v</code></li>
  <li>Bandingkan: REST hanya kirim saat kamu curl; MQTT kirim tiap interval publish ESP32</li>
  <li>Matikan broker — MQTT gagal connect; REST tetap jalan (independen)</li>
  <li>Nyalakan <a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">subscriber #18</a> — hanya MQTT yang mengisi MySQL otomatis</li>
</ol>

<h2>FAQ Singkat</h2>
<dl>
  <dt><strong>Bisakah ESP32 REST dan MQTT bersamaan?</strong></dt>
  <dd>Ya — RAM cukup untuk web server ringan + MQTT client. Prioritaskan satu path <em>utama</em> ke database agar tidak dobel insert.</dd>
  <dt><strong>MQTT butuh internet?</strong></dt>
  <dd>Tidak — broker Mosquitto di LAN (<a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">#16</a>) sudah cukup untuk Grafana lokal (<a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">#19</a>).</dd>
  <dt><strong>REST lebih aman dari MQTT?</strong></dt>
  <dd>Tidak otomatis. Keduanya butuh <a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">TLS (#17)</a> dan autentikasi jika di-expose ke internet.</dd>
  <dt><strong>Kapan ganti dari REST ke MQTT?</strong></dt>
  <dd>Saat muncul kebutuhan kedua: histori otomatis (<a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">#18</a>) atau dashboard kedua (<a href="/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22">#10</a> capstone sudah hybrid).</dd>
</dl>

<h2>Tips &amp; Troubleshooting</h2>
<ul>
  <li><strong>REST lambat terasa:</strong> Normal — kamu yang harus refresh/polling; bukan bug ESP32</li>
  <li><strong>MQTT data dobel di DB:</strong> Jangan jalankan REST scraper + MQTT subscriber ke DB yang sama tanpa dedup</li>
  <li><strong>ESP32 overload:</strong> Jangan jalankan web server berat + MQTT TLS + OTA bersamaan di RAM terbatas — prioritaskan satu path data utama</li>
  <li><strong>CORS / browser block:</strong> REST dari web app hosted beda origin butuh header CORS di handler ESP32 (topik lanjutan)</li>
  <li><strong>Port 80 bentrok:</strong> Hanya satu layanan di port 80 — matikan web server jika fokus MQTT saja</li>
  <li><strong>WiFi 2.4 GHz:</strong> ESP32 tidak support jaringan 5 GHz saja</li>
</ul>

<h2>Estimasi Biaya Infrastruktur</h2>
<p>REST-only di ESP32 hampir <strong>tanpa biaya tambahan</strong> — cukup board + WiFi rumah. MQTT production menambah komponen opsional:</p>
<table>
  <thead>
    <tr><th>Komponen</th><th>REST saja</th><th>MQTT stack</th></tr>
  </thead>
  <tbody>
    <tr><td>ESP32 + sensor</td><td>~Rp 80–150rb</td><td>Sama</td></tr>
    <tr><td><a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Broker Mosquitto (#16)</a></td><td>Tidak perlu</td><td>Raspberry Pi / VPS ~Rp 50–150rb/bulan</td></tr>
    <tr><td><a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">MySQL (#18)</a> / <a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">InfluxDB (#19)</a></td><td>Manual export</td><td>Gratis self-host; cloud opsional</td></tr>
    <tr><td>Bandwidth</td><td>Tinggi jika polling sering</td><td>Rendah — event-driven</td></tr>
  </tbody>
</table>
<p>Untuk proyek hobi satu sensor, mulai <a href="/artikel/membuat-web-server-esp32-monitoring-sensor-dht22">REST (#6)</a>. Saat butuh histori dan multi-dashboard, investasi <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">broker (#16)</a> lebih masuk akal daripada membangun banyak scraper HTTP.</p>

<h2>Ringkasan Jalur B — REST vs MQTT dalam Stack</h2>
<p>Di Seri 2, urutan belajar umumnya:</p>
<ol>
  <li><strong><a href="/artikel/membuat-web-server-esp32-monitoring-sensor-dht22">#6</a></strong> — REST lokal untuk prototipe cepat</li>
  <li><strong><a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">#7</a> + <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">#16</a></strong> — MQTT sebagai bus data utama</li>
  <li><strong><a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">#17</a></strong> — TLS untuk MQTT di internet</li>
  <li><strong><a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">#18</a> / <a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">#19</a></strong> — histori SQL atau grafik Grafana</li>
  <li><strong>#20 (ini)</strong> — keputusan arsitektur sebelum scale</li>
</ol>
<p>REST tidak “kalah” dari MQTT — ia <strong>lebih sederhana</strong> di tahap awal. MQTT unggul saat data harus mengalir ke banyak sistem tanpa ESP32 menjadi bottleneck.</p>

<h2>Keamanan &amp; Produksi</h2>
<ul>
  <li>Jangan commit password WiFi/MQTT ke GitHub — pakai NVS/WiFiManager (<a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">#12</a>)</li>
  <li>Placeholder <code>GANTI_PASSWORD_MQTT</code> — ganti sebelum deploy lapangan</li>
  <li>Expose REST tanpa auth hanya di VLAN sensor terpisah</li>
  <li>Audit trail: MQTT + <a href="/artikel/ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt">timestamp (#34)</a> lebih mudah di-log ke DB daripada polling REST acak</li>
</ul>

<h2>Langkah Selanjutnya (Seri 2)</h2>
<ul>
  <li><strong><a href="/artikel/esp-now-kirim-data-antar-esp32-tanpa-router-wifi">ESP-NOW (#25)</a>:</strong> komunikasi antar ESP32 tanpa router WiFi</li>
  <li><strong><a href="/artikel/lora-esp32-modul-sx1278-kirim-data-jarak-jauh">LoRa SX1278 (#26)</a>:</strong> sensor jarak jauh tanpa infrastruktur WiFi</li>
  <li><strong><a href="/artikel/esp32-cam-streaming-mjpeg-capture-foto-wifi">ESP32-CAM (#27)</a>:</strong> streaming visual MJPEG — bandwidth tinggi, bukan MQTT</li>
  <li><strong><a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Broker Mosquitto (#16)</a></strong> — fondasi MQTT production</li>
  <li><strong><a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">InfluxDB + Grafana (#19)</a></strong> — visualisasi setelah pilih MQTT</li>
  <li><strong><a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">MQTT TLS (#17)</a></strong> — amankan push data di internet</li>
  <li>Capstone <strong><a href="/artikel/smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt">greenhouse (#39)</a></strong> — hybrid sensor MQTT + dashboard Grafana</li>
</ul>

<p>Memahami REST vs MQTT membantu kamu <strong>memilih alat yang tepat</strong>, bukan memaksakan satu protokol untuk semua lapisan. Lanjutkan Seri 2 di <a href="/artikel">halaman artikel</a> Koding Indonesia.</p>
HTML;
    }
}
