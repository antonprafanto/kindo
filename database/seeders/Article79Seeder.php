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
        return <<<'SVG'
<figure role="img" aria-label="Gambar utama wiring LED di breadboard dengan ESP32" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 420" width="100%" height="auto" role="img" aria-label="Main LED breadboard wiring">
  <text x="430" y="28" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700">Gambar utama — LED menyala dari 3V3 (belum coding)</text>
  <!-- breadboard body -->
  <rect x="40" y="55" width="780" height="260" rx="10" fill="#FAFAFA" stroke="#1a1a1a" stroke-width="2.5"/>
  <!-- power rails left -->
  <rect x="55" y="70" width="22" height="230" fill="#FFCDD2" stroke="#C62828" stroke-width="1.5"/>
  <rect x="82" y="70" width="22" height="230" fill="#BBDEFB" stroke="#1565C0" stroke-width="1.5"/>
  <text x="66" y="65" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700" fill="#C62828">+</text>
  <text x="93" y="65" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700" fill="#1565C0">−</text>
  <!-- ESP32 block -->
  <rect x="480" y="95" width="280" height="160" rx="8" fill="#263238" stroke="#1a1a1a" stroke-width="2"/>
  <text x="620" y="130" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#fff">ESP32-DevKitC-1</text>
  <text x="620" y="155" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#B0BEC5">dipasang melintasi parit</text>
  <rect x="495" y="175" width="70" height="28" rx="4" fill="#FFCDD2" stroke="#fff" stroke-width="1"/>
  <text x="530" y="194" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">3V3</text>
  <rect x="675" y="175" width="70" height="28" rx="4" fill="#BBDEFB" stroke="#fff" stroke-width="1"/>
  <text x="710" y="194" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">GND</text>
  <!-- wires 3V3 / GND to rails — DIFFERENT visual paths -->
  <path d="M530 203 L530 300 L66 300 L66 290" fill="none" stroke="#C62828" stroke-width="3"/>
  <text x="280" y="292" font-family="system-ui,sans-serif" font-size="11" fill="#C62828" font-weight="700">① 3V3 → rail +</text>
  <path d="M710 203 L710 320 L93 320 L93 290" fill="none" stroke="#212121" stroke-width="3"/>
  <text x="400" y="338" font-family="system-ui,sans-serif" font-size="11" fill="#212121" font-weight="700">② GND → rail −</text>
  <!-- column 2 jumper from + -->
  <path d="M66 120 L160 120" fill="none" stroke="#C62828" stroke-width="3"/>
  <circle cx="160" cy="120" r="7" fill="#C62828"/>
  <text x="160" y="105" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" font-weight="700">kolom 2</text>
  <text x="200" y="88" font-family="system-ui,sans-serif" font-size="11" fill="#C62828" font-weight="700">③ cabut di sini = LED mati</text>
  <!-- resistor -->
  <rect x="175" y="108" width="90" height="24" rx="4" fill="#FFF8E1" stroke="#1a1a1a" stroke-width="2"/>
  <text x="220" y="125" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">220Ω</text>
  <text x="220" y="150" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">④ resistor</text>
  <!-- LED -->
  <polygon points="290,110 290,140 320,125" fill="#FFEB3B" stroke="#1a1a1a" stroke-width="2"/>
  <line x1="320" y1="115" x2="320" y2="135" stroke="#1a1a1a" stroke-width="3"/>
  <text x="340" y="118" font-family="system-ui,sans-serif" font-size="11" font-weight="700">⑤ LED</text>
  <text x="340" y="134" font-family="system-ui,sans-serif" font-size="10" fill="#2E7D32">kaki panjang (+)</text>
  <text x="340" y="148" font-family="system-ui,sans-serif" font-size="10" fill="#C62828">kaki pendek (−)</text>
  <!-- LED cathode to GND rail -->
  <path d="M320 125 L380 125 L380 250 L93 250" fill="none" stroke="#212121" stroke-width="3"/>
  <text x="300" y="240" font-family="system-ui,sans-serif" font-size="11" fill="#212121" font-weight="700">⑥ ke rail −</text>
  <!-- warning box -->
  <rect x="120" y="355" width="620" height="50" rx="6" fill="#FFEBEE" stroke="#C62828" stroke-width="2"/>
  <text x="430" y="378" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700" fill="#B71C1C">Jangan sambungkan 3V3 dan GND di kolom yang sama</text>
  <text x="430" y="398" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Baris A–E dalam satu nomor kolom saling nyambung — itu short!</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
  <strong>Gambar utama</strong> — ESP32 dipasang di breadboard (sumber <strong>3V3</strong> &amp; <strong>GND</strong>). Alur: ① 3V3→rail + · ② GND→rail − · ③ jumper ke <strong>kolom 2</strong> (cabut = LED mati) · ④ resistor 220Ω · ⑤ LED (kaki panjang ke resistor) · ⑥ kaki pendek ke rail −. Nomor kolom boleh digeser asalkan urutan sama.
  <br>Sumber gambar: diagram rangkaian buatan Koding Indonesia (FS-09) — mengganti diagram lama yang berisiko short.
