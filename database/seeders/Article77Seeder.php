<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article77Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first()
            ?? Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $iotCat) {
            throw new \RuntimeException('User atau kategori iot-smart-device/esp32-arduino tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-multimeter-untuk-awam';

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
                'title'              => 'Multimeter untuk orang awam: ukur 3.3V & cek kabel',
                'title_en'           => 'Multimeter for beginners: measure 3.3V & test wires',
                'excerpt'            => 'FS-07 / #77: Mode V DC & continuity, pegang probe aman, ukur 3V3/5V di ESP32, tes jumper putus. Tools-first tanpa sketch.',
                'excerpt_en'         => 'FS-07 / #77: V DC & continuity modes, safe probes, measure 3V3/5V on ESP32, test a jumper wire. Tools-first, no sketch.',
                'body'               => $this->body(),
                'body_en'            => $this->bodyEn(),
                'status'             => 'draft',
                'is_featured'        => false,
                'published_at'       => null,
                'seo_title'          => 'Multimeter Awam — Ukur 3.3V ESP32 & Cek Kabel — Full Stack IoT #77',
                'seo_title_en'       => 'Beginner Multimeter — Measure ESP32 3.3V & Test Wires — Full Stack IoT #77',
                'seo_description'    => 'Belajar multimeter dari nol: V DC, continuity beep, ukur 3V3 & 5V di DevKitC-1, cek jumper. Modul FS-07 Full Stack IoT.',
                'seo_description_en' => 'Learn a multimeter from zero: V DC, continuity beep, measure 3V3 & 5V on DevKitC-1, test a jumper. Full Stack IoT FS-07.',
            ]
        );

        if ($article->published_at !== null || $article->status !== 'draft') {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['fullstack-iot', 'iot', 'esp32'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel #77 / FS-07 tersimpan sebagai DRAFT: '.$article->title);
    }

    private function multimeterFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/kit-multimeter.jpg" width="1200" height="900" alt="Multimeter digital — layar, dial, dan dua probe" loading="eager" style="width:100%;height:auto;max-height:420px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    Multimeter digital tipikal: <strong>dial</strong> (putar mode), <strong>layar</strong> (angka), <strong>probe merah &amp; hitam</strong> (sentuh titik ukur).
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:2017_Cyfrowy_miernik_uniwersalny.jpg" rel="noopener noreferrer" target="_blank">Jacek Halicki — digital multimeter</a> · Wikimedia Commons (CC BY-SA 4.0).
  </figcaption>
</figure>
HTML;
    }

    private function multimeterFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/kit-multimeter.jpg" width="1200" height="900" alt="Digital multimeter — display, dial, and two probes" loading="eager" style="width:100%;height:auto;max-height:420px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    A typical digital multimeter: <strong>dial</strong> (pick the mode), <strong>display</strong> (the number), <strong>red &amp; black probes</strong> (touch the test points).
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:2017_Cyfrowy_miernik_uniwersalny.jpg" rel="noopener noreferrer" target="_blank">Jacek Halicki — digital multimeter</a> · Wikimedia Commons (CC BY-SA 4.0).
  </figcaption>
</figure>
HTML;
    }

    private function pinoutFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/esp32-devkitc-1-pinlayout.jpg" width="1200" height="800" alt="Pinout ESP32-DevKitC-1 — cari label 3V3, 5V, dan GND" loading="lazy" style="width:100%;height:auto;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    Hari ini fokus ke tiga label: <strong>3V3</strong>, <strong>5V</strong>, dan <strong>GND</strong> — jangan ukur pin GPIO acak dulu.
    <br>Sumber gambar: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/boards/ESP32-DevKitC-1.html" rel="noopener noreferrer" target="_blank">Espressif — ESP32-DevKitC-1</a> (dokumen resmi).
  </figcaption>
</figure>
HTML;
    }

    private function pinoutFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/esp32-devkitc-1-pinlayout.jpg" width="1200" height="800" alt="ESP32-DevKitC-1 pinout — find 3V3, 5V, and GND labels" loading="lazy" style="width:100%;height:auto;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    Today focus on three labels: <strong>3V3</strong>, <strong>5V</strong>, and <strong>GND</strong> — do not measure random GPIO pins yet.
    <br>Image source: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/boards/ESP32-DevKitC-1.html" rel="noopener noreferrer" target="_blank">Espressif — ESP32-DevKitC-1</a> (official docs).
  </figcaption>
