<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article76Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first()
            ?? Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $iotCat) {
            throw new \RuntimeException('User atau kategori iot-smart-device/esp32-arduino tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-komputer-siap-driver-arduino-ide';

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
                'title'              => 'Komputer siap: driver USB + Arduino IDE dari nol',
                'title_en'           => 'Computer ready: USB driver + Arduino IDE from zero',
                'excerpt'            => 'FS-06 / #76: Driver CP210x/CH340, Arduino IDE 2.x, Board Manager ESP32, pilih COM, upload pertama. Tools-first untuk pemula.',
                'excerpt_en'         => 'FS-06 / #76: CP210x/CH340 drivers, Arduino IDE 2.x, ESP32 Board Manager, pick COM port, first upload. Tools-first from zero.',
                'body'               => $this->body(),
                'body_en'            => $this->bodyEn(),
                'status'             => 'draft',
                'is_featured'        => false,
                'published_at'       => null,
                'seo_title'          => 'Setup ESP32 Arduino IDE & Driver USB — Full Stack IoT #76',
                'seo_title_en'       => 'ESP32 Arduino IDE & USB Driver Setup — Full Stack IoT #76',
                'seo_description'    => 'Install driver CP210x/CH340, Arduino IDE 2.x, paket board ESP32, dan upload sketch pertama. Modul FS-06 Full Stack IoT.',
                'seo_description_en' => 'Install CP210x/CH340 drivers, Arduino IDE 2.x, ESP32 board package, and your first upload. Full Stack IoT module FS-06.',
            ]
        );

        if ($article->published_at !== null || $article->status !== 'draft') {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['fullstack-iot', 'iot', 'esp32'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel #76 / FS-06 tersimpan sebagai DRAFT: '.$article->title);
    }

    private function overviewFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/esp32-devkitc-overview.jpg" width="1200" height="519" alt="Foto overview ESP32-DevKitC — cari chip berlabel (4) USB-to-UART Bridge" loading="eager" style="width:100%;height:auto;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    Cari chip kecil berlabel <strong>(4) USB-to-UART Bridge</strong> di foto — tulisan di chip biasanya <strong>CP2102</strong> atau <strong>CH340</strong>. Itu penentu driver yang kamu unduh.
    <br>Sumber gambar: <a href="https://docs.espressif.com/projects/esp-dev-kits/en/latest/esp32/esp32-devkitc/user_guide.html" rel="noopener noreferrer" target="_blank">Espressif Systems — ESP32-DevKitC User Guide</a> (dokumen resmi).
  </figcaption>
</figure>
HTML;
    }

    private function overviewFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/esp32-devkitc-overview.jpg" width="1200" height="519" alt="ESP32-DevKitC overview — find the chip labeled (4) USB-to-UART Bridge" loading="eager" style="width:100%;height:auto;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    Look for the small chip labeled <strong>(4) USB-to-UART Bridge</strong> in the photo — the chip text is usually <strong>CP2102</strong> or <strong>CH340</strong>. That decides which driver you download.
    <br>Image source: <a href="https://docs.espressif.com/projects/esp-dev-kits/en/latest/esp32/esp32-devkitc/user_guide.html" rel="noopener noreferrer" target="_blank">Espressif Systems — ESP32-DevKitC User Guide</a> (official docs).
  </figcaption>
</figure>
HTML;
    }

    private function deviceManagerFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/fs06-device-manager-esp32.png" width="900" height="700" alt="Windows Device Manager — Ports (COM &amp; LPT) menampilkan Silicon Labs CP210x USB to UART Bridge" loading="lazy" style="width:100%;height:auto;max-height:520px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Windows Device Manager</strong> — buka dengan <code>Win+R</code> → ketik <code>devmgmt.msc</code> → Enter. Cari <strong>Ports (COM &amp; LPT)</strong> → baris seperti <em>Silicon Labs CP210x USB to UART Bridge (COMx)</em> atau <em>USB-SERIAL CH340 (COMx)</em>. Nomor COM bisa beda tiap PC.
    <br>Sumber gambar: <a href="https://docs.espressif.com/projects/esp-idf/en/latest/esp32/get-started/establish-serial-connection.html" rel="noopener noreferrer" target="_blank">Espressif — Establish Serial Connection with ESP32</a> (dokumen resmi).
  </figcaption>
</figure>
HTML;
    }

    private function deviceManagerFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/fs06-device-manager-esp32.png" width="900" height="700" alt="Windows Device Manager — Ports (COM &amp; LPT) showing Silicon Labs CP210x USB to UART Bridge" loading="lazy" style="width:100%;height:auto;max-height:520px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Windows Device Manager</strong> — open with <code>Win+R</code> → type <code>devmgmt.msc</code> → Enter. Find <strong>Ports (COM &amp; LPT)</strong> → a row like <em>Silicon Labs CP210x USB to UART Bridge (COMx)</em> or <em>USB-SERIAL CH340 (COMx)</em>. Your COM number may differ per PC.
    <br>Image source: <a href="https://docs.espressif.com/projects/esp-idf/en/latest/esp32/get-started/establish-serial-connection.html" rel="noopener noreferrer" target="_blank">Espressif — Establish Serial Connection with ESP32</a> (official docs).
  </figcaption>
