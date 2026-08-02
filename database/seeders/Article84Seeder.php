<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article84Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first()
            ?? Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $iotCat) {
            throw new \RuntimeException('User atau kategori iot-smart-device/esp32-arduino tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-if-else';

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
                'title'              => 'Logika if/else — program bisa memilih',
                'title_en'           => 'if/else logic — the program can choose',
                'excerpt'            => 'FS-14 / #84: Kondisi true/false, perbandingan, else if, dan kesalahan = vs ==. Uji di Arduino IDE + Serial Monitor dengan suhu dummy (belum sensor).',
                'excerpt_en'         => 'FS-14 / #84: true/false conditions, comparisons, else if, and the = vs == mistake. Test in Arduino IDE + Serial Monitor with a dummy temperature (no sensor yet).',
                'body'               => $this->body(),
                'body_en'            => $this->bodyEn(),
                'status'             => 'draft',
                'is_featured'        => false,
                'published_at'       => null,
                'seo_title'          => 'Logika if/else — Full Stack IoT #84',
                'seo_title_en'       => 'if/else logic — Full Stack IoT #84',
                'seo_description'    => 'Belajar if/else dan else if: suhu dummy mencetak DINGIN/NORMAL/PANAS di Serial. Modul FS-14 jalur Full Stack IoT.',
                'seo_description_en' => 'Learn if/else and else if: a dummy temperature prints COLD/NORMAL/HOT on Serial. Full Stack IoT FS-14 module.',
            ]
        );

        if ($article->published_at !== null || $article->status !== 'draft') {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['fullstack-iot', 'iot', 'esp32'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel #84 / FS-14 tersimpan sebagai DRAFT: '.$article->title);
    }

    private function boardFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/esp32-devkitc-overview.jpg" width="1200" height="800" alt="ESP32-DevKitC — board yang memakai Serial untuk melihat hasil if/else" loading="eager" style="width:100%;height:auto;max-height:360px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>ESP32-DevKitC</strong> — colok <strong>kabel USB data</strong> di label <strong>(6)</strong>. Hari ini belum sensor suhu: angka suhu kita tulis sendiri di kode (dummy).
    <br>Sumber gambar: <a href="https://docs.espressif.com/projects/esp-dev-kits/en/latest/esp32/esp32-devkitc/user_guide.html" rel="noopener noreferrer" target="_blank">Espressif — ESP32-DevKitC user guide</a>.
  </figcaption>
</figure>
HTML;
    }

    private function boardFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/esp32-devkitc-overview.jpg" width="1200" height="800" alt="ESP32-DevKitC — the board that uses Serial to show if/else results" loading="eager" style="width:100%;height:auto;max-height:360px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>ESP32-DevKitC</strong> — plug a <strong>USB data cable</strong> at label <strong>(6)</strong>. No temperature sensor today: we write the number ourselves in code (dummy).
    <br>Image source: <a href="https://docs.espressif.com/projects/esp-dev-kits/en/latest/esp32/esp32-devkitc/user_guide.html" rel="noopener noreferrer" target="_blank">Espressif — ESP32-DevKitC user guide</a>.
  </figcaption>
</figure>
HTML;
    }

    private function decisionForkSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="if true atau false seperti simpang jalan" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 280" width="100%" height="auto" role="img" aria-label="decision fork">
  <text x="430" y="26" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">Analogi: if = simpang jalan — pilih satu arah</text>
  <rect x="300" y="50" width="260" height="60" rx="12" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
  <text x="430" y="78" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#0D47A1">Kondisi? (true / false)</text>
  <text x="430" y="98" text-anchor="middle" font-family="Consolas,monospace" font-size="12" fill="#1565C0">contoh: suhu &gt; 30</text>
  <text x="250" y="145" text-anchor="middle" font-size="20" fill="#2E7D32">↙ true</text>
  <text x="610" y="145" text-anchor="middle" font-size="20" fill="#C62828">false ↘</text>
  <rect x="80" y="165" width="280" height="80" rx="12" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="220" y="200" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#1B5E20">blok if { … }</text>
  <text x="220" y="225" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#2E7D32">jalankan hanya jika benar</text>
  <rect x="500" y="165" width="280" height="80" rx="12" fill="#FFEBEE" stroke="#C62828" stroke-width="2.5"/>
  <text x="640" y="200" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#B71C1C">blok else { … }</text>
  <text x="640" y="225" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#C62828">jalankan jika tidak benar</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Intinya:</strong> board mengecek kondisi dulu, lalu memilih satu cabang. Referensi resmi: <a href="https://www.arduino.cc/reference/en/language/structure/control-structure/if/" rel="noopener noreferrer" target="_blank">Arduino Language Reference — if</a>.
    <br>Sumber gambar: diagram buatan Koding Indonesia (FS-14).
  </figcaption>
