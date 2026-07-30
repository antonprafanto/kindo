<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article78Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first()
            ?? Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $iotCat) {
            throw new \RuntimeException('User atau kategori iot-smart-device/esp32-arduino tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-listrik-mini-tegangan-arus-resistansi';

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
                'title'              => 'Listrik mini untuk awam: kenapa LED butuh resistor',
                'title_en'           => 'Mini electricity for beginners: why an LED needs a resistor',
                'excerpt'            => 'FS-08 / #78: Analogi air, Ohm sederhana, hitung resistor LED 5mm dari 3.3V, pilih 220Ω atau 330Ω. Kertas + kalkulator HP — tanpa wiring.',
                'excerpt_en'         => 'FS-08 / #78: Water analogy, simple Ohm\'s law, calculate a 5mm LED resistor from 3.3V, pick 220Ω or 330Ω. Paper + phone calculator — no wiring.',
                'body'               => $this->body(),
                'body_en'            => $this->bodyEn(),
                'status'             => 'draft',
                'is_featured'        => false,
                'published_at'       => null,
                'seo_title'          => 'Listrik Mini Awam — Hitung Resistor LED 3.3V — Full Stack IoT #78',
                'seo_title_en'       => 'Beginner Electricity — Calculate LED Resistor 3.3V — Full Stack IoT #78',
                'seo_description'    => 'Paham V, A, Ω dengan analogi air. Hitung resistor LED 5mm dari 3.3V ESP32, pilih 220Ω atau 330Ω. Modul FS-08 tanpa breadboard.',
                'seo_description_en' => 'Understand V, A, Ω with a water analogy. Calculate a 5mm LED resistor from 3.3V ESP32, pick 220Ω or 330Ω. FS-08 module, no breadboard.',
            ]
        );

        if ($article->published_at !== null || $article->status !== 'draft') {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['fullstack-iot', 'iot', 'esp32'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel #78 / FS-08 tersimpan sebagai DRAFT: '.$article->title);
    }

    private function ledFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/kit-led-5mm.jpg" width="1200" height="900" alt="LED 5mm putih — kaki panjang anoda, kaki pendek katoda" loading="eager" style="width:100%;height:auto;max-height:360px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    LED 5mm dari kit: <strong>kaki panjang = anoda (+)</strong>, <strong>kaki pendek = katoda (-)</strong>. Hari ini belum dicolok — cukup pegang &amp; lihat bentuknya.
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:5mm_LED_Light-emitting_diode.jpg" rel="noopener noreferrer" target="_blank">Wikimedia Commons — 5mm LED</a> (CC BY-SA 3.0).
  </figcaption>
</figure>
HTML;
    }

    private function ledFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/kit-led-5mm.jpg" width="1200" height="900" alt="White 5mm LED — long leg anode, short leg cathode" loading="eager" style="width:100%;height:auto;max-height:360px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    5mm LED from the kit: <strong>long leg = anode (+)</strong>, <strong>short leg = cathode (-)</strong>. Do not plug it in today — just hold and recognize the shape.
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:5mm_LED_Light-emitting_diode.jpg" rel="noopener noreferrer" target="_blank">Wikimedia Commons — 5mm LED</a> (CC BY-SA 3.0).
  </figcaption>
</figure>
HTML;
    }

    private function resistorFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/kit-resistor.jpg" width="1200" height="900" alt="Resistor dengan cincin warna — rem arus untuk LED" loading="lazy" style="width:100%;height:auto;max-height:360px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
  Resistor = <strong>rem arus</strong>. Kit biasanya punya <strong>220Ω</strong> dan <strong>330Ω</strong> — nilai ini yang akan kita pilih setelah hitung.
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:100_ohms_5%25_axial_resistor.jpg" rel="noopener noreferrer" target="_blank">oomlout — axial resistor</a> · Wikimedia Commons (CC BY-SA 2.0).
  </figcaption>
</figure>
HTML;
    }

    private function resistorFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/kit-resistor.jpg" width="1200" height="900" alt="Resistor with color bands — current brake for an LED" loading="lazy" style="width:100%;height:auto;max-height:360px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    A resistor is a <strong>current brake</strong>. Kits usually include <strong>220Ω</strong> and <strong>330Ω</strong> — those are what we pick after the math.
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:100_ohms_5%25_axial_resistor.jpg" rel="noopener noreferrer" target="_blank">oomlout — axial resistor</a> · Wikimedia Commons (CC BY-SA 2.0).
  </figcaption>
