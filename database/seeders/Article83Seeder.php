<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article83Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first()
            ?? Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $iotCat) {
            throw new \RuntimeException('User atau kategori iot-smart-device/esp32-arduino tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-serial-monitor-debug';

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
                'title'              => 'Serial Monitor jadi sahabat debug',
                'title_en'           => 'Serial Monitor as your debug friend',
                'excerpt'            => 'FS-13 / #83: Biasakan membaca log: baud 115200, cetak di setup vs loop, delay 1 detik agar tidak banjir teks. Buka Arduino IDE + Serial Monitor dulu.',
                'excerpt_en'         => 'FS-13 / #83: Get used to reading logs: 115200 baud, print in setup vs loop, 1-second delay so text does not flood. Open Arduino IDE + Serial Monitor first.',
                'body'               => $this->body(),
                'body_en'            => $this->bodyEn(),
                'status'             => 'draft',
                'is_featured'        => false,
                'published_at'       => null,
                'seo_title'          => 'Serial Monitor sahabat debug — Full Stack IoT #83',
                'seo_title_en'       => 'Serial Monitor as debug friend — Full Stack IoT #83',
                'seo_description'    => 'Belajar baca log Serial: baud, setup vs loop, delay 1 detik, jangan banjir teks. Modul FS-13 jalur Full Stack IoT.',
                'seo_description_en' => 'Learn to read Serial logs: baud, setup vs loop, 1-second delay, no text flood. Full Stack IoT FS-13 module.',
            ]
        );

        if ($article->published_at !== null || $article->status !== 'draft') {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['fullstack-iot', 'iot', 'esp32'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel #83 / FS-13 tersimpan sebagai DRAFT: '.$article->title);
    }

    private function boardFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/esp32-devkitc-overview.jpg" width="1200" height="800" alt="ESP32-DevKitC — board yang mengirim log Serial lewat USB" loading="eager" style="width:100%;height:auto;max-height:360px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>ESP32-DevKitC</strong> — colok <strong>kabel USB data</strong> di label <strong>(6)</strong>, Upload, lalu baca log di Serial Monitor. Tombol <strong>EN (7)</strong> = reset bila pesan “siap” terlewat. Hari ini belum sensor baru.
    <br>Sumber gambar: <a href="https://docs.espressif.com/projects/esp-dev-kits/en/latest/esp32/esp32-devkitc/user_guide.html" rel="noopener noreferrer" target="_blank">Espressif — ESP32-DevKitC user guide</a>.
  </figcaption>
</figure>
HTML;
    }

    private function boardFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/esp32-devkitc-overview.jpg" width="1200" height="800" alt="ESP32-DevKitC — the board that sends Serial logs over USB" loading="eager" style="width:100%;height:auto;max-height:360px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>ESP32-DevKitC</strong> — plug a <strong>USB data cable</strong> at label <strong>(6)</strong>, Upload, then read logs in Serial Monitor. The <strong>EN (7)</strong> button resets if you missed the “ready” line. No new sensors today.
    <br>Image source: <a href="https://docs.espressif.com/projects/esp-dev-kits/en/latest/esp32/esp32-devkitc/user_guide.html" rel="noopener noreferrer" target="_blank">Espressif — ESP32-DevKitC user guide</a>.
  </figcaption>
</figure>
HTML;
    }

    private function setupVsLoopSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Cetak di setup sekali vs di loop berulang" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 240" width="100%" height="auto" role="img" aria-label="setup vs loop">
  <text x="430" y="28" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">Kapan println muncul? setup vs loop</text>
  <rect x="40" y="50" width="360" height="160" rx="12" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="220" y="90" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700" fill="#1B5E20">setup()</text>
  <text x="220" y="120" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#33691E">Jalan SEKALI saat board mulai</text>
  <text x="220" y="148" text-anchor="middle" font-family="Consolas,monospace" font-size="12" fill="#1a1a1a">Serial.println("siap");</text>
  <text x="220" y="178" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#2E7D32">Cocok: pesan pembuka</text>
  <rect x="460" y="50" width="360" height="160" rx="12" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
  <text x="640" y="90" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700" fill="#0D47A1">loop()</text>
  <text x="640" y="120" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#1565C0">Jalan TERUS berulang</text>
  <text x="640" y="148" text-anchor="middle" font-family="Consolas,monospace" font-size="12" fill="#1a1a1a">println + delay(1000)</text>
  <text x="640" y="178" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#1565C0">Cocok: detak / status berkala</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Analogi:</strong> <code>setup</code> = ucapan selamat datang sekali. <code>loop</code> = detak jam yang berulang.
    <br>Sumber gambar: diagram buatan Koding Indonesia (FS-13).
  </figcaption>
