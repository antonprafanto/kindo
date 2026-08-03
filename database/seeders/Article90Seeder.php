<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article90Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first()
            ?? Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $iotCat) {
            throw new \RuntimeException('User atau kategori iot-smart-device/esp32-arduino tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-pwm-redupkan-led';

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
                'title'              => 'PWM: redupkan LED & kenalan duty cycle',
                'title_en'           => 'PWM: dim an LED & meet the duty cycle',
                'excerpt'            => 'FS-20 / #90: LED bukan cuma on/off. Uji di Arduino IDE: analogWrite 0–255 di GPIO 2, napas LED (fade) halus.',
                'excerpt_en'         => 'FS-20 / #90: LEDs are not only on/off. Test in Arduino IDE: analogWrite 0–255 on GPIO 2, smooth breathing fade.',
                'body'               => $this->body(),
                'body_en'            => $this->bodyEn(),
                'status'             => 'draft',
                'is_featured'        => false,
                'published_at'       => null,
                'seo_title'          => 'PWM redupkan LED — Full Stack IoT #90',
                'seo_title_en'       => 'PWM dim an LED — Full Stack IoT #90',
                'seo_description'    => 'Belajar PWM, duty cycle, dan analogWrite di ESP32 GPIO 2. Modul FS-20 jalur Full Stack IoT.',
                'seo_description_en' => 'Learn PWM, duty cycle, and analogWrite on ESP32 GPIO 2. Full Stack IoT FS-20 module.',
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
            $src = public_path('images/fsiot/fs20-cover-pwm-led.jpg');
            if (is_file($src)) {
                $dest = 'articles/covers/fs20-cover-pwm-led.jpg';
                \Illuminate\Support\Facades\Storage::disk('public')->put($dest, file_get_contents($src));
                $article->cover_image = $dest;
                $article->save();
            }
        }

        $this->command?->info('✓ Artikel #90 / FS-20 tersimpan sebagai DRAFT: '.$article->title);
    }

    private function ideFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/fs11-ide-overview-cite.png" width="1280" height="720" alt="Arduino IDE 2 — Verify, Upload, dan Serial Monitor" loading="eager" style="width:100%;height:auto;max-height:420px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Arduino IDE 2</strong> — tempat menguji sintaks hari ini. <strong>Verify</strong> → <strong>Upload</strong> → buka <strong>Serial Monitor</strong> (baud 115200) untuk melihat <code>puncak terang</code> / <code>redup total</code>. Board: <strong>ESP32 Dev Module</strong>.
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
    <strong>Arduino IDE 2</strong> — where today’s syntax is tested. <strong>Verify</strong> → <strong>Upload</strong> → open <strong>Serial Monitor</strong> (baud 115200) to watch <code>peak bright</code> / <code>fully dim</code>. Board: <strong>ESP32 Dev Module</strong>.
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
    <strong>ESP32-DevKitC</strong> — USB data di <strong>(6)</strong>, reset di <strong>EN (7)</strong>. LED latihan tetap <strong>GPIO 2</strong> (sama FS-18). Hari ini kita redupkan — bukan ganti pin.
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
    <strong>ESP32-DevKitC</strong> — USB data at <strong>(6)</strong>, reset on <strong>EN (7)</strong>. Practice LED stays on <strong>GPIO 2</strong> (same as FS-18). Today we dim it — we do not change the pin.
    <br>Image source: <a href="https://docs.espressif.com/projects/esp-dev-kits/en/latest/esp32/esp32-devkitc/user_guide.html" rel="noopener noreferrer" target="_blank">Espressif — ESP32-DevKitC user guide</a>. Board pins: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/boards/ESP32-DevKitC-1.html" rel="noopener noreferrer" target="_blank">ESP32-DevKitC-1</a>.
  </figcaption>
</figure>
HTML;
    }

    private function kitFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/kit-led-5mm.jpg" width="900" height="900" alt="LED 5 mm untuk latihan PWM di GPIO 2" loading="eager" style="width:100%;height:auto;max-height:280px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Komponen hari ini:</strong> LED 5 mm + resistor <strong>220 Ω</strong> (pola FS-18). Tidak perlu tombol, sensor, atau resistor ekstra.
    <br>Sumber gambar: foto kit Koding Indonesia (FS-18).
  </figcaption>
