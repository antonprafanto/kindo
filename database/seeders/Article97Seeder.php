<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article97Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first()
            ?? Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $iotCat) {
            throw new \RuntimeException('User atau kategori iot-smart-device/esp32-arduino tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-bus-uart-i2c-spi';

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
                'title'              => 'Bus komunikasi: UART vs I2C vs SPI (bahasa manusia)',
                'title_en'           => 'Communication buses: UART vs I2C vs SPI (human language)',
                'excerpt'            => 'FS-27 / #97: pilih kabel data dengan sadar. UART = 2 orang · I2C = banyak alat 2 kabel · SPI = cepat. Worksheet di browser — tanpa Upload hari ini.',
                'excerpt_en'         => 'FS-27 / #97: choose data wires on purpose. UART = two people · I2C = many devices on 2 wires · SPI = fast. Browser worksheet — no Upload today.',
                'body'               => $this->body(),
                'body_en'            => $this->bodyEn(),
                'status'             => 'draft',
                'is_featured'        => false,
                'published_at'       => null,
                'seo_title'          => 'UART vs I2C vs SPI — Full Stack IoT #97',
                'seo_title_en'       => 'UART vs I2C vs SPI — Full Stack IoT #97',
                'seo_description'    => 'Bandingkan UART, I2C, SPI dengan bahasa manusia. Worksheet keputusan OLED/BME280/microSD. Modul FS-27.',
                'seo_description_en' => 'Compare UART, I2C, and SPI in human language. Decision worksheet for OLED/BME280/microSD. FS-27 module.',
            ]
        );

        if ($article->published_at !== null || $article->status !== 'draft') {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['fullstack-iot', 'iot', 'esp32'])->pluck('id');
        $article->tags()->sync($tagIds);

        $srcWebp = public_path('images/fsiot/fs27-cover-bus.webp');
        $srcJpg = public_path('images/fsiot/fs27-cover-bus.jpg');
        if (is_file($srcWebp)) {
            $dest = 'articles/covers/fs27-cover-bus.webp';
            \Illuminate\Support\Facades\Storage::disk('public')->put($dest, file_get_contents($srcWebp));
            $article->cover_image = $dest;
            $article->save();
        } elseif (is_file($srcJpg)) {
            $dest = 'articles/covers/fs27-cover-bus.jpg';
            \Illuminate\Support\Facades\Storage::disk('public')->put($dest, file_get_contents($srcJpg));
            $article->cover_image = $dest;
            $article->save();
            try {
                $webp = app(\App\Services\ImageService::class)->processCoverImage($dest);
                if ($webp !== $dest) {
                    $article->cover_image = $webp;
                    $article->save();
                }
            } catch (\Throwable) {
                // Keep JPG if WebP conversion unavailable on host.
            }
        }

        $this->command?->info('✓ Artikel #97 / FS-27 tersimpan sebagai DRAFT: '.$article->title);
    }

    private function toolsFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs27-tools-browser.png" width="1400" height="720" alt="Tools hari ini: browser dan worksheet — tanpa Upload" loading="eager" style="width:100%;height:auto;max-height:720px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Tools hari ini:</strong> <strong>browser</strong> (baca + centang checklist) · catatan/kertas opsional. <strong>Tidak</strong> perlu Arduino IDE Upload, Library Manager baru, Laragon, atau <code>php artisan</code>. Praktik wiring I2C ada di <strong>FS-28</strong>.
    <br>Sumber gambar: diagram langkah buatan Koding Indonesia (FS-27).
  </figcaption>
</figure>
HTML;
    }

    private function toolsFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs27-tools-browser.png" width="1400" height="720" alt="Today’s tools: browser and worksheet — no Upload" loading="eager" style="width:100%;height:auto;max-height:720px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Today’s tools:</strong> the <strong>browser</strong> (read + tick the checklist) · optional paper notes. <strong>No</strong> Arduino IDE Upload, new Library Manager install, Laragon, or <code>php artisan</code>. Real I2C wiring practice is in <strong>FS-28</strong>.
    <br>Image source: step diagram by Koding Indonesia (FS-27).
  </figcaption>
