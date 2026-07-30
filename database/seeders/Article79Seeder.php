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
                'seo_title'          => 'LED di Breadboard Awam — Rangkaian Pertama 3.3V — Full Stack IoT #79',
                'seo_title_en'       => 'Beginner LED on Breadboard — First 3.3V Circuit — Full Stack IoT #79',
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

    private function ledFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/kit-led-5mm.jpg" width="1200" height="900" alt="LED 5mm — kaki panjang anoda, kaki pendek katoda" loading="lazy" style="width:100%;height:auto;max-height:320px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>LED 5mm</strong> dari kit: kaki <strong>panjang = anoda (+)</strong>, kaki <strong>pendek = katoda (-)</strong>. Sebelum dicolok, pegang &amp; cocokkan dengan diagram polaritas di bawah.
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:5mm_LED_Light-emitting_diode.jpg" rel="noopener noreferrer" target="_blank">Wikimedia Commons — 5mm LED</a> (CC BY-SA 3.0).
  </figcaption>
</figure>
HTML;
    }

    private function ledFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/kit-led-5mm.jpg" width="1200" height="900" alt="5mm LED — long leg anode, short leg cathode" loading="lazy" style="width:100%;height:auto;max-height:320px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>5mm LED</strong> from the kit: <strong>long leg = anode (+)</strong>, <strong>short leg = cathode (-)</strong>. Before plugging in, hold it and match the polarity diagram below.
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:5mm_LED_Light-emitting_diode.jpg" rel="noopener noreferrer" target="_blank">Wikimedia Commons — 5mm LED</a> (CC BY-SA 3.0).
  </figcaption>
</figure>
HTML;
    }

    private function resistorFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/kit-resistor.jpg" width="1200" height="900" alt="Resistor 220 ohm — rem arus untuk LED" loading="lazy" style="width:100%;height:auto;max-height:320px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    Ambil resistor <strong>220 ohm</strong> (atau 330 ohm) dari kit. Foto ini contoh bentuk resistor — angka di kit kamu 220/330.
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:100_ohms_5%25_axial_resistor.jpg" rel="noopener noreferrer" target="_blank">oomlout — axial resistor</a> · Wikimedia Commons (CC BY-SA 2.0).
  </figcaption>
</figure>
HTML;
    }

    private function resistorFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/kit-resistor.jpg" width="1200" height="900" alt="220 ohm resistor — current brake for LED" loading="lazy" style="width:100%;height:auto;max-height:320px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    Grab a <strong>220 ohm</strong> (or 330 ohm) resistor from the kit. This photo shows the shape — your kit values are 220/330.
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:100_ohms_5%25_axial_resistor.jpg" rel="noopener noreferrer" target="_blank">oomlout — axial resistor</a> · Wikimedia Commons (CC BY-SA 2.0).
  </figcaption>
</figure>
HTML;
    }

    private function realWiringFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/breadboard-led-example.jpg" width="699" height="386" alt="Contoh rangkaian LED dan resistor di breadboard nyata" loading="lazy" style="width:100%;height:auto;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Contoh foto nyata</strong> (bukan ESP32): LED + resistor + kabel di breadboard — bentuknya mirip yang akan kamu rakit, bedanya kita pakai pin <strong>3V3</strong> dari ESP32, bukan baterai 9V.
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:Circuit_with_components_visible_and_breadboard_with_led_and_resistance_and_wire.jpg" rel="noopener noreferrer" target="_blank">T Matheij — breadboard LED circuit</a> · Wikimedia Commons (own work).
  </figcaption>
</figure>
HTML;
    }

    private function realWiringFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/breadboard-led-example.jpg" width="699" height="386" alt="Real example of LED and resistor on a breadboard" loading="lazy" style="width:100%;height:auto;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Real photo example</strong> (not ESP32): LED + resistor + wires on a breadboard — similar to what you will build, except we use the ESP32 <strong>3V3</strong> pin, not a 9V battery.
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:Circuit_with_components_visible_and_breadboard_with_led_and_resistance_and_wire.jpg" rel="noopener noreferrer" target="_blank">T Matheij — breadboard LED circuit</a> · Wikimedia Commons (own work).
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
  <img src="/images/fsiot/esp32-devkitc-1-pinlayout.jpg" width="1200" height="800" alt="Pinout ESP32-DevKitC-1 — lokasi 3V3 dan GND" loading="lazy" style="width:100%;height:auto;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    Cari pin <strong>3V3</strong> dan <strong>GND</strong> di board kamu. Hari ini kita pakai pin <strong>3V3</strong> (bukan GPIO) — LED menyala tanpa program.
    <br>Sumber gambar: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/boards/ESP32-DevKitC-1.html" rel="noopener noreferrer" target="_blank">Espressif — ESP32-DevKitC-1</a> (dokumen resmi).
  </figcaption>