</figure>
SVG;
    }

    private function decisionForkSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="if true or false like a road fork" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 280" width="100%" height="auto" role="img" aria-label="decision fork">
  <text x="430" y="26" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">Analogy: if = a fork in the road — pick one way</text>
  <rect x="300" y="50" width="260" height="60" rx="12" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
  <text x="430" y="78" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#0D47A1">Condition? (true / false)</text>
  <text x="430" y="98" text-anchor="middle" font-family="Consolas,monospace" font-size="12" fill="#1565C0">example: temp &gt; 30</text>
  <text x="250" y="145" text-anchor="middle" font-size="20" fill="#2E7D32">↙ true</text>
  <text x="610" y="145" text-anchor="middle" font-size="20" fill="#C62828">false ↘</text>
  <rect x="80" y="165" width="280" height="80" rx="12" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="220" y="200" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#1B5E20">if { … } block</text>
  <text x="220" y="225" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#2E7D32">runs only when true</text>
  <rect x="500" y="165" width="280" height="80" rx="12" fill="#FFEBEE" stroke="#C62828" stroke-width="2.5"/>
  <text x="640" y="200" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#B71C1C">else { … } block</text>
  <text x="640" y="225" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#C62828">runs when not true</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>In short:</strong> the board checks a condition first, then picks one branch. Official reference: <a href="https://www.arduino.cc/reference/en/language/structure/control-structure/if/" rel="noopener noreferrer" target="_blank">Arduino Language Reference — if</a>.
    <br>Image source: diagram by Koding Indonesia (FS-14).
  </figcaption>
</figure>
SVG;
    }

    private function equalVsCompareSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="beda tanda = dan ==" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 260" width="100%" height="auto" role="img" aria-label="equals vs compare">
  <text x="430" y="26" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">Jangan tertukar: = vs ==</text>
  <rect x="40" y="50" width="380" height="180" rx="12" fill="#FFEBEE" stroke="#C62828" stroke-width="2.5"/>
  <text x="230" y="85" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#B71C1C">SALAH untuk membandingkan</text>
  <text x="230" y="125" text-anchor="middle" font-family="Consolas,monospace" font-size="18" font-weight="700" fill="#C62828">if (suhu = 30)</text>
  <text x="230" y="160" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#B71C1C">satu = = mengisi / menyimpan</text>
  <text x="230" y="190" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#C62828">bukan pertanyaan “apakah sama?”</text>
  <rect x="440" y="50" width="380" height="180" rx="12" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="630" y="85" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#1B5E20">BENAR untuk membandingkan</text>
  <text x="630" y="125" text-anchor="middle" font-family="Consolas,monospace" font-size="18" font-weight="700" fill="#2E7D32">if (suhu == 30)</text>
  <text x="630" y="160" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#1B5E20">dua == = bertanya “sama?”</text>
  <text x="630" y="190" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#2E7D32">hasilnya true atau false</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Tips:</strong> untuk “lebih besar / lebih kecil” pakai <code>&gt;</code> <code>&lt;</code> <code>&gt;=</code> <code>&lt;=</code>. Untuk “sama?” pakai <code>==</code>.
    <br>Sumber gambar: diagram buatan Koding Indonesia (FS-14).
  </figcaption>
