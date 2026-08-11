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
            'excerpt' => 'FS-32 / #102: pahami jalur pesan MQTT dan pasang MQTTX untuk praktik broker lokal di FS-33. Belum perlu ESP32, kode, atau broker publik.',
            'excerpt_en' => 'FS-32 / #102: understand MQTT message flow and install MQTTX for the local-broker lab in FS-33. No ESP32, code, or public broker yet.',
            'body' => $this->body(),
            'body_en' => $this->bodyEn(),
            'status' => 'draft',
            'is_featured' => false,
            'published_at' => null,
            'seo_title' => 'MQTT untuk Pemula: Broker, Topic, Publish, Subscribe — FS-32',
            'seo_title_en' => 'MQTT for Beginners: Broker, Topic, Publish, Subscribe — FS-32',
            'seo_description' => 'Pelajari broker, topic, publish, dan subscribe MQTT dengan bahasa sederhana. Pasang MQTTX untuk praktik Mosquitto lokal di FS-33.',
            'seo_description_en' => 'Learn MQTT broker, topics, publish, and subscribe in plain language. Install MQTTX for the local Mosquitto lab in FS-33.',
        ]);

        $article->tags()->sync(Tag::whereIn('slug', ['fullstack-iot', 'iot', 'mqtt'])->pluck('id'));

        foreach (['webp', 'jpg'] as $extension) {
            $source = public_path("images/fsiot/fs32-cover-mqtt.{$extension}");
            if (is_file($source)) {
                $destination = "articles/covers/fs32-cover-mqtt.{$extension}";
                Storage::disk('public')->put($destination, file_get_contents($source));
                $article->update(['cover_image' => $destination]);
                break;
            }
        }
    }

    private function figure(string $file, string $alt, string $caption): string
    {
        return '<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem"><img src="/images/fsiot/'.$file.'" alt="'.$alt.'" loading="eager" style="width:100%;height:auto;max-height:640px;object-fit:contain;border-radius:6px;background:#F5F5F0"><figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a">'.$caption.'</figcaption></figure>';
    }

    private function body(): string
    {
        $tools = $this->figure('fs32-tools-order.png', 'Urutan tools MQTTX sebelum lab Mosquitto', '<strong>Urutan meja kerja:</strong> pahami konsep → pasang MQTTX → berhenti dulu. Pesan pertama baru dikirim saat broker lokal FS-33 siap. Diagram buatan Koding Indonesia (FS-32).');
        $roles = $this->figure('fs32-broker-roles.png', 'Peran client dan broker MQTT', '<strong>Gambar utama — peran MQTT.</strong> ESP32 dan MQTTX adalah client. Broker menjadi perantara pesan. MQTT adalah protokol client-server dengan pola publish/subscribe. <a href="https://www.oasis-open.org/standard/mqtt-v5-0-os/" target="_blank" rel="noopener noreferrer">Standar MQTT OASIS</a>. Diagram buatan Koding Indonesia (FS-32).');
        $topic = $this->figure('fs32-topic-address.png', 'Contoh alamat topic MQTT bertingkat', '<strong>Topic seperti alamat rak.</strong> Pengirim dan penerima harus memakai tulisan yang sama persis. Huruf besar dan kecil berbeda. Diagram buatan Koding Indonesia (FS-32).');
        $flow = $this->figure('fs32-pub-sub-flow.png', 'Alur publish dan subscribe melalui broker', '<strong>Skema bantu — satu ke banyak.</strong> Publisher tidak perlu tahu siapa yang menerima; broker meneruskan pesan kepada client yang berlangganan topic tersebut. Diagram buatan Koding Indonesia (FS-32).');

        return <<<'HTML'
<h2>Pendahuluan — MQTT adalah jalur pesan, bukan halaman web</h2>
<p><strong>FS-32 / #102 (ini)</strong> mengenalkan MQTT sebelum kita memakai broker lokal di FS-33. Di FS-31, browser meminta halaman ke ESP32. Pada MQTT, perangkat tidak perlu saling membuka halaman; mereka menitipkan dan mengambil <strong>pesan</strong>.</p>
<p><strong>Intinya:</strong> hari ini belum menulis sketch, belum menyalakan ESP32, dan belum memakai broker publik. Buka browser untuk membaca, lalu pasang satu aplikasi client bernama MQTTX.</p>
<p><strong>Analogi:</strong> broker seperti kantor pos. Client adalah pengirim atau penerima surat. Topic adalah alamat loker. <em>Publish</em> berarti menitipkan surat ke loker; <em>subscribe</em> berarti meminta salinan setiap surat yang masuk ke loker itu.</p>

<h2>Hasil yang dituju</h2>
<ul><li>Mampu menyebut broker, client, topic, publish, dan subscribe dengan bahasa sendiri.</li><li>MQTTX sudah terpasang untuk lab broker lokal pada FS-33.</li><li>Tahu bahwa <code>localhost</code> nanti berarti komputer sendiri, bukan ESP32.</li></ul>

<h2>Persiapan — buka tool yang benar dulu</h2>
HTML
            .$tools.<<<'HTML'
<ol><li><strong>Buka browser.</strong> Pakai Chrome, Firefox, Edge, atau Safari untuk mengunduh MQTTX dari situs resminya.</li><li><strong>Jangan buka Arduino IDE dulu.</strong> Tidak ada sketch dan kabel baru pada FS-32.</li><li><strong>Jangan membuat koneksi ke broker publik.</strong> Kita memakai broker lokal sendiri di FS-33 agar latihan lebih stabil dan tidak mengirim data ke layanan orang lain.</li></ol>
<p><strong>Cara memasang MQTTX:</strong> buka <a href="https://mqttx.app/docs" target="_blank" rel="noopener noreferrer">dokumentasi resmi MQTTX</a> → pilih unduhan untuk Windows, macOS, atau Linux → jalankan installer → buka MQTTX. Cukup sampai aplikasi terbuka; belum perlu membuat koneksi.</p>
<p><strong>Jika kamu belum bisa memasang aplikasi:</strong> tetap baca konsep sampai selesai. FS-33 baru dikerjakan setelah MQTTX siap, karena di sana kita akan melihat pesan sungguhan.</p>

<h2>Siapa melakukan apa?</h2>
HTML
            .$roles.<<<'HTML'
<ul><li><strong>Broker:</strong> server perantara yang menerima pesan dan meneruskannya.</li><li><strong>Client:</strong> perangkat atau aplikasi yang tersambung ke broker; ESP32, MQTTX, dan dashboard dapat menjadi client.</li><li><strong>Pesan:</strong> isi yang dikirim, misalnya <code>28.4</code> atau teks <code>ON</code>.</li></ul>
<p>Client boleh melakukan dua peran sekaligus: mengirim suhu dan juga menerima perintah. Namun untuk pemula, kita pisahkan dulu agar alurnya mudah dilihat.</p>

<h2>Topic — alamat yang harus sama persis</h2>
HTML
            .$topic.<<<'HTML'
<p>Contoh latihan kita memakai bentuk <code>kodingindonesia/fsiot/ruang-belajar/telemetry</code>. Ini bukan alamat website dan tidak diketik ke kolom URL browser. Nanti nama topic diketik di MQTTX.</p>
<p><strong>Aturan sederhana:</strong> pilih huruf kecil, pakai garis miring sebagai pemisah, dan jangan mengganti nama di tengah latihan. <code>telemetry</code> berbeda dari <code>Telemetry</code>.</p>

<h2>Publish dan subscribe — kirim dan dengarkan</h2>
HTML
            .$flow.<<<'HTML'
<ol><li>ESP32 atau MQTTX <strong>publish</strong> pesan ke sebuah topic.</li><li>Broker menerima pesan tersebut.</li><li>Broker meneruskan pesan kepada setiap client yang <strong>subscribe</strong> topic sama.</li></ol>
<p><strong>Bedanya dengan HTTP:</strong> pada HTTP browser biasanya meminta satu alamat lalu server menjawab. Pada MQTT, client dapat berlangganan topic dan broker mengantarkan pesan baru ketika ada publisher. Detail protokolnya ada pada <a href="https://docs.oasis-open.org/mqtt/mqtt/v5.0/mqtt-v5.0.html" target="_blank" rel="noopener noreferrer">spesifikasi MQTT 5.0 OASIS</a>.</p>

<h2 id="fsiot-mqtt-checklist">Checklist sebelum FS-33</h2>
<p>Centang setelah kamu benar-benar memahami poinnya. Target: <strong>8/8</strong>. Progres disimpan di browser perangkatmu dan tidak dikirim ke server.</p>
<ul id="fsiot-mqtt-checklist-items">
<li>Saya tahu broker adalah perantara pesan.</li><li>Saya tahu client dapat berupa ESP32 atau MQTTX.</li><li>Saya tahu topic adalah alamat pesan, bukan URL web.</li><li>Saya tahu publish berarti mengirim ke topic.</li><li>Saya tahu subscribe berarti menerima pesan dari topic.</li><li>Saya tidak memakai broker publik untuk lab ini.</li><li>MQTTX sudah terpasang atau saya tahu bagian yang perlu diselesaikan.</li><li>Saya belum perlu membuka Arduino IDE pada FS-32.</li></ul>
<ul><li>Saya tahu broker adalah perantara pesan.</li><li>Saya tahu client dapat berupa ESP32 atau MQTTX.</li><li>Saya tahu topic adalah alamat pesan, bukan URL web.</li><li>Saya tahu publish berarti mengirim ke topic.</li><li>Saya tahu subscribe berarti menerima pesan dari topic.</li><li>Saya tidak memakai broker publik untuk lab ini.</li><li>MQTTX sudah terpasang atau saya tahu bagian yang perlu diselesaikan.</li><li>Saya belum perlu membuka Arduino IDE pada FS-32.</li></ul>
<p><strong>Cara memeriksa kesiapan:</strong> baca setiap poin, lalu jelaskan alur ESP32 → broker → MQTTX dengan kata-katamu sendiri. Jika ada yang belum jelas, kembali ke bagian terkait. Tidak perlu terminal atau perintah sintaks hari ini.</p>

<h2>Kesalahan yang sering terjadi</h2>
<ul><li><strong>Mengira MQTT = HTTP.</strong> MQTT memakai broker dan topic, bukan halaman web.</li><li><strong>Topic salah ketik.</strong> Periksa setiap huruf, termasuk besar-kecilnya.</li><li><strong>Memakai broker publik terlalu dini.</strong> Koneksi dapat berubah atau dibatasi; FS-33 menyiapkan broker lokal.</li><li><strong>Menghubungkan MQTTX sebelum ada broker.</strong> MQTTX adalah client, bukan broker. Tunggu Mosquitto lokal di FS-33.</li></ul>

<h2>Selanjutnya</h2>
<p><strong>Ringkasnya:</strong> MQTT membuat perangkat bertukar pesan lewat broker dan topic. MQTTX sudah menjadi alat lihat-pesan kita, tetapi broker belum ada. Lanjutkan langsung ke <strong>FS-33</strong> untuk memasang Mosquitto lokal, lalu lakukan publish/subscribe pertama tanpa bergantung pada internet publik.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $tools = $this->figure('fs32-tools-order.png', 'MQTTX tool order before the Mosquitto lab', '<strong>Desk order:</strong> learn the concept → install MQTTX → stop there. The first real message is sent only when the local broker in FS-33 is ready. Diagram by Koding Indonesia (FS-32).');
        $roles = $this->figure('fs32-broker-roles.png', 'MQTT client and broker roles', '<strong>Main figure — MQTT roles.</strong> ESP32 and MQTTX are clients; the broker is the message middleman. MQTT is a client-server publish/subscribe protocol. <a href="https://www.oasis-open.org/standard/mqtt-v5-0-os/" target="_blank" rel="noopener noreferrer">OASIS MQTT standard</a>. Diagram by Koding Indonesia (FS-32).');
        $topic = $this->figure('fs32-topic-address.png', 'An MQTT topic address example', '<strong>A topic is like a shelf address.</strong> Publishers and subscribers must type exactly the same text. Uppercase and lowercase letters differ. Diagram by Koding Indonesia (FS-32).');
        $flow = $this->figure('fs32-pub-sub-flow.png', 'Publish and subscribe through a broker', '<strong>Helper schematic — one to many.</strong> A publisher does not need to know listeners; the broker forwards a message to clients subscribed to that topic. Diagram by Koding Indonesia (FS-32).');

        return <<<'HTML'
<h2>Introduction — MQTT is a message path, not a web page</h2>
<p><strong>FS-32 / #102 (this article)</strong> introduces MQTT before we use a local broker in FS-33. In FS-31, a browser asks ESP32 for a page. With MQTT, devices do not need to open pages for each other; they exchange <strong>messages</strong>.</p>
<p><strong>In short:</strong> no sketch, ESP32, or public broker is needed today. Read in a browser, then install one client application called MQTTX.</p>
<p><strong>Analogy:</strong> a broker is a post office. Clients send or receive letters. A topic is a mailbox address. <em>Publish</em> puts a letter in that mailbox; <em>subscribe</em> asks for a copy of every letter arriving there.</p>
<h2>Expected outcome</h2><ul><li>You can explain broker, client, topic, publish, and subscribe in your own words.</li><li>MQTTX is installed for the local-broker lab in FS-33.</li><li>You know that <code>localhost</code> later means your own computer, not ESP32.</li></ul>
<h2>Preparation — open the right tool first</h2>
HTML
            .$tools.<<<'HTML'
<ol><li><strong>Open a browser.</strong> Use Chrome, Firefox, Edge, or Safari to download MQTTX from its official site.</li><li><strong>Do not open Arduino IDE yet.</strong> FS-32 has no sketch or new wiring.</li><li><strong>Do not connect to a public broker.</strong> FS-33 uses our own local broker so the lab stays stable and private.</li></ol>
<p><strong>Install MQTTX:</strong> open the <a href="https://mqttx.app/docs" target="_blank" rel="noopener noreferrer">official MQTTX documentation</a> → choose Windows, macOS, or Linux → run the installer → open MQTTX. Stop once the app opens; do not create a connection yet.</p>
<h2>Who does what?</h2>
HTML
            .$roles.<<<'HTML'
<ul><li><strong>Broker:</strong> the server that accepts and forwards messages.</li><li><strong>Client:</strong> a device or app connected to a broker; ESP32, MQTTX, and a dashboard can all be clients.</li><li><strong>Message:</strong> sent content, for example <code>28.4</code> or <code>ON</code>.</li></ul>
<h2>Topic — an address that must match exactly</h2>
HTML
            .$topic.<<<'HTML'
<p>Our example is <code>kodingindonesia/fsiot/ruang-belajar/telemetry</code>. It is not a website URL. Later, type it into MQTTX. Keep lowercase names and do not rename the topic mid-lab.</p>
<h2>Publish and subscribe — send and listen</h2>
HTML
            .$flow.<<<'HTML'
<ol><li>ESP32 or MQTTX <strong>publishes</strong> a message to a topic.</li><li>The broker receives it.</li><li>The broker forwards it to each client that <strong>subscribes</strong> to the same topic.</li></ol>
<p><strong>Unlike HTTP:</strong> a browser normally requests an address and a server responds. With MQTT, a client can subscribe and the broker delivers new messages when a publisher sends one. See the <a href="https://docs.oasis-open.org/mqtt/mqtt/v5.0/mqtt-v5.0.html" target="_blank" rel="noopener noreferrer">OASIS MQTT 5.0 specification</a>.</p>
<h2 id="fsiot-mqtt-checklist">Checklist before FS-33</h2><p>Tick an item only after you truly understand it. Target: <strong>8/8</strong>. Progress stays in this device's browser and is not sent to the server.</p><ul id="fsiot-mqtt-checklist-items"><li>I know that a broker is a message middleman.</li><li>I know that ESP32 and MQTTX can be clients.</li><li>I know that a topic is a message address, not a web URL.</li><li>I know that publish sends and subscribe receives.</li><li>I do not use a public broker for this lab.</li><li>MQTTX is installed, or I know what remains to install.</li><li>I do not need to open Arduino IDE in FS-32.</li><li>I know FS-33 is where the first local-broker lab begins.</li></ul>
<p><strong>How to check readiness:</strong> read each point, then explain ESP32 → broker → MQTTX in your own words. Return to the related section if anything is unclear. No terminal command is needed today.</p>
<h2>Common mistakes</h2><ul><li><strong>Thinking MQTT is HTTP.</strong> MQTT uses a broker and topics, not web pages.</li><li><strong>Typing a different topic.</strong> Check every character and letter case.</li><li><strong>Using a public broker too early.</strong> Its availability can change; FS-33 creates a local broker.</li><li><strong>Connecting MQTTX before a broker exists.</strong> MQTTX is a client, not a broker.</li></ul>
<h2>Next</h2><p><strong>In short:</strong> MQTT moves messages through a broker and topics. MQTTX is ready, but the broker does not exist yet. Continue directly to <strong>FS-33</strong> to install local Mosquitto and perform the first publish/subscribe lab.</p>
HTML;
    }
}