</figure>
HTML;
    }

    private function pinoutFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/esp32-devkitc-1-pinlayout.jpg" width="1200" height="800" alt="ESP32-DevKitC-1 pinout — 3V3 and GND locations" loading="lazy" style="width:100%;height:auto;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    Find the <strong>3V3</strong> and <strong>GND</strong> pins on your board. Today we use the <strong>3V3</strong> pin (not GPIO) — the LED lights with no program.
    <br>Image source: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/boards/ESP32-DevKitC-1.html" rel="noopener noreferrer" target="_blank">Espressif — ESP32-DevKitC-1</a> (official docs).
  </figcaption>
</figure>
HTML;
    }

    private function powerRailSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Menyambung 3V3 dan GND ke power rail breadboard" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 760 260" width="100%" height="auto" role="img" aria-label="Power rail wiring">
  <text x="380" y="26" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700">Langkah 1-2: power rail dulu</text>
  <rect x="40" y="50" width="120" height="160" rx="6" fill="#ECEFF1" stroke="#1a1a1a" stroke-width="2"/>
  <text x="100" y="78" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">ESP32</text>
  <text x="100" y="100" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#1565C0">3V3</text>
  <text x="100" y="175" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11">GND</text>
  <rect x="220" y="45" width="28" height="170" fill="#FFCDD2" stroke="#1a1a1a" stroke-width="2"/>
  <rect x="258" y="45" width="28" height="170" fill="#BBDEFB" stroke="#1a1a1a" stroke-width="2"/>
  <text x="234" y="38" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">+</text>
  <text x="272" y="38" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">-</text>
  <text x="254" y="230" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">Power rail</text>
  <line x1="160" y1="98" x2="220" y2="70" stroke="#C62828" stroke-width="3"/>
  <text x="175" y="88" font-family="system-ui,sans-serif" font-size="11" fill="#C62828">merah</text>
  <line x1="160" y1="173" x2="258" y2="200" stroke="#1a1a1a" stroke-width="3"/>
  <text x="175" y="195" font-family="system-ui,sans-serif" font-size="11">hitam</text>
  <rect x="360" y="55" width="360" height="150" rx="8" fill="#fff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="540" y="85" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">Aturan aman</text>
  <text x="540" y="110" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12">1. Cabut USB dulu</text>
  <text x="540" y="132" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12">2. Rail + = 3V3</text>
  <text x="540" y="154" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12">3. Rail - = GND</text>
  <text x="540" y="176" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12">4. Jangan tukar merah/hitam</text>
  <text x="380" y="248" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Buatan Koding Indonesia</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Sambungkan 3V3 ke rail merah (+) dan GND ke rail biru (-) sebelum pasang LED (buatan Koding Indonesia).</figcaption>
</figure>
SVG;
    }

    private function powerRailSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Connect 3V3 and GND to breadboard power rails" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 760 260" width="100%" height="auto" role="img" aria-label="Power rail wiring">
  <text x="380" y="26" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700">Steps 1-2: power rails first</text>
  <rect x="40" y="50" width="120" height="160" rx="6" fill="#ECEFF1" stroke="#1a1a1a" stroke-width="2"/>
  <text x="100" y="78" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">ESP32</text>
  <text x="100" y="100" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#1565C0">3V3</text>
  <text x="100" y="175" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11">GND</text>
  <rect x="220" y="45" width="28" height="170" fill="#FFCDD2" stroke="#1a1a1a" stroke-width="2"/>
  <rect x="258" y="45" width="28" height="170" fill="#BBDEFB" stroke="#1a1a1a" stroke-width="2"/>
  <text x="234" y="38" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">+</text>
  <text x="272" y="38" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">-</text>
  <text x="254" y="230" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">Power rails</text>
  <line x1="160" y1="98" x2="220" y2="70" stroke="#C62828" stroke-width="3"/>
  <text x="175" y="88" font-family="system-ui,sans-serif" font-size="11" fill="#C62828">red</text>
  <line x1="160" y1="173" x2="258" y2="200" stroke="#1a1a1a" stroke-width="3"/>
  <text x="175" y="195" font-family="system-ui,sans-serif" font-size="11">black</text>
  <rect x="360" y="55" width="360" height="150" rx="8" fill="#fff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="540" y="85" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">Safety rules</text>
  <text x="540" y="110" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12">1. Unplug USB first</text>
  <text x="540" y="132" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12">2. + rail = 3V3</text>
  <text x="540" y="154" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12">3. - rail = GND</text>
  <text x="540" y="176" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12">4. Do not swap red/black</text>
  <text x="380" y="248" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">By Koding Indonesia</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Connect 3V3 to the red (+) rail and GND to the blue (-) rail before adding the LED (by Koding Indonesia).</figcaption>