</figure>
HTML;
    }

    private function workflowSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Alur setup komputer untuk ESP32" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 160" width="100%" height="auto" role="img" aria-label="Alur setup">
  <text x="360" y="24" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700">Alur hari ini — dari nol sampai upload</text>
  <rect x="10" y="45" width="125" height="70" rx="6" fill="#E3F2FD" stroke="#1a1a1a" stroke-width="2"/>
  <text x="72" y="72" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">1 Driver</text>
  <text x="72" y="92" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">CP210x / CH340</text>
  <text x="145" y="82" font-family="system-ui,sans-serif" font-size="18">→</text>
  <rect x="155" y="45" width="125" height="70" rx="6" fill="#E8F5E9" stroke="#1a1a1a" stroke-width="2"/>
  <text x="217" y="72" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">2 IDE</text>
  <text x="217" y="92" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">Arduino 2.x</text>
  <text x="290" y="82" font-family="system-ui,sans-serif" font-size="18">→</text>
  <rect x="300" y="45" width="125" height="70" rx="6" fill="#FFF8E1" stroke="#1a1a1a" stroke-width="2"/>
  <text x="362" y="72" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">3 Board</text>
  <text x="362" y="92" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">paket ESP32</text>
  <text x="435" y="82" font-family="system-ui,sans-serif" font-size="18">→</text>
  <rect x="445" y="45" width="125" height="70" rx="6" fill="#F3E5F5" stroke="#1a1a1a" stroke-width="2"/>
  <text x="507" y="72" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">4 Port</text>
  <text x="507" y="92" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">COM / tty</text>
  <text x="580" y="82" font-family="system-ui,sans-serif" font-size="18">→</text>
  <rect x="590" y="45" width="120" height="70" rx="6" fill="#E8F5E9" stroke="#1a1a1a" stroke-width="2"/>
  <text x="650" y="72" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">5 Upload</text>
  <text x="650" y="92" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#2E7D32">Done uploading ✓</text>
  <text x="360" y="140" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Buatan Koding Indonesia</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Urutan kerja di modul ini (buatan Koding Indonesia). Jangan loncat ke upload sebelum port COM muncul.</figcaption>
</figure>
SVG;
    }

    private function workflowSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Computer setup flow for ESP32" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 160" width="100%" height="auto" role="img" aria-label="Setup flow">
  <text x="360" y="24" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700">Today’s flow — from zero to upload</text>
  <rect x="10" y="45" width="125" height="70" rx="6" fill="#E3F2FD" stroke="#1a1a1a" stroke-width="2"/>
  <text x="72" y="72" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">1 Driver</text>
  <text x="72" y="92" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">CP210x / CH340</text>
  <text x="145" y="82" font-family="system-ui,sans-serif" font-size="18">→</text>
  <rect x="155" y="45" width="125" height="70" rx="6" fill="#E8F5E9" stroke="#1a1a1a" stroke-width="2"/>
  <text x="217" y="72" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">2 IDE</text>
  <text x="217" y="92" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">Arduino 2.x</text>
  <text x="290" y="82" font-family="system-ui,sans-serif" font-size="18">→</text>
  <rect x="300" y="45" width="125" height="70" rx="6" fill="#FFF8E1" stroke="#1a1a1a" stroke-width="2"/>
  <text x="362" y="72" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">3 Board</text>
  <text x="362" y="92" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">ESP32 package</text>
  <text x="435" y="82" font-family="system-ui,sans-serif" font-size="18">→</text>
  <rect x="445" y="45" width="125" height="70" rx="6" fill="#F3E5F5" stroke="#1a1a1a" stroke-width="2"/>
  <text x="507" y="72" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">4 Port</text>
  <text x="507" y="92" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">COM / tty</text>
  <text x="580" y="82" font-family="system-ui,sans-serif" font-size="18">→</text>
  <rect x="590" y="45" width="120" height="70" rx="6" fill="#E8F5E9" stroke="#1a1a1a" stroke-width="2"/>
  <text x="650" y="72" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">5 Upload</text>
  <text x="650" y="92" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#2E7D32">Done uploading ✓</text>
  <text x="360" y="140" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">By Koding Indonesia</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Work order in this module (by Koding Indonesia). Do not jump to upload before the COM port appears.</figcaption>
