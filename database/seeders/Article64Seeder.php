<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article64Seeder extends Seeder
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
                'user_id'         => $admin->id,
                'category_id'     => $webCat->id,
                'title'           => 'Authorization Policy: Siapa Boleh Ubah',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'Aturan Izin API — Policy Laravel Siapa Boleh Ubah',
                'seo_description' => 'Seri 5 #64: setelah daftar panjang, belajar aturan izin siapa boleh ubah catatan pinjam — cek pemilik PHP dulu, cuplikan Policy Laravel, ramah awam.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['laravel', 'php', 'api', 'http', 'web', 'eloquent', 'database'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel ke-64 berhasil dipublish: '.$article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — siapa boleh ubah catatan pinjam?</h2>
<p>Artikel ini adalah <strong>#64 (ini)</strong> di <strong>Seri 5: Laravel Lanjutan</strong> (di roadmap sering disebut Framework-based). Domain tetap <strong>perpustakaan mini</strong>.</p>
<p>Di <a href="/artikel/laravel-pagination-filter-pencarian">Pagination, Filter &amp; Pencarian (#63)</a> kamu sudah merapikan daftar panjang. Sekarang pertanyaan berikutnya: <strong>siapa boleh mengubah atau menghapus catatan pinjam orang lain?</strong> Tanpa aturan, siapa saja bisa mengubah data — berbahaya.</p>
<p><strong>Awam:</strong> bayangkan kartu anggota perpustakaan. Hanya pemilik kartu (atau petugas resmi) yang boleh mengubah catatan pinjam miliknya. Itu inti <strong>aturan izin</strong> (<em>policy</em>).</p>

<blockquote>
  <p><strong>Prasyarat:</strong> sudah baca <a href="/artikel/laravel-pagination-filter-pencarian">Pagination &amp; Pencarian (#63)</a> dan <a href="/artikel/laravel-eloquent-relasi-peminjaman">Relasi Eloquent (#62)</a>. Pakai <strong>Laravel 11+</strong>.</p>
</blockquote>

<h2>Spesifikasi fitur — apa yang kita bangun?</h2>
<p>Daftar singkat yang bisa dijelaskan ke teman:</p>
<ol>
  <li><strong>Cek pemilik</strong> — sebelum ubah/hapus, pastikan pemanggil adalah pemilik catatan (atau punya peran khusus).</li>
  <li><strong>Jawaban jelas saat ditolak</strong> — status <code>403</code> dengan pesan awam “Tidak punya izin”.</li>
  <li><strong>Aturan terpusat</strong> — logika “boleh/tidak” tidak tersebar di banyak tempat.</li>
</ol>
<p><strong>Awam:</strong> urutan kerja yang nyaman: <strong>kenali siapa memanggil -&gt; cek pemilik -&gt; baru ubah data</strong>. Kalau langsung ubah tanpa cek, siapa pun bisa merusak catatan orang lain.</p>

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
      <td>Daftar “siapa boleh apa”</td>
      <td>Bukan kata sandi login</td>
    </tr>
    <tr>
      <td>Pemilik / <code>anggota_id</code></td>
      <td>Anggota yang punya catatan pinjam</td>
      <td>Dari relasi di <a href="/artikel/laravel-eloquent-relasi-peminjaman">Relasi Eloquent (#62)</a></td>
    </tr>
    <tr>
      <td><code>403 Forbidden</code></td>
      <td>Tidak punya izin</td>
      <td>Beda dengan “belum login”</td>
    </tr>
    <tr>
      <td><code>authorize</code></td>
      <td>Perintah “cek aturan izin dulu”</td>
      <td>Di Laravel, sebelum aksi sensitif</td>
    </tr>
    <tr>
      <td>Kelas Policy</td>
      <td>File tempat aturan ditulis rapi</td>
      <td>Satu tempat untuk “boleh ubah?”</td>
    </tr>
  </tbody>
</table>
<p>Urutan belajar: <strong>array PHP dulu -&gt; cek pemilik dengan <code>if</code> -&gt; baru bungkus Laravel Policy</strong>.</p>

<h2>Kenapa PHP biasa dulu?</h2>
<p>Ide “hanya pemilik yang boleh ubah” lebih mudah dirasakan di array. Kalau alurnya klik, cuplikan <code>authorize</code> dan kelas Policy terasa bungkus yang sama.</p>

<pre><code class="language-php">&lt;?php
// Mini: cek pemilik sebelum ubah status pinjam.
$pinjam = ["id" =&gt; 10, "anggota_id" =&gt; 1, "judul" =&gt; "Dasar PHP", "status" =&gt; "aktif"];
$penggunaId = 2; // bukan pemilik

if ($pinjam["anggota_id"] !== $penggunaId) {
    http_response_code(403);
    echo json_encode(["pesan" =&gt; "Tidak punya izin"], JSON_UNESCAPED_UNICODE), PHP_EOL;
    exit;
}

$pinjam["status"] = "kembali";
echo json_encode(["ok" =&gt; true, "data" =&gt; $pinjam], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL;
</code></pre>
<p>Output saat bukan pemilik:</p>
<pre><code>{
    "pesan": "Tidak punya izin"
}
</code></pre>
<p><strong>Awam:</strong> <code>anggota_id</code> di catatan harus sama dengan siapa yang memanggil. Beda? Tolak dengan <code>403</code> — artinya “Tidak punya izin”, bukan “data hilang”.</p>

<figure role="img" aria-label="Diagram alur cek izin sebelum ubah catatan pinjam" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" style="display:block;max-width:760px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif" viewBox="0 0 760 260">
  <defs>
    <marker id="laravel64policyArrow" orient="auto" markerWidth="8" markerHeight="8" refX="7" refY="4" viewBox="0 0 8 8">
      <path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/>
    </marker>
  </defs>
  <rect width="760" height="260" fill="#F5F5F0"/>
  <text x="24" y="36" fill="#1a1a1a" font-size="16" font-weight="700">Ubah catatan: Siapa memanggil -&gt; Cek pemilik -&gt; Izin / Tolak -&gt; JSON</text>
  <rect x="24" y="70" width="140" height="80" rx="8" fill="#fff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="94" y="105" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Pemanggil</text>
  <text x="94" y="128" text-anchor="middle" fill="#1a1a1a" font-size="12">kartu anggota</text>
  <line x1="164" y1="110" x2="214" y2="110" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel64policyArrow)"/>
  <rect x="218" y="70" width="140" height="80" rx="8" fill="#1a1a1a" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="288" y="105" text-anchor="middle" fill="#fff" font-size="15" font-weight="700">Cek</text>
  <text x="288" y="128" text-anchor="middle" fill="#fff" font-size="12">anggota_id</text>
  <line x1="358" y1="110" x2="408" y2="110" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel64policyArrow)"/>
  <rect x="412" y="70" width="150" height="80" rx="8" fill="#fff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="487" y="105" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Izin?</text>
  <text x="487" y="128" text-anchor="middle" fill="#1a1a1a" font-size="12">ya / 403</text>
  <line x1="562" y1="110" x2="612" y2="110" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel64policyArrow)"/>
  <rect x="616" y="70" width="120" height="80" rx="8" fill="#fff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="676" y="105" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">JSON</text>
  <text x="676" y="128" text-anchor="middle" fill="#1a1a1a" font-size="12">ok / pesan</text>
  <text x="24" y="190" fill="#1a1a1a" font-size="13">403 = Tidak punya izin (bukan “belum login”).</text>
  <text x="24" y="220" fill="#1a1a1a" font-size="13">Setelah daftar panjang jelas, kita mengunci siapa boleh mengubah baris tertentu.</text>
</svg>
<figcaption>Setelah pagination jelas, <strong>#64 (ini)</strong> mengunci siapa boleh ubah catatan pinjam lewat aturan izin.</figcaption>
</figure>

<h2>Alur izin — PHP sederhana</h2>
<p>Pemanggil — aplikasi atau alat yang memanggil API — mengirim identitas anggota. Server membandingkan dengan <code>anggota_id</code> di catatan.</p>

<pre><code class="language-php">&lt;?php
$pinjam = [
    ["id" =&gt; 10, "anggota_id" =&gt; 1, "judul" =&gt; "Dasar PHP", "status" =&gt; "aktif"],
    ["id" =&gt; 11, "anggota_id" =&gt; 2, "judul" =&gt; "Belajar Laravel", "status" =&gt; "aktif"],
];

function ubahStatusPinjam(array $pinjam, int $pinjamId, int $penggunaId, string $statusBaru): array
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
    if ($row["anggota_id"] !== $penggunaId) {
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
<p><strong>Awam:</strong> <code>404</code> = catatan tidak ada. <code>403</code> = ada, tapi bukan milikmu. <code>200</code> = pemilik cocok, boleh ubah. Pola ini sama dengan ide ubah/hapus di <a href="/artikel/laravel-crud-api-buku-ubah-hapus">CRUD ubah &amp; hapus (#61)</a>, hanya ditambah cek pemilik.</p>

<h2>Laravel — cuplikan Policy &amp; authorize (bukan file mandiri)</h2>
<p>Di proyek Laravel, aturan izin sering ditulis di kelas Policy, lalu dipanggil lewat <code>authorize</code> di pengatur kode (<code>controller</code>).</p>

<pre><code class="language-php">&lt;?php
// Cuplikan Laravel (bukan file mandiri) — kelas Policy pinjam.
namespace App\Policies;

use App\Models\Pinjam;
use App\Models\User;

class PinjamPolicy
{
    public function update(User $user, Pinjam $pinjam): bool
    {
        return $user-&gt;id === $pinjam-&gt;anggota_id;
    }
}
</code></pre>

<pre><code class="language-php">&lt;?php
// Cuplikan Laravel (bukan file mandiri) — cek izin sebelum ubah.
use App\Models\Pinjam;
use Illuminate\Http\Request;

public function update(Request $request, Pinjam $pinjam)
{
    $this-&gt;authorize('update', $pinjam);

    $pinjam-&gt;update($request-&gt;only('status'));

    return response()-&gt;json(['ok' =&gt; true, 'data' =&gt; $pinjam]);
}
</code></pre>

<p><strong>Awam:</strong></p>
<ul>
  <li><code>PinjamPolicy::update</code> = aturan “boleh ubah kalau pemilik sama”</li>
  <li><code>authorize('update', $pinjam)</code> = jalankan aturan itu dulu; gagal -&gt; Laravel otomatis jawab <code>403</code> (Tidak punya izin)</li>
  <li>Aturan di satu file = lebih mudah dirawat daripada <code>if</code> berulang di banyak tempat</li>
</ul>

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
    <div><strong style="color:#1a1a1a">Bandingkan pemilik</strong><br><span style="color:#1a1a1a"><code>anggota_id</code> catatan vs pemanggil — PHP <code>if</code> dulu.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div><strong style="color:#1a1a1a">Tolak dengan 403</strong><br><span style="color:#1a1a1a">Pesan awam “Tidak punya izin” — jangan biarkan orang lain ubah.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">5</span>
    <div><strong style="color:#1a1a1a">Pindah ke Policy</strong><br><span style="color:#1a1a1a">Tulis aturan di kelas Policy; panggil <code>authorize</code> sebelum ubah.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">6</span>
    <div><strong style="color:#1a1a1a">Uji tiga jalur</strong><br><span style="color:#1a1a1a">Pemilik benar · bukan pemilik · catatan tidak ada.</span></div>
  </li>
</ol>
</figure>

<h2>Kode lengkap — demo mandiri aturan izin</h2>
<p>Simpan sebagai <code>laravel_policy_otorisasi_api_demo.php</code>, lalu jalankan <code>php laravel_policy_otorisasi_api_demo.php</code>:</p>

<pre><code class="language-php">&lt;?php
declare(strict_types=1);

$pinjam = [
    ["id" =&gt; 10, "anggota_id" =&gt; 1, "judul" =&gt; "Dasar PHP", "status" =&gt; "aktif"],
    ["id" =&gt; 11, "anggota_id" =&gt; 2, "judul" =&gt; "Belajar Laravel", "status" =&gt; "aktif"],
];

function ubahStatusPinjam(array $pinjam, int $pinjamId, int $penggunaId, string $statusBaru): array
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
    if ($row["anggota_id"] !== $penggunaId) {
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
</code></pre>
<p><strong>Awam:</strong> <code>demo(...)</code> hanya membungkus output di terminal. <code>callable</code> = sesuatu yang bisa dipanggil seperti fungsi. <code>declare(strict_types=1);</code> membuat tipe lebih ketat — boleh diikuti, tidak wajib dihafal. Alur penting: bukan pemilik, pemilik benar, catatan hilang.</p>

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
      <td>Lupa cek <code>anggota_id</code></td>
      <td>Cek pemilik sebelum ubah/hapus</td>
    </tr>
    <tr>
      <td><code>403</code> padahal yakin pemilik</td>
      <td>ID pemanggil salah / belum login</td>
      <td>Pastikan identitas pemanggil benar</td>
    </tr>
    <tr>
      <td>Aturan tersebar di banyak file</td>
      <td>Copy-paste <code>if</code> berulang</td>
      <td>Kumpulkan di kelas Policy</td>
    </tr>
    <tr>
      <td>Pesan error membingungkan</td>
      <td>403 tanpa penjelasan awam</td>
      <td>Tulis “Tidak punya izin” yang jelas</td>
    </tr>
  </tbody>
</table>

<h2>Latihan singkat</h2>
<ol>
  <li>Ubah demo: tambah kasus “pemilik benar ubah pinjam id 11” dan bandingkan dengan kasus bukan pemilik.</li>
  <li>Jelaskan ke teman: beda <code>403</code> (Tidak punya izin) dengan <code>404</code> (tidak ketemu).</li>
  <li>Tulis satu kalimat: kenapa aturan izin lebih rapi di kelas Policy daripada <code>if</code> di banyak tempat.</li>
</ol>

<h2>FAQ singkat</h2>
<p><strong>Apakah Policy menggantikan login?</strong><br>
Tidak. Login menjawab “siapa kamu”. Policy menjawab “apakah kamu boleh melakukan ini pada baris ini”.</p>
<p><strong>Haruskah selalu pakai kelas Policy?</strong><br>
Untuk belajar, <code>if</code> PHP sudah cukup memahami ide. Di proyek Laravel nyata, Policy membantu merapikan aturan saat bertambah.</p>
<p><strong>Ke mana setelah ini?</strong><br>
Berikutnya alami: <strong>API Resource</strong> — merapikan bentuk JSON jawaban.</p>

<h2>Kesimpulan</h2>
<p>Kamu sudah mengunci siapa boleh ubah: <strong>cek pemilik</strong> dengan <code>if</code> PHP dulu, lalu pindahkan ke <strong>aturan izin</strong> (<em>Policy</em>) dan <code>authorize</code> di Laravel. Status <code>403</code> = “Tidak punya izin” — jelas untuk pemanggil.</p>
<blockquote>
  <p><strong>Seri 5 progress:</strong> langkah <strong>#64 (ini)</strong> · 4/8 Laravel Lanjutan · prasyarat: <a href="/artikel/laravel-pagination-filter-pencarian">Pagination (#63)</a> LIVE · <a href="/artikel/laravel-eloquent-relasi-peminjaman">Relasi (#62)</a> LIVE. Berikutnya: API Resource (rapikan JSON).</p>
</blockquote>
HTML;
    }
}
