<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article81Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first()
            ?? Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $iotCat) {
            throw new \RuntimeException('User atau kategori iot-smart-device/esp32-arduino tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-sketch-setup-loop';

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
                'title'              => 'Program adalah resep: sketch, setup, dan loop',
                'title_en'           => 'A program is a recipe: sketch, setup, and loop',
                'excerpt'            => 'FS-11 / #81: Pahami sketch sebagai file resep, bedakan setup() sekali jalan vs loop() berulang, lalu Verify → Upload di Arduino IDE. Simpan di folder sketchbook biar tidak menimpa.',
                'excerpt_en'         => 'FS-11 / #81: Treat a sketch as a recipe file, tell setup() (once) from loop() (forever), then Verify → Upload in Arduino IDE. Save under a sketchbook folder so you do not overwrite work.',
                'body'               => $this->body(),
                'body_en'            => $this->bodyEn(),
                'status'             => 'draft',
                'is_featured'        => false,
                'published_at'       => null,
                'seo_title'          => 'Sketch, setup, loop — Resep Program — Full Stack IoT #81',
                'seo_title_en'       => 'Sketch, setup, loop — Program Recipe — Full Stack IoT #81',
                'seo_description'    => 'Belajar sketch Arduino sebagai resep: setup() sekali, loop() berulang, komentar, Verify vs Upload, dan folder sketchbook FS11_hello. Modul FS-11 jalur Full Stack IoT.',
                'seo_description_en' => 'Learn an Arduino sketch as a recipe: setup() once, loop() forever, comments, Verify vs Upload, and the FS11_hello sketchbook folder. Full Stack IoT FS-11 module.',
            ]
        );

        if ($article->published_at !== null || $article->status !== 'draft') {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['fullstack-iot', 'iot', 'esp32'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel #81 / FS-11 tersimpan sebagai DRAFT: '.$article->title);
    }

    private function ideOverviewFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/fs11-ide-overview-cite.png" width="1280" height="720" alt="Arduino IDE 2 — jendela editor dengan toolbar Verify dan Upload di kiri atas" loading="eager" style="width:100%;height:auto;max-height:520px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Arduino IDE 2</strong> — “Word”-nya sketch. Di kiri atas toolbar: tombol <strong>Verify</strong> (centang ✓) dan <strong>Upload</strong> (panah →). Board + port harus sudah dipilih (ulangi kebiasaan FS-06).
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:Ide-2-overview.png" rel="noopener noreferrer" target="_blank">Arduino IDE 2 overview</a> · Wikimedia Commons (CC BY-SA 3.0) · asal dokumen Arduino.
  </figcaption>
</figure>
HTML;
    }

    private function ideOverviewFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/fs11-ide-overview-cite.png" width="1280" height="720" alt="Arduino IDE 2 — editor window with Verify and Upload toolbar on the top left" loading="eager" style="width:100%;height:auto;max-height:520px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Arduino IDE 2</strong> — the “Word” for sketches. Top-left toolbar: <strong>Verify</strong> (checkmark ✓) and <strong>Upload</strong> (arrow →). Board + port must already be selected (repeat FS-06 habits).
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:Ide-2-overview.png" rel="noopener noreferrer" target="_blank">Arduino IDE 2 overview</a> · Wikimedia Commons (CC BY-SA 3.0) · from Arduino docs.
  </figcaption>
</figure>
HTML;
    }

    private function verifyFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/fs11-select-verify.png" width="593" height="600" alt="Menu Sketch — perintah Verify/Compile di Arduino IDE" loading="lazy" style="width:100%;height:auto;max-height:420px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Verify / Compile</strong> = cek ejaan &amp; susunan kode (compile) <em>tanpa</em> mengirim ke board. Target status: <em>Done compiling</em>.
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:Select_verify.png" rel="noopener noreferrer" target="_blank">Select verify.png</a> · Wikimedia Commons.
  </figcaption>
</figure>
HTML;
    }

    private function verifyFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/fs11-select-verify.png" width="593" height="600" alt="Sketch menu — Verify/Compile command in Arduino IDE" loading="lazy" style="width:100%;height:auto;max-height:420px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Verify / Compile</strong> = check spelling &amp; structure (compile) <em>without</em> sending to the board. Target status: <em>Done compiling</em>.
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:Select_verify.png" rel="noopener noreferrer" target="_blank">Select verify.png</a> · Wikimedia Commons.
  </figcaption>
</figure>
HTML;
    }

    private function uploadFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/fs11-select-upload.png" width="593" height="600" alt="Menu Sketch — perintah Upload di Arduino IDE" loading="lazy" style="width:100%;height:auto;max-height:420px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Upload</strong> = compile dulu, lalu kirim program ke ESP32 lewat USB. Target status: <em>Done uploading</em>.
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:Select_upload_Arduino_IDE.png" rel="noopener noreferrer" target="_blank">Select upload Arduino IDE.png</a> · Wikimedia Commons.
  </figcaption>