</figure>
HTML;
    }

    private function waterAnalogySvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Analogi air untuk V A dan Ohm" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 220" width="100%" height="auto" role="img" aria-label="Water analogy">
  <text x="360" y="26" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700">Analogi air — tiga kata yang sering bikin bingung</text>
  <rect x="30" y="50" width="200" height="130" rx="8" fill="#E3F2FD" stroke="#1565C0" stroke-width="2"/>
  <text x="130" y="82" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">V = Volt</text>
  <text x="130" y="104" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12">Tekanan air</text>
  <text x="130" y="124" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">Tinggi tangki / pompa</text>
  <text x="130" y="158" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#1565C0">3.3V = tekanan board</text>
  <rect x="260" y="50" width="200" height="130" rx="8" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2"/>
  <text x="360" y="82" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">A / mA = Ampere</text>
  <text x="360" y="104" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12">Aliran air</text>
  <text x="360" y="124" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">Banyak air lewat pipa</text>
  <text x="360" y="158" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#2E7D32">10 mA = aliran aman LED</text>
  <rect x="490" y="50" width="200" height="130" rx="8" fill="#FFF8E1" stroke="#F9A825" stroke-width="2"/>
  <text x="590" y="82" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">Ohm</text>
  <text x="590" y="104" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12">Sempit pipa</text>
  <text x="590" y="124" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">Resistor = penyempit</text>
  <text x="590" y="158" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#F9A825">220 Ohm = rem arus</text>
  <text x="360" y="205" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Buatan Koding Indonesia — analogi, bukan hukum fisika persis</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Tegangan = tekanan · arus = aliran · resistansi = penyempitan (buatan Koding Indonesia).</figcaption>
</figure>
SVG;
    }

    private function waterAnalogySvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Water analogy for V A and Ohm" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 220" width="100%" height="auto" role="img" aria-label="Water analogy">
  <text x="360" y="26" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700">Water analogy — three words that confuse beginners</text>
  <rect x="30" y="50" width="200" height="130" rx="8" fill="#E3F2FD" stroke="#1565C0" stroke-width="2"/>
  <text x="130" y="82" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">V = Volt</text>
  <text x="130" y="104" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12">Water pressure</text>
  <text x="130" y="124" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">Tank height / pump</text>
  <text x="130" y="158" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#1565C0">3.3V = board pressure</text>
  <rect x="260" y="50" width="200" height="130" rx="8" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2"/>
  <text x="360" y="82" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">A / mA = Ampere</text>
  <text x="360" y="104" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12">Water flow</text>
  <text x="360" y="124" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">How much passes the pipe</text>
  <text x="360" y="158" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#2E7D32">10 mA = safe LED flow</text>
  <rect x="490" y="50" width="200" height="130" rx="8" fill="#FFF8E1" stroke="#F9A825" stroke-width="2"/>
  <text x="590" y="82" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">Ohm</text>
  <text x="590" y="104" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12">Narrow pipe</text>
  <text x="590" y="124" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">Resistor = narrowing</text>
  <text x="590" y="158" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#F9A825">220 Ohm = flow brake</text>
  <text x="360" y="205" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">By Koding Indonesia — analogy, not exact physics</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Voltage = pressure · current = flow · resistance = narrowing (by Koding Indonesia).</figcaption>
</figure>
SVG;
    }

    private function ohmSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Rumus Ohm sederhana" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 200" width="100%" height="auto" role="img" aria-label="Ohm law">
  <text x="360" y="28" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700">Rumus yang cukup hari ini</text>
  <rect x="200" y="55" width="320" height="90" rx="10" fill="#fff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="360" y="95" text-anchor="middle" font-family="system-ui,sans-serif" font-size="22" font-weight="700">V = I x R</text>
  <text x="360" y="125" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16">R = V / I</text>
  <text x="360" y="175" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Buatan Koding Indonesia — V=volt, I=ampere, R=ohm</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Ohm sederhana: tegangan = arus x resistansi. Untuk LED pakai V yang &quot;tersisa&quot; setelah LED makan tegangan.</figcaption>
