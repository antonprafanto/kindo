<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article67Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $webCat = Category::where('slug', 'web-development')->first()
            ?? Category::where('slug', 'programming')->first();

        if (! $admin || ! $webCat) {
            throw new \RuntimeException('User atau kategori web-development/programming tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'laravel-api-resource-json';

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
                'title'              => 'API Resource: Rapikan Bentuk JSON',
                'title_en'           => 'API Resource: Tidy JSON Response Shape',
                'excerpt'            => 'Seri 5 #67: setelah aturan izin, rapikan bentuk jawaban JSON pinjam dengan Resource — array PHP dulu, cuplikan JsonResource Laravel, field konsisten, sembunyikan anggota_id, ramah awam.',
                'excerpt_en'         => 'Seri 5 #67: after permission rules, tidy borrowing JSON with API Resource — plain PHP array first, Laravel JsonResource snippets, consistent fields, hide anggota_id, beginner-friendly.',
                'body'               => $this->body(),
                'body_en'            => $this->bodyEn(),
                'status'             => 'published',
                'is_featured'        => false,
                'seo_title'          => 'Bentuk Jawaban JSON Rapi — API Resource Laravel Peminjaman',
                'seo_title_en'       => 'Tidy JSON Response Shape — Laravel API Resource for Borrowing Records',
                'seo_description'    => 'Seri 5 #67: setelah policy izin, rapikan JSON pinjam dengan JsonResource — toArray, status_label, field konsisten, sembunyikan anggota_id, demo PHP & cuplikan Laravel.',
                'seo_description_en' => 'Seri 5 #67: after authorization policy, tidy borrowing JSON with JsonResource — toArray, status_label, consistent fields, hide anggota_id, PHP demo & Laravel snippets.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        // published_at setelah #66 supaya urutan "Terbaru" di /artikel tidak menjatuhkan #67 ke tengah daftar
        $prevPublished = Article::where('slug', 'laravel-policy-otorisasi-api')->value('published_at');
        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        } elseif ($prevPublished && $article->published_at <= $prevPublished) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['laravel', 'php', 'api', 'http', 'web', 'eloquent', 'database'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel ke-67 berhasil dipublish: '.$article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — slip pinjam yang rapi di JSON</h2>
<p>Artikel ini adalah <strong>#67 (ini)</strong> di <strong>Seri 5: Laravel Lanjutan</strong>. Setelah siapa boleh ubah catatan pinjam dikunci di <a href="/artikel/laravel-policy-otorisasi-api">Authorization Policy: Siapa Boleh Ubah (#66)</a>, pertanyaan berikutnya muncul: <strong>bagaimana bentuk jawaban JSON</strong> yang dikirim ke pemanggil — aplikasi atau alat yang memanggil API?</p>
<p>Tanpa bentuk yang konsisten, pemanggil menerima catatan acak: kadang ada <code>anggota_id</code> mentah, kadang tidak; status hanya kode <code>aktif</code> tanpa label manusiawi. Hari ini kita belajar <strong>API Resource</strong> (bungkus Laravel untuk merapikan JSON): pilih field yang perlu, sembunyikan kolom internal, tambahkan <code>status_label</code>, dan kirim slip pinjam yang rapi.</p>
<p><strong>Awam:</strong> bayangkan dua slip pinjam. Yang satu tulisannya rapi: judul buku, nama anggota, status jelas. Yang lain catatan acak di kertas kusut — isinya sama, tapi susah dibaca. Itu beda <strong>slip pinjam yang rapi</strong> vs <strong>catatan acak</strong>. Di Laravel, <em>JsonResource</em> membantu merapikan bentuk jawaban JSON.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> sudah selesai <a href="/artikel/laravel-policy-otorisasi-api">Authorization Policy: Siapa Boleh Ubah (#66)</a>, paham fondasi <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a> / <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a>. Pakai <strong>Laravel 13+</strong> — butuh <strong>PHP 8.3+</strong>.</p>
</blockquote>

<h2>Spesifikasi fitur — apa yang selesai hari ini?</h2>
<p>Tiga hal ini yang kita kejar:</p>
<ol>
  <li><strong>Field konsisten</strong> — setiap catatan pinjam punya field yang sama: <code>id</code>, <code>judul_buku</code>, <code>nama_anggota</code>, <code>status</code>, <code>status_label</code>.</li>
  <li><strong>Sembunyikan yang tidak perlu</strong> — kolom internal seperti <code>anggota_id</code> mentah tidak dikirim ke pemanggil.</li>
  <li><strong>Satu tempat merapikan</strong> — logika bentuk JSON tidak copy-paste di banyak <strong>pengatur kode</strong>; di Laravel dipindah ke kelas <code>PeminjamanResource</code>.</li>
</ol>
<p><strong>Awam:</strong> selesai artikel ini, kamu belum menulis uji otomatis. Kamu sedang merapikan <strong>bentuk jawaban JSON</strong> slip pinjam di proyek perpustakaan mini — konsisten, tanpa kolom internal bocor. Uji otomatis datang di artikel berikutnya tentang Feature Test.</p>

<h2>Istilah — ringkas untuk bentuk jawaban JSON</h2>
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
      <td><em>JsonResource</em> / API Resource</td>
      <td>Bungkus Laravel yang merapikan satu baris data jadi JSON</td>
      <td>Kelas dengan metode <code>toArray</code></td>
    </tr>
    <tr>
      <td><code>toArray</code></td>
      <td>Perintah &ldquo;ubah data jadi array siap JSON&rdquo;</td>
      <td>Satu fungsi, satu bentuk</td>
    </tr>
    <tr>
      <td><code>status_label</code></td>
      <td>Status dalam bahasa manusia</td>
      <td>Misalnya &ldquo;Sedang dipinjam&rdquo; / &ldquo;Sudah kembali&rdquo;</td>
    </tr>
    <tr>
      <td>Field konsisten</td>
      <td>Setiap baris punya nama field yang sama</td>
      <td>Pemanggil tidak bingung membaca</td>
    </tr>
    <tr>
      <td><code>collection</code></td>
      <td>Bungkus banyak baris sekaligus</td>
      <td><code>PeminjamanResource::collection(...)</code></td>
    </tr>
    <tr>
      <td>Hide <code>anggota_id</code></td>
      <td>Jangan kirim ID internal ke luar</td>
      <td>Pilih field di <code>toArray</code>, bukan kirim baris mentah</td>
    </tr>
  </tbody>
</table>
<p>Urutan belajar kita: <strong>array PHP dulu -&gt; rapikan manual dengan fungsi -&gt; baru bungkus Laravel JsonResource</strong>. Kalau loncat langsung ke Resource tanpa memahami field mana yang perlu, JSON sering masih berantakan.</p>

<h2>Persiapan — alat yang kamu buka</h2>
<p><strong>Alat yang dipakai di artikel ini</strong> (fondasi dari <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a> dan <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a> — tidak ada unduhan Composer baru hari ini):</p>
<ul>
  <li><strong>Explorer</strong> — cek folder proyek <code>perpustakaan-api</code>, lalu lihat <code>app\Http\Resources</code> dan <code>app\Http\Controllers</code> untuk bungkus JSON dan <strong>pengatur kode</strong>.</li>
  <li><strong>Terminal</strong> — Laragon: menu <em>Terminal</em> · XAMPP: tombol <em>Shell</em>. Hindari CMD/PowerShell dari Start Menu kalau PATH PHP-mu belum rapi.</li>
  <li><strong>Editor teks</strong> — Notepad / VS Code — untuk membuka Resource dan <strong>pengatur kode</strong>. Contoh: <code>notepad app\Http\Resources\PeminjamanResource.php</code> dan <code>notepad app\Http\Controllers\PeminjamanController.php</code>.</li>
  <li><strong>Browser</strong> — opsional. Inti uji hari ini ada di terminal; browser berguna kalau kamu sudah menjalankan <code>php artisan serve</code> dan ingin uji lewat alamat URL.</li>
</ul>
<p><strong>Awam:</strong> untuk artikel ini <strong>satu terminal sebenarnya cukup</strong> — jalankan <code>php laravel_api_resource_json_demo.php</code> di folder proyek. Kalau <code>php artisan serve</code> dari artikel sebelumnya masih hidup, pakai <strong>terminal kedua</strong> untuk demo PHP dan perintah <code>curl.exe</code> saat menguji bentuk JSON dari rute Laravel. Kalau butuh jendela kedua: Laragon — klik menu <em>Terminal</em> lagi · XAMPP — klik tombol <em>Shell</em> lagi, lalu <code>cd</code> ke folder proyek yang sama.</p>
<p>Buka terminal Laragon/Shell XAMPP, masuk ke folder proyek:</p>
<pre><code class="language-bash">cd C:\laragon\www\perpustakaan-api
</code></pre>
<p>Di XAMPP biasanya: <code>cd C:\xampp\htdocs\perpustakaan-api</code>. Sesuaikan kalau foldermu beda.</p>
<p><strong>Install-dari-nol:</strong> kalau <code>php</code> atau <code>composer</code> belum dikenali terminal, kembali dulu ke <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a>. Kalau struktur folder proyek masih membingungkan, ulangi <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a>.</p>

<h2>Kenapa PHP biasa dulu?</h2>
<p>Kalau langsung loncat ke kelas JsonResource di Laravel, pemula sering bingung: field mana yang perlu dikirim? Maka kita mulai dari array PHP biasa supaya perbedaan <strong>mentah vs rapi</strong> terlihat jelas sebelum dibungkus <code>toArray</code>.</p>

<pre><code class="language-php">&lt;?php
// Mini: kirim catatan pinjam mentah vs rapi.
$peminjamanMentah = [
    "id" =&gt; 10,
    "anggota_id" =&gt; 1,
    "judul_buku" =&gt; "Dasar PHP",
    "nama_anggota" =&gt; "Budi",
    "status" =&gt; "aktif",
    "created_at" =&gt; "2026-07-20 10:00:00",
];

$peminjamanRapi = [
    "id" =&gt; $peminjamanMentah["id"],
    "judul_buku" =&gt; $peminjamanMentah["judul_buku"],
    "nama_anggota" =&gt; $peminjamanMentah["nama_anggota"],
    "status" =&gt; $peminjamanMentah["status"],
    "status_label" =&gt; $peminjamanMentah["status"] === "aktif" ? "Sedang dipinjam" : "Sudah kembali",
];

echo json_encode(["mentah" =&gt; $peminjamanMentah, "rapi" =&gt; $peminjamanRapi], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL;
</code></pre>
<p><strong>Awam — cara menguji bagian ini:</strong> salin potongan di atas ke file misalnya <code>mentah-vs-rapi.php</code>, lalu di terminal Laragon/XAMPP jalankan <code>php mentah-vs-rapi.php</code>. Kalau muncul dua objek JSON (<code>mentah</code> vs <code>rapi</code>) dan yang rapi tanpa <code>anggota_id</code>, ide “sembunyikan yang tidak perlu” sudah terlihat. Versi <code>rapi</code> menambah <code>status_label</code> supaya status lebih manusiawi daripada kode <code>aktif</code> saja.</p>

<h2>Alur rapikan — array PHP dulu</h2>
<p>Gerakan yang benar selalu sama:</p>
<ol>
  <li><strong>Ambil data mentah</strong> — dari basis data atau array contoh.</li>
  <li><strong>Rapikan satu baris</strong> — pilih field, tambah <code>status_label</code>, sembunyikan <code>anggota_id</code>.</li>
  <li><strong>Terapkan ke daftar</strong> — fungsi yang sama dipakai ke setiap baris sebelum <code>json_encode</code>.</li>
  <li><strong>Kirim JSON</strong> — pemanggil membaca slip yang konsisten.</li>
</ol>

<pre><code class="language-php">&lt;?php
// Salin ke file misalnya rapikan-cek.php lalu jalankan: php rapikan-cek.php
$peminjaman = [
    ["id" =&gt; 10, "anggota_id" =&gt; 1, "judul_buku" =&gt; "Dasar PHP", "nama_anggota" =&gt; "Budi", "status" =&gt; "aktif"],
    ["id" =&gt; 11, "anggota_id" =&gt; 2, "judul_buku" =&gt; "Belajar Laravel", "nama_anggota" =&gt; "Siti", "status" =&gt; "kembali"],
];

function rapikanPeminjaman(array $row): array
{
    return [
        "id" =&gt; $row["id"],
        "judul_buku" =&gt; $row["judul_buku"],
        "nama_anggota" =&gt; $row["nama_anggota"],
        "status" =&gt; $row["status"],
        "status_label" =&gt; $row["status"] === "aktif" ? "Sedang dipinjam" : "Sudah kembali",
    ];
}

$hasil = array_map("rapikanPeminjaman", $peminjaman);
echo json_encode(["data" =&gt; $hasil], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL;
</code></pre>
<p><strong>Awam — cara menguji bagian ini:</strong> salin potongan di atas ke <code>rapikan-cek.php</code>, lalu di terminal jalankan <code>php rapikan-cek.php</code>. Kalau JSON muncul tanpa <code>anggota_id</code> dan ada <code>status_label</code>, rapikan sudah sehat. Bandingkan dengan baris mentah — field internal harus hilang.</p>

<figure role="img" aria-label="Diagram alur rapikan bentuk jawaban JSON pinjam" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" style="display:block;max-width:760px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif" viewBox="0 0 760 260">
  <defs>
    <marker id="laravel67resourceArrow" orient="auto" markerWidth="8" markerHeight="8" refX="7" refY="4" viewBox="0 0 8 8">
      <path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/>
    </marker>
  </defs>
  <rect width="760" height="260" fill="#F5F5F0"/>
  <text x="24" y="36" fill="#1a1a1a" font-size="16" font-weight="700">Baca pinjam -&gt; Rapikan bentuk -&gt; Resource -&gt; JSON ke pemanggil</text>
  <rect x="24" y="70" width="140" height="80" rx="8" fill="#ffffff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="94" y="105" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Baris DB</text>
  <text x="94" y="128" text-anchor="middle" fill="#1a1a1a" font-size="12">catatan acak</text>
  <line x1="164" y1="110" x2="214" y2="110" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel67resourceArrow)"/>
  <rect x="218" y="70" width="140" height="80" rx="8" fill="#1a1a1a" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="288" y="105" text-anchor="middle" fill="#ffffff" font-size="15" font-weight="700">Rapikan</text>
  <text x="288" y="128" text-anchor="middle" fill="#ffffff" font-size="12">pilih field</text>
  <line x1="358" y1="110" x2="408" y2="110" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel67resourceArrow)"/>
  <rect x="412" y="70" width="150" height="80" rx="8" fill="#ffffff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="487" y="105" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Resource</text>
  <text x="487" y="128" text-anchor="middle" fill="#1a1a1a" font-size="12">toArray</text>
  <line x1="562" y1="110" x2="612" y2="110" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel67resourceArrow)"/>
  <rect x="616" y="70" width="120" height="80" rx="8" fill="#ffffff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="676" y="105" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">JSON</text>
  <text x="676" y="128" text-anchor="middle" fill="#1a1a1a" font-size="12">slip rapi</text>
  <text x="24" y="190" fill="#1a1a1a" font-size="13">Slip rapi = field konsisten, status_label manusiawi, tanpa anggota_id mentah.</text>
  <text x="24" y="220" fill="#1a1a1a" font-size="13">Setelah izin jelas di Policy, kita merapikan apa yang pemanggil baca.</text>
</svg>
<figcaption>Setelah aturan izin di <a href="/artikel/laravel-policy-otorisasi-api">Authorization Policy: Siapa Boleh Ubah (#66)</a>, <strong>#67 (ini)</strong> merapikan bentuk jawaban JSON lewat Resource.</figcaption>
</figure>

<h2>Laravel — cuplikan JsonResource &amp; toArray (bukan file mandiri)</h2>
<p>Di proyek Laravel, bentuk jawaban ditulis di kelas Resource, lalu dipanggil dari <strong>pengatur kode</strong> sebelum dikirim ke pemanggil.</p>

<pre><code class="language-php">&lt;?php
// Cuplikan Laravel (bukan file mandiri)
// app/Http/Resources/PeminjamanResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PeminjamanResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" =&gt; $this-&gt;id,
            "judul_buku" =&gt; $this-&gt;buku-&gt;judul,
            "nama_anggota" =&gt; $this-&gt;anggota-&gt;nama,
            "status" =&gt; $this-&gt;status,
            "status_label" =&gt; $this-&gt;status === "aktif" ? "Sedang dipinjam" : "Sudah kembali",
        ];
    }
}
</code></pre>

