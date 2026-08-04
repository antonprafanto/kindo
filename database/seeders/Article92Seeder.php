<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article92Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first()
            ?? Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $iotCat) {
            throw new \RuntimeException('User atau kategori iot-smart-device/esp32-arduino tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-ldr-adc-seberapa-terang';

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
                'title'              => 'LDR + ADC: mengukur “seberapa terang”',
                'title_en'           => 'LDR + ADC: measuring “how bright”',
                'excerpt'            => 'FS-22 / #92: baca cahaya dengan pembagi tegangan LDR + 10 kΩ di GPIO 34. Uji di Arduino IDE: analogRead → Serial (GELAP / REDUP / TERANG).',
                'excerpt_en'         => 'FS-22 / #92: read light with an LDR + 10 kΩ voltage divider on GPIO 34. Test in Arduino IDE: analogRead → Serial (DARK / DIM / BRIGHT).',
                'body'               => $this->body(),
                'body_en'            => $this->bodyEn(),
                'status'             => 'draft',
                'is_featured'        => false,
                'published_at'       => null,
                'seo_title'          => 'LDR + ADC seberapa terang — Full Stack IoT #92',
                'seo_title_en'       => 'LDR + ADC how bright — Full Stack IoT #92',
                'seo_description'    => 'Belajar pembagi tegangan LDR, ADC 12-bit ESP32, dan analogRead di Serial. Modul FS-22 jalur Full Stack IoT.',
                'seo_description_en' => 'Learn LDR voltage divider, ESP32 12-bit ADC, and analogRead on Serial. Full Stack IoT FS-22 module.',
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
            $src = public_path('images/fsiot/fs22-cover-ldr.jpg');
            if (is_file($src)) {
                $dest = 'articles/covers/fs22-cover-ldr.jpg';
                \Illuminate\Support\Facades\Storage::disk('public')->put($dest, file_get_contents($src));
                $article->cover_image = $dest;
                $article->save();
            }
        }

        $this->command?->info('✓ Artikel #92 / FS-22 tersimpan sebagai DRAFT: '.$article->title);
    }

    private function ideFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem">
  <img src="/images/fsiot/fs11-ide-overview-cite.png" width="1280" height="720" alt="Arduino IDE 2 — Verify, Upload, dan Serial Monitor" loading="eager" style="width:100%;height:auto;max-height:420px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Arduino IDE 2</strong> — tempat menguji sintaks hari ini. Tidak perlu Library Manager baru: cukup <strong>Verify</strong> → <strong>Upload</strong> → buka <strong>Serial Monitor</strong> (baud 115200). Board: <strong>ESP32 Dev Module</strong>.
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:Ide-2-overview.png" rel="noopener noreferrer" target="_blank">Arduino IDE 2 overview</a> · Wikimedia Commons (CC BY-SA 3.0). Panduan Serial: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-serial-monitor/" rel="noopener noreferrer" target="_blank">Arduino Docs — Serial Monitor</a>. Fungsi <code>analogRead</code>: <a href="https://docs.arduino.cc/language-reference/en/functions/analog-io/analogRead/" rel="noopener noreferrer" target="_blank">Arduino Docs — analogRead</a>.
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
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:Ide-2-overview.png" rel="noopener noreferrer" target="_blank">Arduino IDE 2 overview</a> · Wikimedia Commons (CC BY-SA 3.0). Serial guide: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-serial-monitor/" rel="noopener noreferrer" target="_blank">Arduino Docs — Serial Monitor</a>. <code>analogRead</code>: <a href="https://docs.arduino.cc/language-reference/en/functions/analog-io/analogRead/" rel="noopener noreferrer" target="_blank">Arduino Docs — analogRead</a>.
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
    <strong>ESP32-DevKitC</strong> — USB data di <strong>(6)</strong>, reset di <strong>EN (7)</strong>. Pin LDR latihan FSIOT = <strong>GPIO 34</strong> (ADC, input-only — tabel global FS-17).
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
    <strong>ESP32-DevKitC</strong> — USB data at <strong>(6)</strong>, reset on <strong>EN (7)</strong>. FSIOT practice LDR pin = <strong>GPIO 34</strong> (ADC, input-only — FS-17 global table).
    <br>Image source: <a href="https://docs.espressif.com/projects/esp-dev-kits/en/latest/esp32/esp32-devkitc/user_guide.html" rel="noopener noreferrer" target="_blank">Espressif — ESP32-DevKitC user guide</a>. Board pins: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/boards/ESP32-DevKitC-1.html" rel="noopener noreferrer" target="_blank">ESP32-DevKitC-1</a>.
  </figcaption>