</figure>
SVG;
    }

    private function equalVsCompareSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="difference between = and ==" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 260" width="100%" height="auto" role="img" aria-label="equals vs compare">
  <text x="430" y="26" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">Do not mix them up: = vs ==</text>
  <rect x="40" y="50" width="380" height="180" rx="12" fill="#FFEBEE" stroke="#C62828" stroke-width="2.5"/>
  <text x="230" y="85" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#B71C1C">WRONG for comparing</text>
  <text x="230" y="125" text-anchor="middle" font-family="Consolas,monospace" font-size="18" font-weight="700" fill="#C62828">if (temp = 30)</text>
  <text x="230" y="160" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#B71C1C">one = assigns / stores</text>
  <text x="230" y="190" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#C62828">not the question “are they equal?”</text>
  <rect x="440" y="50" width="380" height="180" rx="12" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="630" y="85" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#1B5E20">RIGHT for comparing</text>
  <text x="630" y="125" text-anchor="middle" font-family="Consolas,monospace" font-size="18" font-weight="700" fill="#2E7D32">if (temp == 30)</text>
  <text x="630" y="160" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#1B5E20">two == asks “equal?”</text>
  <text x="630" y="190" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#2E7D32">result is true or false</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Tip:</strong> for greater/less use <code>&gt;</code> <code>&lt;</code> <code>&gt;=</code> <code>&lt;=</code>. For “equal?” use <code>==</code>.
    <br>Image source: diagram by Koding Indonesia (FS-14).
  </figcaption>
</figure>
SVG;
    }

    private function ladderSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="tangga if else if else untuk suhu" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 300" width="100%" height="auto" role="img" aria-label="else if ladder">
  <text x="430" y="26" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">Tangga keputusan: if → else if → else</text>
  <rect x="60" y="50" width="740" height="60" rx="10" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
  <text x="430" y="78" text-anchor="middle" font-family="Consolas,monospace" font-size="14" fill="#0D47A1">if (suhu &lt; 20) → DINGIN</text>
  <text x="430" y="98" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#1565C0">cek pertama</text>
  <rect x="60" y="125" width="740" height="60" rx="10" fill="#FFF8E1" stroke="#F9A825" stroke-width="2.5"/>
  <text x="430" y="153" text-anchor="middle" font-family="Consolas,monospace" font-size="14" fill="#F57F17">else if (suhu &lt;= 30) → NORMAL</text>
  <text x="430" y="173" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#F9A825">cek kedua (hanya jika yang pertama false)</text>
  <rect x="60" y="200" width="740" height="60" rx="10" fill="#FFEBEE" stroke="#C62828" stroke-width="2.5"/>
  <text x="430" y="228" text-anchor="middle" font-family="Consolas,monospace" font-size="14" fill="#B71C1C">else → PANAS</text>
  <text x="430" y="248" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#C62828">sisanya (bila kedua di atas false)</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Intinya:</strong> board berhenti di cabang pertama yang benar. Indentasi (spasi ke dalam) membantu mata mengikuti blok <code>{ }</code>.
    <br>Sumber gambar: diagram buatan Koding Indonesia (FS-14).
  </figcaption>
</figure>
SVG;
    }

    private function ladderSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="if else if else ladder for temperature" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 300" width="100%" height="auto" role="img" aria-label="else if ladder">
  <text x="430" y="26" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">Decision ladder: if → else if → else</text>
  <rect x="60" y="50" width="740" height="60" rx="10" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
  <text x="430" y="78" text-anchor="middle" font-family="Consolas,monospace" font-size="14" fill="#0D47A1">if (temp &lt; 20) → COLD</text>
  <text x="430" y="98" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#1565C0">first check</text>
  <rect x="60" y="125" width="740" height="60" rx="10" fill="#FFF8E1" stroke="#F9A825" stroke-width="2.5"/>
  <text x="430" y="153" text-anchor="middle" font-family="Consolas,monospace" font-size="14" fill="#F57F17">else if (temp &lt;= 30) → NORMAL</text>
  <text x="430" y="173" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#F9A825">second check (only if the first was false)</text>
  <rect x="60" y="200" width="740" height="60" rx="10" fill="#FFEBEE" stroke="#C62828" stroke-width="2.5"/>
  <text x="430" y="228" text-anchor="middle" font-family="Consolas,monospace" font-size="14" fill="#B71C1C">else → HOT</text>
  <text x="430" y="248" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#C62828">everything else (if both above were false)</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>In short:</strong> the board stops at the first true branch. Indentation (spaces inward) helps your eyes follow <code>{ }</code> blocks.
    <br>Image source: diagram by Koding Indonesia (FS-14).
  </figcaption>