</figure>
SVG;
    }

    private function ohmSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Simple Ohm law" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 200" width="100%" height="auto" role="img" aria-label="Ohm law">
  <text x="360" y="28" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700">The only formula you need today</text>
  <rect x="200" y="55" width="320" height="90" rx="10" fill="#fff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="360" y="95" text-anchor="middle" font-family="system-ui,sans-serif" font-size="22" font-weight="700">V = I x R</text>
  <text x="360" y="125" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16">R = V / I</text>
  <text x="360" y="175" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">By Koding Indonesia — V=volt, I=ampere, R=ohm</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Simple Ohm: voltage = current x resistance. For an LED use the voltage &quot;left over&quot; after the LED drops some.</figcaption>
</figure>
SVG;
    }

    private function circuitSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Rangkaian LED resistor dari 3V3" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 200" width="100%" height="auto" role="img" aria-label="LED circuit">
  <text x="360" y="26" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700">Rangkaian yang akan kita rakit di FS-09 (hari ini hanya gambar)</text>
  <line x1="60" y1="100" x2="140" y2="100" stroke="#1565C0" stroke-width="3"/>
  <text x="100" y="85" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">3V3</text>
  <polygon points="150,85 150,115 175,100" fill="#F9A825" stroke="#1a1a1a" stroke-width="2"/>
  <line x1="175" y1="100" x2="220" y2="100" stroke="#1565C0" stroke-width="3"/>
  <rect x="220" y="82" width="70" height="36" rx="4" fill="#FFF8E1" stroke="#1a1a1a" stroke-width="2"/>
  <text x="255" y="106" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">220R</text>
  <line x1="290" y1="100" x2="360" y2="100" stroke="#1565C0" stroke-width="3"/>
  <line x1="360" y1="100" x2="360" y2="150" stroke="#1565C0" stroke-width="3"/>
  <line x1="360" y1="150" x2="60" y2="150" stroke="#1565C0" stroke-width="3"/>
  <line x1="60" y1="150" x2="60" y2="100" stroke="#1565C0" stroke-width="3"/>
  <text x="360" y="175" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12">GND</text>
  <text x="200" y="130" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">LED ~2V drop</text>
  <text x="360" y="190" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Buatan Koding Indonesia — belum wiring hari ini</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Alur listrik: 3V3 → LED → resistor 220Ω → GND (buatan Koding Indonesia). Rakit fisiknya di modul berikutnya.</figcaption>
</figure>
SVG;
    }

    private function circuitSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="LED resistor circuit from 3V3" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 200" width="100%" height="auto" role="img" aria-label="LED circuit">
  <text x="360" y="26" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700">Circuit we will build in FS-09 (diagram only today)</text>
  <line x1="60" y1="100" x2="140" y2="100" stroke="#1565C0" stroke-width="3"/>
  <text x="100" y="85" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700">3V3</text>
  <polygon points="150,85 150,115 175,100" fill="#F9A825" stroke="#1a1a1a" stroke-width="2"/>
  <line x1="175" y1="100" x2="220" y2="100" stroke="#1565C0" stroke-width="3"/>
  <rect x="220" y="82" width="70" height="36" rx="4" fill="#FFF8E1" stroke="#1a1a1a" stroke-width="2"/>
  <text x="255" y="106" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">220R</text>
  <line x1="290" y1="100" x2="360" y2="100" stroke="#1565C0" stroke-width="3"/>
  <line x1="360" y1="100" x2="360" y2="150" stroke="#1565C0" stroke-width="3"/>
  <line x1="360" y1="150" x2="60" y2="150" stroke="#1565C0" stroke-width="3"/>
  <line x1="60" y1="150" x2="60" y2="100" stroke="#1565C0" stroke-width="3"/>
  <text x="360" y="175" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12">GND</text>
  <text x="200" y="130" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">LED ~2V drop</text>
  <text x="360" y="190" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">By Koding Indonesia — no physical wiring today</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Current path: 3V3 → LED → 220Ω resistor → GND (by Koding Indonesia). Physical build comes in the next module.</figcaption>
