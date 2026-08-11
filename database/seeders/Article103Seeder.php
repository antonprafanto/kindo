<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class Article103Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@kodingindonesia.com')->first() ?? User::first();
        $category = Category::where('slug', 'iot-smart-device')->first() ?? Category::where('slug', 'esp32-arduino')->first();
        if (! $admin || ! $category) {
            throw new \RuntimeException('User atau kategori IoT tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-mosquitto-broker-lokal-mqttx';
        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        foreach (['fullstack-iot' => 'fullstack-iot', 'iot' => 'iot', 'mqtt' => 'mqtt', 'mosquitto' => 'mosquitto'] as $tagSlug => $tagName) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagName]);
        }

        $article = Article::updateOrCreate(['slug' => $slug], [
            'user_id' => $admin->id,
            'category_id' => $category->id,
            'title' => 'Mosquitto lokal: broker MQTT pertama di komputer sendiri',
            'title_en' => 'Local Mosquitto: your first MQTT broker on your own computer',
            'excerpt' => 'FS-33 / #103: pasang Mosquitto, jalankan broker lokal di 127.0.0.1:1883, lalu kirim pesan pertama lewat MQTTX tanpa broker publik.',
            'excerpt_en' => 'FS-33 / #103: install Mosquitto, run a local broker at 127.0.0.1:1883, and send a first MQTTX message without a public broker.',
            'body' => $this->body(),
            'body_en' => $this->bodyEn(),
            'status' => 'draft',
            'is_featured' => false,
            'published_at' => null,
            'seo_title' => 'Mosquitto Lokal di Windows: Broker MQTT Pertama — FS-33',
            'seo_title_en' => 'Local Mosquitto on Windows: First MQTT Broker — FS-33',
            'seo_description' => 'Panduan pemula memasang Mosquitto lokal dan menguji MQTTX di 127.0.0.1:1883 tanpa membuka firewall atau broker publik.',
            'seo_description_en' => 'Beginner guide to install local Mosquitto and test MQTTX at 127.0.0.1:1883 without opening a firewall or using a public broker.',
        ]);
        $article->tags()->sync(Tag::whereIn('slug', ['fullstack-iot', 'iot', 'mqtt', 'mosquitto'])->pluck('id'));

        foreach (['webp', 'jpg'] as $extension) {
            $source = public_path("images/fsiot/fs33-cover-mosquitto.{$extension}");
            $destination = "articles/covers/fs33-cover-mosquitto.{$extension}";
            if (is_file($source)) {
                Storage::disk('public')->put($destination, file_get_contents($source));
                $article->cover_image = $destination;
            }
        }
        $article->save();
    }

    private function figure(string $file, string $alt, string $caption): string
    {
        return '<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem"><img src="/images/fsiot/'.$file.'" alt="'.$alt.'" loading="eager" style="width:100%;height:auto;max-height:640px;object-fit:contain;border-radius:6px;background:#F5F5F0"><figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a">'.$caption.'</figcaption></figure>';
    }

    private function body(): string
    {
        $tools = $this->figure('fs33-tools-order.png', 'Urutan tools untuk memasang dan menguji broker Mosquitto lokal', '<strong>Urutan kerja aman:</strong> browser → installer Mosquitto → PowerShell → MQTTX. Diagram buatan Koding Indonesia (FS-33).');
        $local = $this->figure('fs33-local-only.png', 'MQTTX dan Mosquitto berada pada komputer yang sama', '<strong>Batas lab.</strong> <code>127.0.0.1</code> menunjuk komputer ini. Pesan tidak memakai broker publik dan tidak perlu keluar ke internet. Diagram buatan Koding Indonesia (FS-33).');
        $message = $this->figure('fs33-first-message.png', 'Alur pesan pertama melalui broker Mosquitto lokal', '<strong>Gambar utama — pesan pertama.</strong> MQTTX berlangganan topic, lalu MQTTX mengirim pesan ke Mosquitto. Broker meneruskannya kembali ke subscriber. Diagram buatan Koding Indonesia (FS-33).');
        $troubleshooting = $this->figure('fs33-troubleshooting.png', 'Tiga pemeriksaan saat MQTTX tidak dapat terhubung ke Mosquitto lokal', '<strong>Skema bantu.</strong> Periksa jendela broker, alamat <code>127.0.0.1</code>, lalu port <code>1883</code>. Diagram buatan Koding Indonesia (FS-33).');

        return <<<'HTML'
<h2>Pendahuluan — sekarang kita punya kantor pos sendiri</h2>
<p><strong>FS-33 / #103 (ini)</strong> adalah lab pertama setelah konsep MQTT di FS-32. Kita memasang <strong>Mosquitto</strong>, yaitu program broker MQTT, pada komputer sendiri. Hari ini belum memakai ESP32 dan belum membuka broker publik.</p>
<p><strong>Intinya:</strong> Mosquitto menerima pesan. MQTTX mengirim dan melihat pesan itu. Karena keduanya berada di komputer yang sama, alamat yang dipakai adalah <code>127.0.0.1</code>.</p>
<p><strong>Analogi:</strong> Mosquitto adalah kantor pos kecil di meja belajar. MQTTX adalah pengirim sekaligus penerima surat. Topic adalah nama kotak suratnya.</p>
<h2>Hasil yang dituju</h2><ul><li>Mosquitto berjalan di komputer sendiri.</li><li>MQTTX tersambung ke <code>127.0.0.1</code> pada port <code>1883</code>.</li><li>Satu pesan latihan terlihat kembali di MQTTX.</li><li>Tahu bahwa lab ini belum membuka akses dari router, HP, atau ESP32.</li></ul>
<h2>Persiapan — buka tool yang benar dulu</h2>
HTML
            .$tools.<<<'HTML'
<ol><li><strong>Buka browser.</strong> Siapkan untuk mengunduh Mosquitto dari situs resminya.</li><li><strong>Buka MQTTX setelah Mosquitto berjalan.</strong> MQTTX sudah dipasang pada FS-32 dan akan menjadi alat uji kita.</li><li><strong>Buka PowerShell hanya saat langkah Windows meminta perintah.</strong> Tidak perlu Arduino IDE, ESP32, kabel, IP router, atau akun broker publik.</li></ol>
<p><strong>Catatan keamanan:</strong> jangan menambah <code>listener</code>, jangan mengubah firewall, dan jangan membuka port router. Konfigurasi keamanan jaringan rumah serta autentikasi dibahas pada FS-49.</p>
<h2>Install Mosquitto — Windows (jalur utama)</h2><ol><li>Di browser, buka <a href="https://mosquitto.org/download/" target="_blank" rel="noopener noreferrer">halaman unduhan resmi Eclipse Mosquitto</a>.</li><li>Pada bagian <strong>Windows</strong>, pilih installer yang sesuai komputer kamu, lalu jalankan setelah selesai diunduh.</li><li>Ikuti pilihan bawaan installer. Lanjutkan hanya bila nama aplikasi dan sumbernya jelas Mosquitto.</li><li>Tutup installer. Belum perlu membuka MQTTX atau mengubah berkas konfigurasi.</li></ol><p><strong>Jika bingung memilih berkas:</strong> Windows modern umumnya memakai installer <em>x64</em>. Jika ragu, cek <strong>Settings → System → About</strong> atau minta bantuan orang yang memasang Windows.</p>
<h2>Install Mosquitto — macOS dan Ubuntu/Debian</h2><p><strong>macOS:</strong> buka aplikasi <strong>Terminal</strong>, lalu jalankan <code>brew install mosquitto</code>. Perintah ini memerlukan Homebrew.</p><p><strong>Ubuntu atau Debian:</strong> buka <strong>Terminal</strong>, lalu jalankan <code>sudo apt update</code> dan, setelah selesai, <code>sudo apt install -y mosquitto mosquitto-clients</code>. Ketik sandi akun komputer saat diminta; karakter sandi memang tidak terlihat. Rujukan instalasi per sistem operasi tersedia di <a href="https://mosquitto.org/download/" target="_blank" rel="noopener noreferrer">unduhan resmi Mosquitto</a>.</p>
<h2>Jalankan broker — Windows</h2><p><strong>Buka dulu PowerShell:</strong> tekan Start → ketik <strong>PowerShell</strong> → pilih <strong>Windows PowerShell</strong>. Tidak perlu memilih <em>Run as administrator</em>.</p><p>Salin perintah ini, tempelkan ke PowerShell, lalu tekan Enter:</p><pre><code>&amp; 'C:\Program Files\mosquitto\mosquitto.exe' -v</code></pre><p><strong>Hasil yang dicari:</strong> jendela PowerShell tetap terbuka dan menampilkan port <code>1883</code>. Biarkan jendela ini terbuka selama praktik. Huruf <code>-v</code> menampilkan catatan kerja broker agar masalah lebih mudah terlihat.</p><p><strong>Jika berkas tidak ditemukan:</strong> buka File Explorer → <code>C:\Program Files\mosquitto</code> → pastikan ada <code>mosquitto.exe</code>. Jika foldernya berbeda, selesaikan instalasi dahulu; jangan menebak alamat berkas.</p>
<h2>Jalankan broker — macOS dan Ubuntu/Debian</h2><p><strong>macOS:</strong> pada Terminal, jalankan <code>mosquitto -v</code>, lalu biarkan Terminal terbuka.</p><p><strong>Ubuntu atau Debian:</strong> jalankan <code>sudo systemctl enable --now mosquitto</code>, lalu <code>sudo systemctl status mosquitto --no-pager</code>. Cari <code>active (running)</code>, kemudian tekan <code>q</code>.</p><p>Menurut <a href="https://mosquitto.org/man/mosquitto-8.html" target="_blank" rel="noopener noreferrer">manual resmi Mosquitto</a>, broker tanpa listener tambahan memakai port <code>1883</code> pada loopback lokal. Inilah alasan lab memakai <code>127.0.0.1</code>, bukan IP router.</p>
<h2>Hubungkan MQTTX ke broker lokal</h2>
HTML
            .$local.<<<'HTML'
<ol><li>Buka <strong>MQTTX</strong>.</li><li>Buat koneksi baru dan beri nama <code>FS33 broker lokal</code>.</li><li>Isi <strong>Host</strong> dengan <code>127.0.0.1</code>.</li><li>Isi <strong>Port</strong> dengan <code>1883</code>.</li><li>Biarkan username dan password kosong untuk lab lokal ini, lalu tekan Connect.</li></ol><p><strong>Hasil yang dicari:</strong> MQTTX menunjukkan status tersambung. Jika gagal, jangan mengganti host menjadi alamat internet.</p>
<h2>Kirim pesan MQTT pertama lewat MQTTX</h2>
HTML
            .$message.<<<'HTML'
<ol><li>Buat subscription pada topic <code>kodingindonesia/fsiot/ruang-belajar/chat</code>.</li><li>Di area publish MQTTX, gunakan topic yang <strong>sama persis</strong>.</li><li>Isi pesan dengan <code>halo dari PC saya</code>, lalu tekan Publish.</li><li>Lihat daftar pesan di MQTTX. Pesan yang sama harus muncul sebagai bukti Mosquitto menerima dan meneruskan pesan.</li></ol><p><strong>Cara menguji:</strong> ganti isi pesan menjadi <code>pesan kedua</code>, lalu Publish lagi. Jika pesan baru terlihat, broker lokal sudah bekerja. Tidak perlu ESP32 untuk pengujian ini.</p>
<h2>Mengapa tidak perlu firewall atau konfigurasi listener?</h2><p>Lab memakai <code>127.0.0.1</code>, artinya hanya aplikasi di komputer yang sama yang berbicara dengan broker. Karena tidak ada perangkat lain dari jaringan, kita tidak perlu membuka firewall, port router, atau akses publik.</p><p>Jangan menyalin contoh internet berisi <code>listener 1883</code> atau <code>allow_anonymous true</code> untuk lab ini. Listener tambahan dapat membuka koneksi jaringan; anonymous access harus dipilih dengan sadar. Baca <a href="https://mosquitto.org/documentation/authentication-methods/" target="_blank" rel="noopener noreferrer">dokumentasi autentikasi Mosquitto</a> bila ingin memahami alasannya. Pengamanan praktik rumah ada pada FS-49.</p>
<h2>Jika MQTTX gagal terhubung</h2>
HTML
            .$troubleshooting.<<<'HTML'
<ol><li><strong>Jendela broker tertutup.</strong> Jalankan kembali perintah Mosquitto dan biarkan jendela tetap terbuka.</li><li><strong>Host salah.</strong> Gunakan <code>127.0.0.1</code>, bukan IP router.</li><li><strong>Port salah.</strong> Gunakan <code>1883</code>. MQTT bukan URL web, jadi jangan menulis <code>http://</code> atau <code>https://</code>.</li><li><strong>“Address already in use”.</strong> Broker lain mungkin sudah memakai port 1883. Tutup jendela Mosquitto yang ganda, lalu coba lagi.</li><li><strong>Peringatan firewall.</strong> Untuk praktik satu komputer, jangan membuat aturan baru. Pastikan host tetap <code>127.0.0.1</code>.</li></ol>
<h2 id="fsiot-mosquitto-checklist">Checklist sebelum FS-34</h2><p>Centang setelah benar-benar dilakukan. Target: <strong>10/10</strong>. Progres tersimpan di browser perangkatmu dan tidak dikirim ke server.</p><ul id="fsiot-mosquitto-checklist-items"><li>Mosquitto diunduh dari sumber resmi.</li><li>Mosquitto selesai dipasang.</li><li>Broker berjalan dan jendela atau layanan masih aktif.</li><li>MQTTX terhubung ke <code>127.0.0.1</code>.</li><li>Port koneksi adalah <code>1883</code>.</li><li>Topic latihan diketik sama persis.</li><li>Pesan <code>halo dari PC saya</code> berhasil dipublish.</li><li>Pesan terlihat kembali di MQTTX.</li><li>Saya tidak membuka firewall atau port router untuk lab ini.</li><li>Saya siap memakai ESP32 pada FS-34.</li></ul><p><strong>Cara memeriksa kesiapan:</strong> jelaskan alur MQTTX → Mosquitto → MQTTX dengan kata-katamu sendiri. Jika masih bingung, ulangi langkah koneksi dan pesan pertama; belum perlu membuka Arduino IDE.</p>
<h2>Selanjutnya</h2><p><strong>Ringkasnya:</strong> Mosquitto sudah menjadi kantor pos MQTT lokal kita. Pada <strong>FS-34</strong>, ESP32 akan bergabung sebagai publisher dan mengirim telemetry DHT22 dalam bentuk JSON ke broker yang sama.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $tools = $this->figure('fs33-tools-order.png', 'Tool order for installing and testing a local Mosquitto broker', '<strong>Safe work order:</strong> browser → Mosquitto installer → PowerShell → MQTTX. Diagram by Koding Indonesia (FS-33).');
        $local = $this->figure('fs33-local-only.png', 'MQTTX and Mosquitto on the same computer', '<strong>Lab boundary.</strong> <code>127.0.0.1</code> means this computer. Messages do not use a public broker or leave to the internet. Diagram by Koding Indonesia (FS-33).');
        $message = $this->figure('fs33-first-message.png', 'First message flow through a local Mosquitto broker', '<strong>Main figure — first message.</strong> MQTTX subscribes, then publishes to Mosquitto. The broker forwards the message to the subscriber. Diagram by Koding Indonesia (FS-33).');
        $troubleshooting = $this->figure('fs33-troubleshooting.png', 'Three checks when MQTTX cannot connect to local Mosquitto', '<strong>Helper schematic.</strong> Check the broker window, <code>127.0.0.1</code>, then port <code>1883</code>. Diagram by Koding Indonesia (FS-33).');
        return <<<'HTML'
<h2>Introduction — now we have our own post office</h2><p><strong>FS-33 / #103 (this article)</strong> is the first lab after FS-32 concepts. We install <strong>Mosquitto</strong>, an MQTT broker program, on our own computer. No ESP32 and no public broker are used today.</p><p><strong>In short:</strong> Mosquitto receives messages. MQTTX sends and sees them. Both are on the same computer, so the address is <code>127.0.0.1</code>.</p><p><strong>Analogy:</strong> Mosquitto is a small desk post office. MQTTX is sender and receiver; a topic is the mailbox name.</p>
<h2>Expected outcome</h2><ul><li>Mosquitto runs on your computer.</li><li>MQTTX connects to <code>127.0.0.1</code> on port <code>1883</code>.</li><li>A practice message appears in MQTTX.</li><li>You know this lab opens no router, phone, or ESP32 access.</li></ul>
<h2>Preparation — open the right tool first</h2>
HTML
            .$tools.<<<'HTML'
<ol><li><strong>Open a browser.</strong> Download Mosquitto from its official site.</li><li><strong>Open MQTTX after Mosquitto runs.</strong> MQTTX is the test tool installed in FS-32.</li><li><strong>Open PowerShell only when a Windows step requests a command.</strong> No Arduino IDE, ESP32, router IP, firewall change, or public-broker account is needed.</li></ol><p><strong>Security note:</strong> do not add a <code>listener</code>, change firewall rules, or open a router port. Home-network security and authentication come in FS-49.</p>
<h2>Install Mosquitto — Windows (main path)</h2><ol><li>Open the <a href="https://mosquitto.org/download/" target="_blank" rel="noopener noreferrer">official Eclipse Mosquitto download page</a>.</li><li>Under <strong>Windows</strong>, choose the matching installer and run it.</li><li>Keep the installer defaults. Continue only when the app name and source are clearly Mosquitto.</li><li>Close the installer. Do not open MQTTX or change configuration files yet.</li></ol><p><strong>If unsure:</strong> modern Windows normally uses an <em>x64</em> installer. Check <strong>Settings → System → About</strong> or ask for help if needed.</p>
<h2>Install Mosquitto — macOS and Ubuntu/Debian</h2><p><strong>macOS:</strong> open <strong>Terminal</strong>, then run <code>brew install mosquitto</code>. This needs Homebrew.</p><p><strong>Ubuntu or Debian:</strong> open <strong>Terminal</strong>, run <code>sudo apt update</code>, then run <code>sudo apt install -y mosquitto mosquitto-clients</code>. Password characters are intentionally hidden. See the <a href="https://mosquitto.org/download/" target="_blank" rel="noopener noreferrer">official download page</a> for platform-specific methods.</p>
<h2>Run the broker — Windows</h2><p><strong>Open PowerShell first:</strong> press Start → type <strong>PowerShell</strong> → choose <strong>Windows PowerShell</strong>. Do not use <em>Run as administrator</em>.</p><p>Paste this command and press Enter:</p><pre><code>&amp; 'C:\Program Files\mosquitto\mosquitto.exe' -v</code></pre><p><strong>Expected result:</strong> PowerShell remains open and shows port <code>1883</code>. Keep it open. The <code>-v</code> option shows broker activity.</p>
<h2>Run the broker — macOS and Ubuntu/Debian</h2><p><strong>macOS:</strong> run <code>mosquitto -v</code> in Terminal and keep it open.</p><p><strong>Ubuntu or Debian:</strong> run <code>sudo systemctl enable --now mosquitto</code>, then <code>sudo systemctl status mosquitto --no-pager</code>. Look for <code>active (running)</code>, then press <code>q</code>.</p><p>The <a href="https://mosquitto.org/man/mosquitto-8.html" target="_blank" rel="noopener noreferrer">official Mosquitto manual</a> says that a broker with no extra listener uses port <code>1883</code> on the local loopback interface.</p>
<h2>Connect MQTTX to the local broker</h2>
HTML
            .$local.<<<'HTML'
<ol><li>Open <strong>MQTTX</strong>.</li><li>Create a connection named <code>FS33 local broker</code>.</li><li>Set <strong>Host</strong> to <code>127.0.0.1</code>.</li><li>Set <strong>Port</strong> to <code>1883</code>.</li><li>Leave username and password empty for this local lab, then press Connect.</li></ol><p><strong>Expected result:</strong> MQTTX shows connected. Do not replace the host with an internet address if it fails.</p>
<h2>Send the first MQTTX message</h2>
HTML
            .$message.<<<'HTML'
<ol><li>Subscribe to <code>kodingindonesia/fsiot/ruang-belajar/chat</code>.</li><li>Use exactly the same topic in MQTTX's publish area.</li><li>Send <code>halo dari PC saya</code>.</li><li>See the message in MQTTX to prove Mosquitto received and forwarded it.</li></ol><p><strong>How to test:</strong> send a different message such as <code>pesan kedua</code>. If it appears, the local broker works. No ESP32 is needed.</p>
<h2>Why no firewall or listener configuration?</h2><p>This lab uses <code>127.0.0.1</code>, so only apps on the same computer reach the broker. There is no need to open a firewall, router port, or public access.</p><p>Do not copy examples containing <code>listener 1883</code> or <code>allow_anonymous true</code> into this lab. An extra listener can accept network connections; anonymous access must be chosen deliberately. Read the <a href="https://mosquitto.org/documentation/authentication-methods/" target="_blank" rel="noopener noreferrer">Mosquitto authentication documentation</a> for the reason. FS-49 covers security.</p>
<h2>If MQTTX cannot connect</h2>
HTML
            .$troubleshooting.<<<'HTML'
<ol><li><strong>The broker window is closed.</strong> Run Mosquitto again and keep the window open.</li><li><strong>The host is wrong.</strong> Use <code>127.0.0.1</code>, not a router IP.</li><li><strong>The port is wrong.</strong> Use <code>1883</code>; MQTT is not a web URL, so do not type <code>http://</code> or <code>https://</code>.</li><li><strong>“Address already in use”.</strong> A broker may already use port 1883. Close duplicate windows and retry.</li><li><strong>A firewall warning appears.</strong> Do not add a rule for this one-computer lab. Keep host <code>127.0.0.1</code>.</li></ol>
<h2 id="fsiot-mosquitto-checklist">Checklist before FS-34</h2><p>Tick only after doing the step. Target: <strong>10/10</strong>. Progress stays in this browser and is not sent to the server.</p><ul id="fsiot-mosquitto-checklist-items"><li>Mosquitto came from the official source.</li><li>Mosquitto is installed.</li><li>The broker window or service is active.</li><li>MQTTX connects to <code>127.0.0.1</code>.</li><li>The connection port is <code>1883</code>.</li><li>The practice topic matches exactly.</li><li>The message <code>halo dari PC saya</code> was published.</li><li>The message appears in MQTTX.</li><li>I did not open a firewall or router port.</li><li>I am ready to use ESP32 in FS-34.</li></ul><p><strong>How to check readiness:</strong> explain MQTTX → Mosquitto → MQTTX in your own words. Repeat the connection and first message if needed; do not open Arduino IDE yet.</p>
<h2>Next</h2><p><strong>In short:</strong> Mosquitto is now our local MQTT post office. In <strong>FS-34</strong>, ESP32 joins as a publisher and sends DHT22 telemetry as JSON to this same broker.</p>
HTML;
    }
}
