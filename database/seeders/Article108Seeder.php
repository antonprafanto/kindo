<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class Article108Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@kodingindonesia.com')->first() ?? User::first();
        $category = Category::where('slug', 'iot-smart-device')->first() ?? Category::where('slug', 'esp32-arduino')->first();
        if (! $admin || ! $category) {
            throw new \RuntimeException('User atau kategori IoT tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-pc-rules-nodered-mqtt';
        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        foreach (['fullstack-iot', 'iot', 'esp32', 'mqtt', 'nodered', 'wifi'] as $tagSlug) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagSlug]);
        }

        $article = Article::updateOrCreate(['slug' => $slug], [
            'user_id' => $admin->id,
            'category_id' => $category->id,
            'title' => 'PC ubah ambang suhu di Node-RED tanpa upload ulang ESP32',
            'title_en' => 'The PC changes the temperature threshold in Node-RED without re-uploading the ESP32',
            'excerpt' => 'FS-38 / #108: aturan jika-maka pindah ke PC. ESP32 hanya kirim suhu dan patuh perintah. Ubah angka 30 di Node-RED, klik Deploy, relay GPIO 26 berubah.',
            'excerpt_en' => 'FS-38 / #108: if-then rules move to the PC. The ESP32 only sends temperature and obeys commands. Change 30 in Node-RED, click Deploy, the GPIO 26 relay follows.',
            'body' => $this->body(),
            'body_en' => $this->bodyEn(),
            'status' => 'draft',
            'is_featured' => false,
            'published_at' => null,
            'seo_title' => 'PC Ubah Ambang Suhu di Node-RED tanpa Upload Ulang ESP32 — FS-38',
            'seo_title_en' => 'Change the Temperature Threshold in Node-RED without Re-uploading the ESP32 — FS-38',
            'seo_description' => 'Lab pemula: Node-RED di PC memegang ambang suhu. ESP32 mengirim telemetri dan mematuhi perintah MQTT, tanpa Python dan tanpa AC 220V.',
            'seo_description_en' => 'A first lab: Node-RED on the PC holds the temperature threshold. The ESP32 sends telemetry and obeys MQTT commands, with no Python and no AC mains.',
        ]);
        $article->tags()->sync(Tag::whereIn('slug', ['fullstack-iot', 'iot', 'esp32', 'mqtt', 'nodered', 'wifi'])->pluck('id'));

        foreach (['webp', 'jpg'] as $extension) {
            $source = public_path("images/fsiot/fs38-cover-rules.{$extension}");
            if (is_file($source)) {
                $destination = "articles/covers/fs38-cover-rules.{$extension}";
                Storage::disk('public')->put($destination, file_get_contents($source));
            }
        }
        $article->update([
            'cover_image' => 'https://kodingindonesia.com/images/fsiot/fs38-cover-rules.webp',
        ]);
    }

    private function figure(string $file, string $alt, string $caption): string
    {
        return '<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem"><img src="/images/fsiot/'.$file.'" alt="'.$alt.'" loading="eager" style="width:100%;height:auto;max-height:640px;object-fit:contain;border-radius:6px;background:#F5F5F0"><figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a">'.$caption.'</figcaption></figure>';
    }

    /**
     * @param  list<array{title: string, text: string}>  $items
     */
    private function stepsCard(array $items, string $caption): string
    {
        $colors = ['#2979FF', '#2979FF', '#FF7A2F', '#1a1a1a', '#2e7d32'];
        $rows = '';
        foreach ($items as $index => $item) {
            $number = $index + 1;
            $color = $colors[$index] ?? '#1a1a1a';
            $border = $index < count($items) - 1 ? 'border-bottom:1px dashed #CBD5E0;' : '';
            $rows .= '<li style="display:flex;gap:1rem;padding:.9rem 0;'.$border.'">'
                .'<span style="flex-shrink:0;width:2rem;height:2rem;border-radius:999px;background:'.$color.';color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:700">'.$number.'</span>'
                .'<div><strong>'.$item['title'].'</strong><span style="display:block;color:#4A5568;margin-top:.25rem">'.$item['text'].'</span></div></li>';
        }

        return '<figure style="margin:1.5rem 0;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1.25rem"><ol style="list-style:none;padding:0;margin:0">'.$rows.'</ol><figcaption style="font-size:0.85rem;margin-top:0.75rem;color:#1a1a1a">'.$caption.'</figcaption></figure>';
    }

    private function sketch(): string
    {
        return implode("\n", [
            '#include <WiFi.h>',
            '#include <ArduinoMqttClient.h>',
            '#include <ArduinoJson.h>',
            '#include <DHT.h>',
            '',
            'const byte DHT_PIN = 4;',
            'const byte RELAY_PIN = 26;',
            'const bool AKTIF_LOW = true;  // Pola kit FS-23. Ubah ke false jika modulmu aktif HIGH.',
            '',
            'const char WIFI_SSID[] = "GANTI_NAMA_WIFI";  // Wi-Fi rumah, sama dengan PC',
            'const char WIFI_PASSWORD[] = "GANTI_SANDI_WIFI";',
            'const char MQTT_HOST[] = "192.168.1.23";  // IPv4 PC dari ipconfig',
            'const int MQTT_PORT = 1883;',
            'const char DEVICE_ID[] = "esp32-meja-01";',
            'const char TOPIC_TELEMETRY[] = "kodingindonesia/fsiot/esp32-meja-01/telemetry";',
            'const char TOPIC_COMMAND[] = "kodingindonesia/fsiot/esp32-meja-01/command";',
            'const char TOPIC_STATUS[] = "kodingindonesia/fsiot/esp32-meja-01/status";',
            '',
            'const unsigned long SAMPLE_INTERVAL_MS = 5000UL;',
            '',
            'DHT dht(DHT_PIN, DHT22);',
            'WiFiClient wifiClient;',
            'MqttClient mqttClient(wifiClient);',
            'unsigned long lastSampleAt = 0;',
            'unsigned long lastWifiAttemptAt = 0;',
            'unsigned long lastMqttAttemptAt = 0;',
            'bool relayOn = false;',
            '',
            'void applyRelay(bool on) {',
            '  relayOn = on;',
            '  digitalWrite(RELAY_PIN, AKTIF_LOW ? (on ? LOW : HIGH) : (on ? HIGH : LOW));',
            '}',
            '',
            'void publishStatus(bool ok) {',
            '  JsonDocument data;',
            '  data["device_id"] = DEVICE_ID;',
            '  data["relay"] = relayOn ? "on" : "off";',
            '  data["ok"] = ok;',
            '  String payload;',
            '  serializeJson(data, payload);',
            '  mqttClient.beginMessage(TOPIC_STATUS);',
            '  mqttClient.print(payload);',
            '  if (mqttClient.endMessage()) {',
            '    Serial.print("Status terkirim: ");',
            '    Serial.println(payload);',
            '  } else {',
            '    Serial.println("Status belum terkirim.");',
            '  }',
            '}',
            '',
            'bool kirimTelemetry(float temperature) {',
            '  JsonDocument data;',
            '  data["device_id"] = DEVICE_ID;',
            '  data["temperature_c"] = temperature;',
            '  data["timestamp_ms"] = millis();',
            '  String payload;',
            '  serializeJson(data, payload);',
            '  mqttClient.beginMessage(TOPIC_TELEMETRY);',
            '  mqttClient.print(payload);',
            '  if (!mqttClient.endMessage()) {',
            '    return false;',
            '  }',
            '  Serial.print("Terkirim: ");',
            '  Serial.println(payload);',
            '  return true;',
            '}',
            '',
            'void handleCommand(const String& payload) {',
            '  JsonDocument data;',
            '  DeserializationError error = deserializeJson(data, payload);',
            '  if (error) {',
            '    Serial.println("JSON perintah tidak dikenali.");',
            '    return;',
            '  }',
            '  const char* id = data["device_id"];',
            '  if (!id || strcmp(id, DEVICE_ID) != 0) {',
            '    Serial.println("device_id tidak cocok. Perintah diabaikan.");',
            '    return;',
            '  }',
            '  const char* relayRaw = data["relay"];',
            '  if (!relayRaw) {',
            '    Serial.println("Isian relay belum ada.");',
            '    return;',
            '  }',
            '  String relay = String(relayRaw);',
            '  relay.toLowerCase();',
            '  if (relay == "on") {',
            '    applyRelay(true);',
            '    Serial.println("Relay ON");',
            '    publishStatus(true);',
            '  } else if (relay == "off") {',
            '    applyRelay(false);',
            '    Serial.println("Relay OFF");',
            '    publishStatus(true);',
            '  } else {',
            '    Serial.println("Nilai relay harus on atau off.");',
            '  }',
            '}',
            '',
            'void onMqttMessage(int messageSize) {',
            '  String payload;',
            '  while (mqttClient.available()) {',
            '    payload += (char) mqttClient.read();',
            '  }',
            '  Serial.print("Perintah: ");',
            '  Serial.println(payload);',
            '  handleCommand(payload);',
            '  (void) messageSize;',
            '}',
            '',
            'void connectWifi() {',
            '  if (WiFi.status() == WL_CONNECTED) {',
            '    return;',
            '  }',
            '  if (lastWifiAttemptAt != 0 && millis() - lastWifiAttemptAt < 10000UL) {',
            '    return;',
            '  }',
            '  lastWifiAttemptAt = millis();',
            '  Serial.println("Menghubungkan Wi-Fi rumah");',
            '  WiFi.mode(WIFI_STA);',
            '  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);',
            '}',
            '',
            'bool connectMqttIfNeeded() {',
            '  if (mqttClient.connected()) {',
            '    return true;',
            '  }',
            '  if (millis() - lastMqttAttemptAt < 5000UL) {',
            '    return false;',
            '  }',
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
            '  if (!mqttClient.subscribe(TOPIC_COMMAND)) {',
            '    Serial.println("Subscribe command gagal.");',
            '    return false;',
            '  }',
            '  Serial.println("Subscribe command siap.");',
            '  return true;',
            '}',
            '',
            'void setup() {',
            '  Serial.begin(115200);',
            '  pinMode(RELAY_PIN, OUTPUT);',
            '  applyRelay(false);',
            '  dht.begin();',
            '  mqttClient.onMessage(onMqttMessage);',
            '  Serial.println("Ambang ada di PC, bukan di sketch.");',
            '  connectWifi();',
            '}',
            '',
            'void loop() {',
            '  if (WiFi.status() != WL_CONNECTED) {',
            '    connectWifi();',
            '    return;',
            '  }',
            '  if (!connectMqttIfNeeded()) {',
            '    return;',
            '  }',
            '  mqttClient.poll();',
            '  if (millis() - lastSampleAt >= SAMPLE_INTERVAL_MS) {',
            '    lastSampleAt = millis();',
            '    float temperature = dht.readTemperature();',
            '    if (isnan(temperature)) {',
            '      Serial.println("DHT22 belum terbaca. Periksa kabel GPIO 4.");',
            '      return;',
            '    }',
            '    kirimTelemetry(temperature);',
            '  }',
            '}',
        ]);
    }

    private function body(): string
    {
        $sketch = htmlspecialchars($this->sketch(), ENT_QUOTES, 'UTF-8');
        $tools = $this->figure('fs38-tools-order.png', 'Urutan lima langkah: browser, Node.js LTS, Node-RED, Mosquitto plus MQTTX, lalu Arduino IDE', '<strong>Urutan meja kerja (lima langkah):</strong> browser → pemasang Node.js LTS → PowerShell untuk Node-RED → Mosquitto + MQTTX → Arduino IDE. Upload sekali; ambang diubah di Node-RED. Diagram buatan Koding Indonesia (FS-38).');
        $wifi = $this->figure('fs38-same-wifi.png', 'PC dan ESP32 di Wi-Fi rumah yang sama; Node-RED memakai 127.0.0.1, ESP32 memakai IPv4 PC', '<strong>Satu Wi-Fi rumah.</strong> Demo hotspot FS-37 tidak dipakai hari ini. Node-RED di PC menyambung ke Mosquitto lewat <code>127.0.0.1</code>. ESP32 memakai IPv4 PC, bukan <code>127.0.0.1</code>. Diagram buatan Koding Indonesia (FS-38).');
        $brain = $this->figure('fs38-brain-on-pc.png', 'Perbandingan aturan if suhu di sketch versus switch di Node-RED', '<strong>Firmware patuh. PC yang memutuskan.</strong> Jangan menulis <code>if (suhu &gt; 30)</code> di sketch. Diagram buatan Koding Indonesia (FS-38).');
        $flow = $this->figure('fs38-flow.png', 'Alur kiri ke kanan: DHT22, Mosquitto, Node-RED, lalu relay GPIO 26', '<strong>Gambar utama — alur.</strong> Baca dari kiri ke kanan: suhu → broker → aturan PC → klik relay. Diagram buatan Koding Indonesia (FS-38).');
        $wiring = $this->figure('fs38-wiring.png', 'Wiring DHT22 GPIO 4 dan relay GPIO 26 ke ESP32, terminal NC COM NO kosong', '<strong>Wiring hari ini.</strong> DHT22: VCC → 3V3, DATA → GPIO 4, GND → GND. Relay 5V: VCC/+ → 5V, IN/S → GPIO 26, GND/− → GND. Terminal NC/COM/NO kosong. <strong>Jangan colok AC 220V.</strong> Diagram buatan Koding Indonesia (FS-38).');
        $editor = $this->figure('fs38-nodered-editor.png', 'Ilustrasi editor Node-RED: mqtt in telemetry, switch suhu lebih dari 30, change relay on atau off', '<strong>Node-RED sudah menampilkan alur jika-maka.</strong> Buka <code>http://127.0.0.1:1880</code>. Setelah mengubah angka, klik <strong>Deploy</strong>. Ilustrasi buatan Koding Indonesia (FS-38), meniru editor <a href="https://nodered.org/" target="_blank" rel="noopener noreferrer">Node-RED</a> oleh OpenJS Foundation (Apache License 2.0). Screenshot jendela resmi tidak dipakai utuh.');
        $deploy = $this->figure('fs38-threshold-deploy.png', 'Urutan ubah ambang: double-klik switch, ganti angka, klik Deploy di kanan atas, relay berubah tanpa Upload', '<strong>Ubah ambang di Node-RED, lalu Deploy.</strong> Tombol Deploy di <strong>kanan atas</strong> editor, bukan kiri atas seperti Upload Arduino. USB ESP32 tetap colok. Diagram buatan Koding Indonesia (FS-38).');
        $manual = $this->figure('fs38-mqttx-manual.png', 'Ilustrasi MQTTX: lihat telemetri 31.2 lalu publish perintah relay on', '<strong>Jalur cadangan.</strong> Kamu yang jadi otak: lihat suhu di MQTTX, lalu publish JSON perintah. Automasi tetap di Node-RED pada jalur utama. Ilustrasi buatan Koding Indonesia (FS-38), meniru tata letak <a href="https://mqttx.app/" target="_blank" rel="noopener noreferrer">MQTTX oleh EMQ</a> (Apache License 2.0).');
        $troubleshooting = $this->figure('fs38-troubleshooting.png', 'Empat pemeriksaan jika relay tidak mengikuti ambang Node-RED', '<strong>Skema bantu.</strong> Periksa jendela Node-RED, Mosquitto, tombol Deploy, lalu IPv4 ESP32. Diagram buatan Koding Indonesia (FS-38).');
        $relayPhoto = $this->figure('kit-relay-5v.jpg', 'Contoh rupa modul relay 1 channel 5V Songle dengan terminal sekrup dan tiga pin', '<strong>Contoh rupa relay.</strong> <strong>Jangan menyalin urutan kaki dari foto.</strong> Wiring menurut tulisan pin. Terminal sekrup NC/COM/NO <strong>bukan</strong> pin ke ESP32, dan <strong>bukan AC 220V</strong>. Sumber: <a href="https://commons.wikimedia.org/wiki/File:SRD-05VDC-SL-C_5V_one-channel_relay_module.jpg" target="_blank" rel="noopener noreferrer">SRD-05VDC-SL-C 5V one-channel relay module</a> · Suyash Dwivedi · Wikimedia Commons · Creative Commons Attribution-Share Alike 4.0.');
        $dhtPhoto = $this->figure('kit-dht22.jpg', 'Contoh rupa modul DHT22 AM2302 dengan pin DAT, VCC, dan GND', '<strong>Contoh rupa sensor.</strong> <strong>Jangan menyalin urutan kaki dari foto.</strong> Wiring tetap VCC → 3V3, DATA atau DAT → GPIO 4, GND → GND. Sumber: <a href="https://commons.wikimedia.org/wiki/File:DHT_22_Sensor.jpg" target="_blank" rel="noopener noreferrer">AM2302 DHT22 Sensor</a> · L293D · Wikimedia Commons · Creative Commons Attribution-Share Alike 4.0.');
        $install = $this->stepsCard([
            ['title' => 'Buka browser', 'text' => 'Pakai Chrome, Firefox, Edge, atau Safari. Siapkan artikel ini. Jangan tekan Upload di Arduino IDE dulu.'],
            ['title' => 'Pasang Node.js LTS', 'text' => 'Buka <a href="https://nodejs.org/" target="_blank" rel="noopener noreferrer">nodejs.org</a>. Unduh pemasang <strong>LTS</strong> untuk Windows. Jalankan berkas <code>.msi</code>, terima bawaan, selesai. Tutup PowerShell lama jika sempat terbuka.'],
            ['title' => 'Buka PowerShell, pasang Node-RED', 'text' => 'Start → ketik <strong>PowerShell</strong> → Windows PowerShell. Tidak perlu <em>Run as administrator</em>. Tempel perintah di bawah, satu per satu. Jendela yang menjalankan <code>node-red</code> harus tetap terbuka.'],
            ['title' => 'Buka Mosquitto, lalu MQTTX', 'text' => 'Mosquitto dulu, seperti FS-33/FS-34: jendela tetap terbuka dan terlihat <code>1883</code>. Baru MQTTX. Host = <strong>IPv4 PC</strong> (bukan <code>127.0.0.1</code>), Port <code>1883</code>. Langganan telemetry dan status.'],
            ['title' => 'Buka Arduino IDE, Upload sekali', 'text' => 'Papan ESP32. Setelah Serial menulis <code>Ambang ada di PC, bukan di sketch.</code> dan <code>Terkirim:</code>, kembali ke Node-RED. Ambang diubah di sana, bukan di sketch.'],
        ], '<strong>Cara menguji hari ini:</strong> bukti sukses = kamu mengganti angka switch di Node-RED, klik Deploy, relay GPIO 26 berubah, dan Arduino IDE <strong>tidak</strong> di-Upload ulang.');

        return <<<'HTML'
<h2>Pendahuluan — otak aturan pindah ke PC</h2>
<p><strong>FS-38 / #108 (ini)</strong> menutup fase CONNECTED. Kemarin kartu menahan antrian saat hotspot putus. Hari ini tugasnya lain: <strong>aturan jika-maka tidak tinggal di ESP32</strong>.</p>
<p><strong>Intinya:</strong> ESP32 hanya mengirim suhu dan mematuhi perintah. Ambang <code>30</code> hidup di Node-RED di PC. Ganti angka, klik Deploy, relay berubah. Tidak perlu Upload ulang.</p>
<p><strong>Analogi:</strong> ESP32 adalah pekerja di gudang. Node-RED adalah papan pengumuman di kantor. Kalau ambang berubah, yang diganti papan itu — bukan orangnya.</p>
<p>Prasyarat lab: Mosquitto + MQTTX dari FS-33/FS-34, perintah relay GPIO 26 dari FS-35, DHT22 GPIO 4 dari FS-34. Kartu microSD dan hotspot HP <strong>tidak</strong> dipakai hari ini. Python ditunda ke FS-39.</p>

<h2>Hasil yang dituju</h2>
<ul>
<li>Node-RED terbuka di <code>http://127.0.0.1:1880</code>, jendela PowerShell tetap terlihat log.</li>
<li>MQTTX menampilkan JSON suhu tiap kira-kira lima detik, lalu JSON status saat relay berklik.</li>
<li>Serial menulis <code>Ambang ada di PC, bukan di sketch.</code>, <code>MQTT tersambung.</code>, <code>Subscribe command siap.</code>, lalu <code>Terkirim:</code>.</li>
<li>Kamu mengubah angka switch, klik Deploy, relay mengikuti — tanpa Upload.</li>
</ul>
<p><strong>Batas lab hari ini:</strong> tidak ada dashboard web, Python, broker publik, port forwarding, atau AC 220V. Bukti cukup = Node-RED + Serial + MQTTX + bunyi klik.</p>

<h2>Istilah yang dipakai hari ini</h2>
<ul>
<li><strong>Aturan jika-maka</strong> — jika suhu di atas ambang, maka kirim perintah on. Jika tidak, off.</li>
<li><strong>Ambang</strong> — angka pembanding. Hari ini contohnya <code>30</code>. Boleh diganti sesuai ruanganmu.</li>
<li><strong>Node-RED</strong> — aplikasi di PC untuk menyusun alur dengan kotak, bukan menulis Python.</li>
<li><strong>Deploy</strong> — tombol merah di <strong>kanan atas</strong> editor Node-RED. Artinya: pakai alur yang baru. Bukan Upload Arduino (kiri atas).</li>
<li><strong>Perangkat saja</strong> — firmware tanpa <code>if (suhu &gt; 30)</code>. ESP32 kirim data dan patuh perintah.</li>
<li><strong>Jalur cadangan</strong> — kamu sendiri yang lihat MQTTX lalu publish perintah. Untuk paham konsep, bukan pengganti Node-RED.</li>
</ul>

<h2>Persiapan — buka tool yang benar dulu</h2>
HTML
            .$tools.$install.<<<'HTML'
<p><strong>Jangan dipakai hari ini:</strong> Python, Laragon, <code>php artisan</code>, kartu microSD, hotspot HP, broker publik, atau mematikan router rumah. <strong>Jangan colok AC 220V.</strong> Terminal NC/COM/NO pada relay tetap kosong.</p>
<p><strong>Tips ponsel:</strong> jika diagram terasa kecil, gunakan fitur perbesar pada browser. Gambar tidak perlu diketuk sampai memenuhi layar agar teks di sekitarnya tetap terbaca.</p>

<h2>Topologi lab — satu Wi-Fi rumah</h2>
HTML
            .$wifi.$flow.<<<'HTML'
<p>Hari ini ESP32 dan PC kembali ke <strong>Wi-Fi rumah yang sama</strong>, seperti FS-34 dan FS-35. Demo hotspot FS-37 sengaja tidak diulang: yang diuji adalah aturan di PC, bukan antrian kartu.</p>
<ol>
<li><strong>PC</strong> menjalankan Mosquitto, MQTTX, dan Node-RED. Node-RED menyambung ke broker di <code>127.0.0.1</code> port <code>1883</code>.</li>
<li><strong>ESP32</strong> memakai SSID Wi-Fi rumah. <code>MQTT_HOST</code> = IPv4 PC dari <code>ipconfig</code>, bukan <code>127.0.0.1</code>.</li>
<li>USB ESP32 tetap terpasang agar Serial Monitor bisa dibaca.</li>
</ol>

<h2>Cari IPv4 PC, jalankan Mosquitto, buka MQTTX</h2>
<p><strong>Buka dulu PowerShell:</strong> Start → ketik <strong>PowerShell</strong> → Windows PowerShell. Tidak perlu <em>Run as administrator</em>.</p>
<p><strong>Cara menempel perintah:</strong> salin baris, klik jendela PowerShell, lalu <kbd>Ctrl</kbd>+<kbd>V</kbd> atau klik kanan. Setelah teks muncul, tekan Enter.</p>
<pre><code>ipconfig</code></pre>
<p>Cari <strong>IPv4 Address</strong> pada adaptor Wi-Fi rumah. Contoh artikel memakai <code>192.168.1.23</code>; punyamu hampir pasti berbeda.</p>
<p>Pakai berkas <code>mosquitto-fs34.conf</code> dari FS-34 jika masih ada:</p>
<pre><code>listener 1883 192.168.1.23
listener_allow_anonymous true</code></pre>
<p>Lalu:</p>
<pre><code>&amp; 'C:\Program Files\mosquitto\mosquitto.exe' -c "$env:USERPROFILE\Documents\mosquitto-fs34.conf" -v</code></pre>
<p><strong>Hasil yang dicari:</strong> jendela tetap terbuka dan terlihat angka <code>1883</code>. Hentikan dengan <strong>Ctrl+C</strong> setelah latihan. Jangan membuka port router. Rujukan: <a href="https://mosquitto.org/man/mosquitto-conf-5.html" target="_blank" rel="noopener noreferrer">dokumentasi konfigurasi Mosquitto</a>.</p>
<p>Pastikan jendela Mosquitto masih terbuka. Baru sekarang buka <strong>MQTTX</strong>. Jika belum terpasang, unduh dari <a href="https://mqttx.app/downloads" target="_blank" rel="noopener noreferrer">mqttx.app/downloads</a> seperti FS-32.</p>
<ol>
<li>Klik <em>New Connection</em>, nama <code>FS38 rules LAN</code>.</li>
<li>Host = IPv4 PC, Port = <code>1883</code>. Bukan <code>127.0.0.1</code>.</li>
<li>Klik <em>Connect</em>, lalu langganan (Subscribe) dua topik:</li>
</ol>
<pre><code>kodingindonesia/fsiot/esp32-meja-01/telemetry
kodingindonesia/fsiot/esp32-meja-01/status</code></pre>
<p><strong>Hasil yang dicari:</strong> MQTTX berstatus tersambung. Belum ada JSON sampai ESP32 mengirim. MQTTX hari ini saksi, bukan pengganti Node-RED pada jalur utama.</p>

<h2>Pasang Node.js, lalu Node-RED</h2>
<p><strong>Buka dulu browser</strong> ke <a href="https://nodejs.org/" target="_blank" rel="noopener noreferrer">nodejs.org</a>. Unduh tombol <strong>LTS</strong>, bukan Current. Jalankan pemasang Windows (<code>.msi</code>). Terima pengaturan bawaan. Setelah selesai, <strong>tutup PowerShell yang lama</strong> lalu buka yang baru agar perintah <code>node</code> dikenali.</p>
<p>Di PowerShell yang baru:</p>
<pre><code>node --version
npm --version</code></pre>
<p><strong>Hasil yang dicari:</strong> dua baris versi, misalnya <code>v22.x</code> dan <code>10.x</code>. Angka persismu boleh berbeda.</p>
<p>Pasang Node-RED secara global, lalu jalankan. Jendela ini <strong>jangan ditutup</strong> selama latihan:</p>
<pre><code>npm install -g node-red
node-red</code></pre>
<p>Tunggu sampai log menampilkan alamat editor. <strong>Buka browser</strong> ke:</p>
<pre><code>http://127.0.0.1:1880</code></pre>
<p>Itu Node-RED di komputer ini, bukan undangan ke internet. Rujukan: <a href="https://nodered.org/docs/getting-started/windows" target="_blank" rel="noopener noreferrer">Running on Windows — Node-RED</a>, OpenJS Foundation, Apache License 2.0.</p>
<p><strong>macOS:</strong> buka <strong>Terminal</strong> dulu, pasang Node.js LTS dari nodejs.org, lalu perintah <code>npm</code> dan <code>node-red</code> yang sama. <strong>Ubuntu atau Debian:</strong> buka <strong>Terminal</strong> dulu; ikuti <a href="https://nodered.org/docs/getting-started/local" target="_blank" rel="noopener noreferrer">dokumentasi Node-RED lokal</a>. Jangan memakai Python sebagai pengganti hari ini.</p>

<h2>Pasang kabel — DHT22 dan relay</h2>
HTML
            .$wiring.$dhtPhoto.$relayPhoto.<<<'HTML'
<p>Cabut USB ESP32 sebelum merapikan kabel. <strong>Jangan menebak pin.</strong></p>
<table>
<thead><tr><th>Tulisan di modul</th><th>GPIO / pin ESP32</th></tr></thead>
<tbody>
<tr><td>DHT22 VCC</td><td><strong>3V3</strong></td></tr>
<tr><td>DHT22 DATA atau DAT</td><td><strong>4</strong></td></tr>
<tr><td>DHT22 GND</td><td>GND</td></tr>
<tr><td>Relay VCC atau +</td><td><strong>5V</strong></td></tr>
<tr><td>Relay IN atau S</td><td><strong>26</strong></td></tr>
<tr><td>Relay GND atau −</td><td>GND</td></tr>
<tr><td>NC / COM / NO</td><td><strong>kosong</strong></td></tr>
</tbody>
</table>
<p><strong>Jangan colok AC 220V.</strong> Kartu microSD tidak dipasang hari ini.</p>

<h2>Kenapa ambang tidak boleh di sketch</h2>
HTML
            .$brain.<<<'HTML'
<p>Kalau ambang ditulis di sketch, setiap ganti angka berarti Verify, Upload, tunggu. Di meja kantor, itu seperti memanggil teknisi tiap kali thermostat diubah. Node-RED memegang angka. ESP32 tetap perangkat.</p>
<p>Jangan dobel: if di sketch <strong>plus</strong> switch di Node-RED. Satu aturan, satu tempat — hari ini tempatnya PC.</p>

<h2>Sketch ESP32 — perangkat saja</h2>
<p><strong>Buka dulu Arduino IDE.</strong> Pilih papan <strong>ESP32</strong>, bukan UNO. Library <strong>ArduinoMqttClient</strong>, <strong>ArduinoJson</strong>, dan <strong>DHT sensor library</strong> (Adafruit) biasanya sudah ada dari FS-34/FS-35. Jika tombol Verify mengeluh file header hilang: di bilah kiri, klik ikon tiga buku (Library Manager). Itu satu-satunya jalur yang dipakai hari ini. Jangan memakai menu lama <em>Tools → Manage Libraries</em>.</p>
<p>Buat sketch baru bernama <code>FS38_device_only</code>. Ganti Wi-Fi rumah, sandi, dan <code>192.168.1.23</code> dengan IPv4 PC. Jangan menaruh sandi asli pada screenshot. Sketch ini <strong>tidak</strong> berisi ambang suhu.</p>
<pre><code class="language-arduino">
HTML
            .$sketch.<<<'HTML'
</code></pre>
<p>Tekan Verify, lalu Upload. Buka <strong>Tools → Serial Monitor</strong>, baud <strong>115200</strong>. Menu itu <strong>bukan</strong> Library Manager. Cari <code>Ambang ada di PC, bukan di sketch.</code>, lalu <code>MQTT tersambung.</code>, <code>Subscribe command siap.</code>, dan <code>Terkirim:</code>. Rujukan: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-installing-a-library/" target="_blank" rel="noopener noreferrer">Installing libraries — Arduino IDE 2</a>, Arduino S.r.l., Creative Commons Attribution-Share Alike 4.0. <a href="https://github.com/arduino-libraries/ArduinoMqttClient" target="_blank" rel="noopener noreferrer">ArduinoMqttClient</a>. <a href="https://arduinojson.org/v7/api/jsondocument/" target="_blank" rel="noopener noreferrer">ArduinoJson</a>. <a href="https://github.com/adafruit/DHT-sensor-library" target="_blank" rel="noopener noreferrer">Adafruit DHT</a>.</p>

<h2>Susun alur Node-RED, lalu Deploy</h2>
HTML
            .$editor.$deploy.<<<'HTML'
<p>Di editor Node-RED, seret kotak dari palette kiri ke kanvas, jangan ketik Python.</p>
<ol>
<li>Seret <strong>mqtt in</strong>. Double-klik. Tambah broker: Server <code>127.0.0.1</code>, Port <code>1883</code>. Topic <code>kodingindonesia/fsiot/esp32-meja-01/telemetry</code>. Output: auto-detect JSON. Klik Done.</li>
<li>Seret <strong>switch</strong>. Property <code>msg.payload.temperature_c</code>. Aturan pertama: <em>&gt;</em> angka <code>30</code>. Tambah aturan otherwise. Klik Done.</li>
<li>Seret dua kotak <strong>change</strong>. Yang atas: set <code>msg.payload</code> ke JSON <code>{"device_id":"esp32-meja-01","relay":"on"}</code>. Yang bawah: <code>"relay":"off"</code> dengan <code>device_id</code> yang sama.</li>
<li>Seret <strong>mqtt out</strong>, dua kali jika perlu, topic <code>kodingindonesia/fsiot/esp32-meja-01/command</code>, broker yang sama. Sambungkan change on dan change off ke mqtt out.</li>
<li>Klik tombol <strong>Deploy</strong> di kanan atas. Jangan tekan Upload di Arduino IDE.</li>
</ol>
<p>Tiup pelan ke DHT22 atau dekati jari agar suhu naik melewati ambang. Serial menulis <code>Perintah:</code> lalu <code>Relay ON</code>. MQTTX menampilkan status. Untuk menguji tanpa menunggu cuaca: turunkan ambang ke angka di bawah suhu ruangan, Deploy lagi.</p>
<p><strong>Berhasil berarti:</strong> mengganti angka + Deploy mengubah perilaku relay, sementara sketch tidak diubah.</p>

<h2>Jalur cadangan di MQTTX</h2>
HTML
            .$manual.<<<'HTML'
<p>Kalau Node-RED belum nyaman, kamu boleh jadi otaknya dulu. Lihat telemetri. Jika <code>temperature_c</code> di atas ambang yang kamu pilih, publish ke topic command:</p>
<pre><code>{"device_id":"esp32-meja-01","relay":"on"}</code></pre>
<p>Untuk mematikan, ganti <code>"relay":"off"</code>. Ini jalur belajar. Automasi tetap disusun di Node-RED pada jalur utama.</p>

<h2>Jika relay tidak mengikuti ambang</h2>
HTML
            .$troubleshooting.<<<'HTML'
<ol>
<li><strong>Jendela Node-RED tertutup.</strong> PowerShell yang menjalankan <code>node-red</code> harus tetap terbuka. Buka lagi <code>http://127.0.0.1:1880</code>.</li>
<li><strong>Belum Deploy.</strong> Mengubah angka tanpa Deploy tidak dipakai. Klik tombol Deploy.</li>
<li><strong>Broker Node-RED salah.</strong> Di PC pakai <code>127.0.0.1</code>. Jangan meniru IPv4 ESP32 di Node-RED, dan jangan menaruh <code>127.0.0.1</code> di sketch ESP32.</li>
<li><strong>ESP32 memakai <code>127.0.0.1</code>.</strong> Ganti IPv4 PC dari <code>ipconfig</code>.</li>
<li><strong>Topic tidak sama persis.</strong> telemetry, command, dan status harus sama seperti sketch.</li>
<li><strong>Relay aktif HIGH.</strong> Ubah <code>AKTIF_LOW</code> menjadi <code>false</code>, Upload sekali, lalu kembali ke Node-RED.</li>
</ol>

<h2 id="fsiot-rules-checklist">Checklist sebelum FS-39</h2>
<p>Centang setelah kamu benar-benar melakukan setiap poin. Target: <strong>10/10</strong>. Progres disimpan di browser perangkatmu dan tidak dikirim ke server.</p>
<ul id="fsiot-rules-checklist-items">
<li>Jendela Mosquitto terbuka dan terlihat <code>1883</code>.</li>
<li>PowerShell menampilkan versi dari <code>node --version</code>.</li>
<li>Node-RED terbuka di <code>http://127.0.0.1:1880</code> dan jendela <code>node-red</code> tetap terbuka.</li>
<li>MQTTX Host = IPv4 PC, langganan telemetry dan status.</li>
<li>ESP32 di Wi-Fi rumah yang sama; <code>MQTT_HOST</code> = IPv4 PC, bukan <code>127.0.0.1</code>.</li>
<li>Wiring DHT22 GPIO 4 dan relay GPIO 26, NC/COM/NO kosong, bukan AC 220V.</li>
<li>Sketch tidak berisi ambang suhu; Serial menulis <code>Ambang ada di PC, bukan di sketch.</code></li>
<li>Serial menampilkan <code>MQTT tersambung.</code>, <code>Subscribe command siap.</code>, dan <code>Terkirim:</code>.</li>
<li>Saya mengubah angka switch, klik Deploy, relay berubah tanpa Upload.</li>
<li>Saya bisa menjelaskan kenapa aturan tidak boleh dobel di sketch dan di PC.</li>
</ul>
<p><strong>Cara memeriksa kesiapan:</strong> ceritakan dengan kata-katamu: suhu → PC → perintah → klik. Pada FS-39, Python masuk sebagai penerima data di PC — bukan pengganti Node-RED hari ini.</p>

<h2>Kesalahan yang sering terjadi</h2>
<ul>
<li><strong>Menulis if suhu di sketch.</strong> Itu mengembalikan otak ke perangkat. Hapus, Upload sekali, ambang di Node-RED.</li>
<li><strong>Memaksa Python hari ini.</strong> Ditunda ke FS-39.</li>
<li><strong>Menutup jendela <code>node-red</code>.</strong> Editor di browser ikut mati.</li>
<li><strong>ESP32 di <code>127.0.0.1</code>.</strong> Ganti IPv4 PC.</li>
<li><strong>Mengisi NC/COM/NO atau colok AC 220V.</strong> Tidak dipakai.</li>
<li><strong>Mengulang demo hotspot FS-37.</strong> Hari ini satu Wi-Fi rumah.</li>
</ul>

<h2>Pertanyaan yang sering muncul</h2>
<h3>Kenapa tidak Python saja?</h3>
<p>Python butuh pemasang, venv, dan pustaka. Node-RED menampilkan kotak. Python menyusul di FS-39 dan boleh jadi pelengkap aturan di FS-40.</p>
<h3>Kenapa ESP32 masih perlu Upload sekali?</h3>
<p>Perangkat harus tahu cara mengirim suhu dan mematuhi perintah. Yang tidak di-Upload ulang adalah <strong>pergantian ambang</strong>.</p>
<h3>Relay nyala-mati berganti cepat. Rusakkah?</h3>
<p>Suhu di sekitar ambang bisa mondar-mandir. Naikkan atau turunkan angka, lalu Deploy. Itu bukan kerusakan ESP32.</p>
<h3>Bolehkah Node-RED di HP?</h3>
<p>Hari ini di PC. HP boleh membuka MQTTX sebagai saksi, bukan menjalankan Node-RED.</p>
<h3>Apakah kartu microSD dipakai?</h3>
<p>Tidak. Antrian kartu sudah lewat di FS-37. Hari ini meja lebih sederhana: suhu, aturan, klik.</p>

<h2>Sumber</h2>
<ul>
<li><a href="https://nodered.org/docs/getting-started/windows" target="_blank" rel="noopener noreferrer">Node-RED — Running on Windows</a>. OpenJS Foundation. Apache License 2.0. Node-RED adalah merek OpenJS Foundation.</li>
<li><a href="https://nodejs.org/" target="_blank" rel="noopener noreferrer">Node.js</a> — unduh LTS. OpenJS Foundation.</li>
<li><a href="https://nodered.org/" target="_blank" rel="noopener noreferrer">Node-RED</a> (Apache License 2.0)</li>
<li><a href="https://mosquitto.org/man/mosquitto-conf-5.html" target="_blank" rel="noopener noreferrer">Mosquitto — konfigurasi listener</a></li>
<li><a href="https://mqttx.app/" target="_blank" rel="noopener noreferrer">MQTTX oleh EMQ</a> (Apache License 2.0)</li>
<li><a href="https://github.com/arduino-libraries/ArduinoMqttClient" target="_blank" rel="noopener noreferrer">ArduinoMqttClient</a></li>
<li><a href="https://arduinojson.org/v7/api/jsondocument/" target="_blank" rel="noopener noreferrer">ArduinoJson JsonDocument</a></li>
<li><a href="https://github.com/adafruit/DHT-sensor-library" target="_blank" rel="noopener noreferrer">Adafruit DHT sensor library</a></li>
<li><a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-installing-a-library/" target="_blank" rel="noopener noreferrer">Arduino — Installing libraries (IDE 2)</a>. Creative Commons Attribution-Share Alike 4.0. Arduino adalah merek Arduino S.r.l.</li>
<li><a href="https://commons.wikimedia.org/wiki/File:SRD-05VDC-SL-C_5V_one-channel_relay_module.jpg" target="_blank" rel="noopener noreferrer">SRD-05VDC-SL-C 5V one-channel relay module</a> · Suyash Dwivedi · Wikimedia Commons · Creative Commons Attribution-Share Alike 4.0. Jangan menyalin urutan kaki dari foto.</li>
<li><a href="https://commons.wikimedia.org/wiki/File:DHT_22_Sensor.jpg" target="_blank" rel="noopener noreferrer">AM2302 DHT22 Sensor</a> · L293D · Wikimedia Commons · Creative Commons Attribution-Share Alike 4.0. Jangan menyalin urutan kaki dari foto.</li>
<li>Diagram urutan tools, satu Wi-Fi, otak di PC, alur jika-maka, wiring, editor Node-RED, ganti ambang, MQTTX cadangan, dan skema periksa — Koding Indonesia (FS-38).</li>
</ul>

<h2>Selanjutnya</h2>
<p><strong>Ringkasnya:</strong> ambang hidup di PC. Pada <strong>FS-39</strong>, Python terpasang dari nol sebagai penerima data di komputer yang sama — pelengkap, bukan pengganti lab hari ini.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $sketch = htmlspecialchars($this->sketch(), ENT_QUOTES, 'UTF-8');
        $tools = $this->figure('fs38-tools-order.png', 'Five-step order: browser, Node.js LTS, Node-RED, Mosquitto plus MQTTX, then Arduino IDE', '<strong>Desk order (five steps):</strong> browser → Node.js LTS installer → PowerShell for Node-RED → Mosquitto + MQTTX → Arduino IDE. Upload once; change the threshold in Node-RED. Diagram by Koding Indonesia (FS-38).');
        $wifi = $this->figure('fs38-same-wifi.png', 'PC and ESP32 on the same home Wi-Fi; Node-RED uses 127.0.0.1, the ESP32 uses the PC IPv4', '<strong>One home Wi-Fi.</strong> The FS-37 hotspot demo is not used today. Node-RED on the PC talks to Mosquitto at <code>127.0.0.1</code>. The ESP32 uses the PC IPv4, not <code>127.0.0.1</code>. Diagram by Koding Indonesia (FS-38).');
        $brain = $this->figure('fs38-brain-on-pc.png', 'Comparison of an if temperature rule in the sketch versus a switch in Node-RED', '<strong>The firmware obeys. The PC decides.</strong> Do not write <code>if (suhu &gt; 30)</code> in the sketch. Diagram by Koding Indonesia (FS-38).');
        $flow = $this->figure('fs38-flow.png', 'Left-to-right flow: DHT22, Mosquitto, Node-RED, then the GPIO 26 relay', '<strong>Main figure — flow.</strong> Read left to right: temperature → broker → PC rule → relay click. Diagram by Koding Indonesia (FS-38).');
        $wiring = $this->figure('fs38-wiring.png', 'DHT22 GPIO 4 and relay GPIO 26 wiring to ESP32, NC COM NO terminals empty', '<strong>Wiring today.</strong> DHT22: VCC → 3V3, DATA → GPIO 4, GND → GND. 5V relay: VCC/+ → 5V, IN/S → GPIO 26, GND/− → GND. Leave NC/COM/NO empty. <strong>Do not connect AC mains.</strong> Diagram by Koding Indonesia (FS-38).');
        $editor = $this->figure('fs38-nodered-editor.png', 'Node-RED editor illustration: mqtt in telemetry, switch temperature greater than 30, change relay on or off', '<strong>Node-RED is already showing the if-then flow.</strong> Open <code>http://127.0.0.1:1880</code>. After you change the number, click <strong>Deploy</strong>. Illustration by Koding Indonesia (FS-38), modelled on the <a href="https://nodered.org/" target="_blank" rel="noopener noreferrer">Node-RED</a> editor by the OpenJS Foundation (Apache License 2.0). The official window screenshot is not used as-is.');
        $deploy = $this->figure('fs38-threshold-deploy.png', 'Threshold change order: double-click switch, change the number, click Deploy at the top right, relay follows without Upload', '<strong>Change the threshold in Node-RED, then Deploy.</strong> Deploy is at the <strong>top right</strong> of the editor, not the top left like Arduino Upload. Leave ESP32 USB plugged in. Diagram by Koding Indonesia (FS-38).');
        $manual = $this->figure('fs38-mqttx-manual.png', 'MQTTX illustration: see telemetry 31.2 then publish a relay on command', '<strong>Fallback path.</strong> You are the brain: watch temperature in MQTTX, then publish command JSON. Automation still belongs in Node-RED on the main path. Illustration by Koding Indonesia (FS-38), modelled on <a href="https://mqttx.app/" target="_blank" rel="noopener noreferrer">MQTTX by EMQ</a> (Apache License 2.0).');
        $troubleshooting = $this->figure('fs38-troubleshooting.png', 'Four checks when the relay does not follow the Node-RED threshold', '<strong>Helper schematic.</strong> Check the Node-RED window, Mosquitto, the Deploy button, then the ESP32 IPv4. Diagram by Koding Indonesia (FS-38).');
        $relayPhoto = $this->figure('kit-relay-5v.jpg', 'Example 5V one-channel Songle relay module with screw terminals and three header pins', '<strong>Relay appearance only.</strong> <strong>Do not copy pin order from the photo.</strong> Wiring follows the printed labels. NC/COM/NO screw terminals are <strong>not</strong> ESP32 pins, and <strong>not AC mains</strong>. Source: <a href="https://commons.wikimedia.org/wiki/File:SRD-05VDC-SL-C_5V_one-channel_relay_module.jpg" target="_blank" rel="noopener noreferrer">SRD-05VDC-SL-C 5V one-channel relay module</a> · Suyash Dwivedi · Wikimedia Commons · Creative Commons Attribution-Share Alike 4.0.');
        $dhtPhoto = $this->figure('kit-dht22.jpg', 'Example DHT22 AM2302 module with DAT, VCC, and GND pins', '<strong>Sensor appearance only.</strong> <strong>Do not copy pin order from the photo.</strong> Wiring is still VCC → 3V3, DATA or DAT → GPIO 4, GND → GND. Source: <a href="https://commons.wikimedia.org/wiki/File:DHT_22_Sensor.jpg" target="_blank" rel="noopener noreferrer">AM2302 DHT22 Sensor</a> · L293D · Wikimedia Commons · Creative Commons Attribution-Share Alike 4.0.');
        $install = $this->stepsCard([
            ['title' => 'Open a browser', 'text' => 'Use Chrome, Firefox, Edge, or Safari. Keep this guide ready. Do not click Upload in Arduino IDE yet.'],
            ['title' => 'Install Node.js LTS', 'text' => 'Open <a href="https://nodejs.org/" target="_blank" rel="noopener noreferrer">nodejs.org</a>. Download the <strong>LTS</strong> installer for Windows. Run the <code>.msi</code>, accept the defaults, finish. Close any old PowerShell window if it was already open.'],
            ['title' => 'Open PowerShell, install Node-RED', 'text' => 'Start → type <strong>PowerShell</strong> → Windows PowerShell. You do not need <em>Run as administrator</em>. Paste the commands below, one at a time. The window that runs <code>node-red</code> must stay open.'],
            ['title' => 'Open Mosquitto, then MQTTX', 'text' => 'Mosquitto first, as in FS-33/FS-34: keep the window open and look for <code>1883</code>. Only then MQTTX. Host = <strong>PC IPv4</strong> (not <code>127.0.0.1</code>), Port <code>1883</code>. Subscribe to telemetry and status.'],
            ['title' => 'Open Arduino IDE, Upload once', 'text' => 'ESP32 board. After Serial prints <code>Ambang ada di PC, bukan di sketch.</code> and <code>Terkirim:</code>, go back to Node-RED. The threshold changes there, not in the sketch.'],
        ], '<strong>How to test today:</strong> success = you change the switch number in Node-RED, click Deploy, the GPIO 26 relay follows, and Arduino IDE is <strong>not</strong> Uploaded again.');

        return <<<'HTML'
<h2>Introduction — the rule brain moves to the PC</h2>
<p><strong>FS-38 / #108 (this article)</strong> closes the CONNECTED phase. Yesterday the card held a queue while the hotspot was down. Today the job is different: <strong>if-then rules do not live on the ESP32</strong>.</p>
<p><strong>In short:</strong> the ESP32 only sends temperature and obeys commands. The threshold <code>30</code> lives in Node-RED on the PC. Change the number, click Deploy, the relay follows. No re-Upload.</p>
<p><strong>Analogy:</strong> the ESP32 is the warehouse worker. Node-RED is the notice board in the office. When the threshold changes, you change the board — not the worker.</p>
<p>Lab prerequisites: Mosquitto + MQTTX from FS-33/FS-34, GPIO 26 relay commands from FS-35, DHT22 on GPIO 4 from FS-34. The microSD card and phone hotspot are <strong>not</strong> used today. Python waits for FS-39.</p>

<h2>Expected outcome</h2>
<ul>
<li>Node-RED is open at <code>http://127.0.0.1:1880</code>, and the PowerShell window still shows the log.</li>
<li>MQTTX shows temperature JSON about every five seconds, then status JSON when the relay clicks.</li>
<li>Serial prints <code>Ambang ada di PC, bukan di sketch.</code>, <code>MQTT tersambung.</code>, <code>Subscribe command siap.</code>, then <code>Terkirim:</code>.</li>
<li>You change the switch number, click Deploy, the relay follows — without Upload.</li>
</ul>
<p><strong>Lab limits today:</strong> no web dashboard, Python, public broker, port forwarding, or AC mains. Proof = Node-RED + Serial + MQTTX + a click sound.</p>

<h2>Terms used today</h2>
<ul>
<li><strong>If-then rule</strong> — if temperature is above the threshold, send on. Otherwise off.</li>
<li><strong>Threshold</strong> — the comparison number. Today the example is <code>30</code>. Change it to match your room.</li>
<li><strong>Node-RED</strong> — a PC app for wiring boxes, not writing Python.</li>
<li><strong>Deploy</strong> — the red button at the <strong>top right</strong> of the Node-RED editor. It means: use the new flow. It is not Arduino Upload (top left).</li>
<li><strong>Device only</strong> — firmware with no <code>if (suhu &gt; 30)</code>. The ESP32 sends data and obeys commands.</li>
<li><strong>Fallback path</strong> — you watch MQTTX and publish the command yourself. For the concept, not a replacement for Node-RED.</li>
</ul>

<h2>Preparation — open the right tools first</h2>
HTML
            .$tools.$install.<<<'HTML'
<p><strong>Not used today:</strong> Python, Laragon, <code>php artisan</code>, a microSD card, the phone hotspot, a public broker, or turning off the home router. <strong>Do not connect AC mains.</strong> Leave the relay NC/COM/NO terminals empty.</p>
<p><strong>Phone tip:</strong> if a diagram feels small, use the browser zoom. You do not need to tap the image until it fills the screen; nearby text should stay readable.</p>

<h2>Lab topology — one home Wi-Fi</h2>
HTML
            .$wifi.$flow.<<<'HTML'
<p>Today the ESP32 and the PC go back to <strong>the same home Wi-Fi</strong>, as in FS-34 and FS-35. The FS-37 hotspot demo is not repeated: the thing under test is the PC rule, not the card queue.</p>
<ol>
<li>The <strong>PC</strong> runs Mosquitto, MQTTX, and Node-RED. Node-RED connects to the broker at <code>127.0.0.1</code> port <code>1883</code>.</li>
<li>The <strong>ESP32</strong> uses the home Wi-Fi SSID. <code>MQTT_HOST</code> = the PC IPv4 from <code>ipconfig</code>, not <code>127.0.0.1</code>.</li>
<li>Leave ESP32 USB plugged in so Serial Monitor can be read.</li>
</ol>

<h2>Find the PC IPv4, start Mosquitto, open MQTTX</h2>
<p><strong>Open PowerShell first:</strong> Start → type <strong>PowerShell</strong> → Windows PowerShell. You do not need <em>Run as administrator</em>.</p>
<p><strong>How to paste:</strong> copy the line, click the PowerShell window, then <kbd>Ctrl</kbd>+<kbd>V</kbd> or right-click. When the text appears, press Enter.</p>
<pre><code>ipconfig</code></pre>
<p>Find <strong>IPv4 Address</strong> on the home Wi-Fi adapter. The article example is <code>192.168.1.23</code>; yours will almost certainly differ.</p>
<p>Reuse <code>mosquitto-fs34.conf</code> from FS-34 if you still have it:</p>
<pre><code>listener 1883 192.168.1.23
listener_allow_anonymous true</code></pre>
<p>Then:</p>
<pre><code>&amp; 'C:\Program Files\mosquitto\mosquitto.exe' -c "$env:USERPROFILE\Documents\mosquitto-fs34.conf" -v</code></pre>
<p><strong>What to look for:</strong> the window stays open and shows <code>1883</code>. Stop with <strong>Ctrl+C</strong> after the lab. Do not open a router port. Reference: <a href="https://mosquitto.org/man/mosquitto-conf-5.html" target="_blank" rel="noopener noreferrer">Mosquitto configuration docs</a>.</p>
<p>Keep the Mosquitto window open. Only now open <strong>MQTTX</strong>. If it is not installed, download it from <a href="https://mqttx.app/downloads" target="_blank" rel="noopener noreferrer">mqttx.app/downloads</a> as in FS-32.</p>
<ol>
<li>Click <em>New Connection</em>, name <code>FS38 rules LAN</code>.</li>
<li>Host = PC IPv4, Port = <code>1883</code>. Not <code>127.0.0.1</code>.</li>
<li>Click <em>Connect</em>, then subscribe to two topics:</li>
</ol>
<pre><code>kodingindonesia/fsiot/esp32-meja-01/telemetry
kodingindonesia/fsiot/esp32-meja-01/status</code></pre>
<p><strong>What to look for:</strong> MQTTX shows connected. There is no JSON yet until the ESP32 sends. MQTTX is a witness today, not a replacement for Node-RED on the main path.</p>

<h2>Install Node.js, then Node-RED</h2>
<p><strong>Open a browser first</strong> at <a href="https://nodejs.org/" target="_blank" rel="noopener noreferrer">nodejs.org</a>. Download the <strong>LTS</strong> button, not Current. Run the Windows installer (<code>.msi</code>). Accept the defaults. When it finishes, <strong>close the old PowerShell</strong> and open a new one so the <code>node</code> command is recognised.</p>
<p>In the new PowerShell:</p>
<pre><code>node --version
npm --version</code></pre>
<p><strong>What to look for:</strong> two version lines, for example <code>v22.x</code> and <code>10.x</code>. Your exact numbers may differ.</p>
<p>Install Node-RED globally, then run it. <strong>Do not close</strong> this window during the lab:</p>
<pre><code>npm install -g node-red
node-red</code></pre>
<p>Wait until the log shows the editor address. <strong>Open a browser</strong> at:</p>
<pre><code>http://127.0.0.1:1880</code></pre>
<p>That is Node-RED on this computer, not an invitation to the internet. Reference: <a href="https://nodered.org/docs/getting-started/windows" target="_blank" rel="noopener noreferrer">Running on Windows — Node-RED</a>, OpenJS Foundation, Apache License 2.0.</p>
<p><strong>macOS:</strong> open <strong>Terminal</strong> first, install Node.js LTS from nodejs.org, then the same <code>npm</code> and <code>node-red</code> commands. <strong>Ubuntu or Debian:</strong> open <strong>Terminal</strong> first; follow the <a href="https://nodered.org/docs/getting-started/local" target="_blank" rel="noopener noreferrer">local Node-RED docs</a>. Do not use Python as a substitute today.</p>

<h2>Wire the cables — DHT22 and relay</h2>
HTML
            .$wiring.$dhtPhoto.$relayPhoto.<<<'HTML'
<p>Unplug ESP32 USB before tidying cables. <strong>Do not guess pins.</strong></p>
<table>
<thead><tr><th>Label on the module</th><th>ESP32 GPIO / pin</th></tr></thead>
<tbody>
<tr><td>DHT22 VCC</td><td><strong>3V3</strong></td></tr>
<tr><td>DHT22 DATA or DAT</td><td><strong>4</strong></td></tr>
<tr><td>DHT22 GND</td><td>GND</td></tr>
<tr><td>Relay VCC or +</td><td><strong>5V</strong></td></tr>
<tr><td>Relay IN or S</td><td><strong>26</strong></td></tr>
<tr><td>Relay GND or −</td><td>GND</td></tr>
<tr><td>NC / COM / NO</td><td><strong>empty</strong></td></tr>
</tbody>
</table>
<p><strong>Do not connect AC mains.</strong> The microSD card is not fitted today.</p>

<h2>Why the threshold must not live in the sketch</h2>
HTML
            .$brain.<<<'HTML'
<p>If the threshold is in the sketch, every number change means Verify, Upload, wait. In an office that is like calling a technician every time the thermostat moves. Node-RED holds the number. The ESP32 stays the device.</p>
<p>Do not double up: an if in the sketch <strong>plus</strong> a switch in Node-RED. One rule, one place — today that place is the PC.</p>

<h2>ESP32 sketch — device only</h2>
<p><strong>Open Arduino IDE first.</strong> Choose an <strong>ESP32</strong> board, not an UNO. <strong>ArduinoMqttClient</strong>, <strong>ArduinoJson</strong>, and Adafruit <strong>DHT sensor library</strong> are usually already there from FS-34/FS-35. If the Verify button complains about a missing header: click the three-book icon (Library Manager) in the left bar. That is the only path used today. Do not use the old <em>Tools → Manage Libraries</em> menu.</p>
<p>Create a new sketch named <code>FS38_device_only</code>. Replace the home Wi-Fi, password, and <code>192.168.1.23</code> with your PC IPv4. Do not put a real password in a screenshot. This sketch <strong>does not</strong> contain a temperature threshold.</p>
<pre><code class="language-arduino">
HTML
            .$sketch.<<<'HTML'
</code></pre>
<p>Click Verify, then Upload. Open <strong>Tools → Serial Monitor</strong>, baud <strong>115200</strong>. That menu is <strong>not</strong> Library Manager. Look for <code>Ambang ada di PC, bukan di sketch.</code>, then <code>MQTT tersambung.</code>, <code>Subscribe command siap.</code>, and <code>Terkirim:</code>. References: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-installing-a-library/" target="_blank" rel="noopener noreferrer">Installing libraries — Arduino IDE 2</a>, Arduino S.r.l., Creative Commons Attribution-Share Alike 4.0. <a href="https://github.com/arduino-libraries/ArduinoMqttClient" target="_blank" rel="noopener noreferrer">ArduinoMqttClient</a>. <a href="https://arduinojson.org/v7/api/jsondocument/" target="_blank" rel="noopener noreferrer">ArduinoJson</a>. <a href="https://github.com/adafruit/DHT-sensor-library" target="_blank" rel="noopener noreferrer">Adafruit DHT</a>.</p>

<h2>Build the Node-RED flow, then Deploy</h2>
HTML
            .$editor.$deploy.<<<'HTML'
<p>In the Node-RED editor, drag boxes from the left palette onto the canvas. Do not type Python.</p>
<ol>
<li>Drag <strong>mqtt in</strong>. Double-click. Add a broker: Server <code>127.0.0.1</code>, Port <code>1883</code>. Topic <code>kodingindonesia/fsiot/esp32-meja-01/telemetry</code>. Output: auto-detect JSON. Click Done.</li>
<li>Drag <strong>switch</strong>. Property <code>msg.payload.temperature_c</code>. First rule: <em>&gt;</em> number <code>30</code>. Add an otherwise rule. Click Done.</li>
<li>Drag two <strong>change</strong> nodes. The upper one: set <code>msg.payload</code> to JSON <code>{"device_id":"esp32-meja-01","relay":"on"}</code>. The lower one: <code>"relay":"off"</code> with the same <code>device_id</code>.</li>
<li>Drag <strong>mqtt out</strong>, twice if needed, topic <code>kodingindonesia/fsiot/esp32-meja-01/command</code>, same broker. Wire change on and change off to mqtt out.</li>
<li>Click <strong>Deploy</strong> at the top right. Do not click Upload in Arduino IDE.</li>
</ol>
<p>Blow gently on the DHT22 or hold a finger near it so temperature crosses the threshold. Serial prints <code>Perintah:</code> then <code>Relay ON</code>. MQTTX shows status. To test without waiting for weather: lower the threshold below room temperature, Deploy again.</p>
<p><strong>Success means:</strong> changing the number + Deploy changes relay behaviour, while the sketch is untouched.</p>

<h2>Fallback path in MQTTX</h2>
HTML
            .$manual.<<<'HTML'
<p>If Node-RED still feels new, you may be the brain first. Watch telemetry. If <code>temperature_c</code> is above the threshold you chose, publish to the command topic:</p>
<pre><code>{"device_id":"esp32-meja-01","relay":"on"}</code></pre>
<p>To turn it off, use <code>"relay":"off"</code>. This is the learning path. Automation still belongs in Node-RED on the main path.</p>

<h2>If the relay does not follow the threshold</h2>
HTML
            .$troubleshooting.<<<'HTML'
<ol>
<li><strong>The Node-RED window was closed.</strong> The PowerShell that runs <code>node-red</code> must stay open. Open <code>http://127.0.0.1:1880</code> again.</li>
<li><strong>You did not Deploy.</strong> Changing the number without Deploy does nothing. Click Deploy.</li>
<li><strong>The Node-RED broker is wrong.</strong> On the PC use <code>127.0.0.1</code>. Do not copy the ESP32 IPv4 into Node-RED, and do not put <code>127.0.0.1</code> in the ESP32 sketch.</li>
<li><strong>The ESP32 uses <code>127.0.0.1</code>.</strong> Replace it with the PC IPv4 from <code>ipconfig</code>.</li>
<li><strong>Topics are not exact.</strong> telemetry, command, and status must match the sketch.</li>
<li><strong>The relay is active HIGH.</strong> Set <code>AKTIF_LOW</code> to <code>false</code>, Upload once, then go back to Node-RED.</li>
</ol>

<h2 id="fsiot-rules-checklist">Checklist before FS-39</h2>
<p>Tick each item after you actually did it. Target: <strong>10/10</strong>. Progress stays in this browser and is not sent to the server.</p>
<ul id="fsiot-rules-checklist-items">
<li>The Mosquitto window is open and shows <code>1883</code>.</li>
<li>PowerShell shows a version from <code>node --version</code>.</li>
<li>Node-RED is open at <code>http://127.0.0.1:1880</code> and the <code>node-red</code> window stays open.</li>
<li>MQTTX Host = PC IPv4, subscribed to telemetry and status.</li>
<li>The ESP32 is on the same home Wi-Fi; <code>MQTT_HOST</code> = PC IPv4, not <code>127.0.0.1</code>.</li>
<li>Wiring DHT22 GPIO 4 and relay GPIO 26, NC/COM/NO empty, no AC mains.</li>
<li>The sketch has no temperature threshold; Serial prints <code>Ambang ada di PC, bukan di sketch.</code></li>
<li>Serial shows <code>MQTT tersambung.</code>, <code>Subscribe command siap.</code>, and <code>Terkirim:</code>.</li>
<li>I changed the switch number, clicked Deploy, and the relay followed without Upload.</li>
<li>I can explain why the rule must not be duplicated in the sketch and on the PC.</li>
</ul>
<p><strong>How to check readiness:</strong> tell it in your own words: temperature → PC → command → click. On FS-39, Python arrives as a data receiver on the same PC — not a substitute for today's lab.</p>

<h2>Common mistakes</h2>
<ul>
<li><strong>Writing if temperature in the sketch.</strong> That puts the brain back on the device. Remove it, Upload once, keep the threshold in Node-RED.</li>
<li><strong>Forcing Python today.</strong> It waits for FS-39.</li>
<li><strong>Closing the <code>node-red</code> window.</strong> The browser editor dies with it.</li>
<li><strong>ESP32 on <code>127.0.0.1</code>.</strong> Use the PC IPv4.</li>
<li><strong>Wiring NC/COM/NO or connecting AC mains.</strong> Not used.</li>
<li><strong>Repeating the FS-37 hotspot demo.</strong> Today is one home Wi-Fi.</li>
</ul>

<h2>Frequently asked questions</h2>
<h3>Why not Python only?</h3>
<p>Python needs an installer, a venv, and libraries. Node-RED shows boxes. Python follows in FS-39 and may complement rules in FS-40.</p>
<h3>Why does the ESP32 still need one Upload?</h3>
<p>The device must know how to send temperature and obey commands. What you do not re-Upload is <strong>the threshold change</strong>.</p>
<h3>The relay chatters on and off. Is it broken?</h3>
<p>Temperature around the threshold can wobble. Raise or lower the number, then Deploy. That is not an ESP32 fault.</p>
<h3>May I run Node-RED on a phone?</h3>
<p>Today it runs on the PC. A phone may open MQTTX as a witness, not run Node-RED.</p>
<h3>Is the microSD card used?</h3>
<p>No. The card queue already happened in FS-37. Today the desk is simpler: temperature, rule, click.</p>

<h2>Sources</h2>
<ul>
<li><a href="https://nodered.org/docs/getting-started/windows" target="_blank" rel="noopener noreferrer">Node-RED — Running on Windows</a>. OpenJS Foundation. Apache License 2.0. Node-RED is a trademark of the OpenJS Foundation.</li>
<li><a href="https://nodejs.org/" target="_blank" rel="noopener noreferrer">Node.js</a> — download LTS. OpenJS Foundation.</li>
<li><a href="https://nodered.org/" target="_blank" rel="noopener noreferrer">Node-RED</a> (Apache License 2.0)</li>
<li><a href="https://mosquitto.org/man/mosquitto-conf-5.html" target="_blank" rel="noopener noreferrer">Mosquitto — listener configuration</a></li>
<li><a href="https://mqttx.app/" target="_blank" rel="noopener noreferrer">MQTTX by EMQ</a> (Apache License 2.0)</li>
<li><a href="https://github.com/arduino-libraries/ArduinoMqttClient" target="_blank" rel="noopener noreferrer">ArduinoMqttClient</a></li>
<li><a href="https://arduinojson.org/v7/api/jsondocument/" target="_blank" rel="noopener noreferrer">ArduinoJson JsonDocument</a></li>
<li><a href="https://github.com/adafruit/DHT-sensor-library" target="_blank" rel="noopener noreferrer">Adafruit DHT sensor library</a></li>
<li><a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-installing-a-library/" target="_blank" rel="noopener noreferrer">Arduino — Installing libraries (IDE 2)</a>. Creative Commons Attribution-Share Alike 4.0. Arduino is a trademark of Arduino S.r.l.</li>
<li><a href="https://commons.wikimedia.org/wiki/File:SRD-05VDC-SL-C_5V_one-channel_relay_module.jpg" target="_blank" rel="noopener noreferrer">SRD-05VDC-SL-C 5V one-channel relay module</a> · Suyash Dwivedi · Wikimedia Commons · Creative Commons Attribution-Share Alike 4.0. Do not copy pin order from the photo.</li>
<li><a href="https://commons.wikimedia.org/wiki/File:DHT_22_Sensor.jpg" target="_blank" rel="noopener noreferrer">AM2302 DHT22 Sensor</a> · L293D · Wikimedia Commons · Creative Commons Attribution-Share Alike 4.0. Do not copy pin order from the photo.</li>
<li>Diagrams for tool order, one Wi-Fi, brain on the PC, if-then flow, wiring, Node-RED editor, threshold change, MQTTX fallback, and the check schematic — Koding Indonesia (FS-38).</li>
</ul>

<h2>Next</h2>
<p><strong>In short:</strong> the threshold lives on the PC. On <strong>FS-39</strong>, Python is installed from scratch as a data receiver on the same computer — a complement, not a replacement for today's lab.</p>
HTML;
    }
}
