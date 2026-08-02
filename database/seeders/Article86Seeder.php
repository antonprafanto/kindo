<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article86Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first()
            ?? Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $iotCat) {
            throw new \RuntimeException('User atau kategori iot-smart-device/esp32-arduino tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-fungsi';

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
                'title'              => 'Fungsi — pecah program biar rapi',
                'title_en'           => 'Functions — split the program so it stays tidy',
                'excerpt'            => 'FS-16 / #86: fungsi, parameter, return. Uji di Arduino IDE + Serial Monitor: cetakStatus(suhu) di loop.',
                'excerpt_en'         => 'FS-16 / #86: functions, parameters, return. Test in Arduino IDE + Serial Monitor: printStatus(temp) in loop.',
                'body'               => $this->body(),
                'body_en'            => $this->bodyEn(),
                'status'             => 'draft',
                'is_featured'        => false,
                'published_at'       => null,
                'seo_title'          => 'Fungsi pecah program — Full Stack IoT #86',
                'seo_title_en'       => 'Functions split the program — Full Stack IoT #86',
                'seo_description'    => 'Belajar fungsi Arduino: parameter, return, dan cetakStatus. Modul FS-16 jalur Full Stack IoT.',
                'seo_description_en' => 'Learn Arduino functions: parameters, return, and printStatus. Full Stack IoT FS-16 module.',
            ]
        );

        if ($article->published_at !== null || $article->status !== 'draft') {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['fullstack-iot', 'iot', 'esp32'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel #86 / FS-16 tersimpan sebagai DRAFT: '.$article->title);
    }

    private function ideFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/fs11-ide-overview-cite.png" width="1280" height="720" alt="Arduino IDE 2 — toolbar Verify, Upload, dan ikon Serial Monitor di kanan atas" loading="eager" style="width:100%;height:auto;max-height:420px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Arduino IDE 2</strong> — tempat menguji sintaks hari ini. Kiri atas: <strong>Verify</strong> (✓) lalu <strong>Upload</strong> (→). Kanan atas: buka <strong>Serial Monitor</strong> untuk membaca <code>status: …</code>. Abaikan board lain di screenshot — kita pakai <strong>ESP32 Dev Module</strong>.
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:Ide-2-overview.png" rel="noopener noreferrer" target="_blank">Arduino IDE 2 overview</a> · Wikimedia Commons (CC BY-SA 3.0) · asal dokumen Arduino. Panduan Serial: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-serial-monitor/" rel="noopener noreferrer" target="_blank">Arduino Docs — Serial Monitor (IDE 2)</a>.
  </figcaption>
</figure>
HTML;
    }

    private function ideFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/fs11-ide-overview-cite.png" width="1280" height="720" alt="Arduino IDE 2 — Verify, Upload toolbar and Serial Monitor icon top-right" loading="eager" style="width:100%;height:auto;max-height:420px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Arduino IDE 2</strong> — where today’s syntax is tested. Top-left: <strong>Verify</strong> (✓) then <strong>Upload</strong> (→). Top-right: open <strong>Serial Monitor</strong> to read <code>status: …</code>. Ignore other boards in the screenshot — we use <strong>ESP32 Dev Module</strong>.
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:Ide-2-overview.png" rel="noopener noreferrer" target="_blank">Arduino IDE 2 overview</a> · Wikimedia Commons (CC BY-SA 3.0) · from Arduino docs. Serial guide: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-serial-monitor/" rel="noopener noreferrer" target="_blank">Arduino Docs — Serial Monitor (IDE 2)</a>.
  </figcaption>
</figure>
HTML;
    }

    private function boardFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/esp32-devkitc-overview.jpg" width="1200" height="800" alt="ESP32-DevKitC — board untuk melihat status suhu di Serial Monitor" loading="eager" style="width:100%;height:auto;max-height:360px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>ESP32-DevKitC</strong> — colok <strong>kabel USB data</strong> di label <strong>(6)</strong>. Tombol <strong>EN (7)</strong> = reset. Hari ini kita merapikan kode dengan fungsi, bukan menambah sensor.
    <br>Sumber gambar: <a href="https://docs.espressif.com/projects/esp-dev-kits/en/latest/esp32/esp32-devkitc/user_guide.html" rel="noopener noreferrer" target="_blank">Espressif — ESP32-DevKitC user guide</a>.
  </figcaption>
