<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article98Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first()
            ?? Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $iotCat) {
            throw new \RuntimeException('User atau kategori iot-smart-device/esp32-arduino tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-i2c-bme280-oled';

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
                'title'              => 'Praktik I2C: BME280 + OLED (data terbaca di layar)',
                'title_en'           => 'I2C practice: BME280 + OLED (data on the screen)',
                'excerpt'            => 'FS-28 / #98: dua perangkat I2C di SDA 21 / SCL 22. Suhu BME280 muncul di OLED + Serial — pasang library dulu.',
                'excerpt_en'         => 'FS-28 / #98: two I2C devices on SDA 21 / SCL 22. BME280 temperature on OLED + Serial — install libraries first.',
                'body'               => $this->body(),
                'body_en'            => $this->bodyEn(),
                'status'             => 'draft',
                'is_featured'        => false,
                'published_at'       => null,
                'seo_title'          => 'I2C BME280 + OLED — Full Stack IoT #98',
                'seo_title_en'       => 'I2C BME280 + OLED — Full Stack IoT #98',
                'seo_description'    => 'Praktik I2C ESP32: BME280 + OLED SSD1306, GPIO 21/22, Library Manager, sketch FS28_bme280_oled. Modul FS-28.',
                'seo_description_en' => 'ESP32 I2C practice: BME280 + SSD1306 OLED, GPIO 21/22, Library Manager, sketch FS28_bme280_oled. FS-28 module.',
            ]
        );

        if ($article->published_at !== null || $article->status !== 'draft') {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['fullstack-iot', 'iot', 'esp32'])->pluck('id');
        $article->tags()->sync($tagIds);

        $srcWebp = public_path('images/fsiot/fs28-cover-i2c.webp');
        $srcJpg = public_path('images/fsiot/fs28-cover-i2c.jpg');
        if (is_file($srcWebp)) {
            $dest = 'articles/covers/fs28-cover-i2c.webp';
            \Illuminate\Support\Facades\Storage::disk('public')->put($dest, file_get_contents($srcWebp));
            $article->cover_image = $dest;
            $article->save();
        } elseif (is_file($srcJpg)) {
            $dest = 'articles/covers/fs28-cover-i2c.jpg';
            \Illuminate\Support\Facades\Storage::disk('public')->put($dest, file_get_contents($srcJpg));
            $article->cover_image = $dest;
            $article->save();
        }

        $this->command?->info('✓ Artikel #98 / FS-28 tersimpan sebagai DRAFT: '.$article->title);
    }

    private function ideFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem">
  <img src="/images/fsiot/fs11-ide-overview-cite.png" width="1280" height="720" alt="Arduino IDE 2 — Verify, Upload, dan Serial Monitor" loading="eager" style="width:100%;height:auto;max-height:420px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Arduino IDE 2</strong> — tempat menguji sintaks hari ini. Urutan tools: buka IDE → <strong>Library Manager</strong> (3 library) → <strong>Verify</strong> → <strong>Upload</strong> → <strong>Serial Monitor</strong> baud <strong>115200</strong> + lihat OLED. Board: <strong>ESP32 Dev Module</strong>. <em>Catatan gambar:</em> screenshot Commons bisa menampilkan contoh baud lain — <strong>abaikan</strong>; untuk FS-28 pakai kode di bawah + baud 115200.
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:Ide-2-overview.png" rel="noopener noreferrer" target="_blank">Arduino IDE 2 overview</a> · Wikimedia Commons (CC BY-SA 3.0). Panduan: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-serial-monitor/" rel="noopener noreferrer" target="_blank">Serial Monitor</a> · <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-installing-a-library/" rel="noopener noreferrer" target="_blank">Installing a library</a>.
  </figcaption>
</figure>
HTML;
    }

    private function ideFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem">
  <img src="/images/fsiot/fs11-ide-overview-cite.png" width="1280" height="720" alt="Arduino IDE 2 — Verify, Upload, and Serial Monitor" loading="eager" style="width:100%;height:auto;max-height:420px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Arduino IDE 2</strong> — where you test today’s syntax. Tool order: open the IDE → <strong>Library Manager</strong> (3 libraries) → <strong>Verify</strong> → <strong>Upload</strong> → <strong>Serial Monitor</strong> at baud <strong>115200</strong> + watch the OLED. Board: <strong>ESP32 Dev Module</strong>. <em>Image note:</em> the Commons screenshot may show another baud — <strong>ignore it</strong>; for FS-28 use the code below + baud 115200.
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:Ide-2-overview.png" rel="noopener noreferrer" target="_blank">Arduino IDE 2 overview</a> · Wikimedia Commons (CC BY-SA 3.0). Guides: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-serial-monitor/" rel="noopener noreferrer" target="_blank">Serial Monitor</a> · <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-installing-a-library/" rel="noopener noreferrer" target="_blank">Installing a library</a>.
  </figcaption>
