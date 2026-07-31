<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article72Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first()
            ?? Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $iotCat) {
            throw new \RuntimeException('User atau kategori iot-smart-device/esp32-arduino tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-satu-gambar-jalur';

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
                'title'              => 'Full Stack IoT: satu gambar untuk seluruh jalur',
                'title_en'           => 'Full Stack IoT: one picture for the whole path',
                'excerpt'            => 'FS-02 / #72: Peta resmi jalur — tujuh lapisan + lima fase. Hafal arah belajar tanpa loncat ke Wi‑Fi. Belum wiring.',
                'excerpt_en'         => 'FS-02 / #72: Official path map — seven layers + five phases. Learn the direction without jumping to Wi‑Fi. No wiring yet.',
                'body'               => $this->body(),
                'body_en'            => $this->bodyEn(),
                'status'             => 'draft',
                'is_featured'        => false,
                'published_at'       => null,
                'seo_title'          => 'Satu Gambar Full Stack IoT — Peta Jalur #72',
                'seo_title_en'       => 'One Picture Full Stack IoT — Path Map #72',
                'seo_description'    => 'Peta resmi Full Stack IoT: dunia nyata sampai production, fase ZERO sampai HERO. Modul FS-02 dari nol.',
                'seo_description_en' => 'Official Full Stack IoT map: real world to production, ZERO to HERO phases. Module FS-02 from zero.',
            ]
        );

        if ($article->published_at !== null || $article->status !== 'draft') {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['fullstack-iot', 'iot', 'esp32'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel #72 / FS-02 tersimpan sebagai DRAFT: '.$article->title);
    }

    private function layersSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Tujuh lapisan Full Stack IoT dari dunia nyata sampai production" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 760 520" width="100%" height="auto" role="img" aria-label="Diagram tujuh lapisan">
  <text x="380" y="28" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="800" fill="#1a1a1a">SATU GAMBAR — tujuh lapisan</text>
  <rect x="40" y="50" width="680" height="42" fill="#FFF3E0" stroke="#1a1a1a" stroke-width="2"/>
  <text x="380" y="77" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">1. Dunia nyata — meja, lampu, udara, tanah</text>
  <text x="380" y="108" text-anchor="middle" font-family="system-ui,sans-serif" font-size="18">↓</text>
  <rect x="40" y="118" width="680" height="42" fill="#EBF4FF" stroke="#1a1a1a" stroke-width="2"/>
  <text x="380" y="145" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">2. Perangkat — board ESP32-DevKitC-1 + sensor/lampu</text>
  <text x="380" y="176" text-anchor="middle" font-family="system-ui,sans-serif" font-size="18">↓</text>
  <rect x="40" y="186" width="680" height="42" fill="#E8F5E9" stroke="#1a1a1a" stroke-width="2"/>
  <text x="380" y="213" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">3. Jaringan — jalan data (Wi‑Fi / kabel) nanti</text>
  <text x="380" y="244" text-anchor="middle" font-family="system-ui,sans-serif" font-size="18">↓</text>
  <rect x="40" y="254" width="680" height="42" fill="#F3E5F5" stroke="#1a1a1a" stroke-width="2"/>
  <text x="380" y="281" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">4. Pintu kecil (edge) — dekat perangkat, opsional</text>
  <text x="380" y="312" text-anchor="middle" font-family="system-ui,sans-serif" font-size="18">↓</text>
  <rect x="40" y="322" width="680" height="42" fill="#E0F7FA" stroke="#1a1a1a" stroke-width="2"/>
  <text x="380" y="349" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">5. Penyimpanan (backend)</text>
  <text x="380" y="380" text-anchor="middle" font-family="system-ui,sans-serif" font-size="18">↓</text>
  <rect x="40" y="390" width="680" height="42" fill="#FFF8E1" stroke="#1a1a1a" stroke-width="2"/>
  <text x="380" y="417" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">6. Layar HP / dashboard</text>
  <text x="380" y="448" text-anchor="middle" font-family="system-ui,sans-serif" font-size="18">↓</text>
  <rect x="40" y="458" width="680" height="42" fill="#FFEBEE" stroke="#1a1a1a" stroke-width="2"/>
  <text x="380" y="485" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">7. Siap pakai sehari-hari (production) — aman &amp; dirawat jarak jauh</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Diagram resmi jalur Full Stack IoT (buatan Koding Indonesia). Baca dari atas ke bawah seperti cerita — detail tiap kotak datang di modul berikutnya.</figcaption>
</figure>
SVG;
    }

    private function layersSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Seven Full Stack IoT layers from the real world to production" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 760 520" width="100%" height="auto" role="img" aria-label="Seven-layer diagram">
  <text x="380" y="28" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="800" fill="#1a1a1a">ONE PICTURE — seven layers</text>
  <rect x="40" y="50" width="680" height="42" fill="#FFF3E0" stroke="#1a1a1a" stroke-width="2"/>
  <text x="380" y="77" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">1. Real world — desk, lamp, air, soil</text>
  <text x="380" y="108" text-anchor="middle" font-family="system-ui,sans-serif" font-size="18">↓</text>
  <rect x="40" y="118" width="680" height="42" fill="#EBF4FF" stroke="#1a1a1a" stroke-width="2"/>
  <text x="380" y="145" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">2. Device — ESP32-DevKitC-1 board + sensors/lamp</text>
  <text x="380" y="176" text-anchor="middle" font-family="system-ui,sans-serif" font-size="18">↓</text>
  <rect x="40" y="186" width="680" height="42" fill="#E8F5E9" stroke="#1a1a1a" stroke-width="2"/>
  <text x="380" y="213" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">3. Network — data road (Wi‑Fi / cable) later</text>
  <text x="380" y="244" text-anchor="middle" font-family="system-ui,sans-serif" font-size="18">↓</text>
  <rect x="40" y="254" width="680" height="42" fill="#F3E5F5" stroke="#1a1a1a" stroke-width="2"/>
  <text x="380" y="281" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">4. Small door (edge) — near the device, optional</text>
  <text x="380" y="312" text-anchor="middle" font-family="system-ui,sans-serif" font-size="18">↓</text>
  <rect x="40" y="322" width="680" height="42" fill="#E0F7FA" stroke="#1a1a1a" stroke-width="2"/>
  <text x="380" y="349" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">5. Storage (backend)</text>
  <text x="380" y="380" text-anchor="middle" font-family="system-ui,sans-serif" font-size="18">↓</text>
  <rect x="40" y="390" width="680" height="42" fill="#FFF8E1" stroke="#1a1a1a" stroke-width="2"/>
  <text x="380" y="417" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">6. Phone screen / dashboard</text>
  <text x="380" y="448" text-anchor="middle" font-family="system-ui,sans-serif" font-size="18">↓</text>
  <rect x="40" y="458" width="680" height="42" fill="#FFEBEE" stroke="#1a1a1a" stroke-width="2"/>
  <text x="380" y="485" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">7. Everyday-ready (production) — safe &amp; remotely maintained</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Official Full Stack IoT path diagram (by Koding Indonesia). Read top to bottom like a story — box detail comes in later modules.</figcaption>
