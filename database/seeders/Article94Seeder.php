<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article94Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first()
            ?? Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $iotCat) {
            throw new \RuntimeException('User atau kategori iot-smart-device/esp32-arduino tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-otomasi-lokal-panas-relay';

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
                'title'              => 'Otomasi lokal #1: jika panas → nyalakan “kipas” (relay)',
                'title_en'           => 'Local automation #1: if hot → turn on a “fan” (relay)',
                'excerpt'            => 'FS-24 / #94: otomasi lokal pertama. DHT22 (GPIO 4) + relay (GPIO 26), ambang + histeresis. Uji di Arduino IDE — bukan AC 220V, bukan Wi-Fi.',
                'excerpt_en'         => 'FS-24 / #94: first local automation. DHT22 (GPIO 4) + relay (GPIO 26), threshold + hysteresis. Test in Arduino IDE — no AC 220V, no Wi-Fi.',
                'body'               => $this->body(),
                'body_en'            => $this->bodyEn(),
                'status'             => 'draft',
                'is_featured'        => false,
                'published_at'       => null,
                'seo_title'          => 'Otomasi lokal panas → relay — Full Stack IoT #94',
                'seo_title_en'       => 'Local automation hot → relay — Full Stack IoT #94',
                'seo_description'    => 'Gabungkan DHT22 dan relay: jika panas nyalakan “kipas”. Ambang + histeresis di ESP32. Modul FS-24 jalur Full Stack IoT.',
                'seo_description_en' => 'Combine DHT22 and relay: if hot, turn on a “fan”. Threshold + hysteresis on ESP32. Full Stack IoT FS-24 module.',
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
            $src = public_path('images/fsiot/fs24-cover-otomasi.jpg');
            if (is_file($src)) {
                $dest = 'articles/covers/fs24-cover-otomasi.jpg';
                \Illuminate\Support\Facades\Storage::disk('public')->put($dest, file_get_contents($src));
                $article->cover_image = $dest;
                $article->save();
            }
        }

        $this->command?->info('✓ Artikel #94 / FS-24 tersimpan sebagai DRAFT: '.$article->title);
    }

    private function ideFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem">
  <img src="/images/fsiot/fs11-ide-overview-cite.png" width="1280" height="720" alt="Arduino IDE 2 — Verify, Upload, dan Serial Monitor" loading="eager" style="width:100%;height:auto;max-height:420px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Arduino IDE 2</strong> — tempat menguji otomasi hari ini. Buka IDE dulu → pastikan library <strong>DHT</strong> terpasang di <strong>Library Manager</strong> (sama FS-21) → <strong>Verify</strong> → <strong>Upload</strong> → buka <strong>Serial Monitor</strong> (baud <strong>115200</strong>). Board: <strong>ESP32 Dev Module</strong>. <em>Catatan gambar:</em> screenshot Commons di atas masih menampilkan contoh AnalogReadSerial + baud 9600 — <strong>abaikan</strong> isi sketch itu; untuk FS-24 pakai kode di bawah + baud 115200.
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:Ide-2-overview.png" rel="noopener noreferrer" target="_blank">Arduino IDE 2 overview</a> · Wikimedia Commons (CC BY-SA 3.0). Panduan Serial: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-serial-monitor/" rel="noopener noreferrer" target="_blank">Arduino Docs — Serial Monitor</a>. Fungsi <code>digitalWrite</code>: <a href="https://docs.arduino.cc/language-reference/en/functions/digital-io/digitalwrite/" rel="noopener noreferrer" target="_blank">Arduino Docs — digitalWrite</a>. Library Manager: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-installing-a-library/" rel="noopener noreferrer" target="_blank">Arduino Docs — Installing a library</a>. Library DHT: <a href="https://learn.adafruit.com/dht/using-a-dhtxx-sensor-with-arduino" rel="noopener noreferrer" target="_blank">Adafruit — Using a DHTxx with Arduino</a>.
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
    <strong>Arduino IDE 2</strong> — where today’s automation is tested. Open the IDE first → ensure the <strong>DHT</strong> library is installed via <strong>Library Manager</strong> (same as FS-21) → <strong>Verify</strong> → <strong>Upload</strong> → open <strong>Serial Monitor</strong> (baud <strong>115200</strong>). Board: <strong>ESP32 Dev Module</strong>. <em>Image note:</em> the Commons screenshot still shows AnalogReadSerial + baud 9600 — <strong>ignore</strong> that sample sketch; for FS-24 use the code below + baud 115200.
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:Ide-2-overview.png" rel="noopener noreferrer" target="_blank">Arduino IDE 2 overview</a> · Wikimedia Commons (CC BY-SA 3.0). Serial guide: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-serial-monitor/" rel="noopener noreferrer" target="_blank">Arduino Docs — Serial Monitor</a>. <code>digitalWrite</code>: <a href="https://docs.arduino.cc/language-reference/en/functions/digital-io/digitalwrite/" rel="noopener noreferrer" target="_blank">Arduino Docs — digitalWrite</a>. Library Manager: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-installing-a-library/" rel="noopener noreferrer" target="_blank">Arduino Docs — Installing a library</a>. DHT library: <a href="https://learn.adafruit.com/dht/using-a-dhtxx-sensor-with-arduino" rel="noopener noreferrer" target="_blank">Adafruit — Using a DHTxx with Arduino</a>.
  </figcaption>
</figure>
HTML;
    }

    private function boardFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem">
  <img src="/images/fsiot/esp32-devkitc-overview.jpg" width="1200" height="800" alt="ESP32-DevKitC — USB (6) dan EN (7)" loading="eager" style="width:100%;height:auto;max-height:360px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>ESP32-DevKitC</strong> — USB data di <strong>(6)</strong>, reset di <strong>EN (7)</strong>. Hari ini dua pin: data DHT22 = <strong>GPIO 4</strong> · sinyal relay = <strong>GPIO 26</strong> (tabel global FS-17). Cari juga <strong>3V3</strong>, <strong>5V</strong>, dan <strong>GND</strong>.
    <br>Sumber gambar: <a href="https://docs.espressif.com/projects/esp-dev-kits/en/latest/esp32/esp32-devkitc/user_guide.html" rel="noopener noreferrer" target="_blank">Espressif — ESP32-DevKitC user guide</a>. Pin board: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/boards/ESP32-DevKitC-1.html" rel="noopener noreferrer" target="_blank">ESP32-DevKitC-1</a>.
  </figcaption>