</figcaption>
</figure>
SVG;
    }

    private function mainWiringFigureEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Main LED breadboard wiring diagram with ESP32" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 420" width="100%" height="auto" role="img" aria-label="Main LED breadboard wiring">
  <text x="430" y="28" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700">Main diagram — LED lit from 3V3 (no code yet)</text>
  <rect x="40" y="55" width="780" height="260" rx="10" fill="#FAFAFA" stroke="#1a1a1a" stroke-width="2.5"/>
  <rect x="55" y="70" width="22" height="230" fill="#FFCDD2" stroke="#C62828" stroke-width="1.5"/>
  <rect x="82" y="70" width="22" height="230" fill="#BBDEFB" stroke="#1565C0" stroke-width="1.5"/>
  <text x="66" y="65" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700" fill="#C62828">+</text>
  <text x="93" y="65" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700" fill="#1565C0">−</text>
  <rect x="480" y="95" width="280" height="160" rx="8" fill="#263238" stroke="#1a1a1a" stroke-width="2"/>
  <text x="620" y="130" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#fff">ESP32-DevKitC-1</text>
  <text x="620" y="155" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#B0BEC5">mounted across the trench</text>
  <rect x="495" y="175" width="70" height="28" rx="4" fill="#FFCDD2" stroke="#fff" stroke-width="1"/>
  <text x="530" y="194" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">3V3</text>
  <rect x="675" y="175" width="70" height="28" rx="4" fill="#BBDEFB" stroke="#fff" stroke-width="1"/>
  <text x="710" y="194" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">GND</text>
  <path d="M530 203 L530 300 L66 300 L66 290" fill="none" stroke="#C62828" stroke-width="3"/>
  <text x="280" y="292" font-family="system-ui,sans-serif" font-size="11" fill="#C62828" font-weight="700">① 3V3 → + rail</text>
  <path d="M710 203 L710 320 L93 320 L93 290" fill="none" stroke="#212121" stroke-width="3"/>
  <text x="400" y="338" font-family="system-ui,sans-serif" font-size="11" fill="#212121" font-weight="700">② GND → − rail</text>
  <path d="M66 120 L160 120" fill="none" stroke="#C62828" stroke-width="3"/>
  <circle cx="160" cy="120" r="7" fill="#C62828"/>
  <text x="160" y="105" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" font-weight="700">column 2</text>
  <text x="210" y="88" font-family="system-ui,sans-serif" font-size="11" fill="#C62828" font-weight="700">③ unplug here = LED off</text>
  <rect x="175" y="108" width="90" height="24" rx="4" fill="#FFF8E1" stroke="#1a1a1a" stroke-width="2"/>
  <text x="220" y="125" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">220Ω</text>
  <text x="220" y="150" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">④ resistor</text>
  <polygon points="290,110 290,140 320,125" fill="#FFEB3B" stroke="#1a1a1a" stroke-width="2"/>
  <line x1="320" y1="115" x2="320" y2="135" stroke="#1a1a1a" stroke-width="3"/>
  <text x="340" y="118" font-family="system-ui,sans-serif" font-size="11" font-weight="700">⑤ LED</text>
  <text x="340" y="134" font-family="system-ui,sans-serif" font-size="10" fill="#2E7D32">long leg (+)</text>
  <text x="340" y="148" font-family="system-ui,sans-serif" font-size="10" fill="#C62828">short leg (−)</text>
  <path d="M320 125 L380 125 L380 250 L93 250" fill="none" stroke="#212121" stroke-width="3"/>
  <text x="300" y="240" font-family="system-ui,sans-serif" font-size="11" fill="#212121" font-weight="700">⑥ to − rail</text>
  <rect x="120" y="355" width="620" height="50" rx="6" fill="#FFEBEE" stroke="#C62828" stroke-width="2"/>
  <text x="430" y="378" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700" fill="#B71C1C">Never put 3V3 and GND in the same column</text>
  <text x="430" y="398" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Rows A–E in one column number share a strip — that is a short!</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
  <strong>Main diagram</strong> — ESP32 sits on the breadboard (source of <strong>3V3</strong> &amp; <strong>GND</strong>). Path: ① 3V3→+ rail · ② GND→− rail · ③ jumper to <strong>column 2</strong> (unplug = LED off) · ④ 220Ω resistor · ⑤ LED (long leg to resistor) · ⑥ short leg to − rail. Column numbers can shift if the order matches.
  <br>Image source: circuit diagram by Koding Indonesia (FS-09) — replaces an earlier diagram that risked a short.
</figcaption>
</figure>
SVG;
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
<li>Ambil <strong>LED 5mm</strong> + <strong>resistor 220Ω</strong> (330Ω juga boleh).</li>
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
<p>Alur listrik: <strong>3V3 → resistor 220Ω → LED → GND</strong>. Resistor melindungi LED; polaritas LED harus benar.</p>
{$flow}
{$main}