</figure>
HTML;
    }

    private function kitFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem">
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;align-items:start">
    <div>
      <img src="/images/fsiot/kit-ldr.jpg" width="900" height="600" alt="Komponen LDR / photoresistor" loading="eager" style="display:block;width:100%;height:auto;max-height:260px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem;margin:0 auto">
      <p style="font-size:0.8rem;margin:0.35rem 0 0;color:#1a1a1a;text-align:center"><strong>LDR</strong> — “mata” cahaya (photoresistor)</p>
    </div>
    <div>
      <img src="/images/fsiot/kit-resistor-10kohm.jpg" width="800" height="600" alt="Resistor 10 kOhm untuk pembagi tegangan" loading="eager" style="display:block;width:100%;height:auto;max-height:260px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem;margin:0 auto">
      <p style="font-size:0.8rem;margin:0.35rem 0 0;color:#1a1a1a;text-align:center"><strong>10 kΩ</strong> — pasangan pembagi tegangan (wajib di latihan ini)</p>
    </div>
  </div>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Komponen hari ini:</strong> LDR + resistor <strong>10 kΩ</strong> + jumper. Berbeda dengan DHT22: di FS-22 resistor <strong>wajib</strong> karena kita membuat pembagi tegangan.
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:LDR_1480405_6_7_HDR_Enhancer_1.jpg" rel="noopener noreferrer" target="_blank">LDR / photoresistor</a> · Wikimedia Commons (CC BY-SA 3.0) · Nevit Dilmen · foto resistor kit Koding Indonesia.
  </figcaption>
</figure>
HTML;
    }

    private function kitFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem">
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;align-items:start">
    <div>
      <img src="/images/fsiot/kit-ldr.jpg" width="900" height="600" alt="LDR / photoresistor component" loading="eager" style="display:block;width:100%;height:auto;max-height:260px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem;margin:0 auto">
      <p style="font-size:0.8rem;margin:0.35rem 0 0;color:#1a1a1a;text-align:center"><strong>LDR</strong> — light “eye” (photoresistor)</p>
    </div>
    <div>
      <img src="/images/fsiot/kit-resistor-10kohm.jpg" width="800" height="600" alt="10 kOhm resistor for voltage divider" loading="eager" style="display:block;width:100%;height:auto;max-height:260px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem;margin:0 auto">
      <p style="font-size:0.8rem;margin:0.35rem 0 0;color:#1a1a1a;text-align:center"><strong>10 kΩ</strong> — voltage-divider partner (required in this lesson)</p>
    </div>
  </div>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Parts today:</strong> LDR + <strong>10 kΩ</strong> resistor + jumpers. Unlike the DHT22: in FS-22 the resistor is <strong>required</strong> because we build a voltage divider.
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:LDR_1480405_6_7_HDR_Enhancer_1.jpg" rel="noopener noreferrer" target="_blank">LDR / photoresistor</a> · Wikimedia Commons (CC BY-SA 3.0) · Nevit Dilmen · Koding Indonesia resistor kit photo.
  </figcaption>
</figure>
HTML;
    }

    private function mainWiringFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs22-ldr-breadboard.png" width="1276" height="767" alt="Gambar utama — rangkaian LDR + 10 kΩ di breadboard ke GPIO 34 (IO34)" loading="eager" style="width:100%;height:auto;max-height:560px;object-fit:contain;border-radius:6px;background:#fff">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Gambar utama — rangkaian LDR di breadboard.</strong> Merah: <strong>3V3</strong> → kaki LDR · biru: titik baca (LDR bertemu 10 kΩ) → <strong>IO34 / GPIO 34</strong> · hitam: kaki 10 kΩ tersisa → <strong>GND</strong> (lewat rail breadboard). Resistor pita coklat–hitam–oranye–emas ≈ <strong>10 kΩ</strong>. Cocokkan <em>label silkscreen</em> di board kamu (clone boleh beda bentuk).
    <br>Sumber gambar: diagram rangkaian buatan Koding Indonesia (FS-22).
  </figcaption>
</figure>
HTML;
    }

    private function mainWiringFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs22-ldr-breadboard.png" width="1276" height="767" alt="Main figure — LDR + 10 kΩ breadboard wiring to GPIO 34 (IO34)" loading="eager" style="width:100%;height:auto;max-height:560px;object-fit:contain;border-radius:6px;background:#fff">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Main figure — LDR on a breadboard.</strong> Red: <strong>3V3</strong> → LDR leg · blue: sense node (LDR meets 10 kΩ) → <strong>IO34 / GPIO 34</strong> · black: remaining 10 kΩ leg → <strong>GND</strong> (via breadboard rail). Brown–black–orange–gold bands ≈ <strong>10 kΩ</strong>. Match the <em>silkscreen labels</em> on your board (clones may look different).
    <br>Image source: wiring diagram by Koding Indonesia (FS-22).
  </figcaption>
