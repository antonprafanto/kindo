<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article65Seeder extends Seeder
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
                'user_id'            => $admin->id,
                'category_id'        => $webCat->id,
                'title'              => 'Pagination, Filter & Pencarian',
                'title_en'           => 'Pagination, Filtering & Search',
                'excerpt'            => 'Seri 5 #65: setelah relasi anggota & pinjam, potong daftar per halaman, filter status, dan cari dengan ?q=. Ramah awam, PHP dulu lalu paginate().',
                'excerpt_en'         => 'Seri 5 #65: after relations, learn to page borrowing lists, filter status, and search with ?q= — plain PHP first, then Laravel paginate().',
                'body'               => $this->body(),
                'body_en'            => $this->bodyEn(),
                'status'             => 'published',
                'is_featured'        => false,
                'seo_title'          => 'Pagination Filter & Pencarian API Laravel — Daftar Peminjaman',
                'seo_title_en'       => 'Laravel Pagination Filter & Search API — Borrowing Lists',
                'seo_description'    => 'Seri 5 #65: setelah relasi anggota & pinjam, potong daftar per halaman, filter status, dan cari dengan ?q=. Ramah awam, PHP dulu lalu paginate().',
                'seo_description_en' => 'Seri 5 #65: after member/borrowing relations, page the list, filter status, and search with ?q=. Beginner-friendly, tools-first, plain PHP before paginate().',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        // published_at setelah #64 supaya urutan "Terbaru" di /artikel tidak menjatuhkan #65 ke tengah daftar
        $prevPublished = Article::where('slug', 'laravel-eloquent-relasi-peminjaman')->value('published_at');
        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        } elseif ($prevPublished && $article->published_at <= $prevPublished) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['laravel', 'php', 'api', 'http', 'web', 'eloquent', 'database'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel ke-65 berhasil dipublish: '.$article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — daftar pinjam panjang butuh potongan</h2>
<p>Artikel ini adalah <strong>#65 (ini)</strong> di <strong>Seri 5: Laravel Lanjutan</strong>. Setelah relasi anggota, buku, dan peminjaman selesai di <a href="/artikel/laravel-eloquent-relasi-peminjaman">Relasi Eloquent: Anggota &amp; Peminjaman (#64)</a>, daftar slip pinjam di proyekmu mulai terasa panjang.</p>
<p>Satu respons yang memuat ratusan baris bikin lambat dan sulit dibaca. Hari ini kita belajar tiga gerakan dasar: <strong>filter status</strong> (aktif atau kembali), <strong>pencarian</strong> lewat <code>?q=</code> pada judul buku atau nama anggota, lalu <strong>pagination</strong> supaya tiap halaman hanya menampilkan potongan kecil. Urutan yang benar: <strong>saring -&gt; cari -&gt; potong</strong> — jangan potong dulu baru saring.</p>
<p><strong>Awam:</strong> bayangkan tumpukan slip pinjam di meja loket. Petugas tidak menyerahkan semua slip sekaligus. Dia memilih slip yang statusnya cocok, mencari nama atau judul buku yang kamu sebut, lalu hanya mengambil segenggam untuk halaman pertama. API daftar pinjam bekerja dengan logika yang sama; jawaban itu dibaca oleh <strong>pemanggil</strong>, yaitu aplikasi atau alat <strong>yang memanggil API</strong>.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> sudah selesai <a href="/artikel/laravel-eloquent-relasi-peminjaman">Relasi Eloquent: Anggota &amp; Peminjaman (#64)</a>, paham fondasi <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a> / <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a>. Pakai <strong>Laravel 13+</strong> — butuh <strong>PHP 8.3+</strong>.</p>
</blockquote>

<h2>Spesifikasi fitur — apa yang selesai hari ini?</h2>
<p>Tiga hal ini yang kita kejar:</p>
<ol>
  <li><strong>Pagination</strong> — daftar pinjam dipotong per halaman dengan <code>page</code> dan <code>per_page</code>.</li>
  <li><strong>Filter status</strong> — hanya tampilkan slip <code>aktif</code> atau <code>kembali</code> lewat parameter <code>status</code>.</li>
  <li><strong>Pencarian</strong> — cari judul buku atau nama anggota lewat <code>?q=</code> (bisa juga disebut kata kunci pencarian; di roadmap kita pakai nama <code>q</code>).</li>
</ol>
<p><strong>Awam:</strong> selesai artikel ini, kamu belum membangun izin siapa boleh ubah pinjam. Kamu sedang membuat daftar panjang terasa rapi untuk <strong>pemanggil</strong> API: tidak semua baris dilontarkan sekaligus, tapi dipilih dan dipotong dengan aturan yang jelas.</p>

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
      <td>Potong daftar per halaman</td>
      <td>Halaman 1, halaman 2, dan seterusnya</td>
    </tr>
    <tr>
      <td><code>page</code></td>
      <td>Nomor halaman yang diminta</td>
      <td>Mulai dari 1, bukan 0</td>
    </tr>
    <tr>
      <td><code>per_page</code></td>
      <td>Jumlah baris per halaman</td>
      <td>Misalnya 3 atau 10 baris</td>
    </tr>
    <tr>
      <td>Filter <code>status</code></td>
      <td>Saring slip menurut kondisi pinjam</td>
      <td><code>aktif</code> atau <code>kembali</code></td>
    </tr>
    <tr>
      <td><code>q</code></td>
      <td>Kata kunci pencarian</td>
      <td>Cocokkan judul buku atau nama anggota</td>
    </tr>
  </tbody>
</table>
<p>Urutan belajar kita: <strong>filter status -&gt; cari dengan <code>q</code> -&gt; baru potong per halaman</strong>. Kalau urutan dibalik, hasil bisa salah karena baris yang tidak relevan ikut masuk ke potongan halaman.</p>

<h2>Persiapan — alat yang kamu buka</h2>
<p><strong>Alat yang dipakai di artikel ini</strong> (fondasi dari <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a> dan <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a> — tidak ada unduhan Composer baru hari ini):</p>
<ul>
  <li><strong>Explorer</strong> — cek folder proyek <code>perpustakaan-api</code>, lalu lihat <code>app\Http\Controllers</code> untuk <strong>pengatur kode</strong> daftar pinjam.</li>
  <li><strong>Terminal</strong> — Laragon: menu <em>Terminal</em> · XAMPP: tombol <em>Shell</em>. Hindari CMD/PowerShell dari Start Menu kalau PATH PHP-mu belum rapi.</li>
  <li><strong>Editor teks</strong> — Notepad / VS Code — untuk membuka atau membuat <strong>pengatur kode</strong>. Contoh: <code>notepad app\Http\Controllers\PeminjamanController.php</code>.</li>
  <li><strong>Browser</strong> — opsional. Inti uji hari ini ada di terminal; browser berguna kalau kamu sudah menjalankan <code>php artisan serve</code> dan ingin uji lewat alamat URL.</li>
</ul>
<p><strong>Awam:</strong> untuk artikel ini <strong>satu terminal sebenarnya cukup</strong> — jalankan <code>php laravel_pagination_filter_pencarian_demo.php</code> di folder proyek. Kalau <code>php artisan serve</code> dari artikel sebelumnya masih hidup, pakai <strong>terminal kedua</strong> untuk demo PHP dan perintah <code>curl.exe</code> saat menguji rute Laravel. Kalau butuh jendela kedua: Laragon — klik menu <em>Terminal</em> lagi · XAMPP — klik tombol <em>Shell</em> lagi, lalu <code>cd</code> ke folder proyek yang sama.</p>
<p>Buka terminal Laragon/Shell XAMPP, masuk ke folder proyek:</p>
<pre><code class="language-bash">cd C:\laragon\www\perpustakaan-api
</code></pre>
<p>Di XAMPP biasanya: <code>cd C:\xampp\htdocs\perpustakaan-api</code>. Sesuaikan kalau foldermu beda.</p>
<p><strong>Install-dari-nol:</strong> kalau <code>php</code> atau <code>composer</code> belum dikenali terminal, kembali dulu ke <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a>. Kalau struktur folder proyek masih membingungkan, ulangi <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a>.</p>

<h2>Kenapa PHP biasa dulu?</h2>
<p>Kalau langsung loncat ke <code>paginate()</code> di Laravel, pemula sering bingung urutan kerja: saring, cari, potong. Maka kita mulai dari array PHP biasa supaya setiap langkah terlihat jelas sebelum dibungkus Eloquent.</p>

<pre><code class="language-php">&lt;?php
$daftar = [
    ["id" =&gt; 100, "judul_buku" =&gt; "Dasar PHP", "nama_anggota" =&gt; "Budi", "status" =&gt; "aktif"],
    ["id" =&gt; 101, "judul_buku" =&gt; "Belajar Laravel", "nama_anggota" =&gt; "Siti", "status" =&gt; "kembali"],
    ["id" =&gt; 102, "judul_buku" =&gt; "Dasar PHP", "nama_anggota" =&gt; "Andi", "status" =&gt; "aktif"],
    ["id" =&gt; 103, "judul_buku" =&gt; "Matematika", "nama_anggota" =&gt; "Budi", "status" =&gt; "kembali"],
    ["id" =&gt; 104, "judul_buku" =&gt; "Biologi", "nama_anggota" =&gt; "Rina", "status" =&gt; "aktif"],
    ["id" =&gt; 105, "judul_buku" =&gt; "Fisika", "nama_anggota" =&gt; "Dewi", "status" =&gt; "kembali"],
];
</code></pre>
<p><strong>Awam:</strong> ini daftar gabungan seperti hasil relasi di artikel sebelumnya: tiap baris sudah punya <code>judul_buku</code>, <code>nama_anggota</code>, dan <code>status</code>. Pagination hanya mengatur <strong>berapa baris yang ditampilkan</strong> dari daftar ini.</p>

<h2>Alur daftar — saring, cari, potong</h2>
<p>Gerakan yang benar selalu sama:</p>
<ol>
  <li><strong>Saring status</strong> — kalau <code>status=aktif</code>, buang slip yang sudah kembali.</li>
  <li><strong>Cari dengan <code>q</code></strong> — cocokkan kata kunci ke judul buku atau nama anggota.</li>
  <li><strong>Potong per halaman</strong> — ambil segenggam baris sesuai <code>page</code> dan <code>per_page</code>.</li>
</ol>

<pre><code class="language-php">&lt;?php
// Salin ke file misalnya daftar-saring.php lalu jalankan: php daftar-saring.php
$daftar = [
    ["id" =&gt; 100, "judul_buku" =&gt; "Dasar PHP", "nama_anggota" =&gt; "Budi", "status" =&gt; "aktif"],
    ["id" =&gt; 101, "judul_buku" =&gt; "Belajar Laravel", "nama_anggota" =&gt; "Siti", "status" =&gt; "kembali"],
    ["id" =&gt; 102, "judul_buku" =&gt; "Dasar PHP", "nama_anggota" =&gt; "Andi", "status" =&gt; "aktif"],
    ["id" =&gt; 103, "judul_buku" =&gt; "Matematika", "nama_anggota" =&gt; "Budi", "status" =&gt; "kembali"],
    ["id" =&gt; 104, "judul_buku" =&gt; "Biologi", "nama_anggota" =&gt; "Rina", "status" =&gt; "aktif"],
    ["id" =&gt; 105, "judul_buku" =&gt; "Fisika", "nama_anggota" =&gt; "Dewi", "status" =&gt; "kembali"],
];

$hasil = $daftar;

// 1) saring status
$status = "aktif";
$hasil = array_values(array_filter($hasil, fn ($row) =&gt; $row["status"] === $status));

// 2) cari q
$q = "php";
$hasil = array_values(array_filter($hasil, function ($row) use ($q) {
    $needle = mb_strtolower($q);
    return str_contains(mb_strtolower($row["judul_buku"]), $needle)
        || str_contains(mb_strtolower($row["nama_anggota"]), $needle);
}));

// 3) potong halaman
$page = 1;
$perPage = 2;
$offset = ($page - 1) * $perPage;
$potongan = array_slice($hasil, $offset, $perPage);

echo json_encode($potongan, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL;
</code></pre>
<p><strong>Awam — cara menguji bagian ini:</strong> salin potongan di atas ke <code>daftar-saring.php</code>, lalu di terminal jalankan <code>php daftar-saring.php</code>. Kalau muncul JSON berisi slip <code>aktif</code> yang judulnya mengandung “php”, urutan saring-cari-potong sudah sehat. Kalau kamu memotong dulu baru menyaring, halaman 2 bisa berisi slip yang seharusnya tidak ikut — urutan <strong>saring -&gt; cari -&gt; potong</strong> menjaga setiap halaman konsisten.</p>

<figure role="img" aria-label="Alur saring, cari, potong daftar pinjam" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" style="display:block;max-width:760px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif" viewBox="0 0 760 220">
  <defs>
    <marker id="laravel65pageArrow" orient="auto" markerWidth="8" markerHeight="8" refX="7" refY="4" viewBox="0 0 8 8">
      <path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/>
    </marker>
  </defs>
  <rect width="760" height="220" fill="#F5F5F0"/>
  <text x="24" y="36" fill="#1a1a1a" font-size="16" font-weight="700">Saring -&gt; Cari q -&gt; Potong halaman</text>
  <rect x="28" y="64" width="150" height="72" rx="10" fill="#ffffff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="103" y="96" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">Semua slip</text>
  <text x="103" y="122" text-anchor="middle" fill="#1a1a1a" font-size="12">daftar panjang</text>
  <line x1="178" y1="100" x2="228" y2="100" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel65pageArrow)"/>
  <rect x="230" y="64" width="150" height="72" rx="10" fill="#ffffff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="305" y="96" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">Filter status</text>
  <text x="305" y="122" text-anchor="middle" fill="#1a1a1a" font-size="12">aktif / kembali</text>
  <line x1="380" y1="100" x2="430" y2="100" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel65pageArrow)"/>
  <rect x="432" y="64" width="150" height="72" rx="10" fill="#ffffff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="493" y="96" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">Cari q</text>
  <text x="493" y="122" text-anchor="middle" fill="#1a1a1a" font-size="12">judul / nama</text>
  <line x1="582" y1="100" x2="632" y2="100" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel65pageArrow)"/>
  <rect x="634" y="64" width="120" height="72" rx="10" fill="#1a1a1a" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="694" y="96" text-anchor="middle" fill="#ffffff" font-size="14" font-weight="700">Halaman</text>
  <text x="694" y="122" text-anchor="middle" fill="#ffffff" font-size="12">page + per_page</text>
  <text x="24" y="188" fill="#1a1a1a" font-size="13">Jangan potong dulu — saring dan cari baru memotong supaya tiap halaman konsisten.</text>
