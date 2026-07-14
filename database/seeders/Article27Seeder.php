<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article27Seeder extends Seeder
{
    public function run(): void
    {
        $admin  = User::first();
        $espCat = Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $espCat) {
            throw new \RuntimeException('User atau kategori tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'esp32-cam-streaming-mjpeg-capture-foto-wifi';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $espCat->id,
                'title'           => 'ESP32-CAM: Streaming MJPEG & Capture Foto via WiFi',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'ESP32-CAM MJPEG Streaming & Capture Foto WiFi',
                'seo_description' => 'Tutorial ESP32-CAM: streaming MJPEG ke browser, capture foto JPEG, dan web server WiFi. Wiring AI-Thinker, sketch esp_camera, tips bandwidth & keamanan.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        Tag::updateOrCreate(['slug' => 'esp32-cam'], ['name' => 'esp32-cam']);
        Tag::updateOrCreate(['slug' => 'mjpeg'], ['name' => 'mjpeg']);
        Tag::updateOrCreate(['slug' => 'camera'], ['name' => 'camera']);

        $tagIds = Tag::whereIn('slug', [
            'esp32', 'esp32-cam', 'mjpeg', 'iot', 'wifi', 'camera',
        ])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command->info('✓ Artikel ke-27 berhasil dipublish: ' . $article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — Dari Sensor ke Gambar</h2>
<p>Seri 2 sudah mengajarkan telemetry: <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">DHT22 (#5)</a>, <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">MQTT (#7)</a>, bahkan <a href="/artikel/lora-esp32-modul-sx1278-kirim-data-jarak-jauh">LoRa jarak jauh (#26)</a>. Tapi kadang kamu butuh <strong>visual</strong> — cek kondisi kebun, pantau gerbang, atau dokumentasi lapangan.</p>

<p><strong>ESP32-CAM</strong> menggabungkan ESP32 + kamera OV2640 dalam satu modul murah. Artikel <strong>Jalur D</strong> ini fokus pada <strong>streaming MJPEG</strong> ke browser dan <strong>capture foto JPEG</strong> via WiFi — pola mirip <a href="/artikel/membuat-web-server-esp32-monitoring-sensor-dht22">web server ESP32 (#6)</a>, tapi payload-nya frame gambar, bukan angka sensor.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> Sudah paham <a href="/artikel/menghubungkan-esp32-wifi-kirim-data-server">WiFi ESP32 (#4)</a> dan idealnya pernah buat <a href="/artikel/membuat-web-server-esp32-monitoring-sensor-dht22">WebServer (#6)</a>. Video streaming <strong>bukan</strong> pengganti MQTT — lihat <a href="/artikel/rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32">REST vs MQTT (#20)</a>.</p>
</blockquote>

<h2>Apa Itu ESP32-CAM?</h2>
<p><strong>ESP32-CAM</strong> adalah modul AI-Thinker (dan clone) berisi:</p>
<ul>
  <li>Chip <strong>ESP32</strong> (WiFi + dual-core)</li>
  <li>Kamera <strong>OV2640</strong> (2 MP, JPEG built-in)</li>
  <li>Slot microSD (opsional, untuk simpan foto)</li>
  <li>LED flash putih + LED indikator merah</li>
</ul>
<p>Modul ini hemat biaya (~Rp 40–60rb) tapi punya keterbatasan: butuh <strong>5V stabil</strong>, panas saat stream lama, dan bandwidth WiFi tinggi dibanding sensor telemetry.</p>

<h2>ESP32-CAM vs DevKit vs LoRa/ESP-NOW</h2>
<table>
  <thead>
    <tr><th>Modul</th><th>Cocok untuk</th><th>Data</th><th>Catatan</th></tr>
  </thead>
  <tbody>
    <tr><td><strong>ESP32 DevKit + DHT22</strong></td><td>Telemetry angka</td><td>Suhu, RH, MQTT</td><td>Hemat bandwidth (<a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">#5</a>, <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">#7</a>)</td></tr>
    <tr><td><strong><a href="/artikel/esp-now-kirim-data-antar-esp32-tanpa-router-wifi">ESP-NOW (#25)</a></strong></td><td>Node dekat, tanpa router</td><td>Paket kecil</td><td>Bukan video</td></tr>
    <tr><td><strong><a href="/artikel/lora-esp32-modul-sx1278-kirim-data-jarak-jauh">LoRa (#26)</a></strong></td><td>Sensor sangat jauh</td><td>Bytes jarang</td><td>Tidak untuk video</td></tr>
    <tr><td><strong>ESP32-CAM (artikel ini)</strong></td><td>Visual monitoring</td><td>MJPEG / JPEG</td><td>Butuh WiFi stabil</td></tr>
  </tbody>
</table>

<h2>Yang Kamu Butuhkan</h2>
<ul>
  <li><strong>1× ESP32-CAM</strong> (AI-Thinker atau clone kompatibel)</li>
  <li><strong>USB-to-TTL</strong> adapter (FTDI/CP2102) untuk flash — <em>jangan</em> colok USB langsung ke modul tanpa regulator</li>
  <li>Kabel jumper · breadboard (opsional)</li>
  <li>Router WiFi 2.4 GHz</li>
  <li>Library <strong>esp32-camera</strong> (biasanya sudah di board package)</li>
</ul>
<p><strong>Estimasi biaya:</strong> modul ESP32-CAM ~Rp 50rb + USB-TTL ~Rp 25rb → lab ~Rp 75–100rb.</p>

<h2>Arsitektur Streaming MJPEG</h2>
<table>
  <thead>
    <tr><th>Komponen</th><th>Peran</th></tr>
  </thead>
  <tbody>
    <tr><td><strong>OV2640</strong></td><td>Capture frame → kompres JPEG di chip kamera</td></tr>
    <tr><td><strong>ESP32</strong></td><td>WiFi + <code>WebServer</code> serve stream HTTP</td></tr>
    <tr><td><strong>Browser / HP</strong></td><td>Buka <code>http://IP_ESP32/stream</code> atau <code>/capture</code></td></tr>
  </tbody>
</table>

<figure role="img" aria-label="Diagram streaming MJPEG ESP32-CAM: kamera OV2640 kirim frame JPEG ke ESP32 WebServer, lalu browser di WiFi LAN membuka stream" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 900 300" style="display:block;max-width:900px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="camArrow" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto">
      <path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/>
    </marker>
    <marker id="camArrowOrange" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto">
      <path d="M0,0 L8,4 L0,8 Z" fill="#FF7A2F"/>
    </marker>
  </defs>
  <rect x="0" y="0" width="900" height="300" fill="#F5F5F0" rx="6"/>
  <line x1="248" y1="90" x2="318" y2="90" stroke="#FF7A2F" stroke-width="2.5" marker-end="url(#camArrowOrange)"/>
  <line x1="568" y1="90" x2="638" y2="90" stroke="#2979FF" stroke-width="2.5" marker-end="url(#camArrow)"/>
  <line x1="760" y1="138" x2="760" y2="198" stroke="#2979FF" stroke-width="2.5" marker-end="url(#camArrow)"/>
  <rect x="24" y="40" width="224" height="100" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="136" y="75" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">Kamera OV2640</text>
  <text x="136" y="98" text-anchor="middle" fill="#4A5568" font-size="12">Capture + kompres JPEG</text>
  <text x="136" y="118" text-anchor="middle" fill="#718096" font-size="11">di chip kamera</text>
  <rect x="258" y="48" width="72" height="24" rx="4" fill="#fff" stroke="#FF7A2F" stroke-width="1.5"/>
  <text x="294" y="65" text-anchor="middle" fill="#FF7A2F" font-size="12" font-weight="700">JPEG →</text>
  <rect x="328" y="40" width="240" height="100" rx="6" fill="#FFF3E8" stroke="#000" stroke-width="2.5"/>
  <text x="448" y="72" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">ESP32 WebServer :80</text>
  <text x="448" y="94" text-anchor="middle" fill="#4A5568" font-size="12">/stream · /capture</text>
  <text x="448" y="116" text-anchor="middle" fill="#718096" font-size="11">multipart MJPEG</text>
  <rect x="578" y="48" width="72" height="24" rx="4" fill="#fff" stroke="#2979FF" stroke-width="1.5"/>
  <text x="614" y="65" text-anchor="middle" fill="#2979FF" font-size="12" font-weight="700">WiFi →</text>
  <rect x="648" y="40" width="224" height="98" rx="6" fill="#2979FF" stroke="#000" stroke-width="2.5"/>
  <text x="760" y="75" text-anchor="middle" fill="#fff" font-size="14" font-weight="700">Router / LAN</text>
  <text x="760" y="97" text-anchor="middle" fill="#e3f2fd" font-size="12">2.4 GHz · IP lokal</text>
  <text x="760" y="117" text-anchor="middle" fill="#cfe4ff" font-size="11">satu jaringan</text>
  <rect x="780" y="156" width="100" height="24" rx="4" fill="#fff" stroke="#2979FF" stroke-width="1.5"/>
  <text x="830" y="173" text-anchor="middle" fill="#2979FF" font-size="12" font-weight="700">HTTP ↓</text>
  <rect x="548" y="208" width="320" height="60" rx="6" fill="#F5F5F0" stroke="#000" stroke-width="2.5"/>
  <text x="708" y="235" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">Browser / HP</text>
  <text x="708" y="255" text-anchor="middle" fill="#4A5568" font-size="12">Live MJPEG + capture JPEG</text>
  <text x="450" y="285" text-anchor="middle" fill="#4A5568" font-size="11">Alur: kamera → ESP32 WebServer → WiFi LAN → browser (bukan lewat MQTT)</text>
</svg>
<figcaption style="margin-top:.75rem;font-size:.875rem;color:#4A5568;text-align:center">Diagram streaming MJPEG ESP32-CAM — OV2640 kirim JPEG ke WebServer; klien di jaringan yang sama membuka <code>/stream</code> atau <code>/capture</code>.</figcaption>
</figure>

<p>Untuk telemetry paralel (suhu + gambar), kamu bisa gabungkan dengan <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">MQTT (#7)</a> di board terpisah — jangan paksa video lewat broker MQTT.</p>

<h2>Power &amp; Flashing — Penting!</h2>
<p>ESP32-CAM <strong>tidak punya USB onboard</strong>. Wiring umum ke USB-TTL:</p>
<table>
  <thead>
    <tr><th>ESP32-CAM</th><th>USB-TTL</th></tr>
  </thead>
  <tbody>
    <tr><td>5V</td><td>5V (supply stabil ≥500 mA)</td></tr>
    <tr><td>GND</td><td>GND</td></tr>
    <tr><td>U0R (GPIO 3)</td><td>TX</td></tr>
    <tr><td>U0T (GPIO 1)</td><td>RX</td></tr>
    <tr><td>GPIO 0</td><td>GND saat flash (boot mode)</td></tr>
  </tbody>
</table>

<blockquote>
  <p><strong>Pro tip:</strong> Lepas jumper GPIO 0 → GND setelah upload. Tekan tombol RESET sebelum buka stream. Undervoltage = gambar corrupt atau reboot loop.</p>
</blockquote>

<h2>Instalasi Board &amp; Library</h2>
<ol>
  <li>Arduino IDE → Board Manager → <strong>esp32</strong> by Espressif</li>
  <li>Pilih board: <strong>AI Thinker ESP32-CAM</strong></li>
  <li>Partition Scheme: <strong>Huge APP (3MB No OTA)</strong> — streaming butuh flash besar</li>
  <li>Library <code>esp_camera</code> ikut board package (tidak perlu install terpisah)</li>
</ol>

<h2>Sketch — Inisialisasi Kamera</h2>
<pre><code class="language-cpp">#include "esp_camera.h"
#include &lt;WiFi.h&gt;
#include &lt;WebServer.h&gt;

#define CAMERA_MODEL_AI_THINKER

const char* ssid = "GANTI_NAMA_WIFI";
const char* password = "GANTI_PASSWORD_WIFI";

WebServer server(80);

void setup() {
  Serial.begin(115200);
  camera_config_t config;
  config.ledc_channel = LEDC_CHANNEL_0;
  config.ledc_timer = LEDC_TIMER_0;
  config.pin_d0 = 5;
  config.pin_d1 = 18;
  config.pin_d2 = 19;
  config.pin_d3 = 21;
  config.pin_d4 = 36;
  config.pin_d5 = 39;
  config.pin_d6 = 34;
  config.pin_d7 = 35;
  config.pin_xclk = 0;
  config.pin_pclk = 22;
  config.pin_vsync = 25;
  config.pin_href = 23;
  config.pin_sccb_sda = 26;
  config.pin_sccb_scl = 27;
  config.pin_pwdn = 32;
  config.pin_reset = -1;
  config.xclk_freq_hz = 20000000;
  config.frame_size = FRAMESIZE_VGA;
  config.pixel_format = PIXFORMAT_JPEG;
  config.grab_mode = CAMERA_GRAB_LATEST;
  config.fb_location = CAMERA_FB_IN_PSRAM;
  config.jpeg_quality = 12;
  config.fb_count = 2;

  if (esp_camera_init(&amp;config) != ESP_OK) {
    Serial.println("Camera init gagal");
    return;
  }

  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) { delay(500); Serial.print("."); }
  Serial.println(WiFi.localIP());
}</code></pre>

<h2>Endpoint MJPEG Stream</h2>
<p>Pola standar: boundary multipart HTTP. Browser menampilkan sebagai video live di jaringan lokal.</p>
<pre><code class="language-cpp">void handleStream() {
  WiFiClient client = server.client();
  String response = "HTTP/1.1 200 OK\r\n";
  response += "Content-Type: multipart/x-mixed-replace; boundary=frame\r\n\r\n";
  server.sendContent(response);

  while (client.connected()) {
    camera_fb_t* fb = esp_camera_fb_get();
    if (!fb) continue;
    client.printf("--frame\r\nContent-Type: image/jpeg\r\nContent-Length: %u\r\n\r\n", fb->len);
    client.write(fb->buf, fb->len);
    client.print("\r\n");
    esp_camera_fb_return(fb);
    delay(30);
  }
}</code></pre>

<h2>Endpoint Capture Foto</h2>
<pre><code class="language-cpp">void handleCapture() {
  camera_fb_t* fb = esp_camera_fb_get();
  if (!fb) {
    server.send(500, "text/plain", "Capture gagal");
    return;
  }
  server.sendHeader("Content-Disposition", "inline; filename=capture.jpg");
  server.send_P(200, "image/jpeg", (const char*)fb->buf, fb->len);
  esp_camera_fb_return(fb);
}

void setupRoutes() {
  server.on("/stream", HTTP_GET, handleStream);
  server.on("/capture", HTTP_GET, handleCapture);
  server.on("/", HTTP_GET, []() {
    server.send(200, "text/html",
      "&lt;h1&gt;ESP32-CAM&lt;/h1&gt;&lt;p&gt;&lt;a href='/stream'&gt;Live stream&lt;/a&gt; · "
      "&lt;a href='/capture'&gt;Capture foto&lt;/a&gt;&lt;/p&gt;");
  });
  server.begin();
}

void loop() {
  server.handleClient();
}</code></pre>

<h2>Resolusi &amp; Kualitas JPEG</h2>
<ul>
  <li><code>FRAMESIZE_QVGA</code> (320×240) — paling ringan, cocok WiFi lemah</li>
  <li><code>FRAMESIZE_VGA</code> (640×480) — sweet spot lab</li>
  <li><code>FRAMESIZE_SVGA</code> / <code>UXGA</code> — lebih tajam, lag lebih besar</li>
  <li><code>jpeg_quality</code> 10–15 — angka lebih <em>rendah</em> = kualitas lebih baik (counter-intuitive di esp_camera)</li>
</ul>

<p>Contoh metadata log (konsisten timestamp Seri 2 untuk proyek hybrid):</p>
<pre><code>{"event":"capture","unix":1782977400,"iso":"2026-07-02T14:30:00","framesize":"VGA"}</code></pre>

<h2>Bandwidth &amp; Latency</h2>
<p>MJPEG di VGA bisa menghabiskan <strong>1–3 Mbps</strong> — jauh di atas paket MQTT sensor. Pastikan:</p>
<ul>
  <li>Klien dan ESP32-CAM di <strong>WiFi yang sama</strong> (2.4 GHz)</li>
  <li>Jangan stream 24/7 tanpa heatsink — modul panas</li>
  <li>Untuk monitoring jarang, pakai <code>/capture</code> periodik, bukan stream terus</li>
</ul>

<h2>MicroSD (Opsional) — Simpan Foto di Lapangan</h2>
<p>Banyak modul ESP32-CAM punya slot <strong>microSD</strong>. Kamu bisa simpan hasil <code>/capture</code> ke kartu untuk audit lapangan tanpa koneksi terus-menerus — pola mirip <a href="/artikel/sd-card-spi-esp32-logging-data-sensor-offline">logging offline SD Card (#37)</a>.</p>
<ul>
  <li>Format kartu <strong>FAT32</strong> · kapasitas ≤32 GB untuk kompatibilitas terbaik</li>
  <li>Gunakan library <code>SD_MMC</code> atau <code>FS</code> dari board package</li>
  <li>Nama file contoh: <code>/sdcard/cap_1782977400.jpg</code> — selaraskan dengan <a href="/artikel/ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt">timestamp NTP (#34)</a></li>
  <li>Jangan stream MJPEG dan write SD bersamaan di sketch pertama — debug satu fitur dulu</li>
</ul>

<h2>Integrasi dengan Stack MQTT (Hybrid)</h2>
<p>Polanya: <strong>ESP32-CAM</strong> untuk visual lokal, <strong>ESP32 DevKit</strong> terpisah untuk telemetry MQTT ke <code>kodingindonesia/esp32/dht22/data</code> via broker <code>192.168.1.50</code>. Jangan kirim frame video lewat MQTT — bandwidth broker tidak dirancang untuk itu.</p>
<p>Event ringan yang masuk MQTT: <code>{"event":"motion","unix":1782977400}</code> atau trigger capture saat PIR terdeteksi lewat <a href="/artikel/node-red-dashboard-otomasi-iot-mqtt-esp32">Node-RED (#23)</a> — metadata saja, bukan binary JPEG.</p>

<h2>WiFiManager untuk Deploy Lapangan</h2>
<p>Ganti hardcode SSID dengan <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">NVS + WiFiManager (#12)</a> — portal konfigurasi saat pertama nyala, sama seperti node sensor produksi.</p>

<h2>LED Flash &amp; Kondisi Cahaya</h2>
<p>ESP32-CAM punya <strong>LED flash putih</strong> di GPIO 4 (umum di AI-Thinker). Untuk foto malam di kebun:</p>
<ul>
  <li>Aktifkan LED sebelum <code>esp_camera_fb_get()</code> — matikan setelah capture untuk hemat arus</li>
  <li>Exposure otomatis OV2640 butuh ~100–300 ms setelah LED nyala</li>
  <li>Stream siang hari tanpa LED — hindari backlight langsung ke lensa</li>
  <li>Gabungkan dengan sensor cahaya LDR di <a href="/artikel/adc-esp32-sensor-analog-soil-moisture-ldr-mqtt">artikel ADC (#35)</a> untuk auto-flash nanti</li>
</ul>

<p>Untuk proyek <a href="/artikel/smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt">greenhouse capstone (#39)</a>, kamera + suhu MQTT + pompa relay adalah tiga jalur terpisah yang saling melengkapi — visual tidak menggantikan angka sensor.</p>

<h2>Checklist: Kapan Pakai ESP32-CAM?</h2>
<ol>
  <li>Butuh lihat kondisi visual real-time di LAN? → <strong>ESP32-CAM stream</strong></li>
  <li>Butuh foto sesekali (audit kebun, bukti lapangan)? → <strong>/capture</strong></li>
  <li>Butuh angka sensor ke dashboard MQTT? → <strong>DHT22/BME280 + <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">MQTT (#7)</a></strong></li>
  <li>Butuh jarak kilometer tanpa WiFi? → <strong><a href="/artikel/lora-esp32-modul-sx1278-kirim-data-jarak-jauh">LoRa (#26)</a></strong>, bukan kamera</li>
  <li>Butuh akses dari internet publik? → <strong>VPN / reverse proxy</strong> — jangan port-forward mentah</li>
</ol>

<h2>Uji Coba (Lab)</h2>
<pre><code class="language-bash"># Setelah flash &amp; Serial menampilkan IP (mis. 192.168.1.88):
# Browser: http://192.168.1.88/
# Live:    http://192.168.1.88/stream
# Foto:    http://192.168.1.88/capture

# Cek MQTT sensor paralel (board terpisah) — topic Seri 2:
mosquitto_sub -h 192.168.1.50 -u kindo_esp32 -P GANTI_PASSWORD_MQTT \
  -t kodingindonesia/esp32/dht22/data -v</code></pre>
<ol>
  <li>Flash sketch, buka Serial Monitor 115200 — catat IP</li>
  <li>Buka <code>/stream</code> di browser HP/laptop <strong>jaringan sama</strong></li>
  <li>Klik <code>/capture</code> — harus muncul JPEG</li>
  <li>Ubah <code>FRAMESIZE_QVGA</code> jika lag — bandingkan smoothness</li>
  <li>Matikan stream 5 menit — cek suhu modul tidak overheat</li>
</ol>

<h2>FAQ Singkat</h2>
<dl>
  <dt><strong>Bisa streaming ke Grafana?</strong></dt>
  <dd><a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">Grafana untuk time-series angka (#19)</a>. Video butuh stack lain (RTSP/WebRTC) — di luar scope tutorial ini.</dd>
  <dt><strong>Bisa kirim frame lewat MQTT?</strong></dt>
  <dd>Secara teknis bisa base64, tapi tidak efisien. MQTT untuk metadata/event; gambar simpan SD atau HTTP.</dd>
  <dt><strong>ESP32-CAM vs CCTV IP camera?</strong></dt>
  <dd>CCTV lebih matang untuk 24/7. ESP32-CAM cocok prototipe &amp; belajar embedded vision murah.</dd>
</dl>

<h2>Tips &amp; Troubleshooting</h2>
<ul>
  <li><strong>Camera init gagal:</strong> Cek model board AI-Thinker, partition Huge APP, power 5V cukup</li>
  <li><strong>Gambar hijau/pink:</strong> Kabel kamera longgar atau PSRAM tidak aktif</li>
  <li><strong>Stream putus-putus:</strong> Turunkan resolusi atau dekatkan ke router</li>
  <li><strong>Tidak bisa flash:</strong> GPIO 0 harus ke GND saat boot flash</li>
  <li><strong>IP tidak muncul:</strong> SSID/password salah — uji dengan <a href="/artikel/menghubungkan-esp32-wifi-kirim-data-server">#4</a> dulu</li>
</ul>

<h2>Keamanan &amp; Produksi</h2>
<ul>
  <li>Jangan expose port 80 ESP32-CAM langsung ke internet — risiko bot scan kamera</li>
  <li>Ganti <code>GANTI_NAMA_WIFI</code> / <code>GANTI_PASSWORD_WIFI</code> — jangan commit ke GitHub</li>
  <li>Pertimbangkan <a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">MQTT TLS (#17)</a> untuk channel kontrol terpisah</li>
  <li>Label modul &amp; lokasi fisik — kamera di area privat perlu consent</li>
</ul>

<h2>Langkah Selanjutnya (Jalur D)</h2>
<ul>
  <li><strong><a href="/artikel/gateway-lora-mqtt-esp32-sensor-jarak-jauh-dashboard">Gateway LoRa → MQTT (#28)</a>:</strong> gabungkan sensor jauh + visual lokal</li>
  <li><strong><a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">Grafana (#19)</a></strong> — dashboard angka sensor (bukan video)</li>
  <li><strong><a href="/artikel/ota-update-firmware-esp32-via-wifi">OTA (#15)</a></strong> — update firmware kamera tanpa USB-TTL</li>
  <li><strong><a href="/artikel/home-assistant-integrasi-esp32-mqtt">Home Assistant (#21)</a></strong> — integrasi stream kamera (addon terpisah)</li>
  <li>Capstone <strong><a href="/artikel/smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt">greenhouse (#39)</a></strong> — kamera + sensor MQTT + pompa</li>
</ul>

<p>ESP32-CAM menambah dimensi <em>visual</em> ke toolkit IoT kamu — tetap di LAN untuk lab, dan pisahkan dari pipeline telemetry MQTT. Lanjutkan Seri 2 di <a href="/artikel">halaman artikel</a> Koding Indonesia.</p>
HTML;
    }
}
