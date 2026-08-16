<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class Article114Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@kodingindonesia.com')->first() ?? User::first();
        $category = Category::where('slug', 'iot-smart-device')->first() ?? Category::where('slug', 'esp32-arduino')->first();
        if (! $admin || ! $category) {
            throw new \RuntimeException('User atau kategori IoT tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-html-dashboard-suhu-flask';
        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        foreach (['fullstack-iot', 'iot', 'python', 'flask', 'html', 'javascript', 'sqlite', 'esp32'] as $tagSlug) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagSlug]);
        }

        $article = Article::updateOrCreate(['slug' => $slug], [
            'user_id' => $admin->id,
            'category_id' => $category->id,
            'title' => 'Buka halaman sendiri lalu tampilkan suhu dari REST Flask',
            'title_en' => 'Open your own page then show the temperature from REST Flask',
            'excerpt' => 'FS-44 / #114: dashboard.html dari Flask di http://127.0.0.1:5000, fetch JSON, angka suhu tampil. Bukan file://. Belum Chart.js.',
            'excerpt_en' => 'FS-44 / #114: dashboard.html from Flask at http://127.0.0.1:5000, fetch JSON, a temperature number appears. Not file://. No Chart.js yet.',
            'body' => $this->body(),
            'body_en' => $this->bodyEn(),
            'status' => 'draft',
            'is_featured' => false,
            'published_at' => null,
            'seo_title' => 'Tampil Suhu di Halaman HTML — FS-44',
            'seo_title_en' => 'Show Temperature on an HTML Page — FS-44',
            'seo_description' => 'Lab pemula: Flask menyajikan dashboard.html dan JSON di satu origin. fetch menampilkan suhu. Jangan file://, jangan flask-cors.',
            'seo_description_en' => 'A first lab: Flask serves dashboard.html and JSON on one origin. fetch shows the temperature. Not file://, no flask-cors.',
        ]);
        $article->tags()->sync(Tag::whereIn('slug', ['fullstack-iot', 'iot', 'python', 'flask', 'html', 'javascript', 'sqlite', 'esp32'])->pluck('id'));

        foreach (['webp', 'jpg'] as $extension) {
            $source = public_path("images/fsiot/fs44-cover-dashboard.{$extension}");
            if (is_file($source)) {
                $destination = "articles/covers/fs44-cover-dashboard.{$extension}";
                Storage::disk('public')->put($destination, file_get_contents($source));
            }
        }
        $article->update([
            'cover_image' => 'https://kodingindonesia.com/images/fsiot/fs44-cover-dashboard.webp',
        ]);
    }

    private function figure(string $file, string $alt, string $caption): string
    {
        return '<figure style="margin:1.5rem 0;max-width:100%;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:0.75rem"><img src="/images/fsiot/'.$file.'" alt="'.$alt.'" loading="eager" style="width:100%;height:auto;max-height:640px;object-fit:contain;border-radius:6px;background:#F5F5F0"><figcaption style="font-size:0.85rem;margin-top:0.5rem;color:#1a1a1a">'.$caption.'</figcaption></figure>';
    }

    /**
     * @param  list<array{title: string, text: string}>  $items
     */
    private function stepsCard(array $items, string $caption): string
    {
        $colors = ['#2979FF', '#2979FF', '#FF7A2F', '#1a1a1a', '#2e7d32'];
        $rows = '';
        foreach ($items as $index => $item) {
            $number = $index + 1;
            $color = $colors[$index] ?? '#1a1a1a';
            $border = $index < count($items) - 1 ? 'border-bottom:1px dashed #CBD5E0;' : '';
            $rows .= '<li style="display:flex;gap:1rem;padding:.9rem 0;'.$border.'">'
                .'<span style="flex-shrink:0;width:2rem;height:2rem;border-radius:999px;background:'.$color.';color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:700">'.$number.'</span>'
                .'<div><strong>'.$item['title'].'</strong><span style="display:block;color:#4A5568;margin-top:.25rem">'.$item['text'].'</span></div></li>';
        }

        return '<figure style="margin:1.5rem 0;background:#F5F5F0;border:2.5px solid #1a1a1a;border-radius:8px;padding:1.25rem"><ol style="list-style:none;padding:0;margin:0">'.$rows.'</ol><figcaption style="font-size:0.85rem;margin-top:0.75rem;color:#1a1a1a">'.$caption.'</figcaption></figure>';
    }

    private function requirements(): string
    {
        return "paho-mqtt==2.1.0\nflask==3.1.3";
    }

    private function dashboard(): string
    {
        return implode("\n", [
            '<!DOCTYPE html>',
            '<html lang="id">',
            '<head>',
            '  <meta charset="utf-8">',
            '  <title>Stasiun meja</title>',
            '  <style>',
            '    body { font-family: Arial, sans-serif; background: #f5f5f0; color: #1a1a1a; padding: 2rem; }',
            '    .angka { font-size: 4rem; font-weight: 700; }',
            '  </style>',
            '</head>',
            '<body>',
            '  <h1>Stasiun meja</h1>',
            '  <p>Suhu</p>',
            '  <p id="suhu-angka" class="angka">…</p>',
            '  <p id="status">Menunggu data…</p>',
            '  <script>',
            '    fetch("/telemetry")',
            '      .then((r) => r.json())',
            '      .then((data) => {',
            '        const baris = (data.baris || []).slice(-1)[0];',
            '        const suhu = baris ? baris.temperature_c : null;',
            '        document.getElementById("suhu-angka").textContent = suhu !== null ? suhu : "—";',
            '        document.getElementById("status").textContent =',
            '          suhu !== null ? "Suhu tampil." : "Belum ada baris di stasiun.db.";',
            '      })',
            '      .catch(() => {',
            '        document.getElementById("status").textContent = "Pintu Flask belum terbuka.";',
            '      });',
            '  </script>',
            '</body>',
            '</html>',
        ]);
    }

    private function pintu(): string
    {
        return implode("\n", [
            'import json',
            'import sqlite3',
            'from pathlib import Path',
            '',
            'import paho.mqtt.client as mqtt',
            'from flask import Flask, jsonify, request, send_from_directory',
            '',
            'HOST = "127.0.0.1"',
            'PORT = 5000',
            'BROKER = "127.0.0.1"',
            'MQTT_PORT = 1883',
            'FOLDER = Path(__file__).resolve().parent',
            'DB_PATH = FOLDER / "stasiun.db"',
            '',
            'app = Flask(__name__)',
            '',
            '',
            'def baca_baris():',
            '    if not DB_PATH.exists():',
            '        return None',
            '    with sqlite3.connect(DB_PATH) as db:',
            '        db.row_factory = sqlite3.Row',
            '        rows = db.execute(',
            '            "SELECT id, received_at, device_id, temperature_c, humidity_pct FROM telemetry ORDER BY id"',
            '        ).fetchall()',
            '    return [dict(row) for row in rows]',
            '',
            '',
            'def topic_command(device_id):',
            '    return f"kodingindonesia/fsiot/{device_id}/command"',
            '',
            '',
            '@app.get("/")',
            'def halaman():',
            '    return send_from_directory(str(FOLDER), "dashboard.html")',
            '',
            '',
            '@app.get("/telemetry")',
            'def telemetry():',
            '    rows = baca_baris()',
            '    if rows is None:',
            '        return jsonify({"jumlah": 0, "pesan": "Berkas stasiun.db belum ada. Ulangi FS-40."}), 503',
            '    device_id = (request.args.get("device_id") or "").strip()',
            '    if device_id:',
            '        rows = [row for row in rows if str(row["device_id"]) == device_id]',
            '    return jsonify({"jumlah": len(rows), "device_id": device_id or None, "baris": rows[-10:]})',
            '',
            '',
            '@app.post("/command")',
            'def command():',
            '    data = request.get_json(silent=True) or {}',
            '    relay = str(data.get("relay", "")).lower()',
            '    device_id = str(data.get("device_id", "esp32-meja-01")).strip() or "esp32-meja-01"',
            '    if relay not in {"on", "off"}:',
            '        return jsonify({"ok": False, "pesan": "Isian relay harus on atau off."}), 400',
            '    payload = json.dumps({"device_id": device_id, "relay": relay})',
            '    topic = topic_command(device_id)',
            '    client = mqtt.Client(',
            '        callback_api_version=mqtt.CallbackAPIVersion.VERSION2,',
            '        client_id="fsiot-fs44-pintu",',
            '    )',
            '    try:',
            '        client.connect(BROKER, MQTT_PORT, keepalive=60)',
            '    except OSError as error:',
            '        return jsonify({"ok": False, "pesan": "Broker belum terbuka di 127.0.0.1:1883", "error": str(error)}), 503',
            '    client.publish(topic, payload)',
            '    client.disconnect()',
            '    return jsonify({"ok": True, "topic": topic, "payload": payload})',
            '',
            '',
            'if __name__ == "__main__":',
            '    print("Pintu stasiun terbuka di http://127.0.0.1:5000")',
            '    print("Buka http://127.0.0.1:5000")',
            '    print("GET  http://127.0.0.1:5000/telemetry")',
            '    app.run(host=HOST, port=PORT, debug=False)',
        ]);
    }

    private function body(): string
    {
        $requirements = htmlspecialchars($this->requirements(), ENT_QUOTES, 'UTF-8');
        $dashboard = htmlspecialchars($this->dashboard(), ENT_QUOTES, 'UTF-8');
        $pintu = htmlspecialchars($this->pintu(), ENT_QUOTES, 'UTF-8');
        $tools = $this->figure('fs44-tools-order.png', 'Urutan lima langkah: browser, File Explorer folder lab, Notepad, PowerShell Flask, browser http 127.0.0.1:5000', '<strong>Urutan meja kerja (lima langkah):</strong> browser → File Explorer cek <code>fsiot-fs39</code> → Notepad menulis <code>dashboard.html</code> dan <code>pintu_stasiun.py</code> → PowerShell menjalankan Flask → browser <code>http://127.0.0.1:5000</code>. Diagram buatan Koding Indonesia (FS-44).');
        $why = $this->figure('fs44-why-page.png', 'Tiga kotak: gudang SQLite, pintu JSON Flask, wajah halaman HTML dengan angka suhu', '<strong>JSON tetap data. HTML adalah wajah angkanya.</strong> Baca dari kiri ke kanan: gudang <code>stasiun.db</code> → pintu <code>/telemetry</code> → halaman suhu. Diagram buatan Koding Indonesia (FS-44).');
        $fileVsHttp = $this->figure('fs44-file-vs-http.png', 'Perbandingan file:// ditolak fetch versus http://127.0.0.1:5000 yang boleh', '<strong>Jangan dobel-klik berkas HTML.</strong> <code>file://</code> bukan server. Lab memakai <code>http://127.0.0.1:5000</code>. Diagram buatan Koding Indonesia (FS-44).');
        $origin = $this->figure('fs44-origin.png', 'Satu origin port 5000 aman versus HTML port 8080 dan JSON port 5000 tertolak CORS', '<strong>Satu origin: halaman dan JSON dari pintu yang sama.</strong> Dua port memicu CORS. Jangan pip <code>flask-cors</code>. Diagram buatan Koding Indonesia (FS-44).');
        $flaskServe = $this->figure('fs44-flask-serve.png', 'Alur kiri ke kanan: browser GET slash, Flask dashboard.html, fetch telemetry, angka suhu tampil', '<strong>Gambar utama — Flask menyajikan halaman dan JSON.</strong> Baca dari kiri ke kanan: browser → <code>/</code> → <code>dashboard.html</code> → fetch <code>/telemetry</code> → angka. Diagram buatan Koding Indonesia (FS-44).');
        $fetch = $this->figure('fs44-fetch.png', 'Alur kiri ke kanan: script fetch slash telemetry, JSON baris terakhir, layar angka Suhu tampil', '<strong>fetch mengambil JSON, lalu angka ditulis ke halaman.</strong> Baca dari kiri ke kanan: script → <code>/telemetry</code> → <code>temperature_c</code> → <code>Suhu tampil.</code> Diagram buatan Koding Indonesia (FS-44).');
        $browser = $this->figure('fs44-browser-suhu.png', 'Ilustrasi jendela browser menampilkan judul Stasiun meja dan angka suhu 27.0 dengan status Suhu tampil', '<strong>Browser sudah menampilkan angka suhu.</strong> Alamat yang dikunci adalah <code>http://127.0.0.1:5000</code> dan teks <code>Suhu tampil.</code> Angka suhu boleh berbeda. Ilustrasi buatan Koding Indonesia (FS-44), meniru jendela browser. Tampilan resmi tidak dipakai utuh.');
        $address = $this->figure('fs44-address.png', 'Ilustrasi bilah alamat: benar http://127.0.0.1:5000, jangan file slash dashboard.html', '<strong>Cek bilah alamat: harus http, bukan file.</strong> Kalau tertulis <code>file://</code>, tutup tab itu. Ilustrasi buatan Koding Indonesia (FS-44), meniru bilah alamat browser. Tampilan resmi tidak dipakai utuh.');
        $troubleshooting = $this->figure('fs44-troubleshooting.png', 'Empat pemeriksaan: Flask, SQLite stasiun.db, alamat file versus http, dua port CORS', '<strong>Skema bantu.</strong> Flask ke <code>127.0.0.1:5000</code>. Jangan <code>file://</code>. Jangan dua port. Diagram buatan Koding Indonesia (FS-44).');
        $install = $this->stepsCard([
            ['title' => 'Buka browser', 'text' => 'Pakai Chrome, Firefox, Edge, atau Safari. Siapkan artikel ini. Jangan ketik perintah Python dulu.'],
            ['title' => 'Buka File Explorer', 'text' => 'Masuk ke <code>Documents\\fsiot-fs39</code>. Folder <code>.venv</code>, berkas <code>stasiun.db</code>, dan <code>pintu_stasiun.py</code> harus sudah ada.'],
            ['title' => 'Buka Notepad, tulis berkas', 'text' => 'Simpan <code>dashboard.html</code> dan perbarui <code>pintu_stasiun.py</code> di folder lab. All files, bukan <code>.txt</code>.'],
            ['title' => 'Buka PowerShell, jalankan Flask', 'text' => 'Start → ketik PowerShell. Tidak perlu <em>Run as administrator</em>. Jalankan Flask. Jendela tetap terbuka.'],
            ['title' => 'Buka browser, ketik alamat http', 'text' => 'Tab baru: <code>http://127.0.0.1:5000</code> — bukan <code>file://</code>. Angka suhu muncul, status <code>Suhu tampil.</code>'],
        ], '<strong>Cara menguji hari ini:</strong> bukti sukses = browser di <code>http://127.0.0.1:5000</code> menampilkan angka suhu (bukan <code>…</code>) dan teks <code>Suhu tampil.</code> ESP32 boleh menyala, tetapi tidak wajib.');

        return <<<'HTML'
<h2>Pendahuluan — JSON punya wajah</h2>
<p><strong>FS-44 / #114 (ini)</strong> adalah lab halaman. Kemarin Flask sudah membuka pintu JSON. Hari ini tugasnya lain: <strong>buka halaman sendiri, lalu tampilkan satu angka suhu</strong> yang diambil dari REST itu.</p>
<p><strong>Intinya:</strong> tulis <code>dashboard.html</code>, biarkan Flask menyajikannya di <code>http://127.0.0.1:5000</code>, lalu <code>fetch("/telemetry")</code> menulis suhu ke layar sampai muncul <code>Suhu tampil.</code></p>
<p><strong>Analogi:</strong> JSON adalah isi gudang yang ditulis di kertas. Halaman HTML adalah papan di depan toko. Pengunjung membaca papan, bukan masuk gudang. Grafik Chart.js dan tombol ON/OFF belum dibangun — itu FS-45 dan FS-46.</p>
<p>Prasyarat lab: <strong>FS-42</strong> (Flask sudah pernah terbuka) dan <strong>FS-40</strong> (<code>stasiun.db</code> berisi baris suhu). Saringan <code>device_id</code> dari FS-43 <strong>tidak wajib</strong> untuk fetch hari ini. FS-41 MariaDB <strong>tidak wajib</strong>. ESP32 <strong>boleh menyala</strong>, dan <strong>boleh dicabut</strong>. Tidak ada kabel baru, tidak ada Upload, <strong>Bukan AC 220V</strong>.</p>

<h2>Hasil yang dituju</h2>
<ul>
<li>Berkas <code>dashboard.html</code> ada di folder <code>Documents\fsiot-fs39</code>.</li>
<li>PowerShell menampilkan <code>Pintu stasiun terbuka di http://127.0.0.1:5000</code>.</li>
<li>Bilah alamat browser adalah <code>http://127.0.0.1:5000</code> — bukan <code>file://</code>.</li>
<li>Halaman menampilkan angka suhu (bukan <code>…</code>).</li>
<li>Teks status adalah <code>Suhu tampil.</code></li>
<li>Tab lain, <code>http://127.0.0.1:5000/telemetry</code> masih JSON.</li>
<li>Berkas <code>stasiun.db</code> masih ada.</li>
</ul>
<p><strong>Batas lab hari ini:</strong> belum Chart.js, belum tombol ON/OFF, belum MySQL, belum pustaka CORS. Bukti cukup = angka suhu di halaman HTML dari API lokal. <code>flask==3.1.3</code> sudah dari FS-42. MQTTX boleh tetap terbuka; tidak wajib hari ini.</p>

<h2>Istilah yang dipakai hari ini</h2>
<ul>
<li><strong>HTML</strong> — berkas halaman. Hari ini namanya <code>dashboard.html</code>.</li>
<li><strong>CSS</strong> — pewarnaan sangat dasar: huruf, jarak, ukuran angka.</li>
<li><strong>fetch</strong> — perintah browser untuk mengambil JSON tanpa pindah halaman.</li>
<li><strong>Origin</strong> — skema + host + port, misalnya <code>http://127.0.0.1:5000</code>.</li>
<li><strong>CORS</strong> — aturan browser: halaman dari origin A tidak boleh sembarang mengambil data origin B.</li>
<li><strong>file://</strong> — alamat dobel-klik berkas. Bukan server. Ditolak fetch ke Flask.</li>
<li><strong>GET /</strong> — pintu halaman. Flask mengirim <code>dashboard.html</code>.</li>
<li><strong>GET /telemetry</strong> — pintu JSON. Masih sama seperti FS-42.</li>
</ul>

<h2>Persiapan — buka tool yang benar dulu</h2>
HTML
            .$tools.$install.<<<'HTML'
<p><strong>Jangan dipakai hari ini:</strong> MySQL/MariaDB, phpMyAdmin, Arduino IDE, AC 220V, <code>file://</code>, membuka port 5000 ke internet, mengubah ExecutionPolicy, pip <code>flask-cors</code>, Chart.js, atau <code>python -m http.server</code> sebagai jalur utama. MQTTX boleh tetap. Node-RED boleh tetap terbuka.</p>
<p><strong>Tips ponsel:</strong> jika diagram terasa kecil, gunakan fitur perbesar pada browser. Gambar tidak perlu diketuk sampai memenuhi layar agar teks di sekitarnya tetap terbaca.</p>

<h2>Kenapa halaman, bukan file://</h2>
HTML
            .$why.$fileVsHttp.<<<'HTML'
<p>Kalau kamu dobel-klik <code>dashboard.html</code>, browser membuka <code>file://</code>. Halaman itu bukan tamu Flask. Perintah <code>fetch("/telemetry")</code> tidak punya pintu yang sama, lalu gagal. Lab yang aman: Flask yang menyajikan halaman <strong>dan</strong> JSON.</p>
<p>Itulah “static server sederhana” di kurikulum hari ini: satu proses Flask di komputer ini. Jangan mencari server lain dulu.</p>

<h2>Satu origin — CORS untuk awam</h2>
HTML
            .$origin.<<<'HTML'
<p>Browser menolak fetch lintas origin supaya situs A tidak mencuri data situs B tanpa izin. Di lab, origin yang dikunci adalah <code>http://127.0.0.1:5000</code> untuk halaman <strong>dan</strong> JSON.</p>
<p>Kalau HTML hidup di port 8080 sementara Flask di 5000, itu dua origin. Browser menolak. Itu CORS. <strong>Jangan dipaksa hari ini.</strong> Jangan pip <code>flask-cors</code>. Jangan izinkan internet. Lab hanya <code>127.0.0.1</code>.</p>

<h2>Tulis dashboard.html</h2>
<p><strong>Buka dulu File Explorer</strong>, masuk ke <code>Documents\fsiot-fs39</code>. Folder <code>.venv</code>, berkas <code>stasiun.db</code>, dan <code>pintu_stasiun.py</code> dari FS-42 harus sudah ada. Jika <code>stasiun.db</code> belum ada, ulangi FS-40 dulu.</p>
<p><strong>Buka dulu Notepad.</strong> Tempel kode ini. File → Save As, All files, nama <code>dashboard.html</code>, folder lab. Jangan Save sebagai <code>.txt</code>.</p>
<pre><code class="language-html">
HTML
            .$dashboard.<<<'HTML'
</code></pre>
<p>HTML menyusun judul dan kotak angka. CSS membesarkan angka. <code>fetch("/telemetry")</code> mengambil JSON di origin yang sama, lalu menulis <code>temperature_c</code> dari baris terakhir. Angka suhu boleh berbeda dari gambar. Yang dikunci adalah teks <code>Suhu tampil.</code></p>

<h2>Flask menyajikan halaman dan JSON</h2>
HTML
            .$flaskServe.<<<'HTML'
<p><code>requirements.txt</code> tetap mengunci <code>flask==3.1.3</code> dan <code>paho-mqtt==2.1.0</code> seperti FS-42. Jangan pip ke Python global. Kalau Flask sudah terpasang, tidak perlu pip ulang. Jangan tambah <code>flask-cors</code>.</p>
<pre><code>
HTML
            .$requirements.<<<'HTML'
</code></pre>
<p><strong>Buka dulu Notepad.</strong> Ganti isi <code>pintu_stasiun.py</code> dengan kode di bawah. Save As, All files, folder <code>Documents\fsiot-fs39</code>. GET <code>/</code> mengirim <code>dashboard.html</code>. GET <code>/telemetry</code> tetap JSON. Saringan <code>?device_id=</code> dari FS-43 tetap ada, tetapi fetch hari ini tidak memakainya.</p>
<pre><code class="language-python">
HTML
            .$pintu.<<<'HTML'
</code></pre>
<p>Jika Flask hilang dari venv:</p>
<pre><code>.\.venv\Scripts\python.exe -m pip install -r requirements.txt</code></pre>

<h2>Jalankan Flask, buka di browser</h2>
HTML
            .$browser.<<<'HTML'
<p>Tutup Flask lama jika masih jalan, lalu <strong>Buka dulu PowerShell:</strong> Start → ketik <strong>PowerShell</strong> → Windows PowerShell. Tidak perlu <em>Run as administrator</em>.</p>
<p><strong>Cara menempel perintah:</strong> salin baris, klik jendela PowerShell, lalu <kbd>Ctrl</kbd>+<kbd>V</kbd> atau klik kanan. Setelah teks muncul, tekan Enter.</p>
<pre><code>cd "$env:USERPROFILE\Documents\fsiot-fs39"
.\.venv\Scripts\python.exe pintu_stasiun.py</code></pre>
<p><strong>Hasil yang dicari:</strong> <code>Pintu stasiun terbuka di http://127.0.0.1:5000</code> lalu <code>Buka http://127.0.0.1:5000</code>. Jendela ini <strong>tetap terbuka</strong>. Jika <code>.\.venv\Scripts\Activate.ps1</code> ditolak, <strong>jangan ubah ExecutionPolicy</strong>.</p>
<p><strong>Buka browser</strong> di tab baru. Ketik alamat ini, lalu Enter:</p>
<pre><code>http://127.0.0.1:5000</code></pre>
<p><strong>Hasil yang dicari:</strong> judul Stasiun meja, angka suhu, teks <code>Suhu tampil.</code> Ini halaman HTML, bukan JSON mentah. <strong>Jangan buka berkas HTML lewat <code>file://</code>.</strong></p>
<p>Tab lain, pastikan pintu JSON masih hidup:</p>
<pre><code>http://127.0.0.1:5000/telemetry</code></pre>
<p><strong>macOS atau Linux:</strong> buka Terminal, <code>cd ~/Documents/fsiot-fs39</code>, lalu <code>.venv/bin/python pintu_stasiun.py</code>.</p>

<h2>Baca fetch kiri ke kanan</h2>
HTML
            .$fetch.<<<'HTML'
<p>Script di dalam halaman memanggil <code>/telemetry</code> — path relatif, origin yang sama. Jawaban JSON punya daftar <code>baris</code>. Baris terakhir punya <code>temperature_c</code>. Angka itu ditulis ke <code>id="suhu-angka"</code>, lalu status menjadi <code>Suhu tampil.</code></p>
<p>Kalau Flask belum terbuka, status menjadi <code>Pintu Flask belum terbuka.</code> Kalau <code>stasiun.db</code> kosong, ulangi FS-40 dulu.</p>

<h2>Cek bilah alamat</h2>
HTML
            .$address.<<<'HTML'
<p>Yang dikunci bukan angka 27.0 di gambar. Yang dikunci: awalan <code>http://127.0.0.1:5000</code> dan teks <code>Suhu tampil.</code> Kalau bilah alamat mulai dengan <code>file://</code>, tutup tab itu, kembalikan Flask, ketik ulang alamat http.</p>

<h2>Bonus: python -m http.server</h2>
<p>Tidak wajib. Kurikulum menyebut server statis sederhana. Di lab ini, Flask sudah melakukan pekerjaan itu: menyajikan <code>dashboard.html</code>.</p>
<p>Server umum Python, di jendela PowerShell <strong>lain</strong> (jangan matikan Flask), kira-kira begini:</p>
<pre><code>.\.venv\Scripts\python.exe -m http.server 8080</code></pre>
<p>Lalu browser ke <code>http://127.0.0.1:8080/dashboard.html</code> sementara JSON tetap di port 5000. Itu dua origin. Browser menolak fetch. Itulah CORS yang diajarkan, <strong>bukan</strong> jalur lulus. Tutup server 8080. Kembali ke <code>http://127.0.0.1:5000</code>.</p>

<h2>Jika angka tidak muncul</h2>
HTML
            .$troubleshooting.<<<'HTML'
<ol>
<li><strong>Flask belum terbuka.</strong> Jendela harus menampilkan <code>Pintu stasiun terbuka</code>. Jangan ditutup sebelum angka tampil.</li>
<li><strong>stasiun.db kosong atau hilang.</strong> Ulangi FS-40. GET <code>/telemetry</code> harus berisi <code>baris</code>.</li>
<li><strong>Masih file://.</strong> Tutup tab. Ketik <code>http://127.0.0.1:5000</code>.</li>
<li><strong>HTML di 8080, JSON di 5000.</strong> Tutup <code>http.server</code>. Satu origin saja.</li>
<li><strong>dashboard.html belum di folder yang sama.</strong> Flask mencari berkas di samping <code>pintu_stasiun.py</code>.</li>
</ol>

<h2 id="fsiot-dash-checklist">Checklist sebelum FS-45</h2>
<p>Centang setelah kamu benar-benar melakukan setiap poin. Target: <strong>10/10</strong>. Progres disimpan di browser perangkatmu dan tidak dikirim ke server.</p>
<ul id="fsiot-dash-checklist-items">
<li>Saya tidak membuka <code>dashboard.html</code> lewat <code>file://</code>.</li>
<li>PowerShell menampilkan Pintu stasiun terbuka di port 5000.</li>
<li>Bilah alamat browser adalah <code>http://127.0.0.1:5000</code>.</li>
<li>Halaman menampilkan angka suhu, bukan <code>…</code>.</li>
<li>Teks status adalah <code>Suhu tampil.</code></li>
<li>Berkas <code>stasiun.db</code> masih ada di folder lab.</li>
<li>GET <code>/telemetry</code> masih JSON.</li>
<li>Saya tidak mengubah ExecutionPolicy.</li>
<li>Saya tidak memakai MySQL hari ini.</li>
<li>Saya tidak memakai dua port atau pustaka CORS sebagai jalur utama.</li>
</ul>
<p><strong>Cara memeriksa kesiapan:</strong> ceritakan dengan kata-katamu: halaman HTML → Flask satu origin → fetch JSON → angka suhu → <code>Suhu tampil.</code> Pada FS-45, angka ini mulai jadi grafik Chart.js. MariaDB tetap opsional.</p>

<h2>Kesalahan yang sering terjadi</h2>
<ul>
<li><strong>Membuka file://.</strong> Fetch ke Flask ditolak. Pakai <code>http://127.0.0.1:5000</code>.</li>
<li><strong>CORS tanpa paham.</strong> Dua port, lalu pip <code>flask-cors</code>. Lab aman = satu origin.</li>
<li><strong>Campur port asal.</strong> HTML 8080 dan JSON 5000 adalah dua origin.</li>
<li><strong>Mengubah ExecutionPolicy.</strong> Tetap pakai <code>.venv\Scripts\python.exe</code>.</li>
<li><strong>Menutup jendela Flask sebelum angka tampil.</strong> Pintu harus tetap terbuka.</li>
<li><strong>Memakai MySQL hari ini.</strong> Gudang tetap SQLite <code>stasiun.db</code>.</li>
<li><strong>Membangun Chart.js atau tombol ON/OFF hari ini.</strong> Ditunda ke FS-45 dan FS-46.</li>
</ul>

<h2>Pertanyaan yang sering muncul</h2>
<h3>Kenapa jangan file://?</h3>
<p>Karena itu bukan server. Halaman dan JSON harus satu origin. Flask menyajikan keduanya.</p>
<h3>Apa itu CORS?</h3>
<p>Aturan browser: origin A tidak boleh mengambil data origin B tanpa izin. Lab aman tidak menyeberang origin.</p>
<h3>Wajib python -m http.server?</h3>
<p>Tidak. Flask sudah server halaman. Server port 8080 hanya contoh kenapa dua origin gagal.</p>
<h3>Wajib pip flask-cors?</h3>
<p>Tidak. Jangan dipasang hari ini.</p>
<h3>Wajib saring device_id?</h3>
<p>Tidak. Fetch memakai <code>/telemetry</code> tanpa saringan. FS-43 tetap berguna, bukan syarat lulus.</p>
<h3>ESP32 wajib menyala?</h3>
<p>Tidak. SQLite sudah punya baris dari FS-40. <strong>Bukan AC 220V.</strong></p>
<h3>Apakah Chart.js hari ini?</h3>
<p>Tidak. Satu angka cukup. Grafik ada di FS-45.</p>
<h3>Kenapa angkanya beda dari gambar?</h3>
<p>Baris di <code>stasiun.db</code> milikmu. Yang dikunci adalah <code>Suhu tampil.</code></p>

<h2>Sumber</h2>
<ul>
<li><a href="https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API" target="_blank" rel="noopener noreferrer">MDN Fetch API</a></li>
<li><a href="https://developer.mozilla.org/en-US/docs/Web/HTML" target="_blank" rel="noopener noreferrer">MDN HTML</a></li>
<li><a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Guides/CORS" target="_blank" rel="noopener noreferrer">MDN CORS</a></li>
<li><a href="https://flask.palletsprojects.com/en/stable/api/#flask.send_from_directory" target="_blank" rel="noopener noreferrer">Flask send_from_directory</a> (BSD-3-Clause)</li>
<li><a href="https://pypi.org/project/Flask/3.1.3/" target="_blank" rel="noopener noreferrer">Flask 3.1.3 on PyPI</a></li>
<li><a href="https://docs.python.org/3/library/http.server.html" target="_blank" rel="noopener noreferrer">http.server — Python docs</a> (dibaca sebagai konsep; bukan jalur utama lab)</li>
<li><a href="https://docs.python.org/3/library/sqlite3.html" target="_blank" rel="noopener noreferrer">sqlite3 — Python docs</a></li>
<li>Diagram urutan tools, gudang–pintu–wajah, file versus http, origin, Flask menyajikan, fetch, skema periksa — Koding Indonesia (FS-44). Ilustrasi bilah alamat dan jendela suhu — Koding Indonesia (FS-44).</li>
</ul>

<h2>Selanjutnya</h2>
<p><strong>Ringkasnya:</strong> halaman sendiri sudah terbuka di <code>http://127.0.0.1:5000</code>. Fetch mengambil JSON, angka suhu tampil. Pada <strong>FS-45</strong>, angka ini mulai jadi grafik Chart.js. Jangan <code>file://</code>. MariaDB tetap opsional.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $requirements = htmlspecialchars($this->requirements(), ENT_QUOTES, 'UTF-8');
        $dashboard = htmlspecialchars($this->dashboard(), ENT_QUOTES, 'UTF-8');
        $pintu = htmlspecialchars($this->pintu(), ENT_QUOTES, 'UTF-8');
        $tools = $this->figure('fs44-tools-order.png', 'Five-step order: browser, File Explorer lab folder, Notepad, PowerShell Flask, browser http 127.0.0.1:5000', '<strong>Desk order (five steps):</strong> browser → File Explorer checks <code>fsiot-fs39</code> → Notepad writes <code>dashboard.html</code> and <code>pintu_stasiun.py</code> → PowerShell runs Flask → browser <code>http://127.0.0.1:5000</code>. Diagram by Koding Indonesia (FS-44).');
        $why = $this->figure('fs44-why-page.png', 'Three boxes: SQLite store, Flask JSON door, HTML page face with a temperature number', '<strong>JSON stays data. HTML is the face of the number.</strong> Read left to right: store <code>stasiun.db</code> → door <code>/telemetry</code> → temperature page. Diagram by Koding Indonesia (FS-44).');
        $fileVsHttp = $this->figure('fs44-file-vs-http.png', 'Comparison of file:// rejected by fetch versus http://127.0.0.1:5000 allowed', '<strong>Do not double-click the HTML file.</strong> <code>file://</code> is not a server. The lab uses <code>http://127.0.0.1:5000</code>. Diagram by Koding Indonesia (FS-44).');
        $origin = $this->figure('fs44-origin.png', 'Same origin on port 5000 is safe versus HTML on 8080 and JSON on 5000 rejected by CORS', '<strong>Same origin: page and JSON from the same door.</strong> Two ports trigger CORS. Do not pip <code>flask-cors</code>. Diagram by Koding Indonesia (FS-44).');
        $flaskServe = $this->figure('fs44-flask-serve.png', 'Left-to-right flow: browser GET slash, Flask dashboard.html, fetch telemetry, temperature number appears', '<strong>Main figure — Flask serves the page and the JSON.</strong> Read left to right: browser → <code>/</code> → <code>dashboard.html</code> → fetch <code>/telemetry</code> → the number. Diagram by Koding Indonesia (FS-44).');
        $fetch = $this->figure('fs44-fetch.png', 'Left-to-right flow: script fetch slash telemetry, JSON last row, screen number Suhu tampil', '<strong>fetch takes JSON, then the number is written on the page.</strong> Read left to right: script → <code>/telemetry</code> → <code>temperature_c</code> → <code>Suhu tampil.</code> Diagram by Koding Indonesia (FS-44).');
        $browser = $this->figure('fs44-browser-suhu.png', 'Browser window illustration showing title Stasiun meja and temperature 27.0 with status Suhu tampil', '<strong>The browser is already showing a temperature number.</strong> The lock is the address <code>http://127.0.0.1:5000</code> and the text <code>Suhu tampil.</code> The temperature number may differ. Illustration by Koding Indonesia (FS-44), modelled on a browser window. The official window is not used as-is.');
        $address = $this->figure('fs44-address.png', 'Address bar illustration: correct http://127.0.0.1:5000, do not use file slash dashboard.html', '<strong>Check the address bar: it must be http, not file.</strong> If it says <code>file://</code>, close that tab. Illustration by Koding Indonesia (FS-44), modelled on a browser address bar. The official window is not used as-is.');
        $troubleshooting = $this->figure('fs44-troubleshooting.png', 'Four checks: Flask, SQLite stasiun.db, file versus http address, two-port CORS', '<strong>Helper schematic.</strong> Flask uses <code>127.0.0.1:5000</code>. Do not use <code>file://</code>. Do not use two ports. Diagram by Koding Indonesia (FS-44).');
        $install = $this->stepsCard([
            ['title' => 'Open a browser', 'text' => 'Use Chrome, Firefox, Edge, or Safari. Keep this guide ready. Do not type Python commands yet.'],
            ['title' => 'Open File Explorer', 'text' => 'Go to <code>Documents\\fsiot-fs39</code>. The <code>.venv</code> folder, the <code>stasiun.db</code> file, and <code>pintu_stasiun.py</code> must already be there.'],
            ['title' => 'Open Notepad, write the files', 'text' => 'Save <code>dashboard.html</code> and update <code>pintu_stasiun.py</code> in the lab folder. All files, not <code>.txt</code>.'],
            ['title' => 'Open PowerShell, run Flask', 'text' => 'Start → type PowerShell. You do not need <em>Run as administrator</em>. Run Flask. Leave the window open.'],
            ['title' => 'Open a browser, type the http address', 'text' => 'New tab: <code>http://127.0.0.1:5000</code> — not <code>file://</code>. A temperature number appears, status <code>Suhu tampil.</code>'],
        ], '<strong>How to test today:</strong> success = the browser at <code>http://127.0.0.1:5000</code> shows a temperature number (not <code>…</code>) and the text <code>Suhu tampil.</code> The ESP32 may be on, but it is not required.');

        return <<<'HTML'
<h2>Introduction — JSON gets a face</h2>
<p><strong>FS-44 / #114 (this article)</strong> is the page lab. Yesterday Flask already opened the JSON door. Today the job is different: <strong>open your own page, then show one temperature number</strong> taken from that REST door.</p>
<p><strong>In short:</strong> write <code>dashboard.html</code>, let Flask serve it at <code>http://127.0.0.1:5000</code>, then <code>fetch("/telemetry")</code> writes the temperature onto the screen until <code>Suhu tampil.</code> appears.</p>
<p><strong>Analogy:</strong> JSON is the store contents written on paper. The HTML page is the sign in front of the shop. Visitors read the sign; they do not walk into the store. Chart.js graphs and ON/OFF buttons are not built yet — that is FS-45 and FS-46.</p>
<p>Lab prerequisites: <strong>FS-42</strong> (Flask has opened before) and <strong>FS-40</strong> (<code>stasiun.db</code> has temperature rows). The <code>device_id</code> filter from FS-43 is <strong>not required</strong> for today’s fetch. FS-41 MariaDB is <strong>not required</strong>. The ESP32 <strong>may stay on</strong>, and <strong>may be unplugged</strong>. No new cables, no Upload, <strong>Not AC mains</strong>.</p>

<h2>Expected outcome</h2>
<ul>
<li>The file <code>dashboard.html</code> is in <code>Documents\fsiot-fs39</code>.</li>
<li>PowerShell shows <code>Pintu stasiun terbuka di http://127.0.0.1:5000</code>.</li>
<li>The browser address bar is <code>http://127.0.0.1:5000</code> — not <code>file://</code>.</li>
<li>The page shows a temperature number (not <code>…</code>).</li>
<li>The status text is <code>Suhu tampil.</code></li>
<li>In another tab, <code>http://127.0.0.1:5000/telemetry</code> is still JSON.</li>
<li>The file <code>stasiun.db</code> is still there.</li>
</ul>
<p><strong>Today’s lab boundary:</strong> no Chart.js, no ON/OFF buttons, no MySQL, no CORS library. Enough proof = a temperature number on an HTML page from the local API. <code>flask==3.1.3</code> is already from FS-42. MQTTX may stay open; it is not required today.</p>

<h2>Terms used today</h2>
<ul>
<li><strong>HTML</strong> — the page file. Today it is named <code>dashboard.html</code>.</li>
<li><strong>CSS</strong> — very basic styling: font, spacing, number size.</li>
<li><strong>fetch</strong> — the browser command that loads JSON without leaving the page.</li>
<li><strong>Origin</strong> — scheme + host + port, for example <code>http://127.0.0.1:5000</code>.</li>
<li><strong>CORS</strong> — the browser rule: a page from origin A must not freely take data from origin B.</li>
<li><strong>file://</strong> — the address from double-clicking a file. Not a server. Fetch to Flask is rejected.</li>
<li><strong>GET /</strong> — the page door. Flask sends <code>dashboard.html</code>.</li>
<li><strong>GET /telemetry</strong> — the JSON door. Still the same as FS-42.</li>
</ul>

<h2>Preparation — open the right tools first</h2>
HTML
            .$tools.$install.<<<'HTML'
<p><strong>Not used today:</strong> MySQL/MariaDB, phpMyAdmin, Arduino IDE, AC mains, <code>file://</code>, opening port 5000 to the internet, changing ExecutionPolicy, pip <code>flask-cors</code>, Chart.js, or <code>python -m http.server</code> as the main path. MQTTX may stay. Node-RED may stay open.</p>
<p><strong>Phone tip:</strong> if a diagram feels small, use the browser zoom. You do not need to tap the image until it fills the screen; nearby text should stay readable.</p>

<h2>Why a page, not file://</h2>
HTML
            .$why.$fileVsHttp.<<<'HTML'
<p>If you double-click <code>dashboard.html</code>, the browser opens <code>file://</code>. That page is not a Flask guest. The command <code>fetch("/telemetry")</code> has no matching door, then it fails. The safe lab: Flask serves the page <strong>and</strong> the JSON.</p>
<p>That is the “simple static server” in today’s curriculum: one Flask process on this computer. Do not look for another server yet.</p>

<h2>Same origin — CORS for beginners</h2>
HTML
            .$origin.<<<'HTML'
<p>The browser rejects cross-origin fetch so site A cannot steal site B’s data without permission. In the lab, the locked origin is <code>http://127.0.0.1:5000</code> for the page <strong>and</strong> the JSON.</p>
<p>If HTML lives on port 8080 while Flask is on 5000, that is two origins. The browser rejects it. That is CORS. <strong>Do not force it today.</strong> Do not pip <code>flask-cors</code>. Do not allow the internet. The lab is only <code>127.0.0.1</code>.</p>

<h2>Write dashboard.html</h2>
<p><strong>Open File Explorer first</strong>, go to <code>Documents\fsiot-fs39</code>. The <code>.venv</code> folder, the <code>stasiun.db</code> file, and <code>pintu_stasiun.py</code> from FS-42 must already be there. If <code>stasiun.db</code> is missing, repeat FS-40 first.</p>
<p><strong>Open Notepad first.</strong> Paste this code. File → Save As, All files, name <code>dashboard.html</code>, lab folder. Do not Save as <code>.txt</code>.</p>
<pre><code class="language-html">
HTML
            .$dashboard.<<<'HTML'
</code></pre>
<p>HTML builds the title and the number box. CSS enlarges the number. <code>fetch("/telemetry")</code> loads JSON on the same origin, then writes <code>temperature_c</code> from the last row. The temperature number may differ from the picture. The lock is the text <code>Suhu tampil.</code></p>

<h2>Flask serves the page and JSON</h2>
HTML
            .$flaskServe.<<<'HTML'
<p><code>requirements.txt</code> still pins <code>flask==3.1.3</code> and <code>paho-mqtt==2.1.0</code> as in FS-42. Do not pip into global Python. If Flask is already installed, you do not need to pip again. Do not add <code>flask-cors</code>.</p>
<pre><code>
HTML
            .$requirements.<<<'HTML'
</code></pre>
<p><strong>Open Notepad first.</strong> Replace <code>pintu_stasiun.py</code> with the code below. Save As, All files, folder <code>Documents\fsiot-fs39</code>. GET <code>/</code> sends <code>dashboard.html</code>. GET <code>/telemetry</code> is still JSON. The <code>?device_id=</code> filter from FS-43 stays, but today’s fetch does not use it.</p>
<pre><code class="language-python">
HTML
            .$pintu.<<<'HTML'
</code></pre>
<p>If Flask is missing from the venv:</p>
<pre><code>.\.venv\Scripts\python.exe -m pip install -r requirements.txt</code></pre>

<h2>Run Flask, open the browser</h2>
HTML
            .$browser.<<<'HTML'
<p>Close the old Flask window if it is still running, then <strong>Open PowerShell first:</strong> Start → type <strong>PowerShell</strong> → Windows PowerShell. You do not need <em>Run as administrator</em>.</p>
<p><strong>How to paste a command:</strong> copy the line, click the PowerShell window, then <kbd>Ctrl</kbd>+<kbd>V</kbd> or right-click. After the text appears, press Enter.</p>
<pre><code>cd "$env:USERPROFILE\Documents\fsiot-fs39"
.\.venv\Scripts\python.exe pintu_stasiun.py</code></pre>
<p><strong>What you want:</strong> <code>Pintu stasiun terbuka di http://127.0.0.1:5000</code> then <code>Buka http://127.0.0.1:5000</code>. Leave this window <strong>open</strong>. If <code>.\.venv\Scripts\Activate.ps1</code> is rejected, <strong>do not change ExecutionPolicy</strong>.</p>
<p><strong>Open a browser</strong> in a new tab. Type this address, then Enter:</p>
<pre><code>http://127.0.0.1:5000</code></pre>
<p><strong>What you want:</strong> the title Stasiun meja, a temperature number, the text <code>Suhu tampil.</code> This is an HTML page, not raw JSON. <strong>Do not open the HTML file through <code>file://</code>.</strong></p>
<p>In another tab, confirm the JSON door still lives:</p>
<pre><code>http://127.0.0.1:5000/telemetry</code></pre>
<p><strong>macOS or Linux:</strong> open Terminal, <code>cd ~/Documents/fsiot-fs39</code>, then <code>.venv/bin/python pintu_stasiun.py</code>.</p>

<h2>Read fetch left to right</h2>
HTML
            .$fetch.<<<'HTML'
<p>The script inside the page calls <code>/telemetry</code> — a relative path, same origin. The JSON reply has a <code>baris</code> list. The last row has <code>temperature_c</code>. That number is written into <code>id="suhu-angka"</code>, then the status becomes <code>Suhu tampil.</code></p>
<p>If Flask is not open, the status becomes <code>Pintu Flask belum terbuka.</code> If <code>stasiun.db</code> is empty, repeat FS-40 first.</p>

<h2>Check the address bar</h2>
HTML
            .$address.<<<'HTML'
<p>The lock is not the number 27.0 in the picture. The lock is the prefix <code>http://127.0.0.1:5000</code> and the text <code>Suhu tampil.</code> If the address bar starts with <code>file://</code>, close that tab, bring Flask back, and type the http address again.</p>

<h2>Bonus: python -m http.server</h2>
<p>Not required. The curriculum mentions a simple static server. In this lab, Flask already does that job: it serves <code>dashboard.html</code>.</p>
<p>The generic Python server, in <strong>another</strong> PowerShell window (do not stop Flask), looks roughly like this:</p>
<pre><code>.\.venv\Scripts\python.exe -m http.server 8080</code></pre>
<p>Then the browser opens <code>http://127.0.0.1:8080/dashboard.html</code> while JSON stays on port 5000. That is two origins. The browser rejects fetch. That is the CORS lesson, <strong>not</strong> the pass path. Close the 8080 server. Return to <code>http://127.0.0.1:5000</code>.</p>

<h2>If the number does not appear</h2>
HTML
            .$troubleshooting.<<<'HTML'
<ol>
<li><strong>Flask is not open.</strong> The window must show <code>Pintu stasiun terbuka</code>. Do not close it before the number appears.</li>
<li><strong>stasiun.db is empty or missing.</strong> Repeat FS-40. GET <code>/telemetry</code> must contain <code>baris</code>.</li>
<li><strong>Still file://.</strong> Close the tab. Type <code>http://127.0.0.1:5000</code>.</li>
<li><strong>HTML on 8080, JSON on 5000.</strong> Close <code>http.server</code>. One origin only.</li>
<li><strong>dashboard.html is not in the same folder.</strong> Flask looks for the file next to <code>pintu_stasiun.py</code>.</li>
</ol>

<h2 id="fsiot-dash-checklist">Checklist before FS-45</h2>
<p>Tick after you have actually done each item. Target: <strong>10/10</strong>. Progress stays in this browser on your device and is not sent to the server.</p>
<ul id="fsiot-dash-checklist-items">
<li>I did not open <code>dashboard.html</code> through <code>file://</code>.</li>
<li>PowerShell shows Pintu stasiun terbuka on port 5000.</li>
<li>The browser address bar is <code>http://127.0.0.1:5000</code>.</li>
<li>The page shows a temperature number, not <code>…</code>.</li>
<li>The status text is <code>Suhu tampil.</code></li>
<li>The file <code>stasiun.db</code> is still in the lab folder.</li>
<li>GET <code>/telemetry</code> is still JSON.</li>
<li>I did not change ExecutionPolicy.</li>
<li>I did not use MySQL today.</li>
<li>I did not use two ports or a CORS library as the main path.</li>
</ul>
<p><strong>How to check readiness:</strong> tell it in your own words: HTML page → Flask one origin → fetch JSON → temperature number → <code>Suhu tampil.</code> In FS-45, this number starts becoming a Chart.js graph. MariaDB stays optional.</p>

<h2>Common mistakes</h2>
<ul>
<li><strong>Opening file://.</strong> Fetch to Flask is rejected. Use <code>http://127.0.0.1:5000</code>.</li>
<li><strong>CORS without understanding.</strong> Two ports, then pip <code>flask-cors</code>. The safe lab is one origin.</li>
<li><strong>Mixing origin ports.</strong> HTML on 8080 and JSON on 5000 are two origins.</li>
<li><strong>Changing ExecutionPolicy.</strong> Keep using <code>.venv\Scripts\python.exe</code>.</li>
<li><strong>Closing the Flask window before the number appears.</strong> The door must stay open.</li>
<li><strong>Using MySQL today.</strong> The store stays SQLite <code>stasiun.db</code>.</li>
<li><strong>Building Chart.js or ON/OFF buttons today.</strong> That waits for FS-45 and FS-46.</li>
</ul>

<h2>Frequently asked questions</h2>
<h3>Why not file://?</h3>
<p>Because that is not a server. The page and the JSON must share one origin. Flask serves both.</p>
<h3>What is CORS?</h3>
<p>The browser rule: origin A must not take origin B’s data without permission. The safe lab does not cross origins.</p>
<h3>Is python -m http.server required?</h3>
<p>No. Flask is already the page server. The port 8080 server is only an example of why two origins fail.</p>
<h3>Must I pip flask-cors?</h3>
<p>No. Do not install it today.</p>
<h3>Must I filter device_id?</h3>
<p>No. Fetch uses <code>/telemetry</code> with no filter. FS-43 stays useful, not a pass gate.</p>
<h3>Must the ESP32 stay on?</h3>
<p>No. SQLite already has rows from FS-40. <strong>Not AC mains.</strong></p>
<h3>Is Chart.js today?</h3>
<p>No. One number is enough. The graph is FS-45.</p>
<h3>Why is the number different from the picture?</h3>
<p>The rows in <code>stasiun.db</code> are yours. The lock is <code>Suhu tampil.</code></p>

<h2>Sources</h2>
<ul>
<li><a href="https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API" target="_blank" rel="noopener noreferrer">MDN Fetch API</a></li>
<li><a href="https://developer.mozilla.org/en-US/docs/Web/HTML" target="_blank" rel="noopener noreferrer">MDN HTML</a></li>
<li><a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Guides/CORS" target="_blank" rel="noopener noreferrer">MDN CORS</a></li>
<li><a href="https://flask.palletsprojects.com/en/stable/api/#flask.send_from_directory" target="_blank" rel="noopener noreferrer">Flask send_from_directory</a> (BSD-3-Clause)</li>
<li><a href="https://pypi.org/project/Flask/3.1.3/" target="_blank" rel="noopener noreferrer">Flask 3.1.3 on PyPI</a></li>
<li><a href="https://docs.python.org/3/library/http.server.html" target="_blank" rel="noopener noreferrer">http.server — Python docs</a> (read as a concept; not the main lab path)</li>
<li><a href="https://docs.python.org/3/library/sqlite3.html" target="_blank" rel="noopener noreferrer">sqlite3 — Python docs</a></li>
<li>Diagrams for tool order, store–door–face, file versus http, origin, Flask serving, fetch, and the check schematic — Koding Indonesia (FS-44). Address-bar and temperature-window illustrations — Koding Indonesia (FS-44).</li>
</ul>

<h2>Next</h2>
<p><strong>In short:</strong> your own page is already open at <code>http://127.0.0.1:5000</code>. Fetch takes JSON, the temperature number appears. In <strong>FS-45</strong>, this number starts becoming a Chart.js graph. Do not use <code>file://</code>. MariaDB stays optional.</p>
HTML;
    }
}
