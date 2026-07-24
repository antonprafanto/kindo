<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article53Seeder extends Seeder
{
    public function run(): void
    {
        // Tombstone slug Flask-era (jangan hidup lagi)
        $oldSlug = 'http-rest-kontrak-stub-flask-oop';
        $old = Article::withTrashed()->where('slug', $oldSlug)->first();
        if ($old) {
            $old->status = 'draft';
            $old->is_featured = false;
            $old->save();
            if (! $old->trashed()) {
                $old->delete();
            }
        }

        $admin = User::first();
        $webCat = Category::where('slug', 'web-development')->first()
            ?? Category::where('slug', 'programming')->first();

        if (! $admin || ! $webCat) {
            throw new \RuntimeException('User atau kategori web-development/programming tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'mengenal-oop-cara-berpikir-dengan-objek-php';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        foreach ([
            'php' => 'php',
            'oop' => 'oop',
            'oop-class' => 'oop-class',
            'web' => 'web',
        ] as $tagSlug => $tagName) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagName]);
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $webCat->id,
                'title'           => 'Mengenal OOP: Cara Berpikir dengan Objek (PHP)',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'Mengenal OOP PHP — Cara Berpikir dengan Objek untuk Pemula',
                'seo_description' => 'Pembuka Seri 4: soft-landing PHP, prosedural vs class, class vs object, dan kenapa OOP penting sebelum Laravel — berbahasa Indonesia.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['php', 'oop', 'oop-class', 'web'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel ke-53 berhasil dipublish: '.$article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — Seri 4 dimulai dari cara berpikir</h2>
<p>Seri 3 di Koding Indonesia sudah mengajarkan OOP dengan <a href="/artikel/mengenal-oop-cara-berpikir-dengan-objek-python">Python (#40)</a> sampai pintu web stub di <a href="/artikel/oop-flask-fastapi-class-api">Flask/FastAPI (#52)</a>. Seri 4 bergeser ke jalur yang selaras stack site ini: <strong>PHP dulu (OOP)</strong>, baru <strong>Laravel</strong> untuk API nyata.</p>
<p>Artikel ini adalah <strong>#53 (ini)</strong> — pembuka jembatan OOP PHP. Kamu belum wajib pernah menulis <code>class</code> di PHP. Yang dibutuhkan: rasa ingin paham kenapa objek membantu sebelum menumpuk route Laravel.</p>

<blockquote>
  <p><strong>Prasyarat ringan:</strong> pernah menulis variabel dan fungsi (bahasa apa saja). Kalau sudah selesai Seri 3 Python, konsepnya sama — sintaksnya yang beda. Domain contoh tetap <strong>perpustakaan mini</strong>.</p>
</blockquote>

<h2>Soft-landing PHP (5 menit)</h2>
<p>Contoh di Seri 4 memakai <strong>PHP 8.2+</strong> (di mesin Kindo lokal sering 8.4). Cek di terminal:</p>

<pre><code class="language-bash">php --version
# contoh: PHP 8.4.x
</code></pre>

<p>Dua sintaks yang cukup untuk membaca artikel ini:</p>

<pre><code class="language-php">&lt;?php
// variabel
$judul = "Dasar OOP";
$halaman = 120;

// fungsi
function sapa(string $nama): string
{
    return "Halo, {$nama}!";
}

echo sapa("Anton"), PHP_EOL;
</code></pre>

<p>Output:</p>
<pre><code>Halo, Anton!
</code></pre>

<p>Catatan singkat: <code>string $nama): string</code> adalah <strong>type hint</strong> — petunjuk tipe data (di sini: teks). Belum wajib dihafal; cukup tahu artinya “fungsi ini menerima dan mengembalikan string”. <code>PHP_EOL</code> = baris baru di terminal.</p>
<p>Simpan berkas <code>.php</code> lalu jalankan: <code>php nama_file.php</code>. Editor: VS Code + ekstensi PHP sudah cukup.</p>

