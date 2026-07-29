<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article69Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $webCat = Category::where('slug', 'web-development')->first()
            ?? Category::where('slug', 'programming')->first();

        if (! $admin || ! $webCat) {
            throw new \RuntimeException('User atau kategori web-development/programming tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'laravel-rate-limiting-api';

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
                'title'              => 'Rate Limiting API',
                'title_en'           => 'Rate Limiting API',
                'excerpt'            => 'Seri 5 #69: setelah Feature Test mengunci JSON pinjam, batasi spam request dengan rate limiting — hitung jendela PHP dulu, demo 429, cuplikan RateLimiter Laravel, ramah awam.',
                'excerpt_en'         => 'Seri 5 #69: after Feature Test locks borrowing JSON, limit spam requests with rate limiting — plain PHP window counter first, 429 demo, Laravel RateLimiter snippets, beginner-friendly.',
                'body'               => $this->body(),
                'body_en'            => $this->bodyEn(),
                'status'             => 'published',
                'is_featured'        => false,
                'seo_title'          => 'Batasi Spam Request API — Rate Limiting Laravel Peminjaman',
                'seo_title_en'       => 'Limit API Spam Requests — Laravel Rate Limiting for Borrowing',
                'seo_description'    => 'Seri 5 #69: batasi spam ke API pinjam — hitung jendela PHP, demo 429 LOLOS/GAGAL, cuplikan RateLimiter &amp; throttle Laravel, curl.exe Windows.',
                'seo_description_en' => 'Seri 5 #69: limit spam to the borrowing API — PHP window counter, 429 pass/fail demo, Laravel RateLimiter &amp; throttle snippets, Windows curl.exe.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        // published_at setelah #68 supaya urutan "Terbaru" di /artikel tidak menjatuhkan #69 ke tengah daftar
        $prevPublished = Article::where('slug', 'laravel-feature-test-api')->value('published_at');
        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        } elseif ($prevPublished && $article->published_at <= $prevPublished) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['laravel', 'php', 'api', 'http', 'web', 'eloquent', 'database'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel ke-69 berhasil dipublish: '.$article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — kenapa batasi request setelah Feature Test</h2>
<p>Artikel ini adalah <strong>#69 (ini)</strong> di <strong>Seri 5: Laravel Lanjutan</strong>. Setelah bentuk jawaban JSON pinjam dikunci lewat <a href="/artikel/laravel-feature-test-api">Feature Test API (#68)</a>, pertanyaan berikutnya muncul: <strong>bagaimana kalau ada yang memanggil API berulang-ulang</strong> sampai server kewalahan?</p>
<p>Tanpa batas, satu skrip bisa menekan tombol pinjam ratusan kali per menit — antrean di loket perpustakaan jadi kacau. Hari ini kita belajar <strong>rate limiting</strong>: petugas loket yang menahan antrean — maksimal <strong>N request per menit</strong>; kalau lebih, respons <strong>429</strong> dengan pesan &ldquo;coba lagi nanti&rdquo;.</p>
<p><strong>Awam:</strong> bayangkan petugas loket perpustakaan yang berkata: &ldquo;Maksimal lima orang per menit.&rdquo; Yang datang ke-enam harus menunggu. Itu peran <strong>throttle</strong> dan <strong>RateLimiter</strong> setelah Feature Test memastikan JSON tetap benar.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> sudah selesai <a href="/artikel/laravel-feature-test-api">Feature Test API (#68)</a>, paham fondasi <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a> / <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a>. Pakai <strong>Laravel 13+</strong> — butuh <strong>PHP 8.3+</strong>.</p>
</blockquote>

<h2>Spesifikasi fitur — apa yang selesai hari ini?</h2>
<p>Tiga hal ini yang kita kejar:</p>
<ol>
  <li><strong>Hitung request dalam jendela waktu</strong> — misalnya maksimal 5 panggilan per menit per IP.</li>
  <li><strong>Tolak yang melewati batas</strong> — respons HTTP <code>429 Too Many Requests</code> dengan pesan &ldquo;coba lagi nanti&rdquo;.</li>
  <li><strong>Loloskan yang masih di bawah batas</strong> — respons normal <code>200</code> seperti biasa.</li>
</ol>
<p><strong>Awam:</strong> selesai artikel ini, kamu punya pola batas yang <strong>melindungi API pinjam</strong> dari spam — bukan mengganti validasi atau otorisasi, melainkan menahan banjir panggilan berulang. Fokus kita <strong>RateLimiter bawaan Laravel</strong> dengan middleware <code>throttle</code>. PHP murni dulu supaya logika hitung-cek-tolak terlihat jelas.</p>

<h2>Istilah — ringkas untuk rate limiting</h2>
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
      <td><code>limit</code></td>
      <td>Batas maksimal — misalnya 5 kali per menit</td>
      <td>Angka yang tidak boleh dilampaui</td>
    </tr>
    <tr>
      <td><code>throttle</code></td>
      <td>Middleware yang memperlambat/memblokir request berlebihan</td>
      <td>Seperti petugas loket yang menahan antrean</td>
    </tr>
    <tr>
      <td><code>RateLimiter</code></td>
      <td>Kelas Laravel untuk mendefinisikan aturan batas</td>
      <td>Didaftarkan di <code>AppServiceProvider</code></td>
    </tr>
    <tr>
      <td><code>429</code></td>
      <td>Status HTTP &ldquo;terlalu banyak request&rdquo;</td>
      <td>Artinya: coba lagi nanti, jangan spam</td>
    </tr>
    <tr>
      <td>Jendela waktu</td>
      <td>Rentang hitungan — misalnya 60 detik terakhir</td>
      <td>Request lama di luar jendela tidak dihitung</td>
    </tr>
    <tr>
      <td>Per IP</td>
      <td>Batas dihitung per alamat pengirim</td>
      <td>Satu pengguna spam tidak menghabiskan kuota semua orang</td>
    </tr>
  </tbody>
</table>
<p>Urutan belajar kita: <strong>fungsi hitung PHP dulu -&gt; demo pass/fail -&gt; baru cuplikan RateLimiter Laravel</strong>. Kalau loncat langsung ke middleware tanpa paham apa yang dihitung, batas sering ditulis asal-asalan.</p>

<h2>Persiapan — alat yang kamu buka</h2>
<p><strong>Alat yang dipakai di artikel ini</strong> (fondasi dari <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a> dan <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a> — tidak ada unduhan Composer baru hari ini):</p>
<ul>
  <li><strong>Explorer</strong> — cek folder proyek <code>perpustakaan-api</code>, lalu lihat <code>routes\api.php</code> atau <code>bootstrap\app.php</code> untuk rute pinjam, serta <code>app\Providers\AppServiceProvider.php</code> untuk mendaftarkan <code>RateLimiter</code>.</li>
  <li><strong>Terminal</strong> — Laragon: menu <em>Terminal</em> · XAMPP: tombol <em>Shell</em>. Hindari CMD/PowerShell dari Start Menu kalau PATH PHP-mu belum rapi.</li>
  <li><strong>Editor teks</strong> — Notepad / VS Code — untuk membuka <code>AppServiceProvider</code> atau file rute. Contoh: <code>notepad app\Providers\AppServiceProvider.php</code>.</li>
  <li><strong>Browser</strong> — opsional. Inti uji hari ini ada di terminal; browser berguna kalau kamu sudah menjalankan <code>php artisan serve</code> dan ingin bandingkan dengan <code>curl.exe</code>.</li>
</ul>
<p><strong>Awam:</strong> untuk artikel ini <strong>satu terminal sebenarnya cukup</strong> — jalankan <code>php laravel_rate_limiting_api_demo.php</code> di folder proyek. Kalau <code>php artisan serve</code> dari artikel sebelumnya masih hidup, pakai <strong>terminal kedua</strong> untuk demo PHP dan perintah <code>curl.exe</code> berulang saat ingin melihat respons <code>429</code> di rute pinjam. Kalau butuh jendela kedua: Laragon — klik menu <em>Terminal</em> lagi · XAMPP — klik tombol <em>Shell</em> lagi, lalu <code>cd</code> ke folder proyek yang sama.</p>
<p>Buka terminal Laragon/Shell XAMPP, masuk ke folder proyek:</p>
<pre><code class="language-bash">cd C:\laragon\www\perpustakaan-api
</code></pre>
<p>Di XAMPP biasanya: <code>cd C:\xampp\htdocs\perpustakaan-api</code>. Sesuaikan kalau foldermu beda.</p>
<p><strong>Install-dari-nol:</strong> kalau <code>php</code> atau <code>composer</code> belum dikenali terminal, kembali dulu ke <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a>. Kalau struktur folder proyek masih membingungkan, ulangi <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a>.</p>

<h2>Kenapa PHP biasa dulu?</h2>
<p>Kalau langsung loncat ke middleware <code>throttle</code> di Laravel, pemula sering bingung: apa yang sebenarnya dihitung? Maka kita mulai dari fungsi PHP biasa yang menghitung request dalam jendela waktu — supaya perbedaan <strong>lolos vs ditolak</strong> terlihat jelas sebelum dibungkus <code>RateLimiter</code>.</p>

<pre><code class="language-php">&lt;?php
// Mini: hitung request dalam jendela 60 detik, batas 3.
$limit = 3;
$window = 60;
$now = time();

$diBawahBatas = [$now - 10, $now - 20];
$diAtasBatas = [$now - 1, $now - 2, $now - 3, $now - 4];

function bolehLewat(array $waktuHit, int $limit, int $window, int $now): bool
{
    $masihAktif = array_filter($waktuHit, fn (int $t) =&gt; ($now - $t) &lt; $window);
    return count($masihAktif) &lt; $limit;
}

echo bolehLewat($diBawahBatas, $limit, $window, $now) ? "LOLOS" : "GAGAL", PHP_EOL;
echo bolehLewat($diAtasBatas, $limit, $window, $now) ? "LOLOS" : "GAGAL", PHP_EOL;
</code></pre>
<p><strong>Awam — cara menguji bagian ini:</strong> salin potongan di atas ke file misalnya <code>batas-cek.php</code>, lalu di terminal Laragon/XAMPP jalankan <code>php batas-cek.php</code>. Kalau muncul <code>LOLOS</code> lalu <code>GAGAL</code>, ide &ldquo;hitung dalam jendela waktu&rdquo; sudah terlihat — yang di bawah batas lolos, yang melewati batas ditolak.</p>

<h2>Alur batas — hitung, cek, tolak</h2>
<p>Gerakan yang benar selalu sama:</p>
<ol>
  <li><strong>Hitung</strong> — berapa request masuk dalam jendela waktu (misalnya 60 detik terakhir).</li>
  <li><strong>Cek</strong> — bandingkan dengan <code>limit</code> yang kamu tetapkan.</li>
  <li><strong>Tolak</strong> — kalau sudah penuh, kembalikan <code>429</code> dengan pesan &ldquo;coba lagi nanti&rdquo;.</li>
  <li><strong>Loloskan</strong> — kalau masih di bawah batas, lanjutkan ke logika pinjam biasa.</li>
</ol>

<pre><code class="language-php">&lt;?php
// Salin ke file misalnya batas-cek.php lalu jalankan: php batas-cek.php
$limit = 5;
$window = 60;
$now = time();

$riwayat = [$now - 5, $now - 10, $now - 15, $now - 20, $now - 25, $now - 30];

function hitungAktif(array $waktuHit, int $window, int $now): int
{
    return count(array_filter($waktuHit, fn (int $t) =&gt; ($now - $t) &lt; $window));
}

function cekBatas(array $waktuHit, int $limit, int $window, int $now): array
{
    $aktif = hitungAktif($waktuHit, $window, $now);
    if ($aktif &gt;= $limit) {
        return ["lolos" =&gt; false, "status" =&gt; 429, "pesan" =&gt; "coba lagi nanti"];
    }
    return ["lolos" =&gt; true, "status" =&gt; 200, "pesan" =&gt; "ok"];
}

$hasil = cekBatas($riwayat, $limit, $window, $now);
echo $hasil["lolos"] ? "CEK LOLOS" : "CEK GAGAL 429", PHP_EOL;
echo $hasil["status"], " — ", $hasil["pesan"], PHP_EOL;
</code></pre>
<p><strong>Awam — cara menguji bagian ini:</strong> salin potongan di atas ke <code>batas-cek.php</code>, lalu di terminal jalankan <code>php batas-cek.php</code>. Kalau muncul <code>CEK GAGAL 429</code> dan <code>429 — coba lagi nanti</code>, fondasi tolak sudah sehat. Ini versi PHP murni dari apa yang nanti ditulis sebagai <code>RateLimiter::for</code> di Laravel.</p>

<figure role="img" aria-label="Diagram alur rate limiting API pinjam perpustakaan" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" style="display:block;max-width:760px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif" viewBox="0 0 760 260">
  <defs>
    <marker id="laravel69rateArrow" orient="auto" markerWidth="8" markerHeight="8" refX="7" refY="4" viewBox="0 0 8 8">
      <path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/>
    </marker>
  </defs>
  <rect width="760" height="260" fill="#F5F5F0"/>
  <text x="24" y="36" fill="#1a1a1a" font-size="16" font-weight="700">Request masuk -&gt; Hitung jendela -&gt; Cek limit -&gt; 200 atau 429</text>
  <rect x="24" y="70" width="140" height="80" rx="8" fill="#ffffff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="94" y="105" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Masuk</text>
  <text x="94" y="128" text-anchor="middle" fill="#1a1a1a" font-size="12">request API</text>
  <line x1="164" y1="110" x2="214" y2="110" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel69rateArrow)"/>
  <rect x="218" y="70" width="140" height="80" rx="8" fill="#1a1a1a" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="288" y="105" text-anchor="middle" fill="#ffffff" font-size="15" font-weight="700">Hitung</text>
  <text x="288" y="128" text-anchor="middle" fill="#ffffff" font-size="12">jendela 60s</text>
  <line x1="358" y1="110" x2="408" y2="110" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel69rateArrow)"/>
  <rect x="412" y="70" width="140" height="80" rx="8" fill="#ffffff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="482" y="105" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Cek</text>
  <text x="482" y="128" text-anchor="middle" fill="#1a1a1a" font-size="12">limit</text>
  <line x1="552" y1="110" x2="602" y2="110" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel69rateArrow)"/>
  <rect x="606" y="70" width="130" height="80" rx="8" fill="#ffffff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="671" y="105" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Hasil</text>
  <text x="671" y="128" text-anchor="middle" fill="#1a1a1a" font-size="12">200 / 429</text>
  <text x="24" y="190" fill="#1a1a1a" font-size="13">Petugas loket: maksimal N request per menit per IP.</text>
  <text x="24" y="220" fill="#1a1a1a" font-size="13">Setelah Feature Test mengunci JSON, rate limiting melindungi dari spam.</text>
