<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article87Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first()
            ?? Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $iotCat) {
            throw new \RuntimeException('User atau kategori iot-smart-device/esp32-arduino tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-peta-pin-devkitc-1';

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
                'title'              => 'Peta pin ESP32-DevKitC-1 — jangan salah cabang',
                'title_en'           => 'ESP32-DevKitC-1 pin map — do not pick the wrong GPIO',
                'excerpt'            => 'FS-17 / #87: cocokkan board di tangan dengan pinout resmi. GPIO aman, input-only, strap, dan IO6–IO11 terlarang.',
                'excerpt_en'         => 'FS-17 / #87: match your board to the official pinout. Safe GPIO, input-only, strap pins, and forbidden IO6–IO11.',
                'body'               => $this->body(),
                'body_en'            => $this->bodyEn(),
                'status'             => 'draft',
                'is_featured'        => false,
                'published_at'       => null,
                'seo_title'          => 'Peta pin DevKitC-1 — Full Stack IoT #87',
                'seo_title_en'       => 'DevKitC-1 pin map — Full Stack IoT #87',
                'seo_description'    => 'Belajar peta pin ESP32-DevKitC-1: GPIO aman, input-only, strap, dan pin flash terlarang. Modul FS-17.',
                'seo_description_en' => 'Learn the ESP32-DevKitC-1 pin map: safe GPIO, input-only, strap, and forbidden flash pins. FS-17 module.',
            ]
        );

        if ($article->published_at !== null || $article->status !== 'draft') {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['fullstack-iot', 'iot', 'esp32'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel #87 / FS-17 tersimpan sebagai DRAFT: '.$article->title);
    }

    private function pinlayoutFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/esp32-devkitc-1-pinlayout.jpg" width="1400" height="955" alt="Peta pin resmi ESP32-DevKitC-1 — silkscreen kiri dan kanan board" loading="eager" style="width:100%;height:auto;max-height:520px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Pinout resmi FSIOT</strong> — ini peta yang kita pakai sepanjang jalur. Bandingkan tulisan di board kamu (silkscreen) dengan diagram ini. Kalau beda, jangan hafal “board tetangga”.
    <br>Sumber gambar: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/boards/ESP32-DevKitC-1.html" rel="noopener noreferrer" target="_blank">Espressif — ESP32-DevKitC-1 (Arduino ESP32 docs)</a> · © Espressif.
  </figcaption>
</figure>
HTML;
    }

    private function pinlayoutFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/esp32-devkitc-1-pinlayout.jpg" width="1400" height="955" alt="Official ESP32-DevKitC-1 pin map — left and right silkscreen" loading="eager" style="width:100%;height:auto;max-height:520px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Official FSIOT pinout</strong> — this is the map we use for the whole path. Match the text on your board (silkscreen) to this diagram. If it differs, do not memorize a “neighbor board”.
    <br>Image source: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/boards/ESP32-DevKitC-1.html" rel="noopener noreferrer" target="_blank">Espressif — ESP32-DevKitC-1 (Arduino ESP32 docs)</a> · © Espressif.
  </figcaption>
</figure>
HTML;
    }

    private function boardFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/esp32-devkitc-overview.jpg" width="1200" height="800" alt="ESP32-DevKitC — USB (6), tombol EN (7), dan baris pin di pinggir" loading="eager" style="width:100%;height:auto;max-height:360px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Orientasi board</strong> — colok USB di label <strong>(6)</strong>. Tombol <strong>EN (7)</strong> = reset. Pin GPIO ada di dua sisi panjang board — baca nomornya di silkscreen, bukan menebak dari foto orang lain.
    <br>Sumber gambar: <a href="https://docs.espressif.com/projects/esp-dev-kits/en/latest/esp32/esp32-devkitc/user_guide.html" rel="noopener noreferrer" target="_blank">Espressif — ESP32-DevKitC user guide</a>.
  </figcaption>
</figure>
HTML;
    }

    private function boardFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/esp32-devkitc-overview.jpg" width="1200" height="800" alt="ESP32-DevKitC — USB (6), EN button (7), and pin rows on the edges" loading="eager" style="width:100%;height:auto;max-height:360px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Board orientation</strong> — plug USB at label <strong>(6)</strong>. <strong>EN (7)</strong> resets the board. GPIO pins sit on both long edges — read the silkscreen numbers; do not guess from someone else’s photo.
    <br>Image source: <a href="https://docs.espressif.com/projects/esp-dev-kits/en/latest/esp32/esp32-devkitc/user_guide.html" rel="noopener noreferrer" target="_blank">Espressif — ESP32-DevKitC user guide</a>.
  </figcaption>