</figure>
HTML;
    }

    private function schemaWiringFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs22-ldr-wiring.png" width="1100" height="780" alt="Skema bantu — ringkasan pembagi tegangan LDR ke GPIO 34" loading="eager" style="width:100%;height:auto;max-height:520px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Skema bantu (ringkas).</strong> Sama dengan gambar utama: 3V3 → LDR → titik baca (<strong>GPIO 34</strong>) → 10 kΩ → GND. Pin 34 = ADC input-only. Pakai ini jika kamu lebih nyaman membaca kotak pin daripada foto breadboard.
    <br>Sumber gambar: diagram berlabel buatan Koding Indonesia (FS-22). Konsep ADC: <a href="https://docs.espressif.com/projects/esp-idf/en/latest/esp32/api-reference/peripherals/adc_oneshot.html" rel="noopener noreferrer" target="_blank">Espressif — ADC</a> · <code>analogRead</code>: <a href="https://docs.arduino.cc/language-reference/en/functions/analog-io/analogRead/" rel="noopener noreferrer" target="_blank">Arduino Docs</a>.
  </figcaption>
</figure>
HTML;
    }

    private function schemaWiringFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs22-ldr-wiring.png" width="1100" height="780" alt="Helper schematic — LDR voltage divider to GPIO 34" loading="eager" style="width:100%;height:auto;max-height:520px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Helper schematic.</strong> Same as the main figure: 3V3 → LDR → sense node (<strong>GPIO 34</strong>) → 10 kΩ → GND. Pin 34 = ADC input-only. Use this if you prefer labeled pin boxes over the breadboard photo.
    <br>Image source: labeled diagram by Koding Indonesia (FS-22). ADC concept: <a href="https://docs.espressif.com/projects/esp-idf/en/latest/esp32/api-reference/peripherals/adc_oneshot.html" rel="noopener noreferrer" target="_blank">Espressif — ADC</a> · <code>analogRead</code>: <a href="https://docs.arduino.cc/language-reference/en/functions/analog-io/analogRead/" rel="noopener noreferrer" target="_blank">Arduino Docs</a>.
  </figcaption>
</figure>
HTML;
    }

    private function principleFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem">
  <img src="/images/fsiot/fs22-ldr-principle.png" width="544" height="318" alt="Prinsip photoresistor / LDR" loading="eager" style="display:block;width:100%;max-width:560px;height:auto;margin:0 auto;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Intinya:</strong> LDR mengubah hambatan saat cahaya berubah — chip tidak “melihat” hambatan langsung, melainkan tegangan dari pembagi.
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:LDR-1.png" rel="noopener noreferrer" target="_blank">LDR-1.png</a> · Wikimedia Commons (CC0) · TSE3A.
  </figcaption>
</figure>
HTML;
    }

    private function principleFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem">
  <img src="/images/fsiot/fs22-ldr-principle.png" width="544" height="318" alt="Photoresistor / LDR principle" loading="eager" style="display:block;width:100%;max-width:560px;height:auto;margin:0 auto;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>In short:</strong> an LDR changes resistance with light — the chip does not “see” resistance directly; it reads voltage from the divider.
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:LDR-1.png" rel="noopener noreferrer" target="_blank">LDR-1.png</a> · Wikimedia Commons (CC0) · TSE3A.
  </figcaption>
</figure>
HTML;
    }

    private function senseSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Alur: cahaya ke LDR ke ADC ke kategori Serial" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 160" width="100%" height="auto" style="display:block;max-height:190px">
    <text x="430" y="28" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#1a1a1a">Alur sederhana: cahaya → LDR → angka → kategori</text>
    <rect x="30" y="55" width="150" height="70" rx="10" fill="#FFFDE7" stroke="#F9A825" stroke-width="2.5"/>
    <text x="105" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" font-weight="700" fill="#F57F17">Cahaya</text>
    <text x="200" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="22" fill="#1a1a1a">→</text>
    <rect x="230" y="55" width="150" height="70" rx="10" fill="#FFF3E0" stroke="#EF6C00" stroke-width="2.5"/>
    <text x="305" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" font-weight="700" fill="#E65100">LDR + 10k</text>
    <text x="400" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="22" fill="#1a1a1a">→</text>
    <rect x="430" y="55" width="150" height="70" rx="10" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
    <text x="505" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" font-weight="700" fill="#0D47A1">GPIO 34 ADC</text>
    <text x="600" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="22" fill="#1a1a1a">→</text>
    <rect x="630" y="55" width="200" height="70" rx="10" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
    <text x="730" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" font-weight="700" fill="#1B5E20">GELAP / REDUP / TERANG</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.35rem;color:#4A5568;">
    <strong>Intinya:</strong> cahaya mengubah hambatan LDR → tegangan berubah → ADC mengubahnya jadi angka → kita beri label manusia. Sumber gambar: diagram buatan Koding Indonesia (FS-22).
  </figcaption>
