<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article57Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $webCat = Category::where('slug', 'web-development')->first()
            ?? Category::where('slug', 'programming')->first();

        if (! $admin || ! $webCat) {
            throw new \RuntimeException('User atau kategori web-development/programming tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'laravel-request-validasi-api';

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
        ] as $tagSlug => $tagName) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagName]);
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $webCat->id,
                'title'           => 'Request & Form Request: Penjaga Input API',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'Laravel Request & Form Request untuk Pemula',
                'seo_description' => 'Seri 4 #57: validasi input API perpustakaan — dari cek PHP sederhana ke Request & Form Request Laravel, status 422, berbahasa Indonesia.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['laravel', 'php', 'api', 'http', 'web'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel ke-57 berhasil dipublish: '.$article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — pintu sudah ada, siapa yang menjaga?</h2>
<p>Di <a href="/artikel/laravel-routing-json-perpustakaan-api">Laravel Routing &amp; JSON (#56)</a> kamu sudah punya pintu HTTP yang menjawab JSON. Artikel ini adalah <strong>#57 (ini)</strong> — langkah kedua stack <strong>Laravel</strong> di Seri 4.</p>
<p>Ide barunya: data yang masuk lewat pintu bisa kotor (kosong, salah tipe, tidak masuk akal). Kita butuh <strong>penjaga</strong> yang memeriksa dulu — baru boleh diproses.</p>
<p><strong>Awam:</strong> bayangkan loket perpustakaan. Pengunjung membawa formulir pinjam. Petugas (penjaga) cek: nama terisi? tahun masuk akal? Kalau tidak, formulir dikembalikan dengan catatan — bukan langsung masuk ke rak.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> sudah baca <a href="/artikel/laravel-routing-json-perpustakaan-api">Laravel Routing &amp; JSON (#56)</a> — paham route, JSON, dan status 200/404. Domain tetap <strong>perpustakaan mini</strong>. Pakai <strong>Laravel 11+</strong> — sintaks validasi di sini berlaku di versi modern.</p>
</blockquote>

<h2>Istilah — Request, validasi, Form Request</h2>
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
      <td>Request</td>
      <td>Paket data yang masuk lewat pintu (judul, tahun, dll.)</td>
      <td><code>{"judul":"...","tahun":2024}</code></td>
    </tr>
    <tr>
      <td>Validasi</td>
      <td>Cek: data wajib ada, tipe benar, rentang masuk akal</td>
      <td>Judul tidak kosong; tahun 1900-2100</td>
    </tr>
    <tr>
      <td>Form Request</td>
      <td>Kelas Laravel khusus yang menyimpan aturan penjaga</td>
      <td><code>StoreBukuRequest</code></td>
    </tr>
    <tr>
      <td>POST</td>
      <td>Cara mengetuk pintu: “kirim data baru/ubah” (bukan hanya minta baca)</td>
      <td><code>POST /api/buku</code> + JSON isi buku</td>
    </tr>
    <tr>
      <td>Status 422</td>
      <td>“Data kamu tidak diterima” (bukan “pintu tidak ada”)</td>
      <td><code>422</code> — isian ditolak</td>
    </tr>
  </tbody>
</table>
<p>Jangan hafal semua status dulu. Cukup tiga: <strong>200/201</strong> (sukses), <strong>404</strong> (tidak ketemu), <strong>422</strong> (data kotor).</p>

<h2>Validasi dulu — tanpa framework</h2>
<p>Kenapa belum langsung Form Request? Karena ide “cek dulu, baru proses” bisa dirasakan di PHP biasa. Kalau ide-nya sudah “klik”, cuplikan Laravel nanti terasa seperti bungkus yang sama.</p>

<pre><code class="language-php">&lt;?php
$input = [
    "judul" =&gt; "",
    "tahun" =&gt; 2024,
];

$errors = [];

if (! isset($input["judul"]) || trim((string) $input["judul"]) === "") {
    $errors["judul"] = "Judul wajib diisi";
}

if (! isset($input["tahun"]) || ! is_numeric($input["tahun"])) {
    $errors["tahun"] = "Tahun harus angka";
} elseif ((int) $input["tahun"] &lt; 1900 || (int) $input["tahun"] &gt; 2100) {
    $errors["tahun"] = "Tahun di luar rentang";
}

header("Content-Type: application/json; charset=utf-8");

if ($errors !== []) {
    http_response_code(422);
    echo json_encode(["pesan" =&gt; "Data tidak valid", "errors" =&gt; $errors], JSON_UNESCAPED_UNICODE), PHP_EOL;
    exit;
}

http_response_code(201);
echo json_encode(["ok" =&gt; true, "buku" =&gt; $input], JSON_UNESCAPED_UNICODE), PHP_EOL;
</code></pre>

<p>Output:</p>
<pre><code>{"pesan":"Data tidak valid","errors":{"judul":"Judul wajib diisi"}}
</code></pre>

<p><strong>Awam:</strong> status <code>422</code> artinya “pintu ketemu, tapi isian ditolak”. Bedakan dari <code>404</code> (pintu/data tidak ada) di <a href="/artikel/laravel-routing-json-perpustakaan-api">Laravel Routing &amp; JSON (#56)</a>.</p>

<h2>Kalau data bersih — status 201</h2>
<p>Isian lengkap dan masuk akal boleh “diterima”:</p>

<pre><code class="language-php">&lt;?php
$input = [
    "judul" =&gt; "Belajar PHP",
    "tahun" =&gt; 2024,
];

$errors = [];

if (! isset($input["judul"]) || trim((string) $input["judul"]) === "") {
    $errors["judul"] = "Judul wajib diisi";
}

if (! isset($input["tahun"]) || ! is_numeric($input["tahun"])) {
    $errors["tahun"] = "Tahun harus angka";
} elseif ((int) $input["tahun"] &lt; 1900 || (int) $input["tahun"] &gt; 2100) {
    $errors["tahun"] = "Tahun di luar rentang";
}

header("Content-Type: application/json; charset=utf-8");

if ($errors !== []) {
    http_response_code(422);
    echo json_encode(["pesan" =&gt; "Data tidak valid", "errors" =&gt; $errors], JSON_UNESCAPED_UNICODE), PHP_EOL;
    exit;
}

http_response_code(201);
echo json_encode(["ok" =&gt; true, "buku" =&gt; $input], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL;
</code></pre>

<p>Output:</p>
<pre><code>{
    "ok": true,
    "buku": {
        "judul": "Belajar PHP",
        "tahun": 2024
    }
}
</code></pre>

<p><strong>Awam:</strong> <code>201</code> sering dipakai untuk “berhasil membuat sesuatu baru” (misalnya buku baru). Kalau bingung, anggap dulu “sukses” — angka pastinya bisa dirapikan nanti.</p>

<figure role="img" aria-label="Diagram request masuk, penjaga validasi, lalu JSON sukses atau 422" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 240" style="display:block;max-width:720px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="laravel57reqArrow" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/></marker>
  </defs>
  <rect x="0" y="0" width="720" height="240" fill="#F5F5F0" rx="6"/>
  <text x="360" y="28" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Request masuk -&gt; penjaga cek -&gt; JSON jawaban</text>
  <rect x="40" y="70" width="160" height="100" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="120" y="115" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">Browser / curl</text>
  <text x="120" y="140" text-anchor="middle" fill="#2D3748" font-size="12">POST + JSON</text>
  <rect x="280" y="70" width="160" height="100" rx="6" fill="#1a1a1a" stroke="#000" stroke-width="2.5"/>
  <text x="360" y="115" text-anchor="middle" fill="#fff" font-size="14" font-weight="700">Penjaga</text>
  <text x="360" y="140" text-anchor="middle" fill="#90CDF4" font-size="12">validasi aturan</text>
  <rect x="520" y="70" width="160" height="100" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="600" y="115" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">JSON + status</text>
  <text x="600" y="140" text-anchor="middle" fill="#2D3748" font-size="12">201 atau 422</text>
  <line x1="200" y1="120" x2="280" y2="120" stroke="#2979FF" stroke-width="2.5" marker-end="url(#laravel57reqArrow)"/>
  <line x1="440" y1="120" x2="520" y2="120" stroke="#2979FF" stroke-width="2.5" marker-end="url(#laravel57reqArrow)"/>
</svg>
<figcaption style="margin-top:.75rem;color:#2D3748;font-size:.95rem">Penjaga bukan “menolak orang”. Ia menolak data yang tidak layak — supaya sistem tetap rapi.</figcaption>
</figure>

<h2>Laravel — Request di route</h2>
<p>Di project Laravel, cuplikan tipikal memakai <code>$request-&gt;validate(...)</code>. File ini <strong>bukan</strong> dijalankan dengan <code>php file.php</code> — ia hidup di dalam project Laravel:</p>

<pre><code class="language-php">&lt;?php
// Cuplikan Laravel (bukan file mandiri) — ide sama dengan validasi PHP di atas.
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/api/buku', function (Request $request) {
    $data = $request-&gt;validate([
        'judul' =&gt; 'required|string|max:120',
        'tahun' =&gt; 'required|integer|min:1900|max:2100',
    ]);

    return response()-&gt;json(['ok' =&gt; true, 'buku' =&gt; $data], 201);
});
</code></pre>

<p><strong>Awam:</strong></p>
<ul>
  <li><code>Request $request</code> = paket data masuk (isi formulir / isi JSON)</li>
  <li><code>validate([...])</code> = daftar aturan penjaga</li>
  <li>Teks aturan dipisah <code>|</code>: <code>required</code> = wajib, <code>string</code> = teks, <code>integer</code> = bilangan bulat, <code>max:120</code> / <code>min:1900</code> = batas panjang/nilai</li>
  <li>Kalau gagal, Laravel biasanya menjawab status <code>422</code> + daftar error (tanpa kamu tulis manual)</li>
</ul>

<h2>Form Request — aturan pindah ke kelas</h2>
<p>Kalau aturan makin panjang, jangan biarkan semuanya menumpuk di file route. Pindahkan ke kelas Form Request:</p>

<pre><code class="language-php">&lt;?php
// Cuplikan Laravel — kelas penjaga (bukan file mandiri).
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBukuRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'judul' =&gt; 'required|string|max:120',
            'tahun' =&gt; 'required|integer|min:1900|max:2100',
        ];
    }
}
</code></pre>

