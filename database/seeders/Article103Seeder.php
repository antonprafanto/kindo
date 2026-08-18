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
            if (is_file($source)) {
                $destination = "articles/covers/fs33-cover-mosquitto.{$extension}";
                Storage::disk('public')->put($destination, file_get_contents($source));
            }
        }
        $article->update([
            'cover_image' => 'https://kodingindonesia.com/images/fsiot/fs33-cover-mosquitto.webp',
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

    private function body(): string
    {
        $tools = $this->figure('fs33-tools-order.png', 'Urutan lima langkah: browser, halaman unduhan, pasang Mosquitto, PowerShell, lalu MQTTX', '<strong>Urutan meja kerja (lima langkah):</strong> browser → halaman unduhan resmi → pemasang Mosquitto → PowerShell untuk menjalankan broker → MQTTX. Baru sekarang Connect boleh. Diagram buatan Koding Indonesia (FS-33).');
        $downloads = $this->figure('fs33-mosquitto-downloads.png', 'Bagian Windows pada halaman unduhan resmi Eclipse Mosquitto', '<strong>Ini bagian yang harus diklik.</strong> Pada heading <strong>Windows</strong>, unduh berkas <code>x64.exe</code> (bukan x86, bukan Source .tar.gz). Sumber: <a href="https://mosquitto.org/download/" target="_blank" rel="noopener noreferrer">mosquitto.org/download</a> — Eclipse Mosquitto / Eclipse Foundation. Tangkapan layar 13 Agustus 2026. Mosquitto berlisensi Eclipse Public License 2.0 dan Eclipse Distribution License 1.0.');
        $local = $this->figure('fs33-local-only.png', 'MQTTX dan Mosquitto berada pada komputer yang sama', '<strong>Batas lab.</strong> <code>127.0.0.1</code> menunjuk komputer ini. Pesan tidak memakai broker publik dan tidak perlu keluar ke internet. Diagram buatan Koding Indonesia (FS-33).');
        $mqttx = $this->figure('fs33-mqttx-local.png', 'Ilustrasi jendela MQTTX yang sudah tersambung: Host 127.0.0.1, Port 1883', '<strong>MQTTX sudah tersambung ke komputer ini.</strong> Host = <code>127.0.0.1</code>, Port = <code>1883</code>. Jendela Mosquitto tetap terbuka. Jangan salin alamat broker dari screenshot internet. Ilustrasi buatan Koding Indonesia (FS-33), meniru tata letak aplikasi resmi <a href="https://mqttx.app/" target="_blank" rel="noopener noreferrer">MQTTX oleh EMQ</a> (Apache License 2.0). Screenshot jendela resmi tidak dipakai utuh karena menampilkan broker publik.');
        $message = $this->figure('fs33-first-message.png', 'Alur kiri ke kanan: MQTTX mengirim, Mosquitto meneruskan, MQTTX melihat pesan', '<strong>Gambar utama — pesan pertama.</strong> Baca dari kiri ke kanan: MQTTX mengirim → Mosquitto menerima lalu meneruskan → MQTTX melihat pesan. Satu jendela MQTTX boleh melakukan keduanya. Diagram buatan Koding Indonesia (FS-33).');
        $troubleshooting = $this->figure('fs33-troubleshooting.png', 'Tiga pemeriksaan saat MQTTX tidak dapat terhubung ke Mosquitto lokal', '<strong>Skema bantu.</strong> Periksa jendela broker, alamat <code>127.0.0.1</code>, lalu port <code>1883</code>. Diagram buatan Koding Indonesia (FS-33).');
        $install = $this->stepsCard([
            ['title' => 'Buka browser', 'text' => 'Pakai Chrome, Firefox, Edge, atau Safari. Jangan buka Arduino IDE dulu. PowerShell baru dibuka pada langkah 4.'],
            ['title' => 'Buka halaman unduhan resmi Mosquitto', 'text' => 'Ketik atau klik <a href="https://mosquitto.org/download/" target="_blank" rel="noopener noreferrer">mosquitto.org/download</a>. Gulir ke heading <strong>Windows</strong>. Jangan unduh berkas Source.'],
            ['title' => 'Jalankan pemasang Windows', 'text' => 'Pilih berkas <code>x64.exe</code>, lalu ikuti jendela pemasang. Jika muncul Microsoft Defender SmartScreen, pilih <strong>Informasi selengkapnya</strong> lalu <strong>Jalankan tetap</strong> hanya jika berkas itu berasal dari mosquitto.org.'],
            ['title' => 'Buka PowerShell hanya untuk perintah broker', 'text' => 'Tekan Start → ketik <strong>PowerShell</strong> → pilih <strong>Windows PowerShell</strong>. Tidak perlu <em>Run as administrator</em>. Perintahnya ada di bagian “Jalankan broker — Windows”.'],
            ['title' => 'Buka MQTTX setelah broker berjalan', 'text' => 'Baru sekarang klik <em>New Connection</em>. Isi Host <code>127.0.0.1</code> dan Port <code>1883</code>. Jika MQTTX belum ada, pasang dari <a href="https://mqttx.app/downloads" target="_blank" rel="noopener noreferrer">mqttx.app/downloads</a> seperti di FS-32, lalu kembali ke sini.'],
        ], '<strong>Cara menguji hari ini:</strong> bukti sukses = jendela Mosquitto tetap terbuka dan memuat angka <code>1883</code>, MQTTX berstatus tersambung, lalu teks <code>halo dari PC saya</code> muncul lagi di daftar pesan.');

        return <<<'HTML'
<h2>Pendahuluan — sekarang kita punya kantor pos sendiri</h2>
<p><strong>FS-33 / #103 (ini)</strong> adalah lab pertama setelah konsep MQTT di FS-32. Kita memasang <strong>Mosquitto</strong>, yaitu program broker MQTT, pada komputer sendiri. Hari ini belum memakai ESP32 dan belum membuka broker publik.</p>
<p><strong>Intinya:</strong> Mosquitto menerima pesan. MQTTX mengirim dan melihat pesan itu. Karena keduanya berada di komputer yang sama, alamat yang dipakai adalah <code>127.0.0.1</code>.</p>
<p><strong>Analogi:</strong> Mosquitto adalah kantor pos kecil di meja belajar. MQTTX adalah pengirim sekaligus penerima surat. Topic adalah nama kotak suratnya.</p>
<p>Di FS-32 kita sengaja <strong>belum</strong> menekan Connect. Hari ini Connect boleh, tetapi hanya ke komputer ini sendiri.</p>

<h2>Hasil yang dituju</h2>
<ul>
<li>Mosquitto berjalan di komputer sendiri.</li>
<li>MQTTX tersambung ke <code>127.0.0.1</code> pada port <code>1883</code>.</li>
<li>Satu pesan latihan terlihat kembali di MQTTX.</li>
<li>Tahu bahwa lab ini belum membuka akses dari router, HP, atau ESP32.</li>
</ul>
<p><strong>Batas lab hari ini:</strong> tidak ada Upload ke papan. Tidak ada Arduino IDE. Tidak ada IP Wi-Fi. Tidak ada firewall baru.</p>

<h2>Istilah yang dipakai hari ini</h2>
<ul>
<li><strong>Mosquitto</strong> — program broker MQTT yang kita pasang di komputer. Bukan MQTTX.</li>
<li><strong>MQTTX</strong> — aplikasi klien untuk mengirim dan melihat pesan. Bukan broker.</li>
<li><strong><code>127.0.0.1</code> / <code>localhost</code></strong> — alamat “komputer ini sendiri”. Di laptop, artinya laptop itu, bukan ESP32 dan bukan HP.</li>
<li><strong>Port <code>1883</code></strong> — nomor pintu MQTT yang biasa. Bukan alamat situs web; jangan menulis <code>http://</code>.</li>
<li><strong>PowerShell</strong> — jendela perintah Windows. Kita membukanya hanya untuk menjalankan Mosquitto.</li>
<li><strong>Broker publik</strong> — broker di internet milik pihak lain. Latihan ini tidak memakainya.</li>
<li><strong><code>listener</code></strong> — baris konfigurasi yang dapat membuka pintu broker ke jaringan. Lab ini tidak menambahkannya.</li>
<li><strong>Topic latihan</strong> — teks <code>kodingindonesia/fsiot/ruang-belajar/chat</code>. Pengirim dan penerima harus sama persis.</li>
</ul>

<h2>Persiapan — buka tool yang benar dulu</h2>
HTML
            .$tools.$install.$downloads.<<<'HTML'
<p><strong>Jika MQTTX belum terpasang:</strong> selesaikan pemasangan MQTTX Desktop dari <a href="https://mqttx.app/downloads" target="_blank" rel="noopener noreferrer">mqttx.app/downloads</a> dulu, lalu kembali ke langkah 5. Tanpa MQTTX, kita tidak punya alat untuk melihat pesan.</p>
<p><strong>Tips ponsel:</strong> jika diagram terasa kecil, gunakan fitur perbesar pada browser atau layar. Gambar tidak perlu diketuk sampai memenuhi layar agar teks di sekitarnya tetap terbaca.</p>
<p><strong>Catatan keamanan:</strong> jangan menambah <code>listener</code>, jangan mengubah firewall, dan jangan membuka port router. Konfigurasi keamanan jaringan rumah serta autentikasi dibahas pada FS-49.</p>

<h2>Pasang Mosquitto — Windows (jalur utama)</h2>
<ol>
<li>Di browser, buka <a href="https://mosquitto.org/download/" target="_blank" rel="noopener noreferrer">halaman unduhan resmi Eclipse Mosquitto</a>.</li>
<li>Pada bagian <strong>Windows</strong>, pilih installer <code>x64.exe</code> untuk komputer 64-bit, lalu jalankan setelah selesai diunduh.</li>
<li>Ikuti pilihan bawaan pemasang. Lanjutkan hanya bila nama aplikasi dan sumbernya jelas Mosquitto.</li>
<li>Tutup pemasang. Belum perlu membuka MQTTX atau mengubah berkas konfigurasi.</li>
</ol>
<p><strong>Jika bingung memilih berkas:</strong> Windows modern umumnya memakai installer <em>x64</em>. Jika ragu, buka <strong>Settings → System → About</strong> dan cari “64-bit”, atau minta bantuan orang yang memasang Windows.</p>
<p><strong>Jika muncul SmartScreen:</strong> pilih <strong>Informasi selengkapnya</strong>, lalu <strong>Jalankan tetap</strong> hanya jika tautan unduhan berasal dari mosquitto.org.</p>

<h2>Pasang Mosquitto — macOS dan Ubuntu/Debian</h2>
<p><strong>macOS:</strong> buka aplikasi <strong>Terminal</strong> dulu, lalu jalankan <code>brew install mosquitto</code>. Perintah ini memerlukan Homebrew. Jangan mengetik perintah itu di PowerShell Windows.</p>
<p><strong>Ubuntu atau Debian:</strong> buka <strong>Terminal</strong> dulu, lalu jalankan <code>sudo apt update</code> dan, setelah selesai, <code>sudo apt install -y mosquitto mosquitto-clients</code>. Ketik sandi akun komputer saat diminta; karakter sandi memang tidak terlihat. Rujukan instalasi per sistem operasi tersedia di <a href="https://mosquitto.org/download/" target="_blank" rel="noopener noreferrer">unduhan resmi Mosquitto</a>.</p>

<h2>Jalankan broker — Windows</h2>
<p><strong>Buka dulu PowerShell:</strong> tekan Start → ketik <strong>PowerShell</strong> → pilih <strong>Windows PowerShell</strong>. Tidak perlu memilih <em>Run as administrator</em>.</p>
<p><strong>Cara menempel perintah:</strong> salin baris di bawah, klik jendela PowerShell, lalu tekan <kbd>Ctrl</kbd>+<kbd>V</kbd> atau klik kanan. Setelah teks muncul, tekan Enter.</p>
<pre><code>&amp; 'C:\Program Files\mosquitto\mosquitto.exe' -v</code></pre>
<p><strong>Hasil yang dicari:</strong> jendela PowerShell tetap terbuka dan memuat angka <code>1883</code>. Biarkan jendela ini terbuka selama praktik. Huruf <code>-v</code> menampilkan catatan kerja broker agar masalah lebih mudah terlihat.</p>
<p><strong>Jika berkas tidak ditemukan:</strong> buka File Explorer → <code>C:\Program Files\mosquitto</code> → pastikan ada <code>mosquitto.exe</code>. Jika foldernya berbeda, selesaikan pemasangan dahulu; jangan menebak alamat berkas.</p>

<h2>Jalankan broker — macOS dan Ubuntu/Debian</h2>
<p><strong>macOS:</strong> buka <strong>Terminal</strong> dulu, jalankan <code>mosquitto -v</code>, lalu biarkan Terminal terbuka. Cari angka <code>1883</code>.</p>
<p><strong>Ubuntu atau Debian:</strong> buka <strong>Terminal</strong> dulu, jalankan <code>sudo systemctl enable --now mosquitto</code>, lalu <code>sudo systemctl status mosquitto --no-pager</code>. Cari <code>active (running)</code>, kemudian tekan <code>q</code>.</p>
<p>Menurut <a href="https://mosquitto.org/man/mosquitto-8.html" target="_blank" rel="noopener noreferrer">manual resmi Mosquitto</a>, broker tanpa listener tambahan memakai port <code>1883</code> pada loopback lokal. Inilah alasan lab memakai <code>127.0.0.1</code>, bukan IP router.</p>

<h2>Hubungkan MQTTX ke broker lokal</h2>
HTML
            .$local.$mqttx.<<<'HTML'
<ol>
<li>Pastikan jendela Mosquitto masih terbuka.</li>
<li>Buka <strong>MQTTX</strong>.</li>
<li>Sekarang klik <em>New Connection</em> dan beri nama <code>FS33 broker lokal</code>.</li>
<li>Isi <strong>Host</strong> dengan <code>127.0.0.1</code>.</li>
<li>Isi <strong>Port</strong> dengan <code>1883</code>.</li>
<li>Biarkan username dan password kosong untuk lab lokal ini, lalu tekan Connect.</li>
</ol>
<p><strong>Hasil yang dicari:</strong> MQTTX menunjukkan status tersambung. Jika gagal, jangan mengganti Host menjadi alamat internet.</p>

<h2>Kirim pesan MQTT pertama lewat MQTTX</h2>
HTML
            .$message.<<<'HTML'
<ol>
<li>Buat subscription pada topic <code>kodingindonesia/fsiot/ruang-belajar/chat</code>.</li>
<li>Di area publish MQTTX, gunakan topic yang <strong>sama persis</strong>.</li>
<li>Isi pesan dengan <code>halo dari PC saya</code>, lalu tekan Publish.</li>
<li>Lihat daftar pesan di MQTTX. Pesan yang sama harus muncul sebagai bukti Mosquitto menerima dan meneruskan pesan.</li>
</ol>
<p><strong>Catatan diagram:</strong> satu koneksi MQTTX boleh melakukan subscribe dan publish. Diagram memisahkan peran pengirim dan penerima hanya agar alurnya mudah dibaca dari kiri ke kanan.</p>
<p><strong>Cara menguji:</strong> ganti isi pesan menjadi <code>pesan kedua</code>, lalu Publish lagi. Jika pesan baru terlihat, broker lokal sudah bekerja. Tidak perlu ESP32 untuk pengujian ini.</p>

<h2>Mengapa tidak perlu firewall atau konfigurasi listener?</h2>
<p>Lab memakai <code>127.0.0.1</code>, artinya hanya aplikasi di komputer yang sama yang berbicara dengan broker. Karena tidak ada perangkat lain dari jaringan, kita tidak perlu membuka firewall, port router, atau akses publik.</p>
<p>Jangan menyalin contoh internet berisi <code>listener 1883</code> atau <code>allow_anonymous true</code> untuk lab ini. Listener tambahan dapat membuka koneksi jaringan; anonymous access harus dipilih dengan sadar. Baca <a href="https://mosquitto.org/documentation/authentication-methods/" target="_blank" rel="noopener noreferrer">dokumentasi autentikasi Mosquitto</a> bila ingin memahami alasannya. Pengamanan praktik rumah ada pada FS-49.</p>

<h2>Jika MQTTX gagal terhubung</h2>
HTML
            .$troubleshooting.<<<'HTML'
<ol>
<li><strong>Jendela broker tertutup.</strong> Jalankan kembali perintah Mosquitto dan biarkan jendela tetap terbuka.</li>
<li><strong>Host salah.</strong> Gunakan <code>127.0.0.1</code>, bukan IP router.</li>
<li><strong>Port salah.</strong> Gunakan <code>1883</code>. MQTT bukan URL web, jadi jangan menulis <code>http://</code> atau <code>https://</code>.</li>
<li><strong>“Address already in use”.</strong> Broker lain mungkin sudah memakai port 1883. Tutup jendela Mosquitto yang ganda, lalu coba lagi.</li>
<li><strong>Peringatan firewall.</strong> Untuk praktik satu komputer, jangan membuat aturan baru. Pastikan host tetap <code>127.0.0.1</code>.</li>
</ol>

<h2 id="fsiot-mosquitto-checklist">Checklist sebelum FS-34</h2>
<p>Centang setelah kamu benar-benar melakukan setiap poin. Target: <strong>10/10</strong>. Progres disimpan di browser perangkatmu dan tidak dikirim ke server.</p>
<ul id="fsiot-mosquitto-checklist-items">
<li>Mosquitto diunduh dari sumber resmi.</li>
<li>Mosquitto selesai dipasang.</li>
<li>Broker berjalan dan jendela atau layanan masih aktif.</li>
<li>MQTTX terhubung ke <code>127.0.0.1</code>.</li>
<li>Port koneksi adalah <code>1883</code>.</li>
<li>Topic latihan diketik sama persis.</li>
<li>Pesan <code>halo dari PC saya</code> berhasil dikirim (tombol Publish).</li>
<li>Pesan terlihat kembali di MQTTX.</li>
<li>Saya tidak membuka firewall atau port router untuk lab ini.</li>
<li>Saya siap memakai ESP32 pada FS-34.</li>
</ul>
<p><strong>Cara memeriksa kesiapan:</strong> jelaskan alur MQTTX → Mosquitto → MQTTX dengan kata-katamu sendiri. Jika masih bingung, ulangi langkah koneksi dan pesan pertama; belum perlu membuka Arduino IDE.</p>

<h2>Kesalahan yang sering terjadi</h2>
<ul>
<li><strong>Menghubungkan MQTTX sebelum Mosquitto berjalan.</strong> Broker harus terbuka dulu. Di FS-32 kita menahan Connect; hari ini Connect hanya setelah angka 1883 terlihat.</li>
<li><strong>Menyalin Host dari screenshot internet.</strong> Banyak gambar resmi menampilkan broker publik. Jangan disalin.</li>
<li><strong>Menutup jendela PowerShell terlalu cepat.</strong> Menutup jendela itu biasanya mematikan broker.</li>
<li><strong>Mengira MQTTX adalah broker.</strong> MQTTX adalah klien. Mosquitto adalah broker.</li>
<li><strong>Membuka Arduino IDE atau menyalakan ESP32 hari ini.</strong> Tidak ada sketch untuk diunggah. ESP32 menyusul di FS-34.</li>
<li><strong>Mengubah firewall atau menambah listener.</strong> Lab satu komputer tidak memerlukannya.</li>
</ul>

<h2>Pertanyaan yang sering muncul</h2>
<h3>MQTTX itu broker?</h3>
<p>Bukan. MQTTX adalah klien: alat untuk mengirim dan melihat pesan. Broker hari ini adalah Mosquitto.</p>
<h3>Kenapa FS-32 bilang jangan Connect, sekarang boleh?</h3>
<p>Waktu itu belum ada broker di komputer kita. Sekarang Mosquitto sudah berjalan di <code>127.0.0.1</code>, jadi Connect ke alamat itu aman untuk lab.</p>
<h3>Harus buka Arduino IDE?</h3>
<p>Tidak. ESP32 dan kode belum dipakai. Arduino IDE dibuka lagi saat lab telemetri DHT22 di FS-34.</p>
<h3>Kalau PowerShell menulis path tidak ditemukan?</h3>
<p>Pemasangan belum selesai, atau foldernya berbeda. Buka File Explorer ke <code>C:\Program Files\mosquitto</code> terlebih dahulu. Jangan menebak path.</p>
<h3>Boleh tutup jendela Mosquitto setelah Connect?</h3>
<p>Tidak. Biarkan jendela itu terbuka sampai lab selesai. Menutupnya biasanya memutus MQTTX.</p>

<h2>Sumber</h2>
<ul>
<li><a href="https://mosquitto.org/download/" target="_blank" rel="noopener noreferrer">Eclipse Mosquitto — halaman unduhan</a></li>
<li>Tangkapan layar halaman unduhan Mosquitto — <a href="https://mosquitto.org/download/" target="_blank" rel="noopener noreferrer">mosquitto.org/download</a>, Eclipse Foundation, 13 Agustus 2026. Lisensi perangkat lunak: Eclipse Public License 2.0 dan Eclipse Distribution License 1.0</li>
<li><a href="https://mosquitto.org/man/mosquitto-8.html" target="_blank" rel="noopener noreferrer">Manual Mosquitto (mosquitto-8)</a></li>
<li><a href="https://mosquitto.org/documentation/authentication-methods/" target="_blank" rel="noopener noreferrer">Dokumentasi autentikasi Mosquitto</a></li>
<li><a href="https://mqttx.app/downloads" target="_blank" rel="noopener noreferrer">Halaman unduhan MQTTX</a> · <a href="https://mqttx.app/docs" target="_blank" rel="noopener noreferrer">dokumentasi MQTTX</a> · aplikasi oleh EMQ, Apache License 2.0</li>
<li>Diagram urutan tools, batas lab, alur pesan, skema periksa, dan ilustrasi jendela MQTTX — Koding Indonesia (FS-33)</li>
</ul>

<h2>Selanjutnya</h2>
<p><strong>Ringkasnya:</strong> Mosquitto sudah menjadi kantor pos MQTT lokal kita. Pada <strong>FS-34</strong>, ESP32 akan bergabung sebagai pengirim dan mengirim telemetri DHT22 dalam bentuk JSON ke broker yang sama.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $tools = $this->figure('fs33-tools-order.png', 'Five-step tool order: browser, download page, install Mosquitto, PowerShell, then MQTTX', '<strong>Desk order (five steps):</strong> browser → official download page → Mosquitto installer → PowerShell to run the broker → MQTTX. Connect is allowed only now. Diagram by Koding Indonesia (FS-33).');
        $downloads = $this->figure('fs33-mosquitto-downloads.png', 'Windows section of the official Eclipse Mosquitto download page', '<strong>This is the part to click.</strong> Under the <strong>Windows</strong> heading, download the <code>x64.exe</code> file (not x86, not the Source .tar.gz). Source: <a href="https://mosquitto.org/download/" target="_blank" rel="noopener noreferrer">mosquitto.org/download</a> — Eclipse Mosquitto / Eclipse Foundation. Screenshot taken 13 August 2026. Mosquitto is licensed under the Eclipse Public License 2.0 and the Eclipse Distribution License 1.0.');
        $local = $this->figure('fs33-local-only.png', 'MQTTX and Mosquitto on the same computer', '<strong>Lab boundary.</strong> <code>127.0.0.1</code> means this computer. Messages do not use a public broker or leave to the internet. Diagram by Koding Indonesia (FS-33).');
        $mqttx = $this->figure('fs33-mqttx-local.png', 'Illustration of MQTTX already connected: Host 127.0.0.1, Port 1883', '<strong>MQTTX is already connected to this computer.</strong> Host = <code>127.0.0.1</code>, Port = <code>1883</code>. Keep the Mosquitto window open. Do not copy a broker address from an internet screenshot. Illustration by Koding Indonesia (FS-33), modelled on the official <a href="https://mqttx.app/" target="_blank" rel="noopener noreferrer">MQTTX by EMQ</a> layout (Apache License 2.0). The official window screenshot is not used as-is because it shows a public broker.');
        $message = $this->figure('fs33-first-message.png', 'Left-to-right flow: MQTTX sends, Mosquitto forwards, MQTTX sees the message', '<strong>Main figure — first message.</strong> Read left to right: MQTTX publishes → Mosquitto receives and forwards → MQTTX sees the message. One MQTTX window may do both. Diagram by Koding Indonesia (FS-33).');
        $troubleshooting = $this->figure('fs33-troubleshooting.png', 'Three checks when MQTTX cannot connect to local Mosquitto', '<strong>Helper schematic.</strong> Check the broker window, <code>127.0.0.1</code>, then port <code>1883</code>. Diagram by Koding Indonesia (FS-33).');
        $install = $this->stepsCard([
            ['title' => 'Open a browser', 'text' => 'Use Chrome, Firefox, Edge, or Safari. Do not open Arduino IDE yet. PowerShell opens only at step 4.'],
            ['title' => 'Open the official Mosquitto download page', 'text' => 'Go to <a href="https://mosquitto.org/download/" target="_blank" rel="noopener noreferrer">mosquitto.org/download</a>. Scroll to the <strong>Windows</strong> heading. Do not download the Source archive.'],
            ['title' => 'Run the Windows installer', 'text' => 'Choose the <code>x64.exe</code> file and follow the installer windows. If Microsoft Defender SmartScreen appears, choose <strong>More info</strong> then <strong>Run anyway</strong> only if the file came from mosquitto.org.'],
            ['title' => 'Open PowerShell only for the broker command', 'text' => 'Press Start → type <strong>PowerShell</strong> → choose <strong>Windows PowerShell</strong>. Do not use <em>Run as administrator</em>. The command is in “Run the broker — Windows”.'],
            ['title' => 'Open MQTTX after the broker is running', 'text' => 'Only now click <em>New Connection</em>. Set Host to <code>127.0.0.1</code> and Port to <code>1883</code>. If MQTTX is missing, install it from <a href="https://mqttx.app/downloads" target="_blank" rel="noopener noreferrer">mqttx.app/downloads</a> as in FS-32, then return here.'],
        ], '<strong>How to test today:</strong> success = the Mosquitto window stays open and shows <code>1883</code>, MQTTX is connected, and the text <code>halo dari PC saya</code> appears again in the message list.');

        return <<<'HTML'
<h2>Introduction — now we have our own post office</h2>
<p><strong>FS-33 / #103 (this article)</strong> is the first lab after the MQTT concepts in FS-32. We install <strong>Mosquitto</strong>, an MQTT broker program, on our own computer. No ESP32 and no public broker are used today.</p>
<p><strong>In short:</strong> Mosquitto receives messages. MQTTX sends and sees them. Both are on the same computer, so the address is <code>127.0.0.1</code>.</p>
<p><strong>Analogy:</strong> Mosquitto is a small desk post office. MQTTX is sender and receiver; a topic is the mailbox name.</p>
<p>In FS-32 we deliberately did <strong>not</strong> press Connect. Today Connect is allowed, but only to this computer itself.</p>

<h2>Expected outcome</h2>
<ul>
<li>Mosquitto runs on your computer.</li>
<li>MQTTX connects to <code>127.0.0.1</code> on port <code>1883</code>.</li>
<li>A practice message appears in MQTTX.</li>
<li>You know this lab opens no router, phone, or ESP32 access.</li>
</ul>
<p><strong>Today’s lab boundary:</strong> there is no board Upload. There is no Arduino IDE. There is no Wi-Fi IP. There is no new firewall rule.</p>

<h2>Terms used today</h2>
<ul>
<li><strong>Mosquitto</strong> — the MQTT broker program we install on the computer. Not MQTTX.</li>
<li><strong>MQTTX</strong> — the client app for sending and viewing messages. Not the broker.</li>
<li><strong><code>127.0.0.1</code> / <code>localhost</code></strong> — “this computer itself”. On a laptop it means that laptop, not ESP32 and not a phone.</li>
<li><strong>Port <code>1883</code></strong> — the usual MQTT door number. It is not a website URL; do not type <code>http://</code>.</li>
<li><strong>PowerShell</strong> — the Windows command window. We open it only to run Mosquitto.</li>
<li><strong>Public broker</strong> — a broker on the internet owned by someone else. This lab does not use one.</li>
<li><strong><code>listener</code></strong> — a config line that can open the broker to the network. This lab does not add one.</li>
<li><strong>Practice topic</strong> — the text <code>kodingindonesia/fsiot/ruang-belajar/chat</code>. Sender and receiver must match exactly.</li>
</ul>

<h2>Preparation — open the right tool first</h2>
HTML
            .$tools.$install.$downloads.<<<'HTML'
<p><strong>If MQTTX is not installed yet:</strong> finish MQTTX Desktop from <a href="https://mqttx.app/downloads" target="_blank" rel="noopener noreferrer">mqttx.app/downloads</a> first, then return to step 5. Without MQTTX we have no way to see messages.</p>
<p><strong>Phone tip:</strong> if a diagram feels small, use the browser or screen zoom. You do not need to tap the image to fill the screen; nearby text should stay readable.</p>
<p><strong>Security note:</strong> do not add a <code>listener</code>, change firewall rules, or open a router port. Home-network security and authentication come in FS-49.</p>

<h2>Install Mosquitto — Windows (main path)</h2>
<ol>
<li>Open the <a href="https://mosquitto.org/download/" target="_blank" rel="noopener noreferrer">official Eclipse Mosquitto download page</a>.</li>
<li>Under <strong>Windows</strong>, choose the <code>x64.exe</code> installer for a 64-bit PC and run it.</li>
<li>Keep the installer defaults. Continue only when the app name and source are clearly Mosquitto.</li>
<li>Close the installer. Do not open MQTTX or change configuration files yet.</li>
</ol>
<p><strong>If unsure:</strong> modern Windows normally uses an <em>x64</em> installer. Check <strong>Settings → System → About</strong> for “64-bit”, or ask for help if needed.</p>
<p><strong>If SmartScreen appears:</strong> choose <strong>More info</strong>, then <strong>Run anyway</strong> only if the download came from mosquitto.org.</p>

<h2>Install Mosquitto — macOS and Ubuntu/Debian</h2>
<p><strong>macOS:</strong> open the <strong>Terminal</strong> app first, then run <code>brew install mosquitto</code>. This needs Homebrew. Do not type that command in Windows PowerShell.</p>
<p><strong>Ubuntu or Debian:</strong> open <strong>Terminal</strong> first, run <code>sudo apt update</code>, then run <code>sudo apt install -y mosquitto mosquitto-clients</code>. Password characters are intentionally hidden. See the <a href="https://mosquitto.org/download/" target="_blank" rel="noopener noreferrer">official download page</a> for platform-specific methods.</p>

<h2>Run the broker — Windows</h2>
<p><strong>Open PowerShell first:</strong> press Start → type <strong>PowerShell</strong> → choose <strong>Windows PowerShell</strong>. Do not use <em>Run as administrator</em>.</p>
<p><strong>How to paste:</strong> copy the line below, click the PowerShell window, then press <kbd>Ctrl</kbd>+<kbd>V</kbd> or right-click. After the text appears, press Enter.</p>
<pre><code>&amp; 'C:\Program Files\mosquitto\mosquitto.exe' -v</code></pre>
<p><strong>Expected result:</strong> PowerShell remains open and shows port <code>1883</code>. Keep it open. The <code>-v</code> option shows broker activity.</p>
<p><strong>If the file is not found:</strong> open File Explorer → <code>C:\Program Files\mosquitto</code> → confirm <code>mosquitto.exe</code> is there. If the folder differs, finish installation first; do not guess the path.</p>

<h2>Run the broker — macOS and Ubuntu/Debian</h2>
<p><strong>macOS:</strong> open <strong>Terminal</strong> first, run <code>mosquitto -v</code>, and keep Terminal open. Look for <code>1883</code>.</p>
<p><strong>Ubuntu or Debian:</strong> open <strong>Terminal</strong> first, run <code>sudo systemctl enable --now mosquitto</code>, then <code>sudo systemctl status mosquitto --no-pager</code>. Look for <code>active (running)</code>, then press <code>q</code>.</p>
<p>The <a href="https://mosquitto.org/man/mosquitto-8.html" target="_blank" rel="noopener noreferrer">official Mosquitto manual</a> says that a broker with no extra listener uses port <code>1883</code> on the local loopback interface. That is why this lab uses <code>127.0.0.1</code>, not a router IP.</p>

<h2>Connect MQTTX to the local broker</h2>
HTML
            .$local.$mqttx.<<<'HTML'
<ol>
<li>Keep the Mosquitto window open.</li>
<li>Open <strong>MQTTX</strong>.</li>
<li>Now click <em>New Connection</em> and name it <code>FS33 local broker</code>.</li>
<li>Set <strong>Host</strong> to <code>127.0.0.1</code>.</li>
<li>Set <strong>Port</strong> to <code>1883</code>.</li>
<li>Leave username and password empty for this local lab, then press Connect.</li>
</ol>
<p><strong>Expected result:</strong> MQTTX shows connected. Do not replace Host with an internet address if it fails.</p>

<h2>Send the first MQTTX message</h2>
HTML
            .$message.<<<'HTML'
<ol>
<li>Subscribe to <code>kodingindonesia/fsiot/ruang-belajar/chat</code>.</li>
<li>Use exactly the same topic in MQTTX's publish area.</li>
<li>Send <code>halo dari PC saya</code>.</li>
<li>See the message in MQTTX to prove Mosquitto received and forwarded it.</li>
</ol>
<p><strong>Diagram note:</strong> one MQTTX connection may subscribe and publish. The diagram separates sender and receiver roles only so the flow reads left to right.</p>
<p><strong>How to test:</strong> send a different message such as <code>pesan kedua</code>. If it appears, the local broker works. No ESP32 is needed.</p>

<h2>Why no firewall or listener configuration?</h2>
<p>This lab uses <code>127.0.0.1</code>, so only apps on the same computer reach the broker. There is no need to open a firewall, router port, or public access.</p>
<p>Do not copy examples containing <code>listener 1883</code> or <code>allow_anonymous true</code> into this lab. An extra listener can accept network connections; anonymous access must be chosen deliberately. Read the <a href="https://mosquitto.org/documentation/authentication-methods/" target="_blank" rel="noopener noreferrer">Mosquitto authentication documentation</a> for the reason. FS-49 covers security.</p>

<h2>If MQTTX cannot connect</h2>
HTML
            .$troubleshooting.<<<'HTML'
<ol>
<li><strong>The broker window is closed.</strong> Run Mosquitto again and keep the window open.</li>
<li><strong>The host is wrong.</strong> Use <code>127.0.0.1</code>, not a router IP.</li>
<li><strong>The port is wrong.</strong> Use <code>1883</code>; MQTT is not a web URL, so do not type <code>http://</code> or <code>https://</code>.</li>
<li><strong>“Address already in use”.</strong> A broker may already use port 1883. Close duplicate windows and retry.</li>
<li><strong>A firewall warning appears.</strong> Do not add a rule for this one-computer lab. Keep host <code>127.0.0.1</code>.</li>
</ol>

<h2 id="fsiot-mosquitto-checklist">Checklist before FS-34</h2>
<p>Tick only after doing the step. Target: <strong>10/10</strong>. Progress stays in this browser and is not sent to the server.</p>
<ul id="fsiot-mosquitto-checklist-items">
<li>Mosquitto came from the official source.</li>
<li>Mosquitto is installed.</li>
<li>The broker window or service is active.</li>
<li>MQTTX connects to <code>127.0.0.1</code>.</li>
<li>The connection port is <code>1883</code>.</li>
<li>The practice topic matches exactly.</li>
<li>The message <code>halo dari PC saya</code> was sent (Publish button).</li>
<li>The message appears in MQTTX.</li>
<li>I did not open a firewall or router port.</li>
<li>I am ready to use ESP32 in FS-34.</li>
</ul>
<p><strong>How to check readiness:</strong> explain MQTTX → Mosquitto → MQTTX in your own words. Repeat the connection and first message if needed; do not open Arduino IDE yet.</p>

<h2>Common mistakes</h2>
<ul>
<li><strong>Connecting MQTTX before Mosquitto is running.</strong> The broker must be open first. FS-32 held Connect; today Connect happens only after 1883 is visible.</li>
<li><strong>Copying Host from an internet screenshot.</strong> Many official pictures show a public broker. Do not copy it.</li>
<li><strong>Closing the PowerShell window too soon.</strong> Closing it usually stops the broker.</li>
<li><strong>Thinking MQTTX is the broker.</strong> MQTTX is a client. Mosquitto is the broker.</li>
<li><strong>Opening Arduino IDE or powering ESP32 today.</strong> There is no sketch to upload. ESP32 comes in FS-34.</li>
<li><strong>Changing the firewall or adding a listener.</strong> A one-computer lab does not need that.</li>
</ul>

<h2>Frequently asked questions</h2>
<h3>Is MQTTX the broker?</h3>
<p>No. MQTTX is a client: a tool to send and view messages. Today’s broker is Mosquitto.</p>
<h3>Why did FS-32 say not to Connect, and now it is allowed?</h3>
<p>There was no broker on our computer then. Mosquitto now runs at <code>127.0.0.1</code>, so connecting to that address is safe for this lab.</p>
<h3>Do I need Arduino IDE?</h3>
<p>No. ESP32 and code are not used today. Arduino IDE returns for the DHT22 telemetry lab in FS-34.</p>
<h3>What if PowerShell says the path was not found?</h3>
<p>Installation is unfinished, or the folder is different. Open File Explorer to <code>C:\Program Files\mosquitto</code> first. Do not guess the path.</p>
<h3>May I close the Mosquitto window after Connect?</h3>
<p>No. Keep it open until the lab is finished. Closing it usually disconnects MQTTX.</p>

<h2>Sources</h2>
<ul>
<li><a href="https://mosquitto.org/download/" target="_blank" rel="noopener noreferrer">Eclipse Mosquitto — download page</a></li>
<li>Screenshot of the Mosquitto download page — <a href="https://mosquitto.org/download/" target="_blank" rel="noopener noreferrer">mosquitto.org/download</a>, Eclipse Foundation, 13 August 2026. Software licence: Eclipse Public License 2.0 and Eclipse Distribution License 1.0</li>
<li><a href="https://mosquitto.org/man/mosquitto-8.html" target="_blank" rel="noopener noreferrer">Mosquitto manual (mosquitto-8)</a></li>
<li><a href="https://mosquitto.org/documentation/authentication-methods/" target="_blank" rel="noopener noreferrer">Mosquitto authentication documentation</a></li>
<li><a href="https://mqttx.app/downloads" target="_blank" rel="noopener noreferrer">MQTTX downloads</a> · <a href="https://mqttx.app/docs" target="_blank" rel="noopener noreferrer">MQTTX documentation</a> · app by EMQ, Apache License 2.0</li>
<li>Tool-order, lab-boundary, message-flow, troubleshooting, and MQTTX-window diagrams — Koding Indonesia (FS-33)</li>
</ul>

<h2>Next</h2>
<p><strong>In short:</strong> Mosquitto is now our local MQTT post office. In <strong>FS-34</strong>, ESP32 joins as a publisher and sends DHT22 telemetry as JSON to this same broker.</p>
HTML;
    }
}
