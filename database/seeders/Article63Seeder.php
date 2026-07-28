<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article63Seeder extends Seeder
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
            'json' => 'json',
            'auth' => 'auth',
        ] as $tagSlug => $tagName) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagName]);
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'            => $admin->id,
                'category_id'        => $webCat->id,
                'title'              => 'CRUD API Buku: Ubah & Hapus (Laravel)',
                'title_en'           => 'CRUD Book API: Update & Delete (Laravel)',
                'excerpt_en'         => 'Seri 4 #63: finish your Laravel book CRUD API — update one book (PUT) and delete one book (DELETE) with a Sanctum staff card, beginner-friendly tools-first walkthrough.',
                'body'               => $this->body(),
                'body_en'            => $this->bodyEn(),
                'status'             => 'published',
                'is_featured'        => false,
                'seo_title'          => 'CRUD API Buku Laravel: Ubah & Hapus dengan Kartu Sanctum',
                'seo_title_en'       => 'Laravel Book CRUD API: Update & Delete with Sanctum Token',
                'seo_description'    => 'Seri 4 #63: lengkapi CRUD API buku Laravel — ubah (PUT) dan hapus (DELETE) satu buku dengan kartu Sanctum, ramah awam.',
                'seo_description_en' => 'Seri 4 #63: complete Laravel book CRUD — update (PUT) and delete (DELETE) one book with a Sanctum token, with second-terminal curl.exe / PowerShell / Postman steps for beginners.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        $prevPublished = Article::where('slug', 'capstone-api-perpustakaan-laravel')->value('published_at');
        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        } elseif ($prevPublished && $article->published_at <= $prevPublished) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['laravel', 'php', 'api', 'http', 'web', 'json', 'auth'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel ke-63 berhasil dipublish: '.$article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — melengkapi rak perpustakaan</h2>
<p>Artikel ini adalah <strong>#63 (ini)</strong> di <strong>Seri 4: Pemrograman Web Lanjut v2</strong> — langkah <strong>8/8</strong>, penutup jalur Laravel dari nol.</p>
<p>Di <a href="/artikel/capstone-api-perpustakaan-laravel">Capstone: API Perpustakaan (#62)</a> staf sudah bisa <strong>membaca</strong> katalog dan <strong>menambah</strong> buku dengan kartu. Tapi hidup nyata butuh dua hal lagi: <strong>memperbaiki</strong> data yang salah tulis, dan <strong>menghapus</strong> buku yang sudah tidak ada di rak.</p>
<p><strong>Awam:</strong> hari ini kita menambah dua loket terakhir. Loket <em>ubah</em> = memperbaiki kartu katalog yang salah tulis. Loket <em>hapus</em> = menarik buku dari rak. Keduanya wajib pakai kartu staf, sama seperti loket tambah.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> sudah selesai <a href="/artikel/capstone-api-perpustakaan-laravel">Capstone: API Perpustakaan (#62)</a> (baca + login + tambah sudah jalan), <a href="/artikel/laravel-auth-api-dasar">Auth API Dasar: Login &amp; Kartu Anggota (#61)</a> (kartu Sanctum), dan <a href="/artikel/laravel-request-validasi-api">Request &amp; Form Request: Menjaga Input API (#59)</a> (satpam slip). Fondasi <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a> / <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a>. Pakai <strong>Laravel 13+</strong> — butuh <strong>PHP 8.3+</strong>.</p>
</blockquote>

<h2>Spesifikasi fitur — apa yang “selesai” hari ini?</h2>
<p>Dua kalimat saja:</p>
<ol>
  <li><strong>Ubah satu buku</strong> — <code>PUT /api/buku/1</code> dengan kartu Bearer + slip judul/penulis yang valid.</li>
  <li><strong>Hapus satu buku</strong> — <code>DELETE /api/buku/1</code> dengan kartu Bearer (tidak perlu slip).</li>
</ol>
<p>Angka <code>1</code> di alamat itu adalah <strong>nomor buku</strong> (kolom <code>id</code> di database). Kalau nomornya tidak ada di rak, jawabannya <code>404</code> — bukan error merah.</p>
<p><strong>Awam:</strong> setelah hari ini, rak perpustakaanmu punya empat gerakan lengkap: lihat, tambah, perbaiki, buang. Itulah yang orang sebut <strong>CRUD</strong>.</p>

<figure style="background:#F5F5F0;border:2px solid #1a1a1a;border-radius:12px;padding:1rem;margin:1.25rem 0">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 760 240" width="100%" height="auto" role="img" aria-label="Empat gerakan CRUD: baca, tambah, ubah, hapus">
  <title>Empat gerakan CRUD pada rak buku</title>
  <defs>
    <marker id="laravel63crudArrow" markerWidth="8" markerHeight="8" refX="6" refY="4" orient="auto">
      <path d="M0,0 L8,4 L0,8 Z" fill="#1a1a1a"/>
    </marker>
  </defs>
  <rect x="12" y="36" width="164" height="88" rx="10" fill="#ffffff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="94" y="72" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Baca</text>
  <text x="94" y="98" text-anchor="middle" fill="#1a1a1a" font-size="12">GET /api/buku</text>
  <line x1="182" y1="80" x2="212" y2="80" stroke="#1a1a1a" stroke-width="3" marker-end="url(#laravel63crudArrow)"/>
  <rect x="220" y="36" width="164" height="88" rx="10" fill="#ffffff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="302" y="72" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Tambah</text>
  <text x="302" y="98" text-anchor="middle" fill="#1a1a1a" font-size="12">POST /api/buku</text>
  <line x1="390" y1="80" x2="420" y2="80" stroke="#1a1a1a" stroke-width="3" marker-end="url(#laravel63crudArrow)"/>
  <rect x="428" y="36" width="150" height="88" rx="10" fill="#ffffff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="503" y="72" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Ubah</text>
  <text x="503" y="98" text-anchor="middle" fill="#1a1a1a" font-size="12">PUT /api/buku/1</text>
  <line x1="584" y1="80" x2="614" y2="80" stroke="#1a1a1a" stroke-width="3" marker-end="url(#laravel63crudArrow)"/>
  <rect x="622" y="36" width="126" height="88" rx="10" fill="#ffffff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="685" y="72" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Hapus</text>
  <text x="685" y="98" text-anchor="middle" fill="#1a1a1a" font-size="12">DELETE /api/buku/1</text>
  <text x="20" y="176" fill="#1a1a1a" font-size="13">Dua kotak pertama sudah kamu buat di Capstone. Dua kotak terakhir dipasang hari ini.</text>
  <text x="20" y="204" fill="#1a1a1a" font-size="13">Ubah &amp; hapus selalu menyebut nomor buku di alamat - dan selalu butuh kartu.</text>
</svg>
<figcaption style="color:#1a1a1a">CRUD lengkap: baca &amp; tambah sudah jalan sejak Capstone, ubah &amp; hapus dipasang hari ini.</figcaption>
</figure>

<h2>Istilah — ringkas ubah &amp; hapus</h2>
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
      <td>Empat gerakan data: lihat, tambah, ubah, buang</td>
      <td>Hari ini melengkapi dua yang terakhir</td>
    </tr>
    <tr>
      <td>PUT</td>
      <td>Cara HTTP untuk “ganti isi catatan ini”</td>
      <td><code>PUT /api/buku/1</code></td>
    </tr>
    <tr>
      <td>DELETE</td>
      <td>Cara HTTP untuk “buang catatan ini”</td>
      <td><code>DELETE /api/buku/1</code></td>
    </tr>
    <tr>
      <td>Nomor di alamat</td>
      <td>Angka penunjuk buku mana yang dimaksud</td>
      <td>Ditulis <code>{id}</code> saat mendaftarkan pintu</td>
    </tr>
    <tr>
      <td>Bearer</td>
      <td>Cara menunjukkan kartu staf lewat header</td>
      <td>Ditulis <code>Authorization: Bearer &lt;token&gt;</code> (dari <a href="/artikel/laravel-auth-api-dasar">Auth API Dasar: Login &amp; Kartu Anggota (#61)</a>)</td>
    </tr>
    <tr>
      <td>404</td>
      <td>Nomor itu tidak ada di rak</td>
      <td>Jawaban wajar, bukan aplikasi rusak</td>
    </tr>
    <tr>
      <td>419</td>
      <td>Laravel curiga kiriman datang dari luar browser</td>
      <td>Muncul kalau pengecualian <code>api/*</code> belum dipasang</td>
    </tr>
  </tbody>
</table>

<h2>Persiapan — alat yang kamu buka</h2>
<p><strong>Alat yang dipakai di artikel ini</strong> (fondasi <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a> / <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a> — <strong>tidak</strong> ada unduhan Composer baru hari ini):</p>
<ul>
  <li><strong>Explorer</strong> — pastikan folder <code>perpustakaan-api</code> ada; cek <code>BukuController.php</code>, <code>routes/web.php</code>, dan <code>bootstrap/app.php</code>.</li>
  <li><strong>Terminal</strong> — Laragon: menu <em>Terminal</em> · XAMPP: tombol <em>Shell</em>. Jangan asal CMD/PowerShell dari Start Menu (PATH PHP bisa hilang).</li>
  <li><strong>Terminal kedua</strong> — wajib: terminal pertama = <code>php artisan serve</code>. Terminal kedua = uji <code>curl.exe</code> / PowerShell (login + ubah + hapus + baca).</li>
  <li><strong>Editor teks</strong> — Notepad / VS Code — menambah method <code>update</code> &amp; <code>destroy</code>, dua route baru, dan satu izin kecil di <code>bootstrap\app.php</code>. Tip: <code>notepad app\Http\Controllers\BukuController.php</code> dari terminal kedua (ganti nama file sesuai yang mau dibuka).</li>
  <li><strong>Browser</strong> — hanya untuk cek lampu toko dan melihat daftar buku (GET). Ubah &amp; hapus <strong>tidak bisa</strong> diuji dari bilah alamat browser.</li>
</ul>
<p><strong>Cara buka terminal kedua</strong> (baru pertama kali buka dua terminal sekaligus? ini caranya, jangan tutup yang pertama): <strong>Laragon</strong> — klik menu <em>Terminal</em> sekali lagi di jendela utama Laragon, sebuah jendela terminal baru akan muncul terpisah dari yang pertama. <strong>XAMPP</strong> — di XAMPP Control Panel, klik tombol <em>Shell</em> sekali lagi, jendela Shell kedua akan terbuka. Kedua jendela boleh hidup bersamaan — jendela pertama tetap menjalankan <code>php artisan serve</code>, jendela kedua kamu pakai untuk mengetik perintah lain.</p>
<p>Buka terminal Laragon/Shell XAMPP (terminal pertama), masuk folder proyek:</p>
<pre><code class="language-bash">cd C:\laragon\www\perpustakaan-api
</code></pre>
<p>Di XAMPP biasanya: <code>cd C:\xampp\htdocs\perpustakaan-api</code>. Sesuaikan path jika foldermu beda.</p>
<p>Nyalakan lampu toko di <strong>terminal pertama</strong>:</p>
<pre><code class="language-bash">php artisan serve
</code></pre>
<p>Biarkan jendela itu hidup. Buka <strong>terminal kedua</strong> (caranya sudah dijelaskan di atas), <code>cd</code> ke folder proyek yang sama — di sini kamu menguji ubah &amp; hapus.</p>
<p><strong>Awam:</strong> Terminal 1 = lampu toko. Terminal 2 = tangan menguji dua loket baru. Editor = menulis <code>update</code> &amp; <code>destroy</code>. Browser = cuma pengintip daftar.</p>
<p><strong>Install-dari-nol:</strong> jika Sanctum / login belum ada, selesaikan dulu <a href="/artikel/laravel-auth-api-dasar">Auth API Dasar: Login &amp; Kartu Anggota (#61)</a>. Jika PHP/Composer belum dikenal terminal, kembali ke <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a>.</p>

<h2>Cek cepat — potongan yang harus sudah ada</h2>
<p>Sebelum menambah loket baru, pastikan di proyekmu sudah pernah jalan:</p>
<ul>
  <li><code>GET /api/buku</code> menampilkan daftar (<a href="/artikel/laravel-controller-service-eloquent">Controller, Service &amp; Eloquent Laravel (#60)</a>)</li>
  <li><code>POST /api/login</code> memberi <code>token</code> (<a href="/artikel/laravel-auth-api-dasar">Auth API Dasar: Login &amp; Kartu Anggota (#61)</a>)</li>
  <li><code>POST /api/buku</code> ber-kartu berhasil menambah buku (<a href="/artikel/capstone-api-perpustakaan-laravel">Capstone: API Perpustakaan (#62)</a>)</li>
  <li>File <code>app\Models\Buku.php</code> punya baris <code>protected $fillable = ['judul', 'penulis'];</code> (dari <a href="/artikel/laravel-controller-service-eloquent">Controller, Service &amp; Eloquent Laravel (#60)</a>)</li>
</ul>
<p>Kalau salah satu belum jalan, perbaiki dulu di artikel asalnya. Loket ubah &amp; hapus hanya menumpang jalur yang sudah ada.</p>
<p><strong>Awam — kenapa baris <code>$fillable</code> itu wajib dicek?</strong> Baris itu adalah daftar kolom yang boleh ditulis sekaligus. Loket <em>ubah</em> menulis judul dan penulis dalam satu gerakan, jadi kalau daftarnya kosong, Laravel <strong>diam-diam membuang</strong> perubahanmu: jawabannya tetap “Buku diperbarui”, tapi isi buku tidak berubah sama sekali. Ini satu-satunya kesalahan hari ini yang tidak memberi pesan merah, jadi buka file itu dan pastikan barisnya ada.</p>

<h2>Nomor buku di alamat — dari mana angkanya?</h2>
<p>Ini pertanyaan pertama pemula: <em>“<code>PUT /api/buku/1</code> — angka 1 itu saya karang sendiri?”</em> Tidak. Angka itu diambil dari daftar buku.</p>
<p>Di <strong>terminal kedua</strong>, lihat dulu isi rak:</p>
<pre><code class="language-bash">curl.exe -s http://127.0.0.1:8000/api/buku ^
  -H "Accept: application/json"
</code></pre>
<p>Di jawaban JSON, tiap buku punya kunci <code>"id"</code>. Itulah nomor buku. Kalau buku “Bumi” punya <code>"id": 3</code>, maka alamat untuk mengubahnya adalah <code>/api/buku/3</code>.</p>
<p><strong>Awam:</strong> <code>{id}</code> saat mendaftarkan route adalah <em>tempat kosong</em>. Saat menguji, tempat kosong itu kamu isi angka nyata dari daftar. Salah nomor = jawaban <code>404</code>, bukan aplikasi rusak.</p>
<p><strong>Kalau jawabannya <code>"data": []</code> alias kosong</strong> — berarti raknya memang belum berisi buku, dan itu wajar kalau di <a href="/artikel/laravel-controller-service-eloquent">Controller, Service &amp; Eloquent Laravel (#60)</a> kamu belum sempat mengisi baris. Tidak ada nomor yang bisa diubah/dihapus. Isi dulu raknya: kerjakan bagian <strong>“Uji ubah &amp; hapus”</strong> di bawah sampai langkah <strong>1) Login</strong> untuk mendapat kartu, lalu tambah satu-dua buku dengan <code>POST /api/buku</code> mengikuti <a href="/artikel/capstone-api-perpustakaan-laravel">Capstone: API Perpustakaan (#62)</a>. Setelah rak berisi, kembali ke sini untuk mencatat <code>"id"</code>-nya.</p>

<figure style="background:#F5F5F0;border:2px solid #1a1a1a;border-radius:12px;padding:1rem;margin:1.25rem 0">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 760 200" width="100%" height="auto" role="img" aria-label="Nomor buku ada berarti berhasil, nomor tidak ada berarti 404">
  <title>Nomor ada versus nomor tidak ada</title>
  <rect x="40" y="40" width="290" height="100" rx="10" fill="#ffffff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="185" y="80" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Nomor ada di rak</text>
  <text x="185" y="108" text-anchor="middle" fill="#1a1a1a" font-size="13">ubah / hapus berhasil</text>
  <rect x="430" y="40" width="290" height="100" rx="10" fill="#ffffff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="575" y="80" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Nomor tidak ada</text>
  <text x="575" y="108" text-anchor="middle" fill="#1a1a1a" font-size="13">jawaban 404, rak aman</text>
  <text x="24" y="175" fill="#1a1a1a" font-size="13">Tanpa kartu Bearer, dua-duanya ditolak lebih dulu - belum sampai urusan nomor.</text>
</svg>
<figcaption style="color:#1a1a1a">Cek nomor dulu, baru urus isi. Tanpa kartu, pintu tidak terbuka sama sekali.</figcaption>
</figure>

<h2>Loket ubah — method update</h2>
<p>Buka <code>notepad app\Http\Controllers\BukuController.php</code>. Jangan hapus baris <code>namespace</code> dan kerangka <code>class BukuController extends Controller</code> — kita hanya menambah.</p>
<p><strong>Bagian 1 — baris <code>use</code>, letaknya DI ATAS <code>class</code></strong> (sejajar dengan baris <code>use</code> yang sudah ada, bukan di dalam class):</p>
<pre><code class="language-php">use App\Http\Requests\StoreBukuRequest; // slip yang sama boleh dipakai ulang
use App\Models\Buku;
use Illuminate\Http\JsonResponse;
</code></pre>
<p><strong>Awam — PENTING, jangan menulis dua kali:</strong> kalau salah satu baris di atas <strong>sudah ada</strong> di filemu, <em>lewati</em> baris itu. Biasanya <code>Illuminate\Http\JsonResponse</code> dan <code>App\Models\Buku</code> sudah kamu tulis sejak <a href="/artikel/laravel-controller-service-eloquent">Controller, Service &amp; Eloquent Laravel (#60)</a>, dan <code>StoreBukuRequest</code> sejak <a href="/artikel/capstone-api-perpustakaan-laravel">Capstone: API Perpustakaan (#62)</a> — jadi sering kali <strong>tidak ada</strong> yang perlu ditambah di bagian ini. Kalau ditulis dua kali, PHP berhenti dengan pesan <code>Cannot use ... because the name is already in use</code>.</p>
<p><strong>Awam — kenapa harus di atas class?</strong> Baris <code>use</code> di atas class berarti “ambil file dari folder lain”. Baris <code>use</code> yang ditulis <em>di dalam</em> class artinya lain sama sekali (menempelkan trait), dan PHP akan mengeluh <code>Trait "App\Models\Buku" not found</code>. Jadi perhatikan betul letaknya.</p>
<p><strong>Bagian 2 — method <code>update</code>, letaknya DI DALAM class</strong>, di sebelah <code>store</code> yang sudah kamu buat di <a href="/artikel/capstone-api-perpustakaan-laravel">Capstone: API Perpustakaan (#62)</a>:</p>
<pre><code class="language-php">// Cuplikan BukuController — loket ubah buku (di dalam class)
public function update(StoreBukuRequest $request, int $id): JsonResponse
{
    $buku = Buku::find($id);

    if (! $buku) {
        return response()-&gt;json(['message' =&gt; 'Buku tidak ditemukan'], 404);
    }

    $buku-&gt;update($request-&gt;validated());

    return response()-&gt;json([
        'message' =&gt; 'Buku diperbarui',
        'data' =&gt; $buku,
    ]);
}
</code></pre>
<p><strong>Awam:</strong> tiga langkah saja — cari buku dari nomor, kalau tidak ada jawab <code>404</code>, kalau ada tulis ulang isinya lalu jawab JSON. Satpam slip (<code>StoreBukuRequest</code>) tetap jalan lebih dulu, jadi judul kosong tidak akan lolos.</p>
<p><strong>Catatan:</strong> nama Form Request sesuaikan dengan file di Explorer-mu. Kalau belum ada, buat mengikuti <a href="/artikel/laravel-request-validasi-api">Request &amp; Form Request: Menjaga Input API (#59)</a>.</p>
<p><strong>“Lho, kok tidak lewat <code>BukuService</code>?”</strong> Di <a href="/artikel/laravel-controller-service-eloquent">Controller, Service &amp; Eloquent Laravel (#60)</a> kita memakai “dapur” (<code>BukuService</code>) supaya loket tidak sibuk mengurus tabel. Di sini <code>Buku::find($id)</code> dipanggil langsung dari loket supaya cuplikannya pendek dan kamu melihat <em>jelas</em> di baris mana <code>404</code> lahir. Kalau kamu sudah punya <code>BukuService</code>, silakan pindahkan <code>find</code>/<code>update</code>/<code>delete</code> ke sana sebagai method <code>ubah()</code> dan <code>hapus()</code> — hasilnya sama, dapurnya lebih rapi.</p>

<h2>Loket hapus — method destroy</h2>
<p>Masih di file yang sama, tetap <strong>di dalam class</strong>, tambahkan method <code>destroy</code> di bawah <code>update</code>. Bagian ini tidak butuh baris <code>use</code> baru — semuanya sudah tersedia dari langkah sebelumnya:</p>
<pre><code class="language-php">// Cuplikan BukuController — loket hapus buku (di dalam class)
public function destroy(int $id): JsonResponse
{
    $buku = Buku::find($id);

    if (! $buku) {
        return response()-&gt;json(['message' =&gt; 'Buku tidak ditemukan'], 404);
    }

    $buku-&gt;delete();

    return response()-&gt;json(['message' =&gt; 'Buku dihapus']);
}
</code></pre>
<p>Simpan file. <strong>Awam:</strong> hapus tidak butuh slip judul/penulis — cukup nomor buku dan kartu staf. Karena itu <code>destroy</code> tidak memakai Form Request.</p>

<h2>Sambungkan pintu di routes</h2>
<p>Buka <code>notepad routes\web.php</code>. Yang perlu kamu ketik <strong>hanya dua baris bertanda BARU</strong> di bawah ini — sisanya sudah ada di filemu sejak artikel sebelumnya, ditampilkan supaya kamu tahu letak persisnya:</p>
<pre><code class="language-php">// Cuplikan routes/web.php — bentuk AKHIR file setelah hari ini
use App\Http\Controllers\AuthController;   // sudah ada dari Auth API
use App\Http\Controllers\BukuController;   // sudah ada dari Controller/Service

Route::get('/api/buku', [BukuController::class, 'index']);   // sudah ada dari Controller/Service
Route::post('/api/login', [AuthController::class, 'login']); // sudah ada dari Auth API

Route::middleware('auth:sanctum')-&gt;group(function () {
    Route::get('/api/saya', [AuthController::class, 'saya']);            // sudah ada dari Auth API
    Route::post('/api/buku', [BukuController::class, 'store']);          // sudah ada dari Capstone
    Route::put('/api/buku/{id}', [BukuController::class, 'update']);     // &lt;-- BARU hari ini
    Route::delete('/api/buku/{id}', [BukuController::class, 'destroy']); // &lt;-- BARU hari ini
});
</code></pre>
<p><strong>Awam — jangan menempel seluruh blok ini mentah-mentah.</strong> Kalau kamu menempelnya di bawah isi yang sudah ada, baris <code>use</code> jadi dobel dan PHP berhenti dengan <code>Cannot use ... name is already in use</code>. Kalau kamu menimpa seluruh isi file, route lain (termasuk halaman biasa milik proyekmu) bisa ikut hilang. Cari kelompok <code>auth:sanctum</code> yang sudah ada di filemu, lalu sisipkan dua baris <code>Route::put</code> dan <code>Route::delete</code> di dalamnya.</p>
<p>Simpan. Pastikan <code>serve</code> masih hidup di terminal pertama.</p>
<p><strong>Awam:</strong> <code>{id}</code> itu tempat kosong untuk nomor buku. Dua pintu baru sengaja ditaruh <em>di dalam</em> <code>auth:sanctum</code> — tanpa kartu, tamu tidak boleh mengubah atau menghapus apa pun.</p>
<p><strong>Pastikan Laravel benar-benar melihat dua pintu baru itu.</strong> Di <strong>terminal kedua</strong> (biarkan <code>serve</code> hidup di terminal pertama), jalankan:</p>
<pre><code class="language-bash">php artisan route:list --path=api/buku
</code></pre>
<p>Kamu harus melihat empat baris: <code>GET</code>, <code>POST</code>, <code>PUT</code>, dan <code>DELETE</code>. Kalau <code>PUT</code>/<code>DELETE</code> belum muncul, berarti <code>routes\web.php</code> belum tersimpan atau kata kerjanya salah ketik — perbaiki dulu sebelum lanjut menguji.</p>

<h2>Cek izin api/* — supaya PUT &amp; DELETE tidak ditolak 419</h2>
<p>Satpam lobi <strong>CSRF</strong> sudah kita pasang sejak <a href="/artikel/laravel-request-validasi-api">Request &amp; Form Request: Menjaga Input API (#59)</a> (dan dicek ulang di <a href="/artikel/laravel-auth-api-dasar">Auth API Dasar: Login &amp; Kartu Anggota (#61)</a> / <a href="/artikel/capstone-api-perpustakaan-laravel">Capstone: API Perpustakaan (#62)</a>). Tugasnya melindungi <em>formulir web</em>: menolak kiriman <code>POST</code>, <code>PUT</code>, dan <code>DELETE</code> yang tidak datang dari halaman browser milik situs itu sendiri.</p>
<p>Karena kita menguji dari <strong>terminal</strong> (bukan browser), tanpa izin <code>api/*</code> jawabannya:</p>
<pre><code class="language-json">{"message":"CSRF token mismatch."}
</code></pre>
<p>dengan kode <strong>419</strong>. Ini <em>bukan</em> berarti kodemu salah — pintu kita bahkan belum sempat dijalankan.</p>
<p><strong>Kalau di dalam <code>withMiddleware</code> sudah ada <code>'api/*'</code></strong> — <strong>lewati langkah ini</strong>. Cukup sekali per proyek; pintu baru hari ini ikut tercakup.</p>
<p><strong>Belum ada / kamu loncat ke artikel ini?</strong> Pastikan terminal kedua sudah di folder proyek (<code>cd</code> ke <code>perpustakaan-api</code>), lalu <code>notepad bootstrap\app.php</code>. <strong>Jangan menempel</strong> blok <code>-&gt;withMiddleware(...)</code> baru di dalam fungsi yang sudah ada. Kerja di dalam fungsi itu: hapus <code>//</code> kalau masih ada, biarkan baris lain, lalu tempel <strong>hanya</strong>:</p>
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
<p>Simpan. <strong>Awam:</strong> <code>except</code> = “kecuali”. Tanda <code>*</code> = “apa pun setelahnya”, jadi <code>api/buku/3</code> dan <code>api/login</code> ikut. Di Laravel 13 nama resminya <code>preventRequestForgery</code>; nama lama <code>validateCsrfTokens</code> masih sama artinya.</p>
<p><strong>Apakah ini membuat API-ku tidak aman?</strong> Tidak. Pagar yang menjaga ubah &amp; hapus adalah <strong>petugas kartu Sanctum</strong> (<code>auth:sanctum</code>) — tanpa kartu tetap ditolak. CSRF adalah satpam lobi untuk formulir browser; API ber-token memang memakai pagar jenis yang berbeda.</p>
<p><strong>Setiap kali menyimpan <code>bootstrap\app.php</code></strong>, matikan <code>serve</code> di terminal pertama (<code>Ctrl+C</code>) lalu nyalakan lagi <code>php artisan serve</code>. File itu hanya dibaca saat aplikasi mulai.</p>

<h2>Uji ubah &amp; hapus di terminal kedua</h2>
<p>Pintu <code>PUT</code> dan <code>DELETE</code> <strong>tidak bisa</strong> dicoba dari bilah alamat browser — browser hanya bisa GET. Jadi uji lewat terminal kedua (atau alat berjendela seperti Postman di Opsi C).</p>
<p>Pastikan user uji masih ada. Jalankan di <strong>terminal kedua</strong> (biarkan <code>serve</code> tetap hidup di terminal pertama) — ini satu tembakan, bukan chat panjang:</p>
<pre><code class="language-bash">php artisan tinker --execute="\App\Models\User::updateOrCreate(['email'=>'staf@perpustakaan.test'], ['name'=>'Staf Mini','password'=>bcrypt('password')]);"
</code></pre>

<p>Pilih <strong>salah satu</strong> opsi di bawah (A, B, atau C) — jangan menjalankan dua opsi pada nomor buku yang sama, karena buku yang sudah dihapus di percobaan pertama tidak ada lagi di percobaan kedua (jawabannya akan <code>404</code>).</p>

<p><strong>Opsi A — <code>curl.exe</code></strong> (Windows 10/11 biasanya sudah punya; ketik <code>curl.exe</code> agar tidak tertukar di PowerShell):</p>
<p><strong>Awam — soal tanda <code>^</code> di ujung baris:</strong> tanda itu berarti “perintah ini masih lanjut ke baris berikutnya”, dan berlaku di <strong>CMD / Shell XAMPP / Terminal Laragon</strong>. Kalau kamu memakai <strong>PowerShell</strong>, <code>^</code> tidak dikenal — ganti setiap <code>^</code> dengan tanda backtick <code>`</code>, atau yang paling aman: ketik seluruh perintah dalam <strong>satu baris panjang</strong> tanpa <code>^</code> sama sekali.</p>
<p><strong>1) Login — ambil token</strong></p>
<pre><code class="language-bash">curl.exe -s -X POST http://127.0.0.1:8000/api/login ^
  -H "Content-Type: application/json" ^
  -H "Accept: application/json" ^
  -d "{\"email\":\"staf@perpustakaan.test\",\"password\":\"password\"}"
</code></pre>
<p><strong>Awam — salin token:</strong> di jawaban JSON cari kunci <code>"token"</code>. Salin <em>hanya</em> string di antara tanda kutip. Jangan salin kata <code>Bearer</code> dari JSON — kata Bearer ditulis di header. Ganti <code>GANTI_DENGAN_TOKEN</code> di bawah.</p>
<p><strong>Awam — cara salin teks dari terminal Windows:</strong> blok teks dengan klik kiri lalu tahan sambil digeser (klik-drag), lepas tombol mouse untuk menyalin otomatis. Tempel dengan klik kanan di jendela terminal (bukan <code>Ctrl+V</code>, terminal bawaan Windows kadang tidak mendukungnya).</p>
<p><strong>2) Lihat nomor buku dulu</strong> — catat salah satu <code>"id"</code> dari jawaban ini, lalu pakai angka itu di langkah berikutnya:</p>
<pre><code class="language-bash">curl.exe -s http://127.0.0.1:8000/api/buku ^
  -H "Accept: application/json"
</code></pre>
<p><strong>Awam — dua kata yang harus kamu ganti sendiri</strong> di semua perintah di bawah: <code>GANTI_DENGAN_TOKEN</code> diganti token dari langkah 1, dan <code>NOMOR</code> diganti angka <code>"id"</code> yang kamu catat di langkah 2. Contoh: kalau <code>id</code>-nya 3, maka <code>/api/buku/NOMOR</code> kamu ketik menjadi <code>/api/buku/3</code>. Jangan biarkan tulisan <code>NOMOR</code> ikut terketik.</p>
<p><strong>3) Ubah tanpa kartu — harus ditolak</strong></p>
<pre><code class="language-bash">curl.exe -s -X PUT http://127.0.0.1:8000/api/buku/NOMOR ^
  -H "Content-Type: application/json" ^
  -H "Accept: application/json" ^
  -d "{\"judul\":\"Coba Ubah\",\"penulis\":\"Tanpa Kartu\"}"
</code></pre>
<p>Kamu harus melihat pesan “Belum diizinkan” / unauthenticated — bukan “Buku diperbarui”. Kalau yang muncul justru <code>CSRF token mismatch</code>, berarti izin <code>api/*</code> di <code>bootstrap\app.php</code> belum terpasang — kembali ke bagian sebelumnya.</p>
<p><strong>4) Ubah dengan kartu — harus berhasil</strong></p>
<pre><code class="language-bash">curl.exe -s -X PUT http://127.0.0.1:8000/api/buku/NOMOR ^
  -H "Content-Type: application/json" ^
  -H "Accept: application/json" ^
  -H "Authorization: Bearer GANTI_DENGAN_TOKEN" ^
  -d "{\"judul\":\"Bumi (Edisi Revisi)\",\"penulis\":\"Tere Liye\"}"
</code></pre>
<p>Jawaban yang benar kira-kira begini (semua menyatu dalam satu baris panjang, itu normal):</p>
<pre><code class="language-json">{"message":"Buku diperbarui","data":{"id":3,"judul":"Bumi (Edisi Revisi)","penulis":"Tere Liye","created_at":"2026-07-20T08:11:02.000000Z","updated_at":"2026-07-27T02:45:13.000000Z"}}
</code></pre>
<p><strong>Awam:</strong> <code>created_at</code> dan <code>updated_at</code> ikut muncul walaupun tidak kamu kirim — itu dua kolom waktu yang diisi Laravel sendiri. Perhatikan <code>updated_at</code>: jamnya baru, tanda bukunya memang tersentuh.</p>
<p><strong>5) Baca katalog — pastikan judulnya benar-benar berubah</strong></p>
<pre><code class="language-bash">curl.exe -s http://127.0.0.1:8000/api/buku ^
  -H "Accept: application/json"
</code></pre>
<p><strong>Jangan lewati langkah ini.</strong> Kalau langkah 4 menjawab “Buku diperbarui” tapi di daftar ini judulnya <em>masih yang lama</em>, penyebabnya hampir pasti baris <code>$fillable</code> di <code>app\Models\Buku.php</code> belum berisi <code>'judul'</code> dan <code>'penulis'</code> (lihat “Cek cepat” di atas). Ini satu-satunya kegagalan hari ini yang menyamar sebagai keberhasilan.</p>
<p><strong>6) Ubah nomor yang tidak ada — harus 404</strong></p>
<pre><code class="language-bash">curl.exe -s -X PUT http://127.0.0.1:8000/api/buku/9999 ^
  -H "Content-Type: application/json" ^
  -H "Accept: application/json" ^
  -H "Authorization: Bearer GANTI_DENGAN_TOKEN" ^
  -d "{\"judul\":\"Buku Hantu\",\"penulis\":\"Tidak Ada\"}"
</code></pre>
<p>Jawaban yang benar: <code>{"message":"Buku tidak ditemukan"}</code>. Angka <code>9999</code> di sini memang sengaja dikarang — tidak perlu diganti.</p>
<p><strong>7) Hapus dengan kartu</strong> (perhatikan: tidak ada <code>-d</code>, karena hapus tidak mengirim slip). Buku yang kamu ubah di langkah 4 boleh dipakai — hasil ubahnya sudah kamu buktikan di langkah 5:</p>
<pre><code class="language-bash">curl.exe -s -X DELETE http://127.0.0.1:8000/api/buku/NOMOR ^
  -H "Accept: application/json" ^
  -H "Authorization: Bearer GANTI_DENGAN_TOKEN"
</code></pre>
<p>Jawaban yang benar: <code>{"message":"Buku dihapus"}</code>.</p>
<p><strong>8) Baca katalog — buku itu harus sudah hilang</strong></p>
<pre><code class="language-bash">curl.exe -s http://127.0.0.1:8000/api/buku ^
  -H "Accept: application/json"
</code></pre>
<p>Buku dengan nomor tadi tidak muncul lagi di daftar. Empat gerakan CRUD sudah lengkap.</p>

<p><strong>Opsi B — PowerShell</strong> (token disimpan otomatis di variabel, tidak perlu salin-tempel manual). Tetap di <strong>Shell Laragon / XAMPP</strong> kalau jendela itu sudah PowerShell (sering begitu). Kalau kamu membuka PowerShell dari Start Menu khusus untuk Opsi B: boleh, karena perintah di bawah memakai <code>Invoke-RestMethod</code> (tidak butuh <code>php</code>) — tapi untuk <code>tinker</code> / <code>route:list</code> tadi, tetap pakai Shell Laragon/XAMPP. <code>cd</code> ke folder proyek, lalu ketik baris-baris ini. Di PowerShell tanda sambung baris adalah backtick <code>`</code> (bukan <code>^</code>):</p>
<pre><code class="language-powershell">$login = Invoke-RestMethod -Method Post -Uri http://127.0.0.1:8000/api/login `
  -ContentType "application/json" `
  -Body '{"email":"staf@perpustakaan.test","password":"password"}'
$token = $login.token

# Lihat isi rak dulu, catat salah satu id
Invoke-RestMethod -Uri http://127.0.0.1:8000/api/buku -Headers @{ Accept = "application/json" }

# GANTI angka 3 di bawah dengan id buku milikmu
$nomor = 3

Invoke-RestMethod -Method Put -Uri "http://127.0.0.1:8000/api/buku/$nomor" `
  -ContentType "application/json" `
  -Headers @{ Authorization = "Bearer $token"; Accept = "application/json" } `
  -Body '{"judul":"Bumi (Edisi Revisi)","penulis":"Tere Liye"}'

# Buktikan judulnya benar-benar berubah
Invoke-RestMethod -Uri http://127.0.0.1:8000/api/buku -Headers @{ Accept = "application/json" }

Invoke-RestMethod -Method Delete -Uri "http://127.0.0.1:8000/api/buku/$nomor" `
  -Headers @{ Authorization = "Bearer $token"; Accept = "application/json" }

Invoke-RestMethod -Uri http://127.0.0.1:8000/api/buku -Headers @{ Accept = "application/json" }
</code></pre>
<p><strong>Awam:</strong> baris yang diawali <code>#</code> adalah catatan untuk manusia — PowerShell mengabaikannya, jadi boleh ikut ditempel. Perhatikan juga: kalau ada yang ditolak (misalnya nomor sudah terhapus), PowerShell menampilkan tulisan merah panjang dan <em>berhenti</em> di situ. Itu wajar — baca baris pertamanya untuk melihat kode statusnya (<code>404</code>, <code>401</code>, atau <code>419</code>), perbaiki, lalu jalankan lagi dari baris yang gagal.</p>

<p><strong>Opsi C — Postman / Insomnia</strong> (alat berjendela, paling nyaman untuk PUT/DELETE karena tidak perlu mengurus tanda kutip). Unduh dan pasang Postman dulu kalau belum ada, lalu:</p>
<ol>
  <li><strong>Ambil token.</strong> Buat request baru, method <strong>POST</strong>, URL <code>http://127.0.0.1:8000/api/login</code>. Tab <em>Headers</em>: tambahkan <code>Accept</code> bernilai <code>application/json</code>. Tab <em>Body</em>: pilih <strong>raw</strong> lalu <strong>JSON</strong>, isi <code>{"email":"staf@perpustakaan.test","password":"password"}</code>. Tekan <strong>Send</strong>, lalu salin nilai <code>token</code> dari jawabannya.</li>
  <li><strong>Cari nomor buku.</strong> Method <strong>GET</strong>, URL <code>http://127.0.0.1:8000/api/buku</code>, tekan <strong>Send</strong>, catat salah satu <code>"id"</code>.</li>
  <li><strong>Ubah.</strong> Method <strong>PUT</strong>, URL <code>http://127.0.0.1:8000/api/buku/NOMOR</code> — ganti tulisan <code>NOMOR</code> dengan angka <code>"id"</code> dari langkah 2 (bukan selalu 3). Tab <em>Authorization</em>: pilih type <strong>Bearer Token</strong>, tempel token dari langkah 1. Tab <em>Body</em>: <strong>raw</strong> + <strong>JSON</strong>, isi judul dan penulis baru. <strong>Send</strong>.</li>
  <li><strong>Hapus.</strong> Ganti method jadi <strong>DELETE</strong>, kosongkan Body, biarkan Authorization tetap terisi, URL tetap memakai nomor yang sama. <strong>Send</strong>.</li>
</ol>
<p><strong>Awam:</strong> di Postman kamu cukup menempel token sekali di tab Authorization — Postman yang menuliskan header <code>Authorization: Bearer ...</code> untukmu. Kode status (200, 401, 404, 419) terlihat jelas di sudut panel jawaban, jadi opsi ini paling enak untuk pemula yang masih gugup dengan terminal.</p>

<h2>Pola Dasar — enam langkah melengkapi CRUD</h2>
<figure role="img" aria-label="Pola Dasar enam langkah melengkapi CRUD ubah dan hapus" style="background:#F5F5F0;border:2px solid #1a1a1a;border-radius:12px;padding:1rem;margin:1.25rem 0">
<ol style="list-style:none;padding:0;margin:0;display:grid;gap:.75rem">
  <li style="display:flex;gap:.75rem;align-items:flex-start">
    <span style="flex:0 0 2rem;height:2rem;border-radius:999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div><strong style="color:#1a1a1a">Buka alat</strong><br><span style="color:#1a1a1a">Terminal 1 <code>serve</code> · Terminal 2 uji · Editor · Browser pengintip.</span></div>
  </li>
  <li style="display:flex;gap:.75rem;align-items:flex-start">
    <span style="flex:0 0 2rem;height:2rem;border-radius:999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div><strong style="color:#1a1a1a">Cek fondasi</strong><br><span style="color:#1a1a1a">Baca, login, tambah dari Capstone masih jalan · <code>$fillable</code> ada di Model.</span></div>
  </li>
  <li style="display:flex;gap:.75rem;align-items:flex-start">
    <span style="flex:0 0 2rem;height:2rem;border-radius:999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div><strong style="color:#1a1a1a">Tulis dua loket</strong><br><span style="color:#1a1a1a"><code>update</code> lalu <code>destroy</code> di dalam class <code>BukuController</code>.</span></div>
  </li>
  <li style="display:flex;gap:.75rem;align-items:flex-start">
    <span style="flex:0 0 2rem;height:2rem;border-radius:999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div><strong style="color:#1a1a1a">Daftarkan pintu</strong><br><span style="color:#1a1a1a"><code>PUT</code> &amp; <code>DELETE</code> di dalam <code>auth:sanctum</code> · cek <code>route:list</code>.</span></div>
  </li>
  <li style="display:flex;gap:.75rem;align-items:flex-start">
    <span style="flex:0 0 2rem;height:2rem;border-radius:999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">5</span>
    <div><strong style="color:#1a1a1a">Cek izin <code>api/*</code></strong><br><span style="color:#1a1a1a">Sudah ada? lewati. Belum? tempel baris di dalam <code>withMiddleware</code> · nyalakan ulang <code>serve</code>.</span></div>
  </li>
  <li style="display:flex;gap:.75rem;align-items:flex-start">
    <span style="flex:0 0 2rem;height:2rem;border-radius:999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">6</span>
    <div><strong style="color:#1a1a1a">Uji berurutan</strong><br><span style="color:#1a1a1a">Lihat nomor -&gt; ubah tanpa kartu gagal -&gt; ubah dengan kartu berhasil -&gt; baca ulang untuk membuktikan -&gt; nomor ngawur jawab 404 -&gt; hapus -&gt; daftar berkurang.</span></div>
  </li>
</ol>
</figure>
<p><strong style="color:#1a1a1a">Awam:</strong> <span style="color:#1a1a1a">selalu uji versi “gagal” dulu (tanpa kartu, nomor ngawur), baru versi “berhasil”. Kalau yang gagal ternyata berhasil, berarti satpam kartu belum terpasang benar.</span></p>

<h2>File contoh — simulasi ubah &amp; hapus</h2>
<p>Simpan sebagai <code>laravel_crud_api_buku_ubah_hapus_demo.php</code> lalu jalankan di terminal kedua: <code>php laravel_crud_api_buku_ubah_hapus_demo.php</code>. Ini <em>bukan</em> Laravel sungguhan — hanya meniru alurnya supaya terasa dulu sebelum dikerjakan di proyek.</p>
<pre><code class="language-php">&lt;?php
declare(strict_types=1);

/**
 * laravel_crud_api_buku_ubah_hapus_demo.php
 * Simulasi ubah (PUT) dan hapus (DELETE) satu buku berdasarkan nomor.
 */

function cariBaris(array $rak, int $id): ?int
{
    foreach ($rak as $baris =&gt; $buku) {
        if ($buku['id'] === $id) {
            return $baris;
        }
    }

    return null;
}

function demo(): void
{
    $rak = [
        ['id' =&gt; 1, 'judul' =&gt; 'Bumi', 'penulis' =&gt; 'Tere Liye'],
        ['id' =&gt; 2, 'judul' =&gt; 'Laskar Pelangi', 'penulis' =&gt; 'Andrea Hirata'],
    ];

    echo "1) PUT /api/buku/2 dengan kartu\n";
    $baris = cariBaris($rak, 2);
    if ($baris !== null) {
        $rak[$baris]['judul'] = 'Laskar Pelangi (Edisi Revisi)';
        echo json_encode(['message' =&gt; 'Buku diperbarui', 'data' =&gt; $rak[$baris]], JSON_UNESCAPED_UNICODE), PHP_EOL;
    }

    echo "2) PUT /api/buku/9999 - nomor tidak ada\n";
    $baris = cariBaris($rak, 9999);
    echo json_encode(['message' =&gt; $baris === null ? 'Buku tidak ditemukan' : 'Buku diperbarui'], JSON_UNESCAPED_UNICODE), PHP_EOL;

    echo "3) DELETE /api/buku/1 dengan kartu\n";
    $baris = cariBaris($rak, 1);
    if ($baris !== null) {
        unset($rak[$baris]);
        $rak = array_values($rak);
    }
    echo json_encode(['message' =&gt; 'Buku dihapus'], JSON_UNESCAPED_UNICODE), PHP_EOL;

    echo "4) GET /api/buku - sisa rak\n";
    echo json_encode(['message' =&gt; 'Daftar buku', 'data' =&gt; $rak], JSON_UNESCAPED_UNICODE), PHP_EOL;
    echo PHP_EOL, "Langkah sungguhan: serve -&gt; login -&gt; Bearer PUT/DELETE /api/buku/{nomor} -&gt; GET daftar.", PHP_EOL;
}

demo();
</code></pre>
<p><strong>Awam:</strong> <code>declare(strict_types=1);</code> = PHP lebih ketat soal tipe data di file contoh. <code>?int</code> artinya fungsi boleh menjawab angka <em>atau</em> “tidak ada”. Di Laravel sungguhan, pencarian baris itu dikerjakan <code>Buku::find($id)</code>.</p>

<h2>Kesalahan umum</h2>
<p>Cari gejala yang kamu lihat di kolom pertama — biasanya penyebabnya cuma satu hal kecil.</p>
<table>
  <thead>
    <tr>
      <th>Gejala di layar</th>
      <th>Penyebab</th>
      <th>Perbaikan</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><code>CSRF token mismatch</code> / kode <code>419</code></td>
      <td>Izin <code>api/*</code> belum dipasang, atau <code>serve</code> belum dinyalakan ulang setelah menyimpan <code>bootstrap\app.php</code></td>
      <td>Pasang <code>preventRequestForgery(except: ['api/*'])</code> (jangan timpa baris lain di <code>withMiddleware</code>), lalu <code>Ctrl+C</code> dan <code>php artisan serve</code> lagi</td>
    </tr>
    <tr>
      <td><code>Cannot use ... name is already in use</code></td>
      <td>Baris <code>use</code> ditulis dua kali karena sudah ada sejak artikel Controller/Capstone sebelumnya</td>
      <td>Hapus baris <code>use</code> yang dobel — cukup satu per nama</td>
    </tr>
    <tr>
      <td><code>Trait "App\Models\Buku" not found</code></td>
      <td>Baris <code>use</code> tertempel <em>di dalam</em> class, bukan di atasnya</td>
      <td>Pindahkan baris <code>use</code> ke atas <code>class BukuController</code></td>
    </tr>
    <tr>
      <td>“Buku diperbarui” tapi daftar masih judul lama</td>
      <td><code>$fillable</code> di <code>app\Models\Buku.php</code> belum berisi <code>'judul'</code> dan <code>'penulis'</code></td>
      <td>Tambahkan kedua kolom itu ke <code>$fillable</code>, lalu ubah lagi</td>
    </tr>
    <tr>
      <td><code>405 Method Not Allowed</code></td>
      <td>Route tersimpan tapi kata kerjanya salah (mis. <code>Route::post</code> padahal diuji <code>PUT</code>)</td>
      <td>Cek dengan <code>php artisan route:list --path=api/buku</code></td>
    </tr>
    <tr>
      <td><code>404</code> tanpa pesan <code>"Buku tidak ditemukan"</code></td>
      <td>Itu 404 dari Laravel: route <code>PUT</code>/<code>DELETE</code> belum terdaftar</td>
      <td>Simpan <code>routes\web.php</code>, cek <code>route:list</code>. Kalau pesannya <em>ada</em>, berarti normal — nomornya memang tidak ada</td>
    </tr>
    <tr>
      <td>Kode <code>401</code> / “Belum diizinkan” padahal token sudah ditempel</td>
      <td>Token tersalin sebagian, atau kata <code>Bearer</code> ikut tersalin dari JSON</td>
      <td>Salin ulang <em>hanya</em> isi kunci <code>"token"</code>; kata <code>Bearer</code> ditulis manual di header</td>
    </tr>
    <tr>
      <td>Perintah <code>curl</code> pecah jadi beberapa error</td>
      <td>Tanda <code>^</code> dipakai di PowerShell</td>
      <td>Ganti <code>^</code> dengan backtick <code>`</code>, atau ketik satu baris panjang</td>
    </tr>
    <tr>
      <td>Halaman HTML panjang, bukan JSON</td>
      <td>Header <code>Accept</code> belum dikirim</td>
      <td>Tambahkan <code>-H "Accept: application/json"</code></td>
    </tr>
    <tr>
      <td>Gagal koneksi / <em>connection refused</em></td>
      <td><code>serve</code> mati di terminal pertama</td>
      <td>Nyalakan lagi <code>php artisan serve</code>, biarkan jendelanya hidup</td>
    </tr>
    <tr>
      <td><code>php tidak dikenali</code></td>
      <td>Terminal dibuka dari Start Menu, PATH PHP tidak ada</td>
      <td>Pakai menu <em>Terminal</em> Laragon atau tombol <em>Shell</em> XAMPP</td>
    </tr>
  </tbody>
</table>
<p>Dua hal lain yang sering bikin bingung, tapi sebenarnya bukan error: <strong>mencoba PUT/DELETE dari bilah alamat browser</strong> (browser hanya bisa GET — pakai terminal kedua atau Postman), dan <strong>mengirim <code>-d</code> saat menghapus</strong> (tidak perlu slip untuk hapus; cukup nomor + kartu). Dan kalau dua pintu baru terlanjur ditaruh <em>di luar</em> <code>auth:sanctum</code>, siapa pun bisa menghapus buku — pastikan keduanya di dalam kelompok berkartu.</p>

<h2>Latihan</h2>
<ol>
  <li>Ubah judul satu buku, lalu <code>GET /api/buku</code> — pastikan judul barunya benar-benar tersimpan.</li>
  <li>Coba ubah dengan slip kosong (<code>{}</code>) memakai kartu — pastikan satpam Form Request menolak, bukan menyimpan judul kosong.</li>
  <li>Hapus satu buku, lalu hapus lagi nomor yang sama — perhatikan jawaban kedua seharusnya <code>404</code>.</li>
  <li>Tambah buku baru (dari <a href="/artikel/capstone-api-perpustakaan-laravel">Capstone: API Perpustakaan (#62)</a>), ubah judulnya, lalu hapus — rasakan empat gerakan CRUD dalam satu tarikan napas.</li>
  <li>(Opsional) Coba hapus tanpa kartu — pastikan ditolak sebelum urusan nomor.</li>
</ol>

<h2>FAQ</h2>
<p><strong>Kenapa <code>PUT</code>, bukan <code>POST</code>?</strong><br>
Keduanya mengirim data, tapi <code>PUT</code> berarti “ganti isi catatan yang nomornya sudah saya sebut”. Memakai kata kerja yang tepat membuat pintu API mudah dibaca orang lain.</p>
<p><strong>Kalau saya pakai <code>PATCH</code> boleh?</strong><br>
Boleh. <code>PATCH</code> biasanya untuk mengubah sebagian kolom saja. Untuk latihan ini <code>PUT</code> lebih sederhana karena kita mengirim judul dan penulis sekaligus.</p>
<p><strong>Kenapa <code>Buku::find</code> lalu cek sendiri, bukan <code>findOrFail</code>?</strong><br>
<code>findOrFail</code> juga benar dan lebih singkat. Kita pakai <code>find</code> + <code>if</code> supaya kamu melihat sendiri kapan angka <code>404</code> itu muncul, bukan “ajaib” dari Laravel.</p>
<p><strong>Apakah perlu mengunduh sesuatu lagi hari ini?</strong><br>
Tidak. Ubah &amp; hapus hanya menambah dua method di Controller, dua baris di routes, dan satu izin kecil di <code>bootstrap\app.php</code> — tidak ada paket Composer baru. Kalau Sanctum belum terpasang, lakukan install-dari-nol di <a href="/artikel/laravel-auth-api-dasar">Auth API Dasar: Login &amp; Kartu Anggota (#61)</a> dulu.</p>
<p><strong>Kenapa muncul 419 / CSRF token mismatch saat uji dari terminal?</strong><br>
Karena satpam lobi CSRF menahan semua kiriman <code>POST</code>, <code>PUT</code>, dan <code>DELETE</code> dari luar browser — termasuk login, tambah, ubah, dan hapus. Izin <code>api/*</code> dipasang sejak <a href="/artikel/laravel-request-validasi-api">Request &amp; Form Request: Menjaga Input API (#59)</a>; cukup sekali per proyek dan berlaku untuk semua pintu <code>api/</code>. Kalau kamu melihat 419 hari ini, hampir pasti izin itu belum ada atau <code>serve</code> belum dinyalakan ulang.</p>
<p><strong>Kalau nanti saya pindah ke <code>routes/api.php</code>, izin itu masih perlu?</strong><br>
Tidak. Route di <code>routes/api.php</code> memang tidak dijaga CSRF sejak awal, jadi pengecualian itu bisa dihapus. Kita tetap di <code>web.php</code> hari ini supaya kamu tidak perlu memasang berkas route tambahan — sesuai jalur yang dipakai sejak <a href="/artikel/laravel-routing-json-perpustakaan-api">Routing &amp; Jawaban JSON API Perpustakaan (#58)</a>.</p>
<p><strong>Token yang mana yang disalin?</strong><br>
Hanya string di kunci <code>"token"</code> pada jawaban login. Panjang, tanpa spasi. Lalu tulis di header: <code>Authorization: Bearer </code> + string itu.</p>
<p><strong>Terminal mana yang dipakai?</strong><br>
Terminal 1: <code>serve</code>. Terminal 2: login, lihat nomor, ubah, hapus. Editor: <code>update</code> &amp; <code>destroy</code> + routes. Browser: hanya melihat daftar.</p>
<p><strong>Kenapa masih Shell XAMPP / Laragon?</strong><br>
Agar PATH PHP sama seperti saat proyek dibuat. CMD dari Start Menu sering menjawab “php tidak dikenal”.</p>
<p><strong>Buku yang dihapus bisa dikembalikan?</strong><br>
Dengan <code>delete()</code> biasa: tidak. Laravel punya fitur “hapus lunak” (soft delete) untuk itu — bahan bagus untuk belajar lanjutan setelah jalur ini tamat.</p>

<h2>Ringkasan</h2>
<p><strong>#63 (ini)</strong> melengkapi CRUD rak buku: <code>PUT /api/buku/{id}</code> untuk memperbaiki data dan <code>DELETE /api/buku/{id}</code> untuk membuangnya, keduanya wajib kartu Sanctum dan mengembalikan <code>404</code> bila nomornya tidak ada. Semua bertumpu pada
<a href="/artikel/capstone-api-perpustakaan-laravel">Capstone: API Perpustakaan (#62)</a>,
<a href="/artikel/laravel-auth-api-dasar">Auth API Dasar: Login &amp; Kartu Anggota (#61)</a>, dan
<a href="/artikel/laravel-request-validasi-api">Request &amp; Form Request: Menjaga Input API (#59)</a>.</p>
<p><strong>Ke mana setelah ini?</strong><br>
Jalur “Laravel dari nol” tamat di sini — kamu sudah bisa membangun API kecil yang utuh dan berpagar. Langkah bagus berikutnya: ulangi <a href="/artikel/capstone-api-perpustakaan-laravel">Capstone: API Perpustakaan (#62)</a> tanpa melihat contoh, lalu lanjutkan ke topik Laravel lanjutan (relasi antar tabel, daftar berhalaman, izin per-pemilik, hapus lunak). Kalau ingin menyambungkan API ke perangkat nyata, lihat jalur <a href="/belajar/fullstack-iot">Full Stack IoT</a>.</p>

<blockquote>
  <p><strong>Seri 4 progress:</strong> langkah <strong>#63 (ini)</strong> · <strong>8/8</strong> jalur Laravel — <strong>tamat</strong> · prasyarat: <a href="/artikel/capstone-api-perpustakaan-laravel">Capstone: API Perpustakaan (#62)</a> LIVE.</p>
</blockquote>
HTML;
    }

    private function bodyEn(): string
    {
        $html = <<<'HTML'
<h2>Introduction — finishing the library shelf</h2>
<p>This article is <strong>#63 (this article)</strong> in <strong>Seri 4: Pemrograman Web Lanjut v2</strong> — step <strong>8/8</strong>, the closing chapter of the Laravel from-scratch path.</p>
<p>In <a href="/artikel/capstone-api-perpustakaan-laravel">Capstone: API Perpustakaan (#62)</a> staff can already <strong>read</strong> the catalog and <strong>add</strong> books with a card. But real life needs two more things: <strong>fixing</strong> data that was typed wrong, and <strong>removing</strong> books that are no longer on the shelf.</p>
<p><strong>Beginner:</strong> today we add the last two counters. The <em>update</em> counter = fix a catalog card that was typed wrong. The <em>delete</em> counter = pull a book off the shelf. Both require a staff card, just like the add counter.</p>

<blockquote>
  <p><strong>Prerequisites:</strong> you have finished <a href="/artikel/capstone-api-perpustakaan-laravel">Capstone: API Perpustakaan (#62)</a> (read + login + add already working), <a href="/artikel/laravel-auth-api-dasar">Auth API Dasar: Login &amp; Kartu Anggota (#61)</a> (Sanctum card), and <a href="/artikel/laravel-request-validasi-api">Request &amp; Form Request: Menjaga Input API (#59)</a> (slip bouncer). Foundation from <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a> / <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a>. Use <strong>Laravel 13+</strong> — requires <strong>PHP 8.3+</strong>.</p>
</blockquote>

<h2>Feature spec — what counts as “done” today?</h2>
<p>Just two sentences:</p>
<ol>
  <li><strong>Update one book</strong> — <code>PUT /api/buku/1</code> with a Bearer card + a valid title/author slip.</li>
  <li><strong>Delete one book</strong> — <code>DELETE /api/buku/1</code> with a Bearer card (no slip needed).</li>
</ol>
<p>The number <code>1</code> in that address is the <strong>book number</strong> (the <code>id</code> column in the database). If that number is not on the shelf, the answer is <code>404</code> — not a red error.</p>
<p><strong>Beginner:</strong> after today, your library shelf has all four moves: view, add, fix, remove. That is what people call <strong>CRUD</strong>.</p>

<figure style="background:#F5F5F0;border:2px solid #1a1a1a;border-radius:12px;padding:1rem;margin:1.25rem 0">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 760 240" width="100%" height="auto" role="img" aria-label="Four CRUD moves: read, add, update, delete">
  <title>Four CRUD moves on the book shelf</title>
  <defs>
    <marker id="laravel63crudArrow" markerWidth="8" markerHeight="8" refX="6" refY="4" orient="auto">
      <path d="M0,0 L8,4 L0,8 Z" fill="#1a1a1a"/>
    </marker>
  </defs>
  <rect x="12" y="36" width="164" height="88" rx="10" fill="#ffffff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="94" y="72" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Read</text>
  <text x="94" y="98" text-anchor="middle" fill="#1a1a1a" font-size="12">GET /api/buku</text>
  <line x1="182" y1="80" x2="212" y2="80" stroke="#1a1a1a" stroke-width="3" marker-end="url(#laravel63crudArrow)"/>
  <rect x="220" y="36" width="164" height="88" rx="10" fill="#ffffff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="302" y="72" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Add</text>
  <text x="302" y="98" text-anchor="middle" fill="#1a1a1a" font-size="12">POST /api/buku</text>
  <line x1="390" y1="80" x2="420" y2="80" stroke="#1a1a1a" stroke-width="3" marker-end="url(#laravel63crudArrow)"/>
  <rect x="428" y="36" width="150" height="88" rx="10" fill="#ffffff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="503" y="72" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Update</text>
  <text x="503" y="98" text-anchor="middle" fill="#1a1a1a" font-size="12">PUT /api/buku/1</text>
  <line x1="584" y1="80" x2="614" y2="80" stroke="#1a1a1a" stroke-width="3" marker-end="url(#laravel63crudArrow)"/>
  <rect x="622" y="36" width="126" height="88" rx="10" fill="#ffffff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="685" y="72" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Delete</text>
  <text x="685" y="98" text-anchor="middle" fill="#1a1a1a" font-size="12">DELETE /api/buku/1</text>
  <text x="20" y="176" fill="#1a1a1a" font-size="13">The first two boxes you built in the Capstone. The last two are installed today.</text>
  <text x="20" y="204" fill="#1a1a1a" font-size="13">Update &amp; delete always name the book number in the address — and always need a card.</text>
</svg>
<figcaption style="color:#1a1a1a">Full CRUD: read &amp; add have worked since the Capstone; update &amp; delete are installed today.</figcaption>
</figure>

<h2>Terms — update &amp; delete in brief</h2>
<table>
  <thead>
    <tr>
      <th>Term</th>
      <th>Plain meaning</th>
      <th>Notes</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>CRUD</td>
      <td>Four data moves: view, add, update, remove</td>
      <td>Today completes the last two</td>
    </tr>
    <tr>
      <td>PUT</td>
      <td>HTTP way to say “replace the contents of this record”</td>
      <td><code>PUT /api/buku/1</code></td>
    </tr>
    <tr>
      <td>DELETE</td>
      <td>HTTP way to say “throw away this record”</td>
      <td><code>DELETE /api/buku/1</code></td>
    </tr>
    <tr>
      <td>Number in the address</td>
      <td>The digit that points to which book you mean</td>
      <td>Written as <code>{id}</code> when registering the door</td>
    </tr>
    <tr>
      <td>Bearer</td>
      <td>How to show your staff card through a header</td>
      <td>Written as <code>Authorization: Bearer &lt;token&gt;</code> (from <a href="/artikel/laravel-auth-api-dasar">Auth API Dasar: Login &amp; Kartu Anggota (#61)</a>)</td>
    </tr>
    <tr>
      <td>404</td>
      <td>That number is not on the shelf</td>
      <td>A normal answer, not a broken app</td>
    </tr>
    <tr>
      <td>419</td>
      <td>Laravel suspects the request came from outside the browser</td>
      <td>Appears if the <code>api/*</code> exception is not set up yet</td>
    </tr>
  </tbody>
</table>

<h2>Preparation — tools to open</h2>
<p><strong>Tools used in this article</strong> (foundation from <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a> / <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a> — <strong>no</strong> new Composer downloads today):</p>
<ul>
  <li><strong>Explorer</strong> — make sure the <code>perpustakaan-api</code> folder exists; check <code>BukuController.php</code>, <code>routes/web.php</code>, and <code>bootstrap/app.php</code>.</li>
  <li><strong>Terminal</strong> — Laragon: <em>Terminal</em> menu · XAMPP: <em>Shell</em> button. Do not open CMD/PowerShell from the Start Menu (PHP PATH may be missing).</li>
  <li><strong>Second terminal</strong> — required: first terminal = <code>php artisan serve</code>. Second terminal = test with <code>curl.exe</code> / PowerShell (login + update + delete + read).</li>
  <li><strong>Text editor</strong> — Notepad / VS Code — add <code>update</code> &amp; <code>destroy</code> methods, two new routes, and one small permission in <code>bootstrap\app.php</code>. Tip: <code>notepad app\Http\Controllers\BukuController.php</code> from the second terminal (change the filename to match what you want to open).</li>
  <li><strong>Browser</strong> — only to check the shop light and view the book list (GET). Update &amp; delete <strong>cannot</strong> be tested from the browser address bar.</li>
</ul>
<p><strong>How to open a second terminal</strong> (first time opening two terminals at once? Here is how — do not close the first one): <strong>Laragon</strong> — click the <em>Terminal</em> menu again in the main Laragon window; a new terminal window will appear separate from the first. <strong>XAMPP</strong> — in XAMPP Control Panel, click the <em>Shell</em> button again; a second Shell window will open. Both windows can stay open — the first keeps running <code>php artisan serve</code>, the second is for typing other commands.</p>
<p>Open the Laragon terminal / XAMPP Shell (first terminal), go to the project folder:</p>
<pre><code class="language-bash">cd C:\laragon\www\perpustakaan-api
</code></pre>
<p>On XAMPP it is usually: <code>cd C:\xampp\htdocs\perpustakaan-api</code>. Adjust the path if your folder is different.</p>
<p>Turn on the shop light in the <strong>first terminal</strong>:</p>
<pre><code class="language-bash">php artisan serve
</code></pre>
<p>Leave that window running. Open a <strong>second terminal</strong> (how to do that is explained above), <code>cd</code> to the same project folder — this is where you test update &amp; delete.</p>
<p><strong>Beginner:</strong> Terminal 1 = shop light. Terminal 2 = hands testing the two new counters. Editor = writing <code>update</code> &amp; <code>destroy</code>. Browser = just peeking at the list.</p>
<p><strong>Install-from-scratch:</strong> if Sanctum / login is not set up yet, finish <a href="/artikel/laravel-auth-api-dasar">Auth API Dasar: Login &amp; Kartu Anggota (#61)</a> first. If PHP/Composer is not recognized in the terminal, go back to <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a>.</p>

<h2>Quick check — pieces that must already exist</h2>
<p>Before adding new counters, make sure these already work in your project:</p>
<ul>
  <li><code>GET /api/buku</code> shows the list (<a href="/artikel/laravel-controller-service-eloquent">Controller, Service &amp; Eloquent Laravel (#60)</a>)</li>
  <li><code>POST /api/login</code> returns a <code>token</code> (<a href="/artikel/laravel-auth-api-dasar">Auth API Dasar: Login &amp; Kartu Anggota (#61)</a>)</li>
  <li><code>POST /api/buku</code> with a card successfully adds a book (<a href="/artikel/capstone-api-perpustakaan-laravel">Capstone: API Perpustakaan (#62)</a>)</li>
  <li>File <code>app\Models\Buku.php</code> has the line <code>protected $fillable = ['judul', 'penulis'];</code> (from <a href="/artikel/laravel-controller-service-eloquent">Controller, Service &amp; Eloquent Laravel (#60)</a>)</li>
</ul>
<p>If any of these do not work yet, fix them in the original article first. The update &amp; delete counters only ride on paths that already exist.</p>
<p><strong>Beginner — why must you check that <code>$fillable</code> line?</strong> That line is the list of columns allowed to be written at once. The <em>update</em> counter writes title and author in one move, so if the list is empty, Laravel <strong>silently drops</strong> your changes: the answer still says “Buku diperbarui”, but the book content does not change at all. This is the only mistake today that gives no red error message, so open that file and make sure the line is there.</p>

<h2>Book number in the address — where does the digit come from?</h2>
<p>This is the first beginner question: <em>“<code>PUT /api/buku/1</code> — do I make up the number 1 myself?”</em> No. The number comes from the book list.</p>
<p>In the <strong>second terminal</strong>, look at the shelf first:</p>
<pre><code class="language-bash">curl.exe -s http://127.0.0.1:8000/api/buku ^
  -H "Accept: application/json"
</code></pre>
<p>In the JSON response, each book has an <code>"id"</code> key. That is the book number. If book “Bumi” has <code>"id": 3</code>, the address to update it is <code>/api/buku/3</code>.</p>
<p><strong>Beginner:</strong> <code>{id}</code> when registering a route is an <em>empty slot</em>. When testing, you fill that slot with a real number from the list. Wrong number = <code>404</code> answer, not a broken app.</p>
<p><strong>If the answer is <code>"data": []</code> (empty)</strong> — the shelf really has no books yet, and that is normal if in <a href="/artikel/laravel-controller-service-eloquent">Controller, Service &amp; Eloquent Laravel (#60)</a> you have not added rows yet. There is no number to update or delete. Fill the shelf first: work through <strong>“Test update &amp; delete”</strong> below up to step <strong>1) Login</strong> to get a card, then add one or two books with <code>POST /api/buku</code> following <a href="/artikel/capstone-api-perpustakaan-laravel">Capstone: API Perpustakaan (#62)</a>. Once the shelf has books, come back here and note their <code>"id"</code> values.</p>

<figure style="background:#F5F5F0;border:2px solid #1a1a1a;border-radius:12px;padding:1rem;margin:1.25rem 0">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 760 200" width="100%" height="auto" role="img" aria-label="Number exists means success, number missing means 404">
  <title>Number exists versus number missing</title>
  <rect x="40" y="40" width="290" height="100" rx="10" fill="#ffffff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="185" y="80" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Number on shelf</text>
  <text x="185" y="108" text-anchor="middle" fill="#1a1a1a" font-size="13">update / delete succeeds</text>
  <rect x="430" y="40" width="290" height="100" rx="10" fill="#ffffff" stroke="#1a1a1a" stroke-width="2"/>
  <text x="575" y="80" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Number missing</text>
  <text x="575" y="108" text-anchor="middle" fill="#1a1a1a" font-size="13">404 answer, shelf safe</text>
  <text x="24" y="175" fill="#1a1a1a" font-size="13">Without a Bearer card, both are rejected first — before the number even matters.</text>
</svg>
<figcaption style="color:#1a1a1a">Check the number first, then worry about content. Without a card, the door does not open at all.</figcaption>
</figure>

<h2>Update counter — the update method</h2>
<p>Open <code>notepad app\Http\Controllers\BukuController.php</code>. Do not delete the <code>namespace</code> line or the <code>class BukuController extends Controller</code> skeleton — we are only adding.</p>
<p><strong>Part 1 — <code>use</code> lines, placed ABOVE <code>class</code></strong> (aligned with existing <code>use</code> lines, not inside the class):</p>
<pre><code class="language-php">use App\Http\Requests\StoreBukuRequest; // slip yang sama boleh dipakai ulang
use App\Models\Buku;
use Illuminate\Http\JsonResponse;
</code></pre>
<p><strong>Beginner — IMPORTANT, do not write these twice:</strong> if any line above <strong>already exists</strong> in your file, <em>skip</em> that line. Usually <code>Illuminate\Http\JsonResponse</code> and <code>App\Models\Buku</code> were already written in <a href="/artikel/laravel-controller-service-eloquent">Controller, Service &amp; Eloquent Laravel (#60)</a>, and <code>StoreBukuRequest</code> since <a href="/artikel/capstone-api-perpustakaan-laravel">Capstone: API Perpustakaan (#62)</a> — so often <strong>nothing</strong> needs to be added here. If written twice, PHP stops with <code>Cannot use ... because the name is already in use</code>.</p>
<p><strong>Beginner — why must they be above class?</strong> A <code>use</code> line above class means “pull in a file from another folder”. A <code>use</code> line written <em>inside</em> class means something completely different (attaching a trait), and PHP will complain <code>Trait "App\Models\Buku" not found</code>. So watch the placement carefully.</p>
<p><strong>Part 2 — the <code>update</code> method, placed INSIDE the class</strong>, next to <code>store</code> that you built in <a href="/artikel/capstone-api-perpustakaan-laravel">Capstone: API Perpustakaan (#62)</a>:</p>
<pre><code class="language-php">// Cuplikan BukuController — loket ubah buku (di dalam class)
public function update(StoreBukuRequest $request, int $id): JsonResponse
{
    $buku = Buku::find($id);

    if (! $buku) {
        return response()-&gt;json(['message' =&gt; 'Buku tidak ditemukan'], 404);
    }

    $buku-&gt;update($request-&gt;validated());

    return response()-&gt;json([
        'message' =&gt; 'Buku diperbarui',
        'data' =&gt; $buku,
    ]);
}
</code></pre>
<p><strong>Beginner:</strong> just three steps — find the book by number, if missing answer <code>404</code>, if found rewrite its contents then answer with JSON. The slip bouncer (<code>StoreBukuRequest</code>) still runs first, so an empty title will not pass.</p>
<p><strong>Note:</strong> match the Form Request name to the file in your Explorer. If it does not exist yet, create it following <a href="/artikel/laravel-request-validasi-api">Request &amp; Form Request: Menjaga Input API (#59)</a>.</p>
<p><strong>“Wait, why not go through <code>BukuService</code>?”</strong> In <a href="/artikel/laravel-controller-service-eloquent">Controller, Service &amp; Eloquent Laravel (#60)</a> we used a “kitchen” (<code>BukuService</code>) so the counter does not busy itself with the table. Here <code>Buku::find($id)</code> is called directly from the counter so the snippet stays short and you see <em>clearly</em> which line births the <code>404</code>. If you already have <code>BukuService</code>, feel free to move <code>find</code>/<code>update</code>/<code>delete</code> there as <code>ubah()</code> and <code>hapus()</code> methods — same result, tidier kitchen.</p>

<h2>Delete counter — the destroy method</h2>
<p>Still in the same file, still <strong>inside the class</strong>, add the <code>destroy</code> method below <code>update</code>. This part needs no new <code>use</code> lines — everything is already available from the previous step:</p>
<pre><code class="language-php">// Cuplikan BukuController — loket hapus buku (di dalam class)
public function destroy(int $id): JsonResponse
{
    $buku = Buku::find($id);

    if (! $buku) {
        return response()-&gt;json(['message' =&gt; 'Buku tidak ditemukan'], 404);
    }

    $buku-&gt;delete();

    return response()-&gt;json(['message' =&gt; 'Buku dihapus']);
}
</code></pre>
<p>Save the file. <strong>Beginner:</strong> delete does not need a title/author slip — just the book number and staff card. That is why <code>destroy</code> does not use a Form Request.</p>

<h2>Wire the doors in routes</h2>
<p>Open <code>notepad routes\web.php</code>. You only need to type the <strong>two lines marked NEW</strong> below — the rest already exists in your file from earlier articles, shown so you know the exact placement:</p>
<pre><code class="language-php">// Cuplikan routes/web.php — bentuk AKHIR file setelah hari ini
use App\Http\Controllers\AuthController;   // sudah ada dari Auth API
use App\Http\Controllers\BukuController;   // sudah ada dari Controller/Service

Route::get('/api/buku', [BukuController::class, 'index']);   // sudah ada dari Controller/Service
Route::post('/api/login', [AuthController::class, 'login']); // sudah ada dari Auth API

Route::middleware('auth:sanctum')-&gt;group(function () {
    Route::get('/api/saya', [AuthController::class, 'saya']);            // sudah ada dari Auth API
    Route::post('/api/buku', [BukuController::class, 'store']);          // sudah ada dari Capstone
    Route::put('/api/buku/{id}', [BukuController::class, 'update']);     // &lt;-- BARU hari ini
    Route::delete('/api/buku/{id}', [BukuController::class, 'destroy']); // &lt;-- BARU hari ini
});
</code></pre>
<p><strong>Beginner — do not paste this whole block as-is.</strong> If you paste it below existing content, <code>use</code> lines become duplicates and PHP stops with <code>Cannot use ... name is already in use</code>. If you overwrite the whole file, other routes (including normal pages in your project) may disappear. Find the existing <code>auth:sanctum</code> group in your file, then insert the two <code>Route::put</code> and <code>Route::delete</code> lines inside it.</p>
<p>Save. Make sure <code>serve</code> is still running in the first terminal.</p>
<p><strong>Beginner:</strong> <code>{id}</code> is an empty slot for the book number. The two new doors are deliberately placed <em>inside</em> <code>auth:sanctum</em> — without a card, guests may not update or delete anything.</p>
<p><strong>Make sure Laravel really sees the two new doors.</strong> In the <strong>second terminal</strong> (leave <code>serve</code> running in the first), run:</p>
<pre><code class="language-bash">php artisan route:list --path=api/buku
</code></pre>
<p>You should see four lines: <code>GET</code>, <code>POST</code>, <code>PUT</code>, and <code>DELETE</code>. If <code>PUT</code>/<code>DELETE</code> do not appear, <code>routes\web.php</code> was not saved or the verbs were mistyped — fix that before continuing to test.</p>

<h2>Check the api/* permission — so PUT &amp; DELETE are not rejected with 419</h2>
<p>The lobby <strong>CSRF</strong> guard was set up in <a href="/artikel/laravel-request-validasi-api">Request &amp; Form Request: Menjaga Input API (#59)</a> (and rechecked in <a href="/artikel/laravel-auth-api-dasar">Auth API Dasar: Login &amp; Kartu Anggota (#61)</a> / <a href="/artikel/capstone-api-perpustakaan-laravel">Capstone: API Perpustakaan (#62)</a>). Its job is to protect <em>web forms</em>: reject <code>POST</code>, <code>PUT</code>, and <code>DELETE</code> submissions that do not come from that site’s own browser pages.</p>
<p>Because we test from the <strong>terminal</strong> (not the browser), without the <code>api/*</code> exception the answer is:</p>
<pre><code class="language-json">{"message":"CSRF token mismatch."}
</code></pre>
<p>with status <strong>419</strong>. This does <em>not</em> mean your code is wrong — our door was not even reached yet.</p>
<p><strong>If <code>'api/*'</code> is already inside <code>withMiddleware</code></strong> — <strong>skip this step</strong>. Once per project is enough; today’s new doors are covered too.</p>
<p><strong>Not there yet / you jumped to this article?</strong> Make sure the second terminal is in the project folder (<code>cd</code> to <code>perpustakaan-api</code>), then <code>notepad bootstrap\app.php</code>. <strong>Do not paste</strong> a new <code>-&gt;withMiddleware(...)</code> block inside the existing function. Work inside that function: remove <code>//</code> if still there, leave other lines, then paste <strong>only</strong>:</p>
<pre><code class="language-php">// Tempel di dalam withMiddleware yang sudah ada — jangan buat withMiddleware baru
$middleware-&gt;preventRequestForgery(except: [
    'api/*',
]);
</code></pre>
<p>The correct result looks roughly like this (other lines around it are fine):</p>
<pre><code class="language-php">-&gt;withMiddleware(function (Middleware $middleware): void {
    $middleware-&gt;preventRequestForgery(except: [
        'api/*',
    ]);
})
</code></pre>
<p>Save. <strong>Beginner:</strong> <code>except</code> = “except”. The <code>*</code> means “anything after that”, so <code>api/buku/3</code> and <code>api/login</code> are included. In Laravel 13 the official name is <code>preventRequestForgery</code>; the old name <code>validateCsrfTokens</code> means the same thing.</p>
<p><strong>Does this make my API unsafe?</strong> No. The fence guarding update &amp; delete is the <strong>Sanctum card guard</strong> (<code>auth:sanctum</code>) — without a card you are still rejected. CSRF is the lobby guard for browser forms; token-based APIs use a different kind of fence.</p>
<p><strong>Every time you save <code>bootstrap\app.php</code></strong>, stop <code>serve</code> in the first terminal (<code>Ctrl+C</code>) then start <code>php artisan serve</code> again. That file is only read when the app starts.</p>

<h2>Test update &amp; delete in the second terminal</h2>
<p>The <code>PUT</code> and <code>DELETE</code> doors <strong>cannot</strong> be tried from the browser address bar — browsers only do GET. So test via the second terminal (or a windowed tool like Postman in Option C).</p>
<p>Make sure the test user still exists. Run in the <strong>second terminal</strong> (leave <code>serve</code> running in the first) — one shot, not a long chat:</p>
<pre><code class="language-bash">php artisan tinker --execute="\App\Models\User::updateOrCreate(['email'=>'staf@perpustakaan.test'], ['name'=>'Staf Mini','password'=>bcrypt('password')]);"
</code></pre>

<p>Pick <strong>one</strong> option below (A, B, or C) — do not run two options on the same book number, because a book deleted in the first try is gone for the second (the answer will be <code>404</code>).</p>

<p><strong>Option A — <code>curl.exe</code></strong> (Windows 10/11 usually has it; type <code>curl.exe</code> so PowerShell does not confuse it):</p>
<p><strong>Beginner — about the <code>^</code> at the end of a line:</strong> that means “this command continues on the next line”, and it works in <strong>CMD / XAMPP Shell / Laragon Terminal</strong>. If you use <strong>PowerShell</strong>, <code>^</code> is not recognized — replace each <code>^</code> with a backtick <code>`</code>, or safest: type the whole command on <strong>one long line</strong> with no <code>^</code> at all.</p>
<p><strong>1) Login — get the token</strong></p>
<pre><code class="language-bash">curl.exe -s -X POST http://127.0.0.1:8000/api/login ^
  -H "Content-Type: application/json" ^
  -H "Accept: application/json" ^
  -d "{\"email\":\"staf@perpustakaan.test\",\"password\":\"password\"}"
</code></pre>
<p><strong>Beginner — copy the token:</strong> in the JSON response find the <code>"token"</code> key. Copy <em>only</em> the string between the quotes. Do not copy the word <code>Bearer</code> from the JSON — Bearer is written in the header. Replace <code>GANTI_DENGAN_TOKEN</code> below.</p>
<p><strong>Beginner — how to copy text from the Windows terminal:</strong> select text with left-click and hold while dragging (click-drag), release the mouse button to copy automatically. Paste with right-click in the terminal window (not <code>Ctrl+V</code> — the default Windows terminal sometimes does not support it).</p>
<p><strong>2) See the book number first</strong> — note one <code>"id"</code> from this response, then use that number in the next steps:</p>
<pre><code class="language-bash">curl.exe -s http://127.0.0.1:8000/api/buku ^
  -H "Accept: application/json"
</code></pre>
<p><strong>Beginner — two words you must replace yourself</strong> in all commands below: replace <code>GANTI_DENGAN_TOKEN</code> with the token from step 1, and replace <code>NOMOR</code> with the <code>"id"</code> number you noted in step 2. Example: if <code>id</code> is 3, type <code>/api/buku/NOMOR</code> as <code>/api/buku/3</code>. Do not leave the word <code>NOMOR</code> typed in.</p>
<p><strong>3) Update without a card — must be rejected</strong></p>
<pre><code class="language-bash">curl.exe -s -X PUT http://127.0.0.1:8000/api/buku/NOMOR ^
  -H "Content-Type: application/json" ^
  -H "Accept: application/json" ^
  -d "{\"judul\":\"Coba Ubah\",\"penulis\":\"Tanpa Kartu\"}"
</code></pre>
<p>You should see an “Unauthenticated” / not-authorized message — not “Buku diperbarui”. If you get <code>CSRF token mismatch</code> instead, the <code>api/*</code> permission in <code>bootstrap\app.php</code> is not set up — go back to the previous section.</p>
<p><strong>4) Update with a card — must succeed</strong></p>
<pre><code class="language-bash">curl.exe -s -X PUT http://127.0.0.1:8000/api/buku/NOMOR ^
  -H "Content-Type: application/json" ^
  -H "Accept: application/json" ^
  -H "Authorization: Bearer GANTI_DENGAN_TOKEN" ^
  -d "{\"judul\":\"Bumi (Edisi Revisi)\",\"penulis\":\"Tere Liye\"}"
</code></pre>
<p>A correct answer looks roughly like this (all on one long line — that is normal):</p>
<pre><code class="language-json">{"message":"Buku diperbarui","data":{"id":3,"judul":"Bumi (Edisi Revisi)","penulis":"Tere Liye","created_at":"2026-07-20T08:11:02.000000Z","updated_at":"2026-07-27T02:45:13.000000Z"}}
</code></pre>
<p><strong>Beginner:</strong> <code>created_at</code> and <code>updated_at</code> appear even though you did not send them — those are two timestamp columns Laravel fills itself. Notice <code>updated_at</code>: the time is new, proof the book was touched.</p>
<p><strong>5) Read the catalog — make sure the title really changed</strong></p>
<pre><code class="language-bash">curl.exe -s http://127.0.0.1:8000/api/buku ^
  -H "Accept: application/json"
</code></pre>
<p><strong>Do not skip this step.</strong> If step 4 answered “Buku diperbarui” but the list here still shows the <em>old</em> title, the cause is almost certainly that <code>$fillable</code> in <code>app\Models\Buku.php</code> does not include <code>'judul'</code> and <code>'penulis'</code> (see “Quick check” above). This is the only failure today that disguises itself as success.</p>
<p><strong>6) Update a number that does not exist — must be 404</strong></p>
<pre><code class="language-bash">curl.exe -s -X PUT http://127.0.0.1:8000/api/buku/9999 ^
  -H "Content-Type: application/json" ^
  -H "Accept: application/json" ^
  -H "Authorization: Bearer GANTI_DENGAN_TOKEN" ^
  -d "{\"judul\":\"Buku Hantu\",\"penulis\":\"Tidak Ada\"}"
</code></pre>
<p>Correct answer: <code>{"message":"Buku tidak ditemukan"}</code> (the message <em>Buku tidak ditemukan</em> means “book not found”). The number <code>9999</code> here is deliberately made up — no need to replace it.</p>
<p><strong>7) Delete with a card</strong> (note: no <code>-d</code>, because delete does not send a slip). You may use the book you updated in step 4 — you already proved the update in step 5:</p>
<pre><code class="language-bash">curl.exe -s -X DELETE http://127.0.0.1:8000/api/buku/NOMOR ^
  -H "Accept: application/json" ^
  -H "Authorization: Bearer GANTI_DENGAN_TOKEN"
</code></pre>
<p>Correct answer: <code>{"message":"Buku dihapus"}</code> (meaning “book deleted”).</p>
<p><strong>8) Read the catalog — that book must be gone</strong></p>
<pre><code class="language-bash">curl.exe -s http://127.0.0.1:8000/api/buku ^
  -H "Accept: application/json"
</code></pre>
<p>The book with that number no longer appears in the list. All four CRUD moves are complete.</p>

<p><strong>Option B — PowerShell</strong> (token is stored automatically in a variable — no manual copy-paste). Stay in <strong>Laragon / XAMPP Shell</strong> if that window is already PowerShell (often the case). If you open PowerShell from the Start Menu just for Option B: that is fine, because the commands below use <code>Invoke-RestMethod</code> (no <code>php</code> needed) — but for <code>tinker</code> / <code>route:list</code> earlier, still use Laragon/XAMPP Shell. <code>cd</code> to the project folder, then type these lines. In PowerShell the line-continuation character is backtick <code>`</code> (not <code>^</code>):</p>
<pre><code class="language-powershell">$login = Invoke-RestMethod -Method Post -Uri http://127.0.0.1:8000/api/login `
  -ContentType "application/json" `
  -Body '{"email":"staf@perpustakaan.test","password":"password"}'
$token = $login.token

# Lihat isi rak dulu, catat salah satu id
Invoke-RestMethod -Uri http://127.0.0.1:8000/api/buku -Headers @{ Accept = "application/json" }

# GANTI angka 3 di bawah dengan id buku milikmu
$nomor = 3

Invoke-RestMethod -Method Put -Uri "http://127.0.0.1:8000/api/buku/$nomor" `
  -ContentType "application/json" `
  -Headers @{ Authorization = "Bearer $token"; Accept = "application/json" } `
  -Body '{"judul":"Bumi (Edisi Revisi)","penulis":"Tere Liye"}'

# Buktikan judulnya benar-benar berubah
Invoke-RestMethod -Uri http://127.0.0.1:8000/api/buku -Headers @{ Accept = "application/json" }

Invoke-RestMethod -Method Delete -Uri "http://127.0.0.1:8000/api/buku/$nomor" `
  -Headers @{ Authorization = "Bearer $token"; Accept = "application/json" }

Invoke-RestMethod -Uri http://127.0.0.1:8000/api/buku -Headers @{ Accept = "application/json" }
</code></pre>
<p><strong>Beginner:</strong> lines starting with <code>#</code> are notes for humans — PowerShell ignores them, so you may paste them too. Also note: if something is rejected (for example the number was already deleted), PowerShell shows a long red message and <em>stops</em> there. That is normal — read the first line for the status code (<code>404</code>, <code>401</code>, or <code>419</code>), fix it, then run again from the line that failed.</p>

<p><strong>Option C — Postman / Insomnia</strong> (windowed tool — most comfortable for PUT/DELETE because you do not fight quotes). Download and install Postman first if you do not have it, then:</p>
<ol>
  <li><strong>Get the token.</strong> New request, method <strong>POST</strong>, URL <code>http://127.0.0.1:8000/api/login</code>. <em>Headers</em> tab: add <code>Accept</code> with value <code>application/json</code>. <em>Body</em> tab: choose <strong>raw</strong> then <strong>JSON</strong>, body <code>{"email":"staf@perpustakaan.test","password":"password"}</code>. Press <strong>Send</strong>, then copy the <code>token</code> value from the response.</li>
  <li><strong>Find the book number.</strong> Method <strong>GET</strong>, URL <code>http://127.0.0.1:8000/api/buku</code>, press <strong>Send</strong>, note one <code>"id"</code>.</li>
  <li><strong>Update.</strong> Method <strong>PUT</strong>, URL <code>http://127.0.0.1:8000/api/buku/NOMOR</code> — replace <code>NOMOR</code> with the <code>"id"</code> from step 2 (not always 3). <em>Authorization</em> tab: type <strong>Bearer Token</strong>, paste token from step 1. <em>Body</em> tab: <strong>raw</strong> + <strong>JSON</strong>, new title and author. <strong>Send</strong>.</li>
  <li><strong>Delete.</strong> Change method to <strong>DELETE</strong>, clear Body, keep Authorization filled, same URL number. <strong>Send</strong>.</li>
</ol>
<p><strong>Beginner:</strong> in Postman you paste the token once on the Authorization tab — Postman writes the <code>Authorization: Bearer ...</code> header for you. Status codes (200, 401, 404, 419) show clearly in the response panel corner, so this option is nicest for beginners still nervous about the terminal.</p>

<h2>Basic Pattern — six steps to complete CRUD</h2>
<figure role="img" aria-label="Basic Pattern six steps to complete update and delete CRUD" style="background:#F5F5F0;border:2px solid #1a1a1a;border-radius:12px;padding:1rem;margin:1.25rem 0">
<ol style="list-style:none;padding:0;margin:0;display:grid;gap:.75rem">
  <li style="display:flex;gap:.75rem;align-items:flex-start">
    <span style="flex:0 0 2rem;height:2rem;border-radius:999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div><strong style="color:#1a1a1a">Open tools</strong><br><span style="color:#1a1a1a">Terminal 1 <code>serve</code> · Terminal 2 test · Editor · Browser peek.</span></div>
  </li>
  <li style="display:flex;gap:.75rem;align-items:flex-start">
    <span style="flex:0 0 2rem;height:2rem;border-radius:999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div><strong style="color:#1a1a1a">Check foundation</strong><br><span style="color:#1a1a1a">Read, login, add from Capstone still work · <code>$fillable</code> in Model.</span></div>
  </li>
  <li style="display:flex;gap:.75rem;align-items:flex-start">
    <span style="flex:0 0 2rem;height:2rem;border-radius:999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div><strong style="color:#1a1a1a">Write two counters</strong><br><span style="color:#1a1a1a"><code>update</code> then <code>destroy</code> inside class <code>BukuController</code>.</span></div>
  </li>
  <li style="display:flex;gap:.75rem;align-items:flex-start">
    <span style="flex:0 0 2rem;height:2rem;border-radius:999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div><strong style="color:#1a1a1a">Register doors</strong><br><span style="color:#1a1a1a"><code>PUT</code> &amp; <code>DELETE</code> inside <code>auth:sanctum</code> · check <code>route:list</code>.</span></div>
  </li>
  <li style="display:flex;gap:.75rem;align-items:flex-start">
    <span style="flex:0 0 2rem;height:2rem;border-radius:999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">5</span>
    <div><strong style="color:#1a1a1a">Check <code>api/*</code> permission</strong><br><span style="color:#1a1a1a">Already there? skip. Not yet? paste line inside <code>withMiddleware</code> · restart <code>serve</code>.</span></div>
  </li>
  <li style="display:flex;gap:.75rem;align-items:flex-start">
    <span style="flex:0 0 2rem;height:2rem;border-radius:999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">6</span>
    <div><strong style="color:#1a1a1a">Test in order</strong><br><span style="color:#1a1a1a">See number -&gt; update without card fails -&gt; update with card succeeds -&gt; read again to prove -&gt; bogus number returns 404 -&gt; delete -&gt; list shrinks.</span></div>
  </li>
</ol>
</figure>
<p><strong style="color:#1a1a1a">Beginner:</strong> <span style="color:#1a1a1a">always test the “fail” version first (no card, bogus number), then the “success” version. If the fail case succeeds, the card guard is not wired correctly.</span></p>

<h2>Example file — simulate update &amp; delete</h2>
<p>Save as <code>laravel_crud_api_buku_ubah_hapus_demo.php</code> then run in the second terminal: <code>php laravel_crud_api_buku_ubah_hapus_demo.php</code>. This is <em>not</em> real Laravel — it only mimics the flow so you can feel it before doing it in the project.</p>
<pre><code class="language-php">&lt;?php
declare(strict_types=1);

/**
 * laravel_crud_api_buku_ubah_hapus_demo.php
 * Simulasi ubah (PUT) dan hapus (DELETE) satu buku berdasarkan nomor.
 */

function cariBaris(array $rak, int $id): ?int
{
    foreach ($rak as $baris =&gt; $buku) {
        if ($buku['id'] === $id) {
            return $baris;
        }
    }

    return null;
}

function demo(): void
{
    $rak = [
        ['id' =&gt; 1, 'judul' =&gt; 'Bumi', 'penulis' =&gt; 'Tere Liye'],
        ['id' =&gt; 2, 'judul' =&gt; 'Laskar Pelangi', 'penulis' =&gt; 'Andrea Hirata'],
    ];

    echo "1) PUT /api/buku/2 dengan kartu\n";
    $baris = cariBaris($rak, 2);
    if ($baris !== null) {
        $rak[$baris]['judul'] = 'Laskar Pelangi (Edisi Revisi)';
        echo json_encode(['message' =&gt; 'Buku diperbarui', 'data' =&gt; $rak[$baris]], JSON_UNESCAPED_UNICODE), PHP_EOL;
    }

    echo "2) PUT /api/buku/9999 - nomor tidak ada\n";
    $baris = cariBaris($rak, 9999);
    echo json_encode(['message' =&gt; $baris === null ? 'Buku tidak ditemukan' : 'Buku diperbarui'], JSON_UNESCAPED_UNICODE), PHP_EOL;

    echo "3) DELETE /api/buku/1 dengan kartu\n";
    $baris = cariBaris($rak, 1);
    if ($baris !== null) {
        unset($rak[$baris]);
        $rak = array_values($rak);
    }
    echo json_encode(['message' =&gt; 'Buku dihapus'], JSON_UNESCAPED_UNICODE), PHP_EOL;

    echo "4) GET /api/buku - sisa rak\n";
    echo json_encode(['message' =&gt; 'Daftar buku', 'data' =&gt; $rak], JSON_UNESCAPED_UNICODE), PHP_EOL;
    echo PHP_EOL, "Langkah sungguhan: serve -&gt; login -&gt; Bearer PUT/DELETE /api/buku/{nomor} -&gt; GET daftar.", PHP_EOL;
}

demo();
</code></pre>
<p><strong>Beginner:</strong> <code>declare(strict_types=1);</code> = PHP is stricter about types in this example file. <code>?int</code> means the function may return a number <em>or</em> “nothing”. In real Laravel, finding the row is done by <code>Buku::find($id)</code>.</p>

<h2>Common mistakes</h2>
<p>Find the symptom you see in the first column — usually the cause is just one small thing.</p>
<table>
  <thead>
    <tr>
      <th>What you see</th>
      <th>Cause</th>
      <th>Fix</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><code>CSRF token mismatch</code> / status <code>419</code></td>
      <td><code>api/*</code> exception not set up, or <code>serve</code> not restarted after saving <code>bootstrap\app.php</code></td>
      <td>Add <code>preventRequestForgery(except: ['api/*'])</code> (do not overwrite other lines in <code>withMiddleware</code>), then <code>Ctrl+C</code> and <code>php artisan serve</code> again</td>
    </tr>
    <tr>
      <td><code>Cannot use ... name is already in use</code></td>
      <td><code>use</code> line written twice because it already existed from earlier Controller/Capstone articles</td>
      <td>Remove duplicate <code>use</code> lines — one per name is enough</td>
    </tr>
    <tr>
      <td><code>Trait "App\Models\Buku" not found</code></td>
      <td><code>use</code> line pasted <em>inside</em> class, not above it</td>
      <td>Move <code>use</code> lines above <code>class BukuController</code></td>
    </tr>
    <tr>
      <td>“Buku diperbarui” but list still shows old title</td>
      <td><code>$fillable</code> in <code>app\Models\Buku.php</code> missing <code>'judul'</code> and <code>'penulis'</code></td>
      <td>Add both columns to <code>$fillable</code>, then update again</td>
    </tr>
    <tr>
      <td><code>405 Method Not Allowed</code></td>
      <td>Route saved but wrong verb (e.g. <code>Route::post</code> while testing <code>PUT</code>)</td>
      <td>Check with <code>php artisan route:list --path=api/buku</code></td>
    </tr>
    <tr>
      <td><code>404</code> without <code>"Buku tidak ditemukan"</code> message</td>
      <td>That is Laravel’s 404: <code>PUT</code>/<code>DELETE</code> route not registered</td>
      <td>Save <code>routes\web.php</code>, check <code>route:list</code>. If the message <em>is</em> there, that is normal — the number really does not exist</td>
    </tr>
    <tr>
      <td>Status <code>401</code> / “Unauthenticated” even though token was pasted</td>
      <td>Token copied partially, or the word <code>Bearer</code> copied from JSON too</td>
      <td>Copy again <em>only</em> the <code>"token"</code> value; type <code>Bearer</code> manually in the header</td>
    </tr>
    <tr>
      <td><code>curl</code> command splits into several errors</td>
      <td><code>^</code> used in PowerShell</td>
      <td>Replace <code>^</code> with backtick <code>`</code>, or type one long line</td>
    </tr>
    <tr>
      <td>Long HTML page, not JSON</td>
      <td><code>Accept</code> header not sent</td>
      <td>Add <code>-H "Accept: application/json"</code></td>
    </tr>
    <tr>
      <td>Connection failed / <em>connection refused</em></td>
      <td><code>serve</code> stopped in the first terminal</td>
      <td>Start <code>php artisan serve</code> again, leave that window running</td>
    </tr>
    <tr>
      <td><code>php is not recognized</code></td>
      <td>Terminal opened from Start Menu, PHP PATH missing</td>
      <td>Use Laragon <em>Terminal</em> menu or XAMPP <em>Shell</em> button</td>
    </tr>
  </tbody>
</table>
<p>Two other things that often confuse people but are not really errors: <strong>trying PUT/DELETE from the browser address bar</strong> (browsers only do GET — use the second terminal or Postman), and <strong>sending <code>-d</code> when deleting</strong> (no slip needed for delete; just number + card). And if the two new doors were placed <em>outside</em> <code>auth:sanctum</code>, anyone could delete books — make sure both are inside the card-protected group.</p>

<h2>Practice</h2>
<ol>
  <li>Update one book’s title, then <code>GET /api/buku</code> — make sure the new title is really saved.</li>
  <li>Try updating with an empty slip (<code>{}</code>) using a card — make sure the Form Request bouncer rejects it, not save an empty title.</li>
  <li>Delete one book, then delete the same number again — notice the second answer should be <code>404</code>.</li>
  <li>Add a new book (from <a href="/artikel/capstone-api-perpustakaan-laravel">Capstone: API Perpustakaan (#62)</a>), update its title, then delete — feel all four CRUD moves in one breath.</li>
  <li>(Optional) Try delete without a card — make sure it is rejected before the number even matters.</li>
</ol>

<h2>FAQ</h2>
<p><strong>Why <code>PUT</code>, not <code>POST</code>?</strong><br>
Both send data, but <code>PUT</code> means “replace the contents of the record whose number I already named”. Using the right verb makes API doors easier for others to read.</p>
<p><strong>Can I use <code>PATCH</code>?</strong><br>
Yes. <code>PATCH</code> is usually for changing only some columns. For this exercise <code>PUT</code> is simpler because we send title and author together.</p>
<p><strong>Why <code>Buku::find</code> then check yourself, not <code>findOrFail</code>?</strong><br>
<code>findOrFail</code> is also correct and shorter. We use <code>find</code> + <code>if</code> so you see yourself when status <code>404</code> appears, not “magically” from Laravel.</p>
<p><strong>Do I need to download anything new today?</strong><br>
No. Update &amp; delete only add two methods in the Controller, two lines in routes, and one small permission in <code>bootstrap\app.php</code> — no new Composer packages. If Sanctum is not installed yet, do install-from-scratch in <a href="/artikel/laravel-auth-api-dasar">Auth API Dasar: Login &amp; Kartu Anggota (#61)</a> first.</p>
<p><strong>Why 419 / CSRF token mismatch when testing from the terminal?</strong><br>
Because the CSRF lobby guard blocks all <code>POST</code>, <code>PUT</code>, and <code>DELETE</code> from outside the browser — including login, add, update, and delete. The <code>api/*</code> exception was set up in <a href="/artikel/laravel-request-validasi-api">Request &amp; Form Request: Menjaga Input API (#59)</a>; once per project and it covers all <code>api/</code> doors. If you see 419 today, almost certainly that exception is missing or <code>serve</code> was not restarted.</p>
<p><strong>If I later move to <code>routes/api.php</code>, is that exception still needed?</strong><br>
No. Routes in <code>routes/api.php</code> are not CSRF-guarded from the start, so that exception can be removed. We stay on <code>web.php</code> today so you do not need an extra route file — matching the path used since <a href="/artikel/laravel-routing-json-perpustakaan-api">Routing &amp; Jawaban JSON API Perpustakaan (#58)</a>.</p>
<p><strong>Which token do I copy?</strong><br>
Only the string in the <code>"token"</code> key in the login response. Long, no spaces. Then write in the header: <code>Authorization: Bearer </code> + that string.</p>
<p><strong>Which terminal for what?</strong><br>
Terminal 1: <code>serve</code>. Terminal 2: login, see number, update, delete. Editor: <code>update</code> &amp; <code>destroy</code> + routes. Browser: view list only.</p>
<p><strong>Why still XAMPP Shell / Laragon?</strong><br>
So PHP PATH matches when the project was created. CMD from Start Menu often answers “php is not recognized”.</p>
<p><strong>Can a deleted book come back?</strong><br>
With normal <code>delete()</code>: no. Laravel has “soft delete” for that — good material for learning after this path ends.</p>

<h2>Summary</h2>
<p><strong>#63 (this article)</strong> completes book-shelf CRUD: <code>PUT /api/buku/{id}</code> to fix data and <code>DELETE /api/buku/{id}</code> to remove it, both require a Sanctum card and return <code>404</code> when the number is missing. Everything builds on
<a href="/artikel/capstone-api-perpustakaan-laravel">Capstone: API Perpustakaan (#62)</a>,
<a href="/artikel/laravel-auth-api-dasar">Auth API Dasar: Login &amp; Kartu Anggota (#61)</a>, and
<a href="/artikel/laravel-request-validasi-api">Request &amp; Form Request: Menjaga Input API (#59)</a>.</p>
<p><strong>Where next?</strong><br>
The “Laravel from scratch” path ends here — you can now build a small, fenced API on your own. A good next step: redo <a href="/artikel/capstone-api-perpustakaan-laravel">Capstone: API Perpustakaan (#62)</a> without looking at examples, then continue to advanced Laravel topics (table relations, pagination, per-owner permissions, soft delete). To connect the API to real devices, see the <a href="/belajar/fullstack-iot">Full Stack IoT</a> path.</p>

<blockquote>
  <p><strong>Seri 4 progress:</strong> step <strong>#63 (this article)</strong> · <strong>8/8</strong> Laravel path — <strong>complete</strong> · prerequisite: <a href="/artikel/capstone-api-perpustakaan-laravel">Capstone: API Perpustakaan (#62)</a> LIVE.</p>
</blockquote>

HTML;

        return str_replace(
            [
                'Seri 4: Pemrograman Web Lanjut v2',
                'Capstone: API Perpustakaan (#62)',
                'Auth API Dasar: Login &amp; Kartu Anggota (#61)',
                'Request &amp; Form Request: Menjaga Input API (#59)',
                'Instal PHP, Composer &amp; Proyek Laravel (#56)',
                'Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)',
                'Routing &amp; Jawaban JSON API Perpustakaan (#58)',
            ],
            [
                'Seri 4: Advanced Web Programming v2',
                'Capstone: Library API (Read + Login + Add) (#62)',
                'Basic Auth API: Login & Member Card (#61)',
                'Request & Form Request: Guarding API Input (#59)',
                'Install PHP, Composer & Your First Laravel Project (#56)',
                'Folder Structure, <code>.env</code> & Artisan Laravel (#57)',
                'Routing & JSON Responses for the Library API (#58)',
            ],
            $html
        );
    }
}