</figure>
SVG;
    }

    private function serialPanelSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Serial berubah saat angka suhu diganti" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 300" width="100%" height="auto" role="img" aria-label="Serial PANAS NORMAL">
  <text x="430" y="24" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">Ubah angka suhu → Upload lagi → teks Serial berubah</text>
  <rect x="40" y="45" width="370" height="220" rx="10" fill="#1E1E1E" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="225" y="75" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#B0BEC5">suhu = 18  · baud 115200</text>
  <text x="70" y="120" font-family="Consolas,monospace" font-size="14" fill="#A5D6A7">suhu dummy = 18</text>
  <text x="70" y="155" font-family="Consolas,monospace" font-size="16" font-weight="700" fill="#81D4FA">DINGIN</text>
  <text x="70" y="200" font-family="system-ui,sans-serif" font-size="12" fill="#90A4AE">karena 18 &lt; 20</text>
  <rect x="450" y="45" width="370" height="220" rx="10" fill="#1E1E1E" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="635" y="75" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#B0BEC5">suhu = 36  · baud 115200</text>
  <text x="480" y="120" font-family="Consolas,monospace" font-size="14" fill="#A5D6A7">suhu dummy = 36</text>
  <text x="480" y="155" font-family="Consolas,monospace" font-size="16" font-weight="700" fill="#EF9A9A">PANAS</text>
  <text x="480" y="200" font-family="system-ui,sans-serif" font-size="12" fill="#90A4AE">karena bukan &lt;20 dan bukan &lt;=30</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Intinya:</strong> belum ada sensor — kamu ganti angka di kode, Upload, lalu baca Serial Monitor (sama seperti FS-13).
    <br>Sumber gambar: diagram buatan Koding Indonesia (FS-14). Panduan Serial: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-serial-monitor/" rel="noopener noreferrer" target="_blank">Arduino Docs — Serial Monitor (IDE 2)</a>.
  </figcaption>
</figure>
SVG;
    }

    private function serialPanelSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Serial changes when the temperature number changes" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 300" width="100%" height="auto" role="img" aria-label="Serial HOT NORMAL">
  <text x="430" y="24" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">Change the number → Upload again → Serial text changes</text>
  <rect x="40" y="45" width="370" height="220" rx="10" fill="#1E1E1E" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="225" y="75" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#B0BEC5">temp = 18  · baud 115200</text>
  <text x="70" y="120" font-family="Consolas,monospace" font-size="14" fill="#A5D6A7">dummy temp = 18</text>
  <text x="70" y="155" font-family="Consolas,monospace" font-size="16" font-weight="700" fill="#81D4FA">COLD</text>
  <text x="70" y="200" font-family="system-ui,sans-serif" font-size="12" fill="#90A4AE">because 18 &lt; 20</text>
  <rect x="450" y="45" width="370" height="220" rx="10" fill="#1E1E1E" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="635" y="75" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#B0BEC5">temp = 36  · baud 115200</text>
  <text x="480" y="120" font-family="Consolas,monospace" font-size="14" fill="#A5D6A7">dummy temp = 36</text>
  <text x="480" y="155" font-family="Consolas,monospace" font-size="16" font-weight="700" fill="#EF9A9A">HOT</text>
  <text x="480" y="200" font-family="system-ui,sans-serif" font-size="12" fill="#90A4AE">because not &lt;20 and not &lt;=30</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>In short:</strong> no sensor yet — change the number in code, Upload, then read Serial Monitor (same as FS-13).
    <br>Image source: diagram by Koding Indonesia (FS-14). Serial guide: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-serial-monitor/" rel="noopener noreferrer" target="_blank">Arduino Docs — Serial Monitor (IDE 2)</a>.
  </figcaption>
