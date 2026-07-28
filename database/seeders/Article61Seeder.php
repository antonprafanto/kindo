<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article61Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $webCat = Category::where('slug', 'web-development')->first()
            ?? Category::where('slug', 'programming')->first();

        if (! $admin || ! $webCat) {
            throw new \RuntimeException('User atau kategori web-development/programming tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'laravel-auth-api-dasar';

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
                'title'           => 'Auth API Dasar: Login & Kartu Anggota',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'Auth API Dasar Laravel: Login & Token untuk Pemula',
                'seo_description' => 'Seri 4 #61: pasang Sanctum, login API, dan lindungi pintu dengan kartu anggota (token) — ramah awam.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        $prevPublished = Article::where('slug', 'laravel-controller-service-eloquent')->value('published_at');
        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        } elseif ($prevPublished && $article->published_at <= $prevPublished) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['laravel', 'php', 'api', 'http', 'web', 'json', 'auth'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel ke-61 berhasil dipublish: '.$article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — kartu anggota di perpustakaan mini</h2>
<p>Artikel ini adalah <strong>#61 (ini)</strong> di <strong>Seri 4: Pemrograman Web Lanjut v2</strong>. Di <a href="/artikel/laravel-controller-service-eloquent">Controller, Service &amp; Eloquent Laravel (#60)</a> kamu sudah punya loket + dapur + tabel. Sekarang langkah <strong>6/8</strong>: belajar <strong>Auth API dasar</strong> — login yang mengeluarkan <strong>kartu anggota</strong> (token), lalu melindungi pintu yang hanya boleh dibuka pemegang kartu.</p>
<p><strong>Awam:</strong> tanpa kartu, siapa saja bisa “masuk ruang staf”. Dengan kartu, tamu harus login dulu. Hari ini kita pasang mesin kartu (Sanctum), buat pintu login, dan uji satu pintu terlindungi.</p>
<p>Domain tetap <strong>perpustakaan mini</strong>. Belum Capstone penuh (baca + login + tambah) — itu langkah berikutnya.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> sudah selesai <a href="/artikel/laravel-controller-service-eloquent">Controller, Service &amp; Eloquent Laravel (#60)</a> — proyek <code>perpustakaan-api</code> jalan, <code>GET /api/buku</code> pernah OK. Fondasi <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a> / <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a>. Pakai <strong>Laravel 13+</strong> — butuh <strong>PHP 8.3+</strong>.</p>
</blockquote>

<h2>Spesifikasi fitur — apa yang kita kuasai?</h2>
<p>Daftar singkat yang bisa kamu centang di akhir artikel:</p>
<ol>
  <li>Memasang <strong>Laravel Sanctum</strong> dari nol (Composer + publish + migrate).</li>
  <li>Membuat pintu <strong>POST /api/login</strong> yang mengembalikan token (kartu anggota).</li>
  <li>Melindungi pintu contoh <strong>GET /api/saya</strong> supaya tanpa kartu = <code>401</code>.</li>
  <li>Menguji dengan <strong>terminal kedua</strong> + <code>curl.exe</code> / PowerShell (bukan bilah alamat browser saja).</li>
</ol>
<p><strong>Awam:</strong> urutan nyaman: <strong>buka alat -&gt; pasang Sanctum -&gt; siapkan user uji -&gt; buat login -&gt; lindungi pintu -&gt; uji 401 lalu sukses</strong>.</p>

<h2>Istilah — ringkas untuk kartu anggota</h2>
<table>
  <thead>
    <tr>
      <th>Istilah</th>
      <th>Arti awam</th>
      <th>Contoh di artikel ini</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Auth / login</td>
      <td>Membuktikan “saya staf yang sah”</td>
      <td><code>POST /api/login</code></td>
    </tr>
    <tr>
      <td>Token / kartu anggota</td>
      <td>String rahasia yang dibawa di setiap permintaan terlindungi</td>
      <td>Header <code>Authorization: Bearer …</code></td>
    </tr>
    <tr>
      <td>Sanctum</td>
      <td>Paket Laravel untuk kartu anggota API</td>
      <td><code>composer require laravel/sanctum</code></td>
    </tr>
    <tr>
      <td><code>401</code></td>
      <td>Belum diizinkan — kartu kosong / salah</td>
      <td>GET <code>/api/saya</code> tanpa Bearer</td>
    </tr>
  </tbody>
</table>
<p>Urutan belajar: kenali kartu -&gt; pasang mesin kartu -&gt; buat login -&gt; lindungi pintu -&gt; uji.</p>

<h2>Kenapa auth sekarang?</h2>
<p>Validasi di <a href="/artikel/laravel-request-validasi-api">Request &amp; Form Request: Menjaga Input API (#59)</a> menjaga slip kotor. <a href="/artikel/laravel-controller-service-eloquent">Controller, Service &amp; Eloquent Laravel (#60)</a> merapikan loket. Tanpa auth, pintu “ruang staf” masih terbuka untuk siapa saja yang tahu URL.</p>
<p><strong>Awam:</strong> satpam cek slip; petugas kartu cek <em>siapa</em> yang boleh masuk. Hari ini fokus siapa — bukan Capstone penuh.</p>
<p>Artikel ini tetap <strong>install-dari-nol</strong> untuk mesin kartu: Sanctum dipasang dari nol di bawah — tidak mengandalkan “sudah ada di laptop orang lain”.</p>

<h2>Alur — login sampai pintu terlindungi</h2>
<figure role="img" aria-label="Alur Auth API: login, token, pintu terlindungi" style="background:#F5F5F0;border:2px solid #1a1a1a;border-radius:12px;padding:1rem;margin:1.25rem 0">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 760 240" width="100%" height="auto" role="img" aria-labelledby="laravel61title">
  <title id="laravel61title">Alur: Login -&gt; Token -&gt; Pintu terlindungi</title>
  <defs>
    <marker id="laravel61authArrow" markerWidth="8" markerHeight="8" refX="6" refY="4" orient="auto">
      <path d="M0,0 L8,4 L0,8 Z" fill="#1a1a1a"/>
    </marker>
  </defs>
  <text x="24" y="28" fill="#1a1a1a" font-size="15" font-weight="700">Alur: Login -&gt; Kartu (token) -&gt; Pintu /api/saya</text>
  <rect x="24" y="48" width="160" height="72" rx="10" fill="#2979FF"/>
  <text x="104" y="80" text-anchor="middle" fill="#fff" font-size="15" font-weight="700">Terminal 2</text>
  <text x="104" y="100" text-anchor="middle" fill="#fff" font-size="12">POST /api/login</text>
  <line x1="192" y1="84" x2="232" y2="84" stroke="#1a1a1a" stroke-width="3" marker-end="url(#laravel61authArrow)"/>
  <rect x="240" y="48" width="160" height="72" rx="10" fill="#00C853"/>
  <text x="320" y="80" text-anchor="middle" fill="#fff" font-size="15" font-weight="700">AuthController</text>
  <text x="320" y="100" text-anchor="middle" fill="#fff" font-size="12">keluarkan token</text>
  <line x1="408" y1="84" x2="448" y2="84" stroke="#1a1a1a" stroke-width="3" marker-end="url(#laravel61authArrow)"/>
  <rect x="456" y="48" width="140" height="72" rx="10" fill="#FF7A2F"/>
  <text x="526" y="80" text-anchor="middle" fill="#fff" font-size="15" font-weight="700">Bearer</text>
  <text x="526" y="100" text-anchor="middle" fill="#fff" font-size="12">kartu di header</text>
  <line x1="604" y1="84" x2="644" y2="84" stroke="#1a1a1a" stroke-width="3" marker-end="url(#laravel61authArrow)"/>
  <rect x="652" y="48" width="84" height="72" rx="10" fill="#1a1a1a"/>
  <text x="694" y="80" text-anchor="middle" fill="#fff" font-size="14" font-weight="700">/api/saya</text>
  <text x="694" y="100" text-anchor="middle" fill="#fff" font-size="11">200 / 401</text>
  <text x="24" y="160" fill="#1a1a1a" font-size="13">Terminal 1 tetap menjalankan php artisan serve (lampu toko).</text>
  <text x="24" y="182" fill="#1a1a1a" font-size="13">Tanpa Bearer: 401 Belum diizinkan. Dengan Bearer sah: JSON profil singkat.</text>
  <text x="24" y="214" fill="#1a1a1a" font-size="13">Urutan #61 (ini) — belum Capstone tambah buku ber-auth.</text>
</svg>
<figcaption style="color:#1a1a1a;margin-top:.5rem"><strong>#61 (ini)</strong>: login -&gt; token -&gt; pintu terlindungi.</figcaption>
</figure>

<h2>Persiapan — alat yang kamu buka</h2>
<p><strong>Alat yang dipakai di artikel ini</strong> (fondasi <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a> / <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a> — unduhan baru hanya lewat Composer di bawah):</p>
<ul>
  <li><strong>Explorer</strong> — memastikan folder proyek <code>perpustakaan-api</code> dan melihat file Controller / Model yang dibuat Artisan.</li>
  <li><strong>Terminal</strong> — Laragon: menu <em>Terminal</em> · XAMPP: tombol <em>Shell</em>. Jangan asal CMD/PowerShell dari Start Menu (PATH PHP/Composer bisa hilang).</li>
  <li><strong>Terminal kedua</strong> — wajib: terminal pertama = <code>php artisan serve</code>. Terminal kedua = Composer, Artisan lain, dan <code>curl.exe</code> / PowerShell untuk uji POST/GET ber-token.</li>
  <li><strong>Editor teks</strong> — Notepad / VS Code — edit Model User, AuthController, <code>routes/web.php</code>. Tip: <code>notepad app\Http\Controllers\AuthController.php</code> dari terminal kedua (ganti nama file sesuai yang mau dibuka).</li>
  <li><strong>Browser</strong> — opsional hari ini. Pintu login &amp; uji token pakai terminal (seperti POST di <a href="/artikel/laravel-request-validasi-api">Request &amp; Form Request: Menjaga Input API (#59)</a>). Browser berguna hanya untuk cek lampu toko masih hidup.</li>
</ul>
<p><strong>Cara buka terminal kedua</strong> (baru pertama kali buka dua terminal sekaligus? ini caranya, jangan tutup yang pertama): <strong>Laragon</strong> — klik menu <em>Terminal</em> sekali lagi di jendela utama Laragon, sebuah jendela terminal baru akan muncul terpisah dari yang pertama. <strong>XAMPP</strong> — di XAMPP Control Panel, klik tombol <em>Shell</em> sekali lagi, jendela Shell kedua akan terbuka. Kedua jendela boleh hidup bersamaan — jendela pertama tetap menjalankan <code>php artisan serve</code>, jendela kedua kamu pakai untuk mengetik perintah lain.</p>
<p>Buka terminal Laragon/Shell XAMPP (terminal pertama), masuk folder proyek:</p>
<pre><code class="language-bash">cd C:\laragon\www\perpustakaan-api
</code></pre>
<p>Di XAMPP biasanya: <code>cd C:\xampp\htdocs\perpustakaan-api</code>. Sesuaikan path jika foldermu beda.</p>
<p>Nyalakan lampu toko di <strong>terminal pertama</strong>:</p>
<pre><code class="language-bash">php artisan serve
</code></pre>
<p>Biarkan jendela itu hidup. Buka <strong>terminal kedua</strong> (caranya sudah dijelaskan di atas), <code>cd</code> ke folder proyek yang sama — di sini kamu mengetik Composer, Artisan, dan uji <code>curl.exe</code>.</p>
<p><strong>Awam:</strong> Terminal 1 = lampu toko. Terminal 2 = tangan memasang Sanctum + menguji kartu. Editor = menulis login. Browser = boleh dicek sebentar, bukan alat utama uji token.</p>

<h2>Pasang Sanctum dari nol</h2>
<p>Di <strong>terminal kedua</strong> (folder <code>perpustakaan-api</code>, lampu toko tetap hidup di terminal pertama):</p>
<pre><code class="language-bash">composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
</code></pre>
<p><strong>Awam:</strong> baris 1 mengunduh mesin kartu. Baris 2 menyalin pengaturan Sanctum ke proyekmu. Baris 3 membuat tabel token di database (SQLite dari fondasi denah <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a> sudah cukup).</p>
<p><strong>Install-dari-nol:</strong> jika <code>composer</code> tidak dikenal, kembali ke <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a> — pakai Shell XAMPP/Laragon yang sama seperti saat <code>create-project</code>.</p>
<p>Buka model user: <code>notepad app\Models\User.php</code>. Pastikan class memakai trait kartu Sanctum (tempel di dalam class, dekat <code>use HasFactory</code> jika ada):</p>
<pre><code class="language-php">// Cuplikan app/Models/User.php — di dalam class User
use Laravel\Sanctum\HasApiTokens;

// ... di tubuh class:
use HasApiTokens, HasFactory, Notifiable;
</code></pre>
<p><strong>Awam:</strong> sesuaikan dengan kerangka file yang sudah ada — jangan hapus <code>namespace</code> / nama class. Yang penting: <code>HasApiTokens</code> ikut dipakai supaya user bisa mengeluarkan token.</p>

<h2>Siapkan satu user uji</h2>
<p>Masih di <strong>terminal kedua</strong>. Buat / perbarui satu staf uji dengan perintah satu tembakan (tidak perlu masuk mode percakapan):</p>
<pre><code class="language-bash">php artisan tinker --execute="\App\Models\User::updateOrCreate(['email'=>'staf@perpustakaan.test'], ['name'=>'Staf Mini','password'=>bcrypt('password')]);"
</code></pre>
<p><strong>Awam:</strong> ini <em>bukan</em> chat panjang. <code>tinker --execute="…"</code> = minta Artisan menjalankan satu baris PHP lalu selesai. Email uji: <code>staf@perpustakaan.test</code> · sandi: <code>password</code> (hanya untuk belajar di laptopmu).</p>

<h2>Controller login — loket kartu</h2>
<p>Di terminal kedua:</p>
<pre><code class="language-bash">php artisan make:controller AuthController
</code></pre>
<p>Buka file: <code>notepad app\Http\Controllers\AuthController.php</code>. Jangan hapus <code>namespace</code> dan kerangka class. Tambahkan <code>use</code> di atas class, lalu method <code>login</code> di dalam class:</p>
<pre><code class="language-php">// Cuplikan AuthController — loket login
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

public function login(Request $request): JsonResponse
{
    $data = $request-&gt;validate([
        'email' =&gt; ['required', 'email'],
        'password' =&gt; ['required', 'string'],
    ]);

    $user = User::query()-&gt;where('email', $data['email'])-&gt;first();

    if (! $user || ! Hash::check($data['password'], $user-&gt;password)) {
        return response()-&gt;json(['message' =&gt; 'Belum diizinkan — email/sandi salah'], 401);
    }

    $token = $user-&gt;createToken('kartu-staf')-&gt;plainTextToken;

    return response()-&gt;json([
        'message' =&gt; 'Login berhasil — simpan kartu (token)',
        'token' =&gt; $token,
        'token_type' =&gt; 'Bearer',
    ]);
}
</code></pre>
<p><strong>Awam:</strong> kalau email/sandi salah = <code>401</code> + pesan jelas. Kalau benar = JSON berisi <code>token</code>. Simpan token itu; nanti ditempel di header.</p>

<h2>Pintu saya — contoh terlindungi</h2>
<p>Tambahkan method di Controller yang sama (atau Controller kecil lain). Cuplikan untuk <code>AuthController</code>:</p>
<pre><code class="language-php">// Cuplikan AuthController — siapa yang sedang masuk
public function saya(Request $request): JsonResponse
{
    $user = $request-&gt;user();

    return response()-&gt;json([
        'message' =&gt; 'Kartu diterima',
        'data' =&gt; [
            'id' =&gt; $user-&gt;id,
            'name' =&gt; $user-&gt;name,
            'email' =&gt; $user-&gt;email,
        ],
    ]);
}
</code></pre>
<p>Buka <code>notepad routes\web.php</code>. Tambahkan (di bawah route yang sudah ada):</p>
<pre><code class="language-php">// Cuplikan routes/web.php — login + pintu ber-kartu
use App\Http\Controllers\AuthController;

Route::post('/api/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')-&gt;get('/api/saya', [AuthController::class, 'saya']);
</code></pre>
<p>Simpan. Pastikan <code>serve</code> masih hidup di terminal pertama.</p>
<p><strong>Awam:</strong> <code>auth:sanctum</code> = satpam kartu di pintu <code>/api/saya</code>. Tanpa header Bearer yang sah, tamu ditolak.</p>

<h2>Cek izin api/* — supaya login tidak ditolak 419</h2>
<p>Laravel punya satpam lobi bawaan bernama <strong>CSRF</strong> yang melindungi formulir web: ia menolak semua kiriman <code>POST</code> yang tidak datang dari halaman browser milik situs itu sendiri. Karena kita menguji <code>POST /api/login</code> dari <strong>terminal</strong> (bukan browser), tanpa izin khusus jawabannya adalah <code>{"message":"CSRF token mismatch."}</code> dengan kode <strong>419</strong> — bukan token.</p>
<p><strong>Kalau di dalam <code>withMiddleware</code> sudah ada <code>'api/*'</code></strong> (biasanya dari <a href="/artikel/laravel-request-validasi-api">Request &amp; Form Request: Menjaga Input API (#59)</a>) — <strong>lewati langkah ini</strong>. Penjelasan panjangnya ada di artikel itu.</p>
<p><strong>Belum ada?</strong> Pastikan terminal kedua sudah di folder proyek (<code>cd</code> ke <code>perpustakaan-api</code>), lalu <code>notepad bootstrap\app.php</code>. <strong>Jangan menempel</strong> blok <code>-&gt;withMiddleware(...)</code> baru di dalam fungsi yang sudah ada. Kerja di dalam fungsi itu: hapus <code>//</code> kalau masih ada, biarkan baris lain, lalu tempel <strong>hanya</strong>:</p>
<pre><code class="language-php">// Tempel di dalam withMiddleware yang sudah ada — jangan buat withMiddleware baru
$middleware-&gt;preventRequestForgery(except: [
    'api/*',
]);
</code></pre>
<p>Hasil yang benar kira-kira begini (boleh ada baris lain di sekitarnya):</p>
<pre><code class="language-php">-&gt;withMiddleware(function (Middleware $middleware): void {
    $middleware-&gt;preventRequestForgery(except: [
        'api/*',
    ]);
})
</code></pre>
<p><strong>Awam:</strong> <code>except</code> = “kecuali”. Di Laravel 13 nama resminya <code>preventRequestForgery</code>; nama lama <code>validateCsrfTokens</code> masih sama artinya.</p>
<p><strong>Setiap kali menyimpan <code>bootstrap\app.php</code></strong>, matikan <code>serve</code> di terminal pertama (<code>Ctrl+C</code>) lalu <code>php artisan serve</code> lagi — file itu hanya dibaca saat aplikasi mulai.</p>
<p><strong>Tenang, kartunya tetap aman.</strong> Pagar yang menjaga <code>/api/saya</code> adalah petugas kartu Sanctum yang kamu pasang di artikel ini — CSRF itu satpam lobi untuk formulir browser, jenis yang berbeda.</p>

<h2>Uji di terminal kedua</h2>
<p>Jangan andalkan bilah alamat browser untuk POST login. Ikuti pola uji di <a href="/artikel/laravel-request-validasi-api">Request &amp; Form Request: Menjaga Input API (#59)</a>.</p>
<p><strong>Opsi A — <code>curl.exe</code></strong> (Windows 10/11 biasanya sudah punya; ketik <code>curl.exe</code> agar tidak tertukar di PowerShell):</p>
<p><strong>1) Login — ambil token</strong></p>
<pre><code class="language-bash">curl.exe -s -X POST http://127.0.0.1:8000/api/login ^
  -H "Content-Type: application/json" ^
  -H "Accept: application/json" ^
  -d "{\"email\":\"staf@perpustakaan.test\",\"password\":\"password\"}"
</code></pre>
<p><strong>Awam — salin token:</strong> di jawaban JSON cari kunci <code>"token"</code>. Salin <em>hanya</em> string di antara tanda kutip (panjang, tanpa spasi di ujung). Jangan salin kata <code>Bearer</code> dari JSON — kata Bearer ditulis nanti di header. Ganti <code>GANTI_DENGAN_TOKEN</code> di perintah berikutnya dengan string itu.</p>
<p><strong>Awam — cara salin teks dari terminal Windows:</strong> blok teks dengan klik kiri lalu tahan sambil digeser (klik-drag), lepas tombol mouse untuk menyalin otomatis. Tempel dengan klik kanan di jendela terminal (bukan <code>Ctrl+V</code>, terminal bawaan Windows kadang tidak mendukungnya).</p>
<p><strong>2) Tanpa kartu — harus 401</strong></p>
<pre><code class="language-bash">curl.exe -s http://127.0.0.1:8000/api/saya ^
  -H "Accept: application/json"
</code></pre>
<p>Kamu harus melihat pesan seperti “Belum diizinkan” / unauthenticated — bukan profil staf.</p>
<p><strong>3) Dengan kartu — harus 200 + data nama/email</strong></p>
<pre><code class="language-bash">curl.exe -s http://127.0.0.1:8000/api/saya ^
  -H "Accept: application/json" ^
  -H "Authorization: Bearer GANTI_DENGAN_TOKEN"
</code></pre>
<p><strong>Opsi B — PowerShell</strong> (kalau kutip <code>curl</code> ribet):</p>
<pre><code class="language-powershell"># Login — catat .token dari hasil
$login = Invoke-RestMethod -Method Post -Uri http://127.0.0.1:8000/api/login `
  -ContentType 'application/json' -Headers @{ Accept = 'application/json' } `
  -Body '{"email":"staf@perpustakaan.test","password":"password"}'
$login.token
</code></pre>
<pre><code class="language-powershell"># Dengan kartu — ganti isi string token
Invoke-RestMethod -Method Get -Uri http://127.0.0.1:8000/api/saya `
  -Headers @{ Accept = 'application/json'; Authorization = 'Bearer GANTI_DENGAN_TOKEN' }
</code></pre>
<p><strong>Opsi C — alat uji API berjendela</strong> (Postman / Insomnia — opsional). Login: metode <strong>POST</strong>, URL <code>http://127.0.0.1:8000/api/login</code>, body JSON email+password, header <code>Accept</code> + <code>Content-Type</code> = <code>application/json</code>. Lalu GET <code>/api/saya</code> dengan header <code>Authorization: Bearer …</code> (tempel token). Ide sama: tanpa kartu ditolak, dengan kartu diterima.</p>
<p><strong>Awam:</strong> baris <code>Accept: application/json</code> meminta jawaban JSON (bukan halaman HTML error panjang). <code>^</code> di CMD = lanjut baris; di PowerShell pakai backtick <code>`</code> atau satu baris. Selalu <code>curl.exe</code>, bukan alias <code>curl</code>.</p>

<h2>Pola Dasar — empat langkah kartu bersih</h2>
<figure role="img" aria-label="Pola Dasar empat langkah Auth API" style="background:#F5F5F0;border:2px solid #1a1a1a;border-radius:12px;padding:1rem;margin:1.25rem 0">
<ol style="list-style:none;padding:0;margin:0;display:grid;gap:.75rem">
  <li style="display:flex;gap:.75rem;align-items:flex-start">
    <span style="flex:0 0 2rem;height:2rem;border-radius:999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div><strong style="color:#1a1a1a">Buka alat</strong><br><span style="color:#1a1a1a">Terminal 1 <code>serve</code> · Terminal 2 Composer/Artisan/curl · Editor.</span></div>
  </li>
  <li style="display:flex;gap:.75rem;align-items:flex-start">
    <span style="flex:0 0 2rem;height:2rem;border-radius:999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div><strong style="color:#1a1a1a">Pasang Sanctum</strong><br><span style="color:#1a1a1a"><code>composer require</code> · publish · migrate · <code>HasApiTokens</code>.</span></div>
  </li>
  <li style="display:flex;gap:.75rem;align-items:flex-start">
    <span style="flex:0 0 2rem;height:2rem;border-radius:999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div><strong style="color:#1a1a1a">Login + pintu</strong><br><span style="color:#1a1a1a"><code>POST /api/login</code> · <code>GET /api/saya</code> + <code>auth:sanctum</code>.</span></div>
  </li>
  <li style="display:flex;gap:.75rem;align-items:flex-start">
    <span style="flex:0 0 2rem;height:2rem;border-radius:999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div><strong style="color:#1a1a1a">Uji 401 lalu 200</strong><br><span style="color:#1a1a1a">Tanpa Bearer ditolak; dengan Bearer menampilkan profil singkat.</span></div>
  </li>
</ol>
</figure>

<h2>Demo kartu anggota — file mandiri</h2>
<p>Latihan ide tanpa mengubah proyek Laravel:</p>
<ol>
  <li>Buka editor, buat file baru, tempel cuplikan di bawah, simpan sebagai <code>laravel_auth_api_dasar_demo.php</code> (boleh di Desktop).</li>
  <li>Buka terminal di folder file itu (Explorer: Shift+klik kanan -&gt; “Open in Terminal” / “Buka di Terminal”, atau <code>cd</code> manual). Pastikan <code>php -v</code> jalan.</li>
  <li>Jalankan: <code>php laravel_auth_api_dasar_demo.php</code> — layar mensimulasikan login gagal/sukses dan cek kartu.</li>
</ol>
<p>File ini <strong>mensimulasikan</strong> peran — tidak mengubah proyek Laravel-mu:</p>
<pre><code class="language-php">&lt;?php

declare(strict_types=1);

function demo(): void
{
    $staf = ['email' =&gt; 'staf@perpustakaan.test', 'password' =&gt; 'password'];

    echo "=== Simulasi Auth API dasar ===", PHP_EOL;

    $salah = loginSimulasi('salah@x.test', 'x', $staf);
    echo "Login salah -&gt; ", $salah['message'], " (HTTP ", $salah['status'], ")", PHP_EOL;

    $ok = loginSimulasi('staf@perpustakaan.test', 'password', $staf);
    echo "Login OK -&gt; token: ", $ok['token'], PHP_EOL;

    $tanpa = cekKartuSimulasi(null);
    echo "Tanpa kartu -&gt; ", $tanpa['message'], " (HTTP ", $tanpa['status'], ")", PHP_EOL;

    $dengan = cekKartuSimulasi($ok['token'] ?? null);
    echo "Dengan kartu -&gt; ", $dengan['message'], " · ", ($dengan['data']['email'] ?? '-'), PHP_EOL;
    echo PHP_EOL, "Langkah sungguhan: Sanctum -&gt; user uji -&gt; AuthController -&gt; route -&gt; curl.exe.", PHP_EOL;
}

/**
 * Simulasi login — kembalikan status + pesan (+ token jika sukses).
 */
function loginSimulasi(string $email, string $password, array $staf): array
{
    if ($email !== $staf['email'] || $password !== $staf['password']) {
        return ['status' =&gt; 401, 'message' =&gt; 'Belum diizinkan — email/sandi salah'];
    }

    return [
        'status' =&gt; 200,
        'message' =&gt; 'Login berhasil',
        'token' =&gt; 'kartu-demo-'.substr(sha1($email), 0, 8),
    ];
}

/**
 * Simulasi cek kartu di pintu terlindungi.
 */
function cekKartuSimulasi(?string $token): array
{
    if ($token === null || $token === '') {
        return ['status' =&gt; 401, 'message' =&gt; 'Belum diizinkan'];
    }

    return [
        'status' =&gt; 200,
        'message' =&gt; 'Kartu diterima',
        'data' =&gt; ['email' =&gt; 'staf@perpustakaan.test'],
    ];
}

demo();
</code></pre>
<p><strong>Awam:</strong> <code>demo()</code> hanya latihan di terminal. Setelah paham, kerjakan langkah Sanctum di folder <code>perpustakaan-api</code>. <code>declare(strict_types=1);</code> membuat tipe lebih ketat — boleh diikuti, tidak wajib dihafal.</p>

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
      <td><code>CSRF token mismatch</code> / kode <code>419</code> saat login</td>
      <td>Izin <code>api/*</code> di <code>bootstrap\app.php</code> belum dipasang, atau <code>serve</code> belum dinyalakan ulang</td>
      <td>Pasang <code>preventRequestForgery(except: ['api/*'])</code> (jangan timpa baris lain), lalu <code>Ctrl+C</code> dan <code>php artisan serve</code> lagi</td>
    </tr>
    <tr>
      <td><code>composer</code> tidak dikenal</td>
      <td>Terminal Start Menu / PATH</td>
      <td>Pakai Laragon Terminal atau Shell XAMPP — lihat <strong>Persiapan</strong></td>
    </tr>
    <tr>
      <td>Class HasApiTokens not found</td>
      <td>Sanctum belum require / trait belum di-use</td>
      <td>Ulangi <code>composer require laravel/sanctum</code> + cek <code>User.php</code></td>
    </tr>
    <tr>
      <td>401 terus saat login</td>
      <td>User uji belum dibuat / sandi beda</td>
      <td>Jalankan ulang perintah <code>tinker --execute</code> di atas</td>
    </tr>
    <tr>
      <td>401 di <code>/api/saya</code> meski sudah login</td>
      <td>Header Bearer kosong / token salah tempel</td>
      <td>Salin token utuh dari kunci <code>"token"</code>; format <code>Authorization: Bearer …</code> (satu spasi setelah Bearer)</td>
    </tr>
    <tr>
      <td>Jawaban HTML panjang / bukan JSON</td>
      <td>Lupa header <code>Accept: application/json</code></td>
      <td>Tambahkan header Accept seperti di Opsi A</td>
    </tr>
    <tr>
      <td><code>curl</code> aneh di PowerShell</td>
      <td>Alias <code>curl</code> tertukar</td>
      <td>Pakai <code>curl.exe</code>, Opsi B PowerShell, atau Opsi C</td>
    </tr>
    <tr>
      <td>Connection refused</td>
      <td><code>serve</code> mati</td>
      <td>Nyalakan lagi di terminal pertama setelah <code>cd</code> ke proyek</td>
    </tr>
  </tbody>
</table>

<h2>Latihan</h2>
<ol>
  <li>Jalankan demo PHP di atas — pastikan kasus login salah = 401 simulasi.</li>
  <li>Di proyek: pasang Sanctum, buat user uji, login, lalu bandingkan respons tanpa vs dengan Bearer.</li>
  <li>Jelaskan ke teman: beda singkat “satpam slip (validasi)” dan “satpam kartu (auth)” dengan bahasa toko.</li>
</ol>

<h2>FAQ</h2>
<p><strong>Terminal mana yang harus dibuka?</strong><br>
Laragon: menu Terminal · XAMPP: tombol Shell. Satu jendela untuk <code>serve</code>, jendela kedua untuk Composer/Artisan/<code>curl.exe</code>.</p>
<p><strong>Harus Postman?</strong><br>
Tidak. <code>curl.exe</code> / PowerShell cukup. Alat berjendela (Opsi C) hanya jika kutip di terminal terasa ribet.</p>
<p><strong>Token yang mana yang disalin?</strong><br>
Dari jawaban login, kunci <code>"token"</code> — string panjang di antara kutip. Jangan salin seluruh JSON. Jangan tulis ulang kata <code>Bearer</code> dua kali.</p>
<p><strong>Apa itu Bearer tanpa istilah sulit?</strong><br>
Bearer = “bawa kartu di header”. Isinya token dari login. Tanpa itu, pintu <code>/api/saya</code> menolak.</p>
<p><strong>Kenapa pakai Sanctum, bukan session web biasa?</strong><br>
API JSON sering dipanggil dari terminal/aplikasi lain. Token mudah dibawa di header. Session cookie lebih cocok halaman web ber-form.</p>
<p><strong>Apa hubungan dengan artikel loket?</strong><br>
<a href="/artikel/laravel-controller-service-eloquent">Controller, Service &amp; Eloquent Laravel (#60)</a> merapikan daftar buku. <strong>#61 (ini)</strong> menambah siapa yang boleh masuk pintu staf.</p>
<p><strong>Ke mana setelah ini?</strong><br>
Berikutnya: <a href="/artikel/capstone-api-perpustakaan-laravel">Capstone: API Perpustakaan (Baca + Login + Tambah) (#62)</a> — menggabungkan baca katalog, login, dan tambah buku dengan kartu.</p>

<h2>Kesimpulan</h2>
<p>Kamu sudah memasang Auth API dasar: Sanctum dari nol, login mengeluarkan token, pintu <code>/api/saya</code> terlindungi, diuji lewat terminal saat <code>artisan serve</code> hidup. Ini langkah <strong>6/8</strong> jalur Laravel di Seri 4.</p>
<blockquote>
  <p><strong>Seri 4 progress:</strong> langkah <strong>#61 (ini)</strong> · <strong>6/8</strong> jalur Laravel · prasyarat: <a href="/artikel/laravel-controller-service-eloquent">Controller, Service &amp; Eloquent Laravel (#60)</a> LIVE. Berikutnya: <a href="/artikel/capstone-api-perpustakaan-laravel">Capstone: API Perpustakaan (Baca + Login + Tambah) (#62)</a>.</p>
</blockquote>
HTML;
    }
}
