<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class Article73Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $iotCat = Category::where('slug', 'iot-smart-device')->first()
            ?? Category::where('slug', 'esp32-arduino')->first();

        if (! $admin || ! $iotCat) {
            throw new \RuntimeException('User atau kategori iot-smart-device/esp32-arduino tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-kamus-mini';

        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        foreach ([
            'fullstack-iot' => 'fullstack-iot',
            'iot' => 'iot',
            'esp32' => 'esp32',
        ] as $tagSlug => $tagName) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagName]);
        }

        $article = Article::updateOrCreate(
            ['slug' => $slug],
            [
                'user_id'            => $admin->id,
                'category_id'        => $iotCat->id,
                'title'              => 'Kamus mini IoT (istilah yang akan sering muncul)',
                'title_en'           => 'Mini IoT glossary (terms you will see often)',
                'excerpt'            => 'FS-03 / #73: Istilah IoT dijelaskan dengan analogi — sensor sampai OTA. Kuis matching 15 soal. Belum wiring.',
                'excerpt_en'         => 'FS-03 / #73: IoT terms explained with analogies — sensor to OTA. 15-item matching quiz. No wiring yet.',
                'body'               => $this->body(),
                'body_en'            => $this->bodyEn(),
                'status'             => 'draft',
                'is_featured'        => false,
                'published_at'       => null,
                'seo_title'          => 'Kamus Mini IoT untuk Awam — Full Stack IoT #73',
                'seo_title_en'       => 'Beginner Mini IoT Glossary — Full Stack IoT #73',
                'seo_description'    => 'Kamus mini Full Stack IoT: sensor, GPIO, Serial, MQTT topic, API, SQLite, OTA — dengan analogi. Modul FS-03.',
                'seo_description_en' => 'Full Stack IoT mini glossary: sensor, GPIO, Serial, MQTT topic, API, SQLite, OTA — with analogies. Module FS-03.',
            ]
        );

        if ($article->published_at !== null || $article->status !== 'draft') {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        $tagIds = Tag::whereIn('slug', ['fullstack-iot', 'iot', 'esp32'])->pluck('id');
        $article->tags()->sync($tagIds);

        $this->command?->info('✓ Artikel #73 / FS-03 tersimpan sebagai DRAFT: '.$article->title);
    }

    private function senseSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Analogi sensor sebagai indra dan aktuator sebagai otot" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 160" width="100%" height="auto" role="img" aria-label="Sensor aktuator mikrokontroler">
  <rect x="20" y="40" width="160" height="70" fill="#EBF4FF" stroke="#1a1a1a" stroke-width="2"/>
  <text x="100" y="70" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">Sensor</text>
  <text x="100" y="92" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">= indra</text>
  <text x="200" y="80" font-family="system-ui,sans-serif" font-size="20">→</text>
  <rect x="230" y="40" width="200" height="70" fill="#FFF3E0" stroke="#1a1a1a" stroke-width="2"/>
  <text x="330" y="70" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">Mikrokontroler</text>
  <text x="330" y="92" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">= otak kecil (DevKitC-1)</text>
  <text x="450" y="80" font-family="system-ui,sans-serif" font-size="20">→</text>
  <rect x="480" y="40" width="200" height="70" fill="#E8F5E9" stroke="#1a1a1a" stroke-width="2"/>
  <text x="580" y="70" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">Aktuator</text>
  <text x="580" y="92" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">= otot (lampu/relay)</text>
  <text x="360" y="145" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Indra baca dunia → otak putuskan → otot bergerak</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Analogi tubuh untuk tiga istilah inti (buatan Koding Indonesia). Detail wiring datang di modul berikutnya.</figcaption>
