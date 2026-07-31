<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article75Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first()
            ?? Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $iotCat) {
            throw new \RuntimeException('User atau kategori iot-smart-device/esp32-arduino tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-keselamatan-sebelum-listrik';

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
                'title'              => 'Keselamatan & kebiasaan sebelum mengalirkan listrik',
                'title_en'           => 'Safety & habits before you apply power',
                'excerpt'            => 'FS-05 / #75: Short circuit, 3.3V vs 5V, cabut USB dulu. Checklist 10 poin sebelum power ON. Belum wiring menyala.',
                'excerpt_en'         => 'FS-05 / #75: Short circuits, 3.3V vs 5V, unplug USB first. 10-point checklist before power ON. No powered wiring yet.',
                'body'               => $this->body(),
                'body_en'            => $this->bodyEn(),
                'status'             => 'draft',
                'is_featured'        => false,
                'published_at'       => null,
                'seo_title'          => 'Keselamatan ESP32 Sebelum Listrik — Full Stack IoT #75',
                'seo_title_en'       => 'ESP32 Safety Before Power — Full Stack IoT #75',
                'seo_description'    => 'Belajar short circuit, 3.3V vs 5V, polaritas, USB data, dan checklist cabut USB dulu. Modul FS-05 Full Stack IoT.',
                'seo_description_en' => 'Learn short circuits, 3.3V vs 5V, polarity, USB data, and the unplug-USB-first checklist. Full Stack IoT module FS-05.',
            ]
        );

        if ($article->published_at !== null || $article->status !== 'draft') {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['fullstack-iot', 'iot', 'esp32'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel #75 / FS-05 tersimpan sebagai DRAFT: '.$article->title);
    }

    private function overviewFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/esp32-devkitc-overview.jpg" width="1200" height="519" alt="Foto overview board ESP32-DevKitC dari dokumentasi Espressif" loading="lazy" style="width:100%;height:auto;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    Kenali lagi letak <strong>USB</strong>, tombol, dan pin di board (masih pengenalan — belum colok untuk latihan).
    <br>Sumber gambar: <a href="https://docs.espressif.com/projects/esp-dev-kits/en/latest/esp32/esp32-devkitc/user_guide.html" rel="noopener noreferrer" target="_blank">Espressif Systems — ESP32-DevKitC User Guide</a> (dokumen resmi).
  </figcaption>
</figure>
HTML;
    }

    private function overviewFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/esp32-devkitc-overview.jpg" width="1200" height="519" alt="ESP32-DevKitC board overview photo from Espressif documentation" loading="lazy" style="width:100%;height:auto;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    Spot the <strong>USB</strong> socket, buttons, and pins again (introduction only — no practice plug-in yet).
    <br>Image source: <a href="https://docs.espressif.com/projects/esp-dev-kits/en/latest/esp32/esp32-devkitc/user_guide.html" rel="noopener noreferrer" target="_blank">Espressif Systems — ESP32-DevKitC User Guide</a> (official docs).
  </figcaption>
</figure>
HTML;
    }

    private function ledPolarityFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/kit-led-5mm.jpg" alt="Foto LED 5mm putih dengan dua kaki berbeda panjang" loading="lazy" style="width:100%;height:auto;max-height:360px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    Contoh <strong>LED 5mm</strong> — kaki lebih panjang biasanya (+), lebih pendek (−). Modul sensor punya label <strong>VCC/+</strong> dan <strong>GND/−</strong> di PCB.
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:5mm_LED_Light-emitting_diode_White_1480334_5_6HDR_Enhancer.jpg" rel="noopener noreferrer" target="_blank">Wikimedia Commons — 5mm white LED</a> (CC BY-SA 3.0).
  </figcaption>
</figure>
HTML;
    }

    private function ledPolarityFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/kit-led-5mm.jpg" alt="Photo of a white 5mm LED with two legs of different lengths" loading="lazy" style="width:100%;height:auto;max-height:360px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    Example <strong>5mm LED</strong> — the longer leg is usually (+), the shorter (−). Sensor modules label <strong>VCC/+</strong> and <strong>GND/−</strong> on the PCB.
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:5mm_LED_Light-emitting_diode_White_1480334_5_6HDR_Enhancer.jpg" rel="noopener noreferrer" target="_blank">Wikimedia Commons — 5mm white LED</a> (CC BY-SA 3.0).
  </figcaption>
