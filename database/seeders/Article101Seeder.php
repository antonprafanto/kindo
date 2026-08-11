<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class Article101Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $category = Category::where('slug', 'iot-smart-device')->first()
            ?? Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $category) {
            throw new \RuntimeException('User atau kategori iot-smart-device/esp32-arduino tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-web-server-lokal-sensor';
        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        foreach (['fullstack-iot' => 'fullstack-iot', 'iot' => 'iot', 'esp32' => 'esp32'] as $tagSlug => $tagName) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagName]);
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id' => $admin->id,
                'category_id' => $category->id,
                'title' => 'ESP32 web server lokal: pantau suhu DHT22 di browser',
                'title_en' => 'ESP32 local web server: monitor DHT22 temperature in a browser',
                'excerpt' => 'FS-31 / #101: ubah pembacaan DHT22 menjadi halaman monitor lokal. Upload dari Arduino IDE, salin IP dari Serial 115200, lalu buka di browser pada Wi-Fi yang sama.',
                'excerpt_en' => 'FS-31 / #101: turn a DHT22 reading into a local monitoring page. Upload from Arduino IDE, copy the IP from Serial at 115200, then open it in a browser on the same Wi-Fi.',
                'body' => $this->body(),
                'body_en' => $this->bodyEn(),
                'status' => 'draft',
                'is_featured' => false,
                'published_at' => null,
                'seo_title' => 'ESP32 Web Server Lokal DHT22 di Browser — Full Stack IoT #101',
                'seo_title_en' => 'ESP32 Local DHT22 Web Server in a Browser — Full Stack IoT #101',
                'seo_description' => 'Buat ESP32 menjadi web server lokal: baca DHT22, salin IP dari Serial 115200, lalu pantau suhu lewat browser satu Wi-Fi. FS-31 / #101.',
                'seo_description_en' => 'Make ESP32 a local web server: read DHT22, copy the IP from Serial at 115200, then monitor temperature in a browser on the same Wi-Fi. FS-31 / #101.',
            ]
        );

        if ($article->published_at !== null || $article->status !== 'draft') {
            $article->update(['status' => 'draft', 'published_at' => null]);
        }

        $article->tags()->sync(Tag::whereIn('slug', ['fullstack-iot', 'iot', 'esp32'])->pluck('id'));

        foreach (['webp', 'jpg'] as $extension) {
            $source = public_path("images/fsiot/fs31-cover-web-server.{$extension}");
            if (is_file($source)) {
                $destination = "articles/covers/fs31-cover-web-server.{$extension}";
                Storage::disk('public')->put($destination, file_get_contents($source));
                $article->update(['cover_image' => $destination]);
                break;
            }
        }

        $this->command?->info('✓ Artikel #101 / FS-31 tersimpan sebagai DRAFT: '.$article->title);
    }

    private function figure(string $asset, string $alt, string $caption): string
    {
        return '<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem">'
            .'<img src="/images/fsiot/'.$asset.'" alt="'.$alt.'" loading="eager" style="width:100%;height:auto;max-height:640px;object-fit:contain;border-radius:6px;background:#F5F5F0">'
            .'<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a">'.$caption.'</figcaption></figure>';
    }

    private function body(): string
    {
        $tools = $this->figure('fs31-tools-order.png', 'Urutan tools Arduino IDE, Serial Monitor, lalu browser', '<strong>Urutan meja kerja:</strong> siapkan DHT22 → buka Arduino IDE → Upload → salin IP dari Serial Monitor → buka browser. Diagram buatan Koding Indonesia (FS-31).');
        $core = $this->figure('fs31-webserver-core.png', 'Tiga bagian WebServer ESP32', '<strong>Intinya:</strong> <code>WebServer.h</code> ikut core ESP32, jadi tidak perlu Library Manager baru untuk server kecil ini. Pola <code>server.on</code> + <code>server.begin</code> + <code>server.handleClient</code> mengikuti <a href="https://github.com/espressif/arduino-esp32/blob/master/libraries/WebServer/examples/HelloServer/HelloServer.ino" rel="noopener noreferrer" target="_blank">contoh HelloServer resmi Espressif</a>. Diagram buatan Koding Indonesia (FS-31).');
        $network = $this->figure('fs31-local-network.png', 'ESP32 DHT22, router, dan HP pada jaringan lokal yang sama', '<strong>Gambar utama — browser meminta halaman dari ESP32.</strong> HP/laptop adalah klien yang meminta halaman; ESP32 adalah server kecil yang menjawab HTML. HTTP memang memakai pola klien–server: browser memulai permintaan lalu server memberi respons. <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Guides/Overview" rel="noopener noreferrer" target="_blank">MDN — HTTP overview</a>. Diagram buatan Koding Indonesia (FS-31).');
        $refresh = $this->figure('fs31-refresh-flow.png', 'Alur halaman browser diperbarui setiap lima detik', '<strong>Skema bantu — refresh sederhana.</strong> Halaman ini memakai <code>meta refresh</code> 5 detik agar pemantauan terasa hidup tanpa aplikasi tambahan. Ini belum dashboard real-time; tujuannya melihat konsep dasar dengan jelas. Diagram buatan Koding Indonesia (FS-31).');
        $success = $this->figure('fs31-success-browser.png', 'Contoh halaman sukses menampilkan suhu di browser', '<strong>Target tampilan:</strong> browser menunjukkan judul stasiun dan angka suhu. Angka contoh hanya ilustrasi; suhu kamu akan berbeda. Diagram UI buatan Koding Indonesia (FS-31).');
        $troubleshooting = $this->figure('fs31-troubleshooting.png', 'Tiga pemeriksaan saat halaman lokal ESP32 tidak terbuka', '<strong>Cek dekat dulu.</strong> IP ada di Serial → perangkat memakai Wi-Fi yang sama → alamat diketik di kolom alamat browser. Jaringan tamu dapat mengisolasi perangkat; alamat lokal seperti <code>192.168.x.x</code> berbeda dari <code>localhost</code>, yang hanya menunjuk ke perangkat yang sedang dipakai. <a href="https://developer.mozilla.org/en-US/docs/Web/Security/Defenses/Local_network_access" rel="noopener noreferrer" target="_blank">MDN — local network access</a>. Diagram buatan Koding Indonesia (FS-31).');

        return <<<'HTML'
<h2>Pendahuluan — ESP32 sekarang punya halaman sendiri</h2>
<p><strong>FS-31 / #101 (ini)</strong> adalah langkah pertama di fase <strong>CONNECTED</strong> setelah Wi-Fi dan HTTP dasar. Sebelumnya angka suhu DHT22 hanya terlihat di Serial Monitor. Sekarang ESP32 akan menjadi <strong>web server lokal</strong>: HP atau laptop membuka alamat IP ESP32 dan melihat suhu di browser.</p>
<p><strong>Intinya:</strong> tidak ada app store, akun cloud, Laragon, atau <code>php artisan</code> hari ini. Yang dibutuhkan hanya ESP32, DHT22, Arduino IDE, browser, dan satu jaringan Wi-Fi yang sama.</p>
<p><strong>Analogi:</strong> ESP32 seperti warung kecil di dalam jaringan rumah. Alamat IP adalah alamat warungnya. Browser di HP atau laptop datang membawa permintaan “tolong tampilkan halaman”, lalu ESP32 menjawab dengan HTML sederhana berisi suhu.</p>

<h2>Prasyarat dan hasil yang dituju</h2>
<ul>
  <li><strong>Sudah:</strong> Wi-Fi ESP32 dari FS-29 dan pembacaan DHT22 dari FS-21.</li>
  <li><strong>Hardware:</strong> ESP32-DevKitC-1, modul DHT22/AM2302, kabel USB data, jumper, serta Wi-Fi 2,4 GHz.</li>
  <li><strong>Hasil:</strong> buka <code>http://IP-ESP32/</code> dan lihat suhu tanpa memasang aplikasi.</li>
</ul>
<p><strong>Batas latihan:</strong> halaman ini hanya untuk jaringan lokal. Jangan membuka port router atau membuatnya dapat diakses dari internet. Keamanan dan akses jarak jauh dibahas jauh setelah fondasi ini kuat.</p>

<h2>Persiapan — pakai tool yang benar dulu</h2>
HTML
            .$tools.<<<'HTML'
<ol>
  <li><strong>Pastikan kabel DHT22 yang lama.</strong> VCC → 3V3, DATA → GPIO 4, GND → GND.</li>
  <li><strong>Buka Arduino IDE 2.</strong> Pilih <strong>ESP32 Dev Module</strong> dan port yang benar.</li>
  <li><strong>Jika DHT22 belum pernah berhasil:</strong> di panel kiri Arduino IDE, klik <strong>Library Manager</strong> (ikon buku), cari <em>DHT sensor library</em> dari Adafruit, lalu pasang. Saat IDE menawarkan dependensi, pasang juga <em>Adafruit Unified Sensor</em>.</li>
  <li><strong>Siapkan browser.</strong> Chrome, Firefox, Edge, atau Safari boleh. Jangan buka Laragon.</li>
</ol>
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem">
  <img src="/images/fsiot/fs21-dht22-breadboard.png" width="1238" height="741" alt="Rangkaian DHT22 di breadboard ke GPIO 4 ESP32" loading="eager" style="width:100%;height:auto;max-height:560px;object-fit:contain;border-radius:6px;background:#fff">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a"><strong>Rangkaian yang dipakai lagi:</strong> VCC → 3V3 · DATA → GPIO 4 · GND → GND. Diagram buatan Koding Indonesia (FS-21), dipakai ulang pada FS-31. Panduan sensor: <a href="https://learn.adafruit.com/dht/connecting-to-a-dhtxx-sensor" rel="noopener noreferrer" target="_blank">Adafruit — connecting a DHTxx sensor</a>.</figcaption>
</figure>
<p><strong>Catatan penting:</strong> jika sketch FS-21 belum pernah menampilkan suhu, selesaikan itu lebih dulu. Web server tidak dapat memperbaiki kabel atau library sensor yang belum benar.</p>
<p><strong>Urutan instalasi library jika baru pertama kali:</strong> buka <em>Library Manager</em> → ketik <code>DHT sensor library</code> → pilih <strong>by Adafruit</strong> → tekan <strong>Install</strong> → setujui pemasangan <code>Adafruit Unified Sensor</code> jika ditawarkan. Panduan resmi Arduino IDE: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-installing-a-library" rel="noopener noreferrer" target="_blank">Installing libraries</a>.</p>

<h2>Kenali perannya — server kecil, alamat IP, dan browser</h2>
HTML
            .$network.<<<'HTML'
<p>Alamat yang nanti terlihat di Serial biasanya mirip <code>192.168.1.42</code>. Angka itu hanya contoh. <strong>Salin angka dari Serial milikmu sendiri</strong>; router setiap rumah dapat memberi angka berbeda.</p>
<p><strong>Jangan pakai <code>localhost</code> di HP.</strong> Saat diketik di HP, <code>localhost</code> berarti “HP ini sendiri”, bukan ESP32. Gunakan IP ESP32 yang dicetak sketch.</p>

<h2>WebServer.h — bagian yang sudah tersedia</h2>
HTML
            .$core.<<<'HTML'
<p><code>WebServer server(80)</code> membuat pintu web pada port standar HTTP. <code>server.on("/", ...)</code> menentukan jawaban ketika browser membuka halaman utama. Di dalam <code>loop()</code>, <code>server.handleClient()</code> wajib dipanggil agar ESP32 mendengarkan permintaan dari browser.</p>

<h2>Praktik — tulis dan Upload sketch</h2>
<p><strong>Buka Arduino IDE dulu.</strong> Buat sketch baru, lalu simpan sebagai <code>FS31_web_server_suhu</code>. Ganti <code>YOUR_SSID</code> dan <code>YOUR_PASS</code> dengan Wi-Fi 2,4 GHz milikmu. Jangan kirim screenshot yang menampilkan sandi Wi-Fi.</p>
<pre><code class="language-cpp">#include &lt;WiFi.h&gt;
#include &lt;WebServer.h&gt;
#include &lt;DHT.h&gt;

const char* ssid = "YOUR_SSID";
const char* password = "YOUR_PASS";

#define DHTPIN 4
#define DHTTYPE DHT22

DHT dht(DHTPIN, DHTTYPE);
WebServer server(80);

float suhuC = NAN;
float kelembapan = NAN;
unsigned long terakhirBaca = 0;

void bacaDhtJikaWaktunya() {
  if (millis() - terakhirBaca &lt; 2000) return;

  terakhirBaca = millis();
  float suhuBaru = dht.readTemperature();
  float kelembapanBaru = dht.readHumidity();

  if (isnan(suhuBaru) || isnan(kelembapanBaru)) {
    Serial.println("DHT22 gagal dibaca — cek kabel atau library.");
    return;
  }

  suhuC = suhuBaru;
  kelembapan = kelembapanBaru;
  Serial.printf("Suhu: %.1f C | Kelembapan: %.1f %%\n", suhuC, kelembapan);
}

String halamanHtml() {
  String suhuTampil = isnan(suhuC) ? "menunggu sensor" : String(suhuC, 1) + " &deg;C";
  String lembapTampil = isnan(kelembapan) ? "menunggu sensor" : String(kelembapan, 1) + " %";

  return "&lt;!doctype html&gt;&lt;html lang='id'&gt;"
         "&lt;head&gt;&lt;meta charset='utf-8'&gt;"
         "&lt;meta http-equiv='refresh' content='5'&gt;"
         "&lt;meta name='viewport' content='width=device-width,initial-scale=1'&gt;"
         "&lt;title&gt;Stasiun Kindo&lt;/title&gt;"
         "&lt;style&gt;body{font-family:Arial,sans-serif;margin:2rem;background:#f5f5f0;color:#1a1a1a}"
         ".card{max-width:420px;padding:1.5rem;border:3px solid #1a1a1a;border-radius:12px;background:white}"
         ".angka{font-size:2rem;font-weight:bold;color:#0d47a1}&lt;/style&gt;&lt;/head&gt;"
         "&lt;body&gt;&lt;main class='card'&gt;&lt;h1&gt;Stasiun Ruang Belajar Kindo&lt;/h1&gt;"
         "&lt;p&gt;Monitor lokal ESP32 + DHT22&lt;/p&gt;"
         "&lt;p class='angka'&gt;Suhu: " + suhuTampil + "&lt;/p&gt;"
         "&lt;p&gt;Kelembapan: " + lembapTampil + "&lt;/p&gt;"
         "&lt;p&gt;&lt;small&gt;Halaman dimuat ulang tiap 5 detik.&lt;/small&gt;&lt;/p&gt;"
         "&lt;/main&gt;&lt;/body&gt;&lt;/html&gt;";
}

void tampilkanHalaman() {
  server.send(200, "text/html; charset=utf-8", halamanHtml());
}

void setup() {
  Serial.begin(115200);
  dht.begin();

  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);
  Serial.print("Menghubungkan ke Wi-Fi");

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println();
  Serial.println("Wi-Fi terhubung.");
  Serial.print("Buka browser: http://");
  Serial.println(WiFi.localIP());

  server.on("/", HTTP_GET, tampilkanHalaman);
  server.begin();
  Serial.println("Web server lokal siap.");
}

