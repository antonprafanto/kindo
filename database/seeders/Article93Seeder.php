<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article93Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first()
            ?? Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $iotCat) {
            throw new \RuntimeException('User atau kategori iot-smart-device/esp32-arduino tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-relay-aman-beban-kecil';

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
                'title'              => 'Relay aman: saklar listrik untuk beban kecil',
                'title_en'           => 'Safe relay: an electrical switch for small loads',
                'excerpt'            => 'FS-23 / #93: nyalakan modul relay dari GPIO 26. Uji di Arduino IDE: digitalWrite → klik + LED indikator. Beban DC kecil saja — belum AC 220V.',
                'excerpt_en'         => 'FS-23 / #93: drive a relay module from GPIO 26. Test in Arduino IDE: digitalWrite → click + indicator LED. Small DC loads only — not AC mains yet.',
                'body'               => $this->body(),
                'body_en'            => $this->bodyEn(),
                'status'             => 'draft',
                'is_featured'        => false,
                'published_at'       => null,
                'seo_title'          => 'Relay aman beban kecil — Full Stack IoT #93',
                'seo_title_en'       => 'Safe relay for small loads — Full Stack IoT #93',
                'seo_description'    => 'Belajar modul relay 5V, GPIO 26, aktif HIGH/LOW, dan batas aman beban DC. Modul FS-23 jalur Full Stack IoT.',
                'seo_description_en' => 'Learn a 5V relay module, GPIO 26, active HIGH/LOW, and safe DC load limits. Full Stack IoT FS-23 module.',
            ]
        );

        if ($article->published_at !== null || $article->status !== 'draft') {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['fullstack-iot', 'iot', 'esp32'])->pluck('id');
        $article->tags()->sync($tagIds);

        if (blank($article->cover_image)) {
            $src = public_path('images/fsiot/fs23-cover-relay.jpg');
            if (is_file($src)) {
                $dest = 'articles/covers/fs23-cover-relay.jpg';
                \Illuminate\Support\Facades\Storage::disk('public')->put($dest, file_get_contents($src));
                $article->cover_image = $dest;
                $article->save();
            }
        }

        $this->command?->info('✓ Artikel #93 / FS-23 tersimpan sebagai DRAFT: '.$article->title);
    }

    private function ideFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem">
  <img src="/images/fsiot/fs11-ide-overview-cite.png" width="1280" height="720" alt="Arduino IDE 2 — Verify, Upload, dan Serial Monitor" loading="eager" style="width:100%;height:auto;max-height:420px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Arduino IDE 2</strong> — tempat menguji sintaks hari ini. Tidak perlu Library Manager baru: cukup <strong>Verify</strong> → <strong>Upload</strong> → buka <strong>Serial Monitor</strong> (baud 115200). Board: <strong>ESP32 Dev Module</strong>.
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:Ide-2-overview.png" rel="noopener noreferrer" target="_blank">Arduino IDE 2 overview</a> · Wikimedia Commons (CC BY-SA 3.0). Panduan Serial: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-serial-monitor/" rel="noopener noreferrer" target="_blank">Arduino Docs — Serial Monitor</a>. Fungsi <code>digitalWrite</code>: <a href="https://docs.arduino.cc/language-reference/en/functions/digital-io/digitalwrite/" rel="noopener noreferrer" target="_blank">Arduino Docs — digitalWrite</a>.
  </figcaption>
</figure>
HTML;
    }

    private function ideFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem">
  <img src="/images/fsiot/fs11-ide-overview-cite.png" width="1280" height="720" alt="Arduino IDE 2 — Verify, Upload, and Serial Monitor" loading="eager" style="width:100%;height:auto;max-height:420px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Arduino IDE 2</strong> — where today’s syntax is tested. No new Library Manager install: just <strong>Verify</strong> → <strong>Upload</strong> → open <strong>Serial Monitor</strong> (baud 115200). Board: <strong>ESP32 Dev Module</strong>.
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:Ide-2-overview.png" rel="noopener noreferrer" target="_blank">Arduino IDE 2 overview</a> · Wikimedia Commons (CC BY-SA 3.0). Serial guide: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-serial-monitor/" rel="noopener noreferrer" target="_blank">Arduino Docs — Serial Monitor</a>. <code>digitalWrite</code>: <a href="https://docs.arduino.cc/language-reference/en/functions/digital-io/digitalwrite/" rel="noopener noreferrer" target="_blank">Arduino Docs — digitalWrite</a>.
  </figcaption>