</figure>
HTML;
    }

    private function workflowSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Alur latihan multimeter" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 160" width="100%" height="auto" role="img" aria-label="Alur ukur">
  <text x="360" y="24" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700">Alur hari ini — multimeter di tangan</text>
  <rect x="10" y="45" width="125" height="70" rx="6" fill="#E3F2FD" stroke="#1a1a1a" stroke-width="2"/>
  <text x="72" y="72" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">1 Dial</text>
  <text x="72" y="92" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">V DC (20V)</text>
  <text x="145" y="82" font-family="system-ui,sans-serif" font-size="18">→</text>
  <rect x="155" y="45" width="125" height="70" rx="6" fill="#E8F5E9" stroke="#1a1a1a" stroke-width="2"/>
  <text x="217" y="72" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">2 Ukur 3V3</text>
  <text x="217" y="92" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">3V3 ↔ GND</text>
  <text x="290" y="82" font-family="system-ui,sans-serif" font-size="18">→</text>
  <rect x="300" y="45" width="125" height="70" rx="6" fill="#FFF8E1" stroke="#1a1a1a" stroke-width="2"/>
  <text x="362" y="72" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">3 Ukur 5V</text>
  <text x="362" y="92" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">5V ↔ GND</text>
  <text x="435" y="82" font-family="system-ui,sans-serif" font-size="18">→</text>
  <rect x="445" y="45" width="125" height="70" rx="6" fill="#F3E5F5" stroke="#1a1a1a" stroke-width="2"/>
  <text x="507" y="72" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">4 Beep</text>
  <text x="507" y="92" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">continuity</text>
  <text x="580" y="82" font-family="system-ui,sans-serif" font-size="18">→</text>
  <rect x="590" y="45" width="120" height="70" rx="6" fill="#E8F5E9" stroke="#1a1a1a" stroke-width="2"/>
  <text x="650" y="72" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">5 Tabel</text>
  <text x="650" y="92" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#2E7D32">catat angka OK</text>
  <text x="360" y="140" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Buatan Koding Indonesia</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Urutan kerja modul ini (buatan Koding Indonesia). Jangan loncat ke continuity sebelum paham mode V DC.</figcaption>
</figure>
SVG;
    }

    private function workflowSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Multimeter practice flow" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 160" width="100%" height="auto" role="img" aria-label="Measure flow">
  <text x="360" y="24" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700">Today’s flow — multimeter in hand</text>
  <rect x="10" y="45" width="125" height="70" rx="6" fill="#E3F2FD" stroke="#1a1a1a" stroke-width="2"/>
  <text x="72" y="72" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">1 Dial</text>
  <text x="72" y="92" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">V DC (20V)</text>
  <text x="145" y="82" font-family="system-ui,sans-serif" font-size="18">→</text>
  <rect x="155" y="45" width="125" height="70" rx="6" fill="#E8F5E9" stroke="#1a1a1a" stroke-width="2"/>
  <text x="217" y="72" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">2 Read 3V3</text>
  <text x="217" y="92" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">3V3 ↔ GND</text>
  <text x="290" y="82" font-family="system-ui,sans-serif" font-size="18">→</text>
  <rect x="300" y="45" width="125" height="70" rx="6" fill="#FFF8E1" stroke="#1a1a1a" stroke-width="2"/>
  <text x="362" y="72" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">3 Read 5V</text>
  <text x="362" y="92" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">5V ↔ GND</text>
  <text x="435" y="82" font-family="system-ui,sans-serif" font-size="18">→</text>
  <rect x="445" y="45" width="125" height="70" rx="6" fill="#F3E5F5" stroke="#1a1a1a" stroke-width="2"/>
  <text x="507" y="72" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">4 Beep</text>
  <text x="507" y="92" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">continuity</text>
  <text x="580" y="82" font-family="system-ui,sans-serif" font-size="18">→</text>
  <rect x="590" y="45" width="120" height="70" rx="6" fill="#E8F5E9" stroke="#1a1a1a" stroke-width="2"/>
  <text x="650" y="72" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">5 Table</text>
  <text x="650" y="92" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#2E7D32">write it OK</text>
  <text x="360" y="140" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">By Koding Indonesia</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Work order in this module (by Koding Indonesia). Do not jump to continuity before you understand V DC mode.</figcaption>
</figure>
SVG;
    }

    private function dialSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Mode dial multimeter" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 200" width="100%" height="auto" role="img" aria-label="Dial modes">
  <text x="360" y="28" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700">Tiga mode yang kamu sentuh hari ini</text>
  <rect x="40" y="50" width="200" height="110" rx="8" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2"/>
  <text x="140" y="82" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700">V DC</text>
  <line x1="118" y1="90" x2="162" y2="90" stroke="#2E7D32" stroke-width="2" stroke-dasharray="5,3"/>
  <text x="140" y="108" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12">Ukur tegangan</text>
  <text x="140" y="130" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#2E7D32">Range 20V atau AUTO</text>
  <rect x="260" y="50" width="200" height="110" rx="8" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2"/>
  <text x="360" y="85" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700">Continuity</text>
  <text x="360" y="108" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12">Cek kabel hidup</text>
  <text x="360" y="130" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#2E7D32">Simbol gelombang + beep</text>
  <rect x="480" y="50" width="200" height="110" rx="8" fill="#FFEBEE" stroke="#C62828" stroke-width="2"/>
  <text x="580" y="85" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700">A / mA</text>
  <text x="580" y="108" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#C62828">JANGAN hari ini</text>
  <text x="580" y="130" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#C62828">Salah mode = bahaya</text>
  <text x="360" y="185" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Buatan Koding Indonesia — label dial bisa sedikit beda merek</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Mode aman vs mode yang ditunda (buatan Koding Indonesia). Pemula cukup V DC + continuity dulu.</figcaption>
