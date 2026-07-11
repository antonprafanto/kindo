<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article33Seeder extends Seeder
{
    public function run(): void
    {
        $admin  = User::first();
        $espCat = Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $espCat) {
            throw new \RuntimeException('User atau kategori tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'kontrol-servo-pwm-esp32-mqtt-gerakan-presisi';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        Tag::updateOrCreate(['slug' => 'pwm'], ['name' => 'pwm']);

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $espCat->id,
                'title'           => 'Kontrol Servo & PWM di ESP32: Gerakan Presisi via MQTT',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'Servo & PWM ESP32 — Kontrol Sudut Presisi via MQTT',
                'seo_description' => 'Tutorial servo SG90 + PWM di ESP32: atur sudut 0–180° lewat MQTT ke broker Mosquitto — melengkapi relay on/off Seri 1 dengan gerakan presisi.',
            ]
        );

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', [
            'esp32', 'servo', 'pwm', 'mqtt', 'iot', 'motor',
        ])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command->info('✓ Artikel ke-33 berhasil dipublish: ' . $article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — Dari On/Off ke Sudut Presisi</h2>
<p>Di Seri 1, aktuator paling sering dipakai adalah <strong>modul relay</strong> — lampu hanya <code>ON</code> atau <code>OFF</code> lewat MQTT (<a href="/artikel/kontrol-lampu-esp32-mqtt-relay">artikel #8</a>). Itu cukup untuk smart switch, tapi tidak cukup untuk <strong>gerakan bertahap</strong>: buka pintu setengah, atur sudut kamera, atau posisi flap greenhouse.</p>

<p>Artikel <strong>Tier 2</strong> ini mengajarkan <strong>servo SG90</strong> dan dasar <strong>PWM</strong> di ESP32: putar lengan servo ke sudut <strong>0–180°</strong> dari perintah MQTT — pola yang melengkapi relay di <a href="/artikel/gabungkan-dht22-relay-mqtt-esp32-satu-proyek">proyek gabungan (#9)</a> dan capstone <a href="/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22">#10</a>.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> Paham <a href="/artikel/blink-led-esp32-tutorial-pertama-embedded-system">GPIO dasar (#3)</a>, <a href="/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">MQTT subscribe (#7)</a>, dan sudah pernah wiring relay <a href="/artikel/kontrol-lampu-esp32-mqtt-relay">#8</a>. Broker sendiri <a href="/artikel/broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">Mosquitto (#16)</a> dipakai di sketch ini.</p>
</blockquote>

<h2>Relay vs Servo vs PWM</h2>
<table>
  <thead>
    <tr><th>Aktuator</th><th>Kontrol</th><th>MQTT payload</th><th>Cocok untuk</th></tr>
  </thead>
  <tbody>
    <tr><td><strong>Relay (#8)</strong></td><td>Digital on/off</td><td><code>ON</code> / <code>OFF</code></td><td>Lampu, pompa, kipas on/off</td></tr>
    <tr><td><strong>Servo SG90</strong></td><td>Sudut 0–180°</td><td><code>0</code> … <code>180</code></td><td>Flap, lengan robot, throttle</td></tr>
    <tr><td><strong>PWM / LEDC</strong></td><td>Duty cycle 0–100%</td><td>Angka atau persen</td><td>Dimmer, kecepatan DC motor kecil</td></tr>
  </tbody>
</table>

<p>Servo SG90 secara internal sudah memakai PWM 50 Hz — library <code>ESP32Servo</code> menyembunyikan detail LEDC. Kamu cukup panggil <code>servo.write(sudut)</code>.</p>

<h2>Hardware — SG90 &amp; ESP32</h2>
<ul>
  <li><strong>Micro servo SG90</strong> (9g, 4,8–6 V)</li>
  <li><strong>ESP32 DevKit</strong></li>
  <li>Kabel jumper · breadboard opsional</li>
  <li>Power supply 5 V eksternal <em>disarankan</em> jika servo + WiFi bersamaan (servo bisa tarik hingga ~650 mA saat stall)</li>
</ul>

<h2>Wiring SG90 ke ESP32</h2>
<table>
  <thead>
    <tr><th>Pin SG90</th><th>Warna umum</th><th>Ke ESP32 / power</th></tr>
  </thead>
  <tbody>
    <tr><td>GND</td><td>Coklat</td><td>GND ESP32</td></tr>
    <tr><td>VCC</td><td>Merah</td><td>5 V (USB/VIN — atau supply 5 V terpisah)</td></tr>
    <tr><td>Signal (PWM)</td><td>Oranye</td><td><strong>GPIO 27</strong></td></tr>
  </tbody>
</table>

<p>Kita pakai <strong>GPIO 27</strong> agar tidak bentrok dengan <strong>GPIO 26</strong> relay (#8) dan <strong>GPIO 4</strong> DHT22 (#5). Hindari GPIO 6–11 (flash internal).</p>

<blockquote>
  <p><strong>Pro tip:</strong> Jika servo bergetar di sudut tertentu, sambungkan kapasitor 100 µF antara VCC dan GND dekat servo — mengurangi noise saat WiFi transmit.</p>
</blockquote>

<h2>Apa Itu PWM di ESP32?</h2>
<p><strong>PWM</strong> (Pulse Width Modulation) mengubah lebar pulsa dalam satu periode tetap. Servo standar mengharapkan pulsa <strong>50 Hz</strong> (periode 20 ms):</p>
<ul>
  <li>~1 ms pulse → sudut ~0°</li>
  <li>~1,5 ms pulse → sudut ~90°</li>
  <li>~2 ms pulse → sudut ~180°</li>
</ul>

<p>ESP32 punya periferal <strong>LEDC</strong> (LED PWM controller) untuk generate sinyal ini. Library <code>ESP32Servo</code> mengalokasikan timer LEDC secara otomatis.</p>

<h2>Library &amp; Dependensi</h2>
<p>Di Arduino IDE, install dari Library Manager:</p>
<ul>
  <li><strong>ESP32Servo</strong> oleh Kevin Harrington</li>
  <li><strong>PubSubClient</strong> + <strong>WiFi</strong> (sudah familiar dari #7/#8)</li>
</ul>

<p>Di <a href="/artikel/migrasi-platformio-esp32-vscode-project-rapi">PlatformIO (#29)</a>:</p>
<pre><code class="language-ini">lib_deps =
  madhephaestus/ESP32Servo
  knolleary/PubSubClient</code></pre>

<h2>Broker &amp; Topic MQTT</h2>
<p>Mengikuti konvensi Seri 2 — broker pribadi <code>192.168.1.50</code>, user <code>kindo_esp32</code>:</p>
<ul>
  <li><strong>Topic sudut servo:</strong> <code>kodingindonesia/esp32/servo/sudut</code></li>
  <li><strong>Payload:</strong> angka bulat <code>0</code>–<code>180</code> (derajat)</li>
  <li><strong>Topic relay (referensi #8):</strong> <code>kodingindonesia/esp32/lampu/kontrol</code> — <code>ON</code>/<code>OFF</code></li>
</ul>

<h2>Sketch Dasar — Sweep Lokal (Tanpa MQTT)</h2>
<p>Uji hardware dulu sebelum WiFi:</p>
<pre><code class="language-cpp">#include &lt;ESP32Servo.h&gt;

#define SERVO_PIN 27

Servo servo;

void setup() {
  Serial.begin(115200);
  ESP32PWM::allocateTimer(0);
  ESP32PWM::allocateTimer(1);
  ESP32PWM::allocateTimer(2);
  ESP32PWM::allocateTimer(3);
  servo.setPeriodHertz(50);
  servo.attach(SERVO_PIN, 500, 2400);
  servo.write(90);
  Serial.println("Servo siap — sweep 0-180");
}

void loop() {
  for (int a = 0; a &lt;= 180; a += 10) {
    servo.write(a);
    Serial.printf("Sudut: %d\n", a);
    delay(300);
  }
  delay(1000);
}</code></pre>

<h2>Sketch Lengkap — MQTT Subscribe Sudut</h2>
<pre><code class="language-cpp">#include &lt;WiFi.h&gt;
#include &lt;PubSubClient.h&gt;
#include &lt;ESP32Servo.h&gt;

#define SERVO_PIN 27

const char* ssid = "GANTI_NAMA_WIFI";
const char* pass = "GANTI_PASSWORD_WIFI";
const char* mqtt_host = "192.168.1.50";
const char* mqtt_user = "kindo_esp32";
const char* mqtt_pass = "GANTI_PASSWORD_MQTT";
const char* topic_servo = "kodingindonesia/esp32/servo/sudut";

Servo servo;
WiFiClient wifiClient;
PubSubClient mqtt(wifiClient);
int currentAngle = 90;

void onMessage(char* topic, byte* payload, unsigned int len) {
  char msg[8];
  size_t n = len &lt; sizeof(msg) - 1 ? len : sizeof(msg) - 1;
  memcpy(msg, payload, n);
  msg[n] = '\0';

  int angle = atoi(msg);
  angle = constrain(angle, 0, 180);
  currentAngle = angle;
  servo.write(angle);
  Serial.printf("MQTT → sudut %d\n", angle);
}

void reconnectMqtt() {
  while (!mqtt.connected()) {
    if (mqtt.connect("kindo-esp32-servo", mqtt_user, mqtt_pass)) {
      mqtt.subscribe(topic_servo);
      Serial.println("MQTT connected + subscribe servo/sudut");
    } else {
      delay(2000);
    }
  }
}

void setup() {
  Serial.begin(115200);
  ESP32PWM::allocateTimer(0);
  ESP32PWM::allocateTimer(1);
  ESP32PWM::allocateTimer(2);
  ESP32PWM::allocateTimer(3);
  servo.setPeriodHertz(50);
  servo.attach(SERVO_PIN, 500, 2400);
  servo.write(90);

  WiFi.begin(ssid, pass);
  while (WiFi.status() != WL_CONNECTED) { delay(300); }

  mqtt.setServer(mqtt_host, 1883);
  mqtt.setCallback(onMessage);
  reconnectMqtt();
}

void loop() {
  if (!mqtt.connected()) reconnectMqtt();
  mqtt.loop();
}</code></pre>

<p>Payload MQTT mengikuti pola JSON sensor di topic <code>kodingindonesia/esp32/dht22/data</code> — di sini sengaja <strong>angka mentah</strong> agar mudah dikirim dari <code>mosquitto_pub</code> atau Node-RED (#23). Untuk timestamp di log, lihat <a href="/artikel/ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt">NTP (#34)</a> — contoh unix <code>1782977400</code> = <code>2026-07-02T14:30:00</code> UTC.</p>

<h2>Mapping Sudut ke Skenario Nyata</h2>
<p>Beberapa contoh mapping praktis untuk proyek IoT:</p>
<table>
  <thead>
    <tr><th>Sudut MQTT</th><th>Contoh mekanik</th><th>Proyek terkait</th></tr>
  </thead>
  <tbody>
    <tr><td><code>0</code></td><td>Flap tertutup penuh</td><td>Greenhouse capstone (#39)</td></tr>
    <tr><td><code>90</code></td><td>Posisi netral / setengah buka</td><td>Kamera tilt</td></tr>
    <tr><td><code>180</code></td><td>Flap terbuka penuh</td><td>Ventilasi maksimum</td></tr>
  </tbody>
</table>

<p>Simpan mapping di dokumentasi tim — MQTT hanya mengirim angka; arti mekanik ditentukan oleh instalasi fisik.</p>

<h2>Integrasi Home Assistant (Opsional)</h2>
<p>Di <a href="/artikel/home-assistant-integrasi-esp32-mqtt">Home Assistant (#21)</a>, buat <strong>MQTT number</strong> atau slider:</p>
<ul>
  <li>Topic state: <code>kodingindonesia/esp32/servo/sudut</code></li>
  <li>Min: 0 · Max: 180 · Step: 1</li>
  <li>Mode: slider di dashboard — gerakkan servo dari UI HA</li>
</ul>

<p>Berbeda dengan switch relay lampu yang hanya dua state — slider HA langsung memetakan ke <code>servo.write()</code> di firmware.</p>

<h2>Uji dengan mosquitto_pub</h2>
<p>Dari laptop yang bisa reach broker <code>192.168.1.50</code>:</p>
<pre><code class="language-bash">mosquitto_pub -h 192.168.1.50 -u kindo_esp32 -P GANTI_PASSWORD_MQTT \
  -t kodingindonesia/esp32/servo/sudut -m 0

mosquitto_pub -h 192.168.1.50 -u kindo_esp32 -P GANTI_PASSWORD_MQTT \
  -t kodingindonesia/esp32/servo/sudut -m 90

mosquitto_pub -h 192.168.1.50 -u kindo_esp32 -P GANTI_PASSWORD_MQTT \
  -t kodingindonesia/esp32/servo/sudut -m 180</code></pre>

<p>Servo harus bergerak ke posisi yang dikirim. Bandingkan dengan relay: <code>mosquitto_pub … -t kodingindonesia/esp32/lampu/kontrol -m ON</code> hanya dua state.</p>

<h2>Kombinasi Servo + Relay di Satu Proyek</h2>
<p>Sketch gabungan bisa subscribe <strong>dua topic</strong>:</p>
<ul>
  <li><code>…/lampu/kontrol</code> → relay GPIO 26 (<a href="/artikel/kontrol-lampu-esp32-mqtt-relay">#8</a>)</li>
  <li><code>…/servo/sudut</code> → servo GPIO 27 (artikel ini)</li>
</ul>

<p>Di callback MQTT, bandingkan <code>strcmp(topic, …)</code> untuk route perintah. Saat proyek membesar, pertimbangkan <a href="/artikel/freertos-esp32-multi-task-sensor-wifi-mqtt">FreeRTOS (#31)</a> — task servo terpisah dari task sensor DHT22.</p>

<h2>LEDC Manual (Opsional)</h2>
<p>Jika butuh PWM murni tanpa servo (mis. dimmer LED), pakai <code>ledcSetup</code> + <code>ledcWrite</code> langsung — tanpa library servo. Servo SG90 lebih praktis lewat <code>ESP32Servo</code> karena kalibrasi pulse width sudah di-handle.</p>

<h2>Gerakan Bertahap (Anti-Jerk)</h2>
<p>Memanggil <code>servo.write(180)</code> langsung dari <code>0</code> bisa membuat lengan servo “loncat” — mekanik flap atau kamera tilt terasa kasar. Untuk demo yang lebih halus, ramp sudut per langkah kecil di firmware:</p>
<pre><code class="language-cpp">void moveServoSmooth(int target) {
  target = constrain(target, 0, 180);
  int step = (target &gt; currentAngle) ? 2 : -2;
  while (currentAngle != target) {
    currentAngle += step;
    if ((step &gt; 0 &amp;&amp; currentAngle &gt; target) ||
        (step &lt; 0 &amp;&amp; currentAngle &lt; target)) {
      currentAngle = target;
    }
    servo.write(currentAngle);
    delay(15);  // ~75–150 ms total untuk lonjakan 90°
  }
}</code></pre>
<p>Panggil <code>moveServoSmooth(angle)</code> dari callback MQTT menggantikan <code>servo.write(angle)</code> langsung. Di produksi, pertimbangkan <code>millis()</code> non-blocking agar <code>mqtt.loop()</code> tetap responsif — pola yang sama dengan task terpisah di <a href="/artikel/freertos-esp32-multi-task-sensor-wifi-mqtt">FreeRTOS (#31)</a>.</p>

<p>Untuk flap greenhouse (#39), ramp 2° per 20 ms sering cukup: flap tidak “membanting” engsel plastik, tapi tetap merespons perintah MQTT dalam waktu kurang dari dua detik untuk perjalanan penuh 0→180°.</p>

<h2>Kalibrasi Pulse Width SG90</h2>
<p>Parameter <code>servo.attach(SERVO_PIN, 500, 2400)</code> memetakan sudut ke lebar pulsa dalam mikrodetik. SG90 murah kadang tidak mencapai 0° atau 180° nominal — jika lengan mentok sebelum angka MQTT, sesuaikan:</p>
<ul>
  <li><strong>0° terlalu dalam:</strong> naikkan nilai minimum (mis. 600 µs bukan 500)</li>
  <li><strong>180° tidak penuh:</strong> naikkan maksimum (mis. 2500 µs)</li>
  <li>Uji dengan <code>mosquitto_pub -m 0</code> dan <code>-m 180</code> sambil amati mekanik — jangan paksa gear saat mentok</li>
</ul>
<p>Catat nilai kalibrasi di README proyek tim; MQTT tetap mengirim 0–180, firmware yang menerjemahkan ke pulse width fisik.</p>

<h2>Daya, Stall Current &amp; Mekanik</h2>
<ul>
  <li>SG90 stall current ~650 mA — USB ESP32 saja bisa drop voltage saat servo + WiFi peak</li>
  <li>Gunakan supply 5 V 1 A terpisah untuk servo; <strong>GND servo dan GND ESP32 harus common</strong></li>
  <li>Jangan memaksa lengan servo saat motor aktif — bisa rusak gear plastik</li>
  <li>Servo continuous rotation (360°) berbeda — butuh library/kalibrasi lain</li>
</ul>

<h2>Keamanan &amp; Produksi</h2>
<ul>
  <li>Ganti <code>GANTI_NAMA_WIFI</code>, <code>GANTI_PASSWORD_WIFI</code>, <code>GANTI_PASSWORD_MQTT</code> — jangan commit ke GitHub</li>
  <li>Validasi sudut di firmware: <code>constrain(angle, 0, 180)</code> — jangan percaya payload MQTT mentah</li>
  <li>Pakai <a href="/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32">MQTT TLS (#17)</a> jika perintah servo mengontrol perangkat fisik berisiko</li>
  <li>Topic kontrol servo sebaiknya tidak retained dengan sudut acak — hindari gerakan tak terduga saat boot</li>
</ul>

<h2>Estimasi Biaya</h2>
<table>
  <thead>
    <tr><th>Komponen</th><th>Harga perkiraan (IDR)</th></tr>
  </thead>
  <tbody>
    <tr><td>ESP32 DevKit</td><td>35.000 – 55.000</td></tr>
    <tr><td>SG90 servo</td><td>18.000 – 30.000</td></tr>
    <tr><td>Breadboard + jumper</td><td>10.000 – 20.000</td></tr>
    <tr><td><strong>Total</strong></td><td><strong>~63.000 – 105.000</strong></td></tr>
  </tbody>
</table>

<h2>Checklist Sebelum Demo</h2>
<ul>
  <li>☐ Sweep lokal 0→180→90 berjalan halus</li>
  <li>☐ WiFi connect + MQTT subscribe <code>servo/sudut</code></li>
  <li>☐ <code>mosquitto_pub -m 0</code>, <code>90</code>, <code>180</code> menggerakkan servo</li>
  <li>☐ GPIO 27 signal · GND common · tidak bentrok GPIO relay/DHT</li>
  <li>☐ Serial mencetak sudut yang diterima</li>
</ul>

<h2>Node-RED &amp; Dashboard Visual</h2>
<p>Di <a href="/artikel/node-red-dashboard-otomasi-iot-mqtt-esp32">Node-RED (#23)</a>, tambahkan node <strong>ui_slider</strong> yang publish ke <code>kodingindonesia/esp32/servo/sudut</code>. Slider 0–180 di browser langsung menggerakkan SG90 — pola mirip tombol ON/OFF relay di dashboard yang sama, tapi dengan kontrol analog.</p>

<p>Kombinasikan dengan chart suhu dari topic <code>kodingindonesia/esp32/dht22/data</code> — misalnya buka flap (sudut 120) otomatis saat suhu &gt; 30°C lewat function node. Itu pratinjau logika capstone <strong>greenhouse (#39)</strong>.</p>

<h2>Publish Balik Sudut (Opsional)</h2>
<p>Untuk monitoring di <a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">Grafana (#19)</a>, ESP32 bisa publish sudut terakhir ke topic <code>kodingindonesia/esp32/servo/status</code> setelah setiap perubahan — payload sama angka 0–180. Subscriber Python (#18) bisa menyimpan histori posisi aktuator, bukan hanya sensor.</p>

<h2>FAQ Singkat</h2>
<dl>
  <dt><strong>Servo tidak bergerak sama sekali?</strong></dt>
  <dd>Cek VCC 5 V, GND common, dan pin signal GPIO 27. Uji sketch sweep lokal dulu.</dd>
  <dt><strong>Servo bergetar di satu sudut?</strong></dt>
  <dd>Normal di beban berat — kurangi beban mekanik atau naikkan supply current.</dd>
  <dt><strong>Bisa kontrol dari Home Assistant?</strong></dt>
  <dd>Ya — buat MQTT number/slider ke topic <code>servo/sudut</code> seperti switch relay di <a href="/artikel/home-assistant-integrasi-esp32-mqtt">#21</a>.</dd>
  <dt><strong>MG996R vs SG90?</strong></dt>
  <dd>MG996R lebih kuat tapi butuh arus lebih besar — wiring MQTT sama, perhatikan power supply.</dd>
</dl>

<h2>Tips &amp; Troubleshooting</h2>
<ul>
  <li><strong>ESP32 reset saat servo gerak:</strong> Power supply lemah — pisahkan 5 V servo atau tambah kapasitor</li>
  <li><strong>MQTT tidak mengubah sudut:</strong> Cek <code>mqtt.setCallback</code> sebelum <code>connect</code>; pastikan <code>mqtt.loop()</code> di <code>loop()</code></li>
  <li><strong>Sudut tidak akurat:</strong> Kalibrasi <code>attach(pin, minUs, maxUs)</code> — SG90 umum 500–2400 µs</li>
  <li><strong>Topic salah:</strong> Harus persis <code>kodingindonesia/esp32/servo/sudut</code> — case sensitive</li>
  <li><strong>Payload bukan angka:</strong> <code>atoi</code> mengembalikan 0 — kirim <code>45</code> bukan <code>"sudut":45</code> di artikel ini</li>
  <li><strong>Konflik timer PWM:</strong> Pastikan <code>allocateTimer(0..3)</code> dipanggil sekali di <code>setup()</code></li>
</ul>

<h2>Langkah Selanjutnya — Tier 2 Seri 2</h2>
<p>Servo melengkapi relay on/off — sekarang aktuator punya <strong>gerakan presisi</strong>. Lanjut ke pelengkap Tier 2:</p>
<ul>
  <li><strong><a href="/artikel/adc-esp32-sensor-analog-soil-moisture-ldr-mqtt">ADC Soil Moisture &amp; LDR (#35)</a>:</strong> sensor analog — melengkapi servo dengan input tanah &amp; cahaya</li>
  <li><strong><a href="/artikel/ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt">NTP (#34)</a></strong> — timestamp di log pergerakan servo</li>
  <li><strong><a href="/artikel/bluetooth-esp32-ble-kirim-data-sensor-smartphone">BLE (#32)</a></strong> — kontrol servo dari app HP (lanjutan)</li>
  <li><strong><a href="/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt">Grafana (#19)</a></strong> — grafik histori sudut jika publish balik ke MQTT</li>
  <li>Capstone <strong>greenhouse (#39)</strong> — servo flap + pompa relay + sensor</li>
</ul>

<p>PWM membuka gerakan halus di proyek ESP32 — lanjutkan di <a href="/artikel">halaman artikel</a> Koding Indonesia.</p>
HTML;
    }
}
