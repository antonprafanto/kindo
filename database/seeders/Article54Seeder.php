<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article54Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $webCat = Category::where('slug', 'web-development')->first()
            ?? Category::where('slug', 'programming')->first();

        if (! $admin || ! $webCat) {
            throw new \RuntimeException('User atau kategori web-development/programming tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'oop-php-property-method-constructor';

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
                'title'           => 'Property, Method & Constructor di PHP',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'Property, Method & Constructor PHP — OOP untuk Pemula',
                'seo_description' => 'Lanjut OOP PHP: property (data), method (perilaku), constructor (__construct), dan type hint — berbahasa Indonesia, ramah awam.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['php', 'oop', 'oop-class', 'web'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel ke-54 berhasil dipublish: '.$article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — dari “ada class” ke “isi class”</h2>
<p>Di <a href="/artikel/mengenal-oop-cara-berpikir-dengan-objek-php">Mengenal OOP PHP (#53)</a> kamu sudah melihat class sebagai cetakan dan <code>new</code> sebagai cara membuat object. Artikel ini adalah <strong>#54 (ini)</strong> — langkah kedua jembatan OOP PHP sebelum Laravel.</p>
<p>Fokusnya sederhana: <strong>property</strong> (data), <strong>method</strong> (perilaku), dan <strong>constructor</strong> (cara object “lahir” siap pakai). Kalau analoginya buku: property = judul &amp; penulis; method = “tampilkan info”; constructor = mengisi data saat buku dicatat pertama kali.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> sudah baca <a href="/artikel/mengenal-oop-cara-berpikir-dengan-objek-php">Mengenal OOP PHP (#53)</a> atau setidaknya pernah membuat <code>class</code> + <code>new</code> di PHP. Domain tetap <strong>perpustakaan mini</strong>.</p>
</blockquote>

<h2>Tiga kata yang sering campur</h2>
<table>
  <thead>
    <tr>
      <th>Istilah</th>
      <th>Arti awam</th>
      <th>Contoh di <code>Buku</code></th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>Property</strong></td>
      <td>Data yang “nempel” di object</td>
      <td><code>$judul</code>, <code>$penulis</code></td>
    </tr>
    <tr>
      <td><strong>Method</strong></td>
      <td>Perilaku / pertanyaan ke object</td>
      <td><code>info()</code>, <code>ringkas()</code></td>
    </tr>
    <tr>
      <td><strong>Constructor</strong></td>
      <td>Langkah otomatis saat <code>new</code></td>
      <td><code>__construct(...)</code></td>
    </tr>
  </tbody>
</table>
<p>Jangan hafal dulu. Baca tabel di atas sekali, lalu ikuti contoh di bawah — otak akan mengaitkan sendiri.</p>

<h2>Property — data yang nempel</h2>
<p>Property ditulis di dalam class. Setelah object dibuat, kamu mengaksesnya dengan <code>-&gt;</code> (bukan titik seperti Python).</p>

<pre><code class="language-php">&lt;?php
class Buku
{
    public string $judul;
    public string $penulis;
}

$buku = new Buku();
$buku-&gt;judul = "Belajar PHP";
$buku-&gt;penulis = "Sari";

echo $buku-&gt;judul, " oleh ", $buku-&gt;penulis, PHP_EOL;
</code></pre>

<p>Output:</p>
<pre><code>Belajar PHP oleh Sari
</code></pre>

<p><code>public</code> artinya “boleh diakses dari luar class”. Nanti di artikel berikutnya kita pelajari kenapa kadang data perlu disembunyikan (<code>private</code>). Untuk sekarang, <code>public</code> cukup agar pola kelihatan jelas.</p>
<p>Type hint <code>string $judul</code> = property ini diharapkan berisi teks. Kalau kamu iseng mengisi angka, PHP 8+ akan protes — itu membantu, bukan mengganggu.</p>
<p><strong>Kenapa contoh pertama belum pakai constructor?</strong> Supaya kamu lihat dulu “property itu kotak data”. Sebentar lagi kita rapikan pengisiannya lewat <code>__construct</code> agar object tidak lahir kosong.</p>

<h2>Method — minta object melakukan sesuatu</h2>
<p>Method adalah fungsi di dalam class. Ia bisa membaca property lewat <code>$this-&gt;...</code> (<code>$this</code> = “object yang sedang berbicara”).</p>

<pre><code class="language-php">&lt;?php
class Buku
{
    public string $judul;
    public string $penulis;

    public function info(): string
    {
        return "{$this-&gt;judul} oleh {$this-&gt;penulis}";
    }
}

$buku = new Buku();
$buku-&gt;judul = "Laravel Praktis";
$buku-&gt;penulis = "Budi";
echo $buku-&gt;info(), PHP_EOL;
</code></pre>

<p>Output:</p>
<pre><code>Laravel Praktis oleh Budi
</code></pre>

<p>Kenapa tidak terus tulis <code>$buku-&gt;judul</code> di mana-mana? Karena aturan tampilan (format, urutan, tambahan tahun) cukup di <strong>satu</strong> method. Ubah sekali, semua pemanggil ikut rapi.</p>

<figure role="img" aria-label="Diagram property method dan this pada class Buku" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 240" style="display:block;max-width:720px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="oop54phpArrow" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/></marker>
  </defs>
  <rect x="0" y="0" width="720" height="240" fill="#F5F5F0" rx="6"/>
  <text x="360" y="28" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">property = data · method = perilaku · $this = object ini</text>
  <rect x="40" y="55" width="260" height="150" rx="6" fill="#1a1a1a" stroke="#000" stroke-width="2.5"/>
  <text x="170" y="90" text-anchor="middle" fill="#fff" font-size="16" font-weight="700">class Buku</text>
  <text x="170" y="120" text-anchor="middle" fill="#E8F4FF" font-size="13">property: judul, penulis</text>
  <text x="170" y="148" text-anchor="middle" fill="#E8F4FF" font-size="13">method: info()</text>
  <text x="170" y="176" text-anchor="middle" fill="#A0AEC0" font-size="12">$this-&gt;judul di dalam method</text>
  <rect x="400" y="70" width="260" height="120" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="530" y="110" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">$buku = new Buku(...)</text>
  <text x="530" y="140" text-anchor="middle" fill="#2D3748" font-size="13">panggil: $buku-&gt;info()</text>
  <text x="530" y="168" text-anchor="middle" fill="#2D3748" font-size="12">bukan utak-atik array</text>
  <line x1="300" y1="130" x2="400" y2="130" stroke="#2979FF" stroke-width="2.5" marker-end="url(#oop54phpArrow)"/>
</svg>
<figcaption style="margin-top:.75rem;color:#2D3748;font-size:.95rem">Class menyimpan resep; object menyimpan data konkret. Method memakai <code>$this</code> untuk membaca data object itu sendiri.</figcaption>
</figure>

<h2>Constructor — lahir sudah lengkap</h2>
<p>Mengisi property satu per satu setelah <code>new</code> mudah lupa. Constructor (<code>__construct</code>) jalan <strong>otomatis</strong> saat <code>new Buku(...)</code>.</p>

<pre><code class="language-php">&lt;?php
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

$buku = new Buku("Belajar PHP", "Sari", 2024);
echo $buku-&gt;info(), PHP_EOL;
</code></pre>

<p>Output:</p>
<pre><code>Belajar PHP oleh Sari (2024)
</code></pre>

<p>Baca perlahan: argumen di <code>new Buku(...)</code> masuk ke parameter constructor, lalu disimpan ke property lewat <code>$this-&gt;...</code>. Object lahir sudah punya data — siap dipanggil method-nya.</p>

<h2>Type hint — petunjuk tipe (lanjutan dari pengantar)</h2>
<p>Di <a href="/artikel/mengenal-oop-cara-berpikir-dengan-objek-php">Mengenal OOP PHP (#53)</a> kita bilang type hint = petunjuk. Di sini sedikit lebih dalam, tetap tanpa drama:</p>
<ul>
  <li><code>string</code> = teks</li>
  <li><code>int</code> = bilangan bulat (tahun, jumlah halaman)</li>
  <li><code>: string</code> setelah method = nilai balik berupa teks</li>
  <li><code>: void</code> = method tidak mengembalikan nilai (sering untuk “cetak saja”)</li>
</ul>
<p>Kamu belum wajib hafal semua tipe. Cukup biasakan menulisnya di constructor dan method penting — editor dan PHP akan membantu menangkap salah isi lebih awal.</p>

<h2>Pola Dasar — property, method, constructor</h2>
<figure style="margin:1.5rem 0;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1.25rem;color:#1a1a1a" aria-label="Lima langkah property method constructor PHP">
<ol style="list-style:none;padding:0;margin:0;color:#1a1a1a">
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Tulis property dulu</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Data apa yang wajib ada di setiap Buku?</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Isi lewat constructor</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem"><code>new Buku(...)</code> = lahir lengkap, bukan object “bolong”.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Bungkus aturan di method</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem"><code>info()</code> / <code>ringkas()</code> — jangan copy-paste format di mana-mana.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Pakai <code>$this-&gt;</code> di dalam class</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Di luar class: <code>$buku-&gt;info()</code>. Di dalam method: <code>$this-&gt;judul</code>.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">5</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Baru pikir visibility</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Artikel berikutnya: <code>private</code> / composition ringan — siap ke Laravel.</span>
    </div>
  </li>
</ol>
</figure>

<h2>Kode lengkap — <code>oop_php_property.php</code></h2>
<p>Simpan dan jalankan: <code>php oop_php_property.php</code>. <code>declare(strict_types=1);</code> membuat PHP lebih ketat soal tipe — boleh dianggap “sabuk pengaman”, bukan materi hafalan.</p>

<pre><code class="language-php">&lt;?php
/**
 * Property, method, constructor (Seri 4 #54).
 * Lanjut: visibility &amp; composition di artikel berikutnya.
 */

declare(strict_types=1);

class Buku
{
    public string $judul;
    public string $penulis;
    public int $tahun;
    public string $isbn;

    public function __construct(string $judul, string $penulis, int $tahun, string $isbn)
    {
        $this-&gt;judul = $judul;
        $this-&gt;penulis = $penulis;
        $this-&gt;tahun = $tahun;
        $this-&gt;isbn = $isbn;
    }

    public function info(): string
    {
        return "{$this-&gt;judul} oleh {$this-&gt;penulis} ({$this-&gt;tahun})";
    }

    public function ringkas(): string
    {
        return "{$this-&gt;judul} [{$this-&gt;isbn}]";
    }
}

function demo(): void
{
    $koleksi = [
        new Buku("Belajar PHP", "Sari", 2024, "978-0001"),
        new Buku("Laravel Praktis", "Budi", 2025, "978-0002"),
    ];

    foreach ($koleksi as $buku) {
        echo $buku-&gt;info(), PHP_EOL;
        echo $buku-&gt;ringkas(), PHP_EOL;
    }

    echo "jumlah=", count($koleksi), PHP_EOL;
}

demo();
</code></pre>

<p>Output yang diharapkan:</p>
<pre><code>Belajar PHP oleh Sari (2024)
Belajar PHP [978-0001]
Laravel Praktis oleh Budi (2025)
Laravel Praktis [978-0002]
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
      <td>Property null / kosong</td>
      <td>Lupa isi setelah <code>new</code></td>
      <td>Pindahkan pengisian ke <code>__construct</code></td>
    </tr>
    <tr>
      <td><code>Undefined property</code></td>
      <td>Typo nama property</td>
      <td>Samakan ejaan di class, constructor, dan pemakaian</td>
    </tr>
    <tr>
      <td><code>$this</code> di luar class</td>
      <td>Salah tempat</td>
      <td><code>$this</code> hanya di dalam method class</td>
    </tr>
    <tr>
      <td>Pakai <code>.</code> seperti Python</td>
      <td>Salah operator</td>
      <td>Object PHP: <code>-&gt;</code></td>
    </tr>
    <tr>
      <td>Method terlalu gemuk</td>
      <td>Semua logika digabung</td>
      <td>Pecah: <code>info()</code> vs <code>ringkas()</code> dulu</td>
    </tr>
  </tbody>
</table>

<h2>Latihan singkat</h2>
<ol>
  <li>Tambah property <code>int $halaman</code> ke constructor dan tampilkan di <code>info()</code>.</li>
  <li>Buat method <code>label(): string</code> yang mengembalikan hanya <code>$this-&gt;judul</code>.</li>
  <li>Tambah object ketiga di <code>demo()</code> dan pastikan <code>jumlah=</code> menjadi 3.</li>
</ol>

<h2>FAQ singkat</h2>
<p><strong>Harus selalu pakai constructor?</strong><br>Untuk object yang “wajib punya data”, ya — lebih aman. Object kosong lalu diisi manual mudah terlupakan.</p>
<p><strong>Apa bedanya property dan variabel biasa?</strong><br>Variabel biasa hidup di satu tempat (fungsi/skrip). Property hidup <strong>bersama object</strong> selama object itu ada.</p>
<p><strong>Kapan belajar <code>private</code>?</strong><br>Lihat <a href="/artikel/oop-php-visibility-composition">Visibility &amp; Composition (#55)</a>. Di sini kita sengaja pakai <code>public</code> agar pola kelihatan dulu.</p>
<p><strong>Lanjut ke mana?</strong><br>Berikutnya: <a href="/artikel/oop-php-visibility-composition">Visibility &amp; Composition Ringan di PHP (#55)</a> — pintu terakhir sebelum Laravel.</p>

<h2>Kesimpulan &amp; langkah berikutnya</h2>
<p>Property = data. Method = perilaku. Constructor = lahir lengkap. Tiga ide ini yang akan kamu bawa ke Form Request, service, dan model di Laravel nanti — bedanya nanti kebanyakan “di mana file-nya”, bukan “apa ide-nya”.</p>
<p>Artikel ini adalah <strong>#54 (ini)</strong> — lanjut dari <a href="/artikel/mengenal-oop-cara-berpikir-dengan-objek-php">OOP PHP pengantar (#53)</a>.</p>

<blockquote>
  <p><strong>Seri 4 progress:</strong> langkah <strong>#54 (ini)</strong> · 4/8 menuju Capstone Laravel · jembatan OOP PHP 2/3 · prasyarat: <a href="/artikel/mengenal-oop-cara-berpikir-dengan-objek-php">OOP PHP pengantar (#53)</a> LIVE. Berikutnya: <a href="/artikel/oop-php-visibility-composition">Visibility &amp; Composition (#55)</a>.</p>
</blockquote>
HTML;
    }
}
