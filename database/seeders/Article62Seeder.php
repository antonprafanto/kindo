<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article62Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $webCat = Category::where('slug', 'web-development')->first()
            ?? Category::where('slug', 'programming')->first();

        if (! $admin || ! $webCat) {
            throw new \RuntimeException('User atau kategori web-development/programming tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'laravel-eloquent-relasi-peminjaman';

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
                'title'           => 'Relasi Eloquent: Anggota & Peminjaman',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'Relasi Eloquent Anggota & Peminjaman — Laravel',
                'seo_description' => 'Seri 5 #62: setelah CRUD buku lengkap, belajar menghubungkan anggota dan peminjaman — PHP dulu, cuplikan Eloquent hasMany/belongsTo, ramah awam.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['laravel', 'php', 'api', 'http', 'web', 'eloquent', 'database'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel ke-62 berhasil dipublish: '.$article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — dari CRUD buku ke pinjam</h2>
<p>Artikel ini adalah <strong>#62 (ini)</strong> di <strong>Seri 5: Laravel Lanjutan</strong> (di roadmap sering disebut Framework-based). Domain tetap <strong>perpustakaan mini</strong>.</p>
<p>Di <a href="/artikel/laravel-crud-api-buku-ubah-hapus">CRUD API Buku: Ubah &amp; Hapus (#61)</a> kamu sudah melengkapi CRUD buku. Capstone (<a href="/artikel/capstone-api-perpustakaan-laravel">#60</a>) memberi baca + login + tambah. Sekarang rak buku saja belum cukup: perpustakaan butuh <strong>anggota</strong> dan catatan <strong>peminjaman</strong> yang saling terhubung.</p>
<p><strong>Awam:</strong> <em>relasi</em> = hubungan antar data. Satu anggota bisa punya banyak pinjaman. Satu pinjaman menunjuk satu anggota dan satu buku. Belum Capstone pinjam-kembali penuh — fokusnya “menghubungkan tabel” dulu.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> sudah baca <a href="/artikel/laravel-crud-api-buku-ubah-hapus">CRUD ubah &amp; hapus (#61)</a> dan <a href="/artikel/capstone-api-perpustakaan-laravel">Capstone (#60)</a>. Pakai <strong>Laravel 11+</strong>.</p>
</blockquote>

<h2>Spesifikasi fitur — apa yang kita bangun?</h2>
<p>Daftar singkat yang bisa dijelaskan ke teman:</p>
<ol>
  <li><strong>Anggota</strong> — orang yang boleh meminjam (nama + ID).</li>
  <li><strong>Peminjaman</strong> — catatan: siapa meminjam buku mana, status aktif atau sudah kembali.</li>
  <li><strong>Hubungan</strong> — dari anggota lihat daftar pinjamannya; dari pinjaman lihat anggota dan buku.</li>
</ol>
<p><strong>Awam:</strong> bayangkan kartu anggota di meja loket, dan slip pinjam yang menempel nomor anggota + nomor buku. Eloquent membantu membaca slip itu tanpa menulis “cari manual” berkali-kali.</p>

<h2>Istilah — ringkas untuk relasi</h2>
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
      <td>Relasi</td>
      <td>Hubungan antar baris data (anggota -&gt; pinjam -&gt; buku)</td>
      <td>Bukan “teman di media sosial”</td>
    </tr>
    <tr>
      <td>Kunci asing</td>
      <td>Nomor di baris pinjam yang menunjuk anggota/buku</td>
      <td>Di kode sering <code>anggota_id</code>, <code>buku_id</code></td>
    </tr>
    <tr>
      <td><code>hasMany</code></td>
      <td>“satu punya banyak” — anggota punya banyak pinjaman</td>
      <td>Nama fungsi Eloquent</td>
    </tr>
    <tr>
      <td><code>belongsTo</code></td>
      <td>“banyak milik satu” — pinjaman milik satu anggota</td>
      <td>Kebalikan arah dari <code>hasMany</code></td>
    </tr>
    <tr>
      <td>Eloquent</td>
      <td>Cara Laravel membaca/menulis tabel lewat model (kelas yang mewakili satu tabel)</td>
      <td>Sudah muncul di Capstone &amp; service</td>
    </tr>
  </tbody>
</table>
<p>Urutan belajar: <strong>data terpisah dulu -&gt; tautkan dengan nomor -&gt; baru bungkus Eloquent</strong>.</p>

<h2>Kenapa PHP biasa dulu?</h2>
<p>Ide “slip pinjam menyimpan nomor anggota dan nomor buku” lebih mudah dirasakan di array PHP. Kalau alurnya klik, cuplikan <code>hasMany</code> / <code>belongsTo</code> terasa bungkus yang sama.</p>

<pre><code class="language-php">&lt;?php
// Mini: gabungkan pinjaman dengan nama anggota &amp; judul buku.
$anggota = [
    1 =&gt; ["nama" =&gt; "Siti"],
    2 =&gt; ["nama" =&gt; "Budi"],
];
$buku = [
    10 =&gt; ["judul" =&gt; "Dasar PHP"],
    11 =&gt; ["judul" =&gt; "Belajar Laravel"],
];
$pinjaman = [
    ["id" =&gt; 1, "anggota_id" =&gt; 1, "buku_id" =&gt; 10, "status" =&gt; "aktif"],
    ["id" =&gt; 2, "anggota_id" =&gt; 1, "buku_id" =&gt; 11, "status" =&gt; "kembali"],
];

$idAnggota = 1;
$daftar = [];
foreach ($pinjaman as $p) {
    if ($p["anggota_id"] !== $idAnggota) {
        continue;
    }
    $daftar[] = [
        "pinjam_id" =&gt; $p["id"],
        "anggota" =&gt; $anggota[$p["anggota_id"]]["nama"],
        "buku" =&gt; $buku[$p["buku_id"]]["judul"],
        "status" =&gt; $p["status"],
    ];
}

echo json_encode(["ok" =&gt; true, "pinjaman" =&gt; $daftar], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL;
</code></pre>
<p>Output (bentuknya mirip):</p>
<pre><code>{
    "ok": true,
    "pinjaman": [
        {
            "pinjam_id": 1,
            "anggota": "Siti",
            "buku": "Dasar PHP",
            "status": "aktif"
        },
        {
            "pinjam_id": 2,
            "anggota": "Siti",
            "buku": "Belajar Laravel",
            "status": "kembali"
        }
    ]
}
</code></pre>
<p><strong>Awam:</strong> <code>anggota_id</code> dan <code>buku_id</code> = nomor yang menempel di slip. Loop di atas = “cari semua slip milik Siti, lalu tulis nama + judul”. Eloquent nanti mengganti loop manual itu.</p>

<figure role="img" aria-label="Diagram relasi anggota peminjaman dan buku" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" style="display:block;max-width:760px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif" viewBox="0 0 760 260">
  <defs>
    <marker id="laravel62relasiArrow" orient="auto" markerWidth="8" markerHeight="8" refX="7" refY="4" viewBox="0 0 8 8">
      <path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/>
    </marker>
  </defs>
  <rect width="760" height="260" fill="#F5F5F0"/>
  <text x="24" y="36" fill="#1a1a1a" font-size="16" font-weight="700">Relasi: Anggota -&gt; Peminjaman -&gt; Buku</text>
  <rect x="40" y="70" width="160" height="80" rx="8" fill="#fff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="120" y="105" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Anggota</text>
  <text x="120" y="128" text-anchor="middle" fill="#1a1a1a" font-size="12">hasMany pinjaman</text>
  <line x1="200" y1="110" x2="270" y2="110" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel62relasiArrow)"/>
  <rect x="274" y="70" width="200" height="80" rx="8" fill="#1a1a1a" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="374" y="100" text-anchor="middle" fill="#fff" font-size="15" font-weight="700">Peminjaman</text>
  <text x="374" y="122" text-anchor="middle" fill="#fff" font-size="12">anggota_id + buku_id</text>
  <line x1="474" y1="110" x2="544" y2="110" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel62relasiArrow)"/>
  <rect x="548" y="70" width="160" height="80" rx="8" fill="#fff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="628" y="105" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Buku</text>
  <text x="628" y="128" text-anchor="middle" fill="#1a1a1a" font-size="12">belongsTo dari pinjam</text>
  <text x="24" y="190" fill="#1a1a1a" font-size="13">Satu anggota boleh banyak pinjaman. Satu pinjaman menunjuk satu anggota dan satu buku.</text>
  <text x="24" y="220" fill="#1a1a1a" font-size="13">CRUD buku tetap (langkah sebelumnya); di sini kita menambah lapisan “siapa meminjam apa”.</text>
</svg>
<figcaption>Anggota dan buku sudah dikenal; <strong>#62 (ini)</strong> menambahkan slip peminjaman yang menghubungkan keduanya.</figcaption>
</figure>

<h2>Alur baca pinjaman — PHP sederhana</h2>
<p>Setelah data terhubung, kita bisa menjawab: “pinjaman aktif milik anggota berapa?”</p>

<pre><code class="language-php">&lt;?php
$anggota = [1 =&gt; ["nama" =&gt; "Siti"]];
$buku = [10 =&gt; ["judul" =&gt; "Dasar PHP"]];
$pinjaman = [
    ["id" =&gt; 1, "anggota_id" =&gt; 1, "buku_id" =&gt; 10, "status" =&gt; "aktif"],
    ["id" =&gt; 2, "anggota_id" =&gt; 1, "buku_id" =&gt; 10, "status" =&gt; "kembali"],
];

function pinjamanAktifAnggota(int $anggotaId, array $pinjaman, array $anggota, array $buku): array
{
    if (! isset($anggota[$anggotaId])) {
        return ["status" =&gt; 404, "body" =&gt; ["pesan" =&gt; "Anggota tidak ketemu"]];
    }

    $hasil = [];
    foreach ($pinjaman as $p) {
        if ($p["anggota_id"] !== $anggotaId || $p["status"] !== "aktif") {
            continue;
        }
        if (! isset($buku[$p["buku_id"]])) {
            return ["status" =&gt; 404, "body" =&gt; ["pesan" =&gt; "Buku tidak ketemu di slip"]];
        }
        $hasil[] = [
            "pinjam_id" =&gt; $p["id"],
            "anggota" =&gt; $anggota[$anggotaId]["nama"],
            "buku" =&gt; $buku[$p["buku_id"]]["judul"],
            "status" =&gt; $p["status"],
        ];
    }

    return ["status" =&gt; 200, "body" =&gt; ["ok" =&gt; true, "pinjaman" =&gt; $hasil]];
}

$r = pinjamanAktifAnggota(1, $pinjaman, $anggota, $buku);
http_response_code($r["status"]);
echo json_encode($r["body"], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL;
</code></pre>
<p><strong>Awam:</strong> filter <code>status === "aktif"</code> = hanya slip yang belum dikembalikan. Kalau anggota tidak ada = <code>404</code> — sama seperti “buku tidak ketemu” di <a href="/artikel/laravel-crud-api-buku-ubah-hapus">#61</a>.</p>

<h2>Laravel — cuplikan relasi Eloquent (bukan file mandiri)</h2>
<p>Di proyek Laravel, ide yang sama ditulis di <strong>model</strong> (kelas yang mewakili satu tabel). Cuplikan di bawah <strong>bukan</strong> dijalankan dengan <code>php file.php</code>:</p>

<pre><code class="language-php">&lt;?php
// Cuplikan Laravel (bukan file mandiri) — model Anggota.
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Anggota extends Model
{
    public function peminjaman(): HasMany
    {
        return $this-&gt;hasMany(Peminjaman::class);
    }
}
</code></pre>

<p><strong>Awam:</strong> <code>hasMany</code> = “satu anggota punya banyak baris peminjaman”. <code>HasMany</code> = tipe kembalian — boleh diabaikan dulu.</p>

<pre><code class="language-php">&lt;?php
// Cuplikan Laravel — model Peminjaman mengarah ke anggota &amp; buku.
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Peminjaman extends Model
{
    public function anggota(): BelongsTo
    {
        return $this-&gt;belongsTo(Anggota::class);
    }

    public function buku(): BelongsTo
    {
        return $this-&gt;belongsTo(Buku::class);
    }
}
</code></pre>

<p><strong>Awam:</strong> <code>belongsTo</code> = “baris ini milik satu anggota / satu buku”. Setelah itu, di pengatur kode (<code>controller</code>) / pekerja (<code>service</code>) kamu bisa menulis gaya: <code>$anggota-&gt;peminjaman</code> atau <code>$pinjam-&gt;buku</code> — Eloquent yang mengisi dari kunci asing.</p>

<h2>Pola Dasar — anggota &amp; peminjaman</h2>
<figure style="margin:1.5rem 0;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1.25rem;color:#1a1a1a" aria-label="Enam langkah relasi Eloquent anggota peminjaman">
<ol style="list-style:none;padding:0;margin:0;color:#1a1a1a">
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div><strong style="color:#1a1a1a">Rapikan CRUD buku dulu</strong><br><span style="color:#1a1a1a">Pastikan alur di <a href="/artikel/laravel-crud-api-buku-ubah-hapus" style="color:#1a1a1a;text-decoration:underline">#61</a> sudah “klik”.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div><strong style="color:#1a1a1a">Buat tabel anggota &amp; peminjaman</strong><br><span style="color:#1a1a1a">Migrasi (skrip buat/ubah tabel): kolom <code>anggota_id</code> dan <code>buku_id</code> di slip pinjam.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div><strong style="color:#1a1a1a">Tulis <code>hasMany</code> / <code>belongsTo</code></strong><br><span style="color:#1a1a1a">Satu arah “punya banyak”, arah balik “milik satu”.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div><strong style="color:#1a1a1a">Baca lewat relasi</strong><br><span style="color:#1a1a1a">Dari anggota ambil daftar pinjam; dari pinjam ambil buku.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">5</span>
    <div><strong style="color:#1a1a1a">Jaga ID kosong</strong><br><span style="color:#1a1a1a">Anggota/buku tidak ketemu = <code>404</code>, jangan pura-pura sukses.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">6</span>
    <div><strong style="color:#1a1a1a">Uji dua arah</strong><br><span style="color:#1a1a1a">Anggota tanpa pinjam · pinjam aktif · ID palsu.</span></div>
  </li>
</ol>
</figure>

<h2>Kode lengkap — demo mandiri relasi</h2>
<p>Simpan sebagai <code>laravel_eloquent_relasi_peminjaman_demo.php</code>, lalu jalankan <code>php laravel_eloquent_relasi_peminjaman_demo.php</code>:</p>

<pre><code class="language-php">&lt;?php
declare(strict_types=1);

$anggota = [
    1 =&gt; ["nama" =&gt; "Siti"],
    2 =&gt; ["nama" =&gt; "Budi"],
];
$buku = [
    10 =&gt; ["judul" =&gt; "Dasar PHP"],
    11 =&gt; ["judul" =&gt; "Belajar Laravel"],
];
$pinjaman = [
    ["id" =&gt; 1, "anggota_id" =&gt; 1, "buku_id" =&gt; 10, "status" =&gt; "aktif"],
    ["id" =&gt; 2, "anggota_id" =&gt; 1, "buku_id" =&gt; 11, "status" =&gt; "kembali"],
    ["id" =&gt; 3, "anggota_id" =&gt; 2, "buku_id" =&gt; 10, "status" =&gt; "aktif"],
];

function pinjamanAktifAnggota(int $anggotaId, array $pinjaman, array $anggota, array $buku): array
{
    if (! isset($anggota[$anggotaId])) {
        return ["status" =&gt; 404, "body" =&gt; ["pesan" =&gt; "Anggota tidak ketemu"]];
    }

    $hasil = [];
    foreach ($pinjaman as $p) {
        if ($p["anggota_id"] !== $anggotaId || $p["status"] !== "aktif") {
            continue;
        }
        if (! isset($buku[$p["buku_id"]])) {
            return ["status" =&gt; 404, "body" =&gt; ["pesan" =&gt; "Buku tidak ketemu di slip"]];
        }
        $hasil[] = [
            "pinjam_id" =&gt; $p["id"],
            "anggota" =&gt; $anggota[$anggotaId]["nama"],
            "buku" =&gt; $buku[$p["buku_id"]]["judul"],
            "status" =&gt; $p["status"],
        ];
    }

    return ["status" =&gt; 200, "body" =&gt; ["ok" =&gt; true, "pinjaman" =&gt; $hasil]];
}

function demo(string $judul, callable $aksi): void
{
    echo "=== {$judul} ===", PHP_EOL;
    $hasil = $aksi();
    echo "status: ", $hasil["status"], PHP_EOL;
    echo json_encode($hasil["body"], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL, PHP_EOL;
}

demo("Anggota palsu -&gt; 404", function () use ($pinjaman, $anggota, $buku) {
    return pinjamanAktifAnggota(99, $pinjaman, $anggota, $buku);
});

demo("Siti pinjam aktif -&gt; 200", function () use ($pinjaman, $anggota, $buku) {
    return pinjamanAktifAnggota(1, $pinjaman, $anggota, $buku);
});

demo("Budi pinjam aktif -&gt; 200", function () use ($pinjaman, $anggota, $buku) {
    return pinjamanAktifAnggota(2, $pinjaman, $anggota, $buku);
});
</code></pre>
<p><strong>Awam:</strong> <code>demo(...)</code> hanya membungkus output di terminal. <code>callable</code> = sesuatu yang bisa dipanggil seperti fungsi. <code>declare(strict_types=1);</code> membuat tipe lebih ketat — boleh diikuti, tidak wajib dihafal. Alur penting: ID palsu, Siti punya pinjam aktif, Budi punya pinjam aktif.</p>

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
      <td>Pinjaman kosong padahal data ada</td>
      <td>Salah <code>anggota_id</code> / lupa filter status</td>
      <td>Cetak ID di demo; cek “aktif” vs “kembali”</td>
    </tr>
    <tr>
      <td>Bingung <code>hasMany</code> vs <code>belongsTo</code></td>
      <td>Membalik arah “punya banyak” vs “milik satu”</td>
      <td>Anggota <code>hasMany</code> pinjam; pinjam <code>belongsTo</code> anggota</td>
    </tr>
    <tr>
      <td>Judul buku kosong</td>
      <td><code>buku_id</code> tidak ketemu di katalog</td>
      <td>Jawab <code>404</code> atau perbaiki kunci asing</td>
    </tr>
    <tr>
      <td>Daftar pinjam terasa lambat saat data banyak</td>
      <td>Membaca buku/anggota satu-satu di dalam loop (sering disebut masalah N+1)</td>
      <td>Nanti dilatih saat daftar panjang (pagination) — cukup tahu dulu</td>
    </tr>
  </tbody>
</table>

<h2>Latihan singkat</h2>
<ol>
  <li>Ubah demo: tambah kasus “anggota tanpa pinjam aktif” dan pastikan daftar kosong tapi status tetap <code>200</code>.</li>
  <li>Jelaskan ke teman: beda <code>hasMany</code> dan <code>belongsTo</code> dengan analogi kartu anggota + slip pinjam.</li>
  <li>Tulis satu kalimat: kenapa slip pinjam menyimpan nomor, bukan menyalin seluruh nama anggota.</li>
</ol>

<h2>FAQ singkat</h2>
<p><strong>Haruskah hafal semua jenis relasi Eloquent?</strong><br>
Belum. Untuk perpustakaan mini, <code>hasMany</code> dan <code>belongsTo</code> sudah cukup. Relasi “banyak-ke-banyak” bisa menyusul kalau dibutuhkan.</p>
<p><strong>Kenapa belum Capstone pinjam-kembali?</strong><br>
Karena fondasi hubungan data harus jelas dulu. Alur pinjam + kembali penuh ada di akhir Seri 5.</p>
<p><strong>Ke mana setelah ini?</strong><br>
Berikutnya alami: <strong>pagination, filter &amp; pencarian</strong> — daftar pinjaman/buku yang panjang tetap nyaman dibaca. Belum perlu hardlink; tunggu artikel berikutnya LIVE.</p>

<h2>Kesimpulan</h2>
<p>Kamu sudah melangkah dari CRUD buku ke <strong>relasi</strong>: anggota dan peminjaman terhubung lewat nomor (<code>anggota_id</code>, <code>buku_id</code>). PHP array dulu; Eloquent <code>hasMany</code> / <code>belongsTo</code> adalah bungkus yang sama.</p>
<blockquote>
  <p><strong>Seri 5 progress:</strong> langkah <strong>#62 (ini)</strong> · 2/8 Laravel Lanjutan · prasyarat: <a href="/artikel/laravel-crud-api-buku-ubah-hapus">CRUD ubah &amp; hapus (#61)</a> LIVE. Berikutnya: Pagination, filter &amp; pencarian — soft, belum hardlink.</p>
</blockquote>
HTML;
    }
}
