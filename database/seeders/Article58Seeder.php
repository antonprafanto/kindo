<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article58Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $webCat = Category::where('slug', 'web-development')->first()
            ?? Category::where('slug', 'programming')->first();

        if (! $admin || ! $webCat) {
            throw new \RuntimeException('User atau kategori web-development/programming tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'laravel-routing-json-perpustakaan-api';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        // Slug baru v2 — tidak menyentuh cover_image

        foreach ([
            'laravel' => 'laravel',
            'php' => 'php',
            'api' => 'api',
            'http' => 'http',
            'web' => 'web',
            'json' => 'json',
            'rest' => 'rest',
        ] as $tagSlug => $tagName) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagName]);
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $webCat->id,
                'title'           => 'Routing & Jawaban JSON API Perpustakaan',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'Routing & Jawaban JSON Laravel untuk Pemula',
                'seo_description' => 'Seri 4 #58: buka pintu HTTP di Laravel, buat route daftar buku, dan jawab JSON untuk API perpustakaan mini — ramah awam.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['laravel', 'php', 'api', 'http', 'web', 'json', 'rest'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel ke-58 berhasil dipublish: '.$article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — buka pintu toko</h2>
<p>Artikel ini adalah <strong>#58 (ini)</strong> di <strong>Seri 4: Pemrograman Web Lanjut v2</strong>. Di <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a> kamu sudah kenal denah, pengaturan, dan Artisan. Sekarang langkah <strong>3/8</strong>: buka <strong>pintu HTTP</strong> (routing) dan jawab dengan <strong>JSON</strong> — bahasa paket data yang sering dipakai API.</p>
<p><strong>Awam:</strong> toko sudah punya denah. Pengunjung butuh <strong>alamat pintu</strong> yang jelas: “daftar buku ada di sini”. Routing = peta alamat. JSON = isi paket yang dikirim balik ke browser/aplikasi — bukan halaman HTML berwarna.</p>
<p>Domain tetap <strong>perpustakaan mini</strong>. Hari ini kita buat daftar buku sederhana (data contoh dulu). Menyimpan ke database, validasi form, dan login datang belakangan.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> sudah selesai <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a> — folder <code>perpustakaan-api</code> ada, <code>php artisan serve</code> pernah jalan. Fondasi instalasi di <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a>. Pakai <strong>Laravel 13+</strong> — butuh <strong>PHP 8.3+</strong>.</p>
</blockquote>

<h2>Spesifikasi fitur — apa yang kita kuasai?</h2>
<p>Daftar singkat yang bisa kamu centang di akhir artikel:</p>
<ol>
  <li>Mengerti <strong>route</strong> sebagai alamat pintu HTTP di file <code>routes/web.php</code>.</li>
  <li>Membuat <code>GET /api/buku</code> yang mengembalikan daftar buku contoh.</li>
  <li>Memakai <code>response()-&gt;json(...)</code> agar jawaban berbentuk <strong>JSON</strong>.</li>
  <li>Menguji di browser (atau terminal) saat <code>php artisan serve</code> hidup.</li>
</ol>
<p><strong>Awam:</strong> urutan nyaman: <strong>paham pintu -&gt; tulis route -&gt; jawab JSON -&gt; coba di browser</strong>. Belum perlu file pengatur terpisah (controller) atau menyimpan ke tabel database di sini.</p>

<h2>Istilah — ringkas untuk pintu &amp; paket</h2>
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
      <td>Route</td>
      <td>Alamat pintu: metode + path</td>
      <td><code>GET /api/buku</code></td>
    </tr>
    <tr>
      <td>HTTP GET</td>
      <td>Permintaan “tolong kirim data” (baca)</td>
      <td>Browser membuka URL daftar buku</td>
    </tr>
    <tr>
      <td>JSON</td>
      <td>Paket teks rapi untuk mesin (bukan HTML)</td>
      <td><code>{"data":[...]}</code></td>
    </tr>
    <tr>
      <td><code>routes/web.php</code></td>
      <td>Buku daftar alamat pintu di proyek</td>
      <td>Tempat menulis <code>Route::get</code></td>
    </tr>
    <tr>
      <td><code>response()-&gt;json</code></td>
      <td>Bungkus jawaban jadi JSON (plus label tipe yang cocok)</td>
      <td>Daftar buku dikirim ke browser/aplikasi</td>
    </tr>
  </tbody>
</table>
<p>Urutan belajar: <strong>baca istilah -&gt; lihat alur -&gt; tulis route -&gt; uji JSON</strong>.</p>