</svg>
<figcaption>Setelah bentuk JSON dikunci di <a href="/artikel/laravel-feature-test-api">Feature Test API (#68)</a>, <strong>#69 (ini)</strong> melindungi API pinjam dari spam request berulang.</figcaption>
</figure>

<h2>Laravel — cuplikan Rate Limiter (bukan file mandiri)</h2>
<p>Di proyek Laravel, aturan batas didaftarkan di <code>app\Providers\AppServiceProvider.php</code>, lalu dipasang ke rute pinjam lewat middleware <code>throttle</code>.</p>

<pre><code class="language-php">&lt;?php
// Cuplikan Laravel (bukan file mandiri)
// app/Providers/AppServiceProvider.php

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

public function boot(): void
{
    RateLimiter::for("pinjam", function (Request $request) {
        return Limit::perMinute(5)-&gt;by($request-&gt;ip());
    });
}
</code></pre>
<p>Pasang middleware ke rute pinjam di <code>routes\api.php</code>:</p>
<pre><code class="language-php">&lt;?php
// Cuplikan Laravel (bukan file mandiri)
// routes/api.php

Route::middleware("throttle:pinjam")-&gt;post("/api/pinjam", [PeminjamanController::class, "store"]);
</code></pre>
<p><strong>Awam:</strong> <code>RateLimiter::for('pinjam', ...)</code> = aturan &ldquo;maksimal 5 per menit per IP&rdquo;. <code>throttle:pinjam</code> = middleware yang menerapkan aturan itu sebelum logika pinjam dijalankan. Cuplikan ini <strong>bukan file mandiri</strong> — tempel ke proyek kalau rute pinjam sudah ada.</p>
<p>Uji dengan <code>curl.exe</code> berulang di terminal (kalau <code>php artisan serve</code> sudah jalan):</p>
<pre><code class="language-bash">curl.exe -X POST "http://127.0.0.1:8000/api/pinjam" -H "Content-Type: application/json" -d "{\"buku_id\":1}"
</code></pre>
<p>Jalankan perintah di atas lebih dari 5 kali dalam satu menit — yang ke-enam harusnya mengembalikan <code>429</code>.</p>
<p><strong>Awam:</strong> <code>curl.exe</code> membantu melihat respons dengan mata — spam beberapa kali sampai muncul <code>429</code>. Kalau muncul <code>404</code>, rute pinjam mungkin belum dipasang — itu wajar; fokus dulu ke demo PHP di atas. Kalau <code>php artisan serve</code> belum jalan, cukup uji demo PHP; terminal kedua hanya untuk uji <code>curl.exe</code> berulang.</p>

<h2>Pola Dasar — antrean yang terkendali</h2>
<figure style="margin:1.5rem 0;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1.25rem;color:#1a1a1a" aria-label="Enam langkah rate limiting API pinjam perpustakaan">
<ol style="list-style:none;padding:0;margin:0;color:#1a1a1a">
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div style="color:#1a1a1a"><strong style="color:#1a1a1a">Tetapkan limit</strong><br><span style="color:#1a1a1a">Misalnya 5 request per menit per IP untuk rute pinjam.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div style="color:#1a1a1a"><strong style="color:#1a1a1a">Daftarkan RateLimiter</strong><br><span style="color:#1a1a1a"><code>RateLimiter::for('pinjam', ...)</code> di <code>AppServiceProvider</code>.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div style="color:#1a1a1a"><strong style="color:#1a1a1a">Pasang middleware throttle</strong><br><span style="color:#1a1a1a"><code>throttle:pinjam</code> pada rute POST pinjam di <code>routes\api.php</code>.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div style="color:#1a1a1a"><strong style="color:#1a1a1a">Hitung dalam jendela</strong><br><span style="color:#1a1a1a">Laravel menghitung otomatis — mirip fungsi <code>hitungAktif</code> di demo PHP.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">5</span>
    <div style="color:#1a1a1a"><strong style="color:#1a1a1a">Tolak dengan 429</strong><br><span style="color:#1a1a1a">Request ke-enam dalam menit yang sama mendapat <code>429</code> &ldquo;coba lagi nanti&rdquo;.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">6</span>
    <div style="color:#1a1a1a"><strong style="color:#1a1a1a">Uji berulang</strong><br><span style="color:#1a1a1a"><code>curl.exe</code> spam di terminal kedua — pastikan <code>429</code> muncul setelah batas tercapai.</span></div>
  </li>
</ol>
</figure>

<h2>Kode lengkap — demo mandiri</h2>
<p>Simpan sebagai <code>laravel_rate_limiting_api_demo.php</code>, lalu jalankan <code>php laravel_rate_limiting_api_demo.php</code>:</p>

<pre><code class="language-php">&lt;?php
declare(strict_types=1);

$limit = 3;
$window = 60;
$now = time();

$riwayatDiBawah = [$now - 10, $now - 20];
$riwayatDiAtas = [$now - 1, $now - 2, $now - 3, $now - 4];

function hitungAktif(array $waktuHit, int $window, int $now): int
{
    return count(array_filter($waktuHit, fn (int $t) =&gt; ($now - $t) &lt; $window));
}

function cekRateLimit(array $waktuHit, int $limit, int $window, int $now): array
{
    $aktif = hitungAktif($waktuHit, $window, $now);
    if ($aktif &gt;= $limit) {
        return ["lolos" =&gt; false, "status" =&gt; 429, "pesan" =&gt; "coba lagi nanti"];
    }
    return ["lolos" =&gt; true, "status" =&gt; 200, "pesan" =&gt; "ok"];
}

function demo(string $judul, array $waktuHit, int $limit, int $window, int $now): void
{
    echo "=== {$judul} ===", PHP_EOL;
    $hasil = cekRateLimit($waktuHit, $limit, $window, $now);
    echo $hasil["lolos"] ? "LOLOS" : "GAGAL", PHP_EOL;
    echo "HTTP ", $hasil["status"], " — ", $hasil["pesan"], PHP_EOL, PHP_EOL;
}

demo("Di bawah batas — harus lolos", $riwayatDiBawah, $limit, $window, $now);
demo("Di atas batas — harus gagal 429", $riwayatDiAtas, $limit, $window, $now);
demo("Tepat di batas — harus gagal 429", [$now - 1, $now - 2, $now - 3], $limit, $window, $now);
</code></pre>
<p><strong>Awam — cara menguji bagian ini:</strong> simpan file sebagai <code>laravel_rate_limiting_api_demo.php</code> di folder proyek, lalu di terminal Laragon/XAMPP jalankan <code>php laravel_rate_limiting_api_demo.php</code>. Harusnya muncul satu <code>LOLOS</code> lalu dua <code>GAGAL</code> dengan <code>HTTP 429 — coba lagi nanti</code>. Fungsi <code>cekRateLimit</code> adalah inti logika; <code>demo(...)</code> hanya membungkus output agar mudah dibaca di terminal — mirip apa yang dilakukan <code>RateLimiter</code> di Laravel.</p>

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
      <td>Semua request ditolak, termasuk yang pertama</td>
      <td><code>limit</code> diset 0 atau jendela terlalu sempit</td>
      <td>Cek angka di <code>Limit::perMinute(5)</code></td>
    </tr>
    <tr>
      <td>Spam tidak pernah kena 429</td>
      <td>Middleware <code>throttle</code> belum dipasang ke rute</td>
      <td>Tambah <code>throttle:pinjam</code> di <code>routes\api.php</code></td>
    </tr>
    <tr>
      <td>Satu pengguna spam menghabiskan kuota semua orang</td>
      <td>Batas global, bukan per IP</td>
      <td>Pakai <code>-&gt;by($request-&gt;ip())</code> di RateLimiter</td>
    </tr>
    <tr>
      <td><code>curl.exe</code> selalu 404</td>
      <td>Rute pinjam belum dipasang atau <code>serve</code> belum jalan</td>
      <td>Fokus demo PHP dulu; 404 wajar kalau rute belum ada</td>
    </tr>
    <tr>
      <td>429 tidak muncul meski spam</td>
      <td>Cache driver tidak jalan di lokal</td>
      <td>Pastikan <code>CACHE_STORE</code> di <code>.env</code> bukan <code>array</code> untuk uji throttle</td>
    </tr>
    <tr>
      <td>Batas terlalu ketat untuk penggunaan normal</td>
      <td><code>limit</code> terlalu rendah untuk rute baca</td>
      <td>Pisahkan limit: ketat untuk POST pinjam, longgar untuk GET daftar</td>
    </tr>
  </tbody>
</table>

<h2>Latihan singkat</h2>
<ol>
  <li>Ubah demo: set <code>$limit = 5</code> dan buat skenario yang lolos vs gagal.</li>
  <li>Jelaskan ke teman: beda petugas loket yang menahan antrean vs tidak ada batas sama sekali — pakai analogi perpustakaan.</li>
  <li>Tulis satu kalimat: kenapa <code>429</code> lebih baik daripada membiarkan server crash karena spam.</li>
</ol>

<h2>FAQ singkat</h2>
<p><strong>Apakah rate limiting menggantikan Feature Test?</strong><br>
Tidak. Feature Test dari <a href="/artikel/laravel-feature-test-api">Feature Test API (#68)</a> mengunci bentuk JSON. Rate limiting melindungi API dari <strong>spam request berulang</strong> — tugas berbeda, keduanya saling melengkapi.</p>
<p><strong>Haruskah batas sama untuk semua rute?</strong><br>
Tidak wajib. Rute POST pinjam biasanya lebih ketat (misalnya 5/menit) daripada GET daftar buku (misalnya 60/menit). Sesuaikan dengan beban nyata perpustakaan mini.</p>
<p><strong>Tool apa yang dibuka dulu?</strong><br>
Explorer untuk memastikan folder proyek benar (<code>routes\api.php</code> + <code>AppServiceProvider</code>), satu terminal untuk demo PHP, editor untuk cuplikan Laravel. Kalau <code>serve</code> hidup, terminal kedua untuk <code>curl.exe</code> spam.</p>
<p><strong>Potongan sintaks diuji di mana?</strong><br>
Langkah tengah (fungsi hitung jendela) salin ke <code>batas-cek.php</code>, lalu jalankan <code>php batas-cek.php</code>. Demo lengkap diuji dengan <code>php laravel_rate_limiting_api_demo.php</code>. Cuplikan Laravel ditempel ke <code>app\Providers\AppServiceProvider.php</code> dan <code>routes\api.php</code>; uji throttle dengan <code>curl.exe</code> berulang.</p>
<p><strong>Ke mana setelah ini?</strong><br>
Berikutnya alami: <strong>Capstone</strong> — satukan semua potongan ke alur pinjam–kembali utuh.</p>

<h2>Kesimpulan</h2>
<p>Kamu sudah melindungi API pinjam dari spam dengan <strong>rate limiting</strong>: fungsi hitung PHP dulu di <code>batas-cek.php</code>, demo pass/fail di <code>laravel_rate_limiting_api_demo.php</code>, lalu cuplikan <strong>RateLimiter</strong> dan middleware <code>throttle</code> Laravel. Petugas loket digital — maksimal N request per menit, sisanya <code>429</code> &ldquo;coba lagi nanti&rdquo;.</p>
<blockquote>
  <p><strong>Seri 5 progress:</strong> langkah <strong>#69 (ini)</strong> · <strong>6/7</strong> Laravel Lanjutan · prasyarat: <a href="/artikel/laravel-feature-test-api">Feature Test API (#68)</a> LIVE. Berikutnya: <strong>Capstone</strong>.</p>
</blockquote>
HTML;
    }

    private function bodyEn(): string
    {
        $html = <<<'HTML'
<h2>Introduction — why limit requests after Feature Test</h2>
<p>This article is <strong>#69 (this article)</strong> in <strong>Seri 5: Laravel Lanjutan</strong>. After the borrowing JSON response shape was locked through <a href="/artikel/laravel-feature-test-api">Feature Test API (#68)</a>, the next question appears: <strong>what if someone calls the API over and over</strong> until the server is overwhelmed?</p>
<p>Without limits, one script could press the borrow button hundreds of times per minute — the library counter queue turns chaotic. Today we learn <strong>rate limiting</strong>: a counter clerk who holds the queue — at most <strong>N requests per minute</strong>; if more, the response is <strong>429</strong> with a &ldquo;try again later&rdquo; message.</p>
<p><strong>Beginner:</strong> imagine a library counter clerk who says: &ldquo;Maximum five people per minute.&rdquo; The sixth person must wait. That is the role of <strong>throttle</strong> and <strong>RateLimiter</strong> after Feature Test makes sure JSON stays correct.</p>

<blockquote>
  <p><strong>Prerequisite:</strong> finish <a href="/artikel/laravel-feature-test-api">Feature Test API (#68)</a>, and keep the foundations from <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a> / <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a>. Use <strong>Laravel 13+</strong> and <strong>PHP 8.3+</strong>.</p>
</blockquote>

<h2>Feature spec — what gets finished today?</h2>
<p>These are the three targets:</p>
<ol>
  <li><strong>Count requests in a time window</strong> — for example at most 5 calls per minute per IP.</li>
  <li><strong>Reject over-limit traffic</strong> — HTTP <code>429 Too Many Requests</code> with a &ldquo;try again later&rdquo; message.</li>
  <li><strong>Allow under-limit traffic</strong> — normal <code>200</code> response as usual.</li>
</ol>
<p><strong>Beginner:</strong> after this article, you have a limit pattern that <strong>protects the borrowing API</strong> from spam — it does not replace validation or authorization, but holds back a flood of repeated calls. We focus on Laravel&rsquo;s built-in <strong>RateLimiter</strong> with <code>throttle</code> middleware. Plain PHP first so the count-check-reject logic is visible.</p>

<h2>Terms — a quick glossary for rate limiting</h2>
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
      <td><code>limit</code></td>
      <td>Maximum cap — for example 5 times per minute</td>
      <td>The number that must not be exceeded</td>
    </tr>
    <tr>
      <td><code>throttle</code></td>
      <td>Middleware that slows or blocks excessive requests</td>
      <td>Like a counter clerk holding the queue</td>
    </tr>
    <tr>
      <td><code>RateLimiter</code></td>
      <td>Laravel class to define limit rules</td>
      <td>Registered in <code>AppServiceProvider</code></td>
    </tr>
    <tr>
      <td><code>429</code></td>
      <td>HTTP status &ldquo;too many requests&rdquo;</td>
      <td>Meaning: try again later, do not spam</td>
    </tr>
    <tr>
      <td>Time window</td>
      <td>Counting range — for example the last 60 seconds</td>
      <td>Old requests outside the window are not counted</td>
    </tr>
    <tr>
      <td>Per IP</td>
      <td>Limit counted per sender address</td>
      <td>One spammer does not consume everyone&rsquo;s quota</td>
    </tr>
  </tbody>
</table>
<p>Our learning order: <strong>plain PHP count functions first -&gt; pass/fail demo -&gt; then Laravel RateLimiter snippets</strong>. If you jump straight into middleware without understanding what is counted, limits are often written randomly.</p>

<h2>Preparation — tools to open</h2>
<p><strong>Tools used in this article</strong> (built on <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a> and <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a> — there is <strong>no new Composer download</strong> today):</p>
<ul>
  <li><strong>Explorer</strong> — check the <code>perpustakaan-api</code> project folder, then look at <code>routes\api.php</code> or <code>bootstrap\app.php</code> for borrowing routes, plus <code>app\Providers\AppServiceProvider.php</code> to register <code>RateLimiter</code>.</li>
  <li><strong>Terminal</strong> — Laragon: <em>Terminal</em> menu · XAMPP: <em>Shell</em> button. Avoid Start Menu CMD/PowerShell if your PHP PATH is still messy.</li>
  <li><strong>Text editor</strong> — Notepad / VS Code — to open <code>AppServiceProvider</code> or route files. Example: <code>notepad app\Providers\AppServiceProvider.php</code>.</li>
  <li><strong>Browser</strong> — optional. The core test today is in the terminal; the browser helps if you already run <code>php artisan serve</code> and want to compare with <code>curl.exe</code>.</li>
</ul>
<p><strong>Beginner:</strong> for this article, <strong>one terminal is actually enough</strong> — run <code>php laravel_rate_limiting_api_demo.php</code> in the project folder. If <code>php artisan serve</code> from the previous article is still alive, use a <strong>second terminal</strong> for the PHP demo and repeated <code>curl.exe</code> when you want to see <code>429</code> on the borrowing route. To open a second window: Laragon — click the <em>Terminal</em> menu again · XAMPP — click the <em>Shell</em> button again, then <code>cd</code> to the same project folder.</p>
<p>Open Laragon Terminal / XAMPP Shell, then move into the project folder:</p>
<pre><code class="language-bash">cd C:\laragon\www\perpustakaan-api
</code></pre>
<p>In XAMPP it is usually: <code>cd C:\xampp\htdocs\perpustakaan-api</code>. Adjust the path if your folder is different.</p>
<p><strong>Install-from-scratch:</strong> if <code>php</code> or <code>composer</code> is not recognized in the terminal, return to <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a>. If your project folder structure is still confusing, review <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a> first.</p>

<h2>Why start with plain PHP first?</h2>
<p>If you jump straight into Laravel <code>throttle</code> middleware, beginners often wonder: what is actually being counted? So we start from plain PHP functions that count requests in a time window — so the difference between <strong>allowed vs rejected</strong> is visible before wrapping it in <code>RateLimiter</code>.</p>

<pre><code class="language-php">&lt;?php
// Mini: count requests in a 60-second window, limit 3.
$limit = 3;
$window = 60;
$now = time();

$underLimit = [$now - 10, $now - 20];
$overLimit = [$now - 1, $now - 2, $now - 3, $now - 4];

function bolehLewat(array $waktuHit, int $limit, int $window, int $now): bool
{
    $masihAktif = array_filter($waktuHit, fn (int $t) =&gt; ($now - $t) &lt; $window);
    return count($masihAktif) &lt; $limit;
}

echo bolehLewat($underLimit, $limit, $window, $now) ? "PASS" : "FAIL", PHP_EOL;
echo bolehLewat($overLimit, $limit, $window, $now) ? "PASS" : "FAIL", PHP_EOL;
</code></pre>
<p><strong>Beginner — how to test this part:</strong> copy the snippet above into a file such as <code>batas-cek.php</code>, then in Laragon/XAMPP terminal run <code>php batas-cek.php</code>. If you see <code>PASS</code> then <code>FAIL</code>, the idea &ldquo;count inside a time window&rdquo; is already visible — under the limit passes, over the limit is rejected.</p>

<h2>Limit flow — count, check, reject</h2>
<p>The correct move order is always the same:</p>
<ol>
  <li><strong>Count</strong> — how many requests arrived in the time window (for example the last 60 seconds).</li>
  <li><strong>Check</strong> — compare with the <code>limit</code> you set.</li>
  <li><strong>Reject</strong> — if full, return <code>429</code> with a &ldquo;try again later&rdquo; message.</li>
  <li><strong>Allow</strong> — if still under the limit, continue to normal borrowing logic.</li>
</ol>

<pre><code class="language-php">&lt;?php
// Copy into a file such as batas-cek.php, then run: php batas-cek.php
$limit = 5;
$window = 60;
$now = time();

$riwayat = [$now - 5, $now - 10, $now - 15, $now - 20, $now - 25, $now - 30];

function hitungAktif(array $waktuHit, int $window, int $now): int
{
    return count(array_filter($waktuHit, fn (int $t) =&gt; ($now - $t) &lt; $window));
}

function cekBatas(array $waktuHit, int $limit, int $window, int $now): array
{
    $aktif = hitungAktif($waktuHit, $window, $now);
    if ($aktif &gt;= $limit) {
        return ["lolos" =&gt; false, "status" =&gt; 429, "pesan" =&gt; "coba lagi nanti"];
    }
    return ["lolos" =&gt; true, "status" =&gt; 200, "pesan" =&gt; "ok"];
}

$hasil = cekBatas($riwayat, $limit, $window, $now);
echo $hasil["lolos"] ? "CHECK PASS" : "CHECK FAIL 429", PHP_EOL;
echo $hasil["status"], " — ", $hasil["pesan"], PHP_EOL;
</code></pre>
<p><strong>Beginner — how to test this part:</strong> copy the snippet above into <code>batas-cek.php</code>, then run <code>php batas-cek.php</code> in the terminal. If you see <code>CHECK FAIL 429</code> and <code>429 — coba lagi nanti</code>, the reject foundation is healthy. This is the plain PHP version of what you will later write as <code>RateLimiter::for</code> in Laravel.</p>

<figure role="img" aria-label="Diagram rate limiting flow for library borrowing API" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" style="display:block;max-width:760px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif" viewBox="0 0 760 260">
  <defs>
    <marker id="laravel69rateArrow" orient="auto" markerWidth="8" markerHeight="8" refX="7" refY="4" viewBox="0 0 8 8">
      <path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/>
    </marker>
  </defs>
  <rect width="760" height="260" fill="#F5F5F0"/>
  <text x="24" y="36" fill="#1a1a1a" font-size="16" font-weight="700">Request in -&gt; Count window -&gt; Check limit -&gt; 200 or 429</text>
  <rect x="24" y="70" width="140" height="80" rx="8" fill="#ffffff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="94" y="105" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">In</text>
  <text x="94" y="128" text-anchor="middle" fill="#1a1a1a" font-size="12">API request</text>
  <line x1="164" y1="110" x2="214" y2="110" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel69rateArrow)"/>
  <rect x="218" y="70" width="140" height="80" rx="8" fill="#1a1a1a" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="288" y="105" text-anchor="middle" fill="#ffffff" font-size="15" font-weight="700">Count</text>
  <text x="288" y="128" text-anchor="middle" fill="#ffffff" font-size="12">60s window</text>
  <line x1="358" y1="110" x2="408" y2="110" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel69rateArrow)"/>
  <rect x="412" y="70" width="140" height="80" rx="8" fill="#ffffff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="482" y="105" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Check</text>
  <text x="482" y="128" text-anchor="middle" fill="#1a1a1a" font-size="12">limit</text>
  <line x1="552" y1="110" x2="602" y2="110" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel69rateArrow)"/>
  <rect x="606" y="70" width="130" height="80" rx="8" fill="#ffffff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="671" y="105" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Result</text>
  <text x="671" y="128" text-anchor="middle" fill="#1a1a1a" font-size="12">200 / 429</text>
  <text x="24" y="190" fill="#1a1a1a" font-size="13">Counter clerk: at most N requests per minute per IP.</text>
  <text x="24" y="220" fill="#1a1a1a" font-size="13">After Feature Test locks JSON, rate limiting protects from spam.</text>
