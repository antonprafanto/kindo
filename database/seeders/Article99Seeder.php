<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article99Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first()
            ?? Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $iotCat) {
            throw new \RuntimeException('User atau kategori iot-smart-device/esp32-arduino tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-wifi-dari-nol';

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
                'title'              => 'Wi-Fi dari nol: SSID, sandi, IP, gagal terhubung',
                'title_en'           => 'Wi-Fi from zero: SSID, password, IP, failed connect',
                'excerpt'            => 'FS-29 / #99: ESP32 gabung Wi-Fi rumah 2,4 GHz. Serial Monitor menampilkan IP valid — buka Arduino IDE + Upload dulu, tanpa Library Manager ekstra.',
                'excerpt_en'         => 'FS-29 / #99: ESP32 joins 2.4 GHz home Wi-Fi. Serial Monitor shows a valid IP — open Arduino IDE + Upload first, no extra Library Manager.',
                'body'               => $this->body(),
                'body_en'            => $this->bodyEn(),
                'status'             => 'draft',
                'is_featured'        => false,
                'published_at'       => null,
                'seo_title'          => 'Wi-Fi ESP32 dari nol — Full Stack IoT #99',
                'seo_title_en'       => 'ESP32 Wi-Fi from zero — Full Stack IoT #99',
                'seo_description'    => 'ESP32 mode gabung (station): SSID 2,4 GHz, WiFi.begin, IP di Serial Monitor 115200. Modul FS-29 / #99.',
                'seo_description_en' => 'ESP32 station mode: 2.4 GHz SSID, WiFi.begin, IP on Serial Monitor 115200. Module FS-29 / #99.',
            ]
        );

        if ($article->published_at !== null || $article->status !== 'draft') {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['fullstack-iot', 'iot', 'esp32'])->pluck('id');
        $article->tags()->sync($tagIds);

        $srcWebp = public_path('images/fsiot/fs29-cover-wifi.webp');
        $srcJpg = public_path('images/fsiot/fs29-cover-wifi.jpg');
        if (is_file($srcWebp)) {
            $dest = 'articles/covers/fs29-cover-wifi.webp';
            \Illuminate\Support\Facades\Storage::disk('public')->put($dest, file_get_contents($srcWebp));
            $article->cover_image = $dest;
            $article->save();
        } elseif (is_file($srcJpg)) {
            $dest = 'articles/covers/fs29-cover-wifi.jpg';
            \Illuminate\Support\Facades\Storage::disk('public')->put($dest, file_get_contents($srcJpg));
            $article->cover_image = $dest;
            $article->save();
        }

        $this->command?->info('✓ Artikel #99 / FS-29 tersimpan sebagai DRAFT: '.$article->title);
    }

    private function ideFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem">
  <img src="/images/fsiot/fs11-ide-overview-cite.png" width="1280" height="720" alt="Arduino IDE 2 — Verify, Upload, dan Serial Monitor" loading="eager" style="width:100%;height:auto;max-height:420px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Arduino IDE 2</strong> — tempat menguji sintaks hari ini. Urutan: buka IDE → isi SSID/sandi di sketch → <strong>Verify</strong> → <strong>Upload</strong> → buka <strong>Tools → Serial Monitor</strong> (atau Ctrl+Shift+M) baud <strong>115200</strong>. Board: <strong>ESP32 Dev Module</strong>. <em>Catatan gambar:</em> screenshot Commons bisa menampilkan baud lain — <strong>abaikan</strong>; untuk FS-29 pakai 115200.
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:Ide-2-overview.png" rel="noopener noreferrer" target="_blank">Arduino IDE 2 overview</a> · Wikimedia Commons (CC BY-SA 3.0). Panduan: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-serial-monitor/" rel="noopener noreferrer" target="_blank">Serial Monitor</a>. Wi-Fi ESP32: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/api/wifi.html" rel="noopener noreferrer" target="_blank">Espressif — WiFi API</a>.
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
    <strong>Arduino IDE 2</strong> — where you test today’s syntax. Order: open the IDE → put SSID/password in the sketch → <strong>Verify</strong> → <strong>Upload</strong> → open <strong>Tools → Serial Monitor</strong> (or Ctrl+Shift+M) at baud <strong>115200</strong>. Board: <strong>ESP32 Dev Module</strong>. <em>Image note:</em> the Commons screenshot may show another baud — <strong>ignore it</strong>; for FS-29 use 115200.
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:Ide-2-overview.png" rel="noopener noreferrer" target="_blank">Arduino IDE 2 overview</a> · Wikimedia Commons (CC BY-SA 3.0). Guide: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-serial-monitor/" rel="noopener noreferrer" target="_blank">Serial Monitor</a>. ESP32 Wi-Fi: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/api/wifi.html" rel="noopener noreferrer" target="_blank">Espressif — WiFi API</a>.
  </figcaption>
