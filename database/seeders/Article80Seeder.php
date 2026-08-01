<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article80Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first()
            ?? Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $iotCat) {
            throw new \RuntimeException('User atau kategori iot-smart-device/esp32-arduino tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-digital-analog-high-low-pull-resistor';

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
                'title'              => 'Digital vs analog: HIGH/LOW & resistor pull-down',
                'title_en'           => 'Digital vs analog: HIGH/LOW & pull-down resistors',
                'excerpt'            => 'FS-10 / #80: Paham sinyal on/off (HIGH/LOW), pin mengambang, dan rakit tombol + resistor pull-down 10 kΩ di breadboard. Uji dengan multimeter — belum coding.',
                'excerpt_en'         => 'FS-10 / #80: Understand on/off signals (HIGH/LOW), floating pins, and build a button + 10 kΩ pull-down on a breadboard. Test with a multimeter — no code yet.',
                'body'               => $this->body(),
                'body_en'            => $this->bodyEn(),
                'status'             => 'draft',
                'is_featured'        => false,
                'published_at'       => null,
                'seo_title'          => 'Digital vs Analog — HIGH/LOW & Pull-down — Full Stack IoT #80',
                'seo_title_en'       => 'Digital vs Analog — HIGH/LOW & Pull-down — Full Stack IoT #80',
                'seo_description'    => 'Paham sinyal digital HIGH/LOW 3.3V ESP32, pin mengambang, dan wiring tombol + resistor pull-down 10 kΩ. Uji tegangan dengan multimeter. Modul FS-10 tanpa upload sketch.',
                'seo_description_en' => 'Understand ESP32 3.3V HIGH/LOW digital signals, floating pins, and button + 10 kΩ pull-down wiring. Test voltage with a multimeter. FS-10 module, no sketch upload.',
            ]
        );

        if ($article->published_at !== null || $article->status !== 'draft') {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['fullstack-iot', 'iot', 'esp32'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel #80 / FS-10 tersimpan sebagai DRAFT: '.$article->title);
    }

    private function buttonFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/kit-tactile-button.jpg" width="1200" height="900" alt="Tombol tactile 4 kaki — pasang melintasi parit tengah breadboard" loading="eager" style="width:100%;height:auto;max-height:360px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Tombol tactile 4 kaki</strong> dari kit — pasang <strong>melintasi parit tengah</strong> breadboard. Kaki di satu sisi saling nyambung; tekan tombol = kedua sisi menyatu.
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:BUTA-06-X-STAN-01.jpg" rel="noopener noreferrer" target="_blank">Wikimedia Commons — tactile pushbutton (oomlout)</a> · CC BY-SA 2.0.
  </figcaption>
</figure>
HTML;
    }

    private function buttonFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/kit-tactile-button.jpg" width="1200" height="900" alt="4-pin tactile button — mount across the breadboard center ditch" loading="eager" style="width:100%;height:auto;max-height:360px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>4-pin tactile button</strong> from the kit — mount it <strong>across the center ditch</strong> of the breadboard. Legs on one side are connected; pressing joins both sides.
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:BUTA-06-X-STAN-01.jpg" rel="noopener noreferrer" target="_blank">Wikimedia Commons — tactile pushbutton (oomlout)</a> · CC BY-SA 2.0.
  </figcaption>
</figure>
HTML;
    }

    private function breadboardFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/kit-breadboard.jpg" width="1200" height="900" alt="Breadboard dengan power rail merah dan biru" loading="lazy" style="width:100%;height:auto;max-height:300px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Breadboard</strong> — rail <strong>merah (+)</strong> ke pin <strong>3V3</strong>, rail <strong>biru (−)</strong> ke <strong>GND</strong> (ulang dari FS-09).
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:400_points_breadboard.jpg" rel="noopener noreferrer" target="_blank">oomlout — breadboard</a> · Wikimedia Commons.
  </figcaption>
</figure>
HTML;
    }

    private function breadboardFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/kit-breadboard.jpg" width="1200" height="900" alt="Breadboard with red and blue power rails" loading="lazy" style="width:100%;height:auto;max-height:300px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Breadboard</strong> — <strong>red (+)</strong> rail to <strong>3V3</strong>, <strong>blue (−)</strong> rail to <strong>GND</strong> (repeat from FS-09).
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:400_points_breadboard.jpg" rel="noopener noreferrer" target="_blank">oomlout — breadboard</a> · Wikimedia Commons.
  </figcaption>
</figure>
HTML;
    }

    private function resistor10kFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/kit-resistor-10kohm.jpg" width="1100" height="520" alt="Resistor 10 kΩ — cincin cokelat hitam oranye di atas kertas milimeter" loading="lazy" style="width:100%;height:auto;max-height:260px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Cari resistor 10 kΩ</strong> di kit — cincin warna umum: <strong>cokelat – hitam – oranye</strong> (+ emas/perak = toleransi). Jangan pakai 220 Ω (FS-09) untuk pull-down.
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:Ceramic_Composition_Resistor_10k.png" rel="noopener noreferrer" target="_blank">Wikimedia Commons — Ceramic Composition Resistor 10k</a> · CC BY-SA 4.0.
  </figcaption>
</figure>
HTML;
    }

    private function resistor10kFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/kit-resistor-10kohm.jpg" width="1100" height="520" alt="10 kΩ resistor — brown black orange bands on graph paper" loading="lazy" style="width:100%;height:auto;max-height:260px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Find a 10 kΩ resistor</strong> in the kit — common bands: <strong>brown – black – orange</strong> (+ gold/silver = tolerance). Do not use 220 Ω (FS-09) for pull-down.
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:Ceramic_Composition_Resistor_10k.png" rel="noopener noreferrer" target="_blank">Wikimedia Commons — Ceramic Composition Resistor 10k</a> · CC BY-SA 4.0.
  </figcaption>
</figure>
HTML;
    }

    private function buttonLegsSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Empat kaki tombol tactile — pasangan kaki" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 520 220" width="100%" height="auto">
  <text x="260" y="24" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">4 kaki tombol — yang nyambung &amp; yang tidak</text>
  <rect x="200" y="70" width="120" height="70" rx="8" fill="#E0E0E0" stroke="#1a1a1a" stroke-width="2"/>
  <text x="260" y="110" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">TEKAN</text>
  <circle cx="170" cy="90" r="8" fill="#1565C0" stroke="#1a1a1a"/><circle cx="170" cy="120" r="8" fill="#1565C0" stroke="#1a1a1a"/>
  <line x1="170" y1="90" x2="170" y2="120" stroke="#1565C0" stroke-width="2"/>
  <text x="130" y="95" font-family="system-ui,sans-serif" font-size="10">sisi A</text>
  <circle cx="350" cy="90" r="8" fill="#E53935" stroke="#1a1a1a"/><circle cx="350" cy="120" r="8" fill="#E53935" stroke="#1a1a1a"/>
  <line x1="350" y1="90" x2="350" y2="120" stroke="#E53935" stroke-width="2" stroke-dasharray="4 3"/>
  <text x="380" y="95" font-family="system-ui,sans-serif" font-size="10">sisi B</text>
  <text x="260" y="165" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">Kaki dalam satu sisi selalu nyambung · Tekan = sisi A menyatu dengan B</text>
  <text x="260" y="185" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">Pasang melintasi parit — sisi A di atas, sisi B di bawah · Buatan Koding Indonesia</text>
