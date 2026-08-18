<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article95Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first()
            ?? Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $iotCat) {
            throw new \RuntimeException('User atau kategori iot-smart-device/esp32-arduino tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-pir-gerak';

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
                'title'              => 'PIR: ada gerak atau tidak',
                'title_en'           => 'PIR: motion or no motion',
                'excerpt'            => 'FS-25 / #95: sensor PIR HC-SR501 di GPIO 25. Gerak tangan → LED GPIO 2 + Serial. Uji di Arduino IDE — tunggu settle dulu.',
                'excerpt_en'         => 'FS-25 / #95: HC-SR501 PIR on GPIO 25. Wave your hand → LED GPIO 2 + Serial. Test in Arduino IDE — wait for settle first.',
                'body'               => $this->body(),
                'body_en'            => $this->bodyEn(),
                'status'             => 'draft',
                'is_featured'        => false,
                'published_at'       => null,
                'seo_title'          => 'PIR gerak HC-SR501 — Full Stack IoT #95',
                'seo_title_en'       => 'PIR motion HC-SR501 — Full Stack IoT #95',
                'seo_description'    => 'Baca sensor PIR digital di ESP32: GPIO 25, settle, false trigger, LED GPIO 2. Modul FS-25 jalur Full Stack IoT.',
                'seo_description_en' => 'Read a digital PIR on ESP32: GPIO 25, settle time, false triggers, LED GPIO 2. Full Stack IoT FS-25 module.',
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
            $src = public_path('images/fsiot/fs25-cover-pir.jpg');
            if (is_file($src)) {
                $dest = 'articles/covers/fs25-cover-pir.jpg';
                \Illuminate\Support\Facades\Storage::disk('public')->put($dest, file_get_contents($src));
                $article->cover_image = $dest;
                $article->save();
            }
        }

        $this->command?->info('✓ Artikel #95 / FS-25 tersimpan sebagai DRAFT: '.$article->title);
    }

    private function ideFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem">
  <img src="/images/fsiot/fs11-ide-overview-cite.png" width="1280" height="720" alt="Arduino IDE 2 — Verify, Upload, dan Serial Monitor" loading="eager" style="width:100%;height:auto;max-height:420px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Arduino IDE 2</strong> — tempat menguji sintaks hari ini. <strong>Tidak perlu Library Manager baru</strong>: cukup <strong>Verify</strong> → <strong>Upload</strong> → buka <strong>Serial Monitor</strong> (baud <strong>115200</strong>). Board: <strong>ESP32 Dev Module</strong>. <em>Catatan gambar:</em> screenshot Commons masih menampilkan AnalogReadSerial + baud 9600 — <strong>abaikan</strong>; untuk FS-25 pakai kode di bawah + baud 115200.
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:Ide-2-overview.png" rel="noopener noreferrer" target="_blank">Arduino IDE 2 overview</a> · Wikimedia Commons (CC BY-SA 3.0). Panduan Serial: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-serial-monitor/" rel="noopener noreferrer" target="_blank">Arduino Docs — Serial Monitor</a>. Fungsi <code>digitalRead</code>: <a href="https://docs.arduino.cc/language-reference/en/functions/digital-io/digitalread/" rel="noopener noreferrer" target="_blank">Arduino Docs — digitalRead</a>.
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
    <strong>Arduino IDE 2</strong> — where today’s syntax is tested. <strong>No new Library Manager install</strong>: just <strong>Verify</strong> → <strong>Upload</strong> → open <strong>Serial Monitor</strong> (baud <strong>115200</strong>). Board: <strong>ESP32 Dev Module</strong>. <em>Image note:</em> the Commons screenshot still shows AnalogReadSerial + baud 9600 — <strong>ignore</strong> it; for FS-25 use the code below + baud 115200.
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:Ide-2-overview.png" rel="noopener noreferrer" target="_blank">Arduino IDE 2 overview</a> · Wikimedia Commons (CC BY-SA 3.0). Serial guide: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-serial-monitor/" rel="noopener noreferrer" target="_blank">Arduino Docs — Serial Monitor</a>. <code>digitalRead</code>: <a href="https://docs.arduino.cc/language-reference/en/functions/digital-io/digitalread/" rel="noopener noreferrer" target="_blank">Arduino Docs — digitalRead</a>.
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
    <strong>ESP32-DevKitC</strong> — USB data di <strong>(6)</strong>, reset di <strong>EN (7)</strong>. Pin hari ini: <strong>GPIO 25</strong> / <strong>IO25</strong> (OUT PIR) · <strong>GPIO 2</strong> / <strong>IO2</strong> (LED) · plus <strong>5V</strong> dan <strong>GND</strong> (tabel FS-17).
    <br>Sumber gambar: <a href="https://docs.espressif.com/projects/esp-idf/en/latest/esp32/hw-reference/esp32/get-started-devkitc.html" rel="noopener noreferrer" target="_blank">Espressif — ESP32-DevKitC user guide</a>.
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
    <strong>ESP32-DevKitC</strong> — USB data at <strong>(6)</strong>, reset on <strong>EN (7)</strong>. Pins today: <strong>GPIO 25</strong> / <strong>IO25</strong> (PIR OUT) · <strong>GPIO 2</strong> / <strong>IO2</strong> (LED) · plus <strong>5V</strong> and <strong>GND</strong> (FS-17 table).
    <br>Image source: <a href="https://docs.espressif.com/projects/esp-idf/en/latest/esp32/hw-reference/esp32/get-started-devkitc.html" rel="noopener noreferrer" target="_blank">Espressif — ESP32-DevKitC user guide</a>.
  </figcaption>