</figure>
SVG;
    }

    private function dialSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Multimeter dial modes" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 200" width="100%" height="auto" role="img" aria-label="Dial modes">
  <text x="360" y="28" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700">Three modes you touch today</text>
  <rect x="40" y="50" width="200" height="110" rx="8" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2"/>
  <text x="140" y="82" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700">V DC</text>
  <line x1="118" y1="90" x2="162" y2="90" stroke="#2E7D32" stroke-width="2" stroke-dasharray="5,3"/>
  <text x="140" y="108" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12">Measure voltage</text>
  <text x="140" y="130" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#2E7D32">20V range or AUTO</text>
  <rect x="260" y="50" width="200" height="110" rx="8" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2"/>
  <text x="360" y="85" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700">Continuity</text>
  <text x="360" y="108" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12">Test a live wire</text>
  <text x="360" y="130" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#2E7D32">Wave icon + beep</text>
  <rect x="480" y="50" width="200" height="110" rx="8" fill="#FFEBEE" stroke="#C62828" stroke-width="2"/>
  <text x="580" y="85" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700">A / mA</text>
  <text x="580" y="108" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#C62828">NOT today</text>
  <text x="580" y="130" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#C62828">Wrong mode = danger</text>
  <text x="360" y="185" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">By Koding Indonesia — dial labels vary slightly by brand</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Safe modes vs postponed ones (by Koding Indonesia). Beginners only need V DC + continuity first.</figcaption>
</figure>
SVG;
    }

    private function probeSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Cara pegang probe multimeter" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 200" width="100%" height="auto" role="img" aria-label="Probe grip">
  <text x="360" y="28" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700">Probe — colok &amp; pegang yang benar</text>
  <rect x="30" y="50" width="320" height="120" rx="6" fill="#fff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="50" y="78" font-family="system-ui,sans-serif" font-size="13" font-weight="700">Merah → jack VΩmA (atau V/Ω)</text>
  <text x="50" y="100" font-family="system-ui,sans-serif" font-size="13" font-weight="700">Hitam → jack COM</text>
  <text x="50" y="130" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Jangan tukar jack saat mengukur tegangan</text>
  <rect x="370" y="50" width="320" height="120" rx="6" fill="#fff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="390" y="78" font-family="system-ui,sans-serif" font-size="13" font-weight="700">Pegang plastik probe, bukan logam</text>
  <text x="390" y="100" font-family="system-ui,sans-serif" font-size="13">Sentuh ujung logam ke pin board</text>
  <text x="390" y="130" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Meja kering · tangan tidak basah</text>
  <text x="360" y="188" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Buatan Koding Indonesia</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Jack probe &amp; cara pegang aman (buatan Koding Indonesia). Hitam ke GND dulu, baru merah ke 3V3/5V.</figcaption>
</figure>
SVG;
    }

    private function probeSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="How to hold multimeter probes" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 200" width="100%" height="auto" role="img" aria-label="Probe grip">
  <text x="360" y="28" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700">Probes — plug in &amp; hold correctly</text>
  <rect x="30" y="50" width="320" height="120" rx="6" fill="#fff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="50" y="78" font-family="system-ui,sans-serif" font-size="13" font-weight="700">Red → VΩmA jack (or V/Ω)</text>
  <text x="50" y="100" font-family="system-ui,sans-serif" font-size="13" font-weight="700">Black → COM jack</text>
  <text x="50" y="130" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Do not swap jacks when measuring voltage</text>
  <rect x="370" y="50" width="320" height="120" rx="6" fill="#fff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="390" y="78" font-family="system-ui,sans-serif" font-size="13" font-weight="700">Hold plastic handles, not metal tips</text>
  <text x="390" y="100" font-family="system-ui,sans-serif" font-size="13">Touch metal tips to board pins</text>
  <text x="390" y="130" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Dry desk · dry hands</text>
  <text x="360" y="188" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">By Koding Indonesia</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Probe jacks &amp; safe grip (by Koding Indonesia). Black to GND first, then red to 3V3/5V.</figcaption>
</figure>
SVG;
    }

    private function continuitySvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Tes continuity pada kabel jumper" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 180" width="100%" height="auto" role="img" aria-label="Continuity jumper test">
  <text x="360" y="26" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700">Tes jumper — dua probe, satu kabel</text>
  <line x1="120" y1="100" x2="600" y2="100" stroke="#1565C0" stroke-width="4" stroke-linecap="round"/>
  <circle cx="120" cy="100" r="10" fill="#1a1a1a" stroke="#fff" stroke-width="2"/>
  <circle cx="600" cy="100" r="10" fill="#C62828" stroke="#fff" stroke-width="2"/>
  <text x="120" y="130" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12">Probe hitam</text>
  <text x="600" y="130" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12">Probe merah</text>
  <rect x="290" y="70" width="140" height="36" rx="6" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2"/>
  <text x="360" y="94" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#1B5E20">BEEP = kabel OK</text>
  <text x="360" y="165" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Buatan Koding Indonesia — USB board dicabut dulu</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Continuity: sentuh probe di dua ujung kabel jumper yang sama (buatan Koding Indonesia).</figcaption>
