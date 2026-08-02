<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article85Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first()
            ?? Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $iotCat) {
            throw new \RuntimeException('User atau kategori iot-smart-device/esp32-arduino tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-for-while-loop';

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
                'title'              => 'Pengulangan for & while — hitung tanpa bingung',
                'title_en'           => 'for & while loops — count without getting lost',
                'excerpt'            => 'FS-15 / #85: for, while, bedanya dengan loop() Arduino, dan peringatan delay. Uji di Arduino IDE + Serial Monitor: cetak hitungan 1–10.',
                'excerpt_en'         => 'FS-15 / #85: for, while, how they differ from Arduino loop(), and a delay warning. Test in Arduino IDE + Serial Monitor: print counts 1–10.',
                'body'               => $this->body(),
                'body_en'            => $this->bodyEn(),
                'status'             => 'draft',
                'is_featured'        => false,
                'published_at'       => null,
                'seo_title'          => 'Pengulangan for & while — Full Stack IoT #85',
                'seo_title_en'       => 'for & while loops — Full Stack IoT #85',
                'seo_description'    => 'Belajar for dan while: cetak 1–10 di Serial, bedakan dari loop() Arduino. Modul FS-15 jalur Full Stack IoT.',
                'seo_description_en' => 'Learn for and while: print 1–10 on Serial, and tell them apart from Arduino loop(). Full Stack IoT FS-15 module.',
            ]
        );

        if ($article->published_at !== null || $article->status !== 'draft') {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['fullstack-iot', 'iot', 'esp32'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel #85 / FS-15 tersimpan sebagai DRAFT: '.$article->title);
    }

    private function boardFigureId(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/esp32-devkitc-overview.jpg" width="1200" height="800" alt="ESP32-DevKitC — board untuk melihat hitungan di Serial Monitor" loading="eager" style="width:100%;height:auto;max-height:360px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>ESP32-DevKitC</strong> — colok <strong>kabel USB data</strong> di label <strong>(6)</strong>. Hari ini kita menghitung di kode, bukan menambah sensor.
    <br>Sumber gambar: <a href="https://docs.espressif.com/projects/esp-dev-kits/en/latest/esp32/esp32-devkitc/user_guide.html" rel="noopener noreferrer" target="_blank">Espressif — ESP32-DevKitC user guide</a>.
  </figcaption>
</figure>
HTML;
    }

    private function boardFigureEn(): string
    {
        return <<<'HTML'
<figure style="margin:1.5rem 0;max-width:100%">
  <img src="/images/fsiot/esp32-devkitc-overview.jpg" width="1200" height="800" alt="ESP32-DevKitC — board for watching counts in Serial Monitor" loading="eager" style="width:100%;height:auto;max-height:360px;object-fit:contain;border:2.5px solid #1a1a1a;border-radius:8px;background:#fff;padding:0.5rem">
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>ESP32-DevKitC</strong> — plug a <strong>USB data cable</strong> at label <strong>(6)</strong>. Today we count in code; no new sensors.
    <br>Image source: <a href="https://docs.espressif.com/projects/esp-dev-kits/en/latest/esp32/esp32-devkitc/user_guide.html" rel="noopener noreferrer" target="_blank">Espressif — ESP32-DevKitC user guide</a>.
  </figcaption>
</figure>
HTML;
    }

    private function forStepsSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="for menghitung langkah demi langkah" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 240" width="100%" height="auto" role="img" aria-label="for steps">
  <text x="430" y="26" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">for = hitung berulang dengan batas jelas</text>
  <rect x="40" y="50" width="180" height="150" rx="12" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
  <text x="130" y="95" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700" fill="#0D47A1">1. mulai</text>
  <text x="130" y="125" text-anchor="middle" font-family="Consolas,monospace" font-size="12" fill="#1565C0">i = 1</text>
  <text x="130" y="155" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#1565C0">nilai awal</text>
  <rect x="240" y="50" width="180" height="150" rx="12" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="330" y="95" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700" fill="#1B5E20">2. cek</text>
  <text x="330" y="125" text-anchor="middle" font-family="Consolas,monospace" font-size="12" fill="#2E7D32">i &lt;= 10 ?</text>
  <text x="330" y="155" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#2E7D32">masih lanjut?</text>
  <rect x="440" y="50" width="180" height="150" rx="12" fill="#FFF8E1" stroke="#F9A825" stroke-width="2.5"/>
  <text x="530" y="95" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700" fill="#F57F17">3. kerja</text>
  <text x="530" y="125" text-anchor="middle" font-family="Consolas,monospace" font-size="12" fill="#F9A825">println(i)</text>
  <text x="530" y="155" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#F9A825">cetak angka</text>
  <rect x="640" y="50" width="180" height="150" rx="12" fill="#F3E5F5" stroke="#7B1FA2" stroke-width="2.5"/>
  <text x="730" y="95" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700" fill="#6A1B9A">4. naikkan</text>
  <text x="730" y="125" text-anchor="middle" font-family="Consolas,monospace" font-size="12" fill="#7B1FA2">i = i + 1</text>
  <text x="730" y="155" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#7B1FA2">lalu ulang cek</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Intinya:</strong> <code>for</code> punya “mulai → cek → kerja → naikkan” dalam satu baris. Referensi: <a href="https://www.arduino.cc/reference/en/language/structure/control-structure/for/" rel="noopener noreferrer" target="_blank">Arduino Language Reference — for</a>.
    <br>Sumber gambar: diagram buatan Koding Indonesia (FS-15).
  </figcaption>
</figure>
SVG;
    }

    private function forStepsSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="for counts step by step" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 240" width="100%" height="auto" role="img" aria-label="for steps">
  <text x="430" y="26" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">for = repeat with a clear limit</text>
  <rect x="40" y="50" width="180" height="150" rx="12" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
  <text x="130" y="95" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700" fill="#0D47A1">1. start</text>
  <text x="130" y="125" text-anchor="middle" font-family="Consolas,monospace" font-size="12" fill="#1565C0">i = 1</text>
  <text x="130" y="155" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#1565C0">initial value</text>
  <rect x="240" y="50" width="180" height="150" rx="12" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="330" y="95" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700" fill="#1B5E20">2. check</text>
  <text x="330" y="125" text-anchor="middle" font-family="Consolas,monospace" font-size="12" fill="#2E7D32">i &lt;= 10 ?</text>
  <text x="330" y="155" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#2E7D32">keep going?</text>
  <rect x="440" y="50" width="180" height="150" rx="12" fill="#FFF8E1" stroke="#F9A825" stroke-width="2.5"/>
  <text x="530" y="95" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700" fill="#F57F17">3. work</text>
  <text x="530" y="125" text-anchor="middle" font-family="Consolas,monospace" font-size="12" fill="#F9A825">println(i)</text>
  <text x="530" y="155" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#F9A825">print the number</text>
  <rect x="640" y="50" width="180" height="150" rx="12" fill="#F3E5F5" stroke="#7B1FA2" stroke-width="2.5"/>
  <text x="730" y="95" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" font-weight="700" fill="#6A1B9A">4. step</text>
  <text x="730" y="125" text-anchor="middle" font-family="Consolas,monospace" font-size="12" fill="#7B1FA2">i = i + 1</text>
  <text x="730" y="155" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#7B1FA2">then check again</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>In short:</strong> <code>for</code> packs “start → check → work → step” into one line. Reference: <a href="https://www.arduino.cc/reference/en/language/structure/control-structure/for/" rel="noopener noreferrer" target="_blank">Arduino Language Reference — for</a>.
    <br>Image source: diagram by Koding Indonesia (FS-15).
  </figcaption>