</figure>
HTML;
    }

    private function multimeterFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/kit-multimeter.jpg" alt="Foto multimeter digital dengan layar dan probe" loading="lazy" style="width:100%;height:auto;max-height:420px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    Multimeter akan dipakai untuk <strong>mengukur</strong> di FS-07. Hari ini cukup kenali bentuknya — belum wajib mengukur.
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:2017_Cyfrowy_miernik_uniwersalny.jpg" rel="noopener noreferrer" target="_blank">Jacek Halicki — digital multimeter</a> · Wikimedia Commons (CC BY-SA 4.0).
  </figcaption>
</figure>
HTML;
    }

    private function multimeterFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/kit-multimeter.jpg" alt="Photo of a digital multimeter with a display and probes" loading="lazy" style="width:100%;height:auto;max-height:420px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    You will <strong>measure</strong> with a multimeter in FS-07. Today just recognize the shape — measuring is not required yet.
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:2017_Cyfrowy_miernik_uniwersalny.jpg" rel="noopener noreferrer" target="_blank">Jacek Halicki — digital multimeter</a> · Wikimedia Commons (CC BY-SA 4.0).
  </figcaption>
</figure>
HTML;
    }

    private function shortSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Short circuit: 3V3 tersambung langsung ke GND" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 220" width="100%" height="auto" role="img" aria-label="Short circuit">
  <text x="360" y="28" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700">Short circuit = “jalan pintas berbahaya”</text>
  <rect x="40" y="60" width="140" height="70" rx="6" fill="#E8F5E9" stroke="#1a1a1a" stroke-width="2"/>
  <text x="110" y="90" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">3V3</text>
  <text x="110" y="112" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">tegangan</text>
  <rect x="540" y="60" width="140" height="70" rx="6" fill="#E3F2FD" stroke="#1a1a1a" stroke-width="2"/>
  <text x="610" y="90" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">GND</text>
  <text x="610" y="112" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">tanah / 0V</text>
  <line x1="180" y1="95" x2="540" y2="95" stroke="#C62828" stroke-width="4"/>
  <circle cx="360" cy="95" r="22" fill="#FFEBEE" stroke="#C62828" stroke-width="3"/>
  <text x="360" y="101" text-anchor="middle" font-family="system-ui,sans-serif" font-size="18" font-weight="700" fill="#C62828">✕</text>
  <text x="360" y="160" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" fill="#C62828">Jumper menyambung 3V3 ↔ GND tanpa “beban”</text>
  <text x="360" y="185" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#4A5568">Arus “kebanyakan” → board / kabel / USB bisa panas atau mati</text>
  <text x="360" y="208" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Buatan Koding Indonesia</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Contoh visual short circuit (buatan Koding Indonesia). Jangan sengaja mencobanya.</figcaption>
</figure>
SVG;
    }

    private function shortSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Short circuit: 3V3 connected straight to GND" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 220" width="100%" height="auto" role="img" aria-label="Short circuit">
  <text x="360" y="28" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700">Short circuit = a dangerous “shortcut”</text>
  <rect x="40" y="60" width="140" height="70" rx="6" fill="#E8F5E9" stroke="#1a1a1a" stroke-width="2"/>
  <text x="110" y="90" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">3V3</text>
  <text x="110" y="112" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">voltage</text>
  <rect x="540" y="60" width="140" height="70" rx="6" fill="#E3F2FD" stroke="#1a1a1a" stroke-width="2"/>
  <text x="610" y="90" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">GND</text>
  <text x="610" y="112" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">ground / 0V</text>
  <line x1="180" y1="95" x2="540" y2="95" stroke="#C62828" stroke-width="4"/>
  <circle cx="360" cy="95" r="22" fill="#FFEBEE" stroke="#C62828" stroke-width="3"/>
  <text x="360" y="101" text-anchor="middle" font-family="system-ui,sans-serif" font-size="18" font-weight="700" fill="#C62828">✕</text>
  <text x="360" y="160" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" fill="#C62828">A jumper ties 3V3 ↔ GND with no “load”</text>
  <text x="360" y="185" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#4A5568">Too much current → board / cable / USB can heat up or die</text>
  <text x="360" y="208" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">By Koding Indonesia</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Visual example of a short circuit (by Koding Indonesia). Do not try this on purpose.</figcaption>
