<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article44Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $progCat = Category::where('slug', 'programming')->first();

        if (! $admin || ! $progCat) {
            throw new \RuntimeException('User atau kategori programming tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'inheritance-pewarisan-class-python';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        foreach ([
            'oop' => 'oop',
            'inheritance' => 'inheritance',
            'python' => 'python',
        ] as $tagSlug => $tagName) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagName]);
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $progCat->id,
                'title'           => 'Inheritance dan super() di Python OOP',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'Inheritance & super() Python — Tutorial OOP Pemula',
                'seo_description' => 'Pelajari pewarisan class di Python: class Ebook(Buku), super().__init__, dan override method info() — lanjutan Seri 3 OOP berbahasa Indonesia.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['python', 'oop', 'inheritance'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel ke-44 berhasil dipublish: '.$article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — mewarisi tanpa menyalin-tempel</h2>
<p>Di <a href="/artikel/encapsulation-property-python-oop">Encapsulation &amp; <code>@property</code> (#43)</a> class <code>Buku</code> sudah punya state terlindungi. Hari ini kita buat <strong>variasi</strong> tanpa menduplikasi seluruh class: ebook, audiobook, atau edisi khusus yang “mirip buku” tapi punya data ekstra.</p>
<p><strong>Inheritance</strong> (pewarisan) = class anak mewarisi attribute &amp; method class induk, lalu boleh menambah atau meng-override yang perlu. Di Python: <code>class Ebook(Buku):</code>.</p>
<p><strong>Catatan:</strong> di artikel ini <code>Buku</code> disederhanakan (attribute publik, tanpa <code>@property</code>) supaya pola <code>super()</code> dan override tidak tertutup detail validasi. Di proyek nyata, inheritance + encapsulation boleh digabung.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> Selesai <a href="/artikel/encapsulation-property-python-oop">Encapsulation (#43)</a> (atau setidaknya nyaman dengan class, <code>__init__</code>, method). Fondasi: <a href="/artikel/attribute-method-constructor-init-python">Attribute &amp; <code>__init__</code> (#42)</a> · <a href="/artikel/class-dan-object-pertama-python">Class &amp; Object (#41)</a> · <a href="/artikel/mengenal-oop-cara-berpikir-dengan-objek-python">Mengenal OOP (#40)</a>.</p>
</blockquote>

<h2>Kenapa inheritance?</h2>
<p>Tanpa pewarisan, kamu cenderung copy-paste <code>Buku</code> jadi <code>Ebook</code>, lalu bug perbaikan harus diubah di dua tempat. Dengan inheritance:</p>
<ul>
  <li>Aturan bersama (mis. <code>pinjam()</code>) tinggal di induk</li>
  <li>Anak hanya menambah yang beda (format file, ukuran MB)</li>
  <li>Override method kalau perilaku perlu berbeda (mis. teks <code>info()</code>)</li>
</ul>
<p>Jangan warisi hanya demi “hemat mengetik” — warisi karena memang <em>adalah-sejenis</em> (ebook <em>adalah</em> buku digital). Pola “punya” daftar object lain: lihat <a href="/artikel/composition-vs-inheritance-python">Composition vs Inheritance (#47)</a>.</p>

<h2>Class induk Buku (titik berangkat)</h2>
<p>Kita pakai versi ringkas yang fokus ke pewarisan. Validasi property tetap boleh seperti di <a href="/artikel/encapsulation-property-python-oop">Encapsulation (#43)</a>; di sini disederhanakan agar pola <code>super()</code> terlihat jelas:</p>

<pre><code class="language-python">class Buku:
    def __init__(self, judul, penulis, tahun, stok=1):
        self.judul = judul
        self.penulis = penulis
        self.tahun = tahun
        self.stok = stok

    def info(self):
        return f"{self.judul} · {self.penulis} ({self.tahun}) · stok {self.stok}"

    def pinjam(self):
        if self.stok &lt;= 0:
            raise ValueError("stok habis")
        self.stok -= 1
        return self.stok


b = Buku("Belajar Python", "Sari", 2024, stok=2)
print(b.info())
</code></pre>

<h2>class Ebook(Buku) — anak mewarisi induk</h2>
<p>Sintaks: nama induk di dalam kurung. Instance <code>Ebook</code> bisa memakai method induk langsung:</p>

<pre><code class="language-python">class Buku:
    def __init__(self, judul, penulis, tahun, stok=1):
        self.judul = judul
        self.penulis = penulis
        self.tahun = tahun
        self.stok = stok

    def info(self):
        return f"{self.judul} · {self.penulis} ({self.tahun}) · stok {self.stok}"

    def pinjam(self):
        if self.stok &lt;= 0:
            raise ValueError("stok habis")
        self.stok -= 1
        return self.stok


class Ebook(Buku):
    """Ebook adalah Buku + metadata file."""
    pass


e = Ebook("Belajar Python", "Sari", 2024, stok=1)
print(e.info())          # diwarisi dari Buku
print(e.pinjam())        # juga diwarisi
print(isinstance(e, Ebook))  # True
print(isinstance(e, Buku))   # True — ebook juga "adalah" Buku
</code></pre>

<p><code>pass</code> di tubuh class berarti “belum menambah apa-apa”. Berguna untuk memahami: anak kosong tetap bisa lahir lewat <code>__init__</code> induk.</p>

<h2>super().__init__ — menyiapkan state induk</h2>
<p>Kalau anak punya <code>__init__</code> sendiri, method itu <strong>menggantikan</strong> <code>__init__</code> induk — Python tidak memanggil induk otomatis. Karena itu kamu hampir selalu perlu memanggil <code>__init__</code> induk lewat <code>super()</code> agar attribute dasar ikut terisi:</p>

<pre><code class="language-python">class Buku:
    def __init__(self, judul, penulis, tahun, stok=1):
        self.judul = judul
        self.penulis = penulis
        self.tahun = tahun
        self.stok = stok

    def info(self):
        return f"{self.judul} · {self.penulis} ({self.tahun}) · stok {self.stok}"


class Ebook(Buku):
    def __init__(self, judul, penulis, tahun, stok=1, format_file="epub", ukuran_mb=1.0):
        super().__init__(judul, penulis, tahun, stok)
        self.format_file = format_file
        self.ukuran_mb = ukuran_mb


e = Ebook("Belajar Python", "Sari", 2024, stok=1, format_file="pdf", ukuran_mb=4.5)
print(e.judul, e.format_file, e.ukuran_mb)
print(e.info())  # masih pakai info() milik Buku
</code></pre>

<p>Tanpa <code>super().__init__(...)</code>, attribute seperti <code>judul</code> / <code>stok</code> sering belum ada — lalu muncul <code>AttributeError</code> saat method induk dijalankan. Bandingkan pola SALAH vs BENAR:</p>

<pre><code class="language-python">class Buku:
    def __init__(self, judul, penulis, tahun, stok=1):
        self.judul = judul
        self.penulis = penulis
        self.tahun = tahun
        self.stok = stok

    def info(self):
        return f"{self.judul} · {self.penulis} ({self.tahun}) · stok {self.stok}"


# SALAH — anak punya __init__ tapi lupa super()
class EbookSalah(Buku):
    def __init__(self, judul, penulis, tahun, stok=1, format_file="epub"):
        # lupa: super().__init__(judul, penulis, tahun, stok)
        self.format_file = format_file


# e = EbookSalah("Belajar Python", "Sari", 2024)  # object lahir
# print(e.info())  # AttributeError: 'EbookSalah' object has no attribute 'judul'


# BENAR
class EbookBenar(Buku):
    def __init__(self, judul, penulis, tahun, stok=1, format_file="epub"):
        super().__init__(judul, penulis, tahun, stok)
        self.format_file = format_file


e = EbookBenar("Belajar Python", "Sari", 2024, format_file="pdf")
print(e.judul, e.format_file)
print(e.info())
</code></pre>

<blockquote>
  <p><strong>Ingat:</strong> <code>super()</code> mencari implementasi di rantai induk (MRO). Untuk kasus satu induk seperti ini, anggap saja “panggil versi Buku”.</p>
</blockquote>

<h2>Override method info()</h2>
<p>Anak boleh menulis ulang method dengan nama sama. Versi anak yang dipanggil saat object-nya Ebook:</p>

<pre><code class="language-python">class Buku:
    def __init__(self, judul, penulis, tahun, stok=1):
        self.judul = judul
        self.penulis = penulis
        self.tahun = tahun
        self.stok = stok

    def info(self):
        return f"{self.judul} · {self.penulis} ({self.tahun}) · stok {self.stok}"


class Ebook(Buku):
    def __init__(self, judul, penulis, tahun, stok=1, format_file="epub", ukuran_mb=1.0):
        super().__init__(judul, penulis, tahun, stok)
        self.format_file = format_file
        self.ukuran_mb = ukuran_mb

    def info(self):
        dasar = super().info()  # manfaatkan teks induk
        return f"{dasar} · {self.format_file.upper()} ({self.ukuran_mb} MB)"


b = Buku("ESP32 Praktis", "Budi", 2023)
e = Ebook("Belajar Python", "Sari", 2024, format_file="pdf", ukuran_mb=4.5)
print(b.info())
print(e.info())
</code></pre>

<p>Pola sehat: override untuk <em>melengkapi</em>, bukan menyalin ulang seluruh logika induk. <code>super().info()</code> menjaga satu sumber kebenaran untuk bagian yang sama. Object <code>Buku</code> tetap memakai versi induk; object <code>Ebook</code> memakai versi anak.</p>
<p>Di dalam method anak, <code>self</code> tetap object <code>Ebook</code> — itu kenapa <code>self.format_file</code> tersedia setelah <code>super().info()</code>. Kalau kamu menulis ulang <code>info()</code> dari nol tanpa <code>super()</code>, teks dasar induk ikut terduplikasi; perbaikan format di <code>Buku.info()</code> tidak otomatis ikut ke anak.</p>

<figure role="img" aria-label="Diagram inheritance: class Ebook dan Audiobook mewarisi Buku" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 300" style="display:block;max-width:720px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="oop44Arrow" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/></marker>
  </defs>
  <rect x="0" y="0" width="720" height="300" fill="#F5F5F0" rx="6"/>
  <rect x="220" y="30" width="280" height="90" rx="6" fill="#2979FF" stroke="#000" stroke-width="2.5"/>
  <text x="360" y="65" text-anchor="middle" fill="#fff" font-size="16" font-weight="700">Buku (induk)</text>
  <text x="360" y="95" text-anchor="middle" fill="#e3f2fd" font-size="13">__init__ · info · pinjam</text>
  <rect x="80" y="180" width="240" height="90" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="200" y="215" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Ebook (anak)</text>
  <text x="200" y="245" text-anchor="middle" fill="#2D3748" font-size="12">+ format_file · override info</text>
  <rect x="400" y="180" width="240" height="90" rx="6" fill="#FFF3E8" stroke="#000" stroke-width="2.5"/>
  <text x="520" y="215" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Audiobook (latihan)</text>
  <text x="520" y="245" text-anchor="middle" fill="#2D3748" font-size="12">+ durasi_menit · override info</text>
  <line x1="300" y1="120" x2="200" y2="180" stroke="#2979FF" stroke-width="2.5" marker-end="url(#oop44Arrow)"/>
  <line x1="420" y1="120" x2="520" y2="180" stroke="#2979FF" stroke-width="2.5" marker-end="url(#oop44Arrow)"/>
  <text x="360" y="155" text-anchor="middle" fill="#2D3748" font-size="12" font-weight="600">mewarisi</text>
</svg>
<figcaption style="margin-top:.75rem;color:#2D3748;font-size:.95rem">Satu induk, banyak anak. <code>Ebook</code> dibahas di artikel; <code>Audiobook</code> kamu kerjakan di latihan.</figcaption>
</figure>

<h2>Pola Dasar — merancang inheritance</h2>
<figure style="margin:1.5rem 0;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1.25rem;color:#1a1a1a" aria-label="Lima langkah merancang inheritance dengan super">
<ol style="list-style:none;padding:0;margin:0;color:#1a1a1a">
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Pastikan “adalah-sejenis”</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Ebook <em>adalah</em> Buku. Kalau hanya “punya” buku, jangan inheritance.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Tulis class Anak(Induk)</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem"><code>class Ebook(Buku):</code> — nama induk di kurung.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#FF7A2F;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Panggil super().__init__</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Isi state bersama dulu, baru attribute khusus anak.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#FF7A2F;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Override secukupnya</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Ubah <code>info()</code> / perilaku yang benar-benar beda; sisanya warisi.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#1a1a1a;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">5</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Uji induk dan anak</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Pastikan <code>Buku</code> lama tidak rusak; <code>isinstance(e, Buku)</code> masuk akal.</span>
    </div>
  </li>
</ol>
<figcaption style="margin-top:1rem;color:#2D3748;font-size:.95rem">Ringkas: adalah-sejenis → Anak(Induk) → super() → override → uji.</figcaption>
</figure>

<h2>Kode lengkap — salin &amp; jalankan</h2>
<p>Simpan sebagai <code>perpustakaan_ebook.py</code>, lalu <code>python perpustakaan_ebook.py</code>:</p>

<pre><code class="language-python">class Buku:
    def __init__(self, judul, penulis, tahun, stok=1):
        self.judul = judul
        self.penulis = penulis
        self.tahun = tahun
        self.stok = stok

    def info(self):
        return f"{self.judul} · {self.penulis} ({self.tahun}) · stok {self.stok}"

    def pinjam(self):
        if self.stok &lt;= 0:
            raise ValueError("stok habis")
        self.stok -= 1
        return self.stok


class Ebook(Buku):
    def __init__(self, judul, penulis, tahun, stok=1, format_file="epub", ukuran_mb=1.0):
        super().__init__(judul, penulis, tahun, stok)
        self.format_file = format_file
        self.ukuran_mb = ukuran_mb

    def info(self):
        return f"{super().info()} · {self.format_file.upper()} ({self.ukuran_mb} MB)"


b = Buku("ESP32 Praktis", "Budi", 2023, stok=2)
e = Ebook("Belajar Python", "Sari", 2024, stok=1, format_file="pdf", ukuran_mb=4.5)

print(b.info())
print(e.info())
print("pinjam ebook:", e.pinjam())
print("isinstance ebook→Buku:", isinstance(e, Buku))
print("type(e) is Buku:", type(e) is Buku)  # False — tipe persis tetap Ebook
</code></pre>

<p>Output yang diharapkan (kurang lebih):</p>
<pre><code>ESP32 Praktis · Budi (2023) · stok 2
Belajar Python · Sari (2024) · stok 1 · PDF (4.5 MB)
pinjam ebook: 0
isinstance ebook→Buku: True
type(e) is Buku: False
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
      <td><code>AttributeError: 'Ebook' object has no attribute 'judul'</code></td>
      <td>Anak punya <code>__init__</code> tapi lupa <code>super().__init__(...)</code></td>
      <td>Panggil <code>super().__init__(...)</code> sebelum pakai attribute induk</td>
    </tr>
    <tr>
      <td><code>TypeError: Buku.__init__() missing ...</code> dari anak</td>
      <td>Argumen ke <code>super().__init__</code> kurang / tertukar</td>
      <td>Samakan urutan parameter dengan induk</td>
    </tr>
    <tr>
      <td>Override menghilangkan perilaku induk</td>
      <td>Menulis ulang <code>info()</code> tanpa <code>super().info()</code></td>
      <td>Panggil <code>super()</code> lalu tambah detail anak</td>
    </tr>
    <tr>
      <td>Warisan terasa dipaksakan</td>
      <td>Anak tidak benar-benar “adalah” induk (hanya reuse field)</td>
      <td>Pertimbangkan <a href="/artikel/composition-vs-inheritance-python">composition (#47)</a> atau class terpisah</td>
    </tr>
    <tr>
      <td>Bingung <code>isinstance</code> vs tipe persis</td>
      <td>Mengira hanya <code>type(e) is Buku</code></td>
      <td><code>isinstance(e, Buku)</code> True untuk anak; detail polimorfisme di artikel berikutnya</td>
    </tr>
  </tbody>
</table>

<h2>Latihan singkat</h2>
<ol>
  <li>Buat <code>class Audiobook(Buku)</code> dengan attribute <code>durasi_menit</code>. Override <code>info()</code> supaya menampilkan durasi.</li>
  <li>Di <code>Ebook.__init__</code>, sengaja hapus <code>super().__init__</code> sebentar — amati error, lalu kembalikan.</li>
  <li>Buat list <code>[b, e]</code> dan cetak <code>item.info()</code> untuk tiap item. (Cuplikan — lihat <a href="/artikel/polymorphism-python-oop">Polymorphism (#45)</a>.)</li>
</ol>

<h2>FAQ singkat</h2>
<p><strong>Apakah anak otomatis mewarisi semua method?</strong><br>Ya, kecuali di-override. Attribute instance tetap lahir lewat <code>__init__</code> (biasanya lewat <code>super()</code>).</p>
<p><strong>Apakah <code>pinjam()</code> harus ditulis ulang di Ebook?</strong><br>Tidak, kalau perilakunya sama. Warisi saja. Override hanya jika ebook butuh aturan berbeda (mis. stok digital tak terbatas — itu keputusan desain nanti).</p>
<p><strong>Bolehkah banyak induk (multiple inheritance)?</strong><br>Boleh di Python, tapi untuk pemula: tahan dulu. Satu induk sudah cukup untuk fondasi.</p>
<p><strong>Haruskah selalu override info()?</strong><br>Tidak. Override hanya jika teks/perilaku induk kurang tepat untuk anak.</p>
<p><strong>Kenapa di sini Buku tanpa <code>@property</code>?</strong><br>Supaya fokus ke pewarisan. Setelah nyaman dengan <code>super()</code>, gabungkan lagi dengan pola <a href="/artikel/encapsulation-property-python-oop">Encapsulation (#43)</a>.</p>
<p><strong>Apakah <code>__nama</code> (name-mangling) ikut “rusak” di anak?</strong><br>Python mengubah <code>__nama</code> jadi <code>_NamaClass__nama</code> menurut class yang <em>mendefinisikan</em>-nya, jadi bentrok nama di subclass jarang terjadi. Untuk pemula: tetap utamakan <code>_</code> seperti di <a href="/artikel/encapsulation-property-python-oop">#43</a>; <code>__</code> jarang diperlukan.</p>

<h2>Kesimpulan &amp; langkah berikutnya</h2>
<p>Inheritance memakai hubungan “adalah-sejenis”: anak mewarisi induk, mengisi state lewat <code>super().__init__</code>, dan meng-override method bila perlu. Kamu menghindari copy-paste sekaligus menjaga satu tempat untuk aturan bersama.</p>
<p>Artikel ini adalah <strong>#44 (ini)</strong> — langkah kelima Seri 3 setelah <a href="/artikel/encapsulation-property-python-oop">Encapsulation (#43)</a>.</p>
<p>Lanjut ke <a href="/artikel/polymorphism-python-oop">Polymorphism (#45)</a>: daftar campuran <code>Buku</code> / <code>Ebook</code> diproses lewat interface yang sama, plus kapan <code>isinstance</code> boleh dipakai.</p>

<blockquote>
  <p><strong>Seri 3 progress:</strong> 8/10 artikel live. Kamu di langkah <strong>#44 (ini)</strong>. Prasyarat: <a href="/artikel/encapsulation-property-python-oop">Encapsulation (#43)</a> · <a href="/artikel/attribute-method-constructor-init-python">Attribute (#42)</a> · <a href="/artikel/class-dan-object-pertama-python">Class &amp; Object (#41)</a> · <a href="/artikel/mengenal-oop-cara-berpikir-dengan-objek-python">Mengenal OOP (#40)</a>.</p>
</blockquote>
HTML;
    }
}
