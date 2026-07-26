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

        $slug = 'laravel-request-validasi-api';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        // Slug v2 Request & Form Request — tidak menyentuh cover_image

        foreach ([
            'laravel' => 'laravel',
            'php' => 'php',
            'api' => 'api',
            'http' => 'http',
            'web' => 'web',
            'json' => 'json',
            'validasi' => 'validasi',
        ] as $tagSlug => $tagName) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagName]);
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $webCat->id,
                'title'           => 'Request & Form Request: Menjaga Input API',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'Request & Form Request Laravel untuk Pemula',
                'seo_description' => 'Seri 4 #59: jaga isi permintaan lewat pintu HTTP — validasi judul & penulis buku, Form Request, status 422 — ramah awam.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        $prevPublished = Article::where('slug', 'laravel-routing-json-perpustakaan-api')->value('published_at');
        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        } elseif ($prevPublished && $article->published_at <= $prevPublished) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['laravel', 'php', 'api', 'http', 'web', 'json', 'validasi'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel ke-59 berhasil dipublish: '.$article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — satpam di pintu toko</h2>
<p>Artikel ini adalah <strong>#59 (ini)</strong> di <strong>Seri 4: Pemrograman Web Lanjut v2</strong>. Di <a href="/artikel/laravel-routing-json-perpustakaan-api">Routing &amp; Jawaban JSON API Perpustakaan (#58)</a> kamu sudah buka pintu HTTP dan jawab JSON. Sekarang langkah <strong>4/8</strong>: jaga <strong>isi permintaan</strong> yang masuk lewat pintu supaya data tidak berantakan.</p>
<p><strong>Awam:</strong> pintu sudah ada. Satpam (validasi) memeriksa slip formulir: judul buku ada? penulis diisi? Kalau slip kosong/kotor, satpam menolak dengan jawaban rapi — bukan menerima data asal-asalan.</p>
<p>Domain tetap <strong>perpustakaan mini</strong>. Hari ini kita menerima permintaan tambah buku (POST) dengan aturan sederhana. Menyimpan ke tabel database dan login datang belakangan.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> sudah selesai <a href="/artikel/laravel-routing-json-perpustakaan-api">Routing &amp; Jawaban JSON API Perpustakaan (#58)</a> — route <code>GET /api/buku</code> pernah jalan. Fondasi di <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a> dan <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a>. Pakai <strong>Laravel 13+</strong> — butuh <strong>PHP 8.3+</strong>.</p>
</blockquote>

<h2>Spesifikasi fitur — apa yang kita kuasai?</h2>
<p>Daftar singkat yang bisa kamu centang di akhir artikel:</p>
<ol>
  <li>Mengerti <strong>Request</strong> sebagai isi permintaan yang masuk lewat pintu HTTP.</li>
  <li>Memakai <code>$request-&gt;validate([...])</code> untuk cek judul &amp; penulis.</li>
  <li>Mengenal <strong>Form Request</strong> sebagai file aturan khusus (lebih rapi).</li>
  <li>Membaca jawaban gagal <strong>422</strong> sebagai “data belum lolos cek”.</li>
</ol>
<p><strong>Awam:</strong> urutan nyaman: <strong>paham Request -&gt; cek di route -&gt; pindah aturan ke Form Request -&gt; uji gagal &amp; sukses</strong>. Belum perlu menyimpan ke tabel database di sini.</p>

<h2>Istilah — ringkas untuk satpam &amp; slip</h2>
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
      <td>Isi permintaan yang datang lewat pintu</td>
      <td>Judul &amp; penulis buku baru</td>
    </tr>
    <tr>
      <td>Validasi</td>
      <td>Cek slip: wajib diisi? bentuknya benar?</td>
      <td><code>judul</code> &amp; <code>penulis</code> required</td>
    </tr>
    <tr>
      <td>Form Request</td>
      <td>File aturan satpam khusus (bukan di route)</td>
      <td><code>StoreBukuRequest</code></td>
    </tr>
    <tr>
      <td>Status 422</td>
      <td>Ditolak karena data belum lolos cek</td>
      <td>Judul kosong -&gt; JSON error</td>
    </tr>
    <tr>
      <td>HTTP POST</td>
      <td>Permintaan “tolong terima data baru”</td>
      <td><code>POST /api/buku</code></td>
    </tr>
  </tbody>
</table>
<p>Urutan belajar: <strong>baca istilah -&gt; lihat alur -&gt; cek di PHP -&gt; Form Request -&gt; uji</strong>.</p>

<h2>Kenapa jaga input dulu?</h2>
<p>Tanpa validasi, API menerima judul kosong atau data aneh. Nanti saat menyimpan ke database, masalahnya makin sulit dilacak.</p>
<p><strong>Awam:</strong> satpam memeriksa slip sebelum buku masuk rak. Lebih baik ditolak di pintu daripada rak penuh kertas kosong.</p>
<p>Artikel ini tetap <strong>install-dari-nol</strong> untuk validasi: tidak ada paket baru. Kita pakai Request &amp; Artisan bawaan Laravel. Fondasi PHP/Composer/Laravel + denah + routing sudah di <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a>, <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a>, dan <a href="/artikel/laravel-routing-json-perpustakaan-api">Routing &amp; Jawaban JSON API Perpustakaan (#58)</a>.</p>

<h2>Alur — dari POST sampai lolos/ditolak</h2>
<figure role="img" aria-label="Alur POST request ke validasi lalu JSON" style="background:#F5F5F0;border:2px solid #1a1a1a;border-radius:12px;padding:1rem;margin:1.25rem 0">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 760 240" width="100%" height="auto" role="img" aria-label="Alur Request ke Form Request">
  <defs>
    <marker id="laravel59reqArrow" orient="auto" markerWidth="8" markerHeight="8" refX="7" refY="4" viewBox="0 0 8 8">
      <path d="M0,0 L8,4 L0,8 Z" fill="#1a1a1a"/>
    </marker>
  </defs>
  <text x="24" y="28" fill="#1a1a1a" font-size="15" font-weight="700">Alur: POST -&gt; Request -&gt; Cek aturan -&gt; JSON</text>
  <rect x="24" y="48" width="140" height="72" rx="10" fill="#2979FF"/>
  <text x="94" y="80" text-anchor="middle" fill="#fff" font-size="15" font-weight="700">Browser</text>
  <text x="94" y="100" text-anchor="middle" fill="#fff" font-size="12">POST /api/buku</text>
  <line x1="164" y1="84" x2="204" y2="84" stroke="#1a1a1a" stroke-width="3" marker-end="url(#laravel59reqArrow)"/>
  <rect x="212" y="48" width="140" height="72" rx="10" fill="#00897B"/>
  <text x="282" y="80" text-anchor="middle" fill="#fff" font-size="15" font-weight="700">Request</text>
  <text x="282" y="100" text-anchor="middle" fill="#fff" font-size="12">isi slip</text>
  <line x1="352" y1="84" x2="392" y2="84" stroke="#1a1a1a" stroke-width="3" marker-end="url(#laravel59reqArrow)"/>
  <rect x="400" y="48" width="140" height="72" rx="10" fill="#F9A825"/>
  <text x="470" y="80" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Cek</text>
  <text x="470" y="100" text-anchor="middle" fill="#1a1a1a" font-size="12">aturan</text>
  <line x1="540" y1="84" x2="580" y2="84" stroke="#1a1a1a" stroke-width="3" marker-end="url(#laravel59reqArrow)"/>
  <rect x="588" y="48" width="148" height="72" rx="10" fill="#1a1a1a"/>
  <text x="662" y="80" text-anchor="middle" fill="#fff" font-size="15" font-weight="700">JSON</text>
  <text x="662" y="100" text-anchor="middle" fill="#fff" font-size="12">OK / 422</text>
  <text x="24" y="160" fill="#1a1a1a" font-size="13">Setelah pintu JSON siap: terima POST, cek judul &amp; penulis,</text>
  <text x="24" y="182" fill="#1a1a1a" font-size="13">lalu jawab sukses atau 422. Menyimpan ke tabel &amp; login datang belakangan.</text>
  <text x="24" y="214" fill="#1a1a1a" font-size="13">Urutan ini mengikuti langkah #59 (ini) — belum pengatur terpisah / tabel database.</text>
</svg>
<figcaption style="color:#1a1a1a;margin-top:.5rem"><strong>#59 (ini)</strong>: POST -&gt; Request -&gt; cek aturan -&gt; JSON OK/422.</figcaption>
</figure>

<h2>Persiapan — toko &amp; pintu masih hidup</h2>
<p>Di folder <code>perpustakaan-api</code>:</p>
<pre><code class="language-bash">php artisan serve</code></pre>
<p>Pastikan <code>GET /api/buku</code> dari <a href="/artikel/laravel-routing-json-perpustakaan-api">Routing &amp; Jawaban JSON API Perpustakaan (#58)</a> masih ada. Hari ini kita menambah pintu <strong>POST</strong>.</p>
<p><strong>Awam:</strong> GET = “tolong kirim daftar”. POST = “tolong terima data baru”.</p>

<h2>PHP dulu — cek slip tanpa Laravel</h2>
<p>Sebelum cuplikan Laravel, rasakan ide satpam di PHP biasa:</p>
<pre><code class="language-php">&lt;?php

declare(strict_types=1);

$input = [
    'judul' =&gt; '',
    'penulis' =&gt; 'Ayu',
];

$errors = [];
if (trim((string) ($input['judul'] ?? '')) === '') {
    $errors['judul'] = 'Judul wajib diisi';
}
if (trim((string) ($input['penulis'] ?? '')) === '') {
    $errors['penulis'] = 'Penulis wajib diisi';
}

if ($errors !== []) {
    echo "Status awam: 422 (data belum lolos cek)", PHP_EOL;
    echo json_encode(['message' =&gt; 'Validasi gagal', 'errors' =&gt; $errors], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), PHP_EOL;
} else {
    echo "Status awam: OK — slip bersih", PHP_EOL;
}
</code></pre>
<p><strong>Awam:</strong> array <code>$errors</code> = catatan satpam. Kalau ada isi, tolak dulu. Ide yang sama dipakai Laravel lewat <code>validate</code> / Form Request.</p>

<h2>Validasi cepat di route</h2>
<p>Di <code>routes/web.php</code>, tambahkan route POST (cuplikan — tempel di bawah route GET kamu):</p>
<pre><code class="language-php">// Cuplikan routes/web.php — POST tambah buku (validasi di route)
use Illuminate\Http\Request;

Route::post('/api/buku', function (Request $request) {
    $data = $request-&gt;validate([
        'judul' =&gt; ['required', 'string', 'max:120'],
        'penulis' =&gt; ['required', 'string', 'max:80'],
    ]);

    return response()-&gt;json([
        'message' =&gt; 'Slip bersih — buku siap diproses (belum disimpan ke tabel)',
        'data' =&gt; $data,
    ], 201);
});
</code></pre>
<p><strong>Awam:</strong> <code>required</code> = wajib diisi. <code>max:120</code> = maksimal 120 huruf/angka (supaya judul tidak kepanjangan). Kalau gagal, Laravel otomatis menjawab JSON error (sering status <strong>422</strong>). Angka <strong>201</strong> = “data baru diterima” (belum berarti sudah di rak database).</p>

<h2>Form Request — aturan di file sendiri</h2>
<p>Kalau aturan makin panjang, pindahkan ke file khusus supaya route tidak penuh:</p>
<pre><code class="language-bash">php artisan make:request StoreBukuRequest</code></pre>
<p>Buka file yang dibuat (biasanya di <code>app/Http/Requests/StoreBukuRequest.php</code>). Di dalam kelas itu sudah ada kerangka fungsi — ganti isi <code>authorize</code> dan <code>rules</code> menjadi seperti cuplikan berikut:</p>
<pre><code class="language-php">// Cuplikan StoreBukuRequest — aturan satpam
public function authorize(): bool
{
    return true; // belum login — semua boleh coba kirim (untuk belajar)
}

public function rules(): array
{
    return [
        'judul' =&gt; ['required', 'string', 'max:120'],
        'penulis' =&gt; ['required', 'string', 'max:80'],
    ];
}
</code></pre>
<p>Lalu di route, minta Laravel memakai satpam itu:</p>
<pre><code class="language-php">// Cuplikan routes/web.php — pakai Form Request
use App\Http\Requests\StoreBukuRequest;

Route::post('/api/buku', function (StoreBukuRequest $request) {
    $data = $request-&gt;validated();

    return response()-&gt;json([
        'message' =&gt; 'Slip bersih lewat Form Request',
        'data' =&gt; $data,
    ], 201);
});
</code></pre>
<p><strong>Awam:</strong> <code>validated()</code> = ambil hanya field yang sudah lolos cek. <code>authorize(): true</code> di sini berarti “belum ada kartu anggota” — login dibahas belakangan. Artisan <code>make:request</code> sudah ada di proyek Laravel-mu (fondasi #56/#57) — tidak perlu unduh paket baru.</p>

<h2>Uji gagal &amp; sukses</h2>
<p>Dengan <code>php artisan serve</code> hidup, uji dari terminal (contoh <code>curl</code>):</p>
<pre><code class="language-bash"># Sengaja kosongkan judul — harus ditolak (422)
curl -s -X POST http://127.0.0.1:8000/api/buku \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{\"judul\":\"\",\"penulis\":\"Ayu\"}"

# Slip bersih — harus 201 + JSON data
curl -s -X POST http://127.0.0.1:8000/api/buku \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{\"judul\":\"Belajar Laravel\",\"penulis\":\"Budi\"}"
</code></pre>
<p><strong>Awam:</strong> baris <code>Accept: application/json</code> meminta jawaban berbentuk JSON (bukan halaman HTML error). Kalau Windows/PowerShell ribet dengan tanda kutip, boleh pakai alat uji API berjendela nanti — ide utamanya: kirim JSON, baca OK atau 422.</p>

<h2>Pola Dasar — empat langkah satpam bersih</h2>
<figure role="img" aria-label="Pola Dasar empat langkah Request Form Request" style="background:#F5F5F0;border:2px solid #1a1a1a;border-radius:12px;padding:1rem;margin:1.25rem 0">
<ol style="list-style:none;padding:0;margin:0;display:grid;gap:.75rem">
  <li style="display:flex;gap:.75rem;align-items:flex-start">
    <span style="flex:0 0 2rem;height:2rem;border-radius:999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div><strong style="color:#1a1a1a">Nyalakan serve</strong><br><span style="color:#1a1a1a"><code>php artisan serve</code> + pintu GET dari artikel routing JSON sebelumnya masih ada.</span></div>
  </li>
  <li style="display:flex;gap:.75rem;align-items:flex-start">
    <span style="flex:0 0 2rem;height:2rem;border-radius:999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div><strong style="color:#1a1a1a">Cek di route dulu</strong><br><span style="color:#1a1a1a"><code>POST /api/buku</code> + <code>$request-&gt;validate</code> untuk judul &amp; penulis.</span></div>
  </li>
  <li style="display:flex;gap:.75rem;align-items:flex-start">
    <span style="flex:0 0 2rem;height:2rem;border-radius:999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div><strong style="color:#1a1a1a">Pindah ke Form Request</strong><br><span style="color:#1a1a1a"><code>php artisan make:request StoreBukuRequest</code> lalu <code>validated()</code>.</span></div>
  </li>
  <li style="display:flex;gap:.75rem;align-items:flex-start">
    <span style="flex:0 0 2rem;height:2rem;border-radius:999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div><strong style="color:#1a1a1a">Uji 422 &amp; 201</strong><br><span style="color:#1a1a1a">Kirim judul kosong (ditolak) lalu slip bersih (diterima).</span></div>
  </li>
</ol>
</figure>

<h2>Demo satpam — file mandiri</h2>
<p>Simpan sebagai <code>laravel_request_validasi_api_demo.php</code>, lalu jalankan <code>php laravel_request_validasi_api_demo.php</code>. File ini mensimulasikan cek slip — tidak mengubah proyek Laravel-mu:</p>
<pre><code class="language-php">&lt;?php

declare(strict_types=1);

/**
 * Demo satpam Request/validasi — simulasi teks untuk awam.
 * File: laravel_request_validasi_api_demo.php
 */

function cekSlip(array $input): array
{
    $errors = [];
    if (trim((string) ($input['judul'] ?? '')) === '') {
        $errors['judul'] = 'Judul wajib diisi';
    }
    if (trim((string) ($input['penulis'] ?? '')) === '') {
        $errors['penulis'] = 'Penulis wajib diisi';
    }

    return $errors;
}

function demo(): void
{
    $kasus = [
        ['judul' =&gt; '', 'penulis' =&gt; 'Ayu'],
        ['judul' =&gt; 'Dasar Laravel', 'penulis' =&gt; 'Budi'],
    ];

    foreach ($kasus as $i =&gt; $input) {
        $n = $i + 1;
        $errors = cekSlip($input);
        echo "=== Kasus {$n} ===", PHP_EOL;
        if ($errors !== []) {
            echo "Hasil: 422 — data belum lolos cek", PHP_EOL;
            echo json_encode(['errors' =&gt; $errors], JSON_UNESCAPED_UNICODE), PHP_EOL;
        } else {
            echo "Hasil: OK — slip bersih", PHP_EOL;
            echo json_encode(['data' =&gt; $input], JSON_UNESCAPED_UNICODE), PHP_EOL;
        }
        echo PHP_EOL;
    }
}

demo();
</code></pre>
<p><strong>Awam:</strong> <code>demo()</code> hanya menampilkan dua kasus di terminal. Setelah paham, kerjakan langkah yang sama di <code>routes/web.php</code> / Form Request. <code>declare(strict_types=1);</code> membuat tipe lebih ketat — boleh diikuti, tidak wajib dihafal.</p>

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
      <td>HTML error page, bukan JSON</td>
      <td>Lupa baris <code>Accept: application/json</code></td>
      <td>Tambah Accept saat uji POST</td>
    </tr>
    <tr>
      <td>405 Method Not Allowed</td>
      <td>Masih memakai GET untuk kirim data</td>
      <td>Pakai POST ke <code>/api/buku</code></td>
    </tr>
    <tr>
      <td>Class StoreBukuRequest not found</td>
      <td>File belum dibuat / nama kelas atau folder salah</td>
      <td>Jalankan ulang <code>make:request</code>, cek folder <code>app/Http/Requests</code></td>
    </tr>
    <tr>
      <td>Selalu 422 meski sudah isi</td>
      <td>Nama field beda (<code>title</code> vs <code>judul</code>)</td>
      <td>Samakan kunci JSON dengan aturan <code>rules()</code></td>
    </tr>
  </tbody>
</table>

<h2>Latihan</h2>
<ol>
  <li>Jalankan demo PHP di atas — pastikan kasus judul kosong ditolak.</li>
  <li>Buat <code>StoreBukuRequest</code>, hubungkan ke <code>POST /api/buku</code>, uji 422 lalu 201.</li>
  <li>Jelaskan ke teman: beda singkat “pintu (route)” dan “satpam (validasi/Form Request)” dengan bahasa toko.</li>
</ol>

<h2>FAQ</h2>
<p><strong>Harus Form Request dari hari pertama?</strong><br>
Tidak. Boleh mulai dari <code>$request-&gt;validate</code> di route. Form Request berguna saat aturan makin panjang atau dipakai di banyak tempat.</p>
<p><strong>Kenapa belum disimpan ke database?</strong><br>
Supaya fokus satu hal: menjaga input. Menyimpan rapi (pengatur kode + tabel) datang setelah fondasi satpam nyaman.</p>
<p><strong>Apa hubungan dengan routing?</strong><br>
<a href="/artikel/laravel-routing-json-perpustakaan-api">Routing &amp; Jawaban JSON API Perpustakaan (#58)</a> memasang pintu. <strong>#59 (ini)</strong> memasang satpam di pintu itu.</p>
<p><strong>Ke mana setelah ini?</strong><br>
Berikutnya kamu akan merapikan alur: file pengatur kode (sering disebut controller), layanan kecil, dan menyimpan ke tabel database — supaya slip bersih benar-benar masuk rak.</p>

<h2>Kesimpulan</h2>
<p>Kamu sudah menjaga input API di Laravel: memahami Request, memakai validasi, mengenal Form Request, dan membedakan jawaban OK vs 422. Ini langkah <strong>4/8</strong> jalur Laravel di Seri 4.</p>
<blockquote>
  <p><strong>Seri 4 progress:</strong> langkah <strong>#59 (ini)</strong> · <strong>4/8</strong> jalur Laravel · prasyarat: <a href="/artikel/laravel-routing-json-perpustakaan-api">Routing &amp; Jawaban JSON API Perpustakaan (#58)</a> LIVE. Berikutnya: merapikan file pengatur, layanan, dan penyimpanan tabel untuk API perpustakaan.</p>
</blockquote>
HTML;
    }
}
