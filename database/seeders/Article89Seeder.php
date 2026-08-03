<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article89Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first()
            ?? Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $iotCat) {
            throw new \RuntimeException('User atau kategori iot-smart-device/esp32-arduino tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-tombol-debounce';

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
                'title'              => 'Tombol + debounce — digitalRead & millis',
                'title_en'           => 'Button + debounce — digitalRead & millis',
                'excerpt'            => 'FS-19 / #89: tombol GPIO 27 andal. Uji di Arduino IDE: digitalRead + debounce millis, toggle LED GPIO 2 tanpa double-klik.',
                'excerpt_en'         => 'FS-19 / #89: reliable GPIO 27 button. Test in Arduino IDE: digitalRead + millis debounce, toggle LED GPIO 2 without double-clicks.',
                'body'               => $this->body(),
                'body_en'            => $this->bodyEn(),
                'status'             => 'draft',
                'is_featured'        => false,
                'published_at'       => null,
                'seo_title'          => 'Tombol + debounce — Full Stack IoT #89',
                'seo_title_en'       => 'Button + debounce — Full Stack IoT #89',
                'seo_description'    => 'Belajar digitalRead, INPUT_PULLUP, bounce, dan millis di ESP32. Modul FS-19 jalur Full Stack IoT.',
                'seo_description_en' => 'Learn digitalRead, INPUT_PULLUP, bounce, and millis on ESP32. Full Stack IoT FS-19 module.',
            ]
        );

        if ($article->published_at !== null || $article->status !== 'draft') {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['fullstack-iot', 'iot', 'esp32'])->pluck('id');
        $article->tags()->sync($tagIds);

        // Cover hanya diisi jika masih kosong — jangan timpa cover yang sudah di-upload manual.
        if (blank($article->cover_image)) {
            $src = public_path('images/fsiot/fs19-cover-button-led.jpg');
            if (is_file($src)) {
                $dest = 'articles/covers/fs19-cover-button-led.jpg';
                \Illuminate\Support\Facades\Storage::disk('public')->put($dest, file_get_contents($src));
                $article->cover_image = $dest;
                $article->save();
            }
        }

        $this->command?->info('✓ Artikel #89 / FS-19 tersimpan sebagai DRAFT: '.$article->title);
    }

    private function ideFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/fs11-ide-overview-cite.png" width="1280" height="720" alt="Arduino IDE 2 — Verify, Upload, dan Serial Monitor" loading="eager" style="width:100%;height:auto;max-height:420px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Arduino IDE 2</strong> — tempat menguji sintaks hari ini. <strong>Verify</strong> → <strong>Upload</strong> → buka <strong>Serial Monitor</strong> (baud 115200) untuk melihat <code>TOMBOL</code> / <code>detak</code>. Board: <strong>ESP32 Dev Module</strong>.
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:Ide-2-overview.png" rel="noopener noreferrer" target="_blank">Arduino IDE 2 overview</a> · Wikimedia Commons (CC BY-SA 3.0). Panduan Serial: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-serial-monitor/" rel="noopener noreferrer" target="_blank">Arduino Docs — Serial Monitor</a>.
  </figcaption>
</figure>
HTML;
    }

    private function ideFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/fs11-ide-overview-cite.png" width="1280" height="720" alt="Arduino IDE 2 — Verify, Upload, and Serial Monitor" loading="eager" style="width:100%;height:auto;max-height:420px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Arduino IDE 2</strong> — where today’s syntax is tested. <strong>Verify</strong> → <strong>Upload</strong> → open <strong>Serial Monitor</strong> (baud 115200) to watch <code>BUTTON</code> / <code>tick</code> lines. Board: <strong>ESP32 Dev Module</strong>.
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:Ide-2-overview.png" rel="noopener noreferrer" target="_blank">Arduino IDE 2 overview</a> · Wikimedia Commons (CC BY-SA 3.0). Serial guide: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-serial-monitor/" rel="noopener noreferrer" target="_blank">Arduino Docs — Serial Monitor</a>.
  </figcaption>
</figure>
HTML;
    }

    private function boardFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/esp32-devkitc-overview.jpg" width="1200" height="800" alt="ESP32-DevKitC — USB (6) dan EN (7)" loading="eager" style="width:100%;height:auto;max-height:360px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>ESP32-DevKitC</strong> — USB data di <strong>(6)</strong>, reset di <strong>EN (7)</strong>. Tombol latihan FSIOT = <strong>GPIO 27</strong> (bukan GPIO 0). LED tetap <strong>GPIO 2</strong> (FS-18).
    <br>Sumber gambar: <a href="https://docs.espressif.com/projects/esp-dev-kits/en/latest/esp32/esp32-devkitc/user_guide.html" rel="noopener noreferrer" target="_blank">Espressif — ESP32-DevKitC user guide</a>. Pin board: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/boards/ESP32-DevKitC-1.html" rel="noopener noreferrer" target="_blank">ESP32-DevKitC-1</a>.
  </figcaption>
</figure>
HTML;
    }

    private function boardFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/esp32-devkitc-overview.jpg" width="1200" height="800" alt="ESP32-DevKitC — USB (6) and EN (7)" loading="eager" style="width:100%;height:auto;max-height:360px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>ESP32-DevKitC</strong> — USB data at <strong>(6)</strong>, reset on <strong>EN (7)</strong>. FSIOT practice button = <strong>GPIO 27</strong> (not GPIO 0). LED stays <strong>GPIO 2</strong> (FS-18).
    <br>Image source: <a href="https://docs.espressif.com/projects/esp-dev-kits/en/latest/esp32/esp32-devkitc/user_guide.html" rel="noopener noreferrer" target="_blank">Espressif — ESP32-DevKitC user guide</a>. Board pins: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/boards/ESP32-DevKitC-1.html" rel="noopener noreferrer" target="_blank">ESP32-DevKitC-1</a>.
  </figcaption>