<pre><code class="language-php">&lt;?php
// Cuplikan Laravel — pakai Form Request di route.
use App\Http\Requests\StoreBukuRequest;
use Illuminate\Support\Facades\Route;

Route::post('/api/buku', function (StoreBukuRequest $request) {
    $data = $request-&gt;validated();

    return response()-&gt;json(['ok' =&gt; true, 'buku' =&gt; $data], 201);
});
</code></pre>

<p><strong>Awam:</strong> <code>validated()</code> hanya mengembalikan field yang sudah lolos penjaga. Data kotor tidak ikut “nyelonong”.</p>

<h2>Pola Dasar — Request &amp; validasi</h2>
<figure style="margin:1.5rem 0;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1.25rem;color:#1a1a1a" aria-label="Lima langkah Request dan Form Request">
<ol style="list-style:none;padding:0;margin:0;color:#1a1a1a">
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Tahu data apa yang masuk</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Misalnya <code>judul</code> dan <code>tahun</code> untuk buku baru.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Tulis aturan penjaga</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Wajib? String? Angka? Rentang tahun?</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Tolak dengan status jujur</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Data kotor -&gt; <code>422</code>. Jangan pura-pura <code>200</code>.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Pakai data yang sudah bersih</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem"><code>validated()</code> / array yang lolos cek — bukan data mentah dari request.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">5</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Baru pikir penyimpanan &amp; struktur kode</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Berikutnya: pengatur kode (controller), layanan (service), dan penyimpanan data — setelah penjaga berdiri.</span>
    </div>
  </li>