</figure>
SVG;
    }

    private function chipSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="CP210x vs CH340" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 200" width="100%" height="auto" role="img" aria-label="Chip USB">
  <text x="360" y="28" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700">Dua “terjemahan” USB yang sering ketemu</text>
  <rect x="40" y="50" width="300" height="110" rx="8" fill="#E3F2FD" stroke="#1a1a1a" stroke-width="2"/>
  <text x="190" y="85" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700">CP2102 / CP210x</text>
  <text x="190" y="110" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13">Silicon Labs</text>
  <text x="190" y="132" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#1565C0">Driver: Silicon Labs VCP</text>
  <text x="190" y="150" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Banyak board resmi Espressif</text>
  <rect x="380" y="50" width="300" height="110" rx="8" fill="#FFF3E0" stroke="#1a1a1a" stroke-width="2"/>
  <text x="530" y="85" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700">CH340 / CH340G</text>
  <text x="530" y="110" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13">WCH (Nanjing Qinheng)</text>
  <text x="530" y="132" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#E65100">Driver: WCH CH341SER</text>
  <text x="530" y="150" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Banyak clone murah</text>
  <text x="360" y="185" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Buatan Koding Indonesia — baca tulisan di chip, jangan tebak</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">CP210x vs CH340 (buatan Koding Indonesia). Salah driver = port COM sering tidak muncul.</figcaption>
</figure>
SVG;
    }

    private function chipSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="CP210x vs CH340" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 200" width="100%" height="auto" role="img" aria-label="USB chip">
  <text x="360" y="28" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700">Two common USB “translators”</text>
  <rect x="40" y="50" width="300" height="110" rx="8" fill="#E3F2FD" stroke="#1a1a1a" stroke-width="2"/>
  <text x="190" y="85" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700">CP2102 / CP210x</text>
  <text x="190" y="110" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13">Silicon Labs</text>
  <text x="190" y="132" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#1565C0">Driver: Silicon Labs VCP</text>
  <text x="190" y="150" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Many official Espressif boards</text>
  <rect x="380" y="50" width="300" height="110" rx="8" fill="#FFF3E0" stroke="#1a1a1a" stroke-width="2"/>
  <text x="530" y="85" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700">CH340 / CH340G</text>
  <text x="530" y="110" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13">WCH (Nanjing Qinheng)</text>
  <text x="530" y="132" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#E65100">Driver: WCH CH341SER</text>
  <text x="530" y="150" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Many budget clones</text>
  <text x="360" y="185" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">By Koding Indonesia — read the chip text, do not guess</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">CP210x vs CH340 (by Koding Indonesia). Wrong driver = COM port often missing.</figcaption>
</figure>
SVG;
    }

    private function boardMenuFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/fs06-arduino-ide-overview.png" width="1920" height="1080" alt="Arduino IDE 2 — toolbar Select Board &amp; Port di bagian atas" loading="lazy" style="width:100%;height:auto;max-height:520px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Pilih board + port COM:</strong> di toolbar atas Arduino IDE 2, klik dropdown <strong>Select Board &amp; Port</strong>. Pilih <strong>ESP32 Dev Module</strong> (sesuai DevKitC-1) dan port COM/tty yang sama dengan Device Manager. Board dan port harus terpilih sebelum upload.
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:Ide-2-overview.png" rel="noopener noreferrer" target="_blank">Arduino IDE 2 overview</a> · Wikimedia Commons (CC BY-SA 3.0).
  </figcaption>
</figure>
HTML;
    }

    private function boardMenuFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/fs06-arduino-ide-overview.png" width="1920" height="1080" alt="Arduino IDE 2 — Select Board &amp; Port toolbar at the top" loading="lazy" style="width:100%;height:auto;max-height:520px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Pick board + COM port:</strong> in the Arduino IDE 2 top toolbar, click the <strong>Select Board &amp; Port</strong> dropdown. Choose <strong>ESP32 Dev Module</strong> (matches DevKitC-1) and the COM/tty port that matches Device Manager. Select both before upload.
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:Ide-2-overview.png" rel="noopener noreferrer" target="_blank">Arduino IDE 2 overview</a> · Wikimedia Commons (CC BY-SA 3.0).
  </figcaption>