</figure>
SVG;
    }

    private function senseSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Flow: light to LDR to ADC to Serial categories" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 160" width="100%" height="auto" style="display:block;max-height:190px">
    <text x="430" y="28" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#1a1a1a">Simple flow: light → LDR → numbers → labels</text>
    <rect x="30" y="55" width="150" height="70" rx="10" fill="#FFFDE7" stroke="#F9A825" stroke-width="2.5"/>
    <text x="105" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" font-weight="700" fill="#F57F17">Light</text>
    <text x="200" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="22" fill="#1a1a1a">→</text>
    <rect x="230" y="55" width="150" height="70" rx="10" fill="#FFF3E0" stroke="#EF6C00" stroke-width="2.5"/>
    <text x="305" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" font-weight="700" fill="#E65100">LDR + 10k</text>
    <text x="400" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="22" fill="#1a1a1a">→</text>
    <rect x="430" y="55" width="150" height="70" rx="10" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
    <text x="505" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" font-weight="700" fill="#0D47A1">GPIO 34 ADC</text>
    <text x="600" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="22" fill="#1a1a1a">→</text>
    <rect x="630" y="55" width="200" height="70" rx="10" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
    <text x="730" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" font-weight="700" fill="#1B5E20">DARK / DIM / BRIGHT</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.35rem;color:#4A5568;">
    <strong>In short:</strong> light changes LDR resistance → voltage changes → ADC turns it into a number → we add human labels. Image source: diagram by Koding Indonesia (FS-22).
  </figcaption>
</figure>
SVG;
    }

    private function scaleSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Skala ADC 0 sampai 4095 menjadi GELAP REDUP TERANG" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 200" width="100%" height="auto" style="display:block;max-height:230px">
    <text x="430" y="28" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#1a1a1a">ADC 12-bit: 0 … 4095 → label manusia (contoh)</text>
    <rect x="40" y="55" width="240" height="90" rx="10" fill="#ECEFF1" stroke="#546E7A" stroke-width="2.5"/>
    <text x="160" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="18" font-weight="700" fill="#37474F">GELAP</text>
    <text x="160" y="122" text-anchor="middle" font-family="Consolas,monospace" font-size="13" fill="#546E7A">&lt; 1200</text>
    <rect x="310" y="55" width="240" height="90" rx="10" fill="#FFF3E0" stroke="#EF6C00" stroke-width="2.5"/>
    <text x="430" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="18" font-weight="700" fill="#E65100">REDUP</text>
    <text x="430" y="122" text-anchor="middle" font-family="Consolas,monospace" font-size="13" fill="#EF6C00">1200 … 2800</text>
    <rect x="580" y="55" width="240" height="90" rx="10" fill="#FFFDE7" stroke="#F9A825" stroke-width="2.5"/>
    <text x="700" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="18" font-weight="700" fill="#F57F17">TERANG</text>
    <text x="700" y="122" text-anchor="middle" font-family="Consolas,monospace" font-size="13" fill="#F9A825">&gt; 2800</text>
    <text x="430" y="175" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#555">Ambang contoh — sesuaikan dengan ruanganmu (lihat tip di bawah)</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.35rem;color:#4A5568;">
    <strong>Intinya:</strong> angka mentah dari <code>analogRead</code> berubah-ubah; label membuatnya mudah dibaca manusia. Sumber gambar: diagram buatan Koding Indonesia (FS-22).
  </figcaption>
</figure>
SVG;
    }

    private function scaleSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="ADC scale 0 to 4095 mapped to DARK DIM BRIGHT" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 200" width="100%" height="auto" style="display:block;max-height:230px">
    <text x="430" y="28" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#1a1a1a">12-bit ADC: 0 … 4095 → human labels (example)</text>
    <rect x="40" y="55" width="240" height="90" rx="10" fill="#ECEFF1" stroke="#546E7A" stroke-width="2.5"/>
    <text x="160" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="18" font-weight="700" fill="#37474F">DARK</text>
    <text x="160" y="122" text-anchor="middle" font-family="Consolas,monospace" font-size="13" fill="#546E7A">&lt; 1200</text>
    <rect x="310" y="55" width="240" height="90" rx="10" fill="#FFF3E0" stroke="#EF6C00" stroke-width="2.5"/>
    <text x="430" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="18" font-weight="700" fill="#E65100">DIM</text>
    <text x="430" y="122" text-anchor="middle" font-family="Consolas,monospace" font-size="13" fill="#EF6C00">1200 … 2800</text>
    <rect x="580" y="55" width="240" height="90" rx="10" fill="#FFFDE7" stroke="#F9A825" stroke-width="2.5"/>
    <text x="700" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="18" font-weight="700" fill="#F57F17">BRIGHT</text>
    <text x="700" y="122" text-anchor="middle" font-family="Consolas,monospace" font-size="13" fill="#F9A825">&gt; 2800</text>
    <text x="430" y="175" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#555">Example thresholds — tune them for your room (see tip below)</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.35rem;color:#4A5568;">
    <strong>In short:</strong> raw <code>analogRead</code> numbers wobble; labels make them human-readable. Image source: diagram by Koding Indonesia (FS-22).
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
        $principle = $this->principleFigureId();
        $sense = $this->senseSvgId();
        $scale = $this->scaleSvgId();

        return <<<HTML