</figure>
HTML;
    }

    private function kitFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/kit-led-5mm.jpg" width="900" height="900" alt="5 mm LED for GPIO 2 PWM practice" loading="eager" style="width:100%;height:auto;max-height:280px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Parts today:</strong> 5 mm LED + <strong>220 Ω</strong> resistor (FS-18 pattern). No button, sensor, or extra resistor needed.
    <br>Image source: Koding Indonesia kit photo (FS-18).
  </figcaption>
</figure>
HTML;
    }

    private function mainWiringFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/fs18-led-gpio2-breadboard.png" width="1287" height="709" alt="Gambar utama — wiring LED GPIO 2 + resistor (sama FS-18)" loading="eager" style="width:100%;height:auto;max-height:480px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Gambar utama — wiring tidak berubah.</strong> Masih <strong>IO2 / GPIO 2</strong> → resistor → LED → <strong>GND</strong>. Yang berubah hanya <em>cara kita menulis kode</em> (bukan on/off kasar, melainkan tingkat terang).
    <br>Sumber gambar: diagram rangkaian Koding Indonesia (FS-18 / FS-20).
  </figcaption>
</figure>
HTML;
    }

    private function mainWiringFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/fs18-led-gpio2-breadboard.png" width="1287" height="709" alt="Main figure — GPIO 2 LED wiring + resistor (same as FS-18)" loading="eager" style="width:100%;height:auto;max-height:480px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Main figure — wiring stays the same.</strong> Still <strong>IO2 / GPIO 2</strong> → resistor → LED → <strong>GND</strong>. What changes is <em>how we write the code</em> (not harsh on/off, but brightness levels).
    <br>Image source: Koding Indonesia wiring diagram (FS-18 / FS-20).
  </figcaption>
</figure>
HTML;
    }

    private function pwmAnalogySvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Analogi PWM: kedip sangat cepat terlihat sebagai kecerahan" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 220" width="100%" height="auto" style="display:block;max-height:260px">
    <text x="430" y="28" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#1a1a1a">PWM = nyala-mati sangat cepat (mata melihat “redup/terang”)</text>
    <rect x="40" y="50" width="360" height="140" rx="10" fill="#FFEBEE" stroke="#C62828" stroke-width="2.5"/>
    <text x="220" y="78" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" font-weight="700" fill="#B71C1C">digitalWrite (FS-18)</text>
    <text x="220" y="108" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#333">hanya ON atau OFF</text>
    <text x="220" y="140" text-anchor="middle" font-family="Consolas,monospace" font-size="14" fill="#C62828">HIGH ····· LOW</text>
    <text x="220" y="170" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#666">dua tingkat saja</text>
    <rect x="460" y="50" width="360" height="140" rx="10" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
    <text x="640" y="78" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" font-weight="700" fill="#1B5E20">analogWrite (hari ini)</text>
    <text x="640" y="108" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#333">banyak tingkat terang</text>
    <text x="640" y="140" text-anchor="middle" font-family="Consolas,monospace" font-size="14" fill="#2E7D32">0 … 128 … 255</text>
    <text x="640" y="170" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#666">mata merasakan napas LED</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.35rem;color:#4A5568;">
    <strong>Intinya:</strong> chip tetap menyala-mati, tetapi sangat cepat. Proporsi waktu “nyala” = <em>duty cycle</em>. Sumber gambar: diagram buatan Koding Indonesia (FS-20).
  </figcaption>