</figure>
SVG;
    }

    private function setupVsLoopSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Print once in setup vs repeating in loop" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 240" width="100%" height="auto" role="img" aria-label="setup vs loop">
  <text x="430" y="28" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">When does println appear? setup vs loop</text>
  <rect x="40" y="50" width="360" height="160" rx="12" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="220" y="90" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700" fill="#1B5E20">setup()</text>
  <text x="220" y="120" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#33691E">Runs ONCE when the board starts</text>
  <text x="220" y="148" text-anchor="middle" font-family="Consolas,monospace" font-size="12" fill="#1a1a1a">Serial.println("ready");</text>
  <text x="220" y="178" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#2E7D32">Good for: welcome line</text>
  <rect x="460" y="50" width="360" height="160" rx="12" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
  <text x="640" y="90" text-anchor="middle" font-family="system-ui,sans-serif" font-size="16" font-weight="700" fill="#0D47A1">loop()</text>
  <text x="640" y="120" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#1565C0">Runs AGAIN and again</text>
  <text x="640" y="148" text-anchor="middle" font-family="Consolas,monospace" font-size="12" fill="#1a1a1a">println + delay(1000)</text>
  <text x="640" y="178" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#1565C0">Good for: heartbeat / status</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Analogy:</strong> <code>setup</code> = one welcome. <code>loop</code> = a repeating clock tick.
    <br>Image source: diagram by Koding Indonesia (FS-13).
  </figcaption>
</figure>
SVG;
    }

    private function floodVsSteadySvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Log banjir tanpa delay vs detak teratur" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 280" width="100%" height="auto" role="img" aria-label="flood vs steady">
  <text x="430" y="26" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">Jangan banjir teks — pakai delay(1000) dulu</text>
  <rect x="30" y="45" width="390" height="200" rx="10" fill="#FFEBEE" stroke="#C62828" stroke-width="2.5"/>
  <text x="225" y="75" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#B71C1C">TANPA delay (banjir)</text>
  <text x="50" y="110" font-family="Consolas,monospace" font-size="12" fill="#C62828">detak</text>
  <text x="50" y="130" font-family="Consolas,monospace" font-size="12" fill="#C62828">detak</text>
  <text x="50" y="150" font-family="Consolas,monospace" font-size="12" fill="#C62828">detak</text>
  <text x="50" y="170" font-family="Consolas,monospace" font-size="12" fill="#C62828">detak detak detak...</text>
  <text x="225" y="210" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#B71C1C">Sulit dibaca / laptop berat</text>
  <rect x="440" y="45" width="390" height="200" rx="10" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="635" y="75" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#1B5E20">DENGAN delay(1000)</text>
  <text x="460" y="115" font-family="Consolas,monospace" font-size="13" fill="#2E7D32">detak #1</text>
  <text x="460" y="145" font-family="Consolas,monospace" font-size="13" fill="#2E7D32">detak #2  (1 detik kemudian)</text>
  <text x="460" y="175" font-family="Consolas,monospace" font-size="13" fill="#2E7D32">detak #3</text>
  <text x="635" y="215" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#1B5E20">Teratur — mudah dibaca</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Intinya:</strong> <code>delay(1000)</code> = jeda 1 detik (1000 milidetik). Nanti di Wi-Fi/MQTT kita ganti dengan <code>millis()</code> — untuk hari ini delay cukup.
    <br>Sumber gambar: diagram buatan Koding Indonesia (FS-13).
  </figcaption>
