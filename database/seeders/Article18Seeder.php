<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article18Seeder extends Seeder
{
    public function run(): void
    {
        $admin  = User::first();
        $netCat = Category::where('slug', 'networking')->first();

        if (! $admin || ! $netCat) {
            throw new \RuntimeException('User atau kategori tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'python-subscriber-mqtt-mysql-simpan-data-sensor-esp32';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $netCat->id,
                'title'           => 'Subscriber Python: Simpan Data MQTT ke MySQL untuk Histori Sensor ESP32',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'Python MQTT → MySQL — Subscriber Histori Sensor ESP32',
                'seo_description' => 'Buat subscriber Python (paho-mqtt) yang subscribe topic sensor ESP32 dan INSERT ke MySQL — parse timestamp dari artikel NTP #34, siap Grafana #19.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        Tag::updateOrCreate(['slug' => 'python'], ['name' => 'python']);
        Tag::updateOrCreate(['slug' => 'mysql'], ['name' => 'mysql']);

        $tagIds = Tag::whereIn('slug', [
            'python', 'mysql', 'mqtt', 'mosquitto', 'iot', 'esp32', 'networking', 'linux',
        ])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command->info('✓ Artikel ke-18 berhasil dipublish: ' . $article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — Kenapa Subscriber Python?</h2>
<p>Di <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">broker Mosquitto (#16)</a>, ESP32 sudah publish JSON sensor ke topic MQTT. Di <a href="/artikel/ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt">artikel NTP (#34)</a>, setiap payload punya field <code>timestamp</code> dan <code>unix</code> — tapi data itu <strong>masih lewat</strong> kecuali ada proses yang <strong>menangkap dan menyimpan</strong> ke database.</p>

<p>Artikel <strong>Jalur B</strong> ini melengkapi stack: script <strong>Python</strong> berjalan di Raspberry Pi / VPS, <strong>subscribe</strong> topic sensor, lalu <strong>INSERT</strong> ke <strong>MySQL</strong>. Hasilnya: histori suhu &amp; kelembaban bisa di-query, diekspor, atau divisualisasikan di <strong>Artikel #19</strong> (InfluxDB + Grafana).</p>

<blockquote>
  <p><strong>Prasyarat:</strong> Broker <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Mosquitto #16</a> jalan, ESP32 publish JSON dengan timestamp (<a href="/artikel/ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt">#34</a>), paham dasar <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">MQTT (#7)</a>. Opsi production: transport TLS dari <a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">#17</a>.</p>
</blockquote>

<h2>Arsitektur: MQTT → Python → MySQL</h2>
<pre><code>  [ ESP32 + DHT22 ]
        | publish JSON (suhu, kelembaban, timestamp, unix)
        v
  [ Mosquitto #16 ]  topic: kodingindonesia/esp32/dht22/data
        |
        | subscribe (user terpisah: kindo_subscriber)
        v
  [ Python + paho-mqtt ]  on_message → parse JSON → INSERT
        v
  [ MySQL ]  tabel sensor_readings
        |
        +-- Query SQL / phpMyAdmin
        +-- Grafana (#19 nanti) / export CSV</code></pre>

<p><strong>Payload contoh</strong> (dari ESP32 #34):</p>
<pre><code>{"suhu":28.5,"kelembaban":65.2,"timestamp":"2026-07-02T14:30:00","unix":1782977400}</code></pre>

<h2>Yang Kamu Butuhkan</h2>
<ul>
  <li><strong>Raspberry Pi / VPS</strong> — tempat broker (#16) dan MySQL (bisa satu mesin)</li>
  <li><strong>MySQL 8</strong> atau MariaDB 10.6+</li>
  <li><strong>Python 3.10+</strong> dengan <code>venv</code></li>
  <li>Library: <strong>paho-mqtt</strong>, <strong>mysql-connector-python</strong></li>
  <li>ESP32 publish ke topic <code>kodingindonesia/esp32/dht22/data</code> (broker <code>192.168.1.50:1883</code>)</li>
</ul>

<p><strong>Estimasi biaya:</strong> Rp 0 jika pakai Pi/VPS yang sudah ada untuk Mosquitto; MySQL open source.</p>

<h2>Skema Database MySQL</h2>
<p>Buat database dan tabel untuk histori sensor:</p>
<pre><code class="language-sql">CREATE DATABASE IF NOT EXISTS kindo_iot
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'kindo_iot'@'localhost' IDENTIFIED BY 'GANTI_PASSWORD_MYSQL';
GRANT ALL PRIVILEGES ON kindo_iot.* TO 'kindo_iot'@'localhost';
FLUSH PRIVILEGES;

USE kindo_iot;

CREATE TABLE IF NOT EXISTS sensor_readings (
  id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  device_id    VARCHAR(64)  NOT NULL DEFAULT 'esp32-dht22',
  suhu         DECIMAL(5,2) NULL,
  kelembaban   DECIMAL(5,2) NULL,
  recorded_at  DATETIME     NOT NULL,
  unix_ts      INT UNSIGNED NULL,
  topic        VARCHAR(128) NOT NULL,
  created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_recorded_at (recorded_at),
  INDEX idx_device (device_id)
) ENGINE=InnoDB;</code></pre>

<p>Kolom <code>recorded_at</code> diisi dari field <code>unix</code> payload (UTC epoch) — lebih andal daripada string ISO tanpa timezone.</p>

<h2>User MQTT Subscriber di Mosquitto</h2>
<p>Pisahkan user subscriber dari publisher ESP32 (<code>kindo_esp32</code> / <code>GANTI_PASSWORD_MQTT</code> di #16):</p>
<pre><code class="language-bash">sudo mosquitto_passwd /etc/mosquitto/passwd kindo_subscriber
# masukkan password — jangan sama dengan kindo_esp32

sudo systemctl restart mosquitto</code></pre>

<blockquote>
  <p><strong>Pro tip:</strong> Beri ACL Mosquitto agar <code>kindo_subscriber</code> hanya <strong>read</strong> topic sensor, sedangkan <code>kindo_esp32</code> hanya <strong>write</strong> — mencegah subscriber ikut publish data palsu.</p>
</blockquote>

<h2>Setup Python (venv + dependensi)</h2>
<pre><code class="language-bash">sudo apt update
sudo apt install -y python3-venv python3-pip mysql-server

mkdir -p ~/kindo-mqtt-mysql &amp;&amp; cd ~/kindo-mqtt-mysql
python3 -m venv .venv
source .venv/bin/activate
pip install paho-mqtt mysql-connector-python</code></pre>

<p>Simpan kredensial di file env (jangan commit ke Git):</p>
<pre><code class="language-bash">cat &gt; .env &lt;&lt;'EOF'
MQTT_HOST=192.168.1.50
MQTT_PORT=1883
MQTT_USER=kindo_subscriber
MQTT_PASS=GANTI_PASSWORD_SUBSCRIBER
MQTT_TOPIC=kodingindonesia/esp32/dht22/data
# Untuk TLS (#17): MQTT_PORT=8883 + tls_set() di script

MYSQL_HOST=127.0.0.1
MYSQL_USER=kindo_iot
MYSQL_PASS=GANTI_PASSWORD_MYSQL
MYSQL_DB=kindo_iot
EOF
chmod 600 .env</code></pre>

<h2>Script Python: Subscriber + INSERT MySQL</h2>
<p>File <code>subscriber_mqtt_mysql.py</code>:</p>
<pre><code class="language-python">#!/usr/bin/env python3
"""Subscriber MQTT → MySQL untuk sensor ESP32 (Seri 2 Koding Indonesia)."""

import json
import os
import sys
from datetime import datetime, timezone
from pathlib import Path

import mysql.connector
import paho.mqtt.client as mqtt

# Muat .env sederhana (tanpa library tambahan)
env_path = Path(__file__).with_name(".env")
if env_path.exists():
    for line in env_path.read_text(encoding="utf-8").splitlines():
        line = line.strip()
        if line and not line.startswith("#") and "=" in line:
            k, v = line.split("=", 1)
            os.environ.setdefault(k.strip(), v.strip())

MQTT_HOST = os.getenv("MQTT_HOST", "192.168.1.50")
MQTT_PORT = int(os.getenv("MQTT_PORT", "1883"))
MQTT_USER = os.getenv("MQTT_USER", "kindo_subscriber")
MQTT_PASS = os.getenv("MQTT_PASS", "GANTI_PASSWORD_SUBSCRIBER")
MQTT_TOPIC = os.getenv("MQTT_TOPIC", "kodingindonesia/esp32/dht22/data")

MYSQL_CFG = {
    "host": os.getenv("MYSQL_HOST", "127.0.0.1"),
    "user": os.getenv("MYSQL_USER", "kindo_iot"),
    "password": os.getenv("MYSQL_PASS", "GANTI_PASSWORD_MYSQL"),
    "database": os.getenv("MYSQL_DB", "kindo_iot"),
}


def koneksi_mysql():
    return mysql.connector.connect(**MYSQL_CFG)


def parse_waktu(payload: dict) -&gt; datetime:
    """Prioritas: unix epoch → fallback ISO timestamp lokal."""
    if "unix" in payload and payload["unix"]:
        return datetime.fromtimestamp(int(payload["unix"]), tz=timezone.utc).replace(tzinfo=None)
    if "timestamp" in payload and payload["timestamp"]:
        return datetime.strptime(payload["timestamp"], "%Y-%m-%dT%H:%M:%S")
    return datetime.utcnow()


def simpan_ke_mysql(topic: str, payload: dict) -&gt; None:
    suhu = payload.get("suhu")
    rh = payload.get("kelembaban")
    if suhu is None and rh is None:
        return

    recorded = parse_waktu(payload)
    unix_ts = payload.get("unix")

    sql = """
        INSERT INTO sensor_readings
          (device_id, suhu, kelembaban, recorded_at, unix_ts, topic)
        VALUES (%s, %s, %s, %s, %s, %s)
    """
    vals = ("esp32-dht22", suhu, rh, recorded, unix_ts, topic)

    conn = koneksi_mysql()
    try:
        cur = conn.cursor()
        cur.execute(sql, vals)
        conn.commit()
        print(f"OK INSERT {recorded} suhu={suhu} rh={rh}", flush=True)
    finally:
        conn.close()


def on_connect(client, userdata, flags, reason_code, properties=None):
    if reason_code == 0:
        print(f"MQTT connected → subscribe {MQTT_TOPIC}", flush=True)
        client.subscribe(MQTT_TOPIC, qos=1)
    else:
        print(f"MQTT connect gagal rc={reason_code}", flush=True)


def on_message(client, userdata, msg):
    try:
        data = json.loads(msg.payload.decode("utf-8"))
    except json.JSONDecodeError as e:
        print(f"JSON invalid: {e}", flush=True)
        return
    try:
        simpan_ke_mysql(msg.topic, data)
    except mysql.connector.Error as e:
        print(f"MySQL error: {e}", flush=True)


def main():
    client = mqtt.Client(mqtt.CallbackAPIVersion.VERSION2, client_id="kindo-python-subscriber")
    client.username_pw_set(MQTT_USER, MQTT_PASS)
    client.on_connect = on_connect
    client.on_message = on_message

    print(f"Connect {MQTT_HOST}:{MQTT_PORT} ...", flush=True)
    client.connect(MQTT_HOST, MQTT_PORT, keepalive=60)
    client.loop_forever()


if __name__ == "__main__":
    main()</code></pre>

<h2>Penjelasan Bagian Kritis</h2>
<ol>
  <li><strong><code>parse_waktu()</code></strong> — pakai <code>unix</code> dari ESP32 (#34) agar urutan waktu konsisten di MySQL.</li>
  <li><strong>User terpisah</strong> — <code>kindo_subscriber</code> vs <code>kindo_esp32</code>; jangan pakai credential publisher untuk script server.</li>
  <li><strong>QoS 1 subscribe</strong> — pesan sensor tidak hilang saat subscriber offline sebentar (broker retain QoS1 queue).</li>
  <li><strong><code>mysql.connector</code></strong> — koneksi per INSERT sederhana untuk tutorial; production bisa pakai connection pool.</li>
  <li><strong>Topic</strong> — harus sama dengan ESP32: <code>kodingindonesia/esp32/dht22/data</code>.</li>
  <li><strong>Tanpa timestamp di payload</strong> — script fallback ke <code>utcnow()</code>, tapi sebaiknya ESP32 sudah ikut #34.</li>
</ol>

<h2>Jalankan sebagai Layanan (systemd)</h2>
<p>Agar subscriber jalan otomatis setelah reboot Pi:</p>
<pre><code class="language-ini"># /etc/systemd/system/kindo-mqtt-mysql.service
[Unit]
Description=Koding Indonesia MQTT subscriber to MySQL
After=network.target mosquitto.service mysql.service

[Service]
Type=simple
User=pi
WorkingDirectory=/home/pi/kindo-mqtt-mysql
EnvironmentFile=/home/pi/kindo-mqtt-mysql/.env
ExecStart=/home/pi/kindo-mqtt-mysql/.venv/bin/python subscriber_mqtt_mysql.py
Restart=on-failure
RestartSec=5

[Install]
WantedBy=multi-user.target</code></pre>

<pre><code class="language-bash">sudo systemctl daemon-reload
sudo systemctl enable --now kindo-mqtt-mysql
sudo systemctl status kindo-mqtt-mysql
journalctl -u kindo-mqtt-mysql -f</code></pre>

<h2>Opsi TLS (Port 8883)</h2>
<p>Jika broker sudah pakai TLS dari <a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">artikel #17</a>, tambahkan sebelum <code>connect()</code>:</p>
<pre><code class="language-python">import ssl

MQTT_PORT = 8883
client.tls_set(
    ca_certs="/path/to/ca.crt",
    certfile=None,
    keyfile=None,
    cert_reqs=ssl.CERT_REQUIRED,
    tls_version=ssl.PROTOCOL_TLS_CLIENT,
)
# client.tls_insecure_set(True)  # JANGAN di produksi</code></pre>

<p>Subscriber CLI dengan TLS:</p>
<pre><code class="language-bash">mosquitto_sub -h 192.168.1.50 -p 8883 \
  --cafile ca.crt \
  -u kindo_subscriber -P 'GANTI_PASSWORD_SUBSCRIBER' \
  -t "kodingindonesia/esp32/dht22/data" -v</code></pre>

<h2>Verifikasi &amp; Query SQL</h2>
<p>Setelah ESP32 publish dan script jalan, cek data masuk:</p>
<pre><code class="language-sql">SELECT id, device_id, suhu, kelembaban, recorded_at, unix_ts, topic
FROM sensor_readings
ORDER BY recorded_at DESC
LIMIT 10;</code></pre>

<pre><code class="language-sql">-- Rata-rata suhu per jam (contoh analitik sederhana)
SELECT DATE_FORMAT(recorded_at, '%Y-%m-%d %H:00') AS jam,
       ROUND(AVG(suhu), 1) AS avg_suhu,
       COUNT(*) AS jumlah
FROM sensor_readings
GROUP BY jam
ORDER BY jam DESC
LIMIT 24;</code></pre>

<h2>Uji Coba (Checklist)</h2>
<ol>
  <li>MySQL: tabel <code>sensor_readings</code> ada, user <code>kindo_iot</code> bisa INSERT</li>
  <li>Mosquitto: user <code>kindo_subscriber</code> bisa subscribe topic sensor</li>
  <li>Jalankan script → log <code>MQTT connected → subscribe ...</code></li>
  <li>ESP32 publish (atau <code>mosquitto_pub</code> dengan JSON + unix) → log <code>OK INSERT ...</code></li>
  <li>Query SQL menampilkan baris baru dengan <code>recorded_at</code> masuk akal</li>
  <li>Restart Pi → systemd auto-start subscriber</li>
  <li>Opsional: aktifkan TLS (#17) dan uji port 8883</li>
</ol>

<h2>Tips &amp; Troubleshooting</h2>
<ul>
  <li><strong>rc=5 MQTT:</strong> User/password salah — cek <code>.env</code> vs <code>/etc/mosquitto/passwd</code></li>
  <li><strong>MySQL Access denied:</strong> Grant belum jalan atau password beda dengan <code>.env</code></li>
  <li><strong>JSON invalid:</strong> Payload bukan UTF-8 JSON — cek ESP32 <code>serializeJson</code></li>
  <li><strong><code>recorded_at</code> tahun 1970:</strong> Field <code>unix</code> kosong — pastikan ESP32 sudah NTP (#34)</li>
  <li><strong>Duplikat banyak:</strong> Normal jika ESP32 publish tiap 10 detik; untuk dedup pakai UNIQUE key opsional</li>
  <li><strong>Connection refused MySQL:</strong> <code>bind-address</code> di <code>mysqld.cnf</code> — untuk lokal pakai 127.0.0.1</li>
  <li><strong>Script mati setelah logout:</strong> Pakai systemd, bukan terminal SSH saja</li>
</ul>

<h2>Keamanan &amp; Produksi</h2>
<ul>
  <li>Jangan commit file <code>.env</code> atau password ke GitHub</li>
  <li>Plain MQTT (1883) hanya untuk LAN — internet wajib <a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">TLS #17</a></li>
  <li>Backup database MySQL rutin (<code>mysqldump kindo_iot</code>)</li>
  <li>Batasi user MySQL: hanya INSERT/SELECT pada tabel sensor, bukan root</li>
</ul>

<h2>Langkah Selanjutnya (Seri 2)</h2>
<ul>
  <li><strong>Artikel #19:</strong> <strong>InfluxDB + Grafana</strong> — alternatif time-series + dashboard grafik</li>
  <li><strong><a href="/artikel/ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt">NTP &amp; timestamp (#34)</a></strong> — pastikan setiap node kirim <code>unix</code> akurat</li>
  <li><strong><a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">MQTT TLS (#17)</a></strong> — amankan subscriber di jaringan publik</li>
  <li><strong><a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Broker Mosquitto (#16)</a></strong> — fondasi infrastruktur</li>
  <li><strong><a href="/artikel/sensor-gerak-pir-esp32-lampu-mqtt-debounce">PIR (#24)</a></strong> — tambah tabel/event gerak dengan pola subscriber sama</li>
  <li><strong><a href="/artikel/i2c-esp32-sensor-bme280-suhu-tekanan-mqtt">BME280 (#13)</a></strong> — perluas kolom tekanan udara di skema MySQL</li>
</ul>

<p>Dengan pipeline MQTT → Python → MySQL, data sensor ESP32 akhirnya tersimpan sebagai histori — fondasi dashboard Grafana di <strong>Artikel #19</strong>. Lanjutkan Seri 2 di <a href="/artikel">halaman artikel</a> Koding Indonesia.</p>
HTML;
    }
}
