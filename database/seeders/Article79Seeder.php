<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article79Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first()
            ?? Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $iotCat) {
            throw new \RuntimeException('User atau kategori iot-smart-device/esp32-arduino tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-led-resistor-di-breadboard';

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
                'title'              => 'Rangkaian pertama: LED menyala di breadboard (tanpa coding)',
                'title_en'           => 'First circuit: LED on a breadboard (no code)',
                'excerpt'            => 'FS-09 / #79: Wiring step-by-step LED + resistor 220Ω dari pin 3V3 ESP32. Nyalakan & matikan dengan cabut jumper — belum upload sketch.',
                'excerpt_en'         => 'FS-09 / #79: Step-by-step LED + 220Ω resistor from ESP32 3V3 pin. Turn on/off by unplugging a jumper — no sketch upload yet.',
                'body'               => $this->body(),
                'body_en'            => $this->bodyEn(),
                'status'             => 'draft',
                'is_featured'        => false,
                'published_at'       => null,
                'seo_title'          => 'LED di Breadboard — Rangkaian Pertama 3.3V — Full Stack IoT #79',
                'seo_title_en'       => 'LED on Breadboard — First 3.3V Circuit — Full Stack IoT #79',
                'seo_description'    => 'Rakit LED + resistor di breadboard dari pin 3V3 ESP32. Wiring aman step-by-step, polaritas LED, power rail. Modul FS-09 tanpa coding.',
                'seo_description_en' => 'Build LED + resistor on a breadboard from ESP32 3V3. Safe step-by-step wiring, LED polarity, power rails. FS-09 module, no code.',
            ]
        );

        if ($article->published_at !== null || $article->status !== 'draft') {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['fullstack-iot', 'iot', 'esp32'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel #79 / FS-09 tersimpan sebagai DRAFT: '.$article->title);
    }

    private function ledResistorFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <div style="display:flex;flex-wrap:wrap;gap:1rem;justify-content:center;align-items:center">
    <img src="/images/fsiot/kit-led-5mm.jpg" width="600" height="450" alt="LED 5mm — kaki panjang anoda, kaki pendek katoda" loading="lazy" style="flex:1 1 220px;max-width:320px;height:auto;max-height:240px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
    <img src="/images/fsiot/kit-resistor-220ohm.jpg" width="600" height="400" alt="Resistor 220 ohm — rem arus untuk LED" loading="lazy" style="flex:1 1 220px;max-width:320px;height:auto;max-height:240px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  </div>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>LED 5mm</strong> (kaki panjang = +) dan <strong>resistor 220Ω</strong> (330Ω boleh). Lihat diagram polaritas LED di bagian wiring.
    <br>Sumber: <a href="https://commons.wikimedia.org/wiki/File:5mm_LED_Light-emitting_diode.jpg" rel="noopener noreferrer" target="_blank">Wikimedia — LED</a> · <a href="https://commons.wikimedia.org/wiki/File:220_ohms_5%25_axial_resistor.jpg" rel="noopener noreferrer" target="_blank">oomlout — 220 ohms resistor</a> · Wikimedia Commons (CC BY-SA 2.0).
  </figcaption>
</figure>
HTML;
    }

    private function ledResistorFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <div style="display:flex;flex-wrap:wrap;gap:1rem;justify-content:center;align-items:center">
    <img src="/images/fsiot/kit-led-5mm.jpg" width="600" height="450" alt="5mm LED — long leg anode, short leg cathode" loading="lazy" style="flex:1 1 220px;max-width:320px;height:auto;max-height:240px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
    <img src="/images/fsiot/kit-resistor-220ohm.jpg" width="600" height="400" alt="220 ohm resistor — current brake for LED" loading="lazy" style="flex:1 1 220px;max-width:320px;height:auto;max-height:240px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  </div>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>5mm LED</strong> (long leg = +) and <strong>220Ω resistor</strong> (330Ω is fine). See the LED polarity diagram in the wiring section.
    <br>Sources: <a href="https://commons.wikimedia.org/wiki/File:5mm_LED_Light-emitting_diode.jpg" rel="noopener noreferrer" target="_blank">Wikimedia — LED</a> · <a href="https://commons.wikimedia.org/wiki/File:220_ohms_5%25_axial_resistor.jpg" rel="noopener noreferrer" target="_blank">oomlout — 220 ohms resistor</a> · Wikimedia Commons (CC BY-SA 2.0).
  </figcaption>
</figure>
HTML;
    }

    private function breadboardFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/kit-breadboard.jpg" width="1200" height="900" alt="Breadboard solderless putih dengan power rail merah dan biru" loading="eager" style="width:100%;height:auto;max-height:360px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Breadboard</strong> = papan percobaan tanpa solder. Garis <strong>merah (+)</strong> dan <strong>biru (-)</strong> di tepi = power rail.
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:400_points_breadboard.jpg" rel="noopener noreferrer" target="_blank">oomlout — 400 points breadboard</a> · Wikimedia Commons.
  </figcaption>
</figure>
HTML;
    }

    private function breadboardFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/kit-breadboard.jpg" width="1200" height="900" alt="White solderless breadboard with red and blue power rails" loading="eager" style="width:100%;height:auto;max-height:360px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    A <strong>breadboard</strong> is a solderless trial board. The <strong>red (+)</strong> and <strong>blue (-)</strong> lines on the edges are power rails.
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:400_points_breadboard.jpg" rel="noopener noreferrer" target="_blank">oomlout — 400 points breadboard</a> · Wikimedia Commons.
  </figcaption>