</figure>
SVG;
    }

    private function ledPolaritySvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Polaritas LED di breadboard" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 220" width="100%" height="auto" role="img" aria-label="LED polarity on breadboard">
  <text x="360" y="26" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700">LED: kaki panjang ke arus, pendek ke GND</text>
  <g fill="#FFFDE7" stroke="#1a1a1a" stroke-width="1.5">
    <rect x="120" y="80" width="28" height="28"/><rect x="160" y="80" width="28" height="28"/><rect x="200" y="80" width="28" height="28"/><rect x="240" y="80" width="28" height="28"/><rect x="280" y="80" width="28" height="28"/>
  </g>
  <text x="200" y="72" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">baris 15</text>
  <line x1="134" y1="94" x2="134" y2="130" stroke="#1a1a1a" stroke-width="5" stroke-linecap="round"/>
  <line x1="266" y1="94" x2="266" y2="118" stroke="#1a1a1a" stroke-width="5" stroke-linecap="round"/>
  <circle cx="200" cy="55" r="22" fill="#FFEB3B" stroke="#1a1a1a" stroke-width="2"/>
  <text x="90" y="155" font-family="system-ui,sans-serif" font-size="12" font-weight="700" fill="#2E7D32">panjang (+)</text>
  <text x="310" y="145" font-family="system-ui,sans-serif" font-size="12" font-weight="700" fill="#C62828">pendek (-)</text>
  <g fill="#FFFDE7" stroke="#1a1a1a" stroke-width="1.5">
    <rect x="120" y="150" width="28" height="28"/><rect x="160" y="150" width="28" height="28"/><rect x="200" y="150" width="28" height="28"/>
  </g>
  <text x="200" y="198" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">baris 16 = kaki pendek LED + jumper ke GND</text>
  <text x="360" y="215" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Buatan Koding Indonesia</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Kaki panjang (anoda) di baris 15 bersama resistor; kaki pendek (katoda) di baris 16 menuju GND (buatan Koding Indonesia).</figcaption>
</figure>
SVG;
    }

    private function ledPolaritySvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="LED polarity on a breadboard" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 220" width="100%" height="auto" role="img" aria-label="LED polarity on breadboard">
  <text x="360" y="26" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700">LED: long leg toward current, short leg toward GND</text>
  <g fill="#FFFDE7" stroke="#1a1a1a" stroke-width="1.5">
    <rect x="120" y="80" width="28" height="28"/><rect x="160" y="80" width="28" height="28"/><rect x="200" y="80" width="28" height="28"/><rect x="240" y="80" width="28" height="28"/><rect x="280" y="80" width="28" height="28"/>
  </g>
  <text x="200" y="72" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">row 15</text>
  <line x1="134" y1="94" x2="134" y2="130" stroke="#1a1a1a" stroke-width="5" stroke-linecap="round"/>
  <line x1="266" y1="94" x2="266" y2="118" stroke="#1a1a1a" stroke-width="5" stroke-linecap="round"/>
  <circle cx="200" cy="55" r="22" fill="#FFEB3B" stroke="#1a1a1a" stroke-width="2"/>
  <text x="90" y="155" font-family="system-ui,sans-serif" font-size="12" font-weight="700" fill="#2E7D32">long (+)</text>
  <text x="310" y="145" font-family="system-ui,sans-serif" font-size="12" font-weight="700" fill="#C62828">short (-)</text>
  <g fill="#FFFDE7" stroke="#1a1a1a" stroke-width="1.5">
    <rect x="120" y="150" width="28" height="28"/><rect x="160" y="150" width="28" height="28"/><rect x="200" y="150" width="28" height="28"/>
  </g>
  <text x="200" y="198" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">row 16 = LED short leg + jumper to GND</text>
  <text x="360" y="215" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">By Koding Indonesia</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Long leg (anode) in row 15 with the resistor; short leg (cathode) in row 16 toward GND (by Koding Indonesia).</figcaption>
