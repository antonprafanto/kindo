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
                'user_id'            => $admin->id,
                'category_id'        => $webCat->id,
                'title'              => 'Relasi Eloquent: Anggota & Peminjaman',
                'title_en'           => 'Eloquent Relations: Members & Borrowing',
                'excerpt_en'         => 'Seri 5 #64: after book CRUD is done, connect members, books, and borrowing records with beginner-friendly Eloquent relations, starting from plain PHP first.',
                'body'               => $this->body(),
                'body_en'            => $this->bodyEn(),
                'status'             => 'published',
                'is_featured'        => false,
                'seo_title'          => 'Relasi Eloquent Laravel — Anggota, Buku, dan Peminjaman',
                'seo_title_en'       => 'Laravel Eloquent Relations — Members, Books, and Borrowing',
                'seo_description'    => 'Seri 5 #64: setelah CRUD buku selesai, sambungkan anggota, buku, dan peminjaman lewat relasi Eloquent. Ramah awam, PHP dulu lalu cuplikan Laravel.',
                'seo_description_en' => 'Seri 5 #64: after book CRUD is complete, connect members, books, and borrowing records with Eloquent relations. Beginner-friendly, tools-first, and plain PHP first before Laravel snippets.',
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

<h2>Persiapan — alat yang kamu buka</h2>
<p><strong>Alat yang dipakai di artikel ini</strong> (fondasi dari <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a> dan <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a> — tidak ada unduhan Composer baru hari ini):</p>
<ul>
  <li><strong>Explorer</strong> — cek folder proyek <code>perpustakaan-api</code>, lalu lihat lokasi <code>app\Models</code> dan folder <code>database\migrations</code>.</li>
  <li><strong>Terminal</strong> — Laragon: menu <em>Terminal</em> · XAMPP: tombol <em>Shell</em>. Hindari CMD/PowerShell dari Start Menu kalau PATH PHP-mu belum rapi.</li>
  <li><strong>Editor teks</strong> — Notepad / VS Code — untuk membuka model hasil <code>make:model</code>. Contoh paling aman: <code>notepad app\Models\Anggota.php</code> lalu <code>notepad app\Models\Peminjaman.php</code>.</li>
  <li><strong>Browser</strong> — opsional saja. Hari ini inti uji ada di terminal dan file contoh PHP, bukan di address bar browser.</li>
</ul>
<p><strong>Awam:</strong> untuk artikel ini <strong>satu terminal sebenarnya cukup</strong>. Tapi kalau kamu mau menjaga <code>php artisan serve</code> dari artikel sebelumnya tetap hidup, pakai <strong>terminal kedua</strong> untuk menjalankan demo PHP dan perintah Artisan di bawah.</p>
<p>Buka terminal Laragon/Shell XAMPP, masuk ke folder proyek:</p>
<pre><code class="language-bash">cd C:\laragon\www\perpustakaan-api
</code></pre>
<p>Di XAMPP biasanya: <code>cd C:\xampp\htdocs\perpustakaan-api</code>. Sesuaikan kalau foldermu beda.</p>
<p><strong>Install-dari-nol:</strong> kalau <code>php</code> atau <code>composer</code> belum dikenali terminal, kembali dulu ke <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a>. Kalau struktur folder proyek masih membingungkan, ulangi <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a>.</p>

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
<p><strong>Awam — cara menguji bagian ini:</strong> potongan pertama dan kedua di atas boleh kamu salin ke satu file misalnya <code>relasi-dasar.php</code>, lalu jalankan di terminal: <code>php relasi-dasar.php</code>. Kalau terminal menjawab JSON berisi <code>judul_buku</code> dan <code>nama_anggota</code>, berarti sintaks dasarmu sudah sehat sebelum menyentuh Laravel.</p>

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
<p>Setelah perintah itu jalan, Laravel akan membuat file model dan file migration. Buka modelnya dengan editor:</p>
<pre><code class="language-bash">notepad app\Models\Anggota.php
notepad app\Models\Peminjaman.php
</code></pre>
<p><strong>Awam:</strong> kalau kamu melihat pesan “Could not open input file” atau “php tidak dikenal”, itu masalah terminal/PATH, bukan relasi. Kembali ke artikel instalasi dulu; jangan memaksa lanjut sambil error dasarnya belum beres.</p>

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
<p><strong>Tool apa yang dibuka dulu?</strong><br>
Mulai dari Explorer untuk memastikan folder proyek benar, lalu satu terminal di folder proyek, lalu editor. Browser opsional saja hari ini. Kalau <code>serve</code> dari artikel sebelumnya masih dibiarkan hidup, pakai terminal kedua untuk demo PHP dan perintah Artisan.</p>
<p><strong>Potongan sintaks diuji di mana?</strong><br>
Potongan PHP biasa diuji di terminal dengan <code>php nama-file.php</code>. Potongan Laravel ditempel ke file model seperti <code>app\Models\Anggota.php</code> dan <code>app\Models\Peminjaman.php</code>.</p>
<p><strong>Ke mana setelah ini?</strong><br>
Berikutnya alami: <strong>Pagination, Filter &amp; Pencarian</strong> untuk daftar slip pinjam yang makin panjang.</p>