</figure>
SVG;
    }

    private function phasesSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Lima fase belajar ZERO sampai HERO" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 760 160" width="100%" height="auto" role="img" aria-label="Peta fase">
  <rect x="10" y="40" width="130" height="70" fill="#2979FF" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="75" y="70" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="800" fill="#fff">ZERO</text>
  <text x="75" y="90" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#EBF4FF">kamu di sini</text>
  <text x="150" y="80" font-family="system-ui,sans-serif" font-size="20">→</text>
  <rect x="170" y="40" width="110" height="70" fill="#fff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="225" y="80" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">BUILDER</text>
  <text x="290" y="80" font-family="system-ui,sans-serif" font-size="20">→</text>
  <rect x="310" y="40" width="120" height="70" fill="#fff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="370" y="80" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">CONNECTED</text>
  <text x="440" y="80" font-family="system-ui,sans-serif" font-size="20">→</text>
  <rect x="460" y="40" width="120" height="70" fill="#fff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="520" y="80" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">FULLSTACK</text>
  <text x="590" y="80" font-family="system-ui,sans-serif" font-size="20">→</text>
  <rect x="610" y="40" width="130" height="70" fill="#fff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="675" y="80" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">HERO</text>
  <text x="380" y="140" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Belajar berurutan — jangan loncat fase</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Peta fase jalur. Kotak biru = posisi kita sekarang (ZERO / fondasi dari nol).</figcaption>
