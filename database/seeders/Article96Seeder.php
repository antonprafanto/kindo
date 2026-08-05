<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article96Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first()
            ?? Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $iotCat) {
            throw new \RuntimeException('User atau kategori iot-smart-device/esp32-arduino tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-servo-pwm';

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
                'title'              => 'Servo: gerakan sudut dengan PWM',
                'title_en'           => 'Servo: angular motion with PWM',
                'excerpt'            => 'FS-26 / #96: servo SG90 di GPIO 13. Sapu 0°→90°→180° di Arduino IDE — pasang library ESP32Servo dulu.',
                'excerpt_en'         => 'FS-26 / #96: SG90 servo on GPIO 13. Sweep 0°→90°→180° in Arduino IDE — install ESP32Servo first.',
                'body'               => $this->body(),
                'body_en'            => $this->bodyEn(),
                'status'             => 'draft',
                'is_featured'        => false,
                'published_at'       => null,
                'seo_title'          => 'Servo SG90 PWM — Full Stack IoT #96',
                'seo_title_en'       => 'SG90 servo PWM — Full Stack IoT #96',
                'seo_description'    => 'Gerakkan servo SG90 di ESP32: GPIO 13, library ESP32Servo, sudut 0/90/180, peringatan daya 5V. Modul FS-26.',
                'seo_description_en' => 'Move an SG90 servo on ESP32: GPIO 13, ESP32Servo library, angles 0/90/180, 5V power warning. FS-26 module.',
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
            $src = public_path('images/fsiot/fs26-cover-servo.jpg');
            if (is_file($src)) {
                $dest = 'articles/covers/fs26-cover-servo.jpg';
                \Illuminate\Support\Facades\Storage::disk('public')->put($dest, file_get_contents($src));
                $article->cover_image = $dest;
                $article->save();
            }
        }

        $this->command?->info('✓ Artikel #96 / FS-26 tersimpan sebagai DRAFT: '.$article->title);
    }

    private function ideFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem">
  <img src="/images/fsiot/fs11-ide-overview-cite.png" width="1280" height="720" alt="Arduino IDE 2 — Verify, Upload, dan Serial Monitor" loading="eager" style="width:100%;height:auto;max-height:420px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Arduino IDE 2</strong> — tempat menguji sintaks hari ini. Urutan tools: buka IDE → <strong>Library Manager</strong> pasang <strong>ESP32Servo</strong> → <strong>Verify</strong> → <strong>Upload</strong> → buka <strong>Serial Monitor</strong> (baud <strong>115200</strong>). Board: <strong>ESP32 Dev Module</strong>. <em>Catatan gambar:</em> screenshot Commons masih menampilkan AnalogReadSerial + baud 9600 — <strong>abaikan</strong>; untuk FS-26 pakai kode di bawah + baud 115200.
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:Ide-2-overview.png" rel="noopener noreferrer" target="_blank">Arduino IDE 2 overview</a> · Wikimedia Commons (CC BY-SA 3.0). Panduan Serial: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-serial-monitor/" rel="noopener noreferrer" target="_blank">Arduino Docs — Serial Monitor</a>. Konsep servo: <a href="https://docs.arduino.cc/learn/electronics/servo-motors/" rel="noopener noreferrer" target="_blank">Arduino Docs — Servo Motors</a>.
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
    <strong>Arduino IDE 2</strong> — where today’s syntax is tested. Tool order: open the IDE → <strong>Library Manager</strong> install <strong>ESP32Servo</strong> → <strong>Verify</strong> → <strong>Upload</strong> → open <strong>Serial Monitor</strong> (baud <strong>115200</strong>). Board: <strong>ESP32 Dev Module</strong>. <em>Image note:</em> the Commons screenshot still shows AnalogReadSerial + baud 9600 — <strong>ignore</strong> it; for FS-26 use the code below + baud 115200.
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:Ide-2-overview.png" rel="noopener noreferrer" target="_blank">Arduino IDE 2 overview</a> · Wikimedia Commons (CC BY-SA 3.0). Serial guide: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-serial-monitor/" rel="noopener noreferrer" target="_blank">Arduino Docs — Serial Monitor</a>. Servo concept: <a href="https://docs.arduino.cc/learn/electronics/servo-motors/" rel="noopener noreferrer" target="_blank">Arduino Docs — Servo Motors</a>.
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
    <strong>ESP32-DevKitC</strong> — USB data di <strong>(6)</strong>, reset di <strong>EN (7)</strong>. Pin hari ini: <strong>GPIO 13</strong> / <strong>IO13</strong> (sinyal servo) · plus <strong>5V</strong> dan <strong>GND</strong> (tabel FS-17).
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
    <strong>ESP32-DevKitC</strong> — USB data at <strong>(6)</strong>, reset on <strong>EN (7)</strong>. Pins today: <strong>GPIO 13</strong> / <strong>IO13</strong> (servo signal) · plus <strong>5V</strong> and <strong>GND</strong> (FS-17 table).
    <br>Image source: <a href="https://docs.espressif.com/projects/esp-idf/en/latest/esp32/hw-reference/esp32/get-started-devkitc.html" rel="noopener noreferrer" target="_blank">Espressif — ESP32-DevKitC user guide</a>.
  </figcaption>