<h2>Pendahuluan — mata cahaya board</h2>
<p>Artikel ini adalah <strong>#92 (ini)</strong> · modul <strong>FS-22</strong> di jalur <em>Full Stack IoT Developer — Dari Nol</em> (fase <strong>BUILDER</strong>). Di <strong>FS-21</strong> board membaca suhu &amp; kelembapan. Hari ini kita mengukur <strong>seberapa terang</strong> dengan <strong>LDR</strong> + <strong>ADC</strong>.</p>
<p><strong>Analogi:</strong> LDR seperti kacamata hitam yang “menebal” di gelap dan “menipis” di terang. Chip tidak bisa membaca hambatan mentah, jadi kita pasangkan resistor tetap → jadi <strong>pembagi tegangan</strong> → dibaca sebagai angka.</p>
{$sense}
<p><strong>Prasyarat:</strong> FS-18 (pernah Upload) · FS-17 (GPIO 34 di tabel global) · Arduino IDE sudah bisa Upload ke ESP32. FS-21 membantu, tapi hari ini tidak wajib Library Manager baru.</p>
<p><strong>Cara pakai artikel ini (urutan kerja):</strong></p>
<ol>
<li>Rakit pembagi tegangan LDR + 10 kΩ ke <strong>GPIO 34</strong>. Cocokkan <strong>gambar utama</strong> (foto breadboard).</li>
<li><strong>Buka Arduino IDE</strong> (bukan Laragon / terminal web).</li>
<li>Buat sketch <code>FS22_ldr_adc</code> → <strong>Verify</strong> → <strong>Upload</strong>.</li>
<li>Buka Serial Monitor baud <strong>115200</strong> — tutup/buka LDR, lihat angka &amp; label berubah.</li>
<li>Centang checklist 10/10 di browser.</li>
</ol>
<p><strong>Tidak perlu hari ini:</strong> Wi-Fi, MQTT, DHT library baru, Laragon, <code>php artisan</code>. Tools hari ini: <strong>Arduino IDE</strong> + <strong>Upload</strong> + ESP32 + LDR + 10 kΩ + jumper + <strong>Serial Monitor</strong> + <strong>browser</strong>.</p>

<h2>Persiapan — buka &amp; siapkan ini dulu</h2>
<p><strong>Urutan meja kerja:</strong> wiring pembagi → Upload sketch → baca Serial sambil tutup/buka LDR.</p>
<ol>
<li>Buka <strong>Arduino IDE 2.x</strong> · board <strong>ESP32 Dev Module</strong> + port.</li>
<li>Siapkan ESP32 + USB data.</li>
<li>Siapkan <strong>LDR</strong> + resistor <strong>10 kΩ</strong> + jumper.</li>
<li>Cari label <strong>GPIO 34</strong>, <strong>3V3</strong>, dan <strong>GND</strong> di silkscreen (34 = input-only).</li>
<li>Siapkan Serial Monitor baud <strong>115200</strong>.</li>
</ol>
<p><strong>Alat yang dipakai hari ini:</strong> Arduino IDE, Upload, ESP32, USB data, LDR, 10 kΩ, jumper, Serial Monitor, browser.</p>
<p><strong>Tidak dipakai hari ini:</strong> Laragon, <code>php artisan</code>, Library Manager baru, tombol FS-19, DHT22 (boleh tetap terpasang di GPIO 4 bila tidak mengganggu).</p>
{$ide}
{$board}
{$kit}
{$principle}
{$main}
{$schema}

<h2>Wiring LDR (bahasa manusia)</h2>
<p><strong>Rangkaian:</strong> 3V3 → satu kaki <strong>LDR</strong> → kaki LDR lainnya bertemu satu kaki <strong>10 kΩ</strong> (titik baca) → kaki 10 kΩ tersisa ke <strong>GND</strong>. Dari <strong>titik baca</strong> tarik kabel ke <strong>GPIO 34</strong> (di silkscreen sering tertulis <strong>IO34</strong>).</p>
<p><strong>Kenapa GPIO 34?</strong> Pin itu ADC <strong>input-only</strong> (tabel FS-17). Cocok untuk membaca tegangan, tidak dipakai untuk LED/relay.</p>
<p><strong>Arah angka:</strong> dengan susunan di atas, cahaya lebih terang biasanya membuat angka <code>analogRead</code> <strong>naik</strong>; menutup LDR biasanya <strong>turun</strong>. Kalau di board-mu terbalik, tukar posisi LDR dan 10 kΩ — atau cukup balik logika label di kode.</p>