</figure>
SVG;
    }

    private function pwmAnalogySvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="PWM analogy: very fast blinking looks like brightness" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 220" width="100%" height="auto" style="display:block;max-height:260px">
    <text x="430" y="28" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#1a1a1a">PWM = on/off so fast the eye sees “dim/bright”</text>
    <rect x="40" y="50" width="360" height="140" rx="10" fill="#FFEBEE" stroke="#C62828" stroke-width="2.5"/>
    <text x="220" y="78" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" font-weight="700" fill="#B71C1C">digitalWrite (FS-18)</text>
    <text x="220" y="108" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#333">only ON or OFF</text>
    <text x="220" y="140" text-anchor="middle" font-family="Consolas,monospace" font-size="14" fill="#C62828">HIGH ····· LOW</text>
    <text x="220" y="170" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#666">just two levels</text>
    <rect x="460" y="50" width="360" height="140" rx="10" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
    <text x="640" y="78" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" font-weight="700" fill="#1B5E20">analogWrite (today)</text>
    <text x="640" y="108" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#333">many brightness levels</text>
    <text x="640" y="140" text-anchor="middle" font-family="Consolas,monospace" font-size="14" fill="#2E7D32">0 … 128 … 255</text>
    <text x="640" y="170" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#666">the eye sees a breathing LED</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.35rem;color:#4A5568;">
    <strong>In short:</strong> the chip still switches on/off, but very fast. The fraction of time “on” = <em>duty cycle</em>. Image source: diagram by Koding Indonesia (FS-20).
  </figcaption>
</figure>
SVG;
    }

    private function dutyCycleFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/fs20-duty-cycle-examples.png" width="542" height="342" alt="Contoh duty cycle 25 persen, 50 persen, dan 75 persen" loading="eager" style="width:100%;height:auto;max-height:360px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Duty cycle</strong> = berapa lama sinyal “tinggi” dalam satu periode. 25% = lebih sering mati (redup); 75% = lebih sering nyala (terang).
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:Duty_Cycle_Examples.png" rel="noopener noreferrer" target="_blank">Duty Cycle Examples</a> · Wikimedia Commons (CC BY-SA 4.0) · Thewrightstuff.
  </figcaption>
</figure>
HTML;
    }

    private function dutyCycleFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/fs20-duty-cycle-examples.png" width="542" height="342" alt="Duty cycle examples at 25 percent, 50 percent, and 75 percent" loading="eager" style="width:100%;height:auto;max-height:360px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Duty cycle</strong> = how long the signal stays “high” in one period. 25% = off more often (dim); 75% = on more often (bright).
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:Duty_Cycle_Examples.png" rel="noopener noreferrer" target="_blank">Duty Cycle Examples</a> · Wikimedia Commons (CC BY-SA 4.0) · Thewrightstuff.
  </figcaption>
</figure>
HTML;
    }

    private function pwmStepsFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/fs20-pwm-5steps.png" width="400" height="438" alt="Diagram PWM Arduino: 0, 25, 50, 75, dan 100 persen" loading="eager" style="width:100%;height:auto;max-height:400px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Tangga terang:</strong> 0% = mati · 50% = setengah · 100% = penuh. Di sketch kita, angka <code>0</code>–<code>255</code> mewakili tangga itu.
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:Pwm_5steps.gif" rel="noopener noreferrer" target="_blank">Pwm 5steps</a> · Wikimedia Commons (CC BY-SA 3.0) · tim Arduino.cc · asal <a href="https://docs.arduino.cc/language-reference/en/functions/analog-io/analogwrite/" rel="noopener noreferrer" target="_blank">Arduino — analogWrite</a>.
  </figcaption>
</figure>
HTML;
    }

    private function pwmStepsFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/fs20-pwm-5steps.png" width="400" height="438" alt="Arduino PWM diagram: 0, 25, 50, 75, and 100 percent" loading="eager" style="width:100%;height:auto;max-height:400px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Brightness steps:</strong> 0% = off · 50% = half · 100% = full. In our sketch, numbers <code>0</code>–<code>255</code> map that ladder.
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:Pwm_5steps.gif" rel="noopener noreferrer" target="_blank">Pwm 5steps</a> · Wikimedia Commons (CC BY-SA 3.0) · Arduino.cc team · from <a href="https://docs.arduino.cc/language-reference/en/functions/analog-io/analogwrite/" rel="noopener noreferrer" target="_blank">Arduino — analogWrite</a>.
  </figcaption>