</figure>
SVG;
    }

    private function voltageSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Perbandingan 3.3V dan 5V di ESP32" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 230" width="100%" height="auto" role="img" aria-label="3.3V vs 5V">
  <text x="360" y="28" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700">3.3V vs 5V di dunia ESP32</text>
  <rect x="30" y="50" width="300" height="130" rx="8" fill="#E8F5E9" stroke="#1a1a1a" stroke-width="2"/>
  <text x="180" y="85" text-anchor="middle" font-family="system-ui,sans-serif" font-size="18" font-weight="700">3V3 / 3.3V</text>
  <text x="180" y="115" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13">Pin “logika” GPIO ESP32</text>
  <text x="180" y="138" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13">Aman untuk sinyal board</text>
  <text x="180" y="161" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#2E7D32">✓ ini “bahasa” ESP32</text>
  <rect x="390" y="50" width="300" height="130" rx="8" fill="#FFF3E0" stroke="#1a1a1a" stroke-width="2"/>
  <text x="540" y="85" text-anchor="middle" font-family="system-ui,sans-serif" font-size="18" font-weight="700">5V</text>
  <text x="540" y="115" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13">Ada di pin 5V / USB</text>
  <text x="540" y="138" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13">Untuk beberapa modul daya</text>
  <text x="540" y="161" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#E65100">⚠ jangan ke pin GPIO</text>
  <text x="360" y="210" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#4A5568">Buatan Koding Indonesia — detail ukur di FS-07</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Beda 3.3V dan 5V (buatan Koding Indonesia). Menyambung 5V ke pin GPIO sering merusak chip.</figcaption>
</figure>
SVG;
    }

    private function voltageSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Comparing 3.3V and 5V on ESP32" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 230" width="100%" height="auto" role="img" aria-label="3.3V vs 5V">
  <text x="360" y="28" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700">3.3V vs 5V in the ESP32 world</text>
  <rect x="30" y="50" width="300" height="130" rx="8" fill="#E8F5E9" stroke="#1a1a1a" stroke-width="2"/>
  <text x="180" y="85" text-anchor="middle" font-family="system-ui,sans-serif" font-size="18" font-weight="700">3V3 / 3.3V</text>
  <text x="180" y="115" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13">ESP32 GPIO “logic” pins</text>
  <text x="180" y="138" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13">Safe for board signals</text>
  <text x="180" y="161" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#2E7D32">✓ the ESP32’s “language”</text>
  <rect x="390" y="50" width="300" height="130" rx="8" fill="#FFF3E0" stroke="#1a1a1a" stroke-width="2"/>
  <text x="540" y="85" text-anchor="middle" font-family="system-ui,sans-serif" font-size="18" font-weight="700">5V</text>
  <text x="540" y="115" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13">On the 5V / USB pins</text>
  <text x="540" y="138" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13">For some power modules</text>
  <text x="540" y="161" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#E65100">⚠ not into GPIO pins</text>
  <text x="360" y="210" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#4A5568">By Koding Indonesia — measuring detail in FS-07</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">3.3V vs 5V (by Koding Indonesia). Feeding 5V into a GPIO often kills the chip.</figcaption>
</figure>
SVG;
    }

    private function unplugSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Urutan: cabut USB dulu sebelum ubah kabel" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 180" width="100%" height="auto" role="img" aria-label="Unplug USB first">
  <text x="360" y="28" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700">Kebiasaan emas: cabut USB dulu</text>
  <rect x="20" y="50" width="200" height="90" rx="6" fill="#E3F2FD" stroke="#1a1a1a" stroke-width="2"/>
  <text x="120" y="85" text-anchor="middle" font-family="system-ui,sans-serif" font-size="22" font-weight="700">1</text>
  <text x="120" y="112" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13">Cabut USB</text>
  <text x="120" y="130" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">board “mati”</text>
  <text x="250" y="100" text-anchor="middle" font-family="system-ui,sans-serif" font-size="22">→</text>
  <rect x="280" y="50" width="200" height="90" rx="6" fill="#FFF8E1" stroke="#1a1a1a" stroke-width="2"/>
  <text x="380" y="85" text-anchor="middle" font-family="system-ui,sans-serif" font-size="22" font-weight="700">2</text>
  <text x="380" y="112" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13">Ubah jumper</text>
  <text x="380" y="130" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">tanpa hot-plug</text>
  <text x="510" y="100" text-anchor="middle" font-family="system-ui,sans-serif" font-size="22">→</text>
  <rect x="530" y="50" width="200" height="90" rx="6" fill="#E8F5E9" stroke="#1a1a1a" stroke-width="2"/>
  <text x="630" y="85" text-anchor="middle" font-family="system-ui,sans-serif" font-size="22" font-weight="700">3</text>
  <text x="630" y="112" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13">Colok lagi</text>
  <text x="630" y="130" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">cek dulu</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Urutan aman sebelum mengubah kabel (buatan Koding Indonesia). Hot-plug = mencabut/menyambung saat masih beraliran listrik.</figcaption>