</figure>
HTML;
    }

    private function kindsSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="empat jenis pin GPIO" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 210" width="100%" height="auto" role="img" aria-label="four GPIO kinds">
  <text x="430" y="22" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">Empat jenis pin yang wajib kamu kenali</text>
  <rect x="20" y="40" width="195" height="120" rx="10" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="117" y="72" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700" fill="#1B5E20">1. Aman latihan</text>
  <text x="117" y="100" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#2E7D32">contoh: 2, 4, 13</text>
  <text x="117" y="126" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#2E7D32">27, 25, 26, 21, 22</text>
  <rect x="230" y="40" width="195" height="120" rx="10" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
  <text x="327" y="72" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700" fill="#0D47A1">2. Input-only</text>
  <text x="327" y="100" text-anchor="middle" font-family="Consolas,monospace" font-size="12" fill="#1565C0">34, 35, 36, 39</text>
  <text x="327" y="126" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#1565C0">bisa baca, bukan output</text>
  <rect x="440" y="40" width="195" height="120" rx="10" fill="#FFF8E1" stroke="#F9A825" stroke-width="2.5"/>
  <text x="537" y="72" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700" fill="#F57F17">3. Strap (hati-hati)</text>
  <text x="537" y="100" text-anchor="middle" font-family="Consolas,monospace" font-size="12" fill="#F9A825">0, 2, 12, 15, 5</text>
  <text x="537" y="126" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#F9A825">bisa ganggu boot</text>
  <rect x="650" y="40" width="190" height="120" rx="10" fill="#FFEBEE" stroke="#C62828" stroke-width="2.5"/>
  <text x="745" y="72" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700" fill="#B71C1C">4. TERLARANG</text>
  <text x="745" y="100" text-anchor="middle" font-family="Consolas,monospace" font-size="12" fill="#C62828">IO6 … IO11</text>
  <text x="745" y="126" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#C62828">flash — jangan pakai</text>
  <text x="430" y="190" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#546E7A">Intinya: pilih dari kolom hijau dulu; merah = larangan mutlak di jalur ini</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Intinya:</strong> tidak semua lubang pin “sama saja”. Referensi board: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/boards/ESP32-DevKitC-1.html" rel="noopener noreferrer" target="_blank">Espressif — DevKitC-1</a>.
    <br>Sumber gambar: diagram buatan Koding Indonesia (FS-17).
  </figcaption>
</figure>
SVG;
    }

    private function kindsSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="four kinds of GPIO pins" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 210" width="100%" height="auto" role="img" aria-label="four GPIO kinds">
  <text x="430" y="22" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">Four pin kinds you must recognize</text>
  <rect x="20" y="40" width="195" height="120" rx="10" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="117" y="72" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700" fill="#1B5E20">1. Safe to practice</text>
  <text x="117" y="100" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#2E7D32">e.g. 2, 4, 13</text>
  <text x="117" y="126" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#2E7D32">27, 25, 26, 21, 22</text>
  <rect x="230" y="40" width="195" height="120" rx="10" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
  <text x="327" y="72" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700" fill="#0D47A1">2. Input-only</text>
  <text x="327" y="100" text-anchor="middle" font-family="Consolas,monospace" font-size="12" fill="#1565C0">34, 35, 36, 39</text>
  <text x="327" y="126" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#1565C0">read OK, not output</text>
  <rect x="440" y="40" width="195" height="120" rx="10" fill="#FFF8E1" stroke="#F9A825" stroke-width="2.5"/>
  <text x="537" y="72" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700" fill="#F57F17">3. Strap (careful)</text>
  <text x="537" y="100" text-anchor="middle" font-family="Consolas,monospace" font-size="12" fill="#F9A825">0, 2, 12, 15, 5</text>
  <text x="537" y="126" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#F9A825">can disturb boot</text>
  <rect x="650" y="40" width="190" height="120" rx="10" fill="#FFEBEE" stroke="#C62828" stroke-width="2.5"/>
  <text x="745" y="72" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700" fill="#B71C1C">4. FORBIDDEN</text>
  <text x="745" y="100" text-anchor="middle" font-family="Consolas,monospace" font-size="12" fill="#C62828">IO6 … IO11</text>
  <text x="745" y="126" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#C62828">flash — do not use</text>
  <text x="430" y="190" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#546E7A">In short: start with the green column; red is an absolute ban on this path</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>In short:</strong> not every hole is the same. Board reference: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/boards/ESP32-DevKitC-1.html" rel="noopener noreferrer" target="_blank">Espressif — DevKitC-1</a>.
    <br>Image source: diagram by Koding Indonesia (FS-17).
  </figcaption>