</svg>
<figcaption>Urutan yang benar: filter status, lalu pencarian <code>q</code>, baru pagination memotong hasil.</figcaption>
</figure>

<h2>Laravel — cuplikan pagination &amp; filter</h2>
<p>Di proyek Laravel, <strong>pengatur kode</strong> daftar pinjam bisa membaca parameter URL lalu membangun query Eloquent dengan urutan yang sama.</p>

<pre><code class="language-php">&lt;?php
// Cuplikan Laravel (bukan file mandiri)
// app/Http/Controllers/PeminjamanController.php

$status = request("status");
$q = request("q");
$perPage = (int) request("per_page", 10);

$query = Peminjaman::query()-&gt;with(["buku", "anggota"]);

if ($status) {
    $query-&gt;where("status", $status);
}

if ($q) {
    $query-&gt;where(function ($builder) use ($q) {
        $builder-&gt;whereHas("buku", fn ($b) =&gt; $b-&gt;where("judul", "like", "%{$q}%"))
            -&gt;orWhereHas("anggota", fn ($a) =&gt; $a-&gt;where("nama", "like", "%{$q}%"));
    });
}

$hasil = $query-&gt;paginate($perPage);
</code></pre>
<p><strong>Awam:</strong> <code>paginate()</code> otomatis menghitung total, halaman aktif, dan potongan data. <code>whereHas</code> memakai relasi dari artikel sebelumnya: cari judul lewat tabel buku, cari nama lewat tabel anggota. Parameter <code>q</code> adalah nama standar di roadmap kita; kata <code>cari</code> kadang dipakai di tutorial lain, tapi di sini kita konsisten dengan <code>q</code>. Cuplikan ini <strong>bukan file mandiri</strong> — tempel ke <code>PeminjamanController</code> kalau rute daftar pinjam sudah ada. Kalau belum, kuasai demo PHP dulu; urutan saring-cari-potong tetap sama.</p>
<p>Kalau <code>php artisan serve</code> sudah jalan di terminal pertama, uji di terminal kedua. Di Windows ketik <code>curl.exe</code> (bukan alias <code>curl</code> saja) supaya PowerShell tidak bingung:</p>
<pre><code class="language-bash">curl.exe "http://127.0.0.1:8000/api/peminjaman?status=aktif&amp;q=php&amp;page=1&amp;per_page=3"
</code></pre>
<p><strong>Awam:</strong> respons JSON dari <code>curl.exe</code> adalah cara cepat melihat apakah filter, pencarian, dan pagination bekerja sebelum membuka browser. Kalau muncul 404, rute daftar pinjam mungkin belum dipasang — itu wajar; fokus dulu ke demo PHP yang sudah jalan di terminal.</p>

