<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article91Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first()
            ?? Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $iotCat) {
            throw new \RuntimeException('User atau kategori iot-smart-device/esp32-arduino tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-sensor-dht22-serial';

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
                'title'              => 'Sensor DHT22: suhu & kelembapan ke Serial',
                'title_en'           => 'DHT22 sensor: temperature & humidity to Serial',
                'excerpt'            => 'FS-21 / #91: baca dunia nyata pertama. Uji di Arduino IDE: Library Manager + DHT22 di GPIO 4, suhu & kelembapan ke Serial tiap 3 detik.',
                'excerpt_en'         => 'FS-21 / #91: first real-world reading. Test in Arduino IDE: Library Manager + DHT22 on GPIO 4, temperature & humidity to Serial every 3 seconds.',
                'body'               => $this->body(),
                'body_en'            => $this->bodyEn(),
                'status'             => 'draft',
                'is_featured'        => false,
                'published_at'       => null,
                'seo_title'          => 'Sensor DHT22 ke Serial — Full Stack IoT #91',
                'seo_title_en'       => 'DHT22 sensor to Serial — Full Stack IoT #91',
                'seo_description'    => 'Belajar wiring DHT22, Library Manager, dan baca suhu/kelembapan di ESP32. Modul FS-21 jalur Full Stack IoT.',
                'seo_description_en' => 'Learn DHT22 wiring, Library Manager, and read temperature/humidity on ESP32. Full Stack IoT FS-21 module.',
            ]
        );

        if ($article->published_at !== null || $article->status !== 'draft') {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['fullstack-iot', 'iot', 'esp32'])->pluck('id');
        $article->tags()->sync($tagIds);

        if (blank($article->cover_image)) {
            $src = public_path('images/fsiot/fs21-cover-dht22.jpg');
            if (is_file($src)) {
                $dest = 'articles/covers/fs21-cover-dht22.jpg';
                \Illuminate\Support\Facades\Storage::disk('public')->put($dest, file_get_contents($src));
                $article->cover_image = $dest;
                $article->save();
            }
        }

        $this->command?->info('✓ Artikel #91 / FS-21 tersimpan sebagai DRAFT: '.$article->title);
    }

    private function ideFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/fs11-ide-overview-cite.png" width="1280" height="720" alt="Arduino IDE 2 — Verify, Upload, dan Serial Monitor" loading="eager" style="width:100%;height:auto;max-height:420px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Arduino IDE 2</strong> — tempat menguji sintaks &amp; library hari ini. Install library di <strong>Library Manager</strong>, lalu <strong>Verify</strong> → <strong>Upload</strong> → buka <strong>Serial Monitor</strong> (baud 115200). Board: <strong>ESP32 Dev Module</strong>.
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:Ide-2-overview.png" rel="noopener noreferrer" target="_blank">Arduino IDE 2 overview</a> · Wikimedia Commons (CC BY-SA 3.0). Panduan Serial: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-serial-monitor/" rel="noopener noreferrer" target="_blank">Arduino Docs — Serial Monitor</a>. Library Manager: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-installing-a-library/" rel="noopener noreferrer" target="_blank">Arduino Docs — Installing a library</a>.
  </figcaption>
</figure>
HTML;
    }

    private function ideFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/fs11-ide-overview-cite.png" width="1280" height="720" alt="Arduino IDE 2 — Verify, Upload, and Serial Monitor" loading="eager" style="width:100%;height:auto;max-height:420px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Arduino IDE 2</strong> — where today’s syntax and libraries are tested. Install libraries in <strong>Library Manager</strong>, then <strong>Verify</strong> → <strong>Upload</strong> → open <strong>Serial Monitor</strong> (baud 115200). Board: <strong>ESP32 Dev Module</strong>.
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:Ide-2-overview.png" rel="noopener noreferrer" target="_blank">Arduino IDE 2 overview</a> · Wikimedia Commons (CC BY-SA 3.0). Serial guide: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-serial-monitor/" rel="noopener noreferrer" target="_blank">Arduino Docs — Serial Monitor</a>. Library Manager: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-installing-a-library/" rel="noopener noreferrer" target="_blank">Arduino Docs — Installing a library</a>.
  </figcaption>
</figure>
HTML;
    }

    private function boardFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/esp32-devkitc-overview.jpg" width="1200" height="800" alt="ESP32-DevKitC — USB (6) dan EN (7)" loading="eager" style="width:100%;height:auto;max-height:360px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>ESP32-DevKitC</strong> — USB data di <strong>(6)</strong>, reset di <strong>EN (7)</strong>. Pin data DHT22 latihan FSIOT = <strong>GPIO 4</strong> (tabel global FS-17).
    <br>Sumber gambar: <a href="https://docs.espressif.com/projects/esp-dev-kits/en/latest/esp32/esp32-devkitc/user_guide.html" rel="noopener noreferrer" target="_blank">Espressif — ESP32-DevKitC user guide</a>. Pin board: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/boards/ESP32-DevKitC-1.html" rel="noopener noreferrer" target="_blank">ESP32-DevKitC-1</a>.
  </figcaption>