</figure>
SVG;
    }

    private function wiringStepsSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Lima langkah wiring LED resistor" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 760 360" width="100%" height="auto" role="img" aria-label="Five wiring steps">
  <text x="380" y="26" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700">Urutan wiring (baris 12 - 15 - 16)</text>
  <circle cx="44" cy="58" r="16" fill="#1565C0" stroke="#1a1a1a" stroke-width="2"/><text x="44" y="63" text-anchor="middle" fill="#fff" font-family="system-ui,sans-serif" font-size="13" font-weight="700">1</text>
  <text x="72" y="54" font-family="system-ui,sans-serif" font-size="12"><tspan>Jumper rail + ke </tspan><tspan font-weight="700">baris 12</tspan></text>
  <text x="72" y="72" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">Cabut kabel ini untuk matikan LED</text>
  <circle cx="44" cy="108" r="16" fill="#1565C0" stroke="#1a1a1a" stroke-width="2"/><text x="44" y="113" text-anchor="middle" fill="#fff" font-family="system-ui,sans-serif" font-size="13" font-weight="700">2</text>
  <text x="72" y="104" font-family="system-ui,sans-serif" font-size="12"><tspan>Resistor 220 ohm: kaki 1 </tspan><tspan font-weight="700">baris 12</tspan></text>
  <text x="72" y="120" font-family="system-ui,sans-serif" font-size="12"><tspan>kaki 2 di </tspan><tspan font-weight="700">baris 15</tspan></text>
  <circle cx="44" cy="158" r="16" fill="#1565C0" stroke="#1a1a1a" stroke-width="2"/><text x="44" y="163" text-anchor="middle" fill="#fff" font-family="system-ui,sans-serif" font-size="13" font-weight="700">3</text>
  <text x="72" y="154" font-family="system-ui,sans-serif" font-size="12"><tspan>LED panjang di </tspan><tspan font-weight="700">baris 15</tspan></text>
  <text x="72" y="170" font-family="system-ui,sans-serif" font-size="12"><tspan>LED pendek di </tspan><tspan font-weight="700">baris 16</tspan></text>
  <circle cx="44" cy="208" r="16" fill="#1565C0" stroke="#1a1a1a" stroke-width="2"/><text x="44" y="213" text-anchor="middle" fill="#fff" font-family="system-ui,sans-serif" font-size="13" font-weight="700">4</text>
  <text x="72" y="208" font-family="system-ui,sans-serif" font-size="12"><tspan>Jumper </tspan><tspan font-weight="700">baris 16</tspan><tspan> ke rail biru (GND)</tspan></text>
  <circle cx="44" cy="258" r="16" fill="#2E7D32" stroke="#1a1a1a" stroke-width="2"/><text x="44" y="263" text-anchor="middle" fill="#fff" font-family="system-ui,sans-serif" font-size="13" font-weight="700">5</text>
  <text x="72" y="258" font-family="system-ui,sans-serif" font-size="12" font-weight="700">Colok USB - LED harus menyala</text>
  <rect x="400" y="48" width="320" height="250" rx="8" fill="#fff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="560" y="78" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">Alur listrik</text>
  <text x="560" y="108" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12">3V3 - R - LED - GND</text>
  <line x1="430" y1="135" x2="690" y2="135" stroke="#1565C0" stroke-width="2.5"/>
  <text x="560" y="162" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">baris 12 - 15 - 16</text>
  <text x="560" y="195" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11">Matikan: cabut jumper</text>
  <text x="560" y="215" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11">baris 12 dari rail +</text>
  <text x="560" y="250" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#C62828">Jangan tukar merah/hitam</text>
  <text x="380" y="340" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Buatan Koding Indonesia</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Lima langkah wiring yang kita ikuti hari ini — nomor baris boleh digeser asal urutan sama (buatan Koding Indonesia).</figcaption>
</figure>
SVG;
    }

    private function wiringStepsSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Five LED resistor wiring steps" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 760 360" width="100%" height="auto" role="img" aria-label="Five wiring steps">
  <text x="380" y="26" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700">Wiring order (rows 12 - 15 - 16)</text>
  <circle cx="44" cy="58" r="16" fill="#1565C0" stroke="#1a1a1a" stroke-width="2"/><text x="44" y="63" text-anchor="middle" fill="#fff" font-family="system-ui,sans-serif" font-size="13" font-weight="700">1</text>
  <text x="72" y="54" font-family="system-ui,sans-serif" font-size="12"><tspan>Jumper + rail to </tspan><tspan font-weight="700">row 12</tspan></text>
  <text x="72" y="72" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">Unplug this wire to turn LED off</text>
  <circle cx="44" cy="108" r="16" fill="#1565C0" stroke="#1a1a1a" stroke-width="2"/><text x="44" y="113" text-anchor="middle" fill="#fff" font-family="system-ui,sans-serif" font-size="13" font-weight="700">2</text>
  <text x="72" y="104" font-family="system-ui,sans-serif" font-size="12"><tspan>220 ohm resistor: leg 1 </tspan><tspan font-weight="700">row 12</tspan></text>
  <text x="72" y="120" font-family="system-ui,sans-serif" font-size="12"><tspan>leg 2 in </tspan><tspan font-weight="700">row 15</tspan></text>
  <circle cx="44" cy="158" r="16" fill="#1565C0" stroke="#1a1a1a" stroke-width="2"/><text x="44" y="163" text-anchor="middle" fill="#fff" font-family="system-ui,sans-serif" font-size="13" font-weight="700">3</text>
  <text x="72" y="154" font-family="system-ui,sans-serif" font-size="12"><tspan>LED long leg in </tspan><tspan font-weight="700">row 15</tspan></text>
  <text x="72" y="170" font-family="system-ui,sans-serif" font-size="12"><tspan>LED short leg in </tspan><tspan font-weight="700">row 16</tspan></text>
  <circle cx="44" cy="208" r="16" fill="#1565C0" stroke="#1a1a1a" stroke-width="2"/><text x="44" y="213" text-anchor="middle" fill="#fff" font-family="system-ui,sans-serif" font-size="13" font-weight="700">4</text>
  <text x="72" y="208" font-family="system-ui,sans-serif" font-size="12"><tspan>Jumper </tspan><tspan font-weight="700">row 16</tspan><tspan> to blue rail (GND)</tspan></text>
  <circle cx="44" cy="258" r="16" fill="#2E7D32" stroke="#1a1a1a" stroke-width="2"/><text x="44" y="263" text-anchor="middle" fill="#fff" font-family="system-ui,sans-serif" font-size="13" font-weight="700">5</text>
  <text x="72" y="258" font-family="system-ui,sans-serif" font-size="12" font-weight="700">Plug USB - LED should light</text>
  <rect x="400" y="48" width="320" height="250" rx="8" fill="#fff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="560" y="78" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">Current path</text>
  <text x="560" y="108" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12">3V3 - R - LED - GND</text>
  <line x1="430" y1="135" x2="690" y2="135" stroke="#1565C0" stroke-width="2.5"/>
  <text x="560" y="162" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">rows 12 - 15 - 16</text>
  <text x="560" y="195" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11">Turn off: unplug jumper</text>
  <text x="560" y="215" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11">from row 12 to + rail</text>
  <text x="560" y="250" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#C62828">Do not swap red/black</text>
  <text x="380" y="340" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">By Koding Indonesia</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Five wiring steps for today — row numbers can shift as long as the order stays the same (by Koding Indonesia).</figcaption>
