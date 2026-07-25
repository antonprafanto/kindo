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

        $slug = 'laravel-instalasi-proyek-pertama';

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
                'title'           => 'Instal PHP, Composer & Proyek Laravel Pertama',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'Instal PHP, Composer & Proyek Laravel Pertama',
                'seo_description' => 'Seri 4 #56: dari nol pasang PHP 8.3+ dan Composer di Windows, buat proyek Laravel 13 perpustakaan-api, cek versi, dan jalankan halaman welcome — ramah awam.',
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
<h2>Pendahuluan — mendirikan toko sebelum jual buku</h2>
<p>Artikel ini adalah <strong>#56 (ini)</strong> di <strong>Seri 4: Pemrograman Web Lanjut v2</strong>. Setelah jembatan OOP PHP dari <a href="/artikel/mengenal-oop-cara-berpikir-dengan-objek-php">Mengenal OOP (#53)</a> hingga <a href="/artikel/oop-php-visibility-composition">Visibility (#55)</a>, kamu mulai jalur <strong>Laravel</strong> — langkah <strong>1/8</strong>.</p>
<p>Di <a href="/artikel/oop-php-visibility-composition">Visibility &amp; Composition (#55)</a> kamu sudah punya object <code>Buku</code> dan <code>Katalog</code> di PHP biasa. Sekarang kita siapkan <strong>rumah kerja Laravel</strong>: PHP, Composer, lalu proyek baru bernama <code>perpustakaan-api</code> (nama awam — nanti dipakai untuk API perpustakaan mini).</p>
<p><strong>Awam:</strong> bayangkan mau jual buku di pasar. Sebelum display buku, kamu butuh <strong>mendirikan toko dulu</strong> — atap, meja, kasir. PHP + Composer + proyek Laravel = toko itu. Baru setelahnya kita isi rak buku (routing, data, API).</p>
<p>Jika class/object masih baru, singgah sebentar ke <a href="/artikel/oop-php-property-method-constructor">Property, Method &amp; Constructor (#54)</a> — fondasi OOP ringan sebelum masuk framework.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> sudah baca <a href="/artikel/oop-php-visibility-composition">Visibility &amp; Composition (#55)</a> — paham class/object ringan. Domain tetap <strong>perpustakaan mini</strong>. Pakai <strong>Laravel 13+</strong> (versi terbaru resmi) — butuh <strong>PHP 8.3+</strong>.</p>
</blockquote>

<h2>Spesifikasi fitur — apa yang kita pasang?</h2>
<p>Daftar singkat yang bisa kamu centang di akhir artikel:</p>
<ol>
  <li><strong>PHP 8.3+</strong> jalan di terminal (<code>php -v</code>) — syarat Laravel 13.</li>
  <li><strong>Composer</strong> terpasang (<code>composer -V</code>).</li>
  <li><strong>Proyek Laravel baru</strong> lewat <code>composer create-project</code> — folder <code>perpustakaan-api</code>.</li>
  <li><strong>Artisan hidup</strong> (<code>php artisan --version</code>) dan server lokal (<code>php artisan serve</code>) menampilkan halaman welcome.</li>
</ol>
<p><strong>Awam:</strong> urutan nyaman: <strong>PHP dulu -&gt; Composer -&gt; buat proyek -&gt; cek -&gt; jalankan server</strong>. Jangan loncat ke kode API sebelum toko berdiri.</p>

<h2>Istilah — ringkas untuk instalasi</h2>
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
      <td>PHP</td>
      <td>Bahasa yang menjalankan kode di server/komputermu</td>
      <td><code>php -v</code></td>
    </tr>
    <tr>
      <td>Composer</td>
      <td>Pengelola paket — seperti aplikasi pasar untuk library PHP</td>
      <td><code>composer -V</code></td>
    </tr>
    <tr>
      <td>create-project</td>
      <td>Perintah Composer untuk mengunduh kerangka proyek siap pakai</td>
      <td><code>laravel/laravel</code></td>
    </tr>
    <tr>
      <td>Artisan</td>
      <td>Asisten perintah bawaan Laravel di dalam proyek</td>
      <td><code>php artisan serve</code></td>
    </tr>
    <tr>
      <td>Proyek</td>
      <td>Folder kerja berisi kode Laravel kamu</td>
      <td><code>perpustakaan-api</code></td>
    </tr>
  </tbody>
</table>
<p>Urutan belajar: <strong>pasang alat -&gt; buat folder proyek -&gt; cek versi -&gt; hidupkan server lokal</strong>.</p>

<h2>Kenapa instal dulu?</h2>
<p>Laravel bukan satu file yang bisa disalin sembarang. Ia butuh PHP versi cukup baru, banyak file pendukung, dan Composer untuk mengunduhnya rapi. Kalau kamu langsung membuka tutorial routing tanpa PHP/Composer, error-nya sering membingungkan: “command not found”, “Class not found”, atau versi PHP terlalu tua.</p>
<p><strong>Awam:</strong> sama seperti perpustakaan — sebelum menerima peminjaman, pastikan loket, buku besar, dan stempel sudah ada. Instalasi = menyiapkan loket. API perpustakaan datang setelah fondasi ini.</p>
<p>Artikel ini sengaja <strong>install-dari-nol</strong>: setiap langkah bisa diikuti di komputer Windows awam (Laragon/XAMPP) tanpa asumsi Laravel sudah terpasang.</p>

<h2>Alur instal — dari nol sampai proyek hidup</h2>
<figure role="img" aria-label="Diagram alur instal PHP Composer dan proyek Laravel" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" style="display:block;max-width:760px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif" viewBox="0 0 760 260">
  <defs>
    <marker id="laravel56installArrow" orient="auto" markerWidth="8" markerHeight="8" refX="7" refY="4" viewBox="0 0 8 8">
      <path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/>
    </marker>
  </defs>
  <rect width="760" height="260" fill="#F5F5F0"/>
  <text x="24" y="36" fill="#1a1a1a" font-size="16" font-weight="700">Instal: PHP -&gt; Composer -&gt; create-project -&gt; cek -&gt; artisan serve</text>
  <rect x="24" y="70" width="120" height="80" rx="8" fill="#fff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="84" y="105" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">PHP</text>
  <text x="84" y="128" text-anchor="middle" fill="#1a1a1a" font-size="12">php -v</text>
  <line x1="144" y1="110" x2="184" y2="110" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel56installArrow)"/>
  <rect x="188" y="70" width="130" height="80" rx="8" fill="#fff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="253" y="105" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Composer</text>
  <text x="253" y="128" text-anchor="middle" fill="#1a1a1a" font-size="12">composer -V</text>
  <line x1="318" y1="110" x2="358" y2="110" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel56installArrow)"/>
  <rect x="362" y="70" width="150" height="80" rx="8" fill="#1a1a1a" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="437" y="105" text-anchor="middle" fill="#fff" font-size="15" font-weight="700">create-project</text>
  <text x="437" y="128" text-anchor="middle" fill="#fff" font-size="12">perpustakaan-api</text>
  <line x1="512" y1="110" x2="552" y2="110" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel56installArrow)"/>
  <rect x="556" y="70" width="90" height="80" rx="8" fill="#fff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="601" y="105" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Cek</text>
  <text x="601" y="128" text-anchor="middle" fill="#1a1a1a" font-size="12">artisan -V</text>
  <line x1="646" y1="110" x2="686" y2="110" stroke="#2979FF" stroke-width="3" marker-end="url(#laravel56installArrow)"/>
  <rect x="690" y="70" width="50" height="80" rx="8" fill="#fff" stroke="#1a1a1a" stroke-width="2.5"/>
  <text x="715" y="115" text-anchor="middle" fill="#1a1a1a" font-size="13" font-weight="700">serve</text>
  <text x="24" y="190" fill="#1a1a1a" font-size="13">Jalur utama Windows: Laragon atau XAMPP sudah menyertakan PHP — tinggal pasang Composer.</text>
  <text x="24" y="220" fill="#1a1a1a" font-size="13">Proyek perpustakaan-api akan dipakai untuk API perpustakaan mini di artikel berikutnya.</text>