</figure>
HTML;
    }

    private function boardFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/esp32-devkitc-overview.jpg" width="1200" height="800" alt="ESP32-DevKitC — USB (6) and EN (7)" loading="eager" style="width:100%;height:auto;max-height:360px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>ESP32-DevKitC</strong> — USB data at <strong>(6)</strong>, reset on <strong>EN (7)</strong>. FSIOT practice DHT22 data pin = <strong>GPIO 4</strong> (FS-17 global table).
    <br>Image source: <a href="https://docs.espressif.com/projects/esp-dev-kits/en/latest/esp32/esp32-devkitc/user_guide.html" rel="noopener noreferrer" target="_blank">Espressif — ESP32-DevKitC user guide</a>. Board pins: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/boards/ESP32-DevKitC-1.html" rel="noopener noreferrer" target="_blank">ESP32-DevKitC-1</a>.
  </figcaption>
</figure>
HTML;
    }

    private function kitFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem">
  <div style="display:grid;grid-template-columns:1.4fr 1fr;gap:0.75rem;align-items:start">
    <div>
      <img src="/images/fsiot/kit-dht22.jpg" width="900" height="600" alt="Modul sensor DHT22 / AM2302" loading="eager" style="display:block;width:100%;height:auto;max-height:280px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem;margin:0 auto">
      <p style="font-size:0.8rem;margin:0.35rem 0 0;color:#1a1a1a;text-align:center"><strong>DHT22 / AM2302</strong> — komponen utama hari ini</p>
    </div>
    <div>
      <img src="/images/fsiot/kit-resistor-10kohm.jpg" width="800" height="600" alt="Resistor 10 kOhm opsional untuk pull-up" loading="eager" style="display:block;width:100%;height:auto;max-height:220px;object-fit:contain;border:2.5px dashed #888;border-radius:8px;background:#fff;padding:0.5rem;margin:0 auto;opacity:0.95">
      <p style="font-size:0.8rem;margin:0.35rem 0 0;color:#1a1a1a;text-align:center"><strong>10 kΩ</strong> — <em>opsional</em> (bukan wajib modul kit)</p>
    </div>
  </div>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Komponen hari ini:</strong> modul DHT22 / AM2302 (+ jumper). Resistor 10 kΩ <strong>tidak wajib</strong> untuk modul kit zaman sekarang — pull-up biasanya sudah ada di PCB. Simpan resistor untuk kasus sensor mentah / baca gagal. DHT11 boleh sementara — ketelitian berbeda. <strong>Baca label silkscreen</strong> — urutan kaki bisa beda antar merek.
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:DHT_22_Sensor.jpg" rel="noopener noreferrer" target="_blank">AM2302 DHT22 Sensor</a> · Wikimedia Commons (CC BY-SA 4.0) · L293D · foto resistor kit Koding Indonesia. Acuan: <a href="https://www.adafruit.com/product/393" rel="noopener noreferrer" target="_blank">Adafruit AM2302</a> (modul ber-pull-up internal).
  </figcaption>
</figure>
HTML;
    }

    private function kitFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem">
  <div style="display:grid;grid-template-columns:1.4fr 1fr;gap:0.75rem;align-items:start">
    <div>
      <img src="/images/fsiot/kit-dht22.jpg" width="900" height="600" alt="DHT22 / AM2302 sensor module" loading="eager" style="display:block;width:100%;height:auto;max-height:280px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem;margin:0 auto">
      <p style="font-size:0.8rem;margin:0.35rem 0 0;color:#1a1a1a;text-align:center"><strong>DHT22 / AM2302</strong> — main part today</p>
    </div>
    <div>
      <img src="/images/fsiot/kit-resistor-10kohm.jpg" width="800" height="600" alt="Optional 10 kOhm pull-up resistor" loading="eager" style="display:block;width:100%;height:auto;max-height:220px;object-fit:contain;border:2.5px dashed #888;border-radius:8px;background:#fff;padding:0.5rem;margin:0 auto;opacity:0.95">
      <p style="font-size:0.8rem;margin:0.35rem 0 0;color:#1a1a1a;text-align:center"><strong>10 kΩ</strong> — <em>optional</em> (not required for kit modules)</p>
    </div>
  </div>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Parts today:</strong> DHT22 / AM2302 module (+ jumpers). A 10 kΩ resistor is <strong>not required</strong> for typical modern kit modules — pull-up is usually already on the PCB. Keep one handy for bare sensors / failed reads. DHT11 is OK temporarily — accuracy differs. <strong>Read the silkscreen</strong> — pin order can differ by brand.
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:DHT_22_Sensor.jpg" rel="noopener noreferrer" target="_blank">AM2302 DHT22 Sensor</a> · Wikimedia Commons (CC BY-SA 4.0) · L293D · Koding Indonesia resistor kit photo. Reference: <a href="https://www.adafruit.com/product/393" rel="noopener noreferrer" target="_blank">Adafruit AM2302</a> (module with internal pull-up).
  </figcaption>