</figure>
HTML;
    }

    private function toolsFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs29-tools-ide.png" width="1400" height="720" alt="Urutan tools: SSID, IDE, Upload, checklist" loading="eager" style="width:100%;height:auto;max-height:640px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Urutan meja kerja:</strong> siapkan SSID 2,4 GHz → buka Arduino IDE → Upload sketch → centang checklist di browser. Sumber gambar: diagram buatan Koding Indonesia (FS-29).
  </figcaption>
</figure>
HTML;
    }

    private function toolsFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs29-tools-ide.png" width="1400" height="720" alt="Tool order: SSID, IDE, Upload, checklist" loading="eager" style="width:100%;height:auto;max-height:640px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Desk order:</strong> prepare a 2.4 GHz SSID → open Arduino IDE → Upload the sketch → tick the checklist in the browser. Image source: diagram by Koding Indonesia (FS-29).
  </figcaption>
</figure>
HTML;
    }

    private function coreFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs29-wifi-core.png" width="1200" height="560" alt="WiFi.h sudah di core ESP32 — tiga langkah sketch" loading="eager" style="width:100%;height:auto;max-height:480px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Intinya:</strong> <code>#include &lt;WiFi.h&gt;</code> sudah ada di core board ESP32 — <strong>tidak perlu</strong> Library Manager ekstra (beda FS-28 yang butuh Adafruit). Sumber gambar: diagram Koding Indonesia (FS-29). API: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/api/wifi.html" rel="noopener noreferrer" target="_blank">Espressif WiFi</a>.
  </figcaption>
</figure>
HTML;
    }

    private function coreFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs29-wifi-core.png" width="1200" height="560" alt="WiFi.h is in the ESP32 core — three sketch steps" loading="eager" style="width:100%;height:auto;max-height:480px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>In short:</strong> <code>#include &lt;WiFi.h&gt;</code> ships with the ESP32 board core — <strong>no</strong> extra Library Manager step (unlike FS-28’s Adafruit libs). Image source: diagram by Koding Indonesia (FS-29). API: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/api/wifi.html" rel="noopener noreferrer" target="_blank">Espressif WiFi</a>.
  </figcaption>
</figure>
HTML;
    }

    private function mainFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs29-wifi-station.png" width="1200" height="720" alt="Gambar utama — ESP32 gabung Wi-Fi rumah lalu IP di Serial" loading="eager" style="width:100%;height:auto;max-height:640px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Gambar utama — ESP32 gabung Wi-Fi rumah.</strong> Mode <strong>gabung (station)</strong> = ESP32 berperilaku seperti HP yang masuk SSID. Router (2,4 GHz) memberi <strong>nomor IP</strong> (DHCP). Bukti sukses dibaca di <strong>Serial Monitor</strong> baud <strong>115200</strong>.
    <br>Sumber gambar: diagram Koding Indonesia (FS-29). Acuan API: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/api/wifi.html" rel="noopener noreferrer" target="_blank">Espressif — WiFi</a>.
  </figcaption>
</figure>
HTML;
    }

    private function mainFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs29-wifi-station.png" width="1200" height="720" alt="Main figure — ESP32 station to router then IP on Serial" loading="eager" style="width:100%;height:auto;max-height:640px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Main figure — ESP32 joins home Wi-Fi.</strong> <strong>Station</strong> mode = the ESP32 behaves like a phone that “joins” an SSID. The router (2.4 GHz) gives an <strong>IP</strong> via DHCP. Proof of success is read on the <strong>Serial Monitor</strong> at baud <strong>115200</strong>.
    <br>Image source: diagram by Koding Indonesia (FS-29). API: <a href="https://docs.espressif.com/projects/arduino-esp32/en/latest/api/wifi.html" rel="noopener noreferrer" target="_blank">Espressif — WiFi</a>.
  </figcaption>
</figure>
HTML;
    }

    private function schemaFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs29-band-2g4.png" width="1200" height="620" alt="Skema bantu — SSID 2,4 GHz vs hanya 5 GHz" loading="eager" style="width:100%;height:auto;max-height:560px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Skema bantu — pilih jaringan yang ESP32 bisa dengar.</strong> Banyak ESP32 DevKit hanya Wi-Fi <strong>2,4 GHz</strong>. SSID yang <strong>hanya 5 GHz</strong> sering membuat titik-titik gagal di Serial.
    <br>Sumber gambar: diagram Koding Indonesia (FS-29).
  </figcaption>