</figure>
HTML;
    }

    private function toolsFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs28-tools-ide.png" width="1400" height="720" alt="Urutan tools: wiring, library, Upload, checklist" loading="eager" style="width:100%;height:auto;max-height:640px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Urutan meja kerja:</strong> rakit wiring → pasang 3 library → Upload sketch → centang checklist di browser. Sumber gambar: diagram buatan Koding Indonesia (FS-28).
  </figcaption>
</figure>
HTML;
    }

    private function toolsFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs28-tools-ide.png" width="1400" height="720" alt="Tool order: wiring, libraries, Upload, checklist" loading="eager" style="width:100%;height:auto;max-height:640px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Desk order:</strong> wire first → install 3 libraries → Upload the sketch → tick the checklist in the browser. Image source: diagram by Koding Indonesia (FS-28).
  </figcaption>
</figure>
HTML;
    }

    private function libraryManagerFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs28-library-manager.png" width="1200" height="560" alt="Tiga library: Adafruit GFX, SSD1306, BME280" loading="eager" style="width:100%;height:auto;max-height:480px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Wajib sebelum Verify:</strong> Library Manager → pasang berurutan <strong>Adafruit GFX Library</strong> → <strong>Adafruit SSD1306</strong> → <strong>Adafruit BME280</strong> (kalau diminta dependensi <em>Adafruit Unified Sensor</em>, Install juga). Lewati langkah ini = error <code>#include</code> merah.
    <br>Sumber gambar: diagram langkah Koding Indonesia (FS-28). Panduan menu: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-installing-a-library/" rel="noopener noreferrer" target="_blank">Arduino Docs — Installing a library</a>.
  </figcaption>
</figure>
HTML;
    }

    private function libraryManagerFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs28-library-manager.png" width="1200" height="560" alt="Three libraries: Adafruit GFX, SSD1306, BME280" loading="eager" style="width:100%;height:auto;max-height:480px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Required before Verify:</strong> Library Manager → install in order <strong>Adafruit GFX Library</strong> → <strong>Adafruit SSD1306</strong> → <strong>Adafruit BME280</strong> (if asked for <em>Adafruit Unified Sensor</em>, install that too). Skip this and <code>#include</code> lines often fail.
    <br>Image source: step diagram by Koding Indonesia (FS-28). Menu guide: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-installing-a-library/" rel="noopener noreferrer" target="_blank">Arduino Docs — Installing a library</a>.
  </figcaption>
</figure>
HTML;
    }

    private function mainWiringFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs28-i2c-breadboard.png" width="1549" height="746" alt="Gambar utama — rangkaian I2C BME280 + OLED di breadboard GPIO 21/22" loading="eager" style="width:100%;height:auto;max-height:640px;object-fit:contain;border-radius:6px;background:#fff">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Gambar utama — rangkaian I2C di breadboard.</strong> Legenda warna: <strong>biru</strong> = <strong>IO21 / GPIO 21</strong> → <strong>SDA</strong> (BME280 + OLED) · <strong>hijau</strong> = <strong>IO22 / GPIO 22</strong> → <strong>SCL</strong> (di OLED sering tertulis <strong>SCK</strong> — itu jam I2C yang sama) · <strong>merah</strong> = <strong>3V3</strong> → VCC · <strong>hitam</strong> = <strong>GND</strong> bersama. Kedua modul berbagi bus yang sama.
    <br>Tip BME280: pakai baris pin <strong>SCL / SDA / 3.3V / GND</strong>; baris bawah (<code>!CS</code>, <code>SDI</code>, …) untuk SPI — biarkan kosong. Sumber gambar: diagram rangkaian Koding Indonesia (FS-28).
  </figcaption>
</figure>
HTML;
    }

    private function mainWiringFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs28-i2c-breadboard.png" width="1549" height="746" alt="Main figure — I2C BME280 + OLED breadboard wiring GPIO 21/22" loading="eager" style="width:100%;height:auto;max-height:640px;object-fit:contain;border-radius:6px;background:#fff">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Main figure — I2C on a breadboard.</strong> Color legend: <strong>blue</strong> = <strong>IO21 / GPIO 21</strong> → <strong>SDA</strong> (BME280 + OLED) · <strong>green</strong> = <strong>IO22 / GPIO 22</strong> → <strong>SCL</strong> (on many OLEDs labeled <strong>SCK</strong> — same I2C clock) · <strong>red</strong> = <strong>3V3</strong> → VCC · <strong>black</strong> = shared <strong>GND</strong>. Both modules share one bus.
    <br>BME280 tip: use the <strong>SCL / SDA / 3.3V / GND</strong> row; the lower row (<code>!CS</code>, <code>SDI</code>, …) is for SPI — leave it empty. Image source: wiring diagram by Koding Indonesia (FS-28).
  </figcaption>
