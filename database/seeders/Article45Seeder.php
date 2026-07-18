<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article45Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $progCat = Category::where('slug', 'programming')->first();

        if (! $admin || ! $progCat) {
            throw new \RuntimeException('User atau kategori programming tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'polymorphism-python-oop';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        foreach ([
            'oop' => 'oop',
            'polymorphism' => 'polymorphism',
            'python' => 'python',
        ] as $tagSlug => $tagName) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagName]);
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $progCat->id,
                'title'           => 'Polymorphism: Satu Antarmuka, Banyak Bentuk di Python OOP',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'Polymorphism Python OOP — Satu Antarmuka Banyak Bentuk',
                'seo_description' => 'Pelajari polymorphism di Python: daftar campuran Buku/Ebook, panggil info() sama, duck typing, dan kapan isinstance boleh dipakai — Seri 3 OOP berbahasa Indonesia.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['python', 'oop', 'polymorphism'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel ke-45 berhasil dipublish: '.$article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — satu pemanggilan, banyak bentuk</h2>
<p>Di <a href="/artikel/inheritance-pewarisan-class-python">Inheritance &amp; <code>super()</code> (#44)</a> kamu sudah punya <code>Buku</code> dan <code>Ebook</code> dengan <code>info()</code> yang berbeda. Hari ini kita manfaatkan itu: satu loop memanggil <code>item.info()</code>, tiap object merespons sesuai bentuknya.</p>
<p><strong>Polymorphism</strong> (banyak bentuk) = object berbeda merespons pesan/method yang sama dengan cara masing-masing. Di Python, ini sering tampak sederhana: “kalau object punya <code>info()</code>, panggil saja.”</p>

<blockquote>
  <p><strong>Prasyarat:</strong> Selesai <a href="/artikel/inheritance-pewarisan-class-python">Inheritance (#44)</a> (nyaman dengan <code>Ebook(Buku)</code>, <code>super()</code>, override). Fondasi: <a href="/artikel/encapsulation-property-python-oop">Encapsulation (#43)</a> · <a href="/artikel/attribute-method-constructor-init-python">Attribute (#42)</a> · <a href="/artikel/class-dan-object-pertama-python">Class &amp; Object (#41)</a> · <a href="/artikel/mengenal-oop-cara-berpikir-dengan-objek-python">Mengenal OOP (#40)</a>.</p>
</blockquote>

<h2>Kenapa polymorphism terasa “ajaib”?</h2>
<p>Tanpa polymorphism, katalog campuran cepat jadi hutan <code>if</code>/<code>isinstance</code>. Dengan polymorphism:</p>
<ul>
  <li>Kode pemanggil tetap pendek: <code>for item in koleksi: print(item.info())</code></li>
  <li>Perilaku spesifik tinggal di class masing-masing (override)</li>
  <li>Menambah tipe baru (mis. <code>Audiobook</code>) sering tidak memaksa ubah loop</li>
</ul>
<p>Intinya: <em>satu antarmuka, banyak implementasi</em>.</p>

<h2>Siapkan Buku, Ebook, Audiobook</h2>
<p>Versi ringkas lanjutan <a href="/artikel/inheritance-pewarisan-class-python">Inheritance (#44)</a> — fokus ke <code>info()</code> yang berbeda per class:</p>

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
        return f"{super().info()} · {self.format_file.upper()} ({self.ukuran_mb} MB)"


class Audiobook(Buku):
    def __init__(self, judul, penulis, tahun, stok=1, durasi_menit=60):
        super().__init__(judul, penulis, tahun, stok)
        self.durasi_menit = durasi_menit

    def info(self):
        return f"{super().info()} · audio {self.durasi_menit} menit"


b = Buku("ESP32 Praktis", "Budi", 2023)
e = Ebook("Belajar Python", "Sari", 2024, format_file="pdf", ukuran_mb=4.5)
a = Audiobook("Cerita Sensor", "Ani", 2025, durasi_menit=90)
print(b.info())
print(e.info())
print(a.info())
</code></pre>

<h2>Satu loop untuk semua — inti polymorphism</h2>
<p>Masukkan object berbeda ke satu list, lalu panggil method yang sama:</p>

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
        return f"{super().info()} · {self.format_file.upper()} ({self.ukuran_mb} MB)"


class Audiobook(Buku):
    def __init__(self, judul, penulis, tahun, stok=1, durasi_menit=60):
        super().__init__(judul, penulis, tahun, stok)
        self.durasi_menit = durasi_menit

    def info(self):
        return f"{super().info()} · audio {self.durasi_menit} menit"


koleksi = [
    Buku("ESP32 Praktis", "Budi", 2023),
    Ebook("Belajar Python", "Sari", 2024, format_file="pdf", ukuran_mb=4.5),
    Audiobook("Cerita Sensor", "Ani", 2025, durasi_menit=90),
]

for item in koleksi:
    print(item.info())  # tiap class menjawab dengan caranya sendiri
</code></pre>

<p>Loop tidak perlu tahu “ini Ebook atau Audiobook”. Yang penting: tiap <code>item</code> paham cara menjawab <code>info()</code>. Python mencari method di <em>tipe object yang sebenarnya</em> — object Ebook menjalankan <code>Ebook.info</code>, bukan versi induk.</p>
<p>Output kurang lebih:</p>
<pre><code>ESP32 Praktis · Budi (2023) · stok 1
Belajar Python · Sari (2024) · stok 1 · PDF (4.5 MB)
Cerita Sensor · Ani (2025) · stok 1 · audio 90 menit
</code></pre>

<figure role="img" aria-label="Diagram polymorphism: satu pemanggilan info() dijawab berbeda oleh Buku, Ebook, dan Audiobook" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 300" style="display:block;max-width:720px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="oop45Arrow" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/></marker>
  </defs>
  <rect x="0" y="0" width="720" height="300" fill="#F5F5F0" rx="6"/>
  <rect x="200" y="20" width="320" height="70" rx="6" fill="#2979FF" stroke="#000" stroke-width="2.5"/>
  <text x="360" y="50" text-anchor="middle" fill="#fff" font-size="16" font-weight="700">for item in koleksi</text>
  <text x="360" y="72" text-anchor="middle" fill="#e3f2fd" font-size="13">item.info()</text>
  <rect x="40" y="160" width="200" height="100" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="140" y="200" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Buku</text>
  <text x="140" y="230" text-anchor="middle" fill="#2D3748" font-size="12">info dasar</text>
  <rect x="260" y="160" width="200" height="100" rx="6" fill="#FFF3E8" stroke="#000" stroke-width="2.5"/>
  <text x="360" y="200" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Ebook</text>
  <text x="360" y="230" text-anchor="middle" fill="#2D3748" font-size="12">+ format / MB</text>
  <rect x="480" y="160" width="200" height="100" rx="6" fill="#E8FFE8" stroke="#000" stroke-width="2.5"/>
  <text x="580" y="200" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Audiobook</text>
  <text x="580" y="230" text-anchor="middle" fill="#2D3748" font-size="12">+ durasi</text>
  <line x1="280" y1="90" x2="140" y2="160" stroke="#2979FF" stroke-width="2.5" marker-end="url(#oop45Arrow)"/>
  <line x1="360" y1="90" x2="360" y2="160" stroke="#2979FF" stroke-width="2.5" marker-end="url(#oop45Arrow)"/>
  <line x1="440" y1="90" x2="580" y2="160" stroke="#2979FF" stroke-width="2.5" marker-end="url(#oop45Arrow)"/>
</svg>
<figcaption style="margin-top:.75rem;color:#2D3748;font-size:.95rem">Satu pesan <code>info()</code>, tiga jawaban berbeda — itu polymorphism di kerja sehari-hari.</figcaption>
</figure>

<h2>Duck typing — “kalau bisa quack…”</h2>
<p>Python tidak selalu menuntut warisan formal. Object di luar pohon <code>Buku</code> pun bisa ikut loop jika punya method yang dibutuhkan:</p>

<pre><code class="language-python">class Buku:
    def __init__(self, judul):
        self.judul = judul

    def info(self):
        return f"Buku: {self.judul}"


class KatalogEntry:
    """Bukan subclass Buku — tapi punya info()."""
    def __init__(self, judul, sumber):
        self.judul = judul
        self.sumber = sumber

    def info(self):
        return f"Entri katalog: {self.judul} ({self.sumber})"


koleksi = [
    Buku("ESP32 Praktis"),
    KatalogEntry("Datasheet DHT22", "vendor"),
]

for item in koleksi:
    print(item.info())  # jalan — keduanya “bisa di-info()”
</code></pre>

<p>Ini sering disebut <em>duck typing</em>: yang diuji kemampuan (<code>info()</code>), bukan silsilah class. Inheritance tetap berguna untuk berbagi state/perilaku; duck typing berguna saat kontrak method-nya kecil dan jelas.</p>
<p>Risikonya: object yang “kelihatan cocok” tapi tanpa method yang diharapkan gagal saat runtime:</p>
<pre><code class="language-python">class Buku:
    def __init__(self, judul):
        self.judul = judul

    def info(self):
        return f"Buku: {self.judul}"


koleksi = [Buku("ESP32 Praktis"), {"judul": "bukan object"}]
# for item in koleksi:
#     print(item.info())  # AttributeError: 'dict' object has no attribute 'info'
print(koleksi[0].info())
</code></pre>

<blockquote>
  <p><strong>Ingat:</strong> duck typing fleksibel, tapi error muncul saat runtime jika method hilang (<code>AttributeError</code>). Untuk kontrak ketat antar banyak class, lihat <a href="/artikel/abstraction-abc-python-oop">Abstraction &amp; ABC (#46)</a>.</p>
</blockquote>

<h2>isinstance — kapan boleh, kapan berlebihan</h2>
<p><code>isinstance</code> berguna untuk <em>pengecualian</em>, bukan sebagai pengganti polymorphism. Bandingkan SALAH vs BENAR:</p>

<pre><code class="language-python">class Buku:
    def __init__(self, judul):
        self.judul = judul

    def info(self):
        return f"Buku: {self.judul}"


class Ebook(Buku):
    def __init__(self, judul, format_file="pdf"):
        super().__init__(judul)
        self.format_file = format_file

    def info(self):
        return f"Ebook: {self.judul} · {self.format_file}"

    def unduh(self):
        return f"unduh {self.judul}.{self.format_file}"


# SALAH — hutan isinstance menggantikan method bersama
# (urutan Ebook→Buku di sini sudah “aman”; yang salah adalah pola hutannya)
def cetak_salah(koleksi):
    for item in koleksi:
        if isinstance(item, Ebook):
            print(f"Ebook: {item.judul} · {item.format_file}")
        elif isinstance(item, Buku):
            print(f"Buku: {item.judul}")
        else:
            print("tipe tidak dikenal")


# BENAR — andalkan info(); isinstance hanya untuk aksi khusus
def cetak_benar(koleksi):
    for item in koleksi:
        print(item.info())
        if isinstance(item, Ebook):
            print("  →", item.unduh())  # hanya Ebook yang punya unduh()


koleksi = [Buku("ESP32 Praktis"), Ebook("Belajar Python", "pdf")]
cetak_benar(koleksi)
# cetak_salah(koleksi)  # anti-pola — jangan dijadikan kebiasaan
</code></pre>

<p>Pakai <code>isinstance</code> saat ada kemampuan yang <em>memang</em> hanya milik sebagian tipe (mis. <code>unduh()</code> di ebook). Jangan pakai untuk meniru seluruh <code>info()</code> yang seharusnya di-override.</p>
<p>Jebakan urutan: karena <code>isinstance(ebook, Buku)</code> juga True (lihat <a href="/artikel/inheritance-pewarisan-class-python">Inheritance (#44)</a>), rantai yang cek induk <em>dulu</em> menelan ebook:</p>
<pre><code class="language-python">class Buku:
    pass


class Ebook(Buku):
    pass


e = Ebook()
if isinstance(e, Buku):
    print("cabang Buku")      # kena — Ebook juga Buku
elif isinstance(e, Ebook):
    print("cabang Ebook")     # tidak pernah tercapai
</code></pre>
<p>Output: <code>cabang Buku</code>. Kalau terpaksa menulis rantai, <strong>cek tipe anak dulu</strong> — atau lebih baik, andalkan method bersama seperti di <code>cetak_benar</code>. Catatan: di contoh <code>cetak_salah</code> urutan Ebook→Buku sudah tepat; yang dilarang adalah mengganti <code>info()</code> dengan hutan <code>isinstance</code>.</p>

<h2>Pola Dasar — merancang polymorphism</h2>
<figure style="margin:1.5rem 0;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1.25rem;color:#1a1a1a" aria-label="Lima langkah merancang polymorphism di Python">
<ol style="list-style:none;padding:0;margin:0;color:#1a1a1a">
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Samakan nama method</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Mis. semua item katalog punya <code>info()</code> — antarmuka bersama.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Override di anak (bila warisan)</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Detail spesifik tinggal di class; pemanggil tidak perlu tahu tipenya.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#FF7A2F;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Loop lewat antarmuka</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem"><code>for item in koleksi: item.info()</code> — bukan rantai <code>isinstance</code>.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#FF7A2F;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">isinstance hanya pengecualian</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Untuk aksi yang benar-benar khas satu tipe (unduh, play, dll.).</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#1a1a1a;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">5</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Uji tipe baru</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Tambah class baru ke list — loop lama sebaiknya tetap jalan tanpa diubah.</span>
    </div>
  </li>
</ol>
<figcaption style="margin-top:1rem;color:#2D3748;font-size:.95rem">Ringkas: antarmuka sama → override → loop → isinstance jarang → uji tipe baru.</figcaption>
</figure>

<h2>Kode lengkap — salin &amp; jalankan</h2>
<p>Simpan sebagai <code>koleksi_polimorfik.py</code>, lalu <code>python koleksi_polimorfik.py</code>:</p>

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
        return f"{super().info()} · {self.format_file.upper()} ({self.ukuran_mb} MB)"

    def unduh(self):
        return f"unduh {self.judul}.{self.format_file}"


class Audiobook(Buku):
    def __init__(self, judul, penulis, tahun, stok=1, durasi_menit=60):
        super().__init__(judul, penulis, tahun, stok)
        self.durasi_menit = durasi_menit

    def info(self):
        return f"{super().info()} · audio {self.durasi_menit} menit"


class KatalogEntry:
    def __init__(self, judul, sumber):
        self.judul = judul
        self.sumber = sumber

    def info(self):
        return f"Entri katalog: {self.judul} ({self.sumber})"


koleksi = [
    Buku("ESP32 Praktis", "Budi", 2023, stok=2),
    Ebook("Belajar Python", "Sari", 2024, format_file="pdf", ukuran_mb=4.5),
    Audiobook("Cerita Sensor", "Ani", 2025, durasi_menit=90),
    KatalogEntry("Datasheet DHT22", "vendor"),
]

for item in koleksi:
    print(item.info())
    if isinstance(item, Ebook):
        print("  →", item.unduh())

print("isinstance ebook→Buku:", isinstance(koleksi[1], Buku))
print("type(katalog) is Buku:", type(koleksi[3]) is Buku)
</code></pre>

<p>Output yang diharapkan (kurang lebih):</p>
<pre><code>ESP32 Praktis · Budi (2023) · stok 2
Belajar Python · Sari (2024) · stok 1 · PDF (4.5 MB)
  → unduh Belajar Python.pdf
Cerita Sensor · Ani (2025) · stok 1 · audio 90 menit
Entri katalog: Datasheet DHT22 (vendor)
isinstance ebook→Buku: True
type(katalog) is Buku: False
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
      <td>Loop penuh <code>isinstance</code> panjang</td>
      <td>Menyalin perilaku ke pemanggil, bukan ke class</td>
      <td>Pindahkan ke method bersama (<code>info()</code>) / override</td>
    </tr>
    <tr>
      <td><code>AttributeError: ... has no attribute 'info'</code></td>
      <td>Object di list tidak punya method yang diharapkan</td>
      <td>Samakan antarmuka, atau jangan masukkan tipe itu ke koleksi</td>
    </tr>
    <tr>
      <td>Tipe baru memaksa ubah semua <code>if</code></td>
      <td>Polymorphism diganti cabang tipe</td>
      <td>Tambah class + method; biarkan loop lama</td>
    </tr>
    <tr>
      <td>Bingung duck typing vs inheritance</td>
      <td>Mengira harus selalu <code>Anak(Induk)</code></td>
      <td>Warisi bila berbagi state/perilaku; duck typing OK untuk kontrak kecil</td>
    </tr>
    <tr>
      <td><code>isinstance</code> “salah cabang”</td>
      <td>Cek anak setelah induk dengan urutan terbalik</td>
      <td>Cek tipe lebih spesifik dulu, atau hindari hutan <code>isinstance</code></td>
    </tr>
  </tbody>
</table>

<h2>Latihan singkat</h2>
<ol>
  <li>Tambah <code>class Majalah(Buku)</code> dengan <code>edisi</code>, override <code>info()</code>, masukkan ke <code>koleksi</code> — pastikan loop lama tidak diubah.</li>
  <li>Sengaja masukkan object tanpa <code>info()</code> ke list — amati <code>AttributeError</code>, lalu perbaiki.</li>
  <li>Refactor satu fungsi yang penuh <code>isinstance</code> menjadi pemanggilan method bersama.</li>
</ol>

<h2>FAQ singkat</h2>
<p><strong>Apakah polymorphism wajib pakai inheritance?</strong><br>Tidak. Inheritance memudahkan berbagi kode; duck typing cukup jika method-nya sama. Keduanya sah di Python.</p>
<p><strong>Kapan <code>isinstance</code> masih boleh?</strong><br>Saat ada aksi yang memang khas satu tipe dan belum layak digabung ke antarmuka bersama.</p>
<p><strong>Apakah <code>type(x) is Buku</code> sama dengan <code>isinstance</code>?</strong><br>Tidak. <code>type(x) is Buku</code> menolak anak class. Untuk “adalah Buku atau turunannya”, pakai <code>isinstance</code> — detailnya sudah muncul di <a href="/artikel/inheritance-pewarisan-class-python">Inheritance (#44)</a>.</p>
<p><strong>Kenapa <code>isinstance(ebook, Buku)</code> True?</strong><br>Karena pewarisan: ebook <em>adalah</em> Buku. Itu kekuatan polymorphism — dan sekaligus alasan hutan <code>isinstance</code> mudah salah urutan.</p>
<p><strong>Bagaimana memaksa semua class punya method tertentu?</strong><br>Itu ranah <a href="/artikel/abstraction-abc-python-oop">Abstraction &amp; ABC (#46)</a>.</p>

<h2>Kesimpulan &amp; langkah berikutnya</h2>
<p>Polymorphism membuat pemanggil tetap sederhana: panggil antarmuka bersama, biarkan tiap class menjawab dengan caranya. Hindari hutan <code>isinstance</code>; cadangkan untuk pengecualian yang benar-benar khas.</p>
<p>Artikel ini adalah <strong>#45 (ini)</strong> — langkah keenam Seri 3 setelah <a href="/artikel/inheritance-pewarisan-class-python">Inheritance (#44)</a>.</p>
<p>Lanjut ke <a href="/artikel/abstraction-abc-python-oop">Abstraction &amp; ABC (#46)</a>: kontrak class abstract supaya subclass wajib mengimplementasikan method tertentu.</p>

<blockquote>
  <p><strong>Seri 3 progress:</strong> 8/10 artikel live. Kamu di langkah <strong>#45 (ini)</strong>. Prasyarat: <a href="/artikel/inheritance-pewarisan-class-python">Inheritance (#44)</a> · <a href="/artikel/encapsulation-property-python-oop">Encapsulation (#43)</a> · <a href="/artikel/attribute-method-constructor-init-python">Attribute (#42)</a> · <a href="/artikel/class-dan-object-pertama-python">Class &amp; Object (#41)</a> · <a href="/artikel/mengenal-oop-cara-berpikir-dengan-objek-python">Mengenal OOP (#40)</a> · lanjut: <a href="/artikel/abstraction-abc-python-oop">Abstraction (#46)</a>.</p>
</blockquote>
HTML;
    }
}