<h2>Kesimpulan</h2>
<p>Kamu sudah belajar fondasi relasi: <strong>anggota</strong>, <strong>buku</strong>, dan <strong>peminjaman</strong> saling terhubung. Mulai dari array PHP dulu, lalu pindah ke <code>hasMany</code> dan <code>belongsTo</code> di Laravel. Setelah ini, daftar pinjam panjang akan jauh lebih mudah dibaca dan diolah.</p>
<blockquote>
  <p><strong>Seri 5 progress:</strong> langkah <strong>#64 (ini)</strong> · <strong>1/7</strong> Laravel Lanjutan · prasyarat: <a href="/artikel/laravel-crud-api-buku-ubah-hapus">CRUD API Buku: Ubah &amp; Hapus (#63)</a> LIVE. Berikutnya: <strong>Pagination, Filter &amp; Pencarian</strong>.</p>
</blockquote>
HTML;
    }

    private function bodyEn(): string
    {
        $html = <<<'HTML'
<h2>Introduction — connecting member cards to borrowing slips</h2>
<p>This article is <strong>#64 (this article)</strong> in <strong>Seri 5: Laravel Lanjutan</strong>. After the from-scratch Laravel path ended in <a href="/artikel/laravel-crud-api-buku-ubah-hapus">CRUD API Buku: Ubah &amp; Hapus (#63)</a>, we now move into the next real layer: <strong>relationships between data</strong>.</p>
<p>In a library, a book does not stand alone. There is a <strong>member</strong> who borrows it, a <strong>book</strong> being borrowed, and a <strong>borrowing record</strong> that connects both. In Laravel, this kind of connection is called an <strong>Eloquent relation</strong>. Later this combined result is read by the <strong>caller</strong>, meaning the app or tool <strong>that calls the API</strong>.</p>
<p><strong>Beginner:</strong> imagine three cards. Card one = member data. Card two = book data. Card three = a borrowing slip that says “who borrowed which book”. That slip points to the other two cards. Today we learn how to write that connection neatly.</p>

<blockquote>
  <p><strong>Prerequisite:</strong> finish <a href="/artikel/laravel-crud-api-buku-ubah-hapus">CRUD API Buku: Ubah &amp; Hapus (#63)</a>, understand <a href="/artikel/laravel-auth-api-dasar">Auth API Dasar: Login &amp; Kartu Anggota (#61)</a>, and keep the foundations from <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a> / <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a>. Use <strong>Laravel 13+</strong> and <strong>PHP 8.3+</strong>.</p>
</blockquote>

<h2>Feature spec — what gets finished today?</h2>
<p>These are the three targets:</p>
<ol>
  <li><strong>Create relation models</strong> — <code>Anggota</code>, <code>Buku</code>, and <code>Peminjaman</code> connect to each other.</li>
  <li><strong>Read combined data</strong> — one borrowing record can show the member name and book title together.</li>
  <li><strong>Prepare the foundation for the next article</strong> — pagination, policies, resources, and tests all depend on these relations.</li>
</ol>
<p><strong>Beginner:</strong> after this article, you are not building the “permission” counter or the “pretty JSON” counter yet. You are building the <strong>map</strong> so Laravel knows which book belongs to which borrowing slip, which member is involved, and what answer the API <strong>caller</strong> should read later.</p>

<figure role="img" aria-label="Relations between book, borrowing, and member" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" style="display:block;max-width:760px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif" viewBox="0 0 760 250">
  <defs>
    <marker id="laravel64relasiArrow" orient="auto" markerWidth="8" markerHeight="8" refX="7" refY="4" viewBox="0 0 8 8">
      <path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/>
    </marker>
  </defs>
  <rect width="760" height="250" fill="#F5F5F0"/>
  <text x="24" y="36" fill="#1a1a1a" font-size="16" font-weight="700">Book -&gt; Borrowing &lt;- Member</text>
  <rect x="28" y="72" width="180" height="90" rx="10" fill="#ffffff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="118" y="107" text-anchor="middle" fill="#1a1a1a" font-size="16" font-weight="700">Book</text>
  <text x="118" y="132" text-anchor="middle" fill="#1a1a1a" font-size="12">one book can appear</text>
  <text x="118" y="150" text-anchor="middle" fill="#1a1a1a" font-size="12">in many borrowing slips</text>
  <line x1="208" y1="116" x2="288" y2="116" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel64relasiArrow)"/>
  <rect x="290" y="58" width="180" height="120" rx="10" fill="#1a1a1a" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="380" y="100" text-anchor="middle" fill="#ffffff" font-size="16" font-weight="700">Borrowing</text>
  <text x="380" y="126" text-anchor="middle" fill="#ffffff" font-size="12">the connector for who</text>
  <text x="380" y="144" text-anchor="middle" fill="#ffffff" font-size="12">borrowed which book</text>
  <line x1="470" y1="116" x2="550" y2="116" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel64relasiArrow)"/>
  <rect x="552" y="72" width="180" height="90" rx="10" fill="#ffffff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="642" y="107" text-anchor="middle" fill="#1a1a1a" font-size="16" font-weight="700">Member</text>
  <text x="642" y="132" text-anchor="middle" fill="#1a1a1a" font-size="12">one member can have</text>
  <text x="642" y="150" text-anchor="middle" fill="#1a1a1a" font-size="12">many borrowing records</text>
  <text x="24" y="212" fill="#1a1a1a" font-size="13">This relation will be reused in pagination, policy, resource, and capstone.</text>