</figure>
HTML;
    }

    private function schemaWiringFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs28-i2c-wiring.png" width="1200" height="720" alt="Skema bantu — ringkasan pin I2C ESP32 BME280 OLED" loading="eager" style="width:100%;height:auto;max-height:560px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Skema bantu (ringkas).</strong> Pin sama gambar utama: <strong>SDA = GPIO 21</strong> · <strong>SCL = GPIO 22</strong> · VCC <strong>3V3</strong> · GND bersama · BME280 ≈ <strong>0x76</strong> · OLED ≈ <strong>0x3C</strong>. Pakai ini jika lebih nyaman membaca kotak pin daripada foto breadboard.
    <br>Sumber gambar: diagram Koding Indonesia (FS-28). Acuan konsep: <a href="https://www.analog.com/en/resources/analog-dialogue/articles/i2c-primer-what-is-i2c-part-1.html" rel="noopener noreferrer" target="_blank">Analog Devices — I²C Primer</a>.
  </figcaption>
</figure>
HTML;
    }

    private function schemaWiringFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs28-i2c-wiring.png" width="1200" height="720" alt="Helper schematic — I2C pin summary ESP32 BME280 OLED" loading="eager" style="width:100%;height:auto;max-height:560px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Helper schematic (compact).</strong> Same pins as the main figure: <strong>SDA = GPIO 21</strong> · <strong>SCL = GPIO 22</strong> · VCC <strong>3V3</strong> · shared GND · BME280 ≈ <strong>0x76</strong> · OLED ≈ <strong>0x3C</strong>. Use this if pin boxes are easier than the breadboard photo.
    <br>Image source: diagram by Koding Indonesia (FS-28). Concept: <a href="https://www.analog.com/en/resources/analog-dialogue/articles/i2c-primer-what-is-i2c-part-1.html" rel="noopener noreferrer" target="_blank">Analog Devices — I²C Primer</a>.
  </figcaption>
</figure>
HTML;
    }

    private function modulFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs28-modul-kit.png" width="1400" height="620" alt="Kit modul BME280 dan OLED untuk I2C" loading="eager" style="width:100%;height:auto;max-height:560px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Kenali modul dulu.</strong> BME280 = sensor I2C · OLED 0,96" = layar I2C · keduanya berbagi SDA/SCL. OLED di gambar = <strong>ilustrasi bentuk tipikal</strong> (bukan foto perangkat lain).
    <br>Sumber foto BME280: <a href="https://commons.wikimedia.org/wiki/File:SparkFun_Atmospheric_Sensor_Breakout_-_BME280_13676.jpg" rel="noopener noreferrer" target="_blank">SparkFun BME280</a> (CC BY 2.0). Ilustrasi OLED + kolase: Koding Indonesia (FS-28).
  </figcaption>
</figure>
HTML;
    }

    private function modulFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs28-modul-kit.png" width="1400" height="620" alt="BME280 and OLED module kit for I2C" loading="eager" style="width:100%;height:auto;max-height:560px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Recognize the modules first.</strong> BME280 = I2C sensor · 0.96" OLED = I2C display · both share SDA/SCL. The OLED panel is a <strong>typical-shape illustration</strong> (not a photo of another device).
    <br>BME280 photo: <a href="https://commons.wikimedia.org/wiki/File:SparkFun_Atmospheric_Sensor_Breakout_-_BME280_13676.jpg" rel="noopener noreferrer" target="_blank">SparkFun BME280</a> (CC BY 2.0). OLED illustration + collage: Koding Indonesia (FS-28).
  </figcaption>
</figure>
HTML;
    }

    private function successFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs28-success-oled-serial.png" width="1200" height="520" alt="Sukses: angka OLED sama dengan Serial Monitor" loading="eager" style="width:100%;height:auto;max-height:480px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Intinya:</strong> sukses = angka suhu/tekanan di <strong>OLED</strong> selaras baris di <strong>Serial Monitor</strong> (baud 115200). Sumber gambar: diagram Koding Indonesia (FS-28).
  </figcaption>
</figure>
HTML;
    }

    private function successFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs28-success-oled-serial.png" width="1200" height="520" alt="Success: OLED numbers match Serial Monitor" loading="eager" style="width:100%;height:auto;max-height:480px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>In short:</strong> success = temperature/pressure on the <strong>OLED</strong> matches the <strong>Serial Monitor</strong> lines (baud 115200). Image source: diagram by Koding Indonesia (FS-28).
  </figcaption>