</figure>
HTML;
    }

    private function boardFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem">
  <img src="/images/fsiot/esp32-devkitc-overview.jpg" width="1200" height="800" alt="ESP32-DevKitC — USB (6) dan EN (7)" loading="eager" style="width:100%;height:auto;max-height:360px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>ESP32-DevKitC</strong> — USB data di <strong>(6)</strong>, reset di <strong>EN (7)</strong>. Pin relay latihan FSIOT = <strong>GPIO 26</strong> (tabel global FS-17). Cari juga pin <strong>5V</strong> dan <strong>GND</strong>.
    <br>Sumber gambar: <a href="https://docs.espressif.com/projects/esp-dev-kits/en/latest/esp32/esp32-devkitc/user_guide.html" rel="noopener noreferrer" target="_blank">Espressif — ESP32-DevKitC user guide</a>. Pin board: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/boards/ESP32-DevKitC-1.html" rel="noopener noreferrer" target="_blank">ESP32-DevKitC-1</a>.
  </figcaption>
</figure>
HTML;
    }

    private function boardFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem">
  <img src="/images/fsiot/esp32-devkitc-overview.jpg" width="1200" height="800" alt="ESP32-DevKitC — USB (6) and EN (7)" loading="eager" style="width:100%;height:auto;max-height:360px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>ESP32-DevKitC</strong> — USB data at <strong>(6)</strong>, reset on <strong>EN (7)</strong>. FSIOT practice relay pin = <strong>GPIO 26</strong> (FS-17 global table). Also find <strong>5V</strong> and <strong>GND</strong>.
    <br>Image source: <a href="https://docs.espressif.com/projects/esp-dev-kits/en/latest/esp32/esp32-devkitc/user_guide.html" rel="noopener noreferrer" target="_blank">Espressif — ESP32-DevKitC user guide</a>. Board pins: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/boards/ESP32-DevKitC-1.html" rel="noopener noreferrer" target="_blank">ESP32-DevKitC-1</a>.
  </figcaption>
</figure>
HTML;
    }

    private function kitFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem">
  <img src="/images/fsiot/kit-relay-5v.jpg" width="900" height="600" alt="Modul relay 1 channel 5V dengan terminal sekrup" loading="eager" style="display:block;width:100%;max-width:520px;height:auto;max-height:320px;object-fit:contain;margin:0 auto;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Komponen hari ini:</strong> modul <strong>relay 1 channel 5V</strong> (kotak biru + 3 pin VCC/GND/IN + terminal sekrup). Di jalur Core: beban <strong>DC kecil</strong> saja — bukan AC 220V.
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:SRD-05VDC-SL-C_5V_one-channel_relay_module.jpg" rel="noopener noreferrer" target="_blank">SRD-05VDC-SL-C 5V one-channel relay module</a> · Wikimedia Commons (CC BY-SA 4.0) · Suyash Dwivedi.
  </figcaption>
</figure>
HTML;
    }

    private function kitFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem">
  <img src="/images/fsiot/kit-relay-5v.jpg" width="900" height="600" alt="5V one-channel relay module with screw terminals" loading="eager" style="display:block;width:100%;max-width:520px;height:auto;max-height:320px;object-fit:contain;margin:0 auto;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Parts today:</strong> a <strong>1-channel 5V relay module</strong> (blue cube + VCC/GND/IN pins + screw terminals). On the Core path: <strong>small DC</strong> loads only — not AC mains.
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:SRD-05VDC-SL-C_5V_one-channel_relay_module.jpg" rel="noopener noreferrer" target="_blank">SRD-05VDC-SL-C 5V one-channel relay module</a> · Wikimedia Commons (CC BY-SA 4.0) · Suyash Dwivedi.
  </figcaption>
</figure>
HTML;
    }

    private function mainWiringFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs23-relay-breadboard.png" width="1236" height="756" alt="Gambar utama — rangkaian relay di breadboard ke GPIO 26 (IO26)" loading="eager" style="width:100%;height:auto;max-height:560px;object-fit:contain;border-radius:6px;background:#fff">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Gambar utama — rangkaian relay di breadboard.</strong> Merah: <strong>5V</strong> → pin <strong>+</strong> (VCC) modul · biru: <strong>IO26 / GPIO 26</strong> → pin <strong>S</strong> (IN) · hitam: <strong>GND</strong> → pin <strong>−</strong>. Terminal sekrup NC/COM/NO hari ini boleh kosong — yang penting klik + LED <em>ON</em>. Cocokkan label di modul &amp; silkscreen board kamu (clone boleh beda bentuk).
    <br>Sumber gambar: diagram rangkaian buatan Koding Indonesia (FS-23).
  </figcaption>