<pre><code class="language-php">&lt;?php
// Cuplikan Laravel (bukan file mandiri)
// app/Http/Controllers/PeminjamanController.php

use App\Http\Resources\PeminjamanResource;
use App\Models\Peminjaman;

public function show(Peminjaman $peminjaman)
{
    return new PeminjamanResource($peminjaman);
}

public function index()
{
    return PeminjamanResource::collection(Peminjaman::paginate(10));
}
</code></pre>
<p><strong>Awam:</strong> <code>PeminjamanResource::toArray</code> = aturan &ldquo;field apa saja yang dikirim&rdquo; — <code>anggota_id</code> sengaja tidak ada. <code>new PeminjamanResource($peminjaman)</code> = bungkus satu baris jadi slip rapi. <code>::collection</code> = bungkus banyak baris sekaligus — cocok dengan daftar panjang. Cuplikan ini <strong>bukan file mandiri</strong> — tempel ke proyek kalau rute pinjam sudah ada.</p>
<p>Kalau <code>php artisan serve</code> sudah jalan di terminal pertama, uji bentuk JSON di terminal kedua. Di Windows ketik <code>curl.exe</code> (bukan alias <code>curl</code> saja) supaya PowerShell tidak bingung:</p>
<pre><code class="language-bash">curl.exe "http://127.0.0.1:8000/api/peminjaman/10"
curl.exe "http://127.0.0.1:8000/api/peminjaman"
</code></pre>
<p><strong>Awam:</strong> respons JSON dari <code>curl.exe</code> adalah cara cepat melihat apakah field konsisten — ada <code>status_label</code>, tidak ada <code>anggota_id</code> mentah. Kalau muncul <code>404</code>, rute pinjam mungkin belum dipasang — itu wajar; fokus dulu ke demo PHP di atas. Kalau bentuk beda-beda tiap halaman, rapikan belum terpusat di satu Resource.</p>