</figure>
SVG;
    }

    private function pickResistorSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Pilih 220 atau 330 ohm" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 180" width="100%" height="auto" role="img" aria-label="Pick resistor">
  <text x="360" y="26" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700">Hitung ~130 ohm → pilih nilai standar di atasnya</text>
  <rect x="80" y="55" width="240" height="90" rx="8" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2"/>
  <text x="200" y="88" text-anchor="middle" font-family="system-ui,sans-serif" font-size="18" font-weight="700">220 ohm</text>
  <text x="200" y="115" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12">Pilihan utama kit</text>
  <text x="200" y="133" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#2E7D32">Terang &amp; aman</text>
  <rect x="400" y="55" width="240" height="90" rx="8" fill="#E3F2FD" stroke="#1565C0" stroke-width="2"/>
  <text x="520" y="88" text-anchor="middle" font-family="system-ui,sans-serif" font-size="18" font-weight="700">330 ohm</text>
  <text x="520" y="115" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12">Alternatif aman</text>
  <text x="520" y="133" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#1565C0">Sedikit lebih redup</text>
  <text x="360" y="168" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Buatan Koding Indonesia — jangan LED tanpa resistor</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Setelah hitung ~130Ω, ambil 220Ω dari kit. 330Ω juga boleh jika ingin lebih aman (buatan Koding Indonesia).</figcaption>
</figure>
SVG;
    }

    private function pickResistorSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Pick 220 or 330 ohm" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 180" width="100%" height="auto" role="img" aria-label="Pick resistor">
  <text x="360" y="26" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700">Calculate ~130 ohm → pick the next standard value up</text>
  <rect x="80" y="55" width="240" height="90" rx="8" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2"/>
  <text x="200" y="88" text-anchor="middle" font-family="system-ui,sans-serif" font-size="18" font-weight="700">220 ohm</text>
  <text x="200" y="115" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12">Main kit pick</text>
  <text x="200" y="133" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#2E7D32">Bright &amp; safe</text>
  <rect x="400" y="55" width="240" height="90" rx="8" fill="#E3F2FD" stroke="#1565C0" stroke-width="2"/>
  <text x="520" y="88" text-anchor="middle" font-family="system-ui,sans-serif" font-size="18" font-weight="700">330 ohm</text>
  <text x="520" y="115" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12">Safe alternative</text>
  <text x="520" y="133" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#1565C0">Slightly dimmer</text>
  <text x="360" y="168" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">By Koding Indonesia — never drive an LED with no resistor</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">After ~130Ω on paper, grab 220Ω from the kit. 330Ω is fine if you want extra safety (by Koding Indonesia).</figcaption>