void loop() {
  bacaDhtJikaWaktunya();
  server.handleClient();
}</code></pre>
<p><strong>Cara menguji perintah di atas:</strong></p>
<ol>
  <li>Tekan <strong>Verify</strong> (ikon centang). Jika muncul error <code>DHT.h: No such file</code>, pasang dua library DHT pada langkah persiapan.</li>
  <li>Tekan <strong>Upload</strong>. Tunggu sampai Arduino IDE selesai mengirim sketch.</li>
  <li>Klik menu <strong>Tools</strong> di bagian atas Arduino IDE, lalu pilih <strong>Serial Monitor</strong>. Pada pilihan <em>baud rate</em> di panel Serial Monitor, pilih <strong>115200</strong> agar sama dengan <code>Serial.begin(115200)</code>.</li>
  <li>Tunggu hingga tertulis <code>Buka browser: http://...</code>. Salin seluruh alamat tersebut. Jika selama kira-kira 30 detik hanya muncul titik, ESP32 belum masuk Wi-Fi: periksa <code>YOUR_SSID</code> dan <code>YOUR_PASS</code> di sketch, perbaiki bila perlu, lalu tekan <strong>Upload</strong> lagi. Upload akan menyalakan ulang ESP32. Jika setelah Upload tidak ada tulisan baru di Serial, tekan tombol <strong>EN</strong> satu kali.</li>
  <li>Di HP/laptop yang memakai Wi-Fi sama, ketik alamat itu di <strong>kolom alamat</strong> browser, lalu tekan Enter. Gunakan <code>http://</code>, <strong>bukan</strong> <code>https://</code>.</li>