</figure>
HTML;
    }

    private function uploadFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/fs11-select-upload.png" width="593" height="600" alt="Sketch menu — Upload command in Arduino IDE" loading="lazy" style="width:100%;height:auto;max-height:420px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Upload</strong> = compile first, then send the program to the ESP32 over USB. Target status: <em>Done uploading</em>.
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:Select_upload_Arduino_IDE.png" rel="noopener noreferrer" target="_blank">Select upload Arduino IDE.png</a> · Wikimedia Commons.
  </figcaption>
</figure>
HTML;
    }

    private function boardFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/esp32-devkitc-overview.jpg" width="1200" height="800" alt="ESP32-DevKitC — board yang menerima sketch lewat kabel USB data" loading="lazy" style="width:100%;height:auto;max-height:360px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>ESP32-DevKitC</strong> — “dapur” yang memasak resep (sketch). Colok <strong>kabel USB data</strong> (bukan charge-only), pilih board + port seperti FS-06.
    <br>Sumber gambar: <a href="https://docs.espressif.com/projects/esp-dev-kits/en/latest/esp32/esp32-devkitc/user_guide.html" rel="noopener noreferrer" target="_blank">Espressif — ESP32-DevKitC user guide</a>.
  </figcaption>
</figure>
HTML;
    }

    private function boardFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/esp32-devkitc-overview.jpg" width="1200" height="800" alt="ESP32-DevKitC — the board that receives a sketch over a USB data cable" loading="lazy" style="width:100%;height:auto;max-height:360px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>ESP32-DevKitC</strong> — the “kitchen” that cooks the recipe (sketch). Plug a <strong>USB data cable</strong> (not charge-only), then pick board + port as in FS-06.
    <br>Image source: <a href="https://docs.espressif.com/projects/esp-dev-kits/en/latest/esp32/esp32-devkitc/user_guide.html" rel="noopener noreferrer" target="_blank">Espressif — ESP32-DevKitC user guide</a>.
  </figcaption>
</figure>
HTML;
    }

    private function recipeSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Analogi resep: setup sekali, loop berulang" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 280" width="100%" height="auto" role="img" aria-label="setup sekali lalu loop berulang">
  <text x="430" y="28" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700">Resep di dapur — setup sekali, loop berulang</text>
  <rect x="40" y="55" width="240" height="160" rx="10" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="160" y="95" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1B5E20">setup()</text>
  <text x="160" y="125" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#33691E">Siapkan dapur</text>
  <text x="160" y="150" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#33691E">Jalan SEKALI</text>
  <text x="160" y="175" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#558B2F">saat board nyala / reset</text>
  <path d="M300 135 L360 135" stroke="#1565C0" stroke-width="3" fill="none" marker-end="url(#arr81)"/>
  <defs><marker id="arr81" markerWidth="8" markerHeight="8" refX="6" refY="3" orient="auto"><path d="M0,0 L6,3 L0,6 Z" fill="#1565C0"/></marker></defs>
  <rect x="380" y="55" width="240" height="160" rx="10" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
  <text x="500" y="95" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#0D47A1">loop()</text>
  <text x="500" y="125" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#1565C0">Masak terus</text>
  <text x="500" y="150" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#1565C0">BERULANG</text>
  <text x="500" y="175" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#1976D2">sampai listrik dicabut</text>
  <path d="M500 215 Q500 245 440 245 Q380 245 380 200" stroke="#1565C0" stroke-width="2.5" fill="none" stroke-dasharray="6 4"/>
  <text x="500" y="262" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#1565C0">kembali ke awal loop</text>
  <rect x="650" y="70" width="170" height="130" rx="10" fill="#FFF8E1" stroke="#F9A825" stroke-width="2.5"/>
  <text x="735" y="110" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700" fill="#F57F17">sketch (.ino)</text>
  <text x="735" y="140" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#795548">= file resep</text>
  <text x="735" y="165" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#795548">di sketchbook</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Analogi:</strong> <code>setup()</code> = siapkan kompor sekali · <code>loop()</code> = masak berulang · file <code>.ino</code> = resep tertulis.
    <br>Sumber gambar: diagram buatan Koding Indonesia (FS-11).
  </figcaption>