<h2>Pola Dasar — bentuk jawaban JSON yang rapi</h2>
<figure style="margin:1.5rem 0;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1.25rem;color:#1a1a1a" aria-label="Enam langkah rapikan bentuk jawaban JSON">
<ol style="list-style:none;padding:0;margin:0;color:#1a1a1a">
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div style="color:#1a1a1a"><strong style="color:#1a1a1a">Ambil data mentah</strong><br><span style="color:#1a1a1a">Dari basis data atau array — fondasi dari langkah sebelumnya.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div style="color:#1a1a1a"><strong style="color:#1a1a1a">Pilih field yang perlu</strong><br><span style="color:#1a1a1a">Sembunyikan kolom internal — hide <code>anggota_id</code> dari JSON publik.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div><strong style="color:#1a1a1a">Tambah label manusiawi</strong><br><span style="color:#1a1a1a"><code>status_label</code> lebih awam daripada kode <code>aktif</code> saja.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div><strong style="color:#1a1a1a">Satu fungsi rapikan</strong><br><span style="color:#1a1a1a">PHP <code>rapikanPeminjaman</code> dulu — jangan copy-paste di banyak tempat.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">5</span>
    <div style="color:#1a1a1a"><strong style="color:#1a1a1a">Pindah ke Resource</strong><br><span style="color:#1a1a1a">Tulis <code>toArray</code> di <code>PeminjamanResource</code>; panggil dari <strong>pengatur kode</strong>.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">6</span>
    <div><strong style="color:#1a1a1a">Uji bentuk konsisten</strong><br><span style="color:#1a1a1a">Satu baris · banyak baris · field tersembunyi benar-benar hilang — pakai <code>curl.exe</code> kalau perlu.</span></div>
  </li>
