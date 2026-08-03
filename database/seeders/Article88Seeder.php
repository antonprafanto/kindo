<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article88Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first()
            ?? Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $iotCat) {
            throw new \RuntimeException('User atau kategori iot-smart-device/esp32-arduino tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-led-dari-kode';

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
                'title'              => 'Nyalakan LED dari kode — pinMode & digitalWrite',
                'title_en'           => 'Turn on an LED from code — pinMode & digitalWrite',
                'excerpt'            => 'FS-18 / #88: GPIO sebagai saklar. Uji di Arduino IDE: pinMode(OUTPUT) + digitalWrite di GPIO 2, LED kedip 1 detik.',
                'excerpt_en'         => 'FS-18 / #88: GPIO as a switch. Test in Arduino IDE: pinMode(OUTPUT) + digitalWrite on GPIO 2, LED blinks 1 second.',
                'body'               => $this->body(),
                'body_en'            => $this->bodyEn(),
                'status'             => 'draft',
                'is_featured'        => false,
                'published_at'       => null,
                'seo_title'          => 'Nyalakan LED dari kode — Full Stack IoT #88',
                'seo_title_en'       => 'Turn on an LED from code — Full Stack IoT #88',
                'seo_description'    => 'Belajar pinMode dan digitalWrite di ESP32 GPIO 2. Modul FS-18 jalur Full Stack IoT.',
                'seo_description_en' => 'Learn pinMode and digitalWrite on ESP32 GPIO 2. Full Stack IoT FS-18 module.',
            ]
        );

        if ($article->published_at !== null || $article->status !== 'draft') {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['fullstack-iot', 'iot', 'esp32'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel #88 / FS-18 tersimpan sebagai DRAFT: '.$article->title);
    }

    private function ideFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/fs11-ide-overview-cite.png" width="1280" height="720" alt="Arduino IDE 2 — toolbar Verify, Upload, dan ikon Serial Monitor" loading="eager" style="width:100%;height:auto;max-height:420px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Arduino IDE 2</strong> — tempat menguji sintaks hari ini. Kiri atas: <strong>Verify</strong> (✓) lalu <strong>Upload</strong> (→). Kanan atas: buka <strong>Serial Monitor</strong> untuk membaca <code>LED ON</code> / <code>LED OFF</code>. Board: <strong>ESP32 Dev Module</strong>.
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:Ide-2-overview.png" rel="noopener noreferrer" target="_blank">Arduino IDE 2 overview</a> · Wikimedia Commons (CC BY-SA 3.0) · asal dokumen Arduino. Panduan Serial: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-serial-monitor/" rel="noopener noreferrer" target="_blank">Arduino Docs — Serial Monitor (IDE 2)</a>.
  </figcaption>
</figure>
HTML;
    }

    private function ideFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/fs11-ide-overview-cite.png" width="1280" height="720" alt="Arduino IDE 2 — Verify, Upload toolbar and Serial Monitor icon" loading="eager" style="width:100%;height:auto;max-height:420px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Arduino IDE 2</strong> — where today’s syntax is tested. Top-left: <strong>Verify</strong> (✓) then <strong>Upload</strong> (→). Top-right: open <strong>Serial Monitor</strong> to read <code>LED ON</code> / <code>LED OFF</code>. Board: <strong>ESP32 Dev Module</strong>.
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:Ide-2-overview.png" rel="noopener noreferrer" target="_blank">Arduino IDE 2 overview</a> · Wikimedia Commons (CC BY-SA 3.0) · from Arduino docs. Serial guide: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-serial-monitor/" rel="noopener noreferrer" target="_blank">Arduino Docs — Serial Monitor (IDE 2)</a>.
  </figcaption>
</figure>
HTML;
    }

    private function boardFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/esp32-devkitc-overview.jpg" width="1200" height="800" alt="ESP32-DevKitC — USB (6) dan tombol EN (7)" loading="eager" style="width:100%;height:auto;max-height:360px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>ESP32-DevKitC</strong> — colok <strong>kabel USB data</strong> di label <strong>(6)</strong>. Tombol <strong>EN (7)</strong> = reset. Pin belajar LED = <strong>GPIO 2</strong> (sudah dikunci di peta pin FS-17).
    <br>Sumber gambar: <a href="https://docs.espressif.com/projects/esp-dev-kits/en/latest/esp32/esp32-devkitc/user_guide.html" rel="noopener noreferrer" target="_blank">Espressif — ESP32-DevKitC user guide</a>.
  </figcaption>