</figure>
HTML;
    }

    private function kitPirFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem">
  <img src="/images/fsiot/kit-pir-hcsr501.jpg" width="900" height="900" alt="Modul PIR HC-SR501 dengan lensa Fresnel putih" loading="eager" style="display:block;width:100%;max-width:420px;height:auto;max-height:360px;object-fit:contain;margin:0 auto;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Indra hari ini:</strong> modul <strong>PIR HC-SR501</strong> (lensa putih bulat = Fresnel). Tiga pin: <strong>VCC</strong> · <strong>OUT</strong> · <strong>GND</strong>. Di belakang ada 2 <strong>potensiometer</strong> (sensitivity &amp; time) + jumper H/L. OUT → <strong>GPIO 25</strong>, VCC → <strong>5V</strong>.
    <br><em>Tip:</em> foto Commons menampilkan sisi belakang + label bahasa Inggris — urutan pin pada clone bisa beda. <strong>Jangan tebak dari warna kabel foto</strong>; cocokkan tulisan silkscreen <strong>VCC / OUT / GND</strong> di modulmu.
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:PIR-inexpensive.jpg" rel="noopener noreferrer" target="_blank">PIR-inexpensive.jpg</a> · Wikimedia Commons (CC BY-SA 4.0) · Lethalattraction. Acuan pinout: <a href="https://learn.adafruit.com/pir-passive-infrared-proximity-motion-sensor" rel="noopener noreferrer" target="_blank">Adafruit — PIR motion sensors</a>.
  </figcaption>
</figure>
HTML;
    }

    private function kitPirFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem">
  <img src="/images/fsiot/kit-pir-hcsr501.jpg" width="900" height="900" alt="HC-SR501 PIR module with white Fresnel lens" loading="eager" style="display:block;width:100%;max-width:420px;height:auto;max-height:360px;object-fit:contain;margin:0 auto;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Sense today:</strong> an <strong>HC-SR501 PIR</strong> module (round white Fresnel lens). Three pins: <strong>VCC</strong> · <strong>OUT</strong> · <strong>GND</strong>. On the back: two <strong>potentiometers</strong> (sensitivity &amp; time) + H/L jumper. OUT → <strong>GPIO 25</strong>, VCC → <strong>5V</strong>.
    <br><em>Tip:</em> the Commons photo shows the back with English labels — pin order can differ on clones. <strong>Do not guess from the photo wire colors</strong>; match the <strong>VCC / OUT / GND</strong> silkscreen on your module.
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:PIR-inexpensive.jpg" rel="noopener noreferrer" target="_blank">PIR-inexpensive.jpg</a> · Wikimedia Commons (CC BY-SA 4.0) · Lethalattraction. Pinout guide: <a href="https://learn.adafruit.com/pir-passive-infrared-proximity-motion-sensor" rel="noopener noreferrer" target="_blank">Adafruit — PIR motion sensors</a>.
  </figcaption>
</figure>
HTML;
    }

    private function mainWiringFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs25-pir-breadboard.png" width="1374" height="766" alt="Gambar utama — rangkaian PIR GPIO 25 + LED GPIO 2 di breadboard" loading="eager" style="width:100%;height:auto;max-height:560px;object-fit:contain;border-radius:6px;background:#fff">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Gambar utama — rangkaian PIR di breadboard.</strong> Merah: <strong>5V</strong> → VCC PIR · kuning/biru: <strong>OUT</strong> → <strong>IO25 / GPIO 25</strong> · oranye: <strong>IO2 / GPIO 2</strong> → anoda LED · hitam: <strong>GND</strong> bersama (katoda LED lewat resistor ke rail GND). Bentuk modul PIR bisa beda (lensa + kabel vs PCB 3 pin) — sambungan yang sama: VCC / OUT / GND.
    <br><em>Tip:</em> nomor kolom breadboard ≠ nomor GPIO. Resistor LED di gambar boleh ~220 Ω–1 kΩ — yang penting LED tidak langsung ke GPIO tanpa resistor. Setelah colok USB, tunggu <strong>30–60 detik</strong> (settle) sebelum menilai false trigger.
    <br>Sumber gambar: diagram rangkaian buatan Koding Indonesia (FS-25).
  </figcaption>