<h2>Wiring langkah demi langkah</h2>
<p><strong>Tips:</strong> ikuti <strong>gambar utama</strong> di atas (ada ESP32-nya). Nomor kolom boleh digeser — yang penting <strong>urutan</strong> listrik sama: 3V3 → resistor → LED → GND.</p>
<ol>
<li><strong>Pasang ESP32</strong> di breadboard (melintasi parit) seperti foto, atau letakkan di samping lalu sambungkan jumper ke rail.</li>
<li><strong>Power rail:</strong> jumper merah dari pin <strong>3V3</strong> ke rail merah (+). Jumper hitam dari pin <strong>GND</strong> ke rail biru (−).</li>
<li><strong>Kolom 2:</strong> jumper merah dari rail merah (+) ke lubang di <strong>kolom 2</strong> (misalnya baris J). <em>Ini kabel yang nanti dicabut untuk mematikan LED.</em></li>
<li><strong>Resistor:</strong> satu kaki di <strong>kolom 2</strong>, kaki lain di kolom menuju kaki panjang LED (lihat foto — biasanya beberapa kolom di sebelahnya).</li>
<li><strong>LED:</strong> kaki <strong>panjang (+)</strong> ke sisi resistor, kaki <strong>pendek (−)</strong> ke jalur yang menuju GND. Polaritas harus benar.</li>
<li><strong>Ke GND:</strong> jumper hitam dari kaki pendek LED (atau kolomnya) ke rail biru (−).</li>
<li><strong>Cek visual:</strong> tidak ada kabel merah menyentuh hitam langsung (short). LED tidak terbalik. Rail + hanya dari 3V3, rail − hanya ke GND.</li>
<li><strong>Colok USB</strong> — LED harus menyala lembut/terang.</li>
<li><strong>Latihan matikan:</strong> cabut jumper merah di kolom 2 dari rail + — LED padam. Pasang lagi — menyala.</li>
</ol>
{$ledSvg}

<h2 id="fsiot-led-circuit-checklist">Praktik — checklist rangkaian LED</h2>
<p>Centang setiap langkah setelah kamu lakukan di meja. Target: <strong>10/10</strong>. Ada checklist interaktif di bawah; versi kertas tetap tersedia.</p>
<ul id="fsiot-led-circuit-checklist-items">
<li>USB sudah dicabut sebelum mulai wiring</li>
<li>Rail merah (+) terhubung ke pin 3V3 ESP32</li>
<li>Rail biru (-) terhubung ke pin GND ESP32</li>
<li>Resistor 220 ohm (atau 330 ohm) terpasang di jalur 3V3 → LED (lihat gambar utama)</li>
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
<li>Grab a <strong>5mm LED</strong> + <strong>220Ω resistor</strong> (330Ω is fine too).</li>
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
<p>Current path: <strong>3V3 → 220Ω resistor → LED → GND</strong>. The resistor protects the LED; LED polarity must be correct.</p>
{$flow}
{$main}

<h2>Step-by-step wiring</h2>
<p><strong>Tip:</strong> follow the <strong>main diagram</strong> above (it shows the ESP32). Column numbers can shift — keep the <strong>electrical order</strong>: 3V3 → resistor → LED → GND.</p>
<ol>
<li><strong>Mount the ESP32</strong> on the breadboard (across the ditch) like the photo, or place it beside and jumper to the rails.</li>
<li><strong>Power rails:</strong> red jumper from <strong>3V3</strong> to the red (+) rail. Black jumper from <strong>GND</strong> to the blue (−) rail.</li>
<li><strong>Column 2:</strong> red jumper from the red (+) rail to a hole in <strong>column 2</strong> (e.g. row J). <em>This wire gets unplugged to turn the LED off.</em></li>
<li><strong>Resistor:</strong> one leg in <strong>column 2</strong>, the other toward the LED long leg (see the photo — usually a few columns over).</li>
<li><strong>LED:</strong> <strong>long (+)</strong> leg toward the resistor, <strong>short (−)</strong> leg toward the path to GND. Polarity must be correct.</li>
<li><strong>To GND:</strong> black jumper from the LED short leg (or its column) to the blue (−) rail.</li>
<li><strong>Visual check:</strong> no red wire touching black directly (short). LED not reversed. + rail only from 3V3, − rail only to GND.</li>
<li><strong>Plug USB</strong> — the LED should glow.</li>
<li><strong>Turn-off practice:</strong> unplug the red jumper at column 2 from the + rail — LED off. Plug back — on.</li>
</ol>
{$ledSvg}

<h2 id="fsiot-led-circuit-checklist">Practice — LED circuit checklist</h2>
<p>Tick each step after you do it on the desk. Target: <strong>10/10</strong>. An interactive checklist is below; a paper version stays available.</p>
<ul id="fsiot-led-circuit-checklist-items">
<li>USB unplugged before wiring</li>
<li>Red (+) rail connected to ESP32 3V3 pin</li>
<li>Blue (-) rail connected to ESP32 GND pin</li>
<li>220 ohm (or 330 ohm) resistor in the 3V3 → LED path (see main diagram)</li>
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