</svg>
<figcaption>Alur <strong>#56 (ini)</strong>: siapkan alat, lahirkan proyek, cek versi, hidupkan server lokal.</figcaption>
</figure>

<h2>Laragon (atau XAMPP) — PHP di Windows</h2>
<p><strong>Jalur utama Windows:</strong> pasang <strong>Laragon</strong> (disarankan) atau <strong>XAMPP</strong>. Keduanya sudah menyertakan PHP — kamu tidak perlu mengunduh PHP terpisah untuk langkah awal.</p>
<ol>
  <li>Unduh Laragon dari situs resminya, instal seperti aplikasi biasa.</li>
  <li>Buka Laragon, klik <strong>Start All</strong> (Apache/MySQL boleh hidup — untuk Laravel kita fokus PHP dulu).</li>
  <li>Buka terminal Laragon (menu <em>Terminal</em>) lalu ketik:</li>
</ol>
<pre><code class="language-bash">php -v
</code></pre>
<p>Output yang sehat menampilkan <strong>PHP 8.3</strong> atau lebih baru (Laravel 13 tidak menerima PHP 8.2). Contoh bentuknya:</p>
<pre><code class="language-php">&lt;?php
// Simulasi output php -v (bukan perintah sungguhan — hanya bentuk teks).
echo "PHP 8.3.12 (cli) (built: Oct 24 2024 00:00:00)", PHP_EOL;
echo "Copyright (c) The PHP Group", PHP_EOL;
</code></pre>
<p><strong>Awam:</strong> kalau terminal bilang <code>php</code> tidak dikenali, PHP belum masuk PATH — restart Laragon atau buka terminal dari menu Laragon, bukan CMD acak.</p>