</figure>
HTML;
    }

    private function boardFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/esp32-devkitc-overview.jpg" width="1200" height="800" alt="ESP32-DevKitC — board for watching status lines in Serial Monitor" loading="eager" style="width:100%;height:auto;max-height:360px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>ESP32-DevKitC</strong> — plug a <strong>USB data cable</strong> at label <strong>(6)</strong>. <strong>EN (7)</strong> resets the board. Today we tidy code with functions; no new sensors.
    <br>Image source: <a href="https://docs.espressif.com/projects/esp-dev-kits/en/latest/esp32/esp32-devkitc/user_guide.html" rel="noopener noreferrer" target="_blank">Espressif — ESP32-DevKitC user guide</a>.
  </figcaption>
</figure>
HTML;
    }

    private function analogySvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="fungsi seperti resep bernama" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 185" width="100%" height="auto" role="img" aria-label="function analogy">
  <text x="430" y="22" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">Fungsi = resep bernama yang bisa dipanggil ulang</text>
  <rect x="30" y="40" width="230" height="100" rx="10" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
  <text x="145" y="70" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700" fill="#0D47A1">1. Definisi: cetakStatus</text>
  <text x="145" y="98" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#1565C0">bahan: angka suhu</text>
  <text x="145" y="122" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#1565C0">hasil: teks di Serial</text>
  <text x="290" y="95" text-anchor="middle" font-family="system-ui,sans-serif" font-size="22" font-weight="700" fill="#1a1a1a">→</text>
  <rect x="320" y="40" width="220" height="100" rx="10" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="430" y="78" text-anchor="middle" font-family="Consolas,monospace" font-size="13" fill="#1B5E20">cetakStatus(18)</text>
  <text x="430" y="110" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#2E7D32">2. panggil sekali</text>
  <text x="570" y="95" text-anchor="middle" font-family="system-ui,sans-serif" font-size="22" font-weight="700" fill="#1a1a1a">→</text>
  <rect x="600" y="40" width="230" height="100" rx="10" fill="#FFF8E1" stroke="#F9A825" stroke-width="2.5"/>
  <text x="715" y="78" text-anchor="middle" font-family="Consolas,monospace" font-size="13" fill="#F57F17">cetakStatus(32)</text>
  <text x="715" y="110" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#F9A825">3. panggil lagi</text>
  <text x="430" y="168" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#546E7A">Alur: tulis sekali → panggil → panggil lagi (tanpa salin-tempel ulang)</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Intinya:</strong> tulis sekali, panggil berkali-kali. Referensi: <a href="https://docs.arduino.cc/learn/programming/functions/" rel="noopener noreferrer" target="_blank">Arduino Docs — Functions</a>.
    <br>Sumber gambar: diagram buatan Koding Indonesia (FS-16).
  </figcaption>
</figure>
SVG;
    }

    private function analogySvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="a function is a named recipe" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 185" width="100%" height="auto" role="img" aria-label="function analogy">
  <text x="430" y="22" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">A function = a named recipe you can call again</text>
  <rect x="30" y="40" width="230" height="100" rx="10" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
  <text x="145" y="70" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700" fill="#0D47A1">1. Define: printStatus</text>
  <text x="145" y="98" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#1565C0">ingredient: a number</text>
  <text x="145" y="122" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#1565C0">result: Serial text</text>
  <text x="290" y="95" text-anchor="middle" font-family="system-ui,sans-serif" font-size="22" font-weight="700" fill="#1a1a1a">→</text>
  <rect x="320" y="40" width="220" height="100" rx="10" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="430" y="78" text-anchor="middle" font-family="Consolas,monospace" font-size="13" fill="#1B5E20">printStatus(18)</text>
  <text x="430" y="110" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#2E7D32">2. call once</text>
  <text x="570" y="95" text-anchor="middle" font-family="system-ui,sans-serif" font-size="22" font-weight="700" fill="#1a1a1a">→</text>
  <rect x="600" y="40" width="230" height="100" rx="10" fill="#FFF8E1" stroke="#F9A825" stroke-width="2.5"/>
  <text x="715" y="78" text-anchor="middle" font-family="Consolas,monospace" font-size="13" fill="#F57F17">printStatus(32)</text>
  <text x="715" y="110" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#F9A825">3. call again</text>
  <text x="430" y="168" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#546E7A">Flow: write once → call → call again (no copy-paste)</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>In short:</strong> write once, call many times. Reference: <a href="https://docs.arduino.cc/learn/programming/functions/" rel="noopener noreferrer" target="_blank">Arduino Docs — Functions</a>.
    <br>Image source: diagram by Koding Indonesia (FS-16).
  </figcaption>
