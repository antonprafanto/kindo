<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class Article106Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@kodingindonesia.com')->first() ?? User::first();
        $category = Category::where('slug', 'iot-smart-device')->first() ?? Category::where('slug', 'esp32-arduino')->first();
        if (! $admin || ! $category) {
            throw new \RuntimeException('User atau kategori IoT tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-esp32-microsd-log-csv';
        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        foreach (['fullstack-iot', 'iot', 'esp32', 'microsd', 'spi', 'csv'] as $tagSlug) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagSlug]);
        }

        $article = Article::updateOrCreate(['slug' => $slug], [
            'user_id' => $admin->id,
            'category_id' => $category->id,
            'title' => 'ESP32 simpan suhu ke microSD sebagai CSV',
            'title_en' => 'ESP32 stores temperature on microSD as CSV',
            'excerpt' => 'FS-36 / #106: format FAT32, wiring SPI GPIO 5/18/19/23, tulis log.csv, bandingkan millis dan NTP. Hari ini tidak MQTT.',
            'excerpt_en' => 'FS-36 / #106: format FAT32, SPI wiring on GPIO 5/18/19/23, write log.csv, compare millis and NTP. No MQTT today.',
            'body' => $this->body(),
            'body_en' => $this->bodyEn(),
            'status' => 'draft',
            'is_featured' => false,
            'published_at' => null,
            'seo_title' => 'ESP32 Simpan Suhu ke microSD sebagai CSV — FS-36',
            'seo_title_en' => 'ESP32 Stores Temperature on microSD as CSV — FS-36',
            'seo_description' => 'Panduan pemula menulis log.csv di kartu microSD ESP32: FAT32, pin SPI dikunci, millis lalu NTP, tanpa MQTT.',
            'seo_description_en' => 'A first lab writing log.csv on an ESP32 microSD card: FAT32, locked SPI pins, millis then NTP, no MQTT.',
        ]);
        $article->tags()->sync(Tag::whereIn('slug', ['fullstack-iot', 'iot', 'esp32', 'microsd', 'spi', 'csv'])->pluck('id'));

        foreach (['webp', 'jpg'] as $extension) {
            $source = public_path("images/fsiot/fs36-cover-sd.{$extension}");
            if (is_file($source)) {
                $destination = "articles/covers/fs36-cover-sd.{$extension}";
                Storage::disk('public')->put($destination, file_get_contents($source));
            }
        }
        $article->update([
            'cover_image' => 'https://kodingindonesia.com/images/fsiot/fs36-cover-sd.webp',
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
            '#include <DHT.h>',
            '#include <WiFi.h>',
            '#include <time.h>',
            '',
            'const byte CS_PIN = 5;',
            'const byte DHT_PIN = 4;',
            'const bool PAKAI_NTP = false;  // true = jam dinding WIB setelah Wi-Fi',
            '',
            'const char WIFI_SSID[] = "GANTI_NAMA_WIFI";',
            'const char WIFI_PASSWORD[] = "GANTI_SANDI_WIFI";',
            '',
            'const unsigned long LOG_INTERVAL_MS = 5000UL;',
            'const char LOG_PATH[] = "/log.csv";',
            '',
            'DHT dht(DHT_PIN, DHT22);',
            'unsigned long lastLogAt = 0;',
            'bool kartuSiap = false;',
            '',
            'void tulisHeaderJikaPerlu() {',
            '  if (SD.exists(LOG_PATH)) {',
            '    return;',
            '  }',
            '  File file = SD.open(LOG_PATH, FILE_WRITE);',
            '  if (!file) {',
            '    Serial.println("Tidak bisa membuat log.csv.");',
            '    return;',
            '  }',
            '  if (PAKAI_NTP) {',
            '    file.println("waktu_wib,temperature_c");',
            '  } else {',
            '    file.println("timestamp_ms,temperature_c");',
            '  }',
            '  file.close();',
            '}',
            '',
            'bool tungguNtp(unsigned long timeoutMs) {',
            '  configTime(7 * 3600, 0, "pool.ntp.org", "time.nist.gov");',
            '  unsigned long started = millis();',
            '  while (millis() - started < timeoutMs) {',
            '    time_t now = time(nullptr);',
            '    if (now > 1600000000) {',
            '      return true;',
            '    }',
            '    delay(500);',
            '  }',
            '  return false;',
            '}',
            '',
            'void setup() {',
            '  Serial.begin(115200);',
            '  delay(1000);',
            '  dht.begin();',
            '',
            '  SPI.begin(18, 19, 23, CS_PIN);',
            '  if (!SD.begin(CS_PIN)) {',
            '    Serial.println("Kartu tidak terbaca. Periksa CS=GPIO 5, format FAT32, dan GND bersama.");',
            '    return;',
            '  }',
            '  kartuSiap = true;',
            '  Serial.println("Kartu siap. Menulis /log.csv");',
            '',
            '  if (PAKAI_NTP) {',
            '    Serial.println("Menyambung Wi-Fi untuk jam internet (NTP).");',
            '    WiFi.mode(WIFI_STA);',
            '    WiFi.begin(WIFI_SSID, WIFI_PASSWORD);',
            '    unsigned long started = millis();',
            '    while (WiFi.status() != WL_CONNECTED && millis() - started < 20000UL) {',
            '      delay(400);',
            '      Serial.print(".");',
            '    }',
            '    Serial.println();',
            '    if (WiFi.status() != WL_CONNECTED) {',
            '      Serial.println("Wi-Fi belum tersambung. NTP butuh internet. Ubah PAKAI_NTP menjadi false, atau periksa nama Wi-Fi.");',
            '      kartuSiap = false;',
            '      return;',
            '    }',
            '    if (!tungguNtp(15000UL)) {',
            '      Serial.println("Jam internet belum didapat. Periksa Wi-Fi rumah.");',
            '      kartuSiap = false;',
            '      return;',
            '    }',
            '    Serial.println("Jam dinding WIB siap.");',
            '  } else {',
            '    Serial.println("Mode millis: kolom timestamp_ms. Bukan jam dinding.");',
            '  }',
            '',
            '  tulisHeaderJikaPerlu();',
            '}',
            '',
            'void loop() {',
            '  if (!kartuSiap) {',
            '    return;',
            '  }',
            '  if (lastLogAt != 0 && millis() - lastLogAt < LOG_INTERVAL_MS) {',
            '    return;',
            '  }',
            '  lastLogAt = millis();',
            '',
            '  float temperature = dht.readTemperature();',
            '  if (isnan(temperature)) {',
            '    Serial.println("DHT22 belum terbaca. Periksa kabel GPIO 4.");',
            '    return;',
            '  }',
            '',
            '  File file = SD.open(LOG_PATH, FILE_APPEND);',
            '  if (!file) {',
            '    Serial.println("Tidak bisa membuka log.csv untuk menulis.");',
            '    return;',
            '  }',
            '',
            '  String baris;',
            '  if (PAKAI_NTP) {',
            '    time_t now = time(nullptr);',
            '    struct tm info;',
            '    localtime_r(&now, &info);',
            '    char buf[24];',
            '    strftime(buf, sizeof(buf), "%Y-%m-%d %H:%M:%S", &info);',
            '    baris = String(buf) + "," + String(temperature, 1);',
            '  } else {',
            '    baris = String(millis()) + "," + String(temperature, 1);',
            '  }',
            '  file.println(baris);',
            '  file.flush();',
            '  file.close();',
            '  Serial.print("Tersimpan: ");',
            '  Serial.println(baris);',
            '}',
        ]);
    }

    private function body(): string
    {
        $sketch = htmlspecialchars($this->sketch(), ENT_QUOTES, 'UTF-8');
        $tools = $this->figure('fs36-tools-order.png', 'Urutan lima langkah: browser, File Explorer, Arduino IDE, kabel SPI, lalu Serial Monitor', '<strong>Urutan meja kerja (lima langkah):</strong> browser → File Explorer (format FAT32) → Arduino IDE → kabel SPI + DHT22 → Serial Monitor, lalu baca <code>log.csv</code> di komputer. Diagram buatan Koding Indonesia (FS-36).');
        $format = $this->figure('fs36-format-fat32.png', 'Ilustrasi File Explorer: kartu dipilih, klik kanan Format, sistem berkas FAT32, tombol Mulai', '<strong>Klik kanan ikon kartu di File Explorer, lalu Format.</strong> Pilih sistem berkas <strong>FAT32</strong>, label boleh <code>FSIOT</code>, centang format cepat, lalu Mulai. Ilustrasi buatan Koding Indonesia (FS-36), meniru langkah Format. Bukan jendela Windows resmi. Acuan format: dokumentasi kartu SD di artikel ini; Windows adalah merek Microsoft.');
        $wiring = $this->figure('fs36-wiring-spi.png', 'Wiring modul microSD SPI dan DHT22 ke ESP32 menurut label pin, bukan urutan kaki foto', '<strong>Gambar utama — wiring.</strong> Cocokkan tulisan pin: CS → GPIO 5, SCK → GPIO 18, MISO → GPIO 19, MOSI → GPIO 23, DHT22 DATA → GPIO 4, GND bersama. VCC modul SD mengikuti label 5V atau 3V3. Urutan kaki fisik bisa berbeda antarmodul. Diagram buatan Koding Indonesia (FS-36).');
        $cardPhoto = $this->figure('kit-microsd-card.jpg', 'Contoh rupa kartu microSD dan adapter SD plastik untuk slot kamera atau laptop', '<strong>Contoh rupa kartu saja.</strong> Foto ini membantu mengenali microSD dan adapter SD. Adapter plastik itu untuk slot laptop atau kamera. <strong>Jangan menyambungkannya ke pin ESP32.</strong> <strong>Jangan menyalin urutan kaki dari foto.</strong> Modul ke ESP32 adalah papan SPI terpisah. Sumber: <a href="https://commons.wikimedia.org/wiki/File:2015_Karta_microSD_z_adapterem_SD.jpg" target="_blank" rel="noopener noreferrer">2015 Karta microSD z adapterem SD</a> · Jacek Halicki · Wikimedia Commons · Creative Commons Attribution-Share Alike 4.0.');
        $spiPhoto = $this->figure('kit-microsd-spi.jpg', 'Contoh rupa papan breakout microSD SPI Adafruit, bukan modul kit biru enam pin', '<strong>Contoh rupa papan SPI saja.</strong> Ini breakout Adafruit, bukan modul kit toko yang sering berwarna biru dengan enam pin. Bentuknya boleh berbeda; busnya tetap SPI. <strong>Jangan menyalin urutan kaki dari foto.</strong> Wiring tetap menurut tulisan pin dan tabel GPIO di artikel ini. Sumber: <a href="https://commons.wikimedia.org/wiki/File:SD_Card_Breakout_Board.jpg" target="_blank" rel="noopener noreferrer">SD Card Breakout Board</a> · oomlout · Wikimedia Commons · Creative Commons Attribution-Share Alike 2.0. Asal Flickr, diunggah ulang ke Commons.');
        $dhtPhoto = $this->figure('kit-dht22.jpg', 'Contoh rupa modul DHT22 AM2302 pada papan merah, tiga pin berlabel DAT, VCC, dan GND', '<strong>Contoh rupa sensor suhu.</strong> <strong>Jangan menyalin urutan kaki dari foto.</strong> Wiring DHT22 tetap: VCC → 3V3, DATA atau DAT → GPIO 4, GND → GND. Sumber: <a href="https://commons.wikimedia.org/wiki/File:DHT_22_Sensor.jpg" target="_blank" rel="noopener noreferrer">AM2302 DHT22 Sensor</a> · L293D · Wikimedia Commons · Creative Commons Attribution-Share Alike 4.0.');
        $flow = $this->figure('fs36-csv-flow.png', 'Alur kiri ke kanan: DHT22, ESP32, berkas log.csv di kartu, lalu komputer', '<strong>Gambar utama — alur berkas.</strong> Baca dari kiri ke kanan: DHT22 → ESP32 → <code>log.csv</code> → komputer. Hari ini tidak lewat Mosquitto. Diagram buatan Koding Indonesia (FS-36).');
        $clock = $this->figure('fs36-millis-vs-ntp.png', 'Perbandingan kolom timestamp_ms saat PAKAI_NTP false dan waktu_wib saat PAKAI_NTP true', '<strong>millis bukan jam dinding.</strong> Uji pertama pakai <code>timestamp_ms</code>. NTP (jam internet) butuh Wi-Fi rumah. Diagram buatan Koding Indonesia (FS-36).');
        $serial = $this->figure('fs36-serial-monitor.png', 'Ilustrasi Serial Monitor Arduino IDE 2 menampilkan Kartu siap dan baris Tersimpan', '<strong>Buka Tools → Serial Monitor, baud 115200.</strong> Cari tulisan <code>Kartu siap. Menulis /log.csv</code> lalu <code>Tersimpan:</code>. Ilustrasi buatan Koding Indonesia (FS-36), meniru Serial Monitor Arduino IDE 2. Screenshot jendela resmi tidak dipakai utuh. Acuan menu: dokumentasi Arduino IDE 2, Arduino S.r.l., Creative Commons Attribution-Share Alike 4.0.');
        $troubleshooting = $this->figure('fs36-troubleshooting.png', 'Empat pemeriksaan jika kartu microSD ESP32 tidak terbaca', '<strong>Skema bantu.</strong> Periksa CS GPIO 5, format FAT32, GND bersama, lalu VCC sesuai label. Diagram buatan Koding Indonesia (FS-36).');
        $install = $this->stepsCard([
            ['title' => 'Buka browser', 'text' => 'Pakai Chrome, Firefox, Edge, atau Safari. Siapkan artikel ini. Jangan Upload sketch dulu.'],
            ['title' => 'Buka File Explorer', 'text' => 'Masukkan kartu ke laptop (slot atau USB reader). Klik kanan ikon kartu → <em>Format</em> → sistem berkas <strong>FAT32</strong> → Mulai. Jangan format partisi Windows.'],
            ['title' => 'Buka Arduino IDE', 'text' => '<code>SD.h</code> dan <code>SPI.h</code> sudah ada di core ESP32. Jangan memasang library SD untuk papan UNO. Library DHT biasanya sudah ada dari FS-21 atau FS-34. Jika Verify mengeluh, baru klik ikon tiga buku.'],
            ['title' => 'Siapkan kabel SPI dan DHT22', 'text' => 'Cabut USB ESP32 dulu. CS → GPIO 5, SCK → GPIO 18, MISO → GPIO 19, MOSI → GPIO 23, DHT22 DATA → GPIO 4. Baca tulisan pin. Jangan colok AC 220V.'],
            ['title' => 'Upload, lalu buka Serial Monitor', 'text' => 'Menu <em>Tools → Serial Monitor</em>, baud <strong>115200</strong>. Setelah beberapa baris <code>Tersimpan:</code>, cabut USB, cabut kartu, buka <code>log.csv</code> di komputer.'],
        ], '<strong>Cara menguji hari ini:</strong> bukti sukses = Serial menulis <code>Kartu siap. Menulis /log.csv</code> dan <code>Tersimpan:</code>, lalu <code>log.csv</code> terbuka di File Explorer dengan header dan angka suhu.');

        return <<<'HTML'
<h2>Pendahuluan — data tetap ada di kartu</h2>
<p><strong>FS-36 / #106 (ini)</strong> menyimpan suhu ke kartu microSD. Kemarin FS-35 mengirim perintah MQTT ke relay. Hari ini Mosquitto dan MQTTX <strong>tidak</strong> dipakai. Yang dibawa pulang adalah berkas <code>log.csv</code>.</p>
<p><strong>Intinya:</strong> ESP32 menulis baris ke kartu. Jika Wi-Fi atau broker nanti putus, angka di kartu tidak ikut hilang. Itu fondasi sebelum FS-37 (kirim ulang) dan grafik FS-45.</p>
<p><strong>Analogi:</strong> FS-34 adalah surat yang dikirim ke kantor pos. FS-36 adalah buku catatan di meja. Kamu masih bisa membaca catatan itu meskipun pos tutup.</p>
<p>Prasyarat lab: DHT22 GPIO 4 dari FS-21/FS-34, keputusan SPI untuk microSD dari FS-27, dan Wi-Fi FS-29 jika kamu menyalakan NTP. Relay GPIO 26 <strong>tidak</strong> dipakai hari ini.</p>

<h2>Hasil yang dituju</h2>
<ul>
<li>Kartu microSD berformat <strong>FAT32</strong> terbaca ESP32.</li>
<li>Berkas <code>log.csv</code> berisi waktu dan suhu.</li>
<li>Uji pertama memakai <code>timestamp_ms</code> (millis), tanpa Wi-Fi.</li>
<li>Uji kedua (opsional) memakai jam dinding WIB lewat NTP setelah Wi-Fi.</li>
<li>Kartu dicabut, lalu <code>log.csv</code> terbuka di komputer.</li>
</ul>
<p><strong>Batas lab hari ini:</strong> tidak ada MQTT, dashboard web, broker publik, atau AC 220V. Bukti cukup = Serial + berkas CSV di PC.</p>

<h2>Istilah yang dipakai hari ini</h2>
<ul>
<li><strong>SPI</strong> — bus empat sinyal untuk memori cepat. Keputusan FS-27: microSD memakai SPI, bukan I2C.</li>
<li><strong>CS</strong> — Chip Select, garis “giliran kartu ini”. Di lab ini CS = GPIO 5.</li>
<li><strong>SCK / MOSI / MISO</strong> — jam, data keluar ESP32, data masuk ESP32. Pin dikunci: 18 / 23 / 19.</li>
<li><strong>FAT32</strong> — format berkas yang dimengerti library <code>SD.h</code> pada ESP32. Bukan NTFS atau exFAT.</li>
<li><strong>CSV</strong> — teks biasa, kolom dipisah koma. Bisa dibuka Notepad atau Excel.</li>
<li><strong><code>log.csv</code></strong> — nama berkas di akar kartu.</li>
<li><strong>millis</strong> — milidetik sejak ESP32 menyala. Bukan jam dinding.</li>
<li><strong>NTP</strong> — jam internet. Butuh Wi-Fi rumah. Di sini zona WIB (GMT+7).</li>
<li><strong>Mount</strong> — ESP32 berhasil membaca kartu. Gagal mount = Serial menulis <code>Kartu tidak terbaca</code>.</li>
<li><strong>GND bersama</strong> — ESP32, modul SD, dan DHT22 memakai satu ground.</li>
</ul>

<h2>Persiapan — buka tool yang benar dulu</h2>
HTML
            .$tools.$install.<<<'HTML'
<p><strong>Jangan dipakai hari ini:</strong> MQTTX, Mosquitto, relay, AC 220V, port forwarding/router, broker publik, Laragon, <code>php artisan</code>, atau dashboard web. Jangan memakai <code>SD_MMC</code> (slot SD native); lab ini memakai SPI + <code>SD.h</code>.</p>
<p><strong>Tips ponsel:</strong> jika diagram terasa kecil, gunakan fitur perbesar pada browser atau layar. Gambar tidak perlu diketuk sampai memenuhi layar agar teks di sekitarnya tetap terbaca.</p>

<h2>Format kartu menjadi FAT32</h2>
HTML
            .$format.$cardPhoto.<<<'HTML'
<p><strong>Buka dulu File Explorer</strong> (ikon folder di bilah tugas, atau tekan <kbd>Win</kbd>+<kbd>E</kbd>). Masukkan kartu ke slot laptop atau USB reader. Pastikan yang dipilih adalah ikon kartu, bukan disk <code>C:</code>.</p>
<ol>
<li>Klik kanan ikon kartu → <strong>Format</strong>.</li>
<li>Sistem berkas: <strong>FAT32</strong>. Label volume boleh <code>FSIOT</code>.</li>
<li>Format cepat boleh dicentang. Klik <strong>Mulai</strong>, lalu tunggu selesai.</li>
</ol>
<p>Pakai kartu <strong>8–32 GB</strong> bermerek. Kartu 64 GB ke atas sering hanya menawarkan exFAT di jendela Format Windows; library hari ini mengharapkan FAT32.</p>
<p><strong>macOS:</strong> buka aplikasi <strong>Disk Utility</strong> dulu, pilih volume kartu, Erase, format <em>MS-DOS (FAT)</em>. <strong>Ubuntu atau Debian:</strong> buka aplikasi <strong>Disks</strong> dulu, pilih kartu, Format, FAT.</p>
<p>Adapter SD plastik pada foto hanya untuk slot kamera atau laptop. Itu <strong>bukan</strong> modul SPI ke ESP32.</p>

<h2>Library Arduino IDE</h2>
<p><strong>Buka dulu Arduino IDE.</strong> Pilih papan <strong>ESP32</strong>, bukan UNO.</p>
<p><code>SD.h</code> dan <code>SPI.h</code> sudah termasuk core Arduino-ESP32. <strong>Jangan</strong> memasang library bernama SD yang menampilkan papan UNO di Library Manager. Itu untuk AVR, bukan lab ini.</p>
<p>Library <strong>DHT sensor library</strong> (Adafruit) biasanya sudah ada dari FS-21 atau FS-34. Jika Verify nanti mengeluh <code>DHT.h</code> hilang: di bilah kiri, klik ikon tiga buku (Library Manager). Itu satu-satunya jalur yang dipakai hari ini untuk DHT. Jangan memakai menu lama <em>Tools → Manage Libraries</em>. Cari <em>DHT sensor library</em>, papan ESP32, lalu INSTALL jika belum ada.</p>
<p>Menu <em>Tools → Serial Monitor</em> tetap dipakai nanti untuk melihat tulisan Serial; itu <strong>bukan</strong> Library Manager.</p>
<p>Rujukan: <a href="https://docs.arduino.cc/libraries/sd/" target="_blank" rel="noopener noreferrer">Arduino SD library</a> dan <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-installing-a-library/" target="_blank" rel="noopener noreferrer">memasang library Arduino IDE 2</a>, Arduino S.r.l. Dokumentasi Arduino berlisensi Creative Commons Attribution-Share Alike 4.0. API kartu pada ESP32: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/api/sd.html" target="_blank" rel="noopener noreferrer">Espressif SD (Arduino)</a>.</p>

<h2>Pasang kabel SPI dan DHT22</h2>
HTML
            .$wiring.$spiPhoto.$dhtPhoto.<<<'HTML'
<p>Cabut USB ESP32 sebelum merapikan kabel. Pin SPI lab ini dikunci (VSPI Arduino-ESP32):</p>
<table>
<thead><tr><th>Tulisan di modul SD</th><th>GPIO ESP32</th></tr></thead>
<tbody>
<tr><td>CS / SS / Chip Select</td><td><strong>5</strong></td></tr>
<tr><td>SCK / CLK / SCLK</td><td><strong>18</strong></td></tr>
<tr><td>MISO / DO / SDO</td><td><strong>19</strong></td></tr>
<tr><td>MOSI / DI / SDI</td><td><strong>23</strong></td></tr>
<tr><td>VCC / 5V / 3V3</td><td><strong>5V</strong> jika tercetak 5V; <strong>3V3</strong> jika hanya tercetak 3,3 V</td></tr>
<tr><td>GND</td><td>GND</td></tr>
</tbody>
</table>
<ol>
<li>Sambungkan empat sinyal SPI menurut tabel, plus VCC sesuai label, plus GND.</li>
<li>DHT22: <strong>VCC → 3V3</strong>, <strong>DATA atau DAT → GPIO 4</strong>, <strong>GND → GND</strong>. Modul kit biasanya sudah punya resistor di papan. Jika modul polos, tambah 10 kΩ ke 3V3 seperti FS-21.</li>
<li>Pasang kembali USB data ke ESP32.</li>
</ol>
<p><strong>Jangan menebak pin.</strong> Label modul toko bisa tertulis CLK / DO / DI, bukan SCK / MISO / MOSI. Baca tulisan itu. Sinyal CS/SCK/MISO/MOSI adalah 3,3 V — jangan menyambungkannya ke 5V. <strong>Jangan colok AC 220V.</strong></p>

<h2>Sketch ESP32 — tulis log.csv</h2>
HTML
            .$flow.<<<'HTML'
<p>Di Arduino IDE, buat sketch baru bernama <code>FS36_sd_log_csv</code>. Biarkan <code>PAKAI_NTP = false</code> pada uji pertama. Ganti <code>GANTI_NAMA_WIFI</code> hanya ketika NTP dinyalakan. Jangan menaruh sandi asli pada screenshot.</p>
<pre><code class="language-arduino">
HTML
            .$sketch.<<<'HTML'
</code></pre>
<p><code>SPI.begin(18, 19, 23, CS_PIN)</code> mengunci SCK, MISO, MOSI, lalu <code>SD.begin(CS_PIN)</code> memakai GPIO 5 sebagai CS. Baris baru ditambah dengan <code>FILE_APPEND</code>. Rujukan: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/api/sd.html" target="_blank" rel="noopener noreferrer">Espressif SD API</a>, <a href="https://github.com/adafruit/DHT-sensor-library" target="_blank" rel="noopener noreferrer">Adafruit DHT sensor library</a>, dan <a href="https://docs.espressif.com/projects/esp-idf/en/latest/esp32/api-reference/system/system_time.html" target="_blank" rel="noopener noreferrer">Espressif system time (NTP)</a>.</p>

<h2>Upload dan baca Serial Monitor</h2>
HTML
            .$serial.<<<'HTML'
<ol>
<li>Pilih board ESP32 dan port USB, lalu <strong>Verify</strong> dan <strong>Upload</strong>.</li>
<li>Buka <strong>Tools → Serial Monitor</strong> pada baud <strong>115200</strong>.</li>
<li>Cari tulisan <code>Kartu siap. Menulis /log.csv</code> lalu <code>Mode millis: kolom timestamp_ms. Bukan jam dinding.</code></li>
<li>Tunggu beberapa baris <code>Tersimpan:</code>, misalnya <code>5123,27.4</code>.</li>
</ol>
<p><strong>Berhasil berarti:</strong> Serial menulis <code>Kartu siap. Menulis /log.csv</code> dan berulang <code>Tersimpan:</code>. Pesan yang sama harus muncul setiap lima detik. Jika yang muncul <code>Kartu tidak terbaca</code>, jangan cabut-pasang kartu berulang kali — periksa empat titik di bagian gangguan.</p>

<h2>Cabut kartu, buka log.csv di komputer</h2>
<ol>
<li>Tunggu Serial berhenti sejenak, atau cabut USB ESP32 agar penulisan berhenti.</li>
<li>Cabut kartu dari modul. Jangan menarik kartu saat Serial masih menulis <code>Tersimpan:</code>.</li>
<li><strong>Buka File Explorer</strong>. Masukkan kartu ke laptop. Cari berkas <code>log.csv</code>.</li>
<li>Buka dengan Notepad. Baris pertama harus <code>timestamp_ms,temperature_c</code>, lalu angka milidetik dan suhu.</li>
</ol>
<p>Excel atau Google Spreadsheet juga bisa. Jika semua kolom menumpuk di satu sel, pilih pemisah <strong>koma</strong>.</p>

<h2>Bandingkan millis dan NTP</h2>
HTML
            .$clock.<<<'HTML'
<p>Uji pertama selesai tanpa Wi-Fi. Itu sengaja: kamu melihat bahwa <code>millis</code> hanya menghitung sejak ESP32 nyala. Setelah Upload ulang, angka di kolom pertama kembali kecil.</p>
<p>Untuk jam dinding:</p>
<ol>
<li>Pastikan Wi-Fi rumah dari FS-29 sudah pernah berhasil.</li>
<li>Ubah <code>PAKAI_NTP</code> menjadi <code>true</code>.</li>
<li>Isi <code>GANTI_NAMA_WIFI</code> dan sandi. Jangan screenshot sandi.</li>
<li>Upload lagi. Serial harus menulis <code>Jam dinding WIB siap.</code> lalu baris seperti <code>2026-08-14 10:15:03,27.4</code>.</li>
</ol>
<p>NTP memakai <code>configTime</code> dan server <code>pool.ntp.org</code>. Itu jam internet, bukan jam di dalam chip. Tanpa Wi-Fi, NTP tidak jalan — kembalikan <code>PAKAI_NTP</code> ke <code>false</code>.</p>
<p>Rujukan NTP: <a href="https://www.ntppool.org/en/use.html" target="_blank" rel="noopener noreferrer">NTP Pool — how to use</a> (pemakaian wajar untuk lab rumah) dan dokumentasi waktu sistem Espressif.</p>

<h2>Mengapa jam salah merusak grafik</h2>
<p>FS-45 akan menggambar suhu terhadap waktu. Jika sumbu X memakai <code>millis</code> dari beberapa kali nyala, titik-titik meloncat ke awal lagi. Grafik tampak “kembali ke nol”, padahal dunia sudah siang.</p>
<p>Jam dinding WIB membuat baris bisa diurut tanggal. Itulah alasan NTP diajarkan sekarang, sebelum chart, bukan sebagai hiasan.</p>

<h2>Jika kartu tidak terbaca</h2>
HTML
            .$troubleshooting.<<<'HTML'
<ol>
<li><strong>CS bukan GPIO 5.</strong> Jangan memindahkan CS ke GPIO lain “karena kelihatannya lebih dekat”. SCK/MISO/MOSI juga harus 18/19/23.</li>
<li><strong>Kartu masih NTFS atau exFAT.</strong> Format ulang ke FAT32 di File Explorer. Kartu di atas 32 GB sering bandel; ganti ke 8–32 GB.</li>
<li><strong>GND tidak bersama.</strong> Modul SD, DHT22, dan ESP32 harus ke GND yang sama.</li>
<li><strong>VCC salah.</strong> Jika tercetak 5V, pakai 5V (modul punya regulator ke kartu). Jika hanya 3V3, pakai 3V3. Sinyal SPI tetap 3,3 V.</li>
<li><strong>Kartu palsu atau slot longgar.</strong> Coba kartu lain bermerek. Tekan kartu sampai bunyi klik.</li>
<li><strong>Library SD untuk UNO terpasang.</strong> Hapus pemakaian itu. Pakai <code>SD.h</code> core ESP32.</li>
<li><strong>DHT22 belum terbaca, kartu sudah siap.</strong> Itu masalah sensor GPIO 4, bukan mount. Periksa VCC 3V3 dan DATA.</li>
</ol>

<h2 id="fsiot-sd-checklist">Checklist sebelum FS-37</h2>
<p>Centang setelah kamu benar-benar melakukan setiap poin. Target: <strong>10/10</strong>. Progres disimpan di browser perangkatmu dan tidak dikirim ke server.</p>
<ul id="fsiot-sd-checklist-items">
<li>Kartu sudah diformat FAT32 di File Explorer (atau Disk Utility / Disks).</li>
<li>Wiring CS=5, SCK=18, MISO=19, MOSI=23, GND bersama, VCC sesuai label.</li>
<li>DHT22 terpasang ke GPIO 4 menurut tulisan pin.</li>
<li>Sketch memakai <code>SD.h</code> core ESP32, bukan library SD untuk UNO.</li>
<li>Uji pertama memakai <code>PAKAI_NTP = false</code>.</li>
<li>Serial Monitor menampilkan <code>Kartu siap. Menulis /log.csv</code>.</li>
<li>Serial Monitor menampilkan <code>Tersimpan:</code> berulang.</li>
<li><code>log.csv</code> terbuka di komputer dengan header <code>timestamp_ms,temperature_c</code>.</li>
<li>Saya paham millis bukan jam dinding.</li>
<li>Saya menjelaskan NTP butuh Wi-Fi, atau sudah mencoba <code>PAKAI_NTP = true</code>.</li>
</ul>
<p><strong>Cara memeriksa kesiapan:</strong> jelaskan alur DHT22 → ESP32 → <code>log.csv</code> → PC dengan kata-katamu sendiri. Pada FS-37, data di kartu akan dikirim ulang saat Wi-Fi kembali.</p>

<h2>Kesalahan yang sering terjadi</h2>
<ul>
<li><strong>Menyalin urutan kaki dari foto.</strong> Foto hanya contoh rupa. Baca tulisan pin.</li>
<li><strong>Menyambung adapter SD plastik ke GPIO.</strong> Adapter itu untuk laptop. ESP32 butuh modul SPI.</li>
<li><strong>Mengira millis = jam dinding.</strong> Angka itu reset setiap nyala.</li>
<li><strong>Menyalakan NTP tanpa Wi-Fi.</strong> Serial akan mengeluh Wi-Fi belum tersambung.</li>
<li><strong>Memakai tegangan 5 V pada MOSI/MISO/SCK/CS.</strong> ESP32 3,3 V.</li>
<li><strong>Kartu palsu atau format NTFS.</strong> Ganti kartu, format FAT32.</li>
<li><strong>Lupa GND bersama.</strong></li>
<li><strong>Membuka MQTTX hari ini.</strong> Lab ini tidak memerlukannya.</li>
</ul>

<h2>Pertanyaan yang sering muncul</h2>
<h3>Kenapa adapter SD tidak boleh ke ESP32?</h3>
<p>Adapter plastik hanya mengubah ukuran kartu agar masuk slot SD penuh. Tidak ada penggeser tegangan dan tidak ada header SPI. Yang ke ESP32 adalah papan modul SPI.</p>
<h3>Modul saya tercetak 5V, teman saya 3V3. Siapa yang benar?</h3>
<p>Keduanya bisa benar. Ikuti tulisan di papanmu. Sinyal SPI tetap 3,3 V ke GPIO.</p>
<h3>Kenapa tidak boleh library SD untuk UNO?</h3>
<p>Itu untuk AVR. ESP32 sudah membawa <code>SD.h</code> di core. Memasang yang salah membuat Verify bingung.</p>
<h3>Bolehkah kartu 64 GB?</h3>
<p>Sering merepotkan di Windows karena exFAT. Pakai 8–32 GB FAT32 untuk lab ini.</p>
<h3>Kenapa MQTTX tidak dibuka?</h3>
<p>Hari ini tugasnya satu: berkas di kartu. Pengiriman ulang saat Wi-Fi putus adalah FS-37.</p>
<h3>Apa itu pool.ntp.org?</h3>
<p>Kumpulan server jam di internet. Lab rumah boleh memakainya dengan wajar. Tanpa Wi-Fi, lewati NTP.</p>

<h2>Sumber</h2>
<ul>
<li><a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/api/sd.html" target="_blank" rel="noopener noreferrer">Espressif — SD (Arduino-ESP32)</a></li>
<li><a href="https://docs.arduino.cc/libraries/sd/" target="_blank" rel="noopener noreferrer">Arduino — SD library</a>. Dokumentasi berlisensi Creative Commons Attribution-Share Alike 4.0. Arduino dan logo Arduino adalah merek Arduino S.r.l.</li>
<li><a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-installing-a-library/" target="_blank" rel="noopener noreferrer">Arduino — Installing libraries (IDE 2)</a></li>
<li><a href="https://github.com/adafruit/DHT-sensor-library" target="_blank" rel="noopener noreferrer">Adafruit DHT sensor library</a></li>
<li><a href="https://docs.espressif.com/projects/esp-idf/en/latest/esp32/api-reference/system/system_time.html" target="_blank" rel="noopener noreferrer">Espressif — System Time / NTP</a></li>
<li><a href="https://www.ntppool.org/en/use.html" target="_blank" rel="noopener noreferrer">NTP Pool — how to use</a></li>
<li><a href="https://commons.wikimedia.org/wiki/File:2015_Karta_microSD_z_adapterem_SD.jpg" target="_blank" rel="noopener noreferrer">2015 Karta microSD z adapterem SD</a> · Jacek Halicki · Wikimedia Commons · Creative Commons Attribution-Share Alike 4.0. Foto hanya contoh rupa kartu; jangan menyalin urutan kaki; adapter bukan modul SPI.</li>
<li><a href="https://commons.wikimedia.org/wiki/File:SD_Card_Breakout_Board.jpg" target="_blank" rel="noopener noreferrer">SD Card Breakout Board</a> · oomlout · Wikimedia Commons · Creative Commons Attribution-Share Alike 2.0. Foto hanya contoh rupa papan SPI; jangan menyalin urutan kaki dari foto.</li>
<li><a href="https://commons.wikimedia.org/wiki/File:DHT_22_Sensor.jpg" target="_blank" rel="noopener noreferrer">AM2302 DHT22 Sensor</a> · L293D · Wikimedia Commons · Creative Commons Attribution-Share Alike 4.0. Foto hanya contoh rupa; jangan menyalin urutan kaki dari foto.</li>
<li>Diagram urutan tools, wiring, alur CSV, millis lawan NTP, skema periksa, serta ilustrasi Format dan Serial Monitor — Koding Indonesia (FS-36)</li>
</ul>

<h2>Selanjutnya</h2>
<p><strong>Ringkasnya:</strong> suhu kini tersimpan di <code>log.csv</code>. Pada <strong>FS-37</strong>, data di kartu akan dikirim ulang ketika Wi-Fi kembali, agar jendela waktu demo tidak bolong total.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $sketch = htmlspecialchars($this->sketch(), ENT_QUOTES, 'UTF-8');
        $tools = $this->figure('fs36-tools-order.png', 'Five-step tool order: browser, File Explorer, Arduino IDE, SPI wiring, then Serial Monitor', '<strong>Desk order (five steps):</strong> browser → File Explorer (FAT32 format) → Arduino IDE → SPI + DHT22 wiring → Serial Monitor, then read <code>log.csv</code> on the computer. Diagram by Koding Indonesia (FS-36).');
        $format = $this->figure('fs36-format-fat32.png', 'File Explorer illustration: card selected, right-click Format, FAT32 file system, Start button', '<strong>Right-click the card icon in File Explorer, then Format.</strong> Choose file system <strong>FAT32</strong>, the label may be <code>FSIOT</code>, quick format is fine, then Start. Illustration by Koding Indonesia (FS-36), modelled on the Format step. Not an official Windows window. Windows is a trademark of Microsoft.');
        $wiring = $this->figure('fs36-wiring-spi.png', 'microSD SPI module and DHT22 wiring to ESP32 by printed labels, not photo pin order', '<strong>Main figure — wiring.</strong> Match the printed labels: CS → GPIO 5, SCK → GPIO 18, MISO → GPIO 19, MOSI → GPIO 23, DHT22 DATA → GPIO 4, shared GND. SD module VCC follows the 5V or 3V3 label. Physical pin order can differ between modules. Diagram by Koding Indonesia (FS-36).');
        $cardPhoto = $this->figure('kit-microsd-card.jpg', 'Example microSD card and plastic SD adapter for a camera or laptop slot', '<strong>Appearance example only.</strong> This photo helps you recognise a microSD card and SD adapter. The plastic adapter is for a laptop or camera slot. <strong>Do not wire it to ESP32 pins.</strong> <strong>Do not copy pin order from the photo.</strong> The ESP32 needs a separate SPI module. Source: <a href="https://commons.wikimedia.org/wiki/File:2015_Karta_microSD_z_adapterem_SD.jpg" target="_blank" rel="noopener noreferrer">2015 Karta microSD z adapterem SD</a> · Jacek Halicki · Wikimedia Commons · Creative Commons Attribution-Share Alike 4.0.');
        $spiPhoto = $this->figure('kit-microsd-spi.jpg', 'Example Adafruit microSD SPI breakout board, not a typical six-pin blue kit module', '<strong>SPI board appearance only.</strong> This is an Adafruit breakout, not the blue six-pin module many kits ship. The shape may differ; the bus is still SPI. <strong>Do not copy pin order from the photo.</strong> Wiring still follows the printed labels and the GPIO table in this article. Source: <a href="https://commons.wikimedia.org/wiki/File:SD_Card_Breakout_Board.jpg" target="_blank" rel="noopener noreferrer">SD Card Breakout Board</a> · oomlout · Wikimedia Commons · Creative Commons Attribution-Share Alike 2.0. Originally on Flickr, re-uploaded to Commons.');
        $dhtPhoto = $this->figure('kit-dht22.jpg', 'Example DHT22 AM2302 module on a red board with three pins labelled DAT, VCC, and GND', '<strong>Sensor appearance only.</strong> <strong>Do not copy pin order from the photo.</strong> DHT22 wiring is still: VCC → 3V3, DATA or DAT → GPIO 4, GND → GND. Source: <a href="https://commons.wikimedia.org/wiki/File:DHT_22_Sensor.jpg" target="_blank" rel="noopener noreferrer">AM2302 DHT22 Sensor</a> · L293D · Wikimedia Commons · Creative Commons Attribution-Share Alike 4.0.');
        $flow = $this->figure('fs36-csv-flow.png', 'Left-to-right flow: DHT22, ESP32, log.csv on the card, then the computer', '<strong>Main figure — file flow.</strong> Read left to right: DHT22 → ESP32 → <code>log.csv</code> → computer. Mosquitto is not in this path today. Diagram by Koding Indonesia (FS-36).');
        $clock = $this->figure('fs36-millis-vs-ntp.png', 'Comparison of timestamp_ms when PAKAI_NTP is false and waktu_wib when PAKAI_NTP is true', '<strong>millis is not wall-clock time.</strong> The first test uses <code>timestamp_ms</code>. NTP (internet time) needs home Wi-Fi. Diagram by Koding Indonesia (FS-36).');
        $serial = $this->figure('fs36-serial-monitor.png', 'Arduino IDE 2 Serial Monitor illustration showing Kartu siap and Tersimpan lines', '<strong>Open Tools → Serial Monitor, baud 115200.</strong> Look for <code>Kartu siap. Menulis /log.csv</code> then <code>Tersimpan:</code>. Illustration by Koding Indonesia (FS-36), modelled on Arduino IDE 2 Serial Monitor. The official window screenshot is not used as-is. Menu reference: Arduino IDE 2 docs, Arduino S.r.l., Creative Commons Attribution-Share Alike 4.0.');
        $troubleshooting = $this->figure('fs36-troubleshooting.png', 'Four checks when the ESP32 microSD card does not mount', '<strong>Helper schematic.</strong> Check CS GPIO 5, FAT32 format, shared GND, then VCC by the printed label. Diagram by Koding Indonesia (FS-36).');
        $install = $this->stepsCard([
            ['title' => 'Open a browser', 'text' => 'Use Chrome, Firefox, Edge, or Safari. Keep this guide ready. Do not Upload a sketch yet.'],
            ['title' => 'Open File Explorer', 'text' => 'Insert the card in the laptop (slot or USB reader). Right-click the card icon → <em>Format</em> → file system <strong>FAT32</strong> → Start. Do not format the Windows partition.'],
            ['title' => 'Open Arduino IDE', 'text' => '<code>SD.h</code> and <code>SPI.h</code> already ship in the ESP32 core. Do not install the SD library for an UNO board. The DHT library is usually already there from FS-21 or FS-34. If Verify complains, only then click the three-book icon.'],
            ['title' => 'Prepare the SPI and DHT22 wiring', 'text' => 'Unplug ESP32 USB first. CS → GPIO 5, SCK → GPIO 18, MISO → GPIO 19, MOSI → GPIO 23, DHT22 DATA → GPIO 4. Read the labels. Do not connect AC mains.'],
            ['title' => 'Upload, then open Serial Monitor', 'text' => 'Use the <em>Tools → Serial Monitor</em> menu, baud <strong>115200</strong>. After a few <code>Tersimpan:</code> lines, unplug USB, remove the card, and open <code>log.csv</code> on the computer.'],
        ], '<strong>How to test today:</strong> success = Serial prints <code>Kartu siap. Menulis /log.csv</code> and <code>Tersimpan:</code>, then <code>log.csv</code> opens in File Explorer with a header and temperature numbers.');

        return <<<'HTML'
<h2>Introduction — the data stays on the card</h2>
<p><strong>FS-36 / #106 (this article)</strong> stores temperature on a microSD card. Yesterday FS-35 sent MQTT commands to a relay. Today Mosquitto and MQTTX are <strong>not</strong> used. What you take home is the <code>log.csv</code> file.</p>
<p><strong>In short:</strong> ESP32 writes rows to the card. If Wi-Fi or the broker later drops, the numbers on the card do not vanish with it. That is the foundation before FS-37 (send again) and the FS-45 chart.</p>
<p><strong>Analogy:</strong> FS-34 was a letter sent to the post office. FS-36 is a notebook on the desk. You can still read the notes when the post office is closed.</p>
<p>Lab prerequisites: DHT22 on GPIO 4 from FS-21/FS-34, the SPI-for-microSD decision from FS-27, and FS-29 Wi-Fi if you turn NTP on. The GPIO 26 relay is <strong>not</strong> used today.</p>

<h2>Expected outcome</h2>
<ul>
<li>A <strong>FAT32</strong> microSD card is readable by ESP32.</li>
<li>The <code>log.csv</code> file holds time and temperature.</li>
<li>The first test uses <code>timestamp_ms</code> (millis), without Wi-Fi.</li>
<li>The second test (optional) uses WIB wall-clock time via NTP after Wi-Fi.</li>
<li>The card is removed, then <code>log.csv</code> opens on the computer.</li>
</ul>
<p><strong>Today’s lab boundary:</strong> there is no MQTT, web dashboard, public broker, or AC mains. Success = Serial + a CSV file on the PC.</p>

<h2>Terms used today</h2>
<ul>
<li><strong>SPI</strong> — a four-signal bus for fast memory. FS-27 decision: microSD uses SPI, not I2C.</li>
<li><strong>CS</strong> — Chip Select, the “this card’s turn” line. In this lab CS = GPIO 5.</li>
<li><strong>SCK / MOSI / MISO</strong> — clock, data out of ESP32, data into ESP32. Locked pins: 18 / 23 / 19.</li>
<li><strong>FAT32</strong> — the filesystem <code>SD.h</code> on ESP32 understands. Not NTFS or exFAT.</li>
<li><strong>CSV</strong> — plain text, columns split by commas. Opens in Notepad or Excel.</li>
<li><strong><code>log.csv</code></strong> — the filename at the root of the card.</li>
<li><strong>millis</strong> — milliseconds since ESP32 powered up. Not wall-clock time.</li>
<li><strong>NTP</strong> — internet time. Needs home Wi-Fi. Here the zone is WIB (GMT+7).</li>
<li><strong>Mount</strong> — ESP32 successfully reads the card. A failed mount prints <code>Kartu tidak terbaca</code>.</li>
<li><strong>Shared GND</strong> — ESP32, the SD module, and DHT22 share one ground.</li>
</ul>

<h2>Preparation — open the right tools first</h2>
HTML
            .$tools.$install.<<<'HTML'
<p><strong>Not used today:</strong> MQTTX, Mosquitto, a relay, AC mains, router port forwarding, a public broker, Laragon, <code>php artisan</code>, or a dashboard. Do not use <code>SD_MMC</code> (native SD slot); this lab uses SPI + <code>SD.h</code>.</p>
<p><strong>Phone tip:</strong> if a diagram feels small, use the browser or screen zoom. You do not need to tap the image to fill the screen; nearby text should stay readable.</p>

<h2>Format the card as FAT32</h2>
HTML
            .$format.$cardPhoto.<<<'HTML'
<p><strong>Open File Explorer first</strong> (folder icon on the taskbar, or press <kbd>Win</kbd>+<kbd>E</kbd>). Insert the card in the laptop slot or a USB reader. Make sure you selected the card icon, not the <code>C:</code> disk.</p>
<ol>
<li>Right-click the card icon → <strong>Format</strong>.</li>
<li>File system: <strong>FAT32</strong>. The volume label may be <code>FSIOT</code>.</li>
<li>Quick format may stay checked. Click <strong>Start</strong> and wait.</li>
</ol>
<p>Use a branded <strong>8–32 GB</strong> card. Cards 64 GB and up often only offer exFAT in the Windows Format window; today’s library expects FAT32.</p>
<p><strong>macOS:</strong> open the <strong>Disk Utility</strong> app first, select the card volume, Erase, format <em>MS-DOS (FAT)</em>. <strong>Ubuntu or Debian:</strong> open the <strong>Disks</strong> app first, select the card, Format, FAT.</p>
<p>The plastic SD adapter in the photo is only for a camera or laptop slot. It is <strong>not</strong> an SPI module for ESP32.</p>

<h2>Arduino IDE libraries</h2>
<p><strong>Open Arduino IDE first.</strong> Choose an <strong>ESP32</strong> board, not an UNO.</p>
<p><code>SD.h</code> and <code>SPI.h</code> already ship in the Arduino-ESP32 core. <strong>Do not</strong> install an SD library that shows an UNO board in Library Manager. That one is for AVR, not this lab.</p>
<p>The Adafruit <strong>DHT sensor library</strong> is usually already there from FS-21 or FS-34. If Verify later complains that <code>DHT.h</code> is missing: in the left bar, click the three-book icon (Library Manager). That is the only path used today for DHT. Do not use the old <em>Tools → Manage Libraries</em> menu. Search <em>DHT sensor library</em>, ESP32 board, then INSTALL if needed.</p>
<p>The <em>Tools → Serial Monitor</em> menu is still used later to read Serial text; that is <strong>not</strong> Library Manager.</p>
<p>References: the <a href="https://docs.arduino.cc/libraries/sd/" target="_blank" rel="noopener noreferrer">Arduino SD library</a> and <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-installing-a-library/" target="_blank" rel="noopener noreferrer">Arduino IDE 2 installing-a-library</a>, Arduino S.r.l. Arduino documentation is licensed under Creative Commons Attribution-Share Alike 4.0. ESP32 card API: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/api/sd.html" target="_blank" rel="noopener noreferrer">Espressif SD (Arduino)</a>.</p>

<h2>Wire SPI and DHT22</h2>
HTML
            .$wiring.$spiPhoto.$dhtPhoto.<<<'HTML'
<p>Unplug ESP32 USB before rearranging cables. This lab locks the Arduino-ESP32 VSPI pins:</p>
<table>
<thead><tr><th>Label on the SD module</th><th>ESP32 GPIO</th></tr></thead>
<tbody>
<tr><td>CS / SS / Chip Select</td><td><strong>5</strong></td></tr>
<tr><td>SCK / CLK / SCLK</td><td><strong>18</strong></td></tr>
<tr><td>MISO / DO / SDO</td><td><strong>19</strong></td></tr>
<tr><td>MOSI / DI / SDI</td><td><strong>23</strong></td></tr>
<tr><td>VCC / 5V / 3V3</td><td><strong>5V</strong> if 5V is printed; <strong>3V3</strong> if only 3.3 V is printed</td></tr>
<tr><td>GND</td><td>GND</td></tr>
</tbody>
</table>
<ol>
<li>Connect the four SPI signals from the table, plus VCC by the label, plus GND.</li>
<li>DHT22: <strong>VCC → 3V3</strong>, <strong>DATA or DAT → GPIO 4</strong>, <strong>GND → GND</strong>. Kit modules usually already have a resistor on the board. If the module is bare, add 10 kΩ to 3V3 as in FS-21.</li>
<li>Reconnect the data USB cable.</li>
</ol>
<p><strong>Do not guess pins.</strong> Shop modules may read CLK / DO / DI instead of SCK / MISO / MOSI. Read those words. CS/SCK/MISO/MOSI are 3.3 V signals — do not wire them to 5V. <strong>Do not connect AC mains.</strong></p>

<h2>ESP32 sketch — write log.csv</h2>
HTML
            .$flow.<<<'HTML'
<p>Create <code>FS36_sd_log_csv</code> in Arduino IDE. Leave <code>PAKAI_NTP = false</code> for the first test. Change <code>GANTI_NAMA_WIFI</code> only when NTP is turned on. Do not put a real password in a screenshot.</p>
<pre><code class="language-arduino">
HTML
            .$sketch.<<<'HTML'
</code></pre>
<p><code>SPI.begin(18, 19, 23, CS_PIN)</code> locks SCK, MISO, MOSI, then <code>SD.begin(CS_PIN)</code> uses GPIO 5 as CS. New rows are added with <code>FILE_APPEND</code>. References: the <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/api/sd.html" target="_blank" rel="noopener noreferrer">Espressif SD API</a>, the <a href="https://github.com/adafruit/DHT-sensor-library" target="_blank" rel="noopener noreferrer">Adafruit DHT sensor library</a>, and <a href="https://docs.espressif.com/projects/esp-idf/en/latest/esp32/api-reference/system/system_time.html" target="_blank" rel="noopener noreferrer">Espressif system time (NTP)</a>.</p>

<h2>Upload and read Serial Monitor</h2>
HTML
            .$serial.<<<'HTML'
<ol>
<li>Select the ESP32 board and USB port, then <strong>Verify</strong> and <strong>Upload</strong>.</li>
<li>Open <strong>Tools → Serial Monitor</strong> at <strong>115200</strong> baud.</li>
<li>Look for <code>Kartu siap. Menulis /log.csv</code> then <code>Mode millis: kolom timestamp_ms. Bukan jam dinding.</code></li>
<li>Wait for a few <code>Tersimpan:</code> lines, for example <code>5123,27.4</code>.</li>
</ol>
<p><strong>Success means:</strong> Serial prints <code>Kartu siap. Menulis /log.csv</code> and repeating <code>Tersimpan:</code>. The same message should appear every five seconds. If you see <code>Kartu tidak terbaca</code>, do not keep reseating the card — check the four points in the troubleshooting section.</p>

<h2>Unplug the card and open log.csv on the computer</h2>
<ol>
<li>Wait for Serial to pause, or unplug ESP32 USB so writing stops.</li>
<li>Remove the card from the module. Do not pull the card while Serial still prints <code>Tersimpan:</code>.</li>
<li><strong>Open File Explorer</strong>. Insert the card in the laptop. Find <code>log.csv</code>.</li>
<li>Open it in Notepad. The first line should be <code>timestamp_ms,temperature_c</code>, then millisecond numbers and temperature.</li>
</ol>
<p>Excel or Google Sheets also work. If every column lands in one cell, choose a <strong>comma</strong> separator.</p>

<h2>Compare millis and NTP</h2>
HTML
            .$clock.<<<'HTML'
<p>The first test finished without Wi-Fi. That is on purpose: you see that <code>millis</code> only counts since ESP32 powered up. After another Upload, the first column starts small again.</p>
<p>For wall-clock time:</p>
<ol>
<li>Confirm home Wi-Fi from FS-29 has worked before.</li>
<li>Set <code>PAKAI_NTP</code> to <code>true</code>.</li>
<li>Fill in <code>GANTI_NAMA_WIFI</code> and the password. Do not screenshot the password.</li>
<li>Upload again. Serial should print <code>Jam dinding WIB siap.</code> then a row such as <code>2026-08-14 10:15:03,27.4</code>.</li>
</ol>
<p>NTP uses <code>configTime</code> and <code>pool.ntp.org</code>. That is internet time, not a clock inside the chip. Without Wi-Fi, NTP does not run — set <code>PAKAI_NTP</code> back to <code>false</code>.</p>
<p>NTP references: <a href="https://www.ntppool.org/en/use.html" target="_blank" rel="noopener noreferrer">NTP Pool — how to use</a> (reasonable home-lab use) and the Espressif system-time documentation.</p>

<h2>Why a wrong clock wrecks a chart</h2>
<p>FS-45 will plot temperature against time. If the X axis uses <code>millis</code> from several power-ups, the points jump back to the start. The chart looks as if time reset, even though it is already afternoon in the real world.</p>
<p>WIB wall-clock time lets rows sort by date. That is why NTP is taught now, before the chart, not as decoration.</p>

<h2>If the card does not mount</h2>
HTML
            .$troubleshooting.<<<'HTML'
<ol>
<li><strong>CS is not GPIO 5.</strong> Do not move CS to another GPIO “because it looks closer”. SCK/MISO/MOSI must stay 18/19/23.</li>
<li><strong>The card is still NTFS or exFAT.</strong> Format again to FAT32 in File Explorer. Cards above 32 GB are often stubborn; switch to 8–32 GB.</li>
<li><strong>GND is not shared.</strong> The SD module, DHT22, and ESP32 must share GND.</li>
<li><strong>Wrong VCC.</strong> If 5V is printed, use 5V (the module regulates down to the card). If only 3V3 is printed, use 3V3. SPI signals stay 3.3 V.</li>
<li><strong>Fake card or loose slot.</strong> Try another branded card. Push the card until it clicks.</li>
<li><strong>An UNO SD library is installed.</strong> Stop using that. Use ESP32-core <code>SD.h</code>.</li>
<li><strong>DHT22 is unread while the card is ready.</strong> That is the GPIO 4 sensor, not the mount. Check 3V3 VCC and DATA.</li>
</ol>

<h2 id="fsiot-sd-checklist">Checklist before FS-37</h2>
<p>Tick only after doing the step. Target: <strong>10/10</strong>. Progress stays in this browser and is not sent to the server.</p>
<ul id="fsiot-sd-checklist-items">
<li>The card is formatted FAT32 in File Explorer (or Disk Utility / Disks).</li>
<li>Wiring is CS=5, SCK=18, MISO=19, MOSI=23, shared GND, VCC by the label.</li>
<li>DHT22 is wired to GPIO 4 by the printed labels.</li>
<li>The sketch uses ESP32-core <code>SD.h</code>, not an UNO SD library.</li>
<li>The first test uses <code>PAKAI_NTP = false</code>.</li>
<li>Serial Monitor prints <code>Kartu siap. Menulis /log.csv</code>.</li>
<li>Serial Monitor prints repeating <code>Tersimpan:</code>.</li>
<li><code>log.csv</code> opens on the computer with header <code>timestamp_ms,temperature_c</code>.</li>
<li>I understand millis is not wall-clock time.</li>
<li>I can explain that NTP needs Wi-Fi, or I already tried <code>PAKAI_NTP = true</code>.</li>
</ul>
<p><strong>How to check readiness:</strong> explain DHT22 → ESP32 → <code>log.csv</code> → PC in your own words. In FS-37, data on the card is sent again when Wi-Fi returns.</p>

<h2>Common mistakes</h2>
<ul>
<li><strong>Copying pin order from a photo.</strong> Photos are appearance only. Read the printed labels.</li>
<li><strong>Wiring a plastic SD adapter to GPIO.</strong> That adapter is for a laptop. ESP32 needs an SPI module.</li>
<li><strong>Thinking millis equals wall-clock time.</strong> That number resets every power-up.</li>
<li><strong>Turning NTP on without Wi-Fi.</strong> Serial will say Wi-Fi is not connected.</li>
<li><strong>Using 5 V logic on MOSI/MISO/SCK/CS.</strong> ESP32 is 3.3 V.</li>
<li><strong>A fake card or NTFS format.</strong> Change the card, format FAT32.</li>
<li><strong>Forgetting shared GND.</strong></li>
<li><strong>Opening MQTTX today.</strong> This lab does not need it.</li>
</ul>

<h2>Frequently asked questions</h2>
<h3>Why must the SD adapter not go to ESP32?</h3>
<p>The plastic adapter only changes card size for a full-size SD slot. It has no level shifting and no SPI header. ESP32 needs an SPI module board.</p>
<h3>My module says 5V, a friend’s says 3V3. Who is right?</h3>
<p>Both can be right. Follow the print on your board. SPI signals stay 3.3 V to GPIO.</p>
<h3>Why not the UNO SD library?</h3>
<p>That one is for AVR. ESP32 already ships <code>SD.h</code> in the core. Installing the wrong one confuses Verify.</p>
<h3>May I use a 64 GB card?</h3>
<p>It often fights Windows because of exFAT. Use 8–32 GB FAT32 for this lab.</p>
<h3>Why is MQTTX not opened?</h3>
<p>Today has one job: a file on the card. Sending again after Wi-Fi drops is FS-37.</p>
<h3>What is pool.ntp.org?</h3>
<p>A pool of internet time servers. A home lab may use it reasonably. Skip NTP without Wi-Fi.</p>

<h2>Sources</h2>
<ul>
<li><a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/api/sd.html" target="_blank" rel="noopener noreferrer">Espressif — SD (Arduino-ESP32)</a></li>
<li><a href="https://docs.arduino.cc/libraries/sd/" target="_blank" rel="noopener noreferrer">Arduino — SD library</a>. Documentation licensed under Creative Commons Attribution-Share Alike 4.0. Arduino and the Arduino logo are trademarks of Arduino S.r.l.</li>
<li><a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-installing-a-library/" target="_blank" rel="noopener noreferrer">Arduino — Installing libraries (IDE 2)</a></li>
<li><a href="https://github.com/adafruit/DHT-sensor-library" target="_blank" rel="noopener noreferrer">Adafruit DHT sensor library</a></li>
<li><a href="https://docs.espressif.com/projects/esp-idf/en/latest/esp32/api-reference/system/system_time.html" target="_blank" rel="noopener noreferrer">Espressif — System Time / NTP</a></li>
<li><a href="https://www.ntppool.org/en/use.html" target="_blank" rel="noopener noreferrer">NTP Pool — how to use</a></li>
<li><a href="https://commons.wikimedia.org/wiki/File:2015_Karta_microSD_z_adapterem_SD.jpg" target="_blank" rel="noopener noreferrer">2015 Karta microSD z adapterem SD</a> · Jacek Halicki · Wikimedia Commons · Creative Commons Attribution-Share Alike 4.0. Appearance example only; do not copy pin order; the adapter is not an SPI module.</li>
<li><a href="https://commons.wikimedia.org/wiki/File:SD_Card_Breakout_Board.jpg" target="_blank" rel="noopener noreferrer">SD Card Breakout Board</a> · oomlout · Wikimedia Commons · Creative Commons Attribution-Share Alike 2.0. Appearance example only; do not copy pin order from the photo.</li>
<li><a href="https://commons.wikimedia.org/wiki/File:DHT_22_Sensor.jpg" target="_blank" rel="noopener noreferrer">AM2302 DHT22 Sensor</a> · L293D · Wikimedia Commons · Creative Commons Attribution-Share Alike 4.0. Appearance example only; do not copy pin order from the photo.</li>
<li>Tool-order, wiring, CSV-flow, millis-versus-NTP, troubleshooting, Format, and Serial Monitor diagrams — Koding Indonesia (FS-36)</li>
</ul>

<h2>Next</h2>
<p><strong>In short:</strong> temperature now lives in <code>log.csv</code>. In <strong>FS-37</strong>, data on the card is sent again when Wi-Fi returns, so the demo window is not a total gap.</p>
HTML;
    }
}
