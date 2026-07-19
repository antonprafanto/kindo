<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article48Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $progCat = Category::where('slug', 'programming')->first();

        if (! $admin || ! $progCat) {
            throw new \RuntimeException('User atau kategori programming tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'special-methods-dataclass-python';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        foreach ([
            'oop' => 'oop',
            'dataclass' => 'dataclass',
            'python' => 'python',
        ] as $tagSlug => $tagName) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagName]);
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $progCat->id,
                'title'           => 'Special Methods & Dataclass: __str__, __repr__, __eq__ di Python OOP',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'Special Methods & Dataclass Python OOP — __str__ __repr__ __eq__',
                'seo_description' => 'Pelajari special methods Python: __str__, __repr__, __eq__, lalu @dataclass untuk class data-heavy — Seri 3 OOP berbahasa Indonesia.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['python', 'oop', 'dataclass'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel ke-48 berhasil dipublish: '.$article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — object yang “bicara” saat di-print</h2>
<p>Di <a href="/artikel/attribute-method-constructor-init-python">Attribute &amp; <code>__init__</code> (#42)</a> kita isi state. Di <a href="/artikel/composition-vs-inheritance-python">Composition (#47)</a> kita susun siapa punya siapa. Hari ini: bagaimana object menampilkan diri dan dibandingkan — lewat <strong>special methods</strong> (kadang disebut <em>dunder method</em>: nama ber-underscore ganda) seperti <code>__str__</code>, <code>__repr__</code>, <code>__eq__</code>, plus shortcut modern <code>@dataclass</code>.</p>
<p>Tanpa special method, <code>print(buku)</code> sering hanya alamat memori yang sulit dibaca. Dengan special method, object ikut “sopan” di log, debugger, dan koleksi.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> Nyaman dengan <a href="/artikel/attribute-method-constructor-init-python">Attribute (#42)</a> dan sudah baca <a href="/artikel/composition-vs-inheritance-python">Composition (#47)</a>. Fondasi: <a href="/artikel/encapsulation-property-python-oop">Encapsulation (#43)</a> · <a href="/artikel/class-dan-object-pertama-python">Class &amp; Object (#41)</a> · <a href="/artikel/mengenal-oop-cara-berpikir-dengan-objek-python">Mengenal OOP (#40)</a>.</p>
</blockquote>

<h2>Default yang kurang ramah</h2>
<p>Class sederhana tanpa special method:</p>

<pre><code class="language-python">class Buku:
    def __init__(self, judul, penulis):
        self.judul = judul
        self.penulis = penulis


b = Buku("ESP32 Praktis", "Budi")
print(b)
print([b])
</code></pre>

<p>Output kurang lebih (alamat hex berbeda tiap jalankan):</p>
<pre><code>&lt;__main__.Buku object at 0x...&gt;
[&lt;__main__.Buku object at 0x...&gt;]
</code></pre>
<p>Itu bukan bug — Python belum tahu cara menampilkan objectmu. Kita ajarkan lewat special method.</p>

<h2><code>__str__</code> — untuk manusia</h2>
<p><code>__str__</code> dipanggil oleh <code>str(obj)</code> dan <code>print(obj)</code>. Isinya sebaiknya singkat dan mudah dibaca orang.</p>

<pre><code class="language-python">class Buku:
    def __init__(self, judul, penulis):
        self.judul = judul
        self.penulis = penulis

    def __str__(self):
        return f"{self.judul} oleh {self.penulis}"


b = Buku("ESP32 Praktis", "Budi")
print(b)
print(str(b))
</code></pre>

<p>Output:</p>
<pre><code>ESP32 Praktis oleh Budi
ESP32 Praktis oleh Budi
</code></pre>

<h2><code>__repr__</code> — untuk developer</h2>
<p><code>__repr__</code> dipakai saat object muncul di list/dict, REPL, dan log debug. Idealnya jelas dan (bila masuk akal) mirip cara membuat ulang object.</p>

<pre><code class="language-python">class Buku:
    def __init__(self, judul, penulis):
        self.judul = judul
        self.penulis = penulis

    def __str__(self):
        return f"{self.judul} oleh {self.penulis}"

    def __repr__(self):
        return f"Buku(judul={self.judul!r}, penulis={self.penulis!r})"


b = Buku("ESP32 Praktis", "Budi")
print(b)        # __str__
print([b])      # __repr__ di dalam list
print(repr(b))  # __repr__ eksplisit
</code></pre>

<p>Output:</p>
<pre><code>ESP32 Praktis oleh Budi
[Buku(judul='ESP32 Praktis', penulis='Budi')]
Buku(judul='ESP32 Praktis', penulis='Budi')
</code></pre>
<p>Kalau hanya <code>__repr__</code> yang ada, <code>print</code> juga memakainya sebagai fallback. Kalau hanya <code>__str__</code>, list masih memakai default alamat memori — jadi sering tulis keduanya.</p>

<figure role="img" aria-label="Diagram special methods: print memakai __str__, list/debug memakai __repr__" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 280" style="display:block;max-width:720px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="oop48Arrow" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/></marker>
  </defs>
  <rect x="0" y="0" width="720" height="280" fill="#F5F5F0" rx="6"/>
  <rect x="40" y="30" width="200" height="70" rx="6" fill="#1a1a1a" stroke="#000" stroke-width="2.5"/>
  <text x="140" y="72" text-anchor="middle" fill="#fff" font-size="16" font-weight="700">print(buku)</text>
  <rect x="40" y="170" width="200" height="70" rx="6" fill="#1a1a1a" stroke="#000" stroke-width="2.5"/>
  <text x="140" y="212" text-anchor="middle" fill="#fff" font-size="16" font-weight="700">[buku] / debug</text>
  <rect x="420" y="30" width="260" height="70" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="550" y="72" text-anchor="middle" fill="#1a1a1a" font-size="16" font-weight="700">__str__</text>
  <rect x="420" y="170" width="260" height="70" rx="6" fill="#FFF3E8" stroke="#000" stroke-width="2.5"/>
  <text x="550" y="212" text-anchor="middle" fill="#1a1a1a" font-size="16" font-weight="700">__repr__</text>
  <line x1="240" y1="65" x2="420" y2="65" stroke="#2979FF" stroke-width="2.5" marker-end="url(#oop48Arrow)"/>
  <line x1="240" y1="205" x2="420" y2="205" stroke="#2979FF" stroke-width="2.5" marker-end="url(#oop48Arrow)"/>
</svg>
<figcaption style="margin-top:.75rem;color:#2D3748;font-size:.95rem">Aturan praktis: manusia baca <code>__str__</code>; developer/debug baca <code>__repr__</code>.</figcaption>
</figure>

<h2><code>__eq__</code> — kapan dua object “sama”?</h2>
<p>Tanpa <code>__eq__</code>, dua instance dengan data sama tetap <code>!=</code> karena dibandingkan identitas (alamat), bukan isi:</p>

<pre><code class="language-python">class Buku:
    def __init__(self, judul, penulis):
        self.judul = judul
        self.penulis = penulis


a = Buku("ESP32 Praktis", "Budi")
b = Buku("ESP32 Praktis", "Budi")
print("tanpa __eq__, a == b?", a == b)
print("a is b?", a is b)
</code></pre>

<p>Output:</p>
<pre><code>tanpa __eq__, a == b? False
a is b? False
</code></pre>
<p>Sekarang tambahkan <code>__eq__</code> supaya perbandingan memakai nilai field:</p>

<pre><code class="language-python">class Buku:
    def __init__(self, judul, penulis):
        self.judul = judul
        self.penulis = penulis

    def __eq__(self, other):
        if not isinstance(other, Buku):
            return NotImplemented
        return self.judul == other.judul and self.penulis == other.penulis


a = Buku("ESP32 Praktis", "Budi")
b = Buku("ESP32 Praktis", "Budi")
c = Buku("Belajar Python", "Sari")
print("a == b?", a == b)
print("a == c?", a == c)
print("a is b?", a is b)
</code></pre>

<p>Output:</p>
<pre><code>a == b? True
a == c? False
a is b? False
</code></pre>
<p><code>==</code> soal kesetaraan nilai; <code>is</code> soal object yang sama di memori. Untuk koleksi di <a href="/artikel/composition-vs-inheritance-python">Composition (#47)</a>, <code>__eq__</code> membantu cek “buku sudah ada di katalog?” tanpa bergantung pada identitas instance.</p>

<h2><code>@dataclass</code> — class data tanpa boilerplate</h2>
<p>Banyak class hanya menyimpan field + ingin <code>__init__</code>, <code>__repr__</code>, dan <code>__eq__</code> otomatis. Modul <code>dataclasses</code> (stdlib) mengurangi boilerplate itu.</p>

<pre><code class="language-python">from dataclasses import dataclass


@dataclass
class Buku:
    judul: str
    penulis: str
    tahun: int = 2024


b1 = Buku("ESP32 Praktis", "Budi", 2023)
b2 = Buku("ESP32 Praktis", "Budi", 2023)
print(b1)
print("b1 == b2?", b1 == b2)
print("repr:", repr(b1))
</code></pre>

<p>Output kurang lebih:</p>
<pre><code>Buku(judul='ESP32 Praktis', penulis='Budi', tahun=2023)
b1 == b2? True
repr: Buku(judul='ESP32 Praktis', penulis='Budi', tahun=2023)
</code></pre>
<p>Catatan: default <code>@dataclass</code> memakai <code>__repr__</code> bergaya field; kalau mau teks ramah manusia terpisah, tetap tulis <code>__str__</code> sendiri.</p>

<pre><code class="language-python">from dataclasses import dataclass


@dataclass
class Buku:
    judul: str
    penulis: str

    def __str__(self):
        return f"{self.judul} oleh {self.penulis}"


print(Buku("Belajar Python", "Sari"))
print([Buku("Belajar Python", "Sari")])
</code></pre>

<p>Output:</p>
<pre><code>Belajar Python oleh Sari
[Buku(judul='Belajar Python', penulis='Sari')]
</code></pre>

<h2>Class biasa vs dataclass — kapan pilih apa?</h2>
<ul>
  <li><strong>Dataclass</strong> — banyak field, sedikit perilaku khusus; ingin <code>__init__</code>/<code>__repr__</code>/<code>__eq__</code> otomatis</li>
  <li><strong>Class biasa</strong> — validasi kompleks di <code>__init__</code>, banyak method bisnis, atau warisan/ABC yang sudah ramai (lihat <a href="/artikel/abstraction-abc-python-oop">ABC (#46)</a>)</li>
  <li><strong>Campur</strong> — dataclass untuk model data; class biasa untuk layanan yang <em>punya</em> koleksi model (<a href="/artikel/composition-vs-inheritance-python">Composition (#47)</a>)</li>
</ul>
<p>Dataclass bukan “pengganti OOP”. Ia mengurangi boilerplate di class yang memang berpusat pada data.</p>

<h2>Pola Dasar — special methods &amp; dataclass</h2>
<figure style="margin:1.5rem 0;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1.25rem;color:#1a1a1a" aria-label="Lima langkah special methods dan dataclass">
<ol style="list-style:none;padding:0;margin:0;color:#1a1a1a">
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Tulis <code>__str__</code> dulu</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Kalau object sering di-print ke log/UI, buat teks singkat untuk manusia.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Tambah <code>__repr__</code> untuk debug</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Tampilkan nama class + field penting; <code>!r</code> membantu string ber-quote.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#FF7A2F;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Definisikan <code>__eq__</code> bila nilai penting</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Bandingkan field yang masuk akal; kembalikan <code>NotImplemented</code> untuk tipe asing.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#FF7A2F;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Pertimbangkan <code>@dataclass</code></strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Field banyak + perilaku tipis? Biarkan stdlib generate boilerplate.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#1a1a1a;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">5</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Jangan paksa dataclass ke mana-mana</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Layanan kompleks, validasi ketat, atau API tebal — class biasa (atau property) sering lebih jujur.</span>
    </div>
  </li>
</ol>
<figcaption style="margin-top:1rem;color:#2D3748;font-size:.95rem">Ringkas: <code>__str__</code> manusia · <code>__repr__</code> debug · <code>__eq__</code> nilai · dataclass untuk data-heavy.</figcaption>
</figure>

<h2>Kode lengkap — salin &amp; jalankan</h2>
<p>Simpan sebagai <code>buku_special_methods.py</code>, lalu <code>python buku_special_methods.py</code>:</p>

<pre><code class="language-python">from dataclasses import dataclass


@dataclass
class Buku:
    judul: str
    penulis: str
    tahun: int

    def __str__(self):
        return f"{self.judul} · {self.penulis} ({self.tahun})"


class Katalog:
    """Composition: punya daftar Buku (dataclass)."""

    def __init__(self, nama):
        self.nama = nama
        self.items = []

    def tambah(self, buku):
        if buku in self.items:
            return False
        self.items.append(buku)
        return True

    def __str__(self):
        return f"Katalog {self.nama}: {len(self.items)} buku"

    def __repr__(self):
        return f"Katalog(nama={self.nama!r}, items={self.items!r})"


kat = Katalog("Kota A")
b1 = Buku("ESP32 Praktis", "Budi", 2023)
b2 = Buku("ESP32 Praktis", "Budi", 2023)  # nilai sama, instance beda
b3 = Buku("Belajar Python", "Sari", 2024)

print(b1)
print("tambah b1:", kat.tambah(b1))
print("tambah b2 (duplikat nilai):", kat.tambah(b2))
print("tambah b3:", kat.tambah(b3))
print(kat)
print("items:", kat.items)
print("b1 == b2?", b1 == b2)
</code></pre>

<p>Output yang diharapkan (kurang lebih):</p>
<pre><code>ESP32 Praktis · Budi (2023)
tambah b1: True
tambah b2 (duplikat nilai): False
tambah b3: True
Katalog Kota A: 2 buku
items: [Buku(judul='ESP32 Praktis', penulis='Budi', tahun=2023), Buku(judul='Belajar Python', penulis='Sari', tahun=2024)]
b1 == b2? True
</code></pre>
<p>Di sini dataclass memberi <code>__eq__</code> otomatis; <code>Katalog</code> class biasa memakai composition + <code>__str__</code>/<code>__repr__</code> sendiri — pola yang sering muncul di proyek nyata.</p>

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
      <td><code>print</code> masih alamat memori</td>
      <td>Belum ada <code>__str__</code>/<code>__repr__</code></td>
      <td>Tambah minimal salah satu; idealnya keduanya</td>
    </tr>
    <tr>
      <td>List menampilkan jelek meski <code>print</code> bagus</td>
      <td>Hanya <code>__str__</code>, tanpa <code>__repr__</code></td>
      <td>Tulis <code>__repr__</code> juga</td>
    </tr>
    <tr>
      <td>Dua buku “sama” tapi <code>==</code> False</td>
      <td>Belum <code>__eq__</code> (atau bukan dataclass)</td>
      <td>Implement <code>__eq__</code> atau pakai <code>@dataclass</code></td>
    </tr>
    <tr>
      <td>Bingung <code>==</code> vs <code>is</code></td>
      <td>Menyamakan kesetaraan nilai dengan identitas</td>
      <td><code>==</code> nilai; <code>is</code> object yang sama di memori</td>
    </tr>
    <tr>
      <td>Dataclass dipakai untuk service tebal</td>
      <td>Memaksa shortcut ke class yang banyak side-effect</td>
      <td>Class biasa + composition; dataclass untuk model data</td>
    </tr>
    <tr>
      <td><code>TypeError: unhashable type</code> setelah tulis <code>__eq__</code></td>
      <td>Custom <code>__eq__</code> membuat object tidak hashable (default)</td>
      <td>Untuk Seri 3: bandingkan dengan <code>==</code> / <code>in</code> list. Jangan masukkan ke <code>set</code>/<code>dict</code> key sebelum paham <code>__hash__</code></td>
    </tr>
  </tbody>
</table>

<h2>Latihan singkat</h2>
<ol>
  <li>Tambah <code>__str__</code> pada class <code>Ebook</code> (lihat pola di <a href="/artikel/inheritance-pewarisan-class-python">Inheritance (#44)</a>) yang menampilkan format file.</li>
  <li>Ubah <code>Katalog.tambah</code> agar menolak duplikat berdasarkan <code>judul</code> saja (bukan seluruh field).</li>
  <li>Bandingkan: class <code>Buku</code> manual dengan <code>__init__</code>/<code>__repr__</code>/<code>__eq__</code> vs versi <code>@dataclass</code> — mana yang lebih pendek?</li>
</ol>

<h2>FAQ singkat</h2>
<p><strong>Apakah harus selalu tulis <code>__str__</code> dan <code>__repr__</code>?</strong><br>Tidak wajib, tapi sangat membantu begitu object sering di-print atau debug. Dataclass sudah memberi <code>__repr__</code> default.</p>
<p><strong>Apa bedanya dengan <a href="/artikel/encapsulation-property-python-oop">Encapsulation (#43)</a>?</strong><br>Encapsulation mengatur akses/validasi field. Special methods mengatur bagaimana object ditampilkan dan dibandingkan.</p>
<p><strong>Apakah dataclass bisa inheritance?</strong><br>Bisa, dengan hati-hati (urutan field default). Untuk belajar Seri 3, mulai dari dataclass “datar” dulu.</p>
<p><strong>Kenapa kembalikan <code>NotImplemented</code> di <code>__eq__</code>?</strong><br>Supaya Python bisa mencoba perbandingan dari sisi object lain, bukan langsung False yang menyesatkan.</p>
<p><strong>Kenapa muncul <code>unhashable type</code> setelah <code>__eq__</code>?</strong><br>Python menonaktifkan hash default bila kamu tulis <code>__eq__</code>. Untuk katalog di list, <code>buku in items</code> memakai <code>==</code> — aman. <code>set</code>/<code>dict</code> key butuh <code>__hash__</code> konsisten (topik lanjutan).</p>
<p><strong>Apakah ini menggantikan <a href="/artikel/composition-vs-inheritance-python">Composition (#47)</a>?</strong><br>Tidak. Special methods membuat tiap object lebih sopan; composition tetap menjawab “siapa punya siapa”.</p>

<h2>Kesimpulan &amp; langkah berikutnya</h2>
<p><code>__str__</code> untuk manusia, <code>__repr__</code> untuk debug, <code>__eq__</code> untuk kesetaraan nilai. <code>@dataclass</code> memotong boilerplate class data-heavy — tetap kombinasikan dengan composition untuk layanan yang mengelola koleksi.</p>
<p>Artikel ini adalah <strong>#48 (ini)</strong> — langkah kesembilan Seri 3 setelah <a href="/artikel/composition-vs-inheritance-python">Composition vs Inheritance (#47)</a>.</p>
<p>Lanjut ke <a href="/artikel/capstone-sistem-perpustakaan-mini-oop-python">Capstone: Sistem Perpustakaan Mini (#49)</a>: gabungkan class, encapsulation, inheritance, polymorphism, ABC, composition, dan special methods dalam satu CLI kecil.</p>

<blockquote>
  <p><strong>Seri 3 progress:</strong> 10/10 artikel live. Kamu di langkah <strong>#48 (ini)</strong>. Prasyarat: <a href="/artikel/composition-vs-inheritance-python">Composition (#47)</a> · <a href="/artikel/attribute-method-constructor-init-python">Attribute (#42)</a> · <a href="/artikel/encapsulation-property-python-oop">Encapsulation (#43)</a> · <a href="/artikel/abstraction-abc-python-oop">Abstraction (#46)</a> · <a href="/artikel/polymorphism-python-oop">Polymorphism (#45)</a> · <a href="/artikel/inheritance-pewarisan-class-python">Inheritance (#44)</a> · <a href="/artikel/class-dan-object-pertama-python">Class &amp; Object (#41)</a> · <a href="/artikel/mengenal-oop-cara-berpikir-dengan-objek-python">Mengenal OOP (#40)</a>.</p>
</blockquote>
HTML;
    }
}