</figure>
HTML;
    }

    private function mainWiringFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs23-relay-breadboard.png" width="1236" height="756" alt="Main figure — relay breadboard wiring to GPIO 26 (IO26)" loading="eager" style="width:100%;height:auto;max-height:560px;object-fit:contain;border-radius:6px;background:#fff">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Main figure — relay on a breadboard.</strong> Red: <strong>5V</strong> → module <strong>+</strong> (VCC) · blue: <strong>IO26 / GPIO 26</strong> → <strong>S</strong> (IN) · black: <strong>GND</strong> → <strong>−</strong>. NC/COM/NO screw terminals may stay empty today — the click + <em>ON</em> LED matter most. Match labels on your module and board silkscreen (clones may look different).
    <br>Image source: wiring diagram by Koding Indonesia (FS-23).
  </figcaption>
</figure>
HTML;
    }

    private function schemaWiringFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs23-relay-wiring.png" width="1100" height="780" alt="Skema bantu — ringkasan pin relay ke ESP32 GPIO 26" loading="eager" style="width:100%;height:auto;max-height:520px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Skema bantu (ringkas).</strong> Sama dengan gambar utama: 5V → VCC/+ · GPIO 26 → IN/S · GND → GND/−. Pakai ini jika kamu lebih nyaman membaca kotak pin daripada foto breadboard. Terminal beban: belum AC 220V.
    <br>Sumber gambar: diagram berlabel buatan Koding Indonesia (FS-23). Fungsi keluaran digital: <a href="https://docs.arduino.cc/language-reference/en/functions/digital-io/digitalwrite/" rel="noopener noreferrer" target="_blank">Arduino Docs — digitalWrite</a>.
  </figcaption>
</figure>
HTML;
    }

    private function schemaWiringFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs23-relay-wiring.png" width="1100" height="780" alt="Helper schematic — relay pins to ESP32 GPIO 26" loading="eager" style="width:100%;height:auto;max-height:520px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Helper schematic.</strong> Same as the main figure: 5V → VCC/+ · GPIO 26 → IN/S · GND → GND/−. Use this if you prefer labeled pin boxes over the breadboard photo. Load terminals: no AC mains yet.
    <br>Image source: labeled diagram by Koding Indonesia (FS-23). Digital output: <a href="https://docs.arduino.cc/language-reference/en/functions/digital-io/digitalwrite/" rel="noopener noreferrer" target="_blank">Arduino Docs — digitalWrite</a>.
  </figcaption>
</figure>
HTML;
    }

    private function senseSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Alur: kode ke GPIO ke relay ke klik saklar" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 160" width="100%" height="auto" style="display:block;max-height:190px">
    <text x="430" y="28" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#1a1a1a">Alur sederhana: kode → pin → klik saklar</text>
    <rect x="30" y="55" width="150" height="70" rx="10" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
    <text x="105" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" font-weight="700" fill="#0D47A1">digitalWrite</text>
    <text x="200" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="22" fill="#1a1a1a">→</text>
    <rect x="230" y="55" width="150" height="70" rx="10" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
    <text x="305" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" font-weight="700" fill="#1B5E20">GPIO 26</text>
    <text x="400" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="22" fill="#1a1a1a">→</text>
    <rect x="430" y="55" width="150" height="70" rx="10" fill="#FFF3E0" stroke="#EF6C00" stroke-width="2.5"/>
    <text x="505" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" font-weight="700" fill="#E65100">Modul relay</text>
    <text x="600" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="22" fill="#1a1a1a">→</text>
    <rect x="630" y="55" width="200" height="70" rx="10" fill="#FFEBEE" stroke="#C62828" stroke-width="2.5"/>
    <text x="730" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" font-weight="700" fill="#B71C1C">Klik + LED indikator</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.35rem;color:#4A5568;">
    <strong>Intinya:</strong> ESP32 tidak “menyentuh” beban besar — ia hanya memberi sinyal ke modul relay, lalu relay yang membuka/menutup saklar. Sumber gambar: diagram buatan Koding Indonesia (FS-23).
  </figcaption>
