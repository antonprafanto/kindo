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

        $slug = 'capstone-api-perpustakaan-laravel';

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
            'json' => 'json',
            'auth' => 'auth',
        ] as $tagSlug => $tagName) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagName]);
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $webCat->id,
                'title'           => 'Capstone: API Perpustakaan (Baca + Login + Tambah)',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'Capstone API Perpustakaan Laravel: Baca, Login, Tambah',
                'seo_description' => 'Seri 4 #62 Capstone: gabungkan baca katalog, login Sanctum, dan tambah buku ber-kartu — ramah awam.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        $prevPublished = Article::where('slug', 'laravel-auth-api-dasar')->value('published_at');
        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        } elseif ($prevPublished && $article->published_at <= $prevPublished) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['laravel', 'php', 'api', 'http', 'web', 'json', 'auth'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel ke-62 berhasil dipublish: '.$article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — merakit perpustakaan mini</h2>
<p>Artikel ini adalah <strong>#62 (ini)</strong> di <strong>Seri 4: Pemrograman Web Lanjut v2</strong> — <strong>Capstone</strong> jalur Laravel. Langkah <strong>7/8</strong>: menggabungkan yang sudah kamu punya jadi satu alur nyata.</p>
<p>Domain tetap <strong>perpustakaan mini</strong>. Hari ini tamu boleh <strong>membaca</strong> katalog, staf <strong>login</strong> dapat kartu, lalu staf <strong>menambah</strong> buku dengan kartu itu. Belum ubah &amp; hapus — itu langkah terakhir jalur.</p>
<p><strong>Awam:</strong> Capstone = hari “rakit”. Bukan tool baru besar — kamu menyambungkan pintu baca, loket login, dan pintu tambah buku yang sudah dipelajari terpisah.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> sudah selesai <a href="/artikel/laravel-auth-api-dasar">Auth API Dasar: Login &amp; Kartu Anggota (#61)</a> (login + <code>/api/saya</code> ber-kartu), <a href="/artikel/laravel-controller-service-eloquent">Controller, Service &amp; Eloquent Laravel (#60)</a> (daftar buku lewat loket), dan <a href="/artikel/laravel-request-validasi-api">Request &amp; Form Request: Menjaga Input API (#59)</a> (satpam slip). Fondasi <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a> / <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a>. Pakai <strong>Laravel 13+</strong> — butuh <strong>PHP 8.3+</strong>.</p>
</blockquote>

<h2>Spesifikasi fitur — apa yang “selesai” hari ini?</h2>
<p>Bayangkan kamu menjelaskan ke teman dalam tiga kalimat:</p>
<ol>
  <li><strong>Baca katalog</strong> — <code>GET /api/buku</code> tanpa kartu (tamu boleh lihat rak).</li>
  <li><strong>Login staf</strong> — <code>POST /api/login</code> -&gt; dapat string <code>token</code> (kartu anggota digital).</li>
  <li><strong>Tambah buku</strong> — <code>POST /api/buku</code> <em>hanya</em> dengan header Bearer + slip judul/penulis yang valid.</li>
</ol>
<p><strong>Awam:</strong> tanpa kartu -&gt; tambah ditolak. Dengan kartu + slip bagus -&gt; buku baru muncul di daftar.</p>

<figure style="margin:1.5rem 0;padding:1rem;background:#F5F5F0;border-radius:8px;">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 760 240" role="img" aria-label="Alur Capstone: baca katalog, login dapat kartu, tambah buku ber-kartu">
  <title>Alur Capstone: baca, login, tambah</title>
  <defs>
    <marker id="laravel62capstoneArrow" markerWidth="8" markerHeight="8" refX="6" refY="4" orient="auto">
      <path d="M0,0 L8,4 L0,8 Z" fill="#1a1a1a"/>
    </marker>
  </defs>
  <rect x="16" y="36" width="200" height="88" rx="10" fill="#ffffff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="116" y="72" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">1. Baca</text>
  <text x="116" y="98" text-anchor="middle" fill="#1a1a1a" font-size="13">GET /api/buku</text>
  <line x1="226" y1="80" x2="278" y2="80" stroke="#1a1a1a" stroke-width="3" marker-end="url(#laravel62capstoneArrow)"/>
  <rect x="290" y="36" width="200" height="88" rx="10" fill="#ffffff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="390" y="72" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">2. Login</text>
  <text x="390" y="98" text-anchor="middle" fill="#1a1a1a" font-size="13">POST /api/login</text>
  <line x1="500" y1="80" x2="552" y2="80" stroke="#1a1a1a" stroke-width="3" marker-end="url(#laravel62capstoneArrow)"/>
  <rect x="564" y="36" width="180" height="88" rx="10" fill="#ffffff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="654" y="72" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">3. Tambah</text>
  <text x="654" y="98" text-anchor="middle" fill="#1a1a1a" font-size="13">POST /api/buku</text>
  <text x="24" y="180" fill="#1a1a1a" font-size="13">Tanpa kartu di langkah 3 = ditolak. Dengan Bearer + slip valid = buku masuk rak.</text>
  <text x="24" y="208" fill="#1a1a1a" font-size="13">Urutan Capstone #62 (ini) - belum ubah &amp; hapus.</text>
</svg>
<figcaption style="color:#1a1a1a">Tiga pintu Capstone: baca (umum) -&gt; login (kartu) -&gt; tambah (ber-kartu).</figcaption>
</figure>

<h2>Istilah — ringkas Capstone</h2>
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
      <td>Capstone</td>
      <td>Proyek penutup yang merakit potongan sebelumnya</td>
      <td>Bukan instal framework baru</td>
    </tr>
    <tr>
      <td>Bearer</td>
      <td>Cara membawa kartu di header HTTP</td>
      <td><code>Authorization: Bearer …</code></td>
    </tr>
    <tr>
      <td>Publik vs terlindungi</td>
      <td>Pintu boleh tanpa kartu vs wajib kartu</td>
      <td>GET daftar = publik; POST tambah = terlindungi</td>
    </tr>
    <tr>
      <td>Slip valid</td>
      <td>Judul/penulis lolos satpam Form Request</td>
      <td>Dari <a href="/artikel/laravel-request-validasi-api">Request &amp; Form Request: Menjaga Input API (#59)</a></td>
    </tr>
  </tbody>
</table>

<h2>Persiapan — alat yang kamu buka</h2>
<p><strong>Alat yang dipakai di artikel ini</strong> (fondasi <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a> / <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a> — <strong>tidak</strong> ada unduhan Composer baru hari ini jika Sanctum sudah dari <a href="/artikel/laravel-auth-api-dasar">Auth API Dasar: Login &amp; Kartu Anggota (#61)</a>):</p>
<ul>
  <li><strong>Explorer</strong> — pastikan folder <code>perpustakaan-api</code> ada; cek file Controller / Form Request / <code>routes/web.php</code>.</li>
  <li><strong>Terminal</strong> — Laragon: menu <em>Terminal</em> · XAMPP: tombol <em>Shell</em>. Jangan asal CMD/PowerShell dari Start Menu (PATH PHP bisa hilang).</li>
  <li><strong>Terminal kedua</strong> — wajib: terminal pertama = <code>php artisan serve</code>. Terminal kedua = uji <code>curl.exe</code> / PowerShell (login + tambah + baca).</li>
  <li><strong>Editor teks</strong> — Notepad / VS Code — sambungkan route POST tambah + method <code>store</code>. Tip: <code>notepad path\ke\file.php</code> dari terminal kedua.</li>
  <li><strong>Browser</strong> — opsional: cek lampu toko. Uji Capstone (POST login/tambah) <strong>bukan</strong> lewat bilah alamat browser.</li>
</ul>
<p>Buka terminal Laragon/Shell XAMPP, masuk folder proyek:</p>
<pre><code class="language-bash">cd C:\laragon\www\perpustakaan-api
</code></pre>
<p>Di XAMPP biasanya: <code>cd C:\xampp\htdocs\perpustakaan-api</code>. Sesuaikan path jika foldermu beda.</p>
<p>Nyalakan lampu toko di <strong>terminal pertama</strong>:</p>
<pre><code class="language-bash">php artisan serve
</code></pre>
<p>Biarkan jendela itu hidup. Buka <strong>terminal kedua</strong>, <code>cd</code> ke folder proyek yang sama — di sini kamu menguji Capstone.</p>
<p><strong>Awam:</strong> Terminal 1 = lampu toko. Terminal 2 = tangan menguji tiga pintu. Editor = menyambungkan route. Browser = boleh dicek sebentar, bukan alat utama POST.</p>
<p><strong>Install-dari-nol:</strong> jika Sanctum / login belum ada, selesaikan dulu <a href="/artikel/laravel-auth-api-dasar">Auth API Dasar: Login &amp; Kartu Anggota (#61)</a> (termasuk <code>composer require laravel/sanctum</code>). Jika PHP/Composer belum dikenal, kembali ke <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a>.</p>

<h2>Cek cepat — potongan yang harus sudah ada</h2>
<p>Sebelum merakit, pastikan di proyekmu sudah pernah jalan:</p>
<ul>
  <li><code>GET /api/buku</code> lewat <code>BukuController</code> (<a href="/artikel/laravel-controller-service-eloquent">Controller, Service &amp; Eloquent Laravel (#60)</a>)</li>
  <li><code>POST /api/login</code> + user uji + Sanctum (<a href="/artikel/laravel-auth-api-dasar">Auth API Dasar: Login &amp; Kartu Anggota (#61)</a>)</li>
  <li>Form Request / aturan validasi untuk slip buku (<a href="/artikel/laravel-request-validasi-api">Request &amp; Form Request: Menjaga Input API (#59)</a>)</li>
</ul>
<p>Kalau salah satu hilang, jangan “loncat Capstone” — perbaiki fondasi dulu. Capstone hanya menyambung.</p>

<h2>Loket tambah — method store</h2>
<p>Buka <code>notepad app\Http\Controllers\BukuController.php</code>. Pastikan ada method <code>store</code> (nama boleh sama) yang menerima slip valid lalu menyimpan lewat Service/Eloquent yang sudah kamu punya di <a href="/artikel/laravel-controller-service-eloquent">Controller, Service &amp; Eloquent Laravel (#60)</a>. Contoh kerangka:</p>
<pre><code class="language-php">// Cuplikan BukuController — loket tambah buku
use App\Http\Requests\StoreBukuRequest; // sesuaikan nama Form Request-mu
use Illuminate\Http\JsonResponse;

public function store(StoreBukuRequest $request): JsonResponse
{
    $data = $request-&gt;validated();

    // Panggil Service / Model yang sudah kamu buat di #60
    $buku = $this-&gt;bukuService-&gt;tambah($data);
    // atau: $buku = Buku::query()-&gt;create($data);

    return response()-&gt;json([
        'message' =&gt; 'Buku ditambahkan',
        'data' =&gt; $buku,
    ], 201);
}
</code></pre>
<p><strong>Awam:</strong> sesuaikan nama class Form Request / Service dengan file di Explorer-mu. Intinya: satpam slip dulu -&gt; baru tulis ke rak -&gt; jawab JSON 201.</p>
<p>Jika Form Request belum ada, buat mengikuti <a href="/artikel/laravel-request-validasi-api">Request &amp; Form Request: Menjaga Input API (#59)</a> (misalnya <code>php artisan make:request StoreBukuRequest</code>), lalu isi aturan <code>judul</code> / <code>penulis</code> required string.</p>

<h2>Sambungkan pintu di routes</h2>
<p>Buka <code>notepad routes\web.php</code>. Pastikan tiga pintu Capstone terdaftar (login dari <a href="/artikel/laravel-auth-api-dasar">Auth API Dasar: Login &amp; Kartu Anggota (#61)</a> boleh sudah ada):</p>
<pre><code class="language-php">// Cuplikan routes/web.php — Capstone baca + login + tambah
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BukuController;

Route::get('/api/buku', [BukuController::class, 'index']);
Route::post('/api/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')-&gt;group(function () {
    Route::get('/api/saya', [AuthController::class, 'saya']);
    Route::post('/api/buku', [BukuController::class, 'store']);
});
</code></pre>
<p>Simpan. Pastikan <code>serve</code> masih hidup di terminal pertama.</p>
<p><strong>Awam:</strong> GET daftar di luar satpam kartu. POST tambah di dalam <code>auth:sanctum</code> — tanpa Bearer sah = ditolak.</p>

<figure style="margin:1.5rem 0;padding:1rem;background:#F5F5F0;border-radius:8px;">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 760 200" role="img" aria-label="Pintu publik baca versus pintu terlindungi tambah">
  <rect x="40" y="40" width="280" height="100" rx="10" fill="#ffffff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="180" y="85" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Publik</text>
  <text x="180" y="112" text-anchor="middle" fill="#1a1a1a" font-size="13">GET /api/buku</text>
  <rect x="440" y="40" width="280" height="100" rx="10" fill="#ffffff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="580" y="85" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Ber-kartu</text>
  <text x="580" y="112" text-anchor="middle" fill="#1a1a1a" font-size="13">POST /api/buku</text>
  <text x="24" y="175" fill="#1a1a1a" font-size="13">Kartu didapat dari POST /api/login (Auth API Dasar).</text>
</svg>
<figcaption>Baca boleh tanpa kartu; tambah wajib kartu Sanctum.</figcaption>
</figure>

<h2>Uji Capstone di terminal kedua</h2>
<p>Jangan andalkan bilah alamat browser untuk POST. Ikuti pola uji di <a href="/artikel/laravel-request-validasi-api">Request &amp; Form Request: Menjaga Input API (#59)</a> dan <a href="/artikel/laravel-auth-api-dasar">Auth API Dasar: Login &amp; Kartu Anggota (#61)</a>.</p>
<p>Pastikan user uji masih ada (satu tembakan, bukan chat panjang):</p>
<pre><code class="language-bash">php artisan tinker --execute="\App\Models\User::updateOrCreate(['email'=>'staf@perpustakaan.test'], ['name'=>'Staf Mini','password'=>bcrypt('password')]);"
</code></pre>

<p><strong>Opsi A — <code>curl.exe</code></strong> (Windows 10/11 biasanya sudah punya; ketik <code>curl.exe</code> agar tidak tertukar di PowerShell):</p>
<p><strong>1) Login — ambil token</strong></p>
<pre><code class="language-bash">curl.exe -s -X POST http://127.0.0.1:8000/api/login ^
  -H "Content-Type: application/json" ^
  -H "Accept: application/json" ^
  -d "{\"email\":\"staf@perpustakaan.test\",\"password\":\"password\"}"
</code></pre>
<p><strong>Awam — salin token:</strong> di jawaban JSON cari kunci <code>"token"</code>. Salin <em>hanya</em> string di antara tanda kutip. Jangan salin kata <code>Bearer</code> dari JSON — kata Bearer ditulis di header. Ganti <code>GANTI_DENGAN_TOKEN</code> di bawah.</p>
<p><strong>2) Tambah tanpa kartu — harus ditolak</strong></p>
<pre><code class="language-bash">curl.exe -s -X POST http://127.0.0.1:8000/api/buku ^
  -H "Content-Type: application/json" ^
  -H "Accept: application/json" ^
  -d "{\"judul\":\"Laskar Pelangi\",\"penulis\":\"Andrea Hirata\"}"
</code></pre>
<p>Kamu harus melihat “Belum diizinkan” / unauthenticated — bukan “Buku ditambahkan”.</p>
<p><strong>3) Tambah dengan kartu — harus 201</strong></p>
<pre><code class="language-bash">curl.exe -s -X POST http://127.0.0.1:8000/api/buku ^
  -H "Content-Type: application/json" ^
  -H "Accept: application/json" ^
  -H "Authorization: Bearer GANTI_DENGAN_TOKEN" ^
  -d "{\"judul\":\"Laskar Pelangi\",\"penulis\":\"Andrea Hirata\"}"
</code></pre>
<p><strong>4) Baca katalog — buku baru harus terlihat</strong></p>
<pre><code class="language-bash">curl.exe -s http://127.0.0.1:8000/api/buku ^
  -H "Accept: application/json"
</code></pre>

<p><strong>Opsi B — PowerShell</strong> (kalau lebih nyaman menempel header Bearer):</p>
<pre><code class="language-powershell">$login = Invoke-RestMethod -Method Post -Uri http://127.0.0.1:8000/api/login `
  -ContentType "application/json" `
  -Body '{"email":"staf@perpustakaan.test","password":"password"}'
$token = $login.token
Invoke-RestMethod -Method Post -Uri http://127.0.0.1:8000/api/buku `
  -ContentType "application/json" `
  -Headers @{ Authorization = "Bearer $token"; Accept = "application/json" } `
  -Body '{"judul":"Laskar Pelangi","penulis":"Andrea Hirata"}'
Invoke-RestMethod -Uri http://127.0.0.1:8000/api/buku -Headers @{ Accept = "application/json" }
</code></pre>

<p><strong>Opsi C — Postman / Insomnia</strong> (alat berjendela): method POST, URL <code>http://127.0.0.1:8000/api/buku</code>, header Authorization type Bearer Token, body JSON judul+penulis. Sama hasilnya — pilih yang paling nyaman.</p>

<h2>Pola Dasar</h2>
<p><strong style="color:#1a1a1a">Urutan Capstone yang aman:</strong> <span style="color:#1a1a1a">(1) nyalakan <code>serve</code> di terminal 1 · (2) pastikan login &amp; GET daftar pernah OK · (3) pasang <code>store</code> + Form Request · (4) bungkus POST <code>/api/buku</code> dengan <code>auth:sanctum</code> · (5) uji: tanpa kartu gagal -&gt; dengan kartu 201 -&gt; GET melihat buku baru.</span></p>
<p><strong style="color:#1a1a1a">Awam:</strong> <span style="color:#1a1a1a">jangan menambah pintu ubah/hapus dulu. Capstone hari ini = baca + login + tambah saja.</span></p>

<h2>File contoh — simulasi alur Capstone</h2>
<p>Simpan sebagai <code>laravel_capstone_api_perpustakaan_demo.php</code> lalu jalankan: <code>php laravel_capstone_api_perpustakaan_demo.php</code>. Ini <em>bukan</em> Laravel sungguhan — hanya meniru tiga pintu agar alur terasa.</p>
<pre><code class="language-php">&lt;?php
declare(strict_types=1);

/**
 * laravel_capstone_api_perpustakaan_demo.php
 * Simulasi Capstone: baca (publik) + login + tambah (ber-kartu).
 */

function demo(): void
{
    $katalog = [
        ['id' =&gt; 1, 'judul' =&gt; 'Bumi', 'penulis' =&gt; 'Tere Liye'],
    ];
    $kartuSah = 'kartu-staf-contoh';

    echo "1) GET /api/buku (publik)\n";
    echo json_encode(['message' =&gt; 'Daftar buku', 'data' =&gt; $katalog], JSON_UNESCAPED_UNICODE), PHP_EOL;

    echo "2) POST /api/login -&gt; token\n";
    echo json_encode(['message' =&gt; 'Login berhasil', 'token' =&gt; $kartuSah], JSON_UNESCAPED_UNICODE), PHP_EOL;

    echo "3) POST /api/buku tanpa kartu -&gt; 401\n";
    echo json_encode(['message' =&gt; 'Belum diizinkan'], JSON_UNESCAPED_UNICODE), PHP_EOL;

    echo "4) POST /api/buku dengan Bearer -&gt; 201\n";
    $baru = ['id' =&gt; 2, 'judul' =&gt; 'Laskar Pelangi', 'penulis' =&gt; 'Andrea Hirata'];
    $katalog[] = $baru;
    echo json_encode(['message' =&gt; 'Buku ditambahkan', 'data' =&gt; $baru], JSON_UNESCAPED_UNICODE), PHP_EOL;

    echo "5) GET /api/buku lagi\n";
    echo json_encode(['message' =&gt; 'Daftar buku', 'data' =&gt; $katalog], JSON_UNESCAPED_UNICODE), PHP_EOL;
    echo PHP_EOL, "Langkah sungguhan: serve -&gt; login curl -&gt; Bearer POST /api/buku -&gt; GET daftar.", PHP_EOL;
}

demo();
</code></pre>
<p><strong>Awam:</strong> <code>declare(strict_types=1);</code> = PHP lebih ketat soal tipe data di file contoh. Di Laravel sungguhan, kartu = token Sanctum, bukan string hard-code.</p>

<h2>Kesalahan umum</h2>
<ul>
  <li><strong>POST tambah dari bilah alamat browser</strong> — browser biasa GET. Pakai terminal kedua + <code>curl.exe</code> / PowerShell / Postman.</li>
  <li><strong>Lupa header Accept</strong> — tambahkan <code>Accept: application/json</code> supaya pesan error jelas (bukan halaman HTML).</li>
  <li><strong>Token salah salin</strong> — salin hanya nilai <code>"token"</code>; jangan dobel kata Bearer; jangan ada spasi di ujung.</li>
  <li><strong>POST /api/buku di luar middleware</strong> — tanpa <code>auth:sanctum</code>, siapa saja bisa menambah. Capstone wajib satpam kartu.</li>
  <li><strong>Serve mati di terminal 1</strong> — semua uji di terminal 2 akan gagal koneksi. Nyalakan lagi <code>php artisan serve</code>.</li>
  <li><strong>Terminal dari Start Menu</strong> — PATH PHP/Composer hilang. Pakai Terminal Laragon / Shell XAMPP.</li>
</ul>

<h2>Latihan</h2>
<ol>
  <li>Uji POST tambah tanpa Bearer — pastikan ditolak.</li>
  <li>Login, salin token, tambah satu buku, lalu GET daftar — pastikan judul baru ada.</li>
  <li>Coba slip kosong (<code>{}</code>) dengan Bearer — pastikan satpam Form Request menolak (bukan 201).</li>
  <li>(Opsional) Tambah buku kedua dengan judul beda; pastikan GET menampilkan keduanya.</li>
</ol>

<h2>FAQ</h2>
<p><strong>Apa bedanya Capstone dengan Auth API Dasar?</strong><br>
<a href="/artikel/laravel-auth-api-dasar">Auth API Dasar: Login &amp; Kartu Anggota (#61)</a> fokus kartu + pintu <code>/api/saya</code>. Capstone memakai kartu itu untuk <em>menambah buku</em>, plus memastikan baca katalog tetap publik.</p>
<p><strong>Token yang mana yang disalin?</strong><br>
Hanya string di kunci <code>"token"</code> pada jawaban login. Panjang, tanpa spasi. Lalu tulis: <code>Authorization: Bearer </code> + string itu.</p>
<p><strong>Terminal mana yang dipakai?</strong><br>
Terminal 1: <code>serve</code>. Terminal 2: login, tambah, baca. Editor: route + <code>store</code>. Browser: opsional cek lampu.</p>
<p><strong>Kenapa masih Shell XAMPP / Laragon?</strong><br>
Agar PATH PHP sama seperti saat proyek dibuat. CMD dari Start Menu sering “php tidak dikenal”.</p>
<p><strong>Apakah perlu Composer baru?</strong><br>
Tidak, jika Sanctum sudah dari <a href="/artikel/laravel-auth-api-dasar">Auth API Dasar: Login &amp; Kartu Anggota (#61)</a>. Kalau belum — install-dari-nol di sana dulu.</p>
<p><strong>Kapan ubah &amp; hapus?</strong><br>
Langkah berikutnya di jalur Laravel (CRUD ubah &amp; hapus) — setelah Capstone ini nyaman.</p>

<h2>Ringkasan</h2>
<p><strong>#62 (ini)</strong> merakit tiga pintu: baca katalog (publik), login (kartu), tambah buku (ber-kartu + slip valid). Fondasi datang dari
<a href="/artikel/laravel-auth-api-dasar">Auth API Dasar: Login &amp; Kartu Anggota (#61)</a>,
<a href="/artikel/laravel-controller-service-eloquent">Controller, Service &amp; Eloquent Laravel (#60)</a>, dan
<a href="/artikel/laravel-request-validasi-api">Request &amp; Form Request: Menjaga Input API (#59)</a>.</p>
<p><strong>Ke mana setelah ini?</strong><br>
Berikutnya: CRUD API Buku — ubah &amp; hapus, supaya staf bisa memperbaiki atau menghapus entri dengan kartu yang sama. (Artikel berikutnya di jalur Laravel.)</p>

<blockquote>
  <p><strong>Seri 4 progress:</strong> langkah <strong>#62 (ini)</strong> · <strong>7/8</strong> jalur Laravel · prasyarat: <a href="/artikel/laravel-auth-api-dasar">Auth API Dasar: Login &amp; Kartu Anggota (#61)</a> LIVE. Berikutnya: CRUD API Buku — Ubah &amp; Hapus.</p>
</blockquote>
HTML;
    }
}