</figure>
HTML;
    }

    private function fadeSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Alur napas LED: naik lalu turun duty cycle" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 200" width="100%" height="auto" style="display:block;max-height:240px">
    <text x="430" y="28" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#1a1a1a">Napas LED (fade): naik → puncak → turun</text>
    <polyline points="60,150 220,150 400,50 580,50 760,150" fill="none" stroke="#F9A825" stroke-width="4"/>
    <circle cx="60" cy="150" r="8" fill="#455A64"/>
    <circle cx="400" cy="50" r="8" fill="#2E7D32"/>
    <circle cx="760" cy="150" r="8" fill="#455A64"/>
    <text x="60" y="178" text-anchor="middle" font-family="Consolas,monospace" font-size="13" fill="#333">0</text>
    <text x="400" y="40" text-anchor="middle" font-family="Consolas,monospace" font-size="13" fill="#1B5E20">255</text>
    <text x="760" y="178" text-anchor="middle" font-family="Consolas,monospace" font-size="13" fill="#333">0</text>
    <text x="220" y="130" font-family="Segoe UI,sans-serif" font-size="12" fill="#666">for naik</text>
    <text x="580" y="80" font-family="Segoe UI,sans-serif" font-size="12" fill="#666">for turun</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.35rem;color:#4A5568;">
    <strong>Intinya:</strong> dua pengulangan <code>for</code> — satu menaikkan nilai, satu menurunkan. Sumber gambar: diagram buatan Koding Indonesia (FS-20).
  </figcaption>
</figure>
SVG;
    }

    private function fadeSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Breathing LED flow: duty cycle up then down" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 200" width="100%" height="auto" style="display:block;max-height:240px">
    <text x="430" y="28" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#1a1a1a">Breathing LED (fade): up → peak → down</text>
    <polyline points="60,150 220,150 400,50 580,50 760,150" fill="none" stroke="#F9A825" stroke-width="4"/>
    <circle cx="60" cy="150" r="8" fill="#455A64"/>
    <circle cx="400" cy="50" r="8" fill="#2E7D32"/>
    <circle cx="760" cy="150" r="8" fill="#455A64"/>
    <text x="60" y="178" text-anchor="middle" font-family="Consolas,monospace" font-size="13" fill="#333">0</text>
    <text x="400" y="40" text-anchor="middle" font-family="Consolas,monospace" font-size="13" fill="#1B5E20">255</text>
    <text x="760" y="178" text-anchor="middle" font-family="Consolas,monospace" font-size="13" fill="#333">0</text>
    <text x="220" y="130" font-family="Segoe UI,sans-serif" font-size="12" fill="#666">for up</text>
    <text x="580" y="80" font-family="Segoe UI,sans-serif" font-size="12" fill="#666">for down</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.35rem;color:#4A5568;">
    <strong>In short:</strong> two <code>for</code> loops — one raises the value, one lowers it. Image source: diagram by Koding Indonesia (FS-20).
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
        $analogy = $this->pwmAnalogySvgId();
        $duty = $this->dutyCycleFigureId();
        $steps = $this->pwmStepsFigureId();
        $fade = $this->fadeSvgId();

        return <<<HTML
<h2>Pendahuluan — LED yang bernapas</h2>
<p>Artikel ini adalah <strong>#90 (ini)</strong> · modul <strong>FS-20</strong> di jalur <em>Full Stack IoT Developer — Dari Nol</em> (fase <strong>BUILDER</strong>). Di <strong>FS-18</strong> LED hanya on/off. Hari ini kita belajar <strong>PWM</strong>: membuat LED redup atau terang secara halus.</p>
<p><strong>Analogi:</strong> bayangkan kipas yang “nyala-mati” sangat cepat. Kalau lebih lama di posisi nyala, angin terasa kencang — itu mirip <em>duty cycle</em>. Mata kita tidak melihat kedip; yang terasa adalah kecerahan.</p>
<p><strong>Prasyarat:</strong> FS-18 (LED GPIO 2 + <code>pinMode</code> / <code>digitalWrite</code>) · FS-15 (pernah lihat pengulangan <code>for</code>).</p>
<p><strong>Cara pakai artikel ini (urutan kerja):</strong></p>
<ol>
<li>Pastikan wiring LED + 220 Ω di <strong>GPIO 2</strong> masih terpasang (sama FS-18). Cocokkan <strong>gambar utama</strong>.</li>
<li><strong>Buka Arduino IDE</strong> (bukan Laragon / terminal web).</li>
<li>Baca singkat: PWM, duty cycle, <code>analogWrite</code> 0–255.</li>
<li>Buat sketch <code>FS20_led_fade</code> → <strong>Verify</strong> → <strong>Upload</strong>.</li>
<li>Lihat LED “bernapas” (fade naik-turun); Serial mencetak <code>puncak terang</code> / <code>redup total</code>.</li>
<li>Centang checklist 10/10 di browser.</li>
</ol>
<p><strong>Tidak perlu hari ini:</strong> tombol, sensor, Wi-Fi, nama API LEDC yang rumit, Laragon, <code>php artisan</code>. Tools hari ini: <strong>Arduino IDE</strong> + <strong>Upload</strong> + ESP32 + LED + 220 Ω + <strong>Serial Monitor</strong> + <strong>browser</strong>.</p>