</figure>
SVG;
    }

    private function phasesSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Five learning phases from ZERO to HERO" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 760 160" width="100%" height="auto" role="img" aria-label="Phase map">
  <rect x="10" y="40" width="130" height="70" fill="#2979FF" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="75" y="70" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="800" fill="#fff">ZERO</text>
  <text x="75" y="90" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#EBF4FF">you are here</text>
  <text x="150" y="80" font-family="system-ui,sans-serif" font-size="20">→</text>
  <rect x="170" y="40" width="110" height="70" fill="#fff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="225" y="80" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">BUILDER</text>
  <text x="290" y="80" font-family="system-ui,sans-serif" font-size="20">→</text>
  <rect x="310" y="40" width="120" height="70" fill="#fff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="370" y="80" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">CONNECTED</text>
  <text x="440" y="80" font-family="system-ui,sans-serif" font-size="20">→</text>
  <rect x="460" y="40" width="120" height="70" fill="#fff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="520" y="80" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">FULLSTACK</text>
  <text x="590" y="80" font-family="system-ui,sans-serif" font-size="20">→</text>
  <rect x="610" y="40" width="130" height="70" fill="#fff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="675" y="80" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">HERO</text>
  <text x="380" y="140" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Learn in order — do not skip phases</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Path phase map. Blue box = where we are now (ZERO / foundation from zero).</figcaption>