<h2>Kenapa routing dulu?</h2>
<p>Tanpa route, Laravel tidak tahu harus menjawab URL mana. Kamu bisa sudah punya denah folder, tapi pengunjung yang ketik alamat di browser masih “tersesat”.</p>
<p><strong>Awam:</strong> sama seperti perpustakaan — sebelum meminjamkan buku, loket harus punya nomor loket yang jelas. Routing = nomor loket. JSON = slip jawaban yang rapi untuk sistem lain (bukan poster dinding).</p>
<p>Artikel ini tetap <strong>install-dari-nol</strong> untuk routing: tidak ada paket baru hari ini. Kita pakai file route bawaan proyek. Fondasi PHP/Composer/Laravel + denah sudah di <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a> dan <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a>.</p>

<h2>Alur — dari URL sampai JSON</h2>
<figure role="img" aria-label="Alur request HTTP ke jawaban JSON daftar buku" style="background:#F5F5F0;border:2px solid #1a1a1a;border-radius:12px;padding:1rem;margin:1.25rem 0">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 760 240" width="100%" height="auto" role="img" aria-label="Alur browser ke JSON">
  <defs>
    <marker id="laravel58routeArrow" orient="auto" markerWidth="8" markerHeight="8" refX="7" refY="4" viewBox="0 0 8 8">
      <path d="M0,0 L8,4 L0,8 Z" fill="#1a1a1a"/>
    </marker>
  </defs>
  <text x="24" y="28" fill="#1a1a1a" font-size="15" font-weight="700">Alur: Browser -&gt; Route -&gt; JSON -&gt; Jawaban</text>
  <rect x="24" y="48" width="140" height="72" rx="10" fill="#2979FF"/>
  <text x="94" y="80" text-anchor="middle" fill="#fff" font-size="15" font-weight="700">Browser</text>
  <text x="94" y="100" text-anchor="middle" fill="#fff" font-size="12">GET /api/buku</text>
  <line x1="164" y1="84" x2="204" y2="84" stroke="#1a1a1a" stroke-width="3" marker-end="url(#laravel58routeArrow)"/>
  <rect x="212" y="48" width="140" height="72" rx="10" fill="#00897B"/>
  <text x="282" y="80" text-anchor="middle" fill="#fff" font-size="15" font-weight="700">Route</text>
  <text x="282" y="100" text-anchor="middle" fill="#fff" font-size="12">web.php</text>
  <line x1="352" y1="84" x2="392" y2="84" stroke="#1a1a1a" stroke-width="3" marker-end="url(#laravel58routeArrow)"/>
  <rect x="400" y="48" width="140" height="72" rx="10" fill="#F9A825"/>
  <text x="470" y="80" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">JSON</text>
  <text x="470" y="100" text-anchor="middle" fill="#1a1a1a" font-size="12">data buku</text>
  <line x1="540" y1="84" x2="580" y2="84" stroke="#1a1a1a" stroke-width="3" marker-end="url(#laravel58routeArrow)"/>
  <rect x="588" y="48" width="148" height="72" rx="10" fill="#1a1a1a"/>
  <text x="662" y="80" text-anchor="middle" fill="#fff" font-size="15" font-weight="700">Jawaban</text>
  <text x="662" y="100" text-anchor="middle" fill="#fff" font-size="12">ke browser</text>
  <text x="24" y="160" fill="#1a1a1a" font-size="13">Setelah denah siap: tulis alamat di routes/web.php, kembalikan JSON,</text>
  <text x="24" y="182" fill="#1a1a1a" font-size="13">lalu uji dengan artisan serve. Menjaga input &amp; menyimpan ke tabel datang belakangan.</text>
  <text x="24" y="214" fill="#1a1a1a" font-size="13">Urutan ini mengikuti langkah #58 (ini) — belum validasi form / tabel database.</text>
</svg>
<figcaption style="color:#1a1a1a;margin-top:.5rem"><strong>#58 (ini)</strong>: browser -&gt; route -&gt; JSON -&gt; jawaban.</figcaption>
</figure>

<h2>Persiapan — pastikan toko hidup</h2>
<p>Di terminal, masuk folder <code>perpustakaan-api</code> lalu:</p>
<pre><code class="language-bash">php artisan serve</code></pre>
<p>Biarkan terminal ini hidup. Buka tab terminal lain jika perlu mengedit file. Alamat lokal biasanya <code>http://127.0.0.1:8000</code>.</p>
<p><strong>Awam:</strong> <code>serve</code> = menyalakan lampu toko sementara. Matikan dengan Ctrl+C.</p>

