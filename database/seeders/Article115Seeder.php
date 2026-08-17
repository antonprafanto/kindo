<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class Article115Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@kodingindonesia.com')->first() ?? User::first();
        $category = Category::where('slug', 'iot-smart-device')->first() ?? Category::where('slug', 'esp32-arduino')->first();
        if (! $admin || ! $category) {
            throw new \RuntimeException('User atau kategori IoT tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-chartjs-histori-suhu-flask';
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
            'title' => 'Lihat tren suhu satu jam di halaman dengan Chart.js',
            'title_en' => 'See a one-hour temperature trend on the page with Chart.js',
            'excerpt' => 'FS-45 / #115: Chart.js dari CDN, GET /history?hours=1, garis tren di http://127.0.0.1:5000. Bukan MySQL. Belum tombol ON/OFF.',
            'excerpt_en' => 'FS-45 / #115: Chart.js from a CDN, GET /history?hours=1, a trend line at http://127.0.0.1:5000. Not MySQL. No ON/OFF buttons yet.',
            'body' => $this->body(),
            'body_en' => $this->bodyEn(),
            'status' => 'draft',
            'is_featured' => false,
            'published_at' => null,
            'seo_title' => 'Grafik Tren Suhu — FS-45',
            'seo_title_en' => 'Show a Temperature Trend Chart — FS-45',
            'seo_description' => 'Lab pemula: Chart.js dari CDN, histori SQLite 1 jam, polling 5 detik. Jangan MySQL, jangan file://, jangan tombol ON/OFF.',
            'seo_description_en' => 'A first lab: Chart.js from a CDN, one-hour SQLite history, 5-second polling. Not MySQL, not file://, no ON/OFF buttons.',
        ]);
        $article->tags()->sync(Tag::whereIn('slug', ['fullstack-iot', 'iot', 'python', 'flask', 'html', 'javascript', 'sqlite', 'esp32'])->pluck('id'));

        foreach (['webp', 'jpg'] as $extension) {
            $source = public_path("images/fsiot/fs45-cover-chart.{$extension}");
            if (is_file($source)) {
                $destination = "articles/covers/fs45-cover-chart.{$extension}";
                Storage::disk('public')->put($destination, file_get_contents($source));
            }
        }
        $article->update([
            'cover_image' => 'https://kodingindonesia.com/images/fsiot/fs45-cover-chart.webp',
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

    private function isi(): string
    {
        return implode("\n", [
            'import sqlite3',
            'from datetime import datetime, timedelta, timezone',
            'from pathlib import Path',
            '',
            'FOLDER = Path(__file__).resolve().parent',
            'DB_PATH = FOLDER / "stasiun.db"',
            'DEVICE = "esp32-meja-01"',
            '',
            'if not DB_PATH.exists():',
            '    print("Berkas stasiun.db belum ada. Ulangi FS-40 dulu.")',
            '    raise SystemExit(1)',
            '',
            'suhu = [26.0, 26.2, 26.4, 26.6, 26.8, 27.0, 27.1, 27.2, 27.3, 27.4, 27.5, 27.5]',
            'now = datetime.now(timezone.utc).astimezone()',
            '',
            'with sqlite3.connect(DB_PATH) as db:',
            '    db.execute(',
            '        """',
            '        CREATE TABLE IF NOT EXISTS telemetry (',
            '            id INTEGER PRIMARY KEY AUTOINCREMENT,',
            '            received_at TEXT NOT NULL,',
            '            device_id TEXT,',
            '            temperature_c REAL,',
            '            humidity_pct REAL,',
            '            topic TEXT,',
            '            payload TEXT',
            '        )',
            '        """',
            '    )',
            '    db.execute("DELETE FROM telemetry WHERE payload LIKE ?", ("%isi_histori%",))',
            '    for index, temperature_c in enumerate(suhu):',
            '        when = now - timedelta(minutes=55 - index * 5)',
            '        db.execute(',
            '            """',
            '            INSERT INTO telemetry (received_at, device_id, temperature_c, humidity_pct, topic, payload)',
            '            VALUES (?, ?, ?, ?, ?, ?)',
            '            """',
            '            ,',
            '            (',
            '                when.isoformat(timespec="seconds"),',
            '                DEVICE,',
            '                temperature_c,',
            '                60.0,',
            '                "kodingindonesia/fsiot/esp32-meja-01/telemetry",',
            '                \'{"sumber":"isi_histori"}\',',
            '            ),',
            '        )',
            '    print("12 titik satu jam siap.")',
        ]);
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
            '    .angka { font-size: 3rem; font-weight: 700; }',
            '    .bingkai { max-width: 100%; }',
            '  </style>',
            '  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>',
            '</head>',
            '<body>',
            '  <h1>Stasiun meja</h1>',
            '  <p>Suhu</p>',
            '  <p id="suhu-angka" class="angka">…</p>',
            '  <div class="bingkai">',
            '    <canvas id="grafik-suhu"></canvas>',
            '  </div>',
            '  <p id="status">Menunggu data…</p>',
            '  <script>',
            '    const grafik = new Chart(document.getElementById("grafik-suhu"), {',
            '      type: "line",',
            '      data: {',
            '        labels: [],',
            '        datasets: [{ label: "Suhu (C)", data: [], borderColor: "#1565c0", fill: false, tension: 0.2 }],',
            '      },',
            '      options: { responsive: true, maintainAspectRatio: true, scales: { y: { beginAtZero: false } } },',
            '    });',
            '',
            '    function jamLabel(iso) {',
            '      const found = String(iso).match(/T(\\d{2}:\\d{2})/);',
            '      return found ? found[1] : String(iso);',
            '    }',
            '',
            '    function muat() {',
            '      fetch("/history?hours=1")',
            '        .then((r) => r.json())',
            '        .then((data) => {',
            '          const baris = data.baris || [];',
            '          grafik.data.labels = baris.map((row) => jamLabel(row.received_at));',
            '          grafik.data.datasets[0].data = baris.map((row) => row.temperature_c);',
            '          grafik.update();',
            '          const last = baris.slice(-1)[0];',
            '          document.getElementById("suhu-angka").textContent = last ? last.temperature_c : "—";',
            '          document.getElementById("status").textContent =',
            '            baris.length ? "Grafik tampil." : "Belum ada titik dalam 1 jam. Jalankan isi_histori.py.";',
            '        })',
            '        .catch(() => {',
            '          document.getElementById("status").textContent = "Pintu Flask belum terbuka.";',
            '        });',
            '    }',
            '',
            '    muat();',
            '    setInterval(muat, 5000);',
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
            'from datetime import datetime, timedelta, timezone',
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
            'MAX_POINTS = 60',
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
            'def parse_when(text):',
            '    raw = str(text).replace("Z", "+00:00")',
            '    try:',
            '        when = datetime.fromisoformat(raw)',
            '    except ValueError:',
            '        return None',
            '    if when.tzinfo is None:',
            '        when = when.replace(tzinfo=datetime.now(timezone.utc).astimezone().tzinfo)',
            '    return when',
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
            '@app.get("/history")',
            'def history():',
            '    rows = baca_baris()',
            '    if rows is None:',
            '        return jsonify({"jumlah": 0, "pesan": "Berkas stasiun.db belum ada. Ulangi FS-40."}), 503',
            '    device_id = (request.args.get("device_id") or "").strip()',
            '    if device_id:',
            '        rows = [row for row in rows if str(row["device_id"]) == device_id]',
            '    batas = datetime.now(timezone.utc).astimezone() - timedelta(hours=1)',
            '    filtered = []',
            '    for row in rows:',
            '        when = parse_when(row.get("received_at"))',
            '        if when is not None and when >= batas:',
            '            filtered.append(',
            '                {',
            '                    "received_at": row["received_at"],',
            '                    "device_id": row["device_id"],',
            '                    "temperature_c": row["temperature_c"],',
            '                }',
            '            )',
            '    dipotong = False',
            '    if len(filtered) > MAX_POINTS:',
            '        filtered = filtered[-MAX_POINTS:]',
            '        dipotong = True',
            '    return jsonify(',
            '        {',
            '            "jumlah": len(filtered),',
            '            "hours": 1,',
            '            "maks_titik": MAX_POINTS,',
            '            "dipotong": dipotong,',
            '            "device_id": device_id or None,',
            '            "baris": filtered,',
            '        }',
            '    )',
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
            '        client_id="fsiot-fs45-pintu",',
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
            '    print("GET  http://127.0.0.1:5000/history?hours=1")',
            '    app.run(host=HOST, port=PORT, debug=False)',
        ]);
    }

    private function body(): string
    {
        $requirements = htmlspecialchars($this->requirements(), ENT_QUOTES, 'UTF-8');
        $isi = htmlspecialchars($this->isi(), ENT_QUOTES, 'UTF-8');
        $dashboard = htmlspecialchars($this->dashboard(), ENT_QUOTES, 'UTF-8');
        $pintu = htmlspecialchars($this->pintu(), ENT_QUOTES, 'UTF-8');
        $tools = $this->figure('fs45-tools-order.png', 'Urutan lima langkah: browser, File Explorer folder lab, Notepad, PowerShell isi histori lalu Flask, browser grafik http 127.0.0.1:5000', '<strong>Urutan meja kerja (lima langkah):</strong> browser → File Explorer cek <code>fsiot-fs39</code> → Notepad menulis <code>isi_histori.py</code>, <code>dashboard.html</code>, dan <code>pintu_stasiun.py</code> → PowerShell mengisi 12 titik lalu Flask → browser <code>http://127.0.0.1:5000</code>. Diagram buatan Koding Indonesia (FS-45).');
        $why = $this->figure('fs45-why-chart.png', 'Tiga kotak: satu angka FS-44, histori 12 titik satu jam, garis Chart.js Grafik tampil', '<strong>Satu angka kemarin. Hari ini garis tren.</strong> Baca dari kiri ke kanan: angka tunggal → histori 1 jam di <code>stasiun.db</code> → garis Chart.js. Diagram buatan Koding Indonesia (FS-45).');
        $history = $this->figure('fs45-history.png', 'Alur kiri ke kanan: gudang SQLite, saring satu jam, pangkas 60 titik, JSON history hours=1', '<strong>Pintu histori: 1 jam, paling banyak 60 titik.</strong> Baca dari kiri ke kanan: <code>stasiun.db</code> → saring → pangkas → <code>/history?hours=1</code>. Diagram buatan Koding Indonesia (FS-45).');
        $cdn = $this->figure('fs45-cdn.png', 'Tiga kotak: pakai jsDelivr Chart.js 4.4.1 HTTPS, jangan zip acak atau npm, hasil script lalu canvas', '<strong>Chart.js dari CDN — langkah aman.</strong> HTTPS, versi dikunci <code>4.4.1</code>, bukan zip acak, bukan npm. Diagram buatan Koding Indonesia (FS-45).');
        $flaskServe = $this->figure('fs45-flask-serve.png', 'Alur kiri ke kanan: browser GET slash, Flask dashboard.html, fetch history, garis Chart.js Grafik tampil', '<strong>Gambar utama — Flask menyajikan halaman dan histori.</strong> Baca dari kiri ke kanan: browser → <code>/</code> → <code>dashboard.html</code> → fetch <code>/history?hours=1</code> → garis. Diagram buatan Koding Indonesia (FS-45).');
        $polling = $this->figure('fs45-polling.png', 'Alur kiri ke kanan: fungsi muat, fetch history, update garis, jeda 5 detik lalu ulang', '<strong>Polling: halaman bertanya lagi setiap 5 detik.</strong> Baca dari kiri ke kanan: <code>muat()</code> → fetch → update garis → jeda. Diagram buatan Koding Indonesia (FS-45).');
        $browser = $this->figure('fs45-browser-chart.png', 'Ilustrasi jendela browser menampilkan judul Stasiun meja, angka suhu, garis tren, dan status Grafik tampil', '<strong>Browser sudah menampilkan garis tren suhu.</strong> Alamat yang dikunci adalah <code>http://127.0.0.1:5000</code> dan teks <code>Grafik tampil.</code> Bentuk garis boleh berbeda. Ilustrasi buatan Koding Indonesia (FS-45), meniru jendela browser. Tampilan resmi tidak dipakai utuh.');
        $timeAxis = $this->figure('fs45-time-axis.png', 'Ilustrasi dua panel: pakai jam 08:15 08:20 versus jangan millis yang meloncat ke nol', '<strong>Sumbu waktu memakai jam, bukan millis.</strong> Label diambil dari <code>received_at</code>. Ilustrasi buatan Koding Indonesia (FS-45), meniru sumbu grafik. Tampilan resmi tidak dipakai utuh.');
        $troubleshooting = $this->figure('fs45-troubleshooting.png', 'Empat pemeriksaan: isi_histori 12 titik, Flask, alamat file versus http, CDN Chart.js', '<strong>Skema bantu.</strong> Isi 12 titik dulu. Flask ke <code>127.0.0.1:5000</code>. Jangan <code>file://</code>. Diagram buatan Koding Indonesia (FS-45).');
        $install = $this->stepsCard([
            ['title' => 'Buka browser', 'text' => 'Pakai Chrome, Firefox, Edge, atau Safari. Siapkan artikel ini. Jangan ketik perintah Python dulu.'],
            ['title' => 'Buka File Explorer', 'text' => 'Masuk ke <code>Documents\\fsiot-fs39</code>. Folder <code>.venv</code>, berkas <code>stasiun.db</code>, dan <code>pintu_stasiun.py</code> harus sudah ada.'],
            ['title' => 'Buka Notepad, tulis berkas', 'text' => 'Simpan <code>isi_histori.py</code>, perbarui <code>dashboard.html</code> dan <code>pintu_stasiun.py</code>. All files, bukan <code>.txt</code>.'],
            ['title' => 'Buka PowerShell, isi lalu Flask', 'text' => 'Start → ketik PowerShell. Tidak perlu <em>Run as administrator</em>. Jalankan <code>isi_histori.py</code>, lalu Flask. Jendela Flask tetap terbuka.'],
            ['title' => 'Buka browser, ketik alamat http', 'text' => 'Tab baru: <code>http://127.0.0.1:5000</code> — bukan <code>file://</code>. Garis tren muncul, status <code>Grafik tampil.</code>'],
        ], '<strong>Cara menguji hari ini:</strong> bukti sukses = <code>isi_histori.py</code> menulis <code>12 titik satu jam siap.</code> dan browser di <code>http://127.0.0.1:5000</code> menampilkan garis tren plus teks <code>Grafik tampil.</code> ESP32 boleh menyala, tetapi tidak wajib.');

        return <<<'HTML'
<h2>Pendahuluan — tren, bukan satu angka</h2>
<p><strong>FS-45 / #115 (ini)</strong> adalah lab grafik. Kemarin halaman sudah menampilkan satu angka suhu. Hari ini tugasnya lain: <strong>lihat tren satu jam</strong>, supaya naik-turunnya kelihatan, bukan hanya titik terakhir.</p>
<p><strong>Intinya:</strong> isi 12 titik ke <code>stasiun.db</code>, buka pintu <code>/history?hours=1</code>, muat Chart.js dari CDN, lalu garis tren muncul di <code>http://127.0.0.1:5000</code> sampai teks <code>Grafik tampil.</code></p>
<p><strong>Analogi:</strong> satu angka adalah foto. Grafik adalah rekaman video singkat: suhu dari tadi sampai sekarang, dibaca kiri ke kanan. Tombol ON/OFF belum dibangun — itu FS-46.</p>
<p>Prasyarat lab: <strong>FS-44</strong> (halaman HTML sudah pernah terbuka), <strong>FS-40</strong> (<code>stasiun.db</code> sudah ada), dan <strong>FS-42</strong> (Flask sudah pernah terbuka). FS-41 MariaDB <strong>tidak wajib</strong>. ESP32 <strong>boleh menyala</strong>, dan <strong>boleh dicabut</strong>. Tidak ada kabel baru, tidak ada Upload, <strong>Bukan AC 220V</strong>.</p>

<h2>Hasil yang dituju</h2>
<ul>
<li><code>isi_histori.py</code> mencetak <code>12 titik satu jam siap.</code></li>
<li>PowerShell menampilkan <code>Pintu stasiun terbuka di http://127.0.0.1:5000</code>.</li>
<li>Bilah alamat browser adalah <code>http://127.0.0.1:5000</code> — bukan <code>file://</code>.</li>
<li>Halaman menampilkan garis tren suhu, bukan kanvas kosong.</li>
<li>Teks status adalah <code>Grafik tampil.</code></li>
<li>Tab lain, <code>http://127.0.0.1:5000/history?hours=1</code> adalah JSON.</li>
<li>Berkas <code>stasiun.db</code> masih ada.</li>
</ul>
<p><strong>Batas lab hari ini:</strong> Chart.js dari CDN, histori SQLite 1 jam, polling 5 detik. Belum tombol ON/OFF, belum MySQL, belum npm. Bukti cukup = garis tren di halaman HTML. <code>flask==3.1.3</code> sudah dari FS-42. MQTTX boleh tetap terbuka; tidak wajib hari ini.</p>

<h2>Istilah yang dipakai hari ini</h2>
<ul>
<li><strong>Histori</strong> — kumpulan titik suhu beserta waktu, bukan satu angka terakhir.</li>
<li><strong>GET /history?hours=1</strong> — pintu JSON untuk titik dalam satu jam terakhir.</li>
<li><strong>Chart.js</strong> — pustaka garis di browser. Hari ini dimuat dari CDN, bukan npm.</li>
<li><strong>CDN</strong> — alamat HTTPS yang mengirim berkas Chart.js. Versi dikunci <code>4.4.1</code>.</li>
<li><strong>Polling</strong> — halaman bertanya lagi ke Flask setiap 5 detik, lalu memperbarui garis.</li>
<li><strong>received_at</strong> — stempel waktu ISO dari SQLite. Sumbu X memakai jam <code>08:15</code>, bukan <code>millis</code>.</li>
<li><strong>maks 60 titik</strong> — batas lab supaya garis tidak ramai.</li>
<li><strong>canvas</strong> — kotak gambar di HTML tempat Chart.js menggambar garis.</li>
</ul>

<h2>Persiapan — buka tool yang benar dulu</h2>
HTML
            .$tools.$install.<<<'HTML'
<p><strong>Jangan dipakai hari ini:</strong> MySQL/MariaDB, phpMyAdmin, Arduino IDE, AC 220V, <code>file://</code>, membuka port 5000 ke internet, mengubah ExecutionPolicy, pip <code>flask-cors</code>, npm Chart.js, atau tombol ON/OFF. MQTTX boleh tetap. Node-RED boleh tetap terbuka.</p>
<p><strong>Tips ponsel:</strong> jika diagram terasa kecil, gunakan fitur perbesar pada browser. Gambar tidak perlu diketuk sampai memenuhi layar agar teks di sekitarnya tetap terbaca.</p>

<h2>Kenapa grafik, bukan satu angka</h2>
HTML
            .$why.<<<'HTML'
<p>Satu angka hanya bilang “sekarang berapa”. Tren bilang “tadi lebih dingin, lalu naik”. Untuk itu Flask harus mengirim banyak titik, bukan baris terakhir saja.</p>
<p>Gudang tetap SQLite <code>stasiun.db</code>. Jangan menunggu MariaDB. FS-41 tetap opsional.</p>

<h2>CDN Chart.js — langkah aman</h2>
HTML
            .$cdn.<<<'HTML'
<p><strong>Langkah aman:</strong> satu baris <code>&lt;script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"&gt;</code> di <code>&lt;head&gt;</code>. HTTPS, versi dikunci, sumber jsDelivr untuk Chart.js.</p>
<p>Jangan unduh zip dari situs tak dikenal. Jangan npm, jangan webpack. JSON suhu tetap dari <code>127.0.0.1:5000</code> — CDN hanya mengirim Chart.js, bukan data stasiunmu.</p>
<p>Kalau komputer tanpa internet, CDN gagal dan kanvas kosong. Sambungkan internet sebentar, atau ulangi setelah jaringan hidup. Lab tetap tidak membuka port 5000 ke internet.</p>

<h2>Isi 12 titik satu jam</h2>
HTML
            .$history.<<<'HTML'
<p><strong>Buka dulu File Explorer</strong>, masuk ke <code>Documents\fsiot-fs39</code>. Folder <code>.venv</code> dan berkas <code>stasiun.db</code> dari FS-40 harus sudah ada. Jika <code>stasiun.db</code> belum ada, ulangi FS-40 dulu.</p>
<p><strong>Buka dulu Notepad.</strong> Tempel kode ini. File → Save As, All files, nama <code>isi_histori.py</code>, folder lab. Jangan Save sebagai <code>.txt</code>. Script ini menghapus hanya baris latihan sebelumnya (payload <code>isi_histori</code>), bukan seluruh gudang.</p>
<pre><code class="language-python">
HTML
            .$isi.<<<'HTML'
</code></pre>
<p><strong>Buka dulu PowerShell:</strong> Start → ketik <strong>PowerShell</strong> → Windows PowerShell. Tidak perlu <em>Run as administrator</em>.</p>
<p><strong>Cara menempel perintah:</strong> salin baris, klik jendela PowerShell, lalu <kbd>Ctrl</kbd>+<kbd>V</kbd> atau klik kanan. Setelah teks muncul, tekan Enter.</p>
<pre><code>cd "$env:USERPROFILE\Documents\fsiot-fs39"
.\.venv\Scripts\python.exe isi_histori.py</code></pre>
<p><strong>Hasil yang dicari:</strong> <code>12 titik satu jam siap.</code></p>
<p><strong>macOS atau Linux:</strong> buka Terminal, <code>cd ~/Documents/fsiot-fs39</code>, lalu <code>.venv/bin/python isi_histori.py</code>.</p>

<h2>Flask membuka pintu histori</h2>
HTML
            .$flaskServe.<<<'HTML'
<p><code>requirements.txt</code> tetap mengunci <code>flask==3.1.3</code> dan <code>paho-mqtt==2.1.0</code> seperti FS-42. Jangan pip ke Python global. Kalau Flask sudah terpasang, tidak perlu pip ulang. Jangan tambah <code>flask-cors</code>.</p>
<pre><code>
HTML
            .$requirements.<<<'HTML'
</code></pre>
<p><strong>Buka dulu Notepad.</strong> Ganti isi <code>pintu_stasiun.py</code> dengan kode di bawah. Save As, All files, folder <code>Documents\fsiot-fs39</code>. GET <code>/</code> mengirim <code>dashboard.html</code>. GET <code>/history?hours=1</code> mengirim titik satu jam, paling banyak 60. GET <code>/telemetry</code> tetap JSON. Saringan <code>?device_id=</code> dari FS-43 tetap ada, tetapi fetch hari ini tidak memakainya.</p>
<pre><code class="language-python">
HTML
            .$pintu.<<<'HTML'
</code></pre>
<p>Jika Flask hilang dari venv:</p>
<pre><code>.\.venv\Scripts\python.exe -m pip install -r requirements.txt</code></pre>

<h2>Tulis dashboard.html dengan canvas</h2>
<p><strong>Buka dulu Notepad.</strong> Ganti isi <code>dashboard.html</code>. File → Save As, All files, folder lab. Jangan Save sebagai <code>.txt</code>.</p>
<pre><code class="language-html">
HTML
            .$dashboard.<<<'HTML'
</code></pre>
<p>HTML menyusun judul, angka, dan <code>&lt;canvas&gt;</code>. Chart.js dimuat dari CDN. <code>fetch("/history?hours=1")</code> mengambil JSON di origin yang sama, lalu label jam ditulis dari <code>received_at</code>. Yang dikunci adalah teks <code>Grafik tampil.</code></p>

<h2>Jalankan Flask, buka di browser</h2>
HTML
            .$browser.<<<'HTML'
<p>Tutup Flask lama jika masih jalan, lalu di PowerShell yang sama:</p>
<pre><code>cd "$env:USERPROFILE\Documents\fsiot-fs39"
.\.venv\Scripts\python.exe pintu_stasiun.py</code></pre>
<p><strong>Hasil yang dicari:</strong> <code>Pintu stasiun terbuka di http://127.0.0.1:5000</code> lalu <code>Buka http://127.0.0.1:5000</code> lalu <code>GET  http://127.0.0.1:5000/history?hours=1</code>. Jendela ini <strong>tetap terbuka</strong>. Jika <code>.\.venv\Scripts\Activate.ps1</code> ditolak, <strong>jangan ubah ExecutionPolicy</strong>.</p>
<p><strong>Buka browser</strong> di tab baru. Ketik alamat ini, lalu Enter:</p>
<pre><code>http://127.0.0.1:5000</code></pre>
<p><strong>Hasil yang dicari:</strong> judul Stasiun meja, garis tren, teks <code>Grafik tampil.</code> Ini halaman HTML, bukan JSON mentah. <strong>Jangan buka berkas HTML lewat <code>file://</code>.</strong></p>
<p>Tab lain, pastikan pintu histori hidup:</p>
<pre><code>http://127.0.0.1:5000/history?hours=1</code></pre>
<p><strong>macOS atau Linux:</strong> buka Terminal, <code>cd ~/Documents/fsiot-fs39</code>, lalu <code>.venv/bin/python pintu_stasiun.py</code>.</p>

<h2>Polling: grafik bertanya lagi</h2>
HTML
            .$polling.<<<'HTML'
<p><code>setInterval(muat, 5000)</code> memanggil pintu histori setiap 5 detik. Kalau tidak ada titik baru, garis boleh diam. Yang dikunci tetap teks <code>Grafik tampil.</code></p>
<p>Kalau ingin melihat garis bergerak: di PowerShell <strong>lain</strong> (jangan matikan Flask), jalankan lagi <code>isi_histori.py</code>, lalu tunggu paling lama 5 detik.</p>

<h2>Sumbu waktu: jam, bukan millis</h2>
HTML
            .$timeAxis.<<<'HTML'
<p>Fungsi <code>jamLabel</code> memotong <code>received_at</code> menjadi <code>08:15</code>. Itu jam dinding, kiri ke kanan. <code>millis</code> atau counter nyala ulang membuat titik meloncat ke awal — seolah waktu reset, padahal siang sudah berganti.</p>
<p>Itulah alasan FS-36 mengajarkan jam internet. Di lab ini stempel sudah ditulis Python saat baris masuk SQLite.</p>

<h2>Terlalu banyak titik</h2>
<p>Kalau semua baris gudang dikirim, garis menjadi ramai dan browser berat. Pintu histori memotong di <code>MAX_POINTS = 60</code>. JSON punya <code>"maks_titik": 60</code>. Itu cukup untuk satu jam di meja belajar.</p>
<p>Jangan mengubah batas itu hari ini. Jangan menarik seluruh tabel.</p>

<h2>Jika grafik tidak muncul</h2>
HTML
            .$troubleshooting.<<<'HTML'
<ol>
<li><strong>isi_histori belum jalan.</strong> Ulangi sampai <code>12 titik satu jam siap.</code> Tanpa titik dalam 1 jam, garis kosong.</li>
<li><strong>Flask belum terbuka.</strong> Jendela harus menampilkan <code>Pintu stasiun terbuka</code>. Jangan ditutup sebelum grafik tampil.</li>
<li><strong>Masih file://.</strong> Tutup tab. Ketik <code>http://127.0.0.1:5000</code>.</li>
<li><strong>CDN Chart.js gagal.</strong> Perlu internet sebentar untuk memuat <code>chart.js@4.4.1</code>. JSON tetap lokal.</li>
<li><strong>dashboard.html belum di folder yang sama.</strong> Flask mencari berkas di samping <code>pintu_stasiun.py</code>.</li>
</ol>

<h2 id="fsiot-chart-checklist">Checklist sebelum FS-46</h2>
<p>Centang setelah kamu benar-benar melakukan setiap poin. Target: <strong>10/10</strong>. Progres disimpan di browser perangkatmu dan tidak dikirim ke server.</p>
<ul id="fsiot-chart-checklist-items">
<li>Saya tidak membuka <code>dashboard.html</code> lewat <code>file://</code>.</li>
<li><code>isi_histori.py</code> mencetak 12 titik satu jam siap.</li>
<li>PowerShell menampilkan Pintu stasiun terbuka di port 5000.</li>
<li>Bilah alamat browser adalah <code>http://127.0.0.1:5000</code>.</li>
<li>Halaman menampilkan garis tren suhu, bukan kanvas kosong.</li>
<li>Teks status adalah <code>Grafik tampil.</code></li>
<li>GET <code>/history?hours=1</code> adalah JSON.</li>
<li>Saya tidak mengubah ExecutionPolicy.</li>
<li>Saya tidak memakai MySQL hari ini.</li>
<li>Saya tidak memakai tombol ON/OFF atau npm Chart.js hari ini.</li>
</ul>
<p><strong>Cara memeriksa kesiapan:</strong> ceritakan dengan kata-katamu: 12 titik → pintu histori 1 jam → Chart.js CDN → garis tren → <code>Grafik tampil.</code> Pada FS-46, halaman ini mendapat tombol ON/OFF. MariaDB tetap opsional.</p>

<h2>Kesalahan yang sering terjadi</h2>
<ul>
<li><strong>Membuka file://.</strong> Fetch ke Flask ditolak. Pakai <code>http://127.0.0.1:5000</code>.</li>
<li><strong>Menunggu MySQL.</strong> Gudang tetap SQLite <code>stasiun.db</code>.</li>
<li><strong>Mengirim semua baris.</strong> Terlalu banyak titik. Lab memotong di 60.</li>
<li><strong>Sumbu millis.</strong> Waktu meloncat setelah nyala ulang. Pakai jam dari <code>received_at</code>.</li>
<li><strong>npm Chart.js hari ini.</strong> CDN versi <code>4.4.1</code> sudah cukup.</li>
<li><strong>Mengubah ExecutionPolicy.</strong> Tetap pakai <code>.venv\Scripts\python.exe</code>.</li>
<li><strong>Membangun tombol ON/OFF hari ini.</strong> Ditunda ke FS-46.</li>
</ul>

<h2>Pertanyaan yang sering muncul</h2>
<h3>Kenapa CDN, bukan npm?</h3>
<p>Supaya lab tetap Notepad. Satu baris script HTTPS, versi dikunci. Webpack ditunda.</p>
<h3>Wajib MySQL?</h3>
<p>Tidak. SQLite cukup. FS-41 tetap opsional.</p>
<h3>ESP32 wajib menyala?</h3>
<p>Tidak. <code>isi_histori.py</code> menulis 12 titik. <strong>Bukan AC 220V.</strong></p>
<h3>Kenapa 12 titik, bukan data papan?</h3>
<p>Supaya rentang 1 jam selalu ada, bahkan jika ESP32 dicabut. Titik papan yang masuk 1 jam terakhir ikut tergambar.</p>
<h3>Apakah tombol ON/OFF hari ini?</h3>
<p>Tidak. Pintu <code>/command</code> boleh tetap ada. Tombol UI adalah FS-46.</p>
<h3>Kenapa garisnya beda dari gambar?</h3>
<p>Angka di <code>stasiun.db</code> milikmu. Yang dikunci adalah <code>Grafik tampil.</code></p>
<h3>Wajib internet terus?</h3>
<p>Hanya untuk memuat Chart.js sekali. JSON histori tetap di komputer ini.</p>
<h3>Kenapa jangan file://?</h3>
<p>Karena itu bukan server. Halaman, JSON, dan canvas harus satu origin Flask.</p>

<h2>Sumber</h2>
<ul>
<li><a href="https://www.chartjs.org/docs/latest/getting-started/installation.html" target="_blank" rel="noopener noreferrer">Chart.js installation</a> (MIT)</li>
<li><a href="https://www.jsdelivr.com/package/npm/chart.js" target="_blank" rel="noopener noreferrer">Chart.js on jsDelivr</a></li>
<li><a href="https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API" target="_blank" rel="noopener noreferrer">MDN Fetch API</a></li>
<li><a href="https://developer.mozilla.org/en-US/docs/Web/HTML/Element/canvas" target="_blank" rel="noopener noreferrer">MDN canvas</a></li>
<li><a href="https://flask.palletsprojects.com/en/stable/api/#flask.send_from_directory" target="_blank" rel="noopener noreferrer">Flask send_from_directory</a> (BSD-3-Clause)</li>
<li><a href="https://pypi.org/project/Flask/3.1.3/" target="_blank" rel="noopener noreferrer">Flask 3.1.3 on PyPI</a></li>
<li><a href="https://docs.python.org/3/library/sqlite3.html" target="_blank" rel="noopener noreferrer">sqlite3 — Python docs</a></li>
<li><a href="https://docs.python.org/3/library/datetime.html" target="_blank" rel="noopener noreferrer">datetime — Python docs</a></li>
<li>Diagram urutan tools, angka versus tren, pintu histori, CDN, Flask menyajikan, polling, skema periksa — Koding Indonesia (FS-45). Ilustrasi jendela grafik dan sumbu waktu — Koding Indonesia (FS-45).</li>
</ul>

<h2>Selanjutnya</h2>
<p><strong>Ringkasnya:</strong> garis tren satu jam sudah terbuka di <code>http://127.0.0.1:5000</code>. Chart.js dari CDN, histori dari SQLite. Pada <strong>FS-46</strong>, halaman ini mendapat tombol ON/OFF. Jangan <code>file://</code>. MariaDB tetap opsional.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $requirements = htmlspecialchars($this->requirements(), ENT_QUOTES, 'UTF-8');
        $isi = htmlspecialchars($this->isi(), ENT_QUOTES, 'UTF-8');
        $dashboard = htmlspecialchars($this->dashboard(), ENT_QUOTES, 'UTF-8');
        $pintu = htmlspecialchars($this->pintu(), ENT_QUOTES, 'UTF-8');
        $tools = $this->figure('fs45-tools-order.png', 'Five-step order: browser, File Explorer lab folder, Notepad, PowerShell fill history then Flask, browser chart at http 127.0.0.1:5000', '<strong>Desk order (five steps):</strong> browser → File Explorer checks <code>fsiot-fs39</code> → Notepad writes <code>isi_histori.py</code>, <code>dashboard.html</code>, and <code>pintu_stasiun.py</code> → PowerShell fills 12 points then Flask → browser <code>http://127.0.0.1:5000</code>. Diagram by Koding Indonesia (FS-45).');
        $why = $this->figure('fs45-why-chart.png', 'Three boxes: one FS-44 number, 12 one-hour points, Chart.js line Grafik tampil', '<strong>One number yesterday. A trend line today.</strong> Read left to right: a single number → one-hour history in <code>stasiun.db</code> → a Chart.js line. Diagram by Koding Indonesia (FS-45).');
        $history = $this->figure('fs45-history.png', 'Left-to-right flow: SQLite store, filter one hour, cap 60 points, JSON history hours=1', '<strong>History door: one hour, 60 points at most.</strong> Read left to right: <code>stasiun.db</code> → filter → cap → <code>/history?hours=1</code>. Diagram by Koding Indonesia (FS-45).');
        $cdn = $this->figure('fs45-cdn.png', 'Three boxes: use jsDelivr Chart.js 4.4.1 HTTPS, do not use a random zip or npm, result is script then canvas', '<strong>Chart.js from a CDN — the safe step.</strong> HTTPS, version pinned to <code>4.4.1</code>, not a random zip, not npm. Diagram by Koding Indonesia (FS-45).');
        $flaskServe = $this->figure('fs45-flask-serve.png', 'Left-to-right flow: browser GET slash, Flask dashboard.html, fetch history, Chart.js line Grafik tampil', '<strong>Main figure — Flask serves the page and the history.</strong> Read left to right: browser → <code>/</code> → <code>dashboard.html</code> → fetch <code>/history?hours=1</code> → the line. Diagram by Koding Indonesia (FS-45).');
        $polling = $this->figure('fs45-polling.png', 'Left-to-right flow: muat function, fetch history, update the line, wait 5 seconds then repeat', '<strong>Polling: the page asks again every 5 seconds.</strong> Read left to right: <code>muat()</code> → fetch → update the line → wait. Diagram by Koding Indonesia (FS-45).');
        $browser = $this->figure('fs45-browser-chart.png', 'Browser window illustration showing title Stasiun meja, a temperature number, a trend line, and status Grafik tampil', '<strong>The browser is already showing a temperature trend line.</strong> The lock is the address <code>http://127.0.0.1:5000</code> and the text <code>Grafik tampil.</code> The line shape may differ. Illustration by Koding Indonesia (FS-45), modelled on a browser window. The official window is not used as-is.');
        $timeAxis = $this->figure('fs45-time-axis.png', 'Two-panel illustration: use clock labels 08:15 08:20 versus do not use millis that jump to zero', '<strong>The time axis uses the clock, not millis.</strong> Labels come from <code>received_at</code>. Illustration by Koding Indonesia (FS-45), modelled on a chart axis. The official window is not used as-is.');
        $troubleshooting = $this->figure('fs45-troubleshooting.png', 'Four checks: isi_histori 12 points, Flask, file versus http address, Chart.js CDN', '<strong>Helper schematic.</strong> Fill 12 points first. Flask uses <code>127.0.0.1:5000</code>. Do not use <code>file://</code>. Diagram by Koding Indonesia (FS-45).');
        $install = $this->stepsCard([
            ['title' => 'Open a browser', 'text' => 'Use Chrome, Firefox, Edge, or Safari. Keep this guide ready. Do not type Python commands yet.'],
            ['title' => 'Open File Explorer', 'text' => 'Go to <code>Documents\\fsiot-fs39</code>. The <code>.venv</code> folder, the <code>stasiun.db</code> file, and <code>pintu_stasiun.py</code> must already be there.'],
            ['title' => 'Open Notepad, write the files', 'text' => 'Save <code>isi_histori.py</code>, then update <code>dashboard.html</code> and <code>pintu_stasiun.py</code>. All files, not <code>.txt</code>.'],
            ['title' => 'Open PowerShell, fill then Flask', 'text' => 'Start → type PowerShell. You do not need <em>Run as administrator</em>. Run <code>isi_histori.py</code>, then Flask. Leave the Flask window open.'],
            ['title' => 'Open a browser, type the http address', 'text' => 'New tab: <code>http://127.0.0.1:5000</code> — not <code>file://</code>. A trend line appears, status <code>Grafik tampil.</code>'],
        ], '<strong>How to test today:</strong> success = <code>isi_histori.py</code> prints <code>12 titik satu jam siap.</code> and the browser at <code>http://127.0.0.1:5000</code> shows a trend line plus the text <code>Grafik tampil.</code> The ESP32 may be on, but it is not required.');

        return <<<'HTML'
<h2>Introduction — a trend, not one number</h2>
<p><strong>FS-45 / #115 (this article)</strong> is the chart lab. Yesterday the page already showed one temperature number. Today the job is different: <strong>see a one-hour trend</strong>, so the rise and fall is visible, not only the last point.</p>
<p><strong>In short:</strong> fill 12 points into <code>stasiun.db</code>, open the <code>/history?hours=1</code> door, load Chart.js from a CDN, then a trend line appears at <code>http://127.0.0.1:5000</code> until the text <code>Grafik tampil.</code></p>
<p><strong>Analogy:</strong> one number is a photo. A chart is a short recording: temperature from a while ago until now, read left to right. ON/OFF buttons are not built yet — that is FS-46.</p>
<p>Lab prerequisites: <strong>FS-44</strong> (the HTML page has opened before), <strong>FS-40</strong> (<code>stasiun.db</code> already exists), and <strong>FS-42</strong> (Flask has opened before). FS-41 MariaDB is <strong>not required</strong>. The ESP32 <strong>may stay on</strong>, and <strong>may be unplugged</strong>. No new cables, no Upload, <strong>Not AC mains</strong>.</p>

<h2>Expected outcome</h2>
<ul>
<li><code>isi_histori.py</code> prints <code>12 titik satu jam siap.</code></li>
<li>PowerShell shows <code>Pintu stasiun terbuka di http://127.0.0.1:5000</code>.</li>
<li>The browser address bar is <code>http://127.0.0.1:5000</code> — not <code>file://</code>.</li>
<li>The page shows a temperature trend line, not an empty canvas.</li>
<li>The status text is <code>Grafik tampil.</code></li>
<li>In another tab, <code>http://127.0.0.1:5000/history?hours=1</code> is JSON.</li>
<li>The file <code>stasiun.db</code> is still there.</li>
</ul>
<p><strong>Today’s lab boundary:</strong> Chart.js from a CDN, one-hour SQLite history, 5-second polling. No ON/OFF buttons, no MySQL, no npm. Enough proof = a trend line on an HTML page. <code>flask==3.1.3</code> is already from FS-42. MQTTX may stay open; it is not required today.</p>

<h2>Terms used today</h2>
<ul>
<li><strong>History</strong> — a set of temperature points with time, not only the last number.</li>
<li><strong>GET /history?hours=1</strong> — the JSON door for points in the last hour.</li>
<li><strong>Chart.js</strong> — the line library in the browser. Today it loads from a CDN, not npm.</li>
<li><strong>CDN</strong> — an HTTPS address that sends the Chart.js file. The version is pinned to <code>4.4.1</code>.</li>
<li><strong>Polling</strong> — the page asks Flask again every 5 seconds, then updates the line.</li>
<li><strong>received_at</strong> — the ISO timestamp from SQLite. The X axis uses clock time <code>08:15</code>, not <code>millis</code>.</li>
<li><strong>60 points max</strong> — the lab cap so the line does not get crowded.</li>
<li><strong>canvas</strong> — the HTML drawing box where Chart.js paints the line.</li>
</ul>

<h2>Preparation — open the right tools first</h2>
HTML
            .$tools.$install.<<<'HTML'
<p><strong>Not used today:</strong> MySQL/MariaDB, phpMyAdmin, Arduino IDE, AC mains, <code>file://</code>, opening port 5000 to the internet, changing ExecutionPolicy, pip <code>flask-cors</code>, npm Chart.js, or ON/OFF buttons. MQTTX may stay. Node-RED may stay open.</p>
<p><strong>Phone tip:</strong> if a diagram feels small, use the browser zoom. You do not need to tap the image until it fills the screen; nearby text should stay readable.</p>

<h2>Why a chart, not one number</h2>
HTML
            .$why.<<<'HTML'
<p>One number only says “what now”. A trend says “it was cooler, then it rose”. For that, Flask must send many points, not only the last row.</p>
<p>The store stays SQLite <code>stasiun.db</code>. Do not wait for MariaDB. FS-41 stays optional.</p>

<h2>CDN Chart.js — the safe step</h2>
HTML
            .$cdn.<<<'HTML'
<p><strong>The safe step:</strong> one line <code>&lt;script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"&gt;</code> in <code>&lt;head&gt;</code>. HTTPS, pinned version, jsDelivr for Chart.js.</p>
<p>Do not download a zip from an unknown site. Do not use npm, do not use webpack. Temperature JSON still comes from <code>127.0.0.1:5000</code> — the CDN only sends Chart.js, not your station data.</p>
<p>If the computer is offline, the CDN fails and the canvas stays empty. Connect to the internet briefly, or retry after the network is up. The lab still does not open port 5000 to the internet.</p>

<h2>Fill 12 one-hour points</h2>
HTML
            .$history.<<<'HTML'
<p><strong>Open File Explorer first</strong>, go to <code>Documents\fsiot-fs39</code>. The <code>.venv</code> folder and the <code>stasiun.db</code> file from FS-40 must already be there. If <code>stasiun.db</code> is missing, repeat FS-40 first.</p>
<p><strong>Open Notepad first.</strong> Paste this code. File → Save As, All files, name <code>isi_histori.py</code>, lab folder. Do not Save as <code>.txt</code>. This script deletes only the previous practice rows (payload <code>isi_histori</code>), not the whole store.</p>
<pre><code class="language-python">
HTML
            .$isi.<<<'HTML'
</code></pre>
<p><strong>Open PowerShell first:</strong> Start → type <strong>PowerShell</strong> → Windows PowerShell. You do not need <em>Run as administrator</em>.</p>
<p><strong>How to paste a command:</strong> copy the line, click the PowerShell window, then <kbd>Ctrl</kbd>+<kbd>V</kbd> or right-click. After the text appears, press Enter.</p>
<pre><code>cd "$env:USERPROFILE\Documents\fsiot-fs39"
.\.venv\Scripts\python.exe isi_histori.py</code></pre>
<p><strong>Result to look for:</strong> <code>12 titik satu jam siap.</code></p>
<p><strong>macOS or Linux:</strong> open Terminal, <code>cd ~/Documents/fsiot-fs39</code>, then <code>.venv/bin/python isi_histori.py</code>.</p>

<h2>Flask opens the history door</h2>
HTML
            .$flaskServe.<<<'HTML'
<p><code>requirements.txt</code> still pins <code>flask==3.1.3</code> and <code>paho-mqtt==2.1.0</code> as in FS-42. Do not pip into global Python. If Flask is already installed, you do not need to pip again. Do not add <code>flask-cors</code>.</p>
<pre><code>
HTML
            .$requirements.<<<'HTML'
</code></pre>
<p><strong>Open Notepad first.</strong> Replace <code>pintu_stasiun.py</code> with the code below. Save As, All files, folder <code>Documents\fsiot-fs39</code>. GET <code>/</code> sends <code>dashboard.html</code>. GET <code>/history?hours=1</code> sends one-hour points, 60 at most. GET <code>/telemetry</code> is still JSON. The <code>?device_id=</code> filter from FS-43 stays, but today’s fetch does not use it.</p>
<pre><code class="language-python">
HTML
            .$pintu.<<<'HTML'
</code></pre>
<p>If Flask is missing from the venv:</p>
<pre><code>.\.venv\Scripts\python.exe -m pip install -r requirements.txt</code></pre>

<h2>Write dashboard.html with a canvas</h2>
<p><strong>Open Notepad first.</strong> Replace <code>dashboard.html</code>. File → Save As, All files, lab folder. Do not Save as <code>.txt</code>.</p>
<pre><code class="language-html">
HTML
            .$dashboard.<<<'HTML'
</code></pre>
<p>HTML builds the title, the number, and a <code>&lt;canvas&gt;</code>. Chart.js loads from the CDN. <code>fetch("/history?hours=1")</code> loads JSON on the same origin, then clock labels are cut from <code>received_at</code>. The lock is the text <code>Grafik tampil.</code></p>

<h2>Run Flask, open it in a browser</h2>
HTML
            .$browser.<<<'HTML'
<p>Close the old Flask window if it is still running, then in the same PowerShell:</p>
<pre><code>cd "$env:USERPROFILE\Documents\fsiot-fs39"
.\.venv\Scripts\python.exe pintu_stasiun.py</code></pre>
<p><strong>Result to look for:</strong> <code>Pintu stasiun terbuka di http://127.0.0.1:5000</code> then <code>Buka http://127.0.0.1:5000</code> then <code>GET  http://127.0.0.1:5000/history?hours=1</code>. Leave this window <strong>open</strong>. If <code>.\.venv\Scripts\Activate.ps1</code> is rejected, <strong>do not change ExecutionPolicy</strong>.</p>
<p><strong>Open a browser</strong> in a new tab. Type this address, then Enter:</p>
<pre><code>http://127.0.0.1:5000</code></pre>
<p><strong>Result to look for:</strong> title Stasiun meja, a trend line, the text <code>Grafik tampil.</code> This is an HTML page, not raw JSON. <strong>Do not open the HTML file through <code>file://</code>.</strong></p>
<p>In another tab, confirm the history door is alive:</p>
<pre><code>http://127.0.0.1:5000/history?hours=1</code></pre>
<p><strong>macOS or Linux:</strong> open Terminal, <code>cd ~/Documents/fsiot-fs39</code>, then <code>.venv/bin/python pintu_stasiun.py</code>.</p>

<h2>Polling: the chart asks again</h2>
HTML
            .$polling.<<<'HTML'
<p><code>setInterval(muat, 5000)</code> calls the history door every 5 seconds. If there is no new point, the line may stay still. The lock is still the text <code>Grafik tampil.</code></p>
<p>If you want to see the line move: in <strong>another</strong> PowerShell (do not stop Flask), run <code>isi_histori.py</code> again, then wait at most 5 seconds.</p>

<h2>Time axis: clock, not millis</h2>
HTML
            .$timeAxis.<<<'HTML'
<p>The <code>jamLabel</code> function cuts <code>received_at</code> down to <code>08:15</code>. That is wall-clock time, left to right. <code>millis</code> or a power-up counter makes points jump back to the start — as if time reset, even though the afternoon has already moved on.</p>
<p>That is why FS-36 taught internet time. In this lab the stamp is already written by Python when the row enters SQLite.</p>

<h2>Too many points</h2>
<p>If every store row is sent, the line gets crowded and the browser gets heavy. The history door cuts at <code>MAX_POINTS = 60</code>. The JSON has <code>"maks_titik": 60</code>. That is enough for one hour at the study desk.</p>
<p>Do not change that cap today. Do not pull the whole table.</p>

<h2>If the chart does not appear</h2>
HTML
            .$troubleshooting.<<<'HTML'
<ol>
<li><strong>isi_histori has not run.</strong> Repeat until <code>12 titik satu jam siap.</code> Without points in the last hour, the line is empty.</li>
<li><strong>Flask is not open.</strong> The window must show <code>Pintu stasiun terbuka</code>. Do not close it before the chart appears.</li>
<li><strong>Still file://.</strong> Close the tab. Type <code>http://127.0.0.1:5000</code>.</li>
<li><strong>The Chart.js CDN failed.</strong> You need the internet briefly to load <code>chart.js@4.4.1</code>. JSON stays local.</li>
<li><strong>dashboard.html is not in the same folder.</strong> Flask looks for the file next to <code>pintu_stasiun.py</code>.</li>
</ol>

<h2 id="fsiot-chart-checklist">Checklist before FS-46</h2>
<p>Tick after you have actually done each item. Target: <strong>10/10</strong>. Progress stays in this browser on your device and is not sent to the server.</p>
<ul id="fsiot-chart-checklist-items">
<li>I did not open <code>dashboard.html</code> through <code>file://</code>.</li>
<li><code>isi_histori.py</code> printed 12 titik satu jam siap.</li>
<li>PowerShell shows Pintu stasiun terbuka on port 5000.</li>
<li>The browser address bar is <code>http://127.0.0.1:5000</code>.</li>
<li>The page shows a temperature trend line, not an empty canvas.</li>
<li>The status text is <code>Grafik tampil.</code></li>
<li>GET <code>/history?hours=1</code> is JSON.</li>
<li>I did not change ExecutionPolicy.</li>
<li>I did not use MySQL today.</li>
<li>I did not use ON/OFF buttons or npm Chart.js today.</li>
</ul>
<p><strong>How to check readiness:</strong> tell it in your own words: 12 points → one-hour history door → Chart.js CDN → trend line → <code>Grafik tampil.</code> In FS-46, this page gets ON/OFF buttons. MariaDB stays optional.</p>

<h2>Common mistakes</h2>
<ul>
<li><strong>Opening file://.</strong> Fetch to Flask is rejected. Use <code>http://127.0.0.1:5000</code>.</li>
<li><strong>Waiting for MySQL.</strong> The store stays SQLite <code>stasiun.db</code>.</li>
<li><strong>Sending every row.</strong> Too many points. The lab cuts at 60.</li>
<li><strong>A millis axis.</strong> Time jumps after a power-up. Use clock time from <code>received_at</code>.</li>
<li><strong>npm Chart.js today.</strong> The CDN at version <code>4.4.1</code> is enough.</li>
<li><strong>Changing ExecutionPolicy.</strong> Keep using <code>.venv\Scripts\python.exe</code>.</li>
<li><strong>Building ON/OFF buttons today.</strong> That waits for FS-46.</li>
</ul>

<h2>Frequently asked questions</h2>
<h3>Why a CDN, not npm?</h3>
<p>So the lab stays in Notepad. One HTTPS script line, version pinned. Webpack waits.</p>
<h3>Is MySQL required?</h3>
<p>No. SQLite is enough. FS-41 stays optional.</p>
<h3>Must the ESP32 stay on?</h3>
<p>No. <code>isi_histori.py</code> writes 12 points. <strong>Not AC mains.</strong></p>
<h3>Why 12 points, not board data?</h3>
<p>So the one-hour window is always there, even if the ESP32 is unplugged. Board points that fall in the last hour are drawn too.</p>
<h3>Are ON/OFF buttons today?</h3>
<p>No. The <code>/command</code> door may stay. The UI buttons are FS-46.</p>
<h3>Why does the line differ from the picture?</h3>
<p>The numbers in <code>stasiun.db</code> are yours. The lock is <code>Grafik tampil.</code></p>
<h3>Do I need the internet the whole time?</h3>
<p>Only to load Chart.js once. History JSON stays on this computer.</p>
<h3>Why not file://?</h3>
<p>Because that is not a server. The page, the JSON, and the canvas must share one Flask origin.</p>

<h2>Sources</h2>
<ul>
<li><a href="https://www.chartjs.org/docs/latest/getting-started/installation.html" target="_blank" rel="noopener noreferrer">Chart.js installation</a> (MIT)</li>
<li><a href="https://www.jsdelivr.com/package/npm/chart.js" target="_blank" rel="noopener noreferrer">Chart.js on jsDelivr</a></li>
<li><a href="https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API" target="_blank" rel="noopener noreferrer">MDN Fetch API</a></li>
<li><a href="https://developer.mozilla.org/en-US/docs/Web/HTML/Element/canvas" target="_blank" rel="noopener noreferrer">MDN canvas</a></li>
<li><a href="https://flask.palletsprojects.com/en/stable/api/#flask.send_from_directory" target="_blank" rel="noopener noreferrer">Flask send_from_directory</a> (BSD-3-Clause)</li>
<li><a href="https://pypi.org/project/Flask/3.1.3/" target="_blank" rel="noopener noreferrer">Flask 3.1.3 on PyPI</a></li>
<li><a href="https://docs.python.org/3/library/sqlite3.html" target="_blank" rel="noopener noreferrer">sqlite3 — Python docs</a></li>
<li><a href="https://docs.python.org/3/library/datetime.html" target="_blank" rel="noopener noreferrer">datetime — Python docs</a></li>
<li>Tool-order, number-versus-trend, history-door, CDN, Flask-serves, polling, and check diagrams — Koding Indonesia (FS-45). Chart-window and time-axis illustrations — Koding Indonesia (FS-45).</li>
</ul>

<h2>Next</h2>
<p><strong>In short:</strong> the one-hour trend line is already open at <code>http://127.0.0.1:5000</code>. Chart.js from a CDN, history from SQLite. In <strong>FS-46</strong>, this page gets ON/OFF buttons. Do not use <code>file://</code>. MariaDB stays optional.</p>
HTML;
    }
}