</figure>
HTML;
    }

    private function kitServoFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem">
  <img src="/images/fsiot/kit-servo-sg90.jpg" width="1200" height="800" alt="Micro servo Tower Pro SG90 dengan kabel 3 warna" loading="eager" style="display:block;width:100%;max-width:520px;height:auto;max-height:360px;object-fit:contain;margin:0 auto;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Otot hari ini:</strong> micro servo <strong>SG90</strong> (sering ada tulisan Tower Pro). Tiga kabel tipikal: <strong>merah = VCC (5V)</strong> · <strong>oranye/kuning = Signal</strong> → <strong>GPIO 13</strong> · <strong>cokelat/hitam = GND</strong>. Warna clone bisa beda — pastikan urutan di konektor 3 pin.
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:Tower_Pro_SG90_micro_servo_motor.jpg" rel="noopener noreferrer" target="_blank">Tower Pro SG90 micro servo motor.jpg</a> · Wikimedia Commons (CC BY-SA 4.0) · Suyash Dwivedi. Acuan konsep: <a href="https://docs.arduino.cc/learn/electronics/servo-motors/" rel="noopener noreferrer" target="_blank">Arduino Docs — Servo Motors</a>.
  </figcaption>
</figure>
HTML;
    }

    private function kitServoFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem">
  <img src="/images/fsiot/kit-servo-sg90.jpg" width="1200" height="800" alt="Tower Pro SG90 micro servo with three-color cable" loading="eager" style="display:block;width:100%;max-width:520px;height:auto;max-height:360px;object-fit:contain;margin:0 auto;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Actuator today:</strong> an <strong>SG90</strong> micro servo (often labeled Tower Pro). Typical three wires: <strong>red = VCC (5V)</strong> · <strong>orange/yellow = Signal</strong> → <strong>GPIO 13</strong> · <strong>brown/black = GND</strong>. Clone colors can differ — check the 3-pin connector order.
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:Tower_Pro_SG90_micro_servo_motor.jpg" rel="noopener noreferrer" target="_blank">Tower Pro SG90 micro servo motor.jpg</a> · Wikimedia Commons (CC BY-SA 4.0) · Suyash Dwivedi. Concept guide: <a href="https://docs.arduino.cc/learn/electronics/servo-motors/" rel="noopener noreferrer" target="_blank">Arduino Docs — Servo Motors</a>.
  </figcaption>
</figure>
HTML;
    }

    private function mainWiringFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs26-servo-breadboard.png" width="1238" height="737" alt="Gambar utama — rangkaian servo SG90 GPIO 13 di breadboard" loading="eager" style="width:100%;height:auto;max-height:560px;object-fit:contain;border-radius:6px;background:#fff">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Gambar utama — rangkaian servo di breadboard.</strong> Merah: <strong>5V</strong> → VCC servo · kuning/oranye: <strong>Signal</strong> → <strong>IO13 / GPIO 13</strong> · hitam: <strong>GND</strong> bersama (rail biru). Jangan isi VCC dari <strong>3V3</strong>.
    <br><em>Tip:</em> nomor kolom breadboard ≠ nomor GPIO. Warna kabel dari badan servo (cokelat/merah/kuning) boleh beda dari jumper — ikuti fungsi VCC / Signal / GND. Kalau ESP32 reset saat lengan bergerak, pindahkan daya servo ke adaptor 5V terpisah (GND tetap bersama).
    <br>Sumber gambar: diagram rangkaian buatan Koding Indonesia (FS-26).
  </figcaption>
