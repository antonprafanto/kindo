<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article26Seeder extends Seeder
{
    public function run(): void
    {
        $admin  = User::first();
        $espCat = Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $espCat) {
            throw new \RuntimeException('User atau kategori tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'lora-esp32-modul-sx1278-kirim-data-jarak-jauh';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $espCat->id,
                'title'           => 'LoRa ESP32 + SX1278: Kirim Data Sensor Jarak Jauh',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'LoRa ESP32 SX1278 — Sensor Jarak Jauh Tanpa WiFi',
                'seo_description' => 'Tutorial LoRa ESP32 + modul SX1278: kirim data sensor ratusan meter hingga kilometer. Band 433 MHz, library LoRa.h, wiring SPI, dan catatan regulasi Indonesia.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        Tag::updateOrCreate(['slug' => 'lora'], ['name' => 'lora']);
        Tag::updateOrCreate(['slug' => 'sx1278'], ['name' => 'sx1278']);
        Tag::updateOrCreate(['slug' => 'spi'], ['name' => 'spi']);

        $tagIds = Tag::whereIn('slug', [
            'esp32', 'lora', 'sx1278', 'iot', 'sensor', 'spi',
        ])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command->info('✓ Artikel ke-26 berhasil dipublish: ' . $article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — Lebih Jauh dari ESP-NOW</h2>
<p>Di <a href="/artikel/esp-now-kirim-data-antar-esp32-tanpa-router-wifi">artikel #25 ESP-NOW</a>, kamu sudah mengirim data antar dua ESP32 tanpa router — cocok untuk jarak puluhan hingga ~200 meter. Tapi bagaimana jika sensor harus di <strong>ujung kebun, sawah, atau area terpencil</strong> yang jaraknya ratusan meter bahkan kilometer dari gateway?</p>

<p><strong>LoRa</strong> (Long Range) mengisi celah itu. Dengan modul radio <strong>SX1278</strong> di tiap ESP32, kamu bisa mengirim paket data kecil dengan konsumsi daya rendah — tanpa infrastruktur WiFi di titik sensor.</p>

<p>Artikel <strong>Jalur D</strong> ini fokus pada link <strong>LoRa peer-to-peer</strong> (node sensor → node receiver). Menggabungkan LoRa ke MQTT/dashboard adalah langkah berikutnya di artikel gateway <strong><a href="/artikel/gateway-lora-mqtt-esp32-sensor-jarak-jauh-dashboard">#28</a></strong>.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> Paham dasar <a href="/artikel/menghubungkan-esp32-wifi-kirim-data-server">WiFi ESP32 (#4)</a>, <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">DHT22 (#5)</a>, dan idealnya sudah baca <a href="/artikel/esp-now-kirim-data-antar-esp32-tanpa-router-wifi">ESP-NOW (#25)</a> untuk membandingkan jangkau radio.</p>
</blockquote>

<h2>Apa Itu LoRa?</h2>
<p><strong>LoRa</strong> (Long Range) adalah teknik modulasi radio proprietary Semtech yang dipakai di jaringan <strong>LPWAN</strong> (Low Power Wide Area Network). Karakteristik utama:</p>
<ul>
  <li><strong>Jangkau jauh</strong> — ratusan meter hingga beberapa kilometer (LOS), tergantung antena &amp; parameter</li>
  <li><strong>Data rate rendah</strong> — cocok telemetry sensor (suhu, kelembaban), bukan video</li>
  <li><strong>Hemat daya</strong> — node bisa kirim sesekali lalu tidur (<a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">deep sleep #11</a>)</li>
  <li><strong>Tanpa WiFi</strong> — link radio langsung antar modul LoRa</li>
</ul>

<p>Chip <strong>SX1278</strong> adalah transceiver LoRa populer di modul murah (433 MHz) yang dipasangkan ke ESP32 via <strong>SPI</strong> — bus serial cepat yang juga dipakai di sensor <a href="/artikel/i2c-esp32-sensor-bme280-suhu-tekanan-mqtt">I2C BME280 (#13)</a>, tapi wiring-nya berbeda (MOSI/MISO/SCK + chip select).</p>

<p>Di Indonesia, pola ini sering dipakai untuk <strong>monitoring kebun/sawah</strong> (suhu tanah, kelembaban udara), <strong>level tangki air</strong> jauh dari rumah, atau node capstone <a href="/artikel/smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt"><strong>greenhouse (#39)</strong></a> yang sensornya di ujung lahan.</p>

<h2>LoRa vs ESP-NOW vs WiFi/MQTT</h2>
<table>
  <thead>
    <tr><th>Protokol</th><th>Jangkau</th><th>Hardware</th><th>Cocok untuk</th></tr>
  </thead>
  <tbody>
    <tr><td><strong><a href="/artikel/esp-now-kirim-data-antar-esp32-tanpa-router-wifi">ESP-NOW (#25)</a></strong></td><td>~10–200 m</td><td>2× ESP32 saja</td><td>Node dekat, latency rendah</td></tr>
    <tr><td><strong>LoRa (artikel ini)</strong></td><td>~500 m – 5+ km LOS</td><td>ESP32 + modul SX1278</td><td>Sensor sangat jauh, data jarang</td></tr>
    <tr><td><strong><a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">WiFi + MQTT (#7)</a></strong></td><td>Seluruh jaringan AP</td><td>Router + <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">broker (#16)</a></td><td>Dashboard, multi-subscriber</td></tr>
  </tbody>
</table>

<p>Lihat juga perbandingan protokol di <a href="/artikel/rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32">REST vs MQTT (#20)</a> untuk lapisan cloud setelah data sampai di gateway.</p>

<h2>Band Frekuensi &amp; Regulasi Indonesia</h2>
<p>Modul SX1278 di pasaran hobby umumnya <strong>433 MHz</strong> atau <strong>868/915 MHz</strong>. Ini penting — salah band = tidak saling dengar atau bermasalah legal.</p>

<table>
  <thead>
    <tr><th>Band modul</th><th>Catatan</th></tr>
  </thead>
  <tbody>
    <tr><td><strong>433 MHz</strong></td><td>Paling umum di kit murah; jarak dekat–menengah; <strong>hobby/lab</strong> — cek regulasi Kominfo sebelum deploy komersial</td></tr>
    <tr><td><strong>868 MHz</strong></td><td>Umum di Eropa; pastikan modul &amp; antena sesuai region</td></tr>
    <tr><td><strong>915 MHz</strong></td><td>Umum di AS; di Indonesia band ISM LPWAN sering dibahas di <strong>920–923 MHz</strong> untuk LoRaWAN komersial</td></tr>
  </tbody>
</table>

<blockquote>
  <p><strong>Peringatan:</strong> Artikel ini untuk <strong>belajar &amp; prototipe</strong>. Jangan deploy jaringan LoRa skala komersial tanpa riset izin frekuensi dan daya pancar sesuai regulasi Indonesia (Kominfo / SDPPI).</p>
</blockquote>

<h2>Yang Kamu Butuhkan</h2>
<ul>
  <li><strong>2× ESP32</strong> DevKit</li>
  <li><strong>2× modul LoRa SX1278</strong> (433 MHz untuk lab — samakan frekuensi di kedua modul)</li>
  <li><strong>DHT22</strong> di node sensor (opsional)</li>
  <li>Kabel jumper, breadboard, antena 433 MHz (sering sudah terpasang di modul)</li>
  <li>Library <strong>LoRa</strong> by Sandeep Mistry di Arduino IDE</li>
</ul>

<p><strong>Estimasi biaya:</strong> modul LoRa 433 MHz ~Rp 25–40rb ×2 + 2× ESP32 → total lab ~Rp 200–300rb.</p>

<h2>Arsitektur Node LoRa</h2>
<table>
  <thead>
    <tr><th>Komponen</th><th>Peran</th><th>Koneksi</th></tr>
  </thead>
  <tbody>
    <tr><td><strong>ESP32 + SX1278 (sensor)</strong></td><td>Baca DHT22, kirim paket LoRa</td><td>SPI ke modul LoRa · tanpa WiFi</td></tr>
    <tr><td><strong>ESP32 + SX1278 (receiver)</strong></td><td>Terima paket, tampilkan Serial / siap forward</td><td>SPI ke modul LoRa · opsional WiFi nanti (<a href="/artikel/gateway-lora-mqtt-esp32-sensor-jarak-jauh-dashboard">#28</a>)</td></tr>
  </tbody>
</table>

<p>Alur data secara singkat:</p>
<figure role="img" aria-label="Diagram LoRa peer-to-peer: ESP32 sensor kirim paket LoRa ke receiver, lalu lab Serial atau forward MQTT ke Mosquitto dan Grafana" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 900 340" style="display:block;max-width:900px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="loraArrow" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto">
      <path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/>
    </marker>
    <marker id="loraArrowOrange" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto">
      <path d="M0,0 L8,4 L0,8 Z" fill="#FF7A2F"/>
    </marker>
  </defs>
  <rect x="0" y="0" width="900" height="340" fill="#F5F5F0" rx="6"/>
  <line x1="248" y1="90" x2="318" y2="90" stroke="#FF7A2F" stroke-width="2.5" marker-end="url(#loraArrowOrange)"/>
  <line x1="568" y1="90" x2="638" y2="90" stroke="#2979FF" stroke-width="2.5" marker-end="url(#loraArrow)"/>
  <line x1="760" y1="148" x2="760" y2="208" stroke="#2979FF" stroke-width="2.5" marker-end="url(#loraArrow)"/>
  <rect x="24" y="35" width="224" height="110" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="136" y="65" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">ESP32 Sensor</text>
  <text x="136" y="88" text-anchor="middle" fill="#4A5568" font-size="12">DHT22 + SX1278</text>
  <text x="136" y="108" text-anchor="middle" fill="#718096" font-size="11">suhu · RH · unix</text>
  <text x="136" y="128" text-anchor="middle" fill="#718096" font-size="11">tanpa WiFi</text>
  <rect x="255" y="48" width="78" height="24" rx="4" fill="#fff" stroke="#FF7A2F" stroke-width="1.5"/>
  <text x="294" y="65" text-anchor="middle" fill="#FF7A2F" font-size="11" font-weight="700">LoRa 433 →</text>
  <rect x="328" y="35" width="240" height="110" rx="6" fill="#FFF3E8" stroke="#000" stroke-width="2.5"/>
  <text x="448" y="65" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">ESP32 Receiver</text>
  <text x="448" y="88" text-anchor="middle" fill="#4A5568" font-size="12">SX1278 RX</text>
  <text x="448" y="108" text-anchor="middle" fill="#718096" font-size="11">Serial Monitor (lab)</text>
  <text x="448" y="128" text-anchor="middle" fill="#718096" font-size="11">siap forward gateway</text>
  <rect x="575" y="48" width="78" height="24" rx="4" fill="#fff" stroke="#2979FF" stroke-width="1.5"/>
  <text x="614" y="65" text-anchor="middle" fill="#2979FF" font-size="11" font-weight="700">MQTT →</text>
  <rect x="648" y="35" width="224" height="110" rx="6" fill="#2979FF" stroke="#000" stroke-width="2.5"/>
  <text x="760" y="72" text-anchor="middle" fill="#fff" font-size="14" font-weight="700">Gateway path</text>
  <text x="760" y="94" text-anchor="middle" fill="#e3f2fd" font-size="12">WiFi + publish</text>
  <text x="760" y="116" text-anchor="middle" fill="#cfe4ff" font-size="11">Jalur D gateway</text>
  <rect x="780" y="166" width="100" height="24" rx="4" fill="#fff" stroke="#2979FF" stroke-width="1.5"/>
  <text x="830" y="183" text-anchor="middle" fill="#2979FF" font-size="12" font-weight="700">dash ↓</text>
  <rect x="548" y="218" width="320" height="70" rx="6" fill="#F5F5F0" stroke="#000" stroke-width="2.5"/>
  <text x="708" y="248" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">Mosquitto / Grafana</text>
  <text x="708" y="270" text-anchor="middle" fill="#4A5568" font-size="12">setelah gateway + dashboard Grafana</text>
  <text x="450" y="318" text-anchor="middle" fill="#4A5568" font-size="11">Lab: sensor → LoRa → receiver Serial · Produksi: + WiFi/MQTT lalu dashboard</text>
</svg>
<figcaption style="margin-top:.75rem;font-size:.875rem;color:#4A5568;text-align:center">Diagram LoRa peer-to-peer — sensor kirim paket SX1278 ke receiver; lab berhenti di Serial; lanjut ke Mosquitto/Grafana lewat <a href="/artikel/gateway-lora-mqtt-esp32-sensor-jarak-jauh-dashboard">gateway #28</a>.</figcaption>
</figure>

<h2>Koneksi SX1278 ke ESP32 (SPI)</h2>
<p>Pin umum untuk modul LoRa 433 MHz di ESP32 DevKit (sesuaikan dengan silkscreen modul kamu):</p>

<table>
  <thead>
    <tr><th>Modul LoRa</th><th>ESP32</th></tr>
  </thead>
  <tbody>
    <tr><td>VCC</td><td>3.3V</td></tr>
    <tr><td>GND</td><td>GND</td></tr>
    <tr><td>SCK</td><td>GPIO 5</td></tr>
    <tr><td>MISO</td><td>GPIO 19</td></tr>
    <tr><td>MOSI</td><td>GPIO 27</td></tr>
    <tr><td>SS (NSS)</td><td>GPIO 18</td></tr>
    <tr><td>RST</td><td>GPIO 14</td></tr>
    <tr><td>DIO0</td><td>GPIO 26</td></tr>
  </tbody>
</table>

<blockquote>
  <p><strong>Pro tip:</strong> Gunakan pin yang <strong>sama persis</strong> di sensor dan receiver — mengurangi salah wiring saat debug.</p>
</blockquote>

<h2>Instalasi Library LoRa.h</h2>
<ol>
  <li>Arduino IDE → <strong>Sketch → Include Library → Manage Libraries</strong></li>
  <li>Cari <strong>LoRa</strong> by <em>Sandeep Mistry</em> → Install</li>
  <li>Pastikan board ESP32 sudah terpasang (Board Manager)</li>
</ol>

<h2>Struktur Data Bersama</h2>
<pre><code class="language-cpp">typedef struct __attribute__((packed)) {
  float suhu;
  float kelembaban;
  uint32_t unix;
} lora_packet_t;</code></pre>

<p>Contoh nilai konsisten Seri 2 (sama dengan <a href="/artikel/ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt">NTP #34</a>):</p>
<pre><code>{"suhu":28.5,"kelembaban":65.2,"unix":1782977400,"iso":"2026-07-02T14:30:00"}</code></pre>

<h2>Sketch Sensor — LoRa Sender</h2>
<pre><code class="language-cpp">#include &lt;SPI.h&gt;
#include &lt;LoRa.h&gt;
#include &lt;DHT.h&gt;

#define DHT_PIN 4
#define LORA_FREQ 433E6

DHT dht(DHT_PIN, DHT22);

typedef struct __attribute__((packed)) {
  float suhu;
  float kelembaban;
  uint32_t unix;
} lora_packet_t;

void setup() {
  Serial.begin(115200);
  dht.begin();

  SPI.begin(5, 19, 27, 18);
  LoRa.setPins(18, 14, 26);

  if (!LoRa.begin(LORA_FREQ)) {
    Serial.println("LoRa init gagal!");
    while (true) { delay(1000); }
  }
  LoRa.setSpreadingFactor(7);
  LoRa.setSyncWord(0x12);
}

void loop() {
  lora_packet_t pkt;
  pkt.suhu = dht.readTemperature();
  pkt.kelembaban = dht.readHumidity();
  pkt.unix = 1782977400;

  LoRa.beginPacket();
  LoRa.write((uint8_t*)&amp;pkt, sizeof(pkt));
  LoRa.endPacket();

  Serial.printf("TX suhu=%.1f RH=%.1f\n", pkt.suhu, pkt.kelembaban);
  delay(10000);
}</code></pre>

<h2>Sketch Receiver — LoRa Receiver</h2>
<pre><code class="language-cpp">#include &lt;SPI.h&gt;
#include &lt;LoRa.h&gt;

#define LORA_FREQ 433E6

typedef struct __attribute__((packed)) {
  float suhu;
  float kelembaban;
  uint32_t unix;
} lora_packet_t;

void setup() {
  Serial.begin(115200);

  SPI.begin(5, 19, 27, 18);
  LoRa.setPins(18, 14, 26);

  if (!LoRa.begin(LORA_FREQ)) {
    Serial.println("LoRa init gagal!");
    while (true) { delay(1000); }
  }
  LoRa.setSpreadingFactor(7);
  LoRa.setSyncWord(0x12);
}

void loop() {
  int packetSize = LoRa.parsePacket();
  if (packetSize == sizeof(lora_packet_t)) {
    lora_packet_t pkt;
    LoRa.readBytes((uint8_t*)&amp;pkt, sizeof(pkt));
    Serial.printf("RX suhu=%.1f RH=%.1f unix=%lu\n",
      pkt.suhu, pkt.kelembaban, (unsigned long)pkt.unix);
  }
}</code></pre>

<h2>Parameter LoRa Penting</h2>
<ul>
  <li><strong>Spreading Factor (SF7–SF12)</strong> — SF lebih tinggi = jangkau lebih jauh, data rate lebih lambat</li>
  <li><strong>Sync Word</strong> — harus <strong>sama</strong> di sender &amp; receiver (mis. <code>0x12</code>); seperti "password" ringan antar node</li>
  <li><strong>Bandwidth</strong> — default library sering 125 kHz; jangan ubah di satu sisi saja</li>
  <li><strong>Frequency</strong> — <code>433E6</code> untuk modul 433 MHz; kedua modul wajib identik</li>
</ul>

<h2>Tabel Spreading Factor — Praktis</h2>
<p><strong>Spreading Factor (SF)</strong> menentukan trade-off jangkau vs kecepatan paket. Semua node dalam satu link wajib pakai SF yang <strong>sama</strong> — ubah di satu sisi saja dan receiver tidak akan decode.</p>

<table>
  <thead>
    <tr><th>SF</th><th>Jangkau relatif</th><th>Time on air</th><th>Cocok untuk</th></tr>
  </thead>
  <tbody>
    <tr><td><strong>SF7</strong></td><td>Pendek–menengah</td><td>Ter cepat</td><td>Lab, uji pertama, interval 10 detik</td></tr>
    <tr><td><strong>SF9</strong></td><td>Menengah</td><td>Sedang</td><td>Kebun terbuka, antena standar</td></tr>
    <tr><td><strong>SF11</strong></td><td>Jauh</td><td>Lambat</td><td>Uji jarak maksimum LOS</td></tr>
    <tr><td><strong>SF12</strong></td><td>Paling jauh</td><td>Paling lambat</td><td>Telemetry jarang (tiap 15–60 menit)</td></tr>
  </tbody>
</table>

<p>Mulai uji dengan <code>setSpreadingFactor(7)</code> seperti sketch di atas. Jika paket hilang saat board dijauhkan, naikkan SF bertahap dan catat jarak + RSSI di Serial Monitor receiver (<code>LoRa.packetRssi()</code> opsional). Untuk node baterai, SF lebih tinggi = radio aktif lebih lama per paket — kombinasikan dengan <a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">deep sleep (#11)</a> dan interval kirim yang jarang.</p>

<h2>Antena &amp; Jangkauan</h2>
<ul>
  <li>Gunakan antena yang sesuai band modul (433 MHz dengan modul 433 MHz)</li>
  <li>Line-of-sight (LOS) jauh lebih baik daripada melalui banyak dinding</li>
  <li>Naikkan SF (mis. 10–12) untuk uji jarak jauh — trade-off: paket lebih lambat</li>
  <li>Untuk jarak &lt;200 m dalam bangunan, pertimbangkan <a href="/artikel/esp-now-kirim-data-antar-esp32-tanpa-router-wifi">ESP-NOW (#25)</a> yang lebih sederhana</li>
</ul>

<h2>Deep Sleep + LoRa di Node Sensor</h2>
<p>Pola mirip <a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">deep sleep (#11)</a>: bangun → baca sensor → kirim LoRa → <code>esp_deep_sleep_start()</code>. Init LoRa ulang setiap boot setelah deep sleep.</p>

<p>Contoh interval lapangan: kirim setiap <strong>5 menit</strong> dengan SF9 — baterai 18650 bisa bertahan berhari-hari jika radio dimatikan di antara paket. Receiver tetap powered (USB/solar) karena harus selalu mendengarkan; pola ini sama seperti gateway ESP-NOW di <a href="/artikel/esp-now-kirim-data-antar-esp32-tanpa-router-wifi">#25</a> yang always-on sementara sensor tidur.</p>

<h2>Koneksi DHT22 di Node Sensor</h2>
<p>Di board sensor, DHT22 tetap di GPIO data (mis. GPIO 4) seperti <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">tutorial #5</a>. LoRa modul memakai bus SPI terpisah — tidak bentrok dengan DHT22 selama pin tidak dobel.</p>
<ul>
  <li>DHT22 VCC → 3.3V · GND → GND · DATA → GPIO 4 + resistor pull-up 10kΩ</li>
  <li>LoRa modul VCC → 3.3V (jangan 5V) · wiring SPI sesuai tabel di atas</li>
  <li>Label fisik <strong>SENSOR</strong> vs <strong>RECEIVER</strong> — hindari flash sketch tertukar</li>
</ul>

<h2>Checklist: Kapan Pakai LoRa?</h2>
<ol>
  <li>Sensor &gt;200 m dari gateway dan tidak ada WiFi? → <strong>LoRa</strong></li>
  <li>Data kecil, interval menit/jam? → <strong>LoRa</strong></li>
  <li>Butuh latency &lt;1 detik di dalam ruangan? → <strong><a href="/artikel/esp-now-kirim-data-antar-esp32-tanpa-router-wifi">ESP-NOW (#25)</a></strong></li>
  <li>Butuh dashboard MQTT langsung dari sensor? → <strong><a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">WiFi + MQTT (#7)</a></strong> atau <strong><a href="/artikel/gateway-lora-mqtt-esp32-sensor-jarak-jauh-dashboard">gateway LoRa (#28)</a></strong></li>
</ol>

<h2>Uji Coba (Lab)</h2>
<pre><code class="language-bash"># Serial Monitor receiver @ 115200 — harus muncul RX suhu=... unix=1782977400
# Setelah gateway LoRa→MQTT (#28), uji broker:
mosquitto_sub -h 192.168.1.50 -u kindo_esp32 -P GANTI_PASSWORD_MQTT \
  -t kodingindonesia/esp32/dht22/data -v</code></pre>
<ol>
  <li>Flash sender ke board sensor, receiver ke board kedua</li>
  <li>Buka Serial Monitor 115200 di <strong>kedua</strong> board</li>
  <li>Pastikan <code>LoRa init gagal</code> tidak muncul</li>
  <li>Dalam 10 detik, receiver menampilkan <code>RX suhu=...</code></li>
  <li>Jauhkan board secara bertahap — catat jarak saat paket hilang</li>
  <li>Ubah <code>setSyncWord</code> di satu sisi — data harus berhenti (verifikasi pairing)</li>
</ol>

<h2>FAQ Singkat</h2>
<dl>
  <dt><strong>LoRa sama dengan LoRaWAN?</strong></dt>
  <dd>Tidak. Artikel ini pakai <strong>LoRa radio</strong> point-to-point. LoRaWAN butuh gateway &amp; server khusus — di luar scope tutorial ini.</dd>
  <dt><strong>Bisa kirim ke HP?</strong></dt>
  <dd>Tidak langsung. Butuh receiver ESP32, lalu forward ke MQTT/app (<a href="/artikel/gateway-lora-mqtt-esp32-sensor-jarak-jauh-dashboard">#28</a>).</dd>
  <dt><strong>Modul 433 MHz bisa bicara dengan 868 MHz?</strong></dt>
  <dd>Tidak. Frekuensi hardware harus sama.</dd>
</dl>

<h2>Tips &amp; Troubleshooting</h2>
<ul>
  <li><strong>LoRa init gagal:</strong> Cek wiring SPI, 3.3V (bukan 5V ke modul), pin RST/DIO0</li>
  <li><strong>RX kosong:</strong> Sync word / frequency / SF beda antara sender &amp; receiver</li>
  <li><strong>Data korup:</strong> <code>struct packed</code> harus identik di kedua sketch</li>
  <li><strong>Jangkau pendek:</strong> Antena, orientasi, atau naikkan SF</li>
  <li><strong>Interferensi:</strong> 433 MHz ramai di perkotaan — uji di waktu berbeda atau ganti lokasi</li>
</ul>

<h2>Keamanan &amp; Produksi</h2>
<ul>
  <li>Sync word bukan enkripsi kuat — untuk produksi pertimbangkan AES di payload atau LoRaWAN</li>
  <li>Riset regulasi Kominfo sebelum deploy lapangan skala besar</li>
  <li>Label board SENSOR / RECEIVER + frekuensi modul</li>
</ul>

<h2>Langkah Selanjutnya (Jalur D)</h2>
<ul>
  <li><strong><a href="/artikel/gateway-lora-mqtt-esp32-sensor-jarak-jauh-dashboard">Gateway LoRa → MQTT (#28)</a>:</strong> receiver + WiFi publish ke <code>kodingindonesia/esp32/dht22/data</code> via broker <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Mosquitto (#16)</a> (<code>192.168.1.50</code>, user <code>kindo_esp32</code>)</li>
  <li><strong><a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">Grafana (#19)</a></strong> — visualisasi setelah data masuk MQTT</li>
  <li><strong><a href="/artikel/esp-now-kirim-data-antar-esp32-tanpa-router-wifi">ESP-NOW (#25)</a></strong> — bandingkan untuk node dekat gateway</li>
  <li><strong><a href="/artikel/esp32-cam-streaming-mjpeg-capture-foto-wifi">ESP32-CAM (#27)</a>:</strong> streaming video — kebutuhan berbeda dari telemetry LoRa</li>
  <li>Capstone <strong><a href="/artikel/smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt">greenhouse (#39)</a></strong> — sensor LoRa kebun + pompa MQTT</li>
</ul>

<p>LoRa membuka sensor di ujung lahan yang WiFi dan ESP-NOW tidak jangkau. Lanjutkan Seri 2 di <a href="/artikel">halaman artikel</a> Koding Indonesia.</p>
HTML;
    }
}
