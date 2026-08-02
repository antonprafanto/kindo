<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article82Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first()
            ?? Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $iotCat) {
            throw new \RuntimeException('User atau kategori iot-smart-device/esp32-arduino tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-variabel-tipe-data-serial';

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
                'title'              => 'Variabel & kotak angka: int, float, dan Serial',
                'title_en'           => 'Variables & number boxes: int, float, and Serial',
                'excerpt'            => 'FS-12 / #82: Simpan nilai di variabel (int, float, bool, String), lalu cetak ke Serial Monitor di baud 115200. Buka Arduino IDE + Serial Monitor dulu — belum sensor.',
                'excerpt_en'         => 'FS-12 / #82: Store values in variables (int, float, bool, String), then print them to the Serial Monitor at 115200 baud. Open Arduino IDE + Serial Monitor first — no sensors yet.',
                'body'               => $this->body(),
                'body_en'            => $this->bodyEn(),
                'status'             => 'draft',
                'is_featured'        => false,
                'published_at'       => null,
                'seo_title'          => 'Variabel, tipe data & Serial — Full Stack IoT #82',
                'seo_title_en'       => 'Variables, data types & Serial — Full Stack IoT #82',
                'seo_description'    => 'Belajar variabel int/float/bool/String dan Serial.begin + Serial.println di ESP32. Buka Serial Monitor baud 115200. Modul FS-12 jalur Full Stack IoT.',
                'seo_description_en' => 'Learn int/float/bool/String variables and Serial.begin + Serial.println on ESP32. Open Serial Monitor at 115200 baud. Full Stack IoT FS-12 module.',
            ]
        );

        if ($article->published_at !== null || $article->status !== 'draft') {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['fullstack-iot', 'iot', 'esp32'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel #82 / FS-12 tersimpan sebagai DRAFT: '.$article->title);
    }

    private function serialFinderSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Cari Open Serial Monitor dan baud 115200 di Arduino IDE 2" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 260" width="100%" height="auto" role="img" aria-label="Serial Monitor dan baud 115200">
  <text x="430" y="28" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">Arduino IDE 2 — dua hal yang wajib sama hari ini</text>
  <rect x="30" y="50" width="390" height="170" rx="12" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
  <circle cx="110" cy="120" r="34" fill="#fff" stroke="#1565C0" stroke-width="3"/>
  <circle cx="110" cy="120" r="14" fill="none" stroke="#1565C0" stroke-width="3"/>
  <line x1="120" y1="130" x2="136" y2="146" stroke="#1565C0" stroke-width="4" stroke-linecap="round"/>
  <text x="250" y="100" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700" fill="#0D47A1">1. Open Serial Monitor</text>
  <text x="250" y="128" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#1565C0">Ikon kaca pembesar — toolbar kanan atas</text>
  <text x="250" y="152" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#1565C0">atau menu Tools → Serial Monitor</text>
  <text x="250" y="180" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#37474F">Buka setelah Upload (supaya port tidak bentrok)</text>
  <rect x="440" y="50" width="390" height="170" rx="12" fill="#FFF8E1" stroke="#F9A825" stroke-width="2.5"/>
  <rect x="490" y="88" width="290" height="44" rx="8" fill="#1565C0"/>
  <text x="635" y="116" text-anchor="middle" font-family="Consolas,monospace" font-size="18" font-weight="700" fill="#fff">115200 baud</text>
  <text x="635" y="160" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700" fill="#F57F17">2. Samakan baud</text>
  <text x="635" y="186" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#6D4C41">Kode: Serial.begin(115200)</text>
  <text x="635" y="208" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#6D4C41">Dropdown Serial Monitor: 115200</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Intinya:</strong> board tetap <strong>ESP32 Dev Module</strong>. Jangan ikut contoh board lain atau baud <strong>9600</strong> dari screenshot internet — hari ini kita pakai <strong>115200</strong>.
    <br>Sumber gambar: diagram buatan Koding Indonesia (FS-12) — selaras Arduino IDE 2 (bukan screenshot IDE 1.x / contoh board MKR).
  </figcaption>
</figure>
SVG;
    }

    private function serialFinderSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Find Open Serial Monitor and 115200 baud in Arduino IDE 2" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 260" width="100%" height="auto" role="img" aria-label="Serial Monitor and 115200 baud">
  <text x="430" y="28" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">Arduino IDE 2 — two things that must match today</text>
  <rect x="30" y="50" width="390" height="170" rx="12" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
  <circle cx="110" cy="120" r="34" fill="#fff" stroke="#1565C0" stroke-width="3"/>
  <circle cx="110" cy="120" r="14" fill="none" stroke="#1565C0" stroke-width="3"/>
  <line x1="120" y1="130" x2="136" y2="146" stroke="#1565C0" stroke-width="4" stroke-linecap="round"/>
  <text x="250" y="100" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700" fill="#0D47A1">1. Open Serial Monitor</text>
  <text x="250" y="128" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#1565C0">Magnifying-glass icon — top-right toolbar</text>
  <text x="250" y="152" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#1565C0">or Tools → Serial Monitor</text>
  <text x="250" y="180" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#37474F">Open after Upload (avoids port conflicts)</text>
  <rect x="440" y="50" width="390" height="170" rx="12" fill="#FFF8E1" stroke="#F9A825" stroke-width="2.5"/>
  <rect x="490" y="88" width="290" height="44" rx="8" fill="#1565C0"/>
  <text x="635" y="116" text-anchor="middle" font-family="Consolas,monospace" font-size="18" font-weight="700" fill="#fff">115200 baud</text>
  <text x="635" y="160" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700" fill="#F57F17">2. Match the baud</text>
  <text x="635" y="186" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#6D4C41">Code: Serial.begin(115200)</text>
  <text x="635" y="208" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#6D4C41">Serial Monitor dropdown: 115200</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>In short:</strong> keep board as <strong>ESP32 Dev Module</strong>. Do not copy another board or <strong>9600</strong> baud from random internet screenshots — today we use <strong>115200</strong>.
    <br>Image source: diagram by Koding Indonesia (FS-12) — aligned with Arduino IDE 2 (not an IDE 1.x / MKR-board screenshot).
  </figcaption>