<h2>Tulis route daftar buku</h2>
<p>Buka <code>routes/web.php</code>. Di bagian bawah file (setelah route bawaan), tambahkan cuplikan berikut (ini cuplikan untuk ditempel ke proyek — bukan file PHP mandiri):</p>
<pre><code class="language-php">// Cuplikan routes/web.php — tempel di bawah route bawaan
Route::get('/api/buku', function () {
    $buku = [
        ['id' =&gt; 1, 'judul' =&gt; 'Belajar PHP', 'penulis' =&gt; 'Ayu'],
        ['id' =&gt; 2, 'judul' =&gt; 'Dasar Laravel', 'penulis' =&gt; 'Budi'],
    ];

    return response()-&gt;json([
        'message' =&gt; 'Daftar buku perpustakaan mini',
        'data' =&gt; $buku,
    ]);
});
</code></pre>
<p>Contoh bentuk data yang akan dikemas jadi JSON (bisa diuji di file mandiri dulu):</p>
<pre><code class="language-php">&lt;?php

declare(strict_types=1);

$buku = [
    ['id' =&gt; 1, 'judul' =&gt; 'Belajar PHP', 'penulis' =&gt; 'Ayu'],
    ['id' =&gt; 2, 'judul' =&gt; 'Dasar Laravel', 'penulis' =&gt; 'Budi'],
];

