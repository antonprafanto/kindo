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

        $slug = 'laravel-crud-api-buku-ubah-hapus';

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
            'crud' => 'crud',
            'eloquent' => 'eloquent',
        ] as $tagSlug => $tagName) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagName]);
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $webCat->id,
                'title'           => 'CRUD API Buku: Ubah & Hapus',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'CRUD API Buku Ubah & Hapus — Laravel Lanjutan',
                'seo_description' => 'Seri 5 #61: setelah Capstone bisa baca dan tambah buku, sekarang belajar ubah dan hapus lewat API — PHP dulu, cuplikan Laravel, ramah awam.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['laravel', 'php', 'api', 'http', 'web', 'crud', 'eloquent'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel ke-61 berhasil dipublish: '.$article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — dari Capstone ke ubah &amp; hapus</h2>
<p>Artikel ini adalah <strong>#61 (ini)</strong> — pembuka <strong>Seri 5: Laravel Lanjutan</strong> (di roadmap sering disebut Framework-based). Bukan loncat stack baru: kita memperdalam pola yang sama di domain <strong>perpustakaan mini</strong>.</p>
<p>Di <a href="/artikel/capstone-api-perpustakaan-laravel">Capstone API Perpustakaan Laravel (#60)</a> kamu sudah punya tiga pintu: baca katalog (publik), login staf, dan tambah buku (wajib bukti masuk). Sekarang kita lengkapi CRUD buku: <strong>ubah</strong> dan <strong>hapus</strong>.</p>
<p><strong>Awam:</strong> CRUD = Create · Read · Update · Delete — buat, baca, ubah, hapus. Capstone sudah Create + Read. Artikel ini fokus Update + Delete. Masih satu rak buku; belum pinjam/kembali (itu nanti).</p>

<blockquote>
  <p><strong>Prasyarat:</strong> sudah baca <a href="/artikel/capstone-api-perpustakaan-laravel">Capstone (#60)</a> — paham route, bukti masuk, <code>401</code>/<code>422</code>/<code>201</code>. Pakai <strong>Laravel 11+</strong>.</p>
</blockquote>

<h2>Spesifikasi fitur — apa yang kita bangun?</h2>
<p>Daftar singkat yang bisa dijelaskan ke teman:</p>
<ol>
  <li><strong>Ubah buku</strong> — wajib bukti masuk. ID tidak ketemu = <code>404</code>; isian kotor = <code>422</code>; sukses = <code>200</code>.</li>
  <li><strong>Hapus buku</strong> — wajib bukti masuk. ID tidak ketemu = <code>404</code>; sukses = <code>204</code> (atau <code>200</code> + pesan singkat).</li>
  <li><strong>Katalog &amp; tambah</strong> — tetap seperti Capstone (tidak diulang panjang di sini).</li>
</ol>
<p><strong>Awam:</strong> <code>404</code> = “tidak ketemu di rak”. Beda dengan <code>401</code> (belum diizinkan) dan <code>422</code> (isian belum rapi).</p>

<h2>Istilah — ringkas untuk ubah &amp; hapus</h2>
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
      <td>CRUD</td>
      <td>Empat kerja dasar data: buat · baca · ubah · hapus</td>
      <td>Bukan nama file Laravel</td>
    </tr>
    <tr>
      <td><code>PUT</code> / <code>PATCH</code></td>
      <td>Metode HTTP untuk ubah data yang sudah ada</td>
      <td>Di sini kita pakai <code>PUT</code> sederhana</td>
    </tr>
    <tr>
      <td><code>DELETE</code></td>
      <td>Metode HTTP untuk menghapus</td>
      <td>Wajib bukti masuk di Capstone-lanjutan ini</td>
    </tr>
    <tr>
      <td><code>404</code></td>
      <td>ID / buku tidak ketemu</td>
      <td>Bukan salah sandi</td>
    </tr>
    <tr>
      <td><code>204</code></td>
      <td>Sukses hapus; tubuh jawaban boleh kosong</td>
      <td>Boleh juga <code>200</code> + JSON — pilih satu gaya</td>
    </tr>
  </tbody>
</table>
<p>Urutan tetap: <strong>cek bukti -&gt; cari buku -&gt; cek isian (untuk ubah) -&gt; simpan/hapus -&gt; jawab JSON</strong>.</p>

<h2>Kenapa PHP biasa dulu?</h2>
<p>Ide “ubah baris di rak” dan “buang baris dari rak” lebih mudah dirasakan tanpa framework. Kalau alurnya klik, cuplikan Laravel terasa bungkus yang sama.</p>

<pre><code class="language-php">&lt;?php
// Mini ubah buku: tanpa bukti = 401; ID tidak ketemu = 404.
$buktiValid = "kartu-abc123";
$buktiDariHeader = "";
$katalog = [
    1 =&gt; ["judul" =&gt; "Dasar PHP", "tahun" =&gt; 2023],
];
$id = 1;
$isian = ["judul" =&gt; "Dasar PHP Revisi", "tahun" =&gt; 2024];

header("Content-Type: application/json; charset=utf-8");

if ($buktiDariHeader === "" || $buktiDariHeader !== $buktiValid) {
    http_response_code(401);
    echo json_encode(["pesan" =&gt; "Belum diizinkan — bawa bukti masuk"], JSON_UNESCAPED_UNICODE), PHP_EOL;
    exit;
}

if (! isset($katalog[$id])) {
    http_response_code(404);
    echo json_encode(["pesan" =&gt; "Buku tidak ketemu"], JSON_UNESCAPED_UNICODE), PHP_EOL;
    exit;
}

echo json_encode(["ok" =&gt; true, "catatan" =&gt; "siap ubah"], JSON_UNESCAPED_UNICODE), PHP_EOL;
</code></pre>
<p>Output:</p>
<pre><code>{"pesan":"Belum diizinkan — bawa bukti masuk"}
</code></pre>
<p><strong>Awam:</strong> contoh ini sengaja tanpa bukti agar kamu lihat pintu <code>401</code> dulu. Di demo lengkap nanti, bukti diisi.</p>

<figure role="img" aria-label="Diagram alur ubah dan hapus buku lewat API" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" style="display:block;max-width:760px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif" viewBox="0 0 760 260">
  <defs>
    <marker id="laravel61crudArrow" orient="auto" markerWidth="8" markerHeight="8" refX="7" refY="4" viewBox="0 0 8 8">
      <path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/>
    </marker>
  </defs>
  <rect width="760" height="260" fill="#F5F5F0"/>
  <text x="24" y="36" fill="#1a1a1a" font-size="16" font-weight="700">CRUD: cek bukti -&gt; cari ID -&gt; ubah/hapus -&gt; JSON</text>
  <rect x="24" y="70" width="120" height="70" rx="8" fill="#fff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="84" y="100" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">Cek login</text>
  <text x="84" y="122" text-anchor="middle" fill="#1a1a1a" font-size="12">bukti / 401</text>
  <line x1="144" y1="105" x2="188" y2="105" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel61crudArrow)"/>
  <rect x="192" y="70" width="120" height="70" rx="8" fill="#1a1a1a" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="252" y="100" text-anchor="middle" fill="#fff" font-size="14" font-weight="700">Cari ID</text>
  <text x="252" y="122" text-anchor="middle" fill="#fff" font-size="12">404 jika kosong</text>
  <line x1="312" y1="105" x2="356" y2="105" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel61crudArrow)"/>
  <rect x="360" y="70" width="140" height="70" rx="8" fill="#fff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="430" y="100" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">Ubah / Hapus</text>
  <text x="430" y="122" text-anchor="middle" fill="#1a1a1a" font-size="12">422 jika kotor</text>
  <line x1="500" y1="105" x2="544" y2="105" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel61crudArrow)"/>
  <rect x="548" y="70" width="140" height="70" rx="8" fill="#fff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="618" y="100" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">JSON</text>
  <text x="618" y="122" text-anchor="middle" fill="#1a1a1a" font-size="12">200 / 204</text>
  <text x="24" y="190" fill="#1a1a1a" font-size="13">Ubah &amp; hapus sensitif — jangan dibuka publik tanpa bukti masuk.</text>
  <text x="24" y="220" fill="#1a1a1a" font-size="13">Katalog baca tetap publik seperti Capstone.</text>