</figure>
HTML;
    }

    private function boardFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem">
  <img src="/images/fsiot/esp32-devkitc-overview.jpg" width="1200" height="800" alt="ESP32-DevKitC — USB (6) and EN (7)" loading="eager" style="width:100%;height:auto;max-height:360px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>ESP32-DevKitC</strong> — USB data at <strong>(6)</strong>, reset on <strong>EN (7)</strong>. Two pins today: DHT22 data = <strong>GPIO 4</strong> · relay signal = <strong>GPIO 26</strong> (FS-17 global table). Also find <strong>3V3</strong>, <strong>5V</strong>, and <strong>GND</strong>.
    <br>Image source: <a href="https://docs.espressif.com/projects/esp-dev-kits/en/latest/esp32/esp32-devkitc/user_guide.html" rel="noopener noreferrer" target="_blank">Espressif — ESP32-DevKitC user guide</a>. Board pins: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/boards/ESP32-DevKitC-1.html" rel="noopener noreferrer" target="_blank">ESP32-DevKitC-1</a>.
  </figcaption>
</figure>
HTML;
    }

    private function kitDhtFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem">
  <img src="/images/fsiot/kit-dht22.jpg" width="900" height="600" alt="Modul sensor DHT22 / AM2302" loading="eager" style="display:block;width:100%;max-width:520px;height:auto;max-height:320px;object-fit:contain;margin:0 auto;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Indra hari ini:</strong> modul <strong>DHT22 / AM2302</strong> (suhu &amp; kelembapan). Pin data → <strong>GPIO 4</strong>, VCC → <strong>3V3</strong>, GND bersama. Baca label silkscreen — urutan kaki bisa beda antar merek.
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:DHT_22_Sensor.jpg" rel="noopener noreferrer" target="_blank">AM2302 DHT22 Sensor</a> · Wikimedia Commons (CC BY-SA 4.0) · L293D. Acuan: <a href="https://www.adafruit.com/product/393" rel="noopener noreferrer" target="_blank">Adafruit AM2302</a> · <a href="https://learn.adafruit.com/dht/connecting-to-a-dhtxx-sensor" rel="noopener noreferrer" target="_blank">Adafruit — Connecting to a DHTxx sensor</a>.
  </figcaption>
</figure>
HTML;
    }

    private function kitDhtFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem">
  <img src="/images/fsiot/kit-dht22.jpg" width="900" height="600" alt="DHT22 / AM2302 sensor module" loading="eager" style="display:block;width:100%;max-width:520px;height:auto;max-height:320px;object-fit:contain;margin:0 auto;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Sense today:</strong> a <strong>DHT22 / AM2302</strong> module (temperature &amp; humidity). Data pin → <strong>GPIO 4</strong>, VCC → <strong>3V3</strong>, shared GND. Read the silkscreen — pin order can differ by brand.
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:DHT_22_Sensor.jpg" rel="noopener noreferrer" target="_blank">AM2302 DHT22 Sensor</a> · Wikimedia Commons (CC BY-SA 4.0) · L293D. Reference: <a href="https://www.adafruit.com/product/393" rel="noopener noreferrer" target="_blank">Adafruit AM2302</a> · <a href="https://learn.adafruit.com/dht/connecting-to-a-dhtxx-sensor" rel="noopener noreferrer" target="_blank">Adafruit — Connecting to a DHTxx sensor</a>.
  </figcaption>
</figure>
HTML;
    }

    private function kitRelayFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem">
  <img src="/images/fsiot/kit-relay-5v.jpg" width="900" height="600" alt="Modul relay 1 channel 5V dengan terminal sekrup" loading="eager" style="display:block;width:100%;max-width:520px;height:auto;max-height:320px;object-fit:contain;margin:0 auto;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Otot hari ini:</strong> modul <strong>relay 1 channel 5V</strong> (kotak biru + 3 pin <strong>S / + / −</strong> — alias IN / VCC / GND — + terminal sekrup NC/COM/NO). Sinyal → <strong>GPIO 26</strong>, VCC → <strong>5V</strong>. “Kipas” = metafora — klik + LED indikator DC cukup; <strong>bukan AC 220V</strong>.
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:SRD-05VDC-SL-C_5V_one-channel_relay_module.jpg" rel="noopener noreferrer" target="_blank">SRD-05VDC-SL-C 5V one-channel relay module</a> · Wikimedia Commons (CC BY-SA 4.0) · Suyash Dwivedi.
  </figcaption>
</figure>
HTML;
    }

    private function kitRelayFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem">
  <img src="/images/fsiot/kit-relay-5v.jpg" width="900" height="600" alt="5V one-channel relay module with screw terminals" loading="eager" style="display:block;width:100%;max-width:520px;height:auto;max-height:320px;object-fit:contain;margin:0 auto;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Actuator today:</strong> a <strong>1-channel 5V relay module</strong> (blue cube + pins <strong>S / + / −</strong> — aliases IN / VCC / GND — + NC/COM/NO screw terminals). Signal → <strong>GPIO 26</strong>, VCC → <strong>5V</strong>. “Fan” is a metaphor — click + DC indicator LED is enough; <strong>not AC mains</strong>.
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:SRD-05VDC-SL-C_5V_one-channel_relay_module.jpg" rel="noopener noreferrer" target="_blank">SRD-05VDC-SL-C 5V one-channel relay module</a> · Wikimedia Commons (CC BY-SA 4.0) · Suyash Dwivedi.
  </figcaption>
</figure>
HTML;
    }

    private function mainWiringFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs24-otomasi-wiring.png" width="1100" height="820" alt="Gambar utama — rangkaian otomasi DHT22 GPIO 4 + relay GPIO 26" loading="eager" style="width:100%;height:auto;max-height:560px;object-fit:contain;border-radius:6px;background:#fff">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Gambar utama — otomasi lokal di breadboard.</strong> DHT22: <strong>3V3</strong> → VCC · <strong>GPIO 4 / IO4</strong> → DATA · <strong>GND</strong> → GND. Relay: <strong>5V</strong> → pin <strong>+</strong> (VCC) · <strong>GPIO 26 / IO26</strong> → pin <strong>S</strong> (IN) · <strong>GND</strong> → pin <strong>−</strong>. Ground ESP32, DHT, dan relay <strong>bersama</strong>. Terminal sekrup NC/COM/NO boleh kosong — yang penting klik + LED <em>ON</em> saat “panas”.
    <br>Sumber gambar: diagram rangkaian buatan Koding Indonesia (FS-24).
  </figcaption>