</figure>
SVG;
    }

    private function whileCompareSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="for vs while dua cara mengulang" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 260" width="100%" height="auto" role="img" aria-label="for vs while">
  <text x="430" y="26" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">for dan while — dua cara mengulang</text>
  <rect x="40" y="50" width="380" height="180" rx="12" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
  <text x="230" y="85" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#0D47A1">for</text>
  <text x="230" y="120" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#1565C0">cocok bila sudah tahu</text>
  <text x="230" y="145" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#1565C0">berapa kali mengulang</text>
  <text x="230" y="180" text-anchor="middle" font-family="Consolas,monospace" font-size="12" fill="#0D47A1">for (i=1; i&lt;=10; i++)</text>
  <text x="230" y="205" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#1565C0">contoh: hitung 1 sampai 10</text>
  <rect x="440" y="50" width="380" height="180" rx="12" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="630" y="85" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#1B5E20">while</text>
  <text x="630" y="120" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#2E7D32">cocok bila “selama masih”</text>
  <text x="630" y="145" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#2E7D32">kondisi tertentu benar</text>
  <text x="630" y="180" text-anchor="middle" font-family="Consolas,monospace" font-size="12" fill="#1B5E20">while (n &lt;= 10)</text>
  <text x="630" y="205" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#2E7D32">ingat naikkan n di dalam!</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Tips:</strong> keduanya bisa dipakai untuk 1–10. Referensi: <a href="https://www.arduino.cc/reference/en/language/structure/control-structure/while/" rel="noopener noreferrer" target="_blank">Arduino Language Reference — while</a>.
    <br>Sumber gambar: diagram buatan Koding Indonesia (FS-15).
  </figcaption>