</figure>
HTML;
    }

    private function mainWiringFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs21-dht22-wiring.png" width="1100" height="740" alt="Gambar utama — wiring DHT22 ke GPIO 4 (modul kit, 3 kabel)" loading="eager" style="width:100%;height:auto;max-height:600px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Gambar utama — wiring DHT22 (modul kit).</strong> Cukup tiga kabel: VCC → <strong>3V3</strong> · DATA → <strong>GPIO 4</strong> · GND → <strong>GND</strong>. Pull-up eksternal biasanya <strong>tidak perlu</strong>. Cocokkan label silkscreen di modulmu.
    <br>Sumber gambar: diagram berlabel buatan Koding Indonesia (FS-21). Panduan: <a href="https://learn.adafruit.com/dht/connecting-to-a-dhtxx-sensor" rel="noopener noreferrer" target="_blank">Adafruit — Connecting to a DHTxx sensor</a> · catatan modul: <a href="https://www.adafruit.com/product/393" rel="noopener noreferrer" target="_blank">Adafruit AM2302</a>.
  </figcaption>
</figure>
HTML;
    }

    private function mainWiringFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs21-dht22-wiring.png" width="1100" height="740" alt="Main figure — DHT22 wiring to GPIO 4 (kit module, 3 wires)" loading="eager" style="width:100%;height:auto;max-height:600px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Main figure — DHT22 wiring (kit module).</strong> Three wires are enough: VCC → <strong>3V3</strong> · DATA → <strong>GPIO 4</strong> · GND → <strong>GND</strong>. An external pull-up is usually <strong>not needed</strong>. Match the silkscreen on your module.
    <br>Image source: labeled diagram by Koding Indonesia (FS-21). Guide: <a href="https://learn.adafruit.com/dht/connecting-to-a-dhtxx-sensor" rel="noopener noreferrer" target="_blank">Adafruit — Connecting to a DHTxx sensor</a> · module note: <a href="https://www.adafruit.com/product/393" rel="noopener noreferrer" target="_blank">Adafruit AM2302</a>.
  </figcaption>
</figure>
HTML;
    }

    private function librarySvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Langkah Library Manager: cari DHT, install Adafruit, lalu Unified Sensor" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 210" width="100%" height="auto" style="display:block;max-height:240px">
    <text x="430" y="28" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#1a1a1a">Library Manager — urutan instal (di Arduino IDE)</text>
    <rect x="30" y="50" width="240" height="120" rx="10" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
    <text x="150" y="85" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" font-weight="700" fill="#0D47A1">1. Sketch → Include Library</text>
    <text x="150" y="115" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#333">→ Manage Libraries…</text>
    <text x="150" y="145" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#555">buka Library Manager</text>
    <rect x="310" y="50" width="240" height="120" rx="10" fill="#FFF3E0" stroke="#EF6C00" stroke-width="2.5"/>
    <text x="430" y="85" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" font-weight="700" fill="#E65100">2. Cari “DHT”</text>
    <text x="430" y="115" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#333">DHT sensor library</text>
    <text x="430" y="145" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#555">by Adafruit → Install</text>
    <rect x="590" y="50" width="240" height="120" rx="10" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
    <text x="710" y="85" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" font-weight="700" fill="#1B5E20">3. Install juga</text>
    <text x="710" y="115" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#333">Adafruit Unified Sensor</text>
    <text x="710" y="145" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#555">wajib sebagai dependensi</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.35rem;color:#4A5568;">
    <strong>Intinya:</strong> dua library — <em>DHT sensor library</em> + <em>Adafruit Unified Sensor</em>. Sumber gambar: diagram buatan Koding Indonesia (FS-21). Panduan: <a href="https://learn.adafruit.com/dht/using-a-dhtxx-sensor-with-arduino" rel="noopener noreferrer" target="_blank">Adafruit — Using a DHTxx with Arduino</a>.
  </figcaption>
</figure>
SVG;
    }

    private function librarySvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Library Manager steps: search DHT, install Adafruit, then Unified Sensor" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 210" width="100%" height="auto" style="display:block;max-height:240px">
    <text x="430" y="28" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#1a1a1a">Library Manager — install order (in Arduino IDE)</text>
    <rect x="30" y="50" width="240" height="120" rx="10" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
    <text x="150" y="85" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" font-weight="700" fill="#0D47A1">1. Sketch → Include Library</text>
    <text x="150" y="115" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#333">→ Manage Libraries…</text>
    <text x="150" y="145" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#555">open Library Manager</text>
    <rect x="310" y="50" width="240" height="120" rx="10" fill="#FFF3E0" stroke="#EF6C00" stroke-width="2.5"/>
    <text x="430" y="85" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" font-weight="700" fill="#E65100">2. Search “DHT”</text>
    <text x="430" y="115" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#333">DHT sensor library</text>
    <text x="430" y="145" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#555">by Adafruit → Install</text>
    <rect x="590" y="50" width="240" height="120" rx="10" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
    <text x="710" y="85" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" font-weight="700" fill="#1B5E20">3. Also install</text>
    <text x="710" y="115" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#333">Adafruit Unified Sensor</text>
    <text x="710" y="145" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#555">required dependency</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.35rem;color:#4A5568;">
    <strong>In short:</strong> two libraries — <em>DHT sensor library</em> + <em>Adafruit Unified Sensor</em>. Image source: diagram by Koding Indonesia (FS-21). Guide: <a href="https://learn.adafruit.com/dht/using-a-dhtxx-sensor-with-arduino" rel="noopener noreferrer" target="_blank">Adafruit — Using a DHTxx with Arduino</a>.
  </figcaption>
