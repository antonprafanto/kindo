<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article41Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $progCat = Category::where('slug', 'programming')->first();

        if (! $admin || ! $progCat) {
            throw new \RuntimeException('User atau kategori programming tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'class-dan-object-pertama-python';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        foreach ([
            'oop' => 'oop',
            'oop-class' => 'oop-class',
            'python' => 'python',
        ] as $tagSlug => $tagName) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagName]);
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $progCat->id,
                'title'           => 'Class dan Object Pertama di Python',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'Class dan Object Pertama di Python — Tutorial OOP Pemula',
                'seo_description' => 'Tulis class Buku pertama, buat instance, pahami identitas objek dengan id() — lanjutan Seri 3 OOP berbahasa Indonesia.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['python', 'oop', 'oop-class'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel ke-41 berhasil dipublish: '.$article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — dari mental model ke sintaks</h2>
<p>Di <a href="/artikel/mengenal-oop-cara-berpikir-dengan-objek-python">Mengenal OOP (#40)</a> kamu sudah punya cara berpikir: class = cetakan, object = barang konkret. Artikel ini mengubah itu menjadi <strong>kode Python yang bisa dijalankan</strong>.</p>

<p>Fokus hari ini sempit dan tajam: menulis <code>class Buku</code>, membuat beberapa instance, dan melihat bahwa tiap object punya <strong>identitas sendiri</strong>.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> Selesai <a href="/artikel/mengenal-oop-cara-berpikir-dengan-objek-python">#40</a> (atau setidaknya paham beda class vs object). Python 3.11+ sudah terpasang (<code>python --version</code>).</p>
</blockquote>

<h2>Syntax class paling sederhana</h2>
<p>Keyword <code>class</code> diikuti nama dalam <strong>PascalCase</strong> (huruf besar di tiap kata). Tubuh class diindentasi seperti fungsi:</p>

<pre><code class="language-python">class Buku:
    pass  # sementara kosong — class valid, belum punya perilaku
</code></pre>

<p><code>pass</code> artinya “belum ada isi”. Berguna saat kamu masih merancang struktur. Untuk produksi, kita segera isi attribute dan method.</p>

<h2>Class dengan attribute di dalam __init__</h2>
<p>Hampir semua class nyata punya <code>__init__</code>: method khusus yang dipanggil otomatis saat object dibuat. Parameter pertama selalu <code>self</code> — merujuk ke instance yang sedang dibuat.</p>

<pre><code class="language-python">class Buku:
    def __init__(self, judul, penulis, tahun):
        self.judul = judul
        self.penulis = penulis
        self.tahun = tahun
</code></pre>

<p>Penjelasan singkat:</p>
<ul>
  <li><code>self.judul = judul</code> — simpan nilai parameter ke <em>attribute</em> milik object ini</li>
  <li>Nama di kiri (<code>self.judul</code>) boleh sama dengan parameter; yang penting adalah <code>self.</code></li>
</ul>

<p>Detail dalam <code>__init__</code>, method lain, dan error “lupa <code>self</code>” akan dibahas lebih dalam di artikel Attribute &amp; Method berikutnya. Hari ini cukup bisa membuat object.</p>

<h2>Membuat object (instance)</h2>
<p>Panggil nama class seperti memanggil fungsi — Python menjalankan <code>__init__</code>:</p>

<pre><code class="language-python">buku_a = Buku("Belajar Python", "Sari", 2024)
buku_b = Buku("ESP32 Praktis", "Budi", 2023)

print(buku_a.judul)   # Belajar Python
print(buku_b.judul)   # ESP32 Praktis
print(buku_a.penulis) # Sari
print(buku_b.tahun)   # 2023
</code></pre>

<p><code>buku_a</code> dan <code>buku_b</code> memakai cetakan yang sama, tetapi <strong>datanya berbeda</strong>. Mengubah satu tidak mengubah yang lain:</p>

<pre><code class="language-python">buku_a.tahun = 2025
print(buku_a.tahun)  # 2025
print(buku_b.tahun)  # 2023 — tidak ikut berubah
</code></pre>

<h2>Diagram: satu class, banyak object</h2>
<figure role="img" aria-label="Diagram class Buku di kiri menghasilkan dua instance buku_a dan buku_b di kanan" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 340" style="display:block;max-width:720px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="oop41Arrow" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/></marker>
    <marker id="oop41ArrowOrange" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#FF7A2F"/></marker>
  </defs>
  <rect x="0" y="0" width="720" height="340" fill="#F5F5F0" rx="6"/>
  <rect x="40" y="100" width="240" height="140" rx="6" fill="#2979FF" stroke="#000" stroke-width="2.5"/>
  <text x="160" y="138" text-anchor="middle" fill="#fff" font-size="16" font-weight="700">class Buku</text>
  <text x="160" y="164" text-anchor="middle" fill="#e3f2fd" font-size="13">__init__(judul, penulis, tahun)</text>
  <text x="160" y="188" text-anchor="middle" fill="#cfe4ff" font-size="12">self.judul / penulis / tahun</text>
  <text x="160" y="212" text-anchor="middle" fill="#cfe4ff" font-size="12">cetakan bersama</text>
  <rect x="400" y="28" width="280" height="100" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="540" y="58" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">buku_a</text>
  <text x="540" y="82" text-anchor="middle" fill="#4A5568" font-size="13">Belajar Python · Sari · 2024</text>
  <text x="540" y="106" text-anchor="middle" fill="#4A5568" font-size="12">instance #1</text>
  <rect x="400" y="212" width="280" height="100" rx="6" fill="#FFF3E8" stroke="#000" stroke-width="2.5"/>
  <text x="540" y="242" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">buku_b</text>
  <text x="540" y="266" text-anchor="middle" fill="#4A5568" font-size="13">ESP32 Praktis · Budi · 2023</text>
  <text x="540" y="290" text-anchor="middle" fill="#4A5568" font-size="12">instance #2</text>
  <line x1="280" y1="130" x2="400" y2="78" stroke="#2979FF" stroke-width="2.5" marker-end="url(#oop41Arrow)"/>
  <text x="300" y="92" fill="#4A5568" font-size="11" font-weight="600">Buku(...)</text>
  <line x1="280" y1="210" x2="400" y2="262" stroke="#FF7A2F" stroke-width="2.5" marker-end="url(#oop41ArrowOrange)"/>
  <text x="300" y="248" fill="#4A5568" font-size="11" font-weight="600">Buku(...)</text>
</svg>
<figcaption style="margin-top:.75rem;color:#4A5568;font-size:.95rem">Pemanggilan Buku(...) menghasilkan object baru di memori.</figcaption>
</figure>

<h2>Identitas object: id() dan is</h2>
<p>Selain isinya (judul, tahun), tiap object punya <strong>identitas</strong> di memori. Fungsi bawaan <code>id()</code> menampilkan angka unik (implementasi CPython: alamat memori):</p>

<pre><code class="language-python">buku_a = Buku("Belajar Python", "Sari", 2024)
buku_b = Buku("Belajar Python", "Sari", 2024)  # isi sama!

print(id(buku_a))
print(id(buku_b))
print(buku_a is buku_b)  # False — dua object berbeda
print(buku_a.judul == buku_b.judul)  # True — nilainya sama
</code></pre>

<p>Operator <code>is</code> membandingkan <em>identitas</em> (object yang sama di memori). Operator <code>==</code> membandingkan <em>nilai</em> — tapi hati-hati: untuk class buatanmu sendiri, <strong>defaultnya</strong> <code>buku_a == buku_b</code> masih <code>False</code> (mirip <code>is</code>) sampai kita tulis <code>__eq__</code> nanti. Makanya contoh di atas membandingkan <code>buku_a.judul == buku_b.judul</code>, bukan kedua object-nya.</p>

<p>Untuk pemula: ingat dulu bahwa <strong>dua pemanggilan <code>Buku(...)</code> = dua object</strong>, meski argumennya identik.</p>

<h2>Method sederhana: perilaku milik object</h2>
<p>Attribute menyimpan data; <strong>method</strong> adalah fungsi di dalam class yang bekerja pada data itu:</p>

<pre><code class="language-python">class Buku:
    def __init__(self, judul, penulis, tahun):
        self.judul = judul
        self.penulis = penulis
        self.tahun = tahun

    def info(self):
        return f"{self.judul} oleh {self.penulis} ({self.tahun})"

buku_a = Buku("Belajar Python", "Sari", 2024)
print(buku_a.info())
# Belajar Python oleh Sari (2024)
</code></pre>

<p>Saat menulis <code>buku_a.info()</code>, Python mengirim <code>buku_a</code> sebagai <code>self</code> secara otomatis. Itulah kenapa method selalu punya parameter <code>self</code> di definisi.</p>

<h2>Kode lengkap — salin &amp; jalankan</h2>
<p>Gabungan singkat dari bagian di atas. Simpan sebagai <code>buku.py</code>, lalu jalankan <code>python buku.py</code>:</p>

<pre><code class="language-python">class Buku:
    def __init__(self, judul, penulis, tahun):
        self.judul = judul
        self.penulis = penulis
        self.tahun = tahun

    def info(self):
        return f"{self.judul} oleh {self.penulis} ({self.tahun})"


buku_a = Buku("Belajar Python", "Sari", 2024)
buku_b = Buku("ESP32 Praktis", "Budi", 2023)

print(buku_a.info())
print(buku_b.info())
print("id berbeda?", id(buku_a) != id(buku_b))
print("object yang sama?", buku_a is buku_b)
</code></pre>

<h2>Pola Dasar — langkah membuat class pertama</h2>
<figure style="margin:1.5rem 0;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1.25rem;color:#1a1a1a" aria-label="Empat langkah membuat class dan object pertama">
<ol style="list-style:none;padding:0;margin:0;color:#1a1a1a">
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Tulis class Nama</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Pakai PascalCase: <code>class Buku:</code></span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Isi __init__ + self</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Terima data, simpan ke <code>self.atribut</code>.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#FF7A2F;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Buat instance</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem"><code>obj = Buku(...)</code> — boleh berkali-kali.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#1a1a1a;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Pakai attribute / method</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem"><code>obj.judul</code>, <code>obj.info()</code> — data dan perilaku lewat object yang sama.</span>
    </div>
  </li>
</ol>
<figcaption style="margin-top:1rem;color:#2D3748;font-size:.95rem">Ringkas: class → __init__ → instance → pakai.</figcaption>
</figure>

<h2>Kesalahan umum pemula</h2>
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
      <td><code>TypeError: Buku.__init__() missing ... required positional argument</code></td>
      <td>Memanggil <code>Buku()</code> tanpa argumen, padahal <code>__init__</code> meminta data</td>
      <td>Isi semua parameter: <code>Buku("Judul", "Penulis", 2024)</code></td>
    </tr>
    <tr>
      <td><code>TypeError: ... missing 1 required positional argument: 'self'</code></td>
      <td>Memanggil method lewat class tanpa instance, atau lupa <code>self</code> di definisi</td>
      <td>Pakai <code>buku_a.info()</code>; pastikan <code>def info(self):</code></td>
    </tr>
    <tr>
      <td><code>AttributeError: 'Buku' object has no attribute 'judul'</code></td>
      <td>Belum di-set di <code>__init__</code>, atau typo nama</td>
      <td>Cek <code>self.judul = judul</code></td>
    </tr>
    <tr>
      <td>Dua variabel “terasa sama” tapi <code>is</code> False</td>
      <td>Dua instance berbeda meski isinya mirip</td>
      <td>Normal — bandingkan nilai attribute, bukan identitas, kecuali memang butuh object yang sama</td>
    </tr>
  </tbody>
</table>

<h2>Latihan singkat</h2>
<ol>
  <li>Buat <code>class Anggota</code> dengan <code>nama</code> dan <code>id_anggota</code>. Buat dua instance, cetak attribute-nya.</li>
  <li>Tambahkan method <code>sapa(self)</code> yang mengembalikan string <code>"Halo, {nama}"</code>.</li>
  <li>Buat dua <code>Buku</code> dengan judul sama. Bandingkan <code>id()</code>, <code>is</code>, dan <code>==</code> pada attribute <code>judul</code>.</li>
</ol>

<h2>FAQ singkat</h2>
<p><strong>Apakah wajib pakai __init__?</strong><br>Tidak wajib secara sintaks, tapi hampir selalu kamu butuh. Tanpa <code>__init__</code>, attribute harus di-set manual setelah object dibuat — mudah lupa.</p>
<p><strong>Kenapa nama class PascalCase?</strong><br>Konvensi PEP 8 agar mudah dibedakan dari fungsi/variabel (<code>snake_case</code>).</p>
<p><strong>Berapa banyak object yang boleh dibuat?</strong><br>Sebanyak yang dibutuhkan program (dan memori komputer). Class tidak membatasi jumlah instance.</p>

<h2>Kesimpulan &amp; langkah berikutnya</h2>
<p>Class adalah cetakan; object adalah hasil <code>NamaClass(...)</code>. Tiap instance punya attribute sendiri dan identitas sendiri (<code>id</code> / <code>is</code>). Method memberi perilaku yang melekat pada object lewat <code>self</code>.</p>
<p>Artikel ini adalah <strong>#41 (ini)</strong> — langkah kedua Seri 3 setelah <a href="/artikel/mengenal-oop-cara-berpikir-dengan-objek-python">Mengenal OOP (#40)</a>. Kamu sudah bisa menulis class, membuat beberapa object, dan membedakan isi vs identitas.</p>
<p>Lanjut ke artikel berikutnya — <strong>Attribute, Method, dan Constructor <code>__init__</code></strong>: kita bedah <code>self</code> lebih dalam, method yang mengubah state, dan kesalahan klasik saat parameter tertukar. (Belum di-hyperlink sampai artikel itu live.)</p>

<blockquote>
  <p><strong>Seri 3 progress:</strong> 2/10 artikel (setelah #41 live). Prasyarat: <a href="/artikel/mengenal-oop-cara-berpikir-dengan-objek-python">Mengenal OOP (#40)</a>.</p>
</blockquote>
HTML;
    }
}