</figure>
SVG;
    }

    private function boardFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/esp32-devkitc-overview.jpg" width="1200" height="800" alt="ESP32-DevKitC — board yang mengirim teks Serial lewat USB" loading="lazy" style="width:100%;height:auto;max-height:360px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>ESP32-DevKitC</strong> — colok <strong>kabel USB data</strong>, Upload sketch, lalu buka Serial Monitor di IDE. Hari ini belum wiring sensor baru.
    <br>Sumber gambar: <a href="https://docs.espressif.com/projects/esp-dev-kits/en/latest/esp32/esp32-devkitc/user_guide.html" rel="noopener noreferrer" target="_blank">Espressif — ESP32-DevKitC user guide</a>.
  </figcaption>
</figure>
HTML;
    }

    private function boardFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/esp32-devkitc-overview.jpg" width="1200" height="800" alt="ESP32-DevKitC — the board that sends Serial text over USB" loading="lazy" style="width:100%;height:auto;max-height:360px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>ESP32-DevKitC</strong> — plug a <strong>USB data cable</strong>, Upload the sketch, then open Serial Monitor in the IDE. No new sensor wiring today.
    <br>Image source: <a href="https://docs.espressif.com/projects/esp-dev-kits/en/latest/esp32/esp32-devkitc/user_guide.html" rel="noopener noreferrer" target="_blank">Espressif — ESP32-DevKitC user guide</a>.
  </figcaption>
</figure>
HTML;
    }

    private function boxesSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Analogi variabel sebagai kotak bernama" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 240" width="100%" height="auto" role="img" aria-label="empat kotak variabel">
  <text x="430" y="28" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700">Variabel = kotak bernama di memori board</text>
  <rect x="30" y="55" width="180" height="140" rx="10" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="120" y="90" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#1B5E20">int</text>
  <text x="120" y="120" text-anchor="middle" font-family="Consolas,monospace" font-size="13">umur</text>
  <text x="120" y="150" text-anchor="middle" font-family="system-ui,sans-serif" font-size="18" font-weight="700">20</text>
  <text x="120" y="175" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#558B2F">bilangan bulat</text>
  <rect x="230" y="55" width="180" height="140" rx="10" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
  <text x="320" y="90" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#0D47A1">float</text>
  <text x="320" y="120" text-anchor="middle" font-family="Consolas,monospace" font-size="13">suhu</text>
  <text x="320" y="150" text-anchor="middle" font-family="system-ui,sans-serif" font-size="18" font-weight="700">28.5</text>
  <text x="320" y="175" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#1976D2">desimal</text>
  <rect x="430" y="55" width="180" height="140" rx="10" fill="#FFF8E1" stroke="#F9A825" stroke-width="2.5"/>
  <text x="520" y="90" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#F57F17">bool</text>
  <text x="520" y="120" text-anchor="middle" font-family="Consolas,monospace" font-size="13">siap</text>
  <text x="520" y="150" text-anchor="middle" font-family="system-ui,sans-serif" font-size="18" font-weight="700">true</text>
  <text x="520" y="175" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#F9A825">ya / tidak</text>
  <rect x="630" y="55" width="200" height="140" rx="10" fill="#F3E5F5" stroke="#7B1FA2" stroke-width="2.5"/>
  <text x="730" y="90" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#6A1B9A">String</text>
  <text x="730" y="120" text-anchor="middle" font-family="Consolas,monospace" font-size="13">nama</text>
  <text x="730" y="150" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700">"Anton"</text>
  <text x="730" y="175" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#8E24AA">teks singkat</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Analogi:</strong> tipe = jenis kotak · nama = label · nilai = isi. Assignment: <code>umur = 21;</code> mengganti isi kotak.
    <br>Sumber gambar: diagram buatan Koding Indonesia (FS-12).
  </figcaption>