</figure>
HTML;
    }

    private function analogySvgId(): string
    {
        return <<<'HTML'
<figure role="img" aria-label="Analogi I2C: satu rapat dua nama" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 900 200" width="100%" height="auto" style="display:block;max-height:240px">
    <rect width="900" height="200" fill="#F5F5F0"/>
    <text x="450" y="28" text-anchor="middle" dominant-baseline="central" font-family="Segoe UI,sans-serif" font-size="20" font-weight="700" fill="#1a1a1a">Analogi: satu rapat · dua nama (alamat)</text>
    <rect x="40" y="55" width="250" height="120" rx="10" fill="#FFF8E1" stroke="#F9A825" stroke-width="2.5"/>
    <text x="165" y="100" text-anchor="middle" dominant-baseline="central" font-family="Segoe UI,sans-serif" font-size="18" font-weight="700" fill="#F57F17">ESP32 = ketua rapat</text>
    <text x="165" y="138" text-anchor="middle" dominant-baseline="central" font-family="Segoe UI,sans-serif" font-size="15" fill="#333">panggil nama alat</text>
    <rect x="325" y="55" width="250" height="120" rx="10" fill="#BBDEFB" stroke="#1565C0" stroke-width="2.5"/>
    <text x="450" y="100" text-anchor="middle" dominant-baseline="central" font-family="Segoe UI,sans-serif" font-size="18" font-weight="700" fill="#0D47A1">BME280 = 0x76</text>
    <text x="450" y="138" text-anchor="middle" dominant-baseline="central" font-family="Segoe UI,sans-serif" font-size="15" fill="#333">sensor menjawab</text>
    <rect x="610" y="55" width="250" height="120" rx="10" fill="#C8E6C9" stroke="#2E7D32" stroke-width="2.5"/>
    <text x="735" y="100" text-anchor="middle" dominant-baseline="central" font-family="Segoe UI,sans-serif" font-size="18" font-weight="700" fill="#1B5E20">OLED = 0x3C</text>
    <text x="735" y="138" text-anchor="middle" dominant-baseline="central" font-family="Segoe UI,sans-serif" font-size="15" fill="#333">layar menampilkan</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Intinya:</strong> dua alat berbagi 2 kabel (SDA/SCL), tapi masing-masing punya <strong>alamat</strong> — jangan bentrok. Sumber gambar: diagram Koding Indonesia (FS-28).
  </figcaption>
</figure>
HTML;
    }

    private function analogySvgEn(): string
    {
        return <<<'HTML'
<figure role="img" aria-label="I2C analogy: one meeting two names" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 900 200" width="100%" height="auto" style="display:block;max-height:240px">
    <rect width="900" height="200" fill="#F5F5F0"/>
    <text x="450" y="28" text-anchor="middle" dominant-baseline="central" font-family="Segoe UI,sans-serif" font-size="20" font-weight="700" fill="#1a1a1a">Analogy: one meeting · two names (addresses)</text>
    <rect x="40" y="55" width="250" height="120" rx="10" fill="#FFF8E1" stroke="#F9A825" stroke-width="2.5"/>
    <text x="165" y="100" text-anchor="middle" dominant-baseline="central" font-family="Segoe UI,sans-serif" font-size="18" font-weight="700" fill="#F57F17">ESP32 = chair</text>
    <text x="165" y="138" text-anchor="middle" dominant-baseline="central" font-family="Segoe UI,sans-serif" font-size="15" fill="#333">calls each device</text>
    <rect x="325" y="55" width="250" height="120" rx="10" fill="#BBDEFB" stroke="#1565C0" stroke-width="2.5"/>
    <text x="450" y="100" text-anchor="middle" dominant-baseline="central" font-family="Segoe UI,sans-serif" font-size="18" font-weight="700" fill="#0D47A1">BME280 = 0x76</text>
    <text x="450" y="138" text-anchor="middle" dominant-baseline="central" font-family="Segoe UI,sans-serif" font-size="15" fill="#333">sensor answers</text>
    <rect x="610" y="55" width="250" height="120" rx="10" fill="#C8E6C9" stroke="#2E7D32" stroke-width="2.5"/>
    <text x="735" y="100" text-anchor="middle" dominant-baseline="central" font-family="Segoe UI,sans-serif" font-size="18" font-weight="700" fill="#1B5E20">OLED = 0x3C</text>
    <text x="735" y="138" text-anchor="middle" dominant-baseline="central" font-family="Segoe UI,sans-serif" font-size="15" fill="#333">display shows it</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>In short:</strong> two devices share 2 wires (SDA/SCL), but each has an <strong>address</strong> — avoid collisions. Image source: diagram by Koding Indonesia (FS-28).
  </figcaption>
</figure>
HTML;
    }

    private function sketchCode(): string
    {
        return <<<'CODE'
// FS28_bme280_oled — Full Stack IoT FS-28
#include &lt;Wire.h&gt;
#include &lt;Adafruit_GFX.h&gt;
#include &lt;Adafruit_SSD1306.h&gt;
#include &lt;Adafruit_BME280.h&gt;

#define SCREEN_W 128
#define SCREEN_H 64
#define OLED_ADDR 0x3C
#define BME_ADDR  0x76   // ganti 0x77 jika modulmu memakai itu

Adafruit_SSD1306 display(SCREEN_W, SCREEN_H, &amp;Wire, -1);
Adafruit_BME280 bme;

void setup() {
  Serial.begin(115200);
  delay(200);
  Wire.begin(21, 22);  // SDA=21 · SCL=22 (tabel FS-17)

  Serial.println("FS28_bme280_oled ready");

  if (! display.begin(SSD1306_SWITCHCAPVCC, OLED_ADDR)) {
    Serial.println("OLED gagal — cek wiring / alamat 0x3C");
    while (true) { delay(1000); }
  }
  Serial.println("OLED OK @ 0x3C");

  if (! bme.begin(BME_ADDR)) {
    Serial.println("BME gagal — coba alamat 0x77 atau cek wiring");
    while (true) { delay(1000); }
  }
  Serial.println("BME OK @ 0x76/0x77");

  display.clearDisplay();
  display.setTextColor(SSD1306_WHITE);
  display.setTextSize(1);
  display.setCursor(0, 0);
  display.println("FS-28 I2C OK");
  display.display();
  delay(800);
}

void loop() {
  float t = bme.readTemperature();
  float p = bme.readPressure() / 100.0F;

  Serial.print("T=");
  Serial.print(t, 1);
  Serial.print(" C  P=");
  Serial.print(p, 1);
  Serial.println(" hPa");

  display.clearDisplay();
  display.setTextSize(2);
  display.setCursor(0, 8);
  display.print(t, 1);
  display.println(" C");
  display.setCursor(0, 40);
  display.print(p, 0);
  display.println(" hPa");
  display.display();

  delay(1000);
}
CODE;
    }

    private function body(): string
    {
        $ide = $this->ideFigureId();
        $tools = $this->toolsFigureId();
        $lib = $this->libraryManagerFigureId();
        $main = $this->mainWiringFigureId();
        $schema = $this->schemaWiringFigureId();
        $modul = $this->modulFigureId();
        $success = $this->successFigureId();
        $analogy = $this->analogySvgId();
        $code = $this->sketchCode();

        return <<<HTML
<h2>Pendahuluan — dua alat, satu jalur ngobrol</h2>
<p>Artikel ini adalah <strong>#98 (ini)</strong> · modul <strong>FS-28</strong> di jalur <em>Full Stack IoT Developer — Dari Nol</em> (fase <strong>BUILDER</strong>). Di <strong>FS-27</strong> kamu memilih bus dengan sadar. Hari ini praktik nyata: <strong>BME280</strong> + <strong>OLED</strong> berbagi I2C.</p>
<p><strong>Analogi:</strong> satu meja rapat (2 kabel SDA/SCL) · tiap peserta punya nama (alamat) · ketua (ESP32) memanggil bergantian.</p>
{$analogy}
<p><strong>Glosarium singkat:</strong></p>
<ul>
<li><strong>I2C</strong> = bus 2 kabel data + alamat per perangkat.</li>
<li><strong>SDA / SCL</strong> = data / jam — di jalur ini <strong>GPIO 21 / 22</strong>.</li>
<li><strong>Alamat</strong> = “nama” di bus (contoh OLED <code>0x3C</code>, BME280 <code>0x76</code>).</li>
<li><strong>Kenapa BME280 jika sudah ada DHT22?</strong> DHT22 = sensor cerita utama (1 kabel data). BME280 = belajar I2C + tekanan udara. Boleh bandingkan keduanya nanti.</li>
</ul>
<p><strong>Prasyarat:</strong> FS-27 (pilih bus) · FS-21 (sensor ke Serial) · FS-14 (Upload + Serial Monitor) · pin FS-17.</p>
<p><strong>Cara pakai artikel ini (urutan kerja):</strong></p>
<ol>
<li>Rakit wiring (cocokkan gambar utama).</li>
<li><strong>Buka Arduino IDE dulu</strong> (bukan Laragon / terminal web).</li>
<li><strong>Library Manager</strong> → pasang <strong>Adafruit GFX</strong> → <strong>Adafruit SSD1306</strong> → <strong>Adafruit BME280</strong>.</li>
<li>Buat sketch <code>FS28_bme280_oled</code> → salin kode → <strong>Verify</strong> → <strong>Upload</strong>.</li>
<li>Buka <strong>Serial Monitor</strong> baud <strong>115200</strong> dan lihat angka di OLED.</li>
<li>Centang checklist 10/10 di browser.</li>
</ol>
<p><strong>Tidak perlu hari ini:</strong> Wi-Fi, MQTT, Laragon, <code>php artisan</code>, SPI/microSD, breadboard ekstra selain I2C. Tools hari ini: <strong>Arduino IDE</strong> + <strong>Library Manager</strong> + <strong>Upload</strong> + ESP32 + BME280 + OLED + jumper + <strong>Serial Monitor</strong> + <strong>browser</strong>.</p>

<h2>Persiapan — buka &amp; siapkan ini dulu</h2>
<p><strong>Urutan meja kerja:</strong> wiring → IDE → library → Upload → Serial + OLED → checklist.</p>
{$tools}
{$ide}
{$lib}
<ul>
<li>Buka <strong>Arduino IDE 2.x</strong> · board <strong>ESP32 Dev Module</strong> + port USB data.</li>
<li>Siapkan BME280 (I2C) + OLED 0,96" (I2C) + jumper.</li>
<li>Cari label <strong>GPIO 21</strong> / <strong>IO21</strong> (SDA) dan <strong>GPIO 22</strong> / <strong>IO22</strong> (SCL), plus <strong>3V3</strong> dan <strong>GND</strong>.</li>
<li>Siapkan Serial Monitor baud <strong>115200</strong>.</li>
</ul>
<p><strong>Alat yang dipakai hari ini:</strong> Arduino IDE, Library Manager, Upload, ESP32, BME280, OLED, jumper, Serial Monitor, browser.</p>
<p><strong>Tidak dipakai hari ini:</strong> Laragon, <code>php artisan</code>, Wi-Fi, MQTT, microSD/SPI.</p>

<h2>Wiring (bahasa manusia)</h2>
{$modul}
{$main}
{$schema}
<p><strong>Blok kabel (kedua modul sama pola):</strong></p>
<ul>
<li><strong>VCC</strong> → <strong>3V3</strong> ESP32 (modul I2C tipikal 3,3 V — cek silkscreen; jangan asal 5V jika tertulis 3V3-only)</li>
<li><strong>GND</strong> → <strong>GND</strong> ESP32</li>
<li><strong>SDA</strong> → <strong>GPIO 21</strong> (di foto: kabel <strong>biru</strong>)</li>
<li><strong>SCL</strong> → <strong>GPIO 22</strong> (di foto: kabel <strong>hijau</strong>; di OLED sering tertulis <strong>SCK</strong>)</li>
</ul>
<p><strong>Intinya:</strong> SDA ke SDA, SCL ke SCL — dua modul “nyambung paralel” di bus yang sama. Banyak modul sudah punya pull-up internal; kalau tidak ketemu di Serial, baru curiga pull-up / alamat.</p>

<h2>Praktik — sketch FS28_bme280_oled</h2>
<p>Tujuan: pasang 3 library Adafruit, baca BME280, tampilkan suhu &amp; tekanan di OLED, cetak yang sama ke Serial.</p>
<ol>
<li><strong>Buka Arduino IDE</strong> dulu (bukan Laragon).</li>
<li><strong>Sketch → Include Library → Manage Libraries…</strong> → Install <strong>Adafruit GFX Library</strong>, lalu <strong>Adafruit SSD1306</strong>, lalu <strong>Adafruit BME280</strong>.</li>
<li><strong>File → New Sketch</strong> → <strong>Simpan sebagai</strong> <code>FS28_bme280_oled</code>.</li>
<li>Ganti isi dengan kode di bawah (salin utuh).</li>
<li><strong>Verify</strong> → <strong>Upload</strong> → tunggu <em>Done uploading</em>.</li>
<li>Buka Serial Monitor baud <strong>115200</strong> dan lihat OLED.</li>
</ol>
<pre><code class="language-cpp">{$code}</code></pre>
<p><strong>Cara menguji perintah di atas:</strong> uji di <strong>Arduino IDE + Library Manager + Upload + Serial Monitor</strong>, lalu cocokkan angka di OLED. Bukan perintah Laragon / web server. Jika BME gagal, ganti <code>BME_ADDR</code> ke <code>0x77</code> lalu Upload lagi.</p>
{$success}

<h2 id="fsiot-i2c-checklist">Praktik — checklist I2C</h2>
<p>Centang setiap langkah setelah kamu lakukan di meja. Target: <strong>10/10</strong>.</p>
<ul id="fsiot-i2c-checklist-items">
<li>Arduino IDE sudah terbuka sebelum menulis kode</li>
<li>Library GFX + SSD1306 + BME280 terpasang lewat Library Manager</li>
<li>Wiring: SDA→21, SCL→22, VCC→3V3, GND bersama</li>
<li>Paham: dua modul berbagi bus, beda alamat</li>
<li>Sketch disimpan sebagai FS28_bme280_oled</li>
<li>Upload berhasil — Done uploading</li>
<li>Serial baud 115200 menampilkan T=… dan P=…</li>
<li>OLED menampilkan angka yang selaras Serial</li>
<li>Paham: BME280 melatih I2C; DHT22 tetap sensor cerita utama</li>
<li>Sadar: gate BUILDER hampir selesai — siap review istilah + foto wiring sendiri</li>
</ul>
<p><strong>Cara menguji checklist:</strong> centang di browser setelah praktik di IDE + board. Tidak perlu <code>php artisan</code>.</p>

<h2>Kesalahan yang sering terjadi</h2>
<ul>
<li><strong>SDA/SCL tertukar.</strong> SDA hanya ke GPIO 21 · SCL ke GPIO 22.</li>
<li><strong>Bingung label OLED “SCK”.</strong> Di modul I2C 0,96", <strong>SCK = SCL</strong> (jam) — sambungkan ke GPIO 22.</li>
<li><strong>Alamat BME salah (0x76 vs 0x77).</strong> Ganti konstanta lalu Upload ulang.</li>
<li><strong>Library belum lengkap.</strong> Pasang GFX dulu, lalu SSD1306, lalu BME280 (+ Unified Sensor jika diminta).</li>
<li><strong>VCC ke 5V pada modul 3V3-only.</strong> Cek silkscreen; banyak breakout I2C aman di 3V3.</li>
<li><strong>Menguji di Laragon / terminal web.</strong> Sketch hanya jalan di board lewat IDE Upload.</li>
<li><strong>Mengira Serial Monitor = I2C.</strong> Serial Monitor = UART/debug; I2C = bus di pin 21/22.</li>
</ul>

<h2>Selanjutnya</h2>
<p><strong>Intinya:</strong> kalau OLED dan Serial menampilkan suhu/tekanan yang selaras, FS-28 selesai — kamu sudah praktik multi-device I2C.</p>
<p>Ini penutup praktik fase <strong>BUILDER</strong> sebelum gate ke <strong>CONNECTED</strong> (Wi-Fi di FS-29) saat modulnya terbit. Ambil foto wiring kamu sendiri sebagai bukti meja kerja.</p>
<p>Daftar modul lengkap: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $ide = $this->ideFigureEn();
        $tools = $this->toolsFigureEn();
        $lib = $this->libraryManagerFigureEn();
        $main = $this->mainWiringFigureEn();
        $schema = $this->schemaWiringFigureEn();
        $modul = $this->modulFigureEn();
        $success = $this->successFigureEn();
        $analogy = $this->analogySvgEn();
        $code = $this->sketchCode();

        return <<<HTML
<h2>Introduction — two devices, one shared talk path</h2>
<p>This is article <strong>#98 (this article)</strong> · module <strong>FS-28</strong> on the <em>Full Stack IoT Developer — From Zero</em> path (<strong>BUILDER</strong> phase). In <strong>FS-27</strong> you chose a bus on purpose. Today is real practice: <strong>BME280</strong> + <strong>OLED</strong> sharing I2C.</p>
<p><strong>Analogy:</strong> one meeting table (2 wires SDA/SCL) · each guest has a name (address) · the chair (ESP32) calls them in turn.</p>
{$analogy}
<p><strong>Short glossary:</strong></p>
<ul>
<li><strong>I2C</strong> = a 2-wire data bus + an address per device.</li>
<li><strong>SDA / SCL</strong> = data / clock — on this path <strong>GPIO 21 / 22</strong>.</li>
<li><strong>Address</strong> = the “name” on the bus (e.g. OLED <code>0x3C</code>, BME280 <code>0x76</code>).</li>
<li><strong>Why BME280 if you already have DHT22?</strong> DHT22 is the main story sensor (1 data wire). BME280 teaches I2C + air pressure. You may compare both later.</li>
</ul>
<p><strong>Prerequisites:</strong> FS-27 (choose a bus) · FS-21 (sensor to Serial) · FS-14 (Upload + Serial Monitor) · FS-17 pins.</p>
<p><strong>How to use this article (work order):</strong></p>
<ol>
<li>Wire the modules (match the main figure).</li>
<li><strong>Open Arduino IDE first</strong> (not Laragon / a web terminal).</li>
<li><strong>Library Manager</strong> → install <strong>Adafruit GFX</strong> → <strong>Adafruit SSD1306</strong> → <strong>Adafruit BME280</strong>.</li>
<li>Create sketch <code>FS28_bme280_oled</code> → paste the code → <strong>Verify</strong> → <strong>Upload</strong>.</li>
<li>Open <strong>Serial Monitor</strong> at baud <strong>115200</strong> and watch the OLED.</li>
<li>Tick the 10/10 checklist in the browser.</li>
</ol>
<p><strong>Not needed today:</strong> Wi-Fi, MQTT, Laragon, <code>php artisan</code>, SPI/microSD, extra breadboards beyond I2C. Today's tools: <strong>Arduino IDE</strong> + <strong>Library Manager</strong> + <strong>Upload</strong> + ESP32 + BME280 + OLED + jumpers + <strong>Serial Monitor</strong> + <strong>browser</strong>.</p>

<h2>Prep — open &amp; set these up first</h2>
<p><strong>Desk order:</strong> wiring → IDE → libraries → Upload → Serial + OLED → checklist.</p>
{$tools}
{$ide}
{$lib}
<ul>
<li>Open <strong>Arduino IDE 2.x</strong> · board <strong>ESP32 Dev Module</strong> + a data USB port.</li>
<li>Prepare a BME280 (I2C) + 0.96" OLED (I2C) + jumpers.</li>
<li>Find labels <strong>GPIO 21</strong> / <strong>IO21</strong> (SDA) and <strong>GPIO 22</strong> / <strong>IO22</strong> (SCL), plus <strong>3V3</strong> and <strong>GND</strong>.</li>
<li>Prepare Serial Monitor at baud <strong>115200</strong>.</li>
</ul>
<p><strong>Tools used today:</strong> Arduino IDE, Library Manager, Upload, ESP32, BME280, OLED, jumpers, Serial Monitor, browser.</p>
<p><strong>Not used today:</strong> Laragon, <code>php artisan</code>, Wi-Fi, MQTT, microSD/SPI.</p>

<h2>Wiring (human language)</h2>
{$modul}
{$main}
{$schema}
<p><strong>Cable block (same pattern for both modules):</strong></p>
<ul>
<li><strong>VCC</strong> → ESP32 <strong>3V3</strong> (typical I2C modules are 3.3 V — check silkscreen; don’t force 5V on 3V3-only boards)</li>
<li><strong>GND</strong> → ESP32 <strong>GND</strong></li>
<li><strong>SDA</strong> → <strong>GPIO 21</strong> (in the photo: the <strong>blue</strong> wire)</li>
<li><strong>SCL</strong> → <strong>GPIO 22</strong> (in the photo: the <strong>green</strong> wire; on OLEDs often labeled <strong>SCK</strong>)</li>
</ul>
<p><strong>In short:</strong> SDA to SDA, SCL to SCL — both modules sit in parallel on the same bus. Many modules already include pull-ups; if Serial never finds them, then suspect pull-ups / addresses.</p>

<h2>Practice — sketch FS28_bme280_oled</h2>
<p>Goal: install 3 Adafruit libraries, read the BME280, show temperature &amp; pressure on the OLED, print the same values to Serial.</p>
<ol>
<li><strong>Open Arduino IDE</strong> first (not Laragon).</li>
<li><strong>Sketch → Include Library → Manage Libraries…</strong> → Install <strong>Adafruit GFX Library</strong>, then <strong>Adafruit SSD1306</strong>, then <strong>Adafruit BME280</strong>.</li>
<li><strong>File → New Sketch</strong> → <strong>Save As</strong> <code>FS28_bme280_oled</code>.</li>
<li>Replace the contents with the code below (paste whole).</li>
<li><strong>Verify</strong> → <strong>Upload</strong> → wait for <em>Done uploading</em>.</li>
<li>Open Serial Monitor at baud <strong>115200</strong> and watch the OLED.</li>
</ol>
<pre><code class="language-cpp">{$code}</code></pre>
<p><strong>How to test the commands above:</strong> test in <strong>Arduino IDE + Library Manager + Upload + Serial Monitor</strong>, then match the OLED numbers. Not a Laragon / web-server command. If BME fails, change <code>BME_ADDR</code> to <code>0x77</code> and Upload again.</p>
{$success}

<h2 id="fsiot-i2c-checklist">Practice — I2C checklist</h2>
<p>Tick each step after you do it at the desk. Target: <strong>10/10</strong>.</p>
<ul id="fsiot-i2c-checklist-items">
<li>Arduino IDE was open before writing code</li>
<li>GFX + SSD1306 + BME280 libraries installed via Library Manager</li>
<li>Wiring: SDA→21, SCL→22, VCC→3V3, shared GND</li>
<li>I understand: two modules share the bus with different addresses</li>
<li>Sketch saved as FS28_bme280_oled</li>
<li>Upload succeeded — Done uploading</li>
<li>Serial at baud 115200 shows T=… and P=…</li>
<li>OLED shows numbers that match Serial</li>
<li>I understand: BME280 trains I2C; DHT22 stays the main story sensor</li>
<li>I know: BUILDER practice is nearly done — ready for a terms review + your own wiring photo</li>
</ul>
<p><strong>How to test the checklist:</strong> tick in the browser after IDE + board practice. No <code>php artisan</code> needed.</p>

<h2>Common mistakes</h2>
<ul>
<li><strong>SDA/SCL swapped.</strong> SDA only to GPIO 21 · SCL to GPIO 22.</li>
<li><strong>Confused by OLED “SCK”.</strong> On a 0.96" I2C module, <strong>SCK = SCL</strong> (clock) — wire it to GPIO 22.</li>
<li><strong>Wrong BME address (0x76 vs 0x77).</strong> Change the constant and Upload again.</li>
<li><strong>Incomplete libraries.</strong> Install GFX first, then SSD1306, then BME280 (+ Unified Sensor if asked).</li>
<li><strong>5V into a 3V3-only module.</strong> Check silkscreen; many I2C breakouts expect 3V3.</li>
<li><strong>Testing in Laragon / a web terminal.</strong> The sketch only runs on the board via IDE Upload.</li>
<li><strong>Equating Serial Monitor with I2C.</strong> Serial Monitor = UART/debug; I2C = the bus on pins 21/22.</li>
</ul>

<h2>Next</h2>
<p><strong>In short:</strong> if the OLED and Serial show matching temperature/pressure, FS-28 is done — you’ve practiced multi-device I2C.</p>
<p>This closes <strong>BUILDER</strong> practice before the gate to <strong>CONNECTED</strong> (Wi-Fi in FS-29) when that module ships. Take a photo of your own wiring as desk proof.</p>
<p>Full module list: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }
}