</figure>
SVG;
    }

    private function unplugSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Sequence: unplug USB before changing wires" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 180" width="100%" height="auto" role="img" aria-label="Unplug USB first">
  <text x="360" y="28" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700">Golden habit: unplug USB first</text>
  <rect x="20" y="50" width="200" height="90" rx="6" fill="#E3F2FD" stroke="#1a1a1a" stroke-width="2"/>
  <text x="120" y="85" text-anchor="middle" font-family="system-ui,sans-serif" font-size="22" font-weight="700">1</text>
  <text x="120" y="112" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13">Unplug USB</text>
  <text x="120" y="130" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">board is “off”</text>
  <text x="250" y="100" text-anchor="middle" font-family="system-ui,sans-serif" font-size="22">→</text>
  <rect x="280" y="50" width="200" height="90" rx="6" fill="#FFF8E1" stroke="#1a1a1a" stroke-width="2"/>
  <text x="380" y="85" text-anchor="middle" font-family="system-ui,sans-serif" font-size="22" font-weight="700">2</text>
  <text x="380" y="112" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13">Change jumpers</text>
  <text x="380" y="130" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">no hot-plug</text>
  <text x="510" y="100" text-anchor="middle" font-family="system-ui,sans-serif" font-size="22">→</text>
  <rect x="530" y="50" width="200" height="90" rx="6" fill="#E8F5E9" stroke="#1a1a1a" stroke-width="2"/>
  <text x="630" y="85" text-anchor="middle" font-family="system-ui,sans-serif" font-size="22" font-weight="700">3</text>
  <text x="630" y="112" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13">Plug back in</text>
  <text x="630" y="130" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">check first</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Safe sequence before changing wires (by Koding Indonesia). Hot-plug = plugging/unplugging while power is still on.</figcaption>
</figure>
SVG;
    }

    private function usbSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="USB data vs charge-only" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 150" width="100%" height="auto" role="img" aria-label="USB data vs charge">
  <rect x="30" y="30" width="300" height="90" fill="#E8F5E9" stroke="#1a1a1a" stroke-width="2" rx="4"/>
  <text x="180" y="65" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">USB data ✓</text>
  <text x="180" y="90" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Charge + kirim program</text>
  <text x="180" y="108" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Ini yang kita butuhkan</text>
  <rect x="390" y="30" width="300" height="90" fill="#FFEBEE" stroke="#1a1a1a" stroke-width="2" rx="4"/>
  <text x="540" y="65" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">Charge-only ✗</text>
  <text x="540" y="90" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Hanya isi daya</text>
  <text x="540" y="108" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Port COM sering “hilang”</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Ulangan singkat dari FS-04 (buatan Koding Indonesia). Tes upload baru di FS-06.</figcaption>
</figure>
SVG;
    }

    private function usbSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="USB data vs charge-only" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 150" width="100%" height="auto" role="img" aria-label="USB data vs charge">
  <rect x="30" y="30" width="300" height="90" fill="#E8F5E9" stroke="#1a1a1a" stroke-width="2" rx="4"/>
  <text x="180" y="65" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">USB data ✓</text>
  <text x="180" y="90" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Charge + send programs</text>
  <text x="180" y="108" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">This is what we need</text>
  <rect x="390" y="30" width="300" height="90" fill="#FFEBEE" stroke="#1a1a1a" stroke-width="2" rx="4"/>
  <text x="540" y="65" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">Charge-only ✗</text>
  <text x="540" y="90" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Power only</text>
  <text x="540" y="108" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">COM port often “missing”</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Quick recap from FS-04 (by Koding Indonesia). Upload testing comes in FS-06.</figcaption>
