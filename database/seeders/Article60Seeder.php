<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article60Seeder extends Seeder
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
            'auth' => 'auth',
            'eloquent' => 'eloquent',
        ] as $tagSlug => $tagName) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagName]);
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $webCat->id,
                'title'           => 'Capstone: API Perpustakaan Laravel',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => true,
                'seo_title'       => 'Capstone API Perpustakaan Laravel — Seri 4',
                'seo_description' => 'Seri 4 #60 Capstone: merangkai routing, validasi, controller/service/Eloquent, dan auth jadi API perpustakaan mini — berbahasa Indonesia, ramah awam.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['laravel', 'php', 'api', 'http', 'web', 'auth', 'eloquent'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel ke-60 berhasil dipublish: '.$article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — Capstone: satukan semua</h2>
<p>Artikel ini adalah <strong>#60 (ini)</strong> — <strong>Capstone</strong> Seri 4. Capstone artinya penutup yang merangkai langkah sebelumnya jadi satu cerita utuh, bukan ide baru yang loncat jauh.</p>
<p>Kamu sudah punya potongan di <a href="/artikel/laravel-routing-json-perpustakaan-api">Routing &amp; JSON (#56)</a>, <a href="/artikel/laravel-request-validasi-api">Request &amp; Form Request (#57)</a>, <a href="/artikel/laravel-controller-service-eloquent">Controller, Service &amp; Eloquent (#58)</a>, dan <a href="/artikel/laravel-auth-api-dasar">Auth API Dasar (#59)</a>. Sekarang kita satukan: <strong>API perpustakaan mini</strong> yang bisa dibaca publik, tapi menambah buku hanya setelah login + bukti masuk.</p>
<p><strong>Awam:</strong> bayangkan perpustakaan yang pintunya sudah dipasang: peta jalan (route), penjaga isian, loket + pekerja + rak (controller/service/Eloquent), lalu kartu anggota (auth). Capstone = menjalankan semuanya dalam satu alur.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> sudah baca <a href="/artikel/laravel-auth-api-dasar">Auth API Dasar (#59)</a> — paham login, bukti masuk, dan status <code>401</code>. Domain tetap <strong>perpustakaan mini</strong>. Pakai <strong>Laravel 11+</strong> — ide merangkai di sini berlaku di versi modern.</p>
</blockquote>

<h2>Spesifikasi fitur — apa yang kita bangun?</h2>
<p>Jangan mulai dari “proyek besar”. Mulai dari daftar singkat yang bisa dijelaskan ke teman:</p>
<ol>
  <li><strong>Baca katalog</strong> — publik (tanpa bukti masuk). Jawaban JSON daftar buku.</li>
  <li><strong>Login staf</strong> — email + sandi. Sukses = bukti masuk; gagal = <code>401</code>.</li>
  <li><strong>Tambah buku</strong> — wajib bukti masuk. Isian kotor = <code>422</code>; tanpa bukti = <code>401</code>; sukses = <code>201</code>.</li>
</ol>
<p><strong>Awam:</strong> tiga pintu itu cukup untuk merasakan “API utuh”. Fitur pinjam/kembali/admin penuh bisa jadi latihan lanjut — bukan syarat Capstone ini.</p>

<h2>Istilah — ringkas untuk Capstone</h2>
<table>
  <thead>
    <tr>
      <th>Istilah</th>
      <th>Arti awam</th>
      <th>Dari artikel</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Route</td>
      <td>Peta: URL + metode HTTP ke fungsi yang menangani</td>
      <td><a href="/artikel/laravel-routing-json-perpustakaan-api">Routing &amp; JSON (#56)</a></td>
    </tr>
    <tr>
      <td>Form Request</td>
      <td>Penjaga isian sebelum masuk ke pengatur kode</td>
      <td><a href="/artikel/laravel-request-validasi-api">Request &amp; Form Request (#57)</a></td>
    </tr>
    <tr>
      <td>Controller / Service / Eloquent</td>
      <td>Loket · pekerja · cara simpan ke database</td>
      <td><a href="/artikel/laravel-controller-service-eloquent">Controller, Service &amp; Eloquent (#58)</a></td>
    </tr>
    <tr>
      <td>Bukti masuk + 401</td>
      <td>Kartu anggota; tanpa kartu = belum diizinkan</td>
      <td><a href="/artikel/laravel-auth-api-dasar">Auth API Dasar (#59)</a></td>
    </tr>
  </tbody>
</table>
<p>Jangan hafal ulang semua. Cukup ingat urutan: <strong>pintu -&gt; cek siapa kamu -&gt; cek isian -&gt; simpan -&gt; jawab JSON</strong>.</p>

<h2>Kenapa belum langsung satu proyek Laravel besar?</h2>
<p>Karena ide “satukan alur” lebih mudah dirasakan di PHP biasa dulu. Kalau alurnya sudah “klik”, cuplikan Laravel terasa seperti bungkus yang sama — bukan sihir baru.</p>

<pre><code class="language-php">&lt;?php
// Mini alur Capstone (bukan database sungguhan): tanpa bukti = 401.
$buktiValid = "kartu-abc123";
$buktiDariHeader = ""; // kosong

header("Content-Type: application/json; charset=utf-8");

if ($buktiDariHeader === "" || $buktiDariHeader !== $buktiValid) {
    http_response_code(401);
    echo json_encode(["pesan" =&gt; "Belum diizinkan — bawa bukti masuk"], JSON_UNESCAPED_UNICODE), PHP_EOL;
    exit;
}

echo json_encode(["ok" =&gt; true], JSON_UNESCAPED_UNICODE), PHP_EOL;
</code></pre>

<p>Output:</p>
<pre><code>{"pesan":"Belum diizinkan — bawa bukti masuk"}
</code></pre>

<p><strong>Awam:</strong> ini hanya pintu cek bukti (auth). Di Capstone, setelah pintu terbuka baru validasi isian dan simpan buku — seperti antrean loket yang rapi.</p>

<figure role="img" aria-label="Diagram Capstone merangkai route validasi controller service auth" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 760 260" style="display:block;max-width:760px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="laravel60capArrow" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/></marker>
  </defs>
  <rect x="0" y="0" width="760" height="260" fill="#F5F5F0" rx="6"/>
  <text x="380" y="28" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Capstone: route -&gt; cek login -&gt; validasi -&gt; service -&gt; JSON</text>
  <rect x="16" y="70" width="130" height="90" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="81" y="110" text-anchor="middle" fill="#1a1a1a" font-size="12" font-weight="700">Route</text>
  <text x="81" y="132" text-anchor="middle" fill="#2D3748" font-size="11">pintu URL</text>
  <rect x="162" y="70" width="130" height="90" rx="6" fill="#1a1a1a" stroke="#000" stroke-width="2.5"/>
  <text x="227" y="110" text-anchor="middle" fill="#fff" font-size="12" font-weight="700">Cek login</text>
  <text x="227" y="132" text-anchor="middle" fill="#90CDF4" font-size="11">bukti / 401</text>
  <rect x="308" y="70" width="130" height="90" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="373" y="110" text-anchor="middle" fill="#1a1a1a" font-size="12" font-weight="700">Validasi</text>
  <text x="373" y="132" text-anchor="middle" fill="#2D3748" font-size="11">422 kotor</text>
  <rect x="454" y="70" width="130" height="90" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="519" y="110" text-anchor="middle" fill="#1a1a1a" font-size="12" font-weight="700">Service</text>
  <text x="519" y="132" text-anchor="middle" fill="#2D3748" font-size="11">simpan buku</text>
  <rect x="600" y="70" width="140" height="90" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="670" y="110" text-anchor="middle" fill="#1a1a1a" font-size="12" font-weight="700">JSON</text>
  <text x="670" y="132" text-anchor="middle" fill="#2D3748" font-size="11">201 / 200</text>
  <line x1="146" y1="115" x2="162" y2="115" stroke="#2979FF" stroke-width="2.5" marker-end="url(#laravel60capArrow)"/>
  <line x1="292" y1="115" x2="308" y2="115" stroke="#2979FF" stroke-width="2.5" marker-end="url(#laravel60capArrow)"/>
  <line x1="438" y1="115" x2="454" y2="115" stroke="#2979FF" stroke-width="2.5" marker-end="url(#laravel60capArrow)"/>
  <line x1="584" y1="115" x2="600" y2="115" stroke="#2979FF" stroke-width="2.5" marker-end="url(#laravel60capArrow)"/>
  <text x="380" y="210" text-anchor="middle" fill="#2D3748" font-size="13">Katalog publik boleh lewati cek login; tambah buku tidak.</text>
</svg>
<figcaption style="margin-top:.75rem;color:#2D3748;font-size:.95rem">Capstone bukan “semua route dikunci”. Publik tetap bisa baca; yang sensitif yang wajib bukti.</figcaption>
</figure>

<h2>Alur tambah buku — dari bukti sampai JSON</h2>
<p>Setelah login (lihat <a href="/artikel/laravel-auth-api-dasar">Auth API Dasar (#59)</a>), <strong>pemanggil</strong> — aplikasi atau alat yang memanggil API — membawa bukti. Di PHP sederhana, urutannya seperti ini:</p>

<pre><code class="language-php">&lt;?php
$buktiValid = "kartu-abc123";
$buktiDariHeader = "kartu-abc123";
$isian = ["judul" =&gt; "Belajar PHP", "tahun" =&gt; 2024];

header("Content-Type: application/json; charset=utf-8");

if ($buktiDariHeader !== $buktiValid) {
    http_response_code(401);
    echo json_encode(["pesan" =&gt; "Belum diizinkan — bawa bukti masuk"], JSON_UNESCAPED_UNICODE), PHP_EOL;
    exit;
}

if (($isian["judul"] ?? "") === "" || ! is_int($isian["tahun"] ?? null)) {
    http_response_code(422);
    echo json_encode(["pesan" =&gt; "Isian belum rapi"], JSON_UNESCAPED_UNICODE), PHP_EOL;
    exit;
}

http_response_code(201);
echo json_encode(["ok" =&gt; true, "buku" =&gt; $isian], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL;
</code></pre>

<p>Output (bentuknya mirip):</p>
<pre><code>{
    "ok": true,
    "buku": {
        "judul": "Belajar PHP",
        "tahun": 2024
    }
}
</code></pre>

<p><strong>Awam:</strong> urutan penting. Kalau validasi dulu tanpa auth, orang asing bisa “menguji” isian. Banyak API cek bukti dulu, baru isian — seperti di diagram.</p>

<h2>Laravel — merangkai cuplikan (bukan file mandiri)</h2>
<p>Di proyek Laravel, ide yang sama biasanya tersebar di beberapa file. Cuplikan di bawah <strong>bukan</strong> dijalankan dengan <code>php file.php</code>:</p>

<pre><code class="language-php">&lt;?php
// Cuplikan Laravel (bukan file mandiri) — route publik + terlindungi.
use App\Http\Controllers\BukuController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/api/buku', [BukuController::class, 'index']); // publik
Route::post('/api/login', [AuthController::class, 'login']);

Route::post('/api/buku', [BukuController::class, 'store'])
    -&gt;middleware('auth:sanctum'); // wajib bukti masuk
</code></pre>

<p><strong>Awam:</strong></p>
<ul>
  <li><code>index</code> = baca daftar (katalog publik)</li>
  <li><code>login</code> = keluarkan bukti masuk (dari <a href="/artikel/laravel-auth-api-dasar">Auth API Dasar (#59)</a>)</li>
  <li><code>store</code> = fungsi tambah; dijaga pemeriksa pintu (<code>middleware</code> <code>auth:sanctum</code>)</li>
  <li>Isian <code>store</code> biasanya lewat Form Request (dari <a href="/artikel/laravel-request-validasi-api">Request &amp; Form Request (#57)</a>), lalu Service + Eloquent (dari <a href="/artikel/laravel-controller-service-eloquent">Controller, Service &amp; Eloquent (#58)</a>)</li>
</ul>

<pre><code class="language-php">&lt;?php
// Cuplikan Laravel — controller tipis memanggil service.
namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Services\BukuService;
use Illuminate\Http\JsonResponse;

class BukuController extends Controller
{
    public function __construct(private BukuService $bukuService)
    {
    }

    public function store(StoreBookRequest $request): JsonResponse
    {
        $buku = $this-&gt;bukuService-&gt;tambah($request-&gt;validated());

        return response()-&gt;json(['ok' =&gt; true, 'buku' =&gt; $buku], 201);
    }
}
</code></pre>

<p><strong>Awam:</strong> <code>StoreBookRequest</code> = penjaga isian. <code>validated()</code> = ambil isian yang sudah lolos penjaga. <code>BukuService</code> = pekerja yang menyimpan (Eloquent di dalamnya). <code>JsonResponse</code> = tipe jawaban “ini JSON” — boleh diabaikan dulu kalau masih asing. Baris <code>private BukuService $bukuService</code> di konstruktor = Laravel menyiapkan layanan otomatis (tidak perlu <code>new</code> manual). Controller tetap tipis: terima yang sudah bersih, minta service bekerja, balas JSON.</p>

<h2>Pola Dasar — Capstone API</h2>
<figure style="margin:1.5rem 0;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1.25rem;color:#1a1a1a" aria-label="Enam langkah Capstone API perpustakaan">
<ol style="list-style:none;padding:0;margin:0;color:#1a1a1a">
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Tulis spesifikasi singkat</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Katalog publik · login · tambah buku terlindungi.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Pasang route</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Peta URL dari <a href="/artikel/laravel-routing-json-perpustakaan-api">Routing &amp; JSON (#56)</a>.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Kunci yang sensitif</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Auth + bukti masuk dari <a href="/artikel/laravel-auth-api-dasar">Auth API Dasar (#59)</a>.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Jaga isian</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Form Request / validasi dari <a href="/artikel/laravel-request-validasi-api">Request &amp; Form Request (#57)</a> — kotor = <code>422</code>.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">5</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Simpan lewat service</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Controller tipis + Service + Eloquent dari <a href="/artikel/laravel-controller-service-eloquent">Controller, Service &amp; Eloquent (#58)</a>.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">6</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Uji tiga jalur</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Tanpa bukti · isian kotor · sukses 201. Baru kembangkan fitur lain.</span>
    </div>
  </li>
</ol>
</figure>

<h2>Kode lengkap — demo mandiri Capstone</h2>
<p>Simpan sebagai <code>laravel_capstone_perpustakaan_demo.php</code>, lalu jalankan <code>php laravel_capstone_perpustakaan_demo.php</code>:</p>

<pre><code class="language-php">&lt;?php
declare(strict_types=1);

$anggota = [
    "email" =&gt; "staf@perpustakaan.test",
    "sandi" =&gt; "rahasia123",
];

$buktiAktif = null;
$katalog = [
    ["judul" =&gt; "Dasar PHP", "tahun" =&gt; 2023],
];

function login(array $input, array $anggota): array
{
    if (($input["email"] ?? "") !== $anggota["email"] || ($input["sandi"] ?? "") !== $anggota["sandi"]) {
        return ["status" =&gt; 401, "body" =&gt; ["pesan" =&gt; "Belum diizinkan — email atau sandi salah"]];
    }

    $bukti = "kartu-".bin2hex(random_bytes(4));

    return ["status" =&gt; 200, "body" =&gt; ["ok" =&gt; true, "bukti_masuk" =&gt; $bukti]];
}

function katalog(array $katalog): array
{
    return ["status" =&gt; 200, "body" =&gt; ["ok" =&gt; true, "data" =&gt; $katalog]];
}

function tambahBuku(?string $buktiHeader, ?string $buktiAktif, array $isian, array &amp;$katalog): array
{
    if ($buktiHeader === null || $buktiHeader === "" || $buktiHeader !== $buktiAktif) {
        return ["status" =&gt; 401, "body" =&gt; ["pesan" =&gt; "Belum diizinkan — bawa bukti masuk"]];
    }

    $judul = trim((string) ($isian["judul"] ?? ""));
    $tahun = $isian["tahun"] ?? null;
    if ($judul === "" || ! is_int($tahun)) {
        return ["status" =&gt; 422, "body" =&gt; ["pesan" =&gt; "Isian belum rapi"]];
    }

    $buku = ["judul" =&gt; $judul, "tahun" =&gt; $tahun];
    $katalog[] = $buku;

    return ["status" =&gt; 201, "body" =&gt; ["ok" =&gt; true, "buku" =&gt; $buku]];
}

function demo(string $judul, callable $aksi): void
{
    echo "=== {$judul} ===", PHP_EOL;
    $hasil = $aksi();
    echo "status: ", $hasil["status"], PHP_EOL;
    echo json_encode($hasil["body"], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL, PHP_EOL;
}

demo("Katalog publik -&gt; 200", function () use ($katalog) {
    return katalog($katalog);
});

demo("Login kotor -&gt; 401", function () use ($anggota) {
    return login(["email" =&gt; "staf@perpustakaan.test", "sandi" =&gt; "salah"], $anggota);
});

demo("Login bersih -&gt; 200 + bukti", function () use ($anggota, &amp;$buktiAktif) {
    $hasil = login(["email" =&gt; "staf@perpustakaan.test", "sandi" =&gt; "rahasia123"], $anggota);
    if (($hasil["body"]["bukti_masuk"] ?? null) !== null) {
        $buktiAktif = $hasil["body"]["bukti_masuk"];
    }

    return $hasil;
});

demo("Tambah tanpa bukti -&gt; 401", function () use (&amp;$buktiAktif, &amp;$katalog) {
    return tambahBuku(null, $buktiAktif, ["judul" =&gt; "Belajar Laravel", "tahun" =&gt; 2024], $katalog);
});

demo("Tambah isian kotor -&gt; 422", function () use (&amp;$buktiAktif, &amp;$katalog) {
    return tambahBuku($buktiAktif, $buktiAktif, ["judul" =&gt; "", "tahun" =&gt; "dua ribu"], $katalog);
});

demo("Tambah bersih -&gt; 201", function () use (&amp;$buktiAktif, &amp;$katalog) {
    return tambahBuku($buktiAktif, $buktiAktif, ["judul" =&gt; "Belajar Laravel", "tahun" =&gt; 2024], $katalog);
});
</code></pre>

<p><strong>Awam:</strong> <code>demo(...)</code> hanya membungkus output di terminal. <code>callable</code> = sesuatu yang bisa dipanggil seperti fungsi. <code>declare(strict_types=1);</code> membuat tipe lebih ketat — boleh diikuti, tidak wajib dihafal. Alur penting: katalog publik, login gagal/sukses, tambah tanpa bukti, isian kotor, lalu sukses.</p>

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
      <td>Semua pintu API 401</td>
      <td>Katalog publik ikut dikunci pemeriksa pintu (middleware)</td>
      <td>Kunci hanya yang sensitif (misalnya POST tambah)</td>
    </tr>
    <tr>
      <td>Bingung 401 vs 422</td>
      <td>Mencampur “belum diizinkan” dengan “data kotor”</td>
      <td>401 = identitas; 422 = isian</td>
    </tr>
    <tr>
      <td>Controller membengkak</td>
      <td>Validasi + simpan + JSON digabung di satu fungsi</td>
      <td>Form Request + Service + controller tipis</td>
    </tr>
    <tr>
      <td>Capstone terasa “belum selesai”</td>
      <td>Target terlalu besar (pinjam, denda, admin…)</td>
      <td>Kunci dulu 3 fitur inti; sisanya latihan</td>
    </tr>
  </tbody>
</table>

<h2>Latihan singkat</h2>
<ol>
  <li>Ubah demo: tambah kasus “bukti palsu” dan pastikan tetap <code>401</code>.</li>
  <li>Jelaskan ke teman (tanpa jargon): urutan auth -&gt; validasi -&gt; simpan dengan analogi loket.</li>
  <li>Tulis satu kalimat: beda pintu publik (<code>GET /api/buku</code>) dan pintu terlindungi (<code>POST /api/buku</code>).</li>
</ol>

<h2>FAQ singkat</h2>
<p><strong>Apakah Capstone harus satu folder proyek Laravel lengkap di laptop?</strong><br>Idealnya ya, tapi artikel ini menekankan <strong>alur</strong>. Demo PHP mandiri sudah cukup untuk “merasakan” satunya pintu. Cuplikan Laravel menunjukkan tempat masing-masing potongan.</p>
<p><strong>Haruskah pakai Sanctum sekarang?</strong><br><strong>Sanctum</strong> = paket Laravel untuk mengeluarkan dan memeriksa bukti masuk API. Untuk alur Capstone, itu pilihan umum di Laravel modern. Detail pasang-pasangnya bisa dipelajari setelah alurnya sudah jelas (lihat juga <a href="/artikel/laravel-auth-api-dasar">Auth API Dasar (#59)</a>).</p>
<p><strong>Ke mana setelah Seri 4?</strong><br>Berikutnya: <a href="/artikel/laravel-crud-api-buku-ubah-hapus">CRUD API Buku: Ubah &amp; Hapus (#61)</a> — pembuka Seri 5 Laravel Lanjutan. Memperdalam pola yang sama: ubah dan hapus buku lewat API.</p>

<h2>Indeks Seri 4 lengkap</h2>
<ol>
  <li><a href="/artikel/mengenal-oop-cara-berpikir-dengan-objek-php">Mengenal OOP PHP (#53)</a></li>
  <li><a href="/artikel/oop-php-property-method-constructor">Property, Method &amp; Constructor (#54)</a></li>
  <li><a href="/artikel/oop-php-visibility-composition">Visibility &amp; Composition (#55)</a></li>
  <li><a href="/artikel/laravel-routing-json-perpustakaan-api">Laravel Routing &amp; JSON (#56)</a></li>
  <li><a href="/artikel/laravel-request-validasi-api">Request &amp; Form Request (#57)</a></li>
  <li><a href="/artikel/laravel-controller-service-eloquent">Controller, Service &amp; Eloquent (#58)</a></li>
  <li><a href="/artikel/laravel-auth-api-dasar">Auth API Dasar (#59)</a></li>
  <li><strong>Capstone: API Perpustakaan Laravel (#60 (ini))</strong> — artikel ini</li>
</ol>

<h2>Kesimpulan</h2>
<p>Kamu sudah menutup Seri 4 dengan Capstone: <strong>route</strong>, <strong>auth</strong>, <strong>validasi</strong>, dan <strong>controller/service/Eloquent</strong> digabung jadi API perpustakaan mini. Publik boleh baca; staf membawa bukti masuk untuk menambah buku. Alur dari <a href="/artikel/laravel-auth-api-dasar">Auth API Dasar (#59)</a> tetap dipakai — sekarang dalam satu cerita utuh.</p>

<blockquote>
  <p><strong>Seri 4 progress:</strong> langkah <strong>#60 (ini)</strong> · 8/8 Capstone Laravel selesai · stack Laravel <strong>5/5</strong> · prasyarat: <a href="/artikel/laravel-auth-api-dasar">Auth API Dasar (#59)</a> LIVE. Berikutnya: <a href="/artikel/laravel-crud-api-buku-ubah-hapus">CRUD API Buku: Ubah &amp; Hapus (#61)</a> — Seri 5 Laravel Lanjutan.</p>
</blockquote>
HTML;
    }
}