<h2>Praktik — sketch FS22_ldr_adc</h2>
{$scale}
<p>Tujuan: Serial menampilkan nilai mentah (0–4095) plus label <strong>GELAP / REDUP / TERANG</strong>. Ambang di sketch adalah <strong>contoh</strong> — sesuaikan setelah kamu lihat angka di ruanganmu.</p>
<ol>
<li>Arduino IDE → <strong>File → New Sketch</strong> → <strong>Simpan sebagai</strong> <code>FS22_ldr_adc</code>.</li>
<li>Ganti isi dengan kode di bawah (salin utuh).</li>
<li><strong>Verify</strong> → <strong>Upload</strong> → tunggu <em>Done uploading</em>.</li>
<li>Buka Serial Monitor baud <strong>115200</strong>.</li>
<li>Tutup LDR dengan tangan, lalu buka ke cahaya — angka &amp; label harus berubah.</li>
</ol>
<pre><code class="language-cpp">// FS22_ldr_adc — Full Stack IoT FS-22
// LDR + 10k di GPIO 34 → kategori cahaya ke Serial

const int PIN_LDR = 34; // ADC input-only (FS-17)

// Ambang CONTOH — sesuaikan setelah lihat angka di Serial
const int AMBANG_GELAP = 1200;
const int AMBANG_REDUP = 2800;

void setup() {
  Serial.begin(115200);
  delay(500);
  Serial.println("FS22_ldr_adc siap");
}

void loop() {
  int nilai = analogRead(PIN_LDR); // ESP32: biasanya 0..4095

  const char* label;
  if (nilai &lt; AMBANG_GELAP) {
    label = "GELAP";
  } else if (nilai &lt; AMBANG_REDUP) {
    label = "REDUP";
  } else {
    label = "TERANG";
  }

  Serial.print("ADC: ");
  Serial.print(nilai);
  Serial.print(" → ");
  Serial.println(label);

  delay(500);
}
</code></pre>
<p><strong>Cara menguji perintah di atas:</strong> uji di <strong>Arduino IDE + Upload + Serial Monitor</strong>. Sukses = menutup LDR mengubah angka/label, membuka ke cahaya mengubah lagi. Bukan perintah Laragon / web server.</p>
<p><strong>Tip ambang:</strong> catat angka saat gelap total dan saat terang di meja. Geser <code>AMBANG_GELAP</code> / <code>AMBANG_REDUP</code> di tengah-tengah angka itu supaya tiga kategori terasa stabil.</p>

<h2 id="fsiot-ldr-checklist">Praktik — checklist LDR + ADC</h2>
<p>Centang setiap langkah setelah kamu lakukan di meja. Target: <strong>10/10</strong>.</p>
<ul id="fsiot-ldr-checklist-items">
<li>Arduino IDE sudah terbuka sebelum menulis kode</li>
<li>Board ESP32 Dev Module + port sudah dipilih</li>
<li>Wiring: 3V3–LDR–titik baca–10 kΩ–GND, titik baca ke GPIO 34</li>
<li>Paham: GPIO 34 = ADC input-only (bukan pin LED)</li>
<li>Paham: analogRead menghasilkan angka (bukan hanya ON/OFF)</li>
<li>Paham: ambang GELAP/REDUP/TERANG bisa disesuaikan</li>
<li>Sketch disimpan sebagai FS22_ldr_adc</li>
<li>Upload berhasil — Done uploading</li>
<li>Serial berubah saat LDR ditutup / dibuka</li>
<li>Sadar: ini fondasi sensor analog sebelum relay &amp; automasi</li>
</ul>
<p><strong>Cara menguji checklist:</strong> centang di browser setelah praktik di IDE + board. Tidak perlu <code>php artisan</code>.</p>

<h2>Kesalahan yang sering terjadi</h2>
<ul>
<li><strong>LDR ke pin digital biasa.</strong> Pakai <strong>GPIO 34</strong> (atau pin ADC input-only lain). Jangan GPIO 0 / pin output latihan LED.</li>
<li><strong>Lupa resistor 10 kΩ.</strong> Tanpa pasangan, pembagi tegangan tidak terbentuk — angka bisa aneh atau mentok.</li>
<li><strong>Lupa ground bersama.</strong> GND ESP32 dan kaki bawah 10 kΩ harus satu “tanah”.</li>
<li><strong>Mengira angka harus sama dengan orang lain.</strong> LDR &amp; cahaya ruangan beda-beda — yang penting berubah konsisten.</li>
<li><strong>Ambang terlalu ketat.</strong> Kalau label meloncat-loncat, longgarkan jarak antara <code>AMBANG_GELAP</code> dan <code>AMBANG_REDUP</code>.</li>
<li><strong>Menguji di terminal web.</strong> Sketch hanya jalan di board lewat IDE Upload.</li>
</ul>