</figure>
HTML;
    }

    private function kitFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;align-items:start">
    <div>
      <img src="/images/fsiot/kit-tactile-button.jpg" width="800" height="600" alt="Tombol tactile 4 kaki" loading="eager" style="display:block;width:100%;height:auto;max-height:260px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem;margin:0 auto">
      <p style="font-size:0.8rem;margin:0.35rem 0 0;color:#4A5568;text-align:center"><strong>Tombol tactile</strong> — 4 kaki, lintasi parit tengah breadboard</p>
    </div>
    <div>
      <img src="/images/fsiot/kit-led-5mm.jpg" width="900" height="900" alt="LED 5 mm untuk toggle GPIO 2" loading="eager" style="display:block;width:100%;height:auto;max-height:260px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem;margin:0 auto">
      <p style="font-size:0.8rem;margin:0.35rem 0 0;color:#4A5568;text-align:center"><strong>LED GPIO 2</strong> — sama seperti FS-18</p>
    </div>
  </div>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Komponen hari ini:</strong> tombol tactile + LED (+ resistor 220 Ω dari FS-18). Hari ini kita pakai <strong>INPUT_PULLUP</strong> — tidak wajib resistor 10 kΩ ekstra (itu pola FS-10).
    <br>Sumber gambar: foto kit Koding Indonesia (FS-04 / FS-10 / FS-18).
  </figcaption>
</figure>
HTML;
    }

    private function kitFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;align-items:start">
    <div>
      <img src="/images/fsiot/kit-tactile-button.jpg" width="800" height="600" alt="4-leg tactile button" loading="eager" style="display:block;width:100%;height:auto;max-height:260px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem;margin:0 auto">
      <p style="font-size:0.8rem;margin:0.35rem 0 0;color:#4A5568;text-align:center"><strong>Tactile button</strong> — 4 legs, across the breadboard center ditch</p>
    </div>
    <div>
      <img src="/images/fsiot/kit-led-5mm.jpg" width="900" height="900" alt="5 mm LED for GPIO 2 toggle" loading="eager" style="display:block;width:100%;height:auto;max-height:260px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem;margin:0 auto">
      <p style="font-size:0.8rem;margin:0.35rem 0 0;color:#4A5568;text-align:center"><strong>GPIO 2 LED</strong> — same as FS-18</p>
    </div>
  </div>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Parts today:</strong> tactile button + LED (+ 220 Ω resistor from FS-18). Today we use <strong>INPUT_PULLUP</strong> — no extra 10 kΩ required (that was the FS-10 pattern).
    <br>Image source: Koding Indonesia kit photos (FS-04 / FS-10 / FS-18).
  </figcaption>
</figure>
HTML;
    }

    private function mainWiringFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/fs19-button-pullup-wiring.png" width="960" height="520" alt="Gambar utama — wiring tombol GPIO 27 INPUT_PULLUP dan LED GPIO 2" loading="eager" style="width:100%;height:auto;max-height:480px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#F5F5F0;padding:0.35rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Gambar utama:</strong> jalur tombol <strong>GPIO 27 → tombol → GND</strong> (pakai <code>INPUT_PULLUP</code> di kode) + LED di <strong>GPIO 2</strong>.
    <br>Sumber gambar: diagram berlabel buatan Koding Indonesia (FS-19).
  </figcaption>
</figure>
HTML;
    }

    private function mainWiringFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/fs19-button-pullup-wiring.png" width="960" height="520" alt="Main figure — GPIO 27 INPUT_PULLUP button wiring and GPIO 2 LED" loading="eager" style="width:100%;height:auto;max-height:480px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#F5F5F0;padding:0.35rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Main figure:</strong> button path <strong>GPIO 27 → button → GND</strong> (use <code>INPUT_PULLUP</code> in code) + LED on <strong>GPIO 2</strong>.
    <br>Image source: labeled diagram by Koding Indonesia (FS-19).
  </figcaption>
</figure>
HTML;
    }

    private function fs10RefFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/fs10-button-pulldown-wiring.png" width="1200" height="800" alt="Foto acuan FS-10 — tombol di breadboard dengan resistor pull-down" loading="lazy" style="width:100%;height:auto;max-height:420px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Foto acuan (pola lain):</strong> di FS-10 tombol memakai resistor pull-down eksternal. Hari ini lebih sederhana: <strong>INPUT_PULLUP</strong> (tanpa 10 kΩ), tapi bentuk fisik tombol di breadboard tetap sama — kaki melintasi <em>parit tengah</em>.
    <br>Sumber gambar: foto rangkaian Koding Indonesia (FS-10).
  </figcaption>
</figure>
HTML;
    }

    private function fs10RefFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/fs10-button-pulldown-wiring.png" width="1200" height="800" alt="FS-10 reference photo — breadboard button with pull-down resistor" loading="lazy" style="width:100%;height:auto;max-height:420px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Reference photo (other pattern):</strong> FS-10 used an external pull-down resistor. Today is simpler: <strong>INPUT_PULLUP</strong> (no 10 kΩ), but the physical button on the breadboard looks the same — legs across the <em>center ditch</em>.
    <br>Image source: Koding Indonesia wiring photo (FS-10).
  </figcaption>