<h2>Pola Dasar — daftar panjang yang rapi</h2>
<figure style="margin:1.5rem 0;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1.25rem;color:#1a1a1a" aria-label="Enam langkah daftar pinjam rapi">
<ol style="list-style:none;padding:0;margin:0;color:#1a1a1a">
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div><strong style="color:#1a1a1a">Terima parameter URL</strong><br><span style="color:#1a1a1a"><code>status</code>, <code>q</code>, <code>page</code>, <code>per_page</code> dari <strong>pemanggil</strong> API.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div><strong style="color:#1a1a1a">Saring status dulu</strong><br><span style="color:#1a1a1a">Buang slip yang tidak cocok sebelum menghitung halaman.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div><strong style="color:#1a1a1a">Cari dengan q</strong><br><span style="color:#1a1a1a">Cocokkan judul buku atau nama anggota dari kata kunci.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div><strong style="color:#1a1a1a">Potong per halaman</strong><br><span style="color:#1a1a1a">Ambil segenggam baris sesuai <code>page</code> dan <code>per_page</code>.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">5</span>
    <div><strong style="color:#1a1a1a">Kembalikan metadata</strong><br><span style="color:#1a1a1a"><code>page</code>, <code>per_page</code>, <code>total</code>, dan <code>data</code> supaya <strong>pemanggil</strong> tahu posisi daftar.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">6</span>
    <div><strong style="color:#1a1a1a">Tolak halaman rusak</strong><br><span style="color:#1a1a1a">Kalau <code>page</code> nol atau negatif, jawab 422 — bukan halaman kosong diam-diam.</span></div>
  </li>