</ol>
</figure>

<h2>Kode lengkap — demo mandiri</h2>
<p>Simpan sebagai <code>laravel_api_resource_json_demo.php</code>, lalu jalankan <code>php laravel_api_resource_json_demo.php</code>:</p>

<pre><code class="language-php">&lt;?php
declare(strict_types=1);

$peminjaman = [
    ["id" =&gt; 10, "anggota_id" =&gt; 1, "judul_buku" =&gt; "Dasar PHP", "nama_anggota" =&gt; "Budi", "status" =&gt; "aktif"],
    ["id" =&gt; 11, "anggota_id" =&gt; 2, "judul_buku" =&gt; "Belajar Laravel", "nama_anggota" =&gt; "Siti", "status" =&gt; "kembali"],
];

function rapikanPeminjaman(array $row): array
{
    return [
        "id" =&gt; $row["id"],
        "judul_buku" =&gt; $row["judul_buku"],
        "nama_anggota" =&gt; $row["nama_anggota"],
        "status" =&gt; $row["status"],
        "status_label" =&gt; $row["status"] === "aktif" ? "Sedang dipinjam" : "Sudah kembali",
    ];
}

function demo(string $judul, callable $aksi): void
{
    echo "=== {$judul} ===", PHP_EOL;
    $hasil = $aksi();
    echo json_encode($hasil, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL, PHP_EOL;
}

demo("Satu baris rapi", function () use ($peminjaman) {
    return rapikanPeminjaman($peminjaman[0]);
});

demo("Daftar rapi", function () use ($peminjaman) {
    return ["data" =&gt; array_map("rapikanPeminjaman", $peminjaman)];
});

demo("Tanpa anggota_id", function () use ($peminjaman) {
    $rapi = rapikanPeminjaman($peminjaman[0]);
    return ["punya_anggota_id" =&gt; array_key_exists("anggota_id", $rapi)];
});
</code></pre>
<p><strong>Awam:</strong> tiga skenario di atas menunjukkan pola yang wajar: satu baris rapi, daftar rapi, dan field internal benar-benar hilang. Fungsi <code>rapikanPeminjaman</code> adalah inti logika; <code>demo(...)</code> hanya membungkus output agar mudah dibaca di terminal.</p>

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
      <td>JSON beda-beda tiap halaman</td>
      <td>Copy-paste rapikan di banyak tempat</td>
      <td>Satu fungsi atau satu <code>PeminjamanResource</code></td>
    </tr>
    <tr>
      <td>Kolom internal bocor ke pemanggil</td>
      <td>Kirim baris mentah dari basis data</td>
      <td>Pilih field di <code>toArray</code> — hide <code>anggota_id</code></td>
    </tr>
    <tr>
      <td>Status membingungkan</td>
      <td>Hanya kode <code>aktif</code> tanpa label</td>
      <td>Tambah <code>status_label</code> manusiawi</td>
    </tr>
    <tr>
      <td>Relasi tidak ikut terbaca</td>
      <td>Lupa ambil <code>judul_buku</code> dari relasi</td>
      <td>Muat relasi dulu di model <code>Peminjaman</code></td>
    </tr>
    <tr>
      <td>Field hilang di daftar panjang</td>
      <td>Rapikan hanya di satu aksi, bukan di <code>collection</code></td>
      <td>Pakai <code>PeminjamanResource::collection(...)</code> untuk banyak baris</td>
    </tr>
    <tr>
      <td><code>curl</code> aneh atau error di PowerShell</td>
      <td>Alias <code>curl</code> di PowerShell bukan <code>curl.exe</code></td>
      <td>Ketik <code>curl.exe</code> persis seperti contoh, atau uji lewat browser</td>
    </tr>
  </tbody>
</table>

<h2>Latihan singkat</h2>
<ol>
  <li>Ubah demo: tambah field <code>dipinjam_sejak</code> di versi rapi dan pastikan <code>anggota_id</code> tetap tidak ikut.</li>
  <li>Jelaskan ke teman: beda slip rapi vs catatan acak — pakai analogi perpustakaan mini.</li>
  <li>Tulis satu kalimat: kenapa <code>PeminjamanResource</code> lebih rapi daripada copy-paste <code>rapikanPeminjaman</code> di banyak <strong>pengatur kode</strong>.</li>
</ol>

<h2>FAQ singkat</h2>
<p><strong>Apakah Resource menggantikan aturan izin?</strong><br>
Tidak. Aturan izin dari <a href="/artikel/laravel-policy-otorisasi-api">Authorization Policy: Siapa Boleh Ubah (#66)</a> menjawab &ldquo;boleh atau tidak&rdquo;. Resource menjawab &ldquo;bentuk jawaban seperti apa&rdquo;.</p>
<p><strong>Haruskah selalu pakai kelas Resource?</strong><br>
Untuk belajar, fungsi PHP <code>rapikanPeminjaman</code> di <code>rapikan-cek.php</code> sudah cukup memahami ide. Di proyek Laravel nyata, Resource membantu merapikan saat field bertambah.</p>
<p><strong>Tool apa yang dibuka dulu?</strong><br>
Explorer untuk memastikan folder proyek benar (Resources + Controllers), satu terminal untuk demo PHP, editor untuk <strong>pengatur kode</strong>. Kalau <code>serve</code> hidup, terminal kedua untuk <code>curl.exe</code>.</p>
<p><strong>Potongan sintaks diuji di mana?</strong><br>
Langkah tengah (rapikan array) salin ke <code>rapikan-cek.php</code>, lalu jalankan <code>php rapikan-cek.php</code>. Demo lengkap diuji dengan <code>php laravel_api_resource_json_demo.php</code>. Cuplikan Laravel ditempel ke <code>app\Http\Resources\PeminjamanResource.php</code> dan <code>app\Http\Controllers\PeminjamanController.php</code>; kalau rute sudah ada, uji bentuk JSON dengan <code>curl.exe</code> di terminal kedua.</p>
<p><strong>Ke mana setelah ini?</strong><br>
Berikutnya alami: <strong>Feature Test</strong> — uji otomatis bahwa bentuk jawaban JSON tetap benar.</p>

<h2>Kesimpulan</h2>
<p>Kamu sudah merapikan bentuk jawaban JSON: <strong>array PHP dulu</strong> dengan fungsi <code>rapikanPeminjaman</code>, lalu pindahkan ke <strong>API Resource</strong> (<code>PeminjamanResource</code>) dan <code>toArray</code> di Laravel. Pemanggil menerima slip pinjam yang konsisten — field sama, <code>status_label</code> manusiawi, tanpa <code>anggota_id</code> mentah.</p>
<blockquote>
  <p><strong>Seri 5 progress:</strong> langkah <strong>#67 (ini)</strong> · <strong>4/7</strong> Laravel Lanjutan · prasyarat: <a href="/artikel/laravel-policy-otorisasi-api">Authorization Policy: Siapa Boleh Ubah (#66)</a> LIVE. Berikutnya: <strong>Feature Test</strong>.</p>
</blockquote>
HTML;
    }

    private function bodyEn(): string
    {
        $html = <<<'HTML'
<h2>Introduction — a tidy borrowing slip in JSON</h2>
<p>This article is <strong>#67 (this article)</strong> in <strong>Seri 5: Laravel Lanjutan</strong>. After who may update borrowing records was locked in <a href="/artikel/laravel-policy-otorisasi-api">Authorization Policy: Siapa Boleh Ubah (#66)</a>, the next question appears: <strong>what should the JSON response shape look like</strong> for the API <strong>caller</strong> — the app or tool <strong>that calls the API</strong>?</p>
<p>Without a consistent shape, callers receive messy records: sometimes raw <code>anggota_id</code>, sometimes not; status as only the code <code>aktif</code> without a human label. Today we learn <strong>API Resource</strong> (Laravel&rsquo;s wrapper to tidy JSON): pick the fields you need, hide internal columns, add <code>status_label</code>, and send a neat borrowing slip.</p>
<p><strong>Beginner:</strong> imagine two borrowing slips. One is neat: book title, member name, clear status. The other is a crumpled note — same data, hard to read. That is the difference between a <strong>tidy borrowing slip</strong> and a <strong>messy note</strong>. In Laravel, <em>JsonResource</em> helps tidy the JSON response shape.</p>

<blockquote>
  <p><strong>Prerequisite:</strong> finish <a href="/artikel/laravel-policy-otorisasi-api">Authorization Policy: Siapa Boleh Ubah (#66)</a>, and keep the foundations from <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a> / <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a>. Use <strong>Laravel 13+</strong> and <strong>PHP 8.3+</strong>.</p>
</blockquote>

<h2>Feature spec — what gets finished today?</h2>
<p>These are the three targets:</p>
<ol>
  <li><strong>Consistent fields</strong> — every borrowing record has the same fields: <code>id</code>, <code>judul_buku</code>, <code>nama_anggota</code>, <code>status</code>, <code>status_label</code>.</li>
  <li><strong>Hide what is not needed</strong> — internal columns such as raw <code>anggota_id</code> are not sent to the caller.</li>
  <li><strong>One place to tidy</strong> — JSON shape logic is not copy-pasted across many <strong>code organizers</strong>; in Laravel it moves into <code>PeminjamanResource</code>.</li>
</ol>
<p><strong>Beginner:</strong> after this article, you are not writing automated tests yet. You are tidying the <strong>JSON response shape</strong> for borrowing slips in the mini-library project — consistent, without internal columns leaking out. Automated tests come in the next article about Feature Test.</p>

<h2>Terms — a quick glossary for JSON response shape</h2>
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
      <td><em>JsonResource</em> / API Resource</td>
      <td>Laravel wrapper that tidies one row into JSON</td>
      <td>Class with a <code>toArray</code> method</td>
    </tr>
    <tr>
      <td><code>toArray</code></td>
      <td>Command to &ldquo;turn data into a JSON-ready array&rdquo;</td>
      <td>One function, one shape</td>
    </tr>
    <tr>
      <td><code>status_label</code></td>
      <td>Status in human language</td>
      <td>For example &ldquo;Sedang dipinjam&rdquo; / &ldquo;Sudah kembali&rdquo;</td>
    </tr>
    <tr>
      <td>Consistent fields</td>
      <td>Every row uses the same field names</td>
      <td>Callers do not get confused reading responses</td>
    </tr>
    <tr>
      <td><code>collection</code></td>
      <td>Wrap many rows at once</td>
      <td><code>PeminjamanResource::collection(...)</code></td>
    </tr>
    <tr>
      <td>Hide <code>anggota_id</code></td>
      <td>Do not send internal IDs outward</td>
      <td>Pick fields in <code>toArray</code>, do not send raw rows</td>
    </tr>
  </tbody>
</table>
<p>Our learning order: <strong>plain PHP array first -&gt; tidy manually with a function -&gt; then wrap in Laravel JsonResource</strong>. If you jump straight to Resource without understanding which fields matter, JSON often stays messy.</p>

<h2>Preparation — tools to open</h2>
<p><strong>Tools used in this article</strong> (built on <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a> and <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a> — there is <strong>no new Composer download</strong> today):</p>
<ul>
  <li><strong>Explorer</strong> — check the <code>perpustakaan-api</code> project folder, then look at <code>app\Http\Resources</code> and <code>app\Http\Controllers</code> for JSON wrappers and the <strong>code organizer</strong>.</li>
  <li><strong>Terminal</strong> — Laragon: <em>Terminal</em> menu · XAMPP: <em>Shell</em> button. Avoid Start Menu CMD/PowerShell if your PHP PATH is still messy.</li>
  <li><strong>Text editor</strong> — Notepad / VS Code — to open Resource and <strong>code organizer</strong> files. Example: <code>notepad app\Http\Resources\PeminjamanResource.php</code> and <code>notepad app\Http\Controllers\PeminjamanController.php</code>.</li>
  <li><strong>Browser</strong> — optional. The core test today is in the terminal; the browser helps if you already run <code>php artisan serve</code> and want to test through a URL.</li>
</ul>
<p><strong>Beginner:</strong> for this article, <strong>one terminal is actually enough</strong> — run <code>php laravel_api_resource_json_demo.php</code> in the project folder. If <code>php artisan serve</code> from the previous article is still alive, use a <strong>second terminal</strong> for the PHP demo and <code>curl.exe</code> when testing JSON shape from the Laravel route. To open a second window: Laragon — click the <em>Terminal</em> menu again · XAMPP — click the <em>Shell</em> button again, then <code>cd</code> to the same project folder.</p>
<p>Open Laragon Terminal / XAMPP Shell, then move into the project folder:</p>
<pre><code class="language-bash">cd C:\laragon\www\perpustakaan-api
</code></pre>
<p>In XAMPP it is usually: <code>cd C:\xampp\htdocs\perpustakaan-api</code>. Adjust the path if your folder is different.</p>
<p><strong>Install-from-scratch:</strong> if <code>php</code> or <code>composer</code> is not recognized in the terminal, return to <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a>. If your project folder structure is still confusing, review <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a> first.</p>

<h2>Why start with plain PHP first?</h2>
<p>If you jump straight into a Laravel JsonResource class, beginners often wonder: which fields should I send? So we start from a plain PHP array so the difference between <strong>raw vs tidy</strong> is visible before wrapping it in <code>toArray</code>.</p>

<pre><code class="language-php">&lt;?php
// Mini: send raw vs tidy borrowing record.
$peminjamanMentah = [
    "id" =&gt; 10,
    "anggota_id" =&gt; 1,
    "judul_buku" =&gt; "Dasar PHP",
    "nama_anggota" =&gt; "Budi",
    "status" =&gt; "aktif",
    "created_at" =&gt; "2026-07-20 10:00:00",
];

$peminjamanRapi = [
    "id" =&gt; $peminjamanMentah["id"],
    "judul_buku" =&gt; $peminjamanMentah["judul_buku"],
    "nama_anggota" =&gt; $peminjamanMentah["nama_anggota"],
    "status" =&gt; $peminjamanMentah["status"],
    "status_label" =&gt; $peminjamanMentah["status"] === "aktif" ? "Sedang dipinjam" : "Sudah kembali",
];

echo json_encode(["mentah" =&gt; $peminjamanMentah, "rapi" =&gt; $peminjamanRapi], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL;
</code></pre>
<p><strong>Beginner — how to test this part:</strong> copy the snippet above into a file such as <code>mentah-vs-rapi.php</code>, then in Laragon/XAMPP terminal run <code>php mentah-vs-rapi.php</code>. If you see two JSON objects (<code>mentah</code> vs <code>rapi</code>) and the tidy one has no <code>anggota_id</code>, the idea “hide what is not needed” is already visible. The <code>rapi</code> version adds <code>status_label</code> so status feels more human than the code <code>aktif</code> alone.</p>

<h2>Tidy flow — plain PHP array first</h2>
<p>The correct move order is always the same:</p>
<ol>
  <li><strong>Fetch raw data</strong> — from the database or a sample array.</li>
  <li><strong>Tidy one row</strong> — pick fields, add <code>status_label</code>, hide <code>anggota_id</code>.</li>
  <li><strong>Apply to a list</strong> — the same function is used on every row before <code>json_encode</code>.</li>
  <li><strong>Send JSON</strong> — the caller reads a consistent slip.</li>
</ol>

<pre><code class="language-php">&lt;?php
// Copy into a file such as rapikan-cek.php, then run: php rapikan-cek.php
$peminjaman = [
    ["id" =&gt; 10, "anggota_id" =&gt; 1, "judul_buku" =&gt; "Dasar PHP", "nama_anggota" =&gt; "Budi", "status" =&gt; "aktif"],
    ["id" =&gt; 11, "anggota_id" =&gt; 2, "judul_buku" =&gt; "Belajar Laravel", "nama_anggota" =&gt; "Siti", "status" =&gt; "kembali"],
];

function rapikanPeminjaman(array $row): array
{
    return [
        "id" =&gt; $row["id"],
        "judul_buku" =&gt; $row["judul_buku"],
        "nama_anggota" =&gt; $row["nama_anggota"],
        "status" =&gt; $row["status"],
        "status_label" =&gt; $row["status"] === "aktif" ? "Sedang dipinjam" : "Sudah kembali",
    ];
}

$hasil = array_map("rapikanPeminjaman", $peminjaman);
echo json_encode(["data" =&gt; $hasil], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL;
</code></pre>
<p><strong>Beginner — how to test this part:</strong> copy the snippet above into <code>rapikan-cek.php</code>, then run <code>php rapikan-cek.php</code> in the terminal. If JSON appears without <code>anggota_id</code> and with <code>status_label</code>, tidying is healthy. Compare with the raw row — internal fields must be gone.</p>

<figure role="img" aria-label="Diagram tidy JSON response flow for borrowing records" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" style="display:block;max-width:760px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif" viewBox="0 0 760 260">
  <defs>
    <marker id="laravel67resourceArrow" orient="auto" markerWidth="8" markerHeight="8" refX="7" refY="4" viewBox="0 0 8 8">
      <path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/>
    </marker>
  </defs>
  <rect width="760" height="260" fill="#F5F5F0"/>
  <text x="24" y="36" fill="#1a1a1a" font-size="16" font-weight="700">Read borrow -&gt; Tidy shape -&gt; Resource -&gt; JSON to caller</text>
  <rect x="24" y="70" width="140" height="80" rx="8" fill="#ffffff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="94" y="105" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">DB row</text>
  <text x="94" y="128" text-anchor="middle" fill="#1a1a1a" font-size="12">messy note</text>
  <line x1="164" y1="110" x2="214" y2="110" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel67resourceArrow)"/>
  <rect x="218" y="70" width="140" height="80" rx="8" fill="#1a1a1a" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="288" y="105" text-anchor="middle" fill="#ffffff" font-size="15" font-weight="700">Tidy</text>
  <text x="288" y="128" text-anchor="middle" fill="#ffffff" font-size="12">pick fields</text>
  <line x1="358" y1="110" x2="408" y2="110" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel67resourceArrow)"/>
  <rect x="412" y="70" width="150" height="80" rx="8" fill="#ffffff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="487" y="105" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Resource</text>
  <text x="487" y="128" text-anchor="middle" fill="#1a1a1a" font-size="12">toArray</text>
  <line x1="562" y1="110" x2="612" y2="110" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel67resourceArrow)"/>
  <rect x="616" y="70" width="120" height="80" rx="8" fill="#ffffff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="676" y="105" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">JSON</text>
  <text x="676" y="128" text-anchor="middle" fill="#1a1a1a" font-size="12">tidy slip</text>
  <text x="24" y="190" fill="#1a1a1a" font-size="13">Tidy slip = consistent fields, human status_label, no raw anggota_id.</text>
  <text x="24" y="220" fill="#1a1a1a" font-size="13">After permission rules in Policy, we tidy what the caller reads.</text>
</svg>
<figcaption>After permission rules in <a href="/artikel/laravel-policy-otorisasi-api">Authorization Policy: Siapa Boleh Ubah (#66)</a>, <strong>#67 (this article)</strong> tidies the JSON response shape through Resource.</figcaption>
</figure>

<h2>Laravel JsonResource snippets (not standalone files)</h2>
<p>In the Laravel project, response shape is written in a Resource class, then called from the <strong>code organizer</strong> before sending to the caller.</p>

<pre><code class="language-php">&lt;?php
// Cuplikan Laravel (bukan file mandiri)
// app/Http/Resources/PeminjamanResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PeminjamanResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" =&gt; $this-&gt;id,
            "judul_buku" =&gt; $this-&gt;buku-&gt;judul,
            "nama_anggota" =&gt; $this-&gt;anggota-&gt;nama,
            "status" =&gt; $this-&gt;status,
            "status_label" =&gt; $this-&gt;status === "aktif" ? "Sedang dipinjam" : "Sudah kembali",
        ];
    }
}
</code></pre>