</ol>
<p>Contoh resmi Espressif juga memakai <code>WiFi.begin</code>, menunggu <code>WL_CONNECTED</code>, mencetak <code>WiFi.localIP()</code>, mendaftarkan rute, lalu memanggil <code>server.handleClient()</code>. Kita menambahkan pembacaan DHT22 dan halaman HTML yang lebih ramah dilihat.</p>
<p><strong>Mengapa harus 115200?</strong> Angka di Serial Monitor harus sama dengan <code>Serial.begin(115200)</code> pada sketch. Jika berbeda, tulisan dapat tampak acak. Lihat panduan resmi Arduino tentang <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-serial-monitor" rel="noopener noreferrer" target="_blank">Serial Monitor</a> dan <a href="https://docs.arduino.cc/language-reference/en/functions/communication/serial/begin/" rel="noopener noreferrer" target="_blank">Serial.begin()</a>.</p>

<h2>Refresh sederhana — mengapa angka dapat berubah?</h2>
HTML
            .$refresh.<<<'HTML'
<p>Tag <code>&lt;meta http-equiv='refresh' content='5'&gt;</code> meminta browser memuat ulang halaman setiap lima detik. Tidak perlu memasang aplikasi dan tidak perlu menyentuh tombol terus-menerus. Kelak, dashboard yang lebih maju bisa memakai cara lain; versi ini sengaja sederhana agar alurnya mudah diamati.</p>