</figure>
HTML;
    }

    private function schemaFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs29-band-2g4.png" width="1200" height="620" alt="Helper schematic — 2.4 GHz SSID vs 5 GHz only" loading="eager" style="width:100%;height:auto;max-height:560px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Helper schematic — pick a band the ESP32 can hear.</strong> Many ESP32 DevKits only do <strong>2.4 GHz</strong> Wi-Fi. A “5 GHz only” SSID often means endless dots / timeout on Serial.
    <br>Image source: diagram by Koding Indonesia (FS-29).
  </figcaption>
</figure>
HTML;
    }

    private function modulFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs29-modul-router.png" width="1400" height="620" alt="Router rumah dan ESP32 mode gabung" loading="eager" style="width:100%;height:auto;max-height:560px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Kenali dulu.</strong> Router = “pintu” Wi-Fi rumah · ESP32 hari ini hanya <strong>gabung</strong> (mode station), belum jadi server web. Hardware: ESP32 + kabel USB data (bukan kabel charge-only). Tidak wajib breadboard sensor.
    <br>Sumber foto router: <a href="https://commons.wikimedia.org/wiki/File:TP-Link_WR841ND_WiFi_router_transparent.png" rel="noopener noreferrer" target="_blank">TP-Link WR841ND</a> (CC BY 4.0, Florian838). Bentuk router kamu bisa beda — yang penting SSID 2,4 GHz. Kolase: Koding Indonesia (FS-29).
  </figcaption>
</figure>
HTML;
    }

    private function modulFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs29-modul-router.png" width="1400" height="620" alt="Home router and ESP32 station" loading="eager" style="width:100%;height:auto;max-height:560px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Recognize first.</strong> Router = your home Wi-Fi “door” · today the ESP32 only <strong>joins</strong> (station), it is not a web server yet. Hardware: ESP32 + a data USB cable (not charge-only). No sensor breadboard required.
    <br>Router photo: <a href="https://commons.wikimedia.org/wiki/File:TP-Link_WR841ND_WiFi_router_transparent.png" rel="noopener noreferrer" target="_blank">TP-Link WR841ND</a> (CC BY 4.0, Florian838). Your router may look different — what matters is a 2.4 GHz SSID. Collage: Koding Indonesia (FS-29).
  </figcaption>
</figure>
HTML;
    }

    private function successFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs29-success-serial.png" width="1200" height="520" alt="Sukses: Serial tampilkan IP valid" loading="eager" style="width:100%;height:auto;max-height:480px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Intinya:</strong> sukses = Serial menampilkan <strong>IP</strong> (contoh <code>192.168.x.x</code>). Titik terus / timeout = belum gabung. Sumber gambar: diagram Koding Indonesia (FS-29).
  </figcaption>
</figure>
HTML;
    }

    private function successFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.35rem">
  <img src="/images/fsiot/fs29-success-serial.png" width="1200" height="520" alt="Success: Serial shows a valid IP" loading="eager" style="width:100%;height:auto;max-height:480px;object-fit:contain;border-radius:6px;background:#F5F5F0">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>In short:</strong> success = Serial prints an <strong>IP</strong> (e.g. <code>192.168.x.x</code>). Endless dots / timeout = not joined yet. Image source: diagram by Koding Indonesia (FS-29).
  </figcaption>
