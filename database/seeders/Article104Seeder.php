<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class Article104Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@kodingindonesia.com')->first() ?? User::first();
        $category = Category::where('slug', 'iot-smart-device')->first() ?? Category::where('slug', 'esp32-arduino')->first();
        if (! $admin || ! $category) {
            throw new \RuntimeException('User atau kategori IoT tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-esp32-dht22-mqtt-json-telemetry';
        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        foreach (['fullstack-iot', 'iot', 'mqtt', 'esp32', 'dht22', 'json'] as $tagSlug) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagSlug]);
        }

        $article = Article::updateOrCreate(['slug' => $slug], [
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
        ]);
        $article->tags()->sync(Tag::whereIn('slug', ['fullstack-iot', 'iot', 'mqtt', 'esp32', 'dht22', 'json'])->pluck('id'));

        foreach (['webp', 'jpg'] as $extension) {
            $source = public_path("images/fsiot/fs34-cover-telemetry.{$extension}");
            if (is_file($source)) {
                $destination = "articles/covers/fs34-cover-telemetry.{$extension}";
                Storage::disk('public')->put($destination, file_get_contents($source));
            }
        }
        $article->update([
            'cover_image' => 'https://kodingindonesia.com/images/fsiot/fs34-cover-telemetry.webp',
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
        $sketch = htmlspecialchars($this->sketch(), ENT_QUOTES, 'UTF-8');
        $tools = $this->figure('fs34-tools-order.png', 'Urutan lima langkah: browser, Arduino IDE, kabel DHT22, PowerShell, lalu MQTTX', '<strong>Urutan meja kerja (lima langkah):</strong> browser → Arduino IDE (ikon buku) → kabel DHT22 → PowerShell untuk IP dan broker → MQTTX. Connect MQTTX hanya setelah angka <code>1883</code> terlihat. Diagram buatan Koding Indonesia (FS-34).');
        $library = $this->figure('fs34-library-manager.png', 'Ilustrasi Library Manager Arduino IDE 2: ikon tiga buku, pencarian DHT sensor library, tombol INSTALL, papan ESP32', '<strong>Ini tampilan yang benar, bukan layar error.</strong> Ikon tiga buku di bilah kiri, ketik <em>DHT sensor library</em>, lalu pilih <strong>INSTALL</strong>. Papan di pojok kanan adalah <strong>ESP32</strong>, bukan UNO. Ilustrasi buatan Koding Indonesia (FS-34), meniru Arduino IDE 2. Screenshot jendela resmi tidak dipakai utuh karena gelap dan menampilkan papan UNO. Acuan langkah: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-installing-a-library/" target="_blank" rel="noopener noreferrer">Installing libraries — Arduino IDE 2</a>, Arduino S.r.l. Dokumentasi Arduino berlisensi Creative Commons Attribution-Share Alike 4.0.');
        $wiring = $this->figure('fs34-wiring-dht22.png', 'Wiring modul DHT22 tiga pin ke ESP32 menurut label VCC, DATA, dan GND', '<strong>Gambar utama — wiring.</strong> Cocokkan tulisan pin: VCC → 3V3, DATA atau DAT → GPIO 4, GND → GND. Urutan kaki fisik bisa berbeda antarmodul. Diagram buatan Koding Indonesia (FS-34).');
        $dhtPhoto = $this->figure('kit-dht22.jpg', 'Contoh rupa modul DHT22 AM2302 pada papan merah, tiga pin berlabel DAT, VCC, dan GND', '<strong>Contoh rupa modul saja.</strong> Foto ini membantu mengenali sensor. <strong>Jangan menyalin urutan kaki dari foto.</strong> Wiring tetap menurut tulisan pin: VCC → 3V3, DATA atau DAT → GPIO 4, GND → GND. Pada foto ini, dari atas ke bawah tertulis DAT / VCC / GND — itu milik modul ini, bukan milik semua modul. Sumber: <a href="https://commons.wikimedia.org/wiki/File:DHT_22_Sensor.jpg" target="_blank" rel="noopener noreferrer">AM2302 DHT22 Sensor</a> · L293D · Wikimedia Commons · Creative Commons Attribution-Share Alike 4.0.');
        $lan = $this->figure('fs34-lan-address.png', 'ESP32 memakai alamat IPv4 LAN PC, bukan localhost atau 127.0.0.1', '<strong>Aturan penting:</strong> <code>127.0.0.1</code> pada ESP32 berarti ESP32 itu sendiri. Gunakan IPv4 PC dari <code>ipconfig</code>. Diagram buatan Koding Indonesia (FS-34).');
        $flow = $this->figure('fs34-json-flow.png', 'Alur kiri ke kanan: DHT22, ESP32, Mosquitto, lalu MQTTX menampilkan JSON', '<strong>Gambar utama — alur data.</strong> Baca dari kiri ke kanan: DHT22 → ESP32 → Mosquitto → MQTTX. Diagram buatan Koding Indonesia (FS-34).');
        $troubleshooting = $this->figure('fs34-troubleshooting.png', 'Empat pemeriksaan jika telemetry ESP32 belum muncul di MQTTX', '<strong>Skema bantu.</strong> Periksa kabel, Wi-Fi, broker serta IP PC, lalu MQTTX. Diagram buatan Koding Indonesia (FS-34).');
        $install = $this->stepsCard([
            ['title' => 'Buka browser', 'text' => 'Pakai Chrome, Firefox, Edge, atau Safari. Siapkan artikel ini dan halaman resmi Arduino. Jangan Upload sketch dulu.'],
            ['title' => 'Buka Arduino IDE', 'text' => 'Klik ikon buku di bilah kiri untuk Library Manager. Jangan memakai menu lama <em>Tools → Manage Libraries</em>. Belum perlu menempel kode.'],
            ['title' => 'Siapkan kabel DHT22', 'text' => 'Cabut USB ESP32 dulu. Modul tiga pin: VCC → 3V3, DATA → GPIO 4, GND → GND. Baca tulisan pin, jangan menebak urutan kaki.'],
            ['title' => 'Buka PowerShell hanya untuk IP dan broker', 'text' => 'Tekan Start → ketik <strong>PowerShell</strong> → pilih <strong>Windows PowerShell</strong>. Tidak perlu <em>Run as administrator</em>. Perintahnya ada di bagian IPv4 dan Mosquitto.'],
            ['title' => 'Buka MQTTX setelah broker berjalan', 'text' => 'Baru sekarang klik <em>New Connection</em>. Isi Host dengan <strong>IPv4 PC</strong> (bukan <code>127.0.0.1</code>) dan Port <code>1883</code>. Jika MQTTX belum ada, pasang dari <a href="https://mqttx.app/downloads" target="_blank" rel="noopener noreferrer">mqttx.app/downloads</a> seperti di FS-32.'],
        ], '<strong>Cara menguji hari ini:</strong> bukti sukses = jendela Mosquitto tetap terbuka dan terlihat <code>1883</code>, Serial Monitor menulis <code>MQTT tersambung.</code> lalu <code>Terkirim:</code>, dan MQTTX menampilkan JSON baru kira-kira setiap lima detik.');

        return <<<'HTML'
<h2>Pendahuluan — sensor akhirnya berbicara ke broker</h2>
<p><strong>FS-34 / #104 (ini)</strong> menyambungkan DHT22, ESP32, dan Mosquitto lokal dari FS-33. ESP32 membaca suhu serta kelembapan, membungkusnya sebagai JSON, lalu mengirimkannya ke broker. MQTTX di PC menunjukkan hasilnya.</p>
<p><strong>Intinya:</strong> ESP32 menjadi pengirim telemetry. Mosquitto tetap menjadi kantor pos. MQTTX hanya melihat surat yang sudah sampai.</p>
<p><strong>Analogi:</strong> DHT22 adalah termometer. ESP32 menulis angka itu di secarik kertas JSON. Mosquitto meneruskan kertas itu. MQTTX membacanya di meja belajar.</p>
<p>Di FS-33 kita tersambung ke <code>127.0.0.1</code> karena MQTTX dan Mosquitto berada di komputer yang sama. Hari ini ESP32 adalah perangkat lain, jadi Host MQTTX dan <code>MQTT_HOST</code> memakai <strong>IPv4 PC</strong>.</p>

<h2>Hasil yang dituju</h2>
<ul>
<li>DHT22 terpasang ke ESP32 dengan aman.</li>
<li>ESP32 terhubung ke Wi-Fi yang sama dengan PC.</li>
<li>MQTTX menerima JSON seperti <code>{"device_id":"esp32-meja-01","temperature_c":27.4,"humidity_pct":63.1}</code>.</li>
<li>Topic telemetry menyebut <code>device_id</code> sejak awal.</li>
</ul>
<p><strong>Batas lab hari ini:</strong> tidak ada relay, dashboard web, akun cloud, atau broker publik. Tidak ada port forwarding di router.</p>

<h2>Istilah yang dipakai hari ini</h2>
<ul>
<li><strong>Telemetry</strong> — data yang dikirim perangkat secara berkala, di sini suhu dan kelembapan.</li>
<li><strong>JSON</strong> — cara merapikan data dengan nama dan nilai, misalnya <code>"temperature_c":27.4</code>.</li>
<li><strong>DHT22</strong> — sensor suhu dan kelembapan. Lab ini memakai modul tiga pin berlabel.</li>
<li><strong>Library Manager</strong> — panel Arduino IDE 2 di bilah kiri, ikon tiga buku. Bukan menu <em>Tools</em> lama.</li>
<li><strong>IPv4 PC</strong> — alamat komputer di Wi-Fi rumah, misalnya <code>192.168.1.23</code>. Bukan contoh tetap; milikmu hampir pasti berbeda.</li>
<li><strong><code>127.0.0.1</code> / <code>localhost</code></strong> — “perangkat ini sendiri”. Di ESP32, itu berarti ESP32, bukan PC.</li>
<li><strong><code>listener</code></strong> — baris konfigurasi yang membuka pintu Mosquitto ke alamat tertentu. FS-33 tidak memakainya; hari ini kita menambahkannya <strong>sementara</strong>, terikat ke IPv4 PC.</li>
<li><strong>Jaringan Private</strong> — izin Windows untuk Wi-Fi rumah tepercaya. Jangan pilih Public, jangan guest Wi-Fi.</li>
<li><strong>Broker publik</strong> — broker di internet milik pihak lain. Latihan ini tidak memakainya.</li>
</ul>

<h2>Persiapan — buka tool yang benar dulu</h2>
HTML
            .$tools.$install.<<<'HTML'
<p><strong>Jangan dipakai hari ini:</strong> broker publik, port forwarding/router, relay, akun cloud, Laragon, <code>php artisan</code>, atau dashboard web.</p>
<p><strong>Tips ponsel:</strong> jika diagram terasa kecil, gunakan fitur perbesar pada browser atau layar. Gambar tidak perlu diketuk sampai memenuhi layar agar teks di sekitarnya tetap terbaca.</p>
<p><strong>Jika MQTTX belum terpasang:</strong> selesaikan MQTTX Desktop dari <a href="https://mqttx.app/downloads" target="_blank" rel="noopener noreferrer">mqttx.app/downloads</a> dulu, lalu kembali ke langkah 5.</p>

<h2>Pasang library di Arduino IDE</h2>
HTML
            .$library.<<<'HTML'
<p><strong>Buka dulu Arduino IDE.</strong> Di bilah kiri, klik <strong>Library Manager</strong>: ikon tiga buku. Pada tampilan yang menyediakan menu, kamu juga dapat membuka <strong>Sketch → Include Library → Manage Libraries</strong>. Cari dan pasang satu per satu:</p>
<ol>
<li><strong>DHT sensor library</strong> oleh Adafruit. Jika diminta memasang <strong>Adafruit Unified Sensor</strong>, pilih <strong>Install All</strong>; dependensi itu memang dibutuhkan library DHT.</li>
<li><strong>ArduinoMqttClient</strong> oleh Arduino. Library ini mendukung pengiriman MQTT dan contoh resminya menyertakan ESP32.</li>
<li><strong>ArduinoJson</strong> oleh Benoit Blanchon. Lab ini memakai <code>JsonDocument</code> dan <code>serializeJson()</code>, jadi kamu tidak perlu merangkai JSON dengan tangan.</li>
</ol>
<p>Sebelum Verify, pilih board ESP32 dan port USB yang benar dari pemilih board di bagian atas Arduino IDE. Dokumentasi resmi Arduino kadang menampilkan UNO hanya sebagai contoh; jangan meniru papan itu untuk lab ini. Jika board ESP32 belum tersedia, selesaikan pemasangan board package dari modul sebelumnya terlebih dahulu.</p>
<p>Rujukan langkah ikon buku: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-installing-a-library/" target="_blank" rel="noopener noreferrer">dokumentasi memasang library Arduino IDE 2</a>.</p>

<h2>Pasang kabel DHT22</h2>
HTML
            .$wiring.$dhtPhoto.<<<'HTML'
<ol>
<li>Cabut USB ESP32.</li>
<li>Pada <strong>modul DHT22 tiga pin</strong>, sambungkan <strong>VCC → 3V3</strong>, <strong>DATA → GPIO 4</strong>, dan <strong>GND → GND</strong>. Beberapa modul menulis <strong>DAT</strong> untuk kaki data.</li>
<li>Pasang kembali USB data ke ESP32.</li>
</ol>
<p><strong>Jangan menebak pin.</strong> Sensor DHT22 bare empat kaki dan modul tiga pin tidak memiliki tata letak yang sama. Artikel ini memakai modul tiga pin berlabel VCC, DATA atau DAT, dan GND. Bila milikmu berbeda, berhenti dulu dan cocokkan tulisan pin pada modul atau kemasannya.</p>

<h2>Cari alamat IPv4 komputer</h2>
<p><strong>Buka dulu PowerShell:</strong> tekan Start → ketik <strong>PowerShell</strong> → pilih <strong>Windows PowerShell</strong>. Tidak perlu <em>Run as administrator</em>.</p>
<p><strong>Cara menempel perintah:</strong> salin baris di bawah, klik jendela PowerShell, lalu tekan <kbd>Ctrl</kbd>+<kbd>V</kbd> atau klik kanan. Setelah teks muncul, tekan Enter.</p>
<pre><code>ipconfig</code></pre>
<p>Cari <strong>IPv4 Address</strong> pada adaptor Wi-Fi yang sedang dipakai. Contoh artikel memakai <code>192.168.1.23</code>; punyamu hampir pasti berbeda. Catat angka itu. Itulah Host MQTTX dan <code>MQTT_HOST</code>.</p>
<p><strong>macOS:</strong> buka aplikasi <strong>Terminal</strong> dulu, lalu jalankan <code>ifconfig</code> dan cari <code>inet</code> pada Wi-Fi. <strong>Ubuntu atau Debian:</strong> buka <strong>Terminal</strong> dulu, lalu jalankan <code>ip addr</code>.</p>

<h2>Jalankan Mosquitto agar ESP32 boleh masuk</h2>
HTML
            .$lan.<<<'HTML'
<p>Pada FS-33, broker hanya menerima aplikasi PC melalui <code>127.0.0.1</code>. Sekarang ESP32 adalah perangkat lain, sehingga kita membuat <strong>akses LAN sementara</strong> yang hanya terikat ke IPv4 PC rumah. Ini lab singkat di Wi-Fi tepercaya, bukan undangan ke internet.</p>
<ol>
<li>Buka <strong>Notepad</strong>, tempel dua baris berikut, dan ganti alamat contoh dengan IPv4 PC milikmu:</li>
</ol>
<pre><code>listener 1883 192.168.1.23
listener_allow_anonymous true</code></pre>
<ol start="2">
<li>Simpan sebagai <code>mosquitto-fs34.conf</code> di folder <strong>Documents</strong>. Pilih <em>Save as type: All files</em> agar Notepad tidak menambahkan akhiran <code>.txt</code>.</li>
<li>Kembali ke PowerShell. <strong>Cara menempel perintah:</strong> salin baris di bawah, klik jendela PowerShell, lalu <kbd>Ctrl</kbd>+<kbd>V</kbd> atau klik kanan, kemudian Enter.</li>
</ol>
<pre><code>&amp; 'C:\Program Files\mosquitto\mosquitto.exe' -c "$env:USERPROFILE\Documents\mosquitto-fs34.conf" -v</code></pre>
<p><strong>Hasil yang dicari:</strong> jendela tetap terbuka dan terlihat angka <code>1883</code>. Biarkan jendela ini terbuka selama praktik. Huruf <code>-v</code> menampilkan catatan kerja broker.</p>
<p>Jika Windows meminta izin jaringan, pilih hanya <strong>Private networks</strong> pada Wi-Fi rumah tepercaya; jangan mengizinkan jaringan Public. Jangan membuka port router atau memakai Wi-Fi tamu.</p>
<p><strong>Mengapa ada akses anonim?</strong> Konfigurasi dua baris ini hanya untuk lab LAN singkat, agar ESP32 dan MQTTX dapat tersambung tanpa akun. Mosquitto mendukung listener yang diikat ke alamat IP tertentu dan opsi anonim per listener. Hentikan broker dengan <strong>Ctrl+C</strong> setelah selesai latihan. Pengguna, sandi, TLS, dan akses yang lebih aman dibahas pada FS-49. Rujukan: <a href="https://mosquitto.org/man/mosquitto-conf-5.html" target="_blank" rel="noopener noreferrer">dokumentasi konfigurasi Mosquitto</a>.</p>
<p><strong>macOS atau Linux:</strong> buka <strong>Terminal</strong> dulu, simpan berkas konfigurasi yang sama, lalu jalankan <code>mosquitto -c ~/Documents/mosquitto-fs34.conf -v</code> dan biarkan jendela terbuka.</p>

<h2>Hubungkan MQTTX ke IPv4 PC</h2>
<p>Pastikan jendela Mosquitto masih terbuka dan terlihat <code>1883</code>. Baru sekarang buka <strong>MQTTX</strong>.</p>
<ol>
<li>Klik <em>New Connection</em> dan beri nama <code>FS34 telemetry LAN</code>.</li>
<li>Isi <strong>Host</strong> dengan IPv4 PC yang sama, misalnya <code>192.168.1.23</code>. Jangan gunakan <code>127.0.0.1</code> pada tahap ini.</li>
<li>Isi <strong>Port</strong> dengan <code>1883</code>.</li>
<li>Biarkan username dan password kosong hanya untuk lab sementara ini, lalu tekan Connect.</li>
<li>Buat subscription ke topic berikut, lalu biarkan MQTTX terbuka:</li>
</ol>
<pre><code>kodingindonesia/fsiot/esp32-meja-01/telemetry</code></pre>
<p><strong>Hasil yang dicari:</strong> MQTTX berstatus tersambung. Belum ada pesan JSON sampai ESP32 mengirim.</p>

<h2>Sketch ESP32 — kirim telemetry JSON tiap lima detik</h2>
HTML
            .$flow.<<<'HTML'
<p>Di Arduino IDE, buat sketch baru bernama <code>FS34_dht22_mqtt_json</code>. Ganti tiga bagian sebelum Upload: <code>GANTI_NAMA_WIFI</code>, <code>GANTI_SANDI_WIFI</code>, dan <code>192.168.1.23</code> dengan IPv4 PC kamu. Jangan menaruh sandi asli pada screenshot atau artikel.</p>
<pre><code class="language-arduino">
HTML
            .$sketch.<<<'HTML'
</code></pre>
<p><strong>Mengapa memakai <code>millis()</code>?</strong> Bagian publish menunggu lima detik tanpa <code>delay()</code> panjang. DHT22 dibaca lebih lambat daripada loop ESP32, sedangkan <code>mqttClient.poll()</code> tetap sering berjalan untuk menjaga koneksi MQTT.</p>
<p>Dasar Wi-Fi ESP32 memakai <code>WiFi.begin()</code> lalu mengecek <code>WL_CONNECTED</code>. Contoh resmi ArduinoMqttClient memakai <code>connect()</code>, <code>poll()</code>, <code>beginMessage()</code>, dan <code>endMessage()</code>. Rujukan: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/api/wifi.html" target="_blank" rel="noopener noreferrer">Espressif Wi-Fi API</a>, <a href="https://github.com/arduino-libraries/ArduinoMqttClient" target="_blank" rel="noopener noreferrer">ArduinoMqttClient</a>, <a href="https://arduinojson.org/v7/api/jsondocument/" target="_blank" rel="noopener noreferrer">ArduinoJson JsonDocument</a>, dan <a href="https://github.com/adafruit/DHT-sensor-library" target="_blank" rel="noopener noreferrer">Adafruit DHT sensor library</a>.</p>

<h2>Upload dan lihat hasilnya</h2>
<ol>
<li>Di Arduino IDE, klik <strong>Verify</strong>. Jika ada error library, buka kembali <strong>Library Manager</strong> melalui ikon buku dan periksa nama library-nya.</li>
<li>Klik <strong>Upload</strong>. Jangan mencabut USB sampai proses selesai.</li>
<li>Buka <strong>Tools → Serial Monitor</strong>, lalu pilih baud <strong>115200</strong>.</li>
<li>Cari tulisan <code>MQTT tersambung.</code> lalu <code>Terkirim:</code>.</li>
<li>Kembali ke MQTTX. Pesan JSON baru harus muncul setiap kira-kira lima detik.</li>
</ol>
<p><strong>Berhasil berarti:</strong> Serial Monitor dan MQTTX menampilkan nilai yang sejalan. Angka suhu dan kelembapan bisa berbeda di setiap ruangan; yang penting format JSON dan topic tepat. Pesan yang sama harus muncul di kedua tempat itu.</p>

<h2>Jika pesan belum muncul di MQTTX</h2>
HTML
            .$troubleshooting.<<<'HTML'
<ol>
<li><strong>ESP32 memakai <code>127.0.0.1</code> atau <code>localhost</code>.</strong> Ganti dengan IPv4 PC dari <code>ipconfig</code>, lalu Upload ulang.</li>
<li><strong>PC dan ESP32 berada di Wi-Fi berbeda.</strong> Pastikan keduanya memakai jaringan rumah yang sama, bukan guest Wi-Fi.</li>
<li><strong>Jendela Mosquitto tertutup atau alamat konfigurasi salah.</strong> Jalankan kembali broker dan cocokkan IPv4 pada file konfigurasi serta sketch.</li>
<li><strong>Windows menolak koneksi.</strong> Pastikan izin hanya untuk jaringan Private sudah diberikan. Jangan membuat port forwarding router.</li>
<li><strong>DHT22 tidak terbaca.</strong> Matikan daya, periksa VCC → 3V3, DATA → GPIO 4, GND → GND, lalu Upload ulang.</li>
<li><strong>MQTTX tidak melihat pesan.</strong> Host MQTTX harus IPv4 PC, port <code>1883</code>, dan topic harus sama persis dengan sketch.</li>
<li><strong>IP PC berubah setelah router atau reboot.</strong> Jalankan <code>ipconfig</code> lagi, ubah file konfigurasi dan <code>MQTT_HOST</code>, lalu jalankan broker serta Upload ulang.</li>
</ol>

<h2 id="fsiot-telemetry-checklist">Checklist sebelum FS-35</h2>
<p>Centang setelah kamu benar-benar melakukan setiap poin. Target: <strong>10/10</strong>. Progres disimpan di browser perangkatmu dan tidak dikirim ke server.</p>
<ul id="fsiot-telemetry-checklist-items">
<li>Library DHT sensor, ArduinoMqttClient, dan ArduinoJson sudah terpasang.</li>
<li>Wiring modul DHT22 tiga pin cocok: 3V3, GPIO 4, GND.</li>
<li>ESP32 dan PC memakai Wi-Fi rumah yang sama.</li>
<li>Saya menemukan IPv4 PC dengan <code>ipconfig</code>.</li>
<li>Konfigurasi Mosquitto memakai IPv4 PC, bukan alamat contoh.</li>
<li>Broker Mosquitto berjalan di PowerShell.</li>
<li>MQTTX memakai IPv4 PC dan port 1883.</li>
<li>Sketch berisi Wi-Fi dan <code>MQTT_HOST</code> milik saya.</li>
<li>Serial Monitor menampilkan pesan <code>Terkirim:</code>.</li>
<li>JSON telemetry terlihat di MQTTX.</li>
</ul>
<p><strong>Cara memeriksa kesiapan:</strong> jelaskan alur DHT22 → ESP32 → Mosquitto → MQTTX dengan kata-katamu sendiri. Setelah selesai, hentikan broker lab dengan <strong>Ctrl+C</strong>. Pada FS-35, MQTT akan membawa perintah dari PC ke relay.</p>

<h2>Kesalahan yang sering terjadi</h2>
<ul>
<li><strong>Host MQTTX masih <code>127.0.0.1</code>.</strong> Itu hanya untuk lab satu komputer di FS-33. Hari ini Host = IPv4 PC.</li>
<li><strong>Mengira dokumentasi Arduino wajib memakai UNO.</strong> UNO hanya contoh di halaman resmi. Lab ini memakai ESP32.</li>
<li><strong>Menebak urutan kaki DHT22.</strong> Baca label VCC, DATA atau DAT, dan GND.</li>
<li><strong>Mengunggah sketch sebelum library terpasang.</strong> Ikon buku dulu, baru kode.</li>
<li><strong>Menutup jendela PowerShell.</strong> Menutupnya biasanya mematikan broker.</li>
<li><strong>Memakai guest Wi-Fi atau membuka port router.</strong> Lab ini tidak memerlukannya.</li>
<li><strong>Menyalin broker publik dari internet.</strong> Kita memakai Mosquitto di PC sendiri.</li>
</ul>

<h2>Pertanyaan yang sering muncul</h2>
<h3>Kenapa ESP32 tidak boleh memakai 127.0.0.1?</h3>
<p>Alamat itu berarti “saya sendiri”. Di ESP32, yang dimaksud adalah ESP32, bukan PC tempat Mosquitto berjalan.</p>
<h3>Kenapa FS-33 bilang jangan menambah listener, sekarang ada?</h3>
<p>FS-33 hanya berbicara di dalam satu komputer. Hari ini ESP32 datang dari Wi-Fi rumah, jadi kita membuka pintu sementara yang terikat ke IPv4 PC, lalu menutupnya dengan Ctrl+C.</p>
<h3>Kenapa tidak boleh Wi-Fi tamu?</h3>
<p>Jaringan tamu sering memisahkan perangkat agar tidak saling berbicara. ESP32 tidak akan menemukan Mosquitto.</p>
<h3>Modul saya empat kaki, bukan tiga pin?</h3>
<p>Berhenti dulu. Cocokkan panduan pin khusus modul itu. Jangan menyalin urutan kaki dari foto modul lain.</p>
<h3>Windows menanyakan izin jaringan. Apa yang dipilih?</h3>
<p>Hanya <strong>Private networks</strong> di rumah tepercaya. Jangan Public. Jangan menambah aturan firewall baru di luar permintaan itu.</p>

<h2>Sumber</h2>
<ul>
<li><a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-installing-a-library/" target="_blank" rel="noopener noreferrer">Arduino — Installing libraries (IDE 2)</a>. Dokumentasi berlisensi Creative Commons Attribution-Share Alike 4.0. Arduino dan logo Arduino adalah merek Arduino S.r.l. Ilustrasi Library Manager buatan Koding Indonesia; screenshot jendela resmi tidak dipakai utuh karena gelap dan menampilkan UNO.</li>
<li><a href="https://commons.wikimedia.org/wiki/File:DHT_22_Sensor.jpg" target="_blank" rel="noopener noreferrer">AM2302 DHT22 Sensor</a> · L293D · Wikimedia Commons · Creative Commons Attribution-Share Alike 4.0. Foto hanya contoh rupa; jangan menyalin urutan kaki dari foto.</li>
<li><a href="https://mosquitto.org/man/mosquitto-conf-5.html" target="_blank" rel="noopener noreferrer">Manual konfigurasi Mosquitto (mosquitto.conf)</a></li>
<li><a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/api/wifi.html" target="_blank" rel="noopener noreferrer">Espressif — Wi-Fi API Arduino ESP32</a></li>
<li><a href="https://github.com/arduino-libraries/ArduinoMqttClient" target="_blank" rel="noopener noreferrer">ArduinoMqttClient</a></li>
<li><a href="https://arduinojson.org/v7/api/jsondocument/" target="_blank" rel="noopener noreferrer">ArduinoJson — JsonDocument</a></li>
<li><a href="https://github.com/adafruit/DHT-sensor-library" target="_blank" rel="noopener noreferrer">Adafruit DHT sensor library</a></li>
<li><a href="https://mqttx.app/downloads" target="_blank" rel="noopener noreferrer">Halaman unduhan MQTTX</a> · aplikasi oleh EMQ, Apache License 2.0</li>
<li>Diagram urutan tools, wiring, batas LAN, alur JSON, skema periksa, dan ilustrasi Library Manager — Koding Indonesia (FS-34)</li>
</ul>

<h2>Selanjutnya</h2>
<p><strong>Ringkasnya:</strong> ESP32 kini menjadi publisher telemetry. Pada <strong>FS-35</strong>, ESP32 akan subscribe topic <code>command</code> dan mengendalikan relay dengan aman.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $sketch = htmlspecialchars($this->sketch(), ENT_QUOTES, 'UTF-8');
        $tools = $this->figure('fs34-tools-order.png', 'Five-step tool order: browser, Arduino IDE, DHT22 wiring, PowerShell, then MQTTX', '<strong>Desk order (five steps):</strong> browser → Arduino IDE (book icon) → DHT22 wiring → PowerShell for IP and broker → MQTTX. Connect MQTTX only after <code>1883</code> is visible. Diagram by Koding Indonesia (FS-34).');
        $library = $this->figure('fs34-library-manager.png', 'Arduino IDE 2 Library Manager illustration: three-book icon, DHT sensor library search, INSTALL, ESP32 board', '<strong>This is the correct view, not an error screen.</strong> The three-book icon in the left bar, type <em>DHT sensor library</em>, then choose <strong>INSTALL</strong>. The board in the top-right is an <strong>ESP32</strong>, not an UNO. Illustration by Koding Indonesia (FS-34), modelled on Arduino IDE 2. The official window screenshot is not used as-is because it is dimmed and shows an UNO. Step reference: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-installing-a-library/" target="_blank" rel="noopener noreferrer">Installing libraries — Arduino IDE 2</a>, Arduino S.r.l. Arduino documentation is licensed under Creative Commons Attribution-Share Alike 4.0.');
        $wiring = $this->figure('fs34-wiring-dht22.png', 'Three-pin DHT22 module wiring to ESP32 by VCC, DATA, and GND labels', '<strong>Main figure — wiring.</strong> Match the printed labels: VCC → 3V3, DATA or DAT → GPIO 4, GND → GND. Physical pin order can differ between modules. Diagram by Koding Indonesia (FS-34).');
        $dhtPhoto = $this->figure('kit-dht22.jpg', 'Example DHT22 AM2302 module on a red board with three pins labelled DAT, VCC, and GND', '<strong>Appearance example only.</strong> This photo helps you recognise the sensor. <strong>Do not copy pin order from the photo.</strong> Wiring still follows the printed labels: VCC → 3V3, DATA or DAT → GPIO 4, GND → GND. In this photo the labels read DAT / VCC / GND from top to bottom — that belongs to this module, not to every module. Source: <a href="https://commons.wikimedia.org/wiki/File:DHT_22_Sensor.jpg" target="_blank" rel="noopener noreferrer">AM2302 DHT22 Sensor</a> · L293D · Wikimedia Commons · Creative Commons Attribution-Share Alike 4.0.');
        $lan = $this->figure('fs34-lan-address.png', 'ESP32 uses the PC LAN IPv4 address, not localhost or 127.0.0.1', '<strong>Important:</strong> <code>127.0.0.1</code> on ESP32 means ESP32 itself. Use the PC IPv4 from <code>ipconfig</code>. Diagram by Koding Indonesia (FS-34).');
        $flow = $this->figure('fs34-json-flow.png', 'Left-to-right flow: DHT22, ESP32, Mosquitto, then MQTTX showing JSON', '<strong>Main figure — data flow.</strong> Read left to right: DHT22 → ESP32 → Mosquitto → MQTTX. Diagram by Koding Indonesia (FS-34).');
        $troubleshooting = $this->figure('fs34-troubleshooting.png', 'Four checks when ESP32 telemetry has not appeared in MQTTX', '<strong>Helper schematic.</strong> Check wiring, Wi-Fi, broker and PC IP, then MQTTX. Diagram by Koding Indonesia (FS-34).');
        $install = $this->stepsCard([
            ['title' => 'Open a browser', 'text' => 'Use Chrome, Firefox, Edge, or Safari. Keep this guide and the official Arduino page ready. Do not Upload a sketch yet.'],
            ['title' => 'Open Arduino IDE', 'text' => 'Click the book icon in the left bar for Library Manager. Do not use the old <em>Tools → Manage Libraries</em> menu. Do not paste code yet.'],
            ['title' => 'Prepare the DHT22 wiring', 'text' => 'Unplug ESP32 USB first. Three-pin module: VCC → 3V3, DATA → GPIO 4, GND → GND. Read the labels; do not guess pin order.'],
            ['title' => 'Open PowerShell only for IP and the broker', 'text' => 'Press Start → type <strong>PowerShell</strong> → choose <strong>Windows PowerShell</strong>. Do not use <em>Run as administrator</em>. The commands are in the IPv4 and Mosquitto sections.'],
            ['title' => 'Open MQTTX after the broker is running', 'text' => 'Only now click <em>New Connection</em>. Set Host to the <strong>PC IPv4</strong> (not <code>127.0.0.1</code>) and Port to <code>1883</code>. If MQTTX is missing, install it from <a href="https://mqttx.app/downloads" target="_blank" rel="noopener noreferrer">mqttx.app/downloads</a> as in FS-32.'],
        ], '<strong>How to test today:</strong> success = the Mosquitto window stays open and shows <code>1883</code>, Serial Monitor prints <code>MQTT tersambung.</code> then <code>Terkirim:</code>, and MQTTX shows a new JSON message about every five seconds.');

        return <<<'HTML'
<h2>Introduction — the sensor now speaks to the broker</h2>
<p><strong>FS-34 / #104 (this article)</strong> connects DHT22, ESP32, and the local Mosquitto broker from FS-33. ESP32 reads temperature and humidity, packs them as JSON, then sends them to the broker. MQTTX on the PC shows the result.</p>
<p><strong>In short:</strong> ESP32 becomes the telemetry sender. Mosquitto stays the post office. MQTTX only reads the mail that arrived.</p>
<p><strong>Analogy:</strong> DHT22 is a thermometer. ESP32 writes those numbers on a JSON slip. Mosquitto forwards the slip. MQTTX reads it at the desk.</p>
<p>In FS-33 we connected to <code>127.0.0.1</code> because MQTTX and Mosquitto were on the same computer. Today ESP32 is a different device, so MQTTX Host and <code>MQTT_HOST</code> use the <strong>PC IPv4</strong>.</p>

<h2>Expected outcome</h2>
<ul>
<li>DHT22 is safely wired to ESP32.</li>
<li>ESP32 joins the same Wi-Fi as the PC.</li>
<li>MQTTX receives JSON such as <code>{"device_id":"esp32-meja-01","temperature_c":27.4,"humidity_pct":63.1}</code>.</li>
<li>The telemetry topic includes <code>device_id</code> from the start.</li>
</ul>
<p><strong>Today’s lab boundary:</strong> there is no relay, web dashboard, cloud account, or public broker. There is no router port forwarding.</p>

<h2>Terms used today</h2>
<ul>
<li><strong>Telemetry</strong> — data a device sends on a schedule; here temperature and humidity.</li>
<li><strong>JSON</strong> — a tidy name-and-value format, for example <code>"temperature_c":27.4</code>.</li>
<li><strong>DHT22</strong> — a temperature and humidity sensor. This lab uses a labelled three-pin module.</li>
<li><strong>Library Manager</strong> — the Arduino IDE 2 left-bar panel with the three-book icon. Not the old <em>Tools</em> menu.</li>
<li><strong>PC IPv4</strong> — the computer’s address on home Wi-Fi, for example <code>192.168.1.23</code>. It is not a fixed sample; yours will almost certainly differ.</li>
<li><strong><code>127.0.0.1</code> / <code>localhost</code></strong> — “this device itself”. On ESP32 that means ESP32, not the PC.</li>
<li><strong><code>listener</code></strong> — a config line that opens Mosquitto on a chosen address. FS-33 did not use one; today we add it <strong>temporarily</strong>, bound to the PC IPv4.</li>
<li><strong>Private network</strong> — Windows permission for trusted home Wi-Fi. Do not choose Public, and do not use guest Wi-Fi.</li>
<li><strong>Public broker</strong> — a broker on the internet owned by someone else. This lab does not use one.</li>
</ul>

<h2>Preparation — open the right tools first</h2>
HTML
            .$tools.$install.<<<'HTML'
<p><strong>Not used today:</strong> a public broker, router port forwarding, relay, cloud account, Laragon, <code>php artisan</code>, or a dashboard.</p>
<p><strong>Phone tip:</strong> if a diagram feels small, use the browser or screen zoom. You do not need to tap the image to fill the screen; nearby text should stay readable.</p>
<p><strong>If MQTTX is not installed yet:</strong> finish MQTTX Desktop from <a href="https://mqttx.app/downloads" target="_blank" rel="noopener noreferrer">mqttx.app/downloads</a> first, then return to step 5.</p>

<h2>Install Arduino IDE libraries</h2>
HTML
            .$library.<<<'HTML'
<p><strong>Open Arduino IDE first.</strong> In the left bar, open <strong>Library Manager</strong>: the three-book icon. If your interface provides the menu path, you can also use <strong>Sketch → Include Library → Manage Libraries</strong>. Install one by one:</p>
<ol>
<li><strong>DHT sensor library</strong> by Adafruit. If asked for <strong>Adafruit Unified Sensor</strong>, choose <strong>Install All</strong>; the DHT library needs that dependency.</li>
<li><strong>ArduinoMqttClient</strong> by Arduino. It supports MQTT sending and its official examples include ESP32.</li>
<li><strong>ArduinoJson</strong> by Benoit Blanchon. This lab uses <code>JsonDocument</code> and <code>serializeJson()</code>, so you do not assemble JSON by hand.</li>
</ol>
<p>Before Verify, select the correct ESP32 board and USB port from the board selector at the top of Arduino IDE. Official Arduino documentation sometimes shows an UNO only as an example; do not copy that board for this lab. If ESP32 is unavailable, finish installing its board package from the earlier module first.</p>
<p>Book-icon steps: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-installing-a-library/" target="_blank" rel="noopener noreferrer">Arduino IDE 2 installing-a-library documentation</a>.</p>

<h2>Wire DHT22</h2>
HTML
            .$wiring.$dhtPhoto.<<<'HTML'
<ol>
<li>Unplug ESP32 USB.</li>
<li>On a <strong>labelled three-pin DHT22 module</strong>, connect <strong>VCC → 3V3</strong>, <strong>DATA → GPIO 4</strong>, and <strong>GND → GND</strong>. Some modules print <strong>DAT</strong> for the data pin.</li>
<li>Reconnect the data USB cable.</li>
</ol>
<p><strong>Do not guess pins.</strong> A bare four-pin DHT22 and a three-pin module do not use the same physical layout. This article uses a labelled three-pin VCC / DATA or DAT / GND module. If yours differs, stop and match the printed labels.</p>

<h2>Find the computer IPv4 address</h2>
<p><strong>Open PowerShell first:</strong> press Start → type <strong>PowerShell</strong> → choose <strong>Windows PowerShell</strong>. Do not use <em>Run as administrator</em>.</p>
<p><strong>How to paste:</strong> copy the line below, click the PowerShell window, then press <kbd>Ctrl</kbd>+<kbd>V</kbd> or right-click. After the text appears, press Enter.</p>
<pre><code>ipconfig</code></pre>
<p>Find the Wi-Fi adapter <strong>IPv4 Address</strong>. This guide uses <code>192.168.1.23</code> only as an example. Write that number down. It is both the MQTTX Host and <code>MQTT_HOST</code>.</p>
<p><strong>macOS:</strong> open the <strong>Terminal</strong> app first, then run <code>ifconfig</code> and look for <code>inet</code> on Wi-Fi. <strong>Ubuntu or Debian:</strong> open <strong>Terminal</strong> first, then run <code>ip addr</code>.</p>

<h2>Run Mosquitto so ESP32 may join</h2>
HTML
            .$lan.<<<'HTML'
<p>FS-33 kept the broker at <code>127.0.0.1</code> for the PC only. ESP32 is a different device, so this short lab creates a LAN listener bound only to the PC home-network IPv4 address. It is a trusted-home lab, not an invitation to the internet.</p>
<ol>
<li>Open <strong>Notepad</strong>, paste the two lines below, and replace the sample address with your PC IPv4:</li>
</ol>
<pre><code>listener 1883 192.168.1.23
listener_allow_anonymous true</code></pre>
<ol start="2">
<li>Save as <code>mosquitto-fs34.conf</code> in <strong>Documents</strong>. Choose <em>Save as type: All files</em> so Notepad does not add <code>.txt</code>.</li>
<li>Return to PowerShell. <strong>How to paste:</strong> copy the line below, click the PowerShell window, then <kbd>Ctrl</kbd>+<kbd>V</kbd> or right-click, then Enter.</li>
</ol>
<pre><code>&amp; 'C:\Program Files\mosquitto\mosquitto.exe' -c "$env:USERPROFILE\Documents\mosquitto-fs34.conf" -v</code></pre>
<p><strong>Expected result:</strong> the window stays open and shows port <code>1883</code>. Keep it open. The <code>-v</code> option shows broker activity.</p>
<p>If Windows asks about network access, allow only <strong>Private networks</strong> for trusted home Wi-Fi, never Public networks. Do not open a router port or use guest Wi-Fi.</p>
<p><strong>Why anonymous access?</strong> These two lines are only for a short LAN lab so ESP32 and MQTTX can connect without an account. Mosquitto supports a listener bound to a specific IP and anonymous access per listener. Stop the broker with <strong>Ctrl+C</strong> when the exercise ends. Users, passwords, TLS, and stronger access come in FS-49. See the <a href="https://mosquitto.org/man/mosquitto-conf-5.html" target="_blank" rel="noopener noreferrer">Mosquitto configuration documentation</a>.</p>
<p><strong>macOS or Linux:</strong> open <strong>Terminal</strong> first, save the same config file, then run <code>mosquitto -c ~/Documents/mosquitto-fs34.conf -v</code> and keep the window open.</p>

<h2>Connect MQTTX to the PC IPv4</h2>
<p>Keep the Mosquitto window open with <code>1883</code> visible. Only now open <strong>MQTTX</strong>.</p>
<ol>
<li>Click <em>New Connection</em> and name it <code>FS34 telemetry LAN</code>.</li>
<li>Set <strong>Host</strong> to the same PC IPv4, for example <code>192.168.1.23</code>. Do not use <code>127.0.0.1</code> in this step.</li>
<li>Set <strong>Port</strong> to <code>1883</code>.</li>
<li>Leave username and password blank only for this temporary lab, then press Connect.</li>
<li>Subscribe to the topic below and leave MQTTX open:</li>
</ol>
<pre><code>kodingindonesia/fsiot/esp32-meja-01/telemetry</code></pre>
<p><strong>Expected result:</strong> MQTTX shows connected. JSON messages appear only after ESP32 publishes.</p>

<h2>ESP32 sketch — publish JSON every five seconds</h2>
HTML
            .$flow.<<<'HTML'
<p>Create <code>FS34_dht22_mqtt_json</code> in Arduino IDE. Replace <code>GANTI_NAMA_WIFI</code>, <code>GANTI_SANDI_WIFI</code>, and the example PC IPv4 address before Upload. Do not put a real password in a screenshot or article.</p>
<pre><code class="language-arduino">
HTML
            .$sketch.<<<'HTML'
</code></pre>
<p><strong>Why <code>millis()</code>?</strong> Publish waits five seconds without a long <code>delay()</code>. DHT22 is slower than the ESP32 loop, while <code>mqttClient.poll()</code> still runs often to keep MQTT alive.</p>
<p>ESP32 Wi-Fi uses <code>WiFi.begin()</code> then checks <code>WL_CONNECTED</code>. Official ArduinoMqttClient examples use <code>connect()</code>, <code>poll()</code>, <code>beginMessage()</code>, and <code>endMessage()</code>. References: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/api/wifi.html" target="_blank" rel="noopener noreferrer">Espressif Wi-Fi API</a>, <a href="https://github.com/arduino-libraries/ArduinoMqttClient" target="_blank" rel="noopener noreferrer">ArduinoMqttClient</a>, <a href="https://arduinojson.org/v7/api/jsondocument/" target="_blank" rel="noopener noreferrer">ArduinoJson JsonDocument</a>, and <a href="https://github.com/adafruit/DHT-sensor-library" target="_blank" rel="noopener noreferrer">Adafruit DHT sensor library</a>.</p>

<h2>Upload and see the result</h2>
<ol>
<li>Click <strong>Verify</strong>. If a library is missing, reopen <strong>Library Manager</strong> using the book icon and check its name.</li>
<li>Click <strong>Upload</strong> and keep USB connected until it finishes.</li>
<li>Open <strong>Tools → Serial Monitor</strong> at <strong>115200</strong> baud.</li>
<li>Look for <code>MQTT tersambung.</code> then <code>Terkirim:</code>.</li>
<li>Return to MQTTX; a JSON message should appear about every five seconds.</li>
</ol>
<p><strong>Success means:</strong> Serial Monitor and MQTTX show matching values. Temperature and humidity numbers can differ by room; the JSON shape and topic must be right. The same message should appear in both places.</p>

<h2>If MQTTX shows no message</h2>
HTML
            .$troubleshooting.<<<'HTML'
<ol>
<li><strong>ESP32 uses <code>127.0.0.1</code> or <code>localhost</code>.</strong> Replace it with PC IPv4 and Upload again.</li>
<li><strong>PC and ESP32 use different Wi-Fi.</strong> Use the same home network, not guest Wi-Fi.</li>
<li><strong>The broker window is closed or the IP is wrong.</strong> Restart Mosquitto and match the config IP to the sketch.</li>
<li><strong>Windows rejects the connection.</strong> Allow only Private network access; do not configure router forwarding.</li>
<li><strong>DHT22 cannot be read.</strong> Power off, check VCC → 3V3, DATA → GPIO 4, GND → GND, then Upload again.</li>
<li><strong>MQTTX watches the wrong place.</strong> Use PC IPv4, port <code>1883</code>, and exactly the sketch topic.</li>
<li><strong>The PC IPv4 changed.</strong> Run <code>ipconfig</code>, update the config and <code>MQTT_HOST</code>, restart the broker, and Upload again.</li>
</ol>

<h2 id="fsiot-telemetry-checklist">Checklist before FS-35</h2>
<p>Tick only after doing the step. Target: <strong>10/10</strong>. Progress stays in this browser and is not sent to the server.</p>
<ul id="fsiot-telemetry-checklist-items">
<li>DHT sensor, ArduinoMqttClient, and ArduinoJson are installed.</li>
<li>Three-pin DHT22 wiring matches 3V3, GPIO 4, GND.</li>
<li>ESP32 and PC use the same home Wi-Fi.</li>
<li>I found the PC IPv4 with <code>ipconfig</code>.</li>
<li>Mosquitto config uses my PC IPv4, not the example address.</li>
<li>Mosquitto is running in PowerShell.</li>
<li>MQTTX uses PC IPv4 and port 1883.</li>
<li>The sketch uses my Wi-Fi and <code>MQTT_HOST</code>.</li>
<li>Serial Monitor prints <code>Terkirim:</code>.</li>
<li>MQTTX shows telemetry JSON.</li>
</ul>
<p><strong>How to check readiness:</strong> explain DHT22 → ESP32 → Mosquitto → MQTTX in your own words. Stop the lab broker with <strong>Ctrl+C</strong>. FS-35 brings MQTT commands from PC to a relay.</p>

<h2>Common mistakes</h2>
<ul>
<li><strong>MQTTX Host is still <code>127.0.0.1</code>.</strong> That was only for the one-computer lab in FS-33. Today Host = the PC IPv4.</li>
<li><strong>Thinking Arduino documentation requires an UNO.</strong> UNO is only the official-page example. This lab uses ESP32.</li>
<li><strong>Guessing DHT22 pin order.</strong> Read the VCC, DATA or DAT, and GND labels.</li>
<li><strong>Uploading before libraries are installed.</strong> Use the book icon first, then the code.</li>
<li><strong>Closing the PowerShell window.</strong> Closing it usually stops the broker.</li>
<li><strong>Using guest Wi-Fi or opening a router port.</strong> This lab does not need that.</li>
<li><strong>Copying a public broker from the internet.</strong> We use Mosquitto on our own PC.</li>
</ul>

<h2>Frequently asked questions</h2>
<h3>Why must ESP32 not use 127.0.0.1?</h3>
<p>That address means “myself”. On ESP32 it means ESP32, not the PC running Mosquitto.</p>
<h3>Why did FS-33 say not to add a listener, and now there is one?</h3>
<p>FS-33 talked only inside one computer. Today ESP32 arrives from home Wi-Fi, so we open a temporary door bound to the PC IPv4, then close it with Ctrl+C.</p>
<h3>Why not guest Wi-Fi?</h3>
<p>Guest networks often isolate devices. ESP32 will not find Mosquitto.</p>
<h3>My module has four pins, not three?</h3>
<p>Stop first. Match that module’s own pin guide. Do not copy pin order from another module’s photo.</p>
<h3>Windows asks for network permission. What should I choose?</h3>
<p>Only <strong>Private networks</strong> on trusted home Wi-Fi. Not Public. Do not add extra firewall rules beyond that prompt.</p>

<h2>Sources</h2>
<ul>
<li><a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-installing-a-library/" target="_blank" rel="noopener noreferrer">Arduino — Installing libraries (IDE 2)</a>. Documentation licensed under Creative Commons Attribution-Share Alike 4.0. Arduino and the Arduino logo are trademarks of Arduino S.r.l. The Library Manager illustration is by Koding Indonesia; the official window screenshot is not used as-is because it is dimmed and shows an UNO.</li>
<li><a href="https://commons.wikimedia.org/wiki/File:DHT_22_Sensor.jpg" target="_blank" rel="noopener noreferrer">AM2302 DHT22 Sensor</a> · L293D · Wikimedia Commons · Creative Commons Attribution-Share Alike 4.0. The photo is an appearance example only; do not copy pin order from the photo.</li>
<li><a href="https://mosquitto.org/man/mosquitto-conf-5.html" target="_blank" rel="noopener noreferrer">Mosquitto configuration manual (mosquitto.conf)</a></li>
<li><a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/api/wifi.html" target="_blank" rel="noopener noreferrer">Espressif — Arduino ESP32 Wi-Fi API</a></li>
<li><a href="https://github.com/arduino-libraries/ArduinoMqttClient" target="_blank" rel="noopener noreferrer">ArduinoMqttClient</a></li>
<li><a href="https://arduinojson.org/v7/api/jsondocument/" target="_blank" rel="noopener noreferrer">ArduinoJson — JsonDocument</a></li>
<li><a href="https://github.com/adafruit/DHT-sensor-library" target="_blank" rel="noopener noreferrer">Adafruit DHT sensor library</a></li>
<li><a href="https://mqttx.app/downloads" target="_blank" rel="noopener noreferrer">MQTTX downloads</a> · app by EMQ, Apache License 2.0</li>
<li>Tool-order, wiring, LAN-boundary, JSON-flow, troubleshooting, and Library Manager diagrams — Koding Indonesia (FS-34)</li>
</ul>

<h2>Next</h2>
<p><strong>In short:</strong> ESP32 is now a telemetry publisher. In <strong>FS-35</strong>, ESP32 subscribes to a <code>command</code> topic and controls a relay safely.</p>
HTML;
    }
}