<h2>Hasil yang diharapkan</h2>
HTML
            .$success.<<<'HTML'
<p>Jika suhu masih <em>menunggu sensor</em>, web server sebenarnya sudah hidup. Periksa DHT22: kabel DATA ke GPIO 4, library, dan jarak baca. Sketch tetap sengaja tidak mengirim angka palsu saat sensor gagal dibaca.</p>

<h2>Glosarium singkat</h2>
<ul>
  <li><strong>Web server:</strong> program yang menerima permintaan browser dan memberi halaman sebagai jawaban.</li>
  <li><strong>Klien:</strong> pihak yang meminta halaman; hari ini HP atau laptop dengan browser.</li>
  <li><strong>IP lokal:</strong> alamat perangkat di jaringan rumah, umumnya diawali <code>192.168.</code> atau <code>10.</code>.</li>
  <li><strong>HTML:</strong> teks berstruktur yang browser ubah menjadi halaman.</li>
  <li><strong>Refresh:</strong> browser meminta halaman baru lagi agar nilai tampak diperbarui.</li>
  <li><strong>Guest isolation:</strong> aturan Wi-Fi tamu yang dapat mencegah HP berbicara dengan ESP32.</li>
</ul>

<h2 id="fsiot-webserver-checklist">Praktik — checklist monitor lokal</h2>
<p>Centang setelah benar-benar dilakukan. Target: <strong>10/10</strong>. Checklist ini tersimpan di browser perangkatmu, bukan dikirim ke server.</p>
<ul id="fsiot-webserver-checklist-items">
  <li>DHT22 terhubung: VCC ke 3V3, DATA ke GPIO 4, GND ke GND</li>
  <li>SSID dan sandi Wi-Fi diisi tanpa membagikannya</li>
  <li>Board dan port yang benar dipilih di Arduino IDE</li>
  <li>Dua library DHT tersedia bila belum memasangnya pada FS-21</li>
  <li>Sketch <code>FS31_web_server_suhu</code> berhasil Verify</li>
  <li>Sketch berhasil Upload ke ESP32</li>
  <li>Serial Monitor memakai baud 115200</li>
  <li>Serial menampilkan alamat <code>http://...</code> milik ESP32</li>
  <li>HP/laptop memakai Wi-Fi yang sama, bukan jaringan tamu atau data seluler</li>
  <li>Browser menampilkan suhu atau status menunggu sensor</li>
