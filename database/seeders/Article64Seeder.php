<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article64Seeder extends Seeder
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
                'seo_title'       => 'Relasi Eloquent Laravel — Anggota, Buku, dan Peminjaman',
                'seo_description' => 'Seri 5 #64: setelah CRUD buku selesai, sambungkan anggota, buku, dan peminjaman lewat relasi Eloquent. Ramah awam, PHP dulu lalu cuplikan Laravel.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['laravel', 'php', 'api', 'http', 'web', 'eloquent', 'database'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel ke-64 berhasil dipublish: '.$article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — menghubungkan kartu anggota ke slip pinjam</h2>
<p>Artikel ini adalah <strong>#64 (ini)</strong> di <strong>Seri 5: Laravel Lanjutan</strong>. Setelah jalur Laravel dari nol selesai di <a href="/artikel/laravel-crud-api-buku-ubah-hapus">CRUD API Buku: Ubah &amp; Hapus (#63)</a>, sekarang kita masuk ke lapisan yang lebih nyata: <strong>hubungan antar data</strong>.</p>
<p>Di dunia perpustakaan, buku tidak berdiri sendirian. Ada <strong>anggota</strong> yang meminjam, ada <strong>buku</strong> yang dipinjam, dan ada <strong>catatan peminjaman</strong> yang menjadi penghubung keduanya. Di Laravel, hubungan seperti ini disebut <strong>relasi Eloquent</strong>. Nanti hasil gabungan ini dibaca oleh <strong>pemanggil</strong>, yaitu aplikasi atau alat <strong>yang memanggil API</strong>.</p>
<p><strong>Awam:</strong> bayangkan tiga kartu. Kartu pertama = data anggota. Kartu kedua = data buku. Kartu ketiga = slip pinjam yang menuliskan “siapa meminjam buku apa”. Slip itu menunjuk ke dua kartu lain. Hari ini kita belajar cara menulis hubungan itu dengan rapi.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> sudah selesai <a href="/artikel/laravel-crud-api-buku-ubah-hapus">CRUD API Buku: Ubah &amp; Hapus (#63)</a>, paham <a href="/artikel/laravel-auth-api-dasar">Auth API Dasar: Login &amp; Kartu Anggota (#61)</a>, dan fondasi <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a> / <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a>. Pakai <strong>Laravel 13+</strong> — butuh <strong>PHP 8.3+</strong>.</p>
</blockquote>

<h2>Spesifikasi fitur — apa yang selesai hari ini?</h2>
<p>Tiga hal ini yang kita kejar:</p>
<ol>
  <li><strong>Buat model relasi</strong> — <code>Anggota</code>, <code>Buku</code>, dan <code>Peminjaman</code> saling terhubung.</li>
  <li><strong>Bisa membaca data gabungan</strong> — satu catatan pinjam bisa menampilkan nama anggota dan judul buku sekaligus.</li>
  <li><strong>Punya fondasi untuk artikel berikutnya</strong> — daftar panjang pinjam, policy, resource, dan test semua bergantung pada relasi ini.</li>
</ol>
<p><strong>Awam:</strong> selesai artikel ini, kamu belum sedang membangun loket “izin” atau “JSON rapi”. Kamu sedang memasang <strong>peta jalan</strong> supaya Laravel tahu buku mana milik slip pinjam yang mana, anggota mana yang terlibat, dan jawaban apa yang harus dibaca oleh <strong>pemanggil</strong> API nanti.</p>

<figure role="img" aria-label="Relasi buku, peminjaman, dan anggota" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" style="display:block;max-width:760px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif" viewBox="0 0 760 250">
  <defs>
    <marker id="laravel64relasiArrow" orient="auto" markerWidth="8" markerHeight="8" refX="7" refY="4" viewBox="0 0 8 8">
      <path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/>
    </marker>
  </defs>
  <rect width="760" height="250" fill="#F5F5F0"/>
  <text x="24" y="36" fill="#1a1a1a" font-size="16" font-weight="700">Buku -&gt; Peminjaman &lt;- Anggota</text>
  <rect x="28" y="72" width="180" height="90" rx="10" fill="#ffffff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="118" y="107" text-anchor="middle" fill="#1a1a1a" font-size="16" font-weight="700">Buku</text>
  <text x="118" y="132" text-anchor="middle" fill="#1a1a1a" font-size="12">satu buku bisa muncul</text>
  <text x="118" y="150" text-anchor="middle" fill="#1a1a1a" font-size="12">di banyak slip pinjam</text>
  <line x1="208" y1="116" x2="288" y2="116" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel64relasiArrow)"/>
  <rect x="290" y="58" width="180" height="120" rx="10" fill="#1a1a1a" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="380" y="100" text-anchor="middle" fill="#ffffff" font-size="16" font-weight="700">Peminjaman</text>
  <text x="380" y="126" text-anchor="middle" fill="#ffffff" font-size="12">penghubung siapa</text>
  <text x="380" y="144" text-anchor="middle" fill="#ffffff" font-size="12">meminjam buku apa</text>
  <line x1="470" y1="116" x2="550" y2="116" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel64relasiArrow)"/>
  <rect x="552" y="72" width="180" height="90" rx="10" fill="#ffffff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="642" y="107" text-anchor="middle" fill="#1a1a1a" font-size="16" font-weight="700">Anggota</text>
  <text x="642" y="132" text-anchor="middle" fill="#1a1a1a" font-size="12">satu anggota bisa punya</text>
  <text x="642" y="150" text-anchor="middle" fill="#1a1a1a" font-size="12">banyak catatan pinjam</text>
  <text x="24" y="212" fill="#1a1a1a" font-size="13">Relasi ini akan dipakai lagi di pagination, policy, resource, dan capstone.</text>