</figure>
HTML;
    }

    private function mainWiringFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs25-pir-breadboard.png" width="1374" height="766" alt="Main figure — PIR GPIO 25 + LED GPIO 2 on a breadboard" loading="eager" style="width:100%;height:auto;max-height:560px;object-fit:contain;border-radius:6px;background:#fff">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Main figure — PIR circuit on a breadboard.</strong> Red: <strong>5V</strong> → PIR VCC · yellow/blue: <strong>OUT</strong> → <strong>IO25 / GPIO 25</strong> · orange: <strong>IO2 / GPIO 2</strong> → LED anode · black: shared <strong>GND</strong> (LED cathode via resistor to the GND rail). PIR module shape can differ (dome+wires vs 3-pin PCB) — same three connections: VCC / OUT / GND.
    <br><em>Tip:</em> breadboard column numbers ≠ GPIO numbers. The LED resistor in the figure may be ~220 Ω–1 kΩ — just do not wire the LED straight to a GPIO with no resistor. After plugging USB in, wait <strong>30–60 seconds</strong> (settle) before judging false triggers.
    <br>Image source: wiring diagram by Koding Indonesia (FS-25).
  </figcaption>
</figure>
HTML;
    }

    private function schemaWiringFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs25-pir-wiring.png" width="1100" height="820" alt="Skema bantu — ringkasan pin PIR GPIO 25 + LED GPIO 2" loading="eager" style="width:100%;height:auto;max-height:520px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Skema bantu (ringkas).</strong> Pin sama gambar utama: PIR 5V/GPIO 25/GND · LED GPIO 2/GND · settle 30–60 dtk. Warna di skema: <strong>merah</strong>=5V · <strong>biru</strong>=OUT · <strong>oranye</strong>=LED · abu=GND (di breadboard, OUT sering kuning lalu biru ke IO25).
    <br>Sumber gambar: diagram berlabel buatan Koding Indonesia (FS-25).
  </figcaption>
</figure>
HTML;
    }

    private function schemaWiringFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs25-pir-wiring.png" width="1100" height="820" alt="Helper schematic — PIR GPIO 25 + LED GPIO 2 pin summary" loading="eager" style="width:100%;height:auto;max-height:520px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Helper schematic.</strong> Same pins as the main figure: PIR 5V/GPIO 25/GND · LED GPIO 2/GND · settle 30–60 s. Schematic colors: <strong>red</strong>=5V · <strong>blue</strong>=OUT · <strong>orange</strong>=LED · grey=GND (on the breadboard, OUT is often yellow then blue into IO25).
    <br>Image source: labeled diagram by Koding Indonesia (FS-25).
  </figcaption>