</figure>
HTML;
    }

    private function boardFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/esp32-devkitc-overview.jpg" width="1200" height="800" alt="ESP32-DevKitC — USB (6) and EN button (7)" loading="eager" style="width:100%;height:auto;max-height:360px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>ESP32-DevKitC</strong> — plug a <strong>USB data cable</strong> at label <strong>(6)</strong>. <strong>EN (7)</strong> resets the board. Practice LED pin = <strong>GPIO 2</strong> (locked in the FS-17 pin map).
    <br>Image source: <a href="https://docs.espressif.com/projects/esp-dev-kits/en/latest/esp32/esp32-devkitc/user_guide.html" rel="noopener noreferrer" target="_blank">Espressif — ESP32-DevKitC user guide</a>.
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
      <img src="/images/fsiot/kit-led-5mm.jpg" width="900" height="900" alt="LED 5 mm — kaki panjang = anode (+)" loading="eager" style="display:block;width:100%;height:auto;aspect-ratio:1/1;max-height:280px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem;margin:0 auto">
      <p style="font-size:0.8rem;margin:0.35rem 0 0;color:#4A5568;text-align:center"><strong>LED 5 mm</strong> — kaki panjang = +</p>
    </div>
    <div>
      <img src="/images/fsiot/kit-resistor-220ohm.jpg" width="1200" height="800" alt="Resistor 220 ohm untuk LED" loading="eager" style="display:block;width:100%;height:auto;aspect-ratio:1/1;max-height:280px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem;margin:0 auto">
      <p style="font-size:0.8rem;margin:0.35rem 0 0;color:#4A5568;text-align:center"><strong>Resistor 220 Ω</strong> — wajib dipasang</p>
    </div>
  </div>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Komponen hari ini:</strong> LED 5 mm + resistor <strong>220 Ω</strong>. Jangan wiring LED langsung tanpa resistor.
    <br>Sumber gambar: foto kit Koding Indonesia (FS-09 / FS-18). Referensi pin board: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/boards/ESP32-DevKitC-1.html" rel="noopener noreferrer" target="_blank">Espressif — ESP32-DevKitC-1</a>.
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
      <img src="/images/fsiot/kit-led-5mm.jpg" width="900" height="900" alt="5 mm LED — long lead = anode (+)" loading="eager" style="display:block;width:100%;height:auto;aspect-ratio:1/1;max-height:280px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem;margin:0 auto">
      <p style="font-size:0.8rem;margin:0.35rem 0 0;color:#4A5568;text-align:center"><strong>5 mm LED</strong> — long lead = +</p>
    </div>
    <div>
      <img src="/images/fsiot/kit-resistor-220ohm.jpg" width="1200" height="800" alt="220 ohm resistor for an LED" loading="eager" style="display:block;width:100%;height:auto;aspect-ratio:1/1;max-height:280px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem;margin:0 auto">
      <p style="font-size:0.8rem;margin:0.35rem 0 0;color:#4A5568;text-align:center"><strong>220 Ω resistor</strong> — required</p>
    </div>
  </div>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Parts today:</strong> 5 mm LED + <strong>220 Ω</strong> resistor. Never wire an LED with no resistor.
    <br>Image source: Koding Indonesia kit photos (FS-09 / FS-18). Board pin reference: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/boards/ESP32-DevKitC-1.html" rel="noopener noreferrer" target="_blank">Espressif — ESP32-DevKitC-1</a>.
  </figcaption>
</figure>
HTML;
    }

    private function polaritySvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Polaritas LED: kaki panjang anode plus, kaki pendek cathode minus" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 190" width="100%" height="auto" style="display:block;max-height:220px">
    <text x="430" y="28" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#1a1a1a">Baca polaritas LED sebelum menusuk breadboard</text>
    <ellipse cx="220" cy="95" rx="48" ry="58" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
    <line x1="200" y1="150" x2="200" y2="175" stroke="#333" stroke-width="3"/>
    <line x1="240" y1="150" x2="240" y2="168" stroke="#333" stroke-width="3"/>
    <text x="200" y="72" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#0D47A1">LED</text>
    <text x="120" y="172" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" font-weight="700" fill="#2E7D32">panjang = +</text>
    <text x="300" y="172" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" font-weight="700" fill="#C62828">pendek = −</text>
    <text x="430" y="100" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="22" fill="#1a1a1a">→</text>
    <rect x="500" y="55" width="300" height="100" rx="8" fill="#FFF8E1" stroke="#F9A825" stroke-width="2.5"/>
    <text x="650" y="90" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" font-weight="700" fill="#F57F17">Ingat singkat</text>
    <text x="650" y="118" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#333">kaki panjang ke sisi GPIO / resistor</text>
    <text x="650" y="140" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#333">kaki pendek ke GND</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.35rem;color:#4A5568;">
    <strong>Intinya:</strong> salah polaritas = LED diam meski kode sudah benar. Sumber gambar: diagram buatan Koding Indonesia (FS-18).
  </figcaption>
</figure>
SVG;
    }

    private function polaritySvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="LED polarity: long lead anode plus, short lead cathode minus" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 190" width="100%" height="auto" style="display:block;max-height:220px">
    <text x="430" y="28" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#1a1a1a">Read LED polarity before plugging the breadboard</text>
    <ellipse cx="220" cy="95" rx="48" ry="58" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
    <line x1="200" y1="150" x2="200" y2="175" stroke="#333" stroke-width="3"/>
    <line x1="240" y1="150" x2="240" y2="168" stroke="#333" stroke-width="3"/>
    <text x="200" y="72" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#0D47A1">LED</text>
    <text x="120" y="172" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" font-weight="700" fill="#2E7D32">long = +</text>
    <text x="300" y="172" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" font-weight="700" fill="#C62828">short = −</text>
    <text x="430" y="100" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="22" fill="#1a1a1a">→</text>
    <rect x="500" y="55" width="300" height="100" rx="8" fill="#FFF8E1" stroke="#F9A825" stroke-width="2.5"/>
    <text x="650" y="90" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" font-weight="700" fill="#F57F17">Quick reminder</text>
    <text x="650" y="118" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#333">long lead toward GPIO / resistor</text>
    <text x="650" y="140" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#333">short lead to GND</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.35rem;color:#4A5568;">
    <strong>In short:</strong> wrong polarity = a dark LED even when the code is right. Image source: diagram by Koding Indonesia (FS-18).
  </figcaption>