</svg>
</figure>
SVG;
    }

    private function buttonLegsSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Four tactile button legs — leg pairs" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 520 220" width="100%" height="auto">
  <text x="260" y="24" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">4 button legs — what connects</text>
  <rect x="200" y="70" width="120" height="70" rx="8" fill="#E0E0E0" stroke="#1a1a1a" stroke-width="2"/>
  <text x="260" y="110" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">PRESS</text>
  <circle cx="170" cy="90" r="8" fill="#1565C0" stroke="#1a1a1a"/><circle cx="170" cy="120" r="8" fill="#1565C0" stroke="#1a1a1a"/>
  <line x1="170" y1="90" x2="170" y2="120" stroke="#1565C0" stroke-width="2"/>
  <text x="130" y="95" font-family="system-ui,sans-serif" font-size="10">side A</text>
  <circle cx="350" cy="90" r="8" fill="#E53935" stroke="#1a1a1a"/><circle cx="350" cy="120" r="8" fill="#E53935" stroke="#1a1a1a"/>
  <line x1="350" y1="90" x2="350" y2="120" stroke="#E53935" stroke-width="2" stroke-dasharray="4 3"/>
  <text x="380" y="95" font-family="system-ui,sans-serif" font-size="10">side B</text>
  <text x="260" y="165" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">Legs on one side are always connected · Press = side A joins side B</text>
  <text x="260" y="185" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">Mount across the ditch — side A above, side B below · by Koding Indonesia</text>
</svg>
</figure>
SVG;
    }

    private function jumperFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/kit-jumper-wires.jpg" width="1200" height="900" alt="Kabel jumper untuk breadboard — merah 3V3, hitam GND" loading="lazy" style="width:100%;height:auto;max-height:280px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Kabel jumper</strong> — ikuti gambar utama: merah (<strong>3V3 → rail + → tombol</strong>) dan hitam (<strong>GND → rail −</strong> serta <strong>kaki resistor → rail −</strong>). <strong>Belum ada jumper ke pin GPIO</strong> — titik sinyal diukur dengan multimeter.
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:Jumper_wires.jpg" rel="noopener noreferrer" target="_blank">Wikimedia Commons — jumper wires</a>.
  </figcaption>
</figure>
HTML;
    }

    private function jumperFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/kit-jumper-wires.jpg" width="1200" height="900" alt="Jumper wires for breadboard — red 3V3, black GND" loading="lazy" style="width:100%;height:auto;max-height:280px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Jumper wires</strong> — follow the main diagram: red (<strong>3V3 → + rail → button</strong>) and black (<strong>GND → − rail</strong> plus <strong>resistor leg → − rail</strong>). <strong>No jumper to any GPIO pin yet</strong> — measure the signal node with a multimeter.
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:Jumper_wires.jpg" rel="noopener noreferrer" target="_blank">Wikimedia Commons — jumper wires</a>.
  </figcaption>
</figure>
HTML;
    }

    private function pinoutFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/esp32-devkitc-1-pinlayout.jpg" width="1200" height="800" alt="Pinout ESP32-DevKitC-1 — cari label 3V3 dan GND" loading="lazy" style="width:100%;height:auto;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Cari 2 pin ini lagi:</strong> <strong>3V3</strong> (jumper merah ke rail +) dan <strong>GND</strong> (jumper hitam ke rail −). GPIO belum dipakai hari ini.
    <br>Sumber gambar: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/boards/ESP32-DevKitC-1.html" rel="noopener noreferrer" target="_blank">Espressif — ESP32-DevKitC-1</a> (dokumen resmi).
  </figcaption>
</figure>
HTML;
    }

    private function pinoutFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/esp32-devkitc-1-pinlayout.jpg" width="1200" height="800" alt="ESP32-DevKitC-1 pinout — find 3V3 and GND labels" loading="lazy" style="width:100%;height:auto;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Find these 2 pins again:</strong> <strong>3V3</strong> (red jumper to + rail) and <strong>GND</strong> (black jumper to − rail). No GPIO wiring today.
    <br>Image source: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/boards/ESP32-DevKitC-1.html" rel="noopener noreferrer" target="_blank">Espressif — ESP32-DevKitC-1</a> (official docs).
  </figcaption>
</figure>
HTML;
    }

    private function multimeterFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/kit-multimeter.jpg" width="1200" height="900" alt="Multimeter digital — putar dial ke mode V DC sebelum mengukur titik sinyal" loading="lazy" style="width:100%;height:auto;max-height:320px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Sebelum mengukur:</strong> dial ke <strong>V DC</strong> (bukan A/continuity) · probe hitam di <strong>COM</strong>, merah di <strong>VΩmA</strong> — sama seperti FS-07.
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:2017_Cyfrowy_miernik_uniwersalny.jpg" rel="noopener noreferrer" target="_blank">Jacek Halicki — digital multimeter</a> · Wikimedia Commons (CC BY-SA 4.0).
  </figcaption>
</figure>
HTML;
    }

    private function multimeterFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/kit-multimeter.jpg" width="1200" height="900" alt="Digital multimeter — set dial to V DC before measuring the signal node" loading="lazy" style="width:100%;height:auto;max-height:320px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Before measuring:</strong> dial to <strong>V DC</strong> (not A/continuity) · black probe on <strong>COM</strong>, red on <strong>VΩmA</strong> — same as FS-07.
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:2017_Cyfrowy_miernik_uniwersalny.jpg" rel="noopener noreferrer" target="_blank">Jacek Halicki — digital multimeter</a> · Wikimedia Commons (CC BY-SA 4.0).
  </figcaption>
