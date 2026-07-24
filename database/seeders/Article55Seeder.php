<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article55Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $webCat = Category::where('slug', 'web-development')->first()
            ?? Category::where('slug', 'programming')->first();

        if (! $admin || ! $webCat) {
            throw new \RuntimeException('User atau kategori web-development/programming tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'oop-php-visibility-composition';

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
                'title'           => 'Visibility & Composition Ringan di PHP',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'Visibility & Composition PHP — OOP untuk Pemula',
                'seo_description' => 'Jembatan terakhir OOP PHP sebelum Laravel: public vs private, kenapa data disembunyikan, dan composition ringan (object memakai object) — berbahasa Indonesia.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['php', 'oop', 'oop-class', 'web'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel ke-55 berhasil dipublish: '.$article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — pintu terakhir sebelum Laravel</h2>
<p>Di <a href="/artikel/oop-php-property-method-constructor">Property, Method &amp; Constructor (#54)</a> kamu sudah bisa membuat object <code>Buku</code> yang lahir lengkap. Artikel ini adalah <strong>#55 (ini)</strong> — langkah ketiga (dan terakhir) jembatan OOP PHP sebelum Laravel.</p>
<p>Dua ide saja yang kita rapikan: <strong>visibility</strong> (siapa boleh menyentuh data) dan <strong>composition</strong> (object memakai object lain). Analoginya: laci buku boleh dikunci (<code>private</code>), dan perpustakaan <strong>memakai</strong> banyak buku — bukan “jadi buku”.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> sudah baca <a href="/artikel/oop-php-property-method-constructor">Property, Method &amp; Constructor (#54)</a> — paham <code>class</code>, property, method, dan <code>__construct</code>. Domain tetap <strong>perpustakaan mini</strong>.</p>
</blockquote>

<h2>Visibility — siapa boleh menyentuh?</h2>
<table>
  <thead>
    <tr>
      <th>Kata kunci</th>
      <th>Arti awam</th>
      <th>Kapan dipakai di artikel ini</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><code>public</code></td>
      <td>Boleh disentuh dari luar class</td>
      <td>Method yang memang untuk dipanggil pemakai</td>
    </tr>
    <tr>
      <td><code>private</code></td>
      <td>Hanya class sendiri yang boleh</td>
      <td>Property penting agar tidak diacak sembarangan</td>
    </tr>
    <tr>
      <td><code>protected</code></td>
      <td>Untuk class anak (inheritance)</td>
      <td>Disebut saja — belum kita dalami di sini</td>
    </tr>
  </tbody>
</table>
<p>Jangan hafal semua dulu. Fokus: <code>public</code> vs <code>private</code>. Itu sudah cukup untuk jembatan ke Laravel.</p>

<h2>Masalah kalau semua public</h2>
<p>Property <code>public</code> nyaman, tapi mudah “dirusak” dari luar:</p>

<pre><code class="language-php">&lt;?php
class Buku
{
    public string $judul;
    public int $tahun;

    public function __construct(string $judul, int $tahun)
    {
        $this-&gt;judul = $judul;
        $this-&gt;tahun = $tahun;
    }
}

$buku = new Buku("Belajar PHP", 2024);
$buku-&gt;tahun = -99; // oops — tahun mustahil, tapi PHP diam saja
echo $buku-&gt;tahun, PHP_EOL;
</code></pre>

<p>Output:</p>
<pre><code>-99
</code></pre>

<p>Object tetap “hidup”, tapi datanya sudah tidak masuk akal. Di aplikasi web, bug seperti ini sering datang dari form/API yang mengisi field sembarangan.</p>

<h2>Private + method — laci terkunci</h2>
<p>Kunci property dengan <code>private</code>, lalu sediakan method yang mengontrol perubahan:</p>

<pre><code class="language-php">&lt;?php
class Buku
{
    private string $judul;
    private int $tahun;

    public function __construct(string $judul, int $tahun)
    {
        $this-&gt;judul = $judul;
        $this-&gt;setTahun($tahun);
    }

    public function info(): string
    {
        return "{$this-&gt;judul} ({$this-&gt;tahun})";
    }

    public function setTahun(int $tahun): void
    {
        if ($tahun &lt; 1900 || $tahun &gt; 2100) {
            throw new InvalidArgumentException("Tahun tidak masuk akal: {$tahun}");
        }
        $this-&gt;tahun = $tahun;
    }
}

$buku = new Buku("Belajar PHP", 2024);
echo $buku-&gt;info(), PHP_EOL;
</code></pre>

<p>Output:</p>
<pre><code>Belajar PHP (2024)
</code></pre>

<p>Dari luar class, <code>$buku-&gt;tahun = -99</code> akan ditolak PHP (<code>Cannot access private property</code>). Perubahan tahun harus lewat <code>setTahun()</code> — satu pintu, satu aturan.</p>
<p>Kalau kamu kirim tahun mustahil lewat pintu itu — misalnya <code>$buku-&gt;setTahun(1800)</code> — PHP akan melempar <code>InvalidArgumentException</code> (pesan error yang kita tulis sendiri). Itu sengaja: lebih baik gagal keras daripada menyimpan data bohong.</p>
<p><strong>Awam:</strong> <code>private</code> bukan “rahasia negara”. Ia artinya: “kalau mau ubah, lewat pintu yang sudah kita sediakan.”</p>

<figure role="img" aria-label="Diagram public method dan private property pada class Buku" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 240" style="display:block;max-width:720px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="oop55phpArrow" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/></marker>
  </defs>
  <rect x="0" y="0" width="720" height="240" fill="#F5F5F0" rx="6"/>
  <text x="360" y="28" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">public = pintu · private = laci dalam</text>
  <rect x="50" y="55" width="280" height="150" rx="6" fill="#1a1a1a" stroke="#000" stroke-width="2.5"/>
  <text x="190" y="90" text-anchor="middle" fill="#fff" font-size="16" font-weight="700">class Buku</text>
  <text x="190" y="122" text-anchor="middle" fill="#E8F4FF" font-size="13">private $judul, $tahun</text>
  <text x="190" y="150" text-anchor="middle" fill="#90CDF4" font-size="13">public info() / setTahun()</text>
  <text x="190" y="178" text-anchor="middle" fill="#A0AEC0" font-size="12">aturan hidup di method</text>
  <rect x="400" y="70" width="260" height="120" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="530" y="110" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">kode di luar class</text>
  <text x="530" y="140" text-anchor="middle" fill="#2D3748" font-size="13">boleh: $buku-&gt;info()</text>
  <text x="530" y="168" text-anchor="middle" fill="#2D3748" font-size="12">tidak: $buku-&gt;tahun = -99</text>
  <line x1="330" y1="130" x2="400" y2="130" stroke="#2979FF" stroke-width="2.5" marker-end="url(#oop55phpArrow)"/>
</svg>
<figcaption style="margin-top:.75rem;color:#2D3748;font-size:.95rem">Pemakai object hanya lewat method <code>public</code>. Data penting tetap di laci <code>private</code>.</figcaption>
</figure>

<h2>Composition — object memakai object</h2>
<p><strong>Composition</strong> artinya: sebuah object <strong>memiliki / memakai</strong> object lain. Bukan “mewarisi” (inheritance) — itu topik lain dan sering keburu rumit untuk awam.</p>
<p>Contoh: <code>Katalog</code> menyimpan banyak <code>Buku</code>. Katalog bukan buku; ia <strong>mengumpulkan</strong> buku.</p>

<pre><code class="language-php">&lt;?php
class Buku
{
    private string $judul;

    public function __construct(string $judul)
    {
        $this-&gt;judul = $judul;
    }

    public function judul(): string
    {
        return $this-&gt;judul;
    }
}

class Katalog
{
    /** @var list&lt;Buku&gt; daftar Buku di dalam katalog */
    private array $koleksi = [];

    public function tambah(Buku $buku): void
    {
        $this-&gt;koleksi[] = $buku;
    }

    public function jumlah(): int
    {
        return count($this-&gt;koleksi);
    }

    public function daftar(): string
    {
        $baris = [];
        foreach ($this-&gt;koleksi as $buku) {
            $baris[] = "- ".$buku-&gt;judul();
        }
        return implode(PHP_EOL, $baris);
    }
}

$katalog = new Katalog();
$katalog-&gt;tambah(new Buku("Belajar PHP"));
$katalog-&gt;tambah(new Buku("Laravel Praktis"));
echo "jumlah=", $katalog-&gt;jumlah(), PHP_EOL;
echo $katalog-&gt;daftar(), PHP_EOL;
</code></pre>

<p>Output:</p>
<pre><code>jumlah=2
- Belajar PHP
- Laravel Praktis
</code></pre>

<p>Ini pola yang nanti sering muncul di Laravel: <strong>controller tipis</strong> (pintu HTTP — terima request, kirim jawaban) dan <strong>service / class domain</strong> (tempat aturan bisnis). Ide-nya sama: potong tanggung jawab, jangan satukan semua di satu file panjang.</p>
<p><strong>Awam:</strong> baris <code>@var list&lt;Buku&gt;</code> di komentar hanya catatan untuk manusia/IDE — “koleksi ini isinya object Buku”. PHP tidak wajib membacanya agar program jalan.</p>

<h2>Pola Dasar — visibility &amp; composition</h2>
<figure style="margin:1.5rem 0;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1.25rem;color:#1a1a1a" aria-label="Lima langkah visibility dan composition PHP">
<ol style="list-style:none;padding:0;margin:0;color:#1a1a1a">
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Kunci data penting</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Property kritis = <code>private</code> (tahun, stok, harga, status).</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Buka pintu lewat method</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem"><code>info()</code>, <code>setTahun()</code>, <code>judul()</code> — aturan hidup di sini.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Pisahkan “benda” dan “pengelola”</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem"><code>Buku</code> vs <code>Katalog</code> — jangan saturasi satu class raksasa.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Composition dulu, inheritance belakangan</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">“Memakai” object lain biasanya lebih mudah dipahami awam.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">5</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Baru bawa ke Laravel</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Berikutnya: <a href="/artikel/laravel-routing-json-perpustakaan-api">Laravel routing &amp; JSON (#56)</a> — pintu HTTP nyata di framework.</span>
    </div>
  </li>
</ol>
</figure>

<h2>Kode lengkap — <code>oop_php_visibility.php</code></h2>
<p>Simpan dan jalankan: <code>php oop_php_visibility.php</code>. Perhatikan: property <code>private</code>, perubahan lewat method, dan <code>Katalog</code> yang memakai <code>Buku</code>.</p>

<pre><code class="language-php">&lt;?php
/**
 * Visibility &amp; composition ringan (Seri 4 #55).
 * Lanjut: Laravel routing &amp; JSON di artikel berikutnya.
 */

declare(strict_types=1);

class Buku
{
    private string $judul;
    private string $penulis;
    private int $tahun;

    public function __construct(string $judul, string $penulis, int $tahun)
    {
        $this-&gt;judul = $judul;
        $this-&gt;penulis = $penulis;
        $this-&gt;setTahun($tahun);
    }

    public function info(): string
    {
        return "{$this-&gt;judul} oleh {$this-&gt;penulis} ({$this-&gt;tahun})";
    }

    public function setTahun(int $tahun): void
    {
        if ($tahun &lt; 1900 || $tahun &gt; 2100) {
            throw new InvalidArgumentException("Tahun tidak masuk akal: {$tahun}");
        }
        $this-&gt;tahun = $tahun;
    }
}

class Katalog
{
    /** @var list&lt;Buku&gt; daftar Buku di dalam katalog */
    private array $koleksi = [];

    public function tambah(Buku $buku): void
    {
        $this-&gt;koleksi[] = $buku;
    }

    public function jumlah(): int
    {
        return count($this-&gt;koleksi);
    }

    public function daftar(): string
    {
        $baris = [];
        foreach ($this-&gt;koleksi as $buku) {
            $baris[] = $buku-&gt;info();
        }
        return implode(PHP_EOL, $baris);
    }
}

function demo(): void
{
    $katalog = new Katalog();
    $katalog-&gt;tambah(new Buku("Belajar PHP", "Sari", 2024));
    $katalog-&gt;tambah(new Buku("Laravel Praktis", "Budi", 2025));

    echo $katalog-&gt;daftar(), PHP_EOL;
    echo "jumlah=", $katalog-&gt;jumlah(), PHP_EOL;
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
      <td><code>Cannot access private property</code></td>
      <td>Mengisi property private dari luar</td>
      <td>Pakai method <code>public</code> (getter/setter/aturan)</td>
    </tr>
    <tr>
      <td>Data tetap “liar”</td>
      <td>Property masih <code>public</code></td>
      <td>Kunci dengan <code>private</code> + validasi di method</td>
    </tr>
    <tr>
      <td>Class raksasa</td>
      <td>Semua logika digabung ke <code>Buku</code></td>
      <td>Pisah pengelola: <code>Katalog</code> / service tipis</td>
    </tr>
    <tr>
      <td>Langsung warisan rumit</td>
      <td>Loncat ke inheritance</td>
      <td>Composition dulu — “memakai”, bukan “menjadi”</td>
    </tr>
    <tr>
      <td>Langsung buka Laravel</td>
      <td>Fondasi object belum rapat</td>
      <td>Selesaikan jembatan OOP PHP dulu (<a href="/artikel/mengenal-oop-cara-berpikir-dengan-objek-php">Mengenal OOP PHP (#53)</a> sampai <strong>#55 (ini)</strong>)</td>
    </tr>
  </tbody>
</table>

<h2>Latihan singkat</h2>
<ol>
  <li>Tambah method <code>judul(): string</code> di <code>Buku</code> (kode lengkap) yang mengembalikan judul private.</li>
  <li>Di <code>setTahun()</code>, coba kirim tahun <code>1800</code> dan pastikan exception muncul (hapus dulu dari demo jika perlu).</li>
  <li>Tambah buku ketiga ke <code>Katalog</code> dan pastikan <code>jumlah=</code> menjadi 3.</li>
</ol>

<h2>FAQ singkat</h2>
<p><strong>Harus selalu private?</strong><br>Untuk data yang punya aturan (tahun, stok, status): ya, lebih aman. Method yang memang untuk dipanggil pemakai: biarkan <code>public</code>.</p>
<p><strong>Apa bedanya composition dan inheritance?</strong><br>Composition = “memakai” (katalog punya buku). Inheritance = “menjadi jenis khusus dari”. Di Seri 4 kita prioritaskan composition karena lebih mudah dibawa ke service Laravel.</p>
<p><strong>Apakah ini sudah cukup untuk Laravel?</strong><br>Untuk fondasi object: ya. Berikutnya kita pakai framework untuk route HTTP &amp; JSON — ide object-nya tetap sama.</p>
<p><strong>Lanjut ke mana?</strong><br>Berikutnya: <a href="/artikel/laravel-routing-json-perpustakaan-api">Laravel Routing &amp; JSON (#56)</a> — pintu HTTP nyata, artikel pertama stack Laravel di Seri 4.</p>

<h2>Kesimpulan &amp; langkah berikutnya</h2>
<p><code>private</code> melindungi data. Method <code>public</code> menjadi pintu. Composition membagi kerja antar object. Tiga kebiasaan ini yang membuat controller Laravel tidak jadi tempat sampah.</p>
<p>Artikel ini adalah <strong>#55 (ini)</strong> — penutup jembatan OOP PHP setelah <a href="/artikel/oop-php-property-method-constructor">Property, Method &amp; Constructor (#54)</a> dan <a href="/artikel/mengenal-oop-cara-berpikir-dengan-objek-php">Mengenal OOP PHP (#53)</a>.</p>

<blockquote>
  <p><strong>Seri 4 progress:</strong> langkah <strong>#55 (ini)</strong> · 8/8 Capstone Laravel selesai · jembatan OOP PHP <strong>3/3 selesai</strong> · prasyarat: <a href="/artikel/oop-php-property-method-constructor">Property, Method &amp; Constructor (#54)</a> LIVE. Berikutnya: <a href="/artikel/laravel-routing-json-perpustakaan-api">Laravel Routing &amp; JSON (#56)</a>.</p>
</blockquote>
HTML;
    }
}