</figure>
HTML;
    }


    private function flowSvgId(): string
    {
        return <<<'HTML'
<figure role="img" aria-label="Alur: gerak ke PIR ke LED" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 160" width="100%" height="auto" style="display:block;max-height:190px">
    <rect width="860" height="160" fill="#F5F5F0"/>
    <text x="430" y="28" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#1a1a1a">Alur: gerak → baca → nyalakan “lampu”</text>
    <rect x="40" y="50" width="200" height="80" rx="10" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
    <text x="140" y="85" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="15" font-weight="700" fill="#1B5E20">Gerak tangan</text>
    <text x="140" y="108" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#333">di depan lensa PIR</text>
    <text x="270" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="22" fill="#1a1a1a">→</text>
    <rect x="300" y="50" width="220" height="80" rx="10" fill="#BBDEFB" stroke="#1565C0" stroke-width="2.5"/>
    <text x="410" y="85" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="15" font-weight="700" fill="#0D47A1">PIR OUT HIGH</text>
    <text x="410" y="108" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#333">GPIO 25 = 1</text>
    <text x="550" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="22" fill="#1a1a1a">→</text>
    <rect x="580" y="50" width="240" height="80" rx="10" fill="#FFF59D" stroke="#F9A825" stroke-width="2.5"/>
    <text x="700" y="85" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="15" font-weight="700" fill="#F57F17">LED GPIO 2 ON</text>
    <text x="700" y="108" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#333">+ baris Serial gerak=YA</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Intinya:</strong> PIR mengeluarkan sinyal digital (HIGH/LOW), bukan angka suhu. Board hanya “mendengar” pin, lalu menyalakan LED. Sumber gambar: diagram buatan Koding Indonesia (FS-25).
  </figcaption>
</figure>
HTML;
    }

    private function flowSvgEn(): string
    {
        return <<<'HTML'
<figure role="img" aria-label="Flow: motion to PIR to LED" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 160" width="100%" height="auto" style="display:block;max-height:190px">
    <rect width="860" height="160" fill="#F5F5F0"/>
    <text x="430" y="28" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#1a1a1a">Flow: motion → read → light the “lamp”</text>
    <rect x="40" y="50" width="200" height="80" rx="10" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
    <text x="140" y="85" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="15" font-weight="700" fill="#1B5E20">Hand wave</text>
    <text x="140" y="108" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#333">in front of the PIR lens</text>
    <text x="270" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="22" fill="#1a1a1a">→</text>
    <rect x="300" y="50" width="220" height="80" rx="10" fill="#BBDEFB" stroke="#1565C0" stroke-width="2.5"/>
    <text x="410" y="85" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="15" font-weight="700" fill="#0D47A1">PIR OUT HIGH</text>
    <text x="410" y="108" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#333">GPIO 25 = 1</text>
    <text x="550" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="22" fill="#1a1a1a">→</text>
    <rect x="580" y="50" width="240" height="80" rx="10" fill="#FFF59D" stroke="#F9A825" stroke-width="2.5"/>
    <text x="700" y="85" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="15" font-weight="700" fill="#F57F17">LED GPIO 2 ON</text>
    <text x="700" y="108" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#333">+ Serial line motion=YES</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>In short:</strong> the PIR outputs a digital signal (HIGH/LOW), not a temperature number. The board only “listens” to a pin, then turns on an LED. Image source: diagram by Koding Indonesia (FS-25).
  </figcaption>
</figure>
HTML;
    }

    private function serialPanelSvgId(): string
    {
        return <<<'HTML'
<figure role="img" aria-label="Contoh log gerak YA/tidak di Serial Monitor baud 115200" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 340" width="100%" height="auto" role="img" aria-label="Serial Monitor PIR">
  <rect width="860" height="340" fill="#F5F5F0"/>
  <text x="430" y="28" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#1a1a1a">Serial Monitor (IDE 2) — contoh sukses FS-25</text>
  <rect x="40" y="48" width="780" height="40" rx="6" fill="#ECEFF1" stroke="#90A4AE" stroke-width="2"/>
  <text x="56" y="74" font-family="Segoe UI,sans-serif" font-size="13" fill="#37474F">Toolbar IDE 2 · Open Serial Monitor →</text>
  <text x="780" y="74" text-anchor="end" font-family="Segoe UI,sans-serif" font-size="13" font-weight="700" fill="#1565C0">Baud: 115200</text>
  <rect x="40" y="100" width="780" height="200" rx="8" fill="#263238" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="60" y="130" font-family="Consolas,monospace" font-size="14" fill="#80CBC4">FS25_pir_gerak siap — tunggu settle, lalu gerakkan tangan</text>
  <text x="60" y="158" font-family="Consolas,monospace" font-size="14" fill="#B0BEC5">gerak=tidak | LED=OFF</text>
  <text x="60" y="186" font-family="Consolas,monospace" font-size="14" fill="#B0BEC5">gerak=tidak | LED=OFF</text>
  <text x="60" y="214" font-family="Consolas,monospace" font-size="14" fill="#FFF59D">gerak=YA | LED=ON</text>
  <text x="60" y="242" font-family="Consolas,monospace" font-size="14" fill="#FFF59D">gerak=YA | LED=ON</text>
  <text x="60" y="270" font-family="Consolas,monospace" font-size="14" fill="#B0BEC5">gerak=tidak | LED=OFF</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Intinya:</strong> setelah Upload, buka Serial Monitor baud <strong>115200</strong>. Sukses = baris <code>gerak=YA</code> selaras LED saat kamu menggerakkan tangan (setelah settle). Sumber gambar: diagram buatan Koding Indonesia (FS-25). Panduan: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-serial-monitor/" rel="noopener noreferrer" target="_blank">Arduino Docs — Serial Monitor</a>.
  </figcaption>
</figure>
HTML;
    }

    private function serialPanelSvgEn(): string
    {
        return <<<'HTML'
<figure role="img" aria-label="Sample motion YES/no log in Serial Monitor at 115200 baud" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 340" width="100%" height="auto" role="img" aria-label="Serial Monitor PIR">
  <rect width="860" height="340" fill="#F5F5F0"/>
  <text x="430" y="28" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#1a1a1a">Serial Monitor (IDE 2) — FS-25 success sample</text>
  <rect x="40" y="48" width="780" height="40" rx="6" fill="#ECEFF1" stroke="#90A4AE" stroke-width="2"/>
  <text x="56" y="74" font-family="Segoe UI,sans-serif" font-size="13" fill="#37474F">IDE 2 toolbar · Open Serial Monitor →</text>
  <text x="780" y="74" text-anchor="end" font-family="Segoe UI,sans-serif" font-size="13" font-weight="700" fill="#1565C0">Baud: 115200</text>
  <rect x="40" y="100" width="780" height="200" rx="8" fill="#263238" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="60" y="130" font-family="Consolas,monospace" font-size="14" fill="#80CBC4">FS25_pir_gerak ready — wait for settle, then wave your hand</text>
  <text x="60" y="158" font-family="Consolas,monospace" font-size="14" fill="#B0BEC5">motion=no | LED=OFF</text>
  <text x="60" y="186" font-family="Consolas,monospace" font-size="14" fill="#B0BEC5">motion=no | LED=OFF</text>
  <text x="60" y="214" font-family="Consolas,monospace" font-size="14" fill="#FFF59D">motion=YES | LED=ON</text>
  <text x="60" y="242" font-family="Consolas,monospace" font-size="14" fill="#FFF59D">motion=YES | LED=ON</text>
  <text x="60" y="270" font-family="Consolas,monospace" font-size="14" fill="#B0BEC5">motion=no | LED=OFF</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>In short:</strong> after Upload, open Serial Monitor at baud <strong>115200</strong>. Success = <code>motion=YES</code> lines matching the LED when you wave (after settle). Image source: diagram by Koding Indonesia (FS-25). Guide: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-serial-monitor/" rel="noopener noreferrer" target="_blank">Arduino Docs — Serial Monitor</a>.
  </figcaption>
</figure>
HTML;
    }

    private function body(): string
    {
        $ide = $this->ideFigureId();
        $board = $this->boardFigureId();
        $kit = $this->kitPirFigureId();
        $main = $this->mainWiringFigureId();
        $schema = $this->schemaWiringFigureId();
        $flow = $this->flowSvgId();
        $serial = $this->serialPanelSvgId();

        return <<<HTML
<h2>Pendahuluan — mata gerak di meja</h2>
<p>Artikel ini adalah <strong>#95 (ini)</strong> · modul <strong>FS-25</strong> di jalur <em>Full Stack IoT Developer — Dari Nol</em> (fase <strong>BUILDER</strong>). Di <strong>FS-19</strong> kamu membaca tombol (HIGH/LOW). Hari ini indra baru: <strong>PIR</strong> — “ada orang bergerak di depan meja?”</p>
<p><strong>Analogi:</strong> seperti sensor lampu koridor hotel. Dia tidak menghitung berapa orang; dia hanya bilang <em>ada gerak</em> atau <em>tidak</em>. LED di GPIO 2 = “lampu meja” mini.</p>
{$flow}
<p><strong>Prasyarat:</strong> FS-19 (tombol + debounce / digitalRead) · FS-14 (Upload + Serial Monitor). Pin mengikuti tabel global FS-17 (<strong>PIR OUT = GPIO 25</strong>).</p>
<p><strong>Cara pakai artikel ini (urutan kerja):</strong></p>
<ol>
<li>Rakit wiring PIR + LED (cocokkan gambar utama).</li>
<li><strong>Buka Arduino IDE dulu</strong> (bukan Laragon / terminal web).</li>
<li>Buat sketch <code>FS25_pir_gerak</code> → salin kode → <strong>Verify</strong> → <strong>Upload</strong>.</li>
<li>Buka <strong>Serial Monitor</strong> baud <strong>115200</strong>.</li>
<li><strong>Tunggu 30–60 detik</strong> (settle) — jangan panik jika LED berkedip dulu.</li>
<li>Gerakkan tangan di depan lensa → <code>gerak=YA</code> + LED nyala.</li>
<li>Centang checklist 10/10 di browser.</li>
</ol>
<p><strong>Tidak perlu hari ini:</strong> Wi-Fi, MQTT, Laragon, <code>php artisan</code>, Library Manager baru, beban AC. Tools hari ini: <strong>Arduino IDE</strong> + <strong>Upload</strong> + ESP32 + modul PIR HC-SR501 + (opsional) LED/resistor + jumper + <strong>Serial Monitor</strong> + <strong>browser</strong>.</p>

<h2>Persiapan — buka &amp; siapkan ini dulu</h2>
<p><strong>Urutan meja kerja:</strong> wiring → buka IDE → Upload → Serial → tunggu settle → uji gerak.</p>
{$ide}
<ul>
<li>Buka <strong>Arduino IDE 2.x</strong> · board <strong>ESP32 Dev Module</strong> + port.</li>
<li>Siapkan ESP32 + USB data.</li>
<li>Siapkan modul <strong>PIR HC-SR501</strong> + jumper.</li>
<li>Cari label <strong>GPIO 25</strong> / <strong>IO25</strong>, <strong>GPIO 2</strong> / <strong>IO2</strong>, <strong>5V</strong>, dan <strong>GND</strong>.</li>
<li>Siapkan Serial Monitor baud <strong>115200</strong>.</li>
</ul>
{$board}
<p><strong>Alat yang dipakai hari ini:</strong> Arduino IDE, Upload, ESP32, USB data, PIR HC-SR501, jumper, Serial Monitor, browser. LED eksternal opsional (banyak board punya LED di GPIO 2).</p>
<p><strong>Tidak dipakai hari ini:</strong> Laragon, <code>php artisan</code>, Library Manager (tidak ada library baru), Wi-Fi, MQTT.</p>

<h2>Wiring (bahasa manusia)</h2>
{$kit}
{$main}
{$schema}
<p><strong>Blok PIR (indra):</strong></p>
<ul>
<li><strong>VCC</strong> modul → pin <strong>5V</strong> ESP32 (HC-SR501 biasanya butuh 5V — jangan paksa 3V3)</li>
<li><strong>OUT</strong> → <strong>GPIO 25</strong> (sering tertulis <strong>IO25</strong>)</li>
<li><strong>GND</strong> → <strong>GND</strong> ESP32</li>
</ul>
<p><strong>Blok LED (“lampu meja”):</strong></p>
<ul>
<li>LED onboard GPIO 2 <em>atau</em> LED eksternal: anoda (+ resistor ~220 Ω, 1 kΩ juga boleh) → <strong>GPIO 2</strong>, katoda → <strong>GND</strong></li>
</ul>
<p><strong>Kenapa settle?</strong> Setelah power, chip PIR butuh waktu “tenang” sebelum bacaannya stabil. Di menit pertama, false trigger (LED kedip sendiri) masih wajar — tunggu dulu, baru uji tangan.</p>
<p><strong>Jumper H/L:</strong> untuk latihan, set ke <strong>H</strong> (ulang/repeatable) supaya gerak terus-menerus tetap terdeteksi. Potensiometer putar pelan — jangan ekstrem di awal.</p>

<h2>Praktik — sketch FS25_pir_gerak</h2>
<p>Tujuan: baca <code>digitalRead(25)</code>; jika HIGH → nyalakan LED GPIO 2; cetak <code>gerak=YA/tidak</code> ke Serial.</p>
<ol>
<li><strong>Buka Arduino IDE</strong> dulu (bukan Laragon).</li>
<li><strong>File → New Sketch</strong> → <strong>Simpan sebagai</strong> <code>FS25_pir_gerak</code>.</li>
<li>Ganti isi dengan kode di bawah (salin utuh).</li>
<li><strong>Verify</strong> → <strong>Upload</strong> → tunggu <em>Done uploading</em>.</li>
<li>Buka Serial Monitor baud <strong>115200</strong>.</li>
<li>Tunggu settle, lalu gerakkan tangan di depan lensa.</li>
</ol>
<pre><code class="language-cpp">// FS25_pir_gerak — Full Stack IoT FS-25
const int PIN_PIR = 25;  // tabel FS-17
const int PIN_LED = 2;   // LED onboard / eksternal

void setup() {
  pinMode(PIN_PIR, INPUT);
  pinMode(PIN_LED, OUTPUT);
  digitalWrite(PIN_LED, LOW);
  Serial.begin(115200);
  delay(500);
  Serial.println("FS25_pir_gerak siap — tunggu settle, lalu gerakkan tangan");
}

void loop() {
  int gerak = digitalRead(PIN_PIR);
  digitalWrite(PIN_LED, gerak ? HIGH : LOW);

  Serial.print("gerak=");
  Serial.print(gerak ? "YA" : "tidak");
  Serial.print(" | LED=");
  Serial.println(gerak ? "ON" : "OFF");
  delay(200);
}
</code></pre>
<p><strong>Cara menguji perintah di atas:</strong> uji di <strong>Arduino IDE + Upload + Serial Monitor</strong>. Setelah settle, gerakkan tangan di depan lensa putih → lihat <code>gerak=YA</code> + LED. Diam → <code>tidak</code>. Bukan perintah Laragon / web server.</p>
{$serial}

<h2 id="fsiot-pir-checklist">Praktik — checklist PIR</h2>
<p>Centang setiap langkah setelah kamu lakukan di meja. Target: <strong>10/10</strong>.</p>
<ul id="fsiot-pir-checklist-items">
<li>Arduino IDE sudah terbuka sebelum menulis kode</li>
<li>Wiring PIR: 5V–VCC, GPIO 25–OUT, GND bersama</li>
<li>LED GPIO 2 siap (onboard atau eksternal)</li>
<li>Paham: tunggu settle 30–60 detik setelah power</li>
<li>Paham: false trigger di menit pertama masih wajar</li>
<li>Sketch disimpan sebagai FS25_pir_gerak</li>
<li>Upload berhasil — Done uploading</li>
<li>Serial baud 115200 menampilkan gerak=YA/tidak</li>
<li>Gerak tangan selaras LED ON (setelah settle)</li>
<li>Sadar: fondasi sensor event sebelum servo FS-26</li>
</ul>
<p><strong>Cara menguji checklist:</strong> centang di browser setelah praktik di IDE + board. Tidak perlu <code>php artisan</code>.</p>

<h2>Kesalahan yang sering terjadi</h2>
<ul>
<li><strong>Uji terlalu cepat setelah colok USB.</strong> Tunggu settle — jangan simpulkan “PIR rusak” di 10 detik pertama.</li>
<li><strong>VCC PIR ke 3V3.</strong> Banyak HC-SR501 butuh 5V; baca silkscreen / coba 5V dulu.</li>
<li><strong>Salah pin (bukan GPIO 25).</strong> Cocokkan silkscreen <strong>IO25</strong> — nomor kolom breadboard bukan GPIO.</li>
<li><strong>Jumper L + harapan trigger terus.</strong> Mode L = sekali lalu diam sampai delay habis; coba H untuk latihan.</li>
<li><strong>Menguji di terminal web.</strong> Sketch hanya jalan di board lewat IDE Upload — bukan Laragon.</li>
<li><strong>Mengira PIR = kamera.</strong> PIR hanya “ada gerak panas”, bukan mengenali wajah.</li>
</ul>

<h2>Selanjutnya</h2>
<p><strong>Intinya:</strong> kalau Serial menunjukkan <code>gerak=YA</code> selaras LED saat tangan bergerak (setelah settle), FS-25 selesai — kamu punya sensor event untuk “ada orang di meja?”.</p>
<p>Lanjut ke <strong>FS-26</strong> (servo / gerakan sudut dengan PWM) saat modulnya terbit. Relay GPIO 26 bisa digabung nanti sebagai “lampu” yang lebih nyata (pola FS-23).</p>
<p>Daftar modul lengkap: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $ide = $this->ideFigureEn();
        $board = $this->boardFigureEn();
        $kit = $this->kitPirFigureEn();
        $main = $this->mainWiringFigureEn();
        $schema = $this->schemaWiringFigureEn();
        $flow = $this->flowSvgEn();
        $serial = $this->serialPanelSvgEn();

        return <<<HTML
<h2>Introduction — a motion eye on the desk</h2>
<p>This is article <strong>#95 (this article)</strong> · module <strong>FS-25</strong> on the <em>Full Stack IoT Developer — From Zero</em> path (<strong>BUILDER</strong> phase). In <strong>FS-19</strong> you read a button (HIGH/LOW). Today a new sense: <strong>PIR</strong> — “is someone moving in front of the desk?”</p>
<p><strong>Analogy:</strong> like a hotel hallway light sensor. It does not count people; it only says <em>motion</em> or <em>no motion</em>. The LED on GPIO 2 is a mini “desk lamp”.</p>
{$flow}
<p><strong>Prerequisites:</strong> FS-19 (button + debounce / digitalRead) · FS-14 (Upload + Serial Monitor). Pins follow the FS-17 global table (<strong>PIR OUT = GPIO 25</strong>).</p>
<p><strong>How to use this article (work order):</strong></p>
<ol>
<li>Wire the PIR + LED (match the main figure).</li>
<li><strong>Open Arduino IDE first</strong> (not Laragon / a web terminal).</li>
<li>Create sketch <code>FS25_pir_gerak</code> → paste the code → <strong>Verify</strong> → <strong>Upload</strong>.</li>
<li>Open <strong>Serial Monitor</strong> at baud <strong>115200</strong>.</li>
<li><strong>Wait 30–60 seconds</strong> (settle) — don’t panic if the LED blinks at first.</li>
<li>Wave your hand in front of the lens → <code>motion=YES</code> + LED on.</li>
<li>Tick the checklist 10/10 in the browser.</li>
</ol>
<p><strong>Not needed today:</strong> Wi-Fi, MQTT, Laragon, <code>php artisan</code>, a new Library Manager install, AC loads. Today's tools: <strong>Arduino IDE</strong> + <strong>Upload</strong> + ESP32 + HC-SR501 PIR + (optional) LED/resistor + jumpers + <strong>Serial Monitor</strong> + <strong>browser</strong>.</p>

<h2>Preparation — open &amp; set these up first</h2>
<p><strong>Desk order:</strong> wiring → open IDE → Upload → Serial → wait for settle → motion test.</p>
{$ide}
<ul>
<li>Open <strong>Arduino IDE 2.x</strong> · board <strong>ESP32 Dev Module</strong> + port.</li>
<li>Prepare the ESP32 + data USB cable.</li>
<li>Prepare an <strong>HC-SR501 PIR</strong> module + jumpers.</li>
<li>Find <strong>GPIO 25</strong> / <strong>IO25</strong>, <strong>GPIO 2</strong> / <strong>IO2</strong>, <strong>5V</strong>, and <strong>GND</strong>.</li>
<li>Prepare Serial Monitor at baud <strong>115200</strong>.</li>
</ul>
{$board}
<p><strong>Tools used today:</strong> Arduino IDE, Upload, ESP32, USB data, HC-SR501 PIR, jumpers, Serial Monitor, browser. External LED optional (many boards already have an LED on GPIO 2).</p>
<p><strong>Not used today:</strong> Laragon, <code>php artisan</code>, Library Manager (no new library), Wi-Fi, MQTT.</p>

<h2>Wiring (human language)</h2>
{$kit}
{$main}
{$schema}
<p><strong>PIR block (sense):</strong></p>
<ul>
<li>Module <strong>VCC</strong> → ESP32 <strong>5V</strong> (HC-SR501 usually needs 5V — don’t force 3V3)</li>
<li><strong>OUT</strong> → <strong>GPIO 25</strong> (often labeled <strong>IO25</strong>)</li>
<li><strong>GND</strong> → ESP32 <strong>GND</strong></li>
</ul>
<p><strong>LED block (“desk lamp”):</strong></p>
<ul>
<li>Onboard LED on GPIO 2 <em>or</em> external LED: anode (+ ~220 Ω resistor; 1 kΩ is fine too) → <strong>GPIO 2</strong>, cathode → <strong>GND</strong></li>
</ul>
<p><strong>Why settle?</strong> After power-up the PIR chip needs quiet time before readings stabilize. In the first minute, false triggers (LED blinking alone) are still normal — wait, then test with your hand.</p>
<p><strong>H/L jumper:</strong> for practice, set <strong>H</strong> (repeatable) so continuous motion keeps retriggering. Turn the potentiometers gently — avoid extremes at first.</p>

<h2>Practice — sketch FS25_pir_gerak</h2>
<p>Goal: read <code>digitalRead(25)</code>; if HIGH → turn on LED GPIO 2; print <code>motion=YES/no</code> to Serial.</p>
<ol>
<li><strong>Open Arduino IDE</strong> first (not Laragon).</li>
<li><strong>File → New Sketch</strong> → <strong>Save As</strong> <code>FS25_pir_gerak</code>.</li>
<li>Replace the contents with the code below (copy whole).</li>
<li><strong>Verify</strong> → <strong>Upload</strong> → wait for <em>Done uploading</em>.</li>
<li>Open Serial Monitor at baud <strong>115200</strong>.</li>
<li>Wait for settle, then wave your hand in front of the lens.</li>
</ol>
<pre><code class="language-cpp">// FS25_pir_gerak — Full Stack IoT FS-25
const int PIN_PIR = 25;  // FS-17 table
const int PIN_LED = 2;   // onboard / external LED

void setup() {
  pinMode(PIN_PIR, INPUT);
  pinMode(PIN_LED, OUTPUT);
  digitalWrite(PIN_LED, LOW);
  Serial.begin(115200);
  delay(500);
  Serial.println("FS25_pir_gerak ready — wait for settle, then wave your hand");
}

void loop() {
  int motion = digitalRead(PIN_PIR);
  digitalWrite(PIN_LED, motion ? HIGH : LOW);

  Serial.print("motion=");
  Serial.print(motion ? "YES" : "no");
  Serial.print(" | LED=");
  Serial.println(motion ? "ON" : "OFF");
  delay(200);
}
</code></pre>
<p><strong>How to test the commands above:</strong> test in <strong>Arduino IDE + Upload + Serial Monitor</strong>. After settle, wave your hand in front of the white lens → see <code>motion=YES</code> + LED. Stay still → <code>no</code>. Not a Laragon / web-server command.</p>
{$serial}

<h2 id="fsiot-pir-checklist">Practice — PIR checklist</h2>
<p>Tick each step after you do it at the desk. Target: <strong>10/10</strong>.</p>
<ul id="fsiot-pir-checklist-items">
<li>Arduino IDE is open before writing code</li>
<li>PIR wiring: 5V–VCC, GPIO 25–OUT, shared GND</li>
<li>GPIO 2 LED ready (onboard or external)</li>
<li>I understand: wait 30–60 seconds to settle after power</li>
<li>I understand: false triggers in the first minute can be normal</li>
<li>Sketch saved as FS25_pir_gerak</li>
<li>Upload succeeded — Done uploading</li>
<li>Serial at 115200 shows motion=YES/no</li>
<li>Hand motion matches LED ON (after settle)</li>
<li>I know: this is the event-sensor foundation before servo FS-26</li>
</ul>
<p><strong>How to test the checklist:</strong> tick in the browser after practice on the IDE + board. No <code>php artisan</code> needed.</p>

<h2>Common mistakes</h2>
<ul>
<li><strong>Testing too soon after plugging USB in.</strong> Wait for settle — don’t conclude “broken PIR” in the first 10 seconds.</li>
<li><strong>PIR VCC on 3V3.</strong> Many HC-SR501 modules need 5V; read the silkscreen / try 5V first.</li>
<li><strong>Wrong pin (not GPIO 25).</strong> Match silkscreen <strong>IO25</strong> — breadboard column numbers are not GPIO numbers.</li>
<li><strong>Jumper L while expecting continuous triggers.</strong> Mode L = once, then quiet until the delay ends; try H for practice.</li>
<li><strong>Testing in a web terminal.</strong> The sketch runs on the board via IDE Upload only — not Laragon.</li>
<li><strong>Thinking PIR is a camera.</strong> PIR only means “warm motion present”, not face recognition.</li>
</ul>

<h2>Next</h2>
<p><strong>In short:</strong> if Serial shows <code>motion=YES</code> matching the LED when your hand moves (after settle), FS-25 is done — you have an event sensor for “someone at the desk?”.</p>
<p>Continue to <strong>FS-26</strong> (servo / angled motion with PWM) when that module publishes. GPIO 26 relay can join later as a more “real” lamp (FS-23 pattern).</p>
<p>Full module list: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }
}
