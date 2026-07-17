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
<p>Di <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">artikel MQTT (#7)</a> kita sudah <strong>publish</strong> data sensor ke broker. Kali ini kita naik level: <strong>mengontrol lampu secara nirkabel</strong> lewat MQTT — fondasi smart home yang paling sering dipraktikkan.</p>

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
  <p><strong>Prasyarat:</strong> Sudah paham dasar MQTT dan koneksi WiFi ESP32. Baca dulu <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">Memahami MQTT dengan ESP32 (#7)</a> dan <a href="/artikel/menghubungkan-esp32-wifi-kirim-data-server">Menghubungkan ESP32 ke WiFi (#4)</a>. Opsional: <a href="/artikel/membuat-web-server-esp32-monitoring-sensor-dht22">Web Server ESP32 + DHT22 (#6)</a>.</p>
</blockquote>

<blockquote>
  <p><strong>Keamanan listrik:</strong> Tutorial ini memakai lampu LED kecil atau lampu desk 5V–12V yang aman untuk pemula. <strong>Jangan</strong> menyentuh kabel listrik AC 220V tanpa pengalaman. Untuk kontrol lampu rumah AC, gunakan relay khusus AC + pelindung dan aturan kelistrikan yang benar.</p>
</blockquote>

<h2>Topologi MQTT — Subscribe Kontrol</h2>
<p>Alur kebalikan dari <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">publish sensor (#7)</a>: kamu yang publish perintah, ESP32 yang subscribe.</p>

<figure role="img" aria-label="Diagram topologi MQTT: publish ON OFF dari Explorer ke broker, ESP32 subscribe lalu kontrol relay GPIO 26" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 620 360" style="display:block;max-width:620px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="g8ArrO" markerWidth="10" markerHeight="10" refX="9" refY="5" orient="auto" markerUnits="userSpaceOnUse"><path d="M0,0 L10,5 L0,10 Z" fill="#FF7A2F"/></marker>
    <marker id="g8ArrB" markerWidth="10" markerHeight="10" refX="9" refY="5" orient="auto" markerUnits="userSpaceOnUse"><path d="M0,0 L10,5 L0,10 Z" fill="#2979FF"/></marker>
    <marker id="g8ArrK" markerWidth="10" markerHeight="10" refX="9" refY="5" orient="auto" markerUnits="userSpaceOnUse"><path d="M0,0 L10,5 L0,10 Z" fill="#1a1a1a"/></marker>
  </defs>
  <rect x="0" y="0" width="620" height="360" fill="#F5F5F0" rx="6"/>
  <!-- MQTT Explorer -->
  <rect x="30" y="30" width="170" height="60" rx="6" fill="#FFF3E8" stroke="#FF7A2F" stroke-width="2.5"/>
  <text x="115" y="55" text-anchor="middle" fill="#1a1a1a" font-size="13" font-weight="700">MQTT Explorer</text>
  <text x="115" y="73" text-anchor="middle" fill="#4A5568" font-size="10">publish ON / OFF</text>
  <!-- Broker -->
  <rect x="300" y="30" width="260" height="60" rx="6" fill="#2979FF" stroke="#000" stroke-width="2.5"/>
  <text x="430" y="55" text-anchor="middle" fill="#fff" font-size="13" font-weight="700">Mosquitto · :1883</text>
  <text x="430" y="73" text-anchor="middle" fill="#e3f2fd" font-size="10">test.mosquitto.org</text>
  <line x1="200" y1="60" x2="298" y2="60" stroke="#FF7A2F" stroke-width="2.5" marker-end="url(#g8ArrO)"/>
  <rect x="210" y="28" width="70" height="20" rx="10" fill="#FFF3E8" stroke="#FF7A2F" stroke-width="1.5"/>
  <text x="245" y="42" text-anchor="middle" fill="#C45A11" font-size="9" font-weight="700">PUB</text>
  <!-- ESP32 -->
  <rect x="80" y="160" width="200" height="70" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="180" y="188" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">ESP32 subscribe</text>
  <text x="180" y="208" text-anchor="middle" fill="#4A5568" font-size="11">callbackMQTT()</text>
  <line x1="360" y1="90" x2="220" y2="158" stroke="#2979FF" stroke-width="2.5" marker-end="url(#g8ArrB)"/>
  <rect x="280" y="110" width="70" height="20" rx="10" fill="#E8F4FF" stroke="#2979FF" stroke-width="1.5"/>
  <text x="315" y="124" text-anchor="middle" fill="#2979FF" font-size="9" font-weight="700">SUB</text>
  <!-- Relay -->
  <rect x="380" y="160" width="180" height="70" rx="6" fill="#C8E6C9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="470" y="188" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">Relay · GPIO 26</text>
  <text x="470" y="208" text-anchor="middle" fill="#4A5568" font-size="11">active LOW</text>
  <line x1="280" y1="195" x2="378" y2="195" stroke="#1a1a1a" stroke-width="2.5" marker-end="url(#g8ArrK)"/>
  <!-- Topic pill -->
  <rect x="150" y="270" width="320" height="28" rx="14" fill="#FFF3E8" stroke="#FF7A2F" stroke-width="1.5"/>
  <text x="310" y="288" text-anchor="middle" fill="#C45A11" font-size="11" font-weight="700">topic · .../lampu/kontrol</text>
  <text x="310" y="335" text-anchor="middle" fill="#4A5568" font-size="11">perintah masuk · aktuator keluar · satu broker</text>