<h2>Masalah yang OOP coba selesaikan</h2>
<p>Versi <strong>prosedural</strong> (data + fungsi terpisah) cepat berantakan saat aturan buku bertambah:</p>

<pre><code class="language-php">&lt;?php
$bukuA = ["judul" =&gt; "Belajar PHP", "penulis" =&gt; "Sari", "tahun" =&gt; 2024];
$bukuB = ["judul" =&gt; "Laravel Praktis", "penulis" =&gt; "Budi", "tahun" =&gt; 2025];

function infoBuku(array $buku): string
{
    return $buku["judul"] . " oleh " . $buku["penulis"] . " (" . $buku["tahun"] . ")";
}

function pinjam(array $buku, string $anggota): void
{
    echo $anggota . " meminjam " . $buku["judul"], PHP_EOL;
}

echo infoBuku($bukuA), PHP_EOL;
pinjam($bukuB, "Rina");
</code></pre>

<p>Output:</p>
<pre><code>Belajar PHP oleh Sari (2024)
Rina meminjam Laravel Praktis
</code></pre>

<p>Satu typo key (<code>"Judul"</code> vs <code>"judul"</code>) sudah cukup bikin bug diam-diam. Semakin banyak fitur, semakin banyak fungsi yang harus “mengingat” bentuk array yang sama.</p>
<p><strong>OOP</strong> menggabungkan data dan perilaku terkait ke dalam <strong>objek</strong>. Class = cetakan; object = barang konkret.</p>

<h2>Class vs object — cetakan dan barangnya</h2>
<figure role="img" aria-label="Diagram class Buku sebagai cetakan dan dua object bukuA bukuB" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 220" style="display:block;max-width:720px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="oop53phpArrow" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/></marker>
  </defs>
  <rect x="0" y="0" width="720" height="220" fill="#F5F5F0" rx="6"/>
  <text x="360" y="28" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">class = cetakan · object = instance</text>
  <rect x="40" y="60" width="200" height="120" rx="6" fill="#1a1a1a" stroke="#000" stroke-width="2.5"/>
  <text x="140" y="100" text-anchor="middle" fill="#fff" font-size="16" font-weight="700">class Buku</text>
  <text x="140" y="130" text-anchor="middle" fill="#E8F4FF" font-size="12">judul, penulis</text>
  <text x="140" y="155" text-anchor="middle" fill="#E8F4FF" font-size="12">info()</text>
  <rect x="340" y="55" width="150" height="55" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="415" y="88" text-anchor="middle" fill="#1a1a1a" font-size="13" font-weight="700">$bukuA</text>
  <rect x="340" y="130" width="150" height="55" rx="6" fill="#FFF3E8" stroke="#000" stroke-width="2.5"/>
  <text x="415" y="163" text-anchor="middle" fill="#1a1a1a" font-size="13" font-weight="700">$bukuB</text>
  <line x1="240" y1="100" x2="340" y2="82" stroke="#2979FF" stroke-width="2.5" marker-end="url(#oop53phpArrow)"/>
  <line x1="240" y1="140" x2="340" y2="157" stroke="#2979FF" stroke-width="2.5" marker-end="url(#oop53phpArrow)"/>
  <text x="560" y="90" fill="#2D3748" font-size="12">data beda</text>
  <text x="560" y="165" fill="#2D3748" font-size="12">perilaku sama</text>
</svg>
<figcaption style="margin-top:.75rem;color:#2D3748;font-size:.95rem">Satu class, banyak object — pola yang sama nanti dipakai model/service di Laravel.</figcaption>
</figure>

<pre><code class="language-php">&lt;?php
class Buku
{
    public string $judul;
    public string $penulis;

    public function __construct(string $judul, string $penulis)
    {
        $this-&gt;judul = $judul;
        $this-&gt;penulis = $penulis;
    }

    public function info(): string
    {
        return "{$this-&gt;judul} oleh {$this-&gt;penulis}";
    }
}