</figure>
HTML;
    }

    private function jumperFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/kit-jumper-wires.jpg" width="1200" height="900" alt="Kabel jumper warna-warni untuk breadboard" loading="lazy" style="width:100%;height:auto;max-height:360px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Kabel jumper</strong> menghubungkan pin board ke breadboard. Hari ini pakai minimal <strong>3 kabel</strong>: merah (3V3), hitam (GND), dan satu warna lain.
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:Jumper_wires.jpg" rel="noopener noreferrer" target="_blank">Wikimedia Commons — jumper wires</a>.
  </figcaption>
</figure>
HTML;
    }

    private function jumperFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/kit-jumper-wires.jpg" width="1200" height="900" alt="Colorful jumper wires for breadboards" loading="lazy" style="width:100%;height:auto;max-height:360px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Jumper wires</strong> connect board pins to the breadboard. Today use at least <strong>3 wires</strong>: red (3V3), black (GND), and one other color.
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:Jumper_wires.jpg" rel="noopener noreferrer" target="_blank">Wikimedia Commons — jumper wires</a>.
  </figcaption>
</figure>
HTML;
    }

    private function pinoutFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/esp32-devkitc-1-pinlayout.jpg" width="1200" height="800" alt="Pinout resmi ESP32-DevKitC-1 — cari label 3V3 dan GND di tepi board" loading="lazy" style="width:100%;height:auto;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Cari 2 pin ini di board kamu:</strong> label <strong>3V3</strong> (jumper merah ke rail +) dan <strong>GND</strong> (jumper hitam ke rail −). Hari ini pakai pin <strong>3V3</strong>, bukan GPIO — LED menyala tanpa program.
    <br>Sumber gambar: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/boards/ESP32-DevKitC-1.html" rel="noopener noreferrer" target="_blank">Espressif — ESP32-DevKitC-1</a> (dokumen resmi).
  </figcaption>
</figure>
HTML;
    }

    private function pinoutFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/esp32-devkitc-1-pinlayout.jpg" width="1200" height="800" alt="Official ESP32-DevKitC-1 pinout — find 3V3 and GND labels on the board edge" loading="lazy" style="width:100%;height:auto;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Find these 2 pins on your board:</strong> the <strong>3V3</strong> label (red jumper to + rail) and <strong>GND</strong> (black jumper to − rail). Today we use the <strong>3V3</strong> pin, not GPIO — the LED lights with no program.
    <br>Image source: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/boards/ESP32-DevKitC-1.html" rel="noopener noreferrer" target="_blank">Espressif — ESP32-DevKitC-1</a> (official docs).
  </figcaption>
</figure>
HTML;
    }

    private function breadboardSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Diagram dalaman breadboard: baris, kolom, dan power rail" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 760 280" width="100%" height="auto" role="img" aria-label="Breadboard connections">
  <rect x="30" y="40" width="36" height="150" fill="#FFCDD2" stroke="#1a1a1a" stroke-width="2"/>
  <rect x="76" y="40" width="36" height="150" fill="#BBDEFB" stroke="#1a1a1a" stroke-width="2"/>
  <text x="48" y="30" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">+</text>
  <text x="94" y="30" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">−</text>
  <text x="71" y="210" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Power rail kiri</text>
  <text x="270" y="28" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">Satu baris = saling nyambung</text>
  <g fill="#FFFDE7" stroke="#1a1a1a" stroke-width="1.5">
    <rect x="150" y="50" width="30" height="30"/><rect x="190" y="50" width="30" height="30"/><rect x="230" y="50" width="30" height="30"/><rect x="270" y="50" width="30" height="30"/><rect x="310" y="50" width="30" height="30"/>
    <rect x="150" y="100" width="30" height="30"/><rect x="190" y="100" width="30" height="30"/><rect x="230" y="100" width="30" height="30"/><rect x="270" y="100" width="30" height="30"/><rect x="310" y="100" width="30" height="30"/>
  </g>
  <line x1="155" y1="65" x2="335" y2="65" stroke="#E53935" stroke-width="2.5" stroke-dasharray="5 3"/>
  <text x="245" y="155" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Contoh baris A–E (5 lubang)</text>
  <rect x="370" y="45" width="28" height="100" fill="#E0E0E0" stroke="#1a1a1a" stroke-width="1.5" stroke-dasharray="4 3"/>
  <text x="384" y="170" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" font-weight="700">parit</text>
  <g fill="#FFFDE7" stroke="#1a1a1a" stroke-width="1.5">
    <rect x="420" y="50" width="30" height="30"/><rect x="460" y="50" width="30" height="30"/><rect x="500" y="50" width="30" height="30"/><rect x="540" y="50" width="30" height="30"/><rect x="580" y="50" width="30" height="30"/>
    <rect x="420" y="100" width="30" height="30"/><rect x="460" y="100" width="30" height="30"/><rect x="500" y="100" width="30" height="30"/><rect x="540" y="100" width="30" height="30"/><rect x="580" y="100" width="30" height="30"/>
  </g>
  <rect x="640" y="40" width="36" height="150" fill="#BBDEFB" stroke="#1a1a1a" stroke-width="2"/>
  <rect x="686" y="40" width="36" height="150" fill="#FFCDD2" stroke="#1a1a1a" stroke-width="2"/>
  <text x="658" y="30" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">−</text>
  <text x="704" y="30" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">+</text>
  <text x="681" y="210" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Power rail kanan</text>
  <text x="380" y="245" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#4A5568">Baris berbeda = TIDAK otomatis nyambung · parit memisahkan kiri &amp; kanan</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Diagram dalaman breadboard (buatan Koding Indonesia). Rail merah (+) ke 3V3, rail biru (-) ke GND.</figcaption>
