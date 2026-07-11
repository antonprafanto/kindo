<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article38Seeder extends Seeder
{
    public function run(): void
    {
        $admin  = User::first();
        $netCat = Category::where('slug', 'networking')->first();

        if (! $admin || ! $netCat) {
            throw new \RuntimeException('User atau kategori tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'https-sertifikat-esp32-wificlientsecure-api-rest';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        Tag::updateOrCreate(['slug' => 'https'], ['name' => 'https']);

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $netCat->id,
                'title'           => 'Keamanan Dasar: HTTPS & Sertifikat di ESP32 (WiFiClientSecure + REST API)',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'HTTPS ESP32 WiFiClientSecure — REST API TLS & Sertifikat CA',
                'seo_description' => 'Tutorial HTTPS di ESP32: WiFiClientSecure + HTTPClient, embed root CA, setCACert untuk GET/POST REST API — pelengkap MQTT TLS artikel #17.',
            ]
        );

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', [
            'esp32', 'tls', 'networking', 'iot', 'wifi', 'https',
        ])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command->info('✓ Artikel ke-38 berhasil dipublish: ' . $article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — Dari HTTP ke HTTPS di ESP32</h2>
<p>Seri 1 sudah mengajarkan <a href="/artikel/menghubungkan-esp32-wifi-kirim-data-server">WiFi (#4)</a>, <a href="/artikel/membuat-web-server-esp32-monitoring-sensor-dht22">web server lokal (#6)</a>, dan <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">MQTT (#7)</a>. Di Tier 2, <a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">MQTT TLS (#17)</a> mengamankan koneksi ke broker <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Mosquitto (#16)</a> di jaringan lokal — misalnya <code>192.168.1.50</code>.</p>

<p>Artikel <strong>Tier 2</strong> ini fokus pada skenario berbeda: ESP32 memanggil <strong>REST API publik</strong> lewat <strong>HTTPS</strong> — webhook, layanan cloud, atau backend VPS di internet. Kita pakai <code>WiFiClientSecure</code> + <code>HTTPClient</code>, bukan protokol MQTT. Ini melengkapi <a href="/artikel/rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32">REST vs MQTT (#20)</a>, <a href="/artikel/esp32-firebase-realtime-database-sensor-cloud">Firebase (#30)</a>, dan persiapan capstone <strong>greenhouse (#39)</strong>.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> Paham <a href="/artikel/blink-led-esp32-tutorial-pertama-embedded-system">GPIO (#3)</a>, <a href="/artikel/menghubungkan-esp32-wifi-kirim-data-server">WiFi (#4)</a>, <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">DHT22 (#5)</a>, dan idealnya sudah baca <a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">MQTT TLS (#17)</a> agar tidak bingung antara TLS untuk broker vs TLS untuk HTTP.</p>
</blockquote>

<h2>HTTPS vs HTTP — Mengapa Harus Terenkripsi?</h2>
<p><strong>HTTP</strong> mengirim data sensor (suhu, lokasi, token API) dalam bentuk teks biasa — siapa pun di jaringan WiFi publik atau ISP bisa membaca paket. <strong>HTTPS</strong> membungkus HTTP di dalam <strong>TLS</strong>: server membuktikan identitas lewat sertifikat, lalu saluran terenkripsi.</p>
<table>
  <thead>
    <tr><th>Aspek</th><th>HTTP</th><th>HTTPS</th></tr>
  </thead>
  <tbody>
    <tr><td>Port default</td><td>80</td><td>443</td></tr>
    <tr><td>Enkripsi</td><td>Tidak</td><td>TLS 1.2+</td></tr>
    <tr><td>Verifikasi server</td><td>Tidak</td><td>Root CA</td></tr>
    <tr><td>Cocok untuk API cloud</td><td>❌</td><td>✅</td></tr>
    <tr><td>Web server ESP32 lokal (#6)</td><td>✅ LAN</td><td>Opsional</td></tr>
  </tbody>
</table>

<h2>WiFiClientSecure vs WiFiClient Biasa</h2>
<p>Library <code>HTTPClient</code> Arduino bisa memakai <code>WiFiClient</code> untuk URL <code>http://</code> atau <code>WiFiClientSecure</code> untuk <code>https://</code>. Kelas secure menambahkan handshake TLS di atas TCP — mirip konsep di MQTT TLS (#17), tapi stack aplikasinya HTTP, bukan publish/subscribe.</p>
<ul>
  <li><code>WiFiClient</code> — cukup untuk <a href="/artikel/membuat-web-server-esp32-monitoring-sensor-dht22">web server lokal (#6)</a> di LAN</li>
  <li><code>WiFiClientSecure</code> — wajib untuk API eksternal, Firebase (#30), webhook produksi</li>
</ul>

<blockquote>
  <p><strong>Pro tip:</strong> Artikel ini <strong>tidak membutuhkan <code>SPI.h</code></strong> — tidak ada SD Card atau periferal SPI seperti di <a href="/artikel/sd-card-spi-esp32-logging-data-sensor-offline">logging SD (#37)</a>. Cukup WiFi + sensor DHT22 (#5).</p>
</blockquote>

<h2>Perbedaan dengan MQTT TLS (#17)</h2>
<table>
  <thead>
    <tr><th>Topik</th><th>MQTT TLS (#17)</th><th>HTTPS REST (artikel ini)</th></tr>
  </thead>
  <tbody>
    <tr><td>Protokol</td><td>MQTT publish/subscribe</td><td>HTTP GET/POST request-response</td></tr>
    <tr><td>Library utama</td><td>PubSubClient + WiFiClientSecure</td><td>HTTPClient + WiFiClientSecure</td></tr>
    <tr><td>Port umum</td><td>8883 (broker)</td><td>443 (web API)</td></tr>
    <tr><td>Target tipikal</td><td>Broker <code>192.168.1.50</code></td><td>Domain publik <code>api.example.com</code></td></tr>
    <tr><td>Pola data</td><td>Topic + payload berkelanjutan</td><td>Endpoint + JSON sekali kirim</td></tr>
  </tbody>
</table>
<p>Keduanya memakai <code>setCACert()</code> untuk verifikasi server — konsep sertifikat sama, konteks aplikasi berbeda. Baca <a href="/artikel/rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32">#20</a> untuk memilih arsitektur proyek.</p>

<h2>HTTPClient + WiFiClientSecure — Pola Dasar</h2>
<p>Alur standar di firmware ESP32:</p>
<ol>
  <li>Connect WiFi dengan placeholder <code>GANTI_NAMA_WIFI</code> / <code>GANTI_PASSWORD_WIFI</code></li>
  <li>Instansiasi <code>WiFiClientSecure client</code></li>
  <li>Panggil <code>client.setCACert(root_ca)</code> dengan sertifikat CA yang di-embed</li>
  <li><code>http.begin(client, url)</code> — pass client secure ke HTTPClient</li>
  <li><code>GET</code> atau <code>POST</code> → baca <code>http.getString()</code> atau status code</li>
  <li><code>http.end()</code> setiap selesai</li>
</ol>

<h2>Prasyarat &amp; Koneksi WiFi</h2>
<p>Sama seperti seluruh seri — jangan hardcode SSID/password di repo publik:</p>
<pre><code class="language-cpp">#include &lt;WiFi.h&gt;

const char* WIFI_SSID = "GANTI_NAMA_WIFI";
const char* WIFI_PASS = "GANTI_PASSWORD_WIFI";

void connectWiFi() {
  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASS);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi OK");
}
</code></pre>
<p>Untuk deploy lapangan, simpan kredensial di <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">NVS (#12)</a> — pola yang sama dipakai di <a href="/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22">capstone #10</a>.</p>

<h2>Mendapatkan Root CA Certificate</h2>
<p>Server HTTPS mempresentasikan rantai sertifikat; ESP32 perlu <strong>root CA</strong> (Certificate Authority) tepercaya untuk memverifikasi bahwa Anda benar-benar berbicara dengan server yang dimaksud — bukan penyerang man-in-the-middle.</p>
<p>Cara umum mengambil root CA (contoh untuk Let's Encrypt / ISRG):</p>
<pre><code class="language-text">openssl s_client -showcerts -connect api.example.com:443 &lt; /dev/null 2&gt;/dev/null \
  | openssl x509 -outform PEM &gt; ca.pem
</code></pre>
<p>Atau unduh bundle CA resmi dari dokumentasi penyedia API. Salin isi PEM ke variabel <code>const char* root_ca</code> di sketch.</p>

<h2>Embed Sertifikat Root CA di Firmware</h2>
<p>Sertifikat disimpan sebagai string multiline di flash — tidak perlu filesystem eksternal:</p>
<pre><code class="language-cpp">const char* root_ca =
  "-----BEGIN CERTIFICATE-----\n"
  "MIIFazCCA1OgAwIBAgIRAIIQz7DSQONZRGPgu2OCiwAwDQYJKoZIhvcNAQELBQAw\n"
  "... baris base64 dari ca.pem ...\n"
  "-----END CERTIFICATE-----\n";
</code></pre>
<p>Ganti baris tengah dengan isi file <code>ca.pem</code> Anda. Jangan commit sertifikat produksi sensitif jika organisasi Anda punya kebijakan khusus — untuk CA publik, ini aman.</p>

<h2>setCACert — Verifikasi Server yang Benar</h2>
<pre><code class="language-cpp">#include &lt;WiFiClientSecure.h&gt;
#include &lt;HTTPClient.h&gt;

WiFiClientSecure secureClient;

void setupSecureHttp() {
  secureClient.setCACert(root_ca);
  // Opsional: setTimeout jika API lambat
  secureClient.setTimeout(15000);
}
</code></pre>
<p>Tanpa <code>setCACert</code>, handshake TLS gagal atau library menolak koneksi — ESP32 tidak punya trust store lengkap seperti browser desktop.</p>

<h2>setInsecure — Hanya untuk Development</h2>
<p>Baris berikut <strong>mematikan verifikasi sertifikat</strong>:</p>
<pre><code class="language-cpp">secureClient.setInsecure(); // JANGAN di produksi!
</code></pre>
<p>Berguna saat prototyping cepat di lab — misalnya API self-signed tanpa CA. Di lapangan atau cloud, <strong>selalu pakai <code>setCACert</code></strong>. Sama seperti peringatan di <a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">MQTT TLS (#17)</a>: jangan bawa kebijakan dev ke produksi.</p>

<h2>HTTPS GET — Baca Data dari API</h2>
<p>Contoh membaca konfigurasi atau status dari endpoint:</p>
<pre><code class="language-cpp">void httpsGetExample() {
  HTTPClient http;
  const char* url = "https://api.example.com/v1/status";

  if (!http.begin(secureClient, url)) {
    Serial.println("http.begin gagal");
    return;
  }

  int code = http.GET();
  if (code == HTTP_CODE_OK) {
    String body = http.getString();
    Serial.println(body);
  } else {
    Serial.printf("GET error: %d\n", code);
  }
  http.end();
}
</code></pre>
<p>Response bisa JSON — parse dengan ArduinoJson jika perlu threshold suhu untuk relay di artikel <a href="/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22">capstone #10</a>.</p>

<h2>HTTPS POST — Kirim Data Sensor JSON</h2>
<p>Pola utama artikel ini: kirim pembacaan DHT22 (#5) ke backend REST, paralel atau pengganti MQTT:</p>
<pre><code class="language-cpp">void httpsPostSensor(float tempC, float humPct, long unixTs) {
  HTTPClient http;
  const char* url = "https://api.example.com/v1/sensors";

  if (!http.begin(secureClient, url)) return;

  http.addHeader("Content-Type", "application/json");
  // Header auth — ganti dengan token Anda, jangan commit ke repo
  http.addHeader("Authorization", "Bearer GANTI_TOKEN_API");

  char payload[200];
  snprintf(payload, sizeof(payload),
    "{\"unix\":%ld,\"timestamp\":\"2026-07-02T14:30:00\","
    "\"temp\":%.1f,\"hum\":%.1f,\"device\":\"esp32-dht22\"}",
    unixTs, tempC, humPct);

  int code = http.POST(payload);
  Serial.printf("POST %d\n", code);
  if (code &gt; 0) {
    Serial.println(http.getString());
  }
  http.end();
}
</code></pre>

<h2>Payload JSON dengan Timestamp NTP</h2>
<p>Gunakan waktu akurat dari <a href="/artikel/ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt">NTP (#34)</a> — contoh unix <code>1782977400</code> dan ISO <code>2026-07-02T14:30:00</code> konsisten di seluruh seri:</p>
<pre><code class="language-cpp">time_t now = time(nullptr); // setelah configTime() NTP
struct tm ti;
localtime_r(&amp;now, &amp;ti);
char iso[25];
strftime(iso, sizeof(iso), "%Y-%m-%dT%H:%M:%S", &amp;ti);

char payload[220];
snprintf(payload, sizeof(payload),
  "{\"unix\":%ld,\"timestamp\":\"%s\",\"temp\":%.1f,\"hum\":%.1f}",
  (long)now, iso, tempC, humPct);
</code></pre>
<p>Backend <a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">Python (#18)</a> bisa menerima POST HTTPS dengan skema field yang sama seperti payload MQTT — memudahkan migrasi dari broker ke REST.</p>

<h2>Kontras: Broker Lokal 192.168.1.50 vs API HTTPS Publik</h2>
<table>
  <thead>
    <tr><th>Lingkungan</th><th>Alamat contoh</th><th>Protokol</th><th>Artikel terkait</th></tr>
  </thead>
  <tbody>
    <tr><td>Broker rumah / VPS LAN</td><td><code>192.168.1.50:1883</code></td><td>MQTT plain / TLS 8883</td><td><a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">#16</a>, <a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">#17</a></td></tr>
    <tr><td>API cloud / SaaS</td><td><code>https://api.example.com</code></td><td>HTTPS 443</td><td><strong>#38 (ini)</strong></td></tr>
    <tr><td>Firebase</td><td><code>https://*.firebaseio.com</code></td><td>HTTPS + token</td><td><a href="/artikel/esp32-firebase-realtime-database-sensor-cloud">#30</a></td></tr>
    <tr><td>Web server ESP32</td><td><code>http://192.168.1.x</code></td><td>HTTP LAN</td><td><a href="/artikel/membuat-web-server-esp32-monitoring-sensor-dht22">#6</a></td></tr>
  </tbody>
</table>
<p>Node MQTT di LAN bisa pakai user <code>kindo_esp32</code> dengan password <code>GANTI_PASSWORD_MQTT</code> — itu pola broker, bukan header REST. Jangan campur kredensial MQTT ke header <code>Authorization</code> API kecuali backend Anda memang mendesainnya demikian.</p>

<h2>REST API vs MQTT — Kapan Pakai HTTPS?</h2>
<p>Rangkuman dari <a href="/artikel/rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32">#20</a>:</p>
<ul>
  <li><strong>HTTPS REST</strong> — laporan berkala ke SaaS, webhook, integrasi HTTP-only, mobile backend</li>
  <li><strong>MQTT</strong> — banyak device, real-time, broker pusat, dashboard <a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">Grafana (#19)</a></li>
  <li><strong>Keduanya</strong> — hybrid seperti <a href="/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22">capstone #10</a>: MQTT lokal + HTTPS backup ke cloud</li>
</ul>

<h2>Error Handling &amp; Kode Respons HTTP</h2>
<ul>
  <li><code>200 OK</code> — sukses GET/POST</li>
  <li><code>201 Created</code> — resource baru tersimpan</li>
  <li><code>401 Unauthorized</code> — token API salah atau kedaluwarsa</li>
  <li><code>429 Too Many Requests</code> — rate limit; tambah interval kirim</li>
  <li><code>-1</code> dari HTTPClient — gagal TLS, DNS, atau timeout</li>
</ul>
<p>Log kode ke Serial dan, jika perlu, simpan counter error di <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">NVS (#12)</a> untuk diagnosa setelah <a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">deep sleep (#11)</a>.</p>

<h2>Timeout, Reconnect &amp; Stabilitas WiFi</h2>
<pre><code class="language-cpp">http.setTimeout(20000);
http.setReuse(false); // tutup koneksi bersih tiap request
</code></pre>
<p>Setelah <a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">deep sleep (#11)</a>, WiFi mungkin putus — panggil <code>connectWiFi()</code> ulang sebelum HTTPS. Untuk node yang hanya POST sekali per bangun, satu request lalu sleep sudah cukup.</p>

<h2>Library Include — Tidak Perlu SPI.h</h2>
<p>Daftar include untuk artikel ini:</p>
<ul>
  <li><code>WiFi.h</code> — koneksi jaringan</li>
  <li><code>WiFiClientSecure.h</code> — lapisan TLS</li>
  <li><code>HTTPClient.h</code> — GET/POST HTTP(S)</li>
  <li><code>DHT.h</code> — sensor demo (#5)</li>
  <li><strong>Tidak</strong> <code>SPI.h</code> / <code>SD.h</code> — logging offline ada di <a href="/artikel/sd-card-spi-esp32-logging-data-sensor-offline">#37</a></li>
</ul>

<h2>Sketch Lengkap — WiFi, GET, POST DHT22</h2>
<pre><code class="language-cpp">#include &lt;WiFi.h&gt;
#include &lt;WiFiClientSecure.h&gt;
#include &lt;HTTPClient.h&gt;
#include &lt;DHT.h&gt;
#include &lt;time.h&gt;

const char* WIFI_SSID = "GANTI_NAMA_WIFI";
const char* WIFI_PASS = "GANTI_PASSWORD_WIFI";

const char* root_ca =
  "-----BEGIN CERTIFICATE-----\n"
  "GANTI_DENGAN_ISI_CA_PEM_ANDA\n"
  "-----END CERTIFICATE-----\n";

#define DHT_PIN  4
#define DHT_TYPE DHT22
DHT dht(DHT_PIN, DHT_TYPE);

WiFiClientSecure client;
const char* API_POST = "https://api.example.com/v1/sensors";
const char* API_GET  = "https://api.example.com/v1/config";

void setupNtp() {
  configTime(7 * 3600, 0, "pool.ntp.org", "time.nist.gov");
  struct tm ti;
  while (!getLocalTime(&amp;ti)) delay(200);
}

void setup() {
  Serial.begin(115200);
  dht.begin();
  delay(2000);

  WiFi.begin(WIFI_SSID, WIFI_PASS);
  while (WiFi.status() != WL_CONNECTED) delay(500);

  client.setCACert(root_ca);
  setupNtp();

  // Demo GET
  HTTPClient httpGet;
  if (httpGet.begin(client, API_GET)) {
    int c = httpGet.GET();
    Serial.printf("GET %d\n", c);
    httpGet.end();
  }

  // Demo POST sensor
  float t = dht.readTemperature();
  float h = dht.readHumidity();
  if (!isnan(t) &amp;&amp; !isnan(h)) {
    HTTPClient httpPost;
    if (httpPost.begin(client, API_POST)) {
      httpPost.addHeader("Content-Type", "application/json");
      char body[180];
      snprintf(body, sizeof(body),
        "{\"unix\":1782977400,\"timestamp\":\"2026-07-02T14:30:00\","
        "\"temp\":%.1f,\"hum\":%.1f}", t, h);
      int c = httpPost.POST(body);
      Serial.printf("POST %d\n", c);
      httpPost.end();
    }
  }
}

void loop() {
  delay(60000);
}
</code></pre>

<h2>PlatformIO — Dependency</h2>
<pre><code class="language-ini">[env:esp32dev]
platform = espressif32
board = esp32dev
framework = arduino
lib_deps =
    adafruit/DHT sensor library@^1.4
</code></pre>
<p><code>HTTPClient</code> dan <code>WiFiClientSecure</code> sudah termasuk framework Arduino ESP32 — tidak perlu lib tambahan untuk TLS dasar.</p>

<h2>Integrasi Dashboard &amp; Otomasi</h2>
<p>Data yang masuk via HTTPS POST bisa diolah server yang sama dengan pipeline MQTT:</p>
<ul>
  <li><a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">Python (#18)</a> — endpoint Flask/FastAPI terima POST, simpan MySQL</li>
  <li><a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">Grafana (#19)</a> — grafik histori dari DB</li>
  <li><a href="/artikel/node-red-dashboard-otomasi-iot-mqtt-esp32">Node-RED (#23)</a> — node HTTP In sebagai alternatif MQTT In</li>
  <li><a href="/artikel/home-assistant-integrasi-esp32-mqtt">Home Assistant (#21)</a> — RESTful sensor jika HA expose webhook</li>
</ul>

<h2>Keamanan &amp; Best Practice</h2>
<ul>
  <li><strong>Selalu <code>setCACert</code></strong> di produksi — hindari <code>setInsecure()</code></li>
  <li>Jangan simpan token API atau password WiFi di Git — pakai <code>GANTI_*</code> placeholder</li>
  <li>Rotasi token jika firmware pernah bocor; simpan token di <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">NVS (#12)</a></li>
  <li>HTTPS tidak menggantikan autentikasi aplikasi — tetap butuh API key atau OAuth di header</li>
  <li>Perbarui root CA jika penyedia ganti CA (jarang, tapi pernah terjadi)</li>
  <li>Pisahkan MQTT broker (<code>192.168.1.50</code>) dari API publik — attack surface berbeda</li>
</ul>

<h2>Estimasi Biaya (Indonesia)</h2>
<table>
  <thead>
    <tr><th>Komponen</th><th>Harga (Rp)</th></tr>
  </thead>
  <tbody>
    <tr><td>ESP32 DevKit</td><td>35.000 – 55.000</td></tr>
    <tr><td>DHT22</td><td>35.000 – 55.000</td></tr>
    <tr><td>Breadboard + jumper</td><td>15.000 – 25.000</td></tr>
    <tr><td>API cloud gratis tier</td><td>0 (Firebase #30, webhook, dll.)</td></tr>
    <tr><td>VPS HTTPS (opsional)</td><td>50.000 – 150.000 / bulan</td></tr>
    <tr><td><strong>Total hardware demo</strong></td><td><strong>~85.000 – 135.000</strong></td></tr>
  </tbody>
</table>

<h2>Checklist Sebelum Demo</h2>
<ul>
  <li>☐ WiFi connect dengan <code>GANTI_NAMA_WIFI</code> / <code>GANTI_PASSWORD_WIFI</code></li>
  <li>☐ Root CA benar — TLS handshake sukses (bukan <code>-1</code>)</li>
  <li>☐ <code>setInsecure()</code> <strong>tidak</strong> aktif di build demo</li>
  <li>☐ GET mengembalikan <code>200</code> atau respons yang diharapkan</li>
  <li>☐ POST JSON berisi <code>unix</code> + suhu/kelembaban DHT22</li>
  <li>☐ Token API pakai placeholder <code>GANTI_TOKEN_API</code></li>
  <li>☐ Serial log menampilkan kode HTTP, bukan stack trace TLS</li>
</ul>

<h2>FAQ Singkat</h2>
<dl>
  <dt><strong>Sama dengan MQTT TLS (#17)?</strong></dt>
  <dd>Tidak — #17 mengamankan broker MQTT; artikel ini mengamankan <strong>HTTP REST</strong> ke API web.</dd>
  <dt><strong>Perlu SPI.h?</strong></dt>
  <dd>Tidak — tidak ada SD Card. Lihat <a href="/artikel/sd-card-spi-esp32-logging-data-sensor-offline">#37</a> jika butuh log offline.</dd>
  <dt><strong>setInsecure aman di rumah?</strong></dt>
  <dd>Hanya prototyping singkat. Untuk API produksi, wajib <code>setCACert</code>.</dd>
  <dt><strong>Bisa gabung MQTT + HTTPS?</strong></dt>
  <dd>Ya — MQTT ke <code>192.168.1.50</code> untuk real-time, HTTPS untuk backup cloud (<a href="/artikel/rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32">#20</a>).</dd>
  <dt><strong>Firebase sudah HTTPS?</strong></dt>
  <dd>Ya — <a href="/artikel/esp32-firebase-realtime-database-sensor-cloud">#30</a> memakai pola serupa dengan token, bukan CA custom.</dd>
</dl>

<h2>Tips &amp; Troubleshooting</h2>
<ul>
  <li><strong>HTTP -1 / connection refused:</strong> Cek URL, DNS, firewall router, dan validitas <code>root_ca</code></li>
  <li><strong>Certificate verify failed:</strong> Salah CA atau server pakai intermediate — unduh ulang chain yang benar</li>
  <li><strong>401 terus:</strong> Token salah; jangan pakai password MQTT sebagai Bearer token</li>
  <li><strong>Heap low:</strong> Kurangi ukuran <code>String</code>; pakai buffer <code>char[]</code> untuk payload</li>
  <li><strong>DHT NaN:</strong> Sama seperti <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">#5</a> — jangan POST jika bacaan invalid</li>
  <li><strong>Deep sleep gagal POST:</strong> Beri waktu cukup setelah wake untuk WiFi + TLS sebelum sleep lagi (#11)</li>
</ul>

<h2>Deep Sleep, NVS &amp; URL API</h2>
<p>Simpan URL endpoint dan interval POST di <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">NVS (#12)</a> agar bisa ganti backend tanpa re-flash. Node <a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">deep sleep (#11)</a> bangun → WiFi → NTP (#34) → satu POST HTTPS → tidur — hemat energi dibanding maintain koneksi MQTT persisten.</p>

<h2>Hybrid: MQTT Lokal + HTTPS Cloud</h2>
<p>Arsitektur <a href="/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22">capstone #10</a> bisa diperluas: publish ke broker <code>192.168.1.50</code> untuk <a href="/artikel/home-assistant-integrasi-esp32-mqtt">Home Assistant (#21)</a>, sekaligus POST HTTPS ke VPS untuk rekan yang tidak punya akses LAN. Data dari <a href="/artikel/sd-card-spi-esp32-logging-data-sensor-offline">SD Card (#37)</a> bisa di-upload lewat HTTPS saat backlog sync.</p>

<h2>Langkah Selanjutnya — Menuju Greenhouse #39</h2>
<p>HTTPS REST melengkapi puzzle keamanan Tier 2 setelah MQTT TLS (#17). Anda sekarang bisa:</p>
<ul>
  <li>Mengirim sensor ke API cloud dengan TLS dan verifikasi CA</li>
  <li>Membedakan kapan MQTT (#7), REST (#20), atau keduanya</li>
  <li>Mempersiapkan backend yang menerima JSON dengan timestamp <code>1782977400</code> / <code>2026-07-02T14:30:00</code></li>
</ul>
<p>Di artikel berikutnya, <strong>capstone greenhouse (#39)</strong> akan menggabungkan multi-sensor, logging <a href="/artikel/sd-card-spi-esp32-logging-data-sensor-offline">SD (#37)</a>, MQTT, dan HTTPS ke satu sistem monitoring kebun lengkap — lanjutkan di <a href="/artikel">halaman artikel</a> Koding Indonesia.</p>
HTML;
    }
}