</figure>
SVG;
    }

    private function continuitySvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Continuity test on a jumper wire" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 180" width="100%" height="auto" role="img" aria-label="Continuity jumper test">
  <text x="360" y="26" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700">Test a jumper — two probes, one wire</text>
  <line x1="120" y1="100" x2="600" y2="100" stroke="#1565C0" stroke-width="4" stroke-linecap="round"/>
  <circle cx="120" cy="100" r="10" fill="#1a1a1a" stroke="#fff" stroke-width="2"/>
  <circle cx="600" cy="100" r="10" fill="#C62828" stroke="#fff" stroke-width="2"/>
  <text x="120" y="130" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12">Black probe</text>
  <text x="600" y="130" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12">Red probe</text>
  <rect x="290" y="70" width="140" height="36" rx="6" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2"/>
  <text x="360" y="94" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#1B5E20">BEEP = wire OK</text>
  <text x="360" y="165" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">By Koding Indonesia — unplug USB from the board first</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Continuity: touch probes on both ends of the same jumper wire (by Koding Indonesia).</figcaption>
</figure>
SVG;
    }

    private function measurePointSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Titik ukur 3V3 dan GND" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 180" width="100%" height="auto" role="img" aria-label="Measure points">
  <text x="360" y="26" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700">Ukur tegangan: dua pin saja</text>
  <rect x="80" y="50" width="240" height="90" rx="6" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2"/>
  <text x="200" y="78" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">3V3 ↔ GND</text>
  <text x="200" y="100" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12">Target ~3.2 – 3.4 V</text>
  <text x="200" y="122" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">Hitam di GND, merah di 3V3</text>
  <rect x="400" y="50" width="240" height="90" rx="6" fill="#FFF8E1" stroke="#F9A825" stroke-width="2"/>
  <text x="520" y="78" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">5V ↔ GND</text>
  <text x="520" y="100" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12">Target ~4.8 – 5.2 V</text>
  <text x="520" y="122" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">USB menyala · board diam</text>
  <text x="360" y="165" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Buatan Koding Indonesia — jangan sentuh dua pin sekaligus dengan jari</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Dua pasangan pin untuk latihan hari ini (buatan Koding Indonesia). Lihat pinout resmi untuk lokasi fisiknya.</figcaption>
</figure>
SVG;
    }

    private function measurePointSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="3V3 and GND measure points" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 180" width="100%" height="auto" role="img" aria-label="Measure points">
  <text x="360" y="26" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700">Measure voltage: two pin pairs only</text>
  <rect x="80" y="50" width="240" height="90" rx="6" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2"/>
  <text x="200" y="78" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">3V3 ↔ GND</text>
  <text x="200" y="100" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12">Target ~3.2 – 3.4 V</text>
  <text x="200" y="122" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">Black on GND, red on 3V3</text>
  <rect x="400" y="50" width="240" height="90" rx="6" fill="#FFF8E1" stroke="#F9A825" stroke-width="2"/>
  <text x="520" y="78" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">5V ↔ GND</text>
  <text x="520" y="100" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12">Target ~4.8 – 5.2 V</text>
  <text x="520" y="122" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">USB powered · board idle</text>
  <text x="360" y="165" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">By Koding Indonesia — do not bridge pins with your fingers</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Two pin pairs for today’s practice (by Koding Indonesia). Use the official pinout for physical locations.</figcaption>