</figure>
SVG;
    }

    private function floodVsSteadySvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Flooded logs without delay vs steady ticks" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 280" width="100%" height="auto" role="img" aria-label="flood vs steady">
  <text x="430" y="26" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">Do not flood text — use delay(1000) for now</text>
  <rect x="30" y="45" width="390" height="200" rx="10" fill="#FFEBEE" stroke="#C62828" stroke-width="2.5"/>
  <text x="225" y="75" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#B71C1C">WITHOUT delay (flood)</text>
  <text x="50" y="110" font-family="Consolas,monospace" font-size="12" fill="#C62828">tick</text>
  <text x="50" y="130" font-family="Consolas,monospace" font-size="12" fill="#C62828">tick</text>
  <text x="50" y="150" font-family="Consolas,monospace" font-size="12" fill="#C62828">tick</text>
  <text x="50" y="170" font-family="Consolas,monospace" font-size="12" fill="#C62828">tick tick tick...</text>
  <text x="225" y="210" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#B71C1C">Hard to read / laptop struggles</text>
  <rect x="440" y="45" width="390" height="200" rx="10" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="635" y="75" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#1B5E20">WITH delay(1000)</text>
  <text x="460" y="115" font-family="Consolas,monospace" font-size="13" fill="#2E7D32">tick #1</text>
  <text x="460" y="145" font-family="Consolas,monospace" font-size="13" fill="#2E7D32">tick #2  (1 second later)</text>
  <text x="460" y="175" font-family="Consolas,monospace" font-size="13" fill="#2E7D32">tick #3</text>
  <text x="635" y="215" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#1B5E20">Steady — easy to read</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>In short:</strong> <code>delay(1000)</code> = wait 1 second (1000 milliseconds). Later with Wi-Fi/MQTT we switch to <code>millis()</code> — delay is enough today.
    <br>Image source: diagram by Koding Indonesia (FS-13).
  </figcaption>
</figure>
SVG;
    }

    private function portConflictSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Satu port COM jangan dipakai dua program" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 280" width="100%" height="auto" role="img" aria-label="port conflict">
  <text x="430" y="26" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">Satu kabel USB = satu port — jangan digandakan</text>
  <rect x="40" y="45" width="200" height="200" rx="12" fill="#FFF" stroke="#1a1a1a" stroke-width="2"/>
  <text x="140" y="120" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">ESP32</text>
  <text x="140" y="148" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">kabel USB data</text>
  <text x="140" y="172" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">(satu kabel)</text>
  <text x="265" y="150" text-anchor="middle" font-size="22" fill="#1565C0">→</text>
  <rect x="290" y="45" width="220" height="200" rx="12" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
  <text x="400" y="115" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#0D47A1">COM / tty</text>
  <text x="400" y="145" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#1565C0">satu pintu saja</text>
  <text x="400" y="175" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#1565C0">hanya satu program</text>
  <text x="535" y="110" text-anchor="middle" font-size="20" fill="#C62828">→</text>
  <text x="535" y="190" text-anchor="middle" font-size="20" fill="#2E7D32">→</text>
  <rect x="560" y="45" width="260" height="85" rx="10" fill="#FFEBEE" stroke="#C62828" stroke-width="2.5"/>
  <text x="690" y="78" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700" fill="#B71C1C">SALAH</text>
  <text x="690" y="102" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#B71C1C">IDE + tool lain bersamaan</text>
  <rect x="560" y="160" width="260" height="85" rx="10" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="690" y="193" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700" fill="#1B5E20">BENAR</text>
  <text x="690" y="217" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#1B5E20">Hanya Arduino IDE dulu</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Tips:</strong> tutup tool Serial lain (PuTTY, jendela monitor kedua) saat Upload / baca log di Arduino IDE 2.
    <br>Sumber gambar: diagram buatan Koding Indonesia (FS-13).
  </figcaption>