<h2>Selanjutnya</h2>
<p><strong>Intinya:</strong> kalau Serial menunjukkan angka yang bergerak saat cahaya berubah dan label GELAP/REDUP/TERANG masuk akal, FS-22 selesai — fondasi baca sensor analog terbuka.</p>
<p>Lanjut ke <strong>FS-23</strong> (relay aman) saat modulnya terbit — fondasi aktuator sebelum automasi lokal.</p>
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
        $principle = $this->principleFigureEn();
        $sense = $this->senseSvgEn();
        $scale = $this->scaleSvgEn();

        return <<<HTML
<h2>Introduction — the board’s light eye</h2>
<p>This is article <strong>#92 (this article)</strong> · module <strong>FS-22</strong> on the <em>Full Stack IoT Developer — From Zero</em> path (<strong>BUILDER</strong> phase). In <strong>FS-21</strong> the board read temperature &amp; humidity. Today we measure <strong>how bright</strong> it is with an <strong>LDR</strong> + <strong>ADC</strong>.</p>
<p><strong>Analogy:</strong> an LDR is like sunglasses that get “thicker” in the dark and “thinner” in bright light. The chip cannot read raw resistance, so we pair it with a fixed resistor → a <strong>voltage divider</strong> → a readable number.</p>
{$sense}
<p><strong>Prerequisites:</strong> FS-18 (you have Uploaded) · FS-17 (GPIO 34 in the global table) · Arduino IDE can Upload to the ESP32. FS-21 helps, but today you do not need a new Library Manager install.</p>
<p><strong>How to use this article (work order):</strong></p>
<ol>
<li>Build the LDR + 10 kΩ voltage divider on <strong>GPIO 34</strong>. Match the <strong>main figure</strong> (breadboard photo).</li>
<li><strong>Open Arduino IDE</strong> (not Laragon / a web terminal).</li>
<li>Create sketch <code>FS22_ldr_adc</code> → <strong>Verify</strong> → <strong>Upload</strong>.</li>
<li>Open Serial Monitor at baud <strong>115200</strong> — cover/uncover the LDR and watch numbers &amp; labels change.</li>
<li>Tick the 10/10 checklist in the browser.</li>
</ol>
<p><strong>Not needed today:</strong> Wi-Fi, MQTT, a new DHT library, Laragon, <code>php artisan</code>. Today's tools: <strong>Arduino IDE</strong> + <strong>Upload</strong> + ESP32 + LDR + 10 kΩ + jumpers + <strong>Serial Monitor</strong> + <strong>browser</strong>.</p>

<h2>Preparation — open &amp; gather these first</h2>
<p><strong>Desk order:</strong> divider wiring → Upload sketch → read Serial while covering/uncovering the LDR.</p>
<ol>
<li>Open <strong>Arduino IDE 2.x</strong> · <strong>ESP32 Dev Module</strong> board + port.</li>
<li>Prepare the ESP32 + USB data cable.</li>
<li>Prepare an <strong>LDR</strong> + <strong>10 kΩ</strong> resistor + jumpers.</li>
<li>Find <strong>GPIO 34</strong>, <strong>3V3</strong>, and <strong>GND</strong> on the silkscreen (34 = input-only).</li>
<li>Have Serial Monitor ready at baud <strong>115200</strong>.</li>
</ol>
<p><strong>Tools used today:</strong> Arduino IDE, Upload, ESP32, USB data, LDR, 10 kΩ, jumpers, Serial Monitor, browser.</p>
<p><strong>Not used today:</strong> Laragon, <code>php artisan</code>, a new Library Manager install, FS-19 button, DHT22 (may stay on GPIO 4 if it does not interfere).</p>
{$ide}
{$board}
{$kit}
{$principle}
{$main}
{$schema}

<h2>LDR wiring (human language)</h2>
<p><strong>Circuit:</strong> 3V3 → one LDR leg → the other LDR leg meets one 10 kΩ leg (sense node) → the remaining 10 kΩ leg to <strong>GND</strong>. From the <strong>sense node</strong>, run a wire to <strong>GPIO 34</strong> (often labeled <strong>IO34</strong> on the silkscreen).</p>
<p><strong>Why GPIO 34?</strong> That pin is an <strong>input-only</strong> ADC (FS-17 table). Good for reading voltage; not used for LEDs/relays.</p>
<p><strong>Number direction:</strong> with the layout above, brighter light usually makes <code>analogRead</code> <strong>rise</strong>; covering the LDR usually makes it <strong>fall</strong>. If yours is reversed, swap LDR and 10 kΩ — or simply flip the label logic in code.</p>