</figure>
SVG;
    }

    private function boxesSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Variables as named boxes analogy" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 240" width="100%" height="auto" role="img" aria-label="four variable boxes">
  <text x="430" y="28" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700">A variable = a named box in board memory</text>
  <rect x="30" y="55" width="180" height="140" rx="10" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="120" y="90" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#1B5E20">int</text>
  <text x="120" y="120" text-anchor="middle" font-family="Consolas,monospace" font-size="13">age</text>
  <text x="120" y="150" text-anchor="middle" font-family="system-ui,sans-serif" font-size="18" font-weight="700">20</text>
  <text x="120" y="175" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#558B2F">whole number</text>
  <rect x="230" y="55" width="180" height="140" rx="10" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
  <text x="320" y="90" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#0D47A1">float</text>
  <text x="320" y="120" text-anchor="middle" font-family="Consolas,monospace" font-size="13">tempC</text>
  <text x="320" y="150" text-anchor="middle" font-family="system-ui,sans-serif" font-size="18" font-weight="700">28.5</text>
  <text x="320" y="175" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#1976D2">decimal</text>
  <rect x="430" y="55" width="180" height="140" rx="10" fill="#FFF8E1" stroke="#F9A825" stroke-width="2.5"/>
  <text x="520" y="90" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#F57F17">bool</text>
  <text x="520" y="120" text-anchor="middle" font-family="Consolas,monospace" font-size="13">ready</text>
  <text x="520" y="150" text-anchor="middle" font-family="system-ui,sans-serif" font-size="18" font-weight="700">true</text>
  <text x="520" y="175" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#F9A825">yes / no</text>
  <rect x="630" y="55" width="200" height="140" rx="10" fill="#F3E5F5" stroke="#7B1FA2" stroke-width="2.5"/>
  <text x="730" y="90" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#6A1B9A">String</text>
  <text x="730" y="120" text-anchor="middle" font-family="Consolas,monospace" font-size="13">name</text>
  <text x="730" y="150" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700">"Anton"</text>
  <text x="730" y="175" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#8E24AA">short text</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Analogy:</strong> type = kind of box · name = label · value = contents. Assignment: <code>age = 21;</code> replaces what is inside.
    <br>Image source: diagram by Koding Indonesia (FS-12).
  </figcaption>
</figure>
SVG;
    }

    private function serialSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Serial Monitor baud 115200 contoh output" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 300" width="100%" height="auto" role="img" aria-label="jendela Serial Monitor">
  <text x="430" y="26" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">Serial Monitor (IDE 2) - mata kita hari ini</text>
  <rect x="40" y="45" width="780" height="220" rx="10" fill="#1E1E1E" stroke="#1a1a1a" stroke-width="2.5"/>
  <rect x="40" y="45" width="780" height="40" rx="10" fill="#2D2D2D"/>
  <rect x="40" y="70" width="780" height="15" fill="#2D2D2D"/>
  <text x="60" y="72" font-family="system-ui,sans-serif" font-size="12" fill="#B0BEC5">Output dari ESP32</text>
  <rect x="560" y="54" width="240" height="28" rx="6" fill="#1565C0"/>
  <text x="680" y="73" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700" fill="#fff">Baud: 115200</text>
  <text x="70" y="120" font-family="Consolas,monospace" font-size="14" fill="#A5D6A7">Halo dari ESP32 - FS12_hello</text>
  <text x="70" y="150" font-family="Consolas,monospace" font-size="14" fill="#A5D6A7">umur = 20</text>
  <text x="70" y="180" font-family="Consolas,monospace" font-size="14" fill="#A5D6A7">suhu = 28.50</text>
  <text x="70" y="210" font-family="Consolas,monospace" font-size="14" fill="#A5D6A7">siap = 1</text>
  <text x="70" y="240" font-family="Consolas,monospace" font-size="14" fill="#A5D6A7">nama = Anton</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Intinya:</strong> kalau baud di kode (<code>Serial.begin(115200)</code>) dan di dropdown Serial Monitor <strong>sama</strong>, teks terbaca. Baud beda = huruf acak.
    <br>Sumber gambar: diagram buatan Koding Indonesia (FS-12) — meniru panel Serial Monitor IDE 2 (bukan screenshot IDE 1.x).
  </figcaption>