</figure>
HTML;
    }

    private function wiringSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Ringkasan dua jalur: tombol GPIO 27 dan LED GPIO 2" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 240" width="100%" height="auto" style="display:block;max-height:280px">
    <text x="430" y="26" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#1a1a1a">Ringkasan dua jalur (baca dari kiri ke kanan)</text>
    <text x="40" y="58" font-family="Segoe UI,sans-serif" font-size="13" font-weight="700" fill="#0D47A1">Jalur A — tombol</text>
    <rect x="40" y="68" width="170" height="54" rx="8" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
    <text x="125" y="92" text-anchor="middle" font-family="Consolas,monospace" font-size="15" font-weight="700" fill="#0D47A1">GPIO 27</text>
    <text x="125" y="110" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="11" fill="#333">baca tombol</text>
    <text x="230" y="100" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="20" fill="#1a1a1a">→</text>
    <rect x="250" y="68" width="170" height="54" rx="8" fill="#FFF3E0" stroke="#EF6C00" stroke-width="2.5"/>
    <text x="335" y="92" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="15" font-weight="700" fill="#E65100">Tombol</text>
    <text x="335" y="110" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="11" fill="#333">parit tengah</text>
    <text x="440" y="100" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="20" fill="#1a1a1a">→</text>
    <rect x="460" y="68" width="150" height="54" rx="8" fill="#ECEFF1" stroke="#455A64" stroke-width="2.5"/>
    <text x="535" y="92" text-anchor="middle" font-family="Consolas,monospace" font-size="15" font-weight="700" fill="#263238">GND</text>
    <text x="535" y="110" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="11" fill="#333">tanah</text>
    <text x="640" y="100" font-family="Segoe UI,sans-serif" font-size="12" fill="#555">lepas=HIGH · tekan=LOW</text>
    <text x="40" y="158" font-family="Segoe UI,sans-serif" font-size="13" font-weight="700" fill="#1B5E20">Jalur B — LED (dari FS-18)</text>
    <rect x="40" y="168" width="170" height="54" rx="8" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
    <text x="125" y="192" text-anchor="middle" font-family="Consolas,monospace" font-size="15" font-weight="700" fill="#1B5E20">GPIO 2</text>
    <text x="125" y="210" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="11" fill="#333">keluaran</text>
    <text x="230" y="200" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="20" fill="#1a1a1a">→</text>
    <rect x="250" y="168" width="170" height="54" rx="8" fill="#FFF8E1" stroke="#F9A825" stroke-width="2.5"/>
    <text x="335" y="192" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="15" font-weight="700" fill="#F57F17">220 ohm</text>
    <text x="335" y="210" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="11" fill="#333">batas arus</text>
    <text x="440" y="200" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="20" fill="#1a1a1a">→</text>
    <rect x="460" y="168" width="150" height="54" rx="8" fill="#FFECB3" stroke="#FF8F00" stroke-width="2.5"/>
    <text x="535" y="192" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="15" font-weight="700" fill="#E65100">LED</text>
    <text x="535" y="210" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="11" fill="#333">lalu ke GND</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.35rem;color:#4A5568;">
    <strong>Intinya:</strong> dua jalur terpisah — tombol di GPIO 27, LED di GPIO 2. Jangan pakai GPIO 0 untuk tombol.
    <br>Sumber gambar: diagram buatan Koding Indonesia (FS-19).
  </figcaption>
</figure>
SVG;
    }

    private function wiringSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Two-path summary: GPIO 27 button and GPIO 2 LED" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 240" width="100%" height="auto" style="display:block;max-height:280px">
    <text x="430" y="26" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#1a1a1a">Two-path summary (read left to right)</text>
    <text x="40" y="58" font-family="Segoe UI,sans-serif" font-size="13" font-weight="700" fill="#0D47A1">Path A — button</text>
    <rect x="40" y="68" width="170" height="54" rx="8" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
    <text x="125" y="92" text-anchor="middle" font-family="Consolas,monospace" font-size="15" font-weight="700" fill="#0D47A1">GPIO 27</text>
    <text x="125" y="110" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="11" fill="#333">read button</text>
    <text x="230" y="100" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="20" fill="#1a1a1a">→</text>
    <rect x="250" y="68" width="170" height="54" rx="8" fill="#FFF3E0" stroke="#EF6C00" stroke-width="2.5"/>
    <text x="335" y="92" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="15" font-weight="700" fill="#E65100">Button</text>
    <text x="335" y="110" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="11" fill="#333">center ditch</text>
    <text x="440" y="100" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="20" fill="#1a1a1a">→</text>
    <rect x="460" y="68" width="150" height="54" rx="8" fill="#ECEFF1" stroke="#455A64" stroke-width="2.5"/>
    <text x="535" y="92" text-anchor="middle" font-family="Consolas,monospace" font-size="15" font-weight="700" fill="#263238">GND</text>
    <text x="535" y="110" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="11" fill="#333">ground</text>
    <text x="640" y="100" font-family="Segoe UI,sans-serif" font-size="12" fill="#555">release=HIGH · press=LOW</text>
    <text x="40" y="158" font-family="Segoe UI,sans-serif" font-size="13" font-weight="700" fill="#1B5E20">Path B — LED (from FS-18)</text>
    <rect x="40" y="168" width="170" height="54" rx="8" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
    <text x="125" y="192" text-anchor="middle" font-family="Consolas,monospace" font-size="15" font-weight="700" fill="#1B5E20">GPIO 2</text>
    <text x="125" y="210" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="11" fill="#333">output</text>
    <text x="230" y="200" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="20" fill="#1a1a1a">→</text>
    <rect x="250" y="168" width="170" height="54" rx="8" fill="#FFF8E1" stroke="#F9A825" stroke-width="2.5"/>
    <text x="335" y="192" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="15" font-weight="700" fill="#F57F17">220 ohm</text>
    <text x="335" y="210" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="11" fill="#333">current limit</text>
    <text x="440" y="200" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="20" fill="#1a1a1a">→</text>
    <rect x="460" y="168" width="150" height="54" rx="8" fill="#FFECB3" stroke="#FF8F00" stroke-width="2.5"/>
    <text x="535" y="192" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="15" font-weight="700" fill="#E65100">LED</text>
    <text x="535" y="210" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="11" fill="#333">then to GND</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.35rem;color:#4A5568;">
    <strong>In short:</strong> two separate paths — button on GPIO 27, LED on GPIO 2. Do not use GPIO 0 for the button.
    <br>Image source: diagram by Koding Indonesia (FS-19).
  </figcaption>