</figure>
SVG;
    }

    private function partsSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="bagian fungsi nama parameter return" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 195" width="100%" height="auto" role="img" aria-label="function parts">
  <text x="430" y="22" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">Tiga bagian yang sering dipakai hari ini</text>
  <rect x="30" y="40" width="250" height="105" rx="10" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
  <text x="155" y="72" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#0D47A1">1. Nama</text>
  <text x="155" y="100" text-anchor="middle" font-family="Consolas,monospace" font-size="13" fill="#1565C0">cetakStatus</text>
  <text x="155" y="126" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#1565C0">supaya mudah dipanggil</text>
  <rect x="305" y="40" width="250" height="105" rx="10" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="430" y="72" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#1B5E20">2. Parameter</text>
  <text x="430" y="100" text-anchor="middle" font-family="Consolas,monospace" font-size="13" fill="#2E7D32">int suhu</text>
  <text x="430" y="126" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#2E7D32">data yang dikirim masuk</text>
  <rect x="580" y="40" width="250" height="105" rx="10" fill="#F3E5F5" stroke="#7B1FA2" stroke-width="2.5"/>
  <text x="705" y="72" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#6A1B9A">3. return</text>
  <text x="705" y="100" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#7B1FA2">opsional hari ini</text>
  <text x="705" y="126" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#7B1FA2">boleh void (tanpa nilai)</text>
  <text x="430" y="175" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#546E7A">void = fungsi bekerja (cetak) tanpa mengembalikan angka</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Tips:</strong> mulai dari <code>void nama(...)</code>. Nanti baru sering pakai <code>return</code> untuk mengembalikan nilai.
    <br>Sumber gambar: diagram buatan Koding Indonesia (FS-16).
  </figcaption>
</figure>
SVG;
    }

    private function partsSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="function name parameter return" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 195" width="100%" height="auto" role="img" aria-label="function parts">
  <text x="430" y="22" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">Three parts we use today</text>
  <rect x="30" y="40" width="250" height="105" rx="10" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
  <text x="155" y="72" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#0D47A1">1. Name</text>
  <text x="155" y="100" text-anchor="middle" font-family="Consolas,monospace" font-size="13" fill="#1565C0">printStatus</text>
  <text x="155" y="126" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#1565C0">easy to call later</text>
  <rect x="305" y="40" width="250" height="105" rx="10" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="430" y="72" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#1B5E20">2. Parameter</text>
  <text x="430" y="100" text-anchor="middle" font-family="Consolas,monospace" font-size="13" fill="#2E7D32">int temp</text>
  <text x="430" y="126" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#2E7D32">data you pass in</text>
  <rect x="580" y="40" width="250" height="105" rx="10" fill="#F3E5F5" stroke="#7B1FA2" stroke-width="2.5"/>
  <text x="705" y="72" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#6A1B9A">3. return</text>
  <text x="705" y="100" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#7B1FA2">optional today</text>
  <text x="705" y="126" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#7B1FA2">void = no value back</text>
  <text x="430" y="175" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#546E7A">void = the function works (prints) without returning a number</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Tip:</strong> start with <code>void name(...)</code>. Later you will use <code>return</code> more often to send a value back.
    <br>Image source: diagram by Koding Indonesia (FS-16).
  </figcaption>
</figure>
SVG;
    }

    private function placeSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="letak fungsi di sketch Arduino" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 220" width="100%" height="auto" role="img" aria-label="where to put functions">
  <text x="430" y="22" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">Di mana menulis fungsi? (penting)</text>
  <rect x="40" y="40" width="360" height="140" rx="10" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="220" y="72" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#1B5E20">BENAR</text>
  <text x="220" y="104" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#2E7D32">tulis di luar setup/loop</text>
  <text x="220" y="132" text-anchor="middle" font-family="Consolas,monospace" font-size="12" fill="#1B5E20">void cetakStatus(...) { }</text>
  <text x="220" y="160" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#2E7D32">lalu panggil dari loop()</text>
  <rect x="460" y="40" width="360" height="140" rx="10" fill="#FFEBEE" stroke="#C62828" stroke-width="2.5"/>
  <text x="640" y="72" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#B71C1C">SALAH</text>
  <text x="640" y="104" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#C62828">jangan di dalam setup()</text>
  <text x="640" y="132" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#C62828">atau di dalam loop()</text>
  <text x="640" y="160" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#C62828">Verify sering error; compiler bingung</text>
  <text x="430" y="205" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#546E7A">Fungsi sejajar dengan setup/loop — bukan isinya</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Intinya:</strong> fungsi sejajar dengan <code>setup</code>/<code>loop</code>, bukan isinya. Referensi: <a href="https://docs.arduino.cc/learn/programming/functions/" rel="noopener noreferrer" target="_blank">Arduino Docs — Functions</a>.
    <br>Sumber gambar: diagram buatan Koding Indonesia (FS-16).
  </figcaption>