</figure>
HTML;
    }

    private function mainCompareFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs27-bus-compare.png" width="1200" height="920" alt="Gambar utama — perbandingan UART I2C SPI bahasa manusia" loading="eager" style="width:100%;height:auto;max-height:820px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Gambar utama — tiga cara ngobrol antar chip.</strong> UART = 1 lawan 1 · I2C = banyak alat di 2 kabel + alamat · SPI = lebih cepat, lebih banyak pin. Pilih sebelum merakit.
    <br>Sumber gambar: diagram perbandingan buatan Koding Indonesia (FS-27). Acuan konsep: <a href="https://www.analog.com/en/resources/analog-dialogue/articles/i2c-primer-what-is-i2c-part-1.html" rel="noopener noreferrer" target="_blank">Analog Devices — I²C Primer</a> · <a href="https://www.analog.com/en/resources/analog-dialogue/articles/introduction-to-spi-interface.html" rel="noopener noreferrer" target="_blank">Analog Devices — Introduction to SPI</a>.
  </figcaption>
</figure>
HTML;
    }

    private function mainCompareFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs27-bus-compare.png" width="1200" height="920" alt="Main figure — UART I2C SPI comparison in human language" loading="eager" style="width:100%;height:auto;max-height:820px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Main figure — three ways chips talk.</strong> UART = one-to-one · I2C = many devices on 2 wires + addresses · SPI = faster, more pins. Choose before you wire.
    <br>Image source: comparison diagram by Koding Indonesia (FS-27). Concept guides: <a href="https://www.analog.com/en/resources/analog-dialogue/articles/i2c-primer-what-is-i2c-part-1.html" rel="noopener noreferrer" target="_blank">Analog Devices — I²C Primer</a> · <a href="https://www.analog.com/en/resources/analog-dialogue/articles/introduction-to-spi-interface.html" rel="noopener noreferrer" target="_blank">Analog Devices — Introduction to SPI</a>.
  </figcaption>
</figure>
HTML;
    }

    private function decisionFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs27-decision-table.png" width="1200" height="760" alt="Tabel keputusan bus untuk OLED BME280 microSD" loading="eager" style="width:100%;height:auto;max-height:760px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Worksheet visual:</strong> OLED + BME280 → <strong>I2C</strong> · microSD → <strong>SPI</strong> · UART tetap untuk Serial Monitor (debug). Ini jawaban yang akan kamu pakai di FS-28 / FS-36.
    <br>Sumber gambar: tabel keputusan buatan Koding Indonesia (FS-27). Pin I2C jalur ini (tabel FS-17): <strong>SDA = GPIO 21</strong> · <strong>SCL = GPIO 22</strong>.
  </figcaption>
</figure>
HTML;
    }

    private function decisionFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs27-decision-table.png" width="1200" height="760" alt="Bus decision table for OLED BME280 microSD" loading="eager" style="width:100%;height:auto;max-height:760px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Visual worksheet:</strong> OLED + BME280 → <strong>I2C</strong> · microSD → <strong>SPI</strong> · UART stays for Serial Monitor (debug). These are the answers you’ll use in FS-28 / FS-36.
    <br>Image source: decision table by Koding Indonesia (FS-27). Path I2C pins (FS-17 table): <strong>SDA = GPIO 21</strong> · <strong>SCL = GPIO 22</strong>.
  </figcaption>
</figure>
HTML;
    }

    private function i2cCommonsFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs27-i2c-labeled.png" width="1200" height="620" alt="Skema bantu I2C: SDA SCL dan alamat perangkat" loading="eager" style="width:100%;height:auto;max-height:620px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Skema bantu I2C (berlabel Indonesia).</strong> Dua kabel bersama: <strong>SDA</strong> (data) + <strong>SCL</strong> (jam). ESP32 = pengendali · BME280/OLED = perangkat dengan <strong>alamat</strong>. Pin jalur ini: <strong>SDA = GPIO 21</strong> · <strong>SCL = GPIO 22</strong>.
    <br>Sumber gambar: diagram buatan Koding Indonesia (FS-27). Inspirasi struktur: <a href="https://commons.wikimedia.org/wiki/File:I2C.svg" rel="noopener noreferrer" target="_blank">I2C.svg</a> · Wikimedia Commons (Cburnett). Acuan: <a href="https://www.analog.com/en/resources/analog-dialogue/articles/i2c-primer-what-is-i2c-part-1.html" rel="noopener noreferrer" target="_blank">Analog Devices — I²C Primer</a>.
  </figcaption>
