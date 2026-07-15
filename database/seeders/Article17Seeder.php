<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article17Seeder extends Seeder
{
    public function run(): void
    {
        $admin  = User::first();
        $netCat = Category::where('slug', 'networking')->first();

        if (! $admin || ! $netCat) {
            throw new \RuntimeException('User atau kategori tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'mqtt-tls-qos-lwt-retained-mosquitto-esp32';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $netCat->id,
                'title'           => 'MQTT Lanjutan: TLS, QoS, LWT & Retained Messages di Mosquitto + ESP32',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'MQTT TLS ESP32 — QoS, LWT & Retained Mosquitto',
                'seo_description' => 'Amankan broker Mosquitto dengan TLS port 8883, pahami QoS/LWT/retained, dan hubungkan ESP32 pakai WiFiClientSecure + sertifikat CA.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        Tag::updateOrCreate(['slug' => 'tls'], ['name' => 'tls']);

        $tagIds = Tag::whereIn('slug', [
            'mqtt', 'mosquitto', 'tls', 'esp32', 'iot', 'networking', 'linux',
        ])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command->info('✓ Artikel ke-17 berhasil dipublish: ' . $article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan</h2>
<p>Di <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">artikel Mosquitto pribadi (#16)</a>, broker kamu sudah pakai username/password — tapi koneksi masih <strong>plain MQTT</strong> di port <code>1883</code>. Di jaringan rumah (LAN) itu sering cukup; begitu broker diakses dari internet (VPS, port forwarding, atau subscriber cloud), password bisa disadap tanpa enkripsi.</p>

<p>Artikel ini melanjutkan <strong>Jalur B</strong> (infrastruktur &amp; data) Seri 2: aktifkan <strong>MQTT over TLS</strong> (port <code>8883</code>), lalu pelajari tiga fitur production MQTT yang sering terlewat — <strong>QoS</strong>, <strong>LWT (Last Will Testament)</strong>, dan <strong>retained messages</strong>. Sketch ESP32 diperbarui memakai <code>WiFiClientSecure</code> + sertifikat CA.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> Broker <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Mosquitto + auth (#16)</a> sudah jalan, paham dasar <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">MQTT publish/subscribe (#7)</a>. Familiar <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">NVS (#12)</a> membantu menyimpan host broker.</p>
</blockquote>

<h2>Yang Kamu Butuhkan</h2>
<ul>
  <li>Server broker yang sama seperti <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">#16</a> (Raspberry Pi atau VPS Ubuntu/Debian)</li>
  <li>Akses <code>sudo</code> + <strong>OpenSSL</strong> (biasanya sudah terpasang)</li>
  <li><strong>ESP32 DevKit</strong> + koneksi WiFi ke broker</li>
  <li>Arduino IDE + library <strong>PubSubClient</strong> (Nick O'Leary)</li>
  <li>Opsional: laptop dengan <code>mosquitto-clients</code> untuk uji TLS</li>
</ul>

<p><strong>Estimasi biaya:</strong> Rp 0 (self-signed CA untuk lab) — sertifikat Let's Encrypt opsional untuk domain publik (dijelaskan di Keamanan).</p>

<h2>Plain MQTT vs MQTT + TLS</h2>
<table>
  <thead>
    <tr><th>Aspek</th><th>Port 1883 (plain)</th><th>Port 8883 (TLS)</th></tr>
  </thead>
  <tbody>
    <tr><td>Enkripsi</td><td>Tidak ada</td><td>TLS — password &amp; payload terenkripsi</td></tr>
    <tr><td>Cocok untuk</td><td>LAN tepercaya, lab cepat</td><td>Internet, VPS, akses remote</td></tr>
    <tr><td>ESP32 client</td><td><code>WiFiClient</code></td><td><code>WiFiClientSecure</code> + CA</td></tr>
    <tr><td>Setup broker</td><td>Listener 1883 + <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">auth (#16)</a></td><td>Sertifikat + listener 8883</td></tr>
  </tbody>
</table>

<p>QoS dan LWT sudah diperkenalkan singkat di <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">artikel MQTT (#7)</a> — di sini kita terapkan ke Mosquitto dan firmware ESP32.</p>

<h2>Arsitektur: TLS + Fitur Production MQTT</h2>
<figure role="img" aria-label="Diagram arsitektur MQTT TLS: ESP32 dengan WiFiClientSecure terhubung ke Mosquitto via TLS port 8883, lalu ke subscriber CLI, Home Assistant/ESPHome/Node-RED, dan Python subscriber" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 620 440" style="display:block;max-width:620px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="tlsArr" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/></marker>
    <marker id="tlsArrO" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#FF7A2F"/></marker>
    <marker id="tlsArrG" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#2E7D32"/></marker>
  </defs>
  <rect x="0" y="0" width="620" height="440" fill="#F5F5F0" rx="6"/>
  <!-- ESP32 -->
  <rect x="170" y="15" width="280" height="80" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="310" y="42" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">ESP32</text>
  <text x="310" y="62" text-anchor="middle" fill="#4A5568" font-size="10">WiFiClientSecure + CA root</text>
  <text x="310" y="78" text-anchor="middle" fill="#4A5568" font-size="10">LWT + retained (opsional)</text>
  <!-- Lock icon label -->
  <rect x="470" y="25" width="130" height="40" rx="14" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2"/>
  <text x="535" y="42" text-anchor="middle" fill="#2E7D32" font-size="11" font-weight="700">TLS :8883</text>
  <text x="535" y="56" text-anchor="middle" fill="#4A5568" font-size="9">user/pass enkripsi</text>
  <!-- Arrow ESP32 → Mosquitto -->
  <line x1="310" y1="95" x2="310" y2="133" stroke="#FF7A2F" stroke-width="2.5" marker-end="url(#tlsArrO)"/>
  <text x="350" y="120" fill="#FF7A2F" font-size="10" font-weight="700">MQTT TLS ↓</text>
  <!-- Mosquitto -->
  <rect x="130" y="140" width="360" height="70" rx="6" fill="#2979FF" stroke="#000" stroke-width="2.5"/>
  <text x="310" y="170" text-anchor="middle" fill="#fff" font-size="14" font-weight="700">Mosquitto #16 + TLS</text>
  <text x="310" y="192" text-anchor="middle" fill="#e3f2fd" font-size="11">listener 8883 · sertifikat CA · auth</text>
  <!-- 3 output arrows -->
  <line x1="160" y1="210" x2="100" y2="268" stroke="#2E7D32" stroke-width="2" marker-end="url(#tlsArrG)"/>
  <line x1="310" y1="210" x2="310" y2="268" stroke="#2E7D32" stroke-width="2" marker-end="url(#tlsArrG)"/>
  <line x1="460" y1="210" x2="520" y2="268" stroke="#2E7D32" stroke-width="2" marker-end="url(#tlsArrG)"/>
  <!-- Output 1: Subscriber CLI -->
  <rect x="10" y="275" width="190" height="50" rx="6" fill="#FFF3E8" stroke="#000" stroke-width="2"/>
  <text x="105" y="296" text-anchor="middle" fill="#1a1a1a" font-size="11" font-weight="700">Subscriber CLI</text>
  <text x="105" y="312" text-anchor="middle" fill="#4A5568" font-size="9">mosquitto_sub --cafile</text>
  <!-- Output 2: HA / ESPHome / Node-RED -->
  <rect x="215" y="275" width="190" height="50" rx="6" fill="#FFF3E8" stroke="#000" stroke-width="2"/>
  <text x="310" y="296" text-anchor="middle" fill="#1a1a1a" font-size="11" font-weight="700">HA / ESPHome / Node-RED</text>
  <text x="310" y="312" text-anchor="middle" fill="#4A5568" font-size="9">#21 / #22 / #23 — TLS config</text>
  <!-- Output 3: Python subscriber -->
  <rect x="420" y="275" width="190" height="50" rx="6" fill="#FFF3E8" stroke="#000" stroke-width="2"/>
  <text x="515" y="296" text-anchor="middle" fill="#1a1a1a" font-size="11" font-weight="700">Python subscriber (#18)</text>
  <text x="515" y="312" text-anchor="middle" fill="#4A5568" font-size="9">paho-mqtt tls_set()</text>
  <!-- Summary -->
  <text x="310" y="360" text-anchor="middle" fill="#4A5568" font-size="11">ESP32 → MQTT TLS :8883 → Mosquitto → subscriber terenkripsi</text>
  <!-- Topics -->
  <rect x="100" y="380" width="420" height="45" rx="6" fill="#fff" stroke="#1a1a1a" stroke-width="1.5"/>
  <text x="310" y="398" text-anchor="middle" fill="#4A5568" font-size="9">Topics:</text>
  <text x="310" y="414" text-anchor="middle" fill="#1a1a1a" font-size="9.5" font-weight="600">data · .../dht22/data   |   status · .../status (LWT)   |   relay · .../lampu/kontrol</text>
</svg>
<figcaption style="margin-top:.75rem;font-size:.875rem;color:#4A5568;text-align:center">ESP32 terhubung via TLS port 8883 ke <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Mosquitto (#16)</a> — subscriber <a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">Python (#18)</a>, <a href="/artikel/home-assistant-integrasi-esp32-mqtt">HA (#21)</a>, <a href="/artikel/node-red-dashboard-otomasi-iot-mqtt-esp32">Node-RED (#23)</a> terenkripsi.</figcaption>
</figure>

<p><strong>Topic contoh</strong> (konsisten Seri 1):</p>
<ul>
  <li>Data: <code>kodingindonesia/esp32/dht22/data</code></li>
  <li>Status online: <code>kodingindonesia/esp32/status</code> — ideal untuk LWT</li>
  <li>Relay: <code>kodingindonesia/esp32/lampu/kontrol</code> — sama <a href="/artikel/kontrol-lampu-esp32-mqtt-relay">#8</a>/<a href="/artikel/sensor-gerak-pir-esp32-lampu-mqtt-debounce">#24</a></li>
</ul>

<h2>Langkah 1: Buat CA &amp; Sertifikat Server (OpenSSL)</h2>
<p>Di broker (SSH), buat folder sertifikat:</p>
<pre><code class="language-bash">sudo mkdir -p /etc/mosquitto/certs
cd /etc/mosquitto/certs</code></pre>

<p><strong>1. Certificate Authority (CA) sendiri</strong> — untuk lab &amp; LAN. Produksi publik pertimbangkan Let's Encrypt (lihat Keamanan).</p>
<pre><code class="language-bash">sudo openssl genrsa -out ca.key 2048
sudo openssl req -new -x509 -days 3650 -key ca.key -out ca.crt \
  -subj "/CN=KindoMQTT-CA"</code></pre>

<p><strong>2. Sertifikat server</strong> — <strong>CN harus cocok</strong> dengan host yang dipakai ESP32/sketch (<code>mqttHost</code>). Jika pakai IP, set CN ke IP; jika pakai hostname, set CN ke hostname yang sama:</p>
<pre><code class="language-bash"># Opsi A — ESP32 pakai IP (contoh sketch di bawah)
sudo openssl genrsa -out server.key 2048
sudo openssl req -new -key server.key -out server.csr \
  -subj "/CN=192.168.1.50"
sudo openssl x509 -req -in server.csr -CA ca.crt -CAkey ca.key -CAcreateserial \
  -out server.crt -days 825

# Opsional — jika verifikasi TLS gagal di client tertentu, sign ulang dengan SAN IP:
# echo "subjectAltName=IP:192.168.1.50" | sudo tee san.ext
# sudo openssl x509 -req -in server.csr -CA ca.crt -CAkey ca.key -CAcreateserial \
#   -out server.crt -days 825 -extfile san.ext

# Opsi B — ESP32 pakai hostname broker.lan (semua klien harus konsisten)
# sudo openssl req -new -key server.key -out server.csr -subj "/CN=broker.lan"</code></pre>

<pre><code class="language-bash">sudo chown mosquitto:mosquitto /etc/mosquitto/certs/*
sudo chmod 640 /etc/mosquitto/certs/server.key</code></pre>

<blockquote>
  <p><strong>Pro tip:</strong> Simpan <code>ca.crt</code> — file ini yang di-embed ke ESP32 sebagai <code>setCACert()</code>. Jangan commit <code>ca.key</code> atau <code>server.key</code> ke Git. Jika handshake TLS gagal padahal CN sudah benar, coba sign ulang dengan <strong>SubjectAltName</strong> (<code>subjectAltName=IP:...</code>) seperti contoh komentar di atas.</p>
</blockquote>

<h2>Langkah 2: Konfigurasi Mosquitto — Listener TLS 8883</h2>
<p>Tambahkan file <code>/etc/mosquitto/conf.d/tls.conf</code> (jangan hapus config auth dari <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">#16</a>):</p>
<pre><code class="language-ini">per_listener_settings true

listener 8883
certfile /etc/mosquitto/certs/server.crt
keyfile /etc/mosquitto/certs/server.key
cafile /etc/mosquitto/certs/ca.crt
require_certificate false
allow_anonymous false
password_file /etc/mosquitto/passwd</code></pre>

<p>Dengan <code>per_listener_settings true</code>, listener 1883 dari <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">#16</a> tetap bisa aktif untuk debugging LAN — sementara <code>8883</code> dipakai untuk koneksi TLS (ESP32 production / akses remote).</p>

<p>Restart &amp; cek log:</p>
<pre><code class="language-bash">sudo systemctl restart mosquitto
sudo systemctl status mosquitto
sudo tail -20 /var/log/mosquitto/mosquitto.log</code></pre>

<p>Firewall — buka <code>8883/tcp</code> (bukan hanya 1883) jika akses dari luar LAN:</p>
<pre><code class="language-bash">sudo ufw allow 8883/tcp
sudo ufw status</code></pre>

<h2>Langkah 3: Uji TLS dari Laptop</h2>
<p>Ganti host agar <strong>sama dengan CN sertifikat</strong> (IP atau hostname):</p>
<pre><code class="language-bash">mosquitto_sub -h 192.168.1.50 -p 8883 \
  --cafile /path/ke/ca.crt \
  -u kindo_esp32 -P 'GANTI_PASSWORD_MQTT' \
  -t "kodingindonesia/esp32/dht22/data" -v</code></pre>

<pre><code class="language-bash">mosquitto_pub -h 192.168.1.50 -p 8883 \
  --cafile /path/ke/ca.crt \
  -u kindo_esp32 -P 'GANTI_PASSWORD_MQTT' \
  -t "kodingindonesia/esp32/dht22/data" \
  -m '{"suhu":28.5,"kelembaban":62}'</code></pre>

<h2>QoS, LWT &amp; Retained — Kapan Pakai?</h2>
<table>
  <thead>
    <tr><th>Fitur</th><th>Fungsi</th><th>Contoh IoT</th></tr>
  </thead>
  <tbody>
    <tr><td><strong>QoS 0</strong></td><td>Kirim sekali, tanpa ACK</td><td>Data suhu tiap 5 detik — hilang 1 paket tidak masalah</td></tr>
    <tr><td><strong>QoS 1</strong></td><td>Minimal sekali sampai (ACK)</td><td>Perintah relay penting, alarm</td></tr>
    <tr><td><strong>QoS 2</strong></td><td>Tepat sekali sampai (4-way handshake)</td><td>Jarang dipakai di ESP32 — berat; CLI/broker tetap mendukung</td></tr>
    <tr><td><strong>LWT</strong></td><td>Pesan otomatis saat client disconnect mendadak</td><td><code>{"online":false}</code> di topic status</td></tr>
    <tr><td><strong>Retained</strong></td><td>Broker simpan pesan terakhir untuk subscriber baru</td><td>Status relay terakhir — hati-hati data basi</td></tr>
  </tbody>
</table>

<p><strong>CLI contoh QoS 1 + retained:</strong></p>
<pre><code class="language-bash">mosquitto_pub -h 192.168.1.50 -p 8883 --cafile ca.crt \
  -u kindo_esp32 -P 'GANTI_PASSWORD_MQTT' \
  -q 1 -r \
  -t "kodingindonesia/esp32/status" -m '{"online":true}'</code></pre>

<blockquote>
  <p><strong>Catatan PubSubClient:</strong> Library default publish <strong>QoS 0</strong>. Untuk mayoritas sensor periodik itu cukup (sama rekomendasi <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">#7</a>). QoS 1 di ESP32 butuh buffer lebih besar — prioritaskan TLS + LWT dulu.</p>
</blockquote>

<h2>Kode ESP32: WiFiClientSecure + LWT</h2>
<p>Ganti SSID, host broker, user/pass, dan paste isi <code>ca.crt</code> ke <code>root_ca</code>:</p>

<pre><code class="language-arduino">#include &lt;WiFi.h&gt;
#include &lt;WiFiClientSecure.h&gt;
#include &lt;PubSubClient.h&gt;
#include &lt;ArduinoJson.h&gt;

const char* ssid     = "NamaWiFiKamu";
const char* password = "PasswordWiFiKamu";

const char* mqttHost = "192.168.1.50";  // IP/hostname broker — harus cocok sertifikat
const int   mqttPort = 8883;
const char* mqttUser = "kindo_esp32";
const char* mqttPass = "GANTI_PASSWORD_MQTT";

const char* topicData   = "kodingindonesia/esp32/dht22/data";
const char* topicStatus = "kodingindonesia/esp32/status";

// Paste isi ca.crt (hanya bagian PEM, termasuk BEGIN/END)
static const char root_ca[] PROGMEM = R"EOF(
-----BEGIN CERTIFICATE-----
PASTE_ISI_CA_CRT_DI_SINI
-----END CERTIFICATE-----
)EOF";

const char* lwtPayload = "{\"online\":false}";

WiFiClientSecure espClient;
PubSubClient mqttClient(espClient);

unsigned long lastPublishMs = 0;

char clientId[24];

void buatClientIdUnik() {
  snprintf(clientId, sizeof(clientId), "ESP32-TLS-%06llX", ESP.getEfuseMac() &amp; 0xFFFFFF);
}

void publishStatusOnline() {
  const char* online = "{\"online\":true}";
  mqttClient.publish(topicStatus, online, true); // retained status
}

bool koneksiMQTT() {
  espClient.setCACert(root_ca);
  mqttClient.setServer(mqttHost, mqttPort);
  mqttClient.setBufferSize(512);
  buatClientIdUnik();

  uint8_t percobaan = 0;
  while (!mqttClient.connected() &amp;&amp; percobaan &lt; 5) {
    Serial.print("MQTT TLS connect...");
    if (mqttClient.connect(
          clientId,
          mqttUser,
          mqttPass,
          topicStatus,   // willTopic
          1,             // willQoS
          true,          // willRetain
          lwtPayload     // willMessage — offline jika disconnect mendadak
        )) {
      Serial.println(" OK");
      publishStatusOnline();
      return true;
    }
    Serial.print(" gagal, rc=");
    Serial.println(mqttClient.state());
    percobaan++;
    delay(5000);
  }
  return mqttClient.connected();
}

void setup() {
  Serial.begin(115200);
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi OK");
  koneksiMQTT();
}

void loop() {
  if (WiFi.status() != WL_CONNECTED) {
    WiFi.reconnect();
  }
  if (!mqttClient.connected()) {
    koneksiMQTT();
  }
  mqttClient.loop();

  if (millis() - lastPublishMs &gt; 5000) {
    lastPublishMs = millis();
    StaticJsonDocument&lt;96&gt; doc;
    doc["suhu"] = 28.0;
    doc["kelembaban"] = 60;
    char buffer[96];
    serializeJson(doc, buffer);
    if (mqttClient.publish(topicData, buffer)) {
      Serial.print("Publish ");
      Serial.println(buffer);
    }
  }
}</code></pre>

<h2>Penjelasan Bagian Kritis</h2>
<ol>
  <li><strong><code>WiFiClientSecure</code> + <code>setCACert()</code></strong> — ESP32 memverifikasi identitas server. Jangan pakai <code>setInsecure()</code> di produksi.</li>
  <li><strong>Port 8883</strong> — standar MQTT over TLS (bukan 1883).</li>
  <li><strong>LWT</strong> — parameter <code>connect()</code> ke-4 s/d ke-7: broker publish <code>{"online":false}</code> retained jika ESP32 mati/listrik putus tanpa disconnect bersih.</li>
  <li><strong>Retained status</strong> — <code>publish(..., true)</code> pada topic status; subscriber baru langsung tahu ESP32 online/offline.</li>
  <li><strong>CN sertifikat</strong> — hostname di sketch harus cocok dengan CN/SAN sertifikat server, atau verifikasi TLS gagal.</li>
  <li><strong>Client ID unik</strong> — <code>ESP.getEfuseMac()</code> agar beberapa ESP32 tidak saling kick di broker yang sama.</li>
  <li><strong>Max 5 percobaan connect</strong> — <code>koneksiMQTT()</code> tidak block <code>loop()</code> selamanya jika broker down.</li>
  <li><strong><code>mqttClient.loop()</code></strong> — wajib di <code>loop()</code> agar TLS session &amp; LWT handshake stabil.</li>
</ol>

<h2>Integrasi Home Assistant, ESPHome &amp; Node-RED</h2>
<p>Setelah broker TLS aktif, update konfigurasi MQTT integration:</p>
<ul>
  <li><strong><a href="/artikel/home-assistant-integrasi-esp32-mqtt">Home Assistant (#21)</a></strong> — broker port <code>8883</code>, centang SSL/TLS, upload <code>ca.crt</code> atau set <code>certificate: auto</code> untuk Let's Encrypt.</li>
  <li><strong><a href="/artikel/esphome-flash-esp32-tanpa-coding-arduino">ESPHome (#22)</a></strong> — di <code>mqtt:</code> YAML set <code>port: 8883</code> + paste isi <code>ca.crt</code> ke field <code>certificate</code> (self-signed) atau <code>certificate_authority</code>.</li>
  <li><strong><a href="/artikel/node-red-dashboard-otomasi-iot-mqtt-esp32">Node-RED (#23)</a></strong> — di node <strong>mqtt-broker</strong> config → port 8883, TLS on, CA file sama seperti laptop.</li>
</ul>

<p>Topic relay &amp; sensor dari artikel sebelumnya <strong>tidak berubah</strong> — hanya transport yang dienkripsi.</p>

<h2>Uji Coba (Checklist)</h2>
<ol>
  <li>Broker <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">#16</a> masih jalan + auth OK di port 1883 (LAN)</li>
  <li>Generate CA + server cert → restart Mosquitto tanpa error log</li>
  <li><code>mosquitto_sub</code> dengan <code>--cafile</code> di port 8883 menerima pesan</li>
  <li>Upload sketch ESP32 → Serial: <code>MQTT TLS connect... OK</code></li>
  <li>Cabut USB ESP32 mendadak → subscriber harus terima LWT <code>{"online":false}</code> di topic status</li>
  <li>Colokkan lagi → status retained kembali <code>{"online":true}</code></li>
  <li>Verifikasi <strong>CN sertifikat = mqttHost</strong> di sketch (IP atau hostname sama persis)</li>
  <li>Coba publish QoS 1 dari CLI (<code>-q 1</code>) — bandingkan dengan QoS 0</li>
  <li>Pastikan <strong>tidak</strong> expose port 1883 ke internet jika sudah pakai 8883</li>
</ol>

<h2>Tips &amp; Troubleshooting</h2>
<ul>
  <li><strong>TLS handshake failed / rc=-2:</strong> CN/SAN sertifikat tidak cocok host — regenerate CSR atau sign ulang dengan <code>subjectAltName=IP:...</code></li>
  <li><strong>Certificate verify failed:</strong> <code>root_ca</code> di sketch bukan CA yang sama dengan yang sign server cert</li>
  <li><strong>Connection refused 8883:</strong> Listener belum aktif atau firewall block — cek <code>ss -tlnp | grep 8883</code></li>
  <li><strong>rc=5 Not authorized:</strong> Sama seperti <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">#16</a> — user/password salah</li>
  <li><strong>Config error setelah tambah TLS:</strong> Cek konflik <code>/etc/mosquitto/conf.d/</code> — sama seperti <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">#16</a></li>
  <li><strong>LWT tidak muncul:</strong> Pastikan disconnect mendadak (bukan <code>disconnect()</code> bersih); keepalive PubSubClient default 15 detik — tunggu ~1,5× keepalive</li>
  <li><strong>Retained data basi:</strong> Hapus dengan publish payload kosong + retain: <code>mosquitto_pub ... -t topic -n -r</code></li>
  <li><strong>ESP32 connect lalu langsung disconnect:</strong> Client ID bentrok — pastikan tiap board punya ID unik (lihat <code>buatClientIdUnik()</code> di sketch)</li>
  <li><strong>WiFi 2.4 GHz:</strong> ESP32 tidak support jaringan 5 GHz saja</li>
</ul>

<h2>Keamanan &amp; Produksi</h2>
<ul>
  <li><strong>Self-signed CA</strong> cocok lab/LAN — untuk domain publik pertimbangkan <strong>Let's Encrypt</strong> + reverse proxy atau certbot</li>
  <li>Tutup port <code>1883</code> dari internet setelah TLS aktif — hanya <code>8883</code> atau VPN</li>
  <li>Jangan commit <code>*.key</code> atau password ke Git — simpan di NVS (<a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">#12</a>)</li>
  <li>Pertimbangkan <strong>ACL Mosquitto</strong> agar user ESP32 hanya publish/subscribe topic tertentu</li>
  <li>HTTPS client di ESP32 (bukan MQTT) → lihat <a href="/artikel/https-sertifikat-esp32-wificlientsecure-api-rest">pelengkap HTTPS (#38)</a></li>
</ul>

<h2>Langkah Selanjutnya (Seri 2)</h2>
<ul>
  <li><strong><a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">Subscriber Python (#18)</a></strong> + <code>tls_set()</code> → simpan data MQTT ke MySQL</li>
  <li><strong><a href="/artikel/ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt">NTP &amp; timestamp (#34)</a></strong> — timestamp akurat sebelum histori database</li>
  <li><strong><a href="/artikel/home-assistant-integrasi-esp32-mqtt">Home Assistant (#21)</a></strong> — aktifkan TLS di integration broker</li>
  <li><strong><a href="/artikel/sensor-gerak-pir-esp32-lampu-mqtt-debounce">PIR + lampu MQTT (#24)</a></strong> — upgrade sketch ke TLS untuk deploy remote</li>
  <li><strong><a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">InfluxDB + Grafana (#19)</a></strong> — dashboard histori sensor</li>
  <li><strong><a href="/artikel/smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt">Capstone Greenhouse (#39)</a></strong> — proyek akhir Seri 2 dengan TLS-ready</li>
</ul>

<p>Dengan TLS, QoS, LWT, dan retained, stack MQTT kamu siap naik level dari lab LAN ke deploy yang lebih aman. Lanjutkan Seri 2 di <a href="/artikel">halaman artikel</a> Koding Indonesia.</p>
HTML;
    }
}