</figure>
SVG;
    }

    private function powerSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="GND 3V3 dan 5V di board" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 185" width="100%" height="auto" role="img" aria-label="GND 3V3 5V">
  <text x="430" y="22" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">Daya di board: GND · 3V3 · 5V</text>
  <rect x="40" y="45" width="240" height="95" rx="10" fill="#ECEFF1" stroke="#455A64" stroke-width="2.5"/>
  <text x="160" y="85" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700" fill="#263238">GND</text>
  <text x="160" y="115" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#546E7A">“tanah” bersama rangkaian</text>
  <rect x="310" y="45" width="240" height="95" rx="10" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="430" y="85" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700" fill="#1B5E20">3V3</text>
  <text x="430" y="115" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#2E7D32">logika ESP32 (aman sensor 3,3 V)</text>
  <rect x="580" y="45" width="240" height="95" rx="10" fill="#FFF3E0" stroke="#EF6C00" stroke-width="2.5"/>
  <text x="700" y="85" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700" fill="#E65100">5V</text>
  <text x="700" y="115" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#EF6C00">dari USB — jangan ke pin 3,3 V</text>
  <text x="430" y="168" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#546E7A">GPIO ESP32 = level 3,3 V. Jangan menyuntik 5 V ke kaki GPIO.</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Intinya:</strong> cari label <code>GND</code>, <code>3V3</code>, dan <code>5V</code> di diagram resmi + silkscreen. Sumber gambar: diagram buatan Koding Indonesia (FS-17).
  </figcaption>
</figure>
SVG;
    }

    private function powerSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="GND 3V3 and 5V on the board" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 185" width="100%" height="auto" role="img" aria-label="GND 3V3 5V">
  <text x="430" y="22" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">Board power: GND · 3V3 · 5V</text>
  <rect x="40" y="45" width="240" height="95" rx="10" fill="#ECEFF1" stroke="#455A64" stroke-width="2.5"/>
  <text x="160" y="85" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700" fill="#263238">GND</text>
  <text x="160" y="115" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#546E7A">common “ground” for the circuit</text>
  <rect x="310" y="45" width="240" height="95" rx="10" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="430" y="85" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700" fill="#1B5E20">3V3</text>
  <text x="430" y="115" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#2E7D32">ESP32 logic (safe for 3.3 V sensors)</text>
  <rect x="580" y="45" width="240" height="95" rx="10" fill="#FFF3E0" stroke="#EF6C00" stroke-width="2.5"/>
  <text x="700" y="85" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700" fill="#E65100">5V</text>
  <text x="700" y="115" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#EF6C00">from USB — not into 3.3 V pins</text>
  <text x="430" y="168" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#546E7A">ESP32 GPIO = 3.3 V level. Never feed 5 V into a GPIO pin.</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>In short:</strong> find <code>GND</code>, <code>3V3</code>, and <code>5V</code> on the official diagram + silkscreen. Image source: diagram by Koding Indonesia (FS-17).
  </figcaption>