</ul>
<p><strong>Cara menguji checklist:</strong> lakukan praktik di Arduino IDE dan browser dahulu, lalu centang setiap bukti yang sudah ada. Tidak perlu Laragon atau terminal.</p>

<h2>Kesalahan yang sering terjadi</h2>
HTML
            .$troubleshooting.<<<'HTML'
<ul>
  <li><strong>Mengetik IP contoh dari artikel.</strong> Gunakan hanya IP yang keluar di Serial Monitor sendiri.</li>
  <li><strong>HP memakai data seluler.</strong> Matikan sementara data seluler atau pastikan HP benar-benar masuk SSID yang sama.</li>
  <li><strong>Masuk Wi-Fi tamu.</strong> Beberapa router sengaja memisahkan perangkat tamu; pindah ke jaringan utama yang aman.</li>
  <li><strong>Alamat diketik di kotak pencarian.</strong> Ketik lengkap <code>http://</code> + IP di kolom alamat browser; jangan ganti menjadi <code>https://</code>.</li>
  <li><strong>Serial hanya mencetak titik.</strong> ESP32 belum mendapat IP. Periksa <code>YOUR_SSID</code> dan <code>YOUR_PASS</code>, lalu lakukan Upload ulang agar perbaikan masuk ke board. Tekan EN sekali hanya jika sesudah Upload Serial tidak mulai menulis lagi.</li>
  <li><strong>Mengubah banyak hal sekaligus.</strong> Mulai dari Serial: jika belum ada IP, masalahnya Wi-Fi; jika ada IP tetapi sensor gagal, periksa DHT22.</li>
  <li><strong>Membuka akses dari internet.</strong> Jangan port forwarding. Latihan ini cukup di LAN dan belum memakai autentikasi.</li>
</ul>

<h2>Selanjutnya</h2>
<p><strong>Intinya:</strong> ESP32 kini bukan hanya pembaca sensor; ia dapat menyajikan halaman kecil yang dibuka browser. Setelah angka lokal ini jelas, langkah CONNECTED berikutnya akan mengenalkan MQTT sebagai jalur pesan. Sementara itu, lihat daftar fase di <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $tools = $this->figure('fs31-tools-order.png', 'Tool order: Arduino IDE, Serial Monitor, then browser', '<strong>Desk order:</strong> prepare DHT22 → open Arduino IDE → Upload → copy the IP from Serial Monitor → open a browser. Diagram by Koding Indonesia (FS-31).');
        $core = $this->figure('fs31-webserver-core.png', 'Three parts of ESP32 WebServer', '<strong>In short:</strong> <code>WebServer.h</code> ships with the ESP32 core, so this small server needs no new Library Manager package. The <code>server.on</code> + <code>server.begin</code> + <code>server.handleClient</code> pattern follows Espressif’s official <a href="https://github.com/espressif/arduino-esp32/blob/master/libraries/WebServer/examples/HelloServer/HelloServer.ino" rel="noopener noreferrer" target="_blank">HelloServer example</a>. Diagram by Koding Indonesia (FS-31).');
        $network = $this->figure('fs31-local-network.png', 'ESP32 DHT22, router, and phone on the same local network', '<strong>Main figure — the browser asks ESP32 for a page.</strong> The phone/laptop is the client requesting a page; ESP32 is the small server answering with HTML. HTTP uses this client–server pattern: the browser starts a request and the server returns a response. <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Guides/Overview" rel="noopener noreferrer" target="_blank">MDN — HTTP overview</a>. Diagram by Koding Indonesia (FS-31).');
        $refresh = $this->figure('fs31-refresh-flow.png', 'Browser page refresh flow every five seconds', '<strong>Helper schematic — simple refresh.</strong> This page uses a 5-second <code>meta refresh</code> so monitoring feels alive without an extra app. It is not a real-time dashboard yet; the goal is to observe the basic flow clearly. Diagram by Koding Indonesia (FS-31).');
        $success = $this->figure('fs31-success-browser.png', 'Example successful browser page showing temperature', '<strong>Target display:</strong> the browser shows a station title and a temperature. The example number is only an illustration; yours depends on the sensor and room. UI diagram by Koding Indonesia (FS-31).');
        $troubleshooting = $this->figure('fs31-troubleshooting.png', 'Three checks when an ESP32 local page does not open', '<strong>Check nearby first.</strong> The IP exists on Serial → devices use the same Wi-Fi → the address is typed in the browser address bar. Guest networks can isolate devices; a local address such as <code>192.168.x.x</code> differs from <code>localhost</code>, which points only to the device currently in use. <a href="https://developer.mozilla.org/en-US/docs/Web/Security/Defenses/Local_network_access" rel="noopener noreferrer" target="_blank">MDN — local network access</a>. Diagram by Koding Indonesia (FS-31).');

        return <<<'HTML'