</figure>
SVG;
    }

    private function nanSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Serial OK versus gagal baca isnan" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 200" width="100%" height="auto" style="display:block;max-height:230px">
    <text x="430" y="28" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#1a1a1a">Hasil Serial: sukses vs gagal baca</text>
    <rect x="40" y="50" width="360" height="120" rx="10" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
    <text x="220" y="80" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" font-weight="700" fill="#1B5E20">SUKSES</text>
    <text x="220" y="115" text-anchor="middle" font-family="Consolas,monospace" font-size="14" fill="#2E7D32">Suhu: 28.4 C | RH: 62.1 %</text>
    <text x="220" y="145" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#555">angka masuk akal, berganti pelan</text>
    <rect x="460" y="50" width="360" height="120" rx="10" fill="#FFEBEE" stroke="#C62828" stroke-width="2.5"/>
    <text x="640" y="80" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" font-weight="700" fill="#B71C1C">GAGAL</text>
    <text x="640" y="115" text-anchor="middle" font-family="Consolas,monospace" font-size="14" fill="#C62828">isnan / GAGAL baca</text>
    <text x="640" y="145" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#555">cek wiring, library, interval ≥ 2 dtk</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.35rem;color:#4A5568;">
    <strong>Intinya:</strong> <code>isnan</code> = “bukan angka” — sensor belum siap atau jalur data bermasalah. Sumber gambar: diagram buatan Koding Indonesia (FS-21).
  </figcaption>
</figure>
SVG;
    }

    private function nanSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Serial OK versus failed isnan reading" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 200" width="100%" height="auto" style="display:block;max-height:230px">
    <text x="430" y="28" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#1a1a1a">Serial results: success vs failed read</text>
    <rect x="40" y="50" width="360" height="120" rx="10" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
    <text x="220" y="80" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" font-weight="700" fill="#1B5E20">SUCCESS</text>
    <text x="220" y="115" text-anchor="middle" font-family="Consolas,monospace" font-size="14" fill="#2E7D32">Temp: 28.4 C | RH: 62.1 %</text>
    <text x="220" y="145" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#555">sensible numbers, change slowly</text>
    <rect x="460" y="50" width="360" height="120" rx="10" fill="#FFEBEE" stroke="#C62828" stroke-width="2.5"/>
    <text x="640" y="80" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" font-weight="700" fill="#B71C1C">FAILED</text>
    <text x="640" y="115" text-anchor="middle" font-family="Consolas,monospace" font-size="14" fill="#C62828">isnan / failed read</text>
    <text x="640" y="145" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#555">check wiring, library, interval ≥ 2 s</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.35rem;color:#4A5568;">
    <strong>In short:</strong> <code>isnan</code> = “not a number” — sensor not ready or data line issue. Image source: diagram by Koding Indonesia (FS-21).
  </figcaption>
</figure>
SVG;
    }

    private function senseSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="DHT22 sebagai indra: suhu dan kelembapan masuk ke chip" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 160" width="100%" height="auto" style="display:block;max-height:190px">
    <text x="430" y="28" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#1a1a1a">Alur sederhana: udara → sensor → angka di Serial</text>
    <rect x="40" y="55" width="180" height="70" rx="10" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
    <text x="130" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" font-weight="700" fill="#0D47A1">Udara sekitar</text>
    <text x="250" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="22" fill="#1a1a1a">→</text>
    <rect x="280" y="55" width="180" height="70" rx="10" fill="#FFF3E0" stroke="#EF6C00" stroke-width="2.5"/>
    <text x="370" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" font-weight="700" fill="#E65100">DHT22</text>
    <text x="490" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="22" fill="#1a1a1a">→</text>
    <rect x="520" y="55" width="180" height="70" rx="10" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
    <text x="610" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" font-weight="700" fill="#1B5E20">ESP32 GPIO 4</text>
    <text x="730" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="22" fill="#1a1a1a">→</text>
    <rect x="750" y="55" width="90" height="70" rx="10" fill="#ECEFF1" stroke="#455A64" stroke-width="2.5"/>
    <text x="795" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" font-weight="700" fill="#263238">Serial</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.35rem;color:#4A5568;">
    <strong>Intinya:</strong> sensor mengukur, chip membaca, Serial menampilkan. Sumber gambar: diagram buatan Koding Indonesia (FS-21).
  </figcaption>
</figure>
SVG;
    }

    private function senseSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="DHT22 as a sense: temperature and humidity into the chip" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 160" width="100%" height="auto" style="display:block;max-height:190px">
    <text x="430" y="28" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#1a1a1a">Simple flow: air → sensor → numbers on Serial</text>
    <rect x="40" y="55" width="180" height="70" rx="10" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
    <text x="130" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" font-weight="700" fill="#0D47A1">Nearby air</text>
    <text x="250" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="22" fill="#1a1a1a">→</text>
    <rect x="280" y="55" width="180" height="70" rx="10" fill="#FFF3E0" stroke="#EF6C00" stroke-width="2.5"/>
    <text x="370" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" font-weight="700" fill="#E65100">DHT22</text>
    <text x="490" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="22" fill="#1a1a1a">→</text>
    <rect x="520" y="55" width="180" height="70" rx="10" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
    <text x="610" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" font-weight="700" fill="#1B5E20">ESP32 GPIO 4</text>
    <text x="730" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="22" fill="#1a1a1a">→</text>
    <rect x="750" y="55" width="90" height="70" rx="10" fill="#ECEFF1" stroke="#455A64" stroke-width="2.5"/>
    <text x="795" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" font-weight="700" fill="#263238">Serial</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.35rem;color:#4A5568;">
    <strong>In short:</strong> the sensor measures, the chip reads, Serial displays. Image source: diagram by Koding Indonesia (FS-21).
  </figcaption>
