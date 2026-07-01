<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article9Seeder extends Seeder
{
    public function run(): void
    {
        $admin  = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first();

        if (! $admin || ! $iotCat) {
            $this->command->error('User atau kategori tidak ditemukan. Jalankan DatabaseSeeder dulu.');

            return;
        }

        $article = Article::updateOrCreate(
            ['slug' => 'gabungkan-dht22-relay-mqtt-esp32-satu-proyek'],
            [
                'user_id'         => $admin->id,
                'category_id'     => $iotCat->id,
                'title'           => 'Gabungkan DHT22 dan Relay MQTT dalam Satu Proyek ESP32',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'Proyek ESP32 Lengkap: DHT22 + Relay MQTT dalam Satu Sketch',
                'seo_description' => 'Publish suhu DHT22 dan kontrol lampu relay lewat MQTT dalam satu kode ESP32. Tutorial gabungan smart home bahasa Indonesia.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament; hindari wipe saat re-seed

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagSlugs = ['esp32', 'mqtt', 'iot', 'dht22', 'relay', 'smarthome'];
        Tag::updateOrCreate(['slug' => 'dht22'], ['name' => 'dht22']);
        $tagIds   = Tag::whereIn('slug', $tagSlugs)->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command->info('✓ Artikel ke-9 berhasil dipublish: ' . $article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan</h2>
<p>Dari artikel-artikel sebelumnya kita sudah belajar:</p>
<ul>
  <li>Membaca sensor <strong>DHT22</strong> (<a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">tutorial DHT22</a>)</li>
  <li><strong>Publish</strong> data suhu ke MQTT (<a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">artikel MQTT</a>)</li>
  <li><strong>Subscribe</strong> perintah ON/OFF untuk relay lampu (<a href="/artikel/kontrol-lampu-esp32-mqtt-relay">artikel relay</a>)</li>
</ul>
<p>Kali ini kita <strong>menggabungkan keduanya dalam satu sketch</strong> — pola yang dipakai di smart home nyata: ESP32 mengirim data sensor sekaligus menerima perintah kontrol dari broker yang sama.</p>

<p>Hasil akhirnya: kamu bisa lihat suhu di MQTT Explorer <em>dan</em> nyalakan/matikan lampu dari HP, tanpa upload ulang kode yang berbeda.</p>

<h2>Yang Kamu Butuhkan</h2>
<ul>
  <li>ESP32 DevKit + kabel jumper</li>
  <li>Sensor DHT22 + resistor pull-up 10kΩ</li>
  <li>Modul relay 1 channel (5V)</li>
  <li>Lampu kecil untuk latihan (LED/soket aman)</li>
  <li>Library: <strong>DHT sensor library</strong>, <strong>PubSubClient</strong>, <strong>ArduinoJson</strong> (v6 atau v7)</li>
  <li><a href="https://mqtt-explorer.com/" target="_blank" rel="noopener">MQTT Explorer</a></li>
</ul>

<blockquote>
  <p><strong>Keamanan listrik:</strong> Untuk latihan gunakan lampu LED kecil atau lampu desk aman. Jangan menyentuh kabel AC 220V tanpa pengalaman — sama seperti <a href="/artikel/kontrol-lampu-esp32-mqtt-relay">artikel relay</a>.</p>
</blockquote>

<blockquote>
  <p><strong>Prasyarat:</strong> Sudah paham dasar MQTT, wiring DHT22, dan modul relay. Baca <a href="/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32">Membaca Sensor DHT22</a>, <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">MQTT ESP32</a>, dan <a href="/artikel/kontrol-lampu-esp32-mqtt-relay">Kontrol Lampu Relay</a> jika belum.</p>
</blockquote>

<h2>Topologi MQTT Proyek Ini</h2>
<p>Kita pakai broker publik <a href="https://mosquitto.org/" target="_blank" rel="noopener">Eclipse Mosquitto</a> <code>test.mosquitto.org:1883</code> (sama seperti artikel sebelumnya):</p>

<table>
  <thead>
    <tr><th>Arah</th><th>Topic</th><th>Isi pesan</th></tr>
  </thead>
  <tbody>
    <tr><td>ESP32 → Broker (publish)</td><td><code>kodingindonesia/esp32/dht22/data</code></td><td>JSON: <code>{"suhu":28.5,"kelembaban":65.2}</code></td></tr>
    <tr><td>Broker → ESP32 (subscribe)</td><td><code>kodingindonesia/esp32/lampu/kontrol</code></td><td><code>ON</code> atau <code>OFF</code></td></tr>
  </tbody>
</table>

<p><em>Catatan:</em> Di <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">artikel MQTT</a> topic sensor adalah <code>kodingindonesia/esp32/dht22</code> (payload teks). Di proyek gabungan kita pakai subtopic <code>.../dht22/data</code> + JSON agar lebih terstruktur. Topic kontrol relay sama dengan <a href="/artikel/kontrol-lampu-esp32-mqtt-relay">artikel relay</a>.</p>

<blockquote>
  <p><strong>Broker bukan website</strong> — <code>test.mosquitto.org</code> tidak dibuka di browser. Gunakan ESP32 atau <a href="https://mqtt-explorer.com/" target="_blank" rel="noopener">MQTT Explorer</a>.</p>
</blockquote>

<blockquote>
  <p><strong>Pro tip:</strong> Ganti segmen topic dengan nama unik, misalnya <code>kodingindonesia/anton/esp32/...</code>, agar tidak bentrok dengan peserta tutorial lain. Jangan kontrol perangkat produksi lewat broker publik tanpa autentikasi.</p>
</blockquote>

<h2>Wiring Ringkas</h2>
<ul>
  <li><strong>DHT22 DATA</strong> → GPIO 4 (+ pull-up 10kΩ ke 3.3V)</li>
  <li><strong>DHT22 VCC</strong> → 3.3V · <strong>GND</strong> → GND</li>
  <li><strong>Relay IN</strong> → GPIO 26 · <strong>VCC</strong> → 5V · <strong>GND</strong> → GND</li>
</ul>

<h2>Kode Lengkap: Publish Suhu + Subscribe Relay</h2>
<p>Ganti <code>ssid</code> dan <code>password</code>, lalu upload:</p>

<pre><code class="language-arduino">#include &lt;WiFi.h&gt;
#include &lt;PubSubClient.h&gt;
#include &lt;DHT.h&gt;
#include &lt;ArduinoJson.h&gt;

const char* ssid     = "NamaWiFiKamu";
const char* password = "PasswordWiFiKamu";

const char* mqttServer    = "test.mosquitto.org";
const int   mqttPort      = 1883;
const char* topicSensor   = "kodingindonesia/esp32/dht22/data";
const char* topicKontrol    = "kodingindonesia/esp32/lampu/kontrol";

#define DHT_PIN   4
#define DHT_TYPE  DHT22
#define RELAY_PIN 26

const bool RELAY_ON  = LOW;   // sesuaikan jika modul active HIGH
const bool RELAY_OFF = HIGH;

DHT dht(DHT_PIN, DHT_TYPE);
WiFiClient espClient;
PubSubClient mqttClient(espClient);

bool lampuMenyala = false;
unsigned long terakhirPublish = 0;
const unsigned long intervalPublish = 10000; // 10 detik

void setLampu(bool nyala) {
  lampuMenyala = nyala;
  digitalWrite(RELAY_PIN, nyala ? RELAY_ON : RELAY_OFF);
  Serial.println(nyala ? "Lampu: ON" : "Lampu: OFF");
}

void callbackMQTT(char* topic, byte* payload, unsigned int length) {
  String pesan;
  for (unsigned int i = 0; i &lt; length; i++) {
    pesan += (char)payload[i];
  }
  pesan.trim();
  pesan.toUpperCase();

  Serial.print("Perintah di ");
  Serial.print(topic);
  Serial.print(": ");
  Serial.println(pesan);

  if (pesan == "ON") {
    setLampu(true);
  } else if (pesan == "OFF") {
    setLampu(false);
  }
}

void koneksiWiFi() {
  Serial.print("Menghubungkan ke WiFi");
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi terhubung!");
}

void koneksiMQTT() {
  mqttClient.setServer(mqttServer, mqttPort);
  mqttClient.setCallback(callbackMQTT);
  mqttClient.setBufferSize(512);

  while (!mqttClient.connected()) {
    Serial.print("MQTT connect...");
    String clientId = "ESP32-Combo-" + String(random(0xffff), HEX);

    if (mqttClient.connect(clientId.c_str())) {
      Serial.println(" OK");
      mqttClient.subscribe(topicKontrol);
      Serial.print("Subscribe: ");
      Serial.println(topicKontrol);
    } else {
      Serial.print(" gagal rc=");
      Serial.println(mqttClient.state());
      delay(5000);
    }
  }
}

void publishSensor() {
  float suhu = dht.readTemperature();
  float kelembaban = dht.readHumidity();

  if (isnan(suhu) || isnan(kelembaban)) {
    Serial.println("Gagal baca DHT22");
    return;
  }

  StaticJsonDocument&lt;128&gt; doc;
  doc["suhu"] = round(suhu * 10) / 10.0;
  doc["kelembaban"] = round(kelembaban * 10) / 10.0;

  char buffer[128];
  serializeJson(doc, buffer);

  if (mqttClient.publish(topicSensor, buffer)) {
    Serial.print("Publish ");
    Serial.print(topicSensor);
    Serial.print(": ");
    Serial.println(buffer);
  } else {
    Serial.println("Publish gagal");
  }

  // Bonus: matikan lampu otomatis jika suhu &gt; 30°C
  if (suhu &gt; 30.0 &amp;&amp; lampuMenyala) {
    Serial.println("Suhu tinggi — lampu dimatikan otomatis");
    setLampu(false);
  }
}

void setup() {
  Serial.begin(115200);
  randomSeed(micros());
  pinMode(RELAY_PIN, OUTPUT);
  setLampu(false);
  dht.begin();

  koneksiWiFi();
  koneksiMQTT();
  delay(2000); // stabilkan DHT22 sebelum baca pertama
}

void loop() {
  if (WiFi.status() != WL_CONNECTED) {
    koneksiWiFi();
  }
  if (!mqttClient.connected()) {
    koneksiMQTT();
  }
  mqttClient.loop();

  unsigned long sekarang = millis();
  if (sekarang - terakhirPublish &gt;= intervalPublish) {
    terakhirPublish = sekarang;
    publishSensor();
  }
}</code></pre>

<blockquote>
  <p><strong>Library ArduinoJson:</strong> Install via Library Manager — cari <em>ArduinoJson</em> by Benoit Blanchon. Kode di bawah memakai sintaks <strong>v6</strong> (<code>StaticJsonDocument</code>). Jika pakai v7, ganti dengan <code>JsonDocument doc;</code> — lihat <a href="https://arduinojson.org/" target="_blank" rel="noopener">dokumentasi ArduinoJson</a>.</p>
</blockquote>

<h2>Uji Coba</h2>
<ol>
  <li>Upload kode, buka Serial Monitor (115200 baud)</li>
  <li>Pastikan muncul log <code>Publish kodingindonesia/esp32/dht22/data: {"suhu":...}</code> setiap 10 detik</li>
  <li>Buka MQTT Explorer → connect ke <code>test.mosquitto.org:1883</code></li>
  <li>Lihat topic <code>kodingindonesia/esp32/dht22/data</code> — payload JSON suhu &amp; kelembaban</li>
  <li>Publish ke <code>kodingindonesia/esp32/lampu/kontrol</code> pesan <code>ON</code> / <code>OFF</code> → relay bereaksi</li>
  <li>Tiup panas ke DHT22 (atau pegang sensor) — jika suhu &gt; 30°C dan lampu nyala, lampu mati otomatis</li>
</ol>

<h3>Alternatif: mosquitto_sub / mosquitto_pub (Terminal)</h3>
<pre><code class="language-bash"># Lihat data sensor (subscribe)
mosquitto_sub -h test.mosquitto.org -t "kodingindonesia/esp32/dht22/data" -v

# Nyalakan / matikan lampu (publish)
mosquitto_pub -h test.mosquitto.org -t "kodingindonesia/esp32/lampu/kontrol" -m "ON"
mosquitto_pub -h test.mosquitto.org -t "kodingindonesia/esp32/lampu/kontrol" -m "OFF"</code></pre>
<p><em>Tool CLI dari paket <a href="https://mosquitto.org/download/" target="_blank" rel="noopener">Mosquitto</a> — Linux, Mac, dan Windows setelah install Mosquitto.</em></p>

<h2>Alur Program</h2>
<ol>
  <li><code>setup()</code> — WiFi, MQTT connect, subscribe topic kontrol</li>
  <li><code>loop()</code> — <code>mqttClient.loop()</code> memproses pesan subscribe</li>
  <li>Setiap 10 detik — baca DHT22, publish JSON ke topic sensor</li>
  <li><code>callbackMQTT()</code> — terima ON/OFF, ubah relay</li>
  <li>Logika bonus — auto-off lampu saat suhu tinggi</li>
</ol>

<p>Ini pola <strong>edge device</strong> klasik di IoT: telemetri + kontrol dalam satu firmware.</p>

<h2>Tips &amp; Troubleshooting</h2>
<ul>
  <li><strong>Publish OK, subscribe tidak jalan:</strong> Pastikan <code>mqttClient.loop()</code> dipanggil di setiap iterasi <code>loop()</code>.</li>
  <li><strong>JSON terpotong:</strong> Naikkan <code>setBufferSize(512)</code> atau lebih.</li>
  <li><strong>Compile error ArduinoJson:</strong> Pastikan versi library cocok — v6 pakai <code>StaticJsonDocument</code>, v7 pakai <code>JsonDocument</code>.</li>
  <li><strong>rc=-2 saat connect MQTT:</strong> WiFi atau broker tidak terjangkau — cek internet.</li>
  <li><strong>rc=4 (bad credentials):</strong> <code>test.mosquitto.org</code> port 1883 tidak perlu auth.</li>
  <li><strong>ESP32 restart saat relay + WiFi aktif:</strong> Power supply relay terpisah 5V, GND common dengan ESP32.</li>
  <li><strong>Suhu selalu NaN:</strong> Cek wiring DHT22, pull-up 10kΩ, dan tunggu 2 detik setelah <code>dht.begin()</code>.</li>
  <li><strong>Topic tidak muncul di Explorer:</strong> Topic case-sensitive — harus sama persis. Coba wildcard <code>kodingindonesia/esp32/#</code>.</li>
  <li><strong>Ingin interval lebih cepat:</strong> Ubah <code>intervalPublish</code> — jangan terlalu agresif di broker publik (hormati resource bersama).</li>
</ul>

<h2>Langkah Selanjutnya</h2>
<ul>
  <li><a href="/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22">Dashboard hybrid Web Server + MQTT</a> — monitoring lokal &amp; remote dalam satu sketch</li>
  <li>Integrasi <strong><a href="/artikel/home-assistant-integrasi-esp32-mqtt">Home Assistant</a></strong> — sensor MQTT + switch relay di satu dashboard</li>
  <li>Atau pakai <strong><a href="/artikel/esphome-flash-esp32-tanpa-coding-arduino">ESPHome (#22)</a></strong> — node DHT22 + relay tanpa coding Arduino</li>
  <li>Dashboard &amp; otomasi visual dengan <strong><a href="/artikel/node-red-dashboard-otomasi-iot-mqtt-esp32">Node-RED (#23)</a></strong> — flow MQTT drag-and-drop</li>
  <li>Broker <strong>Mosquitto pribadi</strong> di Raspberry Pi dengan username/password</li>
  <li>Simpan histori suhu ke database lewat subscriber Python/Node.js di server</li>
  <li>Pelajari <strong>deep sleep</strong> ESP32 untuk hemat baterai pada node sensor</li>
</ul>

<blockquote>
  <p><strong>Selamat!</strong> Dengan artikel ini, kamu sudah menyelesaikan rangkaian proyek ESP32 + MQTT dari sensor hingga aktuator. Ini fondasi solid sebelum masuk ke platform cloud atau home automation lengkap.</p>
</blockquote>
HTML;
    }
}