</figure>
HTML;
    }

    private function body(): string
    {
        $overview = $this->overviewFigureId();
        $flow = $this->workflowSvgId();
        $chip = $this->chipSvgId();
        $devmgr = $this->deviceManagerFigureId();
        $menu = $this->boardMenuFigureId();

        return <<<HTML
<h2>Pendahuluan — kenapa komputer dulu?</h2>
<p>Artikel ini adalah <strong>#76 (ini)</strong> · modul <strong>FS-06</strong> di jalur <em>Full Stack IoT Developer — Dari Nol</em>. Di <strong>FS-05</strong> kamu sudah punya kebiasaan aman. Hari ini komputer dan <strong>Arduino IDE</strong> disiapkan supaya board ESP32-DevKitC-1 bisa dikenali dan menerima program pertama.</p>
<p><strong>Analogi:</strong> seperti mendaftar SIM sebelum menyetir — tanpa SIM (driver + IDE), mobil (board) tidak “sah” di jalan komputermu.</p>
<p><strong>Prasyarat:</strong> ide dari FS-05 (cabut USB dulu, kabel data, jangan 5V ke GPIO). Kamu perlu <strong>PC/laptop</strong>, board, dan kabel USB <strong>data</strong>.</p>

<h2>Persiapan — alat yang kamu buka hari ini</h2>
<p><strong>Alat yang dipakai di artikel ini</strong> (belum Laragon, belum terminal web, belum <code>php artisan</code>, belum multimeter dipakai — itu modul lain):</p>
<ul>
<li><strong>Browser</strong> — unduh driver USB &amp; Arduino IDE 2.x dari situs resmi.</li>
<li><strong>Windows:</strong> <strong>Device Manager</strong> (<code>devmgmt.msc</code>) — cek apakah port COM muncul. <strong>macOS:</strong> System Information → USB. <strong>Linux:</strong> <code>dmesg</code> atau <code>ls /dev/ttyUSB*</code> (baca saja dulu kalau belum terbiasa).</li>
<li><strong>Arduino IDE 2.x</strong> — aplikasi utama untuk upload sketch. Unduh dari <a href="https://www.arduino.cc/en/software" rel="noopener noreferrer" target="_blank">arduino.cc/software</a>, lalu instal seperti program biasa.</li>
<li><strong>ESP32-DevKitC-1 + kabel USB data</strong> — untuk tes colok setelah driver terpasang.</li>
<li><strong>Kertas + pena</strong> — catat nomor port COM kamu (misalnya COM5).</li>
</ul>
<p><strong>Cara pakai artikel ini (urutan kerja):</strong></p>
<ol>
<li><strong>Buka browser</strong> — baca artikel + unduh driver &amp; Arduino IDE dari situs resmi.</li>
<li><strong>Pasang driver</strong> sesuai chip (CP210x / CH340) → restart PC jika diminta.</li>
<li><strong>Buka Arduino IDE 2.x</strong> — di sini kamu akan menempel &amp; menguji sketch.</li>
<li><strong>Buka Device Manager</strong> (<code>Win+R</code> → <code>devmgmt.msc</code>) untuk cek port COM.</li>
<li><strong>Baru colok board USB data</strong> → pilih board + port → Upload → cari <em>Done uploading</em>.</li>
</ol>
<p><strong>Tidak perlu hari ini:</strong> Laragon, terminal web, <code>php artisan</code>, multimeter, wiring breadboard. Sintaks yang diuji = sketch di Arduino IDE (bukan perintah PHP).</p>
<p><strong>Hasil yang kamu cari hari ini:</strong> di Arduino IDE muncul tulisan <strong>Done uploading</strong> tanpa error port.</p>

{$flow}

<h2>Ingat dulu — checklist FS-05</h2>
<p>Sebelum colok USB untuk latihan: <strong>cabut dulu</strong> kalau akan mengubah kabel, pakai kabel <strong>data</strong>, meja kering, dan tidak hot-plug. Kalau belum yakin, ulang checklist keselamatan di FS-05.</p>

<h2>Kenali chip USB di board kamu</h2>
<p>Di dekat port micro-USB ada chip kecil “jembatan” USB. Baca tulisan di permukaannya — jangan tebak dari harga board.</p>
{$overview}
{$chip}
<ul>
<li><strong>CP2102 / CP210x</strong> → driver <a href="https://www.silabs.com/developers/usb-to-uart-bridge-vcp-drivers" rel="noopener noreferrer" target="_blank">Silicon Labs CP210x VCP</a> (pilih Windows / macOS / Linux).</li>
<li><strong>CH340 / CH340G</strong> → driver <a href="https://www.wch-ic.com/downloads/CH341SER_EXE.html" rel="noopener noreferrer" target="_blank">WCH CH341SER</a> (Windows) atau paket distro Linux (<code>brltty</code> kadang bentrok — lihat kesalahan umum).</li>
</ul>
<p><strong>Cara menguji driver:</strong> pasang driver → restart PC kalau diminta → colok board → buka Device Manager → lihat <strong>Ports (COM &amp; LPT)</strong>. Kalau muncul <em>Silicon Labs CP210x USB to UART Bridge (COMx)</em> atau <em>USB-SERIAL CH340 (COMx)</em>, driver OK.</p>
{$devmgr}

<h2>Install Arduino IDE 2.x</h2>
<ol>
<li>Buka <a href="https://www.arduino.cc/en/software" rel="noopener noreferrer" target="_blank">arduino.cc/en/software</a> di browser.</li>
<li>Unduh <strong>Arduino IDE 2.x</strong> untuk OS kamu (Windows 64-bit paling umum).</li>
<li>Jalankan installer → next → finish (default biasanya cukup).</li>
<li>Buka Arduino IDE — kamu akan lihat editor kosong + tombol Verify (✓) dan Upload (→).</li>
</ol>
<p><strong>Analogi:</strong> IDE = “Word” untuk sketch Arduino. Sketch = file resep yang nanti di-upload ke board.</p>
<p><strong>macOS/Linux:</strong> langkah unduh sama; pastikan user kamu punya izin akses port serial (macOS biasanya langsung; Linux kadang perlu grup <code>dialout</code>).</p>

<h2>Tambah paket board ESP32 (Board Manager)</h2>
<p>Arduino IDE belum mengenali ESP32 sampai kamu menambah URL resmi Espressif:</p>
<ol>
<li>Arduino IDE → <strong>File → Preferences</strong> (macOS: <strong>Arduino IDE → Settings</strong>).</li>
<li>Di <strong>Additional boards manager URLs</strong>, tempel URL ini (satu baris):</li>
</ol>
<pre><code class="language-text">https://espressif.github.io/arduino-esp32/package_esp32_index.json</code></pre>
<ol start="3">
<li>OK → buka <strong>Tools → Board → Boards Manager…</strong></li>
<li>Cari <strong>esp32</strong> oleh Espressif Systems → <strong>Install</strong> (unduhan bisa beberapa menit).</li>
<li>Tunggu sampai status <em>installed</em>.</li>
</ol>
<p><strong>Cara menguji Board Manager:</strong> setelah install, menu <strong>Tools → Board</strong> harus punya submenu <strong>ESP32 Arduino</strong>. Kalau tidak ada, cek URL typo atau internet/firewall.</p>

<h2>Pilih board + port COM</h2>
{$menu}
<ol>
<li><strong>Tools → Board → ESP32 Arduino → ESP32 Dev Module</strong> (cocok untuk DevKitC-1 di jalur ini).</li>
<li>Colok board dengan kabel <strong>data</strong>.</li>
<li><strong>Tools → Port</strong> → pilih COM yang baru muncul (Windows) atau <code>/dev/cu.usbserial-*</code> (macOS).</li>
<li>Catat nomor port di kertas — nanti sering dipakai.</li>
</ol>
<p><strong>Intinya:</strong> Port = “pintu” ke board. Salah pintu → upload gagal meski sketch benar.</p>

<h2>Upload pertama — sketch kilat LED</h2>
<p>Sketch paling sederhana: kedipkan LED built-in (GPIO 2 di banyak DevKit). <strong>Buka Arduino IDE</strong> dulu, lalu:</p>
<ol>
<li><strong>File → New Sketch</strong> (atau Ctrl+N).</li>
<li>Hapus isi default, tempel kode ini:</li>
</ol>
<pre><code class="language-cpp">#define LED_BUILTIN 2

void setup() {
  pinMode(LED_BUILTIN, OUTPUT);
}

void loop() {
  digitalWrite(LED_BUILTIN, HIGH);
  delay(1000);
  digitalWrite(LED_BUILTIN, LOW);
  delay(1000);
}</code></pre>
<ol start="3">
<li><strong>Sketch → Save</strong> — beri nama misalnya <code>fs06-blink</code>.</li>
<li>Klik tombol <strong>Upload</strong> (panah →) di toolbar.</li>
<li>Tunggu bar bawah: target <strong>Done uploading</strong>. LED kecil di board biasanya berkedip 1 detik on / 1 detik off.</li>
</ol>
<p><strong>Cara menguji upload:</strong> kalau ada <em>Done uploading</em> dan LED berkedip, FS-06 lulus. Kalau error, jangan panik — baca pesan merah di bawah, lalu cek bagian kesalahan umum.</p>
<p><strong>Belum wiring breadboard hari ini</strong> — LED built-in di board sudah cukup sebagai bukti upload.</p>

<h2 id="fsiot-setup-checklist">Praktik — checklist setup 10 poin</h2>
<p>Centang tiap langkah yang sudah kamu selesaikan. Target: <strong>10/10</strong>. Ada <strong>checklist interaktif</strong> di bawah; versi kertas tetap tersedia.</p>
<ul id="fsiot-setup-checklist-items">
<li>Saya ingat kebiasaan FS-05: cabut USB dulu sebelum ubah kabel</li>
<li>Saya memakai kabel USB data (bukan charge-only)</li>
<li>Saya tahu chip USB board saya (CP210x atau CH340) dan driver sudah terpasang</li>
<li>Port COM / tty muncul di Device Manager (atau setara) saat board dicolok</li>
<li>Arduino IDE 2.x sudah terinstall dan bisa dibuka</li>
<li>URL Board Manager Espressif sudah ditambahkan di Preferences</li>
<li>Paket board <strong>esp32</strong> sudah terinstall di Boards Manager</li>
<li>Saya memilih board <strong>ESP32 Dev Module</strong></li>
<li>Saya memilih port COM/tty yang benar dan sudah mencatat nomornya</li>
<li>Upload sketch contoh berhasil — ada tulisan <strong>Done uploading</strong></li>
</ul>
<p><strong>Cara menguji checklist:</strong> kerjakan checklist di browser. Opsional: foto layar Device Manager + Arduino IDE dengan port terpilih. Tidak perlu menjalankan perintah web server apa pun.</p>

<h2>Kesalahan yang sering terjadi</h2>
<ul>
<li><strong>Port tidak muncul.</strong> Curigai kabel charge-only dulu, lalu driver salah (CP210x vs CH340), lalu colok ke port USB belakang PC.</li>
<li><strong>Salah board.</strong> Pilih <em>ESP32 Dev Module</em>, bukan Arduino Uno atau ESP8266.</li>
<li><strong>Upload gagal “Failed to connect”.</strong> Tekan tombol <strong>BOOT</strong> di board saat upload dimulai (beberapa board butuh), atau ganti kabel/port USB.</li>
<li><strong>Board Manager kosong.</strong> URL Espressif typo atau firewall memblok unduhan.</li>
<li><strong>Linux: permission denied pada /dev/ttyUSB0.</strong> Tambahkan user ke grup <code>dialout</code>, logout-login.</li>
<li><strong>macOS: driver tidak terbuka.</strong> Buka System Settings → Privacy &amp; Security → izinkan driver Silicon Labs/WCH.</li>
<li><strong>Langsung upload tanpa pilih Port.</strong> IDE tidak tahu board mana yang dituju.</li>
</ul>

<h2>Lanjut belajar</h2>
<p>Setelah FS-06, langkah alami berikutnya adalah <strong>FS-07 — multimeter dasar</strong> (mengukur 3.3V dengan aman). Artikel itu belum dilink di sini sampai modulnya siap.</p>
<p>Simpan juga <a href="/belajar/fullstack-iot">halaman jalur Full Stack IoT</a> sebagai pintu masuk resmi.</p>

<h2>Kesimpulan</h2>
<p>Di <strong>#76 (ini)</strong> komputermu sudah mengenali ESP32: driver USB, Arduino IDE 2.x, paket board ESP32, board + port dipilih, dan <strong>Done uploading</strong> sekali. Kamu siap masuk dunia sketch — masih belum rangkaian breadboard penuh.</p>
<p><strong>Intinya:</strong> kalau kamu bisa tunjukkan port COM dan bilang “upload sudah Done”, FS-06 selesai. Lanjut ukur tegangan di FS-07 saat modulnya terbit.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $overview = $this->overviewFigureEn();
        $flow = $this->workflowSvgEn();
        $chip = $this->chipSvgEn();
        $devmgr = $this->deviceManagerFigureEn();
        $menu = $this->boardMenuFigureEn();

        return <<<HTML
<h2>Introduction — why the computer first?</h2>
<p>This article is <strong>#76 (this article)</strong> · module <strong>FS-06</strong> on the <em>Full Stack IoT Developer — From Zero</em> path. In <strong>FS-05</strong> you learned safe habits. Today we prepare the computer and <strong>Arduino IDE</strong> so your ESP32-DevKitC-1 is recognized and accepts its first program.</p>
<p><strong>Analogy:</strong> like getting a license before driving — without the license (driver + IDE), the car (board) is not “legal” on your computer’s roads.</p>
<p><strong>Prerequisites:</strong> ideas from FS-05 (unplug USB first, data cable, no 5V into GPIO). You need a <strong>PC/laptop</strong>, the board, and a USB <strong>data</strong> cable.</p>

<h2>Preparation — tools you open today</h2>
<p><strong>Tools used in this article</strong> (no Laragon, no web terminal, no <code>php artisan</code>, no multimeter practice yet — those are other modules):</p>
<ul>
<li><strong>Browser</strong> — download the USB driver &amp; Arduino IDE 2.x from official sites.</li>
<li><strong>Windows:</strong> <strong>Device Manager</strong> (<code>devmgmt.msc</code>) — check whether a COM port appears. <strong>macOS:</strong> System Information → USB. <strong>Linux:</strong> <code>dmesg</code> or <code>ls /dev/ttyUSB*</code> (read-only is fine if you are new).</li>
<li><strong>Arduino IDE 2.x</strong> — main app for uploading sketches. Download from <a href="https://www.arduino.cc/en/software" rel="noopener noreferrer" target="_blank">arduino.cc/software</a>, then install like any normal program.</li>
<li><strong>ESP32-DevKitC-1 + USB data cable</strong> — for plug-in testing after the driver is installed.</li>
<li><strong>Paper + pen</strong> — write down your COM port number (e.g. COM5).</li>
</ul>
<p><strong>How to use this article (work order):</strong></p>
<ol>
<li><strong>Open a browser</strong> — read the article + download the driver &amp; Arduino IDE from official sites.</li>
<li><strong>Install the driver</strong> for your chip (CP210x / CH340) → restart the PC if asked.</li>
<li><strong>Open Arduino IDE 2.x</strong> — this is where you paste and test the sketch.</li>
<li><strong>Open Device Manager</strong> (<code>Win+R</code> → <code>devmgmt.msc</code>) to check the COM port.</li>
<li><strong>Only then plug the board with a data USB cable</strong> → pick board + port → Upload → look for <em>Done uploading</em>.</li>
</ol>
<p><strong>Not needed today:</strong> Laragon, a web terminal, <code>php artisan</code>, a multimeter, or breadboard wiring. The syntax you test is the sketch in Arduino IDE (not PHP commands).</p>
<p><strong>What you want today:</strong> Arduino IDE shows <strong>Done uploading</strong> with no port error.</p>

{$flow}

<h2>Remember first — FS-05 checklist</h2>
<p>Before plugging USB for practice: <strong>unplug first</strong> when changing wires, use a <strong>data</strong> cable, dry desk, no hot-plug. If unsure, redo the FS-05 safety checklist.</p>

<h2>Spot the USB chip on your board</h2>
<p>Near the micro-USB port there is a small USB “bridge” chip. Read the text on it — do not guess from board price.</p>
{$overview}
{$chip}
<ul>
<li><strong>CP2102 / CP210x</strong> → <a href="https://www.silabs.com/developers/usb-to-uart-bridge-vcp-drivers" rel="noopener noreferrer" target="_blank">Silicon Labs CP210x VCP</a> driver (pick Windows / macOS / Linux).</li>
<li><strong>CH340 / CH340G</strong> → <a href="https://www.wch-ic.com/downloads/CH341SER_EXE.html" rel="noopener noreferrer" target="_blank">WCH CH341SER</a> driver (Windows) or your Linux distro package (<code>brltty</code> sometimes conflicts — see common mistakes).</li>
</ul>
<p><strong>How to test the driver:</strong> install driver → restart PC if asked → plug board → open Device Manager → look under <strong>Ports (COM &amp; LPT)</strong>. If you see <em>Silicon Labs CP210x USB to UART Bridge (COMx)</em> or <em>USB-SERIAL CH340 (COMx)</em>, the driver is OK.</p>
{$devmgr}

<h2>Install Arduino IDE 2.x</h2>
<ol>
<li>Open <a href="https://www.arduino.cc/en/software" rel="noopener noreferrer" target="_blank">arduino.cc/en/software</a> in the browser.</li>
<li>Download <strong>Arduino IDE 2.x</strong> for your OS (Windows 64-bit is most common).</li>
<li>Run the installer → next → finish (defaults are usually fine).</li>
<li>Open Arduino IDE — you will see an empty editor plus Verify (✓) and Upload (→) buttons.</li>
</ol>
<p><strong>Analogy:</strong> the IDE is like “Word” for Arduino sketches. A sketch is the recipe file you upload to the board.</p>
<p><strong>macOS/Linux:</strong> same download steps; make sure your user can access the serial port (macOS usually works out of the box; Linux may need the <code>dialout</code> group).</p>

<h2>Add the ESP32 board package (Board Manager)</h2>
<p>Arduino IDE does not know ESP32 until you add Espressif’s official URL:</p>
<ol>
<li>Arduino IDE → <strong>File → Preferences</strong> (macOS: <strong>Arduino IDE → Settings</strong>).</li>
<li>In <strong>Additional boards manager URLs</strong>, paste this (one line):</li>
</ol>
<pre><code class="language-text">https://espressif.github.io/arduino-esp32/package_esp32_index.json</code></pre>
<ol start="3">
<li>OK → open <strong>Tools → Board → Boards Manager…</strong></li>
<li>Search <strong>esp32</strong> by Espressif Systems → <strong>Install</strong> (download may take several minutes).</li>
<li>Wait until status shows <em>installed</em>.</li>
</ol>
<p><strong>How to test Board Manager:</strong> after install, <strong>Tools → Board</strong> must show an <strong>ESP32 Arduino</strong> submenu. If not, check URL typos or internet/firewall.</p>

<h2>Pick board + COM port</h2>
{$menu}
<ol>
<li><strong>Tools → Board → ESP32 Arduino → ESP32 Dev Module</strong> (fits DevKitC-1 on this path).</li>
<li>Plug the board with a <strong>data</strong> cable.</li>
<li><strong>Tools → Port</strong> → pick the new COM port (Windows) or <code>/dev/cu.usbserial-*</code> (macOS).</li>
<li>Write the port number on paper — you will use it often.</li>
</ol>
<p><strong>In short:</strong> the port is the “door” to your board. Wrong door → upload fails even if the sketch is correct.</p>

<h2>First upload — blink the built-in LED</h2>
<p>Simplest sketch: blink the built-in LED (GPIO 2 on many DevKits). <strong>Open Arduino IDE</strong> first, then:</p>
<ol>
<li><strong>File → New Sketch</strong> (or Ctrl+N).</li>
<li>Clear the default content and paste this code:</li>
</ol>
<pre><code class="language-cpp">#define LED_BUILTIN 2

void setup() {
  pinMode(LED_BUILTIN, OUTPUT);
}

void loop() {
  digitalWrite(LED_BUILTIN, HIGH);
  delay(1000);
  digitalWrite(LED_BUILTIN, LOW);
  delay(1000);
}</code></pre>
<ol start="3">
<li><strong>Sketch → Save</strong> — name it e.g. <code>fs06-blink</code>.</li>
<li>Click the <strong>Upload</strong> button (arrow →) in the toolbar.</li>
<li>Watch the bottom bar: target <strong>Done uploading</strong>. The small LED on the board should blink 1s on / 1s off.</li>
</ol>
<p><strong>How to test the upload:</strong> if you see <em>Done uploading</em> and the LED blinks, FS-06 is passed. If there is an error, read the red message below and check common mistakes.</p>
<p><strong>No breadboard wiring today</strong> — the on-board LED is enough proof of upload.</p>

<h2 id="fsiot-setup-checklist">Practice — 10-point setup checklist</h2>
<p>Tick each step you finished. Target: <strong>10/10</strong>. An <strong>interactive checklist</strong> is below; a paper version stays available.</p>
<ul id="fsiot-setup-checklist-items">
<li>I remember FS-05: unplug USB before changing wires</li>
<li>I use a USB data cable (not charge-only)</li>
<li>I know my board’s USB chip (CP210x or CH340) and the driver is installed</li>
<li>A COM / tty port appears in Device Manager (or equivalent) when the board is plugged in</li>
<li>Arduino IDE 2.x is installed and opens</li>
<li>The Espressif Board Manager URL is added in Preferences</li>
<li>The <strong>esp32</strong> board package is installed in Boards Manager</li>
<li>I selected <strong>ESP32 Dev Module</strong> as the board</li>
<li>I picked the correct COM/tty port and wrote the number down</li>
<li>My test sketch upload succeeded — <strong>Done uploading</strong> appeared</li>
</ul>
<p><strong>How to test the checklist:</strong> complete the checklist in the browser. Optional: photo of Device Manager + Arduino IDE with the port selected. No web server commands required.</p>

<h2>Common mistakes</h2>
<ul>
<li><strong>Port does not appear.</strong> Suspect charge-only cable first, then wrong driver (CP210x vs CH340), then try a rear USB port on the PC.</li>
<li><strong>Wrong board.</strong> Pick <em>ESP32 Dev Module</em>, not Arduino Uno or ESP8266.</li>
<li><strong>Upload fails “Failed to connect”.</strong> Hold the <strong>BOOT</strong> button when upload starts (some boards need it), or swap cable/USB port.</li>
<li><strong>Board Manager empty.</strong> Espressif URL typo or firewall blocking downloads.</li>
<li><strong>Linux: permission denied on /dev/ttyUSB0.</strong> Add user to <code>dialout</code> group, logout-login.</li>
<li><strong>macOS: driver blocked.</strong> Open System Settings → Privacy &amp; Security → allow Silicon Labs/WCH driver.</li>
<li><strong>Upload without picking Port.</strong> The IDE does not know which board to target.</li>
</ul>

<h2>Continue learning</h2>
<p>After FS-06, the natural next step is <strong>FS-07 — basic multimeter</strong> (measuring 3.3V safely). That article is not linked here until the module is ready.</p>
<p>Also bookmark the <a href="/belajar/fullstack-iot">Full Stack IoT path page</a> as the official entry.</p>

<h2>Conclusion</h2>
<p>In <strong>#76 (this article)</strong> your computer recognizes the ESP32: USB driver, Arduino IDE 2.x, ESP32 board package, board + port selected, and <strong>Done uploading</strong> once. You are ready for sketch world — still no full breadboard circuit.</p>
<p><strong>In short:</strong> if you can point at your COM port and say “upload is Done”, FS-06 is done. Continue to measuring voltage in FS-07 when that module publishes.</p>
HTML;
    }
}