</svg>
<figcaption>Relasi Eloquent membantu Laravel membaca hubungan buku, anggota, dan peminjaman tanpa menulis sambungan manual terus-menerus.</figcaption>
</figure>

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
      <td>Hubungan antar data</td>
      <td>Siapa terhubung ke siapa</td>
    </tr>
    <tr>
      <td><code>belongsTo</code></td>
      <td>Baris ini “milik” satu baris lain</td>
      <td>Satu slip pinjam milik satu buku, dan milik satu anggota</td>
    </tr>
    <tr>
      <td><code>hasMany</code></td>
      <td>Satu baris punya banyak pasangan</td>
      <td>Satu anggota punya banyak slip pinjam</td>
    </tr>
    <tr>
      <td><code>buku_id</code></td>
      <td>Nomor buku yang ditunjuk slip</td>
      <td>Kunci penghubung ke tabel buku</td>
    </tr>
    <tr>
      <td><code>anggota_id</code></td>
      <td>Nomor anggota yang meminjam</td>
      <td>Kunci penghubung ke tabel anggota</td>
    </tr>
  </tbody>
</table>
<p>Urutan belajar kita: <strong>array PHP dulu -&gt; lihat relasi secara kasat mata -&gt; baru bungkus dengan <code>hasMany</code> dan <code>belongsTo</code> di Eloquent</strong>.</p>

<h2>Kenapa PHP biasa dulu?</h2>
<p>Kalau langsung loncat ke tiga model Eloquent, pemula sering cuma menghafal fungsi tanpa benar-benar paham hubungan datanya. Maka kita mulai dari array: lihat buku, lihat anggota, lihat slip pinjam, lalu cocokkan nomornya.</p>

<pre><code class="language-php">&lt;?php
$buku = [
    ["id" =&gt; 1, "judul" =&gt; "Dasar PHP"],
    ["id" =&gt; 2, "judul" =&gt; "Belajar Laravel"],
];

