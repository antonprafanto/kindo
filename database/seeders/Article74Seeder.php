<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article74Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first()
            ?? Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $iotCat) {
            throw new \RuntimeException('User atau kategori iot-smart-device/esp32-arduino tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-buka-kotak-kit';

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
                'title'              => 'Buka kotak: kenali setiap komponen kit',
                'title_en'           => 'Open the box: meet every kit part',
                'excerpt'            => 'FS-04 / #74: Kenali board, breadboard, LED, resistor, sensor, relay + checklist kit. Belum wiring menyala.',
                'excerpt_en'         => 'FS-04 / #74: Meet the board, breadboard, LED, resistor, sensor, relay + kit checklist. No powered wiring yet.',
                'body'               => $this->body(),
                'body_en'            => $this->bodyEn(),
                'status'             => 'draft',
                'is_featured'        => false,
                'published_at'       => null,
                'seo_title'          => 'Kenali Komponen Kit ESP32 — Full Stack IoT #74',
                'seo_title_en'       => 'Meet Your ESP32 Kit Parts — Full Stack IoT #74',
                'seo_description'    => 'Buka kotak kit Full Stack IoT: ESP32-DevKitC-1, breadboard, jumper, LED, resistor, DHT22, relay. Checklist + belanja awam. Modul FS-04.',
                'seo_description_en' => 'Open the Full Stack IoT kit: ESP32-DevKitC-1, breadboard, jumpers, LED, resistor, DHT22, relay. Checklist + beginner shopping. Module FS-04.',
            ]
        );

        if ($article->published_at !== null || $article->status !== 'draft') {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['fullstack-iot', 'iot', 'esp32'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel #74 / FS-04 tersimpan sebagai DRAFT: '.$article->title);
    }

    private function kitPhoto(
        string $file,
        string $alt,
        string $captionLead,
        string $commonsUrl,
        string $credit,
        string $license,
        string $sourceLabel = 'Sumber gambar'
    ): string {
        return <<<HTML
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/{$file}" alt="{$alt}" loading="lazy" style="width:100%;height:auto;max-height:420px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    {$captionLead}
    <br>{$sourceLabel}: <a href="{$commonsUrl}" rel="noopener noreferrer" target="_blank">{$credit}</a> · Wikimedia Commons ({$license}).
  </figcaption>
</figure>
HTML;
    }

    private function kitPhotosId(): array
    {
        return [
            'breadboard' => $this->kitPhoto(
                'kit-breadboard.jpg',
                'Foto breadboard solderless putih dengan lubang dan power rail',
                'Contoh <strong>breadboard</strong> (ukuran di foto boleh setengah; di kit kita pakai yang lebih panjang ~830 lubang). Bentuknya mirip: putih, banyak lubang, garis merah/biru di tepi.',
                'https://commons.wikimedia.org/wiki/File:400_points_breadboard.jpg',
                'oomlout — 400 points breadboard',
                'CC BY-SA 2.0'
            ),
            'jumper' => $this->kitPhoto(
                'kit-jumper-wires.jpg',
                'Foto beberapa kabel jumper warna-warni untuk breadboard',
                'Contoh <strong>kabel jumper</strong> (ujung kaku untuk menusuk lubang breadboard). Set M‑M dan M‑F di toko biasanya lebih banyak warna.',
                'https://commons.wikimedia.org/wiki/File:A_few_Jumper_Wires.jpg',
                'oomlout — A few Jumper Wires',
                'CC BY-SA 2.0'
            ),
            'led' => $this->kitPhoto(
                'kit-led-5mm.jpg',
                'Foto LED 5mm putih dengan dua kaki berbeda panjang',
                'Contoh <strong>LED 5mm</strong> asli. Perhatikan dua kaki: biasanya satu lebih panjang (= +).',
                'https://commons.wikimedia.org/wiki/File:5mm_LED_Light-emitting_diode_White_1480334_5_6HDR_Enhancer.jpg',
                'Nevit Dilmen — 5mm LED',
                'CC BY-SA 3.0'
            ),
            'resistor' => $this->kitPhoto(
                'kit-resistor.jpg',
                'Foto resistor dengan badan silinder dan kaki kawat',
                'Contoh <strong>resistor</strong> (bentuk silinder + kaki kawat). Di kit kamu biasanya ada cincin warna; nilai 220Ω/330Ω/10kΩ ditulis di kemasan atau dibaca dari tabel warna.',
                'https://commons.wikimedia.org/wiki/File:Carbon_Composition_Resistor_4K7.png',
                'YoktoBit — Carbon composition resistor',
                'CC BY-SA 4.0'
            ),
            'button' => $this->kitPhoto(
                'kit-tactile-button.jpg',
                'Foto tombol tactile kecil untuk breadboard',
                'Contoh <strong>tombol tactile</strong> (tekan-lepas). Bentuk kotak kecil dengan tombol di tengah.',
                'https://commons.wikimedia.org/wiki/File:BUTA-06-X-STAN-01.jpg',
                'oomlout — 6 mm tactile pushbutton',
                'CC BY-SA 2.0'
            ),
            'dht22' => $this->kitPhoto(
                'kit-dht22.jpg',
                'Foto modul sensor DHT22 suhu dan kelembapan',
                'Contoh modul <strong>DHT22</strong> (kotak biru/putih di PCB). DHT11 bentuknya mirip — bedanya ketelitian.',
                'https://commons.wikimedia.org/wiki/File:DHT22_digital_temperature_and_humidity_sensor_module_pcb.jpg',
                'Suyash Dwivedi — DHT22 module',
                'CC BY-SA 4.0'
            ),
            'ldr' => $this->kitPhoto(
                'kit-ldr.jpg',
                'Foto LDR photoresistor berbentuk bulat dengan kisi',
                'Contoh <strong>LDR</strong> (photoresistor): permukaan bulat dengan kisi — “mata” cahaya.',
                'https://commons.wikimedia.org/wiki/File:LDR_1480405_6_7_HDR_Enhancer_1.jpg',
                'Nevit Dilmen — LDR / photoresistor',
                'CC BY-SA 3.0'
            ),
            'relay' => $this->kitPhoto(
                'kit-relay-5v.jpg',
                'Foto modul relay 1 channel 5V dengan terminal sekrup',
                'Contoh <strong>modul relay 1 channel 5V</strong> (kotak biru + terminal sekrup). Di jalur Core: beban DC kecil saja — bukan AC 220V.',
                'https://commons.wikimedia.org/wiki/File:SRD-05VDC-SL-C_5V_one-channel_relay_module.jpg',
                'Suyash Dwivedi — 5V one-channel relay module',
                'CC BY-SA 4.0'
            ),
            'multimeter' => $this->kitPhoto(
                'kit-multimeter.jpg',
                'Foto multimeter digital dengan layar dan probe',
                'Contoh <strong>multimeter digital</strong> (layar + dua probe). Cara pakainya dipelajari di FS-07 — hari ini cukup kenali bentuknya.',
                'https://commons.wikimedia.org/wiki/File:2017_Cyfrowy_miernik_uniwersalny.jpg',
                'Jacek Halicki — digital multimeter',
                'CC BY-SA 4.0'
            ),
        ];
    }

    private function kitPhotosEn(): array
    {
        return [
            'breadboard' => $this->kitPhoto(
                'kit-breadboard.jpg',
                'Photo of a white solderless breadboard with holes and power rails',
                'Example <strong>breadboard</strong> (the photo may be a half-size board; our kit uses a longer ~830-hole board). Same idea: white body, many holes, red/blue lines on the edges.',
                'https://commons.wikimedia.org/wiki/File:400_points_breadboard.jpg',
                'oomlout — 400 points breadboard',
                'CC BY-SA 2.0',
                'Image source'
            ),
            'jumper' => $this->kitPhoto(
                'kit-jumper-wires.jpg',
                'Photo of several colorful jumper wires for breadboards',
                'Example <strong>jumper wires</strong> (stiff tips for breadboard holes). Store packs of M‑M and M‑F usually include more colors.',
                'https://commons.wikimedia.org/wiki/File:A_few_Jumper_Wires.jpg',
                'oomlout — A few Jumper Wires',
                'CC BY-SA 2.0',
                'Image source'
            ),
            'led' => $this->kitPhoto(
                'kit-led-5mm.jpg',
                'Photo of a white 5mm LED with two legs of different lengths',
                'Example real <strong>5mm LED</strong>. Notice the two legs: usually one is longer (= +).',
                'https://commons.wikimedia.org/wiki/File:5mm_LED_Light-emitting_diode_White_1480334_5_6HDR_Enhancer.jpg',
                'Nevit Dilmen — 5mm LED',
                'CC BY-SA 3.0',
                'Image source'
            ),
            'resistor' => $this->kitPhoto(
                'kit-resistor.jpg',
                'Photo of a cylindrical resistor with wire leads',
                'Example <strong>resistor</strong> (cylinder body + wire leads). Your kit parts usually have color bands; 220Ω/330Ω/10kΩ values are on the pack or read from a color chart.',
                'https://commons.wikimedia.org/wiki/File:Carbon_Composition_Resistor_4K7.png',
                'YoktoBit — Carbon composition resistor',
                'CC BY-SA 4.0',
                'Image source'
            ),
            'button' => $this->kitPhoto(
                'kit-tactile-button.jpg',
                'Photo of a small tactile pushbutton for breadboards',
                'Example <strong>tactile button</strong> (press-release). Small square body with a button in the middle.',
                'https://commons.wikimedia.org/wiki/File:BUTA-06-X-STAN-01.jpg',
                'oomlout — 6 mm tactile pushbutton',
                'CC BY-SA 2.0',
                'Image source'
            ),
            'dht22' => $this->kitPhoto(
                'kit-dht22.jpg',
                'Photo of a DHT22 temperature and humidity sensor module',
                'Example <strong>DHT22</strong> module (blue/white sensor on a PCB). DHT11 looks similar — accuracy differs.',
                'https://commons.wikimedia.org/wiki/File:DHT22_digital_temperature_and_humidity_sensor_module_pcb.jpg',
                'Suyash Dwivedi — DHT22 module',
                'CC BY-SA 4.0',
                'Image source'
            ),
            'ldr' => $this->kitPhoto(
                'kit-ldr.jpg',
                'Photo of a round LDR photoresistor with a grid face',
                'Example <strong>LDR</strong> (photoresistor): round face with a grid — a light “eye”.',
                'https://commons.wikimedia.org/wiki/File:LDR_1480405_6_7_HDR_Enhancer_1.jpg',
                'Nevit Dilmen — LDR / photoresistor',
                'CC BY-SA 3.0',
                'Image source'
            ),
            'relay' => $this->kitPhoto(
                'kit-relay-5v.jpg',
                'Photo of a 5V one-channel relay module with screw terminals',
                'Example <strong>1-channel 5V relay module</strong> (blue cube + screw terminals). On Core: small DC loads only — not AC 220V.',
                'https://commons.wikimedia.org/wiki/File:SRD-05VDC-SL-C_5V_one-channel_relay_module.jpg',
                'Suyash Dwivedi — 5V one-channel relay module',
                'CC BY-SA 4.0',
                'Image source'
            ),
            'multimeter' => $this->kitPhoto(
                'kit-multimeter.jpg',
                'Photo of a digital multimeter with a display and probes',
                'Example <strong>digital multimeter</strong> (display + two probes). How to use it comes in FS-07 — today just recognize the shape.',
                'https://commons.wikimedia.org/wiki/File:2017_Cyfrowy_miernik_uniwersalny.jpg',
                'Jacek Halicki — digital multimeter',
                'CC BY-SA 4.0',
                'Image source'
            ),
        ];
    }

    private function overviewFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/esp32-devkitc-overview.jpg" width="1200" height="519" alt="Foto overview board ESP32-DevKitC dari dokumentasi Espressif" loading="lazy" style="width:100%;height:auto;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    Overview board keluarga <strong>ESP32-DevKitC</strong> (sama keluarga dengan <strong>DevKitC-1</strong> yang kita pakai). Hari ini cukup kenali bentuknya — belum menyambung kabel untuk menyalakan rangkaian.
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
    Overview of the <strong>ESP32-DevKitC</strong> family (same family as the <strong>DevKitC-1</strong> we use). Today, just recognize the shape — no powered wiring yet.
    <br>Image source: <a href="https://docs.espressif.com/projects/esp-dev-kits/en/latest/esp32/esp32-devkitc/user_guide.html" rel="noopener noreferrer" target="_blank">Espressif Systems — ESP32-DevKitC User Guide</a> (official docs).
  </figcaption>
</figure>
HTML;
    }

    private function pinoutFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/esp32-devkitc-1-pinlayout.jpg" width="1200" height="800" alt="Diagram pinout resmi ESP32-DevKitC-1 dari dokumentasi Espressif" loading="lazy" style="width:100%;height:auto;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    Pinout resmi <strong>ESP32-DevKitC-1</strong>. Cocokkan tulisan di board dengan gambar ini. <strong>Jangan hafal semua nomor GPIO hari ini</strong> — cukup tahu di mana 3V3, 5V, GND, EN, dan BOOT.
    <br>Sumber gambar: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/boards/ESP32-DevKitC-1.html" rel="noopener noreferrer" target="_blank">Espressif / Arduino-ESP32 — ESP32-DevKitC-1</a> (dokumen resmi).
  </figcaption>
