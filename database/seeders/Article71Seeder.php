<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article71Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first()
            ?? Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $iotCat) {
            throw new \RuntimeException('User atau kategori iot-smart-device/esp32-arduino tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-apa-itu-iot';

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
                'title'              => 'Apa itu IoT? (tanpa jargon berat)',
                'title_en'           => 'What is IoT? (without heavy jargon)',
                'excerpt'            => 'FS-01 / #71: IoT dijelaskan bahasa sehari-hari — benda terhubung, beda remote biasa, preview Stasiun Ruang Belajar. Belum wiring.',
                'excerpt_en'         => 'FS-01 / #71: IoT in everyday language — connected things, vs a plain remote, Study Room Station preview. No wiring yet.',
                'body'               => $this->body(),
                'body_en'            => $this->bodyEn(),
                'status'             => 'draft',
                'is_featured'        => false,
                'published_at'       => null,
                'seo_title'          => 'Apa itu IoT? Tanpa Jargon — Full Stack IoT #71',
                'seo_title_en'       => 'What is IoT? No Heavy Jargon — Full Stack IoT #71',
                'seo_description'    => 'Belajar IoT dari nol: benda fisik terhubung, contoh rumah/sekolah, beda remote IR, preview Stasiun Ruang Belajar. Modul FS-01.',
                'seo_description_en' => 'Learn IoT from zero: connected physical things, home/school examples, vs IR remote, Study Room Station preview. Module FS-01.',
            ]
        );

        // Pre-launch B: tetap draft — jangan set published_at
        if ($article->published_at !== null || $article->status !== 'draft') {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['fullstack-iot', 'iot', 'esp32'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel #71 / FS-01 tersimpan sebagai DRAFT: '.$article->title);
    }

    private function remotePhotoId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/kit-tv-remote.jpg" width="1200" height="675" alt="Remote TV infrared LG — contoh kendali lokal tanpa internet" loading="lazy" style="width:100%;height:auto;max-height:360px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Remote TV infrared (IR)</strong> — kamu tekan tombol di dekat TV. Tidak ada laporan ke HP dari luar rumah.
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:LG_IR_TV_Remote_Control_AKB74595401.jpg" rel="noopener noreferrer" target="_blank">Slcreza — LG IR TV Remote Control AKB74595401</a> · Wikimedia Commons.
  </figcaption>
</figure>
HTML;
    }

    private function remotePhotoEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/kit-tv-remote.jpg" width="1200" height="675" alt="LG infrared TV remote — example of local control without the internet" loading="lazy" style="width:100%;height:auto;max-height:360px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Infrared (IR) TV remote</strong> — you press buttons near the TV. No report back to your phone from outside the house.
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:LG_IR_TV_Remote_Control_AKB74595401.jpg" rel="noopener noreferrer" target="_blank">Slcreza — LG IR TV Remote Control AKB74595401</a> · Wikimedia Commons.
  </figcaption>
</figure>
HTML;
    }

    private function smartBulbsPhotoId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/kit-smart-bulbs.jpg" width="554" height="1200" alt="Lampu pintar yang bisa dikendalikan lewat jaringan — contoh produk IoT di rumah" loading="lazy" style="width:100%;height:auto;max-height:420px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Contoh produk IoT di rumah:</strong> lampu pintar yang bisa dikendalikan dari HP (bukan hanya saklar dinding). Kita tidak memakai merek ini di jalur — hanya sebagai gambaran “benda terhubung”.
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:LIFX_bulbs.jpg" rel="noopener noreferrer" target="_blank">AG20044018 — LIFX bulbs</a> · Wikimedia Commons (CC BY-SA 4.0).
  </figcaption>
</figure>
HTML;
    }

    private function smartBulbsPhotoEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/kit-smart-bulbs.jpg" width="554" height="1200" alt="Smart bulbs controlled over a network — example of a home IoT product" loading="lazy" style="width:100%;height:auto;max-height:420px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Home IoT product example:</strong> smart bulbs you can control from a phone (not only a wall switch). We do not use this brand on the path — it is only a “connected thing” picture.
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:LIFX_bulbs.jpg" rel="noopener noreferrer" target="_blank">AG20044018 — LIFX bulbs</a> · Wikimedia Commons (CC BY-SA 4.0).
  </figcaption>
</figure>
HTML;
    }

    private function smartPlugsPhotoId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/kit-smart-plugs.jpg" width="1200" height="900" alt="Stop kontak pintar dan saklar pintar — contoh benda rumah yang bisa dikontrol dari jauh" loading="lazy" style="width:100%;height:auto;max-height:360px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Contoh lain:</strong> stop kontak / saklar pintar — benda fisik di rumah yang statusnya bisa dipantau atau dikontrol dari aplikasi.
    <br>Sumber gambar: <a href="https://commons.wikimedia.org/wiki/File:Wemo_smart_plugs_and_switches.jpg" rel="noopener noreferrer" target="_blank">Harborsparrow — Wemo smart plugs and switches</a> · Wikimedia Commons.
  </figcaption>
</figure>
HTML;
    }

    private function smartPlugsPhotoEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/kit-smart-plugs.jpg" width="1200" height="900" alt="Smart plugs and smart switches — home devices you can control from afar" loading="lazy" style="width:100%;height:auto;max-height:360px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Another example:</strong> smart plugs / switches — physical home devices whose state you can monitor or control from an app.
    <br>Image source: <a href="https://commons.wikimedia.org/wiki/File:Wemo_smart_plugs_and_switches.jpg" rel="noopener noreferrer" target="_blank">Harborsparrow — Wemo smart plugs and switches</a> · Wikimedia Commons.
  </figcaption>
