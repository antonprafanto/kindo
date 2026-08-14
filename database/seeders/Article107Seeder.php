<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class Article107Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@kodingindonesia.com')->first() ?? User::first();
        $category = Category::where('slug', 'iot-smart-device')->first() ?? Category::where('slug', 'esp32-arduino')->first();
        if (! $admin || ! $category) {
            throw new \RuntimeException('User atau kategori IoT tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-esp32-sd-store-and-forward';
        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        foreach (['fullstack-iot', 'iot', 'esp32', 'microsd', 'mqtt', 'wifi'] as $tagSlug) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagSlug]);
        }

        $article = Article::updateOrCreate(['slug' => $slug], [
            'user_id' => $admin->id,
            'category_id' => $category->id,
            'title' => 'ESP32 kirim ulang suhu dari kartu saat Wi-Fi kembali',
            'title_en' => 'ESP32 resends temperature from the card when Wi-Fi returns',
            'excerpt' => 'FS-37 / #107: antrian di pending.csv, bukan RAM tak terbatas. Matikan hotspot HP, lalu nyalakan lagi; MQTTX terisi from_sd true.',
            'excerpt_en' => 'FS-37 / #107: queue on pending.csv, not unbounded RAM. Turn the phone hotspot off, then on; MQTTX fills with from_sd true.',
            'body' => $this->body(),
            'body_en' => $this->bodyEn(),
            'status' => 'draft',
            'is_featured' => false,
            'published_at' => null,
            'seo_title' => 'ESP32 Kirim Ulang Suhu dari Kartu saat Wi-Fi Kembali — FS-37',
            'seo_title_en' => 'ESP32 Resends Temperature from the Card when Wi-Fi Returns — FS-37',
            'seo_description' => 'Store-and-forward pemula: ESP32 menaruh antrian di pending.csv saat hotspot putus, lalu mengirim ulang ke Mosquitto saat Wi-Fi kembali.',
            'seo_description_en' => 'A first store-and-forward lab: the ESP32 queues on pending.csv while the hotspot is down, then resends to Mosquitto when Wi-Fi returns.',
        ]);
        $article->tags()->sync(Tag::whereIn('slug', ['fullstack-iot', 'iot', 'esp32', 'microsd', 'mqtt', 'wifi'])->pluck('id'));

        foreach (['webp', 'jpg'] as $extension) {
            $source = public_path("images/fsiot/fs37-cover-forward.{$extension}");
            if (is_file($source)) {
                $destination = "articles/covers/fs37-cover-forward.{$extension}";
                Storage::disk('public')->put($destination, file_get_contents($source));
            }
        }
        $article->update([
            'cover_image' => 'https://kodingindonesia.com/images/fsiot/fs37-cover-forward.webp',
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
            '#include <SPI.h>',
            '#include <SD.h>',
            '#include <WiFi.h>',
            '#include <ArduinoMqttClient.h>',
            '#include <ArduinoJson.h>',
            '#include <DHT.h>',
            '',
            'const byte CS_PIN = 5;',
            'const byte DHT_PIN = 4;',
            '',
            'const char WIFI_SSID[] = "GANTI_NAMA_HOTSPOT";  // hotspot HP, bukan Wi-Fi rumah PC',
            'const char WIFI_PASSWORD[] = "GANTI_SANDI_HOTSPOT";',
            'const char MQTT_HOST[] = "192.168.1.23";  // IPv4 PC dari ipconfig',
            'const int MQTT_PORT = 1883;',
            'const char DEVICE_ID[] = "esp32-meja-01";',
            'const char TOPIC_TELEMETRY[] = "kodingindonesia/fsiot/esp32-meja-01/telemetry";',
            '',
            'const unsigned long SAMPLE_INTERVAL_MS = 5000UL;',
            'const int MAX_FLUSH_PER_LOOP = 5;',
            'const char PENDING_PATH[] = "/pending.csv";',
            'const char LOG_PATH[] = "/log.csv";',
            '',
            'DHT dht(DHT_PIN, DHT22);',
            'WiFiClient wifiClient;',
            'MqttClient mqttClient(wifiClient);',
            'unsigned long lastSampleAt = 0;',
            'unsigned long lastWifiAttemptAt = 0;',
            'unsigned long lastMqttAttemptAt = 0;',
            'bool kartuSiap = false;',
            'bool sudahUmumkanPutus = false;',
            '',
            'void tulisHeaderLogJikaPerlu() {',
            '  if (SD.exists(LOG_PATH)) {',
            '    return;',
            '  }',
            '  File file = SD.open(LOG_PATH, FILE_WRITE);',
            '  if (!file) {',
            '    return;',
            '  }',
            '  file.println("timestamp_ms,temperature_c,jalur");',
            '  file.close();',
            '}',
            '',
            'void catatLog(unsigned long stampMs, float temperature, const char* jalur) {',
            '  File file = SD.open(LOG_PATH, FILE_APPEND);',
            '  if (!file) {',
            '    return;',
            '  }',
            '  file.print(stampMs);',
            '  file.print(\',\');',
            '  file.print(temperature, 1);',
            '  file.print(\',\');',
            '  file.println(jalur);',
            '  file.close();',
            '}',
            '',
            'void simpanPending(unsigned long stampMs, float temperature) {',
            '  File file = SD.open(PENDING_PATH, FILE_APPEND);',
            '  if (!file) {',
            '    Serial.println("Tidak bisa menulis pending.csv.");',
            '    return;',
            '  }',
            '  file.print(stampMs);',
            '  file.print(\',\');',
            '  file.println(temperature, 1);',
            '  file.close();',
            '  Serial.print("Wi-Fi putus. Disimpan ke pending.csv: ");',
            '  Serial.print(stampMs);',
            '  Serial.print(\',\');',
            '  Serial.println(temperature, 1);',
            '  catatLog(stampMs, temperature, "kartu");',
            '}',
            '',
            'bool kirimJson(float temperature, unsigned long stampMs, bool fromSd) {',
            '  JsonDocument data;',
            '  data["device_id"] = DEVICE_ID;',
            '  data["temperature_c"] = temperature;',
            '  data["timestamp_ms"] = stampMs;',
            '  data["from_sd"] = fromSd;',
            '  String payload;',
            '  serializeJson(data, payload);',
            '  mqttClient.beginMessage(TOPIC_TELEMETRY);',
            '  mqttClient.print(payload);',
            '  if (!mqttClient.endMessage()) {',
            '    return false;',
            '  }',
            '  if (fromSd) {',
            '    Serial.print("Kirim ulang dari kartu: ");',
            '  } else {',
            '    Serial.print("Terkirim: ");',
            '  }',
            '  Serial.println(payload);',
            '  return true;',
            '}',
            '',
            'void flushPending() {',
            '  if (!kartuSiap || !mqttClient.connected() || !SD.exists(PENDING_PATH)) {',
            '    return;',
            '  }',
            '  File src = SD.open(PENDING_PATH, FILE_READ);',
            '  if (!src) {',
            '    return;',
            '  }',
            '  File dst = SD.open("/pending.tmp", FILE_WRITE);',
            '  if (!dst) {',
            '    src.close();',
            '    return;',
            '  }',
            '  int flushed = 0;',
            '  bool gagal = false;',
            '  while (src.available()) {',
            '    String line = src.readStringUntil(\'\\n\');',
            '    line.trim();',
            '    if (line.length() == 0) {',
            '      continue;',
            '    }',
            '    int comma = line.indexOf(\',\');',
            '    if (gagal || comma < 1 || flushed >= MAX_FLUSH_PER_LOOP) {',
            '      dst.println(line);',
            '      continue;',
            '    }',
            '    unsigned long stampMs = strtoul(line.substring(0, comma).c_str(), NULL, 10);',
            '    float temperature = line.substring(comma + 1).toFloat();',
            '    if (kirimJson(temperature, stampMs, true)) {',
            '      flushed++;',
            '    } else {',
            '      dst.println(line);',
            '      gagal = true;',
            '    }',
            '  }',
            '  src.close();',
            '  dst.close();',
            '  SD.remove(PENDING_PATH);',
            '  SD.rename("/pending.tmp", PENDING_PATH);',
            '}',
            '',
            'void connectWifi() {',
            '  if (WiFi.status() == WL_CONNECTED) {',
            '    sudahUmumkanPutus = false;',
            '    return;',
            '  }',
            '  if (!sudahUmumkanPutus) {',
            '    Serial.println("Wi-Fi putus. Data ke kartu, bukan RAM tak terbatas.");',
            '    sudahUmumkanPutus = true;',
            '  }',
            '  if (lastWifiAttemptAt != 0 && millis() - lastWifiAttemptAt < 10000UL) {',
            '    return;',
            '  }',
            '  lastWifiAttemptAt = millis();',
            '  Serial.println("Menghubungkan Wi-Fi hotspot HP");',
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
            '  Serial.println("MQTT tersambung. Mengirim antrian kartu.");',
            '  flushPending();',
            '  return true;',
            '}',
            '',
            'void ambilSampel() {',
            '  float temperature = dht.readTemperature();',
            '  if (isnan(temperature)) {',
            '    Serial.println("DHT22 belum terbaca. Periksa kabel GPIO 4.");',
            '    return;',
            '  }',
            '  unsigned long stampMs = millis();',
            '  if (WiFi.status() == WL_CONNECTED && mqttClient.connected() && kirimJson(temperature, stampMs, false)) {',
            '    catatLog(stampMs, temperature, "mqtt");',
            '    return;',
            '  }',
            '  if (!kartuSiap) {',
            '    Serial.println("Kartu belum siap, sampel dilewati.");',
            '    return;',
            '  }',
            '  simpanPending(stampMs, temperature);',
            '}',
            '',
            'void setup() {',
            '  Serial.begin(115200);',
            '  dht.begin();',
            '  SPI.begin(18, 19, 23, CS_PIN);',
            '  kartuSiap = SD.begin(CS_PIN);',
            '  if (!kartuSiap) {',
            '    Serial.println("Kartu tidak terbaca. Periksa CS=GPIO 5, format FAT32, dan GND bersama.");',
            '  } else {',
            '    tulisHeaderLogJikaPerlu();',
            '    Serial.println("Kartu siap. Antrian di /pending.csv");',
            '    Serial.println("Antrian hanya di kartu, bukan RAM tak terbatas.");',
            '  }',
            '  connectWifi();',
            '}',
            '',
            'void loop() {',
            '  if (WiFi.status() != WL_CONNECTED) {',
            '    connectWifi();',
            '  } else {',
            '    connectMqttIfNeeded();',
            '    if (mqttClient.connected()) {',
            '      mqttClient.poll();',
            '      flushPending();',
            '    }',
            '  }',
            '  if (millis() - lastSampleAt >= SAMPLE_INTERVAL_MS) {',
            '    lastSampleAt = millis();',
            '    ambilSampel();',
            '  }',
            '}',
        ]);
    }

    private function body(): string
    {
        $sketch = htmlspecialchars($this->sketch(), ENT_QUOTES, 'UTF-8');
        $tools = $this->figure('fs37-tools-order.png', 'Urutan lima langkah: browser, MQTTX dan broker, Arduino IDE, kabel SPI, lalu Serial Monitor', '<strong>Urutan meja kerja (lima langkah):</strong> browser → MQTTX + Mosquitto (Host = IPv4 PC) → Arduino IDE → kabel SPI seperti FS-36 → Serial Monitor, lalu matikan hotspot HP. Diagram buatan Koding Indonesia (FS-37).');
        $flow = $this->figure('fs37-offline-online.png', 'Alur kiri ke kanan: hotspot putus, pending.csv, hotspot nyala, MQTTX terisi from_sd true', '<strong>Gambar utama — alur.</strong> Baca dari kiri ke kanan: putus → simpan di <code>pending.csv</code> → nyambung → MQTTX terisi. Diagram buatan Koding Indonesia (FS-37).');
        $ram = $this->figure('fs37-ram-vs-sd.png', 'Perbandingan antrian di RAM yang cepat penuh versus antrian di pending.csv', '<strong>RAM kecil. Kartu yang menahan antrian.</strong> Lab ini tidak menumpuk sampel di memori. Diagram buatan Koding Indonesia (FS-37).');
        $pending = $this->figure('fs37-pending-csv.png', 'Dua berkas: pending.csv untuk antrian belum terkirim dan log.csv sebagai arsip', '<strong>Dua berkas.</strong> <code>pending.csv</code> hanya baris yang belum ke broker. <code>log.csv</code> arsip semua sampel. Diagram buatan Koding Indonesia (FS-37).');
        $serial = $this->figure('fs37-serial-monitor.png', 'Ilustrasi Serial Monitor menampilkan Kartu siap, Wi-Fi putus, dan Kirim ulang dari kartu', '<strong>Buka Tools → Serial Monitor, baud 115200.</strong> Cari <code>Kartu siap. Antrian di /pending.csv</code>, lalu saat hotspot mati <code>Wi-Fi putus. Disimpan ke pending.csv</code>, lalu <code>Kirim ulang dari kartu:</code>. Ilustrasi buatan Koding Indonesia (FS-37), meniru Serial Monitor Arduino IDE 2. Screenshot jendela resmi tidak dipakai utuh. Acuan menu: dokumentasi Arduino IDE 2, Arduino S.r.l., Creative Commons Attribution-Share Alike 4.0.');
        $mqttx = $this->figure('fs37-mqttx-backfill.png', 'Ilustrasi MQTTX menampilkan pesan live from_sd false lalu kiriman ulang from_sd true', '<strong>MQTTX sudah menampilkan kiriman ulang dari kartu.</strong> Host = IPv4 PC, Port = <code>1883</code>. Pesan <code>from_sd: true</code> datang berdekatan setelah hotspot nyala. Ilustrasi buatan Koding Indonesia (FS-37), meniru tata letak <a href="https://mqttx.app/" target="_blank" rel="noopener noreferrer">MQTTX oleh EMQ</a> (Apache License 2.0). Screenshot jendela resmi tidak dipakai utuh karena sering menampilkan broker publik. Angka <code>192.168.1.23</code> hanya contoh.');
        $troubleshooting = $this->figure('fs37-troubleshooting.png', 'Empat pemeriksaan jika antrian microSD tidak terkirim ulang ke MQTTX', '<strong>Skema bantu.</strong> Periksa hotspot HP, jendela Mosquitto, kartu pending.csv, lalu MQTTX. Diagram buatan Koding Indonesia (FS-37).');
        $twoWifi = $this->figure('fs37-two-wifi.png', 'PC tetap di Wi-Fi rumah menjalankan Mosquitto, ESP32 memakai hotspot HP, MQTT_HOST adalah IPv4 PC', '<strong>Dua Wi-Fi, satu tujuan.</strong> PC + Mosquitto tetap di Wi-Fi rumah. ESP32 memakai hotspot HP. <code>MQTT_HOST</code> = IPv4 PC dari <code>ipconfig</code>, bukan <code>127.0.0.1</code>. Diagram buatan Koding Indonesia (FS-37).');
        $hotspot = $this->figure('fs37-hotspot-demo.png', 'Urutan demo: buka panel HP, matikan hotspot, PC dan Mosquitto tetap hidup, lalu nyalakan hotspot lagi', '<strong>Buka dulu panel HP.</strong> Geser dari atas layar (atau buka Pengaturan), ketuk Hotspot sampai mati 20–40 detik, lalu nyalakan lagi. USB ESP32 tetap colok. Diagram buatan Koding Indonesia (FS-37).');
        $wiring = $this->figure('fs36-wiring-spi.png', 'Wiring microSD SPI dan DHT22 ke ESP32 sama seperti FS-36, termasuk GND modul SD', '<strong>Wiring hari ini sama seperti FS-36.</strong> CS → GPIO 5, SCK → GPIO 18, MISO → GPIO 19, MOSI → GPIO 23, DHT22 DATA → GPIO 4, <strong>GND modul SD juga ke GND ESP32</strong>. Jangan colok AC 220V. Diagram buatan Koding Indonesia (FS-36), dipakai lagi di FS-37 karena pin tidak berubah.');
        $kitModule = $this->figure('fs36-modul-kit.png', 'Ilustrasi modul microSD SPI kit dengan enam pin CS SCK MOSI MISO VCC GND', '<strong>Kenali modul kit dulu.</strong> Toko sering menjual papan biru enam pin seperti ini. Baca tulisannya: CS, SCK, MOSI, MISO, VCC, GND. Urutan kaki di papanmu boleh berbeda. Ilustrasi buatan Koding Indonesia (FS-36).');
        $spiPhoto = $this->figure('kit-microsd-spi.jpg', 'Contoh rupa papan breakout microSD SPI Adafruit delapan pin, bukan modul kit enam pin', '<strong>Bentuk lain, delapan pin.</strong> Ini breakout Adafruit (ada kaki CD, 3V, dan 5V). Kit toko sering hanya enam pin seperti ilustrasi di atas. Busnya tetap SPI. <strong>Jangan menyalin urutan kaki dari foto.</strong> Wiring menurut tulisan pin dan tabel GPIO. Sumber: <a href="https://commons.wikimedia.org/wiki/File:SD_Card_Breakout_Board.jpg" target="_blank" rel="noopener noreferrer">SD Card Breakout Board</a> · oomlout · Wikimedia Commons · Creative Commons Attribution-Share Alike 2.0.');
        $cardPhoto = $this->figure('kit-microsd-card.jpg', 'Contoh rupa kartu microSD dan adapter SD plastik untuk slot laptop', '<strong>Contoh rupa kartu saja.</strong> Adapter plastik untuk slot laptop, <strong>bukan</strong> modul SPI. <strong>Jangan menyambungkannya ke pin ESP32.</strong> <strong>Jangan menyalin urutan kaki dari foto.</strong> Sumber: <a href="https://commons.wikimedia.org/wiki/File:2015_Karta_microSD_z_adapterem_SD.jpg" target="_blank" rel="noopener noreferrer">2015 Karta microSD z adapterem SD</a> · Jacek Halicki · Wikimedia Commons · Creative Commons Attribution-Share Alike 4.0.');
        $dhtPhoto = $this->figure('kit-dht22.jpg', 'Contoh rupa modul DHT22 AM2302 dengan pin DAT, VCC, dan GND', '<strong>Contoh rupa sensor.</strong> <strong>Jangan menyalin urutan kaki dari foto.</strong> Wiring tetap VCC → 3V3, DATA atau DAT → GPIO 4, GND → GND. Sumber: <a href="https://commons.wikimedia.org/wiki/File:DHT_22_Sensor.jpg" target="_blank" rel="noopener noreferrer">AM2302 DHT22 Sensor</a> · L293D · Wikimedia Commons · Creative Commons Attribution-Share Alike 4.0.');
        $install = $this->stepsCard([
            ['title' => 'Buka browser', 'text' => 'Pakai Chrome, Firefox, Edge, atau Safari. Siapkan artikel ini. Jangan Upload sketch dulu.'],
            ['title' => 'Buka MQTTX setelah broker berjalan', 'text' => 'Mosquitto dulu, seperti FS-34: jendela tetap terbuka dan terlihat <code>1883</code>. Baru kemudian MQTTX. Host = <strong>IPv4 PC</strong> (bukan <code>127.0.0.1</code>), Port <code>1883</code>, subscribe <code>kodingindonesia/fsiot/esp32-meja-01/telemetry</code>.'],
            ['title' => 'Buka Arduino IDE', 'text' => '<code>SD.h</code> sudah di core ESP32. <code>ArduinoMqttClient</code>, ArduinoJson, dan DHT biasanya sudah ada dari FS-34. Jika Verify mengeluh, klik ikon tiga buku di bilah kiri. Itu satu-satunya jalur yang dipakai hari ini.'],
            ['title' => 'Periksa kabel SPI dan DHT22', 'text' => 'Sama seperti FS-36. Cabut USB dulu. CS → GPIO 5, SCK → GPIO 18, MISO → GPIO 19, MOSI → GPIO 23, DHT22 DATA → GPIO 4. Jangan colok AC 220V. Relay GPIO 26 <strong>tidak</strong> dipakai hari ini.'],
            ['title' => 'Upload, Serial Monitor, lalu buka panel HP', 'text' => 'Tools → Serial Monitor, baud 115200. Setelah ada <code>Terkirim:</code> di MQTTX, <strong>buka dulu panel atas HP</strong> (geser dari atas, atau buka Pengaturan). Ketuk Hotspot sampai mati — bukan router rumah, bukan Wi-Fi laptop. Nyalakan lagi, cari <code>Kirim ulang dari kartu:</code>.'],
        ], '<strong>Cara menguji hari ini:</strong> bukti sukses = MQTTX sempat sepi saat hotspot mati, lalu terisi pesan <code>from_sd: true</code> setelah hotspot nyala, dan Serial menulis <code>Kirim ulang dari kartu:</code>.');

        return <<<'HTML'
<h2>Pendahuluan — Wi-Fi putus tidak boleh menghapus jendela waktu</h2>
<p><strong>FS-37 / #107 (ini)</strong> menyambungkan pekerjaan FS-36 (kartu) dan FS-34 (MQTT). Kemarin kartu menyimpan <code>log.csv</code> untuk dibaca di PC. Hari ini kartu menjadi <strong>antrian</strong>: data menunggu, lalu dikirim ulang ke Mosquitto ketika hotspot HP nyala lagi.</p>
<p><strong>Intinya:</strong> ESP32 tidak menumpuk ratusan angka di RAM. Yang belum terkirim masuk <code>pending.csv</code>. Saat MQTT tersambung, baris itu dikirim ulang. MQTTX terisi, jendela waktu demo tidak bolong total.</p>
<p><strong>Analogi:</strong> FS-34 adalah kurir yang langsung antar surat. FS-36 adalah laci di meja. FS-37 adalah laci yang dikosongkan ke kantor pos begitu jalan buka kembali.</p>
<p>Prasyarat lab: Mosquitto + MQTTX dari FS-33/FS-34, kartu FAT32 dan wiring SPI dari FS-36, DHT22 GPIO 4. Relay GPIO 26 <strong>tidak</strong> dipakai hari ini. Node-RED dan Python ditunda ke FS-38 dan FS-39.</p>

<h2>Hasil yang dituju</h2>
<ul>
<li>Saat hotspot HP nyala, MQTTX mendapat JSON kira-kira tiap lima detik, <code>from_sd: false</code>.</li>
<li>Saat hotspot dimatikan, Serial menulis <code>Wi-Fi putus. Disimpan ke pending.csv</code>. MQTTX sepi. Mosquitto di PC tetap hidup.</li>
<li>Saat hotspot dinyalakan lagi, Serial menulis <code>Kirim ulang dari kartu:</code> dan MQTTX terisi <code>from_sd: true</code>.</li>
<li>Kamu bisa menjelaskan kenapa antrian tidak boleh tak terbatas di RAM.</li>
</ul>
<p><strong>Batas lab hari ini:</strong> tidak ada dashboard web, broker publik, port forwarding, AC 220V, atau <code>SD_MMC</code>. Bukti cukup = Serial + MQTTX.</p>

<h2>Istilah yang dipakai hari ini</h2>
<ul>
<li><strong>Store-and-forward</strong> — simpan dulu, kirim kemudian. Bukan membuang sampel hanya karena Wi-Fi sedang putus.</li>
<li><strong>Antrian</strong> — baris yang menunggu giliran ke broker. Di lab ini tempatnya <code>pending.csv</code>.</li>
<li><strong><code>pending.csv</code></strong> — berkas di kartu: hanya data yang belum terkirim.</li>
<li><strong><code>log.csv</code></strong> — arsip semua sampel, kolom terakhir <code>mqtt</code> atau <code>kartu</code>.</li>
<li><strong><code>from_sd</code></strong> — field JSON. <code>false</code> = langsung dari sensor. <code>true</code> = kiriman ulang dari kartu.</li>
<li><strong>Hotspot HP</strong> — Wi-Fi yang dipakai ESP32 hari ini. PC tetap di Wi-Fi rumah agar Mosquitto tidak ikut mati.</li>
<li><strong>RAM tak terbatas</strong> — kesalahan yang sering terjadi: menumpuk array di memori sampai ESP32 mentok atau reset. Lab ini tidak memakai cara itu.</li>
</ul>

<h2>Persiapan — buka tool yang benar dulu</h2>
HTML
            .$tools.$install.<<<'HTML'
<p><strong>Jangan dipakai hari ini:</strong> relay, AC 220V, Node-RED, Python, Laragon, <code>php artisan</code>, broker publik, atau mematikan router rumah. Jangan memakai <code>SD_MMC</code>.</p>
<p><strong>Tips ponsel:</strong> jika diagram terasa kecil, gunakan fitur perbesar pada browser. Gambar tidak perlu diketuk sampai memenuhi layar agar teks di sekitarnya tetap terbaca.</p>

<h2>Topologi lab — dua Wi-Fi, satu tujuan</h2>
HTML
            .$flow.$twoWifi.<<<'HTML'
<p>Kalau ESP32 dan PC memakai router yang sama, mematikan router membuat Mosquitto ikut mati. Itu bukan demo antrian.</p>
<ol>
<li><strong>PC</strong> tetap di Wi-Fi rumah (atau kabel LAN). Di situlah Mosquitto dan MQTTX.</li>
<li><strong>ESP32</strong> tersambung ke <strong>hotspot HP</strong>. SSID dan sandi hotspot itulah yang ditulis di sketch.</li>
<li>Demo: <strong>buka dulu panel HP</strong>, matikan hotspot kira-kira 20–40 detik, lalu nyalakan lagi. USB ESP32 tetap terpasang.</li>
</ol>
<p>Kadang hotspot HP tidak mengizinkan ESP32 bicara ke laptop di Wi-Fi rumah. Gejalanya: hotspot nyala, tapi MQTT tetap gagal. Jalan paling sederhana: sambungkan PC ke hotspot HP yang sama, lalu <code>ipconfig</code> lagi karena IPv4 PC akan berubah. Tempel IPv4 baru ke Mosquitto dan ke sketch.</p>

<h2>Cari IPv4 PC dan jalankan Mosquitto</h2>
HTML
            .<<<'HTML'
<p><strong>Buka dulu PowerShell:</strong> tekan Start → ketik <strong>PowerShell</strong> → pilih <strong>Windows PowerShell</strong>. Tidak perlu <em>Run as administrator</em>.</p>
<p><strong>Cara menempel perintah:</strong> salin baris di bawah, klik jendela PowerShell, lalu <kbd>Ctrl</kbd>+<kbd>V</kbd> atau klik kanan. Setelah teks muncul, tekan Enter.</p>
<pre><code>ipconfig</code></pre>
<p>Cari <strong>IPv4 Address</strong> pada adaptor Wi-Fi rumah. Contoh artikel memakai <code>192.168.1.23</code>; punyamu hampir pasti berbeda.</p>
<p>Pakai berkas <code>mosquitto-fs34.conf</code> dari FS-34 jika masih ada. Jika belum, buka <strong>Notepad</strong>, tempel dua baris, ganti alamat contoh:</p>
<pre><code>listener 1883 192.168.1.23
listener_allow_anonymous true</code></pre>
<p>Simpan di Documents sebagai <code>mosquitto-fs34.conf</code> (<em>Save as type: All files</em>), lalu di PowerShell:</p>
<pre><code>&amp; 'C:\Program Files\mosquitto\mosquitto.exe' -c "$env:USERPROFILE\Documents\mosquitto-fs34.conf" -v</code></pre>
<p><strong>Hasil yang dicari:</strong> jendela tetap terbuka dan terlihat angka <code>1883</code>. Biarkan jendela ini terbuka. Hentikan dengan <strong>Ctrl+C</strong> setelah latihan. Ini lab LAN singkat, bukan undangan ke internet. Jangan membuka port router. Rujukan: <a href="https://mosquitto.org/man/mosquitto-conf-5.html" target="_blank" rel="noopener noreferrer">dokumentasi konfigurasi Mosquitto</a>.</p>
<p><strong>macOS:</strong> buka aplikasi <strong>Terminal</strong> dulu, lalu <code>ifconfig</code> dan <code>mosquitto -c ~/Documents/mosquitto-fs34.conf -v</code>. <strong>Ubuntu atau Debian:</strong> buka <strong>Terminal</strong> dulu, lalu <code>ip addr</code> dan perintah mosquitto yang sama.</p>

<h2>Hubungkan MQTTX</h2>
<p>Pastikan jendela Mosquitto masih terbuka. Baru sekarang buka <strong>MQTTX</strong>. Jika belum terpasang, unduh dari <a href="https://mqttx.app/downloads" target="_blank" rel="noopener noreferrer">mqttx.app/downloads</a> seperti FS-32.</p>
<ol>
<li>Klik <em>New Connection</em>, nama <code>FS37 store-forward LAN</code>.</li>
<li>Host = IPv4 PC, Port = <code>1883</code>. Bukan <code>127.0.0.1</code>.</li>
<li>Connect, lalu subscribe:</li>
</ol>
<pre><code>kodingindonesia/fsiot/esp32-meja-01/telemetry</code></pre>
<p><strong>Hasil yang dicari:</strong> MQTTX berstatus tersambung. Belum ada JSON sampai ESP32 mengirim.</p>

<h2>Library Arduino IDE</h2>
<p><strong>Buka dulu Arduino IDE.</strong> Pilih papan <strong>ESP32</strong>, bukan UNO.</p>
<p><code>SD.h</code> dan <code>SPI.h</code> sudah termasuk core. <strong>Jangan</strong> memasang library SD untuk papan UNO.</p>
<p>Library <strong>ArduinoMqttClient</strong>, <strong>ArduinoJson</strong>, dan <strong>DHT sensor library</strong> (Adafruit) biasanya sudah ada dari FS-34. Jika Verify mengeluh file header hilang: di bilah kiri, klik ikon tiga buku (Library Manager). Itu satu-satunya jalur yang dipakai hari ini. Jangan memakai menu lama <em>Tools → Manage Libraries</em>. Cari nama library, papan ESP32, lalu INSTALL jika belum ada.</p>
<p>Menu <em>Tools → Serial Monitor</em> tetap untuk melihat tulisan Serial; itu <strong>bukan</strong> Library Manager. Rujukan: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-installing-a-library/" target="_blank" rel="noopener noreferrer">Installing libraries — Arduino IDE 2</a>, Arduino S.r.l., Creative Commons Attribution-Share Alike 4.0. <a href="https://github.com/arduino-libraries/ArduinoMqttClient" target="_blank" rel="noopener noreferrer">ArduinoMqttClient</a>. <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/api/sd.html" target="_blank" rel="noopener noreferrer">Espressif SD API</a>.</p>

<h2>Pasang kabel — sama seperti FS-36</h2>
HTML
            .$wiring.$kitModule.$spiPhoto.$cardPhoto.$dhtPhoto.<<<'HTML'
<p>Cabut USB ESP32 sebelum merapikan kabel. Pin SPI dikunci:</p>
<table>
<thead><tr><th>Tulisan di modul SD</th><th>GPIO ESP32</th></tr></thead>
<tbody>
<tr><td>CS / SS</td><td><strong>5</strong></td></tr>
<tr><td>SCK / CLK</td><td><strong>18</strong></td></tr>
<tr><td>MISO / DO</td><td><strong>19</strong></td></tr>
<tr><td>MOSI / DI</td><td><strong>23</strong></td></tr>
<tr><td>VCC</td><td><strong>5V</strong> atau <strong>3V3</strong> menurut label</td></tr>
<tr><td>GND</td><td>GND</td></tr>
</tbody>
</table>
<p>DHT22: <strong>VCC → 3V3</strong>, <strong>DATA atau DAT → GPIO 4</strong>, <strong>GND → GND</strong>. <strong>Jangan menebak pin.</strong> Kartu tetap FAT32. <strong>Jangan colok AC 220V.</strong></p>

<h2>Kenapa tidak menumpuk di RAM</h2>
HTML
            .$ram.$pending.<<<'HTML'
<p>Array di memori terasa mudah, lalu penuh, lalu ESP32 reset, lalu antrian hilang. Kartu lebih lambat, tetapi bertahan saat Wi-Fi putus. Satu sampel boleh ada di variabel; sisanya di <code>pending.csv</code>.</p>

<h2>Sketch ESP32 — pending.csv lalu kirim ulang</h2>
<p>Buat sketch baru bernama <code>FS37_sd_store_forward</code>. Ganti hotspot HP, sandi hotspot, dan <code>192.168.1.23</code> dengan IPv4 PC. Jangan menaruh sandi asli pada screenshot.</p>
<pre><code class="language-arduino">
HTML
            .$sketch.<<<'HTML'
</code></pre>
<p><code>SPI.begin(18, 19, 23, CS_PIN)</code> mengunci VSPI. <code>flushPending()</code> membaca <code>pending.csv</code>, mengirim paling banyak lima baris per putaran loop, lalu menulis sisa ke berkas sementara. JSON memakai <code>from_sd</code> agar MQTTX membedakan kiriman langsung dan kiriman ulang. Rujukan: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/api/sd.html" target="_blank" rel="noopener noreferrer">Espressif SD</a>, <a href="https://github.com/arduino-libraries/ArduinoMqttClient" target="_blank" rel="noopener noreferrer">ArduinoMqttClient</a>, <a href="https://arduinojson.org/v7/api/jsondocument/" target="_blank" rel="noopener noreferrer">ArduinoJson</a>, <a href="https://github.com/adafruit/DHT-sensor-library" target="_blank" rel="noopener noreferrer">Adafruit DHT</a>.</p>

<h2>Upload, lalu demo hotspot</h2>
HTML
            .$serial.$hotspot.<<<'HTML'
<ol>
<li>Verify dan Upload. Buka <strong>Tools → Serial Monitor</strong>, baud <strong>115200</strong>.</li>
<li>Cari <code>Kartu siap. Antrian di /pending.csv</code> dan <code>Antrian hanya di kartu, bukan RAM tak terbatas.</code></li>
<li>Cari <code>MQTT tersambung. Mengirim antrian kartu.</code> lalu <code>Terkirim:</code>.</li>
<li>Pastikan MQTTX menampilkan JSON <code>from_sd</code> bernilai <code>false</code>.</li>
<li><strong>Buka dulu panel atas HP</strong> (geser dari atas, atau buka Pengaturan). Ketuk Hotspot sampai mati. USB ESP32 tetap terpasang. Jangan matikan router rumah. Jangan matikan Wi-Fi laptop.</li>
<li>Serial menulis <code>Wi-Fi putus. Disimpan ke pending.csv</code>. MQTTX berhenti mendapat pesan baru.</li>
<li>Nyalakan hotspot HP. Serial menulis <code>Kirim ulang dari kartu:</code>.</li>
</ol>
HTML
            .$mqttx.<<<'HTML'
<p><strong>Berhasil berarti:</strong> MQTTX sempat sepi, lalu terisi beberapa pesan <code>from_sd: true</code> berdekatan. Angka suhu boleh berbeda; yang penting antrian kembali ke broker.</p>

<h2>Jika antrian tidak terkirim ulang</h2>
HTML
            .$troubleshooting.<<<'HTML'
<ol>
<li><strong>Router rumah dimatikan.</strong> Mosquitto ikut mati. Nyalakan router, jalankan broker lagi, ulangi demo dengan hotspot HP.</li>
<li><strong>ESP32 memakai <code>127.0.0.1</code>.</strong> Ganti IPv4 PC dari <code>ipconfig</code>.</li>
<li><strong>Kartu tidak terbaca.</strong> CS = GPIO 5, FAT32, GND bersama — seperti FS-36.</li>
<li><strong>Hotspot memblokir LAN.</strong> Sambungkan PC ke hotspot yang sama, catat IPv4 baru, perbarui conf Mosquitto dan sketch.</li>
<li><strong>MQTTX Host salah.</strong> Host = IPv4 PC, Port <code>1883</code>, topic persis seperti sketch.</li>
</ol>

<h2 id="fsiot-forward-checklist">Checklist sebelum FS-38</h2>
<p>Centang setelah kamu benar-benar melakukan setiap poin. Target: <strong>10/10</strong>. Progres disimpan di browser perangkatmu dan tidak dikirim ke server.</p>
<ul id="fsiot-forward-checklist-items">
<li>Jendela Mosquitto terbuka dan terlihat <code>1883</code>.</li>
<li>MQTTX Host = IPv4 PC, langganan topik <code>kodingindonesia/fsiot/esp32-meja-01/telemetry</code>.</li>
<li>ESP32 memakai hotspot HP; PC tetap di jaringan yang menjalankan broker.</li>
<li>Wiring CS=5, SCK=18, MISO=19, MOSI=23, DHT22 GPIO 4, GND bersama.</li>
<li>Sketch memakai <code>SD.h</code> core dan <code>pending.csv</code>, bukan array RAM tak terbatas.</li>
<li>Serial menampilkan <code>Kartu siap. Antrian di /pending.csv</code>.</li>
<li>MQTTX menampilkan JSON <code>from_sd: false</code> saat hotspot nyala.</li>
<li>Saat hotspot mati, Serial menampilkan <code>Wi-Fi putus. Disimpan ke pending.csv</code>.</li>
<li>Saat hotspot nyala lagi, Serial menampilkan <code>Kirim ulang dari kartu:</code>.</li>
<li>MQTTX terisi <code>from_sd: true</code> dan saya bisa menjelaskan kenapa RAM bukan gudang antrian.</li>
</ul>
<p><strong>Cara memeriksa kesiapan:</strong> ceritakan dengan kata-katamu: hotspot mati → kartu → hotspot nyala → MQTTX. Pada FS-38, aturan if-then pindah ke PC tanpa upload firmware tiap kali.</p>

<h2>Kesalahan yang sering terjadi</h2>
<ul>
<li><strong>Mematikan router rumah.</strong> Broker ikut mati. Matikan hotspot HP.</li>
<li><strong>Menumpuk array di RAM.</strong> Reset = hilang. Pakai <code>pending.csv</code>.</li>
<li><strong>ESP32 dan MQTTX di <code>127.0.0.1</code>.</strong> Ganti IPv4 PC.</li>
<li><strong>Mengira adapter SD plastik = modul SPI.</strong></li>
<li><strong>Membuka MQTTX sebelum Mosquitto.</strong></li>
<li><strong>Menghidupkan relay atau AC 220V.</strong> Tidak dipakai hari ini.</li>
</ul>

<h2>Pertanyaan yang sering muncul</h2>
<h3>Kenapa tidak mematikan Wi-Fi di laptop saja?</h3>
<p>Laptop menjalankan Mosquitto. Jika Wi-Fi PC mati, broker dan MQTTX ikut gelap. Yang diuji adalah jalur ESP32.</p>
<h3>Berapa lama hotspot boleh mati?</h3>
<p>20–40 detik cukup untuk beberapa baris. Jangan berjam-jam pada lab pertama.</p>
<h3>Apakah pending.csv harus dibuka di PC?</h3>
<p>Tidak wajib untuk lulus. Serial dan MQTTX sudah cukup. Membuka kartu di File Explorer boleh setelah USB dicabut, seperti FS-36.</p>
<h3>Kenapa JSON memakai from_sd?</h3>
<p>Agar kamu melihat bedanya: langsung dari sensor, atau antrian dari kartu. Broker yang sama, asal berbeda.</p>
<h3>Kenapa relay tidak dipakai?</h3>
<p>Hari ini tugasnya satu: jangan kehilangan sampel saat hotspot putus. Perintah relay tetap di FS-35.</p>
<h3>Apa itu store-and-forward?</h3>
<p>Simpan dulu, kirim kemudian. Istilah jaringan lama, dipakai di sini untuk kartu dan MQTT.</p>

<h2>Sumber</h2>
<ul>
<li><a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/api/sd.html" target="_blank" rel="noopener noreferrer">Espressif — SD (Arduino-ESP32)</a></li>
<li><a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/api/wifi.html" target="_blank" rel="noopener noreferrer">Espressif — Wi-Fi API</a></li>
<li><a href="https://github.com/arduino-libraries/ArduinoMqttClient" target="_blank" rel="noopener noreferrer">ArduinoMqttClient</a></li>
<li><a href="https://arduinojson.org/v7/api/jsondocument/" target="_blank" rel="noopener noreferrer">ArduinoJson JsonDocument</a></li>
<li><a href="https://github.com/adafruit/DHT-sensor-library" target="_blank" rel="noopener noreferrer">Adafruit DHT sensor library</a></li>
<li><a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-installing-a-library/" target="_blank" rel="noopener noreferrer">Arduino — Installing libraries (IDE 2)</a>. Creative Commons Attribution-Share Alike 4.0. Arduino adalah merek Arduino S.r.l.</li>
<li><a href="https://mosquitto.org/man/mosquitto-conf-5.html" target="_blank" rel="noopener noreferrer">Mosquitto — konfigurasi listener</a></li>
<li><a href="https://mqttx.app/" target="_blank" rel="noopener noreferrer">MQTTX oleh EMQ</a> (Apache License 2.0)</li>
<li><a href="https://commons.wikimedia.org/wiki/File:SD_Card_Breakout_Board.jpg" target="_blank" rel="noopener noreferrer">SD Card Breakout Board</a> · oomlout · Wikimedia Commons · Creative Commons Attribution-Share Alike 2.0. Jangan menyalin urutan kaki dari foto.</li>
<li><a href="https://commons.wikimedia.org/wiki/File:2015_Karta_microSD_z_adapterem_SD.jpg" target="_blank" rel="noopener noreferrer">2015 Karta microSD z adapterem SD</a> · Jacek Halicki · Wikimedia Commons · Creative Commons Attribution-Share Alike 4.0. Foto hanya contoh rupa; adapter bukan modul SPI.</li>
<li><a href="https://commons.wikimedia.org/wiki/File:DHT_22_Sensor.jpg" target="_blank" rel="noopener noreferrer">AM2302 DHT22 Sensor</a> · L293D · Wikimedia Commons · Creative Commons Attribution-Share Alike 4.0. Jangan menyalin urutan kaki dari foto.</li>
<li>Diagram urutan tools, alur putus-nyambung, dua Wi-Fi, demo hotspot, RAM lawan kartu, berkas pending, Serial Monitor, MQTTX, dan skema periksa — Koding Indonesia (FS-37). Wiring SPI dan ilustrasi kit enam pin dari FS-36.</li>
</ul>

<h2>Selanjutnya</h2>
<p><strong>Ringkasnya:</strong> hotspot putus tidak lagi menghapus seluruh jendela waktu. Pada <strong>FS-38</strong>, aturan if-then pindah ke PC (Node-RED atau checklist MQTTX) tanpa upload firmware tiap kali mengubah ambang.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $sketch = htmlspecialchars($this->sketch(), ENT_QUOTES, 'UTF-8');
        $tools = $this->figure('fs37-tools-order.png', 'Five-step order: browser, MQTTX and broker, Arduino IDE, SPI cables, then Serial Monitor', '<strong>Desk order (five steps):</strong> browser → MQTTX + Mosquitto (Host = PC IPv4) → Arduino IDE → SPI cables as in FS-36 → Serial Monitor, then turn off the phone hotspot. Diagram by Koding Indonesia (FS-37).');
        $flow = $this->figure('fs37-offline-online.png', 'Left-to-right flow: hotspot off, pending.csv, hotspot on, MQTTX fills with from_sd true', '<strong>Main figure — flow.</strong> Read left to right: disconnect → store in <code>pending.csv</code> → reconnect → MQTTX fills. Diagram by Koding Indonesia (FS-37).');
        $ram = $this->figure('fs37-ram-vs-sd.png', 'Comparison of a RAM queue that fills quickly versus a pending.csv queue', '<strong>RAM is small. The card holds the queue.</strong> This lab does not pile samples in memory. Diagram by Koding Indonesia (FS-37).');
        $pending = $this->figure('fs37-pending-csv.png', 'Two files: pending.csv for unsent rows and log.csv as the archive', '<strong>Two files.</strong> <code>pending.csv</code> holds rows not yet on the broker. <code>log.csv</code> archives every sample. Diagram by Koding Indonesia (FS-37).');
        $serial = $this->figure('fs37-serial-monitor.png', 'Serial Monitor illustration showing Kartu siap, Wi-Fi putus, and Kirim ulang dari kartu', '<strong>Open Tools → Serial Monitor, baud 115200.</strong> Look for <code>Kartu siap. Antrian di /pending.csv</code>, then <code>Wi-Fi putus. Disimpan ke pending.csv</code> when the hotspot is off, then <code>Kirim ulang dari kartu:</code>. Illustration by Koding Indonesia (FS-37), modelled on Arduino IDE 2. The official window screenshot is not used as-is. Menu reference: Arduino IDE 2 docs, Arduino S.r.l., Creative Commons Attribution-Share Alike 4.0.');
        $mqttx = $this->figure('fs37-mqttx-backfill.png', 'MQTTX illustration showing live from_sd false then backfill from_sd true', '<strong>MQTTX is already showing the card backfill.</strong> Host = PC IPv4, Port = <code>1883</code>. <code>from_sd: true</code> messages arrive close together after the hotspot returns. Illustration by Koding Indonesia (FS-37), modelled on <a href="https://mqttx.app/" target="_blank" rel="noopener noreferrer">MQTTX by EMQ</a> (Apache License 2.0). The official window screenshot is not used as-is because it often shows a public broker. <code>192.168.1.23</code> is only an example.');
        $troubleshooting = $this->figure('fs37-troubleshooting.png', 'Four checks when the microSD queue is not resent to MQTTX', '<strong>Helper schematic.</strong> Check the phone hotspot, the Mosquitto window, pending.csv, then MQTTX. Diagram by Koding Indonesia (FS-37).');
        $twoWifi = $this->figure('fs37-two-wifi.png', 'PC stays on home Wi-Fi running Mosquitto, ESP32 uses the phone hotspot, MQTT_HOST is the PC IPv4', '<strong>Two Wi-Fi paths, one goal.</strong> The PC + Mosquitto stay on home Wi-Fi. The ESP32 uses the phone hotspot. <code>MQTT_HOST</code> = the PC IPv4 from <code>ipconfig</code>, not <code>127.0.0.1</code>. Diagram by Koding Indonesia (FS-37).');
        $hotspot = $this->figure('fs37-hotspot-demo.png', 'Demo order: open the phone panel, turn the hotspot off, leave the PC and Mosquitto running, then turn the hotspot on', '<strong>Open the phone panel first.</strong> Swipe down (or open Settings), tap Hotspot off for 20–40 seconds, then turn it back on. Leave ESP32 USB plugged in. Diagram by Koding Indonesia (FS-37).');
        $wiring = $this->figure('fs36-wiring-spi.png', 'microSD SPI and DHT22 wiring to ESP32, same as FS-36, including SD module GND', '<strong>Wiring today is the same as FS-36.</strong> CS → GPIO 5, SCK → GPIO 18, MISO → GPIO 19, MOSI → GPIO 23, DHT22 DATA → GPIO 4, and <strong>the SD module GND also goes to ESP32 GND</strong>. Do not connect AC mains. Diagram by Koding Indonesia (FS-36), reused in FS-37 because the pins did not change.');
        $kitModule = $this->figure('fs36-modul-kit.png', 'Illustration of a typical six-pin kit microSD SPI module labelled CS SCK MOSI MISO VCC GND', '<strong>Recognise the kit module first.</strong> Shops often sell a blue six-pin board like this. Read the print: CS, SCK, MOSI, MISO, VCC, GND. The physical order on your board may differ. Illustration by Koding Indonesia (FS-36).');
        $spiPhoto = $this->figure('kit-microsd-spi.jpg', 'Example Adafruit eight-pin microSD SPI breakout, not a six-pin kit module', '<strong>Another shape, eight pins.</strong> This is an Adafruit breakout (extra CD, 3V, and 5V pads). Shop kits often have only six pins, as in the illustration above. The bus is still SPI. <strong>Do not copy pin order from the photo.</strong> Wiring follows the printed labels and the GPIO table. Source: <a href="https://commons.wikimedia.org/wiki/File:SD_Card_Breakout_Board.jpg" target="_blank" rel="noopener noreferrer">SD Card Breakout Board</a> · oomlout · Wikimedia Commons · Creative Commons Attribution-Share Alike 2.0.');
        $cardPhoto = $this->figure('kit-microsd-card.jpg', 'Example microSD card and plastic SD adapter for a laptop slot', '<strong>Appearance example only.</strong> The plastic adapter is for a laptop slot, <strong>not</strong> an SPI module. <strong>Do not wire it to ESP32 pins.</strong> <strong>Do not copy pin order from the photo.</strong> Source: <a href="https://commons.wikimedia.org/wiki/File:2015_Karta_microSD_z_adapterem_SD.jpg" target="_blank" rel="noopener noreferrer">2015 Karta microSD z adapterem SD</a> · Jacek Halicki · Wikimedia Commons · Creative Commons Attribution-Share Alike 4.0.');
        $dhtPhoto = $this->figure('kit-dht22.jpg', 'Example DHT22 AM2302 module with DAT, VCC, and GND pins', '<strong>Sensor appearance only.</strong> <strong>Do not copy pin order from the photo.</strong> Wiring is still VCC → 3V3, DATA or DAT → GPIO 4, GND → GND. Source: <a href="https://commons.wikimedia.org/wiki/File:DHT_22_Sensor.jpg" target="_blank" rel="noopener noreferrer">AM2302 DHT22 Sensor</a> · L293D · Wikimedia Commons · Creative Commons Attribution-Share Alike 4.0.');
        $install = $this->stepsCard([
            ['title' => 'Open a browser', 'text' => 'Use Chrome, Firefox, Edge, or Safari. Keep this guide ready. Do not Upload a sketch yet.'],
            ['title' => 'Open MQTTX after the broker is running', 'text' => 'Mosquitto first, as in FS-34: keep the window open and look for <code>1883</code>. Only then MQTTX. Host = <strong>PC IPv4</strong> (not <code>127.0.0.1</code>), Port <code>1883</code>, subscribe <code>kodingindonesia/fsiot/esp32-meja-01/telemetry</code>.'],
            ['title' => 'Open Arduino IDE', 'text' => '<code>SD.h</code> already ships in the ESP32 core. ArduinoMqttClient, ArduinoJson, and DHT are usually already there from FS-34. If Verify complains, click the three-book icon in the left bar. That is the only path used today.'],
            ['title' => 'Check the SPI and DHT22 wiring', 'text' => 'Same as FS-36. Unplug USB first. CS → GPIO 5, SCK → GPIO 18, MISO → GPIO 19, MOSI → GPIO 23, DHT22 DATA → GPIO 4. Do not connect AC mains. The GPIO 26 relay is <strong>not</strong> used today.'],
            ['title' => 'Upload, Serial Monitor, then open the phone panel', 'text' => 'Tools → Serial Monitor, baud 115200. After <code>Terkirim:</code> appears in MQTTX, <strong>open the phone panel first</strong> (swipe down, or open Settings). Tap Hotspot off — not the home router, not laptop Wi-Fi. Turn it back on and look for <code>Kirim ulang dari kartu:</code>.'],
        ], '<strong>How to test today:</strong> success = MQTTX goes quiet while the hotspot is off, then fills with <code>from_sd: true</code> after the hotspot returns, and Serial prints <code>Kirim ulang dari kartu:</code>.');

        return <<<'HTML'
<h2>Introduction — a Wi-Fi drop must not wipe the time window</h2>
<p><strong>FS-37 / #107 (this article)</strong> joins FS-36 (the card) and FS-34 (MQTT). Yesterday the card stored <code>log.csv</code> to read on a PC. Today the card is a <strong>queue</strong>: data waits, then goes to Mosquitto again when the phone hotspot returns.</p>
<p><strong>In short:</strong> the ESP32 does not pile hundreds of numbers in RAM. Unsent rows go into <code>pending.csv</code>. When MQTT is up, those rows are resent. MQTTX fills, and the demo time window is not a total gap.</p>
<p><strong>Analogy:</strong> FS-34 is a courier who delivers at once. FS-36 is a desk drawer. FS-37 is the drawer emptied at the post office as soon as the road reopens.</p>
<p>Lab prerequisites: Mosquitto + MQTTX from FS-33/FS-34, a FAT32 card and SPI wiring from FS-36, DHT22 on GPIO 4. The GPIO 26 relay is <strong>not</strong> used today. Node-RED and Python wait for FS-38 and FS-39.</p>

<h2>Expected outcome</h2>
<ul>
<li>While the phone hotspot is on, MQTTX gets JSON about every five seconds, <code>from_sd: false</code>.</li>
<li>When the hotspot is off, Serial prints <code>Wi-Fi putus. Disimpan ke pending.csv</code>. MQTTX goes quiet. Mosquitto on the PC stays up.</li>
<li>When the hotspot is on again, Serial prints <code>Kirim ulang dari kartu:</code> and MQTTX fills with <code>from_sd: true</code>.</li>
<li>You can explain why the queue must not be unbounded in RAM.</li>
</ul>
<p><strong>Lab limits today:</strong> no web dashboard, public broker, port forwarding, AC mains, or <code>SD_MMC</code>. Proof = Serial + MQTTX.</p>

<h2>Terms used today</h2>
<ul>
<li><strong>Store-and-forward</strong> — store first, send later. Do not drop a sample only because Wi-Fi is down.</li>
<li><strong>Queue</strong> — rows waiting for the broker. In this lab they live in <code>pending.csv</code>.</li>
<li><strong><code>pending.csv</code></strong> — the card file for data not yet sent.</li>
<li><strong><code>log.csv</code></strong> — archive of every sample; last column <code>mqtt</code> or <code>kartu</code>.</li>
<li><strong><code>from_sd</code></strong> — JSON field. <code>false</code> = live from the sensor. <code>true</code> = resent from the card.</li>
<li><strong>Phone hotspot</strong> — the Wi-Fi the ESP32 uses today. The PC stays on home Wi-Fi so Mosquitto does not die with the demo.</li>
<li><strong>Unbounded RAM</strong> — the beginner trap: grow an array until the ESP32 stalls or resets. This lab does not do that.</li>
</ul>

<h2>Preparation — open the right tools first</h2>
HTML
            .$tools.$install.<<<'HTML'
<p><strong>Not used today:</strong> relay, AC mains, Node-RED, Python, Laragon, <code>php artisan</code>, a public broker, or turning off the home router. Do not use <code>SD_MMC</code>.</p>
<p><strong>Phone tip:</strong> if a diagram feels small, use the browser zoom. You do not need to tap the image until it fills the screen; nearby text should stay readable.</p>

<h2>Lab topology — two Wi-Fi paths, one goal</h2>
HTML
            .$flow.$twoWifi.<<<'HTML'
<p>If the ESP32 and the PC share one router, turning that router off also kills Mosquitto. That is not a queue demo.</p>
<ol>
<li>The <strong>PC</strong> stays on home Wi-Fi (or Ethernet). That is where Mosquitto and MQTTX run.</li>
<li>The <strong>ESP32</strong> joins the <strong>phone hotspot</strong>. That SSID and password go in the sketch.</li>
<li>Demo: <strong>open the phone panel first</strong>, turn the hotspot off for about 20–40 seconds, then turn it back on. Leave ESP32 USB plugged in.</li>
</ol>
<p>Some phone hotspots will not let the ESP32 talk to a laptop that is still on home Wi-Fi. The symptom: the hotspot is on, but MQTT still fails. The simple fix: put the PC on the same hotspot, then run <code>ipconfig</code> again because the PC IPv4 will change. Paste the new IPv4 into Mosquitto and the sketch.</p>

<h2>Find the PC IPv4 and start Mosquitto</h2>
HTML
            .<<<'HTML'
<p><strong>Open PowerShell first:</strong> Start → type <strong>PowerShell</strong> → choose <strong>Windows PowerShell</strong>. You do not need <em>Run as administrator</em>.</p>
<p><strong>How to paste:</strong> copy the line below, click the PowerShell window, then <kbd>Ctrl</kbd>+<kbd>V</kbd> or right-click. When the text appears, press Enter.</p>
<pre><code>ipconfig</code></pre>
<p>Find <strong>IPv4 Address</strong> on the home Wi-Fi adapter. The article example is <code>192.168.1.23</code>; yours will almost certainly differ.</p>
<p>Reuse <code>mosquitto-fs34.conf</code> from FS-34 if you still have it. If not, open <strong>Notepad</strong>, paste two lines, and replace the example address:</p>
<pre><code>listener 1883 192.168.1.23
listener_allow_anonymous true</code></pre>
<p>Save it in Documents as <code>mosquitto-fs34.conf</code> (<em>Save as type: All files</em>), then in PowerShell:</p>
<pre><code>&amp; 'C:\Program Files\mosquitto\mosquitto.exe' -c "$env:USERPROFILE\Documents\mosquitto-fs34.conf" -v</code></pre>
<p><strong>What to look for:</strong> the window stays open and shows <code>1883</code>. Keep it open. Stop with <strong>Ctrl+C</strong> after the lab. This is a short LAN lab, not an invitation to the internet. Do not open a router port. Reference: <a href="https://mosquitto.org/man/mosquitto-conf-5.html" target="_blank" rel="noopener noreferrer">Mosquitto configuration docs</a>.</p>
<p><strong>macOS:</strong> open the <strong>Terminal</strong> app first, then <code>ifconfig</code> and <code>mosquitto -c ~/Documents/mosquitto-fs34.conf -v</code>. <strong>Ubuntu or Debian:</strong> open <strong>Terminal</strong> first, then <code>ip addr</code> and the same mosquitto command.</p>

<h2>Connect MQTTX</h2>
<p>Keep the Mosquitto window open. Only now open <strong>MQTTX</strong>. If it is not installed, download it from <a href="https://mqttx.app/downloads" target="_blank" rel="noopener noreferrer">mqttx.app/downloads</a> as in FS-32.</p>
<ol>
<li>Click <em>New Connection</em>, name <code>FS37 store-forward LAN</code>.</li>
<li>Host = PC IPv4, Port = <code>1883</code>. Not <code>127.0.0.1</code>.</li>
<li>Connect, then subscribe:</li>
</ol>
<pre><code>kodingindonesia/fsiot/esp32-meja-01/telemetry</code></pre>
<p><strong>What to look for:</strong> MQTTX shows connected. There is no JSON yet until the ESP32 sends.</p>

<h2>Arduino IDE libraries</h2>
<p><strong>Open Arduino IDE first.</strong> Choose an <strong>ESP32</strong> board, not an UNO.</p>
<p><code>SD.h</code> and <code>SPI.h</code> already ship in the core. <strong>Do not</strong> install an SD library for an UNO board.</p>
<p><strong>ArduinoMqttClient</strong>, <strong>ArduinoJson</strong>, and Adafruit <strong>DHT sensor library</strong> are usually already there from FS-34. If Verify complains about a missing header: click the three-book icon (Library Manager) in the left bar. That is the only path used today. Do not use the old <em>Tools → Manage Libraries</em> menu. Search the library name, ESP32 board, then INSTALL if needed.</p>
<p><em>Tools → Serial Monitor</em> is still for Serial text; it is <strong>not</strong> Library Manager. References: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-installing-a-library/" target="_blank" rel="noopener noreferrer">Installing libraries — Arduino IDE 2</a>, Arduino S.r.l., Creative Commons Attribution-Share Alike 4.0. <a href="https://github.com/arduino-libraries/ArduinoMqttClient" target="_blank" rel="noopener noreferrer">ArduinoMqttClient</a>. <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/api/sd.html" target="_blank" rel="noopener noreferrer">Espressif SD API</a>.</p>

<h2>Wire the cables — same as FS-36</h2>
HTML
            .$wiring.$kitModule.$spiPhoto.$cardPhoto.$dhtPhoto.<<<'HTML'
<p>Unplug ESP32 USB before tidying cables. SPI pins stay locked:</p>
<table>
<thead><tr><th>Label on the SD module</th><th>ESP32 GPIO</th></tr></thead>
<tbody>
<tr><td>CS / SS</td><td><strong>5</strong></td></tr>
<tr><td>SCK / CLK</td><td><strong>18</strong></td></tr>
<tr><td>MISO / DO</td><td><strong>19</strong></td></tr>
<tr><td>MOSI / DI</td><td><strong>23</strong></td></tr>
<tr><td>VCC</td><td><strong>5V</strong> or <strong>3V3</strong> by the printed label</td></tr>
<tr><td>GND</td><td>GND</td></tr>
</tbody>
</table>
<p>DHT22: <strong>VCC → 3V3</strong>, <strong>DATA or DAT → GPIO 4</strong>, <strong>GND → GND</strong>. <strong>Do not guess pins.</strong> Keep the card FAT32. <strong>Do not connect AC mains.</strong></p>

<h2>Why not pile the queue in RAM</h2>
HTML
            .$ram.$pending.<<<'HTML'
<p>A memory array feels easy, then fills, then the ESP32 resets, then the queue is gone. The card is slower, but it survives a Wi-Fi drop. One sample may sit in a variable; the rest live in <code>pending.csv</code>.</p>

<h2>ESP32 sketch — pending.csv then resend</h2>
<p>Create a new sketch named <code>FS37_sd_store_forward</code>. Replace the phone hotspot, hotspot password, and <code>192.168.1.23</code> with your PC IPv4. Do not put a real password in a screenshot.</p>
<pre><code class="language-arduino">
HTML
            .$sketch.<<<'HTML'
</code></pre>
<p><code>SPI.begin(18, 19, 23, CS_PIN)</code> locks VSPI. <code>flushPending()</code> reads <code>pending.csv</code>, sends at most five rows per loop, then writes leftovers to a temp file. JSON uses <code>from_sd</code> so MQTTX can tell live rows from backfill. References: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/api/sd.html" target="_blank" rel="noopener noreferrer">Espressif SD</a>, <a href="https://github.com/arduino-libraries/ArduinoMqttClient" target="_blank" rel="noopener noreferrer">ArduinoMqttClient</a>, <a href="https://arduinojson.org/v7/api/jsondocument/" target="_blank" rel="noopener noreferrer">ArduinoJson</a>, <a href="https://github.com/adafruit/DHT-sensor-library" target="_blank" rel="noopener noreferrer">Adafruit DHT</a>.</p>

<h2>Upload, then run the hotspot demo</h2>
HTML
            .$serial.$hotspot.<<<'HTML'
<ol>
<li>Verify and Upload. Open <strong>Tools → Serial Monitor</strong>, baud <strong>115200</strong>.</li>
<li>Look for <code>Kartu siap. Antrian di /pending.csv</code> and <code>Antrian hanya di kartu, bukan RAM tak terbatas.</code></li>
<li>Look for <code>MQTT tersambung. Mengirim antrian kartu.</code> then <code>Terkirim:</code>.</li>
<li>Confirm MQTTX shows JSON with <code>from_sd</code> equal to <code>false</code>.</li>
<li><strong>Open the phone panel first</strong> (swipe down, or open Settings). Tap Hotspot off. Leave ESP32 USB plugged in. Do not turn off the home router. Do not turn off laptop Wi-Fi.</li>
<li>Serial prints <code>Wi-Fi putus. Disimpan ke pending.csv</code>. MQTTX stops getting new messages.</li>
<li>Turn the phone hotspot on. Serial prints <code>Kirim ulang dari kartu:</code>.</li>
</ol>
HTML
            .$mqttx.<<<'HTML'
<p><strong>Success means:</strong> MQTTX went quiet, then filled with several <code>from_sd: true</code> messages close together. Temperature numbers may differ; the queue must return to the broker.</p>

<h2>If the queue is not resent</h2>
HTML
            .$troubleshooting.<<<'HTML'
<ol>
<li><strong>The home router was turned off.</strong> Mosquitto died too. Turn the router on, start the broker, and demo with the phone hotspot.</li>
<li><strong>The ESP32 uses <code>127.0.0.1</code>.</strong> Replace it with the PC IPv4 from <code>ipconfig</code>.</li>
<li><strong>The card does not mount.</strong> CS = GPIO 5, FAT32, shared GND — as in FS-36.</li>
<li><strong>The hotspot blocks LAN.</strong> Put the PC on the same hotspot, note the new IPv4, and update the Mosquitto conf and sketch.</li>
<li><strong>MQTTX Host is wrong.</strong> Host = PC IPv4, Port <code>1883</code>, topic exactly as in the sketch.</li>
</ol>

<h2 id="fsiot-forward-checklist">Checklist before FS-38</h2>
<p>Tick each item after you actually did it. Target: <strong>10/10</strong>. Progress stays in this browser and is not sent to the server.</p>
<ul id="fsiot-forward-checklist-items">
<li>The Mosquitto window is open and shows <code>1883</code>.</li>
<li>MQTTX Host = PC IPv4, subscribed to the telemetry topic.</li>
<li>The ESP32 uses the phone hotspot; the PC stays on the network that runs the broker.</li>
<li>Wiring CS=5, SCK=18, MISO=19, MOSI=23, DHT22 GPIO 4, shared GND.</li>
<li>The sketch uses core <code>SD.h</code> and <code>pending.csv</code>, not an unbounded RAM array.</li>
<li>Serial shows <code>Kartu siap. Antrian di /pending.csv</code>.</li>
<li>MQTTX shows JSON <code>from_sd: false</code> while the hotspot is on.</li>
<li>When the hotspot is off, Serial shows <code>Wi-Fi putus. Disimpan ke pending.csv</code>.</li>
<li>When the hotspot is on again, Serial shows <code>Kirim ulang dari kartu:</code>.</li>
<li>MQTTX fills with <code>from_sd: true</code> and I can explain why RAM is not the queue warehouse.</li>
</ul>
<p><strong>How to check readiness:</strong> tell the story in your own words: hotspot off → card → hotspot on → MQTTX. In FS-38, if-then rules move to the PC without a firmware upload every time the threshold changes.</p>

<h2>Common mistakes</h2>
<ul>
<li><strong>Turning off the home router.</strong> The broker dies too. Turn off the phone hotspot.</li>
<li><strong>Piling an array in RAM.</strong> A reset wipes it. Use <code>pending.csv</code>.</li>
<li><strong>ESP32 and MQTTX on <code>127.0.0.1</code>.</strong> Use the PC IPv4.</li>
<li><strong>Treating the plastic SD adapter as an SPI module.</strong></li>
<li><strong>Opening MQTTX before Mosquitto.</strong></li>
<li><strong>Powering a relay or AC mains.</strong> Not used today.</li>
</ul>

<h2>Frequently asked questions</h2>
<h3>Why not turn off Wi-Fi on the laptop?</h3>
<p>The laptop runs Mosquitto. If PC Wi-Fi dies, the broker and MQTTX go dark. The path under test is the ESP32.</p>
<h3>How long may the hotspot stay off?</h3>
<p>20–40 seconds is enough for a few rows. Do not wait hours on the first lab.</p>
<h3>Must I open pending.csv on the PC?</h3>
<p>Not required to pass. Serial and MQTTX are enough. Opening the card in File Explorer is optional after unplugging USB, as in FS-36.</p>
<h3>Why does JSON use from_sd?</h3>
<p>So you can see the difference: live from the sensor, or a queue from the card. Same broker, different origin.</p>
<h3>Why is the relay unused?</h3>
<p>Today has one job: do not lose samples when the hotspot drops. Relay commands stay in FS-35.</p>
<h3>What is store-and-forward?</h3>
<p>Store first, send later. An old networking term, used here for the card and MQTT.</p>

<h2>Sources</h2>
<ul>
<li><a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/api/sd.html" target="_blank" rel="noopener noreferrer">Espressif — SD (Arduino-ESP32)</a></li>
<li><a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/api/wifi.html" target="_blank" rel="noopener noreferrer">Espressif — Wi-Fi API</a></li>
<li><a href="https://github.com/arduino-libraries/ArduinoMqttClient" target="_blank" rel="noopener noreferrer">ArduinoMqttClient</a></li>
<li><a href="https://arduinojson.org/v7/api/jsondocument/" target="_blank" rel="noopener noreferrer">ArduinoJson JsonDocument</a></li>
<li><a href="https://github.com/adafruit/DHT-sensor-library" target="_blank" rel="noopener noreferrer">Adafruit DHT sensor library</a></li>
<li><a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-installing-a-library/" target="_blank" rel="noopener noreferrer">Arduino — Installing libraries (IDE 2)</a>. Creative Commons Attribution-Share Alike 4.0. Arduino is a trademark of Arduino S.r.l.</li>
<li><a href="https://mosquitto.org/man/mosquitto-conf-5.html" target="_blank" rel="noopener noreferrer">Mosquitto — listener configuration</a></li>
<li><a href="https://mqttx.app/" target="_blank" rel="noopener noreferrer">MQTTX by EMQ</a> (Apache License 2.0)</li>
<li><a href="https://commons.wikimedia.org/wiki/File:SD_Card_Breakout_Board.jpg" target="_blank" rel="noopener noreferrer">SD Card Breakout Board</a> · oomlout · Wikimedia Commons · Creative Commons Attribution-Share Alike 2.0. Do not copy pin order from the photo.</li>
<li><a href="https://commons.wikimedia.org/wiki/File:2015_Karta_microSD_z_adapterem_SD.jpg" target="_blank" rel="noopener noreferrer">2015 Karta microSD z adapterem SD</a> · Jacek Halicki · Wikimedia Commons · Creative Commons Attribution-Share Alike 4.0. Appearance only; the adapter is not an SPI module.</li>
<li><a href="https://commons.wikimedia.org/wiki/File:DHT_22_Sensor.jpg" target="_blank" rel="noopener noreferrer">AM2302 DHT22 Sensor</a> · L293D · Wikimedia Commons · Creative Commons Attribution-Share Alike 4.0. Do not copy pin order from the photo.</li>
<li>Tool-order, disconnect-reconnect, two Wi-Fi paths, hotspot demo, RAM versus card, pending files, Serial Monitor, MQTTX, and troubleshooting diagrams — Koding Indonesia (FS-37). SPI wiring and the six-pin kit illustration from FS-36.</li>
</ul>

<h2>Next</h2>
<p><strong>In short:</strong> a hotspot drop no longer wipes the whole time window. In <strong>FS-38</strong>, if-then rules move to the PC (Node-RED or an MQTTX checklist) without a firmware upload every time the threshold changes.</p>
HTML;
    }
}