</figure>
SVG;
    }

    private function breadboardPhotoId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/fs18-led-gpio2-breadboard.png" width="1299" height="799" alt="Gambar utama — ESP32 di breadboard: GPIO 2 ke resistor ke LED ke GND" loading="eager" style="width:100%;height:auto;max-height:480px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Gambar utama — wiring GPIO 2.</strong> Ikuti alur: label <strong>IO2 / GPIO 2</strong> → resistor → kaki panjang LED (+) → kaki pendek LED (−) → <strong>GND</strong>. Cocokkan <em>label silkscreen</em> di board kamu (clone boleh beda bentuk, nomor pin yang dicari sama).
    <br>Catatan resistor: di foto latihan boleh 220 Ω–1 kΩ; di artikel ini kita pakai <strong>220 Ω</strong> (aman untuk LED 5 mm). Sumber gambar: diagram rangkaian Koding Indonesia (FS-18).
  </figcaption>
</figure>
HTML;
    }

    private function breadboardPhotoEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/fs18-led-gpio2-breadboard.png" width="1299" height="799" alt="Main figure — ESP32 on a breadboard: GPIO 2 to resistor to LED to GND" loading="eager" style="width:100%;height:auto;max-height:480px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Main figure — GPIO 2 wiring.</strong> Follow the path: <strong>IO2 / GPIO 2</strong> label → resistor → LED long lead (+) → LED short lead (−) → <strong>GND</strong>. Match the <em>silkscreen labels</em> on your board (clones may look different; the pin names you hunt for are the same).
    <br>Resistor note: practice photos may show 220 Ω–1 kΩ; this article standardizes on <strong>220 Ω</strong> (safe for a 5 mm LED). Image source: Koding Indonesia wiring diagram (FS-18).
  </figcaption>
</figure>
HTML;
    }

    private function switchSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="GPIO sebagai saklar dinding: pinMode mengatur mode, digitalWrite mengatur ON atau OFF" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 175" width="100%" height="auto" style="display:block;max-height:200px">
    <text x="430" y="28" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#1a1a1a">GPIO = saklar dinding untuk LED</text>
    <rect x="40" y="50" width="220" height="88" rx="8" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
    <text x="150" y="82" text-anchor="middle" font-family="Consolas,monospace" font-size="15" font-weight="700" fill="#1B5E20">pinMode</text>
    <text x="150" y="108" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#333">atur pin jadi OUTPUT</text>
    <text x="280" y="98" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="22" fill="#1a1a1a">→</text>
    <rect x="320" y="50" width="220" height="88" rx="8" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
    <text x="430" y="82" text-anchor="middle" font-family="Consolas,monospace" font-size="15" font-weight="700" fill="#0D47A1">digitalWrite</text>
    <text x="430" y="108" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#333">HIGH = nyala · LOW = mati</text>
    <text x="560" y="98" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="22" fill="#1a1a1a">→</text>
    <rect x="600" y="50" width="220" height="88" rx="8" fill="#FFF8E1" stroke="#F9A825" stroke-width="2.5"/>
    <text x="710" y="82" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="15" font-weight="700" fill="#F57F17">LED di GPIO 2</text>
    <text x="710" y="108" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#333">terlihat berkedip</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.35rem;color:#4A5568;">
    <strong>Intinya:</strong> tanpa <code>pinMode(OUTPUT)</code>, saklar belum “siap dipakai”. Sumber gambar: diagram buatan Koding Indonesia (FS-18).
  </figcaption>
</figure>
SVG;
    }

    private function switchSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="GPIO as a wall switch: pinMode sets the mode, digitalWrite sets ON or OFF" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 175" width="100%" height="auto" style="display:block;max-height:200px">
    <text x="430" y="28" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#1a1a1a">GPIO = a wall switch for the LED</text>
    <rect x="40" y="50" width="220" height="88" rx="8" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
    <text x="150" y="82" text-anchor="middle" font-family="Consolas,monospace" font-size="15" font-weight="700" fill="#1B5E20">pinMode</text>
    <text x="150" y="108" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#333">set the pin as OUTPUT</text>
    <text x="280" y="98" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="22" fill="#1a1a1a">→</text>
    <rect x="320" y="50" width="220" height="88" rx="8" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
    <text x="430" y="82" text-anchor="middle" font-family="Consolas,monospace" font-size="15" font-weight="700" fill="#0D47A1">digitalWrite</text>
    <text x="430" y="108" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#333">HIGH = on · LOW = off</text>
    <text x="560" y="98" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="22" fill="#1a1a1a">→</text>
    <rect x="600" y="50" width="220" height="88" rx="8" fill="#FFF8E1" stroke="#F9A825" stroke-width="2.5"/>
    <text x="710" y="82" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="15" font-weight="700" fill="#F57F17">LED on GPIO 2</text>
    <text x="710" y="108" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#333">you see it blink</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.35rem;color:#4A5568;">
    <strong>In short:</strong> without <code>pinMode(OUTPUT)</code>, the switch is not ready. Image source: diagram by Koding Indonesia (FS-18).
  </figcaption>
