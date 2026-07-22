<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article49Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $progCat = Category::where('slug', 'programming')->first();

        if (! $admin || ! $progCat) {
            throw new \RuntimeException('User atau kategori programming tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'capstone-sistem-perpustakaan-mini-oop-python';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        foreach ([
            'python' => 'python',
            'oop' => 'oop',
            'composition' => 'composition',
            'dataclass' => 'dataclass',
        ] as $tagSlug => $tagName) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagName]);
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $progCat->id,
                'title'           => 'Capstone: Sistem Perpustakaan Mini dengan OOP di Python',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => true,
                'seo_title'       => 'Capstone Perpustakaan Mini OOP Python — Seri 3',
                'seo_description' => 'Capstone Seri 3 OOP: perpustakaan mini dengan class, encapsulation, inheritance, polymorphism, ABC, composition, dataclass, dan CLI — berbahasa Indonesia.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['python', 'oop', 'composition', 'dataclass'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel ke-49 berhasil dipublish: '.$article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — capstone Seri 3</h2>
<p>Selamat! Kamu sudah menempuh sembilan artikel Seri 3 — dari <a href="/artikel/mengenal-oop-cara-berpikir-dengan-objek-python">Mengenal OOP (#40)</a> sampai <a href="/artikel/special-methods-dataclass-python">Special Methods &amp; Dataclass (#48)</a>. Artikel ini adalah <strong>capstone penutup</strong>: satu proyek kecil yang menggabungkan class, encapsulation, inheritance, polymorphism, ABC, composition, dan dataclass dalam domain yang familiar — <strong>sistem perpustakaan mini</strong>.</p>
<p>Bukan soal membuat aplikasi produksi. Tujuannya: membuktikan bahwa potongan konsep Seri 3 bisa saling mengunci dalam satu file yang bisa kamu jalankan.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> Sudah baca <a href="/artikel/special-methods-dataclass-python">Special Methods &amp; Dataclass (#48)</a>. Fondasi: <a href="/artikel/composition-vs-inheritance-python">Composition (#47)</a> · <a href="/artikel/abstraction-abc-python-oop">ABC (#46)</a> · <a href="/artikel/polymorphism-python-oop">Polymorphism (#45)</a> · <a href="/artikel/inheritance-pewarisan-class-python">Inheritance (#44)</a> · <a href="/artikel/encapsulation-property-python-oop">Encapsulation (#43)</a> · <a href="/artikel/attribute-method-constructor-init-python">Attribute (#42)</a> · <a href="/artikel/class-dan-object-pertama-python">Class &amp; Object (#41)</a> · <a href="/artikel/mengenal-oop-cara-berpikir-dengan-objek-python">Mengenal OOP (#40)</a>.</p>
</blockquote>

<h2>Spesifikasi fitur</h2>
<p>Perpustakaan mini kita mendukung lima operasi inti:</p>
<ul>
  <li><strong>Tambah</strong> — masukkan buku/ebook ke koleksi</li>
  <li><strong>Pinjam</strong> — kurangi stok jika tersedia</li>
  <li><strong>Kembalikan</strong> — kembalikan stok setelah dipinjam</li>
  <li><strong>Cari</strong> — temukan item berdasarkan judul (substring)</li>
  <li><strong>Laporan</strong> — cetak ringkasan koleksi + status stok</li>
</ul>
<p>Semua dijalankan lewat fungsi <code>demo()</code> yang mensimulasikan perintah CLI — tanpa menunggu keyboard, supaya skrip bisa diuji otomatis.</p>

<h2>Arsitektur folder mini</h2>
<p>Di proyek nyata kamu bisa memisah file. Untuk capstone ini kita tetap satu file supaya mudah disalin — tapi pikirkan batas tanggung jawab seperti folder berikut:</p>

<figure role="img" aria-label="Diagram arsitektur folder mini: models, services, main.py" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 260" style="display:block;max-width:720px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="oop49Arrow" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/></marker>
  </defs>
  <rect x="0" y="0" width="720" height="260" fill="#F5F5F0" rx="6"/>
  <rect x="40" y="40" width="180" height="80" rx="6" fill="#1a1a1a" stroke="#000" stroke-width="2.5"/>
  <text x="130" y="75" text-anchor="middle" fill="#fff" font-size="15" font-weight="700">models/</text>
  <text x="130" y="98" text-anchor="middle" fill="#E2E8F0" font-size="12">Buku · Ebook · ABC</text>
  <rect x="270" y="40" width="180" height="80" rx="6" fill="#1a1a1a" stroke="#000" stroke-width="2.5"/>
  <text x="360" y="75" text-anchor="middle" fill="#fff" font-size="15" font-weight="700">services/</text>
  <text x="360" y="98" text-anchor="middle" fill="#E2E8F0" font-size="12">Perpustakaan</text>
  <rect x="500" y="40" width="180" height="80" rx="6" fill="#2979FF" stroke="#000" stroke-width="2.5"/>
  <text x="590" y="75" text-anchor="middle" fill="#fff" font-size="15" font-weight="700">main.py</text>
  <text x="590" y="98" text-anchor="middle" fill="#E2E8F0" font-size="12">demo() / CLI</text>
  <line x1="220" y1="80" x2="270" y2="80" stroke="#2979FF" stroke-width="2.5" marker-end="url(#oop49Arrow)"/>
  <line x1="450" y1="80" x2="500" y2="80" stroke="#2979FF" stroke-width="2.5" marker-end="url(#oop49Arrow)"/>
  <text x="360" y="180" text-anchor="middle" fill="#2D3748" font-size="14">models -> services -> main (satu arah)</text>
  <text x="360" y="210" text-anchor="middle" fill="#2D3748" font-size="13">Capstone: ketiga lapisan digabung di perpustakaan_mini.py</text>
</svg>
<figcaption style="margin-top:.75rem;color:#2D3748;font-size:.95rem">Pola mental: data di <code>models/</code>, aturan bisnis di <code>services/</code>, pintu masuk di <code>main.py</code>.</figcaption>
</figure>

<h2>Model data — dataclass, inheritance, ABC</h2>
<p>Kita pakai <code>@dataclass</code> untuk <code>Buku</code> (lihat <a href="/artikel/special-methods-dataclass-python">#48</a>), <code>Ebook(Buku)</code> untuk inheritance (<a href="/artikel/inheritance-pewarisan-class-python">#44</a>), dan ABC <code>Pinjaman</code> dengan kontrak <code>pinjam</code>/<code>kembalikan</code> (<a href="/artikel/abstraction-abc-python-oop">#46</a>):</p>

<pre><code class="language-python">from abc import ABC, abstractmethod
from dataclasses import dataclass


class Pinjaman(ABC):
    @abstractmethod
    def pinjam(self) -> bool:
        ...

    @abstractmethod
    def kembalikan(self) -> bool:
        ...


@dataclass
class Buku(Pinjaman):
    judul: str
    penulis: str
    stok: int = 1

    def pinjam(self) -> bool:
        if self.stok &lt; 1:
            return False
        self.stok -= 1
        return True

    def kembalikan(self) -> bool:
        self.stok += 1
        return True

    def info(self) -> str:
        return f"Buku: {self.judul} · {self.penulis} (stok={self.stok})"


@dataclass
class Ebook(Buku):
    format_file: str = "PDF"

    def info(self) -> str:
        return f"Ebook: {self.judul} · {self.format_file} (stok={self.stok})"


b = Buku("ESP32 Praktis", "Budi", stok=2)
e = Ebook("Belajar Python", "Sari", stok=1, format_file="EPUB")
print(b.info())
print(e.info())
print("pinjam buku:", b.pinjam(), "stok=", b.stok)
</code></pre>

<p>Output:</p>
<pre><code>Buku: ESP32 Praktis · Budi (stok=2)
Ebook: Belajar Python · EPUB (stok=1)
pinjam buku: True stok= 1
</code></pre>
<p><code>Ebook</code> mewarisi <code>pinjam</code>/<code>kembalikan</code> dari <code>Buku</code>, lalu menimpa <code>info()</code> — inheritance + polymorphism dalam satu langkah.</p>
<p>Batas <a href="/artikel/encapsulation-property-python-oop">encapsulation (#43)</a> di sini sederhana: stok hanya berubah lewat method <code>pinjam</code>/<code>kembalikan</code>, bukan diubah bebas dari luar. (Naik ke <code>@property</code> boleh belakangan — lihat FAQ.)</p>
<p>Jembatan ke <a href="/artikel/special-methods-dataclass-python">Special Methods (#48)</a>: <code>@dataclass</code> sudah menyediakan <code>__init__</code>, <code>__repr__</code>, dan <code>__eq__</code>. Untuk teks domain kita tetap pakai method <code>info()</code>; di <strong>kode lengkap</strong> nanti kita tambahkan <code>__str__</code> yang memanggil <code>info()</code> supaya <code>print(buku)</code> ikut ramah manusia.</p>

<p>ABC menolak instance langsung — sama seperti di <a href="/artikel/abstraction-abc-python-oop">ABC (#46)</a>:</p>

<pre><code class="language-python">from abc import ABC, abstractmethod


class Pinjaman(ABC):
    @abstractmethod
    def pinjam(self) -> bool:
        ...

    @abstractmethod
    def kembalikan(self) -> bool:
        ...


try:
    Pinjaman()
except TypeError as err:
    print("ABC menolak:", type(err).__name__)
</code></pre>

<p>Output:</p>
<pre><code>ABC menolak: TypeError
</code></pre>

<h2>Composition — class Perpustakaan punya koleksi</h2>
<p>Sesuai <a href="/artikel/composition-vs-inheritance-python">Composition (#47)</a>: <code>Perpustakaan</code> <em>punya</em> daftar item, bukan “adalah” daftar:</p>

<pre><code class="language-python">from dataclasses import dataclass
from abc import ABC, abstractmethod


class Pinjaman(ABC):
    @abstractmethod
    def pinjam(self) -> bool: ...
    @abstractmethod
    def kembalikan(self) -> bool: ...


@dataclass
class Buku(Pinjaman):
    judul: str
    penulis: str
    stok: int = 1

    def pinjam(self) -> bool:
        if self.stok &lt; 1:
            return False
        self.stok -= 1
        return True

    def kembalikan(self) -> bool:
        self.stok += 1
        return True

    def info(self) -> str:
        return f"Buku: {self.judul} (stok={self.stok})"


class Perpustakaan:
    def __init__(self, nama: str):
        self.nama = nama
        self.koleksi = []  # composition: punya items

    def tambah(self, item: Buku) -> None:
        self.koleksi.append(item)

    def cari(self, kata: str):
        k = kata.lower()
        return [i for i in self.koleksi if k in i.judul.lower()]

    def laporan(self) -> str:
        baris = [f"=== {self.nama} ({len(self.koleksi)} item) ==="]
        for i in self.koleksi:
            baris.append(i.info())
        return "\n".join(baris)


p = Perpustakaan("Kota A")
p.tambah(Buku("ESP32 Praktis", "Budi", stok=2))
p.tambah(Buku("MQTT Dasar", "Ani", stok=1))
print(p.laporan())
print("cari 'mqtt':", [x.judul for x in p.cari("mqtt")])
</code></pre>

<p>Output:</p>
<pre><code>=== Kota A (2 item) ===
Buku: ESP32 Praktis (stok=2)
Buku: MQTT Dasar (stok=1)
cari 'mqtt': ['MQTT Dasar']
</code></pre>

<h2>Polymorphism — loop <code>info()</code> pada campuran Buku/Ebook</h2>
<p>Satu loop, dua tipe — pola dari <a href="/artikel/polymorphism-python-oop">Polymorphism (#45)</a>:</p>

<pre><code class="language-python">from abc import ABC, abstractmethod
from dataclasses import dataclass


class Pinjaman(ABC):
    @abstractmethod
    def pinjam(self) -> bool: ...
    @abstractmethod
    def kembalikan(self) -> bool: ...


@dataclass
class Buku(Pinjaman):
    judul: str
    penulis: str
    stok: int = 1

    def pinjam(self) -> bool:
        if self.stok &lt; 1:
            return False
        self.stok -= 1
        return True

    def kembalikan(self) -> bool:
        self.stok += 1
        return True

    def info(self) -> str:
        return f"Buku: {self.judul}"


@dataclass
class Ebook(Buku):
    format_file: str = "PDF"

    def info(self) -> str:
        return f"Ebook: {self.judul} [{self.format_file}]"


items = [
    Buku("ESP32 Praktis", "Budi"),
    Ebook("Belajar Python", "Sari", format_file="EPUB"),
    Buku("MQTT Dasar", "Ani"),
]
for item in items:
    print(item.info())  # method yang tepat dipanggil otomatis
</code></pre>

<p>Output:</p>
<pre><code>Buku: ESP32 Praktis
Ebook: Belajar Python [EPUB]
Buku: MQTT Dasar
</code></pre>

<p>Pinjam sampai stok habis — method mengembalikan <code>False</code>, bukan error:</p>

<pre><code class="language-python">from abc import ABC, abstractmethod
from dataclasses import dataclass


class Pinjaman(ABC):
    @abstractmethod
    def pinjam(self) -> bool: ...
    @abstractmethod
    def kembalikan(self) -> bool: ...


@dataclass
class Buku(Pinjaman):
    judul: str
    penulis: str
    stok: int = 1

    def pinjam(self) -> bool:
        if self.stok &lt; 1:
            return False
        self.stok -= 1
        return True

    def kembalikan(self) -> bool:
        self.stok += 1
        return True


b = Buku("MQTT Dasar", "Ani", stok=1)
print("1:", b.pinjam(), "stok=", b.stok)
print("2:", b.pinjam(), "stok=", b.stok)  # False — stok habis
print("kembali:", b.kembalikan(), "stok=", b.stok)
</code></pre>

<p>Output:</p>
<pre><code>1: True stok= 0
2: False stok= 0
kembali: True stok= 1
</code></pre>

<h2>CLI demo — menu lewat skrip, bukan interaktif</h2>
<p>Di CLI interaktif biasanya ada menu: <code>1) tambah 2) pinjam 3) kembalikan 4) cari 5) laporan</code>. Untuk artikel &amp; CI, kita <strong>tidak</strong> memakai <code>input()</code>. Fungsi <code>demo()</code> memanggil method yang sama secara berurutan — hasilnya deterministic dan bisa di-<code>py_compile</code>.</p>

<pre><code class="language-python">from abc import ABC, abstractmethod
from dataclasses import dataclass


class Pinjaman(ABC):
    @abstractmethod
    def pinjam(self) -> bool: ...
    @abstractmethod
    def kembalikan(self) -> bool: ...


@dataclass
class Buku(Pinjaman):
    judul: str
    penulis: str
    stok: int = 1

    def pinjam(self) -> bool:
        if self.stok &lt; 1:
            return False
        self.stok -= 1
        return True

    def kembalikan(self) -> bool:
        self.stok += 1
        return True

    def info(self) -> str:
        return f"{self.judul} (stok={self.stok})"


@dataclass
class Ebook(Buku):
    format_file: str = "PDF"

    def info(self) -> str:
        return f"{self.judul} [{self.format_file}] (stok={self.stok})"


class Perpustakaan:
    def __init__(self, nama):
        self.nama = nama
        self.koleksi = []

    def tambah(self, item):
        self.koleksi.append(item)

    def cari(self, kata):
        k = kata.lower()
        return [i for i in self.koleksi if k in i.judul.lower()]

    def laporan(self):
        return "\n".join(i.info() for i in self.koleksi)


def demo():
    p = Perpustakaan("Mini")
    p.tambah(Buku("ESP32 Praktis", "Budi", stok=2))
    p.tambah(Ebook("Belajar Python", "Sari", format_file="EPUB"))
    # perintah terscript — setara menu: pinjam, kembalikan, cari, laporan
    print("pinjam:", p.koleksi[0].pinjam())
    print("kembalikan:", p.koleksi[0].kembalikan())
    print("cari:", [x.judul for x in p.cari("python")])
    print(p.laporan())


demo()
</code></pre>

<p>Output:</p>
<pre><code>pinjam: True
kembalikan: True
cari: ['Belajar Python']
ESP32 Praktis (stok=2)
Belajar Python [EPUB] (stok=1)
</code></pre>
<p>Perhatikan: demo memanggil <code>p.koleksi[0].pinjam()</code> langsung — sengaja “bocor” ke item agar pola polymorphism terlihat. Latihan 1 meminta kamu naikkan ke <code>Perpustakaan.pinjam_judul(...)</code> supaya layanan yang mengorkestrasi.</p>
<p>Kalau nanti kamu ingin CLI interaktif di mesin sendiri, bungkus menu dengan loop + pembacaan keyboard — tapi jangan masukkan itu ke skrip yang harus jalan di CI/artikel.</p>

<h2>Pola Dasar — lima langkah capstone</h2>
<figure style="margin:1.5rem 0;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1.25rem;color:#1a1a1a" aria-label="Lima langkah membangun perpustakaan mini OOP">
<ol style="list-style:none;padding:0;margin:0;color:#1a1a1a">
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Tulis kontrak ABC dulu</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem"><code>Pinjaman</code> mendefinisikan <code>pinjam</code>/<code>kembalikan</code> sebelum detail stok.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Model data dengan dataclass</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem"><code>Buku</code> + <code>Ebook(Buku)</code> — field jelas, inheritance ringan.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#FF7A2F;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Composition di layanan</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem"><code>Perpustakaan</code> punya <code>self.koleksi</code> — tambah, cari, laporan.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#FF7A2F;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Polymorphism lewat <code>info()</code></strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Loop campuran Buku/Ebook tanpa <code>if isinstance</code> berlebihan.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#1a1a1a;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">5</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Demo terscript, deterministic</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Satu fungsi <code>demo()</code> memanggil alur pinjam/kembalikan/cari/laporan agar bisa diuji.</span>
    </div>
  </li>
</ol>
<figcaption style="margin-top:1rem;color:#2D3748;font-size:.95rem">Ringkas: ABC -> model -> composition -> polymorphism -> demo deterministic (plus <code>__str__</code> di kode lengkap).</figcaption>
</figure>

<h2>Kode lengkap — salin &amp; jalankan</h2>
<p>Simpan sebagai <code>perpustakaan_mini.py</code>, lalu <code>python perpustakaan_mini.py</code>:</p>

<pre><code class="language-python">from abc import ABC, abstractmethod
from dataclasses import dataclass


class Pinjaman(ABC):
    @abstractmethod
    def pinjam(self) -> bool: ...
    @abstractmethod
    def kembalikan(self) -> bool: ...


@dataclass
class Buku(Pinjaman):
    judul: str
    penulis: str
    stok: int = 1

    def pinjam(self) -> bool:
        if self.stok &lt; 1:
            return False
        self.stok -= 1
        return True

    def kembalikan(self) -> bool:
        self.stok += 1
        return True

    def info(self) -> str:
        return f"Buku: {self.judul} · {self.penulis} (stok={self.stok})"

    def __str__(self) -> str:
        return self.info()  # special method (#48): print(buku) pakai ini


@dataclass
class Ebook(Buku):
    format_file: str = "PDF"

    def info(self) -> str:
        return f"Ebook: {self.judul} · {self.format_file} (stok={self.stok})"


class Perpustakaan:
    def __init__(self, nama: str):
        self.nama = nama
        self.koleksi = []

    def tambah(self, item: Buku) -> None:
        self.koleksi.append(item)

    def cari(self, kata: str):
        k = kata.lower()
        return [i for i in self.koleksi if k in i.judul.lower()]

    def laporan(self) -> str:
        baris = [f"=== {self.nama} ({len(self.koleksi)} item) ==="]
        for i in self.koleksi:
            baris.append("  - " + str(i))  # __str__ -> info()
        return "\n".join(baris)


def demo():
    p = Perpustakaan("Kota A")
    p.tambah(Buku("ESP32 Praktis", "Budi", stok=2))
    p.tambah(Ebook("Belajar Python", "Sari", format_file="EPUB"))
    p.tambah(Buku("MQTT Dasar", "Ani", stok=1))
    print("pinjam ESP32:", p.koleksi[0].pinjam())
    print("pinjam lagi:", p.koleksi[0].pinjam())
    print("kembalikan:", p.koleksi[0].kembalikan())
    print("cari 'python':", [x.judul for x in p.cari("python")])
    print("print satu item:", p.koleksi[1])  # __str__ / polymorphism
    print(p.laporan())


if __name__ == "__main__":
    demo()
</code></pre>

<p>Output yang diharapkan (kurang lebih):</p>
<pre><code>pinjam ESP32: True
pinjam lagi: True
kembalikan: True
cari 'python': ['Belajar Python']
print satu item: Ebook: Belajar Python · EPUB (stok=1)
=== Kota A (3 item) ===
  - Buku: ESP32 Praktis · Budi (stok=1)
  - Ebook: Belajar Python · EPUB (stok=1)
  - Buku: MQTT Dasar · Ani (stok=1)
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
      <td><code>TypeError: Can't instantiate abstract class</code></td>
      <td>Subclass belum implementasi semua <code>@abstractmethod</code></td>
      <td>Lengkapkan <code>pinjam</code> dan <code>kembalikan</code> (lihat <a href="/artikel/abstraction-abc-python-oop">ABC #46</a>)</td>
    </tr>
    <tr>
      <td>Stok jadi negatif</td>
      <td><code>pinjam</code> tidak cek <code>stok &lt; 1</code></td>
      <td>Return <code>False</code> lebih awal; jangan kurangi stok kosong</td>
    </tr>
    <tr>
      <td><code>Perpustakaan</code> diwariskan dari <code>list</code></td>
      <td>Mencampur “adalah list” dengan “punya koleksi”</td>
      <td>Composition: <code>self.koleksi = []</code> (<a href="/artikel/composition-vs-inheritance-python">#47</a>)</td>
    </tr>
    <tr>
      <td>Demo macet menunggu keyboard</td>
      <td>Skrip menunggu ketikan pengguna</td>
      <td>Ganti dengan <code>demo()</code> terscript</td>
    </tr>
    <tr>
      <td><code>info()</code> selalu teks Buku untuk Ebook</td>
      <td>Lupa override di subclass</td>
      <td>Override <code>info()</code> di <code>Ebook</code> — polymorphism</td>
    </tr>
    <tr>
      <td>Field dataclass hilang di subclass</td>
      <td>Urutan field default di inheritance dataclass</td>
      <td>Field tanpa default dulu; field ber-default di subclass (Seri 3: mulai sederhana)</td>
    </tr>
  </tbody>
</table>

<h2>Latihan singkat</h2>
<ol>
  <li>Tambah method <code>Perpustakaan.pinjam_judul(judul)</code> yang mencari item lalu memanggil <code>pinjam()</code>.</li>
  <li>Tolak duplikat judul saat <code>tambah</code> (bandingkan case-insensitive).</li>
  <li>Buat subclass <code>Audiobook(Buku)</code> (pola di <a href="/artikel/inheritance-pewarisan-class-python">Inheritance (#44)</a>) dengan field <code>durasi_menit</code> dan override <code>info()</code>.</li>
</ol>

<h2>FAQ singkat</h2>
<p><strong>Kenapa satu file, bukan folder models/services?</strong><br>Supaya mudah disalin &amp; diuji. Pola folder tetap berlaku saat proyek membesar.</p>
<p><strong>Apa bedanya dengan encapsulation di <a href="/artikel/encapsulation-property-python-oop">#43</a>?</strong><br>Di sini stok diubah lewat method <code>pinjam</code>/<code>kembalikan</code>. Kamu bisa naikkan ke <code>@property</code> bila ingin validasi lebih ketat.</p>
<p><strong>Apakah ABC wajib untuk proyek kecil?</strong><br>Tidak wajib, tapi membantu menegaskan kontrak sebelum implementasi — terutama bila ada banyak tipe item.</p>
<p><strong>Bolehkah CLI interaktif di rumah?</strong><br>Boleh untuk latihan. Untuk artikel/CI, tetap pakai <code>demo()</code> terscript.</p>
<p><strong>Ke mana setelah Seri 3?</strong><br>Lanjut Tier 2: <a href="/artikel/design-pattern-factory-strategy-python">Factory &amp; Strategy (#50)</a> — merapikan hutan <code>if</code> di Capstone · <a href="/artikel/oop-micropython-esp32-class-sensor">OOP MicroPython / ESP32 (#51)</a> — jembatan ke Seri IoT. Ide berikutnya (belum live): class di Flask/FastAPI.</p>

<h2>Indeks Seri 3 lengkap</h2>
<ol>
  <li><a href="/artikel/mengenal-oop-cara-berpikir-dengan-objek-python">Mengenal OOP — Cara Berpikir dengan Objek (#40)</a></li>
  <li><a href="/artikel/class-dan-object-pertama-python">Class dan Object Pertama di Python (#41)</a></li>
  <li><a href="/artikel/attribute-method-constructor-init-python">Attribute, Method &amp; Constructor <code>__init__</code> (#42)</a></li>
  <li><a href="/artikel/encapsulation-property-python-oop">Encapsulation &amp; Property (#43)</a></li>
  <li><a href="/artikel/inheritance-pewarisan-class-python">Inheritance — Pewarisan Class (#44)</a></li>
  <li><a href="/artikel/polymorphism-python-oop">Polymorphism di Python OOP (#45)</a></li>
  <li><a href="/artikel/abstraction-abc-python-oop">Abstraction &amp; ABC (#46)</a></li>
  <li><a href="/artikel/composition-vs-inheritance-python">Composition vs Inheritance (#47)</a></li>
  <li><a href="/artikel/special-methods-dataclass-python">Special Methods &amp; Dataclass (#48)</a></li>
  <li><strong>Capstone: Sistem Perpustakaan Mini (#49 (ini))</strong> — artikel ini (capstone)</li>
</ol>

<h2>Kesimpulan — Seri 3 selesai</h2>
<p>Capstone ini merangkai seluruh Seri 3: berpikir objek (<a href="/artikel/mengenal-oop-cara-berpikir-dengan-objek-python">#40</a>), <a href="/artikel/class-dan-object-pertama-python">class &amp; object (#41)</a>, <a href="/artikel/attribute-method-constructor-init-python">attribute/<code>__init__</code> (#42)</a>, <a href="/artikel/encapsulation-property-python-oop">encapsulation (#43)</a>, <a href="/artikel/inheritance-pewarisan-class-python">inheritance (#44)</a>, <a href="/artikel/polymorphism-python-oop">polymorphism (#45)</a>, <a href="/artikel/abstraction-abc-python-oop">ABC (#46)</a>, <a href="/artikel/composition-vs-inheritance-python">composition (#47)</a>, <a href="/artikel/special-methods-dataclass-python">special methods/dataclass (#48)</a> — lalu satu CLI mini yang runnable.</p>
<p><strong>Seri 3 resmi selesai (10/10 artikel live).</strong> Lanjut Tier 2: <a href="/artikel/design-pattern-factory-strategy-python">Design Pattern ringan — Factory &amp; Strategy (#50)</a> (buat Buku/Ebook + ganti aturan denda tanpa hutan <code>if</code>), lalu jembatan IoT <a href="/artikel/oop-micropython-esp32-class-sensor">OOP di MicroPython / ESP32 (#51)</a>. Ide berikutnya (belum live): class di Flask/FastAPI. Kalau kamu dari jalur perangkat, pola “service punya sensor/aktuator” sudah muncul di <a href="/artikel/smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt">capstone smart greenhouse (#39)</a> — OOP membantu merapikan firmware yang membesar.</p>
<p>Artikel ini adalah <strong>#49 (ini)</strong> — penutup Seri 3 setelah <a href="/artikel/special-methods-dataclass-python">Special Methods (#48)</a>.</p>

<blockquote>
  <p><strong>Seri 3 progress:</strong> 10/10 artikel live. Kamu di langkah <strong>#49 (ini)</strong>. Prasyarat: <a href="/artikel/special-methods-dataclass-python">Special Methods (#48)</a> · <a href="/artikel/composition-vs-inheritance-python">Composition (#47)</a> · <a href="/artikel/abstraction-abc-python-oop">ABC (#46)</a> · <a href="/artikel/polymorphism-python-oop">Polymorphism (#45)</a> · <a href="/artikel/inheritance-pewarisan-class-python">Inheritance (#44)</a> · <a href="/artikel/encapsulation-property-python-oop">Encapsulation (#43)</a> · <a href="/artikel/attribute-method-constructor-init-python">Attribute (#42)</a> · <a href="/artikel/class-dan-object-pertama-python">Class &amp; Object (#41)</a> · <a href="/artikel/mengenal-oop-cara-berpikir-dengan-objek-python">Mengenal OOP (#40)</a>.</p>
</blockquote>
HTML;
    }
}