</figure>
SVG;
    }

    private function placeSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="where to put functions in Arduino sketch" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 220" width="100%" height="auto" role="img" aria-label="where to put functions">
  <text x="430" y="22" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">Where do you write a function? (important)</text>
  <rect x="40" y="40" width="360" height="140" rx="10" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="220" y="72" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#1B5E20">CORRECT</text>
  <text x="220" y="104" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#2E7D32">outside setup/loop</text>
  <text x="220" y="132" text-anchor="middle" font-family="Consolas,monospace" font-size="12" fill="#1B5E20">void printStatus(...) { }</text>
  <text x="220" y="160" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#2E7D32">then call it from loop()</text>
  <rect x="460" y="40" width="360" height="140" rx="10" fill="#FFEBEE" stroke="#C62828" stroke-width="2.5"/>
  <text x="640" y="72" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#B71C1C">WRONG</text>
  <text x="640" y="104" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#C62828">not inside setup()</text>
  <text x="640" y="132" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#C62828">or inside loop()</text>
  <text x="640" y="160" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#C62828">Verify often errors; compiler confused</text>
  <text x="430" y="205" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#546E7A">A function sits beside setup/loop — not inside them</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>In short:</strong> a function sits beside <code>setup</code>/<code>loop</code>, not inside them. Reference: <a href="https://docs.arduino.cc/learn/programming/functions/" rel="noopener noreferrer" target="_blank">Arduino Docs — Functions</a>.
    <br>Image source: diagram by Koding Indonesia (FS-16).
  </figcaption>
</figure>
SVG;
    }

    private function serialSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Serial menampilkan status dari fungsi" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 280" width="100%" height="auto" role="img" aria-label="Serial status from function">
  <text x="430" y="22" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">Serial Monitor — cetakStatus di loop (baud 115200)</text>
  <rect x="80" y="40" width="700" height="200" rx="10" fill="#1E1E1E" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="110" y="72" font-family="Consolas,monospace" font-size="14" fill="#A5D6A7">FS16_status siap</text>
  <text x="110" y="102" font-family="Consolas,monospace" font-size="14" fill="#81C784">status: DINGIN</text>
  <text x="360" y="102" font-family="system-ui,sans-serif" font-size="11" fill="#90A4AE">← suhu 18</text>
  <text x="110" y="132" font-family="Consolas,monospace" font-size="14" fill="#FFF59D">status: NORMAL</text>
  <text x="360" y="132" font-family="system-ui,sans-serif" font-size="11" fill="#90A4AE">← suhu 25</text>
  <text x="110" y="162" font-family="Consolas,monospace" font-size="14" fill="#FFAB91">status: PANAS</text>
  <text x="360" y="162" font-family="system-ui,sans-serif" font-size="11" fill="#90A4AE">← suhu 35</text>
  <text x="110" y="200" font-family="Consolas,monospace" font-size="13" fill="#FFE082">ganti angka suhu → Upload → label berubah</text>
  <rect x="520" y="60" width="220" height="36" rx="6" fill="#1565C0"/>
  <text x="630" y="83" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700" fill="#fff">Baud: 115200</text>
  <text x="430" y="262" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#546E7A">Contoh tiga label setelah ganti angka lalu Upload ulang</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Intinya:</strong> <code>loop()</code> hanya memanggil fungsi — isi detail ada di <code>cetakStatus</code>. Panduan Serial: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-serial-monitor/" rel="noopener noreferrer" target="_blank">Arduino Docs — Serial Monitor (IDE 2)</a>.
    <br>Sumber gambar: diagram buatan Koding Indonesia (FS-16).
  </figcaption>
