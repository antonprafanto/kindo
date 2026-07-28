<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article66Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $webCat = Category::where('slug', 'web-development')->first()
            ?? Category::where('slug', 'programming')->first();

        if (! $admin || ! $webCat) {
            throw new \RuntimeException('User atau kategori web-development/programming tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'laravel-policy-otorisasi-api';

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
                'title'              => 'Authorization Policy: Siapa Boleh Ubah',
                'title_en'           => 'Authorization Policy: Who May Update',
                'excerpt'            => 'Seri 5 #66: setelah pagination & filter, kunci siapa boleh ubah catatan pinjam dengan policy/izin — cek pemilik PHP dulu, cuplikan Policy Laravel, ramah awam.',
                'excerpt_en'         => 'Seri 5 #66: after pagination & filter, lock who may update borrowing records with policy/permission rules — plain PHP owner check first, then Laravel Policy snippets.',
                'body'               => $this->body(),
                'body_en'            => $this->bodyEn(),
                'status'             => 'published',
                'is_featured'        => false,
                'seo_title'          => 'Aturan Izin API Laravel — Policy Siapa Boleh Ubah Peminjaman',
                'seo_title_en'       => 'Laravel Authorization Policy API — Who May Update Borrowing Records',
                'seo_description'    => 'Seri 5 #66: setelah pagination & filter, kunci siapa boleh ubah catatan pinjam dengan policy/izin — cek pemilik PHP dulu, cuplikan Policy Laravel, ramah awam.',
                'seo_description_en' => 'Seri 5 #66: after pagination & filter, learn who may update borrowing records with policy/permission — plain PHP owner check first, Laravel Policy snippets, beginner-friendly.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        // published_at setelah #65 supaya urutan "Terbaru" di /artikel tidak menjatuhkan #66 ke tengah daftar
        $prevPublished = Article::where('slug', 'laravel-pagination-filter-pencarian')->value('published_at');
        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        } elseif ($prevPublished && $article->published_at <= $prevPublished) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['laravel', 'php', 'api', 'http', 'web', 'eloquent', 'database'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel ke-66 berhasil dipublish: '.$article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — siapa boleh ubah catatan pinjam?</h2>
<p>Artikel ini adalah <strong>#66 (ini)</strong> di <strong>Seri 5: Laravel Lanjutan</strong>. Setelah daftar pinjam panjang dirapikan di <a href="/artikel/laravel-pagination-filter-pencarian">Pagination, Filter &amp; Pencarian (#65)</a>, pertanyaan berikutnya muncul: <strong>siapa boleh mengubah slip pinjam orang lain?</strong></p>
<p>Tanpa aturan izin, siapa saja bisa mengubah catatan pinjam milik anggota lain — berbahaya. Hari ini kita belajar <strong>policy</strong> (aturan izin): cek pemilik dulu, tolak dengan <code>403</code> kalau bukan pemilik, dan izinkan petugas (<strong>staf</strong>) lewat aturan sederhana.</p>
<p><strong>Awam:</strong> bayangkan kartu anggota perpustakaan. Hanya pemilik kartu (atau petugas resmi di loket) yang boleh mengubah catatan pinjam miliknya. Itu inti <strong>aturan izin</strong> (<em>policy</em>). <strong>Pemanggil</strong> API — aplikasi atau alat yang memanggil API — harus dikenali dulu sebelum server mengizinkan ubah data.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> sudah selesai <a href="/artikel/laravel-pagination-filter-pencarian">Pagination, Filter &amp; Pencarian (#65)</a>, paham fondasi <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a> / <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a>. Pakai <strong>Laravel 13+</strong> — butuh <strong>PHP 8.3+</strong>.</p>
</blockquote>

<h2>Spesifikasi fitur — apa yang selesai hari ini?</h2>
<p>Tiga hal ini yang kita kejar:</p>
<ol>
  <li><strong>Cek pemilik</strong> — sebelum ubah, pastikan <code>callerId</code> sama dengan <code>anggota_id</code> di catatan pinjam (atau pemanggil adalah staf).</li>
  <li><strong>Jawaban jelas saat ditolak</strong> — status <code>403</code> dengan pesan awam &ldquo;Tidak punya izin&rdquo;.</li>
  <li><strong>Aturan terpusat</strong> — logika &ldquo;boleh/tidak&rdquo; tidak tersebar di banyak tempat; di Laravel dipindah ke kelas Policy.</li>
</ol>
<p><strong>Awam:</strong> selesai artikel ini, kamu belum merapikan bentuk JSON jawaban. Kamu sedang mengunci <strong>siapa boleh ubah</strong> slip pinjam di proyek perpustakaan mini — pemilik atau staf saja. Format JSON yang cantik datang di artikel berikutnya tentang API Resource.</p>

<h2>Istilah — ringkas untuk aturan izin</h2>
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
      <td>Aturan izin / <em>policy</em></td>
      <td>Daftar &ldquo;siapa boleh apa&rdquo;</td>
      <td>Bukan kata sandi login</td>
    </tr>
    <tr>
      <td><code>anggota_id</code></td>
      <td>ID anggota pemilik catatan pinjam</td>
      <td>Dari relasi di artikel relasi Eloquent sebelumnya</td>
    </tr>
    <tr>
      <td><code>403</code> vs <code>401</code> / login</td>
      <td><code>403</code> = sudah dikenali tapi tidak punya izin · <code>401</code> = belum login</td>
      <td>Pesan awam: &ldquo;Tidak punya izin&rdquo; untuk 403</td>
    </tr>
    <tr>
      <td><code>authorize</code></td>
      <td>Perintah &ldquo;cek aturan izin dulu&rdquo;</td>
      <td>Di Laravel, sebelum aksi sensitif di <strong>pengatur kode</strong></td>
    </tr>
    <tr>
      <td>Kelas Policy</td>
      <td>File tempat aturan ditulis rapi</td>
      <td>Contoh: <code>PeminjamanPolicy</code></td>
    </tr>
    <tr>
      <td><strong>Staf</strong></td>
      <td>Petugas perpustakaan yang boleh mengubah catatan anggota</td>
      <td>Override sederhana: <code>$isStaf === true</code></td>
    </tr>
  </tbody>
</table>
<p>Urutan belajar kita: <strong>array PHP dulu -&gt; cek pemilik dengan <code>if</code> -&gt; baru bungkus Laravel Policy</strong>. Kalau loncat langsung ke Policy tanpa memahami <code>anggota_id</code>, pesan <code>403</code> sering terasa misterius.</p>

<h2>Persiapan — alat yang kamu buka</h2>
<p><strong>Alat yang dipakai di artikel ini</strong> (fondasi dari <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a> dan <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a> — tidak ada unduhan Composer baru hari ini):</p>
<ul>
  <li><strong>Explorer</strong> — cek folder proyek <code>perpustakaan-api</code>, lalu lihat <code>app\Http\Controllers</code> dan <code>app\Policies</code> untuk <strong>pengatur kode</strong> dan aturan izin.</li>
  <li><strong>Terminal</strong> — Laragon: menu <em>Terminal</em> · XAMPP: tombol <em>Shell</em>. Hindari CMD/PowerShell dari Start Menu kalau PATH PHP-mu belum rapi.</li>
  <li><strong>Editor teks</strong> — Notepad / VS Code — untuk membuka <strong>pengatur kode</strong>. Contoh: <code>notepad app\Http\Controllers\PeminjamanController.php</code> dan <code>notepad app\Policies\PeminjamanPolicy.php</code>.</li>
  <li><strong>Browser</strong> — opsional. Inti uji hari ini ada di terminal; browser berguna kalau kamu sudah menjalankan <code>php artisan serve</code> dan ingin uji lewat alamat URL.</li>
</ul>
<p><strong>Awam:</strong> untuk artikel ini <strong>satu terminal sebenarnya cukup</strong> — jalankan <code>php laravel_policy_otorisasi_api_demo.php</code> di folder proyek. Kalau <code>php artisan serve</code> dari artikel sebelumnya masih hidup, pakai <strong>terminal kedua</strong> untuk demo PHP dan perintah <code>curl.exe</code> saat menguji rute Laravel. Kalau butuh jendela kedua: Laragon — klik menu <em>Terminal</em> lagi · XAMPP — klik tombol <em>Shell</em> lagi, lalu <code>cd</code> ke folder proyek yang sama.</p>
<p>Buka terminal Laragon/Shell XAMPP, masuk ke folder proyek:</p>
<pre><code class="language-bash">cd C:\laragon\www\perpustakaan-api
</code></pre>
<p>Di XAMPP biasanya: <code>cd C:\xampp\htdocs\perpustakaan-api</code>. Sesuaikan kalau foldermu beda.</p>
<p><strong>Install-dari-nol:</strong> kalau <code>php</code> atau <code>composer</code> belum dikenali terminal, kembali dulu ke <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a>. Kalau struktur folder proyek masih membingungkan, ulangi <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a>.</p>

<h2>Kenapa PHP biasa dulu?</h2>
<p>Kalau langsung loncat ke kelas Policy di Laravel, pemula sering bingung: kenapa ditolak? Maka kita mulai dari array PHP biasa supaya cek <code>anggota_id</code> terlihat jelas sebelum dibungkus <code>authorize</code>.</p>

<pre><code class="language-php">&lt;?php
// Mini: cek pemilik sebelum ubah status pinjam.
$pinjam = ["id" =&gt; 10, "anggota_id" =&gt; 1, "judul" =&gt; "Dasar PHP", "status" =&gt; "aktif"];
$callerId = 2; // bukan pemilik

if ($callerId !== $pinjam["anggota_id"]) {
    http_response_code(403);
    echo json_encode(["pesan" =&gt; "Tidak punya izin"], JSON_UNESCAPED_UNICODE), PHP_EOL;
    exit;
}

$pinjam["status"] = "kembali";
echo json_encode(["ok" =&gt; true, "data" =&gt; $pinjam], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL;
</code></pre>
<p><strong>Awam:</strong> <code>anggota_id</code> di catatan harus sama dengan siapa yang memanggil (<code>callerId</code>). Beda? Tolak dengan <code>403</code> — artinya &ldquo;Tidak punya izin&rdquo;, bukan &ldquo;data hilang&rdquo; (<code>404</code>). Login menjawab &ldquo;siapa kamu&rdquo;; aturan izin menjawab &ldquo;apakah kamu boleh ubah baris ini&rdquo;.</p>

<h2>Alur izin — cek pemilik dulu</h2>
<p>Gerakan yang benar selalu sama:</p>
<ol>
  <li><strong>Kenali pemanggil</strong> — siapa yang login / kartu anggota mana.</li>
  <li><strong>Temukan catatan</strong> — pinjam ada? Kalau tidak, jawab <code>404</code>.</li>
  <li><strong>Bandingkan pemilik</strong> — <code>anggota_id</code> catatan vs <code>callerId</code>, atau cek apakah pemanggil staf.</li>
  <li><strong>Izinkan atau tolak</strong> — pemilik/staf boleh ubah (<code>200</code>), lainnya <code>403</code> &ldquo;Tidak punya izin&rdquo;.</li>
</ol>

<pre><code class="language-php">&lt;?php
// Salin ke file misalnya izin-cek.php lalu jalankan: php izin-cek.php
$pinjam = [
    ["id" =&gt; 10, "anggota_id" =&gt; 1, "judul" =&gt; "Dasar PHP", "status" =&gt; "aktif"],
    ["id" =&gt; 11, "anggota_id" =&gt; 2, "judul" =&gt; "Belajar Laravel", "status" =&gt; "aktif"],
];

function ubahStatusPinjam(array $pinjam, int $pinjamId, int $callerId, string $statusBaru, bool $isStaf = false): array
{
    $row = null;
    foreach ($pinjam as $p) {
        if ($p["id"] === $pinjamId) {
            $row = $p;
            break;
        }
    }
    if ($row === null) {
        return ["status" =&gt; 404, "body" =&gt; ["pesan" =&gt; "Catatan pinjam tidak ketemu"]];
    }
    if (! $isStaf &amp;&amp; $row["anggota_id"] !== $callerId) {
        return ["status" =&gt; 403, "body" =&gt; ["pesan" =&gt; "Tidak punya izin"]];
    }

    return [
        "status" =&gt; 200,
        "body" =&gt; ["ok" =&gt; true, "id" =&gt; $pinjamId, "status" =&gt; $statusBaru],
    ];
}

$r = ubahStatusPinjam($pinjam, 10, 2, "kembali");
http_response_code($r["status"]);
echo json_encode($r["body"], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL;
</code></pre>
<p><strong>Awam — cara menguji bagian ini:</strong> salin potongan di atas ke <code>izin-cek.php</code>, lalu di terminal jalankan <code>php izin-cek.php</code>. Kalau muncul JSON dengan <code>"pesan": "Tidak punya izin"</code> dan status 403, cek pemilik sudah sehat. Ubah <code>$callerId</code> ke <code>1</code> (pemilik) dan jalankan lagi — harus dapat <code>200</code> dengan <code>ok: true</code>.</p>

<figure role="img" aria-label="Alur cek izin pemilik atau staf sebelum ubah pinjam" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" style="display:block;max-width:760px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif" viewBox="0 0 760 240">
  <defs>
    <marker id="laravel66policyArrow" orient="auto" markerWidth="8" markerHeight="8" refX="7" refY="4" viewBox="0 0 8 8">
      <path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/>
    </marker>
  </defs>
  <rect width="760" height="240" fill="#F5F5F0"/>
  <text x="24" y="36" fill="#1a1a1a" font-size="16" font-weight="700">Pemanggil -&gt; Cek pemilik/staf -&gt; Update / 403</text>
  <rect x="28" y="64" width="140" height="72" rx="10" fill="#ffffff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="98" y="96" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">Pemanggil</text>
  <text x="98" y="122" text-anchor="middle" fill="#1a1a1a" font-size="12">callerId</text>
  <line x1="168" y1="100" x2="218" y2="100" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel66policyArrow)"/>
  <rect x="220" y="64" width="180" height="72" rx="10" fill="#1a1a1a" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="310" y="96" text-anchor="middle" fill="#ffffff" font-size="14" font-weight="700">Cek pemilik/staf</text>
  <text x="310" y="122" text-anchor="middle" fill="#ffffff" font-size="12">anggota_id cocok?</text>
  <line x1="400" y1="100" x2="450" y2="100" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel66policyArrow)"/>
  <rect x="452" y="64" width="130" height="72" rx="10" fill="#ffffff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="515" y="96" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">Ya: update</text>
  <text x="515" y="122" text-anchor="middle" fill="#1a1a1a" font-size="12">status 200</text>
  <rect x="600" y="64" width="130" height="72" rx="10" fill="#ffffff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="665" y="96" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">Tidak: 403</text>
  <text x="665" y="122" text-anchor="middle" fill="#1a1a1a" font-size="12">Tidak punya izin</text>
  <line x1="400" y1="140" x2="665" y2="140" stroke="#2979FF" stroke-width="2" marker-end="url(#laravel66policyArrow)"/>
  <text x="24" y="200" fill="#1a1a1a" font-size="13">403 = Tidak punya izin (bukan &ldquo;belum login&rdquo;). Staf boleh lewat aturan override sederhana.</text>
</svg>
<figcaption>Urutan yang benar: kenali pemanggil, cek pemilik atau staf, baru izinkan ubah atau tolak dengan 403.</figcaption>
</figure>

<h2>Laravel — cuplikan Policy &amp; authorize (bukan file mandiri)</h2>
<p>Di proyek Laravel, aturan izin ditulis di kelas Policy, lalu dipanggil lewat <code>authorize</code> di <strong>pengatur kode</strong> sebelum aksi sensitif.</p>

<pre><code class="language-php">&lt;?php
// Cuplikan Laravel (bukan file mandiri)
// app/Policies/PeminjamanPolicy.php

namespace App\Policies;

use App\Models\Peminjaman;
use App\Models\User;

class PeminjamanPolicy
{
    public function update(User $user, Peminjaman $peminjaman): bool
    {
        return $user-&gt;id === $peminjaman-&gt;anggota_id || $user-&gt;is_staf;
    }
}
</code></pre>

<pre><code class="language-php">&lt;?php
// Cuplikan Laravel (bukan file mandiri)
// app/Http/Controllers/PeminjamanController.php

public function update(Request $request, Peminjaman $peminjaman)
{
    $this-&gt;authorize('update', $peminjaman);

    $peminjaman-&gt;update($request-&gt;only('status'));

    return response()-&gt;json(['ok' =&gt; true, 'data' =&gt; $peminjaman]);
}
</code></pre>
<p><strong>Awam:</strong> <code>PeminjamanPolicy::update</code> = aturan &ldquo;boleh ubah kalau pemilik sama atau staf&rdquo;. <code>authorize('update', $peminjaman)</code> = jalankan aturan itu dulu; gagal -&gt; Laravel otomatis jawab <code>403</code>. Aturan di satu file Policy lebih mudah dirawat daripada <code>if</code> berulang di banyak tempat. Cuplikan ini <strong>bukan file mandiri</strong> — tempel ke proyek kalau rute ubah pinjam sudah ada.</p>
<p>Kalau <code>php artisan serve</code> sudah jalan di terminal pertama, uji di terminal kedua. Di Windows ketik <code>curl.exe</code> (bukan alias <code>curl</code> saja) supaya PowerShell tidak bingung:</p>
<pre><code class="language-bash">curl.exe -X PUT "http://127.0.0.1:8000/api/peminjaman/10" -H "Content-Type: application/json" -d "{\"status\":\"kembali\"}"
</code></pre>
<p><strong>Awam:</strong> respons JSON dari <code>curl.exe</code> adalah cara cepat melihat apakah aturan izin bekerja sebelum membuka browser. Kalau muncul 403 dengan pesan jelas, policy menolak dengan benar. Kalau 404, catatan pinjam mungkin tidak ada — beda masalah dari izin.</p>

<h2>Pola Dasar — aturan izin yang rapi</h2>
<figure style="margin:1.5rem 0;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1.25rem;color:#1a1a1a" aria-label="Enam langkah aturan izin API">
<ol style="list-style:none;padding:0;margin:0;color:#1a1a1a">
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div><strong style="color:#1a1a1a">Kenali pemanggil</strong><br><span style="color:#1a1a1a">Siapa yang login / kartu anggota mana — fondasi dari langkah sebelumnya.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div><strong style="color:#1a1a1a">Temukan catatan</strong><br><span style="color:#1a1a1a">Pinjam ada? Kalau tidak, jawab <code>404</code> jelas.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div><strong style="color:#1a1a1a">Bandingkan pemilik</strong><br><span style="color:#1a1a1a"><code>anggota_id</code> catatan vs <code>callerId</code> — PHP <code>if</code> dulu, termasuk cek staf.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div><strong style="color:#1a1a1a">Tolak dengan 403</strong><br><span style="color:#1a1a1a">Pesan awam &ldquo;Tidak punya izin&rdquo; — jangan biarkan orang lain ubah.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">5</span>
    <div><strong style="color:#1a1a1a">Pindah ke Policy</strong><br><span style="color:#1a1a1a">Tulis aturan di kelas <code>PeminjamanPolicy</code>; panggil <code>authorize</code> sebelum ubah.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">6</span>
    <div><strong style="color:#1a1a1a">Uji tiga jalur</strong><br><span style="color:#1a1a1a">Pemilik benar · bukan pemilik · catatan tidak ada · staf override.</span></div>
  </li>
</ol>
</figure>

<h2>Kode lengkap — demo mandiri</h2>
<p>Simpan sebagai <code>laravel_policy_otorisasi_api_demo.php</code>, lalu jalankan <code>php laravel_policy_otorisasi_api_demo.php</code>:</p>

<pre><code class="language-php">&lt;?php
declare(strict_types=1);

$pinjam = [
    ["id" =&gt; 10, "anggota_id" =&gt; 1, "judul" =&gt; "Dasar PHP", "status" =&gt; "aktif"],
    ["id" =&gt; 11, "anggota_id" =&gt; 2, "judul" =&gt; "Belajar Laravel", "status" =&gt; "aktif"],
];

function ubahStatusPinjam(array $pinjam, int $pinjamId, int $callerId, string $statusBaru, bool $isStaf = false): array
{
    $row = null;
    foreach ($pinjam as $p) {
        if ($p["id"] === $pinjamId) {
            $row = $p;
            break;
        }
    }
    if ($row === null) {
        return ["status" =&gt; 404, "body" =&gt; ["pesan" =&gt; "Catatan pinjam tidak ketemu"]];
    }
    if (! $isStaf &amp;&amp; $row["anggota_id"] !== $callerId) {
        return ["status" =&gt; 403, "body" =&gt; ["pesan" =&gt; "Tidak punya izin"]];
    }

    return [
        "status" =&gt; 200,
        "body" =&gt; ["ok" =&gt; true, "id" =&gt; $pinjamId, "status" =&gt; $statusBaru],
    ];
}

function demo(string $judul, callable $aksi): void
{
    echo "=== {$judul} ===", PHP_EOL;
    $hasil = $aksi();
    echo "status: ", $hasil["status"], PHP_EOL;
    echo json_encode($hasil["body"], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL, PHP_EOL;
}

demo("Bukan pemilik -&gt; 403", function () use ($pinjam) {
    return ubahStatusPinjam($pinjam, 10, 2, "kembali");
});

demo("Pemilik benar -&gt; 200", function () use ($pinjam) {
    return ubahStatusPinjam($pinjam, 10, 1, "kembali");
});

demo("Catatan tidak ada -&gt; 404", function () use ($pinjam) {
    return ubahStatusPinjam($pinjam, 99, 1, "kembali");
});

demo("Staf override -&gt; 200", function () use ($pinjam) {
    return ubahStatusPinjam($pinjam, 10, 2, "kembali", true);
});
</code></pre>
<p><strong>Awam:</strong> empat skenario di atas menunjukkan pola respons yang wajar: bukan pemilik ditolak, pemilik boleh, catatan hilang dapat 404, staf boleh ubah meski bukan pemilik. Fungsi <code>ubahStatusPinjam</code> adalah inti logika; <code>demo(...)</code> hanya membungkus output agar mudah dibaca di terminal.</p>

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
      <td>Siapa saja bisa ubah pinjam orang lain</td>
      <td>Lupa cek <code>anggota_id</code> vs <code>callerId</code></td>
      <td>Cek pemilik sebelum ubah — salin pola dari <code>izin-cek.php</code></td>
    </tr>
    <tr>
      <td><code>403</code> tanpa pesan yang jelas</td>
      <td>Respons kosong atau teknis</td>
      <td>Tulis &ldquo;Tidak punya izin&rdquo; yang awam pahami</td>
    </tr>
    <tr>
      <td>Bingung <code>403</code> vs <code>404</code></td>
      <td>Keduanya dianggap &ldquo;gagal&rdquo;</td>
      <td><code>404</code> = tidak ketemu · <code>403</code> = ada tapi tidak punya izin</td>
    </tr>
    <tr>
      <td><code>403</code> padahal yakin pemilik</td>
      <td><code>callerId</code> salah atau belum login</td>
      <td>Pastikan identitas pemanggil benar sebelum cek policy</td>
    </tr>
    <tr>
      <td>Aturan tersebar di banyak file</td>
      <td>Copy-paste <code>if</code> berulang</td>
      <td>Kumpulkan di kelas <code>PeminjamanPolicy</code></td>
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
  <li>Ubah demo: tambah kasus &ldquo;pemilik benar ubah pinjam id 11&rdquo; dan bandingkan dengan kasus bukan pemilik.</li>
  <li>Jelaskan ke teman: beda <code>403</code> (Tidak punya izin) dengan <code>404</code> (tidak ketemu).</li>
  <li>Tulis satu kalimat: kenapa aturan izin lebih rapi di kelas Policy daripada <code>if</code> di banyak tempat.</li>
</ol>

<h2>FAQ singkat</h2>
<p><strong>Apakah Policy menggantikan login?</strong><br>
Tidak. Login menjawab &ldquo;siapa kamu&rdquo; (<code>401</code> kalau belum). Policy menjawab &ldquo;apakah kamu boleh melakukan ini pada baris ini&rdquo; (<code>403</code> kalau tidak).</p>
<p><strong>Haruskah selalu pakai kelas Policy?</strong><br>
Untuk belajar, <code>if</code> PHP di <code>izin-cek.php</code> sudah cukup memahami ide. Di proyek Laravel nyata, Policy membantu merapikan aturan saat bertambah.</p>
<p><strong>Tool apa yang dibuka dulu?</strong><br>
Explorer untuk memastikan folder proyek benar (Controllers + Policies), satu terminal untuk demo PHP, editor untuk <strong>pengatur kode</strong>. Kalau <code>serve</code> hidup, terminal kedua untuk <code>curl.exe</code>.</p>
<p><strong>Potongan sintaks diuji di mana?</strong><br>
Langkah tengah (cek pemilik) salin ke <code>izin-cek.php</code>, lalu jalankan <code>php izin-cek.php</code>. Demo lengkap diuji dengan <code>php laravel_policy_otorisasi_api_demo.php</code>. Cuplikan Laravel ditempel ke <code>app\Http\Controllers\PeminjamanController.php</code> dan <code>app\Policies\PeminjamanPolicy.php</code>; kalau rute sudah ada, uji dengan <code>curl.exe</code> di terminal kedua.</p>
<p><strong>Ke mana setelah ini?</strong><br>
Berikutnya alami: <strong>API Resource</strong> — rapikan bentuk JSON jawaban.</p>

<h2>Kesimpulan</h2>
<p>Kamu sudah mengunci siapa boleh ubah catatan pinjam: <strong>cek pemilik</strong> dengan <code>if</code> PHP dulu, lalu pindahkan ke <strong>aturan izin</strong> (<em>Policy</em>) dan <code>authorize</code> di Laravel. Status <code>403</code> = &ldquo;Tidak punya izin&rdquo; — jelas untuk <strong>pemanggil</strong> API. Staf boleh lewat override sederhana sebelum aturan makin rumit.</p>
<blockquote>
  <p><strong>Seri 5 progress:</strong> langkah <strong>#66 (ini)</strong> · <strong>3/7</strong> Laravel Lanjutan · prasyarat: <a href="/artikel/laravel-pagination-filter-pencarian">Pagination, Filter &amp; Pencarian (#65)</a> LIVE. Berikutnya: <strong>API Resource</strong>.</p>
</blockquote>
HTML;
    }

    private function bodyEn(): string
    {
        $html = <<<'HTML'
<h2>Introduction — who may update a borrowing record?</h2>
<p>This article is <strong>#66 (this article)</strong> in <strong>Seri 5: Laravel Lanjutan</strong>. After long borrowing lists were tidied in <a href="/artikel/laravel-pagination-filter-pencarian">Pagination, Filter &amp; Pencarian (#65)</a>, the next question appears: <strong>who may change someone else&rsquo;s borrowing slip?</strong></p>
<p>Without permission rules, anyone could change another member&rsquo;s record — dangerous. Today we learn <strong>policy</strong> (permission rules): check the owner first, reject with <code>403</code> when it is not the owner, and allow staff (<strong>staf</strong>) through a simple override.</p>
<p><strong>Beginner:</strong> imagine a library membership card. Only the card owner (or official desk staff) may change their borrowing record. That is the core of a <strong>permission rule</strong> (<em>policy</em>). The API <strong>caller</strong> — the app or tool <strong>that calls the API</strong> — must be recognized before the server allows a data update.</p>

<blockquote>
  <p><strong>Prerequisite:</strong> finish <a href="/artikel/laravel-pagination-filter-pencarian">Pagination, Filter &amp; Pencarian (#65)</a>, and keep the foundations from <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a> / <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a>. Use <strong>Laravel 13+</strong> and <strong>PHP 8.3+</strong>.</p>
</blockquote>

<h2>Feature spec — what gets finished today?</h2>
<p>These are the three targets:</p>
<ol>
  <li><strong>Owner check</strong> — before updating, ensure <code>callerId</code> matches <code>anggota_id</code> on the borrowing record (or the caller is staff).</li>
  <li><strong>Clear rejection</strong> — status <code>403</code> with the beginner-friendly message &ldquo;Tidak punya izin&rdquo; (no permission).</li>
  <li><strong>Centralized rules</strong> — &ldquo;allowed or not&rdquo; logic is not scattered; in Laravel it moves into a Policy class.</li>
</ol>
<p><strong>Beginner:</strong> after this article, you are not tidying JSON response shape yet. You are locking <strong>who may update</strong> borrowing slips in the mini-library project — owner or staff only. Prettier JSON comes in the next article about API Resource.</p>

<h2>Terms — a quick glossary for permission rules</h2>
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
      <td>Permission rule / <em>policy</em></td>
      <td>A list of &ldquo;who may do what&rdquo;</td>
      <td>Not the login password</td>
    </tr>
    <tr>
      <td><code>anggota_id</code></td>
      <td>Member ID owning the borrowing record</td>
      <td>From the Eloquent relation article earlier</td>
    </tr>
    <tr>
      <td><code>403</code> vs <code>401</code> / login</td>
      <td><code>403</code> = recognized but not allowed · <code>401</code> = not logged in</td>
      <td>Beginner message for 403: &ldquo;Tidak punya izin&rdquo;</td>
    </tr>
    <tr>
      <td><code>authorize</code></td>
      <td>Command to &ldquo;check permission rules first&rdquo;</td>
      <td>In Laravel, before sensitive actions in the <strong>code organizer</strong></td>
    </tr>
    <tr>
      <td>Policy class</td>
      <td>File where rules are written neatly</td>
      <td>Example: <code>PeminjamanPolicy</code></td>
    </tr>
    <tr>
      <td><strong>Staff</strong></td>
      <td>Library clerk who may update member records</td>
      <td>Simple override: <code>$isStaf === true</code></td>
    </tr>
  </tbody>
</table>
<p>Our learning order: <strong>plain PHP array first -&gt; owner check with <code>if</code> -&gt; then wrap in Laravel Policy</strong>. If you jump straight to Policy without understanding <code>anggota_id</code>, a <code>403</code> response often feels mysterious.</p>

<h2>Preparation — tools to open</h2>
<p><strong>Tools used in this article</strong> (built on <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a> and <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a> — there is <strong>no new Composer download</strong> today):</p>
<ul>
  <li><strong>Explorer</strong> — check the <code>perpustakaan-api</code> project folder, then look at <code>app\Http\Controllers</code> and <code>app\Policies</code> for the <strong>code organizer</strong> and permission rules.</li>
  <li><strong>Terminal</strong> — Laragon: <em>Terminal</em> menu · XAMPP: <em>Shell</em> button. Avoid Start Menu CMD/PowerShell if your PHP PATH is still messy.</li>
  <li><strong>Text editor</strong> — Notepad / VS Code — to open the <strong>code organizer</strong>. Example: <code>notepad app\Http\Controllers\PeminjamanController.php</code> and <code>notepad app\Policies\PeminjamanPolicy.php</code>.</li>
  <li><strong>Browser</strong> — optional. The core test today is in the terminal; the browser helps if you already run <code>php artisan serve</code> and want to test through a URL.</li>
</ul>
<p><strong>Beginner:</strong> for this article, <strong>one terminal is actually enough</strong> — run <code>php laravel_policy_otorisasi_api_demo.php</code> in the project folder. If <code>php artisan serve</code> from the previous article is still alive, use a <strong>second terminal</strong> for the PHP demo and <code>curl.exe</code> when testing the Laravel route. To open a second window: Laragon — click the <em>Terminal</em> menu again · XAMPP — click the <em>Shell</em> button again, then <code>cd</code> to the same project folder.</p>
<p>Open Laragon Terminal / XAMPP Shell, then move into the project folder:</p>
<pre><code class="language-bash">cd C:\laragon\www\perpustakaan-api
</code></pre>
<p>In XAMPP it is usually: <code>cd C:\xampp\htdocs\perpustakaan-api</code>. Adjust the path if your folder is different.</p>
<p><strong>Install-from-scratch:</strong> if <code>php</code> or <code>composer</code> is not recognized in the terminal, return to <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a>. If your project folder structure is still confusing, review <a href="/artikel/laravel-struktur-env-artisan">Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)</a> first.</p>

<h2>Why start with plain PHP first?</h2>
<p>If you jump straight into a Laravel Policy class, beginners often wonder: why was I rejected? So we start from a plain PHP array so the <code>anggota_id</code> check is visible before wrapping it in <code>authorize</code>.</p>

<pre><code class="language-php">&lt;?php
// Mini: check owner before updating borrowing status.
$pinjam = ["id" =&gt; 10, "anggota_id" =&gt; 1, "judul" =&gt; "Dasar PHP", "status" =&gt; "aktif"];
$callerId = 2; // not the owner

if ($callerId !== $pinjam["anggota_id"]) {
    http_response_code(403);
    echo json_encode(["pesan" =&gt; "Tidak punya izin"], JSON_UNESCAPED_UNICODE), PHP_EOL;
    exit;
}

$pinjam["status"] = "kembali";
echo json_encode(["ok" =&gt; true, "data" =&gt; $pinjam], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL;
</code></pre>
<p><strong>Beginner:</strong> <code>anggota_id</code> on the record must match who is calling (<code>callerId</code>). Different? Reject with <code>403</code> — meaning &ldquo;no permission&rdquo;, not &ldquo;data missing&rdquo; (<code>404</code>). Login answers &ldquo;who are you&rdquo;; permission rules answer &ldquo;may you update this row&rdquo;.</p>

<h2>Permission flow — check the owner first</h2>
<p>The correct move order is always the same:</p>
<ol>
  <li><strong>Recognize the caller</strong> — who is logged in / which membership card.</li>
  <li><strong>Find the record</strong> — does the borrowing row exist? If not, answer <code>404</code>.</li>
  <li><strong>Compare owner</strong> — record <code>anggota_id</code> vs <code>callerId</code>, or check whether the caller is staff.</li>
  <li><strong>Allow or reject</strong> — owner/staff may update (<code>200</code>), others get <code>403</code> &ldquo;Tidak punya izin&rdquo;.</li>
</ol>

<pre><code class="language-php">&lt;?php
// Copy into a file such as izin-cek.php, then run: php izin-cek.php
$pinjam = [
    ["id" =&gt; 10, "anggota_id" =&gt; 1, "judul" =&gt; "Dasar PHP", "status" =&gt; "aktif"],
    ["id" =&gt; 11, "anggota_id" =&gt; 2, "judul" =&gt; "Belajar Laravel", "status" =&gt; "aktif"],
];

function ubahStatusPinjam(array $pinjam, int $pinjamId, int $callerId, string $statusBaru, bool $isStaf = false): array
{
    $row = null;
    foreach ($pinjam as $p) {
        if ($p["id"] === $pinjamId) {
            $row = $p;
            break;
        }
    }
    if ($row === null) {
        return ["status" =&gt; 404, "body" =&gt; ["pesan" =&gt; "Catatan pinjam tidak ketemu"]];
    }
    if (! $isStaf &amp;&amp; $row["anggota_id"] !== $callerId) {
        return ["status" =&gt; 403, "body" =&gt; ["pesan" =&gt; "Tidak punya izin"]];
    }

    return [
        "status" =&gt; 200,
        "body" =&gt; ["ok" =&gt; true, "id" =&gt; $pinjamId, "status" =&gt; $statusBaru],
    ];
}

$r = ubahStatusPinjam($pinjam, 10, 2, "kembali");
http_response_code($r["status"]);
echo json_encode($r["body"], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL;
</code></pre>
<p><strong>Beginner — how to test this part:</strong> copy the snippet above into <code>izin-cek.php</code>, then run <code>php izin-cek.php</code> in the terminal. If you see JSON with <code>"pesan": "Tidak punya izin"</code> and status 403, the owner check is healthy. Change <code>$callerId</code> to <code>1</code> (the owner) and run again — you should get <code>200</code> with <code>ok: true</code>.</p>

<figure role="img" aria-label="Owner or staff permission check before updating borrowing" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" style="display:block;max-width:760px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif" viewBox="0 0 760 240">
  <defs>
    <marker id="laravel66policyArrow" orient="auto" markerWidth="8" markerHeight="8" refX="7" refY="4" viewBox="0 0 8 8">
      <path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/>
    </marker>
  </defs>
  <rect width="760" height="240" fill="#F5F5F0"/>
  <text x="24" y="36" fill="#1a1a1a" font-size="16" font-weight="700">Caller -&gt; Check owner/staff -&gt; Update / 403</text>
  <rect x="28" y="64" width="140" height="72" rx="10" fill="#ffffff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="98" y="96" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">Caller</text>
  <text x="98" y="122" text-anchor="middle" fill="#1a1a1a" font-size="12">callerId</text>
  <line x1="168" y1="100" x2="218" y2="100" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel66policyArrow)"/>
  <rect x="220" y="64" width="180" height="72" rx="10" fill="#1a1a1a" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="310" y="96" text-anchor="middle" fill="#ffffff" font-size="14" font-weight="700">Check owner/staff</text>
  <text x="310" y="122" text-anchor="middle" fill="#ffffff" font-size="12">anggota_id match?</text>
  <line x1="400" y1="100" x2="450" y2="100" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel66policyArrow)"/>
  <rect x="452" y="64" width="130" height="72" rx="10" fill="#ffffff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="515" y="96" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">Yes: update</text>
  <text x="515" y="122" text-anchor="middle" fill="#1a1a1a" font-size="12">status 200</text>
  <rect x="600" y="64" width="130" height="72" rx="10" fill="#ffffff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="665" y="96" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">No: 403</text>
  <text x="665" y="122" text-anchor="middle" fill="#1a1a1a" font-size="12">Tidak punya izin</text>
  <line x1="400" y1="140" x2="665" y2="140" stroke="#2979FF" stroke-width="2" marker-end="url(#laravel66policyArrow)"/>
  <text x="24" y="200" fill="#1a1a1a" font-size="13">403 = no permission (not &ldquo;not logged in&rdquo;). Staff may pass through a simple override.</text>
</svg>
<figcaption>The correct order: recognize the caller, check owner or staff, then allow update or reject with 403.</figcaption>
</figure>

<h2>Laravel Policy snippets (not standalone files)</h2>
<p>In the Laravel project, permission rules live in a Policy class, then <code>authorize</code> is called in the <strong>code organizer</strong> before sensitive actions.</p>

<pre><code class="language-php">&lt;?php
// Cuplikan Laravel (bukan file mandiri)
// app/Policies/PeminjamanPolicy.php

namespace App\Policies;

use App\Models\Peminjaman;
use App\Models\User;

class PeminjamanPolicy
{
    public function update(User $user, Peminjaman $peminjaman): bool
    {
        return $user-&gt;id === $peminjaman-&gt;anggota_id || $user-&gt;is_staf;
    }
}
</code></pre>

<pre><code class="language-php">&lt;?php
// Cuplikan Laravel (bukan file mandiri)
// app/Http/Controllers/PeminjamanController.php

public function update(Request $request, Peminjaman $peminjaman)
{
    $this-&gt;authorize('update', $peminjaman);

    $peminjaman-&gt;update($request-&gt;only('status'));

    return response()-&gt;json(['ok' =&gt; true, 'data' =&gt; $peminjaman]);
}
</code></pre>
<p><strong>Beginner:</strong> <code>PeminjamanPolicy::update</code> = rule &ldquo;may update if same owner or staff&rdquo;. <code>authorize('update', $peminjaman)</code> = run that rule first; failure -&gt; Laravel answers <code>403</code> automatically. Rules in one Policy file are easier to maintain than repeated <code>if</code> blocks everywhere. This snippet is <strong>not a standalone file</strong> — paste it into the project when the update route exists.</p>
<p>If <code>php artisan serve</code> is already running in the first terminal, test in the second terminal. On Windows type <code>curl.exe</code> (not the <code>curl</code> alias alone) so PowerShell does not get confused:</p>
<pre><code class="language-bash">curl.exe -X PUT "http://127.0.0.1:8000/api/peminjaman/10" -H "Content-Type: application/json" -d "{\"status\":\"kembali\"}"
</code></pre>
<p><strong>Beginner:</strong> the JSON response from <code>curl.exe</code> is a fast way to see whether permission rules work before opening the browser. If you get 403 with a clear message, the policy rejected correctly. If 404, the borrowing record may not exist — a different problem from permission.</p>

<h2>Basic Pattern — neat permission rules</h2>
<figure style="margin:1.5rem 0;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1.25rem;color:#1a1a1a" aria-label="Six steps for API permission rules">
<ol style="list-style:none;padding:0;margin:0;color:#1a1a1a">
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div><strong style="color:#1a1a1a">Recognize the caller</strong><br><span style="color:#1a1a1a">Who is logged in / which membership card — foundation from earlier steps.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div><strong style="color:#1a1a1a">Find the record</strong><br><span style="color:#1a1a1a">Does the borrowing row exist? If not, answer <code>404</code> clearly.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div><strong style="color:#1a1a1a">Compare owner</strong><br><span style="color:#1a1a1a">Record <code>anggota_id</code> vs <code>callerId</code> — plain PHP <code>if</code> first, including staff check.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div><strong style="color:#1a1a1a">Reject with 403</strong><br><span style="color:#1a1a1a">Beginner message &ldquo;Tidak punya izin&rdquo; — do not let others update.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">5</span>
    <div><strong style="color:#1a1a1a">Move to Policy</strong><br><span style="color:#1a1a1a">Write rules in <code>PeminjamanPolicy</code>; call <code>authorize</code> before updating.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">6</span>
    <div><strong style="color:#1a1a1a">Test three paths</strong><br><span style="color:#1a1a1a">Correct owner · wrong owner · missing record · staff override.</span></div>
  </li>
</ol>
</figure>

<h2>Full code — self-run demo</h2>
<p>Save it as <code>laravel_policy_otorisasi_api_demo.php</code>, then run <code>php laravel_policy_otorisasi_api_demo.php</code>:</p>

<pre><code class="language-php">&lt;?php
declare(strict_types=1);

$pinjam = [
    ["id" =&gt; 10, "anggota_id" =&gt; 1, "judul" =&gt; "Dasar PHP", "status" =&gt; "aktif"],
    ["id" =&gt; 11, "anggota_id" =&gt; 2, "judul" =&gt; "Belajar Laravel", "status" =&gt; "aktif"],
];

function ubahStatusPinjam(array $pinjam, int $pinjamId, int $callerId, string $statusBaru, bool $isStaf = false): array
{
    $row = null;
    foreach ($pinjam as $p) {
        if ($p["id"] === $pinjamId) {
            $row = $p;
            break;
        }
    }
    if ($row === null) {
        return ["status" =&gt; 404, "body" =&gt; ["pesan" =&gt; "Catatan pinjam tidak ketemu"]];
    }
    if (! $isStaf &amp;&amp; $row["anggota_id"] !== $callerId) {
        return ["status" =&gt; 403, "body" =&gt; ["pesan" =&gt; "Tidak punya izin"]];
    }

    return [
        "status" =&gt; 200,
        "body" =&gt; ["ok" =&gt; true, "id" =&gt; $pinjamId, "status" =&gt; $statusBaru],
    ];
}

function demo(string $judul, callable $aksi): void
{
    echo "=== {$judul} ===", PHP_EOL;
    $hasil = $aksi();
    echo "status: ", $hasil["status"], PHP_EOL;
    echo json_encode($hasil["body"], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL, PHP_EOL;
}

demo("Wrong owner -&gt; 403", function () use ($pinjam) {
    return ubahStatusPinjam($pinjam, 10, 2, "kembali");
});

demo("Correct owner -&gt; 200", function () use ($pinjam) {
    return ubahStatusPinjam($pinjam, 10, 1, "kembali");
});

demo("Missing record -&gt; 404", function () use ($pinjam) {
    return ubahStatusPinjam($pinjam, 99, 1, "kembali");
});

demo("Staff override -&gt; 200", function () use ($pinjam) {
    return ubahStatusPinjam($pinjam, 10, 2, "kembali", true);
});
</code></pre>
<p><strong>Beginner:</strong> the four scenarios above show a sensible response pattern: wrong owner rejected, owner allowed, missing record gets 404, staff may update even when not the owner. Function <code>ubahStatusPinjam</code> is the core logic; <code>demo(...)</code> only wraps output so the terminal result is easy to read.</p>

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
      <td>Anyone can update others&rsquo; borrowing rows</td>
      <td>Forgetting to check <code>anggota_id</code> vs <code>callerId</code></td>
      <td>Check owner before update — copy the pattern from <code>izin-cek.php</code></td>
    </tr>
    <tr>
      <td><code>403</code> without a clear message</td>
      <td>Empty or technical response</td>
      <td>Write &ldquo;Tidak punya izin&rdquo; that beginners understand</td>
    </tr>
    <tr>
      <td>Confusing <code>403</code> vs <code>404</code></td>
      <td>Both treated as &ldquo;failed&rdquo;</td>
      <td><code>404</code> = not found · <code>403</code> = found but not allowed</td>
    </tr>
    <tr>
      <td><code>403</code> even when sure you are the owner</td>
      <td>Wrong <code>callerId</code> or not logged in</td>
      <td>Confirm caller identity before checking policy</td>
    </tr>
    <tr>
      <td>Rules scattered across many files</td>
      <td>Repeated copy-paste <code>if</code> blocks</td>
      <td>Collect rules in <code>PeminjamanPolicy</code></td>
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
  <li>Change the demo: add a case &ldquo;correct owner updates borrowing id 11&rdquo; and compare with the wrong-owner case.</li>
  <li>Explain to a friend: difference between <code>403</code> (no permission) and <code>404</code> (not found).</li>
  <li>Write one sentence: why permission rules are neater in a Policy class than <code>if</code> in many places.</li>
</ol>

<h2>FAQ</h2>
<p><strong>Does Policy replace login?</strong><br>
No. Login answers &ldquo;who are you&rdquo; (<code>401</code> when not logged in). Policy answers &ldquo;may you do this on this row&rdquo; (<code>403</code> when not allowed).</p>
<p><strong>Must I always use a Policy class?</strong><br>
For learning, plain PHP <code>if</code> in <code>izin-cek.php</code> is enough to understand the idea. In a real Laravel project, Policy helps tidy rules as they grow.</p>
<p><strong>Which tools should I open first?</strong><br>
Explorer to confirm the project folder (Controllers + Policies), one terminal for the PHP demo, editor for the <strong>code organizer</strong>. If <code>serve</code> is alive, a second terminal for <code>curl.exe</code>.</p>
<p><strong>Where should I test the snippets?</strong><br>
The middle step (owner check) is copied into <code>izin-cek.php</code>, then run with <code>php izin-cek.php</code>. The full demo is tested with <code>php laravel_policy_otorisasi_api_demo.php</code>. Laravel snippets are pasted into <code>app\Http\Controllers\PeminjamanController.php</code> and <code>app\Policies\PeminjamanPolicy.php</code>; when the route exists, test with <code>curl.exe</code> in a second terminal.</p>
<p><strong>Where next?</strong><br>
The natural next step is <strong>API Resource</strong> — tidy the JSON response shape.</p>

<h2>Conclusion</h2>
<p>You locked who may update borrowing records: <strong>owner check</strong> with plain PHP <code>if</code> first, then move rules into a <strong>Policy</strong> and <code>authorize</code> in Laravel. Status <code>403</code> = &ldquo;Tidak punya izin&rdquo; — clear for the API <strong>caller</strong>. Staff may pass through a simple override before rules grow more complex.</p>
<blockquote>
  <p><strong>Seri 5 progress:</strong> step <strong>#66 (this article)</strong> · <strong>3/7</strong> Laravel Lanjutan · prerequisite: <a href="/artikel/laravel-pagination-filter-pencarian">Pagination, Filter &amp; Pencarian (#65)</a> LIVE. Next: <strong>API Resource</strong>.</p>
</blockquote>
HTML;

        return str_replace(
            [
                'Seri 5: Laravel Lanjutan',
                'Pagination, Filter &amp; Pencarian (#65)',
                'Instal PHP, Composer &amp; Proyek Laravel (#56)',
                'Struktur Folder, <code>.env</code> &amp; Artisan Laravel (#57)',
                'API Resource',
                'Laravel Lanjutan',
            ],
            [
                'Seri 5: Advanced Laravel',
                'Pagination, Filtering &amp; Search (#65)',
                'Install PHP, Composer &amp; Your First Laravel Project (#56)',
                'Folder Structure, <code>.env</code> &amp; Artisan Laravel (#57)',
                'API Resource',
                'Advanced Laravel',
            ],
            $html
        );
    }
}