</figure>
SVG;
    }

    private function serialSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Serial Monitor 115200 baud sample output" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 300" width="100%" height="auto" role="img" aria-label="Serial Monitor window">
  <text x="430" y="26" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">Serial Monitor (IDE 2) - our eyes today</text>
  <rect x="40" y="45" width="780" height="220" rx="10" fill="#1E1E1E" stroke="#1a1a1a" stroke-width="2.5"/>
  <rect x="40" y="45" width="780" height="40" rx="10" fill="#2D2D2D"/>
  <rect x="40" y="70" width="780" height="15" fill="#2D2D2D"/>
  <text x="60" y="72" font-family="system-ui,sans-serif" font-size="12" fill="#B0BEC5">Output from ESP32</text>
  <rect x="560" y="54" width="240" height="28" rx="6" fill="#1565C0"/>
  <text x="680" y="73" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700" fill="#fff">Baud: 115200</text>
  <text x="70" y="120" font-family="Consolas,monospace" font-size="14" fill="#A5D6A7">Hello from ESP32 - FS12_hello</text>
  <text x="70" y="150" font-family="Consolas,monospace" font-size="14" fill="#A5D6A7">age = 20</text>
  <text x="70" y="180" font-family="Consolas,monospace" font-size="14" fill="#A5D6A7">tempC = 28.50</text>
  <text x="70" y="210" font-family="Consolas,monospace" font-size="14" fill="#A5D6A7">ready = 1</text>
  <text x="70" y="240" font-family="Consolas,monospace" font-size="14" fill="#A5D6A7">name = Anton</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>In short:</strong> if the baud in code (<code>Serial.begin(115200)</code>) matches the Serial Monitor dropdown, text is readable. Mismatched baud = garbage characters.
    <br>Image source: diagram by Koding Indonesia (FS-12) — mimics the IDE 2 Serial Monitor panel (not an IDE 1.x screenshot).
  </figcaption>
</figure>
SVG;
    }

    private function workflowSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Urutan kerja Upload lalu buka Serial Monitor" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 150" width="100%" height="auto" role="img" aria-label="Edit Upload Serial Monitor">
  <text x="430" y="24" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700">Urutan kerja hari ini (jangan dibalik)</text>
  <rect x="20" y="45" width="150" height="70" rx="8" fill="#FFF" stroke="#1a1a1a" stroke-width="2"/>
  <text x="95" y="75" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">1. Edit</text>
  <text x="95" y="95" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11">di Arduino IDE</text>
  <text x="185" y="85" font-size="18" fill="#1565C0">→</text>
  <rect x="210" y="45" width="150" height="70" rx="8" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2"/>
  <text x="285" y="75" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">2. Upload</text>
  <text x="285" y="95" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11">Done uploading</text>
  <text x="375" y="85" font-size="18" fill="#1565C0">→</text>
  <rect x="400" y="45" width="200" height="70" rx="8" fill="#E3F2FD" stroke="#1565C0" stroke-width="2"/>
  <text x="500" y="75" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">3. Serial Monitor</text>
  <text x="500" y="95" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11">baud 115200</text>
  <text x="615" y="85" font-size="18" fill="#1565C0">→</text>
  <rect x="640" y="45" width="200" height="70" rx="8" fill="#FFF8E1" stroke="#F9A825" stroke-width="2"/>
  <text x="740" y="75" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">4. Baca teks</text>
  <text x="740" y="95" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11">bukan php artisan</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Tips:</strong> buka Serial Monitor <em>setelah</em> Upload (atau reset board dengan tombol EN) supaya baris awal tidak terlewat.
    <br>Sumber gambar: diagram buatan Koding Indonesia (FS-12).
  </figcaption>