<h2>Introduction — ESP32 now has its own page</h2>
<p><strong>FS-31 / #101 (this article)</strong> is the first step in the <strong>CONNECTED</strong> phase after basic Wi-Fi and HTTP. Previously, the DHT22 temperature was visible only in Serial Monitor. Now ESP32 becomes a <strong>local web server</strong>: a phone or laptop opens the ESP32 IP address and sees the temperature in a browser.</p>
<p><strong>In short:</strong> no app store, cloud account, Laragon, or <code>php artisan</code> is needed today. You only need ESP32, DHT22, Arduino IDE, a browser, and one shared Wi-Fi network.</p>
<p><strong>Analogy:</strong> ESP32 is a tiny shop inside your home network. Its IP address is the shop address. A browser on a phone or laptop arrives with “please show the page”, then ESP32 answers with simple HTML containing the temperature.</p>

<h2>Prerequisites and target result</h2>
<ul>
  <li><strong>Already done:</strong> ESP32 Wi-Fi from FS-29 and DHT22 reading from FS-21.</li>
  <li><strong>Hardware:</strong> ESP32-DevKitC-1, DHT22/AM2302 module, data USB cable, jumpers, and 2.4 GHz Wi-Fi.</li>
  <li><strong>Result:</strong> open <code>http://ESP32-IP/</code> and see temperature without installing an app.</li>
</ul>
<p><strong>Lab boundary:</strong> this page is only for the local network. Do not open router ports or make it internet-accessible. Security and remote access come much later, after this foundation is solid.</p>

<h2>Preparation — use the right tool first</h2>
HTML
            .$tools.<<<'HTML'
<ol>
  <li><strong>Check the existing DHT22 wires.</strong> VCC → 3V3, DATA → GPIO 4, GND → GND.</li>
  <li><strong>Open Arduino IDE 2.</strong> Select <strong>ESP32 Dev Module</strong> and the correct port.</li>
  <li><strong>If DHT22 has never worked yet:</strong> click <strong>Library Manager</strong> (the book icon) in the Arduino IDE left panel, search for Adafruit’s <em>DHT sensor library</em>, then install it. If IDE offers dependencies, install <em>Adafruit Unified Sensor</em> too.</li>
  <li><strong>Prepare a browser.</strong> Chrome, Firefox, Edge, or Safari is fine. Do not open Laragon.</li>
</ol>
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem">
  <img src="/images/fsiot/fs21-dht22-breadboard.png" width="1238" height="741" alt="DHT22 breadboard wiring to ESP32 GPIO 4" loading="eager" style="width:100%;height:auto;max-height:560px;object-fit:contain;border-radius:6px;background:#fff">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a"><strong>Wiring reused here:</strong> VCC → 3V3 · DATA → GPIO 4 · GND → GND. Diagram by Koding Indonesia (FS-21), reused in FS-31. Sensor guide: <a href="https://learn.adafruit.com/dht/connecting-to-a-dhtxx-sensor" rel="noopener noreferrer" target="_blank">Adafruit — connecting a DHTxx sensor</a>.</figcaption>
</figure>
<p><strong>Important:</strong> if the FS-21 sketch has never printed a temperature, solve that first. A web server cannot fix unfinished sensor wiring or libraries.</p>
<p><strong>First-time library order:</strong> open <em>Library Manager</em> → type <code>DHT sensor library</code> → choose <strong>by Adafruit</strong> → press <strong>Install</strong> → accept <code>Adafruit Unified Sensor</code> if prompted. Official Arduino IDE guide: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-installing-a-library" rel="noopener noreferrer" target="_blank">Installing libraries</a>.</p>

<h2>Know the roles — small server, IP address, and browser</h2>
HTML
            .$network.<<<'HTML'
<p>The address later printed on Serial may look like <code>192.168.1.42</code>. That number is only an example. <strong>Copy the number from your own Serial output</strong>; each home router can assign a different one.</p>
<p><strong>Do not use <code>localhost</code> on a phone.</strong> On a phone, <code>localhost</code> means “this phone itself”, not ESP32. Use the ESP32 IP printed by the sketch.</p>

<h2>WebServer.h — the built-in parts</h2>
HTML
            .$core.<<<'HTML'
