<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article16Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $netCat = Category::where('slug', 'networking')->first();

        if (! $admin || ! $netCat) {
            throw new \RuntimeException('User atau kategori tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $netCat->id,
                'title'           => 'Broker Mosquitto Pribadi di Raspberry Pi / VPS + Autentikasi ESP32',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'Mosquitto Pribadi + Auth — Broker MQTT untuk ESP32',
                'seo_description' => 'Pasang broker Mosquitto sendiri di Raspberry Pi atau VPS: autentikasi user/password, firewall, dan hubungkan ESP32 lewat NVS tanpa hardcode kredensial.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        Tag::updateOrCreate(['slug' => 'mosquitto'], ['name' => 'mosquitto']);
        Tag::updateOrCreate(['slug' => 'linux'], ['name' => 'linux']);
        Tag::updateOrCreate(['slug' => 'networking'], ['name' => 'networking']);

        $tagSlugs = ['mqtt', 'mosquitto', 'iot', 'esp32', 'networking', 'linux'];
        $tagIds   = Tag::whereIn('slug', $tagSlugs)->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command->info('✓ Artikel ke-16 berhasil dipublish: ' . $article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan</h2>
<p>Di Seri 1 dan awal Seri 2, ESP32 kamu mengirim data sensor ke <code>test.mosquitto.org</code> — broker MQTT publik tanpa password. Itu bagus untuk belajar, tapi <strong>tidak layak produksi</strong>: siapa saja bisa subscribe topic kamu, data bisa dibaca orang lain, dan broker publik bisa down tanpa pemberitahuan.</p>

<p>Di <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">artikel NVS + WiFiManager (#12)</a> kita sudah menyiapkan firmware tanpa hardcode WiFi. Artikel ini melengkapi langkah berikutnya: <strong>broker MQTT pribadi</strong> dengan <strong>autentikasi username/password</strong>, lalu hubungkan ESP32 dengan pola NVS yang sama.</p>

<p>Ini adalah <strong>artikel pembuka Jalur B</strong> (infrastruktur &amp; data) di Seri 2 — fondasi sebelum Python subscriber (#18), Grafana (#19), dan Home Assistant (#21).</p>

<blockquote>
  <p><strong>Prasyarat:</strong> Paham dasar <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">MQTT &amp; publish ESP32 (#7)</a>. Familiar dengan <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">NVS + WiFiManager (#12)</a> sangat membantu untuk bagian firmware. Wiring <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">DHT22</a> mengikuti Seri 1 (GPIO 4).</p>
</blockquote>

<h2>Yang Kamu Butuhkan</h2>
<ul>
  <li><strong>Server broker:</strong> Raspberry Pi (LAN) <em>atau</em> VPS Ubuntu/Debian dengan SSH</li>
  <li><strong>Laptop/PC</strong> untuk install Mosquitto dan uji <code>mosquitto_sub</code></li>
  <li><strong>ESP32 DevKit + DHT22</strong> (opsional tapi disarankan untuk uji end-to-end)</li>
  <li>Akun SSH dengan <code>sudo</code> di mesin broker</li>
  <li>Opsional: <strong>MQTT Explorer</strong> di laptop untuk inspeksi topic visual</li>
</ul>

<p>Di <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">artikel MQTT (#7)</a> kita menjanjikan broker Mosquitto sendiri untuk production — artikel ini memenuhi janji itu. Di <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">artikel #12</a> kamu sudah disarankan pindah dari <code>test.mosquitto.org</code>; sekarang kita pasang brokernya.</p>

<h2>Broker Publik vs Broker Pribadi</h2>
<table>
  <thead>
    <tr><th>Aspek</th><th><code>test.mosquitto.org</code></th><th>Mosquitto pribadi</th></tr>
  </thead>
  <tbody>
    <tr><td>Autentikasi</td><td>Tidak ada</td><td>Username + password wajib</td></tr>
    <tr><td>Privasi data</td><td>Topic bisa dibaca siapa saja</td><td>Hanya user yang diizinkan</td></tr>
    <tr><td>Kontrol</td><td>Terbatas</td><td>Full control config &amp; ACL</td></tr>
    <tr><td>Biaya</td><td>Gratis</td><td>Gratis (Pi di rumah) atau VPS ~50rb/bln</td></tr>
    <tr><td>Cocok untuk</td><td>Belajar, uji coba</td><td>Produksi, smart home, klien</td></tr>
  </tbody>
</table>

<p>Dari artikel ini ke depan (roadmap Seri 2), proyek production disarankan memakai <strong>broker sendiri</strong>. Artikel #11–#15 masih boleh pakai broker publik saat belajar hardware.</p>

<h2>Arsitektur Sistem</h2>
<table>
  <thead>
    <tr><th>Komponen</th><th>Peran</th><th>Koneksi</th></tr>
  </thead>
  <tbody>
    <tr><td><strong>ESP32</strong></td><td>Publisher (kirim data sensor)</td><td>WiFi/LAN → MQTT <code>:1883</code> + auth</td></tr>
    <tr><td><strong>Mosquitto</strong></td><td>Broker pusat</td><td>Raspberry Pi (LAN) atau VPS</td></tr>
    <tr><td><strong>mosquitto_sub</strong></td><td>Subscriber CLI</td><td>Laptop → broker (uji coba)</td></tr>
    <tr><td><strong>MQTT Explorer</strong></td><td>Subscriber GUI</td><td>Laptop / HP → broker</td></tr>
    <tr><td><strong>Python</strong> (#18)</td><td>Subscriber + simpan data</td><td>Lanjutan Jalur B → MySQL</td></tr>
  </tbody>
</table>

<p>Alur data secara singkat:</p>
<pre><code>  [ ESP32 ]
      |
      |  WiFi/LAN  ·  MQTT :1883  ·  username + password
      v
  [ Mosquitto @ Pi / VPS ]
      |
      +-- mosquitto_sub  (laptop)
      +-- MQTT Explorer  (HP)
      +-- Python → MySQL (artikel #18, nanti)</code></pre>

<p><strong>Topic sensor</strong> tetap mengikuti konvensi Seri 1: <code>kodingindonesia/esp32/dht22/data</code> dengan payload JSON <code>{"suhu":28.5,"kelembaban":65.2}</code>.</p>

<h2>Pilih Platform: Raspberry Pi vs VPS</h2>
<table>
  <thead>
    <tr><th>Opsi</th><th>Kelebihan</th><th>Kekurangan</th></tr>
  </thead>
  <tbody>
    <tr><td><strong>Raspberry Pi</strong> di rumah</td><td>Gratis setelah beli hardware; latency rendah di LAN</td><td>Butuh IP statis/DDNS jika diakses dari luar; listrik 24/7</td></tr>
    <tr><td><strong>VPS</strong> (Ubuntu)</td><td>IP publik tetap; akses dari mana saja</td><td>Biaya bulanan; latency lebih tinggi dari LAN</td></tr>
  </tbody>
</table>

<p>Perintah di bawah memakai <strong>Ubuntu / Debian</strong> (cocok untuk VPS dan Raspberry Pi OS). Login via SSH sebagai user dengan akses <code>sudo</code>.</p>

<blockquote>
  <p><strong>Contoh IP di artikel ini:</strong> <code>192.168.1.50</code> = Mosquitto di jaringan lokal (Pi). Ganti dengan IP VPS atau hostname kamu, misalnya <code>mqtt.rumahku.ddns.net</code>.</p>
</blockquote>

<h2>Install Mosquitto</h2>
<p>Update paket dan install Mosquitto broker + client tools:</p>

<pre><code class="language-bash">sudo apt update
sudo apt install -y mosquitto mosquitto-clients
sudo systemctl enable mosquitto
sudo systemctl start mosquitto
sudo systemctl status mosquitto --no-pager</code></pre>

<p>Pastikan status <code>active (running)</code>. Versi Mosquitto 2.x default di Ubuntu 22.04+ sudah cukup untuk tutorial ini.</p>

<h2>Konfigurasi &amp; Autentikasi</h2>
<p>Buat file password untuk user MQTT (contoh user <code>kindo_esp32</code>):</p>

<pre><code class="language-bash">sudo mosquitto_passwd -c /etc/mosquitto/passwd kindo_esp32
# masukkan password kuat, misalnya: KindoMQTT2026!</code></pre>

<p>Edit konfigurasi utama:</p>

<pre><code class="language-bash">sudo nano /etc/mosquitto/mosquitto.conf</code></pre>

<p>Tambahkan (atau pastikan ada) baris berikut:</p>

<pre><code>per_listener_settings true

listener 1883
allow_anonymous false
password_file /etc/mosquitto/passwd

persistence true
persistence_location /var/lib/mosquitto/

log_dest file /var/log/mosquitto/mosquitto.log
log_type error
log_type warning
log_type notice</code></pre>

<p>Restart broker:</p>

<pre><code class="language-bash">sudo systemctl restart mosquitto
sudo systemctl status mosquitto --no-pager</code></pre>

<blockquote>
  <p><strong>Mosquitto 2.x — konflik config:</strong> Ubuntu sering punya file default di <code>/etc/mosquitto/conf.d/</code> (misalnya <code>mosquitto.conf</code> atau <code>default.conf</code>) yang masih <code>allow_anonymous true</code>. Jika auth tidak jalan, rename file tersebut (<code>sudo mv ... ...bak</code>) lalu restart — atau pastikan hanya satu <code>listener 1883</code> aktif dengan <code>allow_anonymous false</code>.</p>
</blockquote>

<blockquote>
  <p><strong>User MQTT tambahan:</strong> Untuk user kedua, jangan pakai flag <code>-c</code> (itu menghapus file lama). Gunakan: <code>sudo mosquitto_passwd /etc/mosquitto/passwd kindo_laptop</code></p>
</blockquote>

<blockquote>
  <p><strong>Broker bukan website:</strong> IP broker tidak dibuka di browser Chrome. MQTT berjalan di port <strong>1883</strong>. Sama seperti penjelasan di <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">artikel MQTT</a>.</p>
</blockquote>

<h2>Firewall &amp; Akses Jaringan</h2>
<p><strong>Di LAN (Raspberry Pi):</strong> ESP32 dan laptop harus satu jaringan WiFi dengan Pi. Tidak perlu buka port ke internet jika hanya dipakai di rumah.</p>

<p><strong>Di VPS (akses dari internet):</strong> buka port 1883 — tapi ingat artikel ini belum pakai TLS (plain MQTT). Untuk production internet, lanjut ke <strong>artikel #17</strong> (MQTT + TLS).</p>

<pre><code class="language-bash"># VPS dengan UFW (opsional, hati-hati di production)
sudo ufw allow 1883/tcp
sudo ufw status</code></pre>

<ul>
  <li>Pastikan ESP32 bisa <code>ping</code> IP broker (LAN) atau resolve hostname (VPS)</li>
  <li>Router rumah: port forwarding 1883 hanya jika benar-benar perlu akses luar — prefer VPN atau TLS (#17)</li>
</ul>

<h2>Uji Coba dari Laptop</h2>
<p>Terminal 1 — subscribe (ganti IP, user, password):</p>

<pre><code class="language-bash">mosquitto_sub -h 192.168.1.50 -p 1883 \
  -u kindo_esp32 -P 'KindoMQTT2026!' \
  -t "kodingindonesia/esp32/dht22/data" -v</code></pre>

<p>Terminal 2 — publish manual:</p>

<pre><code class="language-bash">mosquitto_pub -h 192.168.1.50 -p 1883 \
  -u kindo_esp32 -P 'KindoMQTT2026!' \
  -t "kodingindonesia/esp32/dht22/data" \
  -m '{"suhu":25.0,"kelembaban":60.0}'</code></pre>

<p>Jika Terminal 1 menampilkan payload, broker + autentikasi sudah benar. Alternatif GUI: buka <strong>MQTT Explorer</strong>, connect ke IP broker dengan username/password yang sama, subscribe topic di atas.</p>

<blockquote>
  <p><strong>Pro tip:</strong> Buat user MQTT terpisah per perangkat (<code>kindo_esp32_ruangtamu</code>, <code>kindo_esp32_kebun</code>) agar lebih mudah audit dan revoke.</p>
</blockquote>

<h2>ESP32: Koneksi ke Broker + Auth (NVS)</h2>
<p>Perluas pola <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">WiFiManager + NVS (#12)</a>: simpan <code>mqtt_host</code>, <code>mqtt_user</code>, <code>mqtt_pass</code> di flash — tidak di sketch. Field custom di portal WiFiManager mengisi nilai awal saat provisioning.</p>

<p><strong>Library</strong> (sama #12): WiFiManager (tzapu), PubSubClient (Nick O'Leary), DHT Adafruit + Unified Sensor. Board <strong>esp32</strong> v3.x.</p>

<p>Sketch ringkas (DHT22 GPIO 4, topic Seri 1, AP portal <code>KindoESP32-Setup</code>):</p>

<pre><code class="language-arduino">#include &lt;WiFi.h&gt;
#include &lt;WiFiManager.h&gt;
#include &lt;Preferences.h&gt;
#include &lt;PubSubClient.h&gt;
#include &lt;DHT.h&gt;

#define DHT_PIN  4
#define DHT_TYPE DHT22
const char* NS_KINDO = "kindo";
const int MQTT_PORT = 1883;

DHT dht(DHT_PIN, DHT_TYPE);
WiFiClient espClient;
PubSubClient mqttClient(espClient);
Preferences prefs;

String mqttHost, mqttUser, mqttPass, topicSensor;

WiFiManagerParameter pHost("mqtt_host", "MQTT broker IP/hostname", "192.168.1.50", 64);
WiFiManagerParameter pUser("mqtt_user", "MQTT username", "kindo_esp32", 32);
WiFiManagerParameter pPass("mqtt_pass", "MQTT password", "", 48);
WiFiManagerParameter pTopic("mqtt_topic", "MQTT topic", "kodingindonesia/esp32/dht22/data", 64);

void muatMqttDariNvs() {
  prefs.begin(NS_KINDO, true);
  mqttHost   = prefs.getString("mqtt_host", "192.168.1.50");
  mqttUser   = prefs.getString("mqtt_user", "kindo_esp32");
  mqttPass   = prefs.getString("mqtt_pass", "");
  topicSensor = prefs.getString("mqtt_topic", "kodingindonesia/esp32/dht22/data");
  prefs.end();
}

void simpanMqttKeNvs() {
  prefs.begin(NS_KINDO, false);
  prefs.putString("mqtt_host", pHost.getValue());
  prefs.putString("mqtt_user", pUser.getValue());
  prefs.putString("mqtt_pass", pPass.getValue());
  prefs.putString("mqtt_topic", pTopic.getValue());
  prefs.end();
}

bool setupWiFiManager() {
  WiFiManager wm;
  wm.setConfigPortalTimeout(180);
  wm.addParameter(&amp;pHost);
  wm.addParameter(&amp;pUser);
  wm.addParameter(&amp;pPass);
  wm.addParameter(&amp;pTopic);

  muatMqttDariNvs();
  pHost.setValue(mqttHost.c_str(), 64);
  pUser.setValue(mqttUser.c_str(), 32);
  pPass.setValue(mqttPass.c_str(), 48);
  pTopic.setValue(topicSensor.c_str(), 64);

  if (!wm.autoConnect("KindoESP32-Setup")) return false;
  simpanMqttKeNvs();
  return true;
}

bool koneksiMQTT() {
  mqttClient.setServer(mqttHost.c_str(), MQTT_PORT);
  mqttClient.setBufferSize(512);
  String clientId = "ESP32-MQTT-" + String(random(0xffff), HEX);
  if (mqttClient.connect(clientId.c_str(), mqttUser.c_str(), mqttPass.c_str())) {
    Serial.println("MQTT terhubung (auth)");
    return true;
  }
  Serial.print("MQTT gagal, rc=");
  Serial.println(mqttClient.state());
  return false;
}

void publishDHT() {
  float suhu = dht.readTemperature();
  float kelembaban = dht.readHumidity();
  if (isnan(suhu) || isnan(kelembaban)) return;

  char payload[96];
  snprintf(payload, sizeof(payload), "{\"suhu\":%.1f,\"kelembaban\":%.1f}", suhu, kelembaban);
  mqttClient.loop();
  if (mqttClient.publish(topicSensor.c_str(), payload, false)) {
    Serial.print("Publish OK → ");
    Serial.println(payload);
  } else {
    Serial.println("Publish gagal");
  }
}

void setup() {
  Serial.begin(115200);
  dht.begin();
  delay(2000);
  if (!setupWiFiManager()) ESP.restart();
  if (!koneksiMQTT()) ESP.restart();
  publishDHT();
}

void loop() {
  mqttClient.loop();
  delay(10000);
  publishDHT();
}</code></pre>

<h2>Uji Coba ESP32 (End-to-End)</h2>
<ol>
  <li>Upload sketch, buka Serial Monitor <strong>115200</strong></li>
  <li>Portal <code>KindoESP32-Setup</code> — isi WiFi rumah + <em>MQTT broker IP</em>, <em>username</em>, <em>password</em> (sama seperti di <code>mosquitto_passwd</code>)</li>
  <li>Serial: <code>MQTT terhubung (auth)</code> → <code>Publish OK</code> dengan JSON suhu/kelembaban</li>
  <li>Di laptop, jalankan <code>mosquitto_sub</code> ke broker yang sama — harus muncul payload dari ESP32</li>
  <li>Reboot ESP32 — harus connect tanpa portal (kredensial WiFi + MQTT sudah di NVS)</li>
</ol>

<blockquote>
  <p><strong>Pro tip topic:</strong> Gunakan topic unik per perangkat, misalnya <code>kodingindonesia/anton/esp32/dht22/data</code>, agar tidak bentrok jika banyak unit di satu broker.</p>
</blockquote>

<h2>Penjelasan Bagian Kritis</h2>
<ul>
  <li><strong><code>allow_anonymous false</code></strong> — broker menolak koneksi tanpa user/password</li>
  <li><strong><code>mqttClient.connect(..., user, pass)</code></strong> — overload PubSubClient dengan autentikasi</li>
  <li><strong><code>WiFiManagerParameter</code></strong> — provisioning host/user/pass lewat portal HP, lalu disimpan NVS</li>
  <li><strong><code>mqttClient.loop()</code></strong> — wajib sebelum <code>publish()</code> (konsisten Seri 1)</li>
  <li><strong>Port 1883</strong> — plain MQTT; untuk internet publik gunakan TLS port 8883 di artikel #17</li>
</ul>

<h2>Tips &amp; Troubleshooting</h2>
<ul>
  <li><strong>Connection refused (rc=-2):</strong> Mosquitto tidak jalan — cek <code>systemctl status mosquitto</code></li>
  <li><strong>Not authorized (rc=5):</strong> Username/password salah — ulangi <code>mosquitto_passwd</code></li>
  <li><strong>ESP32 tidak connect ke Pi:</strong> Pastikan satu subnet WiFi 2.4 GHz; cek IP Pi dengan <code>hostname -I</code></li>
  <li><strong>VPS tidak bisa diakses:</strong> Cek firewall cloud provider + UFW; port 1883 harus terbuka</li>
  <li><strong>Config error saat restart:</strong> Cek log <code>sudo tail -20 /var/log/mosquitto/mosquitto.log</code></li>
  <li><strong>Publish OK di laptop, ESP32 gagal:</strong> Cek field portal — password MQTT kosong di NVS</li>
  <li><strong>DHT22 NaN:</strong> <code>delay(2000)</code> setelah <code>dht.begin()</code>; GPIO 4 + pull-up — sama seperti <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">tutorial DHT22</a></li>
  <li><strong>Auth gagal setelah edit config:</strong> Cek konflik file di <code>/etc/mosquitto/conf.d/</code> (lihat catatan Mosquitto 2.x di atas)</li>
</ul>

<h2>Keamanan &amp; Produksi</h2>
<ul>
  <li>Jangan commit password MQTT ke GitHub — simpan di NVS / portal seperti WiFi</li>
  <li>Plain MQTT (port 1883) di internet = password terkirim tanpa enkripsi → wajib <strong>TLS (#17)</strong> untuk deploy luar LAN</li>
  <li>Pertimbangkan ACL Mosquitto agar user ESP32 hanya bisa publish ke topic tertentu</li>
  <li>Backup file <code>/etc/mosquitto/passwd</code> secara aman</li>
</ul>

<h2>Langkah Selanjutnya (Seri 2)</h2>
<ul>
  <li><strong>Artikel #17:</strong> <strong>MQTT TLS</strong>, QoS, LWT &amp; retained messages — amankan broker di internet</li>
  <li><strong>Artikel #18:</strong> <strong>Subscriber Python</strong> → simpan data MQTT ke MySQL</li>
  <li><strong>Artikel #21:</strong> <strong>Home Assistant</strong> — integrasi ESP32 via broker pribadi</li>
  <li><strong><a href="/artikel/i2c-esp32-sensor-bme280-suhu-tekanan-mqtt">Sensor BME280 via I2C</a></strong> — publish suhu, kelembaban &amp; tekanan ke broker pribadi kamu</li>
  <li><strong><a href="/artikel/oled-ssd1306-esp32-tampilkan-data-sensor-i2c">OLED SSD1306</a></strong> — panel lokal untuk data BME280 di bus I2C</li>
  <li>Kembali ke <a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">node deep sleep (#11)</a> + <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">NVS (#12)</a> untuk stack lapangan lengkap</li>
</ul>

<p>Dengan broker Mosquitto pribadi, kamu punya fondasi infrastruktur IoT sendiri — tidak lagi bergantung pada broker publik. Lanjutkan Seri 2 di <a href="/artikel">halaman artikel</a> Koding Indonesia.</p>
HTML;
    }
}