</figure>
SVG;
    }

    private function workflowSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Work order Upload then open Serial Monitor" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 150" width="100%" height="auto" role="img" aria-label="Edit Upload Serial Monitor">
  <text x="430" y="24" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700">Today's work order (do not reverse it)</text>
  <rect x="20" y="45" width="150" height="70" rx="8" fill="#FFF" stroke="#1a1a1a" stroke-width="2"/>
  <text x="95" y="75" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">1. Edit</text>
  <text x="95" y="95" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11">in Arduino IDE</text>
  <text x="185" y="85" font-size="18" fill="#1565C0">→</text>
  <rect x="210" y="45" width="150" height="70" rx="8" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2"/>
  <text x="285" y="75" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">2. Upload</text>
  <text x="285" y="95" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11">Done uploading</text>
  <text x="375" y="85" font-size="18" fill="#1565C0">→</text>
  <rect x="400" y="45" width="200" height="70" rx="8" fill="#E3F2FD" stroke="#1565C0" stroke-width="2"/>
  <text x="500" y="75" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">3. Serial Monitor</text>
  <text x="500" y="95" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11">baud 115200</text>
  <text x="615" y="85" font-size="18" fill="#1565C0">→</text>
  <rect x="640" y="45" width="200" height="70" rx="8" fill="#FFF8E1" stroke="#F9A825" stroke-width="2"/>
  <text x="740" y="75" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">4. Read text</text>
  <text x="740" y="95" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11">not php artisan</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Tip:</strong> open Serial Monitor <em>after</em> Upload (or press the EN reset button) so early lines are not missed.
    <br>Image source: diagram by Koding Indonesia (FS-12).
  </figcaption>
