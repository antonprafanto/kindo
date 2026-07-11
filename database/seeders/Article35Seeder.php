<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article35Seeder extends Seeder
{
    public function run(): void
    {
        $admin  = User::first();
        $espCat = Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $espCat) {
            throw new \RuntimeException('User atau kategori tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'adc-esp32-sensor-analog-soil-moisture-ldr-mqtt';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        Tag::updateOrCreate(['slug' => 'adc'], ['name' => 'adc']);
        Tag::updateOrCreate(['slug' => 'soil'], ['name' => 'soil']);

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $espCat->id,
                'title'           => 'ADC ESP32: Sensor Analog Soil Moisture & LDR via MQTT',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'ADC ESP32 — Soil Moisture & LDR Sensor Analog MQTT',
                'seo_description' => 'Baca sensor analog soil moisture & LDR di ESP32 lewat ADC 12-bit, kalibrasi basah/kering, publish JSON ke MQTT — melengkapi DHT22 digital di Seri 2.',
            ]
        );

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', [
            'esp32', 'adc', 'sensor', 'mqtt', 'iot', 'soil',
        ])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command->info('✓ Artikel ke-35 berhasil dipublish: ' . $article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — Dari Digital ke Analog</h2>
<p>Di Seri 1, sensor utama adalah <strong>DHT22</strong> — output <strong>digital</strong> suhu &amp; kelembaban udara lewat protokol satu-wire (<a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">artikel #5</a>). Banyak proyek IoT lapangan butuh input lain: <strong>kelembaban tanah</strong> untuk pompa greenhouse dan <strong>tingkat cahaya</strong> (LDR) untuk lampu otomatis atau flash kamera.</p>

<p>Artikel <strong>Tier 2</strong> ini mengajarkan <strong>ADC</strong> (Analog-to-Digital Converter) bawaan ESP32: baca tegangan 0–3,3 V dari modul <strong>soil moisture</strong> dan <strong>LDR</strong>, kalibrasi ke persen, lalu publish JSON ke MQTT — pola yang melengkapi <a href="/artikel/kontrol-servo-pwm-esp32-mqtt-gerakan-presisi">servo (#33)</a> dan relay <a href="/artikel/kontrol-lampu-esp32-mqtt-relay">#8</a> di capstone <a href="/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22">#10</a> dan <strong>greenhouse (#39)</strong>.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> Sudah paham <a href="/artikel/blink-led-esp32-tutorial-pertama-embedded-system">GPIO (#3)</a>, <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">DHT22 (#5)</a>, dan <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">MQTT publish (#7)</a>. Broker <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Mosquitto (#16)</a> dipakai di sketch. Timestamp JSON mengikuti <a href="/artikel/ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt">NTP (#34)</a>.</p>
</blockquote>

<h2>Digital vs Analog di ESP32</h2>
<table>
  <thead>
    <tr><th>Sensor</th><th>Tipe</th><th>Contoh</th><th>API umum</th></tr>
  </thead>
  <tbody>
    <tr><td>DHT22 (#5)</td><td>Digital protokol</td><td>Suhu &amp; RH udara</td><td><code>dht.readTemperature()</code></td></tr>
    <tr><td>BME280 (#13)</td><td>Digital I2C</td><td>Suhu, tekanan, RH</td><td><code>bme.readHumidity()</code></td></tr>
    <tr><td>Soil moisture</td><td><strong>Analog</strong></td><td>Kelembaban media tanam</td><td><code>analogRead()</code></td></tr>
    <tr><td>LDR + resistor</td><td><strong>Analog</strong></td><td>Terang/gelap</td><td><code>analogRead()</code></td></tr>
  </tbody>
</table>

<p>DHT22 tidak bisa menggantikan soil probe — yang diukur adalah <strong>udara</strong>, bukan <strong>tanah</strong>. Untuk greenhouse (#39), kombinasi BME280 + soil moisture + relay pompa adalah pola standar.</p>

<h2>ADC di ESP32 — 12-bit</h2>
<p>ESP32 punya SAR ADC <strong>12-bit</strong> (nilai mentah 0–4095 pada resolusi default). Tegangan referensi sekitar <strong>3,3 V</strong> — jangan kirim &gt;3,3 V ke pin ADC atau modul bisa rusak.</p>
<ul>
  <li><code>analogRead(pin)</code> — baca nilai mentah</li>
  <li><code>analogReadResolution(12)</code> — set resolusi (default sering sudah 12)</li>
  <li><code>map(value, in_min, in_max, 0, 100)</code> — konversi ke persen setelah kalibrasi</li>
</ul>

<h2>Pin ADC Aman dengan WiFi</h2>
<p>Saat WiFi aktif, hindari pin <strong>ADC2</strong> (GPIO 0, 2, 4, 12–15, 25–27) untuk pembacaan analog — bisa konflik. Gunakan pin <strong>ADC1 input-only</strong>:</p>
<table>
  <thead>
    <tr><th>Fungsi</th><th>GPIO</th><th>Catatan</th></tr>
  </thead>
  <tbody>
    <tr><td>Soil moisture (signal)</td><td><strong>GPIO 34</strong></td><td>Input only · ADC1 · tidak bentrok DHT GPIO 4</td></tr>
    <tr><td>LDR (titik tengah divider)</td><td><strong>GPIO 35</strong></td><td>Input only · ADC1</td></tr>
    <tr><td>DHT22 (#5)</td><td>GPIO 4</td><td>Digital — jangan pakai untuk analog</td></tr>
    <tr><td>Relay (#8)</td><td>GPIO 26</td><td>Digital output</td></tr>
    <tr><td>Servo (#33)</td><td>GPIO 27</td><td>PWM</td></tr>
  </tbody>
</table>

<blockquote>
  <p><strong>Pro tip:</strong> GPIO 34 dan 35 tidak punya pull-up internal — untuk LDR pakai voltage divider eksternal, bukan <code>INPUT_PULLUP</code> saja.</p>
</blockquote>

<h2>Hardware — Modul Soil Moisture</h2>
<ul>
  <li><strong>Modul soil moisture</strong> capacitive atau resistive (corrosion-resistant lebih awet)</li>
  <li><strong>ESP32 DevKit</strong></li>
  <li>Kabel jumper · breadboard</li>
  <li>Opsional: pompa mini + relay untuk demo otomasi (#8)</li>
</ul>

<p>Modul umum punya 3 pin: <code>VCC</code>, <code>GND</code>, <code>AOUT</code> (analog out). Supply 3,3 V cukup untuk pembacaan ADC — beberapa modul juga punya pin digital <code>D0</code> (threshold) yang tidak kita pakai di artikel ini.</p>

<h2>Hardware — LDR (Photoresistor)</h2>
<p>LDR mengubah resistansi sesuai cahaya. ESP32 butuh <strong>voltage divider</strong>:</p>
<ul>
  <li>LDR ke <strong>3,3 V</strong></li>
  <li>LDR ke resistor tetap <strong>10 kΩ</strong> ke GND</li>
  <li>Sambungan tengah LDR–resistor → <strong>GPIO 35</strong></li>
</ul>
<p>Makin terang → tegangan di GPIO 35 naik (polaritas bisa dibalik dengan layout divider — catat saat kalibrasi).</p>

<h2>Wiring Soil Moisture ke ESP32</h2>
<table>
  <thead>
    <tr><th>Pin modul</th><th>Ke ESP32</th></tr>
  </thead>
  <tbody>
    <tr><td>VCC</td><td>3V3</td></tr>
    <tr><td>GND</td><td>GND</td></tr>
    <tr><td>AOUT</td><td><strong>GPIO 34</strong></td></tr>
  </tbody>
</table>

<h2>Sketch Dasar — Baca ADC Lokal</h2>
<p>Uji sensor sebelum WiFi:</p>
<pre><code class="language-cpp">#define SOIL_PIN 34
#define LDR_PIN  35

// Kalibrasi soil — ukur di proyekmu, nilai ini contoh!
const int SOIL_DRY  = 3200;  // tanah kering / udara
const int SOIL_WET  = 1400;  // tanah basah

void setup() {
  Serial.begin(115200);
  analogReadResolution(12);
}

int soilPercent(int raw) {
  int pct = map(raw, SOIL_DRY, SOIL_WET, 0, 100);
  return constrain(pct, 0, 100);
}

void loop() {
  int soilRaw = analogRead(SOIL_PIN);
  int ldrRaw  = analogRead(LDR_PIN);
  int soilPct = soilPercent(soilRaw);
  int lightPct = map(ldrRaw, 500, 3500, 0, 100);
  lightPct = constrain(lightPct, 0, 100);

  Serial.printf("Soil raw=%d → %d%% | LDR raw=%d → %d%%\n",
                soilRaw, soilPct, ldrRaw, lightPct);
  delay(2000);
}</code></pre>

<h2>Kalibrasi Soil Moisture</h2>
<ol>
  <li>Celup probe di <strong>air</strong> (atau tanah basah) — catat <code>SOIL_WET</code></li>
  <li>Angkat ke <strong>udara</strong> (kering) — catat <code>SOIL_DRY</code></li>
  <li>Beberapa modul resistive: basah = nilai <em>lebih rendah</em> — balik argumen <code>map()</code> jika perlu</li>
  <li>Simpan konstanta di <code>config.h</code> atau NVS (#12) untuk deploy lapangan</li>
</ol>

<h2>Capacitive vs Resistive Probe</h2>
<p>Modul soil moisture murah di pasar umumnya dua tipe:</p>
<table>
  <thead>
    <tr><th>Tipe</th><th>Cara kerja</th><th>Umur pakai</th></tr>
  </thead>
  <tbody>
    <tr><td><strong>Resistive</strong></td><td>Arus kecil lewat media basah — hantaran listrik</td><td>Korosi elektroda setelah minggu basah terus</td></tr>
    <tr><td><strong>Capacitive</strong></td><td>Mengukur kapasitansi dielektrik tanah</td><td>Lebih awet — disarankan untuk greenhouse (#39)</td></tr>
  </tbody>
</table>
<p>Keduanya keluaran <code>AOUT</code> analog — wiring ke GPIO 34 dan sketch <code>analogRead</code> sama. Bedanya di kalibrasi dan interval penyiraman: jangan siram setiap menit hanya karena ADC turun 1% — pakai hysteresis seperti relay PIR di <a href="/artikel/sensor-gerak-pir-esp32-lampu-mqtt-debounce">artikel #24</a>.</p>

<h2>Filter Noise — Rata-rata Bergerak</h2>
<p>ADC mentah berfluktuasi karena kabel panjang dan WiFi. Ambil <strong>median</strong> atau rata-rata N sampel:</p>
<pre><code class="language-cpp">int readSoilFiltered() {
  const int N = 10;
  long sum = 0;
  for (int i = 0; i &lt; N; i++) {
    sum += analogRead(SOIL_PIN);
    delay(5);
  }
  return (int)(sum / N);
}</code></pre>
<p>Di firmware produksi, jalankan pembacaan ADC di task terpisah seperti contoh <a href="/artikel/freertos-esp32-multi-task-sensor-wifi-mqtt">FreeRTOS (#31)</a> — task sensor tidak memblokir <code>mqtt.loop()</code>.</p>

<h2>Broker &amp; Topic MQTT</h2>
<p>Konvensi Seri 2 — broker <code>192.168.1.50</code>, user <code>kindo_esp32</code>:</p>
<ul>
  <li><strong>Tanah:</strong> <code>kodingindonesia/esp32/tanah/data</code></li>
  <li><strong>Cahaya LDR:</strong> <code>kodingindonesia/esp32/cahaya/data</code></li>
  <li><strong>Referensi DHT22:</strong> <code>kodingindonesia/esp32/dht22/data</code></li>
  <li><strong>Relay pompa (#8):</strong> <code>kodingindonesia/esp32/lampu/kontrol</code> — <code>ON</code>/<code>OFF</code></li>
</ul>

<h2>Payload JSON + Timestamp NTP</h2>
<p>Format konsisten dengan sensor lain — sertakan <code>unix</code> setelah sinkron <a href="/artikel/ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt">NTP (#34)</a>:</p>
<pre><code class="language-json">{
  "kelembaban_tanah": 42,
  "unix": 1782977400
}</code></pre>
<pre><code class="language-json">{
  "cahaya_percent": 78,
  "unix": 1782977400
}</code></pre>
<p>Contoh unix <code>1782977400</code> = <code>2026-07-02T14:30:00</code> UTC — sama di seluruh Seri 2.</p>

<h2>Sketch Lengkap — MQTT Publish Tanah &amp; Cahaya</h2>
<pre><code class="language-cpp">#include &lt;WiFi.h&gt;
#include &lt;PubSubClient.h&gt;

#define SOIL_PIN 34
#define LDR_PIN  35

const char* ssid = "GANTI_NAMA_WIFI";
const char* pass = "GANTI_PASSWORD_WIFI";
const char* mqtt_host = "192.168.1.50";
const char* mqtt_user = "kindo_esp32";
const char* mqtt_pass = "GANTI_PASSWORD_MQTT";
const char* topic_tanah  = "kodingindonesia/esp32/tanah/data";
const char* topic_cahaya = "kodingindonesia/esp32/cahaya/data";

const int SOIL_DRY = 3200;
const int SOIL_WET = 1400;

WiFiClient wifiClient;
PubSubClient mqtt(wifiClient);
unsigned long lastPub = 0;

int soilPercent(int raw) {
  return constrain(map(raw, SOIL_DRY, SOIL_WET, 0, 100), 0, 100);
}

int readFiltered(uint8_t pin) {
  long sum = 0;
  for (int i = 0; i &lt; 10; i++) { sum += analogRead(pin); delay(5); }
  return (int)(sum / 10);
}

void reconnectMqtt() {
  while (!mqtt.connected()) {
    if (mqtt.connect("kindo-esp32-adc", mqtt_user, mqtt_pass)) {
      Serial.println("MQTT connected");
    } else {
      delay(2000);
    }
  }
}

void setup() {
  Serial.begin(115200);
  analogReadResolution(12);
  WiFi.begin(ssid, pass);
  while (WiFi.status() != WL_CONNECTED) { delay(300); }
  mqtt.setServer(mqtt_host, 1883);
  reconnectMqtt();
}

void loop() {
  if (!mqtt.connected()) reconnectMqtt();
  mqtt.loop();

  if (millis() - lastPub &gt; 10000) {
    lastPub = millis();
    int soil = soilPercent(readFiltered(SOIL_PIN));
    int ldr  = constrain(map(readFiltered(LDR_PIN), 500, 3500, 0, 100), 0, 100);

    char bufTanah[64];
    snprintf(bufTanah, sizeof(bufTanah),
             "{\"kelembaban_tanah\":%d,\"unix\":1782977400}", soil);
    mqtt.publish(topic_tanah, bufTanah);

    char bufCahaya[64];
    snprintf(bufCahaya, sizeof(bufCahaya),
             "{\"cahaya_percent\":%d,\"unix\":1782977400}", ldr);
    mqtt.publish(topic_cahaya, bufCahaya);

    Serial.printf("Published tanah=%d%% cahaya=%d%%\n", soil, ldr);
  }
}</code></pre>

<h2>PlatformIO</h2>
<p>Di <a href="/artikel/migrasi-platformio-esp32-vscode-project-rapi">PlatformIO (#29)</a>:</p>
<pre><code class="language-ini">lib_deps =
  knolleary/PubSubClient</code></pre>

<h2>Otomasi Pompa — Tanah Kering + Relay</h2>
<p>Gabungkan dengan <a href="/artikel/kontrol-lampu-esp32-mqtt-relay">relay (#8)</a>: jika <code>kelembaban_tanah &lt; 30</code>, publish <code>ON</code> ke pompa; jika &gt; 60, <code>OFF</code> (hysteresis anti-flicker). Logika bisa di firmware ESP32 atau di <a href="/artikel/node-red-dashboard-otomasi-iot-mqtt-esp32">Node-RED (#23)</a> — function node subscribe <code>tanah/data</code> dan publish ke <code>lampu/kontrol</code>.</p>

<p>Itu pratinjau arsitektur <strong>greenhouse (#39)</strong>: soil moisture → MQTT → pompa relay + servo flap (#33) + grafik <a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">Grafana (#19)</a>.</p>

<h2>Integrasi Home Assistant (Opsional)</h2>
<p>Di <a href="/artikel/home-assistant-integrasi-esp32-mqtt">Home Assistant (#21)</a>:</p>
<ul>
  <li><strong>MQTT sensor</strong> state topic <code>kodingindonesia/esp32/tanah/data</code> · value template <code>{{ value_json.kelembaban_tanah }}</code> · unit <code>%</code></li>
  <li><strong>MQTT sensor</strong> state topic <code>kodingindonesia/esp32/cahaya/data</code> · value template <code>{{ value_json.cahaya_percent }}</code></li>
  <li>Automation: cahaya &lt; 20% → nyalakan lampu relay</li>
</ul>

<h2>Node-RED &amp; Dashboard</h2>
<p>Tambahkan <strong>ui_gauge</strong> di <a href="/artikel/node-red-dashboard-otomasi-iot-mqtt-esp32">Node-RED (#23)</a> untuk <code>kelembaban_tanah</code> dan <code>cahaya_percent</code> — satu dashboard dengan chart suhu dari <code>dht22/data</code>.</p>

<h2>Uji dengan mosquitto_sub</h2>
<pre><code class="language-bash">mosquitto_sub -h 192.168.1.50 -u kindo_esp32 -P GANTI_PASSWORD_MQTT \
  -t kodingindonesia/esp32/tanah/data -v

mosquitto_sub -h 192.168.1.50 -u kindo_esp32 -P GANTI_PASSWORD_MQTT \
  -t kodingindonesia/esp32/cahaya/data -v</code></pre>

<h2>ESP32-CAM &amp; LDR</h2>
<p>Di <a href="/artikel/esp32-cam-streaming-mjpeg-capture-foto-wifi">ESP32-CAM (#27)</a>, LDR bisa mengontrol LED flash otomatis — baca cahaya analog lalu nyalakan GPIO flash saat gelap. Wiring LDR di board kamera terpisah karena GPIO 4 dipakai flash internal.</p>

<h2>Keamanan &amp; Produksi</h2>
<ul>
  <li>Ganti <code>GANTI_NAMA_WIFI</code>, <code>GANTI_PASSWORD_WIFI</code>, <code>GANTI_PASSWORD_MQTT</code></li>
  <li>Jangan commit kredensial ke GitHub</li>
  <li>Kalibrasi soil per media tanam — cocopeat vs tanah kebun beda kurva</li>
  <li>Pakai <a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">MQTT TLS (#17)</a> jika data sensor dipakai keputusan irigasi produksi</li>
  <li>Modul resistive: jangan biarkan probe basah 24/7 tanpa power cycle — korosi lebih cepat</li>
</ul>

<h2>Estimasi Biaya</h2>
<table>
  <thead>
    <tr><th>Komponen</th><th>Harga perkiraan (IDR)</th></tr>
  </thead>
  <tbody>
    <tr><td>ESP32 DevKit</td><td>35.000 – 55.000</td></tr>
    <tr><td>Modul soil moisture</td><td>12.000 – 25.000</td></tr>
    <tr><td>LDR + resistor 10k</td><td>3.000 – 8.000</td></tr>
    <tr><td>Breadboard + jumper</td><td>10.000 – 20.000</td></tr>
    <tr><td><strong>Total</strong></td><td><strong>~60.000 – 108.000</strong></td></tr>
  </tbody>
</table>

<h2>Checklist Sebelum Demo</h2>
<ul>
  <li>☐ Serial menampilkan raw ADC soil &amp; LDR berubah saat kondisi berubah</li>
  <li>☐ Kalibrasi basah/kering tercatat</li>
  <li>☐ WiFi + MQTT publish ke <code>tanah/data</code> dan <code>cahaya/data</code></li>
  <li>☐ <code>mosquitto_sub</code> menerima JSON valid</li>
  <li>☐ GPIO 34/35 · tidak bentrok DHT/relay/servo</li>
</ul>

<h2>FAQ Singkat</h2>
<dl>
  <dt><strong>Soil selalu 0% atau 100%?</strong></dt>
  <dd>Balik atau sesuaikan <code>SOIL_DRY</code> / <code>SOIL_WET</code> — setiap modul berbeda.</dd>
  <dt><strong>LDR tidak berubah?</strong></dt>
  <dd>Cek voltage divider dan GPIO 35 — ukur multimeter di titik tengah.</dd>
  <dt><strong>Bisa gabung DHT22 + soil satu board?</strong></dt>
  <dd>Ya — DHT GPIO 4 digital, soil GPIO 34 analog; publish ke topic terpisah.</dd>
  <dt><strong>Perlu library khusus?</strong></dt>
  <dd>Tidak untuk ADC dasar — cukup <code>analogRead</code> + PubSubClient dari #7.</dd>
</dl>

<h2>Tips &amp; Troubleshooting</h2>
<ul>
  <li><strong>Nilai ADC naik-turun liar:</strong> Tambah filter rata-rata · perpendek kabel · kapasitor 100 nF dekat pin</li>
  <li><strong>WiFi connect tapi ADC aneh:</strong> Pastikan pin ADC1 (34/35), bukan ADC2 saat WiFi on</li>
  <li><strong>MQTT tidak keluar:</strong> Cek <code>mqtt.loop()</code> dan buffer size <code>setBufferSize(256)</code></li>
  <li><strong>Topic kosong di broker:</strong> Case-sensitive — harus persis <code>kodingindonesia/esp32/tanah/data</code></li>
  <li><strong>JSON parse gagal di subscriber:</strong> Hindari koma trailing · validasi dengan <code>mosquitto_sub -v</code></li>
</ul>

<h2>Langkah Selanjutnya — Tier 2 Seri 2</h2>
<p>Input analog melengkapi aktuator servo (#33) dan relay (#8) — sensor tanah siap untuk capstone. Lanjut ke pelengkap Tier 2:</p>
<ul>
  <li><strong>ESP8266 / NodeMCU vs ESP32 (#36):</strong> kapan pakai board murah vs upgrade</li>
  <li><strong><a href="/artikel/ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt">NTP (#34)</a></strong> — ganti unix contoh dengan waktu nyata</li>
  <li><strong><a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">Subscriber Python (#18)</a></strong> — simpan histori kelembaban tanah ke MySQL</li>
  <li><strong><a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">Grafana (#19)</a></strong> — grafik tanah vs cahaya vs suhu DHT</li>
  <li>Capstone <strong>greenhouse (#39)</strong> — BME280 + soil + pompa + dashboard</li>
</ul>

<p>ADC membuka sensor analog murah di proyek ESP32 — lanjutkan di <a href="/artikel">halaman artikel</a> Koding Indonesia.</p>
HTML;
    }
}
