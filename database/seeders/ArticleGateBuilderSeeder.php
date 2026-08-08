<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class ArticleGateBuilderSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first()
            ?? Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $iotCat) {
            throw new \RuntimeException('User atau kategori iot-smart-device/esp32-arduino tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-gate-builder';

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
                'title'              => 'Gate BUILDER → CONNECTED: kuis naik fase',
                'title_en'           => 'BUILDER → CONNECTED gate: phase-up quiz',
                'excerpt'            => 'Pintu naik dari BUILDER ke CONNECTED. Kuis matching 15 istilah di browser — target ≥12/15. Soft bridge ke Wi-Fi (FS-29).',
                'excerpt_en'         => 'Gateway from BUILDER to CONNECTED. Matching quiz of 15 terms in the browser — target ≥12/15. Soft bridge to Wi-Fi (FS-29).',
                'body'               => $this->body(),
                'body_en'            => $this->bodyEn(),
                'status'             => 'draft',
                'is_featured'        => false,
                'published_at'       => null,
                'seo_title'          => 'Gate BUILDER → CONNECTED — Full Stack IoT',
                'seo_title_en'       => 'BUILDER → CONNECTED gate — Full Stack IoT',
                'seo_description'    => 'Kuis naik fase BUILDER ke CONNECTED. Matching 15 istilah, target ≥12/15, checklist wiring. Browser saja.',
                'seo_description_en' => 'Phase-up quiz BUILDER to CONNECTED. Match 15 terms, target ≥12/15, wiring checklist. Browser only.',
            ]
        );

        if ($article->published_at !== null || $article->status !== 'draft') {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['fullstack-iot', 'iot', 'esp32'])->pluck('id');
        $article->tags()->sync($tagIds);

        $srcWebp = public_path('images/fsiot/fs-gate-builder-cover.webp');
        $srcJpg = public_path('images/fsiot/fs-gate-builder-cover.jpg');
        if (is_file($srcWebp)) {
            $dest = 'articles/covers/fs-gate-builder-cover.webp';
            \Illuminate\Support\Facades\Storage::disk('public')->put($dest, file_get_contents($srcWebp));
            $article->cover_image = $dest;
            $article->save();
        } elseif (is_file($srcJpg)) {
            $dest = 'articles/covers/fs-gate-builder-cover.jpg';
            \Illuminate\Support\Facades\Storage::disk('public')->put($dest, file_get_contents($srcJpg));
            $article->cover_image = $dest;
            $article->save();
        }

        $this->command?->info('✓ Gate BUILDER tersimpan sebagai DRAFT: '.$article->title);
    }

    private function toolsFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs-gate-builder-tools.png" width="1400" height="640" alt="Urutan tools: browser, kuis, skor, checklist" loading="eager" style="width:100%;height:auto;max-height:560px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Urutan meja kerja:</strong> buka browser → kerjakan kuis → Cek skor → checklist. Sumber gambar: diagram Koding Indonesia (Gate BUILDER).
  </figcaption>
</figure>
HTML;
    }

    private function toolsFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs-gate-builder-tools.png" width="1400" height="640" alt="Tool order: browser, quiz, score, checklist" loading="eager" style="width:100%;height:auto;max-height:560px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Desk order:</strong> open browser → take the quiz → Check score → checklist. Image source: diagram by Koding Indonesia (BUILDER gate).
  </figcaption>
</figure>
HTML;
    }

    private function criteriaFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs-gate-builder-criteria.png" width="1200" height="700" alt="Enam kriteria lulus fase BUILDER" loading="eager" style="width:100%;height:auto;max-height:560px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Gambar utama — kriteria lulus BUILDER.</strong> Automasi lokal · OLED/Serial · relay aman · peta pin · foto wiring · kuis ≥12/15. Sumber gambar: diagram Koding Indonesia (Gate BUILDER).
  </figcaption>
</figure>
HTML;
    }

    private function criteriaFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs-gate-builder-criteria.png" width="1200" height="700" alt="Six BUILDER phase pass criteria" loading="eager" style="width:100%;height:auto;max-height:560px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Main figure — BUILDER pass criteria.</strong> Local automation · OLED/Serial · safe relay · pin map · wiring photo · quiz ≥12/15. Image source: diagram by Koding Indonesia (BUILDER gate).
  </figcaption>