</figure>
HTML;
    }

    private function remoteSvgId(): string
    {
        return <<<'HTML'
<figure role="img" aria-label="Perbandingan remote IR lokal versus jalur IoT jarak jauh" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 760 260" width="100%" height="auto" role="img" aria-label="Remote lokal versus IoT">
  <text x="380" y="28" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700">Remote lokal vs arah IoT</text>
  <text x="40" y="58" font-family="system-ui,sans-serif" font-size="12" font-weight="700" fill="#4A5568">Atas — lokal (IR)</text>
  <rect x="40" y="70" width="150" height="50" rx="6" fill="#fff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="115" y="100" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">Remote IR</text>
  <text x="210" y="100" font-family="system-ui,sans-serif" font-size="18">→</text>
  <rect x="240" y="70" width="150" height="50" rx="6" fill="#fff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="315" y="100" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">TV di ruang</text>
  <text x="410" y="100" font-family="system-ui,sans-serif" font-size="13" fill="#4A5568">· hanya dekat · tanpa laporan ke HP</text>
  <text x="40" y="160" font-family="system-ui,sans-serif" font-size="12" font-weight="700" fill="#1565C0">Bawah — arah IoT</text>
  <rect x="40" y="172" width="160" height="50" rx="6" fill="#EBF4FF" stroke="#1a1a1a" stroke-width="2"/>
  <text x="120" y="202" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">Sensor / board</text>
  <text x="220" y="202" font-family="system-ui,sans-serif" font-size="18">→</text>
  <rect x="250" y="172" width="170" height="50" rx="6" fill="#EBF4FF" stroke="#1a1a1a" stroke-width="2"/>
  <text x="335" y="202" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">Jaringan / server</text>
  <text x="440" y="202" font-family="system-ui,sans-serif" font-size="18">→</text>
  <rect x="470" y="172" width="250" height="50" rx="6" fill="#EBF4FF" stroke="#1a1a1a" stroke-width="2"/>
  <text x="595" y="202" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">Dashboard / HP kamu</text>
  <text x="380" y="250" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">Detail teknis belakangan · Buatan Koding Indonesia</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Atas: remote lokal. Bawah: arah IoT yang kita pelajari di jalur ini.</figcaption>
</figure>
HTML;
    }

    private function remoteSvgEn(): string
    {
        return <<<'HTML'
<figure role="img" aria-label="Comparing a local IR remote versus a long-distance IoT path" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 760 260" width="100%" height="auto" role="img" aria-label="Local remote versus IoT">
  <text x="380" y="28" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700">Local remote vs IoT direction</text>
  <text x="40" y="58" font-family="system-ui,sans-serif" font-size="12" font-weight="700" fill="#4A5568">Top — local (IR)</text>
  <rect x="40" y="70" width="150" height="50" rx="6" fill="#fff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="115" y="100" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">IR remote</text>
  <text x="210" y="100" font-family="system-ui,sans-serif" font-size="18">→</text>
  <rect x="240" y="70" width="150" height="50" rx="6" fill="#fff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="315" y="100" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">TV in room</text>
  <text x="410" y="100" font-family="system-ui,sans-serif" font-size="13" fill="#4A5568">· nearby only · no phone report</text>
  <text x="40" y="160" font-family="system-ui,sans-serif" font-size="12" font-weight="700" fill="#1565C0">Bottom — IoT direction</text>
  <rect x="40" y="172" width="160" height="50" rx="6" fill="#EBF4FF" stroke="#1a1a1a" stroke-width="2"/>
  <text x="120" y="202" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">Sensor / board</text>
  <text x="220" y="202" font-family="system-ui,sans-serif" font-size="18">→</text>
  <rect x="250" y="172" width="170" height="50" rx="6" fill="#EBF4FF" stroke="#1a1a1a" stroke-width="2"/>
  <text x="335" y="202" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">Network / server</text>
  <text x="440" y="202" font-family="system-ui,sans-serif" font-size="18">→</text>
  <rect x="470" y="172" width="250" height="50" rx="6" fill="#EBF4FF" stroke="#1a1a1a" stroke-width="2"/>
  <text x="595" y="202" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">Dashboard / your phone</text>
  <text x="380" y="250" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">Technical detail later · by Koding Indonesia</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Top: local remote. Bottom: IoT direction on this path.</figcaption>
</figure>
HTML;
    }

    private function stationSvgId(): string
    {
        return <<<'HTML'
<figure role="img" aria-label="Sketsa Stasiun Ruang Belajar: meja, sensor, lampu, dan HP" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 760 230" width="100%" height="auto" role="img" aria-label="Sketsa Stasiun Ruang Belajar">
  <text x="380" y="28" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700">Stasiun Ruang Belajar — arah proyek</text>
  <rect x="40" y="140" width="300" height="44" rx="6" fill="#fff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="190" y="168" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">Meja belajar</text>
  <rect x="60" y="55" width="110" height="55" rx="6" fill="#EBF4FF" stroke="#1a1a1a" stroke-width="2"/>
  <text x="115" y="88" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">Sensor</text>
  <rect x="200" y="55" width="110" height="55" rx="6" fill="#FFF3E0" stroke="#1a1a1a" stroke-width="2"/>
  <text x="255" y="88" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">Lampu</text>
  <text x="360" y="90" font-family="system-ui,sans-serif" font-size="22">→</text>
  <rect x="400" y="50" width="140" height="70" rx="6" fill="#EBF4FF" stroke="#1a1a1a" stroke-width="2"/>
  <text x="470" y="80" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">Board</text>
  <text x="470" y="100" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">nanti: DevKitC-1</text>
  <text x="560" y="90" font-family="system-ui,sans-serif" font-size="22">→</text>
  <rect x="590" y="50" width="140" height="70" rx="6" fill="#fff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="660" y="80" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">HP /</text>
  <text x="660" y="100" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">dashboard</text>
  <text x="380" y="215" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">Bukan wiring — hanya peta arah · Buatan Koding Indonesia</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Sketsa arah proyek (bukan wiring). Detail komponen datang pelan di modul berikutnya.</figcaption>
</figure>
HTML;
    }

    private function stationSvgEn(): string
    {
        return <<<'HTML'
<figure role="img" aria-label="Sketch of Study Room Station: desk, sensor, lamp, and phone" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 760 230" width="100%" height="auto" role="img" aria-label="Study Room Station sketch">
  <text x="380" y="28" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700">Study Room Station — project direction</text>
  <rect x="40" y="140" width="300" height="44" rx="6" fill="#fff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="190" y="168" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">Study desk</text>
  <rect x="60" y="55" width="110" height="55" rx="6" fill="#EBF4FF" stroke="#1a1a1a" stroke-width="2"/>
  <text x="115" y="88" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">Sensor</text>
  <rect x="200" y="55" width="110" height="55" rx="6" fill="#FFF3E0" stroke="#1a1a1a" stroke-width="2"/>
  <text x="255" y="88" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">Lamp</text>
  <text x="360" y="90" font-family="system-ui,sans-serif" font-size="22">→</text>
  <rect x="400" y="50" width="140" height="70" rx="6" fill="#EBF4FF" stroke="#1a1a1a" stroke-width="2"/>
  <text x="470" y="80" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">Board</text>
  <text x="470" y="100" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">later: DevKitC-1</text>
  <text x="560" y="90" font-family="system-ui,sans-serif" font-size="22">→</text>
  <rect x="590" y="50" width="140" height="70" rx="6" fill="#fff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="660" y="80" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">Phone /</text>
  <text x="660" y="100" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700">dashboard</text>
  <text x="380" y="215" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">Not wiring — direction only · by Koding Indonesia</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Project direction sketch (not wiring). Component detail comes slowly in later modules.</figcaption>
</figure>
HTML;
    }

    private function boardFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/esp32-devkitc-overview.jpg" width="1200" height="519" alt="Foto overview board ESP32-DevKitC dari dokumentasi Espressif dengan penanda bagian 1 sampai 7" loading="lazy" style="width:100%;height:auto;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    Overview board keluarga <strong>ESP32-DevKitC</strong> (sama keluarga dengan DevKitC-1 yang kita pakai). Label Inggris di gambar = dari dokumen resmi; baca arti awam di tabel di bawah. Pin belum perlu dihafal.
    <br>Sumber gambar: <a href="https://docs.espressif.com/projects/esp-dev-kits/en/latest/esp32/esp32-devkitc/user_guide.html" rel="noopener noreferrer" target="_blank">Espressif Systems — ESP32-DevKitC User Guide</a> (dokumen resmi).
  </figcaption>
