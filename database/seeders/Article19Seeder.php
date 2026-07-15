<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article19Seeder extends Seeder
{
    public function run(): void
    {
        $admin  = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first();

        if (! $admin || ! $iotCat) {
            throw new \RuntimeException('User atau kategori tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'influxdb-grafana-dashboard-histori-sensor-esp32-mqtt';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $iotCat->id,
                'title'           => 'Histori Sensor ESP32: InfluxDB + Grafana Dashboard dari Data MQTT',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'InfluxDB + Grafana — Dashboard Histori Sensor ESP32 MQTT',
                'seo_description' => 'Pasang InfluxDB 2 dan Grafana di Raspberry Pi/VPS: Telegraf atau Python tulis data MQTT ESP32, lalu grafik suhu & kelembaban — lanjutan MySQL #18.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        Tag::updateOrCreate(['slug' => 'influxdb'], ['name' => 'influxdb']);
        Tag::updateOrCreate(['slug' => 'grafana'], ['name' => 'grafana']);
        Tag::updateOrCreate(['slug' => 'docker'], ['name' => 'docker']);

        $tagIds = Tag::whereIn('slug', [
            'influxdb', 'grafana', 'mqtt', 'mosquitto', 'iot', 'esp32', 'linux', 'docker',
        ])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command->info('✓ Artikel ke-19 berhasil dipublish: ' . $article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — Dari Database ke Dashboard</h2>
<p>Di <a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">artikel #18</a>, data sensor ESP32 sudah tersimpan di <strong>MySQL</strong> — bagus untuk query SQL dan ekspor. Tapi untuk <strong>grafik histori</strong> (zoom 24 jam, rata-rata per jam, alert suhu), stack industri memakai <strong>time-series database</strong> + <strong>dashboard</strong>.</p>

<p>Artikel <strong>Jalur B</strong> ini melengkapi pipeline: <strong>InfluxDB 2</strong> menyimpan titik data sensor dengan timestamp akurat (<a href="/artikel/ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt">#34</a>), lalu <strong>Grafana</strong> menampilkan grafik suhu &amp; kelembaban secara interaktif. ESP32 tetap publish ke MQTT seperti biasa — tidak perlu ubah firmware.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> Broker <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Mosquitto #16</a> jalan, payload JSON dengan <code>unix</code> (<a href="/artikel/ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt">#34</a>), paham <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">MQTT (#7)</a>. Disarankan sudah baca <a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">Python → MySQL (#18)</a> untuk membandingkan dua pendekatan penyimpanan.</p>
</blockquote>

<h2><a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">MySQL (#18)</a> vs InfluxDB — Kapan Pakai Apa?</h2>
<table>
  <thead>
    <tr><th>Aspek</th><th><a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">MySQL (#18)</a></th><th>InfluxDB (artikel ini)</th></tr>
  </thead>
  <tbody>
    <tr><td><strong>Kekuatan</strong></td><td>Relasi, JOIN, laporan SQL</td><td>Grafik time-series, retensi otomatis, downsampling</td></tr>
    <tr><td><strong>Query</strong></td><td>SQL</td><td>Flux (atau SQL di InfluxDB 3)</td></tr>
    <tr><td><strong>Dashboard</strong></td><td>Grafana + datasource MySQL</td><td>Grafana + datasource InfluxDB (native)</td></tr>
    <tr><td><strong>Produksi</strong></td><td>Bisa keduanya paralel: MySQL arsip, InfluxDB grafik</td><td>Telegraf ringan, tanpa kode Python</td></tr>
  </tbody>
</table>

<h2>Arsitektur: MQTT → InfluxDB → Grafana</h2>
<figure role="img" aria-label="Diagram pipeline MQTT ke Grafana: ESP32 publish ke Mosquitto, lalu Telegraf atau Python tulis ke InfluxDB, Grafana tampilkan panel suhu dan kelembaban" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 920 380" style="display:block;max-width:920px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="igArrow" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/></marker>
    <marker id="igArrowOrange" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#FF7A2F"/></marker>
    <marker id="igArrowGreen" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#2E7D32"/></marker>
  </defs>
  <rect x="0" y="0" width="920" height="380" fill="#F5F5F0" rx="6"/>
  <rect x="24" y="40" width="180" height="90" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="114" y="72" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">ESP32 + DHT22</text>
  <text x="114" y="94" text-anchor="middle" fill="#4A5568" font-size="11">publish JSON</text>
  <text x="114" y="114" text-anchor="middle" fill="#718096" font-size="11">suhu · RH · unix</text>
  <line x1="204" y1="85" x2="268" y2="85" stroke="#FF7A2F" stroke-width="2.5" marker-end="url(#igArrowOrange)"/>
  <text x="236" y="76" text-anchor="middle" fill="#FF7A2F" font-size="11" font-weight="700">MQTT →</text>
  <rect x="274" y="40" width="210" height="90" rx="6" fill="#2979FF" stroke="#000" stroke-width="2.5"/>
  <text x="379" y="72" text-anchor="middle" fill="#fff" font-size="14" font-weight="700">Broker Mosquitto</text>
  <text x="379" y="94" text-anchor="middle" fill="#e3f2fd" font-size="11">192.168.1.50:1883</text>
  <text x="379" y="114" text-anchor="middle" fill="#cfe4ff" font-size="11">topic Seri 2</text>
  <line x1="379" y1="130" x2="379" y2="168" stroke="#2979FF" stroke-width="2.5" marker-end="url(#igArrow)"/>
  <text x="404" y="156" fill="#2979FF" font-size="11" font-weight="700">subscribe ↓</text>
  <rect x="24" y="178" width="220" height="70" rx="6" fill="#FFF3E8" stroke="#000" stroke-width="2.5"/>
  <text x="134" y="208" text-anchor="middle" fill="#1a1a1a" font-size="13" font-weight="700">Opsi A · Telegraf</text>
  <text x="134" y="228" text-anchor="middle" fill="#4A5568" font-size="11">mqtt_consumer → write</text>
  <rect x="264" y="178" width="220" height="70" rx="6" fill="#FFF3E8" stroke="#000" stroke-width="2.5"/>
  <text x="374" y="208" text-anchor="middle" fill="#1a1a1a" font-size="13" font-weight="700">Opsi B · Python</text>
  <text x="374" y="228" text-anchor="middle" fill="#4A5568" font-size="11">influxdb-client</text>
  <line x1="244" y1="200" x2="530" y2="200" stroke="#2979FF" stroke-width="2.5" marker-end="url(#igArrow)"/>
  <line x1="484" y1="226" x2="530" y2="226" stroke="#2979FF" stroke-width="2.5" marker-end="url(#igArrow)"/>
  <text x="508" y="192" text-anchor="middle" fill="#2979FF" font-size="10" font-weight="700">write →</text>
  <rect x="536" y="168" width="200" height="90" rx="6" fill="#2979FF" stroke="#000" stroke-width="2.5"/>
  <text x="636" y="200" text-anchor="middle" fill="#fff" font-size="14" font-weight="700">InfluxDB 2</text>
  <text x="636" y="222" text-anchor="middle" fill="#e3f2fd" font-size="11">bucket iot_sensors</text>
  <text x="636" y="242" text-anchor="middle" fill="#cfe4ff" font-size="11">measurement dht22</text>
  <line x1="636" y1="258" x2="636" y2="292" stroke="#2E7D32" stroke-width="2.5" marker-end="url(#igArrowGreen)"/>
  <text x="660" y="280" fill="#2E7D32" font-size="11" font-weight="700">query ↓</text>
  <rect x="500" y="300" width="272" height="50" rx="6" fill="#F5F5F0" stroke="#000" stroke-width="2.5"/>
  <text x="636" y="322" text-anchor="middle" fill="#1a1a1a" font-size="13" font-weight="700">Grafana :3000</text>
  <text x="636" y="340" text-anchor="middle" fill="#4A5568" font-size="11">panel suhu &amp; kelembaban 24 jam</text>
  <text x="460" y="368" text-anchor="middle" fill="#4A5568" font-size="11">Alur: ESP32 publish MQTT → ingest Telegraf/Python → InfluxDB → Grafana</text>
</svg>
<figcaption style="margin-top:.75rem;font-size:.875rem;color:#4A5568;text-align:center">Diagram pipeline MQTT → InfluxDB → Grafana — ESP32 publish ke <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Mosquitto (#16)</a>; pilih Telegraf atau Python (#18-style) untuk tulis ke InfluxDB; Grafana query bucket untuk grafik.</figcaption>
</figure>

<p><strong>Payload contoh</strong> (sama dengan <a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">#18</a> / <a href="/artikel/ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt">#34</a>):</p>
<pre><code>{"suhu":28.5,"kelembaban":65.2,"timestamp":"2026-07-02T14:30:00","unix":1782977400}</code></pre>

<h2>Yang Kamu Butuhkan</h2>
<ul>
  <li><strong>Raspberry Pi 4 / VPS</strong> — minimal 2 GB RAM (InfluxDB + Grafana + Mosquitto bisa satu mesin)</li>
  <li><strong>Docker</strong> + Docker Compose</li>
  <li><strong>InfluxDB 2.7</strong>, <strong>Grafana</strong>, <strong>Telegraf</strong> (opsi A)</li>
  <li>Atau <strong>Python 3.10+</strong> + <code>influxdb-client</code> (opsi B, mirip <a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">#18</a>)</li>
  <li>Broker <code>192.168.1.50:1883</code>, user subscriber <code>kindo_subscriber</code> dari <a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">#18</a></li>
</ul>

<p><strong>Estimasi biaya:</strong> Rp 0 (open source) jika pakai Pi/VPS yang sudah ada; Grafana &amp; InfluxDB gratis untuk self-host.</p>

<h2>Docker Compose: InfluxDB + Grafana + Telegraf</h2>
<p>Buat folder <code>~/kindo-iot-stack</code>:</p>
<pre><code class="language-bash">mkdir -p ~/kindo-iot-stack &amp;&amp; cd ~/kindo-iot-stack</code></pre>

<p>File <code>docker-compose.yml</code>:</p>
<pre><code class="language-yaml">services:
  influxdb:
    image: influxdb:2.7
    container_name: kindo-influxdb
    restart: unless-stopped
    ports:
      - "8086:8086"
    volumes:
      - influxdb-data:/var/lib/influxdb2
    environment:
      DOCKER_INFLUXDB_INIT_MODE: setup
      DOCKER_INFLUXDB_INIT_USERNAME: kindo_admin
      DOCKER_INFLUXDB_INIT_PASSWORD: GANTI_PASSWORD_INFLUX_ADMIN
      DOCKER_INFLUXDB_INIT_ORG: kindo
      DOCKER_INFLUXDB_INIT_BUCKET: iot_sensors
      DOCKER_INFLUXDB_INIT_ADMIN_TOKEN: GANTI_INFLUX_TOKEN

  grafana:
    image: grafana/grafana:11.3.0
    container_name: kindo-grafana
    restart: unless-stopped
    ports:
      - "3000:3000"
    volumes:
      - grafana-data:/var/lib/grafana
    depends_on:
      - influxdb

  telegraf:
    image: telegraf:1.32
    container_name: kindo-telegraf
    restart: unless-stopped
    volumes:
      - ./telegraf.conf:/etc/telegraf/telegraf.conf:ro
    depends_on:
      - influxdb

volumes:
  influxdb-data:
  grafana-data:</code></pre>

<pre><code class="language-bash">docker compose up -d
docker compose ps</code></pre>

<blockquote>
  <p><strong>Pro tip:</strong> Simpan token InfluxDB di file <code>.env</code> (jangan commit ke Git). Ganti semua placeholder <code>GANTI_*</code> sebelum production.</p>
</blockquote>

<h2>Telegraf: MQTT → InfluxDB (Opsi A — Tanpa Kode)</h2>
<p>File <code>telegraf.conf</code> di folder yang sama:</p>
<pre><code class="language-toml">[agent]
  interval = "10s"
  flush_interval = "10s"

[[outputs.influxdb_v2]]
  urls = ["http://influxdb:8086"]
  token = "GANTI_INFLUX_TOKEN"
  organization = "kindo"
  bucket = "iot_sensors"

[[inputs.mqtt_consumer]]
  servers = ["tcp://192.168.1.50:1883"]
  topics = ["kodingindonesia/esp32/dht22/data"]
  username = "kindo_subscriber"
  password = "GANTI_PASSWORD_SUBSCRIBER"
  qos = 1
  client_id = "kindo-telegraf"
  data_format = "json"
  json_time_key = "unix"
  json_time_format = "unix"
  name_override = "dht22"</code></pre>

<p>Field <code>suhu</code> dan <code>kelembaban</code> dari JSON otomatis jadi field InfluxDB; timestamp dari <code>unix</code> (<a href="/artikel/ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt">#34</a>).</p>

<pre><code class="language-bash">docker compose restart telegraf
docker logs kindo-telegraf --tail 20</code></pre>

<h2>Opsi B: Python → InfluxDB (Lanjutan #18)</h2>
<p>Jika sudah nyaman dengan <a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">subscriber Python #18</a>, tambahkan penulisan ke InfluxDB:</p>
<pre><code class="language-python">from influxdb_client import InfluxDBClient, Point
from influxdb_client.client.write_api import SYNCHRONOUS

INFLUX_URL = "http://127.0.0.1:8086"
INFLUX_TOKEN = "GANTI_INFLUX_TOKEN"
INFLUX_ORG = "kindo"
INFLUX_BUCKET = "iot_sensors"

client = InfluxDBClient(url=INFLUX_URL, token=INFLUX_TOKEN, org=INFLUX_ORG)
write_api = client.write_api(write_options=SYNCHRONOUS)

def simpan_ke_influx(payload: dict) -&gt; None:
  ts_ns = int(payload["unix"]) * 1_000_000_000
  point = (
    Point("dht22")
    .field("suhu", float(payload.get("suhu", 0)))
    .field("kelembaban", float(payload.get("kelembaban", 0)))
    .time(ts_ns)
  )
  write_api.write(bucket=INFLUX_BUCKET, org=INFLUX_ORG, record=point)</code></pre>

<p>Panggil <code>simpan_ke_influx(data)</code> di dalam <code>on_message</code> bersama atau menggantikan INSERT MySQL — terserah kebutuhan proyek.</p>

<h2>Verifikasi Data di InfluxDB</h2>
<p>Masuk ke UI InfluxDB: <code>http://192.168.1.50:8086</code> → Data Explorer, atau CLI:</p>
<pre><code class="language-bash">docker exec -it kindo-influxdb influx query '
from(bucket: "iot_sensors")
  |> range(start: -1h)
  |> filter(fn: (r) =&gt; r._measurement == "dht22")
  |> limit(n: 10)
' --org kindo --token GANTI_INFLUX_TOKEN</code></pre>

<p>Harus muncul field <code>suhu</code> dan <code>kelembaban</code> dengan waktu yang masuk akal (bukan tahun 1970 — pastikan ESP32 sudah NTP).</p>

<h2>Grafana: Datasource InfluxDB</h2>
<ol>
  <li>Buka <code>http://192.168.1.50:3000</code> — login default <code>admin</code> / <code>admin</code> (ganti password saat pertama masuk)</li>
  <li><strong>Connections → Data sources → Add data source → InfluxDB</strong></li>
  <li>Query Language: <strong>Flux</strong></li>
  <li>URL: <code>http://influxdb:8086</code> (dari container Grafana) atau <code>http://127.0.0.1:8086</code> jika Grafana di host</li>
  <li>Organization: <code>kindo</code> · Bucket: <code>iot_sensors</code> · Token: <code>GANTI_INFLUX_TOKEN</code></li>
  <li><strong>Save &amp; test</strong> → harus hijau</li>
</ol>

<h2>Dashboard: Grafik Suhu &amp; Kelembaban</h2>
<p><strong>Dashboard baru</strong> → Add visualization → Time series.</p>

<p>Query Flux untuk <strong>suhu</strong> (24 jam terakhir):</p>
<pre><code class="language-sql">from(bucket: "iot_sensors")
  |> range(start: -24h)
  |> filter(fn: (r) =&gt; r._measurement == "dht22")
  |> filter(fn: (r) =&gt; r._field == "suhu")
  |> aggregateWindow(every: 5m, fn: mean, createEmpty: false)</code></pre>

<p>Duplikasi panel untuk <code>_field == "kelembaban"</code>. Tambahkan panel <strong>Stat</strong> untuk nilai terakhir, dan <strong>Gauge</strong> opsional (0–100 % RH).</p>

<table>
  <thead>
    <tr><th>Panel</th><th>Tipe</th><th>Field</th></tr>
  </thead>
  <tbody>
    <tr><td>Suhu 24 jam</td><td>Time series</td><td><code>suhu</code></td></tr>
    <tr><td>Kelembaban 24 jam</td><td>Time series</td><td><code>kelembaban</code></td></tr>
    <tr><td>Nilai sekarang</td><td>Stat</td><td>keduanya</td></tr>
  </tbody>
</table>

<blockquote>
  <p><strong>Pro tip:</strong> Set timezone dashboard ke <strong>Asia/Jakarta</strong> (Dashboard settings → General) agar sumbu waktu cocok dengan <code>timestamp</code> lokal ESP32 (<a href="/artikel/ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt">#34</a>).</p>
</blockquote>

<p>Untuk variabel dashboard yang bisa dipakai ulang, simpan query Flux sebagai <strong>Dashboard variable</strong> (mis. <code>$measurement</code> = <code>dht22</code> atau <code>bme280</code> dari <a href="/artikel/i2c-esp32-sensor-bme280-suhu-tekanan-mqtt">#13</a>). Panel Stat bisa menampilkan <em>Last value</em> dengan unit <code>°C</code> dan <code>%</code> — cocok dipantau dari HP di jaringan LAN.</p>

<h2>Uji Cepat Tanpa ESP32 (mosquitto_pub)</h2>
<p>Sebelum firmware siap, kirim payload uji ke broker (<a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">#16</a>) sebagai user subscriber:</p>
<pre><code class="language-bash">mosquitto_pub -h 192.168.1.50 -p 1883 \
  -u kindo_subscriber -P 'GANTI_PASSWORD_SUBSCRIBER' \
  -t "kodingindonesia/esp32/dht22/data" \
  -m '{"suhu":29.1,"kelembaban":62.0,"timestamp":"2026-07-02T14:30:00","unix":1782977400}'</code></pre>
<p>Refresh Grafana — titik baru harus muncul dalam beberapa detik. Ulangi 3–5 kali dengan nilai berbeda untuk melihat garis naik/turun. Ini cara tercepat memastikan Telegraf, InfluxDB, dan Grafana terhubung tanpa debug firmware ESP32 sekaligus — sama seperti uji <code>mosquitto_sub</code> di <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">artikel MQTT (#7)</a>.</p>

<h2>Retensi Data &amp; Penghematan Disk</h2>
<p>InfluxDB 2 mengelola retensi lewat <strong>Bucket</strong>. Di UI InfluxDB: <strong>Data → Buckets → iot_sensors → Edit</strong> — set retensi misalnya <strong>30 hari</strong> atau <strong>90 hari</strong> tergantung kapasitas SD card Pi.</p>
<ul>
  <li>Turunkan frekuensi publish ESP32 (mis. dari 5 detik ke 30 detik) jika grafik cukup update per menit</li>
  <li>Gunakan <code>aggregateWindow</code> di Grafana agar panel tetap responsif meski data mentah menumpuk</li>
  <li>Backup bucket penting via <code>influx backup</code> sebelum upgrade major version InfluxDB</li>
</ul>
<p>MySQL dari <a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">#18</a> bisa tetap menyimpan arsip jangka panjang sementara InfluxDB fokus grafik operasional harian — kombinasi yang umum di proyek IoT skala kecil hingga menengah.</p>

<h2>Alert Grafana (Opsional)</h2>
<p>Grafana mendukung <strong>alert rules</strong> — misalnya notifikasi Telegram atau email jika suhu &gt; 35 °C lebih dari 10 menit. Buat rule dari panel Time series → <strong>Alert</strong> → New alert rule. Query Flux sama seperti panel; set threshold dan contact point di <strong>Alerting → Contact points</strong>. Fitur ini berguna untuk <a href="/artikel/smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt">greenhouse (#39)</a> atau cold storage tanpa coding tambahan — melengkapi otomasi visual <a href="/artikel/node-red-dashboard-otomasi-iot-mqtt-esp32">Node-RED (#23)</a>.</p>

<h2>Auto-start Setelah Reboot Raspberry Pi</h2>
<p>Docker Compose dengan <code>restart: unless-stopped</code> sudah cukup jika daemon Docker aktif saat boot. Pastikan:</p>
<pre><code class="language-bash">sudo systemctl enable docker
cd ~/kindo-iot-stack && docker compose up -d</code></pre>
<p>Setelah power cycle Pi, tunggu ~60 detik lalu buka Grafana — stack harus hidup otomatis. Telegraf akan reconnect ke MQTT <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">broker #16</a> begitu jaringan siap; tidak perlu SSH manual setiap reboot kecuali ada perubahan <code>telegraf.conf</code>.</p>

<h2>Opsi Bonus: Grafana + MySQL (Lanjutan #18)</h2>
<p>Jika sudah punya data di tabel <code>sensor_readings</code> MySQL, Grafana juga bisa pakai <strong>MySQL datasource</strong> tanpa InfluxDB:</p>
<pre><code class="language-sql">SELECT recorded_at AS time, suhu, kelembaban
FROM sensor_readings
WHERE recorded_at &gt; NOW() - INTERVAL 24 HOUR
ORDER BY recorded_at;</code></pre>

<p>InfluxDB lebih nyaman untuk jutaan titik data dan retensi otomatis; MySQL cukup untuk skala kecil. Banyak tim memakai <strong>keduanya</strong>: InfluxDB untuk live dashboard, MySQL untuk backup laporan bulanan.</p>

<h2>Opsi TLS &amp; Produksi</h2>
<p>Deploy di internet: amankan MQTT dengan <a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">TLS #17</a> (port 8883). Telegraf mendukung <code>tls_ca</code> / <code>tls_cert</code> di blok <code>mqtt_consumer</code>. Grafana &amp; InfluxDB sebaiknya di belakang reverse proxy HTTPS (Nginx) — jangan expose port 3000/8086 mentah ke internet tanpa auth kuat.</p>

<h2>Penjelasan Bagian Kritis</h2>
<ol>
  <li><strong><code>json_time_key = "unix"</code></strong> — waktu titik data = waktu sensor, bukan waktu Telegraf menerima pesan.</li>
  <li><strong><code>name_override = "dht22"</code></strong> — measurement InfluxDB konsisten untuk query Flux.</li>
  <li><strong>User <code>kindo_subscriber</code></strong> — sama dengan <a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">#18</a>; jangan pakai credential publisher ESP32.</li>
  <li><strong>Topic</strong> — harus <code>kodingindonesia/esp32/dht22/data</code> (konsisten Seri 2).</li>
  <li><strong>Retensi bucket</strong> — atur di InfluxDB (mis. 30 hari) agar disk Pi tidak penuh.</li>
  <li><strong>Grafana vs <a href="/artikel/node-red-dashboard-otomasi-iot-mqtt-esp32">Node-RED (#23)</a></strong> — Grafana fokus histori/grafik; Node-RED fokus otomasi visual.</li>
</ol>

<h2>Uji Coba (Checklist)</h2>
<ol>
  <li><code>docker compose ps</code> — ketiga container running</li>
  <li>InfluxDB UI login + bucket <code>iot_sensors</code> ada</li>
  <li>ESP32 publish (atau <code>mosquitto_pub</code> dengan JSON + unix) → data muncul di Data Explorer</li>
  <li>Grafana datasource InfluxDB → test sukses</li>
  <li>Panel suhu menampilkan garis naik/turun (bukan flat kosong)</li>
  <li>Timezone dashboard Asia/Jakarta — jam cocok dengan WIB</li>
  <li>Restart Pi → <code>docker compose up -d</code> otomatis (opsional: systemd unit)</li>
</ol>

<h2>Tips &amp; Troubleshooting</h2>
<ul>
  <li><strong>Grafik kosong:</strong> Cek range waktu dashboard (24h) vs data benar-benar masuk — query Flux manual di InfluxDB</li>
  <li><strong>Timestamp 1970:</strong> Field <code>unix</code> kosong di payload — aktifkan NTP di ESP32 (<a href="/artikel/ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt">#34</a>)</li>
  <li><strong>Telegraf rc=5 MQTT:</strong> User/password salah — samakan dengan <code>/etc/mosquitto/passwd</code></li>
  <li><strong>Grafana tidak reach InfluxDB:</strong> Pakai hostname container <code>http://influxdb:8086</code> dari dalam jaringan Docker</li>
  <li><strong>Telegraf tidak reach Mosquitto:</strong> Dari container Docker, <code>localhost</code> bukan host Pi — pakai IP LAN host (<code>192.168.1.50</code>) atau tambahkan <code>extra_hosts: ["host.docker.internal:host-gateway"]</code> lalu <code>tcp://host.docker.internal:1883</code></li>
  <li><strong>Disk penuh:</strong> Kurangi retensi bucket atau turunkan frekuensi publish ESP32</li>
  <li><strong>Port 3000 bentrok:</strong> Ubah mapping <code>"3001:3000"</code> di compose</li>
  <li><strong>Data dobel MySQL + Influx:</strong> Normal jika jalankan <a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">#18</a> dan Telegraf bersamaan — atau pilih satu penyimpanan utama</li>
</ul>

<h2>Keamanan &amp; Produksi</h2>
<ul>
  <li>Jangan commit token InfluxDB, password Grafana, atau <code>.env</code> ke GitHub</li>
  <li>Ganti password default Grafana segera setelah instalasi</li>
  <li>Plain MQTT (1883) hanya untuk LAN — production pakai <a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">TLS #17</a></li>
  <li>Batasi akses Grafana dengan user/role — jangan publik tanpa HTTPS</li>
</ul>

<h2>Langkah Selanjutnya (Seri 2)</h2>
<ul>
  <li><strong><a href="/artikel/rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32">REST API vs MQTT (#20)</a></strong> — kapan pakai HTTP polling vs push MQTT</li>
  <li><strong><a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">Python → MySQL (#18)</a></strong> — arsip SQL paralel dengan grafik InfluxDB</li>
  <li><strong><a href="/artikel/home-assistant-integrasi-esp32-mqtt">Home Assistant (#21)</a></strong> — dashboard smart home alternatif</li>
  <li><strong><a href="/artikel/i2c-esp32-sensor-bme280-suhu-tekanan-mqtt">BME280 (#13)</a></strong> — tambah field tekanan di measurement <code>bme280</code></li>
  <li><strong><a href="/artikel/sensor-gerak-pir-esp32-lampu-mqtt-debounce">PIR (#24)</a></strong> — panel event gerak di Grafana</li>
  <li><strong><a href="/artikel/gateway-lora-mqtt-esp32-sensor-jarak-jauh-dashboard">Gateway LoRa → MQTT (#28)</a></strong> — sensor kebun tanpa WiFi masuk panel Grafana ini</li>
  <li>Capstone <strong><a href="/artikel/smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt">greenhouse (#39)</a></strong> — sensor + pompa + dashboard Grafana</li>
</ul>

<p>Dengan InfluxDB dan Grafana, histori sensor ESP32 akhirnya <strong>terlihat</strong> — grafik interaktif siap dipantau dari browser. Lanjutkan Seri 2 di <a href="/artikel">halaman artikel</a> Koding Indonesia.</p>
HTML;
    }
}
