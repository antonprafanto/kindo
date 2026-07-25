<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article57Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $webCat = Category::where('slug', 'web-development')->first()
            ?? Category::where('slug', 'programming')->first();

        if (! $admin || ! $webCat) {
            throw new \RuntimeException('User atau kategori web-development/programming tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'laravel-struktur-env-artisan';

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
                'title'           => 'Struktur Folder, .env & Artisan Laravel',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'Struktur Folder, .env & Artisan Laravel untuk Pemula',
                'seo_description' => 'Seri 4 #57: kenali denah folder Laravel, file .env, database SQLite dari nol, dan perintah Artisan sehari-hari — ramah awam.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['laravel', 'php', 'api', 'http', 'web'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel ke-57 berhasil dipublish: '.$article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — kenali denah toko</h2>
<p>Artikel ini adalah <strong>#57 (ini)</strong> di <strong>Seri 4: Pemrograman Web Lanjut v2</strong>. Di <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a> kamu sudah mendirikan toko: proyek <code>perpustakaan-api</code> hidup dan halaman welcome muncul. Sekarang langkah <strong>2/8</strong>: kenali <strong>denah folder</strong>, buku pengaturan <strong><code>.env</code></strong>, dan asisten perintah <strong>Artisan</strong>.</p>
<p><strong>Awam:</strong> toko sudah berdiri. Sebelum jual buku (routing/API), kamu harus tahu mana gudang, mana kasir, mana buku catatan pengaturan. Tanpa denah, kamu mudah tersesat membuka file salah.</p>
<p>Domain tetap <strong>perpustakaan mini</strong>. Kita pakai database sederhana dulu (<strong>SQLite</strong>) supaya instal dari nol tetap ringan di Windows.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> sudah selesai <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a> — folder <code>perpustakaan-api</code> ada, <code>php artisan serve</code> pernah jalan. Pakai <strong>Laravel 13+</strong> — butuh <strong>PHP 8.3+</strong>.</p>
</blockquote>

<h2>Spesifikasi fitur — apa yang kita kuasai?</h2>
<p>Daftar singkat yang bisa kamu centang di akhir artikel:</p>
<ol>
  <li>Mengenal folder penting: <code>app</code>, <code>routes</code>, <code>database</code>, <code>config</code>, <code>public</code>, <code>.env</code>.</li>
  <li>Menyalin <code>.env.example</code> ke <code>.env</code> (jika belum ada) dan menjalankan <code>php artisan key:generate</code>.</li>
  <li>Menyiapkan database <strong>SQLite dari nol</strong> lalu <code>php artisan migrate</code>.</li>
  <li>Memakai Artisan sehari-hari: <code>list</code>, <code>migrate</code>, <code>serve</code>.</li>
</ol>
<p><strong>Awam:</strong> urutan nyaman: <strong>denah folder -&gt; .env -&gt; database -&gt; Artisan</strong>. Belum perlu menulis route API di sini.</p>

<h2>Istilah — ringkas untuk denah &amp; pengaturan</h2>
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
      <td>Struktur folder</td>
      <td>Denah ruangan di dalam proyek</td>
      <td><code>app/</code>, <code>routes/</code>, <code>database/</code></td>
    </tr>
    <tr>
      <td><code>.env</code></td>
      <td>Buku catatan pengaturan rahasia mesinmu</td>
      <td><code>APP_NAME</code>, <code>DB_CONNECTION</code></td>
    </tr>
    <tr>
      <td>Artisan</td>
      <td>Asisten perintah di terminal dalam proyek</td>
      <td><code>php artisan migrate</code></td>
    </tr>
    <tr>
      <td>Migrasi</td>
      <td>Cetak biru tabel database yang dijalankan Artisan</td>
      <td><code>php artisan migrate</code></td>
    </tr>
    <tr>
      <td>SQLite</td>
      <td>Database satu file — cocok belajar di laptop</td>
      <td><code>database/database.sqlite</code></td>
    </tr>
  </tbody>
</table>
<p>Urutan belajar: <strong>baca denah -&gt; isi pengaturan -&gt; siapkan database -&gt; coba perintah Artisan</strong>.</p>

<h2>Kenapa denah dulu?</h2>
<p>Laravel punya banyak folder. Kalau kamu langsung ubah sembarang file, error-nya sulit dilacak. Dengan denah, kamu tahu: kode aplikasi di <code>app</code>, pintu HTTP di <code>routes</code>, pengaturan di <code>.env</code>, dan data di <code>database</code>.</p>
<p><strong>Awam:</strong> sama seperti perpustakaan — sebelum menata buku, petugas harus tahu mana ruang katalog, mana gudang, mana loket. Denah = peta kerja.</p>
<p>Artikel ini tetap <strong>install-dari-nol</strong> untuk bagian database: kita pakai SQLite bawaan supaya tidak wajib pasang MySQL dulu. Fondasi PHP/Composer/Laravel sudah di <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a>.</p>

<h2>Alur — dari denah sampai database siap</h2>
<figure role="img" aria-label="Alur kenali struktur Laravel: folder, .env, database, Artisan" style="background:#F5F5F0;border:2px solid #1a1a1a;border-radius:12px;padding:1rem;margin:1.25rem 0">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 220" width="100%" height="auto" role="img" aria-label="Alur denah folder ke Artisan">
  <defs>
    <marker id="laravel57structArrow" orient="auto" markerWidth="8" markerHeight="8" refX="7" refY="4" viewBox="0 0 8 8">
      <path d="M0,0 L8,4 L0,8 Z" fill="#1a1a1a"/>
    </marker>
  </defs>
  <rect x="16" y="28" width="140" height="64" rx="10" fill="#2979FF"/>
  <text x="86" y="56" text-anchor="middle" fill="#fff" font-size="15" font-weight="700">Folder</text>
  <text x="86" y="76" text-anchor="middle" fill="#fff" font-size="12">denah proyek</text>
  <line x1="156" y1="60" x2="226" y2="60" stroke="#1a1a1a" stroke-width="3" marker-end="url(#laravel57structArrow)"/>
  <rect x="234" y="28" width="140" height="64" rx="10" fill="#00897B"/>
  <text x="304" y="56" text-anchor="middle" fill="#fff" font-size="15" font-weight="700">.env</text>
  <text x="304" y="76" text-anchor="middle" fill="#fff" font-size="12">pengaturan</text>
  <line x1="374" y1="60" x2="444" y2="60" stroke="#1a1a1a" stroke-width="3" marker-end="url(#laravel57structArrow)"/>
  <rect x="452" y="28" width="140" height="64" rx="10" fill="#F9A825"/>
  <text x="522" y="56" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">SQLite</text>
  <text x="522" y="76" text-anchor="middle" fill="#1a1a1a" font-size="12">+ migrate</text>
  <line x1="592" y1="60" x2="630" y2="60" stroke="#1a1a1a" stroke-width="3" marker-end="url(#laravel57structArrow)"/>
  <line x1="630" y1="92" x2="630" y2="120" stroke="#1a1a1a" stroke-width="3" marker-end="url(#laravel57structArrow)"/>
  <rect x="560" y="120" width="140" height="64" rx="10" fill="#1a1a1a"/>
  <text x="630" y="148" text-anchor="middle" fill="#fff" font-size="15" font-weight="700">Artisan</text>
  <text x="630" y="168" text-anchor="middle" fill="#fff" font-size="12">serve / list</text>
  <text x="24" y="150" fill="#1a1a1a" font-size="13">Setelah instalasi: masuk folder perpustakaan-api, kenali denah, isi .env, buat file sqlite, migrate.</text>
  <text x="24" y="172" fill="#1a1a1a" font-size="13">Baru setelah ini nyaman lanjut ke routing &amp; JSON.</text>
</svg>
<figcaption style="color:#1a1a1a;margin-top:.5rem"><strong>#57 (ini)</strong>: denah folder -&gt; <code>.env</code> -&gt; database SQLite -&gt; Artisan.</figcaption>
</figure>

<h2>Struktur folder — denah singkat</h2>
<p>Buka folder <code>perpustakaan-api</code>. Fokus dulu ke yang sering dipakai:</p>
<table>
  <thead>
    <tr>
      <th>Folder / file</th>
      <th>Peran awam</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><code>app/</code></td>
      <td>Isi otak aplikasi — kode inti (class) nanti dikumpulkan di sini</td>
    </tr>
    <tr>
      <td><code>routes/</code></td>
      <td>Daftar pintu lalu-lintas web (HTTP) — web &amp; API</td>
    </tr>
    <tr>
      <td><code>database/</code></td>
      <td>Migrasi, seeder, dan file SQLite</td>
    </tr>
    <tr>
      <td><code>config/</code></td>
      <td>Pengaturan kerangka (jarang disentuh awam di awal)</td>
    </tr>
    <tr>
      <td><code>public/</code></td>
      <td>Pintu depan yang dilayani saat kamu buka situs di browser</td>
    </tr>
    <tr>
      <td><code>.env</code></td>
      <td>Pengaturan lokal mesinmu (jangan diunggah sembarangan)</td>
    </tr>
  </tbody>
</table>
<p><strong>Awam:</strong> belum hafal semua folder? Tidak apa-apa. Yang wajib diingat sekarang: <code>app</code>, <code>routes</code>, <code>database</code>, <code>.env</code>.</p>

<h2>File .env — buku pengaturan</h2>
<p>Kalau belum ada <code>.env</code>, salin dari contoh:</p>
<pre><code class="language-bash">copy .env.example .env
php artisan key:generate
</code></pre>
<p>Di macOS/Linux: <code>cp .env.example .env</code> lalu <code>php artisan key:generate</code>.</p>
<p>Beberapa baris yang sering dilihat awam:</p>
<pre><code class="language-php">&lt;?php
// Simulasi isi .env — bukan file PHP sungguhan; hanya contoh teks.
echo "APP_NAME=PerpustakaanApi", PHP_EOL;
echo "APP_ENV=local", PHP_EOL;
echo "APP_KEY=base64:...(diisi artisan key:generate)", PHP_EOL;
echo "DB_CONNECTION=sqlite", PHP_EOL;
</code></pre>
<p><strong>Awam:</strong> <code>.env</code> seperti label di belakang toko: nama toko, mode latihan (<code>local</code>), dan jenis buku besar (database). <code>APP_KEY</code> digenerate Artisan — jangan dikarang manual.</p>

<h2>Database dari nol — SQLite dulu</h2>
<p>Untuk belajar di Windows tanpa memasang MySQL dulu, pakai <strong>SQLite</strong> (satu file).</p>
<ol>
  <li>Pastikan di <code>.env</code>: <code>DB_CONNECTION=sqlite</code>.</li>
  <li>Buat file kosong: <code>database/database.sqlite</code>.
    <ul>
      <li><strong>Windows (Explorer):</strong> masuk folder <code>database</code>, buat Text File baru, namai <code>database.sqlite</code> (boleh kosong).</li>
      <li><strong>Windows (terminal):</strong> <code>type nul &gt; database\database.sqlite</code></li>
    </ul>
  </li>
  <li>Jalankan migrasi:</li>
</ol>
<pre><code class="language-bash">php artisan migrate
</code></pre>
<p>Perintah ini membuat tabel dasar Laravel di file SQLite. Kalau sukses, terminal biasanya menampilkan daftar migrasi yang dijalankan.</p>
<p><strong>Awam:</strong> SQLite = buku besar satu map. Cukup untuk belajar denah. Nanti kalau butuh MySQL di Laragon, cukup ubah <code>DB_*</code> di <code>.env</code> — tidak wajib hari ini.</p>
<p><strong>Install-dari-nol:</strong> kamu tidak perlu unduh driver ekstra untuk SQLite di jalur Laravel modern; cukup file <code>.sqlite</code> + <code>migrate</code>. Fondasi proyek tetap merujuk <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a>.</p>

<h2>Artisan — asisten perintah sehari-hari</h2>
<p>Jalankan di dalam folder <code>perpustakaan-api</code>:</p>
<pre><code class="language-bash">php artisan list
php artisan --version
php artisan migrate
php artisan serve
</code></pre>
<p>Contoh bentuk output versi (simulasi):</p>
<pre><code class="language-php">&lt;?php
echo "Laravel Framework 13.0.0", PHP_EOL;
</code></pre>
<p><strong>Awam:</strong> <code>artisan list</code> seperti menu remote TV — kamu melihat perintah yang tersedia tanpa hafal semua. <code>serve</code> menyalakan lampu toko sementara.</p>

<h2>Pola Dasar — empat langkah denah bersih</h2>
<figure style="background:#F5F5F0;border:2px solid #1a1a1a;border-radius:12px;padding:1.25rem;margin:1.25rem 0">
<ol style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:.85rem">
  <li style="display:flex;gap:.75rem;align-items:flex-start;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div><strong style="color:#1a1a1a">Buka denah</strong><br><span style="color:#1a1a1a">Kenali <code>app</code>, <code>routes</code>, <code>database</code>, <code>.env</code> di <code>perpustakaan-api</code>.</span></div>
  </li>
  <li style="display:flex;gap:.75rem;align-items:flex-start;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div><strong style="color:#1a1a1a">Siapkan .env</strong><br><span style="color:#1a1a1a">Salin dari <code>.env.example</code> bila perlu, lalu <code>php artisan key:generate</code>.</span></div>
  </li>
  <li style="display:flex;gap:.75rem;align-items:flex-start;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div><strong style="color:#1a1a1a">Database SQLite</strong><br><span style="color:#1a1a1a">Buat <code>database/database.sqlite</code>, set <code>DB_CONNECTION=sqlite</code>, jalankan <code>migrate</code>.</span></div>
  </li>
  <li style="display:flex;gap:.75rem;align-items:flex-start;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div><strong style="color:#1a1a1a">Cek Artisan</strong><br><span style="color:#1a1a1a"><code>php artisan list</code> dan <code>php artisan serve</code> — pastikan proyek masih hidup.</span></div>
  </li>
</ol>
</figure>

<h2>Demo peta folder — file mandiri</h2>
<p>Simpan sebagai <code>laravel_struktur_env_artisan_demo.php</code>, lalu jalankan <code>php laravel_struktur_env_artisan_demo.php</code>. File ini <strong>mensimulasikan</strong> denah &amp; pengaturan — tidak mengubah proyek Laravel-mu:</p>
<pre><code class="language-php">&lt;?php
declare(strict_types=1);

/**
 * Demo peta folder &amp; .env — simulasi teks untuk awam.
 */

function petaFolder(): array
{
    return [
        'app' =&gt; 'otak aplikasi',
        'routes' =&gt; 'daftar pintu HTTP',
        'database' =&gt; 'migrasi &amp; sqlite',
        '.env' =&gt; 'pengaturan lokal',
    ];
}

function contohEnv(): array
{
    return [
        'APP_NAME' =&gt; 'PerpustakaanApi',
        'DB_CONNECTION' =&gt; 'sqlite',
        'APP_ENV' =&gt; 'local',
    ];
}

function demo(): void
{
    echo "=== Demo denah Laravel ===", PHP_EOL;
    foreach (petaFolder() as $nama =&gt; $peran) {
        echo "- {$nama}: {$peran}", PHP_EOL;
    }

    echo PHP_EOL, "=== Contoh pengaturan .env ===", PHP_EOL;
    foreach (contohEnv() as $kunci =&gt; $nilai) {
        echo "{$kunci}={$nilai}", PHP_EOL;
    }

    echo PHP_EOL, "Langkah berikutnya sungguhan: key:generate -&gt; buat sqlite -&gt; migrate -&gt; serve.", PHP_EOL;
}

demo();
</code></pre>
<p><strong>Awam:</strong> <code>demo()</code> hanya menampilkan peta di terminal. Setelah paham, kerjakan langkah yang sama di folder <code>perpustakaan-api</code> sungguhan. <code>declare(strict_types=1);</code> membuat tipe lebih ketat — boleh diikuti, tidak wajib dihafal.</p>

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
      <td><code>.env</code> tidak ketemu</td>
      <td>Belum disalin dari <code>.env.example</code></td>
      <td><code>copy .env.example .env</code> lalu <code>key:generate</code></td>
    </tr>
    <tr>
      <td>Migrate gagal / database error</td>
      <td>File <code>database.sqlite</code> belum dibuat</td>
      <td>Buat file kosong di <code>database/database.sqlite</code></td>
    </tr>
    <tr>
      <td>Artisan “could not open input file”</td>
      <td>Belum <code>cd</code> ke folder proyek</td>
      <td>Masuk ke <code>perpustakaan-api</code> dulu</td>
    </tr>
    <tr>
      <td>Bingung beda <code>config/</code> dan <code>.env</code></td>
      <td>Membuka terlalu banyak file sekaligus</td>
      <td>Untuk awam: ubah <code>.env</code> dulu; <code>config/</code> biarkan</td>
    </tr>
  </tbody>
</table>

<h2>Latihan</h2>
<ol>
  <li>Jalankan demo PHP di atas, lalu buka Explorer ke folder <code>perpustakaan-api</code> — cocokkan nama folder dengan peta.</li>
  <li>Pastikan <code>.env</code> ada, jalankan <code>php artisan key:generate</code>, buat <code>database.sqlite</code>, lalu <code>php artisan migrate</code>.</li>
  <li>Jelaskan ke teman: beda singkat <code>app/</code>, <code>routes/</code>, dan <code>.env</code> dengan bahasa toko/perpustakaan.</li>
</ol>

<h2>FAQ</h2>
<p><strong>Harus MySQL dari awal?</strong><br>
Tidak. SQLite cukup untuk langkah denah ini. MySQL di Laragon boleh belakangan saat data makin besar.</p>
<p><strong>Bagaimana membuat file database.sqlite di Windows?</strong><br>
Paling mudah lewat Explorer: folder <code>database</code>, buat file baru bernama <code>database.sqlite</code> (isi boleh kosong). Atau di terminal proyek: <code>type nul &gt; database\database.sqlite</code>.</p>
<p><strong>Boleh mengedit banyak file di config/?</strong><br>
Untuk pemula: tahan dulu. Kebanyakan pengaturan harian cukup lewat <code>.env</code>.</p>
<p><strong>Apa hubungan dengan instalasi (#56)?</strong><br>
<a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a> mendirikan proyek. <strong>#57 (ini)</strong> mengajakmu mengenali isi rumahnya.</p>
<p><strong>Ke mana setelah ini?</strong><br>
Berikutnya kamu akan belajar <strong>routing</strong> dan jawaban <strong>JSON</strong> — membuka pintu HTTP untuk API perpustakaan mini.</p>

<h2>Kesimpulan</h2>
<p>Kamu sudah punya denah kerja Laravel: folder penting, file <code>.env</code>, database SQLite dari nol, dan Artisan untuk <code>migrate</code>/<code>serve</code>/<code>list</code>. Ini langkah <strong>2/8</strong> jalur Laravel di Seri 4.</p>
<blockquote>
  <p><strong>Seri 4 progress:</strong> langkah <strong>#57 (ini)</strong> · <strong>2/8</strong> jalur Laravel · prasyarat: <a href="/artikel/laravel-instalasi-proyek-pertama">Instal PHP, Composer &amp; Proyek Laravel (#56)</a> LIVE. Berikutnya: routing &amp; jawaban JSON untuk API perpustakaan.</p>
</blockquote>
HTML;
    }
}