<p><code>WebServer server(80)</code> creates a web door on the standard HTTP port. <code>server.on("/", ...)</code> defines the answer when a browser opens the home page. Inside <code>loop()</code>, <code>server.handleClient()</code> must run so ESP32 listens for browser requests.</p>

<h2>Practice — write and Upload the sketch</h2>
<p><strong>Open Arduino IDE first.</strong> Create a new sketch and save it as <code>FS31_web_server_suhu</code>. Replace <code>YOUR_SSID</code> and <code>YOUR_PASS</code> with your own 2.4 GHz Wi-Fi. Do not share a screenshot containing the Wi-Fi password.</p>
<pre><code class="language-cpp">#include &lt;WiFi.h&gt;
#include &lt;WebServer.h&gt;
#include &lt;DHT.h&gt;

const char* ssid = "YOUR_SSID";
const char* password = "YOUR_PASS";

#define DHTPIN 4
#define DHTTYPE DHT22

DHT dht(DHTPIN, DHTTYPE);
WebServer server(80);

float suhuC = NAN;
float kelembapan = NAN;
unsigned long terakhirBaca = 0;

void bacaDhtJikaWaktunya() {
  if (millis() - terakhirBaca &lt; 2000) return;

  terakhirBaca = millis();
  float suhuBaru = dht.readTemperature();
  float kelembapanBaru = dht.readHumidity();

  if (isnan(suhuBaru) || isnan(kelembapanBaru)) {
    Serial.println("DHT22 failed — check wiring or libraries.");
    return;
  }

  suhuC = suhuBaru;
  kelembapan = kelembapanBaru;
  Serial.printf("Temperature: %.1f C | Humidity: %.1f %%\n", suhuC, kelembapan);
}

String halamanHtml() {
  String suhuTampil = isnan(suhuC) ? "waiting for sensor" : String(suhuC, 1) + " &deg;C";
  String lembapTampil = isnan(kelembapan) ? "waiting for sensor" : String(kelembapan, 1) + " %";

  return "&lt;!doctype html&gt;&lt;html lang='en'&gt;"
         "&lt;head&gt;&lt;meta charset='utf-8'&gt;"
         "&lt;meta http-equiv='refresh' content='5'&gt;"
         "&lt;meta name='viewport' content='width=device-width,initial-scale=1'&gt;"
         "&lt;title&gt;Kindo Station&lt;/title&gt;"
         "&lt;style&gt;body{font-family:Arial,sans-serif;margin:2rem;background:#f5f5f0;color:#1a1a1a}"
         ".card{max-width:420px;padding:1.5rem;border:3px solid #1a1a1a;border-radius:12px;background:white}"
         ".angka{font-size:2rem;font-weight:bold;color:#0d47a1}&lt;/style&gt;&lt;/head&gt;"
         "&lt;body&gt;&lt;main class='card'&gt;&lt;h1&gt;Kindo Learning Room Station&lt;/h1&gt;"
         "&lt;p&gt;Local ESP32 + DHT22 monitor&lt;/p&gt;"
         "&lt;p class='angka'&gt;Temperature: " + suhuTampil + "&lt;/p&gt;"
         "&lt;p&gt;Humidity: " + lembapTampil + "&lt;/p&gt;"
         "&lt;p&gt;&lt;small&gt;Page reloads every 5 seconds.&lt;/small&gt;&lt;/p&gt;"
         "&lt;/main&gt;&lt;/body&gt;&lt;/html&gt;";
}

void tampilkanHalaman() {
  server.send(200, "text/html; charset=utf-8", halamanHtml());
}

void setup() {
  Serial.begin(115200);
  dht.begin();

  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);
  Serial.print("Connecting to Wi-Fi");

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println();
  Serial.println("Wi-Fi connected.");
  Serial.print("Open in browser: http://");
  Serial.println(WiFi.localIP());

  server.on("/", HTTP_GET, tampilkanHalaman);
  server.begin();
  Serial.println("Local web server ready.");
}

void loop() {
  bacaDhtJikaWaktunya();
  server.handleClient();
}</code></pre>
<p><strong>How to test the commands above:</strong></p>
<ol>
  <li>Press <strong>Verify</strong> (check icon). If you see <code>DHT.h: No such file</code>, install the two DHT libraries from preparation.</li>
  <li>Press <strong>Upload</strong>. Wait for Arduino IDE to finish sending the sketch.</li>
  <li>Click the <strong>Tools</strong> menu at the top of Arduino IDE, then choose <strong>Serial Monitor</strong>. In the Serial Monitor <em>baud rate</em> selector, choose <strong>115200</strong> so it matches <code>Serial.begin(115200)</code>.</li>
  <li>Wait for <code>Open in browser: http://...</code>, then copy the full address. If only dots appear for about 30 seconds, ESP32 has not joined Wi-Fi: check <code>YOUR_SSID</code> and <code>YOUR_PASS</code> in the sketch, correct them if needed, then press <strong>Upload</strong> again. Upload restarts ESP32. If Serial shows no new text after Upload, press the <strong>EN</strong> button once.</li>
  <li>On a phone/laptop using the same Wi-Fi, type that address in the browser <strong>address bar</strong> and press Enter. Use <code>http://</code>, <strong>not</strong> <code>https://</code>.</li>