echo json_encode([
    'message' =&gt; 'Daftar buku perpustakaan mini',
    'data' =&gt; $buku,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), PHP_EOL;
</code></pre>
<p>Satu lagi — cek jumlah item sebelum dikirim:</p>
<pre><code class="language-php">&lt;?php

declare(strict_types=1);

$data = [
    ['id' =&gt; 1, 'judul' =&gt; 'Belajar PHP'],
    ['id' =&gt; 2, 'judul' =&gt; 'Dasar Laravel'],
];

echo 'Jumlah buku contoh: ', count($data), PHP_EOL;
</code></pre>
<p><strong>Awam:</strong> baris <code>Route::get('/api/buku', ...)</code> artinya: kalau ada permintaan GET ke alamat <code>/api/buku</code>, jalankan fungsi di dalamnya. Data buku masih “contoh di kertas” (array) — belum dari tabel database.</p>
<p>Kenapa di <code>web.php</code>? Supaya awam tidak wajib memasang file/route API ekstra dulu. Nanti jalur <code>routes/api.php</code> bisa dipelajari saat kebutuhan API makin besar — fondasi route-nya sama.</p>

<h2>Uji di browser</h2>
<p>Dengan <code>php artisan serve</code> masih hidup, buka:</p>
<pre><code class="language-bash">http://127.0.0.1:8000/api/buku</code></pre>
<p>Kamu harus melihat teks JSON berisi <code>message</code> dan <code>data</code> (dua buku contoh). Kalau 404, cek: sudah simpan <code>web.php</code>? Sudah di folder proyek yang benar? URL path-nya <code>/api/buku</code>?</p>
<p><strong>Awam:</strong> browser menampilkan JSON seperti “slip jawaban”. Bentuknya kaku untuk mata manusia — itu normal. Yang penting mesin bisa membacanya.</p>

<h2>Pola Dasar — empat langkah pintu bersih</h2>
<figure role="img" aria-label="Pola Dasar empat langkah routing JSON" style="background:#F5F5F0;border:2px solid #1a1a1a;border-radius:12px;padding:1rem;margin:1.25rem 0">
<ol style="list-style:none;padding:0;margin:0;display:grid;gap:.75rem">
  <li style="display:flex;gap:.75rem;align-items:flex-start">
    <span style="flex:0 0 2rem;height:2rem;border-radius:999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div><strong style="color:#1a1a1a">Nyalakan serve</strong><br><span style="color:#1a1a1a"><code>php artisan serve</code> di folder <code>perpustakaan-api</code>.</span></div>
  </li>
  <li style="display:flex;gap:.75rem;align-items:flex-start">
    <span style="flex:0 0 2rem;height:2rem;border-radius:999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div><strong style="color:#1a1a1a">Tulis route</strong><br><span style="color:#1a1a1a">Tambah <code>GET /api/buku</code> di <code>routes/web.php</code>.</span></div>
  </li>
  <li style="display:flex;gap:.75rem;align-items:flex-start">
    <span style="flex:0 0 2rem;height:2rem;border-radius:999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div><strong style="color:#1a1a1a">Jawab JSON</strong><br><span style="color:#1a1a1a">Pakai <code>response()-&gt;json([...])</code> dengan daftar buku contoh.</span></div>
  </li>
  <li style="display:flex;gap:.75rem;align-items:flex-start">
    <span style="flex:0 0 2rem;height:2rem;border-radius:999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div><strong style="color:#1a1a1a">Uji URL</strong><br><span style="color:#1a1a1a">Buka <code>/api/buku</code> di browser — pastikan JSON muncul.</span></div>
  </li>
</ol>
</figure>

<h2>Demo peta pintu — file mandiri</h2>
<p>Simpan sebagai <code>laravel_routing_json_perpustakaan_demo.php</code>, lalu jalankan <code>php laravel_routing_json_perpustakaan_demo.php</code>. File ini mensimulasikan peta route &amp; paket JSON — tidak mengubah proyek Laravel-mu:</p>
<pre><code class="language-php">&lt;?php

declare(strict_types=1);

/**
 * Demo peta route &amp; JSON — simulasi teks untuk awam.
 * File: laravel_routing_json_perpustakaan_demo.php
 */

function petaRoute(): array
{
    return [
        'GET /api/buku' =&gt; 'daftar buku (JSON)',
    ];
}

function paketJson(): array
{
    return [
        'message' =&gt; 'Daftar buku perpustakaan mini',
        'data' =&gt; [
            ['id' =&gt; 1, 'judul' =&gt; 'Belajar PHP', 'penulis' =&gt; 'Ayu'],
            ['id' =&gt; 2, 'judul' =&gt; 'Dasar Laravel', 'penulis' =&gt; 'Budi'],
        ],
    ];
}

function demo(): void
{
    echo "=== Peta pintu (route) ===", PHP_EOL;
    foreach (petaRoute() as $alamat =&gt; $peran) {
        echo "- {$alamat} : {$peran}", PHP_EOL;
    }

    echo PHP_EOL, "=== Contoh jawaban JSON ===", PHP_EOL;
    echo json_encode(paketJson(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), PHP_EOL;
}

demo();
</code></pre>
<p><strong>Awam:</strong> <code>demo()</code> hanya menampilkan peta di terminal. Setelah paham, kerjakan langkah yang sama di <code>routes/web.php</code> sungguhan. <code>declare(strict_types=1);</code> membuat tipe lebih ketat — boleh diikuti, tidak wajib dihafal.</p>

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
      <td>404 Not Found</td>
      <td>Path salah atau file belum disimpan</td>
      <td>Cek <code>/api/buku</code> dan simpan <code>web.php</code></td>
    </tr>
    <tr>
      <td>Halaman HTML welcome, bukan JSON</td>
      <td>Membuka <code>/</code> bukan <code>/api/buku</code></td>
      <td>Ketik path lengkap di bilah alamat</td>
    </tr>
    <tr>
      <td>Connection refused</td>
      <td><code>artisan serve</code> belum jalan</td>
      <td>Nyalakan lagi di folder proyek</td>
    </tr>
    <tr>
      <td>JSON terlihat “aneh” di browser</td>
      <td>Browser menampilkan teks mentah</td>
      <td>Normal untuk awam — yang penting strukturnya ada</td>
    </tr>
  </tbody>
</table>

<h2>Latihan</h2>
<ol>
  <li>Jalankan demo PHP di atas, lalu bandingkan dengan JSON di browser setelah route hidup.</li>
  <li>Tambah satu buku ketiga di array <code>$buku</code>, simpan, refresh <code>/api/buku</code>.</li>
  <li>Jelaskan ke teman: beda singkat “alamat pintu (route)” dan “isi paket (JSON)” dengan bahasa toko/perpustakaan.</li>
</ol>

<h2>FAQ</h2>
<p><strong>Harus pakai routes/api.php?</strong><br>
Belum wajib di langkah ini. <code>web.php</code> cukup untuk belajar pintu + JSON. File <code>api.php</code> bisa belakangan saat kebutuhan API bertambah.</p>
<p><strong>Kenapa belum pakai database?</strong><br>
Supaya fokus satu hal: membuka pintu dan menjawab JSON. Menyimpan ke tabel datang setelah fondasi ini nyaman.</p>
<p><strong>Apa hubungan dengan denah (#57)?</strong><br>
<a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a> menyiapkan rumah. <strong>#58 (ini)</strong> memasang nomor loket dan slip jawaban.</p>
<p><strong>Ke mana setelah ini?</strong><br>
Berikutnya kamu akan belajar menjaga <strong>isi formulir/permintaan</strong> yang masuk lewat pintu (Request &amp; Form Request) supaya data tidak berantakan.</p>

<h2>Kesimpulan</h2>
<p>Kamu sudah membuka pintu HTTP di Laravel: menulis route <code>GET /api/buku</code>, menjawab dengan JSON, dan mengujinya saat <code>artisan serve</code> hidup. Ini langkah <strong>3/8</strong> jalur Laravel di Seri 4.</p>
<blockquote>
  <p><strong>Seri 4 progress:</strong> langkah <strong>#58 (ini)</strong> · <strong>3/8</strong> jalur Laravel · prasyarat: <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a> LIVE. Berikutnya: menjaga input permintaan (Request &amp; Form Request) untuk API perpustakaan.</p>
</blockquote>
HTML;
    }
}