</figure>
SVG;
    }

    private function recipeSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Recipe analogy: setup once, loop forever" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 280" width="100%" height="auto" role="img" aria-label="setup once then loop forever">
  <text x="430" y="28" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700">Kitchen recipe — setup once, loop forever</text>
  <rect x="40" y="55" width="240" height="160" rx="10" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="160" y="95" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1B5E20">setup()</text>
  <text x="160" y="125" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#33691E">Prep the kitchen</text>
  <text x="160" y="150" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#33691E">Runs ONCE</text>
  <text x="160" y="175" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#558B2F">on power / reset</text>
  <path d="M300 135 L360 135" stroke="#1565C0" stroke-width="3" fill="none" marker-end="url(#arr81e)"/>
  <defs><marker id="arr81e" markerWidth="8" markerHeight="8" refX="6" refY="3" orient="auto"><path d="M0,0 L6,3 L0,6 Z" fill="#1565C0"/></marker></defs>
  <rect x="380" y="55" width="240" height="160" rx="10" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
  <text x="500" y="95" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#0D47A1">loop()</text>
  <text x="500" y="125" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#1565C0">Keep cooking</text>
  <text x="500" y="150" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#1565C0">REPEATS</text>
  <text x="500" y="175" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#1976D2">until power off</text>
  <path d="M500 215 Q500 245 440 245 Q380 245 380 200" stroke="#1565C0" stroke-width="2.5" fill="none" stroke-dasharray="6 4"/>
  <text x="500" y="262" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#1565C0">back to start of loop</text>
  <rect x="650" y="70" width="170" height="130" rx="10" fill="#FFF8E1" stroke="#F9A825" stroke-width="2.5"/>
  <text x="735" y="110" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700" fill="#F57F17">sketch (.ino)</text>
  <text x="735" y="140" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#795548">= written recipe</text>
  <text x="735" y="165" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#795548">in sketchbook</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Analogy:</strong> <code>setup()</code> = prep the stove once · <code>loop()</code> = keep cooking · <code>.ino</code> file = the written recipe.
    <br>Image source: diagram by Koding Indonesia (FS-11).
  </figcaption>
</figure>
SVG;
    }

    private function flowSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Alur kerja edit Verify Upload run" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 160" width="100%" height="auto" role="img" aria-label="Edit lalu Verify lalu Upload lalu run di board">
  <text x="430" y="26" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700">Alur kerja hari ini (urutan tombol)</text>
  <rect x="20" y="50" width="150" height="70" rx="8" fill="#FFF" stroke="#1a1a1a" stroke-width="2"/>
  <text x="95" y="80" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">1. Edit</text>
  <text x="95" y="100" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11">tulis di IDE</text>
  <text x="185" y="90" font-family="system-ui,sans-serif" font-size="18" fill="#1565C0">→</text>
  <rect x="210" y="50" width="150" height="70" rx="8" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2"/>
  <text x="285" y="80" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">2. Verify</text>
  <text x="285" y="100" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11">Done compiling</text>
  <text x="375" y="90" font-family="system-ui,sans-serif" font-size="18" fill="#1565C0">→</text>
  <rect x="400" y="50" width="150" height="70" rx="8" fill="#E3F2FD" stroke="#1565C0" stroke-width="2"/>
  <text x="475" y="80" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">3. Upload</text>
  <text x="475" y="100" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11">Done uploading</text>
  <text x="565" y="90" font-family="system-ui,sans-serif" font-size="18" fill="#1565C0">→</text>
  <rect x="590" y="50" width="240" height="70" rx="8" fill="#FFF8E1" stroke="#F9A825" stroke-width="2"/>
  <text x="710" y="80" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">4. Run di board</text>
  <text x="710" y="100" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11">setup lalu loop otomatis</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Intinya:</strong> edit di laptop → Verify (cek) → Upload (kirim) → board menjalankan sendiri. Mengedit tanpa Upload = board masih pakai program lama.
    <br>Sumber gambar: diagram buatan Koding Indonesia (FS-11).
  </figcaption>
</figure>
SVG;
    }

    private function flowSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Workflow edit Verify Upload run" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 160" width="100%" height="auto" role="img" aria-label="Edit then Verify then Upload then run on board">
  <text x="430" y="26" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700">Today's workflow (button order)</text>
  <rect x="20" y="50" width="150" height="70" rx="8" fill="#FFF" stroke="#1a1a1a" stroke-width="2"/>
  <text x="95" y="80" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">1. Edit</text>
  <text x="95" y="100" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11">type in the IDE</text>
  <text x="185" y="90" font-family="system-ui,sans-serif" font-size="18" fill="#1565C0">→</text>
  <rect x="210" y="50" width="150" height="70" rx="8" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2"/>
  <text x="285" y="80" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">2. Verify</text>
  <text x="285" y="100" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11">Done compiling</text>
  <text x="375" y="90" font-family="system-ui,sans-serif" font-size="18" fill="#1565C0">→</text>
  <rect x="400" y="50" width="150" height="70" rx="8" fill="#E3F2FD" stroke="#1565C0" stroke-width="2"/>
  <text x="475" y="80" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">3. Upload</text>
  <text x="475" y="100" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11">Done uploading</text>
  <text x="565" y="90" font-family="system-ui,sans-serif" font-size="18" fill="#1565C0">→</text>
  <rect x="590" y="50" width="240" height="70" rx="8" fill="#FFF8E1" stroke="#F9A825" stroke-width="2"/>
  <text x="710" y="80" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">4. Run on board</text>
  <text x="710" y="100" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11">setup then loop auto</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>In short:</strong> edit on the laptop → Verify (check) → Upload (send) → the board runs by itself. Editing without Upload leaves the old program on the board.
    <br>Image source: diagram by Koding Indonesia (FS-11).
  </figcaption>