</figure>
SVG;
    }

    private function body(): string
    {
        $overview = $this->overviewFigureId();
        $short = $this->shortSvgId();
        $volt = $this->voltageSvgId();
        $ledPol = $this->ledPolarityFigureId();
        $unplug = $this->unplugSvgId();
        $usb = $this->usbSvgId();
        $mm = $this->multimeterFigureId();

        return <<<HTML
<h2>Pendahuluan — kenapa keselamatan dulu?</h2>
<p>Artikel ini adalah <strong>#75 (ini)</strong> · modul <strong>FS-05</strong> di jalur <em>Full Stack IoT Developer — Dari Nol</em>. Di <strong>FS-04</strong> kamu sudah kenal nama benda di kit. Hari ini kita belajar <strong>kebiasaan aman</strong> sebelum mengalirkan listrik — supaya board, USB, dan kamu sendiri aman.</p>
<p><strong>Analogi:</strong> seperti menyetir — sebelum gas, kamu cek rem &amp; sabuk. Di sini “rem”-nya adalah checklist cabut USB &amp; tidak menyambung pin yang salah.</p>
<p><strong>Prasyarat:</strong> ide dari FS-04 (kenal board, breadboard, jumper). Belum wajib sudah punya semua barang — tapi board + beberapa jumper sangat membantu untuk “latihan bayangan” di meja.</p>

<p><strong>Cara pakai artikel ini (urutan baca):</strong></p>
<ol>
<li><strong>Buka browser</strong> — baca artikel di laptop atau HP.</li>
<li><strong>Siapkan kertas</strong> (opsional) — salin checklist 10 poin; tempel di meja.</li>
<li><strong>Lihat diagram</strong> short circuit, 3.3V vs 5V, polaritas LED, USB, urutan cabut USB.</li>
<li><strong>Centang checklist interaktif</strong> 10/10 di akhir artikel.</li>
<li><strong>Ulangi dengan suara</strong>: “cabut dulu, cek 3V3/GND, jangan 5V ke GPIO”.</li>
</ol>
<p><strong>Tidak perlu hari ini:</strong> Arduino IDE, driver USB, terminal, Laragon, <code>php artisan</code>, unggah sketch, colok USB untuk latihan, mengukur dengan multimeter.</p>

<h2>Persiapan — alat yang kamu buka hari ini</h2>
<p><strong>Alat yang dipakai di artikel ini</strong> (belum Arduino IDE, belum driver USB, belum terminal, belum Laragon, belum mengukur dengan multimeter):</p>
<ul>
<li><strong>Browser</strong> — membaca artikel + mengerjakan <strong>checklist interaktif</strong> 10 poin di akhir.</li>
<li><strong>ESP32-DevKitC-1 + jumper</strong> di meja (opsional tapi berguna) — hanya untuk menunjuk pin; <strong>belum colok USB untuk latihan</strong>.</li>
<li><strong>Kertas + spidol</strong> — salin checklist 10 poin; tempel di meja kerja.</li>
</ul>
<p><strong>Tidak ada perintah sintaks hari ini.</strong> Tidak ada baris kode, tidak ada <code>php artisan</code>, tidak ada sketch Arduino, tidak ada upload. Cara “menguji” di FS-05 = <strong>baca + sebut ulang + centang checklist</strong>. Belum menyalakan rangkaian latihan.</p>
<p><strong>Tips:</strong> kalau kamu hanya punya browser hari ini, tetap bisa lulus FS-05 dengan checklist. Kit fisik dipakai lebih intens mulai FS-06–FS-09.</p>

{$overview}

<h2>Short circuit — jalan pintas yang merusak</h2>
<p><strong>Short circuit</strong> (hubungan singkat) = jalur listrik yang terlalu “mudah”, biasanya karena <strong>3V3 tersambung langsung ke GND</strong> (atau 5V ke GND) lewat jumper / kaki komponen yang salah.</p>
{$short}
<p><strong>Analogi:</strong> bayangkan selang air yang disambung lurus ke saluran buang tanpa keran — air “tidak ada yang menahan”. Di listrik, yang “kebanyakan” adalah arus; chip atau kabel bisa panas.</p>
<ul>
<li>Gejala umum: board reset terus, USB komputer “klik”, bau panas, LED power padam.</li>
<li>Pencegahan: cabut USB dulu, lalu cek tidak ada jumper 3V3↔GND yang tak sengaja.</li>
</ul>

<h2>3.3V vs 5V — dua “bahasa” tegangan</h2>
<p>Di board ESP32-DevKitC-1 ada pin berlabel <strong>3V3</strong> dan sering juga <strong>5V</strong>. Keduanya <em>bukan</em> sama.</p>
{$volt}
<ul>
<li><strong>GPIO</strong> (pin sinyal) = logika <strong>3.3V</strong>. Jangan masukkan 5V ke situ.</li>
<li><strong>5V</strong> biasanya dari USB — untuk beberapa modul yang butuh 5V (misalnya sebagian relay), dengan aturan nanti di modul aktuator.</li>
<li><strong>GND</strong> = “tanah” bersama. Modul luar harus berbagi GND dengan board.</li>
</ul>
<p><strong>Analogi:</strong> 3.3V seperti bis kecil; 5V seperti truk. Truk masuk jalur bis → tabrakan. Jangan “asal colok karena lubangnya masuk”.</p>

<h2>Polaritas &amp; ground bersama</h2>
<p>Dari FS-04: LED punya kaki panjang (+) dan pendek (−). Sensor/modul sering punya pin <strong>VCC / +</strong>, <strong>GND / −</strong>, dan sinyal. Tertukar polaritas → modul tidak hidup atau rusak.</p>
{$ledPol}
<ul>
<li><strong>Polaritas:</strong> + ke +, − ke − (GND).</li>
<li><strong>Ground bersama:</strong> kalau pakai sensor di breadboard, sambungkan GND sensor ke GND board.</li>
<li>Belum merakit hari ini — cukup hafal aturan mainnya.</li>
</ul>
<p><strong>Analogi:</strong> ground bersama seperti “lantai yang sama” supaya dua orang bisa bergandengan tangan. Tanpa lantai yang sama, sinyal “mengambang”.</p>

<h2>USB data vs charge-only — pengingat singkat</h2>
<p>Di FS-04 kamu sudah lihat bedanya. Di sini diulang karena sering jadi “port hilang” saat mulai upload di FS-06.</p>
{$usb}
<p><strong>Tips:</strong> kabel murah sering charge-only. Kalau nanti komputer tidak melihat port COM, curigai kabel dulu — bukan chip-mu “rusak”.</p>

<h2>Kebiasaan emas — cabut USB dulu</h2>
<p><strong>Hot-plug</strong> = mengubah jumper / menusuk kabel saat board masih tersambung USB (masih beraliran). Itu kebiasaan berbahaya di awal belajar.</p>
{$unplug}
<ol>
<li>Cabut USB dari board (atau dari komputer).</li>
<li>Ubah jumper / komponen dengan tenang.</li>
<li>Cek sekali lagi (3V3 tidak nempel GND, polaritas OK).</li>
<li>Baru colok USB lagi.</li>
</ol>
<p><strong>Analogi:</strong> seperti mengganti baterai remote — kamu cabut dulu, bukan cabut-pasang sambil tombol masih ditekan.</p>

{$mm}

<h2 id="fsiot-safety-checklist">Praktik — checklist 10 poin sebelum power ON</h2>
<p>Ini checklist yang akan kamu pakai di modul berikutnya. Di bawah ada <strong>checklist interaktif</strong>: centang tiap kebiasaan yang sudah kamu pahami. Versi kertas (salin spidol) tetap tersedia. Target: <strong>10/10 tercentang</strong>.</p>
<ul id="fsiot-safety-checklist-items">
<li>Saya akan cabut USB dulu sebelum mengubah jumper</li>
<li>Saya tahu short = 3V3 (atau 5V) nyambung langsung ke GND itu berbahaya</li>
<li>Saya tidak akan menyambung 5V ke pin GPIO</li>
<li>Saya tahu GPIO ESP32 memakai logika 3.3V</li>
<li>Saya akan jaga polaritas (+/−) saat menyambung LED/modul</li>
<li>Saya paham modul luar perlu ground bersama (GND ke GND)</li>
<li>Saya pakai / akan cari kabel USB data (bukan charge-only)</li>
<li>Meja kerja kering; saya tidak bekerja di lantai basah</li>
<li>Saya tidak “coba-coba colok” tanpa rencana pin</li>
<li>Checklist ini akan saya baca ulang sebelum latihan wiring berikutnya</li>
</ul>
<p><strong>Cara menguji:</strong> kerjakan checklist interaktif di browser. Opsional: tulis 10 poin di kertas, foto di meja. Tidak perlu menjalankan perintah apa pun. Belum colok USB untuk upload.</p>

<h2>Kesalahan yang sering terjadi</h2>
<ul>
<li><strong>Hot-plug sembarangan.</strong> Ubah kabel saat USB masih colok → risiko short &amp; pin bengkok.</li>
<li><strong>Menyambung 5V ke pin GPIO.</strong> Logika ESP32 = 3.3V.</li>
<li><strong>Jumper 3V3–GND tak sengaja.</strong> Cek visual sebelum colok USB.</li>
<li><strong>Kabel USB charge-only.</strong> Gejala di FS-06: port tidak muncul.</li>
<li><strong>Mengira “asal lubang masuk = aman”.</strong> Breadboard &amp; pin punya aturan.</li>
<li><strong>Langsung ukur / wiring tanpa checklist.</strong> FS-07 baru untuk multimeter; FS-09 baru untuk LED menyala.</li>
<li><strong>Mencampur Seri ESP32 lama sebagai prasyarat.</strong> Jalur ini mandiri dari nol.</li>
</ul>

<h2>Lanjut belajar</h2>
<p>Setelah FS-05, langkah alami berikutnya adalah <strong>FS-06 — komputer siap: driver USB + Arduino IDE dari nol</strong>. Artikel itu belum dilink di sini sampai modulnya siap.</p>
<p>Simpan juga <a href="/belajar/fullstack-iot">halaman jalur Full Stack IoT</a> sebagai pintu masuk resmi.</p>

<h2>Kesimpulan</h2>
<p>Di <strong>#75 (ini)</strong> kamu sudah paham short circuit, beda 3.3V vs 5V, polaritas &amp; ground bersama, pengingat USB data, dan kebiasaan <strong>cabut USB dulu</strong>. Checklist 10 poin siap dipakai di modul berikutnya — masih belum wiring menyala.</p>
<p><strong>Intinya:</strong> kalau kamu bisa bilang “cabut dulu, cek 3V3/GND, jangan 5V ke GPIO”, FS-05 selesai. Lanjut ke setup komputer di FS-06 saat modulnya terbit.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $overview = $this->overviewFigureEn();
        $short = $this->shortSvgEn();
        $volt = $this->voltageSvgEn();
        $ledPol = $this->ledPolarityFigureEn();
        $unplug = $this->unplugSvgEn();
        $usb = $this->usbSvgEn();
        $mm = $this->multimeterFigureEn();

        return <<<HTML
<h2>Introduction — why safety first?</h2>
<p>This article is <strong>#75 (this article)</strong> · module <strong>FS-05</strong> on the <em>Full Stack IoT Developer — From Zero</em> path. In <strong>FS-04</strong> you learned the names of kit parts. Today we learn <strong>safe habits</strong> before applying power — so the board, USB, and you stay safe.</p>
<p><strong>Analogy:</strong> like driving — before you hit the gas, you check brakes and seatbelt. Here the “brake” is the unplug-USB checklist and not wiring the wrong pins.</p>
<p><strong>Prerequisites:</strong> ideas from FS-04 (know the board, breadboard, jumpers). You do not need every part yet — but a board + a few jumpers help for “air practice” on the desk.</p>

<p><strong>How to use this article (reading order):</strong></p>
<ol>
<li><strong>Open a browser</strong> — read on a laptop or phone.</li>
<li><strong>Prepare paper</strong> (optional) — copy the 10-point checklist; keep it on the desk.</li>
<li><strong>Study the diagrams</strong> for short circuit, 3.3V vs 5V, LED polarity, USB, and unplug order.</li>
<li><strong>Tick the interactive checklist</strong> 10/10 at the end.</li>
<li><strong>Say it out loud</strong>: “unplug first, check 3V3/GND, no 5V into GPIO”.</li>
</ol>
<p><strong>Not needed today:</strong> Arduino IDE, USB drivers, terminal, Laragon, <code>php artisan</code>, uploading a sketch, plugging USB for practice, or measuring with a multimeter.</p>

<h2>Preparation — tools you open today</h2>
<p><strong>Tools used in this article</strong> (no Arduino IDE yet, no USB driver, no terminal, no Laragon, no multimeter measuring yet):</p>
<ul>
<li><strong>Browser</strong> — read the article + complete the <strong>interactive 10-point checklist</strong> at the end.</li>
<li><strong>ESP32-DevKitC-1 + jumpers</strong> on the desk (optional but useful) — only to point at pins; <strong>do not plug USB for practice</strong>.</li>
<li><strong>Paper + marker</strong> — copy the 10-point checklist; stick it on your work desk.</li>
</ul>
<p><strong>There is no syntax to run today.</strong> No code lines, no <code>php artisan</code>, no Arduino sketch, no upload. How you “test” in FS-05 = <strong>read + restate + tick the checklist</strong>. No practice circuit is powered yet.</p>
<p><strong>Tip:</strong> if you only have a browser today, you can still pass FS-05 with the checklist. The physical kit gets used more from FS-06–FS-09.</p>

{$overview}

<h2>Short circuits — the shortcut that breaks things</h2>
<p>A <strong>short circuit</strong> is an electrical path that is too “easy”, usually because <strong>3V3 is tied straight to GND</strong> (or 5V to GND) through a jumper or a misplaced component leg.</p>
{$short}
<p><strong>Analogy:</strong> picture a water hose dumped straight into a drain with no tap — nothing holds the flow. In electricity, the “too much” is current; the chip or cable can heat up.</p>
<ul>
<li>Common symptoms: board keeps resetting, computer USB “clicks”, hot smell, power LED goes dark.</li>
<li>Prevention: unplug USB first, then check there is no accidental 3V3↔GND jumper.</li>
</ul>

<h2>3.3V vs 5V — two voltage “languages”</h2>
<p>On an ESP32-DevKitC-1 you will see pins labeled <strong>3V3</strong> and often <strong>5V</strong>. They are <em>not</em> the same.</p>
{$volt}
<ul>
<li><strong>GPIO</strong> (signal pins) = <strong>3.3V</strong> logic. Do not feed 5V into them.</li>
<li><strong>5V</strong> usually comes from USB — for some modules that need 5V (e.g. some relays), with rules later in actuator modules.</li>
<li><strong>GND</strong> = shared ground. External modules must share GND with the board.</li>
</ul>
<p><strong>Analogy:</strong> 3.3V is like a small bus; 5V is like a truck. A truck on the bus lane crashes. Do not “plug it because the hole fits”.</p>

<h2>Polarity &amp; shared ground</h2>
<p>From FS-04: LEDs have a long leg (+) and a short leg (−). Sensors/modules often have <strong>VCC / +</strong>, <strong>GND / −</strong>, and a signal pin. Swapped polarity → the module stays dead or dies.</p>
{$ledPol}
<ul>
<li><strong>Polarity:</strong> + to +, − to − (GND).</li>
<li><strong>Shared ground:</strong> if a sensor sits on the breadboard, connect sensor GND to board GND.</li>
<li>We do not build today — just learn the rules of the game.</li>
</ul>
<p><strong>Analogy:</strong> shared ground is like standing on the same floor so two people can hold hands. Without a common floor, signals “float”.</p>

<h2>USB data vs charge-only — a short reminder</h2>
<p>You saw this in FS-04. We repeat it because it often becomes “missing port” when uploads start in FS-06.</p>
{$usb}
<p><strong>Tip:</strong> cheap cables are often charge-only. If the computer later does not see a COM port, suspect the cable first — not that “your chip is broken”.</p>

<h2>Golden habit — unplug USB first</h2>
<p><strong>Hot-plug</strong> = changing jumpers / poking wires while the board is still on USB (still powered). That is a dangerous habit for newcomers.</p>
{$unplug}
<ol>
<li>Unplug USB from the board (or from the computer).</li>
<li>Change jumpers / parts calmly.</li>
<li>Check once more (3V3 not touching GND, polarity OK).</li>
<li>Only then plug USB back in.</li>
</ol>
<p><strong>Analogy:</strong> like changing remote batteries — you remove them first; you do not yank them while holding a button.</p>

{$mm}

<h2 id="fsiot-safety-checklist">Practice — 10-point checklist before power ON</h2>
<p>This is the checklist you will reuse in later modules. Below is an <strong>interactive checklist</strong>: tick each habit you already understand. A paper version (marker copy) stays available. Target: <strong>10/10 checked</strong>.</p>
<ul id="fsiot-safety-checklist-items">
<li>I will unplug USB before changing jumpers</li>
<li>I know a short = 3V3 (or 5V) tied straight to GND is dangerous</li>
<li>I will not connect 5V to a GPIO pin</li>
<li>I know ESP32 GPIO uses 3.3V logic</li>
<li>I will keep polarity (+/−) when wiring LEDs/modules</li>
<li>I understand external modules need shared ground (GND to GND)</li>
<li>I use / will find a USB data cable (not charge-only)</li>
<li>My desk is dry; I do not work on a wet floor</li>
<li>I will not “plug and hope” without a pin plan</li>
<li>I will re-read this checklist before the next wiring practice</li>
</ul>
<p><strong>How to test:</strong> complete the interactive checklist in the browser. Optional: write the 10 points on paper and keep them on the desk. No commands to run. No USB plug-in for upload yet.</p>

<h2>Common mistakes</h2>
<ul>
<li><strong>Hot-plugging randomly.</strong> Changing wires while USB is plugged in → short risk &amp; bent pins.</li>
<li><strong>Feeding 5V into a GPIO pin.</strong> ESP32 logic = 3.3V.</li>
<li><strong>Accidental 3V3–GND jumper.</strong> Visual check before plugging USB.</li>
<li><strong>Charge-only USB cable.</strong> Symptom in FS-06: port does not appear.</li>
<li><strong>Assuming “if the hole fits, it is safe”.</strong> Breadboards and pins have rules.</li>
<li><strong>Jumping straight to measuring / wiring without a checklist.</strong> Multimeter comes in FS-07; lit LED practice in FS-09.</li>
<li><strong>Treating old ESP32 series articles as prerequisites.</strong> This path stands alone from zero.</li>
</ul>

<h2>Continue learning</h2>
<p>After FS-05, the natural next step is <strong>FS-06 — computer ready: USB driver + Arduino IDE from zero</strong>. That article is not linked here until the module is ready.</p>
<p>Also bookmark the <a href="/belajar/fullstack-iot">Full Stack IoT path page</a> as the official entry.</p>

<h2>Conclusion</h2>
<p>In <strong>#75 (this article)</strong> you understand short circuits, 3.3V vs 5V, polarity &amp; shared ground, the USB-data reminder, and the <strong>unplug USB first</strong> habit. The 10-point checklist is ready for later modules — still no powered wiring.</p>
<p><strong>In short:</strong> if you can say “unplug first, check 3V3/GND, no 5V into GPIO”, FS-05 is done. Continue to computer setup in FS-06 when that module publishes.</p>
HTML;
    }
}
