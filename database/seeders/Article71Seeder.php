<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article71Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first()
            ?? Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $iotCat) {
            throw new \RuntimeException('User atau kategori iot-smart-device/esp32-arduino tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-apa-itu-iot';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        foreach ([
            'fullstack-iot' => 'fullstack-iot',
            'iot' => 'iot',
            'esp32' => 'esp32',
        ] as $tagSlug => $tagName) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagName]);
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'            => $admin->id,
                'category_id'        => $iotCat->id,
                'title'              => 'Apa itu IoT? (tanpa jargon berat)',
                'title_en'           => 'What is IoT? (without heavy jargon)',
                'excerpt'            => 'FS-01 / #71: IoT dijelaskan bahasa sehari-hari — benda terhubung, beda remote biasa, preview Stasiun Ruang Belajar. Belum wiring.',
                'excerpt_en'         => 'FS-01 / #71: IoT in everyday language — connected things, vs a plain remote, Study Room Station preview. No wiring yet.',
                'body'               => $this->body(),
                'body_en'            => $this->bodyEn(),
                'status'             => 'draft',
                'is_featured'        => false,
                'published_at'       => null,
                'seo_title'          => 'Apa itu IoT? Tanpa Jargon — Full Stack IoT #71',
                'seo_title_en'       => 'What is IoT? No Heavy Jargon — Full Stack IoT #71',
                'seo_description'    => 'Belajar IoT dari nol: benda fisik terhubung, contoh rumah/sekolah, beda remote IR, preview Stasiun Ruang Belajar. Modul FS-01.',
                'seo_description_en' => 'Learn IoT from zero: connected physical things, home/school examples, vs IR remote, Study Room Station preview. Module FS-01.',
            ]
        );

        // Pre-launch B: tetap draft — jangan set published_at
        if ($article->published_at !== null || $article->status !== 'draft') {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['fullstack-iot', 'iot', 'esp32'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel #71 / FS-01 tersimpan sebagai DRAFT: '.$article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — langkah pertama jalur Full Stack IoT</h2>
<p>Artikel ini adalah <strong>#71 (ini)</strong> · modul <strong>FS-01</strong> di jalur <strong>Full Stack IoT Developer — Dari Nol</strong>. Belum ada kabel, belum ada kode, belum ada unduhan software. Hari ini kamu hanya butuh satu pertanyaan: <em>IoT itu apa, kalau dijelaskan ke teman yang tidak suka istilah teknis?</em></p>
<p><strong>Awam:</strong> bayangkan kamu menjelaskan ke ibu atau adik di ruang tamu. Kalau mereka mengangguk dalam satu menit, kamu sudah lulus modul ini.</p>
<blockquote>
  <p><strong>Prasyarat:</strong> tidak ada. Modul ini pintu masuk jalur. Soft bridge ke modul berikutnya (satu gambar seluruh jalur) akan muncul setelah FS-02 siap — tanpa lompat wiring.</p>
</blockquote>

<h2>IoT dalam bahasa sehari-hari</h2>
<p><strong>IoT</strong> kepanjangan dari <em>Internet of Things</em> — “internet of things”. Intinya sederhana:</p>
<ul>
  <li>Ada <strong>benda fisik</strong> di dunia nyata (lampu, kipas, sensor suhu, pintu, pot tanaman).</li>
  <li>Benda itu bisa <strong>saling terhubung</strong> (sering lewat Wi‑Fi, tapi bukan wajib di setiap langkah belajar).</li>
  <li>Kamu bisa <strong>memantau</strong> atau <strong>mengendalikan</strong>nya dari jauh — lewat HP, laptop, atau dashboard.</li>
</ul>
<p><strong>Awam:</strong> IoT = benda di meja/rumah yang “bisa bicara” ke sistem, supaya kamu tahu keadaannya atau bisa menyuruhnya bergerak tanpa harus berdiri di sampingnya.</p>

<h2>Bukan sekadar “ada Wi‑Fi”</h2>
<p>Banyak orang mengira: “kalau sudah Wi‑Fi, itu IoT.” Belum tentu.</p>
<p>Wi‑Fi hanyalah salah satu <strong>jalan</strong> data. Yang membuat sesuatu “IoT” adalah rangkaian: <strong>benda → data/perintah → sistem yang menyimpan/menampilkan → kamu yang membaca atau mengontrol</strong>.</p>
<p>Contoh: printer di kantor yang hanya dilayani kabel lokal, tanpa pantauan dari luar, biasanya bukan yang kita maksud sebagai proyek IoT di jalur ini. Sebaliknya, sensor suhu di ruang belajar yang datanya muncul di dashboard HP-mu — itu arah IoT yang kita kejar.</p>
<p><strong>Awam:</strong> Wi‑Fi seperti jalan raya. IoT adalah “mobil + penumpang + tujuan”. Jalan saja belum cukup kalau tidak ada yang pergi ke suatu tempat.</p>

<h2>Contoh sederhana di sekitar kita</h2>
<table>
  <thead>
    <tr>
      <th>Tempat</th>
      <th>Contoh awam</th>
      <th>Yang dipantau / dikontrol</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Rumah</td>
      <td>Lampu yang bisa dinyalakan dari HP saat kamu masih di luar</td>
      <td>Nyala/mati lampu</td>
    </tr>
    <tr>
      <td>Sekolah / ruang belajar</td>
      <td>Sensor suhu &amp; cahaya di meja belajar</td>
      <td>Angka suhu, terang/gelap, alert kalau panas</td>
    </tr>
    <tr>
      <td>Pertanian kecil</td>
      <td>Sensor kelembaban tanah di pot</td>
      <td>Kapan tanah kering (nanti bisa picu pompa)</td>
    </tr>
  </tbody>
</table>
<p>Di jalur ini kita tidak mulai dari pabrik raksasa. Kita mulai dari <strong>Stasiun Ruang Belajar</strong> — proyek benang merah yang akan kamu bangun pelan-pelan.</p>

<h2>Remote TV biasa vs sistem IoT</h2>
<p>Remote infrared (IR) di TV: kamu tekan tombol, sinyal cahaya tak terlihat menuju TV. Kalau kamu keluar rumah, remote itu tidak berguna dari jarak jauh lewat internet.</p>
<p>Sistem IoT yang kita bayangkan: sensor atau saklar di perangkat, data/perintah bisa lewat jaringan, dan kamu melihat status di aplikasi atau dashboard — bahkan saat tidak berada di ruangan yang sama.</p>
<p><strong>Awam:</strong> remote TV = “bicara langsung di depan pintu”. IoT = “kirim pesan ke rumah, lalu rumah melaporkan balik ke kamu.”</p>

<figure role="img" aria-label="Perbandingan remote IR lokal versus jalur IoT jarak jauh" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 220" width="100%" height="auto" role="img" aria-label="Remote lokal versus IoT">
  <rect x="20" y="40" width="140" height="60" fill="#fff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="90" y="75" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">Remote IR</text>
  <text x="200" y="75" font-family="system-ui,sans-serif" font-size="18">→</text>
  <rect x="230" y="40" width="120" height="60" fill="#fff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="290" y="75" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">TV di ruang</text>
  <text x="40" y="130" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Hanya dekat · tanpa laporan ke HP</text>
  <rect x="20" y="150" width="140" height="50" fill="#EBF4FF" stroke="#1a1a1a" stroke-width="2"/>
  <text x="90" y="180" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">Sensor / ESP32</text>
  <text x="200" y="180" font-family="system-ui,sans-serif" font-size="18">→</text>
  <rect x="230" y="150" width="150" height="50" fill="#EBF4FF" stroke="#1a1a1a" stroke-width="2"/>
  <text x="305" y="180" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">Jaringan / server</text>
  <text x="410" y="180" font-family="system-ui,sans-serif" font-size="18">→</text>
  <rect x="440" y="150" width="240" height="50" fill="#EBF4FF" stroke="#1a1a1a" stroke-width="2"/>
  <text x="560" y="180" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">Dashboard / HP kamu</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Atas: remote lokal. Bawah: arah IoT yang kita pelajari di jalur ini (detail teknis belakangan).</figcaption>
</figure>

<h2>Preview proyek: Stasiun Ruang Belajar</h2>
<p>Seluruh jalur mengitari satu proyek: <strong>Stasiun Ruang Belajar</strong>. Nanti (modul-modul berikutnya) kamu akan:</p>
<ul>
  <li>membaca <strong>suhu</strong> dan <strong>cahaya</strong> di meja belajar,</li>
  <li>mengendalikan <strong>lampu</strong> (relay) dari sistem,</li>
  <li>melihat data di <strong>dashboard</strong>,</li>
  <li>mendapat <strong>alert</strong> bila kondisi aneh,</li>
  <li>belajar tetap aman saat Wi‑Fi putus.</li>
</ul>
<p>Hari ini cukup tahu namanya. Belum merakit apa pun.</p>
<p><strong>Awam:</strong> Stasiun Ruang Belajar = “mini cuaca + saklar pintar di meja belajar” yang kita bangun setahap demi setahap.</p>

<h2>Board yang nanti kita pakai</h2>
<p>Di jalur ini board resmi kita adalah <strong>ESP32-DevKitC-1</strong> — papan kecil yang mudah dicari di toko lokal maupun luar negeri. Di FS-01 kamu <strong>belum</strong> menyentuh pin, kabel, atau software unduhan. Cukup ingat namanya: nanti, mulai modul kit dan IDE, kita pakai DevKitC-1 secara konsisten.</p>
<p><strong>Awam:</strong> DevKitC-1 seperti “otak kecil” di breadboard. Kita kenalan dulu lewat nama, baru nanti wiring.</p>

<h2>Persiapan — alat yang kamu buka</h2>
<p><strong>Alat yang dipakai di artikel ini</strong> (belum Laragon, belum Arduino IDE, belum USB board):</p>
<ul>
  <li><strong>Browser</strong> — membaca artikel ini.</li>
  <li><strong>Catatan</strong> — kertas, Notepad, atau catatan HP untuk latihan 3 contoh IoT.</li>
</ul>
<p>Tidak ada unduhan hari ini. Tidak ada “install-dari-nol” software. Kalau kamu sudah punya board di laci, biarkan saja — kita buka kotaknya di modul belakangan.</p>

<h2>Istilah mini untuk FS-01</h2>
<table>
  <thead>
    <tr>
      <th>Istilah</th>
      <th>Arti awam</th>
      <th>Catatan</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>IoT</td>
      <td>Benda fisik terhubung yang bisa dipantau/dikendalikan dari jauh</td>
      <td>Fokus hari ini</td>
    </tr>
    <tr>
      <td>Sensor</td>
      <td>“Indra” yang mengukur sesuatu (suhu, cahaya, gerak)</td>
      <td>Dipakai di Stasiun Ruang Belajar nanti</td>
    </tr>
    <tr>
      <td>Aktuator</td>
      <td>“Otot” yang bergerak (lampu, relay, motor kecil)</td>
      <td>Nanti: lampu via relay</td>
    </tr>
    <tr>
      <td>Dashboard</td>
      <td>Layar ringkas yang menampilkan data</td>
      <td>Fase belakangan</td>
    </tr>
    <tr>
      <td>ESP32-DevKitC-1</td>
      <td>Board resmi jalur ini</td>
      <td>Belum wiring di FS-01</td>
    </tr>
  </tbody>
</table>

<h2>Praktik — tulis 3 contoh IoT di sekitarmu</h2>
<ol>
  <li>Ambil catatan.</li>
  <li>Tulis tiga benda di rumah/sekolah yang <em>bisa</em> jadi IoT (atau sudah IoT).</li>
  <li>Untuk tiap benda, tulis satu kalimat: apa yang dipantau atau dikontrol.</li>
</ol>
<p>Contoh jawaban (boleh beda): (1) lampu kamar — nyala/mati dari HP, (2) sensor asap — alert ke HP, (3) pot tanaman — kelembaban tanah.</p>
<p><strong>Awam — cara menguji bagian ini:</strong> bacakan tiga contohmu ke orang non-teknis. Kalau mereka paham tanpa kamu menjelaskan Wi‑Fi atau cloud, latihanmu lolos.</p>

<h2>Kesalahan umum awam</h2>
<ol>
  <li><strong>Mengira IoT = robot.</strong> Robot bisa memakai ide IoT, tapi IoT lebih luas: sensor + kontrol jarak jauh juga IoT.</li>
  <li><strong>Mengira harus cloud mahal dari hari pertama.</strong> Jalur kita mulai lokal dan bertahap; cloud bukan syarat FS-01.</li>
  <li><strong>Mengira harus bisa coding dulu.</strong> FS-01 tidak menulis kode. Coding datang pelan setelah fondasi.</li>
  <li><strong>Mengira “sudah Wi‑Fi” = sudah IoT.</strong> Lihat bagian “bukan sekadar Wi‑Fi” di atas.</li>
  <li><strong>Langsung beli banyak komponen tanpa peta.</strong> Tunggu modul kit; hari ini cukup paham konsep.</li>
  <li><strong>Mencampur jalur lama ESP32 sebagai prasyarat.</strong> Jalur Full Stack IoT ini mandiri dari nol — ikuti FS-01 → FS-… berurutan.</li>
</ol>

<h2>Lanjut belajar</h2>
<p>Setelah FS-01, langkah alami berikutnya adalah <strong>FS-02 — satu gambar untuk seluruh jalur</strong> (peta lapisan dari benda nyata sampai dashboard). Artikel itu belum dilink di sini sampai modulnya siap, supaya tidak ada tautan mati.</p>
<p>Simpan juga halaman jalur Full Stack IoT di situs ini sebagai pintu masuk resmi — saat rilis, materi akan terbuka berurutan.</p>

<h2>Kesimpulan</h2>
<p>Di <strong>#71 (ini)</strong> kamu sudah punya definisi IoT yang bisa dijelaskan ke orang awam, beda remote lokal vs jalur jarak jauh, dan preview <strong>Stasiun Ruang Belajar</strong>. Board resmi nanti: <strong>ESP32-DevKitC-1</strong>. Belum wiring — dan itu sengaja.</p>
<p><strong>Awam:</strong> kalau kamu bisa jawab “IoT itu apa?” tanpa mengutip jargon berat, FS-01 selesai. Lanjut ke peta jalur di FS-02 saat modulnya terbit.</p>
HTML;
    }

    private function bodyEn(): string
    {
        return <<<'HTML'
<h2>Introduction — first step on the Full Stack IoT path</h2>
<p>This article is <strong>#71 (this article)</strong> · module <strong>FS-01</strong> on the <strong>Full Stack IoT Developer — From Zero</strong> path. No wires, no code, no software downloads today. You only need one question: <em>What is IoT, if you explain it to a friend who dislikes technical jargon?</em></p>
<p><strong>Beginner:</strong> imagine explaining it to a parent or sibling in the living room. If they nod within one minute, you have passed this module.</p>
<blockquote>
  <p><strong>Prerequisites:</strong> none. This module is the path entrance. A soft bridge to the next module (one picture of the whole path) will appear when FS-02 is ready — without jumping into wiring.</p>
</blockquote>

<h2>IoT in everyday language</h2>
<p><strong>IoT</strong> stands for <em>Internet of Things</em>. The core idea is simple:</p>
<ul>
  <li>There is a <strong>physical thing</strong> in the real world (a lamp, fan, temperature sensor, door, plant pot).</li>
  <li>That thing can be <strong>connected</strong> (often via Wi‑Fi, but not required at every learning step).</li>
  <li>You can <strong>monitor</strong> or <strong>control</strong> it from afar — via phone, laptop, or a dashboard.</li>
</ul>
<p><strong>Beginner:</strong> IoT = something on your desk or in your home that can “talk” to a system so you know its state or can tell it to act without standing next to it.</p>

<h2>Not just “having Wi‑Fi”</h2>
<p>Many people think: “If there is Wi‑Fi, it is IoT.” Not always.</p>
<p>Wi‑Fi is only one <strong>road</strong> for data. What makes something “IoT” is the chain: <strong>thing → data/commands → a system that stores/shows them → you reading or controlling</strong>.</p>
<p>Example: an office printer only used on a local cable, with no remote monitoring, is usually not what we mean by an IoT project on this path. A study-desk temperature sensor whose readings appear on your phone dashboard — that is the IoT direction we chase.</p>
<p><strong>Beginner:</strong> Wi‑Fi is like a road. IoT is “car + passengers + destination.” A road alone is not enough if nothing is going somewhere.</p>

<h2>Simple examples around us</h2>
<table>
  <thead>
    <tr>
      <th>Place</th>
      <th>Beginner example</th>
      <th>What is monitored / controlled</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Home</td>
      <td>A lamp you can turn on from your phone while still outside</td>
      <td>Lamp on/off</td>
    </tr>
    <tr>
      <td>School / study room</td>
      <td>Temperature and light sensors on a study desk</td>
      <td>Temperature numbers, bright/dark, alert if too hot</td>
    </tr>
    <tr>
      <td>Small farming</td>
      <td>Soil moisture sensor in a pot</td>
      <td>When soil is dry (later can trigger a pump)</td>
    </tr>
  </tbody>
</table>
<p>On this path we do not start with a giant factory. We start with <strong>Study Room Station</strong> — the story project you will build step by step.</p>

<h2>A plain TV remote vs an IoT system</h2>
<p>An infrared (IR) TV remote: you press a button, an invisible light signal goes to the TV. If you leave the house, that remote cannot help over the internet.</p>
<p>The IoT system we imagine: a sensor or switch on a device, data/commands can travel over a network, and you see status in an app or dashboard — even when you are not in the same room.</p>
<p><strong>Beginner:</strong> a TV remote = “talking right at the door.” IoT = “sending a message home, then home reports back to you.”</p>

<figure role="img" aria-label="Comparing a local IR remote versus a long-distance IoT path" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 220" width="100%" height="auto" role="img" aria-label="Local remote versus IoT">
  <rect x="20" y="40" width="140" height="60" fill="#fff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="90" y="75" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">IR remote</text>
  <text x="200" y="75" font-family="system-ui,sans-serif" font-size="18">→</text>
  <rect x="230" y="40" width="120" height="60" fill="#fff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="290" y="75" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">TV in room</text>
  <text x="40" y="130" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Nearby only · no phone report</text>
  <rect x="20" y="150" width="140" height="50" fill="#EBF4FF" stroke="#1a1a1a" stroke-width="2"/>
  <text x="90" y="180" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">Sensor / ESP32</text>
  <text x="200" y="180" font-family="system-ui,sans-serif" font-size="18">→</text>
  <rect x="230" y="150" width="150" height="50" fill="#EBF4FF" stroke="#1a1a1a" stroke-width="2"/>
  <text x="305" y="180" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">Network / server</text>
  <text x="410" y="180" font-family="system-ui,sans-serif" font-size="18">→</text>
  <rect x="440" y="150" width="240" height="50" fill="#EBF4FF" stroke="#1a1a1a" stroke-width="2"/>
  <text x="560" y="180" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">Dashboard / your phone</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Top: local remote. Bottom: IoT direction on this path (technical detail later).</figcaption>
</figure>

<h2>Project preview: Study Room Station</h2>
<p>The whole path follows one project: <strong>Study Room Station</strong>. Later (in upcoming modules) you will:</p>
<ul>
  <li>read <strong>temperature</strong> and <strong>light</strong> on the study desk,</li>
  <li>control a <strong>lamp</strong> (relay) from the system,</li>
  <li>view data on a <strong>dashboard</strong>,</li>
  <li>get <strong>alerts</strong> when something looks wrong,</li>
  <li>learn to stay safe when Wi‑Fi drops.</li>
</ul>
<p>Today you only need the name. Do not assemble anything yet.</p>
<p><strong>Beginner:</strong> Study Room Station = a “mini weather + smart switch on your study desk” that we build step by step.</p>

<h2>The board we will use later</h2>
<p>On this path our official board is <strong>ESP32-DevKitC-1</strong> — a small board that is easy to find in local and international stores. In FS-01 you do <strong>not</strong> touch pins, cables, or download software. Just remember the name: later, from the kit and IDE modules, we use DevKitC-1 consistently.</p>
<p><strong>Beginner:</strong> DevKitC-1 is like a “small brain” on a breadboard. We learn the name first; wiring comes later.</p>

<h2>Preparation — tools you open</h2>
<p><strong>Tools used in this article</strong> (no Laragon, no Arduino IDE, no USB board yet):</p>
<ul>
  <li><strong>Browser</strong> — to read this article.</li>
  <li><strong>Notes</strong> — paper, Notepad, or a phone note for the 3 IoT examples exercise.</li>
</ul>
<p>No downloads today. No install-from-scratch software. If you already have a board in a drawer, leave it there — we open the kit in a later module.</p>

<h2>Mini glossary for FS-01</h2>
<table>
  <thead>
    <tr>
      <th>Term</th>
      <th>Beginner meaning</th>
      <th>Note</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>IoT</td>
      <td>Connected physical things you can monitor/control from afar</td>
      <td>Focus today</td>
    </tr>
    <tr>
      <td>Sensor</td>
      <td>“Sense” that measures something (temperature, light, motion)</td>
      <td>Used later in Study Room Station</td>
    </tr>
    <tr>
      <td>Actuator</td>
      <td>“Muscle” that moves (lamp, relay, small motor)</td>
      <td>Later: lamp via relay</td>
    </tr>
    <tr>
      <td>Dashboard</td>
      <td>A simple screen that shows data</td>
      <td>Later phase</td>
    </tr>
    <tr>
      <td>ESP32-DevKitC-1</td>
      <td>Official board for this path</td>
      <td>No wiring in FS-01</td>
    </tr>
  </tbody>
</table>

<h2>Practice — write 3 IoT examples around you</h2>
<ol>
  <li>Grab your notes.</li>
  <li>Write three things at home/school that <em>could</em> be IoT (or already are).</li>
  <li>For each thing, write one sentence: what is monitored or controlled.</li>
</ol>
<p>Sample answers (yours may differ): (1) bedroom lamp — on/off from phone, (2) smoke sensor — alert to phone, (3) plant pot — soil moisture.</p>
<p><strong>Beginner — how to test this part:</strong> read your three examples to a non-technical person. If they understand without you explaining Wi‑Fi or cloud, your practice passes.</p>

<h2>Common beginner mistakes</h2>
<ol>
  <li><strong>Thinking IoT = robot.</strong> Robots can use IoT ideas, but IoT is broader: sensors + remote control also count.</li>
  <li><strong>Thinking you need expensive cloud from day one.</strong> Our path starts local and gradual; cloud is not an FS-01 requirement.</li>
  <li><strong>Thinking you must know coding first.</strong> FS-01 writes no code. Coding comes slowly after foundations.</li>
  <li><strong>Thinking “has Wi‑Fi” = already IoT.</strong> See the “not just Wi‑Fi” section above.</li>
  <li><strong>Buying many parts without a map.</strong> Wait for the kit module; today is concepts only.</li>
  <li><strong>Mixing old ESP32 series as a prerequisite.</strong> This Full Stack IoT path is independent from zero — follow FS-01 → FS-… in order.</li>
</ol>

<h2>Keep learning</h2>
<p>After FS-01, the natural next step is <strong>FS-02 — one picture for the whole path</strong> (layer map from real-world things to dashboard). That article is not hard-linked here until the module is ready, so we avoid dead links.</p>
<p>Also bookmark the Full Stack IoT path page on this site as the official entrance — at launch, lessons will open in order.</p>

<h2>Conclusion</h2>
<p>In <strong>#71 (this article)</strong> you have an IoT definition you can explain to a non-technical person, the difference between a local remote and a long-distance path, and a preview of <strong>Study Room Station</strong>. Official board later: <strong>ESP32-DevKitC-1</strong>. No wiring yet — on purpose.</p>
<p><strong>Beginner:</strong> if you can answer “What is IoT?” without heavy jargon, FS-01 is done. Continue to the path map in FS-02 when that module publishes.</p>
HTML;
    }
}