</figure>
HTML;
    }

    private function relayFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs-gate-builder-relay-contacts.png" width="1200" height="560" alt="Tiga kaki kontak relay: NC, COM, NO" loading="eager" style="width:100%;height:auto;max-height:480px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Ingat singkat:</strong> COM = kaki bersama; NO/NC = dua jalur yang dipilih. Sumber gambar: diagram Koding Indonesia (Gate BUILDER) · istilah standar kontak relay.
  </figcaption>
</figure>
HTML;
    }

    private function relayFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs-gate-builder-relay-contacts.png" width="1200" height="560" alt="Relay contact pins: NC, COM, NO" loading="eager" style="width:100%;height:auto;max-height:480px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Quick recall:</strong> COM = shared pin; NO/NC = the two paths you choose. Image source: diagram by Koding Indonesia (BUILDER gate) · standard relay contact terms.
  </figcaption>
</figure>
HTML;
    }

    private function wiringFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs-gate-builder-wiring-example.png" width="1549" height="746" alt="Contoh foto wiring meja: ESP32, BME280, OLED di breadboard" loading="eager" style="width:100%;height:auto;max-height:520px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Contoh foto wiring (honor system).</strong> Simpan foto meja praktikmu sendiri di HP/laptop — belum perlu unggah ke situs. Sumber foto: dokumentasi praktikum jalur Full Stack IoT (I2C BME280 + OLED / FS-28).
  </figcaption>
</figure>
HTML;
    }

    private function wiringFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs-gate-builder-wiring-example.png" width="1549" height="746" alt="Example desk wiring photo: ESP32, BME280, OLED on a breadboard" loading="eager" style="width:100%;height:auto;max-height:520px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Example wiring photo (honor system).</strong> Keep your own desk photo on phone/laptop — no site upload yet. Photo source: Full Stack IoT path lab docs (I2C BME280 + OLED / FS-28).
  </figcaption>
</figure>
HTML;
    }

    private function successFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs-gate-builder-success.png" width="1200" height="480" alt="Sukses gate: skor kuis ≥ 12/15" loading="eager" style="width:100%;height:auto;max-height:420px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Intinya:</strong> lulus gate = skor matching <strong>≥ 12/15</strong> + checklist 10/10. Sumber gambar: diagram Koding Indonesia (Gate BUILDER).
  </figcaption>
</figure>
HTML;
    }

    private function successFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs-gate-builder-success.png" width="1200" height="480" alt="Gate success: quiz score ≥ 12/15" loading="eager" style="width:100%;height:auto;max-height:420px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>In short:</strong> pass the gate = matching score <strong>≥ 12/15</strong> + checklist 10/10. Image source: diagram by Koding Indonesia (BUILDER gate).
  </figcaption>
