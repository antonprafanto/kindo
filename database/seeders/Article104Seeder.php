<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article104Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@kodingindonesia.com')->first() ?? User::first();
        $category = Category::where('slug', 'iot-smart-device')->first() ?? Category::where('slug', 'esp32-arduino')->first();
        if (! $admin || ! $category) {
            throw new \RuntimeException('User atau kategori IoT tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        foreach (['fullstack-iot', 'iot', 'mqtt', 'esp32', 'dht22', 'json'] as $tagSlug) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagSlug]);
        }

        $article = Article::updateOrCreate(['slug' => 'fullstack-iot-esp32-dht22-mqtt-json-telemetry'], [
            'user_id' => $admin->id,
            'category_id' => $category->id,
            'title' => 'ESP32 kirim telemetry DHT22 ke MQTT sebagai JSON',
            'title_en' => 'ESP32 sends DHT22 telemetry to MQTT as JSON',
            'excerpt' => 'FS-34 / #104: hubungkan DHT22 ke ESP32, kirim suhu serta kelembapan sebagai JSON ke Mosquitto lokal, lalu lihat pesan hidup di MQTTX.',
            'excerpt_en' => 'FS-34 / #104: wire DHT22 to ESP32, send temperature and humidity as JSON to local Mosquitto, then watch live messages in MQTTX.',
            'body' => $this->body(),
            'body_en' => $this->bodyEn(),
            'status' => 'draft',
            'is_featured' => false,
            'published_at' => null,
            'seo_title' => 'ESP32 DHT22 ke MQTT JSON di Mosquitto Lokal — FS-34',
            'seo_title_en' => 'ESP32 DHT22 to Local Mosquitto MQTT JSON — FS-34',
            'seo_description' => 'Panduan pemula mengirim telemetry DHT22 dari ESP32 sebagai JSON ke Mosquitto lokal dan melihatnya di MQTTX.',
            'seo_description_en' => 'Beginner guide to send DHT22 telemetry from ESP32 as JSON to local Mosquitto and watch it in MQTTX.',
            'cover_image' => 'https://kodingindonesia.com/images/fsiot/fs34-cover-telemetry.webp',
        ]);

        $article->tags()->sync(Tag::whereIn('slug', ['fullstack-iot', 'iot', 'mqtt', 'esp32', 'dht22', 'json'])->pluck('id'));
    }

    private function figure(string $file, string $alt, string $caption): string
    {
        return '<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem"><img src="/images/fsiot/'.$file.'" alt="'.$alt.'" loading="eager" style="width:100%;height:auto;max-height:680px;object-fit:contain;border-radius:6px;background:#F5F5F0"><figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a">'.$caption.'</figcaption></figure>';
    }

    private function sketch(): string
    {
        return implode("\n", [
            '#include <WiFi.h>',
            '#include <ArduinoMqttClient.h>',
            '#include <ArduinoJson.h>',
            '#include <DHT.h>',
            '',
            'const char WIFI_SSID[] = "GANTI_NAMA_WIFI";',
            'const char WIFI_PASSWORD[] = "GANTI_SANDI_WIFI";',
            'const char MQTT_HOST[] = "192.168.1.23";  // Ganti dengan IPv4 PC dari ipconfig',
            'const int MQTT_PORT = 1883;',
            'const char DEVICE_ID[] = "esp32-meja-01";',
            'const char TOPIC_TELEMETRY[] = "kodingindonesia/fsiot/esp32-meja-01/telemetry";',
            '',
            'const byte DHT_PIN = 4;',
            'const unsigned long PUBLISH_INTERVAL_MS = 5000UL;',
            '',
            'DHT dht(DHT_PIN, DHT22);',
            'WiFiClient wifiClient;',
            'MqttClient mqttClient(wifiClient);',
            'unsigned long lastPublishAt = 0;',
            'unsigned long lastWifiAttemptAt = 0;',
            'unsigned long lastMqttAttemptAt = 0;',
            '',
            'void connectWifi() {',
            '  if (WiFi.status() == WL_CONNECTED) return;',
            '  if (lastWifiAttemptAt != 0 && millis() - lastWifiAttemptAt < 10000UL) return;',
            '  lastWifiAttemptAt = millis();',
            '  Serial.print("Menghubungkan Wi-Fi");',
            '  WiFi.mode(WIFI_STA);',
            '  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);',
            '}',
            '',
            'bool connectMqttIfNeeded() {',
            '  if (mqttClient.connected()) return true;',
            '  if (millis() - lastMqttAttemptAt < 5000UL) return false;',
            '  lastMqttAttemptAt = millis();',
            '  Serial.print("Menghubungkan MQTT ke ");',
            '  Serial.println(MQTT_HOST);',
            '  mqttClient.setId(DEVICE_ID);',
            '  if (!mqttClient.connect(MQTT_HOST, MQTT_PORT)) {',
            '    Serial.print("MQTT gagal. Kode: ");',
            '    Serial.println(mqttClient.connectError());',
            '    return false;',
            '  }',
            '  Serial.println("MQTT tersambung.");',
            '  return true;',
            '}',
            '',
            'void publishTelemetry() {',
            '  float humidity = dht.readHumidity();',
            '  float temperature = dht.readTemperature();',
            '  if (isnan(humidity) || isnan(temperature)) {',
            '    Serial.println("DHT22 belum terbaca. Periksa kabel.");',
            '    return;',
            '  }',
            '  JsonDocument data;',
            '  data["device_id"] = DEVICE_ID;',
            '  data["temperature_c"] = temperature;',
            '  data["humidity_pct"] = humidity;',
            '  String payload;',
            '  serializeJson(data, payload);',
            '  mqttClient.beginMessage(TOPIC_TELEMETRY);',
            '  mqttClient.print(payload);',
            '  if (mqttClient.endMessage()) {',
            '    Serial.print("Terkirim: ");',
            '    Serial.println(payload);',
            '  } else {',
            '    Serial.println("Pesan belum terkirim.");',
            '  }',
            '}',
            '',
            'void setup() {',
            '  Serial.begin(115200);',
            '  dht.begin();',
            '  connectWifi();',
            '}',
            '',
            'void loop() {',
            '  if (WiFi.status() != WL_CONNECTED) {',
            '    connectWifi();',
            '    return;',
            '  }',
            '  if (!connectMqttIfNeeded()) return;',
            '  mqttClient.poll();',
            '  if (millis() - lastPublishAt >= PUBLISH_INTERVAL_MS) {',
            '    lastPublishAt = millis();',
            '    publishTelemetry();',
            '  }',
            '}',
        ]);
    }

    private function body(): string
    {
        return $this->articleBody(false);
    }

    private function bodyEn(): string
    {
        return $this->articleBody(true);
    }

    private function articleBody(bool $english): string
    {
        $sketch = htmlspecialchars($this->sketch(), ENT_QUOTES, 'UTF-8');
        $figures = $this->figures($english);
        $copy = $english ? $this->englishCopy() : $this->indonesianCopy();

        return strtr($copy, [
            '{tools}' => $figures['tools'],
            '{wiring}' => $figures['wiring'],
            '{lan}' => $figures['lan'],
            '{flow}' => $figures['flow'],
            '{troubleshooting}' => $figures['troubleshooting'],
            '{sketch}' => $sketch,
        ]);
    }

    private function figures(bool $english): array
    {
        if ($english) {
            return [
                'tools' => $this->figure('fs34-tools-order.png', 'Tool order for ESP32 sending DHT22 telemetry to MQTT', '<strong>Safe order:</strong> browser → Arduino IDE → DHT22 wiring → broker PowerShell → MQTTX. Diagram by Koding Indonesia (FS-34).'),
                'wiring' => $this->figure('fs34-wiring-dht22.png', 'Three-pin DHT22 module wiring to ESP32: 3V3, GPIO 4, and GND', '<strong>Main figure — wiring.</strong> VCC → 3V3, DATA → GPIO 4, GND → GND. Diagram by Koding Indonesia (FS-34).'),
                'lan' => $this->figure('fs34-lan-address.png', 'ESP32 uses the PC LAN IPv4 address, not localhost or 127.0.0.1', '<strong>Important:</strong> <code>127.0.0.1</code> on ESP32 means ESP32 itself. Use the PC IPv4 from <code>ipconfig</code>. Diagram by Koding Indonesia (FS-34).'),
                'flow' => $this->figure('fs34-json-flow.png', 'DHT22 is read by ESP32 then sent as JSON and shown in MQTTX', '<strong>Data flow:</strong> DHT22 → ESP32 → Mosquitto → MQTTX. Diagram by Koding Indonesia (FS-34).'),
                'troubleshooting' => $this->figure('fs34-troubleshooting.png', 'Four checks when ESP32 telemetry has not appeared in MQTTX', '<strong>Helper schematic.</strong> Check wiring, Wi-Fi, broker and PC IP, then MQTTX. Diagram by Koding Indonesia (FS-34).'),
            ];
        }

        return [
            'tools' => $this->figure('fs34-tools-order.png', 'Urutan tools untuk ESP32 mengirim telemetry DHT22 ke MQTT', '<strong>Urutan aman:</strong> browser → Arduino IDE → kabel DHT22 → PowerShell broker → MQTTX. Diagram buatan Koding Indonesia (FS-34).'),
            'wiring' => $this->figure('fs34-wiring-dht22.png', 'Wiring modul DHT22 tiga pin ke ESP32: 3V3, GPIO 4, dan GND', '<strong>Gambar utama — wiring.</strong> VCC → 3V3, DATA → GPIO 4, GND → GND. Diagram buatan Koding Indonesia (FS-34).'),
            'lan' => $this->figure('fs34-lan-address.png', 'ESP32 memakai alamat IPv4 LAN PC, bukan localhost atau 127.0.0.1', '<strong>Aturan penting:</strong> <code>127.0.0.1</code> pada ESP32 berarti ESP32 itu sendiri. Gunakan IPv4 PC dari <code>ipconfig</code>. Diagram buatan Koding Indonesia (FS-34).'),
            'flow' => $this->figure('fs34-json-flow.png', 'DHT22 dibaca ESP32 lalu dikirim sebagai JSON dan terlihat di MQTTX', '<strong>Alur data:</strong> DHT22 → ESP32 → Mosquitto → MQTTX. Diagram buatan Koding Indonesia (FS-34).'),
            'troubleshooting' => $this->figure('fs34-troubleshooting.png', 'Empat pemeriksaan jika telemetry ESP32 belum muncul di MQTTX', '<strong>Skema bantu.</strong> Periksa kabel, Wi-Fi, broker serta IP PC, lalu MQTTX. Diagram buatan Koding Indonesia (FS-34).'),
        ];
    }

    private function indonesianCopy(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — sensor akhirnya berbicara ke broker</h2><p><strong>FS-34 / #104 (ini)</strong> menyambungkan DHT22, ESP32, dan Mosquitto lokal dari FS-33. ESP32 membaca suhu serta kelembapan, membungkusnya sebagai JSON, lalu mengirimkannya ke broker. MQTTX di PC menunjukkan hasilnya.</p><p><strong>Target lab:</strong> satu pesan baru muncul setiap lima detik di MQTTX. Tidak ada cloud, akun broker publik, port router, relay, atau dashboard web hari ini.</p>
<h2>Hasil yang dituju</h2><ul><li>DHT22 terpasang ke ESP32 dengan aman.</li><li>ESP32 terhubung ke Wi-Fi yang sama dengan PC.</li><li>MQTTX menerima JSON seperti <code>{"device_id":"esp32-meja-01","temperature_c":27.4,"humidity_pct":63.1}</code>.</li><li>Topic telemetry menyebut <code>device_id</code> sejak awal.</li></ul>
<h2>Persiapan — buka tool yang benar dulu</h2>{tools}<ol><li><strong>Buka browser.</strong> Siapkan untuk membaca panduan dan sumber resmi.</li><li><strong>Buka Arduino IDE.</strong> Kita memasang library sebelum menempel sketch.</li><li><strong>Siapkan ESP32, kabel data USB, modul DHT22, dan kabel jumper.</strong> Lepaskan USB sebelum mengubah wiring.</li><li><strong>Buka PowerShell di PC broker.</strong> Nanti kita mencari IPv4 PC lalu menjalankan Mosquitto.</li><li><strong>Buka MQTTX paling akhir.</strong> MQTTX dipakai untuk melihat pesan setelah broker dan ESP32 siap.</li></ol><p><strong>Jangan dipakai hari ini:</strong> broker publik, port forwarding/router, relay, akun cloud, Laragon, <code>php artisan</code>, atau dashboard web.</p>
<h2>Pasang library di Arduino IDE</h2><p>Di Arduino IDE, buka <strong>Library Manager</strong>: klik ikon buku pada bilah kiri. Pada tampilan yang menyediakan menu, kamu juga dapat membuka <strong>Sketch → Include Library → Manage Libraries</strong>. Cari dan pasang satu per satu:</p><ol><li><strong>DHT sensor library</strong> oleh Adafruit. Jika diminta memasang <strong>Adafruit Unified Sensor</strong>, pilih <strong>Install All</strong>; dependensi itu memang dibutuhkan library DHT.</li><li><strong>ArduinoMqttClient</strong> oleh Arduino. Library ini mendukung pengiriman MQTT dan contoh resminya menyertakan ESP32.</li><li><strong>ArduinoJson</strong> oleh Benoit Blanchon. Lab ini memakai <code>JsonDocument</code> dan <code>serializeJson()</code>, jadi kamu tidak perlu merangkai JSON dengan tangan.</li></ol><p>Sebelum Verify, pilih board ESP32 dan port USB yang benar dari pemilih board di bagian atas Arduino IDE. Jika board ESP32 belum tersedia, selesaikan pemasangan board package dari modul sebelumnya terlebih dahulu.</p>
<h2>Pasang kabel DHT22</h2>{wiring}<ol><li>Cabut USB ESP32.</li><li>Pada <strong>modul DHT22 tiga pin</strong>, sambungkan <strong>VCC → 3V3</strong>, <strong>DATA → GPIO 4</strong>, dan <strong>GND → GND</strong>.</li><li>Pasang kembali USB data ke ESP32.</li></ol><p><strong>Jangan menebak pin.</strong> Sensor DHT22 bare empat kaki dan modul tiga pin tidak memiliki tata letak yang sama. Artikel ini memakai modul tiga pin berlabel VCC, DATA, GND. Bila milikmu berbeda, berhenti dulu dan cocokkan tulisan pin pada modul atau kemasannya.</p>
<h2>Siapkan Mosquitto agar ESP32 boleh masuk dari Wi-Fi rumah</h2>{lan}<p>Pada FS-33, broker hanya menerima aplikasi PC melalui <code>127.0.0.1</code>. Sekarang ESP32 adalah perangkat lain, sehingga kita membuat <strong>akses LAN sementara</strong> yang hanya terikat ke IPv4 PC rumah.</p><ol><li>Di PC broker, buka <strong>PowerShell</strong>.</li><li>Ketik <code>ipconfig</code>, lalu cari <strong>IPv4 Address</strong> pada adaptor Wi-Fi yang sedang dipakai. Contoh artikel memakai <code>192.168.1.23</code>; punyamu hampir pasti berbeda.</li><li>Buka <strong>Notepad</strong>, tempel konfigurasi berikut, dan ganti alamat contoh dengan IPv4 PC milikmu:</li></ol><pre><code>listener 1883 192.168.1.23&#10;listener_allow_anonymous true</code></pre><ol start="4"><li>Simpan sebagai <code>mosquitto-fs34.conf</code> di folder <strong>Documents</strong>. Pastikan Notepad tidak menambahkan akhiran <code>.txt</code>.</li><li>Kembali ke PowerShell, jalankan perintah ini:</li></ol><pre><code>&amp; 'C:\Program Files\mosquitto\mosquitto.exe' -c "$env:USERPROFILE\Documents\mosquitto-fs34.conf" -v</code></pre><p><strong>Hasil yang diharapkan:</strong> jendela tetap terbuka dan tidak menampilkan error. Jika Windows meminta izin jaringan, pilih hanya <strong>Private networks</strong> pada Wi-Fi rumah tepercaya; jangan mengizinkan jaringan Public. Jangan membuka port router atau memakai Wi-Fi tamu.</p><p><strong>Mengapa ada akses anonim?</strong> Konfigurasi dua baris ini hanya untuk lab LAN singkat, agar ESP32 dan MQTTX dapat tersambung tanpa akun. Mosquitto mendukung listener yang diikat ke alamat IP tertentu dan opsi anonim per listener. Hentikan broker dengan <strong>Ctrl+C</strong> setelah selesai latihan. Pengguna, sandi, TLS, dan akses yang lebih aman dibahas pada FS-49. Rujukan: <a href="https://mosquitto.org/man/mosquitto-conf-5.html" target="_blank" rel="noopener noreferrer">dokumentasi konfigurasi Mosquitto</a>.</p>
<h2>Hubungkan MQTTX lebih dulu</h2><p>Di MQTTX, buat koneksi baru dengan <strong>Host = IPv4 PC</strong> yang sama, misalnya <code>192.168.1.23</code>, dan <strong>Port = 1883</strong>. Jangan gunakan <code>127.0.0.1</code> pada tahap ini. Biarkan username dan password kosong hanya untuk lab sementara ini.</p><p>Buat subscription ke topic berikut, lalu biarkan MQTTX terbuka:</p><pre><code>kodingindonesia/fsiot/esp32-meja-01/telemetry</code></pre>
<h2>Sketch ESP32 — kirim telemetry JSON tiap lima detik</h2>{flow}<p>Di Arduino IDE, buat sketch baru bernama <code>FS34_dht22_mqtt_json</code>. Ganti tiga bagian sebelum Upload: <code>GANTI_NAMA_WIFI</code>, <code>GANTI_SANDI_WIFI</code>, dan <code>192.168.1.23</code> dengan IPv4 PC kamu. Jangan menaruh sandi asli pada screenshot atau artikel.</p><pre><code class="language-arduino">{sketch}</code></pre><p><strong>Mengapa memakai <code>millis()</code>?</strong> Bagian publish menunggu lima detik tanpa <code>delay()</code> panjang. DHT22 dibaca lebih lambat daripada loop ESP32, sedangkan <code>mqttClient.poll()</code> tetap sering berjalan untuk menjaga koneksi MQTT.</p><p>Dasar Wi-Fi ESP32 memakai <code>WiFi.begin()</code> lalu mengecek <code>WL_CONNECTED</code>. Contoh resmi ArduinoMqttClient memakai <code>connect()</code>, <code>poll()</code>, <code>beginMessage()</code>, dan <code>endMessage()</code>. Rujukan: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/api/wifi.html" target="_blank" rel="noopener noreferrer">Espressif Wi-Fi API</a>, <a href="https://github.com/arduino-libraries/ArduinoMqttClient" target="_blank" rel="noopener noreferrer">ArduinoMqttClient</a>, <a href="https://arduinojson.org/v7/api/jsondocument/" target="_blank" rel="noopener noreferrer">ArduinoJson JsonDocument</a>, dan <a href="https://github.com/adafruit/DHT-sensor-library" target="_blank" rel="noopener noreferrer">Adafruit DHT sensor library</a>.</p>
<h2>Upload dan lihat hasilnya</h2><ol><li>Di Arduino IDE, klik <strong>Verify</strong>. Jika ada error library, kembali ke <strong>Tools → Manage Libraries</strong> dan periksa namanya.</li><li>Klik <strong>Upload</strong>. Jangan mencabut USB sampai proses selesai.</li><li>Buka <strong>Tools → Serial Monitor</strong>, lalu pilih baud <strong>115200</strong>.</li><li>Cari tulisan <code>MQTT tersambung.</code> lalu <code>Terkirim:</code>.</li><li>Kembali ke MQTTX. Pesan JSON baru harus muncul setiap kira-kira lima detik.</li></ol><p><strong>Berhasil berarti:</strong> Serial Monitor dan MQTTX menampilkan nilai yang sejalan. Angka suhu/kelembapan bisa berbeda di setiap ruangan; yang penting format JSON dan topic tepat.</p>
<h2>Jika pesan belum muncul di MQTTX</h2>{troubleshooting}<ol><li><strong>ESP32 memakai <code>127.0.0.1</code> atau <code>localhost</code>.</strong> Ganti dengan IPv4 PC dari <code>ipconfig</code>, lalu Upload ulang.</li><li><strong>PC dan ESP32 berada di Wi-Fi berbeda.</strong> Pastikan keduanya memakai jaringan rumah yang sama, bukan guest Wi-Fi.</li><li><strong>Jendela Mosquitto tertutup atau alamat konfigurasi salah.</strong> Jalankan kembali broker dan cocokkan IPv4 pada file konfigurasi serta sketch.</li><li><strong>Windows menolak koneksi.</strong> Pastikan izin hanya untuk jaringan Private sudah diberikan. Jangan membuat port forwarding router.</li><li><strong>DHT22 tidak terbaca.</strong> Matikan daya, periksa VCC → 3V3, DATA → GPIO 4, GND → GND, lalu Upload ulang.</li><li><strong>MQTTX tidak melihat pesan.</strong> Host MQTTX harus IPv4 PC, port <code>1883</code>, dan topic harus sama persis dengan sketch.</li><li><strong>IP PC berubah setelah router/reboot.</strong> Jalankan <code>ipconfig</code> lagi, ubah file konfigurasi dan <code>MQTT_HOST</code>, lalu jalankan broker serta Upload ulang.</li></ol>
<h2 id="fsiot-telemetry-checklist">Checklist sebelum FS-35</h2><p>Centang setelah benar-benar dilakukan. Target: <strong>10/10</strong>. Progres hanya tersimpan di browser ini dan tidak dikirim ke server.</p><ul id="fsiot-telemetry-checklist-items"><li>Library DHT sensor, ArduinoMqttClient, dan ArduinoJson sudah terpasang.</li><li>Wiring modul DHT22 tiga pin cocok: 3V3, GPIO 4, GND.</li><li>ESP32 dan PC memakai Wi-Fi rumah yang sama.</li><li>Saya menemukan IPv4 PC dengan <code>ipconfig</code>.</li><li>Konfigurasi Mosquitto memakai IPv4 PC, bukan alamat contoh.</li><li>Broker Mosquitto berjalan di PowerShell.</li><li>MQTTX memakai IPv4 PC dan port 1883.</li><li>Sketch berisi Wi-Fi dan <code>MQTT_HOST</code> milik saya.</li><li>Serial Monitor menampilkan pesan <code>Terkirim:</code>.</li><li>JSON telemetry terlihat di MQTTX.</li></ul><p><strong>Cara memeriksa kesiapan:</strong> jelaskan alur DHT22 → ESP32 → Mosquitto → MQTTX dengan kata-katamu sendiri. Setelah selesai, hentikan broker lab dengan <strong>Ctrl+C</strong>. Pada FS-35, MQTT akan membawa perintah dari PC ke relay.</p>
<h2>Selanjutnya</h2><p><strong>Ringkasnya:</strong> ESP32 kini menjadi publisher telemetry. Pada <strong>FS-35</strong>, ESP32 akan subscribe topic <code>command</code> dan mengendalikan relay dengan aman.</p>
HTML;
    }

    private function englishCopy(): string
    {
        return <<<'HTML'
<h2>Introduction — the sensor now speaks to the broker</h2><p><strong>FS-34 / #104 (this article)</strong> connects DHT22, ESP32, and the local Mosquitto broker from FS-33. ESP32 reads temperature and humidity, packs them as JSON, then sends them to the broker. MQTTX on the PC shows the result.</p><p><strong>Lab target:</strong> one new MQTTX message every five seconds. There is no cloud, public-broker account, router port, relay, or web dashboard today.</p>
<h2>Target result</h2><ul><li>DHT22 is safely wired to ESP32.</li><li>ESP32 joins the same Wi-Fi as the PC.</li><li>MQTTX receives JSON such as <code>{"device_id":"esp32-meja-01","temperature_c":27.4,"humidity_pct":63.1}</code>.</li><li>The telemetry topic includes <code>device_id</code> from the start.</li></ul>
<h2>Preparation — open the right tools first</h2>{tools}<ol><li><strong>Open a browser.</strong> Keep this guide and official references available.</li><li><strong>Open Arduino IDE.</strong> Install libraries before pasting the sketch.</li><li><strong>Prepare ESP32, a data USB cable, DHT22 module, and jumpers.</strong> Unplug USB before changing wiring.</li><li><strong>Open PowerShell on the broker PC.</strong> We find the PC IPv4 address and run Mosquitto there.</li><li><strong>Open MQTTX last.</strong> It watches messages after broker and ESP32 are ready.</li></ol><p><strong>Not used today:</strong> a public broker, router port forwarding, relay, cloud account, Laragon, <code>php artisan</code>, or dashboard.</p>
<h2>Install Arduino IDE libraries</h2><p>Open <strong>Library Manager</strong> by selecting the book icon in the left sidebar. If your interface provides the menu path, you can also use <strong>Sketch → Include Library → Manage Libraries</strong>. Install <strong>DHT sensor library</strong> by Adafruit and choose <strong>Install All</strong> if asked for Adafruit Unified Sensor. Then install <strong>ArduinoMqttClient</strong> by Arduino and <strong>ArduinoJson</strong> by Benoit Blanchon. Before Verify, select the correct ESP32 board and USB port from the board selector at the top of Arduino IDE. If ESP32 is unavailable, finish installing its board package from the earlier module first.</p>
<h2>Wire DHT22</h2>{wiring}<ol><li>Unplug ESP32 USB.</li><li>On a <strong>labelled three-pin DHT22 module</strong>, connect <strong>VCC → 3V3</strong>, <strong>DATA → GPIO 4</strong>, and <strong>GND → GND</strong>.</li><li>Reconnect the data USB cable.</li></ol><p><strong>Do not guess pins.</strong> A bare four-pin DHT22 and a three-pin module do not use the same physical layout. This article uses a labelled three-pin VCC/DATA/GND module.</p>
<h2>Let ESP32 reach Mosquitto over trusted home Wi-Fi</h2>{lan}<p>FS-33 kept the broker at <code>127.0.0.1</code> for the PC only. ESP32 is a different device, so this short lab creates a LAN listener bound only to the PC home-network IPv4 address.</p><ol><li>Open <strong>PowerShell</strong> on the broker PC.</li><li>Run <code>ipconfig</code> and find the Wi-Fi adapter <strong>IPv4 Address</strong>. This guide uses <code>192.168.1.23</code> only as an example.</li><li>Open <strong>Notepad</strong>, replace the example address, then save this as <code>mosquitto-fs34.conf</code> in Documents:</li></ol><pre><code>listener 1883 192.168.1.23&#10;listener_allow_anonymous true</code></pre><ol start="4"><li>Run this in PowerShell:</li></ol><pre><code>&amp; 'C:\Program Files\mosquitto\mosquitto.exe' -c "$env:USERPROFILE\Documents\mosquitto-fs34.conf" -v</code></pre><p>If Windows asks about network access, allow only <strong>Private networks</strong> for trusted home Wi-Fi, never Public networks. Do not open a router port or use guest Wi-Fi. This anonymous listener is temporary for this trusted LAN lab only; stop it with <strong>Ctrl+C</strong> when done. FS-49 covers credentials and TLS. See the <a href="https://mosquitto.org/man/mosquitto-conf-5.html" target="_blank" rel="noopener noreferrer">Mosquitto configuration documentation</a>.</p>
<h2>Connect MQTTX first</h2><p>Create an MQTTX connection with <strong>Host = the same PC IPv4 address</strong> and <strong>Port = 1883</strong>. Do not use <code>127.0.0.1</code> in this step. Leave username and password blank only for this temporary lab. Subscribe to:</p><pre><code>kodingindonesia/fsiot/esp32-meja-01/telemetry</code></pre>
<h2>ESP32 sketch — publish JSON every five seconds</h2>{flow}<p>Create <code>FS34_dht22_mqtt_json</code> in Arduino IDE. Replace <code>GANTI_NAMA_WIFI</code>, <code>GANTI_SANDI_WIFI</code>, and the example PC IPv4 address before Upload.</p><pre><code class="language-arduino">{sketch}</code></pre><p>The publish interval uses <code>millis()</code>, not a long <code>delay()</code>, while <code>mqttClient.poll()</code> keeps MQTT alive. References: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/api/wifi.html" target="_blank" rel="noopener noreferrer">Espressif Wi-Fi API</a>, <a href="https://github.com/arduino-libraries/ArduinoMqttClient" target="_blank" rel="noopener noreferrer">ArduinoMqttClient</a>, <a href="https://arduinojson.org/v7/api/jsondocument/" target="_blank" rel="noopener noreferrer">ArduinoJson JsonDocument</a>, and <a href="https://github.com/adafruit/DHT-sensor-library" target="_blank" rel="noopener noreferrer">Adafruit DHT sensor library</a>.</p>
<h2>Upload and see the result</h2><ol><li>Click <strong>Verify</strong>. Fix missing libraries in <strong>Tools → Manage Libraries</strong>.</li><li>Click <strong>Upload</strong> and keep USB connected until it finishes.</li><li>Open <strong>Tools → Serial Monitor</strong> at <strong>115200</strong> baud.</li><li>Look for <code>MQTT tersambung.</code> then <code>Terkirim:</code>.</li><li>Return to MQTTX; a JSON message should appear about every five seconds.</li></ol>
<h2>If MQTTX shows no message</h2>{troubleshooting}<ol><li><strong>ESP32 uses <code>127.0.0.1</code> or <code>localhost</code>.</strong> Replace it with PC IPv4 and Upload again.</li><li><strong>PC and ESP32 use different Wi-Fi.</strong> Use the same home network, not guest Wi-Fi.</li><li><strong>Broker window is closed or IP is wrong.</strong> Restart Mosquitto and match the config IP to the sketch.</li><li><strong>Windows rejects the connection.</strong> Allow only Private network access; do not configure router forwarding.</li><li><strong>DHT22 cannot be read.</strong> Power off, check 3V3 → GPIO 4 → GND, then Upload again.</li><li><strong>MQTTX watches the wrong place.</strong> Use PC IPv4, port <code>1883</code>, and exactly the sketch topic.</li><li><strong>PC IPv4 changed.</strong> Run <code>ipconfig</code>, update config and <code>MQTT_HOST</code>, restart broker, and Upload again.</li></ol>
<h2 id="fsiot-telemetry-checklist">Checklist before FS-35</h2><p>Tick only after doing the step. Target: <strong>10/10</strong>. Progress stays only in this browser.</p><ul id="fsiot-telemetry-checklist-items"><li>DHT sensor, ArduinoMqttClient, and ArduinoJson are installed.</li><li>Three-pin DHT22 wiring matches 3V3, GPIO 4, GND.</li><li>ESP32 and PC use the same home Wi-Fi.</li><li>I found the PC IPv4 with <code>ipconfig</code>.</li><li>Mosquitto config uses my PC IPv4, not the example address.</li><li>Mosquitto is running in PowerShell.</li><li>MQTTX uses PC IPv4 and port 1883.</li><li>The sketch uses my Wi-Fi and <code>MQTT_HOST</code>.</li><li>Serial Monitor prints <code>Terkirim:</code>.</li><li>MQTTX shows telemetry JSON.</li></ul><p><strong>How to check readiness:</strong> explain DHT22 → ESP32 → Mosquitto → MQTTX in your own words. Stop the lab broker with <strong>Ctrl+C</strong>. FS-35 brings MQTT commands from PC to a relay.</p>
<h2>Next</h2><p><strong>In short:</strong> ESP32 is now a telemetry publisher. In <strong>FS-35</strong>, ESP32 subscribes to a <code>command</code> topic and controls a relay safely.</p>
HTML;
    }
}
