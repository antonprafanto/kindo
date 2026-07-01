<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article8Seeder extends Seeder
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
            ['slug' => 'kontrol-lampu-esp32-mqtt-relay'],
            [
                'user_id'         => $admin->id,
                'category_id'     => $iotCat->id,
                'title'           => 'Kontrol Lampu dengan ESP32 via MQTT dan Modul Relay',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'Tutorial Kontrol Lampu ESP32 MQTT + Relay — Smart Home Dasar',
                'seo_description' => 'Nyalakan dan matikan lampu dari HP atau laptop lewat MQTT. Tutorial ESP32, modul relay, dan broker Mosquitto berbahasa Indonesia.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament; hindari wipe saat re-seed

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagSlugs = ['esp32', 'mqtt', 'iot', 'relay', 'smarthome'];
        $tagIds   = Tag::whereIn('slug', $tagSlugs)->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command->info('✓ Artikel ke-8 berhasil dipublish: ' . $article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan</h2>
<p>Di artikel sebelumnya kita sudah mengirim data sensor DHT22 ke broker MQTT. Kali ini kita naik level: <strong>mengontrol lampu secara nirkabel</strong> lewat MQTT — fondasi smart home yang paling sering dipraktikkan.</p>

<p>ESP32 akan <strong>subscribe</strong> ke topic kontrol. Saat kamu kirim pesan <code>ON</code> atau <code>OFF</code> dari MQTT Explorer (atau HP), relay menyalakan atau mematikan lampu. Tanpa kabel tambahan ke komputer, tanpa buka web server manual.</p>

<h2>Yang Kamu Butuhkan</h2>
<ul>
  <li>ESP32 DevKit</li>
  <li>Modul relay 1 channel (5V, dengan optocoupler)</li>
  <li>Lampu LED atau lampu kecil + soket (untuk latihan aman)</li>
  <li>Kabel jumper</li>
  <li>Arduino IDE + library <strong>PubSubClient</strong></li>
  <li>MQTT Explorer di laptop (opsional di HP)</li>
</ul>

<blockquote>
  <p><strong>Prasyarat:</strong> Sudah paham dasar MQTT dan koneksi WiFi ESP32. Baca dulu <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker"><em>Memahami MQTT dengan ESP32</em></a> dan <a href="/artikel/menghubungkan-esp32-wifi-kirim-data-server"><em>Menghubungkan ESP32 ke WiFi</em></a>. Opsional: <a href="/artikel/membuat-web-server-esp32-monitoring-sensor-dht22"><em>Web Server ESP32 + DHT22</em></a>.</p>
</blockquote>

<blockquote>
  <p><strong>Keamanan listrik:</strong> Tutorial ini memakai lampu LED kecil atau lampu desk 5V–12V yang aman untuk pemula. <strong>Jangan</strong> menyentuh kabel listrik AC 220V tanpa pengalaman. Untuk kontrol lampu rumah AC, gunakan relay khusus AC + pelindung dan aturan kelistrikan yang benar.</p>
</blockquote>

<h2>Wiring Modul Relay ke ESP32</h2>
<p>Modul relay umum (misalnya HW-483 / SRD-05VDC) memiliki pin:</p>
<ul>
  <li><strong>VCC</strong> → 5V ESP32 (atau VIN jika board menyediakan 5V)</li>
  <li><strong>GND</strong> → GND ESP32</li>
  <li><strong>IN</strong> → GPIO 26 ESP32</li>
  <li><strong>COM / NO</strong> → rangkaian lampu (lampu kecil saja untuk latihan)</li>
</ul>

<p>Banyak modul relay <strong>active LOW</strong> — relay aktif saat pin IN menerima sinyal LOW. Kode di bawah sudah menyesuaikan logika ini.</p>

<h2>Broker &amp; Topic MQTT</h2>
<p>Kita pakai test server <a href="https://mosquitto.org/" target="_blank" rel="noopener">Eclipse Mosquitto</a> (sama seperti artikel MQTT sebelumnya):</p>
<ul>
  <li><strong>Broker:</strong> <code>test.mosquitto.org</code> port <code>1883</code></li>
  <li><strong>Topic kontrol:</strong> <code>kodingindonesia/esp32/lampu/kontrol</code></li>
  <li><strong>Pesan:</strong> <code>ON</code> atau <code>OFF</code> (huruf besar, tanpa spasi)</li>
</ul>

<blockquote>
  <p><strong>Broker bukan website</strong> — <code>test.mosquitto.org</code> tidak dibuka di browser. Gunakan ESP32 atau <a href="https://mqtt-explorer.com/" target="_blank" rel="noopener">MQTT Explorer</a>.</p>
</blockquote>

<blockquote>
  <p><strong>Keamanan broker publik:</strong> Siapa saja bisa publish ke topic yang sama di broker test. Pakai topic unik (lihat pro tip di bawah) dan jangan kontrol perangkat produksi lewat broker publik tanpa autentikasi.</p>
</blockquote>

<h2>Kode Program: ESP32 + Relay + MQTT Subscribe</h2>
<p>Ganti <code>ssid</code> dan <code>password</code> WiFi, lalu upload:</p>

<pre><code class="language-arduino">#include &lt;WiFi.h&gt;
#include &lt;PubSubClient.h&gt;

const char* ssid     = "NamaWiFiKamu";
const char* password = "PasswordWiFiKamu";

const char* mqttServer   = "test.mosquitto.org";
const int   mqttPort     = 1883;
const char* topicKontrol = "kodingindonesia/esp32/lampu/kontrol";

#define RELAY_PIN 26
// Banyak modul relay active LOW: LOW = nyala, HIGH = mati
const bool RELAY_ON  = LOW;
const bool RELAY_OFF = HIGH;

WiFiClient espClient;
PubSubClient mqttClient(espClient);

bool lampuMenyala = false;

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

  Serial.print("Pesan di ");
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

  while (!mqttClient.connected()) {
    Serial.print("MQTT connect...");
    String clientId = "ESP32-Relay-" + String(random(0xffff), HEX);

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

void setup() {
  Serial.begin(115200);
  randomSeed(micros());
  pinMode(RELAY_PIN, OUTPUT);
  setLampu(false);

  koneksiWiFi();
  koneksiMQTT();
}

void loop() {
  if (WiFi.status() != WL_CONNECTED) {
    koneksiWiFi();
  }
  if (!mqttClient.connected()) {
    koneksiMQTT();
  }
  mqttClient.loop();
}</code></pre>

<h2>Uji Coba dari MQTT Explorer</h2>
<ol>
  <li>Upload kode ke ESP32, buka Serial Monitor (115200 baud)</li>
  <li>Buka <a href="https://mqtt-explorer.com/" target="_blank" rel="noopener">MQTT Explorer</a> → connect ke <code>test.mosquitto.org:1883</code></li>
  <li>Di panel publish, isi topic: <code>kodingindonesia/esp32/lampu/kontrol</code></li>
  <li>Kirim pesan <code>ON</code> → relay berbunyi klik, lampu menyala</li>
  <li>Kirim pesan <code>OFF</code> → lampu mati</li>
</ol>

<h3>Alternatif: mosquitto_pub (Terminal)</h3>
<pre><code class="language-bash">mosquitto_pub -h test.mosquitto.org -t "kodingindonesia/esp32/lampu/kontrol" -m "ON"
mosquitto_pub -h test.mosquitto.org -t "kodingindonesia/esp32/lampu/kontrol" -m "OFF"</code></pre>
<p><em>Perintah <code>mosquitto_pub</code> dari paket <a href="https://mosquitto.org/download/" target="_blank" rel="noopener">Mosquitto</a> — tersedia di Linux, Mac, dan Windows setelah install Mosquitto.</em></p>

<h2>Cara Kerjanya</h2>
<ol>
  <li>ESP32 connect ke WiFi lalu ke broker MQTT</li>
  <li>ESP32 <strong>subscribe</strong> topic kontrol — menunggu perintah</li>
  <li>Kamu <strong>publish</strong> <code>ON</code>/<code>OFF</code> dari MQTT Explorer</li>
  <li>Fungsi <code>callbackMQTT()</code> dipanggil → relay di GPIO 26 berubah state</li>
</ol>

<p>Ini kebalikan dari artikel sensor sebelumnya (publish data). Di smart home, sering keduanya digabung — lihat <a href="/artikel/gabungkan-dht22-relay-mqtt-esp32-satu-proyek">proyek gabungan DHT22 + relay</a> di artikel berikutnya.</p>

<h2>Tips &amp; Troubleshooting</h2>
<ul>
  <li><strong>Relay tidak klik:</strong> Cek VCC 5V dan GND. Beberapa board butuh power supply terpisah untuk relay.</li>
  <li><strong>Logika terbalik:</strong> Modul kamu mungkin active HIGH — tukar nilai <code>RELAY_ON</code> / <code>RELAY_OFF</code>.</li>
  <li><strong>Pesan tidak diterima:</strong> Topic harus sama persis. Cek Serial Monitor saat publish. Pastikan <code>mqttClient.loop()</code> dipanggil di <code>loop()</code>.</li>
  <li><strong>Pesan <code>on</code> kecil juga OK:</strong> Kode memanggil <code>toUpperCase()</code> — <code>ON</code>/<code>off</code> tetap dikenali.</li>
  <li><strong>rc=-2 saat connect MQTT:</strong> WiFi atau broker tidak terjangkau — cek internet.</li>
  <li><strong>rc=4 (bad credentials):</strong> <code>test.mosquitto.org</code> port 1883 tidak perlu auth.</li>
  <li><strong>ESP32 restart saat relay aktif:</strong> Relay boros arus — gunakan power supply eksternal 5V untuk modul relay, GND disatukan.</li>
  <li><strong>GPIO 26 tidak cocok:</strong> Pin lain yang aman: 25, 27, 32, 33. Hindari GPIO 6–11 (flash) dan GPIO 2 (boot).</li>
</ul>

<h2>Langkah Selanjutnya</h2>
<ul>
  <li><a href="/artikel/gabungkan-dht22-relay-mqtt-esp32-satu-proyek">Gabungkan publish DHT22 + subscribe relay</a> dalam satu sketch ESP32</li>
  <li>Kontrol otomatis: matikan lampu jika suhu &gt; 30°C — sudah ada contoh di artikel gabungan</li>
  <li>Integrasi <strong><a href="/artikel/home-assistant-integrasi-esp32-mqtt">Home Assistant</a></strong> — switch &amp; sensor MQTT native di dashboard smart home</li>
  <li>Alternatif tanpa sketch Arduino: <strong><a href="/artikel/esphome-flash-esp32-tanpa-coding-arduino">ESPHome (#22)</a></strong> — flash ESP32 dari YAML</li>
  <li>Broker <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Mosquitto pribadi</a> di Raspberry Pi dengan autentikasi</li>
</ul>

<blockquote>
  <p><strong>Pro tip:</strong> Ubah topic menjadi unik, misalnya <code>kodingindonesia/anton/esp32/lampu/kontrol</code>, agar tidak ada orang lain yang tidak sengaja mengontrol lampu kamu di broker publik.</p>
</blockquote>
HTML;
    }
}