<pre><code class="language-php">&lt;?php
// Cuplikan Laravel (bukan file mandiri)
// app/Http/Controllers/PeminjamanController.php

use App\Http\Resources\PeminjamanResource;
use App\Models\Peminjaman;

public function show(Peminjaman $peminjaman)
{
    return new PeminjamanResource($peminjaman);
}

public function index()
{
    return PeminjamanResource::collection(Peminjaman::paginate(10));
}
</code></pre>
<p><strong>Beginner:</strong> <code>PeminjamanResource::toArray</code> = rule for &ldquo;which fields to send&rdquo; — <code>anggota_id</code> is intentionally absent. <code>new PeminjamanResource($peminjaman)</code> = wrap one row into a tidy slip. <code>::collection</code> = wrap many rows at once — good for long lists. This snippet is <strong>not a standalone file</strong> — paste it into the project when the borrowing route exists.</p>
<p>If <code>php artisan serve</code> is already running in the first terminal, test JSON shape in the second terminal. On Windows type <code>curl.exe</code> (not the <code>curl</code> alias alone) so PowerShell does not get confused:</p>
<pre><code class="language-bash">curl.exe "http://127.0.0.1:8000/api/peminjaman/10"
curl.exe "http://127.0.0.1:8000/api/peminjaman"
</code></pre>
<p><strong>Beginner:</strong> the JSON response from <code>curl.exe</code> is a fast way to see whether fields are consistent — <code>status_label</code> is present, raw <code>anggota_id</code> is not. If you get <code>404</code>, the borrowing route may not be installed yet — that is normal; focus on the PHP demo above first. If the shape differs page by page, tidying is not centralized in one Resource yet.</p>