<h2>Composer — pengelola paket PHP</h2>
<p>Setelah PHP hidup, pasang <strong>Composer</strong> dari <a href="https://getcomposer.org/download/" rel="noopener noreferrer">getcomposer.org</a> (installer Windows). Composer mengunduh Laravel dan dependensinya ke folder proyek.</p>
<pre><code class="language-bash">composer -V
</code></pre>
<p>Contoh output yang kamu harapkan:</p>
<pre><code class="language-php">&lt;?php
// Simulasi output composer -V.
echo "Composer version 2.8.3 2024-11-12 15:00:00", PHP_EOL;
</code></pre>
<p><strong>Awam:</strong> Composer seperti kurir yang mengantar rak, label, dan perlengkapan toko Laravel — kamu tidak mengunduh ribuan file manual.</p>

<h2>create-project — lahirkan proyek Laravel</h2>
<p>Pilih folder kerja (misalnya <code>C:\laragon\www</code> di Laragon). Lalu jalankan:</p>
<pre><code class="language-bash">composer create-project laravel/laravel perpustakaan-api
</code></pre>
<p>Perintah ini membuat folder <code>perpustakaan-api</code> berisi kerangka <strong>Laravel 13+</strong> (yang diunduh Composer saat ini). Proses bisa beberapa menit — normal. Nama <code>perpustakaan-api</code> mengingatkan bahwa proyek ini nanti dipakai untuk <strong>API perpustakaan mini</strong>.</p>
<p>Masuk ke folder proyek:</p>
<pre><code class="language-bash">cd perpustakaan-api
</code></pre>
<p><strong>Awam:</strong> <code>create-project</code> = paket “toko siap bangun”. Isi rak buku (route, model) kita tambah di artikel berikutnya.</p>