</figure>
SVG;
    }

    private function breadboardSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Breadboard internals: rows, columns, and power rails" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 760 280" width="100%" height="auto" role="img" aria-label="Breadboard connections">
  <rect x="30" y="40" width="36" height="150" fill="#FFCDD2" stroke="#1a1a1a" stroke-width="2"/>
  <rect x="76" y="40" width="36" height="150" fill="#BBDEFB" stroke="#1a1a1a" stroke-width="2"/>
  <text x="48" y="30" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">+</text>
  <text x="94" y="30" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">−</text>
  <text x="71" y="210" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Left power rail</text>
  <text x="270" y="28" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">One row = connected together</text>
  <g fill="#FFFDE7" stroke="#1a1a1a" stroke-width="1.5">
    <rect x="150" y="50" width="30" height="30"/><rect x="190" y="50" width="30" height="30"/><rect x="230" y="50" width="30" height="30"/><rect x="270" y="50" width="30" height="30"/><rect x="310" y="50" width="30" height="30"/>
    <rect x="150" y="100" width="30" height="30"/><rect x="190" y="100" width="30" height="30"/><rect x="230" y="100" width="30" height="30"/><rect x="270" y="100" width="30" height="30"/><rect x="310" y="100" width="30" height="30"/>
  </g>
  <line x1="155" y1="65" x2="335" y2="65" stroke="#E53935" stroke-width="2.5" stroke-dasharray="5 3"/>
  <text x="245" y="155" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Example row A–E (5 holes)</text>
  <rect x="370" y="45" width="28" height="100" fill="#E0E0E0" stroke="#1a1a1a" stroke-width="1.5" stroke-dasharray="4 3"/>
  <text x="384" y="170" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" font-weight="700">trench</text>
  <g fill="#FFFDE7" stroke="#1a1a1a" stroke-width="1.5">
    <rect x="420" y="50" width="30" height="30"/><rect x="460" y="50" width="30" height="30"/><rect x="500" y="50" width="30" height="30"/><rect x="540" y="50" width="30" height="30"/><rect x="580" y="50" width="30" height="30"/>
    <rect x="420" y="100" width="30" height="30"/><rect x="460" y="100" width="30" height="30"/><rect x="500" y="100" width="30" height="30"/><rect x="540" y="100" width="30" height="30"/><rect x="580" y="100" width="30" height="30"/>
  </g>
  <rect x="640" y="40" width="36" height="150" fill="#BBDEFB" stroke="#1a1a1a" stroke-width="2"/>
  <rect x="686" y="40" width="36" height="150" fill="#FFCDD2" stroke="#1a1a1a" stroke-width="2"/>
  <text x="658" y="30" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">−</text>
  <text x="704" y="30" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">+</text>
  <text x="681" y="210" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Right power rail</text>
  <text x="380" y="245" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#4A5568">Different rows are NOT auto-connected · trench separates left &amp; right</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Breadboard internals diagram (by Koding Indonesia). Red (+) rail to 3V3, blue (-) rail to GND.</figcaption>
</figure>
SVG;
    }

    private function ledSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="LED: kaki panjang anoda dan kaki pendek katoda" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 250" width="100%" height="auto" role="img" aria-label="LED polarity">
  <text x="200" y="28" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700">LED 5mm</text>
  <circle cx="200" cy="85" r="48" fill="#FFEB3B" stroke="#1a1a1a" stroke-width="2.5"/>
  <rect x="152" y="85" width="96" height="26" fill="#FFEB3B" stroke="#1a1a1a" stroke-width="2.5"/>
  <line x1="165" y1="111" x2="165" y2="165" stroke="#1a1a1a" stroke-width="6" stroke-linecap="round"/>
  <line x1="235" y1="111" x2="235" y2="148" stroke="#1a1a1a" stroke-width="6" stroke-linecap="round"/>
  <text x="95" y="185" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">← kaki panjang</text>
  <text x="95" y="208" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#2E7D32">= + (anoda)</text>
  <text x="305" y="185" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">kaki pendek →</text>
  <text x="305" y="208" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#C62828">= − (katoda)</text>
  <rect x="400" y="50" width="290" height="130" fill="#E8F5E9" stroke="#1a1a1a" stroke-width="2" rx="6"/>
  <text x="545" y="95" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700">Resistor 220Ω</text>
  <text x="545" y="125" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" fill="#4A5568">kaki panjang LED = ke sisi resistor</text>
  <text x="545" y="152" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" fill="#4A5568">kaki pendek LED = ke jalur GND</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Polaritas LED — cocokkan sebelum dicolok ke breadboard (buatan Koding Indonesia).</figcaption>