</figure>
SVG;
    }

    private function mapSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="tabel pin global FSIOT ringkas" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 250" width="100%" height="auto" role="img" aria-label="FSIOT global pin map">
  <text x="430" y="22" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">Peta pin global FSIOT (hafalkan yang ini)</text>
  <rect x="30" y="40" width="190" height="70" rx="8" fill="#E3F2FD" stroke="#1565C0" stroke-width="2"/>
  <text x="125" y="68" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700" fill="#0D47A1">LED belajar</text>
  <text x="125" y="92" text-anchor="middle" font-family="Consolas,monospace" font-size="14" fill="#1565C0">GPIO 2</text>
  <rect x="235" y="40" width="190" height="70" rx="8" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2"/>
  <text x="330" y="68" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700" fill="#1B5E20">Tombol user</text>
  <text x="330" y="92" text-anchor="middle" font-family="Consolas,monospace" font-size="14" fill="#2E7D32">GPIO 27</text>
  <rect x="440" y="40" width="190" height="70" rx="8" fill="#FFF8E1" stroke="#F9A825" stroke-width="2"/>
  <text x="535" y="68" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700" fill="#F57F17">DHT22 data</text>
  <text x="535" y="92" text-anchor="middle" font-family="Consolas,monospace" font-size="14" fill="#F9A825">GPIO 4</text>
  <rect x="645" y="40" width="185" height="70" rx="8" fill="#F3E5F5" stroke="#7B1FA2" stroke-width="2"/>
  <text x="737" y="68" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700" fill="#6A1B9A">LDR (ADC)</text>
  <text x="737" y="92" text-anchor="middle" font-family="Consolas,monospace" font-size="14" fill="#7B1FA2">GPIO 34</text>
  <rect x="30" y="125" width="190" height="70" rx="8" fill="#FFEBEE" stroke="#C62828" stroke-width="2"/>
  <text x="125" y="153" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700" fill="#B71C1C">Relay IN</text>
  <text x="125" y="177" text-anchor="middle" font-family="Consolas,monospace" font-size="14" fill="#C62828">GPIO 26</text>
  <rect x="235" y="125" width="190" height="70" rx="8" fill="#E0F7FA" stroke="#00838F" stroke-width="2"/>
  <text x="330" y="153" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700" fill="#006064">PIR OUT</text>
  <text x="330" y="177" text-anchor="middle" font-family="Consolas,monospace" font-size="14" fill="#00838F">GPIO 25</text>
  <rect x="440" y="125" width="190" height="70" rx="8" fill="#FBE9E7" stroke="#D84315" stroke-width="2"/>
  <text x="535" y="153" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700" fill="#BF360C">Servo</text>
  <text x="535" y="177" text-anchor="middle" font-family="Consolas,monospace" font-size="14" fill="#D84315">GPIO 13</text>
  <rect x="645" y="125" width="185" height="70" rx="8" fill="#E8EAF6" stroke="#3949AB" stroke-width="2"/>
  <text x="737" y="153" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700" fill="#283593">I2C SDA/SCL</text>
  <text x="737" y="177" text-anchor="middle" font-family="Consolas,monospace" font-size="13" fill="#3949AB">21 / 22</text>
  <text x="430" y="230" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#546E7A">Satu jalur = satu tabel. Jangan ganti nomor sesuka hati di tengah jalan.</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Intinya:</strong> tabel ini dikunci untuk seluruh Core FSIOT. Detail lengkap ada di bagian tabel di bawah. Sumber gambar: diagram buatan Koding Indonesia (FS-17).
  </figcaption>
</figure>
SVG;
    }

    private function mapSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="FSIOT global pin map summary" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 250" width="100%" height="auto" role="img" aria-label="FSIOT global pin map">
  <text x="430" y="22" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">FSIOT global pin map (learn these)</text>
  <rect x="30" y="40" width="190" height="70" rx="8" fill="#E3F2FD" stroke="#1565C0" stroke-width="2"/>
  <text x="125" y="68" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700" fill="#0D47A1">Practice LED</text>
  <text x="125" y="92" text-anchor="middle" font-family="Consolas,monospace" font-size="14" fill="#1565C0">GPIO 2</text>
  <rect x="235" y="40" width="190" height="70" rx="8" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2"/>
  <text x="330" y="68" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700" fill="#1B5E20">User button</text>
  <text x="330" y="92" text-anchor="middle" font-family="Consolas,monospace" font-size="14" fill="#2E7D32">GPIO 27</text>
  <rect x="440" y="40" width="190" height="70" rx="8" fill="#FFF8E1" stroke="#F9A825" stroke-width="2"/>
  <text x="535" y="68" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700" fill="#F57F17">DHT22 data</text>
  <text x="535" y="92" text-anchor="middle" font-family="Consolas,monospace" font-size="14" fill="#F9A825">GPIO 4</text>
  <rect x="645" y="40" width="185" height="70" rx="8" fill="#F3E5F5" stroke="#7B1FA2" stroke-width="2"/>
  <text x="737" y="68" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700" fill="#6A1B9A">LDR (ADC)</text>
  <text x="737" y="92" text-anchor="middle" font-family="Consolas,monospace" font-size="14" fill="#7B1FA2">GPIO 34</text>
  <rect x="30" y="125" width="190" height="70" rx="8" fill="#FFEBEE" stroke="#C62828" stroke-width="2"/>
  <text x="125" y="153" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700" fill="#B71C1C">Relay IN</text>
  <text x="125" y="177" text-anchor="middle" font-family="Consolas,monospace" font-size="14" fill="#C62828">GPIO 26</text>
  <rect x="235" y="125" width="190" height="70" rx="8" fill="#E0F7FA" stroke="#00838F" stroke-width="2"/>
  <text x="330" y="153" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700" fill="#006064">PIR OUT</text>
  <text x="330" y="177" text-anchor="middle" font-family="Consolas,monospace" font-size="14" fill="#00838F">GPIO 25</text>
  <rect x="440" y="125" width="190" height="70" rx="8" fill="#FBE9E7" stroke="#D84315" stroke-width="2"/>
  <text x="535" y="153" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700" fill="#BF360C">Servo</text>
  <text x="535" y="177" text-anchor="middle" font-family="Consolas,monospace" font-size="14" fill="#D84315">GPIO 13</text>
  <rect x="645" y="125" width="185" height="70" rx="8" fill="#E8EAF6" stroke="#3949AB" stroke-width="2"/>
  <text x="737" y="153" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700" fill="#283593">I2C SDA/SCL</text>
  <text x="737" y="177" text-anchor="middle" font-family="Consolas,monospace" font-size="13" fill="#3949AB">21 / 22</text>
  <text x="430" y="230" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#546E7A">One path = one table. Do not change numbers halfway for fun.</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>In short:</strong> this table is locked for the whole FSIOT Core. Full detail is in the table below. Image source: diagram by Koding Indonesia (FS-17).
  </figcaption>