<h2>Cek versi &amp; artisan serve</h2>
<p>Tiga cek cepat sebelum lanjut:</p>
<pre><code class="language-bash">php -v
composer -V
php artisan --version
</code></pre>
<p>Contoh output Artisan (bentuk mirip):</p>
<pre><code class="language-php">&lt;?php
// Simulasi output php artisan --version.
echo "Laravel Framework 13.0.0", PHP_EOL;
</code></pre>
<p>Hidupkan server pengembangan bawaan:</p>
<pre><code class="language-bash">php artisan serve
</code></pre>
<p>Buka browser ke alamat yang muncul (biasanya <code>http://127.0.0.1:8000</code>). Kamu harus melihat <strong>halaman welcome Laravel</strong> — artinya proyek hidup.</p>
<p><strong>Awam:</strong> <code>artisan serve</code> = menyalakan lampu toko sementara di komputermu. Matikan dengan <code>Ctrl+C</code> di terminal.</p>

<h2>Alternatif singkat — PHP di PATH + Composer resmi</h2>
<p>Jika tidak memakai Laragon/XAMPP: unduh PHP zip dari php.net, ekstrak, tambahkan folder PHP ke <strong>PATH</strong> Windows, lalu pasang Composer installer. Setelah <code>php -v</code> dan <code>composer -V</code> sama-sama jalan, langkah <code>create-project</code> dan <code>artisan serve</code> identik dengan atas.</p>

<h2>Pola Dasar — enam langkah instal bersih</h2>
<figure style="margin:1.5rem 0;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1.25rem;color:#1a1a1a" aria-label="Enam langkah instal PHP Composer Laravel">
<ol style="list-style:none;padding:0;margin:0;color:#1a1a1a">
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div><strong style="color:#1a1a1a">Pasang PHP</strong><br><span style="color:#1a1a1a">Laragon/XAMPP atau PHP manual — pastikan <code>php -v</code> menampilkan 8.3+.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div><strong style="color:#1a1a1a">Pasang Composer</strong><br><span style="color:#1a1a1a">Installer resmi — cek <code>composer -V</code> di terminal yang sama dengan PHP.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div><strong style="color:#1a1a1a">Buat proyek</strong><br><span style="color:#1a1a1a"><code>composer create-project laravel/laravel perpustakaan-api</code> di folder kerja.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div><strong style="color:#1a1a1a">Masuk folder &amp; cek Artisan</strong><br><span style="color:#1a1a1a"><code>cd perpustakaan-api</code> lalu <code>php artisan --version</code>.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">5</span>
    <div><strong style="color:#1a1a1a">Jalankan server</strong><br><span style="color:#1a1a1a"><code>php artisan serve</code> — buka halaman welcome di browser.</span></div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">6</span>
    <div><strong style="color:#1a1a1a">Catat lokasi proyek</strong><br><span style="color:#1a1a1a">Folder ini dipakai lagi untuk struktur folder, file <code>.env</code>, dan perintah Artisan di langkah berikutnya.</span></div>
  </li>
</ol>
</figure>

<h2>Demo cek versi — file mandiri</h2>
<p>Simpan sebagai <code>laravel_instalasi_proyek_pertama_demo.php</code>, lalu jalankan <code>php laravel_instalasi_proyek_pertama_demo.php</code>. File ini <strong>mensimulasikan</strong> output cek versi — tidak butuh Laravel terpasang:</p>

<pre><code class="language-php">&lt;?php
declare(strict_types=1);

/**
 * Demo cek versi instalasi — simulasi string output.
 */

function cekVersi(string $label, string $perintah, string $outputSimulasi, bool $lulus): array
{
    return [
        "label" =&gt; $label,
        "perintah" =&gt; $perintah,
        "output" =&gt; $outputSimulasi,
        "lulus" =&gt; $lulus,
    ];
}

function demo(): void
{
    $cek = [
        cekVersi("PHP", "php -v", "PHP 8.3.12 (cli)", true),
        cekVersi("Composer", "composer -V", "Composer version 2.8.3", true),
        cekVersi("Artisan", "php artisan --version", "Laravel Framework 13.0.0", true),
        cekVersi("Server", "php artisan serve", "Server running on http://127.0.0.1:8000", true),
    ];

    echo "=== Demo cek instal Laravel ===", PHP_EOL;
    foreach ($cek as $baris) {
        $status = $baris["lulus"] ? "OK" : "GAGAL";
        echo "[{$status}] {$baris["label"]}: {$baris["perintah"]}", PHP_EOL;
        echo "  -&gt; {$baris["output"]}", PHP_EOL;
    }
    echo PHP_EOL, "Semua cek simulasi lulus — siap lanjut ke struktur proyek.", PHP_EOL;
}

demo();
</code></pre>
<p><strong>Awam:</strong> <code>demo()</code> hanya menampilkan contoh teks yang kamu harapkan dari terminal sungguhan. <code>declare(strict_types=1);</code> membuat tipe lebih ketat — boleh diikuti, tidak wajib dihafal. Setelah instal nyata, bandingkan output-mu dengan baris di atas.</p>

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
      <td><code>php</code> tidak dikenali</td>
      <td>PHP belum di PATH atau terminal salah</td>
      <td>Buka terminal dari Laragon; restart setelah instal</td>
    </tr>
    <tr>
      <td><code>composer</code> tidak dikenali</td>
      <td>Composer belum terpasang / PATH belum refresh</td>
      <td>Instal ulang Composer; tutup-buka terminal</td>
    </tr>
    <tr>
      <td><code>create-project</code> gagal / lambat</td>
      <td>Jaringan atau folder tanpa izin tulis</td>
      <td>Coba folder lain (mis. <code>www</code> Laragon); cek internet</td>
    </tr>
    <tr>
      <td><code>artisan</code> error “could not open input file”</td>
      <td>Belum <code>cd</code> ke folder proyek</td>
      <td>Masuk ke <code>perpustakaan-api</code> dulu</td>
    </tr>
    <tr>
      <td>Browser kosong / tidak bisa buka</td>
      <td>Server belum jalan atau port bentrok</td>
      <td>Pastikan <code>php artisan serve</code> masih aktif; coba port lain</td>
    </tr>
  </tbody>
</table>

<h2>Latihan</h2>
<ol>
  <li>Jalankan demo PHP di atas, lalu jalankan <code>php -v</code> sungguhan — bandingkan baris versinya.</li>
  <li>Buat proyek dengan nama lain (mis. <code>perpustakaan-coba</code>) dan pastikan halaman welcome muncul.</li>
  <li>Jelaskan ke teman analogi “mendirikan toko sebelum jual buku” untuk instal Laravel.</li>
</ol>

<h2>FAQ</h2>
<p><strong>Harus pakai Laragon?</strong><br>
Tidak wajib. Laragon/XAMPP hanya jalur paling nyaman di Windows. Yang wajib: <strong>PHP 8.3+</strong> dan Composer jalan di terminal yang sama (syarat Laravel 13).</p>
<p><strong>Kenapa bukan Laravel 11 lagi?</strong><br>
Versi terbaru resmi saat artikel ini ditulis adalah <strong>Laravel 13</strong> — butuh PHP 8.3 sampai 8.5. Kalau <code>php -v</code> masih 8.2, upgrade PHP di Laragon dulu sebelum <code>create-project</code>.</p>
<p><strong>Boleh pakai nama folder selain perpustakaan-api?</strong><br>
Boleh. Nama itu hanya pengingat domain perpustakaan — yang penting kamu tahu lokasi foldernya.</p>
<p><strong>Ke mana setelah ini?</strong><br>
Berikutnya kamu akan kenali <strong>struktur folder</strong> proyek, file <strong><code>.env</code></strong> untuk pengaturan, dan perintah <strong>Artisan</strong> sehari-hari — fondasi sebelum routing JSON dan API perpustakaan.</p>

<h2>Kesimpulan</h2>
<p>Kamu sudah menyiapkan fondasi Laravel dari nol: <strong>PHP</strong>, <strong>Composer</strong>, proyek <code>perpustakaan-api</code> lewat <code>create-project</code>, cek <code>artisan --version</code>, dan <code>php artisan serve</code> dengan halaman welcome. Ini langkah <strong>1/8</strong> jalur Laravel di Seri 4.</p>
<blockquote>
  <p><strong>Seri 4 progress:</strong> langkah <strong>#56 (ini)</strong> · <strong>1/8</strong> jalur Laravel · prasyarat: <a href="/artikel/oop-php-visibility-composition">Visibility &amp; Composition (#55)</a> LIVE. Berikutnya: struktur folder, file <code>.env</code>, database, dan Artisan.</p>
</blockquote>
HTML;
    }
}