</figure>
SVG;
    }

    private function bounceSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Bounce tombol: sinyal bergetar vs sinyal bersih setelah debounce" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 210" width="100%" height="auto" style="display:block;max-height:240px">
    <text x="430" y="26" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#1a1a1a">Bounce = getaran mekanik singkat</text>
    <rect x="40" y="45" width="360" height="130" rx="8" fill="#FFEBEE" stroke="#C62828" stroke-width="2.5"/>
    <text x="220" y="70" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" font-weight="700" fill="#B71C1C">Tanpa debounce</text>
    <polyline points="70,150 100,70 120,140 140,75 160,135 180,80 210,70 320,70" fill="none" stroke="#C62828" stroke-width="3"/>
    <text x="220" y="165" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#666">banyak tepi → LED bisa double-toggle</text>
    <rect x="460" y="45" width="360" height="130" rx="8" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
    <text x="640" y="70" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" font-weight="700" fill="#1B5E20">Dengan debounce millis</text>
    <polyline points="490,70 560,70 580,150 740,150" fill="none" stroke="#2E7D32" stroke-width="3"/>
    <text x="640" y="165" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#666">satu tepi bersih → satu tekan = satu aksi</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.35rem;color:#4A5568;">
    <strong>Intinya:</strong> tunggu sinyal stabil ±50 ms sebelum percaya “baru ditekan”. Sumber gambar: diagram buatan Koding Indonesia (FS-19).
  </figcaption>
</figure>
SVG;
    }

    private function bounceSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Button bounce: noisy edges versus clean edge after debounce" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 210" width="100%" height="auto" style="display:block;max-height:240px">
    <text x="430" y="26" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#1a1a1a">Bounce = a short mechanical chatter</text>
    <rect x="40" y="45" width="360" height="130" rx="8" fill="#FFEBEE" stroke="#C62828" stroke-width="2.5"/>
    <text x="220" y="70" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" font-weight="700" fill="#B71C1C">Without debounce</text>
    <polyline points="70,150 100,70 120,140 140,75 160,135 180,80 210,70 320,70" fill="none" stroke="#C62828" stroke-width="3"/>
    <text x="220" y="165" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#666">many edges → LED may double-toggle</text>
    <rect x="460" y="45" width="360" height="130" rx="8" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
    <text x="640" y="70" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" font-weight="700" fill="#1B5E20">With millis debounce</text>
    <polyline points="490,70 560,70 580,150 740,150" fill="none" stroke="#2E7D32" stroke-width="3"/>
    <text x="640" y="165" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#666">one clean edge → one press = one action</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.35rem;color:#4A5568;">
    <strong>In short:</strong> wait until the signal stays stable ~50 ms before trusting a “new press”. Image source: diagram by Koding Indonesia (FS-19).
  </figcaption>
</figure>
SVG;
    }

    private function millisSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="millis versus delay: jam dinding terus berdetak tanpa membekukan program" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 185" width="100%" height="auto" style="display:block;max-height:210px">
    <text x="430" y="26" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#1a1a1a">millis() = jam dinding di dalam chip</text>
    <rect x="40" y="50" width="360" height="95" rx="8" fill="#FFEBEE" stroke="#C62828" stroke-width="2.5"/>
    <text x="220" y="82" text-anchor="middle" font-family="Consolas,monospace" font-size="15" font-weight="700" fill="#B71C1C">delay(1000)</text>
    <text x="220" y="108" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#333">program “tidur” — tidak baca tombol</text>
    <text x="220" y="130" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#666">mudah, tapi membekukan loop</text>
    <rect x="460" y="50" width="360" height="95" rx="8" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
    <text x="640" y="82" text-anchor="middle" font-family="Consolas,monospace" font-size="15" font-weight="700" fill="#0D47A1">millis()</text>
    <text x="640" y="108" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#333">cek jam: “sudah lewat N ms?”</text>
    <text x="640" y="130" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#666">loop tetap bisa baca tombol</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.35rem;color:#4A5568;">
    <strong>Intinya:</strong> <code>millis()</code> bukan jam internet — hanya penghitung milidetik sejak board menyala. Sumber gambar: diagram buatan Koding Indonesia (FS-19). Referensi: <a href="https://docs.arduino.cc/language-reference/en/functions/time/millis/" rel="noopener noreferrer" target="_blank">Arduino — millis()</a>.
  </figcaption>