$bukuA = new Buku("Belajar PHP", "Sari");
$bukuB = new Buku("Laravel Praktis", "Budi");
echo $bukuA-&gt;info(), PHP_EOL;
echo $bukuB-&gt;info(), PHP_EOL;
</code></pre>

<p>Output:</p>
<pre><code>Belajar PHP oleh Sari
Laravel Praktis oleh Budi
</code></pre>

<p><code>new Buku(...)</code> membuat object. Method <code>info()</code> dipanggil dengan <code>-&gt;</code> (bukan titik seperti Python).</p>

<h2>Kenapa ini penting sebelum Laravel?</h2>
<table>
  <thead>
    <tr>
      <th>Tanpa OOP</th>
      <th>Gejala di web</th>
      <th>Dengan OOP ringan</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Array “liar” di route (URL handler)</td>
      <td>Controller jadi gudang logika</td>
      <td>Class <code>Buku</code> / service memegang aturan</td>
    </tr>
    <tr>
      <td>Copy-paste validasi</td>
      <td>Bug beda di tiap endpoint (alamat API)</td>
      <td>Satu tempat ubah, banyak pemakai</td>
    </tr>
    <tr>
      <td>Langsung model database “aja”</td>
      <td>Bingung mana aturan bisnis vs framework</td>
      <td>Dulu paham object, baru map ke model</td>
    </tr>
  </tbody>
</table>
<p>Laravel nanti menyediakan route, request, dan akses data. Yang tidak diganti framework: <strong>cara kamu memotong masalah jadi objek</strong>.</p>