</figure>
SVG;
    }

    private function body(): string
    {
        $layers = $this->layersSvgId();
        $phases = $this->phasesSvgId();

        return <<<HTML
<h2>Pendahuluan — kenapa butuh satu gambar?</h2>
<p>Artikel ini adalah <strong>#72 (ini)</strong> · modul <strong>FS-02</strong> di jalur <strong>Full Stack IoT Developer — Dari Nol</strong>. Di <strong>#71 (FS-01)</strong> kamu sudah bisa menjawab “IoT itu apa?”. Hari ini kita pakai <strong>satu gambar</strong> supaya perjalanan belajar tidak terasa acak.</p>
<p>Bayangkan peta kereta. Tanpa peta, kamu naik kereta mana saja. Dengan peta, kamu tahu stasiun sekarang dan stasiun berikutnya — tanpa harus hafal mesin kereta dulu.</p>
<blockquote>
  <p><strong>Prasyarat:</strong> paham ide IoT dari FS-01. Belum perlu board, kabel, atau unduhan perangkat lunak.</p>
</blockquote>

<p><strong>Cara pakai artikel ini (urutan baca):</strong></p>
<ol>
<li><strong>Buka browser</strong> — baca artikel di laptop atau HP.</li>
<li><strong>Siapkan catatan</strong> (opsional) — kertas/Notepad jika lebih suka tulis tangan.</li>
<li><strong>Lihat satu gambar</strong> tujuh lapisan + peta fase (ZERO).</li>
<li><strong>Baca satu kalimat per kotak</strong> — hafal cerita panah, bukan jargon.</li>
<li><strong>Isi worksheet interaktif</strong> di browser → Cek kelengkapan (fase ZERO).</li>
</ol>
<p><strong>Tidak perlu hari ini:</strong> Laragon, Arduino IDE, terminal, USB board, <code>php artisan</code>, unggah sketch.</p>

<h2>Persiapan — alat yang kamu buka hari ini</h2>
<p><strong>Alat yang dipakai di artikel ini</strong> (belum Laragon, belum Arduino IDE, belum USB board, belum terminal):</p>
<ul>
  <li><strong>Browser</strong> — membaca artikel + mengerjakan <strong>worksheet interaktif</strong> di akhir (Chrome, Edge, Firefox, atau browser HP).</li>
  <li><strong>Catatan</strong> (opsional) — kertas / Notepad hanya jika kamu lebih suka versi tulis tangan.</li>
</ul>
<p><strong>Tidak ada perintah sintaks hari ini.</strong> Tidak ada baris kode, tidak ada <code>php artisan</code>, tidak ada sketch Arduino. Cara “menguji” di FS-02 = isi tujuh kotak di worksheet interaktif (satu kalimat peran dengan bahasamu sendiri) + pastikan fase <strong>ZERO</strong>.</p>

<h2>Satu gambar resmi — tujuh lapisan</h2>
<p>Ini diagram yang akan kita pakai ulang sepanjang jalur. Panah mengalir dari atas (dunia nyata) ke bawah (sistem siap pakai).</p>
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/fs02-real-world-lamp.jpg" width="554" height="1200" alt="Lampu di meja belajar — contoh dunia nyata untuk Stasiun Ruang Belajar" loading="lazy" style="width:100%;height:auto;max-height:420px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    Contoh <strong>dunia nyata</strong> di jalur ini: lampu di meja belajar (nanti jadi bagian Stasiun Ruang Belajar). Belum merakit board hari ini — foto ini hanya gambaran “benda di meja”, bukan target wiring FS-02.
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:LIFX_bulbs.jpg" rel="noopener noreferrer" target="_blank">AG20044018 — LIFX bulbs</a> · Wikimedia Commons (CC BY-SA 4.0).
  </figcaption>
</figure>
{$layers}
<p><strong>Intinya:</strong> baca dari atas ke bawah seperti cerita: benda di meja → otak kecil di board → jalan data → (kadang) pintu kecil → tempat simpan → layar HP → baru urusan “siap dipakai setiap hari”.</p>
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/esp32-devkitc-overview.jpg" width="1200" height="519" alt="Board ESP32-DevKitC — contoh kotak perangkat di peta, belum wiring hari ini" loading="lazy" style="width:100%;height:auto;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    Contoh isi kotak <strong>perangkat</strong> nanti: <strong>ESP32-DevKitC-1</strong>. Di FS-02 cukup kenal namanya di peta — pin &amp; kabel belakangan.
    <br>Sumber gambar: <a href="https://docs.espressif.com/projects/esp-dev-kits/en/latest/esp32/esp32-devkitc/user_guide.html" rel="noopener noreferrer" target="_blank">Espressif Systems — ESP32-DevKitC User Guide</a> (dokumen resmi).
  </figcaption>
</figure>


<h2 id="fsiot-layer-roles">Satu kalimat per kotak</h2>
<table>
  <thead>
    <tr>
      <th>Kotak</th>
      <th>Peran (satu kalimat)</th>
      <th>Analogi rumah</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Dunia nyata</td>
      <td>Yang kita ukur atau kendalikan di meja/ruang.</td>
      <td>Suhu kamar, lampu belajar</td>
    </tr>
    <tr>
      <td>Perangkat (device)</td>
      <td>Board + sensor/lampu yang “merasakan” atau “bergerak”.</td>
      <td>ESP32-DevKitC-1 + sensor (nanti)</td>
    </tr>
    <tr>
      <td>Jaringan</td>
      <td>Jalan agar data/perintah bisa pergi jauh.</td>
      <td>Wi‑Fi rumah (belum sekarang)</td>
    </tr>
    <tr>
      <td>Pintu kecil (edge)</td>
      <td>Pintu dekat perangkat (opsional) sebelum data jauh.</td>
      <td>Resepsionis di pintu rumah</td>
    </tr>
    <tr>
      <td>Penyimpanan (backend)</td>
      <td>Tempat menyimpan dan mengolah data.</td>
      <td>Lemari arsip / catatan di komputer</td>
    </tr>
    <tr>
      <td>Layar / aplikasi</td>
      <td>Layar yang kamu baca atau tombol yang kamu tekan.</td>
      <td>Dashboard di HP/laptop</td>
    </tr>
    <tr>
      <td>Siap pakai (production)</td>
      <td>Sistem yang aman dan bisa dirawat jarak jauh.</td>
      <td>Rumah yang sudah “siap ditinggali”</td>
    </tr>
  </tbody>
</table>
<p><strong>Tips:</strong> jangan hafal istilah Inggris dulu. Hafal <em>cerita panah</em>. Nama teknis diperdalam di modul kamus (FS-03) dan modul masing-masing.</p>

<h2>Peta fase — kita di mana?</h2>
<p>Selain tujuh lapisan, jalur dibagi <strong>lima fase</strong>. Nama fasenya sengaja singkat supaya mudah diingat:</p>
{$phases}
<ul>
  <li><strong>ZERO</strong> — fondasi dari nol: peta, alat, listrik, latihan coding mini. <em>Tanpa Wi‑Fi dulu.</em> ← <strong>kamu di sini</strong></li>
  <li><strong>BUILDER</strong> — perangkat hidup di meja (sensor, lampu) tanpa internet.</li>
  <li><strong>CONNECTED</strong> — baru belajar menghubungkan ke jaringan.</li>
  <li><strong>FULLSTACK</strong> — data tersimpan di komputer/server + terlihat di layar dashboard.</li>
  <li><strong>HERO</strong> — lebih aman, hemat daya, bisa di-update dari jauh, proyek utuh.</li>
</ul>
<p><strong>Analogi:</strong> fase seperti level game. Jangan loncat ke “CONNECTED” hanya karena penasaran Wi‑Fi — fondasi ZERO mencegah kabel salah sambung dan bingung istilah.</p>

<h2>Stasiun Ruang Belajar di peta</h2>
<p>Proyek benang merah kita tetap <strong>Stasiun Ruang Belajar</strong>. Cara membacanya di peta:</p>
<ul>
  <li><strong>ZERO:</strong> paham tujuan + peta + alat (belum merakit).</li>
  <li><strong>BUILDER:</strong> sensor suhu/cahaya + lampu hidup di meja (lokal).</li>
  <li><strong>CONNECTED:</strong> data mulai “pergi” lewat jaringan.</li>
  <li><strong>FULLSTACK:</strong> angka muncul di dashboard; bisa kirim perintah.</li>
  <li><strong>HERO:</strong> stasiun lebih aman dan siap dirawat.</li>
</ul>
<p><strong>Intinya:</strong> satu proyek, banyak fase — bukan lima proyek berbeda.</p>

<h2>Mengapa jangan loncat ke Wi‑Fi?</h2>
<p>Banyak pemula ingin langsung “nyambung internet”. Di peta, Wi‑Fi ada di kotak <strong>jaringan</strong> — dan fase <strong>CONNECTED</strong>. Kalau kamu loncat:</p>
<ul>
  <li>kabel salah sambung → sulit bedakan error kabel vs error Wi‑Fi,</li>
  <li>istilah bertumpuk → mudah menyerah,</li>
  <li>board belum sempat mengirim status sederhana ke komputer → kamu belum punya bukti lokal bahwa perangkat “hidup”.</li>
</ul>
<p><strong>Tips:</strong> pelajari dulu “lampu di meja hidup tanpa internet”. Baru kemudian “kirim kabar ke HP”.</p>

<h2 id="fsiot-worksheet-boxes">Praktik — worksheet isi kotak</h2>
<p>Tutup tabel di atas sebentar. Di bawah ini ada <strong>worksheet interaktif</strong>: tulis satu kalimat peran untuk tiap kotak, pilih fase <strong>ZERO</strong>, lalu tekan <strong>Cek kelengkapan</strong>. Versi catatan (tulis tangan) tetap tersedia sebagai cadangan.</p>
<ol>
  <li>Untuk tiap kotak, tulis <strong>satu kalimat peran</strong> dengan bahasamu sendiri (bukan salin paste panjang).</li>
  <li>Pilih / pastikan fase yang sedang kita jalani: <strong>ZERO</strong>.</li>
  <li>Baru buka contoh jawaban kalau sudah mencoba sendiri.</li>
</ol>
<table>
  <thead>
    <tr>
      <th>Kotak</th>
      <th>Peran (tulis sendiri)</th>
    </tr>
  </thead>
  <tbody>
    <tr><td>Dunia nyata</td><td>________________________</td></tr>
    <tr><td>Perangkat</td><td>________________________</td></tr>
    <tr><td>Jaringan</td><td>________________________</td></tr>
    <tr><td>Pintu kecil</td><td>________________________</td></tr>
    <tr><td>Penyimpanan</td><td>________________________</td></tr>
    <tr><td>Layar / aplikasi</td><td>________________________</td></tr>
    <tr><td>Siap pakai</td><td>________________________</td></tr>
  </tbody>
</table>
<p><strong>Cara menguji:</strong> kerjakan dulu di worksheet interaktif di browser. Opsional: sebutkan tiap kotak + satu kalimat ke orang non-teknis — kalau dia mengangguk, kamu paham. Tidak perlu menjalankan perintah komputer apa pun.</p>

<h2>Kesalahan yang sering terjadi</h2>
<ol>
  <li><strong>Langsung loncat ke coding Wi‑Fi.</strong> Wi‑Fi ada di fase CONNECTED — setelah fondasi ZERO dan BUILDER.</li>
  <li><strong>Hafal nama kotak tanpa paham peran.</strong> Satu kalimat peran lebih penting daripada hafalan istilah.</li>
  <li><strong>Mengira “pintu kecil / edge” wajib dari hari pertama.</strong> Bagian ini opsional; banyak proyek kecil langsung perangkat → jaringan → penyimpanan.</li>
  <li><strong>Mengira satu fase = selesai semua lapisan.</strong> Lapisan dibangun pelan; fase menandai fokus belajar.</li>
  <li><strong>Membuang peta setelah FS-02.</strong> Diagram ini akan dipakai ulang — simpan catatanmu.</li>
  <li><strong>Mencampur Seri ESP32 lama sebagai prasyarat.</strong> Jalur ini mandiri dari nol. Artikel terkait di bawah halaman bisa dari topik lama; itu bukan syarat FS-02.</li>
</ol>

<h2>Lanjut belajar</h2>
<p>Setelah FS-02, langkah alami berikutnya adalah <strong>FS-03 — kamus mini IoT</strong> (istilah yang akan sering muncul, dengan analogi). Modul itu sudah ada di jalur draft; tautan publik artikel dibuka saat rilis supaya tidak ada tautan mati.</p>
<p>Simpan juga <a href="/belajar/fullstack-iot">halaman jalur Full Stack IoT</a> sebagai pintu masuk resmi.</p>

<h2>Kesimpulan</h2>
<p>Di <strong>#72 (ini)</strong> kamu punya <strong>satu gambar</strong> tujuh lapisan dan peta lima fase. Kamu sekarang di <strong>ZERO</strong>. Board resmi nanti tetap <strong>ESP32-DevKitC-1</strong> — masih di kotak perangkat, belum wiring.</p>
<p>Kalau kamu bisa menunjuk tiap kotak dan bilang perannya dalam satu napas, FS-02 selesai. Lanjut ke kamus mini di FS-03 saat modulnya terbit.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $layers = $this->layersSvgEn();
        $phases = $this->phasesSvgEn();

        return <<<HTML
<h2>Introduction — why one picture?</h2>
<p>This article is <strong>#72 (this article)</strong> · module <strong>FS-02</strong> on the <strong>Full Stack IoT Developer — From Zero</strong> path. In <strong>#71 (FS-01)</strong> you can already answer “What is IoT?”. Today we use <strong>one picture</strong> so the learning journey does not feel random.</p>
<p>Imagine a train map. Without a map, you board any train. With a map, you know the current station and the next one — without memorizing the locomotive first.</p>
<blockquote>
  <p><strong>Prerequisites:</strong> the IoT idea from FS-01. No board, cables, or software downloads yet.</p>
</blockquote>

<p><strong>How to use this article (read in order):</strong></p>
<ol>
<li><strong>Open a browser</strong> — read on a laptop or phone.</li>
<li><strong>Prepare notes</strong> (optional) — paper/Notepad if you prefer handwriting.</li>
<li><strong>Look at one picture</strong> of seven layers + the phase map (ZERO).</li>
<li><strong>Read one sentence per box</strong> — memorize the arrow story, not jargon.</li>
<li><strong>Fill the interactive worksheet</strong> in the browser → Check completeness (phase ZERO).</li>
</ol>
<p><strong>Not needed today:</strong> Laragon, Arduino IDE, a terminal, USB board, <code>php artisan</code>, sketch upload.</p>

<h2>Preparation — tools you open today</h2>
<p><strong>Tools used in this article</strong> (no Laragon, no Arduino IDE, no USB board, no terminal yet):</p>
<ul>
  <li><strong>Browser</strong> — to read this article and complete the <strong>interactive worksheet</strong> at the end (Chrome, Edge, Firefox, or a phone browser).</li>
  <li><strong>Notes</strong> (optional) — paper / Notepad only if you prefer handwriting.</li>
</ul>
<p><strong>There is no syntax to run today.</strong> No code lines, no <code>php artisan</code>, no Arduino sketch. “Testing” in FS-02 means filling seven boxes on the interactive worksheet (one role sentence in your own words) and confirming phase <strong>ZERO</strong>.</p>

<h2>The official picture — seven layers</h2>
<p>This is the diagram we will reuse along the path. Arrows flow from top (real world) to bottom (ready-to-use system).</p>
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/fs02-real-world-lamp.jpg" width="554" height="1200" alt="Lamp on a study desk — real-world example for Study Room Station" loading="lazy" style="width:100%;height:auto;max-height:420px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    Example of the <strong>real world</strong> on this path: a lamp on the study desk (later part of Study Room Station). No board assembly today — this photo is only “thing on the desk,” not FS-02 wiring.
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:LIFX_bulbs.jpg" rel="noopener noreferrer" target="_blank">AG20044018 — LIFX bulbs</a> · Wikimedia Commons (CC BY-SA 4.0).
  </figcaption>
</figure>
{$layers}
<p><strong>In short:</strong> read top to bottom like a story: thing on the desk → small brain on the board → data road → (sometimes) a small door → storage place → phone screen → then “ready for everyday use”.</p>
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/esp32-devkitc-overview.jpg" width="1200" height="519" alt="ESP32-DevKitC board — device-box example on the map, no wiring today" loading="lazy" style="width:100%;height:auto;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    Example of the <strong>device</strong> box later: <strong>ESP32-DevKitC-1</strong>. In FS-02 just know its name on the map — pins and cables come later.
    <br>Image source: <a href="https://docs.espressif.com/projects/esp-dev-kits/en/latest/esp32/esp32-devkitc/user_guide.html" rel="noopener noreferrer" target="_blank">Espressif Systems — ESP32-DevKitC User Guide</a> (official docs).
  </figcaption>
</figure>


<h2 id="fsiot-layer-roles">One sentence per box</h2>
<table>
  <thead>
    <tr>
      <th>Box</th>
      <th>Role (one sentence)</th>
      <th>Home analogy</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Real world</td>
      <td>What we measure or control in the room.</td>
      <td>Room temperature, study lamp</td>
    </tr>
    <tr>
      <td>Device</td>
      <td>Board + sensors/lamp that “sense” or “act”.</td>
      <td>ESP32-DevKitC-1 + sensor (later)</td>
    </tr>
    <tr>
      <td>Network</td>
      <td>The road so data/commands can travel far.</td>
      <td>Home Wi‑Fi (not yet)</td>
    </tr>
    <tr>
      <td>Small door (edge)</td>
      <td>A door near the device (optional) before data goes far.</td>
      <td>A receptionist at the front door</td>
    </tr>
    <tr>
      <td>Storage (backend)</td>
      <td>Where data is stored and processed.</td>
      <td>A filing cabinet / notes on a computer</td>
    </tr>
    <tr>
      <td>Screen / app</td>
      <td>The screen you read or the button you press.</td>
      <td>Dashboard on phone/laptop</td>
    </tr>
    <tr>
      <td>Everyday-ready (production)</td>
      <td>A system that is safe and can be maintained remotely.</td>
      <td>A house ready to live in</td>
    </tr>
  </tbody>
</table>
<p><strong>Tip:</strong> do not memorize English jargon first. Memorize the <em>arrow story</em>. Technical names deepen in the glossary module (FS-03) and later modules.</p>

<h2>Phase map — where are we?</h2>
<p>Besides seven layers, the path has <strong>five phases</strong>. Short names on purpose:</p>
{$phases}
<ul>
  <li><strong>ZERO</strong> — foundation from zero: map, tools, electricity, mini coding practice. <em>No Wi‑Fi yet.</em> ← <strong>you are here</strong></li>
  <li><strong>BUILDER</strong> — devices alive on the desk (sensors, lamp) without internet.</li>
  <li><strong>CONNECTED</strong> — then learn to connect to a network.</li>
  <li><strong>FULLSTACK</strong> — data stored on a computer/server + visible on a dashboard screen.</li>
  <li><strong>HERO</strong> — safer, power-aware, remotely updatable, full project.</li>
</ul>
<p><strong>Analogy:</strong> phases are like game levels. Do not jump to “CONNECTED” just because Wi‑Fi is exciting — ZERO foundations prevent wrong cable wiring and term confusion.</p>

<h2>Study Room Station on the map</h2>
<p>Our story project remains <strong>Study Room Station</strong>. How to read it on the map:</p>
<ul>
  <li><strong>ZERO:</strong> understand the goal + map + tools (no assembly yet).</li>
  <li><strong>BUILDER:</strong> temperature/light sensors + lamp alive on the desk (local).</li>
  <li><strong>CONNECTED:</strong> data starts “traveling” over the network.</li>
  <li><strong>FULLSTACK:</strong> numbers appear on a dashboard; you can send commands.</li>
  <li><strong>HERO:</strong> the station is safer and ready to maintain.</li>
</ul>
<p><strong>In short:</strong> one project, many phases — not five different projects.</p>

<h2>Why not jump to Wi‑Fi?</h2>
<p>Many people want internet on day one. On the map, Wi‑Fi sits in the <strong>network</strong> box — and the <strong>CONNECTED</strong> phase. If you jump:</p>
<ul>
  <li>wrong cable wiring → hard to tell cable error vs Wi‑Fi error,</li>
  <li>terms pile up → easy to quit,</li>
  <li>the board has not yet sent a simple status to a computer → you lack local proof that the device is “alive”.</li>
</ul>
<p><strong>Tip:</strong> first learn “lamp on the desk works without internet.” Then “send news to the phone.”</p>

<h2 id="fsiot-worksheet-boxes">Practice — fill-the-boxes worksheet</h2>
<p>Close the table above for a moment. Below is an <strong>interactive worksheet</strong>: write one role sentence for each box, select phase <strong>ZERO</strong>, then press <strong>Check completeness</strong>. A handwritten paper version stays available as backup.</p>
<ol>
  <li>For each box, write <strong>one role sentence</strong> in your own words (not a long copy-paste).</li>
  <li>Select / confirm the phase we are in: <strong>ZERO</strong>.</li>
  <li>Only then open the sample answers after you have tried yourself.</li>
</ol>
<table>
  <thead>
    <tr>
      <th>Box</th>
      <th>Role (write yourself)</th>
    </tr>
  </thead>
  <tbody>
    <tr><td>Real world</td><td>________________________</td></tr>
    <tr><td>Device</td><td>________________________</td></tr>
    <tr><td>Network</td><td>________________________</td></tr>
    <tr><td>Small door</td><td>________________________</td></tr>
    <tr><td>Storage</td><td>________________________</td></tr>
    <tr><td>Screen / app</td><td>________________________</td></tr>
    <tr><td>Everyday-ready</td><td>________________________</td></tr>
  </tbody>
</table>
<p><strong>How to test:</strong> complete the interactive worksheet in the browser first. Optional: say each box + one sentence to a non-technical person — if they nod, you understand. You do not need to run any computer command.</p>

<h2>Common mistakes</h2>
<ol>
  <li><strong>Jumping straight into Wi‑Fi coding.</strong> Wi‑Fi belongs to CONNECTED — after ZERO and BUILDER foundations.</li>
  <li><strong>Memorizing box names without understanding roles.</strong> One role sentence beats term drilling.</li>
  <li><strong>Thinking the “small door / edge” is required on day one.</strong> That part is optional; many small projects go device → network → storage.</li>
  <li><strong>Thinking one phase finishes every layer.</strong> Layers grow slowly; phases mark learning focus.</li>
  <li><strong>Throwing away the map after FS-02.</strong> We will reuse this diagram — keep your notes.</li>
  <li><strong>Mixing old ESP32 series as a prerequisite.</strong> This path is independent from zero. Related articles below may be older IoT topics; they are not FS-02 requirements.</li>
</ol>

<h2>Keep learning</h2>
<p>After FS-02, the natural next step is <strong>FS-03 — mini IoT glossary</strong> (terms you will see often, with analogies). That module already exists in the draft path; public article links open at launch so visitors do not hit dead links.</p>
<p>Also bookmark the <a href="/belajar/fullstack-iot">Full Stack IoT path page</a> as the official entrance.</p>

<h2>Conclusion</h2>
<p>In <strong>#72 (this article)</strong> you have <strong>one picture</strong> of seven layers and a five-phase map. You are in <strong>ZERO</strong>. The official board later remains <strong>ESP32-DevKitC-1</strong> — still in the device box, no wiring yet.</p>
<p>If you can point at each box and say its role in one breath, FS-02 is done. Continue to the mini glossary in FS-03 when that module publishes.</p>
HTML;
    }
}