</figure>
SVG;
    }

    private function sketchbookSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Struktur folder sketchbook FS11_hello" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 220" width="100%" height="auto" role="img" aria-label="Folder Arduino berisi FS11_hello">
  <text x="430" y="28" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700">Sketchbook — satu folder per proyek</text>
  <rect x="80" y="50" width="700" height="140" rx="10" fill="#FAFAFA" stroke="#1a1a1a" stroke-width="2"/>
  <text x="110" y="85" font-family="Consolas,monospace" font-size="14" fill="#1a1a1a">Documents / Arduino /</text>
  <text x="140" y="115" font-family="Consolas,monospace" font-size="14" font-weight="700" fill="#1565C0">FS11_hello /</text>
  <text x="170" y="145" font-family="Consolas,monospace" font-size="14" fill="#2E7D32">FS11_hello.ino</text>
  <text x="170" y="170" font-family="system-ui,sans-serif" font-size="12" fill="#616161">nama folder = nama file .ino (aturan Arduino)</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Tips:</strong> di Windows biasanya <code>Documents\Arduino\</code>. Jangan menimpa sketch lama — buat folder baru per modul (<code>FS11_hello</code>, nanti <code>FS12_...</code>).
    <br>Sumber gambar: diagram buatan Koding Indonesia (FS-11).
  </figcaption>
</figure>
SVG;
    }

    private function sketchbookSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Sketchbook folder structure for FS11_hello" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 220" width="100%" height="auto" role="img" aria-label="Arduino folder containing FS11_hello">
  <text x="430" y="28" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700">Sketchbook — one folder per project</text>
  <rect x="80" y="50" width="700" height="140" rx="10" fill="#FAFAFA" stroke="#1a1a1a" stroke-width="2"/>
  <text x="110" y="85" font-family="Consolas,monospace" font-size="14" fill="#1a1a1a">Documents / Arduino /</text>
  <text x="140" y="115" font-family="Consolas,monospace" font-size="14" font-weight="700" fill="#1565C0">FS11_hello /</text>
  <text x="170" y="145" font-family="Consolas,monospace" font-size="14" fill="#2E7D32">FS11_hello.ino</text>
  <text x="170" y="170" font-family="system-ui,sans-serif" font-size="12" fill="#616161">folder name = .ino file name (Arduino rule)</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Tip:</strong> on Windows this is often <code>Documents\Arduino\</code>. Do not overwrite old sketches — make a new folder per module (<code>FS11_hello</code>, later <code>FS12_...</code>).
    <br>Image source: diagram by Koding Indonesia (FS-11).
  </figcaption>