</figure>
SVG;
    }

    private function body(): string
    {
        $ide = $this->ideFigureId();
        $board = $this->boardFigureId();
        $kit = $this->kitFigureId();
        $main = $this->mainWiringFigureId();
        $sense = $this->senseSvgId();
        $lib = $this->librarySvgId();
        $nan = $this->nanSvgId();

        return <<<HTML
<h2>Pendahuluan — indra pertama board</h2>
<p>Artikel ini adalah <strong>#91 (ini)</strong> · modul <strong>FS-21</strong> di jalur <em>Full Stack IoT Developer — Dari Nol</em> (fase <strong>BUILDER</strong>). Sampai <strong>FS-20</strong> kita menggerakkan LED. Hari ini board <strong>membaca dunia nyata</strong>: suhu dan kelembapan lewat <strong>DHT22</strong>.</p>
<p><strong>Analogi:</strong> DHT22 seperti termometer + higrometer mini yang “berbisik” angka lewat satu kabel data. Kita dengarkan bisikannya di <strong>Serial Monitor</strong>.</p>
{$sense}
<p><strong>Prasyarat:</strong> FS-18 (pernah Upload sketch) · FS-17 (GPIO 4 di tabel global) · Arduino IDE sudah bisa Upload ke ESP32.</p>
<p><strong>Cara pakai artikel ini (urutan kerja):</strong></p>
<ol>
<li>Siapkan wiring DHT22 di <strong>GPIO 4</strong> (modul kit: biasanya 3 kabel saja). Cocokkan <strong>gambar utama</strong>.</li>
<li><strong>Buka Arduino IDE</strong> (bukan Laragon / terminal web).</li>
<li>Install library lewat <strong>Library Manager</strong> (langkah di bawah).</li>
<li>Buat sketch <code>FS21_dht22_serial</code> → <strong>Verify</strong> → <strong>Upload</strong>.</li>
<li>Buka Serial Monitor baud <strong>115200</strong> — lihat suhu &amp; RH tiap ±3 detik.</li>
<li>Centang checklist 10/10 di browser.</li>
</ol>
<p><strong>Tidak perlu hari ini:</strong> Wi-Fi, MQTT, database, Laragon, <code>php artisan</code>, OLED. Tools hari ini: <strong>Arduino IDE</strong> + <strong>Library Manager</strong> + <strong>Upload</strong> + ESP32 + DHT22 + jumper + <strong>Serial Monitor</strong> + <strong>browser</strong>. Resistor 10 kΩ hanya cadangan (opsional).</p>

<h2>Persiapan — buka &amp; siapkan ini dulu</h2>
<p><strong>Urutan meja kerja:</strong> wiring sensor → install library di IDE → Upload → baca Serial.</p>
<ol>
<li>Buka <strong>Arduino IDE 2.x</strong> · board <strong>ESP32 Dev Module</strong> + port.</li>
<li>Siapkan ESP32 + USB data.</li>
<li>Siapkan modul DHT22 + jumper. Resistor 10 kΩ cukup disiapkan sebagai cadangan.</li>
<li>Cari label <strong>GPIO 4</strong>, <strong>3V3</strong>, dan <strong>GND</strong> di silkscreen.</li>
<li>Lepas tombol FS-19 dari GPIO 4 jika masih terpasang — hari ini pin itu untuk sensor.</li>
<li>Siapkan Serial Monitor baud <strong>115200</strong>.</li>
</ol>
<p><strong>Alat yang dipakai hari ini:</strong> Arduino IDE, Library Manager, Upload, ESP32, USB data, DHT22, jumper, Serial Monitor, browser. (10 kΩ = opsional.)</p>
<p><strong>Tidak dipakai hari ini:</strong> Laragon, <code>php artisan</code>, LED PWM, tombol, multimeter (opsional), PuTTY.</p>
{$ide}
{$board}
{$kit}
{$main}

<h2>Wiring DHT22 (bahasa manusia)</h2>
<p>Modul kit biasanya 3 kaki: <strong>VCC</strong>, <strong>DATA</strong> (kadang tertulis <strong>DAT</strong> / <strong>OUT</strong>), dan <strong>GND</strong>. Sambungkan VCC ke <strong>3V3</strong>, DATA ke <strong>GPIO 4</strong>, GND ke <strong>GND</strong> — <strong>tiga kabel saja</strong> untuk kasus paling umum.</p>
<p><strong>Soal pull-up:</strong> jalur data DHT memang butuh resistor pull-up, tetapi <strong>modul kit / AM2302 zaman sekarang biasanya sudah punya di PCB</strong> (sering ≈4,7–5,1 kΩ). Jadi kamu <strong>tidak perlu</strong> menambah 10 kΩ eksternal kecuali: sensor mentah (chip 4 kaki tanpa papan), kabel panjang, atau Serial sering <code>isnan</code>. Acuan: <a href="https://www.adafruit.com/product/393" rel="noopener noreferrer" target="_blank">Adafruit AM2302</a> (pull-up internal) · <a href="https://learn.adafruit.com/dht/connecting-to-a-dhtxx-sensor" rel="noopener noreferrer" target="_blank">Adafruit — Connecting to a DHTxx</a> (sensor mentah → tambah pull-up).</p>
<p><strong>Baca label silkscreen di modulmu</strong> — urutan kaki bisa beda antar merek; jangan hanya meniru foto.</p>

<h2>Library Manager — pasang “penerjemah” sensor</h2>
{$lib}
<p>Tanpa library, timing pulsa DHT22 rumit ditulis manual. Library = penerjemah siap pakai. Ikuti 3 kotak di atas di <strong>Arduino IDE</strong> (bukan di browser).</p>

<h2>Praktik — sketch FS21_dht22_serial</h2>
{$nan}
<p>Tujuan: setiap ±3 detik Serial menampilkan suhu (°C) dan kelembapan relatif (RH %). DHT22 butuh jeda ≥ ±2 detik antar baca — jangan terlalu cepat.</p>
<ol>
<li>Arduino IDE → <strong>File → New Sketch</strong> → <strong>Simpan sebagai</strong> <code>FS21_dht22_serial</code>.</li>
<li>Ganti isi dengan kode di bawah (salin utuh).</li>
<li><strong>Verify</strong> → <strong>Upload</strong> → tunggu <em>Done uploading</em>.</li>
<li>Buka Serial Monitor baud <strong>115200</strong>.</li>
<li>Tekan <strong>EN (7)</strong> bila perlu restart. Tiup pelan ke sensor — RH biasanya naik.</li>
</ol>
<pre><code class="language-cpp">// FS21_dht22_serial — Full Stack IoT FS-21
// DHT22 di GPIO 4 → suhu &amp; kelembapan ke Serial

#include &lt;DHT.h&gt;

#define DHTPIN 4
#define DHTTYPE DHT22

DHT dht(DHTPIN, DHTTYPE);

void setup() {
  Serial.begin(115200);
  dht.begin();
  delay(2000); // beri waktu sensor siap
  Serial.println("FS21_dht22_serial siap");
}

void loop() {
  float h = dht.readHumidity();
  float t = dht.readTemperature(); // Celsius

  if (isnan(h) || isnan(t)) {
    Serial.println("GAGAL baca — cek wiring / library / jeda");
  } else {
    Serial.print("Suhu: ");
    Serial.print(t);
    Serial.print(" C | RH: ");
    Serial.print(h);
    Serial.println(" %");
  }

  delay(3000); // DHT22: jangan lebih cepat dari ~2 detik
}
</code></pre>
<p><strong>Cara menguji perintah di atas:</strong> uji di <strong>Arduino IDE + Upload + Serial Monitor</strong>. Sukses = angka suhu/RH masuk akal (bukan <code>isnan</code> terus). Bukan perintah Laragon / web server.</p>

<h2 id="fsiot-dht-checklist">Praktik — checklist DHT22 ke Serial</h2>
<p>Centang setiap langkah setelah kamu lakukan di meja. Target: <strong>10/10</strong>.</p>
<ul id="fsiot-dht-checklist-items">
<li>Arduino IDE sudah terbuka sebelum menulis kode</li>
<li>Board ESP32 Dev Module + port sudah dipilih</li>
<li>Library DHT (Adafruit) + Unified Sensor terpasang</li>
<li>Wiring DHT22: VCC 3V3, DATA GPIO 4, GND (modul kit: 3 kabel; pull-up ekstra opsional)</li>
<li>Paham: isnan = gagal baca (bukan “suhu nol”)</li>
<li>Paham: jeda baca ≥ ±2 detik</li>
<li>Sketch disimpan sebagai FS21_dht22_serial</li>
<li>Upload berhasil — Done uploading</li>
<li>Serial menampilkan suhu &amp; RH angka masuk akal</li>
<li>Sadar: GPIO 4 hari ini untuk sensor (bukan tombol FS-19)</li>
</ul>
<p><strong>Cara menguji checklist:</strong> centang di browser setelah praktik di IDE + board. Tidak perlu <code>php artisan</code>.</p>

<h2>Kesalahan yang sering terjadi</h2>
<ul>
<li><strong>Library salah / belum Unified Sensor.</strong> Install keduanya lewat Library Manager.</li>
<li><strong>Interval terlalu cepat.</strong> DHT22 butuh jeda; pakai ±3 detik seperti sketch.</li>
<li><strong>DATA ke pin salah.</strong> FSIOT kunci latihan di <strong>GPIO 4</strong> (bukan GPIO 0).</li>
<li><strong>Bingung soal pull-up.</strong> Modul kit biasanya sudah punya di PCB — jangan merasa wajib pasang 10 kΩ. Tambah eksternal hanya jika sensor mentah / baca gagal terus.</li>
<li><strong>Power ke 5V tanpa perlu.</strong> Mulai dari <strong>3V3</strong>; banyak modul kit cukup di situ.</li>
<li><strong>Mengira nan = suhu 0.</strong> <code>isnan</code> = gagal baca, bukan dingin ekstrem.</li>
<li><strong>Menguji di terminal web.</strong> Sketch hanya jalan di board lewat IDE Upload.</li>
</ul>

<h2>Selanjutnya</h2>
<p><strong>Intinya:</strong> kalau Serial menampilkan suhu &amp; kelembapan yang masuk akal tiap beberapa detik, FS-21 selesai — fondasi “baca sensor” terbuka.</p>
<p>Lanjut ke <strong>FS-22</strong> (LDR + ADC: seberapa terang) saat modulnya terbit.</p>
<p>Daftar modul lengkap: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $ide = $this->ideFigureEn();
        $board = $this->boardFigureEn();
        $kit = $this->kitFigureEn();
        $main = $this->mainWiringFigureEn();
        $sense = $this->senseSvgEn();
        $lib = $this->librarySvgEn();
        $nan = $this->nanSvgEn();

        return <<<HTML
<h2>Introduction — the board’s first sense</h2>
<p>This is article <strong>#91 (this article)</strong> · module <strong>FS-21</strong> on the <em>Full Stack IoT Developer — From Zero</em> path (<strong>BUILDER</strong> phase). Through <strong>FS-20</strong> we drove an LED. Today the board <strong>reads the real world</strong>: temperature and humidity via a <strong>DHT22</strong>.</p>
<p><strong>Analogy:</strong> a DHT22 is like a tiny thermometer + hygrometer that “whispers” numbers on one data wire. We listen on the <strong>Serial Monitor</strong>.</p>
{$sense}
<p><strong>Prerequisites:</strong> FS-18 (you have Uploaded a sketch) · FS-17 (GPIO 4 in the global table) · Arduino IDE can Upload to the ESP32.</p>
<p><strong>How to use this article (work order):</strong></p>
<ol>
<li>Wire the DHT22 on <strong>GPIO 4</strong> (kit module: usually just 3 wires). Match the <strong>main figure</strong>.</li>
<li><strong>Open Arduino IDE</strong> (not Laragon / a web terminal).</li>
<li>Install libraries via <strong>Library Manager</strong> (steps below).</li>
<li>Create sketch <code>FS21_dht22_serial</code> → <strong>Verify</strong> → <strong>Upload</strong>.</li>
<li>Open Serial Monitor at baud <strong>115200</strong> — watch temperature &amp; RH every ≈3 seconds.</li>
<li>Tick the 10/10 checklist in the browser.</li>
</ol>
<p><strong>Not needed today:</strong> Wi-Fi, MQTT, databases, Laragon, <code>php artisan</code>, OLED. Today's tools: <strong>Arduino IDE</strong> + <strong>Library Manager</strong> + <strong>Upload</strong> + ESP32 + DHT22 + jumpers + <strong>Serial Monitor</strong> + <strong>browser</strong>. A 10 kΩ resistor is only a spare (optional).</p>

<h2>Preparation — open &amp; gather these first</h2>
<p><strong>Desk order:</strong> sensor wiring → install libraries in the IDE → Upload → read Serial.</p>
<ol>
<li>Open <strong>Arduino IDE 2.x</strong> · <strong>ESP32 Dev Module</strong> board + port.</li>
<li>Prepare the ESP32 + USB data cable.</li>
<li>Prepare a DHT22 module + jumpers. Keep a 10 kΩ resistor only as a spare.</li>
<li>Find <strong>GPIO 4</strong>, <strong>3V3</strong>, and <strong>GND</strong> on the silkscreen.</li>
<li>Remove the FS-19 button from GPIO 4 if still attached — today that pin is for the sensor.</li>
<li>Have Serial Monitor ready at baud <strong>115200</strong>.</li>
</ol>
<p><strong>Tools used today:</strong> Arduino IDE, Library Manager, Upload, ESP32, USB data, DHT22, jumpers, Serial Monitor, browser. (10 kΩ = optional.)</p>
<p><strong>Not used today:</strong> Laragon, <code>php artisan</code>, PWM LED, button, multimeter (optional), PuTTY.</p>
{$ide}
{$board}
{$kit}
{$main}

<h2>DHT22 wiring (human language)</h2>
<p>Kit modules usually have 3 pins: <strong>VCC</strong>, <strong>DATA</strong> (sometimes labeled <strong>DAT</strong> / <strong>OUT</strong>), and <strong>GND</strong>. Wire VCC to <strong>3V3</strong>, DATA to <strong>GPIO 4</strong>, GND to <strong>GND</strong> — <strong>three wires only</strong> for the common case.</p>
<p><strong>About the pull-up:</strong> the DHT data line does need a pull-up, but <strong>modern kit / AM2302 modules usually already include one on the PCB</strong> (often ≈4.7–5.1 kΩ). So you <strong>do not need</strong> an extra 10 kΩ unless you have a bare 4-pin sensor, a long cable, or Serial keeps showing <code>isnan</code>. Refs: <a href="https://www.adafruit.com/product/393" rel="noopener noreferrer" target="_blank">Adafruit AM2302</a> (internal pull-up) · <a href="https://learn.adafruit.com/dht/connecting-to-a-dhtxx-sensor" rel="noopener noreferrer" target="_blank">Adafruit — Connecting to a DHTxx</a> (bare sensor → add a pull-up).</p>
<p><strong>Read the silkscreen on your module</strong> — pin order can differ by brand; do not copy a photo blindly.</p>

<h2>Library Manager — install the sensor “translator”</h2>
{$lib}
<p>Without a library, DHT22 pulse timing is painful to write by hand. A library is a ready translator. Follow the three boxes above in <strong>Arduino IDE</strong> (not in the browser).</p>

<h2>Practice — sketch FS21_dht22_serial</h2>
{$nan}
<p>Goal: about every 3 seconds Serial prints temperature (°C) and relative humidity (RH %). The DHT22 needs ≥ ≈2 seconds between reads — do not poll too fast.</p>
<ol>
<li>Arduino IDE → <strong>File → New Sketch</strong> → <strong>Save As</strong> <code>FS21_dht22_serial</code>.</li>
<li>Replace the contents with the code below (copy whole).</li>
<li><strong>Verify</strong> → <strong>Upload</strong> → wait for <em>Done uploading</em>.</li>
<li>Open Serial Monitor at baud <strong>115200</strong>.</li>
<li>Press <strong>EN (7)</strong> if you need a restart. Breathe gently on the sensor — RH usually rises.</li>
</ol>
<pre><code class="language-cpp">// FS21_dht22_serial — Full Stack IoT FS-21
// DHT22 on GPIO 4 → temperature &amp; humidity to Serial

#include &lt;DHT.h&gt;

#define DHTPIN 4
#define DHTTYPE DHT22

DHT dht(DHTPIN, DHTTYPE);

void setup() {
  Serial.begin(115200);
  dht.begin();
  delay(2000); // give the sensor time to wake
  Serial.println("FS21_dht22_serial ready");
}

void loop() {
  float h = dht.readHumidity();
  float t = dht.readTemperature(); // Celsius

  if (isnan(h) || isnan(t)) {
    Serial.println("FAILED read — check wiring / library / delay");
  } else {
    Serial.print("Temp: ");
    Serial.print(t);
    Serial.print(" C | RH: ");
    Serial.print(h);
    Serial.println(" %");
  }

  delay(3000); // DHT22: do not go faster than ~2 seconds
}
</code></pre>
<p><strong>How to test the commands above:</strong> test in <strong>Arduino IDE + Upload + Serial Monitor</strong>. Success = sensible temperature/RH numbers (not endless <code>isnan</code>). Not a Laragon / web-server command.</p>

<h2 id="fsiot-dht-checklist">Practice — DHT22 to Serial checklist</h2>
<p>Tick each step after you do it at the desk. Target: <strong>10/10</strong>.</p>
<ul id="fsiot-dht-checklist-items">
<li>Arduino IDE is open before writing code</li>
<li>ESP32 Dev Module board + port are selected</li>
<li>DHT (Adafruit) + Unified Sensor libraries are installed</li>
<li>DHT22 wiring: VCC 3V3, DATA GPIO 4, GND (kit module: 3 wires; extra pull-up optional)</li>
<li>I understand: isnan = failed read (not “zero degrees”)</li>
<li>I understand: read interval ≥ ≈2 seconds</li>
<li>Sketch saved as FS21_dht22_serial</li>
<li>Upload succeeded — Done uploading</li>
<li>Serial shows sensible temperature &amp; RH numbers</li>
<li>I know: GPIO 4 is for the sensor today (not the FS-19 button)</li>
</ul>
<p><strong>How to test the checklist:</strong> tick in the browser after practice on the IDE + board. No <code>php artisan</code> needed.</p>

<h2>Common mistakes</h2>
<ul>
<li><strong>Wrong library / missing Unified Sensor.</strong> Install both via Library Manager.</li>
<li><strong>Interval too fast.</strong> DHT22 needs a pause; use ≈3 seconds like the sketch.</li>
<li><strong>DATA on the wrong pin.</strong> FSIOT locks practice to <strong>GPIO 4</strong> (not GPIO 0).</li>
<li><strong>Confused about the pull-up.</strong> Kit modules usually already have one on the PCB — an extra 10 kΩ is not mandatory. Add external only for a bare sensor / persistent failed reads.</li>
<li><strong>Powering from 5V unnecessarily.</strong> Start from <strong>3V3</strong>; many kit modules are fine there.</li>
<li><strong>Thinking nan means 0 °C.</strong> <code>isnan</code> = failed read, not extreme cold.</li>
<li><strong>Testing in a web terminal.</strong> The sketch runs on the board via IDE Upload only.</li>
</ul>

<h2>Next</h2>
<p><strong>In short:</strong> if Serial shows sensible temperature &amp; humidity every few seconds, FS-21 is done — the “read a sensor” foundation is open.</p>
<p>Continue to <strong>FS-22</strong> (LDR + ADC: how bright) when that module publishes.</p>
<p>Full module list: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }
}
