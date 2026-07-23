<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article56Seeder extends Seeder
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
                'title'           => 'Laravel Routing & JSON: Pintu HTTP Perpustakaan',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'Laravel Routing & JSON — API untuk Pemula',
                'seo_description' => 'Artikel pertama Laravel di Seri 4: paham route sebagai pintu HTTP, kirim JSON, dan status 200/404 — domain perpustakaan mini, berbahasa Indonesia.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['laravel', 'php', 'api', 'http', 'web'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel ke-56 berhasil dipublish: '.$article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — dari object PHP ke pintu HTTP</h2>
<p>Di <a href="/artikel/oop-php-visibility-composition">Visibility &amp; Composition (#55)</a> kamu sudah punya object <code>Buku</code> dan <code>Katalog</code>. Artikel ini adalah <strong>#56 (ini)</strong> — langkah pertama stack <strong>Laravel</strong> di Seri 4.</p>
<p>Ide barunya sederhana: orang (atau aplikasi) mengetuk alamat URL, Laravel memilih <strong>route</strong> (pintu), lalu menjawab dengan <strong>JSON</strong> (data rapi untuk komputer).</p>
<p><strong>Awam:</strong> bayangkan loket perpustakaan. Pengunjung bilang “saya mau daftar buku” — itu URL. Petugas memilih loket yang tepat — itu route. Jawaban tertulis rapi di kertas data — itu JSON.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> sudah baca <a href="/artikel/oop-php-visibility-composition">Visibility &amp; Composition (#55)</a> — paham class/object ringan. Domain tetap <strong>perpustakaan mini</strong>. Pakai <strong>Laravel 11+</strong> — sintaks route &amp; JSON di sini berlaku di versi modern.</p>
</blockquote>

<h2>Route — pintu yang dipilih dari URL</h2>
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
      <td>Aturan: “kalau URL ini dikunjungi, jalankan kode ini”</td>
      <td><code>GET /api/buku</code> -&gt; daftar buku</td>
    </tr>
    <tr>
      <td>JSON</td>
      <td>Format teks yang mudah dibaca program (bukan halaman HTML)</td>
      <td><code>{"judul":"Belajar PHP"}</code></td>
    </tr>
    <tr>
      <td>Status HTTP</td>
      <td>Kode singkat: sukses, tidak ketemu, salah, dll.</td>
      <td><code>200</code> OK · <code>404</code> tidak ketemu</td>
    </tr>
    <tr>
      <td>GET</td>
      <td>Cara mengetuk pintu: “minta data” (baca), bukan kirim form</td>
      <td><code>GET /api/buku</code></td>
    </tr>
  </tbody>
</table>
<p>Jangan hafal semua status dulu. Cukup dua: <strong>200</strong> (berhasil) dan <strong>404</strong> (pintu/data tidak ada).</p>

<h2>JSON dulu — tanpa framework</h2>
<p>Kenapa belum langsung buka Laravel? Karena ide JSON + status HTTP bisa dirasakan dulu di PHP biasa. Kalau ide-nya sudah “klik”, cuplikan Laravel nanti terasa seperti bungkus yang sama — bukan sihir baru.</p>
<p>Sebelum Laravel, lihat ide JSON di PHP biasa:</p>

<pre><code class="language-php">&lt;?php
$buku = [
    "judul" => "Belajar PHP",
    "tahun" => 2024,
];

header("Content-Type: application/json; charset=utf-8");
http_response_code(200);
echo json_encode($buku, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL;
</code></pre>

<p>Output:</p>
<pre><code>{
    "judul": "Belajar PHP",
    "tahun": 2024
}
</code></pre>

<p><strong>Awam:</strong> <code>json_encode</code> mengubah array PHP menjadi teks JSON. Header bilang “ini JSON, bukan HTML”.</p>

<h2>Kalau data tidak ada — status 404</h2>
<p>Pintu yang benar tetap bisa menjawab “tidak ketemu” dengan jujur:</p>

<pre><code class="language-php">&lt;?php
$id = 99;
$koleksi = [
    1 => ["judul" => "Belajar PHP", "tahun" => 2024],
];

header("Content-Type: application/json; charset=utf-8");

if (! isset($koleksi[$id])) {
    http_response_code(404);
    echo json_encode(["pesan" => "Buku tidak ditemukan"], JSON_UNESCAPED_UNICODE), PHP_EOL;
    exit;
}

http_response_code(200);
echo json_encode($koleksi[$id], JSON_UNESCAPED_UNICODE), PHP_EOL;
</code></pre>

<p>Output:</p>
<pre><code>{"pesan":"Buku tidak ditemukan"}
</code></pre>

<p>(Status HTTP-nya <code>404</code> — di browser, buka panel Developer Tools; atau di terminal jalankan <code>curl -i</code>. Angka status itu yang dicari, bukan hanya teks JSON.)</p>

<figure role="img" aria-label="Diagram browser memanggil route Laravel lalu menerima JSON" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 240" style="display:block;max-width:720px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="laravel56jsonArrow" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/></marker>
  </defs>
  <rect x="0" y="0" width="720" height="240" fill="#F5F5F0" rx="6"/>
  <text x="360" y="28" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">URL diketuk -&gt; route dipilih -&gt; JSON dikirim</text>
  <rect x="40" y="70" width="160" height="100" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="120" y="115" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">Browser / curl</text>
  <text x="120" y="140" text-anchor="middle" fill="#2D3748" font-size="12">GET /api/buku</text>
  <rect x="280" y="70" width="160" height="100" rx="6" fill="#1a1a1a" stroke="#000" stroke-width="2.5"/>
  <text x="360" y="115" text-anchor="middle" fill="#fff" font-size="14" font-weight="700">Laravel Route</text>
  <text x="360" y="140" text-anchor="middle" fill="#90CDF4" font-size="12">pintu HTTP</text>
  <rect x="520" y="70" width="160" height="100" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="600" y="115" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">JSON + status</text>
  <text x="600" y="140" text-anchor="middle" fill="#2D3748" font-size="12">200 atau 404</text>
  <line x1="200" y1="120" x2="280" y2="120" stroke="#2979FF" stroke-width="2.5" marker-end="url(#laravel56jsonArrow)"/>
  <line x1="440" y1="120" x2="520" y2="120" stroke="#2979FF" stroke-width="2.5" marker-end="url(#laravel56jsonArrow)"/>
</svg>
<figcaption style="margin-top:.75rem;color:#2D3748;font-size:.95rem">Route bukan “halaman web”. Ia adalah pintu yang menjawab data — sering berupa JSON.</figcaption>
</figure>

<h2>Laravel — menulis pintu JSON</h2>
<p>Di project Laravel, cuplikan tipikal (misalnya <code>routes/api.php</code>). File ini <strong>bukan</strong> dijalankan dengan <code>php file.php</code> — ia hidup di dalam project Laravel:</p>

<pre><code class="language-php">&lt;?php
// Cuplikan Laravel (bukan file mandiri) — ide sama dengan demo JSON di atas.
use Illuminate\Support\Facades\Route;

Route::get('/api/buku', function () {
    return response()-&gt;json([
        ["judul" =&gt; "Belajar PHP", "tahun" =&gt; 2024],
        ["judul" =&gt; "Laravel Praktis", "tahun" =&gt; 2025],
    ]);
});
</code></pre>

<p><strong>Awam:</strong></p>
<ul>
  <li><code>Route::get(...)</code> = “kalau ada yang GET ke URL ini…”</li>
  <li><code>response()-&gt;json(...)</code> = “jawab dengan JSON + header yang benar”</li>
  <li>Default sukses biasanya status <code>200</code></li>
</ul>

<pre><code class="language-php">&lt;?php
// Cuplikan Laravel — satu buku atau 404.
use Illuminate\Support\Facades\Route;

Route::get('/api/buku/{id}', function (int $id) {
    $koleksi = [
        1 =&gt; ["judul" =&gt; "Belajar PHP", "tahun" =&gt; 2024],
    ];

    if (! isset($koleksi[$id])) {
        return response()-&gt;json(["pesan" =&gt; "Buku tidak ditemukan"], 404);
    }

    return response()-&gt;json($koleksi[$id]);
});
</code></pre>

<p>Argumen kedua <code>404</code> di <code>response()-&gt;json(..., 404)</code> mengatur status HTTP — sama ide-nya dengan <code>http_response_code(404)</code> di PHP biasa.</p>

<h2>Pola Dasar — routing &amp; JSON</h2>
<figure style="margin:1.5rem 0;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1.25rem;color:#1a1a1a" aria-label="Lima langkah Laravel routing dan JSON">
<ol style="list-style:none;padding:0;margin:0;color:#1a1a1a">
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Tentukan pintu (URL + cara ketuk)</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem"><code>GET /api/buku</code> untuk daftar; <code>GET /api/buku/{id}</code> untuk satu item.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Siapkan data sebagai array/object</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Sama seperti array PHP di jembatan OOP — belum perlu database di artikel ini.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Kirim JSON, bukan HTML</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem"><code>response()-&gt;json(...)</code> mengurus header yang bilang “ini JSON” (sering disebut Content-Type).</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Pakai status yang jujur</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Ketemu -&gt; 200. Tidak ketemu -&gt; 404. Jangan selalu 200 dengan pesan bohong.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">5</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Baru pikir validasi request</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Berikutnya: <a href="/artikel/laravel-request-validasi-api">Request &amp; Form Request (#57)</a> — penjaga di pintu masuk supaya data kotor tidak masuk sembarangan.</span>
    </div>
  </li>
</ol>
</figure>

<h2>Kode lengkap — <code>laravel_routing_json_demo.php</code></h2>
<p>Simpan dan jalankan: <code>php laravel_routing_json_demo.php</code>. Ini meniru jawaban API (JSON + status) tanpa server Laravel — supaya ide-nya terasa dulu.</p>
<p><strong>Awam:</strong> baris <code>mixed $data</code> artinya “data bisa bermacam bentuk (array, teks, dll.)”. Tidak perlu dihafal; fokus ke <code>kirimJson()</code> yang mengurus status + JSON. <code>array_values(...)</code> hanya merapikan daftar jadi nomor urut 0, 1, 2… supaya JSON-nya berbentuk array daftar, bukan objek ber-id.</p>

<pre><code class="language-php">&lt;?php
/**
 * Demo ide routing &amp; JSON (Seri 4 #56).
 * Di Laravel, ide yang sama hidup di Route + response()-&gt;json.
 */

declare(strict_types=1);

function kirimJson(mixed $data, int $status = 200): void
{
    http_response_code($status);
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL;
}

function demo(): void
{
    $koleksi = [
        1 =&gt; ["judul" =&gt; "Belajar PHP", "tahun" =&gt; 2024],
        2 =&gt; ["judul" =&gt; "Laravel Praktis", "tahun" =&gt; 2025],
    ];

    // Simulasi: daftar buku (200)
    echo "=== GET /api/buku ===", PHP_EOL;
    kirimJson(array_values($koleksi), 200);

    // Simulasi: tidak ketemu (404)
    echo "=== GET /api/buku/99 ===", PHP_EOL;
    kirimJson(["pesan" =&gt; "Buku tidak ditemukan"], 404);
}

demo();
</code></pre>

<p>Output yang diharapkan:</p>
<pre><code>=== GET /api/buku ===
[
    {
        "judul": "Belajar PHP",
        "tahun": 2024
    },
    {
        "judul": "Laravel Praktis",
        "tahun": 2025
    }
]
=== GET /api/buku/99 ===
{
    "pesan": "Buku tidak ditemukan"
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
      <td>Jawaban terlihat seperti HTML</td>
      <td>Mengembalikan halaman HTML / teks biasa</td>
      <td>Pakai <code>response()-&gt;json(...)</code></td>
    </tr>
    <tr>
      <td>Selalu status 200 padahal gagal</td>
      <td>Lupa argumen status</td>
      <td><code>response()-&gt;json(..., 404)</code></td>
    </tr>
    <tr>
      <td>Route tidak ketemu</td>
      <td>URL atau cara ketuk (GET/POST) salah, atau daftar route masih tersimpan lama</td>
      <td>Cek path; <code>php artisan route:list</code></td>
    </tr>
    <tr>
      <td>Langsung loncat database</td>
      <td>Mau model database terlalu dini</td>
      <td>Rapatkan pintu JSON dulu (<strong>#56 (ini)</strong>), baru penyimpanan data nanti</td>
    </tr>
    <tr>
      <td>Satu file route jadi gudang besar</td>
      <td>Semua logika ditumpuk di dalam file route</td>
      <td>Nanti pecah ke controller — mulai dari pintu yang tipis</td>
    </tr>
  </tbody>
</table>

<h2>Latihan singkat</h2>
<ol>
  <li>Di demo PHP, tambah buku id <code>3</code> dan pastikan daftar memuat 3 item.</li>
  <li>Ubah simulasi id <code>99</code> menjadi id <code>1</code> dan pastikan output menampilkan judul buku (bukan pesan 404).</li>
  <li>Di cuplikan Laravel, tulis route <code>GET /api/ping</code> yang mengembalikan <code>{"ok":true}</code>.</li>
</ol>

<h2>FAQ singkat</h2>
<p><strong>Harus install Laravel dulu?</strong><br>Untuk memahami ide: demo PHP di atas sudah cukup. Untuk latihan framework: buat project Laravel 11+ lalu tempel cuplikan route.</p>
<p><strong>Kenapa JSON, bukan HTML?</strong><br>API biasanya dilayani ke aplikasi lain (mobile, frontend, IoT). JSON lebih mudah diparse program daripada halaman penuh.</p>
<p><strong>Apa bedanya <code>routes/web.php</code> dan <code>routes/api.php</code>?</strong><br>Secara awam: <code>web</code> sering untuk halaman + login/sesi di browser; <code>api</code> untuk JSON ke aplikasi lain. Lapisan pengaman tambahan (sering disebut middleware) menyusul — fokus dulu: ada pintu, ada jawaban JSON.</p>
<p><strong>Lanjut ke mana?</strong><br>Berikutnya: <a href="/artikel/laravel-request-validasi-api">Request &amp; Form Request (#57)</a> — penjaga di pintu masuk supaya data kotor tidak masuk sembarangan.</p>

<h2>Kesimpulan &amp; langkah berikutnya</h2>
<p>Route = pintu. JSON = isi jawaban. Status = kejujuran sukses/gagal. Tiga ide ini yang membuat API perpustakaan bisa diajak bicara dari luar.</p>
<p>Artikel ini adalah <strong>#56 (ini)</strong> — pembuka Laravel setelah <a href="/artikel/oop-php-visibility-composition">Visibility &amp; Composition (#55)</a> menutup jembatan OOP PHP.</p>

<blockquote>
  <p><strong>Seri 4 progress:</strong> langkah <strong>#56 (ini)</strong> · 6/8 menuju Capstone Laravel · stack Laravel <strong>1/5</strong> · prasyarat: <a href="/artikel/oop-php-visibility-composition">Visibility &amp; Composition (#55)</a> LIVE. Berikutnya: <a href="/artikel/laravel-request-validasi-api">Request &amp; Form Request (#57)</a> — penjaga input di pintu HTTP.</p>
</blockquote>
HTML;
    }
}
