<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article47Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $progCat = Category::where('slug', 'programming')->first();

        if (! $admin || ! $progCat) {
            throw new \RuntimeException('User atau kategori programming tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'composition-vs-inheritance-python';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        foreach ([
            'oop' => 'oop',
            'inheritance' => 'inheritance',
            'composition' => 'composition',
            'python' => 'python',
        ] as $tagSlug => $tagName) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagName]);
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $progCat->id,
                'title'           => 'Composition vs Inheritance: Kapan Pakai Apa di Python OOP',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'Composition vs Inheritance Python OOP — Has-a vs Is-a',
                'seo_description' => 'Pelajari composition vs inheritance di Python: Perpustakaan punya daftar Buku (has-a), anti-pattern warisi demi reuse, dan kapan inheritance tetap tepat — Seri 3 OOP berbahasa Indonesia.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['python', 'oop', 'inheritance', 'composition'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel ke-47 berhasil dipublish: '.$article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — “adalah” vs “punya”</h2>
<p>Di <a href="/artikel/inheritance-pewarisan-class-python">Inheritance (#44)</a> kita pakai <em>is-a</em>: ebook <em>adalah</em> buku. Di <a href="/artikel/abstraction-abc-python-oop">Abstraction &amp; ABC (#46)</a> kita tulis kontrak kemampuan. Hari ini pertanyaan berikutnya: kapan objek sebaiknya <em>punya</em> objek lain (<strong>composition</strong>, has-a), bukan mewarisi class lain?</p>
<p>Aturan praktis: warisi karena hubungan “adalah-sejenis” yang jujur. Kalau hanya butuh “menyimpan/mengelola” object lain — biasanya composition lebih bersih.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> Selesai <a href="/artikel/inheritance-pewarisan-class-python">Inheritance (#44)</a> dan nyaman dengan <a href="/artikel/abstraction-abc-python-oop">ABC (#46)</a>. Fondasi: <a href="/artikel/polymorphism-python-oop">Polymorphism (#45)</a> · <a href="/artikel/encapsulation-property-python-oop">Encapsulation (#43)</a> · <a href="/artikel/attribute-method-constructor-init-python">Attribute (#42)</a> · <a href="/artikel/class-dan-object-pertama-python">Class &amp; Object (#41)</a> · <a href="/artikel/mengenal-oop-cara-berpikir-dengan-objek-python">Mengenal OOP (#40)</a>.</p>
</blockquote>

<h2>Inheritance yang masih tepat — is-a</h2>
<p>Cuplikan ringkas dari jalur Seri 3: ebook memang sejenis buku digital.</p>

<pre><code class="language-python">class Buku:
    def __init__(self, judul, penulis):
        self.judul = judul
        self.penulis = penulis

    def info(self):
        return f"{self.judul} · {self.penulis}"


class Ebook(Buku):
    def __init__(self, judul, penulis, format_file="pdf"):
        super().__init__(judul, penulis)
        self.format_file = format_file

    def info(self):
        return f"{super().info()} · {self.format_file.upper()}"


e = Ebook("Belajar Python", "Sari", "epub")
print(e.info())
print("Ebook adalah Buku?", isinstance(e, Buku))
</code></pre>

<p>Output: <code>Belajar Python · Sari · EPUB</code> lalu <code>Ebook adalah Buku? True</code>. Inheritance di sini jujur — bukan tipuan demi hemat mengetik.</p>

<h2>Anti-pola — warisi hanya demi “punya daftar”</h2>
<p>Kesalahan umum: membuat perpustakaan mewarisi <code>Buku</code> supaya “langsung dapat” atribut judul. Padahal perpustakaan <em>bukan</em> sebuah buku.</p>

<pre><code class="language-python">class Buku:
    def __init__(self, judul):
        self.judul = judul


# SALAH — Perpustakaan "adalah" Buku? Tidak masuk akal
class PerpustakaanSalah(Buku):
    def __init__(self, nama, judul_buku_pertama):
        super().__init__(judul_buku_pertama)  # memaksa "judul" ke perpustakaan
        self.nama = nama


p = PerpustakaanSalah("Kota A", "ESP32 Praktis")
print("nama:", p.nama)
print("judul (milik 'Buku'?):", p.judul)
print("isinstance perpustakaan->Buku:", isinstance(p, Buku))  # True — menyesatkan
</code></pre>

<p>Output kurang lebih:</p>
<pre><code>nama: Kota A
judul (milik 'Buku'?): ESP32 Praktis
isinstance perpustakaan->Buku: True
</code></pre>
<p><code>isinstance</code> bilang True, tapi model domainnya bohong. Itu sinyal inheritance dipakai sebagai “kantong atribut”, bukan hubungan is-a.</p>

<h2>Composition — Perpustakaan <em>punya</em> koleksi Buku</h2>
<p>Composition: object menyimpan object lain sebagai bagian (biasanya di list/dict/atribut). Perpustakaan <em>punya</em> banyak buku — bukan <em>adalah</em> buku.</p>

<pre><code class="language-python">class Buku:
    def __init__(self, judul, penulis):
        self.judul = judul
        self.penulis = penulis

    def info(self):
        return f"{self.judul} · {self.penulis}"


class Perpustakaan:
    def __init__(self, nama):
        self.nama = nama
        self.koleksi = []  # punya daftar Buku

    def tambah(self, buku):
        self.koleksi.append(buku)

    def daftar(self):
        return [b.info() for b in self.koleksi]


lib = Perpustakaan("Kota A")
lib.tambah(Buku("ESP32 Praktis", "Budi"))
lib.tambah(Buku("Belajar Python", "Sari"))
print(lib.nama)
for baris in lib.daftar():
    print("-", baris)
print("isinstance lib->Buku:", isinstance(lib, Buku))
</code></pre>

<p>Output kurang lebih:</p>
<pre><code>Kota A
- ESP32 Praktis · Budi
- Belajar Python · Sari
isinstance lib->Buku: False
</code></pre>
<p>Sekarang modelnya jujur: perpustakaan mengelola koleksi; tiap buku tetap object sendiri. Composition juga sering berpasangan dengan <a href="/artikel/abstraction-abc-python-oop">ABC (#46)</a>: class pemilik koleksi <em>tidak perlu</em> mewarisi kontrak — ia cukup <em>menyimpan</em> object yang sudah memenuhi antarmuka (mis. item yang bisa <code>info()</code> atau <code>pinjam()</code>).</p>

<figure role="img" aria-label="Diagram composition: Perpustakaan punya daftar Buku, bukan mewarisi Buku" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 300" style="display:block;max-width:720px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="oop47Arrow" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/></marker>
  </defs>
  <rect x="0" y="0" width="720" height="300" fill="#F5F5F0" rx="6"/>
  <rect x="210" y="20" width="300" height="70" rx="6" fill="#1a1a1a" stroke="#000" stroke-width="2.5"/>
  <text x="360" y="50" text-anchor="middle" fill="#fff" font-size="16" font-weight="700">Perpustakaan</text>
  <text x="360" y="72" text-anchor="middle" fill="#CBD5E0" font-size="13">koleksi[]  (has-a)</text>
  <rect x="60" y="170" width="260" height="90" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="190" y="210" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Buku</text>
  <text x="190" y="235" text-anchor="middle" fill="#2D3748" font-size="12">ESP32 Praktis</text>
  <rect x="400" y="170" width="260" height="90" rx="6" fill="#FFF3E8" stroke="#000" stroke-width="2.5"/>
  <text x="530" y="210" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Buku</text>
  <text x="530" y="235" text-anchor="middle" fill="#2D3748" font-size="12">Belajar Python</text>
  <line x1="300" y1="90" x2="190" y2="170" stroke="#2979FF" stroke-width="2.5" marker-end="url(#oop47Arrow)"/>
  <line x1="420" y1="90" x2="530" y2="170" stroke="#2979FF" stroke-width="2.5" marker-end="url(#oop47Arrow)"/>
</svg>
<figcaption style="margin-top:.75rem;color:#2D3748;font-size:.95rem">Panah “punya”: Perpustakaan mengelola Buku — composition, bukan pewarisan.</figcaption>
</figure>

<h2>Refactor — dari SALAH ke BENAR</h2>
<p>Bandingkan sisi pemanggil. Composition membuat API perpustakaan berbicara tentang koleksi, bukan menyamar sebagai buku:</p>

<pre><code class="language-python">class Buku:
    def __init__(self, judul):
        self.judul = judul


class PerpustakaanSalah(Buku):
    def __init__(self, nama, judul):
        super().__init__(judul)
        self.nama = nama


class PerpustakaanBenar:
    def __init__(self, nama):
        self.nama = nama
        self.koleksi = []

    def tambah(self, buku):
        self.koleksi.append(buku)

    def jumlah(self):
        return len(self.koleksi)


salah = PerpustakaanSalah("Kota A", "ESP32 Praktis")
benar = PerpustakaanBenar("Kota A")
benar.tambah(Buku("ESP32 Praktis"))
benar.tambah(Buku("Cerita Sensor"))
print("SALAH judul di perpustakaan:", salah.judul)
print("BENAR jumlah buku:", benar.jumlah())
</code></pre>

<p>Output: <code>SALAH judul di perpustakaan: ESP32 Praktis</code> lalu <code>BENAR jumlah buku: 2</code>. Versi benar bisa tumbuh (cari, pinjam, statistik) tanpa memaksa perpustakaan “jadi” buku.</p>

<h2>Kapan inheritance masih pilihan pertama?</h2>
<ul>
  <li><strong>Is-a jujur</strong> — <code>Ebook(Buku)</code>, <code>Audiobook(Buku)</code></li>
  <li><strong>Perilaku bersama + override</strong> — method induk dipakai ulang dengan variasi (lihat <a href="/artikel/polymorphism-python-oop">Polymorphism (#45)</a>)</li>
  <li><strong>Kontrak formal</strong> — ABC di <a href="/artikel/abstraction-abc-python-oop">Abstraction (#46)</a> memang memakai pewarisan untuk menegakkan antarmuka</li>
</ul>
<p>Kalau alasanmu hanya “biar tidak copy-paste atribut” atau “biar dapat method list gratis” — berhenti sejenak. Composition (atau fungsi helper) sering lebih aman. Contoh klasik: mewarisi <code>list</code>:</p>

<pre><code class="language-python">class Buku:
    def __init__(self, judul):
        self.judul = judul


# SALAH — katalog "adalah" list? API jadi bocor ke method list mentah
class KatalogSalah(list):
    def judul_semua(self):
        return [b.judul for b in self]


# BENAR — katalog punya items
class KatalogBenar:
    def __init__(self):
        self.items = []

    def tambah(self, buku):
        self.items.append(buku)

    def judul_semua(self):
        return [b.judul for b in self.items]


salah = KatalogSalah([Buku("ESP32 Praktis"), Buku("Belajar Python")])
benar = KatalogBenar()
benar.tambah(Buku("ESP32 Praktis"))
benar.tambah(Buku("Belajar Python"))
print("SALAH isinstance list:", isinstance(salah, list))
print("BENAR isinstance list:", isinstance(benar, list))
print("BENAR judul:", benar.judul_semua())
</code></pre>

<p>Output kurang lebih:</p>
<pre><code>SALAH isinstance list: True
BENAR isinstance list: False
BENAR judul: ['ESP32 Praktis', 'Belajar Python']
</code></pre>
<p><code>KatalogSalah</code> “kebetulan” bisa <code>append</code>/<code>extend</code> seperti list — tapi tipe domainnya menyesatkan. Prefer bungkus list di dalam atribut.</p>
<p>Composition juga memudahkan menambah perilaku di pemilik koleksi — tanpa mengubah tipe <code>Buku</code>:</p>

<pre><code class="language-python">class Buku:
    def __init__(self, judul, tahun):
        self.judul = judul
        self.tahun = tahun


class Perpustakaan:
    def __init__(self, nama):
        self.nama = nama
        self.koleksi = []

    def tambah(self, buku):
        self.koleksi.append(buku)

    def cari(self, kata):
        kata = kata.lower()
        return [b for b in self.koleksi if kata in b.judul.lower()]


lib = Perpustakaan("Kota A")
lib.tambah(Buku("ESP32 Praktis", 2023))
lib.tambah(Buku("Belajar Python", 2024))
hasil = lib.cari("python")
print([b.judul for b in hasil])
</code></pre>

<p>Output: <code>['Belajar Python']</code>. Fitur pencarian hidup di perpustakaan — pemilik koleksi — bukan disisipkan lewat warisan aneh.</p>

<h2>Pola Dasar — memilih composition atau inheritance</h2>
<figure style="margin:1.5rem 0;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1.25rem;color:#1a1a1a" aria-label="Lima langkah memilih composition atau inheritance">
<ol style="list-style:none;padding:0;margin:0;color:#1a1a1a">
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Uji kalimat is-a</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">“X adalah Y?” Kalau terdengar aneh di dunia nyata, curigai inheritance.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Uji kalimat has-a</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">“X punya Y / mengelola banyak Y?” → composition (atribut/koleksi).</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#FF7A2F;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Jangan warisi demi reuse buta</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Butuh perilaku mirip ≠ wajib jadi subclass. Ekstrak fungsi/helper atau composition.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#FF7A2F;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Biarkan API pemilik koleksi</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem"><code>tambah</code>, <code>cari</code>, <code>jumlah</code> hidup di class yang <em>punya</em> daftar.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#1a1a1a;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">5</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Campur dengan sadar</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Inheritance untuk is-a/kontrak; composition untuk struktur “punya”. Keduanya sering dipakai bersama.</span>
    </div>
  </li>
</ol>
<figcaption style="margin-top:1rem;color:#2D3748;font-size:.95rem">Ringkas: is-a? → inheritance. has-a? → composition. reuse buta? → jangan warisi.</figcaption>
</figure>

<h2>Kode lengkap — salin &amp; jalankan</h2>
<p>Simpan sebagai <code>perpustakaan_komposisi.py</code>, lalu <code>python perpustakaan_komposisi.py</code>:</p>

<pre><code class="language-python">class Buku:
    def __init__(self, judul, penulis, tahun):
        self.judul = judul
        self.penulis = penulis
        self.tahun = tahun

    def info(self):
        return f"{self.judul} · {self.penulis} ({self.tahun})"


class Ebook(Buku):
    """Inheritance tetap dipakai di mana is-a jujur."""

    def __init__(self, judul, penulis, tahun, format_file="pdf"):
        super().__init__(judul, penulis, tahun)
        self.format_file = format_file

    def info(self):
        return f"{super().info()} · {self.format_file.upper()}"


class Perpustakaan:
    """Composition: punya koleksi item katalog."""

    def __init__(self, nama):
        self.nama = nama
        self.koleksi = []

    def tambah(self, item):
        self.koleksi.append(item)

    def daftar(self):
        return [item.info() for item in self.koleksi]

    def cari(self, kata):
        kata = kata.lower()
        return [item for item in self.koleksi if kata in item.info().lower()]


lib = Perpustakaan("Kota A")
lib.tambah(Buku("ESP32 Praktis", "Budi", 2023))
lib.tambah(Ebook("Belajar Python", "Sari", 2024, "epub"))
print("Perpustakaan:", lib.nama)
for baris in lib.daftar():
    print("-", baris)
print("cari python:", [x.judul for x in lib.cari("python")])
print("Ebook adalah Buku?", isinstance(lib.koleksi[1], Buku))
print("Perpustakaan adalah Buku?", isinstance(lib, Buku))
</code></pre>

<p>Output yang diharapkan (kurang lebih):</p>
<pre><code>Perpustakaan: Kota A
- ESP32 Praktis · Budi (2023)
- Belajar Python · Sari (2024) · EPUB
cari python: ['Belajar Python']
Ebook adalah Buku? True
Perpustakaan adalah Buku? False
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
      <td><code>isinstance(perpustakaan, Buku)</code> True padahal aneh</td>
      <td>Warisi class domain yang salah</td>
      <td>Ganti ke composition: simpan list Buku</td>
    </tr>
    <tr>
      <td>Class induk jadi “god class”</td>
      <td>Menjejalkan semua fitur lewat inheritance</td>
      <td>Pecah tanggung jawab; pemilik koleksi terpisah</td>
    </tr>
    <tr>
      <td>Takut inheritance sama sekali</td>
      <td>Mengira composition selalu lebih baik</td>
      <td>Pakai inheritance untuk is-a / kontrak <a href="/artikel/abstraction-abc-python-oop">ABC (#46)</a> yang jujur</td>
    </tr>
    <tr>
      <td>Copy-paste atribut antar class</td>
      <td>Menghindari inheritance tanpa alternatif</td>
      <td>Composition, helper, atau <a href="/artikel/special-methods-dataclass-python">dataclass (#48)</a></td>
    </tr>
    <tr>
      <td>Bingung dengan Encapsulation</td>
      <td>Menyamakan “sembunyikan detail” dengan “punya object”</td>
      <td><a href="/artikel/encapsulation-property-python-oop">Encapsulation (#43)</a> jaga akses; composition jaga struktur kepemilikan</td>
    </tr>
  </tbody>
</table>

<h2>Latihan singkat</h2>
<ol>
  <li>Tambah method <code>hapus(judul)</code> di <code>Perpustakaan</code> yang mengeluarkan buku dari <code>koleksi</code>.</li>
  <li>Ubah <code>KatalogSalah</code> menjadi composition murni (tanpa mewarisi <code>list</code>), lalu bandingkan <code>isinstance(..., list)</code>.</li>
  <li>Jelaskan dalam dua kalimat: kenapa <code>Ebook(Buku)</code> OK tapi <code>Perpustakaan(Buku)</code> tidak.</li>
</ol>

<h2>FAQ singkat</h2>
<p><strong>Apakah composition menggantikan inheritance?</strong><br>Tidak. Keduanya alat. Inheritance untuk is-a/kontrak; composition untuk has-a/struktur.</p>
<p><strong>Apa bedanya dengan <a href="/artikel/abstraction-abc-python-oop">ABC (#46)</a>?</strong><br>ABC memaksa daftar method. Composition menjawab “siapa punya object siapa” — sering dipakai bersama.</p>
<p><strong>Bolehkah class mewarisi <code>list</code> atau <code>dict</code>?</strong><br>Bisa secara teknis, sering menyulitkan. Prefer composition: <code>self.items = []</code> lalu bungkus method yang kamu butuhkan.</p>
<p><strong>Apakah “favor composition over inheritance” berarti inheritance jelek?</strong><br>Tidak. Itu peringatan agar tidak warisi demi reuse buta — selaras pesan di <a href="/artikel/inheritance-pewarisan-class-python">Inheritance (#44)</a>.</p>
<p><strong>Di mana atribut koleksi sebaiknya divalidasi?</strong><br>Di method pemilik (<code>tambah</code>) atau lewat property — lihat lagi <a href="/artikel/encapsulation-property-python-oop">Encapsulation (#43)</a>.</p>

<h2>Kesimpulan &amp; langkah berikutnya</h2>
<p>Tanya dulu: <em>adalah</em> atau <em>punya</em>? Is-a yang jujur → inheritance. Has-a / mengelola banyak object → composition. Hindari mewarisi hanya supaya atribut “ikut gratis”.</p>
<p>Artikel ini adalah <strong>#47 (ini)</strong> — langkah kedelapan Seri 3 setelah <a href="/artikel/abstraction-abc-python-oop">Abstraction &amp; ABC (#46)</a>.</p>
<p>Lanjut ke <a href="/artikel/special-methods-dataclass-python">Special Methods &amp; Dataclass (#48)</a>: <code>__str__</code>, <code>__repr__</code>, <code>__eq__</code>, dan <code>@dataclass</code> agar object lebih nyaman dibaca dan dibandingkan.</p>

<blockquote>
  <p><strong>Seri 3 progress:</strong> 10/10 artikel live. Kamu di langkah <strong>#47 (ini)</strong>. Prasyarat: <a href="/artikel/abstraction-abc-python-oop">Abstraction (#46)</a> · <a href="/artikel/inheritance-pewarisan-class-python">Inheritance (#44)</a> · <a href="/artikel/polymorphism-python-oop">Polymorphism (#45)</a> · <a href="/artikel/encapsulation-property-python-oop">Encapsulation (#43)</a> · <a href="/artikel/attribute-method-constructor-init-python">Attribute (#42)</a> · <a href="/artikel/class-dan-object-pertama-python">Class &amp; Object (#41)</a> · <a href="/artikel/mengenal-oop-cara-berpikir-dengan-objek-python">Mengenal OOP (#40)</a> · lanjut: <a href="/artikel/special-methods-dataclass-python">Special Methods (#48)</a>.</p>
</blockquote>
HTML;
    }
}