</figure>
SVG;
    }

    private function ledSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="LED: long anode leg and short cathode leg" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 250" width="100%" height="auto" role="img" aria-label="LED polarity">
  <text x="200" y="28" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700">5mm LED</text>
  <circle cx="200" cy="85" r="48" fill="#FFEB3B" stroke="#1a1a1a" stroke-width="2.5"/>
  <rect x="152" y="85" width="96" height="26" fill="#FFEB3B" stroke="#1a1a1a" stroke-width="2.5"/>
  <line x1="165" y1="111" x2="165" y2="165" stroke="#1a1a1a" stroke-width="6" stroke-linecap="round"/>
  <line x1="235" y1="111" x2="235" y2="148" stroke="#1a1a1a" stroke-width="6" stroke-linecap="round"/>
  <text x="95" y="185" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">← long leg</text>
  <text x="95" y="208" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#2E7D32">= + (anode)</text>
  <text x="305" y="185" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">short leg →</text>
  <text x="305" y="208" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#C62828">= − (cathode)</text>
  <rect x="400" y="50" width="290" height="130" fill="#E8F5E9" stroke="#1a1a1a" stroke-width="2" rx="6"/>
  <text x="545" y="95" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700">220Ω resistor</text>
  <text x="545" y="125" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" fill="#4A5568">long LED leg = toward resistor</text>
  <text x="545" y="152" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" fill="#4A5568">short LED leg = toward GND path</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">LED polarity — match this before plugging into the breadboard (by Koding Indonesia).</figcaption>
</figure>
SVG;
    }

    private function currentFlowSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Alur listrik 3V3 ke GND melalui resistor dan LED" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 120" width="100%" height="auto" role="img" aria-label="Current flow">
  <text x="360" y="22" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">Alur listrik (gambaran sederhana)</text>
  <rect x="30" y="45" width="70" height="40" rx="6" fill="#FFEBEE" stroke="#C62828" stroke-width="2"/>
  <text x="65" y="70" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">3V3</text>
  <line x1="100" y1="65" x2="140" y2="65" stroke="#1565C0" stroke-width="3" marker-end="url(#arr)"/>
  <rect x="140" y="45" width="70" height="40" rx="6" fill="#FFF8E1" stroke="#1a1a1a" stroke-width="2"/>
  <text x="175" y="70" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">220Ω</text>
  <line x1="210" y1="65" x2="250" y2="65" stroke="#1565C0" stroke-width="3"/>
  <polygon points="260,50 260,80 290,65" fill="#FFEB3B" stroke="#1a1a1a" stroke-width="2"/>
  <line x1="290" y1="65" x2="330" y2="65" stroke="#1565C0" stroke-width="3"/>
  <rect x="330" y="45" width="70" height="40" rx="6" fill="#ECEFF1" stroke="#1a1a1a" stroke-width="2"/>
  <text x="365" y="70" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">GND</text>
  <text x="360" y="108" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Di breadboard: ikuti gambar utama (ESP32 + LED) · Buatan Koding Indonesia</text>
  <defs><marker id="arr" markerWidth="8" markerHeight="8" refX="6" refY="3" orient="auto"><path d="M0,0 L6,3 L0,6 Z" fill="#1565C0"/></marker></defs>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Alur listrik yang akan kamu rakit: <strong>3V3 → resistor 220Ω → LED → GND</strong>. Diagram breadboard lengkap ada di bawah.</figcaption>
</figure>
SVG;
    }

    private function currentFlowSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Current flow from 3V3 to GND through resistor and LED" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 120" width="100%" height="auto" role="img" aria-label="Current flow">
  <text x="360" y="22" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">Current path (simple view)</text>
  <rect x="30" y="45" width="70" height="40" rx="6" fill="#FFEBEE" stroke="#C62828" stroke-width="2"/>
  <text x="65" y="70" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">3V3</text>
  <line x1="100" y1="65" x2="140" y2="65" stroke="#1565C0" stroke-width="3" marker-end="url(#arr2)"/>
  <rect x="140" y="45" width="70" height="40" rx="6" fill="#FFF8E1" stroke="#1a1a1a" stroke-width="2"/>
  <text x="175" y="70" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">220Ω</text>
  <line x1="210" y1="65" x2="250" y2="65" stroke="#1565C0" stroke-width="3"/>
  <polygon points="260,50 260,80 290,65" fill="#FFEB3B" stroke="#1a1a1a" stroke-width="2"/>
  <line x1="290" y1="65" x2="330" y2="65" stroke="#1565C0" stroke-width="3"/>
  <rect x="330" y="45" width="70" height="40" rx="6" fill="#ECEFF1" stroke="#1a1a1a" stroke-width="2"/>
  <text x="365" y="70" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">GND</text>
  <text x="360" y="108" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">On breadboard: follow the main diagram (ESP32 + LED) · By Koding Indonesia</text>
  <defs><marker id="arr2" markerWidth="8" markerHeight="8" refX="6" refY="3" orient="auto"><path d="M0,0 L6,3 L0,6 Z" fill="#1565C0"/></marker></defs>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">The path you will build: <strong>3V3 → 220Ω resistor → LED → GND</strong>. Full breadboard diagram below.</figcaption>