</figure>
<table style="width:100%;border-collapse:collapse;margin:1rem 0;font-size:0.95rem">
<thead>
<tr style="background:#F5F5F0;border:2px solid #1a1a1a">
<th style="padding:0.5rem;border:1px solid #1a1a1a;text-align:left">No.</th>
<th style="padding:0.5rem;border:1px solid #1a1a1a;text-align:left">Label di foto (EN)</th>
<th style="padding:0.5rem;border:1px solid #1a1a1a;text-align:left">Arti awam</th>
</tr>
</thead>
<tbody>
<tr><td style="padding:0.5rem;border:1px solid #ccc">1</td><td style="padding:0.5rem;border:1px solid #ccc">5V Power On LED</td><td style="padding:0.5rem;border:1px solid #ccc">Lampu kecil tanda board mendapat daya</td></tr>
<tr><td style="padding:0.5rem;border:1px solid #ccc">2</td><td style="padding:0.5rem;border:1px solid #ccc">I/O Connector</td><td style="padding:0.5rem;border:1px solid #ccc">Deretan pin di tepi — nanti untuk kabel sensor/aktuator</td></tr>
<tr><td style="padding:0.5rem;border:1px solid #ccc">3</td><td style="padding:0.5rem;border:1px solid #ccc">ESP32-WROOM-…</td><td style="padding:0.5rem;border:1px solid #ccc">Modul “otak” + Wi‑Fi (perisai perak)</td></tr>
<tr><td style="padding:0.5rem;border:1px solid #ccc">4</td><td style="padding:0.5rem;border:1px solid #ccc">USB-to-UART Bridge</td><td style="padding:0.5rem;border:1px solid #ccc">Chip kecil yang menerjemahkan USB ↔ data serial</td></tr>
<tr><td style="padding:0.5rem;border:1px solid #ccc">5</td><td style="padding:0.5rem;border:1px solid #ccc">Boot Button</td><td style="padding:0.5rem;border:1px solid #ccc">Tombol Boot (nanti untuk mode unggah program)</td></tr>
<tr><td style="padding:0.5rem;border:1px solid #ccc">6</td><td style="padding:0.5rem;border:1px solid #ccc">USB-to-UART Port</td><td style="padding:0.5rem;border:1px solid #ccc">Port USB ke komputer — di board kamu bisa Micro-USB atau USB-C</td></tr>
<tr><td style="padding:0.5rem;border:1px solid #ccc">7</td><td style="padding:0.5rem;border:1px solid #ccc">EN Button</td><td style="padding:0.5rem;border:1px solid #ccc">Tombol EN / reset (mengulang board)</td></tr>
</tbody>
</table>
HTML;
    }

    private function boardFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/esp32-devkitc-overview.jpg" width="1200" height="519" alt="ESP32-DevKitC board overview photo from Espressif documentation with labels 1 to 7" loading="lazy" style="width:100%;height:auto;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    Overview of the <strong>ESP32-DevKitC</strong> board family (same family as the DevKitC-1 we use). English labels come from the official docs; read the beginner meanings in the table below. You do not need to memorize pins today.
    <br>Image source: <a href="https://docs.espressif.com/projects/esp-dev-kits/en/latest/esp32/esp32-devkitc/user_guide.html" rel="noopener noreferrer" target="_blank">Espressif Systems — ESP32-DevKitC User Guide</a> (official docs).
  </figcaption>
