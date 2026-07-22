<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article53Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $webCat = Category::where('slug', 'web-development')->first()
            ?? Category::where('slug', 'programming')->first();

        if (! $admin || ! $webCat) {
            throw new \RuntimeException('User atau kategori web-development/programming tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'http-rest-kontrak-stub-flask-oop';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        foreach ([
            'python' => 'python',
            'http' => 'http',
            'rest' => 'rest',
            'api' => 'api',
            'web' => 'web',
            'flask' => 'flask',
            'oop' => 'oop',
        ] as $tagSlug => $tagName) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagName]);
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $webCat->id,
                'title'           => 'HTTP & REST: Kontrak di Balik Stub Flask OOP',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'HTTP & REST — Kontrak Stub OOP ke API Perpustakaan',
                'seo_description' => 'Pahami method, status, dan resource REST dari stub HttpResponse #52 — tanpa wajib install Flask dulu, siap masuk routing nyata.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['python', 'http', 'rest', 'api', 'web', 'flask', 'oop'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel ke-53 berhasil dipublish: '.$article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — stub sudah ada, kontraknya belum</h2>
<p>Di <a href="/artikel/oop-flask-fastapi-class-api">Class Flask / FastAPI (#52)</a> kamu sudah punya <code>HttpResponse</code>, <code>handle_list</code>, dan <code>handle_create</code> yang runnable tanpa server. Seri 4 Web Lanjut memulai dari sini: memahami <strong>kontrak HTTP/REST</strong> supaya saat Flask dipasang di artikel berikutnya, route tidak jadi tebak-tebakan.</p>
<p>Artikel ini sengaja tetap di PC tanpa wajib <code>pip install flask</code>. Kita perluas stub jadi “mini router”: method + path + status — lalu baru siap masuk pintu framework.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> <a href="/artikel/oop-flask-fastapi-class-api">OOP Flask / FastAPI (#52)</a> · idealnya <a href="/artikel/capstone-sistem-perpustakaan-mini-oop-python">Capstone (#49)</a> · <a href="/artikel/composition-vs-inheritance-python">Composition (#47)</a> · <a href="/artikel/mengenal-oop-cara-berpikir-dengan-objek-python">OOP (#40)</a>.</p>
</blockquote>

<h2>Kenapa HTTP dulu, bukan langsung Flask?</h2>
<table>
  <thead>
    <tr>
      <th>Tanpa kontrak</th>
      <th>Gejala</th>
      <th>Dengan kontrak</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Langsung tulis <code>@app.get</code></td>
      <td>Status “asal 200”; GET mengubah data</td>
      <td>Method &amp; status dipilih sadar</td>
    </tr>
    <tr>
      <td>JSON “asal return”</td>
      <td>Client bingung sukses vs gagal</td>
      <td>Body + status = satu paket (<code>HttpResponse</code>)</td>
    </tr>
  </tbody>
</table>
<p>REST di sini dipakai ringan: resource punya URL, aksi memakai method HTTP — bukan dogma “pure REST” penuh. Nama path mengikuti benda (<code>/api/buku</code>), bukan kata kerja acak seperti <code>/buatBuku</code> — supaya client bisa menebak pola tanpa membaca source.</p>

<pre><code class="language-python"># Inti yang sudah ada di #52 — kita pakai lagi
class HttpResponse:
    def __init__(self, status, body):
        self.status = status
        self.body = body


ok = HttpResponse(201, {"judul": "OOP Python"})
bad = HttpResponse(400, {"error": "judul wajib"})
print(ok.status, ok.body)
print(bad.status, bad.body)
</code></pre>

<p>Output:</p>
<pre><code>201 {'judul': 'OOP Python'}
400 {'error': 'judul wajib'}
</code></pre>

<figure role="img" aria-label="Diagram Client ke method path lalu handler dan HttpResponse" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 250" style="display:block;max-width:720px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="oop53Arrow" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/></marker>
  </defs>
  <rect x="0" y="0" width="720" height="250" fill="#F5F5F0" rx="6"/>
  <text x="360" y="28" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Request = method + path + body</text>
  <rect x="40" y="70" width="140" height="56" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="110" y="104" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">Client</text>
  <rect x="250" y="70" width="200" height="56" rx="6" fill="#1a1a1a" stroke="#000" stroke-width="2.5"/>
  <text x="350" y="104" text-anchor="middle" fill="#fff" font-size="14" font-weight="700">GET /api/buku</text>
  <rect x="520" y="70" width="160" height="56" rx="6" fill="#FFF3E8" stroke="#000" stroke-width="2.5"/>
  <text x="600" y="104" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">Handler</text>
  <line x1="180" y1="98" x2="250" y2="98" stroke="#2979FF" stroke-width="2.5" marker-end="url(#oop53Arrow)"/>
  <line x1="450" y1="98" x2="520" y2="98" stroke="#2979FF" stroke-width="2.5" marker-end="url(#oop53Arrow)"/>
  <rect x="520" y="170" width="160" height="48" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="600" y="200" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">HttpResponse</text>
  <line x1="600" y1="126" x2="600" y2="170" stroke="#2979FF" stroke-width="2.5" marker-end="url(#oop53Arrow)"/>
  <text x="350" y="160" text-anchor="middle" fill="#2D3748" font-size="12">status + body</text>
</svg>
<figcaption style="margin-top:.75rem;color:#2D3748;font-size:.95rem">Framework nanti hanya menerjemahkan baris “GET /api/buku” menjadi pemanggilan handler.</figcaption>
</figure>

<h2>Method &amp; status — kamus singkat</h2>
<table>
  <thead>
    <tr>
      <th>Method</th>
      <th>Makna ringan</th>
      <th>Status tipikal</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><code>GET</code></td>
      <td>Baca resource; jangan ubah data</td>
      <td>200 + daftar/item</td>
    </tr>
    <tr>
      <td><code>POST</code></td>
      <td>Buat resource baru</td>
      <td>201 dibuat · 400 validasi gagal</td>
    </tr>
  </tbody>
</table>
<p>Untuk Seri 4 kita fokus GET/POST dulu. PUT/PATCH/DELETE menyusul saat resource punya id stabil.</p>

<pre><code class="language-python">def ringkas_status(code):
    if code == 200:
        return "OK baca"
    if code == 201:
        return "Created"
    if code == 400:
        return "Bad Request"
    if code == 404:
        return "Not Found"
    if code == 405:
        return "Method Not Allowed"
    return f"lain:{code}"


for c in (200, 201, 400, 404, 405):
    print(c, "-&gt;", ringkas_status(c))
</code></pre>

<p>Output:</p>
<pre><code>200 -&gt; OK baca
201 -&gt; Created
400 -&gt; Bad Request
404 -&gt; Not Found
405 -&gt; Method Not Allowed
</code></pre>
<p>404 dan 405 sering tertukar: path salah vs method salah — kita bedakan lagi di mini-router.</p>

<h2>Resource <code>/api/buku</code> — satu URL, dua aksi</h2>
<p>Resource perpustakaan kita tetap domain yang sama dengan Capstone. Bedanya: klien berbicara lewat path, bukan menu CLI.</p>

<pre><code class="language-python">class BukuItem:
    def __init__(self, judul, penulis):
        self.judul = judul
        self.penulis = penulis

    def as_dict(self):
        return {"judul": self.judul, "penulis": self.penulis}


class PerpustakaanService:
    def __init__(self):
        self._items = []

    def tambah(self, judul, penulis):
        if not judul.strip():
            raise ValueError("judul wajib")
        item = BukuItem(judul.strip(), penulis.strip() or "Anonim")
        self._items.append(item)
        return item

    def daftar(self):
        return [i.as_dict() for i in self._items]


class HttpResponse:
    def __init__(self, status, body):
        self.status = status
        self.body = body


def handle_list(service):
    return HttpResponse(200, {"items": service.daftar()})


def handle_create(service, judul, penulis):
    try:
        item = service.tambah(judul, penulis)
    except ValueError as exc:
        return HttpResponse(400, {"error": str(exc)})
    return HttpResponse(201, item.as_dict())


svc = PerpustakaanService()
print("GET /api/buku -&gt;", handle_list(svc).status)
print("POST kosong -&gt;", handle_create(svc, "  ", "X").status)
print("POST ok -&gt;", handle_create(svc, "REST Ringkas", "Dewi").status)
print("GET lagi -&gt;", handle_list(svc).body)
</code></pre>

<p>Output:</p>
<pre><code>GET /api/buku -&gt; 200
POST kosong -&gt; 400
POST ok -&gt; 201
GET lagi -&gt; {'items': [{'judul': 'REST Ringkas', 'penulis': 'Dewi'}]}
</code></pre>

<h2>Mini router — method + path tanpa Flask</h2>
<p>Ini jembatan mental ke decorator Flask: kita “dispatch” request ke handler yang sudah ada.</p>

<pre><code class="language-python">class BukuItem:
    def __init__(self, judul, penulis):
        self.judul = judul
        self.penulis = penulis

    def as_dict(self):
        return {"judul": self.judul, "penulis": self.penulis}


class PerpustakaanService:
    def __init__(self):
        self._items = []

    def tambah(self, judul, penulis):
        if not judul.strip():
            raise ValueError("judul wajib")
        item = BukuItem(judul.strip(), penulis.strip() or "Anonim")
        self._items.append(item)
        return item

    def daftar(self):
        return [i.as_dict() for i in self._items]


class HttpResponse:
    def __init__(self, status, body):
        self.status = status
        self.body = body


class HttpRequest:
    def __init__(self, method, path, body=None):
        self.method = method.upper()
        self.path = path
        self.body = body or {}


def handle_list(service):
    return HttpResponse(200, {"items": service.daftar()})


def handle_create(service, judul, penulis):
    try:
        item = service.tambah(judul, penulis)
    except ValueError as exc:
        return HttpResponse(400, {"error": str(exc)})
    return HttpResponse(201, item.as_dict())


def dispatch(service, request):
    if request.path != "/api/buku":
        return HttpResponse(404, {"error": "path tidak dikenal"})
    if request.method == "GET":
        return handle_list(service)
    if request.method == "POST":
        return handle_create(
            service,
            request.body.get("judul", ""),
            request.body.get("penulis", ""),
        )
    return HttpResponse(405, {"error": "method tidak diizinkan"})


svc = PerpustakaanService()
print(dispatch(svc, HttpRequest("GET", "/api/buku")).status)
print(dispatch(svc, HttpRequest("POST", "/api/buku", {"judul": "HTTP", "penulis": "Sari"})).status)
print(dispatch(svc, HttpRequest("GET", "/api/salah")).status)
print(dispatch(svc, HttpRequest("DELETE", "/api/buku")).status)
</code></pre>

<p>Output:</p>
<pre><code>200
201
404
405
</code></pre>
<p>Perhatikan: <code>DELETE</code> menolak dengan 405 — jujur soal method yang belum kita dukung, bukan “diam-diam 200”.</p>

<h2>GET aman diulang, POST berhati-hati</h2>
<p>Idempotensi ringan: memanggil GET berkali-kali tidak menambah buku. POST yang sama bisa menambah entri baru — itu normal untuk “create” tanpa id unik dulu.</p>

<pre><code class="language-python">class BukuItem:
    def __init__(self, judul, penulis):
        self.judul = judul
        self.penulis = penulis

    def as_dict(self):
        return {"judul": self.judul, "penulis": self.penulis}


class PerpustakaanService:
    def __init__(self):
        self._items = []

    def tambah(self, judul, penulis):
        if not judul.strip():
            raise ValueError("judul wajib")
        item = BukuItem(judul.strip(), penulis.strip() or "Anonim")
        self._items.append(item)
        return item

    def daftar(self):
        return [i.as_dict() for i in self._items]


class HttpResponse:
    def __init__(self, status, body):
        self.status = status
        self.body = body


def handle_list(service):
    return HttpResponse(200, {"items": service.daftar()})


def handle_create(service, judul, penulis):
    try:
        item = service.tambah(judul, penulis)
    except ValueError as exc:
        return HttpResponse(400, {"error": str(exc)})
    return HttpResponse(201, item.as_dict())


svc = PerpustakaanService()
handle_create(svc, "Satu", "A")
print("setelah 1 POST, jumlah=", len(handle_list(svc).body["items"]))
handle_list(svc)
handle_list(svc)
print("setelah 2 GET, jumlah tetap=", len(handle_list(svc).body["items"]))
handle_create(svc, "Satu", "A")
print("POST judul sama lagi, jumlah=", len(handle_list(svc).body["items"]))
</code></pre>

<p>Output:</p>
<pre><code>setelah 1 POST, jumlah= 1
setelah 2 GET, jumlah tetap= 1
POST judul sama lagi, jumlah= 2
</code></pre>

<h2>Pola Dasar — kontrak sebelum framework</h2>
<figure style="margin:1.5rem 0;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1.25rem;color:#1a1a1a" aria-label="Lima langkah HTTP REST sebelum Flask">
<ol style="list-style:none;padding:0;margin:0;color:#1a1a1a">
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Tentukan resource</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem"><code>/api/buku</code> — koleksi, bukan “halaman PHP acak”.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Pilih method</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">GET baca · POST buat — jangan campur di satu fungsi tanpa beda status.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Status + body bersama</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem"><code>HttpResponse</code> dari <a href="/artikel/oop-flask-fastapi-class-api">Flask/FastAPI (#52)</a> — jangan sukses palsu 200.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Dispatch tipis</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem"><code>HttpRequest</code> + <code>dispatch</code> — bayangan decorator Flask.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">5</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Baru pasang framework</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Artikel berikutnya: routing Flask nyata — service tetap sama.</span>
    </div>
  </li>
</ol>
</figure>

<h2>Kode lengkap — <code>http_rest_kontrak.py</code></h2>
<p>Simpan dan jalankan: <code>python http_rest_kontrak.py</code>.</p>

<pre><code class="language-python">"""HTTP/REST ringan di atas stub OOP (Seri 4 #53).
Lanjut ke Flask routing di artikel berikutnya — service/handler tetap dipakai.
"""

from __future__ import annotations


class BukuItem:
    def __init__(self, judul: str, penulis: str) -&gt; None:
        self.judul = judul
        self.penulis = penulis

    def as_dict(self) -&gt; dict:
        return {"judul": self.judul, "penulis": self.penulis}


class PerpustakaanService:
    def __init__(self) -&gt; None:
        self._items: list[BukuItem] = []

    def tambah(self, judul: str, penulis: str) -&gt; BukuItem:
        if not judul.strip():
            raise ValueError("judul wajib")
        item = BukuItem(judul.strip(), penulis.strip() or "Anonim")
        self._items.append(item)
        return item

    def daftar(self) -&gt; list[dict]:
        return [i.as_dict() for i in self._items]


class HttpResponse:
    def __init__(self, status: int, body) -&gt; None:
        self.status = status
        self.body = body


class HttpRequest:
    def __init__(self, method: str, path: str, body: dict | None = None) -&gt; None:
        self.method = method.upper()
        self.path = path
        self.body = body or {}


def handle_list(service: PerpustakaanService) -&gt; HttpResponse:
    return HttpResponse(200, {"items": service.daftar()})


def handle_create(service: PerpustakaanService, judul: str, penulis: str) -&gt; HttpResponse:
    try:
        item = service.tambah(judul, penulis)
    except ValueError as exc:
        return HttpResponse(400, {"error": str(exc)})
    return HttpResponse(201, item.as_dict())


def dispatch(service: PerpustakaanService, request: HttpRequest) -&gt; HttpResponse:
    if request.path != "/api/buku":
        return HttpResponse(404, {"error": "path tidak dikenal"})
    if request.method == "GET":
        return handle_list(service)
    if request.method == "POST":
        return handle_create(
            service,
            str(request.body.get("judul", "")),
            str(request.body.get("penulis", "")),
        )
    return HttpResponse(405, {"error": "method tidak diizinkan"})


def demo() -&gt; None:
    svc = PerpustakaanService()
    r1 = dispatch(svc, HttpRequest("GET", "/api/buku"))
    r2 = dispatch(svc, HttpRequest("POST", "/api/buku", {"judul": "REST", "penulis": "Kindo"}))
    r3 = dispatch(svc, HttpRequest("POST", "/api/buku", {"judul": "  ", "penulis": "X"}))
    r4 = dispatch(svc, HttpRequest("GET", "/api/buku"))
    r5 = dispatch(svc, HttpRequest("DELETE", "/api/buku"))
    print(r1.status, r1.body)
    print(r2.status, r2.body)
    print(r3.status, r3.body)
    print(r4.status, r4.body)
    print(r5.status, r5.body)


if __name__ == "__main__":
    demo()
</code></pre>

<p>Output yang diharapkan:</p>
<pre><code>200 {'items': []}
201 {'judul': 'REST', 'penulis': 'Kindo'}
400 {'error': 'judul wajib'}
200 {'items': [{'judul': 'REST', 'penulis': 'Kindo'}]}
405 {'error': 'method tidak diizinkan'}
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
      <td>Semua “sukses” 200</td>
      <td>Tidak membedakan create/validasi</td>
      <td>201 / 400 seperti stub <a href="/artikel/oop-flask-fastapi-class-api">Flask/FastAPI (#52)</a></td>
    </tr>
    <tr>
      <td>GET menambah data</td>
      <td>Side-effect di baca</td>
      <td>Mutasi hanya di POST (atau method tulis lain)</td>
    </tr>
    <tr>
      <td>404 tidak pernah muncul</td>
      <td>Path salah tetap diproses</td>
      <td>Cek path di <code>dispatch</code></td>
    </tr>
    <tr>
      <td>Method aneh diabaikan</td>
      <td>Fallthrough ke GET</td>
      <td>Kembalikan 405 dengan jelas</td>
    </tr>
    <tr>
      <td>Langsung belajar decorator</td>
      <td>Lompat kontrak</td>
      <td>Stub dulu — Flask belakangan</td>
    </tr>
  </tbody>
</table>

<h2>Latihan singkat</h2>
<ol>
  <li>Tambah path <code>/api/buku/cari</code> (GET) yang filter judul — tetap lewat <code>dispatch</code>.</li>
  <li>Tolak POST tanpa key <code>judul</code> di body dengan 400 (beda dari judul kosong).</li>
  <li>Catat di kertas: mapping mana dari <code>dispatch</code> yang akan jadi <code>@app.get</code> / <code>@app.post</code> di Flask.</li>
</ol>

<h2>FAQ singkat</h2>
<p><strong>Apakah ini REST “murni”?</strong><br>Tidak perlu. Cukup resource + method + status yang konsisten untuk API mini.</p>
<p><strong>Kenapa belum install Flask?</strong><br>Supaya kontrak HTTP jelas sebelum sintaks framework. Pola sama dengan stub di <a href="/artikel/oop-flask-fastapi-class-api">Flask/FastAPI (#52)</a>.</p>
<p><strong>Apa bedanya 404 dan 405?</strong><br>404 = path tidak dikenal. 405 = path dikenal, method tidak diizinkan.</p>
<p><strong>Lanjut ke mana?</strong><br>Artikel berikutnya di Seri 4: Flask routing &amp; JSON — memasang <code>dispatch</code> ke pintu nyata (belum hardlink sampai artikel itu live).</p>

<h2>Kesimpulan &amp; langkah berikutnya</h2>
<p>HTTP/REST adalah bahasa bersama client dan server. Stub OOP-mu sudah berbicara bahasa itu; framework hanya penerjemah.</p>
<p>Artikel ini adalah <strong>#53 (ini)</strong> — pembuka Seri 4 Web Lanjut setelah <a href="/artikel/oop-flask-fastapi-class-api">pintu OOP web (#52)</a>.</p>

<blockquote>
  <p><strong>Seri 4 progress:</strong> langkah <strong>#53 (ini)</strong> · 0/8 menuju Capstone API · prasyarat <a href="/artikel/oop-flask-fastapi-class-api">Flask/FastAPI (#52)</a> LIVE · fondasi Tier 2 tetap. Berikutnya: Flask routing &amp; JSON (tanpa hardlink sampai artikel itu live).</p>
</blockquote>
HTML;
    }
}