</svg>
<figcaption>Eloquent relations help Laravel read the connection between books, members, and borrowing records without manually joining them every time.</figcaption>
</figure>

<h2>Terms — a quick relation glossary</h2>
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
      <td>Relation</td>
      <td>A connection between data</td>
      <td>Who is linked to whom</td>
    </tr>
    <tr>
      <td><code>belongsTo</code></td>
      <td>This row “belongs” to one other row</td>
      <td>One borrowing slip belongs to one book and one member</td>
    </tr>
    <tr>
      <td><code>hasMany</code></td>
      <td>One row has many partners</td>
      <td>One member can have many borrowing slips</td>
    </tr>
    <tr>
      <td><code>buku_id</code></td>
      <td>The book number pointed to by the slip</td>
      <td>Connection key to the books table</td>
    </tr>
    <tr>
      <td><code>anggota_id</code></td>
      <td>The member number who borrowed it</td>
      <td>Connection key to the members table</td>
    </tr>
  </tbody>
</table>
<p>Our learning order is: <strong>plain PHP arrays first -&gt; see the connection with your eyes -&gt; then wrap it with <code>hasMany</code> and <code>belongsTo</code> in Eloquent</strong>.</p>

<h2>Preparation — tools to open</h2>
<p><strong>Tools used in this article</strong> (built on <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a> and <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a> — there is <strong>no new Composer download</strong> today):</p>
<ul>
  <li><strong>Explorer</strong> — check the <code>perpustakaan-api</code> project folder, then look at <code>app\Models</code> and <code>database\migrations</code>.</li>
  <li><strong>Terminal</strong> — Laragon: <em>Terminal</em> menu · XAMPP: <em>Shell</em> button. Avoid Start Menu CMD/PowerShell if your PHP PATH is still messy.</li>
  <li><strong>Text editor</strong> — Notepad / VS Code — to open models created by <code>make:model</code>. Safe examples: <code>notepad app\Models\Anggota.php</code> and <code>notepad app\Models\Peminjaman.php</code>.</li>
  <li><strong>Browser</strong> — optional only. Today the main checks happen in the terminal and in the plain PHP demo file, not in the browser address bar.</li>
</ul>
<p><strong>Beginner:</strong> for this article, <strong>one terminal is actually enough</strong>. But if you still want to keep <code>php artisan serve</code> running from the previous article, use a <strong>second terminal</strong> for the PHP demo file and the Artisan commands below.</p>
<p>Open Laragon Terminal / XAMPP Shell, then move into the project folder:</p>
<pre><code class="language-bash">cd C:\laragon\www\perpustakaan-api
</code></pre>
<p>In XAMPP it is usually: <code>cd C:\xampp\htdocs\perpustakaan-api</code>. Adjust the path if your folder is different.</p>
<p><strong>Install-from-scratch:</strong> if <code>php</code> or <code>composer</code> is not recognized in the terminal, return to <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a>. If your project folder structure is still confusing, review <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a> first.</p>