</figure>
HTML;
    }

    private function mainWiringFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs26-servo-breadboard.png" width="1238" height="737" alt="Main figure — SG90 servo GPIO 13 on a breadboard" loading="eager" style="width:100%;height:auto;max-height:560px;object-fit:contain;border-radius:6px;background:#fff">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Main figure — servo circuit on a breadboard.</strong> Red: <strong>5V</strong> → servo VCC · yellow/orange: <strong>Signal</strong> → <strong>IO13 / GPIO 13</strong> · black: shared <strong>GND</strong> (blue rail). Do not power VCC from <strong>3V3</strong>.
    <br><em>Tip:</em> breadboard column numbers ≠ GPIO numbers. Servo lead colors (brown/red/yellow) may differ from jumpers — follow VCC / Signal / GND. If the ESP32 resets when the arm moves, move servo power to a separate 5V supply (keep a common GND).
    <br>Image source: wiring diagram by Koding Indonesia (FS-26).
  </figcaption>
</figure>
HTML;
    }

    private function schemaWiringFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs26-servo-wiring.png" width="1100" height="860" alt="Skema bantu — ringkasan pin servo SG90 GPIO 13" loading="eager" style="width:100%;height:auto;max-height:520px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Skema bantu (ringkas).</strong> Pin sama gambar utama: Signal GPIO 13 · VCC 5V · GND bersama · sapu 0°/90°/180°. Pakai ini jika kamu lebih nyaman membaca kotak pin daripada foto breadboard.
    <br>Sumber gambar: diagram berlabel buatan Koding Indonesia (FS-26).
  </figcaption>
</figure>
HTML;
    }

    private function schemaWiringFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs26-servo-wiring.png" width="1100" height="860" alt="Helper schematic — SG90 servo GPIO 13 pin summary" loading="eager" style="width:100%;height:auto;max-height:520px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Helper schematic.</strong> Same pins as the main figure: Signal GPIO 13 · VCC 5V · shared GND · sweep 0°/90°/180°. Use this if you prefer labeled pin boxes over the breadboard photo.
    <br>Image source: labeled diagram by Koding Indonesia (FS-26).
  </figcaption>