<h2>Basic Pattern — a tidy JSON response shape</h2>
<figure style="margin:1.5rem 0;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1.25rem;color:#1a1a1a" aria-label="Six steps to tidy JSON response shape">
<ol style="list-style:none;padding:0;margin:0;color:#1a1a1a">
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div style="color:#1a1a1a"><strong style="color:#1a1a1a">Fetch raw data</strong><br><span style="color:#1a1a1a">From the database or array — foundation from earlier steps.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div><strong style="color:#1a1a1a">Pick needed fields</strong><br><span style="color:#1a1a1a">Hide internal columns — hide <code>anggota_id</code> from public JSON.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div><strong style="color:#1a1a1a">Add a human label</strong><br><span style="color:#1a1a1a"><code>status_label</code> is more beginner-friendly than the code <code>aktif</code> alone.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div><strong style="color:#1a1a1a">One tidy function</strong><br><span style="color:#1a1a1a">Plain PHP <code>rapikanPeminjaman</code> first — do not copy-paste in many places.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">5</span>
    <div><strong style="color:#1a1a1a">Move to Resource</strong><br><span style="color:#1a1a1a">Write <code>toArray</code> in <code>PeminjamanResource</code>; call it from the <strong>code organizer</strong>.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">6</span>
    <div><strong style="color:#1a1a1a">Test consistent shape</strong><br><span style="color:#1a1a1a">One row · many rows · hidden fields truly gone — use <code>curl.exe</code> if needed.</span></div>
  </li>