</figure>
HTML;
    }

    private function workflowSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Alur kerja FS-10" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 760 120" width="100%" height="auto">
  <text x="380" y="22" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">Alur hari ini — FS-10</text>
  <rect x="20" y="40" width="110" height="50" rx="6" fill="#FFECB3" stroke="#1a1a1a" stroke-width="2"/><text x="75" y="70" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11">① Cabut USB</text>
  <rect x="150" y="40" width="110" height="50" rx="6" fill="#E3F2FD" stroke="#1a1a1a" stroke-width="2"/><text x="205" y="70" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11">② Rakit wiring</text>
  <rect x="280" y="40" width="110" height="50" rx="6" fill="#C8E6C9" stroke="#1a1a1a" stroke-width="2"/><text x="335" y="70" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11">③ Colok USB</text>
  <rect x="410" y="40" width="130" height="50" rx="6" fill="#F3E5F5" stroke="#1a1a1a" stroke-width="2"/><text x="475" y="70" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11">④ Multimeter V DC</text>
  <rect x="560" y="40" width="110" height="50" rx="6" fill="#FFF9C4" stroke="#1a1a1a" stroke-width="2"/><text x="615" y="70" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11">⑤ Checklist</text>
  <text x="380" y="108" text-anchor="middle" font-family="system-ui,sans-serif" font-size="10" fill="#4A5568">Belum Arduino IDE · Buatan Koding Indonesia</text>
</svg>
</figure>
SVG;
    }

    private function workflowSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="FS-10 workflow" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 760 120" width="100%" height="auto">
  <text x="380" y="22" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">Today's flow — FS-10</text>
  <rect x="20" y="40" width="110" height="50" rx="6" fill="#FFECB3" stroke="#1a1a1a" stroke-width="2"/><text x="75" y="70" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11">① Unplug USB</text>
  <rect x="150" y="40" width="110" height="50" rx="6" fill="#E3F2FD" stroke="#1a1a1a" stroke-width="2"/><text x="205" y="70" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11">② Build wiring</text>
  <rect x="280" y="40" width="110" height="50" rx="6" fill="#C8E6C9" stroke="#1a1a1a" stroke-width="2"/><text x="335" y="70" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11">③ Plug USB</text>
  <rect x="410" y="40" width="130" height="50" rx="6" fill="#F3E5F5" stroke="#1a1a1a" stroke-width="2"/><text x="475" y="70" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11">④ Multimeter V DC</text>
  <rect x="560" y="40" width="110" height="50" rx="6" fill="#FFF9C4" stroke="#1a1a1a" stroke-width="2"/><text x="615" y="70" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11">⑤ Checklist</text>
  <text x="380" y="108" text-anchor="middle" font-family="system-ui,sans-serif" font-size="10" fill="#4A5568">No Arduino IDE yet · by Koding Indonesia</text>
</svg>
</figure>
SVG;
    }

    private function pullReferenceFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/fs10-pullup-pulldown.svg" width="920" height="420" alt="Diagram pull-up di kiri dan pull-down di kanan — fokus kotak hijau hari ini" loading="lazy" style="width:100%;height:auto;max-height:440px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Kiri = pull-up</strong> (default HIGH, preview FS-11). <strong>Kanan = pull-down</strong> (default LOW) — <strong>kotak hijau = yang kita rakit hari ini</strong> dengan resistor <strong>10 kΩ</strong>.
    <br>Sumber gambar: diagram buatan Koding Indonesia (FS-10).
  </figcaption>
</figure>
HTML;
    }

    private function pullReferenceFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/fs10-pullup-pulldown.svg" width="920" height="420" alt="Side-by-side pull-up (left) and pull-down (right) — green box is today’s focus" loading="lazy" style="width:100%;height:auto;max-height:440px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Left = pull-up</strong> (default HIGH, FS-11 preview). <strong>Right = pull-down</strong> (default LOW) — <strong>green box = what we build today</strong> with a <strong>10 kΩ</strong> resistor.
    <br>Image source: diagram by Koding Indonesia (FS-10).
  </figcaption>
</figure>
HTML;
    }

    private function digitalAnalogSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Analogi digital vs analog: saklar vs dimmer" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 200" width="100%" height="auto">
  <text x="360" y="24" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700">Digital vs analog — analogi sederhana</text>
  <rect x="40" y="50" width="280" height="120" rx="8" fill="#E8F5E9" stroke="#1a1a1a" stroke-width="2"/>
  <text x="180" y="80" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">Digital</text>
  <text x="180" y="105" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13">ON / OFF saja</text>
  <text x="180" y="128" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">seperti saklar lampu</text>
  <circle cx="120" cy="145" r="14" fill="#4CAF50" stroke="#1a1a1a"/><circle cx="240" cy="145" r="14" fill="#E0E0E0" stroke="#1a1a1a"/>
  <rect x="400" y="50" width="280" height="120" rx="8" fill="#FFF3E0" stroke="#1a1a1a" stroke-width="2"/>
  <text x="540" y="80" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">Analog</text>
  <text x="540" y="105" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13">banyak nilai di antaranya</text>
  <text x="540" y="128" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">seperti dimmer / volume</text>
  <rect x="470" y="138" width="140" height="12" rx="6" fill="#FFE0B2" stroke="#1a1a1a"/>
  <circle cx="520" cy="144" r="10" fill="#FF9800" stroke="#1a1a1a"/>
  <text x="360" y="190" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">ESP32 GPIO digital = on/off (3.3V logic) · Buatan Koding Indonesia</text>
</svg>
</figure>
SVG;
    }

    private function digitalAnalogSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Digital vs analog analogy: switch vs dimmer" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 200" width="100%" height="auto">
  <text x="360" y="24" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700">Digital vs analog — simple analogy</text>
  <rect x="40" y="50" width="280" height="120" rx="8" fill="#E8F5E9" stroke="#1a1a1a" stroke-width="2"/>
  <text x="180" y="80" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">Digital</text>
  <text x="180" y="105" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13">ON / OFF only</text>
  <text x="180" y="128" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">like a light switch</text>
  <circle cx="120" cy="145" r="14" fill="#4CAF50" stroke="#1a1a1a"/><circle cx="240" cy="145" r="14" fill="#E0E0E0" stroke="#1a1a1a"/>
  <rect x="400" y="50" width="280" height="120" rx="8" fill="#FFF3E0" stroke="#1a1a1a" stroke-width="2"/>
  <text x="540" y="80" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">Analog</text>
  <text x="540" y="105" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13">many values in between</text>
  <text x="540" y="128" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">like a dimmer / volume knob</text>
  <rect x="470" y="138" width="140" height="12" rx="6" fill="#FFE0B2" stroke="#1a1a1a"/>
  <circle cx="520" cy="144" r="10" fill="#FF9800" stroke="#1a1a1a"/>
  <text x="360" y="190" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">ESP32 digital GPIO = on/off (3.3V logic) · by Koding Indonesia</text>
</svg>
</figure>
SVG;
    }

    private function highLowSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="HIGH dan LOW di ESP32: 3.3V dan GND" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 180" width="100%" height="auto">
  <text x="320" y="26" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700">HIGH / LOW di ESP32 (3.3V logic)</text>
  <rect x="60" y="55" width="200" height="80" rx="8" fill="#C8E6C9" stroke="#1a1a1a" stroke-width="2"/>
  <text x="160" y="88" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700">HIGH</text>
  <text x="160" y="115" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13">~3,3 V</text>
  <rect x="380" y="55" width="200" height="80" rx="8" fill="#ECEFF1" stroke="#1a1a1a" stroke-width="2"/>
  <text x="480" y="88" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700">LOW</text>
  <text x="480" y="115" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13">0 V (GND)</text>
  <text x="320" y="160" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">Jangan pakai 5V ke GPIO — ESP32 = 3,3V logic · Buatan Koding Indonesia</text>