</figure>
HTML;
    }

    private function body(): string
    {
        $tools = $this->toolsFigureId();
        $criteria = $this->criteriaFigureId();
        $relay = $this->relayFigureId();
        $wiring = $this->wiringFigureId();
        $success = $this->successFigureId();

        return <<<'HTML'
<h2>Pendahuluan — pintu naik fase</h2>
<p>Artikel ini adalah <strong>Gate BUILDER (ini)</strong> di jalur <em>Full Stack IoT Developer — Dari Nol</em>. Fase <strong>BUILDER</strong> (praktik lokal sampai FS-28) sudah melatih sensor, aktuator, dan bus tanpa internet. Sebelum masuk fase <strong>CONNECTED</strong> (Wi-Fi, HTTP, MQTT, …), kamu melewati <strong>pintu kuis</strong>.</p>
<p><strong>Analogi:</strong> Gate = pintu kelas berikutnya · kuis = kartu absen · skor ≥12/15 = “silakan masuk”.</p>
<p><strong>Glosarium singkat:</strong></p>
<ul>
<li><strong>Gate fase</strong> = kuis wajib sebelum naik ZERO→BUILDER→CONNECTED→…</li>
<li><strong>BUILDER</strong> = fase sensor &amp; aktuator lokal (tanpa Wi-Fi).</li>
<li><strong>CONNECTED</strong> = fase data lewat jaringan (mulai FS-29).</li>
<li><strong>Matching</strong> = cocokkan istilah dengan artinya (contoh: GPIO ↔ kaki pin serba guna).</li>
<li><strong>COM / NO / NC</strong> = tiga kaki kontak di modul relay (kaki bersama + dua jalur).</li>
</ul>
<p><strong>Prasyarat:</strong> praktik BUILDER sampai FS-28 (I2C BME280 + OLED) · paham FS-19 (<code>millis</code>) · FS-14 (Upload + Serial) sudah pernah dicoba.</p>
<p><strong>Cara pakai artikel ini (urutan kerja):</strong></p>
<ol>
<li><strong>Buka browser</strong> — kerjakan di halaman artikel ini (bukan Laragon / Arduino IDE).</li>
<li>Baca kriteria lulus BUILDER + diagram relay singkat di bawah.</li>
<li>Kerjakan <strong>kuis matching 15 soal</strong> di kotak interaktif → tekan <strong>Cek skor</strong> (target ≥ <strong>12/15</strong>).</li>
<li>Baru buka kunci jawaban jika perlu mengulang.</li>
<li>Centang checklist 10/10 (termasuk foto wiring sendiri).</li>
</ol>
<p><strong>Tidak perlu hari ini:</strong> Arduino IDE, Upload sketch, Laragon, <code>php artisan</code>, Wi-Fi, MQTT. Alat hari ini: <strong>browser</strong> (+ kertas opsional).</p>

<h2>Persiapan — buka ini dulu</h2>
<p><strong>Urutan meja kerja:</strong> browser → kuis → skor → checklist.</p>
HTML
            .$tools.<<<'HTML'
<ul>
<li>Buka artikel ini di browser (laptop atau HP).</li>
<li>Siapkan catatan opsional jika suka tulis tangan.</li>
<li>Siapkan foto wiring meja praktik BUILDER (honor system — belum upload ke situs).</li>
</ul>
<p><strong>Alat yang dipakai hari ini:</strong> browser saja. Tombol <strong>Cek skor</strong> dan kotak centang muncul di halaman artikel setelah kamu gulir ke bagian praktik.</p>
<p><strong>Tidak dipakai hari ini:</strong> Arduino IDE, Upload, Laragon, <code>php artisan</code>, Serial Monitor untuk sketch baru.</p>

<h2>Kriteria lulus BUILDER</h2>
HTML
            .$criteria.<<<'HTML'
<p><strong>Ringkasnya:</strong> sebelum Wi-Fi, pastikan fondasi lokal kuat — automasi jalan, angka sensor terbaca, relay dipakai aman, pin map dikuasai, ada dokumentasi wiring sendiri, lalu lulus kuis istilah.</p>
HTML
            .$relay.<<<'HTML'
<p><strong>Intinya:</strong> gate ini menguji <strong>paham istilah</strong> + <strong>checklist jujur</strong>, bukan Upload sketch baru.</p>

<h2 id="fsiot-kuis-matching">Praktik — kuis matching 15 soal</h2>
<p>Tutup catatan sebentar. Di halaman artikel (pratinjau atau terbit), muncul <strong>kotak kuis interaktif</strong>: pilih arti untuk tiap istilah, lalu tekan <strong>Cek skor</strong>. Versi catatan (tulis tangan) tetap tersedia sebagai cadangan di bagian “Versi catatan”.</p>
<p><strong>Kolom istilah:</strong></p>
<ol>
  <li>GPIO</li>
  <li>millis()</li>
  <li>ADC</li>
  <li>LDR</li>
  <li>Relay</li>
  <li>COM (relay)</li>
  <li>PIR</li>
  <li>PWM</li>
  <li>Servo</li>
  <li>I2C</li>
  <li>SDA</li>
  <li>SCL</li>
  <li>BME280</li>
  <li>OLED SSD1306</li>
  <li>Histeresis</li>
</ol>
<p><strong>Kolom arti (acak):</strong></p>
<ul>
  <li>A. Sensor suhu/tekanan/kelembapan lewat I2C</li>
  <li>B. Kaki pin serba guna di ESP32</li>
  <li>C. Jalur data bus I2C</li>
  <li>D. Sensor gerak inframerah pasif</li>
  <li>E. Ubah tegangan analog jadi angka</li>
  <li>F. Layar kecil I2C untuk menampilkan angka</li>
  <li>G. Aktuator yang bergerak ke sudut tertentu</li>
  <li>H. Saklar listrik yang dikendalikan GPIO</li>
  <li>I. Celah ambang supaya relay tidak “kedip”</li>
  <li>J. Bus dua kabel (data + jam)</li>
  <li>K. Sensor cahaya (nilai gelap/terang)</li>
  <li>L. Jam internal board (bukan delay membekukan)</li>
  <li>M. Jalur jam bus I2C</li>
  <li>N. Sinyal pulsa untuk kecerahan atau sudut</li>
  <li>O. Kaki bersama kontak relay (common)</li>
</ul>
<p><strong>Cara menguji:</strong> kerjakan dulu di kuis interaktif di <strong>browser</strong>, baru buka kunci. Target ≥ <strong>12/15</strong>. Tidak perlu Arduino IDE / Laragon / perintah terminal.</p>

<h2 id="fsiot-kuis-kunci">Kunci jawaban</h2>
<p>1B · 2L · 3E · 4K · 5H · 6O · 7D · 8N · 9G · 10J · 11C · 12M · 13A · 14F · 15I</p>
<p>Hitung skormu. Di bawah 12? Baca ulang istilah yang salah, ulangi matching — itu normal.</p>

<h2 id="fsiot-gate-builder-checklist">Praktik — checklist Gate BUILDER</h2>
<p>Centang setelah kamu jujur menilai diri. Target: <strong>10/10</strong>.</p>
<ul id="fsiot-gate-builder-checklist-items">
<li>Sudah praktik BUILDER sampai sensor/aktuator lokal (hingga FS-28)</li>
<li>Paham: gate = pintu kuis sebelum CONNECTED</li>
<li>Browser dibuka untuk mengerjakan kuis (bukan Laragon)</li>
<li>Kuis matching dikerjakan sebelum membuka kunci</li>
<li>Skor ≥ 12/15 (atau sudah mengulang sampai lulus)</li>
<li>Paham singkat: GPIO, millis, ADC/LDR</li>
<li>Paham singkat: relay COM/NO/NC + PIR + PWM/servo</li>
<li>Paham singkat: I2C SDA/SCL + BME280 + OLED</li>
<li>Sudah punya foto wiring sendiri (simpan di HP/laptop)</li>
<li>Sadar: langkah berikutnya CONNECTED dimulai FS-29 (Wi-Fi) saat modulnya terbit</li>
</ul>
HTML
            .$wiring.<<<'HTML'
<p><strong>Cara menguji checklist:</strong> centang di browser setelah kuis. Tidak perlu <code>php artisan</code>.</p>
HTML
            .$success.<<<'HTML'

<h2>Kesalahan yang sering terjadi</h2>
<ul>
<li><strong>Langsung ke Wi-Fi tanpa gate.</strong> Fondasi lokal dulu; gate ini yang menandai siap CONNECTED.</li>
<li><strong>Membuka kunci sebelum mencoba.</strong> Kerjakan matching dulu.</li>
<li><strong>Mengira harus Upload sketch hari ini.</strong> Hari ini cukup browser + kuis.</li>
<li><strong>Menguji di Laragon.</strong> Tidak ada perintah server yang dijalankan.</li>
<li><strong>Checklist asal centang.</strong> Foto wiring &amp; skor kuis harus benar-benar ada.</li>
<li><strong>Bingung Gate vs “segera hadir” di halaman jalur.</strong> Gate BUILDER = kuis naik fase. Tulisan “segera hadir” / coming soon di <code>/belajar</code> = kunci rilis jalur untuk pengunjung, bukan kuis ini.</li>
</ul>

<h2>Selanjutnya</h2>
<p><strong>Intinya:</strong> kalau skor ≥12/15 dan checklist 10/10, Gate BUILDER selesai — kamu siap fase <strong>CONNECTED</strong>.</p>
<p>Langkah berikutnya: <strong>FS-29</strong> (Wi-Fi dari nol) saat modulnya terbit. Soft bridge saja — belum hardlink artikel.</p>
<p>Daftar modul lengkap: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $tools = $this->toolsFigureEn();
        $criteria = $this->criteriaFigureEn();
        $relay = $this->relayFigureEn();
        $wiring = $this->wiringFigureEn();
        $success = $this->successFigureEn();

        return <<<'HTML'
<h2>Introduction — the phase doorway</h2>
<p>This article is the <strong>BUILDER gate (this article)</strong> on the <em>Full Stack IoT Developer — From Zero</em> track. The <strong>BUILDER</strong> phase (local practice through FS-28) trained sensors, actuators, and buses without the internet. Before entering <strong>CONNECTED</strong> (Wi-Fi, HTTP, MQTT, …), you pass a <strong>quiz doorway</strong>.</p>
<p><strong>Analogy:</strong> the gate = the door to the next classroom · the quiz = your attendance card · score ≥12/15 = “come in”.</p>
<p><strong>Short glossary:</strong></p>
<ul>
<li><strong>Phase gate</strong> = required quiz before ZERO→BUILDER→CONNECTED→…</li>
<li><strong>BUILDER</strong> = local sensor &amp; actuator phase (no Wi-Fi yet).</li>
<li><strong>CONNECTED</strong> = networked data phase (starts at FS-29).</li>
<li><strong>Matching</strong> = pair each term with its meaning (example: GPIO ↔ general-purpose pin).</li>
<li><strong>COM / NO / NC</strong> = three relay contact pins (shared pin + two paths).</li>
</ul>
<p><strong>Prerequisites:</strong> BUILDER practice through FS-28 (I2C BME280 + OLED) · FS-19 (<code>millis</code>) · FS-14 (Upload + Serial) already tried once.</p>
<p><strong>How to use this article (work order):</strong></p>
<ol>
<li><strong>Open a browser</strong> — work on this article page (not Laragon / Arduino IDE).</li>
<li>Read the BUILDER pass criteria + the short relay diagram below.</li>
<li>Take the <strong>15-item matching quiz</strong> in the interactive box → press <strong>Check score</strong> (target ≥ <strong>12/15</strong>).</li>
<li>Only then open the answer key if you need a retry.</li>
<li>Tick the 10/10 checklist (including your own wiring photo).</li>
</ol>
<p><strong>Not needed today:</strong> Arduino IDE, Upload sketch, Laragon, <code>php artisan</code>, Wi-Fi, MQTT. Today's tool: <strong>browser</strong> (+ optional paper).</p>

<h2>Preparation — open this first</h2>
<p><strong>Desk order:</strong> browser → quiz → score → checklist.</p>
HTML
            .$tools.<<<'HTML'
<ul>
<li>Open this article in a browser (laptop or phone).</li>
<li>Optional notes if you like handwriting.</li>
<li>Have a photo of your BUILDER wiring (honor system — no site upload yet).</li>
</ul>
<p><strong>Tools used today:</strong> browser only. The <strong>Check score</strong> button and checklist boxes appear on the article page when you scroll to the practice sections.</p>
<p><strong>Not used today:</strong> Arduino IDE, Upload, Laragon, <code>php artisan</code>, Serial Monitor for a new sketch.</p>

<h2>BUILDER pass criteria</h2>
HTML
            .$criteria.<<<'HTML'
<p><strong>In brief:</strong> before Wi-Fi, make sure the local foundation is solid — automation runs, sensor numbers are readable, relays are used safely, the pin map is owned, you have your own wiring docs, then pass the terms quiz.</p>
HTML
            .$relay.<<<'HTML'
<p><strong>In short:</strong> this gate checks <strong>term understanding</strong> + an <strong>honest checklist</strong>, not a new sketch Upload.</p>

<h2 id="fsiot-kuis-matching">Practice — matching quiz (15 items)</h2>
<p>Close your notes for a moment. On the article page (preview or published), an <strong>interactive quiz box</strong> appears: pick a meaning for each term, then press <strong>Check score</strong>. A paper version remains available under “Paper version” as a backup.</p>
<p><strong>Terms column:</strong></p>
<ol>
  <li>GPIO</li>
  <li>millis()</li>
  <li>ADC</li>
  <li>LDR</li>
  <li>Relay</li>
  <li>COM (relay)</li>
  <li>PIR</li>
  <li>PWM</li>
  <li>Servo</li>
  <li>I2C</li>
  <li>SDA</li>
  <li>SCL</li>
  <li>BME280</li>
  <li>OLED SSD1306</li>
  <li>Hysteresis</li>
</ol>
<p><strong>Meanings column (shuffled):</strong></p>
<ul>
  <li>A. Temperature/pressure/humidity sensor over I2C</li>
  <li>B. General-purpose pin on the ESP32</li>
  <li>C. I2C data line</li>
  <li>D. Passive infrared motion sensor</li>
  <li>E. Turn analog voltage into a number</li>
  <li>F. Small I2C display for numbers</li>
  <li>G. Actuator that moves to an angle</li>
  <li>H. Electrical switch controlled by a GPIO</li>
  <li>I. Threshold gap so a relay does not chatter</li>
  <li>J. Two-wire bus (data + clock)</li>
  <li>K. Light sensor (dark/bright values)</li>
  <li>L. Board’s internal clock (not a freezing delay)</li>
  <li>M. I2C clock line</li>
  <li>N. Pulse signal for brightness or angle</li>
  <li>O. Shared relay contact pin (common)</li>
</ul>
<p><strong>How to test:</strong> try the interactive quiz in the <strong>browser</strong> first, then open the key. Target ≥ <strong>12/15</strong>. No Arduino IDE / Laragon / terminal commands.</p>

<h2 id="fsiot-kuis-kunci">Answer key</h2>
<p>1B · 2L · 3E · 4K · 5H · 6O · 7D · 8N · 9G · 10J · 11C · 12M · 13A · 14F · 15I</p>
<p>Count your score. Below 12? Re-read the wrong terms and match again — that is normal.</p>

<h2 id="fsiot-gate-builder-checklist">Practice — BUILDER gate checklist</h2>
<p>Tick after an honest self-check. Target: <strong>10/10</strong>.</p>
<ul id="fsiot-gate-builder-checklist-items">
<li>BUILDER practice done through local sensors/actuators (through FS-28)</li>
<li>I know: the gate = quiz doorway before CONNECTED</li>
<li>Browser opened to take the quiz (not Laragon)</li>
<li>Matching quiz done before opening the key</li>
<li>Score ≥ 12/15 (or retried until pass)</li>
<li>Brief grasp: GPIO, millis, ADC/LDR</li>
<li>Brief grasp: relay COM/NO/NC + PIR + PWM/servo</li>
<li>Brief grasp: I2C SDA/SCL + BME280 + OLED</li>
<li>I have my own wiring photo (saved on phone/laptop)</li>
<li>I know: next CONNECTED step is FS-29 (Wi-Fi) when that module ships</li>
</ul>
HTML
            .$wiring.<<<'HTML'
<p><strong>How to test the checklist:</strong> tick in the browser after the quiz. No <code>php artisan</code>.</p>
HTML
            .$success.<<<'HTML'

<h2>Common mistakes</h2>
<ul>
<li><strong>Jumping to Wi-Fi without the gate.</strong> Local foundation first; this gate marks CONNECTED readiness.</li>
<li><strong>Opening the key before trying.</strong> Match first.</li>
<li><strong>Thinking you must Upload a sketch today.</strong> Today is browser + quiz only.</li>
<li><strong>Testing in Laragon.</strong> No server commands are run.</li>
<li><strong>Checking the checklist casually.</strong> Wiring photo and quiz score must be real.</li>
<li><strong>Confusing this gate with “coming soon” on the path page.</strong> BUILDER gate = phase-up quiz. “Coming soon” on <code>/belajar</code> = path release lock for guests, not this quiz.</li>
</ul>

<h2>Next</h2>
<p><strong>In short:</strong> if score ≥12/15 and checklist 10/10, the BUILDER gate is done — you are ready for <strong>CONNECTED</strong>.</p>
<p>Next step: <strong>FS-29</strong> (Wi-Fi from zero) when that module ships. Soft bridge only — no hard article link yet.</p>
<p>Full module list: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }
}