<h2>Why start with plain PHP first?</h2>
<p>If you jump straight into three Eloquent models, beginners often memorize the functions without really understanding the data relationship. So we start from arrays: see the book, see the member, see the borrowing slip, then match the numbers.</p>

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
<p><strong>Beginner:</strong> there is no Laravel relation yet here. But the connection already exists: borrowing slip <code>100</code> points to book <code>1</code> and member <code>10</code>. Laravel will later help read it more neatly.</p>

<h2>Combine the data with PHP first</h2>
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
<p>If you run it, you get one readable combined row: book title + member name + status. That is the basic feel of a relation: <strong>data is taken from another place by using a connector number</strong>.</p>
<p><strong>Beginner — how to test this part:</strong> copy the first and second snippets into one file, for example <code>relasi-dasar.php</code>, then run <code>php relasi-dasar.php</code> in the terminal. If the terminal prints JSON with <code>judul_buku</code> and <code>nama_anggota</code>, your base syntax is healthy before touching Laravel.</p>

<h2>Move into Laravel — which models do we need?</h2>
<p>In the Laravel project, we will use three core models:</p>
<ol>
  <li><code>Buku</code> — book data</li>
  <li><code>Anggota</code> — member data</li>
  <li><code>Peminjaman</code> — the connector row between book + member</li>
</ol>
<p><strong>Beginner:</strong> a model is not a magic spell. Think of it as three different data folders. We are teaching each folder how to recognize the others so the <strong>code organizer</strong> (<code>controller</code>) can read combined data without manually reconnecting everything again and again.</p>

<pre><code class="language-bash">php artisan make:model Anggota -m
php artisan make:model Peminjaman -m
</code></pre>
<p>After that command runs, Laravel creates the model files and migration files. Open the models in your editor:</p>
<pre><code class="language-bash">notepad app\Models\Anggota.php
notepad app\Models\Peminjaman.php
</code></pre>
<p><strong>Beginner:</strong> if you see “Could not open input file” or “php is not recognized”, that is a terminal/PATH problem, not a relation problem. Go back to the installation article first; do not force the next step while the basic error is still red.</p>

<h2>Relation snippets inside Laravel models</h2>
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

<p><strong>Beginner:</strong> read the sentences like this:</p>
<ul>
  <li><strong>Anggota hasMany Peminjaman</strong> = one member can have many borrowing slips</li>
  <li><strong>Peminjaman belongsTo Buku</strong> = one slip points to one book</li>
  <li><strong>Peminjaman belongsTo Anggota</strong> = one slip points to one member</li>
</ul>

<h2>Read combined data through Eloquent</h2>
<pre><code class="language-php">&lt;?php
$rows = Peminjaman::with(['buku', 'anggota'])->get();

foreach ($rows as $row) {
    echo $row-&gt;anggota-&gt;nama.' meminjam '.$row-&gt;buku-&gt;judul.PHP_EOL;
}
</code></pre>
<p><strong>Beginner:</strong> <code>with(['buku', 'anggota'])</code> means: “when taking borrowing slips, bring the connected book and member too”. So when printing the result, the <strong>caller</strong> or the <strong>code organizer</strong> does not have to chase the numbers one by one anymore.</p>

<h2>Basic Pattern — reading relations comfortably</h2>
<figure style="margin:1.5rem 0;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1.25rem;color:#1a1a1a" aria-label="Six steps to understand Eloquent relations">
<ol style="list-style:none;padding:0;margin:0;color:#1a1a1a">
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div><strong style="color:#1a1a1a">Name the data folders</strong><br><span style="color:#1a1a1a">Books, members, and borrowing have their own places.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div><strong style="color:#1a1a1a">Install the connector numbers</strong><br><span style="color:#1a1a1a"><code>buku_id</code> and <code>anggota_id</code> live in the borrowing slip.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div><strong style="color:#1a1a1a">See it with arrays first</strong><br><span style="color:#1a1a1a">So the relation is visible, not just a memorized function.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div><strong style="color:#1a1a1a">Write the relations</strong><br><span style="color:#1a1a1a"><code>hasMany</code> on the owner of many, <code>belongsTo</code> on the pointer.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">5</span>
    <div><strong style="color:#1a1a1a">Fetch the combined data</strong><br><span style="color:#1a1a1a">Use <code>with()</code> so the book and member come along.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">6</span>
    <div><strong style="color:#1a1a1a">Prepare the next article</strong><br><span style="color:#1a1a1a">Once the relation is right, pagination becomes much more sensible.</span></div>
  </li>
</ol>
</figure>