</svg>
</figure>
SVG;
    }

    private function highLowSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="HIGH and LOW on ESP32: 3.3V and GND" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 180" width="100%" height="auto">
  <text x="320" y="26" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700">HIGH / LOW on ESP32 (3.3V logic)</text>
  <rect x="60" y="55" width="200" height="80" rx="8" fill="#C8E6C9" stroke="#1a1a1a" stroke-width="2"/>
  <text x="160" y="88" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700">HIGH</text>
  <text x="160" y="115" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13">~3.3 V</text>
  <rect x="380" y="55" width="200" height="80" rx="8" fill="#ECEFF1" stroke="#1a1a1a" stroke-width="2"/>
  <text x="480" y="88" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700">LOW</text>
  <text x="480" y="115" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13">0 V (GND)</text>
  <text x="320" y="160" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">Do not feed 5V into GPIO — ESP32 = 3.3V logic · by Koding Indonesia</text>
</svg>
</figure>
SVG;
    }

    private function floatingSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Pin mengambang tanpa pull resistor" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 200" width="100%" height="auto">
  <text x="320" y="26" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700">Pin mengambang = sinyal &quot;hantu&quot;</text>
  <circle cx="320" cy="100" r="28" fill="#FFECB3" stroke="#1a1a1a" stroke-width="2"/>
  <text x="320" y="96" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">GPIO?</text>
  <text x="320" y="112" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11">???</text>
  <path d="M120 100 Q200 40 280 90" fill="none" stroke="#9E9E9E" stroke-width="2" stroke-dasharray="6 4"/>
  <path d="M360 110 Q440 160 520 100" fill="none" stroke="#9E9E9E" stroke-width="2" stroke-dasharray="6 4"/>
  <text x="100" y="130" font-family="system-ui,sans-serif" font-size="11" fill="#E53935">gangguan</text>
  <text x="500" y="130" font-family="system-ui,sans-serif" font-size="11" fill="#E53935">acak</text>
  <text x="320" y="175" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">Tanpa pull-up/pull-down, pembacaan bisa loncat sendiri · Buatan Koding Indonesia</text>
</svg>
</figure>
SVG;
    }

    private function floatingSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Floating pin without pull resistor" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 200" width="100%" height="auto">
  <text x="320" y="26" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700">Floating pin = &quot;ghost&quot; signal</text>
  <circle cx="320" cy="100" r="28" fill="#FFECB3" stroke="#1a1a1a" stroke-width="2"/>
  <text x="320" y="96" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">GPIO?</text>
  <text x="320" y="112" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11">???</text>
  <path d="M120 100 Q200 40 280 90" fill="none" stroke="#9E9E9E" stroke-width="2" stroke-dasharray="6 4"/>
  <path d="M360 110 Q440 160 520 100" fill="none" stroke="#9E9E9E" stroke-width="2" stroke-dasharray="6 4"/>
  <text x="100" y="130" font-family="system-ui,sans-serif" font-size="11" fill="#E53935">noise</text>
  <text x="500" y="130" font-family="system-ui,sans-serif" font-size="11" fill="#E53935">random</text>
  <text x="320" y="175" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">Without pull-up/pull-down, readings can jump on their own · by Koding Indonesia</text>
</svg>
</figure>
SVG;
    }

    private function wiringSvgId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/fs10-button-pulldown-wiring.svg" width="920" height="520" alt="Skema berlabel: tombol + pull-down 10 kΩ, node sinyal S diukur multimeter V DC — belum ke GPIO" loading="eager" style="width:100%;height:auto;max-height:560px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Gambar utama</strong> — skema berlabel: sumber <strong>3V3</strong> &amp; <strong>GND</strong> dari ESP32 + tombol + <strong>pull-down 10 kΩ</strong>. Alur: jumper merah <strong>3V3 → rail + → kaki A tombol</strong>; kaki B = <strong>node sinyal (S)</strong>; resistor 10 kΩ dari S ke <strong>rail − / GND</strong>. Nomor kolom breadboard boleh digeser asalkan urutan listrik sama — <strong>belum ada kabel ke pin GPIO</strong>; hari ini cukup ukur tegangan di S dengan multimeter, belum coding.
    <br>Sumber gambar: diagram berlabel buatan Koding Indonesia (FS-10).
  </figcaption>
</figure>
HTML;
    }

    private function wiringSvgEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/fs10-button-pulldown-wiring.svg" width="920" height="520" alt="Labeled schematic: button + 10 kΩ pull-down, signal node S measured with V DC multimeter — no GPIO yet" loading="eager" style="width:100%;height:auto;max-height:560px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Main diagram</strong> — labeled schematic: <strong>3V3</strong> &amp; <strong>GND</strong> from the ESP32 + button + <strong>10 kΩ pull-down</strong>. Path: red jumper <strong>3V3 → + rail → button leg A</strong>; leg B = <strong>signal node (S)</strong>; 10 kΩ from S to the <strong>− rail / GND</strong>. Breadboard column numbers may shift if the electrical order stays the same — <strong>no wire to any GPIO pin yet</strong>; today only measure voltage at S with a multimeter, no coding.
    <br>Image source: labeled diagram by Koding Indonesia (FS-10).
  </figcaption>