</figure>
HTML;
    }

    private function analogySvgId(): string
    {
        return <<<'HTML'
<figure role="img" aria-label="Analogi Wi-Fi: ESP32 gabung seperti HP" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 900 200" width="100%" height="auto" style="display:block;max-height:240px">
    <rect width="900" height="200" fill="#F5F5F0"/>
    <text x="450" y="28" text-anchor="middle" dominant-baseline="central" font-family="Segoe UI,sans-serif" font-size="20" font-weight="700" fill="#1a1a1a">Analogi: ESP32 gabung Wi-Fi seperti HP</text>
    <rect x="40" y="55" width="250" height="120" rx="10" fill="#FFF8E1" stroke="#F9A825" stroke-width="2.5"/>
    <text x="165" y="100" text-anchor="middle" dominant-baseline="central" font-family="Segoe UI,sans-serif" font-size="18" font-weight="700" fill="#F57F17">ESP32 = tamu</text>
    <text x="165" y="138" text-anchor="middle" dominant-baseline="central" font-family="Segoe UI,sans-serif" font-size="15" fill="#333">bawa nama + sandi</text>
    <rect x="325" y="55" width="250" height="120" rx="10" fill="#BBDEFB" stroke="#1565C0" stroke-width="2.5"/>
    <text x="450" y="100" text-anchor="middle" dominant-baseline="central" font-family="Segoe UI,sans-serif" font-size="18" font-weight="700" fill="#0D47A1">Router = penjaga</text>
    <text x="450" y="138" text-anchor="middle" dominant-baseline="central" font-family="Segoe UI,sans-serif" font-size="15" fill="#333">cek SSID + sandi</text>
    <rect x="610" y="55" width="250" height="120" rx="10" fill="#C8E6C9" stroke="#2E7D32" stroke-width="2.5"/>
    <text x="735" y="100" text-anchor="middle" dominant-baseline="central" font-family="Segoe UI,sans-serif" font-size="18" font-weight="700" fill="#1B5E20">IP = nomor kursi</text>
    <text x="735" y="138" text-anchor="middle" dominant-baseline="central" font-family="Segoe UI,sans-serif" font-size="15" fill="#333">bukti sudah masuk</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>Intinya:</strong> SSID = nama pesta · sandi = undangan · IP = kursi yang diberikan router. Sumber gambar: diagram Koding Indonesia (FS-29).
  </figcaption>
</figure>
HTML;
    }

    private function analogySvgEn(): string
    {
        return <<<'HTML'
<figure role="img" aria-label="Wi-Fi analogy: ESP32 joins like a phone" style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem 0.75rem 0.25rem">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 900 200" width="100%" height="auto" style="display:block;max-height:240px">
    <rect width="900" height="200" fill="#F5F5F0"/>
    <text x="450" y="28" text-anchor="middle" dominant-baseline="central" font-family="Segoe UI,sans-serif" font-size="20" font-weight="700" fill="#1a1a1a">Analogy: ESP32 joins Wi-Fi like a phone</text>
    <rect x="40" y="55" width="250" height="120" rx="10" fill="#FFF8E1" stroke="#F9A825" stroke-width="2.5"/>
    <text x="165" y="100" text-anchor="middle" dominant-baseline="central" font-family="Segoe UI,sans-serif" font-size="18" font-weight="700" fill="#F57F17">ESP32 = guest</text>
    <text x="165" y="138" text-anchor="middle" dominant-baseline="central" font-family="Segoe UI,sans-serif" font-size="15" fill="#333">brings name + password</text>
    <rect x="325" y="55" width="250" height="120" rx="10" fill="#BBDEFB" stroke="#1565C0" stroke-width="2.5"/>
    <text x="450" y="100" text-anchor="middle" dominant-baseline="central" font-family="Segoe UI,sans-serif" font-size="18" font-weight="700" fill="#0D47A1">Router = doorkeeper</text>
    <text x="450" y="138" text-anchor="middle" dominant-baseline="central" font-family="Segoe UI,sans-serif" font-size="15" fill="#333">checks SSID + password</text>
    <rect x="610" y="55" width="250" height="120" rx="10" fill="#C8E6C9" stroke="#2E7D32" stroke-width="2.5"/>
    <text x="735" y="100" text-anchor="middle" dominant-baseline="central" font-family="Segoe UI,sans-serif" font-size="18" font-weight="700" fill="#1B5E20">IP = seat number</text>
    <text x="735" y="138" text-anchor="middle" dominant-baseline="central" font-family="Segoe UI,sans-serif" font-size="15" fill="#333">proof you got in</text>
  </svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a;">
    <strong>In short:</strong> SSID = party name · password = invitation · IP = the seat the router assigns. Image source: diagram by Koding Indonesia (FS-29).
  </figcaption>
</figure>
HTML;
    }

    private function sketchCode(): string
    {
        return <<<'CODE'
// FS29_wifi_begin — Full Stack IoT FS-29
#include &lt;WiFi.h&gt;

// Ganti dua baris ini dengan Wi-Fi rumahmu (2,4 GHz)
const char* WIFI_SSID = "YOUR_SSID";
const char* WIFI_PASS = "YOUR_PASS";

const uint32_t WIFI_TIMEOUT_MS = 20000;  // sabar 20 detik

void setup() {
  Serial.begin(115200);
  delay(300);
  Serial.println("FS29_wifi_begin ready");

  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASS);
  Serial.print("Menghubungkan ke Wi-Fi");

  uint32_t start = millis();
  while (WiFi.status() != WL_CONNECTED &amp;&amp; (millis() - start) &lt; WIFI_TIMEOUT_MS) {
    delay(400);
    Serial.print(".");
  }
  Serial.println();

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("WL_CONNECTED");
    Serial.print("IP: ");
    Serial.println(WiFi.localIP());
    Serial.print("RSSI: ");
    Serial.print(WiFi.RSSI());
    Serial.println(" dBm");
  } else {
    Serial.println("Gagal connect (timeout).");
    Serial.println("Cek: SSID 2,4 GHz · password · jarak router · USB data.");
  }
}