</figure>
HTML;
    }

    private function pinoutFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/esp32-devkitc-1-pinlayout.jpg" width="1200" height="800" alt="Official ESP32-DevKitC-1 pinout diagram from Espressif documentation" loading="lazy" style="width:100%;height:auto;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    Official <strong>ESP32-DevKitC-1</strong> pinout. Match the printed labels on your board to this picture. <strong>Do not memorize every GPIO number today</strong> — just know where 3V3, 5V, GND, EN, and BOOT are.
    <br>Image source: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/boards/ESP32-DevKitC-1.html" rel="noopener noreferrer" target="_blank">Espressif / Arduino-ESP32 — ESP32-DevKitC-1</a> (official docs).
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
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Diagram dalaman breadboard sederhana (buatan Koding Indonesia). Jangan mengira semua lubang saling nyambung.</figcaption>
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
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Simple breadboard internals diagram (by Koding Indonesia). Do not assume every hole is connected.</figcaption>
</figure>
SVG;
    }

    private function ledSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="LED: kaki panjang anode dan kaki pendek katode" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 250" width="100%" height="auto" role="img" aria-label="LED polarity">
  <text x="200" y="28" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700">LED 5mm</text>
  <circle cx="200" cy="85" r="48" fill="#FFEB3B" stroke="#1a1a1a" stroke-width="2.5"/>
  <rect x="152" y="85" width="96" height="26" fill="#FFEB3B" stroke="#1a1a1a" stroke-width="2.5"/>
  <line x1="165" y1="111" x2="165" y2="165" stroke="#1a1a1a" stroke-width="6" stroke-linecap="round"/>
  <line x1="235" y1="111" x2="235" y2="148" stroke="#1a1a1a" stroke-width="6" stroke-linecap="round"/>
  <text x="95" y="185" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">← kaki panjang</text>
  <text x="95" y="208" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#2E7D32">= + (anode)</text>
  <text x="305" y="185" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">kaki pendek →</text>
  <text x="305" y="208" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#C62828">= − (katode)</text>
  <rect x="400" y="50" width="290" height="130" fill="#E8F5E9" stroke="#1a1a1a" stroke-width="2" rx="6"/>
  <text x="545" y="95" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700">Resistor</text>
  <text x="545" y="125" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" fill="#4A5568">220Ω / 330Ω = “rem” arus</text>
  <text x="545" y="152" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" fill="#4A5568">agar LED tidak “terbakar”</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Polaritas LED + peran resistor (buatan Koding Indonesia). Menyambung supaya menyala baru di modul nanti — hari ini cukup kenali bentuknya.</figcaption>
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
  <text x="545" y="95" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700">Resistor</text>
  <text x="545" y="125" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" fill="#4A5568">220Ω / 330Ω = current “brake”</text>
  <text x="545" y="152" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" fill="#4A5568">so the LED does not burn out</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">LED polarity + resistor role (by Koding Indonesia). Powered wiring comes in later modules — today just recognize the shapes.</figcaption>
