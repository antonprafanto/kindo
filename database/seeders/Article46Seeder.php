<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article46Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $progCat = Category::where('slug', 'programming')->first();

        if (! $admin || ! $progCat) {
            throw new \RuntimeException('User atau kategori programming tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'abstraction-abc-python-oop';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        foreach ([
            'oop' => 'oop',
            'abstraction' => 'abstraction',
            'python' => 'python',
        ] as $tagSlug => $tagName) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagName]);
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $progCat->id,
                'title'           => 'Abstraction & ABC: Kontrak Class Abstract di Python OOP',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'Abstraction & ABC Python OOP — Kontrak Class Abstract',
                'seo_description' => 'Pelajari abstraction di Python dengan abc.ABC dan @abstractmethod: kontrak Pinjaman, TypeError subclass belum lengkap, dan kaitan dengan polymorphism — Seri 3 OOP berbahasa Indonesia.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['python', 'oop', 'abstraction'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel ke-46 berhasil dipublish: '.$article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — dari “bisa quack” ke kontrak tertulis</h2>
<p>Di <a href="/artikel/polymorphism-python-oop">Polymorphism (#45)</a> kita andalkan antarmuka bersama: kalau object punya <code>info()</code>, loop tetap pendek. Duck typing fleksibel — tapi lupa method baru ketahuan saat runtime (<code>AttributeError</code>).</p>
<p><strong>Abstraction</strong> di sini berarti: tulis kontrak “apa yang wajib bisa dilakukan”, lalu biarkan tiap class mengisi “bagaimana”-nya. Di Python, alat resmi untuk itu adalah <strong>ABC</strong> (<em>Abstract Base Class</em>) dari modul <code>abc</code>.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> Selesai <a href="/artikel/polymorphism-python-oop">Polymorphism (#45)</a> (nyaman dengan loop <code>item.info()</code>, duck typing, dan kapan <code>isinstance</code>). Fondasi: <a href="/artikel/inheritance-pewarisan-class-python">Inheritance (#44)</a> · <a href="/artikel/encapsulation-property-python-oop">Encapsulation (#43)</a> · <a href="/artikel/attribute-method-constructor-init-python">Attribute (#42)</a> · <a href="/artikel/class-dan-object-pertama-python">Class &amp; Object (#41)</a> · <a href="/artikel/mengenal-oop-cara-berpikir-dengan-objek-python">Mengenal OOP (#40)</a>.</p>
</blockquote>

<h2>Kenapa perlu kontrak?</h2>
<p>Bayangkan sistem perpustakaan: beberapa tipe item bisa dipinjam. Tanpa kontrak, seorang rekan bisa menulis class baru yang “hampir cocok” tapi lupa <code>kembalikan()</code>. Loop peminjaman baru pecah di produksi.</p>
<p>ABC menjawab: “class ini <em>harus</em> mengimplementasikan method berikut — atau Python menolak menginstansiasinya.” Error muncul lebih awal (saat buat object), bukan di tengah malam saat user pinjam buku.</p>
<ul>
  <li><strong>Duck typing</strong> = percaya kemampuan muncul saat dipanggil</li>
  <li><strong>ABC</strong> = tulis daftar kemampuan wajib + cek sebelum object hidup</li>
</ul>

<h2>Masalah tanpa ABC — lupa method</h2>
<p>Tanpa kontrak formal, class “nyaris siap” tetap bisa dibuat. Bug baru muncul saat method dipanggil:</p>

<pre><code class="language-python">class BukuFisikSalah:
    def __init__(self, judul):
        self.judul = judul
        self.dipinjam = False

    def pinjam(self):
        self.dipinjam = True
        return f"pinjam {self.judul}"
    # lupa kembalikan()


item = BukuFisikSalah("ESP32 Praktis")
print(item.pinjam())
# print(item.kembalikan())  # AttributeError: ... has no attribute 'kembalikan'
print("object hidup — bug method hilang belum terlihat")
</code></pre>

<p>Object sudah “hidup”. Di tim besar, itu terlalu terlambat.</p>

<h2>ABC pertama — kontrak <code>Pinjaman</code></h2>
<p>Modul standar <code>abc</code> menyediakan <code>ABC</code> dan dekorator <code>@abstractmethod</code>. Class abstract mendefinisikan method tanpa isi penuh (sering pakai <code>...</code> atau <code>pass</code>):</p>

<pre><code class="language-python">from abc import ABC, abstractmethod


class Pinjaman(ABC):
    """Kontrak: apa pun yang bisa dipinjam wajib punya pinjam &amp; kembalikan."""

    @abstractmethod
    def pinjam(self):
        ...

    @abstractmethod
    def kembalikan(self):
        ...


print(Pinjaman.__name__)
# Pinjaman()  # TypeError: Can't instantiate abstract class Pinjaman...
</code></pre>

<p>Poin penting: <strong>ABC sendiri tidak diinstansiasi</strong>. Ia cetakan kontrak, bukan object bisnis.</p>

<h2>Subclass belum lengkap → TypeError</h2>
<p>Warisi <code>Pinjaman</code> tapi tinggalkan satu method abstract — Python menolak membuat object:</p>

<pre><code class="language-python">from abc import ABC, abstractmethod


class Pinjaman(ABC):
    @abstractmethod
    def pinjam(self):
        ...

    @abstractmethod
    def kembalikan(self):
        ...


class BukuBelumSiap(Pinjaman):
    def __init__(self, judul):
        self.judul = judul

    def pinjam(self):
        return f"pinjam {self.judul}"
    # kembalikan masih abstract


try:
    BukuBelumSiap("ESP32 Praktis")
except TypeError as err:
    print("TypeError:", err)
</code></pre>

<p>Output kurang lebih: <code>TypeError: Can't instantiate abstract class BukuBelumSiap without an implementation for abstract method 'kembalikan'</code> (teks bisa sedikit beda antar versi Python). Itu sinyal bagus: “kontrak belum dipenuhi.”</p>

<h2>Isi kontrak — dua implementasi berbeda</h2>
<p>Sekarang dua class memenuhi kontrak dengan cara masing-masing (polymorphism + abstraction). Di sini kita <em>tidak</em> mewarisi <code>Buku</code> dari artikel sebelumnya — fokusnya kemampuan “bisa dipinjam”, bukan silsilah katalog:</p>

<pre><code class="language-python">from abc import ABC, abstractmethod


class Pinjaman(ABC):
    @abstractmethod
    def pinjam(self):
        ...

    @abstractmethod
    def kembalikan(self):
        ...


class BukuFisik(Pinjaman):
    def __init__(self, judul, stok=1):
        self.judul = judul
        self.stok = stok

    def pinjam(self):
        if self.stok &lt; 1:
            return f"stok habis: {self.judul}"
        self.stok -= 1
        return f"pinjam fisik {self.judul} · sisa {self.stok}"

    def kembalikan(self):
        self.stok += 1
        return f"kembali fisik {self.judul} · stok {self.stok}"


class EbookLisensi(Pinjaman):
    def __init__(self, judul, kuota=2):
        self.judul = judul
        self.kuota = kuota

    def pinjam(self):
        if self.kuota &lt; 1:
            return f"kuota habis: {self.judul}"
        self.kuota -= 1
        return f"aktifkan lisensi {self.judul} · sisa {self.kuota}"

    def kembalikan(self):
        self.kuota += 1
        return f"nonaktifkan lisensi {self.judul} · kuota {self.kuota}"


b = BukuFisik("ESP32 Praktis", stok=2)
e = EbookLisensi("Belajar Python", kuota=1)
print(b.pinjam())
print(e.pinjam())
print(b.kembalikan())
</code></pre>

<p>Output kurang lebih:</p>
<pre><code>pinjam fisik ESP32 Praktis · sisa 1
aktifkan lisensi Belajar Python · sisa 0
kembali fisik ESP32 Praktis · stok 2
</code></pre>

<p>Pemanggil hanya peduli kontrak <code>pinjam</code>/<code>kembalikan</code>. Detail stok vs kuota lisensi tetap di class masing-masing — selaras pilar Abstraction di <a href="/artikel/mengenal-oop-cara-berpikir-dengan-objek-python">Mengenal OOP (#40)</a>.</p>

<figure role="img" aria-label="Diagram ABC: kontrak Pinjaman diimplementasikan oleh BukuFisik dan EbookLisensi" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 300" style="display:block;max-width:720px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="oop46Arrow" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/></marker>
  </defs>
  <rect x="0" y="0" width="720" height="300" fill="#F5F5F0" rx="6"/>
  <rect x="210" y="20" width="300" height="80" rx="6" fill="#1a1a1a" stroke="#000" stroke-width="2.5"/>
  <text x="360" y="52" text-anchor="middle" fill="#fff" font-size="16" font-weight="700">Pinjaman (ABC)</text>
  <text x="360" y="78" text-anchor="middle" fill="#CBD5E0" font-size="13">pinjam() · kembalikan()</text>
  <rect x="60" y="170" width="260" height="100" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="190" y="210" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">BukuFisik</text>
  <text x="190" y="240" text-anchor="middle" fill="#2D3748" font-size="12">atur stok fisik</text>
  <rect x="400" y="170" width="260" height="100" rx="6" fill="#FFF3E8" stroke="#000" stroke-width="2.5"/>
  <text x="530" y="210" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">EbookLisensi</text>
  <text x="530" y="240" text-anchor="middle" fill="#2D3748" font-size="12">atur kuota lisensi</text>
  <line x1="300" y1="100" x2="190" y2="170" stroke="#2979FF" stroke-width="2.5" marker-end="url(#oop46Arrow)"/>
  <line x1="420" y1="100" x2="530" y2="170" stroke="#2979FF" stroke-width="2.5" marker-end="url(#oop46Arrow)"/>
</svg>
<figcaption style="margin-top:.75rem;color:#2D3748;font-size:.95rem">Satu kontrak ABC, dua implementasi — abstraction + polymorphism bekerja bersama.</figcaption>
</figure>

<h2>Loop aman lewat kontrak</h2>
<p>Setelah semua item memenuhi <code>Pinjaman</code>, loop dari <a href="/artikel/polymorphism-python-oop">Polymorphism (#45)</a> jadi lebih tenang. <code>isinstance(item, Pinjaman)</code> di sini <em>bukan</em> hutan cabang — melainkan cek “apakah object memegang kontrak?”:</p>

<pre><code class="language-python">from abc import ABC, abstractmethod


class Pinjaman(ABC):
    @abstractmethod
    def pinjam(self):
        ...

    @abstractmethod
    def kembalikan(self):
        ...


class BukuFisik(Pinjaman):
    def __init__(self, judul, stok=1):
        self.judul = judul
        self.stok = stok

    def pinjam(self):
        if self.stok &lt; 1:
            return f"stok habis: {self.judul}"
        self.stok -= 1
        return f"pinjam fisik {self.judul} · sisa {self.stok}"

    def kembalikan(self):
        self.stok += 1
        return f"kembali fisik {self.judul} · stok {self.stok}"


class EbookLisensi(Pinjaman):
    def __init__(self, judul, kuota=2):
        self.judul = judul
        self.kuota = kuota

    def pinjam(self):
        if self.kuota &lt; 1:
            return f"kuota habis: {self.judul}"
        self.kuota -= 1
        return f"aktifkan lisensi {self.judul} · sisa {self.kuota}"

    def kembalikan(self):
        self.kuota += 1
        return f"nonaktifkan lisensi {self.judul} · kuota {self.kuota}"


koleksi = [BukuFisik("ESP32 Praktis", stok=1), EbookLisensi("Belajar Python", kuota=1)]
for item in koleksi:
    if isinstance(item, Pinjaman):
        print(item.pinjam())
</code></pre>

<p>Output kurang lebih:</p>
<pre><code>pinjam fisik ESP32 Praktis · sisa 0
aktifkan lisensi Belajar Python · sisa 0
</code></pre>
<p>Bedakan dengan anti-pola di <a href="/artikel/polymorphism-python-oop">Polymorphism (#45)</a>: hutan <code>isinstance</code> yang menyalin seluruh perilaku ke pemanggil. Di sini pemanggil tetap memanggil method kontrak; <code>isinstance</code> hanya menjaga batas API.</p>
<p>Satu perbedaan penting dari duck typing: object yang “kebetulan” punya method sama <em>belum tentu</em> lolos cek kontrak ABC:</p>
<pre><code class="language-python">from abc import ABC, abstractmethod


class Pinjaman(ABC):
    @abstractmethod
    def pinjam(self):
        ...

    @abstractmethod
    def kembalikan(self):
        ...


class BukuFisik(Pinjaman):
    def __init__(self, judul):
        self.judul = judul

    def pinjam(self):
        return f"pinjam {self.judul}"

    def kembalikan(self):
        return f"kembali {self.judul}"


class EntriDuck:
    """Punya pinjam/kembalikan — tapi bukan subclass Pinjaman."""
    def __init__(self, judul):
        self.judul = judul

    def pinjam(self):
        return f"duck pinjam {self.judul}"

    def kembalikan(self):
        return f"duck kembali {self.judul}"


formal = BukuFisik("ESP32 Praktis")
duck = EntriDuck("Datasheet DHT22")
print("isinstance formal:", isinstance(formal, Pinjaman))
print("isinstance duck:", isinstance(duck, Pinjaman))
print(duck.pinjam())  # tetap bisa dipanggil — duck typing
</code></pre>
<p>Output kurang lebih:</p>
<pre><code>isinstance formal: True
isinstance duck: False
duck pinjam Datasheet DHT22
</code></pre>
<p>Jadi: duck typing = “bisa dipanggil”. ABC + <code>isinstance</code> = “terdaftar di kontrak resmi”. Pilih sesuai kebutuhan tim/API.</p>

<h2>Method konkret di ABC — boleh, tapi hemat</h2>
<p>ABC boleh punya method biasa (bukan abstract) untuk perilaku bersama. Tetap jaga agar kontrak tidak membengkak jadi “god class”:</p>

<pre><code class="language-python">from abc import ABC, abstractmethod


class Pinjaman(ABC):
    @abstractmethod
    def pinjam(self):
        ...

    @abstractmethod
    def kembalikan(self):
        ...

    def label(self):
        return self.__class__.__name__


class BukuFisik(Pinjaman):
    def __init__(self, judul):
        self.judul = judul

    def pinjam(self):
        return f"pinjam {self.judul}"

    def kembalikan(self):
        return f"kembali {self.judul}"


b = BukuFisik("Cerita Sensor")
print(b.label(), "->", b.pinjam())
</code></pre>
<p>Output: <code>BukuFisik -> pinjam Cerita Sensor</code>.</p>

<blockquote>
  <p><strong>Ingat:</strong> method bersama di ABC berguna; kalau hampir semua logika pindah ke induk abstract, evaluasi ulang — mungkin inheritance biasa atau <a href="/artikel/composition-vs-inheritance-python">composition (#47)</a> lebih jujur.</p>
</blockquote>

<h2>Pola Dasar — merancang ABC</h2>
<figure style="margin:1.5rem 0;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1.25rem;color:#1a1a1a" aria-label="Lima langkah merancang Abstract Base Class di Python">
<ol style="list-style:none;padding:0;margin:0;color:#1a1a1a">
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Sebut kemampuan wajib</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Mis. “bisa dipinjam” → <code>pinjam</code> + <code>kembalikan</code>, bukan detail stok.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Tulis class <code>(ABC)</code></strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem"><code>from abc import ABC, abstractmethod</code> lalu dekorasi method wajib.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#FF7A2F;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Implementasi di anak</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Setiap subclass mengisi semua <code>@abstractmethod</code> sebelum diinstansiasi.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#FF7A2F;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Loop lewat kontrak</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Panggil method kontrak yang sudah diisi; <code>isinstance(..., Pinjaman)</code> hanya penjaga API.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#1a1a1a;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">5</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Jangan instansiasi ABC</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">ABC = kontrak. Object bisnis = subclass konkret.</span>
    </div>
  </li>
</ol>
<figcaption style="margin-top:1rem;color:#2D3748;font-size:.95rem">Ringkas: kemampuan → ABC → isi anak → loop kontrak → jangan new ABC.</figcaption>
</figure>

<h2>Kode lengkap — salin &amp; jalankan</h2>
<p>Simpan sebagai <code>kontrak_pinjaman.py</code>, lalu <code>python kontrak_pinjaman.py</code>:</p>

<pre><code class="language-python">from abc import ABC, abstractmethod


class Pinjaman(ABC):
    @abstractmethod
    def pinjam(self):
        ...

    @abstractmethod
    def kembalikan(self):
        ...

    def label(self):
        return self.__class__.__name__


class BukuFisik(Pinjaman):
    def __init__(self, judul, stok=1):
        self.judul = judul
        self.stok = stok

    def pinjam(self):
        if self.stok &lt; 1:
            return f"stok habis: {self.judul}"
        self.stok -= 1
        return f"pinjam fisik {self.judul} · sisa {self.stok}"

    def kembalikan(self):
        self.stok += 1
        return f"kembali fisik {self.judul} · stok {self.stok}"


class EbookLisensi(Pinjaman):
    def __init__(self, judul, kuota=2):
        self.judul = judul
        self.kuota = kuota

    def pinjam(self):
        if self.kuota &lt; 1:
            return f"kuota habis: {self.judul}"
        self.kuota -= 1
        return f"aktifkan lisensi {self.judul} · sisa {self.kuota}"

    def kembalikan(self):
        self.kuota += 1
        return f"nonaktifkan lisensi {self.judul} · kuota {self.kuota}"


koleksi = [
    BukuFisik("ESP32 Praktis", stok=2),
    EbookLisensi("Belajar Python", kuota=1),
]

for item in koleksi:
    if isinstance(item, Pinjaman):
        print(item.label(), "->", item.pinjam())

print(koleksi[0].kembalikan())
print("isinstance ebook->Pinjaman:", isinstance(koleksi[1], Pinjaman))
# Pinjaman()  # TypeError — ABC tidak diinstansiasi
</code></pre>

<p>Output yang diharapkan (kurang lebih):</p>
<pre><code>BukuFisik -> pinjam fisik ESP32 Praktis · sisa 1
EbookLisensi -> aktifkan lisensi Belajar Python · sisa 0
kembali fisik ESP32 Praktis · stok 2
isinstance ebook->Pinjaman: True
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
      <td><code>TypeError: Can't instantiate abstract class...</code></td>
      <td>Subclass belum mengisi semua <code>@abstractmethod</code></td>
      <td>Implementasikan method yang disebut di pesan error</td>
    </tr>
    <tr>
      <td>Instansiasi ABC langsung</td>
      <td>Mengira ABC adalah class bisnis</td>
      <td>ABC = kontrak; buat subclass konkret</td>
    </tr>
    <tr>
      <td>Lupa <code>from abc import ...</code></td>
      <td>Menulis class biasa tanpa mekanisme abstract</td>
      <td>Pakai <code>ABC</code> + <code>@abstractmethod</code></td>
    </tr>
    <tr>
      <td>ABC berisi hampir semua logika</td>
      <td>Kontrak membengkak jadi induk gemuk</td>
      <td>Pindahkan detail ke anak; pertimbangkan composition</td>
    </tr>
    <tr>
      <td>Bingung ABC vs Encapsulation</td>
      <td>Menyamakan “sembunyikan detail” dengan “wajibkan method”</td>
      <td><a href="/artikel/encapsulation-property-python-oop">Encapsulation (#43)</a> jaga akses data; ABC jaga daftar kemampuan</td>
    </tr>
  </tbody>
</table>

<h2>Latihan singkat</h2>
<ol>
  <li>Tambah <code>class MajalahPinjam(Pinjaman)</code> dengan edisi + stok; isi kedua abstract method; masukkan ke <code>koleksi</code>.</li>
  <li>Sengaja hapus <code>kembalikan</code> di satu subclass — amati <code>TypeError</code>, lalu perbaiki.</li>
  <li>Tambah method konkret <code>status()</code> di <code>Pinjaman</code> yang dibaca semua anak tanpa di-override.</li>
</ol>

<h2>FAQ singkat</h2>
<p><strong>Apakah ABC menggantikan duck typing?</strong><br>Tidak. Duck typing tetap sah untuk kontrak kecil/lokal — object seperti <code>EntriDuck</code> tetap bisa dipanggil meski <code>isinstance(..., Pinjaman)</code> False. ABC berguna saat banyak class (atau tim) harus menjamin kemampuan yang sama lewat kontrak resmi.</p>
<p><strong>Apa bedanya dengan <a href="/artikel/encapsulation-property-python-oop">Encapsulation (#43)</a>?</strong><br>Encapsulation mengatur akses state (<code>@property</code>, validasi). ABC mengatur <em>daftar method wajib</em> antar class.</p>
<p><strong>Kenapa <code>isinstance(x, Pinjaman)</code> True untuk anak?</strong><br>Karena pewarisan formal ke ABC — mirip <code>isinstance(ebook, Buku)</code> di <a href="/artikel/inheritance-pewarisan-class-python">Inheritance (#44)</a>. Di sini cek kontrak, bukan hutan perilaku.</p>
<p><strong>Apakah wajib pakai <code>...</code> di body abstract?</strong><br>Tidak. <code>pass</code> atau <code>raise NotImplementedError</code> juga umum. Yang penting: dekorator <code>@abstractmethod</code> terpasang.</p>
<p><strong>Cukup <code>@abstractmethod</code> tanpa mewarisi <code>ABC</code>?</strong><br>Tidak. Dekorator saja tidak menegakkan kontrak. Class induk harus mewarisi <code>ABC</code> (seperti <code>class Pinjaman(ABC)</code>) agar Python menolak subclass yang belum lengkap.</p>
<p><strong>Bagaimana dengan <code>typing.Protocol</code>?</strong><br>Protocol (structural typing) mirip “duck typing bertipe”. Untuk seri ini kita fokus <code>abc</code> dulu — cukup untuk kontrak runtime yang jelas.</p>

<h2>Kesimpulan &amp; langkah berikutnya</h2>
<p>Abstraction lewat ABC mengubah janji lisan (“harusnya ada <code>pinjam</code>”) menjadi kontrak yang ditegakkan Python. Gabungkan dengan polymorphism: loop memanggil method kontrak; tiap class mengisi caranya sendiri.</p>
<p>Artikel ini adalah <strong>#46 (ini)</strong> — langkah ketujuh Seri 3 setelah <a href="/artikel/polymorphism-python-oop">Polymorphism (#45)</a>.</p>
<p>Lanjut ke <a href="/artikel/composition-vs-inheritance-python">Composition vs Inheritance (#47)</a>: kapan “punya” (has-a) lebih jujur daripada “adalah” (is-a), dan anti-pattern inheritance hanya demi reuse.</p>

<blockquote>
  <p><strong>Seri 3 progress:</strong> 10/10 artikel live. Kamu di langkah <strong>#46 (ini)</strong>. Prasyarat: <a href="/artikel/polymorphism-python-oop">Polymorphism (#45)</a> · <a href="/artikel/inheritance-pewarisan-class-python">Inheritance (#44)</a> · <a href="/artikel/encapsulation-property-python-oop">Encapsulation (#43)</a> · <a href="/artikel/attribute-method-constructor-init-python">Attribute (#42)</a> · <a href="/artikel/class-dan-object-pertama-python">Class &amp; Object (#41)</a> · <a href="/artikel/mengenal-oop-cara-berpikir-dengan-objek-python">Mengenal OOP (#40)</a> · lanjut: <a href="/artikel/composition-vs-inheritance-python">Composition (#47)</a>.</p>
</blockquote>
HTML;
    }
}