</figure>
HTML;
    }

    private function i2cCommonsFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs27-i2c-labeled.png" width="1200" height="620" alt="Helper I2C schematic: SDA SCL and device addresses" loading="eager" style="width:100%;height:auto;max-height:620px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Helper I2C schematic (labeled).</strong> Two shared wires: <strong>SDA</strong> (data) + <strong>SCL</strong> (clock). ESP32 = controller · BME280/OLED = devices with <strong>addresses</strong>. Path pins: <strong>SDA = GPIO 21</strong> · <strong>SCL = GPIO 22</strong>.
    <br>Image source: diagram by Koding Indonesia (FS-27). Structure inspired by <a href="https://commons.wikimedia.org/wiki/File:I2C.svg" rel="noopener noreferrer" target="_blank">I2C.svg</a> · Wikimedia Commons (Cburnett). Guide: <a href="https://www.analog.com/en/resources/analog-dialogue/articles/i2c-primer-what-is-i2c-part-1.html" rel="noopener noreferrer" target="_blank">Analog Devices — I²C Primer</a>.
  </figcaption>
</figure>
HTML;
    }

    private function spiCommonsFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs27-spi-labeled.png" width="1200" height="640" alt="Skema bantu SPI: SCK MOSI MISO CS ke microSD" loading="eager" style="width:100%;height:auto;max-height:640px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Skema bantu SPI (berlabel Indonesia).</strong> Empat sinyal tipikal: <strong>SCK</strong> · <strong>MOSI</strong> · <strong>MISO</strong> · <strong>CS</strong> (Chip Select; di beberapa buku ditulis <strong>SS</strong>). Panah <strong>MISO</strong> balik ke ESP32 (masuk). Cocok untuk microSD karena butuh kecepatan + CS khusus.
    <br>Sumber gambar: diagram buatan Koding Indonesia (FS-27). Inspirasi struktur: <a href="https://commons.wikimedia.org/wiki/File:SPI_single_slave.svg" rel="noopener noreferrer" target="_blank">SPI_single_slave.svg</a> · Wikimedia Commons. Acuan: <a href="https://www.analog.com/en/resources/analog-dialogue/articles/introduction-to-spi-interface.html" rel="noopener noreferrer" target="_blank">Analog Devices — Introduction to SPI</a>.
  </figcaption>
</figure>
HTML;
    }

    private function spiCommonsFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs27-spi-labeled.png" width="1200" height="640" alt="Helper SPI schematic: SCK MOSI MISO CS to microSD" loading="eager" style="width:100%;height:auto;max-height:640px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Helper SPI schematic (labeled).</strong> Four typical signals: <strong>SCK</strong> · <strong>MOSI</strong> · <strong>MISO</strong> · <strong>CS</strong> (Chip Select; some books write <strong>SS</strong>). The <strong>MISO</strong> arrow points back into the ESP32. Fits microSD because it needs speed + a dedicated CS.
    <br>Image source: diagram by Koding Indonesia (FS-27). Structure inspired by <a href="https://commons.wikimedia.org/wiki/File:SPI_single_slave.svg" rel="noopener noreferrer" target="_blank">SPI_single_slave.svg</a> · Wikimedia Commons. Guide: <a href="https://www.analog.com/en/resources/analog-dialogue/articles/introduction-to-spi-interface.html" rel="noopener noreferrer" target="_blank">Analog Devices — Introduction to SPI</a>.
  </figcaption>
</figure>
HTML;
    }

    private function modulContohFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs27-modul-contoh.png" width="1400" height="620" alt="Contoh modul OLED BME280 microSD — I2C vs SPI" loading="eager" style="width:100%;height:auto;max-height:620px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Kenali bentuk modulnya dulu</strong> (bukan wiring hari ini). OLED + BME280 biasanya I2C · microSD biasanya SPI.
    <br>OLED = <strong>ilustrasi bentuk tipikal</strong> modul 0,96" (4 pin GND/VCC/SCL/SDA) oleh Koding Indonesia — bukan foto perangkat lain.
    microSD di foto = <strong>kartu + adapter</strong>; di toko sering berupa papan SPI kecil (bentuk beda, bus tetap SPI) — detail di FS-36.
    <br>Sumber foto (Wikimedia Commons):
    <a href="https://commons.wikimedia.org/wiki/File:SparkFun_Atmospheric_Sensor_Breakout_-_BME280_13676.jpg" rel="noopener noreferrer" target="_blank">SparkFun BME280</a> (CC BY 2.0) ·
    <a href="https://commons.wikimedia.org/wiki/File:2015_Karta_microSD_z_adapterem_SD.jpg" rel="noopener noreferrer" target="_blank">microSD + adapter</a> (CC BY-SA).
    Kolase + ilustrasi OLED: Koding Indonesia (FS-27).
  </figcaption>