</figure>
SVG;
    }

    private function senseSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Analogy of sensors as senses and actuators as muscles" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 160" width="100%" height="auto" role="img" aria-label="Sensor actuator microcontroller">
  <rect x="20" y="40" width="160" height="70" fill="#EBF4FF" stroke="#1a1a1a" stroke-width="2"/>
  <text x="100" y="70" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">Sensor</text>
  <text x="100" y="92" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">= sense</text>
  <text x="200" y="80" font-family="system-ui,sans-serif" font-size="20">→</text>
  <rect x="230" y="40" width="200" height="70" fill="#FFF3E0" stroke="#1a1a1a" stroke-width="2"/>
  <text x="330" y="70" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">Microcontroller</text>
  <text x="330" y="92" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">= small brain (DevKitC-1)</text>
  <text x="450" y="80" font-family="system-ui,sans-serif" font-size="20">→</text>
  <rect x="480" y="40" width="200" height="70" fill="#E8F5E9" stroke="#1a1a1a" stroke-width="2"/>
  <text x="580" y="70" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">Actuator</text>
  <text x="580" y="92" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">= muscle (lamp/relay)</text>
  <text x="360" y="145" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" fill="#4A5568">Senses read the world → brain decides → muscles move</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Body analogy for three core terms (by Koding Indonesia). Wiring detail comes in later modules.</figcaption>