</ol>
</figure>

<h2>Kode lengkap — demo mandiri daftar panjang</h2>
<p>Simpan sebagai <code>laravel_pagination_filter_pencarian_demo.php</code>, lalu jalankan <code>php laravel_pagination_filter_pencarian_demo.php</code>:</p>

<pre><code class="language-php">&lt;?php
declare(strict_types=1);

$daftar = [
    ["id" =&gt; 100, "judul_buku" =&gt; "Dasar PHP", "nama_anggota" =&gt; "Budi", "status" =&gt; "aktif"],
    ["id" =&gt; 101, "judul_buku" =&gt; "Belajar Laravel", "nama_anggota" =&gt; "Siti", "status" =&gt; "kembali"],
    ["id" =&gt; 102, "judul_buku" =&gt; "Dasar PHP", "nama_anggota" =&gt; "Andi", "status" =&gt; "aktif"],
    ["id" =&gt; 103, "judul_buku" =&gt; "Matematika", "nama_anggota" =&gt; "Budi", "status" =&gt; "kembali"],
    ["id" =&gt; 104, "judul_buku" =&gt; "Biologi", "nama_anggota" =&gt; "Rina", "status" =&gt; "aktif"],
    ["id" =&gt; 105, "judul_buku" =&gt; "Fisika", "nama_anggota" =&gt; "Dewi", "status" =&gt; "kembali"],
];

function daftarPinjam(
    array $rows,
    ?string $status = null,
    ?string $q = null,
    int $page = 1,
    int $perPage = 3
): array {
    if ($page &lt; 1) {
        return [
            "status" =&gt; 422,
            "error" =&gt; "Halaman tidak valid",
        ];
    }

    $hasil = $rows;

    if ($status !== null &amp;&amp; $status !== "") {
        $hasil = array_values(array_filter($hasil, fn ($row) =&gt; $row["status"] === $status));
    }

    if ($q !== null &amp;&amp; $q !== "") {
        $needle = mb_strtolower($q);
        $hasil = array_values(array_filter($hasil, function ($row) use ($needle) {
            return str_contains(mb_strtolower($row["judul_buku"]), $needle)
                || str_contains(mb_strtolower($row["nama_anggota"]), $needle);
        }));
    }

    $total = count($hasil);
    $offset = ($page - 1) * $perPage;
    $data = array_slice($hasil, $offset, $perPage);

    return [
        "status" =&gt; 200,
        "page" =&gt; $page,
        "per_page" =&gt; $perPage,
        "total" =&gt; $total,
        "data" =&gt; $data,
    ];
}