</figure>
<table style="width:100%;border-collapse:collapse;margin:1rem 0;font-size:0.95rem">
<thead>
<tr style="background:#F5F5F0;border:2px solid #1a1a1a">
<th style="padding:0.5rem;border:1px solid #1a1a1a;text-align:left">No.</th>
<th style="padding:0.5rem;border:1px solid #1a1a1a;text-align:left">Label in photo (EN)</th>
<th style="padding:0.5rem;border:1px solid #1a1a1a;text-align:left">Beginner meaning</th>
</tr>
</thead>
<tbody>
<tr><td style="padding:0.5rem;border:1px solid #ccc">1</td><td style="padding:0.5rem;border:1px solid #ccc">5V Power On LED</td><td style="padding:0.5rem;border:1px solid #ccc">Tiny light that shows the board has power</td></tr>
<tr><td style="padding:0.5rem;border:1px solid #ccc">2</td><td style="padding:0.5rem;border:1px solid #ccc">I/O Connector</td><td style="padding:0.5rem;border:1px solid #ccc">Pin rows on the edge — later for sensor/actuator wires</td></tr>
<tr><td style="padding:0.5rem;border:1px solid #ccc">3</td><td style="padding:0.5rem;border:1px solid #ccc">ESP32-WROOM-…</td><td style="padding:0.5rem;border:1px solid #ccc">“Brain” module + Wi‑Fi (silver shield)</td></tr>
<tr><td style="padding:0.5rem;border:1px solid #ccc">4</td><td style="padding:0.5rem;border:1px solid #ccc">USB-to-UART Bridge</td><td style="padding:0.5rem;border:1px solid #ccc">Small chip that translates USB ↔ serial data</td></tr>
<tr><td style="padding:0.5rem;border:1px solid #ccc">5</td><td style="padding:0.5rem;border:1px solid #ccc">Boot Button</td><td style="padding:0.5rem;border:1px solid #ccc">Boot button (later used for upload mode)</td></tr>
<tr><td style="padding:0.5rem;border:1px solid #ccc">6</td><td style="padding:0.5rem;border:1px solid #ccc">USB-to-UART Port</td><td style="padding:0.5rem;border:1px solid #ccc">USB port to the computer — your board may be Micro-USB or USB-C</td></tr>
<tr><td style="padding:0.5rem;border:1px solid #ccc">7</td><td style="padding:0.5rem;border:1px solid #ccc">EN Button</td><td style="padding:0.5rem;border:1px solid #ccc">EN / reset button (restarts the board)</td></tr>
</tbody>
</table>
HTML;
    }

    private function body(): string
    {
        $remotePhoto = $this->remotePhotoId();
        $bulbs = $this->smartBulbsPhotoId();
        $plugs = $this->smartPlugsPhotoId();
        $remoteSvg = $this->remoteSvgId();
        $station = $this->stationSvgId();
        $board = $this->boardFigureId();

        return <<<HTML
<h2>Pendahuluan — langkah pertama jalur Full Stack IoT</h2>
<p>Artikel ini adalah <strong>#71 (ini)</strong> · modul <strong>FS-01</strong> di jalur <strong>Full Stack IoT Developer — Dari Nol</strong>. Belum ada kabel, belum ada kode, belum ada unduhan perangkat lunak. Hari ini kamu hanya butuh satu pertanyaan: <em>IoT itu apa, kalau dijelaskan ke teman yang tidak suka istilah teknis?</em></p>
<p><strong>Awam:</strong> bayangkan kamu menjelaskan ke ibu atau adik di ruang tamu. Kalau mereka mengangguk dalam satu menit, kamu sudah lulus modul ini.</p>
<blockquote>
  <p><strong>Prasyarat:</strong> tidak ada. Modul ini pintu masuk jalur. Langkah berikutnya adalah <strong>FS-02</strong> (satu gambar seluruh jalur) — masih tanpa kabel atau kode.</p>
</blockquote>

<p><strong>Awam — cara pakai artikel ini (urutan baca):</strong></p>
<ol>
<li><strong>Buka browser</strong> — baca di laptop atau HP (Chrome, Edge, Firefox, atau browser bawaan).</li>
<li><strong>Siapkan catatan</strong> — kertas, Notepad, Google Docs, atau catatan HP.</li>
<li><strong>Baca konsep</strong> — definisi IoT, beda Wi‑Fi, remote vs IoT, preview Stasiun Ruang Belajar.</li>
<li><strong>Lihat foto &amp; diagram</strong> — kenalan bentuk board; pin belum dihafal.</li>
<li><strong>Latihan 3 contoh</strong> — tulis di catatan, lalu bacakan ke orang non-teknis.</li>
</ol>
<p><strong>Tidak perlu hari ini:</strong> Laragon, Arduino IDE, terminal, USB board, <code>php artisan</code>, unggah sketch.</p>

<h2>Persiapan — alat yang kamu buka hari ini</h2>
<p><strong>Alat yang dipakai di artikel ini:</strong></p>
<ul>
  <li><strong>Browser</strong> — membaca artikel ini.</li>
  <li><strong>Catatan</strong> — untuk latihan 3 contoh IoT.</li>
</ul>
<p><strong>Tidak ada perintah sintaks hari ini.</strong> Tidak ada baris kode untuk disalin, tidak ada <code>php artisan</code>, tidak ada Arduino sketch, tidak ada unduhan perangkat lunak. Cara “menguji” di FS-01 = <em>menjelaskan dengan kata-katamu sendiri</em>, bukan menjalankan program.</p>
<p>Kalau kamu sudah punya board di laci, biarkan saja — kita buka kotaknya di modul belakangan.</p>

<h2>IoT dalam bahasa sehari-hari</h2>
<p><strong>IoT</strong> kepanjangan dari <em>Internet of Things</em> (internet untuk benda). Intinya sederhana:</p>
<ul>
  <li>Ada <strong>benda fisik</strong> di dunia nyata (lampu, kipas, sensor suhu, pintu, pot tanaman).</li>
  <li>Benda itu bisa <strong>saling terhubung</strong> (sering lewat Wi‑Fi, tapi bukan wajib di setiap langkah belajar).</li>
  <li>Kamu bisa <strong>memantau</strong> atau <strong>mengendalikan</strong>nya dari jauh — lewat HP, laptop, atau dashboard.</li>
</ul>
<p><strong>Awam:</strong> IoT = benda di meja/rumah yang “bisa bicara” ke sistem, supaya kamu tahu keadaannya atau bisa menyuruhnya bergerak tanpa harus berdiri di sampingnya.</p>
{$bulbs}

<h2>Bukan sekadar “ada Wi‑Fi”</h2>
<p>Banyak orang mengira: “kalau sudah Wi‑Fi, itu IoT.” Belum tentu.</p>
<p>Wi‑Fi hanyalah salah satu <strong>jalan</strong> data. Yang membuat sesuatu “IoT” adalah rangkaian: <strong>benda → data/perintah → sistem yang menyimpan/menampilkan → kamu yang membaca atau mengontrol</strong>.</p>
<p>Contoh: printer di kantor yang hanya dilayani kabel lokal, tanpa pemantauan dari luar, biasanya bukan yang kita maksud sebagai proyek IoT di jalur ini. Sebaliknya, sensor suhu di ruang belajar yang datanya muncul di dashboard HP-mu — itu arah IoT yang kita kejar.</p>
<p><strong>Awam:</strong> Wi‑Fi seperti jalan raya. IoT adalah “mobil + penumpang + tujuan”. Jalan saja belum cukup kalau tidak ada yang pergi ke suatu tempat.</p>

<h2>Contoh sederhana di sekitar kita</h2>
<table>
  <thead>
    <tr>
      <th>Tempat</th>
      <th>Contoh awam</th>
      <th>Yang dipantau / dikontrol</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Rumah</td>
      <td>Lampu yang bisa dinyalakan dari HP saat kamu masih di luar</td>
      <td>Nyala/mati lampu</td>
    </tr>
    <tr>
      <td>Sekolah / ruang belajar</td>
      <td>Sensor suhu &amp; cahaya di meja belajar</td>
      <td>Angka suhu, terang/gelap, peringatan kalau panas</td>
    </tr>
    <tr>
      <td>Pertanian kecil</td>
      <td>Sensor kelembapan tanah di pot</td>
      <td>Kapan tanah kering (nanti bisa memicu pompa)</td>
    </tr>
  </tbody>
</table>
{$plugs}
<p>Di jalur ini kita tidak mulai dari pabrik raksasa. Kita mulai dari <strong>Stasiun Ruang Belajar</strong> — proyek benang merah yang akan kamu bangun pelan-pelan.</p>

<h2>Remote TV biasa vs sistem IoT</h2>
<p>Remote infrared (IR) di TV: kamu tekan tombol, sinyal cahaya tak terlihat menuju TV. Kalau kamu keluar rumah, remote itu tidak berguna dari jarak jauh lewat internet.</p>
{$remotePhoto}
<p>Sistem IoT yang kita bayangkan: sensor atau saklar di perangkat, data/perintah bisa lewat jaringan, dan kamu melihat status di aplikasi atau dashboard — bahkan saat tidak berada di ruangan yang sama.</p>
<p><strong>Awam:</strong> remote TV = “bicara langsung di depan pintu”. IoT = “kirim pesan ke rumah, lalu rumah melaporkan balik ke kamu.”</p>
{$remoteSvg}

<h2>Preview proyek: Stasiun Ruang Belajar</h2>
<p>Seluruh jalur mengitari satu proyek: <strong>Stasiun Ruang Belajar</strong>. Nanti (modul-modul berikutnya) kamu akan:</p>
<ul>
  <li>membaca <strong>suhu</strong> dan <strong>cahaya</strong> di meja belajar,</li>
  <li>mengendalikan <strong>lampu</strong> lewat saklar listrik kecil (relay) dari sistem,</li>
  <li>melihat data di <strong>dashboard</strong> (layar ringkas di HP/laptop),</li>
  <li>mendapat <strong>peringatan</strong> bila kondisi aneh,</li>
  <li>belajar tetap aman saat Wi‑Fi putus.</li>
</ul>
<p>Hari ini cukup tahu namanya. Belum merakit apa pun.</p>
{$station}
<p><strong>Awam:</strong> Stasiun Ruang Belajar = “mini cuaca + saklar pintar di meja belajar” yang kita bangun setahap demi setahap.</p>

<h2>Board yang nanti kita pakai</h2>
<p>Di jalur ini board resmi kita adalah <strong>ESP32-DevKitC-1</strong> — papan kecil yang mudah dicari di toko lokal maupun luar negeri. Di FS-01 kamu <strong>belum</strong> menyentuh pin, kabel, atau unduhan perangkat lunak. Cukup kenalan bentuknya dulu.</p>
{$board}
<p><strong>Awam:</strong> DevKitC-1 seperti “otak kecil” di breadboard. Kita kenalan dulu lewat foto &amp; nama, baru nanti wiring. Jangan panik kalau port USB boardmu beda bentuk — yang penting label silkscreen (tulisan di papan) mirip.</p>

<h2>Istilah mini untuk FS-01</h2>
<table>
  <thead>
    <tr>
      <th>Istilah</th>
      <th>Arti awam</th>
      <th>Catatan</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>IoT</td>
      <td>Benda fisik terhubung yang bisa dipantau/dikendalikan dari jauh</td>
      <td>Fokus hari ini</td>
    </tr>
    <tr>
      <td>Sensor</td>
      <td>“Indra” yang mengukur sesuatu (suhu, cahaya, gerak)</td>
      <td>Dipakai di Stasiun Ruang Belajar nanti</td>
    </tr>
    <tr>
      <td>Aktuator / relay</td>
      <td>“Otot” yang bergerak; relay = saklar listrik kecil yang dikendalikan sistem</td>
      <td>Nanti: lampu di meja belajar</td>
    </tr>
    <tr>
      <td>Dashboard</td>
      <td>Layar ringkas yang menampilkan data</td>
      <td>Fase belakangan</td>
    </tr>
    <tr>
      <td>ESP32-DevKitC-1</td>
      <td>Board resmi jalur ini (papan “otak” kecil)</td>
      <td>Belum wiring di FS-01</td>
    </tr>
  </tbody>
</table>

<h2>Praktik — tulis 3 contoh IoT di sekitarmu</h2>
<p><strong>Awam — buka alat ini dulu:</strong> catatan (kertas / Notepad / catatan HP). Tidak perlu terminal atau Arduino IDE.</p>
<ol>
  <li>Buka catatan.</li>
  <li>Tulis tiga benda di rumah/sekolah yang <em>bisa</em> jadi IoT (atau sudah IoT).</li>
  <li>Untuk tiap benda, tulis satu kalimat: apa yang dipantau atau dikontrol.</li>
</ol>
<p>Contoh jawaban (boleh beda): (1) lampu kamar — nyala/mati dari HP, (2) sensor asap — peringatan ke HP, (3) pot tanaman — kelembapan tanah.</p>
<p><strong>Awam — cara menguji bagian ini (tanpa komputer khusus):</strong> bacakan tiga contohmu ke orang non-teknis. Kalau mereka paham tanpa kamu menjelaskan Wi‑Fi atau cloud, latihanmu lolos. Tidak perlu menjalankan perintah apa pun.</p>

<h2 id="fsiot-iot-checklist">Praktik — checklist lulus FS-01</h2>
<p>Centang setelah kamu lakukan. Target: <strong>5/5</strong>.</p>
<ul id="fsiot-iot-checklist-items">
<li>Sudah baca bagian “Tidak ada perintah sintaks hari ini”</li>
<li>Bisa jelaskan IoT ke orang non-teknis dalam ≈1 menit</li>
<li>Paham beda remote lokal vs arah IoT</li>
<li>Tahu nama proyek <strong>Stasiun Ruang Belajar</strong></li>
<li>Sudah menulis 3 contoh IoT di catatan</li>
</ul>

<h2>Kesalahan umum awam</h2>
<ol>
  <li><strong>Mengira IoT = robot.</strong> Robot bisa memakai ide IoT, tapi IoT lebih luas: sensor + kontrol jarak jauh juga IoT.</li>
  <li><strong>Mengira harus cloud mahal dari hari pertama.</strong> Jalur kita mulai lokal dan bertahap; cloud bukan syarat FS-01.</li>
  <li><strong>Mengira harus bisa coding dulu.</strong> FS-01 tidak menulis kode. Coding datang pelan setelah fondasi.</li>
  <li><strong>Mengira “sudah Wi‑Fi” = sudah IoT.</strong> Lihat bagian “bukan sekadar Wi‑Fi” di atas.</li>
  <li><strong>Langsung beli banyak komponen tanpa peta.</strong> Tunggu modul kit; hari ini cukup paham konsep.</li>
  <li><strong>Mencampur jalur lama ESP32 sebagai prasyarat.</strong> Jalur Full Stack IoT ini mandiri dari nol — ikuti FS-01 → FS-… berurutan. Artikel terkait di bawah halaman bisa dari topik IoT lama; itu bukan prasyarat jalur ini.</li>
  <li><strong>Panik karena label Inggris di foto board.</strong> Itu dari dokumen Espressif — pakai tabel arti awam di atas; pin belum dihafal.</li>
</ol>

<h2>Lanjut belajar</h2>
<p>Setelah FS-01, langkah alami berikutnya adalah <strong>FS-02 — satu gambar untuk seluruh jalur</strong> (peta lapisan dari benda nyata sampai dashboard). Modul itu sudah ada di jalur draft; tautan publik artikel dibuka saat rilis supaya tidak ada tautan mati untuk pengunjung.</p>
<p>Simpan juga <a href="/belajar/fullstack-iot">halaman jalur Full Stack IoT</a> sebagai pintu masuk resmi — saat rilis, materi akan terbuka berurutan.</p>

<h2>Kesimpulan</h2>
<p>Di <strong>#71 (ini)</strong> kamu sudah punya definisi IoT yang bisa dijelaskan ke orang awam, beda remote lokal vs jalur jarak jauh, dan preview <strong>Stasiun Ruang Belajar</strong>. Board resmi nanti: <strong>ESP32-DevKitC-1</strong>. Belum wiring — dan itu sengaja.</p>
<p><strong>Awam:</strong> kalau kamu bisa jawab “IoT itu apa?” tanpa mengutip jargon berat, FS-01 selesai. Lanjut ke peta jalur di FS-02 saat modulnya kamu buka di urutan belajar.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $remotePhoto = $this->remotePhotoEn();
        $bulbs = $this->smartBulbsPhotoEn();
        $plugs = $this->smartPlugsPhotoEn();
        $remoteSvg = $this->remoteSvgEn();
        $station = $this->stationSvgEn();
        $board = $this->boardFigureEn();

        return <<<HTML
<h2>Introduction — first step on the Full Stack IoT path</h2>
<p>This article is <strong>#71 (this article)</strong> · module <strong>FS-01</strong> on the <strong>Full Stack IoT Developer — From Zero</strong> path. No wires, no code, no software downloads today. You only need one question: <em>What is IoT, if you explain it to a friend who dislikes technical jargon?</em></p>
<p><strong>Beginner:</strong> imagine explaining it to a parent or sibling in the living room. If they nod within one minute, you have passed this module.</p>
<blockquote>
  <p><strong>Prerequisites:</strong> none. This module is the path entrance. The next step is <strong>FS-02</strong> (one picture of the whole path) — still without cables or code.</p>
</blockquote>

<p><strong>Beginner — how to use this article (read in order):</strong></p>
<ol>
<li><strong>Open a browser</strong> — read on a laptop or phone (Chrome, Edge, Firefox, or the built-in browser).</li>
<li><strong>Prepare notes</strong> — paper, Notepad, Google Docs, or a phone note.</li>
<li><strong>Read the concepts</strong> — IoT definition, Wi‑Fi vs IoT, remote vs IoT, Study Room Station preview.</li>
<li><strong>Look at photos &amp; diagrams</strong> — learn the board shape; do not memorize pins yet.</li>
<li><strong>Practice 3 examples</strong> — write them in your notes, then read them to a non-technical person.</li>
</ol>
<p><strong>Not needed today:</strong> Laragon, Arduino IDE, a terminal, USB board, <code>php artisan</code>, sketch upload.</p>

<h2>Preparation — tools you open today</h2>
<p><strong>Tools used in this article:</strong></p>
<ul>
  <li><strong>Browser</strong> — to read this article.</li>
  <li><strong>Notes</strong> — for the 3 IoT examples exercise.</li>
</ul>
<p><strong>There is no syntax to run today.</strong> No code lines to copy, no <code>php artisan</code>, no Arduino sketch, no software download. “Testing” in FS-01 means <em>explaining in your own words</em>, not running a program.</p>
<p>If you already have a board in a drawer, leave it there — we open the kit in a later module.</p>

<h2>IoT in everyday language</h2>
<p><strong>IoT</strong> stands for <em>Internet of Things</em>. The core idea is simple:</p>
<ul>
  <li>There is a <strong>physical thing</strong> in the real world (a lamp, fan, temperature sensor, door, plant pot).</li>
  <li>That thing can be <strong>connected</strong> (often via Wi‑Fi, but not required at every learning step).</li>
  <li>You can <strong>monitor</strong> or <strong>control</strong> it from afar — via phone, laptop, or a dashboard.</li>
</ul>
<p><strong>Beginner:</strong> IoT = something on your desk or in your home that can “talk” to a system so you know its state or can tell it to act without standing next to it.</p>
{$bulbs}

<h2>Not just “having Wi‑Fi”</h2>
<p>Many people think: “If there is Wi‑Fi, it is IoT.” Not always.</p>
<p>Wi‑Fi is only one <strong>road</strong> for data. What makes something “IoT” is the chain: <strong>thing → data/commands → a system that stores/shows them → you reading or controlling</strong>.</p>
<p>Example: an office printer only used on a local cable, with no remote monitoring, is usually not what we mean by an IoT project on this path. A study-desk temperature sensor whose readings appear on your phone dashboard — that is the IoT direction we chase.</p>
<p><strong>Beginner:</strong> Wi‑Fi is like a road. IoT is “car + passengers + destination.” A road alone is not enough if nothing is going somewhere.</p>

<h2>Simple examples around us</h2>
<table>
  <thead>
    <tr>
      <th>Place</th>
      <th>Beginner example</th>
      <th>What is monitored / controlled</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Home</td>
      <td>A lamp you can turn on from your phone while still outside</td>
      <td>Lamp on/off</td>
    </tr>
    <tr>
      <td>School / study room</td>
      <td>Temperature and light sensors on a study desk</td>
      <td>Temperature numbers, bright/dark, alert if too hot</td>
    </tr>
    <tr>
      <td>Small farming</td>
      <td>Soil moisture sensor in a pot</td>
      <td>When soil is dry (later can trigger a pump)</td>
    </tr>
  </tbody>
</table>
{$plugs}
<p>On this path we do not start with a giant factory. We start with <strong>Study Room Station</strong> — the story project you will build step by step.</p>

<h2>A plain TV remote vs an IoT system</h2>
<p>An infrared (IR) TV remote: you press a button, an invisible light signal goes to the TV. If you leave the house, that remote cannot help over the internet.</p>
{$remotePhoto}
<p>The IoT system we imagine: a sensor or switch on a device, data/commands can travel over a network, and you see status in an app or dashboard — even when you are not in the same room.</p>
<p><strong>Beginner:</strong> a TV remote = “talking right at the door.” IoT = “sending a message home, then home reports back to you.”</p>
{$remoteSvg}

<h2>Project preview: Study Room Station</h2>
<p>The whole path follows one project: <strong>Study Room Station</strong>. Later (in upcoming modules) you will:</p>
<ul>
  <li>read <strong>temperature</strong> and <strong>light</strong> on the study desk,</li>
  <li>control a <strong>lamp</strong> through a small electric switch (relay) from the system,</li>
  <li>view data on a <strong>dashboard</strong> (a simple screen on phone/laptop),</li>
  <li>get <strong>alerts</strong> when something looks wrong,</li>
  <li>learn to stay safe when Wi‑Fi drops.</li>
</ul>
<p>Today you only need the name. Do not assemble anything yet.</p>
{$station}
<p><strong>Beginner:</strong> Study Room Station = a “mini weather + smart switch on your study desk” that we build step by step.</p>

<h2>The board we will use later</h2>
<p>On this path our official board is <strong>ESP32-DevKitC-1</strong> — a small board that is easy to find in local and international stores. In FS-01 you do <strong>not</strong> touch pins, cables, or download software. Just get familiar with how it looks.</p>
{$board}
<p><strong>Beginner:</strong> DevKitC-1 is like a “small brain” on a breadboard. We learn the look and name first; wiring comes later. Do not panic if your USB port shape differs — matching silkscreen labels matters more.</p>

<h2>Mini glossary for FS-01</h2>
<table>
  <thead>
    <tr>
      <th>Term</th>
      <th>Beginner meaning</th>
      <th>Note</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>IoT</td>
      <td>Connected physical things you can monitor/control from afar</td>
      <td>Focus today</td>
    </tr>
    <tr>
      <td>Sensor</td>
      <td>“Sense” that measures something (temperature, light, motion)</td>
      <td>Used later in Study Room Station</td>
    </tr>
    <tr>
      <td>Actuator / relay</td>
      <td>“Muscle” that moves; a relay is a small electric switch controlled by the system</td>
      <td>Later: lamp on the study desk</td>
    </tr>
    <tr>
      <td>Dashboard</td>
      <td>A simple screen that shows data</td>
      <td>Later phase</td>
    </tr>
    <tr>
      <td>ESP32-DevKitC-1</td>
      <td>Official board for this path (small “brain” board)</td>
      <td>No wiring in FS-01</td>
    </tr>
  </tbody>
</table>

<h2>Practice — write 3 IoT examples around you</h2>
<p><strong>Beginner — open this tool first:</strong> notes (paper / Notepad / phone note). No terminal or Arduino IDE.</p>
<ol>
  <li>Open your notes.</li>
  <li>Write three things at home/school that <em>could</em> be IoT (or already are).</li>
  <li>For each thing, write one sentence: what is monitored or controlled.</li>
</ol>
<p>Sample answers (yours may differ): (1) bedroom lamp — on/off from phone, (2) smoke sensor — alert to phone, (3) plant pot — soil moisture.</p>
<p><strong>Beginner — how to test this part (no special computer):</strong> read your three examples to a non-technical person. If they understand without you explaining Wi‑Fi or cloud, your practice passes. You do not need to run any command.</p>

<h2 id="fsiot-iot-checklist">Practice — FS-01 pass checklist</h2>
<p>Tick each item after you do it. Target: <strong>5/5</strong>.</p>
<ul id="fsiot-iot-checklist-items">
<li>Read the “There is no syntax to run today” section</li>
<li>Can explain IoT to a non-technical person in about 1 minute</li>
<li>Understand local remote vs IoT direction</li>
<li>Know the project name <strong>Study Room Station</strong></li>
<li>Wrote 3 IoT examples in your notes</li>
</ul>

<h2>Common beginner mistakes</h2>
<ol>
  <li><strong>Thinking IoT = robot.</strong> Robots can use IoT ideas, but IoT is broader: sensors + remote control also count.</li>
  <li><strong>Thinking you need expensive cloud from day one.</strong> Our path starts local and gradual; cloud is not an FS-01 requirement.</li>
  <li><strong>Thinking you must know coding first.</strong> FS-01 writes no code. Coding comes slowly after foundations.</li>
  <li><strong>Thinking “has Wi‑Fi” = already IoT.</strong> See the “not just Wi‑Fi” section above.</li>
  <li><strong>Buying many parts without a map.</strong> Wait for the kit module; today is concepts only.</li>
  <li><strong>Mixing old ESP32 series as a prerequisite.</strong> This Full Stack IoT path is independent from zero — follow FS-01 → FS-… in order. Related articles at the bottom may come from older IoT topics; they are not prerequisites for this path.</li>
  <li><strong>Panicking about English labels on the board photo.</strong> Those come from Espressif docs — use the beginner-meaning table above; pins are not memorized yet.</li>
</ol>

<h2>Keep learning</h2>
<p>After FS-01, the natural next step is <strong>FS-02 — one picture for the whole path</strong> (layer map from real-world things to dashboard). That module already exists in the draft path; public article links open at launch so visitors do not hit dead links.</p>
<p>Also bookmark the <a href="/belajar/fullstack-iot">Full Stack IoT path page</a> as the official entrance — at launch, lessons will open in order.</p>

<h2>Conclusion</h2>
<p>In <strong>#71 (this article)</strong> you have an IoT definition you can explain to a non-technical person, the difference between a local remote and a long-distance path, and a preview of <strong>Study Room Station</strong>. Official board later: <strong>ESP32-DevKitC-1</strong>. No wiring yet — on purpose.</p>
<p><strong>Beginner:</strong> if you can answer “What is IoT?” without heavy jargon, FS-01 is done. Continue to the path map in FS-02 when you open that module in learning order.</p>
HTML;
    }
}