</figure>
HTML;
    }

    private function mainWiringFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs24-otomasi-wiring.png" width="1100" height="820" alt="Main figure — automation wiring DHT22 GPIO 4 + relay GPIO 26" loading="eager" style="width:100%;height:auto;max-height:560px;object-fit:contain;border-radius:6px;background:#fff">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Main figure — local automation on a breadboard.</strong> DHT22: <strong>3V3</strong> → VCC · <strong>GPIO 4 / IO4</strong> → DATA · <strong>GND</strong> → GND. Relay: <strong>5V</strong> → module <strong>+</strong> (VCC) · <strong>GPIO 26 / IO26</strong> → <strong>S</strong> (IN) · <strong>GND</strong> → <strong>−</strong>. ESP32, DHT, and relay share one <strong>common ground</strong>. NC/COM/NO screw terminals may stay empty — the click + <em>ON</em> LED when “hot” matter most.
    <br>Image source: wiring diagram by Koding Indonesia (FS-24).
  </figcaption>
</figure>
HTML;
    }

    private function helperDhtFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs21-dht22-breadboard.png" width="1238" height="741" alt="Acuan fisik DHT22 (FS-21) — breadboard ke GPIO 4" loading="eager" style="width:100%;height:auto;max-height:480px;object-fit:contain;border-radius:6px;background:#fff">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Acuan fisik DHT22 (FS-21).</strong> Sama seperti latihan sensor sebelumnya: VCC → 3V3 · DATA → GPIO 4 · GND → GND. Pakai ini jika gambar utama terlalu ramai — fokus dulu ke cabang sensor.
    <br>Sumber gambar: diagram rangkaian buatan Koding Indonesia (FS-21).
  </figcaption>
</figure>
HTML;
    }

    private function helperDhtFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs21-dht22-breadboard.png" width="1238" height="741" alt="Physical DHT22 reference (FS-21) — breadboard to GPIO 4" loading="eager" style="width:100%;height:auto;max-height:480px;object-fit:contain;border-radius:6px;background:#fff">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Physical DHT22 reference (FS-21).</strong> Same as the earlier sensor practice: VCC → 3V3 · DATA → GPIO 4 · GND → GND. Use this if the main figure feels crowded — focus on the sensor branch first.
    <br>Image source: wiring diagram by Koding Indonesia (FS-21).
  </figcaption>
</figure>
HTML;
    }

    private function helperRelayFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs23-relay-breadboard.png" width="1236" height="756" alt="Acuan fisik relay (FS-23) — breadboard ke GPIO 26" loading="eager" style="width:100%;height:auto;max-height:480px;object-fit:contain;border-radius:6px;background:#fff">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Acuan fisik relay (FS-23).</strong> 5V → +/VCC · GPIO 26 → S/IN · GND → −. Terminal beban boleh kosong. Ingat: “kipas” hari ini = klik + LED — <strong>bukan colokan AC 220V</strong>.
    <br>Sumber gambar: diagram rangkaian buatan Koding Indonesia (FS-23).
  </figcaption>
</figure>
HTML;
    }

    private function helperRelayFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs23-relay-breadboard.png" width="1236" height="756" alt="Physical relay reference (FS-23) — breadboard to GPIO 26" loading="eager" style="width:100%;height:auto;max-height:480px;object-fit:contain;border-radius:6px;background:#fff">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Physical relay reference (FS-23).</strong> 5V → +/VCC · GPIO 26 → S/IN · GND → −. Load terminals may stay empty. Remember: today’s “fan” = click + LED — <strong>not an AC 220V plug</strong>.
    <br>Image source: wiring diagram by Koding Indonesia (FS-23).
  </figcaption>
</figure>
HTML;
    }

    private function senseSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Alur: DHT22 ke ambang ke relay klik" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 160" width="100%" height="auto" style="display:block;max-height:190px">
    <text x="430" y="28" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#1a1a1a">Alur otomasi: baca → putuskan → gerakkan</text>
    <rect x="30" y="55" width="170" height="70" rx="10" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
    <text x="115" y="85" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" font-weight="700" fill="#0D47A1">DHT22</text>
    <text x="115" y="108" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#333">baca suhu</text>
    <text x="220" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="22" fill="#1a1a1a">→</text>
    <rect x="250" y="55" width="200" height="70" rx="10" fill="#FFF3E0" stroke="#EF6C00" stroke-width="2.5"/>
    <text x="350" y="85" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" font-weight="700" fill="#E65100">Ambang + histeresis</text>
    <text x="350" y="108" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#333">ON ≥ 30 · OFF ≤ 28</text>
    <text x="470" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="22" fill="#1a1a1a">→</text>
    <rect x="500" y="55" width="330" height="70" rx="10" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
    <text x="665" y="85" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" font-weight="700" fill="#1B5E20">Relay “kipas”</text>
    <text x="665" y="108" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#333">klik + LED (bukan AC 220V)</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.35rem;color:#4A5568;">
    <strong>Intinya:</strong> indra (DHT22) + keputusan (ambang) + otot (relay) dalam satu loop lokal — tanpa Wi-Fi. Sumber gambar: diagram buatan Koding Indonesia (FS-24).
  </figcaption>
</figure>
SVG;
    }

    private function senseSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Flow: DHT22 to threshold to relay click" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 160" width="100%" height="auto" style="display:block;max-height:190px">
    <text x="430" y="28" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#1a1a1a">Automation flow: sense → decide → act</text>
    <rect x="30" y="55" width="170" height="70" rx="10" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
    <text x="115" y="85" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" font-weight="700" fill="#0D47A1">DHT22</text>
    <text x="115" y="108" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#333">read temperature</text>
    <text x="220" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="22" fill="#1a1a1a">→</text>
    <rect x="250" y="55" width="200" height="70" rx="10" fill="#FFF3E0" stroke="#EF6C00" stroke-width="2.5"/>
    <text x="350" y="85" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" font-weight="700" fill="#E65100">Threshold + hysteresis</text>
    <text x="350" y="108" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#333">ON ≥ 30 · OFF ≤ 28</text>
    <text x="470" y="95" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="22" fill="#1a1a1a">→</text>
    <rect x="500" y="55" width="330" height="70" rx="10" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
    <text x="665" y="85" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="14" font-weight="700" fill="#1B5E20">Relay “fan”</text>
    <text x="665" y="108" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="12" fill="#333">click + LED (not AC mains)</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.35rem;color:#4A5568;">
    <strong>In short:</strong> sense (DHT22) + decide (threshold) + act (relay) in one local loop — no Wi-Fi. Image source: diagram by Koding Indonesia (FS-24).
  </figcaption>