</figure>
SVG;
    }

    private function usbSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Perbedaan kabel USB data dan charge-only" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 150" width="100%" height="auto" role="img" aria-label="USB data vs charge">
  <rect x="30" y="30" width="300" height="90" fill="#E8F5E9" stroke="#1a1a1a" stroke-width="2" rx="4"/>
  <text x="180" y="65" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">USB data ✓</text>
  <text x="180" y="90" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Bisa charge + kirim program</text>
  <text x="180" y="108" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Ini yang kita butuhkan</text>
  <rect x="390" y="30" width="300" height="90" fill="#FFEBEE" stroke="#1a1a1a" stroke-width="2" rx="4"/>
  <text x="540" y="65" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">Charge-only ✗</text>
  <text x="540" y="90" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Hanya isi daya</text>
  <text x="540" y="108" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Port COM sering “hilang”</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Tips belanja kabel USB (buatan Koding Indonesia). Tes upload baru di FS-06.</figcaption>
</figure>
SVG;
    }

    private function usbSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Difference between USB data cable and charge-only cable" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 150" width="100%" height="auto" role="img" aria-label="USB data vs charge">
  <rect x="30" y="30" width="300" height="90" fill="#E8F5E9" stroke="#1a1a1a" stroke-width="2" rx="4"/>
  <text x="180" y="65" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">USB data ✓</text>
  <text x="180" y="90" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Can charge + send programs</text>
  <text x="180" y="108" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">This is what we need</text>
  <rect x="390" y="30" width="300" height="90" fill="#FFEBEE" stroke="#1a1a1a" stroke-width="2" rx="4"/>
  <text x="540" y="65" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">Charge-only ✗</text>
  <text x="540" y="90" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Power only</text>
  <text x="540" y="108" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">COM port often “missing”</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">USB cable shopping tip (by Koding Indonesia). Upload testing comes in FS-06.</figcaption>