</figure>
SVG;
    }

    private function whileCompareSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="for vs while for beginners" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 260" width="100%" height="auto" role="img" aria-label="for vs while">
  <text x="430" y="26" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">for and while — two ways to repeat</text>
  <rect x="40" y="50" width="380" height="180" rx="12" fill="#E3F2FD" stroke="#1565C0" stroke-width="2.5"/>
  <text x="230" y="85" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#0D47A1">for</text>
  <text x="230" y="120" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#1565C0">great when you already know</text>
  <text x="230" y="145" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#1565C0">how many times to repeat</text>
  <text x="230" y="180" text-anchor="middle" font-family="Consolas,monospace" font-size="12" fill="#0D47A1">for (i=1; i&lt;=10; i++)</text>
  <text x="230" y="205" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#1565C0">example: count 1 to 10</text>
  <rect x="440" y="50" width="380" height="180" rx="12" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="630" y="85" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#1B5E20">while</text>
  <text x="630" y="120" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#2E7D32">great for “as long as”</text>
  <text x="630" y="145" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#2E7D32">a condition stays true</text>
  <text x="630" y="180" text-anchor="middle" font-family="Consolas,monospace" font-size="12" fill="#1B5E20">while (n &lt;= 10)</text>
  <text x="630" y="205" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#2E7D32">remember to increase n!</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Tip:</strong> either can print 1–10. Reference: <a href="https://www.arduino.cc/reference/en/language/structure/control-structure/while/" rel="noopener noreferrer" target="_blank">Arduino Language Reference — while</a>.
    <br>Image source: diagram by Koding Indonesia (FS-15).
  </figcaption>
</figure>
SVG;
    }

    private function loopVsForSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="beda loop Arduino dengan for" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 280" width="100%" height="auto" role="img" aria-label="loop vs for">
  <text x="430" y="26" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">Jangan tertukar: loop() Arduino vs for/while</text>
  <rect x="40" y="50" width="380" height="200" rx="12" fill="#FFF3E0" stroke="#EF6C00" stroke-width="2.5"/>
  <text x="230" y="90" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#E65100">loop()</text>
  <text x="230" y="125" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#EF6C00">otomatis dari Arduino</text>
  <text x="230" y="155" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#EF6C00">jalan TERUS tanpa batas</text>
  <text x="230" y="190" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#E65100">kamu tidak menulis “berapa kali”</text>
  <text x="230" y="220" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#EF6C00">(sudah dipelajari di FS-11)</text>
  <rect x="440" y="50" width="380" height="200" rx="12" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="630" y="90" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#1B5E20">for / while</text>
  <text x="630" y="125" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#2E7D32">kamu tulis sendiri</text>
  <text x="630" y="155" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#2E7D32">punya batas / kondisi</text>
  <text x="630" y="190" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#1B5E20">bisa di dalam setup() atau loop()</text>
  <text x="630" y="220" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#2E7D32">hari ini: for di setup()</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Intinya:</strong> <code>loop()</code> = mesin Arduino yang terus berputar. <code>for</code>/<code>while</code> = pengulangan yang kamu kendalikan.
    <br>Sumber gambar: diagram buatan Koding Indonesia (FS-15).
  </figcaption>