</figure>
SVG;
    }

    private function hystSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Histeresis: ON pada 30 C OFF pada 28 C versus chattering" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 280" width="100%" height="auto" style="display:block;max-height:320px">
    <text x="430" y="28" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#1a1a1a">Histeresis: dua ambang, bukan satu</text>
    <rect x="40" y="50" width="380" height="160" rx="12" fill="#FFEBEE" stroke="#C62828" stroke-width="2.5"/>
    <text x="230" y="85" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="15" font-weight="700" fill="#B71C1C">Tanpa histeresis (buruk)</text>
    <text x="230" y="115" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#333">Satu ambang 30 °C saja</text>
    <text x="230" y="145" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#555">T=29,9 ↔ 30,1 → klik-klik</text>
    <text x="230" y="175" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#555">relay bergetar (chattering)</text>
    <rect x="440" y="50" width="380" height="160" rx="12" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
    <text x="630" y="85" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="15" font-weight="700" fill="#1B5E20">Dengan histeresis (baik)</text>
    <text x="630" y="115" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#333">ON jika T ≥ 30,0 °C</text>
    <text x="630" y="145" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#333">OFF jika T ≤ 28,0 °C</text>
    <text x="630" y="175" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#555">zona tenang di antaranya</text>
    <text x="430" y="250" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#555">Seperti thermostat AC: nyala di ambang atas, mati di ambang bawah — bukan bolak-balik di satu angka.</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.35rem;color:#4A5568;">
    <strong>Intinya:</strong> <code>AMBANG_ON = 30.0</code> dan <code>AMBANG_OFF = 28.0</code> memberi jarak supaya relay tidak “gemetar” di sekitar 30 °C. Sumber gambar: diagram buatan Koding Indonesia (FS-24).
  </figcaption>
</figure>
SVG;
    }

    private function hystSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Hysteresis: ON at 30 C OFF at 28 C versus chattering" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 280" width="100%" height="auto" style="display:block;max-height:320px">
    <text x="430" y="28" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="16" font-weight="700" fill="#1a1a1a">Hysteresis: two thresholds, not one</text>
    <rect x="40" y="50" width="380" height="160" rx="12" fill="#FFEBEE" stroke="#C62828" stroke-width="2.5"/>
    <text x="230" y="85" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="15" font-weight="700" fill="#B71C1C">Without hysteresis (bad)</text>
    <text x="230" y="115" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#333">A single 30 °C threshold</text>
    <text x="230" y="145" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#555">T=29.9 ↔ 30.1 → click-click</text>
    <text x="230" y="175" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#555">relay chatters</text>
    <rect x="440" y="50" width="380" height="160" rx="12" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
    <text x="630" y="85" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="15" font-weight="700" fill="#1B5E20">With hysteresis (good)</text>
    <text x="630" y="115" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#333">ON when T ≥ 30.0 °C</text>
    <text x="630" y="145" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#333">OFF when T ≤ 28.0 °C</text>
    <text x="630" y="175" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#555">quiet zone in between</text>
    <text x="430" y="250" text-anchor="middle" font-family="Segoe UI,sans-serif" font-size="13" fill="#555">Like an AC thermostat: turn on at the high band, off at the low band — not flipping at one number.</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.35rem;color:#4A5568;">
    <strong>In short:</strong> <code>THRESH_ON = 30.0</code> and <code>THRESH_OFF = 28.0</code> leave a gap so the relay does not “chatter” around 30 °C. Image source: diagram by Koding Indonesia (FS-24).
  </figcaption>
</figure>
SVG;
    }

    private function serialPanelSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Contoh log T dan kipas ON/OFF di Serial Monitor baud 115200" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 340" width="100%" height="auto" role="img" aria-label="Serial Monitor otomasi">
  <text x="430" y="24" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">Serial Monitor (IDE 2) — contoh sukses FS-24</text>
  <rect x="40" y="40" width="780" height="44" rx="8" fill="#2D2D2D" stroke="#1a1a1a" stroke-width="2"/>
  <text x="60" y="68" font-family="system-ui,sans-serif" font-size="12" fill="#B0BEC5">Toolbar IDE 2</text>
  <rect x="520" y="48" width="280" height="28" rx="6" fill="#1565C0"/>
  <text x="660" y="67" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700" fill="#fff">Open Serial Monitor →</text>
  <rect x="40" y="96" width="780" height="210" rx="10" fill="#1E1E1E" stroke="#1a1a1a" stroke-width="2.5"/>
  <rect x="40" y="96" width="780" height="36" rx="10" fill="#2D2D2D"/>
  <rect x="40" y="118" width="780" height="14" fill="#2D2D2D"/>
  <text x="60" y="120" font-family="system-ui,sans-serif" font-size="12" fill="#B0BEC5">Output dari ESP32</text>
  <rect x="560" y="104" width="240" height="26" rx="6" fill="#0D47A1"/>
  <text x="680" y="122" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700" fill="#fff">Baud: 115200</text>
  <text x="70" y="165" font-family="Consolas,monospace" font-size="14" fill="#A5D6A7">FS24_panas_relay siap</text>
  <text x="70" y="195" font-family="Consolas,monospace" font-size="14" fill="#81C784">T=27.4 C | kipas=OFF</text>
  <text x="70" y="225" font-family="Consolas,monospace" font-size="14" fill="#A5D6A7">T=30.6 C | kipas=ON</text>
  <text x="70" y="255" font-family="Consolas,monospace" font-size="14" fill="#A5D6A7">T=29.1 C | kipas=ON</text>
  <text x="70" y="285" font-family="Consolas,monospace" font-size="14" fill="#FFAB91">T=27.8 C | kipas=OFF</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Intinya:</strong> setelah Upload, buka Serial Monitor baud <strong>115200</strong>. Sukses = baris <code>T=…</code> + <code>kipas=ON/OFF</code> selaras klik relay (ON tetap di zona 28–30 sampai turun ≤ 28). Sumber gambar: diagram buatan Koding Indonesia (FS-24) — meniru panel IDE 2 (bukan screenshot baud 9600). Panduan: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-serial-monitor/" rel="noopener noreferrer" target="_blank">Arduino Docs — Serial Monitor</a>.
  </figcaption>