</figure>
SVG;
    }

    private function fullCircuitSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Rangkaian lengkap LED resistor di breadboard" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 240" width="100%" height="auto" role="img" aria-label="Full circuit">
  <text x="360" y="24" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700">Rangkaian lengkap (tanpa coding)</text>
  <line x1="50" y1="120" x2="130" y2="120" stroke="#1565C0" stroke-width="3"/>
  <text x="90" y="105" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">3V3</text>
  <rect x="130" y="102" width="60" height="36" rx="4" fill="#FFF8E1" stroke="#1a1a1a" stroke-width="2"/>
  <text x="160" y="126" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">220R</text>
  <line x1="190" y1="120" x2="260" y2="120" stroke="#1565C0" stroke-width="3"/>
  <polygon points="270,105 270,135 295,120" fill="#FFEB3B" stroke="#1a1a1a" stroke-width="2"/>
  <line x1="295" y1="120" x2="380" y2="120" stroke="#1565C0" stroke-width="3"/>
  <line x1="380" y1="120" x2="380" y2="170" stroke="#1565C0" stroke-width="3"/>
  <line x1="380" y1="170" x2="50" y2="170" stroke="#1565C0" stroke-width="3"/>
  <line x1="50" y1="170" x2="50" y2="120" stroke="#1565C0" stroke-width="3"/>
  <text x="380" y="195" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12">GND</text>
  <text x="230" y="150" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">LED ~2V drop</text>
  <text x="360" y="225" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Buatan Koding Indonesia — pin 3V3, bukan GPIO</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Alur listrik: 3V3 → resistor 220 ohm → LED → GND. Nanti di modul coding kita ganti 3V3 dengan GPIO (buatan Koding Indonesia).</figcaption>
</figure>
SVG;
    }

    private function fullCircuitSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Full LED resistor circuit on breadboard" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 240" width="100%" height="auto" role="img" aria-label="Full circuit">
  <text x="360" y="24" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700">Full circuit (no code)</text>
  <line x1="50" y1="120" x2="130" y2="120" stroke="#1565C0" stroke-width="3"/>
  <text x="90" y="105" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">3V3</text>
  <rect x="130" y="102" width="60" height="36" rx="4" fill="#FFF8E1" stroke="#1a1a1a" stroke-width="2"/>
  <text x="160" y="126" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">220R</text>
  <line x1="190" y1="120" x2="260" y2="120" stroke="#1565C0" stroke-width="3"/>
  <polygon points="270,105 270,135 295,120" fill="#FFEB3B" stroke="#1a1a1a" stroke-width="2"/>
  <line x1="295" y1="120" x2="380" y2="120" stroke="#1565C0" stroke-width="3"/>
  <line x1="380" y1="120" x2="380" y2="170" stroke="#1565C0" stroke-width="3"/>
  <line x1="380" y1="170" x2="50" y2="170" stroke="#1565C0" stroke-width="3"/>
  <line x1="50" y1="170" x2="50" y2="120" stroke="#1565C0" stroke-width="3"/>
  <text x="380" y="195" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12">GND</text>
  <text x="230" y="150" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">LED ~2V drop</text>
  <text x="360" y="225" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">By Koding Indonesia — 3V3 pin, not GPIO</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Current path: 3V3 → 220 ohm resistor → LED → GND. In a later coding module we swap 3V3 for GPIO (by Koding Indonesia).</figcaption>