</figure>
SVG;
    }

    private function body(): string
    {
        $ide = $this->ideOverviewFigureId();
        $board = $this->boardFigureId();
        $recipe = $this->recipeSvgId();
        $flow = $this->flowSvgId();
        $book = $this->sketchbookSvgId();
        $verify = $this->verifyFigureId();
        $upload = $this->uploadFigureId();

        return <<<HTML
<h2>Pendahuluan — kenapa disebut “resep”?</h2>
<p>Artikel ini adalah <strong>#81 (ini)</strong> · modul <strong>FS-11</strong> di jalur <em>Full Stack IoT Developer — Dari Nol</em>. Di <strong>FS-06</strong> komputer + Arduino IDE sudah siap upload. Di <strong>FS-10</strong> kamu paham bahasa sinyal HIGH/LOW. Hari ini kita fokus mental model: <strong>sketch = resep tertulis</strong>, lalu bedakan <code>setup()</code> dan <code>loop()</code>, plus kebiasaan simpan file supaya tidak menimpa.</p>
<p><strong>Analogi:</strong> sketch seperti resep masakan. <code>setup()</code> = siapkan dapur sekali. <code>loop()</code> = masak langkah yang sama berulang. Board = dapur yang menjalankan resep setelah kamu Upload.</p>
<p><strong>Prasyarat:</strong> FS-06 (Arduino IDE, board ESP32 Dev Module, port COM/tty, pernah <em>Done uploading</em>) + FS-10 (paham HIGH/LOW — belum wajib wiring tombol hari ini).</p>
<p><strong>Cara pakai artikel ini (urutan kerja):</strong></p>
<ol>
<li><strong>Buka alat</strong> di daftar Persiapan — Arduino IDE dulu, belum colok USB jika masih merapikan file.</li>
<li><strong>Baca + lihat gambar</strong> sampai paham setup vs loop dan Verify vs Upload.</li>
<li><strong>Buat sketch</strong> <code>FS11_hello</code> di sketchbook (langkah praktik).</li>
<li><strong>Verify → Upload</strong> di IDE — target <em>Done uploading</em>.</li>
<li><strong>Centang checklist 10/10</strong> di browser.</li>
</ol>
<p><strong>Tidak perlu hari ini:</strong> Serial Monitor (itu FS-12/FS-13), <code>pinMode</code> / <code>digitalRead</code> tombol, wiring breadboard baru, Laragon, terminal proyek web, <code>php artisan</code>. Hari ini tools-nya: <strong>Arduino IDE</strong> + <strong>File Explorer</strong> (lihat folder sketchbook) + ESP32 + kabel USB data + <strong>browser</strong> (checklist).</p>

<h2>Persiapan — buka &amp; siapkan ini dulu</h2>
<p><strong>Urutan meja kerja:</strong> jangan langsung tekan Upload. Ikuti urutan supaya tidak bingung “sintaks di mana diuji”.</p>
<ol>
<li><strong>Buka Arduino IDE 2.x</strong> di komputer (bukan browser, bukan Laragon).</li>
<li><strong>Buka File Explorer</strong> (Windows) atau Finder (macOS) — nanti kita cek folder sketchbook.</li>
<li>Siapkan <strong>ESP32-DevKitC</strong> + <strong>kabel USB data</strong> (ingat FS-05/FS-06).</li>
<li>Di IDE: pastikan board <strong>ESP32 Dev Module</strong> + <strong>port COM/tty</strong> sudah dipilih (ulangi FS-06).</li>
<li>Baru colok USB saat mau Upload.</li>
</ol>
<p><strong>Alat yang dipakai hari ini:</strong> Arduino IDE, File Explorer/Finder, ESP32, kabel USB data, browser (artikel + checklist).</p>
<p><strong>Tidak dipakai hari ini:</strong> Serial Monitor sebagai fokus utama, multimeter, Laragon, terminal <code>php artisan</code>, editor PHP.</p>
{$ide}
{$board}

<h2>Apa itu sketch?</h2>
<p><strong>Sketch</strong> = satu proyek program untuk board. File utamanya berakhiran <code>.ino</code> (contoh: <code>FS11_hello.ino</code>). Isinya teks yang kamu tulis di Arduino IDE.</p>
<p><strong>Intinya:</strong> sketch bukan “aplikasi Windows”. Sketch baru hidup di board setelah <strong>Upload</strong> berhasil. Menyimpan di laptop saja belum mengubah perilaku board.</p>

<h2>setup() sekali · loop() berulang</h2>
{$recipe}
<p>Setiap sketch “standar” punya dua fungsi ini:</p>
<ul>
<li><code>void setup() { ... }</code> — dijalankan <strong>sekali</strong> saat board baru menyala atau di-reset.</li>
<li><code>void loop() { ... }</code> — dijalankan <strong>berulang terus</strong> setelah <code>setup</code> selesai, sampai listrik dicabut / reset lagi.</li>
</ul>
<p><strong>Komentar</strong> = catatan untuk manusia, diabaikan mesin. Diawali <code>//</code> sampai akhir baris. Pakai komentar supaya kamu ingat maksud baris tanpa menebak.</p>

<h2>Verify vs Upload vs run</h2>
{$flow}
{$verify}
{$upload}
<p><strong>Cara menguji di meja (bukan di terminal web):</strong></p>
<ol>
<li>Pastikan Arduino IDE sudah terbuka (lihat Persiapan).</li>
<li>Klik <strong>Verify</strong> (✓) → tunggu tulisan <em>Done compiling</em> di panel bawah.</li>
<li>Colok USB → pastikan port benar → klik <strong>Upload</strong> (→) → tunggu <em>Done uploading</em>.</li>
<li>Board lalu menjalankan <code>setup</code> lalu <code>loop</code> sendiri — itu artinya <strong>run</strong>.</li>
</ol>
<p><strong>Tips:</strong> Upload juga meng-compile. Verify berguna supaya kamu cek error ketik sebelum mengirim.</p>

<h2>Folder sketchbook — jangan menimpa</h2>
{$book}
<p>Arduino menyimpan proyek di folder <strong>sketchbook</strong> (biasanya <code>Documents\Arduino</code> di Windows). Setiap sketch = <strong>satu folder</strong> berisi file <code>.ino</code> dengan <strong>nama sama</strong>.</p>
<p><strong>Cara cek di File Explorer:</strong> buka <code>Documents\Arduino</code> → cari folder <code>FS11_hello</code> → di dalamnya harus ada <code>FS11_hello.ino</code>. Kalau folder kosong atau nama beda, Save ulang dari IDE.</p>

<h2>Praktik — sketch “do nothing” FS11_hello</h2>
<p>Tujuan praktik: paham struktur + berhasil Upload ulang. LED tidak wajib berkedip hari ini (itu sudah pernah di FS-06). Fokus = file rapi + tombol IDE.</p>
<ol>
<li>Di Arduino IDE: <strong>File → New Sketch</strong>.</li>
<li><strong>File → Save</strong> — beri nama <code>FS11_hello</code> (IDE akan buat foldernya).</li>
<li>Ganti isi editor dengan kode di bawah (salin utuh).</li>
<li>Klik <strong>Verify</strong> → harus <em>Done compiling</em>.</li>
<li>Colok ESP32 → pilih port → klik <strong>Upload</strong> → harus <em>Done uploading</em>.</li>
<li>Buka File Explorer → pastikan folder <code>FS11_hello</code> ada di sketchbook.</li>
</ol>
<pre><code class="language-cpp">// FS11_hello — modul Full Stack IoT FS-11
// Sketch "do nothing": fokus paham setup/loop + kebiasaan Upload.

void setup() {
  // setup() jalan SEKALI saat board menyala / di-reset
  // (belum pinMode / Serial — itu modul berikutnya)
}

void loop() {
  // loop() berulang terus setelah setup selesai
  // sengaja kosong: board "diam" tetap OK untuk latihan Upload
}
</code></pre>
<p><strong>Cara menguji perintah di atas:</strong> uji di <strong>Arduino IDE</strong> (Verify/Upload), bukan di terminal Laragon atau <code>php artisan</code>. Sukses = status <em>Done uploading</em> + file terlihat di File Explorer.</p>

<h2 id="fsiot-sketch-checklist">Praktik — checklist sketch &amp; Upload</h2>
<p>Centang setiap langkah setelah kamu lakukan di meja. Target: <strong>10/10</strong>. Ada checklist interaktif di bawah; versi kertas tetap tersedia.</p>
<ul id="fsiot-sketch-checklist-items">
<li>Arduino IDE 2.x sudah terbuka sebelum menulis kode</li>
<li>Board ESP32 Dev Module + port COM/tty sudah dipilih</li>
<li>Paham sketch = file resep (.ino), bukan aplikasi PC</li>
<li>Bisa jelaskan: setup() sekali, loop() berulang</li>
<li>Tahu beda Verify (Done compiling) vs Upload (Done uploading)</li>
<li>Sketch disimpan sebagai FS11_hello (folder + .ino sama nama)</li>
<li>File Explorer menunjukkan Documents\Arduino\FS11_hello\</li>
<li>Verify berhasil — Done compiling</li>
<li>Upload berhasil — Done uploading</li>
<li>Sadar: edit tanpa Upload = board masih program lama</li>
</ul>
<p><strong>Cara menguji checklist:</strong> kerjakan di browser setelah praktik di IDE. Tidak perlu Laragon atau <code>php artisan</code> — “menguji” = status IDE + folder terlihat + centang (bukan perintah sintaks web).</p>

<h2>Kesalahan yang sering terjadi</h2>
<ul>
<li><strong>Mengedit lalu lupa Upload.</strong> Board tidak membaca file di laptop sampai Upload sukses.</li>
<li><strong>Menimpa sketch lama.</strong> Save dengan nama sama → isi lama hilang. Pakai nama per modul (<code>FS11_hello</code>).</li>
<li><strong>Port belum dipilih.</strong> Upload gagal meski kode benar — ulangi FS-06.</li>
<li><strong>Kabel charge-only.</strong> Port tidak muncul / upload gagal — ganti kabel data.</li>
<li><strong>Bingung kenapa Serial kosong.</strong> Hari ini kita belum pakai Serial Monitor; itu wajar. Serial dibahas di FS-12/FS-13.</li>
<li><strong>Mengira Verify = sudah jalan di board.</strong> Verify hanya compile. Butuh Upload supaya board menjalankan.</li>
<li><strong>Nama folder ≠ nama .ino.</strong> Arduino rewel — samakan keduanya.</li>
</ul>

<h2>Selanjutnya</h2>
<p><strong>Intinya:</strong> kalau kamu bisa menjelaskan setup vs loop, menyimpan <code>FS11_hello</code> di sketchbook, dan melihat <em>Done uploading</em>, FS-11 selesai.</p>
<p>Lanjut ke <strong>FS-12</strong> (variabel + cetak ke Serial) saat modulnya terbit — di sana Serial Monitor jadi “mata” kita.</p>
<p>Daftar modul lengkap: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $ide = $this->ideOverviewFigureEn();
        $board = $this->boardFigureEn();
        $recipe = $this->recipeSvgEn();
        $flow = $this->flowSvgEn();
        $book = $this->sketchbookSvgEn();
        $verify = $this->verifyFigureEn();
        $upload = $this->uploadFigureEn();

        return <<<HTML
<h2>Introduction — why call it a “recipe”?</h2>
<p>This article is <strong>#81 (this article)</strong> · module <strong>FS-11</strong> on the path <em>Full Stack IoT Developer — From Zero</em>. In <strong>FS-06</strong> your PC + Arduino IDE could already upload. In <strong>FS-10</strong> you learned HIGH/LOW signal language. Today we focus on the mental model: <strong>a sketch = a written recipe</strong>, then tell <code>setup()</code> from <code>loop()</code>, plus a save habit so you do not overwrite work.</p>
<p><strong>Analogy:</strong> a sketch is like a cooking recipe. <code>setup()</code> = prep the kitchen once. <code>loop()</code> = repeat the same cooking steps. The board = the kitchen that runs the recipe after you Upload.</p>
<p><strong>Prerequisites:</strong> FS-06 (Arduino IDE, ESP32 Dev Module board, COM/tty port, at least one <em>Done uploading</em>) + FS-10 (HIGH/LOW literacy — new button wiring is not required today).</p>
<p><strong>How to use this article (work order):</strong></p>
<ol>
<li><strong>Open the tools</strong> in Preparation — Arduino IDE first; do not plug USB yet if you are still organizing files.</li>
<li><strong>Read + look at the figures</strong> until setup vs loop and Verify vs Upload are clear.</li>
<li><strong>Create the</strong> <code>FS11_hello</code> <strong>sketch</strong> in the sketchbook (practice steps).</li>
<li><strong>Verify → Upload</strong> in the IDE — target <em>Done uploading</em>.</li>
<li><strong>Tick the 10/10 checklist</strong> in the browser.</li>
</ol>
<p><strong>Not needed today:</strong> Serial Monitor (that is FS-12/FS-13), <code>pinMode</code> / <code>digitalRead</code> for a button, new breadboard wiring, Laragon, web project terminal, <code>php artisan</code>. Today's tools: <strong>Arduino IDE</strong> + <strong>File Explorer</strong> (to see the sketchbook folder) + ESP32 + USB data cable + <strong>browser</strong> (checklist).</p>

<h2>Preparation — open &amp; gather these first</h2>
<p><strong>Desk order:</strong> do not hit Upload immediately. Follow the order so you know <em>where</em> syntax is tested.</p>
<ol>
<li><strong>Open Arduino IDE 2.x</strong> on the computer (not the browser, not Laragon).</li>
<li><strong>Open File Explorer</strong> (Windows) or Finder (macOS) — we will check the sketchbook folder later.</li>
<li>Prepare the <strong>ESP32-DevKitC</strong> + a <strong>USB data cable</strong> (remember FS-05/FS-06).</li>
<li>In the IDE: confirm board <strong>ESP32 Dev Module</strong> + <strong>COM/tty port</strong> are selected (repeat FS-06).</li>
<li>Only then plug USB when you are ready to Upload.</li>
</ol>
<p><strong>Tools used today:</strong> Arduino IDE, File Explorer/Finder, ESP32, USB data cable, browser (article + checklist).</p>
<p><strong>Not used today:</strong> Serial Monitor as the main focus, multimeter, Laragon, <code>php artisan</code> terminal, PHP editors.</p>
{$ide}
{$board}

<h2>What is a sketch?</h2>
<p>A <strong>sketch</strong> is one program project for the board. The main file ends in <code>.ino</code> (example: <code>FS11_hello.ino</code>). It is text you type in Arduino IDE.</p>
<p><strong>In short:</strong> a sketch is not a “Windows app”. It only lives on the board after a successful <strong>Upload</strong>. Saving on the laptop alone does not change board behavior.</p>

<h2>setup() once · loop() forever</h2>
{$recipe}
<p>Every “standard” sketch has these two functions:</p>
<ul>
<li><code>void setup() { ... }</code> — runs <strong>once</strong> when the board powers on or resets.</li>
<li><code>void loop() { ... }</code> — runs <strong>over and over</strong> after <code>setup</code> finishes, until power is removed / reset again.</li>
</ul>
<p><strong>Comments</strong> = notes for humans; the machine ignores them. Start with <code>//</code> to the end of the line. Use comments so you remember intent without guessing.</p>

<h2>Verify vs Upload vs run</h2>
{$flow}
{$verify}
{$upload}
<p><strong>How to test at the desk (not in a web terminal):</strong></p>
<ol>
<li>Make sure Arduino IDE is open (see Preparation).</li>
<li>Click <strong>Verify</strong> (✓) → wait for <em>Done compiling</em> in the bottom panel.</li>
<li>Plug USB → confirm the port → click <strong>Upload</strong> (→) → wait for <em>Done uploading</em>.</li>
<li>The board then runs <code>setup</code> then <code>loop</code> by itself — that is <strong>run</strong>.</li>
</ol>
<p><strong>Tip:</strong> Upload also compiles. Verify is useful to catch typos before sending.</p>

<h2>Sketchbook folder — do not overwrite</h2>
{$book}
<p>Arduino stores projects in a <strong>sketchbook</strong> folder (often <code>Documents\Arduino</code> on Windows). Each sketch = <strong>one folder</strong> containing a <code>.ino</code> file with the <strong>same name</strong>.</p>
<p><strong>How to check in File Explorer:</strong> open <code>Documents\Arduino</code> → find folder <code>FS11_hello</code> → inside it you should see <code>FS11_hello.ino</code>. If the folder is empty or names differ, Save again from the IDE.</p>

<h2>Practice — “do nothing” sketch FS11_hello</h2>
<p>Practice goal: understand structure + succeed at Upload again. A blinking LED is not required today (you already did that in FS-06). Focus = tidy files + IDE buttons.</p>
<ol>
<li>In Arduino IDE: <strong>File → New Sketch</strong>.</li>
<li><strong>File → Save</strong> — name it <code>FS11_hello</code> (the IDE creates the folder).</li>
<li>Replace the editor contents with the code below (copy whole).</li>
<li>Click <strong>Verify</strong> → expect <em>Done compiling</em>.</li>
<li>Plug the ESP32 → pick the port → click <strong>Upload</strong> → expect <em>Done uploading</em>.</li>
<li>Open File Explorer → confirm folder <code>FS11_hello</code> exists in the sketchbook.</li>
</ol>
<pre><code class="language-cpp">// FS11_hello — Full Stack IoT module FS-11
// "Do nothing" sketch: focus on setup/loop + Upload habit.

void setup() {
  // setup() runs ONCE when the board powers on / resets
  // (no pinMode / Serial yet — that is a later module)
}

void loop() {
  // loop() repeats forever after setup finishes
  // intentionally empty: a quiet board is fine for Upload practice
}
</code></pre>
<p><strong>How to test the code above:</strong> test in <strong>Arduino IDE</strong> (Verify/Upload), not in a Laragon terminal or with <code>php artisan</code>. Success = <em>Done uploading</em> status + the file visible in File Explorer.</p>

<h2 id="fsiot-sketch-checklist">Practice — sketch &amp; Upload checklist</h2>
<p>Tick each step after you do it at the desk. Target: <strong>10/10</strong>. An interactive checklist is below; a paper version stays available.</p>
<ul id="fsiot-sketch-checklist-items">
<li>Arduino IDE 2.x is open before writing code</li>
<li>ESP32 Dev Module board + COM/tty port are selected</li>
<li>I can explain: sketch = recipe file (.ino), not a PC app</li>
<li>I can explain: setup() once, loop() forever</li>
<li>I know Verify (Done compiling) vs Upload (Done uploading)</li>
<li>Sketch saved as FS11_hello (folder + .ino same name)</li>
<li>File Explorer shows Documents\Arduino\FS11_hello\</li>
<li>Verify succeeded — Done compiling</li>
<li>Upload succeeded — Done uploading</li>
<li>I know: edit without Upload = board still runs the old program</li>
</ul>
<p><strong>How to test the checklist:</strong> complete it in the browser after IDE practice. No Laragon or <code>php artisan</code> — “testing” = IDE status + visible folder + ticks (not web syntax commands).</p>

<h2>Common mistakes</h2>
<ul>
<li><strong>Editing then forgetting Upload.</strong> The board does not read laptop files until Upload succeeds.</li>
<li><strong>Overwriting an old sketch.</strong> Saving with the same name wipes the old contents. Use a per-module name (<code>FS11_hello</code>).</li>
<li><strong>Port not selected.</strong> Upload fails even if the code is fine — repeat FS-06.</li>
<li><strong>Charge-only cable.</strong> Port missing / upload fails — swap to a data cable.</li>
<li><strong>Confused why Serial is empty.</strong> We are not using Serial Monitor today; that is expected. Serial comes in FS-12/FS-13.</li>
<li><strong>Thinking Verify means it already runs on the board.</strong> Verify only compiles. You need Upload for the board to run it.</li>
<li><strong>Folder name ≠ .ino name.</strong> Arduino is picky — make them match.</li>
</ul>

<h2>Next steps</h2>
<p><strong>In short:</strong> if you can explain setup vs loop, save <code>FS11_hello</code> in the sketchbook, and see <em>Done uploading</em>, FS-11 is done.</p>
<p>Continue to <strong>FS-12</strong> (variables + print to Serial) when that module publishes — Serial Monitor becomes our “eyes” there.</p>
<p>Full module list: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }
}
