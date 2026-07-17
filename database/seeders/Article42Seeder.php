<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article42Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $progCat = Category::where('slug', 'programming')->first();

        if (! $admin || ! $progCat) {
            throw new \RuntimeException('User atau kategori programming tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'attribute-method-constructor-init-python';

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
                'title'           => 'Attribute, Method, dan Constructor __init__ di Python',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'Attribute, Method & __init__ Python — Tutorial OOP Pemula',
                'seo_description' => 'Pahami self, attribute, method yang mengubah state, dan constructor __init__ — lanjutan Seri 3 OOP berbahasa Indonesia.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['python', 'oop', 'oop-class'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel ke-42 berhasil dipublish: '.$article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — self, state, dan perilaku</h2>
<p>Di <a href="/artikel/class-dan-object-pertama-python">Class dan Object Pertama (#41)</a> kamu sudah menulis <code>class Buku</code>, membuat instance, dan melihat identitas object. Hari ini kita bedah tiga hal yang membuat class “hidup”:</p>
<ul>
  <li><strong>Attribute</strong> — data yang melekat pada object (state)</li>
  <li><strong>Method</strong> — fungsi di dalam class yang bekerja lewat <code>self</code></li>
  <li><strong>Constructor <code>__init__</code></strong> — tempat menyiapkan state saat object lahir</li>
</ul>

<blockquote>
  <p><strong>Prasyarat:</strong> Selesai <a href="/artikel/class-dan-object-pertama-python">#41</a> (atau setidaknya bisa membuat <code>class</code> + instance). Mental model OOP: <a href="/artikel/mengenal-oop-cara-berpikir-dengan-objek-python">Mengenal OOP (#40)</a>.</p>
</blockquote>

<h2>Attribute = state milik object</h2>
<p>Attribute adalah nama yang kamu simpan di <code>self</code>. Tiap instance punya salinan nilainya sendiri:</p>

<pre><code class="language-python">class Buku:
    def __init__(self, judul, penulis, tahun, stok=1):
        self.judul = judul      # attribute
        self.penulis = penulis  # attribute
        self.tahun = tahun      # attribute
        self.stok = stok        # state awal — berapa salinan tersedia

buku_a = Buku("Belajar Python", "Sari", 2024, stok=2)
buku_b = Buku("ESP32 Praktis", "Budi", 2023)

print(buku_a.judul)   # Belajar Python
print(buku_a.stok)    # 2
buku_a.tahun = 2025   # ubah state object ini saja
print(buku_b.tahun)   # 2023 — tidak ikut berubah
</code></pre>

<p>Convention: nama attribute pakai <code>snake_case</code>. Jangan takut menambah attribute di <code>__init__</code> kalau memang bagian dari “siapa object ini”.</p>

<h2>__init__ = constructor: menyiapkan object</h2>
<p><code>__init__</code> dipanggil otomatis saat kamu menulis <code>Buku(...)</code>. Parameter pertama wajib <code>self</code> — itu object yang sedang dibuat. Argumen setelahnya kamu yang tentukan:</p>

<pre><code class="language-python">class Buku:
    def __init__(self, judul, penulis, tahun, stok=1):
        if tahun &lt; 1900:
            raise ValueError("tahun tidak masuk akal")
        self.judul = judul
        self.penulis = penulis
        self.tahun = tahun
        self.stok = stok


buku = Buku("Belajar Python", "Sari", 2024)
print(buku.judul, buku.stok)
# Buku("Kuno", "Anonim", 1800)  # ValueError: tahun tidak masuk akal
</code></pre>

<p>Catatan praktis:</p>
<ul>
  <li><code>__init__</code> biasanya <strong>tidak</strong> <code>return</code> nilai (selain <code>None</code> implisit)</li>
  <li>Validasi ringan di sini boleh — detail “penyembunyian” lebih dalam di <a href="/artikel/encapsulation-property-python-oop">Encapsulation (#43)</a></li>
  <li>Default argument (<code>stok=1</code>) membuat pemanggilan lebih nyaman</li>
</ul>

<h2>self — kenapa selalu ada?</h2>
<p>Di <a href="/artikel/class-dan-object-pertama-python">#41</a> kamu sudah melihat method <code>info()</code>. Saat menulis <code>buku_a.info()</code>, Python menerjemahkannya kira-kira menjadi <code>Buku.info(buku_a)</code>. Parameter <code>self</code> menerima object pemanggil. Tanpa <code>self</code>, method tidak tahu <em>attribute siapa</em> yang dibaca.</p>

<figure role="img" aria-label="Diagram: pemanggilan buku_a.info() mengirim buku_a sebagai self ke method info" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 300" style="display:block;max-width:720px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="oop42Arrow" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/></marker>
  </defs>
  <rect x="0" y="0" width="720" height="300" fill="#F5F5F0" rx="6"/>
  <rect x="40" y="90" width="220" height="120" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="150" y="130" text-anchor="middle" fill="#1a1a1a" font-size="16" font-weight="700">buku_a</text>
  <text x="150" y="158" text-anchor="middle" fill="#2D3748" font-size="13">judul · penulis · stok</text>
  <text x="150" y="184" text-anchor="middle" fill="#2D3748" font-size="12">instance di memori</text>
  <rect x="400" y="70" width="280" height="160" rx="6" fill="#2979FF" stroke="#000" stroke-width="2.5"/>
  <text x="540" y="110" text-anchor="middle" fill="#fff" font-size="16" font-weight="700">def info(self):</text>
  <text x="540" y="140" text-anchor="middle" fill="#e3f2fd" font-size="13">self = buku_a</text>
  <text x="540" y="168" text-anchor="middle" fill="#cfe4ff" font-size="12">baca self.judul, …</text>
  <text x="540" y="196" text-anchor="middle" fill="#cfe4ff" font-size="12">return string</text>
  <line x1="260" y1="150" x2="400" y2="150" stroke="#2979FF" stroke-width="2.5" marker-end="url(#oop42Arrow)"/>
  <text x="330" y="135" text-anchor="middle" fill="#2D3748" font-size="12" font-weight="600">buku_a.info()</text>
</svg>
<figcaption style="margin-top:.75rem;color:#2D3748;font-size:.95rem">Method selalu “tahu” object-nya lewat self.</figcaption>
</figure>

<h2>Method baca vs method ubah state</h2>
<p>Method bisa hanya <em>membaca</em> attribute, atau <em>mengubah</em> state object:</p>

<pre><code class="language-python">class Buku:
    def __init__(self, judul, penulis, tahun, stok=1):
        self.judul = judul
        self.penulis = penulis
        self.tahun = tahun
        self.stok = stok

    def info(self):
        """Method baca — tidak mengubah state."""
        return f"{self.judul} oleh {self.penulis} ({self.tahun}) · stok {self.stok}"

    def pinjam(self):
        """Method ubah state — stok berkurang."""
        if self.stok &lt;= 0:
            raise ValueError("stok habis")
        self.stok -= 1
        return self.stok

    def kembalikan(self):
        self.stok += 1
        return self.stok


buku = Buku("Belajar Python", "Sari", 2024, stok=2)
print(buku.info())
print("sisa:", buku.pinjam())   # 1
print("sisa:", buku.pinjam())   # 0
# buku.pinjam()  # ValueError: stok habis
print("sisa:", buku.kembalikan())  # 1
</code></pre>

<p>Pola ini membuat aturan bisnis (stok tidak boleh negatif) tinggal di satu tempat — dekat datanya — bukan tersebar di banyak fungsi lepas.</p>

<blockquote>
  <p><strong>Jebakan klasik:</strong> menulis <code>riwayat = []</code> di tubuh class (bukan di <code>__init__</code>) membuat <em>semua</em> instance berbagi list yang sama. Selalu inisialisasi data per-object lewat <code>self.riwayat = []</code> di dalam <code>__init__</code>.</p>
</blockquote>

<h2>Parameter method: self + argumen tambahan</h2>
<p>Method boleh menerima argumen selain <code>self</code>. Contoh class lengkap dengan method <code>pinjam_untuk</code>:</p>

<pre><code class="language-python">class Buku:
    def __init__(self, judul, penulis, tahun, stok=1):
        self.judul = judul
        self.penulis = penulis
        self.tahun = tahun
        self.stok = stok

    def pinjam_untuk(self, nama_anggota):
        if self.stok &lt;= 0:
            raise ValueError("stok habis")
        self.stok -= 1
        return f"{nama_anggota} meminjam {self.judul}"


buku = Buku("Belajar Python", "Sari", 2024, stok=2)
print(buku.pinjam_untuk("Rina"))
# Rina meminjam Belajar Python
print("sisa stok:", buku.stok)  # 1
</code></pre>

<p>Yang sering keliru: menulis <code>buku.pinjam_untuk("Rina")</code> tapi di definisi <strong>lupa <code>self</code></strong> — Python mengira <code>"Rina"</code> adalah <code>self</code>, lalu muncul TypeError argumen tertukar/kurang.</p>

<h2>Pola Dasar — merancang attribute &amp; method</h2>
<figure style="margin:1.5rem 0;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1.25rem;color:#1a1a1a" aria-label="Lima langkah merancang attribute dan method">
<ol style="list-style:none;padding:0;margin:0;color:#1a1a1a">
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Tulis data wajib di __init__</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Judul, penulis, tahun, stok — semua lewat <code>self.nama = ...</code></span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Pisahkan baca vs ubah</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem"><code>info()</code> hanya baca; <code>pinjam()</code> mengubah stok.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#FF7A2F;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Selalu tulis self di method</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem"><code>def nama(self, ...):</code> — jangan dihilangkan.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#FF7A2F;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Validasi di batas yang jelas</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Tolak stok negatif / tahun aneh sedini mungkin.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#1a1a1a;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">5</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Uji lewat beberapa instance</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Pastikan ubah <code>buku_a</code> tidak merusak <code>buku_b</code>.</span>
    </div>
  </li>
</ol>
<figcaption style="margin-top:1rem;color:#2D3748;font-size:.95rem">Ringkas: init → attribute → method baca/ubah → uji multi-instance.</figcaption>
</figure>

<h2>Kode lengkap — salin &amp; jalankan</h2>
<p>Simpan sebagai <code>perpustakaan_mini.py</code>, lalu <code>python perpustakaan_mini.py</code>:</p>

<pre><code class="language-python">class Buku:
    def __init__(self, judul, penulis, tahun, stok=1):
        self.judul = judul
        self.penulis = penulis
        self.tahun = tahun
        self.stok = stok

    def info(self):
        return f"{self.judul} · stok {self.stok}"

    def pinjam(self):
        if self.stok &lt;= 0:
            raise ValueError("stok habis")
        self.stok -= 1
        return self.stok

    def kembalikan(self):
        self.stok += 1
        return self.stok


a = Buku("Belajar Python", "Sari", 2024, stok=2)
b = Buku("ESP32 Praktis", "Budi", 2023)

print(a.info())
print("pinjam a:", a.pinjam())
print("a stok:", a.stok, "| b stok:", b.stok)
print(a.kembalikan())
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
      <td><code>TypeError: Buku.__init__() missing ... required positional argument</code></td>
      <td>Memanggil <code>Buku()</code> tanpa argumen wajib</td>
      <td>Isi parameter: <code>Buku("Judul", "Penulis", 2024)</code></td>
    </tr>
    <tr>
      <td><code>TypeError: ... missing 1 required positional argument: 'self'</code></td>
      <td>Memanggil lewat class tanpa instance, atau lupa <code>self</code> di definisi</td>
      <td>Pakai <code>obj.method()</code>; pastikan <code>def method(self):</code></td>
    </tr>
    <tr>
      <td><code>TypeError: ... takes 1 positional argument but 2 were given</code></td>
      <td>Lupa menulis <code>self</code> di <code>def</code>, lalu memanggil dengan argumen</td>
      <td>Tambah <code>self</code> sebagai parameter pertama</td>
    </tr>
    <tr>
      <td><code>AttributeError: ... has no attribute 'stok'</code></td>
      <td>Attribute belum di-set di <code>__init__</code>, atau typo</td>
      <td>Cek <code>self.stok = stok</code></td>
    </tr>
    <tr>
      <td>Semua instance “ikut berubah”</td>
      <td>List/dict diletakkan di tubuh class (attribute class bersama), bukan di <code>__init__</code></td>
      <td>Pakai <code>self.riwayat = []</code> di dalam <code>__init__</code>, jangan <code>riwayat = []</code> di level class</td>
    </tr>
  </tbody>
</table>

<h2>Latihan singkat</h2>
<ol>
  <li>Tambah attribute <code>kategori</code> (string) ke <code>Buku</code>. Cetak lewat <code>info()</code>.</li>
  <li>Buat method <code>apakah_tersedia(self)</code> yang mengembalikan <code>True</code> jika <code>stok &gt; 0</code>.</li>
  <li>Buat dua instance; pinjam salah satu berkali-kali sampai <code>ValueError</code>. Pastikan instance lain tidak terdampak.</li>
</ol>

<h2>FAQ singkat</h2>
<p><strong>Apakah __init__ wajib?</strong><br>Secara sintaks tidak. Secara praktik hampir selalu ya — tanpa itu attribute harus di-set manual setelah object dibuat.</p>
<p><strong>Bolehkah method tanpa mengubah state?</strong><br>Boleh dan dianjurkan. Method baca (seperti <code>info</code>) membuat kode lebih jelas.</p>
<p><strong>Kapan pakai attribute class (bukan self)?</strong><br>Untuk konstanta bersama yang jarang berubah. Untuk state per object, selalu <code>self</code>. Detail penyembunyian attribute: lihat <a href="/artikel/encapsulation-property-python-oop">Encapsulation (#43)</a>.</p>

<h2>Kesimpulan &amp; langkah berikutnya</h2>
<p><code>__init__</code> menyiapkan state; attribute menyimpan data; method membaca atau mengubah state lewat <code>self</code>. Kalau tiga ini sudah nyaman, class Python-mu mulai terasa seperti “benda” yang punya tanggung jawab — bukan sekadar kumpulan dict.</p>
<p>Artikel ini adalah <strong>#42 (ini)</strong> — langkah ketiga Seri 3 setelah <a href="/artikel/class-dan-object-pertama-python">Class &amp; Object (#41)</a>.</p>
<p>Lanjut ke <a href="/artikel/encapsulation-property-python-oop">Encapsulation &amp; <code>@property</code> (#43)</a>: kita rapikan akses data dengan konvensi <code>_</code> / <code>__</code> dan property/setter agar stok &amp; tahun tidak diubah sembarangan dari luar.</p>

<blockquote>
  <p><strong>Seri 3 progress:</strong> 3/10 artikel (setelah <strong>#42 (ini)</strong> live). Prasyarat: <a href="/artikel/class-dan-object-pertama-python">Class &amp; Object (#41)</a> · fondasi: <a href="/artikel/mengenal-oop-cara-berpikir-dengan-objek-python">Mengenal OOP (#40)</a>.</p>
</blockquote>
HTML;
    }
}
