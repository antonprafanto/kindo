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

        $slug = 'laravel-controller-service-eloquent';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        // Slug v2 Controller/Service/Eloquent — tidak menyentuh cover_image

        foreach ([
            'laravel' => 'laravel',
            'php' => 'php',
            'api' => 'api',
            'http' => 'http',
            'web' => 'web',
            'json' => 'json',
            'eloquent' => 'eloquent',
        ] as $tagSlug => $tagName) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagName]);
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $webCat->id,
                'title'           => 'Controller, Service & Eloquent Laravel',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'Controller, Service & Eloquent Laravel untuk Pemula',
                'seo_description' => 'Seri 4 #60: pindahkan daftar buku dari route ke Controller + Service, lalu baca tabel dengan Eloquent — ramah awam.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        $prevPublished = Article::where('slug', 'laravel-request-validasi-api')->value('published_at');
        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        } elseif ($prevPublished && $article->published_at <= $prevPublished) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['laravel', 'php', 'api', 'http', 'web', 'json', 'eloquent'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel ke-60 berhasil dipublish: '.$article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — dari pintu ke loket &amp; dapur</h2>
<p>Artikel ini adalah <strong>#60 (ini)</strong> di <strong>Seri 4: Pemrograman Web Lanjut v2</strong>. Di <a href="/artikel/laravel-request-validasi-api">Request &amp; Form Request: Menjaga Input API (#59)</a> kamu sudah punya satpam di pintu. Sekarang langkah <strong>5/8</strong>: pindahkan kerja dari file route ke <strong>Controller</strong> (loket), <strong>Service</strong> (dapur), dan <strong>Eloquent</strong> (cara baca baris di tabel).</p>
<p><strong>Awam:</strong> kalau semua aturan ditulis di <code>routes/web.php</code>, file pintu cepat penuh. Loket menerima tamu, dapur menyiapkan daftar buku, rak (tabel) menyimpan data. Kita pisahkan biar rapi.</p>
<p>Domain tetap <strong>perpustakaan mini</strong>. Hari ini fokus: daftar buku lewat Controller + Service, lalu baca dari tabel dengan model Eloquent. Login/auth datang di langkah berikutnya.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> sudah selesai <a href="/artikel/laravel-request-validasi-api">Request &amp; Form Request: Menjaga Input API (#59)</a> dan <a href="/artikel/laravel-routing-json-perpustakaan-api">Routing &amp; Jawaban JSON API Perpustakaan (#58)</a> — <code>GET /api/buku</code> pernah jalan. Fondasi di <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a> dan <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a>. Pakai <strong>Laravel 13+</strong> — butuh <strong>PHP 8.3+</strong>.</p>
</blockquote>

<h2>Spesifikasi fitur — apa yang kita kuasai?</h2>
<p>Daftar singkat yang bisa kamu centang di akhir artikel:</p>
<ol>
  <li>Membuat <strong>Controller</strong> dengan Artisan dan menghubungkan route ke loket.</li>
  <li>Membuat <strong>Service</strong> sederhana yang menyiapkan daftar buku.</li>
  <li>Mengenal <strong>Eloquent</strong> (model) sebagai cara baca baris di tabel <code>bukus</code>.</li>
  <li>Menguji <code>GET /api/buku</code> di browser setelah <code>php artisan serve</code> hidup.</li>
</ol>
<p><strong>Awam:</strong> urutan nyaman: <strong>buka alat -&gt; buat model/tabel -&gt; buat service -&gt; buat controller -&gt; hubungkan route -&gt; uji di browser</strong>. Belum perlu login.</p>

<h2>Istilah — ringkas untuk loket, dapur, rak</h2>
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
      <td>Controller</td>
      <td>Loket — menerima permintaan, memanggil dapur, mengembalikan jawaban</td>
      <td><code>BukuController</code></td>
    </tr>
    <tr>
      <td>Service</td>
      <td>Dapur — aturan bisnis ringan (ambil daftar buku)</td>
      <td><code>BukuService</code></td>
    </tr>
    <tr>
      <td>Eloquent / Model</td>
      <td>Cara bicara ke tabel database lewat class PHP</td>
      <td><code>Buku::query()-&gt;get()</code></td>
    </tr>
    <tr>
      <td>Migrasi</td>
      <td>Cetak biru tabel yang dijalankan Artisan</td>
      <td><code>php artisan migrate</code></td>
    </tr>
  </tbody>
</table>
<p>Urutan belajar: kenali tiga peran -&gt; buat file -&gt; hubungkan route -&gt; uji JSON.</p>

<h2>Kenapa pindah dari route?</h2>
<p>Di artikel routing &amp; validasi sebelumnya, banyak logika masih di <code>routes/web.php</code>. Itu bagus untuk belajar pintu. Begitu daftar buku + validasi + tabel bertambah, pintu jadi ramai.</p>
<p><strong>Awam:</strong> sama seperti perpustakaan — satpam tetap di pintu, tapi petugas loket dan dapur punya meja sendiri. Route hanya menunjuk: “tamu daftar buku -&gt; ke loket BukuController”.</p>
<p>Artikel ini tetap <strong>install-dari-nol</strong> untuk file baru: kita pakai Artisan <code>make:model</code> / <code>make:controller</code> dan membuat Service manual. Tidak ada paket Composer baru. Fondasi tetap merujuk <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a>.</p>

<h2>Alur — dari URL sampai daftar buku</h2>
<figure role="img" aria-label="Alur Controller Service Eloquent: browser, controller, service, tabel" style="background:#F5F5F0;border:2px solid #1a1a1a;border-radius:12px;padding:1rem;margin:1.25rem 0">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 760 240" width="100%" height="auto" role="img" aria-labelledby="laravel60title">
  <title id="laravel60title">Alur: Browser -&gt; Controller -&gt; Service -&gt; Eloquent/tabel</title>
  <defs>
    <marker id="laravel60cseArrow" markerWidth="8" markerHeight="8" refX="6" refY="4" orient="auto">
      <path d="M0,0 L8,4 L0,8 Z" fill="#1a1a1a"/>
    </marker>
  </defs>
  <text x="24" y="28" fill="#1a1a1a" font-size="15" font-weight="700">Alur: Browser -&gt; Controller -&gt; Service -&gt; Tabel</text>
  <rect x="24" y="48" width="148" height="72" rx="10" fill="#2979FF"/>
  <text x="98" y="80" text-anchor="middle" fill="#fff" font-size="15" font-weight="700">Browser</text>
  <text x="98" y="100" text-anchor="middle" fill="#fff" font-size="12">GET /api/buku</text>
  <line x1="180" y1="84" x2="220" y2="84" stroke="#1a1a1a" stroke-width="3" marker-end="url(#laravel60cseArrow)"/>
  <rect x="228" y="48" width="148" height="72" rx="10" fill="#00C853"/>
  <text x="302" y="80" text-anchor="middle" fill="#fff" font-size="15" font-weight="700">Controller</text>
  <text x="302" y="100" text-anchor="middle" fill="#fff" font-size="12">loket</text>
  <line x1="384" y1="84" x2="424" y2="84" stroke="#1a1a1a" stroke-width="3" marker-end="url(#laravel60cseArrow)"/>
  <rect x="432" y="48" width="148" height="72" rx="10" fill="#FF7A2F"/>
  <text x="506" y="80" text-anchor="middle" fill="#fff" font-size="15" font-weight="700">Service</text>
  <text x="506" y="100" text-anchor="middle" fill="#fff" font-size="12">dapur</text>
  <line x1="588" y1="84" x2="628" y2="84" stroke="#1a1a1a" stroke-width="3" marker-end="url(#laravel60cseArrow)"/>
  <rect x="636" y="48" width="100" height="72" rx="10" fill="#1a1a1a"/>
  <text x="686" y="80" text-anchor="middle" fill="#fff" font-size="14" font-weight="700">Tabel</text>
  <text x="686" y="100" text-anchor="middle" fill="#fff" font-size="11">Eloquent</text>
  <text x="24" y="160" fill="#1a1a1a" font-size="13">Setelah validasi: pindahkan daftar buku ke loket + dapur, baca dari tabel,</text>
  <text x="24" y="182" fill="#1a1a1a" font-size="13">lalu uji lagi di browser. Login/auth datang belakangan.</text>
  <text x="24" y="214" fill="#1a1a1a" font-size="13">Urutan ini mengikuti langkah #60 (ini) — belum auth API.</text>
</svg>
<figcaption style="color:#1a1a1a;margin-top:.5rem"><strong>#60 (ini)</strong>: browser -&gt; controller -&gt; service -&gt; Eloquent/tabel.</figcaption>
</figure>

<h2>Persiapan — alat yang kamu buka</h2>
<p><strong>Alat yang dipakai di artikel ini</strong> (sudah dari fondasi <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a> / <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a> — tidak ada unduhan wajib baru):</p>
<ul>
  <li><strong>Explorer</strong> — melihat folder <code>app</code>, <code>database/migrations</code>, dan membuat folder <code>app/Services</code> jika belum ada.</li>
  <li><strong>Terminal</strong> — Laragon: menu <em>Terminal</em> · XAMPP: tombol <em>Shell</em>. Untuk <code>cd</code>, <code>php artisan serve</code>, <code>make:model</code>, <code>make:controller</code>, dan <code>migrate</code>. Jangan asal buka CMD/PowerShell dari Start Menu.</li>
  <li><strong>Terminal kedua</strong> — wajib hari ini: terminal pertama menjalankan <code>serve</code> (lampu toko). Terminal kedua untuk Artisan <code>make:…</code> dan perintah lain tanpa mematikan lampu.</li>
  <li><strong>Editor teks</strong> — Notepad / VS Code — membuka file Controller, Service, Model, migrasi, dan <code>routes/web.php</code>. Cara cepat Windows: <code>notepad path\ke\file.php</code> dari terminal kedua.</li>
  <li><strong>Browser</strong> — menguji <code>http://127.0.0.1:8000/api/buku</code> setelah loket terhubung.</li>
</ul>
<p>Buka terminal Laragon/Shell XAMPP, masuk folder proyek dulu:</p>
<pre><code class="language-bash">cd C:\laragon\www\perpustakaan-api
</code></pre>
<p>Di XAMPP biasanya: <code>cd C:\xampp\htdocs\perpustakaan-api</code>. Sesuaikan path jika foldermu beda.</p>
<p>Nyalakan lampu toko di <strong>terminal pertama</strong>:</p>
<pre><code class="language-bash">php artisan serve
</code></pre>
<p>Biarkan jendela itu hidup. Buka <strong>terminal kedua</strong> (Laragon/Shell lagi), <code>cd</code> ke folder proyek yang sama — di sini kamu mengetik perintah Artisan berikutnya.</p>
<p><strong>Awam:</strong> Terminal 1 = lampu toko. Terminal 2 = tangan membuat file &amp; migrasi. Editor = menulis isi loket/dapur. Browser = melihat slip JSON. Matikan <code>serve</code> dengan Ctrl+C hanya di terminal yang menjalankannya.</p>

<h2>Model &amp; tabel — Eloquent dari nol</h2>
<p>Di <strong>terminal kedua</strong> (folder <code>perpustakaan-api</code>):</p>
<pre><code class="language-bash">php artisan make:model Buku -m
</code></pre>
<p>Artisan membuat dua berkas: model <code>app/Models/Buku.php</code> dan file migrasi di <code>database/migrations/</code> (nama berisi <code>create_bukus_table</code>).</p>
<p>Buka file migrasi di editor. Cara awam:</p>
<ol>
  <li><strong>Explorer:</strong> masuk folder <code>database/migrations</code>, pilih file <strong>terbaru</strong> yang namanya mengandung <code>create_bukus_table</code>, buka dengan Notepad/VS Code.</li>
  <li><strong>Atau terminal kedua</strong> — daftar dulu, lalu buka dengan nama yang muncul:</li>
</ol>
<pre><code class="language-bash">dir database\migrations
notepad database\migrations\nama_file_create_bukus_table.php
</code></pre>
<p>Ganti <code>nama_file_create_bukus_table.php</code> dengan nama lengkap dari hasil <code>dir</code>. Di dalam fungsi <code>up()</code>, pastikan ada kolom sederhana (sesuaikan cuplikan ke kerangka yang Artisan buat):</p>
<pre><code class="language-php">// Cuplikan migrasi — kolom judul &amp; penulis
$table-&gt;id();
$table-&gt;string('judul');
$table-&gt;string('penulis');
$table-&gt;timestamps();
</code></pre>
<p>Simpan, lalu di terminal kedua:</p>
<pre><code class="language-bash">php artisan migrate
</code></pre>
<p>Buka model di editor (dari terminal kedua: <code>notepad app\Models\Buku.php</code>). Tambahkan daftar kolom yang boleh diisi massal (cuplikan — tempel di dalam class):</p>
<pre><code class="language-php">// Cuplikan app/Models/Buku.php
protected $fillable = ['judul', 'penulis'];
</code></pre>
<p><strong>Awam:</strong> <strong>Eloquent</strong> = cara Laravel membaca/menulis baris tabel lewat class Model. <code>Buku</code> = cetakan satu baris buku. <code>migrate</code> = membangun rak tabel di database (SQLite dari fondasi denah sudah cukup).</p>
<p><strong>Install-dari-nol:</strong> tidak perlu driver ekstra. Pastikan <code>DB_CONNECTION=sqlite</code> dan file <code>database/database.sqlite</code> sudah ada seperti di <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a>.</p>

<h2>Service — dapur daftar buku</h2>
<p>Buat folder <code>app/Services</code> jika belum ada. Di <strong>terminal kedua</strong> (masih di folder proyek):</p>
<pre><code class="language-bash">mkdir app\Services
</code></pre>
<p>Kalau terminal bilang folder sudah ada — lanjut saja, itu normal. Lalu buat file <code>app/Services/BukuService.php</code> (paling mudah: <code>notepad app\Services\BukuService.php</code>, tempel cuplikan di bawah, simpan):</p>
<pre><code class="language-php">&lt;?php

namespace App\Services;

use App\Models\Buku;
use Illuminate\Support\Collection;

class BukuService
{
    public function daftar(): Collection
    {
        return Buku::query()-&gt;orderBy('id')-&gt;get(['id', 'judul', 'penulis']);
    }
}
</code></pre>
<p><strong>Awam:</strong> Service = dapur. Loket tidak perlu tahu cara query tabel — cukup minta <code>daftar()</code>. Kalau tabel masih kosong, JSON <code>data</code> bisa berupa array kosong — itu normal; isi baris buku boleh belakangan (bukan wajib hari ini).</p>

<h2>Controller — loket</h2>
<p>Masih di <strong>terminal kedua</strong>:</p>
<pre><code class="language-bash">php artisan make:controller BukuController
</code></pre>
<p>Buka <code>app/Http/Controllers/BukuController.php</code> di editor (boleh <code>notepad app\Http\Controllers\BukuController.php</code>). <strong>Jangan hapus</strong> baris <code>namespace</code> dan kerangka <code>class BukuController extends Controller</code>. Tambahkan baris <code>use</code> di atas class, lalu tempel method <code>index</code> di dalam class:</p>
<pre><code class="language-php">// Cuplikan BukuController — loket daftar buku
use App\Services\BukuService;
use Illuminate\Http\JsonResponse;

public function index(BukuService $bukuService): JsonResponse
{
    return response()-&gt;json([
        'message' =&gt; 'Daftar buku perpustakaan mini',
        'data' =&gt; $bukuService-&gt;daftar(),
    ]);
}
</code></pre>
<p><strong>Awam:</strong> parameter <code>BukuService $bukuService</code> diisi otomatis oleh Laravel (seperti dipanggilkan ke dapur). Loket hanya menyusun jawaban JSON.</p>

<h2>Hubungkan route ke loket</h2>
<p>Buka <code>routes/web.php</code> di editor (atau <code>notepad routes\web.php</code> dari terminal kedua). Ganti / sesuaikan route GET daftar buku menjadi:</p>
<pre><code class="language-php">// Cuplikan routes/web.php — arahkan ke Controller
use App\Http\Controllers\BukuController;

Route::get('/api/buku', [BukuController::class, 'index']);
</code></pre>
<p>Simpan file. Pastikan <code>php artisan serve</code> masih hidup di terminal pertama.</p>
<p><strong>Awam:</strong> baris itu artinya: tamu yang GET <code>/api/buku</code> dilayani loket <code>BukuController</code> fungsi <code>index</code> — bukan lagi fungsi panjang di dalam file route.</p>

<h2>Uji di browser</h2>
<p>Dengan <code>serve</code> masih hidup, buka browser ke:</p>
<pre><code class="language-bash">http://127.0.0.1:8000/api/buku
</code></pre>
<p>Kamu harus melihat JSON berisi <code>message</code> dan <code>data</code>. Kalau tabel masih kosong, <code>data</code> bisa <code>[]</code> — struktur tetap benar. Kalau error class tidak ditemukan, cek: file Service/Controller sudah disimpan? Nama folder <code>Services</code> huruf besar S? Sudah <code>cd</code> di proyek yang sama?</p>
<p><strong>Awam:</strong> browser = mata melihat slip. Terminal = tangan membuat file. Jangan menguji URL sebelum <code>serve</code> hidup.</p>

<h2>Pola Dasar — empat langkah loket bersih</h2>
<figure role="img" aria-label="Pola Dasar empat langkah Controller Service Eloquent" style="background:#F5F5F0;border:2px solid #1a1a1a;border-radius:12px;padding:1rem;margin:1.25rem 0">
<ol style="list-style:none;padding:0;margin:0;display:grid;gap:.75rem">
  <li style="display:flex;gap:.75rem;align-items:flex-start">
    <span style="flex:0 0 2rem;height:2rem;border-radius:999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div><strong style="color:#1a1a1a">Buka alat</strong><br><span style="color:#1a1a1a">Terminal 1 <code>serve</code> · Terminal 2 Artisan · Editor · Browser.</span></div>
  </li>
  <li style="display:flex;gap:.75rem;align-items:flex-start">
    <span style="flex:0 0 2rem;height:2rem;border-radius:999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div><strong style="color:#1a1a1a">Model + migrate</strong><br><span style="color:#1a1a1a"><code>make:model Buku -m</code> lalu isi kolom &amp; <code>migrate</code>.</span></div>
  </li>
  <li style="display:flex;gap:.75rem;align-items:flex-start">
    <span style="flex:0 0 2rem;height:2rem;border-radius:999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div><strong style="color:#1a1a1a">Service + Controller</strong><br><span style="color:#1a1a1a">Dapur <code>daftar()</code> · loket <code>index</code> mengembalikan JSON.</span></div>
  </li>
  <li style="display:flex;gap:.75rem;align-items:flex-start">
    <span style="flex:0 0 2rem;height:2rem;border-radius:999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div><strong style="color:#1a1a1a">Route + uji</strong><br><span style="color:#1a1a1a">Arahkan <code>GET /api/buku</code> ke Controller, cek di browser.</span></div>
  </li>
</ol>
</figure>

<h2>Demo loket &amp; dapur — file mandiri</h2>
<p>Latihan ide tanpa mengubah proyek Laravel:</p>
<ol>
  <li>Buka editor teks, buat file baru, tempel cuplikan di bawah, simpan sebagai <code>laravel_controller_service_eloquent_demo.php</code> (boleh di Desktop).</li>
  <li>Buka terminal di folder tempat file itu disimpan (Explorer: Shift+klik kanan -&gt; “Open in Terminal” / “Buka di Terminal”, atau <code>cd</code> manual). Pastikan <code>php -v</code> sudah jalan.</li>
  <li>Jalankan: <code>php laravel_controller_service_eloquent_demo.php</code> — layar menampilkan simulasi loket -&gt; dapur -&gt; daftar buku.</li>
</ol>
<p>File ini <strong>mensimulasikan</strong> peran — tidak mengubah proyek Laravel-mu:</p>
<pre><code class="language-php">&lt;?php

declare(strict_types=1);

/**
 * Demo loket (Controller) &amp; dapur (Service) — simulasi teks untuk awam.
 */

function dapurDaftarBuku(): array
{
    return [
        ['id' =&gt; 1, 'judul' =&gt; 'Belajar PHP', 'penulis' =&gt; 'Ayu'],
        ['id' =&gt; 2, 'judul' =&gt; 'Dasar Laravel', 'penulis' =&gt; 'Budi'],
    ];
}

function loketIndex(): array
{
    return [
        'message' =&gt; 'Daftar buku perpustakaan mini',
        'data' =&gt; dapurDaftarBuku(),
    ];
}

function demo(): void
{
    echo "=== Simulasi loket -&gt; dapur ===", PHP_EOL;
    echo json_encode(loketIndex(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), PHP_EOL;
    echo PHP_EOL, "Langkah sungguhan: Model+migrate -&gt; BukuService -&gt; BukuController -&gt; route -&gt; browser.", PHP_EOL;
}

demo();
</code></pre>
<p><strong>Awam:</strong> <code>demo()</code> hanya menampilkan JSON contoh di terminal. Setelah paham, kerjakan langkah sungguhan di folder <code>perpustakaan-api</code>. <code>declare(strict_types=1);</code> membuat tipe lebih ketat — boleh diikuti, tidak wajib dihafal.</p>

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
      <td>Class BukuService not found</td>
      <td>File/folder salah atau belum disimpan</td>
      <td>Cek path <code>app/Services/BukuService.php</code> dan huruf besar <code>S</code></td>
    </tr>
    <tr>
      <td>Connection refused</td>
      <td><code>serve</code> belum hidup</td>
      <td>Nyalakan di terminal pertama setelah <code>cd</code> ke proyek</td>
    </tr>
    <tr>
      <td>Bingung perintah diketik di mana</td>
      <td>Terminal salah / Start Menu</td>
      <td>Lihat <strong>Persiapan — alat yang kamu buka</strong>; pakai Laragon/Shell XAMPP</td>
    </tr>
    <tr>
      <td>Tabel/migrate error</td>
      <td>SQLite belum siap atau <code>DB_CONNECTION</code> masih mysql</td>
      <td>Kembali ke <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a>: file <code>database.sqlite</code> + <code>DB_CONNECTION=sqlite</code></td>
    </tr>
    <tr>
      <td>Bingung file migrasi mana yang dibuka</td>
      <td>Banyak file di folder <code>migrations</code></td>
      <td>Pilih yang namanya mengandung <code>create_bukus_table</code> — pakai <code>dir database\migrations</code> atau Explorer</td>
    </tr>
    <tr>
      <td><code>data</code> kosong <code>[]</code></td>
      <td>Tabel belum diisi baris</td>
      <td>Normal di awal — struktur JSON sudah benar; isi data belakangan</td>
    </tr>
  </tbody>
</table>

<h2>Latihan</h2>
<ol>
  <li>Jalankan demo PHP di atas, lalu bandingkan strukturnya dengan JSON di browser setelah Controller hidup.</li>
  <li>Jelaskan ke teman: beda singkat loket (Controller), dapur (Service), dan rak (Eloquent/tabel).</li>
  <li>Pastikan kamu bisa mengulang: terminal 1 <code>serve</code>, terminal 2 <code>make:…</code>, editor simpan, browser uji.</li>
</ol>

<h2>FAQ</h2>
<p><strong>Terminal mana yang harus dibuka?</strong><br>
Laragon: menu Terminal · XAMPP: tombol Shell. Lalu <code>cd</code> ke <code>perpustakaan-api</code>. Satu jendela untuk <code>serve</code>, jendela kedua untuk Artisan.</p>
<p><strong>Harus VS Code?</strong><br>
Tidak. Notepad cukup untuk menempel cuplikan. Dari terminal kedua: <code>notepad app\Services\BukuService.php</code> atau path file lain. VS Code membantu kalau kamu suka.</p>
<p><strong>File migrasi yang mana?</strong><br>
Setelah <code>make:model Buku -m</code>, di terminal kedua ketik <code>dir database\migrations</code> — pilih file yang namanya mengandung <code>create_bukus_table</code>, lalu <code>notepad database\migrations\…</code>. Atau lewat Explorer ke folder yang sama.</p>
<p><strong>Kenapa Service dibuat manual?</strong><br>
Supaya awam melihat folder <code>app/Services</code> dengan jelas. Intinya: dapur = class berisi fungsi daftar buku.</p>
<p><strong>Apa itu Eloquent tanpa istilah sulit?</strong><br>
Eloquent = cara membaca/menulis tabel lewat model PHP. <code>Buku::query()-&gt;get()</code> ≈ “ambil semua baris buku”.</p>
<p><strong>Apa hubungan dengan artikel validasi?</strong><br>
<a href="/artikel/laravel-request-validasi-api">Request &amp; Form Request: Menjaga Input API (#59)</a> menjaga slip masuk. <strong>#60 (ini)</strong> merapikan siapa yang melayani daftar buku (loket + dapur + tabel).</p>
<p><strong>Ke mana setelah ini?</strong><br>
Berikutnya: <a href="/artikel/laravel-auth-api-dasar">Auth API Dasar: Login &amp; Kartu Anggota (#61)</a> — kartu anggota supaya tidak semua orang boleh masuk pintu staf.</p>

<h2>Kesimpulan</h2>
<p>Kamu sudah memindahkan daftar buku ke pola rapi: Controller (loket), Service (dapur), Eloquent (baca tabel), diuji lewat browser saat <code>artisan serve</code> hidup. Ini langkah <strong>5/8</strong> jalur Laravel di Seri 4.</p>
<blockquote>
  <p><strong>Seri 4 progress:</strong> langkah <strong>#60 (ini)</strong> · <strong>5/8</strong> jalur Laravel · prasyarat: <a href="/artikel/laravel-request-validasi-api">Request &amp; Form Request: Menjaga Input API (#59)</a> LIVE. Berikutnya: <a href="/artikel/laravel-auth-api-dasar">Auth API Dasar: Login &amp; Kartu Anggota (#61)</a>.</p>
</blockquote>
HTML;
    }
}
