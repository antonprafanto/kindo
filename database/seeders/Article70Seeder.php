<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article70Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $webCat = Category::where('slug', 'web-development')->first()
            ?? Category::where('slug', 'programming')->first();

        if (! $admin || ! $webCat) {
            throw new \RuntimeException('User atau kategori web-development/programming tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'capstone-pinjam-kembali-laravel';

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
                'title'              => 'Capstone: Pinjam & Kembalikan',
                'title_en'           => 'Capstone: Borrow & Return',
                'excerpt'            => 'Seri 5 #70 capstone: satukan pinjam, daftar, kembalikan — alur-cek PHP, demo LOLOS/GAGAL, cuplikan Laravel Policy/Resource/Test/throttle, curl.exe Windows.',
                'excerpt_en'         => 'Seri 5 #70 capstone: unite borrow, list, and return — plain PHP flow check, pass/fail demo, Laravel Policy/Resource/Test/throttle snippets, Windows curl.exe.',
                'body'               => $this->body(),
                'body_en'            => $this->bodyEn(),
                'status'             => 'published',
                'is_featured'        => false,
                'seo_title'          => 'Capstone Pinjam & Kembalikan — Satukan Alur API Perpustakaan',
                'seo_title_en'       => 'Borrow & Return Capstone — Unite the Library API Flow',
                'seo_description'    => 'Seri 5 #70 capstone: satukan pinjam, daftar, kembalikan — alur-cek PHP, demo LOLOS/GAGAL, cuplikan Laravel Policy/Resource/Test/throttle, curl.exe.',
                'seo_description_en' => 'Seri 5 #70 capstone: unite borrow, list, return — PHP flow check, pass/fail demo, Laravel Policy/Resource/Test/throttle snippets, curl.exe.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        // published_at setelah #69 supaya urutan "Terbaru" di /artikel tidak menjatuhkan #70 ke tengah daftar
        $prevPublished = Article::where('slug', 'laravel-rate-limiting-api')->value('published_at');
        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        } elseif ($prevPublished && $article->published_at <= $prevPublished) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['laravel', 'php', 'api', 'http', 'web', 'eloquent', 'database'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel ke-70 berhasil dipublish: '.$article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — kenapa capstone setelah enam artikel sebelumnya</h2>
<p>Artikel ini adalah <strong>#70 (ini)</strong> di <strong>Seri 5: Laravel Lanjutan</strong> — langkah penutup seri. Setelah <a href="/artikel/laravel-rate-limiting-api">Rate Limiting API (#69)</a> melindungi API pinjam dari spam, saatnya <strong>menyatukan semua potongan</strong> ke satu alur utuh: anggota pinjam buku, lihat daftar pinjam, lalu kembalikan.</p>
<p>Bayangkan petugas perpustakaan yang menyelesaikan satu shift lengkap — dari slip pinjam baru sampai buku kembali ke rak. Capstone ini menggabungkan fondasi dari <a href="/artikel/laravel-eloquent-relasi-peminjaman">Relasi Eloquent: Anggota &amp; Peminjaman (#64)</a>, <a href="/artikel/laravel-pagination-filter-pencarian">Pagination, Filter &amp; Pencarian (#65)</a>, <a href="/artikel/laravel-policy-otorisasi-api">Authorization Policy: Siapa Boleh Ubah (#66)</a>, <a href="/artikel/laravel-api-resource-json">API Resource: Rapikan Bentuk JSON (#67)</a>, <a href="/artikel/laravel-feature-test-api">Feature Test API (#68)</a>, dan <a href="/artikel/laravel-rate-limiting-api">Rate Limiting API (#69)</a>.</p>
<p><strong>Awam:</strong> enam artikel sebelumnya seperti belajar potongan puzzle. Hari ini kamu merakit puzzle itu jadi gambar utuh — alur pinjam, daftar, kembalikan — tanpa memulai dari nol.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> sudah selesai <a href="/artikel/laravel-rate-limiting-api">Rate Limiting API (#69)</a> dan fondasi <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a> / <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a>. CRUD buku dari <a href="/artikel/laravel-crud-api-buku-ubah-hapus">CRUD API Buku: Ubah &amp; Hapus (#63)</a> diasumsikan sudah ada. Pakai <strong>Laravel 13+</strong> — butuh <strong>PHP 8.3+</strong>.</p>
</blockquote>

<h2>Spesifikasi fitur — pinjam, daftar, kembalikan</h2>
<p>Tiga gerakan yang harus jalan berurutan:</p>
<ol>
  <li><strong>Pinjam</strong> — anggota meminjam buku; status berubah dari tersedia ke dipinjam.</li>
  <li><strong>Daftar</strong> — petugas melihat daftar pinjam aktif (dengan pagination dan filter dari artikel sebelumnya).</li>
  <li><strong>Kembalikan</strong> — buku dikembalikan; status kembali tersedia.</li>
</ol>
<p><strong>Awam:</strong> selesai artikel ini, kamu punya peta alur lengkap perpustakaan mini — bukan lagi potongan terpisah. Cuplikan Laravel di bawah merujuk ke Policy, Resource, Feature Test, dan throttle yang sudah dipelajari; fokus capstone adalah <strong>urutan yang benar</strong>.</p>

<h2>Istilah — ringkas untuk capstone</h2>
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
      <td>Proyek penutup yang menyatukan banyak pelajaran</td>
      <td>Langkah penutup Seri 5</td>
    </tr>
    <tr>
      <td><code>store</code></td>
      <td>Method controller untuk mencatat pinjam baru</td>
      <td>POST pinjam</td>
    </tr>
    <tr>
      <td><code>index</code></td>
      <td>Method controller untuk menampilkan daftar</td>
      <td>GET daftar pinjam</td>
    </tr>
    <tr>
      <td><code>return</code></td>
      <td>Method controller untuk mengembalikan buku</td>
      <td>POST atau PATCH kembalikan</td>
    </tr>
    <tr>
      <td>Status pinjam</td>
      <td>Keadaan buku: tersedia, dipinjam, dikembalikan</td>
      <td>Inti state machine PHP di demo</td>
    </tr>
    <tr>
      <td>Alur utuh</td>
      <td>Rangkaian pinjam -&gt; daftar -&gt; kembalikan tanpa lompatan</td>
      <td>Capstone menguji urutan ini</td>
    </tr>
  </tbody>
</table>
<p>Urutan belajar kita: <strong>cek alur PHP dulu -&gt; demo pass/fail -&gt; baru cuplikan controller Laravel</strong>. Kalau loncat langsung ke controller tanpa paham status pinjam, kembalikan sering ditulis di waktu yang salah.</p>

<h2>Persiapan — alat yang kamu buka</h2>
<p><strong>Alat yang dipakai di artikel ini</strong> (fondasi dari <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a> dan <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a> — tidak ada unduhan Composer baru hari ini):</p>
<ul>
  <li><strong>Explorer</strong> — cek folder proyek <code>perpustakaan-api</code>, lalu lihat <code>app\Http\Controllers</code>, <code>app\Models</code>, <code>routes\api.php</code>, dan <code>tests\Feature</code> untuk cuplikan capstone.</li>
  <li><strong>Terminal</strong> — Laragon: menu <em>Terminal</em> · XAMPP: tombol <em>Shell</em>. Hindari CMD/PowerShell dari Start Menu kalau PATH PHP-mu belum rapi.</li>
  <li><strong>Editor teks</strong> — Notepad / VS Code — untuk membuka controller atau file demo. Contoh: <code>notepad app\Http\Controllers\PeminjamanController.php</code>.</li>
  <li><strong>Browser</strong> — opsional. Inti uji hari ini ada di terminal; browser berguna kalau kamu sudah menjalankan <code>php artisan serve</code> dan ingin bandingkan dengan <code>curl.exe</code>.</li>
</ul>
<p><strong>Awam:</strong> untuk artikel ini <strong>satu terminal sebenarnya cukup</strong> — jalankan <code>php capstone_pinjam_kembali_laravel_demo.php</code> di folder proyek. Kalau <code>php artisan serve</code> dari artikel sebelumnya masih hidup, pakai <strong>terminal kedua</strong> untuk perintah <code>curl.exe</code> pinjam lalu kembalikan. Kalau butuh jendela kedua: Laragon — klik menu <em>Terminal</em> lagi · XAMPP — klik tombol <em>Shell</em> lagi, lalu <code>cd</code> ke folder proyek yang sama.</p>
<p>Buka terminal Laragon/Shell XAMPP, masuk ke folder proyek:</p>
<pre><code class="language-bash">cd C:\laragon\www\perpustakaan-api
</code></pre>
<p>Di XAMPP biasanya: <code>cd C:\xampp\htdocs\perpustakaan-api</code>. Sesuaikan kalau foldermu beda.</p>
<p><strong>Install-dari-nol:</strong> kalau <code>php</code> atau <code>composer</code> belum dikenali terminal, kembali dulu ke <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a>. Kalau struktur folder proyek masih membingungkan, ulangi <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a>.</p>

<h2>Kenapa PHP biasa dulu?</h2>
<p>Kalau langsung loncat ke controller Laravel capstone, pemula sering bingung: kapan status boleh berubah? Maka kita mulai dari variabel status sederhana — supaya perbedaan <strong>pinjam lolos vs status salah gagal</strong> terlihat jelas sebelum dibungkus Eloquent dan Policy.</p>

<pre><code class="language-php">&lt;?php
// Mini: status buku sebelum dan sesudah pinjam.
$status = 'tersedia';
echo $status === 'tersedia' ? "LOLOS" : "GAGAL", PHP_EOL;
$status = 'dipinjam';
echo $status === 'tersedia' ? "LOLOS" : "GAGAL", PHP_EOL;
</code></pre>
<p><strong>Awam — cara menguji bagian ini:</strong> salin potongan di atas ke file misalnya <code>status-cek.php</code>, lalu di terminal Laragon/XAMPP jalankan <code>php status-cek.php</code>. Kalau muncul <code>LOLOS</code> lalu <code>GAGAL</code>, ide &ldquo;status harus cocok sebelum aksi&rdquo; sudah terlihat.</p>

<h2>Alur pinjam–kembali — langkah demi langkah</h2>
<p>Gerakan petugas perpustakaan yang benar selalu sama:</p>
<ol>
  <li><strong>Pinjam</strong> — cek buku tersedia, catat slip, ubah status ke dipinjam.</li>
  <li><strong>Daftar</strong> — tampilkan slip aktif (nanti pakai pagination dari <a href="/artikel/laravel-pagination-filter-pencarian">Pagination, Filter &amp; Pencarian (#65)</a>).</li>
  <li><strong>Kembalikan</strong> — cek status dipinjam, ubah ke dikembalikan atau tersedia.</li>
  <li><strong>Tolak salah urutan</strong> — kembalikan buku yang belum dipinjam harus gagal.</li>
</ol>

<pre><code class="language-php">&lt;?php
// Salin ke file misalnya alur-cek.php lalu jalankan: php alur-cek.php
$status = 'kosong';

function jalankanLangkah(string $aksi, string $statusSaatIni): array
{
    if ($aksi === 'pinjam' &amp;&amp; $statusSaatIni === 'kosong') {
        return ['lolos' =&gt; true, 'statusBaru' =&gt; 'dipinjam', 'pesan' =&gt; 'pinjam ok'];
    }
    if ($aksi === 'daftar') {
        return ['lolos' =&gt; true, 'statusBaru' =&gt; $statusSaatIni, 'pesan' =&gt; 'daftar ok'];
    }
    if ($aksi === 'kembalikan' &amp;&amp; $statusSaatIni === 'dipinjam') {
        return ['lolos' =&gt; true, 'statusBaru' =&gt; 'dikembalikan', 'pesan' =&gt; 'kembali ok'];
    }
    return ['lolos' =&gt; false, 'statusBaru' =&gt; $statusSaatIni, 'pesan' =&gt; 'status salah'];
}

$hasil = jalankanLangkah('pinjam', $status);
$status = $hasil['statusBaru'];
echo $hasil['lolos'] ? 'CEK LOLOS' : 'CEK GAGAL', ' — ', $hasil['pesan'], PHP_EOL;

$hasil = jalankanLangkah('daftar', $status);
echo $hasil['lolos'] ? 'CEK LOLOS' : 'CEK GAGAL', ' — ', $hasil['pesan'], PHP_EOL;

$hasil = jalankanLangkah('kembalikan', $status);
$status = $hasil['statusBaru'];
echo $hasil['lolos'] ? 'CEK LOLOS' : 'CEK GAGAL', ' — ', $hasil['pesan'], PHP_EOL;

$hasil = jalankanLangkah('kembalikan', $status);
echo $hasil['lolos'] ? 'CEK LOLOS' : 'CEK GAGAL', ' — ', $hasil['pesan'], PHP_EOL;
</code></pre>
<p><strong>Awam — cara menguji bagian ini:</strong> salin potongan di atas ke <code>alur-cek.php</code>, lalu di terminal jalankan <code>php alur-cek.php</code>. Harusnya tiga baris <code>CEK LOLOS</code> lalu satu <code>CEK GAGAL — status salah</code>. Ini versi PHP murni dari alur capstone sebelum cuplikan controller Laravel.</p>

<figure role="img" aria-label="Diagram alur capstone pinjam daftar kembalikan perpustakaan" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" style="display:block;max-width:760px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif" viewBox="0 0 760 260">
  <defs>
    <marker id="laravel70capArrow" orient="auto" markerWidth="8" markerHeight="8" refX="7" refY="4" viewBox="0 0 8 8">
      <path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/>
    </marker>
  </defs>
  <rect width="760" height="260" fill="#F5F5F0"/>
  <text x="24" y="36" fill="#1a1a1a" font-size="16" font-weight="700">Pinjam -&gt; Daftar -&gt; Kembalikan (capstone Seri 5)</text>
  <rect x="24" y="70" width="140" height="80" rx="8" fill="#ffffff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="94" y="105" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Pinjam</text>
  <text x="94" y="128" text-anchor="middle" fill="#1a1a1a" font-size="12">POST store</text>
  <line x1="164" y1="110" x2="214" y2="110" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel70capArrow)"/>
  <rect x="218" y="70" width="140" height="80" rx="8" fill="#1a1a1a" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="288" y="105" text-anchor="middle" fill="#ffffff" font-size="15" font-weight="700">Daftar</text>
  <text x="288" y="128" text-anchor="middle" fill="#ffffff" font-size="12">GET index</text>
  <line x1="358" y1="110" x2="408" y2="110" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel70capArrow)"/>
  <rect x="412" y="70" width="140" height="80" rx="8" fill="#ffffff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="482" y="105" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Kembalikan</text>
  <text x="482" y="128" text-anchor="middle" fill="#1a1a1a" font-size="12">POST return</text>
  <line x1="552" y1="110" x2="602" y2="110" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel70capArrow)"/>
  <rect x="606" y="70" width="130" height="80" rx="8" fill="#ffffff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="671" y="105" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Selesai</text>
  <text x="671" y="128" text-anchor="middle" fill="#1a1a1a" font-size="12">status ok</text>
  <text x="24" y="190" fill="#1a1a1a" font-size="13">Policy, Resource, Feature Test, dan throttle melindungi tiap langkah.</text>
  <text x="24" y="220" fill="#1a1a1a" font-size="13">Capstone menyatukan potongan Relasi sampai Rate Limiting ke satu alur petugas.</text>
</svg>
<figcaption><strong>#70 (ini)</strong> menutup Seri 5 dengan alur pinjam–daftar–kembalikan yang menggabungkan pelajaran dari <a href="/artikel/laravel-eloquent-relasi-peminjaman">Relasi Eloquent (#64)</a> sampai <a href="/artikel/laravel-rate-limiting-api">Rate Limiting (#69)</a>.</figcaption>
</figure>

<h2>Laravel — cuplikan capstone (bukan file mandiri)</h2>
<p>Di proyek Laravel, tiga method controller ini melengkapi alur. Masing-masing merujuk ke artikel sebelumnya — tempel ke proyek kalau fondasi Relasi Eloquent sampai Rate Limiting sudah ada.</p>

<pre><code class="language-php">&lt;?php
// Cuplikan Laravel (bukan file mandiri)
// app/Http/Controllers/PeminjamanController.php — store (pinjam)

public function store(Request $request)
{
    $this-&gt;authorize('create', Peminjaman::class); // Policy otorisasi
    $pinjam = Peminjaman::create($request-&gt;validated());
    return new PeminjamanResource($pinjam); // API Resource
}
</code></pre>

<pre><code class="language-php">&lt;?php
// Cuplikan Laravel (bukan file mandiri)
// app/Http/Controllers/PeminjamanController.php — index (daftar)

public function index(Request $request)
{
    $query = Peminjaman::with(['anggota', 'buku'])-&gt;where('status', 'dipinjam');
    // pagination + filter dari artikel Pagination
    return PeminjamanResource::collection($query-&gt;paginate(10));
}
</code></pre>

<pre><code class="language-php">&lt;?php
// Cuplikan Laravel (bukan file mandiri)
// app/Http/Controllers/PeminjamanController.php — returnBook (kembalikan)

public function returnBook(Request $request, int $id)
{
    $pinjam = Peminjaman::findOrFail($id);
    $this-&gt;authorize('update', $pinjam); // Policy otorisasi
    $pinjam-&gt;update(['status' =&gt; 'dikembalikan', 'dikembalikan_at' =&gt; now()]);
    return new PeminjamanResource($pinjam);
}
</code></pre>

<p>Pasang rute di <code>routes\api.php</code> — POST pinjam pakai <code>throttle:pinjam</code> dari <a href="/artikel/laravel-rate-limiting-api">Rate Limiting API (#69)</a>:</p>
<pre><code class="language-php">&lt;?php
// Cuplikan Laravel (bukan file mandiri)
// routes/api.php

Route::middleware('throttle:pinjam')-&gt;post('/api/pinjam', [PeminjamanController::class, 'store']);
Route::get('/api/pinjam', [PeminjamanController::class, 'index']);
Route::post('/api/pinjam/{id}/kembalikan', [PeminjamanController::class, 'returnBook']);
</code></pre>
<p>Feature Test dari <a href="/artikel/laravel-feature-test-api">Feature Test API (#68)</a> bisa memastikan alur: <code>$response-&gt;assertJsonStructure(['data' =&gt; ['id', 'status']])</code> setelah pinjam, lalu assert status berubah setelah kembalikan.</p>
<p><strong>Awam:</strong> cuplikan ini <strong>bukan file mandiri</strong> — gabungkan ke controller dan rute yang sudah ada. Relasi Eloquent dari <a href="/artikel/laravel-eloquent-relasi-peminjaman">Relasi Eloquent (#64)</a> membuat <code>with(['anggota', 'buku'])</code> bisa membaca nama tanpa query manual.</p>
<p>Uji dengan <code>curl.exe</code> di terminal (kalau <code>php artisan serve</code> sudah jalan):</p>
<pre><code class="language-bash">curl.exe -X POST "http://127.0.0.1:8000/api/pinjam" -H "Content-Type: application/json" -d "{\"buku_id\":1,\"anggota_id\":1}"
curl.exe -X POST "http://127.0.0.1:8000/api/pinjam/1/kembalikan" -H "Content-Type: application/json"
</code></pre>
<p><strong>Awam:</strong> jalankan perintah pinjam dulu, lalu kembalikan. Kalau muncul <code>404</code>, rute mungkin belum dipasang — itu wajar; fokus dulu ke demo PHP di atas. Kalau <code>php artisan serve</code> belum jalan, cukup uji demo PHP; terminal kedua hanya untuk uji <code>curl.exe</code>.</p>

<h2>Pola Dasar — satu alur utuh petugas perpustakaan</h2>
<figure style="margin:1.5rem 0;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1.25rem;color:#1a1a1a" aria-label="Enam langkah capstone pinjam daftar kembalikan">
<ol style="list-style:none;padding:0;margin:0;color:#1a1a1a">
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div style="color:#1a1a1a"><strong style="color:#1a1a1a">Cek status buku</strong><br><span style="color:#1a1a1a">Hanya buku tersedia yang boleh dipinjam — mirip <code>alur-cek.php</code>.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div style="color:#1a1a1a"><strong style="color:#1a1a1a">Catat pinjam (store)</strong><br><span style="color:#1a1a1a">Policy izinkan, Resource rapikan JSON — fondasi otorisasi dan Resource.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div style="color:#1a1a1a"><strong style="color:#1a1a1a">Tampilkan daftar (index)</strong><br><span style="color:#1a1a1a">Pagination dan filter dari <a href="/artikel/laravel-pagination-filter-pencarian">Pagination (#65)</a>.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div style="color:#1a1a1a"><strong style="color:#1a1a1a">Kembalikan buku (return)</strong><br><span style="color:#1a1a1a">Ubah status; tolak kalau belum pernah dipinjam.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">5</span>
    <div style="color:#1a1a1a"><strong style="color:#1a1a1a">Kunci dengan Feature Test</strong><br><span style="color:#1a1a1a"><code>assertJsonStructure</code> dari <a href="/artikel/laravel-feature-test-api">Feature Test (#68)</a>.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">6</span>
    <div style="color:#1a1a1a"><strong style="color:#1a1a1a">Lindungi POST pinjam</strong><br><span style="color:#1a1a1a"><code>throttle:pinjam</code> dari <a href="/artikel/laravel-rate-limiting-api">Rate Limiting (#69)</a>.</span></div>
  </li>
</ol>
</figure>

<h2>Kode lengkap — demo mandiri</h2>
<p>Simpan sebagai <code>capstone_pinjam_kembali_laravel_demo.php</code>, lalu jalankan <code>php capstone_pinjam_kembali_laravel_demo.php</code>:</p>

<pre><code class="language-php">&lt;?php
declare(strict_types=1);

function pinjam(array $buku): array
{
    if ($buku['status'] !== 'tersedia') {
        return ['lolos' =&gt; false, 'pesan' =&gt; 'buku sudah dipinjam'];
    }
    $buku['status'] = 'dipinjam';
    return ['lolos' =&gt; true, 'pesan' =&gt; 'pinjam ok', 'buku' =&gt; $buku];
}

function kembalikan(array $buku): array
{
    if ($buku['status'] !== 'dipinjam') {
        return ['lolos' =&gt; false, 'pesan' =&gt; 'belum dipinjam'];
    }
    $buku['status'] = 'tersedia';
    return ['lolos' =&gt; true, 'pesan' =&gt; 'kembali ok', 'buku' =&gt; $buku];
}

function demo(string $judul, array $buku, string $mode): void
{
    echo "=== {$judul} ===", PHP_EOL;
    if ($mode === 'pinjam') {
        $hasil = pinjam($buku);
    } else {
        $hasil = kembalikan($buku);
    }
    echo $hasil['lolos'] ? 'LOLOS' : 'GAGAL', PHP_EOL;
    echo $hasil['pesan'], PHP_EOL, PHP_EOL;
}

$bukuTersedia = ['id' =&gt; 1, 'status' =&gt; 'tersedia'];
$bukuDipinjam = ['id' =&gt; 2, 'status' =&gt; 'dipinjam'];

demo('Pinjam lolos', $bukuTersedia, 'pinjam');
demo('Kembalikan lolos', $bukuDipinjam, 'kembalikan');
demo('Status salah gagal', ['id' =&gt; 3, 'status' =&gt; 'dipinjam'], 'pinjam');
</code></pre>
<p><strong>Awam — cara menguji bagian ini:</strong> simpan file sebagai <code>capstone_pinjam_kembali_laravel_demo.php</code> di folder proyek, lalu di terminal Laragon/XAMPP jalankan <code>php capstone_pinjam_kembali_laravel_demo.php</code>. Harusnya muncul dua <code>LOLOS</code> lalu satu <code>GAGAL</code>. Fungsi <code>pinjam</code> dan <code>kembalikan</code> adalah inti logika; <code>demo(...)</code> hanya membungkus output agar mudah dibaca di terminal.</p>

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
      <td>Kembalikan selalu gagal</td>
      <td>Status belum diubah saat pinjam</td>
      <td>Cek alur <code>alur-cek.php</code> — pinjam harus jalan dulu</td>
    </tr>
    <tr>
      <td>Daftar kosong padahal ada pinjam</td>
      <td>Filter <code>status</code> salah di <code>index</code></td>
      <td>Samakan string status dengan migration</td>
    </tr>
    <tr>
      <td>403 saat pinjam</td>
      <td>Policy belum mengizinkan <code>create</code></td>
      <td>Ulangi <a href="/artikel/laravel-policy-otorisasi-api">Authorization Policy (#66)</a></td>
    </tr>
    <tr>
      <td>JSON berantakan</td>
      <td>Resource belum dipasang</td>
      <td>Kembali ke <a href="/artikel/laravel-api-resource-json">API Resource (#67)</a></td>
    </tr>
    <tr>
      <td><code>curl.exe</code> selalu 404</td>
      <td>Rute belum dipasang atau <code>serve</code> belum jalan</td>
      <td>Fokus demo PHP dulu; 404 wajar kalau rute belum ada</td>
    </tr>
    <tr>
      <td>Test capstone gagal acak</td>
      <td>Database test tidak fresh</td>
      <td>Pakai <code>RefreshDatabase</code> seperti di <a href="/artikel/laravel-feature-test-api">Feature Test (#68)</a></td>
    </tr>
  </tbody>
</table>

<h2>Latihan singkat</h2>
<ol>
  <li>Ubah demo: tambah skenario kembalikan buku yang belum dipinjam — harus <code>GAGAL</code>.</li>
  <li>Jelaskan ke teman: urutan pinjam -&gt; daftar -&gt; kembalikan dengan analogi petugas perpustakaan.</li>
  <li>Tulis satu kalimat: artikel Relasi Eloquent sampai Rate Limiting mana yang melindungi tiap langkah capstone.</li>
</ol>

<h2>FAQ singkat</h2>
<p><strong>Apakah capstone menggantikan artikel Relasi sampai Rate Limiting?</strong><br>
Tidak. Capstone <strong>menyatukan</strong> potongan yang sudah dipelajari. Kalau relasi atau Policy belum paham, kembali ke artikel masing-masing — jangan loncat.</p>
<p><strong>Haruskah semua cuplikan Laravel langsung jalan?</strong><br>
Tidak wajib hari ini. Demo PHP membuktikan logika status; cuplikan controller ditempel bertahap ke proyek yang sudah punya fondasi instalasi sampai rate limiting.</p>
<p><strong>Tool apa yang dibuka dulu?</strong><br>
Explorer untuk <code>Controllers</code>, <code>Models</code>, <code>routes\api.php</code>, <code>tests\Feature</code>; satu terminal untuk demo PHP; editor untuk cuplikan. Kalau <code>serve</code> hidup, terminal kedua untuk <code>curl.exe</code> pinjam lalu kembalikan.</p>
<p><strong>Potongan sintaks diuji di mana?</strong><br>
Langkah tengah salin ke <code>alur-cek.php</code>, lalu <code>php alur-cek.php</code>. Demo lengkap: <code>php capstone_pinjam_kembali_laravel_demo.php</code>. Cuplikan Laravel ditempel ke controller dan <code>routes\api.php</code>; uji alur dengan <code>curl.exe</code>.</p>
<p><strong>Ke mana setelah Seri 5?</strong><br>
Seri 5 selesai. Berikutnya alami: <strong>Piranti Bergerak</strong> — jalur belajar berikutnya setelah Laravel Lanjutan.</p>

<h2>Kesimpulan</h2>
<p>Kamu menyelesaikan <strong>capstone pinjam &amp; kembalikan</strong>: cek alur di <code>alur-cek.php</code>, demo pass/fail di <code>capstone_pinjam_kembali_laravel_demo.php</code>, lalu cuplikan controller yang merujuk Policy, Resource, Feature Test, dan throttle dari enam artikel sebelumnya. Petugas perpustakaan digital — satu shift lengkap dari slip pinjam sampai buku kembali.</p>
<blockquote>
  <p><strong>Seri 5 progress:</strong> langkah <strong>#70 (ini)</strong> · <strong>7/7 — tamat</strong> Laravel Lanjutan · prasyarat: <a href="/artikel/laravel-rate-limiting-api">Rate Limiting API (#69)</a> LIVE. Seri 5: <strong>7/7 — tamat</strong>. Berikutnya alami: <strong>Piranti Bergerak</strong>.</p>
</blockquote>
HTML;
    }

    private function bodyEn(): string
    {
        $html = <<<'HTML'
<h2>Introduction — why a capstone after the six previous articles</h2>
<p>This article is <strong>#70 (this article)</strong> in <strong>Seri 5: Laravel Lanjutan</strong> — the closing step of the series. After <a href="/artikel/laravel-rate-limiting-api">Rate Limiting API (#69)</a> protects the borrowing API from spam, it is time to <strong>unite all pieces</strong> into one complete flow: a member borrows a book, views the borrowing list, then returns it.</p>
<p>Imagine a library clerk finishing one full shift — from a new borrowing slip to the book back on the shelf. This capstone combines foundations from <a href="/artikel/laravel-eloquent-relasi-peminjaman">Eloquent Relations: Members &amp; Borrowing (#64)</a>, <a href="/artikel/laravel-pagination-filter-pencarian">Pagination, Filter &amp; Search (#65)</a>, <a href="/artikel/laravel-policy-otorisasi-api">Authorization Policy: Who May Change (#66)</a>, <a href="/artikel/laravel-api-resource-json">API Resource: Tidy JSON Shape (#67)</a>, <a href="/artikel/laravel-feature-test-api">Feature Test API (#68)</a>, and <a href="/artikel/laravel-rate-limiting-api">Rate Limiting API (#69)</a>.</p>
<p><strong>Beginner:</strong> the six previous articles are like learning puzzle pieces. Today you assemble them into one picture — borrow, list, return — without starting from scratch.</p>

<blockquote>
  <p><strong>Prerequisite:</strong> finish <a href="/artikel/laravel-rate-limiting-api">Rate Limiting API (#69)</a> and keep foundations from <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a> / <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a>. Book CRUD from <a href="/artikel/laravel-crud-api-buku-ubah-hapus">CRUD API Buku: Ubah &amp; Hapus (#63)</a> is assumed. Use <strong>Laravel 13+</strong> and <strong>PHP 8.3+</strong>.</p>
</blockquote>

<h2>Feature spec — borrow, list, return</h2>
<p>Three moves that must run in order:</p>
<ol>
  <li><strong>Borrow</strong> — a member borrows a book; status changes from available to borrowed.</li>
  <li><strong>List</strong> — the clerk views active borrowings (with pagination and filters from earlier articles).</li>
  <li><strong>Return</strong> — the book is returned; status becomes available again.</li>
</ol>
<p><strong>Beginner:</strong> after this article, you have a complete mini-library flow map — no longer separate pieces. Laravel snippets below refer to Policy, Resource, Feature Test, and throttle you already learned; the capstone focus is the <strong>correct order</strong>.</p>

<h2>Terms — a quick glossary for the capstone</h2>
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
      <td>Capstone</td>
      <td>Closing proyek that unites many lessons</td>
      <td>Closing step of Seri 5</td>
    </tr>
    <tr>
      <td><code>store</code></td>
      <td>Controller method to record a new borrowing</td>
      <td>POST borrow</td>
    </tr>
    <tr>
      <td><code>index</code></td>
      <td>Controller method to show a list</td>
      <td>GET borrowing list</td>
    </tr>
    <tr>
      <td><code>return</code></td>
      <td>Controller method to return a book</td>
      <td>POST or PATCH return</td>
    </tr>
    <tr>
      <td>Borrowing status</td>
      <td>Book state: available, borrowed, returned</td>
      <td>Core of the PHP state machine demo</td>
    </tr>
    <tr>
      <td>Complete flow</td>
      <td>Borrow -&gt; list -&gt; return without skipping steps</td>
      <td>Capstone tests this order</td>
    </tr>
  </tbody>
</table>
<p>Our learning order: <strong>plain PHP flow check first -&gt; pass/fail demo -&gt; then Laravel controller snippets</strong>. If you jump straight into controllers without understanding borrowing status, returns are often written at the wrong time.</p>

<h2>Preparation — tools to open</h2>
<p><strong>Tools used in this article</strong> (built on <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a> and <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a> — there is <strong>no new Composer download</strong> today):</p>
<ul>
  <li><strong>Explorer</strong> — check the <code>perpustakaan-api</code> proyek folder, then look at <code>app\Http\Controllers</code>, <code>app\Models</code>, <code>routes\api.php</code>, and <code>tests\Feature</code> for capstone snippets.</li>
  <li><strong>Terminal</strong> — Laragon: <em>Terminal</em> menu · XAMPP: <em>Shell</em> button. Avoid Start Menu CMD/PowerShell if your PHP PATH is still messy.</li>
  <li><strong>Text editor</strong> — Notepad / VS Code — to open controllers or demo files. Example: <code>notepad app\Http\Controllers\PeminjamanController.php</code>.</li>
  <li><strong>Browser</strong> — optional. The core test today is in the terminal; the browser helps if you already run <code>php artisan serve</code> and want to compare with <code>curl.exe</code>.</li>
</ul>
<p><strong>Beginner:</strong> for this article, <strong>one terminal is actually enough</strong> — run <code>php capstone_pinjam_kembali_laravel_demo.php</code> in the proyek folder. If <code>php artisan serve</code> from the previous article is still alive, use a <strong>second terminal</strong> for <code>curl.exe</code> borrow then return commands. To open a second window: Laragon — click the <em>Terminal</em> menu again · XAMPP — click the <em>Shell</em> button again, then <code>cd</code> to the same proyek folder.</p>
<p>Open Laragon Terminal / XAMPP Shell, then move into the proyek folder:</p>
<pre><code class="language-bash">cd C:\laragon\www\perpustakaan-api
</code></pre>
<p>In XAMPP it is usually: <code>cd C:\xampp\htdocs\perpustakaan-api</code>. Adjust the path if your folder is different.</p>
<p><strong>Install-from-scratch:</strong> if <code>php</code> or <code>composer</code> is not recognized in the terminal, return to <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a>. If your proyek folder structure is still confusing, review <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a> first.</p>

<h2>Why start with plain PHP first?</h2>
<p>If you jump straight into the Laravel capstone controller, beginners often wonder: when may status change? So we start from a simple status variable — so the difference between <strong>borrow pass vs wrong status fail</strong> is visible before wrapping it in Eloquent and Policy.</p>

<pre><code class="language-php">&lt;?php
// Mini: book status before and after borrowing.
$status = 'tersedia';
echo $status === 'tersedia' ? "PASS" : "FAIL", PHP_EOL;
$status = 'dipinjam';
echo $status === 'tersedia' ? "PASS" : "FAIL", PHP_EOL;
</code></pre>
<p><strong>Beginner — how to test this part:</strong> copy the snippet above into a file such as <code>status-cek.php</code>, then in Laragon/XAMPP terminal run <code>php status-cek.php</code>. If you see <code>PASS</code> then <code>FAIL</code>, the idea &ldquo;status must match before an action&rdquo; is already visible.</p>

<h2>Borrow–return flow — step by step</h2>
<p>The correct library clerk move order is always the same:</p>
<ol>
  <li><strong>Borrow</strong> — check book available, record slip, change status to borrowed.</li>
  <li><strong>List</strong> — show active slips (later using pagination from <a href="/artikel/laravel-pagination-filter-pencarian">Pagination, Filter &amp; Search (#65)</a>).</li>
  <li><strong>Return</strong> — check borrowed status, change to returned or available.</li>
  <li><strong>Reject wrong order</strong> — returning a book that was never borrowed must fail.</li>
</ol>

<pre><code class="language-php">&lt;?php
// Copy into a file such as alur-cek.php, then run: php alur-cek.php
$status = 'kosong';

function jalankanLangkah(string $aksi, string $statusSaatIni): array
{
    if ($aksi === 'pinjam' &amp;&amp; $statusSaatIni === 'kosong') {
        return ['lolos' =&gt; true, 'statusBaru' =&gt; 'dipinjam', 'pesan' =&gt; 'pinjam ok'];
    }
    if ($aksi === 'daftar') {
        return ['lolos' =&gt; true, 'statusBaru' =&gt; $statusSaatIni, 'pesan' =&gt; 'daftar ok'];
    }
    if ($aksi === 'kembalikan' &amp;&amp; $statusSaatIni === 'dipinjam') {
        return ['lolos' =&gt; true, 'statusBaru' =&gt; 'dikembalikan', 'pesan' =&gt; 'kembali ok'];
    }
    return ['lolos' =&gt; false, 'statusBaru' =&gt; $statusSaatIni, 'pesan' =&gt; 'status salah'];
}

$hasil = jalankanLangkah('pinjam', $status);
$status = $hasil['statusBaru'];
echo $hasil['lolos'] ? 'CHECK PASS' : 'CHECK FAIL', ' — ', $hasil['pesan'], PHP_EOL;

$hasil = jalankanLangkah('daftar', $status);
echo $hasil['lolos'] ? 'CHECK PASS' : 'CHECK FAIL', ' — ', $hasil['pesan'], PHP_EOL;

$hasil = jalankanLangkah('kembalikan', $status);
$status = $hasil['statusBaru'];
echo $hasil['lolos'] ? 'CHECK PASS' : 'CHECK FAIL', ' — ', $hasil['pesan'], PHP_EOL;

$hasil = jalankanLangkah('kembalikan', $status);
echo $hasil['lolos'] ? 'CHECK PASS' : 'CHECK FAIL', ' — ', $hasil['pesan'], PHP_EOL;
</code></pre>
<p><strong>Beginner — how to test this part:</strong> copy the snippet above into <code>alur-cek.php</code>, then run <code>php alur-cek.php</code> in the terminal. You should see three <code>CHECK PASS</code> lines then one <code>CHECK FAIL — status salah</code>. This is the plain PHP version of the capstone flow before Laravel controller snippets.</p>

<figure role="img" aria-label="Capstone borrow list return flow diagram for library" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" style="display:block;max-width:760px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif" viewBox="0 0 760 260">
  <defs>
    <marker id="laravel70capArrow" orient="auto" markerWidth="8" markerHeight="8" refX="7" refY="4" viewBox="0 0 8 8">
      <path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/>
    </marker>
  </defs>
  <rect width="760" height="260" fill="#F5F5F0"/>
  <text x="24" y="36" fill="#1a1a1a" font-size="16" font-weight="700">Borrow -&gt; List -&gt; Return (Seri 5 capstone)</text>
  <rect x="24" y="70" width="140" height="80" rx="8" fill="#ffffff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="94" y="105" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Borrow</text>
  <text x="94" y="128" text-anchor="middle" fill="#1a1a1a" font-size="12">POST store</text>
  <line x1="164" y1="110" x2="214" y2="110" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel70capArrow)"/>
  <rect x="218" y="70" width="140" height="80" rx="8" fill="#1a1a1a" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="288" y="105" text-anchor="middle" fill="#ffffff" font-size="15" font-weight="700">List</text>
  <text x="288" y="128" text-anchor="middle" fill="#ffffff" font-size="12">GET index</text>
  <line x1="358" y1="110" x2="408" y2="110" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel70capArrow)"/>
  <rect x="412" y="70" width="140" height="80" rx="8" fill="#ffffff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="482" y="105" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Return</text>
  <text x="482" y="128" text-anchor="middle" fill="#1a1a1a" font-size="12">POST return</text>
  <line x1="552" y1="110" x2="602" y2="110" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel70capArrow)"/>
  <rect x="606" y="70" width="130" height="80" rx="8" fill="#ffffff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="671" y="105" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Done</text>
  <text x="671" y="128" text-anchor="middle" fill="#1a1a1a" font-size="12">status ok</text>
  <text x="24" y="190" fill="#1a1a1a" font-size="13">Policy, Resource, Feature Test, and throttle protect each step.</text>
  <text x="24" y="220" fill="#1a1a1a" font-size="13">Capstone unites Eloquent Relations through Rate Limiting into one clerk flow.</text>
</svg>
<figcaption><strong>#70 (this article)</strong> closes Seri 5 with a borrow–list–return flow combining lessons from <a href="/artikel/laravel-eloquent-relasi-peminjaman">Eloquent Relations (#64)</a> through <a href="/artikel/laravel-rate-limiting-api">Rate Limiting (#69)</a>.</figcaption>
</figure>

<h2>Laravel capstone snippets (not standalone files)</h2>
<p>In the Laravel proyek, these three controller methods complete the flow. Each refers to earlier articles — paste into the proyek when Eloquent Relations through Rate Limiting foundations exist.</p>

<pre><code class="language-php">&lt;?php
// Cuplikan Laravel (bukan file mandiri)
// app/Http/Controllers/PeminjamanController.php — store (borrow)

public function store(Request $request)
{
    $this-&gt;authorize('create', Peminjaman::class); // Policy otorisasi
    $pinjam = Peminjaman::create($request-&gt;validated());
    return new PeminjamanResource($pinjam); // API Resource
}
</code></pre>

<pre><code class="language-php">&lt;?php
// Cuplikan Laravel (bukan file mandiri)
// app/Http/Controllers/PeminjamanController.php — index (list)

public function index(Request $request)
{
    $query = Peminjaman::with(['anggota', 'buku'])-&gt;where('status', 'dipinjam');
    // pagination + filter from Pagination article
    return PeminjamanResource::collection($query-&gt;paginate(10));
}
</code></pre>

<pre><code class="language-php">&lt;?php
// Cuplikan Laravel (bukan file mandiri)
// app/Http/Controllers/PeminjamanController.php — returnBook (return)

public function returnBook(Request $request, int $id)
{
    $pinjam = Peminjaman::findOrFail($id);
    $this-&gt;authorize('update', $pinjam); // Policy otorisasi
    $pinjam-&gt;update(['status' =&gt; 'dikembalikan', 'dikembalikan_at' =&gt; now()]);
    return new PeminjamanResource($pinjam);
}
</code></pre>

<p>Attach routes in <code>routes\api.php</code> — POST borrow uses <code>throttle:pinjam</code> from <a href="/artikel/laravel-rate-limiting-api">Rate Limiting API (#69)</a>:</p>
<pre><code class="language-php">&lt;?php
// Cuplikan Laravel (bukan file mandiri)
// routes/api.php

Route::middleware('throttle:pinjam')-&gt;post('/api/pinjam', [PeminjamanController::class, 'store']);
Route::get('/api/pinjam', [PeminjamanController::class, 'index']);
Route::post('/api/pinjam/{id}/kembalikan', [PeminjamanController::class, 'returnBook']);
</code></pre>
<p>Feature Test from <a href="/artikel/laravel-feature-test-api">Feature Test API (#68)</a> can lock the flow: <code>$response-&gt;assertJsonStructure(['data' =&gt; ['id', 'status']])</code> after borrow, then assert status changes after return.</p>
<p><strong>Beginner:</strong> these snippets are <strong>not standalone files</strong> — merge them into existing controllers and routes. Eloquent relations from <a href="/artikel/laravel-eloquent-relasi-peminjaman">Eloquent Relations (#64)</a> make <code>with(['anggota', 'buku'])</code> read names without manual queries.</p>
<p>Test with <code>curl.exe</code> in the terminal (if <code>php artisan serve</code> is running):</p>
<pre><code class="language-bash">curl.exe -X POST "http://127.0.0.1:8000/api/pinjam" -H "Content-Type: application/json" -d "{\"buku_id\":1,\"anggota_id\":1}"
curl.exe -X POST "http://127.0.0.1:8000/api/pinjam/1/kembalikan" -H "Content-Type: application/json"
</code></pre>
<p><strong>Beginner:</strong> run borrow first, then return. If you get <code>404</code>, the route may not be installed yet — that is normal; focus on the PHP demo above first. If <code>php artisan serve</code> is not running, the PHP demo is enough; a second terminal is only for <code>curl.exe</code> tests.</p>

<h2>Basic Pattern — one complete library clerk flow</h2>
<figure style="margin:1.5rem 0;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1.25rem;color:#1a1a1a" aria-label="Six capstone borrow list return steps">
<ol style="list-style:none;padding:0;margin:0;color:#1a1a1a">
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div style="color:#1a1a1a"><strong style="color:#1a1a1a">Check book status</strong><br><span style="color:#1a1a1a">Only available books may be borrowed — like <code>alur-cek.php</code>.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div style="color:#1a1a1a"><strong style="color:#1a1a1a">Record borrow (store)</strong><br><span style="color:#1a1a1a">Policy allows, Resource tidies JSON — authorization and Resource foundations.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div style="color:#1a1a1a"><strong style="color:#1a1a1a">Show list (index)</strong><br><span style="color:#1a1a1a">Pagination and filters from <a href="/artikel/laravel-pagination-filter-pencarian">Pagination (#65)</a>.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div style="color:#1a1a1a"><strong style="color:#1a1a1a">Return book (return)</strong><br><span style="color:#1a1a1a">Change status; reject if never borrowed.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">5</span>
    <div style="color:#1a1a1a"><strong style="color:#1a1a1a">Lock with Feature Test</strong><br><span style="color:#1a1a1a"><code>assertJsonStructure</code> from <a href="/artikel/laravel-feature-test-api">Feature Test (#68)</a>.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">6</span>
    <div style="color:#1a1a1a"><strong style="color:#1a1a1a">Protect POST borrow</strong><br><span style="color:#1a1a1a"><code>throttle:pinjam</code> from <a href="/artikel/laravel-rate-limiting-api">Rate Limiting (#69)</a>.</span></div>
  </li>
</ol>
</figure>

<h2>Full code — self-run demo</h2>
<p>Save it as <code>capstone_pinjam_kembali_laravel_demo.php</code>, then run <code>php capstone_pinjam_kembali_laravel_demo.php</code>:</p>

<pre><code class="language-php">&lt;?php
declare(strict_types=1);

function pinjam(array $buku): array
{
    if ($buku['status'] !== 'tersedia') {
        return ['lolos' =&gt; false, 'pesan' =&gt; 'buku sudah dipinjam'];
    }
    $buku['status'] = 'dipinjam';
    return ['lolos' =&gt; true, 'pesan' =&gt; 'pinjam ok', 'buku' =&gt; $buku];
}

function kembalikan(array $buku): array
{
    if ($buku['status'] !== 'dipinjam') {
        return ['lolos' =&gt; false, 'pesan' =&gt; 'belum dipinjam'];
    }
    $buku['status'] = 'tersedia';
    return ['lolos' =&gt; true, 'pesan' =&gt; 'kembali ok', 'buku' =&gt; $buku];
}

function demo(string $judul, array $buku, string $mode): void
{
    echo "=== {$judul} ===", PHP_EOL;
    if ($mode === 'pinjam') {
        $hasil = pinjam($buku);
    } else {
        $hasil = kembalikan($buku);
    }
    echo $hasil['lolos'] ? 'PASS' : 'FAIL', PHP_EOL;
    echo $hasil['pesan'], PHP_EOL, PHP_EOL;
}

$bukuTersedia = ['id' =&gt; 1, 'status' =&gt; 'tersedia'];
$bukuDipinjam = ['id' =&gt; 2, 'status' =&gt; 'dipinjam'];

demo('Borrow pass', $bukuTersedia, 'pinjam');
demo('Return pass', $bukuDipinjam, 'kembalikan');
demo('Wrong status fail', ['id' =&gt; 3, 'status' =&gt; 'dipinjam'], 'pinjam');
</code></pre>
<p><strong>Beginner — how to test this part:</strong> save the file as <code>capstone_pinjam_kembali_laravel_demo.php</code> in the proyek folder, then in Laragon/XAMPP terminal run <code>php capstone_pinjam_kembali_laravel_demo.php</code>. You should see two <code>PASS</code> then one <code>FAIL</code>. Functions <code>pinjam</code> and <code>kembalikan</code> are the core logic; <code>demo(...)</code> only wraps output for easy terminal reading.</p>

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
      <td>Return always fails</td>
      <td>Status not changed during borrow</td>
      <td>Check <code>alur-cek.php</code> flow — borrow must run first</td>
    </tr>
    <tr>
      <td>List empty despite borrowings</td>
      <td>Wrong <code>status</code> filter in <code>index</code></td>
      <td>Match status strings with migration</td>
    </tr>
    <tr>
      <td>403 on borrow</td>
      <td>Policy does not allow <code>create</code></td>
      <td>Review <a href="/artikel/laravel-policy-otorisasi-api">Authorization Policy (#66)</a></td>
    </tr>
    <tr>
      <td>Messy JSON</td>
      <td>Resource not attached</td>
      <td>Return to <a href="/artikel/laravel-api-resource-json">API Resource (#67)</a></td>
    </tr>
    <tr>
      <td><code>curl.exe</code> always returns 404</td>
      <td>Route not installed or <code>serve</code> not running</td>
      <td>Focus on PHP demo first; 404 is normal if route is missing</td>
    </tr>
    <tr>
      <td>Capstone test fails randomly</td>
      <td>Test database not fresh</td>
      <td>Use <code>RefreshDatabase</code> like in <a href="/artikel/laravel-feature-test-api">Feature Test (#68)</a></td>
    </tr>
  </tbody>
</table>

<h2>Short practice</h2>
<ol>
  <li>Change the demo: add a return-without-borrow scenario — must be <code>FAIL</code>.</li>
  <li>Explain to a friend: borrow -&gt; list -&gt; return order using the library clerk analogy.</li>
  <li>Write one sentence: which article from Eloquent Relations through Rate Limiting protects each capstone step.</li>
</ol>

<h2>FAQ</h2>
<p><strong>Does the capstone replace the six previous articles?</strong><br>
No. The capstone <strong>unites</strong> pieces you already learned. If relations or Policy are unclear, return to each article — do not skip.</p>
<p><strong>Must all Laravel snippets run immediately?</strong><br>
Not required today. The PHP demo proves status logic; controller snippets are pasted step by step into a proyek with install-through-rate-limiting foundations.</p>
<p><strong>Which tools should I open first?</strong><br>
Explorer for <code>Controllers</code>, <code>Models</code>, <code>routes\api.php</code>, <code>tests\Feature</code>; one terminal for the PHP demo; editor for snippets. If <code>serve</code> is alive, a second terminal for <code>curl.exe</code> borrow then return.</p>
<p><strong>Where should I test the snippets?</strong><br>
Middle step: copy into <code>alur-cek.php</code>, then <code>php alur-cek.php</code>. Full demo: <code>php capstone_pinjam_kembali_laravel_demo.php</code>. Laravel snippets go into controllers and <code>routes\api.php</code>; test the flow with <code>curl.exe</code>.</p>
<p><strong>Where after Seri 5?</strong><br>
Seri 5 is complete. The natural next path is <strong>Mobile Devices</strong> — the learning track after Laravel Lanjutan.</p>

<h2>Conclusion</h2>
<p>You finished the <strong>borrow &amp; return capstone</strong>: flow check in <code>alur-cek.php</code>, pass/fail demo in <code>capstone_pinjam_kembali_laravel_demo.php</code>, then controller snippets referring to Policy, Resource, Feature Test, and throttle from the six previous articles. A digital library clerk — one full shift from borrowing slip to book back on the shelf.</p>
<blockquote>
  <p><strong>Seri 5 progress:</strong> step <strong>#70 (this article)</strong> · <strong>7/7 — complete</strong> Laravel Lanjutan · prerequisite: <a href="/artikel/laravel-rate-limiting-api">Rate Limiting API (#69)</a> LIVE. Seri 5: <strong>7/7 — complete</strong>. Natural next path: <strong>Mobile Devices</strong>.</p>
</blockquote>
HTML;

        return str_replace(
            [
                'Seri 5: Laravel Lanjutan',
                'Instal PHP, Composer &amp; Proyek Laravel (#56)',
                'Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)',
                'CRUD API Buku: Ubah &amp; Hapus (#63)',
                'Laravel Lanjutan',
                'Cuplikan Laravel (bukan file mandiri)',
            ],
            [
                'Seri 5: Advanced Laravel',
                'Install PHP, Composer &amp; Your First Laravel Project (#56)',
                'Folder Structure, <code>.env</code> &amp; Artisan Laravel (#57)',
                'Book CRUD API: Update &amp; Delete (#63)',
                'Advanced Laravel',
                'Laravel snippet (not a standalone file)',
            ],
            $html
        );
    }
}
