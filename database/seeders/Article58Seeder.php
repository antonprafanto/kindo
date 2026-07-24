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

        $slug = 'laravel-controller-service-eloquent';

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
        ] as $tagSlug => $tagName) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagName]);
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $webCat->id,
                'title'           => 'Controller, Service & Eloquent: Susun Kode API Rapi',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'Laravel Controller, Service & Eloquent untuk Pemula',
                'seo_description' => 'Seri 4 #58: susun API perpustakaan — pengatur kode (controller), layanan (service), dan Eloquent sebagai pintu ke tabel, berbahasa Indonesia.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['laravel', 'php', 'api', 'http', 'web', 'eloquent'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel ke-58 berhasil dipublish: '.$article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — penjaga sudah ada, siapa yang mengatur alur?</h2>
<p>Di <a href="/artikel/laravel-request-validasi-api">Request &amp; Form Request (#57)</a> kamu sudah punya <strong>penjaga input</strong>. Artikel ini adalah <strong>#58 (ini)</strong> — langkah ketiga stack <strong>Laravel</strong> di Seri 4.</p>
<p>Ide barunya: setelah data lolos penjaga, kode jangan menumpuk di satu tempat. Kita membagi peran: <strong>pengatur kode (controller)</strong> mengatur alur, <strong>layanan (service)</strong> mengerjakan langkah kerja (sering disebut logika bisnis), dan <strong>Eloquent</strong> sebagai pintu ke tabel database.</p>
<p><strong>Awam:</strong> bayangkan perpustakaan. Petugas loket (penjaga) cek formulir. Manajer loket (controller) bilang “simpan buku ini”. Staf rak (service) menyusun langkah kerja. Buku lalu masuk kartu katalog (Eloquent / tabel) — bukan ditulis di kertas acak di meja loket.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> sudah baca <a href="/artikel/laravel-request-validasi-api">Request &amp; Form Request (#57)</a> — paham validasi, status 422/201. Domain tetap <strong>perpustakaan mini</strong>. Pakai <strong>Laravel 11+</strong> — pola controller/service di sini berlaku di versi modern.</p>
</blockquote>

<h2>Istilah — Controller, Service, Eloquent</h2>
<table>
  <thead>
    <tr>
      <th>Istilah</th>
      <th>Arti awam</th>
      <th>Contoh singkat</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Controller</td>
      <td>Pengatur kode: terima request bersih, panggil layanan, kembalikan JSON</td>
      <td><code>BukuController::store</code></td>
    </tr>
    <tr>
      <td>Service</td>
      <td>Layanan: tempat langkah kerja (“tambah buku ke koleksi”)</td>
      <td><code>BukuService::tambah</code></td>
    </tr>
    <tr>
      <td>Eloquent</td>
      <td>Cara Laravel bicara ke tabel database lewat kelas Model</td>
      <td><code>Buku::create(...)</code></td>
    </tr>
    <tr>
      <td>Model</td>
      <td>Kelas yang mewakili satu jenis data (misalnya baris di tabel buku)</td>
      <td><code>class Buku extends Model</code></td>
    </tr>
  </tbody>
</table>
<p>Jangan hafal semua dulu. Cukup ingat: <strong>controller mengatur</strong>, <strong>service mengerjakan</strong>, <strong>Eloquent menyimpan/membaca</strong>.</p>

<h2>Kenapa belum langsung Eloquent?</h2>
<p>Kenapa belum langsung model Laravel? Karena ide “pisah peran” bisa dirasakan di PHP biasa dengan array sebagai “rak sementara”. Kalau ide-nya sudah “klik”, cuplikan Laravel nanti terasa seperti bungkus yang sama — hanya penyimpanannya diganti tabel sungguhan.</p>

<pre><code class="language-php">&lt;?php
// Rak sementara di memori (bukan database).
$rak = [];

class BukuService
{
    public function __construct(private array &amp;$rak)
    {
    }

    public function tambah(array $data): array
    {
        $id = count($this-&gt;rak) + 1;
        $buku = [
            "id" =&gt; $id,
            "judul" =&gt; $data["judul"],
            "tahun" =&gt; (int) $data["tahun"],
        ];
        $this-&gt;rak[] = $buku;

        return $buku;
    }
}

// Pengatur kode tipis: terima data bersih -&gt; panggil layanan -&gt; jawab JSON.
function simpanBuku(array $data, BukuService $layanan): void
{
    $buku = $layanan-&gt;tambah($data);
    header("Content-Type: application/json; charset=utf-8");
    http_response_code(201);
    echo json_encode(["ok" =&gt; true, "buku" =&gt; $buku], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL;
}

$layanan = new BukuService($rak);
simpanBuku(["judul" =&gt; "Belajar PHP", "tahun" =&gt; 2024], $layanan);
</code></pre>

<p>Output:</p>
<pre><code>{
    "ok": true,
    "buku": {
        "id": 1,
        "judul": "Belajar PHP",
        "tahun": 2024
    }
}
</code></pre>

<p><strong>Awam:</strong> fungsi <code>simpanBuku</code> seperti controller mini. Kelas <code>BukuService</code> mengerjakan “cara menambah buku”. Array <code>$rak</code> sementara menggantikan tabel — nanti diganti Eloquent.</p>

<figure role="img" aria-label="Diagram request ke controller, service, lalu penyimpanan" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 760 240" style="display:block;max-width:760px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="laravel58ctrlArrow" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/></marker>
  </defs>
  <rect x="0" y="0" width="760" height="240" fill="#F5F5F0" rx="6"/>
  <text x="380" y="28" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Request bersih -&gt; controller -&gt; service -&gt; simpan</text>
  <rect x="24" y="70" width="140" height="100" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="94" y="115" text-anchor="middle" fill="#1a1a1a" font-size="13" font-weight="700">Request</text>
  <text x="94" y="140" text-anchor="middle" fill="#2D3748" font-size="11">sudah lolos penjaga</text>
  <rect x="200" y="70" width="140" height="100" rx="6" fill="#1a1a1a" stroke="#000" stroke-width="2.5"/>
  <text x="270" y="115" text-anchor="middle" fill="#fff" font-size="13" font-weight="700">Controller</text>
  <text x="270" y="140" text-anchor="middle" fill="#90CDF4" font-size="11">atur alur</text>
  <rect x="376" y="70" width="140" height="100" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="446" y="115" text-anchor="middle" fill="#1a1a1a" font-size="13" font-weight="700">Service</text>
  <text x="446" y="140" text-anchor="middle" fill="#2D3748" font-size="11">langkah kerja</text>
  <rect x="552" y="70" width="180" height="100" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="642" y="115" text-anchor="middle" fill="#1a1a1a" font-size="13" font-weight="700">Eloquent / rak</text>
  <text x="642" y="140" text-anchor="middle" fill="#2D3748" font-size="11">simpan &amp; baca</text>
  <line x1="164" y1="120" x2="200" y2="120" stroke="#2979FF" stroke-width="2.5" marker-end="url(#laravel58ctrlArrow)"/>
  <line x1="340" y1="120" x2="376" y2="120" stroke="#2979FF" stroke-width="2.5" marker-end="url(#laravel58ctrlArrow)"/>
  <line x1="516" y1="120" x2="552" y2="120" stroke="#2979FF" stroke-width="2.5" marker-end="url(#laravel58ctrlArrow)"/>
</svg>
<figcaption style="margin-top:.75rem;color:#2D3748;font-size:.95rem">Controller tidak “menyimpan sendiri”. Ia mengatur siapa yang bekerja — supaya file tetap tipis dan mudah dibaca.</figcaption>
</figure>

<h2>Baca daftar — service yang sama</h2>
<p>Menambah dan membaca memakai layanan yang sama, supaya aturan “cara kerja buku” tidak tersebar:</p>

<pre><code class="language-php">&lt;?php
$rak = [
    ["id" =&gt; 1, "judul" =&gt; "Belajar PHP", "tahun" =&gt; 2024],
];

class BukuService
{
    public function __construct(private array &amp;$rak)
    {
    }

    public function semua(): array
    {
        return $this-&gt;rak;
    }

    public function cari(int $id): ?array
    {
        foreach ($this-&gt;rak as $buku) {
            if ((int) $buku["id"] === $id) {
                return $buku;
            }
        }

        return null;
    }
}

$layanan = new BukuService($rak);
header("Content-Type: application/json; charset=utf-8");
echo json_encode(["data" =&gt; $layanan-&gt;semua()], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL;
</code></pre>

<p>Output:</p>
<pre><code>{
    "data": [
        {
            "id": 1,
            "judul": "Belajar PHP",
            "tahun": 2024
        }
    ]
}
</code></pre>

<p><strong>Awam:</strong> kalau nanti ganti array jadi tabel, cukup ubah isi service — controller tetap “panggil layanan, balas JSON”.</p>

<h2>Laravel — Controller tipis</h2>
<p>Di project Laravel, cuplikan tipikal memindahkan alur ke kelas controller. File ini <strong>bukan</strong> dijalankan dengan <code>php file.php</code> — ia hidup di dalam project Laravel:</p>

<pre><code class="language-php">&lt;?php
// Cuplikan Laravel (bukan file mandiri) — pengatur kode.
namespace App\Http\Controllers;

use App\Http\Requests\StoreBukuRequest;
use App\Services\BukuService;
use Illuminate\Http\JsonResponse;

class BukuController extends Controller
{
    public function __construct(private BukuService $layanan)
    {
    }

    public function store(StoreBukuRequest $request): JsonResponse
    {
        $buku = $this-&gt;layanan-&gt;tambah($request-&gt;validated());

        return response()-&gt;json(['ok' =&gt; true, 'buku' =&gt; $buku], 201);
    }
}
</code></pre>

<p><strong>Awam:</strong></p>
<ul>
  <li><code>StoreBukuRequest</code> = penjaga dari <a href="/artikel/laravel-request-validasi-api">Request &amp; Form Request (#57)</a> — data sudah dicek</li>
  <li><code>validated()</code> = ambil hanya isian yang sudah lolos penjaga</li>
  <li><code>BukuService</code> = layanan yang tahu cara menambah buku</li>
  <li><code>private BukuService $layanan</code> di konstruktor = Laravel menyiapkan layanan otomatis (kamu tidak perlu <code>new</code> manual di sini)</li>
  <li><code>JsonResponse</code> = tipe jawaban “ini JSON” (boleh diabaikan dulu kalau masih asing)</li>
  <li>Controller hanya mengatur: terima -&gt; panggil layanan -&gt; JSON</li>
</ul>
<p>Route biasanya mengarah ke method controller, misalnya <code>POST /api/buku</code> -&gt; <code>BukuController::store</code>. Pintu HTTP tetap seperti di <a href="/artikel/laravel-routing-json-perpustakaan-api">Laravel Routing &amp; JSON (#56)</a> — yang berubah: isi pintu sekarang memanggil pengatur kode, bukan menumpuk semua logika di satu file route.</p>

<h2>Laravel — Service &amp; Eloquent</h2>
<p>Layanan memakai Model Eloquent untuk menulis ke tabel. Anggap Model seperti “kartu katalog” untuk satu jenis data:</p>

<pre><code class="language-php">&lt;?php
// Cuplikan Laravel — Model (pintu ke tabel buku).
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Buku extends Model
{
    protected $fillable = ['judul', 'tahun'];
}
</code></pre>

<pre><code class="language-php">&lt;?php
// Cuplikan Laravel — layanan memakai Eloquent.
namespace App\Services;

use App\Models\Buku;

class BukuService
{
    public function tambah(array $data): Buku
    {
        return Buku::create([
            'judul' =&gt; $data['judul'],
            'tahun' =&gt; (int) $data['tahun'],
        ]);
    }

    public function semua()
    {
        return Buku::query()-&gt;orderBy('id')-&gt;get();
    }
}
</code></pre>

<p><strong>Awam:</strong> <code>Buku::create(...)</code> artinya “buat baris baru di tabel buku”. <code>$fillable</code> = daftar kolom/isian yang boleh diisi lewat create (supaya tidak sembarang data ikut masuk). <code>Buku::query()-&gt;orderBy('id')-&gt;get()</code> artinya “ambil semua buku, urutkan menurut id”. Tabel sungguhan biasanya dibuat lewat migrasi (skrip pembuat tabel) — detailnya bisa dipelajari nanti; di sini cukup paham Model sebagai pintu.</p>

<h2>Pola Dasar — Controller, Service, Eloquent</h2>
<figure style="margin:1.5rem 0;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1.25rem;color:#1a1a1a" aria-label="Lima langkah controller service eloquent">
<ol style="list-style:none;padding:0;margin:0;color:#1a1a1a">
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Pastikan penjaga sudah berdiri</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Validasi dulu (lihat <a href="/artikel/laravel-request-validasi-api">Request &amp; Form Request (#57)</a>) — baru susun alur.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Buat controller tipis</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Terima request bersih, panggil layanan, kembalikan JSON + status.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Pindahkan langkah kerja ke service</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">“Tambah buku”, “cari buku”, “daftar buku” tinggal di satu tempat.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Simpan lewat Eloquent</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Model + <code>create</code> / <code>query</code> — bukan SQL panjang di controller.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">5</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Baru pikir siapa yang boleh masuk</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Berikutnya: <a href="/artikel/laravel-auth-api-dasar">Auth API Dasar (#59)</a> — otentikasi (siapa yang login) setelah alur simpan sudah rapi.</span>
    </div>
  </li>
</ol>
</figure>

<h2>Kode lengkap — demo mandiri</h2>
<p>Simpan sebagai <code>laravel_controller_service_demo.php</code>, lalu jalankan <code>php laravel_controller_service_demo.php</code>:</p>

<pre><code class="language-php">&lt;?php
declare(strict_types=1);

$rak = [];

class BukuService
{
    public function __construct(private array &amp;$rak)
    {
    }

    public function tambah(array $data): array
    {
        $id = count($this-&gt;rak) + 1;
        $buku = [
            "id" =&gt; $id,
            "judul" =&gt; (string) $data["judul"],
            "tahun" =&gt; (int) $data["tahun"],
        ];
        $this-&gt;rak[] = $buku;

        return $buku;
    }

    public function semua(): array
    {
        return $this-&gt;rak;
    }
}

function simpanBuku(array $data, BukuService $layanan): array
{
    $buku = $layanan-&gt;tambah($data);

    return ["status" =&gt; 201, "body" =&gt; ["ok" =&gt; true, "buku" =&gt; $buku]];
}

function daftarBuku(BukuService $layanan): array
{
    return ["status" =&gt; 200, "body" =&gt; ["data" =&gt; $layanan-&gt;semua()]];
}

function demo(string $judul, callable $aksi): void
{
    echo "=== {$judul} ===", PHP_EOL;
    $hasil = $aksi();
    echo "status: ", $hasil["status"], PHP_EOL;
    echo json_encode($hasil["body"], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL, PHP_EOL;
}

$layanan = new BukuService($rak);

demo("POST bersih -&gt; 201", function () use ($layanan) {
    return simpanBuku(["judul" =&gt; "Belajar PHP", "tahun" =&gt; 2024], $layanan);
});

demo("GET daftar -&gt; 200", function () use ($layanan) {
    return daftarBuku($layanan);
});
</code></pre>

<p><strong>Awam:</strong> <code>demo(...)</code> hanya membungkus output agar mudah dibaca di terminal — bukan fitur Laravel. <code>callable</code> artinya “sesuatu yang bisa dipanggil seperti fungsi”. Baris <code>declare(strict_types=1);</code> membuat tipe data lebih ketat — boleh diikuti, tidak wajib dihafal dulu.</p>

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
      <td>Controller membengkak</td>
      <td>Langkah kerja ditulis di controller</td>
      <td>Pindahkan ke service</td>
    </tr>
    <tr>
      <td>Perintah database tersebar di banyak file</td>
      <td>Eloquent/query dipanggil dari mana-mana</td>
      <td>Satukan lewat service</td>
    </tr>
    <tr>
      <td>Data kotor ikut tersimpan</td>
      <td>Melewati penjaga</td>
      <td>Pakai Form Request dulu (<a href="/artikel/laravel-request-validasi-api">Request &amp; Form Request</a>)</td>
    </tr>
    <tr>
      <td>Isian tidak tersimpan / error “kolom tidak boleh diisi massal”</td>
      <td>Kolom belum ada di <code>$fillable</code></td>
      <td>Tambahkan nama kolom ke <code>$fillable</code></td>
    </tr>
  </tbody>
</table>

<h2>Latihan singkat</h2>
<ol>
  <li>Ubah demo: tambah method <code>cari(int $id)</code> di service, lalu buat fungsi pengatur yang mengembalikan 404 jika tidak ketemu.</li>
  <li>Di cuplikan Laravel, bayangkan route <code>POST /api/buku</code> mengarah ke method <code>store</code> di <code>BukuController</code> — tulis dalam satu kalimat alur dari request sampai JSON 201.</li>
  <li>Jelaskan ke teman (tanpa jargon): beda controller vs service vs Eloquent dengan analogi loket perpustakaan.</li>
</ol>

<h2>FAQ singkat</h2>
<p><strong>Apa bedanya controller dan service?</strong><br>Controller mengatur alur (siapa dipanggil, apa yang dikembalikan). Service mengerjakan langkah kerja. Awam: manajer loket vs staf yang menyusun rak.</p>
<p><strong>Haruskah selalu pakai service?</strong><br>Untuk API kecil, kadang controller langsung ke Eloquent masih oke. Begitu aturan bertambah (cek stok, hitung denda, kirim notifikasi), service membantu tetap rapi.</p>
<p><strong>Eloquent wajib dari awal?</strong><br>Ide penyimpanan bisa dilatih dengan array dulu. Eloquent dipakai saat data perlu bertahan di database sungguhan.</p>
<p><strong>Lanjut ke mana?</strong><br>Berikutnya: <a href="/artikel/laravel-auth-api-dasar">Auth API Dasar (#59)</a> — siapa yang boleh memanggil API (login / “bukti masuk”). Setelah alur simpan rapi, baru kunci pintu.</p>

<h2>Kesimpulan</h2>
<p>Kamu sudah memisahkan peran: <strong>controller</strong> mengatur, <strong>service</strong> mengerjakan, <strong>Eloquent</strong> menyimpan/membaca. Penjaga dari <a href="/artikel/laravel-request-validasi-api">Request &amp; Form Request (#57)</a> tetap di depan — baru data bersih masuk alur ini.</p>

<blockquote>
  <p><strong>Seri 4 progress:</strong> langkah <strong>#58 (ini)</strong> · 7/8 menuju Capstone Laravel · stack Laravel <strong>3/5</strong> · prasyarat: <a href="/artikel/laravel-request-validasi-api">Request &amp; Form Request (#57)</a> LIVE. Berikutnya: <a href="/artikel/laravel-auth-api-dasar">Auth API Dasar (#59)</a>.</p>
</blockquote>
HTML;
    }
}
