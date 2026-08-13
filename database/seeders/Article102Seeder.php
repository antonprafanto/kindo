<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class Article102Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $category = Category::where('slug', 'iot-smart-device')->first() ?? Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $category) {
            throw new \RuntimeException('User atau kategori IoT tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-mqtt-broker-topic-publish-subscribe';
        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        foreach (['fullstack-iot' => 'fullstack-iot', 'iot' => 'iot', 'mqtt' => 'mqtt'] as $tagSlug => $tagName) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagName]);
        }

        $article = Article::updateOrCreate(['slug' => $slug], [
            'user_id' => $admin->id,
            'category_id' => $category->id,
            'title' => 'MQTT untuk pemula: broker, topic, publish, dan subscribe',
            'title_en' => 'MQTT for beginners: broker, topic, publish, and subscribe',
            'excerpt' => 'FS-32 / #102: pahami jalur pesan MQTT di browser, lalu pasang MQTTX. Belum perlu terminal, Arduino IDE, ESP32, kode, atau broker publik.',
            'excerpt_en' => 'FS-32 / #102: learn the MQTT message path in a browser, then install MQTTX. No terminal, Arduino IDE, ESP32, code, or public broker yet.',
            'body' => $this->body(),
            'body_en' => $this->bodyEn(),
            'status' => 'draft',
            'is_featured' => false,
            'published_at' => null,
            'seo_title' => 'MQTT untuk Pemula: Broker, Topic, Publish, Subscribe — FS-32',
            'seo_title_en' => 'MQTT for Beginners: Broker, Topic, Publish, Subscribe — FS-32',
            'seo_description' => 'Pelajari broker, topic, publish, dan subscribe MQTT dengan bahasa sederhana. Buka browser, pasang MQTTX, lalu lanjut ke Mosquitto lokal di FS-33.',
            'seo_description_en' => 'Learn MQTT broker, topics, publish, and subscribe in plain language. Open a browser, install MQTTX, then continue to local Mosquitto in FS-33.',
        ]);

        $article->tags()->sync(Tag::whereIn('slug', ['fullstack-iot', 'iot', 'mqtt'])->pluck('id'));

        foreach (['webp', 'jpg'] as $extension) {
            $source = public_path("images/fsiot/fs32-cover-mqtt.{$extension}");
            if (is_file($source)) {
                $destination = "articles/covers/fs32-cover-mqtt.{$extension}";
                Storage::disk('public')->put($destination, file_get_contents($source));
            }
        }
        $article->update([
            'cover_image' => 'https://kodingindonesia.com/images/fsiot/fs32-cover-mqtt.webp',
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
        $colors = ['#2979FF', '#2979FF', '#FF7A2F', '#1a1a1a'];
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
        $tools = $this->figure('fs32-tools-order.png', 'Urutan tools: browser, unduh MQTTX, pasang, jangan connect', '<strong>Urutan meja kerja:</strong> pahami konsep di browser → unduh MQTTX dari situs resmi → pasang tanpa terminal → berhenti. Pesan yang sebenarnya baru dikirim saat broker lokal FS-33 siap. Diagram buatan Koding Indonesia (FS-32).');
        $downloads = $this->figure('fs32-mqttx-downloads.png', 'Halaman unduhan resmi MQTTX Desktop di mqttx.app/downloads', '<strong>Ini halaman yang harus dibuka.</strong> Pilih sistem operasimu, lalu unduh <strong>MQTTX Desktop</strong> (berkas <code>.exe</code> di Windows). Jangan pilih MQTTX CLI, MQTTX Web, atau tautan broker publik di menu situs. Sumber: <a href="https://mqttx.app/downloads" target="_blank" rel="noopener noreferrer">mqttx.app/downloads</a> — EMQ Technologies. Tangkapan layar 13 Agustus 2026. Aplikasi MQTTX berlisensi Apache License 2.0.');
        $mqttx = $this->figure('fs32-mqttx-empty.png', 'Ilustrasi jendela MQTTX yang baru dibuka tanpa koneksi — tampilan sukses, bukan error', '<strong>Ini tampilan yang benar, bukan error.</strong> Jendela MQTTX terbuka dan daftar koneksi masih kosong = sukses hari ini. Jangan klik <em>New Connection</em> dan jangan isi Host. Ilustrasi buatan Koding Indonesia (FS-32), meniru tata letak aplikasi resmi <a href="https://mqttx.app/" target="_blank" rel="noopener noreferrer">MQTTX oleh EMQ</a> (Apache License 2.0). Screenshot jendela resmi tidak dipakai utuh karena menampilkan broker publik.');
        $roles = $this->figure('fs32-broker-roles.png', 'Peran klien dan broker MQTT', '<strong>Gambar utama — peran MQTT.</strong> ESP32 dan MQTTX adalah klien (<em>client</em>). Broker menjadi perantara pesan. MQTT adalah protokol klien-server dengan pola publish/subscribe. <a href="https://www.oasis-open.org/standard/mqtt-v5-0-os/" target="_blank" rel="noopener noreferrer">Standar MQTT OASIS</a>. Diagram buatan Koding Indonesia (FS-32).');
        $commons = $this->figure('fs32-mqtt-architecture-cite.png', 'Arsitektur MQTT dari Wikimedia Commons dengan label Indonesia', '<strong>Gambar pembanding.</strong> Alur yang sama: pengirim → broker → penerima. Label Indonesia ditambahkan di atas teks Portugis asli agar mudah dibaca. Ikon mobil dan awan berasal dari sumber; lab kita memakai ESP32, MQTTX, dan Mosquitto lokal — bukan internet publik. Sumber: <a href="https://commons.wikimedia.org/wiki/File:Arquitetura_MQTT_exemplo.png" target="_blank" rel="noopener noreferrer">Arquitetura MQTT exemplo.png</a> karya Ana beloti, Wikimedia Commons, <a href="https://creativecommons.org/licenses/by-sa/4.0/" target="_blank" rel="noopener noreferrer">CC BY-SA 4.0</a>. Diolah Koding Indonesia: label Indonesia ditambahkan.');
        $topic = $this->figure('fs32-topic-address.png', 'Empat bagian alamat topic MQTT', '<strong>Topic seperti alamat loker.</strong> Pengirim dan penerima harus menulis teks yang sama persis. Huruf besar dan huruf kecil berbeda. Diagram buatan Koding Indonesia (FS-32).');
        $flow = $this->figure('fs32-pub-sub-flow.png', 'Alur publish dan subscribe melalui broker', '<strong>Skema bantu — satu ke banyak.</strong> Publisher tidak perlu tahu siapa yang menerima; broker meneruskan pesan kepada klien yang berlangganan topic tersebut. Kotak <em>klien lain</em> hanya pengingat bahwa nanti bisa ada penerima tambahan; hari ini tidak perlu. Diagram buatan Koding Indonesia (FS-32).');
        $install = $this->stepsCard([
            ['title' => 'Buka browser', 'text' => 'Pakai Chrome, Firefox, Edge, atau Safari. Jangan buka Arduino IDE, PowerShell, atau Command Prompt.'],
            ['title' => 'Buka halaman unduhan resmi MQTTX', 'text' => 'Ketik atau klik <a href="https://mqttx.app/downloads" target="_blank" rel="noopener noreferrer">mqttx.app/downloads</a>. Pilih <strong>MQTTX Desktop</strong> untuk Windows, macOS, atau Linux. Jangan unduh MQTTX CLI atau MQTTX Web untuk lab ini.'],
            ['title' => 'Jalankan pemasang, lalu buka aplikasinya', 'text' => 'Di Windows, pilih berkas <code>.exe</code> lalu ikuti jendela pemasang. Jika muncul Microsoft Defender SmartScreen, pilih <strong>Informasi selengkapnya</strong> lalu <strong>Jalankan tetap</strong> hanya jika berkas itu berasal dari mqttx.app. Setelah selesai, buka MQTTX.'],
            ['title' => 'Berhenti. Jangan Connect', 'text' => 'Cukup sampai jendela aplikasi terbuka. Jangan klik <em>New Connection</em>, jangan isi Host, dan jangan menyalin alamat broker dari internet.'],
        ], '<strong>Cara menguji hari ini:</strong> tidak ada perintah terminal dan tidak ada kode untuk dijalankan. Bukti sukses = MQTTX terbuka. Pesan pertama baru diuji di FS-33.');

        return <<<'HTML'
<h2>Pendahuluan — MQTT adalah jalur pesan, bukan halaman web</h2>
<p><strong>FS-32 / #102 (ini)</strong> mengenalkan MQTT sebelum kita memakai broker lokal di FS-33. Di FS-31, browser meminta halaman ke ESP32. Pada MQTT, perangkat tidak perlu saling membuka halaman; mereka menitipkan dan mengambil <strong>pesan</strong>.</p>
<p><strong>Intinya:</strong> hari ini belum menulis sketch, belum menyalakan ESP32, dan belum memakai broker publik. Buka browser untuk membaca, lalu pasang satu aplikasi klien bernama MQTTX.</p>
<p><strong>Analogi:</strong> broker seperti kantor pos. Klien (<em>client</em>) adalah pengirim atau penerima surat. Topic adalah alamat loker. <em>Publish</em> berarti menitipkan surat ke loker itu; <em>subscribe</em> berarti meminta salinan setiap surat yang masuk ke loker tersebut.</p>

<h2>Hasil yang dituju</h2>
<ul>
<li>Mampu menyebut broker, klien, topic, publish, dan subscribe dengan bahasa sendiri.</li>
<li>MQTTX sudah terpasang, atau kamu tahu bagian pemasangan yang masih kurang.</li>
<li>Tahu bahwa <code>localhost</code> dan <code>127.0.0.1</code> nanti berarti komputer yang sedang kamu pakai, bukan ESP32.</li>
</ul>
<p><strong>Batas lab hari ini:</strong> tidak ada sintaks untuk diuji di terminal. Tidak ada Upload ke papan. Tidak ada koneksi ke broker.</p>

<h2>Istilah yang dipakai hari ini</h2>
<ul>
<li><strong>MQTT</strong> — protokol jalur pesan untuk IoT; bukan halaman web.</li>
<li><strong>Broker</strong> — program perantara, analoginya kantor pos.</li>
<li><strong>Klien (client)</strong> — pengirim atau penerima yang tersambung ke broker. ESP32 dan MQTTX keduanya klien.</li>
<li><strong>Topic</strong> — alamat teks tempat pesan dititipkan, bukan URL browser.</li>
<li><strong>Publish</strong> — mengirim pesan ke sebuah topic.</li>
<li><strong>Subscribe</strong> — berlangganan salinan pesan dari topic yang sama.</li>
<li><strong>MQTTX</strong> — aplikasi di komputer untuk melihat dan mengirim pesan MQTT.</li>
<li><strong>Broker publik</strong> — broker di internet milik pihak lain. Latihan ini tidak memakainya.</li>
<li><strong><code>localhost</code> / <code>127.0.0.1</code></strong> — alamat “komputer ini sendiri”. Di HP, artinya HP itu; di ESP32, artinya ESP32 itu.</li>
</ul>

<h2>Persiapan — buka tool yang benar dulu</h2>
HTML
            .$tools.$install.$downloads.$mqttx.<<<'HTML'
<p><strong>Jika pemasangan belum selesai:</strong> tetap baca konsep sampai akhir. FS-33 baru dikerjakan setelah MQTTX siap, karena di sana kita akan melihat pesan yang sebenarnya.</p>
<p><strong>Tips ponsel:</strong> jika diagram terasa kecil, gunakan fitur perbesar pada browser atau layar. Gambar tidak perlu diketuk sampai memenuhi layar agar teks di sekitarnya tetap terbaca.</p>

<h2>Siapa melakukan apa?</h2>
HTML
            .$roles.$commons.<<<'HTML'
<ul>
<li><strong>Broker:</strong> server perantara yang menerima pesan dan meneruskannya.</li>
<li><strong>Klien:</strong> perangkat atau aplikasi yang tersambung ke broker. ESP32, MQTTX, dan layar pemantau nanti dapat menjadi klien.</li>
<li><strong>Pesan:</strong> isi yang dikirim, misalnya <code>28.4</code> atau teks <code>ON</code>.</li>
</ul>
<p>Klien boleh melakukan dua peran sekaligus: mengirim suhu dan juga menerima perintah. Namun untuk langkah pertama, kita pisahkan dulu agar alurnya mudah dilihat. ESP32 belum dinyalakan hari ini.</p>

<h2>Topic — alamat yang harus sama persis</h2>
HTML
            .$topic.<<<'HTML'
<p>Contoh latihan kita memakai bentuk <code>kodingindonesia/fsiot/ruang-belajar/telemetry</code>. Empat bagian itu dibaca: organisasi / jalur belajar / tempat / jenis pesan. Ini bukan alamat situs web dan tidak diketik ke kolom URL browser. Nanti nama topic diketik di MQTTX, pada FS-33.</p>
<p><strong>Aturan sederhana:</strong> pilih huruf kecil, pakai garis miring sebagai pemisah, dan jangan mengganti nama di tengah latihan. <code>telemetry</code> berbeda dari <code>Telemetry</code>.</p>

<h2>Publish dan subscribe — kirim dan dengarkan</h2>
HTML
            .$flow.<<<'HTML'
<ol>
<li>ESP32 atau MQTTX <strong>publish</strong> pesan ke sebuah topic.</li>
<li>Broker menerima pesan tersebut.</li>
<li>Broker meneruskan pesan kepada setiap klien yang <strong>subscribe</strong> topic yang sama.</li>
</ol>
<p><strong>Bedanya dengan HTTP:</strong> pada HTTP, browser biasanya meminta satu alamat lalu server menjawab. Pada MQTT, klien dapat berlangganan topic dan broker mengantarkan pesan baru ketika ada yang mengirim. Detail protokolnya ada pada <a href="https://docs.oasis-open.org/mqtt/mqtt/v5.0/mqtt-v5.0.html" target="_blank" rel="noopener noreferrer">spesifikasi MQTT 5.0 OASIS</a>.</p>

<h2>localhost artinya komputer ini, bukan ESP32</h2>
<p>Nanti di FS-33 kita akan mengetik <code>127.0.0.1</code> atau <code>localhost</code> di MQTTX. Angka itu berarti <strong>komputer yang sedang menjalankan MQTTX</strong>.</p>
<ul>
<li>Di laptop, <code>localhost</code> = laptop itu.</li>
<li>Di HP, <code>localhost</code> = HP itu, bukan laptop dan bukan ESP32.</li>
<li>Di ESP32, <code>localhost</code> = ESP32 itu sendiri, bukan komputer kamu.</li>
</ul>
<p><strong>Intinya:</strong> jangan mengetik <code>localhost</code> di ESP32 jika yang dimaksud adalah komputer. Penjelasan alamat LAN menyusul di FS-34.</p>

<h2 id="fsiot-mqtt-checklist">Checklist sebelum FS-33</h2>
<p>Centang setelah kamu benar-benar memahami setiap poin. Target: <strong>8/8</strong>. Progres disimpan di browser perangkatmu dan tidak dikirim ke server.</p>
<ul id="fsiot-mqtt-checklist-items">
<li>Saya tahu broker adalah perantara pesan.</li>
<li>Saya tahu klien dapat berupa ESP32 atau MQTTX.</li>
<li>Saya tahu topic adalah alamat pesan, bukan URL web.</li>
<li>Saya tahu publish berarti mengirim ke topic.</li>
<li>Saya tahu subscribe berarti menerima pesan dari topic.</li>
<li>Saya tidak memakai broker publik untuk lab ini.</li>
<li>MQTTX sudah terpasang atau saya tahu bagian yang perlu diselesaikan.</li>
<li>Saya belum perlu membuka Arduino IDE pada FS-32.</li>
</ul>
<p><strong>Cara memeriksa kesiapan:</strong> baca setiap poin, lalu jelaskan alur ESP32 → broker → MQTTX dengan kata-katamu sendiri. Jika ada yang belum jelas, kembali ke bagian terkait. Tidak perlu membuka terminal atau menjalankan perintah apa pun hari ini.</p>

<h2>Kesalahan yang sering terjadi</h2>
<ul>
<li><strong>Mengira MQTT sama dengan HTTP.</strong> MQTT memakai broker dan topic, bukan halaman web.</li>
<li><strong>Topic salah ketik.</strong> Periksa setiap huruf, termasuk huruf besar dan huruf kecil.</li>
<li><strong>Memakai broker publik terlalu dini.</strong> Koneksi dapat berubah atau dibatasi; FS-33 menyiapkan broker lokal.</li>
<li><strong>Menghubungkan MQTTX sebelum ada broker.</strong> MQTTX adalah klien, bukan broker. Tunggu Mosquitto lokal di FS-33.</li>
<li><strong>Membuka Arduino IDE atau terminal hari ini.</strong> Tidak ada sketch dan tidak ada perintah untuk diuji. Tool hari ini hanya browser dan MQTTX.</li>
<li><strong>Menyalin Host dari screenshot internet.</strong> Banyak gambar resmi menampilkan broker publik. Jangan disalin.</li>
</ul>

<h2>Pertanyaan yang sering muncul</h2>
<h3>MQTTX itu broker?</h3>
<p>Bukan. MQTTX adalah klien: alat untuk mengirim dan melihat pesan. Broker adalah program terpisah. Kita memasang Mosquitto di FS-33.</p>
<h3>Kenapa tidak boleh Connect sekarang?</h3>
<p>Belum ada broker di komputer kita. Mengisi Host sembarangan sering mengarah ke broker publik milik orang lain.</p>
<h3>Harus buka Arduino IDE?</h3>
<p>Tidak. ESP32 dan kode belum dipakai. Arduino IDE dibuka lagi saat telemetry di FS-34.</p>
<h3>Topic diketik di mana?</h3>
<p>Nanti di MQTTX, pada lab FS-33. Hari ini cukup hafal bentuknya dan aturan huruf besar-kecil.</p>

<h2>Sumber</h2>
<ul>
<li><a href="https://docs.oasis-open.org/mqtt/mqtt/v5.0/mqtt-v5.0.html" target="_blank" rel="noopener noreferrer">OASIS — MQTT Version 5.0</a></li>
<li><a href="https://mqttx.app/docs" target="_blank" rel="noopener noreferrer">Dokumentasi resmi MQTTX</a> · <a href="https://mqttx.app/downloads" target="_blank" rel="noopener noreferrer">halaman unduhan MQTTX</a> · aplikasi oleh EMQ, Apache License 2.0</li>
<li>Tangkapan layar halaman unduhan MQTTX — <a href="https://mqttx.app/downloads" target="_blank" rel="noopener noreferrer">mqttx.app/downloads</a>, EMQ Technologies, 13 Agustus 2026</li>
<li><a href="https://commons.wikimedia.org/wiki/File:Arquitetura_MQTT_exemplo.png" target="_blank" rel="noopener noreferrer">Arquitetura MQTT exemplo.png</a> — Ana beloti, Wikimedia Commons, CC BY-SA 4.0</li>
<li>Diagram urutan tools, peran, topic, alur, dan ilustrasi jendela MQTTX — Koding Indonesia (FS-32)</li>
</ul>

<h2>Selanjutnya</h2>
<p><strong>Ringkasnya:</strong> MQTT membuat perangkat bertukar pesan lewat broker dan topic. MQTTX sudah menjadi alat lihat-pesan kita, tetapi broker belum ada. Lanjutkan langsung ke <strong>FS-33</strong> untuk memasang Mosquitto lokal, lalu lakukan publish/subscribe pertama tanpa bergantung pada internet publik.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $tools = $this->figure('fs32-tools-order.png', 'Tool order: browser, download MQTTX, install, do not connect', '<strong>Desk order:</strong> learn the concept in a browser → download MQTTX from the official site → install without a terminal → stop. The first real message is sent only when the local broker in FS-33 is ready. Diagram by Koding Indonesia (FS-32).');
        $downloads = $this->figure('fs32-mqttx-downloads.png', 'Official MQTTX Desktop download page at mqttx.app/downloads', '<strong>This is the page to open.</strong> Choose your operating system, then download <strong>MQTTX Desktop</strong> (the <code>.exe</code> file on Windows). Do not choose MQTTX CLI, MQTTX Web, or a public-broker link in the site menu. Source: <a href="https://mqttx.app/downloads" target="_blank" rel="noopener noreferrer">mqttx.app/downloads</a> — EMQ Technologies. Screenshot taken 13 August 2026. MQTTX is licensed under Apache License 2.0.');
        $mqttx = $this->figure('fs32-mqttx-empty.png', 'Illustration of a newly opened MQTTX window with no connection — a success state, not an error', '<strong>This is the correct view, not an error.</strong> MQTTX is open and the connection list is still empty = success today. Do not click <em>New Connection</em> and do not fill in Host. Illustration by Koding Indonesia (FS-32), modelled on the official <a href="https://mqttx.app/" target="_blank" rel="noopener noreferrer">MQTTX by EMQ</a> layout (Apache License 2.0). The official window screenshot is not used as-is because it shows a public broker.');
        $roles = $this->figure('fs32-broker-roles.png', 'MQTT client and broker roles', '<strong>Main figure — MQTT roles.</strong> ESP32 and MQTTX are clients. The broker is the message middleman. MQTT is a client-server publish/subscribe protocol. <a href="https://www.oasis-open.org/standard/mqtt-v5-0-os/" target="_blank" rel="noopener noreferrer">OASIS MQTT standard</a>. Diagram by Koding Indonesia (FS-32).');
        $commons = $this->figure('fs32-mqtt-architecture-cite.png', 'MQTT architecture from Wikimedia Commons with Indonesian labels', '<strong>Comparison figure.</strong> Same flow: sender → broker → receiver. Indonesian labels are drawn over the original Portuguese text so beginners can read it. The car and cloud icons come from the source; our lab uses ESP32, MQTTX, and local Mosquitto — not the public internet. Source: <a href="https://commons.wikimedia.org/wiki/File:Arquitetura_MQTT_exemplo.png" target="_blank" rel="noopener noreferrer">Arquitetura MQTT exemplo.png</a> by Ana beloti, Wikimedia Commons, <a href="https://creativecommons.org/licenses/by-sa/4.0/" target="_blank" rel="noopener noreferrer">CC BY-SA 4.0</a>. Adapted by Koding Indonesia: Indonesian labels added.');
        $topic = $this->figure('fs32-topic-address.png', 'Four parts of an MQTT topic address', '<strong>A topic is like a mailbox address.</strong> Publishers and subscribers must type exactly the same text. Uppercase and lowercase letters differ. Diagram by Koding Indonesia (FS-32).');
        $flow = $this->figure('fs32-pub-sub-flow.png', 'Publish and subscribe through a broker', '<strong>Helper schematic — one to many.</strong> A publisher does not need to know the listeners; the broker forwards a message to clients subscribed to that topic. The <em>other client</em> box is only a reminder that more receivers can join later; not today. Diagram by Koding Indonesia (FS-32).');
        $install = $this->stepsCard([
            ['title' => 'Open a browser', 'text' => 'Use Chrome, Firefox, Edge, or Safari. Do not open Arduino IDE, PowerShell, or Command Prompt.'],
            ['title' => 'Open the official MQTTX download page', 'text' => 'Go to <a href="https://mqttx.app/downloads" target="_blank" rel="noopener noreferrer">mqttx.app/downloads</a>. Choose <strong>MQTTX Desktop</strong> for Windows, macOS, or Linux. Do not download MQTTX CLI or MQTTX Web for this lab.'],
            ['title' => 'Run the installer, then open the app', 'text' => 'On Windows, choose the <code>.exe</code> file and follow the installer windows. If Microsoft Defender SmartScreen appears, choose <strong>More info</strong> then <strong>Run anyway</strong> only if the file came from mqttx.app. Then open MQTTX.'],
            ['title' => 'Stop. Do not Connect', 'text' => 'Stop once the app window opens. Do not click <em>New Connection</em>, do not fill in Host, and do not copy a broker address from the internet.'],
        ], '<strong>How to test today:</strong> there is no terminal command and no code to run. Success = MQTTX is open. The first message is tested in FS-33.');

        return <<<'HTML'
<h2>Introduction — MQTT is a message path, not a web page</h2>
<p><strong>FS-32 / #102 (this article)</strong> introduces MQTT before we use a local broker in FS-33. In FS-31, a browser asks ESP32 for a page. With MQTT, devices do not need to open pages for each other; they exchange <strong>messages</strong>.</p>
<p><strong>In short:</strong> no sketch, ESP32, or public broker is needed today. Read in a browser, then install one client application called MQTTX.</p>
<p><strong>Analogy:</strong> a broker is a post office. A client sends or receives letters. A topic is a mailbox address. <em>Publish</em> puts a letter in that mailbox; <em>subscribe</em> asks for a copy of every letter arriving there.</p>

<h2>Expected outcome</h2>
<ul>
<li>You can explain broker, client, topic, publish, and subscribe in your own words.</li>
<li>MQTTX is installed, or you know which install step remains.</li>
<li>You know that <code>localhost</code> and <code>127.0.0.1</code> later mean the computer you are using, not ESP32.</li>
</ul>
<p><strong>Today’s lab boundary:</strong> there is no syntax to test in a terminal. There is no board Upload. There is no broker connection.</p>

<h2>Terms used today</h2>
<ul>
<li><strong>MQTT</strong> — a message-path protocol for IoT; not a web page.</li>
<li><strong>Broker</strong> — the middleman program; like a post office.</li>
<li><strong>Client</strong> — a sender or receiver connected to a broker. ESP32 and MQTTX are both clients.</li>
<li><strong>Topic</strong> — a text address where messages are left; not a browser URL.</li>
<li><strong>Publish</strong> — send a message to a topic.</li>
<li><strong>Subscribe</strong> — ask for copies of messages on that topic.</li>
<li><strong>MQTTX</strong> — a desktop app for viewing and sending MQTT messages.</li>
<li><strong>Public broker</strong> — a broker on the internet owned by someone else. This lab does not use one.</li>
<li><strong><code>localhost</code> / <code>127.0.0.1</code></strong> — “this computer itself”. On a phone it means the phone; on ESP32 it means ESP32.</li>
</ul>

<h2>Preparation — open the right tool first</h2>
HTML
            .$tools.$install.$downloads.$mqttx.<<<'HTML'
<p><strong>If the app is not installed yet:</strong> still finish the concept. FS-33 starts only after MQTTX is ready, because that is where real messages appear.</p>
<p><strong>Phone tip:</strong> if a diagram feels small, use the browser or screen zoom. You do not need to tap the image to fill the screen; nearby text should stay readable.</p>

<h2>Who does what?</h2>
HTML
            .$roles.$commons.<<<'HTML'
<ul>
<li><strong>Broker:</strong> the server that accepts and forwards messages.</li>
<li><strong>Client:</strong> a device or app connected to a broker. ESP32, MQTTX, and a later dashboard can all be clients.</li>
<li><strong>Message:</strong> sent content, for example <code>28.4</code> or <code>ON</code>.</li>
</ul>
<p>A client may send and receive at the same time. For the first step we keep the roles separate so the path is easy to see. ESP32 stays off today.</p>

<h2>Topic — an address that must match exactly</h2>
HTML
            .$topic.<<<'HTML'
<p>Our example is <code>kodingindonesia/fsiot/ruang-belajar/telemetry</code>. Read the four parts as organisation / learning path / place / message type. It is not a website URL. Later, in FS-33, type it into MQTTX.</p>
<p><strong>Simple rule:</strong> keep lowercase names, use slashes as separators, and do not rename the topic mid-lab. <code>telemetry</code> differs from <code>Telemetry</code>.</p>

<h2>Publish and subscribe — send and listen</h2>
HTML
            .$flow.<<<'HTML'
<ol>
<li>ESP32 or MQTTX <strong>publishes</strong> a message to a topic.</li>
<li>The broker receives it.</li>
<li>The broker forwards it to each client that <strong>subscribes</strong> to the same topic.</li>
</ol>
<p><strong>Unlike HTTP:</strong> a browser normally requests an address and a server responds. With MQTT, a client can subscribe and the broker delivers new messages when a publisher sends one. See the <a href="https://docs.oasis-open.org/mqtt/mqtt/v5.0/mqtt-v5.0.html" target="_blank" rel="noopener noreferrer">OASIS MQTT 5.0 specification</a>.</p>

<h2>localhost means this computer, not ESP32</h2>
<p>In FS-33 we will type <code>127.0.0.1</code> or <code>localhost</code> in MQTTX. That address means <strong>the computer currently running MQTTX</strong>.</p>
<ul>
<li>On a laptop, <code>localhost</code> is that laptop.</li>
<li>On a phone, <code>localhost</code> is that phone, not the laptop and not ESP32.</li>
<li>On ESP32, <code>localhost</code> is ESP32 itself, not your computer.</li>
</ul>
<p><strong>In short:</strong> do not type <code>localhost</code> on ESP32 when you mean the computer. LAN addressing comes in FS-34.</p>

<h2 id="fsiot-mqtt-checklist">Checklist before FS-33</h2>
<p>Tick an item only after you truly understand it. Target: <strong>8/8</strong>. Progress stays in this device's browser and is not sent to the server.</p>
<ul id="fsiot-mqtt-checklist-items">
<li>I know that a broker is a message middleman.</li>
<li>I know that ESP32 and MQTTX can be clients.</li>
<li>I know that a topic is a message address, not a web URL.</li>
<li>I know that publish means sending to a topic.</li>
<li>I know that subscribe means receiving messages from a topic.</li>
<li>I do not use a public broker for this lab.</li>
<li>MQTTX is installed, or I know what remains to install.</li>
<li>I do not need to open Arduino IDE in FS-32.</li>
</ul>
<p><strong>How to check readiness:</strong> read each point, then explain ESP32 → broker → MQTTX in your own words. Return to the related section if anything is unclear. No terminal command is needed today.</p>

<h2>Common mistakes</h2>
<ul>
<li><strong>Thinking MQTT is HTTP.</strong> MQTT uses a broker and topics, not web pages.</li>
<li><strong>Typing a different topic.</strong> Check every character and letter case.</li>
<li><strong>Using a public broker too early.</strong> Its availability can change; FS-33 creates a local broker.</li>
<li><strong>Connecting MQTTX before a broker exists.</strong> MQTTX is a client, not a broker.</li>
<li><strong>Opening Arduino IDE or a terminal today.</strong> There is no sketch and no command to test. Today’s tools are the browser and MQTTX.</li>
<li><strong>Copying Host from an internet screenshot.</strong> Many official pictures show a public broker. Do not copy it.</li>
</ul>

<h2>Frequently asked questions</h2>
<h3>Is MQTTX the broker?</h3>
<p>No. MQTTX is a client: a tool to send and view messages. The broker is a separate program. We install Mosquitto in FS-33.</p>
<h3>Why not Connect now?</h3>
<p>There is no broker on our computer yet. Filling in a random Host often points at someone else’s public broker.</p>
<h3>Do I need Arduino IDE?</h3>
<p>No. ESP32 and code are not used today. Arduino IDE returns for telemetry in FS-34.</p>
<h3>Where do I type the topic?</h3>
<p>Later, in MQTTX, during the FS-33 lab. Today, remember the shape and the letter-case rule.</p>

<h2>Sources</h2>
<ul>
<li><a href="https://docs.oasis-open.org/mqtt/mqtt/v5.0/mqtt-v5.0.html" target="_blank" rel="noopener noreferrer">OASIS — MQTT Version 5.0</a></li>
<li><a href="https://mqttx.app/docs" target="_blank" rel="noopener noreferrer">Official MQTTX documentation</a> · <a href="https://mqttx.app/downloads" target="_blank" rel="noopener noreferrer">MQTTX downloads</a> · app by EMQ, Apache License 2.0</li>
<li>Screenshot of the MQTTX download page — <a href="https://mqttx.app/downloads" target="_blank" rel="noopener noreferrer">mqttx.app/downloads</a>, EMQ Technologies, 13 August 2026</li>
<li><a href="https://commons.wikimedia.org/wiki/File:Arquitetura_MQTT_exemplo.png" target="_blank" rel="noopener noreferrer">Arquitetura MQTT exemplo.png</a> — Ana beloti, Wikimedia Commons, CC BY-SA 4.0</li>
<li>Tool-order, roles, topic, flow, and MQTTX-window diagrams — Koding Indonesia (FS-32)</li>
</ul>

<h2>Next</h2>
<p><strong>In short:</strong> MQTT moves messages through a broker and topics. MQTTX is ready, but the broker does not exist yet. Continue directly to <strong>FS-33</strong> to install local Mosquitto and perform the first publish/subscribe lab.</p>
HTML;
    }
}