</figure>
SVG;
    }

    private function millisSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="millis versus delay: a wall clock that keeps ticking without freezing the program" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 185" width="100%" height="auto" style="display:block;max-height:210px">
    <text x="430" y="26" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#1a1a1a">millis() = a wall clock inside the chip</text>
    <rect x="40" y="50" width="360" height="95" rx="8" fill="#FFEBEE" stroke="#C62828" stroke-width="2.5"/>
    <text x="220" y="82" text-anchor="middle" font-family="Consolas,monospace" font-size="15" font-weight="700" fill="#B71C1C">delay(1000)</text>
    <text x="220" y="108" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#333">program “sleeps” — no button reads</text>
    <text x="220" y="130" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#666">easy, but freezes the loop</text>
    <rect x="460" y="50" width="360" height="95" rx="8" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
    <text x="640" y="82" text-anchor="middle" font-family="Consolas,monospace" font-size="15" font-weight="700" fill="#0D47A1">millis()</text>
    <text x="640" y="108" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#333">check the clock: “N ms passed?”</text>
    <text x="640" y="130" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#666">loop can still read the button</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.35rem;color:#4A5568;">
    <strong>In short:</strong> <code>millis()</code> is not internet time — just milliseconds since the board powered on. Image source: diagram by Koding Indonesia (FS-19). Reference: <a href="https://docs.arduino.cc/language-reference/en/functions/time/millis/" rel="noopener noreferrer" target="_blank">Arduino — millis()</a>.
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
        $fs10 = $this->fs10RefFigureId();
        $wiring = $this->wiringSvgId();
        $bounce = $this->bounceSvgId();
        $millis = $this->millisSvgId();

        return <<<HTML
<h2>Pendahuluan — tombol yang andal</h2>
<p>Artikel ini adalah <strong>#89 (ini)</strong> · modul <strong>FS-19</strong> di jalur <em>Full Stack IoT Developer — Dari Nol</em> (fase <strong>BUILDER</strong>). Di <strong>FS-18</strong> LED dikendalikan dari kode. Hari ini kita <strong>membaca</strong> dunia nyata: tombol di <strong>GPIO 27</strong>, plus kenalan <code>millis()</code>.</p>
<p><strong>Analogi:</strong> tombol mekanik sering “getar” sepersekian detik (bounce) — seperti saklar rumah yang berbunyi klik-klik. Debounce = tunggu sampai getaran reda, baru percaya satu tekan.</p>
<p><strong>Prasyarat:</strong> FS-18 (LED GPIO 2) · FS-10 (kenal pull-up/pull-down) · FS-15 (pernah lihat pengulangan).</p>
<p><strong>Cara pakai artikel ini (urutan kerja):</strong></p>
<ol>
<li>Siapkan wiring: tombol <strong>GPIO 27 ↔ GND</strong> + LED <strong>GPIO 2</strong> (pola FS-18). Cocokkan dengan <strong>gambar utama</strong> di bawah.</li>
<li><strong>Buka Arduino IDE</strong> (bukan Laragon / terminal web).</li>
<li>Baca singkat: <code>digitalRead</code>, bounce, <code>millis</code> vs <code>delay</code>.</li>
<li>Buat sketch <code>FS19_btn_debounce</code> → <strong>Verify</strong> → <strong>Upload</strong>.</li>
<li>Tekan tombol: LED toggle <strong>sekali per tekan</strong>; Serial tetap mencetak <code>detak</code> tiap 500 ms.</li>
<li>Centang checklist 10/10 di browser.</li>
</ol>
<p><strong>Tidak perlu hari ini:</strong> PWM, sensor, Wi-Fi, Laragon, <code>php artisan</code>, interrupt rumit. Tools hari ini: <strong>Arduino IDE</strong> + <strong>Upload</strong> + ESP32 + tombol + LED + resistor 220 Ω + <strong>Serial Monitor</strong> + <strong>browser</strong>.</p>

<h2>Persiapan — buka &amp; siapkan ini dulu</h2>
<p><strong>Urutan meja kerja:</strong> wiring dulu, baru Upload — sintaks diuji di Arduino IDE; hasil dilihat di LED + Serial.</p>
<ol>
<li>Buka <strong>Arduino IDE 2.x</strong> · board <strong>ESP32 Dev Module</strong> + port.</li>
<li>Siapkan ESP32 + USB data.</li>
<li>Siapkan tombol tactile + LED + resistor 220 Ω (dari FS-18).</li>
<li>Cari label <strong>GPIO 27</strong>, <strong>GND</strong>, dan <strong>GPIO 2</strong> di silkscreen.</li>
<li>Siapkan Serial Monitor baud <strong>115200</strong>.</li>
</ol>
<p><strong>Alat yang dipakai hari ini:</strong> Arduino IDE, Upload, ESP32, USB data, tombol, LED, 220 Ω, jumper, Serial Monitor, browser.</p>
<p><strong>Tidak dipakai hari ini:</strong> Laragon, <code>php artisan</code>, DHT, multimeter (opsional), PuTTY.</p>
{$ide}
{$board}
{$kit}
{$main}

<h2>digitalRead dan INPUT_PULLUP</h2>
{$wiring}
<p><code>digitalRead(pin)</code> mengembalikan <code>HIGH</code> atau <code>LOW</code>. Dengan <code>pinMode(BTN, INPUT_PULLUP)</code>, chip memasang resistor internal ke 3,3 V — lepas = HIGH, tekan ke GND = LOW.</p>
<p>Referensi: <a href="https://docs.arduino.cc/language-reference/en/functions/digital-io/digitalread/" rel="noopener noreferrer" target="_blank">Arduino — digitalRead</a> · <a href="https://docs.arduino.cc/language-reference/en/functions/digital-io/pinmode/" rel="noopener noreferrer" target="_blank">Arduino — pinMode</a>.</p>
<p><strong>Langkah wiring cepat (cocokkan dengan gambar utama):</strong></p>
<ol>
<li>Pasang tombol melintasi <strong>parit tengah</strong> breadboard (celah memanjang di tengah papan).</li>
<li>Satu kaki tombol → <strong>GPIO 27</strong>.</li>
<li>Kaki pasangan/diagonal tombol → <strong>GND</strong>.</li>
<li>LED + 220 Ω tetap di <strong>GPIO 2</strong> seperti FS-18.</li>
<li>Jangan wiring tombol ke GPIO 0 (bisa masuk mode download).</li>
</ol>
{$fs10}