<h2>Persiapan — buka &amp; siapkan ini dulu</h2>
<p><strong>Urutan meja kerja:</strong> wiring (boleh tetap dari FS-18) → baca singkat → Upload di Arduino IDE → lihat LED.</p>
<ol>
<li>Buka <strong>Arduino IDE 2.x</strong> · board <strong>ESP32 Dev Module</strong> + port.</li>
<li>Siapkan ESP32 + USB data.</li>
<li>LED + resistor 220 Ω di <strong>GPIO 2</strong> dan <strong>GND</strong> (pola FS-18).</li>
<li>Pastikan paket board <strong>esp32 by Espressif</strong> sudah terpasang (v3.x mendukung <code>analogWrite</code>).</li>
<li>Siapkan Serial Monitor baud <strong>115200</strong>.</li>
</ol>
<p><strong>Alat yang dipakai hari ini:</strong> Arduino IDE, Upload, ESP32, USB data, LED, 220 Ω, jumper, Serial Monitor, browser.</p>
<p><strong>Tidak dipakai hari ini:</strong> Laragon, <code>php artisan</code>, tombol, DHT, multimeter (opsional), PuTTY.</p>
{$ide}
{$board}
{$kit}
{$main}

<h2>PWM dan duty cycle (bahasa manusia)</h2>
{$analogy}
<p><strong>PWM</strong> (pulse-width modulation) = mengatur lebar “denyut” nyala dalam waktu sangat singkat. <strong>Duty cycle</strong> = rasio waktu nyala terhadap satu periode (0% mati total · 100% nyala penuh).</p>
{$duty}
{$steps}
<p>Di Arduino klasik dan di <strong>Arduino-ESP32 v3.x</strong>, kita memakai <code>analogWrite(pin, nilai)</code> dengan nilai <strong>0–255</strong> (8 bit). Tidak perlu menghafal nama channel LEDC dulu — itu boleh dipelajari nanti di dokumentasi lanjut.</p>
<p>Referensi: <a href="https://docs.arduino.cc/language-reference/en/functions/analog-io/analogwrite/" rel="noopener noreferrer" target="_blank">Arduino — analogWrite</a> · <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/api/ledc.html" rel="noopener noreferrer" target="_blank">Espressif — LEDC / PWM (Arduino-ESP32)</a>.</p>

<h2>Praktik — sketch FS20_led_fade</h2>
{$fade}
<p>Tujuan: LED di GPIO 2 <strong>bernapas</strong> — terang naik pelan, lalu redup pelan, berulang.</p>
<ol>
<li>Arduino IDE → <strong>File → New Sketch</strong> → <strong>Simpan sebagai</strong> <code>FS20_led_fade</code>.</li>
<li>Ganti isi dengan kode di bawah (salin utuh).</li>
<li><strong>Verify</strong> → <strong>Upload</strong> → tunggu <em>Done uploading</em>.</li>
<li>Lihat LED fade. Buka Serial Monitor baud <strong>115200</strong>.</li>
<li>Tekan <strong>EN (7)</strong> bila perlu restart.</li>
</ol>
<pre><code class="language-cpp">// FS20_led_fade — Full Stack IoT FS-20
// PWM sederhana: analogWrite 0–255 di GPIO 2 (napas LED)

const int LED_PIN = 2;
const int STEP_MS = 8; // jeda tiap langkah (ms) — kecilkan = lebih cepat