</figure>
SVG;
    }

    private function loopVsForSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Arduino loop versus for" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 280" width="100%" height="auto" role="img" aria-label="loop vs for">
  <text x="430" y="26" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">Do not mix them up: Arduino loop() vs for/while</text>
  <rect x="40" y="50" width="380" height="200" rx="12" fill="#FFF3E0" stroke="#EF6C00" stroke-width="2.5"/>
  <text x="230" y="90" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#E65100">loop()</text>
  <text x="230" y="125" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#EF6C00">automatic from Arduino</text>
  <text x="230" y="155" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#EF6C00">runs FOREVER</text>
  <text x="230" y="190" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#E65100">you do not write “how many times”</text>
  <text x="230" y="220" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#EF6C00">(learned in FS-11)</text>
  <rect x="440" y="50" width="380" height="200" rx="12" fill="#E8F5E9" stroke="#2E7D32" stroke-width="2.5"/>
  <text x="630" y="90" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700" fill="#1B5E20">for / while</text>
  <text x="630" y="125" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#2E7D32">you write them yourself</text>
  <text x="630" y="155" text-anchor="middle" font-family="system-ui,sans-serif" font-size="13" fill="#2E7D32">they have a limit / condition</text>
  <text x="630" y="190" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#1B5E20">can live in setup() or loop()</text>
  <text x="630" y="220" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#2E7D32">today: for inside setup()</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>In short:</strong> <code>loop()</code> = Arduino's forever engine. <code>for</code>/<code>while</code> = loops you control.
    <br>Image source: diagram by Koding Indonesia (FS-15).
  </figcaption>
</figure>
SVG;
    }

    private function serialCountSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Serial menampilkan hitungan 1 sampai 10" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 320" width="100%" height="auto" role="img" aria-label="Serial count 1-10">
  <text x="430" y="24" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">Serial Monitor — hitungan 1 sampai 10 (baud 115200)</text>
  <rect x="80" y="45" width="700" height="240" rx="10" fill="#1E1E1E" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="110" y="80" font-family="Consolas,monospace" font-size="14" fill="#A5D6A7">FS15_hitung siap</text>
  <text x="110" y="110" font-family="Consolas,monospace" font-size="14" fill="#A5D6A7">hitung 1</text>
  <text x="110" y="135" font-family="Consolas,monospace" font-size="14" fill="#A5D6A7">hitung 2</text>
  <text x="110" y="160" font-family="Consolas,monospace" font-size="14" fill="#A5D6A7">hitung 3</text>
  <text x="110" y="185" font-family="Consolas,monospace" font-size="14" fill="#90A4AE">…</text>
  <text x="110" y="210" font-family="Consolas,monospace" font-size="14" fill="#A5D6A7">hitung 10</text>
  <text x="110" y="245" font-family="Consolas,monospace" font-size="14" fill="#FFE082">selesai sekali di setup</text>
  <rect x="520" y="70" width="220" height="36" rx="6" fill="#1565C0"/>
  <text x="630" y="93" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700" fill="#fff">Baud: 115200</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>Intinya:</strong> angka muncul berurutan sekali setelah Upload (karena <code>for</code> di <code>setup</code>). Tekan EN jika sudah lewat.
    <br>Sumber gambar: diagram buatan Koding Indonesia (FS-15). Panduan Serial: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-serial-monitor/" rel="noopener noreferrer" target="_blank">Arduino Docs — Serial Monitor (IDE 2)</a>.
  </figcaption>
</figure>
SVG;
    }

    private function serialCountSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Serial shows counts from 1 to 10" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 860 320" width="100%" height="auto" role="img" aria-label="Serial count 1-10">
  <text x="430" y="24" text-anchor="middle" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="#1a1a1a">Serial Monitor — count 1 to 10 (baud 115200)</text>
  <rect x="80" y="45" width="700" height="240" rx="10" fill="#1E1E1E" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="110" y="80" font-family="Consolas,monospace" font-size="14" fill="#A5D6A7">FS15_hitung ready</text>
  <text x="110" y="110" font-family="Consolas,monospace" font-size="14" fill="#A5D6A7">count 1</text>
  <text x="110" y="135" font-family="Consolas,monospace" font-size="14" fill="#A5D6A7">count 2</text>
  <text x="110" y="160" font-family="Consolas,monospace" font-size="14" fill="#A5D6A7">count 3</text>
  <text x="110" y="185" font-family="Consolas,monospace" font-size="14" fill="#90A4AE">…</text>
  <text x="110" y="210" font-family="Consolas,monospace" font-size="14" fill="#A5D6A7">count 10</text>
  <text x="110" y="245" font-family="Consolas,monospace" font-size="14" fill="#FFE082">done once in setup</text>
  <rect x="520" y="70" width="220" height="36" rx="6" fill="#1565C0"/>
  <text x="630" y="93" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700" fill="#fff">Baud: 115200</text>
