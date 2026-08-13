<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class Article105Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@kodingindonesia.com')->first() ?? User::first();
        $category = Category::where('slug', 'iot-smart-device')->first() ?? Category::where('slug', 'esp32-arduino')->first();
        if (! $admin || ! $category) {
            throw new \RuntimeException('User atau kategori IoT tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-esp32-mqtt-command-relay';
        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        foreach (['fullstack-iot', 'iot', 'mqtt', 'esp32', 'relay', 'json'] as $tagSlug) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagSlug]);
        }

        $article = Article::updateOrCreate(['slug' => $slug], [
            'user_id' => $admin->id,
            'category_id' => $category->id,
            'title' => 'ESP32 terima perintah MQTT untuk mengendalikan relay',
            'title_en' => 'ESP32 receives MQTT commands to control a relay',
            'excerpt' => 'FS-35 / #105: kirim JSON on/off dari MQTTX ke Mosquitto lokal, ESP32 subscribe topic command, lalu relay GPIO 26 berklik. Bukan AC 220V.',
            'excerpt_en' => 'FS-35 / #105: send on/off JSON from MQTTX to local Mosquitto, ESP32 subscribes to the command topic, then the GPIO 26 relay clicks. Not AC mains.',
            'body' => $this->body(),
            'body_en' => $this->bodyEn(),
            'status' => 'draft',
            'is_featured' => false,
            'published_at' => null,
            'seo_title' => 'ESP32 Terima Perintah MQTT untuk Relay — FS-35',
            'seo_title_en' => 'ESP32 Receives MQTT Commands for a Relay — FS-35',
            'seo_description' => 'Panduan pemula mengendalikan relay ESP32 dari MQTTX lewat Mosquitto lokal: topic command, status JSON, GPIO 26, tanpa AC 220V.',
            'seo_description_en' => 'Beginner guide to control an ESP32 relay from MQTTX via local Mosquitto: command topic, JSON status, GPIO 26, no AC mains.',
        ]);
        $article->tags()->sync(Tag::whereIn('slug', ['fullstack-iot', 'iot', 'mqtt', 'esp32', 'relay', 'json'])->pluck('id'));

        foreach (['webp', 'jpg'] as $extension) {
            $source = public_path("images/fsiot/fs35-cover-command.{$extension}");
            if (is_file($source)) {
                $destination = "articles/covers/fs35-cover-command.{$extension}";
                Storage::disk('public')->put($destination, file_get_contents($source));
            }
        }
        $article->update([
            'cover_image' => 'https://kodingindonesia.com/images/fsiot/fs35-cover-command.webp',
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
            '',
            'const char WIFI_SSID[] = "GANTI_NAMA_WIFI";',
            'const char WIFI_PASSWORD[] = "GANTI_SANDI_WIFI";',
            'const char MQTT_HOST[] = "192.168.1.23";  // Ganti dengan IPv4 PC dari ipconfig',
            'const int MQTT_PORT = 1883;',
            'const char DEVICE_ID[] = "esp32-meja-01";',
            'const char TOPIC_COMMAND[] = "kodingindonesia/fsiot/esp32-meja-01/command";',
            'const char TOPIC_STATUS[] = "kodingindonesia/fsiot/esp32-meja-01/status";',
            '',
            'const byte RELAY_PIN = 26;',
            'const bool AKTIF_LOW = true;  // Pola kit FS-23. Ubah ke false jika modulmu aktif HIGH.',
            '',
            'WiFiClient wifiClient;',
            'MqttClient mqttClient(wifiClient);',
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
            '    Serial.println("Field relay belum ada.");',
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
            '  mqttClient.onMessage(onMqttMessage);',
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
            '}',
        ]);
    }

    private function body(): string
    {
        $sketch = htmlspecialchars($this->sketch(), ENT_QUOTES, 'UTF-8');
        $tools = $this->figure('fs35-tools-order.png', 'Urutan lima langkah: browser, Arduino IDE, kabel relay, PowerShell, lalu MQTTX', '<strong>Urutan meja kerja (lima langkah):</strong> browser → Arduino IDE (ikon buku) → kabel relay → PowerShell untuk IP dan broker → MQTTX. Connect MQTTX hanya setelah angka <code>1883</code> terlihat. Diagram buatan Koding Indonesia (FS-35).');
        $library = $this->figure('fs35-library-manager.png', 'Ilustrasi Library Manager Arduino IDE 2: ikon tiga buku, pencarian ArduinoMqttClient, tombol INSTALL, papan ESP32', '<strong>Ini tampilan yang benar, bukan layar error.</strong> Ikon tiga buku di bilah kiri. Cari <em>ArduinoMqttClient</em>, lalu <em>ArduinoJson</em>. Papan di pojok kanan adalah <strong>ESP32</strong>, bukan UNO. Jika FS-34 sudah selesai, library biasanya sudah ada. Ilustrasi buatan Koding Indonesia (FS-35), meniru Arduino IDE 2. Screenshot jendela resmi tidak dipakai utuh karena gelap dan menampilkan papan UNO. Acuan langkah: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-installing-a-library/" target="_blank" rel="noopener noreferrer">Installing libraries — Arduino IDE 2</a>, Arduino S.r.l. Dokumentasi Arduino berlisensi Creative Commons Attribution-Share Alike 4.0.');
        $wiring = $this->figure('fs35-wiring-relay.png', 'Wiring modul relay 5V ke ESP32 menurut label VCC, IN, dan GND', '<strong>Gambar utama — wiring.</strong> Cocokkan tulisan pin: VCC atau + → 5V, IN atau S → GPIO 26, GND atau − → GND. Urutan kaki fisik bisa berbeda antarmodul. Terminal NC/COM/NO hari ini kosong. Diagram buatan Koding Indonesia (FS-35).');
        $relayPhoto = $this->figure('kit-relay-5v.jpg', 'Contoh rupa modul relay 1 channel 5V Songle dengan terminal sekrup dan tiga pin S plus minus', '<strong>Contoh rupa modul saja.</strong> Foto ini membantu mengenali relay kit. <strong>Jangan menyalin urutan kaki dari foto.</strong> Wiring tetap menurut tulisan pin: VCC/+ → 5V, IN/S → GPIO 26, GND/− → GND. Terminal sekrup kiri adalah jalur beban NC/COM/NO — <strong>bukan</strong> pin ke ESP32, dan <strong>bukan AC 220V</strong>. Sumber: <a href="https://commons.wikimedia.org/wiki/File:SRD-05VDC-SL-C_5V_one-channel_relay_module.jpg" target="_blank" rel="noopener noreferrer">SRD-05VDC-SL-C 5V one-channel relay module</a> · Suyash Dwivedi · Wikimedia Commons · Creative Commons Attribution-Share Alike 4.0.');
        $lan = $this->figure('fs35-lan-address.png', 'ESP32 memakai alamat IPv4 LAN PC, bukan localhost atau 127.0.0.1', '<strong>Aturan penting:</strong> <code>127.0.0.1</code> pada ESP32 berarti ESP32 itu sendiri. Gunakan IPv4 PC dari <code>ipconfig</code>. Diagram buatan Koding Indonesia (FS-35).');
        $flow = $this->figure('fs35-command-flow.png', 'Alur kiri ke kanan: MQTTX, Mosquitto, ESP32, lalu relay; status JSON kembali lewat Mosquitto', '<strong>Gambar utama — alur perintah.</strong> Baca dari kiri ke kanan: MQTTX → Mosquitto → ESP32 → relay. Status JSON kembali lewat Mosquitto ke MQTTX pada topic <code>status</code>. Diagram buatan Koding Indonesia (FS-35).');
        $mqttx = $this->figure('fs35-mqttx-publish.png', 'Ilustrasi MQTTX tersambung ke IPv4 PC, subscribe status, dan publish JSON relay on', '<strong>Ini tampilan yang benar, bukan layar error.</strong> Tulisan MQTTX di kiri bukan tombol silang. Host = IPv4 PC, Port = <code>1883</code>. Subscribe topic <code>status</code>, lalu publish JSON ke topic <code>command</code>. Jangan salin Host dari screenshot internet. Ilustrasi buatan Koding Indonesia (FS-35), meniru tata letak aplikasi resmi <a href="https://mqttx.app/" target="_blank" rel="noopener noreferrer">MQTTX oleh EMQ</a> (Apache License 2.0). Screenshot jendela resmi tidak dipakai utuh karena menampilkan broker publik. Angka <code>192.168.1.23</code> hanya contoh.');
        $troubleshooting = $this->figure('fs35-troubleshooting.png', 'Empat pemeriksaan jika relay ESP32 belum berklik setelah perintah MQTT', '<strong>Skema bantu.</strong> Periksa kabel relay, Wi-Fi, broker serta IP PC, lalu topic dan JSON di MQTTX. Diagram buatan Koding Indonesia (FS-35).');
        $install = $this->stepsCard([
            ['title' => 'Buka browser', 'text' => 'Pakai Chrome, Firefox, Edge, atau Safari. Siapkan artikel ini. Jangan Upload sketch dulu.'],
            ['title' => 'Buka Arduino IDE', 'text' => 'Library ArduinoMqttClient dan ArduinoJson biasanya sudah ada dari FS-34. Jika belum, klik ikon tiga buku (Library Manager). Jangan memakai menu lama <em>Tools → Manage Libraries</em>.'],
            ['title' => 'Siapkan kabel relay', 'text' => 'Cabut USB ESP32 dulu. Modul 5V: VCC/+ → 5V, IN/S → GPIO 26, GND/− → GND. Baca tulisan pin. Jangan colok AC 220V.'],
            ['title' => 'Buka PowerShell hanya untuk IP dan broker', 'text' => 'Tekan Start → ketik <strong>PowerShell</strong> → pilih <strong>Windows PowerShell</strong>. Tidak perlu <em>Run as administrator</em>. Perintahnya ada di bagian IPv4 dan Mosquitto.'],
            ['title' => 'Buka MQTTX setelah broker berjalan', 'text' => 'Baru sekarang klik <em>New Connection</em>. Isi Host dengan <strong>IPv4 PC</strong> (bukan <code>127.0.0.1</code>) dan Port <code>1883</code>. Subscribe topic status dulu, baru publish perintah.'],
        ], '<strong>Cara menguji hari ini:</strong> bukti sukses = jendela Mosquitto tetap terbuka dan terlihat <code>1883</code>, Serial Monitor menulis <code>MQTT tersambung.</code> lalu <code>Subscribe command siap.</code>, relay berklik, dan MQTTX menampilkan JSON status.');

        return <<<'HTML'
<h2>Pendahuluan — perintah turun ke relay</h2>
<p><strong>FS-35 / #105 (ini)</strong> membalik arah FS-34. Kemarin ESP32 mengirim telemetry. Hari ini MQTTX mengirim perintah, ESP32 mendengarkan, lalu relay di GPIO 26 berklik.</p>
<p><strong>Intinya:</strong> ESP32 menjadi pendengar perintah. Mosquitto tetap menjadi kantor pos. MQTTX menulis surat <code>on</code> atau <code>off</code>.</p>
<p><strong>Analogi:</strong> FS-34 adalah termometer yang mengirim angka. FS-35 adalah sakelar jarak jauh di meja belajar. Kamu menekan Publish di PC, relay di papan berbunyi klik.</p>
<p>Prasyarat lab: Mosquitto lokal dan MQTTX dari FS-33/FS-34, plus pola relay GPIO 26 dari FS-23. DHT22 <strong>tidak</strong> dipakai hari ini agar meja kerja lebih sederhana.</p>

<h2>Hasil yang dituju</h2>
<ul>
<li>Relay 5V terpasang ke ESP32 dengan aman, tanpa AC 220V.</li>
<li>MQTTX mengirim JSON (publish) ke topic <code>command</code>.</li>
<li>Relay berklik dan LED indikator berubah.</li>
<li>MQTTX menerima JSON status di topic <code>status</code>.</li>
</ul>
<p><strong>Batas lab hari ini:</strong> tidak ada dashboard web, akun cloud, broker publik, atau lampu PLN. Bukti cukup = klik + LED + JSON status.</p>

<h2>Istilah yang dipakai hari ini</h2>
<ul>
<li><strong>Command</strong> — perintah yang dikirim ke perangkat, di sini <code>on</code> atau <code>off</code>.</li>
<li><strong>Subscribe</strong> — ESP32 mendaftar agar broker meneruskan pesan topic tertentu.</li>
<li><strong>Status</strong> — laporan balik setelah perintah dijalankan.</li>
<li><strong>Relay</strong> — sakelar elektromagnet. Hari ini kita hanya memakai klik dan LED modul.</li>
<li><strong>Library Manager</strong> — panel di bilah kiri Arduino IDE 2, ikon tiga buku. Bukan menu <em>Tools</em> lama.</li>
<li><strong>Aktif LOW</strong> — banyak modul kit menyalakan relay saat pin GPIO bernilai LOW. Itu pola FS-23.</li>
<li><strong>NC / COM / NO</strong> — terminal sekrup untuk beban. Bukan pin ke ESP32. Hari ini dikosongkan.</li>
<li><strong>IPv4 PC</strong> — alamat komputer di Wi-Fi rumah, misalnya <code>192.168.1.23</code>. Punyamu hampir pasti berbeda.</li>
<li><strong><code>127.0.0.1</code> / <code>localhost</code></strong> — “perangkat ini sendiri”. Di ESP32, itu berarti ESP32, bukan PC.</li>
<li><strong>Broker publik</strong> — broker di internet milik pihak lain. Latihan ini tidak memakainya.</li>
<li><strong>Guest Wi-Fi</strong> — jaringan tamu yang memisahkan perangkat. Jangan dipakai pada lab ini.</li>
</ul>

<h2>Persiapan — buka tool yang benar dulu</h2>
HTML
            .$tools.$install.<<<'HTML'
<p><strong>Jangan dipakai hari ini:</strong> AC 220V, port forwarding/router, broker publik, DHT22, Laragon, <code>php artisan</code>, atau dashboard web.</p>
<p><strong>Tips ponsel:</strong> jika diagram terasa kecil, gunakan fitur perbesar pada browser atau layar. Gambar tidak perlu diketuk sampai memenuhi layar agar teks di sekitarnya tetap terbaca.</p>

<h2>Pastikan library Arduino IDE</h2>
HTML
            .$library.<<<'HTML'
<p><strong>Buka dulu Arduino IDE.</strong> Di bilah kiri, klik <strong>Library Manager</strong>: ikon tiga buku. Itu satu-satunya jalur yang dipakai hari ini. Jangan memakai menu lama <em>Tools → Manage Libraries</em>.</p>
<p>Jika FS-34 sudah selesai, <strong>ArduinoMqttClient</strong> dan <strong>ArduinoJson</strong> biasanya sudah terpasang. Jika Verify nanti mengeluh library hilang, pasang lagi satu per satu lewat ikon buku, papan ESP32, bukan UNO. Menu <em>Tools → Serial Monitor</em> tetap dipakai nanti untuk melihat tulisan Serial; itu <strong>bukan</strong> Library Manager.</p>
<p>Rujukan: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-installing-a-library/" target="_blank" rel="noopener noreferrer">dokumentasi memasang library Arduino IDE 2</a>, Arduino S.r.l. Dokumentasi Arduino berlisensi Creative Commons Attribution-Share Alike 4.0.</p>

<h2>Pasang kabel relay</h2>
HTML
            .$wiring.$relayPhoto.<<<'HTML'
<ol>
<li>Cabut USB ESP32.</li>
<li>Pada <strong>modul relay 5V</strong>, sambungkan <strong>VCC atau + → 5V</strong>, <strong>IN atau S → GPIO 26</strong>, dan <strong>GND atau − → GND</strong>.</li>
<li>Biarkan terminal sekrup <strong>NC / COM / NO</strong> kosong.</li>
<li>Pasang kembali USB data ke ESP32.</li>
</ol>
<p><strong>Jangan menebak pin.</strong> Label modul bisa tertulis S / + / − atau IN / VCC / GND. Baca tulisan itu. <strong>Jangan colok AC 220V.</strong> Pola pin GPIO 26 mengikuti tabel kit FS-23.</p>

<h2>Cari alamat IPv4 komputer</h2>
<p><strong>Buka dulu PowerShell:</strong> tekan Start → ketik <strong>PowerShell</strong> → pilih <strong>Windows PowerShell</strong>. Tidak perlu <em>Run as administrator</em>.</p>
<p><strong>Cara menempel perintah:</strong> salin baris di bawah, klik jendela PowerShell, lalu tekan <kbd>Ctrl</kbd>+<kbd>V</kbd> atau klik kanan. Setelah teks muncul, tekan Enter.</p>
<pre><code>ipconfig</code></pre>
<p>Cari <strong>IPv4 Address</strong> pada adaptor Wi-Fi yang sedang dipakai. Contoh artikel memakai <code>192.168.1.23</code>; punyamu hampir pasti berbeda. Catat angka itu. Itulah Host MQTTX dan <code>MQTT_HOST</code>.</p>
<p><strong>macOS:</strong> buka aplikasi <strong>Terminal</strong> dulu, lalu jalankan <code>ifconfig</code> dan cari <code>inet</code> pada Wi-Fi. <strong>Ubuntu atau Debian:</strong> buka <strong>Terminal</strong> dulu, lalu jalankan <code>ip addr</code>.</p>

<h2>Jalankan Mosquitto agar ESP32 boleh masuk</h2>
HTML
            .$lan.<<<'HTML'
<p>Sama seperti FS-34: ESP32 adalah perangkat lain, jadi kita membuat <strong>akses LAN sementara</strong> yang hanya terikat ke IPv4 PC rumah. FS-33 tidak memakai <code>listener</code> karena MQTTX dan broker berada di komputer yang sama.</p>
<ol>
<li>Buka <strong>Notepad</strong>, tempel dua baris berikut, dan ganti alamat contoh dengan IPv4 PC milikmu:</li>
</ol>
<pre><code>listener 1883 192.168.1.23
listener_allow_anonymous true</code></pre>
<ol start="2">
<li>Simpan sebagai <code>mosquitto-fs35.conf</code> di folder <strong>Documents</strong>. Pilih <em>Save as type: All files</em> agar Notepad tidak menambahkan akhiran <code>.txt</code>.</li>
<li>Kembali ke PowerShell. <strong>Cara menempel perintah:</strong> salin baris di bawah, klik jendela PowerShell, lalu <kbd>Ctrl</kbd>+<kbd>V</kbd> atau klik kanan, kemudian Enter.</li>
</ol>
<pre><code>&amp; 'C:\Program Files\mosquitto\mosquitto.exe' -c "$env:USERPROFILE\Documents\mosquitto-fs35.conf" -v</code></pre>
<p><strong>Hasil yang dicari:</strong> jendela tetap terbuka dan terlihat angka <code>1883</code>. Biarkan jendela ini terbuka selama praktik.</p>
<p>Jika Windows meminta izin jaringan, pilih hanya <strong>Private networks</strong> pada Wi-Fi rumah tepercaya; jangan mengizinkan jaringan Public. Jangan membuka port router atau memakai Wi-Fi tamu (guest Wi-Fi).</p>
<p><strong>Mengapa ada akses anonim?</strong> Ini lab LAN singkat. Siapa saja di Wi-Fi rumah yang sama dapat mengirim perintah. Hentikan broker dengan <strong>Ctrl+C</strong> setelah selesai. Pengguna, sandi, dan TLS dibahas pada FS-49. Rujukan: <a href="https://mosquitto.org/man/mosquitto-conf-5.html" target="_blank" rel="noopener noreferrer">dokumentasi konfigurasi Mosquitto</a>.</p>
<p><strong>macOS atau Linux:</strong> buka <strong>Terminal</strong> dulu, simpan berkas konfigurasi yang sama, lalu jalankan <code>mosquitto -c ~/Documents/mosquitto-fs35.conf -v</code> dan biarkan jendela terbuka.</p>

<h2>Hubungkan MQTTX, subscribe status, siapkan perintah</h2>
HTML
            .$mqttx.<<<'HTML'
<p>Pastikan jendela Mosquitto masih terbuka dan terlihat <code>1883</code>. Baru sekarang buka <strong>MQTTX</strong>.</p>
<ol>
<li>Klik <em>New Connection</em> dan beri nama <code>FS35 perintah LAN</code>.</li>
<li>Isi <strong>Host</strong> dengan IPv4 PC, misalnya <code>192.168.1.23</code>. Jangan gunakan <code>127.0.0.1</code>.</li>
<li>Isi <strong>Port</strong> dengan <code>1883</code>, lalu Connect.</li>
<li>Buat subscription ke topic status berikut, lalu biarkan MQTTX terbuka:</li>
</ol>
<pre><code>kodingindonesia/fsiot/esp32-meja-01/status</code></pre>
<p>Siapkan juga topic publish:</p>
<pre><code>kodingindonesia/fsiot/esp32-meja-01/command</code></pre>
<p>Isi pesan JSON persis seperti ini, huruf kecil:</p>
<pre><code>{"device_id":"esp32-meja-01","relay":"on"}</code></pre>
<p>Untuk mematikan, ganti <code>"relay":"off"</code>. <strong>Belum</strong> tekan Publish sampai ESP32 sudah Upload dan Serial menulis <code>Subscribe command siap.</code></p>

<h2>Sketch ESP32 — subscribe command</h2>
HTML
            .$flow.<<<'HTML'
<p>Di Arduino IDE, buat sketch baru bernama <code>FS35_mqtt_relay_command</code>. Ganti <code>GANTI_NAMA_WIFI</code>, <code>GANTI_SANDI_WIFI</code>, dan <code>192.168.1.23</code> sebelum Upload. Jangan menaruh sandi asli pada screenshot.</p>
<p><strong>Mengapa subscribe diulang setelah MQTT tersambung?</strong> Jika Wi-Fi atau broker sempat putus, ESP32 harus daftar ulang ke topic <code>command</code>. Tanpa itu, Publish di MQTTX tidak sampai.</p>
<pre><code class="language-arduino">
HTML
            .$sketch.<<<'HTML'
</code></pre>
<p>Dasar MQTT subscribe mengikuti pola resmi ArduinoMqttClient: <code>onMessage()</code>, <code>connect()</code>, <code>subscribe()</code>, lalu <code>poll()</code> di loop. JSON memakai <code>JsonDocument</code> dan <code>serializeJson()</code>. Rujukan: <a href="https://github.com/arduino-libraries/ArduinoMqttClient" target="_blank" rel="noopener noreferrer">ArduinoMqttClient</a>, <a href="https://arduinojson.org/v7/api/jsondocument/" target="_blank" rel="noopener noreferrer">ArduinoJson JsonDocument</a>, dan <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/api/wifi.html" target="_blank" rel="noopener noreferrer">Espressif Wi-Fi API</a>.</p>

<h2>Upload dan uji perintah</h2>
<ol>
<li>Pilih board ESP32 dan port USB, lalu <strong>Verify</strong> dan <strong>Upload</strong>.</li>
<li>Buka <strong>Tools → Serial Monitor</strong> pada baud <strong>115200</strong>.</li>
<li>Cari tulisan <code>MQTT tersambung.</code> lalu <code>Subscribe command siap.</code></li>
<li>Kembali ke MQTTX. Pastikan subscription status aktif. Publish JSON <code>on</code> ke topic command.</li>
<li>Dengar klik relay dan lihat LED. MQTTX harus menampilkan JSON status. Lalu publish <code>off</code>.</li>
</ol>
<p><strong>Berhasil berarti:</strong> Serial menulis <code>Relay ON</code> atau <code>Relay OFF</code>, modul berklik, dan topic status menampilkan <code>"ok":true</code>. Pesan yang sama harus muncul di Serial dan MQTTX.</p>

<h2>Jika relay belum klik</h2>
HTML
            .$troubleshooting.<<<'HTML'
<ol>
<li><strong>ESP32 memakai <code>127.0.0.1</code>.</strong> Ganti dengan IPv4 PC dari <code>ipconfig</code>, lalu Upload ulang.</li>
<li><strong>Topic salah.</strong> Publish ke <code>command</code>, subscribe ke <code>status</code>. Keduanya harus sama persis dengan sketch.</li>
<li><strong>JSON berbeda kapital atau tanpa <code>device_id</code>.</strong> Salin contoh huruf kecil di artikel ini.</li>
<li><strong>Subscribe hilang setelah putus.</strong> Sketch sudah mengulang subscribe setelah MQTT tersambung. Jangan tutup jendela Mosquitto.</li>
<li><strong>Modul aktif HIGH, sketch masih aktif LOW.</strong> Jika LED terbalik, ubah <code>AKTIF_LOW</code> menjadi <code>false</code>, lalu Upload ulang. Itu pola FS-23.</li>
<li><strong>Salah pin.</strong> IN/S harus GPIO 26, VCC ke 5V, GND bersama. Jangan menebak dari foto.</li>
<li><strong>PC dan ESP32 berada di Wi-Fi berbeda.</strong> Pastikan keduanya memakai jaringan rumah yang sama, bukan guest Wi-Fi.</li>
</ol>

<h2 id="fsiot-command-checklist">Checklist sebelum FS-36</h2>
<p>Centang setelah kamu benar-benar melakukan setiap poin. Target: <strong>10/10</strong>. Progres disimpan di browser perangkatmu dan tidak dikirim ke server.</p>
<ul id="fsiot-command-checklist-items">
<li>ArduinoMqttClient dan ArduinoJson sudah terpasang.</li>
<li>Wiring relay 5V, GPIO 26, GND cocok menurut label pin.</li>
<li>Terminal NC/COM/NO kosong dan tidak ada AC 220V.</li>
<li>ESP32 dan PC memakai Wi-Fi rumah yang sama.</li>
<li>Saya menemukan IPv4 PC dengan <code>ipconfig</code>.</li>
<li>Broker Mosquitto berjalan di PowerShell dan terlihat 1883.</li>
<li>MQTTX memakai IPv4 PC, port 1883, dan subscribe topic status.</li>
<li>Sketch berisi Wi-Fi dan <code>MQTT_HOST</code> milik saya.</li>
<li>Serial Monitor menampilkan <code>Subscribe command siap.</code></li>
<li>Publish <code>on</code> lalu <code>off</code> membuat relay berklik dan JSON status muncul.</li>
</ul>
<p><strong>Cara memeriksa kesiapan:</strong> jelaskan alur MQTTX → Mosquitto → ESP32 → relay dengan kata-katamu sendiri. Setelah selesai, hentikan broker lab dengan <strong>Ctrl+C</strong>. Pada FS-36, data akan disimpan ke kartu microSD.</p>

<h2>Kesalahan yang sering terjadi</h2>
<ul>
<li><strong>Host MQTTX masih <code>127.0.0.1</code>.</strong> Hari ini Host = IPv4 PC, seperti FS-34.</li>
<li><strong>Publish ke topic status.</strong> Perintah masuk lewat <code>command</code>. Status hanya laporan balik.</li>
<li><strong>Menulis ON dengan huruf besar saja.</strong> Sketch menerima <code>on</code>/<code>off</code> setelah dijadikan huruf kecil, tetapi <code>device_id</code> harus persis.</li>
<li><strong>Colok lampu PLN ke NC/COM/NO.</strong> Lab ini tidak memerlukannya.</li>
<li><strong>Memakai guest Wi-Fi atau membuka port router.</strong> Lab ini tidak memerlukannya.</li>
<li><strong>Lupa subscribe ulang setelah broker restart.</strong> Upload ulang atau biarkan sketch menyambung lagi sampai Serial menulis <code>Subscribe command siap.</code></li>
</ul>

<h2>Pertanyaan yang sering muncul</h2>
<h3>Kenapa ESP32 tidak boleh memakai 127.0.0.1?</h3>
<p>Alamat itu berarti “saya sendiri”. Di ESP32, yang dimaksud adalah ESP32, bukan PC tempat Mosquitto berjalan.</p>
<h3>Kenapa siapa saja di Wi-Fi rumah bisa mengirim perintah?</h3>
<p>Listener lab ini anonim dan hanya untuk latihan singkat. Jangan biarkan broker hidup semalaman. Pengguna dan sandi datang di FS-49.</p>
<h3>Modul saya aktif HIGH. Apa yang diubah?</h3>
<p>Ubah <code>AKTIF_LOW</code> menjadi <code>false</code>, lalu Verify dan Upload. Jangan menebak pin.</p>
<h3>Kenapa tidak boleh Wi-Fi tamu?</h3>
<p>Jaringan tamu (guest Wi-Fi) sering memisahkan perangkat. ESP32 tidak akan menemukan Mosquitto di PC.</p>
<h3>Bolehkah menyambung lampu 220V?</h3>
<p>Tidak pada lab ini. Bukti sukses adalah klik dan LED indikator. AC PLN dibahas jauh kemudian, dengan pengaman terpisah.</p>
<h3>Kenapa DHT22 tidak dipakai?</h3>
<p>FS-34 sudah mengirim suhu. Hari ini satu tugas: perintah turun ke sakelar. Meja kerja lebih rapi.</p>

<h2>Sumber</h2>
<ul>
<li><a href="https://github.com/arduino-libraries/ArduinoMqttClient" target="_blank" rel="noopener noreferrer">ArduinoMqttClient</a> — pola <code>onMessage</code>, <code>subscribe</code>, dan <code>poll</code></li>
<li><a href="https://arduinojson.org/v7/api/jsondocument/" target="_blank" rel="noopener noreferrer">ArduinoJson — JsonDocument</a></li>
<li><a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/api/wifi.html" target="_blank" rel="noopener noreferrer">Espressif — Wi-Fi API Arduino ESP32</a></li>
<li><a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-installing-a-library/" target="_blank" rel="noopener noreferrer">Arduino — Installing libraries (IDE 2)</a>. Dokumentasi berlisensi Creative Commons Attribution-Share Alike 4.0. Arduino dan logo Arduino adalah merek Arduino S.r.l.</li>
<li><a href="https://mosquitto.org/man/mosquitto-conf-5.html" target="_blank" rel="noopener noreferrer">Manual konfigurasi Mosquitto (mosquitto.conf)</a></li>
<li><a href="https://mqttx.app/" target="_blank" rel="noopener noreferrer">MQTTX</a> · aplikasi oleh EMQ, Apache License 2.0. Screenshot jendela resmi tidak dipakai utuh karena menampilkan broker publik.</li>
<li><a href="https://commons.wikimedia.org/wiki/File:SRD-05VDC-SL-C_5V_one-channel_relay_module.jpg" target="_blank" rel="noopener noreferrer">SRD-05VDC-SL-C 5V one-channel relay module</a> · Suyash Dwivedi · Wikimedia Commons · Creative Commons Attribution-Share Alike 4.0. Foto hanya contoh rupa; jangan menyalin urutan kaki dari foto.</li>
<li>Diagram urutan tools, wiring, batas LAN, alur perintah, skema periksa, serta ilustrasi Library Manager dan MQTTX — Koding Indonesia (FS-35)</li>
</ul>

<h2>Selanjutnya</h2>
<p><strong>Ringkasnya:</strong> ESP32 kini menjadi pendengar perintah. Pada <strong>FS-36</strong>, data akan disimpan ke kartu microSD agar tidak hilang saat akan offline.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $sketch = htmlspecialchars($this->sketch(), ENT_QUOTES, 'UTF-8');
        $tools = $this->figure('fs35-tools-order.png', 'Five-step tool order: browser, Arduino IDE, relay wiring, PowerShell, then MQTTX', '<strong>Desk order (five steps):</strong> browser → Arduino IDE (book icon) → relay wiring → PowerShell for IP and broker → MQTTX. Connect MQTTX only after <code>1883</code> is visible. Diagram by Koding Indonesia (FS-35).');
        $library = $this->figure('fs35-library-manager.png', 'Arduino IDE 2 Library Manager illustration: three-book icon, ArduinoMqttClient search, INSTALL, ESP32 board', '<strong>This is the correct view, not an error screen.</strong> The three-book icon in the left bar. Search <em>ArduinoMqttClient</em>, then <em>ArduinoJson</em>. The board in the top-right is an <strong>ESP32</strong>, not an UNO. If FS-34 is already done, the libraries are usually already there. Illustration by Koding Indonesia (FS-35), modelled on Arduino IDE 2. The official window screenshot is not used as-is because it is dimmed and shows an UNO. Step reference: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-installing-a-library/" target="_blank" rel="noopener noreferrer">Installing libraries — Arduino IDE 2</a>, Arduino S.r.l. Arduino documentation is licensed under Creative Commons Attribution-Share Alike 4.0.');
        $wiring = $this->figure('fs35-wiring-relay.png', 'Five-volt relay module wiring to ESP32 by VCC, IN, and GND labels', '<strong>Main figure — wiring.</strong> Match the printed labels: VCC or + → 5V, IN or S → GPIO 26, GND or − → GND. Physical pin order can differ between modules. Leave NC/COM/NO empty today. Diagram by Koding Indonesia (FS-35).');
        $relayPhoto = $this->figure('kit-relay-5v.jpg', 'Example 5V one-channel Songle relay module with screw terminals and three S plus minus pins', '<strong>Appearance example only.</strong> This photo helps you recognise a kit relay. <strong>Do not copy pin order from the photo.</strong> Wiring still follows the printed labels: VCC/+ → 5V, IN/S → GPIO 26, GND/− → GND. The left screw terminals are the NC/COM/NO load path — <strong>not</strong> ESP32 pins, and <strong>not AC mains</strong>. Source: <a href="https://commons.wikimedia.org/wiki/File:SRD-05VDC-SL-C_5V_one-channel_relay_module.jpg" target="_blank" rel="noopener noreferrer">SRD-05VDC-SL-C 5V one-channel relay module</a> · Suyash Dwivedi · Wikimedia Commons · Creative Commons Attribution-Share Alike 4.0.');
        $lan = $this->figure('fs35-lan-address.png', 'ESP32 uses the PC LAN IPv4 address, not localhost or 127.0.0.1', '<strong>Important:</strong> <code>127.0.0.1</code> on ESP32 means ESP32 itself. Use the PC IPv4 from <code>ipconfig</code>. Diagram by Koding Indonesia (FS-35).');
        $flow = $this->figure('fs35-command-flow.png', 'Left-to-right flow: MQTTX, Mosquitto, ESP32, then relay; JSON status returns through Mosquitto', '<strong>Main figure — command flow.</strong> Read left to right: MQTTX → Mosquitto → ESP32 → relay. JSON status returns through Mosquitto to MQTTX on the <code>status</code> topic. Diagram by Koding Indonesia (FS-35).');
        $mqttx = $this->figure('fs35-mqttx-publish.png', 'MQTTX illustration connected to the PC IPv4, subscribed to status, publishing relay-on JSON', '<strong>This is the correct view, not an error screen.</strong> The MQTTX label on the left is not a close button. Host = the PC IPv4, Port = <code>1883</code>. Subscribe to <code>status</code>, then publish JSON to <code>command</code>. Do not copy a Host from an internet screenshot. Illustration by Koding Indonesia (FS-35), modelled on the official <a href="https://mqttx.app/" target="_blank" rel="noopener noreferrer">MQTTX by EMQ</a> layout (Apache License 2.0). The official window screenshot is not used as-is because it shows a public broker. The address <code>192.168.1.23</code> is only an example.');
        $troubleshooting = $this->figure('fs35-troubleshooting.png', 'Four checks when the ESP32 relay has not clicked after an MQTT command', '<strong>Helper schematic.</strong> Check relay wiring, Wi-Fi, broker and PC IP, then the MQTTX topic and JSON. Diagram by Koding Indonesia (FS-35).');
        $install = $this->stepsCard([
            ['title' => 'Open a browser', 'text' => 'Use Chrome, Firefox, Edge, or Safari. Keep this guide ready. Do not Upload a sketch yet.'],
            ['title' => 'Open Arduino IDE', 'text' => 'ArduinoMqttClient and ArduinoJson are usually already installed from FS-34. If not, click the three-book icon (Library Manager). Do not use the old <em>Tools → Manage Libraries</em> menu.'],
            ['title' => 'Prepare the relay wiring', 'text' => 'Unplug ESP32 USB first. 5V module: VCC/+ → 5V, IN/S → GPIO 26, GND/− → GND. Read the labels. Do not connect AC mains.'],
            ['title' => 'Open PowerShell only for IP and the broker', 'text' => 'Press Start → type <strong>PowerShell</strong> → choose <strong>Windows PowerShell</strong>. Do not use <em>Run as administrator</em>. The commands are in the IPv4 and Mosquitto sections.'],
            ['title' => 'Open MQTTX after the broker is running', 'text' => 'Only now click <em>New Connection</em>. Set Host to the <strong>PC IPv4</strong> (not <code>127.0.0.1</code>) and Port to <code>1883</code>. Subscribe to status first, then publish the command.'],
        ], '<strong>How to test today:</strong> success = the Mosquitto window stays open and shows <code>1883</code>, Serial Monitor prints <code>MQTT tersambung.</code> then <code>Subscribe command siap.</code>, the relay clicks, and MQTTX shows status JSON.');

        return <<<'HTML'
<h2>Introduction — the command now reaches the relay</h2>
<p><strong>FS-35 / #105 (this article)</strong> reverses FS-34. Yesterday ESP32 sent telemetry. Today MQTTX sends a command, ESP32 listens, then the GPIO 26 relay clicks.</p>
<p><strong>In short:</strong> ESP32 becomes the command listener. Mosquitto stays the post office. MQTTX writes an <code>on</code> or <code>off</code> note.</p>
<p><strong>Analogy:</strong> FS-34 was a thermometer sending numbers. FS-35 is a remote switch on the study desk. You press Publish on the PC, and the relay on the board clicks.</p>
<p>Lab prerequisites: local Mosquitto and MQTTX from FS-33/FS-34, plus the GPIO 26 relay pattern from FS-23. DHT22 is <strong>not</strong> used today so the desk stays simpler.</p>

<h2>Expected outcome</h2>
<ul>
<li>A 5V relay is safely wired to ESP32, with no AC mains.</li>
<li>MQTTX publishes JSON to the <code>command</code> topic.</li>
<li>The relay clicks and the indicator LED changes.</li>
<li>MQTTX receives status JSON on the <code>status</code> topic.</li>
</ul>
<p><strong>Today’s lab boundary:</strong> there is no web dashboard, cloud account, public broker, or mains lamp. Success = click + LED + status JSON.</p>

<h2>Terms used today</h2>
<ul>
<li><strong>Command</strong> — an order sent to a device; here <code>on</code> or <code>off</code>.</li>
<li><strong>Subscribe</strong> — ESP32 registers so the broker forwards a chosen topic.</li>
<li><strong>Status</strong> — a report sent back after the command runs.</li>
<li><strong>Relay</strong> — an electromagnetic switch. Today we only use the module click and LED.</li>
<li><strong>Library Manager</strong> — the Arduino IDE 2 left-bar panel with the three-book icon. Not the old <em>Tools</em> menu.</li>
<li><strong>Active LOW</strong> — many kit modules turn the relay on when the GPIO pin is LOW. That is the FS-23 pattern.</li>
<li><strong>NC / COM / NO</strong> — screw terminals for a load. Not ESP32 pins. Leave them empty today.</li>
<li><strong>PC IPv4</strong> — the computer’s address on home Wi-Fi, for example <code>192.168.1.23</code>. Yours will almost certainly differ.</li>
<li><strong><code>127.0.0.1</code> / <code>localhost</code></strong> — “this device itself”. On ESP32 that means ESP32, not the PC.</li>
<li><strong>Public broker</strong> — a broker on the internet owned by someone else. This lab does not use one.</li>
<li><strong>Guest Wi-Fi</strong> — a guest network that isolates devices. Do not use it in this lab.</li>
</ul>

<h2>Preparation — open the right tools first</h2>
HTML
            .$tools.$install.<<<'HTML'
<p><strong>Not used today:</strong> AC mains, router port forwarding, a public broker, DHT22, Laragon, <code>php artisan</code>, or a dashboard.</p>
<p><strong>Phone tip:</strong> if a diagram feels small, use the browser or screen zoom. You do not need to tap the image to fill the screen; nearby text should stay readable.</p>

<h2>Confirm Arduino IDE libraries</h2>
HTML
            .$library.<<<'HTML'
<p><strong>Open Arduino IDE first.</strong> In the left bar, open <strong>Library Manager</strong>: the three-book icon. That is the only path used today. Do not use the old <em>Tools → Manage Libraries</em> menu.</p>
<p>If FS-34 is already done, <strong>ArduinoMqttClient</strong> and <strong>ArduinoJson</strong> are usually installed. If Verify later complains, install them one by one with the book icon, ESP32 board, not an UNO. The <em>Tools → Serial Monitor</em> menu is still used later to read Serial text; that is <strong>not</strong> Library Manager.</p>
<p>Reference: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-installing-a-library/" target="_blank" rel="noopener noreferrer">Arduino IDE 2 installing-a-library documentation</a>, Arduino S.r.l. Arduino documentation is licensed under Creative Commons Attribution-Share Alike 4.0.</p>

<h2>Wire the relay</h2>
HTML
            .$wiring.$relayPhoto.<<<'HTML'
<ol>
<li>Unplug ESP32 USB.</li>
<li>On a <strong>5V relay module</strong>, connect <strong>VCC or + → 5V</strong>, <strong>IN or S → GPIO 26</strong>, and <strong>GND or − → GND</strong>.</li>
<li>Leave the <strong>NC / COM / NO</strong> screw terminals empty.</li>
<li>Reconnect the data USB cable.</li>
</ol>
<p><strong>Do not guess pins.</strong> Labels may read S / + / − or IN / VCC / GND. Read those words. <strong>Do not connect AC mains.</strong> GPIO 26 follows the FS-23 kit table.</p>

<h2>Find the computer IPv4 address</h2>
<p><strong>Open PowerShell first:</strong> press Start → type <strong>PowerShell</strong> → choose <strong>Windows PowerShell</strong>. Do not use <em>Run as administrator</em>.</p>
<p><strong>How to paste:</strong> copy the line below, click the PowerShell window, then press <kbd>Ctrl</kbd>+<kbd>V</kbd> or right-click. After the text appears, press Enter.</p>
<pre><code>ipconfig</code></pre>
<p>Find the Wi-Fi adapter <strong>IPv4 Address</strong>. This guide uses <code>192.168.1.23</code> only as an example. Write that number down. It is both the MQTTX Host and <code>MQTT_HOST</code>.</p>
<p><strong>macOS:</strong> open the <strong>Terminal</strong> app first, then run <code>ifconfig</code> and look for <code>inet</code> on Wi-Fi. <strong>Ubuntu or Debian:</strong> open <strong>Terminal</strong> first, then run <code>ip addr</code>.</p>

<h2>Run Mosquitto so ESP32 may join</h2>
HTML
            .$lan.<<<'HTML'
<p>As in FS-34, ESP32 is a different device, so this short lab creates a LAN listener bound only to the PC home-network IPv4. FS-33 did not use a <code>listener</code> because MQTTX and the broker were on the same computer.</p>
<ol>
<li>Open <strong>Notepad</strong>, paste the two lines below, and replace the sample address with your PC IPv4:</li>
</ol>
<pre><code>listener 1883 192.168.1.23
listener_allow_anonymous true</code></pre>
<ol start="2">
<li>Save as <code>mosquitto-fs35.conf</code> in <strong>Documents</strong>. Choose <em>Save as type: All files</em> so Notepad does not add <code>.txt</code>.</li>
<li>Return to PowerShell. <strong>How to paste:</strong> copy the line below, click the PowerShell window, then <kbd>Ctrl</kbd>+<kbd>V</kbd> or right-click, then Enter.</li>
</ol>
<pre><code>&amp; 'C:\Program Files\mosquitto\mosquitto.exe' -c "$env:USERPROFILE\Documents\mosquitto-fs35.conf" -v</code></pre>
<p><strong>Expected result:</strong> the window stays open and shows port <code>1883</code>. Keep it open.</p>
<p>If Windows asks about network access, allow only <strong>Private networks</strong> for trusted home Wi-Fi, never Public networks. Do not open a router port or use guest Wi-Fi.</p>
<p><strong>Why anonymous access?</strong> This is a short LAN lab. Anyone on the same home Wi-Fi can send a command. Stop the broker with <strong>Ctrl+C</strong> when finished. Users, passwords, and TLS come in FS-49. See the <a href="https://mosquitto.org/man/mosquitto-conf-5.html" target="_blank" rel="noopener noreferrer">Mosquitto configuration documentation</a>.</p>
<p><strong>macOS or Linux:</strong> open <strong>Terminal</strong> first, save the same config file, then run <code>mosquitto -c ~/Documents/mosquitto-fs35.conf -v</code> and keep the window open.</p>

<h2>Connect MQTTX, subscribe to status, prepare the command</h2>
HTML
            .$mqttx.<<<'HTML'
<p>Keep the Mosquitto window open with <code>1883</code> visible. Only now open <strong>MQTTX</strong>.</p>
<ol>
<li>Click <em>New Connection</em> and name it <code>FS35 perintah LAN</code>.</li>
<li>Set <strong>Host</strong> to the PC IPv4, for example <code>192.168.1.23</code>. Do not use <code>127.0.0.1</code>.</li>
<li>Set <strong>Port</strong> to <code>1883</code>, then Connect.</li>
<li>Subscribe to the status topic below and leave MQTTX open:</li>
</ol>
<pre><code>kodingindonesia/fsiot/esp32-meja-01/status</code></pre>
<p>Also prepare this publish topic:</p>
<pre><code>kodingindonesia/fsiot/esp32-meja-01/command</code></pre>
<p>Use this JSON payload exactly, in lowercase:</p>
<pre><code>{"device_id":"esp32-meja-01","relay":"on"}</code></pre>
<p>To turn it off, change <code>"relay":"off"</code>. Do <strong>not</strong> press Publish until ESP32 has uploaded and Serial prints <code>Subscribe command siap.</code></p>

<h2>ESP32 sketch — subscribe to command</h2>
HTML
            .$flow.<<<'HTML'
<p>Create <code>FS35_mqtt_relay_command</code> in Arduino IDE. Replace <code>GANTI_NAMA_WIFI</code>, <code>GANTI_SANDI_WIFI</code>, and the example IPv4 before Upload. Do not put a real password in a screenshot.</p>
<p><strong>Why subscribe again after MQTT connects?</strong> If Wi-Fi or the broker drops, ESP32 must register for the <code>command</code> topic again. Without that, MQTTX Publish never arrives.</p>
<pre><code class="language-arduino">
HTML
            .$sketch.<<<'HTML'
</code></pre>
<p>MQTT subscribe follows the official ArduinoMqttClient pattern: <code>onMessage()</code>, <code>connect()</code>, <code>subscribe()</code>, then <code>poll()</code> in the loop. JSON uses <code>JsonDocument</code> and <code>serializeJson()</code>. References: <a href="https://github.com/arduino-libraries/ArduinoMqttClient" target="_blank" rel="noopener noreferrer">ArduinoMqttClient</a>, <a href="https://arduinojson.org/v7/api/jsondocument/" target="_blank" rel="noopener noreferrer">ArduinoJson JsonDocument</a>, and the <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/api/wifi.html" target="_blank" rel="noopener noreferrer">Espressif Wi-Fi API</a>.</p>

<h2>Upload and test the command</h2>
<ol>
<li>Select the ESP32 board and USB port, then <strong>Verify</strong> and <strong>Upload</strong>.</li>
<li>Open <strong>Tools → Serial Monitor</strong> at <strong>115200</strong> baud.</li>
<li>Look for <code>MQTT tersambung.</code> then <code>Subscribe command siap.</code></li>
<li>Return to MQTTX. Keep the status subscription active. Publish the <code>on</code> JSON to the command topic.</li>
<li>Listen for the relay click and watch the LED. MQTTX should show status JSON. Then publish <code>off</code>.</li>
</ol>
<p><strong>Success means:</strong> Serial prints <code>Relay ON</code> or <code>Relay OFF</code>, the module clicks, and the status topic shows <code>"ok":true</code>. The same message should appear in Serial and MQTTX.</p>

<h2>If the relay does not click</h2>
HTML
            .$troubleshooting.<<<'HTML'
<ol>
<li><strong>ESP32 uses <code>127.0.0.1</code>.</strong> Replace it with the PC IPv4 from <code>ipconfig</code> and Upload again.</li>
<li><strong>Wrong topic.</strong> Publish to <code>command</code>, subscribe to <code>status</code>. Both must match the sketch exactly.</li>
<li><strong>JSON case or missing <code>device_id</code>.</strong> Copy the lowercase example in this article.</li>
<li><strong>Subscribe lost after a drop.</strong> The sketch resubscribes after MQTT connects. Do not close the Mosquitto window.</li>
<li><strong>The module is active HIGH while the sketch is still active LOW.</strong> If the LED is inverted, set <code>AKTIF_LOW</code> to <code>false</code> and Upload again. That is the FS-23 pattern.</li>
<li><strong>Wrong pin.</strong> IN/S must be GPIO 26, VCC to 5V, shared GND. Do not guess from the photo.</li>
<li><strong>PC and ESP32 use different Wi-Fi.</strong> Use the same home network, not guest Wi-Fi.</li>
</ol>

<h2 id="fsiot-command-checklist">Checklist before FS-36</h2>
<p>Tick only after doing the step. Target: <strong>10/10</strong>. Progress stays in this browser and is not sent to the server.</p>
<ul id="fsiot-command-checklist-items">
<li>ArduinoMqttClient and ArduinoJson are installed.</li>
<li>Relay wiring matches 5V, GPIO 26, GND by the printed labels.</li>
<li>NC/COM/NO terminals are empty and there is no AC mains.</li>
<li>ESP32 and PC use the same home Wi-Fi.</li>
<li>I found the PC IPv4 with <code>ipconfig</code>.</li>
<li>Mosquitto is running in PowerShell and shows 1883.</li>
<li>MQTTX uses PC IPv4, port 1883, and subscribes to the status topic.</li>
<li>The sketch uses my Wi-Fi and <code>MQTT_HOST</code>.</li>
<li>Serial Monitor prints <code>Subscribe command siap.</code></li>
<li>Publishing <code>on</code> then <code>off</code> clicks the relay and shows status JSON.</li>
</ul>
<p><strong>How to check readiness:</strong> explain MQTTX → Mosquitto → ESP32 → relay in your own words. Stop the lab broker with <strong>Ctrl+C</strong>. FS-36 stores data on a microSD card.</p>

<h2>Common mistakes</h2>
<ul>
<li><strong>MQTTX Host is still <code>127.0.0.1</code>.</strong> Today Host = the PC IPv4, as in FS-34.</li>
<li><strong>Publishing to the status topic.</strong> Commands enter through <code>command</code>. Status is only the report back.</li>
<li><strong>Writing only uppercase ON.</strong> The sketch lowercases <code>on</code>/<code>off</code>, but <code>device_id</code> must match exactly.</li>
<li><strong>Connecting a mains lamp to NC/COM/NO.</strong> This lab does not need that.</li>
<li><strong>Using guest Wi-Fi or opening a router port.</strong> This lab does not need that.</li>
<li><strong>Forgetting to resubscribe after a broker restart.</strong> Upload again or wait until Serial prints <code>Subscribe command siap.</code></li>
</ul>

<h2>Frequently asked questions</h2>
<h3>Why must ESP32 not use 127.0.0.1?</h3>
<p>That address means “myself”. On ESP32 it means ESP32, not the PC running Mosquitto.</p>
<h3>Why can anyone on home Wi-Fi send a command?</h3>
<p>This lab listener is anonymous and only for a short exercise. Do not leave the broker running overnight. Users and passwords come in FS-49.</p>
<h3>My module is active HIGH. What do I change?</h3>
<p>Set <code>AKTIF_LOW</code> to <code>false</code>, then Verify and Upload. Do not guess pins.</p>
<h3>Why not guest Wi-Fi?</h3>
<p>Guest Wi-Fi often isolates devices. ESP32 will not find Mosquitto on the PC.</p>
<h3>May I connect a 220 V lamp?</h3>
<p>Not in this lab. Success is the click and indicator LED. Mains AC comes much later, with separate safety.</p>
<h3>Why is DHT22 unused?</h3>
<p>FS-34 already sent temperature. Today has one job: a command down to a switch. The desk stays tidy.</p>

<h2>Sources</h2>
<ul>
<li><a href="https://github.com/arduino-libraries/ArduinoMqttClient" target="_blank" rel="noopener noreferrer">ArduinoMqttClient</a> — <code>onMessage</code>, <code>subscribe</code>, and <code>poll</code> pattern</li>
<li><a href="https://arduinojson.org/v7/api/jsondocument/" target="_blank" rel="noopener noreferrer">ArduinoJson — JsonDocument</a></li>
<li><a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/api/wifi.html" target="_blank" rel="noopener noreferrer">Espressif — Arduino ESP32 Wi-Fi API</a></li>
<li><a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-installing-a-library/" target="_blank" rel="noopener noreferrer">Arduino — Installing libraries (IDE 2)</a>. Documentation licensed under Creative Commons Attribution-Share Alike 4.0. Arduino and the Arduino logo are trademarks of Arduino S.r.l.</li>
<li><a href="https://mosquitto.org/man/mosquitto-conf-5.html" target="_blank" rel="noopener noreferrer">Mosquitto configuration manual (mosquitto.conf)</a></li>
<li><a href="https://mqttx.app/" target="_blank" rel="noopener noreferrer">MQTTX</a> · app by EMQ, Apache License 2.0. The official window screenshot is not used as-is because it shows a public broker.</li>
<li><a href="https://commons.wikimedia.org/wiki/File:SRD-05VDC-SL-C_5V_one-channel_relay_module.jpg" target="_blank" rel="noopener noreferrer">SRD-05VDC-SL-C 5V one-channel relay module</a> · Suyash Dwivedi · Wikimedia Commons · Creative Commons Attribution-Share Alike 4.0. The photo is an appearance example only; do not copy pin order from the photo.</li>
<li>Tool-order, wiring, LAN-boundary, command-flow, troubleshooting, Library Manager, and MQTTX diagrams — Koding Indonesia (FS-35)</li>
</ul>

<h2>Next</h2>
<p><strong>In short:</strong> ESP32 is now a command listener. In <strong>FS-36</strong>, data is stored on a microSD card so it is not lost when the station is about to go offline.</p>
HTML;
    }
}