<h2>Bounce dan debounce</h2>
{$bounce}
<p>Satu tekan fisik bisa menghasilkan banyak tepi sinyal. Debounce sederhana: catat waktu <code>millis()</code> saat bacaan berubah; baru terima perubahan jika sudah stabil ≥ 50 ms.</p>

<h2>millis() vs delay()</h2>
{$millis}
<p>Di sketch hari ini, <code>detak</code> Serial tiap 500 ms memakai <code>millis</code> — jadi saat kamu menahan tombol, detak <strong>tetap jalan</strong>. Kalau pakai <code>delay</code> panjang di loop, tombol terasa “macet”.</p>

<h2>Praktik — sketch FS19_btn_debounce</h2>
<p>Tujuan: <strong>satu tekan = satu toggle LED</strong> (tanpa double-klik), dan baris <code>detak</code> tetap muncul di Serial.</p>
<ol>
<li>Arduino IDE → <strong>File → New Sketch</strong> → <strong>Simpan sebagai</strong> <code>FS19_btn_debounce</code>.</li>
<li>Ganti isi dengan kode di bawah (salin utuh).</li>
<li><strong>Verify</strong> → <strong>Upload</strong> → tunggu <em>Done uploading</em>.</li>
<li>Buka Serial Monitor baud <strong>115200</strong>.</li>
<li>Tekan tombol pelan: LED berubah sekali; Serial mencetak <code>TOMBOL: toggle</code> dan <code>detak …</code> terus berjalan. Tekan <strong>EN (7)</strong> bila perlu restart.</li>
</ol>
<pre><code class="language-cpp">// FS19_btn_debounce — Full Stack IoT FS-19
// Tombol GPIO 27 (INPUT_PULLUP) + debounce millis + toggle LED GPIO 2

const int LED_PIN = 2;
const int BTN_PIN = 27;
const unsigned long DEBOUNCE_MS = 50;
const unsigned long TICK_MS = 500;

int ledOn = LOW;
int lastStable = HIGH;   // pull-up: lepas = HIGH
int lastReading = HIGH;
unsigned long lastDebounceAt = 0;
unsigned long lastTickAt = 0;

void setup() {
  pinMode(LED_PIN, OUTPUT);
  pinMode(BTN_PIN, INPUT_PULLUP);
  digitalWrite(LED_PIN, ledOn);
  Serial.begin(115200);
  delay(300);
  Serial.println("FS19_btn_debounce siap");
}

void loop() {
  unsigned long now = millis();

  // --- detak non-blocking (bukti loop tidak beku) ---
  if (now - lastTickAt &gt;= TICK_MS) {
    lastTickAt = now;
    Serial.print("detak ");
    Serial.println(now);
  }

  // --- baca tombol + debounce ---
  int reading = digitalRead(BTN_PIN);
  if (reading != lastReading) {
    lastDebounceAt = now;
    lastReading = reading;
  }

  if ((now - lastDebounceAt) &gt;= DEBOUNCE_MS) {
    if (reading != lastStable) {
      lastStable = reading;
      // tekan = HIGH -&gt; LOW (tepi turun)
      if (lastStable == LOW) {
        ledOn = (ledOn == LOW) ? HIGH : LOW;
        digitalWrite(LED_PIN, ledOn);
        Serial.println("TOMBOL: toggle");
      }
    }
  }
}
</code></pre>
<p><strong>Cara menguji perintah di atas:</strong> uji di <strong>Arduino IDE + Upload + jari di tombol + mata ke LED</strong> (dan Serial). Sukses = satu tekan → satu toggle; <code>detak</code> tetap muncul saat tombol ditekan. Bukan perintah Laragon / web server.</p>

<h2 id="fsiot-btn-checklist">Praktik — checklist tombol + debounce</h2>
<p>Centang setiap langkah setelah kamu lakukan di meja. Target: <strong>10/10</strong>.</p>
<ul id="fsiot-btn-checklist-items">
<li>Arduino IDE sudah terbuka sebelum menulis kode</li>
<li>Board ESP32 Dev Module + port sudah dipilih</li>
<li>Tombol terhubung GPIO 27 dan GND (INPUT_PULLUP)</li>
<li>LED + 220 Ω masih di GPIO 2</li>
<li>Paham: bounce bisa bikin double-toggle</li>
<li>Paham: millis() bukan jam internet</li>
<li>Sketch disimpan sebagai FS19_btn_debounce</li>
<li>Upload berhasil — Done uploading</li>
<li>Satu tekan tombol = satu toggle LED (bukan dobel)</li>
<li>Serial detak tetap jalan saat tombol ditekan</li>
</ul>
<p><strong>Cara menguji checklist:</strong> centang di browser setelah praktik di IDE + board. Tidak perlu <code>php artisan</code>.</p>