</ol>
</figure>

<h2>Kode lengkap — <code>laravel_request_validasi_demo.php</code></h2>
<p>Simpan dan jalankan: <code>php laravel_request_validasi_demo.php</code>. Ini meniru penjaga validasi tanpa server Laravel — supaya ide-nya terasa dulu.</p>
<p><strong>Awam:</strong> fungsi <code>validasiBuku()</code> mengembalikan daftar kesalahan (kosong = lolos). Baris <code>mixed $data</code> artinya data bisa bermacam bentuk — tidak perlu dihafal. Fokus ke alur: cek -&gt; tolak/terima.</p>

<pre><code class="language-php">&lt;?php
/**
 * Demo ide Request &amp; validasi (Seri 4 #57).
 * Di Laravel, ide yang sama hidup di Request::validate / Form Request.
 */

declare(strict_types=1);

function kirimJson(mixed $data, int $status = 200): void
{
    http_response_code($status);
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL;
}

function validasiBuku(array $input): array
{
    $errors = [];

    if (! isset($input['judul']) || trim((string) $input['judul']) === '') {
        $errors['judul'] = 'Judul wajib diisi';
    }

    if (! isset($input['tahun']) || ! is_numeric($input['tahun'])) {
        $errors['tahun'] = 'Tahun harus angka';
    } elseif ((int) $input['tahun'] &lt; 1900 || (int) $input['tahun'] &gt; 2100) {
        $errors['tahun'] = 'Tahun di luar rentang';
    }

    return $errors;
}