</figure>
SVG;
    }

    private function senseSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Flow: code to GPIO to relay to click" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 160" width="100%" height="auto" style="display:block;max-height:190px">
    <text x="430" y="28" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#1a1a1a">Simple flow: code → pin → switch click</text>
    <rect x="30" y="55" width="150" height="70" rx="10" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
    <text x="105" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" font-weight="700" fill="#0D47A1">digitalWrite</text>
    <text x="200" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="22" fill="#1a1a1a">→</text>
    <rect x="230" y="55" width="150" height="70" rx="10" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
    <text x="305" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" font-weight="700" fill="#1B5E20">GPIO 26</text>
    <text x="400" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="22" fill="#1a1a1a">→</text>
    <rect x="430" y="55" width="150" height="70" rx="10" fill="#FFF3E0" stroke="#EF6C00" stroke-width="2.5"/>
    <text x="505" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" font-weight="700" fill="#E65100">Relay module</text>
    <text x="600" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="22" fill="#1a1a1a">→</text>
    <rect x="630" y="55" width="200" height="70" rx="10" fill="#FFEBEE" stroke="#C62828" stroke-width="2.5"/>
    <text x="730" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" font-weight="700" fill="#B71C1C">Click + indicator LED</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.35rem;color:#4A5568;">
    <strong>In short:</strong> the ESP32 does not “touch” a big load — it only signals the relay module, and the relay opens/closes the switch. Image source: diagram by Koding Indonesia (FS-23).
  </figcaption>
</figure>
SVG;
    }

    private function activeSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Aktif LOW versus aktif HIGH pada modul relay" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 220" width="100%" height="auto" style="display:block;max-height:250px">
    <text x="430" y="28" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#1a1a1a">Aktif LOW vs aktif HIGH (baca silkscreen modulmu)</text>
    <rect x="40" y="55" width="360" height="120" rx="12" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
    <text x="220" y="90" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="18" font-weight="700" fill="#1B5E20">Sering di kit: aktif LOW</text>
    <text x="220" y="120" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" fill="#333">IN = LOW → relay ON (klik)</text>
    <text x="220" y="148" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" fill="#333">IN = HIGH → relay OFF</text>
    <rect x="460" y="55" width="360" height="120" rx="12" fill="#FFF3E0" stroke="#EF6C00" stroke-width="2.5"/>
    <text x="640" y="90" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="18" font-weight="700" fill="#E65100">Kadang: aktif HIGH</text>
    <text x="640" y="120" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" fill="#333">IN = HIGH → relay ON</text>
    <text x="640" y="148" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" fill="#333">IN = LOW → relay OFF</text>
    <text x="430" y="205" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#555">Kalau terasa “terbalik”, ubah logika di kode — jangan panik wiring dulu.</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.35rem;color:#4A5568;">
    <strong>Intinya:</strong> banyak modul optocoupler kit bersifat <strong>aktif LOW</strong>. Sketch di bawah mulai dari pola itu; sesuaikan jika modulmu beda. Sumber gambar: diagram buatan Koding Indonesia (FS-23).
  </figcaption>
</figure>
SVG;
    }

    private function activeSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Active LOW versus active HIGH on relay modules" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 220" width="100%" height="auto" style="display:block;max-height:250px">
    <text x="430" y="28" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#1a1a1a">Active LOW vs active HIGH (read your module silkscreen)</text>
    <rect x="40" y="55" width="360" height="120" rx="12" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
    <text x="220" y="90" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="18" font-weight="700" fill="#1B5E20">Common in kits: active LOW</text>
    <text x="220" y="120" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" fill="#333">IN = LOW → relay ON (click)</text>
    <text x="220" y="148" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" fill="#333">IN = HIGH → relay OFF</text>
    <rect x="460" y="55" width="360" height="120" rx="12" fill="#FFF3E0" stroke="#EF6C00" stroke-width="2.5"/>
    <text x="640" y="90" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="18" font-weight="700" fill="#E65100">Sometimes: active HIGH</text>
    <text x="640" y="120" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" fill="#333">IN = HIGH → relay ON</text>
    <text x="640" y="148" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" fill="#333">IN = LOW → relay OFF</text>
    <text x="430" y="205" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#555">If it feels “reversed”, flip the logic in code — don’t panic about wiring first.</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.35rem;color:#4A5568;">
    <strong>In short:</strong> many kit optocoupler modules are <strong>active LOW</strong>. The sketch below starts from that pattern; adjust if yours differs. Image source: diagram by Koding Indonesia (FS-23).
  </figcaption>