<h2>Practice — sketch FS22_ldr_adc</h2>
{$scale}
<p>Goal: Serial prints a raw value (0–4095) plus a <strong>DARK / DIM / BRIGHT</strong> label. Thresholds in the sketch are <strong>examples</strong> — tune them after you see numbers in your room.</p>
<ol>
<li>Arduino IDE → <strong>File → New Sketch</strong> → <strong>Save As</strong> <code>FS22_ldr_adc</code>.</li>
<li>Replace the contents with the code below (copy whole).</li>
<li><strong>Verify</strong> → <strong>Upload</strong> → wait for <em>Done uploading</em>.</li>
<li>Open Serial Monitor at baud <strong>115200</strong>.</li>
<li>Cover the LDR with your hand, then open it to light — numbers &amp; labels should change.</li>
</ol>
<pre><code class="language-cpp">// FS22_ldr_adc — Full Stack IoT FS-22
// LDR + 10k on GPIO 34 → light category to Serial

const int PIN_LDR = 34; // ADC input-only (FS-17)

// EXAMPLE thresholds — tune after watching Serial numbers
const int THRESH_DARK = 1200;
const int THRESH_DIM = 2800;

void setup() {
  Serial.begin(115200);
  delay(500);
  Serial.println("FS22_ldr_adc ready");
}

void loop() {
  int value = analogRead(PIN_LDR); // ESP32: usually 0..4095

  const char* label;
  if (value &lt; THRESH_DARK) {
    label = "DARK";
  } else if (value &lt; THRESH_DIM) {
    label = "DIM";
  } else {
    label = "BRIGHT";
  }

  Serial.print("ADC: ");
  Serial.print(value);
  Serial.print(" → ");
  Serial.println(label);

  delay(500);
}
</code></pre>
<p><strong>How to test the commands above:</strong> test in <strong>Arduino IDE + Upload + Serial Monitor</strong>. Success = covering the LDR changes the number/label, and opening to light changes it again. Not a Laragon / web-server command.</p>
<p><strong>Threshold tip:</strong> note the number in full dark and in bright desk light. Move <code>THRESH_DARK</code> / <code>THRESH_DIM</code> between those readings so the three labels feel stable.</p>

<h2 id="fsiot-ldr-checklist">Practice — LDR + ADC checklist</h2>
<p>Tick each step after you do it at the desk. Target: <strong>10/10</strong>.</p>
<ul id="fsiot-ldr-checklist-items">
<li>Arduino IDE is open before writing code</li>
<li>ESP32 Dev Module board + port are selected</li>
<li>Wiring: 3V3–LDR–sense node–10 kΩ–GND, sense node to GPIO 34</li>
<li>I understand: GPIO 34 = ADC input-only (not an LED pin)</li>
<li>I understand: analogRead returns a number (not just ON/OFF)</li>
<li>I understand: DARK/DIM/BRIGHT thresholds can be tuned</li>
<li>Sketch saved as FS22_ldr_adc</li>
<li>Upload succeeded — Done uploading</li>
<li>Serial changes when the LDR is covered / uncovered</li>
<li>I know: this is the analog-sensor foundation before relays &amp; automation</li>
</ul>
<p><strong>How to test the checklist:</strong> tick in the browser after practice on the IDE + board. No <code>php artisan</code> needed.</p>

<h2>Common mistakes</h2>
<ul>
<li><strong>LDR on a normal digital pin.</strong> Use <strong>GPIO 34</strong> (or another input-only ADC pin). Not GPIO 0 / LED practice pins.</li>
<li><strong>Forgetting the 10 kΩ.</strong> Without the partner resistor there is no voltage divider — readings can stick or look random.</li>
<li><strong>Missing shared ground.</strong> ESP32 GND and the bottom of the 10 kΩ must share one ground.</li>
<li><strong>Expecting the same numbers as someone else.</strong> LDRs and rooms differ — consistent change matters more.</li>
<li><strong>Thresholds too tight.</strong> If labels chatter, widen the gap between <code>THRESH_DARK</code> and <code>THRESH_DIM</code>.</li>
<li><strong>Testing in a web terminal.</strong> The sketch runs on the board via IDE Upload only.</li>
</ul>

<h2>Next</h2>
<p><strong>In short:</strong> if Serial shows moving numbers when light changes and DARK/DIM/BRIGHT labels feel sensible, FS-22 is done — the analog-sensor foundation is open.</p>
<p>Continue to <strong>FS-23</strong> (safe relay) when that module publishes — actuator foundation before local automation.</p>
<p>Full module list: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }
}