function demo(): void
{
    echo "=== POST kotor (judul kosong) ===", PHP_EOL;
    $kotor = ['judul' =&gt; '', 'tahun' =&gt; 2024];
    $errors = validasiBuku($kotor);
    if ($errors !== []) {
        kirimJson(['pesan' =&gt; 'Data tidak valid', 'errors' =&gt; $errors], 422);
    }

    echo "=== POST bersih ===", PHP_EOL;
    $bersih = ['judul' =&gt; 'Belajar PHP', 'tahun' =&gt; 2024];
    $errors = validasiBuku($bersih);
    if ($errors === []) {
        kirimJson(['ok' =&gt; true, 'buku' =&gt; $bersih], 201);
    }
}

demo();
</code></pre>

<p>Output yang diharapkan:</p>
<pre><code>=== POST kotor (judul kosong) ===
{
    "pesan": "Data tidak valid",
    "errors": {
        "judul": "Judul wajib diisi"
    }
}
=== POST bersih ===
{
    "ok": true,
    "buku": {
        "judul": "Belajar PHP",
        "tahun": 2024
    }
}
</code></pre>

<h2>Kesalahan umum</h2>
<table>
  <thead>
    <tr>
      <th>Gejala</th>
      <th>Penyebab tipikal</th>
      <th>Perbaikan</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Data kotor tetap diproses</td>
      <td>Langsung pakai input mentah</td>
      <td>Validasi dulu; pakai hasil yang sudah bersih</td>
    </tr>
    <tr>
      <td>Gagal validasi tapi status 200</td>
      <td>Lupa status error</td>
      <td>Pakai <code>422</code> (atau biarkan Laravel yang mengurus)</td>
    </tr>
    <tr>
      <td>Bingung 404 vs 422</td>
      <td>Semua error dianggap “tidak ketemu”</td>
      <td>404 = tidak ada; 422 = ada tapi isian ditolak</td>
    </tr>
    <tr>
      <td>Aturan menumpuk di route</td>
      <td>Semua cek ditumpuk di dalam file route</td>
      <td>Pindah ke Form Request (<strong>#57 (ini)</strong>)</td>
    </tr>
    <tr>
      <td>Langsung loncat database</td>
      <td>Mau simpan sebelum penjaga siap</td>
      <td>Rapatkan validasi dulu, baru penyimpanan nanti</td>
    </tr>
  </tbody>
</table>

<h2>Latihan singkat</h2>
<ol>
  <li>Di demo PHP, tambah aturan: judul maksimal 40 karakter; uji dengan judul yang terlalu panjang.</li>
  <li>Ubah tahun jadi <code>1800</code> dan pastikan output menampilkan error rentang (bukan sukses).</li>
  <li>Di cuplikan Laravel, tambah field <code>penulis</code> wajib string di aturan <code>validate</code>.</li>
</ol>

<h2>FAQ singkat</h2>
<p><strong>Harus install Laravel dulu?</strong><br>Untuk memahami ide: demo PHP di atas sudah cukup. Untuk latihan framework: buat project Laravel 11+ lalu tempel cuplikan route / Form Request.</p>
<p><strong>Kenapa tidak cukup cek di tampilan browser saja?</strong><br>Tampilan di browser (sering disebut frontend) bisa dilewati. Penjaga di server (API) tetap wajib — itu tempat keputusan yang bisa dipercaya.</p>
<p><strong>Apa bedanya <code>$request-&gt;validate</code> dan Form Request?</strong><br>Secara awam: sama-sama penjaga. Form Request memindahkan aturan ke kelas sendiri supaya file route / pengatur kode tetap tipis dan rapi.</p>
<p><strong>Lanjut ke mana?</strong><br>Berikutnya: pengatur kode (controller), layanan (service), dan penyimpanan data — setelah penjaga berdiri, baru rapikan tempat menyimpan buku.</p>

<h2>Kesimpulan &amp; langkah berikutnya</h2>
<p>Request = paket masuk. Validasi = cek kelayakan. Form Request = rumah aturan penjaga. Status 422 = “isian ditolak dengan jujur”.</p>
<p>Artikel ini adalah <strong>#57 (ini)</strong> — penjaga input setelah <a href="/artikel/laravel-routing-json-perpustakaan-api">Laravel Routing &amp; JSON (#56)</a> membuka pintu HTTP.</p>

<blockquote>
  <p><strong>Seri 4 progress:</strong> langkah <strong>#57 (ini)</strong> · 5/8 menuju Capstone Laravel · stack Laravel <strong>2/5</strong> · prasyarat: <a href="/artikel/laravel-routing-json-perpustakaan-api">Laravel Routing &amp; JSON (#56)</a> LIVE. Berikutnya: pengatur kode (controller), layanan (service), dan penyimpanan data.</p>
</blockquote>
HTML;
    }
}