$anggota = [
    ["id" =&gt; 10, "nama" =&gt; "Budi"],
    ["id" =&gt; 11, "nama" =&gt; "Siti"],
];

$peminjaman = [
    ["id" =&gt; 100, "buku_id" =&gt; 1, "anggota_id" =&gt; 10, "status" =&gt; "aktif"],
];
</code></pre>
<p><strong>Awam:</strong> di sini relasi belum memakai Laravel sama sekali. Tapi hubungan itu sudah ada: slip pinjam <code>100</code> menunjuk buku <code>1</code> dan anggota <code>10</code>. Laravel nanti hanya membantu membacanya lebih rapi.</p>

<h2>Gabungkan data dengan PHP dulu</h2>
<pre><code class="language-php">&lt;?php
$buku = [
    1 =&gt; ["id" =&gt; 1, "judul" =&gt; "Dasar PHP"],
    2 =&gt; ["id" =&gt; 2, "judul" =&gt; "Belajar Laravel"],
];

$anggota = [
    10 =&gt; ["id" =&gt; 10, "nama" =&gt; "Budi"],
    11 =&gt; ["id" =&gt; 11, "nama" =&gt; "Siti"],
];

$peminjaman = ["id" =&gt; 100, "buku_id" =&gt; 1, "anggota_id" =&gt; 10, "status" =&gt; "aktif"];

$hasil = [
    "id" =&gt; $peminjaman["id"],
    "judul_buku" =&gt; $buku[$peminjaman["buku_id"]]["judul"],
    "nama_anggota" =&gt; $anggota[$peminjaman["anggota_id"]]["nama"],
    "status" =&gt; $peminjaman["status"],
];

