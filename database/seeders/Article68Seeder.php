<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article68Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $webCat = Category::where('slug', 'web-development')->first()
            ?? Category::where('slug', 'programming')->first();

        if (! $admin || ! $webCat) {
            throw new \RuntimeException('User atau kategori web-development/programming tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'laravel-feature-test-api';

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
                'title'              => 'Feature Test API',
                'title_en'           => 'Feature Test API',
                'excerpt'            => 'Seri 5 #68: setelah Resource merapikan JSON pinjam, kunci bentuk jawaban dengan Feature Test — uji otomatis assertStatus &amp; assertJsonPath, PHP dulu, cuplikan PHPUnit Laravel, ramah awam.',
                'excerpt_en'         => 'Seri 5 #68: after Resource tidies borrowing JSON, lock the response shape with Feature Test — automated assertStatus &amp; assertJsonPath checks, plain PHP first, Laravel PHPUnit snippets, beginner-friendly.',
                'body'               => $this->body(),
                'body_en'            => $this->bodyEn(),
                'status'             => 'published',
                'is_featured'        => false,
                'seo_title'          => 'Uji Otomatis Bentuk JSON API — Feature Test Laravel Peminjaman',
                'seo_title_en'       => 'Automated JSON Shape Tests — Laravel Feature Test for Borrowing API',
                'seo_description'    => 'Seri 5 #68: setelah Resource, kunci JSON pinjam dengan Feature Test — assertStatus, assertJsonPath, status_label ada, anggota_id hilang, demo PHP &amp; PHPUnit.',
                'seo_description_en' => 'Seri 5 #68: after API Resource, lock borrowing JSON shape with Feature Test — assertStatus, assertJsonPath, status_label present, anggota_id absent, PHP demo &amp; PHPUnit snippets.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        // published_at setelah #67 supaya urutan "Terbaru" di /artikel tidak menjatuhkan #68 ke tengah daftar
        $prevPublished = Article::where('slug', 'laravel-api-resource-json')->value('published_at');
        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        } elseif ($prevPublished && $article->published_at <= $prevPublished) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['laravel', 'php', 'api', 'http', 'web', 'eloquent', 'database'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel ke-68 berhasil dipublish: '.$article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — kenapa uji otomatis setelah Resource</h2>
<p>Artikel ini adalah <strong>#68 (ini)</strong> di <strong>Seri 5: Laravel Lanjutan</strong>. Setelah bentuk jawaban JSON pinjam dirapikan lewat <a href="/artikel/laravel-api-resource-json">API Resource: Rapikan Bentuk JSON (#67)</a>, pertanyaan berikutnya muncul: <strong>bagaimana memastikan bentuk itu tetap benar</strong> setiap kali kode berubah?</p>
<p>Tanpa uji otomatis, kamu harus cek manual tiap hari: buka browser, panggil rute, lihat JSON, pastikan <code>status_label</code> ada dan <code>anggota_id</code> tidak bocor. Itu melelahkan dan mudah terlewat. Hari ini kita belajar <strong>Feature Test</strong> — uji otomatis yang memanggil rute API seperti klien sungguhan, lalu memeriksa status dan bentuk JSON dengan <code>assertStatus</code>, <code>assertJson</code>, dan <code>assertJsonPath</code>.</p>
<p><strong>Awam:</strong> bayangkan petugas perpustakaan yang setiap malam menjalankan <strong>checklist otomatis</strong> pada slip pinjam: judul buku ada, nama anggota ada, status jelas, ID internal tidak bocor. Bukan cek manual satu per satu tiap pagi. Itu peran <strong>Feature Test</strong> setelah Resource merapikan JSON.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> sudah selesai <a href="/artikel/laravel-api-resource-json">API Resource: Rapikan Bentuk JSON (#67)</a>, paham fondasi <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a> / <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a>. Pakai <strong>Laravel 13+</strong> — butuh <strong>PHP 8.3+</strong>.</p>
</blockquote>

<h2>Spesifikasi fitur — apa yang selesai hari ini?</h2>
<p>Tiga hal ini yang kita kejar:</p>
<ol>
  <li><strong>Uji status HTTP</strong> — rute pinjam mengembalikan <code>200</code> saat data ada, dicek dengan <code>assertStatus(200)</code>.</li>
  <li><strong>Uji field wajib</strong> — <code>id</code>, <code>judul_buku</code>, <code>nama_anggota</code>, <code>status</code>, <code>status_label</code> hadir di JSON.</li>
  <li><strong>Uji field tersembunyi</strong> — <code>anggota_id</code> tidak ada di respons publik, dicek dengan <code>assertJsonMissingPath</code> atau setara.</li>
</ol>
<p><strong>Awam:</strong> selesai artikel ini, kamu punya pola uji yang <strong>merawat bentuk JSON</strong> dari Resource — kalau besok ada yang tidak sengaja mengembalikan baris mentah, uji otomatis langsung gagal. Fokus kita <strong>PHPUnit-style Feature Test</strong> (bawaan Laravel). Pest ada sebagai alternatif singkat di akhir, tapi tidak wajib supaya awam tidak bingung.</p>

<h2>Istilah — ringkas untuk uji otomatis</h2>
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
      <td>Feature Test</td>
      <td>Uji yang memanggil rute/API seperti pengguna sungguhan</td>
      <td>File di <code>tests\Feature</code></td>
    </tr>
    <tr>
      <td><code>assertStatus</code></td>
      <td>Perintah &ldquo;status HTTP harus angka ini&rdquo;</td>
      <td>Misalnya <code>assertStatus(200)</code></td>
    </tr>
    <tr>
      <td><code>assertJson</code></td>
      <td>Perintah &ldquo;JSON harus punya potongan ini&rdquo;</td>
      <td>Cocok untuk field kecil</td>
    </tr>
    <tr>
      <td><code>assertJsonPath</code> / <code>assertJsonFragment</code></td>
      <td>Perintah &ldquo;nilai di jalur JSON ini harus begini&rdquo; atau &ldquo;potongan JSON ini harus ada&rdquo;</td>
      <td>Misalnya <code>data.0.status_label</code> atau fragmen kecil field</td>
    </tr>
    <tr>
      <td>Arrange-Act-Assert</td>
      <td>Atur data -&gt; jalankan aksi -&gt; cek hasil</td>
      <td>Dalam bahasa awam: atur-jalankan-cek</td>
    </tr>
    <tr>
      <td>PHPUnit</td>
      <td>Mesin uji bawaan Laravel untuk menjalankan Feature Test</td>
      <td><code>php artisan test</code> atau <code>php vendor/bin/phpunit</code></td>
    </tr>
  </tbody>
</table>
<p>Urutan belajar kita: <strong>fungsi cek PHP dulu -&gt; demo pass/fail -&gt; baru cuplikan Feature Test Laravel</strong>. Kalau loncat langsung ke kelas uji tanpa paham apa yang dicek, assert sering ditulis asal-asalan.</p>

<h2>Persiapan — alat yang kamu buka</h2>
<p><strong>Alat yang dipakai di artikel ini</strong> (fondasi dari <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a> dan <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a> — tidak ada unduhan Composer baru hari ini):</p>
<ul>
  <li><strong>Explorer</strong> — cek folder proyek <code>perpustakaan-api</code>, lalu lihat <code>tests\Feature</code> serta <code>app\Http\Controllers</code> dan <code>app\Http\Resources</code> untuk rute pinjam dan Resource.</li>
  <li><strong>Terminal</strong> — Laragon: menu <em>Terminal</em> · XAMPP: tombol <em>Shell</em>. Hindari CMD/PowerShell dari Start Menu kalau PATH PHP-mu belum rapi.</li>
  <li><strong>Editor teks</strong> — Notepad / VS Code — untuk membuka file uji. Contoh: <code>notepad tests\Feature\PeminjamanResourceTest.php</code> (atau nama serupa di folder <code>tests\Feature</code>).</li>
  <li><strong>Browser</strong> — opsional. Inti uji hari ini ada di terminal; browser berguna kalau kamu sudah menjalankan <code>php artisan serve</code> dan ingin bandingkan dengan <code>curl.exe</code>.</li>
</ul>
<p><strong>Awam:</strong> untuk artikel ini <strong>satu terminal sebenarnya cukup</strong> — jalankan <code>php laravel_feature_test_api_demo.php</code> di folder proyek. Untuk suite Laravel, di terminal yang sama jalankan <code>php artisan test</code> atau <code>php vendor/bin/phpunit</code>. Kalau <code>php artisan serve</code> dari artikel sebelumnya masih hidup, pakai <strong>terminal kedua</strong> untuk demo PHP dan perintah <code>curl.exe</code> saat ingin bandingkan JSON manual dengan hasil uji otomatis. Kalau butuh jendela kedua: Laragon — klik menu <em>Terminal</em> lagi · XAMPP — klik tombol <em>Shell</em> lagi, lalu <code>cd</code> ke folder proyek yang sama.</p>
<p>Buka terminal Laragon/Shell XAMPP, masuk ke folder proyek:</p>
<pre><code class="language-bash">cd C:\laragon\www\perpustakaan-api
</code></pre>
<p>Di XAMPP biasanya: <code>cd C:\xampp\htdocs\perpustakaan-api</code>. Sesuaikan kalau foldermu beda.</p>
<p><strong>Install-dari-nol:</strong> kalau <code>php</code> atau <code>composer</code> belum dikenali terminal, kembali dulu ke <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a>. Kalau struktur folder proyek masih membingungkan, ulangi <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a>.</p>

<h2>Kenapa PHP biasa dulu?</h2>
<p>Kalau langsung loncat ke kelas Feature Test di Laravel, pemula sering bingung: apa yang sebenarnya dicek? Maka kita mulai dari fungsi PHP biasa yang memeriksa array JSON — supaya perbedaan <strong>lolos vs gagal</strong> terlihat jelas sebelum dibungkus <code>assertJsonPath</code>.</p>

<pre><code class="language-php">&lt;?php
// Mini: cek slip pinjam rapi vs bocor.
$jsonRapi = [
    "id" =&gt; 10,
    "judul_buku" =&gt; "Dasar PHP",
    "nama_anggota" =&gt; "Budi",
    "status" =&gt; "aktif",
    "status_label" =&gt; "Sedang dipinjam",
];

$jsonBocor = [
    "id" =&gt; 10,
    "anggota_id" =&gt; 1,
    "judul_buku" =&gt; "Dasar PHP",
    "nama_anggota" =&gt; "Budi",
    "status" =&gt; "aktif",
];

function cekSlipRapi(array $json): bool
{
    $wajib = ["id", "judul_buku", "nama_anggota", "status", "status_label"];
    foreach ($wajib as $field) {
        if (! array_key_exists($field, $json)) {
            return false;
        }
    }
    return ! array_key_exists("anggota_id", $json);
}

echo cekSlipRapi($jsonRapi) ? "LOLOS" : "GAGAL", PHP_EOL;
echo cekSlipRapi($jsonBocor) ? "LOLOS" : "GAGAL", PHP_EOL;
</code></pre>
<p><strong>Awam — cara menguji bagian ini:</strong> salin potongan di atas ke file misalnya <code>uji-cek.php</code>, lalu di terminal Laragon/XAMPP jalankan <code>php uji-cek.php</code>. Kalau muncul <code>LOLOS</code> lalu <code>GAGAL</code>, ide &ldquo;uji bentuk JSON&rdquo; sudah terlihat — yang rapi lolos, yang bocor <code>anggota_id</code> gagal.</p>

<h2>Alur uji — atur, jalankan, cek</h2>
<p>Gerakan yang benar selalu sama (Arrange-Act-Assert dalam bahasa awam: <strong>atur-jalankan-cek</strong>):</p>
<ol>
  <li><strong>Atur data</strong> — siapkan catatan pinjam di basis data uji atau array contoh.</li>
  <li><strong>Jalankan aksi</strong> — panggil rute API (<code>getJson</code> di Laravel) seperti klien sungguhan.</li>
  <li><strong>Cek hasil</strong> — status HTTP benar, field wajib ada, field internal hilang.</li>
  <li><strong>Ulangi saat kode berubah</strong> — satu perintah di terminal, bukan cek manual tiap pagi.</li>
</ol>

<pre><code class="language-php">&lt;?php
// Salin ke file misalnya uji-cek.php lalu jalankan: php uji-cek.php
$peminjamanRapi = [
    "id" =&gt; 10,
    "judul_buku" =&gt; "Dasar PHP",
    "nama_anggota" =&gt; "Budi",
    "status" =&gt; "aktif",
    "status_label" =&gt; "Sedang dipinjam",
];

function assertFieldAda(array $json, string $field): bool
{
    return array_key_exists($field, $json);
}

function assertFieldTidakAda(array $json, string $field): bool
{
    return ! array_key_exists($field, $json);
}

$lolos = assertFieldAda($peminjamanRapi, "status_label")
    &amp;&amp; assertFieldTidakAda($peminjamanRapi, "anggota_id");

echo $lolos ? "CEK LOLOS" : "CEK GAGAL", PHP_EOL;
echo json_encode($peminjamanRapi, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL;
</code></pre>
<p><strong>Awam — cara menguji bagian ini:</strong> salin potongan di atas ke <code>uji-cek.php</code>, lalu di terminal jalankan <code>php uji-cek.php</code>. Kalau muncul <code>CEK LOLOS</code> dan JSON tanpa <code>anggota_id</code>, fondasi assert sudah sehat. Ini versi PHP murni dari apa yang nanti ditulis sebagai <code>assertJsonPath</code> di Laravel.</p>

<figure role="img" aria-label="Diagram alur Feature Test kunci bentuk JSON pinjam" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" style="display:block;max-width:760px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif" viewBox="0 0 760 260">
  <defs>
    <marker id="laravel68testArrow" orient="auto" markerWidth="8" markerHeight="8" refX="7" refY="4" viewBox="0 0 8 8">
      <path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/>
    </marker>
  </defs>
  <rect width="760" height="260" fill="#F5F5F0"/>
  <text x="24" y="36" fill="#1a1a1a" font-size="16" font-weight="700">Atur data -&gt; Panggil rute -&gt; Assert JSON -&gt; Lolos / Gagal</text>
  <rect x="24" y="70" width="140" height="80" rx="8" fill="#ffffff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="94" y="105" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Atur</text>
  <text x="94" y="128" text-anchor="middle" fill="#1a1a1a" font-size="12">data pinjam</text>
  <line x1="164" y1="110" x2="214" y2="110" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel68testArrow)"/>
  <rect x="218" y="70" width="140" height="80" rx="8" fill="#1a1a1a" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="288" y="105" text-anchor="middle" fill="#ffffff" font-size="15" font-weight="700">Jalankan</text>
  <text x="288" y="128" text-anchor="middle" fill="#ffffff" font-size="12">getJson</text>
  <line x1="358" y1="110" x2="408" y2="110" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel68testArrow)"/>
  <rect x="412" y="70" width="140" height="80" rx="8" fill="#ffffff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="482" y="105" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Cek</text>
  <text x="482" y="128" text-anchor="middle" fill="#1a1a1a" font-size="12">assertJson</text>
  <line x1="552" y1="110" x2="602" y2="110" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel68testArrow)"/>
  <rect x="606" y="70" width="130" height="80" rx="8" fill="#ffffff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="671" y="105" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Hasil</text>
  <text x="671" y="128" text-anchor="middle" fill="#1a1a1a" font-size="12">lolos/gagal</text>
  <text x="24" y="190" fill="#1a1a1a" font-size="13">Checklist otomatis: status_label ada, anggota_id tidak bocor.</text>
  <text x="24" y="220" fill="#1a1a1a" font-size="13">Setelah Resource merapikan JSON, Feature Test mengunci bentuk itu.</text>
</svg>
<figcaption>Setelah bentuk JSON dirapikan di <a href="/artikel/laravel-api-resource-json">API Resource: Rapikan Bentuk JSON (#67)</a>, <strong>#68 (ini)</strong> mengunci bentuk itu dengan Feature Test otomatis.</figcaption>
</figure>

<h2>Laravel — cuplikan Feature Test (bukan file mandiri)</h2>
<p>Di proyek Laravel, uji ditulis di folder <code>tests\Feature</code>. Cuplikan berikut memanggil rute pinjam dan memeriksa bentuk JSON dari Resource.</p>

<pre><code class="language-php">&lt;?php
// Cuplikan Laravel (bukan file mandiri)
// tests/Feature/PeminjamanResourceTest.php

namespace Tests\Feature;

use App\Models\Peminjaman;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeminjamanResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_peminjaman_json_rapi(): void
    {
        $peminjaman = Peminjaman::factory()-&gt;create([
            "status" =&gt; "aktif",
        ]);

        $response = $this-&gt;getJson("/api/peminjaman/{$peminjaman-&gt;id}");

        $response-&gt;assertStatus(200)
            -&gt;assertJsonPath("data.id", $peminjaman-&gt;id)
            -&gt;assertJsonPath("data.judul_buku", $peminjaman-&gt;buku-&gt;judul)
            -&gt;assertJsonPath("data.nama_anggota", $peminjaman-&gt;anggota-&gt;nama)
            -&gt;assertJsonPath("data.status", "aktif")
            -&gt;assertJsonPath("data.status_label", "Sedang dipinjam")
            -&gt;assertJsonMissingPath("data.anggota_id");
    }
}
</code></pre>
<p><strong>Awam:</strong> <code>getJson</code> = panggil rute seperti klien API. <code>assertStatus(200)</code> = status HTTP harus sukses. <code>assertJsonPath</code> = nilai di jalur JSON harus cocok. <code>assertJsonMissingPath</code> = field internal tidak boleh ada. Cuplikan ini <strong>bukan file mandiri</strong> — tempel ke proyek kalau rute dan factory pinjam sudah ada.</p>
<p>Jalankan suite uji di terminal yang sama:</p>
<pre><code class="language-bash">php artisan test
php vendor/bin/phpunit --filter test_peminjaman_json_rapi
</code></pre>
<p>Kalau <code>php artisan serve</code> sudah jalan di terminal pertama, bandingkan manual di terminal kedua dengan <code>curl.exe</code> (opsional):</p>
<pre><code class="language-bash">curl.exe "http://127.0.0.1:8000/api/peminjaman/10"
</code></pre>
<p><strong>Awam:</strong> <code>curl.exe</code> membantu melihat JSON dengan mata — tapi yang mengunci bentuk setiap hari adalah <code>php artisan test</code>, bukan cek manual. Kalau uji gagal, ada field yang hilang atau <code>anggota_id</code> bocor kembali.</p>
<p><em>Catatan singkat:</em> Pest adalah alternatif sintaks uji yang lebih ringkas; Laravel mendukungnya, tapi artikel ini fokus PHPUnit karena itu bawaan dan dokumentasi resmi paling mudah diikuti awam.</p>

<h2>Pola Dasar — uji yang merawat JSON</h2>
<figure style="margin:1.5rem 0;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1.25rem;color:#1a1a1a" aria-label="Enam langkah uji otomatis bentuk JSON pinjam">
<ol style="list-style:none;padding:0;margin:0;color:#1a1a1a">
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div style="color:#1a1a1a"><strong style="color:#1a1a1a">Atur data uji</strong><br><span style="color:#1a1a1a">Siapkan catatan pinjam — factory atau seeder di basis data uji.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div style="color:#1a1a1a"><strong style="color:#1a1a1a">Panggil rute API</strong><br><span style="color:#1a1a1a"><code>getJson</code> ke <code>/api/peminjaman/{id}</code> seperti klien sungguhan.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div style="color:#1a1a1a"><strong style="color:#1a1a1a">Cek status HTTP</strong><br><span style="color:#1a1a1a"><code>assertStatus(200)</code> — rute hidup dan tidak error.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div style="color:#1a1a1a"><strong style="color:#1a1a1a">Cek field wajib</strong><br><span style="color:#1a1a1a"><code>assertJsonPath</code> untuk <code>id</code>, <code>judul_buku</code>, <code>nama_anggota</code>, <code>status</code>, <code>status_label</code>.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">5</span>
    <div style="color:#1a1a1a"><strong style="color:#1a1a1a">Cek field tersembunyi</strong><br><span style="color:#1a1a1a"><code>assertJsonMissingPath("data.anggota_id")</code> — ID internal tidak bocor.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">6</span>
    <div style="color:#1a1a1a"><strong style="color:#1a1a1a">Jalankan berulang</strong><br><span style="color:#1a1a1a"><code>php artisan test</code> setiap kode berubah — checklist otomatis, bukan cek manual.</span></div>
  </li>
</ol>
</figure>

<h2>Kode lengkap — demo mandiri</h2>
<p>Simpan sebagai <code>laravel_feature_test_api_demo.php</code>, lalu jalankan <code>php laravel_feature_test_api_demo.php</code>:</p>

<pre><code class="language-php">&lt;?php
declare(strict_types=1);

$peminjamanRapi = [
    "id" =&gt; 10,
    "judul_buku" =&gt; "Dasar PHP",
    "nama_anggota" =&gt; "Budi",
    "status" =&gt; "aktif",
    "status_label" =&gt; "Sedang dipinjam",
];

$peminjamanBocor = [
    "id" =&gt; 11,
    "anggota_id" =&gt; 2,
    "judul_buku" =&gt; "Belajar Laravel",
    "nama_anggota" =&gt; "Siti",
    "status" =&gt; "aktif",
];

function cekBentukJson(array $json): array
{
    $wajib = ["id", "judul_buku", "nama_anggota", "status", "status_label"];
    $gagal = [];

    foreach ($wajib as $field) {
        if (! array_key_exists($field, $json)) {
            $gagal[] = "field hilang: {$field}";
        }
    }
    if (array_key_exists("anggota_id", $json)) {
        $gagal[] = "anggota_id tidak boleh ada";
    }

    return ["lolos" =&gt; $gagal === [], "gagal" =&gt; $gagal];
}

function demo(string $judul, array $json): void
{
    echo "=== {$judul} ===", PHP_EOL;
    $hasil = cekBentukJson($json);
    echo $hasil["lolos"] ? "LOLOS" : "GAGAL", PHP_EOL;
    if ($hasil["gagal"] !== []) {
        echo implode(", ", $hasil["gagal"]), PHP_EOL;
    }
    echo json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL, PHP_EOL;
}

demo("Slip rapi — harus lolos", $peminjamanRapi);
demo("Slip bocor — harus gagal", $peminjamanBocor);
demo("Tanpa status_label — harus gagal", [
    "id" =&gt; 12,
    "judul_buku" =&gt; "PHP Lanjut",
    "nama_anggota" =&gt; "Ani",
    "status" =&gt; "kembali",
]);
</code></pre>
<p><strong>Awam:</strong> tiga skenario di atas menunjukkan pola yang wajar: slip rapi lolos, slip bocor gagal, field wajib hilang gagal. Fungsi <code>cekBentukJson</code> adalah inti logika; <code>demo(...)</code> hanya membungkus output agar mudah dibaca di terminal — mirip apa yang dilakukan <code>assertJsonPath</code> di Laravel.</p>

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
      <td>Uji lolos tapi JSON di browser salah</td>
      <td>Uji tidak memanggil rute yang sama dengan produksi</td>
      <td>Pastikan <code>getJson</code> ke URL yang benar</td>
    </tr>
    <tr>
      <td><code>anggota_id</code> bocor tapi uji tidak gagal</td>
      <td>Lupa assert field tersembunyi</td>
      <td>Tambah <code>assertJsonMissingPath("data.anggota_id")</code></td>
    </tr>
    <tr>
      <td>Uji gagal padahal Resource benar</td>
      <td>Data uji tidak disiapkan (factory/seeder kosong)</td>
      <td>Atur data dulu di langkah Arrange</td>
    </tr>
    <tr>
      <td><code>assertJsonPath</code> selalu gagal</td>
      <td>Jalur JSON salah — misalnya lupa awalan <code>data.</code></td>
      <td>Cek respons mentah dengan <code>curl.exe</code> atau <code>dump()</code></td>
    </tr>
    <tr>
      <td>Hanya cek status, tidak cek isi</td>
      <td>Hanya <code>assertStatus(200)</code> tanpa assert JSON</td>
      <td>Tambah assert untuk setiap field wajib dari Resource</td>
    </tr>
    <tr>
      <td><code>php artisan test</code> tidak dikenali</td>
      <td>Terminal bukan dari Laragon/XAMPP</td>
      <td>Buka Terminal Laragon atau Shell XAMPP, <code>cd</code> ke proyek</td>
    </tr>
  </tbody>
</table>

<h2>Latihan singkat</h2>
<ol>
  <li>Ubah demo: tambah assert bahwa <code>status_label</code> untuk status <code>kembali</code> harus &ldquo;Sudah kembali&rdquo;.</li>
  <li>Jelaskan ke teman: beda cek manual tiap pagi vs checklist otomatis setiap malam — pakai analogi petugas perpustakaan.</li>
  <li>Tulis satu kalimat: kenapa <code>assertJsonMissingPath</code> penting setelah Resource menyembunyikan <code>anggota_id</code>.</li>
</ol>

<h2>FAQ singkat</h2>
<p><strong>Apakah Feature Test menggantikan Resource?</strong><br>
Tidak. Resource dari <a href="/artikel/laravel-api-resource-json">API Resource: Rapikan Bentuk JSON (#67)</a> merapikan bentuk jawaban. Feature Test memastikan bentuk itu <strong>tetap benar</strong> setiap kode berubah.</p>
<p><strong>Haruskah pakai Pest, bukan PHPUnit?</strong><br>
Tidak wajib. PHPUnit adalah bawaan Laravel dan fokus artikel ini. Pest opsional kalau kamu sudah nyaman — polanya sama: atur, jalankan, cek.</p>
<p><strong>Tool apa yang dibuka dulu?</strong><br>
Explorer untuk memastikan folder proyek benar (<code>tests\Feature</code> + Controllers/Resources), satu terminal untuk demo PHP, editor untuk file uji. Kalau <code>serve</code> hidup, terminal kedua untuk <code>curl.exe</code> bandingan opsional.</p>
<p><strong>Potongan sintaks diuji di mana?</strong><br>
Langkah tengah (fungsi cek array) salin ke <code>uji-cek.php</code>, lalu jalankan <code>php uji-cek.php</code>. Demo lengkap diuji dengan <code>php laravel_feature_test_api_demo.php</code>. Cuplikan Laravel ditempel ke <code>tests\Feature\PeminjamanResourceTest.php</code>; jalankan suite dengan <code>php artisan test</code> atau <code>php vendor/bin/phpunit</code>.</p>
<p><strong>Ke mana setelah ini?</strong><br>
Berikutnya alami: <strong>Rate Limiting</strong> — batasi spam request ke API perpustakaan mini.</p>

<h2>Kesimpulan</h2>
<p>Kamu sudah mengunci bentuk jawaban JSON dari Resource dengan <strong>uji otomatis</strong>: fungsi cek PHP dulu di <code>uji-cek.php</code>, demo pass/fail di <code>laravel_feature_test_api_demo.php</code>, lalu cuplikan <strong>Feature Test</strong> Laravel dengan <code>assertStatus</code>, <code>assertJsonPath</code>, dan <code>assertJsonMissingPath</code>. Checklist otomatis setiap malam — bukan cek manual tiap pagi.</p>
<blockquote>
  <p><strong>Seri 5 progress:</strong> langkah <strong>#68 (ini)</strong> · <strong>5/7</strong> Laravel Lanjutan · prasyarat: <a href="/artikel/laravel-api-resource-json">API Resource: Rapikan Bentuk JSON (#67)</a> LIVE. Berikutnya: <strong>Rate Limiting</strong>.</p>
</blockquote>
HTML;
    }

    private function bodyEn(): string
    {
        $html = <<<'HTML'
<h2>Introduction — why automated tests after Resource</h2>
<p>This article is <strong>#68 (this article)</strong> in <strong>Seri 5: Laravel Lanjutan</strong>. After the borrowing JSON response shape was tidied through <a href="/artikel/laravel-api-resource-json">API Resource: Rapikan Bentuk JSON (#67)</a>, the next question appears: <strong>how do you make sure that shape stays correct</strong> every time code changes?</p>
<p>Without automated tests, you must check manually every day: open the browser, call the route, read JSON, confirm <code>status_label</code> is present and <code>anggota_id</code> does not leak. That is tiring and easy to miss. Today we learn <strong>Feature Test</strong> — automated tests that call API routes like a real client, then inspect status and JSON shape with <code>assertStatus</code>, <code>assertJson</code>, and <code>assertJsonPath</code>.</p>
<p><strong>Beginner:</strong> imagine a library clerk who every night runs an <strong>automated checklist</strong> on borrowing slips: book title present, member name present, status clear, internal ID not leaking. Not a one-by-one manual check every morning. That is the role of <strong>Feature Test</strong> after Resource tidies JSON.</p>

<blockquote>
  <p><strong>Prerequisite:</strong> finish <a href="/artikel/laravel-api-resource-json">API Resource: Rapikan Bentuk JSON (#67)</a>, and keep the foundations from <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a> / <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a>. Use <strong>Laravel 13+</strong> and <strong>PHP 8.3+</strong>.</p>
</blockquote>

<h2>Feature spec — what gets finished today?</h2>
<p>These are the three targets:</p>
<ol>
  <li><strong>Test HTTP status</strong> — the borrowing route returns <code>200</code> when data exists, checked with <code>assertStatus(200)</code>.</li>
  <li><strong>Test required fields</strong> — <code>id</code>, <code>judul_buku</code>, <code>nama_anggota</code>, <code>status</code>, <code>status_label</code> are present in JSON.</li>
  <li><strong>Test hidden fields</strong> — <code>anggota_id</code> is absent from the public response, checked with <code>assertJsonMissingPath</code> or equivalent.</li>
</ol>
<p><strong>Beginner:</strong> after this article, you have a test pattern that <strong>maintains JSON shape</strong> from Resource — if tomorrow someone accidentally returns a raw row, the automated test fails immediately. We focus on <strong>PHPUnit-style Feature Test</strong> (Laravel default). Pest is mentioned briefly as an optional alternative so beginners do not get confused.</p>

<h2>Terms — a quick glossary for automated tests</h2>
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
      <td>Feature Test</td>
      <td>Test that calls a route/API like a real user</td>
      <td>File in <code>tests\Feature</code></td>
    </tr>
    <tr>
      <td><code>assertStatus</code></td>
      <td>Command: &ldquo;HTTP status must be this number&rdquo;</td>
      <td>For example <code>assertStatus(200)</code></td>
    </tr>
    <tr>
      <td><code>assertJson</code></td>
      <td>Command: &ldquo;JSON must contain this fragment&rdquo;</td>
      <td>Good for small field checks</td>
    </tr>
    <tr>
      <td><code>assertJsonPath</code> / <code>assertJsonFragment</code></td>
      <td>Command: &ldquo;value at this JSON path must match&rdquo; or &ldquo;this JSON fragment must exist&rdquo;</td>
      <td>For example <code>data.0.status_label</code> or a small field fragment</td>
    </tr>
    <tr>
      <td>Arrange-Act-Assert</td>
      <td>Set up data -&gt; run action -&gt; check result</td>
      <td>Beginner phrase: set up, run, check</td>
    </tr>
    <tr>
      <td>PHPUnit</td>
      <td>Laravel&rsquo;s built-in test runner for Feature Tests</td>
      <td><code>php artisan test</code> or <code>php vendor/bin/phpunit</code></td>
    </tr>
  </tbody>
</table>
<p>Our learning order: <strong>plain PHP check functions first -&gt; pass/fail demo -&gt; then Laravel Feature Test snippets</strong>. If you jump straight into a test class without understanding what is checked, asserts are often written randomly.</p>

<h2>Preparation — tools to open</h2>
<p><strong>Tools used in this article</strong> (built on <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a> and <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a> — there is <strong>no new Composer download</strong> today):</p>
<ul>
  <li><strong>Explorer</strong> — check the <code>perpustakaan-api</code> project folder, then look at <code>tests\Feature</code> plus <code>app\Http\Controllers</code> and <code>app\Http\Resources</code> for borrowing routes and Resource.</li>
  <li><strong>Terminal</strong> — Laragon: <em>Terminal</em> menu · XAMPP: <em>Shell</em> button. Avoid Start Menu CMD/PowerShell if your PHP PATH is still messy.</li>
  <li><strong>Text editor</strong> — Notepad / VS Code — to open test files. Example: <code>notepad tests\Feature\PeminjamanResourceTest.php</code> (or a similar name in <code>tests\Feature</code>).</li>
  <li><strong>Browser</strong> — optional. The core test today is in the terminal; the browser helps if you already run <code>php artisan serve</code> and want to compare with <code>curl.exe</code>.</li>
</ul>
<p><strong>Beginner:</strong> for this article, <strong>one terminal is actually enough</strong> — run <code>php laravel_feature_test_api_demo.php</code> in the project folder. For the Laravel suite, in the same terminal run <code>php artisan test</code> or <code>php vendor/bin/phpunit</code>. If <code>php artisan serve</code> from the previous article is still alive, use a <strong>second terminal</strong> for the PHP demo and <code>curl.exe</code> when you want to compare JSON manually with automated test results. To open a second window: Laragon — click the <em>Terminal</em> menu again · XAMPP — click the <em>Shell</em> button again, then <code>cd</code> to the same project folder.</p>
<p>Open Laragon Terminal / XAMPP Shell, then move into the project folder:</p>
<pre><code class="language-bash">cd C:\laragon\www\perpustakaan-api
</code></pre>
<p>In XAMPP it is usually: <code>cd C:\xampp\htdocs\perpustakaan-api</code>. Adjust the path if your folder is different.</p>
<p><strong>Install-from-scratch:</strong> if <code>php</code> or <code>composer</code> is not recognized in the terminal, return to <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a>. If your project folder structure is still confusing, review <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a> first.</p>

<h2>Why start with plain PHP first?</h2>
<p>If you jump straight into a Laravel Feature Test class, beginners often wonder: what is actually being checked? So we start from plain PHP functions that inspect a JSON array — so the difference between <strong>pass vs fail</strong> is visible before wrapping it in <code>assertJsonPath</code>.</p>

<pre><code class="language-php">&lt;?php
// Mini: check tidy vs leaking borrowing slip.
$jsonRapi = [
    "id" =&gt; 10,
    "judul_buku" =&gt; "Dasar PHP",
    "nama_anggota" =&gt; "Budi",
    "status" =&gt; "aktif",
    "status_label" =&gt; "Sedang dipinjam",
];

$jsonBocor = [
    "id" =&gt; 10,
    "anggota_id" =&gt; 1,
    "judul_buku" =&gt; "Dasar PHP",
    "nama_anggota" =&gt; "Budi",
    "status" =&gt; "aktif",
];

function cekSlipRapi(array $json): bool
{
    $wajib = ["id", "judul_buku", "nama_anggota", "status", "status_label"];
    foreach ($wajib as $field) {
        if (! array_key_exists($field, $json)) {
            return false;
        }
    }
    return ! array_key_exists("anggota_id", $json);
}

echo cekSlipRapi($jsonRapi) ? "PASS" : "FAIL", PHP_EOL;
echo cekSlipRapi($jsonBocor) ? "PASS" : "FAIL", PHP_EOL;
</code></pre>
<p><strong>Beginner — how to test this part:</strong> copy the snippet above into a file such as <code>uji-cek.php</code>, then in Laragon/XAMPP terminal run <code>php uji-cek.php</code>. If you see <code>PASS</code> then <code>FAIL</code>, the idea &ldquo;test JSON shape&rdquo; is already visible — the tidy one passes, the leaking <code>anggota_id</code> one fails.</p>

<h2>Test flow — set up, run, check</h2>
<p>The correct move order is always the same (Arrange-Act-Assert in beginner words: <strong>set up, run, check</strong>):</p>
<ol>
  <li><strong>Set up data</strong> — prepare borrowing records in the test database or a sample array.</li>
  <li><strong>Run the action</strong> — call the API route (<code>getJson</code> in Laravel) like a real client.</li>
  <li><strong>Check the result</strong> — HTTP status is correct, required fields present, internal fields gone.</li>
  <li><strong>Repeat when code changes</strong> — one terminal command, not a manual check every morning.</li>
</ol>

<pre><code class="language-php">&lt;?php
// Copy into a file such as uji-cek.php, then run: php uji-cek.php
$peminjamanRapi = [
    "id" =&gt; 10,
    "judul_buku" =&gt; "Dasar PHP",
    "nama_anggota" =&gt; "Budi",
    "status" =&gt; "aktif",
    "status_label" =&gt; "Sedang dipinjam",
];

function assertFieldAda(array $json, string $field): bool
{
    return array_key_exists($field, $json);
}

function assertFieldTidakAda(array $json, string $field): bool
{
    return ! array_key_exists($field, $json);
}

$lolos = assertFieldAda($peminjamanRapi, "status_label")
    &amp;&amp; assertFieldTidakAda($peminjamanRapi, "anggota_id");

echo $lolos ? "CHECK PASS" : "CHECK FAIL", PHP_EOL;
echo json_encode($peminjamanRapi, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL;
</code></pre>
<p><strong>Beginner — how to test this part:</strong> copy the snippet above into <code>uji-cek.php</code>, then run <code>php uji-cek.php</code> in the terminal. If you see <code>CHECK PASS</code> and JSON without <code>anggota_id</code>, the assert foundation is healthy. This is the plain PHP version of what you will later write as <code>assertJsonPath</code> in Laravel.</p>

<figure role="img" aria-label="Diagram Feature Test flow locking borrowing JSON shape" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" style="display:block;max-width:760px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif" viewBox="0 0 760 260">
  <defs>
    <marker id="laravel68testArrow" orient="auto" markerWidth="8" markerHeight="8" refX="7" refY="4" viewBox="0 0 8 8">
      <path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/>
    </marker>
  </defs>
  <rect width="760" height="260" fill="#F5F5F0"/>
  <text x="24" y="36" fill="#1a1a1a" font-size="16" font-weight="700">Set up -&gt; Call route -&gt; Assert JSON -&gt; Pass / Fail</text>
  <rect x="24" y="70" width="140" height="80" rx="8" fill="#ffffff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="94" y="105" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Set up</text>
  <text x="94" y="128" text-anchor="middle" fill="#1a1a1a" font-size="12">borrow data</text>
  <line x1="164" y1="110" x2="214" y2="110" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel68testArrow)"/>
  <rect x="218" y="70" width="140" height="80" rx="8" fill="#1a1a1a" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="288" y="105" text-anchor="middle" fill="#ffffff" font-size="15" font-weight="700">Run</text>
  <text x="288" y="128" text-anchor="middle" fill="#ffffff" font-size="12">getJson</text>
  <line x1="358" y1="110" x2="408" y2="110" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel68testArrow)"/>
  <rect x="412" y="70" width="140" height="80" rx="8" fill="#ffffff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="482" y="105" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Check</text>
  <text x="482" y="128" text-anchor="middle" fill="#1a1a1a" font-size="12">assertJson</text>
  <line x1="552" y1="110" x2="602" y2="110" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel68testArrow)"/>
  <rect x="606" y="70" width="130" height="80" rx="8" fill="#ffffff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="671" y="105" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Result</text>
  <text x="671" y="128" text-anchor="middle" fill="#1a1a1a" font-size="12">pass/fail</text>
  <text x="24" y="190" fill="#1a1a1a" font-size="13">Automated checklist: status_label present, anggota_id not leaking.</text>
  <text x="24" y="220" fill="#1a1a1a" font-size="13">After Resource tidies JSON, Feature Test locks that shape.</text>
</svg>
<figcaption>After JSON shape was tidied in <a href="/artikel/laravel-api-resource-json">API Resource: Rapikan Bentuk JSON (#67)</a>, <strong>#68 (this article)</strong> locks that shape with automated Feature Test.</figcaption>
</figure>

<h2>Laravel Feature Test snippets (not standalone files)</h2>
<p>In the Laravel project, tests live in <code>tests\Feature</code>. The snippet below calls the borrowing route and inspects JSON shape from Resource.</p>

<pre><code class="language-php">&lt;?php
// Cuplikan Laravel (bukan file mandiri)
// tests/Feature/PeminjamanResourceTest.php

namespace Tests\Feature;

use App\Models\Peminjaman;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeminjamanResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_peminjaman_json_rapi(): void
    {
        $peminjaman = Peminjaman::factory()-&gt;create([
            "status" =&gt; "aktif",
        ]);

        $response = $this-&gt;getJson("/api/peminjaman/{$peminjaman-&gt;id}");

        $response-&gt;assertStatus(200)
            -&gt;assertJsonPath("data.id", $peminjaman-&gt;id)
            -&gt;assertJsonPath("data.judul_buku", $peminjaman-&gt;buku-&gt;judul)
            -&gt;assertJsonPath("data.nama_anggota", $peminjaman-&gt;anggota-&gt;nama)
            -&gt;assertJsonPath("data.status", "aktif")
            -&gt;assertJsonPath("data.status_label", "Sedang dipinjam")
            -&gt;assertJsonMissingPath("data.anggota_id");
    }
}
</code></pre>
<p><strong>Beginner:</strong> <code>getJson</code> = call the route like an API client. <code>assertStatus(200)</code> = HTTP status must succeed. <code>assertJsonPath</code> = value at a JSON path must match. <code>assertJsonMissingPath</code> = internal field must not exist. This snippet is <strong>not a standalone file</strong> — paste it into the project when the route and borrowing factory exist.</p>
<p>Run the test suite in the same terminal:</p>
<pre><code class="language-bash">php artisan test
php vendor/bin/phpunit --filter test_peminjaman_json_rapi
</code></pre>
<p>If <code>php artisan serve</code> is already running in the first terminal, optionally compare manually in the second terminal with <code>curl.exe</code>:</p>
<pre><code class="language-bash">curl.exe "http://127.0.0.1:8000/api/peminjaman/10"
</code></pre>
<p><strong>Beginner:</strong> <code>curl.exe</code> helps you see JSON with your eyes — but what locks the shape every day is <code>php artisan test</code>, not manual checking. If the test fails, a field is missing or <code>anggota_id</code> leaked again.</p>
<p><em>Brief note:</em> Pest is an alternative test syntax that is more compact; Laravel supports it, but this article focuses on PHPUnit because it is the default and the official docs are easiest for beginners.</p>

<h2>Basic Pattern — tests that maintain JSON</h2>
<figure style="margin:1.5rem 0;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1.25rem;color:#1a1a1a" aria-label="Six steps for automated borrowing JSON shape tests">
<ol style="list-style:none;padding:0;margin:0;color:#1a1a1a">
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div style="color:#1a1a1a"><strong style="color:#1a1a1a">Set up test data</strong><br><span style="color:#1a1a1a">Prepare borrowing records — factory or seeder in the test database.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div style="color:#1a1a1a"><strong style="color:#1a1a1a">Call the API route</strong><br><span style="color:#1a1a1a"><code>getJson</code> to <code>/api/peminjaman/{id}</code> like a real client.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div style="color:#1a1a1a"><strong style="color:#1a1a1a">Check HTTP status</strong><br><span style="color:#1a1a1a"><code>assertStatus(200)</code> — route is alive and not erroring.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div style="color:#1a1a1a"><strong style="color:#1a1a1a">Check required fields</strong><br><span style="color:#1a1a1a"><code>assertJsonPath</code> for <code>id</code>, <code>judul_buku</code>, <code>nama_anggota</code>, <code>status</code>, <code>status_label</code>.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">5</span>
    <div style="color:#1a1a1a"><strong style="color:#1a1a1a">Check hidden fields</strong><br><span style="color:#1a1a1a"><code>assertJsonMissingPath("data.anggota_id")</code> — internal ID does not leak.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">6</span>
    <div style="color:#1a1a1a"><strong style="color:#1a1a1a">Run repeatedly</strong><br><span style="color:#1a1a1a"><code>php artisan test</code> whenever code changes — automated checklist, not manual checking.</span></div>
  </li>
</ol>
</figure>

<h2>Full code — self-run demo</h2>
<p>Save it as <code>laravel_feature_test_api_demo.php</code>, then run <code>php laravel_feature_test_api_demo.php</code>:</p>

<pre><code class="language-php">&lt;?php
declare(strict_types=1);

$peminjamanRapi = [
    "id" =&gt; 10,
    "judul_buku" =&gt; "Dasar PHP",
    "nama_anggota" =&gt; "Budi",
    "status" =&gt; "aktif",
    "status_label" =&gt; "Sedang dipinjam",
];

$peminjamanBocor = [
    "id" =&gt; 11,
    "anggota_id" =&gt; 2,
    "judul_buku" =&gt; "Belajar Laravel",
    "nama_anggota" =&gt; "Siti",
    "status" =&gt; "aktif",
];

function cekBentukJson(array $json): array
{
    $wajib = ["id", "judul_buku", "nama_anggota", "status", "status_label"];
    $gagal = [];

    foreach ($wajib as $field) {
        if (! array_key_exists($field, $json)) {
            $gagal[] = "missing field: {$field}";
        }
    }
    if (array_key_exists("anggota_id", $json)) {
        $gagal[] = "anggota_id must not exist";
    }

    return ["lolos" =&gt; $gagal === [], "gagal" =&gt; $gagal];
}

function demo(string $judul, array $json): void
{
    echo "=== {$judul} ===", PHP_EOL;
    $hasil = cekBentukJson($json);
    echo $hasil["lolos"] ? "PASS" : "FAIL", PHP_EOL;
    if ($hasil["gagal"] !== []) {
        echo implode(", ", $hasil["gagal"]), PHP_EOL;
    }
    echo json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL, PHP_EOL;
}

demo("Tidy slip — should pass", $peminjamanRapi);
demo("Leaking slip — should fail", $peminjamanBocor);
demo("Without status_label — should fail", [
    "id" =&gt; 12,
    "judul_buku" =&gt; "PHP Lanjut",
    "nama_anggota" =&gt; "Ani",
    "status" =&gt; "kembali",
]);
</code></pre>
<p><strong>Beginner:</strong> the three scenarios above show a sensible pattern: tidy slip passes, leaking slip fails, missing required field fails. Function <code>cekBentukJson</code> is the core logic; <code>demo(...)</code> only wraps output so the terminal result is easy to read — similar to what <code>assertJsonPath</code> does in Laravel.</p>

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
      <td>Test passes but browser JSON is wrong</td>
      <td>Test does not call the same route as production</td>
      <td>Make sure <code>getJson</code> hits the correct URL</td>
    </tr>
    <tr>
      <td><code>anggota_id</code> leaks but test does not fail</td>
      <td>Forgot to assert hidden fields</td>
      <td>Add <code>assertJsonMissingPath("data.anggota_id")</code></td>
    </tr>
    <tr>
      <td>Test fails although Resource is correct</td>
      <td>Test data not prepared (empty factory/seeder)</td>
      <td>Set up data first in the Arrange step</td>
    </tr>
    <tr>
      <td><code>assertJsonPath</code> always fails</td>
      <td>Wrong JSON path — for example missing <code>data.</code> prefix</td>
      <td>Inspect raw response with <code>curl.exe</code> or <code>dump()</code></td>
    </tr>
    <tr>
      <td>Only status checked, not content</td>
      <td>Only <code>assertStatus(200)</code> without JSON asserts</td>
      <td>Add asserts for every required field from Resource</td>
    </tr>
    <tr>
      <td><code>php artisan test</code> not recognized</td>
      <td>Terminal not from Laragon/XAMPP</td>
      <td>Open Laragon Terminal or XAMPP Shell, <code>cd</code> into the project</td>
    </tr>
  </tbody>
</table>

<h2>Short practice</h2>
<ol>
  <li>Change the demo: add an assert that <code>status_label</code> for status <code>kembali</code> must be &ldquo;Sudah kembali&rdquo;.</li>
  <li>Explain to a friend: difference between manual check every morning vs automated checklist every night — use the library clerk analogy.</li>
  <li>Write one sentence: why <code>assertJsonMissingPath</code> matters after Resource hides <code>anggota_id</code>.</li>
</ol>

<h2>FAQ</h2>
<p><strong>Does Feature Test replace Resource?</strong><br>
No. Resource from <a href="/artikel/laravel-api-resource-json">API Resource: Rapikan Bentuk JSON (#67)</a> tidies the response shape. Feature Test makes sure that shape <strong>stays correct</strong> whenever code changes.</p>
<p><strong>Must I use Pest instead of PHPUnit?</strong><br>
Not required. PHPUnit is Laravel&rsquo;s default and this article&rsquo;s focus. Pest is optional when you are comfortable — the pattern is the same: set up, run, check.</p>
<p><strong>Which tools should I open first?</strong><br>
Explorer to confirm the project folder (<code>tests\Feature</code> + Controllers/Resources), one terminal for the PHP demo, editor for the test file. If <code>serve</code> is alive, a second terminal for optional <code>curl.exe</code> comparison.</p>
<p><strong>Where should I test the snippets?</strong><br>
The middle step (array check functions) is copied into <code>uji-cek.php</code>, then run with <code>php uji-cek.php</code>. The full demo is tested with <code>php laravel_feature_test_api_demo.php</code>. Laravel snippets are pasted into <code>tests\Feature\PeminjamanResourceTest.php</code>; run the suite with <code>php artisan test</code> or <code>php vendor/bin/phpunit</code>.</p>
<p><strong>Where next?</strong><br>
The natural next step is <strong>Rate Limiting</strong> — limit spam requests to the mini-library API.</p>

<h2>Conclusion</h2>
<p>You locked the JSON response shape from Resource with <strong>automated tests</strong>: plain PHP checks first in <code>uji-cek.php</code>, pass/fail demo in <code>laravel_feature_test_api_demo.php</code>, then Laravel <strong>Feature Test</strong> snippets with <code>assertStatus</code>, <code>assertJsonPath</code>, and <code>assertJsonMissingPath</code>. An automated checklist every night — not a manual check every morning.</p>
<blockquote>
  <p><strong>Seri 5 progress:</strong> step <strong>#68 (this article)</strong> · <strong>5/7</strong> Laravel Lanjutan · prerequisite: <a href="/artikel/laravel-api-resource-json">API Resource: Rapikan Bentuk JSON (#67)</a> LIVE. Next: <strong>Rate Limiting</strong>.</p>
</blockquote>
HTML;

        return str_replace(
            [
                'Seri 5: Laravel Lanjutan',
                'API Resource: Rapikan Bentuk JSON (#67)',
                'Instal PHP, Composer &amp; Proyek Laravel (#56)',
                'Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)',
                'Rate Limiting',
                'Laravel Lanjutan',
            ],
            [
                'Seri 5: Advanced Laravel',
                'API Resource: Tidy JSON Response Shape (#67)',
                'Install PHP, Composer &amp; Your First Laravel Project (#56)',
                'Folder Structure, <code>.env</code> &amp; Artisan Laravel (#57)',
                'Rate Limiting',
                'Advanced Laravel',
            ],
            $html
        );
    }
}
