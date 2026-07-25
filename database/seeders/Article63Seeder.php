<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article63Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $webCat = Category::where('slug', 'web-development')->first()
            ?? Category::where('slug', 'programming')->first();

        if (! $admin || ! $webCat) {
            throw new \RuntimeException('User atau kategori web-development/programming tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'laravel-pagination-filter-pencarian';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        foreach ([
            'laravel' => 'laravel',
            'php' => 'php',
            'api' => 'api',
            'http' => 'http',
            'web' => 'web',
            'eloquent' => 'eloquent',
            'database' => 'database',
        ] as $tagSlug => $tagName) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagName]);
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $webCat->id,
                'title'           => 'Pagination, Filter & Pencarian',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'Pagination Filter & Pencarian API — Laravel',
                'seo_description' => 'Seri 5 #63: setelah relasi anggota & pinjam, belajar memotong daftar per halaman, filter status, dan cari judul — PHP dulu, cuplikan Laravel, ramah awam.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['laravel', 'php', 'api', 'http', 'web', 'eloquent', 'database'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel ke-63 berhasil dipublish: '.$article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — daftar panjang butuh potongan</h2>
<p>Artikel ini adalah <strong>#63 (ini)</strong> di <strong>Seri 5: Laravel Lanjutan</strong> (di roadmap sering disebut Framework-based). Domain tetap <strong>perpustakaan mini</strong>.</p>
<p>Di <a href="/artikel/laravel-eloquent-relasi-peminjaman">Relasi Eloquent: Anggota &amp; Peminjaman (#62)</a> kamu sudah menghubungkan anggota dan pinjaman. Saat katalog atau daftar pinjam membesar, mengirim <strong>semua baris sekaligus</strong> membuat jawaban lambat dan sulit dibaca. Kita butuh: potong per halaman, saring status, dan cari judul.</p>
<p><strong>Awam:</strong> bayangkan loket mencetak daftar buku 5 baris per halaman, bisa pilih “hanya aktif”, dan bisa ketik “PHP” untuk menyempitkan. Itu pagination + filter + pencarian.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> sudah baca <a href="/artikel/laravel-eloquent-relasi-peminjaman">Relasi Eloquent (#62)</a> dan <a href="/artikel/laravel-crud-api-buku-ubah-hapus">CRUD ubah &amp; hapus (#61)</a>. Pakai <strong>Laravel 11+</strong>.</p>
</blockquote>

<h2>Spesifikasi fitur — apa yang kita bangun?</h2>
<p>Daftar singkat yang bisa dijelaskan ke teman:</p>
<ol>
  <li><strong>Halaman</strong> — kirim sebagian daftar (misalnya 5 item) + info “halaman berapa / total”.</li>
  <li><strong>Filter</strong> — saring menurut status (aktif / kembali) atau field sederhana lain.</li>
  <li><strong>Pencarian</strong> — cari kata di judul (atau nama) tanpa mengirim seluruh katalog mentah.</li>
</ol>
<p><strong>Awam:</strong> urutan kerja yang nyaman: <strong>saring dulu -&gt; cari dulu -&gt; baru potong halaman</strong>. Kalau potong dulu baru saring, hasilnya bisa “aneh” (halaman kosong padahal data ada).</p>

<h2>Istilah — ringkas untuk daftar panjang</h2>
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
      <td>Pagination</td>
      <td>Memotong daftar jadi halaman-halaman</td>
      <td>Bukan “menghapus data”</td>
    </tr>
    <tr>
      <td>Halaman / <code>page</code></td>
      <td>Nomor lembar yang diminta pemanggil</td>
      <td>Mulai dari 1</td>
    </tr>
    <tr>
      <td>Per halaman / <code>per_page</code></td>
      <td>Berapa item di satu lembar</td>
      <td>Contoh: 5 atau 10</td>
    </tr>
    <tr>
      <td>Filter</td>
      <td>Saring baris menurut aturan (status, tahun, …)</td>
      <td>Beda dengan cari teks bebas</td>
    </tr>
    <tr>
      <td>Pencarian</td>
      <td>Cari kata di judul/nama</td>
      <td>Di SQL sering pakai pola <code>LIKE</code></td>
    </tr>
  </tbody>
</table>
<p>Urutan belajar: <strong>array PHP dulu -&gt; potong &amp; saring manual -&gt; baru bungkus Laravel</strong>.</p>

<h2>Kenapa PHP biasa dulu?</h2>
<p>Ide “ambil lembar 2 dari daftar yang sudah disaring” lebih mudah dirasakan di array. Kalau alurnya klik, cuplikan <code>paginate()</code> terasa bungkus yang sama.</p>

<pre><code class="language-php">&lt;?php
// Mini: saring status, cari judul, potong halaman.
$katalog = [
    ["id" =&gt; 1, "judul" =&gt; "Dasar PHP", "status" =&gt; "aktif"],
    ["id" =&gt; 2, "judul" =&gt; "Belajar Laravel", "status" =&gt; "aktif"],
    ["id" =&gt; 3, "judul" =&gt; "PHP Lanjut", "status" =&gt; "kembali"],
    ["id" =&gt; 4, "judul" =&gt; "API Dasar", "status" =&gt; "aktif"],
    ["id" =&gt; 5, "judul" =&gt; "Eloquent Relasi", "status" =&gt; "aktif"],
    ["id" =&gt; 6, "judul" =&gt; "Validasi Request", "status" =&gt; "kembali"],
];

$status = "aktif";
$cari = "PHP";
$page = 1;
$perPage = 2;

$hasil = [];
foreach ($katalog as $buku) {
    if ($buku["status"] !== $status) {
        continue;
    }
    if ($cari !== "" &amp;&amp; stripos($buku["judul"], $cari) === false) {
        continue;
    }
    $hasil[] = $buku;
}

$total = count($hasil);
$mulai = ($page - 1) * $perPage;
$isi = array_slice($hasil, $mulai, $perPage);

echo json_encode([
    "ok" =&gt; true,
    "page" =&gt; $page,
    "per_page" =&gt; $perPage,
    "total" =&gt; $total,
    "data" =&gt; $isi,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL;
</code></pre>
<p>Output (bentuknya mirip):</p>
<pre><code>{
    "ok": true,
    "page": 1,
    "per_page": 2,
    "total": 1,
    "data": [
        {"id": 1, "judul": "Dasar PHP", "status": "aktif"}
    ]
}
</code></pre>
<p><strong>Awam:</strong> filter status <code>aktif</code> dulu, lalu cari “PHP”, baru potong. “PHP Lanjut” statusnya kembali — tidak ikut. <code>array_slice</code> hanya mengambil lembar yang diminta.</p>

<figure role="img" aria-label="Diagram alur filter cari dan pagination" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" style="display:block;max-width:760px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif" viewBox="0 0 760 260">
  <defs>
    <marker id="laravel63pageArrow" orient="auto" markerWidth="8" markerHeight="8" refX="7" refY="4" viewBox="0 0 8 8">
      <path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/>
    </marker>
  </defs>
  <rect width="760" height="260" fill="#F5F5F0"/>
  <text x="24" y="36" fill="#1a1a1a" font-size="16" font-weight="700">Daftar panjang: Filter -&gt; Cari -&gt; Potong halaman -&gt; JSON</text>
  <rect x="24" y="70" width="140" height="80" rx="8" fill="#fff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="94" y="105" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Filter</text>
  <text x="94" y="128" text-anchor="middle" fill="#1a1a1a" font-size="12">status / aturan</text>
  <line x1="164" y1="110" x2="214" y2="110" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel63pageArrow)"/>
  <rect x="218" y="70" width="140" height="80" rx="8" fill="#1a1a1a" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="288" y="105" text-anchor="middle" fill="#fff" font-size="15" font-weight="700">Cari</text>
  <text x="288" y="128" text-anchor="middle" fill="#fff" font-size="12">kata di judul</text>
  <line x1="358" y1="110" x2="408" y2="110" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel63pageArrow)"/>
  <rect x="412" y="70" width="150" height="80" rx="8" fill="#fff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="487" y="105" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Potong</text>
  <text x="487" y="128" text-anchor="middle" fill="#1a1a1a" font-size="12">page + per_page</text>
  <line x1="562" y1="110" x2="612" y2="110" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel63pageArrow)"/>
  <rect x="616" y="70" width="120" height="80" rx="8" fill="#fff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="676" y="105" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">JSON</text>
  <text x="676" y="128" text-anchor="middle" fill="#1a1a1a" font-size="12">data + total</text>
  <text x="24" y="190" fill="#1a1a1a" font-size="13">Jangan potong dulu baru saring — halaman bisa kosong padahal data masih ada.</text>
  <text x="24" y="220" fill="#1a1a1a" font-size="13">Relasi tetap (langkah sebelumnya); di sini kita merapikan cara membaca daftar yang panjang.</text>
</svg>
<figcaption>Setelah relasi jelas, <strong>#63 (ini)</strong> merapikan cara membaca daftar panjang lewat API.</figcaption>
</figure>

<h2>Alur daftar — PHP sederhana</h2>
<p>Pemanggil — aplikasi atau alat yang memanggil API — mengirim <code>page</code>, opsional <code>status</code>, dan opsional kata <code>cari</code>.</p>

<pre><code class="language-php">&lt;?php
$katalog = [
    ["id" =&gt; 1, "judul" =&gt; "Dasar PHP", "status" =&gt; "aktif"],
    ["id" =&gt; 2, "judul" =&gt; "Belajar Laravel", "status" =&gt; "aktif"],
    ["id" =&gt; 3, "judul" =&gt; "PHP Lanjut", "status" =&gt; "kembali"],
    ["id" =&gt; 4, "judul" =&gt; "API Dasar", "status" =&gt; "aktif"],
];

function daftarBuku(array $katalog, int $page, int $perPage, ?string $status, string $cari): array
{
    if ($page &lt; 1 || $perPage &lt; 1) {
        return ["status" =&gt; 422, "body" =&gt; ["pesan" =&gt; "Isian halaman belum rapi"]];
    }

    $hasil = [];
    foreach ($katalog as $buku) {
        if ($status !== null &amp;&amp; $buku["status"] !== $status) {
            continue;
        }
        if ($cari !== "" &amp;&amp; stripos($buku["judul"], $cari) === false) {
            continue;
        }
        $hasil[] = $buku;
    }

    $total = count($hasil);
    $mulai = ($page - 1) * $perPage;
    $isi = array_values(array_slice($hasil, $mulai, $perPage));

    return [
        "status" =&gt; 200,
        "body" =&gt; [
            "ok" =&gt; true,
            "page" =&gt; $page,
            "per_page" =&gt; $perPage,
            "total" =&gt; $total,
            "data" =&gt; $isi,
        ],
    ];
}

$r = daftarBuku($katalog, 1, 2, "aktif", "PHP");
http_response_code($r["status"]);
echo json_encode($r["body"], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL;
</code></pre>
<p><strong>Awam:</strong> <code>stripos</code> = cari kata di judul tanpa peduli huruf besar/kecil. <code>array_slice</code> = potong lembar. <code>422</code> = isian halaman kotor (sama ide dengan isian belum rapi di <a href="/artikel/laravel-crud-api-buku-ubah-hapus">CRUD ubah &amp; hapus (#61)</a>).</p>

<h2>Laravel — cuplikan pagination &amp; filter (bukan file mandiri)</h2>
<p>Di proyek Laravel, ide yang sama sering ditulis di pengatur kode (<code>controller</code>) / pekerja (<code>service</code>). Cuplikan di bawah <strong>bukan</strong> dijalankan dengan <code>php file.php</code>:</p>

<pre><code class="language-php">&lt;?php
// Cuplikan Laravel (bukan file mandiri) — daftar buku berhalaman.
use App\Models\Buku;
use Illuminate\Http\Request;

public function index(Request $request)
{
    $perPage = (int) $request-&gt;integer('per_page', 10);
    $query = Buku::query();

    if ($request-&gt;filled('status')) {
        $query-&gt;where('status', $request-&gt;string('status'));
    }

    if ($request-&gt;filled('cari')) {
        $kata = $request-&gt;string('cari');
        $query-&gt;where('judul', 'like', "%{$kata}%");
    }

    return response()-&gt;json($query-&gt;paginate($perPage));
}
</code></pre>

<p><strong>Awam:</strong></p>
<ul>
  <li><code>paginate($perPage)</code> = potong halaman + isi info ringkas (total, halaman) otomatis</li>
  <li><code>where('status', …)</code> = filter aturan tepat</li>
  <li><code>like '%kata%'</code> = judul mengandung kata (pola pencarian SQL)</li>
  <li><code>filled(...)</code> = “isian ada dan tidak kosong” — boleh diikuti, tidak wajib dihafal</li>
</ul>

<pre><code class="language-php">&lt;?php
// Cuplikan Laravel — contoh jawaban paginate (bentuk ringkas).
// Bukan file mandiri; hanya ilustrasi bentuk JSON.
[
    "data" =&gt; [ /* item halaman ini */ ],
    "current_page" =&gt; 1,
    "per_page" =&gt; 10,
    "total" =&gt; 42,
]
</code></pre>
<p><strong>Awam:</strong> nama info tambahan bisa sedikit berbeda antar versi; yang penting kamu paham tiga angka: halaman sekarang, per halaman, dan total.</p>

<h2>Pola Dasar — daftar panjang yang rapi</h2>
<figure style="margin:1.5rem 0;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1.25rem;color:#1a1a1a" aria-label="Enam langkah pagination filter pencarian">
<ol style="list-style:none;padding:0;margin:0;color:#1a1a1a">
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div><strong style="color:#1a1a1a">Pastikan relasi sudah klik</strong><br><span style="color:#1a1a1a">Fondasi di <a href="/artikel/laravel-eloquent-relasi-peminjaman" style="color:#1a1a1a;text-decoration:underline">Relasi Eloquent (#62)</a> membantu daftar pinjam tetap bermakna.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div><strong style="color:#1a1a1a">Saring dulu</strong><br><span style="color:#1a1a1a">Filter status (atau aturan lain) sebelum potong halaman.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div><strong style="color:#1a1a1a">Cari di judul</strong><br><span style="color:#1a1a1a">Kata kunci menyempitkan hasil; kosong = tidak menyaring teks.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div><strong style="color:#1a1a1a">Potong halaman</strong><br><span style="color:#1a1a1a"><code>page</code> + <code>per_page</code>; jaga angka &gt;= 1.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">5</span>
    <div><strong style="color:#1a1a1a">Kirim info ringkas</strong><br><span style="color:#1a1a1a">Total + halaman sekarang supaya layar pemanggil bisa buat tombol “berikutnya”.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">6</span>
    <div><strong style="color:#1a1a1a">Uji tiga jalur</strong><br><span style="color:#1a1a1a">Halaman kotor · filter ketat · cari + halaman 2.</span></div>
  </li>
</ol>
</figure>

<h2>Kode lengkap — demo mandiri daftar panjang</h2>
<p>Simpan sebagai <code>laravel_pagination_filter_pencarian_demo.php</code>, lalu jalankan <code>php laravel_pagination_filter_pencarian_demo.php</code>:</p>

<pre><code class="language-php">&lt;?php
declare(strict_types=1);

$katalog = [
    ["id" =&gt; 1, "judul" =&gt; "Dasar PHP", "status" =&gt; "aktif"],
    ["id" =&gt; 2, "judul" =&gt; "Belajar Laravel", "status" =&gt; "aktif"],
    ["id" =&gt; 3, "judul" =&gt; "PHP Lanjut", "status" =&gt; "kembali"],
    ["id" =&gt; 4, "judul" =&gt; "API Dasar", "status" =&gt; "aktif"],
    ["id" =&gt; 5, "judul" =&gt; "Eloquent Relasi", "status" =&gt; "aktif"],
    ["id" =&gt; 6, "judul" =&gt; "Validasi Request", "status" =&gt; "kembali"],
];

function daftarBuku(array $katalog, int $page, int $perPage, ?string $status, string $cari): array
{
    if ($page &lt; 1 || $perPage &lt; 1) {
        return ["status" =&gt; 422, "body" =&gt; ["pesan" =&gt; "Isian halaman belum rapi"]];
    }

    $hasil = [];
    foreach ($katalog as $buku) {
        if ($status !== null &amp;&amp; $buku["status"] !== $status) {
            continue;
        }
        if ($cari !== "" &amp;&amp; stripos($buku["judul"], $cari) === false) {
            continue;
        }
        $hasil[] = $buku;
    }

    $total = count($hasil);
    $mulai = ($page - 1) * $perPage;
    $isi = array_values(array_slice($hasil, $mulai, $perPage));

    return [
        "status" =&gt; 200,
        "body" =&gt; [
            "ok" =&gt; true,
            "page" =&gt; $page,
            "per_page" =&gt; $perPage,
            "total" =&gt; $total,
            "data" =&gt; $isi,
        ],
    ];
}

function demo(string $judul, callable $aksi): void
{
    echo "=== {$judul} ===", PHP_EOL;
    $hasil = $aksi();
    echo "status: ", $hasil["status"], PHP_EOL;
    echo json_encode($hasil["body"], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL, PHP_EOL;
}

demo("Halaman kotor -&gt; 422", function () use ($katalog) {
    return daftarBuku($katalog, 0, 2, "aktif", "");
});

demo("Aktif + cari PHP -&gt; 200", function () use ($katalog) {
    return daftarBuku($katalog, 1, 5, "aktif", "PHP");
});

demo("Semua status halaman 2 -&gt; 200", function () use ($katalog) {
    return daftarBuku($katalog, 2, 2, null, "");
});
</code></pre>
<p><strong>Awam:</strong> <code>demo(...)</code> hanya membungkus output di terminal. <code>callable</code> = sesuatu yang bisa dipanggil seperti fungsi. <code>declare(strict_types=1);</code> membuat tipe lebih ketat — boleh diikuti, tidak wajib dihafal. Alur penting: halaman kotor, filter+cari, halaman 2 tanpa filter.</p>

<h2>Kesalahan umum</h2>
<table>
  <thead>
    <tr>
      <th>Gejala</th>
      <th>Penyebab tipikal</th>
      <th>Perbaikan awam</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Halaman kosong padahal data ada</td>
      <td>Potong dulu baru filter/cari</td>
      <td>Urutan: saring -&gt; cari -&gt; potong</td>
    </tr>
    <tr>
      <td>Hasil “PHP” tidak ketemu</td>
      <td>Huruf besar/kecil atau salah kolom</td>
      <td>Pakai pencarian tanpa peduli kapital; cek kolom judul</td>
    </tr>
    <tr>
      <td><code>422</code> saat buka daftar</td>
      <td><code>page</code> / <code>per_page</code> &lt;= 0</td>
      <td>Validasi angka &gt;= 1</td>
    </tr>
    <tr>
      <td>Daftar lambat saat data banyak</td>
      <td>Mengirim semua baris tanpa potong</td>
      <td>Pakai pagination; filter sebelum potong</td>
    </tr>
  </tbody>
</table>

<h2>Latihan singkat</h2>
<ol>
  <li>Ubah demo: tambah kasus “cari kosong + status kembali” dan hitung totalnya.</li>
  <li>Jelaskan ke teman: kenapa saring harus sebelum potong halaman.</li>
  <li>Tulis satu kalimat: beda filter status dengan pencarian judul.</li>
</ol>

<h2>FAQ singkat</h2>
<p><strong>Berapa <code>per_page</code> yang bagus?</strong><br>
Untuk belajar, 5–10 sudah nyaman. Di produksi, batasi maksimum supaya pemanggil tidak minta 10.000 sekaligus.</p>
<p><strong>Apakah wajib pakai <code>paginate()</code> Laravel?</strong><br>
Tidak wajib memahami setiap opsi dulu. Yang penting: potong + kirim total. <code>paginate()</code> tinggal membungkus ide yang sama.</p>
<p><strong>Ke mana setelah ini?</strong><br>
Berikutnya: <a href="/artikel/laravel-policy-otorisasi-api">Authorization Policy: Siapa Boleh Ubah (#64)</a> — aturan izin siapa boleh ubah/hapus data.</p>

<h2>Kesimpulan</h2>
<p>Kamu sudah merapikan daftar panjang: <strong>filter</strong> menyaring aturan, <strong>pencarian</strong> menyempitkan teks, <strong>pagination</strong> memotong lembar. PHP array dulu; Laravel <code>paginate()</code> / <code>where</code> / <code>like</code> adalah bungkus yang sama.</p>
<blockquote>
  <p><strong>Seri 5 progress:</strong> langkah <strong>#63 (ini)</strong> · 3/8 Laravel Lanjutan · prasyarat: <a href="/artikel/laravel-eloquent-relasi-peminjaman">Relasi Eloquent (#62)</a> LIVE. Berikutnya: <a href="/artikel/laravel-policy-otorisasi-api">Authorization Policy: Siapa Boleh Ubah (#64)</a>.</p>
</blockquote>
HTML;
    }
}