</figure>
SVG;
    }

    private function body(): string
    {
        $water = $this->waterAnalogySvgId();
        $ohm = $this->ohmSvgId();
        $led = $this->ledFigureId();
        $res = $this->resistorFigureId();
        $circuit = $this->circuitSvgId();
        $pick = $this->pickResistorSvgId();

        return <<<HTML
<h2>Pendahuluan — kenapa belajar rumus sekarang?</h2>
<p>Artikel ini adalah <strong>#78 (ini)</strong> · modul <strong>FS-08</strong> di jalur <em>Full Stack IoT Developer — Dari Nol</em>. Di <strong>FS-07</strong> kamu sudah bisa membaca 3.3V di board. Hari ini kamu paham <strong>kenapa LED butuh resistor</strong> dan cara memilih 220Ω atau 330Ω — masih di meja, belum menyolder atau merakit breadboard.</p>
<p><strong>Awam:</strong> modul ini seperti belajar rem mobil sebelum menyetir — supaya LED tidak “kebablasan” arusnya.</p>
<p><strong>Prasyarat:</strong> FS-07 (bisa ukur 3V3) + kenal bentuk LED &amp; resistor dari FS-04. <strong>Tidak ada wiring breadboard, Arduino IDE, atau <code>php artisan</code> hari ini</strong> — hanya kertas, kalkulator HP, dan komponen di tangan (belum dicolok).</p>

<h2>Persiapan — alat yang kamu buka hari ini</h2>
<p><strong>Alat yang dipakai di artikel ini</strong> (belum breadboard wiring, belum upload sketch):</p>
<ul>
<li><strong>Kertas + pena</strong> — tulis hitungan langkah demi langkah.</li>
<li><strong>Kalkulator HP</strong> — aplikasi Calculator bawaan (Android/iPhone). Tidak perlu aplikasi khusus elektronik.</li>
<li><strong>LED 5mm</strong> + <strong>resistor 220Ω / 330Ω</strong> dari kit FS-04 — pegang &amp; lihat, belum dicolok ke board.</li>
<li><strong>Browser</strong> — buka artikel ini + kalkulator interaktif di bawah.</li>
<li><strong>Multimeter</strong> (opsional) — kalau mau cek ulang 3V3 seperti FS-07.</li>
</ul>
<p><strong>Awam — urutan buka:</strong> (1) baca analogi air → (2) pahami V = I x R → (3) lihat foto LED &amp; resistor → (4) hitung di kertas → (5) cek dengan kalkulator di artikel → (6) checklist 8/8.</p>
<p><strong>Hasil yang kamu cari hari ini:</strong> bisa tulis “R = (3.3 - 2.0) / 0.01 = 130 ohm → pakai <strong>220Ω</strong>” dengan percaya diri.</p>

<h2>Ingat dulu — dari FS-07</h2>
<p>Board ESP32 menyediakan <strong>3.3V</strong> untuk logika GPIO. Kamu sudah memverifikasi angka itu dengan multimeter. Hari ini angka <strong>3.3V</strong> jadi “bahan bakar” hitungan resistor LED.</p>

<h2>Tiga kata: V, A, dan Ohm</h2>
{$water}
<ul>
<li><strong>Volt (V)</strong> — “tekanan” listrik. Board kita = 3.3V.</li>
<li><strong>Ampere (A / mA)</strong> — “aliran” listrik. LED kecil aman sekitar <strong>10 mA</strong> (0.01 A), bukan maksimum 20 mA.</li>
<li><strong>Ohm</strong> — “sempitnya jalan”. Resistor membatasi aliran.</li>
</ul>
<p><strong>Awam — cara menguji:</strong> jelaskan ke teman non-teknis: “Volt seperti tekanan keran, ampere seperti debit air, ohm seperti keran yang disetengah buka.” Kalau mereka mengangguk, konsep dasar sudah masuk.</p>

<h2>Rumus Ohm — cukup satu baris</h2>
{$ohm}
<p>Untuk resistor LED, pakai tegangan yang <strong>tersisa</strong> setelah LED “makan” sebagian tegangan:</p>
<p><strong>R = (V_supply - V_LED) / I</strong></p>
<p>Contoh angka kit: V_supply = <strong>3.3 V</strong>, V_LED ≈ <strong>2.0 V</strong> (LED merah/hijau 5mm), I = <strong>0.01 A</strong> (10 mA).</p>

<h2>Kenapa LED butuh resistor?</h2>
{$led}
{$res}
<p>LED hampir seperti kabel pendek saat menyala — tanpa resistor, arus bisa melonjak dan LED panas / rusak. Resistor = rem yang menjaga arus tetap wajar.</p>
<p><strong>Awam:</strong> jangan pernah colok LED langsung 3V3–GND tanpa resistor. Itu topik kesalahan umum di bawah.</p>

<h2>Gambar rangkaian (belum dirakit)</h2>
{$circuit}

<h2>Hitung di kertas — langkah demi langkah</h2>
<ol>
<li>Tulis <strong>V_supply = 3.3</strong> (volt dari board).</li>
<li>Tulis <strong>V_LED = 2.0</strong> (tegangan jatuh tipikal LED 5mm; 1.8–2.2 masih OK).</li>
<li>Tulis <strong>I = 0.01</strong> (10 mA dalam ampere — bagi 10 dengan 1000).</li>
<li>Hitung sisa tegangan: <strong>3.3 - 2.0 = 1.3 V</strong>.</li>
<li>Hitung R: <strong>1.3 / 0.01 = 130 ohm</strong>.</li>
<li>Nilai standar di kit: ambil <strong>220Ω</strong> (lebih besar dari 130 → lebih aman). <strong>330Ω</strong> juga boleh — LED sedikit lebih redup.</li>
</ol>
{$pick}
<table>
<thead><tr><th>Langkah</th><th>Isi kertasmu</th></tr></thead>
<tbody>
<tr><td>V_supply</td><td>3.3 V</td></tr>
<tr><td>V_LED</td><td>2.0 V</td></tr>
<tr><td>I (mA)</td><td>10 mA = 0.01 A</td></tr>
<tr><td>R hitung</td><td>~130 ohm</td></tr>
<tr><td>Pilih resistor</td><td>220Ω (utama) atau 330Ω</td></tr>
</tbody>
</table>
<p><strong>Awam — cara menguji:</strong> foto kertas + kalimat “130 ohm, pakai 220” sudah cukup bukti FS-08 selesai.</p>

<h2>Daya (W) — secukupnya</h2>
<p><strong>P = V x I</strong> (Watt). Untuk resistor: P ≈ 1.3 V x 0.01 A = <strong>0.013 W</strong> — sangat kecil. Resistor 1/4 W di kit jauh lebih kuat dari kebutuhan. Tidak perlu hafal lebih dalam sekarang.</p>

<h2 id="fsiot-resistor-calc">Praktik — kalkulator resistor LED</h2>
<p>Isi tiga angka di bawah (default sudah benar untuk kit DevKitC-1). Klik hitung — bandingkan dengan kertasmu.</p>
<div id="fsiot-resistor-calc-root" aria-label="Kalkulator resistor LED interaktif"></div>
<p><strong>Awam — cara menguji:</strong> ubah I jadi 20 mA, lihat R turun — itu sebabnya kita pilih 10 mA agar aman. Tidak perlu Arduino IDE.</p>

<h2 id="fsiot-electric-checklist">Praktik — checklist paham listrik mini</h2>
<p>Centang tiap poin setelah kamu paham atau selesai menulis di kertas. Target: <strong>8/8</strong>. Ada checklist interaktif di bawah; versi kertas tetap tersedia.</p>
<ul id="fsiot-electric-checklist-items">
<li>Saya ingat FS-07: 3V3 board terbaca sekitar 3.2–3.4 V</li>
<li>Saya bisa jelaskan V = tekanan, A = aliran, Ohm = sempit (analogi air)</li>
<li>Saya tahu rumus R = (V_supply - V_LED) / I</li>
<li>Saya menulis hitungan 3.3 V, 2.0 V, 10 mA di kertas</li>
<li>Hasil hitung saya mendekati 130 ohm</li>
<li>Saya memilih 220Ω — bukan LED tanpa resistor</li>
<li>Saya tahu 330Ω juga aman (LED sedikit lebih redup)</li>
<li>Saya tidak takut angka — kalkulator HP cukup</li>
</ul>
<p><strong>Awam — cara menguji:</strong> kerjakan checklist di browser. Tidak perlu menjalankan perintah web server atau merakit breadboard.</p>

<h2>Kesalahan umum awam</h2>
<ul>
<li><strong>LED langsung ke 3V3 tanpa resistor.</strong> Arus berlebihan — LED cepat rusak.</li>
<li><strong>Takut rumus.</strong> Cukup satu: R = (V_supply - V_LED) / I. Kalkulator HP boleh.</li>
<li><strong>Pakai 20 mA karena “maksimum datasheet”.</strong> 10 mA sudah terang &amp; lebih aman untuk belajar.</li>
<li><strong>Salah satuan.</strong> 10 mA = 0.01 A — jangan lupa bagi 1000.</li>
<li><strong>Resistor di bawah hitungan (mis. 100Ω).</strong> Ambil nilai standar <strong>di atas</strong> hitungan (~130 → 220Ω).</li>
<li><strong>Langsung wiring breadboard sebelum paham.</strong> Itu modul FS-09 — hari ini teori + hitung saja.</li>
<li><strong>Membalik LED di kepala.</strong> Kaki panjang ke +, pendek ke -. Wiring fisik nanti.</li>
</ul>

<h2>Lanjut belajar</h2>
<p>Setelah FS-08, langkah alami berikutnya adalah <strong>FS-09 — rangkaian pertama: LED + resistor di breadboard</strong> (belum coding). Artikel itu belum dilink di sini sampai modulnya siap.</p>
<p>Simpan juga <a href="/belajar/fullstack-iot">halaman jalur Full Stack IoT</a> sebagai pintu masuk resmi.</p>

<h2>Kesimpulan</h2>
<p>Di <strong>#78 (ini)</strong> kamu sudah paham V, A, Ohm dengan analogi air, menghitung ~130Ω untuk LED dari 3.3V, dan memilih <strong>220Ω</strong> (atau 330Ω) dengan alasan.</p>
<p><strong>Awam:</strong> kalau kamu bisa tunjukkan kertas hitungan dan bilang “LED butuh rem, saya pakai 220 ohm”, FS-08 selesai. Lanjut merakit LED menyala di FS-09 saat modulnya terbit.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $water = $this->waterAnalogySvgEn();
        $ohm = $this->ohmSvgEn();
        $led = $this->ledFigureEn();
        $res = $this->resistorFigureEn();
        $circuit = $this->circuitSvgEn();
        $pick = $this->pickResistorSvgEn();

        return <<<HTML
<h2>Introduction — why learn the formula now?</h2>
<p>This article is <strong>#78 (this article)</strong> · module <strong>FS-08</strong> on the <em>Full Stack IoT Developer — From Zero</em> path. In <strong>FS-07</strong> you could already read 3.3V on the board. Today you understand <strong>why an LED needs a resistor</strong> and how to pick 220Ω or 330Ω — still at the desk, no soldering or breadboard build yet.</p>
<p><strong>Beginner:</strong> this module is like learning the brake before driving — so the LED does not get too much current.</p>
<p><strong>Prerequisites:</strong> FS-07 (can measure 3V3) + recognize LED &amp; resistor shapes from FS-04. <strong>No breadboard wiring, Arduino IDE, or <code>php artisan</code> today</strong> — only paper, a phone calculator, and parts in your hand (not plugged in).</p>

<h2>Preparation — tools you open today</h2>
<p><strong>Tools used in this article</strong> (no breadboard wiring, no sketch upload):</p>
<ul>
<li><strong>Paper + pen</strong> — write the step-by-step math.</li>
<li><strong>Phone calculator</strong> — built-in Calculator app (Android/iPhone). No special electronics app required.</li>
<li><strong>5mm LED</strong> + <strong>220Ω / 330Ω resistor</strong> from the FS-04 kit — hold &amp; look, not plugged into the board.</li>
<li><strong>Browser</strong> — keep this article open + the interactive calculator below.</li>
<li><strong>Multimeter</strong> (optional) — re-check 3V3 like FS-07 if you want.</li>
</ul>
<p><strong>Beginner — open order:</strong> (1) read the water analogy → (2) understand V = I x R → (3) look at LED &amp; resistor photos → (4) calculate on paper → (5) check with the in-article calculator → (6) checklist 8/8.</p>
<p><strong>What you want today:</strong> you can write “R = (3.3 - 2.0) / 0.01 = 130 ohm → use <strong>220Ω</strong>” with confidence.</p>

<h2>Remember first — from FS-07</h2>
<p>The ESP32 board provides <strong>3.3V</strong> for GPIO logic. You already verified that number with a multimeter. Today <strong>3.3V</strong> is the fuel for LED resistor math.</p>

<h2>Three words: V, A, and Ohm</h2>
{$water}
<ul>
<li><strong>Volt (V)</strong> — electrical “pressure”. Our board = 3.3V.</li>
<li><strong>Ampere (A / mA)</strong> — electrical “flow”. A small LED is happy around <strong>10 mA</strong> (0.01 A), not the 20 mA maximum.</li>
<li><strong>Ohm</strong> — how “narrow” the path is. A resistor limits flow.</li>
</ul>
<p><strong>Beginner — how to test:</strong> explain to a non-technical friend: “Volts are like tap pressure, amps are like flow rate, ohms are like a half-closed valve.” If they nod, the basics landed.</p>

<h2>Ohm's law — one line is enough</h2>
{$ohm}
<p>For an LED resistor, use the voltage <strong>left over</strong> after the LED drops some:</p>
<p><strong>R = (V_supply - V_LED) / I</strong></p>
<p>Typical kit numbers: V_supply = <strong>3.3 V</strong>, V_LED ≈ <strong>2.0 V</strong> (red/green 5mm LED), I = <strong>0.01 A</strong> (10 mA).</p>

<h2>Why does an LED need a resistor?</h2>
{$led}
{$res}
<p>An LED behaves almost like a short wire when on — without a resistor, current can spike and the LED overheats / dies. The resistor is the brake that keeps flow reasonable.</p>
<p><strong>Beginner:</strong> never connect an LED straight from 3V3 to GND without a resistor. That is a common mistake below.</p>

<h2>Circuit picture (not built yet)</h2>
{$circuit}

<h2>Calculate on paper — step by step</h2>
<ol>
<li>Write <strong>V_supply = 3.3</strong> (volts from the board).</li>
<li>Write <strong>V_LED = 2.0</strong> (typical 5mm LED drop; 1.8–2.2 is fine).</li>
<li>Write <strong>I = 0.01</strong> (10 mA in amperes — divide 10 by 1000).</li>
<li>Leftover voltage: <strong>3.3 - 2.0 = 1.3 V</strong>.</li>
<li>Find R: <strong>1.3 / 0.01 = 130 ohm</strong>.</li>
<li>Standard kit value: pick <strong>220Ω</strong> (bigger than 130 → safer). <strong>330Ω</strong> is also fine — slightly dimmer LED.</li>
</ol>
{$pick}
<table>
<thead><tr><th>Step</th><th>Your paper</th></tr></thead>
<tbody>
<tr><td>V_supply</td><td>3.3 V</td></tr>
<tr><td>V_LED</td><td>2.0 V</td></tr>
<tr><td>I (mA)</td><td>10 mA = 0.01 A</td></tr>
<tr><td>R calculated</td><td>~130 ohm</td></tr>
<tr><td>Pick resistor</td><td>220Ω (main) or 330Ω</td></tr>
</tbody>
</table>
<p><strong>Beginner — how to test:</strong> a photo of your paper + the sentence “130 ohm, use 220” is enough proof FS-08 is done.</p>

<h2>Power (W) — just enough</h2>
<p><strong>P = V x I</strong> (Watt). For the resistor: P ≈ 1.3 V x 0.01 A = <strong>0.013 W</strong> — tiny. The 1/4 W resistors in the kit are far stronger than needed. No deeper memorization today.</p>

<h2 id="fsiot-resistor-calc">Practice — LED resistor calculator</h2>
<p>Fill the three numbers below (defaults are correct for the DevKitC-1 kit). Click calculate — compare with your paper.</p>
<div id="fsiot-resistor-calc-root" aria-label="Interactive LED resistor calculator"></div>
<p><strong>Beginner — how to test:</strong> change I to 20 mA and watch R drop — that is why we use 10 mA to stay safe. No Arduino IDE required.</p>

<h2 id="fsiot-electric-checklist">Practice — mini electricity checklist</h2>
<p>Tick each point once you understand it or finished writing on paper. Target: <strong>8/8</strong>. An interactive checklist is below; a paper version stays available.</p>
<ul id="fsiot-electric-checklist-items">
<li>I remember FS-07: board 3V3 reads about 3.2–3.4 V</li>
<li>I can explain V = pressure, A = flow, Ohm = narrow (water analogy)</li>
<li>I know R = (V_supply - V_LED) / I</li>
<li>I wrote the 3.3 V, 2.0 V, 10 mA calculation on paper</li>
<li>My result is close to 130 ohm</li>
<li>I pick 220Ω — not an LED without a resistor</li>
<li>I know 330Ω is also safe (slightly dimmer LED)</li>
<li>I am not afraid of the numbers — a phone calculator is enough</li>
</ul>
<p><strong>Beginner — how to test:</strong> complete the checklist in the browser. No web server commands or breadboard build required.</p>

<h2>Common beginner mistakes</h2>
<ul>
<li><strong>LED straight to 3V3 with no resistor.</strong> Too much current — LED dies fast.</li>
<li><strong>Fear of formulas.</strong> One is enough: R = (V_supply - V_LED) / I. A phone calculator is fine.</li>
<li><strong>Using 20 mA because “datasheet max”.</strong> 10 mA is already bright &amp; safer for learning.</li>
<li><strong>Wrong units.</strong> 10 mA = 0.01 A — do not forget to divide by 1000.</li>
<li><strong>Resistor below the math (e.g. 100Ω).</strong> Pick a standard value <strong>above</strong> the calculation (~130 → 220Ω).</li>
<li><strong>Jumping to breadboard before understanding.</strong> That is FS-09 — today is theory + math only.</li>
<li><strong>LED polarity confusion.</strong> Long leg to +, short to -. Physical wiring comes later.</li>
</ul>

<h2>Continue learning</h2>
<p>After FS-08, the natural next step is <strong>FS-09 — first circuit: LED + resistor on a breadboard</strong> (no code yet). That article is not linked here until the module is ready.</p>
<p>Also bookmark the <a href="/belajar/fullstack-iot">Full Stack IoT path page</a> as the official entry.</p>

<h2>Conclusion</h2>
<p>In <strong>#78 (this article)</strong> you understand V, A, and Ohm with a water analogy, calculate ~130Ω for an LED from 3.3V, and pick <strong>220Ω</strong> (or 330Ω) with a reason.</p>
<p><strong>Beginner:</strong> if you can show your paper and say “the LED needs a brake, I use 220 ohm”, FS-08 is done. Continue to a glowing LED build in FS-09 when that module publishes.</p>
HTML;
    }
}