</svg>
<figcaption>After JSON shape was locked in <a href="/artikel/laravel-feature-test-api">Feature Test API (#68)</a>, <strong>#69 (this article)</strong> protects the borrowing API from repeated spam requests.</figcaption>
</figure>

<h2>Laravel rate limiter snippets (not standalone files)</h2>
<p>In the Laravel project, limit rules are registered in <code>app\Providers\AppServiceProvider.php</code>, then applied to the borrowing route through <code>throttle</code> middleware.</p>

<pre><code class="language-php">&lt;?php
// Cuplikan Laravel (bukan file mandiri)
// app/Providers/AppServiceProvider.php

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

public function boot(): void
{
    RateLimiter::for("pinjam", function (Request $request) {
        return Limit::perMinute(5)-&gt;by($request-&gt;ip());
    });
}
</code></pre>
<p>Attach middleware to the borrowing route in <code>routes\api.php</code>:</p>
<pre><code class="language-php">&lt;?php
// Cuplikan Laravel (bukan file mandiri)
// routes/api.php

Route::middleware("throttle:pinjam")-&gt;post("/api/pinjam", [PeminjamanController::class, "store"]);
</code></pre>
<p><strong>Beginner:</strong> <code>RateLimiter::for('pinjam', ...)</code> = rule &ldquo;at most 5 per minute per IP&rdquo;. <code>throttle:pinjam</code> = middleware that applies the rule before borrowing logic runs. This snippet is <strong>not a standalone file</strong> — paste it into the project when the borrowing route exists.</p>
<p>Test with repeated <code>curl.exe</code> in the terminal (if <code>php artisan serve</code> is running):</p>
<pre><code class="language-bash">curl.exe -X POST "http://127.0.0.1:8000/api/pinjam" -H "Content-Type: application/json" -d "{\"buku_id\":1}"
</code></pre>
<p>Run the command above more than 5 times within one minute — the sixth should return <code>429</code>.</p>
<p><strong>Beginner:</strong> <code>curl.exe</code> helps you see responses with your eyes — spam several times until <code>429</code> appears. If you get <code>404</code>, the borrowing route may not be installed yet — that is normal; focus on the PHP demo above first. If <code>php artisan serve</code> is not running, the PHP demo is enough; a second terminal is only for repeated <code>curl.exe</code> tests.</p>

<h2>Basic Pattern — controlled queue</h2>
<figure style="margin:1.5rem 0;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1.25rem;color:#1a1a1a" aria-label="Six steps for library borrowing API rate limiting">
<ol style="list-style:none;padding:0;margin:0;color:#1a1a1a">
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div style="color:#1a1a1a"><strong style="color:#1a1a1a">Set the limit</strong><br><span style="color:#1a1a1a">For example 5 requests per minute per IP for the borrowing route.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div style="color:#1a1a1a"><strong style="color:#1a1a1a">Register RateLimiter</strong><br><span style="color:#1a1a1a"><code>RateLimiter::for('pinjam', ...)</code> in <code>AppServiceProvider</code>.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div style="color:#1a1a1a"><strong style="color:#1a1a1a">Attach throttle middleware</strong><br><span style="color:#1a1a1a"><code>throttle:pinjam</code> on the POST borrowing route in <code>routes\api.php</code>.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div style="color:#1a1a1a"><strong style="color:#1a1a1a">Count inside the window</strong><br><span style="color:#1a1a1a">Laravel counts automatically — similar to the <code>hitungAktif</code> function in the PHP demo.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">5</span>
    <div style="color:#1a1a1a"><strong style="color:#1a1a1a">Reject with 429</strong><br><span style="color:#1a1a1a">The sixth request in the same minute gets <code>429</code> &ldquo;try again later&rdquo;.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">6</span>
    <div style="color:#1a1a1a"><strong style="color:#1a1a1a">Test repeatedly</strong><br><span style="color:#1a1a1a"><code>curl.exe</code> spam in a second terminal — confirm <code>429</code> appears after the limit is reached.</span></div>
  </li>
</ol>
</figure>

<h2>Full code — self-run demo</h2>
<p>Save it as <code>laravel_rate_limiting_api_demo.php</code>, then run <code>php laravel_rate_limiting_api_demo.php</code>:</p>

<pre><code class="language-php">&lt;?php
declare(strict_types=1);

$limit = 3;
$window = 60;
$now = time();

$riwayatDiBawah = [$now - 10, $now - 20];
$riwayatDiAtas = [$now - 1, $now - 2, $now - 3, $now - 4];

function hitungAktif(array $waktuHit, int $window, int $now): int
{
    return count(array_filter($waktuHit, fn (int $t) =&gt; ($now - $t) &lt; $window));
}

function cekRateLimit(array $waktuHit, int $limit, int $window, int $now): array
{
    $aktif = hitungAktif($waktuHit, $window, $now);
    if ($aktif &gt;= $limit) {
        return ["lolos" =&gt; false, "status" =&gt; 429, "pesan" =&gt; "coba lagi nanti"];
    }
    return ["lolos" =&gt; true, "status" =&gt; 200, "pesan" =&gt; "ok"];
}

function demo(string $judul, array $waktuHit, int $limit, int $window, int $now): void
{
    echo "=== {$judul} ===", PHP_EOL;
    $hasil = cekRateLimit($waktuHit, $limit, $window, $now);
    echo $hasil["lolos"] ? "PASS" : "FAIL", PHP_EOL;
    echo "HTTP ", $hasil["status"], " — ", $hasil["pesan"], PHP_EOL, PHP_EOL;
}

demo("Under limit — should pass", $riwayatDiBawah, $limit, $window, $now);
demo("Over limit — should fail 429", $riwayatDiAtas, $limit, $window, $now);
demo("Exactly at limit — should fail 429", [$now - 1, $now - 2, $now - 3], $limit, $window, $now);
</code></pre>
<p><strong>Beginner — how to test this part:</strong> save the file as <code>laravel_rate_limiting_api_demo.php</code> in the project folder, then in Laragon/XAMPP terminal run <code>php laravel_rate_limiting_api_demo.php</code>. You should see one <code>PASS</code> then two <code>FAIL</code> with <code>HTTP 429 — coba lagi nanti</code>. Function <code>cekRateLimit</code> is the core logic; <code>demo(...)</code> only wraps output so the terminal result is easy to read — similar to what <code>RateLimiter</code> does in Laravel.</p>

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
      <td>All requests rejected, including the first</td>
      <td><code>limit</code> set to 0 or window too narrow</td>
      <td>Check the number in <code>Limit::perMinute(5)</code></td>
    </tr>
    <tr>
      <td>Spam never hits 429</td>
      <td><code>throttle</code> middleware not attached to the route</td>
      <td>Add <code>throttle:pinjam</code> in <code>routes\api.php</code></td>
    </tr>
    <tr>
      <td>One spammer consumes everyone&rsquo;s quota</td>
      <td>Global limit, not per IP</td>
      <td>Use <code>-&gt;by($request-&gt;ip())</code> in RateLimiter</td>
    </tr>
    <tr>
      <td><code>curl.exe</code> always returns 404</td>
      <td>Borrowing route not installed or <code>serve</code> not running</td>
      <td>Focus on the PHP demo first; 404 is normal if the route is missing</td>
    </tr>
    <tr>
      <td>429 never appears despite spam</td>
      <td>Cache driver not working locally</td>
      <td>Make sure <code>CACHE_STORE</code> in <code>.env</code> is not <code>array</code> for throttle tests</td>
    </tr>
    <tr>
      <td>Limit too strict for normal use</td>
      <td><code>limit</code> too low for read routes</td>
      <td>Split limits: strict for POST borrow, relaxed for GET list</td>
    </tr>
  </tbody>
</table>

<h2>Short practice</h2>
<ol>
  <li>Change the demo: set <code>$limit = 5</code> and create pass vs fail scenarios.</li>
  <li>Explain to a friend: difference between a counter clerk holding the queue vs no limit at all — use the library analogy.</li>
  <li>Write one sentence: why <code>429</code> is better than letting the server crash from spam.</li>
</ol>

<h2>FAQ</h2>
<p><strong>Does rate limiting replace Feature Test?</strong><br>
No. Feature Test from <a href="/artikel/laravel-feature-test-api">Feature Test API (#68)</a> locks JSON shape. Rate limiting protects the API from <strong>repeated spam requests</strong> — different jobs, they complement each other.</p>
<p><strong>Must the limit be the same for every route?</strong><br>
Not required. POST borrowing routes are usually stricter (for example 5/minute) than GET book lists (for example 60/minute). Adjust to real load in the mini-library.</p>
<p><strong>Which tools should I open first?</strong><br>
Explorer to confirm the project folder (<code>routes\api.php</code> + <code>AppServiceProvider</code>), one terminal for the PHP demo, editor for Laravel snippets. If <code>serve</code> is alive, a second terminal for <code>curl.exe</code> spam.</p>
<p><strong>Where should I test the snippets?</strong><br>
The middle step (window count functions) is copied into <code>batas-cek.php</code>, then run with <code>php batas-cek.php</code>. The full demo is tested with <code>php laravel_rate_limiting_api_demo.php</code>. Laravel snippets are pasted into <code>app\Providers\AppServiceProvider.php</code> and <code>routes\api.php</code>; test throttle with repeated <code>curl.exe</code>.</p>
<p><strong>Where next?</strong><br>
The natural next step is <strong>Capstone</strong> — bring all pieces together into one complete borrow–return flow.</p>

<h2>Conclusion</h2>
<p>You protected the borrowing API from spam with <strong>rate limiting</strong>: plain PHP counting first in <code>batas-cek.php</code>, pass/fail demo in <code>laravel_rate_limiting_api_demo.php</code>, then Laravel <strong>RateLimiter</strong> and <code>throttle</code> middleware snippets. A digital counter clerk — at most N requests per minute, the rest get <code>429</code> &ldquo;try again later&rdquo;.</p>
<blockquote>
  <p><strong>Seri 5 progress:</strong> step <strong>#69 (this article)</strong> · <strong>6/7</strong> Laravel Lanjutan · prerequisite: <a href="/artikel/laravel-feature-test-api">Feature Test API (#68)</a> LIVE. Next: <strong>Capstone</strong>.</p>
</blockquote>
HTML;

        return str_replace(
            [
                'Seri 5: Laravel Lanjutan',
                'Feature Test API (#68)',
                'Instal PHP, Composer &amp; Proyek Laravel (#56)',
                'Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)',
                'Capstone',
                'Laravel Lanjutan',
            ],
            [
                'Seri 5: Advanced Laravel',
                'Feature Test API (#68)',
                'Install PHP, Composer &amp; Your First Laravel Project (#56)',
                'Folder Structure, <code>.env</code> &amp; Artisan Laravel (#57)',
                'Capstone',
                'Advanced Laravel',
            ],
            $html
        );
    }
}