<h2>Kesalahan yang sering terjadi</h2>
<ul>
<li><strong>Floating pin.</strong> Tanpa pull-up/pull-down, bacaan bergoyang. Pakai <code>INPUT_PULLUP</code> atau resistor eksternal (FS-10).</li>
<li><strong>Baca di loop tanpa debounce.</strong> Satu tekan bisa jadi banyak toggle.</li>
<li><strong>Pakai <code>delay</code> panjang untuk “tunggu tombol”.</strong> Loop membeku — detak dan aksi lain berhenti.</li>
<li><strong>Tombol di GPIO 0.</strong> Bisa masuk mode download. Pakai <strong>GPIO 27</strong>.</li>
<li><strong>Salah kaki tombol.</strong> Pastikan dua kaki yang terhubung internal saat ditekan (biasanya seberang parit).</li>
<li><strong>Baud Serial salah.</strong> Samakan 115200.</li>
<li><strong>Menguji di terminal web.</strong> Sketch hanya jalan di board lewat IDE Upload.</li>
</ul>

<h2>Selanjutnya</h2>
<p><strong>Intinya:</strong> kalau satu tekan = satu toggle LED dan <code>detak</code> Serial tetap hidup, FS-19 selesai — fondasi <code>millis</code> untuk non-blocking siap.</p>
<p>Lanjut ke <strong>FS-20</strong> (PWM / redupkan LED) saat modulnya terbit.</p>
<p>Daftar modul lengkap: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $ide = $this->ideFigureEn();
        $board = $this->boardFigureEn();
        $kit = $this->kitFigureEn();
        $main = $this->mainWiringFigureEn();
        $fs10 = $this->fs10RefFigureEn();
        $wiring = $this->wiringSvgEn();
        $bounce = $this->bounceSvgEn();
        $millis = $this->millisSvgEn();

        return <<<HTML
<h2>Introduction — a reliable button</h2>
<p>This is article <strong>#89 (this article)</strong> · module <strong>FS-19</strong> on the <em>Full Stack IoT Developer — From Zero</em> path (<strong>BUILDER</strong> phase). In <strong>FS-18</strong> the LED was driven from code. Today we <strong>read</strong> the real world: a button on <strong>GPIO 27</strong>, plus a first look at <code>millis()</code>.</p>
<p><strong>Analogy:</strong> a mechanical button often “chatters” for a split second (bounce) — like a light switch that click-clicks. Debounce = wait until the chatter settles, then trust one press.</p>
<p><strong>Prerequisites:</strong> FS-18 (LED on GPIO 2) · FS-10 (know pull-up/pull-down) · FS-15 (you have seen loops).</p>
<p><strong>How to use this article (work order):</strong></p>
<ol>
<li>Prepare wiring: button <strong>GPIO 27 ↔ GND</strong> + LED on <strong>GPIO 2</strong> (FS-18 pattern). Match the <strong>main figure</strong> below.</li>
<li><strong>Open Arduino IDE</strong> (not Laragon / a web terminal).</li>
<li>Skim: <code>digitalRead</code>, bounce, <code>millis</code> vs <code>delay</code>.</li>
<li>Create sketch <code>FS19_btn_debounce</code> → <strong>Verify</strong> → <strong>Upload</strong>.</li>
<li>Press the button: LED toggles <strong>once per press</strong>; Serial keeps printing <code>tick</code> every 500 ms.</li>
<li>Tick the 10/10 checklist in the browser.</li>
</ol>
<p><strong>Not needed today:</strong> PWM, sensors, Wi-Fi, Laragon, <code>php artisan</code>, fancy interrupts. Today's tools: <strong>Arduino IDE</strong> + <strong>Upload</strong> + ESP32 + button + LED + 220 Ω resistor + <strong>Serial Monitor</strong> + <strong>browser</strong>.</p>

<h2>Preparation — open &amp; gather these first</h2>
<p><strong>Desk order:</strong> wire first, then Upload — syntax is tested in Arduino IDE; results are seen on the LED + Serial.</p>
<ol>
<li>Open <strong>Arduino IDE 2.x</strong> · <strong>ESP32 Dev Module</strong> board + port.</li>
<li>Prepare the ESP32 + USB data cable.</li>
<li>Prepare a tactile button + LED + 220 Ω resistor (from FS-18).</li>
<li>Find <strong>GPIO 27</strong>, <strong>GND</strong>, and <strong>GPIO 2</strong> on the silkscreen.</li>
<li>Have Serial Monitor ready at baud <strong>115200</strong>.</li>
</ol>
<p><strong>Tools used today:</strong> Arduino IDE, Upload, ESP32, USB data, button, LED, 220 Ω, jumpers, Serial Monitor, browser.</p>
<p><strong>Not used today:</strong> Laragon, <code>php artisan</code>, DHT, multimeter (optional), PuTTY.</p>
{$ide}
{$board}
{$kit}
{$main}

<h2>digitalRead and INPUT_PULLUP</h2>
{$wiring}
<p><code>digitalRead(pin)</code> returns <code>HIGH</code> or <code>LOW</code>. With <code>pinMode(BTN, INPUT_PULLUP)</code>, the chip enables an internal resistor to 3.3 V — released = HIGH, pressed to GND = LOW.</p>
<p>References: <a href="https://docs.arduino.cc/language-reference/en/functions/digital-io/digitalread/" rel="noopener noreferrer" target="_blank">Arduino — digitalRead</a> · <a href="https://docs.arduino.cc/language-reference/en/functions/digital-io/pinmode/" rel="noopener noreferrer" target="_blank">Arduino — pinMode</a>.</p>
<p><strong>Quick wiring steps (match the main figure):</strong></p>
<ol>
<li>Place the button across the breadboard <strong>center ditch</strong> (the long gap down the middle).</li>
<li>One button leg → <strong>GPIO 27</strong>.</li>
<li>The paired/diagonal leg → <strong>GND</strong>.</li>
<li>LED + 220 Ω stay on <strong>GPIO 2</strong> like FS-18.</li>
<li>Do not wire the button to GPIO 0 (download mode risk).</li>
</ol>
{$fs10}