</figure>
SVG;
    }

    private function mainWiringFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/fs09-led-breadboard-wiring.png" width="1162" height="757" alt="Foto rangkaian: ESP32 di breadboard, LED merah + resistor, jumper 3V3 dan GND — belum coding" loading="eager" style="width:100%;height:auto;max-height:560px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Gambar utama</strong> — ESP32 dipasang di breadboard (sumber <strong>3V3</strong> &amp; <strong>GND</strong>). Ikuti foto: jumper merah <strong>3V3 → rail +</strong> · hitam <strong>GND → rail −</strong> · jumper merah rail + ke <strong>kolom 2</strong> (cabut = LED mati) · resistor <strong>220Ω</strong> dari kolom 2 ke kolom 7 · LED melintasi parit (kaki panjang kolom 7, kaki pendek kolom 8) · hitam ke rail −. Lingkaran hijau = lubang yang saling nyambung dalam satu kolom. Resistor di foto = <strong>220Ω</strong> (±5%; merah–merah–cokelat–emas); <strong>330Ω</strong> juga boleh. Nomor kolom boleh digeser asalkan urutan sama.
    <br><strong>Peringatan:</strong> Jangan sambungkan 3V3 dan GND di kolom yang sama — baris A–E dalam satu nomor kolom saling nyambung (itu short!).
    <br>Sumber gambar: foto rangkaian buatan Koding Indonesia (FS-09).
  </figcaption>
</figure>
HTML;
    }

    private function mainWiringFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/fs09-led-breadboard-wiring.png" width="1162" height="757" alt="Photo: ESP32 on breadboard, red LED + resistor, 3V3 and GND jumpers — no code yet" loading="eager" style="width:100%;height:auto;max-height:560px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Main diagram</strong> — ESP32 sits on the breadboard (source of <strong>3V3</strong> &amp; <strong>GND</strong>). Follow the photo: red jumper <strong>3V3 → + rail</strong> · black <strong>GND → − rail</strong> · red jumper from + rail to <strong>column 2</strong> (unplug = LED off) · <strong>220Ω</strong> resistor from column 2 to column 7 · LED across the ditch (long leg column 7, short leg column 8) · black to the − rail. Green circles = holes that share one column strip. Resistor in the photo = <strong>220Ω</strong> (±5%; bands red–red–brown–gold); <strong>330Ω</strong> is also fine. Column numbers may shift if the order matches.
    <br><strong>Warning:</strong> Never put 3V3 and GND in the same column — rows A–E in one column number share a strip (that is a short!).
    <br>Image source: circuit photo by Koding Indonesia (FS-09).
  </figcaption>