</figure>
HTML;
    }

    private function modulContohFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs27-modul-contoh.png" width="1400" height="620" alt="Example OLED BME280 microSD modules — I2C vs SPI" loading="eager" style="width:100%;height:auto;max-height:620px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Recognize the modules first</strong> (no wiring today). OLED + BME280 are typically I2C · microSD is typically SPI.
    <br>OLED = <strong>typical-shape illustration</strong> of a 0.96" module (4 pins GND/VCC/SCL/SDA) by Koding Indonesia — not a photo of another device.
    The microSD photo is a <strong>card + adapter</strong>; shops often sell a small SPI breakout board (different shape, same SPI bus) — details in FS-36.
    <br>Photo sources (Wikimedia Commons):
    <a href="https://commons.wikimedia.org/wiki/File:SparkFun_Atmospheric_Sensor_Breakout_-_BME280_13676.jpg" rel="noopener noreferrer" target="_blank">SparkFun BME280</a> (CC BY 2.0) ·
    <a href="https://commons.wikimedia.org/wiki/File:2015_Karta_microSD_z_adapterem_SD.jpg" rel="noopener noreferrer" target="_blank">microSD + adapter</a> (CC BY-SA).
    Collage + OLED illustration: Koding Indonesia (FS-27).
  </figcaption>
