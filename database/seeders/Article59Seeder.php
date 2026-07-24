<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article59Seeder extends Seeder
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
            'auth' => 'auth',
        ] as $tagSlug => $tagName) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagName]);
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $webCat->id,
                'title'           => 'Auth API Dasar: Siapa yang Boleh Masuk',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'Laravel Auth API Dasar untuk Pemula',
                'seo_description' => 'Seri 4 #59: otentikasi API perpustakaan — login, bukti masuk, status 401, dari PHP sederhana ke cuplikan Laravel, berbahasa Indonesia.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['laravel', 'php', 'api', 'http', 'web', 'auth'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel ke-59 berhasil dipublish: '.$article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — alur sudah rapi, siapa yang boleh masuk?</h2>
<p>Di <a href="/artikel/laravel-controller-service-eloquent">Controller, Service &amp; Eloquent (#58)</a> kamu sudah punya alur rapi: penjaga input, pengatur kode, layanan, dan penyimpanan. Artikel ini adalah <strong>#59 (ini)</strong> — langkah keempat stack <strong>Laravel</strong> di Seri 4.</p>
<p>Ide barunya: tidak semua orang boleh memanggil API. Kita butuh <strong>otentikasi</strong> — memastikan “siapa kamu” sebelum pintu dibuka. Awam: <strong>bukti masuk</strong> (sering disebut token) seperti kartu anggota perpustakaan.</p>
<p><strong>Awam:</strong> bayangkan loket khusus staf. Siapa saja boleh lihat katalog umum. Tapi menambah buku hanya untuk yang sudah login dan membawa kartu anggota (bukti masuk). Tanpa kartu: ditolak — bukan karena data kotor, tapi karena belum diizinkan.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> sudah baca <a href="/artikel/laravel-controller-service-eloquent">Controller, Service &amp; Eloquent (#58)</a> — paham controller/service dan status JSON. Domain tetap <strong>perpustakaan mini</strong>. Pakai <strong>Laravel 11+</strong> — ide login + bukti masuk di sini berlaku di versi modern.</p>
</blockquote>

<h2>Istilah — Auth, login, bukti masuk</h2>
<table>
  <thead>
    <tr>
      <th>Istilah</th>
      <th>Arti awam</th>
      <th>Contoh singkat</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Otentikasi (auth)</td>
      <td>Memastikan “siapa kamu” sebelum akses</td>
      <td>Login email + kata sandi</td>
    </tr>
    <tr>
      <td>Login</td>
      <td>Proses membuktikan identitas (biasanya email/sandi)</td>
      <td><code>POST /api/login</code></td>
    </tr>
    <tr>
      <td>Bukti masuk (token)</td>
      <td>Kartu sementara setelah login — dibawa di permintaan berikutnya</td>
      <td>String acak di header</td>
    </tr>
    <tr>
      <td>Status 401</td>
      <td>“Belum diizinkan” — pintu ketemu, tapi kamu belum terbukti</td>
      <td><code>401</code> tanpa bukti / bukti salah</td>
    </tr>
    <tr>
      <td>Pemeriksa pintu</td>
      <td>Lapisan yang cek bukti masuk sebelum controller jalan (sering disebut middleware)</td>
      <td>Cek header dulu, baru <code>store</code></td>
    </tr>
  </tbody>
</table>
<p>Jangan hafal semua dulu. Cukup: <strong>login mengeluarkan bukti</strong>, <strong>permintaan berikutnya membawa bukti</strong>, <strong>tanpa bukti = 401</strong>.</p>
<p>Bedakan dari <code>422</code> di <a href="/artikel/laravel-request-validasi-api">Request &amp; Form Request (#57)</a>: <code>422</code> = data kotor; <code>401</code> = kamu belum diizinkan.</p>

<h2>Kenapa belum langsung paket Laravel?</h2>
<p>Kenapa belum langsung paket bukti masuk Laravel (sering disebut Sanctum) / login bawaan? Karena ide “cek identitas dulu, baru lanjut” bisa dirasakan di PHP biasa. Kalau ide-nya sudah “klik”, cuplikan Laravel nanti terasa seperti bungkus yang sama.</p>

<pre><code class="language-php">&lt;?php
// Anggota sederhana (bukan database sungguhan).
$anggota = [
    "email" =&gt; "staf@perpustakaan.test",
    "sandi" =&gt; "rahasia123",
];

$input = [
    "email" =&gt; "staf@perpustakaan.test",
    "sandi" =&gt; "salah",
];

header("Content-Type: application/json; charset=utf-8");

if ($input["email"] !== $anggota["email"] || $input["sandi"] !== $anggota["sandi"]) {
    http_response_code(401);
    echo json_encode(["pesan" =&gt; "Belum diizinkan — email atau sandi salah"], JSON_UNESCAPED_UNICODE), PHP_EOL;
    exit;
}

$bukti = "kartu-".bin2hex(random_bytes(8));
http_response_code(200);
echo json_encode(["ok" =&gt; true, "bukti_masuk" =&gt; $bukti], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL;
</code></pre>

<p>Output:</p>
<pre><code>{"pesan":"Belum diizinkan — email atau sandi salah"}
</code></pre>

<p><strong>Awam:</strong> status <code>401</code> artinya “pintu ketemu, tapi kamu belum terbukti”. Sandi di contoh disimpan polos supaya mudah dibaca — di dunia nyata sandi disimpan terenkripsi (tidak dibaca apa adanya). <code>bin2hex(random_bytes(...))</code> hanya cara membuat teks acak untuk kartu — tidak perlu dihafal dulu.</p>

<figure role="img" aria-label="Diagram login mengeluarkan bukti masuk lalu permintaan terlindungi" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 760 240" style="display:block;max-width:760px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="laravel59authArrow" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/></marker>
  </defs>
  <rect x="0" y="0" width="760" height="240" fill="#F5F5F0" rx="6"/>
  <text x="380" y="28" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Login -&gt; bukti masuk -&gt; permintaan terlindungi</text>
  <rect x="24" y="70" width="150" height="100" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="99" y="115" text-anchor="middle" fill="#1a1a1a" font-size="13" font-weight="700">Login</text>
  <text x="99" y="140" text-anchor="middle" fill="#2D3748" font-size="11">email + sandi</text>
  <rect x="210" y="70" width="150" height="100" rx="6" fill="#1a1a1a" stroke="#000" stroke-width="2.5"/>
  <text x="285" y="115" text-anchor="middle" fill="#fff" font-size="13" font-weight="700">Cek login</text>
  <text x="285" y="140" text-anchor="middle" fill="#90CDF4" font-size="11">siapa kamu?</text>
  <rect x="396" y="70" width="150" height="100" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="471" y="115" text-anchor="middle" fill="#1a1a1a" font-size="13" font-weight="700">Bukti masuk</text>
  <text x="471" y="140" text-anchor="middle" fill="#2D3748" font-size="11">kartu sementara</text>
  <rect x="582" y="70" width="150" height="100" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="657" y="115" text-anchor="middle" fill="#1a1a1a" font-size="13" font-weight="700">API terlindungi</text>
  <text x="657" y="140" text-anchor="middle" fill="#2D3748" font-size="11">201 atau 401</text>
  <line x1="174" y1="120" x2="210" y2="120" stroke="#2979FF" stroke-width="2.5" marker-end="url(#laravel59authArrow)"/>
  <line x1="360" y1="120" x2="396" y2="120" stroke="#2979FF" stroke-width="2.5" marker-end="url(#laravel59authArrow)"/>
  <line x1="546" y1="120" x2="582" y2="120" stroke="#2979FF" stroke-width="2.5" marker-end="url(#laravel59authArrow)"/>
</svg>
<figcaption style="margin-top:.75rem;color:#2D3748;font-size:.95rem">Bukti masuk bukan “rahasia abadi”. Ia dikeluarkan setelah login, lalu dibawa di permintaan berikutnya.</figcaption>
</figure>

<h2>Login bersih — dapat bukti masuk</h2>
<p>Kalau email dan sandi cocok, sistem mengeluarkan bukti:</p>

<pre><code class="language-php">&lt;?php
$anggota = [
    "email" =&gt; "staf@perpustakaan.test",
    "sandi" =&gt; "rahasia123",
];

$input = [
    "email" =&gt; "staf@perpustakaan.test",
    "sandi" =&gt; "rahasia123",
];

header("Content-Type: application/json; charset=utf-8");

if ($input["email"] !== $anggota["email"] || $input["sandi"] !== $anggota["sandi"]) {
    http_response_code(401);
    echo json_encode(["pesan" =&gt; "Belum diizinkan — email atau sandi salah"], JSON_UNESCAPED_UNICODE), PHP_EOL;
    exit;
}

$bukti = "kartu-".bin2hex(random_bytes(8));
http_response_code(200);
echo json_encode(["ok" =&gt; true, "bukti_masuk" =&gt; $bukti], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL;
</code></pre>

<p>Output (bentuknya mirip; angka di bukti berubah tiap jalan):</p>
<pre><code>{
    "ok": true,
    "bukti_masuk": "kartu-ab12cd34ef56..."
}
</code></pre>

<p><strong>Awam:</strong> simpan bukti ini di sisi pemanggil (aplikasi / alat uji), lalu kirim lagi saat menambah buku.</p>

<h2>Pintu terlindungi — cek bukti dulu</h2>
<p>Sebelum controller menyimpan buku, pemeriksa pintu membaca bukti masuk:</p>

<pre><code class="language-php">&lt;?php
$buktiValid = "kartu-abc123";
$buktiDariHeader = ""; // kosong = belum bawa kartu

header("Content-Type: application/json; charset=utf-8");

if ($buktiDariHeader === "" || $buktiDariHeader !== $buktiValid) {
    http_response_code(401);
    echo json_encode(["pesan" =&gt; "Belum diizinkan — bawa bukti masuk"], JSON_UNESCAPED_UNICODE), PHP_EOL;
    exit;
}

http_response_code(201);
echo json_encode(["ok" =&gt; true, "buku" =&gt; ["judul" =&gt; "Belajar PHP"]], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL;
</code></pre>

<p>Output:</p>
<pre><code>{"pesan":"Belum diizinkan — bawa bukti masuk"}
</code></pre>

<p><strong>Awam:</strong> ini “pemeriksa pintu” — ide yang sama dengan middleware di Laravel: cek dulu, baru lanjut ke pengatur kode.</p>

<h2>Laravel — login &amp; bukti masuk (cuplikan)</h2>
<p>Di project Laravel, cuplikan tipikal memakai penjaga Form Request + layanan auth. File ini <strong>bukan</strong> dijalankan dengan <code>php file.php</code>:</p>

<pre><code class="language-php">&lt;?php
// Cuplikan Laravel (bukan file mandiri) — login mengeluarkan bukti masuk.
namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::query()-&gt;where('email', $request-&gt;validated('email'))-&gt;first();

        if (! $user || ! Hash::check($request-&gt;validated('sandi'), $user-&gt;password)) {
            return response()-&gt;json(['pesan' =&gt; 'Belum diizinkan — email atau sandi salah'], 401);
        }

        // createToken = cara Laravel (Sanctum) membuat bukti masuk API.
        $bukti = $user-&gt;createToken('api-perpustakaan')-&gt;plainTextToken;

        return response()-&gt;json(['ok' =&gt; true, 'bukti_masuk' =&gt; $bukti], 200);
    }
}
</code></pre>

<p><strong>Awam:</strong></p>
<ul>
  <li><code>LoginRequest</code> = penjaga isian login (ide Form Request dari <a href="/artikel/laravel-request-validasi-api">Request &amp; Form Request (#57)</a>)</li>
  <li><code>JsonResponse</code> = tipe jawaban “ini JSON” (boleh diabaikan dulu kalau masih asing)</li>
  <li><code>Hash::check</code> = bandingkan sandi input dengan sandi tersimpan (yang sudah dienkripsi)</li>
  <li><code>createToken(...)</code> = buat bukti masuk; <code>plainTextToken</code> = teks bukti yang dikirim ke pemanggil (hanya tampil sekali)</li>
  <li>Sanctum = paket Laravel yang biasa dipakai untuk bukti masuk API — detail pasang-pasangnya bisa dipelajari nanti; di sini cukup paham alurnya</li>
</ul>

<pre><code class="language-php">&lt;?php
// Cuplikan Laravel — route terlindungi.
use App\Http\Controllers\BukuController;
use Illuminate\Support\Facades\Route;

// auth:sanctum = pemeriksa pintu: wajib bawa bukti masuk yang valid.
Route::post('/api/buku', [BukuController::class, 'store'])
    -&gt;middleware('auth:sanctum');
</code></pre>

<p><strong>Awam:</strong> pemanggil biasanya mengirim header <code>Authorization: Bearer &lt;bukti&gt;</code>. <code>Authorization</code> = kotak di header untuk “siapa yang meminta”. <code>Bearer</code> artinya “ini bukti yang saya bawa”. Tanpa itu (atau salah): Laravel menjawab <code>401</code>.</p>
<p>Controller <code>store</code> tetap tipis seperti di <a href="/artikel/laravel-controller-service-eloquent">Controller, Service &amp; Eloquent (#58)</a> — yang baru: pintu di depannya sudah dikunci.</p>

<h2>Pola Dasar — Auth API</h2>
<figure style="margin:1.5rem 0;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1.25rem;color:#1a1a1a" aria-label="Lima langkah auth API dasar">
<ol style="list-style:none;padding:0;margin:0;color:#1a1a1a">
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Rapikan alur dulu</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Penjaga + controller/service sudah berdiri (lihat <a href="/artikel/laravel-controller-service-eloquent">Controller, Service &amp; Eloquent (#58)</a>).</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Sediakan login</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Cek email/sandi — gagal = <code>401</code>, sukses = bukti masuk.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Lindungi pintu yang sensitif</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Misalnya <code>POST /api/buku</code> wajib bukti; katalog publik boleh tetap terbuka.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Bawa bukti di setiap permintaan terlindungi</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Header Authorization (tempat bukti) + Bearer + teks bukti.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">5</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Baru satukan jadi proyek utuh</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Berikutnya: Capstone — merangkai routing, validasi, controller/service, dan auth jadi API perpustakaan lengkap.</span>
    </div>
  </li>
</ol>
</figure>

<h2>Kode lengkap — demo mandiri</h2>
<p>Simpan sebagai <code>laravel_auth_api_demo.php</code>, lalu jalankan <code>php laravel_auth_api_demo.php</code>:</p>

<pre><code class="language-php">&lt;?php
declare(strict_types=1);

$anggota = [
    "email" =&gt; "staf@perpustakaan.test",
    "sandi" =&gt; "rahasia123",
];

$buktiAktif = null;

function login(array $input, array $anggota): array
{
    if (($input["email"] ?? "") !== $anggota["email"] || ($input["sandi"] ?? "") !== $anggota["sandi"]) {
        return ["status" =&gt; 401, "body" =&gt; ["pesan" =&gt; "Belum diizinkan — email atau sandi salah"]];
    }

    $bukti = "kartu-".bin2hex(random_bytes(4));

    return ["status" =&gt; 200, "body" =&gt; ["ok" =&gt; true, "bukti_masuk" =&gt; $bukti]];
}

function tambahBuku(?string $buktiHeader, ?string $buktiAktif): array
{
    if ($buktiHeader === null || $buktiHeader === "" || $buktiHeader !== $buktiAktif) {
        return ["status" =&gt; 401, "body" =&gt; ["pesan" =&gt; "Belum diizinkan — bawa bukti masuk"]];
    }

    return ["status" =&gt; 201, "body" =&gt; ["ok" =&gt; true, "buku" =&gt; ["judul" =&gt; "Belajar PHP", "tahun" =&gt; 2024]]];
}

function demo(string $judul, callable $aksi): void
{
    echo "=== {$judul} ===", PHP_EOL;
    $hasil = $aksi();
    echo "status: ", $hasil["status"], PHP_EOL;
    echo json_encode($hasil["body"], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL, PHP_EOL;
}

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

demo("Tanpa bukti -&gt; 401", function () use (&amp;$buktiAktif) {
    return tambahBuku(null, $buktiAktif);
});

demo("Dengan bukti -&gt; 201", function () use (&amp;$buktiAktif) {
    return tambahBuku($buktiAktif, $buktiAktif);
});
</code></pre>

<p><strong>Awam:</strong> <code>demo(...)</code> hanya membungkus output di terminal. <code>callable</code> = sesuatu yang bisa dipanggil seperti fungsi. Baris <code>declare(strict_types=1);</code> membuat tipe data lebih ketat — boleh diikuti, tidak wajib dihafal dulu. Alur yang penting: login gagal/sukses, lalu pintu dengan/ tanpa bukti.</p>

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
      <td>Selalu 401 padahal sudah login</td>
      <td>Bukti tidak dikirim / salah header</td>
      <td>Kirim <code>Authorization: Bearer &lt;bukti&gt;</code></td>
    </tr>
    <tr>
      <td>Bingung 401 vs 422</td>
      <td>Mencampur “belum diizinkan” dengan “data kotor”</td>
      <td>401 = identitas; 422 = isian</td>
    </tr>
    <tr>
      <td>Sandi tersimpan polos</td>
      <td>Tidak memakai Hash (pembanding sandi terenkripsi)</td>
      <td>Simpan sandi terenkripsi lewat Hash</td>
    </tr>
    <tr>
      <td>Semua route dikunci</td>
      <td>Katalog publik ikut pemeriksa pintu (middleware)</td>
      <td>Kunci hanya yang sensitif (misalnya POST)</td>
    </tr>
  </tbody>
</table>

<h2>Latihan singkat</h2>
<ol>
  <li>Ubah demo: tambah kasus “bukti palsu” (string acak) dan pastikan status tetap 401.</li>
  <li>Jelaskan ke teman (tanpa jargon): beda 401 dan 422 dengan analogi loket perpustakaan.</li>
  <li>Tulis satu kalimat: apa yang terjadi dari login sukses sampai <code>POST /api/buku</code> dengan bukti masuk.</li>
</ol>

<h2>FAQ singkat</h2>
<p><strong>Apa bedanya auth dan validasi?</strong><br>Validasi cek “apakah isian masuk akal”. Auth cek “apakah kamu yang berhak”. Keduanya sering berurutan: bukti dulu, baru isian — atau sebaliknya tergantung desain, tapi perannya beda.</p>
<p><strong>Haruskah semua API pakai bukti masuk?</strong><br>Tidak. Baca katalog boleh publik. Menambah/mengubah data biasanya dikunci.</p>
<p><strong>Token / bukti masuk aman digeser ke orang lain?</strong><br>Tidak. Siapa yang punya bukti = dianggap kamu. Jaga seperti kunci.</p>
<p><strong>Lanjut ke mana?</strong><br>Berikutnya: Capstone — merangkai routing, validasi, controller/service/Eloquent, dan auth jadi API perpustakaan yang utuh.</p>

<h2>Kesimpulan</h2>
<p>Kamu sudah menambah kunci di depan alur: <strong>login</strong> mengeluarkan <strong>bukti masuk</strong>, pintu sensitif memeriksa bukti, tanpa bukti = <strong>401</strong>. Alur dari <a href="/artikel/laravel-controller-service-eloquent">Controller, Service &amp; Eloquent (#58)</a> tetap dipakai — hanya pintunya yang dikunci.</p>

<blockquote>
  <p><strong>Seri 4 progress:</strong> langkah <strong>#59 (ini)</strong> · 7/8 menuju Capstone Laravel · stack Laravel <strong>4/5</strong> · prasyarat: <a href="/artikel/laravel-controller-service-eloquent">Controller, Service &amp; Eloquent (#58)</a> LIVE. Berikutnya: Capstone API perpustakaan (merangkai semua langkah).</p>
</blockquote>
HTML;
    }
}