</figure>
SVG;
    }

    private function portConflictSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="One COM port should not be shared by two programs" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 280" width="100%" height="auto" role="img" aria-label="port conflict">
  <text x="430" y="26" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">One USB cable = one port — do not double-book it</text>
  <rect x="40" y="45" width="200" height="200" rx="12" fill="#FFF" stroke="#1a1a1a" stroke-width="2"/>
  <text x="140" y="120" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">ESP32</text>
  <text x="140" y="148" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">USB data cable</text>
  <text x="140" y="172" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">(one cable)</text>
  <text x="265" y="150" text-anchor="middle" font-size="22" fill="#1565C0">→</text>
  <rect x="290" y="45" width="220" height="200" rx="12" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
  <text x="400" y="115" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#0D47A1">COM / tty</text>
  <text x="400" y="145" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#1565C0">one door only</text>
  <text x="400" y="175" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#1565C0">one program at a time</text>
  <text x="535" y="110" text-anchor="middle" font-size="20" fill="#C62828">→</text>
  <text x="535" y="190" text-anchor="middle" font-size="20" fill="#2E7D32">→</text>
  <rect x="560" y="45" width="260" height="85" rx="10" fill="#FFEBEE" stroke="#C62828" stroke-width="2.5"/>
  <text x="690" y="78" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700" fill="#B71C1C">WRONG</text>
  <text x="690" y="102" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#B71C1C">IDE + another tool together</text>
  <rect x="560" y="160" width="260" height="85" rx="10" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="690" y="193" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700" fill="#1B5E20">RIGHT</text>
  <text x="690" y="217" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#1B5E20">Arduino IDE only for now</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Tip:</strong> close other Serial tools (PuTTY, a second monitor window) while Uploading / reading logs in Arduino IDE 2.
    <br>Image source: diagram by Koding Indonesia (FS-13).
  </figcaption>
</figure>
SVG;
    }

    private function serialPanelSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Contoh log detak di Serial Monitor baud 115200" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 340" width="100%" height="auto" role="img" aria-label="Serial Monitor detak">
  <text x="430" y="24" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">Serial Monitor (IDE 2) — contoh log detak</text>
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
  <text x="70" y="165" font-family="Consolas,monospace" font-size="14" fill="#A5D6A7">FS13_detak siap</text>
  <text x="70" y="195" font-family="Consolas,monospace" font-size="14" fill="#A5D6A7">detak #1</text>
  <text x="70" y="225" font-family="Consolas,monospace" font-size="14" fill="#A5D6A7">detak #2</text>
  <text x="70" y="255" font-family="Consolas,monospace" font-size="14" fill="#A5D6A7">detak #3</text>
  <text x="70" y="285" font-family="Consolas,monospace" font-size="14" fill="#81C784">(satu baris tiap ~1 detik)</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Intinya:</strong> buka Serial Monitor dari toolbar kanan atas IDE 2, lalu set baud <strong>115200</strong> (sama dengan kode). Timestamp opsional — fokus dulu pada teks yang terbaca.
    <br>Sumber gambar: diagram buatan Koding Indonesia (FS-13) — meniru panel IDE 2 (bukan screenshot IDE 1.x / baud 9600). Panduan resmi: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-serial-monitor/" rel="noopener noreferrer" target="_blank">Arduino Docs — Serial Monitor (IDE 2)</a>.
  </figcaption>
</figure>
SVG;
    }

    private function serialPanelSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Sample heartbeat log in Serial Monitor at 115200 baud" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 340" width="100%" height="auto" role="img" aria-label="Serial Monitor heartbeat">
  <text x="430" y="24" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">Serial Monitor (IDE 2) — sample heartbeat log</text>
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
  <text x="70" y="165" font-family="Consolas,monospace" font-size="14" fill="#A5D6A7">FS13_detak ready</text>
  <text x="70" y="195" font-family="Consolas,monospace" font-size="14" fill="#A5D6A7">tick #1</text>
  <text x="70" y="225" font-family="Consolas,monospace" font-size="14" fill="#A5D6A7">tick #2</text>
  <text x="70" y="255" font-family="Consolas,monospace" font-size="14" fill="#A5D6A7">tick #3</text>
  <text x="70" y="285" font-family="Consolas,monospace" font-size="14" fill="#81C784">(one line about every 1 second)</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>In short:</strong> open Serial Monitor from the top-right toolbar in IDE 2, then set baud <strong>115200</strong> (match the code). Timestamps are optional — readable text matters most.
    <br>Image source: diagram by Koding Indonesia (FS-13) — mimics the IDE 2 panel (not an IDE 1.x / 9600 screenshot). Official guide: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-serial-monitor/" rel="noopener noreferrer" target="_blank">Arduino Docs — Serial Monitor (IDE 2)</a>.
  </figcaption>