void setup() {
  pinMode(LED_PIN, OUTPUT);
  Serial.begin(115200);
  delay(300);
  Serial.println("FS20_led_fade siap");
}

void loop() {
  // naik: redup → terang
  for (int d = 0; d &lt;= 255; d++) {
    analogWrite(LED_PIN, d);
    delay(STEP_MS);
  }
  Serial.println("puncak terang");

  // turun: terang → redup
  for (int d = 255; d &gt;= 0; d--) {
    analogWrite(LED_PIN, d);
    delay(STEP_MS);
  }
  Serial.println("redup total");
}
</code></pre>
<p><strong>Cara menguji perintah di atas:</strong> uji di <strong>Arduino IDE + Upload + mata ke LED</strong> (dan Serial). Sukses = LED naik-turun halus; Serial mencetak <code>puncak terang</code> / <code>redup total</code>. Bukan perintah Laragon / web server.</p>
<p><strong>Catatan Verify:</strong> jika IDE mengeluh tidak kenal <code>analogWrite</code>, perbarui board package <strong>esp32 by Espressif Systems</strong> ke v3.x (Tools → Board → Boards Manager).</p>

<h2 id="fsiot-pwm-checklist">Praktik — checklist PWM / napas LED</h2>
<p>Centang setiap langkah setelah kamu lakukan di meja. Target: <strong>10/10</strong>.</p>
<ul id="fsiot-pwm-checklist-items">
<li>Arduino IDE sudah terbuka sebelum menulis kode</li>
<li>Board ESP32 Dev Module + port sudah dipilih</li>
<li>Wiring LED + 220 Ω masih di GPIO 2 (pola FS-18)</li>
<li>Paham: PWM = nyala-mati sangat cepat</li>
<li>Paham: duty cycle 0–255 lewat analogWrite</li>
<li>Sketch disimpan sebagai FS20_led_fade</li>
<li>Upload berhasil — Done uploading</li>
<li>LED bernapas (fade naik-turun) terlihat mata</li>
<li>Serial menampilkan puncak terang / redup total</li>
<li>Sadar: pin tertentu tidak ideal untuk PWM — kita tetap di GPIO 2</li>
</ul>
<p><strong>Cara menguji checklist:</strong> centang di browser setelah praktik di IDE + board. Tidak perlu <code>php artisan</code>.</p>

<h2>Kesalahan yang sering terjadi</h2>
<ul>
<li><strong>Mengira harus ganti wiring.</strong> Hari ini wiring sama FS-18; yang berubah hanya kode.</li>
<li><strong>Pakai <code>digitalWrite</code> saja.</strong> Itu hanya on/off — tidak ada tingkat redup.</li>
<li><strong>Nilai di luar 0–255.</strong> Tetap di rentang itu untuk pola latihan hari ini.</li>
<li><strong>Board package ESP32 terlalu lama.</strong> Update ke v3.x agar <code>analogWrite</code> tersedia.</li>
<li><strong>Pin aneh / IO6–IO11.</strong> Tetap latihan di <strong>GPIO 2</strong>.</li>
<li><strong>Delay langkah terlalu besar.</strong> Fade terasa “melompat”; coba <code>STEP_MS</code> 5–12.</li>
<li><strong>Menguji di terminal web.</strong> Sketch hanya jalan di board lewat IDE Upload.</li>
</ul>

<h2>Selanjutnya</h2>
<p><strong>Intinya:</strong> kalau LED di GPIO 2 bernapas halus dengan <code>analogWrite</code>, FS-20 selesai — fondasi PWM untuk aktuator (misalnya servo di modul nanti) sudah terbuka.</p>
<p>Lanjut ke <strong>FS-21</strong> (sensor DHT22 ke Serial) saat modulnya terbit.</p>
<p>Daftar modul lengkap: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $ide = $this->ideFigureEn();
        $board = $this->boardFigureEn();
        $kit = $this->kitFigureEn();
        $main = $this->mainWiringFigureEn();
        $analogy = $this->pwmAnalogySvgEn();
        $duty = $this->dutyCycleFigureEn();
        $steps = $this->pwmStepsFigureEn();
        $fade = $this->fadeSvgEn();

        return <<<HTML