<h2>Pola Dasar — berpikir objek di PHP</h2>
<figure style="margin:1.5rem 0;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1.25rem;color:#1a1a1a" aria-label="Lima langkah mengenal OOP PHP">
<ol style="list-style:none;padding:0;margin:0;color:#1a1a1a">
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Tentukan benda</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Buku, anggota, peminjaman — bukan “halaman PHP acak”.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Tulis class sebagai cetakan</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Property = data · method = perilaku.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Buat object dengan <code>new</code></strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Isi lewat constructor agar object lahir siap pakai.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Panggil method, bukan utak-atik array</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem"><code>$buku-&gt;info()</code> lebih jelas daripada <code>$buku["judul"]</code> tersebar.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">5</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Baru bawa ke framework</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Berikutnya: <a href="/artikel/oop-php-property-method-constructor">property/method/constructor (#54)</a> — lalu Laravel.</span>
    </div>
  </li>
</ol>
</figure>

<h2>Kode lengkap — <code>oop_php_dasar.php</code></h2>
<p>Simpan dan jalankan: <code>php oop_php_dasar.php</code>. Baris <code>declare(strict_types=1);</code> membuat PHP lebih ketat soal tipe — aman diabaikan dulu; yang penting pola <code>class</code> + <code>new</code> + method.</p>

<pre><code class="language-php">&lt;?php
/**
 * OOP PHP dasar (Seri 4 #53).
 * Lanjut: property/method/constructor lebih dalam di artikel berikutnya.
 */

declare(strict_types=1);

class Buku
{
    public string $judul;
    public string $penulis;
    public int $tahun;

    public function __construct(string $judul, string $penulis, int $tahun)
    {
        $this-&gt;judul = $judul;
        $this-&gt;penulis = $penulis;
        $this-&gt;tahun = $tahun;
    }

    public function info(): string
    {
        return "{$this-&gt;judul} oleh {$this-&gt;penulis} ({$this-&gt;tahun})";
    }
}

function demo(): void
{
    $koleksi = [
        new Buku("Belajar PHP", "Sari", 2024),
        new Buku("Laravel Praktis", "Budi", 2025),
    ];

    foreach ($koleksi as $buku) {
        echo $buku-&gt;info(), PHP_EOL;
    }

    echo "jumlah=", count($koleksi), PHP_EOL;
}

demo();
</code></pre>

<p>Output yang diharapkan:</p>
<pre><code>Belajar PHP oleh Sari (2024)
Laravel Praktis oleh Budi (2025)
jumlah=2
</code></pre>

<h2>Kesalahan umum</h2>
<table>
  <thead>
    <tr>
      <th>Gejala</th>
      <th>Penyebab tipikal</th>
      <th>Perbaikan</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><code>php</code> tidak ketemu</td>
      <td>PATH / XAMPP belum siap</td>
      <td>Pakai full path PHP atau terminal yang sudah dikonfigurasi</td>
    </tr>
    <tr>
      <td>Pakai <code>.</code> seperti Python</td>
      <td>Salah operator</td>
      <td>Object di PHP: <code>-&gt;</code> · static: <code>::</code></td>
    </tr>
    <tr>
      <td>Lupa <code>&lt;?php</code></td>
      <td>File dianggap teks</td>
      <td>Mulai berkas dengan tag pembuka</td>
    </tr>
    <tr>
      <td>Array tetap dipakai di mana-mana</td>
      <td>Class hanya “hiasan”</td>
      <td>Pindahkan aturan ke method object</td>
    </tr>
    <tr>
      <td>Langsung buka Laravel</td>
      <td>Loncat fondasi</td>
      <td>Selesaikan jembatan OOP PHP dulu (tiga artikel sebelum Laravel)</td>
    </tr>
  </tbody>
</table>

<h2>Latihan singkat</h2>
<ol>
  <li>Tambah property <code>isbn</code> (string) ke <code>Buku</code> dan tampilkan di <code>info()</code>.</li>
  <li>Buat object ketiga di <code>demo()</code> lalu pastikan <code>jumlah=</code> menjadi 3.</li>
  <li>Tuliskan di kertas: tiga “benda” di aplikasi perpustakaan yang layak jadi class (selain Buku).</li>
</ol>

<h2>FAQ singkat</h2>
<p><strong>Harus hapus pengetahuan Python OOP?</strong><br>Tidak. Konsep class/object sama. Seri 4 melatih sintaks PHP agar jalan ke Laravel mulus.</p>
<p><strong>Apakah ini sudah encapsulation lengkap?</strong><br>Belum. Visibility <code>private</code>/<code>protected</code> dibahas setelah property &amp; constructor.</p>
<p><strong>Kapan mulai Laravel?</strong><br>Setelah jembatan OOP PHP selesai (pengantar -&gt; property/constructor -&gt; visibility). Sabar dulu — fondasi objek lebih dulu, framework belakangan.</p>
<p><strong>Lanjut ke mana?</strong><br>Berikutnya: <a href="/artikel/oop-php-property-method-constructor">property, method, dan constructor lebih dalam di PHP (#54)</a>.</p>

<h2>Kesimpulan &amp; langkah berikutnya</h2>
<p>OOP bukan soal “pakai class biar keren”. Ia cara merapikan data + perilaku supaya siap dibawa ke web framework tanpa controller jadi tempat sampah.</p>
<p>Artikel ini adalah <strong>#53 (ini)</strong> — pembuka Seri 4 setelah pintu OOP web Python di <a href="/artikel/oop-flask-fastapi-class-api">Flask/FastAPI (#52)</a>. Lanjut: <a href="/artikel/oop-php-property-method-constructor">property &amp; constructor PHP (#54)</a>.</p>

<blockquote>
  <p><strong>Seri 4 progress:</strong> langkah <strong>#53 (ini)</strong> · 8/8 Capstone Laravel selesai · jembatan OOP PHP 1/3 · prasyarat konsep: <a href="/artikel/mengenal-oop-cara-berpikir-dengan-objek-python">OOP Python (#40)</a> opsional · <a href="/artikel/oop-flask-fastapi-class-api">Flask/FastAPI (#52)</a> LIVE · lanjut <a href="/artikel/oop-php-property-method-constructor">property &amp; constructor (#54)</a>.</p>
</blockquote>
HTML;
    }
}