</figure>
SVG;
    }

    private function wiringSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Alur wiring GPIO 2 ke resistor 220 ohm ke LED ke GND" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 200" width="100%" height="auto" style="display:block;max-height:230px">
    <text x="430" y="28" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#1a1a1a">Wiring aman — GPIO 2 → 220 Ω → LED → GND</text>
    <rect x="30" y="55" width="150" height="70" rx="8" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
    <text x="105" y="85" text-anchor="middle" font-family="Consolas,monospace" font-size="16" font-weight="700" fill="#0D47A1">GPIO 2</text>
    <text x="105" y="108" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#333">output kode</text>
    <text x="200" y="92" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="22" fill="#1a1a1a">→</text>
    <rect x="230" y="55" width="150" height="70" rx="8" fill="#FFF3E0" stroke="#EF6C00" stroke-width="2.5"/>
    <text x="305" y="85" text-anchor="middle" font-family="Consolas,monospace" font-size="16" font-weight="700" fill="#E65100">220 Ω</text>
    <text x="305" y="108" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#333">batas arus</text>
    <text x="400" y="92" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="22" fill="#1a1a1a">→</text>
    <rect x="430" y="55" width="150" height="70" rx="8" fill="#FFEBEE" stroke="#C62828" stroke-width="2.5"/>
    <text x="505" y="85" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#B71C1C">LED</text>
    <text x="505" y="108" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#333">+ panjang dulu</text>
    <text x="600" y="92" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="22" fill="#1a1a1a">→</text>
    <rect x="630" y="55" width="200" height="70" rx="8" fill="#ECEFF1" stroke="#455A64" stroke-width="2.5"/>
    <text x="730" y="85" text-anchor="middle" font-family="Consolas,monospace" font-size="16" font-weight="700" fill="#263238">GND</text>
    <text x="730" y="108" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#333">tanah bersama</text>
    <text x="430" y="160" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#444">Beda FS-09: dulu LED ke 3V3 (tanpa kode). Sekarang saklarnya GPIO 2 dari sketch.</text>
    <text x="430" y="182" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#666">Catatan: beberapa board punya LED onboard di GPIO 2 — boleh ikut berkedip; wiring luar tetap dilatih.</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.35rem;color:#4A5568;">
    <strong>Intinya:</strong> cari label <strong>GPIO 2</strong> dan <strong>GND</strong> di silkscreen (peta FS-17). Sumber gambar: diagram buatan Koding Indonesia (FS-18).
  </figcaption>
</figure>
SVG;
    }

    private function wiringSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Wiring path GPIO 2 to 220 ohm resistor to LED to GND" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 200" width="100%" height="auto" style="display:block;max-height:230px">
    <text x="430" y="28" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#1a1a1a">Safe wiring — GPIO 2 → 220 Ω → LED → GND</text>
    <rect x="30" y="55" width="150" height="70" rx="8" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
    <text x="105" y="85" text-anchor="middle" font-family="Consolas,monospace" font-size="16" font-weight="700" fill="#0D47A1">GPIO 2</text>
    <text x="105" y="108" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#333">code output</text>
    <text x="200" y="92" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="22" fill="#1a1a1a">→</text>
    <rect x="230" y="55" width="150" height="70" rx="8" fill="#FFF3E0" stroke="#EF6C00" stroke-width="2.5"/>
    <text x="305" y="85" text-anchor="middle" font-family="Consolas,monospace" font-size="16" font-weight="700" fill="#E65100">220 Ω</text>
    <text x="305" y="108" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#333">limits current</text>
    <text x="400" y="92" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="22" fill="#1a1a1a">→</text>
    <rect x="430" y="55" width="150" height="70" rx="8" fill="#FFEBEE" stroke="#C62828" stroke-width="2.5"/>
    <text x="505" y="85" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#B71C1C">LED</text>
    <text x="505" y="108" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#333">long lead = +</text>
    <text x="600" y="92" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="22" fill="#1a1a1a">→</text>
    <rect x="630" y="55" width="200" height="70" rx="8" fill="#ECEFF1" stroke="#455A64" stroke-width="2.5"/>
    <text x="730" y="85" text-anchor="middle" font-family="Consolas,monospace" font-size="16" font-weight="700" fill="#263238">GND</text>
    <text x="730" y="108" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#333">common ground</text>
    <text x="430" y="160" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#444">Unlike FS-09: then LED on 3V3 (no code). Now the switch is GPIO 2 from the sketch.</text>
    <text x="430" y="182" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#666">Note: some boards have an onboard LED on GPIO 2 — it may blink too; still practice the external wire.</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.35rem;color:#4A5568;">
    <strong>In short:</strong> find <strong>GPIO 2</strong> and <strong>GND</strong> on the silkscreen (FS-17 map). Image source: diagram by Koding Indonesia (FS-18).
  </figcaption>
