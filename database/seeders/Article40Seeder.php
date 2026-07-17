<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article40Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $progCat = Category::where('slug', 'programming')->first();

        if (! $admin || ! $progCat) {
            throw new \RuntimeException('User atau kategori programming tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'mengenal-oop-cara-berpikir-dengan-objek-python';

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
                'title'           => 'Mengenal OOP: Cara Berpikir dengan Objek (Python)',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => true,
                'seo_title'       => 'Mengenal OOP Python — Cara Berpikir dengan Objek untuk Pemula',
                'seo_description' => 'Mulai Seri 3 OOP: soft-landing Python, procedural vs class, empat pilar OOP, dan kapan tidak perlu objek — berbahasa Indonesia.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['python', 'oop', 'oop-class'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel ke-40 berhasil dipublish: '.$article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — Seri 3: dari perangkat ke cara berpikir</h2>
<p>Seri ESP32 di Koding Indonesia mengajarkan <strong>membangun perangkat</strong>: sensor, MQTT, dashboard, sampai <a href="/artikel/smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt">capstone greenhouse (#39)</a>. Seri 3 ini bergeser ke fondasi yang sama pentingnya: <strong>merapikan pikiran &amp; kode</strong> supaya proyek (IoT, web, script) tidak jadi spaghetti.</p>

<p>Artikel ini adalah <strong>pembuka Seri 3 — Pemrograman Berorientasi Objek (OOP) dengan Python</strong>. Kamu belum wajib pernah memakai <code>class</code>. Yang dibutuhkan: rasa ingin paham kenapa orang bilang “pikirkan objek dulu, baru tulis kode”.</p>

<blockquote>
  <p><strong>Prasyarat ringan:</strong> pernah menulis variabel dan fungsi sederhana (bahasa apa saja). Kalau kamu dari dunia Arduino/C++, bagian soft-landing Python di bawah akan membantu. Contoh Python di ekosistem Kindo sudah ada di <a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">subscriber MQTT → MySQL (#18)</a> — artikel itu fokus IoT; artikel ini fokus <em>cara berpikir</em> OOP.</p>
</blockquote>

<h2>Soft-landing Python (5 menit)</h2>
<p>OOP di Seri 3 memakai <strong>Python 3.11+</strong>. Instal dari <a href="https://www.python.org/downloads/" rel="noopener noreferrer" target="_blank">python.org</a> (centang “Add Python to PATH” di Windows), lalu cek di terminal:</p>

<pre><code class="language-bash">python --version
# contoh keluaran: Python 3.12.3
</code></pre>

<p>Dua sintaks yang cukup untuk membaca contoh di artikel ini:</p>

<pre><code class="language-python"># variabel
judul = "Dasar OOP"
halaman = 120

# fungsi
def sapa(nama):
    return f"Halo, {nama}!"

print(sapa("Anton"))
</code></pre>

<p>Kalau perintah <code>python</code> tidak ketemu di Windows, coba <code>py --version</code>. Editor: VS Code + ekstensi Python sudah cukup.</p>

<h2>Masalah yang OOP coba selesaikan</h2>
<p>Bayangkan kamu mengelola data buku di perpustakaan mini. Versi <strong>prosedural</strong> (data + fungsi terpisah) cepat terlihat berantakan:</p>

<pre><code class="language-python"># Data tersebar di beberapa dict / list
buku_a = {"judul": "Belajar Python", "penulis": "Sari", "tahun": 2024}
buku_b = {"judul": "ESP32 Praktis", "penulis": "Budi", "tahun": 2023}

def info_buku(buku):
    return f'{buku["judul"]} oleh {buku["penulis"]} ({buku["tahun"]})'

def pinjam(buku, anggota):
    # aturan pinjam tersebar di banyak fungsi…
    print(f'{anggota} meminjam {buku["judul"]}')

print(info_buku(buku_a))
pinjam(buku_b, "Rina")
</code></pre>

<p>Semakin banyak fitur (denda, e-book, majalah, antrian pinjam), semakin banyak fungsi yang harus “mengingat” struktur dict yang sama. Satu typo key (<code>"Judul"</code> vs <code>"judul"</code>) sudah cukup bikin bug diam-diam.</p>

<p><strong>OOP</strong> mengajak kamu menggabungkan <em>data</em> dan <em>perilaku</em> yang saling terkait ke dalam satu konsep: <strong>objek</strong>. Class adalah cetakannya; object adalah barang konkretnya.</p>

<h2>Class vs object — cetakan dan barangnya</h2>
<figure role="img" aria-label="Diagram: class Buku sebagai cetakan, dua object buku_a dan buku_b sebagai instance dengan data berbeda" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 340" style="display:block;max-width:720px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="oop40Arrow" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/></marker>
    <marker id="oop40ArrowOrange" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#FF7A2F"/></marker>
  </defs>
  <rect x="0" y="0" width="720" height="340" fill="#F5F5F0" rx="6"/>
  <!-- Class: vertikal tengah -->
  <rect x="40" y="100" width="240" height="140" rx="6" fill="#2979FF" stroke="#000" stroke-width="2.5"/>
  <text x="160" y="138" text-anchor="middle" fill="#fff" font-size="16" font-weight="700">class Buku</text>
  <text x="160" y="164" text-anchor="middle" fill="#e3f2fd" font-size="13">Cetakan / blueprint</text>
  <text x="160" y="188" text-anchor="middle" fill="#cfe4ff" font-size="12">judul · penulis · tahun</text>
  <text x="160" y="212" text-anchor="middle" fill="#cfe4ff" font-size="12">+ info() · pinjam()</text>
  <!-- Object atas -->
  <rect x="400" y="28" width="280" height="100" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="540" y="58" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">object buku_a</text>
  <text x="540" y="82" text-anchor="middle" fill="#4A5568" font-size="13">"Belajar Python" · Sari · 2024</text>
  <text x="540" y="106" text-anchor="middle" fill="#4A5568" font-size="12">satu barang konkret</text>
  <!-- Object bawah -->
  <rect x="400" y="212" width="280" height="100" rx="6" fill="#FFF3E8" stroke="#000" stroke-width="2.5"/>
  <text x="540" y="242" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">object buku_b</text>
  <text x="540" y="266" text-anchor="middle" fill="#4A5568" font-size="13">"ESP32 Praktis" · Budi · 2023</text>
  <text x="540" y="290" text-anchor="middle" fill="#4A5568" font-size="12">cetakan sama, data beda</text>
  <!-- Panah diagonal bersih (bukan L putus-putus) -->
  <line x1="280" y1="130" x2="400" y2="78" stroke="#2979FF" stroke-width="2.5" marker-end="url(#oop40Arrow)"/>
  <text x="300" y="92" fill="#4A5568" font-size="11" font-weight="600">buat</text>
  <text x="300" y="106" fill="#4A5568" font-size="11" font-weight="600">instance</text>
  <line x1="280" y1="210" x2="400" y2="262" stroke="#FF7A2F" stroke-width="2.5" marker-end="url(#oop40ArrowOrange)"/>
  <text x="300" y="248" fill="#4A5568" font-size="11" font-weight="600">buat</text>
  <text x="300" y="262" fill="#4A5568" font-size="11" font-weight="600">instance</text>
</svg>
<figcaption style="margin-top:.75rem;color:#4A5568;font-size:.95rem">Satu class bisa menghasilkan banyak object — masing-masing punya state sendiri.</figcaption>
</figure>

<p>Cuplikan “rasa” OOP (detail sintaks <code>class</code> di <a href="/artikel/class-dan-object-pertama-python">Class &amp; Object (#41)</a>):</p>

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
</code></pre>

<p><code>buku_a</code> dan <code>buku_b</code> adalah dua object berbeda dari cetakan yang sama. Itulah inti mental model OOP untuk pemula.</p>

<h2>Empat pilar OOP — preview singkat</h2>
<p>Seri 3 akan membahas empat ide klasik satu per satu. Di artikel pembuka ini cukup kenalan:</p>

<table>
  <thead>
    <tr>
      <th>Pilar</th>
      <th>Inti dalam satu kalimat</th>
      <th>Bahas di</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>Encapsulation</strong></td>
      <td>Sembunyikan detail; tampilkan antarmuka yang aman.</td>
      <td>Artikel Encapsulation (menyusul)</td>
    </tr>
    <tr>
      <td><strong>Inheritance</strong></td>
      <td>Class anak mewarisi perilaku class induk.</td>
      <td>Artikel Inheritance (menyusul)</td>
    </tr>
    <tr>
      <td><strong>Polymorphism</strong></td>
      <td>Satu cara panggil, banyak bentuk perilaku.</td>
      <td>Artikel Polymorphism (menyusul)</td>
    </tr>
    <tr>
      <td><strong>Abstraction</strong></td>
      <td>Fokus ke “apa yang bisa dilakukan”, bukan “bagaimana di dalam”.</td>
      <td>Artikel Abstraction (menyusul)</td>
    </tr>
  </tbody>
</table>

<p>Jangan hafalkan definisi formal dulu. Yang penting: OOP bukan sekadar menulis kata <code>class</code> — melainkan <strong>mengatur tanggung jawab</strong> di kode. Detail sintaks <code>class</code> ada di <a href="/artikel/class-dan-object-pertama-python">Class dan Object Pertama (#41)</a>.</p>

<h2>Kapan kamu <em>tidak</em> perlu OOP?</h2>
<p>OOP bukan agama. Untuk skrip 20 baris yang sekali jalan, dict + fungsi sering lebih jujur dan cepat. Pakai OOP saat:</p>
<ul>
  <li>Ada <strong>banyak data sejenis</strong> yang sering diperlakukan sama (banyak buku, banyak sensor node, banyak user)</li>
  <li>Aturan bisnis mulai tersebar di banyak fungsi</li>
  <li>Kamu ingin batas jelas: “modul A tidak boleh mengutak-atik isi dalam B”</li>
</ul>
<p>Kalau semua cukup dengan satu fungsi <code>main()</code> yang linear — tunggu sampai rasa “berantakan” muncul, baru refactor ke class. Itu juga bagian dari berpikir dewasa sebagai programmer.</p>

<h2>Pola Dasar — berpikir objek sebelum coding</h2>
<figure style="margin:1.5rem 0;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1.25rem;color:#1a1a1a" aria-label="Lima langkah berpikir objek sebelum menulis class">
<ol style="list-style:none;padding:0;margin:0;color:#1a1a1a">
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Sebut benda dunia nyata</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Buku, anggota perpustakaan, sensor, pompa — bukan “data1” dan “fungsi2”.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Catat data yang melekat</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Untuk Buku: judul, penulis, tahun. Itu calon <em style="color:#2D3748">attribute</em>.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#FF7A2F;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Catat aksi yang masuk akal</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">info(), pinjam(), kembalikan() — calon <em style="color:#2D3748">method</em>.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#FF7A2F;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Buat satu contoh konkret di kepala</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">“buku_a = Belajar Python milik Sari, 2024” — itu object.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#1a1a1a;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">5</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Baru tulis class</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Sintaks Python menyusul di <a href="/artikel/class-dan-object-pertama-python">#41</a>. Mental model dulu, keyboard kemudian.</span>
    </div>
  </li>
</ol>
<figcaption style="margin-top:1rem;color:#2D3748;font-size:.95rem">Ringkas: benda → data → aksi → contoh → class.</figcaption>
</figure>

<h2>Indeks rencana Seri 3 (OOP Python)</h2>
<p>Artikel ini adalah <strong>#40 (ini)</strong> — pembuka Seri 3. Jalur linear yang akan kita tempuh:</p>
<ol>
  <li><strong>#40 (ini)</strong> — Mengenal OOP &amp; cara berpikir objek</li>
  <li><a href="/artikel/class-dan-object-pertama-python">Class dan object pertama di Python (#41)</a></li>
  <li>Attribute, method, dan constructor <code>__init__</code></li>
  <li>Encapsulation &amp; <code>@property</code></li>
  <li>Inheritance</li>
  <li>Polymorphism</li>
  <li>Abstraction &amp; ABC</li>
  <li>Composition vs inheritance</li>
  <li>Special methods &amp; dataclass</li>
  <li>Capstone: sistem perpustakaan mini</li>
</ol>
<p>Domain contoh sepanjang seri: <strong>perpustakaan / inventaris buku</strong> — sederhana, mudah divisualisasikan, dan bisa dihubungkan ke IoT di jalur opsional nanti (MicroPython).</p>

<h2>Latihan singkat</h2>
<ol>
  <li>Pilih satu benda di proyekmu (buku, sensor DHT, lampu relay). Tulis 3 data + 2 aksi di kertas.</li>
  <li>Bandingkan: kalau masih dict + fungsi, di mana bug paling mungkin muncul?</li>
  <li>(Opsional) Buka <a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">subscriber MQTT → MySQL (#18)</a> dan tandai mana yang “prosedural murni” vs mana yang sudah terasa seperti tanggung jawab tersendiri (subscriber, parser, database).</li>
</ol>

<h2>FAQ singkat</h2>
<p><strong>Apakah OOP hanya untuk Python?</strong><br>Tidak. Konsepnya ada di Java, C++, C#, PHP, bahkan sebagian di firmware C++ Arduino. Python dipilih karena sintaksnya ramah untuk belajar konsep.</p>
<p><strong>Apakah saya harus hapus semua dict?</strong><br>Tidak. Dict tetap sah. Class berguna saat data + aturan mulai “hidup” bersama.</p>
<p><strong>Kenapa tidak langsung inheritance?</strong><br>Karena banyak orang terburu mewarisi class sebelum paham object sederhana. Urutan Seri 3 sengaja linear.</p>

<h2>Kesimpulan &amp; langkah berikutnya</h2>
<p>OOP adalah cara mengelompokkan <strong>data + perilaku</strong> menjadi object dari sebuah class (cetakan). Empat pilar menunggu di artikel Encapsulation sampai Abstraction; hari ini cukup punya mental model dan Python yang siap jalan.</p>
<p>Lanjut ke <a href="/artikel/class-dan-object-pertama-python">Class dan Object Pertama di Python (#41)</a>: kita menulis <code>class Buku</code> dari nol, membuat beberapa instance, dan melihat identitas tiap object.</p>

<blockquote>
  <p><strong>Seri 3 progress:</strong> 2/10 artikel live (#40–#41). Lanjut <a href="/artikel/class-dan-object-pertama-python">Class &amp; Object (#41)</a>. Capstone: sistem perpustakaan mini.</p>
</blockquote>
HTML;
    }
}