</svg>
<figcaption style="margin-top:.75rem;font-size:.875rem;color:#4A5568;text-align:center">Kamu publish dari Explorer → broker → ESP32 subscribe → relay. Pola kebalikan <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">MQTT publish (#7)</a>.</figcaption>
</figure>

<h2>Wiring Modul Relay ke ESP32</h2>
<p>Modul relay umum (misalnya HW-483 / SRD-05VDC). Pin konsisten dengan proyek gabungan <a href="/artikel/gabungkan-dht22-relay-mqtt-esp32-satu-proyek">DHT22 + relay (#9)</a>:</p>

<figure role="img" aria-label="Diagram wiring ESP32 ke modul relay: 5V, GND, dan GPIO 26 ke IN" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 620 340" style="display:block;max-width:620px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="w8V" markerWidth="9" markerHeight="9" refX="8" refY="4.5" orient="auto" markerUnits="userSpaceOnUse"><path d="M0,0 L9,4.5 L0,9 Z" fill="#E65100"/></marker>
    <marker id="w8K" markerWidth="9" markerHeight="9" refX="8" refY="4.5" orient="auto" markerUnits="userSpaceOnUse"><path d="M0,0 L9,4.5 L0,9 Z" fill="#1a1a1a"/></marker>
    <marker id="w8P" markerWidth="9" markerHeight="9" refX="8" refY="4.5" orient="auto" markerUnits="userSpaceOnUse"><path d="M0,0 L9,4.5 L0,9 Z" fill="#7B1FA2"/></marker>
  </defs>
  <rect x="0" y="0" width="620" height="340" fill="#F5F5F0" rx="6"/>
  <!-- ESP32 -->
  <rect x="30" y="40" width="170" height="220" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="115" y="68" text-anchor="middle" fill="#1a1a1a" font-size="13" font-weight="700">ESP32 DevKit</text>
  <circle cx="185" cy="110" r="5" fill="#E65100"/>
  <text x="170" y="115" text-anchor="end" fill="#1a1a1a" font-size="11" font-weight="600">5V</text>
  <circle cx="185" cy="160" r="5" fill="#1a1a1a"/>
  <text x="170" y="165" text-anchor="end" fill="#1a1a1a" font-size="11" font-weight="600">GND</text>
  <circle cx="185" cy="210" r="5" fill="#7B1FA2"/>
  <text x="170" y="207" text-anchor="end" fill="#1a1a1a" font-size="11" font-weight="600">GPIO 26</text>
  <text x="170" y="221" text-anchor="end" fill="#4A5568" font-size="9">IN</text>
  <!-- Relay -->
  <rect x="400" y="70" width="190" height="180" rx="6" fill="#FFF3E8" stroke="#FF7A2F" stroke-width="2.5"/>
  <text x="495" y="98" text-anchor="middle" fill="#1a1a1a" font-size="13" font-weight="700">Relay 1ch</text>
  <text x="495" y="116" text-anchor="middle" fill="#4A5568" font-size="10">modul 5V · active LOW</text>
  <circle cx="415" cy="150" r="5" fill="#E65100"/>
  <text x="430" y="155" fill="#1a1a1a" font-size="11" font-weight="600">VCC</text>
  <circle cx="415" cy="190" r="5" fill="#1a1a1a"/>
  <text x="430" y="195" fill="#1a1a1a" font-size="11" font-weight="600">GND</text>
  <circle cx="415" cy="230" r="5" fill="#7B1FA2"/>
  <text x="430" y="235" fill="#1a1a1a" font-size="11" font-weight="600">IN</text>
  <!-- Wires ortogonal pin-ke-pin -->
  <polyline fill="none" points="190,110 300,110 300,150 410,150" stroke="#E65100" stroke-width="2.5" marker-end="url(#w8V)"/>
  <polyline fill="none" points="190,160 320,160 320,190 410,190" stroke="#1a1a1a" stroke-width="2.5" marker-end="url(#w8K)"/>
  <polyline fill="none" points="190,210 340,210 340,230 410,230" stroke="#7B1FA2" stroke-width="2.5" marker-end="url(#w8P)"/>
  <text x="30" y="300" fill="#4A5568" font-size="10">5V→VCC · GND→GND · GPIO26→IN · COM/NO ke rangkaian lampu kecil</text>
  <text x="30" y="320" fill="#4A5568" font-size="10">Relay boros arus: supply 5V terpisah + GND common jika ESP32 restart</text>
</svg>
<figcaption style="margin-top:.75rem;font-size:.875rem;color:#4A5568;text-align:center">Wiring pin-ke-pin: VCC 5V, GND bersama, IN di GPIO 26.</figcaption>
</figure>

<ul>
  <li><strong>VCC</strong> → 5V ESP32 (atau VIN jika board menyediakan 5V)</li>
  <li><strong>GND</strong> → GND ESP32</li>
  <li><strong>IN</strong> → GPIO 26 ESP32</li>
  <li><strong>COM / NO</strong> → rangkaian lampu (lampu kecil saja untuk latihan)</li>
</ul>

<p>Banyak modul relay <strong>active LOW</strong> — relay aktif saat pin IN menerima sinyal LOW. Kode di bawah sudah menyesuaikan logika ini.</p>

<h2>Broker &amp; Topic MQTT</h2>
<p>Kita pakai test server <a href="https://mosquitto.org/" target="_blank" rel="noopener">Eclipse Mosquitto</a> (sama seperti <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">artikel MQTT (#7)</a>):</p>
<ul>
  <li><strong>Broker:</strong> <code>test.mosquitto.org</code> port <code>1883</code></li>
  <li><strong>Topic kontrol:</strong> <code>kodingindonesia/esp32/lampu/kontrol</code></li>
  <li><strong>Pesan:</strong> <code>ON</code> atau <code>OFF</code> (huruf besar, tanpa spasi)</li>
</ul>

<blockquote>
  <p><strong>Broker bukan website</strong> — <code>test.mosquitto.org</code> tidak dibuka di browser. Gunakan ESP32 atau <a href="https://mqtt-explorer.com/" target="_blank" rel="noopener">MQTT Explorer</a>.</p>
</blockquote>

<blockquote>
  <p><strong>Keamanan broker publik:</strong> Siapa saja bisa publish ke topic yang sama di broker test. Pakai topic unik (lihat pro tip di bawah) dan jangan kontrol perangkat produksi lewat broker publik tanpa autentikasi. Untuk produksi, lanjut ke <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Mosquitto pribadi (#16)</a> + <a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">TLS (#17)</a>.</p>
</blockquote>

<h2>Kode Program: ESP32 + Relay + MQTT Subscribe</h2>
<p>Ganti placeholder <code>GANTI_SSID_WIFI</code> / <code>GANTI_PASSWORD_WIFI</code>, lalu upload. Untuk produksi tanpa hardcode, lihat <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">NVS + WiFiManager (#12)</a>.</p>

<pre><code class="language-arduino">#include &lt;WiFi.h&gt;
#include &lt;PubSubClient.h&gt;

const char* ssid     = "GANTI_SSID_WIFI";
const char* password = "GANTI_PASSWORD_WIFI";

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

<p>Ini kebalikan dari <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">artikel sensor MQTT (#7)</a> (publish data). Di smart home, sering keduanya digabung — lihat <a href="/artikel/gabungkan-dht22-relay-mqtt-esp32-satu-proyek">proyek gabungan DHT22 + relay (#9)</a>.</p>

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
  <li><a href="/artikel/gabungkan-dht22-relay-mqtt-esp32-satu-proyek">Gabungkan publish DHT22 + subscribe relay (#9)</a> dalam satu sketch ESP32</li>
  <li>Kontrol otomatis: matikan lampu jika suhu &gt; 30°C — sudah ada contoh di <a href="/artikel/gabungkan-dht22-relay-mqtt-esp32-satu-proyek">artikel gabungan (#9)</a></li>
  <li>Integrasi <a href="/artikel/home-assistant-integrasi-esp32-mqtt">Home Assistant (#21)</a> — switch &amp; sensor MQTT native di dashboard smart home</li>
  <li>Alternatif tanpa sketch Arduino: <a href="/artikel/esphome-flash-esp32-tanpa-coding-arduino">ESPHome (#22)</a> — flash ESP32 dari YAML</li>
  <li>Dashboard &amp; otomasi visual: <a href="/artikel/node-red-dashboard-otomasi-iot-mqtt-esp32">Node-RED (#23)</a> — kontrol relay lewat flow MQTT</li>
  <li>Automasi gerak: <a href="/artikel/sensor-gerak-pir-esp32-lampu-mqtt-debounce">PIR + lampu MQTT (#24)</a> — interrupt &amp; debounce</li>
  <li>Broker <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Mosquitto pribadi (#16)</a> di Raspberry Pi dengan autentikasi</li>
  <li>Deploy remote / internet: amankan MQTT dengan <a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">TLS port 8883 (#17)</a> — QoS, LWT &amp; retained</li>
</ul>

<blockquote>
  <p><strong>Pro tip:</strong> Ubah topic menjadi unik, misalnya <code>kodingindonesia/anton/esp32/lampu/kontrol</code>, agar tidak ada orang lain yang tidak sengaja mengontrol lampu kamu di broker publik.</p>
</blockquote>

<h2>Langkah Selanjutnya — Gerakan Presisi (Seri 2)</h2>
<p>Relay hanya on/off — untuk sudut presisi (flap, lengan robot), lanjut ke <a href="/artikel/kontrol-servo-pwm-esp32-mqtt-gerakan-presisi">Kontrol Servo &amp; PWM via MQTT (#33)</a>: SG90 0–180° lewat topic <code>kodingindonesia/esp32/servo/sudut</code>.</p>
HTML;
    }
}