</figure>
SVG;
    }

    private function timelineSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Timeline LED ON satu detik lalu OFF satu detik berulang" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 175" width="100%" height="auto" style="display:block;max-height:200px">
    <text x="430" y="26" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#1a1a1a">Hasil terlihat: blink 1 detik ON / 1 detik OFF</text>
    <rect x="60" y="55" width="320" height="55" rx="6" fill="#C8E6C9" stroke="#2E7D32" stroke-width="2"/>
    <text x="220" y="88" text-anchor="middle" font-family="Consolas,monospace" font-size="15" font-weight="700" fill="#1B5E20">HIGH · LED ON · delay(1000)</text>
    <rect x="400" y="55" width="320" height="55" rx="6" fill="#FFCDD2" stroke="#C62828" stroke-width="2"/>
    <text x="560" y="88" text-anchor="middle" font-family="Consolas,monospace" font-size="15" font-weight="700" fill="#B71C1C">LOW · LED OFF · delay(1000)</text>
    <text x="740" y="88" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="18" fill="#1a1a1a">↻</text>
    <text x="430" y="145" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#444">Serial ikut mencetak LED ON / LED OFF agar mudah dicek di Monitor.</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.35rem;color:#4A5568;">
    <strong>Intinya:</strong> sukses = mata melihat kedip teratur, bukan “sekali nyala lalu diam”. Sumber gambar: diagram buatan Koding Indonesia (FS-18).
  </figcaption>
</figure>
SVG;
    }

    private function timelineSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Timeline LED ON one second then OFF one second repeating" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 175" width="100%" height="auto" style="display:block;max-height:200px">
    <text x="430" y="26" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#1a1a1a">Visible result: blink 1 s ON / 1 s OFF</text>
    <rect x="60" y="55" width="320" height="55" rx="6" fill="#C8E6C9" stroke="#2E7D32" stroke-width="2"/>
    <text x="220" y="88" text-anchor="middle" font-family="Consolas,monospace" font-size="15" font-weight="700" fill="#1B5E20">HIGH · LED ON · delay(1000)</text>
    <rect x="400" y="55" width="320" height="55" rx="6" fill="#FFCDD2" stroke="#C62828" stroke-width="2"/>
    <text x="560" y="88" text-anchor="middle" font-family="Consolas,monospace" font-size="15" font-weight="700" fill="#B71C1C">LOW · LED OFF · delay(1000)</text>
    <text x="740" y="88" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="18" fill="#1a1a1a">↻</text>
    <text x="430" y="145" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#444">Serial also prints LED ON / LED OFF so you can check the Monitor.</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.35rem;color:#4A5568;">
    <strong>In short:</strong> success = your eyes see a steady blink, not “on once then stuck”. Image source: diagram by Koding Indonesia (FS-18).
  </figcaption>