<h2>Full code — a self-run simple relation demo</h2>
<p>Save it as <code>laravel_eloquent_relasi_peminjaman_demo.php</code>, then run <code>php laravel_eloquent_relasi_peminjaman_demo.php</code>:</p>

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
<p><strong>Beginner:</strong> <code>demo(...)</code> only wraps the output. <code>declare(strict_types=1);</code> means PHP reads data types more strictly; you can follow it, but you do not have to memorize it. The important part is <code>gabungRelasi</code>: it takes one borrowing slip, then finds the matching book and member by number.</p>

<h2>Common mistakes</h2>
<table>
  <thead>
    <tr>
      <th>Symptom</th>
      <th>Typical cause</th>
      <th>Beginner fix</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Member name is empty</td>
      <td><code>anggota_id</code> is wrong or the model is not connected yet</td>
      <td>Check the connector number and the <code>belongsTo</code> relation</td>
    </tr>
    <tr>
      <td>Book title does not appear</td>
      <td><code>buku_id</code> does not match the book data</td>
      <td>Make sure the slip points to the correct book</td>
    </tr>
    <tr>
      <td>Combined data feels slow or repetitive</td>
      <td>Relations are fetched one by one without strategy</td>
      <td>Learn <code>with()</code> and fetch the combined list neatly</td>
    </tr>
    <tr>
      <td>Relations feel abstract</td>
      <td>You memorized functions before seeing the data example</td>
      <td>Go back to the plain PHP arrays until the relation feels real</td>
    </tr>
  </tbody>
</table>

<h2>Practice</h2>
<ol>
  <li>Add one new member and one new borrowing slip to the demo, then check whether the combined result appears too.</li>
  <li>Explain to a friend: why is <code>peminjaman</code> better as a connector table than putting everything into the books table?</li>
  <li>Write one sentence: what is the difference between <code>hasMany</code> and <code>belongsTo</code> in beginner language?</li>
</ol>

<h2>FAQ</h2>
<p><strong>Why not jump straight to policy or resource?</strong><br>
Because policy, resource, and pagination only feel useful after the data relation itself is correct. This article installs that foundation.</p>
<p><strong>Can one book be borrowed many times?</strong><br>
Conceptually yes. That is why one book can appear in many borrowing rows at different times.</p>
<p><strong>Which tools should I open first?</strong><br>
Start with Explorer to make sure the project folder is right, then one terminal in the project folder, then your editor. Browser is optional today. If you keep <code>serve</code> alive from the previous article, use a second terminal for the demo file and Artisan commands.</p>
<p><strong>Where should I test the snippets?</strong><br>
Plain PHP snippets are tested in a normal terminal with <code>php nama-file.php</code>. Laravel relation snippets are copied into model files like <code>app\Models\Anggota.php</code> and <code>app\Models\Peminjaman.php</code>.</p>
<p><strong>Where next?</strong><br>
The natural next step is <strong>Pagination, Filter &amp; Pencarian</strong> for borrowing lists that are getting longer.</p>

<h2>Summary</h2>
<p>You have learned the foundation of relations: <strong>members</strong>, <strong>books</strong>, and <strong>borrowing</strong> connect to each other. Start from plain PHP arrays first, then move into <code>hasMany</code> and <code>belongsTo</code> in Laravel. After this, long borrowing lists become much easier to read and process.</p>
<blockquote>
  <p><strong>Seri 5 progress:</strong> step <strong>#64 (this article)</strong> · <strong>1/7</strong> Laravel Lanjutan · prerequisite: <a href="/artikel/laravel-crud-api-buku-ubah-hapus">CRUD API Buku: Ubah &amp; Hapus (#63)</a> LIVE. Next: <strong>Pagination, Filter &amp; Pencarian</strong>.</p>
</blockquote>
HTML;

        return str_replace(
            [
                'Seri 5: Laravel Lanjutan',
                'CRUD API Buku: Ubah &amp; Hapus (#63)',
                'Auth API Dasar: Login &amp; Kartu Anggota (#61)',
                'Instal PHP, Composer &amp; Proyek Laravel (#56)',
                'Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)',
                'Pagination, Filter &amp; Pencarian',
                'Laravel Lanjutan',
            ],
            [
                'Seri 5: Advanced Laravel',
                'CRUD Book API: Update &amp; Delete (#63)',
                'Basic Auth API: Login &amp; Member Card (#61)',
                'Install PHP, Composer &amp; Your First Laravel Project (#56)',
                'Folder Structure, <code>.env</code> &amp; Artisan Laravel (#57)',
                'Pagination, Filtering &amp; Search',
                'Advanced Laravel',
            ],
            $html
        );
    }
}