</figure>
SVG;
    }

    private function flowSvgId(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Arah data telemetry dan command antara perangkat dan sistem" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 210" width="100%" height="auto" role="img" aria-label="Perangkat broker sistem">
  <rect x="20" y="70" width="170" height="70" fill="#EBF4FF" stroke="#1a1a1a" stroke-width="2"/>
  <text x="105" y="100" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">Perangkat</text>
  <text x="105" y="122" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">board (DevKitC-1)</text>
  <rect x="275" y="70" width="170" height="70" fill="#FFF3E0" stroke="#1a1a1a" stroke-width="2"/>
  <text x="360" y="100" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">Broker</text>
  <text x="360" y="122" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">pengantar pesan</text>
  <rect x="530" y="70" width="170" height="70" fill="#E8F5E9" stroke="#1a1a1a" stroke-width="2"/>
  <text x="615" y="100" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">Sistem</text>
  <text x="615" y="122" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">server / HP</text>
  <line x1="190" y1="55" x2="530" y2="55" stroke="#2E7D32" stroke-width="2"/>
  <polygon points="530,55 520,50 520,60" fill="#2E7D32"/>
  <text x="360" y="45" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700" fill="#2E7D32">Telemetry — laporan (perangkat → sistem)</text>
  <line x1="530" y1="165" x2="190" y2="165" stroke="#C62828" stroke-width="2"/>
  <polygon points="190,165 200,160 200,170" fill="#C62828"/>
  <text x="360" y="188" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700" fill="#C62828">Command — perintah (sistem → perangkat)</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Arah data: telemetry naik dari perangkat, command turun dari sistem — broker mengantar di tengah, topic jadi alamatnya (buatan Koding Indonesia). Belum praktek di modul ini.</figcaption>
</figure>
SVG;
    }

    private function flowSvgEn(): string
    {
        return <<<'SVG'
<figure role="img" aria-label="Data direction of telemetry and command between device and system" style="margin:1.5rem 0;max-width:100%;overflow-x:auto;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1rem">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 720 210" width="100%" height="auto" role="img" aria-label="Device broker system">
  <rect x="20" y="70" width="170" height="70" fill="#EBF4FF" stroke="#1a1a1a" stroke-width="2"/>
  <text x="105" y="100" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">Device</text>
  <text x="105" y="122" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">board (DevKitC-1)</text>
  <rect x="275" y="70" width="170" height="70" fill="#FFF3E0" stroke="#1a1a1a" stroke-width="2"/>
  <text x="360" y="100" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">Broker</text>
  <text x="360" y="122" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">message messenger</text>
  <rect x="530" y="70" width="170" height="70" fill="#E8F5E9" stroke="#1a1a1a" stroke-width="2"/>
  <text x="615" y="100" text-anchor="middle" font-family="system-ui,sans-serif" font-size="14" font-weight="700">System</text>
  <text x="615" y="122" text-anchor="middle" font-family="system-ui,sans-serif" font-size="11" fill="#4A5568">server / phone</text>
  <line x1="190" y1="55" x2="530" y2="55" stroke="#2E7D32" stroke-width="2"/>
  <polygon points="530,55 520,50 520,60" fill="#2E7D32"/>
  <text x="360" y="45" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700" fill="#2E7D32">Telemetry — report (device → system)</text>
  <line x1="530" y1="165" x2="190" y2="165" stroke="#C62828" stroke-width="2"/>
  <polygon points="190,165 200,160 200,170" fill="#C62828"/>
  <text x="360" y="188" text-anchor="middle" font-family="system-ui,sans-serif" font-size="12" font-weight="700" fill="#C62828">Command — order (system → device)</text>
</svg>
<figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#4A5568;">Data direction: telemetry goes up from the device, command comes down from the system — the broker relays in the middle, the topic is the address (by Koding Indonesia). No practice in this module.</figcaption>
</figure>
SVG;
    }

    private function body(): string
    {
        $sense = $this->senseSvgId();
        $flow = $this->flowSvgId();

        return <<<HTML
<h2>Pendahuluan — kenapa perlu kamus mini?</h2>
<p>Artikel ini adalah <strong>#73 (ini)</strong> · modul <strong>FS-03</strong> di jalur <strong>Full Stack IoT Developer — Dari Nol</strong>. Di <strong>#72 (FS-02)</strong> kamu sudah punya peta jalur. Hari ini kita kenalan dengan <strong>kata-kata</strong> yang akan sering muncul — supaya nanti tidak kaget.</p>
<p><strong>Awam:</strong> kamus ini seperti daftar nama teman baru. Cukup kenal wajah dan satu kalimat “dia siapa”, bukan hafal nomor KTP-nya.</p>
<blockquote>
  <p><strong>Prasyarat:</strong> sudah lihat peta tujuh lapisan + fase ZERO di FS-02. Belum perlu board, kabel, atau software unduhan.</p>
</blockquote>

<h2>Persiapan — alat yang kamu buka hari ini</h2>
<p><strong>Alat yang dipakai di artikel ini</strong> (belum Laragon, belum Arduino IDE, belum USB board, belum terminal):</p>
<ul>
  <li><strong>Browser</strong> — membaca artikel + mengerjakan <strong>kuis interaktif</strong> di akhir (Chrome, Edge, Firefox, atau browser HP).</li>
  <li><strong>Catatan</strong> (opsional) — kertas / Notepad hanya jika kamu lebih suka versi tulis tangan.</li>
</ul>
<p><strong>Tidak ada perintah sintaks hari ini.</strong> Tidak ada baris kode untuk disalin, tidak ada <code>php artisan</code>, tidak ada sketch Arduino. Cara “menguji” = cocokkan istilah ↔ arti di kuis interaktif (target ≥ 12/15).</p>

<h2>Keluarga 1 — indra, otak, otot</h2>
{$sense}
<table>
  <thead>
    <tr>
      <th>Istilah</th>
      <th>Arti awam</th>
      <th>Contoh di Stasiun Ruang Belajar</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>Sensor</strong></td>
      <td>“Indra” yang mengukur sesuatu.</td>
      <td>Sensor suhu / cahaya di meja</td>
    </tr>
    <tr>
      <td><strong>Aktuator</strong></td>
      <td>“Otot” yang bergerak atau menyala.</td>
      <td>Lampu lewat saklar kecil (relay)</td>
    </tr>
    <tr>
      <td><strong>Mikrokontroler</strong></td>
      <td>“Otak kecil” di board.</td>
      <td>Chip di dalam <strong>ESP32-DevKitC-1</strong></td>
    </tr>
    <tr>
      <td><strong>Firmware</strong></td>
      <td>Program yang “menempel” di otak kecil itu.</td>
      <td>Program yang nanti kita upload ke board</td>
    </tr>
  </tbody>
</table>
<p><strong>Awam:</strong> sensor baca, aktuator bergerak, mikrokontroler putuskan. Firmware = “isi pikiran” otak kecil.</p>

<h2>Keluarga 2 — kaki pin &amp; program di board</h2>
<table>
  <thead>
    <tr>
      <th>Istilah</th>
      <th>Arti awam</th>
      <th>Catatan</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>GPIO</strong></td>
      <td>Lubang/kaki pin umum untuk sambung sensor/lampu.</td>
      <td>Kepanjangan: General Purpose Input/Output — “pintu masuk/keluar serba guna”</td>
    </tr>
    <tr>
      <td><strong>Sketch</strong></td>
      <td>File program di Arduino IDE (nama kebiasaan komunitas).</td>
      <td>Belum kita tulis hari ini</td>
    </tr>
    <tr>
      <td><strong>Upload</strong></td>
      <td>Mengirim program dari komputer ke board lewat USB.</td>
      <td>Seperti “menyimpan lagu ke HP”</td>
    </tr>
    <tr>
      <td><strong>Serial</strong> / Serial Monitor</td>
      <td>Jendela teks di komputer yang menampilkan pesan dari board.</td>
      <td>Bukti lokal bahwa board “hidup” — sebelum Wi‑Fi</td>
    </tr>
  </tbody>
</table>
<p><strong>Awam:</strong> GPIO = soket kaki. Sketch = naskah. Upload = kirim naskah ke otak. Serial = papan tulis teks dari board ke layarmu.</p>

<h2>Keluarga 3 — percakapan data (nanti di fase CONNECTED)</h2>
<p>Cukup kenal dulu. Detailnya jauh di depan peta.</p>
{$flow}
<table>
  <thead>
    <tr>
      <th>Istilah</th>
      <th>Arti awam</th>
      <th>Analogi</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>Telemetry</strong></td>
      <td>Data laporan dari perangkat ke sistem (“suhu sekarang 28°C”).</td>
      <td>Pesan status dari rumah ke HP</td>
    </tr>
    <tr>
      <td><strong>Command</strong></td>
      <td>Perintah dari sistem ke perangkat (“nyalakan lampu”).</td>
      <td>Pesan perintah dari HP ke rumah</td>
    </tr>
    <tr>
      <td><strong>Broker</strong></td>
      <td>Pengantar pesan di tengah (sering di dunia MQTT).</td>
      <td>Kantor pos / resepsionis pesan</td>
    </tr>
    <tr>
      <td><strong>Topic</strong></td>
      <td>Alamat/label saluran pesan (“ruang-belajar/suhu”).</td>
      <td>Nomor kotak surat / label folder</td>
    </tr>
  </tbody>
</table>
<p><strong>Awam:</strong> telemetry = laporan. Command = perintah. Broker = pengantar. Topic = alamat folder pesan. Belum wajib praktek hari ini.</p>

<h2>Keluarga 4 — layar, gudang data, update jauh</h2>
<table>
  <thead>
    <tr>
      <th>Istilah</th>
      <th>Arti awam</th>
      <th>Catatan jalur kita</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>API</strong></td>
      <td>Pintu resmi agar dua program saling bicara.</td>
      <td>Nanti dashboard minta data lewat API</td>
    </tr>
    <tr>
      <td><strong>Database</strong> / <strong>SQLite</strong></td>
      <td>Gudang data. SQLite = gudang ringan di satu file.</td>
      <td>Pilihan Core jalur ini (nanti di FULLSTACK)</td>
    </tr>
    <tr>
      <td><strong>Dashboard</strong></td>
      <td>Layar ringkas yang menampilkan angka/status.</td>
      <td>Yang kamu baca di HP/laptop</td>
    </tr>
    <tr>
      <td><strong>OTA</strong></td>
      <td>Update program board dari jarak jauh (Over-The-Air).</td>
      <td>Fase HERO — belum sekarang</td>
    </tr>
  </tbody>
</table>
<p><strong>Awam:</strong> API = loket. Database = lemari arsip. Dashboard = papan info. OTA = “update aplikasi tanpa cabut kabel” untuk board.</p>

<h2>Nama yang cukup dikenal dulu (preview)</h2>
<p>Tiga nama ini cukup kamu <em>ingat ada</em>. Diperdalam di modulnya masing-masing:</p>
<ul>
  <li><strong>Flask</strong> — framework Python ringan untuk API/backend (Core jalur).</li>
  <li><strong>Node-RED</strong> — alat “susun alur” visual (opsional / edge ringan).</li>
  <li><strong>NTP</strong> — cara board mendapat jam akurat dari internet.</li>
</ul>
<p><strong>Awam:</strong> jangan instal apa pun hari ini. Cukup seperti mengenal nama kota di peta sebelum berangkat.</p>

<h2 id="fsiot-kuis-matching">Praktik — kuis matching 15 soal</h2>
<p>Tutup tabel di atas sebentar. Di bawah ini ada <strong>kuis interaktif</strong>: pilih arti untuk tiap istilah, lalu tekan <strong>Cek skor</strong>. Versi catatan (tulis tangan) tetap tersedia sebagai cadangan.</p>
<p><strong>Kolom istilah:</strong></p>
<ol>
  <li>Sensor</li>
  <li>Aktuator</li>
  <li>Mikrokontroler</li>
  <li>Firmware</li>
  <li>GPIO</li>
  <li>Sketch</li>
  <li>Upload</li>
  <li>Serial Monitor</li>
  <li>Telemetry</li>
  <li>Command</li>
  <li>Broker</li>
  <li>Topic</li>
  <li>API</li>
  <li>SQLite</li>
  <li>OTA</li>
</ol>
<p><strong>Kolom arti (acak):</strong></p>
<ul>
  <li>A. Gudang data ringan dalam satu file</li>
  <li>B. Indra yang mengukur</li>
  <li>C. Kirim program ke board lewat USB</li>
  <li>D. Update program dari jarak jauh</li>
  <li>E. Otot yang bergerak/menyala</li>
  <li>F. Pintu resmi antar program</li>
  <li>G. Otak kecil di board</li>
  <li>H. Label saluran pesan</li>
  <li>I. Program yang menempel di otak kecil</li>
  <li>J. Laporan status dari perangkat</li>
  <li>K. File program di Arduino IDE</li>
  <li>L. Pengantar pesan di tengah</li>
  <li>M. Jendela teks pesan dari board</li>
  <li>N. Perintah ke perangkat</li>
  <li>O. Kaki pin serba guna</li>
</ul>
<p><strong>Awam — cara menguji:</strong> kerjakan dulu di kuis interaktif, baru buka kunci. Target ≥ <strong>12/15</strong>. Tidak perlu menjalankan perintah komputer apa pun.</p>

<h2 id="fsiot-kuis-kunci">Kunci jawaban</h2>
<p>1B · 2E · 3G · 4I · 5O · 6K · 7C · 8M · 9J · 10N · 11L · 12H · 13F · 14A · 15D</p>
<p>Hitung skormu. Di bawah 12? Baca ulang keluarga yang salah, ulangi matching — itu normal.</p>

<h2>Kesalahan umum awam</h2>
<ol>
  <li><strong>Hafal istilah tanpa contoh.</strong> Satu analogi + satu contoh stasiun lebih berharga daripada hafalan kosong.</li>
  <li><strong>Mengira harus praktek MQTT hari ini.</strong> Broker/topic cukup kenal nama di ZERO.</li>
  <li><strong>Mengira Serial = Wi‑Fi.</strong> Serial Monitor biasanya lewat USB lokal dulu.</li>
  <li><strong>Mengira API = website cantik.</strong> API adalah pintu data; dashboard yang “cantik”.</li>
  <li><strong>Mengira OTA wajib dari modul pertama.</strong> OTA di fase HERO.</li>
  <li><strong>Mencampur Seri ESP32 lama sebagai prasyarat.</strong> Jalur ini mandiri. Artikel terkait di bawah bisa dari topik lama — bukan syarat FS-03.</li>
</ol>

<h2>Lanjut belajar</h2>
<p>Setelah FS-03, langkah alami berikutnya adalah <strong>FS-04 — buka kotak: kenali setiap komponen kit</strong> (bentuk board, breadboard, LED, belanja bertahap). Artikel itu belum dilink sampai modulnya siap.</p>
<p>Simpan juga <a href="/belajar/fullstack-iot">halaman jalur Full Stack IoT</a> sebagai pintu masuk resmi.</p>

<h2>Kesimpulan</h2>
<p>Di <strong>#73 (ini)</strong> kamu punya kamus mini: dari sensor/aktuator sampai API/OTA, plus preview Flask/Node-RED/NTP. Board resmi tetap <strong>ESP32-DevKitC-1</strong> — masih kenalan nama, belum wiring.</p>
<p><strong>Awam:</strong> kalau skor matching ≥12/15, FS-03 selesai. Lanjut buka kotak kit di FS-04 saat modulnya terbit.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $sense = $this->senseSvgEn();
        $flow = $this->flowSvgEn();

        return <<<HTML
<h2>Introduction — why a mini glossary?</h2>
<p>This article is <strong>#73 (this article)</strong> · module <strong>FS-03</strong> on the <strong>Full Stack IoT Developer — From Zero</strong> path. In <strong>#72 (FS-02)</strong> you already have the path map. Today we meet the <strong>words</strong> you will see often — so they do not surprise you later.</p>
<p><strong>Beginner:</strong> this glossary is like a list of new friends’ names. Know the face and one sentence about who they are — not their ID number.</p>
<blockquote>
  <p><strong>Prerequisites:</strong> you have seen the seven-layer map + ZERO phase in FS-02. No board, cables, or software downloads yet.</p>
</blockquote>

<h2>Preparation — tools you open today</h2>
<p><strong>Tools used in this article</strong> (no Laragon, no Arduino IDE, no USB board, no terminal yet):</p>
<ul>
  <li><strong>Browser</strong> — to read this article and take the <strong>interactive quiz</strong> at the end (Chrome, Edge, Firefox, or a phone browser).</li>
  <li><strong>Notes</strong> (optional) — paper / Notepad only if you prefer handwriting.</li>
</ul>
<p><strong>There is no syntax to run today.</strong> No code to copy, no <code>php artisan</code>, no Arduino sketch. “Testing” means matching term ↔ meaning in the interactive quiz (target ≥ 12/15).</p>

<h2>Family 1 — sense, brain, muscle</h2>
{$sense}
<table>
  <thead>
    <tr>
      <th>Term</th>
      <th>Beginner meaning</th>
      <th>Study Room Station example</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>Sensor</strong></td>
      <td>A “sense” that measures something.</td>
      <td>Temperature / light sensor on the desk</td>
    </tr>
    <tr>
      <td><strong>Actuator</strong></td>
      <td>A “muscle” that moves or turns on.</td>
      <td>Lamp via a small switch (relay)</td>
    </tr>
    <tr>
      <td><strong>Microcontroller</strong></td>
      <td>The “small brain” on the board.</td>
      <td>The chip inside <strong>ESP32-DevKitC-1</strong></td>
    </tr>
    <tr>
      <td><strong>Firmware</strong></td>
      <td>The program that “lives on” that small brain.</td>
      <td>The program we will later upload to the board</td>
    </tr>
  </tbody>
</table>
<p><strong>Beginner:</strong> sensors read, actuators move, the microcontroller decides. Firmware = the “thoughts” inside the small brain.</p>

<h2>Family 2 — pins &amp; programs on the board</h2>
<table>
  <thead>
    <tr>
      <th>Term</th>
      <th>Beginner meaning</th>
      <th>Note</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>GPIO</strong></td>
      <td>General pins/holes to connect sensors/lamps.</td>
      <td>Stands for General Purpose Input/Output</td>
    </tr>
    <tr>
      <td><strong>Sketch</strong></td>
      <td>A program file in Arduino IDE (community habit name).</td>
      <td>We do not write one today</td>
    </tr>
    <tr>
      <td><strong>Upload</strong></td>
      <td>Sending a program from computer to board over USB.</td>
      <td>Like saving a song onto a phone</td>
    </tr>
    <tr>
      <td><strong>Serial</strong> / Serial Monitor</td>
      <td>A text window on the computer showing messages from the board.</td>
      <td>Local proof the board is “alive” — before Wi‑Fi</td>
    </tr>
  </tbody>
</table>
<p><strong>Beginner:</strong> GPIO = pin sockets. Sketch = script. Upload = send the script to the brain. Serial = a text whiteboard from board to your screen.</p>

<h2>Family 3 — data conversation (later in CONNECTED)</h2>
<p>Know the names first. Details are farther on the map.</p>
{$flow}
<table>
  <thead>
    <tr>
      <th>Term</th>
      <th>Beginner meaning</th>
      <th>Analogy</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>Telemetry</strong></td>
      <td>Status data from device to system (“temperature is 28°C”).</td>
      <td>A status message from home to your phone</td>
    </tr>
    <tr>
      <td><strong>Command</strong></td>
      <td>An order from system to device (“turn the lamp on”).</td>
      <td>A command message from phone to home</td>
    </tr>
    <tr>
      <td><strong>Broker</strong></td>
      <td>A middle messenger (often in MQTT).</td>
      <td>A post office / message receptionist</td>
    </tr>
    <tr>
      <td><strong>Topic</strong></td>
      <td>The channel address/label (“study-room/temperature”).</td>
      <td>A mailbox number / folder label</td>
    </tr>
  </tbody>
</table>
<p><strong>Beginner:</strong> telemetry = report. Command = order. Broker = messenger. Topic = message folder address. No practice required today.</p>

<h2>Family 4 — screen, data storage, remote update</h2>
<table>
  <thead>
    <tr>
      <th>Term</th>
      <th>Beginner meaning</th>
      <th>On our path</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>API</strong></td>
      <td>An official door so two programs can talk.</td>
      <td>Later the dashboard asks for data through an API</td>
    </tr>
    <tr>
      <td><strong>Database</strong> / <strong>SQLite</strong></td>
      <td>A data warehouse. SQLite = a light warehouse in one file.</td>
      <td>Core choice on this path (later in FULLSTACK)</td>
    </tr>
    <tr>
      <td><strong>Dashboard</strong></td>
      <td>A simple screen that shows numbers/status.</td>
      <td>What you read on phone/laptop</td>
    </tr>
    <tr>
      <td><strong>OTA</strong></td>
      <td>Updating board software from afar (Over-The-Air).</td>
      <td>HERO phase — not now</td>
    </tr>
  </tbody>
</table>
<p><strong>Beginner:</strong> API = a service counter. Database = filing cabinet. Dashboard = info board. OTA = “update the app without unplugging” for the board.</p>

<h2>Names to recognize for now (preview)</h2>
<p>Three names are enough to <em>know they exist</em>. Each deepens in its own module:</p>
<ul>
  <li><strong>Flask</strong> — a light Python framework for API/backend (path Core).</li>
  <li><strong>Node-RED</strong> — a visual “flow builder” tool (optional / light edge).</li>
  <li><strong>NTP</strong> — how the board gets accurate time from the internet.</li>
</ul>
<p><strong>Beginner:</strong> install nothing today. Just like learning city names on a map before you travel.</p>

<h2 id="fsiot-kuis-matching">Practice — matching quiz (15 items)</h2>
<p>Briefly close the tables above. Below is an <strong>interactive quiz</strong>: pick a meaning for each term, then press <strong>Check score</strong>. A paper version stays available as a backup.</p>
<p><strong>Terms:</strong></p>
<ol>
  <li>Sensor</li>
  <li>Actuator</li>
  <li>Microcontroller</li>
  <li>Firmware</li>
  <li>GPIO</li>
  <li>Sketch</li>
  <li>Upload</li>
  <li>Serial Monitor</li>
  <li>Telemetry</li>
  <li>Command</li>
  <li>Broker</li>
  <li>Topic</li>
  <li>API</li>
  <li>SQLite</li>
  <li>OTA</li>
</ol>
<p><strong>Meanings (shuffled):</strong></p>
<ul>
  <li>A. Light data warehouse in one file</li>
  <li>B. A sense that measures</li>
  <li>C. Send a program to the board over USB</li>
  <li>D. Update software from afar</li>
  <li>E. A muscle that moves/turns on</li>
  <li>F. Official door between programs</li>
  <li>G. Small brain on the board</li>
  <li>H. Message channel label</li>
  <li>I. Program that lives on the small brain</li>
  <li>J. Status report from a device</li>
  <li>K. Program file in Arduino IDE</li>
  <li>L. Middle messenger</li>
  <li>M. Text window of board messages</li>
  <li>N. An order to a device</li>
  <li>O. General-purpose pins</li>
</ul>
<p><strong>Beginner — how to test:</strong> answer in the interactive quiz first, then open the key. Target ≥ <strong>12/15</strong>. You do not need to run any computer command.</p>

<h2 id="fsiot-kuis-kunci">Answer key</h2>
<p>1B · 2E · 3G · 4I · 5O · 6K · 7C · 8M · 9J · 10N · 11L · 12H · 13F · 14A · 15D</p>
<p>Count your score. Under 12? Re-read the family you missed and match again — that is normal.</p>

<h2>Common beginner mistakes</h2>
<ol>
  <li><strong>Memorizing terms without examples.</strong> One analogy + one station example beats empty drilling.</li>
  <li><strong>Thinking you must practice MQTT today.</strong> Broker/topic are name-only in ZERO.</li>
  <li><strong>Thinking Serial = Wi‑Fi.</strong> Serial Monitor is usually local USB first.</li>
  <li><strong>Thinking API = a pretty website.</strong> API is a data door; the dashboard is the “pretty” screen.</li>
  <li><strong>Thinking OTA is required from module one.</strong> OTA belongs to HERO.</li>
  <li><strong>Mixing old ESP32 series as a prerequisite.</strong> This path is independent. Related articles below may be older topics — not FS-03 requirements.</li>
</ol>

<h2>Keep learning</h2>
<p>After FS-03, the natural next step is <strong>FS-04 — open the box: know every kit part</strong> (board shape, breadboard, LED, gradual shopping). That article is not hard-linked until the module is ready.</p>
<p>Also bookmark the <a href="/belajar/fullstack-iot">Full Stack IoT path page</a> as the official entrance.</p>

<h2>Conclusion</h2>
<p>In <strong>#73 (this article)</strong> you have a mini glossary: from sensor/actuator to API/OTA, plus a Flask/Node-RED/NTP name preview. The official board remains <strong>ESP32-DevKitC-1</strong> — name acquaintance only, no wiring yet.</p>
<p><strong>Beginner:</strong> if your matching score is ≥12/15, FS-03 is done. Continue to open the kit box in FS-04 when that module publishes.</p>
HTML;
    }
}