</figure>
SVG;
    }

    private function body(): string
    {
        $flow = $this->workflowSvgId();
        $meter = $this->multimeterFigureId();
        $dial = $this->dialSvgId();
        $probe = $this->probeSvgId();
        $pinout = $this->pinoutFigureId();
        $points = $this->measurePointSvgId();
        $continuity = $this->continuitySvgId();

        return <<<HTML
<h2>Pendahuluan — kenapa multimeter sekarang?</h2>
<p>Artikel ini adalah <strong>#77 (ini)</strong> · modul <strong>FS-07</strong> di jalur <em>Full Stack IoT Developer — Dari Nol</em>. Di <strong>FS-06</strong> komputermu sudah mengenali board. Hari ini kamu belajar <strong>alat ukur</strong> supaya bisa memverifikasi tegangan dan kabel sebelum rangkaian makin rumit.</p>
<p><strong>Awam:</strong> multimeter = “termometer” listrik — tidak menambah program, hanya membaca kondisi board.</p>
<p><strong>Prasyarat:</strong> kebiasaan aman FS-05 (kabel data, meja kering) + board sudah dikenali PC (FS-06). <strong>Tidak ada perintah Arduino, terminal, atau <code>php artisan</code> hari ini</strong> — hanya multimeter di tangan.</p>

<h2>Persiapan — alat yang kamu buka hari ini</h2>
<p><strong>Alat yang dipakai di artikel ini</strong> (belum Arduino IDE untuk latihan, belum breadboard wiring, belum Laragon):</p>
<ul>
<li><strong>Multimeter digital</strong> — dengan probe merah &amp; hitam (dari kit FS-04).</li>
<li><strong>ESP32-DevKitC-1</strong> — dicolok USB <strong>data</strong> ke PC atau charger 5V stabil (board menyala, tidak perlu upload sketch baru).</li>
<li><strong>1 kabel jumper</strong> — untuk tes continuity (beep).</li>
<li><strong>Kertas + pena</strong> — isi tabel ukur di bawah (versi kertas).</li>
<li><strong>Browser</strong> (opsional) — buka artikel ini di layar kedua supaya pinout tetap terlihat saat mengukur.</li>
</ul>
<p><strong>Awam — urutan buka:</strong> (1) baca sampai bagian dial → (2) colok probe ke jack yang benar → (3) putar dial ke <strong>V DC</strong> → (4) colok USB board → (5) ukur 3V3 lalu 5V → (6) ganti ke continuity → (7) tes jumper → (8) isi tabel.</p>
<p><strong>Hasil yang kamu cari hari ini:</strong> layar multimeter menunjukkan angka masuk akal (~3.3V dan ~5V) + jumper berbunyi <em>beep</em> saat mode continuity.</p>

{$flow}

<h2>Ingat dulu — kebiasaan FS-05 &amp; FS-06</h2>
<p>Ukur hanya saat board <strong>stabil di meja</strong>, kabel <strong>data</strong>, tangan kering. Jangan ukur sambil mengganti kabel (cabut USB dulu). Board boleh menyala via USB — <strong>jangan sentuh pin dengan jari</strong> saat probe menyentuh.</p>

<h2>Kenali multimeter kamu</h2>
<p>Sebelum menyentuh board, kenali tiga bagian: <strong>dial</strong> (mode), <strong>layar</strong> (angka), <strong>probe</strong> (merah/hitam).</p>
{$meter}
{$dial}
{$probe}

<h2>Mode V DC — ukur tegangan</h2>
<ol>
<li>Putar dial ke <strong>V DC</strong> — cari tulisan <strong>V</strong> dengan garis putus-putus di bawahnya (bukan <strong>V AC</strong> ~ gelombang sinus).</li>
<li>Pilih range <strong>20V</strong> atau <strong>AUTO</strong> jika ada.</li>
<li>Probe hitam ke pin <strong>GND</strong>, probe merah ke pin yang mau diukur.</li>
<li>Baca angka di layar — tunggu 1–2 detik sampai stabil.</li>
</ol>
<p><strong>Awam — cara menguji:</strong> sentuh merah &amp; hitam ke dua ujung baterai AA (jika ada) — harus muncul ~1.5V. Kalau layar kosong, cek baterai multimeter atau jack probe.</p>

<h2>Di mana pin 3V3, 5V, dan GND?</h2>
<p>Gunakan pinout resmi DevKitC-1. Hari ini <strong>hanya</strong> tiga label ini:</p>
{$pinout}
{$points}
<ol>
<li>Colok USB board (LED power menyala).</li>
<li>Hitam ke <strong>GND</strong>, merah ke <strong>3V3</strong> → catat angka (target <strong>3.2 – 3.4 V</strong>).</li>
<li>Tetap hitam di <strong>GND</strong>, merah pindah ke <strong>5V</strong> → catat (target <strong>4.8 – 5.2 V</strong>).</li>
</ol>
<p><strong>Awam:</strong> 3V3 = “bahasa” pin GPIO ESP32 · 5V = dari jalur USB/charger · GND = referensi nol volt.</p>

<h2>Mode continuity — cek kabel jumper</h2>
{$continuity}
<ol>
<li><strong>Cabut USB board</strong> dulu (lebih aman untuk pemula saat tes kabel).</li>
<li>Putar dial ke simbol <strong>continuity</strong> (gelombang / speaker / beep).</li>
<li>Sentuh probe ke dua ujung <strong>kabel jumper yang sama</strong>.</li>
<li>Kabel bagus → <strong>beep</strong> + angka mendekati 0 Ω. Kabel putus → tidak beep.</li>
</ol>
<p><strong>Awam — cara menguji:</strong> sentuh dua probe langsung (tanpa kabel) — harus beep. Itu bukti mode continuity aktif.</p>

<h2>Praktik — tabel ukur (isi di kertas)</h2>
<p>Salin tabel ini ke buku catatan. Isi kolom <strong>Hasil kamu</strong> setelah mengukur di meja.</p>
<table style="width:100%;border-collapse:collapse;margin:1rem 0;font-size:0.95rem">
<thead>
<tr style="background:#F5F5F0;border:2px solid #1a1a1a">
<th style="padding:0.5rem;border:1px solid #1a1a1a;text-align:left">Uji</th>
<th style="padding:0.5rem;border:1px solid #1a1a1a;text-align:left">Target</th>
<th style="padding:0.5rem;border:1px solid #1a1a1a;text-align:left">Hasil kamu</th>
<th style="padding:0.5rem;border:1px solid #1a1a1a;text-align:left">Lulus?</th>
</tr>
</thead>
<tbody>
<tr><td style="padding:0.5rem;border:1px solid #ccc">3V3 ↔ GND (USB nyala)</td><td style="padding:0.5rem;border:1px solid #ccc">3.2 – 3.4 V</td><td style="padding:0.5rem;border:1px solid #ccc">_____ V</td><td style="padding:0.5rem;border:1px solid #ccc">☐</td></tr>
<tr><td style="padding:0.5rem;border:1px solid #ccc">5V ↔ GND (USB nyala)</td><td style="padding:0.5rem;border:1px solid #ccc">4.8 – 5.2 V</td><td style="padding:0.5rem;border:1px solid #ccc">_____ V</td><td style="padding:0.5rem;border:1px solid #ccc">☐</td></tr>
<tr><td style="padding:0.5rem;border:1px solid #ccc">Jumper bagus (continuity)</td><td style="padding:0.5rem;border:1px solid #ccc">Beep / ~0 Ω</td><td style="padding:0.5rem;border:1px solid #ccc">☐ beep</td><td style="padding:0.5rem;border:1px solid #ccc">☐</td></tr>
<tr><td style="padding:0.5rem;border:1px solid #ccc">Jumper putus (simulasi)</td><td style="padding:0.5rem;border:1px solid #ccc">Tidak beep</td><td style="padding:0.5rem;border:1px solid #ccc">☐ diam</td><td style="padding:0.5rem;border:1px solid #ccc">☐</td></tr>
</tbody>
</table>
<p><strong>Awam — cara menguji:</strong> minimal 3 baris pertama terisi angka masuk akal. Opsional: foto layar multimeter + tabel tulisan tangan.</p>

<h2 id="fsiot-multimeter-checklist">Praktik — checklist multimeter 10 poin</h2>
<p>Centang tiap langkah yang sudah kamu selesaikan. Target: <strong>10/10</strong>. Ada <strong>checklist interaktif</strong> di bawah; versi kertas tetap tersedia.</p>
<ul id="fsiot-multimeter-checklist-items">
<li>Saya ingat FS-05: meja kering, kabel data, cabut USB sebelum ganti kabel</li>
<li>Probe merah di jack V/Ω, hitam di COM — tidak tertukar</li>
<li>Dial di mode V DC (bukan A / mA, bukan V AC)</li>
<li>Saya mengukur 3V3–GND dan mendapat ~3.2–3.4 V</li>
<li>Saya mengukur 5V–GND dan mendapat ~4.8–5.2 V</li>
<li>Saya tidak menyentuh pin board dengan jari saat mengukur</li>
<li>Saya pindah ke mode continuity dengan USB board dicabut</li>
<li>Kabel jumper bagus memberi beep / ~0 Ω</li>
<li>Saya tahu beda kabel putus (tidak beep) vs bagus</li>
<li>Tabel ukur di kertas sudah terisi minimal 3 baris</li>
</ul>
<p><strong>Awam — cara menguji:</strong> kerjakan checklist di browser. Tidak perlu menjalankan perintah web server atau Arduino IDE.</p>

<h2>Kesalahan umum awam</h2>
<ul>
<li><strong>Mode A (ampere) untuk ukur tegangan.</strong> Bahaya — hanya mode V DC hari ini.</li>
<li><strong>Probe di jack salah.</strong> Merah harus V/Ω, hitam COM.</li>
<li><strong>V AC dipilih untuk board DC.</strong> Angka jadi tidak masuk akal.</li>
<li><strong>Jari menyentuh logam pin.</strong> Bisa short atau sakit — pegang plastik probe.</li>
<li><strong>Mengukur sambil hot-plug kabel.</strong> Cabut USB dulu saat ganti wiring.</li>
<li><strong>Continuity di board yang masih powered.</strong> Cabut USB dulu untuk tes jumper.</li>
<li><strong>Panic saat angka 3.28V bukan persis 3.30V.</strong> Normal — yang penting rentang ~3.2–3.4V.</li>
</ul>

<h2>Lanjut belajar</h2>
<p>Setelah FS-07, langkah alami berikutnya adalah <strong>FS-08 — listrik mini: tegangan, arus, resistansi</strong> (kenapa LED butuh resistor). Artikel itu belum dilink di sini sampai modulnya siap.</p>
<p>Simpan juga <a href="/belajar/fullstack-iot">halaman jalur Full Stack IoT</a> sebagai pintu masuk resmi.</p>

<h2>Kesimpulan</h2>
<p>Di <strong>#77 (ini)</strong> kamu sudah bisa membaca tegangan 3V3 &amp; 5V di DevKitC-1 dan mengetes jumper dengan continuity. Ini fondasi sebelum merangkai LED di breadboard.</p>
<p><strong>Awam:</strong> kalau kamu bisa tunjukkan foto tabel ukur dan bilang “3.3V kelihatan, jumper beep”, FS-07 selesai. Lanjut teori ringan di FS-08 saat modulnya terbit.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $flow = $this->workflowSvgEn();
        $meter = $this->multimeterFigureEn();
        $dial = $this->dialSvgEn();
        $probe = $this->probeSvgEn();
        $pinout = $this->pinoutFigureEn();
        $points = $this->measurePointSvgEn();
        $continuity = $this->continuitySvgEn();

        return <<<HTML
<h2>Introduction — why a multimeter now?</h2>
<p>This article is <strong>#77 (this article)</strong> · module <strong>FS-07</strong> on the <em>Full Stack IoT Developer — From Zero</em> path. In <strong>FS-06</strong> your computer already recognizes the board. Today you learn the <strong>measuring tool</strong> so you can verify voltage and wires before circuits get more complex.</p>
<p><strong>Beginner:</strong> a multimeter is an “electric thermometer” — it does not add code, it only reads the board’s condition.</p>
<p><strong>Prerequisites:</strong> safe habits from FS-05 (data cable, dry desk) + board recognized by the PC (FS-06). <strong>No Arduino commands, terminal, or <code>php artisan</code> today</strong> — only a multimeter in your hand.</p>

<h2>Preparation — tools you open today</h2>
<p><strong>Tools used in this article</strong> (no Arduino IDE practice yet, no breadboard wiring, no Laragon):</p>
<ul>
<li><strong>Digital multimeter</strong> — with red &amp; black probes (from the FS-04 kit).</li>
<li><strong>ESP32-DevKitC-1</strong> — plugged in with a USB <strong>data</strong> cable to a PC or stable 5V charger (board powered, no new sketch upload required).</li>
<li><strong>1 jumper wire</strong> — for continuity (beep) testing.</li>
<li><strong>Paper + pen</strong> — fill the measurement table below (paper version).</li>
<li><strong>Browser</strong> (optional) — keep this article open on a second screen so the pinout stays visible while measuring.</li>
</ul>
<p><strong>Beginner — open order:</strong> (1) read through the dial section → (2) plug probes into the correct jacks → (3) set dial to <strong>V DC</strong> → (4) plug USB into the board → (5) measure 3V3 then 5V → (6) switch to continuity → (7) test the jumper → (8) fill the table.</p>
<p><strong>What you want today:</strong> the multimeter shows sensible numbers (~3.3V and ~5V) + the jumper <em>beeps</em> in continuity mode.</p>

{$flow}

<h2>Remember first — FS-05 &amp; FS-06 habits</h2>
<p>Measure only with the board <strong>stable on the desk</strong>, <strong>data</strong> cable, dry hands. Do not measure while swapping wires (unplug USB first). The board may be powered via USB — <strong>do not touch pins with your fingers</strong> while probes are connected.</p>

<h2>Know your multimeter</h2>
<p>Before touching the board, identify three parts: <strong>dial</strong> (mode), <strong>display</strong> (number), <strong>probes</strong> (red/black).</p>
{$meter}
{$dial}
{$probe}

<h2>V DC mode — measure voltage</h2>
<ol>
<li>Turn the dial to <strong>V DC</strong> — look for <strong>V</strong> with a dashed line underneath (not <strong>V AC</strong> ~ sine wave).</li>
<li>Pick <strong>20V</strong> range or <strong>AUTO</strong> if available.</li>
<li>Black probe on <strong>GND</strong>, red probe on the pin you want to measure.</li>
<li>Read the display — wait 1–2 seconds until stable.</li>
</ol>
<p><strong>Beginner — how to test:</strong> touch red &amp; black to the ends of an AA battery (if you have one) — you should see ~1.5V. If the display is blank, check the multimeter battery or probe jacks.</p>

<h2>Where are 3V3, 5V, and GND?</h2>
<p>Use the official DevKitC-1 pinout. Today <strong>only</strong> these three labels:</p>
{$pinout}
{$points}
<ol>
<li>Plug USB into the board (power LED on).</li>
<li>Black on <strong>GND</strong>, red on <strong>3V3</strong> → write the number (target <strong>3.2 – 3.4 V</strong>).</li>
<li>Keep black on <strong>GND</strong>, move red to <strong>5V</strong> → write it (target <strong>4.8 – 5.2 V</strong>).</li>
</ol>
<p><strong>Beginner:</strong> 3V3 = GPIO logic language on ESP32 · 5V = from USB/charger path · GND = zero-volt reference.</p>

<h2>Continuity mode — test a jumper wire</h2>
{$continuity}
<ol>
<li><strong>Unplug USB from the board</strong> first (safer for beginners when testing wires).</li>
<li>Turn the dial to <strong>continuity</strong> (wave / speaker / beep symbol).</li>
<li>Touch probes to both ends of the <strong>same jumper wire</strong>.</li>
<li>Good wire → <strong>beep</strong> + reading near 0 Ω. Broken wire → no beep.</li>
</ol>
<p><strong>Beginner — how to test:</strong> touch the two probes together (no wire) — it must beep. That proves continuity mode is active.</p>

<h2>Practice — measurement table (fill on paper)</h2>
<p>Copy this table into your notebook. Fill <strong>Your result</strong> after measuring at your desk.</p>
<table style="width:100%;border-collapse:collapse;margin:1rem 0;font-size:0.95rem">
<thead>
<tr style="background:#F5F5F0;border:2px solid #1a1a1a">
<th style="padding:0.5rem;border:1px solid #1a1a1a;text-align:left">Test</th>
<th style="padding:0.5rem;border:1px solid #1a1a1a;text-align:left">Target</th>
<th style="padding:0.5rem;border:1px solid #1a1a1a;text-align:left">Your result</th>
<th style="padding:0.5rem;border:1px solid #1a1a1a;text-align:left">Pass?</th>
</tr>
</thead>
<tbody>
<tr><td style="padding:0.5rem;border:1px solid #ccc">3V3 ↔ GND (USB on)</td><td style="padding:0.5rem;border:1px solid #ccc">3.2 – 3.4 V</td><td style="padding:0.5rem;border:1px solid #ccc">_____ V</td><td style="padding:0.5rem;border:1px solid #ccc">☐</td></tr>
<tr><td style="padding:0.5rem;border:1px solid #ccc">5V ↔ GND (USB on)</td><td style="padding:0.5rem;border:1px solid #ccc">4.8 – 5.2 V</td><td style="padding:0.5rem;border:1px solid #ccc">_____ V</td><td style="padding:0.5rem;border:1px solid #ccc">☐</td></tr>
<tr><td style="padding:0.5rem;border:1px solid #ccc">Good jumper (continuity)</td><td style="padding:0.5rem;border:1px solid #ccc">Beep / ~0 Ω</td><td style="padding:0.5rem;border:1px solid #ccc">☐ beep</td><td style="padding:0.5rem;border:1px solid #ccc">☐</td></tr>
<tr><td style="padding:0.5rem;border:1px solid #ccc">Broken jumper (simulate)</td><td style="padding:0.5rem;border:1px solid #ccc">No beep</td><td style="padding:0.5rem;border:1px solid #ccc">☐ silent</td><td style="padding:0.5rem;border:1px solid #ccc">☐</td></tr>
</tbody>
</table>
<p><strong>Beginner — how to test:</strong> at least the first 3 rows filled with sensible numbers. Optional: photo of the multimeter display + handwritten table.</p>

<h2 id="fsiot-multimeter-checklist">Practice — 10-point multimeter checklist</h2>
<p>Tick each step you finished. Target: <strong>10/10</strong>. An <strong>interactive checklist</strong> is below; a paper version stays available.</p>
<ul id="fsiot-multimeter-checklist-items">
<li>I remember FS-05: dry desk, data cable, unplug USB before changing wires</li>
<li>Red probe in V/Ω jack, black in COM — not swapped</li>
<li>Dial on V DC mode (not A / mA, not V AC)</li>
<li>I measured 3V3–GND and got ~3.2–3.4 V</li>
<li>I measured 5V–GND and got ~4.8–5.2 V</li>
<li>I did not touch board pins with my fingers while measuring</li>
<li>I switched to continuity mode with USB unplugged from the board</li>
<li>A good jumper wire beeps / shows ~0 Ω</li>
<li>I know broken wire (no beep) vs good wire</li>
<li>My paper measurement table has at least 3 rows filled</li>
</ul>
<p><strong>Beginner — how to test:</strong> complete the checklist in the browser. No web server commands or Arduino IDE required.</p>

<h2>Common beginner mistakes</h2>
<ul>
<li><strong>A (ampere) mode for voltage.</strong> Dangerous — only V DC mode today.</li>
<li><strong>Probes in wrong jacks.</strong> Red must be V/Ω, black COM.</li>
<li><strong>V AC selected for a DC board.</strong> Numbers will not make sense.</li>
<li><strong>Fingers touching metal pins.</strong> Can short or hurt — hold plastic handles.</li>
<li><strong>Measuring while hot-plugging wires.</strong> Unplug USB first when changing wiring.</li>
<li><strong>Continuity on a powered board.</strong> Unplug USB first for jumper tests.</li>
<li><strong>Panicking because 3.28V is not exactly 3.30V.</strong> Normal — the ~3.2–3.4V range matters.</li>
</ul>

<h2>Continue learning</h2>
<p>After FS-07, the natural next step is <strong>FS-08 — mini electricity: voltage, current, resistance</strong> (why an LED needs a resistor). That article is not linked here until the module is ready.</p>
<p>Also bookmark the <a href="/belajar/fullstack-iot">Full Stack IoT path page</a> as the official entry.</p>

<h2>Conclusion</h2>
<p>In <strong>#77 (this article)</strong> you can read 3V3 &amp; 5V on the DevKitC-1 and test jumpers with continuity. This is the foundation before wiring an LED on a breadboard.</p>
<p><strong>Beginner:</strong> if you can show your filled table and say “3.3V looks right, jumper beeps”, FS-07 is done. Continue to light theory in FS-08 when that module publishes.</p>
HTML;
    }
}