</figure>
SVG;
    }

    private function body(): string
    {
        $finder = $this->serialFinderSvgId();
        $board = $this->boardFigureId();
        $boxes = $this->boxesSvgId();
        $serial = $this->serialSvgId();
        $flow = $this->workflowSvgId();

        return <<<HTML
<h2>Pendahuluan — kenapa butuh “kotak”?</h2>
<p>Artikel ini adalah <strong>#82 (ini)</strong> · modul <strong>FS-12</strong> di jalur <em>Full Stack IoT Developer — Dari Nol</em>. Di <strong>FS-11</strong> kamu sudah paham sketch, <code>setup</code>/<code>loop</code>, dan Upload. Hari ini kita isi “resep” dengan <strong>variabel</strong> (kotak penyimpan angka/teks), lalu melihat isinya lewat <strong>Serial Monitor</strong>.</p>
<p><strong>Analogi:</strong> variabel seperti kotak bertuliskan nama di meja. Tipe = jenis kotak. Nilai = isi. Serial Monitor = kamera yang memperlihatkan isi kotak ke layar laptop.</p>
<p><strong>Prasyarat:</strong> FS-11 (Arduino IDE, pernah <em>Done uploading</em>, sketchbook rapi).</p>
<p><strong>Cara pakai artikel ini (urutan kerja):</strong></p>
<ol>
<li><strong>Buka Arduino IDE</strong> dulu (bukan Laragon / terminal web).</li>
<li>Baca bagian variabel + lihat gambar kotak di bawah.</li>
<li>Buat sketch <code>FS12_hello</code> → <strong>Upload</strong>.</li>
<li><strong>Buka Serial Monitor</strong> → set baud <strong>115200</strong> → baca teks.</li>
<li>Centang checklist 10/10 di browser.</li>
</ol>
<p><strong>Tidak perlu hari ini:</strong> sensor DHT/LDR, <code>digitalRead</code> tombol, log berulang tiap detik (itu FS-13), Laragon, <code>php artisan</code>. Hari ini tools-nya: <strong>Arduino IDE</strong> + <strong>Serial Monitor</strong> + ESP32 + kabel USB data + <strong>browser</strong> (checklist).</p>

<h2>Persiapan — buka &amp; siapkan ini dulu</h2>
<p><strong>Urutan meja kerja:</strong> sintaks diuji di Arduino IDE + Serial Monitor — bukan di terminal PHP.</p>
<ol>
<li>Buka <strong>Arduino IDE 2.x</strong>.</li>
<li>Pastikan board <strong>ESP32 Dev Module</strong> + port COM/tty sudah dipilih (FS-06/FS-11).</li>
<li>Siapkan ESP32 + kabel USB data.</li>
<li>Siapkan lokasi tombol <strong>Open Serial Monitor</strong> (ikon kaca pembesar kanan atas) — belum wajib dibuka sebelum Upload.</li>
</ol>
<p><strong>Alat yang dipakai hari ini:</strong> Arduino IDE, Serial Monitor, ESP32, USB data, browser.</p>
<p><strong>Tidak dipakai hari ini:</strong> Laragon, terminal <code>php artisan</code>, multimeter, wiring breadboard baru.</p>
{$finder}
{$board}
{$flow}

<h2>Variabel = kotak bernama</h2>
{$boxes}
<p>Contoh deklarasi + assignment:</p>
<pre><code class="language-cpp">int umur = 20;        // bilangan bulat
float suhu = 28.5;    // desimal
bool siap = true;     // true / false
String nama = "Anton"; // teks — kenalan dulu saja hari ini
</code></pre>
<p><strong>Tips nama:</strong> pakai huruf kecil + jelas (<code>suhu</code>, bukan <code>x</code>). Hindari spasi dan awalan angka.</p>
<p><strong>Catatan String:</strong> di jalur ini kita pakai <code>String</code> untuk latihan cetak. Nanti di proyek besar ada cara lebih hemat memori — cukup tahu dulu hari ini.</p>

<h2>Serial.begin &amp; Serial.println — mata kita</h2>
{$serial}
<p>Dua perintah inti:</p>
<ul>
<li><code>Serial.begin(115200);</code> — nyalakan “radio” USB Serial di kecepatan 115200.</li>
<li><code>Serial.println(...);</code> — kirim teks + ganti baris ke Serial Monitor.</li>
</ul>
<p><strong>Cara menguji di meja:</strong> setelah Upload, buka Serial Monitor di IDE → pastikan dropdown baud = <strong>115200</strong> (bukan 9600) → tekan tombol EN (reset) di board jika baris awal sudah lewat. Jangan uji dengan <code>php artisan</code>.</p>

<h2>Praktik — sketch FS12_hello</h2>
<p>Tujuan: variabel terbaca di Serial Monitor. Cetak sekali di <code>setup</code> (belum spam di <code>loop</code>).</p>
<ol>
<li>Arduino IDE → <strong>File → New Sketch</strong> → Save sebagai <code>FS12_hello</code>.</li>
<li>Ganti isi dengan kode di bawah (salin utuh).</li>
<li><strong>Verify</strong> → <strong>Upload</strong> → tunggu <em>Done uploading</em>.</li>
<li>Klik <strong>Open Serial Monitor</strong> → set <strong>115200</strong>.</li>
<li>Tekan <strong>EN</strong> di board jika perlu → baca baris Halo + nilai variabel.</li>
</ol>
<pre><code class="language-cpp">// FS12_hello — Full Stack IoT FS-12
// Cetak variabel ke Serial Monitor (baud 115200).

void setup() {
  Serial.begin(115200);
  delay(1000); // beri waktu kamu membuka Serial Monitor

  int umur = 20;
  float suhu = 28.5;
  bool siap = true;
  String nama = "Anton";

  Serial.println("Halo dari ESP32 — FS12_hello");
  Serial.print("umur = ");
  Serial.println(umur);
  Serial.print("suhu = ");
  Serial.println(suhu);
  Serial.print("siap = ");
  Serial.println(siap); // true tampil sebagai 1, false sebagai 0
  Serial.print("nama = ");
  Serial.println(nama);
}

void loop() {
  // sengaja kosong — log berulang dibahas di FS-13
}
</code></pre>
<p><strong>Cara menguji perintah di atas:</strong> uji di <strong>Arduino IDE + Serial Monitor</strong>. Sukses = teks terbaca (bukan huruf acak) pada baud 115200. Bukan perintah Laragon / web server.</p>

<h2 id="fsiot-var-checklist">Praktik — checklist variabel &amp; Serial</h2>
<p>Centang setiap langkah setelah kamu lakukan di meja. Target: <strong>10/10</strong>. Ada checklist interaktif di bawah.</p>
<ul id="fsiot-var-checklist-items">
<li>Arduino IDE sudah terbuka sebelum menulis kode</li>
<li>Board ESP32 Dev Module + port sudah dipilih</li>
<li>Paham variabel = kotak bernama (tipe + nama + nilai)</li>
<li>Bisa bedakan int, float, bool, dan String secara kasar</li>
<li>Sketch disimpan sebagai FS12_hello</li>
<li>Upload berhasil — Done uploading</li>
<li>Serial Monitor terbuka di IDE 2</li>
<li>Baud Serial Monitor = 115200 (sama dengan Serial.begin)</li>
<li>Teks Halo / umur / suhu / nama terbaca (bukan acak)</li>
<li>Sadar: baud salah = huruf aneh; lupa buka Serial Monitor = “kosong”</li>
</ul>
<p><strong>Cara menguji checklist:</strong> centang di browser setelah praktik di IDE. Tidak perlu <code>php artisan</code>.</p>

<h2>Kesalahan yang sering terjadi</h2>
<ul>
<li><strong>Baud salah.</strong> Kode 115200, dropdown masih 9600 → teks acak. Samakan keduanya.</li>
<li><strong>Menyalin baud 9600 dari screenshot internet.</strong> Banyak contoh lama memakai 9600. Di FS-12 kita pakai <strong>115200</strong> — ikuti kode artikel ini.</li>
<li><strong>Serial Monitor belum dibuka.</strong> Upload sukses tapi “tidak ada teks” — buka panel Serial dulu.</li>
<li><strong>Membuka Serial sebelum Upload selesai.</strong> Kadang port bentrok. Tutup dulu, Upload, buka lagi.</li>
<li><strong>Lupa <code>Serial.begin</code>.</strong> Tanpa itu, println tidak muncul.</li>
<li><strong>Menguji di terminal web.</strong> Perintah sketch tidak dijalankan Laragon — hanya di board lewat IDE.</li>
<li><strong>Nama variabel tidak jelas.</strong> <code>a</code>/<code>b</code> membingungkan; pakai <code>umur</code>/<code>suhu</code>.</li>
<li><strong>Mengisi <code>loop</code> dengan println tanpa jeda.</strong> Nanti dibahas FS-13 — hari ini biarkan kosong.</li>
</ul>

<h2>Selanjutnya</h2>
<p><strong>Intinya:</strong> kalau variabel tercetak rapi di Serial Monitor baud 115200, FS-12 selesai.</p>
<p>Lanjut ke <strong>FS-13</strong> (Serial jadi sahabat debug: log teratur, delay, jangan flood) saat modulnya terbit.</p>
<p>Daftar modul lengkap: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $finder = $this->serialFinderSvgEn();
        $board = $this->boardFigureEn();
        $boxes = $this->boxesSvgEn();
        $serial = $this->serialSvgEn();
        $flow = $this->workflowSvgEn();

        return <<<HTML
<h2>Introduction — why do we need “boxes”?</h2>
<p>This article is <strong>#82 (this article)</strong> · module <strong>FS-12</strong> on the path <em>Full Stack IoT Developer — From Zero</em>. In <strong>FS-11</strong> you already know sketches, <code>setup</code>/<code>loop</code>, and Upload. Today we fill the “recipe” with <strong>variables</strong> (boxes that store numbers/text), then view them in the <strong>Serial Monitor</strong>.</p>
<p><strong>Analogy:</strong> a variable is a labeled box on the desk. Type = kind of box. Value = contents. Serial Monitor = a camera showing the box contents on your laptop screen.</p>
<p><strong>Prerequisites:</strong> FS-11 (Arduino IDE, at least one <em>Done uploading</em>, tidy sketchbook).</p>
<p><strong>How to use this article (work order):</strong></p>
<ol>
<li><strong>Open Arduino IDE</strong> first (not Laragon / a web terminal).</li>
<li>Read the variables section + look at the box figure below.</li>
<li>Create sketch <code>FS12_hello</code> → <strong>Upload</strong>.</li>
<li><strong>Open Serial Monitor</strong> → set baud <strong>115200</strong> → read the text.</li>
<li>Tick the 10/10 checklist in the browser.</li>
</ol>
<p><strong>Not needed today:</strong> DHT/LDR sensors, button <code>digitalRead</code>, one-second log spam (that is FS-13), Laragon, <code>php artisan</code>. Today's tools: <strong>Arduino IDE</strong> + <strong>Serial Monitor</strong> + ESP32 + USB data cable + <strong>browser</strong> (checklist).</p>

<h2>Preparation — open &amp; gather these first</h2>
<p><strong>Desk order:</strong> syntax is tested in Arduino IDE + Serial Monitor — not in a PHP terminal.</p>
<ol>
<li>Open <strong>Arduino IDE 2.x</strong>.</li>
<li>Confirm board <strong>ESP32 Dev Module</strong> + COM/tty port are selected (FS-06/FS-11).</li>
<li>Prepare the ESP32 + USB data cable.</li>
<li>Locate <strong>Open Serial Monitor</strong> (magnifying-glass icon, top right) — you do not have to open it before Upload.</li>
</ol>
<p><strong>Tools used today:</strong> Arduino IDE, Serial Monitor, ESP32, USB data, browser.</p>
<p><strong>Not used today:</strong> Laragon, <code>php artisan</code> terminal, multimeter, new breadboard wiring.</p>
{$finder}
{$board}
{$flow}

<h2>A variable = a named box</h2>
{$boxes}
<p>Example declaration + assignment:</p>
<pre><code class="language-cpp">int age = 20;          // whole number
float tempC = 28.5;    // decimal
bool ready = true;     // true / false
String name = "Anton"; // text — just meet it gently today
</code></pre>
<p><strong>Naming tip:</strong> use clear lowercase names (<code>tempC</code>, not <code>x</code>). Avoid spaces and leading digits.</p>
<p><strong>String note:</strong> we use <code>String</code> for print practice. Larger projects later may prefer leaner text handling — knowing the idea is enough today.</p>

<h2>Serial.begin &amp; Serial.println — our eyes</h2>
{$serial}
<p>Two core calls:</p>
<ul>
<li><code>Serial.begin(115200);</code> — turn on USB Serial radio at 115200.</li>
<li><code>Serial.println(...);</code> — send text + newline to Serial Monitor.</li>
</ul>
<p><strong>How to test at the desk:</strong> after Upload, open Serial Monitor in the IDE → set baud dropdown to <strong>115200</strong> (not 9600) → press the board EN (reset) button if early lines already passed. Do not test with <code>php artisan</code>.</p>

<h2>Practice — sketch FS12_hello</h2>
<p>Goal: variables appear in Serial Monitor. Print once in <code>setup</code> (no spam in <code>loop</code> yet).</p>
<ol>
<li>Arduino IDE → <strong>File → New Sketch</strong> → Save as <code>FS12_hello</code>.</li>
<li>Replace contents with the code below (copy whole).</li>
<li><strong>Verify</strong> → <strong>Upload</strong> → wait for <em>Done uploading</em>.</li>
<li>Click <strong>Open Serial Monitor</strong> → set <strong>115200</strong>.</li>
<li>Press <strong>EN</strong> on the board if needed → read the Hello line + variable values.</li>
</ol>
<pre><code class="language-cpp">// FS12_hello — Full Stack IoT FS-12
// Print variables to Serial Monitor (baud 115200).

void setup() {
  Serial.begin(115200);
  delay(1000); // give you time to open Serial Monitor

  int age = 20;
  float tempC = 28.5;
  bool ready = true;
  String name = "Anton";

  Serial.println("Hello from ESP32 — FS12_hello");
  Serial.print("age = ");
  Serial.println(age);
  Serial.print("tempC = ");
  Serial.println(tempC);
  Serial.print("ready = ");
  Serial.println(ready); // true shows as 1, false as 0
  Serial.print("name = ");
  Serial.println(name);
}

void loop() {
  // intentionally empty — repeating logs are FS-13
}
</code></pre>
<p><strong>How to test the code above:</strong> test in <strong>Arduino IDE + Serial Monitor</strong>. Success = readable text (not garbage) at baud 115200. Not a Laragon / web-server command.</p>

<h2 id="fsiot-var-checklist">Practice — variables &amp; Serial checklist</h2>
<p>Tick each step after you do it at the desk. Target: <strong>10/10</strong>. An interactive checklist is below.</p>
<ul id="fsiot-var-checklist-items">
<li>Arduino IDE is open before writing code</li>
<li>ESP32 Dev Module board + port are selected</li>
<li>I understand: variable = named box (type + name + value)</li>
<li>I can roughly tell int, float, bool, and String apart</li>
<li>Sketch saved as FS12_hello</li>
<li>Upload succeeded — Done uploading</li>
<li>Serial Monitor is open in IDE 2</li>
<li>Serial Monitor baud = 115200 (matches Serial.begin)</li>
<li>Hello / age / temp / name lines are readable (not garbage)</li>
<li>I know: wrong baud = weird characters; no Serial Monitor = “blank”</li>
</ul>
<p><strong>How to test the checklist:</strong> tick it in the browser after IDE practice. No <code>php artisan</code> required.</p>

<h2>Common mistakes</h2>
<ul>
<li><strong>Wrong baud.</strong> Code uses 115200, dropdown still 9600 → garbage text. Match both.</li>
<li><strong>Copying 9600 from an internet screenshot.</strong> Many old examples use 9600. In FS-12 we use <strong>115200</strong> — follow this article's code.</li>
<li><strong>Serial Monitor not open.</strong> Upload works but “no text” — open the Serial panel first.</li>
<li><strong>Opening Serial before Upload finishes.</strong> Ports can conflict. Close it, Upload, open again.</li>
<li><strong>Forgot <code>Serial.begin</code>.</strong> Without it, println never appears.</li>
<li><strong>Testing in a web terminal.</strong> Sketch commands do not run in Laragon — only on the board via the IDE.</li>
<li><strong>Unclear variable names.</strong> <code>a</code>/<code>b</code> confuse you; use <code>age</code>/<code>tempC</code>.</li>
<li><strong>Filling <code>loop</code> with println and no pause.</strong> That is FS-13 — leave it empty today.</li>
</ul>

<h2>Next steps</h2>
<p><strong>In short:</strong> if variables print cleanly in Serial Monitor at 115200 baud, FS-12 is done.</p>
<p>Continue to <strong>FS-13</strong> (Serial as a debug friend: steady logs, delay, no flood) when that module publishes.</p>
<p>Full module list: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }
}