</figure>
HTML;
    }

    private function body(): string
    {
        $bb = $this->breadboardFigureId();
        $jump = $this->jumperFigureId();
        $ledRes = $this->ledResistorFigureId();
        $pin = $this->pinoutFigureId();
        $breadSvg = $this->breadboardSvgId();
        $flow = $this->currentFlowSvgId();
        $main = $this->mainWiringFigureId();
        $ledSvg = $this->ledSvgId();

        return <<<HTML
<h2>Pendahuluan — LED menyala tanpa coding?</h2>
<p>Artikel ini adalah <strong>#79 (ini)</strong> · modul <strong>FS-09</strong> di jalur <em>Full Stack IoT Developer — Dari Nol</em>. Di <strong>FS-08</strong> kamu sudah paham kenapa LED butuh resistor 220Ω. Hari ini kamu <strong>merakit rangkaian pertama</strong> di breadboard — LED menyala dari pin <strong>3V3</strong> ESP32, murni wiring, <strong>belum upload sketch</strong>.</p>
<p><strong>Analogi:</strong> modul ini seperti menyalakan lampu kamar dengan saklar di stop kontak — belum pakai remote (program).</p>
<p><strong>Prasyarat:</strong> FS-08 (pilih resistor) + kenal breadboard dari FS-04 + kebiasaan cabut USB dari FS-05. <strong>Tidak ada Arduino IDE, upload sketch, atau <code>php artisan</code> hari ini</strong> — hanya breadboard, jumper, LED, resistor, dan ESP32 + kabel USB data.</p>

<p><strong>Cara pakai artikel ini (urutan kerja):</strong></p>
<ol>
<li><strong>Kumpulkan alat</strong> di meja (daftar di bawah) — belum colok USB.</li>
<li><strong>Baca + lihat gambar</strong> sampai bagian wiring step-by-step.</li>
<li><strong>Rakit di meja</strong> ikuti gambar utama (ESP32 di breadboard).</li>
<li><strong>Colok USB</strong> → LED menyala → latihan cabut jumper.</li>
<li><strong>Centang checklist 10/10</strong> di browser (widget interaktif di bawah).</li>
</ol>
<p><strong>Tidak perlu hari ini:</strong> Arduino IDE, upload sketch, Serial Monitor, Laragon, terminal proyek web, <code>php artisan</code>. Hari ini tools-nya: <strong>browser</strong> (artikel + checklist) + kit di meja.</p>

<h2>Persiapan — buka &amp; siapkan ini dulu</h2>
<p><strong>Urutan meja kerja:</strong> jangan langsung colok kabel. Ikuti urutan di bawah supaya tidak bingung.</p>
<ol>
<li><strong>Cabut USB</strong> dari ESP32 (wajib sebelum menyentuh jumper).</li>
<li>Letakkan <strong>breadboard</strong> di meja datar. ESP32 boleh dipasang di breadboard seperti gambar utama (atau di samping dulu, lalu sambungkan jumper).</li>
<li>Siapkan <strong>jumper</strong>: minimal merah (3V3), hitam (GND), plus jumper lain sesuai gambar utama.</li>
<li>Ambil <strong>LED 5mm</strong> + <strong>resistor 220Ω</strong> (330Ω juga boleh — sama seperti FS-08).</li>
<li>Buka pinout board — cari tulisan <strong>3V3</strong> dan <strong>GND</strong>.</li>
</ol>
<p><strong>Alat yang dipakai hari ini:</strong> breadboard, jumper, LED, resistor, ESP32-DevKitC-1, kabel USB data.</p>
<p><strong>Tidak dipakai hari ini:</strong> Arduino IDE, Serial Monitor, multimeter (opsional), Laragon, terminal proyek web.</p>

{$bb}
{$jump}
{$ledRes}
{$pin}

<h2>Kenali power rail breadboard</h2>
<p>Power rail = jalur listrik di tepi breadboard. Rail <strong>merah (+)</strong> kita hubungkan ke <strong>3V3</strong>. Rail <strong>biru (-)</strong> ke <strong>GND</strong>.</p>
<p><strong>Analogi:</strong> bayangkan rail seperti pipa air utama — jumper ke baris tengah seperti cabang pipa ke kran.</p>
{$breadSvg}

<h2>Rangkaian yang akan dibuat</h2>
<p>Alur listrik: <strong>3V3 → resistor → LED → GND</strong>. Resistor melindungi LED; polaritas LED harus benar. Di gambar utama kamu melihat layout nyata di breadboard (bukan skema abstrak).</p>
{$flow}
{$main}

<h2>Wiring langkah demi langkah</h2>
<p><strong>Tips:</strong> ikuti <strong>gambar utama</strong> (foto) di atas, dari kiri ke kanan. Nomor kolom boleh digeser — yang penting <strong>urutan</strong> listrik sama: <strong>3V3 → resistor → LED → GND</strong>.</p>
<p><strong>Orientasi foto (supaya tidak bingung “atas/bawah”):</strong> <em>parit</em> = celah panjang di tengah breadboard. Baris <strong>F–J</strong> = sisi atas parit · baris <strong>A–E</strong> = sisi bawah parit. Dalam satu nomor kolom, baris A–E saling nyambung; F–J juga — tapi <strong>atas dan bawah parit tidak nyambung</strong>.</p>
<ol>
<li><strong>Pastikan USB sudah dicabut</strong> dari ESP32 (ulangi kebiasaan FS-05).</li>
<li><strong>Pasang ESP32</strong> melintasi parit seperti foto (USB menghadap ke kanan / keluar).</li>
<li><strong>Power rail:</strong> jumper <strong>merah</strong> dari pin <strong>3V3</strong> ke rail merah (+). Jumper <strong>hitam</strong> dari pin <strong>GND</strong> ke rail biru (−).</li>
<li><strong>Saklar manual (kolom 2):</strong> jumper merah dari rail (+) ke <strong>kolom 2</strong> sisi atas (di foto baris J). <em>Kabel ini nanti dicabut untuk mematikan LED.</em></li>
<li><strong>Resistor 220Ω:</strong> satu kaki di <strong>kolom 2</strong>, kaki lain di <strong>kolom 7</strong> — baris yang sama di sisi atas (di foto baris I). Cincin: merah–merah–cokelat–emas. <strong>330Ω</strong> juga boleh.</li>
<li><strong>LED (melintasi parit):</strong> kaki <strong>panjang (+)</strong> di <strong>kolom 7</strong> sisi atas (satu kolom dengan ujung resistor — tidak perlu jumper ekstra). Kaki <strong>pendek (−)</strong> di <strong>kolom 8</strong> sisi bawah. Polaritas harus benar.</li>
<li><strong>Ke GND:</strong> jumper hitam dari <strong>kolom 8</strong> (kaki pendek LED) ke rail biru (−).</li>
<li><strong>Cek visual:</strong> 3V3 dan GND tidak di kolom yang sama · LED tidak terbalik · lingkaran hijau di foto = jalur yang saling nyambung.</li>
<li><strong>Colok USB</strong> — LED harus menyala lembut/terang.</li>
<li><strong>Latihan matikan:</strong> cabut jumper merah di <strong>kolom 2</strong> dari rail + — LED padam. Pasang lagi — menyala.</li>
</ol>
{$ledSvg}

<h2 id="fsiot-led-circuit-checklist">Praktik — checklist rangkaian LED</h2>
<p>Centang setiap langkah setelah kamu lakukan di meja. Target: <strong>10/10</strong>. Ada checklist interaktif di bawah; versi kertas tetap tersedia.</p>
<ul id="fsiot-led-circuit-checklist-items">
<li>USB sudah dicabut sebelum mulai wiring</li>
<li>Rail merah (+) terhubung ke pin 3V3 ESP32</li>
<li>Rail biru (-) terhubung ke pin GND ESP32</li>
<li>Resistor 220Ω (atau 330Ω) terpasang di jalur 3V3 → LED (lihat gambar utama)</li>
<li>LED kaki panjang ke sisi resistor, kaki pendek menuju GND</li>
<li>Jumper dari kaki pendek LED menuju rail GND</li>
<li>Tidak ada short antara 3V3 dan GND</li>
<li>Colok USB — LED menyala</li>
<li>Cabut jumper merah di kolom 2 — LED mati</li>
<li>Bisa jelaskan alur: 3V3 → R → LED → GND</li>
</ul>
<p><strong>Cara menguji:</strong> kerjakan checklist di browser setelah wiring di meja. Tidak perlu Arduino IDE, terminal, atau <code>php artisan</code> — “menguji” = LED menyala/mati di meja + centang checklist (bukan perintah sintaks).</p>

<h2>Kesalahan yang sering terjadi</h2>
<ul>
<li><strong>LED terbalik.</strong> Kaki pendek ke 3V3 = tidak menyala atau rusak. Balik arah LED.</li>
<li><strong>Resistor di kaki salah.</strong> Resistor harus di jalur arus, bukan di kaki GND saja tanpa sambung ke 3V3.</li>
<li><strong>Short power–GND.</strong> Jumper merah dan hitam menyentuh, atau 3V3 &amp; GND di <strong>kolom yang sama</strong> (baris A–E saling nyambung) = panas/board reset. Cabut USB, periksa ulang.</li>
<li><strong>LED tanpa resistor.</strong> Jangan pernah langsung 3V3 ke LED — ingat FS-08.</li>
<li><strong>Colok USB saat masih merakit.</strong> Selalu cabut dulu (FS-05).</li>
<li><strong>Mengira semua lubang nyambung.</strong> Hanya dalam satu baris (sisi yang sama) — lihat FS-04.</li>
</ul>

<h2>Selanjutnya</h2>
<p><strong>Intinya:</strong> kalau LED sudah menyala dan bisa dimatikan dengan cabut jumper, FS-09 selesai. Lanjut ke <strong>FS-10</strong> (digital vs analog, HIGH/LOW, pull-up) saat modulnya terbit — di sana kita pakai bahasa sinyal untuk GPIO.</p>
<p>Daftar modul lengkap: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $bb = $this->breadboardFigureEn();
        $jump = $this->jumperFigureEn();
        $ledRes = $this->ledResistorFigureEn();
        $pin = $this->pinoutFigureEn();
        $breadSvg = $this->breadboardSvgEn();
        $flow = $this->currentFlowSvgEn();
        $main = $this->mainWiringFigureEn();
        $ledSvg = $this->ledSvgEn();

        return <<<HTML
<h2>Introduction — an LED without code?</h2>
<p>This article is <strong>#79 (this article)</strong> · module <strong>FS-09</strong> on the <em>Full Stack IoT Developer — From Zero</em> track. In <strong>FS-08</strong> you learned why an LED needs a 220Ω resistor. Today you <strong>build your first circuit</strong> on a breadboard — the LED lights from the ESP32 <strong>3V3</strong> pin, wiring only, <strong>no sketch upload yet</strong>.</p>
<p><strong>Analogy:</strong> this module is like turning on a room light with a wall switch — no remote (program) yet.</p>
<p><strong>Prerequisites:</strong> FS-08 (pick a resistor) + know the breadboard from FS-04 + unplug-USB habit from FS-05. <strong>No Arduino IDE, sketch upload, or <code>php artisan</code> today</strong> — only breadboard, jumpers, LED, resistor, and ESP32 + data USB cable.</p>

<p><strong>How to use this article (work order):</strong></p>
<ol>
<li><strong>Gather tools</strong> on your desk (list below) — do not plug USB yet.</li>
<li><strong>Read + study images</strong> through the step-by-step wiring section.</li>
<li><strong>Build on the desk</strong> following the main diagram (ESP32 on the breadboard).</li>
<li><strong>Plug USB</strong> → LED lights → practice unplugging the jumper.</li>
<li><strong>Tick the 10/10 checklist</strong> in the browser (interactive widget below).</li>
</ol>
<p><strong>Not needed today:</strong> Arduino IDE, sketch upload, Serial Monitor, Laragon, web project terminal, <code>php artisan</code>. Today’s tools: <strong>browser</strong> (article + checklist) + kit on the desk.</p>

<h2>Preparation — open &amp; gather these first</h2>
<p><strong>Desk order:</strong> do not plug wires in randomly. Follow the order below so you do not get lost.</p>
<ol>
<li><strong>Unplug USB</strong> from the ESP32 (required before touching jumpers).</li>
<li>Place the <strong>breadboard</strong> on a flat desk. The ESP32 can sit on the breadboard like the main diagram (or beside it first, then connect jumpers).</li>
<li>Prepare <strong>jumpers</strong>: at least red (3V3), black (GND), plus others as in the main diagram.</li>
<li>Grab a <strong>5mm LED</strong> + <strong>220Ω resistor</strong> (330Ω is fine too — same as FS-08).</li>
<li>Open the board pinout — find <strong>3V3</strong> and <strong>GND</strong>.</li>
</ol>
<p><strong>Tools used today:</strong> breadboard, jumpers, LED, resistor, ESP32-DevKitC-1, data USB cable.</p>
<p><strong>Not used today:</strong> Arduino IDE, Serial Monitor, multimeter (optional), Laragon, web project terminal.</p>

{$bb}
{$jump}
{$ledRes}
{$pin}

<h2>Know the breadboard power rails</h2>
<p>Power rails are the supply strips on the breadboard edges. The <strong>red (+)</strong> rail connects to <strong>3V3</strong>. The <strong>blue (-)</strong> rail to <strong>GND</strong>.</p>
<p><strong>Analogy:</strong> think of rails as main water pipes — jumpers to middle rows are branches to taps.</p>
{$breadSvg}

<h2>The circuit we will build</h2>
<p>Current path: <strong>3V3 → resistor → LED → GND</strong>. The resistor protects the LED; LED polarity must be correct. The main diagram shows the real breadboard layout (not an abstract sketch).</p>
{$flow}
{$main}

<h2>Step-by-step wiring</h2>
<p><strong>Tip:</strong> follow the <strong>main diagram</strong> (photo) above, left to right. Column numbers can shift — keep the <strong>electrical order</strong>: <strong>3V3 → resistor → LED → GND</strong>.</p>
<p><strong>Photo orientation (so “top/bottom” is clear):</strong> the <em>ditch</em> is the long gap in the middle of the breadboard. Rows <strong>F–J</strong> = top side of the ditch · rows <strong>A–E</strong> = bottom side. In one column number, A–E share a strip; F–J share another — but <strong>top and bottom across the ditch do not connect</strong>.</p>
<ol>
<li><strong>Confirm USB is unplugged</strong> from the ESP32 (repeat the FS-05 habit).</li>
<li><strong>Mount the ESP32</strong> across the ditch like the photo (USB facing right / outward).</li>
<li><strong>Power rails:</strong> <strong>red</strong> jumper from pin <strong>3V3</strong> to the red (+) rail. <strong>Black</strong> jumper from pin <strong>GND</strong> to the blue (−) rail.</li>
<li><strong>Manual switch (column 2):</strong> red jumper from the (+) rail to <strong>column 2</strong> on the top side (row J in the photo). <em>This wire gets unplugged later to turn the LED off.</em></li>
<li><strong>220Ω resistor:</strong> one leg in <strong>column 2</strong>, the other in <strong>column 7</strong> — same row on the top side (row I in the photo). Bands: red–red–brown–gold. <strong>330Ω</strong> is also fine.</li>
<li><strong>LED (across the ditch):</strong> <strong>long (+)</strong> leg in <strong>column 7</strong> on the top side (same column as the resistor end — no extra jumper). <strong>Short (−)</strong> leg in <strong>column 8</strong> on the bottom side. Polarity must be correct.</li>
<li><strong>To GND:</strong> black jumper from <strong>column 8</strong> (LED short leg) to the blue (−) rail.</li>
<li><strong>Visual check:</strong> 3V3 and GND not in the same column · LED not reversed · green circles in the photo = shared strips.</li>
<li><strong>Plug USB</strong> — the LED should glow.</li>
<li><strong>Turn-off practice:</strong> unplug the red jumper at <strong>column 2</strong> from the + rail — LED off. Plug back — on.</li>
</ol>
{$ledSvg}

<h2 id="fsiot-led-circuit-checklist">Practice — LED circuit checklist</h2>
<p>Tick each step after you do it on the desk. Target: <strong>10/10</strong>. An interactive checklist is below; a paper version stays available.</p>
<ul id="fsiot-led-circuit-checklist-items">
<li>USB unplugged before wiring</li>
<li>Red (+) rail connected to ESP32 3V3 pin</li>
<li>Blue (-) rail connected to ESP32 GND pin</li>
<li>220Ω (or 330Ω) resistor in the 3V3 → LED path (see main diagram)</li>
<li>LED long leg toward resistor, short leg toward GND</li>
<li>Jumper from LED short leg to GND rail</li>
<li>No short between 3V3 and GND</li>
<li>Plug USB — LED lights</li>
<li>Unplug red jumper at column 2 — LED off</li>
<li>Can explain the path: 3V3 → R → LED → GND</li>
</ul>
<p><strong>How to test:</strong> complete the checklist in the browser after wiring on the desk. No Arduino IDE, terminal, or <code>php artisan</code> — “testing” means the LED turns on/off on the desk + checklist ticks (not typing syntax).</p>

<h2>Common mistakes</h2>
<ul>
<li><strong>Reversed LED.</strong> Short leg toward 3V3 = no light or damage. Flip the LED.</li>
<li><strong>Resistor on the wrong leg.</strong> The resistor must be in the current path, not only on GND without reaching 3V3.</li>
<li><strong>Power–GND short.</strong> Red and black touching, or 3V3 &amp; GND in the <strong>same column</strong> (rows A–E share a strip) = heat/board reset. Unplug USB and recheck.</li>
<li><strong>LED without resistor.</strong> Never wire 3V3 straight to an LED — remember FS-08.</li>
<li><strong>USB plugged while building.</strong> Always unplug first (FS-05).</li>
<li><strong>Assuming every hole connects.</strong> Only within one row (same side) — see FS-04.</li>
</ul>

<h2>Next steps</h2>
<p><strong>In short:</strong> if the LED lights and you can turn it off by unplugging a jumper, FS-09 is done. Continue to <strong>FS-10</strong> (digital vs analog, HIGH/LOW, pull-up) when that module publishes — there we use signal language for GPIO.</p>
<p>Full module list: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }
}