</ol>
<p>The official Espressif example also uses <code>WiFi.begin</code>, waits for <code>WL_CONNECTED</code>, prints <code>WiFi.localIP()</code>, registers a route, then calls <code>server.handleClient()</code>. We add DHT22 reading and an easier HTML page.</p>
<p><strong>Why 115200?</strong> The Serial Monitor number must match <code>Serial.begin(115200)</code> in the sketch. If it differs, text may look scrambled. See Arduino’s official guides to the <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-serial-monitor" rel="noopener noreferrer" target="_blank">Serial Monitor</a> and <a href="https://docs.arduino.cc/language-reference/en/functions/communication/serial/begin/" rel="noopener noreferrer" target="_blank">Serial.begin()</a>.</p>

<h2>Simple refresh — why can the number change?</h2>
HTML
            .$refresh.<<<'HTML'
<p>The <code>&lt;meta http-equiv='refresh' content='5'&gt;</code> tag asks the browser to reload the page every five seconds. No extra app and no constant button presses are needed. A later dashboard can use other approaches; this version stays simple so the flow is easy to observe.</p>

<h2>Expected result</h2>
HTML
            .$success.<<<'HTML'
<p>If the temperature still says <em>waiting for sensor</em>, the web server itself is alive. Check DHT22 DATA on GPIO 4, the libraries, and the read distance. The sketch intentionally does not send a fake number when the sensor fails.</p>

<h2>Short glossary</h2>
<ul>
  <li><strong>Web server:</strong> a program that receives browser requests and answers with a page.</li>
  <li><strong>Client:</strong> the page requester; today a phone or laptop browser.</li>
  <li><strong>Local IP:</strong> a device address on the home network, often beginning with <code>192.168.</code> or <code>10.</code>.</li>
  <li><strong>HTML:</strong> structured text that a browser turns into a page.</li>
  <li><strong>Refresh:</strong> the browser asks for a new page again so a value appears updated.</li>
  <li><strong>Guest isolation:</strong> a guest Wi-Fi rule that can prevent a phone from speaking to ESP32.</li>
</ul>

<h2 id="fsiot-webserver-checklist">Practice — local monitor checklist</h2>
<p>Tick only after doing the step. Target: <strong>10/10</strong>. This checklist stays in your device browser; it is not sent to a server.</p>
<ul id="fsiot-webserver-checklist-items">
  <li>DHT22 is wired: VCC to 3V3, DATA to GPIO 4, GND to GND</li>
  <li>The Wi-Fi SSID and password are filled in without sharing them</li>
  <li>The correct board and port are selected in Arduino IDE</li>
  <li>The two DHT libraries are available if they were not installed in FS-21</li>
  <li>The <code>FS31_web_server_suhu</code> sketch passes Verify</li>
  <li>The sketch uploads to ESP32</li>
  <li>Serial Monitor uses 115200 baud</li>
  <li>Serial prints the ESP32 <code>http://...</code> address</li>
  <li>The phone/laptop uses the same Wi-Fi, not guest Wi-Fi or cellular data</li>
  <li>The browser shows a temperature or waiting-for-sensor status</li>
</ul>
<p><strong>How to test the checklist:</strong> practice in Arduino IDE and the browser first, then tick each piece of evidence. No Laragon or terminal is needed.</p>

<h2>Common mistakes</h2>
HTML
            .$troubleshooting.<<<'HTML'
<ul>
  <li><strong>Typing the article’s example IP.</strong> Use only the IP from your own Serial Monitor.</li>
  <li><strong>The phone uses cellular data.</strong> Temporarily turn it off or confirm the phone really joined the same SSID.</li>
  <li><strong>Using guest Wi-Fi.</strong> Some routers intentionally separate guest devices; switch to the safe main network.</li>
  <li><strong>Typing the address into a search box.</strong> Type the complete <code>http://</code> + IP in the browser address bar; do not change it to <code>https://</code>.</li>
  <li><strong>Serial prints only dots.</strong> ESP32 has no IP yet. Check <code>YOUR_SSID</code> and <code>YOUR_PASS</code>, then Upload again so the correction reaches the board. Press EN once only if Serial does not start printing again after Upload.</li>
  <li><strong>Changing many things at once.</strong> Start with Serial: no IP means Wi-Fi; an IP with sensor failure means check DHT22.</li>
  <li><strong>Opening access to the internet.</strong> Do not port-forward. This LAN lab has no authentication yet.</li>
</ul>

<h2>Next</h2>
<p><strong>In short:</strong> ESP32 is now more than a sensor reader; it can serve a small page that a browser opens. After this local number is clear, the next CONNECTED step introduces MQTT as a message path. Until then, see the phase list at <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }
}