function demo(string $judul, callable $aksi): void
{
    echo "=== {$judul} ===", PHP_EOL;
    echo json_encode($aksi(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL, PHP_EOL;
}

demo("Halaman rusak -&gt; 422", function () use ($daftar) {
    return daftarPinjam($daftar, null, null, 0, 3);
});

demo("Status aktif + q php -&gt; 200", function () use ($daftar) {
    return daftarPinjam($daftar, "aktif", "php", 1, 3);
});

demo("Semua status halaman 2 -&gt; 200", function () use ($daftar) {
    return daftarPinjam($daftar, null, null, 2, 3);
});
</code></pre>
<p><strong>Awam:</strong> tiga skenario di atas menunjukkan pola respons yang wajar: halaman invalid ditolak, filter + pencarian digabung, lalu pagination tanpa filter menampilkan halaman kedua. Fungsi <code>daftarPinjam</code> adalah inti logika; <code>demo(...)</code> hanya membungkus output agar mudah dibaca di terminal.</p>

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
      <td>Halaman 2 kosong tapi total besar</td>
      <td>Memotong dulu baru menyaring</td>
      <td>Ubah urutan: saring -&gt; cari -&gt; potong</td>
    </tr>
    <tr>
      <td>Pencarian tidak menemukan nama anggota</td>
      <td>Hanya mencari di judul buku</td>
      <td>Cari juga di <code>nama_anggota</code> atau relasi anggota</td>
    </tr>
    <tr>
      <td><code>page=0</code> mengembalikan data aneh</td>
      <td>Tidak memvalidasi nomor halaman</td>
      <td>Kembalikan 422 untuk halaman tidak valid</td>
    </tr>
    <tr>
      <td>Filter status diabaikan</td>
      <td>Parameter URL tidak dibaca di <strong>pengatur kode</strong></td>
      <td>Baca <code>request("status")</code> sebelum membangun query</td>
    </tr>
    <tr>
      <td><code>curl</code> aneh atau error di PowerShell</td>
      <td>Alias <code>curl</code> di PowerShell bukan <code>curl.exe</code></td>
      <td>Ketik <code>curl.exe</code> persis seperti contoh, atau uji lewat browser</td>
    </tr>
  </tbody>
</table>

<h2>Latihan singkat</h2>
<ol>
  <li>Tambah satu baris baru ke array demo, lalu cek apakah <code>total</code> ikut berubah.</li>
  <li>Coba <code>daftarPinjam($daftar, "kembali", "budi", 1, 2)</code> dan jelaskan urutan saring-cari-potong yang terjadi.</li>
  <li>Tulis satu kalimat: kenapa <code>q</code> lebih fleksibel daripada hanya filter judul buku?</li>
</ol>

<h2>FAQ singkat</h2>
<p><strong>Kenapa tidak langsung policy atau resource?</strong><br>
Karena daftar harus rapi dulu sebelum membahas izin ubah pinjam atau format JSON yang lebih cantik. Artikel ini fokus pada pagination, filter, dan pencarian.</p>
<p><strong>Beda <code>q</code> dan <code>cari</code>?</strong><br>
Keduanya bisa berarti kata kunci pencarian. Di roadmap Seri 5 kita pakai <code>q</code> supaya konsisten di artikel berikutnya.</p>
<p><strong>Tool apa yang dibuka dulu?</strong><br>
Explorer untuk memastikan folder proyek benar, satu terminal untuk demo PHP, editor untuk <strong>pengatur kode</strong>. Kalau <code>serve</code> hidup, terminal kedua untuk <code>curl.exe</code>.</p>
<p><strong>Potongan sintaks diuji di mana?</strong><br>
Langkah tengah (saring-cari-potong) salin ke <code>daftar-saring.php</code>, lalu jalankan <code>php daftar-saring.php</code>. Demo lengkap diuji dengan <code>php laravel_pagination_filter_pencarian_demo.php</code>. Cuplikan Laravel ditempel ke <code>app\Http\Controllers\PeminjamanController.php</code>; kalau rute sudah ada, uji dengan <code>curl.exe</code> di terminal kedua.</p>
<p><strong>Ke mana setelah ini?</strong><br>
Berikutnya alami: <strong>Authorization Policy</strong> — aturan izin siapa boleh mengubah catatan pinjam.</p>

<h2>Kesimpulan</h2>
<p>Kamu sudah belajar memotong daftar pinjam panjang dengan urutan yang benar: <strong>saring status -&gt; cari dengan <code>q</code> -&gt; potong per halaman</strong>. Mulai dari array PHP dulu, lalu pindah ke <code>paginate()</code> dan <code>whereHas</code> di Laravel. Setelah ini, daftar terasa rapi untuk <strong>pemanggil</strong> API sebelum kita membahas izin.</p>
<blockquote>
  <p><strong>Seri 5 progress:</strong> langkah <strong>#65 (ini)</strong> · <strong>2/7</strong> Laravel Lanjutan · prasyarat: <a href="/artikel/laravel-eloquent-relasi-peminjaman">Relasi Eloquent: Anggota &amp; Peminjaman (#64)</a> LIVE. Berikutnya: <strong>Authorization Policy</strong>.</p>
</blockquote>
HTML;
    }

    private function bodyEn(): string
    {
        $html = <<<'HTML'
<h2>Introduction — long borrowing lists need slices</h2>
<p>This article is <strong>#65 (this article)</strong> in <strong>Seri 5: Laravel Lanjutan</strong>. After member, book, and borrowing relations were finished in <a href="/artikel/laravel-eloquent-relasi-peminjaman">Relasi Eloquent: Anggota &amp; Peminjaman (#64)</a>, the borrowing slip list in your project starts to feel long.</p>
<p>One response with hundreds of rows is slow and hard to read. Today we learn three basic moves: <strong>filter by status</strong> (active or returned), <strong>search</strong> with <code>?q=</code> on book title or member name, then <strong>pagination</strong> so each page only shows a small slice. The correct order is <strong>filter -&gt; search -&gt; slice</strong> — never slice first and filter later.</p>
<p><strong>Beginner:</strong> imagine a pile of borrowing slips on the counter. The clerk does not hand you every slip at once. They pick slips with the right status, search for the book title or member name you mention, then only take a handful for the first page. A borrowing-list API works with the same logic; the answer is read by the <strong>caller</strong>, meaning the app or tool <strong>that calls the API</strong>.</p>

<blockquote>
  <p><strong>Prerequisite:</strong> finish <a href="/artikel/laravel-eloquent-relasi-peminjaman">Relasi Eloquent: Anggota &amp; Peminjaman (#64)</a>, and keep the foundations from <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a> / <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a>. Use <strong>Laravel 13+</strong> and <strong>PHP 8.3+</strong>.</p>
</blockquote>

<h2>Feature spec — what gets finished today?</h2>
<p>These are the three targets:</p>
<ol>
  <li><strong>Pagination</strong> — split the borrowing list by page with <code>page</code> and <code>per_page</code>.</li>
  <li><strong>Status filter</strong> — show only <code>aktif</code> or <code>kembali</code> slips through the <code>status</code> parameter.</li>
  <li><strong>Search</strong> — match book title or member name with <code>?q=</code> (you may hear <code>cari</code> in other tutorials; in our roadmap the name is <code>q</code>).</li>
</ol>
<p><strong>Beginner:</strong> after this article, you are not building who is allowed to change a borrowing record yet. You are making long lists feel neat for the API <strong>caller</strong>: not every row is thrown at once, but chosen and sliced with clear rules.</p>

<h2>Terms — a quick glossary for long lists</h2>
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
      <td>Pagination</td>
      <td>Split a list by page</td>
      <td>Page 1, page 2, and so on</td>
    </tr>
    <tr>
      <td><code>page</code></td>
      <td>The page number requested</td>
      <td>Starts at 1, not 0</td>
    </tr>
    <tr>
      <td><code>per_page</code></td>
      <td>How many rows per page</td>
      <td>For example 3 or 10 rows</td>
    </tr>
    <tr>
      <td>Filter <code>status</code></td>
      <td>Keep slips with the right borrowing state</td>
      <td><code>aktif</code> or <code>kembali</code></td>
    </tr>
    <tr>
      <td><code>q</code></td>
      <td>Search keyword</td>
      <td>Match book title or member name</td>
    </tr>
  </tbody>
</table>
<p>Our learning order is: <strong>filter status -&gt; search with <code>q</code> -&gt; then slice by page</strong>. If you reverse the order, the result can be wrong because irrelevant rows enter the page slice.</p>

<h2>Preparation — tools to open</h2>
<p><strong>Tools used in this article</strong> (built on <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a> and <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a> — there is <strong>no new Composer download</strong> today):</p>
<ul>
  <li><strong>Explorer</strong> — check the <code>perpustakaan-api</code> project folder, then look at <code>app\Http\Controllers</code> for the borrowing-list <strong>code organizer</strong>.</li>
  <li><strong>Terminal</strong> — Laragon: <em>Terminal</em> menu · XAMPP: <em>Shell</em> button. Avoid Start Menu CMD/PowerShell if your PHP PATH is still messy.</li>
  <li><strong>Text editor</strong> — Notepad / VS Code — to open or create the <strong>code organizer</strong>. Example: <code>notepad app\Http\Controllers\PeminjamanController.php</code>.</li>
  <li><strong>Browser</strong> — optional. The core test today is in the terminal; the browser helps if you already run <code>php artisan serve</code> and want to test through a URL.</li>
</ul>
<p><strong>Beginner:</strong> for this article, <strong>one terminal is actually enough</strong> — run <code>php laravel_pagination_filter_pencarian_demo.php</code> in the project folder. If <code>php artisan serve</code> from the previous article is still alive, use a <strong>second terminal</strong> for the PHP demo and <code>curl.exe</code> when testing the Laravel route. To open a second window: Laragon — click the <em>Terminal</em> menu again · XAMPP — click the <em>Shell</em> button again, then <code>cd</code> to the same project folder.</p>
<p>Open Laragon Terminal / XAMPP Shell, then move into the project folder:</p>
<pre><code class="language-bash">cd C:\laragon\www\perpustakaan-api
</code></pre>
<p>In XAMPP it is usually: <code>cd C:\xampp\htdocs\perpustakaan-api</code>. Adjust the path if your folder is different.</p>
<p><strong>Install-from-scratch:</strong> if <code>php</code> or <code>composer</code> is not recognized in the terminal, return to <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a>. If your project folder structure is still confusing, review <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a> first.</p>

<h2>Why start with plain PHP first?</h2>
<p>If you jump straight into Laravel <code>paginate()</code>, beginners often get confused about the work order: filter, search, slice. So we start from a plain PHP array so every step is visible before Eloquent wraps it.</p>

<pre><code class="language-php">&lt;?php
$daftar = [
    ["id" =&gt; 100, "judul_buku" =&gt; "Dasar PHP", "nama_anggota" =&gt; "Budi", "status" =&gt; "aktif"],
    ["id" =&gt; 101, "judul_buku" =&gt; "Belajar Laravel", "nama_anggota" =&gt; "Siti", "status" =&gt; "kembali"],
    ["id" =&gt; 102, "judul_buku" =&gt; "Dasar PHP", "nama_anggota" =&gt; "Andi", "status" =&gt; "aktif"],
    ["id" =&gt; 103, "judul_buku" =&gt; "Matematika", "nama_anggota" =&gt; "Budi", "status" =&gt; "kembali"],
    ["id" =&gt; 104, "judul_buku" =&gt; "Biologi", "nama_anggota" =&gt; "Rina", "status" =&gt; "aktif"],
    ["id" =&gt; 105, "judul_buku" =&gt; "Fisika", "nama_anggota" =&gt; "Dewi", "status" =&gt; "kembali"],
];
</code></pre>
<p><strong>Beginner:</strong> this is a combined list like the relation result from the previous article: each row already has <code>judul_buku</code>, <code>nama_anggota</code>, and <code>status</code>. Pagination only controls <strong>how many rows are shown</strong> from this list.</p>

<h2>List flow — filter, search, slice</h2>
<p>The correct move order is always the same:</p>
<ol>
  <li><strong>Filter status</strong> — if <code>status=aktif</code>, remove slips that are already returned.</li>
  <li><strong>Search with <code>q</code></strong> — match the keyword to book title or member name.</li>
  <li><strong>Slice by page</strong> — take a handful of rows according to <code>page</code> and <code>per_page</code>.</li>
</ol>

<pre><code class="language-php">&lt;?php
// Copy into a file such as daftar-saring.php, then run: php daftar-saring.php
$daftar = [
    ["id" =&gt; 100, "judul_buku" =&gt; "Dasar PHP", "nama_anggota" =&gt; "Budi", "status" =&gt; "aktif"],
    ["id" =&gt; 101, "judul_buku" =&gt; "Belajar Laravel", "nama_anggota" =&gt; "Siti", "status" =&gt; "kembali"],
    ["id" =&gt; 102, "judul_buku" =&gt; "Dasar PHP", "nama_anggota" =&gt; "Andi", "status" =&gt; "aktif"],
    ["id" =&gt; 103, "judul_buku" =&gt; "Matematika", "nama_anggota" =&gt; "Budi", "status" =&gt; "kembali"],
    ["id" =&gt; 104, "judul_buku" =&gt; "Biologi", "nama_anggota" =&gt; "Rina", "status" =&gt; "aktif"],
    ["id" =&gt; 105, "judul_buku" =&gt; "Fisika", "nama_anggota" =&gt; "Dewi", "status" =&gt; "kembali"],
];

$hasil = $daftar;

// 1) filter status
$status = "aktif";
$hasil = array_values(array_filter($hasil, fn ($row) =&gt; $row["status"] === $status));

// 2) search q
$q = "php";
$hasil = array_values(array_filter($hasil, function ($row) use ($q) {
    $needle = mb_strtolower($q);
    return str_contains(mb_strtolower($row["judul_buku"]), $needle)
        || str_contains(mb_strtolower($row["nama_anggota"]), $needle);
}));

// 3) slice page
$page = 1;
$perPage = 2;
$offset = ($page - 1) * $perPage;
$potongan = array_slice($hasil, $offset, $perPage);

echo json_encode($potongan, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL;
</code></pre>
<p><strong>Beginner — how to test this part:</strong> copy the snippet above into <code>daftar-saring.php</code>, then run <code>php daftar-saring.php</code> in the terminal. If you see JSON of <code>aktif</code> slips whose title contains “php”, the filter-search-slice order is healthy. If you slice first and filter later, page 2 can contain slips that should not be there — the order <strong>filter -&gt; search -&gt; slice</strong> keeps every page consistent.</p>

<figure role="img" aria-label="Filter, search, slice flow for borrowing lists" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" style="display:block;max-width:760px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif" viewBox="0 0 760 220">
  <defs>
    <marker id="laravel65pageArrow" orient="auto" markerWidth="8" markerHeight="8" refX="7" refY="4" viewBox="0 0 8 8">
      <path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/>
    </marker>
  </defs>
  <rect width="760" height="220" fill="#F5F5F0"/>
  <text x="24" y="36" fill="#1a1a1a" font-size="16" font-weight="700">Filter -&gt; Search q -&gt; Slice page</text>
  <rect x="28" y="64" width="150" height="72" rx="10" fill="#ffffff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="103" y="96" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">All slips</text>
  <text x="103" y="122" text-anchor="middle" fill="#1a1a1a" font-size="12">long list</text>
  <line x1="178" y1="100" x2="228" y2="100" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel65pageArrow)"/>
  <rect x="230" y="64" width="150" height="72" rx="10" fill="#ffffff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="305" y="96" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">Filter status</text>
  <text x="305" y="122" text-anchor="middle" fill="#1a1a1a" font-size="12">aktif / kembali</text>
  <line x1="380" y1="100" x2="430" y2="100" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel65pageArrow)"/>
  <rect x="432" y="64" width="150" height="72" rx="10" fill="#ffffff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="493" y="96" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">Search q</text>
  <text x="493" y="122" text-anchor="middle" fill="#1a1a1a" font-size="12">title / name</text>
  <line x1="582" y1="100" x2="632" y2="100" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel65pageArrow)"/>
  <rect x="634" y="64" width="120" height="72" rx="10" fill="#1a1a1a" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="694" y="96" text-anchor="middle" fill="#ffffff" font-size="14" font-weight="700">Page</text>
  <text x="694" y="122" text-anchor="middle" fill="#ffffff" font-size="12">page + per_page</text>
  <text x="24" y="188" fill="#1a1a1a" font-size="13">Do not slice first — filter and search before slicing so every page stays consistent.</text>
</svg>
<figcaption>The correct order: filter status, then <code>q</code> search, then pagination slices the result.</figcaption>
</figure>

<h2>Laravel — pagination &amp; filter snippets</h2>
<p>In the Laravel project, the borrowing-list <strong>code organizer</strong> can read URL parameters and build an Eloquent query with the same order.</p>

<pre><code class="language-php">&lt;?php
// Cuplikan Laravel (bukan file mandiri)
// app/Http/Controllers/PeminjamanController.php

$status = request("status");
$q = request("q");
$perPage = (int) request("per_page", 10);

$query = Peminjaman::query()-&gt;with(["buku", "anggota"]);

if ($status) {
    $query-&gt;where("status", $status);
}

if ($q) {
    $query-&gt;where(function ($builder) use ($q) {
        $builder-&gt;whereHas("buku", fn ($b) =&gt; $b-&gt;where("judul", "like", "%{$q}%"))
            -&gt;orWhereHas("anggota", fn ($a) =&gt; $a-&gt;where("nama", "like", "%{$q}%"));
    });
}

$hasil = $query-&gt;paginate($perPage);
</code></pre>
<p><strong>Beginner:</strong> <code>paginate()</code> automatically counts the total, active page, and data slice. <code>whereHas</code> uses the relation from the previous article: search the title through the books table, search the name through the members table. Parameter <code>q</code> is the standard name in our roadmap; the word <code>cari</code> appears in some tutorials, but here we stay consistent with <code>q</code>. This snippet is <strong>not a standalone file</strong> — paste it into <code>PeminjamanController</code> when the borrowing-list route exists. If not yet, master the PHP demo first; the filter-search-slice order stays the same.</p>
<p>If <code>php artisan serve</code> is already running in the first terminal, test in the second terminal. On Windows type <code>curl.exe</code> (not the <code>curl</code> alias alone) so PowerShell does not get confused:</p>
<pre><code class="language-bash">curl.exe "http://127.0.0.1:8000/api/peminjaman?status=aktif&amp;q=php&amp;page=1&amp;per_page=3"
</code></pre>
<p><strong>Beginner:</strong> the JSON response from <code>curl.exe</code> is a fast way to see whether filter, search, and pagination work before opening the browser. If you get 404, the borrowing-list route may not be wired yet — that is normal; focus on the PHP demo that already runs in the terminal.</p>

<h2>Basic Pattern — a neat long list</h2>
<figure style="margin:1.5rem 0;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1.25rem;color:#1a1a1a" aria-label="Six steps for a neat borrowing list">
<ol style="list-style:none;padding:0;margin:0;color:#1a1a1a">
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div><strong style="color:#1a1a1a">Read URL parameters</strong><br><span style="color:#1a1a1a"><code>status</code>, <code>q</code>, <code>page</code>, <code>per_page</code> from the API <strong>caller</strong>.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div><strong style="color:#1a1a1a">Filter status first</strong><br><span style="color:#1a1a1a">Remove mismatched slips before counting pages.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div><strong style="color:#1a1a1a">Search with q</strong><br><span style="color:#1a1a1a">Match book title or member name from the keyword.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div><strong style="color:#1a1a1a">Slice by page</strong><br><span style="color:#1a1a1a">Take a handful of rows according to <code>page</code> and <code>per_page</code>.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">5</span>
    <div><strong style="color:#1a1a1a">Return metadata</strong><br><span style="color:#1a1a1a"><code>page</code>, <code>per_page</code>, <code>total</code>, and <code>data</code> so the <strong>caller</strong> knows list position.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">6</span>
    <div><strong style="color:#1a1a1a">Reject broken pages</strong><br><span style="color:#1a1a1a">If <code>page</code> is zero or negative, answer 422 — not a silent empty page.</span></div>
  </li>
</ol>
</figure>

<h2>Full code — self-run long-list demo</h2>
<p>Save it as <code>laravel_pagination_filter_pencarian_demo.php</code>, then run <code>php laravel_pagination_filter_pencarian_demo.php</code>:</p>

<pre><code class="language-php">&lt;?php
declare(strict_types=1);

$daftar = [
    ["id" =&gt; 100, "judul_buku" =&gt; "Dasar PHP", "nama_anggota" =&gt; "Budi", "status" =&gt; "aktif"],
    ["id" =&gt; 101, "judul_buku" =&gt; "Belajar Laravel", "nama_anggota" =&gt; "Siti", "status" =&gt; "kembali"],
    ["id" =&gt; 102, "judul_buku" =&gt; "Dasar PHP", "nama_anggota" =&gt; "Andi", "status" =&gt; "aktif"],
    ["id" =&gt; 103, "judul_buku" =&gt; "Matematika", "nama_anggota" =&gt; "Budi", "status" =&gt; "kembali"],
    ["id" =&gt; 104, "judul_buku" =&gt; "Biologi", "nama_anggota" =&gt; "Rina", "status" =&gt; "aktif"],
    ["id" =&gt; 105, "judul_buku" =&gt; "Fisika", "nama_anggota" =&gt; "Dewi", "status" =&gt; "kembali"],
];

function daftarPinjam(
    array $rows,
    ?string $status = null,
    ?string $q = null,
    int $page = 1,
    int $perPage = 3
): array {
    if ($page &lt; 1) {
        return [
            "status" =&gt; 422,
            "error" =&gt; "Halaman tidak valid",
        ];
    }

    $hasil = $rows;

    if ($status !== null &amp;&amp; $status !== "") {
        $hasil = array_values(array_filter($hasil, fn ($row) =&gt; $row["status"] === $status));
    }

    if ($q !== null &amp;&amp; $q !== "") {
        $needle = mb_strtolower($q);
        $hasil = array_values(array_filter($hasil, function ($row) use ($needle) {
            return str_contains(mb_strtolower($row["judul_buku"]), $needle)
                || str_contains(mb_strtolower($row["nama_anggota"]), $needle);
        }));
    }

    $total = count($hasil);
    $offset = ($page - 1) * $perPage;
    $data = array_slice($hasil, $offset, $perPage);

    return [
        "status" =&gt; 200,
        "page" =&gt; $page,
        "per_page" =&gt; $perPage,
        "total" =&gt; $total,
        "data" =&gt; $data,
    ];
}

function demo(string $judul, callable $aksi): void
{
    echo "=== {$judul} ===", PHP_EOL;
    echo json_encode($aksi(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL, PHP_EOL;
}

demo("Broken page -&gt; 422", function () use ($daftar) {
    return daftarPinjam($daftar, null, null, 0, 3);
});

demo("Status aktif + q php -&gt; 200", function () use ($daftar) {
    return daftarPinjam($daftar, "aktif", "php", 1, 3);
});

demo("All status page 2 -&gt; 200", function () use ($daftar) {
    return daftarPinjam($daftar, null, null, 2, 3);
});
</code></pre>
<p><strong>Beginner:</strong> the three scenarios above show a sensible response pattern: invalid page rejected, filter + search combined, then pagination without filter showing page two. Function <code>daftarPinjam</code> is the core logic; <code>demo(...)</code> only wraps output so the terminal result is easy to read.</p>

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
      <td>Page 2 is empty but total is large</td>
      <td>Slicing before filtering</td>
      <td>Change the order: filter -&gt; search -&gt; slice</td>
    </tr>
    <tr>
      <td>Search misses member names</td>
      <td>Only searching book titles</td>
      <td>Search <code>nama_anggota</code> or the member relation too</td>
    </tr>
    <tr>
      <td><code>page=0</code> returns odd data</td>
      <td>Page number is not validated</td>
      <td>Return 422 for invalid pages</td>
    </tr>
    <tr>
      <td>Status filter is ignored</td>
      <td>URL parameter not read in the <strong>code organizer</strong></td>
      <td>Read <code>request("status")</code> before building the query</td>
    </tr>
    <tr>
      <td><code>curl</code> acts weird or errors in PowerShell</td>
      <td>PowerShell <code>curl</code> alias is not <code>curl.exe</code></td>
      <td>Type <code>curl.exe</code> exactly as shown, or test through the browser</td>
    </tr>
  </tbody>
</table>

<h2>Practice</h2>
<ol>
  <li>Add one new row to the demo array, then check whether <code>total</code> changes too.</li>
  <li>Try <code>daftarPinjam($daftar, "kembali", "budi", 1, 2)</code> and explain the filter-search-slice order that happens.</li>
  <li>Write one sentence: why is <code>q</code> more flexible than only filtering book titles?</li>
</ol>

<h2>FAQ</h2>
<p><strong>Why not jump straight to policy or resource?</strong><br>
Because the list must be neat first before discussing who may change a borrowing record or prettier JSON. This article focuses on pagination, filter, and search.</p>
<p><strong>Difference between <code>q</code> and <code>cari</code>?</strong><br>
Both can mean a search keyword. In Seri 5 roadmap we use <code>q</code> for consistency in later articles.</p>
<p><strong>Which tools should I open first?</strong><br>
Explorer to confirm the project folder, one terminal for the PHP demo, editor for the <strong>code organizer</strong>. If <code>serve</code> is alive, a second terminal for <code>curl.exe</code>.</p>
<p><strong>Where should I test the snippets?</strong><br>
The middle step (filter-search-slice) is copied into <code>daftar-saring.php</code>, then run with <code>php daftar-saring.php</code>. The full demo is tested with <code>php laravel_pagination_filter_pencarian_demo.php</code>. Laravel snippets are pasted into <code>app\Http\Controllers\PeminjamanController.php</code>; when the route exists, test with <code>curl.exe</code> in a second terminal.</p>
<p><strong>Where next?</strong><br>
The natural next step is <strong>Authorization Policy</strong> — rules for who may change borrowing records.</p>

<h2>Summary</h2>
<p>You learned how to slice a long borrowing list with the correct order: <strong>filter status -&gt; search with <code>q</code> -&gt; slice by page</strong>. Start from plain PHP arrays first, then move into <code>paginate()</code> and <code>whereHas</code> in Laravel. After this, the list feels neat for the API <strong>caller</strong> before we discuss permissions.</p>
<blockquote>
  <p><strong>Seri 5 progress:</strong> step <strong>#65 (this article)</strong> · <strong>2/7</strong> Laravel Lanjutan · prerequisite: <a href="/artikel/laravel-eloquent-relasi-peminjaman">Relasi Eloquent: Anggota &amp; Peminjaman (#64)</a> LIVE. Next: <strong>Authorization Policy</strong>.</p>
</blockquote>
HTML;

        return str_replace(
            [
                'Seri 5: Laravel Lanjutan',
                'Relasi Eloquent: Anggota &amp; Peminjaman (#64)',
                'Instal PHP, Composer &amp; Proyek Laravel (#56)',
                'Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)',
                'Authorization Policy',
                'Laravel Lanjutan',
            ],
            [
                'Seri 5: Advanced Laravel',
                'Eloquent Relations: Members &amp; Borrowing (#64)',
                'Install PHP, Composer &amp; Your First Laravel Project (#56)',
                'Folder Structure, <code>.env</code> &amp; Artisan Laravel (#57)',
                'Authorization Policy',
                'Advanced Laravel',
            ],
            $html
        );
    }
}