void loop() {
  // Hari ini cukup setup — loop kosong sengaja.
  delay(1000);
}
CODE;
    }

    private function body(): string
    {
        $ide = $this->ideFigureId();
        $tools = $this->toolsFigureId();
        $core = $this->coreFigureId();
        $main = $this->mainFigureId();
        $schema = $this->schemaFigureId();
        $modul = $this->modulFigureId();
        $success = $this->successFigureId();
        $analogy = $this->analogySvgId();
        $code = $this->sketchCode();

        return <<<HTML
<h2>Pendahuluan — ESP32 masuk jaringan rumah</h2>
<p>Artikel ini adalah <strong>#99 (ini)</strong> · modul <strong>FS-29</strong> di jalur <em>Full Stack IoT Developer — Dari Nol</em> (fase <strong>CONNECTED</strong>). Fase <strong>BUILDER</strong> (sampai FS-28) sudah melatih sensor/aktuator lokal. Hari ini langkah pertama “terhubung”: ESP32 <strong>gabung Wi-Fi</strong> dan mendapat <strong>IP</strong>.</p>
<p><strong>Analogi:</strong> ESP32 = tamu · router = penjaga pintu · SSID = nama pesta · sandi (password) = undangan · IP = nomor kursi.</p>
{$analogy}
<p><strong>Glosarium singkat:</strong></p>
<ul>
<li><strong>SSID</strong> = nama Wi-Fi yang kamu pilih di HP.</li>
<li><strong>Mode gabung (station)</strong> = ESP32 bergabung ke router (bukan jadi hotspot).</li>
<li><strong>IP</strong> = alamat di LAN rumah (contoh <code>192.168.1.42</code>).</li>
<li><strong>RSSI</strong> = perkiraan kekuatan sinyal (angka negatif; mendekati 0 = lebih kuat).</li>
<li><strong>2,4 GHz vs 5 GHz</strong> = banyak ESP32 hanya mendengar <strong>2,4 GHz</strong>.</li>
</ul>
<p><strong>Prasyarat:</strong> gate BUILDER (praktik lokal sampai FS-28) · FS-19 (<code>millis</code> / timeout ramah) · FS-14 (Upload + Serial Monitor).</p>
<p><strong>Cara pakai artikel ini (urutan kerja):</strong></p>
<ol>
<li>Catat SSID Wi-Fi rumah yang <strong>2,4 GHz</strong> (bukan yang hanya 5 GHz).</li>
<li><strong>Buka Arduino IDE dulu</strong> (bukan Laragon / terminal web).</li>
<li>Buat sketch <code>FS29_wifi_begin</code> → isi SSID/sandi → <strong>Verify</strong> → <strong>Upload</strong>.</li>
<li>Buka <strong>Tools → Serial Monitor</strong> (atau Ctrl+Shift+M) baud <strong>115200</strong> — tunggu IP muncul.</li>
<li>Centang checklist 10/10 di browser.</li>
</ol>
<p><strong>Tidak perlu hari ini:</strong> Library Manager ekstra, MQTT, Flask, Laragon, <code>php artisan</code>, breadboard sensor, web server di ESP32. Alat hari ini: <strong>Arduino IDE</strong> + <strong>Upload</strong> + ESP32 + Wi-Fi 2,4 GHz + <strong>Serial Monitor</strong> + <strong>browser</strong>.</p>

<h2>Persiapan — buka &amp; siapkan ini dulu</h2>
<p><strong>Urutan meja kerja:</strong> SSID 2,4 GHz → IDE → Upload → Serial Monitor → checklist.</p>
{$tools}
{$ide}
{$core}
<ul>
<li>Buka <strong>Arduino IDE 2.x</strong> · board <strong>ESP32 Dev Module</strong> + port USB <strong>data</strong>.</li>
<li>Siapkan nama &amp; sandi Wi-Fi <strong>2,4 GHz</strong> (hotspot HP 2,4 juga boleh untuk uji).</li>
<li>Siapkan Serial Monitor: menu <strong>Tools → Serial Monitor</strong>, baud <strong>115200</strong>.</li>
<li>Ingat: <code>WiFi.h</code> sudah di core — tidak perlu pasang library baru.</li>
</ul>
<p><strong>Alat yang dipakai hari ini:</strong> Arduino IDE, Upload, ESP32, Wi-Fi 2,4 GHz, Serial Monitor, browser.</p>
<p><strong>Tidak dipakai hari ini:</strong> Laragon, <code>php artisan</code>, Library Manager ekstra, MQTT, microSD.</p>

<h2>Jaringan rumah (bahasa manusia)</h2>
{$modul}
{$main}
{$schema}
<p><strong>Blok konsep:</strong></p>
<ul>
<li><strong>Router</strong> memancarkan SSID → ESP32 memanggil <code>WiFi.begin(SSID, password)</code>.</li>
<li>Kalau diterima → status <strong>WL_CONNECTED</strong> → router memberi <strong>IP</strong>.</li>
<li>Kalau ditolak / salah frekuensi / timeout → Serial hanya titik atau pesan gagal.</li>
<li><strong>Guest Wi-Fi / AP isolation</strong> bisa membuat HP tidak “melihat” ESP32 di LAN — untuk FS-29 cukup bukti IP di Serial dulu.</li>
</ul>
<p><strong>Intinya:</strong> hari ini sukses = <strong>IP valid di Serial Monitor</strong>, bukan halaman web di HP.</p>

<h2>Praktik — sketch FS29_wifi_begin</h2>
<p>Tujuan: ESP32 bergabung (mode station) ke SSID 2,4 GHz, mencetak IP (+ RSSI) ke Serial Monitor, dengan timeout ramah memakai <code>millis</code>.</p>
<ol>
<li><strong>Buka Arduino IDE</strong> dulu (bukan Laragon).</li>
<li><strong>File → New Sketch</strong> → <strong>Simpan sebagai</strong> <code>FS29_wifi_begin</code>.</li>
<li>Ganti isi dengan kode di bawah — ganti <code>YOUR_SSID</code> / <code>YOUR_PASS</code>.</li>
<li><strong>Verify</strong> → <strong>Upload</strong> → tunggu <em>Done uploading</em>.</li>
<li>Buka <strong>Tools → Serial Monitor</strong> (Ctrl+Shift+M) baud <strong>115200</strong>.</li>
</ol>
<pre><code class="language-cpp">{$code}</code></pre>
<p><strong>Cara menguji perintah di atas:</strong> uji di <strong>Arduino IDE + Upload + Serial Monitor</strong>. Bukan perintah Laragon / browser. Sukses jika muncul baris <code>IP: 192.168....</code>. Jika gagal, ganti ke SSID 2,4 GHz atau hotspot HP lalu Upload lagi.</p>
{$success}

<h2 id="fsiot-wifi-checklist">Praktik — checklist Wi-Fi</h2>
<p>Centang setiap langkah setelah kamu lakukan di meja. Target: <strong>10/10</strong>.</p>
<ul id="fsiot-wifi-checklist-items">
<li>Arduino IDE sudah terbuka sebelum menulis kode</li>
<li>SSID yang dipakai adalah 2,4 GHz (bukan yang hanya 5 GHz)</li>
<li>Paham: WiFi.h di core — tanpa Library Manager ekstra</li>
<li>Sketch disimpan sebagai FS29_wifi_begin</li>
<li>YOUR_SSID / YOUR_PASS sudah diganti (bukan teks contoh)</li>
<li>Upload berhasil — Done uploading</li>
<li>Serial Monitor baud 115200 terbuka (Tools → Serial Monitor)</li>
<li>Muncul WL_CONNECTED atau IP valid</li>
<li>Paham: IP di Serial ≠ localhost di HP</li>
<li>Sadar: ini pintu fase CONNECTED — siap FS-30 (HTTP/JSON) saat terbit</li>
</ul>
<p><strong>Cara menguji checklist:</strong> centang di browser setelah praktik di IDE + board. Tidak perlu <code>php artisan</code>.</p>

<h2>Kesalahan yang sering terjadi</h2>
<ul>
<li><strong>SSID hanya 5 GHz.</strong> Pilih SSID 2,4 GHz atau aktifkan band 2,4 di router.</li>
<li><strong>Sandi salah / spasi tersembunyi.</strong> Salin ulang; hindari spasi di ujung string.</li>
<li><strong>Timeout terlalu singkat.</strong> Kode di atas menunggu sampai 20 detik — jangan panik di detik ke-3.</li>
<li><strong>Kabel USB charge-only.</strong> Upload / Serial butuh kabel data.</li>
<li><strong>Menguji di Laragon / terminal web.</strong> Sketch hanya jalan di board lewat IDE Upload.</li>
<li><strong>Mengira localhost di HP = ESP32.</strong> Hari ini bukti = IP di Serial Monitor.</li>
<li><strong>Guest network isolation.</strong> Untuk FS-29 cukup IP di Serial; saling-lihat antar perangkat dibahas belakangan.</li>
</ul>

<h2>Selanjutnya</h2>
<p><strong>Intinya:</strong> kalau Serial menampilkan IP valid, FS-29 selesai — ESP32 sudah “punya kursi” di Wi-Fi rumah.</p>
<p>Langkah berikutnya di fase <strong>CONNECTED</strong> adalah <strong>FS-30</strong> (HTTP &amp; JSON bahasa manusia) saat modulnya terbit. Soft bridge saja — belum hardlink artikel.</p>
<p>Daftar modul lengkap: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $ide = $this->ideFigureEn();
        $tools = $this->toolsFigureEn();
        $core = $this->coreFigureEn();
        $main = $this->mainFigureEn();
        $schema = $this->schemaFigureEn();
        $modul = $this->modulFigureEn();
        $success = $this->successFigureEn();
        $analogy = $this->analogySvgEn();
        $code = $this->sketchCode();

        return <<<HTML
<h2>Introduction — ESP32 joins the home network</h2>
<p>This is article <strong>#99 (this article)</strong> · module <strong>FS-29</strong> on the <em>Full Stack IoT Developer — From Zero</em> path (<strong>CONNECTED</strong> phase). The <strong>BUILDER</strong> phase (through FS-28) trained local sensors/actuators. Today is the first “connected” step: the ESP32 <strong>joins Wi-Fi</strong> and gets an <strong>IP</strong>.</p>
<p><strong>Analogy:</strong> ESP32 = guest · router = doorkeeper · SSID = party name · password = invitation · IP = seat number.</p>
{$analogy}
<p><strong>Short glossary:</strong></p>
<ul>
<li><strong>SSID</strong> = the Wi-Fi name you pick on your phone.</li>
<li><strong>Station mode</strong> = the ESP32 joins a router (it is not a hotspot).</li>
<li><strong>IP</strong> = address on the home LAN (e.g. <code>192.168.1.42</code>).</li>
<li><strong>RSSI</strong> = rough signal strength (negative numbers; closer to 0 is stronger).</li>
<li><strong>2.4 GHz vs 5 GHz</strong> = many ESP32 boards only hear <strong>2.4 GHz</strong>.</li>
</ul>
<p><strong>Prerequisites:</strong> BUILDER gate (local practice through FS-28) · FS-19 (<code>millis</code> / friendly timeout) · FS-14 (Upload + Serial Monitor).</p>
<p><strong>How to use this article (work order):</strong></p>
<ol>
<li>Note a home Wi-Fi SSID that is <strong>2.4 GHz</strong> (not 5 GHz only).</li>
<li><strong>Open Arduino IDE first</strong> (not Laragon / a web terminal).</li>
<li>Create sketch <code>FS29_wifi_begin</code> → fill SSID/password → <strong>Verify</strong> → <strong>Upload</strong>.</li>
<li>Open <strong>Serial Monitor</strong> at baud <strong>115200</strong> — wait for an IP.</li>
<li>Tick the 10/10 checklist in the browser.</li>
</ol>
<p><strong>Not needed today:</strong> extra Library Manager installs, MQTT, Flask, Laragon, <code>php artisan</code>, sensor breadboards, an ESP32 web server. Today's tools: <strong>Arduino IDE</strong> + <strong>Upload</strong> + ESP32 + 2.4 GHz Wi-Fi + <strong>Serial Monitor</strong> + <strong>browser</strong>.</p>

<h2>Prep — open &amp; set these up first</h2>
<p><strong>Desk order:</strong> 2.4 GHz SSID → IDE → Upload → Serial → checklist.</p>
{$tools}
{$ide}
{$core}
<ul>
<li>Open <strong>Arduino IDE 2.x</strong> · board <strong>ESP32 Dev Module</strong> + a <strong>data</strong> USB port.</li>
<li>Prepare a <strong>2.4 GHz</strong> Wi-Fi name &amp; password (a 2.4 GHz phone hotspot is fine for a test).</li>
<li>Prepare Serial Monitor at baud <strong>115200</strong>.</li>
<li>Remember: <code>WiFi.h</code> is in the core — no new library install.</li>
</ul>
<p><strong>Tools used today:</strong> Arduino IDE, Upload, ESP32, 2.4 GHz Wi-Fi, Serial Monitor, browser.</p>
<p><strong>Not used today:</strong> Laragon, <code>php artisan</code>, extra Library Manager, MQTT, microSD.</p>

<h2>Home network (human language)</h2>
{$modul}
{$main}
{$schema}
<p><strong>Concept block:</strong></p>
<ul>
<li>The <strong>router</strong> broadcasts an SSID → the ESP32 calls <code>WiFi.begin(SSID, password)</code>.</li>
<li>If accepted → status <strong>WL_CONNECTED</strong> → the router assigns an <strong>IP</strong>.</li>
<li>If rejected / wrong band / timeout → Serial only shows dots or a failure line.</li>
<li><strong>Guest Wi-Fi / AP isolation</strong> can hide the ESP32 from your phone on the LAN — for FS-29, Serial IP proof is enough.</li>
</ul>
<p><strong>In short:</strong> success today = a <strong>valid IP on Serial</strong>, not a web page on your phone.</p>

<h2>Practice — sketch FS29_wifi_begin</h2>
<p>Goal: ESP32 station connects to a 2.4 GHz SSID, prints IP (+ RSSI) to Serial, with a friendly <code>millis</code> timeout.</p>
<ol>
<li><strong>Open Arduino IDE</strong> first (not Laragon).</li>
<li><strong>File → New Sketch</strong> → <strong>Save As</strong> <code>FS29_wifi_begin</code>.</li>
<li>Replace the contents with the code below — change <code>YOUR_SSID</code> / <code>YOUR_PASS</code>.</li>
<li><strong>Verify</strong> → <strong>Upload</strong> → wait for <em>Done uploading</em>.</li>
<li>Open Serial Monitor at baud <strong>115200</strong>.</li>
</ol>
<pre><code class="language-cpp">{$code}</code></pre>
<p><strong>How to test the commands above:</strong> test in <strong>Arduino IDE + Upload + Serial Monitor</strong>. Not a Laragon / browser command. Success means a line like <code>IP: 192.168....</code>. If it fails, switch to a 2.4 GHz SSID or phone hotspot and Upload again.</p>
{$success}

<h2 id="fsiot-wifi-checklist">Practice — Wi-Fi checklist</h2>
<p>Tick each step after you do it at the desk. Target: <strong>10/10</strong>.</p>
<ul id="fsiot-wifi-checklist-items">
<li>Arduino IDE was open before writing code</li>
<li>The SSID used is 2.4 GHz (not 5 GHz only)</li>
<li>I understand: WiFi.h is in the core — no extra Library Manager</li>
<li>Sketch saved as FS29_wifi_begin</li>
<li>YOUR_SSID / YOUR_PASS were replaced (not sample text)</li>
<li>Upload succeeded — Done uploading</li>
<li>Serial Monitor at baud 115200 is open</li>
<li>WL_CONNECTED or a valid IP appeared</li>
<li>I understand: Serial IP ≠ localhost on the phone</li>
<li>I know: this opens CONNECTED — ready for FS-30 (HTTP/JSON) when it ships</li>
</ul>
<p><strong>How to test the checklist:</strong> tick in the browser after IDE + board practice. No <code>php artisan</code> needed.</p>

<h2>Common mistakes</h2>
<ul>
<li><strong>5 GHz-only SSID.</strong> Pick a 2.4 GHz SSID or enable the 2.4 GHz band on the router.</li>
<li><strong>Wrong password / hidden spaces.</strong> Re-copy; avoid trailing spaces in the string.</li>
<li><strong>Timeout too short.</strong> The sketch waits up to 20 seconds — don’t panic at second 3.</li>
<li><strong>Charge-only USB cable.</strong> Upload / Serial need a data cable.</li>
<li><strong>Testing in Laragon / a web terminal.</strong> The sketch only runs on the board via IDE Upload.</li>
<li><strong>Thinking phone localhost = the ESP32.</strong> Today’s proof is the IP on Serial Monitor.</li>
<li><strong>Guest network isolation.</strong> For FS-29, Serial IP is enough; device-to-device visibility comes later.</li>
</ul>

<h2>Next</h2>
<p><strong>In short:</strong> if Serial shows a valid IP, FS-29 is done — the ESP32 has a “seat” on home Wi-Fi.</p>
<p>Next in <strong>CONNECTED</strong> is <strong>FS-30</strong> (HTTP &amp; JSON in plain language) when that module ships. Soft bridge only — no hard article link yet.</p>
<p>Full module list: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }
}