</figure>
HTML;
    }


    private function flowSvgId(): string
    {
        return <<<'HTML'
<figure role="img" aria-label="Alur: perintah sudut ke PWM ke gerak servo" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 160" width="100%" height="auto" style="display:block;max-height:190px">
    <rect width="860" height="160" fill="#F5F5F0"/>
    <text x="430" y="28" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#1a1a1a">Alur: angka sudut → PWM → lengan bergerak</text>
    <rect x="30" y="50" width="220" height="80" rx="10" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
    <text x="140" y="85" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="15" font-weight="700" fill="#1B5E20">write(0 / 90 / 180)</text>
    <text x="140" y="108" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#333">perintah di sketch</text>
    <text x="270" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="22" fill="#1a1a1a">→</text>
    <rect x="300" y="50" width="220" height="80" rx="10" fill="#BBDEFB" stroke="#1565C0" stroke-width="2.5"/>
    <text x="410" y="85" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="15" font-weight="700" fill="#0D47A1">PWM di GPIO 13</text>
    <text x="410" y="108" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#333">bukan “terang LED”</text>
    <text x="550" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="22" fill="#1a1a1a">→</text>
    <rect x="580" y="50" width="250" height="80" rx="10" fill="#FFF59D" stroke="#F9A825" stroke-width="2.5"/>
    <text x="705" y="85" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="15" font-weight="700" fill="#F57F17">Servo ke posisi</text>
    <text x="705" y="108" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#333">+ baris Serial sudut=…</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Intinya:</strong> di FS-20 PWM mengatur <em>terang</em> LED. Di FS-26 PWM mengatur <em>posisi sudut</em> servo. Sumber gambar: diagram buatan Koding Indonesia (FS-26).
  </figcaption>
</figure>
HTML;
    }

    private function flowSvgEn(): string
    {
        return <<<'HTML'
<figure role="img" aria-label="Flow: angle command to PWM to servo motion" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 160" width="100%" height="auto" style="display:block;max-height:190px">
    <rect width="860" height="160" fill="#F5F5F0"/>
    <text x="430" y="28" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#1a1a1a">Flow: angle number → PWM → arm moves</text>
    <rect x="30" y="50" width="220" height="80" rx="10" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
    <text x="140" y="85" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="15" font-weight="700" fill="#1B5E20">write(0 / 90 / 180)</text>
    <text x="140" y="108" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#333">command in the sketch</text>
    <text x="270" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="22" fill="#1a1a1a">→</text>
    <rect x="300" y="50" width="220" height="80" rx="10" fill="#BBDEFB" stroke="#1565C0" stroke-width="2.5"/>
    <text x="410" y="85" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="15" font-weight="700" fill="#0D47A1">PWM on GPIO 13</text>
    <text x="410" y="108" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#333">not “LED brightness”</text>
    <text x="550" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="22" fill="#1a1a1a">→</text>
    <rect x="580" y="50" width="250" height="80" rx="10" fill="#FFF59D" stroke="#F9A825" stroke-width="2.5"/>
    <text x="705" y="85" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="15" font-weight="700" fill="#F57F17">Servo to a position</text>
    <text x="705" y="108" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#333">+ Serial line angle=…</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>In short:</strong> in FS-20 PWM sets LED <em>brightness</em>. In FS-26 PWM sets servo <em>angle</em>. Image source: diagram by Koding Indonesia (FS-26).
  </figcaption>
</figure>
HTML;
    }

    private function serialPanelSvgId(): string
    {
        return <<<'HTML'
<figure role="img" aria-label="Contoh log sudut servo di Serial Monitor baud 115200" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 340" width="100%" height="auto" role="img" aria-label="Serial Monitor servo">
  <rect width="860" height="340" fill="#F5F5F0"/>
  <text x="430" y="28" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#1a1a1a">Serial Monitor (IDE 2) — contoh sukses FS-26</text>
  <rect x="40" y="48" width="780" height="40" rx="6" fill="#ECEFF1" stroke="#90A4AE" stroke-width="2"/>
  <text x="56" y="74" font-family="Segoe UI,sans-serif" font-size="13" fill="#37474F">Toolbar IDE 2 · Open Serial Monitor →</text>
  <text x="780" y="74" text-anchor="end" font-family="Segoe UI,sans-serif" font-size="13" font-weight="700" fill="#1565C0">Baud: 115200</text>
  <rect x="40" y="100" width="780" height="200" rx="8" fill="#263238" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="60" y="130" font-family="Consolas,monospace" font-size="14" fill="#80CBC4">FS26_servo_sudut siap — sapu 0 → 90 → 180</text>
  <text x="60" y="158" font-family="Consolas,monospace" font-size="14" fill="#FFF59D">sudut=0</text>
  <text x="60" y="186" font-family="Consolas,monospace" font-size="14" fill="#FFF59D">sudut=90</text>
  <text x="60" y="214" font-family="Consolas,monospace" font-size="14" fill="#FFF59D">sudut=180</text>
  <text x="60" y="242" font-family="Consolas,monospace" font-size="14" fill="#FFF59D">sudut=90</text>
  <text x="60" y="270" font-family="Consolas,monospace" font-size="14" fill="#B0BEC5">…mengulang…</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Intinya:</strong> setelah Upload, buka Serial Monitor baud <strong>115200</strong>. Sukses = baris <code>sudut=…</code> selaras lengan servo yang bergerak pelan. Sumber gambar: diagram buatan Koding Indonesia (FS-26). Panduan: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-serial-monitor/" rel="noopener noreferrer" target="_blank">Arduino Docs — Serial Monitor</a>.
  </figcaption>
</figure>
HTML;
    }

    private function serialPanelSvgEn(): string
    {
        return <<<'HTML'
<figure role="img" aria-label="Sample servo angle log in Serial Monitor at 115200 baud" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 340" width="100%" height="auto" role="img" aria-label="Serial Monitor servo">
  <rect width="860" height="340" fill="#F5F5F0"/>
  <text x="430" y="28" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#1a1a1a">Serial Monitor (IDE 2) — FS-26 success sample</text>
  <rect x="40" y="48" width="780" height="40" rx="6" fill="#ECEFF1" stroke="#90A4AE" stroke-width="2"/>
  <text x="56" y="74" font-family="Segoe UI,sans-serif" font-size="13" fill="#37474F">IDE 2 toolbar · Open Serial Monitor →</text>
  <text x="780" y="74" text-anchor="end" font-family="Segoe UI,sans-serif" font-size="13" font-weight="700" fill="#1565C0">Baud: 115200</text>
  <rect x="40" y="100" width="780" height="200" rx="8" fill="#263238" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="60" y="130" font-family="Consolas,monospace" font-size="14" fill="#80CBC4">FS26_servo_sudut ready — sweep 0 → 90 → 180</text>
  <text x="60" y="158" font-family="Consolas,monospace" font-size="14" fill="#FFF59D">angle=0</text>
  <text x="60" y="186" font-family="Consolas,monospace" font-size="14" fill="#FFF59D">angle=90</text>
  <text x="60" y="214" font-family="Consolas,monospace" font-size="14" fill="#FFF59D">angle=180</text>
  <text x="60" y="242" font-family="Consolas,monospace" font-size="14" fill="#FFF59D">angle=90</text>
  <text x="60" y="270" font-family="Consolas,monospace" font-size="14" fill="#B0BEC5">…repeating…</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>In short:</strong> after Upload, open Serial Monitor at baud <strong>115200</strong>. Success = <code>angle=…</code> lines matching the servo arm moving slowly. Image source: diagram by Koding Indonesia (FS-26). Guide: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-serial-monitor/" rel="noopener noreferrer" target="_blank">Arduino Docs — Serial Monitor</a>.
  </figcaption>
</figure>
HTML;
    }

    private function body(): string
    {
        $ide = $this->ideFigureId();
        $board = $this->boardFigureId();
        $kit = $this->kitServoFigureId();
        $main = $this->mainWiringFigureId();
        $schema = $this->schemaWiringFigureId();
        $flow = $this->flowSvgId();
        $serial = $this->serialPanelSvgId();

        return <<<HTML
<h2>Pendahuluan — tangan kecil yang ikut perintah</h2>
<p>Artikel ini adalah <strong>#96 (ini)</strong> · modul <strong>FS-26</strong> di jalur <em>Full Stack IoT Developer — Dari Nol</em> (fase <strong>BUILDER</strong>). Di <strong>FS-20</strong> kamu mengatur terang LED dengan PWM. Hari ini PWM dipakai untuk <strong>posisi</strong>: lengan servo bergerak ke sudut tertentu.</p>
<p><strong>Analogi:</strong> seperti jarum jam yang bisa kamu suruh “menghadap jam 3 / jam 6 / jam 9”. Bukan kipas yang berputar terus — tapi “tangan kecil” yang berhenti di sudut.</p>
{$flow}
<p><strong>Prasyarat:</strong> FS-20 (PWM / <code>analogWrite</code>) · FS-14 (Upload + Serial Monitor). Pin mengikuti tabel FS-16 / FS-17 (<strong>sinyal servo = GPIO 13</strong>).</p>
<p><strong>Cara pakai artikel ini (urutan kerja):</strong></p>
<ol>
<li>Rakit wiring servo (cocokkan gambar utama).</li>
<li><strong>Buka Arduino IDE dulu</strong> (bukan Laragon / terminal web).</li>
<li><strong>Library Manager</strong> → cari <strong>ESP32Servo</strong> → Install.</li>
<li>Buat sketch <code>FS26_servo_sudut</code> → salin kode → <strong>Verify</strong> → <strong>Upload</strong>.</li>
<li>Buka <strong>Serial Monitor</strong> baud <strong>115200</strong>.</li>
<li>Lihat lengan sapu 0° → 90° → 180° selaras baris <code>sudut=…</code>.</li>
<li>Centang checklist 10/10 di browser.</li>
</ol>
<p><strong>Tidak perlu hari ini:</strong> Wi-Fi, MQTT, Laragon, <code>php artisan</code>, beban AC, motor DC besar. Tools hari ini: <strong>Arduino IDE</strong> + <strong>Library Manager (ESP32Servo)</strong> + <strong>Upload</strong> + ESP32 + servo SG90 + jumper + <strong>Serial Monitor</strong> + <strong>browser</strong>.</p>

<h2>Persiapan — buka &amp; siapkan ini dulu</h2>
<p><strong>Urutan meja kerja:</strong> wiring → buka IDE → pasang library → Upload → Serial → amati sudut.</p>
{$ide}
<ul>
<li>Buka <strong>Arduino IDE 2.x</strong> · board <strong>ESP32 Dev Module</strong> + port.</li>
<li>Siapkan ESP32 + USB data.</li>
<li>Siapkan servo <strong>SG90</strong> + jumper (3 kabel).</li>
<li>Cari label <strong>GPIO 13</strong> / <strong>IO13</strong>, <strong>5V</strong>, dan <strong>GND</strong>.</li>
<li>Siapkan Serial Monitor baud <strong>115200</strong>.</li>
</ul>
{$board}
<p><strong>Alat yang dipakai hari ini:</strong> Arduino IDE, Library Manager, Upload, ESP32, USB data, SG90, jumper, Serial Monitor, browser.</p>
<p><strong>Tidak dipakai hari ini:</strong> Laragon, <code>php artisan</code>, Wi-Fi, MQTT, relay/AC.</p>

<h2>Wiring (bahasa manusia)</h2>
{$kit}
{$main}
{$schema}
<p><strong>Blok servo:</strong></p>
<ul>
<li><strong>VCC</strong> (merah) → pin <strong>5V</strong> ESP32 — <strong>jangan</strong> 3V3</li>
<li><strong>Signal</strong> (oranye/kuning) → <strong>GPIO 13</strong> (sering tertulis <strong>IO13</strong>)</li>
<li><strong>GND</strong> (cokelat/hitam) → <strong>GND</strong> ESP32</li>
</ul>
<p><strong>Kenapa 5V?</strong> SG90 dirancang sekitar 4,8–6 V. Pin 3V3 sering membuat gerak lemah atau board “ngambek”. Satu SG90 ringan dari 5V USB biasanya cukup untuk latihan; kalau ESP32 reset saat lengan bergerak, pindahkan daya servo ke adaptor 5V terpisah dan sambungkan GND bersama.</p>
<p><strong>Servo vs LED PWM:</strong> di FS-20 angka 0–255 = seberapa terang. Di sini angka 0 / 90 / 180 = <em>sudut</em>. Sama-sama PWM, arti perintah berbeda.</p>

<h2>Praktik — sketch FS26_servo_sudut</h2>
<p>Tujuan: pasang library <strong>ESP32Servo</strong>, tempel servo di GPIO 13, sapu sudut 0→90→180→90 sambil mencetak ke Serial.</p>
<ol>
<li><strong>Buka Arduino IDE</strong> dulu (bukan Laragon).</li>
<li><strong>Sketch → Include Library → Manage Libraries…</strong> → cari <code>ESP32Servo</code> → <strong>Install</strong>.</li>
<li><strong>File → New Sketch</strong> → <strong>Simpan sebagai</strong> <code>FS26_servo_sudut</code>.</li>
<li>Ganti isi dengan kode di bawah (salin utuh).</li>
<li><strong>Verify</strong> → <strong>Upload</strong> → tunggu <em>Done uploading</em>.</li>
<li>Buka Serial Monitor baud <strong>115200</strong> dan amati lengan.</li>
</ol>
<pre><code class="language-cpp">// FS26_servo_sudut — Full Stack IoT FS-26
#include &lt;ESP32Servo.h&gt;

Servo lengan;
const int PIN_SERVO = 13;  // tabel FS-17

void setup() {
  Serial.begin(115200);
  lengan.attach(PIN_SERVO);
  delay(300);
  Serial.println("FS26_servo_sudut siap — sapu 0 → 90 → 180");
}

void loop() {
  int sudutList[] = {0, 90, 180, 90};
  for (int i = 0; i &lt; 4; i++) {
    int a = sudutList[i];
    lengan.write(a);
    Serial.print("sudut=");
    Serial.println(a);
    delay(1000);
  }
}
</code></pre>
<p><strong>Cara menguji perintah di atas:</strong> uji di <strong>Arduino IDE + Library Manager + Upload + Serial Monitor</strong>. Lihat lengan bergerak pelan selaras <code>sudut=0/90/180</code>. Bukan perintah Laragon / web server. Jika Verify gagal karena library, pastikan <strong>ESP32Servo</strong> sudah terpasang (bukan library Servo klasik Arduino Uno saja).</p>
{$serial}

<h2 id="fsiot-servo-checklist">Praktik — checklist servo</h2>
<p>Centang setiap langkah setelah kamu lakukan di meja. Target: <strong>10/10</strong>.</p>
<ul id="fsiot-servo-checklist-items">
<li>Arduino IDE sudah terbuka sebelum menulis kode</li>
<li>Library ESP32Servo terpasang lewat Library Manager</li>
<li>Wiring: 5V–VCC, GPIO 13–Signal, GND bersama</li>
<li>Paham: VCC servo bukan dari 3V3</li>
<li>Sketch disimpan sebagai FS26_servo_sudut</li>
<li>Upload berhasil — Done uploading</li>
<li>Serial baud 115200 menampilkan sudut=0/90/180</li>
<li>Lengan servo bergerak selaras Serial</li>
<li>Paham: PWM di sini = posisi, bukan terang LED</li>
<li>Sadar: fondasi aktuator sudut sebelum bus I2C FS-27</li>
</ul>
<p><strong>Cara menguji checklist:</strong> centang di browser setelah praktik di IDE + board. Tidak perlu <code>php artisan</code>.</p>

<h2>Kesalahan yang sering terjadi</h2>
<ul>
<li><strong>VCC ke 3V3.</strong> Pindahkan ke <strong>5V</strong>.</li>
<li><strong>ESP32 reset saat servo gerak.</strong> Arus drop — pakai adaptor 5V terpisah + GND bersama.</li>
<li><strong>Salah pin (bukan GPIO 13).</strong> Cocokkan silkscreen <strong>IO13</strong>.</li>
<li><strong>Library salah / belum dipasang.</strong> Install <strong>ESP32Servo</strong> di Library Manager, lalu Verify lagi.</li>
<li><strong>Menguji di terminal web.</strong> Sketch hanya jalan di board lewat IDE Upload — bukan Laragon.</li>
<li><strong>Memutar lengan paksa saat bertegangan.</strong> Bisa merusak gear plastik — biarkan bergerak dari kode.</li>
</ul>

<h2>Selanjutnya</h2>
<p><strong>Intinya:</strong> kalau Serial menunjukkan <code>sudut=…</code> selaras lengan yang bergerak pelan, FS-26 selesai — kamu punya skill PWM posisi (indikator mekanis opsional di stasiun nanti).</p>
<p>Lanjut ke <strong>FS-27</strong> (UART vs I2C vs SPI — bahasa manusia) saat modulnya terbit.</p>
<p>Daftar modul lengkap: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $ide = $this->ideFigureEn();
        $board = $this->boardFigureEn();
        $kit = $this->kitServoFigureEn();
        $main = $this->mainWiringFigureEn();
        $schema = $this->schemaWiringFigureEn();
        $flow = $this->flowSvgEn();
        $serial = $this->serialPanelSvgEn();

        return <<<HTML
<h2>Introduction — a small arm that follows orders</h2>
<p>This is article <strong>#96 (this article)</strong> · module <strong>FS-26</strong> on the <em>Full Stack IoT Developer — From Zero</em> path (<strong>BUILDER</strong> phase). In <strong>FS-20</strong> you set LED brightness with PWM. Today PWM means <strong>position</strong>: a servo arm moves to a chosen angle.</p>
<p><strong>Analogy:</strong> like a clock hand you can tell to “face 3 o’clock / 6 / 9”. Not a fan that spins forever — a small arm that stops at an angle.</p>
{$flow}
<p><strong>Prerequisites:</strong> FS-20 (PWM / <code>analogWrite</code>) · FS-14 (Upload + Serial Monitor). Pins follow the FS-16 / FS-17 table (<strong>servo signal = GPIO 13</strong>).</p>
<p><strong>How to use this article (work order):</strong></p>
<ol>
<li>Wire the servo (match the main figure).</li>
<li><strong>Open Arduino IDE first</strong> (not Laragon / a web terminal).</li>
<li><strong>Library Manager</strong> → search <strong>ESP32Servo</strong> → Install.</li>
<li>Create sketch <code>FS26_servo_sudut</code> → paste the code → <strong>Verify</strong> → <strong>Upload</strong>.</li>
<li>Open <strong>Serial Monitor</strong> at baud <strong>115200</strong>.</li>
<li>Watch the arm sweep 0° → 90° → 180° matching <code>angle=…</code> lines.</li>
<li>Tick the 10/10 checklist in the browser.</li>
</ol>
<p><strong>Not needed today:</strong> Wi-Fi, MQTT, Laragon, <code>php artisan</code>, AC loads, big DC motors. Today's tools: <strong>Arduino IDE</strong> + <strong>Library Manager (ESP32Servo)</strong> + <strong>Upload</strong> + ESP32 + SG90 servo + jumpers + <strong>Serial Monitor</strong> + <strong>browser</strong>.</p>

<h2>Prep — open &amp; set these up first</h2>
<p><strong>Desk order:</strong> wiring → open IDE → install library → Upload → Serial → watch the angle.</p>
{$ide}
<ul>
<li>Open <strong>Arduino IDE 2.x</strong> · board <strong>ESP32 Dev Module</strong> + port.</li>
<li>Prepare the ESP32 + a data USB cable.</li>
<li>Prepare an <strong>SG90</strong> servo + jumpers (3 wires).</li>
<li>Find labels <strong>GPIO 13</strong> / <strong>IO13</strong>, <strong>5V</strong>, and <strong>GND</strong>.</li>
<li>Prepare Serial Monitor at baud <strong>115200</strong>.</li>
</ul>
{$board}
<p><strong>Tools used today:</strong> Arduino IDE, Library Manager, Upload, ESP32, USB data, SG90, jumpers, Serial Monitor, browser.</p>
<p><strong>Not used today:</strong> Laragon, <code>php artisan</code>, Wi-Fi, MQTT, relay/AC.</p>

<h2>Wiring (human language)</h2>
{$kit}
{$main}
{$schema}
<p><strong>Servo block:</strong></p>
<ul>
<li><strong>VCC</strong> (red) → ESP32 <strong>5V</strong> — <strong>not</strong> 3V3</li>
<li><strong>Signal</strong> (orange/yellow) → <strong>GPIO 13</strong> (often labeled <strong>IO13</strong>)</li>
<li><strong>GND</strong> (brown/black) → ESP32 <strong>GND</strong></li>
</ul>
<p><strong>Why 5V?</strong> SG90s are designed for about 4.8–6 V. 3V3 often makes weak motion or an unhappy board. One light SG90 from USB 5V is usually enough for practice; if the ESP32 resets when the arm moves, move servo power to a separate 5V supply and keep a common GND.</p>
<p><strong>Servo vs LED PWM:</strong> in FS-20, 0–255 means brightness. Here 0 / 90 / 180 means <em>angle</em>. Same PWM idea, different meaning.</p>

<h2>Practice — sketch FS26_servo_sudut</h2>
<p>Goal: install <strong>ESP32Servo</strong>, attach the servo on GPIO 13, sweep 0→90→180→90 while printing to Serial.</p>
<ol>
<li><strong>Open Arduino IDE</strong> first (not Laragon).</li>
<li><strong>Sketch → Include Library → Manage Libraries…</strong> → search <code>ESP32Servo</code> → <strong>Install</strong>.</li>
<li><strong>File → New Sketch</strong> → <strong>Save as</strong> <code>FS26_servo_sudut</code>.</li>
<li>Replace the contents with the code below (paste entire sketch).</li>
<li><strong>Verify</strong> → <strong>Upload</strong> → wait for <em>Done uploading</em>.</li>
<li>Open Serial Monitor at baud <strong>115200</strong> and watch the arm.</li>
</ol>
<pre><code class="language-cpp">// FS26_servo_sudut — Full Stack IoT FS-26
#include &lt;ESP32Servo.h&gt;

Servo arm;
const int PIN_SERVO = 13;  // FS-17 table

void setup() {
  Serial.begin(115200);
  arm.attach(PIN_SERVO);
  delay(300);
  Serial.println("FS26_servo_sudut ready — sweep 0 → 90 → 180");
}

void loop() {
  int angles[] = {0, 90, 180, 90};
  for (int i = 0; i &lt; 4; i++) {
    int a = angles[i];
    arm.write(a);
    Serial.print("angle=");
    Serial.println(a);
    delay(1000);
  }
}
</code></pre>
<p><strong>How to test the commands above:</strong> test in <strong>Arduino IDE + Library Manager + Upload + Serial Monitor</strong>. Watch the arm move slowly with <code>angle=0/90/180</code>. Not a Laragon / web-server command. If Verify fails on the library, make sure <strong>ESP32Servo</strong> is installed (not only the classic Uno Servo library).</p>
{$serial}

<h2 id="fsiot-servo-checklist">Practice — servo checklist</h2>
<p>Tick each step after you do it on the desk. Target: <strong>10/10</strong>.</p>
<ul id="fsiot-servo-checklist-items">
<li>Arduino IDE was open before writing code</li>
<li>ESP32Servo library installed via Library Manager</li>
<li>Wiring: 5V–VCC, GPIO 13–Signal, shared GND</li>
<li>I understand: servo VCC is not from 3V3</li>
<li>Sketch saved as FS26_servo_sudut</li>
<li>Upload succeeded — Done uploading</li>
<li>Serial at 115200 shows angle=0/90/180</li>
<li>Servo arm motion matches Serial</li>
<li>I understand: PWM here means position, not LED brightness</li>
<li>I know this is the angle-actuator foundation before I2C bus FS-27</li>
</ul>
<p><strong>How to test the checklist:</strong> tick in the browser after practicing on the IDE + board. No <code>php artisan</code> needed.</p>

<h2>Common mistakes</h2>
<ul>
<li><strong>VCC on 3V3.</strong> Move it to <strong>5V</strong>.</li>
<li><strong>ESP32 resets when the servo moves.</strong> Current drop — use a separate 5V supply + common GND.</li>
<li><strong>Wrong pin (not GPIO 13).</strong> Match silkscreen <strong>IO13</strong>.</li>
<li><strong>Wrong / missing library.</strong> Install <strong>ESP32Servo</strong> in Library Manager, then Verify again.</li>
<li><strong>Testing in a web terminal.</strong> The sketch only runs on the board via IDE Upload — not Laragon.</li>
<li><strong>Forcing the arm by hand while powered.</strong> Plastic gears can break — let the code move it.</li>
</ul>

<h2>Next</h2>
<p><strong>In short:</strong> if Serial shows <code>angle=…</code> matching a slowly moving arm, FS-26 is done — you have positional PWM skill (an optional mechanical indicator for the station later).</p>
<p>Continue to <strong>FS-27</strong> (UART vs I2C vs SPI in human language) when that module ships.</p>
<p>Full module list: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }
}