</figure>
SVG;
    }

    private function serialPanelSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Sample T and FAN ON/OFF log in Serial Monitor at 115200 baud" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 340" width="100%" height="auto" role="img" aria-label="Serial Monitor automation">
  <text x="430" y="24" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">Serial Monitor (IDE 2) — FS-24 success sample</text>
  <rect x="40" y="40" width="780" height="44" rx="8" fill="#2D2D2D" stroke="#1a1a1a" stroke-width="2"/>
  <text x="60" y="68" font-family="system-ui,sans-serif" font-size="12" fill="#B0BEC5">IDE 2 toolbar</text>
  <rect x="520" y="48" width="280" height="28" rx="6" fill="#1565C0"/>
  <text x="660" y="67" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700" fill="#fff">Open Serial Monitor →</text>
  <rect x="40" y="96" width="780" height="210" rx="10" fill="#1E1E1E" stroke="#1a1a1a" stroke-width="2.5"/>
  <rect x="40" y="96" width="780" height="36" rx="10" fill="#2D2D2D"/>
  <rect x="40" y="118" width="780" height="14" fill="#2D2D2D"/>
  <text x="60" y="120" font-family="system-ui,sans-serif" font-size="12" fill="#B0BEC5">Output from ESP32</text>
  <rect x="560" y="104" width="240" height="26" rx="6" fill="#0D47A1"/>
  <text x="680" y="122" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700" fill="#fff">Baud: 115200</text>
  <text x="70" y="165" font-family="Consolas,monospace" font-size="14" fill="#A5D6A7">FS24_panas_relay ready</text>
  <text x="70" y="195" font-family="Consolas,monospace" font-size="14" fill="#81C784">T=27.4 C | fan=OFF</text>
  <text x="70" y="225" font-family="Consolas,monospace" font-size="14" fill="#A5D6A7">T=30.6 C | fan=ON</text>
  <text x="70" y="255" font-family="Consolas,monospace" font-size="14" fill="#A5D6A7">T=29.1 C | fan=ON</text>
  <text x="70" y="285" font-family="Consolas,monospace" font-size="14" fill="#FFAB91">T=27.8 C | fan=OFF</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>In short:</strong> after Upload, open Serial Monitor at baud <strong>115200</strong>. Success = <code>T=…</code> lines + <code>fan=ON/OFF</code> matching relay clicks (ON stays through the 28–30 band until T ≤ 28). Image source: diagram by Koding Indonesia (FS-24) — mimics the IDE 2 panel (not a baud-9600 screenshot). Guide: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-serial-monitor/" rel="noopener noreferrer" target="_blank">Arduino Docs — Serial Monitor</a>.
  </figcaption>