</figure>
SVG;
    }

    private function body(): string
    {
        $board = $this->boardFigureId();
        $fork = $this->decisionForkSvgId();
        $eq = $this->equalVsCompareSvgId();
        $ladder = $this->ladderSvgId();
        $panel = $this->serialPanelSvgId();

        return <<<HTML
<h2>Pendahuluan — program yang bisa memilih</h2>
<p>Artikel ini adalah <strong>#84 (ini)</strong> · modul <strong>FS-14</strong> di jalur <em>Full Stack IoT Developer — Dari Nol</em>. Di <strong>FS-13</strong> kamu sudah biasa membaca log di Serial Monitor. Hari ini program mulai <strong>memutuskan</strong>: angka suhu dummy → cetak DINGIN, NORMAL, atau PANAS.</p>
<p><strong>Analogi:</strong> <code>if</code> = simpang jalan. Kondisi true/false = rambu. <code>else if</code> = cabang berikutnya. <code>else</code> = jalan terakhir jika semua cabang sebelumnya tidak cocok.</p>
<p><strong>Prasyarat:</strong> FS-13 (Serial Monitor baud 115200 sudah pernah terbaca).</p>
<p><strong>Cara pakai artikel ini (urutan kerja):</strong></p>
<ol>
<li><strong>Buka Arduino IDE</strong> dulu (bukan Laragon / terminal web).</li>
<li>Baca gambar simpang + beda <code>=</code> vs <code>==</code> + tangga <code>else if</code>.</li>
<li>Buat sketch <code>FS14_panas</code> → <strong>Upload</strong>.</li>
<li><strong>Buka Serial Monitor</strong> → baud <strong>115200</strong> → lihat label suhu.</li>
<li>Ganti angka <code>suhu</code>, Upload lagi, bandingkan teksnya. Centang checklist 10/10.</li>
</ol>
<p><strong>Tidak perlu hari ini:</strong> sensor suhu nyata, Wi-Fi, Laragon, <code>php artisan</code>, wiring baru. Tools hari ini: <strong>Arduino IDE</strong> + <strong>Serial Monitor</strong> + ESP32 + kabel USB data + <strong>browser</strong> (checklist).</p>

<h2>Persiapan — buka &amp; siapkan ini dulu</h2>
<p><strong>Urutan meja kerja:</strong> sintaks diuji di Arduino IDE + Serial Monitor — bukan di terminal PHP.</p>
<ol>
<li>Buka <strong>Arduino IDE 2.x</strong>.</li>
<li>Board <strong>ESP32 Dev Module</strong> + port COM/tty sudah dipilih.</li>
<li>Siapkan ESP32 + kabel USB data.</li>
<li>Pastikan Serial Monitor siap di baud 115200 (seperti FS-13).</li>
</ol>
<p><strong>Alat yang dipakai hari ini:</strong> Arduino IDE, Serial Monitor, ESP32, USB data, browser.</p>
<p><strong>Tidak dipakai hari ini:</strong> Laragon, <code>php artisan</code>, sensor DHT/BME, multimeter, PuTTY.</p>
{$board}

<h2>Kondisi true/false dan if / else</h2>
{$fork}
<ul>
<li>Kondisi yang benar → <strong>true</strong> → blok <code>if</code> jalan.</li>
<li>Kondisi yang salah → <strong>false</strong> → blok <code>else</code> jalan (jika ada).</li>
<li>Perbandingan umum: <code>&gt;</code> <code>&lt;</code> <code>&gt;=</code> <code>&lt;=</code> <code>==</code>.</li>
</ul>

<h2>Kesalahan klasik: = vs ==</h2>
{$eq}
<p><strong>Intinya:</strong> di dalam <code>if (...)</code> hampir selalu pakai <code>==</code> untuk “apakah sama?”. Satu <code>=</code> untuk menyimpan nilai ke variabel (misalnya <code>int suhu = 25;</code>).</p>

<h2>else if — lebih dari dua pilihan</h2>
{$ladder}
<p>Indentasi (menjorok ke dalam) tidak mengubah arti kode di C++, tetapi sangat membantu mata membaca “blok mana milik if yang mana”. Ikuti pola sketch di bawah.</p>

<h2>Praktik — sketch FS14_panas</h2>
{$panel}
<p>Tujuan: ganti angka suhu dummy → label Serial berubah (DINGIN / NORMAL / PANAS).</p>
<ol>
<li>Arduino IDE → <strong>File → New Sketch</strong> → Save sebagai <code>FS14_panas</code>.</li>
<li>Ganti isi dengan kode di bawah (salin utuh).</li>
<li><strong>Verify</strong> → <strong>Upload</strong> → tunggu <em>Done uploading</em>.</li>
<li>Klik <strong>Open Serial Monitor</strong> (toolbar kanan atas IDE 2) → set <strong>115200</strong>.</li>
<li>Ubah <code>int suhu = …</code> ke 18, lalu 25, lalu 36 — tiap kali <strong>Upload</strong> ulang — amati teksnya.</li>
</ol>
<pre><code class="language-cpp">// FS14_panas — Full Stack IoT FS-14
// Suhu dummy (bukan sensor). Baud Serial 115200.

int suhu = 36; // coba ganti: 18, 25, atau 36 — lalu Upload lagi

void setup() {
  Serial.begin(115200);
  delay(1000); // waktu membuka Serial Monitor
  Serial.println("FS14_panas siap");
}

void loop() {
  Serial.print("suhu dummy = ");
  Serial.println(suhu);

  if (suhu < 20) {
    Serial.println("DINGIN");
  } else if (suhu <= 30) {
    Serial.println("NORMAL");
  } else {
    Serial.println("PANAS");
  }

  delay(2000); // jeda biar mudah dibaca
}
</code></pre>
<p><strong>Cara menguji perintah di atas:</strong> uji di <strong>Arduino IDE + Serial Monitor</strong>. Sukses = teks DINGIN/NORMAL/PANAS cocok dengan angka yang kamu tulis. Bukan perintah Laragon / web server.</p>

<h2 id="fsiot-if-checklist">Praktik — checklist if/else</h2>
<p>Centang setiap langkah setelah kamu lakukan di meja. Target: <strong>10/10</strong>. Ada checklist interaktif di bawah.</p>
<ul id="fsiot-if-checklist-items">
<li>Arduino IDE sudah terbuka sebelum menulis kode</li>
<li>Board ESP32 Dev Module + port sudah dipilih</li>
<li>Paham: if = cabang true, else = cabang false</li>
<li>Paham beda = (menyimpan) vs == (membandingkan)</li>
<li>Sketch disimpan sebagai FS14_panas</li>
<li>Upload berhasil — Done uploading</li>
<li>Serial Monitor terbuka, baud = 115200</li>
<li>Dengan suhu 36 muncul PANAS (atau sesuai angka uji)</li>
<li>Setelah ganti angka + Upload, teks Serial berubah</li>
<li>Sadar: ini suhu dummy — sensor nyata belakangan</li>
</ul>
<p><strong>Cara menguji checklist:</strong> centang di browser setelah praktik di IDE. Tidak perlu <code>php artisan</code>.</p>

<h2>Kesalahan yang sering terjadi</h2>
<ul>
<li><strong>Menulis <code>if (suhu = 30)</code>.</strong> Itu menyimpan, bukan membandingkan. Pakai <code>==</code>.</li>
<li><strong>Lupa kurung kurawal <code>{ }</code>.</strong> Blok jadi ambigu — selalu pakai pasangan lengkap.</li>
<li><strong>Menguji di terminal web.</strong> Sketch hanya jalan di board lewat IDE.</li>
<li><strong>Baud salah.</strong> Samakan 115200 di kode dan Serial Monitor.</li>
<li><strong>Mengganti angka tanpa Upload ulang.</strong> Board masih memakai sketch lama.</li>
<li><strong>Mengira sudah pakai sensor.</strong> Angka masih dummy di kode — sensor belakangan.</li>
<li><strong>Indentasi berantakan.</strong> Kode masih bisa jalan, tetapi sulit dicek — rapikan dulu.</li>
</ul>

<h2>Selanjutnya</h2>
<p><strong>Intinya:</strong> kalau mengubah angka suhu dummy mengubah teks Serial dengan benar, FS-14 selesai.</p>
<p>Lanjut ke <strong>FS-15</strong> (pengulangan <code>for</code> / <code>while</code>) saat modulnya terbit.</p>
<p>Daftar modul lengkap: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $board = $this->boardFigureEn();
        $fork = $this->decisionForkSvgEn();
        $eq = $this->equalVsCompareSvgEn();
        $ladder = $this->ladderSvgEn();
        $panel = $this->serialPanelSvgEn();

        return <<<HTML
<h2>Introduction — a program that can choose</h2>
<p>This is article <strong>#84 (this article)</strong> · module <strong>FS-14</strong> on the <em>Full Stack IoT Developer — From Zero</em> path. In <strong>FS-13</strong> you got used to reading Serial Monitor logs. Today the program starts to <strong>decide</strong>: a dummy temperature number → print COLD, NORMAL, or HOT.</p>
<p><strong>Analogy:</strong> <code>if</code> = a fork in the road. A true/false condition = the sign. <code>else if</code> = the next branch. <code>else</code> = the last road if earlier branches did not match.</p>
<p><strong>Prerequisites:</strong> FS-13 (you have already read Serial Monitor at 115200 baud).</p>
<p><strong>How to use this article (work order):</strong></p>
<ol>
<li><strong>Open Arduino IDE</strong> first (not Laragon / a web terminal).</li>
<li>Read the fork figure + <code>=</code> vs <code>==</code> + the <code>else if</code> ladder.</li>
<li>Create sketch <code>FS14_panas</code> → <strong>Upload</strong>.</li>
<li><strong>Open Serial Monitor</strong> → baud <strong>115200</strong> → watch the label.</li>
<li>Change the <code>suhu</code>/<code>temp</code> number, Upload again, compare the text. Tick the 10/10 checklist.</li>
</ol>
<p><strong>Not needed today:</strong> a real temperature sensor, Wi-Fi, Laragon, <code>php artisan</code>, new wiring. Today's tools: <strong>Arduino IDE</strong> + <strong>Serial Monitor</strong> + ESP32 + USB data cable + <strong>browser</strong> (checklist).</p>

<h2>Preparation — open &amp; gather these first</h2>
<p><strong>Desk order:</strong> syntax is tested in Arduino IDE + Serial Monitor — not in a PHP terminal.</p>
<ol>
<li>Open <strong>Arduino IDE 2.x</strong>.</li>
<li><strong>ESP32 Dev Module</strong> board + COM/tty port are selected.</li>
<li>Prepare the ESP32 + USB data cable.</li>
<li>Have Serial Monitor ready at baud 115200 (like FS-13).</li>
</ol>
<p><strong>Tools used today:</strong> Arduino IDE, Serial Monitor, ESP32, USB data, browser.</p>
<p><strong>Not used today:</strong> Laragon, <code>php artisan</code>, DHT/BME sensors, multimeter, PuTTY.</p>
{$board}

<h2>true/false conditions and if / else</h2>
{$fork}
<ul>
<li>True condition → <strong>true</strong> → the <code>if</code> block runs.</li>
<li>False condition → <strong>false</strong> → the <code>else</code> block runs (if present).</li>
<li>Common comparisons: <code>&gt;</code> <code>&lt;</code> <code>&gt;=</code> <code>&lt;=</code> <code>==</code>.</li>
</ul>

<h2>Classic mistake: = vs ==</h2>
{$eq}
<p><strong>In short:</strong> inside <code>if (...)</code> you almost always use <code>==</code> for “are they equal?”. One <code>=</code> stores a value in a variable (for example <code>int suhu = 25;</code>).</p>

<h2>else if — more than two choices</h2>
{$ladder}
<p>Indentation (spaces inward) does not change C++ meaning, but it helps your eyes see which block belongs to which <code>if</code>. Follow the sketch pattern below.</p>

<h2>Practice — sketch FS14_panas</h2>
{$panel}
<p>Goal: change the dummy temperature number → Serial label changes (COLD / NORMAL / HOT).</p>
<ol>
<li>Arduino IDE → <strong>File → New Sketch</strong> → Save as <code>FS14_panas</code>.</li>
<li>Replace the contents with the code below (copy it whole).</li>
<li><strong>Verify</strong> → <strong>Upload</strong> → wait for <em>Done uploading</em>.</li>
<li>Click <strong>Open Serial Monitor</strong> (top-right toolbar in IDE 2) → set <strong>115200</strong>.</li>
<li>Change <code>int suhu = …</code> to 18, then 25, then 36 — <strong>Upload</strong> each time — watch the text.</li>
</ol>
<pre><code class="language-cpp">// FS14_panas — Full Stack IoT FS-14
// Dummy temperature (not a sensor). Serial baud 115200.

int suhu = 36; // try 18, 25, or 36 — then Upload again

void setup() {
  Serial.begin(115200);
  delay(1000); // time to open Serial Monitor
  Serial.println("FS14_panas ready");
}

void loop() {
  Serial.print("dummy temp = ");
  Serial.println(suhu);

  if (suhu < 20) {
    Serial.println("COLD");
  } else if (suhu <= 30) {
    Serial.println("NORMAL");
  } else {
    Serial.println("HOT");
  }

  delay(2000); // pause so it is easy to read
}
</code></pre>
<p><strong>How to test the code above:</strong> test in <strong>Arduino IDE + Serial Monitor</strong>. Success = COLD/NORMAL/HOT matches the number you wrote. Not a Laragon / web-server command.</p>

<h2 id="fsiot-if-checklist">Practice — if/else checklist</h2>
<p>Tick each step after you do it at the desk. Target: <strong>10/10</strong>. An interactive checklist is below.</p>
<ul id="fsiot-if-checklist-items">
<li>Arduino IDE is open before writing code</li>
<li>ESP32 Dev Module board + port are selected</li>
<li>I understand: if = true branch, else = false branch</li>
<li>I know = (assign) vs == (compare)</li>
<li>Sketch saved as FS14_panas</li>
<li>Upload succeeded — Done uploading</li>
<li>Serial Monitor open, baud = 115200</li>
<li>With suhu 36 I see HOT (or matching my test number)</li>
<li>After changing the number + Upload, Serial text changes</li>
<li>I know: this is dummy suhu — real sensors come later</li>
</ul>
<p><strong>How to test the checklist:</strong> tick it in the browser after IDE practice. No <code>php artisan</code> required.</p>

<h2>Common mistakes</h2>
<ul>
<li><strong>Writing <code>if (suhu = 30)</code>.</strong> That assigns, it does not compare. Use <code>==</code>.</li>
<li><strong>Missing braces <code>{ }</code>.</strong> Blocks become unclear — always use matching pairs.</li>
<li><strong>Testing in a web terminal.</strong> Sketches only run on the board via the IDE.</li>
<li><strong>Wrong baud.</strong> Match 115200 in code and Serial Monitor.</li>
<li><strong>Changing the number without Uploading again.</strong> The board still runs the old sketch.</li>
<li><strong>Thinking a sensor is already in use.</strong> The number is still a dummy in code — sensors come later.</li>
<li><strong>Messy indentation.</strong> Code may still run, but it is hard to check — tidy it first.</li>
</ul>

<h2>Next steps</h2>
<p><strong>In short:</strong> if changing the dummy temperature number correctly changes Serial text, FS-14 is done.</p>
<p>Continue to <strong>FS-15</strong> (<code>for</code> / <code>while</code> loops) when that module publishes.</p>
<p>Full module list: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }
}