<h2>Introduction — a breathing LED</h2>
<p>This is article <strong>#90 (this article)</strong> · module <strong>FS-20</strong> on the <em>Full Stack IoT Developer — From Zero</em> path (<strong>BUILDER</strong> phase). In <strong>FS-18</strong> the LED was only on/off. Today we learn <strong>PWM</strong>: making the LED dim or bright smoothly.</p>
<p><strong>Analogy:</strong> imagine a fan that turns on/off very fast. If it spends more time “on”, the breeze feels stronger — that is like a <em>duty cycle</em>. Your eye does not see the blink; it feels brightness.</p>
<p><strong>Prerequisites:</strong> FS-18 (LED on GPIO 2 + <code>pinMode</code> / <code>digitalWrite</code>) · FS-15 (you have seen <code>for</code> loops).</p>
<p><strong>How to use this article (work order):</strong></p>
<ol>
<li>Keep the LED + 220 Ω wiring on <strong>GPIO 2</strong> (same as FS-18). Match the <strong>main figure</strong>.</li>
<li><strong>Open Arduino IDE</strong> (not Laragon / a web terminal).</li>
<li>Skim: PWM, duty cycle, <code>analogWrite</code> 0–255.</li>
<li>Create sketch <code>FS20_led_fade</code> → <strong>Verify</strong> → <strong>Upload</strong>.</li>
<li>Watch the breathing LED (fade up/down); Serial prints <code>peak bright</code> / <code>fully dim</code>.</li>
<li>Tick the 10/10 checklist in the browser.</li>
</ol>
<p><strong>Not needed today:</strong> buttons, sensors, Wi-Fi, heavy LEDC API names, Laragon, <code>php artisan</code>. Today's tools: <strong>Arduino IDE</strong> + <strong>Upload</strong> + ESP32 + LED + 220 Ω + <strong>Serial Monitor</strong> + <strong>browser</strong>.</p>

<h2>Preparation — open &amp; gather these first</h2>
<p><strong>Desk order:</strong> wiring (may keep FS-18) → short read → Upload in Arduino IDE → watch the LED.</p>
<ol>
<li>Open <strong>Arduino IDE 2.x</strong> · <strong>ESP32 Dev Module</strong> board + port.</li>
<li>Prepare the ESP32 + USB data cable.</li>
<li>LED + 220 Ω on <strong>GPIO 2</strong> and <strong>GND</strong> (FS-18 pattern).</li>
<li>Confirm the <strong>esp32 by Espressif</strong> board package is installed (v3.x supports <code>analogWrite</code>).</li>
<li>Have Serial Monitor ready at baud <strong>115200</strong>.</li>
</ol>
<p><strong>Tools used today:</strong> Arduino IDE, Upload, ESP32, USB data, LED, 220 Ω, jumpers, Serial Monitor, browser.</p>
<p><strong>Not used today:</strong> Laragon, <code>php artisan</code>, button, DHT, multimeter (optional), PuTTY.</p>
{$ide}
{$board}
{$kit}
{$main}

<h2>PWM and duty cycle (human language)</h2>
{$analogy}
<p><strong>PWM</strong> (pulse-width modulation) = controlling how wide the “on” pulse is in a very short period. <strong>Duty cycle</strong> = the on-time ratio in one period (0% fully off · 100% fully on).</p>
{$duty}
{$steps}
<p>On classic Arduino and on <strong>Arduino-ESP32 v3.x</strong>, we use <code>analogWrite(pin, value)</code> with <strong>0–255</strong> (8-bit). You do not need to memorize LEDC channel names yet — that can wait for advanced docs.</p>
<p>References: <a href="https://docs.arduino.cc/language-reference/en/functions/analog-io/analogwrite/" rel="noopener noreferrer" target="_blank">Arduino — analogWrite</a> · <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/api/ledc.html" rel="noopener noreferrer" target="_blank">Espressif — LEDC / PWM (Arduino-ESP32)</a>.</p>

