<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article43Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $progCat = Category::where('slug', 'programming')->first();

        if (! $admin || ! $progCat) {
            throw new \RuntimeException('User atau kategori programming tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'encapsulation-property-python-oop';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        foreach ([
            'oop' => 'oop',
            'encapsulation' => 'encapsulation',
            'python' => 'python',
        ] as $tagSlug => $tagName) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagName]);
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $progCat->id,
                'title'           => 'Encapsulation dan @property di Python OOP',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'Encapsulation & @property Python — Tutorial OOP Pemula',
                'seo_description' => 'Pelajari encapsulation di Python: konvensi _, name-mangling __, @property dan setter untuk validasi — lanjutan Seri 3 OOP berbahasa Indonesia.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['python', 'oop', 'encapsulation'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel ke-43 berhasil dipublish: '.$article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — menjaga data di dalam object</h2>
<p>Di <a href="/artikel/attribute-method-constructor-init-python">Attribute, Method, dan <code>__init__</code> (#42)</a> kamu sudah punya <code>self.stok</code> dan method <code>pinjam()</code>. Masalahnya: siapa pun masih bisa menulis <code>buku.stok = -99</code> dari luar — aturan bisnis dilompati.</p>
<p><strong>Encapsulation</strong> adalah praktik menyembunyikan detail implementasi dan membuka akses lewat “pintu” yang kamu kendalikan. Di Python, itu bukan kunci gembok keras seperti bahasa lain — lebih ke <em>konvensi + API</em> (<code>_</code>, <code>__</code>, <code>@property</code>).</p>

<blockquote>
  <p><strong>Prasyarat:</strong> Selesai <a href="/artikel/attribute-method-constructor-init-python">#42</a> (attribute, method, <code>__init__</code>). Fondasi object: <a href="/artikel/class-dan-object-pertama-python">Class &amp; Object (#41)</a> · mental model: <a href="/artikel/mengenal-oop-cara-berpikir-dengan-objek-python">Mengenal OOP (#40)</a>.</p>
</blockquote>

<h2>Masalah: attribute publik terlalu terbuka</h2>
<p>Tanpa penjaga, state bisa rusak dari mana saja:</p>

<pre><code class="language-python">class Buku:
    def __init__(self, judul, tahun, stok=1):
        self.judul = judul
        self.tahun = tahun
        self.stok = stok

    def pinjam(self):
        if self.stok &lt;= 0:
            raise ValueError("stok habis")
        self.stok -= 1


buku = Buku("Belajar Python", 2024, stok=2)
buku.stok = -5          # lolos — pinjam() tidak dipanggil
buku.tahun = 1200       # tahun mustahil, tetap diterima
print(buku.stok, buku.tahun)
</code></pre>

<p>Method <code>pinjam()</code> sudah benar, tapi “pintu belakang” masih terbuka. Encapsulation menutup pintu itu — atau setidaknya membuatnya sengaja dan terlihat.</p>

<h2>Tiga tingkat akses di Python</h2>
<p>Python tidak punya <code>private</code> keyword seperti Java. Yang ada: kesepakatan nama:</p>
<table>
  <thead>
    <tr>
      <th>Nama</th>
      <th>Arti konvensi</th>
      <th>Contoh</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><code>stok</code></td>
      <td>Publik — boleh diakses dari luar</td>
      <td><code>buku.stok</code></td>
    </tr>
    <tr>
      <td><code>_stok</code></td>
      <td>“Internal” — jangan disentuh dari luar kecuali sadar</td>
      <td><code>self._stok</code></td>
    </tr>
    <tr>
      <td><code>__stok</code></td>
      <td>Name-mangling — Python ubah jadi <code>_Buku__stok</code></td>
      <td>hindari bentrok di inheritance</td>
    </tr>
  </tbody>
</table>

<pre><code class="language-python">class Buku:
    def __init__(self, judul, stok=1):
        self.judul = judul      # publik OK untuk label
        self._stok = stok       # internal — pakai _ awalan
        self.__kode = "BK-01"   # name-mangling

buku = Buku("Belajar Python", stok=2)
print(buku.judul)          # OK
print(buku._stok)          # bisa, tapi “jangan” — sinyal internal
# print(buku.__kode)       # AttributeError
print(buku._Buku__kode)    # bisa, tapi jelek — hindari
</code></pre>

<p>Gunakan <code>_</code> untuk state yang ingin kamu jaga. Cadangkan <code>__</code> jika benar-benar khawatir bentrok nama di subclass (lihat <a href="/artikel/inheritance-pewarisan-class-python">Inheritance (#44)</a>). Jangan mengira <code>__</code> = keamanan mutlak.</p>

<h2>@property — baca lewat API bersih</h2>
<p><code>@property</code> membuat method terlihat seperti attribute saat dibaca. Dari luar: <code>buku.stok</code>. Di dalam: kamu yang tentukan dari mana nilai itu datang:</p>

<pre><code class="language-python">class Buku:
    def __init__(self, judul, tahun, stok=1):
        self.judul = judul
        self._tahun = tahun
        self._stok = stok

    @property
    def stok(self):
        """Baca stok — dari luar seperti attribute."""
        return self._stok

    @property
    def tahun(self):
        return self._tahun


buku = Buku("Belajar Python", 2024, stok=2)
print(buku.stok)    # 2 — memanggil property, bukan field mentah
print(buku.tahun)   # 2024
# buku.stok = 9     # AttributeError: can't set attribute (belum ada setter)
</code></pre>

<p>Ini jembatan bagus dari <a href="/artikel/attribute-method-constructor-init-python">#42</a>: pembaca masih menulis <code>buku.stok</code>, tapi kamu sudah mengontrol implementasinya. Di contoh di atas kita isi <code>_tahun</code>/<code>_stok</code> langsung karena belum ada setter — setelah ada setter (bagian berikutnya), prefer <code>self.tahun = tahun</code> di <code>__init__</code> supaya validasi ikut jalan.</p>

<h2>@setter — validasi saat menulis</h2>
<p>Tambah setter agar penulisan juga lewat pintu yang sama. Di sini kita jaga <code>tahun</code> dan <code>stok</code>:</p>

<pre><code class="language-python">class Buku:
    def __init__(self, judul, tahun, stok=1):
        self.judul = judul
        self.tahun = tahun   # lewat setter di bawah
        self.stok = stok

    @property
    def tahun(self):
        return self._tahun

    @tahun.setter
    def tahun(self, nilai):
        if nilai &lt; 1900 or nilai &gt; 2100:
            raise ValueError("tahun tidak masuk akal")
        self._tahun = nilai

    @property
    def stok(self):
        return self._stok

    @stok.setter
    def stok(self, nilai):
        if nilai &lt; 0:
            raise ValueError("stok tidak boleh negatif")
        self._stok = nilai

    def pinjam(self):
        if self.stok &lt;= 0:
            raise ValueError("stok habis")
        self.stok -= 1
        return self.stok


buku = Buku("Belajar Python", 2024, stok=2)
print(buku.stok)          # 2
buku.stok = 1             # OK lewat setter
print(buku.pinjam())      # 0
# buku.stok = -1          # ValueError: stok tidak boleh negatif
# Buku("Kuno", 1800)      # ValueError: tahun tidak masuk akal
</code></pre>

<p>Perhatikan: di <code>__init__</code> kita menulis <code>self.tahun = tahun</code> dan <code>self.stok = stok</code> — itu memanggil setter, jadi validasi jalan sejak object lahir. Jangan assign langsung ke <code>self._tahun</code> di <code>__init__</code> kalau kamu ingin aturan yang sama berlaku di mana-mana (kecuali kasus khusus yang kamu sadari).</p>
<p>Bonus kecil: di <code>pinjam()</code>, baris <code>self.stok -= 1</code> juga lewat property — Python membaca lewat getter, lalu menulis lewat setter. Aturan “stok tidak negatif” tetap dijaga.</p>

<blockquote>
  <p><strong>Tip:</strong> Method seperti <code>pinjam()</code> tetap berguna untuk aksi bisnis (“pinjam satu salinan”). Property menjaga <em>invariant</em> data (stok ≥ 0, tahun wajar). Keduanya saling melengkapi — bukan saling mengganti.</p>
</blockquote>

<p>Jebakan paling sering di property: di dalam setter kamu menulis <code>self.stok = ...</code> lagi — itu memanggil setter yang sama, lalu <code>RecursionError</code>:</p>

<pre><code class="language-python"># SALAH — setter memanggil dirinya sendiri
class BukuSalah:
    def __init__(self, stok=1):
        self.stok = stok

    @property
    def stok(self):
        return self._stok

    @stok.setter
    def stok(self, nilai):
        self.stok = nilai  # memanggil setter lagi → RecursionError

# BukuSalah(2)  # RecursionError: maximum recursion depth exceeded

# BENAR — simpan ke attribute internal
class BukuBenar:
    def __init__(self, stok=1):
        self.stok = stok

    @property
    def stok(self):
        return self._stok

    @stok.setter
    def stok(self, nilai):
        if nilai &lt; 0:
            raise ValueError("stok tidak boleh negatif")
        self._stok = nilai


b = BukuBenar(2)
print(b.stok)  # 2
</code></pre>

<figure role="img" aria-label="Diagram encapsulation: kode luar mengakses property, property menjaga attribute internal _stok" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 320" style="display:block;max-width:720px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="oop43Arrow" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/></marker>
  </defs>
  <rect x="0" y="0" width="720" height="320" fill="#F5F5F0" rx="6"/>
  <rect x="30" y="100" width="180" height="100" rx="6" fill="#FFF3E8" stroke="#000" stroke-width="2.5"/>
  <text x="120" y="140" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Kode luar</text>
  <text x="120" y="168" text-anchor="middle" fill="#2D3748" font-size="13">buku.stok = 3</text>
  <rect x="270" y="70" width="200" height="160" rx="6" fill="#2979FF" stroke="#000" stroke-width="2.5"/>
  <text x="370" y="110" text-anchor="middle" fill="#fff" font-size="15" font-weight="700">@property / setter</text>
  <text x="370" y="140" text-anchor="middle" fill="#e3f2fd" font-size="13">validasi &amp; aturan</text>
  <text x="370" y="168" text-anchor="middle" fill="#cfe4ff" font-size="12">pintu resmi</text>
  <text x="370" y="196" text-anchor="middle" fill="#cfe4ff" font-size="12">tolak nilai aneh</text>
  <rect x="530" y="100" width="160" height="100" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="610" y="140" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">_stok</text>
  <text x="610" y="168" text-anchor="middle" fill="#2D3748" font-size="12">internal</text>
  <line x1="210" y1="150" x2="270" y2="150" stroke="#2979FF" stroke-width="2.5" marker-end="url(#oop43Arrow)"/>
  <line x1="470" y1="150" x2="530" y2="150" stroke="#2979FF" stroke-width="2.5" marker-end="url(#oop43Arrow)"/>
  <text x="360" y="290" text-anchor="middle" fill="#2D3748" font-size="13">Luar tidak menulis _stok langsung — lewat setter.</text>
</svg>
<figcaption style="margin-top:.75rem;color:#2D3748;font-size:.95rem">Encapsulation = satu pintu resmi ke state internal.</figcaption>
</figure>

<h2>Kapan encapsulation terasa penting?</h2>
<ul>
  <li><strong>Proyek tumbuh</strong> — banyak file menyentuh object yang sama; satu pintu mengurangi bug “siapa yang set stok aneh?”</li>
  <li><strong>API publik class</strong> — kamu bisa ganti penyimpanan internal (<code>_stok</code> → hitung dari list pinjaman) tanpa mengubah pemanggilan <code>buku.stok</code></li>
  <li><strong>Validasi terpusat</strong> — aturan tahun/stok tidak diduplikasi di 10 tempat</li>
</ul>
<p>Untuk script 30 baris sekali pakai, attribute publik dari <a href="/artikel/attribute-method-constructor-init-python">#42</a> sering cukup. Encapsulation mulai “wajib secara praktik” saat class dipakai orang lain (atau dirimu 3 bulan kemudian).</p>

<h2>Pola Dasar — merancang encapsulation</h2>
<figure style="margin:1.5rem 0;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1.25rem;color:#1a1a1a" aria-label="Lima langkah merancang encapsulation dengan property">
<ol style="list-style:none;padding:0;margin:0;color:#1a1a1a">
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Tandai state sensitif dengan _</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem"><code>_stok</code>, <code>_tahun</code> — sinyal “internal”.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Buka baca lewat @property</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">API luar tetap <code>buku.stok</code>, implementasi bisa berubah.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#FF7A2F;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Validasi di @setter</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Tolak nilai mustahil sedini mungkin.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#FF7A2F;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Init lewat property</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem"><code>self.stok = stok</code> di <code>__init__</code> supaya aturan sama.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#1a1a1a;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">5</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Method untuk aksi bisnis</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem"><code>pinjam()</code> / <code>kembalikan()</code> — bukan hanya setter mentah.</span>
    </div>
  </li>
</ol>
<figcaption style="margin-top:1rem;color:#2D3748;font-size:.95rem">Ringkas: _internal → property → setter → init lewat API → method aksi.</figcaption>
</figure>

<h2>Kode lengkap — salin &amp; jalankan</h2>
<p>Simpan sebagai <code>buku_terlindungi.py</code>, lalu <code>python buku_terlindungi.py</code>:</p>

<pre><code class="language-python">class Buku:
    def __init__(self, judul, penulis, tahun, stok=1):
        self.judul = judul
        self.penulis = penulis
        self.tahun = tahun
        self.stok = stok

    @property
    def tahun(self):
        return self._tahun

    @tahun.setter
    def tahun(self, nilai):
        if nilai &lt; 1900 or nilai &gt; 2100:
            raise ValueError("tahun tidak masuk akal")
        self._tahun = nilai

    @property
    def stok(self):
        return self._stok

    @stok.setter
    def stok(self, nilai):
        if nilai &lt; 0:
            raise ValueError("stok tidak boleh negatif")
        self._stok = nilai

    def info(self):
        return f"{self.judul} · {self.penulis} ({self.tahun}) · stok {self.stok}"

    def pinjam(self):
        if self.stok &lt;= 0:
            raise ValueError("stok habis")
        self.stok -= 1
        return self.stok

    def kembalikan(self):
        self.stok += 1
        return self.stok


a = Buku("Belajar Python", "Sari", 2024, stok=2)
print(a.info())
print("pinjam:", a.pinjam())
print("stok:", a.stok)
a.stok = 5
print("set stok:", a.stok)
# a.stok = -1  # ValueError
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
      <td><code>AttributeError: property 'stok' of 'Buku' object has no setter</code></td>
      <td>Hanya ada <code>@property</code>, belum <code>@stok.setter</code></td>
      <td>Tambah setter, atau jangan assign dari luar</td>
    </tr>
    <tr>
      <td><code>RecursionError</code> di property</td>
      <td>Di dalam getter/setter menulis <code>self.stok</code> lagi (bukan <code>self._stok</code>)</td>
      <td>Simpan di <code>self._stok</code>; property hanya pintu</td>
    </tr>
    <tr>
      <td>Validasi tidak jalan di <code>__init__</code></td>
      <td>Assign langsung <code>self._stok = stok</code> tanpa lewat setter</td>
      <td>Pakai <code>self.stok = stok</code> di init</td>
    </tr>
    <tr>
      <td><code>AttributeError: ... has no attribute '_Buku__x'</code> bingung</td>
      <td>Mengira <code>__x</code> “rahasia absolut”, lalu akses salah nama</td>
      <td>Lebih sering cukup <code>_x</code>; pahami mangling sebelum pakai <code>__</code></td>
    </tr>
    <tr>
      <td>Setter terlalu ketat / longgar</td>
      <td>Aturan bisnis tidak jelas</td>
      <td>Tulis invariant dulu (stok ≥ 0), baru kode</td>
    </tr>
  </tbody>
</table>

<h2>Latihan singkat</h2>
<ol>
  <li>Tambah property <code>tersedia</code> (read-only) yang mengembalikan <code>True</code> jika <code>stok &gt; 0</code>.</li>
  <li>Buat setter <code>judul</code> yang menolak string kosong.</li>
  <li>Coba sengaja tulis <code>self.stok = self.stok</code> di dalam setter (tanpa <code>_</code>) — amati <code>RecursionError</code>, lalu perbaiki.</li>
</ol>

<h2>FAQ singkat</h2>
<p><strong>Apakah _ benar-benar mencegah akses?</strong><br>Tidak. Itu sinyal sosial untuk programmer. Python percaya dewasa: kamu <em>bisa</em> menyentuh <code>_stok</code>, tapi sebaiknya tidak.</p>
<p><strong>Haruskah semua attribute jadi property?</strong><br>Tidak. Mulai dari data yang punya aturan (stok, tahun, email). Label sederhana seperti <code>judul</code> sering aman publik dulu.</p>
<p><strong>Apa bedanya property dan method info()?</strong><br>Property terasa seperti data (<code>buku.stok</code>). Method terasa seperti aksi atau ringkasan (<code>buku.info()</code>). Pilih yang paling jujur dibaca orang lain.</p>

<h2>Kesimpulan &amp; langkah berikutnya</h2>
<p>Encapsulation di Python = konvensi nama + pintu resmi (<code>@property</code> / setter) supaya invariant tetap hidup. Kamu tidak mengunci dunia — kamu merapikan kontrak class.</p>
<p>Artikel ini adalah <strong>#43 (ini)</strong> — langkah keempat Seri 3 setelah <a href="/artikel/attribute-method-constructor-init-python">Attribute &amp; <code>__init__</code> (#42)</a>.</p>
<p>Lanjut ke <a href="/artikel/inheritance-pewarisan-class-python">Inheritance &amp; <code>super()</code> (#44)</a>: class <code>Ebook(Buku)</code>, <code>super().__init__</code>, dan override method tanpa menduplikasi kode.</p>

<blockquote>
  <p><strong>Seri 3 progress:</strong> 5/10 artikel live. Kamu di langkah <strong>#43 (ini)</strong>. Prasyarat: <a href="/artikel/attribute-method-constructor-init-python">#42</a> · <a href="/artikel/class-dan-object-pertama-python">#41</a> · <a href="/artikel/mengenal-oop-cara-berpikir-dengan-objek-python">#40</a>.</p>
</blockquote>
HTML;
    }
}