</svg>
<figcaption>Capstone sudah punya tambah; <strong>#61 (ini)</strong> menambah ubah dan hapus dengan urutan yang sama.</figcaption>
</figure>

<h2>Alur ubah buku — PHP sederhana</h2>
<p>Setelah login (lihat <a href="/artikel/capstone-api-perpustakaan-laravel">Capstone (#60)</a>), <strong>pemanggil</strong> — aplikasi atau alat yang memanggil API — membawa bukti dan ID buku.</p>

<pre><code class="language-php">&lt;?php
$buktiValid = "kartu-abc123";
$buktiDariHeader = "kartu-abc123";
$katalog = [
    1 =&gt; ["judul" =&gt; "Dasar PHP", "tahun" =&gt; 2023],
];
$id = 1;
$isian = ["judul" =&gt; "Dasar PHP Revisi", "tahun" =&gt; 2024];

header("Content-Type: application/json; charset=utf-8");

if ($buktiDariHeader !== $buktiValid) {
    http_response_code(401);
    echo json_encode(["pesan" =&gt; "Belum diizinkan — bawa bukti masuk"], JSON_UNESCAPED_UNICODE), PHP_EOL;
    exit;
}

if (! isset($katalog[$id])) {
    http_response_code(404);
    echo json_encode(["pesan" =&gt; "Buku tidak ketemu"], JSON_UNESCAPED_UNICODE), PHP_EOL;
    exit;
}

$judul = trim((string) ($isian["judul"] ?? ""));
$tahun = $isian["tahun"] ?? null;
if ($judul === "" || ! is_int($tahun)) {
    http_response_code(422);
    echo json_encode(["pesan" =&gt; "Isian belum rapi"], JSON_UNESCAPED_UNICODE), PHP_EOL;
    exit;
}

$katalog[$id] = ["judul" =&gt; $judul, "tahun" =&gt; $tahun];
http_response_code(200);
echo json_encode(["ok" =&gt; true, "buku" =&gt; $katalog[$id]], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL;
</code></pre>
<p>Output (bentuknya mirip):</p>
<pre><code>{
    "ok": true,
    "buku": {
        "judul": "Dasar PHP Revisi",
        "tahun": 2024
    }
}
</code></pre>
<p><strong>Awam:</strong> urutan penting. Cek bukti dulu, baru cari ID, baru validasi isian — supaya orang asing tidak “menguji” rak dan aturan isian.</p>

<h2>Laravel — cuplikan ubah &amp; hapus (bukan file mandiri)</h2>
<p>Di proyek Laravel, ide yang sama tersebar di route + controller + Form Request + service. Cuplikan di bawah <strong>bukan</strong> dijalankan dengan <code>php file.php</code>:</p>

<pre><code class="language-php">&lt;?php
// Cuplikan Laravel (bukan file mandiri) — ubah &amp; hapus terlindungi.
use App\Http\Controllers\BukuController;
use Illuminate\Support\Facades\Route;

Route::put('/api/buku/{id}', [BukuController::class, 'update'])
    -&gt;middleware('auth:sanctum'); // pemeriksa pintu

Route::delete('/api/buku/{id}', [BukuController::class, 'destroy'])
    -&gt;middleware('auth:sanctum');
</code></pre>

<p><strong>Awam:</strong></p>
<ul>
  <li><code>update</code> = fungsi ubah; dijaga pemeriksa pintu (<code>middleware</code> <code>auth:sanctum</code>)</li>
  <li><code>destroy</code> = fungsi hapus; nama umum di Laravel untuk “buang data”</li>
  <li><code>{id}</code> = nomor buku di URL — seperti nomor loker di rak</li>
</ul>

<pre><code class="language-php">&lt;?php
// Cuplikan Laravel — controller tipis memanggil service.
namespace App\Http\Controllers;

use App\Http\Requests\UpdateBookRequest;
use App\Services\BukuService;
use Illuminate\Http\JsonResponse;

class BukuController extends Controller
{
    public function __construct(private BukuService $bukuService)
    {
    }

    public function update(UpdateBookRequest $request, int $id): JsonResponse
    {
        $buku = $this->bukuService-&gt;ubah($id, $request-&gt;validated());

        if ($buku === null) {
            return response()-&gt;json(['pesan' =&gt; 'Buku tidak ketemu'], 404);
        }

        return response()-&gt;json(['ok' =&gt; true, 'buku' =&gt; $buku], 200);
    }

    public function destroy(int $id): JsonResponse
    {
        $ok = $this-&gt;bukuService-&gt;hapus($id);

        if (! $ok) {
            return response()-&gt;json(['pesan' =&gt; 'Buku tidak ketemu'], 404);
        }

        return response()-&gt;json(null, 204);
    }
}
</code></pre>
<p><strong>Awam:</strong> <code>UpdateBookRequest</code> = penjaga isian untuk ubah. <code>validated()</code> = ambil isian yang sudah lolos. <code>BukuService</code> = pekerja yang menyimpan/menghapus (Eloquent di dalamnya). <code>JsonResponse</code> = tipe jawaban “ini JSON” — boleh diabaikan dulu. Baris <code>private BukuService $bukuService</code> = Laravel menyiapkan layanan otomatis (tidak perlu <code>new</code> manual).</p>

<figure style="margin:1.5rem 0;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1.25rem;color:#1a1a1a" aria-label="Enam langkah CRUD ubah hapus API buku">
<ol style="list-style:none;padding:0;margin:0;color:#1a1a1a">
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div><strong style="color:#1a1a1a">Mulai dari Capstone</strong><br><span style="color:#1a1a1a">Pastikan baca + login + tambah sudah “klik” di <a href="/artikel/capstone-api-perpustakaan-laravel" style="color:#1a1a1a;text-decoration:underline">Capstone (#60)</a>.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div><strong style="color:#1a1a1a">Pasang route ubah &amp; hapus</strong><br><span style="color:#1a1a1a"><code>PUT</code> dan <code>DELETE</code> dengan pemeriksa pintu.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div><strong style="color:#1a1a1a">Cari ID dulu</strong><br><span style="color:#1a1a1a">Tidak ketemu = <code>404</code> — jangan pura-pura sukses.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div><strong style="color:#1a1a1a">Jaga isian saat ubah</strong><br><span style="color:#1a1a1a">Form Request / validasi — kotor = <code>422</code>.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">5</span>
    <div><strong style="color:#1a1a1a">Service tipis</strong><br><span style="color:#1a1a1a">Controller minta pekerja; Eloquent di dalam service.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">6</span>
    <div><strong style="color:#1a1a1a">Uji empat jalur</strong><br><span style="color:#1a1a1a">Tanpa bukti · ID palsu · isian kotor · sukses ubah/hapus.</span></div>
  </li>
</ol>
</figure>

<h2>Kode lengkap — demo mandiri ubah &amp; hapus</h2>
<p>Simpan sebagai <code>laravel_crud_buku_ubah_hapus_demo.php</code>, lalu jalankan <code>php laravel_crud_buku_ubah_hapus_demo.php</code>:</p>

<pre><code class="language-php">&lt;?php
declare(strict_types=1);

$buktiAktif = "kartu-abc123";
$katalog = [
    1 =&gt; ["judul" =&gt; "Dasar PHP", "tahun" =&gt; 2023],
    2 =&gt; ["judul" =&gt; "Belajar Laravel", "tahun" =&gt; 2024],
];

function ubahBuku(?string $buktiHeader, string $buktiAktif, int $id, array $isian, array &amp;$katalog): array
{
    if ($buktiHeader === null || $buktiHeader === "" || $buktiHeader !== $buktiAktif) {
        return ["status" =&gt; 401, "body" =&gt; ["pesan" =&gt; "Belum diizinkan — bawa bukti masuk"]];
    }

    if (! isset($katalog[$id])) {
        return ["status" =&gt; 404, "body" =&gt; ["pesan" =&gt; "Buku tidak ketemu"]];
    }

    $judul = trim((string) ($isian["judul"] ?? ""));
    $tahun = $isian["tahun"] ?? null;
    if ($judul === "" || ! is_int($tahun)) {
        return ["status" =&gt; 422, "body" =&gt; ["pesan" =&gt; "Isian belum rapi"]];
    }

    $katalog[$id] = ["judul" =&gt; $judul, "tahun" =&gt; $tahun];

    return ["status" =&gt; 200, "body" =&gt; ["ok" =&gt; true, "buku" =&gt; $katalog[$id]]];
}

function hapusBuku(?string $buktiHeader, string $buktiAktif, int $id, array &amp;$katalog): array
{
    if ($buktiHeader === null || $buktiHeader === "" || $buktiHeader !== $buktiAktif) {
        return ["status" =&gt; 401, "body" =&gt; ["pesan" =&gt; "Belum diizinkan — bawa bukti masuk"]];
    }

    if (! isset($katalog[$id])) {
        return ["status" =&gt; 404, "body" =&gt; ["pesan" =&gt; "Buku tidak ketemu"]];
    }

    unset($katalog[$id]);

    return ["status" =&gt; 204, "body" =&gt; null];
}

function demo(string $judul, callable $aksi): void
{
    echo "=== {$judul} ===", PHP_EOL;
    $hasil = $aksi();
    echo "status: ", $hasil["status"], PHP_EOL;
    if ($hasil["body"] === null) {
        echo "(tubuh kosong — cocok untuk 204)", PHP_EOL, PHP_EOL;
        return;
    }
    echo json_encode($hasil["body"], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL, PHP_EOL;
}

demo("Ubah tanpa bukti -&gt; 401", function () use ($buktiAktif, &amp;$katalog) {
    return ubahBuku(null, $buktiAktif, 1, ["judul" =&gt; "X", "tahun" =&gt; 2025], $katalog);
});

demo("Ubah ID palsu -&gt; 404", function () use ($buktiAktif, &amp;$katalog) {
    return ubahBuku($buktiAktif, $buktiAktif, 99, ["judul" =&gt; "X", "tahun" =&gt; 2025], $katalog);
});

demo("Ubah isian kotor -&gt; 422", function () use ($buktiAktif, &amp;$katalog) {
    return ubahBuku($buktiAktif, $buktiAktif, 1, ["judul" =&gt; "", "tahun" =&gt; "dua"], $katalog);
});

demo("Ubah bersih -&gt; 200", function () use ($buktiAktif, &amp;$katalog) {
    return ubahBuku($buktiAktif, $buktiAktif, 1, ["judul" =&gt; "Dasar PHP Revisi", "tahun" =&gt; 2025], $katalog);
});

demo("Hapus bersih -&gt; 204", function () use ($buktiAktif, &amp;$katalog) {
    return hapusBuku($buktiAktif, $buktiAktif, 2, $katalog);
});
</code></pre>
<p><strong>Awam:</strong> <code>demo(...)</code> hanya membungkus output di terminal. <code>callable</code> = sesuatu yang bisa dipanggil seperti fungsi. <code>declare(strict_types=1);</code> membuat tipe lebih ketat — boleh diikuti, tidak wajib dihafal. Alur penting: tanpa bukti, ID palsu, isian kotor, sukses ubah, sukses hapus.</p>

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
      <td>Bingung 401 vs 404</td>
      <td>Mencampur “belum diizinkan” dengan “tidak ketemu”</td>
      <td>401 = identitas; 404 = ID kosong di rak</td>
    </tr>
    <tr>
      <td>Ubah publik tanpa bukti</td>
      <td>Lupa pemeriksa pintu (<code>middleware</code>)</td>
      <td>Kunci <code>PUT</code>/<code>DELETE</code> seperti kunci <code>POST</code> tambah</td>
    </tr>
    <tr>
      <td>Hapus sukses tapi data masih ada</td>
      <td>Salah ID atau belum <code>unset</code>/hapus di service</td>
      <td>Uji ulang dengan demo; cek ID yang sama</td>
    </tr>
    <tr>
      <td>Controller membengkak</td>
      <td>Validasi + cari + hapus digabung satu fungsi</td>
      <td>Form Request + Service + controller tipis</td>
    </tr>
  </tbody>
</table>

<h2>Latihan singkat</h2>
<ol>
  <li>Ubah demo: tambah kasus “hapus tanpa bukti” dan pastikan tetap <code>401</code>.</li>
  <li>Jelaskan ke teman: beda <code>404</code> dan <code>422</code> dengan analogi rak buku.</li>
  <li>Tulis satu kalimat: kenapa ubah/hapus tidak boleh publik seperti baca katalog.</li>
</ol>

<h2>FAQ singkat</h2>
<p><strong>Haruskah pakai <code>PUT</code> atau <code>PATCH</code>?</strong><br>Untuk belajar, <code>PUT</code> (ganti data buku yang dikirim) sudah cukup. <code>PATCH</code> biasanya untuk ubah sebagian — boleh dipelajari setelah alur ini jelas.</p>
<p><strong>Kenapa <code>204</code> tubuhnya kosong?</strong><br>Artinya “sukses, tidak ada JSON yang perlu dibaca”. Kalau kamu lebih nyaman <code>200</code> + <code>{"ok": true}</code>, itu juga valid — pilih satu gaya dan konsisten.</p>
<p><strong>Ke mana setelah ini?</strong><br>Berikutnya alami: <strong>relasi Eloquent</strong> (anggota &amp; peminjaman) — masih Laravel lanjutan, belum Capstone pinjam-kembali. Belum perlu hardlink; tunggu artikel berikutnya LIVE.</p>

<h2>Kesimpulan</h2>
<p>Kamu sudah melengkapi CRUD buku: Capstone memberi baca + tambah; <strong>#61 (ini)</strong> menambah <strong>ubah</strong> dan <strong>hapus</strong> dengan urutan bukti -&gt; cari ID -&gt; validasi -&gt; kerja service. Status baru yang penting: <code>404</code> dan <code>204</code>.</p>

<blockquote>
  <p><strong>Seri 5 progress:</strong> langkah <strong>#61 (ini)</strong> · 1/8 Laravel Lanjutan · prasyarat: <a href="/artikel/capstone-api-perpustakaan-laravel">Capstone (#60)</a> LIVE. Berikutnya: Relasi Eloquent (anggota &amp; peminjaman) — soft, belum hardlink.</p>
</blockquote>
HTML;
    }
}