</figure>
SVG;
    }

    private function body(): string
    {
        $ide = $this->ideFigureId();
        $board = $this->boardFigureId();
        $kitDht = $this->kitDhtFigureId();
        $kitRelay = $this->kitRelayFigureId();
        $main = $this->mainWiringFigureId();
        $helperDht = $this->helperDhtFigureId();
        $helperRelay = $this->helperRelayFigureId();
        $sense = $this->senseSvgId();
        $hyst = $this->hystSvgId();
        $serial = $this->serialPanelSvgId();

        return <<<HTML
<h2>Pendahuluan — gabung indra + otot</h2>
<p>Artikel ini adalah <strong>#94 (ini)</strong> · modul <strong>FS-24</strong> di jalur <em>Full Stack IoT Developer — Dari Nol</em> (fase <strong>BUILDER</strong>). Di <strong>FS-21</strong> board membaca suhu; di <strong>FS-23</strong> board menggerakkan relay. Hari ini keduanya digabung: jika panas → nyalakan “kipas” (klik relay).</p>
<p><strong>Analogi:</strong> seperti thermostat AC rumah — sensor merasakan udara, otak kecil memutuskan, saklar menggerakkan mesin. Bedanya: “kipas” di sini metafora. Cukup dengar <strong>klik</strong> + lihat LED indikator DC. <strong>Belum AC 220V / PLN.</strong></p>
{$sense}
<p><strong>Prasyarat:</strong> FS-21 (DHT22 ke Serial) · FS-23 (relay klik aman) · FS-14 (kebiasaan Upload + Serial Monitor). Pin mengikuti tabel FS-16 / FS-17.</p>
<p><strong>Cara pakai artikel ini (urutan kerja):</strong></p>
<ol>
<li>Rakit wiring DHT <strong>dan</strong> relay (lihat gambar utama).</li>
<li><strong>Buka Arduino IDE dulu</strong> (bukan Laragon / terminal web).</li>
<li>Pastikan library DHT ada di <strong>Library Manager</strong> (kalau belum, install seperti FS-21).</li>
<li>Buat sketch <code>FS24_panas_relay</code> → salin kode → <strong>Verify</strong> → <strong>Upload</strong>.</li>
<li>Buka <strong>Serial Monitor</strong> baud <strong>115200</strong>.</li>
<li>Hangatkan sensor dengan tangan → lihat <code>kipas=ON</code> + klik; dinginkan → <code>OFF</code>.</li>
<li>Centang checklist 10/10 di browser.</li>
</ol>
<p><strong>Tidak perlu hari ini:</strong> Wi-Fi, MQTT, Laragon, <code>php artisan</code>, beban AC rumah. Tools hari ini: <strong>Arduino IDE</strong> + Library Manager (DHT) + <strong>Upload</strong> + ESP32 + DHT22 + relay 5V + jumper + <strong>Serial Monitor</strong> + <strong>browser</strong>.</p>

<h2>Persiapan — buka &amp; siapkan ini dulu</h2>
<p><strong>Urutan meja kerja:</strong> wiring keduanya → buka IDE → library DHT → Upload → Serial → uji panas/dingin.</p>
<ol>
<li>Buka <strong>Arduino IDE 2.x</strong> · board <strong>ESP32 Dev Module</strong> + port.</li>
<li>Siapkan ESP32 + USB data.</li>
<li>Siapkan modul <strong>DHT22</strong> + modul <strong>relay 1 channel 5V</strong> + jumper.</li>
<li>Cari label <strong>GPIO 4</strong> / <strong>IO4</strong>, <strong>GPIO 26</strong> / <strong>IO26</strong>, <strong>3V3</strong>, <strong>5V</strong>, dan <strong>GND</strong>.</li>
<li>Siapkan Serial Monitor baud <strong>115200</strong>.</li>
</ol>
<p><strong>Alat yang dipakai hari ini:</strong> Arduino IDE, Library Manager (DHT), Upload, ESP32, USB data, DHT22, relay 5V, jumper, Serial Monitor, browser.</p>
<p><strong>Tidak dipakai hari ini:</strong> Laragon, <code>php artisan</code>, Wi-Fi, MQTT, colokan AC 220V.</p>
{$ide}
{$board}
{$kitDht}
{$kitRelay}
{$main}
{$helperDht}
{$helperRelay}

<h2>Wiring (bahasa manusia)</h2>
<p><strong>Blok DHT22 (indra):</strong></p>
<ul>
<li><strong>VCC</strong> / <strong>+</strong> modul → pin <strong>3V3</strong> ESP32</li>
<li><strong>DATA</strong> (pin tengah / label OUT) → <strong>GPIO 4</strong> (sering tertulis <strong>IO4</strong>)</li>
<li><strong>GND</strong> / <strong>−</strong> → <strong>GND</strong> ESP32</li>
</ul>
<p><strong>Blok relay (otot):</strong></p>
<ul>
<li><strong>VCC</strong> / pin <strong>+</strong> modul → pin <strong>5V</strong> ESP32</li>
<li><strong>IN</strong> / pin <strong>S</strong> modul → <strong>GPIO 26</strong> (sering tertulis <strong>IO26</strong>)</li>
<li><strong>GND</strong> / pin <strong>−</strong> → <strong>GND</strong> ESP32 (sama tanah dengan DHT)</li>
</ul>
<p><strong>Kenapa ground bersama?</strong> Sinyal GPIO hanya “ketemu” jika ESP32, sensor, dan modul relay memakai tanah yang sama. Tanpa GND bersama, baca DHT bisa gagal atau relay tidak klik.</p>
<p><strong>Terminal sekrup (COM / NO / NC):</strong> jalur beban — hari ini boleh kosong. “Kipas” = klik + LED indikator. <strong>Jangan</strong> colok AC 220V / PLN.</p>

<h2>Ambang &amp; histeresis</h2>
{$hyst}
<p>Bahasa sederhana: nyalakan “kipas” kalau suhu <strong>≥ 30,0 °C</strong>; matikan lagi baru kalau suhu <strong>≤ 28,0 °C</strong>. Di antara 28 dan 30, status tetap — supaya relay tidak bergetar bolak-balik di sekitar satu angka.</p>
<p>Di kode: <code>AMBANG_ON = 30.0</code> dan <code>AMBANG_OFF = 28.0</code>. Kamu boleh ubah angka itu setelah paham pola histeresis.</p>

<h2>Praktik — sketch FS24_panas_relay</h2>
<p>Tujuan: baca DHT22 tiap ±2,5 detik; kalau panas → relay ON; kalau cukup dingin → OFF; Serial menampilkan <code>T=…</code> dan <code>kipas=ON/OFF</code>. Pola relay default = <strong>aktif LOW</strong> (<code>AKTIF_LOW = true</code>).</p>
<ol>
<li><strong>Buka Arduino IDE</strong> dulu (bukan Laragon).</li>
<li>Pastikan library DHT terpasang (Library Manager → cari “DHT” by Adafruit + Unified Sensor, seperti FS-21).</li>
<li><strong>File → New Sketch</strong> → <strong>Simpan sebagai</strong> <code>FS24_panas_relay</code>.</li>
<li>Ganti isi dengan kode di bawah (salin utuh).</li>
<li><strong>Verify</strong> → <strong>Upload</strong> → tunggu <em>Done uploading</em>.</li>
<li>Buka Serial Monitor baud <strong>115200</strong>.</li>
</ol>
<pre><code class="language-cpp">// FS24_panas_relay — Full Stack IoT FS-24
#include &lt;DHT.h&gt;

const int PIN_DHT = 4;      // FS-21 / tabel FS-17
const int PIN_RELAY = 26;   // FS-23 / tabel FS-17
const bool AKTIF_LOW = true;

const float AMBANG_ON = 30.0;   // nyalakan "kipas"
const float AMBANG_OFF = 28.0;  // matikan (histeresis)

DHT dht(PIN_DHT, DHT22);
bool kipasNyala = false;

void setRelay(bool nyala) {
  if (AKTIF_LOW) digitalWrite(PIN_RELAY, nyala ? LOW : HIGH);
  else digitalWrite(PIN_RELAY, nyala ? HIGH : LOW);
}

void setup() {
  pinMode(PIN_RELAY, OUTPUT);
  setRelay(false);
  Serial.begin(115200);
  dht.begin();
  delay(1500);
  Serial.println("FS24_panas_relay siap");
}

void loop() {
  delay(2500);
  float t = dht.readTemperature();
  if (isnan(t)) {
    Serial.println("Gagal baca DHT22");
    return;
  }

  if (!kipasNyala &amp;&amp; t &gt;= AMBANG_ON) {
    kipasNyala = true;
    setRelay(true);
  } else if (kipasNyala &amp;&amp; t &lt;= AMBANG_OFF) {
    kipasNyala = false;
    setRelay(false);
  }

  Serial.print("T=");
  Serial.print(t, 1);
  Serial.print(" C | kipas=");
  Serial.println(kipasNyala ? "ON" : "OFF");
}
</code></pre>
<p><strong>Cara menguji perintah di atas:</strong> uji di <strong>Arduino IDE + Upload + Serial Monitor</strong>. Hangatkan DHT dengan tangan (atau napas hangat) sampai <code>T</code> melewati 30 → dengar klik + <code>kipas=ON</code>. Lepas / dinginkan sampai ≤ 28 → klik lagi + <code>OFF</code>. Bukan perintah Laragon / web server.</p>
<p><strong>Tip AKTIF_LOW:</strong> kalau Serial bilang ON tapi LED indikator mati (atau sebaliknya), ubah <code>AKTIF_LOW</code> menjadi <code>false</code>. Jangan buru-buru cabut kabel 5V/GND.</p>
{$serial}

<h2 id="fsiot-auto-checklist">Praktik — checklist otomasi lokal</h2>
<p>Centang setiap langkah setelah kamu lakukan di meja. Target: <strong>10/10</strong>.</p>
<ul id="fsiot-auto-checklist-items">
<li>Arduino IDE sudah terbuka sebelum menulis kode</li>
<li>Library DHT (Adafruit) sudah terpasang di Library Manager</li>
<li>Wiring DHT: 3V3–VCC, GPIO 4–DATA, GND bersama</li>
<li>Wiring relay: 5V–VCC/+, GPIO 26–IN/S, GND bersama</li>
<li>Paham: hari ini belum boleh AC 220V / PLN (“kipas” = metafora)</li>
<li>Paham: histeresis ON ≥ 30 °C dan OFF ≤ 28 °C</li>
<li>Sketch disimpan sebagai FS24_panas_relay</li>
<li>Upload berhasil — Done uploading</li>
<li>Serial menampilkan T=… dan kipas=ON/OFF; klik selaras saat panas/dingin</li>
<li>Sadar: ini fondasi otomasi lokal sebelum sensor gerak FS-25</li>
</ul>
<p><strong>Cara menguji checklist:</strong> centang di browser setelah praktik di IDE + board. Tidak perlu <code>php artisan</code>.</p>

<h2>Kesalahan yang sering terjadi</h2>
<ul>
<li><strong>Langsung colok AC 220V.</strong> Di jalur ini: <strong>dilarang</strong>. “Kipas” = klik + LED DC saja.</li>
<li><strong>Satu ambang tanpa histeresis.</strong> Relay bergetar di sekitar 30 °C — pakai ON 30 / OFF 28.</li>
<li><strong>Lupa install library DHT.</strong> Verify gagal di <code>#include &lt;DHT.h&gt;</code> — buka Library Manager dulu.</li>
<li><strong>Pin tertukar (GPIO 4 vs 26).</strong> DHT di 4, relay di 26 — cocokkan silkscreen IO4 / IO26.</li>
<li><strong>VCC DHT ke 5V pada clone 3V3-only.</strong> Banyak modul kit aman di 3V3; jangan paksa 5V jika silkscreen bilang 3,3 V.</li>
<li><strong>Ground tidak bersama.</strong> ESP32 + DHT + relay harus satu GND.</li>
<li><strong>Menguji di terminal web.</strong> Sketch hanya jalan di board lewat IDE Upload — bukan Laragon.</li>
</ul>

<h2>Selanjutnya</h2>
<p><strong>Intinya:</strong> kalau Serial menunjukkan suhu + <code>kipas=ON/OFF</code> selaras klik tanpa menyentuh AC PLN, FS-24 selesai — indra + keputusan + otot sudah menyatu di board.</p>
<p>Lanjut ke <strong>FS-25</strong> (sensor gerak / PIR sebagai pemicu otomasi berikutnya) saat modulnya terbit.</p>
<p>Daftar modul lengkap: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $ide = $this->ideFigureEn();
        $board = $this->boardFigureEn();
        $kitDht = $this->kitDhtFigureEn();
        $kitRelay = $this->kitRelayFigureEn();
        $main = $this->mainWiringFigureEn();
        $helperDht = $this->helperDhtFigureEn();
        $helperRelay = $this->helperRelayFigureEn();
        $sense = $this->senseSvgEn();
        $hyst = $this->hystSvgEn();
        $serial = $this->serialPanelSvgEn();

        return <<<HTML
<h2>Introduction — combine sense + muscle</h2>
<p>This is article <strong>#94 (this article)</strong> · module <strong>FS-24</strong> on the <em>Full Stack IoT Developer — From Zero</em> path (<strong>BUILDER</strong> phase). In <strong>FS-21</strong> the board read temperature; in <strong>FS-23</strong> it drove a relay. Today both join: if hot → turn on a “fan” (relay click).</p>
<p><strong>Analogy:</strong> like a home AC thermostat — a sensor feels the air, a small brain decides, a switch moves the machine. Difference: “fan” here is a metaphor. Hearing the <strong>click</strong> + seeing a DC indicator LED is enough. <strong>No AC mains / 220V yet.</strong></p>
{$sense}
<p><strong>Prerequisites:</strong> FS-21 (DHT22 to Serial) · FS-23 (safe relay click) · FS-14 (Upload + Serial Monitor habits). Pins follow the FS-16 / FS-17 table.</p>
<p><strong>How to use this article (work order):</strong></p>
<ol>
<li>Wire both the DHT <strong>and</strong> the relay (see the main figure).</li>
<li><strong>Open Arduino IDE first</strong> (not Laragon / a web terminal).</li>
<li>Ensure the DHT library is in <strong>Library Manager</strong> (install as in FS-21 if needed).</li>
<li>Create sketch <code>FS24_panas_relay</code> → paste the code → <strong>Verify</strong> → <strong>Upload</strong>.</li>
<li>Open <strong>Serial Monitor</strong> at baud <strong>115200</strong>.</li>
<li>Warm the sensor with your hand → see <code>fan=ON</code> + click; cool it → <code>OFF</code>.</li>
<li>Tick the 10/10 checklist in the browser.</li>
</ol>
<p><strong>Not needed today:</strong> Wi-Fi, MQTT, Laragon, <code>php artisan</code>, household AC loads. Today's tools: <strong>Arduino IDE</strong> + Library Manager (DHT) + <strong>Upload</strong> + ESP32 + DHT22 + 5V relay + jumpers + <strong>Serial Monitor</strong> + <strong>browser</strong>.</p>

<h2>Preparation — open &amp; gather these first</h2>
<p><strong>Desk order:</strong> wire both → open IDE → DHT library → Upload → Serial → hot/cold test.</p>
<ol>
<li>Open <strong>Arduino IDE 2.x</strong> · <strong>ESP32 Dev Module</strong> board + port.</li>
<li>Prepare the ESP32 + USB data cable.</li>
<li>Prepare a <strong>DHT22</strong> module + a <strong>1-channel 5V relay</strong> + jumpers.</li>
<li>Find <strong>GPIO 4</strong> / <strong>IO4</strong>, <strong>GPIO 26</strong> / <strong>IO26</strong>, <strong>3V3</strong>, <strong>5V</strong>, and <strong>GND</strong>.</li>
<li>Have Serial Monitor ready at baud <strong>115200</strong>.</li>
</ol>
<p><strong>Tools used today:</strong> Arduino IDE, Library Manager (DHT), Upload, ESP32, USB data, DHT22, 5V relay, jumpers, Serial Monitor, browser.</p>
<p><strong>Not used today:</strong> Laragon, <code>php artisan</code>, Wi-Fi, MQTT, AC 220V plugs.</p>
{$ide}
{$board}
{$kitDht}
{$kitRelay}
{$main}
{$helperDht}
{$helperRelay}

<h2>Wiring (human language)</h2>
<p><strong>DHT22 block (sense):</strong></p>
<ul>
<li>Module <strong>VCC</strong> / <strong>+</strong> → ESP32 <strong>3V3</strong></li>
<li><strong>DATA</strong> (middle pin / OUT label) → <strong>GPIO 4</strong> (often labeled <strong>IO4</strong>)</li>
<li><strong>GND</strong> / <strong>−</strong> → ESP32 <strong>GND</strong></li>
</ul>
<p><strong>Relay block (actuator):</strong></p>
<ul>
<li>Module <strong>VCC</strong> / <strong>+</strong> → ESP32 <strong>5V</strong></li>
<li>Module <strong>IN</strong> / <strong>S</strong> → <strong>GPIO 26</strong> (often labeled <strong>IO26</strong>)</li>
<li>Module <strong>GND</strong> / <strong>−</strong> → ESP32 <strong>GND</strong> (same ground as the DHT)</li>
</ul>
<p><strong>Why a shared ground?</strong> GPIO signals only “meet” when the ESP32, sensor, and relay module share one return path. Without shared GND, DHT reads can fail or the relay won’t click.</p>
<p><strong>Screw terminals (COM / NO / NC):</strong> the load path — may stay empty today. “Fan” = click + indicator LED. <strong>Do not</strong> plug in AC mains / 220V.</p>

<h2>Threshold &amp; hysteresis</h2>
{$hyst}
<p>In plain language: turn the “fan” on when temperature is <strong>≥ 30.0 °C</strong>; turn it off only when temperature is <strong>≤ 28.0 °C</strong>. Between 28 and 30, the state stays put — so the relay does not chatter around a single number.</p>
<p>In code: <code>THRESH_ON = 30.0</code> and <code>THRESH_OFF = 28.0</code>. You may change those numbers after you understand the hysteresis pattern.</p>

<h2>Practice — sketch FS24_panas_relay</h2>
<p>Goal: read the DHT22 about every 2.5 seconds; if hot → relay ON; if cool enough → OFF; Serial shows <code>T=…</code> and <code>fan=ON/OFF</code>. Default relay pattern = <strong>active LOW</strong> (<code>ACTIVE_LOW = true</code>).</p>
<ol>
<li><strong>Open Arduino IDE</strong> first (not Laragon).</li>
<li>Ensure the DHT library is installed (Library Manager → search “DHT” by Adafruit + Unified Sensor, as in FS-21).</li>
<li><strong>File → New Sketch</strong> → <strong>Save As</strong> <code>FS24_panas_relay</code>.</li>
<li>Replace the contents with the code below (copy whole).</li>
<li><strong>Verify</strong> → <strong>Upload</strong> → wait for <em>Done uploading</em>.</li>
<li>Open Serial Monitor at baud <strong>115200</strong>.</li>
</ol>
<pre><code class="language-cpp">// FS24_panas_relay — Full Stack IoT FS-24
#include &lt;DHT.h&gt;

const int PIN_DHT = 4;      // FS-21 / FS-17 table
const int PIN_RELAY = 26;   // FS-23 / FS-17 table
const bool ACTIVE_LOW = true;

const float THRESH_ON = 30.0;   // turn "fan" on
const float THRESH_OFF = 28.0;  // turn off (hysteresis)

DHT dht(PIN_DHT, DHT22);
bool fanOn = false;

void setRelay(bool on) {
  if (ACTIVE_LOW) digitalWrite(PIN_RELAY, on ? LOW : HIGH);
  else digitalWrite(PIN_RELAY, on ? HIGH : LOW);
}

void setup() {
  pinMode(PIN_RELAY, OUTPUT);
  setRelay(false);
  Serial.begin(115200);
  dht.begin();
  delay(1500);
  Serial.println("FS24_panas_relay ready");
}

void loop() {
  delay(2500);
  float t = dht.readTemperature();
  if (isnan(t)) {
    Serial.println("DHT22 read failed");
    return;
  }

  if (!fanOn &amp;&amp; t &gt;= THRESH_ON) {
    fanOn = true;
    setRelay(true);
  } else if (fanOn &amp;&amp; t &lt;= THRESH_OFF) {
    fanOn = false;
    setRelay(false);
  }

  Serial.print("T=");
  Serial.print(t, 1);
  Serial.print(" C | fan=");
  Serial.println(fanOn ? "ON" : "OFF");
}
</code></pre>
<p><strong>How to test the commands above:</strong> test in <strong>Arduino IDE + Upload + Serial Monitor</strong>. Warm the DHT with your hand (or warm breath) until <code>T</code> crosses 30 → hear a click + <code>fan=ON</code>. Release / cool until ≤ 28 → another click + <code>OFF</code>. Not a Laragon / web-server command.</p>
<p><strong>ACTIVE_LOW tip:</strong> if Serial says ON but the indicator LED is off (or the reverse), set <code>ACTIVE_LOW</code> to <code>false</code>. Don’t rush to unplug 5V/GND.</p>
{$serial}

<h2 id="fsiot-auto-checklist">Practice — local-automation checklist</h2>
<p>Tick each step after you do it at the desk. Target: <strong>10/10</strong>.</p>
<ul id="fsiot-auto-checklist-items">
<li>Arduino IDE is open before writing code</li>
<li>DHT library (Adafruit) is installed via Library Manager</li>
<li>DHT wiring: 3V3–VCC, GPIO 4–DATA, shared GND</li>
<li>Relay wiring: 5V–VCC/+, GPIO 26–IN/S, shared GND</li>
<li>I understand: no AC mains / 220V today (“fan” is a metaphor)</li>
<li>I understand: hysteresis ON ≥ 30 °C and OFF ≤ 28 °C</li>
<li>Sketch saved as FS24_panas_relay</li>
<li>Upload succeeded — Done uploading</li>
<li>Serial shows T=… and fan=ON/OFF; clicks match hot/cold</li>
<li>I know: this is the local-automation foundation before FS-25 motion sensing</li>
</ul>
<p><strong>How to test the checklist:</strong> tick in the browser after practice on the IDE + board. No <code>php artisan</code> needed.</p>

<h2>Common mistakes</h2>
<ul>
<li><strong>Plugging in AC 220V immediately.</strong> On this path: <strong>forbidden</strong>. “Fan” = click + DC LED only.</li>
<li><strong>A single threshold without hysteresis.</strong> The relay chatters around 30 °C — use ON 30 / OFF 28.</li>
<li><strong>Forgetting the DHT library.</strong> Verify fails on <code>#include &lt;DHT.h&gt;</code> — open Library Manager first.</li>
<li><strong>Swapped pins (GPIO 4 vs 26).</strong> DHT on 4, relay on 26 — match silkscreen IO4 / IO26.</li>
<li><strong>DHT VCC on 5V on a 3V3-only clone.</strong> Many kit modules are fine on 3V3; don’t force 5V if the silkscreen says 3.3 V.</li>
<li><strong>Missing shared ground.</strong> ESP32 + DHT + relay must share one GND.</li>
<li><strong>Testing in a web terminal.</strong> The sketch runs on the board via IDE Upload only — not Laragon.</li>
</ul>

<h2>Next</h2>
<p><strong>In short:</strong> if Serial shows temperature + <code>fan=ON/OFF</code> in sync with clicks without touching AC mains, FS-24 is done — sense + decide + act are united on the board.</p>
<p>Continue to <strong>FS-25</strong> (motion / PIR as the next automation trigger) when that module publishes.</p>
<p>Full module list: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }
}