</figure>
SVG;
    }

    private function serialSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Serial shows status from a function" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 280" width="100%" height="auto" role="img" aria-label="Serial status from function">
  <text x="430" y="22" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">Serial Monitor — printStatus in loop (baud 115200)</text>
  <rect x="80" y="40" width="700" height="200" rx="10" fill="#1E1E1E" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="110" y="72" font-family="Consolas,monospace" font-size="14" fill="#A5D6A7">FS16_status ready</text>
  <text x="110" y="102" font-family="Consolas,monospace" font-size="14" fill="#81C784">status: COLD</text>
  <text x="340" y="102" font-family="system-ui,sans-serif" font-size="11" fill="#90A4AE">← temp 18</text>
  <text x="110" y="132" font-family="Consolas,monospace" font-size="14" fill="#FFF59D">status: NORMAL</text>
  <text x="360" y="132" font-family="system-ui,sans-serif" font-size="11" fill="#90A4AE">← temp 25</text>
  <text x="110" y="162" font-family="Consolas,monospace" font-size="14" fill="#FFAB91">status: HOT</text>
  <text x="340" y="162" font-family="system-ui,sans-serif" font-size="11" fill="#90A4AE">← temp 35</text>
  <text x="110" y="200" font-family="Consolas,monospace" font-size="13" fill="#FFE082">change the number → Upload → label changes</text>
  <rect x="520" y="60" width="220" height="36" rx="6" fill="#1565C0"/>
  <text x="630" y="83" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700" fill="#fff">Baud: 115200</text>
  <text x="430" y="262" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#546E7A">Three labels after you change the number and Upload again</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>In short:</strong> <code>loop()</code> only calls the function — the details live in <code>printStatus</code>. Serial guide: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-serial-monitor/" rel="noopener noreferrer" target="_blank">Arduino Docs — Serial Monitor (IDE 2)</a>.
    <br>Image source: diagram by Koding Indonesia (FS-16).
  </figcaption>