echo json_encode($hasil, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL;
</code></pre>
<p>Kalau dijalankan, kamu mendapat satu baris gabungan yang enak dibaca: judul buku + nama anggota + status. Itulah rasa dasar relasi: <strong>data diambil dari tempat lain lewat nomor penghubung</strong>.</p>

<h2>Masuk ke Laravel — model apa saja?</h2>
<p>Di proyek Laravel, kita akan punya tiga model inti:</p>
<ol>
  <li><code>Buku</code> — data buku</li>
  <li><code>Anggota</code> — data peminjam</li>
  <li><code>Peminjaman</code> — baris penghubung buku + anggota</li>
</ol>
<p><strong>Awam:</strong> model itu bukan mantra. Anggap saja seperti tiga map data yang beda. Kita sedang mengajari tiap map cara saling kenal, supaya <strong>pengatur kode</strong> (<code>controller</code>) nanti bisa membaca data gabungan tanpa menyambung manual terus-menerus.</p>

<pre><code class="language-bash">php artisan make:model Anggota -m
php artisan make:model Peminjaman -m
</code></pre>

<h2>Cuplikan relasi di model Laravel</h2>
<pre><code class="language-php">&lt;?php
// app/Models/Anggota.php
use Illuminate\Database\Eloquent\Relations\HasMany;

public function peminjaman(): HasMany
{
    return $this-&gt;hasMany(Peminjaman::class);
}
</code></pre>

<pre><code class="language-php">&lt;?php
// app/Models/Peminjaman.php
use Illuminate\Database\Eloquent\Relations\BelongsTo;

public function buku(): BelongsTo
{
    return $this-&gt;belongsTo(Buku::class);
}

public function anggota(): BelongsTo
{
    return $this-&gt;belongsTo(Anggota::class);
}
</code></pre>

<p><strong>Awam:</strong> kalimatnya dibaca begini:</p>
<ul>
  <li><strong>Anggota hasMany Peminjaman</strong> = satu anggota bisa punya banyak slip pinjam</li>
  <li><strong>Peminjaman belongsTo Buku</strong> = satu slip menunjuk satu buku</li>
  <li><strong>Peminjaman belongsTo Anggota</strong> = satu slip menunjuk satu anggota</li>
</ul>

<h2>Membaca data gabungan lewat Eloquent</h2>
<pre><code class="language-php">&lt;?php
$rows = Peminjaman::with(['buku', 'anggota'])->get();

foreach ($rows as $row) {
    echo $row-&gt;anggota-&gt;nama.' meminjam '.$row-&gt;buku-&gt;judul.PHP_EOL;
}
</code></pre>
<p><strong>Awam:</strong> <code>with(['buku', 'anggota'])</code> berarti: “saat ambil slip pinjam, sekalian bawa buku dan anggota yang terhubung”. Jadi saat mencetak, <strong>pemanggil</strong> atau <strong>pengatur kode</strong> tidak pusing memburu nomor satu per satu lagi.</p>

<h2>Pola Dasar — membaca relasi dengan nyaman</h2>
<figure style="margin:1.5rem 0;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1.25rem;color:#1a1a1a" aria-label="Enam langkah memahami relasi Eloquent">
<ol style="list-style:none;padding:0;margin:0;color:#1a1a1a">
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div><strong style="color:#1a1a1a">Tentukan map data</strong><br><span style="color:#1a1a1a">Buku, anggota, dan peminjaman punya tempat masing-masing.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div><strong style="color:#1a1a1a">Pasang nomor penghubung</strong><br><span style="color:#1a1a1a"><code>buku_id</code> dan <code>anggota_id</code> hidup di slip peminjaman.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div><strong style="color:#1a1a1a">Lihat dengan array dulu</strong><br><span style="color:#1a1a1a">Supaya hubungan datanya terlihat jelas, bukan sekadar hafalan fungsi.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div><strong style="color:#1a1a1a">Tuliskan relasi</strong><br><span style="color:#1a1a1a"><code>hasMany</code> di pemilik banyak, <code>belongsTo</code> di penunjuk.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">5</span>
    <div><strong style="color:#1a1a1a">Ambil data gabungan</strong><br><span style="color:#1a1a1a">Pakai <code>with()</code> agar buku dan anggota ikut terbaca.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">6</span>
    <div><strong style="color:#1a1a1a">Siapkan artikel berikutnya</strong><br><span style="color:#1a1a1a">Setelah relasi benar, pagination jadi jauh lebih masuk akal.</span></div>
  </li>
</ol>
</figure>

<h2>Kode lengkap — demo mandiri relasi sederhana</h2>
<p>Simpan sebagai <code>laravel_eloquent_relasi_peminjaman_demo.php</code>, lalu jalankan <code>php laravel_eloquent_relasi_peminjaman_demo.php</code>:</p>

<pre><code class="language-php">&lt;?php
declare(strict_types=1);

$buku = [
    1 =&gt; ["id" =&gt; 1, "judul" =&gt; "Dasar PHP"],
    2 =&gt; ["id" =&gt; 2, "judul" =&gt; "Belajar Laravel"],
];

$anggota = [
    10 =&gt; ["id" =&gt; 10, "nama" =&gt; "Budi"],
    11 =&gt; ["id" =&gt; 11, "nama" =&gt; "Siti"],
];

$peminjaman = [
    ["id" =&gt; 100, "buku_id" =&gt; 1, "anggota_id" =&gt; 10, "status" =&gt; "aktif"],
    ["id" =&gt; 101, "buku_id" =&gt; 2, "anggota_id" =&gt; 11, "status" =&gt; "kembali"],
];

function gabungRelasi(array $row, array $buku, array $anggota): array
{
    return [
        "id" =&gt; $row["id"],
        "judul_buku" =&gt; $buku[$row["buku_id"]]["judul"],
        "nama_anggota" =&gt; $anggota[$row["anggota_id"]]["nama"],
        "status" =&gt; $row["status"],
    ];
}

function demo(string $judul, callable $aksi): void
{
    echo "=== {$judul} ===", PHP_EOL;
    echo json_encode($aksi(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL, PHP_EOL;
}

demo("Satu slip gabungan", function () use ($peminjaman, $buku, $anggota) {
    return gabungRelasi($peminjaman[0], $buku, $anggota);
});

demo("Dua slip gabungan", function () use ($peminjaman, $buku, $anggota) {
    return array_map(fn ($row) =&gt; gabungRelasi($row, $buku, $anggota), $peminjaman);
});
</code></pre>
<p><strong>Awam:</strong> <code>demo(...)</code> hanya pembungkus output. <code>declare(strict_types=1);</code> artinya PHP lebih ketat membaca tipe data; boleh diikuti, tidak wajib dihafal. Yang penting adalah fungsi <code>gabungRelasi</code>: dia mengambil satu slip, lalu mencari buku dan anggota yang cocok berdasarkan nomor.</p>

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
      <td>Nama anggota kosong</td>
      <td><code>anggota_id</code> salah atau model belum terhubung</td>
      <td>Cek nomor penghubung dan relasi <code>belongsTo</code></td>
    </tr>
    <tr>
      <td>Judul buku tidak muncul</td>
      <td><code>buku_id</code> tidak cocok dengan data buku</td>
      <td>Pastikan slip menunjuk buku yang benar</td>
    </tr>
    <tr>
      <td>Data gabungan lambat atau berulang</td>
      <td>Ambil relasi satu per satu tanpa strategi</td>
      <td>Belajar <code>with()</code> dan daftar gabungan dengan rapi</td>
    </tr>
    <tr>
      <td>Relasi terasa abstrak</td>
      <td>Langsung hafal fungsi tanpa melihat contoh data</td>
      <td>Kembali ke array PHP dulu sampai hubungan datanya terasa</td>
    </tr>
  </tbody>
</table>

<h2>Latihan singkat</h2>
<ol>
  <li>Tambahkan satu anggota baru dan satu slip pinjam baru ke demo, lalu cek apakah hasil gabungan ikut muncul.</li>
  <li>Jelaskan ke teman: kenapa <code>peminjaman</code> lebih cocok menjadi tabel penghubung daripada menaruh semua informasi di tabel buku.</li>
  <li>Tulis satu kalimat: beda <code>hasMany</code> dan <code>belongsTo</code> dengan bahasa awam.</li>
</ol>

<h2>FAQ singkat</h2>
<p><strong>Kenapa tidak langsung policy atau resource?</strong><br>
Karena policy, resource, dan pagination baru terasa berguna kalau hubungan datanya sudah benar. Artikel ini memasang fondasi itu.</p>
<p><strong>Apakah satu buku bisa dipinjam berkali-kali?</strong><br>
Secara konsep iya. Itulah kenapa satu buku bisa muncul di banyak baris peminjaman pada waktu yang berbeda.</p>
<p><strong>Ke mana setelah ini?</strong><br>
Berikutnya alami: <strong>Pagination, Filter &amp; Pencarian</strong> untuk daftar slip pinjam yang makin panjang.</p>

<h2>Kesimpulan</h2>
<p>Kamu sudah belajar fondasi relasi: <strong>anggota</strong>, <strong>buku</strong>, dan <strong>peminjaman</strong> saling terhubung. Mulai dari array PHP dulu, lalu pindah ke <code>hasMany</code> dan <code>belongsTo</code> di Laravel. Setelah ini, daftar pinjam panjang akan jauh lebih mudah dibaca dan diolah.</p>
<blockquote>
  <p><strong>Seri 5 progress:</strong> langkah <strong>#64 (ini)</strong> · <strong>1/7</strong> Laravel Lanjutan · prasyarat: <a href="/artikel/laravel-crud-api-buku-ubah-hapus">CRUD Buku (#63)</a> LIVE. Berikutnya: <strong>Pagination, Filter &amp; Pencarian</strong>.</p>
</blockquote>
HTML;
    }
}