</figure>
SVG;
    }

    private function body(): string
    {
        $board = $this->boardFigureId();
        $setupLoop = $this->setupVsLoopSvgId();
        $flood = $this->floodVsSteadySvgId();
        $port = $this->portConflictSvgId();
        $panel = $this->serialPanelSvgId();

        return <<<HTML
<h2>Pendahuluan — kenapa Serial Monitor “sahabat”?</h2>
<p>Artikel ini adalah <strong>#83 (ini)</strong> · modul <strong>FS-13</strong> di jalur <em>Full Stack IoT Developer — Dari Nol</em>. Di <strong>FS-12</strong> kamu sudah bisa mencetak variabel sekali. Hari ini kita biasakan <strong>membaca log berulang</strong> dengan tenang — seperti mendengar detak jam, bukan hujan teks.</p>
<p><strong>Analogi:</strong> Serial Monitor = jendela ke “pikiran” board. Baud = kecepatan bicara. <code>delay</code> = jeda supaya bicara tidak saling tumpuk.</p>
<p><strong>Prasyarat:</strong> FS-12 (pernah lihat teks di Serial Monitor baud 115200).</p>
<p><strong>Cara pakai artikel ini (urutan kerja):</strong></p>
<ol>
<li><strong>Buka Arduino IDE</strong> dulu (bukan Laragon / terminal web).</li>
<li>Baca beda <code>setup</code> vs <code>loop</code> + gambar banjir vs detak.</li>
<li>Buat sketch <code>FS13_detak</code> → <strong>Upload</strong>.</li>
<li><strong>Buka Serial Monitor</strong> → baud <strong>115200</strong> → amati baris tiap ~1 detik.</li>
<li>Centang checklist 10/10 di browser.</li>
</ol>
<p><strong>Tidak perlu hari ini:</strong> sensor, <code>if</code>/<code>else</code> (itu FS-14), Wi-Fi, Laragon, <code>php artisan</code>. Tools hari ini: <strong>Arduino IDE</strong> + <strong>Serial Monitor</strong> + ESP32 + kabel USB data + <strong>browser</strong> (checklist).</p>

<h2>Persiapan — buka &amp; siapkan ini dulu</h2>
<p><strong>Urutan meja kerja:</strong> sintaks diuji di Arduino IDE + Serial Monitor — bukan di terminal PHP.</p>
<ol>
<li>Buka <strong>Arduino IDE 2.x</strong>.</li>
<li>Board <strong>ESP32 Dev Module</strong> + port COM/tty sudah dipilih.</li>
<li>Siapkan ESP32 + kabel USB data.</li>
<li>Pastikan tidak ada tool Serial lain yang membuka port yang sama.</li>
</ol>
<p><strong>Alat yang dipakai hari ini:</strong> Arduino IDE, Serial Monitor, ESP32, USB data, browser.</p>
<p><strong>Tidak dipakai hari ini:</strong> Laragon, <code>php artisan</code>, multimeter, wiring baru, PuTTY (kecuali kamu sudah mahir — hari ini cukup IDE).</p>
{$board}
{$port}

<h2>Baud, setup, dan loop — ulang singkat</h2>
{$setupLoop}
<ul>
<li><code>Serial.begin(115200);</code> — tetap di <code>setup</code>, sama seperti FS-12.</li>
<li>Pesan “siap” bisa sekali di <code>setup</code>.</li>
<li>Detak berkala di <code>loop</code> + <code>delay(1000)</code>.</li>
</ul>
<p><strong>Timestamp (opsional):</strong> di Arduino IDE 2, Serial Monitor bisa menampilkan waktu di samping baris. Kalau ada, bagus untuk latihan baca. Kalau tidak ada / belum aktif — tidak apa-apa; yang penting teks terbaca.</p>

<h2>Jangan banjir teks — delay 1 detik</h2>
{$flood}
<p><code>delay(1000)</code> menghentikan board sebentar (blocking). Untuk latihan ZERO ini aman. Nanti saat Wi-Fi/MQTT, jeda panjang bisa mengganggu — kita ganti dengan <code>millis()</code> di modul berikutnya (FS-19).</p>
<p><strong>Istilah:</strong> “banjir teks” = log terlalu cepat sehingga sulit dibaca (sering disebut <em>flood</em> di forum bahasa Inggris).</p>

<h2>Praktik — sketch FS13_detak</h2>
{$panel}
<p>Tujuan: melihat log “detak” teratur tiap ~1 detik di Serial Monitor.</p>
<ol>
<li>Arduino IDE → <strong>File → New Sketch</strong> → Save sebagai <code>FS13_detak</code>.</li>
<li>Ganti isi dengan kode di bawah (salin utuh).</li>
<li><strong>Verify</strong> → <strong>Upload</strong> → tunggu <em>Done uploading</em>.</li>
<li>Klik <strong>Open Serial Monitor</strong> (toolbar kanan atas IDE 2) → set <strong>115200</strong> (bukan 9600).</li>
<li>Amati baris <code>detak #…</code> muncul bergiliran. Tekan <strong>EN</strong> jika pesan “siap” sudah lewat.</li>
</ol>
<p>Butuh gambaran resmi tombol Serial Monitor? Lihat <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-serial-monitor/" rel="noopener noreferrer" target="_blank">Arduino Docs — Serial Monitor (IDE 2)</a> (pastikan baud tetap 115200 seperti di artikel ini).</p>
<pre><code class="language-cpp">// FS13_detak — Full Stack IoT FS-13
// Log detak tiap 1 detik di Serial Monitor (baud 115200).

int nomor = 0;

void setup() {
  Serial.begin(115200);
  delay(1000); // waktu membuka Serial Monitor
  Serial.println("FS13_detak siap");
}

void loop() {
  nomor = nomor + 1;
  Serial.print("detak #");
  Serial.println(nomor);
  delay(1000); // jeda 1 detik — jangan dihapus hari ini
}
</code></pre>
<p><strong>Cara menguji perintah di atas:</strong> uji di <strong>Arduino IDE + Serial Monitor</strong>. Sukses = baris detak terbaca dengan jarak kira-kira 1 detik, baud 115200. Bukan perintah Laragon / web server.</p>

<h2 id="fsiot-sm-checklist">Praktik — checklist Serial sahabat debug</h2>
<p>Centang setiap langkah setelah kamu lakukan di meja. Target: <strong>10/10</strong>. Ada checklist interaktif di bawah.</p>
<ul id="fsiot-sm-checklist-items">
<li>Arduino IDE sudah terbuka sebelum menulis kode</li>
<li>Board ESP32 Dev Module + port sudah dipilih</li>
<li>Tidak ada tool Serial lain yang memegang port yang sama</li>
<li>Paham: setup = sekali, loop = berulang</li>
<li>Sketch disimpan sebagai FS13_detak</li>
<li>Upload berhasil — Done uploading</li>
<li>Serial Monitor terbuka, baud = 115200</li>
<li>Pesan FS13_detak siap (atau reset EN) terbaca</li>
<li>Baris detak muncul teratur (~1 detik), bukan banjir</li>
<li>Sadar: delay(1000) sementara; millis() dibahas nanti</li>
</ul>
<p><strong>Cara menguji checklist:</strong> centang di browser setelah praktik di IDE. Tidak perlu <code>php artisan</code>.</p>

<h2>Kesalahan yang sering terjadi</h2>
<ul>
<li><strong>Baud salah.</strong> Kode 115200, dropdown 9600 → huruf acak. Samakan.</li>
<li><strong>Menyalin contoh 9600 dari internet.</strong> Ikuti kode artikel ini (115200).</li>
<li><strong>Port dipakai dua program.</strong> Tutup PuTTY / monitor lain, lalu buka lagi di IDE.</li>
<li><strong>Serial Monitor dibuka sebelum Upload selesai.</strong> Tutup dulu, Upload, buka lagi.</li>
<li><strong>Menghapus delay.</strong> Log banjir — sulit dibaca. Kembalikan <code>delay(1000)</code>.</li>
<li><strong>Menguji di terminal web.</strong> Sketch hanya jalan di board lewat IDE.</li>
<li><strong>Bingung kenapa nomor terus naik.</strong> Itu normal — <code>loop</code> memang berulang.</li>
</ul>

<h2>Selanjutnya</h2>
<p><strong>Intinya:</strong> kalau log detak terbaca rapi tiap ~1 detik di baud 115200, FS-13 selesai.</p>
<p>Lanjut ke <strong>FS-14</strong> (<code>if</code> / <code>else</code> — program bisa memilih) saat modulnya terbit.</p>
<p>Daftar modul lengkap: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $board = $this->boardFigureEn();
        $setupLoop = $this->setupVsLoopSvgEn();
        $flood = $this->floodVsSteadySvgEn();
        $port = $this->portConflictSvgEn();
        $panel = $this->serialPanelSvgEn();

        return <<<HTML
<h2>Introduction — why is Serial Monitor a “friend”?</h2>
<p>This article is <strong>#83 (this article)</strong> · module <strong>FS-13</strong> on the path <em>Full Stack IoT Developer — From Zero</em>. In <strong>FS-12</strong> you already printed variables once. Today we practice <strong>reading repeating logs calmly</strong> — like hearing a clock tick, not a rain of text.</p>
<p><strong>Analogy:</strong> Serial Monitor = a window into the board's “thoughts”. Baud = speaking speed. <code>delay</code> = a pause so words do not pile up.</p>
<p><strong>Prerequisites:</strong> FS-12 (you have seen text in Serial Monitor at 115200 baud).</p>
<p><strong>How to use this article (work order):</strong></p>
<ol>
<li><strong>Open Arduino IDE</strong> first (not Laragon / a web terminal).</li>
<li>Read setup vs loop + the flood vs steady figures.</li>
<li>Create sketch <code>FS13_detak</code> → <strong>Upload</strong>.</li>
<li><strong>Open Serial Monitor</strong> → baud <strong>115200</strong> → watch one line about every second.</li>
<li>Tick the 10/10 checklist in the browser.</li>
</ol>
<p><strong>Not needed today:</strong> sensors, <code>if</code>/<code>else</code> (that is FS-14), Wi-Fi, Laragon, <code>php artisan</code>. Today's tools: <strong>Arduino IDE</strong> + <strong>Serial Monitor</strong> + ESP32 + USB data cable + <strong>browser</strong> (checklist).</p>

<h2>Preparation — open &amp; gather these first</h2>
<p><strong>Desk order:</strong> syntax is tested in Arduino IDE + Serial Monitor — not in a PHP terminal.</p>
<ol>
<li>Open <strong>Arduino IDE 2.x</strong>.</li>
<li>Board <strong>ESP32 Dev Module</strong> + COM/tty port are selected.</li>
<li>Prepare the ESP32 + USB data cable.</li>
<li>Make sure no other Serial tool is holding the same port.</li>
</ol>
<p><strong>Tools used today:</strong> Arduino IDE, Serial Monitor, ESP32, USB data, browser.</p>
<p><strong>Not used today:</strong> Laragon, <code>php artisan</code>, multimeter, new wiring, PuTTY (unless you already know it — IDE is enough today).</p>
{$board}
{$port}

<h2>Baud, setup, and loop — short refresh</h2>
{$setupLoop}
<ul>
<li><code>Serial.begin(115200);</code> — still in <code>setup</code>, same as FS-12.</li>
<li>A “ready” line can print once in <code>setup</code>.</li>
<li>Periodic ticks live in <code>loop</code> + <code>delay(1000)</code>.</li>
</ul>
<p><strong>Timestamp (optional):</strong> Arduino IDE 2 Serial Monitor can show time beside each line. Nice for practice if enabled. If missing — that is fine; readable text matters most.</p>

<h2>Do not flood text — 1-second delay</h2>
{$flood}
<p><code>delay(1000)</code> pauses the board briefly (blocking). Safe for ZERO practice. Later with Wi-Fi/MQTT, long pauses hurt — we switch to <code>millis()</code> (FS-19).</p>
<p><strong>Term:</strong> “flood” means logs arrive so fast they are hard to read.</p>

<h2>Practice — sketch FS13_detak</h2>
{$panel}
<p>Goal: see a steady “tick” log about every second in Serial Monitor.</p>
<ol>
<li>Arduino IDE → <strong>File → New Sketch</strong> → Save as <code>FS13_detak</code>.</li>
<li>Replace contents with the code below (copy whole).</li>
<li><strong>Verify</strong> → <strong>Upload</strong> → wait for <em>Done uploading</em>.</li>
<li>Click <strong>Open Serial Monitor</strong> (top-right toolbar in IDE 2) → set <strong>115200</strong> (not 9600).</li>
<li>Watch <code>tick #…</code> lines appear in turn. Press <strong>EN</strong> if the ready line already passed.</li>
</ol>
<p>Want the official Serial Monitor button overview? See <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-serial-monitor/" rel="noopener noreferrer" target="_blank">Arduino Docs — Serial Monitor (IDE 2)</a> (keep baud at 115200 as in this article).</p>
<pre><code class="language-cpp">// FS13_detak — Full Stack IoT FS-13
// Heartbeat log every 1 second on Serial Monitor (baud 115200).

int number = 0;

void setup() {
  Serial.begin(115200);
  delay(1000); // time to open Serial Monitor
  Serial.println("FS13_detak ready");
}

void loop() {
  number = number + 1;
  Serial.print("tick #");
  Serial.println(number);
  delay(1000); // 1-second pause — keep it today
}
</code></pre>
<p><strong>How to test the code above:</strong> test in <strong>Arduino IDE + Serial Monitor</strong>. Success = readable tick lines about 1 second apart at baud 115200. Not a Laragon / web-server command.</p>

<h2 id="fsiot-sm-checklist">Practice — Serial debug-friend checklist</h2>
<p>Tick each step after you do it at the desk. Target: <strong>10/10</strong>. An interactive checklist is below.</p>
<ul id="fsiot-sm-checklist-items">
<li>Arduino IDE is open before writing code</li>
<li>ESP32 Dev Module board + port are selected</li>
<li>No other Serial tool is holding the same port</li>
<li>I understand: setup = once, loop = repeats</li>
<li>Sketch saved as FS13_detak</li>
<li>Upload succeeded — Done uploading</li>
<li>Serial Monitor open, baud = 115200</li>
<li>FS13_detak ready line readable (or after EN reset)</li>
<li>Tick lines appear steadily (~1 second), not flooded</li>
<li>I know: delay(1000) is temporary; millis() comes later</li>
</ul>
<p><strong>How to test the checklist:</strong> tick it in the browser after IDE practice. No <code>php artisan</code> required.</p>

<h2>Common mistakes</h2>
<ul>
<li><strong>Wrong baud.</strong> Code 115200, dropdown 9600 → garbage. Match both.</li>
<li><strong>Copying 9600 from the internet.</strong> Follow this article (115200).</li>
<li><strong>Port used by two programs.</strong> Close PuTTY / other monitors, then reopen in the IDE.</li>
<li><strong>Opening Serial before Upload finishes.</strong> Close it, Upload, open again.</li>
<li><strong>Removing delay.</strong> Logs flood — hard to read. Put <code>delay(1000)</code> back.</li>
<li><strong>Testing in a web terminal.</strong> Sketches only run on the board via the IDE.</li>
<li><strong>Confused why the number keeps rising.</strong> That is normal — <code>loop</code> repeats.</li>
</ul>

<h2>Next steps</h2>
<p><strong>In short:</strong> if steady tick logs read cleanly every ~1 second at 115200 baud, FS-13 is done.</p>
<p>Continue to <strong>FS-14</strong> (<code>if</code> / <code>else</code> — the program can choose) when that module publishes.</p>
<p>Full module list: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }
}