</figure>
SVG;
    }

    private function body(): string
    {
        $ide = $this->ideFigureId();
        $board = $this->boardFigureId();
        $analogy = $this->analogySvgId();
        $parts = $this->partsSvgId();
        $place = $this->placeSvgId();
        $panel = $this->serialSvgId();

        return <<<HTML
<h2>Pendahuluan — program yang rapi</h2>
<p>Artikel ini adalah <strong>#86 (ini)</strong> · modul <strong>FS-16</strong> di jalur <em>Full Stack IoT Developer — Dari Nol</em>. Di <strong>FS-15</strong> kamu sudah bisa mengulang. Hari ini kita belajar <strong>fungsi</strong>: pecah program jadi blok bernama supaya mudah dibaca dan dipanggil ulang.</p>
<p><strong>Analogi:</strong> fungsi = resep bernama. Kamu tulis sekali, lalu panggil kapan saja — seperti “cetak status suhu” tanpa menyalin ulang semua baris <code>if</code>.</p>
<p><strong>Prasyarat:</strong> FS-15 (pernah lihat hitungan / Serial jalan).</p>
<p><strong>Cara pakai artikel ini (urutan kerja):</strong></p>
<ol>
<li><strong>Buka Arduino IDE</strong> dulu (bukan Laragon / terminal web).</li>
<li>Baca: apa itu fungsi, parameter, dan di mana menulisnya.</li>
<li>Buat sketch <code>FS16_status</code> → <strong>Verify</strong> → <strong>Upload</strong>.</li>
<li><strong>Buka Serial Monitor</strong> (ikon di kanan atas IDE 2) → baud <strong>115200</strong> → lihat baris <code>status: …</code>.</li>
<li>Centang checklist 10/10 di browser.</li>
</ol>
<p><strong>Tidak perlu hari ini:</strong> sensor nyata, Wi-Fi, Laragon, <code>php artisan</code>, wiring baru. Tools hari ini: <strong>Arduino IDE</strong> + <strong>Serial Monitor</strong> + ESP32 + kabel USB data + <strong>browser</strong> (checklist).</p>

<h2>Persiapan — buka &amp; siapkan ini dulu</h2>
<p><strong>Urutan meja kerja:</strong> sintaks diuji di Arduino IDE + Serial Monitor — bukan di terminal PHP.</p>
<ol>
<li>Buka <strong>Arduino IDE 2.x</strong>.</li>
<li>Board <strong>ESP32 Dev Module</strong> + port COM/tty sudah dipilih.</li>
<li>Siapkan ESP32 + kabel USB data.</li>
<li>Siapkan Serial Monitor (baud 115200) — tombolnya di kanan atas IDE.</li>
</ol>
<p><strong>Alat yang dipakai hari ini:</strong> Arduino IDE, Serial Monitor, ESP32, USB data, browser.</p>
<p><strong>Tidak dipakai hari ini:</strong> Laragon, <code>php artisan</code>, sensor, multimeter, PuTTY.</p>
{$ide}
{$board}

<h2>Fungsi itu apa?</h2>
{$analogy}
<p>Tanpa fungsi, kamu bisa <strong>salin-tempel</strong> blok yang sama berkali-kali. Itu cepat kacau. Dengan fungsi, <code>loop()</code> tetap pendek: cukup memanggil nama fungsi.</p>

<h2>Nama, parameter, dan return</h2>
{$parts}
<p>Contoh kerangka (belum sketch penuh):</p>
<pre><code class="language-cpp">void cetakStatus(int suhu) {
  // di sini boleh ada if/else
  Serial.print("status: ");
  Serial.println("DINGIN"); // contoh
}
</code></pre>
<p><code>int suhu</code> = parameter (angka yang kamu kirim saat memanggil). <code>void</code> = hari ini fungsi tidak mengembalikan angka — ia bekerja (mencetak).</p>
<p>Contoh singkat <code>return</code> (pengenalan):</p>
<pre><code class="language-cpp">int tambahSatu(int x) {
  return x + 1;
}
</code></pre>
<p>Praktik utama hari ini tetap <code>void cetakStatus(...)</code>. <code>return</code> cukup kamu kenali dulu.</p>

<h2>Di mana menulis fungsi?</h2>
{$place}
<p><strong>Peringatan penting:</strong> jangan menaruh definisi fungsi di dalam <code>setup()</code> atau <code>loop()</code>. Tulis di luar keduanya, lalu panggil dari dalam <code>loop()</code>.</p>

<h2>Praktik — sketch FS16_status</h2>
{$panel}
<p>Tujuan: <code>loop()</code> memanggil <code>cetakStatus(suhu)</code>, dan Serial menampilkan label (DINGIN / NORMAL / PANAS) berulang.</p>
<ol>
<li>Arduino IDE → <strong>File → New Sketch</strong> → <strong>Simpan sebagai</strong> (<em>Save As</em>) <code>FS16_status</code>.</li>
<li>Ganti isi dengan kode di bawah (salin utuh).</li>
<li><strong>Verify</strong> (✓) → <strong>Upload</strong> (→) → tunggu <em>Done uploading</em>.</li>
<li>Klik <strong>Open Serial Monitor</strong> (toolbar kanan atas IDE 2) → set baud <strong>115200</strong>.</li>
<li>Amati baris <code>status: …</code>. Ganti angka <code>suhu</code> (misalnya 18 / 25 / 35) → Upload lagi → label berubah (DINGIN / NORMAL / PANAS). Jika perlu, tekan tombol <strong>EN (7)</strong>.</li>
</ol>
<pre><code class="language-cpp">// FS16_status — Full Stack IoT FS-16
// Fungsi cetakStatus(int suhu) dipanggil dari loop.

void cetakStatus(int suhu) {
  Serial.print("status: ");
  if (suhu &lt; 20) {
    Serial.println("DINGIN");
  } else if (suhu &lt;= 30) {
    Serial.println("NORMAL");
  } else {
    Serial.println("PANAS");
  }
}

void setup() {
  Serial.begin(115200);
  delay(1000);
  Serial.println("FS16_status siap");
}

void loop() {
  int suhu = 18; // ganti angka ini: 18 / 25 / 35
  cetakStatus(suhu);
  delay(2000);
}
</code></pre>
<p><strong>Cara menguji perintah di atas:</strong> uji di <strong>Arduino IDE + Serial Monitor</strong>. Sukses = baris <code>status: DINGIN</code> (atau NORMAL/PANAS) muncul berulang di baud 115200, dan <code>loop()</code> terlihat pendek karena detail ada di fungsi. Bukan perintah Laragon / web server.</p>

<h2 id="fsiot-fn-checklist">Praktik — checklist fungsi</h2>
<p>Centang setiap langkah setelah kamu lakukan di meja. Target: <strong>10/10</strong>. Ada checklist interaktif di bawah.</p>
<ul id="fsiot-fn-checklist-items">
<li>Arduino IDE sudah terbuka sebelum menulis kode</li>
<li>Board ESP32 Dev Module + port sudah dipilih</li>
<li>Paham: fungsi = resep bernama yang dipanggil ulang</li>
<li>Paham: parameter membawa data masuk ke fungsi</li>
<li>Fungsi ditulis di luar setup dan loop</li>
<li>Sketch disimpan sebagai FS16_status</li>
<li>Upload berhasil — Done uploading</li>
<li>Serial Monitor terbuka, baud = 115200</li>
<li>Baris status muncul dari pemanggilan cetakStatus</li>
<li>Sadar: ganti angka suhu lalu Upload lagi untuk uji label</li>
</ul>
<p><strong>Cara menguji checklist:</strong> centang di browser setelah praktik di IDE. Tidak perlu <code>php artisan</code>.</p>

<h2>Kesalahan yang sering terjadi</h2>
<ul>
<li><strong>Fungsi ditulis di dalam setup/loop.</strong> Pindahkan ke luar. Lihat diagram BENAR/SALAH di atas.</li>
<li><strong>Lupa memanggil fungsi.</strong> Menulis <code>cetakStatus</code> saja tidak cukup — harus <code>cetakStatus(suhu);</code> di <code>loop</code>.</li>
<li><strong>Salah ketik nama.</strong> Nama saat definisi dan saat panggil harus sama (huruf besar/kecil ikut).</li>
<li><strong>Baud salah.</strong> Samakan 115200 di kode dan Serial Monitor.</li>
<li><strong>Menguji di terminal web.</strong> Sketch hanya jalan di board lewat IDE.</li>
<li><strong>Mengharapkan sensor nyata.</strong> Hari ini angka <code>suhu</code> masih dummy (seperti FS-14).</li>
<li><strong>Indentasi berantakan.</strong> Rapikan <code>{ }</code> supaya blok fungsi mudah dicek.</li>
</ul>

<h2>Selanjutnya</h2>
<p><strong>Intinya:</strong> kalau <code>loop()</code> pendek dan Serial menampilkan status lewat fungsi, FS-16 selesai — fase ZERO hampir penuh.</p>
<p>Lanjut ke <strong>FS-17</strong> (peta pin board resmi) saat modulnya terbit. Gate ZERO → BUILDER menyusul setelah kuis/gate fase.</p>
<p>Daftar modul lengkap: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $ide = $this->ideFigureEn();
        $board = $this->boardFigureEn();
        $analogy = $this->analogySvgEn();
        $parts = $this->partsSvgEn();
        $place = $this->placeSvgEn();
        $panel = $this->serialSvgEn();

        return <<<HTML
<h2>Introduction — tidy programs</h2>
<p>This is article <strong>#86 (this article)</strong> · module <strong>FS-16</strong> on the <em>Full Stack IoT Developer — From Zero</em> path. In <strong>FS-15</strong> you learned to repeat. Today we learn <strong>functions</strong>: split the program into named blocks you can call again.</p>
<p><strong>Analogy:</strong> a function is a named recipe. Write it once, call it whenever you need — like “print temperature status” without copying every <code>if</code> line.</p>
<p><strong>Prerequisites:</strong> FS-15 (you have seen counts / Serial working).</p>
<p><strong>How to use this article (work order):</strong></p>
<ol>
<li><strong>Open Arduino IDE</strong> first (not Laragon / a web terminal).</li>
<li>Read: what a function is, parameters, and where to write it.</li>
<li>Create sketch <code>FS16_status</code> → <strong>Verify</strong> → <strong>Upload</strong>.</li>
<li><strong>Open Serial Monitor</strong> (top-right icon in IDE 2) → baud <strong>115200</strong> → watch <code>status: …</code> lines.</li>
<li>Tick the 10/10 checklist in the browser.</li>
</ol>
<p><strong>Not needed today:</strong> real sensors, Wi-Fi, Laragon, <code>php artisan</code>, new wiring. Today's tools: <strong>Arduino IDE</strong> + <strong>Serial Monitor</strong> + ESP32 + USB data cable + <strong>browser</strong> (checklist).</p>

<h2>Preparation — open &amp; gather these first</h2>
<p><strong>Desk order:</strong> syntax is tested in Arduino IDE + Serial Monitor — not in a PHP terminal.</p>
<ol>
<li>Open <strong>Arduino IDE 2.x</strong>.</li>
<li><strong>ESP32 Dev Module</strong> board + COM/tty port are selected.</li>
<li>Prepare the ESP32 + USB data cable.</li>
<li>Have Serial Monitor ready (baud 115200) — button is top-right in the IDE.</li>
</ol>
<p><strong>Tools used today:</strong> Arduino IDE, Serial Monitor, ESP32, USB data, browser.</p>
<p><strong>Not used today:</strong> Laragon, <code>php artisan</code>, sensors, multimeter, PuTTY.</p>
{$ide}
{$board}

<h2>What is a function?</h2>
{$analogy}
<p>Without functions, you copy-paste the same block many times. That gets messy. With a function, <code>loop()</code> stays short: just call the name.</p>

<h2>Name, parameter, and return</h2>
{$parts}
<p>Skeleton example (not the full sketch yet):</p>
<pre><code class="language-cpp">void printStatus(int temp) {
  // if/else can live here
  Serial.print("status: ");
  Serial.println("COLD"); // example
}
</code></pre>
<p><code>int temp</code> is a parameter (the number you pass when calling). <code>void</code> means today the function does not return a number — it works (it prints).</p>
<p>Short <code>return</code> intro:</p>
<pre><code class="language-cpp">int addOne(int x) {
  return x + 1;
}
</code></pre>
<p>Today's main practice stays <code>void printStatus(...)</code>. Just recognize <code>return</code> for now.</p>

<h2>Where do you write a function?</h2>
{$place}
<p><strong>Important warning:</strong> do not define a function inside <code>setup()</code> or <code>loop()</code>. Write it outside both, then call it from <code>loop()</code>.</p>

<h2>Practice — sketch FS16_status</h2>
{$panel}
<p>Goal: <code>loop()</code> calls <code>printStatus(temp)</code>, and Serial shows a label (COLD / NORMAL / HOT) repeatedly.</p>
<ol>
<li>Arduino IDE → <strong>File → New Sketch</strong> → <strong>Save as</strong> <code>FS16_status</code>.</li>
<li>Replace the contents with the code below (copy it whole).</li>
<li><strong>Verify</strong> (✓) → <strong>Upload</strong> (→) → wait for <em>Done uploading</em>.</li>
<li>Click <strong>Open Serial Monitor</strong> (top-right toolbar in IDE 2) → set baud <strong>115200</strong>.</li>
<li>Watch <code>status: …</code>. Change the <code>temp</code> number (e.g. 18 / 25 / 35) → Upload again → the label changes (COLD / NORMAL / HOT). Press <strong>EN (7)</strong> if needed.</li>
</ol>
<pre><code class="language-cpp">// FS16_status — Full Stack IoT FS-16
// Function printStatus(int temp) called from loop.

void printStatus(int temp) {
  Serial.print("status: ");
  if (temp &lt; 20) {
    Serial.println("COLD");
  } else if (temp &lt;= 30) {
    Serial.println("NORMAL");
  } else {
    Serial.println("HOT");
  }
}

void setup() {
  Serial.begin(115200);
  delay(1000);
  Serial.println("FS16_status ready");
}

void loop() {
  int temp = 18; // change this: 18 / 25 / 35
  printStatus(temp);
  delay(2000);
}
</code></pre>
<p><strong>How to test the code above:</strong> test in <strong>Arduino IDE + Serial Monitor</strong>. Success = <code>status: COLD</code> (or NORMAL/HOT) repeats at baud 115200, and <code>loop()</code> stays short because details live in the function. Not a Laragon / web-server command.</p>

<h2 id="fsiot-fn-checklist">Practice — function checklist</h2>
<p>Tick each step after you do it at the desk. Target: <strong>10/10</strong>. An interactive checklist is below.</p>
<ul id="fsiot-fn-checklist-items">
<li>Arduino IDE is open before writing code</li>
<li>ESP32 Dev Module board + port are selected</li>
<li>I understand: a function is a named recipe you call again</li>
<li>I understand: a parameter carries data into the function</li>
<li>The function is written outside setup and loop</li>
<li>Sketch saved as FS16_status</li>
<li>Upload succeeded — Done uploading</li>
<li>Serial Monitor open, baud = 115200</li>
<li>Status lines appear from calling printStatus</li>
<li>I know: change the temp number and Upload again to test labels</li>
</ul>
<p><strong>How to test the checklist:</strong> tick it in the browser after IDE practice. No <code>php artisan</code> required.</p>

<h2>Common mistakes</h2>
<ul>
<li><strong>Function written inside setup/loop.</strong> Move it outside. See the CORRECT/WRONG diagram above.</li>
<li><strong>Forgetting to call it.</strong> Defining <code>printStatus</code> is not enough — you need <code>printStatus(temp);</code> in <code>loop</code>.</li>
<li><strong>Name typo.</strong> Definition name and call name must match (including letter case).</li>
<li><strong>Wrong baud.</strong> Match 115200 in code and Serial Monitor.</li>
<li><strong>Testing in a web terminal.</strong> Sketches only run on the board via the IDE.</li>
<li><strong>Expecting a real sensor.</strong> Today <code>temp</code> is still a dummy number (like FS-14).</li>
<li><strong>Messy indentation.</strong> Tidy <code>{ }</code> so the function block is easy to check.</li>
</ul>

<h2>Next steps</h2>
<p><strong>In short:</strong> if <code>loop()</code> is short and Serial shows status via a function, FS-16 is done — phase ZERO is almost full.</p>
<p>Continue to <strong>FS-17</strong> (official board pin map) when that module publishes. The ZERO → BUILDER gate follows after the phase quiz/gate.</p>
<p>Full module list: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }
}