</ol>
</figure>

<h2>Full code — self-run demo</h2>
<p>Save it as <code>laravel_api_resource_json_demo.php</code>, then run <code>php laravel_api_resource_json_demo.php</code>:</p>

<pre><code class="language-php">&lt;?php
declare(strict_types=1);

$peminjaman = [
    ["id" =&gt; 10, "anggota_id" =&gt; 1, "judul_buku" =&gt; "Dasar PHP", "nama_anggota" =&gt; "Budi", "status" =&gt; "aktif"],
    ["id" =&gt; 11, "anggota_id" =&gt; 2, "judul_buku" =&gt; "Belajar Laravel", "nama_anggota" =&gt; "Siti", "status" =&gt; "kembali"],
];

function rapikanPeminjaman(array $row): array
{
    return [
        "id" =&gt; $row["id"],
        "judul_buku" =&gt; $row["judul_buku"],
        "nama_anggota" =&gt; $row["nama_anggota"],
        "status" =&gt; $row["status"],
        "status_label" =&gt; $row["status"] === "aktif" ? "Sedang dipinjam" : "Sudah kembali",
    ];
}

function demo(string $judul, callable $aksi): void
{
    echo "=== {$judul} ===", PHP_EOL;
    $hasil = $aksi();
    echo json_encode($hasil, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL, PHP_EOL;
}

demo("One tidy row", function () use ($peminjaman) {
    return rapikanPeminjaman($peminjaman[0]);
});

demo("Tidy list", function () use ($peminjaman) {
    return ["data" =&gt; array_map("rapikanPeminjaman", $peminjaman)];
});

demo("Without anggota_id", function () use ($peminjaman) {
    $rapi = rapikanPeminjaman($peminjaman[0]);
    return ["punya_anggota_id" =&gt; array_key_exists("anggota_id", $rapi)];
});
</code></pre>
<p><strong>Beginner:</strong> the three scenarios above show a sensible pattern: one tidy row, a tidy list, and internal fields truly gone. Function <code>rapikanPeminjaman</code> is the core logic; <code>demo(...)</code> only wraps output so the terminal result is easy to read.</p>

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
      <td>JSON differs on every page</td>
      <td>Copy-paste tidying in many places</td>
      <td>One function or one <code>PeminjamanResource</code></td>
    </tr>
    <tr>
      <td>Internal columns leak to the caller</td>
      <td>Send raw database rows</td>
      <td>Pick fields in <code>toArray</code> — hide <code>anggota_id</code></td>
    </tr>
    <tr>
      <td>Confusing status</td>
      <td>Only the code <code>aktif</code> without a label</td>
      <td>Add a human <code>status_label</code></td>
    </tr>
    <tr>
      <td>Relations not readable</td>
      <td>Forgot to pull <code>judul_buku</code> from a relation</td>
      <td>Eager-load relations on the <code>Peminjaman</code> model first</td>
    </tr>
    <tr>
      <td>Fields missing in long lists</td>
      <td>Tidying only in one action, not in <code>collection</code></td>
      <td>Use <code>PeminjamanResource::collection(...)</code> for many rows</td>
    </tr>
    <tr>
      <td><code>curl</code> acts weird or errors in PowerShell</td>
      <td>PowerShell <code>curl</code> alias is not <code>curl.exe</code></td>
      <td>Type <code>curl.exe</code> exactly as shown, or test through the browser</td>
    </tr>
  </tbody>
</table>

<h2>Short practice</h2>
<ol>
  <li>Change the demo: add a <code>dipinjam_sejak</code> field in the tidy version and make sure <code>anggota_id</code> still does not appear.</li>
  <li>Explain to a friend: difference between a tidy slip and a messy note — use the mini-library analogy.</li>
  <li>Write one sentence: why <code>PeminjamanResource</code> is neater than copy-pasting <code>rapikanPeminjaman</code> across many <strong>code organizers</strong>.</li>
</ol>

<h2>FAQ</h2>
<p><strong>Does Resource replace permission rules?</strong><br>
No. Permission rules from <a href="/artikel/laravel-policy-otorisasi-api">Authorization Policy: Siapa Boleh Ubah (#66)</a> answer &ldquo;allowed or not&rdquo;. Resource answers &ldquo;what should the response shape look like&rdquo;.</p>
<p><strong>Must I always use a Resource class?</strong><br>
For learning, plain PHP <code>rapikanPeminjaman</code> in <code>rapikan-cek.php</code> is enough to understand the idea. In a real Laravel project, Resource helps tidy rules as fields grow.</p>
<p><strong>Which tools should I open first?</strong><br>
Explorer to confirm the project folder (Resources + Controllers), one terminal for the PHP demo, editor for the <strong>code organizer</strong>. If <code>serve</code> is alive, a second terminal for <code>curl.exe</code>.</p>
<p><strong>Where should I test the snippets?</strong><br>
The middle step (tidy array) is copied into <code>rapikan-cek.php</code>, then run with <code>php rapikan-cek.php</code>. The full demo is tested with <code>php laravel_api_resource_json_demo.php</code>. Laravel snippets are pasted into <code>app\Http\Resources\PeminjamanResource.php</code> and <code>app\Http\Controllers\PeminjamanController.php</code>; when the route exists, test JSON shape with <code>curl.exe</code> in a second terminal.</p>
<p><strong>Where next?</strong><br>
The natural next step is <strong>Feature Test</strong> — automated tests that the JSON response shape stays correct.</p>

<h2>Conclusion</h2>
<p>You tidied the JSON response shape: <strong>plain PHP array first</strong> with <code>rapikanPeminjaman</code>, then move into <strong>API Resource</strong> (<code>PeminjamanResource</code>) and <code>toArray</code> in Laravel. Callers receive a consistent borrowing slip — same fields, human <code>status_label</code>, without raw <code>anggota_id</code>.</p>
<blockquote>
  <p><strong>Seri 5 progress:</strong> step <strong>#67 (this article)</strong> · <strong>4/7</strong> Laravel Lanjutan · prerequisite: <a href="/artikel/laravel-policy-otorisasi-api">Authorization Policy: Siapa Boleh Ubah (#66)</a> LIVE. Next: <strong>Feature Test</strong>.</p>
</blockquote>
HTML;

        return str_replace(
            [
                'Seri 5: Laravel Lanjutan',
                'Authorization Policy: Siapa Boleh Ubah (#66)',
                'Instal PHP, Composer &amp; Proyek Laravel (#56)',
                'Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)',
                'Feature Test',
                'Laravel Lanjutan',
            ],
            [
                'Seri 5: Advanced Laravel',
                'Authorization Policy: Who May Update (#66)',
                'Install PHP, Composer &amp; Your First Laravel Project (#56)',
                'Folder Structure, <code>.env</code> &amp; Artisan Laravel (#57)',
                'Feature Test',
                'Advanced Laravel',
            ],
            $html
        );
    }
}