<h2>Practice — sketch FS20_led_fade</h2>
{$fade}
<p>Goal: the GPIO 2 LED <strong>breathes</strong> — brightness rises slowly, then falls slowly, repeating.</p>
<ol>
<li>Arduino IDE → <strong>File → New Sketch</strong> → <strong>Save As</strong> <code>FS20_led_fade</code>.</li>
<li>Replace the contents with the code below (copy whole).</li>
<li><strong>Verify</strong> → <strong>Upload</strong> → wait for <em>Done uploading</em>.</li>
<li>Watch the fade. Open Serial Monitor at baud <strong>115200</strong>.</li>
<li>Press <strong>EN (7)</strong> if you need a restart.</li>
</ol>
<pre><code class="language-cpp">// FS20_led_fade — Full Stack IoT FS-20
// Simple PWM: analogWrite 0–255 on GPIO 2 (breathing LED)

const int LED_PIN = 2;
const int STEP_MS = 8; // delay per step (ms) — smaller = faster

void setup() {
  pinMode(LED_PIN, OUTPUT);
  Serial.begin(115200);
  delay(300);
  Serial.println("FS20_led_fade ready");
}

void loop() {
  // up: dim → bright
  for (int d = 0; d &lt;= 255; d++) {
    analogWrite(LED_PIN, d);
    delay(STEP_MS);
  }
  Serial.println("peak bright");

  // down: bright → dim
  for (int d = 255; d &gt;= 0; d--) {
    analogWrite(LED_PIN, d);
    delay(STEP_MS);
  }
  Serial.println("fully dim");
}
</code></pre>
<p><strong>How to test the commands above:</strong> test in <strong>Arduino IDE + Upload + eyes on the LED</strong> (and Serial). Success = smooth fade up/down; Serial prints <code>peak bright</code> / <code>fully dim</code>. Not a Laragon / web-server command.</p>
<p><strong>Verify note:</strong> if the IDE complains it does not know <code>analogWrite</code>, update the <strong>esp32 by Espressif Systems</strong> board package to v3.x (Tools → Board → Boards Manager).</p>

<h2 id="fsiot-pwm-checklist">Practice — PWM / breathing LED checklist</h2>
<p>Tick each step after you do it at the desk. Target: <strong>10/10</strong>.</p>
<ul id="fsiot-pwm-checklist-items">
<li>Arduino IDE is open before writing code</li>
<li>ESP32 Dev Module board + port are selected</li>
<li>LED + 220 Ω still on GPIO 2 (FS-18 pattern)</li>
<li>I understand: PWM = very fast on/off</li>
<li>I understand: duty cycle 0–255 via analogWrite</li>
<li>Sketch saved as FS20_led_fade</li>
<li>Upload succeeded — Done uploading</li>
<li>Breathing LED (fade up/down) is visible</li>
<li>Serial shows peak bright / fully dim</li>
<li>I know: some pins are poor for PWM — we stay on GPIO 2</li>
</ul>
<p><strong>How to test the checklist:</strong> tick in the browser after practice on the IDE + board. No <code>php artisan</code> needed.</p>

<h2>Common mistakes</h2>
<ul>
<li><strong>Thinking wiring must change.</strong> Today’s wiring matches FS-18; only the code changes.</li>
<li><strong>Using only <code>digitalWrite</code>.</strong> That is on/off — no dim levels.</li>
<li><strong>Values outside 0–255.</strong> Stay in that range for today’s practice pattern.</li>
<li><strong>Very old ESP32 board package.</strong> Update to v3.x so <code>analogWrite</code> exists.</li>
<li><strong>Weird pins / IO6–IO11.</strong> Keep practice on <strong>GPIO 2</strong>.</li>
<li><strong>Step delay too large.</strong> Fade feels jumpy; try <code>STEP_MS</code> 5–12.</li>
<li><strong>Testing in a web terminal.</strong> The sketch runs on the board via IDE Upload only.</li>
</ul>

<h2>Next</h2>
<p><strong>In short:</strong> if the GPIO 2 LED breathes smoothly with <code>analogWrite</code>, FS-20 is done — the PWM foundation for actuators (for example a servo in a later module) is open.</p>
<p>Continue to <strong>FS-21</strong> (DHT22 sensor to Serial) when that module publishes.</p>
<p>Full module list: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }
}
