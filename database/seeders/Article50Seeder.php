<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article50Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $progCat = Category::where('slug', 'programming')->first();

        if (! $admin || ! $progCat) {
            throw new \RuntimeException('User atau kategori programming tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'design-pattern-factory-strategy-python';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        foreach ([
            'oop' => 'oop',
            'python' => 'python',
            'polymorphism' => 'polymorphism',
            'composition' => 'composition',
            'design-pattern' => 'design-pattern',
        ] as $tagSlug => $tagName) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagName]);
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $progCat->id,
                'title'           => 'Design Pattern Ringan: Factory & Strategy di Python OOP',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'Factory & Strategy Pattern Python OOP — Design Pattern Ringan',
                'seo_description' => 'Pelajari Factory dan Strategy pattern di Python: buat Buku/Ebook tanpa hutan if, ganti aturan denda di runtime — Tier 2 setelah Capstone Seri 3.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['python', 'oop', 'polymorphism', 'composition', 'design-pattern'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel ke-50 berhasil dipublish: '.$article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — pola yang merapikan Capstone</h2>
<p>Di <a href="/artikel/capstone-sistem-perpustakaan-mini-oop-python">Capstone Perpustakaan Mini (#49)</a> kamu sudah punya class, inheritance, polymorphism, ABC, composition, dan special methods dalam satu sistem. Tier 2 mulai di sini: bukan konsep OOP baru, melainkan <strong>design pattern ringan</strong> — resep nama yang sering dipakai agar kode tidak mengeras di hutan <code>if</code>.</p>
<p>Model di artikel ini sengaja lebih ramping daripada Capstone (fokus <code>label()</code>, tanpa alur stok/pinjam) supaya Factory &amp; Strategy tidak tenggelam di detail CLI.</p>
<p>Dua pola yang paling berguna untuk domain perpustakaan kita:</p>
<ul>
  <li><strong>Factory</strong> — satu pintu membuat object (<code>Buku</code> / <code>Ebook</code>) tanpa pemanggil harus tahu class mana.</li>
  <li><strong>Strategy</strong> — aturan yang bisa diganti (misalnya perhitungan denda) tanpa menulis ulang layanan yang memanggilnya.</li>
</ul>
<p>Keduanya berdiri di atas <a href="/artikel/polymorphism-python-oop">Polymorphism (#45)</a>, kontrak <a href="/artikel/abstraction-abc-python-oop">ABC (#46)</a>, dan “siapa punya siapa” dari <a href="/artikel/composition-vs-inheritance-python">Composition (#47)</a>. Jangan over-engineer: dua class + satu factory sudah cukup untuk belajar.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> Selesai <a href="/artikel/capstone-sistem-perpustakaan-mini-oop-python">Capstone (#49)</a>. Nyaman dengan <a href="/artikel/polymorphism-python-oop">Polymorphism (#45)</a> · <a href="/artikel/abstraction-abc-python-oop">ABC (#46)</a> · <a href="/artikel/composition-vs-inheritance-python">Composition (#47)</a>. Fondasi: <a href="/artikel/encapsulation-property-python-oop">Encapsulation (#43)</a> · <a href="/artikel/inheritance-pewarisan-class-python">Inheritance (#44)</a> · <a href="/artikel/special-methods-dataclass-python">Special Methods (#48)</a> · <a href="/artikel/mengenal-oop-cara-berpikir-dengan-objek-python">Mengenal OOP (#40)</a>.</p>
</blockquote>

<h2>Masalah: hutan <code>if</code> di mana-mana</h2>
<p>Tanpa pola, pembuatan item dan aturan denda sering tersebar:</p>

<pre><code class="language-python">class Buku:
    def __init__(self, judul, penulis):
        self.judul = judul
        self.penulis = penulis


class Ebook(Buku):
    def __init__(self, judul, penulis, format_file="pdf"):
        super().__init__(judul, penulis)
        self.format_file = format_file


def buat_item_lama(jenis, judul, penulis, format_file=None):
    if jenis == "buku":
        return Buku(judul, penulis)
    elif jenis == "ebook":
        return Ebook(judul, penulis, format_file or "pdf")
    else:
        raise ValueError(f"jenis tidak dikenal: {jenis}")


def hitung_denda_lama(hari_terlambat, mode="flat"):
    if mode == "flat":
        return 5000 if hari_terlambat &gt; 0 else 0
    elif mode == "per_hari":
        return max(0, hari_terlambat) * 1000
    else:
        raise ValueError("mode denda tidak dikenal")


print(type(buat_item_lama("buku", "ESP32", "Budi")).__name__)
print(hitung_denda_lama(3, "per_hari"))
</code></pre>

<p>Output:</p>
<pre><code>Buku
3000
</code></pre>

<p>Fungsi di atas masih “jalan”, tapi setiap tempat yang butuh item baru atau mode denda baru akan menyalin cabang yang sama. Factory &amp; Strategy memindahkan cabang itu ke satu tempat yang punya nama jelas.</p>

<figure role="img" aria-label="Diagram Factory membuat Buku atau Ebook, Strategy memilih aturan denda flat atau per hari" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 740 320" style="display:block;max-width:740px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="oop50Arrow" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/></marker>
  </defs>
  <rect x="0" y="0" width="740" height="320" fill="#F5F5F0" rx="6"/>
  <text x="370" y="28" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Factory &amp; Strategy — domain perpustakaan</text>
  <rect x="40" y="50" width="200" height="56" rx="6" fill="#1a1a1a" stroke="#000" stroke-width="2.5"/>
  <text x="140" y="84" text-anchor="middle" fill="#fff" font-size="15" font-weight="700">buat_item(...)</text>
  <rect x="320" y="40" width="160" height="48" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="400" y="70" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Buku</text>
  <rect x="320" y="108" width="160" height="48" rx="6" fill="#FFF3E8" stroke="#000" stroke-width="2.5"/>
  <text x="400" y="138" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Ebook</text>
  <line x1="240" y1="70" x2="320" y2="64" stroke="#2979FF" stroke-width="2.5" marker-end="url(#oop50Arrow)"/>
  <line x1="240" y1="90" x2="320" y2="132" stroke="#2979FF" stroke-width="2.5" marker-end="url(#oop50Arrow)"/>
  <rect x="40" y="200" width="200" height="56" rx="6" fill="#1a1a1a" stroke="#000" stroke-width="2.5"/>
  <text x="140" y="234" text-anchor="middle" fill="#fff" font-size="15" font-weight="700">Kasir.denda</text>
  <rect x="320" y="190" width="180" height="48" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="410" y="220" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">DendaFlat</text>
  <rect x="320" y="258" width="180" height="48" rx="6" fill="#FFF3E8" stroke="#000" stroke-width="2.5"/>
  <text x="410" y="288" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">DendaPerHari</text>
  <line x1="240" y1="220" x2="320" y2="214" stroke="#2979FF" stroke-width="2.5" marker-end="url(#oop50Arrow)"/>
  <line x1="240" y1="240" x2="320" y2="282" stroke="#2979FF" stroke-width="2.5" marker-end="url(#oop50Arrow)"/>
  <rect x="540" y="190" width="160" height="116" rx="6" fill="#F0FFF4" stroke="#000" stroke-width="2.5"/>
  <text x="620" y="240" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">hitung(hari)</text>
  <text x="620" y="268" text-anchor="middle" fill="#2D3748" font-size="13">satu antarmuka</text>
  <line x1="500" y1="214" x2="540" y2="240" stroke="#2979FF" stroke-width="2.5" marker-end="url(#oop50Arrow)"/>
  <line x1="500" y1="282" x2="540" y2="260" stroke="#2979FF" stroke-width="2.5" marker-end="url(#oop50Arrow)"/>
</svg>
<figcaption style="margin-top:.75rem;color:#2D3748;font-size:.95rem">Factory memusatkan pembuatan object; Strategy memusatkan perilaku yang bisa diganti di runtime.</figcaption>
</figure>

<h2>Factory — satu pintu membuat item</h2>
<p>Factory di level Tier 2 (skala Capstone) cukup berupa <strong>fungsi</strong> (atau method class) yang mengembalikan subtype yang tepat. Pemanggil hanya tahu “saya butuh item”, bukan detail class:</p>

<pre><code class="language-python">from dataclasses import dataclass


@dataclass
class Buku:
    judul: str
    penulis: str

    def label(self) -&gt; str:
        return f"Buku: {self.judul} · {self.penulis}"


@dataclass
class Ebook(Buku):
    format_file: str = "pdf"

    def label(self) -&gt; str:
        return f"Ebook ({self.format_file}): {self.judul} · {self.penulis}"


def buat_item(jenis: str, judul: str, penulis: str, format_file: str = "pdf"):
    jenis = jenis.lower().strip()
    if jenis == "buku":
        return Buku(judul, penulis)
    if jenis == "ebook":
        return Ebook(judul, penulis, format_file)
    raise ValueError(f"jenis tidak dikenal: {jenis}")


items = [
    buat_item("buku", "ESP32 Praktis", "Budi"),
    buat_item("ebook", "Python OOP", "Sari", "epub"),
]
for item in items:
    print(item.label())

try:
    buat_item("majalah", "X", "Y")
except ValueError as e:
    print("error:", e)
</code></pre>

<p>Output:</p>
<pre><code>Buku: ESP32 Praktis · Budi
Ebook (epub): Python OOP · Sari
error: jenis tidak dikenal: majalah
</code></pre>
<p>Loop memakai <code>label()</code> yang sama — itu polymorphism dari <a href="/artikel/polymorphism-python-oop">Polymorphism (#45)</a>. Factory hanya menjawab: <em>object mana yang dibuat</em>. Cabang <code>ValueError</code> menjaga jenis yang belum didaftarkan gagal jelas di satu tempat.</p>
<p>Kenapa tidak membiarkan pemanggil menulis <code>Buku(...)</code> / <code>Ebook(...)</code> langsung? Boleh, untuk kode kecil. Factory berguna saat jenis datang dari string (form, JSON, CLI) atau saat nanti kamu menambah <code>Audiobook</code> tanpa menyentuh sepuluh pemanggil.</p>

<h2>Strategy — ganti aturan tanpa tulis ulang kasir</h2>
<p>Strategy = object yang mewakili <em>satu cara melakukan sesuatu</em>. Kasir (atau perpustakaan) <strong>punya</strong> strategi denda lewat composition, lalu memanggil antarmuka yang sama:</p>

<pre><code class="language-python">from abc import ABC, abstractmethod


class StrategiDenda(ABC):
    @abstractmethod
    def hitung(self, hari_terlambat: int) -&gt; int:
        """Kembalikan denda dalam rupiah."""


class DendaFlat(StrategiDenda):
    def __init__(self, nominal: int = 5000):
        self.nominal = nominal

    def hitung(self, hari_terlambat: int) -&gt; int:
        return self.nominal if hari_terlambat &gt; 0 else 0


class DendaPerHari(StrategiDenda):
    def __init__(self, per_hari: int = 1000):
        self.per_hari = per_hari

    def hitung(self, hari_terlambat: int) -&gt; int:
        return max(0, hari_terlambat) * self.per_hari


class Kasir:
    def __init__(self, strategi: StrategiDenda):
        self.strategi = strategi

    def denda(self, hari_terlambat: int) -&gt; int:
        return self.strategi.hitung(hari_terlambat)


kasir_a = Kasir(DendaFlat(5000))
kasir_b = Kasir(DendaPerHari(1000))
print("flat 3 hari:", kasir_a.denda(3))
print("per hari 3 hari:", kasir_b.denda(3))
print("flat 0 hari:", kasir_a.denda(0))
</code></pre>

<p>Output:</p>
<pre><code>flat 3 hari: 5000
per hari 3 hari: 3000
flat 0 hari: 0
</code></pre>
<p><code>Kasir</code> tidak peduli flat atau per hari — ia hanya memanggil <code>hitung</code>. Itu Strategy + composition. Kontrak ABC memastikan subclass baru tidak “lupa” method (lihat <a href="/artikel/abstraction-abc-python-oop">ABC (#46)</a>). Di runtime kamu bisa ganti: <code>kasir_a.strategi = DendaPerHari(1500)</code>.</p>

<h2>Gabungkan di domain Capstone</h2>
<p>Factory membuat koleksi; Strategy mengatur denda. Keduanya hidup berdampingan tanpa saling mengunci. Di Capstone, titik masuk praktis: ganti pembuatan <code>Buku</code>/<code>Ebook</code> mentah dengan <code>buat_item</code>, dan pindahkan rumus denda ke object strategi yang dipegang layanan (bukan diwariskan ke class buku).</p>

<pre><code class="language-python">from abc import ABC, abstractmethod
from dataclasses import dataclass


@dataclass
class Buku:
    judul: str
    penulis: str

    def label(self) -&gt; str:
        return f"Buku: {self.judul} · {self.penulis}"


@dataclass
class Ebook(Buku):
    format_file: str = "pdf"

    def label(self) -&gt; str:
        return f"Ebook ({self.format_file}): {self.judul} · {self.penulis}"


def buat_item(jenis, judul, penulis, format_file="pdf"):
    key = jenis.lower().strip()
    if key == "buku":
        return Buku(judul, penulis)
    if key == "ebook":
        return Ebook(judul, penulis, format_file)
    raise ValueError(f"jenis tidak dikenal: {jenis}")


class StrategiDenda(ABC):
    @abstractmethod
    def hitung(self, hari_terlambat: int) -&gt; int:
        raise NotImplementedError


class DendaPerHari(StrategiDenda):
    def __init__(self, per_hari=1000):
        self.per_hari = per_hari

    def hitung(self, hari_terlambat: int) -&gt; int:
        return max(0, hari_terlambat) * self.per_hari


class Kasir:
    def __init__(self, strategi: StrategiDenda):
        self.strategi = strategi

    def denda(self, hari_terlambat: int) -&gt; int:
        return self.strategi.hitung(hari_terlambat)


koleksi = [
    buat_item("buku", "MQTT Dasar", "Andi"),
    buat_item("ebook", "Flask Ringkas", "Dewi", "pdf"),
]
kasir = Kasir(DendaPerHari(1000))
for item in koleksi:
    print(item.label(), "-&gt; denda 2 hari:", kasir.denda(2))
</code></pre>

<p>Output:</p>
<pre><code>Buku: MQTT Dasar · Andi -&gt; denda 2 hari: 2000
Ebook (pdf): Flask Ringkas · Dewi -&gt; denda 2 hari: 2000
</code></pre>

<h2>Pola Dasar — Factory &amp; Strategy</h2>
<figure style="margin:1.5rem 0;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1.25rem;color:#1a1a1a" aria-label="Lima langkah Factory dan Strategy">
<ol style="list-style:none;padding:0;margin:0;color:#1a1a1a">
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Kenali cabang yang tersebar</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">String jenis item atau mode denda yang di-<code>if</code> di banyak file -&gt; kandidat Factory/Strategy.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Factory untuk “apa yang dibuat”</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem"><code>buat_item(jenis, ...)</code> mengembalikan <code>Buku</code>/<code>Ebook</code>; pemanggil tidak import semua class.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Strategy untuk “bagaimana dihitung”</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">ABC <code>hitung</code> + <code>DendaFlat</code>/<code>DendaPerHari</code>; layanan hanya menyimpan referensi strategi.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Tempel lewat composition</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem"><code>Kasir</code>/<code>Perpustakaan</code> <em>punya</em> strategi — jangan warisi denda ke class buku (lihat <a href="/artikel/composition-vs-inheritance-python">Composition (#47)</a>).</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">5</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Berhenti sebelum over-engineer</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Dua–tiga strategi + satu factory cukup. Jangan tambah AbstractFactory/Singleton “karena keren”.</span>
    </div>
  </li>
</ol>
</figure>

<h2>Kode lengkap — <code>factory_strategy_perpustakaan.py</code></h2>
<p>Simpan sebagai <code>factory_strategy_perpustakaan.py</code>, lalu jalankan <code>python factory_strategy_perpustakaan.py</code>. Skenario memakai <code>demo()</code> tetap — tidak ada prompt interaktif yang menggantung.</p>

<pre><code class="language-python">"""Factory &amp; Strategy ringan — domain perpustakaan (Tier 2 #50)."""

from __future__ import annotations

from abc import ABC, abstractmethod
from dataclasses import dataclass


@dataclass
class Buku:
    judul: str
    penulis: str

    def label(self) -> str:
        return f"Buku: {self.judul} · {self.penulis}"


@dataclass
class Ebook(Buku):
    format_file: str = "pdf"

    def label(self) -> str:
        return f"Ebook ({self.format_file}): {self.judul} · {self.penulis}"


def buat_item(
    jenis: str,
    judul: str,
    penulis: str,
    format_file: str = "pdf",
) -&gt; Buku:
    key = jenis.lower().strip()
    if key == "buku":
        return Buku(judul, penulis)
    if key == "ebook":
        return Ebook(judul, penulis, format_file)
    raise ValueError(f"jenis tidak dikenal: {jenis}")


class StrategiDenda(ABC):
    @abstractmethod
    def hitung(self, hari_terlambat: int) -&gt; int:
        raise NotImplementedError


class DendaFlat(StrategiDenda):
    def __init__(self, nominal: int = 5000) -&gt; None:
        self.nominal = nominal

    def hitung(self, hari_terlambat: int) -&gt; int:
        return self.nominal if hari_terlambat &gt; 0 else 0


class DendaPerHari(StrategiDenda):
    def __init__(self, per_hari: int = 1000) -&gt; None:
        self.per_hari = per_hari

    def hitung(self, hari_terlambat: int) -&gt; int:
        return max(0, hari_terlambat) * self.per_hari


class Perpustakaan:
    """Composition: punya koleksi + strategi denda."""

    def __init__(self, nama: str, strategi: StrategiDenda) -&gt; None:
        self.nama = nama
        self.strategi = strategi
        self._items: list[Buku] = []

    def tambah(self, jenis: str, judul: str, penulis: str, format_file: str = "pdf") -&gt; Buku:
        item = buat_item(jenis, judul, penulis, format_file)
        self._items.append(item)
        return item

    def denda(self, hari_terlambat: int) -&gt; int:
        return self.strategi.hitung(hari_terlambat)

    def ganti_strategi(self, strategi: StrategiDenda) -&gt; None:
        self.strategi = strategi

    @property
    def items(self) -&gt; list[Buku]:
        """Salinan baca — jangan mutasi stok dari luar (encapsulation)."""
        return list(self._items)

    def __str__(self) -&gt; str:
        return f"{self.nama}: {len(self._items)} item"


def demo() -&gt; None:
    lib = Perpustakaan("Kota A", DendaFlat(5000))
    lib.tambah("buku", "ESP32 Praktis", "Budi")
    lib.tambah("ebook", "Python OOP", "Sari", "epub")
    print(lib)
    for item in lib.items:
        print(" -", item.label())
    print("denda flat 4 hari:", lib.denda(4))
    lib.ganti_strategi(DendaPerHari(1000))
    print("denda per hari 4 hari:", lib.denda(4))
    print("denda 0 hari:", lib.denda(0))


if __name__ == "__main__":
    demo()
</code></pre>

<p>Output yang diharapkan:</p>
<pre><code>Kota A: 2 item
 - Buku: ESP32 Praktis · Budi
 - Ebook (epub): Python OOP · Sari
denda flat 4 hari: 5000
denda per hari 4 hari: 4000
denda 0 hari: 0
</code></pre>
<p><code>Perpustakaan.tambah</code> memakai Factory; <code>denda</code> / <code>ganti_strategi</code> memakai Strategy. <code>__str__</code> mengikuti kebiasaan dari <a href="/artikel/special-methods-dataclass-python">Special Methods (#48)</a>. Property <code>items</code> mengikuti semangat <a href="/artikel/encapsulation-property-python-oop">Encapsulation (#43)</a> — koleksi tidak diutak-atik lewat <code>_items</code> dari luar.</p>

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
      <td>Pemanggil masih penuh <code>if jenis</code></td>
      <td>Factory ada tapi tidak dipakai</td>
      <td>Semua pembuatan lewat <code>buat_item</code> / method factory</td>
    </tr>
    <tr>
      <td>Denda di-hardcode di method pinjam</td>
      <td>Belum dipisah sebagai strategi</td>
      <td>Pindahkan rumus ke class strategi (method <code>hitung</code>); layanan hanya memanggil</td>
    </tr>
    <tr>
      <td><code>Buku</code> mewarisi <code>DendaFlat</code></td>
      <td>Salah pakai inheritance</td>
      <td>Composition: layanan <em>punya</em> strategi, buku tetap data</td>
    </tr>
    <tr>
      <td>AbstractFactory + 12 class untuk 2 jenis</td>
      <td>Over-engineer</td>
      <td>Fungsi factory + 2–3 strategy cukup untuk skala Capstone</td>
    </tr>
    <tr>
      <td>Mutasi <code>lib._items</code> dari luar</td>
      <td>Melewati encapsulation koleksi</td>
      <td>Pakai property/method publik (<code>items</code> / <code>tambah</code>) — lihat <a href="/artikel/encapsulation-property-python-oop">Encapsulation (#43)</a></td>
    </tr>
    <tr>
      <td><code>ValueError: jenis tidak dikenal</code></td>
      <td>Typo string / belum daftarkan jenis baru</td>
      <td>Normalisasi <code>lower().strip()</code>; tambah cabang di satu tempat</td>
    </tr>
  </tbody>
</table>

<h2>Latihan singkat</h2>
<ol>
  <li>Tambah jenis <code>audiobook</code> di <code>buat_item</code> (lihat pola inheritance di <a href="/artikel/inheritance-pewarisan-class-python">Inheritance (#44)</a>) dengan field <code>durasi_menit</code>.</li>
  <li>Buat <code>DendaBertingkat</code>: 0 hari = 0; 1–3 hari = 2000; &gt;3 hari = 2000 + 1500 per hari ekstra.</li>
  <li>Ubah <code>demo()</code> agar mencetak denda untuk setiap item (tetap tanpa prompt interaktif).</li>
</ol>

<h2>FAQ singkat</h2>
<p><strong>Apa bedanya Factory dengan constructor biasa?</strong><br>Constructor membuat <em>satu</em> class. Factory memilih <em>class mana</em> berdasarkan input (sering string/enum) lalu memanggil constructor yang tepat.</p>
<p><strong>Haruskah Strategy selalu pakai ABC?</strong><br>Tidak wajib. Duck typing + method <code>hitung</code> sudah polymorphism (<a href="/artikel/polymorphism-python-oop">Polymorphism (#45)</a>). ABC membantu IDE/kontrak saat tim membesar — pola yang sama di <a href="/artikel/abstraction-abc-python-oop">ABC (#46)</a>.</p>
<p><strong>Apakah ini menggantikan Capstone?</strong><br>Tidak. <a href="/artikel/capstone-sistem-perpustakaan-mini-oop-python">Capstone (#49)</a> adalah sistem utuh. Artikel ini menambahkan dua resep agar Capstone lebih mudah dikembangkan tanpa hutan <code>if</code>.</p>
<p><strong>Kapan tidak perlu pattern?</strong><br>Satu jenis object dan satu rumus denda selamanya -&gt; class biasa cukup. Pattern dibayar dengan indirection; pakai saat cabang mulai menyebar.</p>
<p><strong>Lanjut ke mana?</strong><br>Lanjut Tier 2: <a href="/artikel/oop-micropython-esp32-class-sensor">OOP di MicroPython / ESP32 (#51)</a> — class Sensor &amp; Node sebagai jembatan ke Seri IoT. Lanjut web: <a href="/artikel/oop-flask-fastapi-class-api">Class di Flask / FastAPI (#52)</a>.</p>

<h2>Kesimpulan &amp; langkah berikutnya</h2>
<p><strong>Factory</strong> memusatkan pembuatan object; <strong>Strategy</strong> memusatkan perilaku yang bisa diganti. Keduanya memakai polymorphism + composition — bukan hierarchy baru yang rumit.</p>
<p>Artikel ini adalah <strong>#50 (ini)</strong> — pembuka Tier 2 setelah Seri 3 selesai di <a href="/artikel/capstone-sistem-perpustakaan-mini-oop-python">Capstone (#49)</a>.</p>
<p>Lanjut: <a href="/artikel/oop-micropython-esp32-class-sensor">OOP di MicroPython / ESP32 (#51)</a>. Seri 3 tetap fondasi yang sudah lengkap (sepuluh artikel inti sampai Capstone).</p>

<blockquote>
  <p><strong>Tier 2 progress:</strong> langkah <strong>#50 (ini)</strong> · Seri 3 tetap 10/10 live. Prasyarat: <a href="/artikel/capstone-sistem-perpustakaan-mini-oop-python">Capstone (#49)</a> · <a href="/artikel/polymorphism-python-oop">Polymorphism (#45)</a> · <a href="/artikel/abstraction-abc-python-oop">ABC (#46)</a> · <a href="/artikel/composition-vs-inheritance-python">Composition (#47)</a> · <a href="/artikel/special-methods-dataclass-python">Special Methods (#48)</a> · <a href="/artikel/encapsulation-property-python-oop">Encapsulation (#43)</a> · <a href="/artikel/inheritance-pewarisan-class-python">Inheritance (#44)</a> · <a href="/artikel/mengenal-oop-cara-berpikir-dengan-objek-python">Mengenal OOP (#40)</a>.</p>
</blockquote>
HTML;
    }
}