</figure>
SVG;
    }

    private function body(): string
    {
        $ide = $this->ideFigureId();
        $board = $this->boardFigureId();
        $kit = $this->kitFigureId();
        $polarity = $this->polaritySvgId();
        $switch = $this->switchSvgId();
        $wiring = $this->wiringSvgId();
        $breadboard = $this->breadboardPhotoId();
        $timeline = $this->timelineSvgId();

        return <<<HTML
<h2>Pendahuluan — GPIO jadi saklar</h2>
<p>Artikel ini adalah <strong>#88 (ini)</strong> · modul <strong>FS-18</strong> di jalur <em>Full Stack IoT Developer — Dari Nol</em>. Fase <strong>BUILDER</strong> sudah dibuka di <strong>FS-17</strong> (peta pin). Hari ini GPIO dipakai sebagai <strong>saklar</strong>: LED dikendalikan dari kode.</p>
<p><strong>Analogi:</strong> <code>pinMode</code> = memasang saklar di dinding; <code>digitalWrite</code> = menekan ON/OFF. Tanpa memasang saklar dulu, tombol tidak berguna.</p>
<p><strong>Prasyarat:</strong> FS-17 (kenal GPIO 2 / GND) · FS-09 (pernah wiring LED + resistor di breadboard).</p>
<p><strong>Cara pakai artikel ini (urutan kerja):</strong></p>
<ol>
<li>Siapkan <strong>board + breadboard</strong>: LED + resistor 220 Ω ke <strong>GPIO 2</strong> dan <strong>GND</strong>.</li>
<li><strong>Buka Arduino IDE</strong> (bukan Laragon / terminal web).</li>
<li>Baca singkat: <code>pinMode(OUTPUT)</code> dan <code>digitalWrite</code>.</li>
<li>Buat sketch <code>FS18_blink</code> → <strong>Verify</strong> → <strong>Upload</strong>.</li>
<li>Lihat LED berkedip 1 detik · buka <strong>Serial Monitor</strong> (baud 115200) untuk baris <code>LED ON</code> / <code>LED OFF</code>.</li>
<li>Centang checklist 10/10 di browser.</li>
</ol>
<p><strong>Tidak perlu hari ini:</strong> tombol, PWM, sensor, Wi-Fi, Laragon, <code>php artisan</code>. Tools hari ini: <strong>Arduino IDE</strong> + <strong>Upload</strong> + ESP32 + LED + resistor 220 Ω + breadboard/jumper + <strong>Serial Monitor</strong> + <strong>browser</strong> (checklist).</p>

<h2>Persiapan — buka &amp; siapkan ini dulu</h2>
<p><strong>Urutan meja kerja:</strong> wiring dulu, baru Upload — sintaks diuji di Arduino IDE, hasil dilihat di LED (dan Serial).</p>
<ol>
<li>Buka <strong>Arduino IDE 2.x</strong> · board <strong>ESP32 Dev Module</strong> + port COM/tty.</li>
<li>Siapkan ESP32 + kabel USB data.</li>
<li>Siapkan LED 5 mm + resistor <strong>220 Ω</strong> + breadboard + jumper.</li>
<li>Cari label <strong>GPIO 2</strong> dan <strong>GND</strong> di silkscreen (ingat peta FS-17).</li>
<li>Siapkan Serial Monitor (baud 115200) di kanan atas IDE.</li>
</ol>
<p><strong>Alat yang dipakai hari ini:</strong> Arduino IDE, Upload, ESP32, USB data, LED, resistor 220 Ω, breadboard, jumper, Serial Monitor, browser.</p>
<p><strong>Tidak dipakai hari ini:</strong> Laragon, <code>php artisan</code>, tombol, DHT, multimeter (opsional nanti), PuTTY.</p>
{$ide}
{$board}
{$kit}
{$polarity}

<h2>GPIO sebagai saklar</h2>
{$switch}
<p><code>pinMode(pin, OUTPUT)</code> memberitahu chip: “pin ini akan mengeluarkan sinyal.” <code>digitalWrite(pin, HIGH)</code> = hampir 3,3 V (nyala). <code>digitalWrite(pin, LOW)</code> = 0 V (mati).</p>
<p>Referensi resmi: <a href="https://docs.arduino.cc/language-reference/en/functions/digital-io/pinmode/" rel="noopener noreferrer" target="_blank">Arduino — pinMode</a> · <a href="https://docs.arduino.cc/language-reference/en/functions/digital-io/digitalwrite/" rel="noopener noreferrer" target="_blank">Arduino — digitalWrite</a>.</p>

<h2>Wiring LED ke GPIO 2</h2>
{$wiring}
{$breadboard}
<p><strong>Langkah cepat (cocokkan dengan gambar utama di atas):</strong></p>
<ol>
<li>Anode LED (kaki panjang) → salah satu kaki resistor 220 Ω.</li>
<li>Kaki resistor lainnya → jumper ke <strong>GPIO 2</strong> (label <code>IO2</code> di banyak board) di board.</li>
<li>Cathode LED (kaki pendek) → jumper ke <strong>GND</strong>.</li>
<li>Jangan sambungkan LED ke 5V. Level logika ESP32 = 3,3 V.</li>
</ol>
<p>Kalau LED tidak menyala setelah Upload: cek polaritas LED, cek GPIO 2 (bukan IO6–IO11), cek resistor terpasang.</p>

<h2>Praktik — sketch FS18_blink</h2>
{$timeline}
<p>Tujuan: LED di GPIO 2 berkedip <strong>1 detik nyala / 1 detik mati</strong>, terkendali kode — bukan hanya dari 3V3 seperti FS-09.</p>
<ol>
<li>Arduino IDE → <strong>File → New Sketch</strong> → <strong>Simpan sebagai</strong> (<em>Save As</em>) <code>FS18_blink</code>.</li>
<li>Ganti isi dengan kode di bawah (salin utuh).</li>
<li><strong>Verify</strong> (✓) → <strong>Upload</strong> (→) → tunggu <em>Done uploading</em>.</li>
<li>Lihat LED berkedip. Buka <strong>Serial Monitor</strong> → baud <strong>115200</strong> → baris <code>LED ON</code> / <code>LED OFF</code> bergantian.</li>
<li>Jika perlu, tekan tombol <strong>EN (7)</strong> untuk restart sketch.</li>
</ol>
<pre><code class="language-cpp">// FS18_blink — Full Stack IoT FS-18
// GPIO 2 sebagai saklar LED (pinMode + digitalWrite)

const int LED_PIN = 2; // tabel global FSIOT: LED belajar = GPIO 2

void setup() {
  pinMode(LED_PIN, OUTPUT);
  Serial.begin(115200);
  delay(500);
  Serial.println("FS18_blink siap");
}

void loop() {
  digitalWrite(LED_PIN, HIGH); // nyala
  Serial.println("LED ON");
  delay(1000);

  digitalWrite(LED_PIN, LOW); // mati
  Serial.println("LED OFF");
  delay(1000);
}
</code></pre>
<p><strong>Cara menguji perintah di atas:</strong> uji di <strong>Arduino IDE + Upload + mata ke LED</strong> (dan Serial Monitor). Sukses = kedip teratur 1 detik + baris ON/OFF di baud 115200. Bukan perintah Laragon / web server.</p>

<h2 id="fsiot-blink-checklist">Praktik — checklist LED dari kode</h2>
<p>Centang setiap langkah setelah kamu lakukan di meja. Target: <strong>10/10</strong>. Ada checklist interaktif di bawah.</p>
<ul id="fsiot-blink-checklist-items">
<li>Arduino IDE sudah terbuka sebelum menulis kode</li>
<li>Board ESP32 Dev Module + port sudah dipilih</li>
<li>LED + resistor 220 Ω terhubung GPIO 2 dan GND</li>
<li>Paham: pinMode(OUTPUT) wajib sebelum digitalWrite</li>
<li>Paham: HIGH = nyala, LOW = mati</li>
<li>Sketch disimpan sebagai FS18_blink</li>
<li>Upload berhasil — Done uploading</li>
<li>LED berkedip kira-kira 1 detik ON / 1 detik OFF</li>
<li>Serial Monitor baud 115200 menampilkan LED ON / LED OFF</li>
<li>Sadar: lupa pinMode atau tanpa resistor = kesalahan umum</li>
</ul>
<p><strong>Cara menguji checklist:</strong> centang di browser setelah praktik di IDE + board. Tidak perlu <code>php artisan</code>.</p>

<h2>Kesalahan yang sering terjadi</h2>
<ul>
<li><strong>Lupa <code>pinMode(OUTPUT)</code>.</strong> Tanpa itu, <code>digitalWrite</code> sering tidak andal. Tulis di <code>setup()</code>.</li>
<li><strong>LED tanpa resistor.</strong> Bisa merusak LED/pin. Selalu 220 Ω (atau nilai dekat yang aman).</li>
<li><strong>Salah polaritas LED.</strong> Kaki panjang = anode (+) mengarah ke sisi GPIO/resistor.</li>
<li><strong>Wiring ke IO6–IO11.</strong> Terlarang (flash). Tetap di GPIO 2.</li>
<li><strong>LED ke 5V.</strong> GPIO ESP32 level 3,3 V — ikuti jalur GPIO 2 → resistor → LED → GND.</li>
<li><strong>Baud Serial salah.</strong> Samakan 115200 di kode dan Monitor.</li>
<li><strong>Menguji di terminal web.</strong> Sketch hanya jalan di board lewat IDE Upload.</li>
</ul>

<h2>Selanjutnya</h2>
<p><strong>Intinya:</strong> kalau LED di GPIO 2 berkedip terkendali <code>pinMode</code> + <code>digitalWrite</code>, FS-18 selesai — saklar digital pertama di fase BUILDER.</p>
<p>Lanjut ke <strong>FS-19</strong> (tombol + debounce) saat modulnya terbit. Di sana kita baca input, bukan hanya menulis output.</p>
<p>Daftar modul lengkap: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $ide = $this->ideFigureEn();
        $board = $this->boardFigureEn();
        $kit = $this->kitFigureEn();
        $polarity = $this->polaritySvgEn();
        $switch = $this->switchSvgEn();
        $wiring = $this->wiringSvgEn();
        $breadboard = $this->breadboardPhotoEn();
        $timeline = $this->timelineSvgEn();

        return <<<HTML
<h2>Introduction — GPIO as a switch</h2>
<p>This is article <strong>#88 (this article)</strong> · module <strong>FS-18</strong> on the <em>Full Stack IoT Developer — From Zero</em> path. The <strong>BUILDER</strong> phase opened in <strong>FS-17</strong> (pin map). Today GPIO becomes a <strong>switch</strong>: the LED is controlled from code.</p>
<p><strong>Analogy:</strong> <code>pinMode</code> = mounting a wall switch; <code>digitalWrite</code> = pressing ON/OFF. Without mounting the switch first, the button does nothing useful.</p>
<p><strong>Prerequisites:</strong> FS-17 (know GPIO 2 / GND) · FS-09 (you have wired an LED + resistor on a breadboard).</p>
<p><strong>How to use this article (work order):</strong></p>
<ol>
<li>Prepare the <strong>board + breadboard</strong>: LED + 220 Ω resistor to <strong>GPIO 2</strong> and <strong>GND</strong>.</li>
<li><strong>Open Arduino IDE</strong> (not Laragon / a web terminal).</li>
<li>Skim: <code>pinMode(OUTPUT)</code> and <code>digitalWrite</code>.</li>
<li>Create sketch <code>FS18_blink</code> → <strong>Verify</strong> → <strong>Upload</strong>.</li>
<li>Watch the LED blink every 1 second · open <strong>Serial Monitor</strong> (baud 115200) for <code>LED ON</code> / <code>LED OFF</code> lines.</li>
<li>Tick the 10/10 checklist in the browser.</li>
</ol>
<p><strong>Not needed today:</strong> buttons, PWM, sensors, Wi-Fi, Laragon, <code>php artisan</code>. Today's tools: <strong>Arduino IDE</strong> + <strong>Upload</strong> + ESP32 + LED + 220 Ω resistor + breadboard/jumpers + <strong>Serial Monitor</strong> + <strong>browser</strong> (checklist).</p>

<h2>Preparation — open &amp; gather these first</h2>
<p><strong>Desk order:</strong> wire first, then Upload — syntax is tested in Arduino IDE; the result is seen on the LED (and Serial).</p>
<ol>
<li>Open <strong>Arduino IDE 2.x</strong> · <strong>ESP32 Dev Module</strong> board + COM/tty port.</li>
<li>Prepare the ESP32 + USB data cable.</li>
<li>Prepare a 5 mm LED + <strong>220 Ω</strong> resistor + breadboard + jumpers.</li>
<li>Find <strong>GPIO 2</strong> and <strong>GND</strong> on the silkscreen (remember the FS-17 map).</li>
<li>Have Serial Monitor ready (baud 115200) — top-right in the IDE.</li>
</ol>
<p><strong>Tools used today:</strong> Arduino IDE, Upload, ESP32, USB data, LED, 220 Ω resistor, breadboard, jumpers, Serial Monitor, browser.</p>
<p><strong>Not used today:</strong> Laragon, <code>php artisan</code>, buttons, DHT, multimeter (optional later), PuTTY.</p>
{$ide}
{$board}
{$kit}
{$polarity}

<h2>GPIO as a switch</h2>
{$switch}
<p><code>pinMode(pin, OUTPUT)</code> tells the chip: “this pin will drive a signal.” <code>digitalWrite(pin, HIGH)</code> ≈ 3.3 V (on). <code>digitalWrite(pin, LOW)</code> = 0 V (off).</p>
<p>Official references: <a href="https://docs.arduino.cc/language-reference/en/functions/digital-io/pinmode/" rel="noopener noreferrer" target="_blank">Arduino — pinMode</a> · <a href="https://docs.arduino.cc/language-reference/en/functions/digital-io/digitalwrite/" rel="noopener noreferrer" target="_blank">Arduino — digitalWrite</a>.</p>

<h2>Wire the LED to GPIO 2</h2>
{$wiring}
{$breadboard}
<p><strong>Quick steps (match the main figure above):</strong></p>
<ol>
<li>LED anode (long lead) → one leg of the 220 Ω resistor.</li>
<li>Other resistor leg → jumper to <strong>GPIO 2</strong> (often labeled <code>IO2</code>) on the board.</li>
<li>LED cathode (short lead) → jumper to <strong>GND</strong>.</li>
<li>Do not connect the LED to 5V. ESP32 logic level is 3.3 V.</li>
</ol>
<p>If the LED stays dark after Upload: check LED polarity, check GPIO 2 (not IO6–IO11), check the resistor is in place.</p>

<h2>Practice — sketch FS18_blink</h2>
{$timeline}
<p>Goal: the LED on GPIO 2 blinks <strong>1 second on / 1 second off</strong>, controlled by code — not only from 3V3 like FS-09.</p>
<ol>
<li>Arduino IDE → <strong>File → New Sketch</strong> → <strong>Save as</strong> <code>FS18_blink</code>.</li>
<li>Replace the contents with the code below (copy it whole).</li>
<li><strong>Verify</strong> (✓) → <strong>Upload</strong> (→) → wait for <em>Done uploading</em>.</li>
<li>Watch the LED blink. Open <strong>Serial Monitor</strong> → baud <strong>115200</strong> → alternating <code>LED ON</code> / <code>LED OFF</code> lines.</li>
<li>Press <strong>EN (7)</strong> if you need to restart the sketch.</li>
</ol>
<pre><code class="language-cpp">// FS18_blink — Full Stack IoT FS-18
// GPIO 2 as an LED switch (pinMode + digitalWrite)

const int LED_PIN = 2; // FSIOT global table: practice LED = GPIO 2

void setup() {
  pinMode(LED_PIN, OUTPUT);
  Serial.begin(115200);
  delay(500);
  Serial.println("FS18_blink ready");
}

void loop() {
  digitalWrite(LED_PIN, HIGH); // on
  Serial.println("LED ON");
  delay(1000);

  digitalWrite(LED_PIN, LOW); // off
  Serial.println("LED OFF");
  delay(1000);
}
</code></pre>
<p><strong>How to test the commands above:</strong> test in <strong>Arduino IDE + Upload + your eyes on the LED</strong> (and Serial Monitor). Success = a steady 1-second blink + ON/OFF lines at baud 115200. Not a Laragon / web-server command.</p>

<h2 id="fsiot-blink-checklist">Practice — LED-from-code checklist</h2>
<p>Tick each step after you do it at the desk. Target: <strong>10/10</strong>. An interactive checklist appears below.</p>
<ul id="fsiot-blink-checklist-items">
<li>Arduino IDE is open before writing code</li>
<li>ESP32 Dev Module board + port are selected</li>
<li>LED + 220 Ω resistor connect GPIO 2 and GND</li>
<li>Understood: pinMode(OUTPUT) is required before digitalWrite</li>
<li>Understood: HIGH = on, LOW = off</li>
<li>Sketch saved as FS18_blink</li>
<li>Upload succeeded — Done uploading</li>
<li>LED blinks about 1 s ON / 1 s OFF</li>
<li>Serial Monitor at baud 115200 shows LED ON / LED OFF</li>
<li>Aware: forgetting pinMode or skipping the resistor are common mistakes</li>
</ul>
<p><strong>How to test the checklist:</strong> tick items in the browser after IDE + board practice. No <code>php artisan</code> needed.</p>

<h2>Common mistakes</h2>
<ul>
<li><strong>Forgot <code>pinMode(OUTPUT)</code>.</strong> Without it, <code>digitalWrite</code> is often unreliable. Put it in <code>setup()</code>.</li>
<li><strong>LED with no resistor.</strong> Can damage the LED/pin. Always use 220 Ω (or a nearby safe value).</li>
<li><strong>Wrong LED polarity.</strong> Long lead = anode (+) toward the GPIO/resistor side.</li>
<li><strong>Wiring to IO6–IO11.</strong> Forbidden (flash). Stay on GPIO 2.</li>
<li><strong>LED on 5V.</strong> ESP32 GPIO is 3.3 V — follow GPIO 2 → resistor → LED → GND.</li>
<li><strong>Wrong Serial baud.</strong> Match 115200 in code and the Monitor.</li>
<li><strong>Testing in a web terminal.</strong> The sketch only runs on the board via IDE Upload.</li>
</ul>

<h2>Next</h2>
<p><strong>In short:</strong> if the LED on GPIO 2 blinks under <code>pinMode</code> + <code>digitalWrite</code>, FS-18 is done — your first digital switch in the BUILDER phase.</p>
<p>Continue to <strong>FS-19</strong> (button + debounce) when that module publishes. There we read inputs, not only write outputs.</p>
<p>Full module list: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }
}