</figure>
SVG;
    }

    private function body(): string
    {
        $bb = $this->breadboardFigureId();
        $jump = $this->jumperFigureId();
        $led = $this->ledFigureId();
        $res = $this->resistorFigureId();
        $pin = $this->pinoutFigureId();
        $rail = $this->powerRailSvgId();
        $pol = $this->ledPolaritySvgId();
        $steps = $this->wiringStepsSvgId();
        $circuit = $this->fullCircuitSvgId();
        $real = $this->realWiringFigureId();

        return <<<HTML
<h2>Pendahuluan — LED menyala tanpa coding?</h2>
<p>Artikel ini adalah <strong>#79 (ini)</strong> · modul <strong>FS-09</strong> di jalur <em>Full Stack IoT Developer — Dari Nol</em>. Di <strong>FS-08</strong> kamu sudah paham kenapa LED butuh resistor 220Ω. Hari ini kamu <strong>merakit rangkaian pertama</strong> di breadboard — LED menyala dari pin <strong>3V3</strong> ESP32, murni wiring, <strong>belum upload sketch</strong>.</p>
<p><strong>Awam:</strong> modul ini seperti menyalakan lampu kamar dengan saklar di stop kontak — belum pakai remote (program).</p>
<p><strong>Prasyarat:</strong> FS-08 (pilih resistor) + kenal breadboard dari FS-04 + kebiasaan cabut USB dari FS-05. <strong>Tidak ada Arduino IDE, upload sketch, atau <code>php artisan</code> hari ini</strong> — hanya breadboard, jumper, LED, resistor, dan ESP32 + kabel USB data.</p>

<p><strong>Awam — cara pakai artikel ini (urutan baca):</strong></p>
<ol>
<li><strong>Kumpulkan alat</strong> di meja (daftar di bawah) — belum colok USB.</li>
<li><strong>Baca + lihat gambar</strong> sampai bagian wiring step-by-step.</li>
<li><strong>Rakit di meja</strong> ikuti baris 12 → 15 → 16.</li>
<li><strong>Colok USB</strong> → LED menyala → latihan cabut jumper.</li>
<li><strong>Centang checklist 10/10</strong> di browser (widget interaktif di bawah).</li>
</ol>
<p><strong>Tidak perlu hari ini:</strong> Arduino IDE, upload sketch, Serial Monitor, <code>php artisan</code>, Laragon.</p>

<h2>Persiapan — buka &amp; siapkan ini dulu</h2>
<p><strong>Awam — urutan meja kerja:</strong> jangan langsung colok kabel. Ikuti urutan di bawah supaya tidak bingung.</p>
<ol>
<li><strong>Cabut USB</strong> dari ESP32 (wajib sebelum menyentuh jumper).</li>
<li>Letakkan <strong>breadboard</strong> di meja datar, ESP32 di sampingnya.</li>
<li>Siapkan <strong>3 jumper</strong>: merah (3V3), hitam (GND), satu warna lain (sinyal).</li>
<li>Ambil <strong>LED 5mm</strong> + <strong>resistor 220Ω</strong> (330Ω juga boleh).</li>
<li>Buka pinout board — cari tulisan <strong>3V3</strong> dan <strong>GND</strong>.</li>
</ol>
<p><strong>Alat yang dipakai hari ini:</strong> breadboard, jumper, LED, resistor, ESP32-DevKitC-1, kabel USB data.</p>
<p><strong>Tidak dipakai hari ini:</strong> Arduino IDE, Serial Monitor, multimeter (opsional), Laragon, terminal proyek web.</p>

{$bb}
{$jump}
{$led}
{$res}
{$pin}

<h2>Kenali power rail breadboard</h2>
<p>Power rail = jalur listrik di tepi breadboard. Rail <strong>merah (+)</strong> kita hubungkan ke <strong>3V3</strong>. Rail <strong>biru (-)</strong> ke <strong>GND</strong>.</p>
<p><strong>Awam:</strong> bayangkan rail seperti pipa air utama — jumper ke baris tengah seperti cabang pipa ke kran.</p>
{$rail}

<h2>Rangkaian yang akan dibuat</h2>
<p>Alur listrik: <strong>3V3 → resistor 220Ω → LED → GND</strong>. Resistor melindungi LED; polaritas LED harus benar.</p>
{$circuit}

<h2>Wiring step-by-step</h2>
<p><strong>Awam:</strong> nomor baris (12, 15, 16) boleh digeser — yang penting <strong>urutan</strong> sama. Tulis nomor baris di kertas jika perlu.</p>
<ol>
<li><strong>Power rail:</strong> jumper merah dari pin <strong>3V3</strong> ESP32 ke rail merah (+). Jumper hitam dari <strong>GND</strong> ke rail biru (-).</li>
<li><strong>Baris 12:</strong> jumper dari rail merah (+) ke satu lubang di baris 12. <em>Ini kabel yang nanti dicabut untuk mematikan LED.</em></li>
<li><strong>Resistor:</strong> satu kaki di baris 12, kaki lain di <strong>baris 15</strong>.</li>
<li><strong>LED:</strong> kaki <strong>panjang</strong> di baris 15 (bersama kaki resistor). Kaki <strong>pendek</strong> di <strong>baris 16</strong>.</li>
<li><strong>Baris 16 ke GND:</strong> jumper dari baris 16 ke rail biru (-).</li>
<li><strong>Cek visual:</strong> tidak ada kabel merah menyentuh hitam langsung (short). LED tidak terbalik.</li>
<li><strong>Colok USB</strong> — LED harus menyala lembut/terang.</li>
<li><strong>Latihan matikan:</strong> cabut jumper baris 12 dari rail + — LED padam. Pasang lagi — menyala.</li>
</ol>
{$pol}
{$steps}
{$real}

<h2 id="fsiot-led-circuit-checklist">Praktik — checklist rangkaian LED</h2>
<p>Centang setiap langkah setelah kamu lakukan di meja. Target: <strong>10/10</strong>. Ada checklist interaktif di bawah; versi kertas tetap tersedia.</p>
<ul id="fsiot-led-circuit-checklist-items">
<li>USB sudah dicabut sebelum mulai wiring</li>
<li>Rail merah (+) terhubung ke pin 3V3 ESP32</li>
<li>Rail biru (-) terhubung ke pin GND ESP32</li>
<li>Resistor 220 ohm (atau 330 ohm) terpasang antara baris 12 dan 15</li>
<li>LED kaki panjang di baris 15, pendek di baris 16</li>
<li>Jumper baris 16 menuju rail GND</li>
<li>Tidak ada short antara 3V3 dan GND</li>
<li>Colok USB — LED menyala</li>
<li>Cabut jumper baris 12 — LED mati</li>
<li>Bisa jelaskan alur: 3V3 → R → LED → GND</li>
</ul>
<p><strong>Awam — cara menguji:</strong> kerjakan checklist di browser setelah wiring di meja. Tidak perlu Arduino IDE atau <code>php artisan</code>.</p>

<h2>Kesalahan umum pemula</h2>
<ul>
<li><strong>LED terbalik.</strong> Kaki pendek ke 3V3 = tidak menyala atau rusak. Balik arah LED.</li>
<li><strong>Resistor di kaki salah.</strong> Resistor harus di jalur arus, bukan di kaki GND saja tanpa sambung ke 3V3.</li>
<li><strong>Short power–GND.</strong> Jumper merah dan hitam menyentuh = panas/board reset. Cabut USB, periksa ulang.</li>
<li><strong>LED tanpa resistor.</strong> Jangan pernah langsung 3V3 ke LED — ingat FS-08.</li>
<li><strong>Colok USB saat masih merakit.</strong> Selalu cabut dulu (FS-05).</li>
<li><strong>Mengira semua lubang nyambung.</strong> Hanya dalam satu baris (sisi yang sama) — lihat FS-04.</li>
</ul>

<h2>Selanjutnya</h2>
<p><strong>Awam:</strong> kalau LED sudah menyala dan bisa dimatikan dengan cabut jumper, FS-09 selesai. Lanjut ke <strong>FS-10</strong> (digital vs analog, HIGH/LOW, pull-up) saat modulnya terbit — di sana kita pakai bahasa sinyal untuk GPIO.</p>
<p>Daftar modul lengkap: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $bb = $this->breadboardFigureEn();
        $jump = $this->jumperFigureEn();
        $led = $this->ledFigureEn();
        $res = $this->resistorFigureEn();
        $pin = $this->pinoutFigureEn();
        $rail = $this->powerRailSvgEn();
        $pol = $this->ledPolaritySvgEn();
        $steps = $this->wiringStepsSvgEn();
        $circuit = $this->fullCircuitSvgEn();
        $real = $this->realWiringFigureEn();

        return <<<HTML
<h2>Introduction — an LED without code?</h2>
<p>This article is <strong>#79 (this article)</strong> · module <strong>FS-09</strong> on the <em>Full Stack IoT Developer — From Zero</em> track. In <strong>FS-08</strong> you learned why an LED needs a 220Ω resistor. Today you <strong>build your first circuit</strong> on a breadboard — the LED lights from the ESP32 <strong>3V3</strong> pin, wiring only, <strong>no sketch upload yet</strong>.</p>
<p><strong>Beginner:</strong> this module is like turning on a room light with a wall switch — no remote (program) yet.</p>
<p><strong>Prerequisites:</strong> FS-08 (pick a resistor) + know the breadboard from FS-04 + unplug-USB habit from FS-05. <strong>No Arduino IDE, sketch upload, or <code>php artisan</code> today</strong> — only breadboard, jumpers, LED, resistor, and ESP32 + data USB cable.</p>

<p><strong>Beginner — how to use this article (read in order):</strong></p>
<ol>
<li><strong>Gather tools</strong> on your desk (list below) — do not plug USB yet.</li>
<li><strong>Read + study images</strong> through the step-by-step wiring section.</li>
<li><strong>Build on the desk</strong> following rows 12 → 15 → 16.</li>
<li><strong>Plug USB</strong> → LED lights → practice unplugging the jumper.</li>
<li><strong>Tick the 10/10 checklist</strong> in the browser (interactive widget below).</li>
</ol>
<p><strong>Not needed today:</strong> Arduino IDE, sketch upload, Serial Monitor, <code>php artisan</code>, Laragon.</p>

<h2>Preparation — open &amp; gather these first</h2>
<p><strong>Beginner — desk order:</strong> do not plug wires in randomly. Follow the order below so you do not get lost.</p>
<ol>
<li><strong>Unplug USB</strong> from the ESP32 (required before touching jumpers).</li>
<li>Place the <strong>breadboard</strong> on a flat desk, ESP32 beside it.</li>
<li>Prepare <strong>3 jumpers</strong>: red (3V3), black (GND), one other color.</li>
<li>Grab a <strong>5mm LED</strong> + <strong>220Ω resistor</strong> (330Ω is fine too).</li>
<li>Open the board pinout — find <strong>3V3</strong> and <strong>GND</strong>.</li>
</ol>
<p><strong>Tools used today:</strong> breadboard, jumpers, LED, resistor, ESP32-DevKitC-1, data USB cable.</p>
<p><strong>Not used today:</strong> Arduino IDE, Serial Monitor, multimeter (optional), Laragon, web project terminal.</p>

{$bb}
{$jump}
{$led}
{$res}
{$pin}

<h2>Know the breadboard power rails</h2>
<p>Power rails are the supply strips on the breadboard edges. The <strong>red (+)</strong> rail connects to <strong>3V3</strong>. The <strong>blue (-)</strong> rail to <strong>GND</strong>.</p>
<p><strong>Beginner:</strong> think of rails as main water pipes — jumpers to middle rows are branches to taps.</p>
{$rail}

<h2>The circuit we will build</h2>
<p>Current path: <strong>3V3 → 220Ω resistor → LED → GND</strong>. The resistor protects the LED; LED polarity must be correct.</p>
{$circuit}

<h2>Step-by-step wiring</h2>
<p><strong>Beginner:</strong> row numbers (12, 15, 16) can shift — keep the <strong>order</strong> the same. Write row numbers on paper if needed.</p>
<ol>
<li><strong>Power rails:</strong> red jumper from ESP32 <strong>3V3</strong> to red (+) rail. Black jumper from <strong>GND</strong> to blue (-) rail.</li>
<li><strong>Row 12:</strong> jumper from red (+) rail to one hole in row 12. <em>This wire gets unplugged to turn the LED off.</em></li>
<li><strong>Resistor:</strong> one leg in row 12, the other in <strong>row 15</strong>.</li>
<li><strong>LED:</strong> <strong>long</strong> leg in row 15 (with the resistor). <strong>Short</strong> leg in <strong>row 16</strong>.</li>
<li><strong>Row 16 to GND:</strong> jumper from row 16 to blue (-) rail.</li>
<li><strong>Visual check:</strong> no red wire touching black directly (short). LED not reversed.</li>
<li><strong>Plug USB</strong> — the LED should glow.</li>
<li><strong>Turn-off practice:</strong> unplug the row-12 jumper from the + rail — LED off. Plug back — on.</li>
</ol>
{$pol}
{$steps}
{$real}

<h2 id="fsiot-led-circuit-checklist">Practice — LED circuit checklist</h2>
<p>Tick each step after you do it on the desk. Target: <strong>10/10</strong>. An interactive checklist is below; a paper version stays available.</p>
<ul id="fsiot-led-circuit-checklist-items">
<li>USB unplugged before wiring</li>
<li>Red (+) rail connected to ESP32 3V3 pin</li>
<li>Blue (-) rail connected to ESP32 GND pin</li>
<li>220 ohm (or 330 ohm) resistor between rows 12 and 15</li>
<li>LED long leg in row 15, short leg in row 16</li>
<li>Jumper from row 16 to GND rail</li>
<li>No short between 3V3 and GND</li>
<li>Plug USB — LED lights</li>
<li>Unplug row-12 jumper — LED off</li>
<li>Can explain the path: 3V3 → R → LED → GND</li>
</ul>
<p><strong>Beginner — how to test:</strong> complete the checklist in the browser after wiring on the desk. No Arduino IDE or <code>php artisan</code> required.</p>

<h2>Common beginner mistakes</h2>
<ul>
<li><strong>Reversed LED.</strong> Short leg toward 3V3 = no light or damage. Flip the LED.</li>
<li><strong>Resistor on the wrong leg.</strong> The resistor must be in the current path, not only on GND without reaching 3V3.</li>
<li><strong>Power–GND short.</strong> Red and black touching = heat/board reset. Unplug USB and recheck.</li>
<li><strong>LED without resistor.</strong> Never wire 3V3 straight to an LED — remember FS-08.</li>
<li><strong>USB plugged while building.</strong> Always unplug first (FS-05).</li>
<li><strong>Assuming every hole connects.</strong> Only within one row (same side) — see FS-04.</li>
</ul>

<h2>Next steps</h2>
<p><strong>Beginner:</strong> if the LED lights and you can turn it off by unplugging a jumper, FS-09 is done. Continue to <strong>FS-10</strong> (digital vs analog, HIGH/LOW, pull-up) when that module publishes — there we use signal language for GPIO.</p>
<p>Full module list: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }
}