</figure>
HTML;
    }

    private function analogySvgId(): string
    {
        return <<<'HTML'
<figure role="img" aria-label="Analogi manusia untuk UART I2C SPI" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 900 210" width="100%" height="auto" style="display:block;max-height:260px">
    <rect width="900" height="210" fill="#F5F5F0"/>
    <text x="450" y="28" text-anchor="middle" dominant-baseline="central" font-family="Segoe UI,sans-serif" font-size="20" font-weight="700" fill="#1a1a1a">Analogi cepat (ingat ini dulu)</text>
    <rect x="30" y="55" width="260" height="120" rx="10" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
    <text x="160" y="100" text-anchor="middle" dominant-baseline="central" font-family="Segoe UI,sans-serif" font-size="18" font-weight="700" fill="#1B5E20">UART = telepon 1↔1</text>
    <text x="160" y="138" text-anchor="middle" dominant-baseline="central" font-family="Segoe UI,sans-serif" font-size="16" fill="#333">TX / RX / GND</text>
    <rect x="320" y="55" width="260" height="120" rx="10" fill="#BBDEFB" stroke="#1565C0" stroke-width="2.5"/>
    <text x="450" y="100" text-anchor="middle" dominant-baseline="central" font-family="Segoe UI,sans-serif" font-size="18" font-weight="700" fill="#0D47A1">I2C = rapat + nama</text>
    <text x="450" y="138" text-anchor="middle" dominant-baseline="central" font-family="Segoe UI,sans-serif" font-size="16" fill="#333">SDA / SCL + alamat</text>
    <rect x="610" y="55" width="260" height="120" rx="10" fill="#FFF59D" stroke="#F9A825" stroke-width="2.5"/>
    <text x="740" y="100" text-anchor="middle" dominant-baseline="central" font-family="Segoe UI,sans-serif" font-size="18" font-weight="700" fill="#F57F17">SPI = kurir cepat</text>
    <text x="740" y="138" text-anchor="middle" dominant-baseline="central" font-family="Segoe UI,sans-serif" font-size="16" fill="#333">SCK / MOSI / MISO / CS</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Intinya:</strong> jangan hafalkan singkatan dulu — ingat analoginya. Detail pin dilatih di FS-28 (I2C) dan FS-36 (SPI). Sumber gambar: diagram buatan Koding Indonesia (FS-27).
  </figcaption>
</figure>
HTML;
    }

    private function analogySvgEn(): string
    {
        return <<<'HTML'
<figure role="img" aria-label="Human analogies for UART I2C SPI" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 900 210" width="100%" height="auto" style="display:block;max-height:260px">
    <rect width="900" height="210" fill="#F5F5F0"/>
    <text x="450" y="28" text-anchor="middle" dominant-baseline="central" font-family="Segoe UI,sans-serif" font-size="20" font-weight="700" fill="#1a1a1a">Quick analogies (remember these first)</text>
    <rect x="30" y="55" width="260" height="120" rx="10" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
    <text x="160" y="100" text-anchor="middle" dominant-baseline="central" font-family="Segoe UI,sans-serif" font-size="18" font-weight="700" fill="#1B5E20">UART = 1↔1 phone call</text>
    <text x="160" y="138" text-anchor="middle" dominant-baseline="central" font-family="Segoe UI,sans-serif" font-size="16" fill="#333">TX / RX / GND</text>
    <rect x="320" y="55" width="260" height="120" rx="10" fill="#BBDEFB" stroke="#1565C0" stroke-width="2.5"/>
    <text x="450" y="100" text-anchor="middle" dominant-baseline="central" font-family="Segoe UI,sans-serif" font-size="18" font-weight="700" fill="#0D47A1">I2C = meeting + names</text>
    <text x="450" y="138" text-anchor="middle" dominant-baseline="central" font-family="Segoe UI,sans-serif" font-size="16" fill="#333">SDA / SCL + address</text>
    <rect x="610" y="55" width="260" height="120" rx="10" fill="#FFF59D" stroke="#F9A825" stroke-width="2.5"/>
    <text x="740" y="100" text-anchor="middle" dominant-baseline="central" font-family="Segoe UI,sans-serif" font-size="18" font-weight="700" fill="#F57F17">SPI = fast courier</text>
    <text x="740" y="138" text-anchor="middle" dominant-baseline="central" font-family="Segoe UI,sans-serif" font-size="16" fill="#333">SCK / MOSI / MISO / CS</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>In short:</strong> don’t memorize acronyms first — keep the analogies. Pin details are practiced in FS-28 (I2C) and FS-36 (SPI). Image source: diagram by Koding Indonesia (FS-27).
  </figcaption>
</figure>
HTML;
    }

    private function body(): string
    {
        $tools = $this->toolsFigureId();
        $main = $this->mainCompareFigureId();
        $decision = $this->decisionFigureId();
        $modul = $this->modulContohFigureId();
        $i2c = $this->i2cCommonsFigureId();
        $spi = $this->spiCommonsFigureId();
        $analogy = $this->analogySvgId();

        return <<<HTML
<h2>Pendahuluan — jangan colok “asal sama”</h2>
<p>Artikel ini adalah <strong>#97 (ini)</strong> · modul <strong>FS-27</strong> di jalur <em>Full Stack IoT Developer — Dari Nol</em> (fase <strong>BUILDER</strong>). Setelah sensor, relay, dan servo, kamu akan menambah modul yang “ngobrol” lewat <strong>bus</strong> (jalur data bersama). Kalau semua dianggap “colok saja sama”, wiring mudah salah.</p>
<p><strong>Analogi:</strong> UART seperti telepon 1 lawan 1 · I2C seperti rapat dengan daftar nama · SPI seperti kurir cepat yang butuh jalur khusus per paket.</p>
{$analogy}
<p><strong>Glosarium singkat (baca sekali):</strong></p>
<ul>
<li><strong>Bus</strong> = jalur data bersama antar chip (bukan “bus kota”).</li>
<li><strong>UART</strong> = ngobrol 1 lawan 1 (contoh yang sudah kamu pakai: Serial Monitor lewat USB).</li>
<li><strong>I2C</strong> = banyak alat berbagi 2 kabel data + tiap alat punya <em>alamat</em>.</li>
<li><strong>SPI</strong> = lebih cepat, biasanya lebih banyak kabel; sering ada garis <strong>CS</strong> (Chip Select) per chip.</li>
<li><strong>SDA / SCL</strong> = dua kabel I2C (data / jam). Di jalur ini: GPIO <strong>21</strong> / <strong>22</strong>.</li>
</ul>
<p><strong>Prasyarat:</strong> FS-17 (peta pin / tabel pin) · pengalaman Serial Monitor (FS-14) membantu memahami UART tanpa sadar.</p>
<p><strong>Cara pakai artikel ini (urutan kerja):</strong></p>
<ol>
<li><strong>Buka artikel ini di browser</strong> (Chrome/Edge/Firefox biasa). Jangan buka Laragon, terminal, atau Arduino IDE dulu.</li>
<li>Baca tiga analogi UART / I2C / SPI + glosarium di atas.</li>
<li>Lihat gambar utama + skema I2C/SPI + contoh modul + tabel keputusan OLED / BME280 / microSD.</li>
<li>Centang checklist worksheet <strong>10/10</strong> di browser (tombol “Cek kelengkapan”).</li>
<li>Simpan keputusan: I2C untuk sensor+layar · SPI untuk microSD · UART untuk debug Serial.</li>
</ol>
<p><strong>Tidak perlu hari ini:</strong> Arduino IDE Upload, Library Manager baru, Wi-Fi, MQTT, Laragon, <code>php artisan</code>, breadboard baru. Tools hari ini: <strong>browser</strong> + (opsional) catatan. <strong>Tidak ada sintaks C++ yang harus diuji</strong> — ujiannya = centang checklist.</p>

<h2>Persiapan — buka &amp; siapkan ini dulu</h2>
<p><strong>Urutan meja kerja:</strong> buka browser → baca analogi → kenali contoh modul → tabel keputusan → centang checklist. Tidak ada langkah Upload.</p>
{$tools}
<ul>
<li>Buka artikel ini di tab browser (mode pratinjau / nanti live).</li>
<li>Siapkan catatan jika lebih nyaman menulis “OLED = I2C” dll.</li>
<li>Ingat pin I2C jalur ini (FS-17): <strong>SDA = GPIO 21</strong> · <strong>SCL = GPIO 22</strong> (dipakai di FS-28).</li>
</ul>
<p><strong>Alat yang dipakai hari ini:</strong> browser, checklist interaktif, (opsional) kertas.</p>
<p><strong>Tidak dipakai hari ini:</strong> Arduino IDE Upload, Library Manager, Laragon, <code>php artisan</code>, Wi-Fi.</p>

<h2>Tiga bus (bahasa manusia)</h2>
{$main}
<p><strong>UART</strong> — dua arah sederhana (TX mengirim, RX menerima) + GND. Sudah kamu pakai: jendela <strong>Serial Monitor</strong> lewat USB. Cocok untuk debug dan modul 1↔1 (mis. GPS), bukan untuk sepuluh sensor sekaligus.</p>
<p><strong>I2C</strong> — dua kabel data bersama (<strong>SDA</strong>, <strong>SCL</strong>) + tiap perangkat punya <strong>alamat</strong>. Banyak sensor/layar kecil bisa berbagi bus. Di jalur ini: BME280 + OLED (FS-28).</p>
<p><strong>SPI</strong> — lebih cepat, tetapi butuh lebih banyak kabel; tiap chip sering punya garis <strong>CS</strong> sendiri. Cocok untuk microSD / memori cepat (FS-36).</p>
{$i2c}
{$spi}
{$modul}

<h2>Praktik — worksheet keputusan</h2>
<p>Tujuan: untuk tiga modul di jalur, kamu bisa menjawab “pakai bus apa?” tanpa menebak.</p>
{$decision}
<table>
<thead>
<tr><th>Modul</th><th>Pilih bus</th><th>Satu kalimat kenapa</th></tr>
</thead>
<tbody>
<tr><td>OLED 0,96"</td><td>I2C</td><td>Berbagi 2 kabel dengan sensor lain.</td></tr>
<tr><td>BME280</td><td>I2C</td><td>Sensor + alamat di bus yang sama.</td></tr>
<tr><td>microSD</td><td>SPI</td><td>Butuh kecepatan + CS khusus.</td></tr>
</tbody>
</table>
<p><strong>Cara menguji pemahaman di atas:</strong> baca ulang tabel, lalu centang checklist di browser. Bukan perintah di terminal / Laragon. Tidak ada sintaks C++ yang harus di-Verify hari ini.</p>

<h2 id="fsiot-bus-checklist">Praktik — checklist bus</h2>
<p>Centang setiap langkah setelah kamu paham di meja/browser. Target: <strong>10/10</strong>.</p>
<ul id="fsiot-bus-checklist-items">
<li>Artikel dibuka di browser sebelum mengisi checklist</li>
<li>Paham: UART = ngobrol 1 lawan 1 (contoh: Serial Monitor)</li>
<li>Paham: I2C = banyak perangkat, 2 kabel + alamat</li>
<li>Paham: SPI = lebih cepat, lebih banyak kabel / CS</li>
<li>OLED 0,96" dipilih I2C</li>
<li>BME280 dipilih I2C</li>
<li>microSD dipilih SPI</li>
<li>Ingat pin I2C jalur: SDA 21 · SCL 22 (untuk FS-28)</li>
<li>Sadar: hari ini tanpa Upload sketch</li>
<li>Siap lanjut praktik I2C nyata di FS-28</li>
</ul>
<p><strong>Cara menguji checklist:</strong> centang di browser setelah membaca gambar utama + tabel. Tidak perlu <code>php artisan</code> atau IDE.</p>

<h2>Kesalahan yang sering terjadi</h2>
<ul>
<li><strong>Mengira semua sensor “colok saja sama”.</strong> Cek dulu: UART / I2C / SPI di datasheet modul.</li>
<li><strong>Menyamakan Serial Monitor dengan I2C.</strong> Serial Monitor = UART/debug; I2C = bus alamat di pin SDA/SCL.</li>
<li><strong>Memaksakan microSD ke I2C.</strong> Modul microSD tipikal = SPI.</li>
<li><strong>Lupa alamat I2C.</strong> Dua perangkat alamat sama = bentrok (dibahas FS-28).</li>
<li><strong>Mencoba Upload sketch hari ini.</strong> FS-27 = keputusan di kepala; Upload ada di FS-28.</li>
<li><strong>Menguji di Laragon / terminal web.</strong> Worksheet hanya di browser artikel.</li>
</ul>

<h2>Selanjutnya</h2>
<p><strong>Intinya:</strong> kalau kamu bisa memilih I2C untuk OLED+BME280 dan SPI untuk microSD tanpa menebak, FS-27 selesai.</p>
<p>Lanjut ke <strong>FS-28</strong> (praktik I2C: BME280 + OLED di layar) saat modulnya terbit — di situ baru IDE + Library Manager + Upload.</p>
<p>Daftar modul lengkap: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $tools = $this->toolsFigureEn();
        $main = $this->mainCompareFigureEn();
        $decision = $this->decisionFigureEn();
        $modul = $this->modulContohFigureEn();
        $i2c = $this->i2cCommonsFigureEn();
        $spi = $this->spiCommonsFigureEn();
        $analogy = $this->analogySvgEn();

        return <<<HTML
<h2>Introduction — don’t just “plug it the same way”</h2>
<p>This is article <strong>#97 (this article)</strong> · module <strong>FS-27</strong> on the <em>Full Stack IoT Developer — From Zero</em> path (<strong>BUILDER</strong> phase). After sensors, relays, and servos, you’ll add modules that talk over a <strong>bus</strong> (a shared data path). If everything is treated as “just plug it the same,” wiring goes wrong fast.</p>
<p><strong>Analogy:</strong> UART is a one-to-one phone call · I2C is a meeting with name tags · SPI is a fast courier that needs a dedicated lane per package.</p>
{$analogy}
<p><strong>Short glossary (read once):</strong></p>
<ul>
<li><strong>Bus</strong> = a shared data path between chips (not a city bus).</li>
<li><strong>UART</strong> = one-to-one talk (you already used it: Serial Monitor over USB).</li>
<li><strong>I2C</strong> = many devices share 2 data wires + each has an <em>address</em>.</li>
<li><strong>SPI</strong> = faster, usually more wires; often a <strong>CS</strong> (Chip Select) line per chip.</li>
<li><strong>SDA / SCL</strong> = the two I2C wires (data / clock). On this path: GPIO <strong>21</strong> / <strong>22</strong>.</li>
</ul>
<p><strong>Prerequisites:</strong> FS-17 (pin map / pin table) · Serial Monitor experience (FS-14) already showed you UART without naming it.</p>
<p><strong>How to use this article (work order):</strong></p>
<ol>
<li><strong>Open this article in the browser</strong> (normal Chrome/Edge/Firefox). Do not open Laragon, a terminal, or the Arduino IDE first.</li>
<li>Read the three UART / I2C / SPI analogies + the glossary above.</li>
<li>Study the main figure + I2C/SPI schematics + module examples + OLED / BME280 / microSD decision table.</li>
<li>Tick the worksheet checklist to <strong>10/10</strong> in the browser (“Check completeness”).</li>
<li>Keep the decisions: I2C for sensor+display · SPI for microSD · UART for Serial debug.</li>
</ol>
<p><strong>Not needed today:</strong> Arduino IDE Upload, a new Library Manager install, Wi-Fi, MQTT, Laragon, <code>php artisan</code>, a new breadboard. Today's tools: the <strong>browser</strong> + (optional) notes. <strong>No C++ syntax to test</strong> — the test is ticking the checklist.</p>

<h2>Prep — open &amp; set these up first</h2>
<p><strong>Desk order:</strong> open the browser → read analogies → recognize example modules → decision table → tick the checklist. No Upload step.</p>
{$tools}
<ul>
<li>Open this article in a browser tab (preview mode / later live).</li>
<li>Keep notes if writing “OLED = I2C” helps.</li>
<li>Remember this path’s I2C pins (FS-17): <strong>SDA = GPIO 21</strong> · <strong>SCL = GPIO 22</strong> (used in FS-28).</li>
</ul>
<p><strong>Tools used today:</strong> browser, interactive checklist, (optional) paper.</p>
<p><strong>Not used today:</strong> Arduino IDE Upload, Library Manager, Laragon, <code>php artisan</code>, Wi-Fi.</p>

<h2>Three buses (human language)</h2>
{$main}
<p><strong>UART</strong> — simple two-way link (TX sends, RX receives) + GND. You’ve already used it: the <strong>Serial Monitor</strong> over USB. Great for debug and 1↔1 modules (e.g. GPS), not for ten sensors at once.</p>
<p><strong>I2C</strong> — two shared data wires (<strong>SDA</strong>, <strong>SCL</strong>) + each device has an <strong>address</strong>. Many small sensors/displays can share the bus. On this path: BME280 + OLED (FS-28).</p>
<p><strong>SPI</strong> — faster, but more wires; each chip often gets its own <strong>CS</strong> line. Fits microSD / fast memory (FS-36).</p>
{$i2c}
{$spi}
{$modul}

<h2>Practice — decision worksheet</h2>
<p>Goal: for three path modules, you can answer “which bus?” without guessing.</p>
{$decision}
<table>
<thead>
<tr><th>Module</th><th>Choose bus</th><th>One-line why</th></tr>
</thead>
<tbody>
<tr><td>0.96" OLED</td><td>I2C</td><td>Shares 2 wires with other sensors.</td></tr>
<tr><td>BME280</td><td>I2C</td><td>Sensor + address on the same bus.</td></tr>
<tr><td>microSD</td><td>SPI</td><td>Needs speed + a dedicated CS.</td></tr>
</tbody>
</table>
<p><strong>How to test the understanding above:</strong> reread the table, then tick the checklist in the browser. Not a Laragon / terminal command. No C++ syntax to Verify today.</p>

<h2 id="fsiot-bus-checklist">Practice — bus checklist</h2>
<p>Tick each step after you understand it at the desk/browser. Target: <strong>10/10</strong>.</p>
<ul id="fsiot-bus-checklist-items">
<li>Article was opened in the browser before filling the checklist</li>
<li>I understand: UART = one-to-one talk (example: Serial Monitor)</li>
<li>I understand: I2C = many devices, 2 wires + addresses</li>
<li>I understand: SPI = faster, more wires / CS</li>
<li>0.96" OLED chosen as I2C</li>
<li>BME280 chosen as I2C</li>
<li>microSD chosen as SPI</li>
<li>I remember path I2C pins: SDA 21 · SCL 22 (for FS-28)</li>
<li>I know: no sketch Upload today</li>
<li>I’m ready for real I2C practice in FS-28</li>
</ul>
<p><strong>How to test the checklist:</strong> tick in the browser after reading the main figure + table. No <code>php artisan</code> or IDE needed.</p>

<h2>Common mistakes</h2>
<ul>
<li><strong>Assuming every sensor “plugs the same.”</strong> Check the module datasheet first: UART / I2C / SPI.</li>
<li><strong>Equating Serial Monitor with I2C.</strong> Serial Monitor = UART/debug; I2C = address bus on SDA/SCL.</li>
<li><strong>Forcing microSD onto I2C.</strong> Typical microSD modules = SPI.</li>
<li><strong>Forgetting I2C addresses.</strong> Two devices with the same address collide (covered in FS-28).</li>
<li><strong>Trying to Upload a sketch today.</strong> FS-27 is a head decision; Upload is in FS-28.</li>
<li><strong>Testing in Laragon / a web terminal.</strong> The worksheet lives only in the article browser.</li>
</ul>

<h2>Next</h2>
<p><strong>In short:</strong> if you can pick I2C for OLED+BME280 and SPI for microSD without guessing, FS-27 is done.</p>
<p>Continue to <strong>FS-28</strong> (I2C practice: BME280 + OLED on screen) when that module ships — that’s when IDE + Library Manager + Upload return.</p>
<p>Full module list: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }
}
