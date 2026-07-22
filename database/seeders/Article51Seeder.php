<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article51Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $progCat = Category::where('slug', 'programming')->first();

        if (! $admin || ! $progCat) {
            throw new \RuntimeException('User atau kategori programming tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'oop-micropython-esp32-class-sensor';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        foreach ([
            'oop' => 'oop',
            'python' => 'python',
            'micropython' => 'micropython',
            'esp32' => 'esp32',
            'composition' => 'composition',
        ] as $tagSlug => $tagName) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagName]);
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'         => $admin->id,
                'category_id'     => $progCat->id,
                'title'           => 'OOP di MicroPython / ESP32: Class Sensor & Node IoT',
                'body'            => $this->body(),
                'status'          => 'published',
                'is_featured'     => false,
                'seo_title'       => 'OOP MicroPython ESP32 — Class Sensor & Composition Node',
                'seo_description' => 'Bawa OOP Python ke MicroPython di ESP32: class Sensor/Led, composition Node, stub PC yang runnable — jembatan Seri 3 ke Seri IoT.',
            ]
        );
        // cover_image tidak disentuh — upload manual via Filament

        if ($article->wasRecentlyCreated || ! $article->published_at) {
            $article->published_at = now();
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['python', 'oop', 'micropython', 'esp32', 'composition'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel ke-51 berhasil dipublish: '.$article->title);
    }

    private function body(): string
    {
        return <<<'HTML'
<h2>Pendahuluan — OOP yang sama, RAM yang beda</h2>
<p>Di Seri 3 kamu sudah nyaman dengan class, composition, dan pattern ringan di <a href="/artikel/design-pattern-factory-strategy-python">Factory &amp; Strategy (#50)</a> serta <a href="/artikel/capstone-sistem-perpustakaan-mini-oop-python">Capstone (#49)</a>. Artikel ini jembatan ke perangkat: <strong>MicroPython di ESP32</strong> — OOP-nya tetap Python, tapi memori dan modul hardware memaksa desain lebih hemat.</p>
<p>Pola yang sama dengan firmware yang membesar di <a href="/artikel/smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt">capstone smart greenhouse (#39)</a>: layanan <em>punya</em> sensor dan aktuator, bukan satu file spaghetti GPIO.</p>

<blockquote>
  <p><strong>Prasyarat:</strong> <a href="/artikel/design-pattern-factory-strategy-python">Factory &amp; Strategy (#50)</a> atau minimal <a href="/artikel/composition-vs-inheritance-python">Composition (#47)</a> + <a href="/artikel/mengenal-oop-cara-berpikir-dengan-objek-python">Mengenal OOP (#40)</a>. Familiar ESP32: <a href="/artikel/mengenal-esp32-mikrokontroler-wifi-bluetooth-iot">Mengenal ESP32 (#1)</a> · <a href="/artikel/smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt">Greenhouse (#39)</a>. Opsional: Python di ekosistem Kindo lewat <a href="/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32">Subscriber MQTT (#18)</a>.</p>
</blockquote>

<h2>CPython vs MicroPython — apa yang berubah?</h2>
<table>
  <thead>
    <tr>
      <th>Aspek</th>
      <th>CPython (Seri 3)</th>
      <th>MicroPython (ESP32)</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>RAM / flash</td>
      <td>Longgar</td>
      <td>Ketat — hindari object berlebihan</td>
    </tr>
    <tr>
      <td><code>@dataclass</code></td>
      <td>Stdlib lengkap</td>
      <td>Sering tidak ada / terbatas — class biasa lebih aman</td>
    </tr>
    <tr>
      <td>Hardware</td>
      <td>Tidak ada</td>
      <td><code>machine.Pin</code>, ADC, I2C, WiFi</td>
    </tr>
    <tr>
      <td>OOP inti</td>
      <td>class, inheritance, composition</td>
      <td><strong>Sama</strong> — mental model Seri 3 tetap berlaku</td>
    </tr>
  </tbody>
</table>
<p>Jangan buang composition hanya karena “firmware”. Justru di node IoT, <a href="/artikel/composition-vs-inheritance-python">Composition (#47)</a> menjaga GPIO tidak tercecer di mana-mana.</p>

<figure role="img" aria-label="Diagram Node ESP32 yang punya Sensor dan Led lewat composition" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 260" style="display:block;max-width:720px;width:100%;height:auto;font-family:Inter,system-ui,sans-serif">
  <defs>
    <marker id="oop51Arrow" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 Z" fill="#2979FF"/></marker>
  </defs>
  <rect x="0" y="0" width="720" height="260" fill="#F5F5F0" rx="6"/>
  <text x="360" y="28" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Node IoT — composition, bukan spaghetti GPIO</text>
  <rect x="260" y="50" width="200" height="56" rx="6" fill="#1a1a1a" stroke="#000" stroke-width="2.5"/>
  <text x="360" y="84" text-anchor="middle" fill="#fff" font-size="16" font-weight="700">Node</text>
  <rect x="60" y="160" width="180" height="56" rx="6" fill="#E8F4FF" stroke="#000" stroke-width="2.5"/>
  <text x="150" y="194" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Sensor</text>
  <rect x="480" y="160" width="180" height="56" rx="6" fill="#FFF3E8" stroke="#000" stroke-width="2.5"/>
  <text x="570" y="194" text-anchor="middle" fill="#1a1a1a" font-size="15" font-weight="700">Led</text>
  <line x1="300" y1="106" x2="180" y2="160" stroke="#2979FF" stroke-width="2.5" marker-end="url(#oop51Arrow)"/>
  <line x1="420" y1="106" x2="540" y2="160" stroke="#2979FF" stroke-width="2.5" marker-end="url(#oop51Arrow)"/>
  <text x="200" y="140" fill="#2D3748" font-size="12">punya</text>
  <text x="500" y="140" fill="#2D3748" font-size="12">punya</text>
</svg>
<figcaption style="margin-top:.75rem;color:#2D3748;font-size:.95rem"><code>Node</code> punya <code>Sensor</code> dan <code>Led</code> — pola yang sama dengan “service punya koleksi” di Seri 3.</figcaption>
</figure>

<h2>Stub PC — belajar tanpa board di tangan</h2>
<p>Contoh di bawah memakai stub <code>FakePin</code> agar bisa dijalankan di PC (CPython) untuk latihan. Di ESP32 ganti menjadi <code>from machine import Pin</code>. Ini sengaja: audit &amp; belajar tidak bergantung pada hardware.</p>

<pre><code class="language-python">class FakePin:
    """Pengganti machine.Pin untuk latihan di PC."""

    OUT = 1
    IN = 0

    def __init__(self, nomor, mode=OUT):
        self.nomor = nomor
        self.mode = mode
        self._nilai = 0

    def value(self, v=None):
        if v is None:
            return self._nilai
        self._nilai = 1 if v else 0
        return self._nilai


pin = FakePin(2, FakePin.OUT)
pin.value(1)
print("pin", pin.nomor, "=&gt;", pin.value())
</code></pre>

<p>Output:</p>
<pre><code>pin 2 =&gt; 1
</code></pre>

<h2>Class Led — encapsulate GPIO</h2>
<p>Jangan sebar <code>pin.value(1)</code> di loop utama. Bungkus di class (semangat <a href="/artikel/encapsulation-property-python-oop">Encapsulation (#43)</a>):</p>

<pre><code class="language-python">class FakePin:
    OUT = 1

    def __init__(self, nomor, mode=OUT):
        self.nomor = nomor
        self._nilai = 0

    def value(self, v=None):
        if v is None:
            return self._nilai
        self._nilai = 1 if v else 0
        return self._nilai


class Led:
    def __init__(self, pin):
        self._pin = pin

    def nyala(self):
        self._pin.value(1)

    def mati(self):
        self._pin.value(0)

    def status(self):
        return "ON" if self._pin.value() else "OFF"


led = Led(FakePin(2))
led.nyala()
print(led.status())
led.mati()
print(led.status())
</code></pre>

<p>Output:</p>
<pre><code>ON
OFF
</code></pre>

<h2>Class Sensor — baca + label</h2>
<p>Sensor di MicroPython sering ADC atau DHT. Untuk latihan PC kita simulasi nilai:</p>

<pre><code class="language-python">class SensorSuhu:
    def __init__(self, nama, baca_fn):
        self.nama = nama
        self._baca_fn = baca_fn

    def baca(self):
        return float(self._baca_fn())

    def label(self, nilai=None):
        if nilai is None:
            nilai = self.baca()
        return f"{self.nama}: {nilai:.1f} C"


def simulasi_dht():
    return 27.5


s = SensorSuhu("DHT22", simulasi_dht)
print(s.label())
</code></pre>

<p>Output:</p>
<pre><code>DHT22: 27.5 C
</code></pre>
<p>Parameter opsional <code>nilai</code> disiapkan sejak dini: nanti di <code>Node.tick()</code> kita teruskan hasil satu bacaan agar sensor tidak dibaca dua kali. Di board nyata, <code>baca_fn</code> bisa memanggil driver DHT — pola callback/composition ini selaras dengan “satu antarmuka, banyak bentuk” di <a href="/artikel/polymorphism-python-oop">Polymorphism (#45)</a>.</p>

<h2>Node — composition di firmware</h2>
<p><code>Node</code> tidak mewarisi <code>Led</code>; ia <em>punya</em> sensor dan led:</p>

<pre><code class="language-python">class FakePin:
    OUT = 1

    def __init__(self, nomor, mode=OUT):
        self.nomor = nomor
        self.mode = mode
        self._nilai = 0

    def value(self, v=None):
        if v is None:
            return self._nilai
        self._nilai = 1 if v else 0
        return self._nilai


class Led:
    def __init__(self, pin):
        self._pin = pin

    def nyala(self):
        self._pin.value(1)

    def mati(self):
        self._pin.value(0)

    def status(self):
        return "ON" if self._pin.value() else "OFF"


class SensorSuhu:
    def __init__(self, nama, baca_fn):
        self.nama = nama
        self._baca_fn = baca_fn

    def baca(self):
        return float(self._baca_fn())

    def label(self, nilai=None):
        # Jangan panggil baca() lagi jika nilai sudah ada —
        # penting saat baca_fn mahal / side-effect (pop, I2C).
        if nilai is None:
            nilai = self.baca()
        return f"{self.nama}: {nilai:.1f} C"


class Node:
    def __init__(self, nama, sensor, led, ambang=30.0):
        self.nama = nama
        self.sensor = sensor
        self.led = led
        self.ambang = ambang

    def tick(self):
        suhu = self.sensor.baca()
        if suhu &gt;= self.ambang:
            self.led.nyala()
        else:
            self.led.mati()
        return f"{self.nama} | {self.sensor.label(suhu)} | LED {self.led.status()}"


def baca():
    return 31.2


node = Node("Kebun-A", SensorSuhu("DHT22", baca), Led(FakePin(2)), ambang=30.0)
print(node.tick())
</code></pre>

<p>Output:</p>
<pre><code>Kebun-A | DHT22: 31.2 C | LED ON
</code></pre>
<p>Satu <code>tick()</code> = satu <code>baca()</code>. Label memakai nilai yang sudah dibaca, bukan memanggil sensor lagi.</p>

<h2>Porting singkat ke ESP32</h2>
<p>Setelah <code>demo()</code> hijau di PC, flash ke board. Ganti stub dengan modul hardware — antarmuka class <code>Led</code>/<code>Node</code> tetap sama (semangat <a href="/artikel/encapsulation-property-python-oop">Encapsulation (#43)</a>):</p>
<pre><code class="language-text"># MicroPython di ESP32
from machine import Pin

led = Led(Pin(2, Pin.OUT))
# SensorSuhu: isi baca_fn dengan driver DHT/ADC kamu
# node = Node("Kebun-A", sensor, led, ambang=30.0)
# while True:
#     print(node.tick())
</code></pre>
<p>Jangan biarkan nomor GPIO tersebar di loop utama. Kalau <code>ImportError: machine</code> muncul di desktop, itu tanda kamu menjalankan kode board di CPython — kembali ke <code>FakePin</code> untuk latihan.</p>

<h2>Pola Dasar — OOP di node MicroPython</h2>
<figure style="margin:1.5rem 0;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1.25rem;color:#1a1a1a" aria-label="Lima langkah OOP MicroPython ESP32">
<ol style="list-style:none;padding:0;margin:0;color:#1a1a1a">
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">1</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Bungkus pin di class kecil</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem"><code>Led</code>/<code>Sensor</code> — jangan biarkan nomor GPIO tersebar.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">2</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Susun Node dengan composition</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem"><code>Node</code> punya sensor + aktuator — pola <a href="/artikel/composition-vs-inheritance-python">Composition (#47)</a>.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">3</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Hemat fitur bahasa</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">Class biasa dulu; <a href="/artikel/special-methods-dataclass-python">dataclass (#48)</a> / <a href="/artikel/abstraction-abc-python-oop">ABC (#46)</a> berat boleh ditunda di MCU kecil.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;border-bottom:1px dashed #A0AEC0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">4</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Uji di PC dengan stub</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem"><code>FakePin</code> / fungsi simulasi — ganti ke <code>machine</code> saat flash.</span>
    </div>
  </li>
  <li style="display:flex;gap:1rem;padding:.9rem 0;color:#1a1a1a">
    <span style="flex-shrink:0;width:2rem;height:2rem;border-radius:9999px;background:#2979FF;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">5</span>
    <div style="color:#1a1a1a">
      <strong style="color:#1a1a1a">Hubungkan ke Seri IoT</strong>
      <span style="display:block;color:#2D3748;margin-top:.25rem">MQTT/dashboard: lihat <a href="/artikel/smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt">Greenhouse (#39)</a> dan <a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">Deep Sleep DHT22 (#11)</a>.</span>
    </div>
  </li>
</ol>
</figure>

<h2>Kode lengkap — <code>node_micropython_oop.py</code></h2>
<p>Simpan dan jalankan di PC: <code>python node_micropython_oop.py</code>. Di ESP32, ganti <code>FakePin</code> dengan <code>machine.Pin</code> (lihat komentar).</p>

<pre><code class="language-python">"""OOP ringan untuk node IoT — stub PC (Tier 2 #51).
Di ESP32: from machine import Pin  # lalu Pin(2, Pin.OUT)
"""

from __future__ import annotations


class FakePin:
    OUT = 1
    IN = 0

    def __init__(self, nomor: int, mode: int = OUT) -&gt; None:
        self.nomor = nomor
        self.mode = mode
        self._nilai = 0

    def value(self, v=None):
        if v is None:
            return self._nilai
        self._nilai = 1 if v else 0
        return self._nilai


class Led:
    def __init__(self, pin: FakePin) -&gt; None:
        self._pin = pin

    def nyala(self) -&gt; None:
        self._pin.value(1)

    def mati(self) -&gt; None:
        self._pin.value(0)

    def status(self) -&gt; str:
        return "ON" if self._pin.value() else "OFF"


class SensorSuhu:
    def __init__(self, nama: str, baca_fn) -&gt; None:
        self.nama = nama
        self._baca_fn = baca_fn

    def baca(self) -&gt; float:
        return float(self._baca_fn())

    def label(self, nilai: float | None = None) -&gt; str:
        if nilai is None:
            nilai = self.baca()
        return f"{self.nama}: {nilai:.1f} C"


class Node:
    def __init__(self, nama: str, sensor: SensorSuhu, led: Led, ambang: float = 30.0) -&gt; None:
        self.nama = nama
        self.sensor = sensor
        self.led = led
        self.ambang = ambang

    def tick(self) -&gt; str:
        suhu = self.sensor.baca()
        if suhu &gt;= self.ambang:
            self.led.nyala()
        else:
            self.led.mati()
        return f"{self.nama} | {self.sensor.label(suhu)} | LED {self.led.status()}"


def demo() -&gt; None:
    nilai = [28.0, 31.5, 29.0]

    def baca():
        return nilai.pop(0) if nilai else 29.0

    node = Node("Kebun-A", SensorSuhu("DHT22", baca), Led(FakePin(2)), ambang=30.0)
    for _ in range(3):
        print(node.tick())


if __name__ == "__main__":
    demo()
</code></pre>

<p>Output yang diharapkan:</p>
<pre><code>Kebun-A | DHT22: 28.0 C | LED OFF
Kebun-A | DHT22: 31.5 C | LED ON
Kebun-A | DHT22: 29.0 C | LED OFF
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
      <td>MemoryError di MCU</td>
      <td>Terlalu banyak object / string besar</td>
      <td>Kurangi alokasi; class tipis; hindari import berat</td>
    </tr>
    <tr>
      <td>GPIO “aneh” di banyak file</td>
      <td>Nomor pin hardcode tersebar</td>
      <td>Bungkus di <code>Led</code>/<code>Sensor</code>; Node yang orkestrasi</td>
    </tr>
    <tr>
      <td><code>ImportError: machine</code> di PC</td>
      <td>Menjalankan kode ESP32 di CPython</td>
      <td>Pakai stub <code>FakePin</code> saat belajar di desktop</td>
    </tr>
    <tr>
      <td>Memaksa dataclass/ABC penuh</td>
      <td>Port MicroPython terbatas</td>
      <td>Class biasa + composition cukup untuk node kecil</td>
    </tr>
    <tr>
      <td>Warisi <code>Node(Led)</code></td>
      <td>Salah inheritance</td>
      <td>Composition: Node <em>punya</em> Led</td>
    </tr>
    <tr>
      <td>Nilai sensor “loncat” / LED salah</td>
      <td><code>label()</code> memanggil <code>baca()</code> lagi di dalam <code>tick()</code></td>
      <td>Satu bacaan per tick — teruskan nilai ke <code>label(suhu)</code></td>
    </tr>
  </tbody>
</table>

<h2>Latihan singkat</h2>
<ol>
  <li>Tambah class <code>Relay</code> mirip <code>Led</code> (semangat <a href="/artikel/encapsulation-property-python-oop">Encapsulation (#43)</a>), lalu masukkan ke <code>Node</code> (composition).</li>
  <li>Ubah <code>demo()</code> agar LED nyala hanya jika suhu &gt; ambang <em>dan</em> dua tick berturut-turut panas.</li>
  <li>Sketsa: bagaimana <code>Node.tick()</code> bisa menerbitkan JSON ke MQTT (lihat pola topik di <a href="/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai">Deep Sleep (#11)</a>) — tanpa wajib implementasi penuh.</li>
</ol>

<h2>FAQ singkat</h2>
<p><strong>Apakah harus MicroPython, bukan Arduino C++?</strong><br>Tidak wajib. Artikel ini untuk pembaca Seri 3 yang ingin OOP Python di board. Firmware C++ tetap valid di Seri ESP32.</p>
<p><strong>Apakah <a href="/artikel/design-pattern-factory-strategy-python">Factory/Strategy (#50)</a> dipakai di MCU?</strong><br>Boleh jika cabang jenis sensor mulai liar. Untuk satu DHT + satu LED, composition sederhana lebih dulu.</p>
<p><strong>Type hint <code>float | None</code> aman di ESP32?</strong><br>Blok lengkap memakai <code>from __future__ import annotations</code> untuk latihan di PC (CPython 3.10+). Saat flash MicroPython, type hint boleh dihapus — perilaku runtime tidak bergantung padanya.</p>
<p><strong>Di mana WiFi/MQTT?</strong><br>Di luar scope singkat ini. Sambungkan ke alur <a href="/artikel/smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt">Greenhouse (#39)</a> setelah struktur class rapi.</p>
<p><strong>Lanjut ke mana?</strong><br>Lanjut Tier 2: <a href="/artikel/oop-flask-fastapi-class-api">Dari OOP ke Web — Class di Flask / FastAPI (#52)</a> — service + handler tipis sebagai pintu Web Dev.</p>

<h2>Kesimpulan &amp; langkah berikutnya</h2>
<p>OOP Seri 3 tidak hilang di MicroPython — yang berubah adalah anggaran memori dan akses hardware. Bungkus GPIO, susun Node dengan composition, uji di PC dengan stub, lalu flash ke ESP32.</p>
<p>Artikel ini adalah <strong>#51 (ini)</strong> — jembatan Tier 2 setelah <a href="/artikel/design-pattern-factory-strategy-python">Factory &amp; Strategy (#50)</a>, mengarah ke Seri IoT. Lanjut web: <a href="/artikel/oop-flask-fastapi-class-api">Flask / FastAPI (#52)</a>.</p>

<blockquote>
  <p><strong>Tier 2 progress:</strong> langkah <strong>#51 (ini)</strong> · <a href="/artikel/design-pattern-factory-strategy-python">Factory (#50)</a> LIVE · <a href="/artikel/oop-flask-fastapi-class-api">Flask/FastAPI (#52)</a> · Seri 3 tetap 10/10. Prasyarat: <a href="/artikel/design-pattern-factory-strategy-python">Factory (#50)</a> · <a href="/artikel/composition-vs-inheritance-python">Composition (#47)</a> · <a href="/artikel/capstone-sistem-perpustakaan-mini-oop-python">Capstone (#49)</a> · <a href="/artikel/mengenal-oop-cara-berpikir-dengan-objek-python">OOP (#40)</a> · <a href="/artikel/smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt">Greenhouse (#39)</a> · <a href="/artikel/mengenal-esp32-mikrokontroler-wifi-bluetooth-iot">ESP32 (#1)</a>.</p>
</blockquote>
HTML;
    }
}