</figure>
SVG;
    }

    private function body(): string
    {
        $pinlayout = $this->pinlayoutFigureId();
        $board = $this->boardFigureId();
        $kinds = $this->kindsSvgId();
        $power = $this->powerSvgId();
        $map = $this->mapSvgId();

        return <<<HTML
<h2>Pendahuluan — fase BUILDER dimulai</h2>
<p>Artikel ini adalah <strong>#87 (ini)</strong> · modul <strong>FS-17</strong> di jalur <em>Full Stack IoT Developer — Dari Nol</em>. Fase <strong>ZERO</strong> sudah selesai (sampai FS-16). Mulai hari ini kita masuk fase <strong>BUILDER</strong>: perangkat hidup di meja, masih tanpa Wi‑Fi.</p>
<p><strong>Analogi:</strong> peta pin = denah rumah. Kalau salah cabang, LED “nyasar” ke kaki yang salah — atau lebih parah, menyentuh pin flash.</p>
<p><strong>Prasyarat:</strong> FS-16 (fungsi) · board ESP32 sudah pernah Upload · paham Serial Monitor.</p>
<p><strong>Cara pakai artikel ini (urutan kerja):</strong></p>
<ol>
<li><strong>Siapkan board di tangan</strong> (DevKitC-1 atau clone yang silkscreen-nya cocok diagram resmi).</li>
<li><strong>Buka artikel ini di browser</strong> — hari ini kita membaca peta, bukan menulis sketch baru.</li>
<li>Cocokkan silkscreen board dengan diagram pinout resmi di bawah.</li>
<li>Catat empat jenis pin: aman, input-only, strap, terlarang.</li>
<li>Centang checklist 10/10 di browser.</li>
</ol>
<p><strong>Tidak perlu hari ini:</strong> Upload sketch baru, Laragon, <code>php artisan</code>, wiring sensor baru, Wi‑Fi. Tools hari ini: <strong>board di tangan</strong> + <strong>browser</strong> (artikel + checklist) + pensil/catatan (opsional).</p>

<h2>Persiapan — buka &amp; siapkan ini dulu</h2>
<p><strong>Urutan meja kerja:</strong> peta dibaca di browser sambil memegang board — bukan di terminal PHP, dan bukan “asal Upload dulu”.</p>
<ol>
<li>Ambil board ESP32-DevKitC-1 (atau clone pin-compatible).</li>
<li>Buka artikel ini full-screen di browser (zoom diagram bila perlu).</li>
<li>Siapkan lampu meja / cahaya cukup supaya silkscreen terbaca.</li>
<li>(Opsional) pensil + kertas untuk mencatat GPIO yang akan kamu pakai nanti.</li>
</ol>
<p><strong>Alat yang dipakai hari ini:</strong> board ESP32, browser, mata + jari (cocokkan label), catatan opsional.</p>
<p><strong>Tidak dipakai hari ini:</strong> Arduino IDE sebagai fokus utama, Serial Monitor, Laragon, <code>php artisan</code>, multimeter (boleh nanti di FS-18+).</p>
{$board}

<h2>Cocokkan board dengan diagram resmi</h2>
{$pinlayout}
<p><strong>Langkah praktis:</strong> putar board di tangan sampai arah USB sama dengan diagram. Lalu tunjuk satu nomor di silkscreen (misalnya <code>GND</code> atau <code>3V3</code>) dan temukan pasangan yang sama di gambar. Kalau banyak label beda jauh, itu bukan peta yang kita pakai — cari board yang cocok DevKitC-1.</p>

<h2>Empat jenis pin</h2>
{$kinds}
<p><strong>Input-only (34 / 35 / 36 / 39):</strong> boleh dipakai untuk membaca sensor analog (ADC), tetapi <strong>jangan</strong> dipakai sebagai output LED/relay.</p>
<p><strong>Strap (0, 2, 12, 15, 5):</strong> pin ini ikut “ritual boot”. Di jalur FSIOT kita tetap boleh memakai <strong>GPIO 2</strong> untuk LED belajar (sudah dikunci di tabel global), tetapi hindari GPIO0 untuk tombol latihan.</p>
<p><strong>IO6–IO11:</strong> terhubung ke flash/PSRAM. <strong>Jangan pernah</strong> wiring ke situ di Core.</p>

<h2>GND, 3V3, dan 5V</h2>
{$power}
<p>Semua rangkaian breadboard nanti harus punya <strong>GND bersama</strong> dengan board. Sensor 3,3 V ambil dari <code>3V3</code>. Pin <code>5V</code> hanya untuk modul yang memang butuh 5 V (misalnya beberapa relay) — bukan untuk kaki GPIO.</p>

<h2>Tabel pin global FSIOT</h2>
{$map}
<p>Ini tabel yang dikunci untuk seluruh jalur. Hafalkan fungsi → nomor:</p>
<table>
<thead>
<tr><th>Fungsi</th><th>GPIO</th><th>Keterangan</th></tr>
</thead>
<tbody>
<tr><td>LED status / belajar</td><td><strong>2</strong></td><td>atau LED onboard jika ada</td></tr>
<tr><td>Tombol user</td><td><strong>27</strong></td><td>hindari GPIO0 untuk latihan tombol</td></tr>
<tr><td>DHT22 data</td><td><strong>4</strong></td><td>+ pull-up 10kΩ (nanti di FS-21)</td></tr>
<tr><td>LDR → ADC</td><td><strong>34</strong></td><td>input-only</td></tr>
<tr><td>Relay IN</td><td><strong>26</strong></td><td>detail aktif HIGH/LOW di FS-23</td></tr>
<tr><td>PIR OUT</td><td><strong>25</strong></td><td>nanti di FS-25</td></tr>
<tr><td>Servo signal</td><td><strong>13</strong></td><td>nanti di FS-26</td></tr>
<tr><td>I2C SDA</td><td><strong>21</strong></td><td>BME280 + OLED</td></tr>
<tr><td>I2C SCL</td><td><strong>22</strong></td><td></td></tr>
</tbody>
</table>
<p><strong>Cara menguji pemahaman di atas:</strong> tanpa Upload. Sukses = kamu bisa menunjuk di board: di mana kira-kira GPIO 2, GND, dan 3V3, serta bilang kenapa IO6–IO11 tidak boleh. Bukan perintah Laragon / web server.</p>

<h2 id="fsiot-pin-checklist">Praktik — checklist peta pin</h2>
<p>Centang setiap langkah setelah kamu lakukan di meja. Target: <strong>10/10</strong>. Ada checklist interaktif di bawah.</p>
<ul id="fsiot-pin-checklist-items">
<li>Board ada di tangan saat membaca artikel</li>
<li>Browser menampilkan diagram pinout resmi</li>
<li>Silkscreen board sudah dicocokkan dengan diagram Espressif</li>
<li>Paham: IO6–IO11 terlarang (flash)</li>
<li>Paham: 34/35/36/39 = input-only</li>
<li>Paham: strap pin bisa ganggu boot</li>
<li>Bisa tunjuk label GND di board</li>
<li>Bisa tunjuk label 3V3 di board</li>
<li>Hafal LED belajar = GPIO 2 dan tombol = GPIO 27</li>
<li>Sadar: satu jalur = satu tabel pin (jangan ganti nomor sesuka hati)</li>
</ul>
<p><strong>Cara menguji checklist:</strong> centang di browser setelah cocokkan board. Tidak perlu <code>php artisan</code> atau Upload.</p>

<h2>Kesalahan yang sering terjadi</h2>
<ul>
<li><strong>Mengikuti pinout board beda.</strong> Clone DOIT / “DevKit” lain bisa beda urutan. Selalu cocokkan silkscreen.</li>
<li><strong>Memakai IO6–IO11.</strong> Bisa merusak boot / flash. Larangan mutlak.</li>
<li><strong>LED di GPIO 34.</strong> Input-only — tidak bisa jadi output yang andal.</li>
<li><strong>Tombol di GPIO0.</strong> Bisa masuk mode download tanpa sengaja. Pakai GPIO 27.</li>
<li><strong>Menyuntik 5 V ke GPIO.</strong> Level logika ESP32 = 3,3 V.</li>
<li><strong>Menghafal dari foto Instagram tanpa label.</strong> Pakai diagram resmi di artikel ini.</li>
<li><strong>Mengira hari ini harus Upload.</strong> FS-17 = peta. Kode GPIO mulai FS-18.</li>
</ul>

<h2>Selanjutnya</h2>
<p><strong>Intinya:</strong> kalau board di tangan sudah “kenal” dengan diagram resmi dan tabel FSIOT, FS-17 selesai — fondasi BUILDER siap.</p>
<p>Lanjut ke <strong>FS-18</strong> (nyalakan LED dari kode) saat modulnya terbit. Di sana baru Arduino IDE + <code>pinMode</code> / <code>digitalWrite</code>.</p>
<p>Daftar modul lengkap: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $pinlayout = $this->pinlayoutFigureEn();
        $board = $this->boardFigureEn();
        $kinds = $this->kindsSvgEn();
        $power = $this->powerSvgEn();
        $map = $this->mapSvgEn();

        return <<<HTML
<h2>Introduction — BUILDER phase begins</h2>
<p>This is article <strong>#87 (this article)</strong> · module <strong>FS-17</strong> on the <em>Full Stack IoT Developer — From Zero</em> path. Phase <strong>ZERO</strong> is done (through FS-16). Today we enter <strong>BUILDER</strong>: devices live on the desk, still without Wi‑Fi.</p>
<p><strong>Analogy:</strong> a pin map is a floor plan. Wrong branch → the LED “wanders” to the wrong leg — or worse, you touch a flash pin.</p>
<p><strong>Prerequisites:</strong> FS-16 (functions) · you have uploaded to an ESP32 before · you know Serial Monitor.</p>
<p><strong>How to use this article (work order):</strong></p>
<ol>
<li><strong>Have the board in hand</strong> (DevKitC-1 or a clone whose silkscreen matches the official diagram).</li>
<li><strong>Open this article in the browser</strong> — today we read a map; we do not write a new sketch.</li>
<li>Match your board silkscreen to the official pinout diagram below.</li>
<li>Note the four pin kinds: safe, input-only, strap, forbidden.</li>
<li>Tick the 10/10 checklist in the browser.</li>
</ol>
<p><strong>Not needed today:</strong> uploading a new sketch, Laragon, <code>php artisan</code>, new sensor wiring, Wi‑Fi. Today's tools: <strong>board in hand</strong> + <strong>browser</strong> (article + checklist) + pencil/notes (optional).</p>

<h2>Preparation — open &amp; gather these first</h2>
<p><strong>Desk order:</strong> read the map in the browser while holding the board — not in a PHP terminal, and not “Upload first, think later”.</p>
<ol>
<li>Take your ESP32-DevKitC-1 (or pin-compatible clone).</li>
<li>Open this article full-screen in the browser (zoom the diagram if needed).</li>
<li>Use enough light so the silkscreen is readable.</li>
<li>(Optional) pencil + paper to note GPIOs you will use later.</li>
</ol>
<p><strong>Tools used today:</strong> ESP32 board, browser, eyes + fingers (match labels), optional notes.</p>
<p><strong>Not used today:</strong> Arduino IDE as the main focus, Serial Monitor, Laragon, <code>php artisan</code>, multimeter (OK later in FS-18+).</p>
{$board}

<h2>Match your board to the official diagram</h2>
{$pinlayout}
<p><strong>Practical step:</strong> rotate the board until the USB direction matches the diagram. Then point to one silkscreen label (for example <code>GND</code> or <code>3V3</code>) and find the same pair in the image. If many labels differ a lot, that is not our map — find a board that matches DevKitC-1.</p>

<h2>Four kinds of pins</h2>
{$kinds}
<p><strong>Input-only (34 / 35 / 36 / 39):</strong> fine for reading analog sensors (ADC), but <strong>do not</strong> use them as LED/relay outputs.</p>
<p><strong>Strap (0, 2, 12, 15, 5):</strong> these pins take part in boot. On the FSIOT path we still use <strong>GPIO 2</strong> for the practice LED (locked in the global table), but avoid GPIO0 for practice buttons.</p>
<p><strong>IO6–IO11:</strong> tied to flash/PSRAM. <strong>Never</strong> wire to them in Core.</p>

<h2>GND, 3V3, and 5V</h2>
{$power}
<p>Every breadboard circuit later needs a <strong>shared GND</strong> with the board. 3.3 V sensors take power from <code>3V3</code>. The <code>5V</code> pin is only for modules that truly need 5 V (some relays) — not for GPIO legs.</p>

<h2>FSIOT global pin table</h2>
{$map}
<p>This table is locked for the whole path. Memorize function → number:</p>
<table>
<thead>
<tr><th>Function</th><th>GPIO</th><th>Notes</th></tr>
</thead>
<tbody>
<tr><td>Status / practice LED</td><td><strong>2</strong></td><td>or onboard LED if present</td></tr>
<tr><td>User button</td><td><strong>27</strong></td><td>avoid GPIO0 for button practice</td></tr>
<tr><td>DHT22 data</td><td><strong>4</strong></td><td>+ 10kΩ pull-up (later in FS-21)</td></tr>
<tr><td>LDR → ADC</td><td><strong>34</strong></td><td>input-only</td></tr>
<tr><td>Relay IN</td><td><strong>26</strong></td><td>active HIGH/LOW detail in FS-23</td></tr>
<tr><td>PIR OUT</td><td><strong>25</strong></td><td>later in FS-25</td></tr>
<tr><td>Servo signal</td><td><strong>13</strong></td><td>later in FS-26</td></tr>
<tr><td>I2C SDA</td><td><strong>21</strong></td><td>BME280 + OLED</td></tr>
<tr><td>I2C SCL</td><td><strong>22</strong></td><td></td></tr>
</tbody>
</table>
<p><strong>How to test the understanding above:</strong> no Upload. Success = you can point on the board to roughly where GPIO 2, GND, and 3V3 are, and explain why IO6–IO11 are banned. Not a Laragon / web-server command.</p>

<h2 id="fsiot-pin-checklist">Practice — pin map checklist</h2>
<p>Tick each step after you do it at the desk. Target: <strong>10/10</strong>. An interactive checklist is below.</p>
<ul id="fsiot-pin-checklist-items">
<li>Board is in hand while reading the article</li>
<li>Browser shows the official pinout diagram</li>
<li>Board silkscreen matched to the Espressif diagram</li>
<li>I know: IO6–IO11 are forbidden (flash)</li>
<li>I know: 34/35/36/39 are input-only</li>
<li>I know: strap pins can disturb boot</li>
<li>I can point to the GND label on the board</li>
<li>I can point to the 3V3 label on the board</li>
<li>I remember practice LED = GPIO 2 and button = GPIO 27</li>
<li>I know: one path = one pin table (do not change numbers freely)</li>
</ul>
<p><strong>How to test the checklist:</strong> tick it in the browser after matching the board. No <code>php artisan</code> or Upload required.</p>

<h2>Common mistakes</h2>
<ul>
<li><strong>Following a different board’s pinout.</strong> DOIT / other “DevKit” clones can reorder pins. Always match silkscreen.</li>
<li><strong>Using IO6–IO11.</strong> Can break boot / flash. Absolute ban.</li>
<li><strong>LED on GPIO 34.</strong> Input-only — not a reliable output.</li>
<li><strong>Button on GPIO0.</strong> Can enter download mode by accident. Use GPIO 27.</li>
<li><strong>Feeding 5 V into GPIO.</strong> ESP32 logic level = 3.3 V.</li>
<li><strong>Memorizing from an unlabeled Instagram photo.</strong> Use the official diagram in this article.</li>
<li><strong>Thinking today needs Upload.</strong> FS-17 = map. GPIO code starts in FS-18.</li>
</ul>

<h2>Next</h2>
<p><strong>In short:</strong> if the board in your hand already “knows” the official diagram and the FSIOT table, FS-17 is done — BUILDER foundation is ready.</p>
<p>Continue to <strong>FS-18</strong> (turn on an LED from code) when that module publishes. There we use Arduino IDE + <code>pinMode</code> / <code>digitalWrite</code>.</p>
<p>Full module list: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }
}