</figure>
SVG;
    }

    private function body(): string
    {
        $overview = $this->overviewFigureId();
        $pinout = $this->pinoutFigureId();
        $bread = $this->breadboardSvgId();
        $led = $this->ledSvgId();
        $usb = $this->usbSvgId();
        $p = $this->kitPhotosId();
        $photoBb = $p['breadboard'];
        $photoJumper = $p['jumper'];
        $photoLed = $p['led'];
        $photoRes = $p['resistor'];
        $photoBtn = $p['button'];
        $photoDht = $p['dht22'];
        $photoLdr = $p['ldr'];
        $photoRelay = $p['relay'];
        $photoMm = $p['multimeter'];

        return <<<HTML
<h2>Pendahuluan — kenapa buka kotak dulu?</h2>
<p>Artikel ini adalah <strong>#74 (ini)</strong> · modul <strong>FS-04</strong> di jalur <em>Full Stack IoT Developer — Dari Nol</em>. Di FS-01 kamu paham IoT itu apa; di FS-02 kamu punya peta; di <strong>FS-03</strong> kamu punya kamus mini. Hari ini kita <strong>buka kotak</strong>: kenali benda di meja sebelum mengalirkan listrik atau menulis program.</p>
<p><strong>Awam:</strong> seperti memasak — sebelum nyalakan kompor, kamu susun bahan &amp; sebut namanya. Salah sebut “garam” vs “gula” lebih bahaya kalau sudah panas.</p>
<p><strong>Prasyarat:</strong> ide dari FS-01–FS-03. Belum wajib punya semua barang hari ini — kalau belum beli, pakai daftar belanja di bawah sebagai panduan.</p>

<h2>Persiapan — alat yang kamu buka hari ini</h2>
<p><strong>Alat yang dipakai di artikel ini</strong> (belum Arduino IDE, belum driver USB, belum terminal, belum Laragon):</p>
<ul>
<li><strong>Browser</strong> — membaca artikel + mengerjakan <strong>checklist interaktif</strong> di akhir (Chrome, Edge, Firefox, atau browser HP).</li>
<li><strong>Kit fisik di meja</strong> (atau daftar belanja jika belum lengkap) — board, breadboard, kabel, LED, resistor, sensor, relay, multimeter, kabel USB.</li>
<li><strong>Kertas label + spidol</strong> (opsional) — tulis nama tiap benda; foto untuk catatanmu sendiri.</li>
</ul>
<p><strong>Tidak ada perintah sintaks hari ini.</strong> Tidak ada baris kode, tidak ada <code>php artisan</code>, tidak ada sketch Arduino, tidak ada upload. Cara “menguji” di FS-04 = <strong>susun &amp; sebut nama</strong> + centang checklist (browser atau kertas). Belum menyalakan rangkaian latihan.</p>
<p><strong>Awam:</strong> kalau kamu hanya punya browser hari ini, tetap bisa belajar dari foto &amp; daftar belanja. Kit fisik bisa menyusul sebelum FS-06.</p>

<h2>Board resmi — ESP32-DevKitC-1</h2>
<p>Board resmi jalur ini: <strong>ESP32-DevKitC-1</strong>. Di pasaran banyak board mirip berlabel “DevKitC” / DOIT — <strong>wajib cocokkan tulisan di board</strong> dengan diagram resmi di bawah, jangan hafal “board tetangga”.</p>
{$overview}
<p>Yang perlu kamu temukan hari ini (dengan jari, tanpa kabel program):</p>
<ul>
<li><strong>Modul hitam</strong> di tengah (chip + antena Wi‑Fi di dalamnya).</li>
<li><strong>Lubang pin</strong> kiri–kanan (tempat jumper nanti).</li>
<li>Tombol <strong>EN</strong> (reset) dan <strong>BOOT</strong> (mode unduh — detail di modul upload).</li>
<li>Soket <strong>USB</strong> (micro-USB atau USB‑C, tergantung edisi board).</li>
</ul>
{$pinout}
<p><strong>Awam:</strong> pinout seperti peta kursi bioskop. Hari ini cukup tahu di mana pintu masuk (USB) dan kursi “aman” (3V3 / GND). Kursi GPIO detail datang saat BUILDER.</p>

<h2>Breadboard — meja percobaan berlubang</h2>
<p>Breadboard adalah papan berlubang untuk mencoba rangkaian <strong>tanpa solder</strong>. Kunci awam: <strong>tidak semua lubang saling nyambung</strong>.</p>
{$photoBb}
{$bread}
<ul>
<li><strong>Power rail</strong> (garis +/− di tepi) — biasanya sepanjang papan.</li>
<li><strong>Satu baris kecil</strong> (misalnya 5 lubang) — saling nyambung.</li>
<li><strong>Parit tengah</strong> — memisahkan sisi kiri dan kanan (berguna saat menancapkan board).</li>
</ul>
<p><strong>Awam:</strong> bayangkan kursi bis: satu baris kursi saling “ngobrol”; baris di belakang tidak otomatis ngobrol kecuali kamu sambungkan dengan jumper.</p>

<h2>Jumper, LED, resistor</h2>
<p><strong>Jumper</strong> = kabel pendek Male‑Male (M‑M) atau Male‑Female (M‑F). Warnanya hanya penanda; yang penting ujungnya masuk lubang dengan kencang.</p>
{$photoJumper}
{$photoLed}
{$led}
{$photoRes}
{$photoBtn}
<ul>
<li><strong>LED:</strong> kaki <strong>panjang = anode (+)</strong>, kaki <strong>pendek = katode (−)</strong>. Tertukar → sering tidak menyala (nanti saat latihan LED).</li>
<li><strong>Resistor 220Ω / 330Ω:</strong> “rem” arus untuk LED. Baca warna sederhana cukup: bandingkan dengan tabel di kemasan / aplikasi pembaca warna — detail hitung Ohm di FS-08.</li>
<li><strong>Resistor 10kΩ:</strong> sering dipakai untuk tombol / pembagi tegangan (LDR) nanti.</li>
<li><strong>Tombol tactile:</strong> saklar kecil tekan-lepas.</li>
</ul>
<p><strong>Awam:</strong> LED tanpa resistor seperti keran tanpa pembatas — bisa “kebanyakan” dan rusak. Kita belum merakit hari ini; cukup kenali bentuknya.</p>

<h2>Sensor vs relay — indra vs otot</h2>
<p>Di kamus FS-03: sensor ≈ indra, aktuator ≈ otot. Di kotak kit:</p>
{$photoDht}
{$photoLdr}
{$photoRelay}
<ul>
<li><strong>DHT22</strong> — sensor suhu &amp; kelembapan (indra). Kalau stok kosong, <strong>DHT11 boleh sementara</strong> — bedanya ketelitian &amp; rentang; sebut saja di catatanmu.</li>
<li><strong>LDR</strong> — “mata” cahaya (bersama resistor 10k nanti).</li>
<li><strong>Modul relay 1 channel 5V</strong> — saklar elektronik (otot kecil). Di jalur Core kita pakai untuk <strong>beban DC kecil</strong> saja.</li>
</ul>
<p><strong>Awam:</strong> sensor membaca; relay mengganti saklar. Jangan beli kit “AC 220V / lampu rumah” dulu — itu level lain dan lebih berbahaya.</p>

<h2>Belanja awam — harga &amp; urutan</h2>
<p>Estimasi kasar marketplace Indonesia (Juli 2026 — <strong>update berkala</strong>; harga berubah):</p>
<table>
<thead><tr><th>Item</th><th>Qty</th><th>Kisaran (IDR)</th></tr></thead>
<tbody>
<tr><td>ESP32-DevKitC-1 (atau clone pin-compatible)</td><td>1</td><td>Rp 60.000–120.000</td></tr>
<tr><td>Breadboard 830</td><td>1</td><td>Rp 15.000–35.000</td></tr>
<tr><td>Jumper M‑M &amp; M‑F</td><td>1 set</td><td>Rp 10.000–25.000</td></tr>
<tr><td>LED 5mm</td><td>5</td><td>Rp 5.000–15.000</td></tr>
<tr><td>Resistor 220/330Ω</td><td>10</td><td>Rp 5.000–15.000</td></tr>
<tr><td>Resistor 10kΩ</td><td>10</td><td>Rp 5.000–15.000</td></tr>
<tr><td>Tombol tactile</td><td>2</td><td>Rp 5.000–10.000</td></tr>
<tr><td>DHT22 (atau DHT11 sementara)</td><td>1</td><td>Rp 25.000–50.000</td></tr>
<tr><td>LDR</td><td>1</td><td>Rp 2.000–8.000</td></tr>
<tr><td>Modul relay 1ch 5V</td><td>1</td><td>Rp 10.000–25.000</td></tr>
<tr><td>Kabel USB <strong>data</strong></td><td>1</td><td>Rp 15.000–40.000</td></tr>
<tr><td>Multimeter</td><td>1</td><td>Rp 50.000–150.000</td></tr>
</tbody>
</table>
<p><strong>Total kasar kit wajib di awal:</strong> sekitar <strong>Rp 200.000–450.000</strong> tergantung toko &amp; kualitas. Belanja bertahap OK: minimal board + breadboard + jumper + LED + resistor + USB data sebelum latihan lampu.</p>
{$photoMm}
{$usb}
<ul>
<li>Beli di marketplace lokal; baca ulasan “cable data” / “bukan charge only”.</li>
<li>Komponen tambahan nanti (PIR, servo, OLED, BME280) belanja sebelum akhir fase BUILDER — belum wajib hari ini.</li>
<li><strong>Jangan</strong> beli kit beban AC 220V untuk Core.</li>
</ul>
<p><strong>Awam:</strong> harga murah bukan selalu jebakan — tapi kabel USB murah sering charge-only. Simpan struk &amp; foto barang agar mudah klaim.</p>

<h2 id="fsiot-kit-checklist">Praktik — checklist kit di meja</h2>
<p>Susun komponen di meja. Di bawah ada <strong>checklist interaktif</strong>: centang tiap item yang sudah kamu punya / kenali. Versi kertas (label spidol) tetap tersedia. Target: <strong>semua 12 item kit wajib tercentang</strong> (atau tulis “pesan” di catatan jika menunggu kiriman).</p>
<ul id="fsiot-kit-checklist-items">
<li>ESP32-DevKitC-1 (cocok diagram resmi)</li>
<li>Breadboard 830</li>
<li>Jumper M‑M &amp; M‑F (1 set)</li>
<li>LED 5mm (minimal 5)</li>
<li>Resistor 220Ω atau 330Ω (minimal 10)</li>
<li>Resistor 10kΩ (minimal 10)</li>
<li>Tombol tactile (minimal 2)</li>
<li>DHT22 (atau DHT11 sementara)</li>
<li>LDR</li>
<li>Modul relay 1 channel 5V</li>
<li>Kabel USB data (bukan charge-only)</li>
<li>Multimeter</li>
</ul>
<p><strong>Awam — cara menguji:</strong> kerjakan dulu checklist interaktif di browser. Opsional: tempel label kertas di tiap benda, foto untuk catatan. Tidak perlu menjalankan perintah apa pun. Belum cabut-colok USB untuk upload.</p>

<h2>Kesalahan umum awam</h2>
<ul>
<li><strong>Mengira semua lubang breadboard nyambung.</strong> Hanya baris/rail tertentu — lihat diagram di atas.</li>
<li><strong>Tertukar anode/katode LED.</strong> Panjang = +; pendek = −.</li>
<li><strong>Belanja beban AC 220V terlalu cepat.</strong> Relay Core = beban DC kecil dulu.</li>
<li><strong>Kabel USB charge-only.</strong> Gejala nanti: port tidak muncul di komputer (FS-06).</li>
<li><strong>Hafal pinout penuh sebelum paham 3V3/GND.</strong> Cukup kenali dulu; GPIO detail menyusul.</li>
<li><strong>Langsung nyambung listrik “coba-coba”.</strong> Modul berikutnya (FS-05) adalah kebiasaan aman sebelum power ON.</li>
<li><strong>Mencampur Seri ESP32 lama sebagai prasyarat.</strong> Jalur ini mandiri dari nol. Artikel terkait di bawah halaman bisa dari topik lama; itu bukan syarat FS-04.</li>
</ul>

<h2>Lanjut belajar</h2>
<p>Setelah FS-04, langkah alami berikutnya adalah <strong>FS-05 — keselamatan &amp; kebiasaan sebelum mengalirkan listrik</strong> (short circuit, 3.3V vs 5V, checklist cabut USB dulu). Artikel itu belum dilink di sini sampai modulnya siap.</p>
<p>Simpan juga <a href="/belajar/fullstack-iot">halaman jalur Full Stack IoT</a> sebagai pintu masuk resmi.</p>

<h2>Kesimpulan</h2>
<p>Di <strong>#74 (ini)</strong> kamu sudah bisa menyebut nama benda di kit, membedakan indra vs otot, membaca pinout secara awam, dan mencentang checklist 12 item. Board resmi tetap <strong>ESP32-DevKitC-1</strong> — masih pengenalan, belum wiring menyala.</p>
<p><strong>Awam:</strong> kalau kamu bisa menunjuk board, breadboard, LED, resistor, sensor, dan relay sambil bilang “ini apa”, FS-04 selesai. Lanjut ke kebiasaan aman di FS-05 saat modulnya terbit.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $overview = $this->overviewFigureEn();
        $pinout = $this->pinoutFigureEn();
        $bread = $this->breadboardSvgEn();
        $led = $this->ledSvgEn();
        $usb = $this->usbSvgEn();
        $p = $this->kitPhotosEn();
        $photoBb = $p['breadboard'];
        $photoJumper = $p['jumper'];
        $photoLed = $p['led'];
        $photoRes = $p['resistor'];
        $photoBtn = $p['button'];
        $photoDht = $p['dht22'];
        $photoLdr = $p['ldr'];
        $photoRelay = $p['relay'];
        $photoMm = $p['multimeter'];

        return <<<HTML
<h2>Introduction — why open the box first?</h2>
<p>This article is <strong>#74 (this article)</strong> · module <strong>FS-04</strong> on the <em>Full Stack IoT Developer — From Zero</em> path. In FS-01 you learned what IoT is; in FS-02 you got the map; in <strong>FS-03</strong> you got a mini glossary. Today we <strong>open the box</strong>: name the parts on the desk before power or code.</p>
<p><strong>Beginner:</strong> like cooking — before you turn on the stove, lay out ingredients and name them. Mixing salt and sugar is worse once things are hot.</p>
<p><strong>Prerequisites:</strong> ideas from FS-01–FS-03. You do not need every part today — if you have not bought yet, use the shopping list below as a guide.</p>

<h2>Preparation — tools you open today</h2>
<p><strong>Tools used in this article</strong> (no Arduino IDE yet, no USB driver, no terminal, no Laragon):</p>
<ul>
<li><strong>Browser</strong> — read the article + complete the <strong>interactive checklist</strong> at the end (Chrome, Edge, Firefox, or a phone browser).</li>
<li><strong>Physical kit on the desk</strong> (or a shopping list if incomplete) — board, breadboard, wires, LED, resistor, sensor, relay, multimeter, USB cable.</li>
<li><strong>Paper labels + marker</strong> (optional) — write each part’s name; photo for your own notes.</li>
</ul>
<p><strong>There is no syntax to run today.</strong> No code lines, no <code>php artisan</code>, no Arduino sketch, no upload. How you “test” in FS-04 = <strong>lay out &amp; name</strong> + tick the checklist (browser or paper). No practice circuit is powered on yet.</p>
<p><strong>Beginner:</strong> if you only have a browser today, you can still learn from photos and the shopping list. The physical kit can arrive before FS-06.</p>

<h2>Official board — ESP32-DevKitC-1</h2>
<p>Official board for this path: <strong>ESP32-DevKitC-1</strong>. Many marketplace boards say “DevKitC” / DOIT-like — <strong>match the printed labels on the board</strong> to the official diagram below; do not memorize a neighbor board.</p>
{$overview}
<p>Find these today (with your finger, no programming cable required):</p>
<ul>
<li>The <strong>black module</strong> in the middle (chip + Wi‑Fi antenna inside).</li>
<li><strong>Pin headers</strong> left and right (where jumpers go later).</li>
<li><strong>EN</strong> (reset) and <strong>BOOT</strong> buttons (download mode — detail in the upload module).</li>
<li>The <strong>USB</strong> socket (micro-USB or USB‑C, depending on board edition).</li>
</ul>
{$pinout}
<p><strong>Beginner:</strong> a pinout is like a cinema seating map. Today, know the entrance (USB) and safe seats (3V3 / GND). Detailed GPIO seats come in BUILDER.</p>

<h2>Breadboard — a holey practice table</h2>
<p>A breadboard lets you try circuits <strong>without soldering</strong>. Beginner key: <strong>not every hole is connected</strong>.</p>
{$photoBb}
{$bread}
<ul>
<li><strong>Power rails</strong> (+/− strips on the sides) — usually run the length of the board.</li>
<li><strong>One short row</strong> (e.g. 5 holes) — connected together.</li>
<li><strong>Center trench</strong> — separates left and right halves (useful when seating the board).</li>
</ul>
<p><strong>Beginner:</strong> picture bus seats: one row of seats “talks”; the row behind does not unless you bridge them with a jumper.</p>

<h2>Jumpers, LEDs, resistors</h2>
<p><strong>Jumpers</strong> = short Male‑Male (M‑M) or Male‑Female (M‑F) wires. Color is just a label; what matters is a firm fit in the hole.</p>
{$photoJumper}
{$photoLed}
{$led}
{$photoRes}
{$photoBtn}
<ul>
<li><strong>LED:</strong> <strong>long leg = anode (+)</strong>, <strong>short leg = cathode (−)</strong>. Swapped → often stays dark (later, in the LED lesson).</li>
<li><strong>220Ω / 330Ω resistor:</strong> a current “brake” for the LED. Simple color reading is enough: compare with the pack chart / a color-reader app — Ohm math comes in FS-08.</li>
<li><strong>10kΩ resistor:</strong> often used later for buttons / LDR voltage dividers.</li>
<li><strong>Tactile button:</strong> a small press-release switch.</li>
</ul>
<p><strong>Beginner:</strong> an LED without a resistor is like a tap without a limiter — too much flow can kill it. We do not build today; just recognize the shapes.</p>

<h2>Sensor vs relay — sense vs muscle</h2>
<p>From the FS-03 glossary: sensor ≈ sense, actuator ≈ muscle. In the kit box:</p>
{$photoDht}
{$photoLdr}
{$photoRelay}
<ul>
<li><strong>DHT22</strong> — temperature &amp; humidity sensor (sense). If stock is empty, <strong>DHT11 is OK temporarily</strong> — accuracy/range differ; note it in your notes.</li>
<li><strong>LDR</strong> — a light “eye” (with a 10k resistor later).</li>
<li><strong>1-channel 5V relay module</strong> — an electronic switch (small muscle). On Core we use it for <strong>small DC loads</strong> only.</li>
</ul>
<p><strong>Beginner:</strong> sensors read; relays switch. Do not buy “AC 220V / house lamp” kits yet — that is another level and more dangerous.</p>

<h2>Beginner shopping — price &amp; order</h2>
<p>Rough Indonesian marketplace estimates (July 2026 — <strong>update periodically</strong>; prices move):</p>
<table>
<thead><tr><th>Item</th><th>Qty</th><th>Range (IDR)</th></tr></thead>
<tbody>
<tr><td>ESP32-DevKitC-1 (or pin-compatible clone)</td><td>1</td><td>Rp 60,000–120,000</td></tr>
<tr><td>Breadboard 830</td><td>1</td><td>Rp 15,000–35,000</td></tr>
<tr><td>Jumper M‑M &amp; M‑F</td><td>1 set</td><td>Rp 10,000–25,000</td></tr>
<tr><td>5mm LED</td><td>5</td><td>Rp 5,000–15,000</td></tr>
<tr><td>220/330Ω resistor</td><td>10</td><td>Rp 5,000–15,000</td></tr>
<tr><td>10kΩ resistor</td><td>10</td><td>Rp 5,000–15,000</td></tr>
<tr><td>Tactile button</td><td>2</td><td>Rp 5,000–10,000</td></tr>
<tr><td>DHT22 (or temporary DHT11)</td><td>1</td><td>Rp 25,000–50,000</td></tr>
<tr><td>LDR</td><td>1</td><td>Rp 2,000–8,000</td></tr>
<tr><td>1ch 5V relay module</td><td>1</td><td>Rp 10,000–25,000</td></tr>
<tr><td><strong>Data</strong> USB cable</td><td>1</td><td>Rp 15,000–40,000</td></tr>
<tr><td>Multimeter</td><td>1</td><td>Rp 50,000–150,000</td></tr>
</tbody>
</table>
<p><strong>Rough required starter kit total:</strong> about <strong>Rp 200,000–450,000</strong> depending on shop &amp; quality. Staged buying is fine: at least board + breadboard + jumpers + LED + resistor + data USB before the first lamp practice.</p>
{$photoMm}
{$usb}
<ul>
<li>Buy on local marketplaces; read reviews for “data cable” / “not charge only”.</li>
<li>Extra parts later (PIR, servo, OLED, BME280) come before the end of BUILDER — not required today.</li>
<li><strong>Do not</strong> buy AC 220V load kits for Core.</li>
</ul>
<p><strong>Beginner:</strong> cheap is not always a trap — but cheap USB cables are often charge-only. Keep receipts and photos for easy claims.</p>

<h2 id="fsiot-kit-checklist">Practice — kit checklist on the desk</h2>
<p>Lay the parts on the desk. Below is an <strong>interactive checklist</strong>: tick each item you already own / recognize. A paper version (marker labels) stays available. Target: <strong>all 12 required kit items checked</strong> (or write “ordered” in your notes if waiting on shipping).</p>
<ul id="fsiot-kit-checklist-items">
<li>ESP32-DevKitC-1 (matches official diagram)</li>
<li>Breadboard 830</li>
<li>Jumper M‑M &amp; M‑F (1 set)</li>
<li>5mm LED (at least 5)</li>
<li>220Ω or 330Ω resistor (at least 10)</li>
<li>10kΩ resistor (at least 10)</li>
<li>Tactile button (at least 2)</li>
<li>DHT22 (or temporary DHT11)</li>
<li>LDR</li>
<li>1-channel 5V relay module</li>
<li>USB data cable (not charge-only)</li>
<li>Multimeter</li>
</ul>
<p><strong>Beginner — how to test:</strong> complete the interactive checklist in the browser first. Optional: stick paper labels on each part and take a photo for notes. No commands to run. No USB plug/unplug for upload yet.</p>

<h2>Common beginner mistakes</h2>
<ul>
<li><strong>Assuming every breadboard hole is connected.</strong> Only certain rows/rails — see the diagram above.</li>
<li><strong>Swapping LED anode/cathode.</strong> Long = +; short = −.</li>
<li><strong>Buying AC 220V loads too early.</strong> Core relay = small DC loads first.</li>
<li><strong>Charge-only USB cable.</strong> Later symptom: port does not appear on the computer (FS-06).</li>
<li><strong>Memorizing the full pinout before knowing 3V3/GND.</strong> Recognize first; GPIO detail follows.</li>
<li><strong>Powering “just to try” immediately.</strong> The next module (FS-05) is safe habits before power ON.</li>
<li><strong>Treating old ESP32 series articles as prerequisites.</strong> This path stands alone from zero. Related articles below may be older topics; they are not FS-04 requirements.</li>
</ul>

<h2>Continue learning</h2>
<p>After FS-04, the natural next step is <strong>FS-05 — safety &amp; habits before applying power</strong> (short circuits, 3.3V vs 5V, unplug-USB-first checklist). That article is not linked here until the module is ready.</p>
<p>Also bookmark the <a href="/belajar/fullstack-iot">Full Stack IoT path page</a> as the official entry.</p>

<h2>Conclusion</h2>
<p>In <strong>#74 (this article)</strong> you can name kit parts, tell sense from muscle, read the pinout as a beginner, and tick the 12-item checklist. The official board remains <strong>ESP32-DevKitC-1</strong> — introduction only, no powered wiring yet.</p>
<p><strong>Beginner:</strong> if you can point at the board, breadboard, LED, resistor, sensor, and relay while saying what each is, FS-04 is done. Continue to safe habits in FS-05 when that module publishes.</p>
HTML;
    }
}