</figure>
SVG;
    }

    private function body(): string
    {
        $ide = $this->ideFigureId();
        $board = $this->boardFigureId();
        $kit = $this->kitFigureId();
        $main = $this->mainWiringFigureId();
        $schema = $this->schemaWiringFigureId();
        $sense = $this->senseSvgId();
        $active = $this->activeSvgId();

        return <<<HTML
<h2>Pendahuluan — otot kecil board</h2>
<p>Artikel ini adalah <strong>#93 (ini)</strong> · modul <strong>FS-23</strong> di jalur <em>Full Stack IoT Developer — Dari Nol</em> (fase <strong>BUILDER</strong>). Di <strong>FS-21</strong> dan <strong>FS-22</strong> board <em>membaca</em> dunia. Hari ini board mulai <em>menggerakkan</em> saklar lewat <strong>relay</strong>.</p>
<p><strong>Analogi:</strong> relay seperti saklar dinding yang diganti magnet kecil. Kode ESP32 hanya “menekan” pin kontrol; yang menyambung/memutus arus beban adalah kontak di dalam modul.</p>
{$sense}
<p><strong>Prasyarat:</strong> FS-18 (pernah Upload + <code>digitalWrite</code>) · FS-05 (kebiasaan aman listrik) · Arduino IDE sudah bisa Upload ke ESP32.</p>
<p><strong>Cara pakai artikel ini (urutan kerja):</strong></p>
<ol>
<li>Baca peringatan aman: <strong>belum AC 220V / PLN</strong>.</li>
<li>Rakit 3 kabel: 5V · GND · <strong>GPIO 26</strong>. Cocokkan <strong>gambar utama</strong> (foto breadboard).</li>
<li><strong>Buka Arduino IDE</strong> (bukan Laragon / terminal web).</li>
<li>Buat sketch <code>FS23_relay_klik</code> → <strong>Verify</strong> → <strong>Upload</strong>.</li>
<li>Dengarkan klik + lihat LED indikator + baca Serial baud <strong>115200</strong>.</li>
<li>Centang checklist 10/10 di browser.</li>
</ol>
<p><strong>Tidak perlu hari ini:</strong> Wi-Fi, MQTT, Library Manager baru, Laragon, <code>php artisan</code>, beban AC rumah. Tools hari ini: <strong>Arduino IDE</strong> + <strong>Upload</strong> + ESP32 + modul relay 5V + jumper + <strong>Serial Monitor</strong> + <strong>browser</strong>.</p>

<h2>Persiapan — buka &amp; siapkan ini dulu</h2>
<p><strong>Urutan meja kerja:</strong> baca aman → wiring 3 kabel → Upload → dengarkan klik.</p>
<ol>
<li>Buka <strong>Arduino IDE 2.x</strong> · board <strong>ESP32 Dev Module</strong> + port.</li>
<li>Siapkan ESP32 + USB data.</li>
<li>Siapkan modul <strong>relay 1 channel 5V</strong> + jumper.</li>
<li>Cari label <strong>GPIO 26</strong> / <strong>IO26</strong>, <strong>5V</strong>, dan <strong>GND</strong> di silkscreen.</li>
<li>Siapkan Serial Monitor baud <strong>115200</strong>.</li>
</ol>
<p><strong>Alat yang dipakai hari ini:</strong> Arduino IDE, Upload, ESP32, USB data, modul relay 5V, jumper, Serial Monitor, browser.</p>
<p><strong>Tidak dipakai hari ini:</strong> Laragon, <code>php artisan</code>, Library Manager baru, DHT22, LDR, colokan AC 220V.</p>
{$ide}
{$board}
{$kit}
{$main}
{$schema}

<h2>Wiring relay (bahasa manusia)</h2>
<p><strong>Rangkaian kontrol (3 kabel):</strong></p>
<ul>
<li><strong>VCC</strong> / pin <strong>+</strong> modul → pin <strong>5V</strong> ESP32</li>
<li><strong>GND</strong> / pin <strong>−</strong> modul → <strong>GND</strong> ESP32</li>
<li><strong>IN</strong> / pin <strong>S</strong> modul → <strong>GPIO 26</strong> (sering tertulis <strong>IO26</strong>)</li>
</ul>
<p><strong>Kenapa GPIO 26?</strong> Itu pin relay di tabel global FS-17 — konsisten sampai automasi lokal berikutnya.</p>
<p><strong>Terminal sekrup (COM / NO / NC):</strong> itu jalur <em>beban</em>. Di latihan pertama, boleh kosong. Kalau mau uji ekstra aman: LED + resistor 220 Ω di sisi DC kecil saja — <strong>bukan</strong> kabel PLN.</p>

<h2>Praktik — sketch FS23_relay_klik</h2>
{$active}
<p>Tujuan: setiap ±2 detik relay <strong>klik</strong>, LED indikator modul berubah, Serial menulis ON/OFF. Pola awal = <strong>aktif LOW</strong> (umum di kit).</p>
<ol>
<li>Arduino IDE → <strong>File → New Sketch</strong> → <strong>Simpan sebagai</strong> <code>FS23_relay_klik</code>.</li>
<li>Ganti isi dengan kode di bawah (salin utuh).</li>
<li><strong>Verify</strong> → <strong>Upload</strong> → tunggu <em>Done uploading</em>.</li>
<li>Buka Serial Monitor baud <strong>115200</strong>.</li>
<li>Dengarkan klik. Kalau LED/klik terasa terbalik terhadap teks Serial, ubah <code>AKTIF_LOW</code> menjadi <code>false</code>.</li>
</ol>
<pre><code class="language-cpp">// FS23_relay_klik — Full Stack IoT FS-23
// GPIO 26 → IN modul relay 5V (pola aktif LOW default)

const int PIN_RELAY = 26; // tabel global FS-17
const bool AKTIF_LOW = true; // true = kit umum; false = aktif HIGH

void setRelay(bool nyala) {
  if (AKTIF_LOW) {
    digitalWrite(PIN_RELAY, nyala ? LOW : HIGH);
  } else {
    digitalWrite(PIN_RELAY, nyala ? HIGH : LOW);
  }
}

void setup() {
  pinMode(PIN_RELAY, OUTPUT);
  Serial.begin(115200);
  delay(500);
  Serial.println("FS23_relay_klik siap");
  setRelay(false);
}

void loop() {
  setRelay(true);
  Serial.println("RELAY ON");
  delay(2000);

  setRelay(false);
  Serial.println("RELAY OFF");
  delay(2000);
}
</code></pre>
<p><strong>Cara menguji perintah di atas:</strong> uji di <strong>Arduino IDE + Upload + Serial Monitor</strong>. Sukses = ada klik bergantian + teks ON/OFF. Bukan perintah Laragon / web server.</p>
<p><strong>Tip aktif HIGH/LOW:</strong> kalau Serial bilang ON tapi LED indikator mati (atau sebaliknya), cukup ubah <code>AKTIF_LOW</code>. Jangan buru-buru cabut kabel 5V/GND.</p>

<h2 id="fsiot-relay-checklist">Praktik — checklist relay aman</h2>
<p>Centang setiap langkah setelah kamu lakukan di meja. Target: <strong>10/10</strong>.</p>
<ul id="fsiot-relay-checklist-items">
<li>Arduino IDE sudah terbuka sebelum menulis kode</li>
<li>Board ESP32 Dev Module + port sudah dipilih</li>
<li>Wiring: 5V–VCC, GND–GND, GPIO 26–IN</li>
<li>Paham: hari ini belum boleh AC 220V / PLN</li>
<li>Paham: aktif LOW vs aktif HIGH bisa beda antar modul</li>
<li>Sketch disimpan sebagai FS23_relay_klik</li>
<li>Upload berhasil — Done uploading</li>
<li>Relay berbunyi klik bergantian</li>
<li>Serial menampilkan ON/OFF selaras dengan perilaku</li>
<li>Sadar: ini fondasi aktuator sebelum automasi lokal FS-24</li>
</ul>
<p><strong>Cara menguji checklist:</strong> centang di browser setelah praktik di IDE + board. Tidak perlu <code>php artisan</code>.</p>

<h2>Kesalahan yang sering terjadi</h2>
<ul>
<li><strong>Langsung colok AC 220V.</strong> Di jalur pemula: <strong>dilarang</strong>. Latihan klik + LED indikator dulu.</li>
<li><strong>VCC ke 3V3 padahal coil butuh 5V.</strong> Banyak modul kit butuh 5V — klik lemah atau tidak terjadi.</li>
<li><strong>Lupa ground bersama.</strong> Tanpa GND bersama, sinyal IN tidak “ketemu” tanah yang sama.</li>
<li><strong>Salah pin (bukan GPIO 26).</strong> Cocokkan silkscreen IO26 / tabel FS-17.</li>
<li><strong>Mengira semua modul aktif HIGH.</strong> Kit optocoupler sering aktif LOW — sesuaikan <code>AKTIF_LOW</code>.</li>
<li><strong>Menguji di terminal web.</strong> Sketch hanya jalan di board lewat IDE Upload.</li>
</ul>

<h2>Selanjutnya</h2>
<p><strong>Intinya:</strong> kalau relay berbunyi klik selaras Serial ON/OFF tanpa menyentuh AC PLN, FS-23 selesai — fondasi aktuator aman terbuka.</p>
<p>Lanjut ke <strong>FS-24</strong> (jika panas → nyalakan “kipas” lewat relay) saat modulnya terbit — gabungan sensor + keputusan + aktuator.</p>
<p>Daftar modul lengkap: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $ide = $this->ideFigureEn();
        $board = $this->boardFigureEn();
        $kit = $this->kitFigureEn();
        $main = $this->mainWiringFigureEn();
        $schema = $this->schemaWiringFigureEn();
        $sense = $this->senseSvgEn();
        $active = $this->activeSvgEn();

        return <<<HTML
<h2>Introduction — the board’s small muscle</h2>
<p>This is article <strong>#93 (this article)</strong> · module <strong>FS-23</strong> on the <em>Full Stack IoT Developer — From Zero</em> path (<strong>BUILDER</strong> phase). In <strong>FS-21</strong> and <strong>FS-22</strong> the board <em>read</em> the world. Today it starts <em>driving</em> a switch through a <strong>relay</strong>.</p>
<p><strong>Analogy:</strong> a relay is like a wall switch moved by a tiny magnet. ESP32 code only “presses” a control pin; the contacts inside the module connect or disconnect the load path.</p>
{$sense}
<p><strong>Prerequisites:</strong> FS-18 (you have Uploaded + used <code>digitalWrite</code>) · FS-05 (safe electrical habits) · Arduino IDE can Upload to the ESP32.</p>
<p><strong>How to use this article (work order):</strong></p>
<ol>
<li>Read the safety note: <strong>no AC mains / 220V yet</strong>.</li>
<li>Build 3 wires: 5V · GND · <strong>GPIO 26</strong>. Match the <strong>main figure</strong> (breadboard photo).</li>
<li><strong>Open Arduino IDE</strong> (not Laragon / a web terminal).</li>
<li>Create sketch <code>FS23_relay_klik</code> → <strong>Verify</strong> → <strong>Upload</strong>.</li>
<li>Listen for the click + watch the indicator LED + read Serial at baud <strong>115200</strong>.</li>
<li>Tick the 10/10 checklist in the browser.</li>
</ol>
<p><strong>Not needed today:</strong> Wi-Fi, MQTT, a new Library Manager install, Laragon, <code>php artisan</code>, household AC loads. Today's tools: <strong>Arduino IDE</strong> + <strong>Upload</strong> + ESP32 + 5V relay module + jumpers + <strong>Serial Monitor</strong> + <strong>browser</strong>.</p>

<h2>Preparation — open &amp; gather these first</h2>
<p><strong>Desk order:</strong> read safety → 3-wire wiring → Upload → listen for clicks.</p>
<ol>
<li>Open <strong>Arduino IDE 2.x</strong> · <strong>ESP32 Dev Module</strong> board + port.</li>
<li>Prepare the ESP32 + USB data cable.</li>
<li>Prepare a <strong>1-channel 5V relay module</strong> + jumpers.</li>
<li>Find <strong>GPIO 26</strong> / <strong>IO26</strong>, <strong>5V</strong>, and <strong>GND</strong> on the silkscreen.</li>
<li>Have Serial Monitor ready at baud <strong>115200</strong>.</li>
</ol>
<p><strong>Tools used today:</strong> Arduino IDE, Upload, ESP32, USB data, 5V relay module, jumpers, Serial Monitor, browser.</p>
<p><strong>Not used today:</strong> Laragon, <code>php artisan</code>, a new Library Manager install, DHT22, LDR, AC 220V plugs.</p>
{$ide}
{$board}
{$kit}
{$main}
{$schema}

<h2>Relay wiring (human language)</h2>
<p><strong>Control wiring (3 wires):</strong></p>
<ul>
<li>Module <strong>VCC</strong> / <strong>+</strong> → ESP32 <strong>5V</strong></li>
<li>Module <strong>GND</strong> / <strong>−</strong> → ESP32 <strong>GND</strong></li>
<li>Module <strong>IN</strong> / <strong>S</strong> → <strong>GPIO 26</strong> (often labeled <strong>IO26</strong>)</li>
</ul>
<p><strong>Why GPIO 26?</strong> That is the relay pin in the FS-17 global table — consistent through the next local-automation modules.</p>
<p><strong>Screw terminals (COM / NO / NC):</strong> that is the <em>load</em> path. For the first practice they may stay empty. Extra-safe optional test: a small DC LED + 220 Ω only — <strong>not</strong> mains cable.</p>

<h2>Practice — sketch FS23_relay_klik</h2>
{$active}
<p>Goal: about every 2 seconds the relay <strong>clicks</strong>, the module LED changes, and Serial prints ON/OFF. Default pattern = <strong>active LOW</strong> (common in kits).</p>
<ol>
<li>Arduino IDE → <strong>File → New Sketch</strong> → <strong>Save As</strong> <code>FS23_relay_klik</code>.</li>
<li>Replace the contents with the code below (copy whole).</li>
<li><strong>Verify</strong> → <strong>Upload</strong> → wait for <em>Done uploading</em>.</li>
<li>Open Serial Monitor at baud <strong>115200</strong>.</li>
<li>Listen for clicks. If LED/click feels reversed vs Serial text, set <code>ACTIVE_LOW</code> to <code>false</code>.</li>
</ol>
<pre><code class="language-cpp">// FS23_relay_klik — Full Stack IoT FS-23
// GPIO 26 → 5V relay module IN (active-LOW default)

const int PIN_RELAY = 26; // FS-17 global table
const bool ACTIVE_LOW = true; // true = common kits; false = active HIGH

void setRelay(bool on) {
  if (ACTIVE_LOW) {
    digitalWrite(PIN_RELAY, on ? LOW : HIGH);
  } else {
    digitalWrite(PIN_RELAY, on ? HIGH : LOW);
  }
}

void setup() {
  pinMode(PIN_RELAY, OUTPUT);
  Serial.begin(115200);
  delay(500);
  Serial.println("FS23_relay_klik ready");
  setRelay(false);
}

void loop() {
  setRelay(true);
  Serial.println("RELAY ON");
  delay(2000);

  setRelay(false);
  Serial.println("RELAY OFF");
  delay(2000);
}
</code></pre>
<p><strong>How to test the commands above:</strong> test in <strong>Arduino IDE + Upload + Serial Monitor</strong>. Success = alternating clicks + ON/OFF text. Not a Laragon / web-server command.</p>
<p><strong>Active HIGH/LOW tip:</strong> if Serial says ON but the indicator LED is off (or the reverse), just flip <code>ACTIVE_LOW</code>. Don’t rush to unplug 5V/GND.</p>

<h2 id="fsiot-relay-checklist">Practice — safe-relay checklist</h2>
<p>Tick each step after you do it at the desk. Target: <strong>10/10</strong>.</p>
<ul id="fsiot-relay-checklist-items">
<li>Arduino IDE is open before writing code</li>
<li>ESP32 Dev Module board + port are selected</li>
<li>Wiring: 5V–VCC, GND–GND, GPIO 26–IN</li>
<li>I understand: no AC mains / 220V today</li>
<li>I understand: active LOW vs active HIGH can differ by module</li>
<li>Sketch saved as FS23_relay_klik</li>
<li>Upload succeeded — Done uploading</li>
<li>Relay clicks alternately</li>
<li>Serial ON/OFF matches the behavior</li>
<li>I know: this is the actuator foundation before FS-24 local automation</li>
</ul>
<p><strong>How to test the checklist:</strong> tick in the browser after practice on the IDE + board. No <code>php artisan</code> needed.</p>

<h2>Common mistakes</h2>
<ul>
<li><strong>Plugging in AC 220V immediately.</strong> On this Core path: <strong>forbidden</strong>. Practice click + indicator LED first.</li>
<li><strong>VCC on 3V3 when the coil needs 5V.</strong> Many kit modules need 5V — weak or missing clicks.</li>
<li><strong>Missing shared ground.</strong> Without shared GND, the IN signal has no common return.</li>
<li><strong>Wrong pin (not GPIO 26).</strong> Match silkscreen IO26 / the FS-17 table.</li>
<li><strong>Assuming every module is active HIGH.</strong> Optocoupler kits are often active LOW — tune <code>ACTIVE_LOW</code>.</li>
<li><strong>Testing in a web terminal.</strong> The sketch runs on the board via IDE Upload only.</li>
</ul>

<h2>Next</h2>
<p><strong>In short:</strong> if the relay clicks in sync with Serial ON/OFF without touching AC mains, FS-23 is done — the safe actuator foundation is open.</p>
<p>Continue to <strong>FS-24</strong> (if hot → turn on a “fan” via relay) when that module publishes — sensor + decision + actuator together.</p>
<p>Full module list: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }
}
