<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article52Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $progCat = Category::where('slug', 'programming')->first();

        if (! $admin || ! $progCat) {
            throw new \RuntimeException('User atau kategori programming tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'oop-flask-fastapi-class-api';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        foreach ([
            'oop' => 'oop',
            'python' => 'python',
            'flask' => 'flask',
            'fastapi' => 'fastapi',
            'api' => 'api',
            'composition' => 'composition',
        ] as $tagSlug => $tagName) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagName]);
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $progCat->id,
                'title'           => 'Dari OOP ke Web: Class di Flask / FastAPI',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'OOP Flask FastAPI — Class Service & API Perpustakaan',
                'seo_description' => 'Bawa OOP Seri 3 ke web: class service, handler tipis, stub HTTP runnable — jembatan ke Flask/FastAPI tanpa spaghetti view.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['python', 'oop', 'flask', 'fastapi', 'api', 'composition'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel ke-52 berhasil dipublish: '.$article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — class yang sama, pintu HTTP</h2>
<p>Di Seri 3 kamu sudah punya mental model objek. Tier 2 membawa OOP ke dua arah: perangkat di <a href="/artikel/oop-micropython-esp32-class-sensor">MicroPython (#51)</a>, dan sekarang <strong>web API</strong>. Flask/FastAPI hanya “pintu”; logika bisnis tetap di class — pola yang sama dengan service di <a href="/artikel/capstone-sistem-perpustakaan-mini-oop-python">Capstone (#49)</a> dan factory ringan di <a href="/artikel/design-pattern-factory-strategy-python">Factory (#50)</a>.</p>
<p>Artikel ini sengaja runnable di PC tanpa wajib install framework dulu: stub HTTP + class service. Di bagian porting, kamu lihat bagaimana stub itu dipasang ke Flask/FastAPI. Tujuannya bukan tutorial framework lengkap, melainkan menjaga batas OOP tetap jelas saat request masuk.</p>
<p>Kalau Capstone melatih CLI dan MicroPython melatih node, di sini kamu melatih <em>adapter</em>: HTTP di tepi, domain di tengah. Ganti Flask dengan FastAPI (atau sebaliknya) seharusnya tidak merombak <code>PerpustakaanService</code>.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> <a href="/artikel/oop-micropython-esp32-class-sensor">MicroPython OOP (#51)</a> atau minimal <a href="/artikel/composition-vs-inheritance-python">Composition (#47)</a> + <a href="/artikel/capstone-sistem-perpustakaan-mini-oop-python">Capstone (#49)</a> · <a href="/artikel/mengenal-oop-cara-berpikir-dengan-objek-python">Mengenal OOP (#40)</a>. Opsional: <a href="/artikel/design-pattern-factory-strategy-python">Factory (#50)</a>.</p>
</blockquote>

<h2>Kenapa class di web, bukan view panjang?</h2>
<table>
  <thead>
    <tr>
      <th>Pendekatan</th>
      <th>Gejala</th>
      <th>Akibat</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Semua di fungsi route</td>
      <td>Validasi + DB + JSON campur</td>
      <td>Sulit diuji; route jadi “tuhan”</td>
    </tr>
    <tr>
      <td>Class service</td>
      <td>Route tipis; service punya koleksi/aturan</td>
      <td>Bisa <code>demo()</code> tanpa server; ganti framework lebih aman</td>
    </tr>
  </tbody>
</table>
<p>Ini composition: aplikasi <em>punya</em> service, service <em>punya</em> item — bukan mewarisi Flask. Inheritance framework mengikat domain ke satu library; composition menjaga service bisa diuji dan diport.</p>

<pre><code class="language-python"># Anti-pola (jangan): class App(Flask): ...
# Pola OOP: app punya service

class PerpustakaanService:
    def __init__(self):
        self._items = []

    @property
    def jumlah(self):
        return len(self._items)


class AppShell:
    """Pengganti 'app' tipis — composition, bukan inheritance framework."""

    def __init__(self, service):
        self.service = service


app = AppShell(PerpustakaanService())
print("service terpasang, jumlah=", app.service.jumlah)
</code></pre>

<p>Output:</p>
<pre><code>service terpasang, jumlah= 0
</code></pre>

<figure role="img" aria-label="Diagram Request ke handler tipis lalu PerpustakaanService" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 260" style="display:block;max-width:720px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="oop52Arrow" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/></marker>
  </defs>
  <rect x="0" y="0" width="720" height="260" fill="#F5F5F0" rx="6"/>
  <text x="360" y="28" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">HTTP tipis + service OOP</text>
  <rect x="40" y="70" width="160" height="56" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="120" y="104" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">Request</text>
  <rect x="280" y="70" width="160" height="56" rx="6" fill="#1a1a1a" stroke="#000" stroke-width="2.5"/>
  <text x="360" y="104" text-anchor="middle" fill="#fff" font-size="14" font-weight="700">Handler</text>
  <rect x="520" y="70" width="160" height="56" rx="6" fill="#FFF3E8" stroke="#000" stroke-width="2.5"/>
  <text x="600" y="104" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">Service</text>
  <line x1="200" y1="98" x2="280" y2="98" stroke="#2979FF" stroke-width="2.5" marker-end="url(#oop52Arrow)"/>
  <line x1="440" y1="98" x2="520" y2="98" stroke="#2979FF" stroke-width="2.5" marker-end="url(#oop52Arrow)"/>
  <rect x="520" y="170" width="160" height="48" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="600" y="200" text-anchor="middle" fill="#1a1a1a" font-size="14" font-weight="700">Item</text>
  <line x1="600" y1="126" x2="600" y2="170" stroke="#2979FF" stroke-width="2.5" marker-end="url(#oop52Arrow)"/>
  <text x="360" y="150" text-anchor="middle" fill="#2D3748" font-size="12">punya</text>
</svg>
<figcaption style="margin-top:.75rem;color:#2D3748;font-size:.95rem">Handler hanya menerjemahkan HTTP; aturan domain tinggal di <code>PerpustakaanService</code>.</figcaption>
</figure>

<h2>Item + service — domain yang sudah dikenal</h2>
<p>Tetap domain perpustakaan agar jembatan ke Capstone jelas (semangat <a href="/artikel/encapsulation-property-python-oop">Encapsulation (#43)</a>):</p>

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


svc = PerpustakaanService()
svc.tambah("OOP Python", "Kindo")
print(svc.daftar())
</code></pre>

<p>Output:</p>
<pre><code>[{'judul': 'OOP Python', 'penulis': 'Kindo'}]
</code></pre>

<h2>Stub HTTP — uji tanpa Flask terpasang</h2>
<p>Framework boleh diganti; kontrak response yang kita kendalikan:</p>

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
print(handle_create(svc, "Flask Ringkas", "Dewi").status)
print(handle_list(svc).body)
print(handle_create(svc, "  ", "X").status)
</code></pre>

<p>Output:</p>
<pre><code>201
{'items': [{'judul': 'Flask Ringkas', 'penulis': 'Dewi'}]}
400
</code></pre>
<p>Satu antarmuka handler, banyak “kerangka” di belakang — selaras <a href="/artikel/polymorphism-python-oop">Polymorphism (#45)</a>.</p>

<h2>Factory opsional — jenis item tanpa hutan if di route</h2>
<p>Kalau tipe mulai bertambah, pakai factory ringan (pola <a href="/artikel/design-pattern-factory-strategy-python">Factory (#50)</a>), tetap di luar Flask. <code>EbookItem</code> memakai pewarisan sederhana (lihat <a href="/artikel/inheritance-pewarisan-class-python">Inheritance (#44)</a>), tapi keputusan “buku atau ebook” tetap di fungsi factory — bukan di decorator route:</p>

<pre><code class="language-python">class BukuItem:
    def __init__(self, judul, penulis):
        self.judul = judul
        self.penulis = penulis

    def as_dict(self):
        return {"jenis": "buku", "judul": self.judul, "penulis": self.penulis}


class EbookItem(BukuItem):
    def as_dict(self):
        d = super().as_dict()
        d["jenis"] = "ebook"
        return d


def buat_item(jenis, judul, penulis):
    if jenis == "buku":
        return BukuItem(judul, penulis)
    if jenis == "ebook":
        return EbookItem(judul, penulis)
    raise ValueError(f"jenis tidak dikenal: {jenis}")


print(buat_item("ebook", "FastAPI Ringkas", "Sari").as_dict())
try:
    buat_item("majalah", "X", "Y")
except ValueError as exc:
    print("error:", exc)
</code></pre>

<p>Output:</p>
<pre><code>{'jenis': 'ebook', 'judul': 'FastAPI Ringkas', 'penulis': 'Sari'}
error: jenis tidak dikenal: majalah
</code></pre>

<h2>Porting singkat ke Flask / FastAPI</h2>
<p>Setelah <code>demo()</code> hijau, pasang service yang sama. Blok di bawah <strong>sketsa</strong> (bukan wajib dijalankan di audit PC):</p>
<pre><code class="language-text"># Flask (sketsa)
# from flask import Flask, request, jsonify
# app = Flask(__name__)
# svc = PerpustakaanService()
#
# @app.get("/api/buku")
# def list_buku():
#     r = handle_list(svc)
#     return jsonify(r.body), r.status
#
# @app.post("/api/buku")
# def create_buku():
#     data = request.get_json(force=True) or {}
#     r = handle_create(svc, data.get("judul", ""), data.get("penulis", ""))
#     return jsonify(r.body), r.status

# FastAPI (sketsa)
# from fastapi import FastAPI
# from fastapi.responses import JSONResponse
# app = FastAPI()
# svc = PerpustakaanService()
#
# @app.get("/api/buku")
# def list_buku():
#     r = handle_list(svc)
#     return JSONResponse(r.body, status_code=r.status)
#
# # Sketsa: query param biar pendek; body JSON nyata = model Pydantic belakangan
# @app.post("/api/buku")
# def create_buku(judul: str = "", penulis: str = ""):
#     r = handle_create(svc, judul, penulis)
#     return JSONResponse(r.body, status_code=r.status)
</code></pre>
<p>Perhatikan: <code>PerpustakaanService</code> tidak mengimpor Flask/FastAPI. Itu titik OOP-nya. Sketsa di atas sengaja tipis — body JSON nyata bisa pakai model Pydantic belakangan, setelah stub hijau.</p>

<h2>Pola Dasar — OOP di pintu web</h2>
<figure style="margin:1.5rem 0;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1.25rem;color:#1a1a1a" aria-label="Lima langkah OOP Flask FastAPI">
<ol style="list-style:none;padding:0;margin:0;color:#1a1a1a">
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Model domain dulu</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem"><code>BukuItem</code> + <code>as_dict()</code> — bukan dict longgar di route.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Service dengan composition</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem"><code>PerpustakaanService</code> punya koleksi — pola <a href="/artikel/composition-vs-inheritance-python">Composition (#47)</a>.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Handler tipis</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Terjemahkan HTTP &lt;-&gt; service; jangan taruh aturan di decorator saja.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Uji dengan stub</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem"><code>HttpResponse</code> + <code>demo()</code> — tanpa server, tanpa input menggantung.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">5</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Pasang framework</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Flask atau FastAPI hanya adapter — service tetap sama.</span>
    </div>
  </li>
</ol>
</figure>

<h2>Kode lengkap — <code>perpustakaan_api_oop.py</code></h2>
<p>Simpan dan jalankan: <code>python perpustakaan_api_oop.py</code>.</p>

<pre><code class="language-python">"""OOP ringan untuk API perpustakaan — stub HTTP (Tier 2 #52).
Pasang ke Flask/FastAPI lewat handler yang sama (lihat sketsa di artikel).
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


def handle_list(service: PerpustakaanService) -&gt; HttpResponse:
    return HttpResponse(200, {"items": service.daftar()})


def handle_create(service: PerpustakaanService, judul: str, penulis: str) -&gt; HttpResponse:
    try:
        item = service.tambah(judul, penulis)
    except ValueError as exc:
        return HttpResponse(400, {"error": str(exc)})
    return HttpResponse(201, item.as_dict())


def demo() -&gt; None:
    svc = PerpustakaanService()
    ok = handle_create(svc, "OOP Python", "Kindo")
    bad = handle_create(svc, "   ", "X")
    listed = handle_list(svc)
    print(ok.status, ok.body)
    print(bad.status, bad.body)
    print(listed.status, listed.body)


if __name__ == "__main__":
    demo()
</code></pre>

<p>Output yang diharapkan:</p>
<pre><code>201 {'judul': 'OOP Python', 'penulis': 'Kindo'}
400 {'error': 'judul wajib'}
200 {'items': [{'judul': 'OOP Python', 'penulis': 'Kindo'}]}
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
      <td>Route 200 baris</td>
      <td>Logika domain di view</td>
      <td>Pindahkan ke <code>PerpustakaanService</code></td>
    </tr>
    <tr>
      <td>Sulit unit-test</td>
      <td>Import Flask di class domain</td>
      <td>Service murni; framework hanya di adapter</td>
    </tr>
    <tr>
      <td>Validasi hilang</td>
      <td>Percaya JSON mentah</td>
      <td><code>ValueError</code> di service -&gt; status 400 di handler</td>
    </tr>
    <tr>
      <td>Warisi <code>Flask</code> / <code>FastAPI</code></td>
      <td>Salah inheritance</td>
      <td>Composition: app <em>punya</em> service</td>
    </tr>
    <tr>
      <td>Over-engineer Pydantic dulu</td>
      <td>Terburu schema penuh</td>
      <td>Class biasa + stub dulu; schema belakangan</td>
    </tr>
    <tr>
      <td>Status selalu 200 di FastAPI</td>
      <td>Return dict mentah tanpa <code>status_code</code></td>
      <td>Pakai <code>JSONResponse(..., status_code=r.status)</code> seperti sketsa</td>
    </tr>
  </tbody>
</table>

<h2>Latihan singkat</h2>
<ol>
  <li>Tambah <code>handle_cari(service, kata)</code> yang filter judul (case-insensitive).</li>
  <li>Ganti pembuatan item lewat fungsi factory sederhana (pola <a href="/artikel/design-pattern-factory-strategy-python">Factory (#50)</a>) untuk tipe <code>ebook</code>/<code>buku</code>.</li>
  <li>Sketsa: satu endpoint yang memanggil service Node dari <a href="/artikel/oop-micropython-esp32-class-sensor">MicroPython (#51)</a> (mis. status suhu) — tanpa wajib wire MQTT.</li>
</ol>

<h2>FAQ singkat</h2>
<p><strong>Harus Flask atau FastAPI?</strong><br>Keduanya OK. FastAPI nyaman untuk type hint &amp; docs; Flask ringan untuk belajar adapter. OOP-nya sama: service di tengah.</p>
<p><strong>Kenapa tidak <code>class App(Flask)</code>?</strong><br>Karena domain ikut terikat ke framework. Composition (<code>AppShell</code> / app punya service) membuat unit-test dan ganti pintu HTTP jauh lebih murah — semangat yang sama dengan <a href="/artikel/composition-vs-inheritance-python">Composition (#47)</a>.</p>
<p><strong>Apakah ini pengganti Capstone?</strong><br>Bukan. Capstone CLI tetap fondasi. Artikel ini hanya membuka pintu HTTP dengan class yang sama.</p>
<p><strong>Apakah perlu instal package untuk latihan?</strong><br>Tidak untuk stub. Install Flask/FastAPI saat kamu siap menjalankan sketsa porting.</p>
<p><strong>Type hint di kode lengkap wajib?</strong><br>Tidak. Type hint membantu FastAPI/IDE; di stub PC cukup untuk kejelasan. Jangan biarkan anotasi menunda <code>demo()</code> hijau.</p>
<p><strong>Dari jalur IoT, mulai di mana?</strong><br>Kalau kamu baru dari perangkat, selesaikan dulu <a href="/artikel/oop-micropython-esp32-class-sensor">MicroPython (#51)</a> (pola service punya sensor). Pola yang sama dipakai di <a href="/artikel/smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt">capstone greenhouse (#39)</a> — di sini pintu HTTP-nya.</p>
<p><strong>Lanjut ke mana?</strong><br>Lanjut Seri 4: <a href="/artikel/http-rest-kontrak-stub-flask-oop">HTTP &amp; REST — Kontrak di Balik Stub (#53)</a> — method, status, resource, mini-router tanpa wajib install Flask.</p>

<h2>Kesimpulan &amp; langkah berikutnya</h2>
<p>Web tidak membatalkan OOP — ia meminta batas yang lebih jelas: domain di class, HTTP di adapter. Stub dulu, framework belakangan. Kalau route sudah “tuhan”, pecah lagi: item, service, handler, baru pintu Flask/FastAPI.</p>
<p>Artikel ini adalah <strong>#52 (ini)</strong> — pintu web setelah <a href="/artikel/oop-micropython-esp32-class-sensor">MicroPython (#51)</a> dan <a href="/artikel/design-pattern-factory-strategy-python">Factory (#50)</a>. Lanjut Seri 4: <a href="/artikel/http-rest-kontrak-stub-flask-oop">HTTP &amp; REST (#53)</a>.</p>

<blockquote>
  <p><strong>Tier 2 progress:</strong> langkah <strong>#52 (ini)</strong> · <a href="/artikel/oop-micropython-esp32-class-sensor">MicroPython (#51)</a> LIVE · <a href="/artikel/design-pattern-factory-strategy-python">Factory (#50)</a> LIVE · Seri 3 tetap 10/10 · Seri 4: <a href="/artikel/http-rest-kontrak-stub-flask-oop">HTTP &amp; REST (#53)</a>. Prasyarat: <a href="/artikel/oop-micropython-esp32-class-sensor">MicroPython (#51)</a> · <a href="/artikel/capstone-sistem-perpustakaan-mini-oop-python">Capstone (#49)</a> · <a href="/artikel/composition-vs-inheritance-python">Composition (#47)</a> · <a href="/artikel/design-pattern-factory-strategy-python">Factory (#50)</a> · <a href="/artikel/mengenal-oop-cara-berpikir-dengan-objek-python">OOP (#40)</a>.</p>
</blockquote>
HTML;
    }
}