</svg>
  <figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">
    <strong>In short:</strong> numbers appear in order once after Upload (because <code>for</code> is in <code>setup</code>). Press EN if you missed them.
    <br>Image source: diagram by Koding Indonesia (FS-15). Serial guide: <a href="https://docs.arduino.cc/software/ide-v2/tutorials/ide-v2-serial-monitor/" rel="noopener noreferrer" target="_blank">Arduino Docs — Serial Monitor (IDE 2)</a>.
  </figcaption>
</figure>
SVG;
    }

    private function body(): string
    {
        $board = $this->boardFigureId();
        $forSteps = $this->forStepsSvgId();
        $whileCmp = $this->whileCompareSvgId();
        $loopVs = $this->loopVsForSvgId();
        $panel = $this->serialCountSvgId();

        return <<<HTML
<h2>Pendahuluan — mengulang tanpa tersesat</h2>
<p>Artikel ini adalah <strong>#85 (ini)</strong> · modul <strong>FS-15</strong> di jalur <em>Full Stack IoT Developer — Dari Nol</em>. Di <strong>FS-14</strong> program sudah bisa memilih. Hari ini kita belajar <strong>mengulang</strong> dengan <code>for</code> dan <code>while</code> — lalu membedakannya dari <code>loop()</code> Arduino.</p>
<p><strong>Analogi:</strong> <code>for</code> = menghitung tangga 1 sampai 10. <code>while</code> = “selama masih ada tangga, terus naik”. <code>loop()</code> Arduino = lift yang turun-naik sendiri tanpa batas.</p>
<p><strong>Prasyarat:</strong> FS-14 (pernah lihat Serial berubah karena kondisi).</p>
<p><strong>Cara pakai artikel ini (urutan kerja):</strong></p>
<ol>
<li><strong>Buka Arduino IDE</strong> dulu (bukan Laragon / terminal web).</li>
<li>Baca beda <code>for</code>, <code>while</code>, dan <code>loop()</code>.</li>
<li>Buat sketch <code>FS15_hitung</code> → <strong>Upload</strong>.</li>
<li><strong>Buka Serial Monitor</strong> → baud <strong>115200</strong> → lihat hitungan 1–10.</li>
<li>Centang checklist 10/10 di browser.</li>
</ol>
<p><strong>Tidak perlu hari ini:</strong> sensor, Wi-Fi, Laragon, <code>php artisan</code>, wiring baru. Tools hari ini: <strong>Arduino IDE</strong> + <strong>Serial Monitor</strong> + ESP32 + kabel USB data + <strong>browser</strong> (checklist).</p>

<h2>Persiapan — buka &amp; siapkan ini dulu</h2>
<p><strong>Urutan meja kerja:</strong> sintaks diuji di Arduino IDE + Serial Monitor — bukan di terminal PHP.</p>
<ol>
<li>Buka <strong>Arduino IDE 2.x</strong>.</li>
<li>Board <strong>ESP32 Dev Module</strong> + port COM/tty sudah dipilih.</li>
<li>Siapkan ESP32 + kabel USB data.</li>
<li>Serial Monitor siap di baud 115200.</li>
</ol>
<p><strong>Alat yang dipakai hari ini:</strong> Arduino IDE, Serial Monitor, ESP32, USB data, browser.</p>
<p><strong>Tidak dipakai hari ini:</strong> Laragon, <code>php artisan</code>, sensor, multimeter, PuTTY.</p>
{$board}

<h2>for — hitung dengan batas jelas</h2>
{$forSteps}
<p>Contoh singkat (belum sketch penuh): <code>for (int i = 1; i &lt;= 10; i = i + 1)</code> artinya mulai dari 1, selama <code>i</code> masih ≤ 10, cetak, lalu naikkan.</p>

<h2>while — ulang selama kondisi benar</h2>
{$whileCmp}
<p><strong>Peringatan penting:</strong> <code>while (true) { … }</code> tanpa jalan keluar bisa membuat board “sibuk terus” di dalam blok itu. Untuk hari ini, selalu tulis kondisi yang suatu saat menjadi salah (false), atau naikkan variabel penhitung.</p>

<h2>Bedanya dengan loop() Arduino</h2>
{$loopVs}
<p>Di sketch hari ini, <code>for</code> kita taruh di <code>setup()</code> supaya hitungan 1–10 muncul <strong>sekali</strong> setelah Upload — mudah dibaca di Serial Monitor.</p>

<h2>Praktik — sketch FS15_hitung</h2>
{$panel}
<p>Tujuan: melihat baris <code>hitung 1</code> … <code>hitung 10</code> berurutan di Serial Monitor.</p>
<ol>
<li>Arduino IDE → <strong>File → New Sketch</strong> → Save sebagai <code>FS15_hitung</code>.</li>
<li>Ganti isi dengan kode di bawah (salin utuh).</li>
<li><strong>Verify</strong> → <strong>Upload</strong> → tunggu <em>Done uploading</em>.</li>
<li>Klik <strong>Open Serial Monitor</strong> (toolbar kanan atas IDE 2) → set <strong>115200</strong>.</li>
<li>Amati hitungan 1–10. Tekan <strong>EN</strong> jika sudah lewat.</li>
</ol>
<pre><code class="language-cpp">// FS15_hitung — Full Stack IoT FS-15
// Cetak hitungan 1 sampai 10 di Serial (baud 115200).

void setup() {
  Serial.begin(115200);
  delay(1000); // waktu membuka Serial Monitor
  Serial.println("FS15_hitung siap");

  for (int i = 1; i <= 10; i = i + 1) {
    Serial.print("hitung ");
    Serial.println(i);
  }

  Serial.println("selesai sekali di setup");
}

void loop() {
  // loop() Arduino tetap ada, tapi hari ini kosong dulu
  delay(5000);
}
</code></pre>
<p><strong>Opsional — versi while (sama hasilnya):</strong> ganti blok <code>for</code> dengan:</p>
<pre><code class="language-cpp">int n = 1;
while (n <= 10) {
  Serial.print("hitung ");
  Serial.println(n);
  n = n + 1; // jangan lupa naikkan!
}
</code></pre>
<p><strong>Cara menguji perintah di atas:</strong> uji di <strong>Arduino IDE + Serial Monitor</strong>. Sukses = baris hitung 1 sampai 10 terbaca berurutan, baud 115200. Bukan perintah Laragon / web server.</p>

<h2>Catatan singkat tentang delay</h2>
<p><code>delay(5000)</code> di <code>loop</code> hari ini hanya supaya board tidak “sibuk kosong” tanpa jeda. <strong>Peringatan:</strong> <code>delay</code> yang panjang bersifat <em>blocking</em> (board “diam” dulu). Nanti saat Wi-Fi/MQTT, jeda panjang bisa mengganggu — kita ganti pola dengan <code>millis()</code> di <strong>FS-19</strong>.</p>

<h2 id="fsiot-fw-checklist">Praktik — checklist for &amp; while</h2>
<p>Centang setiap langkah setelah kamu lakukan di meja. Target: <strong>10/10</strong>. Ada checklist interaktif di bawah.</p>
<ul id="fsiot-fw-checklist-items">
<li>Arduino IDE sudah terbuka sebelum menulis kode</li>
<li>Board ESP32 Dev Module + port sudah dipilih</li>
<li>Paham: for punya mulai, cek, kerja, naikkan</li>
<li>Paham beda loop() Arduino vs for/while</li>
<li>Sketch disimpan sebagai FS15_hitung</li>
<li>Upload berhasil — Done uploading</li>
<li>Serial Monitor terbuka, baud = 115200</li>
<li>Hitungan 1 sampai 10 terbaca berurutan</li>
<li>Sadar: while butuh variabel yang naik / kondisi berhenti</li>
<li>Sadar: delay panjang nanti diganti millis() (FS-19)</li>
</ul>
<p><strong>Cara menguji checklist:</strong> centang di browser setelah praktik di IDE. Tidak perlu <code>php artisan</code>.</p>

<h2>Kesalahan yang sering terjadi</h2>
<ul>
<li><strong><code>while (true)</code> tanpa keluar.</strong> Board terjebak di dalam. Tambah kondisi berhenti atau naikkan penhitung.</li>
<li><strong>Lupa <code>n = n + 1</code> di while.</strong> Angka tidak maju — bisa mengulang selamanya.</li>
<li><strong>Membingungkan for dengan loop().</strong> <code>loop()</code> otomatis; <code>for</code> kamu tulis sendiri.</li>
<li><strong>Baud salah.</strong> Samakan 115200 di kode dan Serial Monitor.</li>
<li><strong>Menguji di terminal web.</strong> Sketch hanya jalan di board lewat IDE.</li>
<li><strong>Melewatkan Serial karena for di setup.</strong> Tekan EN / Upload ulang, lalu buka Serial cepat.</li>
<li><strong>Indentasi berantakan.</strong> Rapikan supaya blok <code>{ }</code> mudah dicek.</li>
</ul>

<h2>Selanjutnya</h2>
<p><strong>Intinya:</strong> kalau hitungan 1–10 terbaca rapi di Serial baud 115200, FS-15 selesai.</p>
<p>Lanjut ke <strong>FS-16</strong> (fungsi — pecah program biar rapi) saat modulnya terbit.</p>
<p>Daftar modul lengkap: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $board = $this->boardFigureEn();
        $forSteps = $this->forStepsSvgEn();
        $whileCmp = $this->whileCompareSvgEn();
        $loopVs = $this->loopVsForSvgEn();
        $panel = $this->serialCountSvgEn();

        return <<<HTML
<h2>Introduction — repeating without getting lost</h2>
<p>This is article <strong>#85 (this article)</strong> · module <strong>FS-15</strong> on the <em>Full Stack IoT Developer — From Zero</em> path. In <strong>FS-14</strong> the program could choose. Today we learn to <strong>repeat</strong> with <code>for</code> and <code>while</code> — and how they differ from Arduino <code>loop()</code>.</p>
<p><strong>Analogy:</strong> <code>for</code> = climbing stairs 1 to 10. <code>while</code> = “as long as there are stairs, keep climbing”. Arduino <code>loop()</code> = an elevator that keeps moving forever.</p>
<p><strong>Prerequisites:</strong> FS-14 (you have seen Serial change because of a condition).</p>
<p><strong>How to use this article (work order):</strong></p>
<ol>
<li><strong>Open Arduino IDE</strong> first (not Laragon / a web terminal).</li>
<li>Read the difference between <code>for</code>, <code>while</code>, and <code>loop()</code>.</li>
<li>Create sketch <code>FS15_hitung</code> → <strong>Upload</strong>.</li>
<li><strong>Open Serial Monitor</strong> → baud <strong>115200</strong> → watch counts 1–10.</li>
<li>Tick the 10/10 checklist in the browser.</li>
</ol>
<p><strong>Not needed today:</strong> sensors, Wi-Fi, Laragon, <code>php artisan</code>, new wiring. Today's tools: <strong>Arduino IDE</strong> + <strong>Serial Monitor</strong> + ESP32 + USB data cable + <strong>browser</strong> (checklist).</p>

<h2>Preparation — open &amp; gather these first</h2>
<p><strong>Desk order:</strong> syntax is tested in Arduino IDE + Serial Monitor — not in a PHP terminal.</p>
<ol>
<li>Open <strong>Arduino IDE 2.x</strong>.</li>
<li><strong>ESP32 Dev Module</strong> board + COM/tty port are selected.</li>
<li>Prepare the ESP32 + USB data cable.</li>
<li>Have Serial Monitor ready at baud 115200.</li>
</ol>
<p><strong>Tools used today:</strong> Arduino IDE, Serial Monitor, ESP32, USB data, browser.</p>
<p><strong>Not used today:</strong> Laragon, <code>php artisan</code>, sensors, multimeter, PuTTY.</p>
{$board}

<h2>for — count with a clear limit</h2>
{$forSteps}
<p>Short example (not the full sketch yet): <code>for (int i = 1; i &lt;= 10; i = i + 1)</code> means start at 1, while <code>i</code> is still ≤ 10, print, then increase.</p>

<h2>while — repeat while a condition is true</h2>
{$whileCmp}
<p><strong>Important warning:</strong> <code>while (true) { … }</code> with no exit can keep the board busy inside that block. For today, always write a condition that eventually becomes false, or increase a counter variable.</p>

<h2>How this differs from Arduino loop()</h2>
{$loopVs}
<p>In today's sketch, we put <code>for</code> in <code>setup()</code> so counts 1–10 appear <strong>once</strong> after Upload — easy to read in Serial Monitor.</p>

<h2>Practice — sketch FS15_hitung</h2>
{$panel}
<p>Goal: see lines <code>count 1</code> … <code>count 10</code> in order on Serial Monitor.</p>
<ol>
<li>Arduino IDE → <strong>File → New Sketch</strong> → Save as <code>FS15_hitung</code>.</li>
<li>Replace the contents with the code below (copy it whole).</li>
<li><strong>Verify</strong> → <strong>Upload</strong> → wait for <em>Done uploading</em>.</li>
<li>Click <strong>Open Serial Monitor</strong> (top-right toolbar in IDE 2) → set <strong>115200</strong>.</li>
<li>Watch counts 1–10. Press <strong>EN</strong> if you missed them.</li>
</ol>
<pre><code class="language-cpp">// FS15_hitung — Full Stack IoT FS-15
// Print counts 1 to 10 on Serial (baud 115200).

void setup() {
  Serial.begin(115200);
  delay(1000); // time to open Serial Monitor
  Serial.println("FS15_hitung ready");

  for (int i = 1; i <= 10; i = i + 1) {
    Serial.print("count ");
    Serial.println(i);
  }

  Serial.println("done once in setup");
}

void loop() {
  // Arduino loop() still exists, but stays empty for today
  delay(5000);
}
</code></pre>
<p><strong>Optional — while version (same result):</strong> replace the <code>for</code> block with:</p>
<pre><code class="language-cpp">int n = 1;
while (n <= 10) {
  Serial.print("count ");
  Serial.println(n);
  n = n + 1; // do not forget to increase!
}
</code></pre>
<p><strong>How to test the code above:</strong> test in <strong>Arduino IDE + Serial Monitor</strong>. Success = count lines 1 through 10 in order at baud 115200. Not a Laragon / web-server command.</p>

<h2>A short note about delay</h2>
<p><code>delay(5000)</code> in <code>loop</code> today only keeps the board from spinning empty with no pause. <strong>Warning:</strong> long <code>delay</code> is <em>blocking</em> (the board “pauses”). Later with Wi-Fi/MQTT, long pauses hurt — we switch patterns to <code>millis()</code> in <strong>FS-19</strong>.</p>

<h2 id="fsiot-fw-checklist">Practice — for &amp; while checklist</h2>
<p>Tick each step after you do it at the desk. Target: <strong>10/10</strong>. An interactive checklist is below.</p>
<ul id="fsiot-fw-checklist-items">
<li>Arduino IDE is open before writing code</li>
<li>ESP32 Dev Module board + port are selected</li>
<li>I understand: for has start, check, work, step</li>
<li>I know Arduino loop() vs for/while</li>
<li>Sketch saved as FS15_hitung</li>
<li>Upload succeeded — Done uploading</li>
<li>Serial Monitor open, baud = 115200</li>
<li>Counts 1 through 10 appear in order</li>
<li>I know: while needs an increasing variable / stop condition</li>
<li>I know: long delay later becomes millis() (FS-19)</li>
</ul>
<p><strong>How to test the checklist:</strong> tick it in the browser after IDE practice. No <code>php artisan</code> required.</p>

<h2>Common mistakes</h2>
<ul>
<li><strong><code>while (true)</code> with no exit.</strong> The board gets stuck inside. Add a stop condition or increase a counter.</li>
<li><strong>Forgetting <code>n = n + 1</code> in while.</strong> The number never advances — it may loop forever.</li>
<li><strong>Mixing for with loop().</strong> <code>loop()</code> is automatic; <code>for</code> is something you write.</li>
<li><strong>Wrong baud.</strong> Match 115200 in code and Serial Monitor.</li>
<li><strong>Testing in a web terminal.</strong> Sketches only run on the board via the IDE.</li>
<li><strong>Missing Serial because for is in setup.</strong> Press EN / Upload again, then open Serial quickly.</li>
<li><strong>Messy indentation.</strong> Tidy it so <code>{ }</code> blocks are easy to check.</li>
</ul>

<h2>Next steps</h2>
<p><strong>In short:</strong> if counts 1–10 read cleanly on Serial at 115200 baud, FS-15 is done.</p>
<p>Continue to <strong>FS-16</strong> (functions — split the program so it stays tidy) when that module publishes.</p>
<p>Full module list: <a href="/belajar/fullstack-iot">/belajar/fullstack-iot</a>.</p>
HTML;
    }
}
