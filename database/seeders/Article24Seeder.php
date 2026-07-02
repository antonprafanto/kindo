<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article24Seeder extends Seeder
{
    public function run(): void
    {
        $admin  = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first();

        if (! $admin || ! $iotCat) {
            throw new \RuntimeException('User atau kategori tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'sensor-gerak-pir-esp32-lampu-mqtt-debounce';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $iotCat->id,
                'title'           => 'Automasi Rumah: Sensor Gerak PIR + Lampu MQTT di ESP32',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'PIR ESP32 MQTT — Lampu Otomatis + Debounce',
                'seo_description' => 'Buat lampu otomatis dengan sensor gerak PIR di ESP32: interrupt, debounce, hold time anti-flicker, dan publish status ke MQTT untuk Home Assistant.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        Tag::updateOrCreate(['slug' => 'pir'], ['name' => 'pir']);
        Tag::updateOrCreate(['slug' => 'nodered'], ['name' => 'nodered']);

        $tagIds = Tag::whereIn('slug', [
            'esp32', 'pir', 'mqtt', 'iot', 'smarthome', 'homeassistant', 'relay', 'nodered',
        ])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command->info('✓ Artikel ke-24 berhasil dipublish: ' . $article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan</h2>
<p>Setelah <a href="/artikel/home-assistant-integrasi-esp32-mqtt">Home Assistant (#21)</a>, <a href="/artikel/esphome-flash-esp32-tanpa-coding-arduino">ESPHome (#22)</a>, dan <a href="/artikel/node-red-dashboard-otomasi-iot-mqtt-esp32">Node-RED (#23)</a>, sekarang kita tambahkan sensor <strong>gerak PIR</strong> — komponen klasik lampu koridor, garasi, dan ruang penyimpanan.</p>

<p>Artikel ini fokus pada firmware ESP32: baca PIR lewat <strong>interrupt</strong>, terapkan <strong>debounce</strong> agar tidak spam event, dan <strong>hold time</strong> (hysteresis) supaya lampu tidak berkedip saat orang diam di ruangan. Status gerak + lampu dipublish ke broker <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Mosquitto pribadi (#16)</a> — siap ditampilkan di HA atau Node-RED.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> Sudah paham <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">MQTT dasar (#7)</a>, <a href="/artikel/kontrol-lampu-esp32-mqtt-relay">kontrol relay MQTT (#8)</a>, broker <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Mosquitto + auth (#16)</a> jalan, dan opsional sudah integrasi <a href="/artikel/home-assistant-integrasi-esp32-mqtt">Home Assistant (#21)</a> atau <a href="/artikel/node-red-dashboard-otomasi-iot-mqtt-esp32">Node-RED (#23)</a>.</p>
</blockquote>

<h2>Yang Kamu Butuhkan</h2>
<ul>
  <li><strong>ESP32</strong> DevKit</li>
  <li><strong>Modul PIR</strong> HC-SR501 (atau setara) — output digital HIGH saat gerak</li>
  <li><strong>Modul relay</strong> 1 channel (sama seperti <a href="/artikel/kontrol-lampu-esp32-mqtt-relay">#8</a>)</li>
  <li>Lampu latihan kecil (LED / lampu desk) — <strong>bukan</strong> AC 220V tanpa pengalaman</li>
  <li>Arduino IDE + library <strong>PubSubClient</strong>, <strong>ArduinoJson</strong></li>
</ul>

<p><strong>Estimasi biaya:</strong> Modul PIR HC-SR501 ~Rp 10–20 rb + relay yang sudah dipakai di proyek sebelumnya.</p>

<h2>Mengapa Debounce &amp; Hold Time?</h2>
<table>
  <thead>
    <tr><th>Masalah</th><th>Penyebab</th><th>Solusi di artikel ini</th></tr>
  </thead>
  <tbody>
    <tr><td>Lampu kedip-kedip cepat</td><td>PIR memicu berkali-kali dalam milidetik</td><td><strong>Debounce</strong> 50 ms setelah interrupt</td></tr>
    <tr><td>Lampu mati saat orang diam</td><td>PIR sudah LOW padahal orang masih di ruangan</td><td><strong>Hold time</strong> (hysteresis) 60 detik sejak gerak terakhir</td></tr>
    <tr><td>Event MQTT berlebihan</td><td>Publish di setiap ping ISR</td><td>Publish hanya saat transisi ON/OFF lampu</td></tr>
  </tbody>
</table>

<h2>Arsitektur: PIR → ESP32 → Mosquitto → Smart Home</h2>
<pre><code>  [ HC-SR501 PIR ]
      |  GPIO interrupt (RISING)
      v
  [ ESP32 ]
      |  relay GPIO 26 → lampu
      |  publish: kodingindonesia/esp32/pir/gerak  (JSON)
      |  subscribe: kodingindonesia/esp32/lampu/kontrol  (ON/OFF/AUTO)
      v
  [ Mosquitto #16 ]
      |
      +-- Home Assistant (#21) — binary_sensor + switch
      +-- Node-RED (#23) — automasi visual</code></pre>

<p><strong>Topic MQTT</strong> (konsisten Seri 1):</p>
<ul>
  <li>Gerak: <code>kodingindonesia/esp32/pir/gerak</code> — JSON <code>{"gerak":true,"lampu":"ON"}</code> / <code>{"gerak":false,"lampu":"OFF"}</code></li>
  <li>Kontrol manual: <code>kodingindonesia/esp32/lampu/kontrol</code> — <code>ON</code> / <code>OFF</code> / <code>AUTO</code> (sama <a href="/artikel/gabungkan-dht22-relay-mqtt-esp32-satu-proyek">#8/#9</a>)</li>
</ul>

<h2>Wiring PIR + Relay</h2>
<ul>
  <li><strong>PIR VCC</strong> → 5V · <strong>GND</strong> → GND · <strong>OUT</strong> → GPIO 27</li>
  <li><strong>Relay IN</strong> → GPIO 26 · <strong>VCC</strong> → 5V · <strong>GND</strong> → GND</li>
  <li>Potensiometer <em>delay</em> &amp; <em>sensitivitas</em> di modul HC-SR501 — atur di hardware (lihat troubleshooting). Mode <strong>retrigger (H)</strong> disarankan agar output tetap HIGH selama ada gerak.</li>
</ul>

<blockquote>
  <p><strong>GPIO aman:</strong> Hindari GPIO 6–11 (flash). GPIO 27 cocok untuk input PIR (modul HC-SR501 output 3.3V/5V). Pin input-only murni di ESP32: GPIO 34–39. Relay tetap di GPIO 26 seperti seri sebelumnya.</p>
</blockquote>

<h2>Kode Lengkap: Interrupt + Debounce + Hold Time</h2>
<p>Ganti WiFi, IP broker, dan password MQTT (sesuai <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">#16</a>):</p>

<pre><code class="language-arduino">#include &lt;WiFi.h&gt;
#include &lt;PubSubClient.h&gt;
#include &lt;ArduinoJson.h&gt;

const char* ssid     = "NamaWiFiKamu";
const char* password = "PasswordWiFiKamu";

const char* mqttServer   = "192.168.1.50";
const int   mqttPort     = 1883;
const char* mqttUser     = "kindo_esp32";
const char* mqttPass     = "GANTI_PASSWORD_MQTT";

const char* topicPir      = "kodingindonesia/esp32/pir/gerak";
const char* topicKontrol  = "kodingindonesia/esp32/lampu/kontrol";

#define PIR_PIN   27
#define RELAY_PIN 26

const bool RELAY_ON  = LOW;
const bool RELAY_OFF = HIGH;

const unsigned long DEBOUNCE_MS = 50;
const unsigned long HOLD_MS     = 60000; // lampu tetap nyala 60 detik setelah gerak terakhir

volatile bool pirFlag = false;
unsigned long lastIsrMs = 0;
unsigned long lastMotionMs = 0;

bool lampuMenyala = false;
bool otomasiAktif = true;

WiFiClient espClient;
PubSubClient mqttClient(espClient);

void publishStatus() {
  bool gerakAktif = digitalRead(PIR_PIN) == HIGH;
  StaticJsonDocument&lt;96&gt; doc;
  doc["gerak"] = gerakAktif;
  doc["lampu"] = lampuMenyala ? "ON" : "OFF";

  char buffer[96];
  serializeJson(doc, buffer);

  if (mqttClient.publish(topicPir, buffer)) {
    Serial.print("Publish ");
    Serial.println(buffer);
  }
}

void setLampu(bool nyala) {
  if (lampuMenyala == nyala) return;
  lampuMenyala = nyala;
  digitalWrite(RELAY_PIN, nyala ? RELAY_ON : RELAY_OFF);
  publishStatus();
  Serial.println(nyala ? "Lampu: ON" : "Lampu: OFF");
}

void IRAM_ATTR pirISR() {
  pirFlag = true;
}

void callbackMQTT(char* topic, byte* payload, unsigned int length) {
  String pesan;
  for (unsigned int i = 0; i &lt; length; i++) {
    pesan += (char)payload[i];
  }
  pesan.trim();
  pesan.toUpperCase();

  if (pesan == "ON") {
    otomasiAktif = false;
    setLampu(true);
  } else if (pesan == "OFF") {
    otomasiAktif = false;
    setLampu(false);
  } else if (pesan == "AUTO") {
    otomasiAktif = true;
    Serial.println("Mode otomasi PIR aktif kembali");
  }
}

void koneksiWiFi() {
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi OK");
}

void koneksiMQTT() {
  mqttClient.setServer(mqttServer, mqttPort);
  mqttClient.setCallback(callbackMQTT);
  mqttClient.setBufferSize(256);

  while (!mqttClient.connected()) {
    if (mqttClient.connect("ESP32-PIR", mqttUser, mqttPass)) {
      mqttClient.subscribe(topicKontrol);
      Serial.println("MQTT OK");
    } else {
      delay(5000);
    }
  }
}

void handlePirEvent() {
  unsigned long now = millis();
  if (now - lastIsrMs &lt; DEBOUNCE_MS) {
    // Gerak berulang cepat — tetap perpanjang hold time jika lampu sudah nyala
    if (otomasiAktif &amp;&amp; lampuMenyala) {
      lastMotionMs = now;
    }
    return;
  }
  lastIsrMs = now;
  lastMotionMs = now;

  if (otomasiAktif &amp;&amp; !lampuMenyala) {
    setLampu(true);
  }
}

void setup() {
  Serial.begin(115200);
  pinMode(RELAY_PIN, OUTPUT);
  pinMode(PIR_PIN, INPUT);
  digitalWrite(RELAY_PIN, RELAY_OFF);

  attachInterrupt(digitalPinToInterrupt(PIR_PIN), pirISR, RISING);

  koneksiWiFi();
  koneksiMQTT();
}

void loop() {
  if (WiFi.status() != WL_CONNECTED) koneksiWiFi();
  if (!mqttClient.connected()) koneksiMQTT();
  mqttClient.loop();

  if (pirFlag) {
    pirFlag = false;
    handlePirEvent();
  }

  if (otomasiAktif &amp;&amp; lampuMenyala) {
    if (digitalRead(PIR_PIN) == HIGH) {
      lastMotionMs = millis(); // pin masih HIGH — perpanjang hold tanpa interrupt baru
    } else if (millis() - lastMotionMs &gt; HOLD_MS) {
      setLampu(false);
    }
  }
}</code></pre>

<h2>Penjelasan Bagian Kritis</h2>
<ol>
  <li><strong><code>IRAM_ATTR pirISR()</code></strong> — ISR sependek mungkin: hanya set flag. Jangan <code>delay()</code> atau MQTT di interrupt.</li>
  <li><strong>Debounce</strong> — <code>DEBOUNCE_MS</code> dicek di <code>handlePirEvent()</code>, bukan di ISR.</li>
  <li><strong>Hold time (hysteresis)</strong> — <code>lastMotionMs</code> diperbarui tiap gerak valid; lampu mati jika sudah <code>HOLD_MS</code> tanpa gerak baru. Saat lampu sudah nyala, gerak berulang (meski kena debounce) tetap memperpanjang <code>lastMotionMs</code>.</li>
  <li><strong>Override manual</strong> — MQTT <code>ON</code>/<code>OFF</code> mematikan otomasi sementara; kirim <code>AUTO</code> untuk kembali ke mode PIR.</li>
  <li><strong>Publish hemat</strong> — <code>setLampu()</code> hanya publish saat status lampu benar-benar berubah; field <code>gerak</code> di JSON dibaca langsung dari pin PIR (<code>digitalRead</code>), bukan disamakan dengan status lampu.</li>
  <li><strong><code>mqttClient.loop()</code></strong> — wajib dipanggil di <code>loop()</code> agar perintah MQTT manual diterima (sama seperti <a href="/artikel/kontrol-lampu-esp32-mqtt-relay">#8</a>).</li>
  <li><strong>Polling pin PIR di <code>loop()</code></strong> — selama <code>digitalRead(PIR_PIN)</code> masih HIGH, <code>lastMotionMs</code> diperbarui. Ini mencegah lampu mati prematur saat orang diam tapi sensor masih mendeteksi gerak (mode retrigger HC-SR501).</li>
</ol>

<blockquote>
  <p><strong>Pro tip:</strong> Untuk ruang ramai (koridor sekolah), naikkan <code>HOLD_MS</code> ke 2–5 menit. Untuk ruang kecil, 30–60 detik biasanya cukup.</p>
</blockquote>

<h2>Integrasi Home Assistant (#21)</h2>
<p>Tambahkan ke <code>configuration.yaml</code> (sesuaikan broker):</p>
<pre><code class="language-yaml">mqtt:
  binary_sensor:
    - name: "ESP32 PIR Gerak"
      unique_id: esp32_pir_gerak_kindo
      state_topic: "kodingindonesia/esp32/pir/gerak"
      value_template: "{{ value_json.gerak }}"
      payload_on: true
      payload_off: false
      device_class: motion</code></pre>

<p>Switch lampu bisa pakai entitas yang sama seperti <a href="/artikel/home-assistant-integrasi-esp32-mqtt">#21</a> — topic <code>lampu/kontrol</code> tidak berubah.</p>

<p><strong>Automasi contoh</strong> — nyalakan lampu saat gerak terdeteksi (Settings → Automations → Edit in YAML):</p>
<pre><code class="language-yaml">alias: Lampu koridor nyala saat PIR
trigger:
  - platform: mqtt
    topic: "kodingindonesia/esp32/pir/gerak"
condition:
  - condition: template
    value_template: "{{ trigger.payload_json.gerak == true }}"
action:
  - service: switch.turn_on
    target:
      entity_id: switch.lampu_esp32_relay</code></pre>

<p>Untuk matikan otomatis, andalkan <code>HOLD_MS</code> di firmware — atau tambah automasi HA dengan <code>for:</code> beberapa menit tanpa gerak.</p>

<h2>Integrasi Node-RED (#23)</h2>
<ol>
  <li><strong>mqtt in</strong> — topic <code>kodingindonesia/esp32/pir/gerak</code></li>
  <li><strong>json</strong> → <strong>function</strong> — filter gerak aktif:
<pre><code class="language-javascript">if (msg.payload.gerak === true) {
    return msg;
}
return null;</code></pre>
  </li>
  <li>Sambungkan ke notifikasi, <strong>ui_text</strong>, atau <strong>mqtt out</strong> — pola wiring sama Langkah 4–6 di <a href="/artikel/node-red-dashboard-otomasi-iot-mqtt-esp32">#23</a></li>
</ol>

<h2>Uji Coba (Checklist)</h2>
<ol>
  <li>Sketch mengarah ke broker <strong>pribadi</strong> <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Mosquitto (#16)</a> — bukan <code>test.mosquitto.org</code></li>
  <li>Upload sketch → Serial Monitor 115200 — WiFi &amp; MQTT harus OK</li>
  <li>Tunggu <strong>warm-up PIR</strong> ~30–60 detik setelah power on (HC-SR501 stabil)</li>
  <li>Gerakkan tangan di depan PIR — lampu nyala + log publish JSON</li>
  <li>Diam 60 detik — lampu mati otomatis (sesuai <code>HOLD_MS</code>)</li>
  <li>Verifikasi dari terminal:
<pre><code class="language-bash">mosquitto_sub -h 192.168.1.50 -p 1883 \
  -u kindo_esp32 -P 'PASSWORD_ANDA' \
  -t "kodingindonesia/esp32/pir/gerak" -v</code></pre>
  </li>
  <li>Override manual:
<pre><code class="language-bash">mosquitto_pub -h 192.168.1.50 -p 1883 \
  -u kindo_esp32 -P 'PASSWORD_ANDA' \
  -t "kodingindonesia/esp32/lampu/kontrol" -m "ON"</code></pre>
<pre><code class="language-bash">mosquitto_pub -h 192.168.1.50 -p 1883 \
  -u kindo_esp32 -P 'PASSWORD_ANDA' \
  -t "kodingindonesia/esp32/lampu/kontrol" -m "OFF"</code></pre>
  </li>
  <li>Kirim <code>AUTO</code> untuk aktifkan mode PIR lagi:
<pre><code class="language-bash">mosquitto_pub -h 192.168.1.50 -p 1883 \
  -u kindo_esp32 -P 'PASSWORD_ANDA' \
  -t "kodingindonesia/esp32/lampu/kontrol" -m "AUTO"</code></pre>
  </li>
  <li>Gerak berulang saat lampu nyala — hold time harus <em>reset</em> (lampu tidak mati prematur)</li>
</ol>

<h2>Tips &amp; Troubleshooting</h2>
<ul>
  <li><strong>PIR selalu HIGH:</strong> Atur potensiometer sensitivitas &amp; delay di modul; jauhkan dari AC / angin panas langsung</li>
  <li><strong>Tidak ada interrupt:</strong> Cek wiring OUT ke GPIO 27; HC-SR501 butuh warm-up ~30–60 detik setelah power on</li>
  <li><strong>Lampu flicker:</strong> Naikkan <code>DEBOUNCE_MS</code> atau <code>HOLD_MS</code>; jangan publish di ISR</li>
  <li><strong>MQTT tidak connect:</strong> ESP32 harus ke broker pribadi (#16) — bukan <code>test.mosquitto.org</code></li>
  <li><strong>Relay tidak klik:</strong> Cek active LOW/HIGH — sama troubleshooting <a href="/artikel/kontrol-lampu-esp32-mqtt-relay">#8</a></li>
  <li><strong>WiFi 2.4 GHz:</strong> ESP32 tidak support jaringan 5 GHz saja</li>
</ul>

<h2>Keamanan &amp; Produksi</h2>
<ul>
  <li>Jangan hardcode password MQTT di sketch yang di-share — gunakan build flag atau <a href="/artikel/nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">NVS (#12)</a></li>
  <li>Sensor gerak + lampu di area publik — pertimbangkan notifikasi HA, bukan hanya lampu lokal</li>
  <li>MQTT over internet → wajib <a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">TLS (#17)</a></li>
</ul>

<h2>Langkah Selanjutnya (Seri 2)</h2>
<ul>
  <li><strong><a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">MQTT TLS (#17)</a></strong> — amankan Mosquitto</li>
  <li><strong>Artikel #18:</strong> Simpan histori event PIR ke <strong>MySQL</strong> via subscriber Python</li>
  <li><strong>Artikel #34:</strong> <strong>NTP</strong> — timestamp akurat di log gerak</li>
  <li>Capstone <strong>greenhouse (#39)</strong> — PIR + sensor + pompa relay</li>
</ul>

<p>Sensor PIR melengkapi Jalur C smart home: dari dashboard HA/Node-RED hingga automasi gerak di firmware ESP32. Lanjutkan di <a href="/artikel">halaman artikel</a> Koding Indonesia.</p>
HTML;
    }
}