</figure>
HTML;
    }

    private function body(): string
    {
        $flow = $this->workflowSvgId();
        $bb = $this->breadboardFigureId();
        $pin = $this->pinoutFigureId();
        $jump = $this->jumperFigureId();
        $btn = $this->buttonFigureId();
        $legs = $this->buttonLegsSvgId();
        $r10k = $this->resistor10kFigureId();
        $meter = $this->multimeterFigureId();
        $pull = $this->pullReferenceFigureId();
        $dig = $this->digitalAnalogSvgId();
        $hl = $this->highLowSvgId();
        $float = $this->floatingSvgId();
        $wire = $this->wiringSvgId();

        return <<<HTML
<h2>Pendahuluan — bahasa sinyal sebelum coding</h2>
<p>Artikel ini adalah <strong>#80 (ini)</strong> · modul <strong>FS-10</strong> di jalur <em>Full Stack IoT Developer — Dari Nol</em>. Di <strong>FS-09</strong> LED sudah menyala dari wiring. Hari ini kamu belajar <strong>bahasa on/off</strong> yang dipakai pin <strong>GPIO</strong> (<em>General Purpose Input/Output</em> — pin masukan/keluaran umum) ESP32: <strong>HIGH</strong> dan <strong>LOW</strong>, lalu merakit <strong>tombol + resistor pull-down 10 kΩ</strong> di breadboard.</p>
<p><strong>Analogi:</strong> GPIO seperti telinga board — harus tahu apakah yang didengar “ya” (HIGH) atau “tidak” (LOW). Kalau tidak diatur, telinga itu mendengar angin (pin mengambang).</p>
<p><strong>Prasyarat:</strong> FS-09 (breadboard + 3V3/GND) + FS-07 (multimeter mode V DC) + kebiasaan <strong>cabut USB dulu</strong> dari FS-05. <strong>Belum upload sketch atau Arduino IDE hari ini</strong> — kita uji tegangan dengan multimeter.</p>

<p><strong>Cara pakai artikel ini (urutan kerja):</strong></p>
<ol>
<li><strong>Kumpulkan alat</strong> di meja — USB belum dicolok.</li>
<li><strong>Baca konsep</strong> digital/analog, HIGH/LOW, pin mengambang (gambar di bawah).</li>
<li><strong>Rakit wiring</strong> ikuti gambar utama.</li>
<li><strong>Buka multimeter</strong> (mode V DC) → ukur titik sinyal lepas vs tekan.</li>
<li><strong>Catat angka</strong> di kertas → centang checklist 10/10 di browser.</li>
</ol>
<p><strong>Tidak perlu hari ini:</strong> Arduino IDE, <code>pinMode</code>, <code>digitalRead</code>, Serial Monitor, Laragon, <code>php artisan</code>. Hari ini tools-nya: <strong>browser</strong> (artikel + checklist) + kit di meja + <strong>multimeter</strong> (mode V DC).</p>
{$flow}

<h2>Persiapan — buka &amp; siapkan ini dulu</h2>
<p><strong>Urutan meja kerja:</strong> jangan langsung colok USB. Ikuti urutan di bawah supaya tidak bingung.</p>
<ol>
<li><strong>Cabut USB</strong> dari ESP32 (wajib sebelum menyentuh jumper).</li>
<li>Letakkan <strong>breadboard</strong> + <strong>ESP32-DevKitC-1</strong> di meja.</li>
<li>Siapkan <strong>tombol tactile</strong>, <strong>resistor 10 kΩ</strong> (bukan 220 Ω), jumper <strong>merah &amp; hitam</strong> sesuai gambar utama (power + tombol + kaki resistor ke GND — <strong>belum jumper ke GPIO</strong>).</li>
<li><strong>Kertas + pena</strong> — untuk mencatat tegangan lepas vs tekan.</li>
<li><strong>Buka multimeter</strong> — putar dial ke <strong>V DC</strong> (sama seperti FS-07). Probe hitam ke COM, merah ke VΩmA.</li>
<li>Colokkan jumper <strong>3V3</strong> dan <strong>GND</strong> ke power rail breadboard (ulang dari FS-09).</li>
</ol>
<p><strong>Alat yang dipakai hari ini:</strong> breadboard, jumper, tombol tactile, resistor 10 kΩ, ESP32 + kabel USB data, multimeter, kertas + pena.</p>
<p><strong>Tidak dipakai hari ini:</strong> Arduino IDE, laptop untuk upload, Laragon, terminal web.</p>

{$bb}
{$pin}
{$jump}
{$r10k}
{$btn}
{$legs}

<h2>Digital vs analog — bedanya apa?</h2>
<p><strong>Digital</strong> = dua keadaan jelas: nyala atau mati. <strong>Analog</strong> = nilai bisa di antaranya (misalnya suhu 27,3 °C). Pin GPIO digital ESP32 hanya membaca <strong>HIGH</strong> atau <strong>LOW</strong>.</p>
<p><strong>Intinya:</strong> digital bukan “lebih canggih” — hanya lebih sederhana untuk komputer (ya/tidak).</p>
{$dig}

<h2>HIGH dan LOW di ESP32</h2>
<p>ESP32 memakai <strong>3,3 V logic</strong>:</p>
<ul>
<li><strong>HIGH</strong> ≈ 3,3 V (tegangan mendekati pin 3V3)</li>
<li><strong>LOW</strong> ≈ 0 V (sama potensial dengan GND)</li>
</ul>
<p><strong>Tips:</strong> jangan sambungkan 5 V langsung ke GPIO — itu di luar aturan 3,3 V.</p>
{$hl}

<h2>Pin mengambang &amp; resistor pull-down</h2>
<p>Kalau pin input tidak disambung ke 3V3 maupun GND, ia <strong>mengambang</strong> — pembacaan bisa loncat-loncat (tombol hantu).</p>
<p><strong>Resistor pull-down</strong> menarik titik sinyal ke GND lembut lewat resistor 10 kΩ. Saat tombol ditekan, 3V3 “menang” dan sinyal jadi HIGH.</p>
<p><strong>Intinya — pull-up vs pull-down:</strong> pull-down = default LOW (lepas = 0 V). Pull-up kebalikannya (default HIGH) — ESP32 punya pull-up internal yang akan kita pakai di FS-11; hari ini kita latih pull-down eksternal dulu supaya wiring terlihat jelas.</p>
{$float}
{$pull}

<h2>Rangkaian yang akan dibuat</h2>
<p>Alur saat <strong>tombol lepas</strong>: titik sinyal terhubung ke GND lewat 10 kΩ → LOW. Saat <strong>tombol ditekan</strong>: 3V3 menyambung ke titik sinyal → HIGH. Di skema, ESP32 hanya menyuplai <strong>3V3</strong> dan <strong>GND</strong> lewat rail — <strong>belum ada jumper ke pin GPIO</strong>. Titik sinyal (S) = kolom kaki B tombol (sisi resistor); nanti disambung ke GPIO di FS-11. Hari ini uji dulu dengan multimeter.</p>
{$wire}

<h2>Wiring langkah demi langkah</h2>
<p><strong>Tips:</strong> ikuti <strong>gambar utama</strong> di atas (skema berlabel). Nomor kolom boleh digeser — yang penting <strong>urutan</strong> listrik sama: <strong>3V3 → tombol → titik sinyal (S) → 10 kΩ → GND</strong>.</p>
<ol>
<li><strong>Pasang ESP32</strong> di breadboard melintasi parit (antena kiri, USB kanan) — hanya untuk menyuplai <strong>3V3</strong> dan <strong>GND</strong>.</li>
<li><strong>Power rail:</strong> jumper merah dari pin <strong>3V3</strong> ke rail merah (+). Jumper hitam dari pin <strong>GND</strong> ke rail biru (−).</li>
<li><strong>Tombol:</strong> pasang di <strong>kiri</strong> ESP32, melintasi <strong>parit tengah</strong> (satu sisi di atas parit = kaki A, satu sisi di bawah = kaki B).</li>
<li><strong>Ke 3V3:</strong> jumper merah dari rail (+) ke <strong>kolom kaki A tombol</strong> (sisi atas parit).</li>
<li><strong>Titik sinyal (S):</strong> kolom <strong>kaki B</strong> di sisi bawah parit — di sini kaki kiri resistor dan probe merah nanti. <strong>Jangan sambung ke GPIO dulu.</strong></li>
<li><strong>Resistor 10 kΩ:</strong> satu kaki di kolom S, kaki lain beberapa kolom ke kanan (cincin cokelat-hitam-oranye).</li>
<li><strong>Ke GND:</strong> jumper hitam dari kaki kanan resistor ke rail biru (−).</li>
<li><strong>Cek visual:</strong> tidak ada short langsung 3V3–GND; tombol benar melintasi parit; tidak ada kabel ke pin IO.</li>
<li><strong>Colok USB</strong> — board menyalakan rail (jangan sentuh bagian logam probe).</li>
</ol>

<h2>Uji dengan multimeter (mode V DC)</h2>
<p><strong>Buka alat ini dulu:</strong> ambil multimeter dari FS-07, pastikan dial sudah di mode <strong>V DC</strong>, baru sentuh titik sinyal (S). Ini bukan perintah di terminal — alatnya di meja.</p>
{$meter}
<p><strong>Langkah ukur:</strong></p>
<ol>
<li>Probe <strong>hitam</strong> ke rail GND (atau pin GND board).</li>
<li>Probe <strong>merah</strong> ke <strong>titik sinyal (S)</strong> — lubang di kolom kaki B tombol / kaki kiri resistor (lihat gambar utama), <strong>bukan</strong> pin GPIO.</li>
<li><strong>Tombol lepas:</strong> layar ≈ <strong>0 V</strong> (LOW).</li>
<li><strong>Tombol ditekan:</strong> layar ≈ <strong>3,3 V</strong> (HIGH).</li>
<li>Catat di kertas: lepas = ___ V · tekan = ___ V.</li>
</ol>
<p><strong>Tips:</strong> kalau angka acak saat lepas, resistor 10 kΩ mungkin belum ke GND — periksa ulang wiring.</p>

<h2>Praktik — tabel ukur tombol (isi di kertas)</h2>
<p>Salin tabel ini ke buku catatan. Isi kolom <strong>Hasil kamu</strong> setelah mengukur di meja.</p>
<table style="width:100%;border-collapse:collapse;margin:1rem 0;font-size:0.95rem">
<thead>
<tr style="background:#F5F5F0;border:2px solid #1a1a1a">
<th style="padding:0.5rem;border:1px solid #1a1a1a;text-align:left">Keadaan tombol</th>
<th style="padding:0.5rem;border:1px solid #1a1a1a;text-align:left">Target tegangan</th>
<th style="padding:0.5rem;border:1px solid #1a1a1a;text-align:left">Arti digital</th>
<th style="padding:0.5rem;border:1px solid #1a1a1a;text-align:left">Hasil kamu</th>
</tr>
</thead>
<tbody>
<tr><td style="padding:0.5rem;border:1px solid #ccc">Lepas (tidak ditekan)</td><td style="padding:0.5rem;border:1px solid #ccc">0 – 0,3 V</td><td style="padding:0.5rem;border:1px solid #ccc">LOW</td><td style="padding:0.5rem;border:1px solid #ccc">_____ V</td></tr>
<tr><td style="padding:0.5rem;border:1px solid #ccc">Ditekan</td><td style="padding:0.5rem;border:1px solid #ccc">3,0 – 3,4 V</td><td style="padding:0.5rem;border:1px solid #ccc">HIGH</td><td style="padding:0.5rem;border:1px solid #ccc">_____ V</td></tr>
</tbody>
</table>
<p><strong>Cara menguji:</strong> kedua baris terisi angka masuk akal = wiring pull-down benar. Opsional: foto layar multimeter saat tekan &amp; lepas. Tidak ada sintaks untuk dijalankan hari ini.</p>

<p><strong>Opsional sebelum wiring:</strong> kalau ragu jumper bagus/putus, ulangi tes <strong>continuity</strong> dari FS-07 (USB board tetap dicabut).</p>

<h2 id="fsiot-signal-checklist">Praktik — checklist sinyal digital 10 poin</h2>
<p>Centang setiap langkah setelah kamu lakukan di meja. Target: <strong>10/10</strong>. Ada <strong>checklist interaktif</strong> di bawah; versi kertas (tabel di atas) tetap tersedia.</p>
<ul id="fsiot-signal-checklist-items">
<li>USB dicabut sebelum mulai wiring</li>
<li>Tombol terpasang di kiri ESP32, melintasi parit tengah breadboard</li>
<li>Rail 3V3 dan GND terhubung dari ESP32</li>
<li>Jumper merah dari rail + ke kolom kaki A tombol</li>
<li>Resistor 10 kΩ (cokelat-hitam-oranye) dari titik sinyal S (kaki B tombol) ke GND lewat jumper hitam</li>
<li>Tidak ada short langsung 3V3 ke GND</li>
<li>Multimeter mode V DC, probe hitam di GND</li>
<li>Tombol lepas — titik sinyal membaca sekitar 0 V</li>
<li>Tombol ditekan — titik sinyal membaca sekitar 3,3 V</li>
<li>Bisa jelaskan kenapa tanpa 10 kΩ sinyal bisa mengambang</li>
</ul>
<p><strong>Cara menguji:</strong> kerjakan checklist di browser setelah ukur di meja. Tidak perlu Arduino IDE, terminal, atau <code>php artisan</code> — “menguji” = angka tegangan di multimeter + centang checklist (bukan perintah sintaks).</p>

<h2>Kesalahan yang sering terjadi</h2>
<ul>
<li><strong>Mengira analog lebih “keren”.</strong> Digital cukup untuk tombol on/off — analog dipakai saat nilai bertahap (sensor suhu, cahaya).</li>
<li><strong>Salah resistor.</strong> 220 Ω (FS-09) bukan pull-down — pakai <strong>10 kΩ</strong> (cokelat-hitam-oranye).</li>
<li><strong>Lupa resistor pull-down.</strong> Titik sinyal mengambang → angka acak di multimeter.</li>
<li><strong>Tombol tidak melintasi parit.</strong> Keempat kaki short sendiri — tombol tidak berfungsi.</li>
<li><strong>Salah mode multimeter.</strong> Harus <strong>V DC</strong>, bukan A (ampere) atau continuity saat ukur tegangan.</li>
<li><strong>Probe tertukar.</strong> Hitam ke GND, merah ke titik sinyal — konsisten seperti FS-07.</li>
<li><strong>Colok USB saat masih merakit.</strong> Selalu cabut dulu (FS-05) — baru colok setelah cek visual wiring.</li>
<li><strong>Langsung coding tanpa paham HIGH/LOW.</strong> Nanti di FS-11 <code>digitalRead</code> akan membingungkan kalau wiring salah.</li>
</ul>

<h2>Selanjutnya</h2>
<p><strong>Intinya:</strong> kalau lepas ≈ 0 V dan tekan ≈ 3,3 V, FS-10 selesai. Lanjut ke <strong>FS-11</strong> (sketch, <code>setup</code>, <code>loop</code>, tombol dibaca program) saat modulnya terbit.</p>
<p>Daftar modul: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $flow = $this->workflowSvgEn();
        $bb = $this->breadboardFigureEn();
        $pin = $this->pinoutFigureEn();
        $jump = $this->jumperFigureEn();
        $btn = $this->buttonFigureEn();
        $legs = $this->buttonLegsSvgEn();
        $r10k = $this->resistor10kFigureEn();
        $meter = $this->multimeterFigureEn();
        $pull = $this->pullReferenceFigureEn();
        $dig = $this->digitalAnalogSvgEn();
        $hl = $this->highLowSvgEn();
        $float = $this->floatingSvgEn();
        $wire = $this->wiringSvgEn();

        return <<<HTML
<h2>Introduction — signal language before code</h2>
<p>This article is <strong>#80 (this article)</strong> · module <strong>FS-10</strong> on the <em>Full Stack IoT Developer — From Zero</em> track. In <strong>FS-09</strong> your LED lit from wiring alone. Today you learn the <strong>on/off language</strong> used by ESP32 <strong>GPIO</strong> (<em>General Purpose Input/Output</em> — general input/output pins): <strong>HIGH</strong> and <strong>LOW</strong>, then build a <strong>button + 10 kΩ pull-down resistor</strong> on a breadboard.</p>
<p><strong>Analogy:</strong> GPIO is like the board’s ear — it must know whether it hears “yes” (HIGH) or “no” (LOW). Without setup, it only hears wind (floating pin).</p>
<p><strong>Prerequisites:</strong> FS-09 (breadboard + 3V3/GND) + FS-07 (multimeter V DC mode) + the <strong>unplug USB first</strong> habit from FS-05. <strong>No sketch upload or Arduino IDE today</strong> — we test voltage with a multimeter.</p>

<p><strong>How to use this article (work order):</strong></p>
<ol>
<li><strong>Gather tools</strong> on the desk — USB not plugged in yet.</li>
<li><strong>Read concepts</strong> digital/analog, HIGH/LOW, floating pins (images below).</li>
<li><strong>Build wiring</strong> following the main diagram.</li>
<li><strong>Open the multimeter</strong> (V DC mode) → measure the signal node released vs pressed.</li>
<li><strong>Write numbers</strong> on paper → tick the 10/10 checklist in the browser.</li>
</ol>
<p><strong>Not needed today:</strong> Arduino IDE, <code>pinMode</code>, <code>digitalRead</code>, Serial Monitor, Laragon, <code>php artisan</code>. Today’s tools: the <strong>browser</strong> (article + checklist) + kit on the desk + a <strong>multimeter</strong> (V DC mode).</p>
{$flow}

<h2>Preparation — open &amp; gather these first</h2>
<p><strong>Desk order:</strong> do not plug USB yet. Follow the steps below so you do not get lost.</p>
<ol>
<li><strong>Unplug USB</strong> from the ESP32 (required before touching jumpers).</li>
<li>Place the <strong>breadboard</strong> + <strong>ESP32-DevKitC-1</strong> on the desk.</li>
<li>Prepare a <strong>tactile button</strong>, <strong>10 kΩ resistor</strong> (not 220 Ω), <strong>red &amp; black</strong> jumpers matching the main diagram (power + button + resistor leg to GND — <strong>no GPIO jumper yet</strong>).</li>
<li><strong>Paper + pen</strong> — to record released vs pressed voltage.</li>
<li><strong>Open the multimeter</strong> — dial to <strong>V DC</strong> (same as FS-07). Black probe to COM, red to VΩmA.</li>
<li>Connect <strong>3V3</strong> and <strong>GND</strong> jumpers to breadboard power rails (repeat from FS-09).</li>
</ol>
<p><strong>Tools used today:</strong> breadboard, jumpers, tactile button, 10 kΩ resistor, ESP32 + data USB cable, multimeter, paper + pen.</p>
<p><strong>Not used today:</strong> Arduino IDE, laptop for upload, Laragon, web terminal.</p>

{$bb}
{$pin}
{$jump}
{$r10k}
{$btn}
{$legs}

<h2>Digital vs analog — what is the difference?</h2>
<p><strong>Digital</strong> = two clear states: on or off. <strong>Analog</strong> = values in between (e.g. 27.3 °C). ESP32 digital GPIO pins only read <strong>HIGH</strong> or <strong>LOW</strong>.</p>
<p><strong>In short:</strong> digital is not “more advanced” — it is simpler for computers (yes/no).</p>
{$dig}

<h2>HIGH and LOW on the ESP32</h2>
<p>The ESP32 uses <strong>3.3 V logic</strong>:</p>
<ul>
<li><strong>HIGH</strong> ≈ 3.3 V (close to the 3V3 pin voltage)</li>
<li><strong>LOW</strong> ≈ 0 V (same potential as GND)</li>
</ul>
<p><strong>Tip:</strong> do not tie 5 V directly to GPIO — that breaks 3.3 V rules.</p>
{$hl}

<h2>Floating pins &amp; pull-down resistors</h2>
<p>If an input pin is connected to neither 3V3 nor GND, it <strong>floats</strong> — readings can jump (ghost button).</p>
<p>A <strong>pull-down resistor</strong> gently pulls the signal node to GND through 10 kΩ. When the button is pressed, 3V3 “wins” and the signal goes HIGH.</p>
<p><strong>In short — pull-up vs pull-down:</strong> pull-down = default LOW (released = 0 V). Pull-up is the opposite (default HIGH) — the ESP32 has internal pull-ups we will use in FS-11; today we practice an external pull-down so the wiring is visible.</p>
{$float}
{$pull}

<h2>The circuit we will build</h2>
<p>When the <strong>button is released</strong>: the signal node connects to GND through 10 kΩ → LOW. When <strong>pressed</strong>: 3V3 connects to the signal node → HIGH. In the schematic the ESP32 only supplies <strong>3V3</strong> and <strong>GND</strong> via the rails — <strong>no jumper to any GPIO pin yet</strong>. The signal node (S) is the button leg B column (resistor side); it will connect to GPIO in FS-11. Today we test with a multimeter first.</p>
{$wire}

<h2>Step-by-step wiring</h2>
<p><strong>Tip:</strong> follow the <strong>main diagram</strong> above (labeled schematic). Column numbers may shift — keep the <strong>electrical order</strong> the same: <strong>3V3 → button → signal node (S) → 10 kΩ → GND</strong>.</p>
<ol>
<li><strong>Mount the ESP32</strong> on the breadboard across the ditch (antenna left, USB right) — only to supply <strong>3V3</strong> and <strong>GND</strong>.</li>
<li><strong>Power rails:</strong> red jumper from pin <strong>3V3</strong> to the red (+) rail. Black jumper from pin <strong>GND</strong> to the blue (−) rail.</li>
<li><strong>Button:</strong> place it to the <strong>left</strong> of the ESP32, across the <strong>center ditch</strong> (one side above = leg A, one side below = leg B).</li>
<li><strong>To 3V3:</strong> red jumper from the (+) rail to the <strong>button leg A column</strong> (top side of the ditch).</li>
<li><strong>Signal node (S):</strong> the <strong>leg B</strong> column on the bottom side of the ditch — this is where the left resistor leg and the red probe go. <strong>Do not wire to GPIO yet.</strong></li>
<li><strong>10 kΩ resistor:</strong> one leg in column S, the other a few columns to the right (brown-black-orange bands).</li>
<li><strong>To GND:</strong> black jumper from the right resistor leg to the blue (−) rail.</li>
<li><strong>Visual check:</strong> no direct 3V3–GND short; button truly across the ditch; no wire to an IO pin.</li>
<li><strong>Plug USB</strong> — the board powers the rails (do not touch bare probe tips).</li>
</ol>

<h2>Test with a multimeter (V DC mode)</h2>
<p><strong>Open this tool first:</strong> take the multimeter from FS-07, confirm the dial is in <strong>V DC</strong> mode, then touch the signal node (S). This is not a terminal command — the tool is on your desk.</p>
{$meter}
<p><strong>Measurement steps:</strong></p>
<ol>
<li><strong>Black</strong> probe on the GND rail (or board GND pin).</li>
<li><strong>Red</strong> probe on the <strong>signal node (S)</strong> — the hole in the button leg B / left resistor column (see the main diagram), <strong>not</strong> a GPIO pin.</li>
<li><strong>Button released:</strong> display ≈ <strong>0 V</strong> (LOW).</li>
<li><strong>Button pressed:</strong> display ≈ <strong>3.3 V</strong> (HIGH).</li>
<li>Write on paper: released = ___ V · pressed = ___ V.</li>
</ol>
<p><strong>Tip:</strong> if the value drifts when released, the 10 kΩ may not reach GND — recheck wiring.</p>

<h2>Practice — button measurement table (on paper)</h2>
<p>Copy this table into your notebook. Fill <strong>Your result</strong> after measuring on the desk.</p>
<table style="width:100%;border-collapse:collapse;margin:1rem 0;font-size:0.95rem">
<thead>
<tr style="background:#F5F5F0;border:2px solid #1a1a1a">
<th style="padding:0.5rem;border:1px solid #1a1a1a;text-align:left">Button state</th>
<th style="padding:0.5rem;border:1px solid #1a1a1a;text-align:left">Target voltage</th>
<th style="padding:0.5rem;border:1px solid #1a1a1a;text-align:left">Digital meaning</th>
<th style="padding:0.5rem;border:1px solid #1a1a1a;text-align:left">Your result</th>
</tr>
</thead>
<tbody>
<tr><td style="padding:0.5rem;border:1px solid #ccc">Released (not pressed)</td><td style="padding:0.5rem;border:1px solid #ccc">0 – 0.3 V</td><td style="padding:0.5rem;border:1px solid #ccc">LOW</td><td style="padding:0.5rem;border:1px solid #ccc">_____ V</td></tr>
<tr><td style="padding:0.5rem;border:1px solid #ccc">Pressed</td><td style="padding:0.5rem;border:1px solid #ccc">3.0 – 3.4 V</td><td style="padding:0.5rem;border:1px solid #ccc">HIGH</td><td style="padding:0.5rem;border:1px solid #ccc">_____ V</td></tr>
</tbody>
</table>
<p><strong>How to test:</strong> both rows filled with sensible numbers = correct pull-down wiring. Optional: photo of the multimeter display pressed &amp; released. No syntax to run today.</p>

<p><strong>Optional before wiring:</strong> if you doubt a jumper is good, repeat the <strong>continuity</strong> test from FS-07 (board USB still unplugged).</p>

<h2 id="fsiot-signal-checklist">Practice — 10-point digital signal checklist</h2>
<p>Tick each step after you do it on the desk. Target: <strong>10/10</strong>. An <strong>interactive checklist</strong> is below; the paper version (table above) stays available.</p>
<ul id="fsiot-signal-checklist-items">
<li>USB unplugged before wiring</li>
<li>Button mounted left of the ESP32, across the breadboard center ditch</li>
<li>3V3 and GND rails connected from the ESP32</li>
<li>Red jumper from the + rail to the button leg A column</li>
<li>10 kΩ resistor (brown-black-orange) from signal node S (button leg B) to GND via a black jumper</li>
<li>No direct short from 3V3 to GND</li>
<li>Multimeter in V DC mode, black probe on GND</li>
<li>Button released — signal node reads about 0 V</li>
<li>Button pressed — signal node reads about 3.3 V</li>
<li>Can explain why the signal floats without the 10 kΩ</li>
</ul>
<p><strong>How to test:</strong> use the browser checklist after measuring on the desk. No Arduino IDE, terminal, or <code>php artisan</code> — “testing” means voltage numbers on the multimeter + ticking the checklist (not syntax commands).</p>

<h2>Common mistakes</h2>
<ul>
<li><strong>Thinking analog is “cooler”.</strong> Digital is enough for on/off buttons — analog is for gradual values (temperature, light).</li>
<li><strong>Wrong resistor.</strong> 220 Ω (FS-09) is not a pull-down — use <strong>10 kΩ</strong> (brown-black-orange).</li>
<li><strong>Forgetting the pull-down resistor.</strong> The signal node floats → random multimeter values.</li>
<li><strong>Button not across the ditch.</strong> All four legs short together — button does not work.</li>
<li><strong>Wrong multimeter mode.</strong> Must be <strong>V DC</strong>, not A (amps) or continuity when measuring voltage.</li>
<li><strong>Swapped probes.</strong> Black on GND, red on signal — stay consistent like FS-07.</li>
<li><strong>Plugging USB while still wiring.</strong> Always unplug first (FS-05) — plug in only after a visual wiring check.</li>
<li><strong>Coding before understanding HIGH/LOW.</strong> In FS-11, <code>digitalRead</code> will confuse you if wiring is wrong.</li>
</ul>

<h2>Next steps</h2>
<p><strong>In short:</strong> if released ≈ 0 V and pressed ≈ 3.3 V, FS-10 is done. Continue to <strong>FS-11</strong> (sketch, <code>setup</code>, <code>loop</code>, reading the button in code) when that module publishes.</p>
<p>Full module list: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }
}