<h2>Bounce and debounce</h2>
{$bounce}
<p>One physical press can create many signal edges. Simple debounce: note <code>millis()</code> when the reading changes; accept the change only after it stays stable ≥ 50 ms.</p>

<h2>millis() vs delay()</h2>
{$millis}
<p>In today’s sketch, the Serial <code>tick</code> every 500 ms uses <code>millis</code> — so while you hold the button, ticks <strong>keep going</strong>. A long <code>delay</code> in the loop would make the button feel stuck.</p>

<h2>Practice — sketch FS19_btn_debounce</h2>
<p>Goal: <strong>one press = one LED toggle</strong> (no double-click), and <code>tick</code> lines keep appearing in Serial.</p>
<ol>
<li>Arduino IDE → <strong>File → New Sketch</strong> → <strong>Save as</strong> <code>FS19_btn_debounce</code>.</li>
<li>Replace the contents with the code below (copy it whole).</li>
<li><strong>Verify</strong> → <strong>Upload</strong> → wait for <em>Done uploading</em>.</li>
<li>Open Serial Monitor at baud <strong>115200</strong>.</li>
<li>Press the button gently: the LED changes once; Serial prints <code>BUTTON: toggle</code> and <code>tick …</code> keeps running. Press <strong>EN (7)</strong> if you need a restart.</li>
</ol>
<pre><code class="language-cpp">// FS19_btn_debounce — Full Stack IoT FS-19
// Button GPIO 27 (INPUT_PULLUP) + millis debounce + toggle LED GPIO 2

const int LED_PIN = 2;
const int BTN_PIN = 27;
const unsigned long DEBOUNCE_MS = 50;
const unsigned long TICK_MS = 500;

int ledOn = LOW;
int lastStable = HIGH;   // pull-up: released = HIGH
int lastReading = HIGH;
unsigned long lastDebounceAt = 0;
unsigned long lastTickAt = 0;

void setup() {
  pinMode(LED_PIN, OUTPUT);
  pinMode(BTN_PIN, INPUT_PULLUP);
  digitalWrite(LED_PIN, ledOn);
  Serial.begin(115200);
  delay(300);
  Serial.println("FS19_btn_debounce ready");
}

void loop() {
  unsigned long now = millis();

  // --- non-blocking tick (proof the loop is not frozen) ---
  if (now - lastTickAt &gt;= TICK_MS) {
    lastTickAt = now;
    Serial.print("tick ");
    Serial.println(now);
  }

  // --- read button + debounce ---
  int reading = digitalRead(BTN_PIN);
  if (reading != lastReading) {
    lastDebounceAt = now;
    lastReading = reading;
  }

  if ((now - lastDebounceAt) &gt;= DEBOUNCE_MS) {
    if (reading != lastStable) {
      lastStable = reading;
      // press = HIGH -&gt; LOW (falling edge)
      if (lastStable == LOW) {
        ledOn = (ledOn == LOW) ? HIGH : LOW;
        digitalWrite(LED_PIN, ledOn);
        Serial.println("BUTTON: toggle");
      }
    }
  }
}
</code></pre>
<p><strong>How to test the commands above:</strong> test in <strong>Arduino IDE + Upload + finger on the button + eyes on the LED</strong> (and Serial). Success = one press → one toggle; <code>tick</code> keeps appearing while you press. Not a Laragon / web-server command.</p>

<h2 id="fsiot-btn-checklist">Practice — button + debounce checklist</h2>
<p>Tick each step after you do it at the desk. Target: <strong>10/10</strong>.</p>
<ul id="fsiot-btn-checklist-items">
<li>Arduino IDE is open before writing code</li>
<li>ESP32 Dev Module board + port are selected</li>
<li>Button connects GPIO 27 and GND (INPUT_PULLUP)</li>
<li>LED + 220 Ω still on GPIO 2</li>
<li>Understood: bounce can cause double-toggles</li>
<li>Understood: millis() is not internet time</li>
<li>Sketch saved as FS19_btn_debounce</li>
<li>Upload succeeded — Done uploading</li>
<li>One button press = one LED toggle (not double)</li>
<li>Serial ticks keep running while the button is pressed</li>
</ul>
<p><strong>How to test the checklist:</strong> tick items in the browser after IDE + board practice. No <code>php artisan</code> needed.</p>

<h2>Common mistakes</h2>
<ul>
<li><strong>Floating pin.</strong> Without pull-up/pull-down, readings wander. Use <code>INPUT_PULLUP</code> or an external resistor (FS-10).</li>
<li><strong>Reading in the loop with no debounce.</strong> One press can become many toggles.</li>
<li><strong>Long <code>delay</code> to “wait for the button”.</strong> The loop freezes — ticks and other actions stop.</li>
<li><strong>Button on GPIO 0.</strong> Can enter download mode. Use <strong>GPIO 27</strong>.</li>
<li><strong>Wrong button legs.</strong> Use the pair that connects when pressed (usually across the ditch).</li>
<li><strong>Wrong Serial baud.</strong> Match 115200.</li>
<li><strong>Testing in a web terminal.</strong> The sketch only runs on the board via IDE Upload.</li>
</ul>

<h2>Next</h2>
<p><strong>In short:</strong> if one press = one LED toggle and Serial <code>tick</code> stays alive, FS-19 is done — your <code>millis</code> foundation for non-blocking work is ready.</p>
<p>Continue to <strong>FS-20</strong> (PWM / dim the LED) when that module publishes.</p>
<p>Full module list: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }
}
